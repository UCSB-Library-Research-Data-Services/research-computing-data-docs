<?php

namespace Drupal\simple_sitemap\Plugin\simple_sitemap\SitemapGenerator;

use Drupal\simple_sitemap\Entity\SimpleSitemapInterface;
use Drupal\simple_sitemap\Plugin\simple_sitemap\SimpleSitemapPluginInterface;

/**
 * Interface SitemapGeneratorInterface
 */
interface SitemapGeneratorInterface extends SimpleSitemapPluginInterface {

  public function setSitemapVariant(SimpleSitemapInterface $sitemap): SitemapGeneratorInterface;

  public function getChunkXml(array $links): string;

  public function getIndexXml(): string;
}
