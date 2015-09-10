<?php
/**
 * @package WPSIE
 * @version 0.1.0
 * @author Felix Arntz <felix-arntz@leaves-and-love.net>
 */

namespace WPSIE;

use WP_Customize_Color_Control;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'WPSIE\BackgroundHandler' ) ) {
	/**
	 * This class is responsible for everything managing the icon background setting.
	 *
	 * @since 0.1.0
	 */
	class BackgroundHandler {

		/**
		 * @since 0.1.0
		 * @var WPSIE\BackgroundHandler|null Holds the singleton instance of the class.
		 */
		private static $instance = null;

		/**
		 * Returns the singleton instance of the class.
		 *
		 * @since 0.1.0
		 * @return WPSIE\BackgroundHandler
		 */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Class constructor.
		 *
		 * @since 0.1.0
		 */
		private function __construct() {
		}

		/**
		 * Adds actions and filters to integrate the class functionality into WordPress.
		 *
		 * @since 0.1.0
		 */
		public function add_hooks() {
			add_action( 'customize_register', array( $this, 'customize_register_background' ), 99, 1 );
		}

		/**
		 * Gets the site icon background color setting.
		 *
		 * @since 0.1.0
		 * @return string a hexadecimal color string or empty if not set
		 */
		public function get_background_color() {
			return get_option( 'wpsie_background_color', '' );
		}

		/**
		 * Registers the icon background setting in the Customizer. It is shown right below the Site Icon setting.
		 *
		 * @since 0.1.0
		 * @param WP_Customize_Manager $wp_customize action argument passed by WordPress
		 */
		public function customize_register_background( $wp_customize ) {
			$site_icon_control = $wp_customize->get_control( 'site_icon' );
			if ( ! $site_icon_control ) {
				return;
			}

			$wp_customize->add_setting( 'wpsie_background_color', array(
				'type'			=> 'option',
				'capability'	=> 'manage_options',
				'transport'		=> 'postMessage',
			) );

			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'wpsie_background_color', array(
				'label'			=> __( 'Site Icon Background Color', 'wpsie' ),
				'description'	=> __( 'The background color for the site icon is used across several Microsoft devices.', 'wpsie' ),
				'section'		=> $site_icon_control->section,
				'priority'		=> $site_icon_control->priority + 1,
			) ) );
		}
	}
}
