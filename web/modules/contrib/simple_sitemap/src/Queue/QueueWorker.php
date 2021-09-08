<?php

namespace Drupal\simple_sitemap\Queue;

use Drupal\Component\Utility\Timer;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\simple_sitemap\Entity\SimpleSitemap;
use Drupal\simple_sitemap\Settings;
use Drupal\Core\State\StateInterface;
use Drupal\simple_sitemap\Logger;

class QueueWorker {

  use BatchTrait;

  protected const REBUILD_QUEUE_CHUNK_ITEM_SIZE = 5000;
  public const LOCK_ID = 'simple_sitemap:generation';
  public const GENERATE_LOCK_TIMEOUT = 3600;

  public const GENERATE_TYPE_FORM = 'form';
  public const GENERATE_TYPE_DRUSH = 'drush';
  public const GENERATE_TYPE_CRON = 'cron';
  public const GENERATE_TYPE_BACKEND = 'backend';

  /**
   * @var \Drupal\simple_sitemap\Settings
   */
  protected $settings;

  /**
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * @var \Drupal\simple_sitemap\Queue\SimpleSitemapQueue
   */
  protected $queue;

  /**
   * @var \Drupal\simple_sitemap\Logger
   */
  protected $logger;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;

  /**
   * @var \Drupal\simple_sitemap\Entity\SimpleSitemapInterface
   */
  protected $variantProcessedNow;

  /**
   * @var array
   */
  protected $results = [];

  /**
   * @var array
   */
  protected $processedResults = [];

  /**
   * @var array
   */
  protected $processedPaths = [];

  /**
   * @var array
   */
  protected $generatorSettings;

  /**
   * @var int|null
   */
  protected $maxLinks;

  /**
   * @var int|null
   */
  protected $elementsRemaining;

  /**
   * @var int|null
   */
  protected $elementsTotal;

  /**
   * QueueWorker constructor.
   *
   * @param \Drupal\simple_sitemap\Settings $settings
   * @param \Drupal\Core\State\StateInterface $state
   * @param \Drupal\simple_sitemap\Queue\SimpleSitemapQueue $element_queue
   * @param \Drupal\simple_sitemap\Logger $logger
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   */
  public function __construct(Settings $settings,
                              StateInterface $state,
                              SimpleSitemapQueue $element_queue,
                              Logger $logger,
                              ModuleHandlerInterface $module_handler,
                              EntityTypeManagerInterface $entity_type_manager,
                              LockBackendInterface $lock) {
    $this->settings = $settings;
    $this->state = $state;
    $this->queue = $element_queue;
    $this->logger = $logger;
    $this->moduleHandler = $module_handler;
    $this->entityTypeManager = $entity_type_manager;
    $this->lock = $lock;
  }

  /**
   * @param string[]|string|null $variants
   * @return $this
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function queue($variants = NULL): QueueWorker {
    $all_data_sets = [];
    $empty_variants = [];

    $variants = $variants !== NULL ? (array) $variants : NULL;

    /** @var \Drupal\simple_sitemap\Entity\SimpleSitemap[] $variants */
    $variants = $this->entityTypeManager->getStorage('simple_sitemap')->loadMultiple($variants);

    foreach ($variants as $variant_id => $variant) {
      $data_sets = [];
      foreach ($variant->getType()->getUrlGenerators() as $url_generator_id => $url_generator) {
        $data_sets = $url_generator->setSitemapVariant($variant)->getDataSets(); //todo automatically set variant
        foreach ($data_sets as $data_set) {
          $all_data_sets[] = [
            'data' => $data_set,
            'sitemap' => $variant_id,
            'url_generator' => $url_generator_id,
          ];

          if (count($all_data_sets) === self::REBUILD_QUEUE_CHUNK_ITEM_SIZE) {
            $this->queueElements($all_data_sets);
            $all_data_sets = [];
          }
        }
      }
      if (empty($data_sets)) {
        $empty_variants[] = $variant_id;
      }
    }

    if (!empty($all_data_sets)) {
      $this->queueElements($all_data_sets);
    }
    $this->getQueuedElementCount(TRUE);

    // Remove all sitemap content of variants which did not yield any queue elements.
    foreach ($empty_variants as $empty_variant) {
      $variants[$empty_variant]->deleteContent();
    }

    return $this;
  }

  /**
   * @param string[]|string|null $variants
   * @return $this
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function rebuildQueue($variants = NULL): QueueWorker {
    if (!$this->lock->acquire(static::LOCK_ID)) {
      throw new \RuntimeException('Unable to acquire a lock for sitemap queue rebuilding');
    }
    $this->deleteQueue();
    $this->queue($variants);
    $this->lock->release(static::LOCK_ID);

    return $this;
  }

  protected function queueElements($elements): void {
    $this->queue->createItems($elements);
    $this->state->set('simple_sitemap.queue_items_initial_amount', ($this->state->get('simple_sitemap.queue_items_initial_amount') + count($elements)));
  }

  /**
   * @param string $from
   *
   * @return $this
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function generateSitemap(string $from = self::GENERATE_TYPE_FORM): QueueWorker {

    $this->generatorSettings = [
      'base_url' => $this->settings->get('base_url', ''),
      'xsl' => $this->settings->get('xsl', TRUE),
      'default_variant' => $this->settings->get('default_variant', NULL),
      'skip_untranslated' => $this->settings->get('skip_untranslated', FALSE),
      'remove_duplicates' => $this->settings->get('remove_duplicates', TRUE),
      'excluded_languages' => $this->settings->get('excluded_languages', []),
    ];
    $this->maxLinks = $this->settings->get('max_links');
    $max_execution_time = $this->settings->get('generate_duration', 10000);
    Timer::start('simple_sitemap_generator');

    $this->unstashResults();

    if (!$this->generationInProgress()) {
      $this->rebuildQueue();
    }

    // Acquire a lock for max execution time + 5 seconds. If max_execution time
    // is unlimited then lock for 1 hour.
    $lock_timeout = $max_execution_time > 0 ? ($max_execution_time / 1000) + 5 : static::GENERATE_LOCK_TIMEOUT;
    if (!$this->lock->acquire(static::LOCK_ID, $lock_timeout)) {
      throw new \RuntimeException('Unable to acquire a lock for sitemap generation');
    }

    foreach ($this->queue->yieldItem() as $element) {

      if (!empty($max_execution_time) && Timer::read('simple_sitemap_generator') >= $max_execution_time) {
        break;
      }

      try {
        if ($this->variantProcessedNow === NULL || $element->data['sitemap'] !== $this->variantProcessedNow->id()) {

          if (NULL !== $this->variantProcessedNow) {
            $this->generateVariantChunksFromResults(TRUE);
            $this->publishCurrentVariant();
          }

          $this->variantProcessedNow = $this->entityTypeManager->getStorage('simple_sitemap')->load($element->data['sitemap']);
          $this->processedPaths = [];
        }

        $this->generateResultsFromElement($element);

        if (!empty($this->maxLinks) && count($this->results) >= $this->maxLinks) {
          $this->generateVariantChunksFromResults();
        }
      }
      catch (\Exception $e) {
        watchdog_exception('simple_sitemap', $e);
      }

      $this->queue->deleteItem($element); //todo May want to use deleteItems() instead.
      $this->elementsRemaining--;
    }

    if ($this->getQueuedElementCount() === 0) {
      $this->generateVariantChunksFromResults(TRUE);
      $this->publishCurrentVariant();
    }
    else {
      $this->stashResults();
    }
    $this->lock->release(static::LOCK_ID);

    return $this;
  }

  /**
   * @param $element
   */
  protected function generateResultsFromElement($element): void {
    $results = $this->variantProcessedNow->getType()->getUrlGenerators()[$element->data['url_generator']]
      ->setSitemapVariant($this->variantProcessedNow)
      ->generate($element->data['data']);

    $this->removeDuplicates($results);
    $this->results = array_merge($this->results, $results);
  }

  /**
   * @param array $results
   */
  protected function removeDuplicates(array &$results): void {
    if ($this->generatorSettings['remove_duplicates'] && !empty($results)) {
      $result = $results[key($results)];
      if (isset($result['meta']['path'])) {
        if (isset($this->processedPaths[$result['meta']['path']])) {
          $results = [];
        }
        else {
          $this->processedPaths[$result['meta']['path']] = TRUE;
        }
      }
    }
  }

  /**
   * @param bool $complete
   */
  protected function generateVariantChunksFromResults(bool $complete = FALSE): void {
    if (!empty($this->results)) {
      $processed_results = $this->results;
      $variant_id = $this->variantProcessedNow->id();
      $this->moduleHandler->alter('simple_sitemap_links', $processed_results, $variant_id); // todo Context could be sitemap object instead?
      $this->processedResults = array_merge($this->processedResults, $processed_results);
      $this->results = [];
    }

    if (empty($this->processedResults)) {
      return;
    }

    if (!empty($this->maxLinks)) {
      foreach (array_chunk($this->processedResults, $this->maxLinks, TRUE) as $chunk_links) {
        if ($complete || count($chunk_links) === $this->maxLinks) {
          $this->variantProcessedNow->addChunk($chunk_links);
          $this->processedResults = array_diff_key($this->processedResults, $chunk_links);
        }
      }
    }
    else {
      $this->variantProcessedNow->addChunk($this->processedResults);
      $this->processedResults = [];
    }
  }

  protected function publishCurrentVariant(): void {
    if ($this->variantProcessedNow !== NULL) {
      $this->variantProcessedNow->generateIndex()->publish();
    }
  }

  protected function resetWorker() {
    $this->results = [];
    $this->processedPaths = [];
    $this->processedResults = [];
    $this->variantProcessedNow = NULL;
    $this->elementsTotal = NULL;
    $this->elementsRemaining = NULL;
  }

  /**
   * @return $this
   */
  public function deleteQueue(): QueueWorker {
    $this->queue->deleteQueue();
    SimpleSitemap::purgeContent(NULL, SimpleSitemap::FETCH_BY_STATUS_UNPUBLISHED);
    $this->state->set('simple_sitemap.queue_items_initial_amount', 0);
    $this->state->delete('simple_sitemap.queue_stashed_results');
    $this->resetWorker();

    return $this;
  }

  protected function stashResults(): void {
    $this->state->set('simple_sitemap.queue_stashed_results', [
      'variant' => $this->variantProcessedNow->id(),
      'results' => $this->results,
      'processed_results' => $this->processedResults,
      'processed_paths' => $this->processedPaths,
    ]);
    $this->resetWorker();
  }

  protected function unstashResults(): void {
    if (NULL !== $results = $this->state->get('simple_sitemap.queue_stashed_results')) {
      $this->state->delete('simple_sitemap.queue_stashed_results');
      $this->results = !empty($results['results']) ? $results['results'] : [];
      $this->processedResults = !empty($results['processed_results']) ? $results['processed_results'] : [];
      $this->processedPaths = !empty($results['processed_paths']) ? $results['processed_paths'] : [];
      $this->variantProcessedNow = $this->entityTypeManager->getStorage('simple_sitemap')->load($results['variant']);
    }
  }

  public function getInitialElementCount(): ?int {
    if (NULL === $this->elementsTotal) {
      $this->elementsTotal = (int) $this->state->get('simple_sitemap.queue_items_initial_amount', 0);
    }

    return $this->elementsTotal;
  }

  /**
   * @param bool $force_recount
   *
   * @return int
   */
  public function getQueuedElementCount(bool $force_recount = FALSE): ?int {
    if ($force_recount || NULL === $this->elementsRemaining) {
      $this->elementsRemaining = $this->queue->numberOfItems();
    }

    return $this->elementsRemaining;
  }

  /**
   * @return int
   */
  public function getStashedResultCount(): int {
    $results = $this->state->get('simple_sitemap.queue_stashed_results', []);
    return (!empty($results['results']) ? count($results['results']) : 0)
      + (!empty($results['processed_results']) ? count($results['processed_results']) : 0);
  }

  /**
   * @return int
   */
  public function getProcessedElementCount(): ?int {
    $initial = $this->getInitialElementCount();
    $remaining = $this->getQueuedElementCount();

    return $initial > $remaining ? ($initial - $remaining) : 0;
  }

  /**
   * @return bool
   */
  public function generationInProgress(): bool {
    return 0 < ($this->getQueuedElementCount() + $this->getStashedResultCount());
  }
}

