<?php

namespace Drupal\simple_sitemap\Plugin\simple_sitemap;

use Drupal\Core\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SimpleSitemapPluginBase
 */
abstract class SimpleSitemapPluginBase extends PluginBase implements SimpleSitemapPluginInterface {

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): SimpleSitemapPluginBase {
    return new static($configuration, $plugin_id, $plugin_definition);
  }

  public function label(): string {
    return $this->getPluginDefinition()['label'];
  }

  public function description(): string {
    return $this->getPluginDefinition()['description'];
  }

  public function settings(): array {
    return $this->getPluginDefinition()['settings'];
  }
}
