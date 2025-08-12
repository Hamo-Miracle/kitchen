<?php
/**
 * Licensing.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

use WP_Error;

/**
 * Class Air_WP_Sync_Licensing
 */
class Air_WP_Sync_Licensing {
	/**
	 * API Server URL
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * EDD Product ID
	 *
	 * @var int
	 */
	protected $product_id;

	/**
	 * Constructor
	 *
	 * @param string $url API Server URL.
	 * @param int    $product_id EDD Product ID.
	 */
	public function __construct( $url, $product_id ) {
		$this->url        = $url;
		$this->product_id = $product_id;
	}

	/**
	 * Activate a license.
	 *
	 * @param string $license License key.
	 *
	 * @return string|WP_Error
	 */
	public function activate( $license = '' ) {
		$args = array(
			'timeout' => 25,
			'body'    => array(
				'edd_action'  => 'activate_license',
				'license'     => $license,
				'item_id'     => $this->product_id,
				'url'         => str_ireplace( array( 'http://', 'https://' ), '', home_url() ),
				'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
			),
		);

		$response = wp_remote_post( $this->url, apply_filters( 'airwpsync/licensing/request_args', $args ) );

		if ( is_wp_error( $response ) ) {
			$error = $response->get_error_message();
		} elseif ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$error = sprintf(
			/* translators: the HTTP response code */
				__( 'WP connect server returned an HTTP error, code: %s', 'air-wp-sync' ),
				wp_remote_retrieve_response_code( $response )
			);
		}

		if ( ! isset( $error ) ) {
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			if ( true === $license_data->success ) {
				return $license_data->license;
			}
			$error = $this->get_license_error_message( $license_data->error, $license_data->expires );
		}

		return new WP_Error( 'license_activation_api_error', $error );
	}

	/**
	 * Deactivate a license.
	 *
	 * @param string $license License key.
	 *
	 * @return string|WP_Error
	 */
	public function deactivate( $license = '' ) {
		$args = array(
			'timeout' => 25,
			'body'    => array(
				'edd_action'  => 'deactivate_license',
				'license'     => $license,
				'item_id'     => $this->product_id,
				'url'         => str_ireplace( array( 'http://', 'https://' ), '', home_url() ),
				'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
			),
		);

		$response = wp_remote_post( $this->url, apply_filters( 'airwpsync/licensing/request_args', $args ) );

		if ( is_wp_error( $response ) ) {
			$error = $response->get_error_message();
		} elseif ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$error = sprintf(
			/* translators: the HTTP response code */
				__( 'WP connect server returned an HTTP error, code: %s', 'air-wp-sync' ),
				wp_remote_retrieve_response_code( $response )
			);
		}

		if ( isset( $error ) ) {
			return new WP_Error( 'license_deactivation_api_error', $error );
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		return $license_data->license;
	}

	/**
	 * Check licence status information
	 *
	 * @param string $license License key.
	 *
	 * @return  string|WP_Error
	 */
	public function check_license( $license = '' ) {
		$args = array(
			'timeout' => 25,
			'body'    => array(
				'edd_action'  => 'check_license',
				'license'     => $license,
				'item_id'     => $this->product_id,
				'url'         => str_ireplace( array( 'http://', 'https://' ), '', home_url() ),
				'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
			),
		);

		$response = wp_remote_get( $this->url, apply_filters( 'airwpsync/licensing/request_args', $args ) );

		if ( is_wp_error( $response ) ) {
			$error = $response->get_error_message();
		} elseif ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$error = sprintf(
			/* translators: the HTTP response code */
				__( 'WP connect server returned an HTTP error, code: %s', 'air-wp-sync' ),
				wp_remote_retrieve_response_code( $response )
			);
		}

		if ( ! isset( $error ) ) {
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			if ( 'valid' === $license_data->license ) {
				return $license_data->license;
			}
			$error = $this->get_license_error_message( $license_data->license, $license_data->expires );
		}
		return new WP_Error( 'invalid_license', $error );
	}

	/**
	 * Check licence status information
	 *
	 * @see https://easydigitaldownloads.com/docs/software-licensing-api/#check_license
	 *
	 * @param string $license_status License status.
	 * @param string $expires Expiry date (e.g. "2020-06-30 23:59:59").
	 *
	 * @return  string
	 */
	protected function get_license_error_message( $license_status = '', $expires = '' ) {
		switch ( $license_status ) {
			case 'expired':
				$error = sprintf(
				/* translators: the license key expiration date */
					__( 'Your license key expired on %s.', 'air-wp-sync' ),
					// phpcs:ignore: WordPress.DateTime.CurrentTimeTimestamp.Requested
					date_i18n( get_option( 'date_format' ), strtotime( $expires, current_time( 'timestamp' ) ) )
				);
				break;
			case 'disabled':
			case 'revoked':
				$error = __( 'Your license key has been disabled.', 'air-wp-sync' );
				break;
			case 'invalid':
			case 'invalid_item_id':
			case 'missing':
				$error = __( 'Your license is invalid.', 'air-wp-sync' );
				break;
			case 'inactive':
			case 'site_inactive':
				$error = __( 'Your license is not active for this URL.', 'air-wp-sync' );
				break;
			case 'no_activations_left':
				$error = __( 'Your license key has reached its activation limit.', 'air-wp-sync' );
				break;
			default:
				$error = __( 'An error occurred, please try again.', 'air-wp-sync' );
				break;
		}
		return $error;
	}
}
