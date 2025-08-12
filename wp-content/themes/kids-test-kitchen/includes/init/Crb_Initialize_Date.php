<?php

use Carbon_Fields\Container\Container;
use Carbon_Fields\Field\Field;

/**
 * Initialize the Dates
 *
 * Dates would not be directly Editable from other post types, however they will be changed when Classes entries are updated.
 * This was initially admin/session admin editable,
 * however now it is fully integrated as part of the Complex field in Class edit screen.
 */
class Crb_Initialize_Date extends Crb_Initialize_Base {
	public $singular_name;
	public $plural_name;
	public $slug;
	public $is_edit_post_type_screen = false;

	public function __construct() {
		$this->singular_name = __( 'Date', 'crb' );
		$this->plural_name = __( 'Dates', 'crb' );
		$this->slug = 'crb_date';

		$this->base_init();

		add_action( 'updated_post_meta', array( $this, 'updated_post_meta' ), 10, 4 );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 3 );
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ), 10, 4 );
	}

	/**
	 * Returns Array of additional post type arguments
	 */
	public function get_post_type_args() {
		return array(
			'menu_icon' => 'dashicons-calendar-alt',
			'supports' => array( 'author' ),
		);
	}

	/**
	 * Register Carbon Custom Fields for the current post type
	 */
	public function register_custom_fields() {
		Container::make( 'post_meta', sprintf( __( '%s settings', 'crb' ), $this->singular_name ) )
			->show_on_post_type( $this->slug )
			->add_fields( array(
				Field::make( 'date', 'crb_date_start', __( 'Date', 'crb' ) )
					->set_required( true ),
				Field::make( 'wickedpicker', 'crb_date_time_start', __( 'Time Start', 'crb' ) )
					->set_required( true ),
				Field::make( 'wickedpicker', 'crb_date_time_end', __( 'Time End', 'crb' ) )
					->set_required( true ),
				Field::make( 'select', 'crb_date_class', __( 'Select Class', 'crb' ) )
					->set_required( true )
					->set_options( array( 'Crb_Initialize_Class', 'get_select_options' ) ),
				Field::make( 'complex', 'crb_additional_recipes', __( 'Additional recipes on this date', 'crb' ) )
					->setup_labels( array(
						'singular_name' => __( 'Recipe', 'crb' ),
						'plural_name'   => __( 'Recipes', 'crb' ),
					) )
					->add_fields( array(
						Field::make( 'wickedpicker', 'time_start', __('Recipe Start Time', 'crb') )
							->set_required( true )
							->set_width( 50 ),
						Field::make( 'wickedpicker', 'time_end', __('Recipe End Time', 'crb') )
							->set_required( true )
							->set_width( 50 ),
						Field::make( 'select_recipe', 'recipe', __('Select Recipe', 'crb') )
							->set_width( 50 )
							->set_options( array( 'Crb_Initialize_Recipe', 'get_select_options' ) ),
						Field::make( 'select', 'facilitator', __('Select Facilitator', 'crb') )
							->help_text( 'You can use this field to override the Facilitator setup for the related Class.' )
							->set_width( 50 )
							->set_options( array( 'Crb_User_Initialize_Facilitator', 'get_select_options' ) )
					) ),
			) );

		if ( Crb_Current_User()->is( 'administrator' ) ) {
			Container::make( 'post_meta', sprintf( __( '%s settings (Admin only)', 'crb' ), $this->singular_name ) )
				->show_on_post_type( $this->slug )
				->add_fields( array(
					Field::make( 'select', 'crb_date_recipe', __( 'Select Recipe', 'crb' ) )
						->set_options( array( 'Crb_Initialize_Recipe', 'get_select_options' ) ),
					Field::make( 'select', 'crb_date_facilitator', __( 'Select Facilitator', 'crb' ) )
						->help_text( 'You can use this field to override the Facilitator setup for the related Class.' )
						->set_options( array( 'Crb_User_Initialize_Facilitator', 'get_select_options' ) ),
				) );
		}
	}

	/**
	 * Register Custom Taxonomies
	 */
	function register_taxonomies() {
	}

	/**
	 * Register Custom Admin Columns
	 */
	function register_admin_columns() {
		$columns_to_be_removed = array( 'date' );

		if ( ! Crb_Current_User()->is( 'administrator' ) ) {
			$columns_to_be_removed = array( 'date', 'author' );
		}

		Carbon_Admin_Columns_Manager::modify_columns( 'post', array( $this->slug ) )
			->remove( $columns_to_be_removed )
			->add( array(
				Carbon_Admin_Column::create( 'Facilitator' )
					->set_name( 'crb-facilitator-column' )
					->set_callback( array( $this, 'column_callback_get_facilitator' ) ),
				Carbon_Admin_Column::create( 'Recipe' )
					->set_name( 'crb-recipe-column' )
					->set_callback( array( $this, 'column_callback_get_recipe' ) ),
				Carbon_Admin_Column::create( 'Class' )
					->set_name( 'crb-class-column' )
					->set_callback( array( $this, 'column_callback_get_class' ) ),
				Carbon_Admin_Column::create( 'Location' )
					->set_name( 'crb-location-column' )
					->set_callback( array( $this, 'column_callback_get_location' ) ),
				Carbon_Admin_Column::create( 'Date' )
					->set_name( 'crb-date-start-column' )
					->set_field( '_crb_date_start' ),
				Carbon_Admin_Column::create( 'Begins at' )
					->set_name( 'crb-date-time-start-column' )
					->set_field( '_crb_date_time_start' ),
				Carbon_Admin_Column::create( 'Ends at' )
					->set_name( 'crb-date-time-end-column' )
					->set_field( '_crb_date_time_end' ),
			) );
	}

	/**
	 * Return options for dropdown
	 */
	static function get_select_options( $post_type = '' ) {
		return PARENT::get_select_options( 'crb_date' );
	}

	/**
	 * Dynamically update post title, depending on Custom Field Values
	 */
	function updated_post_meta( $meta_id, $post_id, $meta_key, $meta_value ) {
		// Title
		if ( in_array( $meta_key, array( '_crb_date_start', '_crb_date_class' ) ) ) {
			$date = carbon_get_post_meta( $post_id, 'crb_date_start' );
			$class = carbon_get_post_meta( $post_id, 'crb_date_class' );

			if ( empty( $date ) || empty( $class ) ) {
				return;
			}

			$title = get_the_title( $class ) . ' - ' . $date;

			$update_status = wp_update_post( array(
				'ID' => $post_id,
				'post_title' => $title,
			) );
		}
	}

	/**
	 * Dynamically update post title, depending on Custom Field Values
	 */
	function save_post( $post_id, $post, $update ) {
		$date = carbon_get_post_meta( $post_id, 'crb_date_start' );
		$class = carbon_get_post_meta( $post_id, 'crb_date_class' );
		$recipe_id = carbon_get_post_meta( $post_id, 'crb_date_recipe' );

		if ( empty( $date ) ) {
			return;
		}

		// Title
		if ( !empty( $class ) ) {
			$title = get_the_title( $class ) . ' - ' . $date;

			remove_action( 'save_post', array( $this, 'save_post' ), 10, 3 );

			$update_status = wp_update_post( array(
				'ID' => $post_id,
				'post_title' => $title,
			) );
		}
	}

	/**
	 * Get all Dates with the specified Facilitator directly, or undirectly throught the Class relation
	 */
	function get_all_dates_by_facilitator( $facilitator_id ) {
		$transient_key = 'crb_facilitator_' . $facilitator_id . '_dates';
		$cache = get_transient( $transient_key );
		if ( ! empty( $cache ) ) {
			return $cache;
		}

		global $wpdb;

		$subquery_dates_with_facilitator = $wpdb->prepare( "
					SELECT P_Date.ID as X
					FROM $wpdb->posts as P_Date
					INNER JOIN $wpdb->postmeta as PM_Date_Facilitator
						on P_Date.ID = PM_Date_Facilitator.post_id
					WHERE
						P_Date.post_type = 'crb_date'
						AND PM_Date_Facilitator.meta_key = '_crb_date_facilitator'
						AND PM_Date_Facilitator.meta_value = '%s'
		", $facilitator_id );
		$subquery_dates_with_classed_with_facilitator = $wpdb->prepare( "
					SELECT P_Date.ID as X
					FROM $wpdb->posts as P_Date
					INNER JOIN $wpdb->postmeta as PM_Class
						on P_Date.ID = PM_Class.post_id
					INNER JOIN $wpdb->postmeta as PM_Class_Facilitator
						on PM_Class.meta_value = PM_Class_Facilitator.post_id
					WHERE
						P_Date.post_type = 'crb_date'
						AND PM_Class.meta_key = '_crb_date_class'
						AND PM_Class_Facilitator.meta_key = '_crb_class_facilitator'
						AND PM_Class_Facilitator.meta_value = '%s'
		", $facilitator_id );

		$date_ids = $wpdb->get_col( "
			SELECT dates.ID
			FROM $wpdb->posts AS dates
			WHERE dates.post_type = 'crb_date'
			AND (
				dates.ID IN (
					$subquery_dates_with_facilitator
				)
				OR
				dates.ID IN (
					$subquery_dates_with_classed_with_facilitator
				)
			)
			GROUP BY dates.ID
		" );

		set_transient( $transient_key, $date_ids, HOUR_IN_SECONDS );

		return $date_ids;
	}

	/**
	 * On Dates screen, when the current user is Facilitator, show only Dates for the current Facilitator
	 * Modify the default Post Type listing Order
	 */
	function pre_get_posts( $query ) {
		if (
			$this->is_edit_post_type_screen &&
			Crb_Current_User()->is( 'crb_facilitator' )
		) {
			$facilitator_id = Crb_Current_User()->get_id();

			// This requires pure SQL, since the Facilitator may be kept in the Date or in the Class post type
			$date_ids = $this->get_all_dates_by_facilitator( $facilitator_id );

			$query->set( 'post__in', $date_ids );
		}

		add_filter( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

		if (
			$this->is_edit_post_type_screen &&
			! isset( $_GET['orderby'] )
		) {

			$query->set( 'orderby', 'meta_value' );
			$query->set( 'order', 'ASC' );
			$query->set( 'meta_key', '_crb_date_start' );
		}
	}

	/**
	 * Display the Facilitator assigned to the current Date
	 */
	function column_callback_get_facilitator( $post_id ) {
		$date = new Crb_Date( $post_id );

		$facilitator_id = $date->get_facilitator_id();

		return $this->get_user_edit_link( $facilitator_id );
	}

	/**
	 * Display the Recipe assigned to the current Date
	 */
	function column_callback_get_recipe( $post_id ) {
		$date = new Crb_Date( $post_id );

		$recipe_id = $date->get_recipe_id();
		if ( empty( $recipe_id ) ) {
			return;
		}

		return $this->get_post_edit_link( $recipe_id );
	}

	/**
	 * Display the Class assigned to the current Date
	 */
	function column_callback_get_class( $post_id ) {
		$date = new Crb_Date( $post_id );

		$class_id = $date->get_class_id();

		return $this->get_post_edit_link( $class_id );
	}

	/**
	 * Display the Location assigned to the current Class
	 */
	function column_callback_get_location( $post_id ) {
		$date = new Crb_Date( $post_id );

		$location_id = $date->get_location_id();

		return $this->get_post_edit_link( $location_id );
	}
}

global $Crb_Initialize_Date;
$Crb_Initialize_Date = new Crb_Initialize_Date();
