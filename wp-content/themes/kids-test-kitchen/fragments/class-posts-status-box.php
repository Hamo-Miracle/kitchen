<div id="minor-publishing">

	<?php // Hidden submit button early on so that the browser chooses the right button when form is submitted with Return key ?>
<div style="display:none;">
	<?php submit_button( __( 'Save' ), '', 'save' ); ?>
</div>

<div id="misc-publishing-actions">
	<div class="misc-pub-section misc-pub-post-status">
		<?php _e( 'Status:' ); ?> <span id="post-status-display">
				<?php

				switch ( $post->post_status ) {
					case 'private':
						_e( 'Privately Published' );
						break;
					case 'publish':
						_e( 'Published' );
						break;
					case 'future':
						_e( 'Scheduled' );
						break;
					case 'pending':
						_e( 'Pending Review' );
						break;
					case 'draft':
					case 'auto-draft':
						_e( 'Draft' );
						break;
				}
				?>
	</span>
		<?php
		if ( 'publish' == $post->post_status || 'private' == $post->post_status || $can_publish ) {
			$private_style = '';
			if ( 'private' == $post->post_status ) {
				$private_style = 'style="display:none"';
			}
			?>
	<a href="#post_status" <?php echo $private_style; ?> class="edit-post-status hide-if-no-js" role="button"><span aria-hidden="true"><?php _e( 'Edit' ); ?></span> <span class="screen-reader-text"><?php _e( 'Edit status' ); ?></span></a>

	<div id="post-status-select" class="hide-if-js">
	<input type="hidden" name="hidden_post_status" id="hidden_post_status" value="<?php echo esc_attr( ( 'auto-draft' == $post->post_status ) ? 'draft' : $post->post_status ); ?>" />
	<label for="post_status" class="screen-reader-text"><?php _e( 'Set status' ); ?></label>
	<select name="post_status" id="post_status">
		<option<?php selected( $post->post_status, 'publish' ); ?> value='publish'><?php _e( 'Published' ); ?></option>
	</select>
	<a href="#post_status" class="save-post-status hide-if-no-js button"><?php _e( 'OK' ); ?></a>
	<a href="#post_status" class="cancel-post-status hide-if-no-js button-cancel"><?php _e( 'Cancel' ); ?></a>
	</div>

	<?php } ?>
	</div><!-- .misc-pub-section -->

	<?php
	/**
	 * Fires after the post time/date setting in the Publish meta box.
	 *
	 * @since 2.9.0
	 * @since 4.4.0 Added the `$post` parameter.
	 *
	 * @param WP_Post $post WP_Post object for the current post.
	 */
	do_action( 'post_submitbox_misc_actions', $post );
	?>
</div>
<div class="clear"></div>
</div>