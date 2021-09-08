<?php

namespace Drupal\simple_sitemap\Manager;

use Drupal\Core\Path\PathValidator;
use Drupal\Core\Config\ConfigFactory;

/**
 * Class CustomLinkManager
 */
class CustomLinkManager {

  use VariantSetterTrait;
  use LinkSettingsTrait;

  /**
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * @var \Drupal\Core\Path\PathValidator
   */
  protected $pathValidator;

  /**
   * @var array
   */
  protected static $linkSettingDefaults = ['priority' => '0.5', 'changefreq' => '',];

  /**
   * CustomLinks constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   * @param \Drupal\Core\Path\PathValidator $path_validator
   */
  public function __construct(
    ConfigFactory $config_factory,
    PathValidator $path_validator
  ) {
    $this->configFactory = $config_factory;
    $this->pathValidator = $path_validator;
  }

  /**
   * Stores a custom path along with its settings to configuration for the
   * currently set variants.
   *
   * @param string $path
   * @param array $settings
   *  Settings that are not provided are supplemented by defaults.
   *
   * @return \Drupal\simple_sitemap\Manager\CustomLinkManager
   * @todo Validate $settings and throw exceptions
   */
  public function add(string $path, array $settings = []): CustomLinkManager {
    if (empty($variants = $this->getVariants(FALSE))) {
      return $this;
    }

    if (!(bool) $this->pathValidator->getUrlIfValidWithoutAccessCheck($path)) {
      // todo: log error.
      return $this;
    }
    if ($path[0] !== '/') {
      // todo: log error.
      return $this;
    }

    $variant_links = $this->get(NULL, FALSE, TRUE);
    foreach ($variants as $variant) {
      $links = [];
      $link_key = 0;
      if (isset($variant_links[$variant])) {
        $links = $variant_links[$variant];
        $link_key = count($links);
        foreach ($links as $key => $link) {
          if ($link['path'] === $path) {
            $link_key = $key;
            break;
          }
        }
      }

      $links[$link_key] = ['path' => $path] + $settings;
      $this->configFactory->getEditable("simple_sitemap.custom_links.$variant")
        ->set('links', $links)->save();
    }

    return $this;
  }

  /**
   * Gets custom link settings for the currently set variants.
   *
   * @param string|null $path
   *  Limits the result set by an internal path.
   * @param bool $supplement_defaults
   *  Supplements the result set with default custom link settings.
   * @param bool $multiple_variants
   *  If true, returns an array of results keyed by variant name, otherwise it
   *  returns the result set for the first variant only.
   *
   * @return array|mixed|null
   *
   */
  public function get(?string $path = NULL, bool $supplement_defaults = TRUE, bool $multiple_variants = FALSE): array {
    $all_custom_links = [];
    foreach ($this->getVariants(FALSE) as $variant) {
      $custom_links = $this->configFactory
        ->get("simple_sitemap.custom_links.$variant")
        ->get('links');

      $custom_links = !empty($custom_links) ? $custom_links : [];

      if (!empty($custom_links) && $path !== NULL) {
        foreach ($custom_links as $key => $link) {
          if ($link['path'] !== $path) {
            unset($custom_links[$key]);
          }
        }
      }

      if (!empty($custom_links) && $supplement_defaults) {
        foreach ($custom_links as $i => $link_settings) {
          self::supplementDefaultSettings($link_settings);
          $custom_links[$i] = $link_settings;
        }
      }

      $custom_links = $path !== NULL && !empty($custom_links)
        ? array_values($custom_links)[0]
        : array_values($custom_links);


      if (!empty($custom_links)) {
        if ($multiple_variants) {
          $all_custom_links[$variant] = $custom_links;
        }
        else {
          return $custom_links;
        }
      }
    }

    return $all_custom_links;
  }

  /**
   * Removes custom links from currently set variants.
   *
   * @param array|string|null $paths
   *  Limits the removal to certain paths.
   *
   * @return \Drupal\simple_sitemap\Manager\CustomLinkManager
   */
  public function remove($paths = NULL): CustomLinkManager {
    if (empty($variants = $this->getVariants(FALSE))) {
      return $this;
    }

    if (NULL === $paths) {
      foreach ($variants as $variant) {
        $this->configFactory
          ->getEditable("simple_sitemap.custom_links.$variant")->delete();
      }
    }
    else {
      $variant_links = $this->get(NULL, FALSE, TRUE);
      foreach ($variant_links as $variant => $links) {
        $custom_links = $links;
        $save = FALSE;
        foreach ((array) $paths  as $path) {
          foreach ($custom_links as $key => $link) {
            if ($link['path'] === $path) {
              unset($custom_links[$key]);
              $save = TRUE;
              break 2;
            }
          }
        }
        if ($save) {
          $this->configFactory->getEditable("simple_sitemap.custom_links.$variant")
            ->set('links', array_values($custom_links))->save();
        }
      }
    }

    return $this;
  }

}
