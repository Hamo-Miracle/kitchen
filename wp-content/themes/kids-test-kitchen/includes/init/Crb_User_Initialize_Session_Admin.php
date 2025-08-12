<?php

use Carbon_Fields\Container\Container;
use Carbon_Fields\Field\Field;

/**
 * Register the Session Admin user role
 */
class Crb_User_Initialize_Session_Admin {
	public $role = 'crb_session_admin';
	public $singular_name = 'KTK Session Admin';

	function __construct() {
		add_action( 'after_switch_theme', array( $this, 'create_user_role' ), 10 );
		add_action( 'after_switch_theme', array( $this, 'add_user_caps' ), 11 );

		add_action( 'carbon_register_fields', array( $this, 'user_meta' ) );

		add_action( 'init', array( $this, 'register_admin_columns' ) );

		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

		add_filter( 'wp_dropdown_users_args', array( $this, 'wp_dropdown_users_args' ), 10,  2 );

		// User Specific Hooks
		if ( Crb_Current_User()->is( $this->role ) ) {
			global $Crb_User_Initialize_Base;
			$Crb_User_Initialize_Base->dashboard_cleanup();
			$Crb_User_Initialize_Base->profile_cleanup();
		}

        add_filter('user_contactmethods', 'modify_contact_methods');
        add_action('admin_head','change_profile_style');
    }

	/**
	 * Register User Meta
	 */
	function user_meta() {
		Container::make( 'user_meta', 'ADDRESS' )
			->show_on_user_role( $this->role )
			->show_for( array(
				'relation' => 'OR',
				'administrator',
				'crb_assistant'
			) )
			->add_fields( array(
				Field::make( 'select', 'crb_user_organization', __( 'Select Organization', 'crb' ) )
							->set_options( array( 'Crb_Initialize_Org', 'get_select_options' ) )
							->set_required( true )
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
	 * Filter out entries for newly registered users
	 */
	function pre_get_posts( $query ) {
		if (
			Crb_Current_User()->is( $this->role ) &&
			$query->is_main_query() &&
			$query->found_posts == 0 &&
			!empty( $_GET['post_type'] ) &&
			in_array( $_GET['post_type'], array( 'crb_location', 'crb_class' ) )
		) {
			$query->set( 'author', Crb_Current_User()->get_id() );
		}
	}

	/**
	 * Filter parameters for "get_users" called within "wp_dropdown_users"
	 * Works only for Locations
	 * This will allows the admin to assign Session Admins as authors of locations
	 */
	function wp_dropdown_users_args( $query_args, $r ) {
		$post = crb_request_param( 'post' );
		$post_type = crb_request_param( 'post_type' );
		$post_id = get_the_ID();
		if ( empty( $post_type ) && ! empty( $post ) ) {
			$post_obj = get_post( $post );
			$post_type = $post_obj->post_type;
		}
		
		
		if ( ! in_array( $post_type, array( 'crb_location', 'crb_class', 'crb_date' ) ) ) {
			return $query_args;
		}

		$org = get_post_meta($post_id,'_crb_user_organization',true);
		

		$query_args['role__in'] = (array) $query_args['role__in'];

		// Add Session Admin to the dropdown
		
		if ( empty( $query_args['role__in'] ) ) {
			$query_args['role__in'] = array(
				'administrator',
				$this->role,
			);
		} else {
			$query_args['role__in'][] = $this->role;
		}
		
		
		$query_args['meta_query'] = array(
			'relation' => 'OR',
			array(
				'key' => 'kkc_capabilities',
				'value' => 'administrator',
				'compare' => 'like'
			),
			array(
				'key' => '_crb_user_organization',
				'value' => $org,
				'compare' => '='
			)
		);
		
		

		// Remove the ancient "level_0"
		$query_args['who'] = '';

		//$query_args['meta_key'] = '_crb_user_organization';
		//$query_args['meta_value'] = $org;

		return $query_args;
	}

	/**
	 * Return Location IDs
	 */
	function get_all_location_ids( $user_id ) {
		$locations = get_posts( array(
			'post_type' => 'crb_location',
			'author' => $user_id,
			'posts_per_page' => -1,
			'fields' => 'ids',
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
			$link = $Crb_Initialize_Location->get_post_edit_link( $location_id );

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
			'order' => 'ASC',
			'fields' => 'all'
		) );

		if ( empty( $regions ) ) {
			return;
		}

		array_walk( $regions, function( &$region_obj ) {
			global $Crb_Initialize_Location;
			$link = $Crb_Initialize_Location->get_term_edit_link( $region_obj );

			$region_obj = $link;
		} );

		return implode( ', <br /><br />', $regions );
	}

	/**
	 * Create User Role
	 */
	function create_user_role() {
		// Remove, so it allows modifications from the next line
		remove_role( $this->role );

		add_role( $this->role, $this->singular_name, array( 'read' => true, 'level_0' => true ) );

		// Setup this role as default
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

		// gets the admin role
		$role = get_role( $this->role );

		$objects = array(
			'crb_location' => $Crb_Initialize_Location,
			'crb_class' => $Crb_Initialize_Class,
			'crb_date' => $Crb_Initialize_Date,
			'crb_recipe' => $Crb_Initialize_Recipe,
		);

		$taxonomy_capabilities = array();
		$capabilities = array();

		/**
		 * Allow Session Admins to create and edit own entries, not others
		 */
		foreach ( $objects as $slug => $object ) {
			$taxonomy_capabilities = array_merge( $taxonomy_capabilities, $object->get_taxonomy_capabilities() );

			$capabilities[$slug] = $object->get_post_type_capabilities();

			// First remove all possible Caps
			foreach ( $capabilities[$slug] as $capability ) {
				$role->remove_cap( $capability );
			}

			if ( in_array( $slug, array( 'crb_location', 'crb_class' ) ) ) {
				// Allow the Post type to appear in administration
				$role->add_cap( $capabilities[$slug]['edit_posts'] );

				// Allow new entries to be created with "Add New" button
				$role->add_cap( $capabilities[$slug]['create_posts'] );
			}

		}

		// Publishing capabilities, allowing posts to be directly published without
		$role->add_cap( $capabilities['crb_location']['publish_posts'] );
		$role->add_cap( $capabilities['crb_class']['publish_posts'] );

		// Taxonomy capabilities
		foreach ( $taxonomy_capabilities as $taxonomy => $capabilities ) {
			foreach ( $capabilities as $capability ) {
				$role->remove_cap( $capability );
			}
		}

		$role->add_cap( $taxonomy_capabilities['crb_class_age']['assign_terms'] );
	}

}

new Crb_User_Initialize_Session_Admin();
