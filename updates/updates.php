<?php

use Drupal\field\Entity\FieldConfig;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;

function isa_update_9001(){
    \Drupal::service('module_installer')->uninstall([
      'isa_block_alert',
    ]);
  }

  function isa_update_9002(){
    \Drupal::service('module_installer')->uninstall([
      'isa_activity', 'isa_authorities' , 'isa_document' , 'isa_schedule' , 'isa_news' , 
      'isa_page' , 'isa_turist_info' , 'isa_expiration' , 'isa_block_accordion' ,
      'isa_block_audio' , 'isa_block_card' , 'isa_block_highlighted_circle' , 'isa_block_enlace' ,
      'isa_block_header' , 'isa_block_links' , 'isa_block_download_list' , 
    ]);
  }

  function isa_update_9003(){
    _isa_import_config([
      'isa_field_storage',
    ]);
    \Drupal::service('module_installer')->install([
      'introduccion', 'presentacion', 
    ]);
  }

  function isa_update_9004(){

    Vocabulary::create([
      'vid' => 'ejemplo',
      'name' => 'Ejemplo',
    ])->save();

    $vocab = 'ejemplo';
    $items = [
      'Blue',
      'Red',
      'Hot Pink',
    ];
    foreach ($items as $item) {
      $term = Term::create(array(
        'parent' => array(),
        'name' => $item,
        'vid' => $vocab,
      ))->save();
    }
  }

  function isa_update_9005(){
    \Drupal::service('module_installer')->install([
      'image_widget_crop', 'crop',
    ]);
  }


  function isa_update_9007(){
    _isa_import_config([
      'isa_field_storage', 'isa_news'
    ]);
  }