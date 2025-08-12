<?php

/**
 * Date Post Type Entry
 */
class Crb_Date {
	public $date_id = 0;
	public $facilitator_id = 0;
	public $recipe_id = 0;
	public $class_id = 0;
	public $location_id = 0;

	public $date_obj;
	public $facilitator_obj;
	public $recipe_obj;
	public $class_obj;
	public $location_obj;
	public $additional_recipes_times;

	/**
	 * Build all data associated with the object
	 */
	function __construct( $date_id ) {
		$this->date_id = $date_id;
		$this->date_obj = get_post( $date_id );

		// Class id
		$class_id = carbon_get_post_meta( $date_id, 'crb_date_class' );
		if ( !empty( $class_id ) ) {
			$this->class_id = $class_id;
			$this->class_obj = get_post( $class_id );

			// Location id
			$location_id = carbon_get_post_meta( $class_id, 'crb_class_location' );
			if ( !empty( $location_id ) ) {
				$this->location_id = $location_id;
				$this->location_obj = get_post( $location_id );
			}
		}

		// Recipe id
		$recipe_id = carbon_get_post_meta( $date_id, 'crb_date_recipe' );
		if ( !empty( $recipe_id ) ) {
			$this->recipe_id = $recipe_id;
			$this->recipe_obj = get_post( $recipe_id );
		}

		// Facilitator id
		$date_facilitator_id = carbon_get_post_meta( $date_id, 'crb_date_facilitator' );
		$class_facilitator_id = carbon_get_post_meta( $class_id, 'crb_class_facilitator' );
		if ( ! empty( $date_facilitator_id ) ) {
			$this->facilitator_id = $date_facilitator_id;
		} elseif ( ! empty( $class_facilitator_id ) ) {
			$this->facilitator_id = $class_facilitator_id;
		}

		if ( !empty( $this->facilitator_id ) ) {
			$this->facilitator_obj = get_user_by( 'ID', $this->facilitator_id );
		}

		// Additional Recipes
		$additional_recipes = carbon_get_post_meta( $this->date_id, 'crb_additional_recipes' );
		if ( $additional_recipes ) {
			$this->additional_recipes_times = $additional_recipes;
		}
	}

	/**
	 * Return Class ID
	 */
	function get_class_id() {
		return $this->class_id;
	}

	/**
	 * Return Location ID
	 */
	function get_location_id() {
		return $this->location_id;
	}

	/**
	 * Return Recipe ID
	 */
	function get_recipe_id() {
		return $this->recipe_id;
	}

	/**
	 * Return User ID
	 */
	function get_facilitator_id() {
		return $this->facilitator_id;
	}

	/**
	 * Return User ID
	 */
	function get_additional_facilitators_ids() {
		if ( ! $this->additional_recipes_times ) {
			return false;
		}

		$all_facilitators_ids = [];
		foreach ( $this->additional_recipes_times as $value ) {
			$all_facilitators_ids[] = $value['facilitator'];
		}

		return array_unique( $all_facilitators_ids );
	}

	/**
	 * Return Class Obj
	 */
	function get_class_obj() {
		return $this->class_obj;
	}

	/**
	 * Return Location Obj
	 */
	function get_location_obj() {
		return $this->location_obj;
	}

	/**
	 * Return Recipe Obj
	 */
	function get_recipe_obj() {
		return $this->recipe_obj;
	}

	/**
	 * Return User Obj
	 */
	function get_facilitator_obj() {
		return $this->facilitator_obj;
	}

	/**
	 * Return Time Start
	 */
	function get_time_start() {
		if ( isset( $this->time_start ) ) {
			return $this->time_start;
		}

		$time_start = carbon_get_post_meta( $this->date_id, 'crb_date_time_start' );
		if ( ! empty( $time_start ) ) {
			$this->time_start = $this->convert_24h_to_12_time( $time_start );
		}

		return $this->time_start;
	}

	/**
	 * Return Time End
	 */
	function get_time_end() {
		if ( isset( $this->time_end ) ) {
			return $this->time_end;
		}

		$time_end = carbon_get_post_meta( $this->date_id, 'crb_date_time_end' );
		if ( ! empty( $time_end ) ) {
			$this->time_end = $this->convert_24h_to_12_time( $time_end );
		}

		return $this->time_end;
	}

	/**
	 * Returns additional date times/recipes
	 */
	function get_additional_date_times() {
		return $this->additional_recipes_times ? $this->additional_recipes_times : false;
	}

	/**
	 * Converts 24h hour time into a 12h time
	 */
	function convert_24h_to_12_time( $time_24h ) {
		$time_12h = date( 'h:i A', strtotime( $time_24h . ' 01/01/1970' ) );

		return $time_12h;
	}
}
