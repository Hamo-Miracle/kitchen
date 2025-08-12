<?php
/**
 * @package Pluto
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<h1 class="entry-title">
			<?php the_title(); ?>
		</h1>
	</header><!-- .entry-header -->

	<?php if (has_post_thumbnail() ) : ?>
		<div id="post_thumbnail">
			<?php the_post_thumbnail(); ?>
		</div>
	<?php endif; ?>

	<div class="entry-content">
		<?php

		the_content();

		wp_link_pages( array(
			'before' => '<div class="page-links">' . __( 'Pages:', 'pluto' ),
			'after'  => '</div>',
		) );

		?>
	</div><!-- .entry-content -->
</article><!-- #post-## -->
