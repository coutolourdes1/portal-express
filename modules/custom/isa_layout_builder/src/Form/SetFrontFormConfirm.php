<?php

namespace Drupal\isa_layout_builder\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Implements a SetFront Confirm Form.
 */
class SetFrontFormConfirm extends ConfirmFormBase {

  /**
   * Alias to set front.
   *
   * @var string
   */
  protected $alias;

  /**
   * @return string
   */
  public function getFormId() {
    return 'isa_layout_builder_set_front_form_confirm';
  }

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   *
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $id = NULL) {
    $this->alias = \Drupal::request()->query->get('alias');
    return parent::buildForm($form, $form_state);
  }

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::configFactory()->getEditable('system.site');
    $config->set('page.front', $this->alias)->save();

    \Drupal::messenger()
      ->addStatus($this->t('"@alias" was set as the home page',
        ['@alias' => $this->alias]
      ));

    $url = Url::fromRoute('view.layout_pages.page_1');
    $form_state->setRedirectUrl($url);

    return $url;
  }


  /**
   * Returns the question to ask the user.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The form question. The page title will be set to this value.
   */
  public function getQuestion() {
    return $this->t('Do you want set the url "@alias" as front page?', ['@alias' => $this->alias]);
  }

  /**
   * Returns the route to go to if the user cancels the action.
   *
   * @return \Drupal\Core\Url
   *   A URL object.
   */
  public function getCancelUrl() {
    return Url::fromRoute('view.layout_pages.page_1');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('This action changes the home page of the site, you can disassemble by setting another home page');
  }

}