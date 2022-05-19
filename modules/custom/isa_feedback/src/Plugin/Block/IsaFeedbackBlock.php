<?php

namespace Drupal\isa_feedback\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a 'Feedback' Block.
 *
 * @Block(
 *   id = "isa_feedback_block",
 *   admin_label = @Translation("Isa Feedback Block"),
 *   category = @Translation("Isa Feedback"),
 * )
 */
class IsaFeedbackBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * ConfigFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    // Instantiates this class.
    return new static(
      // Load the service required to construct this class.
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('config.factory')
    );
  }

  /**
   * Class constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, ConfigFactoryInterface $configFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->configFactory->get('webform.webform.isa_feedback_webform');
    $webform_id = $config->get('id');
    $webform = $this->entityTypeManager->getStorage('webform')->load($webform_id);
    $page_submit_path = $webform->getSetting('page_submit_path');
    $url = Url::fromUri('internal:' . $page_submit_path);

    $block = [
      '#theme' => 'isa_feedback_block',
      '#url' => $url,
      '#description' => $this->t('help us to improve')
    ];

    return $block;

  }

}
