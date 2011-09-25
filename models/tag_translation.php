<?php

require_once WPML_JSON_API_PATH.'/models/translation.php';

/**
 * A translation of a WPML_JSON_API_Tag.
 */
class WPML_JSON_API_TagTranslation extends WPML_JSON_API_Translation {
  public $name;
  public $term_id;
  public $post_count;

  function __construct($tag, $translation) {
    parent::__construct($tag, $translation);

    $this->name = $translation->name;
    $this->term_id = (integer) $translation->term_id;
    $this->post_count = (integer) $translation->instances;
  }

  /**
   * Returns the tag for this translation.
   *
   * @return JSON_API_Tag
   */
  function resolve_resource() {
    $wp_tag = get_term_by('id', $this->resource_id, 'post_tag');

    if (!is_null($wp_tag)) {
      return $this->_resource = new JSON_API_Tag($wp_tag);
    }
  }

}
