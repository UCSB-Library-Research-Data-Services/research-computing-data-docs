<?php

/**
 * Implements hook_drush_command().
 */
function ssis_core_drush_command() {
  return [
    'sist-deploy' => [
      'description' => 'Run a batch of drush commands to ensure successful deployment',
      'drupal dependencies' => ['ssis_core'],
      'aliases' => ['sistd', 'sist:deploy'],
    ]
  ];
}

function drush_ssis_core_sist_deploy() {
  _drush_ssis_core_sist_deploy_steps();
}

// run the steps necessary to handle new code deployments
function _drush_ssis_core_sist_deploy_steps() {

  $steps = array();
  
  array_push($steps,['state-set',      ['system.maintenance_mode', 'TRUE']]);
  array_push($steps,['updatedb',       ['-y']]);
  array_push($steps,['config-import',  ['-y']]);
  
  $livestatus = \Drupal::config('config_split.config_split.live')->get('status');
  if ($livestatus == true) {
    array_push($steps,['config-split-import',       ['live', '-y']]);
  }
  $teststatus = \Drupal::config('config_split.config_split.test')->get('status');
  if ($teststatus == true) {
    array_push($steps,['config-split-import',       ['test', '-y']]);
  }
  $devstatus = \Drupal::config('config_split.config_split.dev')->get('status');
  if ($devstatus == true) {
    array_push($steps,['config-split-import',       ['dev', '-y']]);
  }
  
  array_push($steps,['updatedb',       ['-y']]);
  array_push($steps,['core-cron',      ['-y']]);
  array_push($steps,['php-eval',       ['-y', 'node_access_rebuild();']]);
  array_push($steps,['cache-rebuild',  ['-y']]);
  array_push($steps,['state-set',      ['system.maintenance_mode', 'FALSE']]);
  array_push($steps,['cache-rebuild',  ['-y']]);

  foreach ($steps as $step) {
    drush_log("SIS&T deploy step: $step[0]", 'ok');
    drush_invoke_process('@self', $step[0], $step[1]);
  }
}
