<?php

/**
 * @file
 * isa_language module drush integration.
 */

use Drupal\isa_language\LanguageGenerator;

/**
 * Implements hook_drush_command().
 */
function isa_language_drush_command() {
  $items = [];

  $items['isa_language-generate'] = [
    'description' => 'Display current Features settings.',
    'arguments' => [
      'features' => 'A space delimited list of features to export language collection.',
    ],
    'examples' => [
      "drush isa_language-generate" => 'Export language collection in all features.',
      "drush isa_language-generate example_article example_page" => "Export language collection in the example_article and example_page.",
    ],
    'aliases' => ['isalg'],
  ];

  return $items;
}

/**
 * Provides Drush command callback for isa_language-generate.
 */
function drush_isa_language_generate($features = NULL) {
  $features = func_get_args();

  /** @var LanguageGenerator $generator */
  $generator = \Drupal::service('isa_language.generator');
  $generator->generateFeatures($features);
}


