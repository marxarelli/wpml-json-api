<?php

require_once WPML_JSON_API_PATH.'/lib/model.php';

class WPML_JSON_API_Resource extends WPML_JSON_API_Model {
  /**
   * The properties that can be translated.
   * @var array
   */
  public $translatable_properties = array();

  /**
   * Encapsulated JSON-API model instance.
   * @var mixed
   */
  protected $resource;

  /**
   * The type of resource this is.
   * @var string
   */
  protected $type;

  /**
   * The type of resource this is, according to the WPML CMS plugin.
   * @var string
   */
  protected $wpml_type;

  private $_translations;

  /**
   * Instantiates a new resource by the given type.
   */
  static function create($type, $resource) {
    if ($class = parent::create($type)) {
      return new $class($resource);
    }
  }

  /**
   * Instantiate a new resource.
   *
   * @param mixed $resource Resource to extend.
   */
  function __construct($resource) {
    $this->resource = $resource;
  }

  /**
   * Returns the language of the resource.
   *
   * @return  string
   */
  function language() {
    foreach($this->get_translations() as $translation) {
      if ($this->resource->id == $translation->resource_id) {
        return $translation->language_code;
      }
    }
  }

  /**
   * Returns the translation for the given language.
   *
   * @param string  $language Language code.
   *
   * @return stdclass
   */
  function get_translation($language) {
    $translations = $this->get_translations();
    return isset($translations[$language]) ? $translations[$language] : null;
  }

  /**
   * Returns all translations of the resource.
   *
   * @return array
   */
  function get_translations() {
    if (isset($this->_translations)) {
      return $this->_translations;
    }

    $details = $this->sp->get_element_language_details($this->resource->id, $this->wpml_type);

    $translations = array();

    if ($sp_translations = $this->sp->get_element_translations($details->trid, $this->wpml_type)) {
      foreach ($sp_translations as $key => $tr) {
        $translations[$key] = WPML_JSON_API_Translation::create($this->type, $this->resource, $tr);
      }
    }

    return $this->_translations = $translations;
  }

  /**
   * Returns a translated version of the resource in the given language.
   *
   * @param string  $to_lang  Target language.
   *
   * @return mixed
   */
  function translate($to_lang) {
    if ($to_lang != $this->language() && $translation = $this->get_translation($to_lang)) {
      // Only consider published translations
      if ($translation->published()) {
        return $translation->resource();
      }
    }
  }

  /**
   * Translates the given resource to the given language.
   *
   * @param mixed   $resource Resource to translate.
   * @param string  $to_lang  Target language.
   */
  function translate_resource(&$resource, $to_lang) {
    $resource->translated = false;

    if ($translated_resource = $this->translate($to_lang)) {
      $resource->translated = true;
      $resource->language = $to_lang;

      // Rewrite translatable properties of the original.
      foreach ($this->translatable_properties as $property) {
        if (!in_array($property, array('content', 'excerpt'))) {
          $resource->{"original_$property"} = $resource->{$property};
        }
        if (isset($translated_resource->{$property})) {
          $resource->{$property} = $translated_resource->{$property};
        }
      }

    }
  }
}
