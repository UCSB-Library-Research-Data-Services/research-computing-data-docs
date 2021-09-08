<?php

namespace Drupal\simple_sitemap\Manager;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\simple_sitemap\Entity\EntityHelper;
use Drupal\simple_sitemap\Logger;
use Drupal\simple_sitemap\Settings;

/**
 * Class EntityManager
 */
class EntityManager {

  use VariantSetterTrait;
  use LinkSettingsTrait;

  protected static $linkSettingDefaults = [
    'index' => FALSE,
    'priority' => '0.5',
    'changefreq' => '',
    'include_images' => FALSE,
  ];

  /**
   * @var \Drupal\simple_sitemap\Entity\EntityHelper
   */
  protected $entityHelper;

  /**
   * @var \Drupal\simple_sitemap\Settings
   */
  protected $settings;

  /**
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\simple_sitemap\Logger
   */
  protected $logger;

  /**
   * Simplesitemap constructor.
   *
   * @param \Drupal\simple_sitemap\Entity\EntityHelper $entity_helper
   * @param \Drupal\simple_sitemap\Settings $settings
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   * @param \Drupal\Core\Database\Connection $database
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\simple_sitemap\Logger|null $logger
   */
  public function __construct(
    EntityHelper $entity_helper,
    Settings $settings,
    ConfigFactory $config_factory,
    Connection $database,
    EntityTypeManagerInterface $entity_type_manager,
    Logger $logger = NULL
  ) {
    $this->entityHelper = $entity_helper;
    $this->settings = $settings;
    $this->configFactory = $config_factory;
    $this->database = $database;
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
  }

  /**
   * Enables sitemap support for an entity type. Enabled entity types show
   * sitemap settings on their bundle setting forms. If an enabled entity type
   * features bundles (e.g. 'node'), it needs to be set up with
   * setBundleSettings() as well.
   *
   * @param string $entity_type_id
   *  Entity type id like 'node'.
   *
   * @return \Drupal\simple_sitemap\Manager\EntityManager
   */
  public function enableEntityType(string $entity_type_id): EntityManager {
    $enabled_entity_types = $this->settings->get('enabled_entity_types');
    if (!in_array($entity_type_id, $enabled_entity_types, TRUE)) {
      $enabled_entity_types[] = $entity_type_id;
      $this->settings->save('enabled_entity_types', $enabled_entity_types);
    }

    return $this;
  }

  /**
   * Disables sitemap support for an entity type. Disabling support for an
   * entity type deletes its sitemap settings permanently and removes sitemap
   * settings from entity forms.
   *
   * @param string $entity_type_id
   *
   * @return \Drupal\simple_sitemap\Manager\EntityManager
   */
  public function disableEntityType(string $entity_type_id): EntityManager {

    // Updating settings.
    $enabled_entity_types = $this->settings->get('enabled_entity_types');
    if (FALSE !== ($key = array_search($entity_type_id, $enabled_entity_types, TRUE))) {
      unset ($enabled_entity_types[$key]);
      $this->settings->save('enabled_entity_types', array_values($enabled_entity_types));
    }

    // Deleting inclusion settings.
    $config_names = $this->configFactory->listAll('simple_sitemap.bundle_settings.');
    foreach ($config_names as $config_name) {
      $config_name_parts = explode('.', $config_name);
      if ($config_name_parts[3] === $entity_type_id) {
        $this->configFactory->getEditable($config_name)->delete();
      }
    }

    // Deleting entity overrides.
    $this->setVariants(TRUE)->removeEntityInstanceSettings($entity_type_id);

    return $this;
  }

  /**
   * Sets settings for bundle or non-bundle entity types. This is done for the
   * currently set variant.
   * Note that this method takes only the first set variant into account. See todo.
   *
   * @param string $entity_type_id
   * @param string|null $bundle_name
   * @param array $settings
   *
   * @return \Drupal\simple_sitemap\Manager\EntityManager
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @todo multiple variants
   * @todo Pass entity object instead of id and bundle.
   */
  public function setBundleSettings(string $entity_type_id, ?string $bundle_name = NULL, array $settings = ['index' => TRUE]): EntityManager {
    if (empty($variants = $this->getVariants(FALSE))) {
      return $this;
    }

    $bundle_name = $bundle_name ?? $entity_type_id;

    if (!empty($old_settings = $this->getBundleSettings($entity_type_id, $bundle_name))) {
      $settings = array_merge($old_settings, $settings);
    }
    self::supplementDefaultSettings($settings);

    if ($settings != $old_settings) {

      // Save new bundle settings to configuration.
      $bundle_settings = $this->configFactory
        ->getEditable("simple_sitemap.bundle_settings.$variants[0].$entity_type_id.$bundle_name");
      foreach ($settings as $setting_key => $setting) {
        $bundle_settings->set($setting_key, $setting);
      }
      $bundle_settings->save();

      if (empty($entity_ids = $this->entityHelper->getEntityInstanceIds($entity_type_id, $bundle_name))) {
        return $this;
      }

      // Delete all entity overrides in case bundle indexation is disabled.
      if (empty($settings['index'])) {
        $this->removeEntityInstanceSettings($entity_type_id, $entity_ids);

        return $this;
      }

      // Delete entity overrides which are identical to new bundle settings.
      // todo Enclose into some sensible method.
      $query = $this->database->select('simple_sitemap_entity_overrides', 'o')
        ->fields('o', ['id', 'inclusion_settings'])
        ->condition('o.entity_type', $entity_type_id)
        ->condition('o.type', $variants[0]);
      if (!empty($entity_ids)) {
        $query->condition('o.entity_id', $entity_ids, 'IN');
      }

      $delete_instances = [];
      foreach ($query->execute()->fetchAll() as $result) {
        $delete = TRUE;
        $instance_settings = unserialize($result->inclusion_settings);
        foreach ($instance_settings as $setting_key => $instance_setting) {
          if ($instance_setting != $settings[$setting_key]) {
            $delete = FALSE;
            break;
          }
        }
        if ($delete) {
          $delete_instances[] = $result->id;
        }
      }
      if (!empty($delete_instances)) {

        // todo Use removeEntityInstanceSettings() instead.
        $this->database->delete('simple_sitemap_entity_overrides')
          ->condition('id', $delete_instances, 'IN')
          ->execute();
      }
    }

    return $this;
  }

  /**
   * Gets settings for bundle or non-bundle entity types. This is done for the
   * currently set variants.
   *
   * @param string|null $entity_type_id
   *  Limit the result set to a specific entity type.
   * @param string|null $bundle_name
   *  Limit the result set to a specific bundle name.
   * @param bool $supplement_defaults
   *  Supplements the result set with default bundle settings.
   * @param bool $multiple_variants
   *  If true, returns an array of results keyed by variant name, otherwise it
   *  returns the result set for the first variant only.
   *
   * @return array|false
   *  Array of settings or array of settings keyed by variant name. False if
   *  entity type does not exist.
   *
   * @todo Pass entity object instead of id and bundle.
   */
  public function getBundleSettings(?string $entity_type_id = NULL, ?string $bundle_name = NULL, bool $supplement_defaults = TRUE, bool $multiple_variants = FALSE) {
    $bundle_name = $bundle_name ?? $entity_type_id;
    $all_bundle_settings = [];

    foreach ($this->getVariants(FALSE) as $variant) {
      if (NULL !== $entity_type_id) {
        $bundle_settings = $this->configFactory
          ->get("simple_sitemap.bundle_settings.$variant.$entity_type_id.$bundle_name")
          ->get();

        if (empty($bundle_settings) && $supplement_defaults) {
          self::supplementDefaultSettings($bundle_settings);
        }
      }
      else {
        $config_names = $this->configFactory->listAll("simple_sitemap.bundle_settings.$variant.");
        $bundle_settings = [];
        foreach ($config_names as $config_name) {
          $config_name_parts = explode('.', $config_name);
          $bundle_settings[$config_name_parts[3]][$config_name_parts[4]] = $this->configFactory->get($config_name)->get();
        }

        // Supplement default bundle settings for all bundles not found in simple_sitemap.bundle_settings.*.* configuration.
        if ($supplement_defaults) {
          foreach ($this->entityHelper->getSupportedEntityTypes() as $type_id => $type_definition) {
            foreach($this->entityHelper->getBundleInfo($type_id) as $bundle => $bundle_definition) {
              if (!isset($bundle_settings[$type_id][$bundle])) {
                self::supplementDefaultSettings($bundle_settings[$type_id][$bundle]);
              }
            }
          }
        }
      }

      if ($multiple_variants) {
        $all_bundle_settings[$variant] = $bundle_settings;
      }
      else {
        return $bundle_settings;
      }
    }

    return $all_bundle_settings;
  }

  /**
   * Removes settings for bundle or a non-bundle entity types. This is done for
   * the currently set variants.
   *
   * @param string|null $entity_type_id
   *  Limit the removal to a specific entity type.
   * @param string|null $bundle_name
   *  Limit the removal to a specific bundle name.
   *
   * @return \Drupal\simple_sitemap\Manager\EntityManager
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @todo Pass entity object instead of id and bundle.
   */
  public function removeBundleSettings(?string $entity_type_id = NULL, ?string $bundle_name = NULL): EntityManager {
    if (empty($variants = $this->getVariants(FALSE))) {
      return $this;
    }

    if (NULL !== $entity_type_id) {
      $bundle_name = $bundle_name ?? $entity_type_id;

      foreach ($variants as $variant) {
        $this->configFactory
          ->getEditable("simple_sitemap.bundle_settings.$variant.$entity_type_id.$bundle_name")->delete();
      }

      if (!empty($entity_ids = $this->entityHelper->getEntityInstanceIds($entity_type_id, $bundle_name))) {
        $this->removeEntityInstanceSettings($entity_type_id, $entity_ids);
      }
    }
    else {
      foreach ($variants as $variant) {
        $config_names = $this->configFactory->listAll("simple_sitemap.bundle_settings.$variant.");
        foreach ($config_names as $config_name) {
          $this->configFactory->getEditable($config_name)->delete();
        }
      }
      $this->removeEntityInstanceSettings();
    }

    return $this;
  }

  /**
   * Overrides sitemap settings for a single entity for the currently set
   * variants.
   *
   * @param string $entity_type_id
   * @param string $id
   * @param array $settings
   *
   * @return \Drupal\simple_sitemap\Manager\EntityManager
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @todo Check functionality (variant setting etc).
   * @todo Pass entity object instead of id and bundle.
   */
  public function setEntityInstanceSettings(string $entity_type_id, string $id, array $settings): EntityManager {
    if (empty($this->getVariants(FALSE))) {
      return $this;
    }

    if (($entity = $this->entityTypeManager->getStorage($entity_type_id)->load($id)) === NULL) {
      // todo exception
      return $this;
    }

    $all_bundle_settings = $this->getBundleSettings(
      $entity_type_id, $this->entityHelper->getEntityInstanceBundleName($entity), TRUE, TRUE
    );

    foreach ($all_bundle_settings as $variant => $bundle_settings) {
      if (!empty($bundle_settings)) {

        // Check if overrides are different from bundle setting before saving.
        $override = FALSE;
        foreach ($settings as $key => $setting) {
          if (!isset($bundle_settings[$key]) || $setting != $bundle_settings[$key]) {
            $override = TRUE;
            break;
          }
        }

        // Save overrides for this entity if something is different.
        if ($override) {
          $this->database->merge('simple_sitemap_entity_overrides')
            ->keys([
              'type' => $variant,
              'entity_type' => $entity_type_id,
              'entity_id' => $id])
            ->fields([
              'type' => $variant,
              'entity_type' => $entity_type_id,
              'entity_id' => $id,
              'inclusion_settings' => serialize(array_merge($bundle_settings, $settings))])
            ->execute();
        }
        // Else unset override.
        else {
          $this->removeEntityInstanceSettings($entity_type_id, $id);
        }
      }
    }

    return $this;
  }

  /**
   * Gets sitemap settings for an entity instance which overrides bundle
   * settings, or gets bundle settings, if they are not overridden. This is
   * done for the currently set variant.
   * Please note, this method takes only the first set
   * variant into account. See todo.
   *
   * @param string $entity_type_id
   * @param string $id
   *
   * @return array|false
   *  Array of entity instance settings or the settings of its bundle. False if
   *  entity type or variant does not exist.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *
   * @todo multiple variants
   * @todo Pass entity object instead of id and bundle.
   */
  public function getEntityInstanceSettings(string $entity_type_id, string $id) {
    if (empty($variants = $this->getVariants(FALSE))) {
      return FALSE;
    }

    $results = $this->database->select('simple_sitemap_entity_overrides', 'o')
      ->fields('o', ['inclusion_settings'])
      ->condition('o.type', reset($variants))
      ->condition('o.entity_type', $entity_type_id)
      ->condition('o.entity_id', $id)
      ->execute()
      ->fetchField();

    if (!empty($results)) {
      return unserialize($results);
    }

    if (($entity = $this->entityTypeManager->getStorage($entity_type_id)->load($id)) === NULL) {
      return FALSE;
    }

    return $this->getBundleSettings(
      $entity_type_id,
      $this->entityHelper->getEntityInstanceBundleName($entity)
    );
  }

  /**
   * Removes sitemap settings for entities that override bundle settings. This
   * is done for the currently set variants.
   *
   * @param string|null $entity_type_id
   *  Limits the removal to a certain entity type.
   * @param string|array|null $entity_ids
   *  Limits the removal to entities with certain IDs.
   *
   * @return \Drupal\simple_sitemap\Manager\EntityManager
   * @todo Pass entity object instead of id and bundle.
   */
  public function removeEntityInstanceSettings(?string $entity_type_id = NULL, $entity_ids = NULL): EntityManager {
    if (empty($variants = $this->getVariants(FALSE))) {
      return $this;
    }

    $query = $this->database->delete('simple_sitemap_entity_overrides')
      ->condition('type', $variants, 'IN');

    if (NULL !== $entity_type_id) {
      $query->condition('entity_type', $entity_type_id);

      if (NULL !== $entity_ids) {
        $query->condition('entity_id', (array) $entity_ids, 'IN');
      }
    }

    $query->execute();

    return $this;
  }

  /**
   * Checks if an entity bundle (or a non-bundle entity type) is set to be
   * indexed for any of the currently set variants.
   *
   * @param string $entity_type_id
   * @param string|null $bundle_name
   *
   * @return bool
   *
   */
  public function bundleIsIndexed(string $entity_type_id, ?string $bundle_name = NULL): bool {
    foreach ($this->getBundleSettings($entity_type_id, $bundle_name, FALSE, TRUE) as $settings) {
      if (!empty($settings['index'])) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Checks if an entity type is enabled in the sitemap settings.
   *
   * @param string $entity_type_id
   *
   * @return bool
   *
   */
  public function entityTypeIsEnabled(string $entity_type_id): bool {
    return in_array($entity_type_id, $this->settings->get('enabled_entity_types', []), TRUE);
  }

}
