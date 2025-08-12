<?php
/**
 * Rest API abstract route.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

use WP_REST_Server, WP_REST_Request;

/**
 * Class Air_WP_Sync_Api_Abstract_Route
 */
abstract class Air_WP_Sync_Api_Abstract_Route {

	/**
	 * API namespace
	 *
	 * @var string
	 */
	protected $namespace = 'airwpsync/v1';

	/**
	 * Route slug
	 *
	 * @var string
	 */
	protected $route;

	/**
	 * Route methods
	 *
	 * @var string
	 */
	protected $methods = WP_REST_Server::READABLE;

	/**
	 * Set hooks.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_route' ) );

		$this->set_hooks();
	}

	/**
	 * Register menu route.
	 */
	public function register_route() {
		register_rest_route(
			$this->namespace,
			$this->route,
			array(
				'methods'             => $this->methods,
				'callback'            => array( $this, 'run' ),
				'args'                => $this->get_route_args(),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Set hooks
	 */
	protected function set_hooks() {
	}

	/**
	 * Get route arguments.
	 */
	abstract protected function get_route_args();

	/**
	 * Generate and return the actual data.
	 *
	 * @param WP_REST_Request $request Request to process.
	 */
	abstract public function run( WP_REST_Request $request );
}
