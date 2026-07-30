<?php
/** Catalog header controls. */

defined( 'ABSPATH' ) || exit;
?>
<div class="dewit-live-topbar">
	<form class="catalog-header-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<label class="screen-reader-text" for="catalog-header-search-input"><?php esc_html_e( 'Zoeken naar producten', 'dewit-catalog-theme' ); ?></label>
		<input id="catalog-header-search-input" type="search" name="s" placeholder="Zoeken naar producten" value="<?php echo esc_attr( get_search_query() ); ?>">
		<input type="hidden" name="post_type" value="product">
		<button type="submit" aria-label="Zoeken">⌕</button>
	</form>
	<a class="header-contact header-contact--email" href="mailto:info@dewitbouwmachines.nl">✉ <span>info@dewitbouwmachines.nl</span></a>
	<a class="header-contact header-contact--phone" href="tel:+31412634969">☎ <span>0412 - 63 49 69</span></a>
</div>
