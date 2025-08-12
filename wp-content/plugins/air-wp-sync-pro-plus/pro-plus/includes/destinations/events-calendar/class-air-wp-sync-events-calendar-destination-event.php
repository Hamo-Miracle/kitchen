<?php
/**
 * Manages events import for The Events Calendar.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

/**
 * Air_WP_Sync_Events_Calendar_Destination_Event class.
 */
class Air_WP_Sync_Events_Calendar_Destination_Event extends Air_WP_Sync_Abstract_Destination {

	/**
	 * Destination slug.
	 *
	 * @var string
	 */
	protected $slug = 'events-calendar';


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
	 * Datetime supported sources.
	 *
	 * @var string[]
	 */
	protected $datetime_supported_sources = array(
		'date',
		'dateTime',
		'createdTime',
		'lastModifiedTime',
	);

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
	 * Removes filters on `pre_get_posts` when processing `tribe_events`,
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
		if ( 'post' === $importer->get_module()->get_slug() && 'tribe_events' === $importer->get_post_type() ) {
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
		$keys[] = '_EventOrigin';
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
		$mapped_fields   = $this->get_destination_mapping( $importer, $fields );
		$timezone_string = $this->get_timezone_string_mapping_value( $importer, $fields, $mapped_fields );

		foreach ( $mapped_fields as $mapped_field ) {

			// Get meta value.
			$raw_value = $this->get_airtable_value( $fields, $mapped_field['airtable'], $importer );
			$value     = $this->format( $raw_value, $mapped_field, $importer, $post_id, $timezone_string );

			// Get meta key.
			$key = $mapped_field['wordpress'];

			// Save meta.
			if ( ! empty( $key ) ) {
				update_post_meta( $post_id, $key, $value );

				if ( in_array( $key, array( '_EventStartDate', '_EventEndDate' ), true ) ) {
					try {
						// Date from AirTable is always UTC. Save it now.
						$date_utc  = new \DateTime( $raw_value, new \DateTimeZone( 'UTC' ) );
						$formatted = $date_utc->format( 'Y-m-d H:i:s' );
						$meta_key  = '_EventStartDate' === $key ? '_EventStartDateUTC' : '_EventEndDateUTC';
						update_post_meta( $post_id, $meta_key, $formatted );
					} catch ( \Exception $e ) {
						$importer->log( $e->getMessage(), 'error' );
					}
				}

				if ( '_EventOrganizerID' === $key ) {
					delete_post_meta( $post_id, '_EventOrganizerID' );
					foreach ( $value as $id ) {
						add_post_meta( $post_id, '_EventOrganizerID', $id, false );
					}
				}
			}
		}

		// Trigger update in the events custom database.
		if ( 'tribe_event' === $importer->get_post_type() ) {
			// Update timezone.
			update_post_meta( $post_id, '_EventTimezone', $timezone_string );
			update_post_meta( $post_id, '_EventOrigin', 'AirWPSync' );
		}
	}

	/**
	 * Looks for timezone mapping value in the Airtable record
	 *
	 * @param Air_WP_Sync_Abstract_Importer $importer Importer.
	 * @param array                         $fields  Fields.
	 * @param array                         $mapped_fields  Mapped fields.
	 * @return  string  $timezone_string  Timezone string
	 */
	public function get_timezone_string_mapping_value( $importer, $fields, $mapped_fields = array() ) {
		if ( empty( $mapped_fields ) ) {
			$mapped_fields = $this->get_destination_mapping( $importer );
		}

		$timezone_string = wp_timezone_string();
		foreach ( $mapped_fields as $mapped_field ) {
			if ( '_EventTimezone' === $mapped_field['wordpress'] ) {
				$value = $this->get_airtable_value( $fields, $mapped_field['airtable'], $importer );
				if ( ! empty( $value ) ) {
					$timezone_string = $this->format( $value, $mapped_field, $importer, null );
				}
			}
		}
		return $timezone_string;
	}

	/**
	 * Format imported value
	 *
	 * @param mixed                         $value Field value.
	 * @param array                         $mapped_field Field mapping conf.
	 * @param Air_WP_Sync_Abstract_Importer $importer Importer.
	 * @param mixed                         $post_id WordPress object id.
	 * @param string                        $timezone_string Timezone for formatting dates.
	 */
	protected function format( $value, $mapped_field, $importer, $post_id = null, $timezone_string = null ) {
		$airtable_id = $mapped_field['airtable'];
		$destination = $mapped_field['wordpress'];
		$source_type = $this->get_source_type( $airtable_id, $importer );

		if ( ! $timezone_string ) {
			$timezone_string = wp_timezone_string();
		}

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

		// Formats specific destination fields.
		if ( '_EventTimezone' === $destination ) {
			if ( ! in_array( $value, timezone_identifiers_list(), true ) ) {
				$value = wp_timezone_string();
			}
		} elseif ( '_EventHideFromUpcoming' === $destination ) {
			$value = ! empty( $value ) ? 'yes' : false;
		} elseif ( '_EventCurrencyPosition' === $destination ) {
			$value = 'prefix' === $value ? 'prefix' : 'suffix';
		} elseif ( '_tribe_events_status' === $destination ) {
			$statuses = (array) apply_filters( 'tec_event_statuses', array(), '' );
			$slugs    = ! empty( $statuses ) ? wp_list_pluck( $statuses, 'value' ) : array( 'scheduled', 'postponed', 'canceled' );
			if ( ! in_array( $value, $slugs, true ) ) {
				$value = 'scheduled';
			}
		} elseif ( in_array( $destination, array( '_EventStartDate', '_EventEndDate' ), true ) ) {
			try {
				// Date from AirTable is always UTC. Convert it to the given timezone.
				$date_utc = new \DateTime( $value, new \DateTimeZone( 'UTC' ) );
				$value    = $date_utc->setTimezone( new \DateTimeZone( $timezone_string ) )->format( 'Y-m-d H:i:s' );
			} catch ( \Exception $e ) {
				$importer->log( $e->getMessage(), 'error' );
			}
		} elseif ( '_EventVenueID' === $destination ) {
			$value = (int) $value;
		} elseif ( '_EventOrganizerID' === $destination ) {
			if ( ! is_array( $value ) ) {
				$value = array( $value );
			}
			$value = array_reduce( $value, array( $this, 'recursive_split' ), array() );
			$value = array_map(
				function ( $v ) {
					return (int) $v;
				},
				$value
			);
		}

		return $value;
	}

	/**
	 * Callback function used to recursively merge array, and split strings by commas
	 *
	 * @param  array        $carry  Array of values from the preceding iteration.
	 * @param  array|string $item  Current item.
	 * @return  array  $carry  Array of values after the current iteration.
	 */
	public function recursive_split( $carry, $item ) {
		if ( is_int( $item ) ) {
			$carry[] = (int) $item;
		}
		if ( is_string( $item ) ) {
			$carry = array_merge( $carry, array_map( 'trim', explode( ',', $item ) ) );
		}
		if ( is_array( $item ) ) {
			$carry = array_merge( $carry, $this->recursive_split( $carry, $item ) );
		}
		return $carry;
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
		if ( is_post_type_viewable( $post_type ) && 'tribe_events' === $post_type ) {
			$destination_features = array(
				'_EventOrigin',
				'_EventStartDate',
				'_EventStartDateUTC',
				'_tribe_events_status',
				'_tribe_events_status_reason', // Not used directly.
				'_EventEndDate',
				'_EventEndDateUTC',
				'_EventAllDay',
				'_EventShowMapLink',
				'_EventShowMap',
				'_EventVenueID',
				'_EventOrganizerID',
				'_EventDuration', // Not used directly.
				'_EventCurrencySymbol',
				'_EventCurrencyCode',
				'_EventCurrencyPosition',
				'_EventCost',
				'_EventURL',
				'_EventTimezone',
				'_EventTimezoneAbbr', // Not used directly.
				'_EventHideFromUpcoming',
				'_tribe_featured',
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
				'value'             => '_EventStartDate',
				'label'             => __( 'Event Start Date', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => $this->datetime_supported_sources,
			),
			array(
				'value'             => '_EventEndDate',
				'label'             => __( 'Event End Date', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => $this->datetime_supported_sources,
			),
			array(
				'value'             => '_EventAllDay',
				'label'             => __( 'Event All Day', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'checkbox' ),
			),
			array(
				'value'             => '_EventTimezone',
				'label'             => __( 'Event Timezone', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText', 'singleSelect' ),
			),
			array(
				'value'             => '_tribe_events_status',
				'label'             => __( 'Event Status', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText', 'singleSelect' ),
			),
			array(
				'value'             => '_EventVenueID',
				'label'             => __( 'Event Venue ID', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText', 'number', 'singleSelect' ),
			),
			array(
				'value'             => '_EventShowMap',
				'label'             => __( 'Show Map', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'checkbox' ),
			),
			array(
				'value'             => '_EventShowMapLink',
				'label'             => __( 'Show map link', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'checkbox' ),
			),
			array(
				'value'             => '_EventOrganizerID',
				'label'             => __( 'Event Organizer ID', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText', 'number', 'singleSelect', 'multipleSelects' ),
			),
			array(
				'value'             => '_EventURL',
				'label'             => __( 'Event URL', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText', 'url' ),
			),
			array(
				'value'             => '_EventCurrencySymbol',
				'label'             => __( 'Currency Symbol', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText', 'singleSelect' ),
			),
			array(
				'value'             => '_EventCurrencyPosition',
				'label'             => __( 'Currency Position', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText', 'singleSelect' ),
			),
			array(
				'value'             => '_EventCurrencyCode',
				'label'             => __( 'Currency Code', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText', 'singleSelect' ),
			),
			array(
				'value'             => '_EventCost',
				'label'             => __( 'Event cost', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'singleLineText', 'number', 'currency' ),
			),
			array(
				'value'             => '_tribe_featured',
				'label'             => __( 'Featured Event', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'checkbox' ),
			),
			array(
				'value'             => '_EventHideFromUpcoming',
				'label'             => __( 'Hide From Event Listings', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array( 'checkbox', 'singleLineText', 'singleSelect' ),
			),
		);
	}
}
