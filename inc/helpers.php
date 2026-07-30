<?php
/**
 * Shared theme and WooCommerce data helpers.
 *
 * @package DewitTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return WooCommerce product category image data when a thumbnail is configured.
 *
 * @return array{src:string,width:int,height:int}|null
 */
function dewit_theme_get_product_category_image_data( int $term_id ): ?array {
	$thumbnail_id = absint( get_term_meta( $term_id, 'thumbnail_id', true ) );
	$image_data   = $thumbnail_id ? wp_get_attachment_image_src( $thumbnail_id, 'woocommerce_thumbnail' ) : false;

	if ( ! $image_data ) {
		return null;
	}

	return array(
		'src'    => $image_data[0],
		'width'  => absint( $image_data[1] ),
		'height' => absint( $image_data[2] ),
	);
}

/**
 * Return the WooCommerce placeholder image data for cards without an image.
 *
 * @return array{src:string,width:int,height:int}
 */
function dewit_theme_get_placeholder_image_data(): array {
	$src = function_exists( 'wc_placeholder_img_src' ) ? wc_placeholder_img_src( 'woocommerce_thumbnail' ) : '';

	return array(
		'src'    => $src,
		'width'  => 300,
		'height' => 300,
	);
}
