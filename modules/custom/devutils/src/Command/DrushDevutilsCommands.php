<?php

namespace Drupal\devutils\Command;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drush\Commands\DrushCommands;

/**
 * A drush command file.
 *
 * @package Drupal\devutils\Command
 */
class DrushDevutilsCommands extends DrushCommands {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;


  /**
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entity_type_manager = $entity_type_manager;
  }

  /**
   * Drush command that export uuid for entities.
   *
   * @param string $entityType
   *   Entity type to search, available [node, menu_link, block,media,
   *   file,term ].
   * @param string $filter
   *   Filter to search, all.
   *
   * @command devutils:uuid
   * @aliases devutils uuid
   * @usage devutils:uuid node page
   * @option label Display label to entity
   */
  public function devutils($entityType = 'node', $filter = 'all', $options = ['label' => FALSE]) {
    $entities = [];
    if ($entityType === 'menu_link') {
      $menu_content_storage = $this->entity_type_manager
        ->getStorage('menu_link_content');
      /** @var \Drupal\menu_link_content\MenuLinkContentInterface[] $menu_link_contents */
      $entities = $menu_content_storage->loadByProperties(['menu_name' => $filter]);
    }

    if ($entityType === 'block') {
      if ($filter === 'all') {
        $entities = $this->entity_type_manager
          ->getStorage('block_content')
          ->loadMultiple();
      }
      else {
        $entities = $this->entity_type_manager
          ->getStorage('block_content')
          ->loadByProperties(['type' => $filter]);
      }
    }

    if ($entityType === 'node') {
      if ($filter === 'all') {
        $entities = $this->entity_type_manager
          ->getStorage('node')
          ->loadMultiple();
      }
      else {
        $entities = $this->entity_type_manager
          ->getStorage('node')
          ->loadByProperties(['type' => $filter]);
      }
    }

    if ($entityType === 'media') {
      $entities = $this->entity_type_manager
        ->getStorage('media')
        ->loadByProperties(['bundle' => $filter]);
    }

    if ($entityType === 'file') {
      /** @var \Drupal\file\FileInterface[] $files */
      $entities = $this->entity_type_manager
        ->getStorage('file')
        ->loadMultiple();
    }

    if ($entityType === 'term') {
      $entities = $this->entity_type_manager
        ->getStorage('taxonomy_term')
        ->loadByProperties(['vid' => $filter]);
    }

    if ($entityType === 'paragraph') {
      $entities = $this->entity_type_manager
        ->getStorage('paragraph')
        ->loadByProperties(['type' => $filter]);
    }


    foreach ($entities as $entity) {
      if ($options['label']) {
        $this->output()->writeln('# ' . $entity->label());
      }
      $this->output()->writeln('- ' . $entity->uuid());
    }
  }

  /**
   * Drush command that clear not used file.
   *
   *
   * @command devutils:clear-files
   * @aliases devutils clear-files
   * @usage devutils:clear-files
   */
  public function clearFiles() {
    /** @var \Drupal\file\FileInterface[] $files */
    $files = \Drupal::entityTypeManager()
      ->getStorage('file')
      ->loadByProperties();

    foreach ($files as $file) {
      $listUsage = \Drupal::service('file.usage')->listUsage($file);
      if (count($listUsage) == 0) {
        $file->delete();
        $this->output()->writeln('Delete file:' . $file->label());
      }
    }

  }

}