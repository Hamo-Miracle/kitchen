<?php
/**
 * Main entry point for the Pro Plus plugin, initialize all features.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

use WP_CLI;

define( 'AIR_WP_SYNC_PRO_PLUS_PLUGIN_DIR', AIR_WP_SYNC_PRO_PLUGIN_DIR . 'pro-plus/' );

if ( ! defined( 'AIR_WP_SYNC_PRO_WPC_PRODUCT_ID' ) ) {
	define( 'AIR_WP_SYNC_PRO_WPC_PRODUCT_ID', 7357 );
}

require_once AIR_WP_SYNC_PRO_PLUS_PLUGIN_DIR . 'includes/destinations/acf/class-air-wp-sync-abstract-acf-destination.php';
require_once AIR_WP_SYNC_PRO_PLUS_PLUGIN_DIR . 'includes/destinations/acf/class-air-wp-sync-acf-destination-post.php';
require_once AIR_WP_SYNC_PRO_PLUS_PLUGIN_DIR . 'includes/destinations/acf/class-air-wp-sync-acf-destination-user.php';

/**
 * Air_WP_Sync_Pro_Plus class.
 */
class Air_WP_Sync_Pro_Plus {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'airwpsync/register_destination', array( $this, 'register_acf_destination' ), 10, 0 );
		add_action( 'airwpsync/register_destination', array( $this, 'register_yoast_destination' ), 10, 0 );
		add_action( 'airwpsync/register_destination', array( $this, 'register_rankmath_destination' ), 10, 0 );
		add_action( 'airwpsync/register_destination', array( $this, 'register_seopress_destination' ), 10, 0 );
		add_action( 'airwpsync/register_destination', array( $this, 'register_aioseo_destination' ), 10, 0 );
		add_action( 'airwpsync/register_destination', array( $this, 'register_events_calendar_destination' ), 10, 0 );
	}

	/**
	 * Init plugin.
	 */
	public function register_acf_destination() {
		if ( class_exists( 'ACF' ) ) {
			new Air_WP_Sync_ACF_Destination_Post( new Air_WP_Sync_Markdown_Formatter( new Air_WP_Sync_Parsedown() ), Air_WP_Sync_Services::get_instance()->get( 'attachments_formatter' ), new Air_WP_Sync_Interval_Formatter(), new Air_WP_Sync_Terms_Formatter() );
			new Air_WP_Sync_ACF_Destination_User( new Air_WP_Sync_Markdown_Formatter( new Air_WP_Sync_Parsedown() ), Air_WP_Sync_Services::get_instance()->get( 'attachments_formatter' ), new Air_WP_Sync_Interval_Formatter(), new Air_WP_Sync_Terms_Formatter() );
		}
	}

	/**
	 * Yoast SEO support
	 */
	public function register_yoast_destination() {
		if ( class_exists( 'WPSEO_Options' ) ) {
			require_once AIR_WP_SYNC_PRO_PLUS_PLUGIN_DIR . 'includes/destinations/yoast/class-air-wp-sync-yoast-destination-post.php';
			new Air_WP_Sync_Yoast_Destination_Post( new Air_WP_Sync_Markdown_Formatter( new Air_WP_Sync_Parsedown() ), Air_WP_Sync_Services::get_instance()->get( 'attachments_formatter' ) );
		}
		if ( class_exists( 'WPSEO_Taxonomy_Meta' ) ) {
			require_once AIR_WP_SYNC_PRO_PLUS_PLUGIN_DIR . 'includes/destinations/yoast/class-air-wp-sync-yoast-destination-term.php';
			new Air_WP_Sync_Yoast_Destination_Term( new Air_WP_Sync_Markdown_Formatter( new Air_WP_Sync_Parsedown() ), Air_WP_Sync_Services::get_instance()->get( 'attachments_formatter' ) );
		}
	}

	/**
	 * RankMath SEO support
	 */
	public function register_rankmath_destination() {
		if ( class_exists( 'RankMath' ) ) {
			require_once AIR_WP_SYNC_PRO_PLUS_PLUGIN_DIR . 'includes/destinations/class-air-wp-sync-rankmath-destination.php';
			new Air_WP_Sync_RankMath_Destination( new Air_WP_Sync_Markdown_Formatter( new Air_WP_Sync_Parsedown() ), Air_WP_Sync_Services::get_instance()->get( 'attachments_formatter' ) );
		}
	}

	/**
	 * SEOPress support
	 */
	public function register_seopress_destination() {
		if ( class_exists( 'SEOPress\Core\Kernel' ) ) {
			require_once AIR_WP_SYNC_PRO_PLUS_PLUGIN_DIR . 'includes/destinations/class-air-wp-sync-seopress-destination.php';
			new Air_WP_Sync_SEOPress_Destination( new Air_WP_Sync_Markdown_Formatter( new Air_WP_Sync_Parsedown() ), Air_WP_Sync_Services::get_instance()->get( 'attachments_formatter' ) );
		}
	}

	/**
	 * All in One SEO Module support instanciation
	 */
	public function register_aioseo_destination() {
		if ( class_exists( 'AIOSEO\Plugin\AIOSEO' ) ) {
			require_once AIR_WP_SYNC_PRO_PLUS_PLUGIN_DIR . 'includes/destinations/class-air-wp-sync-aioseo-destination.php';
			new Air_WP_Sync_AIOSEO_Destination( new Air_WP_Sync_Markdown_Formatter( new Air_WP_Sync_Parsedown() ), Air_WP_Sync_Services::get_instance()->get( 'attachments_formatter' ) );
		}
	}

	/**
	 * The Events Calendar support instanciation
	 */
	public function register_events_calendar_destination() {
		if ( class_exists( 'Tribe__Events__Main' ) ) {
			require_once AIR_WP_SYNC_PRO_PLUS_PLUGIN_DIR . 'includes/destinations/events-calendar/class-air-wp-sync-events-calendar-destination-event.php';
			require_once AIR_WP_SYNC_PRO_PLUS_PLUGIN_DIR . 'includes/destinations/events-calendar/class-air-wp-sync-events-calendar-destination-venue.php';
			require_once AIR_WP_SYNC_PRO_PLUS_PLUGIN_DIR . 'includes/destinations/events-calendar/class-air-wp-sync-events-calendar-destination-organizer.php';
			new Air_WP_Sync_Events_Calendar_Destination_Event( new Air_WP_Sync_Markdown_Formatter( new Air_WP_Sync_Parsedown() ), Air_WP_Sync_Services::get_instance()->get( 'attachments_formatter' ) );
			new Air_WP_Sync_Events_Calendar_Destination_Venue( new Air_WP_Sync_Markdown_Formatter( new Air_WP_Sync_Parsedown() ), Air_WP_Sync_Services::get_instance()->get( 'attachments_formatter' ) );
			new Air_WP_Sync_Events_Calendar_Destination_Organizer( new Air_WP_Sync_Markdown_Formatter( new Air_WP_Sync_Parsedown() ), Air_WP_Sync_Services::get_instance()->get( 'attachments_formatter' ) );
		}
	}
}
