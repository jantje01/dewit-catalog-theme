<?php
/** Catalog category navigation. */

defined( 'ABSPATH' ) || exit;
?>
<aside id="catalog-sidebar" class="shop-sidebar dewit-catalog-sidebar dewit-catalog__sidebar">
	<div class="dewit-catalog-sidebar__logo"><?php echo dewit_theme_get_logo_markup( 'dewit-catalog-logo' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	<div class="dewit-catalog-sidebar__categories">
		<search class="dewit-category-filter dewit-catalog-category-filter" role="search" aria-label="<?php esc_attr_e( 'Categorieën', 'dewit-catalog-theme' ); ?>"></search>
		<?php if ( is_active_sidebar( 'shop-sidebar' ) ) : ?>
			<?php dynamic_sidebar( 'shop-sidebar' ); ?>
		<?php else : ?>
			<nav class="dewit-catalog-nav" aria-label="<?php esc_attr_e( 'Catalogus categorieën', 'dewit-catalog-theme' ); ?>">
				<?php
				wp_list_categories( array(
					'taxonomy'   => 'product_cat',
					'title_li'   => '',
					'hide_empty' => true,
				) );
				?>
			</nav>
		<?php endif; ?>
	</div>
</aside>
