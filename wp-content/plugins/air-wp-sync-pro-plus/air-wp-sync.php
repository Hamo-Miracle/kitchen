<?php
/**
 * Plugin Name: Air WP Sync Pro+ - Airtable to WordPress
 * Plugin URI: https://wpconnect.co/air-wp-sync-plugin/
 * Description: Swiftly sync Airtable to your WordPress website!
 * Version: 2.9.0
 * Requires at least: 5.7
 * Tested up to: 6.8.1
 * Requires PHP: 7.0
 * Author: WP connect
 * Author URI: https://wpconnect.co/
 * License: GPLv2 or later License
 * Text Domain: air-wp-sync
 * Domain Path: /languages/
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Check if Air WP Sync pro is activated.
( function () {
	if ( file_exists( WP_PLUGIN_DIR . '/air-wp-sync-pro/air-wp-sync.php' ) ) {
		if ( is_multisite() && is_network_admin() ) {
			$active_plugins = (array) get_site_option( 'active_sitewide_plugins', array() );
			$active_plugins = array_keys( $active_plugins );
		} else {
			$active_plugins = (array) get_option( 'active_plugins', array() );
		}
		if ( in_array( 'air-wp-sync-pro/air-wp-sync.php', $active_plugins, true ) ) {
			set_transient( 'airwpsync_pro_deactivated_notice_id', 1, 1 * HOUR_IN_SECONDS );
			deactivate_plugins( 'air-wp-sync-pro/air-wp-sync.php' );
		}

		if ( (int) get_transient( 'airwpsync_pro_deactivated_notice_id' ) === 1 ) {
			add_action(
				'admin_notices',
				function () {
					?><div class="notice notice-warning">
						<p><?php esc_html_e( "Air WP Sync Pro+ and Air WP Sync Pro should not be active at the same time. We've automatically deactivated Air WP Sync Pro.", 'air-wp-sync' ); ?></p>
					</div>
					<?php
					delete_transient( 'airwpsync_pro_deactivated_notice_id' );
				}
			);
		}
	}
} )();

define( 'AIR_WP_SYNC_PRO_VERSION', '2.9.0' );
define( 'AIR_WP_SYNC_PRO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AIR_WP_SYNC_PRO_PLUGIN_FILE', __FILE__ );
define( 'AIR_WP_SYNC_PRO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AIR_WP_SYNC_PRO_BASENAME', plugin_basename( __FILE__ ) );
define( 'AIR_WP_SYNC_PRO_LOGDIR', wp_upload_dir( null, false )['basedir'] . '/airwpsync-logs/' );

require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'vendor/woocommerce/action-scheduler/action-scheduler.php';

require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/class-air-wp-sync-services.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/class-air-wp-sync-abstract-settings.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/class-air-wp-sync-abstract-module.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/class-air-wp-sync-abstract-importer.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/class-air-wp-sync-abstract-destination.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/class-air-wp-sync-api-abstract-route.php';


require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/class-air-wp-sync.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/class-air-wp-sync-parsedown.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/class-air-wp-sync-options.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/class-air-wp-sync-licensing.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/class-air-wp-sync-updater.php';

require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/class-air-wp-sync-action-consumer.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/class-air-wp-sync-importer-settings.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/class-air-wp-sync-helper.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/class-air-wp-sync-filters.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/admin/class-air-wp-sync-admin.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/class-air-wp-sync-airtable-api-client.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/class-air-wp-sync-cli.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/class-air-wp-sync-api-import-route.php';

require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'modules/post/class-air-wp-sync-post-module.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'modules/user/class-air-wp-sync-user-module.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'modules/term/class-air-wp-sync-term-module.php';

require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/formatters/class-air-wp-sync-attachments-formatter.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/formatters/class-air-wp-sync-markdown-formatter.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/formatters/class-air-wp-sync-terms-formatter.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/formatters/class-air-wp-sync-interval-formatter.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/sources/class-air-wp-sync-barcode-source.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/sources/class-air-wp-sync-collaborator-source.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/sources/class-air-wp-sync-link-to-another-record-source.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/sources/class-air-wp-sync-formula-source.php';
require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'includes/sources/class-air-wp-sync-unsupported-source.php';

if ( file_exists( AIR_WP_SYNC_PRO_PLUGIN_DIR . 'pro-plus/class-air-wp-sync-pro-plus.php' ) ) {
	require_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'pro-plus/class-air-wp-sync-pro-plus.php';
	new Air_WP_Sync_Pro_Plus();
}

if ( ! defined( 'AIR_WP_SYNC_PRO_WPC_PRODUCT_ID' ) ) {
	define( 'AIR_WP_SYNC_PRO_WPC_PRODUCT_ID', 5437 );
}

if ( ! defined( 'AIR_WP_SYNC_PRO_WPC_URL' ) ) {
	define( 'AIR_WP_SYNC_PRO_WPC_URL', 'https://wpconnect.co' );
}

register_deactivation_hook( __FILE__, __NAMESPACE__ . '\air_wp_sync_pro_deactivate' );


if ( ! function_exists( __NAMESPACE__ . '\air_wp_sync_pro_deactivate' ) ) {
	/**
	 * The code that runs during plugin deactivation.
	 */
	function air_wp_sync_pro_deactivate() {
		// flush rewrite rules.
		flush_rewrite_rules();
		// Clear hooks.
		foreach ( _get_cron_array() as $cron ) {
			foreach ( array_keys( $cron ) as $hook ) {
				if ( strpos( $hook, 'air_wp_sync_importer_' ) === 0 ) {
					wp_clear_scheduled_hook( $hook );
				}
			}
		}
	}
}

register_uninstall_hook( __FILE__, __NAMESPACE__ . '\air_wp_sync_pro_uninstall' );
if ( ! function_exists( __NAMESPACE__ . '\air_wp_sync_pro_uninstall' ) ) {
	/**
	 * Uninstall procedure
	 */
	function air_wp_sync_pro_uninstall() {
		$options     = new Air_WP_Sync_Options();
		$licensing   = new Air_WP_Sync_Licensing( AIR_WP_SYNC_PRO_WPC_URL, AIR_WP_SYNC_PRO_WPC_PRODUCT_ID );
		$license_key = $options->get( 'license_key' );

		if ( ! empty( $license_key ) ) {
			$result = $licensing->deactivate( $license_key );
			if ( ! is_wp_error( $result ) ) {
				$options->delete_options();
			} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( $result->get_error_message() );
			}
		}
	}
}

// Init plugin.
new Air_WP_Sync();
