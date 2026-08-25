<?php
/**
 * The footer for the theme.
 *
 * @package DewitTheme
 */

?>
<footer class="site-footer">
	<div class="container">
		<div class="site-footer__inner">
			<div class="site-footer__brand">
				<?php if ( has_custom_logo() ) : ?>
					<div class="site-footer__logo"><?php the_custom_logo(); ?></div>
				<?php else : ?>
					<span class="site-footer__eyebrow">De Wit Bouwmachines</span>
				<?php endif; ?>
				<strong>Altijd de oplossing in huis.</strong>
			</div>
			<div class="site-footer__contact">
				<strong>Bezoek ons</strong>
				<p class="site-footer__address"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></svg><span>Friezenweg 24, 5349 AW Oss</span></p>
				<p class="site-footer__opening-note"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg><span>Iedere werkdag geopend van 07:30–17:00</span></p>
			</div>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
