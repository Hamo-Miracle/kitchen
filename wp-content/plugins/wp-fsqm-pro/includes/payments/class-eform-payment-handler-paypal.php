<?php
/**
 * eForm PayPal Payment Handlers
 *
 * Provides some shortcuts to handle payments through PayPal system
 *
 * 1. Process Direct Payment ( CC )
 * 2. Process Express Checkout
 * 3. Callback for the redirect URI
 * 4. Error handling
 *
 * This is a singleton class
 *
 * @package eForm - WordPress Form Builder
 * @subpackage Payments\PayPal
 * @author Swashata Ghosh <swashata@wpquark.com>
 */
class EForm_Payment_Handler_PayPal {
	/**
	 * Singleton instance variable
	 */
	private static $instance = null;

	protected $api_context = null;

	/**
	 * Get the instance of this singleton class
	 *
	 * @return     EForm_Payment_Handler_PayPal  The instance of the class
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
	 * Get the API Context Object from PayPal SDK
	 *
	 * @param      string  $client_id      The client identifier
	 * @param      string  $client_secret  The client secret
	 * @param      string  $mode           API Context mode ( sandbox|live )
	 * @param      string  $partner        The partner ID
	 */
	public function set_api_context( $client_id, $client_secret, $mode, $partner = '' ) {
		// Create the API Context
		$api_context = new \PayPal\Rest\ApiContext(
			new \PayPal\Auth\OAuthTokenCredential( $client_id, $client_secret )
		);
		// Set the mode and log
		$log_level = 'WARN';
		if ( 'sandbox' == $mode ) {
			// Sandbox mode, so we can print a little info here
			$log_level = 'INFO';
			// But if WP_DEBUG is enabled, then change it to debug
			if ( defined( 'WP_DEBUG' ) && true == WP_DEBUG ) {
				$log_level = 'DEBUG';
			}
			$api_context->setConfig( array(
				'mode' => 'sandbox',
				'log.LogEnabled' => true,
				'log.FileName' => WP_CONTENT_DIR . '/eForm-PayPal-Sandbox.log',
				'log.LogLevel' => $log_level,
				'cache.enabled' => true,
			) );
		} else {
			// Live mode, so print as little as possible
			$log_enabled = false;
			if ( defined( 'WP_DEBUG' ) && true == WP_DEBUG ) {
				$log_enabled = true;
			}
			$api_context->setConfig( array(
				'mode' => 'live',
				'log.LogEnabled' => $log_enabled,
				'log.FileName' => WP_CONTENT_DIR . '/eForm-PayPal-Live.log',
				'log.LogLevel' => 'WARN',
				'cache.enabled' => true,
			) );
		}

		// Add partner if needed
		if ( '' != $partner ) {
			$api_context->setConfig( array(
				'http.headers.PayPal-Partner-Attribution-Id' => $partner,
			) );
			$api_context->addRequestHeader( 'PayPal-Partner-Attribution-Id', $partner );
		}

		// Set the API Context
		$this->api_context = $api_context;
	}

	/**
	 * Gets the api context.
	 *
	 * @return     \PayPal\Rest\ApiContext  The api context.
	 */
	public function get_api_context() {
		return $this->api_context;
	}

	/**
	 * Get the PayPal Express Checkout URL for given produt
	 *
	 * @param      array   $product        Associative array containing product
	 *                                     data with the following parameters:
	 *                                         name         Product name
	 *                                         sku          Product sku
	 *                                         invoiceid    Product invoice id
	 *                                         description  Product description
	 * @param      string  $currency       Currency code
	 * @param      float   $total_amount   Total sale amount
	 * @param      string  $trackback_url  eForm trackback URL with which
	 *                                     payment and cancel URL will be
	 *                                     populated
	 *
	 * @return     array   Associative array with success status and redirect_url
	 *
	 * @codeCoverageIgnore
	 */
	public function get_express_checkout_url( $product, $currency, $total_amount, $trackback_url ) {
		// Initiate the return status
		$payment_status = array(
			'success' => false,
			'redirect_url' => false,
		);

		// Create a new Payer
		$payer = $this->get_payer();
		// Set payment method
		$payer->setPaymentMethod( 'paypal' );

		// Create our transaction
		$transaction = $this->get_transaction( $product, $currency, $total_amount );

		// Redirect URLs
		$redirect_url = new \PayPal\Api\RedirectUrls();
		$redirect_url->setReturnUrl( add_query_arg( array(
			'psuccess' => 'true',
			'action' => 'payment',
			'mode' => 'paypal_e',
		), $trackback_url ) )
		->setCancelUrl( add_query_arg( array(
			'psuccess' => 'false',
			'action' => 'payment',
			'mode' => 'paypal_e',
		), $trackback_url ) );

		// Create our Payment Object
		$payment = $this->get_payment( $payer, $transaction, 'sale' );
		// Set the URL
		$payment->setRedirectUrls( $redirect_url );

		// Execute
		try {
			$payment->create( $this->api_context );
			// Get redirect URL
			$payment_status['redirect_url'] = $payment->getApprovalLink();
			$payment_status['success'] = true;
		} catch ( Exception $e ) {
			$payment_status['success'] = false;
		}

		// Return
		return $payment_status;
	}

	/**
	 * Execute express checkout and return the result
	 *
	 * @param      \PayPal\Rest\ApiContext  $api_context   The api context from
	 *                                                     PayPal SDK
	 * @param      array                    $payment_data  Payment data from the
	 *                                                     database
	 *
	 * @return     boolean|array            false if execution was not successful, object if successfully executed
	 *
	 * @codeCoverageIgnore
	 */
	public function execute_express_checkout( $payment_data, $payment_id, $payer_id ) {
		// Let's try to get the payment
		try {
			$payment = \PayPal\Api\Payment::get( $payment_id, $this->api_context );
		} catch ( Exception $e ) {
			return false;
		}

		// Now that we have the payment, let's try to execute
		$execution = new \PayPal\Api\PaymentExecution();
		$execution->setPayerId( $payer_id );

		// We need to set the transaction and amount
		$transaction = new \PayPal\Api\Transaction();
		$amount = new \PayPal\Api\Amount();

		$amount->setCurrency( $payment_data->currency )
			->setTotal( $payment_data->amount );

		$transaction->setAmount( $amount );

		$execution->addTransaction( $transaction );

		// Let's execute
		try {
			$result = $payment->execute( $execution, $this->api_context );
			return $result;
		} catch ( Exception $e ) {
			return false;
		}
		// Couldn't do stuff, so return false
		return false;
	}

	/**
	 * Call the PayPal REST API for direct Payment through creditcard
	 *
	 * @param      array   $product        Associative array containing product
	 *                                     data with the following parameters:
	 *                                         name         Product name
	 *                                         sku          Product sku
	 *                                         invoiceid    Product invoice id
	 *                                         description  Product description
	 * @param      string   $currency      Currency code
	 * @param      float    $total_amount  Total sale amount
	 * @param      array    $cc            Credit Card information
	 *
	 * @return     boolean  REST Response if succeeds, false if fails
	 *
	 * @codeCoverageIgnore
	 */
	public function direct_payment( $product, $currency, $total_amount, $cc ) {
		// Create Address
		$address = new \PayPal\Api\Address();
		$address->setLine1( $cc['address'] )
		->setCountryCode( $cc['country']['alpha2'] )
		->setPostalCode( $cc['zip'] );

		// Create CC
		$card = new \PayPal\Api\CreditCard();
		$card->setType( $cc['type'] )
		->setNumber( $cc['number'] )
		->setExpireMonth( $cc['em'] )
		->setExpireYear( $cc['ey'] )
		->setCvv2( $cc['cvv'] )
		->setFirstName( $cc['fname'] )
		->setLastName( $cc['lname'] )
		->setBillingAddress( $address );

		// Create funding instrument
		$fi = new \PayPal\Api\FundingInstrument();
		$fi->setCreditCard( $card );

		// Create payer
		$payer = $this->get_payer();
		// Set method and instrument
		$payer->setPaymentMethod( 'credit_card' )
		->setFundingInstruments( array( $fi ) );

		// Get transaction
		$transaction = $this->get_transaction( $product, $currency, $total_amount );

		// Create payment
		$payment = $this->get_payment( $payer, $transaction );

		// Do it
		try {
			$return = $payment->create( $this->api_context );
			return $return;
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Get the transaction object from PayPal SDK
	 *
	 * @param      array   $product        Associative array containing product
	 *                                     data with the following parameters:
	 *                                         name         Product name
	 *                                         sku          Product sku
	 *                                         invoiceid    Product invoice id
	 *                                         description  Product description
	 * @param      string                   $currency      Currency code
	 * @param      float                    $total_amount  Total sale amount
	 *
	 * @return     \PayPal\Api\Transaction  The transaction object from PayPal API SDK
	 */
	public function get_transaction( $product, $currency, $total_amount ) {
		// Normalize the $product
		$product = wp_parse_args( $product, array(
			'name' => '',
			'sku' => '',
			'invoiceid' => '',
			'description' => '',
		) );

		// Item Information
		$item = new \PayPal\Api\Item();

		$item->setName( $product['name'] )
		->setCurrency( $currency )
		->setQuantity( 1 )
		->setSku( $product['sku'] )
		->setPrice( $total_amount );

		// Item List
		$item_list = new \PayPal\Api\ItemList();
		$item_list->setItems( array( $item ) );

		// Set the final amount
		$amount = new \PayPal\Api\Amount();

		$amount->setCurrency( $currency )
		->setTotal( $total_amount );

		// Transaction for this sale
		$transaction = new \PayPal\Api\Transaction();

		$transaction->setAmount( $amount )
		->setItemList( $item_list )
		->setDescription( $product['description'] )
		->setInvoiceNumber( get_bloginfo( 'name' ) . '-' . $product['invoiceid'] );

		return $transaction;
	}

	/**
	 * Get the Payment API from PayPal SDK
	 *
	 * @param      \PayPal\Api\Payer        $payer        The payer object
	 * @param      \PayPal\Api\Transaction  $transaction  The transaction object
	 * @param      string                   $intent       Payment intent,
	 *                                                    defaults to sale
	 *
	 * @return     \PayPal\Api\Payment      The payment object from PayPal SDK
	 */
	public function get_payment( $payer, $transaction, $intent = 'sale' ) {
		$payment = new \PayPal\Api\Payment();

		$payment->setIntent( $intent )
		->setPayer( $payer )
		->setTransactions( array( $transaction ) );

		return $payment;
	}

	/**
	 * Get a Payer object from PayPal SDK
	 *
	 * @return     \PayPal\Api\Payer     The payer object
	 */
	public function get_payer() {
		return new \PayPal\Api\Payer();
	}
}
