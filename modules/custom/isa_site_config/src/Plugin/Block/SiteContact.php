<?php

namespace Drupal\isa_site_config\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\file\Entity\File;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "isa_site_config_site_contact",
 *   admin_label = @Translation("Site contact informtion"),
 * )
 */
class SiteContact extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [
      '#theme' => 'isa_site_config_site_contact',
    ];

    $site_config = \Drupal::config('system.site');
    $build['#site_name'] = $site_config->get('name');

    $config = \Drupal::config('isa_site_config.config');
    $build['#site_description'] = $config->get('description');
    $build['#site_email'] = $config->get('email');
    $build['#site_phone'] = $config->get('phone');
    $build['#site_address'] = $config->get('address');
    $build['#site_schedule'] = $config->get('schedule');

    $socail_config = \Drupal::config('isa_social_networks.settings');
    $build['#facebook'] = $socail_config->get('facebook');
    $build['#twitter'] = $socail_config->get('twitter');
    $build['#instagram'] = $socail_config->get('instagram');
    $build['#linkedin'] = $socail_config->get('linkedin');

    $site_contact_image_url = \Drupal::service('module_handler')
      ->getModule('isa_site_config')
      ->getPath().'/img/map.jpg';
    $contact_image = $config->get('contact_image');
    if ($contact_image && count($contact_image)) {
      $img_id = array_pop($contact_image);
      $file = File::load($img_id);
      if ($file) {
        $site_contact_image_url = $file->createFileUrl();
      }
    }

    $build['#site_contact_image_url'] = $site_contact_image_url;

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['isa_site_config_address_map_settings'] = $form_state->getValue('isa_site_config_address_map_settings');
  }

}

