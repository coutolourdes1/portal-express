<?php
/**
 * @file
 * Contiene los hooks updates para no tenerlo en el fichero del install.
 */

use Drupal\field\Entity\FieldConfig;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Instalar modulos [maxlength , isa_paragraph_multiple_images]
 * Update [ isa_field_storage, isa_block_gallery, isa_page,
 * isa_paragraph_image, isa_tags, isa_news,isa_block_links,
 * isa_paragraph_links_list] En el TC pagina Eliminar el campo imagen y pasar
 * los datos para la galeria En noticia eliminar el campo municpio y pasar los
 * datos para municpios
 */
function isa_update_9001() {
  \Drupal::service('module_installer')->install([
    'material_icons',
  ]);

  _isa_import_config(['isa_field_storage']);

  \Drupal::service('module_installer')->install([
    'maxlength',
    'scheduler',
    'isa_paragraph_multiple_images',
  ]);
  _isa_import_config([
    'isa_block_gallery',
    'isa_page',
    'isa_paragraph_image',
    'isa_tags',
    'isa_news',
    'isa_block_links',
    'isa_paragraph_links_list',
  ]);

  // Cambiando campo field_municipio por field_municipalities en noticias
  $news = \Drupal::service('entity_type.manager')->getStorage('node')
    ->loadByProperties(['type' => 'news']);

  foreach ($news as $new) {
    $new->field_municipalities->value = [
      $new->get('field_municipio')
        ->getValue(),
    ];
  }

  FieldConfig::loadByName('node', 'news', 'field_municipio')->delete();

  // Cambiando campo field_images por field_gallery en pagina
  $pages = \Drupal::service('entity_type.manager')->getStorage('node')
    ->loadByProperties(['type' => 'page']);

  foreach ($pages as $page) {
    $medias = [];

    foreach ($page->get('field_images')->getValue() as $image) {
      $mid = $image['target_id'];

      $image = Paragraph::create([
        'type' => 'images',
        'field_description' => $image['title'],
        'field_image' => [
          'target_id' => $mid,
        ],
      ]);
      $image->save();
      $medias[] = [
        'target_id' => $image->id(),
        'target_revision_id' => $image->getRevisionId(),
      ];
    }

    if (count($medias)) {
      $gallery = Paragraph::create([
        'type' => 'gallery',
        'field_medias' => $medias,
      ]);
      $gallery->save();

      $page->set('field_gallery', $gallery);
      $page->save();
    }

  }
  FieldConfig::loadByName('node', 'page', 'field_images')->delete();
}

/**
 * Instalar modulos [material_icons],
 * Actulizando[isa_field_storage,isa_block_enlace, isa_news ]
 */
function isa_update_9002() {
  \Drupal::service('module_installer')->install([
    'material_icons',
    'extlink'
  ]);
  _isa_import_config([
    'isa_field_storage',
    'isa_block_enlace',
    'isa_block_highlighted_circle',
    'isa_paragraph_image',
    'isa_news',
    'isa_paragraph_gallery',
    'isa_block_simple',
    'isa_layout_page',
    'isa_site',
    'isa_block_download_list'
  ]);


  drupal_flush_all_caches();

  // Estableciendo variante con imagen por defecto
  $blocks = \Drupal::service('entity_type.manager')->getStorage('block_content')
    ->loadByProperties(['type' => ['highlighted_circle', 'link']]);

  foreach ($blocks as $block) {
    $block->set('field_link_block_variant', 'imagen');
    $block->save();
  }

  //Eliminando campo municipio si existe en noticias
  $field_municipio = FieldConfig::loadByName('node', 'news', 'field_municipio');
  if ($field_municipio) {
    $field_municipio->delete();
  }
}

/**
 * Agregando peso al Horario
 */
function isa_update_9003() {
  _isa_import_config([
    'isa_schedule',
    'isa_block_gallery',
    'isa_site',
    'isa_news'
  ]);
}

/**
 * Actualizando isa_activity y isa_news
 */
function isa_update_9004() {
  _isa_import_config([
    'isa_activity',
    'isa_news'
  ]);
}

/**
 * 1. Quitando galería por defecto en el TC Page.
 * 2. Arreglando el orden de las actividades en las vistas.
 * 3. Agregando variante para el bloque del carrusel.
 * 4. Agregando vista con noticias recientes
 * 5. Refactorizando TC "turist_information"
 */
function isa_update_9005() {

  FieldConfig::loadByName('node', 'turist_information', 'field_attachments')->delete();
  FieldConfig::loadByName('node', 'turist_information', 'field_email_address')->delete();
  FieldConfig::loadByName('node', 'turist_information', 'field_links')->delete();
  FieldConfig::loadByName('node', 'turist_information', 'field_subtitle')->delete();
  FieldConfig::loadByName('node', 'turist_information', 'field_iframe')->delete();

  _isa_import_config(['isa_field_storage']);
  _isa_import_config([
    'isa_page',
    'isa_activity',
    'isa_block_slider',
    'isa_news',
    'isa_turist_info',
    'isa_core',
    'isa_paragraph_image',
    'isa_block_slider',
    'isa_content_moderation',
    'isa_feedback',
    'isa_public_information'
  ]);
}


/**
 * Version 1.5.6
 * 1. Creando alias automática para Página de diseño y Información turística.
 * 2. Cambiando el orden de los elementos en las secciones de actividades
 * 3. Instalando modulo webform_ui
 * 4. Campo fecha multiple en actividad.
 * 5. Agregando departamento a las autoridades.
 * 6. Mostrando solo des niveles en el menu principal y multiples en el menu lateral
 * 7. Configurando recaptcha v2
 * 8. Ocultando acciones masivas en la vista Contenido
 * 9. Agregando capstcha a los formularios
 * 10. Agregando bloque de archivos por municpio y tipo
 * 11. Filtro de las paginas de diseño.
 * 12. Agregando texto con formato al acordion
 * 13. Mostrando HTML en el bloque destacado
 * 14. Limitando vistas que se muestran en ellayout builder.
 */
function isa_update_9006() {
  _isa_import_config([
    'isa_field_storage'
  ]);

  \Drupal::service('module_installer')->install([
    'webform_ui',
    'isa_paragraph_event_date',
    'isa_department',
    'recaptcha',
    'multivalue_form_element'
  ]);

  _isa_import_config([
    'isa_site',
    'isa_activity',
    'isa_authorities',
    'isa_feedback',
    'isa_site',
    'isa_public_information',
    'isa_file',
    'isa_layout_page',
    'isa_paragraph_text',
    'isa_news',
    'isa_communication',
    'isa_turist_info',
    'isa_page'
  ]);

  // Cambiando campo field_images por field_gallery en pagina
  $activities = \Drupal::service('entity_type.manager')->getStorage('node')
    ->loadByProperties(['type' => 'activity']);

  foreach ($activities as $activity) {
      $dates = Paragraph::create([
        'type' => 'event_date',
        'field_date' => $activity->get('field_date'),
        'field_range_time' => $activity->get('field_time_range')
      ]);

    $dates->save();

    $activity->set('field_dates', $dates);
    $activity->save();
  }
  FieldConfig::loadByName('node', 'activity', 'field_date')->delete();
  FieldConfig::loadByName('node', 'activity', 'field_time_range')->delete();

  \Drupal::service('module_installer')->uninstall([
    'recaptcha_v3'
  ]);

  // Paragraph text_item
  $paragraphs = \Drupal::service('entity_type.manager')->getStorage('paragraph')
    ->loadByProperties(['type' => ['text_item']]);

  foreach ($paragraphs as $paragraph) {
    $paragraph->field_rich_text->value = $paragraph->field_text->value;
    $paragraph->field_rich_text->format ='basic_html';
    $paragraph->save();
  }

  FieldConfig::loadByName('paragraph', 'text_item', 'field_text')->delete();
}

/**
 * Version 1.6.0
 * 1. Agregando campo filtro al tipo de bloque slider
 */
function isa_update_9160() {
  _isa_import_config([
    'isa_field_storage'
  ]);

  _isa_import_config([
    'isa_block_slider',
    'isa_file',
    'isa_paragraph_image'
  ]);
}

/**
 * Version 1.6.2
 * 1. Eliminando modulo group
 */
function isa_update_9162() {
  \Drupal::service('module_installer')->uninstall([
    'group',
  ]);
}

/**
 * Version 1.6.3
 * 1. Agregando patron de url a las layouts page
 * 2. Actualizando descripción de la imagen de noticias
 */
function isa_update_9163() {
  _isa_import_config([
    'isa_layout_page',
    'isa_news'
  ]);
}

/**
 * Version 1.6.4
 * 1. Agregando vista de resumen de actividades
 */
function isa_update_9164() {
  _isa_import_config([
    'isa_authorities'
  ]);
}

/**
 * Version 1.6.6
 * 1. Quitar del editor de Ckeditor el boon de mostrar el HTML.
 *
 */
function isa_update_9166() {
  _isa_import_config([
    'isa_site'
  ]);
}


/**
 * Version 1.7.1
 * Se agrego un campo en el tipo de contenido Pagina.
 *
 */

function isa_update_9171() {
  _isa_import_config([
    'isa_field_storage',
    'isa_page'
  ]);
}

