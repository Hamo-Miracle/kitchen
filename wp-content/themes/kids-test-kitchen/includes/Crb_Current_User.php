<?php

/**
 * Current User
 */
class Crb_Current_User extends Crb_User {
	// Inherits the Crb_User methods
}

function Crb_Current_User() {
	static $instance;

	if ( $instance === null ) {
		$user_id = 0;
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
		}

		$instance = new Crb_User( $user_id );
	}

	return $instance;
}
