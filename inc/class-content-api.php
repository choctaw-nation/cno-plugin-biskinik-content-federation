<?php
/**
 * Content API
 * Handles content federation between the Biskinik and Nation sites
 *
 * @package ChoctawNation
 * @subpackage BiskinikContentFederation
 */

namespace ChoctawNation\BiskinikContentFederation;

use Exception;
use WP_Error;

/**
 * Content API
 * Handles content federation between the Biskinik and Nation sites
 */
class Content_API {
	/**
	 * The API key for the Nation site
	 *
	 * @var string $api_key
	 */
	private string|false $api_key;

	/**
	 * The remote path for the API to make fetches to
	 *
	 * @var string $remote_path
	 */
	private string $remote_path;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->api_key     = get_option( 'cno_biskinik_federated_content' );
		$this->remote_path = 'https://www.choctawnation.com/wp-json/wp/v2';
	}

	/**
	 * Checks the API key to see if it exists
	 */
	private function api_key_exists(): bool {
		return is_string( $this->api_key ) && ! empty( $this->api_key );
	}

	/**
	 * Returns the headers for the API request
	 *
	 * @param array $additional_params Additional parameters to add to the header
	 * @return array The auth header
	 * @throws Exception If the API key is not set.
	 */
	private function get_auth_header( array $additional_params = array() ): array {
		if ( ! $this->api_key_exists() ) {
			throw new Exception( 'API key not set' );
		}
		return array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( "cnodigital:{$this->api_key}" ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			),
			...$additional_params,
		);
	}

	/**
	 * Grabs the ID of the term from the Nation site
	 *
	 * @param string $taxonomy The taxonomy to search
	 * @param string $title    The title of the term to search for
	 * @return int|false The ID of the term, or false on failure
	 */
	public function fetch_term_id( $taxonomy, $title ): int|false {
		try {
			$response = wp_remote_get(
				"{$this->remote_path}/{$taxonomy}/?search=" . sanitize_title( $title ) . '&_fields=id',
				array( $this->get_auth_header() )
			);
			if ( is_wp_error( $response ) ) {
				return false;
			}
			$body = wp_remote_retrieve_body( $response );
			if ( is_wp_error( $body ) ) {
				return false;
			}
			$body = json_decode( $body );
			return $body[0]->id ?? false;
		} catch ( Exception $e ) {
			error_log( 'Error fetching term ID: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return false;
		}
	}

	/**
	 * Grabs the latest post from the Nation site
	 *
	 * @param string|int $term_id The term ID to search for
	 * @param string     $taxonomy The taxonomy to search
	 * @return array|\WP_Error The latest post, or false on failure
	 */
	public function fetch_latest_post( string|int $term_id, string $taxonomy ) {
		$response = wp_remote_get( "{$this->remote_path}/news?{$taxonomy}={$term_id}&order=desc&orderby=date&_fields=id,title,link,status,date,_links.wp:featuredmedia,_embedded&per_page=1&_embed=wp:featuredmedia" );
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$body = wp_remote_retrieve_body( $response );
		if ( is_wp_error( $body ) ) {
			return $body;
		}
		return json_decode( $body );
	}

	/**
	 * Uploads the featured image to the media library
	 *
	 * @param string $image_url The URL of the image to upload
	 * @param int    $post_id  The ID of the post to attach the image to
	 * @return int|WP_Error The ID of the attachment, or a WP_Error object on failure
	 */
	public function upload_featured_image( string $image_url, int $post_id ): int|WP_Error {
		// Include required WordPress files for media handling
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		// Fetch the image and upload to media library
		$tmp_file = download_url( $image_url );

		if ( is_wp_error( $tmp_file ) ) {
			return $tmp_file;
		}

		// Get the file name and type
		$file = array(
			'name'     => basename( $image_url ), // Extract file name
			'type'     => mime_content_type( $tmp_file ), // Get MIME type
			'tmp_name' => $tmp_file, // Temporary file
			'error'    => 0,
			'size'     => filesize( $tmp_file ),
		);

		// Upload the file to the media library
		$attachment_id = media_handle_sideload( $file, $post_id );

		if ( is_wp_error( $attachment_id ) ) {
			wp_delete_file( $tmp_file );
		}
		wp_generate_attachment_metadata( $attachment_id, $tmp_file );
		return $attachment_id;
	}
}
