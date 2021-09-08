<?php

namespace Drupal\simple_sitemap\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\simple_sitemap\Plugin\simple_sitemap\SitemapGenerator\SitemapGeneratorInterface;

interface SimpleSitemapTypeInterface extends ConfigEntityInterface {

  /**
   * @return \Drupal\simple_sitemap\Plugin\simple_sitemap\SitemapGenerator\SitemapGeneratorInterface
   */
  public function getSitemapGenerator(): SitemapGeneratorInterface;

  /**
   * @return \Drupal\simple_sitemap\Plugin\simple_sitemap\UrlGenerator\UrlGeneratorInterface[]
   */
  public function getUrlGenerators(): array;

}
