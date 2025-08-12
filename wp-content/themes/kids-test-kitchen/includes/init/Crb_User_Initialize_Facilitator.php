<?php

use Carbon_Fields\Container\Container;
use Carbon_Fields\Field\Field;

/**
 * Register the Facilitator user role
 */
class Crb_User_Initialize_Facilitator {
	public $role = 'crb_facilitator';
	public $singular_name = 'KTK Facilitator';

	function __construct() {
		add_action( 'after_switch_theme', array( $this, 'create_user_role' ), 10 );
		add_action( 'after_switch_theme', array( $this, 'add_user_caps' ), 11 );

		add_action( 'carbon_register_fields', array( $this, 'user_meta' ) );

		add_action( 'init', array( $this, 'register_admin_columns' ) );

		add_filter( 'user_has_cap', array( $this, 'user_has_cap' ), 10, 4 );

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
		// Add profile display panel
		Container::make( 'user_meta', 'PROFILE LINK' )
			->show_on_user_role( $this->role )
			->show_for( array(
				'relation' => 'OR',
				'administrator',
                'crb_assistant',
				$this->role
			) )
			->add_fields( array(
				Field::make( 'html', 'crb_profile_display', __( 'Profile', 'crb' ) )
					->set_html( 'crb_display_user_profile' ),
			) );
		Container::make( 'user_meta', 'ADDRESS' )
			->show_on_user_role( $this->role )
			->show_for( array(
				'relation' => 'OR',
				'administrator',
                'crb_assistant',
				$this->role
			) )
			->add_fields( array(
				Field::make( 'textarea', 'crb_address', __( 'Address', 'crb' ) )
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
				Carbon_Admin_Column::create( 'Address' )
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
				Carbon_Admin_Column::create( 'Locations' )
					->set_name( 'crb-locations-column' )
					->set_callback( array( $this, 'get_all_locations' ) ),
			) );
	}

	function get_all_locations( $user_id ) {
		global $Crb_Initialize_Location;

		$locations = $Crb_Initialize_Location->get_all_locations_by_facilitator( $user_id );

		array_walk( $locations, function( &$location_id ) {
			global $Crb_Initialize_Location;
			$link = $Crb_Initialize_Location->get_post_edit_link( $location_id );

			$location_id = $link;
		} );

		return implode( ', <br />', $locations );
	}

	/**
	 * Create User Role
	 */
	function create_user_role() {
		// Remove, so it allows modifications from the next line
		remove_role( $this->role );

		add_role( $this->role, $this->singular_name, array( 'read' => true, 'level_0' => true ) );
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
		 * Allow Facilitators to view entries
		 */
		foreach ( $objects as $slug => $object ) {
			$taxonomy_capabilities = array_merge( $taxonomy_capabilities, $object->get_taxonomy_capabilities() );

			$capabilities[$slug] = $object->get_post_type_capabilities();

			// First remove all possible Caps
			foreach ( $capabilities[$slug] as $capability ) {
				$role->remove_cap( $capability );
			}

			if ( in_array( $slug, array( 'crb_class', 'crb_recipe' ) ) ) {
				// Allow the Post type to appear in administration
				$role->add_cap( $capabilities[$slug]['edit_posts'] );
			}
		}

		// Allow new entries to be created with "Add New" button
		// $role->add_cap( $capabilities['crb_recipe']['create_posts'] );

		// Allow new entries to be published without approval
		// $role->add_cap( $capabilities['crb_recipe']['publish_posts'] );

		// Taxonomy capabilities
		foreach ( $taxonomy_capabilities as $taxonomy => $capabilities ) {
			foreach ( $capabilities as $capability ) {
				$role->remove_cap( $capability );
			}
		}

		// $role->add_cap( $taxonomy_capabilities['crb_recipe_flavor']['assign_terms'] );
		// $role->add_cap( $taxonomy_capabilities['crb_recipe_temperature']['assign_terms'] );
	}

	/**
	 * Allow the Dates to be visible, regardless of the fact that they are not editable.
	 * This will add the required Capability only on the specified page.
	 * This will make wordpress print a bunch of non-accessible URL, which would be hidden with CSS
	 */
	function user_has_cap( $allcaps, $caps, $args, $user_obj ) {
		global $pagenow;
		if (
			$pagenow == 'edit.php' &&
			isset( $_GET['post_type'] ) &&
			in_array( $_GET['post_type'], array( 'crb_class', 'crb_date', 'crb_recipe' ) ) &&
			Crb_Current_User()->is( $this->role )
		) {
			$allcaps['edit_posts'] = 1;
		}

		return $allcaps;
	}

	/**
	 * Return options for dropdown
	 */
	static function get_select_options() {
		$output = array( ' --- Select --- ' );

		$users = get_users( array(
			'role' => 'crb_facilitator',
		) );

		foreach ( $users as $user ) {
			$output[$user->ID] = $user->data->display_name;
		}

		return $output;
	}
}

new Crb_User_Initialize_Facilitator();
