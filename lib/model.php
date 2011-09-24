<?php

class WPML_JSON_API_Model {
  /**
   * SitePress client. Defined by the WPML Multilingual CMS plugin.
   * @var SitePress
   */
  private $_sp;

  /**
   * Returns the model class for the given type.
   */
  static function create($type) {
    $type = self::singularize($type);
    $class = self::classify($type);
    $class = "WPML_JSON_API_$class";

    if (file_exists(WPML_JSON_API_PATH."/models/$type.php")) {
      include_once WPML_JSON_API_PATH."/models/$type.php";
    }

    if (class_exists($class)) {
      return $class;
    }

    return null;
  }

  static protected function singularize($str) {
    $str = preg_replace('/ies$/', 'y', $str);
    $str = preg_replace('/([bcdefgklmnpqrtvw])s$/', '\1', $str);
    return $str;
  }

  static protected function classify($str) {
    $str = preg_replace_callback('/(?:^|_)([a-z])/', array(__CLASS__, 'uppercase_match'), $str);
    $str = ltrim($str, '_');
    return $str;
  }

  static private function uppercase_match($matches) {
    return strtoupper($matches[1]);
  }

  /**
   * Used to easily access dependent APIs.
   */
  function __get($name) {
    global $sitepress;

    switch ($name) {
      case 'sp':
        if (!isset($this->_sp)) {
          $this->_sp = $sitepress;
        }
        return $this->_sp;
    }
  }

  protected function get_post($id, $type) {
    if ($type == 'page') {
      return $this->wp_query_first('page_id=%d', $id);
    }
    else {
      return $this->wp_query_first('p=%d', $id);
    }
  }

  protected function wp_query() {
    return new WP_Query(call_user_func_array('sprintf', func_get_args()));
  }

  protected function wp_query_first() {
    $wp_query = call_user_func_array(array($this, 'wp_query'), func_get_args());

    if ($wp_query->have_posts()) {
      return $wp_query->next_post();
    }
  }
}
