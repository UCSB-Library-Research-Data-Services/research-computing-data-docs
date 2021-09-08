<?php

namespace Drupal\simple_sitemap\Entity;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\MemoryCache\MemoryCacheInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\simple_sitemap\Exception\SitemapNotExistsException;
use Drupal\simple_sitemap\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SimpleSitemapStorage extends ConfigEntityStorage {
  public const SITEMAP_INDEX_DELTA = 0;
  public const SITEMAP_CHUNK_FIRST_DELTA = 1;

  protected const SITEMAP_PUBLISHED = 1;
  protected const SITEMAP_UNPUBLISHED = 0;

  protected $database;

  protected $time;

  protected $entityTypeManager;

  protected $settings;

  public function __construct(EntityTypeInterface $entity_type, ConfigFactoryInterface $config_factory, UuidInterface $uuid_service, LanguageManagerInterface $language_manager, MemoryCacheInterface $memory_cache, Connection $database, TimeInterface $time, EntityTypeManagerInterface $entity_type_manager, Settings $settings) {
    parent::__construct($entity_type, $config_factory, $uuid_service, $language_manager, $memory_cache);
    $this->database = $database;
    $this->time = $time;
    $this->entityTypeManager = $entity_type_manager;
    $this->settings = $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('config.factory'),
      $container->get('uuid'),
      $container->get('language_manager'),
      $container->get('entity.memory_cache'),
      $container->get('database'),
      $container->get('datetime.time'),
      $container->get('entity_type.manager'),
      $container->get('simple_sitemap.settings')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @todo Improve performance of his method.
   */
  protected function doDelete($entities) {
    $default_variant = $this->settings->get('default_variant');

    /** @var \Drupal\simple_sitemap\Entity\SimpleSitemapInterface[] $entities */
    foreach ($entities as $entity) {

      // Remove sitemap content.
      $this->deleteContent($entity);

      // Unset default variant setting if necessary.
      if ($default_variant === $entity->id()) {
        $this->settings->save('default_variant', NULL);
      }

      // Remove bundle settings.
      foreach ($this->configFactory->listAll("simple_sitemap.bundle_settings.{$entity->id()}.") as $config_name) {
        $this->configFactory->getEditable($config_name)->delete();
      }

      // Remove custom links.
      foreach ($this->configFactory->listAll("simple_sitemap.custom_links.{$entity->id()}") as $config_name) {
        $this->configFactory->getEditable($config_name)->delete();
      }

      // Remove bundle settings entity overrides.
      $this->database->delete('simple_sitemap_entity_overrides')->condition('type', $entity->id())->execute();
    }

    parent::doDelete($entities);
  }

  /**
   * Loads all sitemaps, sorted by their weight.
   *
   * {@inheritdoc}
   */
  protected function doLoadMultiple(?array $ids = NULL): array {
    $sitemaps = parent::doLoadMultiple($ids);
    uasort($sitemaps, [SimpleSitemap::class, 'sort']);

    return $sitemaps;
  }

  public function loadByProperties(array $values = []): array {
    $sitemaps = parent::loadByProperties($values);
    uasort($sitemaps, [SimpleSitemap::class, 'sort']);

    return $sitemaps;
  }

  public function create(array $values = []) {
    if (isset($values['id']) && ($sitemap = SimpleSitemap::load($values['id'])) !== NULL) {
      foreach (['type', 'label', 'weight'] as $property) {
        if (isset($values[$property])) {
          $sitemap->set('type', $values[$property]);
        }
      }
      return $sitemap;
    }

    return parent::create($values);
  }

  /**
   * {@inheritdoc}
   */
  protected function doSave($id, EntityInterface $entity) {
    /** @var SimpleSitemapInterface $entity */
    if (!preg_match('/^[\w\-_]+$/', $id)) {
      throw new \InvalidArgumentException("The sitemap ID can only include alphanumeric characters, dashes and underscores.");
    }

    if ($entity->get('type') === NULL || $entity->get('type') === '') {
      throw new \InvalidArgumentException("The sitemap must define its sitemap type information.");
    }

    if ($this->entityTypeManager->getStorage('simple_sitemap_type')->load($entity->get('type')) === NULL) {
      throw new \InvalidArgumentException("Sitemap type {$entity->get('type')} does not exist.");
    }

    if ($entity->label() === NULL || $entity->label() === '') {
      $entity->set('label', $id);
    }

    if ($entity->get('weight') === NULL || $entity->get('weight') === '') {
      $entity->set('weight', 0);
    }

    return parent::doSave($id, $entity);
  }

  /*
   * @todo Costs too much.
   */
  protected function getChunkData(SimpleSitemapInterface $entity) {
    return \Drupal::database()->select('simple_sitemap', 's')
      ->fields('s', ['id', 'type', 'delta', 'sitemap_created', 'status', 'link_count'])
      ->condition('s.type', $entity->id())
      ->execute()
      ->fetchAllAssoc('id');
  }

  public function publish(SimpleSitemap $entity): void {
    $unpublished_chunk = $this->database->query('SELECT MAX(id) FROM {simple_sitemap} WHERE type = :type AND status = :status', [
      ':type' => $entity->id(), ':status' => self::SITEMAP_UNPUBLISHED
    ])->fetchField();

    // Only allow publishing a sitemap variant if there is an unpublished
    // sitemap variant, as publishing involves deleting the currently published
    // variant.
    if (FALSE !== $unpublished_chunk) {
      $this->database->delete('simple_sitemap')->condition('type', $entity->id())->condition('status', self::SITEMAP_PUBLISHED)->execute();
      $this->database->query('UPDATE {simple_sitemap} SET status = :status WHERE type = :type', [':type' => $entity->id(), ':status' => self::SITEMAP_PUBLISHED]);
    }
  }

  public function deleteContent(SimpleSitemap $entity): void {
    $this->purgeContent($entity->id());
  }

  public function addChunk(SimpleSitemapInterface $entity, string $xml, $link_count): void {
    $highest_delta = $this->database->query('SELECT MAX(delta) FROM {simple_sitemap} WHERE type = :type AND status = :status', [':type' => $entity->id(), ':status' => self::SITEMAP_UNPUBLISHED])
      ->fetchField();

    $this->database->insert('simple_sitemap')->fields([
      'delta' => NULL === $highest_delta ? self::SITEMAP_CHUNK_FIRST_DELTA : $highest_delta + 1,
      'type' =>  $entity->id(),
      'sitemap_string' => $xml,
      'sitemap_created' => $this->time->getRequestTime(),
      'status' => 0,
      'link_count' => $link_count,
    ])->execute();
  }

  public function generateIndex(SimpleSitemapInterface $entity, string $xml): void {
    $this->database->merge('simple_sitemap')
      ->keys([
        'delta' => self::SITEMAP_INDEX_DELTA,
        'type' => $entity->id(),
        'status' => 0
      ])
      ->insertFields([
        'delta' => self::SITEMAP_INDEX_DELTA,
        'type' =>  $entity->id(),
        'sitemap_string' => $xml,
        'sitemap_created' => $this->time->getRequestTime(),
        'status' => 0,
      ])
      ->updateFields([
        'sitemap_string' => $xml,
        'sitemap_created' => $this->time->getRequestTime(),
      ])
      ->execute();
  }

  public function getChunkCount(SimpleSitemap $entity, ?bool $status = SimpleSitemap::FETCH_BY_STATUS_PUBLISHED_UNPUBLISHED): int {
    $query = $this->database->select('simple_sitemap', 's')
      ->condition('s.type', $entity->id())
      ->condition('s.delta', self::SITEMAP_INDEX_DELTA, '<>');

    if ($status !== SimpleSitemap::FETCH_BY_STATUS_PUBLISHED_UNPUBLISHED) {
      $query->condition('s.status', $status);
    }

    return (int) $query->countQuery()->execute()->fetchField();
  }

  /**
   * @todo Duplicate query.
   */
  public function getChunk(SimpleSitemap $entity, ?bool $status, int $delta = SimpleSitemapStorage::SITEMAP_CHUNK_FIRST_DELTA): string {
    if ($delta === self::SITEMAP_INDEX_DELTA) {
      throw new SitemapNotExistsException('The sitemap chunk delta needs to be higher than 0.');
    }

    return $this->getSitemapString($entity, $this->getIdByDelta($entity, $delta, $status), $status);
  }

  public function hasIndex(SimpleSitemap $entity, bool $status): bool {
    try {
      $this->getIdByDelta($entity, self::SITEMAP_INDEX_DELTA, $status);
      return TRUE;
    }
    catch (SitemapNotExistsException $e) {
      return FALSE;
    }
  }

  /**
   * @todo Duplicate query.
   */
  public function getIndex(SimpleSitemap $entity, ?bool $status): string {
    return $this->getSitemapString($entity, $this->getIdByDelta($entity, self::SITEMAP_INDEX_DELTA, $status), $status );
  }

  protected function getIdByDelta(SimpleSitemap $entity, int $delta, bool $status): int {
    foreach ($this->getChunkData($entity) as $chunk) {
      if ($chunk->delta == $delta && $chunk->status == $status) {
        return $chunk->id;
      }
    }

    throw new SitemapNotExistsException();
  }

  protected function getSitemapString(SimpleSitemap $entity, int $id, ?bool $status): string {
    $chunk_data = $this->getChunkData($entity);
    if (!isset($chunk_data[$id])) {
      throw new SitemapNotExistsException();
    }

    if (empty($chunk_data[$id]->sitemap_string)) {
      $query = $this->database->select('simple_sitemap', 's')
        ->fields('s', ['sitemap_string'])
        ->condition('status', $status)
        ->condition('id', $id);

      $chunk_data[$id]->sitemap_string = $query->execute()->fetchField();
    }

    return $chunk_data[$id]->sitemap_string;
  }

  public function status(SimpleSitemap $entity): int {
    foreach ($this->getChunkData($entity) as $chunk) {
      $status[$chunk->status] = $chunk->status;
    }

    if (!isset($status)) {
      return SimpleSitemap::SITEMAP_UNPUBLISHED;
    }

    if (count($status) === 1) {
      return (int) reset($status) === self::SITEMAP_UNPUBLISHED
        ? SimpleSitemap::SITEMAP_UNPUBLISHED
        : SimpleSitemap::SITEMAP_PUBLISHED;
    }

    return SimpleSitemap::SITEMAP_PUBLISHED_GENERATING;
  }

  public function getCreated(SimpleSitemap $entity, ?bool $status = SimpleSitemap::FETCH_BY_STATUS_PUBLISHED_UNPUBLISHED): ?string {
    foreach ($this->getChunkData($entity) as $chunk) {
      if ($status === SimpleSitemap::FETCH_BY_STATUS_PUBLISHED_UNPUBLISHED || $chunk->status == $status) {
        return $chunk->sitemap_created;
      }
    }

    return NULL;
  }

  public function getLinkCount(SimpleSitemap $entity, ?bool $status = SimpleSitemap::FETCH_BY_STATUS_PUBLISHED_UNPUBLISHED): int {
    $count = 0;
    foreach ($this->getChunkData($entity) as $chunk) {
      if ($chunk->delta != self::SITEMAP_INDEX_DELTA
        && ($status === SimpleSitemap::FETCH_BY_STATUS_PUBLISHED_UNPUBLISHED || $chunk->status == $status)) {
        $count += (int) $chunk->link_count;
      }
    }

    return $count;
  }

  public function purgeContent($variants = NULL, ?bool $status = SimpleSitemap::FETCH_BY_STATUS_PUBLISHED_UNPUBLISHED): void {
    $query = \Drupal::database()->delete('simple_sitemap');
    if ($status !== SimpleSitemap::FETCH_BY_STATUS_PUBLISHED_UNPUBLISHED) {
      $query->condition('status', $status);
    }
    if ($variants !== NULL) {
      $query->condition('type', (array) $variants, 'IN');
    }
    $query->execute();
  }

}
