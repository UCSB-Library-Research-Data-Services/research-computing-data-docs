<?php

namespace Drupal\simple_sitemap\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

interface SimpleSitemapInterface extends ConfigEntityInterface {

  public function fromPublished(): SimpleSitemapInterface;

  public function fromUnpublished(): SimpleSitemapInterface;

  public function fromPublishedAndUnpublished(): SimpleSitemapInterface;

  public function getType(): SimpleSitemapTypeInterface;

  public function toString(?int $delta = NULL): string;

  public function publish(): SimpleSitemapInterface;

  public function deleteContent(): SimpleSitemapInterface;

  public function addChunk(array $links): SimpleSitemapInterface;

  public function generateIndex(): SimpleSitemapInterface;

  public function getChunk(int $delta = SimpleSitemapStorage::SITEMAP_CHUNK_FIRST_DELTA): string;

  public function getChunkCount(): int;

  public function hasIndex(): bool;

  public function getIndex(): string;

  public function contentStatus(): ?int;

  public function getCreated(): ?string;

  public function getLinkCount(): int;

  public function isDefault(): bool;

  public function isMultilingual(): bool;

  public static function purgeContent(?array $variants = NULL, ?bool $status = SimpleSitemap::FETCH_BY_STATUS_PUBLISHED_UNPUBLISHED);
}
