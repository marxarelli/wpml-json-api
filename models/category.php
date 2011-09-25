<?php

require_once WPML_JSON_API_PATH.'/models/resource.php';

class WPML_JSON_API_Category extends WPML_JSON_API_Resource {
  public $translatable_properties = array(
    'slug',
    'title',
    'description',
  );
  protected $type = 'category';
  protected $wpml_type = 'tax_category';
}
