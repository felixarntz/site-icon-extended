<?php
/*
Plugin Name: Site Icon Extended
Plugin URI: http://wordpress.org/plugins/site-icon-extended/
Description: This plugin enhances the WordPress Site Icon feature by adding more icon formats for improved cross-browser compatibility.
Version: 0.2.3
Author: Felix Arntz
Author URI: http://leaves-and-love.net
License: GNU General Public License v3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: site-icon-extended
Domain Path: /languages/
Tags: wordpress, plugin, site-icon, icon, favicon, browser, compatibility, browserconfig, ico, apple-touch-icon, pinned-tab-icon
*/
/**
 * @package WPSIE
 * @version 0.2.3
 * @author Felix Arntz <felix-arntz@leaves-and-love.net>
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'WPSIE\App' ) && file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	if ( version_compare( phpversion(), '5.3.0' ) >= 0 ) {
		require_once dirname( __FILE__ ) . '/vendor/autoload.php';
	} else {
		require_once dirname( __FILE__ ) . '/vendor/felixarntz/leavesandlove-wp-plugin-util/leavesandlove-wp-plugin-loader.php';
	}
}

LaL_WP_Plugin_Loader::load_plugin( array(
	'slug'				=> 'site-icon-extended',
	'name'				=> 'Site Icon Extended',
	'version'			=> '0.2.3',
	'main_file'			=> __FILE__,
	'namespace'			=> 'WPSIE',
	'textdomain'		=> 'site-icon-extended',
), array(
	'phpversion'		=> '5.3.0',
	'wpversion'			=> '4.3',
) );
