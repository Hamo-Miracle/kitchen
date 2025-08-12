<div id="social_icons">
		    <?php if ( of_get_option('facebook', true) != "") { ?>
			 <a target="_blank" href="<?php echo esc_url(of_get_option('facebook', true)); ?>" title="<?php esc_attr_e( 'Facebook', 'pluto' ); ?>" ><img src="<?php echo get_template_directory_uri()."/images/facebook.png"; ?>"></a>
             <?php } ?>
            <?php if ( of_get_option('twitter', true) != "") { ?>
			 <a target="_blank" href="<?php echo esc_url(of_get_option('twitter', true)); ?>" title="<?php esc_attr_e( 'Twitter', 'pluto' ); ?>" ><img src="<?php echo get_template_directory_uri()."/images/twitter.png"; ?>"></a>
             <?php } ?>
             <?php if ( of_get_option('google', true) != "") { ?>
			 <a target="_blank" href="<?php echo esc_url(of_get_option('google', true)); ?>" title="<?php esc_attr_e( 'Google Plus', 'pluto' ); ?>" ><img src="<?php echo get_template_directory_uri()."/images/google.png"; ?>"></a>
             <?php } ?>
             <?php if ( of_get_option('pinterest', true) != "") { ?>
			 <a target="_blank" href="<?php echo esc_url(of_get_option('pinterest', true)); ?>" title="<?php esc_attr_e( 'Pinterest', 'pluto' ); ?>" ><img src="<?php echo get_template_directory_uri()."/images/pinterest.png"; ?>"></a>
             <?php } ?>
             <?php if ( of_get_option('linkedin', true) != "") { ?>
			 <a target="_blank" href="<?php echo esc_url(of_get_option('linkedin', true)); ?>" title="<?php esc_attr_e( 'Linked In', 'pluto' ); ?>" ><img src="<?php echo get_template_directory_uri()."/images/linkedin.png"; ?>"></a>
             <?php } ?>
             <?php if ( of_get_option('instagram', true) != "") { ?>
			 <a target="_blank" href="<?php echo esc_url(of_get_option('instagram', true)); ?>" title="<?php esc_attr_e( 'Instagram', 'pluto' ); ?>" ><img src="<?php echo get_template_directory_uri()."/images/instagram.png"; ?>"></a>
             <?php } ?>
             <?php if ( of_get_option('youtube', true) != "") { ?>
			 <a target="_blank" href="<?php echo esc_url(of_get_option('youtube', true)); ?>" title="<?php esc_attr_e( 'YouTube', 'pluto' ); ?>" ><img src="<?php echo get_template_directory_uri()."/images/youtube.png"; ?>"></a>
             <?php } ?>
             <?php if ( of_get_option('feedburner', true) != "") { ?>
			 <a target="_blank" href="<?php echo esc_url(of_get_option('feedburner', true)); ?>" title="<?php esc_attr_e( 'RSS Feed', 'pluto' ); ?>" ><img src="<?php echo get_template_directory_uri()."/images/rss.png"; ?>"></a>
             <?php } ?>
          </div>	