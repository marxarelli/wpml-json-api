<?php

require_once WPML_JSON_API_PATH.'/models/translation.php';

/**
 * A translation of a WPML_JSON_API_Post.
 */
class WPML_JSON_API_PostTranslation extends WPML_JSON_API_Translation {
  public $post_id;
  public $post_title;
  public $post_status;

  function __construct($post, $translation) {
    parent::__construct($post, $translation);

    $this->post_id = $this->resource_id;
    $this->post_status = $translation->post_status;
    $this->post_title = $translation->post_title;
  }

  /**
   * Returns the post for this translation.
   *
   * @return JSON_API_Post
   */
  function resolve_resource() {
    $wp_post = get_post($this->resource_id);

    if (!is_null($wp_post)) {
      return $this->_resource = new JSON_API_Post($wp_post);
    }
  }

  /**
   * Returns whether the translation has been published.
   */
  function published() {
    return $this->post_status == 'publish';
  }
}
