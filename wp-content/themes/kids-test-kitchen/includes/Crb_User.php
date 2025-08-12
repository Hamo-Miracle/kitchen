<?php

/**
 * User Helper functions
 * not used currently
 */
class Crb_User {
    private $summary_dates_compare = '>=';

    function __construct( $user_id ) {
        $this->user = get_user_by( 'ID', $user_id );
    }

    /*static function get_name( $user_id ) {
        $user = get_user_by( 'id', $user_id );
        if ( empty( $user ) ) {
            return $user;
        }

        return $user->name;
    }*/

    /**
     * Check if the current user is from specific role
     * Example:
     *  is( 'administrator' )
     */
    function is( $role ) {
        if ( ! $this->is_user_valid() ) {
            return false;
        }

        return in_array( $role, (array) $this->user->roles );
    }

    /**
     * Instance validation
     */
    function is_user_valid() {
        if ( empty( $this->user ) ) {
            return false;
        }

        return true;
    }

    /**
     * Return User ID
     */
    function get_id() {
        if ( ! $this->is_user_valid() ) {
            return false;
        }

        return $this->user->ID;
    }

    /**
     * Return User Obj
     */
    function get_user() {
        if ( ! $this->is_user_valid() ) {
            return false;
        }

        return $this->user;
    }

    /**
     * Return summary for the user with all of the locations created by him
     */
    function get_summary() {
        if ( ! $this->is_user_valid() ) {
            return false;
        }

        $user_id = $this->get_id();

        $locations = $this->get_summary_locations( $user_id );

        $show_user_info = false;

        ob_start();
        include( locate_template( 'fragments/locations-classes-dates-table.php' ) );
        $html = ob_get_clean();

        return $html;
    }

    function get_summary_dates_compare() {
        return $this->summary_dates_compare;
    }

    function set_summary_dates_compare( $compare = '>=' ) {
        $this->summary_dates_compare = $compare;

        return $this;
    }

    private function get_summary_locations( $user_id ) {
        $locations = get_posts( array(
            'post_type' => 'crb_location',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'author' => $this->get_id(),
            'fields' => 'ids',
        ) );

        if ( empty( $locations ) ) {
            return array();
        }

        $locations = array_flip( $locations );

        array_walk( $locations, function( &$location_value, $location_id ) {
            $classes = $this->get_summary_classes( $location_id );

            $location_value = $classes;
        } );

        return $locations;
    }

    private function get_summary_classes( $location_id ) {
        $classes = get_posts( array(
            'post_type' => 'crb_class',
            'post_status' => 'any',
            'posts_per_page' => -1,
            // 'author' => $this->get_id(),
            'meta_key' => '_crb_class_location',
            'meta_value' => $location_id,
            'meta_compare' => '=',
            'fields' => 'ids',
        ) );

        if ( ! empty( $classes ) ) {
            $classes = array_flip( $classes );

            array_walk( $classes, function( &$class_value, $class_id ) {
                $dates = $this->get_summary_dates( $class_id );

                $class_value = $dates;
            } );
        } else {
            $classes = array();
        }

        return $classes;
    }

    private function get_summary_dates( $class_id ) {
        $dates = get_posts( array(
            'post_type' => 'crb_date',
            'post_status' => 'any',
            'posts_per_page' => -1,
            // 'author' => $this->get_id(),
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_crb_date_class',
                    'value' => $class_id,
                    'compare' => '=',
                ),
                array(
                    'key' => '_crb_date_start',
                    // Yesterday
                    'value' => date( 'Y-m-d', time() - DAY_IN_SECONDS ),
                    'compare' => $this->get_summary_dates_compare(),
                ),
            ),
            'fields' => 'ids',
        ) );

        if ( empty( $dates ) ) {
            $dates = array();
        }

        return $dates;
    }
}

function crb_get_current_user_summary() {
    if ( !empty( $_GET['user_id'] ) ) {
        $user_id = $_GET['user_id'];
    } else {
        $user_id = get_current_user_id();
    }

    $Crb_User = new Crb_User( $user_id );

    $label = sprintf( '<label>%s</label>', 'Upcoming Sessions' );
    $html = $label . $Crb_User->get_summary();

    // Display Past Sessions
    // $Crb_User->set_summary_dates_compare( '<' );
    // $label = sprintf( '<label>%s</label>', 'Past Sessions' );
    // $html .= $label . $Crb_User->get_summary();

    return $html;
}

