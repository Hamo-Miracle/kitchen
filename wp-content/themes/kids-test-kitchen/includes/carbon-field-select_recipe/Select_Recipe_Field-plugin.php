<?php
/*
Plugin Name: Carbon Field: Select_Recipe
Description: Extends the base Carbon Fields with a Select_Recipe field.
Version: 1.0.0
*/

/**
 * Set text domain
 * @see https://codex.wordpress.org/Function_Reference/load_plugin_textdomain
 */
load_plugin_textdomain( 'carbon-field-select-recipe', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

define( 'CRB_SELECT_RECIPE_DIR', dirname( __FILE__ ) . DIRECTORY_SEPARATOR );

# Define root URL
if ( ! defined( 'CRB_SELECT_RECIPE_URL' ) ) {
	$url = trailingslashit( __DIR__ );
	$count = 0;

	# Sanitize directory separator on Windows
	$url = str_replace( '\\' ,'/', $url );

	$possible_locations = array(
		WP_PLUGIN_DIR => plugins_url(), # If installed as a plugin
		WP_CONTENT_DIR => content_url(), # If anywhere in wp-content
		ABSPATH => site_url( '/' ), # If anywhere else within the WordPress installation
	);

	foreach ( $possible_locations as $test_dir => $test_url ) {
		$test_dir_normalized = str_replace( '\\' ,'/', $test_dir );
		$url = str_replace( $test_dir_normalized, $test_url, $url, $count );

		if ( $count > 0 ) {
			break;
		}
	}

	define( 'CRB_SELECT_RECIPE_URL', untrailingslashit( $url ) );
}

/**
 * Hook field initialization
 */
add_action( 'after_setup_theme', 'crb_init_carbon_field_select_recipe', 15 );
function crb_init_carbon_field_select_recipe() {
	if ( class_exists( 'Carbon_Fields\\Field\\Field' ) ) {
		include_once CRB_SELECT_RECIPE_DIR . 'Select_Recipe_Field.php';
	}
}
