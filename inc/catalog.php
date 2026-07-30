<?php
/**
 * Catalog rendering components.
 *
 * @package DewitTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render one grouped catalog product card through a template part.
 *
 * @param array<string, mixed> $product Product view model.
 * @param int                  $index Zero-based card index.
 * @param bool                 $priority Whether the image is eager/high priority.
 */
function dewit_theme_render_catalog_product_card( array $product, int $index, bool $priority = false ): void {
	get_template_part(
		'template-parts/catalog/product-card',
		null,
		array(
			'product'  => $product,
			'index'    => $index,
			'priority' => $priority,
		)
	);
}

/**
 * Render the catalog-driven mega menu used when no WordPress menu is assigned.
 *
 * The top-level product categories become the menu columns and their children
 * are shown in a full-width panel, so the navigation stays data-driven.
 */
function dewit_theme_render_catalog_mega_menu(): void {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return;
	}

	$parents = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( is_wp_error( $parents ) || ! $parents ) {
		return;
	}
	?>
	<ul id="primary-menu" class="dewit-mega-menu__root">
		<li class="menu-item menu-item-has-children dewit-mega-menu__products">
			<button class="dewit-mega-menu__trigger" type="button" aria-expanded="false" aria-controls="dewit-mega-menu-panel">
				<?php esc_html_e( 'Producten', 'dewit-catalog-theme' ); ?>
			</button>
			<div id="dewit-mega-menu-panel" class="dewit-mega-menu__panel">
				<div class="dewit-mega-menu__panel-inner">
					<div class="dewit-mega-menu__panel-heading">
						<span class="dewit-mega-menu__kicker"><?php esc_html_e( 'Assortiment', 'dewit-catalog-theme' ); ?></span>
						<strong><?php esc_html_e( 'Alles voor de bouw', 'dewit-catalog-theme' ); ?></strong>
						<a href="<?php echo esc_url( dewit_theme_get_default_shop_url() ); ?>"><?php esc_html_e( 'Bekijk alles', 'dewit-catalog-theme' ); ?> <span aria-hidden="true">→</span></a>
					</div>
					<div class="dewit-mega-menu__columns">
						<?php foreach ( $parents as $parent ) : ?>
							<?php
							$children = get_terms(
								array(
									'taxonomy'   => 'product_cat',
									'parent'     => $parent->term_id,
									'hide_empty' => false,
									'orderby'    => 'name',
									'order'      => 'ASC',
								)
							);
							$parent_url = get_term_link( $parent );
							if ( is_wp_error( $parent_url ) ) {
								$parent_url = dewit_theme_get_default_shop_url() . '?dewit_parent_cat=' . rawurlencode( $parent->slug );
							}
							?>
							<div class="dewit-mega-menu__column">
								<a class="dewit-mega-menu__column-title" href="<?php echo esc_url( $parent_url ); ?>"><?php echo esc_html( $parent->name ); ?><span aria-hidden="true">↗</span></a>
								<?php if ( ! is_wp_error( $children ) && $children ) : ?>
								<ul>
									<?php foreach ( $children as $child ) : ?>
										<?php $child_url = get_term_link( $child ); ?>
										<?php if ( ! is_wp_error( $child_url ) ) : ?><li><a href="<?php echo esc_url( $child_url ); ?>"><?php echo esc_html( $child->name ); ?></a></li><?php endif; ?>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</li>
	</ul>
	<?php
}

/**
 * Render the compact white category bar below the global header.
 */
function dewit_theme_render_category_bar(): void {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return;
	}

	$parents = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( is_wp_error( $parents ) || ! $parents ) {
		return;
	}
	?>
	<nav id="dewit-category-bar" class="dewit-category-bar" aria-label="<?php esc_attr_e( 'Productcategorieën', 'dewit-catalog-theme' ); ?>">
		<div class="container dewit-category-bar__inner">
			<ul class="dewit-category-bar__list">
				<?php foreach ( $parents as $parent ) : ?>
					<?php
					if ( 'alle' === strtolower( (string) $parent->slug ) || 'alle' === strtolower( (string) $parent->name ) ) {
						continue;
					}

					$children = get_terms(
						array(
							'taxonomy'   => 'product_cat',
							'parent'     => $parent->term_id,
							'hide_empty' => true,
							'orderby'    => 'name',
							'order'      => 'ASC',
						)
					);
					$parent_url = get_term_link( $parent );
					if ( is_wp_error( $parent_url ) ) {
						$parent_url = dewit_theme_get_default_shop_url() . '?dewit_parent_cat=' . rawurlencode( $parent->slug );
					}
					?>
					<li class="dewit-category-bar__item<?php echo ( ! is_wp_error( $children ) && $children ) ? ' has-children' : ''; ?>">
						<a class="dewit-category-bar__link" href="<?php echo esc_url( $parent_url ); ?>">
							<?php echo esc_html( $parent->name ); ?>
						</a>
						<?php if ( ! is_wp_error( $children ) && $children ) : ?>
							<div class="dewit-category-bar__panel">
								<div class="dewit-category-bar__panel-inner">
									<div class="dewit-category-bar__panel-heading">
										<a href="<?php echo esc_url( $parent_url ); ?>"><?php echo esc_html( $parent->name ); ?> <b aria-hidden="true">↗</b></a>
									</div>
									<ul>
										<?php foreach ( $children as $child ) : ?>
											<?php $child_url = get_term_link( $child ); ?>
											<?php if ( ! is_wp_error( $child_url ) ) : ?><li><a href="<?php echo esc_url( $child_url ); ?>"><?php echo esc_html( $child->name ); ?></a></li><?php endif; ?>
										<?php endforeach; ?>
									</ul>
								</div>
							</div>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</nav>
	<?php
}
