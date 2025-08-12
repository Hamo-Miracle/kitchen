<?php
/**
 * Term Destination.
 *
 * @package Air_WP_Sync_Free
 */

namespace Air_WP_Sync_Free;

/**
 * Class Air_WP_Sync_Term_Destination
 */
class Air_WP_Sync_Term_Destination extends Air_WP_Sync_Abstract_Destination {
	/**
	 * Destination slug.
	 *
	 * @var string
	 */
	protected $slug = 'term';

	/**
	 * Module slug.
	 *
	 * @var string
	 */
	protected $module = 'term';

	/**
	 * Markdown formatter.
	 *
	 * @var Air_WP_Sync_Markdown_Formatter
	 */
	protected $markdown_formatter;

	/**
	 * Interval formatter.
	 *
	 * @var Air_WP_Sync_Interval_Formatter
	 */
	protected $interval_formatter;

	/**
	 * String supported sources.
	 *
	 * @var string[]
	 */
	protected $string_supported_sources = array(
		'autoNumber',
		'barcode.type',
		'barcode.text',
		'count',
		'createdBy.id',
		'createdBy.email',
		'createdBy.name',
		'currency',
		'date',
		'dateTime',
		'duration',
		'email',
		'externalSyncSource',
		'lastModifiedBy.id',
		'lastModifiedBy.email',
		'lastModifiedBy.name',
		'lastModifiedTime',
		'multipleCollaborators.id',
		'multipleCollaborators.email',
		'multipleCollaborators.name',
		'multipleRecordLinks',
		'multipleSelects',
		'multilineText',
		'number',
		'percent',
		'phoneNumber',
		'rating',
		'richText',
		'rollup',
		'singleCollaborator.id',
		'singleCollaborator.email',
		'singleCollaborator.name',
		'singleLineText',
		'singleSelect',
		'url',
	);

	/**
	 * Number supported sources.
	 *
	 * @var string[]
	 */
	protected $number_supported_sources = array(
		'number',
		'singleLineText',
		'singleSelect',
	);

	/**
	 * Constructor
	 *
	 * @param Air_WP_Sync_Markdown_Formatter $markdown_formatter Markdown formatter.
	 * @param Air_WP_Sync_Interval_Formatter $interval_formatter Interval formatter.
	 */
	public function __construct( $markdown_formatter, $interval_formatter ) {
		parent::__construct();

		$this->markdown_formatter = $markdown_formatter;
		$this->interval_formatter = $interval_formatter;

		add_filter( 'airwpsync/import_term_data', array( $this, 'add_to_term_data' ), 20, 5 );
		add_filter( 'airwpsync/import_record_after', array( $this, 'check_parent_relationship' ), 10, 4 );
		add_filter( 'airwpsync/features_by_taxonomy', array( $this, 'add_features_by_taxonomy' ), 10, 2 );
	}

	/**
	 * Handle term data importing.
	 *
	 * @param array                     $term_data Term data.
	 * @param Air_WP_Sync_Term_Importer $importer Term importer.
	 * @param array                     $fields Fields.
	 * @param \stdClass                 $record Airtable record.
	 * @param int                       $term_id Existing content id.
	 */
	public function add_to_term_data( $term_data, $importer, $fields, $record, $term_id ) {
		$mapped_fields = $this->get_destination_mapping( $importer );
		$taxonomy      = $this->get_taxonomy_mapping_value( $importer, $fields, $mapped_fields );

		foreach ( $mapped_fields as $mapped_field ) {
			$key   = $mapped_field['wordpress'];
			$value = $this->get_airtable_value( $fields, $mapped_field['airtable'], $importer );
			if ( 'parentById' === $mapped_field['wordpress'] || 'parentByName' === $mapped_field['wordpress'] ) {
				$key = 'parent';
				if ( ! empty( $term_data[ $key ] ) ) {
					continue;
				}
			}
			$term_data[ $key ] = $this->format( $value, $mapped_field, $importer, $taxonomy );
		}

		return $term_data;
	}

	/**
	 * Check parent relationship, and forces an update if parentName is mapped but term is not found.
	 *
	 * @param Air_WP_Sync_Abstract_Importer $importer Importer.
	 * @param array                         $fields Fields.
	 * @param \stdClass                     $record Airtable record.
	 * @param mixed|null                    $term_id Term ID.
	 */
	public function check_parent_relationship( $importer, $fields, $record, $term_id ) {
		$mapped_fields = $this->get_destination_mapping( $importer );
		$taxonomy      = $this->get_taxonomy_mapping_value( $importer, $fields, $mapped_fields );

		foreach ( $mapped_fields as $mapped_field ) {
			if ( in_array( $mapped_field['wordpress'], array( 'parentByName', 'parentById' ), true ) ) {
				$value = $this->get_airtable_value( $fields, $mapped_field['airtable'], $importer );
				if ( ! empty( $value ) ) {
					$parent_id = $this->format( $value, $mapped_field, $importer, $taxonomy );
					if ( ! $parent_id ) {
						delete_term_meta( $term_id, '_air_wp_sync_hash' );
					}
				}
			}
		}
	}

	/**
	 * Looks for taxonomy mapping value in the Airtable record
	 *
	 * @param Air_WP_Sync_Abstract_Importer $importer Importer.
	 * @param array                         $fields  Fields.
	 * @param array                         $mapped_fields  Mapped fields.
	 * @return  string  $taxonomy  Taxonomy slug
	 */
	public function get_taxonomy_mapping_value( $importer, $fields, $mapped_fields = array() ) {
		if ( empty( $mapped_fields ) ) {
			$mapped_fields = $this->get_destination_mapping( $importer );
		}

		$taxonomy = $importer->config()->get( 'taxonomy' );

		foreach ( $mapped_fields as $mapped_field ) {
			if ( 'taxonomy' === $mapped_field['wordpress'] ) {
				$value = $this->get_airtable_value( $fields, $mapped_field['airtable'], $importer );
				if ( ! empty( $value ) ) {
					$taxonomy = $this->format( $value, $mapped_field, $importer );
				}
			}
		}

		return $taxonomy;
	}

	/**
	 * Add field features for each taxonomy
	 *
	 * @param array  $features  Features.
	 * @param string $taxonomy  Taxonomy term object.
	 *
	 * @return array
	 */
	public function add_features_by_taxonomy( $features, $taxonomy ) {
		$destination_features = array(
			'name',
			'slug',
			'taxonomy',
			'description',
		);

		if ( $taxonomy->hierarchical ) {
			$destination_features[] = 'parentById';
			$destination_features[] = 'parentByName';
		}

		$features[ $this->slug ] = $destination_features;
		return $features;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return array
	 */
	protected function get_group() {
		return array(
			'label' => __( 'Terms', 'air-wp-sync' ),
			'slug'  => 'term',
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return array
	 */
	protected function get_mapping_fields() {
		return array(
			array(
				'value'             => 'name',
				'label'             => __( 'Name', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => $this->string_supported_sources,
			),
			array(
				'value'             => 'slug',
				'label'             => __( 'Slug', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => $this->string_supported_sources,
			),
			array(
				'value'             => 'taxonomy',
				'label'             => __( 'Taxonomy', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => $this->string_supported_sources,
			),
			array(
				'value'             => 'description',
				'label'             => __( 'Description', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => $this->string_supported_sources,
			),
			array(
				'value'             => 'parentById',
				'label'             => __( 'Parent (by ID)', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => $this->number_supported_sources,
			),
			array(
				'value'             => 'parentByName',
				'label'             => __( 'Parent (by name)', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => $this->string_supported_sources,
			),
		);
	}

	/**
	 * Format imported value
	 *
	 * @param mixed                     $value Field value.
	 * @param array                     $mapped_field Field mapping conf.
	 * @param Air_WP_Sync_Term_Importer $importer Term importer.
	 * @param string                    $taxonomy Taxonomy slug.
	 */
	protected function format( $value, $mapped_field, $importer, $taxonomy = '' ) {
		$airtable_id = $mapped_field['airtable'];
		$destination = $mapped_field['wordpress'];
		$source_type = $this->get_source_type( $airtable_id, $importer );

		if ( 'parentByName' === $destination ) {
			$term  = get_term_by( 'name', $value, $taxonomy );
			$value = $term ? $term->term_id : 0;
		} elseif ( 'parentById' === $destination ) {
			$term  = get_term_by( 'id', $value, $taxonomy );
			$value = $term ? $term->term_id : 0;
		} elseif ( 'taxonomy' === $destination ) {
			$value = sanitize_title( $value );
		} elseif ( 'richText' === $source_type ) {
			// Markdown.
			$value = $this->markdown_formatter->format( $value );
		} elseif ( in_array( $source_type, array( 'date', 'dateTime' ), true ) ) {
			// Date.
			$value = date_i18n( get_option( 'date_format' ), strtotime( $value ) );
		} elseif ( 'duration' === $source_type ) {
			$field = $this->get_field_by_id( $airtable_id, $importer );
			$value = $this->interval_formatter->format( $value, $field );
		} elseif ( is_array( $value ) ) {
			// Multiple values.
			$value = implode( ', ', $value );
		} else {
			// Default string.
			$value = strval( $value );
		}

		return $value;
	}
}
