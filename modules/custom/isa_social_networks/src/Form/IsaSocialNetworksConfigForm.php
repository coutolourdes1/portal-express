<?php

namespace Drupal\isa_social_networks\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class IsaSocialNetworksConfigForm extends ConfigFormBase {

    /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'isa_social_networks.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'isa_social_networks_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    // Main Section.
    $form['isa_social_networks'] = array(
        '#type' => 'fieldset',
        '#title' => $this->t('Social Networks'),
      );

    $form['isa_social_networks']['facebook'] = [
      '#type' => 'url',
      '#title' => $this->t('Facebook'),
      '#default_value' => $config->get('facebook'),
    ];

    $form['isa_social_networks']['twitter'] = [
        '#type' => 'url',
        '#title' => $this->t('Twitter'),
        '#default_value' => $config->get('twitter'),
    ];

    $form['isa_social_networks']['instagram'] = [
        '#type' => 'url',
        '#title' => $this->t('Instagram'),
        '#default_value' => $config->get('instagram'),
      ];

    $form['isa_social_networks']['linkedin'] = [
      '#type' => 'url',
      '#title' => $this->t('Linkedin'),
      '#default_value' => $config->get('linkedin'),
    ];

    $form['isa_social_networks']['youtube'] = [
      '#type' => 'url',
      '#title' => $this->t('Youtube'),
      '#default_value' => $config->get('youtube'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $completeForm = $form_state->getCompleteForm();
    if (strlen(trim($completeForm['isa_social_networks']['facebook']['#value'])) > 0 && strpos($completeForm['isa_social_networks']['facebook']['#value'], 'facebook') === false) {
      $form_state->setErrorByName('facebook', $this->t('Enter a valid Facebook url'));
    }
    if (strlen(trim(($completeForm['isa_social_networks']['twitter']['#value']))) > 0 && strpos($completeForm['isa_social_networks']['twitter']['#value'], 'twitter') === false) {
      $form_state->setErrorByName('twitter', $this->t('Enter a valid Twitter url'));
    }
    if (strlen(trim(($completeForm['isa_social_networks']['instagram']['#value']))) > 0 && strpos($completeForm['isa_social_networks']['instagram']['#value'], 'instagram') === false) {
      $form_state->setErrorByName('instagram', $this->t('Enter a valid Instagram url'));
    }
    if (strlen(trim(($completeForm['isa_social_networks']['linkedin']['#value']))) > 0 && strpos($completeForm['isa_social_networks']['linkedin']['#value'], 'linkedin') === false) {
      $form_state->setErrorByName('linkedin', $this->t('Enter a valid Linkedin url'));
    }
    if (strlen(trim(($completeForm['isa_social_networks']['youtube']['#value']))) > 0
      && strpos($completeForm['isa_social_networks']['youtube']['#value'], 'youtube') === false) {
      $form_state->setErrorByName('youtube', $this->t('Enter a valid Youtube url'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->configFactory->getEditable(static::SETTINGS)
      // Set the submitted configuration setting.
      ->set('facebook', $form_state->getValue('facebook'))
      // You can set multiple configurations at once by making
      // multiple calls to set().
      ->set('twitter', $form_state->getValue('twitter'))
      ->set('instagram', $form_state->getValue('instagram'))
      ->set('linkedin', $form_state->getValue('linkedin'))
      ->set('youtube', $form_state->getValue('youtube'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
