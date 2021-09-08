<?php

namespace Drupal\simple_sitemap\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\simple_sitemap\Plugin\simple_sitemap\SitemapGenerator\SitemapGeneratorInterface;

/**
 * Defines the simple_sitemap entity.
 *
 * @ConfigEntityType(
 *   id = "simple_sitemap_type",
 *   label = @Translation("Simple XML sitemap type"),
 *   label_collection = @Translation("Sitemap types"),
 *   label_singular = @Translation("sitemap type"),
 *   label_plural = @Translation("sitemap types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count sitema type",
 *     plural = "@count sitemap types",
 *   ),
 *   handlers = {
 *     "storage" = "Drupal\simple_sitemap\Entity\SimpleSitemapTypeStorage",
 *     "list_builder" = "\Drupal\simple_sitemap\SimpleSitemapTypeListBuilder",
 *     "form" = {
 *       "default" = "\Drupal\simple_sitemap\Form\SimpleSitemapTypeEntityForm",
 *       "add" = "\Drupal\simple_sitemap\Form\SimpleSitemapTypeEntityForm",
 *       "edit" = "\Drupal\simple_sitemap\Form\SimpleSitemapTypeEntityForm",
 *       "delete" = "\Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *   },
 *   config_prefix = "type",
 *   admin_permission = "administer sitemap settings",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "label" = "label",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "sitemap_generator",
 *     "url_generators",
 *   },
 *   links = {
 *     "add-form" = "/admin/config/search/simplesitemap/types/add",
 *     "edit-form" = "/admin/config/search/simplesitemap/types/{simple_sitemap_type}",
 *     "delete-form" = "/admin/config/search/simplesitemap/types/{simple_sitemap_type}/delete",
 *     "collection" = "/admin/config/search/simplesitemap/types",
 *   },
 * )
 *
 * @todo Implement dependency injection after https://www.drupal.org/project/drupal/issues/2142515 is fixed.
 */
class SimpleSitemapType extends ConfigEntityBase implements SimpleSitemapTypeInterface {

  /**
   * @var \Drupal\simple_sitemap\Plugin\simple_sitemap\SitemapGenerator\SitemapGeneratorInterface
   */
  protected $sitemapGenerator;

  /**
   * @var \Drupal\simple_sitemap\Plugin\simple_sitemap\UrlGenerator\UrlGeneratorInterface[]
   */
  protected $urlGenerators;

  /**
   * {@inheritdoc}
   */
  public function getSitemapGenerator(): SitemapGeneratorInterface {
    if ($this->sitemapGenerator === NULL) {
      $this->sitemapGenerator = \Drupal::service('plugin.manager.simple_sitemap.sitemap_generator')
        ->createInstance($this->get('sitemap_generator'));
    }

    return $this->sitemapGenerator;
  }

  /**
   * {@inheritdoc}
   */
  public function getUrlGenerators(): array {
    if ($this->urlGenerators === NULL) {
      $this->urlGenerators = [];
      $url_generator_manager = \Drupal::service('plugin.manager.simple_sitemap.url_generator');
      foreach ($this->get('url_generators') as $generator_id) {
        $this->urlGenerators[$generator_id] = $url_generator_manager->createInstance($generator_id);
      }
    }

    return $this->urlGenerators;
  }

  /**
   * {@inheritdoc}
   */
  public function set($property_name, $value) {
    if ($property_name === 'sitemap_generator') {
      $this->sitemapGenerator = NULL;
    }
    elseif ($property_name === 'url_generators') {
      $this->urlGenerators = NULL;
    }

    return parent::set($property_name, $value);
  }

}
