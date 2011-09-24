<?php

require_once WPML_JSON_API_PATH.'/models/resource.php';

class WPML_JSON_API_Category extends WPML_JSON_API_Resource {
  protected $type = 'category';
  protected $wpml_type = 'tax_category';
}
