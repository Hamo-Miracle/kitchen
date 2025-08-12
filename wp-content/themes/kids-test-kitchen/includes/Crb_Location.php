<?php

/**
 * Location Post Type Entry
 */
class Crb_Location {
	public $location_id = 0;
	public $location_obj;

	/**
	 * Build all data associated with the object
	 */
	function __construct( $location_id ) {
		$this->location_id = $location_id;
		$this->location_obj = get_post( $location_id );
	}

	/**
	 * Return Location ID
	 */
	function get_location_id() {
		return $this->location_id;
	}

	/**
	 * Return Location Obj
	 */
	function get_location_obj() {
		return $this->location_obj;
	}

	/**
	 * Return Location Address
	 */
	function get_location_address() {
		if ( ! empty( $this->location_address ) ) {
			return $this->location_address;
		}

		$location_address = '';


		$address = array_filter( array(
			'Address'                  => carbon_get_post_meta( $this->get_location_id(), 'crb_location_address' ),
			'Phone'                    => carbon_get_post_meta( $this->get_location_id(), 'crb_location_phone' ),
			'Contact Person Name'      => carbon_get_post_meta( $this->get_location_id(), 'crb_location_contact_name' ),
			'Helpful Location Details' => carbon_get_post_meta( $this->get_location_id(), 'crb_location_contact_email' ),
		) );

		if ( ! empty( $address ) ) {
			$address_pieces = array();
			foreach ( $address as $element => $content ) {
				$address_pieces[] = sprintf( '<span class="row-indented"><strong>%s</strong>: %s</span>', $element, $content );
			}
			$location_address = implode( '<br />', $address_pieces );
		}

		$this->location_address = $location_address;

		return $this->location_address;
	}
}
