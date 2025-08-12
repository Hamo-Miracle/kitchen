<?php
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
// Define the log file
if ( defined( 'WP_DEBUG' ) && true == WP_DEBUG && ! defined( 'AUTHORIZENET_LOG_FILE' ) ) {
	define( 'AUTHORIZENET_LOG_FILE', WP_CONTENT_DIR . '/eForm-AuthorizeNet.log' );
}
/**
 * eForm Authorize.net Payment Handlers
 *
 * Provides some shortcuts to handle payments through Authorize.net system
 *
 * 1. Process Direct Payment ( CC )
 *
 * This is a singleton class
 *
 * @package eForm - WordPress Form Builder
 * @subpackage Payments\AuthorizeNet
 * @author Swashata Ghosh <swashata@wpquark.com>
 */
class EForm_Payment_Handler_AuthorizeNet {
	/**
	 * Singleton instance variable
	 */
	private static $instance = null;

	protected $api_context = null;

	/**
	 * Get the instance of this singleton class
	 *
	 * @return     EForm_Payment_Handler_AuthorizeNet  The instance of the class
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
	private function __construct() {

	}

	/**
	 * Execute Direct Payment
	 *
	 * @param      array  $product   The product
	 * @param      string  $currency  The currency
	 * @param      float  $amount    The amount
	 * @param      array  $cc        Credit Card Data
	 *
	 * @codeCoverageIgnore
	 */
	public function direct_payment( $product, $currency, $amount, $cc, $mode ) {
		// Get the Credit Card
		$credit_card = $this->get_credit_card( $cc['number'], $cc['ey'] . '-' . $cc['em'], $cc['cvv'] );
		// Payment
		$payment = new AnetAPI\PaymentType();
		$payment->setCreditCard( $credit_card );

		// Order Info
		$order = $this->get_order_type( $product['invoiceid'] );

		// Line Item
		$lineitem = $this->get_line_item( $product, $amount );

		// Bill To
		$name = array(
			'fname' => $cc['fname'],
			'lname' => $cc['lname'],
		);
		$address = array(
			'line' => $cc['address'],
			'zip' => $cc['zip'],
			'country' => $cc['country']['alpha3'],
		);
		$billto = $this->get_bill_to( $name, $address );

		// Transaction
		$txn = $this->get_transaction_request_type( $amount, $currency );
		$txn->setPayment( $payment );
		$txn->setOrder( $order );
		$txn->addToLineItems( $lineitem );
		$txn->setBillTo( $billto );

		// Request
		$request = new AnetAPI\CreateTransactionRequest();
		$request->setMerchantAuthentication( $this->get_api_context() );
		$request->setRefId( $this->get_ref_id() );
		$request->setTransactionRequest( $txn );
		$controller = new AnetController\CreateTransactionController( $request );
		$api_mode = 'sandbox' == $mode ? \net\authorize\api\constants\ANetEnvironment::SANDBOX : \net\authorize\api\constants\ANetEnvironment::PRODUCTION;

		try {
			return $controller->executeWithApiResponse( $api_mode );
		} catch ( Exception $e ) {
			if ( defined( 'WP_DEBUG' ) && true == WP_DEBUG ) {
				error_log( $e->getMessage() );
			}
			return false;
		}
	}

	/**
	 * Sets the api context.
	 *
	 * @param      string  $login_id         The login identifier
	 * @param      string  $transaction_key  The transaction key
	 */
	public function set_api_context( $login_id, $transaction_key ) {
		$api_context = new AnetAPI\MerchantAuthenticationType();
		$api_context->setName( $login_id );
		$api_context->setTransactionKey( $transaction_key );
		$this->api_context = $api_context;
	}

	/**
	 * Gets the api context.
	 *
	 * @return     AnetAPI\MerchantAuthenticationType  The api context.
	 */
	public function get_api_context() {
		return $this->api_context;
	}

	/**
	 * Gets the credit card.
	 *
	 * @param      string   $number      The number
	 * @param      string   $expiration  The expiration
	 * @param      string   $code        The code
	 *
	 * @return     AnetAPI\CreditCardType  The credit card.
	 */
	public function get_credit_card( $number, $expiration, $code ) {
		$credit_card = new AnetAPI\CreditCardType();
		$credit_card->setCardNumber( $number );
		$credit_card->setExpirationDate( $expiration );
		$credit_card->setCardCode( $code );
		return $credit_card;
	}

	/**
	 * Gets the order type.
	 *
	 * @param      string   $invoice  The invoice
	 *
	 * @return     AnetAPI\OrderType  The order type.
	 */
	public function get_order_type( $invoice ) {
		$order = new AnetAPI\OrderType();
		$order->setInvoiceNumber( $invoice );
		return $order;
	}

	/**
	 * Gets the line item.
	 *
	 * @param      array   $product  The product
	 * @param      float   $amount   The amount
	 *
	 * @return     AnetAPI\LineItemType  The line item.
	 */
	public function get_line_item( $product, $amount ) {
		$lineitem = new AnetAPI\LineItemType();
		$lineitem->setItemId( $product['sku'] );
		$lineitem->setName( $product['name'] );
		$lineitem->setDescription( $product['description'] );
		$lineitem->setQuantity( '1' );
		$lineitem->setUnitPrice( $amount );
		$lineitem->setTaxable( 'N' );
		return $lineitem;
	}

	/**
	 * Gets the bill to.
	 *
	 * @param      array   $name     The name
	 * @param      array   $address  The address
	 *
	 * @return     AnetAPI\CustomerAddressType  The bill to.
	 */
	public function get_bill_to( $name, $address ) {
		$billto = new AnetAPI\CustomerAddressType();
		$billto->setFirstName( $name['fname'] );
		$billto->setLastName( $name['lname'] );
		$billto->setAddress( $address['line'] );
		$billto->setZip( $address['zip'] );
		$billto->setCountry( $address['country'] );
		return $billto;
	}

	/**
	 * Gets the transaction request type.
	 *
	 * @return     AnetAPI\TransactionRequestType  The transaction request type.
	 */
	public function get_transaction_request_type( $amount, $currency ) {
		$txn = new AnetAPI\TransactionRequestType();
		$txn->setTransactionType( 'authCaptureTransaction' );
		$txn->setAmount( $amount );
		$txn->setCurrencyCode( $currency );
		return $txn;
	}

	/**
	 * Gets the reference identifier.
	 *
	 * @return     string  The reference identifier.
	 */
	public function get_ref_id() {
		// A reference ID for this transaction
		return uniqid( 'ear-' );
	}
}
