<?php
/**
 * Display modules settings.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

/**
 * Class Air_WP_Sync_Metabox_Importer_Settings
 */
class Air_WP_Sync_Metabox_Importer_Settings {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
	}

	/**
	 * Add metabox
	 */
	public function add_meta_box() {
		add_meta_box(
			'airwpsync-post-settings',
			__( 'Import As...', 'air-wp-sync' ),
			array( $this, 'display' ),
			'airwpsync-connection',
			'normal',
			'high'
		);
	}

	/**
	 * Output metabox HTML
	 *
	 * @param \WP_Post $post The connection.
	 */
	public function display( $post ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		$modules = Air_WP_Sync_Helper::get_modules();
		$view    = include_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'views/metabox-importer-settings.php';
		$view( $modules, $post );
	}
}
