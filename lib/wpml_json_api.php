<?php

require_once WPML_JSON_API_PATH.'/models/resource.php';
require_once WPML_JSON_API_PATH.'/models/translation.php';

class WPML_JSON_API {
  /**
   * @var JSON_API
   */
  private $_json;

  /**
   * @var SitePress
   */
  private $_sp;

  /**
   * Registers all filters and actions.
   */
  function register() {
    add_filter('json_api_encode', array($this, 'dispatch_translate_resource_filter'));
    add_filter('json_api_encode', array($this, 'dispatch_add_translations_filter'));
  }

  /**
   * Dispatches the given filter with the given arguments
   */
  function dispatch_filter($filter, $arguments) {
    $response = array_shift($arguments);

    // Filters need only operate on successful responses.
    if ($response['status'] == 'ok') {

      $error = null;

      // Resolve the object to filter and filter it.
      foreach ($response as $type => $value) {
        if (is_object($value)) {
          $resources = array($value);
          $error = $this->$filter($type, $resources);
          break;
        }
        elseif (is_array($value) && is_object($value[0])) {
          $error = $this->$filter($type, $value);
          break;
        }
      }

      // If there was an error, overwrite the response with it.
      return is_null($error) ? $response : $error;
    }

    return $response;
  }

  /**
   * Returns the requested query parameter value.
   *
   * @param string  $name Parameter name.
   *
   * @return string|null
   */
  function get_param($name) {
    if ($params = $this->json->query->get(array($name))) {
      return $params[$name];
    }
    else {
      return null;
    }
  }

  /**
   * Returns whether the provided language is supported.
   * 
   * @param string  $language_code  Language code.
   *
   * @return boolean
   */
  function language_is_supported($language_code) {
    foreach ($this->sp->get_active_languages() as $language) {
      if ($language_code == $language['code']) {
        return true;
      }
    }

    return false;
  }

  /**
   * Augments the response resource with the languages and translations.
   */
  function add_translations($type, &$resources) {
    foreach ($resources as $resource) {
      if ($wpml_resource = WPML_JSON_API_Resource::create($type, $resource)) {
        $resource->translations = array();

        if (($language = $wpml_resource->language()) && !isset($resource->language)) {
          $resource->language = $language;
        }

        foreach ($wpml_resource->get_translations() as $language => $translation) {
          if ($this->language_is_supported($language)) {
            $resource->translations[$language] = $translation;
          }
        }
      }
    }
  }

  /**
   * Translates the content of the JSON-API response if a translation is
   * available.
   */
  function translate_resource($type, &$resources) {
    if ($to_lang = $this->get_param('to_lang')) {
      if ($this->language_is_supported($to_lang)) {
        foreach ($resources as $resource) {
          if ($wpml_resource = WPML_JSON_API_Resource::create($type, $resource)) {
            $wpml_resource->translate_resource($resource, $to_lang);
          }
          else {
            return array('status' => 'error', 'error' => "Type `$type' is not translatable.");
          }
        }
      }
      else {
        return array('status' => 'error', 'error' => 'Language not supported.');
      }
    }
  }

  /**
   * Used to easily access dependent APIs.
   */
  function __get($name) {
    global $json_api, $sitepress;

    switch ($name) {
      case 'json':
        if (!isset($this->_json)) {
          $this->_json = $json_api;
        }
        return $this->_json;
      case 'sp':
        if (!isset($this->_sp)) {
          $this->_sp = $sitepress;
        }
        return $this->_sp;
    }
  }

  /**
   * Implements dispatch routing for callbacks.
   */
  function __call($method, $arguments) {
    $m = array();

    if (preg_match('/^dispatch_(\w+)_filter$/', $method, $m)) {
      return $this->dispatch_filter($m[1], $arguments);
    }
  }
}
