<?php

/**
 * @file
 * Builds placeholder replacement tokens for ISA profile.
 */

use Drupal\Core\Render\BubbleableMetadata;


/**
 * Implements hook_token_info().
 */
function isa_site_config_token_info() {
  // We don't create a new type, but add the token to the existing
  $tokens['webform']['isa-emails'] = [
    'name' => t('Custom emails'),
    'description' => t('Emails set by user associate to the webform.')
  ];
  return ['tokens' => $tokens];
}

/**
 * Implements hook_tokens().
 */
function isa_site_config_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  if ($type == 'webform' && !empty($data['webform'])) {

    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'isa-emails':
          /** @var \Drupal\webform\WebformInterface $webform */
          $webform = $data['webform'];

          $webfomEmails = \Drupal::config('isa_site_config.settings')->get('webfom_emails')?:[];

          $emails = '';
          foreach ($webfomEmails as $webfomEmail){
            if($webfomEmail['webform'] == $webform->id()){
              $emails = $emails . $webfomEmail['mail'] . ',';
            }
          }

          $replacements[$original] = $emails;
          break;
      }
    }
  }

  return $replacements;

}
