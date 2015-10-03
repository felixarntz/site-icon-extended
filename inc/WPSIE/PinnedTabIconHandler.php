<?php
/**
 * @package WPSIE
 * @version 0.2.0
 * @author Felix Arntz <felix-arntz@leaves-and-love.net>
 */

namespace WPSIE;

use WP_Customize_Image_Control;
use WP_Customize_Color_Control;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'WPSIE\PinnedTabIconHandler' ) ) {
	/**
	 * This class is responsible for everything related to handling the Safari Pinned Tab Icon.
	 *
	 * @since 0.2.0
	 */
	class PinnedTabIconHandler {

		/**
		 * @since 0.2.0
		 * @var WPSIE\PinnedTabIconHandler|null Holds the singleton instance of the class.
		 */
		private static $instance = null;

		/**
		 * Returns the singleton instance of the class.
		 *
		 * @since 0.2.0
		 * @return WPSIE\PinnedTabIconHandler
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
		 * @since 0.2.0
		 */
		private function __construct() {
		}

		/**
		 * Adds actions and filters to integrate the class functionality into WordPress.
		 *
		 * @since 0.2.0
		 */
		public function add_hooks() {
			if ( ! defined( 'WPSIE_PREVENT_SVG_UPLOAD' ) || ! WPSIE_PREVENT_SVG_UPLOAD ) {
				add_action( 'customize_register', array( $this, 'customize_register_pinned_tab_icon' ), 99, 1 );
				add_filter( 'upload_mimes', array( $this, 'allow_svg_uploads' ) );
			}
		}

		/**
		 * Gets the Pinned Tab Icon URL.
		 *
		 * @since 0.2.0
		 * @return string|false the URL to the icon or false if not set
		 */
		public function get_svg_url() {
			return get_option( 'wpsie_pinned_tab_icon_url', false );
		}

		/**
		 * Gets the Pinned Tab Icon color setting.
		 *
		 * @since 0.2.0
		 * @return string a hexadecimal color string (black as default)
		 */
		public function get_color() {
			return get_option( 'wpsie_pinned_tab_icon_color', '#000000' );
		}

		/**
		 * Registers the Pinned Tab Icon settings in the Customizer. They are shown below the Site Icon setting.
		 *
		 * @since 0.2.0
		 * @param WP_Customize_Manager $wp_customize action argument passed by WordPress
		 */
		public function customize_register_pinned_tab_icon( $wp_customize ) {
			$site_icon_control = $wp_customize->get_control( 'site_icon' );
			if ( ! $site_icon_control ) {
				return;
			}

			$wp_customize->add_setting( 'wpsie_pinned_tab_icon_url', array(
				'type'			=> 'option',
				'capability'	=> 'manage_options',
				'transport'		=> 'postMessage',
			) );

			$wp_customize->add_setting( 'wpsie_pinned_tab_icon_color', array(
				'type'			=> 'option',
				'capability'	=> 'manage_options',
				'transport'		=> 'postMessage',
			) );

			$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'wpsie_pinned_tab_icon_url', array(
				'label'			=> __( 'Pinned Tab Icon', 'site-icon-extended' ),
				'description'	=> __( 'Upload a square SVG image with all vectors 100% black to use as Pinned Tab Icon for Safari.', 'site-icon-extended' ),
				'section'		=> $site_icon_control->section,
				'priority'		=> $site_icon_control->priority + 2,
			) ) );

			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'wpsie_pinned_tab_icon_color', array(
				'label'			=> __( 'Pinned Tab Icon Color', 'site-icon-extended' ),
				'description'	=> __( 'Set the color to be applied to the Pinned Tab Icon.', 'site-icon-extended' ),
				'section'		=> $site_icon_control->section,
				'priority'		=> $site_icon_control->priority + 3,
			) ) );
		}

		/**
		 * Filters the upload MIME types to allow uploading SVG files.
		 *
		 * @since 0.2.0
		 * @param array $mime_types the original MIME types
		 * @return array the filtered MIME types containing SVG
		 */
		public function allow_svg_uploads( $mime_types ) {
			$mime_types['svg'] = 'image/svg+xml';

			return $mime_types;
		}
	}
}
