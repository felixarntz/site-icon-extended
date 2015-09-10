<?php
/*
Plugin Name: Site Icon Extended
Plugin URI: http://wordpress.org/plugins/site-icon-extended/
Description: This plugin enhances the WordPress Site Icon feature by adding more icon formats for improved cross-browser compatibility.
Version: 0.1.0
Author: Felix Arntz
Author URI: http://leaves-and-love.net
License: GNU General Public License v2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wpsie
Domain Path: /languages/
Tags: wordpress, plugin, site-icon, icon, favicon, browser, compatibility
*/
/**
 * @package WPSIE
 * @version 0.1.0
 * @author Felix Arntz <felix-arntz@leaves-and-love.net>
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'WPSIE\App' ) && file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

\LaL_WP_Plugin_Loader::load_plugin( array(
	'slug'				=> 'site-icon-extended',
	'name'				=> 'Site Icon Extended',
	'version'			=> '0.1.0',
	'main_file'			=> __FILE__,
	'namespace'			=> 'WPSIE',
	'textdomain'		=> 'wpsie',
), array(
	'phpversion'		=> '5.3.0',
	'wpversion'			=> '4.3',
) );
