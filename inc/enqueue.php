<?php
/**
 * Front-end asset loading.
 *
 * @package DewitTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue the theme assets and the data required by the catalog UI.
 */
function dewit_theme_scripts(): void {
	$style_path    = get_template_directory() . '/assets/css/theme.css';
	$script_path   = get_template_directory() . '/assets/js/theme.js';
	$style_version = file_exists( $style_path ) ? (string) filemtime( $style_path ) : DEWIT_THEME_VERSION;
	$script_version = file_exists( $script_path ) ? (string) filemtime( $script_path ) : DEWIT_THEME_VERSION;

	wp_enqueue_style(
		'dewit-theme-woocommerce-style',
		get_template_directory_uri() . '/assets/css/theme.css',
		array(),
		$style_version
	);

	wp_enqueue_script(
		'dewit-theme-woocommerce-script',
		get_template_directory_uri() . '/assets/js/theme.js',
		array(),
		$script_version,
		true
	);

	wp_localize_script(
		'dewit-theme-woocommerce-script',
		'dewitTheme',
		array(
			'ajaxUrl'               => admin_url( 'admin-ajax.php' ),
			'defaultParentCategory' => dewit_theme_get_default_parent_category_slug(),
			'logoUrl'               => dewit_theme_get_logo_url(),
			'homeUrl'               => dewit_theme_get_default_shop_url(),
			'placeholderImage'      => dewit_theme_get_placeholder_image_data(),
		)
	);

	wp_add_inline_script(
		'dewit-theme-woocommerce-script',
		'window.dewitTheme = window.dewitTheme || (typeof dewitTheme !== "undefined" ? dewitTheme : {});',
		'before'
	);

	$parent_slug = dewit_theme_get_current_parent_category_slug();

	if ( '' !== $parent_slug ) {
		$parent_term = get_term_by( 'slug', $parent_slug, 'product_cat' );

		if ( $parent_term instanceof WP_Term ) {
			wp_add_inline_script(
				'dewit-theme-woocommerce-script',
				'window.dewitGroupedCategory = window.dewitGroupedCategory || ' . wp_json_encode( array(
					'label' => $parent_term->name,
					'slug'  => $parent_slug,
				) ) . ';',
				'before'
			);
		}
	}

	if ( taxonomy_exists( 'product_cat' ) ) {
		$terms = get_terms( array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'number'     => 100,
		) );

		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$categories = array_values( array_map(
				static function ( WP_Term $term ): array {
					return array(
						'id'     => $term->term_id,
						'name'   => $term->name,
						'slug'   => $term->slug,
						'parent' => $term->parent,
						'count'  => $term->count,
						'image'  => dewit_theme_get_product_category_image_data( $term->term_id ),
					);
				},
				$terms
			) );

			wp_add_inline_script(
				'dewit-theme-woocommerce-script',
				'window.dewitProductCategories = ' . wp_json_encode( $categories ) . ';',
				'before'
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'dewit_theme_scripts' );
