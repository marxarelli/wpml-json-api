<?php

require_once WPML_JSON_API_PATH.'/models/category.php';

class WPML_JSON_API_Tag extends WPML_JSON_API_Category {
  protected $type = 'tag';
  protected $wpml_type = 'tax_post_tag';
}
