<?php
/**
 * Manages import as AllInOne SEO fields.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

/**
 * Air_WP_Sync_ACF_Destination class.
 */
class Air_WP_Sync_AIOSEO_Destination extends Air_WP_Sync_Abstract_Destination {

	/**
	 * Destination slug.
	 *
	 * @var string
	 */
	protected $slug = 'aioseo';

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
		$post = \AIOSEO\Plugin\Common\Models\Post::getPost( $post_id );

		// Set AIOSEO post if not exists.
		if ( ! $post->exists() ) {
			$post->savePost(
				$post_id,
				array(
					'created'             => gmdate( 'Y-m-d H:i:s' ),
					'updated'             => gmdate( 'Y-m-d H:i:s' ),
					'title'               => '',
					'description'         => '',
					'og_title'            => '',
					'og_description'      => '',
					'twitter_title'       => '',
					'twitter_description' => '',
					'og_article_section'  => '',
				)
			);
		}

		$mapped_fields = $this->get_destination_mapping( $importer, $fields );
		foreach ( $mapped_fields as $mapped_field ) {

			// Get meta value.
			$value = $this->get_airtable_value( $fields, $mapped_field['airtable'], $importer );
			$value = $this->format( $value, $mapped_field, $importer, $post_id );

			// Get meta key.
			$key = $mapped_field['wordpress'];

			// Save meta.
			if ( ! empty( $key ) ) {
				$db_key = preg_replace( '/^_aioseo_/', '$2', $key );
				$metas  = array(
					$db_key   => $value,
					'updated' => gmdate( 'Y-m-d H:i:s' ),
				);

				if ( in_array( $key, array( 'og_image_url', 'twitter_image_url' ), true ) ) {
					$prefix                                 = explode( '_', $key )[0];
					$metas[ $prefix . '_image_type' ]       = 'custom_image';
					$metas[ $db_key ]                       = wp_get_attachment_image_url( $value, 'full' );
					$metas[ $prefix . '_image_custom_url' ] = $metas[ $db_key ];

					if ( 'og' === $prefix ) {
						$media_data                         = wp_get_attachment_metadata( $value );
						$metas[ $prefix . '_image_width' ]  = $media_data['width'];
						$metas[ $prefix . '_image_height' ] = $media_data['height'];
					}
				}

				if ( 'keyphrases' === $key ) {
					$keyphrases                   = $post->getKeyphrasesDefaults();
					$keyphrases->focus->keyphrase = is_array( $value ) ? implode( ' ', $value ) : $value;
					$metas[ $db_key ]             = wp_json_encode( $keyphrases );
				}

				if ( 'canonical_url' === $key ) {
					$metas[ $db_key ] = esc_url( $metas[ $db_key ] );
				}

				// Escape html tags for description fields.
				if ( in_array( $key, array( '_aioseo_description', '_aioseo_og_description', '_aioseo_twitter_description' ), true ) ) {
					$metas[ $db_key ] = wp_strip_all_tags( $value );
				}

				aioseo()->core->db
					->update( 'aioseo_posts' )
					->set( $metas )
					->where( array( 'post_id' => $post_id ) )
					->run();

				// Set post metas only for specific datas.
				if ( preg_match( '/^_aioseo_/', $key ) ) {
					update_post_meta( $post_id, $key, $value );
				}
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

		if ( in_array( $destination, array( 'og_image_url', 'twitter_image_url' ), true ) ) {
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

		// Escape html tags for description's fields.
		if ( in_array( $destination, array( '_aioseo_description', '_aioseo_og_description', '_aioseo_twitter_description' ), true ) ) {
			$value = wp_strip_all_tags( $value );
		}

		return $value;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_group() {
		return array(
			'label' => __( 'All in One SEO', 'air-wp-sync' ),
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
				'_aioseo_title',
				'_aioseo_description',
				'_aioseo_og_title',
				'_aioseo_og_description',
				'_aioseo_twitter_title',
				'_aioseo_twitter_description',
				'twitter_use_og',
				'og_image_url',
				'twitter_image_url',
				'canonical_url',
				'keyphrases',
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
				'value'             => '_aioseo_title',
				'label'             => __( 'Meta Title', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText' ),
			),
			array(
				'value'             => '_aioseo_description',
				'label'             => __( 'Meta Description', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText', 'richText', 'multilineText' ),
			),
			array(
				'value'             => 'keyphrases',
				'label'             => __( 'Focus Keyphrase', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText' ),
			),
			array(
				'value'             => 'canonical_url',
				'label'             => __( 'Canonical URL', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText', 'url' ),
			),
			array(
				'value'             => '_aioseo_og_title',
				'label'             => __( 'Facebook Title', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText' ),
			),
			array(
				'value'             => '_aioseo_og_description',
				'label'             => __( 'Facebook Description', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText', 'richText', 'multilineText' ),
			),
			array(
				'value'             => '_aioseo_twitter_title',
				'label'             => __( 'X(Twitter) Title', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText' ),
			),
			array(
				'value'             => '_aioseo_twitter_description',
				'label'             => __( 'X(Twitter) Description', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText', 'richText', 'multilineText' ),
			),
			array(
				'value'             => 'og_image_url',
				'label'             => __( 'Facebook Image', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'multipleAttachments' ),
			),
			array(
				'value'             => 'twitter_image_url',
				'label'             => __( 'X(Twitter) Image', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'multipleAttachments' ),
			),
			array(
				'value'             => 'twitter_use_og',
				'label'             => __( 'Use data from Facebook Tab', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'checkbox' ),
			),
		);
	}
}
