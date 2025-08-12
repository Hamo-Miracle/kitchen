<?php

/**
 * Cleanup The Default Meta Box
 */
class Crb_Replace_Submit_Meta_Box {
	function __construct() {
		// Override the Callback for submit box
		add_action( 'add_meta_boxes', array( $this, 'override_submit_meta_box' ), 10, 2 );
	}

	function override_submit_meta_box( $post_type, $post ) {
		global $wp_meta_boxes;

		foreach ( array( 'crb_location', 'crb_class', 'crb_recipe' ) as $post_type ) {
			if (
				!empty( $wp_meta_boxes ) &&
				!empty( $wp_meta_boxes[$post_type] ) &&
				!empty( $wp_meta_boxes[$post_type]['side'] ) &&
				!empty( $wp_meta_boxes[$post_type]['side']['core'] ) &&
				!empty( $wp_meta_boxes[$post_type]['side']['core']['submitdiv'] ) &&
				!empty( $wp_meta_boxes[$post_type]['side']['core']['submitdiv']['callback'] )
			) {
				// page, context, priority, id, callback
				$wp_meta_boxes[$post_type]['side']['core']['submitdiv']['callback'] = array( $this, 'post_submit_meta_box' );
			}
		}
	}

	/**
	 * Displays post submit form fields.
	 *
	 * @since 2.7.0
	 *
	 * @global string $action
	 *
	 * @param WP_Post  $post Current post object.
	 * @param array    $args {
	 *     Array of arguments for building the post submit meta box.
	 *
	 *     @type string   $id       Meta box 'id' attribute.
	 *     @type string   $title    Meta box title.
	 *     @type callable $callback Meta box display callback.
	 *     @type array    $args     Extra meta box arguments.
	 * }
	 */
	function post_submit_meta_box( $post, $args = array() ) {
		global $action;

		$post_type = $post->post_type;
		$post_type_object = get_post_type_object($post_type);
		$can_publish = current_user_can($post_type_object->cap->publish_posts);
		?>
		<div class="submitbox" id="submitpost">
			<?php
			if ( $post_type === 'crb_class' && ( $post->post_status === 'publish' || $post->post_status === 'archive' )  ) {
				crb_render_fragment( 'class-posts-status-box', array(
					'post'             => $post,
					'post_type'        => $post_type,
					'post_type_object' => $post_type_object,
					'can_publish'      => $can_publish,
				) );
			}
			?>

			<div id="major-publishing-actions">
				<?php
				/**
				 * Fires at the beginning of the publishing actions section of the Publish meta box.
				 *
				 * @since 2.7.0
				 */
				do_action( 'post_submitbox_start' );
				?>

				<div id="delete-action">
					<?php
					if ( current_user_can( "delete_post", $post->ID ) ) {
						if ( !EMPTY_TRASH_DAYS )
							$delete_text = __('Delete Permanently');
						else
							$delete_text = __('Move to Trash');
						?>
					<a class="submitdelete deletion" href="<?php echo get_delete_post_link($post->ID); ?>"><?php echo $delete_text; ?></a><?php
					} ?>
				</div>

				<div id="publishing-action">
					<span class="spinner"></span>
					<?php
					if ( !in_array( $post->post_status, array('publish', 'future', 'private') ) || 0 == $post->ID ) {
						if ( $can_publish ) :
							if ( !empty($post->post_date_gmt) && time() < strtotime( $post->post_date_gmt . ' +0000' ) ) : ?>
							<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Schedule') ?>" />
							<?php submit_button( apply_filters( 'crb_submit_button_label_schedule', __( 'Schedule' ) ), 'primary large', 'publish', false ); ?>
					<?php	else : ?>
							<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Publish') ?>" />
							<?php submit_button( apply_filters( 'crb_submit_button_label_publish', __( 'Publish' ) ), 'primary large', 'publish', false ); ?>
					<?php	endif;
						else : ?>
							<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Submit for Review') ?>" />
							<?php submit_button( apply_filters( 'crb_submit_button_label_submit', __( 'Submit for Review' ) ), 'primary large', 'publish', false ); ?>
					<?php
						endif;
					} else { ?>
							<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Update') ?>" />
							<input name="save" type="submit" class="button button-primary button-large" id="publish" value="<?php echo esc_attr( apply_filters( 'crb_submit_button_label_update', __( 'Update' ) ) ) ?>" />
					<?php
					} ?>
				</div>

				<div class="clear"></div>
			</div>
		</div>

		<?php
	}
}

new Crb_Replace_Submit_Meta_Box();
