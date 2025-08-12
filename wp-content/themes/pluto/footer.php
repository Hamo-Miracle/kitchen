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
            <div id="footer-navigation"> 
            <?php wp_nav_menu(array( 'theme_location' => 'footer')); ?>
            </div> 
            
            
        	<?php
			if ( (function_exists( 'of_get_option' ) && (of_get_option('footertext2', true) != 1) ) ) : ?>
            <div id="footertext">
           <?php
			 	echo of_get_option('footertext2', true); 
			?> </div> 	
			<?php endif; ?>
        	 
            
           <?php if ( of_get_option('credit1', true) == 0 ) : ?>
            <div class="site-info">
                <?php do_action( 'pluto_credits' ); ?>
                <?php printf( __( '%1$s Theme by %2$s', 'pluto' ), 'Pluto', '<a href="http://www.viaviweb.com" rel="designer">Viaviwebtech</a>' ); ?>
				 
            </div><!-- .site-info -->
        <?php endif; ?>  
        </div><!--#footer-container--> 
	</footer><!-- #colophon -->
</div><!-- #page -->
<?php wp_footer(); ?>
</body>
</html>