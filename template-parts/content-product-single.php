<?php
/**
 * Elementor-free product detail template.
 *
 * @package DewitTheme
 */

defined( 'ABSPATH' ) || exit;
?>

<main id="primary" class="site-main dewit-product-page">
	<div class="dewit-product-page__layout">
		<aside id="catalog-sidebar" class="shop-sidebar dewit-product__sidebar">
			<button class="dewit-product__sidebar-close" type="button" aria-label="<?php esc_attr_e( 'Categorieën sluiten', 'dewit-catalog-theme' ); ?>">
				<span aria-hidden="true">&times;</span>
			</button>
			<a class="dewit-sidebar-logo-link dewit-product__sidebar-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Terug naar de homepage', 'dewit-theme-woocommerce' ); ?>">
				<?php echo dewit_theme_get_logo_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
			<nav class="dewit-product__category-nav" aria-label="<?php esc_attr_e( 'Categorieën', 'dewit-theme-woocommerce' ); ?>">
				<?php
				wp_list_categories( array(
					'taxonomy'        => 'product_cat',
					'title_li'        => '',
					'hide_empty'      => true,
					'show_count'      => false,
					'orderby'         => 'name',
					'current_category' => is_product_category() ? get_queried_object_id() : 0,
					'hierarchical'    => true,
				) );
				?>
			</nav>
		</aside>

		<div class="container dewit-product-page__inner dewit-product__content">
			<?php while ( have_posts() ) : the_post();
				global $product;
				if ( ! $product instanceof WC_Product ) {
					$product = wc_get_product( get_the_ID() );
				}
				$product_resources = dewit_theme_get_product_resources( $product );
				$product_description = $product ? dewit_theme_remove_empty_paragraphs( apply_filters( 'the_content', $product->get_description() ) ) : '';
				$breadcrumb_terms = $product ? get_the_terms( $product->get_id(), 'product_cat' ) : array();
				$breadcrumb_term = ( ! is_wp_error( $breadcrumb_terms ) && ! empty( $breadcrumb_terms ) ) ? end( $breadcrumb_terms ) : null;
				$breadcrumb_chain = array();
				while ( $breadcrumb_term instanceof WP_Term ) {
					array_unshift( $breadcrumb_chain, $breadcrumb_term );
					if ( ! $breadcrumb_term->parent ) {
						break;
					}
					$breadcrumb_term = get_term( $breadcrumb_term->parent, 'product_cat' );
				}
			?>
			<nav class="dewit-product-breadcrumb" aria-label="<?php esc_attr_e( 'Product navigatie', 'dewit-theme-woocommerce' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
				<span aria-hidden="true">/</span>
				<?php foreach ( $breadcrumb_chain as $index => $breadcrumb_item ) : ?>
					<?php $breadcrumb_url = get_term_link( $breadcrumb_item ); ?>
					<?php if ( ! is_wp_error( $breadcrumb_url ) ) : ?>
						<a href="<?php echo esc_url( $breadcrumb_url ); ?>"><?php echo esc_html( $breadcrumb_item->name ); ?></a>
						<?php if ( $index < count( $breadcrumb_chain ) - 1 ) : ?><span aria-hidden="true">/</span><?php endif; ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</nav>
			<?php wc_print_notices(); ?>
			<article id="product-<?php the_ID(); ?>" <?php wc_product_class( 'dewit-product', $product ); ?>>
				<section class="dewit-product-hero">
					<div class="dewit-product-gallery"><?php woocommerce_show_product_images(); ?></div>
					<div class="dewit-product-summary">
						<div class="dewit-product-summary__content">
							<?php if ( $product && $product->get_sku() ) : ?>
								<p class="dewit-product-sku"><?php esc_html_e( 'Artikelnummer', 'dewit-theme-woocommerce' ); ?> <?php echo dewit_theme_render_non_linked_text( $product->get_sku() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
							<?php endif; ?>
							<?php woocommerce_template_single_title(); ?>
							<?php if ( '' !== trim( wp_strip_all_tags( $product_description ) ) ) : ?><div class="dewit-product-description"><?php echo $product_description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php else : ?><?php woocommerce_template_single_excerpt(); ?><?php endif; ?>
							<?php if ( ! empty( $product_resources ) ) : ?>
								<div class="dewit-product-resources" aria-label="<?php esc_attr_e( 'Productinformatie', 'dewit-theme-woocommerce' ); ?>"><p><?php esc_html_e( 'Productinformatie', 'dewit-theme-woocommerce' ); ?></p><div class="dewit-product-resources__links">
								<?php foreach ( $product_resources as $resource ) : ?><a class="dewit-product-resource dewit-product-resource--<?php echo esc_attr( $resource['type'] ); ?>" href="<?php echo esc_url( $resource['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $resource['label'] ); ?></a><?php endforeach; ?>
								</div></div>
							<?php endif; ?>
							<div class="dewit-product-notice dewit-product-order-callout" role="note">
								<div class="dewit-product-order-callout__copy">
									<strong><?php esc_html_e( 'Direct bestellen? Bel ons.', 'dewit-catalog-theme' ); ?></strong>
									<p><?php esc_html_e( 'Online bestellen is momenteel niet mogelijk.', 'dewit-catalog-theme' ); ?></p>
								</div>
								<a class="site-header__phone dewit-product-order-callout__phone" href="tel:+31412634969" aria-label="<?php esc_attr_e( 'Bel ons op 0412 - 63 49 69', 'dewit-catalog-theme' ); ?>">
									<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6.6 10.8c1.5 3 3.9 5.4 6.9 6.9l2.3-2.3a1 1 0 0 1 1-.24c1.1.36 2.26.54 3.45.54a1 1 0 0 1 1 1V21a1 1 0 0 1-1 1C10.7 22 2 13.3 2 2.8a1 1 0 0 1 1-1H7.3a1 1 0 0 1 1 1c0 1.19.18 2.35.54 3.45a1 1 0 0 1-.24 1Z"/></svg>
									<?php esc_html_e( '0412 - 63 49 69', 'dewit-catalog-theme' ); ?>
								</a>
							</div>
						</div>
					</div>
				</section>
				<?php if ( ! empty( apply_filters( 'woocommerce_product_tabs', array() ) ) ) : ?><section class="dewit-product-details"><?php woocommerce_output_product_data_tabs(); ?></section><?php endif; ?>
				<?php
				if ( $product instanceof WC_Product ) {
					echo dewit_theme_render_product_category_options_table( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
			</article>
			<?php endwhile; ?>
		</div>
	</div>
</main>
