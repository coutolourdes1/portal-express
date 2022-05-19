<?php

namespace Drupal\isa_site_config\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a block with a map.
 *
 * @Block(
 *   id = "isa_site_config_address_map",
 *   admin_label = @Translation("Site address map"),
 * )
 */
class AddressMap extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = \Drupal::config('isa_site_config.config');
    $address =  $config->get('address');

    $url_suffix = urlencode($address);
    return  [
      '#theme' => 'simple_gmap_output',
      '#include_map' => TRUE,
      '#include_static_map' => FALSE,
      '#include_link' => FALSE,
      '#include_text' => FALSE,
      '#width' => ['#plain_text' => '100%'],
      '#iframe_title' => ['#plain_text' => ''],
      '#static_scale' => 1,
      '#url_suffix' => $url_suffix,
      '#zoom' => 14,
      '#link_text' => ['#plain_text' => 'View larger map'],
      '#address_text' => '',
      '#map_type' => 'm',
      '#langcode' => ['#plain_text' => 'en'],
      '#static_map_type' => 'roadmap',
      '#apikey' => ''
    ];
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

