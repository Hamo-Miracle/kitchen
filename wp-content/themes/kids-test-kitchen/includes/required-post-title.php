<?php

/**
 * Required Title
 */
add_action( 'admin_init', 'crb_force_post_title_init' );
function crb_force_post_title_init() {
	wp_enqueue_script( 'jquery' );
}

add_action( 'edit_form_advanced', 'crb_force_post_title_js' );
function crb_force_post_title_js() {
	global $pagenow;

	$post_type = false;
	if( isset( $_GET ) && isset( $_GET['post_type'] ) ) {
		$post_type = $_GET['post_type'];
	} elseif ( $pagenow == 'post.php' && $check_post_type = get_post_type( get_the_id() ) ) {
		$post_type = $check_post_type;
	}

	// Check valid post type
	if ( empty( $post_type ) ) {
		return;
	}

	// Check post type allowed
	if ( ! in_array( $post_type, array( 'crb_class', 'crb_location' ) ) ) {
		return;
	}

	if ( $post_type == 'crb_location' ) {
		$required_text = __( 'Location Name is required', 'crb' );
	} elseif ( $post_type == 'crb_class' ) {
		$required_text = __( 'Class Name is required', 'crb' );
	}

	?>

	<script type='text/javascript'>
	;(function($, window, document, undefined) {
		var $doc = $(document);
		$doc.ready( function() {
			$('#title').change(function( e ){
				var $title = $(this);
				var $titlewrap = $title.closest( '#titlewrap' );

				if ( $title.val().length < 1 ) {
					$titlewrap.css( 'background', '#cd4c15' );
				} else {
					$titlewrap.css( 'background', 'transparent' );
				}
			});

			$('#publish').click(function( e ){
				var $title = $('#titlediv').find('#title');
				var $titlewrap = $title.closest( '#titlewrap' );

				if ( $title.val().length < 1 ) {
					$titlewrap.css( 'background', '#cd4c15' );

					alert("<?php echo esc_js($required_text); ?>");

					e.preventDefault();
				} else {
					$titlewrap.css( 'background', 'transparent' );
				}
			});
		} );

	})(jQuery, window, document);
	</script>

	<?php
}
