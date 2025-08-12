<?php
/**
 * eForm Stripe Payment Handlers
 *
 * Provides some shortcuts to handle payments through Stripe system
 *
 * 1. Gets the token and processes the payment
 *
 * This is a singleton class
 *
 * @package eForm - WordPress Form Builder
 * @subpackage Payments\Stripe
 * @author Swashata Ghosh <swashata@wpquark.com>
 */
class EForm_Payment_Handler_Stripe {
	/**
	 * Singleton instance variable
	 *
	 * @var        EForm_Payment_Handler_Stripe
	 */
	private static $instance = null;

	/**
	 * Stripe Secret API Key
	 *
	 * @var        string
	 */
	protected $api_key = null;

	/**
	 * User metakey for storing and retreving stripe customer id
	 *
	 * @var        string
	 */
	const CUST_META_KEY = 'eform_stripe_custid';

	/**
	 * Get the instance of this singleton class
	 *
	 * @return     EForm_Payment_Handler_Stripe  The instance of the class
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
	 * Sets the stripe secret api key.
	 *
	 * @param      string  $api_key  The stripe secret API key
	 */
	public function set_api_key( $api_key ) {
		$this->api_key = $api_key;
		$this->set_stripe_api();
	}

	/**
	 * Gets the api key.
	 *
	 * @return     string  The api key.
	 */
	public function get_api_key() {
		return $this->api_key;
	}

	/**
	 * Create a payment intent for SCA flow.
	 *
	 * @param float  $amount Amount for which to create the intent.
	 * @param string $currency Currency code.
	 * @param bool   $subscription Whether or not this intent is for subscription.
	 * @param string $subscription_source Whether or not to use existing payment_method for the intent.
	 *
	 * @return \Stripe\PaymentIntent
	 */
	public function create_intent( $amount, $currency, $subscription = false, $subscription_source = 'new' ) {
		$config = [
			'amount' => $amount,
			'currency' => $currency,
			'payment_method_types' => [ 'card' ],
			'capture_method' => 'manual',
		];
		if ( $subscription ) {
			$config['setup_future_usage'] = 'off_session';
			if ( $subscription_source !== 'new' ) {
				$config['payment_method'] = $subscription_source;
			}
		}
		$intent = \Stripe\PaymentIntent::create( $config );
		return $intent;
	}

	/**
	 * Verify a Stripe Charge through payment intent and returns the output.
	 * On error it returns false.
	 *
	 * @param string $token     The token.
	 * @param array  $product   The product.
	 * @param string $email     The email.
	 * @param string $currency  The currency.
	 * @param float  $amount    The amount.
	 *
	 * @return     boolean|\Stripe\Charge  Returns the Charge object on success, false on failure
	 */
	public function verify_intent( $client_data, $product, $email, $currency, $amount ) {
		// bail early if there's no paymentIntent Id in client data
		if ( ! $client_data
			|| ! isset( $client_data['paymentIntent'] )
			|| ! isset( $client_data['paymentIntent']['id'] )
		) {
			return false;
		}
		try {
			// first get list of charges made by the payment intent
			$charges = \Stripe\Charge::all( [
				'payment_intent' => $client_data['paymentIntent']['id'],
				'limit' => 1,
			] );
			// get the final charge
			$charge = $charges->data[0];
			// make sure the amount and currency matches
			$amount_in_charge = floatval($charge->amount);
			$amount_original = floatval($amount);
			if ( abs( $amount_in_charge - $amount_original ) >= 0.00001 ) {
				ipt_error_log( 'Charge mismatch', $amount_in_charge, $amount_original );
				return false;
			}
			if ( strtolower( $currency ) !== strtolower( $charge->currency ) ) {
				ipt_error_log( 'Currency mismatch', $currency, $charge->currency );
				return false;
			}

			// All good, capture the intent
			$intent = \Stripe\PaymentIntent::retrieve(
				$client_data['paymentIntent']['id']
			);
			$intent->capture();

			// update it with product metadata
			$update_charge_data = [
				'description' => $product['description'],
				'metadata' => $product['metadata'],
			];
			if ( is_email( $email ) && ! empty( $email ) ) {
				$update_charge_data['receipt_email'] = $email;
			}

			\Stripe\Charge::update( $charge->id, $update_charge_data );

			// update payment intent
			\Stripe\PaymentIntent::update(
				$client_data['paymentIntent']['id'],
				[
					'description' => $product['description'],
					'metadata' => $product['metadata'],
				]
			);

			// return the updated one
			return \Stripe\Charge::retrieve( $charge->id );

		} catch ( Exception $e ) {
			ipt_error_log( $e );
			return false;
		}
	}

	/**
	 * Get stripe customer data for the provided WordPress user.
	 *
	 * If customer data is already present, then retrieve otherwise returns an
	 * empty array.
	 *
	 * @param      int    $user_id  The WordPress user id
	 *
	 * @return     array  The customer data, empty if not found.
	 */
	public function get_customer_data( $user_id ) {
		// See if customer ID is already present
		$stripe_cust_id = $this->get_stripe_customer_id( $user_id );
		// If customer is not present, then just return empty array
		if ( ! $stripe_cust_id ) {
			return [];
		}
		// Customer id is present, so query Stripe
		$customer_data = $this->get_stripe_customer_data( $stripe_cust_id );
		if ( false === $customer_data ) {
			return [];
		}
		// Stripe has returned, so normalize the stuff
		$return_data = [
			'id' => $customer_data->id,
			'balance' => $customer_data->account_balance,
			'default_source' => $customer_data->default_source,
			'sources' => [],
		];
		// Populate the sources
		foreach ( $customer_data->sources->data as $source ) {
			$return_data['sources'][] = $source;
		}
		// Return
		return $return_data;
	}

	/**
	 * Creates customer if not exists. While creating the customer (if
	 * necessary) add a payment source, through the token and add a metadata to
	 * refer to the customer ID.
	 *
	 * @param      int     $user_id        WordPress user id.
	 * @param      int     $submission_id  eForm submission id.
	 * @param      string  $token          Stripe source token obtained from
	 *                                     stripe.js/elements in eForm format.
	 *
	 * @return     string  Returns the customer id.
	 */
	public function create_customer( $user_id, $submission_id = null, $token = null ) {
		$stripe_cust_id = $this->get_stripe_customer_id( $user_id );
		// If customer id is already present, then don't do anything
		if ( $stripe_cust_id ) {
			// But do add the new source, if provided
			if ( ! is_null( $token ) ) {
				$this->update_stripe_customer_sources( $stripe_cust_id, $token );
			}
			return $stripe_cust_id;
		}
		// Other create a customer in stripe and return the customer id
		return $this->create_stripe_customer( $user_id, $submission_id, $token );
	}

	/**
	 * Add a new source to an existing customer. If customer isn't present, then
	 * it will return false, otherwise will return the status of the source
	 * addition.
	 *
	 * @param      int      $user_id  WordPress user Id.
	 * @param      string   $token    Stripe source token obtained from
	 *                                stripe.js/elements in eForm format.
	 *
	 * @return     boolean  True if customer exists and source was added, false otherwise
	 */
	public function update_customer_sources( $user_id, $token ) {
		$stripe_cust_id = $this->get_stripe_customer_id( $user_id );
		// If customer is present, then only attempt to update, else fail
		if ( $stripe_cust_id ) {
			return $this->update_stripe_customer_sources( $stripe_cust_id, $token );
		}
		return false;
	}

	/**
	 * Update customer default source, so that we can subscribe safely.
	 *
	 * @param      int      $user_id  WordPress user Id.
	 * @param      string   $source   The stripe source Id attached to the
	 *                                customer. This is just the ID and not the
	 *                                token provided by eForm.
	 *
	 * @return     boolean  True on success, false otherwise
	 */
	public function update_customer_default_source( $user_id, $source ) {
		$stripe_cust_id = $this->get_stripe_customer_id( $user_id );
		// If customer is present, then only attempt to update, else fail
		if ( $stripe_cust_id ) {
			return $this->update_stripe_customer_default_source( $stripe_cust_id, $source );
		}
		return false;
	}

	/**
	 * Delete the stripe customer account associated with WordPress user id
	 *
	 * @param      int      $user_id  WordPress user id
	 *
	 * @return     boolean  true if customer is deleted, false otherwise
	 */
	public function delete_customer( $user_id ) {
		$stripe_cust_id = $this->get_stripe_customer_id( $user_id );
		// If customer id is already present, then do the stuff
		if ( $stripe_cust_id ) {
			return $this->delete_stripe_customer( $stripe_cust_id );
		}
		return false;
	}

	/**
	 * Verify Subscription Status.
	 *
	 * @param string $subscription_id Subscription ID.
	 *
	 * @return bool True if subscription is active, false otherwise.
	 */
	public function verify_subscription( $subscription_id ) {
		try {
			$subscription = \Stripe\Subscription::retrieve( $subscription_id );
			if ( 'active' === $subscription->status ) {
				return true;
			}
			return false;
		} catch ( Exception $e ) {
			ipt_error_log( $e->getMessage() );
			return false;
		}
	}

	/**
	 * Creates a subscription for a WordPress User
	 *
	 * @param int    $user_id  WordPress User Id.
	 * @param string $plan     Stripe Plan (already created).
	 *
	 * @return     boolean|\Stripe\Subscription  Will return a Stripe object on success, false otherwise
	 */
	public function create_subscription( $user_id, $plan ) {
		$stripe_cust_id = $this->get_stripe_customer_id( $user_id );
		// If customer id is already present, then do the stuff
		if ( $stripe_cust_id ) {
			return $this->create_stripe_subscription( $stripe_cust_id, $plan );
		}
		return false;
	}

	/**
	 * Delete a subscription from Stripe.
	 *
	 * @param      string   $sub_id  Subscription Id.
	 *
	 * @return     boolean  true on success, false otherwise
	 */
	public function delete_subscription( $sub_id ) {
		return $this->delete_stripe_subscription( $sub_id );
	}

	/**
	 * Creates a Stripe Product for storing different plans
	 *
	 * @param      array           $product_config  The product configuration
	 *
	 * @return     boolean|object  false on failure, Stripe Product Object on success
	 */
	public function create_product( $product_config ) {
		$product_config = wp_parse_args( $product_config, [
			'id' => '',
			'name' => 'eForm',
			'type' => 'service',
			'statement_descriptor' => 'EFORM-MNTHLY',
		] );
		try {
			return \Stripe\Product::create( $product_config );
		} catch ( Exception $e ) {
			ipt_error_log( $e->getMessage() );
			return false;
		}
	}

	/**
	 * Retrieves a Stripe Product by ID.
	 *
	 * @param      string          $product_id  The product identifier
	 *
	 * @return     boolean|object  false on failure, Stripe Product Object on success.
	 */
	public function get_product( $product_id ) {
		try {
			return \Stripe\Product::retrieve( $product_id );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Deletes a Stripe Product by ID
	 *
	 * @param      string   $product_id  The product identifier
	 *
	 * @return     boolean  true on success, false on failure
	 */
	public function delete_product( $product_id ) {
		try {
			$product = \Stripe\Product::retrieve( $product_id );
			$product->delete();
			return true;
		} catch ( Exception $e ) {
			ipt_error_log( $e->getMessage() );
			return false;
		}
	}

	/**
	 * Creates a Stripe Plan for subscription
	 *
	 * @param      array           $plan_config  Associative array of required
	 *                                           plan configuration
	 *
	 * @return     boolean|object  false on failure, Stripe Plan object on success
	 */
	public function create_plan( $plan_config ) {
		$plan_config = wp_parse_args( $plan_config, [
			'id' => '',
			'amount' => 0,
			'currency' => 'usd',
			'interval' => 'month',
			'nickname' => 'eForm Stripe Plan',
			'interval_count' => 1,
			'product' => null,
		] );
		try {
			return \Stripe\Plan::create( $plan_config );
		} catch ( Exception $e ) {
			ipt_error_log( $e->getMessage() );
			return false;
		}
	}

	/**
	 * Gets a Stripe Plan by the Plan ID.
	 *
	 * @param      string          $plan_id  The plan identifier
	 *
	 * @return     boolean|object  false on failure, Stripe\Plan Object on success.
	 */
	public function get_plan( $plan_id ) {
		try {
			return \Stripe\Plan::retrieve( $plan_id );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Deletes a Stripe Plan by ID
	 *
	 * @param      string   $plan_id  Srripe plan Id.
	 *
	 * @return     boolean  true on success, false otherwise
	 */
	public function delete_plan( $plan_id ) {
		try {
			$plan = \Stripe\Plan::retrieve( $plan_id );
			$plan->delete();
			return true;
		} catch ( Exception $e ) {
			ipt_error_log( $e->getMessage() );
			return false;
		}
	}

	/**
	 * Creates a stripe subscription.
	 *
	 * @param string $cust_id  The customer identifier.
	 * @param string $plan     The plan Id.
	 *
	 * @return     boolean|\Stripe\Subscription  False on failure Stripe Subscription on success.
	 */
	private function create_stripe_subscription( $cust_id, $plan ) {
		try {
			return \Stripe\Subscription::create( [
				'customer' => $cust_id,
				'items' => [
					[
						'plan' => $plan,
					],
				],
				// expand payment intent so that it can be processed further
				'expand' => [ 'latest_invoice.payment_intent' ],
			] );
		} catch ( Exception $e ) {
			ipt_error_log( $e->getMessage() );
			return false;
		}
	}

	/**
	 * Cancels a subscription
	 *
	 * @param      string   $sub_id  The subscription Id.
	 *
	 * @return     boolean  true on success, false otherwise
	 */
	private function delete_stripe_subscription( $sub_id ) {
		try {
			$sub = \Stripe\Subscription::retrieve( $sub_id );
			$sub->cancel();
			return true;
		} catch ( Exception $e ) {
			ipt_error_log( $e->getMessage() );
			return false;
		}
	}

	/**
	 * Update the default source of a stripe customer
	 *
	 * @param      int|object  $cust    The stripe customer Id or the customer
	 *                                  object.
	 * @param      string      $source  The source Id. This is not in eForm
	 *                                  format, and is just the ID of the token
	 *                                  or previous source.
	 *
	 * @return     boolean     True on success, false otherwise
	 */
	private function update_stripe_customer_default_source( $cust, $source ) {
		try {
			$cu = is_object( $cust ) ? $cust : $this->get_stripe_customer_data( $cust );
			$cu->default_source = $source;
			$cu->save();
			return true;
		} catch( Exception $e ) {
			ipt_error_log( $e->getMessage() );
			return false;
		}
	}

	/**
	 * Update Stripe customer source by customer id
	 *
	 * @param      string   $cust_id  Stripe customer id
	 * @param      string   $token    Stripe source token in eForm Format
	 *
	 * @return     boolean  true if succeeds, false otherwise
	 */
	private function update_stripe_customer_sources( $cust_id, $token ) {
		// Check the token
		$source = $this->get_token_data( $token );
		if ( false === $source ) {
			return false;
		}
		// Add the card and return true if succeeds
		try {
			$cu = $this->get_stripe_customer_data( $cust_id );
			$sources = $cu->sources->create( [
				'source' => $source,
			] );
			return $this->update_stripe_customer_default_source( $cu, $sources->id );
		} catch ( Exception $e ) {
			ipt_error_log( $e->getMessage() );
			return false;
		}
	}

	/**
	 * Delete a Stripe customer account by customer id
	 *
	 * @param      string   $cust_id  Stripe customer id.
	 *
	 * @return     boolean  true if deleted, false otherwise
	 */
	private function delete_stripe_customer( $cust_id ) {
		// Delete the customer and return true if succeeds
		try {
			$cu = $this->get_stripe_customer_data( $cust_id );
			if ( is_object( $cu ) ) {
				$cu->delete();
				return true;
			}
			return false;
		} catch ( Exception $e ) {
			ipt_error_log( $e->getMessage() );
			return false;
		}
	}

	/**
	 * Create a Stripe Customer based on WordPress User Id. Takes into account
	 * the submission id and stripe token.
	 *
	 * @param      int             $user_id        WordPress User Id.
	 * @param      int             $submission_id  eForm submission id.
	 * @param      string          $token          Stripe source token obtained
	 *                                             from stripe.js/element, in
	 *                                             eForm format.
	 *
	 * @throws     LogicException  When the provided user_id is invalid
	 *
	 * @return     boolean|string  customer id on success, false on failure
	 */
	private function create_stripe_customer( $user_id, $submission_id = null, $token = null ) {
		// Get the needed customer metadata
		$userdata = get_userdata( $user_id );
		if ( false === $userdata ) {
			throw new LogicException( 'Invalid User ID. WordPress User does not exists.', 99 );
		}
		$metadata = [
			'first_name' => $userdata->first_name,
			'last_name' => $userdata->last_name,
			'username' => $userdata->user_login,
			'user_id' => $user_id,
		];
		// Attach the submission id if not null
		if ( ! is_null( $submission_id ) ) {
			$metadata['submission_id'] = $submission_id;
		}
		$email = $userdata->user_email;
		/* translators: %1$d is the user id of the customer, %2$s is the user_login (username) */
		$description = sprintf( __( 'eForm Customer for: %2$s (%1$d)', 'ipt_fsqm' ), $user_id, $userdata->user_login );
		// Create
		$customer_data = [
			'email' => $email,
			'description' => $description,
			'metadata' => $metadata,
		];
		// Attach the source too if passed
		if ( ! is_null( $token ) ) {
			$source = $this->get_token_data( $token );
			if ( false !== $source ) {
				$customer_data['source'] = $source;
			}
		}
		try {
			// Create the customer
			$stripe_customer = \Stripe\Customer::create( $customer_data );
			// Get the customer id and add to the usermetadata
			$cust_id = $stripe_customer->id;
			$this->set_stripe_customer_id( $user_id, $cust_id );
			return $cust_id;
		} catch ( Exception $e ) {
			ipt_error_log( $e->getMessage() );
			return false;
		}
	}

	/**
	 * Retrieve stripe customer data for the provided customer id.
	 *
	 * It will return false if customer id or stripe request throws an error.
	 *
	 * @param      string          $cust_id  The customer identifier
	 *
	 * @return     boolean|object  false if fails, customer object if succeeds.
	 */
	private function get_stripe_customer_data( $cust_id ) {
		// Try to retrieve
		try {
			return \Stripe\Customer::retrieve( $cust_id );
		} catch ( Exception $e ) {
			ipt_error_log( $e->getMessage() );
			return false;
		}
	}

	/**
	 * Get customer ID from metadata for a given WordPress User ID.
	 *
	 * This doesn't create a customer if not present.
	 *
	 * @param      int  $user_id  The user identifier
	 *
	 * @return     string|boolean  The customer identifier, false if not present
	 */
	private function get_stripe_customer_id( $user_id ) {
		return get_user_meta( $user_id, self::CUST_META_KEY . '_for_' . md5( $this->api_key ), true );
	}

	/**
	 * Set customer ID on metadata for a given WordPress User Id and corresponding
	 * stripe Customer Id.
	 *
	 * This takes into account the API key for which the customer has been
	 * generated.
	 *
	 * @param int $user_id The WordPress Id of the user.
	 * @param int $cust_id Customer Id from Stripe.
	 * @return mixed true on update, meta Id if the key didn't exist, false on failure.
	 */
	private function set_stripe_customer_id( $user_id, $cust_id ) {
		return update_user_meta( $user_id, self::CUST_META_KEY . '_for_' . md5( $this->api_key ), $cust_id );
	}

	/**
	 * Sets the API key for the Stripe Object
	 */
	private function set_stripe_api() {
		\Stripe\Stripe::setApiKey( $this->api_key );
	}

	/**
	 * Get Stripe Token ID from eForm provided token data
	 *
	 * @param      array           $token  eForm provided token information
	 *
	 * @return     boolean|string  false if not a valid token information, id of the token otherwise
	 */
	public function get_token_data( $token ) {
		if ( ! isset( $token['token'] ) || isset( $token['error'] ) ) {
			return false;
		}
		return $token['token']['id'];
	}

}
