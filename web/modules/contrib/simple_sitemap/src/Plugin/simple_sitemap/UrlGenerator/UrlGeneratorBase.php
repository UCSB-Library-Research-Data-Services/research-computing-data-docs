<?php

namespace Drupal\simple_sitemap\Plugin\simple_sitemap\UrlGenerator;

use Drupal\simple_sitemap\Exception\SkipElementException;
use Drupal\simple_sitemap\Plugin\simple_sitemap\SimpleSitemapPluginBase;
use Drupal\simple_sitemap\Entity\SimpleSitemapInterface;
use Drupal\simple_sitemap\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\simple_sitemap\Logger;

/**
 * Class UrlGeneratorBase
 */
abstract class UrlGeneratorBase extends SimpleSitemapPluginBase implements UrlGeneratorInterface {

  /**
   * @var \Drupal\simple_sitemap\Logger
   */
  protected $logger;

  /**
   * @var \Drupal\simple_sitemap\Settings
   */
  protected $settings;

  /**
   * @var \Drupal\simple_sitemap\Entity\SimpleSitemapInterface
   */
  protected $sitemapVariant;

  /**
   * UrlGeneratorBase constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\simple_sitemap\Logger $logger
   * @param \Drupal\simple_sitemap\Settings $settings
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    Logger $logger,
    Settings $settings
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger;
    $this->settings = $settings;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): SimpleSitemapPluginBase {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('simple_sitemap.logger'),
      $container->get('simple_sitemap.settings')
    );
  }

  /**
   * @param \Drupal\simple_sitemap\Entity\SimpleSitemapInterface $sitemap_variant
   *
   * @return $this
   */
  public function setSitemapVariant(SimpleSitemapInterface $sitemap_variant): UrlGeneratorInterface {
    $this->sitemapVariant = $sitemap_variant;

    return $this;
  }

  /**
   * @param string $url
   *
   * @return string
   */
  protected function replaceBaseUrlWithCustom(string $url): string {
    return !empty($base_url = $this->settings->get('base_url'))
      ? str_replace($GLOBALS['base_url'], $base_url, $url)
      : $url;
  }

  /**
   * @return mixed
   *
   * @todo Throw and catch SkipElementException here and children.
   */
  abstract public function getDataSets(): array;

  /**
   * @param $data_set
   * @return mixed
   */
  abstract protected function processDataSet($data_set): array;

  /**
   * @param $data_set
   * @return array
   *
   * @todo catch SkipElementException here and children.
   */
  public function generate($data_set): array {
    try {
      return [$this->processDataSet($data_set)];
    }
    catch (SkipElementException $e) {
      return [];
    }
  }
}
