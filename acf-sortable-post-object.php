<?php

/*
Plugin Name: Advanced Custom Fields: Sortable Post Object by DrJest
Plugin URI: https://github.com/drjest/acf-sortable-post-object
Description: A sortable post object field for ACF, powered by jQuery Sortable
Version: 1.0.0
Author: DrJest
Author URI: https://github.com/drjest
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if( ! defined( 'ABSPATH' ) ) exit;

if( !class_exists('dj_acf_plugin_sortable_post_object') ) :

class dj_acf_plugin_sortable_post_object {
  var $settings;

  function __construct() {
    $this->settings = array(
      'version' => '1.0.0',
      'url'   => plugin_dir_url( __FILE__ ),
      'path'    => plugin_dir_path( __FILE__ )
    );

    load_plugin_textdomain( 'acf-sortable-post-object', false, plugin_basename( dirname( __FILE__ ) ) . '/lang' ); 
    
    add_action('acf/include_field_types',   array($this, 'include_field_types')); // v5
    add_action('acf/register_fields',     array($this, 'include_field_types')); // v4
  }
  
  function include_field_types( $version = false ) {
    if( !$version ) $version = 4;
    require_once('fields/class-dj-acf-field-sortable-post-object-v' . $version . '.php');
  }
}

new dj_acf_plugin_sortable_post_object();

endif;
  
?>