<?php

namespace Drupal\simple_sitemap\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\simple_sitemap\Entity\SimpleSitemapType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SimpleSitemapEntityForm
 */
class SimpleSitemapEntityForm extends EntityForm {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * SimpleSitemapEntityForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   */
  public function __construct(EntityTypeManagerInterface $entity_manager) {
    $this->entityTypeManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->label(),
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#disabled' => !$this->entity->isNew(),
      '#maxlength' => EntityTypeInterface::ID_MAX_LENGTH,
      '#required' => TRUE,
      '#machine_name' => [
        'exists' => '\Drupal\simple_sitemap\Entity\SimpleSitemap::load',
        'replace_pattern' => '[^a-z0-9-_.]+',
        'replace' => '-',
      ],
      '#description' => $this->t('A unique name that will be part of the sitemap URL. Can only contain lowercase letters, numbers, dashes and underscores.'),
    ];

    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Sitemap type'),
      '#options' => array_map(function($sitemap_type) { return $sitemap_type->label(); }, SimpleSitemapType::loadMultiple()),
      '#default_value' => !$this->entity->isNew() ? $this->entity->getType()->id() : NULL,
      '#required' => TRUE,
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#default_value' => $this->entity->get('description'),
      '#title' => $this->t('Description'),
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    if ($this->entity->save() === SAVED_UPDATED) {
      $this->messenger()->addStatus($this->t('Sitemap variant %label has been updated.', ['%label' => $this->entity->label()]));
    }
    else {
      $this->messenger()->addStatus($this->t('Sitemap variant %label has been created.', ['%label' => $this->entity->label()]));
    }

    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
  }

}
