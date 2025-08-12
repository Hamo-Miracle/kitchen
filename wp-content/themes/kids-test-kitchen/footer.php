<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content after
 *
 * @package Pluto
 */
?>

	</div><!-- #content -->

	<footer id="colophon" class="site-footer" role="contentinfo">
		<div id="footer-container">
			<?php if ( has_nav_menu( 'footer' ) ): ?>
				<div id="footer-navigation">
					<?php
					wp_nav_menu( array(
						'theme_location' => 'footer',
					) );
					?>
				</div>
			<?php endif; ?>

			<?php if ( ( function_exists( 'of_get_option' ) && ( of_get_option( 'footertext2', true ) != 1 ) ) ) : ?>
				<div id="footertext">
					<?php echo of_get_option( 'footertext2', true ); ?>
				</div>
			<?php endif; ?>

			<?php if ( of_get_option( 'credit1', true ) == 0 ) : ?>
				<div class="site-info">
					<?php do_action( 'pluto_credits' ); ?>

					<?php
					printf(
						__( '%1$s Theme by %2$s', 'pluto' ),
						'Pluto',
						'<a href="http://www.viaviweb.com" rel="designer">Viaviwebtech</a>'
					);
					?>
				</div><!-- .site-info -->
			<?php endif; ?>
		</div><!--#footer-container-->
	</footer><!-- #colophon -->

</div><!-- #page -->
<?php wp_footer(); ?>
<!-- Start of StatCounter Code for Default Guide -->
<script type="text/javascript">
var sc_project=11463522; 
var sc_invisible=1; 
var sc_security="5edb9e2f"; 
var scJsHost = (("https:" == document.location.protocol) ?
"https://secure." : "http://www.");
document.write("<sc"+"ript type='text/javascript' src='" +
scJsHost+
"statcounter.com/counter/counter.js'></"+"script>");
</script>
<noscript><div class="statcounter"><a title="Web Analytics
Made Easy - StatCounter" href="http://statcounter.com/"
target="_blank"><img class="statcounter"
src="//c.statcounter.com/11463522/0/5edb9e2f/1/" alt="Web
Analytics Made Easy - StatCounter"></a></div></noscript>
<!-- End of StatCounter Code for Default Guide -->
</body>
</html>
