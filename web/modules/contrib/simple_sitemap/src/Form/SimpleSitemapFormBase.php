<?php

namespace Drupal\simple_sitemap\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\simple_sitemap\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\simple_sitemap\Manager\Generator;

/**
 * Class SimpleSitemapFormBase
 */
abstract class SimpleSitemapFormBase extends ConfigFormBase {

  /**
   * @var \Drupal\simple_sitemap\Manager\Generator
   */
  protected $generator;

  /**
   * @var \Drupal\simple_sitemap\Settings
   */
  protected $settings;

  /**
   * @var \Drupal\simple_sitemap\Form\FormHelper
   */
  protected $formHelper;

  /**
   * SimpleSitemapFormBase constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\simple_sitemap\Manager\Generator $generator
   * @param \Drupal\simple_sitemap\Settings $settings
   * @param \Drupal\simple_sitemap\Form\FormHelper $form_helper
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    Generator $generator,
    Settings $settings,
    FormHelper $form_helper
  ) {
    $this->generator = $generator;
    $this->settings = $settings;
    $this->formHelper = $form_helper;


    parent::__construct($config_factory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('simple_sitemap.generator'),
      $container->get('simple_sitemap.settings'),
      $container->get('simple_sitemap.form_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['simple_sitemap.settings'];
  }

}
