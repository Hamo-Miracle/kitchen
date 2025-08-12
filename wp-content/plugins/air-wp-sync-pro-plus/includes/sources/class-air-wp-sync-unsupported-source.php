<?php
/**
 * Unsupported Source.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

/**
 * Class Air_WP_Sync_Unsupported_Source.
 */
class Air_WP_Sync_Unsupported_Source {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'airwpsync/get_table_fields', array( $this, 'remove_fields' ) );
	}

	/**
	 * Remove all unsupported fields.
	 *
	 * @param array $fields Airtable fields.
	 *
	 * @return array
	 */
	public function remove_fields( $fields ) {
		$fields = array_filter(
			$fields,
			function ( $field ) {
				return 'button' !== $field->type;
			}
		);
		return array_values( $fields );
	}
}
