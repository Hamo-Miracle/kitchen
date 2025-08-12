<?php
/**
 * Register an Archived Classes status.
 */
function crb_register_archived_classes_status(){
	register_post_status( 'archive', array(
		'label'                     => __( 'Archived', 'crb' ),
		'public'                    => false,
		'private'                   => true,
		'exclude_from_search'       => true,
		'show_in_admin_all_list'    => false,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Archived <span class="count">(%s)</span>', 'Archived <span class="count">(%s)</span>', 'crb' ),
	) );
}
add_action( 'init', 'crb_register_archived_classes_status' );

/**
 * Display Archived state text next to post titles.
 */
function crb_update_classes_listings_names( $post_states, $post ) {
	if ( $post->post_status === 'archive' && get_query_var( 'post_status' ) === 'archive' ) {
		return array_merge( $post_states, array( 'archive' => __( 'Archived', 'crb' ) )	);
	}

	return $post_states;
}
add_filter( 'display_post_states', 'crb_update_classes_listings_names', 10, 2 );

/**
 * Modify the DOM on post screens.
 */
function crb_update_statuses_dropdown() {
	global $post;
	if ( $post->post_type !== 'crb_class' ) {
		return;
	}

	if ( 'draft' !== $post->post_status && 'pending' !== $post->post_status ) { ?>
		<script>
		var selected = <?php $post->post_status === 'archive' ? 'selected' : ''; ?>

		jQuery( document ).ready( function( $ ) {
			$( '#post_status' ).append( '<option value="archive" ' + selected + '><?php esc_html_e( 'Archived', 'archived-post-status' ) ?></option>' );
		} );
		</script>
	<?php
	}

	if ( 'archive' === $post->post_status ) { ?>
		<script>
		jQuery( document ).ready( function( $ ) {
			$( '#post-status-display' ).text( '<?php esc_html_e( 'Archived', 'archived-post-status' ) ?>' );
		} );
		</script>
		<?php
	}
}
add_action( 'admin_footer-post.php', 'crb_update_statuses_dropdown' );

/**
 * Modify the DOM on edit screens.
 */
function crb_update_quick_edit_statuses_dropdown() {
	global $typenow;
	if ( $typenow !== 'crb_class' ) {
		return;
	}
	?>
	<script>
		jQuery( document ).ready( function( $ ) {
			$( 'select[name="_status"]' ).append( '<option value="archive"><?php esc_html_e( 'Archived', 'archived-post-status' ) ?></option>' );

			$( '.editinline' ).on( 'click', function() {
				var $row        = $( this ).closest( 'tr' ),
					$option     = $( '.inline-edit-row' ).find( 'select[name="_status"] option[value="archive"]' ),
					is_archived = $row.hasClass( 'status-archive' );

				$option.prop( 'selected', is_archived );
			} );
		} );
	</script>
	<?php
}
add_action( 'admin_footer-edit.php', 'crb_update_quick_edit_statuses_dropdown' );

add_action( 'pre_get_posts', 'crb_update_query_to_get_only_published_posts' );
function crb_update_query_to_get_only_published_posts( $query ) {
	if ( ! is_admin() && $query->get( 'post_type' ) === 'crb_class' ) {
		$query->set( 'post_status', 'publish' );
	}
}

// Exclude Archived posts from all posts listing screen
add_action( 'posts_where', function( $where, $query ) {
    if( is_admin()
        && $query->is_main_query()
        && $query->get( 'post_type' ) === 'crb_class'
        && ! filter_input( INPUT_GET, 'post_status' )
        && ( $screen = get_current_screen() ) instanceof \WP_Screen
    )
    {
        global $wpdb;
        $status_to_exclude = 'archive';

        $where .= sprintf(
            " AND {$wpdb->posts}.post_status NOT IN ( '%s' ) ",
            $status_to_exclude
        );
    }
    return $where;
}, 10, 2 );