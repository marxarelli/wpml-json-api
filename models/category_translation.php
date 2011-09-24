<?php

require_once WPML_JSON_API_PATH.'/models/translation.php';

/**
 * A translation of a WPML_JSON_API_Category.
 */
class WPML_JSON_API_CategoryTranslation extends WPML_JSON_API_Translation {
  public $name;
  public $term_id;
  public $post_count;

  function __construct($category, $translation) {
    parent::__construct($category, $translation);

    $this->name = $translation->name;
    $this->term_id = (integer) $translation->term_id;
    $this->post_count = (integer) $translation->instances;
  }

  /**
   * Returns the category for this translation.
   *
   * @return JSON_API_Category
   */
  function resolve_resource() {
    $wp_cat = get_category($this->resource_id);

    if (!is_null($wp_cat)) {
      return $this->_resource = new JSON_API_Category($wp_cat);
    }
  }

}
