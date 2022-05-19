<?php

namespace Drupal\isa_language\Plugin\FeaturesGeneration;


use Drupal\features\FeaturesBundleInterface;
use Drupal\features\Plugin\FeaturesGeneration\FeaturesGenerationArchive;


// Todo
class OverrideFeaturesGenerationArchive extends FeaturesGenerationArchive {
   public function generate(array $packages = array(), FeaturesBundleInterface $bundle = NULL) {
     $result = parent::generate($packages, $bundle);
     // Todo ver como se hace esto si es necesario
     return $result;
   }
}
