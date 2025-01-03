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

		// Add these new hooks.
		add_action( 'save_post_climbing_area', [ $this, 'sync_area_to_taxonomy' ], 10, 3 );
		add_filter( 'post_type_link', [ $this, 'modify_route_permalink' ], 10, 2 );
		add_filter( 'post_type_link', [ $this, 'modify_area_permalink' ], 10, 2 );
		add_action( 'init', [ $this, 'add_rewrite_rules' ], 20 );
		add_action( 'admin_menu', [ $this, 'add_migration_menu' ] );

		// Register activation hook.
		register_activation_hook( __FILE__, [ $this, 'activate' ] );
	}

	/**
	 * Initialize the plugin
	 */
	private function init() {
		// Add hooks here.
		add_action( 'init', [ $this, 'register_post_types' ] );
		add_action( 'init', [ $this, 'register_taxonomies' ] );
		add_action( 'init', [ $this, 'register_blocks' ] );
		add_action( 'init', [ $this, 'register_meta' ] );
	}

	/**
	 * Register custom post types
	 */
	public function register_post_types() {
		// Register the Areas CPT.
		register_post_type(
			'climbing_area',
			[
				'labels'        => [
					'name'               => __( 'Areas', 'climbing-guide' ),
					'singular_name'      => __( 'Area', 'climbing-guide' ),
					'add_new'            => __( 'Add New Area', 'climbing-guide' ),
					'add_new_item'       => __( 'Add New Area', 'climbing-guide' ),
					'edit_item'          => __( 'Edit Area', 'climbing-guide' ),
					'new_item'           => __( 'New Area', 'climbing-guide' ),
					'view_item'          => __( 'View Area', 'climbing-guide' ),
					'search_items'       => __( 'Search Areas', 'climbing-guide' ),
					'not_found'          => __( 'No areas found', 'climbing-guide' ),
					'not_found_in_trash' => __( 'No areas found in trash', 'climbing-guide' ),
					'parent_item_colon'  => __( 'Parent Area:', 'climbing-guide' ),
				],
				'public'        => true,
				'has_archive'   => true,
				'show_in_rest'  => true,
				'menu_position' => 19,
				'supports'      => [ 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'custom-fields' ],
				'menu_icon'     => 'dashicons-location',
				'rewrite'       => [
					'slug'       => 'area',
					'with_front' => false,
					'feeds'      => false,
					'pages'      => false,
				],
				'hierarchical'  => true,
			]
		);

		// Existing Routes CPT registration.
		register_post_type(
			'climbing_route',
			[
				'labels'        => [
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
				'public'        => true,
				'has_archive'   => true,
				'show_in_rest'  => true,
				'menu_position' => 18,
				'supports'      => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
				'menu_icon'     => 'dashicons-admin-site',
				'rewrite'       => [
					'slug'       => 'area/%route_area%',
					'with_front' => false,
				],
			]
		);
	}

	/**
	 * Register custom taxonomies
	 */
	public function register_taxonomies() {
		register_taxonomy(
			'route_area',
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
				'rewrite'           => false,
				'public'            => false,
				'show_in_nav_menus' => false,
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

	/**
	 * Sync Area CPT to Area Taxonomy when saving an area
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function sync_area_to_taxonomy( $post_id, $post ) {
		// Don't run if this is an autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Don't run if this is a post revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Create or update the corresponding taxonomy term.
		$term_name = $post->post_title;
		$term_slug = $post->post_name;

		$parent_term_id = 0;
		if ( $post->post_parent ) {
			$parent_area = get_post( $post->post_parent );
			if ( $parent_area ) {
				$parent_term = get_term_by( 'slug', $parent_area->post_name, 'route_area' );
				if ( $parent_term ) {
					$parent_term_id = $parent_term->term_id;
				}
			}
		}

		$existing_term = get_term_by( 'slug', $term_slug, 'route_area' );

		if ( $existing_term ) {
			wp_update_term(
				$existing_term->term_id,
				'route_area',
				[
					'name'   => $term_name,
					'slug'   => $term_slug,
					'parent' => $parent_term_id,
				]
			);
		} else {
			wp_insert_term(
				$term_name,
				'route_area',
				[
					'slug'   => $term_slug,
					'parent' => $parent_term_id,
				]
			);
		}
	}

	/**
	 * Modify the permalink structure for routes
	 *
	 * @param string  $post_link The post's permalink.
	 * @param WP_Post $post      The post in question.
	 * @return string Modified permalink
	 */
	public function modify_route_permalink( $post_link, $post ) {
		if ( 'climbing_route' !== $post->post_type ) {
			return $post_link;
		}

		$terms = wp_get_object_terms( $post->ID, 'route_area' );
		if ( ! empty( $terms ) ) {
			$area_slug = $terms[0]->slug;
			return str_replace( '%route_area%', $area_slug, $post_link );
		}

		return str_replace( '/area/%route_area%/', '/area/', $post_link );
	}

	/**
	 * Modify the permalink structure for areas to include the full path
	 *
	 * @param string  $post_link The post's permalink.
	 * @param WP_Post $post      The post in question.
	 * @return string Modified permalink
	 */
	public function modify_area_permalink( $post_link, $post ) {
		if ( 'climbing_area' !== $post->post_type ) {
			return $post_link;
		}

		// Get all ancestors
		$ancestors = get_post_ancestors( $post->ID );
		if ( empty( $ancestors ) ) {
			return $post_link;
		}

		// Reverse the array to get the correct order (root -> child)
		$ancestors = array_reverse( $ancestors );
		
		// Build the path
		$path = '';
		foreach ( $ancestors as $ancestor ) {
			$path .= get_post_field( 'post_name', $ancestor ) . '/';
		}

		// Add the current post's slug
		$path .= $post->post_name;

		// Replace the permalink
		return home_url( "area/{$path}/" );
	}

	/**
	 * Add custom rewrite rules
	 */
	public function add_rewrite_rules() {
		// Rule for nested areas - match any number of path segments
		add_rewrite_rule(
			'^area/(.+?)/?$',
			'index.php?climbing_area=$matches[1]',
			'top'
		);

		// Rule for routes under areas
		add_rewrite_rule(
			'^area/(.+)/([^/]+)/?$',
			'index.php?climbing_route=$matches[2]',
			'top'
		);
	}

	/**
	 * Migrate existing taxonomy terms to CPT posts
	 */
	public function migrate_terms_to_posts() {
		// Get all terms, including empty ones.
		$terms = get_terms(
			[
				'taxonomy'   => 'route_area',
				'hide_empty' => false,
				'orderby'    => 'parent',
				'order'      => 'ASC',
			]
		);

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return;
		}

		// First pass: Create all posts and store term_id => post_id mapping.
		$term_to_post_map = [];

		foreach ( $terms as $term ) {
			// Check if a post already exists with this slug.
			$existing_post = get_posts(
				[
					'post_type'      => 'climbing_area',
					'name'           => $term->slug,
					'posts_per_page' => 1,
				]
			);

			if ( ! empty( $existing_post ) ) {
				$term_to_post_map[ $term->term_id ] = $existing_post[0]->ID;
				continue;
			}

			// Create the post.
			$post_data = [
				'post_title'   => $term->name,
				'post_name'    => $term->slug,
				'post_content' => $term->description,
				'post_type'    => 'climbing_area',
				'post_status'  => 'publish',
			];

			// Insert the post.
			$post_id = wp_insert_post( $post_data );

			if ( ! is_wp_error( $post_id ) ) {
				$term_to_post_map[ $term->term_id ] = $post_id;
			}
		}

		// Second pass: Update parent relationships.
		foreach ( $terms as $term ) {
			if ( $term->parent && isset( $term_to_post_map[ $term->term_id ] ) && isset( $term_to_post_map[ $term->parent ] ) ) {
				wp_update_post(
					[
						'ID'          => $term_to_post_map[ $term->term_id ],
						'post_parent' => $term_to_post_map[ $term->parent ],
					]
				);
			}
		}

		// Third pass: Update route associations.
		$routes = get_posts(
			[
				'post_type'      => 'climbing_route',
				'posts_per_page' => -1,
			]
		);

		foreach ( $routes as $route ) {
			$route_terms = wp_get_object_terms( $route->ID, 'route_area' );

			if ( ! empty( $route_terms ) && ! is_wp_error( $route_terms ) ) {
				foreach ( $route_terms as $term ) {
					if ( isset( $term_to_post_map[ $term->term_id ] ) ) {
						// Keep the taxonomy relationship and add post relationship.
						wp_set_object_terms( $route->ID, $term->term_id, 'route_area', true );
					}
				}
			}
		}
	}

	/**
	 * Add migration menu item
	 */
	public function add_migration_menu() {
		add_submenu_page(
			'edit.php?post_type=climbing_area',
			__( 'Migrate Areas', 'climbing-guide' ),
			__( 'Migrate Areas', 'climbing-guide' ),
			'manage_options',
			'migrate-climbing-areas',
			[ $this, 'render_migration_page' ],
		);
	}

	/**
	 * Render migration page
	 */
	public function render_migration_page() {
		if ( isset( $_POST['migrate_areas'] ) && check_admin_referer( 'migrate_areas_nonce' ) ) {
			$this->migrate_terms_to_posts();
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Migration completed!', 'climbing-guide' ) . '</p></div>';
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Migrate Areas', 'climbing-guide' ); ?></h1>
			<p><?php esc_html_e( 'This will migrate all existing area taxonomy terms to area posts.', 'climbing-guide' ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'migrate_areas_nonce' ); ?>
				<input type="submit" name="migrate_areas" class="button button-primary" value="<?php esc_attr_e( 'Start Migration', 'climbing-guide' ); ?>">
			</form>
		</div>
		<?php
	}

	/**
	 * Register blocks
	 */
	public function register_blocks() {
		register_block_type( __DIR__ . '/build/blocks/area-routes' );
	}

	/**
	 * Plugin activation handler
	 */
	public function activate() {
		$this->migrate_terms_to_posts();
		flush_rewrite_rules();
	}

	/**
	 * Register custom meta fields
	 */
	public function register_meta() {
		register_post_meta(
			'climbing_area',
			'_route_order',
			[
				'type'          => 'array',
				'description'   => __( 'Custom order of routes in this area', 'climb-guide' ),
				'single'        => true,
				'show_in_rest'  => [
					'schema' => [
						'type'  => 'array',
						'items' => [
							'type' => 'integer',
						],
					],
				],
				'auth_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
			]
		);
	}
}

// Initialize the plugin.
new Climb_Guide();
