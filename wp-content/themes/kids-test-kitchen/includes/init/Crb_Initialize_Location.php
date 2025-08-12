<?php

use Carbon_Fields\Container\Container;
use Carbon_Fields\Field\Field;

/**
 * Initialize the Locations
 */
class Crb_Initialize_Location extends Crb_Initialize_Base {
	public $singular_name;
	public $plural_name;
	public $slug;
	public $is_edit_post_type_screen = false;

	public function __construct() {
		$this->singular_name = __( 'Location', 'crb' );
		$this->plural_name = __( 'Locations', 'crb' );
		$this->slug = 'crb_location';

		$this->base_init();

		

		add_filter( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
		add_filter( 'posts_orderby', array( $this, 'posts_orderby' ), 1000, 2 );

		// UI Modifications
		add_filter( 'post_type_labels_' . $this->slug, array( $this, 'modify_labels' ) );
		add_filter( 'enter_title_here', array( $this, 'enter_title_here' ) );
		add_filter( 'crb_submit_button_label_schedule', array( $this, 'label_schedule' ), 10 );
		add_filter( 'crb_submit_button_label_publish', array( $this, 'label_publish' ), 10 );
		add_filter( 'crb_submit_button_label_submit', array( $this, 'label_submit' ), 10 );
		add_filter( 'crb_submit_button_label_update', array( $this, 'label_update' ), 10 );

		
		
	}

	/* ==========================================================================
		# Main Interface
	========================================================================== */

		/**
		 * Returns Array of additional post type arguments
		 */
		public function get_post_type_args() {
			return array(
				'menu_icon' => 'dashicons-admin-multisite',
				'supports' => array( 'title', 'author' ),
			);
		}

		/**
		 * Register Carbon Custom Fields for the current post type
		 */
		public function register_custom_fields() {
			if ( ( Crb_Current_User()->is( 'administrator' ) ) || ( Crb_Current_User()->is( 'crb_assistant' ) ) ) {
				Container::make( 'post_meta', 'Organization' )
				//->show_on_user_role( $this->role )
				->show_on_post_type( $this->slug )
				->add_fields( array(
					Field::make( 'select', 'crb_user_organization', __( 'Select Organization', 'crb' ) )
								->set_options( array( 'Crb_Initialize_Org', 'get_select_options' ) )
								->set_required( true )
				) );
			}


			Container::make( 'post_meta', sprintf( __( '%s settings', 'crb' ), $this->singular_name ) )
				->show_on_post_type( $this->slug )
				->add_fields( array(
					Field::make( 'text', 'crb_location_address', __( 'Address', 'crb' ) )
						->set_required( true ),
					Field::make( 'text', 'crb_location_phone', __( 'Phone', 'crb' ) ),
					Field::make( 'text', 'crb_location_contact_name', __( 'Contact Person Name', 'crb' ) ),
					Field::make( 'textarea', 'crb_location_contact_email', __( 'Helpful location details: (parking, specific entry directions, etc)', 'crb' ) ),
				) );
		}

		/**
		 * Register Custom Taxonomies
		 */
		function register_taxonomies() {
			$taxonomy_slug = 'crb_location_region';
			$capabilities = $this->generate_taxonomy_capabilities( $taxonomy_slug );
			$this->taxonomy_capabilities[$taxonomy_slug] = $capabilities;

			register_taxonomy(
				$taxonomy_slug, # Taxonomy name
				array( $this->slug ), # Post Types
				array( # Arguments
					'labels'            => array(
						'name'              => __( 'Regions', 'crb' ),
						'singular_name'     => __( 'Region', 'crb' ),
						'search_items'      => __( 'Search Regions', 'crb' ),
						'all_items'         => __( 'All Regions', 'crb' ),
						'parent_item'       => __( 'Parent Region', 'crb' ),
						'parent_item_colon' => __( 'Parent Region:', 'crb' ),
						'view_item'         => __( 'View Region', 'crb' ),
						'edit_item'         => __( 'Edit Region', 'crb' ),
						'update_item'       => __( 'Update Region', 'crb' ),
						'add_new_item'      => __( 'Add New Region', 'crb' ),
						'new_item_name'     => __( 'New Region Name', 'crb' ),
						'menu_name'         => __( 'Regions', 'crb' ),
					),
					'hierarchical'      => true,
					'show_ui'           => true,
					'show_admin_column' => true,
					'query_var'         => true,
					'rewrite'           => array( 'slug' => 'location-region' ),
					'capabilities'      => array(
						'manage_terms' => 'manage_crb_location_region',
						'edit_terms'   => 'edit_crb_location_region',
						'delete_terms' => 'delete_crb_location_region',
						'assign_terms' => 'assign_crb_location_region',
					),
				)
			);
		}

		/**
		 * Register Custom Admin Columns
		 */
		function register_admin_columns() {
			$columns_to_be_removed = array();

			if ( Crb_Current_User()->is( 'crb_session_admin' ) ) {
				$columns_to_be_removed = array( 'taxonomy-crb_location_region', 'author' );
			}

			Carbon_Admin_Columns_Manager::modify_columns( 'post', array( $this->slug ) )
				->remove( $columns_to_be_removed )
				->add( array(
					Carbon_Admin_Column::create( 'Address' )
						->set_name( 'crb-location-address-column' )
						->set_field( '_crb_location_address' ),
					Carbon_Admin_Column::create( 'Phone' )
						->set_name( 'crb-location-phone-column' )
						->set_field( '_crb_location_phone' ),
					Carbon_Admin_Column::create( 'Contact Name' )
						->set_name( 'crb-location-contact-name-column' )
						->set_field( '_crb_location_contact_name' ),
					Carbon_Admin_Column::create( 'Location Details' )
						->set_name( 'crb-location-contact-email-column' )
						->set_field( '_crb_location_contact_email' ),
					Carbon_Admin_Column::create( 'Dates' )
						->set_name( 'crb-location-dates-column' )
						->set_callback( array( $this, 'column_callback_get_dates' ) ),
					// Carbon_Admin_Column::create( 'Passed Dates' )
					// 	->set_name( 'crb-location-passed-dates-column' )
					// 	->set_callback( array( $this, 'column_callback_get_passed_dates' ) ),
				) );
		}

		/**
		 * Return options for dropdown
		 */
		static function get_select_options( $post_type = '' ) {
			return PARENT::get_select_options( 'crb_location' );
		}

	/* ==========================================================================
		# Actions, Filters
	========================================================================== */

		/**
		 * Modify the default Post Type listing Order
		 */
		public function pre_get_posts( $query ) {
			$is_correct_screen = $this->is_edit_post_type_screen && ! isset( $_GET['orderby'] );

			if ( $is_correct_screen ) {
				$query->set( 'orderby', 'title' );
				$query->set( 'order', 'ASC' );
			}
		}

		/**
		 * Modify the default Post Type listing Order
		 * Add for example: "FIND_IN_SET( ID, '15,7,10,22' )"
		 */
		public function posts_orderby( $orderby , $query ) {
			$is_correct_screen = $this->is_edit_post_type_screen && ! isset( $_GET['orderby'] );

			if ( ! $is_correct_screen ) {
				return $orderby;
			}

			global $wpdb;

			$compare = '>=';

			$ordered_post_ids = $wpdb->get_col( $wpdb->prepare( "
				SELECT
					PM_class_location.meta_value as 'ID'
				FROM
					$wpdb->postmeta as PM_date_start
				INNER JOIN $wpdb->postmeta as PM_date_class
					ON PM_date_start.post_id = PM_date_class.post_id
				INNER JOIN $wpdb->postmeta as PM_class_location
					ON PM_date_class.meta_value = PM_class_location.post_id
				WHERE
					PM_date_start.meta_key = '_crb_date_start' AND PM_date_start.meta_value $compare '%s'
					AND
					PM_date_class.meta_key = '_crb_date_class'
					AND
					PM_class_location.meta_key = '_crb_class_location'

				ORDER BY CAST(PM_date_start.meta_value AS DATE) ASC
			", date('Y-m-d') ) );

			$ordered_post_ids = array_unique( $ordered_post_ids );

			$all_other_post_ids = get_posts( array(
				'post_type' => 'crb_location',
				'post__not_in' => $ordered_post_ids,
				'post_status' => 'any',
				'fields' => 'ids',
				'posts_per_page' => -1,
				'order' => 'ASC',
				'orderby' => 'title',
			) );

			$sorted_post_ids = array_merge( $ordered_post_ids, $all_other_post_ids );
			$sorted_post_ids = implode( ',', $sorted_post_ids );

			$orderby = " FIND_IN_SET( $wpdb->posts.id, '$sorted_post_ids' ) ";

			return $orderby;
		}

	/* ==========================================================================
		# Callbacks
	========================================================================== */

		/**
		 * Display all Dates assigned to the current Class, that are scheduled
		 */
		function column_callback_get_dates( $location_id ) {
			$dates = $this->get_all_dates_for_location( $location_id, '>=' );

			array_walk( $dates, function( &$date_id ) {
				$name = carbon_get_post_meta( $date_id, 'crb_date_start' );

				$date_obj = new Crb_Date( $date_id );
				$facilitator_id = $date_obj->get_facilitator_id();

				$date_id = $this->get_post_edit_link_with_custom_name( $date_id, $name );
				if ( !empty( $facilitator_id ) ) {
					$date_id .= ' (' . $this->get_user_edit_link( $facilitator_id ) . ')';
				}
			});

			return implode( ',<br />', $dates );
		}

		/**
		 * Display all Dates assigned to the current Class, that are in the past
		 */
		function column_callback_get_passed_dates( $location_id ) {
			$dates = $this->get_all_dates_for_location( $location_id, '<' );

			array_walk( $dates, function( &$date_id ) {
				$name = carbon_get_post_meta( $date_id, 'crb_date_start' );

				$date_obj = new Crb_Date( $date_id );
				$facilitator_id = $date_obj->get_facilitator_id();

				$date_id = $this->get_post_edit_link_with_custom_name( $date_id, $name );
				if ( !empty( $facilitator_id ) ) {
					$date_id .= ' (' . $this->get_user_edit_link( $facilitator_id ) . ')';
				}
			});

			return implode( ',<br />', $dates );
		}

	/* ==========================================================================
		# Helpers
	========================================================================== */

		/**
		 * Get all Dates with the specified Facilitator directly, or undirectly throught the Class relation
		 */
		function get_all_locations_by_facilitator( $facilitator_id ) {
			$transient_key = 'crb_facilitator_' . $facilitator_id . '_locations';
			$cache = get_transient( $transient_key );
			if ( ! empty( $cache ) ) {
				return $cache;
			}

			global $wpdb;

			$subquery_classes_with_dates_overridding_facilitator = $wpdb->prepare( "
						SELECT
							PM_Class_Location.meta_value
						FROM $wpdb->posts as P_Date
						INNER JOIN $wpdb->postmeta as PM_Date_Facilitator
							on P_Date.ID = PM_Date_Facilitator.post_id
						INNER JOIN $wpdb->postmeta as PM_Date_Class
							on P_Date.ID = PM_Date_Class.post_id
						INNER JOIN $wpdb->postmeta as PM_Class_Location
							on PM_Date_Class.meta_value = PM_Class_Location.post_id
						WHERE
							P_Date.post_type = 'crb_date'
							AND PM_Date_Facilitator.meta_key = '_crb_date_facilitator'
							AND PM_Date_Facilitator.meta_value = '%s'
							AND PM_Date_Class.meta_key = '_crb_date_class'
							AND PM_Class_Location.meta_key = '_crb_class_location'
			", $facilitator_id );

			$subquery_classes_with_facilitator = $wpdb->prepare( "
						SELECT
							PM_Class_Location.meta_value
						FROM $wpdb->posts as P_Date
						INNER JOIN $wpdb->postmeta as PM_Date_Class
							on P_Date.ID = PM_Date_Class.post_id
						INNER JOIN $wpdb->postmeta as PM_Class_Facilitator
							on PM_Date_Class.meta_value = PM_Class_Facilitator.post_id
						INNER JOIN $wpdb->postmeta as PM_Class_Location
							on PM_Date_Class.meta_value = PM_Class_Location.post_id
						WHERE
							P_Date.post_type = 'crb_date'
							AND PM_Date_Class.meta_key = '_crb_date_class'
							AND PM_Class_Facilitator.meta_key = '_crb_class_facilitator'
							AND PM_Class_Facilitator.meta_value = '%s'
							AND PM_Class_Location.meta_key = '_crb_class_location'
			", $facilitator_id );

			$location_ids = $wpdb->get_col( "
				SELECT locations.ID
				FROM $wpdb->posts AS locations
				WHERE locations.post_type = 'crb_location'
				AND (
					locations.ID IN (
						$subquery_classes_with_dates_overridding_facilitator
					)
					OR
					locations.ID IN (
						$subquery_classes_with_facilitator
					)
				)
				GROUP BY locations.ID
			" );

			set_transient( $transient_key, $location_ids, HOUR_IN_SECONDS );

			return $location_ids;
		}

		/**
		 * Return Dates for location, either in the passed, or in the feature
		 */
		function get_all_dates_for_location( $location_id, $compare = '>=' ) {
			global $wpdb;

			$date_ids = $wpdb->get_col( $wpdb->prepare( "
				SELECT
					P_Date.ID
				FROM
					$wpdb->posts as P_Date
				INNER JOIN $wpdb->postmeta as PM_date_start
					ON P_Date.ID = PM_date_start.post_id
				INNER JOIN $wpdb->postmeta as PM_date_class
					ON P_Date.ID = PM_date_class.post_id
				INNER JOIN $wpdb->postmeta as PM_class_location
					ON PM_date_class.meta_value = PM_class_location.post_id
				WHERE
					P_Date.post_type = 'crb_date'
					AND
					PM_date_start.meta_key = '_crb_date_start' AND PM_date_start.meta_value $compare '%s'
					AND
					PM_date_class.meta_key = '_crb_date_class'
					AND
					PM_class_location.meta_key = '_crb_class_location' AND PM_class_location.meta_value = '%s'
				ORDER BY CAST(PM_date_start.meta_value AS date) ASC
			", date('Y-m-d'), $location_id ) );

			return $date_ids;
		}

	/* ==========================================================================
		# UI Modifications
	========================================================================== */

		// Change Some Labels Throughout Admin
		function modify_labels( $labels ) {
			return $labels;
		}

		// Change Some Labels Throughout Admin
		function enter_title_here( $title ) {
			global $pagenow;
			if ( $pagenow == 'post-new.php' && !empty( $_GET['post_type'] ) && $_GET['post_type'] == $this->slug ) {
				$title = __( 'Enter Location Name here', 'crb' );
			}

			return $title;
		}

		// Change "Schedule" button Text
		function label_schedule( $label ) {
			if ( get_post_type() == $this->slug ) {
				$label = __( 'Schedule Location', 'crb' );
			}

			return $label;
		}

		// Change "Publish" button Text
		function label_publish( $label ) {
			if ( get_post_type() == $this->slug ) {
				$label = __( 'Create Location', 'crb' );
			}

			return $label;
		}

		// Change "Submit" button Text
		function label_submit( $label ) {
			if ( get_post_type() == $this->slug ) {
				$label = __( 'Submit Location for Review', 'crb' );
			}

			return $label;
		}

		// Change "Update" button Text
		function label_update( $label ) {
			if ( get_post_type() == $this->slug ) {
				$label = __( 'Update Location', 'crb' );
			}

			return $label;
		}

}

global $Crb_Initialize_Location;
$Crb_Initialize_Location = new Crb_Initialize_Location();
