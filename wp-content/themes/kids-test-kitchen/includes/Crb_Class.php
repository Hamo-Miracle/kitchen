<?php

/**
 * Class Entry
 *
 * @param $class_id        - Date Post ID
 * @param $class_obj       - Date Post Obj
 * @param $new_class_dates - Array [ date => [ 'start', 'time_start', 'time_end' ] ]
 * @param $old_class_dates - Array [ date => [ 'start', 'time_start', 'time_end' ] ]
 * @param $date_ids        - Array [ date_id ]
 * @param $new_dates       - Array [ date_string ]
 * @param $old_dates       - Array [ date_string ]
 */
class Crb_Class {
	private $class_id = 0;
	private $class_facilitator_id = 0;
	private $class_obj;
	private $new_class_dates = array();
	private $old_class_dates = array();
	private $date_ids = array();
	private $new_dates = array();
	private $old_dates = array();

	function __construct( $class_id ) {
		$this->class_id = absint( $class_id );

		if ( empty( $this->class_id ) ) {
			throw new Exception( 'Missing Class ID in Crb_Class' );
		}

		$class_facilitator_id = carbon_get_post_meta( $class_id, 'crb_class_facilitator' );
		if ( $class_facilitator_id ) {
			$this->class_facilitator_id = $class_facilitator_id;
		}

		$this->class_obj = get_post( $this->class_id );
	}

	/**
	 * Prepare a list of date IDs related to the current Class
	 */
	function get_dates() {
		if ( ! empty( $this->dates ) ) {
			return $this->dates;
		}

		$dates = get_posts( array(
			'post_type' => 'crb_date',
			'post_status' => array( 'publish', 'pending' ),
			'posts_per_page' => -1,

			'order' => 'ASC',
			'orderby' => 'meta_value',
			'meta_key' => '_crb_date_start',

			'fields' => 'ids',

			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => '_crb_date_start',
					'value' => date('Y-m-d'),
					'compare' => '>=',
				),
				array(
					'key' => '_crb_date_class',
					'value' => $this->class_id,
				),
			),
		) );

		$this->dates = $dates;

		return $this->dates;
	}

	/**
	 * Sync post meta from the Class with Dates objects
	 * Tape & Glue function
	 */
	function sync_complex_with_date_posts() {
		$new_class_dates = carbon_get_post_meta( $this->class_id, 'crb_class_dates', 'complex' );
		$new_dates = wp_list_pluck( $new_class_dates, 'start' );

		$new_class_dates = array_combine( $new_dates, $new_class_dates );

		$date_ids = get_posts( array(
			'post_type'      => 'crb_date',
			'post_status'    => array( 'publish', 'pending' ),
			'posts_per_page' => -1,
			'meta_key'       => '_crb_date_class',
			'meta_value'     => $this->class_id,
			'fields'         => 'ids',
		) );

		$old_class_dates = $date_ids;
		array_walk( $old_class_dates, function( &$date_id ) {
			$date_id = array(
				'start'      => carbon_get_post_meta( $date_id, 'crb_date_start' ),
				'time_start' => carbon_get_post_meta( $date_id, 'crb_date_time_start' ),
				'time_end'   => carbon_get_post_meta( $date_id, 'crb_date_time_end' ),
			);
		} );

		$old_dates = $date_ids;
		array_walk( $old_dates, function( &$date_id ) {
			$date_id = carbon_get_post_meta( $date_id, 'crb_date_start' );
		} );

		$old_class_dates = array_combine( $old_dates, $old_class_dates );

		if ( !empty( $new_class_dates ) ) {
			$this->new_class_dates = $new_class_dates;
		}

		if ( !empty( $old_class_dates ) ) {
			$this->old_class_dates = $old_class_dates;
		}

		if ( !empty( $date_ids ) ) {
			$this->date_ids = $date_ids;
		}

		if ( !empty( $new_dates ) ) {
			$this->new_dates = $new_dates;
		}

		if ( !empty( $old_dates ) ) {
			$this->old_dates = $old_dates;
		}

		$this->publish_missing_dates();
		$this->update_dates();
		$this->delete_dates();

		Crb_Cache_Booster()->flush( 'sync_complex_with_date_posts' );
	}

	/**
	 * Publish dates that are not yet published
	 */
	private function publish_missing_dates() {
		$dates_to_be_published = array_diff_key( $this->new_class_dates, $this->old_class_dates );

		foreach ( $dates_to_be_published as $date => $new_class_date ) {
			// $new_class_date is an array with "start", "time_start" and "time_end" params.
			if ( ! $this->date_exists( $new_class_date ) ) {
				$new_date_id = wp_insert_post( array(
					'post_author' => $this->class_obj->post_author,
					'post_status' => $this->get_date_status(),
					'post_type'   => 'crb_date',
				) );

				update_post_meta( $new_date_id, '_crb_date_start', $new_class_date['start'] );
				update_post_meta( $new_date_id, '_crb_date_time_start', $new_class_date['time_start'] );
				update_post_meta( $new_date_id, '_crb_date_time_end', $new_class_date['time_end'] );
				update_post_meta( $new_date_id, '_crb_date_class', absint( $this->class_id ) );

				if ( isset( $new_class_date['recipe'] ) && is_numeric( $new_class_date['recipe'] ) ) {
					update_post_meta( $new_date_id, '_crb_date_recipe', absint( $new_class_date['recipe'] ) );
				}
				if ( isset( $new_class_date['facilitator'] ) && is_numeric( $new_class_date['facilitator'] ) ) {
					update_post_meta( $new_date_id, '_crb_date_facilitator', absint( $new_class_date['facilitator'] ) );
				}

				if ( isset( $new_class_date['additional_recipes'] ) && ! empty( $new_class_date['additional_recipes'] ) ) {
					update_post_meta( $new_date_id, '_crb_additional_recipes', $new_class_date['additional_recipes'] );
				}

				// Manually Trigger Updated_post_meta ( not triggered with calls to update_post_meta )
				do_action( 'updated_post_meta', 0, $new_date_id, '_crb_date_start', $new_class_date['start'] );
			}
		}
	}

	/**
	 * Update already publish dates, in cate of Time Change. If the date is changed, an entirely new entry will be created.
	 */
	private function update_dates() {
		$dates_to_be_updated = array_intersect_key( $this->old_class_dates, $this->new_class_dates );

		foreach ( $dates_to_be_updated as $date => $meta ) {
			$dates_for_update = get_posts( array(
				'post_type' => 'crb_date',
				'post_status' => array( 'publish', 'pending' ),
				'posts_per_page' => -1,
				'fields' => 'ids',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => '_crb_date_class',
						'value' => $this->class_id,
					),
					array(
						'key' => '_crb_date_start',
						'value' => $date,
					),
				)
			) );

			foreach ( $dates_for_update as $date_id ) {
				update_post_meta( $date_id, '_crb_date_time_start', $this->new_class_dates[$date]['time_start'] );
				update_post_meta( $date_id, '_crb_date_time_end', $this->new_class_dates[$date]['time_end'] );

				if ( isset( $this->new_class_dates[$date]['recipe'] ) && is_numeric( $this->new_class_dates[$date]['recipe'] ) ) {
					update_post_meta( $date_id, '_crb_date_recipe', absint( $this->new_class_dates[$date]['recipe'] ) );
				}
				if ( isset( $this->new_class_dates[$date]['facilitator'] ) && is_numeric( $this->new_class_dates[$date]['facilitator'] ) ) {
					update_post_meta( $date_id, '_crb_date_facilitator', absint( $this->new_class_dates[$date]['facilitator'] ) );
				}

				if ( isset( $this->new_class_dates[$date]['additional_recipes'] ) && ! empty( $this->new_class_dates[$date]['additional_recipes'] ) ) {
					update_post_meta( $date_id, '_crb_additional_recipes', $this->new_class_dates[$date]['additional_recipes'] );
				}

				$new_date_id = wp_update_post( array(
					'ID'          => $date_id,
					'post_status' => $this->get_date_status(),
				) );
			}
		}
	}

	/**
	 * Delete Dates appointed at the current Class, if the date does not exists in the Class edit screen.
	 */
	private function delete_dates() {
		$dates_to_be_deleted = array_diff( $this->old_dates, $this->new_dates );

		foreach ( $dates_to_be_deleted as $date_for_deletion ) {
			$dates_for_deletion = get_posts( array(
				'post_type' => 'crb_date',
				'post_status' => array( 'publish', 'pending' ),
				'posts_per_page' => -1,
				'fields' => 'ids',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => '_crb_date_class',
						'value' => $this->class_id,
					),
					array(
						'key' => '_crb_date_start',
						'value' => $date_for_deletion,
					),
				)
			) );

			foreach ( $dates_for_deletion as $date_id ) {
				wp_delete_post( $date_id, true );
			}
		}
	}

	/**
	 * Check if date is publish
	 * @param $date = array( "start", "time_start", "time_end" )
	 */
	private function date_exists( $date ) {
		return in_array( $date['start'], $this->old_dates );
	}

	/**
	 * Get main class facilitator
	 */
	public function get_main_facilitator() {
		if ( $this->class_facilitator_id ) {
			return $this->class_facilitator_id;
		}

		return false;
	}

	private function get_date_status() {
		$crb_class_schedule_approve = carbon_get_post_meta( $this->class_id, 'crb_class_schedule_approve' );
		$post_status = 'pending';
		if ( !empty( $crb_class_schedule_approve ) ) {
			$post_status = 'publish';
		}

		return $post_status;
	}
}
