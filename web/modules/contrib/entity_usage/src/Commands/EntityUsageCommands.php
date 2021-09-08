<?php

namespace Drupal\entity_usage\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drush\Commands\DrushCommands;
use Drupal\entity_usage\EntityUsageBatchManager;

/**
 * Entity Usage drush commands.
 */
class EntityUsageCommands extends DrushCommands {

  /**
   * The Entity Usage batch manager.
   *
   * @var \Drupal\entity_usage\EntityUsageBatchManager
   */
  protected $batchManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity usage configuration.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $entityUsageConfig;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityUsageBatchManager $batch_manager, EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory) {
    parent::__construct();
    $this->batchManager = $batch_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityUsageConfig = $config_factory->get('entity_usage.settings');
  }

  /**
   * Recreate all entity usage statistics.
   *
   * @command entity-usage:recreate
   * @aliases eu-r,entity-usage-recreate
   * @option use-queue
   *   Use a queue instead of a batch process to recreate tracking info. This
   *   means usage information won't be accurate until all items in the queue
   *   have been processed by cron runs.
   * @option multi-pass
   *   Use this command with options --use-queue and --multi-pass if you are
   *   experiencing timeouts when populating the queue. This means that every
   *   every time the command is passed with both options, queue items will not
   *   be created from start, but from where we left off in the previous
   *   execution. Run the command several times until all items have been
   *   queued. Use --clear-multi-pass to reset the --multi-pass flag.
   * @option clear-multi-pass
   *   If --clear-multi-pass is set, all this command will do is to reset the
   *   multi-pass flag, so subsequent executions of this command will start
   *   fresh.
   */
  public function recreate($options = ['use-queue' => FALSE, 'multi-pass' => FALSE, 'clear-multi-pass' => FALSE]) {
    if (!empty($options['clear-multi-pass'])) {
      \Drupal::state()->delete('entity_usage.multi_pass');
      $this->output()->writeln(t('Multi-pass flag has been cleared. Subsequent executions will start from scratch.'));
      return;
    }
    if (!empty($options['multi-pass']) && empty($options['use-queue'])) {
      $this->output()->writeln(t('The --multi-pass option can only be used when the --use-queue flag is specified. Aborting.'));
      return;
    }
    if (!empty($options['multi-pass'])) {
      $this->output()->writeln(t('Multi-pass flag has been set. If this command breaks, try running it again and it will do its best to resume where things were left off. Current state values:'));
      $state_values = \Drupal::state()->get('entity_usage.multi_pass', []);
      array_walk($state_values, function (&$value, $key) { $value = "$key: $value"; });
      if (empty($state_values)) {
        $state_values = t('Nothing yet!');
      }
      $this->output()->writeln($state_values);
    }
    if (!empty($options['use-queue'])) {
      $to_track = $this->entityUsageConfig->get('track_enabled_source_entity_types');
      foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
        // Only look for entities enabled for tracking on the settings form.
        $track_this_entity_type = FALSE;
        if (!is_array($to_track) && ($entity_type->entityClassImplements('\Drupal\Core\Entity\ContentEntityInterface'))) {
          // When no settings are defined, track all content entities by default,
          // except for Files and Users.
          if (!in_array($entity_type_id, ['file', 'user'])) {
            $track_this_entity_type = TRUE;
          }
        }
        elseif (is_array($to_track) && in_array($entity_type_id, $to_track, TRUE)) {
          $track_this_entity_type = TRUE;
        }
        if ($track_this_entity_type) {
          $this->generateQueueItems($entity_type_id, (bool) $options['multi-pass']);
        }
      }
    }
    else {
      $this->batchManager->recreate();
      drush_backend_batch_process();
    }
  }

  /**
   * Populate items to be tracked into the EU tracking queue.
   *
   * @param string $entity_type_id
   *   The entity type machine name
   * @param bool $multi_pass
   *   Whether this is operating in multi_pass mode. If true, this will store
   *   in Drupal state the ID of the last queue item created, and if something
   *   interrupts the execution, next time this is called in multi_pass mode,
   *   this will resume creating items from the last ID we stopped earlier,
   *   instead of creating from scratch. Defaults to FALSE.
   */
  protected function generateQueueItems($entity_type_id, $multi_pass = FALSE) {
    $queue = \Drupal::queue('entity_usage_regenerate_queue');

    if ($multi_pass) {
      $multi_pass_from_state = \Drupal::state()->get('entity_usage.multi_pass', []);
      if (!empty($multi_pass_from_state[$entity_type_id])) {
        $current_id = $multi_pass_from_state[$entity_type_id];
      }
      else {
        $current_id = 0;
      }
    }

    // Delete current usage statistics for these entities if we are starting
    // for this entity_type.
    if (empty($current_id)) {
      \Drupal::service('entity_usage.usage')->bulkDeleteSources($entity_type_id);
    }

    $storage = $this->entityTypeManager->getStorage($entity_type_id);
    $entity_type = $storage->getEntityType();
    if ($entity_type->isRevisionable()) {
      $revision_key = $entity_type->getKey('revision');
      $query = $storage
        ->getQuery()
        ->allRevisions()
        ->sort($revision_key, 'ASC')
        ->accessCheck(FALSE);
      if ($multi_pass) {
        $query->condition($revision_key, $current_id, '>');
      }
      $result = $query->execute();
      foreach ($result as $revision_id => $id) {
        $queue->createItem([
          'entity_type' => $entity_type_id,
          'entity_revision_id' => $revision_id,
        ]);
        if ($multi_pass) {
          $multi_pass_from_state[$entity_type_id] = $revision_id;
          \Drupal::state()->set('entity_usage.multi_pass', $multi_pass_from_state);
        }
      }
    }
    else {
      $id_key = $entity_type->getKey('id');
      $query = $storage
        ->getQuery()
        ->sort($id_key, 'ASC')
        ->accessCheck(FALSE);
      if ($multi_pass) {
        $query->condition($id_key, $current_id, '>');
      }
      $result = $query->execute();
      foreach ($result as $id) {
        $queue->createItem([
          'entity_type' => $entity_type_id,
          'entity_id' => $id,
        ]);
        if ($multi_pass) {
          $multi_pass_from_state[$entity_type_id] = $id;
          \Drupal::state()->set('entity_usage.multi_pass', $multi_pass_from_state);
        }
      }
    }
  }

}
