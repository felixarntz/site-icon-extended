<?php
/**
 * @package WPSIE
 * @version 0.2.3
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
			add_action( 'admin_init', array( $this, 'maybe_generate_ico_attachment_check' ), 10 );

			add_filter( 'wp_generate_attachment_metadata', array( $this, 'maybe_generate_ico_attachment_filter' ), 10, 2 );
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
		public function maybe_generate_ico_attachment_check() {
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

			$status = $this->maybe_generate_ico_attachment( $site_icon );
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
		public function maybe_generate_ico_attachment_filter( $metadata, $attachment_id ) {
			$this->maybe_generate_ico_attachment( $attachment_id );

			return $metadata;
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

		/**
		 * Generates an .ico attachment for another existing attachment if that attachment is a site icon.
		 *
		 * This attachment's ID is stored as a meta value in the original attachment to be able to retrieve it anytime.
		 *
		 * @since 0.1.0
		 * @param int $attachment_id the ID of the site icon attachment
		 * @return bool true if successful, false otherwise
		 */
		private function maybe_generate_ico_attachment( $attachment_id ) {
			$attachment_context = get_post_meta( $attachment_id, '_wp_attachment_context', true );

			if ( 'site-icon' !== $attachment_context ) {
				return false;
			}

			$attachment = get_post( $attachment_id );
			$src = get_attached_file( $attachment_id );

			if ( ! $src ) {
				return false;
			}

			$dst = $this->generate_ico_file( $attachment_id, $src );

			if ( ! $dst ) {
				return false;
			}

			$parent_url = $attachment->guid;
			$url = str_replace( basename( $parent_url ), basename( $dst ), $parent_url );

			$ico_id = $this->insert_ico_attachment( $dst, $url );

			if ( ! $ico_id ) {
				return false;
			}

			update_post_meta( $attachment_id, 'wpsie_ico_id', $ico_id );

			return true;
		}

		/**
		 * Generates an .ico file from an existing attachment.
		 *
		 * The PHP_ICO class is used to convert the different image sizes into the .ico file.
		 *
		 * @since 0.1.0
		 * @param int $attachment_id the ID of the site icon attachment
		 * @param string $source_file path to the site icon attachment file
		 * @return string|false path to the new file or false if an error occurred
		 */
		private function generate_ico_file( $attachment_id, $source_file ) {
			$destination_file = explode( '.', $source_file );
			$destination_file[ count( $destination_file ) - 1 ] = 'ico';
			$destination_file = implode( '.', $destination_file );

			$ico_creator = new PHP_ICO();
			$has_image = false;

			foreach ( $this->sizes as $size ) {
				$intermediate = image_get_intermediate_size( $attachment_id, array( $size, $size ) );
				if ( is_array( $intermediate ) ) {
					$resized_file = str_replace( wp_basename( $source_file ), $intermediate['file'], $source_file );
					if ( $ico_creator->add_image( $resized_file, array( array( $size, $size ) ) ) ) {
						$has_image = true;
					}
				}
			}

			if ( ! $has_image ) {
				return false;
			}

			$status = $ico_creator->save_ico( $destination_file );
			if ( ! $status ) {
				return false;
			}

			return $destination_file;
		}

		/**
		 * Inserts an attachment for an .ico file.
		 *
		 * @param string $file path to the .ico file
		 * @param string $url URL to the .ico file
		 * @return int|false the attachment ID or false if an error occurred
		 */
		private function insert_ico_attachment( $file, $url ) {
			$size = @getimagesize( $file );
			$image_type = ( $size ) ? $size['mime'] : 'image/vnd.microsoft.icon';

			$object = array(
				'post_title'     => basename( $file ),
				'post_content'   => $url,
				'post_mime_type' => $image_type,
				'guid'           => $url,
				'context'        => 'site-icon-ico-file',
			);

			$ico_id = wp_insert_attachment( $object, $file );

			if ( ! $ico_id ) {
				return false;
			}

			$ico_metadata = wp_generate_attachment_metadata( $ico_id, $file );

			$ico_metadata = apply_filters( 'wpsie_ico_attachment_metadata', $ico_metadata );

			wp_update_attachment_metadata( $ico_id, $ico_metadata );

			return $ico_id;
		}
	}
}
