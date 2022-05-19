<?php

namespace Drupal\isa_language\Commands;

use Drupal\isa_language\LanguageGenerator;
use Drush\Commands\DrushCommands;
use Drush\Utils\StringUtils;

/**
 * Drush commands for Features.
 */
class IsaLanguageCommands extends DrushCommands {

  /**
   * The config_update.config_diff service.
   *
   * @var LanguageGenerator
   */
  protected $generate;


  /**
   * FeaturesCommands constructor.
   *
   * @param LanguageGenerator $generate
   *   The isa_language.generator service.
   */
  public function __construct(LanguageGenerator $generate) {
    parent::__construct();
    $this->generate = $generate;

  }

  /**
   * Export language of Features.
   *
   * @param string $features
   *   A possibly empty, comma-separated, list of config information to display.
   *
   * @command isa_language:generate
   *
   * @aliases isalg,isa_language-generate
   */
  public function generate($features = NULL) {
    if (!empty($features)) {
      $features = StringUtils::csvToArray($features);
    }
    $this->generate->generateFeatures($features);
  }

}
