<?php

namespace Air_WP_Sync_Free;

/**
 * Terms Formatter
 */
class Air_WP_Sync_Terms_Formatter {
	/* @var Air_WP_Sync_Importer */
	protected $importer;

	/**
	 * Format source value
	 *
	 * @param array|string|null             $value The list of string.
	 * @param Air_WP_Sync_Abstract_Importer $importer The importer.
	 * @param string                        $taxonomy The taxonomy.
	 * @param bool                          $split_comma_separated_string_into_terms  Whether to split the strngs on the commas to create terms.
	 *
	 * @return array
	 */
	public function format( $value, $importer, $taxonomy, $split_comma_separated_string_into_terms ) {
		$this->importer = $importer;

		if ( is_null( $value ) ) {
			return array();
		}

		// Make sure we have an array of terms
		$values = ! is_array( $value ) ? array( $value ) : $value;

		// Go through the array and split strings if needed.
		if ( $split_comma_separated_string_into_terms ) {
			$values = array_reduce( $values, array( $this, 'recursive_split' ), array() );
		}

		$terms = array();
		foreach ( $values as $value ) {
			$value = wp_strip_all_tags( $value );
			if ( empty( $value ) ) {
				continue;
			}

			$term = term_exists( $value, $taxonomy );
			if ( 0 === $term || null === $term ) {
				$term = wp_insert_term( $value, $taxonomy );
			}

			if ( is_wp_error( $term ) ) {
				$this->log( sprintf( '- Cannot get term \'%s\' (taxonomy: \'%s\'), error: %s', $value, $taxonomy, $term->get_error_message() ) );
			} else {
				$terms[] = (int) $term['term_id'];
			}
		}
		return $terms;
	}

	/**
	 * Log
	 */
	protected function log( $message, $level = 'log' ) {
		if ( $this->importer ) {
			$this->importer->log( $message, $level );
		}
	}

	/**
	 * Callback function used to recursively merge array, and split strings by commas
	 *
	 * @param  array        $carry  Array of values from the preceding iteration.
	 * @param  array|string $item  Current item.
	 * @return  array  $carry  Array of values after the current iteration.
	 */
	function recursive_split( $carry, $item ) {
		if ( is_string( $item ) ) {
			$carry = array_merge( $carry, array_map( 'trim', explode( ',', $item ) ) );
		}
		if ( is_array( $item ) ) {
			$carry = array_merge( $carry, $this->recursive_split( $carry, $item ) );
		}
		return $carry;
	}
}
