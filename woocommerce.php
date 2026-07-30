<?php
/**
 * WooCommerce template bridge.
 *
 * @package DewitTheme
 */

if ( is_product() ) {
	get_header();
	get_template_part( 'template-parts/content', 'product-single' );
	get_footer();
	return;
}

if ( is_shop() || is_post_type_archive( 'product' ) || is_product_category() || is_product_tag() ) {
	get_template_part( 'front-page' );
} else {
	get_header();
	woocommerce_content();
	get_footer();
}
