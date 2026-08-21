<?php
/**
 * De Wit Theme WooCommerce functions and definitions.
 *
 * @package DewitTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DEWIT_THEME_VERSION', '0.3.199' );
define( 'DEWIT_DEFAULT_PARENT_CATEGORY_SLUG', 'steigermateriaal' );
define( 'DEWIT_SHOP_SOCIAL_IMAGE_URL', 'https://shop.dewitbouwmachines.nl/wp-content/uploads/2026/06/download.jpg' );
define( 'DEWIT_THEME_LOGO_FILE', '/assets/images/dewit-logo.svg' );
define( 'DEWIT_CATALOG_UI_ENABLED', false );

require_once get_template_directory() . '/inc/helpers.php';
require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/enqueue.php';
require_once get_template_directory() . '/inc/catalog.php';

function dewit_theme_is_catalog_ui_enabled(): bool {
	return defined( 'DEWIT_CATALOG_UI_ENABLED' ) && DEWIT_CATALOG_UI_ENABLED;
}

function dewit_theme_get_shop_meta_description(): string {
	return 'Bekijk het assortiment bouwmachines, bekisting, steigers, verbruiksmaterialen en ondersteuningsmateriaal van De Wit Bouwmachines. Wij hebben altijd de oplossing in huis.';
}

function dewit_theme_remove_empty_paragraphs( string $html ): string {
	return preg_replace(
		'/<p>(?:\s|&nbsp;|&#160;|\xC2\xA0|<br\s*\/?>)*<\/p>/iu',
		'',
		$html
	) ?? $html;
}

function dewit_theme_print_critical_font_style(): void {
	?>
	<style id="dewit-critical-font-css">
		:root {
			--dewit-system-font: Inter, ui-sans-serif, -apple-system, BlinkMacSystemFont, "SF Pro Text", "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
		}

		html,
		body,
		button,
		input,
		select,
		textarea {
			font-family: var(--dewit-system-font) !important;
		}

		html body :where(.woocommerce, .woocommerce-loop-product__title, .product_title, [class^="dewit-"], [class*=" dewit-"]) {
			font-family: var(--dewit-system-font) !important;
		}
	</style>
	<?php
}
function dewit_theme_get_social_locale(): string {
	return 'nl_NL';
}
add_filter( 'wpseo_locale', 'dewit_theme_get_social_locale', 20 );
add_filter( 'wpseo_og_locale', 'dewit_theme_get_social_locale', 20 );

function dewit_theme_get_default_shop_url(): string {
	return add_query_arg( 'dewit_parent_cat', sanitize_title( DEWIT_DEFAULT_PARENT_CATEGORY_SLUG ), home_url( '/' ) );
}

function dewit_theme_get_logo_url(): string {
	return get_template_directory_uri() . DEWIT_THEME_LOGO_FILE;
}

function dewit_theme_get_logo_markup( string $class = 'custom-logo' ): string {
	return sprintf(
		'<img src="%1$s" class="%2$s" alt="%3$s" width="1028" height="245" loading="eager" decoding="async" fetchpriority="high">',
		esc_url( dewit_theme_get_logo_url() ),
		esc_attr( $class ),
		esc_attr( get_bloginfo( 'name' ) )
	);
}

function dewit_theme_prioritize_logo_image_attributes( array $attr, WP_Post $attachment, $size ): array {
	$custom_logo_id = (int) get_theme_mod( 'custom_logo' );

	if ( $custom_logo_id && (int) $attachment->ID === $custom_logo_id ) {
		$attr['loading']       = 'eager';
		$attr['decoding']      = 'async';
		$attr['fetchpriority'] = 'high';
		$attr['alt']           = get_bloginfo( 'name' );
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'dewit_theme_prioritize_logo_image_attributes', 20, 3 );

function dewit_theme_use_theme_logo_for_attachment( $image, int $attachment_id ) {
	$custom_logo_id = (int) get_theme_mod( 'custom_logo' );

	if ( $custom_logo_id && $attachment_id === $custom_logo_id ) {
		return array( dewit_theme_get_logo_url(), 1028, 245, false );
	}

	return $image;
}
add_filter( 'wp_get_attachment_image_src', 'dewit_theme_use_theme_logo_for_attachment', 20, 2 );

function dewit_theme_route_custom_logo_to_default_shop( string $html ): string {
	if ( '' === $html ) {
		return $html;
	}

	$logo_html = preg_replace(
		'/<img\b[^>]*>/',
		dewit_theme_get_logo_markup(),
		$html,
		1
	);
	$html      = is_string( $logo_html ) ? $logo_html : $html;

	return str_replace(
		'href="' . esc_url( home_url( '/' ) ) . '"',
		'href="' . esc_url( home_url( '/' ) ) . '"',
		$html
	);
}
add_filter( 'get_custom_logo', 'dewit_theme_route_custom_logo_to_default_shop' );

function dewit_theme_add_logo_img_priority_attributes( string $html ): string {
	if ( ! str_contains( $html, 'dewit-logo.svg' ) ) {
		return $html;
	}

	return (string) preg_replace_callback(
		'/<img\b(?=[^>]*dewit-logo\.svg)[^>]*>/i',
		static function ( array $matches ): string {
			$tag     = $matches[0];
			$closing = str_ends_with( $tag, '/>' ) ? ' />' : '>';
			$tag     = (string) preg_replace( '/\s*\/?>$/', '', $tag );

			foreach ( array(
				'loading'       => 'eager',
				'decoding'      => 'async',
				'fetchpriority' => 'high',
			) as $name => $value ) {
				if ( ! preg_match( '/\s' . preg_quote( $name, '/' ) . '\s*=/i', $tag ) ) {
					$tag .= sprintf( ' %s="%s"', $name, esc_attr( $value ) );
				}
			}

			return $tag . $closing;
		},
		$html
	);
}

function dewit_theme_start_logo_attribute_buffer(): void {
	if ( is_admin() || wp_doing_ajax() || ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) ) {
		return;
	}

	ob_start( 'dewit_theme_add_logo_img_priority_attributes' );
}
add_action( 'template_redirect', 'dewit_theme_start_logo_attribute_buffer', 0 );

function dewit_theme_is_product_tag_archive(): bool {
	return function_exists( 'is_tax' ) && is_tax( 'product_tag' );
}

function dewit_theme_get_product_tag_redirect_url(): string {
	return dewit_theme_get_default_shop_url();
}

function dewit_theme_has_category_context_query(): bool {
	foreach ( array_keys( $_GET ) as $key ) {
		if ( 'dewit_parent_cat' === $key || 'product_cat' === $key || str_starts_with( (string) $key, 'e-filter-' ) ) {
			return true;
		}
	}

	return false;
}

function dewit_theme_redirect_product_tag_archives(): void {
	if ( ! dewit_theme_is_product_tag_archive() ) {
		return;
	}

	wp_safe_redirect( dewit_theme_get_product_tag_redirect_url(), 301 );
	exit;
}
add_action( 'template_redirect', 'dewit_theme_redirect_product_tag_archives', 1 );

function dewit_theme_noindex_product_tag_archives( array $robots ): array {
	if ( ! dewit_theme_is_product_tag_archive() ) {
		return $robots;
	}

	unset( $robots['index'] );
	$robots['noindex'] = true;
	$robots['follow']  = true;

	return $robots;
}
add_filter( 'wp_robots', 'dewit_theme_noindex_product_tag_archives', 20 );

function dewit_theme_noindex_product_tag_archives_for_yoast( string $robots ): string {
	if ( ! dewit_theme_is_product_tag_archive() ) {
		return $robots;
	}

	return 'noindex, follow';
}
add_filter( 'wpseo_robots', 'dewit_theme_noindex_product_tag_archives_for_yoast', 20 );

function dewit_theme_exclude_product_tags_from_yoast_sitemap( bool $exclude, string $taxonomy ): bool {
	if ( 'product_tag' === $taxonomy ) {
		return true;
	}

	return $exclude;
}
add_filter( 'wpseo_sitemap_exclude_taxonomy', 'dewit_theme_exclude_product_tags_from_yoast_sitemap', 20, 2 );

function dewit_theme_disable_emoji_assets(): void {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}
add_action( 'init', 'dewit_theme_disable_emoji_assets' );

function dewit_theme_has_external_og_locale_provider(): bool {
	return defined( 'WPSEO_VERSION' ) || class_exists( 'WPSEO_Frontend' );
}

function dewit_theme_is_shop_seo_context(): bool {
	$is_product_page     = function_exists( 'is_product' ) && is_product();
	$is_product_taxonomy = function_exists( 'is_product_taxonomy' ) && is_product_taxonomy();
	$is_shop_page        = function_exists( 'is_shop' ) && is_shop();

	if ( is_admin() || is_search() || $is_product_page ) {
		return false;
	}

	foreach ( array_keys( $_GET ) as $key ) {
		if ( 'dewit_parent_cat' === $key || 'product_cat' === $key || str_starts_with( (string) $key, 'e-filter-' ) ) {
			return true;
		}
	}

	return $is_shop_page || $is_product_taxonomy || is_post_type_archive( 'product' ) || is_front_page() || is_home();
}

function dewit_theme_print_shop_meta_description(): void {
	if ( ! dewit_theme_is_shop_seo_context() ) {
		return;
	}

	printf(
		'<meta name="description" content="%s">' . "\n",
		esc_attr( dewit_theme_get_shop_meta_description() )
	);
}
add_action( 'wp_head', 'dewit_theme_print_shop_meta_description', 2 );

function dewit_theme_print_shop_social_meta(): void {
	if ( ! dewit_theme_is_shop_seo_context() ) {
		return;
	}

	$title       = 'De Wit Bouwmachines Assortiment';
	$description = dewit_theme_get_shop_meta_description();
	$image_url   = DEWIT_SHOP_SOCIAL_IMAGE_URL;
	$page_url    = home_url( add_query_arg( null, null ) );

	if ( ! dewit_theme_has_external_og_locale_provider() ) {
		printf( '<meta property="og:locale" content="%s">' . "\n", esc_attr( dewit_theme_get_social_locale() ) );
	}
	printf( '<meta property="og:type" content="website">' . "\n" );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $description ) );
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $page_url ) );
	printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image_url ) );
	printf( '<meta property="og:image:type" content="image/jpeg">' . "\n" );
	printf( '<meta name="twitter:card" content="summary_large_image">' . "\n" );
	printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $description ) );
	printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $image_url ) );
}
add_action( 'wp_head', 'dewit_theme_print_shop_social_meta', 3 );

function dewit_theme_filter_shop_document_title( array $parts ): array {
	if ( ! dewit_theme_is_shop_seo_context() ) {
		return $parts;
	}

	$parts['title'] = 'De Wit Bouwmachines Assortiment';
	unset( $parts['tagline'] );

	return $parts;
}
add_filter( 'document_title_parts', 'dewit_theme_filter_shop_document_title', 20 );

function dewit_theme_filter_shop_document_title_text( string $title ): string {
	if ( ! dewit_theme_is_shop_seo_context() ) {
		return $title;
	}

	return 'De Wit Bouwmachines Assortiment';
}
add_filter( 'pre_get_document_title', 'dewit_theme_filter_shop_document_title_text', 20 );

/**
 * Enqueue scripts and styles.
 */
if ( ! function_exists( 'dewit_theme_scripts' ) ) {
	function dewit_theme_scripts(): void {
	wp_enqueue_style(
		'dewit-theme-woocommerce-style',
		get_template_directory_uri() . '/assets/css/theme.css',
		array(),
		DEWIT_THEME_VERSION
	);

	wp_enqueue_script(
		'dewit-theme-woocommerce-script',
		get_template_directory_uri() . '/assets/js/theme.js',
		array(),
		DEWIT_THEME_VERSION,
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
}
add_action( 'wp_enqueue_scripts', 'dewit_theme_scripts' );

function dewit_theme_is_catalog_performance_context(): bool {
	if ( is_admin() || is_search() || is_preview() || is_customize_preview() ) {
		return false;
	}

	if ( function_exists( 'is_product' ) && is_product() ) {
		return false;
	}

	if ( function_exists( 'is_cart' ) && is_cart() ) {
		return false;
	}

	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		return false;
	}

	return is_front_page()
		|| is_home()
		|| dewit_theme_has_category_context_query()
		|| ( function_exists( 'is_shop' ) && is_shop() )
		|| is_post_type_archive( 'product' );
}

function dewit_theme_trim_catalog_frontend_assets(): void {
	if ( ! dewit_theme_is_catalog_performance_context() ) {
		return;
	}

	$style_handles = array(
		'wp-block-library',
		'wc-blocks-style',
		'wc-blocks-vendors-style',
		'woocommerce-general',
		'woocommerce-layout',
		'woocommerce-smallscreen',
	);

	foreach ( $style_handles as $handle ) {
		wp_dequeue_style( $handle );
	}

	$script_handles = array(
		'jquery-blockui',
		'js-cookie',
		'wc-add-to-cart',
		'woocommerce',
		'wc-cart-fragments',
		'wc-cart-fragments-js',
		'wc-order-attribution',
		'sourcebuster-js',
	);

	foreach ( $script_handles as $handle ) {
		wp_dequeue_script( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'dewit_theme_trim_catalog_frontend_assets', 100 );

function dewit_theme_dequeue_catalog_assets_by_source(): void {
	if ( ! dewit_theme_is_catalog_performance_context() ) {
		return;
	}

	global $wp_scripts, $wp_styles;

	$style_src_matches = array(
		'/woocommerce/assets/client/blocks/wc-blocks.css',
		'/woocommerce/assets/css/woocommerce',
	);

	if ( $wp_styles instanceof WP_Styles ) {
		foreach ( (array) $wp_styles->queue as $handle ) {
			$src = isset( $wp_styles->registered[ $handle ] ) ? (string) $wp_styles->registered[ $handle ]->src : '';

			foreach ( $style_src_matches as $match ) {
				if ( str_contains( $src, $match ) ) {
					wp_dequeue_style( $handle );
					break;
				}
			}
		}
	}

	$script_src_matches = array(
		'/woocommerce/assets/js/jquery-blockui/',
		'/woocommerce/assets/js/js-cookie/',
		'/woocommerce/assets/js/frontend/woocommerce',
		'/woocommerce/assets/js/sourcebuster/',
		'/woocommerce/assets/js/frontend/order-attribution',
	);

	if ( $wp_scripts instanceof WP_Scripts ) {
		foreach ( (array) $wp_scripts->queue as $handle ) {
			$src = isset( $wp_scripts->registered[ $handle ] ) ? (string) $wp_scripts->registered[ $handle ]->src : '';

			foreach ( $script_src_matches as $match ) {
				if ( str_contains( $src, $match ) ) {
					wp_dequeue_script( $handle );
					break;
				}
			}
		}
	}
}
add_action( 'wp_print_styles', 'dewit_theme_dequeue_catalog_assets_by_source', 100 );
add_action( 'wp_print_scripts', 'dewit_theme_dequeue_catalog_assets_by_source', 100 );

function dewit_theme_should_omit_catalog_asset_src( string $src ): bool {
	if ( '' === $src || ! dewit_theme_is_catalog_performance_context() ) {
		return false;
	}

	$matches = array(
		'/wp-includes/css/dist/block-library/style.min.css',
		'/woocommerce/assets/client/blocks/wc-blocks.css',
		'/woocommerce/assets/css/woocommerce',
		'/wp-includes/js/jquery/jquery-migrate.min.js',
		'/woocommerce/assets/js/jquery-blockui/',
		'/woocommerce/assets/js/js-cookie/',
		'/woocommerce/assets/js/frontend/woocommerce',
		'/woocommerce/assets/js/sourcebuster/',
		'/woocommerce/assets/js/frontend/order-attribution',
		'/google-site-kit/dist/assets/js/googlesitekit-events-provider-woocommerce',
	);

	foreach ( $matches as $match ) {
		if ( str_contains( $src, $match ) ) {
			return true;
		}
	}

	return false;
}

function dewit_theme_filter_catalog_style_tag( string $html, string $handle, string $href ): string {
	if ( dewit_theme_should_omit_catalog_asset_src( $href ) ) {
		return '';
	}

	return $html;
}
add_filter( 'style_loader_tag', 'dewit_theme_filter_catalog_style_tag', 20, 3 );

function dewit_theme_filter_catalog_script_tag( string $tag, string $handle, string $src ): string {
	if ( dewit_theme_should_omit_catalog_asset_src( $src ) ) {
		return '';
	}

	if ( 'dewit-theme-woocommerce-script' === $handle && ! str_contains( $tag, 'data-no-defer' ) ) {
		$tag = str_replace( '<script ', '<script data-no-defer="1" ', $tag );
	}

	return $tag;
}
add_filter( 'script_loader_tag', 'dewit_theme_filter_catalog_script_tag', 20, 3 );

/**
 * Keep the catalog renderer out of LiteSpeed's interaction-based JS delay.
 * The product grid is critical page content, not a secondary enhancement.
 *
 * @param array<int, string> $excludes Existing LiteSpeed exclusions.
 * @return array<int, string>
 */
function dewit_theme_litespeed_critical_js_excludes( array $excludes ): array {
	$excludes[] = '/assets/js/theme.js';
	$excludes[] = 'dewit-grouped-products-bootstrap';

	return array_values( array_unique( $excludes ) );
}
add_filter( 'litespeed_optm_js_defer_exc', 'dewit_theme_litespeed_critical_js_excludes' );
add_filter( 'litespeed_optm_gm_js_exc', 'dewit_theme_litespeed_critical_js_excludes' );

function dewit_theme_preload_logo_asset(): void {
	if ( is_admin() ) {
		return;
	}

	printf(
		'<link rel="preload" as="image" href="%s" type="image/svg+xml" fetchpriority="high">' . "\n",
		esc_url( dewit_theme_get_logo_url() )
	);
}
add_action( 'wp_head', 'dewit_theme_preload_logo_asset', 1 );

/**
 * Preload the first catalog product images so mobile LCP can start earlier.
 */
function dewit_theme_preload_shop_lcp_images(): void {
	if ( ! dewit_theme_is_catalog_ui_enabled() || is_admin() || is_search() || ( function_exists( 'is_product' ) && is_product() ) ) {
		return;
	}

	$parent_slug = dewit_theme_get_current_parent_category_slug();

	if ( '' === $parent_slug ) {
		return;
	}

	$groups          = dewit_theme_get_grouped_category_products( $parent_slug );
	$preloaded_count = 0;

	foreach ( $groups as $group ) {
		foreach ( $group['products'] as $product ) {
			if ( empty( $product['image'] ) ) {
				continue;
			}

			printf(
				'<link rel="preload" as="image" href="%1$s"%2$s imagesizes="%3$s" fetchpriority="high">' . "\n",
				esc_url( $product['image'] ),
				! empty( $product['image_srcset'] ) ? ' imagesrcset="' . esc_attr( $product['image_srcset'] ) . '"' : '',
				esc_attr( $product['image_sizes'] ?? '(max-width: 766px) calc((100vw - 40px) / 2), 220px' )
			);

			$preloaded_count++;

			if ( $preloaded_count >= 2 ) {
				return;
			}
		}
	}
}
add_action( 'wp_head', 'dewit_theme_preload_shop_lcp_images', 1 );

/**
 * Clean product text from ERP/WooCommerce before it is printed or sent to JavaScript.
 *
 * Some imported titles contain malformed numeric entities such as "&8221;"
 * instead of "&#8221;". Normalize those first, then decode valid entities.
 */
function dewit_theme_clean_product_text( string $text ): string {
	$text = wp_strip_all_tags( $text );
	$text = preg_replace( '/&(\d{2,6});/', '&#$1;', $text ) ?? $text;

	return html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, get_bloginfo( 'charset' ) ?: 'UTF-8' );
}

/**
 * Render numeric product codes as separate visual characters to prevent mobile tel: auto-linking.
 */
function dewit_theme_render_non_linked_text( string $text ): string {
	$text       = dewit_theme_clean_product_text( $text );
	$characters = preg_split( '//u', $text, -1, PREG_SPLIT_NO_EMPTY );

	if ( ! is_array( $characters ) || empty( $characters ) ) {
		return '';
	}

	$output = sprintf( '<span class="dewit-no-link-text" aria-label="%s">', esc_attr( $text ) );

	foreach ( $characters as $character ) {
		$output .= sprintf( '<i aria-hidden="true">%s</i>', esc_html( $character ) );
	}

	$output .= '</span>';

	return $output;
}

/**
 * Return the configured default parent category for the shop UI.
 */
function dewit_theme_get_default_parent_category_slug(): string {
	return sanitize_title( DEWIT_DEFAULT_PARENT_CATEGORY_SLUG );
}

/**
 * Return the requested or default parent category slug.
 */
function dewit_theme_get_current_parent_category_slug(): string {
	if ( isset( $_GET['dewit_parent_cat'] ) ) {
		return sanitize_title( wp_unslash( $_GET['dewit_parent_cat'] ) );
	}

	if ( function_exists( 'is_product_category' ) && is_product_category() ) {
		$queried_term = get_queried_object();

		if ( $queried_term instanceof WP_Term && 'product_cat' === $queried_term->taxonomy ) {
			return sanitize_title( $queried_term->slug );
		}
	}

	return dewit_theme_get_default_parent_category_slug();
}

/**
 * Build a shop URL for a selected parent category.
 */
function dewit_theme_get_parent_category_shop_url( string $slug ): string {
	return add_query_arg( 'dewit_parent_cat', sanitize_title( $slug ), home_url( '/' ) );
}

/**
 * Return product page back-link data with category context when possible.
 *
 * @return array{url:string,label:string}
 */
function dewit_theme_get_product_back_link( ?WC_Product $product = null ): array {
	$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/?post_type=product' );
	$back_link = array(
		'url'   => $shop_url,
		'label' => __( 'Terug naar assortiment', 'dewit-theme-woocommerce' ),
	);
	$parent_slug = isset( $_GET['dewit_parent_cat'] ) ? sanitize_title( wp_unslash( $_GET['dewit_parent_cat'] ) ) : '';

	if ( '' === $parent_slug && $product instanceof WC_Product ) {
		$terms = get_the_terms( $product->get_id(), 'product_cat' );

		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$term = reset( $terms );

			while ( $term instanceof WP_Term && $term->parent ) {
				$parent = get_term( $term->parent, 'product_cat' );

				if ( ! $parent instanceof WP_Term || is_wp_error( $parent ) ) {
					break;
				}

				$term = $parent;
			}

			if ( $term instanceof WP_Term ) {
				$parent_slug = $term->slug;
			}
		}
	}

	if ( '' !== $parent_slug ) {
		$parent_term = get_term_by( 'slug', $parent_slug, 'product_cat' );

		if ( $parent_term instanceof WP_Term ) {
			$back_link['url'] = dewit_theme_get_parent_category_shop_url( $parent_slug );
			$back_link['label'] = sprintf(
				/* translators: %s: product category name. */
				__( 'Terug naar %s', 'dewit-theme-woocommerce' ),
				dewit_theme_clean_product_text( $parent_term->name )
			);
		}
	}

	return $back_link;
}

/**
 * Return the first non-empty product meta value from a list of possible ERP keys.
 *
 * @param array<int, string> $keys Product meta keys.
 */
function dewit_theme_get_first_product_meta_value( int $product_id, array $keys ): string {
	foreach ( $keys as $key ) {
		$value = get_post_meta( $product_id, $key, true );

		if ( is_scalar( $value ) && '' !== trim( (string) $value ) ) {
			return trim( (string) $value );
		}
	}

	return '';
}

/**
 * Return optional ERP-driven product resource links.
 *
 * @return array<int, array{label:string,url:string,type:string}>
 */
function dewit_theme_get_product_resources( ?WC_Product $product = null ): array {
	if ( ! $product instanceof WC_Product ) {
		return array();
	}

	$product_id = $product->get_id();
	$resources = array(
		array(
			'label' => __( 'Video', 'dewit-theme-woocommerce' ),
			'type'  => 'video',
			'url'   => dewit_theme_get_first_product_meta_value( $product_id, array(
				'WB-videourl',
				'WB.videourl',
				'videourl',
				'_dewit_video_url',
			) ),
		),
		array(
			'label' => __( 'Handleiding', 'dewit-theme-woocommerce' ),
			'type'  => 'manual',
			'url'   => dewit_theme_get_first_product_meta_value( $product_id, array(
				'WB-handleidingurl',
				'WB.handleidingurl',
				'handleidingurl',
				'_dewit_manual_url',
			) ),
		),
		array(
			'label' => __( 'Tech specs', 'dewit-theme-woocommerce' ),
			'type'  => 'specs',
			'url'   => dewit_theme_get_first_product_meta_value( $product_id, array(
				'WB-techspecsurl',
				'WB.techspecsurl',
				'techspecsurl',
				'_dewit_tech_specs_url',
			) ),
		),
	);

	return array_values( array_filter(
		$resources,
		static function ( array $resource ): bool {
			return '' !== $resource['url'] && false !== filter_var( $resource['url'], FILTER_VALIDATE_URL );
		}
	) );
}

/**
 * Add WooCommerce body classes.
 *
 * @param array<string> $classes Existing classes.
 * @return array<string>
 */
function dewit_theme_body_classes( array $classes ): array {
	if ( class_exists( 'WooCommerce' ) ) {
		$classes[] = 'has-woocommerce';
	}

	if ( dewit_theme_is_catalog_ui_enabled() && '' !== dewit_theme_get_current_parent_category_slug() && ! ( function_exists( 'is_product' ) && is_product() ) ) {
		$classes[] = 'dewit-shop-grouped';
	}

	return $classes;
}
add_filter( 'body_class', 'dewit_theme_body_classes' );

/**
 * Legacy sidebar renderer retained for backwards compatibility.
 *
 * The Elementor-free catalog does not register or call this renderer.
 */
function dewit_theme_print_sidebar_category_fallback(): void {
	if ( ! dewit_theme_is_catalog_ui_enabled() ) {
		return;
	}

	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return;
	}

	$terms = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'number'     => 100,
	) );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return;
	}

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
	?>
	<script>
	(function () {
		const categories = <?php echo wp_json_encode( $categories ); ?>;
		window.dewitProductCategories = Array.isArray(window.dewitProductCategories) && window.dewitProductCategories.length
			? window.dewitProductCategories
			: categories;

		function isVisibleCategory(category) {
			return category && category.slug !== 'alle' && String(category.name || '').trim().toLowerCase() !== 'alle';
		}

		function hasProducts(category, childrenByParent) {
			const children = childrenByParent.get(category.id) || [];
			return Number(category.count) > 0 || children.some(function (child) {
				return hasProducts(child, childrenByParent);
			});
		}

		function getUrl(slug) {
			const homeUrl = (window.dewitTheme && window.dewitTheme.homeUrl) || '/';
			const url = new URL(homeUrl, window.location.origin + '/');

			Array.from(url.searchParams.keys()).forEach(function (key) {
				if (key.indexOf('e-filter-') === 0 || key === 'product_cat' || key === 'dewit_parent_cat') {
					url.searchParams.delete(key);
				}
			});

			url.searchParams.delete('product-page');
			url.searchParams.set('dewit_parent_cat', slug);
			return url.toString();
		}

		function getCategorySectionId(slug) {
			return 'dewit-cat-' + String(slug || '').replace(/[^a-z0-9_-]/gi, '-');
		}

		function scrollToCategorySection(slug) {
			const section = document.getElementById(getCategorySectionId(slug));

			if (!section) {
				return;
			}

			section.scrollIntoView({
				block: 'start',
				behavior: 'smooth',
			});
		}

		function appendCategoryTriggerContent(trigger, category) {
			const label = document.createElement('span');

			label.className = 'dewit-category-label';
			label.textContent = category.name || '';

			trigger.appendChild(label);
		}

		function render() {
			const filter = document.querySelector('#catalog-sidebar .dewit-catalog-category-filter');

			if (!filter || !Array.isArray(categories) || !categories.length) {
				return;
			}

			const childrenByParent = new Map();

			categories.filter(isVisibleCategory).forEach(function (category) {
				if (!childrenByParent.has(category.parent)) {
					childrenByParent.set(category.parent, []);
				}

				childrenByParent.get(category.parent).push(category);
			});

			const parents = (childrenByParent.get(0) || [])
				.filter(function (category) {
					return hasProducts(category, childrenByParent);
				})
				.sort(function (a, b) {
					return a.name.localeCompare(b.name, 'nl');
				});

			if (!parents.length) {
				return;
			}

			const activeParent = new URL(window.location.href).searchParams.get('dewit_parent_cat') || '';
			filter.innerHTML = '';

			const allLink = document.createElement('a');
			allLink.className = 'dewit-category-trigger dewit-category-trigger--all';
			allLink.href = getUrl('');
			allLink.setAttribute('aria-pressed', activeParent ? 'false' : 'true');
			appendCategoryTriggerContent(allLink, { name: 'Alle' });
			filter.appendChild(allLink);

			parents.forEach(function (category) {
				const group = document.createElement('div');
				const link = document.createElement('a');
				const children = (childrenByParent.get(category.id) || [])
					.filter(function (child) {
						return hasProducts(child, childrenByParent);
					})
					.sort(function (a, b) {
						return a.name.localeCompare(b.name, 'nl');
					});

				group.className = 'dewit-category-group';
				group.classList.toggle('is-open', activeParent === category.slug);
				link.className = 'dewit-category-trigger';
				link.href = getUrl(category.slug);
				link.setAttribute('aria-pressed', activeParent === category.slug ? 'true' : 'false');
				appendCategoryTriggerContent(link, category);
				group.appendChild(link);

				if (children.length) {
					const panel = document.createElement('div');
					panel.className = 'dewit-category-panel';
					panel.hidden = activeParent !== category.slug;

					children.forEach(function (child) {
						const childLink = document.createElement('a');
						childLink.className = 'dewit-category-child';
						childLink.href = getUrl(category.slug) + '#' + getCategorySectionId(child.slug);
						childLink.setAttribute('data-filter', child.slug);
						childLink.textContent = child.name || '';
						childLink.addEventListener('click', function (event) {
							event.preventDefault();
							event.stopPropagation();

							if (document.body.classList.contains('single-product') || activeParent !== category.slug) {
								window.location.href = childLink.href;
								return;
							}

							document.querySelectorAll('.dewit-category-child[aria-pressed="true"]').forEach(function (item) {
								item.setAttribute('aria-pressed', 'false');
							});
							childLink.setAttribute('aria-pressed', 'true');
							scrollToCategorySection(child.slug);
						});
						panel.appendChild(childLink);
					});

					group.appendChild(panel);
				}

				filter.appendChild(group);
			});

			filter.classList.add('dewit-category-dropdowns-ready');
			window.dispatchEvent(new CustomEvent('dewit/categories-ready'));
		}

		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', render);
		} else {
			render();
		}

		window.addEventListener('load', render);
	}());
	</script>
	<?php
}
/**
 * Customize WooCommerce wrappers to match the theme layout.
 */
function dewit_theme_woocommerce_wrapper_start(): void {
	echo '<main id="primary" class="site-main site-main--shop"><div class="container shop-layout">';
}

function dewit_theme_woocommerce_wrapper_end(): void {
	echo '</div></main>';
}

remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
add_action( 'woocommerce_before_main_content', 'dewit_theme_woocommerce_wrapper_start', 10 );
add_action( 'woocommerce_after_main_content', 'dewit_theme_woocommerce_wrapper_end', 10 );

/**
 * Adjust WooCommerce product grid defaults.
 */
function dewit_theme_loop_columns(): int {
	return 3;
}
add_filter( 'loop_shop_columns', 'dewit_theme_loop_columns' );

function dewit_theme_products_per_page(): int {
	return 12;
}
add_filter( 'loop_shop_per_page', 'dewit_theme_products_per_page' );

/**
 * Product searches should always be global, even if a stale frontend still submits category filters.
 */
function dewit_theme_strip_category_filters_from_product_search(): void {
	if ( is_admin() || ! is_search() || 'product' !== get_query_var( 'post_type' ) ) {
		return;
	}

	$has_filter = false;
	$url        = home_url( '/' );
	$params     = array();

	foreach ( $_GET as $key => $value ) {
		$clean_key = sanitize_key( wp_unslash( $key ) );

		if ( 'product_cat' === $clean_key || str_starts_with( $clean_key, 'e-filter-' ) ) {
			$has_filter = true;
			continue;
		}

		if ( ! is_scalar( $value ) ) {
			continue;
		}

		$params[ $clean_key ] = sanitize_text_field( wp_unslash( $value ) );
	}

	if ( ! $has_filter ) {
		return;
	}

	wp_safe_redirect( add_query_arg( $params, $url ) );
	exit;
}
add_action( 'template_redirect', 'dewit_theme_strip_category_filters_from_product_search' );

/**
 * Match product searches against title, content, SKU, product categories, and product tags.
 *
 * @param string   $search Existing SQL search fragment.
 * @param WP_Query $query  Current query.
 * @return string
 */
function dewit_theme_expand_product_search_sql( string $search, WP_Query $query ): string {
	if ( is_admin() || ! $query->is_search() ) {
		return $search;
	}

	$post_type = $query->get( 'post_type' );

	if ( 'product' !== $post_type && ! ( is_array( $post_type ) && in_array( 'product', $post_type, true ) ) ) {
		return $search;
	}

	$term = trim( (string) $query->get( 's' ) );

	if ( '' === $term ) {
		return $search;
	}

	global $wpdb;

	$like        = '%' . $wpdb->esc_like( $term ) . '%';
	$product_ids = dewit_theme_get_product_ids_for_term_matches( $term, 80 );
	$id_sql      = '';

	if ( ! empty( $product_ids ) ) {
		$id_sql = ' OR ' . $wpdb->posts . '.ID IN (' . implode( ',', array_map( 'absint', $product_ids ) ) . ')';
	}

	return $wpdb->prepare(
		" AND (
			({$wpdb->posts}.post_title LIKE %s)
			OR ({$wpdb->posts}.post_excerpt LIKE %s)
			OR ({$wpdb->posts}.post_content LIKE %s)
			OR EXISTS (
				SELECT 1
				FROM {$wpdb->postmeta} dewit_sku_search
				WHERE dewit_sku_search.post_id = {$wpdb->posts}.ID
				AND dewit_sku_search.meta_key = '_sku'
				AND dewit_sku_search.meta_value LIKE %s
			)
			{$id_sql}
		)",
		$like,
		$like,
		$like,
		$like
	);
}
add_filter( 'posts_search', 'dewit_theme_expand_product_search_sql', 10, 2 );

/**
 * Get product IDs connected to matching product categories or tags.
 *
 * @param string $term Search term.
 * @param int    $limit Max product IDs.
 * @return array<int>
 */
function dewit_theme_get_product_ids_for_term_matches( string $term, int $limit = 20 ): array {
	$taxonomies = array_filter( array( 'product_cat', 'product_tag' ), 'taxonomy_exists' );

	if ( empty( $taxonomies ) ) {
		return array();
	}

	$terms = get_terms( array(
		'taxonomy'   => $taxonomies,
		'hide_empty' => true,
		'number'     => 20,
		'search'     => $term,
	) );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	$terms_by_taxonomy = array();

	foreach ( $terms as $matched_term ) {
		if ( $matched_term instanceof WP_Term ) {
			$terms_by_taxonomy[ $matched_term->taxonomy ][] = absint( $matched_term->term_id );
		}
	}

	if ( empty( $terms_by_taxonomy ) ) {
		return array();
	}

	$tax_query = array( 'relation' => 'OR' );

	foreach ( $terms_by_taxonomy as $taxonomy => $term_ids ) {
		$tax_query[] = array(
			'taxonomy' => $taxonomy,
			'field'    => 'term_id',
			'terms'    => array_values( array_unique( $term_ids ) ),
			'operator' => 'IN',
		);
	}

	$products = get_posts( array(
		'post_type'              => 'product',
		'post_status'            => 'publish',
		'fields'                 => 'ids',
		'posts_per_page'         => $limit,
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'tax_query'              => $tax_query,
	) );

	return array_map( 'absint', $products );
}

/**
 * Return live product search suggestions for the custom toolbar.
 */
function dewit_theme_ajax_product_search(): void {
	$term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';

	if ( strlen( $term ) < 2 ) {
		wp_send_json_success( array() );
	}

	$product_ids = array();

	$title_query = new WP_Query( array(
		'post_type'              => 'product',
		'post_status'            => 'publish',
		's'                      => $term,
		'fields'                 => 'ids',
		'posts_per_page'         => 8,
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	) );

	$product_ids = array_merge( $product_ids, array_map( 'absint', $title_query->posts ) );

	$sku_products = get_posts( array(
		'post_type'              => 'product',
		'post_status'            => 'publish',
		'fields'                 => 'ids',
		'posts_per_page'         => 8,
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
		'meta_query'             => array(
			array(
				'key'     => '_sku',
				'value'   => $term,
				'compare' => 'LIKE',
			),
		),
	) );

	$product_ids = array_merge( $product_ids, array_map( 'absint', $sku_products ) );
	$product_ids = array_merge( $product_ids, dewit_theme_get_product_ids_for_term_matches( $term, 8 ) );
	$product_ids = array_values( array_unique( array_filter( $product_ids ) ) );

	if ( empty( $product_ids ) ) {
		wp_send_json_success( array() );
	}

	$args = array(
		'post_type'              => 'product',
		'post_status'            => 'publish',
		'post__in'               => $product_ids,
		'orderby'                => 'post__in',
		'posts_per_page'         => 6,
		'no_found_rows'          => true,
		'update_post_term_cache' => true,
	);

	$results = array();
	$products = new WP_Query( $args );

	foreach ( $products->posts as $post_id ) {
		$product = wc_get_product( $post_id );

		if ( ! $product instanceof WC_Product ) {
			continue;
		}

		$results[] = array(
			'title'      => dewit_theme_clean_product_text( get_the_title( $post_id ) ),
			'url'        => get_permalink( $post_id ),
			'sku'        => dewit_theme_clean_product_text( $product->get_sku() ),
			'image'      => get_the_post_thumbnail_url( $post_id, 'woocommerce_thumbnail' ),
			'categories' => dewit_theme_clean_product_text( wp_strip_all_tags( wc_get_product_category_list( $post_id, ', ' ) ) ),
		);
	}

	wp_send_json_success( $results );
}
add_action( 'wp_ajax_dewit_product_search', 'dewit_theme_ajax_product_search' );
add_action( 'wp_ajax_nopriv_dewit_product_search', 'dewit_theme_ajax_product_search' );

/**
 * Get products grouped by direct child categories for a selected parent category.
 *
 * @param string $slug Parent category slug.
 * @return array<int, array{name:string, slug:string, products:array<int, array{id:int, title:string, url:string, sku:string, image:string|false, image_width:int, image_height:int}>}>
 */
function dewit_theme_get_grouped_category_products( string $slug ): array {
	static $grouped_products_cache = array();

	if ( '' === $slug || ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}

	if ( isset( $grouped_products_cache[ $slug ] ) ) {
		return $grouped_products_cache[ $slug ];
	}

	$parent = get_term_by( 'slug', $slug, 'product_cat' );

	if ( ! $parent instanceof WP_Term ) {
		return array();
	}

	$children = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'parent'     => $parent->term_id,
		'orderby'    => 'name',
		'order'      => 'ASC',
	) );

	if ( is_wp_error( $children ) || empty( $children ) ) {
		$children = array( $parent );
	}

	$groups = array();

	foreach ( $children as $child ) {
		if ( ! $child instanceof WP_Term ) {
			continue;
		}

		$products = new WP_Query( array(
			'post_type'              => 'product',
			'post_status'            => 'publish',
			'fields'                 => 'ids',
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => $child->term_id,
				),
			),
			'orderby'                => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
		) );

		if ( ! $products->have_posts() ) {
			continue;
		}

		$items = array();

		foreach ( $products->posts as $post_id ) {
			$product = wc_get_product( $post_id );

			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			$image_id       = get_post_thumbnail_id( $post_id );
			$image_metadata = $image_id ? wp_get_attachment_metadata( $image_id ) : false;
			$image_size     = is_array( $image_metadata ) && ! empty( $image_metadata['sizes']['dewit_catalog_card'] )
				? 'dewit_catalog_card'
				: 'medium_large';
			$image_data     = $image_id ? wp_get_attachment_image_src( $image_id, $image_size ) : false;
			$image_srcset   = $image_id ? wp_get_attachment_image_srcset( $image_id, $image_size ) : false;

			$items[] = array(
				'id'           => absint( $post_id ),
				'title'        => dewit_theme_clean_product_text( get_the_title( $post_id ) ),
				'url'          => add_query_arg( 'dewit_parent_cat', $parent->slug, get_permalink( $post_id ) ),
				'sku'          => dewit_theme_clean_product_text( $product->get_sku() ),
				'image'        => $image_data ? $image_data[0] : false,
				'image_width'  => $image_data ? absint( $image_data[1] ) : 300,
				'image_height' => $image_data ? absint( $image_data[2] ) : 300,
				'image_srcset' => $image_srcset ?: '',
				'image_sizes'  => '(max-width: 766px) calc((100vw - 40px) / 2), 220px',
			);
		}

		if ( empty( $items ) ) {
			continue;
		}

		$groups[] = array(
			'name'     => dewit_theme_clean_product_text( $child->name ),
			'slug'     => $child->slug,
			'products' => $items,
		);
	}

	$grouped_products_cache[ $slug ] = $groups;

	return $groups;
}

/**
 * Render grouped category products markup.
 *
 * @param string $slug Parent category slug.
 * @return string
 */
function dewit_theme_render_grouped_category_products_html( string $slug ): string {
	$groups = dewit_theme_get_grouped_category_products( $slug );

	ob_start();
	?>
	<div class="dewit-grouped-products is-visible" style="display: grid;">
		<?php if ( empty( $groups ) ) : ?>
			<div class="dewit-grouped-products__status"><?php esc_html_e( 'Geen producten gevonden', 'dewit-theme-woocommerce' ); ?></div>
		<?php else : ?>
			<?php $product_index = 0; ?>
			<?php foreach ( $groups as $group ) : ?>
				<section class="dewit-grouped-products__section" id="<?php echo esc_attr( 'dewit-cat-' . sanitize_html_class( $group['slug'] ) ); ?>" data-category-slug="<?php echo esc_attr( $group['slug'] ); ?>">
					<?php if ( $group['slug'] !== $slug ) : ?><h2 class="dewit-grouped-products__heading"><?php echo esc_html( $group['name'] ); ?></h2><?php endif; ?>
					<div class="dewit-grouped-products__grid<?php echo 1 === count( $group['products'] ) ? ' is-single' : ''; ?>">
						<?php foreach ( $group['products'] as $product ) : ?>
							<?php
							$is_priority_image = $product_index < 2;
							$is_lcp_candidate  = $product_index < 2;
							$product_index++;
							?>
							<?php dewit_theme_render_catalog_product_card( $product, $product_index - 1, $is_lcp_candidate ); ?>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
	<?php
	return trim( ob_get_clean() );
}

/**
 * Return the most relevant product category term for product-page option rows.
 */
function dewit_theme_get_product_option_category( WC_Product $product ): ?WP_Term {
	$terms = get_the_terms( $product->get_id(), 'product_cat' );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return null;
	}

	$parent_slug = isset( $_GET['dewit_parent_cat'] ) ? sanitize_title( wp_unslash( $_GET['dewit_parent_cat'] ) ) : '';
	$parent_term = '' !== $parent_slug ? get_term_by( 'slug', $parent_slug, 'product_cat' ) : null;
	$candidates  = array();

	foreach ( $terms as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}

		if ( $parent_term instanceof WP_Term ) {
			$ancestors = get_ancestors( $term->term_id, 'product_cat' );

			if ( $term->term_id !== $parent_term->term_id && ! in_array( $parent_term->term_id, $ancestors, true ) ) {
				continue;
			}
		}

		$candidates[] = $term;
	}

	if ( empty( $candidates ) ) {
		$candidates = $terms;
	}

	usort(
		$candidates,
		static function ( WP_Term $a, WP_Term $b ): int {
			return count( get_ancestors( $b->term_id, 'product_cat' ) ) <=> count( get_ancestors( $a->term_id, 'product_cat' ) );
		}
	);

	$category = reset( $candidates );

	return $category instanceof WP_Term ? $category : null;
}

/**
 * Return all products in the same option category as the current product.
 *
 * @return array{category:WP_Term|null, products:array<int, array{id:int,title:string,url:string,sku:string,image:string|false,image_width:int,image_height:int,is_current:bool}>}
 */
function dewit_theme_get_product_category_options( WC_Product $product ): array {
	$category = dewit_theme_get_product_option_category( $product );

	if ( ! $category instanceof WP_Term ) {
		return array(
			'category' => null,
			'products' => array(),
		);
	}

	$parent_slug = isset( $_GET['dewit_parent_cat'] ) ? sanitize_title( wp_unslash( $_GET['dewit_parent_cat'] ) ) : '';
	$query       = new WP_Query( array(
		'post_type'              => 'product',
		'post_status'            => 'publish',
		'fields'                 => 'ids',
		'posts_per_page'         => -1,
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
		'tax_query'              => array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => $category->term_id,
			),
		),
		'orderby'                => array(
			'menu_order' => 'ASC',
			'title'      => 'ASC',
		),
	) );
	$products    = array();

	foreach ( $query->posts as $post_id ) {
		$option_product = wc_get_product( $post_id );

		if ( ! $option_product instanceof WC_Product ) {
			continue;
		}

		$image_id   = get_post_thumbnail_id( $post_id );
		$image_data = $image_id ? wp_get_attachment_image_src( $image_id, 'woocommerce_thumbnail' ) : false;
		$image_data = $image_data ? array(
			'src'    => $image_data[0],
			'width'  => absint( $image_data[1] ),
			'height' => absint( $image_data[2] ),
		) : dewit_theme_get_placeholder_image_data();
		$url        = get_permalink( $post_id );

		if ( '' !== $parent_slug ) {
			$url = add_query_arg( 'dewit_parent_cat', $parent_slug, $url );
		}

		$products[] = array(
			'id'           => absint( $post_id ),
			'title'        => dewit_theme_clean_product_text( get_the_title( $post_id ) ),
			'url'          => $url,
			'sku'          => dewit_theme_clean_product_text( $option_product->get_sku() ),
			'image'        => $image_data['src'],
			'image_width'  => $image_data['width'],
			'image_height' => $image_data['height'],
			'is_current'   => absint( $post_id ) === $product->get_id(),
		);
	}

	return array(
		'category' => $category,
		'products' => $products,
	);
}

/**
 * Render product-page options from the active product subcategory.
 */
function dewit_theme_render_product_category_options_table( WC_Product $product ): string {
	$options = dewit_theme_get_product_category_options( $product );

	if ( empty( $options['products'] ) ) {
		return '';
	}

	ob_start();
	?>
	<section class="dewit-product-related-options">
		<div class="dewit-product-related-options__header">
			<h2><?php esc_html_e( 'Meer uit deze categorie', 'dewit-theme-woocommerce' ); ?></h2>
			<?php if ( $options['category'] instanceof WP_Term ) : ?>
				<p><?php echo esc_html( dewit_theme_clean_product_text( $options['category']->name ) ); ?></p>
			<?php endif; ?>
		</div>
		<div class="dewit-grouped-products__grid">
			<?php foreach ( $options['products'] as $index => $option ) : ?>
				<a class="dewit-grouped-product-card<?php echo $option['is_current'] ? ' is-current-product' : ''; ?>" style="--dewit-card-index: <?php echo esc_attr( min( $index, 24 ) ); ?>;" href="<?php echo esc_url( $option['url'] ); ?>"<?php echo $option['is_current'] ? ' aria-current="page"' : ''; ?>>
					<span class="dewit-grouped-product-card__image">
						<?php if ( $option['image'] ) : ?>
							<img
								src="<?php echo esc_url( $option['image'] ); ?>"
								alt=""
								width="<?php echo esc_attr( $option['image_width'] ); ?>"
								height="<?php echo esc_attr( $option['image_height'] ); ?>"
								loading="lazy"
								decoding="async"
							>
						<?php endif; ?>
					</span>
					<span class="dewit-grouped-product-card__body">
						<span class="dewit-grouped-product-card__sku"><?php echo dewit_theme_render_non_linked_text( $option['sku'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="dewit-grouped-product-card__title"><?php echo esc_html( $option['title'] ); ?></span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
	return trim( ob_get_clean() );
}

/**
 * Return products grouped by direct child categories for a selected parent category.
 */
function dewit_theme_ajax_grouped_category_products(): void {
	$slug = isset( $_GET['category'] ) ? sanitize_title( wp_unslash( $_GET['category'] ) ) : '';
	wp_send_json_success( dewit_theme_get_grouped_category_products( $slug ) );
}
add_action( 'wp_ajax_dewit_category_grouped_products', 'dewit_theme_ajax_grouped_category_products' );
add_action( 'wp_ajax_nopriv_dewit_category_grouped_products', 'dewit_theme_ajax_grouped_category_products' );

/**
 * Get a small product preview for a sidebar parent category.
 */
function dewit_theme_ajax_category_preview_products(): void {
	$slug = isset( $_GET['category'] ) ? sanitize_title( wp_unslash( $_GET['category'] ) ) : '';

	if ( '' === $slug || ! taxonomy_exists( 'product_cat' ) ) {
		wp_send_json_success( array() );
	}

	$term = get_term_by( 'slug', $slug, 'product_cat' );

	if ( ! $term instanceof WP_Term ) {
		wp_send_json_success( array() );
	}

	$products = new WP_Query( array(
		'post_type'              => 'product',
		'post_status'            => 'publish',
		'fields'                 => 'ids',
		'posts_per_page'         => 3,
		'no_found_rows'          => true,
		'update_post_meta_cache' => true,
		'update_post_term_cache' => false,
		'tax_query'              => array(
			array(
				'taxonomy'         => 'product_cat',
				'field'            => 'term_id',
				'terms'            => $term->term_id,
				'include_children' => true,
			),
		),
		'orderby'                => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
	) );

	$items = array();

	foreach ( $products->posts as $post_id ) {
		$product = wc_get_product( $post_id );

		if ( ! $product instanceof WC_Product ) {
			continue;
		}

		$items[] = array(
			'title' => dewit_theme_clean_product_text( get_the_title( $post_id ) ),
			'url'   => add_query_arg( 'dewit_parent_cat', $term->slug, get_permalink( $post_id ) ),
			'sku'   => dewit_theme_clean_product_text( $product->get_sku() ),
			'image' => get_the_post_thumbnail_url( $post_id, 'woocommerce_thumbnail' ),
		);
	}

	wp_send_json_success( $items );
}
add_action( 'wp_ajax_dewit_category_preview_products', 'dewit_theme_ajax_category_preview_products' );
add_action( 'wp_ajax_nopriv_dewit_category_preview_products', 'dewit_theme_ajax_category_preview_products' );

/**
 * Temporarily disable purchasing while the catalog is being prepared.
 */
function dewit_theme_disable_product_purchases(): bool {
	return false;
}
add_filter( 'woocommerce_is_purchasable', 'dewit_theme_disable_product_purchases' );

/**
 * Keep the product description tab clean and Dutch.
 */
function dewit_theme_filter_product_tabs( array $tabs ): array {
	unset( $tabs['description'] );

	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'dewit_theme_filter_product_tabs', 20 );

function dewit_theme_remove_product_description_heading(): string {
	return '';
}
add_filter( 'woocommerce_product_description_heading', 'dewit_theme_remove_product_description_heading', 20 );

function dewit_theme_translate_product_description_label( string $translated, string $text, string $domain ): string {
	if ( ! function_exists( 'is_product' ) || ! is_product() || 'Description' !== $text ) {
		return $translated;
	}

	return __( 'Omschrijving', 'dewit-theme-woocommerce' );
}
add_filter( 'gettext', 'dewit_theme_translate_product_description_label', 20, 3 );

/**
 * Use Dutch copy for the related products section.
 */
function dewit_theme_related_products_heading(): string {
	return __( 'Meer uit deze categorie', 'dewit-theme-woocommerce' );
}
add_filter( 'woocommerce_product_related_products_heading', 'dewit_theme_related_products_heading' );
