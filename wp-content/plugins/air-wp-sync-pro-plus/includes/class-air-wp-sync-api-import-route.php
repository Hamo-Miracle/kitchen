<?php
/**
 * Rest API import route.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

use WP_REST_Request, WP_REST_Response;
use Exception;
use WP_Error;

/**
 * Class Air_WP_Sync_Api_Import_Route
 */
class Air_WP_Sync_Api_Import_Route extends Air_WP_Sync_Api_Abstract_Route {

	/**
	 * Route slug.
	 *
	 * @var string
	 */
	protected $route = 'import/(?P<importer_hash>[^/]+)';

	/**
	 * Route methods.
	 *
	 * @var string
	 */
	protected $methods = array( 'GET', 'POST' );

	/**
	 * Set hooks
	 */
	protected function set_hooks() {
	}

	/**
	 * Get route arguments.
	 *
	 * @return array
	 */
	protected function get_route_args() {
		return array(
			'importer_hash' => array(
				'required'          => true,
				'validate_callback' => array( $this, 'validate_importer_hash' ),
			),
		);
	}

	/**
	 * Validate importer parameter.
	 *
	 * @param mixed            $value Hash value.
	 * @param \WP_REST_Request $request Request.
	 * @param string           $param Param key.
	 *
	 * @return true|\WP_Error
	 */
	public function validate_importer_hash( $value, $request, $param ) {
		return false !== $this->get_importer_by_hash( $value );
	}


	/**
	 * Get importer instance from hash
	 *
	 * @param string $hash Hash value.
	 */
	protected function get_importer_by_hash( $hash ) {
		return array_reduce(
			Air_WP_Sync_Helper::get_importers(),
			function ( $result, $importer ) use ( $hash ) {
				return $importer->infos()->get( 'hash' ) === $hash ? $importer : $result;
			},
			false
		);
	}

	/**
	 * Run importer
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @throws Exception No connection found.
	 * @return WP_Error|WP_REST_Response
	 */
	public function run( WP_REST_Request $request ) {
		try {
			$importer_hash = $request->get_param( 'importer_hash' );
			$importer      = $this->get_importer_by_hash( $importer_hash );

			if ( ! $importer ) {
				throw new Exception( 'No connection found.' );
			}

			// Cancel any ongoing run.
			$importer->end_run( 'cancel' );
			// Run a new import.
			$result = $importer->run();

			if ( is_wp_error( $result ) ) {
				throw new Exception( $result->get_error_message() );
			}

			return new WP_REST_Response(
				array(
					'success' => true,
				)
			);
		} catch ( Exception $e ) {
			return new WP_Error( 'error', $e->getMessage() );
		}
	}
}
