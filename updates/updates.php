<?php

use Drupal\field\Entity\FieldConfig;
use Drupal\paragraphs\Entity\Paragraph;

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
      'museos_destacado', 
    ]);
  }

