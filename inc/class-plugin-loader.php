<?php
/**
 * Plugin Loader
 *
 * @since 1.0
 * @package ChoctawNation
 * @subpackage BiskinikContentFederation
 */

namespace ChoctawNation\BiskinikContentFederation;

/** Inits the Plugin */
class Plugin_Loader {
	/**
	 * The plugin path
	 *
	 * @var string
	 */
	private string $plugin_path;

	/**
	 * The taxonomy handler
	 *
	 * @var Taxonomy_Handler $tax_handler
	 */
	private Taxonomy_Handler $tax_handler;

	/**
	 * The API handler
	 *
	 * @var API $api
	 */
	private API $api;

	/**
	 * The options page handler
	 *
	 * @var Options_Page $options_page
	 */
	private Options_Page $options_page;

	/**
	 * The posts to federate
	 *
	 * @var array $posts_to_federate
	 */
	private array $posts_to_federate;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->plugin_path       = plugin_dir_path( __FILE__ );
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
		$this->load_files();
		add_action( 'admin_init', array( $this, 'activation_redirection' ) );
		// $this->generate_terms();
	}

	/**
	 * Loads the Plugin Files
	 */
	private function load_files(): void {
		$files = array( 'taxonomy-handler', 'api', 'options-page' );
		foreach ( $files as $file ) {
			require_once "{$this->plugin_path}class-{$file}.php";
		}
		$this->tax_handler  = new Taxonomy_Handler();
		$this->api          = new API();
		$this->options_page = new Options_Page();
		$this->options_page->register();
	}

	/**
	 * Initializes the Plugin
	 */
	public function activate(): void {
		add_option( 'cno_biskinik_federated_content_activation_redirect', true );
		$this->tax_handler->register_taxonomy();
		$this->tax_handler->register_acf_field();
		flush_rewrite_rules();
		// $this->generate_terms();
		// $this->fetch_latest_posts();
		// $this->schedule_cron_event();
	}

	/**
	 * Generates the Terms for the Custom Taxonomy
	 */
	public function generate_terms() {
		// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_error_log
		$taxonomy_exists = taxonomy_exists( $this->tax_handler->tax_id );
		if ( ! $taxonomy_exists ) {
			return;
		}
		foreach ( $this->posts_to_federate as $new_term ) {
			if ( term_exists( sanitize_title( $new_term['title'] ), $this->tax_handler->tax_id ) ) {
				continue;
			}
			$term_id = wp_insert_term( $new_term['title'], $this->tax_handler->tax_id );
			if ( is_wp_error( $term_id ) ) {
				error_log( "Failed to insert term {$new_term['title']}: " . $term_id->get_error_message() );
				continue;
			}
			$cno_term_id = $this->api->fetch_term_id( $new_term['taxonomy'], $new_term['title'] );
			if ( $cno_term_id ) {
				update_field( 'taxonomy_id', $cno_term_id, "{$this->tax_handler->tax_id}_{$term_id['term_id']}" );
			} else {
				error_log( "Failed to fetch ID for {$new_term['title']}" );
			}
		}
		// phpcs:enable WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}

	/**
	 * Fetches the latest posts from the API
	 */
	public function fetch_latest_posts() {
		$term_ids = array();
		foreach ( $this->posts_to_federate as $term ) {
			$term_ids[] = array(
				'term'     => get_term_by( 'name', $term['title'], $this->tax_handler->tax_id ),
				'taxonomy' => $term['taxonomy'],
			);
		}
		foreach ( $term_ids as $term ) {
			$cno_term_id = get_field( 'taxonomy_id', "{$this->tax_handler->tax_id}_{$term['term']->term_id}" );
			if ( ! $cno_term_id ) {
				error_log( "No CNO term ID found for {$term['term']->name}" );
				continue;
			}
			$latest_post = $this->api->fetch_latest_post( $cno_term_id, $term['taxonomy'] );
			if ( is_wp_error( $latest_post ) ) {
				error_log( "Failed to fetch latest post for {$term['term']->name}: " . $latest_post->get_error_message() );
				continue;
			}
			if ( empty( $latest_post ) ) {
				error_log( "No posts found for {$term['term']->name}" );
				continue;
			}
			$post_id = wp_insert_post(
				array(
					'post_title'   => $latest_post[0]->title->rendered,
					'post_content' => '',
					'post_date'    => $latest_post[0]->date,
					'post_status'  => 'publish',
					'post_type'    => 'post',
				)
			);
			if ( is_wp_error( $post_id ) ) {
				error_log( "Failed to insert post for {$term['term']->name}: " . $post_id->get_error_message() );
				continue;
			}
			wp_set_object_terms( $post_id, array( $term['term']->term_id ), $this->tax_handler->tax_id );
		}
	}

	/**
	 * Schedules the cron event to fetch the latest posts
	 */
	private function schedule_cron_event() {
		if ( ! wp_next_scheduled( 'cno_fetch_latest_cno_posts' ) ) {
			wp_schedule_event( time(), 'daily', 'cno_fetch_latest_cno_posts' );
		}
		add_action( 'cno_fetch_latest_cno_posts', array( $this, 'fetch_latest_posts' ) );
	}

	public function activation_redirection() {
		if ( get_option( 'cno_biskinik_federated_content_activation_redirect', false ) ) {
			delete_option( 'cno_biskinik_federated_content_activation_redirect' );
			wp_safe_redirect( admin_url( 'options-general.php?page=federated-content' ) );
			exit;
		}
	}

	/**
	 * Handles Plugin Deactivation
	 * (this is a callback function for the `register_deactivation_hook` function)
	 *
	 * @return void
	 */
	public function deactivate(): void {
		unregister_taxonomy( 'federated-post' );
		$this->options_page->unregister();
		flush_rewrite_rules();
	}
}
