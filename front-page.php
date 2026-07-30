<?php
/**
 * Catalog homepage and category landing view.
 *
 * @package DewitTheme
 */

defined( 'ABSPATH' ) || exit;

$parent_slug = function_exists( 'dewit_theme_get_current_parent_category_slug' )
	? dewit_theme_get_current_parent_category_slug()
	: '';
$parent_term = $parent_slug ? get_term_by( 'slug', $parent_slug, 'product_cat' ) : false;
$has_category_context = isset( $_GET['dewit_parent_cat'] ) || ( function_exists( 'is_product_category' ) && is_product_category() ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

get_header();
?>

<main id="primary" class="site-main site-main--catalog dewit-grid-catalog" aria-label="<?php esc_attr_e( 'Productcatalogus', 'dewit-catalog-theme' ); ?>">
	<div class="container dewit-grid-catalog__inner">
		<?php if ( $has_category_context ) : ?>
			<header class="dewit-grid-catalog__intro">
				<h1><?php echo $parent_term instanceof WP_Term ? esc_html( $parent_term->name ) : esc_html__( 'Productcatalogus', 'dewit-catalog-theme' ); ?></h1>
			</header>

			<?php echo dewit_theme_render_grouped_category_products_html( $parent_slug ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php else : ?>
			<section class="dewit-home-hero" aria-labelledby="dewit-home-title">
				<div class="dewit-home-hero__copy">
					<h1 id="dewit-home-title"><?php esc_html_e( 'Alles voor de bouw op één plek.', 'dewit-catalog-theme' ); ?></h1>
					<p><?php esc_html_e( 'Ontdek ons assortiment bouwmachines, bouwmaterialen en professioneel materieel.', 'dewit-catalog-theme' ); ?></p>
					<a class="dewit-home-hero__cta" href="#dewit-home-categories">
						<?php esc_html_e( 'Bekijk het assortiment', 'dewit-catalog-theme' ); ?>
						<span aria-hidden="true">→</span>
					</a>
				</div>
				<div class="dewit-home-hero__mark" aria-hidden="true">AW</div>
			</section>

			<section id="dewit-home-categories" class="dewit-home-categories" aria-labelledby="dewit-home-categories-title">
				<div class="dewit-home-section-heading">
					<p class="dewit-home-hero__eyebrow"><?php esc_html_e( 'Ons assortiment', 'dewit-catalog-theme' ); ?></p>
					<h2 id="dewit-home-categories-title"><?php esc_html_e( 'Waar ben je naar op zoek?', 'dewit-catalog-theme' ); ?></h2>
				</div>
				<div class="dewit-home-category-grid">
					<?php
					$home_categories = get_terms(
						array(
							'taxonomy'   => 'product_cat',
							'parent'     => 0,
							'hide_empty' => true,
							'orderby'    => 'name',
							'order'      => 'ASC',
						)
					);
					if ( ! is_wp_error( $home_categories ) ) :
						foreach ( $home_categories as $category ) :
							if ( 'alle' === strtolower( (string) $category->slug ) || 'alle' === strtolower( (string) $category->name ) ) {
								continue;
							}
							$category_url = get_term_link( $category );
							if ( is_wp_error( $category_url ) ) {
								$category_url = dewit_theme_get_parent_category_shop_url( $category->slug );
							}
							$subcategories = get_terms( array(
								'taxonomy'   => 'product_cat',
								'parent'     => $category->term_id,
								'hide_empty' => true,
								'orderby'    => 'name',
								'order'      => 'ASC',
							) );
							?>
			<div class="dewit-home-category-group">
			<a class="dewit-home-category-card" href="<?php echo esc_url( $category_url ); ?>">
								<span><?php echo esc_html( $category->name ); ?></span>
								<strong aria-hidden="true">→</strong>
			</a>
			<?php if ( ! is_wp_error( $subcategories ) && $subcategories ) : ?>
				<ul class="dewit-home-subcategory-list">
					<?php foreach ( $subcategories as $subcategory ) : ?>
						<?php $subcategory_url = get_term_link( $subcategory ); ?>
						<?php if ( ! is_wp_error( $subcategory_url ) ) : ?>
							<li><a href="<?php echo esc_url( $subcategory_url ); ?>"><span><?php echo esc_html( $subcategory->name ); ?></span></a></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			</div>
							<?php
						endforeach;
					endif;
					?>
				</div>
			</section>
		<?php endif; ?>
	</div>
</main>

<?php get_footer(); ?>
