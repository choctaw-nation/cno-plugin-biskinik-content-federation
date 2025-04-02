<?php
/**
 * Taxonomy Handler
 *
 * @package ChoctawNation
 * @subpackage BiskinikContentFederation
 */

namespace ChoctawNation\BiskinikContentFederation;

/**
 * Registers the `federated-post` taxonomy and its needed ACF fields
 */
class Taxonomy_Handler {
	/**
	 * The Taxonomy ID
	 *
	 * @var string
	 */
	public string $tax_id = 'federated-post';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_taxonomy' ) );
		add_action( 'acf/include_fields', array( $this, 'register_acf_field' ), );
	}

	/**
	 * Register the ACF Field
	 */
	public function register_acf_field() {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'                   => 'group_67eae65b06af5',
				'title'                 => 'Taxonomy — Federated Content',
				'fields'                => array(
					array(
						'key'                => 'field_67eae65b5e528',
						'label'              => 'Taxonomy ID',
						'name'               => 'taxonomy_id',
						'aria-label'         => '',
						'type'               => 'number',
						'instructions'       => 'The ID of the term on the Nation Site',
						'required'           => 1,
						'conditional_logic'  => 0,
						'wrapper'            => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'relevanssi_exclude' => 1,
						'default_value'      => '',
						'min'                => 1,
						'max'                => '',
						'allow_in_bindings'  => 0,
						'placeholder'        => '',
						'step'               => 1,
						'prepend'            => '',
						'append'             => '',
					),
				),
				'location'              => array(
					array(
						array(
							'param'    => 'taxonomy',
							'operator' => '==',
							'value'    => $this->tax_id,
						),
					),
				),
				'menu_order'            => 0,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen'        => '',
				'active'                => true,
				'description'           => '',
				'show_in_rest'          => 0,
			)
		);
	}

	/**
	 * Register the Taxonomy
	 */
	public function register_taxonomy() {
		register_taxonomy(
			$this->tax_id,
			array(
				0 => 'post',
			),
			array(
				'labels'            => array(
					'name'                       => 'Federated Posts',
					'singular_name'              => 'Federated Post',
					'menu_name'                  => 'Federated Posts',
					'all_items'                  => 'All Federated Posts',
					'edit_item'                  => 'Edit Federated Post',
					'view_item'                  => 'View Federated Post',
					'update_item'                => 'Update Federated Post',
					'add_new_item'               => 'Add New Federated Post',
					'new_item_name'              => 'New Federated Post Name',
					'search_items'               => 'Search Federated Posts',
					'popular_items'              => 'Popular Federated Posts',
					'separate_items_with_commas' => 'Separate federated posts with commas',
					'add_or_remove_items'        => 'Add or remove federated posts',
					'choose_from_most_used'      => 'Choose from the most used federated posts',
					'not_found'                  => 'No federated posts found',
					'no_terms'                   => 'No federated posts',
					'items_list_navigation'      => 'Federated Posts list navigation',
					'items_list'                 => 'Federated Posts list',
					'back_to_items'              => '← Go to federated posts',
					'item_link'                  => 'Federated Post Link',
					'item_link_description'      => 'A link to a federated post',
				),
				'description'       => 'Content federated from the Choctaw Nation site',
				'public'            => true,
				'show_in_menu'      => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
			)
		);
	}
}
