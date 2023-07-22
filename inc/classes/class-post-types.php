<?php
/**
 * Register Post Types
 *
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;

class Post_Types {
	use Singleton;
	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		add_action('init', [$this, 'create_log_cpt'], 0, 0);
	}
	// Register Custom Post Type Movie
	public function create_log_cpt() {
		$labels = [
			'name'                  => _x( 'Category Popup Logs', 'Post Type General Name', 'teddybearsprompts' ),
			'singular_name'         => _x( 'Log', 'Post Type Singular Name', 'teddybearsprompts' ),
			'menu_name'             => _x( 'Logs', 'Admin Menu text', 'teddybearsprompts' ),
			'name_admin_bar'        => _x( 'Log', 'Add New on Toolbar', 'teddybearsprompts' ),
			'archives'              => __( 'Log Archives', 'teddybearsprompts' ),
			'attributes'            => __( 'Log Attributes', 'teddybearsprompts' ),
			'parent_item_colon'     => __( 'Parent Log:', 'teddybearsprompts' ),
			'all_items'             => __( 'All Logs', 'teddybearsprompts' ),
			'add_new_item'          => __( 'Add New Log', 'teddybearsprompts' ),
			'add_new'               => __( 'Add New', 'teddybearsprompts' ),
			'new_item'              => __( 'New Log', 'teddybearsprompts' ),
			'edit_item'             => __( 'Edit Log', 'teddybearsprompts' ),
			'update_item'           => __( 'Update Log', 'teddybearsprompts' ),
			'view_item'             => __( 'View Log', 'teddybearsprompts' ),
			'view_items'            => __( 'View Logs', 'teddybearsprompts' ),
			'search_items'          => __( 'Search Log', 'teddybearsprompts' ),
			'not_found'             => __( 'Not found', 'teddybearsprompts' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'teddybearsprompts' ),
			'featured_image'        => __( 'Featured Image', 'teddybearsprompts' ),
			'set_featured_image'    => __( 'Set featured image', 'teddybearsprompts' ),
			'remove_featured_image' => __( 'Remove featured image', 'teddybearsprompts' ),
			'use_featured_image'    => __( 'Use as featured image', 'teddybearsprompts' ),
			'insert_into_item'      => __( 'Insert into Log', 'teddybearsprompts' ),
			'uploaded_to_this_item' => __( 'Uploaded to this Log', 'teddybearsprompts' ),
			'items_list'            => __( 'Logs list', 'teddybearsprompts' ),
			'items_list_navigation' => __( 'Logs list navigation', 'teddybearsprompts' ),
			'filter_items_list'     => __( 'Filter Logs list', 'teddybearsprompts' ),
		];
		$args = [
			'label'               => __( 'Log', 'teddybearsprompts' ),
			'description'         => __( 'The Logs', 'teddybearsprompts' ),
			'labels'              => $labels,
			'menu_icon'           => 'dashicons-video-alt',
			'supports'            => [
				'title',
				// 'editor',
				// 'excerpt',
				// 'thumbnail',
				// 'revisions',
				// 'author',
				// 'comments',
				// 'trackbacks',
				// 'page-attributes',
				// 'custom-fields'
			],
			'taxonomies'          => [],
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'hierarchical'        => false,
			'exclude_from_search' => false,
			'show_in_rest'        => true,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
		];
		register_post_type( 'logs', $args );
	}
}
