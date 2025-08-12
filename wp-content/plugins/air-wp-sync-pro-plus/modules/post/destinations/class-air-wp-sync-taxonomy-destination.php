<?php
/**
 * Taxonomy Destination.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

/**
 * Class Air_WP_Sync_Taxonomy_Destination.
 */
class Air_WP_Sync_Taxonomy_Destination extends Air_WP_Sync_Abstract_Destination {
	/**
	 * Destination slug.
	 *
	 * @var string
	 */
	protected $slug = 'taxonomy';

	/**
	 * Module slug.
	 *
	 * @var string
	 */
	protected $module = 'post';

	/**
	 * Markdown formatter.
	 *
	 * @var Air_WP_Sync_Markdown_Formatter
	 */
	protected $markdown_formatter;

	/**
	 * Term formatter.
	 *
	 * @var Air_WP_Sync_Term_Formatter
	 */
	protected $term_formatter;

	/**
	 * Interval formatter.
	 *
	 * @var Air_WP_Sync_Interval_Formatter
	 */
	protected $interval_formatter;

	/**
	 * Constructor
	 *
	 * @param Air_WP_Sync_Markdown_Formatter $markdown_formatter Markdown formatter.
	 * @param Air_WP_Sync_Terms_Formatter    $term_formatter Term formatter.
	 * @param Air_WP_Sync_Interval_Formatter $interval_formatter Interval formatter.
	 */
	public function __construct( $markdown_formatter, $term_formatter, $interval_formatter ) {
		parent::__construct();

		$this->markdown_formatter = $markdown_formatter;
		$this->term_formatter     = $term_formatter;
		$this->interval_formatter = $interval_formatter;

		add_action( 'airwpsync/import_record_after', array( $this, 'import' ), 10, 4 );
		add_filter( 'airwpsync/features_by_post_type', array( $this, 'add_features_by_post_type' ), 10, 2 );
	}

	/**
	 * Import terms
	 *
	 * @param Air_WP_Sync_Abstract_Importer $importer Importer.
	 * @param array                         $fields Fields.
	 * @param \stdClass                     $record Airtable record.
	 * @param mixed|null                    $existing_object_id WordPress object id.
	 */
	public function import( $importer, $fields, $record, $existing_object_id ) {
		$mapped_fields = $this->get_destination_mapping( $importer );
		foreach ( $mapped_fields as $mapped_field ) {
			$value    = $this->get_airtable_value( $fields, $mapped_field['airtable'], $importer );
			$taxonomy = $mapped_field['wordpress'];
			$value    = $this->format( $value, $mapped_field, $importer, $taxonomy );
			wp_set_object_terms( $existing_object_id, $value, $taxonomy );
		}
	}

	/**
	 * Add field features for each post types
	 *
	 * @param array  $features Features.
	 * @param string $post_type Post type.
	 *
	 * @return array
	 */
	public function add_features_by_post_type( $features, $post_type ) {
		$features[ $this->slug ] = get_object_taxonomies( $post_type );
		return $features;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_group() {
		return array(
			'label' => __( 'Taxonomies', 'air-wp-sync' ),
			'slug'  => 'taxonomy',
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_mapping_fields() {
		$excluded         = array( 'link_category' );
		$taxonomies       = get_taxonomies(
			array(
				'show_ui' => 1,
			),
			'objects'
		);
		$taxonomy_options = array();

		foreach ( $taxonomies as $taxonomy ) {
			if ( ! in_array( $taxonomy->name, $excluded, true ) ) {
				$taxonomy_options[] = array(
					'value'             => $taxonomy->name,
					'label'             => sprintf( '%s (%s)', $taxonomy->labels->singular_name, $taxonomy->name ),
					'enabled'           => true,
					'form_options'      => array(
						array(
							'name'  => 'split_comma_separated_string_into_terms',
							'type'  => 'checkbox',
							'label' => __( 'Split comma-separated string into terms', 'air-wp-sync' ),
						),
					),
					'supported_sources' => array(
						'autoNumber',
						'barcode.type',
						'barcode.text',
						'count',
						'createdBy.id',
						'createdBy.email',
						'createdBy.name',
						'number',
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
						'airwpsyncProxyRecordLinks|singleLineText',
						'airwpsyncProxyRecordLinks|singleSelect',
						'airwpsyncProxyRecordLinks|multipleSelects',
					),
				);
			}
		}

		return $taxonomy_options;
	}

	/**
	 * Format imported value
	 *
	 * @param mixed                         $value Field value.
	 * @param array                         $mapped_field Field mapping conf.
	 * @param Air_WP_Sync_Abstract_Importer $importer Importer.
	 * @param string                        $taxonomy The taxonomy.
	 *
	 * @return array
	 */
	protected function format( $value, $mapped_field, $importer, $taxonomy ) {
		$airtable_id = $mapped_field['airtable'];
		$source_type = $this->get_source_type( $airtable_id, $importer );

		if ( 'airwpsyncProxyRecordLinks|multipleSelects' === $source_type ) {
			$value = Air_WP_Sync_Helper::array_flatten( $value );
			$value = array_values( array_unique( $value ) );
		} elseif ( 'richText' === $source_type ) {
			// Markdown.
			$value = $this->markdown_formatter->format( $value );
		} elseif ( in_array( $source_type, array( 'date', 'dateTime' ), true ) ) {
			// Date.
			$value = date_i18n( get_option( 'date_format' ), strtotime( $value ) );
		} elseif ( 'duration' === $source_type ) {
			$field = $this->get_field_by_id( $airtable_id, $importer );
			$value = $this->interval_formatter->format( $value, $field );
		} elseif ( ! is_array( $value ) ) {
			// Default string.
			$value = strval( $value );
		}

		$split_comma_separated_string_into_terms = ! empty( $mapped_field['options']['form_options_values']['split_comma_separated_string_into_terms'] );
		return $this->term_formatter->format( $value, $importer, $taxonomy, $split_comma_separated_string_into_terms );
	}
}
