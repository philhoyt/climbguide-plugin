<?php
/**
 * Plugin Name: Climb Guide
 * Plugin URI: #
 * Description: A WordPress plugin for managing climbing routes and locations
 * Version: 1.0.0
 * Author: Phil Hoyt
 * Author URI: #
 * Text Domain: climb-guide
 * Domain Path: /languages
 *
 * @package ClimbingGuide
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin constants.
define( 'CLIMBING_GUIDE_VERSION', '1.0.0' );
define( 'CLIMBING_GUIDE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CLIMBING_GUIDE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Class to initialize the plugin
 */
class Climb_Guide {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize the plugin
	 */
	private function init() {
		// Add hooks here.
		add_action( 'init', [ $this, 'register_post_types' ] );
		add_action( 'init', [ $this, 'register_taxonomies' ] );
		// dd_action( 'init', [ $this, 'register_blocks' ] );
	}

	/**
	 * Register custom post types
	 */
	public function register_post_types() {
		register_post_type(
			'climbing_route',
			[
				'labels'       => [
					'name'               => __( 'Routes', 'climbing-guide' ),
					'singular_name'      => __( 'Route', 'climbing-guide' ),
					'add_new'            => __( 'Add New Route', 'climbing-guide' ),
					'add_new_item'       => __( 'Add New Route', 'climbing-guide' ),
					'edit_item'          => __( 'Edit Route', 'climbing-guide' ),
					'new_item'           => __( 'New Route', 'climbing-guide' ),
					'view_item'          => __( 'View Route', 'climbing-guide' ),
					'search_items'       => __( 'Search Routes', 'climbing-guide' ),
					'not_found'          => __( 'No routes found', 'climbing-guide' ),
					'not_found_in_trash' => __( 'No routes found in trash', 'climbing-guide' ),
				],
				'public'       => true,
				'has_archive'  => true,
				'show_in_rest' => true,
				'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
				'menu_icon'    => 'dashicons-admin-site',
				'rewrite'      => [ 'slug' => 'routes' ],
			]
		);
	}

	/**
	 * Register custom taxonomies
	 */
	public function register_taxonomies() {
		register_taxonomy(
			'climbing_area',
			'climbing_route',
			[
				'labels'            => [
					'name'              => __( 'Climbing Areas', 'climbing-guide' ),
					'singular_name'     => __( 'Climbing Area', 'climbing-guide' ),
					'search_items'      => __( 'Search Areas', 'climbing-guide' ),
					'all_items'         => __( 'All Areas', 'climbing-guide' ),
					'parent_item'       => __( 'Parent Area', 'climbing-guide' ),
					'parent_item_colon' => __( 'Parent Area:', 'climbing-guide' ),
					'edit_item'         => __( 'Edit Area', 'climbing-guide' ),
					'update_item'       => __( 'Update Area', 'climbing-guide' ),
					'add_new_item'      => __( 'Add New Area', 'climbing-guide' ),
					'new_item_name'     => __( 'New Area Name', 'climbing-guide' ),
					'menu_name'         => __( 'Areas', 'climbing-guide' ),
				],
				'hierarchical'      => true,
				'show_ui'           => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => [ 'slug' => 'area' ],
			]
		);

		register_taxonomy(
			'difficulty',
			'climbing_route',
			[
				'labels'            => [
					'name'          => __( 'Difficulties', 'climbing-guide' ),
					'singular_name' => __( 'Difficulty', 'climbing-guide' ),
					'search_items'  => __( 'Search Difficulties', 'climbing-guide' ),
					'all_items'     => __( 'All Difficulties', 'climbing-guide' ),
					'edit_item'     => __( 'Edit Difficulty', 'climbing-guide' ),
					'update_item'   => __( 'Update Difficulty', 'climbing-guide' ),
					'add_new_item'  => __( 'Add New Difficulty', 'climbing-guide' ),
					'new_item_name' => __( 'New Difficulty Name', 'climbing-guide' ),
					'menu_name'     => __( 'Difficulties', 'climbing-guide' ),
				],
				'hierarchical'      => false,
				'show_ui'           => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => [ 'slug' => 'difficulty' ],
			]
		);
	}
}

// Initialize the plugin.
new Climb_Guide();
