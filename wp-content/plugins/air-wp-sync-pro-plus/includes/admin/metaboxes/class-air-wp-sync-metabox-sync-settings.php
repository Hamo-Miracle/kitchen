<?php
/**
Manage the sync strategy options: manual, recurring...
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

/**
 * Class Air_WP_Sync_Metabox_Sync_Settings
 */
class Air_WP_Sync_Metabox_Sync_Settings {
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
			'airwpsync-sync-settings',
			__( 'Sync Settings', 'air-wp-sync' ),
			array( $this, 'display' ),
			'airwpsync-connection',
			'normal'
		);
	}

	/**
	 * Output metabox HTML
	 *
	 * @param WP_Post $post The connection.
	 */
	public function display( $post ) {
		$importer        = Air_WP_Sync_Helper::get_importer_by_id( $post->ID );
		$webhook_url     = $importer ? get_rest_url( null, 'airwpsync/v1/import/' . $importer->infos()->get( 'hash' ) ) : null;
		$sync_strategies = $this->get_sync_strategies();
		$schedules       = $this->get_schedules();
		$view            = include_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'views/metabox-sync.php';
		$view( $sync_strategies, $schedules, $webhook_url );
	}

	/**
	 * Get sync strategies
	 */
	protected function get_sync_strategies() {
		return array(
			'add_update_delete' => __( 'Add, Update & Delete', 'air-wp-sync' ),
			'add_update'        => __( 'Add & Update', 'air-wp-sync' ),
			'add'               => __( 'Add', 'air-wp-sync' ),
		);
	}

	/**
	 * Get schedules
	 */
	protected function get_schedules() {
		$schedules = array();

		$wp_schedules = array_filter(
			wp_get_schedules(),
			function ( $schedule ) {
				return isset( $schedule['interval'] ) && $schedule['interval'] >= 1800;
			}
		);

		foreach ( $wp_schedules as $key => $schedule ) {
			$schedules[ $schedule['interval'] ] = array(
				'value'   => $key,
				'label'   => $schedule['display'],
				'enabled' => true,
			);
		}
		ksort( $schedules );
		return array_reverse( $schedules );
	}
}
