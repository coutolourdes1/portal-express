<?php

use Drupal\isa\Config\ConfigBit;
use Drupal\isa\Entity\IsaEntityDefinitionUpdateManager;
use Drupal\isa\Form\AssemblerForm;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Config\FileStorage;
use Drupal\user\Entity\User;


/**
 * Implements hook_install_tasks().
 */
function isa_install_tasks(&$install_state) {
  return [
    'isa_extra_components' => [
      'display_name' => t('Extra components'),
      'display' => TRUE,
      'type' => 'form',
      'function' => AssemblerForm::class,
    ],
    'isa_assemble_extra_components' => [
      'display_name' => t('Assemble extra components'),
      'display' => TRUE,
      'type' => 'batch',
    ],
  ];
}

/**
 * Implements hook_tasks_alter().
 *
 */
function isa_install_tasks_alter(&$tasks, $install_state) {
  $tasks['install_finished']['function'] = 'isa_alter_install_finished';
}

/**
 * Batch job to assemble isa extra components.
 *
 * @param array $install_state
 *   The current install state.
 *
 * @return array
 *   The batch job definition.
 */
function isa_assemble_extra_components(array &$install_state) {
  $batch = [];

  // Install selected extra features.
  $selected_extra_features = [];
  $selected_extra_features_configs = [];

  if (isset($install_state['isa']['extra_features_values'])) {
    $selected_extra_features = $install_state['isa']['extra_features_values'];
  }

  if (isset($install_state['isa']['extra_features_configs'])) {
    $selected_extra_features_configs = $install_state['isa']['extra_features_configs'];
  }

  // Get the list of extra features config bits.
  $extraFeatures = ConfigBit::getList('configbit/extra.components.isa.bit.yml', 'show_extra_components', TRUE, 'dependencies', 'profile', 'isa');

  // If we do have selected extra features.
  if (count($selected_extra_features) && count($extraFeatures)) {
    // Have batch processes for each selected extra features.
    foreach ($selected_extra_features as $extra_feature_key => $extra_feature_checked) {
      if ($extra_feature_checked) {

        // If the extra feature was a module and not enabled, then enable it.
        if (!\Drupal::moduleHandler()->moduleExists($extra_feature_key)) {
          // Add the checked extra feature to the batch process to be enabled.
          $batch['operations'][] = [
            'isa_assemble_extra_component_then_install',
            (array) $extra_feature_key,
          ];
        }

        if (count($selected_extra_features_configs) &&
          isset($extraFeatures[$extra_feature_key]['config_form']) &&
          $extraFeatures[$extra_feature_key]['config_form'] == TRUE &&
          isset($extraFeatures[$extra_feature_key]['formbit'])) {

          $formbit_file_name = drupal_get_path('profile', 'isa') . '/' . $extraFeatures[$extra_feature_key]['formbit'];

          if (file_exists($formbit_file_name)) {

            // Added the selected extra feature configs to the batch process
            // with the same function name in the formbit.
            $batch['operations'][] = [
              'isa_save_editable_config_values',
              (array) [
                $extra_feature_key,
                $formbit_file_name,
                $selected_extra_features_configs,
              ],
            ];
          }
        }
      }
    }

    // Hide Wornings and status messages.
    $batch['operations'][] = [
      'isa_hide_warning_and_status_messages',
      (array) TRUE,
    ];

    // Fix entity updates to clear up any mismatched entity.
    $batch['operations'][] = ['isa_fix_entity_update', (array) TRUE];
  }

  return $batch;
}

/**
 * Batch function to assemble and install needed extra components.
 *
 * @param string|array $extra_component
 *   Name of the extra component.
 */
function isa_assemble_extra_component_then_install($extra_component) {
  \Drupal::service('module_installer')->install((array) $extra_component, TRUE);
}


/**
 * Batch function to save editable config values for extra components.
 *
 * @param string|array $extra_component_machine_name
 *   Machine name key of the extra component.
 * @param string|array $formbit_file_name
 *   FormBit file name.
 * @param string|array $editable_config_values
 *   Editable config values.
 */
function isa_save_editable_config_values($extra_component_machine_name, $formbit_file_name, $editable_config_values) {
  include_once $formbit_file_name;
  call_user_func_array($extra_component_machine_name . "_submit_formbit", [$editable_config_values]);
}

/**
 * Batch function to fix entity updates to clear up any mismatched entity.
 *
 * Entity and/or field definitions, The following changes were detected in
 * the entity type and field definitions.
 *
 * @param string|array $entity_update
 *   To entity update or not.
 */
function isa_fix_entity_update($entity_update) {
  if ($entity_update) {
    \Drupal::classResolver()
      ->getInstanceFromDefinition(IsaEntityDefinitionUpdateManager::class)
      ->applyUpdates();
  }
}

/**
 * Batch function to hide warning messages.
 *
 * @param bool $hide
 *   To hide or not.
 */
function isa_hide_warning_and_status_messages($hide) {
  if ($hide && !isset($_SESSION['messages']['error'])) {
    unset($_SESSION['messages']);
  }
}


/**
 * The actions to need execute after finish install
 *
 * @param $install_state
 */
function isa_alter_install_finished(&$install_state) {
  // Entity updates to clear up any mismatched entity and/or field definitions
  // And Fix changes were detected in the entity type and field definitions.
  \Drupal::classResolver()
    ->getInstanceFromDefinition(IsaEntityDefinitionUpdateManager::class)
    ->applyUpdates();

  // Instalar los contenidos por defecto
  // Desabilite esta parte porque agotaba la memoria de PHP
  //\Drupal::service('module_installer')->install(['isa_default_content']);

  // Todo: Si pongo este modulo en el profile da error
  \Drupal::service('module_installer')->install(['isa_block_highlight_content']);

  // Importar los ficheros de los sitios que usan el perfil y sobrescriben la configuracion
  $site_path = \Drupal::service('site.path');
  $config_path = "${site_path}/config/install";
  $modules_after_install = [];

  $isa_conf_path = "${site_path}/isa.yml";
  if (file_exists($isa_conf_path)) {
    $isa_conf = Yaml::decode(file_get_contents($isa_conf_path));

    if (isset($isa_conf['install'])) {
      $modules = $isa_conf['install'];
      \Drupal::service('module_installer')->install($modules);
    }

    if (isset($isa_conf['theme'])) {
      $theme = trim($isa_conf['theme']);
      \Drupal::service('theme_installer')->install([$theme]);
      \Drupal::configFactory()
        ->getEditable('system.theme')
        ->set('default', $theme)
        ->save();
    }

    if (isset($isa_conf['after_install'])) {
      $modules_after_install = $isa_conf['after_install'];
    }
  }

  if (is_dir($config_path)) {
    $config_source = new FileStorage($config_path);
    \Drupal::service('config.installer')->installOptionalConfig($config_source);
  }

  \Drupal::service('module_installer')->install($modules_after_install);

  // After install of extra modules by install: in the .info.yml files.
  // In isa profile and all isa components.
  // ---------------------------------------------------------------------------
  // * Necessary inlitilization for the entire system.
  // * Account for changed config by the end install.
  // * Flush all persistent caches.
  // * Flush asset file caches.
  // * Wipe the Twig PHP Storage cache.
  // * Rebuild module and theme data.
  // * Clear all plugin caches.
  // * Rebuild the menu router based on all rebuilt data.
  drupal_flush_all_caches();
}
