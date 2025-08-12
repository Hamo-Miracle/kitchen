<?php

/**
 *
 */
class Crb_User_Initialize_Admin {
	public $role = 'administrator';

	function __construct() {
		add_action( 'after_switch_theme', array( $this, 'add_user_caps' ), 11 );

		// User Specific Hooks
		if ( Crb_Current_User()->is( $this->role ) ) {
		}

        add_action('admin_head','change_profile_style');
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

		/**
		 * Allow admins to edit all CPTs
		 */
		foreach ( $objects as $slug => $object ) {
			$taxonomy_capabilities = array_merge( $taxonomy_capabilities, $object->get_taxonomy_capabilities() );

			$capabilities = $object->get_post_type_capabilities();

			// First remove all possible Caps
			foreach ( $capabilities as $capability ) {
				$role->remove_cap( $capability );

				// Skip date, since it would not be editable from administration
				if ( in_array( $slug, array( 'crb_location', 'crb_class', 'crb_recipe' ) ) ) {
					$role->add_cap( $capability );
				}
			}
		}

		// Taxonomy capabilities
		foreach ( $taxonomy_capabilities as $taxonomy => $capabilities ) {
			foreach ( $capabilities as $capability ) {
				$role->add_cap( $capability );
			}
		}
	}
}

new Crb_User_Initialize_Admin();
