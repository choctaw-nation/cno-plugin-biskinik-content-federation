<?php
/**
 * Options Page for Federated Content
 *
 * @since 1.0
 * @package ChoctawNation
 * @subpackage BiskinikContentFederation
 */

namespace ChoctawNation\BiskinikContentFederation;

/**
 * Options Page class to handle the settings page
 */
class Options_Page {
	/**
	 * Registers the options page and settings
	 */
	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Deregisters the options page and settings
	 */
	public function unregister(): void {
		remove_action( 'admin_init', array( $this, 'register_settings' ) );
		remove_action( 'admin_menu', array( $this, 'add_options_page' ) );
		remove_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Adds the options page to the WordPress admin menu
	 */
	public function add_options_page(): void {
		add_options_page(
			'Federated Content',
			'Federated Content',
			'manage_options',
			'federated-content',
			array( $this, 'render_options_page_html' )
		);
	}

	/**
	 * Enqueues scripts for the options page
	 */
	public function enqueue_scripts(): void {
		if ( ! is_admin() || get_current_screen()->id !== 'settings_page_federated-content' ) {
			return;
		}
		$file_name  = 'cno-plugin-biskinik-content-federation';
		$base_path  = __DIR__;
		$asset_file = require_once plugin_dir_path( $base_path ) . "dist/{$file_name}.asset.php";
		wp_enqueue_script(
			$file_name,
			plugin_dir_url( $base_path ) . "dist/{$file_name}.js",
			$asset_file['dependencies'],
			$asset_file['version'],
			array( 'strategy' => 'defer' )
		);
		wp_enqueue_style( 'wp-components' );
	}

	/**
	 * Registers the API key setting
	 */
	public function register_settings(): void {
		register_setting(
			'options',
			'cno_biskinik_federated_content',
			array(
				'type'              => 'string',
				'label'             => 'Federated Content API key',
				'sanitize_callback' => 'sanitize_text_field',
				'description'       => 'API key for the Federated Content API',
				'default'           => '',
				'show_in_rest'      => true,
			)
		);
	}


	/**
	 * Renders the options page
	 */
	public function render_options_page_html(): void {
		echo '<div class="wrap" id="cno-biskinik-federated-content-settings">Loading...</div>';
	}
}
