<?php

/**
 * Ajax callback for Select Recipe Dropdown
 */
add_action( 'wp_ajax_crb_select_recipe_field', 'crb_get_recipe_last_used' );
function crb_get_recipe_last_used($cache_on = true) {
    $default_output = array(
        'status' => 'green',
        'text' => '',
        'date' => '',
    );
    $recipe_id = crb_request_param( 'recipe_id' );
    $class_id = crb_request_param( 'class_id' );
    $current_date = crb_request_param( 'date' );
    $cache_key = 'crb_recipe_temperature_' . $recipe_id;

    if (
        empty( $recipe_id ) ||
        empty( $class_id ) ||
        ! is_numeric( $recipe_id ) ||
        ! is_numeric( $class_id )
    ) {
        echo json_encode( $default_output );
        exit;
    }

    if( $cache_on ){
        $last_use = wp_cache_get( $cache_key, 'crb_get_recipe_last_used' );
    }

    if (empty($last_use)){
        $recipe = new Crb_Recipe( $recipe_id );
        $last_use = $recipe->get_last_use( $class_id, $current_date );
        if( $cache_on ){
            wp_cache_add( $cache_key, $last_use, 'crb_get_recipe_last_used', 45);
        }
    }

    echo json_encode( wp_parse_args( $last_use, $default_output ) );
    exit;
}