<?php

require_once WPML_JSON_API_PATH.'/models/resource.php';

/**
 * Encapsulates JSON_API_Post to provide translation extensions.
 */
class WPML_JSON_API_Post extends WPML_JSON_API_Resource {
  public $translatable_properties = array(
    'slug',
    'title',
    'title_plain',
    'content',
    'excerpt',
  );
  protected $type = 'post';
  protected $wpml_type = 'post_post';
}
