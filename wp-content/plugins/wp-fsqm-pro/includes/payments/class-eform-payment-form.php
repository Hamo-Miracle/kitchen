<?php
/**
 * eForm Payment Form Handler
 *
 * Provides a use-anywhere solution to provide with Payment Form with proper
 * providers
 *
 *
 * @package    eForm - WordPress Form Builder
 * @subpackage Payments\Form
 * @author     Swashata Ghosh <swashata@wpquark.com>
 */
class EForm_Payment_Form {
	/**
	 * Form ID variable
	 * @var        int
	 */
	protected $form_id;

	/**
	 * Name Prefix for the form
	 *
	 * @var        string
	 */
	protected $name_prefix;
	/**
	 * The UI Variable
	 * @var        IPT_Plugin_UIF_Base
	 */
	protected $ui;

	/**
	 * Payment Element Data
	 * @var        array
	 */
	protected $data;

	/**
	 * Payment Element Key
	 *
	 * @var        array
	 */
	protected $key;

	/**
	 * Payment related settings
	 *
	 * @var        array
	 */
	protected $settings;

	/**
	 * Constructor
	 *
	 * @param      int                   $form_id       Form id
	 * @param      string                $name_prefix   Form element name prefix
	 * @param      IPT_Plugin_UIF_Front  $ui            UI object
	 * @param      array                 $element_data  Element configuration
	 * @param      int                   $element_key   Element key, used for
	 *                                                  creating HTML names
	 * @param      array                 $settings      Payment settings
	 */
	public function __construct( $form_id, $name_prefix, $ui, $element_data, $element_key, $settings ) {
		// Set the variables
		$this->form_id = $form_id;
		$this->name_prefix = $name_prefix;
		$this->ui = $ui;
		$this->data = $element_data;
		$this->key = $element_key;
		$this->settings = $settings;
	}

	/**
	 * Show the subscription or checkout form according to the settings
	 */
	public function show_form() {
		if ( 'recurring' == $this->settings['payment_type'] ) {
			$this->subscription_form();
		} else {
			$this->checkout_form();
		}
	}

	/**
	 * Show the single checkout payment form
	 */
	public function checkout_form() {
		$payment_types = array();
		$payment_selections = $this->get_payment_gateways();

		if ( true == $this->settings['paypal']['enabled'] && '' != $this->settings['paypal']['d_settings']['client_id'] && true == $this->settings['paypal']['allow_direct'] ) {
			$payment_types[] = array(
				'value' => 'paypal_d',
				'label' => $this->settings['paypal']['label_paypal_d'],
				'data' => array(
					'condid' => 'ipt_fsqm_form_' . $this->form_id . '_' . $this->key . '_payment_cc',
				),
			);
			$payment_selections['paypal_d'] = true;
		}
		if ( true == $this->settings['paypal']['enabled'] && '' != $this->settings['paypal']['d_settings']['client_id'] ) {
			$payment_types[] = array(
				'value' => 'paypal_e',
				'label' => $this->settings['paypal']['label_paypal_e'],
				'data' => array(
					'condid' => 'ipt_fsqm_form_' . $this->form_id . '_' . $this->key . '_payment_pp',
				),
			);
			$payment_selections['paypal_e'] = true;
		}
		if ( true == $this->settings['stripe']['enabled'] ) {
			$payment_types[] = array(
				'value' => 'stripe',
				'label' => $this->settings['stripe']['label_stripe'],
				'data' => array(
					'condid' => 'ipt_fsqm_form_' . $this->form_id . '_' . $this->key . '_payment_stripe_wrap',
				),
			);
			$payment_selections['stripe'] = true;
		}
		if ( true == $this->settings['authorizenet']['enabled'] ) {
			$payment_types[] = array(
				'value' => 'authorizenet',
				'label' => $this->settings['authorizenet']['label'],
				'data' => array(
					'condid' => 'ipt_fsqm_form_' . $this->form_id . '_' . $this->key . '_payment_cc',
				),
			);
			$payment_selections['authorizenet'] = true;
		}
		if ( true == $this->settings['offline']['enabled'] ) {
			$payment_types[] = [
				'value' => 'offline',
				'label' => $this->settings['offline']['label'],
				'data' => [
					'condid' => 'ipt_fsqm_form_' . $this->form_id . '_' . $this->key . '_payment_ofl',
				],
			];
			$payment_selections['offline'] = true;
		}

		if ( empty( $payment_types ) ) {
			$this->ui->msg_error( __( 'Please enable atleast one payment gateway from CONFIG > Payment.', 'ipt_fsqm' ), true, __( 'No active payment gateway', 'ipt_fsqm' ) );
			return;
		}

		$this->print_gateway_radios( $payment_types );

		$this->ui->column_head( '', 'full', false, array( 'eform-checkout-gateways' ) );
		if ( true == $payment_selections['paypal_d'] || true == $payment_selections['authorizenet'] ) {
			$this->cc_direct_ui();
		}

		if ( true == $payment_selections['paypal_e'] ) {
			$this->paypal_express_ui();
		}

		if ( true == $payment_selections['stripe'] ) {
			$this->stripe_ui();
		}

		if ( true == $payment_selections['offline'] ) {
			$this->offline_ui();
		}

		// hook for thirdpary
		do_action( 'ipt_eform_payment_front_ui', $payment_selections, $payment_types, $this );

		$this->ui->column_tail();
	}

	/**
	 * Show the subscription form for automated frequency based form
	 *
	 * It expects the user to be logged in so that it can fetch relevant
	 * settings. If user is not logged in, then it throws an exception.
	 *
	 * Right now only Stripe subscription works, but we will change it in
	 * future.
	 *
	 * @throws     LogicException  If user is not logged in.
	 */
	protected function subscription_form() {
		// Check if user is logged in
		if ( ! is_user_logged_in() ) {
			throw new LogicException( 'Subscription Form works only for logged in user.', 99 );
		}

		$payment_types = array();
		$payment_selections = $this->get_payment_gateways();

		// Get the enabled payment types
		// For now, only Stripe
		if ( true == $this->settings['stripe']['enabled'] && true == $this->settings['stripe']['subscription']['enabled'] ) {
			$payment_types[] = array(
				'value' => 'stripe',
				'label' => $this->settings['stripe']['label_stripe'],
				'data' => array(
					'condid' => 'ipt_fsqm_form_' . $this->form_id . '_' . $this->key . '_payment_stripe_sub_wrap',
				),
			);
			$payment_selections['stripe'] = true;
		}

		if ( empty( $payment_types ) ) {
			$this->ui->msg_error( __( 'Please enable Stripe Gateway with subscription from CONFIG > Payment.', 'ipt_fsqm' ), true, __( 'No active payment gateway', 'ipt_fsqm' ) );
			return;
		}

		// Print gateway radios
		$this->print_gateway_radios( $payment_types );

		// Print individual gateway settings
		$this->ui->column_head( '', 'full', false, array( 'eform-checkout-gateways eform-subscription-gateways' ) );
		if ( true == $payment_selections['stripe'] ) {
			$this->stripe_subscription_ui();
		}
		$this->ui->column_tail();
	}

	/**
	 * Prints a radio element for selecting the gateway.
	 *
	 * @param      array  $payment_types  Available payment types
	 */
	protected function print_gateway_radios( $payment_types ) {
		// Print the payment gateway radios
		$sparams = array(
			$this->name_prefix . '[pmethod]',
			$payment_types,
			$this->settings['type'],
			array(
				'required' => true,
			),
			'random',
			true,
		);
		$this->ui->column_head( '', 'full', false, array( 'eform-checkout-gateways-radio' ) );
		$this->ui->question_container( $this->name_prefix . '[pmethod]', $this->data['settings']['ptitle'], '', array( array( $this->ui, 'radios' ), $sparams ), true, false, false, '', array( 'ipt_fsqm_payment_method_radio' ), array(
			'iptfsqmpp' => $this->settings['type'],
		) );
		$this->ui->column_tail();
	}

	/**
	 * Print UI for stripe subscription
	 *
	 * It would show available/stored cards for current user as well as option
	 * for entering new card.
	 */
	protected function stripe_subscription_ui() {
		echo '<div class="eform-stripe-subscription-options" id="ipt_fsqm_form_' . $this->form_id . '_' . $this->key . '_payment_stripe_sub_wrap">';
		// Show the question container
		$this->ui->question_container( $this->name_prefix, $this->data['settings']['ctitle'], __( 'all information are stored securely in stripe server.', 'ipt_fsqm' ), array( $this, 'stripe_subscription_options' ), true );
		echo '</div>';
	}

	/**
	 * Show available/stored cards and the new card UI for stripe
	 */
	public function stripe_subscription_options() {
		$stripe = EForm_Payment_Handler_Stripe::instance();
		$stripe->set_api_key( $this->settings['stripe']['api'] );
		// Get the stripe data for current users
		$customer_data = $stripe->get_customer_data( get_current_user_id() );
		// If stripe data is empty, then just show the element
		if ( empty( $customer_data ) ) {
			$this->stripe_elements();
		} else {
			// Now we need to show available cards as well as the new card UI
			$available_methods = [];
			$duplicate_cards = [];
			foreach ( $customer_data['sources'] as $card ) {
				if ( in_array( $card->fingerprint, $duplicate_cards ) ) {
					continue;
				}
				$duplicate_cards[] = $card->fingerprint;
				$available_methods[] = [
					'label' => sprintf( '%1$s <code class="eform-cc-safe-number"><span class="eform-cc-hiddens">XXXX XXXX XXXX</span> %2$s</code> (%3$d/%4$d)', $this->get_card_svg_tag( $card->brand ), $card->last4, $card->exp_month, $card->exp_year ),
					'value' => $card->id,
				];
			}
			if ( empty( $available_methods ) ) {
				$this->stripe_elements();
			} else {
				$available_methods[] = [
					'label' => __( 'Add New Card', 'ipt_fsqm' ),
					'value' => 'new',
					'data' => [
						'condid' => 'ipt_fsqm_form_' . $this->form_id . '_stripe_pm_sub_new',
					],
				];
				$selected_method = ( ! empty( $customer_data['default_source'] ) ) ? $customer_data['default_source'] : 'new';
				echo '<div class="eform-stripe-saved-cards">';
				$this->ui->radios( $this->name_prefix . '[stripe_source]', $available_methods, $selected_method, [ 'required' => true ], '1', true );
				echo '</div>';
				echo '<div id="ipt_fsqm_form_' . $this->form_id . '_stripe_pm_sub_new">';
				$this->stripe_elements();
				echo '</div>';
			}
		}
	}


	/**
	 * Print the Stripe UI
	 */
	protected function stripe_ui() {
		echo '<div class="ipt_uif_question" id="ipt_fsqm_form_' . $this->form_id . '_' . $this->key . '_payment_stripe_wrap">';
		$this->ui->question_container( $this->name_prefix, $this->data['settings']['ctitle'], __( 'we do not store any information you provide', 'ipt_fsqm' ), array( $this, 'stripe_elements' ), true );
		echo '</div>';
	}

	/**
	 * Print the stripe element to be used with Stripe.js
	 */
	public function stripe_elements() {
		// Custom Validation for the namefield
		$validation = $this->ui->get_cc_validation();
		$address_validations = $this->ui->get_address_validations();
		$ccparams = $this->get_cc_params();
		echo '<div class="eform-stripe-checkout">';
		// Name field
		$this->ui->column_head( '', 'full', true, array( 'eform-stripe-checkout-name' ) );
		$this->ui->text( $this->name_prefix . '[stripe][name]', '', '', 'none', 'normal', array( 'ipt_uif_cc_name ipt_fsqm_sayt_exclude' ), $validation, false, array(
			'placeholder' => $ccparams[2]['name'],
		) );
		$this->ui->column_tail();

		// This is mounted by Stripe.js
		$this->ui->column_head( '', 'full', true, array( 'eform-stripe-checkout-elements' ) );
		echo '<div class="input-field">'
			. '<div class="eform-stripe-elements"'
			. ' id="ipt_fsqm_form_' . $this->form_id . '_payment_stripe"'
			. ' data-stripe-pub-key="' . esc_attr( $this->settings['stripe']['pub'] ) . '"'
			. ' data-stripe-nonce="' . esc_attr( wp_create_nonce( 'ipt_fsqm_sca_intent_' . $this->form_id ) ) . '"'
			. '></div></div>';
		$this->ui->column_tail();

		// Country and ZIP
		$this->ui->column_head( '', 'half', true, array( 'no_margin_right', 'eform-stripe-checkout-country' ) );
		$this->ui->select( $this->name_prefix . '[stripe][country]', $this->ui->get_countries( $ccparams[2]['country'] ), $ccparams[1]['country'], $address_validations['country'], false, true, false, false, $ccparams[2]['country'] );
		$this->ui->column_tail();
		$this->ui->column_head( '', 'half', true, array( 'no_margin_right', 'eform-stripe-checkout-zip' ) );
		$this->ui->text( $this->name_prefix . '[stripe][zip]', '', '', 'none', 'normal', array( 'ipt_uif_cc_zip ipt_fsqm_sayt_exclude' ), $address_validations['zip'], false, array(
			'placeholder' => $ccparams[2]['zip'],
		) );
		$this->ui->column_tail();
		$this->ui->clear();
		echo '</div>';
	}

	/**
	 * UI for PayPal Express Checkout
	 */
	protected function paypal_express_ui() {
		echo '<div class="ipt_uif_question" id="ipt_fsqm_form_' . $this->form_id . '_' . $this->key . '_payment_pp">';
		$this->ui->msg_okay( $this->data['settings']['ppmsg'], true, __( 'Pay through PayPal', 'ipt_fsqm' ) );
		echo '</div>';
	}

	/**
	 * UI for Offline Checkout
	 */
	protected function offline_ui() {
		echo '<div class="ipt_uif_question" id="ipt_fsqm_form_' . $this->form_id . '_' . $this->key . '_payment_ofl">';
		$this->ui->msg_okay( $this->settings['offline']['instruction'], true, $this->settings['offline']['label'] );
		echo '</div>';
	}

	/**
	 * eForm Managed Credit Card form
	 *
	 * Used by PayPal Direct Payment and Authorized.net
	 */
	protected function cc_direct_ui() {
		echo '<div class="ipt_uif_question" id="ipt_fsqm_form_' . $this->form_id . '_' . $this->key . '_payment_cc">';
		$ccparams = $this->get_cc_params();

		$this->ui->question_container( $this->name_prefix, $this->data['settings']['ctitle'], __( 'we do not store any information you provide', 'ipt_fsqm' ), array( array( $this->ui, 'creditcard' ), $ccparams ), true );
		echo '</div>';
	}

	/**
	 * Get available payment gateways for eForm.
	 *
	 * @return     array  The payment gateways.
	 */
	protected function get_payment_gateways() {
		return IPT_FSQM_Form_Elements_Static::get_valid_payment_selections();
	}

	/**
	 * Get credit card UI default parameters.
	 *
	 * Used for generating both native CC as well as Stripe field.
	 *
	 * @return     array  Credit Card UI parameters
	 */
	protected function get_cc_params() {
		return array(
			$this->name_prefix . '[cc]',
			array(
				'name' => '',
				'number' => '',
				'expiry' => '',
				'cvc' => '',
				'ctype' => '',
				'address' => '',
				'country' => $this->data['settings']['country'],
				'zip' => '',
			),
			array(
				'name' => __( 'Cardholder\'s name', 'ipt_fsqm' ),
				'number' => __( 'Card number', 'ipt_fsqm' ),
				'expiry' => __( 'MM/YY', 'ipt_fsqm' ),
				'cvc' => __( 'CVC', 'ipt_fsqm' ),
				'address' => __( 'Address', 'ipt_fsqm' ),
				'country' => __( 'Country', 'ipt_fsqm' ),
				'zip' => __( 'ZIP/Postal Code', 'ipt_fsqm' ),
			),
		);
	}

	/**
	 * Get SVG image tag of a card by it's type. Available card types are found
	 * inside /static/front/images/cards/ directory.
	 *
	 * @param      string   $type    Card type
	 * @param      integer  $height  The height if the image, set in pixels
	 *
	 * @return     string   Card SVG image tag
	 */
	protected function get_card_svg_tag( $type, $height = 16 ) {
		$available = [
			'amex',
			'cirrus',
			'dinersclub',
			'discover',
			'jcb',
			'maestro',
			'mastercard',
			'visa',
		];
		$type = strtolower( preg_replace( '/[^a-zA-Z]/', '', $type ) );
		$url_source = IPT_FSQM_Loader::$static_location . 'front/images/cards/';
		$image = $url_source . '/generic.svg';
		if ( in_array( $type, $available ) ) {
			$image = $url_source . $type . '.svg';
		}
		return '<img src="' . esc_attr( $image ) . '" style="width: auto; height: ' . $height . 'px;" class="eform-card-svg" />';
	}
}
