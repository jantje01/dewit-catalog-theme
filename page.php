<?php
/**
 * Page template.
 *
 * @package DewitTheme
 */

if ( is_front_page() ) {
	get_template_part( 'front-page' );
	return;
}

get_header();
?>

<main id="primary" class="site-main">
	<div class="container page-layout">
		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content', 'page' );

			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		endwhile;
		?>
	</div>
</main>

<?php
get_footer();
