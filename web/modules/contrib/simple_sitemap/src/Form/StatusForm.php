<?php

namespace Drupal\simple_sitemap\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\simple_sitemap\Queue\QueueWorker;
use Drupal\simple_sitemap\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\simple_sitemap\Manager\Generator as SimplesitemapOld;
use Drupal\Core\Database\Connection;

/**
 * Class SitemapsForm
 */
class StatusForm extends SimpleSitemapFormBase {

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $db;

  /**
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * @var \Drupal\simple_sitemap\Queue\QueueWorker
   */
  protected $queueWorker;

  /**
   * SitemapsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param SimplesitemapOld $generator
   * @param \Drupal\simple_sitemap\Settings $settings
   * @param \Drupal\simple_sitemap\Form\FormHelper $form_helper
   * @param \Drupal\Core\Database\Connection $database
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   * @param \Drupal\simple_sitemap\Queue\QueueWorker $queue_worker
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    SimplesitemapOld $generator,
    Settings $settings,
    FormHelper $form_helper,
    Connection $database,
    DateFormatter $date_formatter,
    QueueWorker $queue_worker
  ) {
    parent::__construct(
      $config_factory,
      $generator,
      $settings,
      $form_helper
    );
    $this->db = $database;
    $this->dateFormatter = $date_formatter;
    $this->queueWorker = $queue_worker;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('simple_sitemap.generator'),
      $container->get('simple_sitemap.settings'),
      $container->get('simple_sitemap.form_helper'),
      $container->get('database'),
      $container->get('date.formatter'),
      $container->get('simple_sitemap.queue_worker')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'simple_sitemap_status_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form['#attached']['library'][] = 'simple_sitemap/sitemaps';

    $form['status'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Sitemap status'),
      '#markup' => '<div class="description">' . $this->t('Sitemaps can be regenerated on demand here.') . '</div>',
    ];

    $form['status']['actions'] = [
      '#prefix' => '<div class="clearfix"><div class="form-item">',
      '#suffix' => '</div></div>',
    ];

    $form['status']['actions']['rebuild_queue_submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Rebuild queue'),
      '#submit' => [self::class . '::rebuildQueue'],
      '#validate' => [],
    ];

    $form['status']['actions']['regenerate_submit'] = [
      '#type' => 'submit',
      '#value' => $this->queueWorker->generationInProgress()
        ? $this->t('Resume generation')
        : $this->t('Rebuild queue & generate'),
      '#submit' => [self::class . '::generateSitemap'],
      '#validate' => [],
    ];

    $form['status']['progress'] = [
      '#prefix' => '<div class="clearfix">',
      '#suffix' => '</div>',
    ];

    $form['status']['progress']['title']['#markup'] = $this->t('Progress of sitemap regeneration');

    $total_count = $this->queueWorker->getInitialElementCount();
    if (!empty($total_count)) {
      $indexed_count = $this->queueWorker->getProcessedElementCount();
      $percent = round(100 * $indexed_count / $total_count);

      // With all results processed, there still may be some stashed results to be indexed.
      $percent = $percent === 100 && $this->queueWorker->generationInProgress() ? 99 : $percent;

      $index_progress = [
        '#theme' => 'progress_bar',
        '#percent' => $percent,
        '#message' => $this->t('@indexed out of @total queue items have been processed.<br>Each sitemap is published after all of its items have been processed.', ['@indexed' => $indexed_count, '@total' => $total_count]),
      ];
      $form['status']['progress']['bar']['#markup'] = render($index_progress);
    }
    else {
      $form['status']['progress']['bar']['#markup'] = '<div class="description">' . $this->t('There are no items to be indexed.') . '</div>';
    }

    return $form;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public static function generateSitemap(array &$form, FormStateInterface $form_state): void {
    \Drupal::service('simple_sitemap.generator')->generateSitemap();
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public static function rebuildQueue(array &$form, FormStateInterface $form_state): void {
    \Drupal::service('simple_sitemap.generator')->rebuildQueue();
  }

}
