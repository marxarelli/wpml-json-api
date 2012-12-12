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

  /**
   * Instantiates a new post resource. The wpml_type property is set according
   * to any custom post type.
   *
   * @param mixed $resource Resource to extend.
   */
  function __construct($resource) {
    parent::__construct($resource);

    if (isset($resource->type)) {
      $this->wpml_type = "post_{$resource->type}";
    }
  }

}
