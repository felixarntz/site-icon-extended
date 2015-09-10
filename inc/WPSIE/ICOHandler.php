<?php
/**
 * @package WPSIE
 * @version 0.1.0
 * @author Felix Arntz <felix-arntz@leaves-and-love.net>
 */

namespace WPSIE;

use PHP_ICO;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'WPSIE\ICOHandler' ) ) {
	/**
	 * This class is responsible for everything related to generating the .ico file used as the classic favicon.
	 *
	 * @since 0.1.0
	 */
	class ICOHandler {

		/**
		 * @since 0.1.0
		 * @var WPSIE\ICOHandler|null Holds the singleton instance of the class.
		 */
		private static $instance = null;

		/**
		 * Returns the singleton instance of the class.
		 *
		 * @since 0.1.0
		 * @return WPSIE\ICOHandler
		 */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * @since 0.1.0
		 * @var array Holds the icon sizes for the .ico file.
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
			add_action( 'delete_attachment', array( $this, 'maybe_delete_ico_file' ), 8, 1 );
			add_action( 'admin_init', array( $this, 'maybe_generate_ico_file_check' ), 10 );

			add_filter( 'wp_generate_attachment_metadata', array( $this, 'maybe_generate_ico_file_filter' ), 10, 2 );
		}

		/**
		 * Gets the URL to the site icon .ico file.
		 *
		 * @since 0.1.0
		 * @return string|false URL to the file or false if no .ico file is set
		 */
		public function get_ico_url() {
			$site_icon = get_option( 'site_icon' );
			if ( ! $site_icon ) {
				return false;
			}

			$ico_id = get_post_meta( $site_icon, 'wpsie_ico_id', true );
			if ( ! $ico_id ) {
				return false;
			}

			return wp_get_attachment_url( $ico_id );
		}

		/**
		 * Tries to generate an .ico file if a site icon is set, but no .ico file is set.
		 *
		 * This ensures that an .ico file is generated even when the `wp_generate_attachment_metadata` filter is not run.
		 *
		 * @since 0.1.0
		 */
		public function maybe_generate_ico_file_check() {
			if ( ! current_user_can( 'upload_files' ) ) {
				return;
			}

			$site_icon = get_option( 'site_icon' );
			if ( ! $site_icon ) {
				return;
			}

			$ico_id = get_post_meta( $site_icon, 'wpsie_ico_id', true );
			if ( $ico_id ) {
				return;
			}

			$transient = get_transient( 'wpsie_check_ico_file' );
			if ( $transient ) {
				return;
			}

			$status = $this->maybe_generate_ico_file( $site_icon );
			if ( ! $status ) {
				// if .ico file does not exist, try to generate it once a week
				set_transient( 'wpsie_check_ico_file', '1', WEEK_IN_SECONDS );
			}
		}

		/**
		 * Generates an .ico file whenever a site icon has just been generated.
		 *
		 * @since 0.1.0
		 * @param array $metadata attachment metadata (not modified at all)
		 * @param int $attachment_id the ID of the attachment that has just been generated
		 * @return array the unmodified metadata
		 */
		public function maybe_generate_ico_file_filter( $metadata, $attachment_id ) {
			$this->maybe_generate_ico_file( $attachment_id );

			return $metadata;
		}

		/**
		 * Generates an .ico file from a site icon attachment.
		 *
		 * The PHP_ICO class is used to convert the PNG images into the .ico file.
		 * The file is then stored as an additional attachment in WordPress.
		 * This attachment's ID is stored in an option to be able to retrieve it anytime.
		 *
		 * @since 0.1.0
		 * @param int $attachment_id the ID of the site icon attachment
		 */
		private function maybe_generate_ico_file( $attachment_id ) {
			$attachment_context = get_post_meta( $attachment_id, '_wp_attachment_context', true );

			if ( 'site-icon' !== $attachment_context ) {
				return false;
			}

			$attachment = get_post( $attachment_id );
			$src = get_attached_file( $attachment_id );

			if ( ! $src ) {
				return false;
			}

			$dst = explode( '.', $src );
			$dst[ count( $dst ) - 1 ] = 'ico';
			$dst = implode( '.', $dst );

			$ico_creator = new PHP_ICO();
			$has_image = false;

			foreach ( $this->sizes as $size ) {
				$intermediate = image_get_intermediate_size( $attachment_id, array( $size, $size ) );
				if ( is_array( $intermediate ) ) {
					$resized_file = str_replace( wp_basename( $src ), $intermediate['file'], $src );
					if ( $ico_creator->add_image( $resized_file, array( array( $size, $size ) ) ) ) {
						$has_image = true;
					}
				}
			}

			if ( ! $has_image ) {
				return false;
			}

			$status = $ico_creator->save_ico( $dst );

			if ( ! $status ) {
				return false;
			}

			$parent_url = $attachment->guid;
			$url = str_replace( basename( $parent_url ), basename( $dst ), $parent_url );

			$size = @getimagesize( $dst );
			$image_type = ( $size ) ? $size['mime'] : 'image/vnd.microsoft.icon';

			$object = array(
				'post_title'     => basename( $dst ),
				'post_content'   => $url,
				'post_mime_type' => $image_type,
				'guid'           => $url,
				'context'        => 'site-icon-ico-file',
			);

			$ico_id = wp_insert_attachment( $object, $dst );

			if ( ! $ico_id ) {
				return false;
			}

			$ico_metadata = wp_generate_attachment_metadata( $ico_id, $dst );

			$ico_metadata = apply_filters( 'wpsie_ico_attachment_metadata', $ico_metadata );

			wp_update_attachment_metadata( $ico_id, $ico_metadata );

			update_post_meta( $attachment_id, 'wpsie_ico_id', $ico_id );

			return true;
		}

		/**
		 * Deletes the site icon .ico file whenever the current site icon is deleted.
		 * If the .ico file itself is deleted, this function also deletes the related option.
		 *
		 * @since 0.1.0
		 * @param int $attachment_id ID of the attachment that is being deleted
		 */
		public function maybe_delete_ico_file( $attachment_id ) {
			$site_icon_id = get_option( 'site_icon' );
			$ico_id = get_post_meta( $site_icon, 'wpsie_ico_id', true );

			if ( $site_icon_id && $attachment_id == $site_icon_id ) {
				if ( $ico_id ) {
					wp_delete_attachment( $ico_id, true );
				}
			}
		}
	}
}
