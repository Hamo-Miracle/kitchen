<?php

namespace Air_WP_Sync_Free;

/**
 * Formula Source
 */
class Air_WP_Sync_Formula_Source {
	public function __construct() {
		add_filter( 'airwpsync/get_table_fields', array( $this, 'convert_formula_fields' ) );
	}

	public function convert_formula_fields( $fields ) {
		$fields = array_map(
			function ( $field ) {
				if ( $field->type === 'formula' ) {
					if ( isset( $field->options->result->type ) ) {
						// Copy formula result as a new field.
						$new_field = clone $field->options->result;
						// Keep id, name and description from the original formula field.
						$new_field->id          = $field->id;
						$new_field->name        = $field->name;
						$new_field->description = $field->description ?? '';
						return $new_field;
					}
				}
				return $field;
			},
			$fields
		);
		return $fields;
	}
}
