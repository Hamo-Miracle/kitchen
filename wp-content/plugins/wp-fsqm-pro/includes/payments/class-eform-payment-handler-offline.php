<?php
/**
 * Class for handling Offline Payment Gateway internally
 *
 * It interacts with the database directly since no external service is needed.
 * It provides methods for initiating payments, sending reminders and marking
 * payments completed or cancelled.
 *
 * @package eForm
 * @subpackage payments
 * @author Swashata Ghosh <swashata@wpquark.com>
 */
class EForm_Payment_Handler_Offline {
	/**
	 * Singleton instance variable
	 */
	private static $instance = null;

	/**
	 * Get the instance of this singleton class
	 *
	 * @return     EForm_Payment_Handler_Offline  The instance of the class
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new static();
		}
		return self::$instance;
	}

	/**
	 * Constructor made private so that instance has to be called to get an instance
	 *
	 * We may change the implementation in future. This makes sure our code is
	 * future proof
	 */
	private function __construct() {}

	/**
	 * Initialize an offline payment
	 *
	 * @param      float    $amount        The amount of
	 * @param      integer  $invoiceid     The invoice ID (from database)
	 * @param      array    $product_info  Additional product information
	 *
	 * @return     boolean  True if updated, false if DB error
	 */
	public function initiate_payment( $amount, $invoiceid, $product_info ) {
		global $wpdb, $ipt_fsqm_info;
		// Check for integrity
		$present_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$ipt_fsqm_info['payment_table']} WHERE id=%d AND mode=%s", $invoiceid, 'offline' ) ); // WPCS: Unprepared SQL ok
		if ( ! $present_id ) {
			return false;
		}
		// Create unique transaction ID
		$txn = 'EFM-OFL-' . bin2hex( openssl_random_pseudo_bytes( 8 ) );
		// Prepare the data
		$data = [
			'product' => $product_info,
			'amount' => $amount,
		];
		$update = [
			'txn' => $txn,
			'status' => 4, // 4 means awaiting
			'meta' => maybe_serialize( $data ),
		];
		$status = $wpdb->update( $ipt_fsqm_info['payment_table'], $update, [
			'id' => $invoiceid,
			'mode' => 'offline',
		], [ '%s', '%d', '%s' ], [ '%d', '%s' ] );
		if ( false === $status ) {
			return false;
		}
		return true;
	}

	/**
	 * Sets the payment status of an already initiated payment.
	 *
	 * @param      int      $id      The invoice ID (from database)
	 * @param      string   $status  Status which we need to set, paid or
	 *                               cancelled
	 *
	 * @return     boolean  True if successful, false if db error or not an offline payment
	 */
	public function set_payment_status( $id, $status ) {
		global $wpdb, $ipt_fsqm_info;
		$payment_db = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_info['payment_table']} WHERE id=%d", $id ) ); // WPCS: unprepared SQL ok
		if ( ! $payment_db ) {
			return false;
		}
		// Don't do anything if not offline payment
		if ( 'offline' != $payment_db->mode ) {
			return false;
		}
		$data = new IPT_FSQM_Form_Elements_Data( $payment_db->data_id );
		if ( is_null( $data->data_id ) ) {
			return false;
		}
		switch ( $status ) {
			case 'paid':
				$this->set_payment_paid( $payment_db, $data );
				break;
			case 'cancelled':
				$this->set_payment_cancelled( $payment_db, $data );
				break;
			default:
				return false;
		}
		return true;
	}

	/**
	 * Set the payment status to paid and processed hooks like sending email
	 * and updating data object.
	 *
	 * @param      Object                       $payment_db  The payment
	 *                                                       database object
	 * @param      IPT_FSQM_Form_Elements_Data  $data        The data object
	 */
	protected function set_payment_paid( $payment_db, $data ) {
		global $wpdb, $ipt_fsqm_info;
		// Update payment tables
		$wpdb->update( $ipt_fsqm_info['payment_table'], [
			'status' => 1,
		], [
			'id' => $payment_db->id,
		], '%d', '%d' );
		// Set the paid status to 1
		$data->set_paid_status( 1 );
		// Send User Email if it was locked
		if ( true == $data->settings['payment']['sub_on_success'] ) {
			$data->send_user_notification_email();
		} else {
			// Just send the payment confirmation
			$data->send_payment_email( $this->get_payment_status( $payment_db, 'paid' ), false, false, 'offline', false );
		}
	}

	/**
	 * Set the payment status to cancelled and processed hooks like sending email
	 * and updating data object.
	 *
	 * @param      Object                       $payment_db  The payment
	 *                                                       database object
	 * @param      IPT_FSQM_Form_Elements_Data  $data        The data object
	 */
	protected function set_payment_cancelled( $payment_db, $data ) {
		// Set the paid status to 2
		global $wpdb, $ipt_fsqm_info;
		// Update payment tables
		$wpdb->update( $ipt_fsqm_info['payment_table'], [
			'status' => 2,
		], [
			'id' => $payment_db->id,
		], '%d', '%d' );
		// Set paid status to 0
		$data->set_paid_status( 0 );
		// Send User Payment Email
		$data->send_payment_email( $this->get_payment_status( $payment_db, 'cancelled' ), false, true, 'offline', false );
	}

	/**
	 * Gets the payment status array depending on database and needing status.
	 *
	 * @param      Object  $payment_db  The payment database Object
	 * @param      string  $status      Needed status, paid or cancelled
	 *
	 * @return     array   The payment status.
	 */
	protected function get_payment_status( $payment_db, $status ) {
		return [
			'needed' => true,
			'success' => ( 'paid' == $status ? true : false ),
			'invoice' => $payment_db->id,
		];
	}
}
