<?php

namespace Drupal\isa_language;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\features\FeaturesManagerInterface;
use Drush\Utils\StringUtils;
use Psr\Log\LoggerInterface;

/**
 * Class responsible for performing package generation.
 */
class LanguageGenerator {

  use StringTranslationTrait;

  /**
   * The file_system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The features manager.
   *
   * @var \Drupal\features\FeaturesManagerInterface
   */
  protected $featuresManager;

  /**
   * The config.storage service.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $configStorage;

  /**
   * The app root.
   *
   * @var string
   */
  protected $root;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a new FeaturesGenerator object.
   *
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The filesystem service.
   *   The feature assigner interface.
   *
   * @param \Drupal\features\FeaturesManagerInterface $features_manager
   *   The features manager.
   *
   * @param \Drupal\Core\Config\StorageInterface $configStorage
   *   The config.storage service.
   *
   * @param string $root
   *   The app root.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   */
  public function __construct(FileSystemInterface $fileSystem,
                              FeaturesManagerInterface $features_manager,
                              StorageInterface $configStorage,
                              string $root,
                              MessengerInterface $messenger,
                              LoggerInterface $logger) {
    $this->fileSystem = $fileSystem;
    $this->featuresManager = $features_manager;
    $this->configStorage = $configStorage;
    $this->root = $root;
    $this->messenger = $messenger;
    $this->logger = $logger;

    $assigner = \Drupal::service('features_assigner');
    $assigner->assignConfigPackages();
  }

  public function generateFeatures($features = NULL){
    if (empty($features)) {
      $features = $this->getAllFeatures();
    }
    foreach ($features as $feature){
      $this->generateFiles($feature);
    }
  }

  public function generateFiles($feature) {
    $manager = $this->featuresManager;
    $package = $manager->getPackage($feature);
    $configs = $package->getConfigOrig();

    $existing_packages = $this->featuresManager->listPackageDirectories([$feature]);

    $collectionFiles = [];
    foreach ($this->configStorage->getAllCollectionNames() as $collectionName) {
      $collection = $this->configStorage->createCollection($collectionName);
      foreach ($configs as $config) {
        if ($collection->exists($config)) {
          $data = $collection->read($config);
          $collectionFiles[$collectionName][$config] = Yaml::encode($data);
        }
      }
    }

    //Todo, esto no esta funcionando con las features nuevas
    if(isset($existing_packages[$feature])) {
      $this->writeFiles($collectionFiles, $existing_packages[$feature]);
    }
  }

  /**
   * Write files in respectives modules
   *
   * @param $collectionFiles
   * @param $directory
   *
   * @throws \Exception
   */
  private function writeFiles($collectionFiles, $directory) {

    $destinationDir = $directory . '/config/install';
    foreach ($collectionFiles as $collectionName => $collection) {
      $collection_directory = $destinationDir . '/' . str_replace('.', '/', $collectionName);
      if (is_dir($collection_directory)) {
        $this->fileSystem->deleteRecursive($collection_directory);
      }
      foreach ($collection as $config => $data) {
        $file = [
          'filename' => $config . '.yml',
          'string' => $data,
        ];
        $this->generateFile($collection_directory, $file);
      }
    }
  }

  /**
   * Writes a file to the file system, creating its directory as needed.
   *
   * @param string $directory
   *   The extension's directory.
   * @param array $file
   *   Array with the following keys:
   *   - 'filename': the name of the file.
   *   - 'subdirectory': any subdirectory of the file within the extension
   *      directory.
   *   - 'string': the contents of the file.
   *
   * @throws Exception
   */
  protected function generateFile($directory, array $file) {
    $directory = $this->root . '/' . $directory;
    if (!is_dir($directory)) {
      if ($this->fileSystem->mkdir($directory, NULL, TRUE) === FALSE) {
        throw new \Exception($this->t('Failed to create directory @directory.', ['@directory' => $directory]));
      }
    }
    if (file_put_contents($directory . '/' . $file['filename'], $file['string']) === FALSE) {
      throw new \Exception($this->t('Failed to write file @filename.', ['@filename' => $file['filename']]));
    }
  }

  public function getAllFeatures() {
    return array_keys($this->featuresManager->listPackageDirectories());
  }

}
