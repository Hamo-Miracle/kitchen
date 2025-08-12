<?php

/**
 * Boost various transient and other caching solutions from 3rd party locations
 */
class Crb_Cache_Booster {
	function __construct() {
	}

	function flush( $target ) {
		global $wpdb;
		switch ( $target ) {
			// target transient created in get_all_classes_by_facilitator()
			case 'sync_complex_with_date_posts':
				$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name like '%crb_facilitator_%_classes%'" );
				break;

			default:
				# code...
				break;
		}
	}
}

function Crb_Cache_Booster() {
	return new Crb_Cache_Booster();
}
