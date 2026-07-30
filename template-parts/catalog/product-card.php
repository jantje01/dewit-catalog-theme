<?php
/** Grouped catalog product card. */

defined( 'ABSPATH' ) || exit;

$product  = isset( $args['product'] ) && is_array( $args['product'] ) ? $args['product'] : array();
$index    = isset( $args['index'] ) ? absint( $args['index'] ) : 0;
$priority = ! empty( $args['priority'] );
?>
<a class="dewit-grouped-product-card" style="--dewit-card-index: <?php echo esc_attr( min( $index, 24 ) ); ?>;" href="<?php echo esc_url( (string) ( $product['url'] ?? '' ) ); ?>">
	<span class="dewit-grouped-product-card__image">
		<?php if ( ! empty( $product['image'] ) ) : ?>
			<img
				src="<?php echo esc_url( (string) $product['image'] ); ?>"
				alt=""
				width="<?php echo esc_attr( absint( $product['image_width'] ?? 300 ) ); ?>"
				height="<?php echo esc_attr( absint( $product['image_height'] ?? 300 ) ); ?>"
				<?php if ( ! empty( $product['image_srcset'] ) ) : ?>srcset="<?php echo esc_attr( (string) $product['image_srcset'] ); ?>"<?php endif; ?>
				sizes="<?php echo esc_attr( (string) ( $product['image_sizes'] ?? '(max-width: 766px) calc((100vw - 40px) / 2), 220px' ) ); ?>"
				loading="<?php echo esc_attr( $priority ? 'eager' : 'lazy' ); ?>"
				decoding="async"
				fetchpriority="<?php echo esc_attr( $priority ? 'high' : 'low' ); ?>"
			>
		<?php endif; ?>
	</span>
	<span class="dewit-grouped-product-card__body">
		<span class="dewit-grouped-product-card__sku"><?php echo dewit_theme_render_non_linked_text( (string) ( $product['sku'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<span class="dewit-grouped-product-card__title"><?php echo esc_html( (string) ( $product['title'] ?? '' ) ); ?></span>
	</span>
</a>
