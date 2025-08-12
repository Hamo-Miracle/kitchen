<?php
/**
 * Manages licence admin page.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

/**
 * Admin Page class
 */
class Air_WP_Sync_Admin_Page {
	/**
	 * Plugin settings
	 *
	 * @var Air_WP_Sync_Settings
	 */
	protected $options;

	/**
	 * Licensing
	 *
	 * @var Air_WP_Sync_Licensing
	 */
	protected $licensing;

	/**
	 * Admin page slug
	 *
	 * @var string
	 */
	protected $page_slug = 'air-wp-sync-settings';

	/**
	 * Constructor
	 *
	 * @param Air_WP_Sync_Options   $options Plugin settings.
	 * @param Air_WP_Sync_Licensing $licensing Licensing.
	 */
	public function __construct( $options, $licensing ) {
		$this->options   = $options;
		$this->licensing = $licensing;

		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_styles_scripts' ) );
	}

	/**
	 * Add setting page in admin menu
	 */
	public function add_menu() {
		add_submenu_page(
			'edit.php?post_type=airwpsync-connection',
			__( 'Settings', 'air-wp-sync' ),
			__( 'Settings', 'air-wp-sync' ),
			apply_filters( 'airwpsync/manage_options_capability', 'manage_options' ),
			$this->page_slug,
			array( $this, 'admin_page' )
		);
	}


	/**
	 * Register admin styles and scripts
	 */
	public function register_styles_scripts() {
		$screen = get_current_screen();
		if ( is_object( $screen ) && 'air-wp-sync_page_air-wp-sync-settings' === $screen->id ) {
			wp_enqueue_script( 'air-wp-sync-settings', plugins_url( 'assets/js/settings-page.js', AIR_WP_SYNC_PRO_PLUGIN_FILE ), array( 'jquery-ui-tooltip' ), AIR_WP_SYNC_PRO_VERSION, false );
		}
	}


	/**
	 * Render admin page
	 */
	public function admin_page() {
		$this->maybe_activate_license();
		$this->maybe_deactivate_license();
		$this->maybe_update_settings();
		$view = include_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'views/settings-page.php';
		$view( $this->options );
	}

	/**
	 * Activate License
	 */
	protected function maybe_activate_license() {
		if ( ! isset( $_POST['air-wp-sync-license-activate'] ) || ! check_admin_referer( 'air-wp-sync-settings-form' ) ) {
			return;
		}

		$license_key = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : '';
		$result      = $this->licensing->activate( $license_key );

		if ( is_wp_error( $result ) ) {
			$this->display_message( $result->get_error_message(), 'error' );
		} else {
			$this->options->set( 'license_status', $result );
			$this->options->set( 'license_key', $license_key );
			$this->options->save();
			$this->display_message( __( 'License successfully activated!', 'air-wp-sync' ), 'success' );
		}
	}

	/**
	 * Deactivate License
	 */
	protected function maybe_deactivate_license() {
		if ( ! isset( $_POST['air-wp-sync-license-deactivate'] ) || ! check_admin_referer( 'air-wp-sync-settings-form' ) ) {
			return;
		}

		$license_key = $this->options->get( 'license_key' );
		$result      = $this->licensing->deactivate( $license_key );

		if ( is_wp_error( $result ) ) {
			$this->display_message( $result->get_error_message(), 'error' );
		} else {
			$this->options->delete( 'license_status' );
			$this->options->save();
			$this->display_message( __( 'License successfully deactivated.', 'air-wp-sync' ), 'success' );
		}
	}

	/**
	 * Update settings based on form submission
	 */
	protected function maybe_update_settings() {
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'air-wp-sync-settings-form' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return;
		}

		if ( isset( $_POST['air-wp-sync-settings-clear-cache'] ) ) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_airwpsync_tables_%'" );
			$this->display_message( __( 'Table cache cleared!', 'air-wp-sync' ), 'success' );
		}

		if ( isset( $_POST['air-wp-sync-settings-update'] ) ) {
			$old_license_key = $this->options->get( 'license_key' );
			$new_license_key = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : '';

			if ( $old_license_key !== $new_license_key ) {
				$this->options->set( 'license_key', $new_license_key );
				$this->options->delete( 'license_status' );
			}

			$cache_duration = ! empty( $_POST['cache_duration'] ) ? max( 2, (int) $_POST['cache_duration'] ) : 15;
			$this->options->set( 'cache_duration', $cache_duration );

			// Save options.
			$this->options->save();
			$this->display_message( __( 'Settings saved!', 'air-wp-sync' ), 'success' );
		}
	}

	/**
	 * Display a WP notice
	 *
	 * @param string $message Message.
	 * @param string $type Message type (@see https://developer.wordpress.org/reference/hooks/admin_notices/#example).
	 */
	protected function display_message( $message, $type = 'info' ) {
		echo '<div class="notice notice-' . esc_attr( $type ) . '"><p>' . esc_html( $message ) . '</p></div>';
	}
}
