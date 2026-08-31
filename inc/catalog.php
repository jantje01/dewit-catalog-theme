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
							$parent_url = get_term_link( $parent );
							if ( is_wp_error( $parent_url ) ) {
								$parent_url = dewit_theme_get_default_shop_url() . '?dewit_parent_cat=' . rawurlencode( $parent->slug );
							}
							?>
							<div class="dewit-mega-menu__column">
								<a class="dewit-mega-menu__column-title" href="<?php echo esc_url( $parent_url ); ?>"><?php echo esc_html( $parent->name ); ?><span aria-hidden="true">↗</span></a>
								<?php if ( get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $parent->term_id, 'hide_empty' => true, 'fields' => 'ids' ) ) ) : ?>
								<ul>
									<?php dewit_theme_render_mega_menu_children( $parent->term_id ); ?>
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
 * Render category-bar children recursively.
 */
function dewit_theme_render_category_bar_children( int $parent_id ): void {
	$children = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => $parent_id,
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( is_wp_error( $children ) || empty( $children ) ) {
		return;
	}

	foreach ( $children as $child ) {
		$child_url = get_term_link( $child );

		if ( is_wp_error( $child_url ) ) {
			continue;
		}

		$grandchildren = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => $child->term_id,
				'hide_empty' => true,
				'fields'     => 'ids',
			)
		);
		?>
		<li class="<?php echo ( ! is_wp_error( $grandchildren ) && ! empty( $grandchildren ) ) ? 'has-children' : ''; ?>">
			<?php if ( ! is_wp_error( $grandchildren ) && ! empty( $grandchildren ) ) : ?>
				<a class="dewit-category-bar__desktop-category-link" href="<?php echo esc_url( $child_url ); ?>"><?php echo esc_html( $child->name ); ?></a>
				<button class="dewit-category-bar__mobile-level-trigger" type="button" aria-expanded="false"><?php echo esc_html( $child->name ); ?></button>
			<?php else : ?>
				<a href="<?php echo esc_url( $child_url ); ?>"><?php echo esc_html( $child->name ); ?></a>
			<?php endif; ?>
			<?php if ( ! is_wp_error( $grandchildren ) && ! empty( $grandchildren ) ) : ?>
				<button class="dewit-category-bar__child-trigger" type="button" aria-expanded="false" aria-label="<?php echo esc_attr( sprintf( __( '%s openen', 'dewit-catalog-theme' ), $child->name ) ); ?>"><span aria-hidden="true">›</span></button>
				<button class="dewit-category-bar__child-back" type="button"><span aria-hidden="true">‹</span> <?php esc_html_e( 'Terug', 'dewit-catalog-theme' ); ?></button>
				<ul>
					<?php dewit_theme_render_category_bar_children( $child->term_id ); ?>
				</ul>
			<?php endif; ?>
		</li>
		<?php
	}
}

function dewit_theme_render_mega_menu_children( int $parent_id ): void {
	$children = get_terms( array(
		'taxonomy'   => 'product_cat',
		'parent'     => $parent_id,
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	) );

	if ( is_wp_error( $children ) || empty( $children ) ) {
		return;
	}

	foreach ( $children as $child ) :
		$child_url = get_term_link( $child );
		if ( is_wp_error( $child_url ) ) {
			continue;
		}
		$grandchildren = get_terms( array(
			'taxonomy'   => 'product_cat',
			'parent'     => $child->term_id,
			'hide_empty' => true,
			'fields'     => 'ids',
		) );
		?>
		<li>
			<a href="<?php echo esc_url( $child_url ); ?>"><?php echo esc_html( $child->name ); ?></a>
			<?php if ( ! is_wp_error( $grandchildren ) && ! empty( $grandchildren ) ) : ?>
				<ul><?php dewit_theme_render_mega_menu_children( $child->term_id ); ?></ul>
			<?php endif; ?>
		</li>
		<?php
	endforeach;
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
			<div class="dewit-category-bar__mobile-header">
				<button class="dewit-category-bar__mobile-close" type="button" aria-label="<?php esc_attr_e( 'Categorieën sluiten', 'dewit-catalog-theme' ); ?>"><span aria-hidden="true">×</span></button>
				<strong><?php esc_html_e( 'Menu', 'dewit-catalog-theme' ); ?></strong>
				<a class="dewit-category-bar__mobile-contact" href="tel:+31412634969" aria-label="<?php esc_attr_e( 'Bel De Wit Bouwmachines', 'dewit-catalog-theme' ); ?>">
					<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6.6 10.8c1.5 3 3.9 5.4 6.9 6.9l2.3-2.3a1 1 0 0 1 1-.24c1.1.36 2.26.54 3.45.54a1 1 0 0 1 1 1V21a1 1 0 0 1-1 1C10.7 22 2 13.3 2 2.8a1 1 0 0 1 1-1H7.3a1 1 0 0 1 1 1c0 1.19.18 2.35.54 3.45a1 1 0 0 1-.24 1Z"/></svg>
				</a>
			</div>
			<form class="dewit-category-bar__mobile-search catalog-header-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="screen-reader-text" for="dewit-mobile-menu-search">Zoeken naar producten</label>
				<input id="dewit-mobile-menu-search" type="search" name="s" placeholder="<?php esc_attr_e( 'Zoeken naar producten', 'dewit-catalog-theme' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
				<input type="hidden" name="post_type" value="product">
				<button type="submit" aria-label="<?php esc_attr_e( 'Zoeken', 'dewit-catalog-theme' ); ?>">
					<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
				</button>
				<div class="dewit-shop-search-results" role="region" aria-label="<?php esc_attr_e( 'Directe zoekresultaten', 'dewit-catalog-theme' ); ?>"></div>
			</form>
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
						<?php if ( ! is_wp_error( $children ) && $children ) : ?>
							<a class="dewit-category-bar__link dewit-category-bar__desktop-category-link" href="<?php echo esc_url( $parent_url ); ?>"><?php echo esc_html( $parent->name ); ?></a>
							<button class="dewit-category-bar__link dewit-category-bar__mobile-level-trigger" type="button" aria-expanded="false"><?php echo esc_html( $parent->name ); ?></button>
						<?php else : ?>
							<a class="dewit-category-bar__link" href="<?php echo esc_url( $parent_url ); ?>"><?php echo esc_html( $parent->name ); ?></a>
						<?php endif; ?>
						<?php if ( ! is_wp_error( $children ) && $children ) : ?>
							<button class="dewit-category-bar__root-trigger" type="button" aria-expanded="false" aria-label="<?php echo esc_attr( sprintf( __( '%s openen', 'dewit-catalog-theme' ), $parent->name ) ); ?>"><span aria-hidden="true">›</span></button>
							<div class="dewit-category-bar__panel">
								<div class="dewit-category-bar__panel-inner">
									<div class="dewit-category-bar__mobile-heading"><button class="dewit-category-bar__root-back" type="button"><span aria-hidden="true">‹</span> <?php esc_html_e( 'Terug', 'dewit-catalog-theme' ); ?></button><strong><?php echo esc_html( $parent->name ); ?></strong></div>
							<ul>
								<?php dewit_theme_render_category_bar_children( $parent->term_id ); ?>
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
