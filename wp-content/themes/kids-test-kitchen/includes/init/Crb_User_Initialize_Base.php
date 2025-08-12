<?php

class Crb_User_Initialize_Base {
	function __construct() {
		add_action( 'init', array( $this, 'register_admin_columns' ) );
		add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );
		add_action( 'admin_init', array( $this, 'admin_redirect' ) );
		add_filter( 'map_meta_cap', array( $this, 'map_meta_cap' ), 10, 4 );
	}

	/**
	 * Cleanup the User Interface
	 */
	function dashboard_cleanup() {

		// Remove "Screen Options" top menu
		add_filter( 'screen_options_show_screen', '__return_false' );

		// Dashboard - remove menu Link
		add_action( 'admin_menu', function() {
			remove_menu_page( 'index.php' );
		} );

		// Dashboard - redirect to Locations screen
		add_action( 'admin_init', function() {
			global $pagenow;

			if ( $pagenow === 'index.php' ) {
				if ( Crb_Current_User()->is( 'crb_facilitator' ) ) {
					wp_redirect( admin_url( '/admin.php?page=schedule.php' ), 301 );
					exit;
				} elseif ( Crb_Current_User()->is( 'crb_session_admin' ) ) {
					wp_redirect( admin_url( '/admin.php?page=crbn-site-instructions.php' ), 301 );
					exit;
				}
			}
		} );

		// Remove Some admin bar elements
		add_action( 'add_admin_bar_menus', function() {
			remove_action( 'admin_bar_menu', 'wp_admin_bar_search_menu', 4 );
			remove_action( 'admin_bar_menu', 'wp_admin_bar_wp_menu', 10 );
		} );

		// Replace Dashboard text with Site Instructions
//		add_filter( 'gettext', function ( $text ) {
//			if ( $text === 'Dashboard' ) {
//				$text = __( 'Site Instructions', 'crb' );
//			}
//
//			return $text;
//		} );
	}

	/**
	 * Cleanup the User Interface in Profile page
	 */
	function profile_cleanup() {
		remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );
	}

	/**
	 * Admin columns for Users listing, when no Role filter is applied
	 */
	function register_admin_columns() {
		$role_filters_present = isset( $_GET ) && isset( $_GET['role'] );
		if ( $role_filters_present ) {
			return;
		}

		Carbon_Admin_Columns_Manager::modify_columns( 'user' )
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
			) );
	}

	/**
	 * Add User Role to Body Class
	 */
	function admin_body_class( $classes ) {
		$user = wp_get_current_user();
		$roles = (array) $user->roles;

		// Prefix Role Names with unique string
		array_walk( $roles, function( &$role_name ) {
			$role_name = 'crb_user_role-' . $role_name;
		} );

		$classes .= ' ' . implode( ' ', $roles );

		return $classes;
	}

	/**
	 * Check current admin page.
	 * Match URLs like 'edit.php?post_type=crb_location&all_posts=1'
	 * Match URLs like 'edit.php?post_type=crb_location&post_status=publish'
	 * Fallbacks to 'edit.php?post_type=crb_location', aka the "Mine" tab
	 *
	 * The tab links themselfs are hidden with css
	 */
	function admin_redirect() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		global $pagenow;

		if (
			$pagenow == 'edit.php' &&
			isset( $_GET['post_type'] ) &&
			in_array( $_GET['post_type'], array( 'crb_location', 'crb_class', 'crb_date' ) ) &&
			(
				isset( $_GET['all_posts'] ) ||
				isset( $_GET['post_status'] )
			) &&
			Crb_Current_User()->is( 'crb_session_admin' )
		) {
			wp_redirect( admin_url( '/edit.php?post_type=' . $_GET['post_type'] ), 301 );
			exit;
		} else if (
			$pagenow == 'post-new.php' &&
			isset( $_GET['post_type'] ) &&
			in_array( $_GET['post_type'], array( 'crb_date' ) ) &&
			Crb_Current_User()->is( 'crb_facilitator' )
		) {
			wp_redirect( admin_url( '/edit.php?post_type=' . $_GET['post_type'] ), 301 );
			exit;
		}
	}

	/**
	 * This will do internal decision making on which post to be editable, and which not
	 */
	function map_meta_cap( $caps, $cap, $user_id, $args ) {
		// Do not change Admin's priviledges
		if ( Crb_Current_User()->is( 'administrator' ) ) {
			return $caps;
		}

		// No post argument is passed
		if ( empty( $args ) || empty( $args[0] ) ) {
			return $caps;
		}

		// Post does not exists
		$post = get_post( $args[0] );
		if ( empty( $post ) ) {
			return $caps;
		}

		$post_type = get_post_type_object( $post->post_type );

		// Post type does not exists
		if ( empty( $post_type ) ) {
			return $caps;
		}

		// Only work on specific post types
		if ( ! in_array( $post_type->name, array( 'crb_location', 'crb_class', 'crb_date', 'crb_recipe' ) ) ) {
			return $caps;
		}

		$capabilities = $post_type->cap;

		/* If editing, deleting, or reading a post, get the post and post type object. */
		if ( in_array( $cap, array( $capabilities->edit_post, $capabilities->delete_post, $capabilities->read_post ) ) ) {

			/* Set an empty array for the caps. */
			$caps = array();

			/* If editing a post, assign the required capability. */
			if ( $cap == $capabilities->edit_post ) {
				if (
					$post->post_author == $user_id &&
					$post->post_type === 'crb_date' &&
					$post->post_status != 'publish'
				) {
					$caps[] = $capabilities->edit_posts;
				} elseif (
					$post->post_author == $user_id &&
					$post->post_type !== 'crb_date'
				) {
					$caps[] = $capabilities->edit_posts;
				} else {
					$caps[] = $capabilities->edit_others_posts;
				}
			}

			/* If deleting a post, assign the required capability. */
			elseif ( $cap == $capabilities->delete_post ) {
				if ( $post->post_author == $user_id ) {
					$caps[] = $capabilities->delete_posts;
				} else {
					$caps[] = $capabilities->delete_others_posts;
				}
			}

			/* If reading a private post, assign the required capability. */
			elseif ( $cap == $capabilities->read_post ) {
				if ( $post->post_status != 'private' ) {
					$caps[] = 'read';
				} elseif ( $post->post_author == $user_id ) {
					$caps[] = 'read';
				} else {
					$caps[] = $capabilities->read_private_posts;
				}
			}
		}

		/* Return the capabilities required by the user. */
		return $caps;
	}

}

global $Crb_User_Initialize_Base;
$Crb_User_Initialize_Base = new Crb_User_Initialize_Base();
