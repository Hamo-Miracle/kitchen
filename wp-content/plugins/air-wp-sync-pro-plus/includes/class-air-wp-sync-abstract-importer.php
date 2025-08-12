<?php
/**
 * Abstract Importer.
 * Base class to define custom importer (e.g post, user importer).
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

use DateTime, DateTimeZone, DateInterval;
use Exception, TypeError;
use WP_Error, WP_CLI;

/**
 * Abstract Importer
 */
abstract class Air_WP_Sync_Abstract_Importer {
	/**
	 * Infos
	 *
	 * @var Air_WP_Sync_Importer_Settings
	 */
	public $infos;

	/**
	 * Config
	 *
	 * @var Air_WP_Sync_Importer_Settings
	 */
	public $config;

	/**
	 * Api Client
	 *
	 * @var Air_WP_Sync_Airtable_Api_Client
	 */
	protected $api_client;

	/**
	 * Module
	 *
	 * @var Air_WP_Sync_Abstract_Module
	 */
	protected $module;

	/**
	 * API client class factory.
	 *
	 * @var callable
	 */
	protected $api_client_class_factory;

	/**
	 * Filters class instance.
	 *
	 * @var Air_WP_Sync_Filters
	 */
	protected $filters;

	/**
	 * Constructor
	 *
	 * @param \WP_Post                    $importer_post_object Post object holding the importer config.
	 * @param Air_WP_Sync_Abstract_Module $module Importer Module.
	 * @param callable                    $api_client_class_factory The API client class factory.
	 * @param Air_WP_Sync_Filters         $filters Filters class instance.
	 */
	public function __construct( $importer_post_object, $module, $api_client_class_factory, $filters ) {
		$this->module                   = $module;
		$this->api_client_class_factory = $api_client_class_factory;
		$this->filters                  = $filters;
		$this->load_settings( $importer_post_object );

		add_filter( 'airwpsync/get_importers', array( $this, 'register' ) );
		$this->init();
		$this->schedule_cron_event();
	}

	/**
	 * Register importer
	 *
	 * @param Air_WP_Sync_Abstract_Importer[] $importers Registered importers.
	 *
	 * @return Air_WP_Sync_Abstract_Importer[] $importers Registered importers.
	 */
	public function register( $importers ) {
		$importers[] = $this;
		return $importers;
	}

	/**
	 * Infos getter
	 *
	 * @return Air_WP_Sync_Importer_Settings;
	 */
	public function infos() {
		return $this->infos;
	}

	/**
	 * Config getter
	 *
	 * @return Air_WP_Sync_Importer_Settings
	 */
	public function config() {
		if ( ! $this->config->get( 'enable_link_to_another_record' ) && $this->config->get( 'mapping' ) ) {
			foreach ( $this->config->get( 'mapping' ) as $mapping ) {
				if ( $mapping['airtable'] && strpos( $mapping['airtable'], '__rel__' ) === 0 ) {
					$this->config->set( 'enable_link_to_another_record', 'yes' );
					break;
				}
			}
		}
		return $this->config;
	}

	/**
	 * Scheduled Sync next getter
	 *
	 * @return int|false
	 */
	public function get_next_scheduled_sync() {
		return wp_next_scheduled( $this->get_schedule_slug() );
	}

	/**
	 * Fields getter
	 *
	 * @return array|false|null
	 */
	public function get_airtable_fields() {
		return get_post_meta( $this->infos()->get( 'id' ), 'table_fields', true );
	}

	/**
	 * Run ID getter
	 *
	 * @return string|false|null
	 */
	public function get_run_id() {
		return get_post_meta( $this->infos()->get( 'id' ), 'run', true );
	}

	/**
	 * Cron action
	 *
	 * @return bool|WP_Error
	 */
	public function cron() {
		return $this->run();
	}

	/**
	 * Run importer
	 *
	 * @return bool|WP_Error
	 */
	public function run() {
		if ( $this->get_run_id() ) {
			return new WP_Error( 'air-wp-sync-run-error', __( 'A sync is already running.', 'air-wp-sync' ) );
		}

		$this->reset_errors();

		try {
			// Define a unique id for this run.
			$run_id = uniqid();
			// Save run.
			update_post_meta( $this->infos()->get( 'id' ), 'run', $run_id );

			update_option( 'airwpsync-' . $this->infos()->get( 'id' ) . '-run-' . $run_id . '-start-date', gmdate( 'Y-m-d H:i:s' ), false );

			$this->log( sprintf( 'Starting importer...' ) );

			// Save table schema.
			update_post_meta( $this->infos()->get( 'id' ), 'table_fields', $this->get_table_fields() );
			// Loop through all pages.
			$offset = null;
			do {
				wp_cache_delete( $this->infos()->get( 'id' ), 'post_meta' );
				$offset = $this->get_records( $run_id, $offset );
			} while ( ! empty( $offset ) && $this->get_run_id() );

			return true;
		} catch ( Exception $e ) {
			$this->log( $e->getMessage() );
			$this->end_run( 'error', $e->getMessage() );
			return new WP_Error( 'air-wp-sync-run-error', $e->getMessage() );
		} catch ( TypeError $e ) {
			$this->log( $e->getMessage() );
			$this->end_run( 'error', $e->getMessage() );
			return new WP_Error( 'air-wp-sync-run-error', $e->getMessage() );
		}
	}

	/**
	 * Log message to file and WPCLI output
	 *
	 * @param string $message Message to log.
	 * @param string $level Log level ("log", "error", "warning").
	 */
	public function log( $message, $level = 'log' ) {
		if ( ! is_dir( AIR_WP_SYNC_PRO_LOGDIR ) ) {
			wp_mkdir_p( AIR_WP_SYNC_PRO_LOGDIR );
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		$file = fopen( AIR_WP_SYNC_PRO_LOGDIR . '/' . $this->infos()->get( 'slug' ) . '-' . gmdate( 'Y-m-d' ) . '-' . $this->get_run_id() . '.log', 'a' );
		if ( ! is_string( $message ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
				$message = var_export( $message, true );
			} else {
				$message = 'Airtable_WP_Sync_Importer::log, the $message parameter is not a string, to debug the object turn on WP_DEBUG.';
			}
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
		fwrite( $file, "\n" . gmdate( 'Y-m-d H:i:s' ) . ' ' . $level . ' :: ' . $message );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose( $file );

		if ( class_exists( 'WP_CLI' ) ) {
			$method = method_exists( 'WP_CLI', $level ) ? $level : 'log';
			WP_CLI::$method( $message );
		}
	}

	/**
	 * Returns the log files in ante-chronological order filter by a prefix if defined.
	 *
	 * @param string|false $prefix The prefix to filter the files to return.
	 *
	 * @return string[]
	 */
	public function get_log_files( $prefix = false ) {
		$files = glob( trailingslashit( AIR_WP_SYNC_PRO_LOGDIR ) . '*.log' );
		$files = array_combine( $files, array_map( 'filemtime', $files ) );
		arsort( $files );
		$files = array_keys( $files );
		if ( false !== $prefix ) {
			$files = array_filter(
				$files,
				function ( $file ) use ( $prefix ) {
					return strpos( $file, trailingslashit( AIR_WP_SYNC_PRO_LOGDIR ) . $prefix ) === 0;
				}
			);
			$files = array_values( $files );
		}
		return $files;
	}


	/**
	 * Returns the latest log file URL if any.
	 *
	 * @return string|false
	 */
	public function get_latest_log_file_url() {
		$log_files = $this->get_log_files( $this->infos()->get( 'slug' ) );
		if ( count( $log_files ) === 0 ) {
			return false;
		}
		$latest_log_file = array_shift( $log_files );

		return str_replace( trailingslashit( ABSPATH ), trailingslashit( get_site_url() ), $latest_log_file );
	}

	/**
	 * Reset errors.
	 *
	 * @return void
	 */
	public function reset_errors() {
		delete_post_meta( $this->infos()->get( 'id' ), 'errors' );
	}

	/**
	 * Add an error (string or WP_Error object ) to the importer error list.
	 *
	 * @param string|WP_Error $error Error to add to the importer error list.
	 *
	 * @return void
	 */
	public function add_error( $error ) {
		$errors[] = $error;
		update_post_meta( $this->infos()->get( 'id' ), 'errors', $errors );
	}

	/**
	 * Return errors.
	 *
	 * @return array
	 */
	public function get_errors() {
		$importer_id = $this->infos()->get( 'id' );
		$errors      = get_post_meta( $importer_id, 'errors', true );
		if ( ! is_array( $errors ) ) {
			$errors = array();
		}
		// Backward compatibility.
		$last_error = get_post_meta( $importer_id, 'last_error', true );
		if ( $last_error ) {
			array_unshift( $errors, $last_error );
		}
		return $errors;
	}

	/**
	 * Process an airtable record import.
	 * Return:
	 * - on success an array with the content id created on WordPress as first element and null as second element.
	 * - on failure  an array with null as first element and a WP_Error object as second element.
	 *
	 * @param object $record Airtable record.
	 *
	 * @return array
	 */
	public function process_airtable_record( $record ) {
		$this->log( sprintf( 'Record ID %s', $record->id ) );
		try {
			// Check if we have existing content for this record.
			$content_id           = $this->get_existing_content_id( $record );
			$should_import_record = true;
			if ( $content_id ) {
				$this->log( sprintf( '- Found matching content, ID %s', $content_id ) );
				// Check if we should skip updating it.
				if ( 'add' === $this->config()->get( 'sync_strategy' ) || ! $this->needs_update( $content_id, $record ) ) {
					$should_import_record = false;
					$this->log( sprintf( '- No update needed' ) );
				}
			} else {
				$content_id = null;
			}
			if ( $should_import_record ) {
				$record     = $this->pre_import_record_filter( $record );
				$content_id = $this->import_record( $record, $content_id );
			}
			return array( $content_id, null );
		} catch ( Exception $e ) {
			$this->log( $e->getMessage() );
			return array( false, new WP_Error( 'air-wp-sync-run-error', $e->getMessage() ) );
		} catch ( TypeError $e ) {
			$this->log( $e->getMessage() );
			return array( false, new WP_Error( 'air-wp-sync-run-error', $e->getMessage() ) );
		}
	}

	/**
	 * Checks whether the import started more than two hours ago.
	 * If yes, then refresh record if attachment fields are involved.
	 *
	 * @param  \stdClass $record  Airtable record.
	 */
	public function pre_import_record_filter( $record ) {
		$start_date = get_option( 'airwpsync-' . $this->infos()->get( 'id' ) . '-run-' . $this->get_run_id() . '-start-date' );
		// If we have started the import at least 2 hours ago, attachment URLs could be expired, in that case we should refresh the record.
		if ( Air_WP_Sync_Helper::should_refresh_attachment_urls( $start_date ) ) {
			$attachment_fields = Air_WP_Sync_Helper::get_attachment_fields_id( $this->get_airtable_fields() );
			if ( count( $attachment_fields ) > 0 ) {
				try {
					$base_id  = $this->config()->get( 'app_id' );
					$table_id = $this->config()->get( 'table' );
					$record   = $this->get_api_client()->get_record( $base_id, $table_id, $record->id, array( 'returnFieldsByFieldId' => true ) );
				} catch ( \Throwable $exception ) {
					/* translators: %s = error message */
					$this->log( sprintf( __( 'Could not refresh attachment URLs: %s', 'air-wp-sync' ), $exception->getMessage() ) );
				}
			}
		}
		return $record;
	}


	/**
	 * Return importer's module.
	 *
	 * @return Air_WP_Sync_Abstract_Module
	 */
	public function get_module() {
		return $this->module;
	}

	/**
	 * Delete other content existing in WP but deleted in AT.
	 */
	abstract public function delete_removed_contents();

	/**
	 * End run
	 *
	 * @param string      $status Import status ("success", "cancel" or "error").
	 * @param string|null $error Error message.
	 */
	public function end_run( $status = 'success', $error = null ) {
		global $wpdb;
		$importer_id       = $this->infos()->get( 'id' );
		$run_id            = $this->get_run_id();
		$content_ids       = get_post_meta( $importer_id, 'content_ids', true );
		$count_processed   = is_array( $content_ids ) ? count( $content_ids ) : 0;
		$start_date_string = get_option( sprintf( 'airwpsync-%s-run-%s-start-date', $importer_id, $run_id ) );
		$end_date_string   = gmdate( 'Y-m-d H:i:s' );
		$latest_log_url    = $this->get_latest_log_file_url();

		// Delete any remaining AS actions.
		$action_ids = \ActionScheduler::store()->query_actions(
			array(
				'hook'                  => 'airwpsync_process_records',
				'status'                => \ActionScheduler_Store::STATUS_PENDING,
				'partial_args_matching' => 'like',
				'args'                  => array(
					'importer_id' => $importer_id,
					'run_id'      => $run_id,
				),
				'per_page'              => -1,
			)
		);
		foreach ( $action_ids as $action_id ) {
			\ActionScheduler::store()->cancel_action( $action_id );
		}
		// Delete temporary options.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
				$wpdb->esc_like( sprintf( 'airwpsync-%s-run-%s-', $importer_id, $run_id ) ) . '%'
			)
		);

		$action_ids = \ActionScheduler::store()->query_actions(
			array(
				'hook'                  => 'airwpsync_process_records',
				'status'                => \ActionScheduler_Store::STATUS_FAILED,
				'partial_args_matching' => 'like',
				'args'                  => array(
					'importer_id' => $importer_id,
					'run_id'      => $run_id,
				),
				'per_page'              => -1,
			)
		);
		if ( count( $action_ids ) > 0 ) {
			$this->add_error(
				sprintf(
				// translators: %1$s: start link tag, %2$s end link tag.
					__( 'Some tasks have been interrupted, %1$smore details%2$s', 'air-wp-sync' ),
					'<a href="' . esc_url( admin_url( 'tools.php?page=action-scheduler&status=failed&s=' . $run_id . '&action=-1&action2=-1&paged=1' ) ) . '">',
					'</a>'
				)
			);
		}
		// Remove temporary metas.
		update_post_meta( $importer_id, 'content_ids', null );
		update_post_meta( $importer_id, 'run', null );
		update_post_meta( $importer_id, 'table_fields', null );
		$importer_errors = $this->get_errors();
		if ( $importer_errors && 'success' === $status ) {
			$status = 'error';
		}
		// Update status and error.
		update_post_meta( $importer_id, 'status', $status );
		update_post_meta( $importer_id, 'last_error', $error );
		update_post_meta( $importer_id, 'latest_log_url', $latest_log_url );
		// Save data if success.
		if ( 'success' === $status ) {
			update_post_meta( $importer_id, 'last_updated', gmdate( 'Y-m-d H:i:s' ) );
			update_post_meta( $importer_id, 'count_processed', $count_processed );
			if ( $start_date_string ) {
				$start_datetime = DateTime::createFromFormat( 'Y-m-d H:i:s', $start_date_string );
				$sync_time      = date_diff( new DateTime( 'now' ), $start_datetime, true );
				update_post_meta( $importer_id, 'last_sync_time', $sync_time->format( '%s' ) );
			}
		}
	}

	/**
	 * Get table fields from API.
	 */
	public function get_table_fields() {
		$app_id = $this->config()->get( 'app_id' );
		$data   = $this->get_api_client()->get_tables( $app_id );
		$table  = Air_WP_Sync_Helper::get_table_by_id( $data->tables, $this->config()->get( 'table' ) );

		$fields = $table && $table->fields ? $table->fields : array();

		return apply_filters( 'airwpsync/get_table_fields', $fields, $app_id, $this->get_api_client(), $this->get_import_fields_options() );
	}

	/**
	 * Get AT records from API.
	 *
	 * @param string      $run_id Import run id.
	 * @param string|null $offset Pagination offset.
	 *
	 * @return false
	 */
	protected function get_records( $run_id, $offset = null ) {
		$formula_filter = $this->config()->get( 'formula_filter' );
		if ( $this->config()->get( 'use_filter_ui' ) ) {
			$formula_filter = $this->filters->build_formula(
				$this->config()->get( 'filters' ),
				$this->filters->get_filters_from_fields( $this->get_airtable_fields() )
			);
		}

		$api_list_options = array(
			'offset'                => $offset,
			'view'                  => $this->config()->get( 'view' ),
			'filterByFormula'       => $formula_filter,
			'returnFieldsByFieldId' => true,
		);
		// Remove empty values.
		$api_list_options = array_filter( $api_list_options );
		// Get records.
		$app_id   = $this->config()->get( 'app_id' );
		$table_id = $this->config()->get( 'table' );
		$data     = $this->get_api_client()->list_records( $app_id, $table_id, $api_list_options );
		// Loop through all records.
		$chunks = array_chunk( $data->records, 10 );
		foreach ( $chunks as $chunk ) {
			// Save Airtable record as a temporary option.
			$item_id = uniqid( 'airwpsync-' . $this->infos()->get( 'id' ) . '-run-' . $this->get_run_id() . '-item-' );
			update_option( $item_id, $chunk );
			// Add it to queue.
			as_enqueue_async_action(
				'airwpsync_process_records',
				array(
					'importer_id' => $this->infos()->get( 'id' ),
					'run_id'      => $run_id,
					'item_id'     => $item_id,
				)
			);
		}

		return ! empty( $data->offset ) ? $data->offset : false;
	}

	/**
	 * Get mapped fields
	 *
	 * @param object $record Airtable record.
	 */
	protected function get_mapped_fields( $record ) {
		// Airtable omit keys for empty fields, lets add them with an empty string.
		$mapping       = ! empty( $this->config()->get( 'mapping' ) ) ? $this->config()->get( 'mapping' ) : array();
		$airtable_keys = array_map(
			function ( $mapping_pair ) {
				if ( preg_match( '/(.+)\.(.+)/', $mapping_pair['airtable'], $matches ) ) {
					$airtable_id = $matches[1];
				} else {
					$airtable_id = $mapping_pair['airtable'];
				}
				return $airtable_id;
			},
			$mapping
		);

		$fields = array();
		foreach ( $airtable_keys as $airtable_key ) {
			$fields[ $airtable_key ] = isset( $record->fields->$airtable_key ) ? $record->fields->$airtable_key : '';
		}

		return apply_filters( 'airwpsync/import_record_fields', $fields, $this );
	}

	/**
	 * Load importer settings from post object
	 *
	 * @param \WP_Post $importer_post_object Post object holding the importer config.
	 */
	protected function load_settings( $importer_post_object ) {
		$infos = array(
			'id'       => $importer_post_object->ID,
			'slug'     => $importer_post_object->post_name,
			'title'    => $importer_post_object->post_title,
			'modified' => $importer_post_object->post_modified_gmt,
			'hash'     => wp_hash( $importer_post_object->ID ),
		);

		$this->infos = new Air_WP_Sync_Importer_Settings( $infos );

		$config       = json_decode( $importer_post_object->post_content, true );
		$this->config = new Air_WP_Sync_Importer_Settings( $config );
	}

	/**
	 * Get cron schedule slug.
	 *
	 * @return string
	 */
	protected function get_schedule_slug() {
		return 'air_wp_sync_importer_' . $this->infos()->get( 'id' );
	}

	/**
	 * Init cron events
	 */
	protected function schedule_cron_event() {
		if ( 'cron' === $this->config()->get( 'scheduled_sync.type' ) && $this->config()->get( 'scheduled_sync.recurrence' ) ) {
			add_action( $this->get_schedule_slug(), array( $this, 'cron' ) );
			if ( false === $this->get_next_scheduled_sync() ) {
				wp_schedule_event( $this->get_schedule_timestamp(), $this->config()->get( 'scheduled_sync.recurrence' ), $this->get_schedule_slug() );
			}
		} elseif ( $this->get_next_scheduled_sync() ) {
			wp_clear_scheduled_hook( $this->get_schedule_slug() );
		}
	}

	/**
	 * Get Schedule timestamp
	 */
	protected function get_schedule_timestamp() {
		$datetime   = new DateTime( 'now', new DateTimeZone( wp_timezone_string() ) );
		$recurrence = $this->config()->get( 'scheduled_sync.recurrence' );
		if ( 'weekly' === $recurrence ) {
			if ( $this->config()->get( 'scheduled_sync.weekday' ) ) {
				$datetime->modify( 'next ' . $this->config()->get( 'scheduled_sync.weekday' ) );
			}
		}
		if ( in_array( $recurrence, array( 'weekly', 'daily' ), true ) ) {
			if ( $this->config()->get( 'scheduled_sync.time' ) ) {
				$time = explode( ':', $this->config()->get( 'scheduled_sync.time' ) );
				$datetime->setTime( $time[0], $time[1] );
			}
		} else {
			$schedules = wp_get_schedules();
			$interval  = isset( $schedules[ $recurrence ] ) ? $schedules[ $recurrence ]['interval'] : HOUR_IN_SECONDS;
			$datetime->add( new DateInterval( 'PT' . $interval . 'S' ) );
		}
		return $datetime->getTimestamp();
	}

	/**
	 * Get or instantiate Airtable API client.
	 *
	 * @return Air_WP_Sync_Airtable_Api_Client
	 */
	public function get_api_client() {
		if ( null === $this->api_client ) {
			$api_client_class_factory = $this->api_client_class_factory;
			$this->api_client         = $api_client_class_factory( $this->config()->get( 'api_key' ) );
		}
		return $this->api_client;
	}

	/**
	 * Compare hashes to check if WP object needs update
	 *
	 * @param mixed  $content_id WordPress object id.
	 * @param object $record Airtable record.
	 *
	 * @return bool
	 */
	protected function needs_update( $content_id, $record ) {
		if ( defined( 'AIR_WP_SYNC_PRO_FORCE_UPDATES' ) && AIR_WP_SYNC_PRO_FORCE_UPDATES ) {
			return true;
		}
		return $this->generate_hash( $record ) !== $this->get_existing_content_hash( $content_id );
	}

	/**
	 * Generate hash for given Airtable record
	 *
	 * @param object $record Airtable record.
	 *
	 * @return string
	 */
	protected function generate_hash( $record ) {
		return Air_WP_Sync_Helper::generate_hash( $record, $this->config()->to_array() );
	}

	/**
	 * Returns the import fields options.
	 *
	 * @return array Import field's options
	 */
	public function get_import_fields_options() {
		$options = array(
			'enable_link_to_another_record' => 'yes' === $this->config()->get( 'enable_link_to_another_record' ),
		);

		return $options;
	}

	/**
	 * Init
	 */
	protected function init() {
	}

	/**
	 * Import AT record and return the WordPress object id created / updated.
	 *
	 * @param object     $record Airtable record.
	 * @param mixed|null $existing_object_id WordPress object id.
	 *
	 * @return mixed
	 */
	abstract protected function import_record( $record, $existing_object_id = null );

	/**
	 * Get existing content id
	 *
	 * @param object $record Airtable record.
	 *
	 * @return mixed|false
	 */
	abstract protected function get_existing_content_id( $record );

	/**
	 * Get existing content hash
	 *
	 * @param mixed $content_id WordPress object id.
	 */
	abstract protected function get_existing_content_hash( $content_id );
}
