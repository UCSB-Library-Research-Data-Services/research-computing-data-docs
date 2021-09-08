<?php

namespace Drupal\simple_sitemap;

use Drupal\Core\Config\ConfigFactory;

/**
 * Class Settings
 */
class Settings {

  /**
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;


  /**
   * SimpleSitemapSettings constructor.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   */
  public function __construct(ConfigFactory $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * Returns a specific sitemap setting or a default value if setting does not
   * exist.
   *
   * @param string $name
   *  Name of the setting, like 'max_links'.
   * @param mixed $default
   *  Value to be returned if the setting does not exist in the configuration.
   *
   * @return mixed
   *  The current setting from configuration or a default value.
   */
  public function get(string $name, $default = FALSE) { // todo Why not NULL?
    $setting = $this->configFactory
      ->get('simple_sitemap.settings')
      ->get($name);

    return $setting ?? $default;
  }

  public function getAll() {
    return $this->configFactory
      ->get('simple_sitemap.settings')
      ->get();
  }

  /**
   * Stores a specific sitemap setting in configuration.
   *
   * @param string $name
   *  Setting name, like 'max_links'.
   * @param mixed $setting
   *  The setting to be saved.
   *
   * @return $this
   */
  public function save(string $name, $setting): Settings {
    $this->configFactory->getEditable('simple_sitemap.settings')
      ->set($name, $setting)->save();

    return $this;
  }
}
