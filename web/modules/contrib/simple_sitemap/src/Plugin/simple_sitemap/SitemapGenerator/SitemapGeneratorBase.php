<?php

namespace Drupal\simple_sitemap\Plugin\simple_sitemap\SitemapGenerator;

use Drupal\simple_sitemap\Plugin\simple_sitemap\SimpleSitemapPluginBase;
use Drupal\simple_sitemap\Entity\SimpleSitemapInterface;
use Drupal\simple_sitemap\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandler;

/**
 * Class SitemapGeneratorBase
 */
abstract class SitemapGeneratorBase extends SimpleSitemapPluginBase implements SitemapGeneratorInterface {

  protected const XMLNS = 'http://www.sitemaps.org/schemas/sitemap/0.9';

  /**
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * @var \Drupal\simple_sitemap\Settings
   */
  protected $settings;

  /**
   * @var \Drupal\simple_sitemap\Plugin\simple_sitemap\SitemapGenerator\SitemapWriter
   */
  protected $writer;

  /**
   * @var \Drupal\simple_sitemap\Entity\SimpleSitemapInterface
   */
  protected $sitemapVariant;

  /**
   * @var array
   */
  protected static $indexAttributes = [
    'xmlns' => self::XMLNS,
  ];

  /**
   * SitemapGeneratorBase constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Extension\ModuleHandler $module_handler
   * @param \Drupal\simple_sitemap\Plugin\simple_sitemap\SitemapGenerator\SitemapWriter $sitemap_writer
   * @param \Drupal\simple_sitemap\Settings $settings
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ModuleHandler $module_handler,
    SitemapWriter $sitemap_writer,
    Settings $settings
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleHandler = $module_handler;
    $this->writer = $sitemap_writer;
    $this->settings = $settings;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): SimpleSitemapPluginBase {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler'),
      $container->get('simple_sitemap.sitemap_writer'),
      $container->get('simple_sitemap.settings')
    );
  }

  /**
   * @param \Drupal\simple_sitemap\Entity\SimpleSitemapInterface $sitemap
   *
   * @return $this
   */
  public function setSitemapVariant(SimpleSitemapInterface $sitemap): SitemapGeneratorInterface  {
    $this->sitemapVariant = $sitemap;

    return $this;
  }

  /**
   * @param array $links
   * @return string
   */
  abstract public function getChunkXml(array $links): string;

  /**
   * @return string
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function getIndexXml(): string {
    $this->writer->openMemory();
    $this->writer->setIndent(TRUE);
    $this->writer->startSitemapDocument();

    // Add the XML stylesheet to document if enabled.
    if ($this->settings->get('xsl')) {
      $this->writer->writeXsl();
    }

    $this->writer->writeGeneratedBy();
    $this->writer->startElement('sitemapindex');

    // Add attributes to document.
    $attributes = self::$indexAttributes;
    $sitemap_variant = $this->sitemapVariant;
    $this->moduleHandler->alter('simple_sitemap_index_attributes', $attributes, $sitemap_variant);
    foreach ($attributes as $name => $value) {
      $this->writer->writeAttribute($name, $value);
    }

    // Add sitemap chunk locations to document.
    for ($delta = 1; $delta <= $this->sitemapVariant->fromUnpublished()->getChunkCount(); $delta++) {
      $this->writer->startElement('sitemap');
      $this->writer->writeElement('loc', $this->sitemapVariant->toUrl('canonical', ['delta' => $delta])->toString());
      $this->writer->writeElement('lastmod', date('c', $this->sitemapVariant->fromUnpublished()->getCreated())); // todo Should this be current time instead?
      $this->writer->endElement();
    }

    $this->writer->endElement();
    $this->writer->endDocument();

    return $this->writer->outputMemory();
  }

}
