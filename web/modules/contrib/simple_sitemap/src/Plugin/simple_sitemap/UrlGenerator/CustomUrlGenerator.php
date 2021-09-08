<?php

namespace Drupal\simple_sitemap\Plugin\simple_sitemap\UrlGenerator;

use Drupal\Core\Url;
use Drupal\simple_sitemap\Annotation\UrlGenerator;
use Drupal\simple_sitemap\Entity\EntityHelper;
use Drupal\simple_sitemap\Exception\SkipElementException;
use Drupal\simple_sitemap\Logger;
use Drupal\simple_sitemap\Manager\CustomLinkManager;
use Drupal\simple_sitemap\Plugin\simple_sitemap\SimpleSitemapPluginBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Path\PathValidator;
use Drupal\simple_sitemap\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CustomUrlGenerator
 *
 * @UrlGenerator(
 *   id = "custom",
 *   label = @Translation("Custom URL generator"),
 *   description = @Translation("Generates URLs set in admin/config/search/simplesitemap/custom."),
 * )
 *
 */
class CustomUrlGenerator extends EntityUrlGeneratorBase {

  protected const PATH_DOES_NOT_EXIST_MESSAGE = 'The custom path @path has been omitted from the XML sitemaps as it does not exist. You can review custom paths <a href="@custom_paths_url">here</a>.';

  /**
   * @var \Drupal\simple_sitemap\Manager\CustomLinkManager
   */
  protected $customLinks;

  /**
   * @var \Drupal\Core\Path\PathValidator
   */
  protected $pathValidator;

  /**
   * @var bool
   */
  protected $includeImages;

  /**
   * CustomUrlGenerator constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\simple_sitemap\Logger $logger
   * @param \Drupal\simple_sitemap\Settings $settings
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\simple_sitemap\Entity\EntityHelper $entity_helper
   * @param \Drupal\simple_sitemap\Manager\CustomLinkManager $custom_links
   * @param \Drupal\Core\Path\PathValidator $path_validator
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    Logger $logger,
    Settings $settings,
    LanguageManagerInterface $language_manager,
    EntityTypeManagerInterface $entity_type_manager,
    EntityHelper $entity_helper,
    CustomLinkManager $custom_links,
    PathValidator $path_validator) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $logger,
      $settings,
      $language_manager,
      $entity_type_manager,
      $entity_helper
    );
    $this->customLinks = $custom_links;
    $this->pathValidator = $path_validator;
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
      $container->get('language_manager'),
      $container->get('entity_type.manager'),
      $container->get('simple_sitemap.entity_helper'),
      $container->get('simple_sitemap.custom_link_manager'),
      $container->get('path.validator')
    );
  }

  /**
   * @inheritdoc
   */
  public function getDataSets(): array {
    $this->includeImages = $this->settings->get('custom_links_include_images', FALSE);

    return array_values($this->customLinks->setVariants($this->sitemapVariant->id())->get());
  }

  /**
   * @inheritdoc
   */
  protected function processDataSet($data_set): array {
    if (!(bool) $this->pathValidator->getUrlIfValidWithoutAccessCheck($data_set['path'])) {
      $this->logger->m(self::PATH_DOES_NOT_EXIST_MESSAGE,
        ['@path' => $data_set['path'], '@custom_paths_url' => $GLOBALS['base_url'] . '/admin/config/search/simplesitemap/custom'])
        ->display('warning', 'administer sitemap settings')
        ->log('warning');

      throw new SkipElementException();
    }

    $url_object = Url::fromUserInput($data_set['path'])->setAbsolute();
    $path = $url_object->getInternalPath();

    $entity = $this->entityHelper->getEntityFromUrlObject($url_object);

    $path_data = [
      'url' => $url_object,
      'lastmod' => !empty($entity) && method_exists($entity, 'getChangedTime')
        ? date('c', $entity->getChangedTime())
        : NULL,
      'priority' => $data_set['priority'] ?? NULL,
      'changefreq' => !empty($data_set['changefreq']) ? $data_set['changefreq'] : NULL,
      'images' => $this->includeImages && !empty($entity)
        ? $this->getEntityImageData($entity)
        : [],
      'meta' => [
        'path' => $path,
      ]
    ];

    // Additional info useful in hooks.
    if (!empty($entity)) {
      $path_data['meta']['entity_info'] = [
        'entity_type' => $entity->getEntityTypeId(),
        'id' => $entity->id(),
      ];
    }

    return $path_data;
  }
}
