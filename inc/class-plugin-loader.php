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
	 * The taxonomy handler
	 *
	 * @var Taxonomy_Handler $tax_handler
	 */
	private Taxonomy_Handler $tax_handler;


	/**
	 * The options page handler
	 *
	 * @var Options_Page $options_page
	 */
	private Options_Page $options_page;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->load_files();
		$this->handle_redirection();
		add_action( 'admin_init', array( $this, 'activation_redirection' ) );
	}

	/**
	 * Handles the redirection for federated post content
	 */
	public function handle_redirection() {
		add_filter(
			'allowed_redirect_hosts',
			fn( $hosts ) => array( ...$hosts, 'www.choctawnation.com' )
		);
		add_filter(
			'template_include',
			function ( $template ) {
				if ( is_singular() ) {
					$post_id = get_the_ID();
					$terms   = get_the_terms( $post_id, 'federated-post' );
					if ( $terms && ! is_wp_error( $terms ) ) {
						$term_slug     = $terms[0]->slug;
						$post_slug     = get_post_field( 'post_name', $post_id );
						$original_post = "https://www.choctawnation.com/news/{$term_slug}/{$post_slug}";
						wp_safe_redirect( $original_post, 301 );
						exit;
					}
				}
				return $template;
			}
		);
	}

	/**
	 * Loads the Plugin Files
	 */
	private function load_files(): void {
		$files_to_load = array(
			'taxonomy-handler',
			'plugin-api',
			'content-api',
			'options-page',
		);
		foreach ( $files_to_load as $file ) {
			require_once plugin_dir_path( __FILE__ ) . "class-{$file}.php";
		}
		$this->tax_handler  = new Taxonomy_Handler();
		$this->options_page = new Options_Page();
		new Plugin_API( $this->tax_handler->tax_id );
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
	}

	/**
	 * Handles the redirect after plugin activation
	 */
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
