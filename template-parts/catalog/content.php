<?php
/** Catalog content and grouped products. */

defined( 'ABSPATH' ) || exit;

$parent_slug = isset( $args['parent_slug'] ) ? (string) $args['parent_slug'] : '';
$parent_term = $parent_slug ? get_term_by( 'slug', $parent_slug, 'product_cat' ) : false;
?>
<section class="dewit-catalog-content dewit-catalog__content" aria-label="<?php esc_attr_e( 'Productcatalogus', 'dewit-catalog-theme' ); ?>">
	<div class="dewit-catalog-toolbar">
		<?php if ( $parent_term instanceof WP_Term ) : ?>
			<h1 class="dewit-catalog-parent-title"><?php echo esc_html( $parent_term->name ); ?></h1>
		<?php endif; ?>
		<div class="dewit-catalog-view-switch" role="group" aria-label="<?php esc_attr_e( 'Productweergave', 'dewit-catalog-theme' ); ?>">
			<button type="button" class="is-active" data-dewit-view="grid" aria-label="Rasterweergave"><span aria-hidden="true">⊞</span></button>
			<button type="button" data-dewit-view="list" aria-label="Lijstweergave"><span aria-hidden="true">▤</span></button>
			<button type="button" data-dewit-view="compact" aria-label="Compacte weergave"><span aria-hidden="true">≡</span></button>
		</div>
	</div>

	<?php echo dewit_theme_render_grouped_category_products_html( $parent_slug ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</section>
