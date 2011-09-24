<?php

require_once WPML_JSON_API_PATH.'/models/post.php';

class WPML_JSON_API_Page extends WPML_JSON_API_Post {
  protected $type = 'page';
  protected $wpml_type = 'post_page';
}
