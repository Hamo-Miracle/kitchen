<?php

/**
 * Recipe Entry
 *
 * @param $recipe_id  - Date Post ID
 * @param $recipe_obj - Date Post Obj
 */
class Crb_Recipe {
	private $recipe_id = 0;
	private $recipe_obj;
	private $last_use = array();

	function __construct( $recipe_id ) {
		$this->recipe_id = absint( $recipe_id );

		$this->recipe_obj = get_post( $this->recipe_id );
	}

	function get_admin_label( $before = '(', $after = ')' ) {
		$title_pieces = array(
			$this->get_title(),
			$this->get_flavors( $before, $after ),
			$this->get_temperatures( $before, $after ),
			$this->get_seasons( $before, $after ),
			$this->get_alerts( $before, $after ),
			$this->get_modifications( $before, $after ),
		);

		return implode( ' ', array_filter( $title_pieces ) );
	}

	function get_title() {
		if ( empty( $this->recipe_obj ) || empty( $this->recipe_obj->post_title ) ) {
			return '';
		}

		return $this->recipe_obj->post_title;
	}

//	function get_flavors( $before = '(', $after = ')' ) {
//		$flavor = wp_get_post_terms( $this->recipe_id, 'crb_recipe_flavor', array( 'fields' => 'names' ) );
//
//		if ( empty( $flavor ) ) {
//			return;
//		}
//
//		return $before . implode( ' ', $flavor ) . $after;
//	}

    function get_flavors( $before = '(', $after = ')' , $cache_on = true) {
        $cache_key = 'crb_recipe_flavor_' . $this->recipe_id;
        $flavor = null;

        if( $cache_on ){
            $flavor = wp_cache_get( $cache_key, 'crb_recipe_flavor' );
        }

        if (empty($flavor)){
            $flavor = wp_get_post_terms( $this->recipe_id, 'crb_recipe_flavor', array( 'fields' => 'names' ) );

            if( $cache_on ){
                wp_cache_add( $cache_key, $flavor, 'crb_recipe_flavor', 15);
            }

            if ( empty( $flavor ) ) {
                return;
            }
        }

        return $before . implode( ' ', $flavor ) . $after;
    }


//	function get_temperatures( $before = '(', $after = ')' ) {
//		$temperature = wp_get_post_terms( $this->recipe_id, 'crb_recipe_temperature', array( 'fields' => 'names' ) );
//
//		if ( empty( $temperature ) ) {
//			return;
//		}
//
//		return $before . implode( ' ', $temperature ) . $after;
//	}

    function get_temperatures( $before = '(', $after = ')' , $cache_on = true) {
        $cache_key = 'crb_recipe_temperature_' . $this->recipe_id;
        $temperature = null;

        if( $cache_on ){
            $temperature = wp_cache_get( $cache_key, 'crb_recipe_temperature' );
        }

        if (empty($temperature)){
            $temperature = wp_get_post_terms( $this->recipe_id, 'crb_recipe_temperature', array( 'fields' => 'names' ) );

            if( $cache_on ){
                wp_cache_add( $cache_key, $temperature, 'crb_recipe_temperature', 15);
            }

            if ( empty( $temperature ) ) {
                return;
            }
        }

        return $before . implode( ' ', $temperature ) . $after;
    }

//    function get_seasons( $before = '(', $after = ')' ) {
//        $season = wp_get_post_terms( $this->recipe_id, 'crb_recipe_season', array( 'fields' => 'names' ) );
//
//        if ( empty( $season ) ) {
//            return;
//        }
//
//        return $before . implode( ' ', $season ) . $after;
//    }


    function get_seasons( $before = '(', $after = ')' , $cache_on = true) {
        $cache_key = 'crb_recipe_season_' . $this->recipe_id;
        $season = null;

        if( $cache_on ){
            $season = wp_cache_get( $cache_key, 'crb_recipe_season' );
        }

        if (empty($season)){
            $season = wp_get_post_terms( $this->recipe_id, 'crb_recipe_season', array( 'fields' => 'names' ) );

            if( $cache_on ){
                wp_cache_add( $cache_key, $season, 'crb_recipe_season', 15);
            }

            if ( empty( $season ) ) {
                return;
            }
        }

        return $before . implode( ' ', $season ) . $after;
    }

//    function get_alerts( $before = '(', $after = ')' ) {
//        $alert = wp_get_post_terms( $this->recipe_id, 'crb_recipe_alert', array( 'fields' => 'names' ) );
//
//        if ( empty( $alert ) ) {
//            return;
//        }
//
//        return $before . implode( ' ', $alert ) . $after;
//    }

    function get_alerts( $before = '(', $after = ')' , $cache_on = true) {
        $cache_key = 'crb_recipe_alert_' . $this->recipe_id;
        $alert = null;

        if( $cache_on ){
            $alert = wp_cache_get( $cache_key, 'crb_recipe_alert' );
        }

        if (empty($alert)){
            $alert = wp_get_post_terms( $this->recipe_id, 'crb_recipe_alert', array( 'fields' => 'names' ) );

            if( $cache_on ){
                wp_cache_add( $cache_key, $alert, 'crb_recipe_alert', 15);
            }

            if ( empty( $alert ) ) {
                return;
            }
        }

        return $before . implode( ' ', $alert ) . $after;
    }

//    function get_modifications( $before = '(', $after = ')' ) {
//        $modification = wp_get_post_terms( $this->recipe_id, 'crb_recipe_modification', array( 'fields' => 'names' ) );
//
//        if ( empty( $modification ) ) {
//            return;
//        }
//
//        return $before . implode( ' ', $modification ) . $after;
//    }

    function get_modifications( $before = '(', $after = ')' , $cache_on = true) {
        $cache_key = 'crb_recipe_modification_' . $this->recipe_id;
        $modification = null;

        if( $cache_on ){
            $modification = wp_cache_get( $cache_key, 'crb_recipe_modification' );
        }

        if (empty($modification)){
            $modification = wp_get_post_terms( $this->recipe_id, 'crb_recipe_modification', array( 'fields' => 'names' ) );

            if( $cache_on ){
                wp_cache_add( $cache_key, $modification, 'crb_recipe_modification', 15);
            }

            if ( empty( $modification ) ) {
                return;
            }
        }

        return $before . implode( ' ', $modification ) . $after;
    }


	function get_last_use_date( $class_id ) {
		if ( empty( $class_id ) ) {
			return '';
		}

		if ( ! empty( $this->last_use[$class_id] ) ) {
			return $this->last_use[$class_id];
		}

		global $wpdb;

		$last_use = $wpdb->get_var( "
			SELECT Complex_Date.meta_value
			FROM $wpdb->posts as Class
			INNER JOIN $wpdb->postmeta as Class_PM_Location
				ON Class.ID = Class_PM_Location.post_id
			INNER JOIN $wpdb->postmeta as Location_Classes
				ON Class_PM_Location.meta_value = Location_Classes.meta_value
			INNER JOIN $wpdb->postmeta as Class_Recipe
				ON Location_Classes.post_id = Class_Recipe.post_id
			INNER JOIN $wpdb->postmeta as Complex_Date
				ON
					CONCAT( '_crb_class_dates_-_start_', SUBSTR( Class_Recipe.meta_key, 27 ) ) = Complex_Date.meta_key
					AND
					Location_Classes.post_id = Complex_Date.post_id

			WHERE
				Class.ID = '$class_id'
					AND
				Class.post_type = 'crb_class'
					AND
				Class_PM_Location.meta_key = '_crb_class_location'
					AND
				Location_Classes.meta_key = '_crb_class_location'
					AND
				Class_Recipe.meta_key LIKE '_crb_class_dates_-_recipe_%'
					AND
				Class_Recipe.meta_value = '{$this->recipe_id}'
					AND
				Complex_Date.meta_key LIKE '_crb_class_dates_-_start_%'

			ORDER BY Complex_Date.meta_value DESC
			LIMIT 1
		" );

		// Replace false values like null with an empty string
		if ( empty( $last_use ) ) {
			$last_use = '';
		}

		$this->last_use[$class_id] = $last_use;

		return $last_use;
	}

	function get_last_use_nice( $class_id ) {
		if ( empty( $class_id ) ) {
			return '';
		}

		$last_use = $this->get_last_use_date( $class_id );

		if ( empty( $last_use ) ) {
			return '';
		}

		$after = ' ago';
		if ( time() < strtotime( $last_use ) ) {
			$after = ' from now';
		}

		$last_use_diff = ' (Last used: ' . human_time_diff( time(), strtotime( $last_use ) ) . $after . ')';

		return $last_use_diff;
	}

	function get_last_use_status( $class_id, $date = '' ) {
		$status = 'green';

		if ( empty( $class_id ) ) {
			return $status;
		}

		$last_use = $this->get_last_use_date( $class_id );
		if ( empty( $last_use ) ) {
			return $status;
		}

		$now = strtotime( date( 'Y-m-d' ) );

		$interval_18_months = new DateInterval( 'P1Y6M' );
		$some_time_ago = new DateTime( date( 'Y-m-d' ) );
		$some_time_ago = $some_time_ago->sub( $interval_18_months )->format( 'U' );

		$last_use_timestamp = strtotime( $last_use );

		if ( $last_use_timestamp < $some_time_ago ) {
			$status = 'green';
		} else {
			$status = 'red';
		}

		// Check if current time matches the last use
		if ( ! empty( $date ) ) {
			if ( strtotime( $date ) == $last_use_timestamp ) {
				$status = 'yellow';
			}
		}

		return $status;
	}

	function get_last_use( $class_id, $date = '' ) {
		return array(
			'date' => $this->get_last_use_date( $class_id ),
			'text' => $this->get_last_use_nice( $class_id ),
			'status' => $this->get_last_use_status( $class_id, $date ),
		);
	}
}
