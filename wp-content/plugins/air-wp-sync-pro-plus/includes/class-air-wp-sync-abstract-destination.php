<?php
/**
 * Abstract Destination.
 * Base class to define import destination.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

/**
 * Class Air_WP_Sync_Abstract_Destination
 */
abstract class Air_WP_Sync_Abstract_Destination {
	/**
	 * Destination slug
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * Module slug
	 *
	 * @var string
	 */
	protected $module;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'airwpsync/get_wp_fields', array( $this, 'add_fields' ), 10, 2 );
	}

	/**
	 * Assign fields to mapping group
	 *
	 * @return array
	 */
	abstract protected function get_group();

	/**
	 * Get mapping fields
	 *
	 * @return array
	 */
	abstract protected function get_mapping_fields();

	/**
	 * Add fields to mapping options
	 *
	 * @param array  $fields Fields.
	 * @param string $module Module's slug.
	 *
	 * @return array|mixed
	 */
	public function add_fields( $fields, $module ) {
		if ( $module === $this->module ) {
			$group          = $this->get_group();
			$new_fields     = $this->get_mapping_fields();
			$options        = array();
			$default_option = array(
				'enabled'        => true,
				'allow_multiple' => false,
			);
			foreach ( $new_fields as $field ) {
				$options[] = array_merge( $default_option, $field, array( 'value' => $this->slug . '::' . $field['value'] ) );
			}

			$fields = array_merge_recursive(
				$fields,
				array(
					$group['slug'] => array(
						'options' => $options,
					),
				)
			);
			if ( ! empty( $group['label'] ) ) {
				$fields[ $group['slug'] ]['label'] = $group['label'];
			}
		}
		return $fields;
	}

	/**
	 * Get mapped fields for our destination specifically
	 *
	 * @param Air_WP_Sync_Abstract_Importer $importer Importer.
	 *
	 * @return array
	 */
	protected function get_destination_mapping( $importer ) {
		$mapping             = ! empty( $importer->config()->get( 'mapping' ) ) ? $importer->config()->get( 'mapping' ) : array();
		$destination_mapping = array();
		$module              = $importer->config()->get( 'module' );

		if ( $module !== $this->module ) {
			return $destination_mapping;
		}

		foreach ( $mapping as $mapping_pair ) {
			$wp_field_parts = explode( '::', $mapping_pair['wordpress'] );
			$wp_field_group = $wp_field_parts[0] ? $wp_field_parts[0] : '';
			$wp_field       = $wp_field_parts[1] ? $wp_field_parts[1] : '';

			if ( $wp_field_group === $this->slug ) {
				$destination_mapping[] = array_merge( $mapping_pair, array( 'wordpress' => $wp_field ) );
			}
		}
		return $destination_mapping;
	}

	/**
	 * Get source type from Airtable id
	 *
	 * @param string                        $airtable_id Airtable field id.
	 * @param Air_WP_Sync_Abstract_Importer $importer Importer.
	 *
	 * @return mixed|string
	 */
	protected function get_source_type( $airtable_id, $importer ) {
		foreach ( $importer->get_airtable_fields() as $field ) {
			if ( $field->id === $airtable_id ) {
				// Use result type for computed fields (formula, lookup).
				return ! empty( $field->options->result->type ) ? $field->options->result->type : $field->type;
			}
		}
		return '';
	}

	/**
	 * Get airtable field definition by id
	 *
	 * @param string                        $airtable_id Airtable field id.
	 * @param Air_WP_Sync_Abstract_Importer $importer Importer.
	 */
	protected function get_field_by_id( $airtable_id, $importer ) {
		foreach ( $importer->get_airtable_fields() as $field ) {
			if ( $field->id === $airtable_id ) {
				return $field;
			}
		}
		return '';
	}

	/**
	 * Get airtable value
	 *
	 * @param array                         $fields Fields.
	 * @param string                        $airtable_id Airtable field id.
	 * @param Air_WP_Sync_Abstract_Importer $importer Importer.
	 *
	 * @return mixed|string
	 */
	protected function get_airtable_value( $fields, $airtable_id, $importer ) {
		$source_type = $this->get_source_type( $airtable_id, $importer );
		// Get real field id if needed.
		if ( preg_match( '/(.+)\.(.+)/', $airtable_id, $matches ) ) {
			$airtable_id = $matches[1];
		}

		$value = array_key_exists( $airtable_id, $fields ) ? $fields[ $airtable_id ] : '';

		// Handle object properties.
		if ( preg_match( '/(.+)\.(.+)/', $source_type, $matches ) ) {
			$key = $matches[2];

			if ( is_array( $value ) ) {
				foreach ( $value as &$val ) {
					$val = is_object( $val ) ? $val->{$key} : '';
				}
			} else {
				$value = is_object( $value ) ? $value->{$key} : '';
			}
		}
		return $value;
	}
}
