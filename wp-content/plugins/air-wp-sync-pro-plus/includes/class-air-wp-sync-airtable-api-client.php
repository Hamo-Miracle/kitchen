<?php
/**
 * Airtable Api Client.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

use Exception;

/**
 * Air_WP_Sync_Airtable_Api_Client class
 */
class Air_WP_Sync_Airtable_Api_Client {
	/**
	 * API Endpoint
	 *
	 * @var string
	 */
	protected $endpoint = 'https://api.airtable.com/v0';

	/**
	 * Authentication Token
	 *
	 * @var string
	 */
	protected $token;

	/**
	 * Fetched records
	 *
	 * @var array
	 */
	protected $records;


	/**
	 * Whether to skip cache
	 *
	 * @var bool
	 */
	protected $skip_cache = false;


	/**
	 * Table cache duration
	 *
	 * @var int
	 */
	protected $cache_duration = 15;

	/**
	 * Constructor
	 *
	 * @param string $token Airtable API token.
	 */
	public function __construct( $token ) {
		$options              = Air_WP_Sync_Services::get_instance()->get( 'options' );
		$this->token          = $token;
		$this->skip_cache     = defined( 'AIR_WP_SYNC_PRO_SKIP_API_CACHE' ) && AIR_WP_SYNC_PRO_SKIP_API_CACHE;
		$this->cache_duration = ! empty( $options->get( 'cache_duration' ) ) ? (int) $options->get( 'cache_duration' ) : 15;
	}

	/**
	 * List bases
	 *
	 * @param array $options Request query args (@see https://airtable.com/developers/web/api/list-bases#query).
	 *
	 * @throws Exception See make_api_request.
	 * @return object
	 */
	public function list_bases( $options = array() ) {
		return $this->make_api_request( '/meta/bases', $options );
	}

	/**
	 * Get tables from Airtable API or from cache if available and if the param $use_cache us true.
	 *
	 * @param string $base_id Base id.
	 * @param bool   $use_cache Use cache.
	 *
	 * @throws Exception See make_api_request.
	 * @return array
	 */
	public function get_tables( $base_id, $use_cache = true ) {
		$tables         = array();
		$transient_name = sprintf( 'airwpsync_tables_%s', $base_id );
		if ( $this->skip_cache ) {
			$use_cache = false;
		}
		if ( $use_cache ) {
			$tables = get_transient( $transient_name );
		}
		if ( empty( $tables ) ) {
			$tables = $this->make_api_request( "/meta/bases/$base_id/tables" );
			set_transient( $transient_name, $tables, (int) $this->cache_duration * MINUTE_IN_SECONDS );
		}
		return $tables;
	}

	/**
	 * List records
	 *
	 * @param string $base_id Base id.
	 * @param string $table_id Table id.
	 * @param array  $options Request query args (@see https://airtable.com/developers/web/api/list-records#query).
	 *
	 * @throws Exception See make_api_request.
	 * @return array|object
	 */
	public function list_records( $base_id, $table_id, $options = array() ) {
		return $this->make_api_request( "/$base_id/$table_id", $options );
	}

	/**
	 * Get record from Airtable API or if available from cache.
	 *
	 * @param string $base_id Base id.
	 * @param string $table_id Table id.
	 * @param string $record_id Record id.
	 * @param array  $options Request query args (@see https://airtable.com/developers/web/api/list-records#query).
	 *
	 * @return object
	 * @throws Exception See make_api_request.
	 */
	public function get_record( $base_id, $table_id, $record_id, $options = array() ) {
		if ( isset( $this->records[ $base_id ][ $table_id ][ $record_id ] ) ) {
			$record = $this->records[ $base_id ][ $table_id ][ $record_id ];
		} else {
			$record = $this->make_api_request( "/$base_id/$table_id/$record_id", $options );
			if ( ! empty( $record ) ) {
				$this->records[ $base_id ][ $table_id ][ $record_id ] = $record;
			}
		}
		return $record;
	}

	/**
	 * Create records
	 *
	 * @param string $base_id Base id.
	 * @param string $table_id Table id.
	 * @param array  $records A list of records (@see https://airtable.com/developers/web/api/create-records#request-records).
	 *
	 * @return array
	 * @throws Exception See make_api_request.
	 */
	public function create_records( $base_id, $table_id, $records ) {
		return $this->make_api_request( "/$base_id/$table_id", array( 'records' => $records ), 'POST' );
	}

	/**
	 * Performs API request
	 *
	 * @param string $url Airtable endpoint.
	 * @param array  $data Query args for GET request or posted data for POST request.
	 * @param string $type Request type (GET and POST are supported).
	 *
	 * @return object|array
	 * @throws Exception Cannot encode body in JSON.
	 * @throws Exception See validate_response.
	 */
	protected function make_api_request( $url, $data = array(), $type = 'GET' ) {
		$url = $this->endpoint . $url;

		if ( 'POST' === $type ) {
			$data = wp_json_encode( $data );
			if ( false === $data ) {
				throw new Exception( 'Cannot encode body in JSON' );
			}
		}
		$args     = $this->get_request_args( array( 'body' => $data ) );
		$response = 'POST' === $type ? wp_remote_post( $url, $args ) : wp_remote_get( $url, $args );
		return $this->validate_response( $response );
	}

	/**
	 * Build request args
	 *
	 * @param array $args wp_remote_post / wp_remote_get args.
	 *
	 * @return array
	 */
	protected function get_request_args( $args = array() ) {
		return array_merge(
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->token,
					'Content-Type'  => 'application/json',
				),
				'timeout' => 15,
			),
			$args
		);
	}

	/**
	 * Validate HTTP Response and returns data
	 *
	 * @param array|\WP_Error $response The response from wp_remote_post / wp_remote_get.
	 *
	 * @return object|array
	 * @throws Exception Request error.
	 * @throws Exception Airtable API error.
	 * @throws Exception Airtable API: Could not decode JSON response.
	 */
	protected function validate_response( $response ) {
		if ( is_wp_error( $response ) ) {
			throw new Exception( esc_html( sprintf( 'Airtable API: %s', $response->get_error_message() ) ) );
		}
		// Check HTTP code.
		$reponse_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $reponse_code ) {
			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body );
			if ( ! empty( $data->error ) ) {
				throw new Exception( esc_html( sprintf( 'Airtable API: %s', $this->get_error_message( $data ) ) ) );
			}
			throw new Exception( esc_html( sprintf( 'Airtable API: Received HTTP Error, code %s', $reponse_code ) ) );
		}
		// Get JSON data from request body.
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body );
		$data = Air_WP_Sync_Helper::maybe_convert_emoji( $data, 'options', 'option_value' );
		if ( is_null( $data ) ) {
			throw new Exception( 'Airtable API: Could not decode JSON response' );
		}
		return $data;
	}

	/**
	 * Get error message from Airtable response
	 *
	 * @param mixed $data Response body from Airtable request.
	 *
	 * @return mixed|string
	 */
	protected function get_error_message( $data ) {
		if ( ! empty( $data->error->message ) ) {
			return $data->error->message;
		}
		if ( ! empty( $data->error->type ) ) {
			return $data->error->type;
		}
		if ( is_string( $data->error ) ) {
			return $data->error;
		}
		return 'No error message';
	}
}
