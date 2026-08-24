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
				<span class="site-footer__eyebrow">De Wit Bouwmachines</span>
				<strong>Altijd de oplossing<br>in huis.</strong>
				<a class="site-header__phone site-footer__phone" href="tel:+31412634969" aria-label="0412 - 63 49 69">
					<span aria-hidden="true">☎</span> 0412 - 63 49 69
				</a>
			</div>
			<div class="site-footer__contact">
				<strong>Bezoek ons</strong>
				<p>Friezenweg 24<br>5349 AW Oss</p>
			</div>
			<div class="site-footer__hours">
				<strong>Openingstijden</strong>
				<ul>
					<li><span>Maandag t/m vrijdag</span><b>07:30–17:00</b></li>
					<li><span>Zaterdag en zondag</span><b>Gesloten</b></li>
				</ul>
			</div>
		</div>
		<div class="site-footer__bottom">
			<span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> De Wit Bouwmachines B.V.</span>
			<span>De Wit Bouwmachines · Oss</span>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
