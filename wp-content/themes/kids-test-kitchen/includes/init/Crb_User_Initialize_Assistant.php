<?php

use Carbon_Fields\Container\Container;
use Carbon_Fields\Field\Field;

/**
 * Register the Assistant user role
 */
class Crb_User_Initialize_Assistant {
	public $role          = 'crb_assistant';
	public $singular_name = 'KTK Assistant';

	function __construct() {
		add_action( 'after_switch_theme',     array( $this, 'create_user_role' ), 10 );
		add_action( 'after_switch_theme',     array( $this, 'add_user_caps' ), 11 );
		add_action( 'carbon_register_fields', array( $this, 'user_meta' ) );
		add_action( 'init',                   array( $this, 'register_admin_columns' ) );
		add_action( 'pre_get_posts',          array( $this, 'pre_get_posts' ) );
		add_filter( 'wp_dropdown_users_args', array( $this, 'wp_dropdown_users_args' ), 10,  2 );

		// User Specific Hooks
		if ( Crb_Current_User()->is( $this->role ) ) {
			global $Crb_User_Initialize_Base;

			$Crb_User_Initialize_Base->dashboard_cleanup();
			$Crb_User_Initialize_Base->profile_cleanup();
		}

        add_action('admin_head','change_profile_style');
    }

	/**
	 * Register User Meta
	 */
	function user_meta() {
		Container::make( 'user_meta', 'Address' )
			->show_on_user_role( $this->role )
			->show_for( array(
				'relation' => 'OR',
				'administrator',
				$this->role
			) )
			->add_fields( array(
				Field::make( 'textarea', 'crb_address', __( 'Billing Address', 'crb' ) )
					->set_required( true )
					->set_rows( 2 ),
				Field::make( 'text', 'crb_city', __( 'City', 'crb' ) )
					->set_required( true ),
				Field::make( 'text', 'crb_state', __( 'State', 'crb' ) )
					->set_required( true ),
				Field::make( 'text', 'crb_zip', __( 'Zip Code', 'crb' ) )
					->set_required( true ),
				Field::make( 'text', 'crb_phone', __( 'Phone', 'crb' ) )
					->set_required( true ),
				Field::make( 'text', 'crb_company_name', __( 'Company Name', 'crb' ) )
					->set_required( true ),
				//Field::make( 'html', 'crb_summary', __( 'Summary', 'crb' ) )
				//	->set_html( 'crb_get_current_user_summary' ),
			) );
	}

	/**
	 * Admin columns for specific user roles
	 */
	function register_admin_columns() {
		$display_additional_columns = isset( $_GET ) && isset( $_GET['role'] ) && $_GET['role'] == $this->role;
		if ( ! $display_additional_columns ) {
			return;
		}

		Carbon_Admin_Columns_Manager::modify_columns( 'user' )
			->remove( array( 'role', 'posts' ) )
			->add( array(
				Carbon_Admin_Column::create( 'Billing Address' )
					->set_name( 'crb-address-column' )
					->set_field( '_crb_address' ),
				Carbon_Admin_Column::create( 'City' )
					->set_name( 'crb-city-column' )
					->set_field( '_crb_city' ),
				Carbon_Admin_Column::create( 'State' )
					->set_name( 'crb-state-column' )
					->set_field( '_crb_state' ),
				Carbon_Admin_Column::create( 'Zip Code' )
					->set_name( 'crb-zip-column' )
					->set_field( '_crb_zip' ),
				Carbon_Admin_Column::create( 'Phone' )
					->set_name( 'crb-phone-column' )
					->set_field( '_crb_phone' ),
				Carbon_Admin_Column::create( 'Company Name' )
					->set_name( 'crb-company_name-column' )
					->set_field( '_crb_company_name' ),
				Carbon_Admin_Column::create( 'Locations' )
					->set_name( 'crb-locations-column' )
					->set_callback( array( $this, 'get_all_locations' ) ),
				Carbon_Admin_Column::create( 'Regions' )
					->set_name( 'crb-regions-column' )
					->set_callback( array( $this, 'get_all_regions' ) ),
			) );
	}

	/**
	 * Filter out entries
	 */
	function pre_get_posts( $query ) {

	}

	/**
	 * Filter parameters for "get_users" called within "wp_dropdown_users"
	 * Works only for Locations
	 * This will allows the admin to assign Session Admins as authors of locations
	 */
	function wp_dropdown_users_args( $query_args, $r ) {
		$post = crb_request_param( 'post' );
		$post_type = crb_request_param( 'post_type' );
		if ( empty( $post_type ) && ! empty( $post ) ) {
			$post_obj = get_post( $post );
			$post_type = $post_obj->post_type;
		}

		if ( ! in_array( $post_type, array( 'crb_location', 'crb_class', 'crb_date' ) ) ) {
			return $query_args;
		}

		$query_args['role__in'] = (array) $query_args['role__in'];

		if ( empty( $query_args['role__in'] ) ) {
			$query_args['role__in'] = array(
				'administrator',
				$this->role,
			);
		} else {
			$query_args['role__in'][] = $this->role;
		}

		$query_args['who'] = '';

		return $query_args;
	}

	/**
	 * Return Location IDs
	 */
	function get_all_location_ids( $user_id ) {
		$locations = get_posts( array(
			'post_type'      => 'crb_location',
			'author'         => $user_id,
			'posts_per_page' => -1,
			'fields'         => 'ids',
		) );

		return $locations;
	}

	/**
	 * Callback column Locations
	 */
	function get_all_locations( $user_id ) {
		$locations = $this->get_all_location_ids( $user_id );
		if ( empty( $locations ) ) {
			return;
		}

		array_walk( $locations, function( &$location_id ) {
			global $Crb_Initialize_Location;

			$link        = $Crb_Initialize_Location->get_post_edit_link( $location_id );
			$location_id = $link;
		} );

		return implode( ', <br /><br />', $locations );
	}

	/**
	 * Callback column Regions
	 */
	function get_all_regions( $user_id ) {
		$locations = $this->get_all_location_ids( $user_id );

		if ( empty( $locations ) ) {
			return;
		}

		$regions = wp_get_object_terms( $locations, 'crb_location_region', array(
			'orderby' => 'term_order',
			'order'   => 'ASC',
			'fields'  => 'all'
		) );

		if ( empty( $regions ) ) {
			return;
		}

		array_walk( $regions, function( &$region_obj ) {
			global $Crb_Initialize_Location;

			$link       = $Crb_Initialize_Location->get_term_edit_link( $region_obj );
			$region_obj = $link;
		} );

		return implode( ', <br /><br />', $regions );
	}

	/**
	 * Create User Role
	 */
	function create_user_role() {
		remove_role( $this->role );

		add_role( $this->role, $this->singular_name, array( 'read' => true, 'level_0' => true ) );

		update_option( 'default_role', $this->role );
	}

	/**
	 * Define User's capabilities
	 */
	function add_user_caps() {
		global $Crb_Initialize_Location;
		global $Crb_Initialize_Class;
		global $Crb_Initialize_Date;
		global $Crb_Initialize_Recipe;

		$role = get_role( $this->role );

		$role->add_cap('create_users');
		$role->add_cap('edit_users');
		$role->add_cap('promote_users');
		$role->add_cap('list_users');
		$role->add_cap('remove_users');
		$role->add_cap('delete_users');

		$objects = array(
			'crb_location' => $Crb_Initialize_Location,
			'crb_class'    => $Crb_Initialize_Class,
			'crb_date'     => $Crb_Initialize_Date,
			'crb_recipe'   => $Crb_Initialize_Recipe,
		);

		$capabilities          = array();
		$taxonomy_capabilities = array();

		foreach ( $objects as $slug => $object ) {
			$taxonomy_capabilities = array_merge( $taxonomy_capabilities, $object->get_taxonomy_capabilities() );
			$capabilities[$slug]   = $object->get_post_type_capabilities();

			foreach ( $capabilities[$slug] as $capability ) {
				if ( in_array( $slug, array( 'crb_location', 'crb_class', 'crb_recipe' ) ) ) {
					$role->add_cap( $capability );
				} else {
					$role->remove_cap( $capability );
				}
			}
		}

		foreach ( $taxonomy_capabilities as $taxonomy => $capabilities ) {
			foreach ( $capabilities as $capability ) {
				$role->add_cap( $capability );
			}
		}
	}
}

new Crb_User_Initialize_Assistant();
