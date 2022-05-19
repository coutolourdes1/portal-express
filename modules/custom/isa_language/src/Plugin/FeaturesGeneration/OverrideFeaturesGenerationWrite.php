<?php

namespace Drupal\isa_language\Plugin\FeaturesGeneration;

use Drupal\features\FeaturesBundleInterface;
use Drupal\features\Plugin\FeaturesGeneration\FeaturesGenerationWrite;
use Drupal\isa_language\LanguageGenerator;

/**
 * Class for writing packages to the local file system.
 *
 * @Plugin(
 *   id =
 *   \Drupal\features\Plugin\FeaturesGeneration\FeaturesGenerationWrite::METHOD_ID,
 *   weight = 1,
 *   name = @Translation("Write"),
 *   description = @Translation("Write packages and optional profile to the file system."),
 * )
 */
class OverrideFeaturesGenerationWrite extends FeaturesGenerationWrite {
  public function generate(array $packages = array(), FeaturesBundleInterface $bundle = NULL) {
    $result = parent::generate($packages, $bundle);
    /** @var LanguageGenerator $languageGenerator */
    $languageGenerator = \Drupal::service('isa_language.generator');

    /** @var \Drupal\features\Package $package */
    foreach ($packages as $package) {
      $languageGenerator->generateFiles($package->getMachineName());
    }

    return $result;
  }
}
