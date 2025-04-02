<?php
/**
 * Plugin API
 * Handles endpoints for user interaction via the plugin admin screen
 *
 * @package ChoctawNation
 * @subpackage BiskinikContentFederation
 */

namespace ChoctawNation\BiskinikContentFederation;

use DateTime;
use Exception;
use stdClass;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Plugin API
 * Handles endpoints for user interaction via the plugin admin screen
 */
class Plugin_API {
	/**
	 * The posts to federate
	 *
	 * @var array $posts_to_federate
	 */
	public array $posts_to_federate;

	/**
	 * The taxonomy ID
	 *
	 * @var string $tax_id
	 */
	private string $tax_id;

	/**
	 * The endpoint base for the API
	 *
	 * @var string $endpoint_base
	 */
	private string $endpoint_base;

	/**
	 * The version of the API
	 *
	 * @var string $version
	 */
	private string $version;

	/**
	 * The controller for the plugin
	 *
	 * @var Content_API $content_api
	 */
	private Content_API $content_api;

	/**
	 * Constructor
	 *
	 * @param string $tax_id The Taxonomy id
	 */
	public function __construct( string $tax_id ) {
		$this->tax_id            = $tax_id;
		$this->content_api       = new Content_API();
		$this->posts_to_federate = array(
			array(
				'title'    => "Chief's Blog",
				'taxonomy' => 'news_categories',
			),
			array(
				'title'    => 'Iti Fabvssa',
				'taxonomy' => 'posts_categories',
			),
		);
		$this->endpoint_base     = 'cno-federated-content';
		$this->version           = '1';
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Registers the routes for the API
	 */
	public function register_routes() {
		register_rest_route(
			"{$this->endpoint_base}/v{$this->version}",
			'/generate-terms',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'generate_terms' ),
				'permission_callback' => fn() => current_user_can( 'manage_options' ),
			)
		);
		register_rest_route(
			"{$this->endpoint_base}/v{$this->version}",
			'/fetch-posts',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'fetch_latest_posts' ),
				'permission_callback' => fn() => current_user_can( 'edit_posts' ),
			)
		);
		register_rest_route(
			"{$this->endpoint_base}/v{$this->version}",
			'/get-next-fetch',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_next_fetch' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Generates the Terms for the Custom Taxonomy
	 */
	public function generate_terms(): WP_REST_Response {
		// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_error_log
		$taxonomy_exists = taxonomy_exists( $this->tax_id );
		if ( ! $taxonomy_exists ) {
			return new WP_REST_Response(
				array(
					'status'  => 'error',
					'message' => 'Taxonomy does not exist!',
				),
				500
			);
		}
		foreach ( $this->posts_to_federate as $new_term ) {
			if ( term_exists( sanitize_title( $new_term['title'] ), $this->tax_id ) ) {
				continue;
			}
			$term_id = wp_insert_term( $new_term['title'], $this->tax_id );
			if ( is_wp_error( $term_id ) ) {
				error_log( "Failed to insert term {$new_term['title']}: " . $term_id->get_error_message() );
				continue;
			}
			$cno_term_id = $this->content_api->fetch_term_id( $new_term['taxonomy'], $new_term['title'] );
			if ( $cno_term_id ) {
				update_field( 'taxonomy_id', $cno_term_id, "{$this->tax_id}_{$term_id['term_id']}" );
			} else {
				error_log( "Failed to fetch ID for {$new_term['title']}" );
			}
		}
		return rest_ensure_response( array( 'status' => 'success' ) );
		// phpcs:enable WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}

	/**
	 * Fetches the latest posts from the API
	 *
	 * @param ?WP_REST_Request $request The REST request object
	 */
	public function fetch_latest_posts( ?WP_REST_Request $request = null ) {
		$term_ids = array();
		if ( $request ) {
			if ( ! $request->get_json_params() ) {
				// try again and pass null
				return $this->fetch_latest_posts();
			}
			$json_params = json_decode( $request->get_json_params() );
			$slug        = esc_textarea( $json_params->slug );
			foreach ( $this->posts_to_federate as $term ) {
				if ( sanitize_title( $term['title'] ) === $slug ) {
					$term_ids[] = array(
						'term'     => get_term_by( 'name', $term['title'], $this->tax_id ),
						'taxonomy' => $term['taxonomy'],
					);
				}
			}
		} else {
			foreach ( $this->posts_to_federate as $term ) {
				$term_ids[] = array(
					'term'     => get_term_by( 'name', $term['title'], $this->tax_id ),
					'taxonomy' => $term['taxonomy'],
				);
			}
		}
		foreach ( $term_ids as $term ) {
			$latest_post   = $this->fetch_latest_post( $term );
			$existing_post = $this->post_exists( $latest_post );
			if ( $existing_post ) {
				wp_update_post(
					array(
						'ID'         => $existing_post->ID,
						'post_title' => $latest_post->title->rendered,
						'post_date'  => $latest_post->date,
					)
				);
				return rest_ensure_response(
					array(
						'status'  => 'success',
						'message' => 'Updated the “' . get_the_title( $existing_post ) . '” post',
					)
				);
			}
			$post_id = wp_insert_post(
				array(
					'post_title'   => $latest_post->title->rendered,
					'post_content' => '',
					'post_date'    => $latest_post->date,
					'post_status'  => 'publish',
					'post_type'    => 'post',
					'meta_input'   => array(
						'cno_post_id' => $latest_post->id,
					),

				)
			);
			if ( is_wp_error( $post_id ) ) {
				return new WP_REST_Response(
					array(
						'status'  => 'error',
						'message' => "Failed to create post for {$term['term']->name}: " . $post_id->get_error_message(),
					),
					500
				);
			}
			wp_set_object_terms( $post_id, array( $term['term']->term_id ), $this->tax_id );
		}
		$this->schedule_cron_event();
		return rest_ensure_response(
			array(
				'status'  => 'success',
				'message' => 'Posts created',
			)
		);
	}

	/**
	 * Gets the next fetch time
	 */
	public function get_next_fetch(): WP_REST_Response {
		$next_fetch = wp_next_scheduled( 'cno_fetch_latest_cno_posts' );
		if ( $next_fetch ) {
			$next_fetch = DateTime::createFromFormat( 'U', $next_fetch, wp_timezone() );
			return rest_ensure_response(
				array(
					'status'  => 'success',
					'message' => 'Next fetch scheduled for ' . $next_fetch->format( 'g:i:s a F j, Y' ),
				)
			);
		}
		return rest_ensure_response(
			array(
				'status'  => 'error',
				'message' => 'No fetch scheduled',
			)
		);
	}

	/**
	 * Schedules the cron event to fetch the latest posts
	 */
	private function schedule_cron_event() {
		if ( ! wp_next_scheduled( 'cno_fetch_latest_cno_posts' ) ) {
			wp_schedule_event( time(), 'daily', 'cno_fetch_latest_cno_posts' );
		}
		add_action( 'cno_fetch_latest_cno_posts', array( $this->content_api, 'fetch_latest_posts' ) );
	}

	/**
	 * Fetches the latest post for a given term
	 *
	 * @param array $term The term to fetch the latest post for
	 * @return stdClass The latest post as a stdClass
	 * @throws Exception If the CNO term ID cannot be fetched.
	 * @throws Exception If the latest post cannot be fetched.
	 * @throws Exception If no posts are found.
	 */
	private function fetch_latest_post( array $term ): stdClass {
		// phpcs:disable WordPress.Security.EscapeOutput.ExceptionNotEscaped
		$cno_term_id = get_field( 'taxonomy_id', "{$this->tax_id}_{$term['term']->term_id}" );
		if ( ! $cno_term_id ) {
			throw new Exception( "Failed to fetch CNO term ID for {$term['term']->name}", 500 );
		}
		$latest_post = $this->content_api->fetch_latest_post( $cno_term_id, $term['taxonomy'] );
		if ( is_wp_error( $latest_post ) ) {
			throw new Exception( "Failed to fetch latest post for {$term['term']->name}: " . $latest_post->get_error_message(), 500 );
		}
		if ( empty( $latest_post ) ) {
			throw new Exception( "No posts found for {$term['term']->name}", 500 );
		}
		return $latest_post[0];
		// phpcs:enable WordPress.Security.EscapeOutput.ExceptionNotEscaped
	}

	/**
	 * Checks if a post already exists
	 *
	 * @param stdClass $latest_post The latest post to check
	 * @return WP_Post|false The existing post if found, false otherwise
	 */
	private function post_exists( stdClass $latest_post ): WP_Post|false {
		$federated_posts = $this->get_existing_federated_posts();
		if ( ! $federated_posts ) {
			return false;
		}
		foreach ( $federated_posts as $post ) {
			$cno_id = (int) get_post_meta( $post->ID, 'cno_post_id', true );
			if ( $cno_id === $latest_post->id ) {
				return $post;
			}
		}
		return false;
	}


	/**
	 * Gets the existing federated posts
	 *
	 * @return ?\WP_Post[] The existing federated posts
	 */
	private function get_existing_federated_posts(): ?array {
		$transient_id = 'cno_federated_posts';
		$cached_posts = get_transient( $transient_id );
		if ( $cached_posts ) {
			return $cached_posts;
		}
		$federated_posts = get_posts(
			array(
				'post_type' => 'post',
				'taxonomy'  => $this->tax_id,
				'order'     => 'DESC',
				'orderby'   => 'date',
			)
		);
		set_transient( $transient_id, $federated_posts, 12 * HOUR_IN_SECONDS );
		return empty( $federated_posts ) ? null : $federated_posts;
	}
}
