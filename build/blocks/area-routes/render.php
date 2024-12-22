<?php
/**
 * Render the Area Routes block
 *
 * @package ClimbGuide
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get the current post ID (this will be the Area post).
$area_id = get_the_ID();

// Get the area's term in the route_area taxonomy.
$area_term = get_term_by( 'slug', get_post_field( 'post_name', $area_id ), 'route_area' );

if ( ! $area_term || is_wp_error( $area_term ) ) {
	return;
}

// Query for routes in this area.
$routes_query = new WP_Query(
	[
		'post_type'      => 'climbing_route',
		'posts_per_page' => -1, // Get all routes
		'tax_query'      => [
			[
				'taxonomy' => 'route_area',
				'field'    => 'term_id',
				'terms'    => $area_term->term_id,
			],
		],
	]
);

if ( ! $routes_query->have_posts() ) {
	echo '<p>' . esc_html__( 'No routes found in this area.', 'climb-guide' ) . '</p>';
	return;
}

// Get the custom order from post meta
$custom_order = get_post_meta( $area_id, '_route_order', true );

// If we have a custom order, reorder the posts accordingly
if ( ! empty( $custom_order ) ) {
	$ordered_posts = [];
	$unordered_posts = [];
	
	while ( $routes_query->have_posts() ) {
		$routes_query->the_post();
		$post_id = get_the_ID();
		
		if ( in_array( $post_id, $custom_order, true ) ) {
			$ordered_posts[ array_search( $post_id, $custom_order, true ) ] = get_post();
		} else {
			$unordered_posts[] = get_post();
		}
	}
	
	// Sort the ordered posts by their position in the custom order
	ksort( $ordered_posts );
	$posts = array_merge( $ordered_posts, $unordered_posts );
} else {
	$posts = $routes_query->posts;
}
?>

<div <?php echo get_block_wrapper_attributes( [ 'class' => 'area-routes' ] ); ?>>
	<ul class="area-routes__list">
		<?php
		foreach ( $posts as $post ) {
			setup_postdata( $post );
			?>
			<li class="area-routes__item">
				<article class="area-routes__article">
					<header class="area-routes__header">
						<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" class="area-routes__link">
							<?php if ( has_post_thumbnail( $post->ID ) ) : ?>
								<div class="area-routes__thumbnail">
									<?php echo get_the_post_thumbnail( $post->ID, 'medium' ); ?>
								</div>
							<?php endif; ?>
							<div class="area-routes__content">
								<h3 class="area-routes__title"><?php echo esc_html( get_the_title( $post->ID ) ); ?></h3>
								<?php
								$difficulties = get_the_terms( $post->ID, 'difficulty' );
								if ( $difficulties && ! is_wp_error( $difficulties ) ) :
									?>
									<span class="area-routes__difficulty">
										<?php echo esc_html( $difficulties[0]->name ); ?>
									</span>
								<?php endif; ?>
							</div>
						</a>
					</header>
					<div class="area-routes__body">
						<?php
						$content = get_the_content( null, false, $post );
						$content = apply_filters( 'the_content', $content );
						echo wp_kses_post( $content );
						?>
					</div>
				</article>
			</li>
			<?php
		}
		wp_reset_postdata();
		?>
	</ul>
</div>
