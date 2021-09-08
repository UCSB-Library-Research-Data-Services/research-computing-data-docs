<?php

namespace Drupal\simple_sitemap\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;
use Drupal\simple_sitemap\Exception\SitemapNotExistsException;

/**
 * Defines the simple_sitemap entity.
 *
 * @ConfigEntityType(
 *   id = "simple_sitemap",
 *   label = @Translation("Sitemap"),
 *   label_collection = @Translation("Sitemaps"),
 *   label_singular = @Translation("sitemap"),
 *   label_plural = @Translation("sitemaps"),
 *   label_count = @PluralTranslation(
 *     singular = "@count sitemap",
 *     plural = "@count sitemaps",
 *   ),
 *   handlers = {
 *     "storage" = "Drupal\simple_sitemap\Entity\SimpleSitemapStorage",
 *     "list_builder" = "\Drupal\simple_sitemap\SimpleSitemapListBuilder",
 *     "form" = {
 *       "default" = "\Drupal\simple_sitemap\Form\SimpleSitemapEntityForm",
 *       "add" = "\Drupal\simple_sitemap\Form\SimpleSitemapEntityForm",
 *       "edit" = "\Drupal\simple_sitemap\Form\SimpleSitemapEntityForm",
 *       "delete" = "\Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *   },
 *   config_prefix = "sitemap",
 *   admin_permission = "administer sitemap settings",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "label" = "label",
 *     "weight" = "weight",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "type",
 *     "weight",
 *   },
 *   links = {
 *     "add-form" = "/admin/config/search/simplesitemap/variants/add",
 *     "edit-form" = "/admin/config/search/simplesitemap/variants/{simple_sitemap}",
 *     "delete-form" = "/admin/config/search/simplesitemap/variants/{simple_sitemap}/delete",
 *     "collection" = "/admin/config/search/simplesitemap",
 *   },
 * )
 *
 * @todo Implement dependency injection after https://www.drupal.org/project/drupal/issues/2142515 is fixed.
 */
class SimpleSitemap extends ConfigEntityBase implements SimpleSitemapInterface {

  public const SITEMAP_UNPUBLISHED = 0;
  public const SITEMAP_PUBLISHED = 1;
  public const SITEMAP_PUBLISHED_GENERATING = 2;

  public const FETCH_BY_STATUS_PUBLISHED_UNPUBLISHED = NULL;
  public const FETCH_BY_STATUS_UNPUBLISHED = 0;
  public const FETCH_BY_STATUS_PUBLISHED = 1;

  /**
   * @var int
   */
  protected $fetchByStatus;

  /**
   * @var \Drupal\simple_sitemap\Entity\SimpleSitemapTypeInterface
   */
  protected $sitemapType;

  public function __toString(): string {
    return $this->toString();
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();
    $this->addDependency('config', $this->getType()->getConfigDependencyName());

    return $this;
  }

  public function fromPublished(): SimpleSitemapInterface {
    $this->fetchByStatus = self::FETCH_BY_STATUS_PUBLISHED;
    return $this;
  }

  public function fromUnpublished(): SimpleSitemapInterface {
    $this->fetchByStatus = self::FETCH_BY_STATUS_UNPUBLISHED;
    return $this;
  }

  public function fromPublishedAndUnpublished(): SimpleSitemapInterface {
    $this->fetchByStatus = self::FETCH_BY_STATUS_PUBLISHED_UNPUBLISHED;
    return $this;
  }

  public function getType(): SimpleSitemapTypeInterface {
    if ($this->sitemapType === NULL) {
      $this->sitemapType = \Drupal::entityTypeManager()->getStorage('simple_sitemap_type')->load($this->get('type'));
    }

    return $this->sitemapType;
  }

  public function toString(?int $delta = NULL): string {
    $status = $this->fetchByStatus ?? self::FETCH_BY_STATUS_PUBLISHED;
    $storage = \Drupal::entityTypeManager()->getStorage('simple_sitemap');

    if ($delta) {
      try {
        return $storage->getChunk($this, $status, $delta);
      }
      catch (SitemapNotExistsException $e) {
      }
    }

    if ($storage->hasIndex($this, $status)) {
      return $storage->getIndex($this, $status);
    }

    try {
      return $storage->getChunk($this, $status);
    }
    catch (SitemapNotExistsException $e) {
      return '';
    }
  }

  public function publish(): SimpleSitemapInterface {
    \Drupal::entityTypeManager()->getStorage('simple_sitemap')->publish($this);
    return $this;
  }

  public function deleteContent(): SimpleSitemapInterface {
    \Drupal::entityTypeManager()->getStorage('simple_sitemap')->deleteContent($this);
    return $this;
  }

  public function addChunk(array $links): SimpleSitemapInterface {
    $xml = $this->getType()->getSitemapGenerator()->setSitemapVariant($this)->getChunkXml($links); //todo automatically set variant
    \Drupal::entityTypeManager()->getStorage('simple_sitemap')->addChunk($this, $xml, count($links));

    return $this;
  }

  public function generateIndex(): SimpleSitemapInterface {
    if ($this->isIndexable()) {
      $xml = $this->getType()->getSitemapGenerator()->setSitemapVariant($this)->getIndexXml(); //todo automatically set variant
      \Drupal::entityTypeManager()->getStorage('simple_sitemap')->generateIndex($this, $xml);
    }

    return $this;
  }

  public function getChunk(int $delta = SimpleSitemapStorage::SITEMAP_CHUNK_FIRST_DELTA): string {
    return \Drupal::entityTypeManager()->getStorage('simple_sitemap')->getChunk($this, $this->fetchByStatus, $delta);
  }

  public function getChunkCount(): int {
    return \Drupal::entityTypeManager()->getStorage('simple_sitemap')->getChunkCount($this, $this->fetchByStatus);
  }

  public function hasIndex(): bool {
    return \Drupal::entityTypeManager()->getStorage('simple_sitemap')->hasIndex($this, $this->fetchByStatus);
  }

  protected function isIndexable(): bool {
    try {
      \Drupal::entityTypeManager()->getStorage('simple_sitemap')->getChunk($this, self::FETCH_BY_STATUS_UNPUBLISHED, 2);
      return TRUE;
    }
    catch (SitemapNotExistsException $e) {
      return FALSE;
    }
  }

  public function getIndex(): string {
    return \Drupal::entityTypeManager()->getStorage('simple_sitemap')->getIndex($this, $this->fetchByStatus);
  }

  public function status(): bool {
    return parent::status() && $this->contentStatus();
  }

  public function contentStatus(): ?int {
    return \Drupal::entityTypeManager()->getStorage('simple_sitemap')->status($this);
  }

  public function getCreated(): ?string {
    return \Drupal::entityTypeManager()->getStorage('simple_sitemap')->getCreated($this, $this->fetchByStatus);
  }

  public function getLinkCount(): int {
    return \Drupal::entityTypeManager()->getStorage('simple_sitemap')->getLinkCount($this, $this->fetchByStatus);
  }

  public function toUrl($rel = 'canonical', array $options = []) {
    if ($rel !== 'canonical') {
      return parent::toUrl($rel, $options);
    }

    $parameters = isset($options['delta']) ? ['page' => $options['delta']] : [];
    unset($options['delta']);

    $options['base_url'] = $options['base_url'] ?? (\Drupal::service('simple_sitemap.settings')
        ->get('base_url') ?: $GLOBALS['base_url']);

    $options['language'] = \Drupal::languageManager()->getLanguage(LanguageInterface::LANGCODE_NOT_APPLICABLE);

    return $this->isDefault()
      ? Url::fromRoute(
        'simple_sitemap.sitemap_default',
        $parameters,
        $options)
      : Url::fromRoute(
        'simple_sitemap.sitemap_variant',
        $parameters + ['variant' => $this->id()],
        $options);
  }

  public function isDefault(): bool {
    return $this->id() === \Drupal::service('simple_sitemap.settings')->get('default_variant');
  }

  /**
   * Determines if the sitemap is to be a multilingual sitemap based on several
   * factors.
   *
   * A hreflang/multilingual sitemap is only wanted if there are indexable
   * languages available and if there is a language negotiation method enabled
   * that is based on URL discovery. Any other language negotiation methods
   * should be irrelevant, as a sitemap can only use URLs to guide to the
   * correct language.
   *
   * @see https://www.drupal.org/project/simple_sitemap/issues/3154570#comment-13730522
   *
   * @return bool
   */
  public function isMultilingual(): bool {
    if (!\Drupal::service('module_handler')->moduleExists('language')) {
      return FALSE;
    }

    $url_negotiation_method_enabled = FALSE;
    $language_negotiator = \Drupal::service('language_negotiator');
    foreach ($language_negotiator->getNegotiationMethods(LanguageInterface::TYPE_URL) as $method) {
      if ($language_negotiator->isNegotiationMethodEnabled($method['id'])) {
        $url_negotiation_method_enabled = TRUE;
        break;
      }
    }

    $has_multiple_indexable_languages = count(
        array_diff_key(\Drupal::languageManager()->getLanguages(),
          \Drupal::service('simple_sitemap.settings')->get('excluded_languages', []))
      ) > 1;

    return $url_negotiation_method_enabled && $has_multiple_indexable_languages;
  }

  public static function purgeContent($variants = NULL, ?bool $status = self::FETCH_BY_STATUS_PUBLISHED_UNPUBLISHED) {
    \Drupal::entityTypeManager()->getStorage('simple_sitemap')->purgeContent($variants, $status);
  }

  /**
   * {@inheritdoc}
   */
  public function set($property_name, $value) {
    if ($property_name === 'type') {
      $this->sitemapType = NULL;
    }

    return parent::set($property_name, $value);
  }

}
