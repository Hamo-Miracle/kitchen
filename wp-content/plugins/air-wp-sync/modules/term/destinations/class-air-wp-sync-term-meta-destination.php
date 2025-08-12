<?php
/**
 * Term Meta Destination.
 *
 * @package Air_WP_Sync_Free
 */

namespace Air_WP_Sync_Free;

/**
 * Clas Air_WP_Sync_Term_Meta_Destination
 */
class Air_WP_Sync_Term_Meta_Destination extends Air_WP_Sync_Abstract_Destination {
	/**
	 * Destination slug
	 *
	 * @var string
	 */
	protected $slug = 'termmeta';

	/**
	 * Module slug
	 *
	 * @var string
	 */
	protected $module = 'term';

	/**
	 * Markdown formatter
	 *
	 * @var Air_WP_Sync_Markdown_Formatter
	 */
	protected $markdown_formatter;

	/**
	 * Attachment formatter
	 *
	 * @var Air_WP_Sync_Attachment_Formatter
	 */
	protected $attachment_formatter;

	/**
	 * Constructor
	 *
	 * @param Air_WP_Sync_Markdown_Formatter    $markdown_formatter Markdown formatter.
	 * @param Air_WP_Sync_Attachments_Formatter $attachment_formatter Attachment formatter.
	 */
	public function __construct( $markdown_formatter, $attachment_formatter ) {
		parent::__construct();

		$this->markdown_formatter   = $markdown_formatter;
		$this->attachment_formatter = $attachment_formatter;

		add_action( 'airwpsync/import_record_after', array( $this, 'add_metas' ), 10, 4 );
		add_filter( 'airwpsync/features_by_taxonomy', array( $this, 'add_features_by_taxonomy' ), 10, 2 );
	}

	/**
	 * Handle term meta importing
	 *
	 * @param Air_WP_Sync_Abstract_Importer $importer Importer.
	 * @param array                         $fields Fields.
	 * @param \stdClass                     $record Airtable record.
	 * @param mixed|null                    $term_id WordPress object id.
	 */
	public function add_metas( $importer, $fields, $record, $term_id ) {
		$mapped_fields = $this->get_destination_mapping( $importer );
		foreach ( $mapped_fields as $mapped_field ) {
			// Get meta value.
			$value = $this->get_airtable_value( $fields, $mapped_field['airtable'], $importer );
			$value = $this->format( $value, $mapped_field, $importer );
			// Get meta key.
			$key = $mapped_field['wordpress'];
			if ( ! empty( $mapped_field['options']['name'] ) ) {
				$key = $mapped_field['options']['name'];
			}
			// Save meta.
			if ( ! empty( $key ) ) {
				update_term_meta( $term_id, $key, $value );
			}
		}
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return array
	 */
	protected function get_group() {
		return array(
			'slug' => 'term',
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
				'value'             => 'custom_field',
				'label'             => __( 'Custom Field...', 'air-wp-sync' ),
				'enabled'           => true,
				'allow_multiple'    => true,
				'supported_sources' => array(
					'autoNumber',
					'barcode.type',
					'barcode.text',
					'checkbox',
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
					'multipleAttachments',
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
				),
			),
		);
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
			'custom_field',
		);

		$features[ $this->slug ] = $destination_features;
		return $features;
	}

	/**
	 * Format imported value
	 *
	 * @param mixed                         $value Field value.
	 * @param array                         $mapped_field Field mapping conf.
	 * @param Air_WP_Sync_Abstract_Importer $importer Importer.
	 *
	 * @return mixed
	 */
	protected function format( $value, $mapped_field, $importer ) {
		$airtable_id = $mapped_field['airtable'];
		$source_type = $this->get_source_type( $airtable_id, $importer );

		if ( 'richText' === $source_type ) {
			// Markdown.
			$value = $this->markdown_formatter->format( $value );
		} elseif ( 'multipleAttachments' === $source_type ) {
			// Attachments.
			$value = $this->attachment_formatter->format( $value, $importer );
		} elseif ( 'checkbox' === $source_type ) {
			// Checkbox.
			// Convert boolean to 0|1.
			$value = (int) filter_var( $value, FILTER_VALIDATE_BOOLEAN );
		}

		return $value;
	}
}
