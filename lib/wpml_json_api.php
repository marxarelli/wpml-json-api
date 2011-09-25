<?php

require_once WPML_JSON_API_PATH.'/models/resource.php';
require_once WPML_JSON_API_PATH.'/models/translation.php';

class WPML_JSON_API {
  protected $plugin_path;

  /**
   * @var JSON_API
   */
  private $_json;

  /**
   * @var SitePress
   */
  private $_sp;

  /**
   * Constructs a new api for the specified plugin.
   */
  function __construct($plugin_path) {
    if (strpos($plugin_path, WP_PLUGIN_DIR) === 0) {
      $this->plugin_path = ltrim(substr($plugin_path, strlen(WP_PLUGIN_DIR)), DIRECTORY_SEPARATOR);
    }
    else {
      $this->plugin_path = $plugin_path;
    }
  }

  /**
   * Registers all filters and actions.
   */
  function register() {
    add_action('template_redirect', array($this, 'ensure_no_wpml_interference'), 5);

    add_filter('json_api_encode', array($this, 'dispatch_add_translations_filter'));
    add_filter('json_api_encode', array($this, 'dispatch_filter_for_language_filter'));
    add_filter('json_api_encode', array($this, 'dispatch_translate_resource_filter'));

    add_action('activate_'.$this->plugin_path, array($this, 'ensure_plugin_dependencies'));
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
      foreach (array_keys($response) as $type) {
        if (is_object($response[$type])) {
          $resources = array($response[$type]);
          $error = $this->$filter($type, $resources);
          break;
        }
        elseif (is_array($response[$type]) && isset($response[$type][0])) {
          if (is_object($response[$type][0])) {
            $error = $this->$filter($type, $response[$type]);
            break;
          }
        }
      }

      // If there was an error, overwrite the response with it.
      return is_null($error) ? $response : $error;
    }

    return $response;
  }

  /**
   * Remove WPML hooks when in a JSON context. JSON objects are requested 
   * explicitly and we don't want WPML interfering.
   */
  function ensure_no_wpml_interference() {
    if ($this->json->query->get('json')) {
      // Deregister WPML hooks that limit results to the detected language
      // NOTE This is some ugly stuff, but I couldn't figure out a better way 
      // to keep WPML from interfering.
      remove_action('pre_get_posts', array($this->sp, 'pre_get_posts'));
      remove_filter('get_pages', array($this->sp, 'exclude_other_language_pages2'));
      remove_filter('get_term', array($this->sp,'get_term_adjust_id'), 1, 1);
      remove_filter('get_terms', array($this->sp,'get_terms_filter'));
      remove_filter('posts_join', array($this->sp,'posts_join_filter'));
      remove_filter('posts_where', array($this->sp,'posts_where_filter'));
      remove_filter('terms_clauses', array($this->sp, 'terms_clauses'));
      remove_filter('query', array($this->sp, 'filter_queries'));
      remove_filter('getarchives_join', array($this->sp,'getarchives_join'));
      remove_filter('getarchives_where', array($this->sp,'getarchives_where'));
      remove_filter('get_previous_post_join', array($this->sp,'get_adjacent_post_join'));
      remove_filter('get_next_post_join', array($this->sp,'get_adjacent_post_join'));
      remove_filter('get_previous_post_where', array($this->sp,'get_adjacent_post_where'));
      remove_filter('get_next_post_where', array($this->sp,'get_adjacent_post_where'));
      remove_filter('get_term', array($this->sp,'get_term_adjust_id'), 1, 1);
      remove_filter('category_link', array($this->sp,'category_link_adjust_id'), 1, 2);
      remove_filter('get_terms', array($this->sp,'get_terms_adjust_ids'), 1, 3);
      remove_filter('get_pages', array($this->sp,'get_pages_adjust_ids'), 1, 2);
      remove_action('parse_query', array($this->sp, 'parse_query'));
      remove_action('wp_list_pages_excludes', array($this->sp, 'adjust_wp_list_pages_excludes'));
      remove_filter("pre_term_name", array($this->sp, 'pre_term_name'), 1, 2);
      remove_filter('the_category', array($this->sp,'the_category_name_filter'));
      remove_filter('get_terms', array($this->sp,'get_terms_filter'));
      remove_filter('get_the_terms', array($this->sp, 'get_the_terms_filter'), 10, 3);
    }
  }

  /**
   * Ensures that the plugin dependencies are fulfilled.
   */
  function ensure_plugin_dependencies($network_wide) {
    if (!is_plugin_active('json-api/json-api.php')) {
      trigger_error('This plugin requires JSON-API!', E_USER_ERROR);
    }

    if (!is_plugin_active('sitepress-multilingual-cms/sitepress.php')) {
      trigger_error('This plugin requires WPML Multilingual CMS!', E_USER_ERROR);
    }
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
   * Filters the response resources to only include those for the specified 
   * language.
   */
  function filter_for_language($type, &$resources) {
    $numeric_keys = false;
    $modified = false;

    foreach (array_keys($resources) as $k) {
      $numeric_keys = is_numeric($k);

      if (($language = $this->json->query->language) && isset($resources[$k]->language)) {
        if ($this->language_is_supported($language)) {
          if ($language != 'all' && $language != $resources[$k]->language) {
            unset($resources[$k]);
            $modified = true;
          }
        }
        else {
          return array('status' => 'error', 'error' => 'Language not supported.');
        }
      }
    }

    // If array has numeric keys and we deleted something, we have to reset 
    // the key order so it will be properly JSON encoded as an array.
    if ($modified && $numeric_keys) {
      $resources = array_values($resources);
    }
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
    if ($to_language = $this->json->query->to_language) {
      if ($this->language_is_supported($to_language)) {
        foreach ($resources as $resource) {
          if ($wpml_resource = WPML_JSON_API_Resource::create($type, $resource)) {
            $wpml_resource->translate_resource($resource, $to_language);
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

  private function find_plugin_in($plugins, $names) {
    return array_search(implode(DIRECTORY_SEPARATOR, (array) $names), array_keys($plugins));
  }
}
