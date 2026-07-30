<?php
/**
 * Theme setup and global registrations.
 *
 * @package DewitTheme
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'dewit_theme_setup' ) ) {
	/**
	 * Set up theme defaults and supported WordPress features.
	 */
	function dewit_theme_setup(): void {
		load_theme_textdomain( 'dewit-theme-woocommerce', get_template_directory() . '/languages' );

		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_image_size( 'dewit_catalog_card', 480, 480, false );
		add_theme_support( 'custom-logo', array(
			'height'      => 80,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		) );
		add_theme_support( 'html5', array(
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
			'navigation-widgets',
		) );
		add_theme_support( 'align-wide' );
		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/css/theme.css' );

		add_theme_support( 'woocommerce' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );

		register_nav_menus( array(
			'primary' => __( 'Primary Menu', 'dewit-theme-woocommerce' ),
			'footer'  => __( 'Footer Menu', 'dewit-theme-woocommerce' ),
		) );
	}
}
add_action( 'after_setup_theme', 'dewit_theme_setup' );

/**
 * Set content width for embeds.
 */
function dewit_theme_content_width(): void {
	$GLOBALS['content_width'] = apply_filters( 'dewit_theme_content_width', 1200 );
}
add_action( 'after_setup_theme', 'dewit_theme_content_width', 0 );

/**
 * Register widget areas.
 */
function dewit_theme_widgets_init(): void {
	register_sidebar( array(
		'name'          => __( 'Sidebar', 'dewit-theme-woocommerce' ),
		'id'            => 'sidebar-1',
		'description'   => __( 'Add widgets here.', 'dewit-theme-woocommerce' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => __( 'Shop Sidebar', 'dewit-theme-woocommerce' ),
		'id'            => 'shop-sidebar',
		'description'   => __( 'Filters and widgets for WooCommerce shop pages.', 'dewit-theme-woocommerce' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'dewit_theme_widgets_init' );
