<?php
/**
 * Manages import as Yoast SEO field.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

/**
 * Air_WP_Sync_ACF_Destination class.
 */
class Air_WP_Sync_Yoast_Destination_Post extends Air_WP_Sync_Abstract_Destination {

	/**
	 * Destination slug.
	 *
	 * @var string
	 */
	protected $slug = 'yoast';


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
	private $markdown_formatter;

	/**
	 * Attachment formatter.
	 *
	 * @var Air_WP_Sync_Attachments_Formatter
	 */
	private $attachment_formatter;

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
	 * Handle post meta importing.
	 *
	 * @param Air_WP_Sync_Abstract_Importer $importer  Importer.
	 * @param array                         $fields    Fields.
	 * @param \stdClass                     $record    The airtable object.
	 * @param int                           $post_id   The post id.
	 */
	public function add_metas( $importer, $fields, $record, $post_id ) {
		$mapped_fields = $this->get_destination_mapping( $importer, $fields );
		foreach ( $mapped_fields as $mapped_field ) {

			// Get meta value.
			$value = $this->get_airtable_value( $fields, $mapped_field['airtable'], $importer );
			$value = $this->format( $value, $mapped_field, $importer, $post_id );

			// Get meta key.
			$key = $mapped_field['wordpress'];

			// Save meta.
			if ( ! empty( $key ) ) {
				if ( ! empty( $value ) && in_array( $key, array( '_yoast_wpseo_opengraph-image', '_yoast_wpseo_twitter-image' ), true ) ) {
					$value = wp_get_attachment_image_url( $value, 'full' );
				}
				update_post_meta( $post_id, $key, $value );
			}
		}
	}

	/**
	 * Format imported value
	 *
	 * @param mixed                         $value Field value.
	 * @param array                         $mapped_field Field mapping conf.
	 * @param Air_WP_Sync_Abstract_Importer $importer Importer.
	 * @param mixed                         $post_id WordPress object id.
	 */
	protected function format( $value, $mapped_field, $importer, $post_id ) {
		$airtable_id = $mapped_field['airtable'];
		$destination = $mapped_field['wordpress'];
		$source_type = $this->get_source_type( $airtable_id, $importer );

		if ( in_array( $destination, array( '_yoast_wpseo_opengraph-image', '_yoast_wpseo_twitter-image' ), true ) ) {
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
		} else {
			if ( 'richText' === $source_type ) {
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

			// Escape html tags for description's fields.
			if ( in_array( $destination, array( '_yoast_wpseo_metadesc', '_yoast_wpseo_opengraph-description', '_yoast_wpseo_twitter-description' ), true ) ) {
				$value = wp_strip_all_tags( $value );
			}
		}

		return $value;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_group() {
		return array(
			'label' => __( 'Yoast SEO', 'air-wp-sync' ),
			'slug'  => $this->slug,
		);
	}

	/**
	 * Add field features for each post types
	 *
	 * @param array  $features Features list.
	 * @param string $post_type Post type.
	 *
	 * @return string[]
	 */
	public function add_features_by_post_type( $features, $post_type ) {
		$destination_features = array();
		if ( is_post_type_viewable( $post_type ) ) {
			$destination_features = array(
				'_yoast_wpseo_title',
				'_yoast_wpseo_metadesc',
				'_yoast_wpseo_focuskw',
				'_yoast_wpseo_bctitle',
				'_yoast_wpseo_canonical',
				'_yoast_wpseo_opengraph-title',
				'_yoast_wpseo_opengraph-description',
				'_yoast_wpseo_twitter-title',
				'_yoast_wpseo_twitter-description',
				'_yoast_wpseo_opengraph-image',
				'_yoast_wpseo_twitter-image',
			);
		}
		$features[ $this->slug ] = $destination_features;
		return $features;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_mapping_fields() {
		return array(
			array(
				'value'             => '_yoast_wpseo_title',
				'label'             => __( 'SEO Title', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText' ),
			),
			array(
				'value'             => '_yoast_wpseo_metadesc',
				'label'             => __( 'Meta Description', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText', 'richText', 'multilineText' ),
			),
			array(
				'value'             => '_yoast_wpseo_focuskw',
				'label'             => __( 'Focus Keyphrase', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText' ),
			),
			array(
				'value'             => '_yoast_wpseo_bctitle',
				'label'             => __( 'Breadcrumbs Title (Advanced)', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText' ),
			),
			array(
				'value'             => '_yoast_wpseo_canonical',
				'label'             => __( 'Canonical URL (Advanced)', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'url' ),
			),
			array(
				'value'             => '_yoast_wpseo_opengraph-title',
				'label'             => __( 'Facebook Title', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText' ),
			),
			array(
				'value'             => '_yoast_wpseo_opengraph-description',
				'label'             => __( 'Facebook Description', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText', 'richText', 'multilineText' ),
			),
			array(
				'value'             => '_yoast_wpseo_twitter-title',
				'label'             => __( 'X(Twitter) Title', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText' ),
			),
			array(
				'value'             => '_yoast_wpseo_twitter-description',
				'label'             => __( 'X(Twitter) Description', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText', 'richText', 'multilineText' ),
			),
			array(
				'value'             => '_yoast_wpseo_opengraph-image',
				'label'             => __( 'Facebook Image', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'multipleAttachments' ),
			),
			array(
				'value'             => '_yoast_wpseo_twitter-image',
				'label'             => __( 'X(Twitter) Image', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'multipleAttachments' ),
			),
		);
	}
}
