<?php
/**
 * Manages organizers import for The Events Calendar.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

/**
 * Air_WP_Sync_Events_Calendar_Destination_Organizer class.
 */
class Air_WP_Sync_Events_Calendar_Destination_Organizer extends Air_WP_Sync_Abstract_Destination {

	/**
	 * Destination slug.
	 *
	 * @var string
	 */
	protected $slug = 'events-calendar-organizer';


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

		add_filter( 'tec_events_custom_tables_v1_tracked_meta_keys', array( $this, 'register_tracked_meta_keys' ), 10, 2 );
		add_action( 'airwpsync_process_records', array( $this, 'remove_pre_get_posts_filters' ), 5, 3 );
		add_action( 'airwpsync/import_record_after', array( $this, 'add_metas' ), 10, 4 );
		add_filter( 'airwpsync/features_by_post_type', array( $this, 'add_features_by_post_type' ), 10, 2 );
	}

	/**
	 * Removes filters on `pre_get_posts` when processing `tribe_organizer`,
	 * as The Events Calendar adds filters that prevent AirWPSync from finding existing posts.
	 *
	 * @param int    $importer_id Importer post id.
	 * @param string $run_id Importer run id.
	 * @param string $item_id Records chunk id.
	 */
	public function remove_pre_get_posts_filters( $importer_id, $run_id, $item_id ) {
		// Get importer instance from id.
		$importer = Air_WP_Sync_Helper::get_importer_by_id( $importer_id );
		if ( ! $importer ) {
			return;
		}
		// Remove all filters on `pre_get_posts`.
		if ( 'post' === $importer->get_module()->get_slug() && 'tribe_organizer' === $importer->get_post_type() ) {
			remove_all_filters( 'pre_get_posts' );
		}
	}

	/**
	 * Registers additional meta keys to track for The Events Calendar to trigger custom table updates
	 *
	 * @param array    $keys The list of tracked meta keys.
	 * @param int|null $id  The ID of the object (could not be an Event post!)
	 *                      the filters are being applied for, or `null` if the tracked keys
	 *                      should not be specific to an Event.
	 * @return  array $keys Additional keys to track.
	 */
	public function register_tracked_meta_keys( $keys, $id ) {
		$keys[] = '_OrganizerOrigin';
		$keys[] = '_air_wp_sync_hash';
		$keys[] = '_air_wp_sync_updated_at';
		return $keys;
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
				update_post_meta( $post_id, $key, $value );
			}
		}

		// Trigger update in the custom database.
		if ( 'tribe_organizer' === $importer->get_post_type() ) {
			update_post_meta( $post_id, '_OrganizerOrigin', 'AirWPSync' );
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
	protected function format( $value, $mapped_field, $importer, $post_id = null ) {
		$airtable_id = $mapped_field['airtable'];
		$destination = $mapped_field['wordpress'];
		$source_type = $this->get_source_type( $airtable_id, $importer );

		// Formats raw value.
		if ( 'richText' === $source_type ) {
			// Markdown.
			$value = $this->markdown_formatter->format( $value );
		} elseif ( 'multipleAttachments' === $source_type ) {
			// Attachments.
			$value = $this->attachment_formatter->format( $value, $importer, $post_id );
		} elseif ( 'url' === $source_type ) {
			// URLs.
			$value = esc_url_raw( $value );
		} elseif ( 'checkbox' === $source_type ) {
			// Checkbox.
			// Convert boolean to 0|1.
			$value = (int) filter_var( $value, FILTER_VALIDATE_BOOLEAN );
		}

		return $value;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_group() {
		return array(
			'label' => __( 'The Events Calendar', 'air-wp-sync' ),
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
		if ( 'tribe_organizer' === $post_type ) {
			$destination_features = array(
				'_OrganizerOrigin',
				'_OrganizerOrganizerID',
				'_OrganizerPhone',
				'_OrganizerWebsite',
				'_OrganizerEmail',
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
				'value'             => '_OrganizerPhone',
				'label'             => __( 'Organizer Phone', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText', 'singleSelect', 'phoneNumber' ),
			),
			array(
				'value'             => '_OrganizerWebsite',
				'label'             => __( 'Organizer Website', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText', 'url', 'singleSelect' ),
			),
			array(
				'value'             => '_OrganizerEmail',
				'label'             => __( 'Organizer Email', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText', 'email', 'singleSelect' ),
			),
		);
	}
}
