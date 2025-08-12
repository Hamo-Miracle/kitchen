<?php
/**
 * Display connection state: status, last error, last updated date time, next sync.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

use Exception;

/**
 * Class Air_WP_Sync_Metabox_Import_Infos
 */
class Air_WP_Sync_Metabox_Import_Infos {
	/**
	 * Constructor
	 */
	public function __construct() {

		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'wp_ajax_air_wp_sync_trigger_update', array( $this, 'trigger_update' ) );
		add_action( 'wp_ajax_air_wp_sync_get_progress', array( $this, 'get_progress' ) );
		add_action( 'wp_ajax_air_wp_sync_cancel_import', array( $this, 'cancel_import' ) );
	}

	/**
	 * Add metabox
	 */
	public function add_meta_box() {
		add_meta_box(
			'airwpsync-import-infos',
			__( 'Actions', 'air-wp-sync' ),
			array( $this, 'display' ),
			'airwpsync-connection',
			'side'
		);
	}

	/**
	 * Output metabox HTML
	 *
	 * @param \WP_Post $post The connection.
	 */
	public function display( $post ) {
		$importer            = Air_WP_Sync_Helper::get_importer_by_id( $post->ID );
		$importer_id         = $importer ? $importer->infos()->get( 'id' ) : 0;
		$importer_is_running = $importer && $importer->get_run_id();
		$view                = include_once AIR_WP_SYNC_PRO_PLUGIN_DIR . 'views/metabox-import-infos.php';
		$view( $importer, $importer_id, $importer_is_running, $this );
	}

	/**
	 * Manual sync AJAX function
	 *
	 * @throws \Exception No connection found.
	 * @throws \Exception Error from importer.
	 */
	public function trigger_update() {
		// Nonce check.
		check_ajax_referer( 'air-wp-sync-trigger-update', 'nonce' );

		$importer_id = (int) $_POST['importer'] ?? 0;

		try {
			$importer = Air_WP_Sync_Helper::get_importer_by_id( $importer_id );
			if ( ! $importer ) {
				throw new Exception( 'No connection found.' );
			}
			// Get Airtable records and add theme to queue.
			$result = $importer->run();
			if ( is_wp_error( $result ) ) {
				throw new Exception( $result->get_error_message() );
			}

			// Unlock Action Scheduler, force queue to start now.
			delete_option( 'action_scheduler_lock_async-request-runner' );

			wp_send_json_success(
				array(
					'feedback' => __( 'In progress...', 'air-wp-sync' ),
				)
			);
		} catch ( Exception $e ) {
			wp_send_json_error(
				array(
					'infosHtml' => $this->get_stats_html( $importer_id, $e->getMessage() ),
					'feedback'  => __( 'Finished with errors.', 'air-wp-sync' ),
				)
			);
		}
	}

	/**
	 * Get sync progress
	 *
	 * @throws \Exception No connection found.
	 */
	public function get_progress() {
		// Nonce check.
		check_ajax_referer( 'air-wp-sync-trigger-update', 'nonce' );

		$importer_id = (int) $_POST['importer'] ?? 0;

		try {
			$importer = Air_WP_Sync_Helper::get_importer_by_id( $importer_id );
			if ( ! $importer ) {
				throw new Exception( 'No connection found.' );
			}

			if ( $importer->get_run_id() ) {
				$actions_remaining = as_get_scheduled_actions(
					array(
						'hook'                  => 'airwpsync_process_records',
						'partial_args_matching' => 'like',
						'status'                => array( \ActionScheduler_Store::STATUS_RUNNING, \ActionScheduler_Store::STATUS_PENDING ),
						'args'                  => array(
							'importer_id' => $importer_id,
							'run_id'      => $importer->get_run_id(),
						),
						'per_page'              => -1,
					)
				);

				$all_actions = as_get_scheduled_actions(
					array(
						'hook'                  => 'airwpsync_process_records',
						'partial_args_matching' => 'like',
						'status'                => array( \ActionScheduler_Store::STATUS_RUNNING, \ActionScheduler_Store::STATUS_PENDING, \ActionScheduler_Store::STATUS_COMPLETE, \ActionScheduler_Store::STATUS_CANCELED, \ActionScheduler_Store::STATUS_FAILED ),
						'args'                  => array(
							'importer_id' => $importer_id,
							'run_id'      => $importer->get_run_id(),
						),
						'per_page'              => -1,
					)
				);

				if ( count( $actions_remaining ) === 0 ) {
					$importer->delete_removed_contents();
					$importer->end_run( 'success' );
				}
			}

			if ( ! $importer->get_run_id() ) {
				wp_send_json_success(
					array(
						'infosHtml' => $this->get_stats_html( $importer_id ),
						'feedback'  => __( 'Finished!', 'air-wp-sync' ),
					)
				);
			} else {
				$finished_actions = count( $all_actions ) - count( $actions_remaining );
				$progress_percent = $finished_actions / count( $all_actions );
				$progress         = number_format( $progress_percent * 100 ) . '%';

				wp_send_json_success(
					array(
						/* translators: %s = progress percentage */
						'feedback' => sprintf( __( 'In progress... %s', 'air-wp-sync' ), $progress ),
					)
				);
			}
		} catch ( Exception $e ) {
			wp_send_json_error(
				array(
					'infosHtml' => $this->get_stats_html( $importer_id, $e->getMessage() ),
					'feedback'  => __( 'Finished with errors.', 'air-wp-sync' ),
				)
			);
		}
	}

	/**
	 * Cancel import
	 *
	 * @throws Exception No connection found.
	 */
	public function cancel_import() {
		// Nonce check.
		check_ajax_referer( 'air-wp-sync-trigger-update', 'nonce' );

		$importer_id = (int) $_POST['importer'] ?? 0;

		try {
			$importer = Air_WP_Sync_Helper::get_importer_by_id( $importer_id );
			if ( ! $importer ) {
				throw new Exception( 'No connection found.' );
			}

			$importer->end_run( 'cancel' );

			wp_send_json_success(
				array(
					'infosHtml' => $this->get_stats_html( $importer_id ),
					'feedback'  => __( 'Canceled.', 'air-wp-sync' ),
				)
			);
		} catch ( Exception $e ) {
			wp_send_json_error(
				array(
					'infosHtml' => $this->get_stats_html( $importer_id, $e->getMessage() ),
					'feedback'  => __( 'Could not cancel import.', 'air-wp-sync' ),
				)
			);
		}
	}

	/**
	 * Get importer statistics html
	 *
	 * @param int         $importer_id Importer id.
	 * @param null|string $forced_error Error to be displayed.
	 *
	 * @return false|string
	 */
	protected function get_stats_html( $importer_id, $forced_error = null ) {
		ob_start();
		$status          = $forced_error ? 'error' : ( $importer_id ? get_post_meta( $importer_id, 'status', true ) : '' );
		$last_updated    = $importer_id ? get_post_meta( $importer_id, 'last_updated', true ) : '';
		$next_sync       = $importer_id ? wp_next_scheduled( 'air_wp_sync_importer_' . $importer_id ) : '';
		$content_ids     = get_post_meta( $importer_id, 'content_ids', true );
		$count_deleted   = get_post_meta( $importer_id, 'count_deleted', true );
		$count_processed = get_post_meta( $importer_id, 'count_processed', true );
		$latest_log_url  = get_post_meta( $importer_id, 'latest_log_url', true );

		$importer = Air_WP_Sync_Helper::get_importer_by_id( $importer_id );
		$errors   = array();
		if ( $forced_error ) {
			$errors[] = $forced_error;
		} elseif ( $importer ) {
			$errors = $importer->get_errors();
		}

		if ( ! empty( $errors ) ) {
			$status = 'error';
		}

		$status_class = '';
		if ( 'success' === $status ) {
			$status_class = 'dashicons-before dashicons-yes-alt';
		} elseif ( 'error' === $status ) {
			$status_class = 'dashicons-before dashicons-dismiss';
		} elseif ( 'cancel' === $status ) {
			$status_class = 'dashicons-before dashicons-warning';
		}

		$args = array(
			'status'          => $status,
			'errors'          => $errors,
			'last_updated'    => $last_updated,
			'next_sync'       => $next_sync,
			'status_class'    => $status_class,
			'content_ids'     => $content_ids,
			'count_processed' => $count_processed,
			'count_deleted'   => $count_deleted,
			'latest_log_url'  => $latest_log_url,
		);

		$view = include AIR_WP_SYNC_PRO_PLUGIN_DIR . 'views/metabox-side/infos.php';
		$view( $args );
		return ob_get_clean();
	}
}
