<?php
/**
 * Yoast Term Meta Destination.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

/**
 * Clas Air_WP_Sync_Term_Meta_Destination
 */
class Air_WP_Sync_Yoast_Destination_Term extends Air_WP_Sync_Abstract_Destination {
	/**
	 * Destination slug
	 *
	 * @var string
	 */
	protected $slug = 'yoast';

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

		$term = get_term( $term_id );
		if ( ! $term ) {
			return;
		}

		$taxonomy = $term->taxonomy ?? $importer->config()->get( 'taxonomy' );
		$values   = \WPSEO_Taxonomy_Meta::get_term_meta( $term, $taxonomy );

		foreach ( $mapped_fields as $mapped_field ) {
			// Get meta value.
			$value = $this->get_airtable_value( $fields, $mapped_field['airtable'], $importer );
			$value = $this->format( $value, $mapped_field, $importer );
			// Get meta key.
			$key = $mapped_field['wordpress'];
			if ( ! empty( $mapped_field['options']['name'] ) ) {
				$key = $mapped_field['options']['name'];
			}
			// Handle image fields.
			if ( ! empty( $value ) && in_array( $key, array( 'wpseo_opengraph-image', 'wpseo_twitter-image' ), true ) ) {
				$values[ $key . '-id' ] = (string) $value;
				$value                  = wp_get_attachment_image_url( $value, 'full' );
			}
			// Save meta.
			if ( ! empty( $key ) ) {
				$values[ $key ] = $value;
			}
		}
		\WPSEO_Taxonomy_Meta::set_values( $term_id, $taxonomy, $values );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return array
	 */
	protected function get_group() {
		return array(
			'label' => __( 'Yoast SEO', 'air-wp-sync' ),
			'slug'  => $this->slug,
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
				'value'             => 'wpseo_title',
				'label'             => __( 'SEO Title', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText' ),
			),
			array(
				'value'             => 'wpseo_desc',
				'label'             => __( 'Meta Description', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText', 'richText', 'multilineText' ),
			),
			array(
				'value'             => 'wpseo_focuskw',
				'label'             => __( 'Focus Keyphrase', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText' ),
			),
			array(
				'value'             => 'wpseo_opengraph-title',
				'label'             => __( 'Facebook Title', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText' ),
			),
			array(
				'value'             => 'wpseo_opengraph-description',
				'label'             => __( 'Facebook Description', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText', 'richText', 'multilineText' ),
			),
			array(
				'value'             => 'wpseo_opengraph-image',
				'label'             => __( 'Facebook Image', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'multipleAttachments' ),
			),
			array(
				'value'             => 'wpseo_twitter-title',
				'label'             => __( 'X(Twitter) Title', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText' ),
			),
			array(
				'value'             => 'wpseo_twitter-description',
				'label'             => __( 'X(Twitter) Description', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText', 'richText', 'multilineText' ),
			),
			array(
				'value'             => 'wpseo_twitter-image',
				'label'             => __( 'X(Twitter) Image', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'multipleAttachments' ),
			),
			array(
				'value'             => 'wpseo_canonical',
				'label'             => __( 'Canonical URL (Advanced)', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'url', 'singleLineText' ),
			),
			array(
				'value'             => 'wpseo_bctitle',
				'label'             => __( 'Breadcrumbs Title (Advanced)', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText' ),
			),
		);
	}

	/**
	 * Add field features for each taxonomy
	 *
	 * @param array       $features  Features.
	 * @param WP_Taxonomy $taxonomy  Taxonomy term object.
	 *
	 * @return array
	 */
	public function add_features_by_taxonomy( $features, $taxonomy ) {
		$destination_features = array(
			'wpseo_title',
			'wpseo_desc',
			'wpseo_focuskw',
			'wpseo_opengraph-title',
			'wpseo_opengraph-description',
			'wpseo_opengraph-image',
			'wpseo_twitter-title',
			'wpseo_twitter-description',
			'wpseo_twitter-image',
			'wpseo_canonical',
			'wpseo_bctitle',
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
		$destination = $mapped_field['wordpress'];
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

		// Escape html tags for description fields.
		if ( in_array( $destination, array( 'wpseo_metadesc', 'wpseo_opengraph-description', 'wpseo_twitter-description' ), true ) ) {
			$value = wp_strip_all_tags( $value );
		}

		// Pick only first image for social image fields.
		if ( in_array( $destination, array( 'wpseo_opengraph-image', 'wpseo_twitter-image' ), true ) ) {
			if ( is_array( $value ) ) {
				$value = array_shift( $value );
			}
			if ( ! empty( $value ) && ! is_int( $value ) ) {
				$value = null;
			}
		}

		return $value;
	}
}
