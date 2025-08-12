<?php
/**
 * User module helper functions.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

/**
 * Post Helper functions
 */
class Air_WP_Sync_User_Helpers {

	/**
	 * Get available user roles keys.
	 *
	 * @return string[]
	 */
	public static function get_available_user_roles_keys() {
		$roles = wp_roles();

		if ( ! $roles || ! $roles->get_names() ) {
			return array();
		}

		return array_keys( $roles->get_names() );
	}
}
