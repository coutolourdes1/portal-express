<?php

namespace Drupal\isa_site_config\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\multivalue_form_element\Element\MultiValue;

/**
 * Class ConfigForm.
 */
class ConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'isa_site_config.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('isa_site_config.config');

    // Main Section.
    $form['main'] = [
      '#type' => 'details',
      '#title' => $this->t('Main'),
      '#description' => $this->t('Configuración general del sitio'),
    ];

    $form['main']['description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#maxlength' => 255,
      '#size' => 100,
      '#default_value' => $config->get('description'),
      '#access' => FALSE,
    ];

    $form['main']['banner'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Logo'),
      '#description' => $this->t('Imagen que se muestra en la cabecera del sitio. Solo se permiten png jpg jpeg svg'),
      '#default_value' => $config->get('banner', ''),
      '#upload_location' => 'public://',
      '#upload_validators' => [
        'file_validate_is_image' => [],
        'file_validate_extensions' => ['png jpg jpeg svg'],
      ],
      '#accept' => '.png,.jpg,.jpeg,.svg',
    ];

    // Contact Section.
    $form['contact'] = [
      '#type' => 'details',
      '#title' => $this->t('Contact'),
      '#description' => $this->t('Información de contacto de la institución, se muestra en la página del formulario de contacto.'),
    ];

    $form['contact']['contact_image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Picture'),
      '#description' => $this->t('Se muestra en el bloque de contactos. Solo se permiten png jpg jpeg svg'),
      '#default_value' => $config->get('contact_image', ''),
      '#upload_location' => 'public://',
      '#upload_validators' => [
        'file_validate_is_image' => [],
        'file_validate_extensions' => ['png jpg jpeg svg'],
      ],
      '#accept' => '.png,.jpg,.jpeg,.svg',
    ];

    $form['contact']['address'] = [
      '#type' => 'textfield',
      '#title' => 'Dirección',
      '#maxlength' => 255,
      '#size' => 60,
      '#default_value' => $config->get('address'),
    ];

    $form['contact']['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#default_value' => $config->get('email'),
    ];

    $form['contact']['phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone'),
      '#maxlength' => 255,
      '#size' => 60,
      '#default_value' => $config->get('phone'),
    ];

    $form['contact']['schedule'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Schedule'),
      '#maxlength' => 255,
      '#size' => 60,
      '#default_value' => $config->get('schedule'),
    ];

    // Contact Form Section.
    $form['contact_form'] = [
      '#type' => 'details',
      '#title' => $this->t('Contact Form'),
      '#description' => $this->t('Detalles personalisables del formulario de contacto..'),

    ];

    $form['contact_form']['contact_form_description'] = [
      '#type' => 'textarea',
      '#description' => $this->t('Text displayed in the header of the contact form'),
      '#title' => $this->t('Description'),
      '#default_value' => $config->get('contact_form_description'),
    ];

    $form['contact_form']['contact_form_purpose'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Purpose of the contact form'),
      '#title' => $this->t('Purpose'),
      '#default_value' => $config->get('contact_form_purpose'),
    ];


    if (\Drupal::moduleHandler()->moduleExists('webform')) {
      $settings = $this->config('isa_site_config.settings');

      $form['emails'] = [
        '#type' => 'details',
        '#title' => $this->t('Emails'),
        '#description' => $this->t('Lista de correos.'),
      ];

      $webforms = \Drupal::entityTypeManager()
        ->getStorage('webform')
        ->loadMultiple(NULL);
      $options = [];
      foreach ($webforms as $id => $webform) {
        $options[$id] = $webform->label();
      }

      $form['emails']['emails_webform'] = [
        '#type' => 'multivalue',
        '#title' => $this->t('Webform'),
        '#cardinality' => MultiValue::CARDINALITY_UNLIMITED,
        '#default_value' => $settings->get('webfom_emails')?:[],
        'webform' => [
          '#type' => 'select',
          '#title' => $this->t('Form'),
          '#options' => $options,
          '#required' => FALSE,
          "#empty_value" => '',
          '#weight' => '0',
        ],
        'mail' => [
          '#type' => 'email',
          '#title' => $this->t('E-mail'),
        ],
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $values = $form_state->getValues();

    $this->config('isa_site_config.config')
      ->set('description', $values['description'])
      ->set('banner', $values['banner'])
      ->set('email', $values['email'])
      ->set('contact_image', $values['contact_image'])
      ->set('phone', $values['phone'])
      ->set('address', $values['address'])
      ->set('contact_form_description', $values['contact_form_description'])
      ->set('contact_form_purpose', $values['contact_form_purpose'])
      ->set('schedule', $values['schedule'])
      ->save();

    if ($file_id = $form_state->getValue(['banner', '0'])) {
      $file = \Drupal::entityTypeManager()->getStorage('file')->load($file_id);
      $file->setPermanent();
      $file->save();
    }

    if ($file_id = $form_state->getValue(['contact_image', '0'])) {
      $file = \Drupal::entityTypeManager()->getStorage('file')->load($file_id);
      $file->setPermanent();
      $file->save();
    }

    $webformEmails = $values['emails_webform'];

    if (count($webformEmails)) {
      $emails = [];
       foreach ($webformEmails as $webformEmail){
         if(!empty($webformEmail['webform']) && !empty($webformEmail['mail'])){
           $emails[] = $webformEmail;
         }
       }

      $this->configFactory()
        ->getEditable('isa_site_config.settings')
        ->set('webfom_emails', $emails)
        ->save();
    }

  }

}
