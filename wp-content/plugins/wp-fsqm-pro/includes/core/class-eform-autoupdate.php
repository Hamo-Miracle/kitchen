<?php
/**
 * Class for e form automatic update.
 *
 * Uses our distribution server with purchase code verification
 *
 * This is a singleton class
 *
 * Make sure you have plugin-update-checker class installed
 *
 * @link {https://github.com/YahnisElsts/plugin-update-checker}
 *
 * To install perform
 *
 * composer require yahnis-elsts/plugin-update-checker
 *
 * @package eForm
 * @subpackage Core\AutoUpdate
 * @author Swashata Ghosh <swashata@wpquark.com>
 * @codeCoverageIgnore
 */
class EForm_AutoUpdate {
	/**
	 * Server URL. Change this to point to your server
	 *
	 * @var        string
	 */
	const SERVER = 'https://dist.wpquark.io';
	/**
	 * Activation endpoint. Do not change this
	 *
	 * @var        string
	 */
	const ACTIVATION_ENDPOINT = 'wpupdate/v1/activate';
	/**
	 * Plugin slug which is registered to the update server
	 *
	 * @var        string
	 */
	const SLUG = 'wp-fsqm-pro';
	/**
	 * WP Options Table key, where token would be stored
	 *
	 * @var        string
	 */
	const TOKEN_OPTIONS = 'eform_update_token';

	/**
	 * WP Options Table key, where status would be stored
	 *
	 * @var        string
	 */
	const STATUS_OPTION = 'eform_update_status';

	/**
	 * Singleton instance variable
	 */
	private static $instance = null;

	/**
	 * Update Server Variable
	 *
	 * @var        Puc_v4_Factory
	 */
	protected $update_server;

	/**
	 * Get the instance of this singleton class
	 *
	 * @return     EForm_AutoUpdate  The instance of the class
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new static();
		}
		return self::$instance;
	}

	private function __construct() {
		global $ipt_fsqm_settings;
		$status = $this->get_activation_status( $ipt_fsqm_settings['purchase_code'] );
		if ( $this->get_token() && is_array( $status ) && true == $status['active'] ) {
			$this->update_server = Puc_v4_Factory::buildUpdateChecker(
				$this->get_update_server_url(),
				IPT_FSQM_Loader::$abs_file,
				self::SLUG
			);
		} else {
			add_action( 'admin_notices', array( $this, 'activation_notice' ) );
		}
	}

	/**
	 * Show activation notice if token is not found
	 */
	public function activation_notice() {
		global $ipt_fsqm_settings;
		// Don't do anything if user has disabled the settings
		if ( true == $ipt_fsqm_settings['disable_un'] ) {
			return;
		}
		// Check for status again
		$status = $this->get_activation_status( $ipt_fsqm_settings['purchase_code'] );

		if ( true == $status['active'] ) {
			return;
		}
		echo '<div class="notice notice-warning is-dismissible"><p>' . __( 'eForm is not activated. To get automatic updates and other features, please activate eForm.', 'ipt_fsqm' );
		echo ' <a href="' . add_query_arg( array(
			'page' => 'ipt_fsqm_settings',
		), admin_url( 'admin.php' ) ) . '" class="button button-primary">' . __( 'Activate eForm', 'ipt_fsqm' ) . '</a>';
		echo '</p></div>';
	}

	/**
	 * Gets the update server url.
	 *
	 * @return     string  The update server url.
	 */
	protected function get_update_server_url() {
		$token_data = $this->get_token();
		$domain = $this->get_domain();
		$query_args = array(
			'wpq_wp_update_token' => $token_data,
			'wpq_wp_update_domain' => $domain,
			'wpq_wp_update_action' => 'get_metadata',
			'wpq_wp_update_slug' => self::SLUG,
		);
		return add_query_arg( $query_args, self::SERVER );
	}

	/**
	 * Get current activation status with flag and message.
	 *
	 * Use this to show activation status within your plugin.
	 *
	 * @param      string  $purchase_code  The purchase code
	 *
	 * @return     array   associative array with 'activated' flag (bool) and 'msg'
	 */
	public function current_activation_status( $purchase_code ) {
		$return = [
			'activated' => false,
			'msg' => '',
		];
		if ( '' == $purchase_code ) {
			$return['msg'] = __( 'Your product eForm is not activated. Please enter your purchase code.', 'ipt_fsqm' );
			return $return;
		}

		$activation_status = $this->get_activation_status( $purchase_code );
		if ( true == $activation_status['active'] ) {
			$return['activated'] = true;
			$return['msg'] = __( 'Your product eForm is activated and receiving automatic updates.', 'ipt_fsqm' );
			return $return;
		}
		$return['msg'] = $activation_status['error'];
		return $return;
	}

	/**
	 * Sets the token from purchase code.
	 *
	 * @param      string   $purchase_code  The purchase code
	 *
	 * @return     boolean  whether successfully stored or not. On success, returns the json output from the server
	 */
	public function set_token_from_code( $purchase_code ) {
		$return = false;
		// If no purchase code, then reset
		if ( '' == $purchase_code ) {
			// Reset activation
			update_option( self::TOKEN_OPTIONS, '' );
		} else {
			$result = wp_remote_post( $this->get_activation_url(), array(
				'body' => array(
					'purchase_code' => $purchase_code,
					'domain' => $this->get_domain(),
				),
			) );
			// Proceed if no wp error
			if ( ! is_wp_error( $result ) ) {
				$json = json_decode( $result['body'], true );
				// If all the json parameters are set, then proceed
				if ( isset( $json['token'] ) && ! empty( $json['token'] ) ) {
					update_option( self::TOKEN_OPTIONS, $json['token'] );
				}
				$return = $json;
			}
		}
		// Flush the activation status
		$this->set_activation_status( $purchase_code );
		return $return;
	}

	/**
	 * Gets the activation status.
	 *
	 * @param      string  $purchase_code  The purchase code
	 *
	 * @return     array   The activation status information
	 */
	public function get_activation_status( $purchase_code ) {
		$status = get_transient( self::STATUS_OPTION );

		if ( $status ) {
			return $status;
		}

		return $this->set_activation_status( $purchase_code );
	}

	/**
	 * Calculates and sets the activation status based on purchase code
	 *
	 * @param      <type>  $purchase_code  The purchase code
	 */
	public function set_activation_status( $purchase_code ) {
		$status = [
			'active' => false,
			'error' => '',
		];
		$result = wp_remote_get( $this->get_activation_url(), array(
			'body' => array(
				'purchase_code' => $purchase_code,
				'domain' => $this->get_domain(),
			),
		) );
		// Check for all kinds of errors
		if ( is_wp_error( $result ) ) {
			// Check for wp error, when connection is not possible
			$status['error'] = __( 'Could not connect to activation server.', 'ipt_fsqm' ) . ' ' . $result->get_error_message();
		} else {
			// Now check for json error
			$json = json_decode( $result['body'], true );
			// If 200 is not the response from server, then purchase code is invalid
			if ( 200 != $result['response']['code'] ) {
				$status['error'] = __( 'Invalid Purchase Code', 'ipt_fsqm' );
			} else {
				// Server responded successfully
				// But could be activated for another domain
				if ( $this->get_domain() == $json['domain'] && $this->get_token() == $json['token'] ) {
					$status['active'] = true;
				} else {
					/** translators: %1$s is replaced by domain code */
					$status['error'] = sprintf( __( 'Your product eForm is activated for another domain <code>%1$s</code>. Saving and activating it here will remove activation from the other domain.', 'ipt_fsqm' ), $json['domain'] );
				}
			}
		}
		// Set the transient
		set_transient( self::STATUS_OPTION, $status, ( 30 * 24 * 60 * 60 ) );
		// Return the status
		return $status;
	}

	/**
	 * Gets the activation url.
	 *
	 * @return     string  The activation url.
	 */
	protected function get_activation_url() {
		return self::SERVER . '/wp-json/' . self::ACTIVATION_ENDPOINT . '/' . self::SLUG;
	}

	/**
	 * Gets the domain of this WordPress installation
	 *
	 * @return     string  The domain.
	 */
	protected function get_domain() {
		return $_SERVER['HTTP_HOST'];
	}

	/**
	 * Gets the token from WP Options Table
	 *
	 * @return     string  The token.
	 */
	protected function get_token() {
		return get_option( self::TOKEN_OPTIONS, '' );
	}
}
