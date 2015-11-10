<?php
/**
 * @package WPSIE
 * @version 0.2.1
 * @author Felix Arntz <felix-arntz@leaves-and-love.net>
 */

namespace WPSIE;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'WPSIE\XMLHandler' ) ) {
	/**
	 * This class is responsible for everything related to generating the browserconfig.xml file.
	 *
	 * @since 0.1.0
	 */
	class XMLHandler {

		/**
		 * @since 0.1.0
		 * @var WPSIE\XMLHandler|null Holds the singleton instance of the class.
		 */
		private static $instance = null;

		/**
		 * Returns the singleton instance of the class.
		 *
		 * @since 0.1.0
		 * @return WPSIE\XMLHandler
		 */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * @since 0.1.0
		 * @var array Holds the icon sizes for the browserconfig.xml file.
		 */
		private $sizes = array();

		/**
		 * Class constructor.
		 *
		 * @since 0.1.0
		 */
		private function __construct() {
		}

		/**
		 * Sets the icon sizes.
		 *
		 * @since 0.1.0
		 * @param array $sizes an array of integers for the icon sizes
		 */
		public function set_sizes( $sizes ) {
			$this->sizes = $sizes;
		}

		/**
		 * Adds actions and filters to integrate the class functionality into WordPress.
		 *
		 * @since 0.1.0
		 */
		public function add_hooks() {
			add_action( 'after_setup_theme', array( $this, 'reduce_query_load' ), 99 );
			add_action( 'init', array( $this, 'add_query_var' ), 1 );
			add_action( 'init', array( $this, 'add_rewrite_rule' ), 1 );
			add_action( 'pre_get_posts', array( $this, 'maybe_show_browserconfig' ), 1, 1 );

			add_filter( 'redirect_canonical', array( $this, 'fix_canonical' ), 10, 1 );
		}

		/**
		 * Gets the URL to the browserconfig.xml file.
		 *
		 * @since 0.1.0
		 * @return string|false URL to the file or false if no icon is set
		 */
		public function get_browserconfig_url() {
			global $wp_rewrite;

			if ( ! has_site_icon() ) {
				return false;
			}

			$base = $wp_rewrite->using_index_permalinks() ? 'index.php/' : '/';

			return home_url( $base . 'browserconfig.xml' );
		}

		/**
		 * Reduces query load on a browserconfig.xml request by removing unnecessary actions.
		 *
		 * This function was basically taken from the Yoast SEO plugin.
		 *
		 * @since 0.1.0
		 */
		public function reduce_query_load() {
			if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
				return;
			}

			$extension = substr( $_SERVER['REQUEST_URI'], -4 );

			if ( false !== stripos( $_SERVER['REQUEST_URI'], 'browserconfig' ) && '.xml' === $extension ) {
				remove_all_actions( 'widgets_init' );
			}
		}

		/**
		 * Adds a query var to check for the browserconfig.xml file.
		 *
		 * @since 0.1.0
		 */
		public function add_query_var() {
			global $wp;

			if ( ! is_object( $wp ) ) {
				return;
			}

			$wp->add_query_var( 'wpsie_browserconfig' );
		}

		/**
		 * Adds a rewrite rule for the browserconfig.xml file.
		 *
		 * @since 0.1.0
		 */
		public function add_rewrite_rule() {
			add_rewrite_rule( 'browserconfig\.xml$', 'index.php?wpsie_browserconfig=1', 'top' );
		}

		/**
		 * Fixes the canonical redirect for the browserconfig.xml file by preventing a trailing slash.
		 *
		 * @since 0.1.0
		 * @param mixed $redirect argument passed by WordPress
		 * @return mixed returns false on a browserconfig.xml request
		 */
		public function fix_canonical( $redirect ) {
			$browserconfig = get_query_var( 'wpsie_browserconfig' );
			if ( empty( $browserconfig ) ) {
				return $redirect;
			}

			return false;
		}

		/**
		 * Shows the browserconfig.xml file if the query var is set.
		 *
		 * @since 0.1.0
		 * @param WP_Query $query the query object to check
		 */
		public function maybe_show_browserconfig( $query ) {
			if ( ! $query->is_main_query() ) {
				return;
			}

			$browserconfig = get_query_var( 'wpsie_browserconfig' );
			if ( empty( $browserconfig ) ) {
				return;
			}

			$this->show_browserconfig();
		}

		/**
		 * Sets the necessary headers and shows the browserconfig.xml file.
		 *
		 * @since 0.1.0
		 */
		private function show_browserconfig() {
			if ( ! headers_sent() ) {
				header( ( ( isset( $_SERVER['SERVER_PROTOCOL'] ) && $_SERVER['SERVER_PROTOCOL'] !== '' ) ? sanitize_text_field( $_SERVER['SERVER_PROTOCOL'] ) : 'HTTP/1.1' ) . ' 200 OK', true, 200 );
				header( 'X-Robots-Tag: noindex, follow', true );
				header( 'Content-Type: text/xml' );
				header( 'Content-Disposition: inline; filename="browserconfig.xml"' );
			}

			echo '<?xml version="1.0" encoding="' . get_bloginfo( 'charset' ) . '"?>' . "\n";
			echo $this->get_browserconfig();

			remove_all_actions( 'wp_footer' );
			die();
		}

		/**
		 * Gets the content of the browserconfig.xml file.
		 *
		 * @since 0.1.0
		 * @return string the content as valid XML
		 */
		private function get_browserconfig() {
			$content = '<browserconfig>' . "\n";
			$content .= '<msapplication>' . "\n";
			$content .= '<tile>' . "\n";

			if ( has_site_icon() ) {
				foreach ( $this->sizes as $size ) {
					$content .= sprintf( '<square%1$slogo src="%2$s"/>', sprintf( '%1$dx%1$d', $size ), esc_url( get_site_icon_url( $size ) ) ) . "\n";
				}
			}

			$background_color = BackgroundHandler::instance()->get_background_color();
			if ( $background_color ) {
				$content .= sprintf( '<TileColor>%s</TileColor>', esc_html( '#' . ltrim( $background_color, '#' ) ) ) . "\n";
			}

			$content .= '</tile>' . "\n";
			$content .= '</msapplication>' . "\n";
			$content .= '</browserconfig>' . "\n";

			return $content;
		}
	}
}
