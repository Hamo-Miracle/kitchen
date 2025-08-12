<?php

/**
 * Change Login page Logo URL
 */
add_filter( 'login_headerurl', 'my_login_logo_url' );
function my_login_logo_url() {
	return admin_url();
}

/**
 * Inject additional custom options
 */
add_filter( 'of_options', 'crb_add_options' );
function crb_add_options( $options ) {
	$new_options = array();
	foreach ( $options as $index => $option ) {
		$new_options[] = $option;

		// Add "logo_home" after "logo"
		if ( isset( $option['id'] ) && $option['id'] === 'logo' ) {
			$new_options[] = array(
				'name' => __( 'Site Logo (Default page template)', 'crb' ),
				'desc' => __( 'This logo will be displayed on Default page template only.', 'crb' ),
				'id' => 'logo_home',
				'class' => '',
				'type' => 'upload',
			);
		}
	}

	return $new_options;
}

/**
 * Admin Site Instructions Menu Entry
 */
add_action( 'admin_menu', 'crb_admin_menu_add_site_instructions' );
function crb_admin_menu_add_site_instructions() {
	add_menu_page(
		__( 'Site Instructions', 'crb' ),
		__( 'Site Instructions', 'crb' ),
		'site_instructions',
		'crbn-site-instructions.php',
		'crb_admin_menu_add_site_instructions_render',
		'dashicons-editor-help',
		1
	);
}

// Callback - render admin page content
function crb_admin_menu_add_site_instructions_render() {
	$crb_site_instructions = carbon_get_theme_option( 'crb_site_instructions' );
    if (
        Crb_Current_User()->is( 'crb_facilitator' )
    ) {
        $crb_site_instructions = carbon_get_theme_option( 'crb_site_instructions_facilitator' );
    }
    if (
        Crb_Current_User()->is( 'crb_assistant' )
    ) {
        $crb_site_instructions = carbon_get_theme_option( 'crb_site_instructions_assistant' );
    }
    if (
        Crb_Current_User()->is( 'crb_session_admin' )
    ) {
        $crb_site_instructions = carbon_get_theme_option( 'crb_site_instructions_session_admin' );
    }
	?>

	<div class="wrap">
		<h1><?php _e( 'Welcome', 'crb' ); ?></h1>

		<div class="card">
			<?php echo apply_filters( 'the_content', $crb_site_instructions ); ?>
		</div>
	</div>

	<?php
}
