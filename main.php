<?php
/*
Plugin Name: Multi-Column Taxonomy List Plus
Plugin URI: https://github.com/clas-web/multi-column-taxonomy-list
Description: 
Version: 2.1.1
Author: Crystal Barton
Author URI: http://www.crystalbarton.com
GitHub Plugin URI: https://github.com/clas-web/multi-column-taxonomy-list
*/


if( !defined('MCTL_PLUGIN_NAME') ):

/**
 * 
 * @var  string
 */
define( 'MCTL_PLUGIN_NAME', 'Multi-Column Taxonomy List' );

/**
 * 
 * @var  string
 */
define( 'MCTL_PLUGIN_VERSION', '1.0.0' );

/**
 * 
 * @var  string
 */
define( 'MCTL_PLUGIN_PATH', __DIR__ );

/**
 * 
 * @var  string
 */
define( 'MCTL_PLUGIN_URL', plugins_url( '', __FILE__ ) );

endif;


require_once( __DIR__.'/control.php' );
MultiColumnTaxonomyList_WidgetShortcodeControl::register_widget();
MultiColumnTaxonomyList_WidgetShortcodeControl::register_shortcode();


