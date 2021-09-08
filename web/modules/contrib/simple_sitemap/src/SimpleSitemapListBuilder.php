<?php

namespace Drupal\simple_sitemap;

use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\simple_sitemap\Entity\SimpleSitemap;
use Drupal\simple_sitemap\Form\FormHelper;
use Drupal\simple_sitemap\Form\StatusForm;

/**
 * Class SimpleSitemapListBuilder
 */
class SimpleSitemapListBuilder extends DraggableListBuilder {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'simple_sitemap_overview_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Sitemap');
    $header['type'] = $this->t('Type');
    $header['status'] = $this->t('Status');
    $header['count'] = $this->t('Link count');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = '<span title="' . $entity->get('description') . '">' . $this->t($entity->label()) . '</span>';
    $row['type']['#markup'] = '<span title="' . $entity->getType()->get('description') . '">' . $this->t($entity->getType()->label()) . '</span>';
    $row['status']['#markup'] = $this->t('pending');
    $row['count']['#markup'] = '';

    /** @var \Drupal\simple_sitemap\Entity\SimpleSitemapInterface $entity */
    if (!empty($entity->fromPublishedAndUnpublished()->getChunkCount())) {
      switch ($entity->contentStatus()) {

        case SimpleSitemap::SITEMAP_UNPUBLISHED:
          $row['status']['#markup'] = $this->t('generating');
          break;

        case SimpleSitemap::SITEMAP_PUBLISHED:
        case SimpleSitemap::SITEMAP_PUBLISHED_GENERATING:
          $row['label'] = $this->t('<a title ="' . $entity->get('description') . '" href="@url" target="_blank">@sitemap</a>',
            ['@url' => $entity->toUrl()->toString(), '@sitemap' => $this->t($entity->label())]
          );
          $row['status']['#markup'] = $this->t(($entity->contentStatus() === SimpleSitemap::SITEMAP_PUBLISHED
            ? 'published on @time'
            : 'published on @time, regenerating'
          ), ['@time' =>\Drupal::service('date.formatter')->format($entity->fromPublished()->getCreated())]);
          $row['count']['#markup'] = $entity->fromPublished()->getLinkCount();
          break;
      }
    }

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity): array {
    return [
      ['title' => t('Edit'), 'url' => $entity->toUrl('edit-form')],
      ['title' => t('Delete'), 'url' => $entity->toUrl('delete-form')],
    ];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $form = \Drupal::formBuilder()->getForm(StatusForm::class) + $form;
    $form['entities']['#empty'] = $this->t('No sitemaps have been defined yet. <a href="@url">Add a new one</a>.', [
      '@url' => Url::fromRoute('simple_sitemap.add')->toString(),
    ]);
    $form['#prefix'] = FormHelper::getDonationText();

    return $form;
  }

}
