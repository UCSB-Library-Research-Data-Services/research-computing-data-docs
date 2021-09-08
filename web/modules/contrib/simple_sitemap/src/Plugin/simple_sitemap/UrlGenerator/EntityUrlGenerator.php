<?php

namespace Drupal\simple_sitemap\Plugin\simple_sitemap\UrlGenerator;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Url;
use Drupal\Core\Cache\MemoryCache\MemoryCacheInterface;
use Drupal\simple_sitemap\Entity\EntityHelper;
use Drupal\simple_sitemap\Exception\SkipElementException;
use Drupal\simple_sitemap\Logger;
use Drupal\simple_sitemap\Manager\EntityManager;
use Drupal\simple_sitemap\Plugin\simple_sitemap\SimpleSitemapPluginBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\simple_sitemap\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EntityUrlGenerator
 *
 * @UrlGenerator(
 *   id = "entity",
 *   label = @Translation("Entity URL generator"),
 *   description = @Translation("Generates URLs for entity bundles and bundle overrides."),
 * )
 */
class EntityUrlGenerator extends EntityUrlGeneratorBase {

  /**
   * @var \Drupal\simple_sitemap\Plugin\simple_sitemap\UrlGenerator\UrlGeneratorManager
   */
  protected $urlGeneratorManager;

  /**
   * @var integer
   */
  protected $entitiesPerDataset;

  /**
   * @var \Drupal\Core\Cache\MemoryCache\MemoryCacheInterface
   */
  protected $entityMemoryCache;

  /**
   * @var \Drupal\simple_sitemap\Manager\EntityManager
   */
  protected $entitiesManager;


  /**
   * EntityUrlGenerator constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\simple_sitemap\Logger $logger
   * @param \Drupal\simple_sitemap\Settings $settings
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\simple_sitemap\Manager\EntityManager $entities_manager
   * @param \Drupal\simple_sitemap\Entity\EntityHelper $entity_helper
   * @param \Drupal\simple_sitemap\Plugin\simple_sitemap\UrlGenerator\UrlGeneratorManager $url_generator_manager
   * @param \Drupal\Core\Cache\MemoryCache\MemoryCacheInterface $memory_cache
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    Logger $logger,
    Settings $settings,
    LanguageManagerInterface $language_manager,
    EntityTypeManagerInterface $entity_type_manager,
    EntityHelper $entity_helper,
    EntityManager $entities_manager,
    UrlGeneratorManager $url_generator_manager,
    MemoryCacheInterface $memory_cache
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $logger,
      $settings,
      $language_manager,
      $entity_type_manager,
      $entity_helper
    );
    $this->entitiesManager = $entities_manager;
    $this->urlGeneratorManager = $url_generator_manager;
    $this->entityMemoryCache = $memory_cache;
    $this->entitiesPerDataset = $this->settings->get('entities_per_queue_item', 50);
  }

  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition): SimpleSitemapPluginBase {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('simple_sitemap.logger'),
      $container->get('simple_sitemap.settings'),
      $container->get('language_manager'),
      $container->get('entity_type.manager'),
      $container->get('simple_sitemap.entity_helper'),
      $container->get('simple_sitemap.entity_manageer'),
      $container->get('plugin.manager.simple_sitemap.url_generator'),
      $container->get('entity.memory_cache')
    );
  }

  /**
   * @inheritdoc
   */
  public function getDataSets(): array {
    $data_sets = [];
    $sitemap_entity_types = $this->entityHelper->getSupportedEntityTypes();

    foreach ($this->entitiesManager->setVariants($this->sitemapVariant->id())->getBundleSettings() as $entity_type_name => $bundles) {
      if (!isset($sitemap_entity_types[$entity_type_name])) {
        continue;
      }

      if ($this->isOverwrittenForEntityType($entity_type_name)) {
        continue;
      }

      $entityTypeStorage = $this->entityTypeManager->getStorage($entity_type_name);
      $keys = $sitemap_entity_types[$entity_type_name]->getKeys();

      foreach ($bundles as $bundle_name => $bundle_settings) {
        if ($bundle_settings['index']) {
          $query = $entityTypeStorage->getQuery();

          if (empty($keys['id'])) {
            $query->sort($keys['id']);
          }
          if (!empty($keys['bundle'])) {
            $query->condition($keys['bundle'], $bundle_name);
          }
          if (!empty($keys['status'])) {
            $query->condition($keys['status'], 1);
          }

          // Shift access check to EntityUrlGeneratorBase for language
          // specific access.
          // See https://www.drupal.org/project/simple_sitemap/issues/3102450.
          $query->accessCheck(FALSE);

          $data_set = [
            'entity_type' => $entity_type_name,
            'id' => [],
          ];
          foreach ($query->execute() as $entity_id) {
            $data_set['id'][] = $entity_id;
            if (count($data_set['id']) >= $this->entitiesPerDataset) {
              $data_sets[] = $data_set;
              $data_set['id'] = [];
            }
          }
          // Add the last data set if there are some IDs gathered.
          if (!empty($data_set['id'])) {
            $data_sets[] = $data_set;
          }
        }
      }
    }

    return $data_sets;
  }

  /**
   * Check if another plugin overrides this plugin's generation for given entity type.
   *
   * @param string $entity_type_name
   *
   * @return bool
   */
  protected function isOverwrittenForEntityType(string $entity_type_name): bool {
    foreach ($this->urlGeneratorManager->getDefinitions() as $plugin) {
      if (isset($plugin['settings']['overrides_entity_type'])
        && $plugin['settings']['overrides_entity_type'] === $entity_type_name) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * @inheritdoc
   */
  protected function processDataSet($data_set): array {
    foreach ($this->entityTypeManager->getStorage($data_set['entity_type'])->loadMultiple((array) $data_set['id']) as $entity) {
      try {
        $paths[] = $this->processEntity($entity);
      }
      catch (SkipElementException $e) {
        continue;
      }
    }

    return $paths ?? [];
  }

  protected function processEntity(ContentEntityInterface $entity): array {
    $entity_settings = $this->entitiesManager
      ->setVariants($this->sitemapVariant->id())
      ->getEntityInstanceSettings($entity->getEntityTypeId(), $entity->id());

    if (empty($entity_settings['index'])) {
      throw new SkipElementException();
    }

    $url_object = $entity->toUrl()->setAbsolute();

    // Do not include external paths.
    if (!$url_object->isRouted()) {
      throw new SkipElementException();
    }

    return [
      'url' => $url_object,
      'lastmod' => method_exists($entity, 'getChangedTime')
        ? date('c', $entity->getChangedTime())
        : NULL,
      'priority' => $entity_settings['priority'] ?? NULL,
      'changefreq' => !empty($entity_settings['changefreq']) ? $entity_settings['changefreq'] : NULL,
      'images' => !empty($entity_settings['include_images'])
        ? $this->getEntityImageData($entity)
        : [],

      // Additional info useful in hooks.
      'meta' => [
        'path' => $url_object->getInternalPath(),
        'entity_info' => [
          'entity_type' => $entity->getEntityTypeId(),
          'id' => $entity->id(),
        ],
      ]
    ];
  }

  /**
   * @inheritdoc
   */
  public function generate($data_set): array {
    $path_data_sets = $this->processDataSet($data_set);
    $url_variant_sets = [];
    foreach ($path_data_sets as $path_data) {
      if (isset($path_data['url']) && $path_data['url'] instanceof Url) {
        $url_object = $path_data['url'];
        unset($path_data['url']);
        $url_variant_sets[] = $this->getUrlVariants($path_data, $url_object);
      }
    }

    // Make sure to clear entity memory cache so it does not build up resulting
    // in a constant increase of memory.
    // See https://www.drupal.org/project/simple_sitemap/issues/3170261 and
    // https://www.drupal.org/project/simple_sitemap/issues/3202233
    if ($this->entityTypeManager->getDefinition($data_set['entity_type'])->isStaticallyCacheable()) {
      $this->entityMemoryCache->deleteAll();
    }

    return array_merge([], ...$url_variant_sets);
  }

}
