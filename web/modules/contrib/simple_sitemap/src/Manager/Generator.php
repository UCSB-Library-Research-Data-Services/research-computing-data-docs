<?php

namespace Drupal\simple_sitemap\Manager;

use Drupal\Core\Lock\LockBackendInterface;
use Drupal\simple_sitemap\Entity\SimpleSitemap;
use Drupal\simple_sitemap\Logger;
use Drupal\simple_sitemap\Queue\QueueWorker;
use Drupal\simple_sitemap\Settings;

/**
 * Main managing service.
 *
 * Capable of setting/loading module settings, queuing elements and generating the
 * sitemap. Services for custom link and entity link generation can be fetched from this
 * service as well.
 */
class Generator {

  use VariantSetterTrait;

  /**
   * @var \Drupal\simple_sitemap\Settings
   */
  protected $settings;

  /**
   * @var \Drupal\simple_sitemap\Queue\QueueWorker
   */
  protected $queueWorker;

  /**
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;

  /**
   * @var \Drupal\simple_sitemap\Logger
   */
  protected $logger;

  /**
   * Simplesitemap constructor.
   *
   * @param \Drupal\simple_sitemap\Settings $settings
   * @param \Drupal\simple_sitemap\Queue\QueueWorker $queue_worker
   * @param \Drupal\Core\Lock\LockBackendInterface|null $lock
   * @param \Drupal\simple_sitemap\Logger|null $logger
   */
  public function __construct(
    Settings $settings,
    QueueWorker $queue_worker,
    LockBackendInterface $lock = NULL,
    Logger $logger = NULL
  ) {
    $this->settings = $settings;
    $this->queueWorker = $queue_worker;
    $this->lock = $lock;
    $this->logger = $logger;
  }

  /**
   * Returns a specific sitemap setting or a default value if setting does not
   * exist.
   *
   * @param string $name
   *  Name of the setting, like 'max_links'.
   *
   * @param mixed $default
   *  Value to be returned if the setting does not exist in the configuration.
   *
   * @return mixed
   *  The current setting from configuration or a default value.
   */
  public function getSetting(string $name, $default = FALSE) {
    return $this->settings->get($name, $default);
  }

  /**
   * Stores a specific sitemap setting in configuration.
   *
   * @param string $name
   *  Setting name, like 'max_links'.
   *
   * @param mixed $setting
   *  The setting to be saved.
   *
   * @return $this
   */
  public function saveSetting(string $name, $setting): Generator {
    $this->settings->save($name, $setting);

    return $this;
  }

  /**
   * Returns a sitemap variant, its index, or its requested chunk.
   *
   * @param int|null $delta
   *  Optional delta of the chunk.
   *
   * @return string|null
   *  If no chunk delta is provided, either the sitemap variant is returned,
   *  or its index in case of a chunked sitemap.
   *  If a chunk delta is provided, the relevant chunk is returned.
   *  Returns null if the sitemap variant is not retrievable from the database.
   */
  public function getSitemap(?int $delta = NULL): ?string {
    /** @var \Drupal\simple_sitemap\Entity\SimpleSitemapInterface $sitemap */
    if (empty($variants = $this->getVariants())) {
      return NULL;
    }
    $sitemap = SimpleSitemap::load(reset($variants));

    return $sitemap ? $sitemap->fromPublished()->toString($delta) : NULL;
  }

  /**
   * Generates all sitemaps.
   *
   * @param string $from
   *  Can be 'form', 'drush', 'cron' and 'backend'.
   *
   * @return $this
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function generateSitemap(string $from = QueueWorker::GENERATE_TYPE_FORM): Generator {
    if (!$this->lock->lockMayBeAvailable(QueueWorker::LOCK_ID)) {
      $this->logger->m('Unable to acquire a lock for sitemap generation.')->log('error')->display('error');
      return $this;
    }
    switch ($from) {
      case QueueWorker::GENERATE_TYPE_FORM:
      case QueueWorker::GENERATE_TYPE_DRUSH;
        $this->queueWorker->batchGenerateSitemap($from);
        break;

      case QueueWorker::GENERATE_TYPE_CRON:
      case QueueWorker::GENERATE_TYPE_BACKEND:
        $this->queueWorker->generateSitemap($from);
        break;
    }

    return $this;
  }

  /**
   * Queues links from currently set variants.
   *
   * @return $this
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function queue(): Generator {
    $this->queueWorker->queue($this->getVariants());

    return $this;
  }

  /**
   * Deletes the queue and queues links from currently set variants.
   *
   * @return $this
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function rebuildQueue(): Generator {
    if (!$this->lock->lockMayBeAvailable(QueueWorker::LOCK_ID)) {
      $this->logger->m('Unable to acquire a lock for sitemap generation.')->log('error')->display('error');
      return $this;
    }
    $this->queueWorker->rebuildQueue($this->getVariants());

    return $this;
  }

  public function entityManager(): EntityManager {
    /** @var \Drupal\simple_sitemap\Manager\EntityManager $entities */
    $entities = \Drupal::service('simple_sitemap.entity_manageer');

    return $entities->setVariants($this->getVariants());
  }

  public function customLinkManager(): CustomLinkManager {
    /** @var \Drupal\simple_sitemap\Manager\CustomLinkManager $custom_links */
    $custom_links = \Drupal::service('simple_sitemap.custom_link_manager');

    return $custom_links->setVariants($this->getVariants());
  }

}
