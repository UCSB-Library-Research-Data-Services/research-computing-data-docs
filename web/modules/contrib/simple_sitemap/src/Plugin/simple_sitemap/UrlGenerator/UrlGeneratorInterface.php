<?php

namespace Drupal\simple_sitemap\Plugin\simple_sitemap\UrlGenerator;

use Drupal\simple_sitemap\Entity\SimpleSitemapInterface;
use Drupal\simple_sitemap\Plugin\simple_sitemap\SimpleSitemapPluginInterface;

/**
 * Interface UrlGeneratorInterface
 */
interface UrlGeneratorInterface extends SimpleSitemapPluginInterface {

  public function setSitemapVariant(SimpleSitemapInterface $sitemap_variant): UrlGeneratorInterface;

  public function getDataSets(): array;

  public function generate($data_set): array;
}
