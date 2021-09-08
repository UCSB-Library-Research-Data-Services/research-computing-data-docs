<?php

namespace Drupal\simple_sitemap\Plugin\simple_sitemap;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Interface SimpleSitemapPluginInterface
 */
interface SimpleSitemapPluginInterface extends ContainerFactoryPluginInterface {

  public function label(): string;

  public function description(): string;

  public function settings(): array;
}
