<?php 
/*
Plugin Name: WPML JSON-API
Description: Extends the JSON-API plugin within WPML multilingual functionality.
Author: Daniel Duvall
Author URI: http://mutual.io
Version: 0.0.1
*/

if (defined('WPML_JSON_API_VERSION')) return;

define('WPML_JSON_API_VERSION', '0.0.1');
define('WPML_JSON_API_PATH', dirname(__FILE__));

require_once WPML_JSON_API_PATH.'/lib/wpml_json_api.php';

global $wpml_json_api;
$wpml_json_api = new WPML_JSON_API();
$wpml_json_api->register();
