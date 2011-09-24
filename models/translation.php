<?php

require_once WPML_JSON_API_PATH.'/lib/model.php';

/**
 * A translation of a resource.
 */
abstract class WPML_JSON_API_Translation extends WPML_JSON_API_Model {
  public $id;
  public $language_code;
  public $is_original;
  public $resource_id;

  protected $original_resource;

  protected $_resource;

  /**
   * Instantiates a new translation by the given type.
   *
   * @param string    $type         Resource type.
   * @param mixed     $resource     Original resource.
   * @param stdclass  $translation  WPML translation.
   */
  static function create($type, $resource, $translation) {
    if ($class = parent::create("{$type}_translation")) {
      return new $class($resource, $translation);
    }
  }

  /**
   * Constructs a translation from the given resource and the given WPML 
   * translation object.
   *
   * @param mixed     $resource     Original resource.
   * @param stdclass  $translation  WPML translation.
   */
  function __construct($resource, $translation) {
    $this->id = (integer) $translation->translation_id;
    $this->language_code = $translation->language_code;
    $this->is_original = (boolean) $translation->original;
    $this->resource_id = (integer) $translation->element_id;

    $this->original_resource = $resource;
  }

  /**
   * Returns the resource that corresponds to the translation type.
   *
   * @return mixed
   */
  abstract function resolve_resource();

  /**
   * Returns the resource for this translation.
   *
   * @return mixed
   */
  function resource() {
    if (isset($this->_resource) && !is_null($this->_resource)) {
      return $this->_resource;
    }
    else {
      return $this->resolve_resource();
    }
  }

  /**
   * Returns whether the translation has been published. Defaults to true in 
   * this implementation, but sub-classes should reimplement if possible.
   *
   * @return boolean
   */
  function published() {
    return true;
  }
}
