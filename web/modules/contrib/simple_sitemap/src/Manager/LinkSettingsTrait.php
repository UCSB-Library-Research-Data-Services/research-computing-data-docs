<?php

namespace Drupal\simple_sitemap\Manager;

/**
 * Trait LinkSettingsTrait
 */
trait LinkSettingsTrait {

  /**
   * Supplements all missing link setting with default values.
   *
   * @param array|null &$settings
   * @param array $overrides
   */
  public static function supplementDefaultSettings(&$settings, array $overrides = []): void {
    foreach (self::$linkSettingDefaults as $setting => $value) {
      if (!isset($settings[$setting])) {
        $settings[$setting] = $overrides[$setting] ?? $value;
      }
    }
  }

}
