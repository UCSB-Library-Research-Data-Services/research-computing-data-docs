<?php

namespace Drupal\simple_sitemap\Manager;

use Drupal\simple_sitemap\Entity\SimpleSitemap;

/**
 * Class Simplesitemap
 */
trait VariantSetterTrait {

  /**
   * @var array
   */
  protected $variants;

  /**
   * @param array|string|true|null $variants
   *  array: Array of variants to be set.
   *  string: A particular variant to be set.
   *  null: Default variant will be set.
   *  true: All existing variants will be set.
   *
   * @todo Check if variants exist and throw exception.
   */
  public function setVariants($variants = NULL) {
    if (NULL === $variants) {
      $this->variants = !empty($default_variant = \Drupal::service('simple_sitemap.settings')
        ->get('default_variant', '')) ? [$default_variant] : [];
    }
    elseif ($variants === TRUE) {
      $this->variants = array_keys(SimpleSitemap::loadMultiple());
    }
    else {
      $this->variants = (array) $variants;
    }

    return $this;
  }

  /**
   * Gets the currently set variants, the default variant, or all variants.
   *
   * @param bool $default_get_all
   *  If true and no variants are set, all variants are returned. If false and
   *  no variants are set, only the default variant is returned.
   *
   * @return array
   */
  protected function getVariants(bool $default_get_all = TRUE): array {
    if (NULL === $this->variants) {
      $this->setVariants($default_get_all ? TRUE : NULL);
    }

    return $this->variants;
  }

}
