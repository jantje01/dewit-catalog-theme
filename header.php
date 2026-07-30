<?php
/**
 * The header for the theme.
 *
 * @package DewitTheme
 */

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="format-detection" content="telephone=no">
	<?php dewit_theme_print_critical_font_style(); ?>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'dewit-theme-woocommerce' ); ?></a>

<header class="site-header">
	<div class="container site-header__inner">
		<div class="site-branding">
			<?php
			if ( has_custom_logo() ) {
				the_custom_logo();
			} else {
				?>
				<a class="site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
			<?php
			}
			?>
		</div>

		<button class="dewit-mobile-menu-toggle" type="button" aria-controls="dewit-category-bar" aria-expanded="false">
			<span class="screen-reader-text">Categorieën openen</span>
			<span aria-hidden="true"></span>
			<span aria-hidden="true"></span>
			<span aria-hidden="true"></span>
		</button>

		<form class="catalog-header-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="dewit-header-search"><?php esc_html_e( 'Zoeken naar producten', 'dewit-catalog-theme' ); ?></label>
			<input id="dewit-header-search" type="search" name="s" placeholder="<?php esc_attr_e( 'Zoeken naar producten', 'dewit-catalog-theme' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
			<input type="hidden" name="post_type" value="product">
			<?php $header_parent_category = dewit_theme_get_current_parent_category_slug(); ?>
			<?php if ( '' !== $header_parent_category ) : ?>
				<input type="hidden" name="dewit_parent_cat" value="<?php echo esc_attr( $header_parent_category ); ?>">
			<?php endif; ?>
			<button type="submit" aria-label="<?php esc_attr_e( 'Zoeken', 'dewit-catalog-theme' ); ?>">
				<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
			</button>
			<div class="dewit-shop-search-results" role="region" aria-label="<?php esc_attr_e( 'Directe zoekresultaten', 'dewit-catalog-theme' ); ?>"></div>
		</form>

		<a class="site-header__phone" href="tel:+31412634969" aria-label="0412 - 63 49 69">
			<span aria-hidden="true">☎</span> 0412 - 63 49 69
		</a>

	</div>
</header>
<?php dewit_theme_render_category_bar(); ?>
