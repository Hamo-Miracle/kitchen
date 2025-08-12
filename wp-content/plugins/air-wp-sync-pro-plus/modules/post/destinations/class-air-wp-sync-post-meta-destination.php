<?php
/**
 * Post Meta Destination.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

/**
 * Class Air_WP_Sync_Post_Meta_Destination.
 */
class Air_WP_Sync_Post_Meta_Destination extends Air_WP_Sync_Abstract_Destination {
	/**
	 * Destination slug.
	 *
	 * @var string
	 */
	protected $slug = 'postmeta';

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
	 * Attachment formatter.
	 *
	 * @var Air_WP_Sync_Attachments_Formatter
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
		add_filter( 'airwpsync/features_by_post_type', array( $this, 'add_features_by_post_type' ), 10, 2 );
	}

	/**
	 * Handle post meta importing
	 *
	 * @param Air_WP_Sync_Abstract_Importer $importer Importer.
	 * @param array                         $fields Fields.
	 * @param \stdClass                     $record Airtable record.
	 * @param mixed|null                    $existing_object_id WordPress object id.
	 */
	public function add_metas( $importer, $fields, $record, $existing_object_id ) {
		$mapped_fields = $this->get_destination_mapping( $importer );
		foreach ( $mapped_fields as $mapped_field ) {
			// Get meta value.
			$value = $this->get_airtable_value( $fields, $mapped_field['airtable'], $importer );
			$value = $this->format( $value, $mapped_field, $importer, $existing_object_id );
			// Get meta key.
			$key = '';
			if ( '_thumbnail_id' === $mapped_field['wordpress'] ) {
				$key = $mapped_field['wordpress'];
			} elseif ( ! empty( $mapped_field['options']['name'] ) ) {
				$key = $mapped_field['options']['name'];
			}
			// Save meta.
			if ( ! empty( $key ) ) {
				update_post_meta( $existing_object_id, $key, $value );
			}
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
		$destination_features = array(
			'custom_field',
		);

		if ( post_type_supports( $post_type, 'thumbnail' ) ) {
			$destination_features[] = '_thumbnail_id';
		}
		$features[ $this->slug ] = $destination_features;
		return $features;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_group() {
		return array(
			'slug' => 'post',
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_mapping_fields() {
		return array(
			array(
				'value'             => '_thumbnail_id',
				'label'             => __( 'Featured Image', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array(
					'multipleAttachments',
				),
			),
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
	 * Format imported value
	 *
	 * @param mixed                         $value Field value.
	 * @param array                         $mapped_field Field mapping conf.
	 * @param Air_WP_Sync_Abstract_Importer $importer Importer.
	 * @param mixed|null                    $post_id WordPress object id.
	 *
	 * @return mixed
	 */
	protected function format( $value, $mapped_field, $importer, $post_id ) {
		$airtable_id = $mapped_field['airtable'];
		$destination = $mapped_field['wordpress'];
		$source_type = $this->get_source_type( $airtable_id, $importer );

		if ( '_thumbnail_id' === $destination ) {
			// Keep first attachment from multipleAttachments.
			if ( is_array( $value ) ) {
				$value = array_shift( $value );
			}
			// Import as media and get attachment ID.
			$value = $this->attachment_formatter->format( $value, $importer, $post_id );
			if ( is_array( $value ) ) {
				$value = array_shift( $value );
			}
			if ( ! empty( $value ) && ! is_int( $value ) ) {
				$value = null;
			}
		} elseif ( 'richText' === $source_type ) {
				// Markdown.
				$value = $this->markdown_formatter->format( $value );
		} elseif ( 'multipleAttachments' === $source_type ) {
			// Attachments.
			$value = $this->attachment_formatter->format( $value, $importer, $post_id );
		} elseif ( 'checkbox' === $source_type ) {
			// Checkbox.
			// Convert boolean to 0|1.
			$value = (int) filter_var( $value, FILTER_VALIDATE_BOOLEAN );
		}

		return $value;
	}

	/**
	 * Check if source attachment is an image
	 *
	 * @param \stdClass $value Airtable image field properties.
	 *
	 * @return string|null
	 */
	protected function check_if_image( $value ) {
		$type_parts = explode( '/', $value->type );
		return 'image' === $type_parts[0] ? $value : null;
	}
}
