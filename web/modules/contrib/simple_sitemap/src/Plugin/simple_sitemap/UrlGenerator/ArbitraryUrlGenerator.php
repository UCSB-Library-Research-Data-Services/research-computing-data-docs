<?php

namespace Drupal\simple_sitemap\Plugin\simple_sitemap\UrlGenerator;

use Drupal\simple_sitemap\Logger;
use Drupal\simple_sitemap\Plugin\simple_sitemap\SimpleSitemapPluginBase;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\simple_sitemap\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ArbitraryUrlGenerator
 *
 * @UrlGenerator(
 *   id = "arbitrary",
 *   label = @Translation("Arbitrary URL generator"),
 *   description = @Translation("Generates URLs from data sets collected in the hook_arbitrary_links_alter hook."),
 * )
 */
class ArbitraryUrlGenerator extends UrlGeneratorBase {

  protected $moduleHandler;

  /**
   * ArbitraryUrlGenerator constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\simple_sitemap\Logger $logger
   * @param \Drupal\simple_sitemap\Settings $settings
   * @param \Drupal\Core\Extension\ModuleHandler $module_handler
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    Logger $logger,
    Settings $settings,
    ModuleHandler $module_handler
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $logger,
      $settings,
    );
    $this->moduleHandler = $module_handler;
  }

  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition): SimpleSitemapPluginBase {

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('simple_sitemap.logger'),
      $container->get('simple_sitemap.settings'),
      $container->get('module_handler')
    );
  }

  /**
   * @inheritdoc
   */
  public function getDataSets(): array {
    $arbitrary_links = [];
    $sitemap_variant = $this->sitemapVariant->id();
    $this->moduleHandler->alter('simple_sitemap_arbitrary_links', $arbitrary_links, $sitemap_variant);

    return array_values($arbitrary_links);
  }

  /**
   * @inheritdoc
   */
  protected function processDataSet($data_set): array {
    return $data_set;
  }
}
