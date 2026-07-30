<?php
/**
 * Elementor-free WooCommerce catalog archive.
 *
 * @package DewitTheme
 */

defined( 'ABSPATH' ) || exit;

$parent_slug = function_exists( 'dewit_theme_get_current_parent_category_slug' )
	? dewit_theme_get_current_parent_category_slug()
	: '';

if ( ! isset( $_GET['dewit_parent_cat'] ) && ! ( function_exists( 'is_product_category' ) && is_product_category() ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	get_template_part( 'front-page' );
	return;
}

$parent_term = $parent_slug ? get_term_by( 'slug', $parent_slug, 'product_cat' ) : false;

get_header();
?>

<main id="primary" class="site-main site-main--catalog dewit-grid-catalog" aria-label="<?php esc_attr_e( 'Productcatalogus', 'dewit-catalog-theme' ); ?>">
	<div class="container dewit-grid-catalog__inner">
		<header class="dewit-grid-catalog__intro">
			<h1><?php echo $parent_term instanceof WP_Term ? esc_html( $parent_term->name ) : esc_html__( 'Productcatalogus', 'dewit-catalog-theme' ); ?></h1>
		</header>
		<?php echo dewit_theme_render_grouped_category_products_html( $parent_slug ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
</main>

<?php get_footer(); ?>
