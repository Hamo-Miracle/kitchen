<?php
/**
 * WP Feedback, Surver & Quiz Manager - Pro Form Elements Class
 * Static APIs
 *
 * @todo #474
 *
 * @package eForm - WordPress Form Builder
 * @subpackage Form\Static
 * @author Swashata Ghosh <swashata@intechgrity.com>
 * @codeCoverageIgnore
 */
class IPT_FSQM_Form_Elements_Static {
	/*==========================================================================
	 * SYSTEM APIs - Needs to be called
	 *========================================================================*/
	/**
	 * Admin Ajax Init
	 * Call inside the loader with (is_admin()) context
	 */
	public static function admin_init() {
		self::ipt_fsqm_quick_preview();
	}

	public static function common_init() {
		self::richtext_init();
		self::uif_label_latex_init();
		self::ipt_fsqm_report();
		self::ipt_fsqm_save_form();
		self::standalone_form_init();
		self::email_rewrite_init();
		self::payment_email_rewrite_init();
		self::user_portal_init();
		self::uploader_ajax_init();
		self::coupon_ajax_init();
		self::payment_sync_init();
		self::stripe_sca_init();
	}

	/*==========================================================================
	 * New Stripe SCA API
	 *========================================================================*/
	public static function stripe_sca_init() {
		add_action(
			'wp_ajax_ipt_fsqm_create_stripe_sca_intent',
			[ __CLASS__, 'create_stripe_sca_intent' ]
		);
		add_action(
			'wp_ajax_nopriv_ipt_fsqm_create_stripe_sca_intent',
			[ __CLASS__, 'create_stripe_sca_intent' ]
		);

		add_action(
			'wp_ajax_ipt_fsqm_verify_stripe_subscription_payment',
			[ __CLASS__, 'verify_stripe_subscription_payment' ]
		);
		add_action(
			'wp_ajax_nopriv_ipt_fsqm_verify_stripe_subscription_payment',
			[ __CLASS__, 'verify_stripe_subscription_payment' ]
		);
	}

	public static function verify_stripe_subscription_payment() {
		$form_id = @$_REQUEST['form_id'];
		$nonce = @$_REQUEST['_wpnonce'];
		$token = @$_REQUEST['token'];

		$return = [
			'msg' => null,
		];
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

		// Now check for authenticity of this request
		$authentic = true;
		// if nonce wasn't verified
		if ( ! wp_verify_nonce( $nonce, 'ipt_fsqm_sca_intent_' . $form_id ) ) {
			$authentic = false;
		}

		$form = new IPT_FSQM_Form_Elements_Data( null, $form_id );
		// if the form doesn't exist
		if ( ! $form->form_id ) {
			$authentic = false;
		}

		// if form doesn't have payments or Stripe enabeld
		$payment_settings = $form->settings['payment'];
		if (
			! $payment_settings['enabled']
			|| ! $payment_settings['stripe']['enabled']
		) {
			$authentic = false;
		}

		// If form doesn't have Stripe subscription enabled
		if (
			'recurring' !== $payment_settings['payment_type']
			|| ! $payment_settings['stripe']['subscription']['enabled']
		) {
			$authentic = false;
		}

		$data_id = self::decode_trackback_id( $token );
		$data = new IPT_FSQM_Form_Elements_Data( null, null );
		if ( ! $data_id ) {
			$authentic = false;
		} else {
			$data->init( $data_id, null );
			if (
				! $data->form_id ||
				(int) $form_id !== (int) $data->form_id
			) {
				$authentic = false;
			}
		}

		if ( ! $authentic ) {
			$return['success'] = false;
			$return['msg'] = __( 'Cheatin&#8217; uh?' );

			echo json_encode( (object) $return );
			die();
		}

		// everything checks out
		$stripe_handler = EForm_Payment_Handler_Stripe::instance();
		$stripe_handler->set_api_key( $payment_settings['stripe']['api'] );
		$payment_data = $data->get_payment_data();

		if (
			$payment_data !== null
			&& $stripe_handler->verify_subscription( $payment_data->txn )
			) {
			$data->set_paid_status( 1 );
			$data->set_payments_data_status( 1 );
			$return['success'] = true;
			$return['msg'] = $data->get_onetime_payment_success_message();
		} else {
			$data->set_paid_status( 0 );
			$data->set_payments_data_status( 3 );
			$return['msg'] = $data->get_payment_error_message();
		}

		echo json_encode( (object) $return );
		die();
	}

	public static function create_stripe_sca_intent() {
		$form_id = @$_REQUEST['form_id'];
		$nonce = @$_REQUEST['_wpnonce'];
		$amount = (float) @$_REQUEST['amount'];
		$subscription = @$_REQUEST['subscription'];
		$subscription_source = @$_REQUEST['subscriptionSource'];
		if ( $subscription && 'true' === $subscription ) {
			$subscription = true;
		} else {
			$subscription = false;
		}

		$return = [
			'success' => true,
			'intent' => null,
		];
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

		// Now check for authenticity of this request
		$authentic = true;
		// if nonce wasn't verified
		if ( ! wp_verify_nonce( $nonce, 'ipt_fsqm_sca_intent_' . $form_id ) ) {
			$authentic = false;
		}

		$form = new IPT_FSQM_Form_Elements_Data( null, $form_id );
		// if the form doesn't exist
		if ( ! $form->form_id ) {
			$authentic = false;
		}

		// if form doesn't have payments or SCA enabeld
		$payment_settings = $form->settings['payment'];
		if (
			! $payment_settings['enabled']
			|| ! $payment_settings['stripe']['enabled']
		) {
			$authentic = false;
		}

		if ( ! $authentic ) {
			$return['success'] = false;
			$return['msg'] = __( 'Cheatin&#8217; uh?' );

			echo json_encode( (object) $return );
			die();
		}

		// everything checks out
		$stripe_handler = EForm_Payment_Handler_Stripe::instance();
		$stripe_handler->set_api_key( $payment_settings['stripe']['api'] );

		// create actual amount depending on the settings
		if ( false == $payment_settings['stripe']['zero_decimal'] ) {
			$amount = $amount * 100;
		}

		try {
			$intent = $stripe_handler->create_intent(
				$amount,
				$payment_settings['currency'],
				$subscription,
				$subscription_source
			);
			$return['intent'] = [
				'status' => $intent->status,
				'secret' => $intent->client_secret,
			];
		} catch( Exception $e ) {
			ipt_error_log( $e );
			$return['success'] = false;
		}

		echo json_encode( $return );
		die();
	}

	/*==========================================================================
	 * Country Listing APIs
	 *========================================================================*/
	public static function get_countries() {
		// Only one search per request
		static $countries = array();
		if ( ! empty( $countries ) ) {
			return $countries;
		}

		// Not found, let's create.
		$path = IPT_EFORM_ABSPATH . 'static/front/countries.json';
		$raw_data = json_decode( file_get_contents( $path ), true );
		$province_data_path = IPT_EFORM_ABSPATH . 'bower_components/countryjs/data/';

		foreach ( $raw_data as $item ) {
			$country_filename = strtolower( $item['name'] ) . '.json';
			$province = array();

			if ( file_exists( $province_data_path . $country_filename ) ) {
				$country_data = @json_decode( file_get_contents( $province_data_path . $country_filename ), true );

				$province = $country_data['provinces'] ?? array();
			}

			$countries[] = array(
				'value' => $country_filename,
				'label' => $item['name'],
				'data' => array(
					'provinces' => $province,
					'iso' => array(
						'alpha2' => $item['isoAlpha2'] ?? '',
						'alpha3' => $item['isoAlpha3'] ?? '',
					)
				),
			);
		}

		return $countries;
	}

	public static function get_province() {
		$path = IPT_EFORM_ABSPATH . 'bower_components/countryjs/data/';
		$country = @$_REQUEST['country'];
		$province = array();
		$cinfo = self::get_country_json( $country );
		if ( ! empty( $cinfo ) && isset( $cinfo['provinces'] ) ) {
			$province = $cinfo['provinces'];
		}
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		echo json_encode( $province );
		die();
	}

	/**
	 * Get the country JSON from the countryjs data
	 *
	 * @param      string  $country  The json file name for the country
	 *
	 * @return     array   The json_decode version of country.json file
	 */
	public static function get_country_json( $country ) {
		$path = IPT_EFORM_ABSPATH . 'bower_components/countryjs/data/';
		// Sanitize
		// Prevent hacks B-) BOOAH
		// Try doing something like ../../../wp-content/... }-D
		$country = preg_replace( '/[^a-zA-Z0-9\._-]/i', '', $country );
		$location = $path . $country;
		if ( file_exists( $location ) ) {
			$cinfo = @json_decode( file_get_contents( $location ), true );
			return $cinfo;
		}
		return array();
	}

	public static function get_country_list() {
		return json_decode( '{"afghanistan.json":"Afghanistan","albania.json":"Albania","algeria.json":"Algeria","american_samoa.json":"American Samoa","angola.json":"Angola","anguilla.json":"Anguilla","antigua_and_barbuda.json":"Antigua and Barbuda","argentina.json":"Argentina","armenia.json":"Armenia","aruba.json":"Aruba","australia.json":"Australia","austria.json":"Austria","azerbaijan.json":"Azerbaijan","bahamas.json":"The Bahamas","bahrain.json":"Bahrain","bangladesh.json":"Bangladesh","barbados.json":"Barbados","belarus.json":"Belarus","belgium.json":"Belgium","belize.json":"Belize","benin.json":"Benin","bermuda.json":"Bermuda","bhutan.json":"Bhutan","bolivia.json":"Bolivia","bosnia_and_herzegovina.json":"Bosnia and Herzegovina","botswana.json":"Botswana","brazil.json":"Brazil","british_virgin_islands.json":"British Indian Ocean Territory","brunei.json":"Brunei","bulgaria.json":"Bulgaria","burkina_faso.json":"Burkina Faso","burundi.json":"Burundi","cambodia.json":"Cambodia","cameroon.json":"Cameroon","canada.json":"Canada","cape_verde.json":"Cape Verde","cayman_islands.json":"Cayman Islands","central_african_republic.json":"Central African Republic","chad.json":"Chad","chile.json":"Chile","china.json":"China","christmas_island.json":"Christmas Island","cocos_keeling_islands.json":"Cocos (Keeling) Islands","colombia.json":"Colombia","comoros.json":"Comoros","congo_democratic_republic_of_the.json":"Republic of the Congo","congo_republic_of_the.json":"Democratic Republic of the Congo","cook_islands.json":"Cook Islands","costa_rica.json":"Costa Rica","cote_d_ivoire.json":"Ivory Coast","croatia.json":"Croatia","cuba.json":"Cuba","cyprus.json":"Cyprus","czeck_republic.json":"Czech Republic","denmark.json":"Denmark","djibouti.json":"Djibouti","dominica.json":"Dominica","dominican_republic.json":"Dominican Republic","ecuador.json":"Ecuador","egypt.json":"Egypt","el_salvador.json":"El Salvador","equatorial_guinea.json":"Equatorial Guinea","eritrea.json":"Eritrea","estonia.json":"Estonia","ethiopia.json":"Ethiopia","falkland_islands_islas_malvinas.json":"Falkland Islands","faroe_islands.json":"Faroe Islands","fiji.json":"Fiji","finland.json":"Finland","france.json":"France","french_guiana.json":"French Guiana","french_polynesia.json":"French Polynesia","french_southern_and_antarctic_lands.json":"French Southern and Antarctic Lands","gabon.json":"Gabon","gambia_the.json":"The Gambia","georgia.json":"Georgia","germany.json":"Germany","ghana.json":"Ghana","gibraltar.json":"Gibraltar","greece.json":"Greece","greenland.json":"Greenland","grenada.json":"Grenada","guadeloupe.json":"Guadeloupe","guam.json":"Guam","guatemala.json":"Guatemala","guernsey.json":"Guernsey","guinea.json":"Guinea","guinea_bissau.json":"Guinea-Bissau","guyana.json":"Guyana","haiti.json":"Haiti","heard_island_and_mc_donald_islands.json":"Heard Island and McDonald Islands","honduras.json":"Honduras","hong_kong.json":"Hong Kong","howland_island.json":"Hungary","iceland.json":"Iceland","india.json":"India","indonesia.json":"Indonesia","iran.json":"Iran","iraq.json":"Iraq","ireland.json":"Ireland","israel.json":"Israel","italy.json":"Italy","jamaica.json":"Jamaica","japan.json":"Japan","jersey.json":"Jersey","jordan.json":"Jordan","kazakhstan.json":"Kazakhstan","kenya.json":"Kenya","kiribati.json":"Kiribati","korea_north.json":"North Korea","korea_south.json":"South Korea","kuwait.json":"Kuwait","kyrgyzstan.json":"Kyrgyzstan","laos.json":"Laos","latvia.json":"Latvia","lebanon.json":"Lebanon","lesotho.json":"Lesotho","liberia.json":"Liberia","libya.json":"Libya","liechtenstein.json":"Liechtenstein","lithuania.json":"Lithuania","luxembourg.json":"Luxembourg","macau.json":"Macau","macedonia_former_yugoslav_republic_of.json":"Republic of Macedonia","madagascar.json":"Madagascar","malawi.json":"Malawi","malaysia.json":"Malaysia","maldives.json":"Maldives","mali.json":"Mali","malta.json":"Malta","man_isle_of.json":"Isle of Man","marshall_islands.json":"Marshall Islands","martinique.json":"Martinique","mauritania.json":"Mauritania","mauritius.json":"Mauritius","mayotte.json":"Mayotte","mexico.json":"Mexico","micronesia_federated_states_of.json":"Federated States of Micronesia","moldova.json":"Moldova","monaco.json":"Monaco","mongolia.json":"Mongolia","montserrat.json":"Montserrat","morocco.json":"Morocco","mozambique.json":"Mozambique","namibia.json":"Namibia","nauru.json":"Nauru","nepal.json":"Nepal","netherlands.json":"Netherlands","new_caledonia.json":"New Caledonia","new_zealand.json":"New Zealand","nicaragua.json":"Nicaragua","niger.json":"Niger","nigeria.json":"Nigeria","niue.json":"Niue","norfolk_island.json":"Norfolk Island","northern_mariana_islands.json":"Northern Mariana Islands","norway.json":"Norway","oman.json":"Oman","pakistan.json":"Pakistan","palau.json":"Palau","panama.json":"Panama","papua_new_guinea.json":"Papua New Guinea","paraguay.json":"Paraguay","peru.json":"Peru","philippines.json":"Philippines","pitcaim_islands.json":"Pitcairn Islands","poland.json":"Poland","portugal.json":"Portugal","puerto_rico.json":"Puerto Rico","qatar.json":"Qatar","reunion.json":"R\u00e9union","romainia.json":"Romania","russia.json":"Russia","rwanda.json":"Rwanda","saint_helena.json":"Saint Helena","saint_kitts_and_nevis.json":"Saint Kitts and Nevis","saint_lucia.json":"Saint Lucia","saint_pierre_and_miquelon.json":"Saint Pierre and Miquelon","saint_vincent_and_the_grenadines.json":"Saint Vincent and the Grenadines","samoa.json":"Samoa","san_marino.json":"San Marino","sao_tome_and_principe.json":"S\u00e3o Tom\u00e9 and Pr\u00edncipe","saudi_arabia.json":"Saudi Arabia","scotland.json":"Scotland","senegal.json":"Senegal","seychelles.json":"Seychelles","sierra_leone.json":"Sierra Leone","singapore.json":"Singapore","slovakia.json":"Slovakia","slovenia.json":"Slovenia","solomon_islands.json":"Solomon Islands","somalia.json":"Somalia","south_africa.json":"South Africa","south_georgia_and_south_sandwich_islands.json":"South Georgia","south_sudan.json":"South Sudan","spain.json":"Spain","sri_lanka.json":"Sri Lanka","sudan.json":"Sudan","suriname.json":"Suriname","svalbard.json":"Svalbard and Jan Mayen","swaziland.json":"Swaziland","sweden.json":"Sweden","switzerland.json":"Switzerland","syria.json":"Syria","taiwan.json":"Taiwan","tajikistan.json":"Tajikistan","tanzania.json":"Tanzania","thailand.json":"Thailand","tobago.json":"East Timor","toga.json":"Togo","tokelau.json":"Tokelau","tonga.json":"Tonga","trinidad.json":"Trinidad and Tobago","tunisia.json":"Tunisia","turkey.json":"Turkey","turkmenistan.json":"Turkmenistan","tuvalu.json":"Tuvalu","uganda.json":"Uganda","ukraine.json":"Ukraine","united_arab_emirates.json":"United Arab Emirates","united_kingdom.json":"United Kingdom","united_states_of_america.json":"United States","uruguay.json":"Uruguay","uzbekistan.json":"Uzbekistan","vanuatu.json":"Vanuatu","venezuela.json":"Venezuela","vietnam.json":"Vietnam","wales.json":"Wales","wallis_and_futuna.json":"Wallis and Futuna","western_sahara.json":"Western Sahara","yemen.json":"Yemen","zambia.json":"Zambia","zimbabwe.json":"Zimbabwe"}', ARRAY_A );
	}


	/*==========================================================================
	 * RichText Init - Adds the filters
	 *========================================================================*/
	public static function richtext_init() {
		add_filter( 'ipt_uif_richtext', array( __CLASS__, 'richtext_filter' ), 8 );
	}

	public static function richtext_filter( $content ) {
		global $shortcode_tags, $ipt_fsqm_settings;
		$original_shortcode_tags = $shortcode_tags;
		$shortcodes_to_remove = array();
		$shortcodes_to_remove = array(
			'ipt_fsqm_form', 'ipt_fsqm_trackback', 'ipt_fsqm_utrackback', 'ipt_fsqm_trends',
		);
		$shortcode_tags = array();
		foreach ( $shortcodes_to_remove as $key ) {
			$shortcode_tags[$key] = 1;
		}
		$content = strip_shortcodes( $content );
		$shortcode_tags = $original_shortcode_tags;
		return $content;
	}

	/*==========================================================================
	 * UIF Label Adding Latex Support
	 *========================================================================*/
	public static function uif_label_latex_init() {
		// Add the generic function
		// But add in when all plugins are loaded
		add_action( 'wp_loaded', array( __CLASS__, 'uif_label_latex_markup' ) );
		// Add the shortcode
		add_filter( 'ipt_uif_label', array( __CLASS__, 'uif_label_latex_filter' ) );
	}

	public static function uif_label_latex_markup() {
		// Add the generic function
		if ( function_exists( 'latex_markup' ) ) {
			add_filter( 'ipt_uif_richtext', 'latex_markup', 9 );
			add_filter( 'ipt_uif_label', 'latex_markup' );
		}
	}
	public static function uif_label_latex_filter( $content ) {
		if ( shortcode_exists( 'latex' ) ) {
			global $shortcode_tags;
			$original_shortcode_tags = $shortcode_tags;
			$shortcode_tags = array();
			foreach ( $original_shortcode_tags as $o_key => $o_val ) {
				if ( $o_key == 'latex' ) {
					$shortcode_tags[$o_key] = $o_val;
				}
			}
			$content = do_shortcode( $content );
			$shortcode_tags = $original_shortcode_tags;
			$content = strip_shortcodes( $content );
		}
		return $content;
	}



	/*==========================================================================
	 * Uploader Callbacks
	 *========================================================================*/
	public static function uploader_ajax_init() {
		add_action( 'wp_ajax_ipt_fsqm_fu_upload', array( __CLASS__, 'uploader_ajax_upload' ) );
		add_action( 'wp_ajax_ipt_fsqm_fu_download', array( __CLASS__, 'uploader_ajax_download' ) );
		add_action( 'wp_ajax_ipt_fsqm_fu_delete', array( __CLASS__, 'uploader_ajax_delete' ) );

		add_action( 'wp_ajax_nopriv_ipt_fsqm_fu_upload', array( __CLASS__, 'uploader_ajax_upload' ) );
		add_action( 'wp_ajax_nopriv_ipt_fsqm_fu_download', array( __CLASS__, 'uploader_ajax_download' ) );
		add_action( 'wp_ajax_nopriv_ipt_fsqm_fu_delete', array( __CLASS__, 'uploader_ajax_delete' ) );

		// recaptcha
		add_action( 'wp_ajax_ipt_fsqm_fu_captcha', [ __CLASS__, 'uploader_ajax_captcha' ] );
		add_action( 'wp_ajax_nopriv_ipt_fsqm_fu_captcha', [ __CLASS__, 'uploader_ajax_captcha' ] );
	}

	public static function uploader_ajax_captcha() {
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

		$form_id = (int) @$_POST['form_id'];
		$element_id = (int) @$_POST['element_key'];
		$captcha_value = @$_POST['captcha'];
		$upload_handler = new IPT_FSQM_Form_Elements_Uploader( $form_id, $element_id );

		$return = [
			'status' => 'error',
			'message' => __( 'Invalid request.', 'ipt_fsqm' ),
		];

		if ( ! $upload_handler->is_valid_element() ) {
			$return['message'] = __( 'Invalid element key.', 'ipt_fsqm' );
			echo json_encode( (object) $return );
			die();
		}
		if ( empty( $captcha_value ) ) {
			$return['message'] = __( 'Invalid captcha value.', 'ipt_fsqm' );
			echo json_encode( (object) $return );
			die();
		}
		$element_settings = $upload_handler->get_element_settings();
		$secret_key = $element_settings['secret_key'];
		if ( empty( $secret_key ) ) {
			$return['message'] = __( 'Invalid reCaptcha Key.', 'ipt_fsqm' );
			echo json_encode( (object) $return );
			die();
		}
		// now resolve
		$rcapi = new \ReCaptcha\ReCaptcha( $secret_key );
		$rcresp = $rcapi->verify( $captcha_value, $_SERVER['REMOTE_ADDR'] );
		if ( ! $rcresp->isSuccess() ) {
			$return['message'] = __( 'Invalid captcha. Please try again.', 'ipt_fsqm' );
			echo json_encode( (object) $return );
			die();
		} else {
			$ctx = (string) time();
			$nonce = wp_create_nonce( "ipt_fsqm_fu_nonce_captcha_" . $ctx . $secret_key . $element_id . $form_id );
			$return = [
				'status' => 'success',
				'data' => [
					'nonce' => $nonce,
					'ctx' => $ctx,
				],
			];
			echo json_encode( (object) $return );
			die();
		}
	}

	public static function uploader_ajax_upload() {
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		$nonce = @$_POST['nonce'];

		$form_id = (int) @$_POST['form_id'];
		$element_id = (int) @$_POST['element_key'];
		$data_id = ( 'null' == @$_POST['data_id'] || '' == @$_POST['data_id'] || 0 == @$_POST['data_id'] ) ? null : (int) @$_POST['data_id'];
		$files_key = @$_POST['files_key'];

		$return = array(
			'files' => array(
				array(
					'name' => __( 'Invalid', 'ipt_fsqm' ),
					'size' => 0,
					'error' => __( 'Invalid nonce.', 'ipt_fsqm' ),
				),
			),
		);
		if ( ! wp_verify_nonce( $nonce, 'ipt_fsqm_upload_' . $form_id . '_' . $data_id . '_' . $element_id ) ) {
			echo json_encode( (object) $return );
			die();
		}

		// verify with upload handler and nonce
		$upload_handler = new IPT_FSQM_Form_Elements_Uploader( $form_id, $element_id );
		if ( ! $upload_handler->is_valid_element() ) {
			$return['files'][0]['error'] = __( 'Invalid element key.', 'ipt_fsqm' );
			echo json_encode( (object) $return );
			die();
		}
		$element_settings = $upload_handler->get_element_settings();
		if ( $element_settings['recaptcha'] ) {
			$secret_key = $element_settings['secret_key'];
			$ctx = (int) @$_REQUEST['captcha_ctx'];
			$captcha_nonce = (string) @$_REQUEST['captcha_nonce'];
			$current_time = time();
			if ( $current_time - $ctx > 60 * 60) {
				$return['files'][0]['error'] = __( 'Invalid nonce context.', 'ipt_fsqm' );
				echo json_encode( (object) $return );
				die();
			}
			if ( ! wp_verify_nonce( $captcha_nonce, "ipt_fsqm_fu_nonce_captcha_" . $ctx . $secret_key . $element_id . $form_id ) ) {
				$return['files'][0]['error'] = __( 'Invalid captcha nonce.', 'ipt_fsqm' );
				echo json_encode( (object) $return );
				die();
			}
		}

		$return = array(
			'files' => $upload_handler->process_file_uploads( $files_key ),
		);
		echo json_encode( (object) $return );

		wp_create_nonce( 'ipt_fsqm_upload_' . $form_id . '_' . $data_id . '_' . $element_id );

		die();
	}
	public static function uploader_ajax_download() {
		$nonce = @$_GET['download_nonce'];
		$data_id = (int) @$_GET['data_id'];
		$form_id = (int) @$_GET['form_id'];
		$element_id = (int) @$_GET['element_key'];

		if ( ! wp_verify_nonce( $nonce, 'ipt_fsqm_download_' . $form_id . '_' . $data_id . '_' . $element_id ) ) {
			die( __( 'Cheatin&#8217; uh?' ) );
		}

		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		$upload_handler = new IPT_FSQM_Form_Elements_Uploader( $form_id, $element_id );
		$uploads = $upload_handler->get_uploads( $data_id, true );
		$formatted_uploads = array();
		foreach ( (array) $uploads as $upload ) {
			$valid_audio = in_array( strtolower( $upload['ext'] ), array( 'mp3', 'wav', 'ogg' ) ) ? true : false;
			$valid_video = strtolower( $upload['ext'] ) == 'mp4' ? true : false;
			$formatted_uploads[] = array(
				'id' => $upload['id'],
				'name' => $upload['name'],
				'size' => $upload['size'],
				'url' => $upload['guid'],
				'thumbnailUrl' => $upload['thumb_url'],
				'deleteUrl' => $upload['delete'],
				'deleteType' => 'DELETE',
				'validAudio' => $valid_audio,
				'validVideo' => $valid_video,
				'type' => $upload['mime_type'],
			);
		}
		$return = array(
			'files' => $formatted_uploads,
		);
		echo json_encode( (object) $return );
		die();
	}

	public static function uploader_ajax_delete() {
		if ( $_SERVER['REQUEST_METHOD'] !== 'DELETE' ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}
		$file_id = (int) @$_GET['file_id'];
		$element_id = (int) @$_GET['element_id'];
		$form_id = (int) @$_GET['form_id'];
		$wpnonce = @$_GET['_wpnonce'];
		$file = @$_GET['file'];

		if ( ! wp_verify_nonce( $wpnonce, 'ipt_fsqm_fu_delete_file_' . $file_id ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}

		$upload_handler = new IPT_FSQM_Form_Elements_Uploader( $form_id, $element_id );

		$return = array(
			$file => $upload_handler->delete_file( $file_id ),
		);

		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		echo json_encode( (object) $return );
		die();
	}

	/*==========================================================================
	 * Coupons Callbacks
	 *========================================================================*/
	public static function coupon_ajax_init() {
		add_action( 'wp_ajax_ipt_fsqm_validate_coupon', array( __CLASS__, 'coupon_ajax_cb' ) );
		add_action( 'wp_ajax_nopriv_ipt_fsqm_validate_coupon', array( __CLASS__, 'coupon_ajax_cb' ) );
	}

	public static function coupon_ajax_cb() {
		$form_id = @$_REQUEST['form_id'];
		$nonce = @$_REQUEST['_wpnonce'];
		$coupon = @$_REQUEST['coupon'];
		$amount = @$_REQUEST['amount'];

		$return = array(
			'success' => true,
			'msg' => '',
		);
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

		if ( ! wp_verify_nonce( $nonce, 'ipt_fsqm_coupon_' . $form_id ) ) {
			$return['success'] = false;
			$return['msg'] = __( 'Cheatin&#8217; uh?' );

			echo json_encode( (object) $return );
			die();
		}

		// Nonce validated
		// Now check if coupon code exists
		$form = new IPT_FSQM_Form_Elements_Base( $form_id );
		$coupons = array();
		if ( ! empty( $form->settings['payment']['coupons'] ) ) {
			foreach ( $form->settings['payment']['coupons'] as $c_key => $c ) {
				$coupons[$c_key] = $c['code'];
			}
		}

		if ( ! in_array( $coupon, $coupons ) ) {
			$return['success'] = false;
			$return['msg'] = __( 'Invalid coupon code', 'ipt_fsqm' );
			echo json_encode( (object) $return );
			die();
		}

		// Coupon code in the array
		$coupon_key = array_search( $coupon, $coupons );
		$coupon_config = $form->settings['payment']['coupons'][$coupon_key];

		// Now check if minimum value is satisfied
		if ( $amount < $coupon_config['min'] ) {
			$return['success'] = false;
			$return['msg'] = __( 'Coupon not applicable for this amount', 'ipt_fsqm' );
			echo json_encode( (object) $return );
			die();
		}

		// Calculate the new formula
		$old_formula = $new_formula = $form->settings['payment']['formula'];

		if ( $coupon_config['type'] == 'percentage' ) {
			$new_formula = '(' . $old_formula . ')*' . ( ( 100 - $coupon_config['value'] ) / 100 );
			$return['msg'] = sprintf( __( '%1$s%% Discount', 'ipt_fsqm' ), $coupon_config['value'] );
		} else {
			$new_formula = '(' . $old_formula . ')-' . $coupon_config['value'];
			$return['msg'] = sprintf( __( '%1$s %2$s Discount', 'ipt_fsqm' ), $coupon_config['value'], $form->settings['payment']['currency'] );
		}
		$return['formula'] = $new_formula;
		echo json_encode( (object) $return );
		die();
	}


	/*==========================================================================
	 * Standalone Form APIs
	 *========================================================================*/
	protected static function standalone_form_init() {
		// Add the ajax for the Form Builder Page
		add_action( 'wp_ajax_ipt_fsqm_preview_form', array( __CLASS__, 'standalone_form_output' ) );
		add_action( 'wp_ajax_ipt_fsqm_standalone_embed_generate', array( __CLASS__, 'standalone_embed_generate' ) );

		add_action( 'init', array( __CLASS__, 'standalone_rewrite' ) );
		add_action( 'template_redirect', array( __CLASS__, 'standalone_frontend' ) );
	}

	public static function standalone_embed_generate() {
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		$form_id = isset( $_REQUEST['form_id'] ) ? (int) $_REQUEST['form_id'] : 0;
		$permalink = self::standalone_permalink_parts( $form_id );

		echo json_encode( $permalink );
		die();
	}

	public static function standalone_base() {
		global $ipt_fsqm_settings;
		$base = ( ! isset( $ipt_fsqm_settings['standalone'] ) || ! isset( $ipt_fsqm_settings['standalone']['base'] ) || '' == $ipt_fsqm_settings['standalone']['base'] ) ? false : $ipt_fsqm_settings['standalone']['base'];
		if ( false !== $base ) {
			$base = sanitize_title( $base, 'eforms' );
		}
		return apply_filters( 'ipt_fsqm_standalone_base', $base );
	}

	public static function standalone_permalink_parts( $form_id ) {
		global $wpdb, $ipt_fsqm_info;

		$form_title = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$ipt_fsqm_info['form_table']} WHERE id = %d", $form_id ) );

		if ( null == $form_title ) {
			return false;
		}

		$base = self::standalone_base();

		if ( false === $base ) {
			return false;
		}

		$slug = sanitize_title( $form_title );

		$return = array(
			'base' => $base,
			'slug' => $slug,
			'id' => $form_id,
			'url' => home_url( '/' ) . $base . '/' . $slug . '/' . $form_id . '/',
			'shortlink' => home_url( '/' ) . $base . '/' . $form_id . '/',
		);

		return apply_filters( 'ipt_fsqm_standalone_permalink', $return );
	}

	public static function standalone_canonical( $url = '' ) {
		$form_id = get_query_var( 'ipt_fsqm_form_id' );
		$permalink = self::standalone_permalink_parts( $form_id );

		if ( false === $permalink || ! isset( $permalink['url'] ) ) {
			return $url;
		} else {
			return $permalink['url'];
		}
	}

	public static function standalone_title( $title, $sep = '|' ) {
		$form_id = get_query_var( 'ipt_fsqm_form_id' );
		$form = self::get_form( $form_id );

		if ( $form == null ) {
			return $title;
		}
		$form_elm = new IPT_FSQM_Form_Elements_Base( $form_id );
		$page_title = $form->name;
		if ( '' != $form_elm->settings['standalone']['title'] ) {
			$page_title = $form_elm->settings['standalone']['title'];
		}

		return sprintf( '%1$s %2$s %3$s', $page_title, $sep, get_bloginfo( 'name' ) );
	}

	public static function standalone_rewrite() {
		global $wp;
		// Now the rewrite rule magic
		// First some sanity check
		$base = self::standalone_base();
		if ( false === $base ) {
			return;
		}
		// Add our query vars
		$wp->add_query_var( 'ipt_fsqm_rewrite' );
		$wp->add_query_var( 'ipt_fsqm_form_id' );

		// Prepare the regex and redirect depending on the base
		$reg_ex = '^' . $base . '/?([^/]*)/([0-9]+)';
		$redirect = 'index.php?ipt_fsqm_rewrite=$matches[1]&ipt_fsqm_form_id=$matches[2]';

		// Add the rewrite rule
		add_rewrite_rule( $reg_ex, $redirect, 'top' );

		// Flush the rewrite rule if necessary
		// Expected is for plugin update or new installation
		if ( get_option( 'ipt_fsqm_flush_rewrite', false ) ) {
			flush_rewrite_rules( true );
			update_option( 'ipt_fsqm_flush_rewrite', false );
		}
	}

	public static function standalone_frontend() {
		$ipt_fsqm_rewrite = get_query_var( 'ipt_fsqm_rewrite' );
		$ipt_fsqm_form_id = get_query_var( 'ipt_fsqm_form_id' );
		if ( '' == $ipt_fsqm_form_id ) {
			return;
		}

		$permalink = self::standalone_permalink_parts( $ipt_fsqm_form_id );

		// Check if it is present
		if ( $permalink === false ) {
			// 404 it
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			return;
		}

		// Redirect to proper page for SEO
		if ( $permalink['slug'] != $ipt_fsqm_rewrite ) {
			wp_redirect( $permalink['url'], 301 );
			die();
		}

		// Add the canonical filters
		add_filter( 'aioseop_canonical_url', array( __CLASS__, 'standalone_canonical' ) );

		// Add the title filter
		add_filter( 'wp_title', array( __CLASS__, 'standalone_title' ), 20, 2 );
		add_filter( 'aioseop_title_single', array( __CLASS__, 'standalone_title' ), 10, 2 );
		add_filter( 'aioseop_title_page', array( __CLASS__, 'standalone_title' ), 10, 2 );

		// Output the form
		self::standalone_form_output( (int) $ipt_fsqm_form_id );
	}

	public static function standalone_form_output( $form_id = false ) {
		global $ipt_fsqm_settings;
		if ( $form_id == false ) {
			$form_id = @$_REQUEST['form_id'];
		}
		$form = new IPT_FSQM_Form_Elements_Front( null, $form_id );
		$theme_dir = get_stylesheet_directory();
		$theme_uri = get_stylesheet_directory_uri();
		$base_css = '';
		$form_css = '';
		if ( file_exists( $theme_dir . '/fsqm-pro.css' ) ) {
			$base_css = '<link href="' . esc_url( $theme_uri . '/fsqm-pro.css' ) . '" media="all" rel="stylesheet" type="text/css" />';
		}
		if ( file_exists( $theme_dir . '/fsqm-pro-' . $form_id . '.css' ) ) {
			$form_css = '<link href="' . esc_url( $theme_uri . '/fsqm-pro-' . $form_id . '.css' ) . '" media="all" rel="stylesheet" type="text/css" />';
		}
		add_filter( 'show_admin_bar', '__return_false' );

		// Set the dynamic theme
		if ( isset( $_GET['fsqm_theme'] ) ) {
			$theme = $form->get_theme_by_id( strip_tags( $_GET['fsqm_theme'] ) );
			if ( ! empty( $theme['src'] ) ) {
				$form->settings['theme']['template'] = strip_tags( $_GET['fsqm_theme'] );
			}
		}

		// Set the dynamic type override
		if ( isset( $_GET['element_style'] ) && 'boxy' === $_GET['element_style'] ) {
			$form->settings['theme']['element_style'] = 'boxy';
		}

		// Set the bg color
		$bg_color = 'ffffff';
		if ( isset( $_GET['bg'] ) ) {
			$bg_color = strip_tags( $_GET['bg'] );
			if ( ! ctype_xdigit( $bg_color ) && $bg_color !== 'transparent' ) {
				$bg_color = 'ffffff';
			}
		}

		// Prepare variables for JSAPI
		$js_api = array(
			'id' => $form_id,
			'name' => $form->name,
			'theme' => $form->settings['theme']['template'],
			'bg_color' => $bg_color,
			'type' => $form->type,
			'product' => __( 'WP Feedback Survey & Quiz Manager Pro', 'ipt_fsqm' ),
			'version' => IPT_FSQM_Loader::$version,
		);
		$permalink = self::standalone_permalink_parts( $form_id );

		ob_start();
		?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<title><?php wp_title( '|', true ); ?></title>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<!-- eForm Page SEO -->
	<?php if ( '' != $form->settings['standalone']['description'] ) : ?>
		<meta name="description" content="<?php echo esc_attr( $form->settings['standalone']['description'] ); ?>" />
	<?php endif; ?>
	<link rel="canonical" href="<?php echo $permalink['url']; ?>" />
	<!-- #end eForm Page SEO -->
	<?php EForm_OpenGraph_Helper::standalone_output( $form ); ?>

	<style type="text/css">
	/* =Reset
	-------------------------------------------------------------- */

	html, body, div, span, applet, object, iframe, h1, h2, h3, h4, h5, h6, p, blockquote, pre, a, abbr, acronym, address, big, cite, code, del, dfn, em, img, ins, kbd, q, s, samp, small, strike, strong, sub, sup, tt, var, b, u, i, center, dl, dt, dd, ol, ul, li, fieldset, form, label, legend, table, caption, tbody, tfoot, thead, tr, th, td, article, aside, canvas, details, embed, figure, figcaption, footer, header, hgroup, menu, nav, output, ruby, section, summary, time, mark, audio, video {
		margin: 0;
		padding: 0;
		border: 0;
		font-size: 100%;
		vertical-align: baseline;
	}
	body {
		line-height: 1;
	}
	html body:before,
	html body:after {
		display: none;
	}
	ol,
	ul {
		list-style: none;
	}
	blockquote,
	q {
		quotes: none;
	}
	blockquote:before,
	blockquote:after,
	q:before,
	q:after {
		content: '';
		content: none;
	}
	table {
		border-collapse: collapse;
		border-spacing: 0;
	}
	caption,
	th,
	td {
		font-weight: normal;
		text-align: left;
	}
	h1,
	h2,
	h3,
	h4,
	h5,
	h6 {
		clear: both;
	}
	html {
		overflow-y: auto;
		font-size: 100%;
		-webkit-text-size-adjust: 100%;
		-ms-text-size-adjust: 100%;
		margin-top: 0 !important;
	}
	a:focus {
		outline: thin dotted;
	}
	article,
	aside,
	details,
	figcaption,
	figure,
	footer,
	header,
	hgroup,
	nav,
	section {
		display: block;
	}
	audio,
	canvas,
	video {
		display: inline-block;
	}
	audio:not([controls]) {
		display: none;
	}
	del {
		color: #333;
	}
	ins {
		background: #fff9c0;
		text-decoration: none;
	}
	hr {
		background-color: #ccc;
		border: 0;
		height: 1px;
		margin: 24px;
		margin-bottom: 1.714285714rem;
	}
	sub,
	sup {
		font-size: 75%;
		line-height: 0;
		position: relative;
		vertical-align: baseline;
	}
	sup {
		top: -0.5em;
	}
	sub {
		bottom: -0.25em;
	}
	small {
		font-size: smaller;
	}
	img {
		border: 0;
		-ms-interpolation-mode: bicubic;
		max-width: 100%;
		height: auto;
	}
	h1, h2, h3, h4, h5, h6, p, ul, ol {
		line-height: 1.3;
		margin: 0 0 20px 0;
	}
	h1, h2, h3, h4, h5, h6 {
		font-family: 'Roboto', 'Arial Narrow', sans-serif;
		font-weight: normal;
		font-style: normal;
	}
	h1 {
		font-size: 2em;
	}
	h2 {
		font-size: 1.8em;
	}
	h3 {
		font-size: 1.6em;
	}
	h4 {
		font-size: 1.4em;
	}
	h5 {
		font-size: 1.2em;
	}
	h6 {
		font-size: 1em;
	}
	ul {
		list-style-type: disc;
		list-style-position: inside;
	}
	ol {
		list-style-type: decimal;
		list-style-position: inside;
	}
	</style>

	<?php echo $ipt_fsqm_settings['standalone']['head']; ?>
	<?php
	// wp_head();
	wp_print_styles();
	wp_print_scripts();
	?>

	<style type="text/css">
	body {
		background-color: #fff;
		background-image: none;
		font-family: 'Roboto', Tahoma, Geneva, sans-serif;
		font-weight: normal;
		font-style: normal;
		font-size: 12px;
		color: #333;
		min-width: 200px;
	}
	#fsqm_form {
		padding: <?php echo isset( $_GET['iframe_embed'] ) && $_GET['iframe_embed'] === '1' ? '4px' : '20px'; ?>;
		margin: 0 auto;
		width: auto;
		-webkit-box-sizing: border-box;
		-moz-box-sizing: border-box;
		box-sizing: border-box;
	}
	#eform-inner {
		max-width: 1200px;
		margin: 0 auto;
	}
	body {
		background-color: <?php echo ( $bg_color == 'transparent' ? 'transparent' : '#' . $bg_color ); ?>;
	}

	/* Fix iframe scroll on iOS */
	<?php if ( ! isset( $_GET['iframe_embed'] ) || $_GET['iframe_embed'] !== '1' ) : ?>
		.eform-ios.is-iframe #eform-form-wrapper {
			width: 1px;
			min-width: 100%;
			overflow: scroll;
		}
		html.eform-ios.is-iframe,
		html.eform-ios.is-iframe body {
			height: 100%;
			overflow: auto;
			-webkit-overflow-scrolling: touch;
		}
	<?php endif; ?>
	</style>
	<?php echo $base_css; ?>
	<?php echo $form_css; ?>
	<?php if ( isset( $form->settings['standalone']['head_html'] ) && '' !== $form->settings['standalone']['head_html'] ) : ?>
		<?php echo $form->settings['standalone']['head_html'];  ?>
	<?php endif; ?>
</head>
<body <?php body_class( 'ipt_uif_common' ); ?>>
	<div id="eform-form-wrapper">
		<?php echo do_shortcode( wpautop( $ipt_fsqm_settings['standalone']['before'] ) ); ?>
		<?php if ( isset( $form->settings['standalone']['before_body_html'] ) && '' !== $form->settings['standalone']['before_body_html'] ) : ?>
			<?php echo $form->settings['standalone']['before_body_html'];  ?>
		<?php endif; ?>
		<div id="fsqm_form">
			<div id="eform-inner">
				<?php $form->show_form(); ?>
			</div>
		</div>
		<?php echo do_shortcode( wpautop( $ipt_fsqm_settings['standalone']['after'] ) ); ?>
		<?php
		$form->enqueue_iframeresizer_contentwindow();
		wp_print_styles();
		wp_print_scripts();
		?>
		<!-- Fix for #wpadminbar -->
		<style type="text/css">
			html {
				margin-top: 0 !important;
			}
		</style>
		<script type="text/javascript">
			// A JS API which would trigger an event to top frame
			// So that parents can easily hook into and also add compatibility code
			// so that iframes can work inside iOS
			jQuery(document).ready(function($) {
				$('html').removeClass('no-js');
				if (navigator && navigator.userAgent && navigator.userAgent.match(/(iPod|iPhone|iPad)/)) {
					$('html').addClass('eform-ios');
				}
				var isIframe = false;
				try {
					if (window.self !== window.top) {
						isIframe = true;
					}
				} catch(e) {
					isIframe = true;
				}
				if (isIframe) {
					$('html').addClass('is-iframe');
				}

				var triggerObj = <?php echo json_encode( (object) $js_api ); ?>,
				w = window;
				triggerObj.bg_color = $('body').css('background-color');
				// fire first event on document load
				if ( w.frameElement != null ) {
					try {
						if ( typeof( w.parent.jQuery ) !== "undefined" ) {
							w.parent.jQuery(w.parent.document).trigger( 'fsqm.ready', [triggerObj] );
							w.parent.jQuery(w.parent.document).trigger( 'ipt.ready', [triggerObj] );
						}
					} catch ( e ) {

					}
				}

				// fire second on window load
				$(w).on( 'load', function() {
					if ( w.frameElement != null ) {
						try {
							if ( typeof( w.parent.jQuery ) !== "undefined" ) {
								w.parent.jQuery(w.parent.document).trigger( 'fsqm.loaded', [triggerObj] );
								w.parent.jQuery(w.parent.document).trigger( 'ipt.loaded', [triggerObj] );
							}
						} catch ( e ) {

						}
					}
				} );
			});
		</script>
		<?php if ( isset( $form->settings['standalone']['after_body_html'] ) && '' !== $form->settings['standalone']['after_body_html'] ) : ?>
			<?php echo $form->settings['standalone']['after_body_html'];  ?>
		<?php endif; ?>
	</div>
</body>
</html>
		<?php
		$form_output = ob_get_clean();
		if ( WP_DEBUG !== true ) {
			$form_output = IPT_FSQM_Minify_HTML::minify( $form_output );
		}
		echo $form_output;
		die();
	}

	public static function email_rewrite_init() {
		add_action( 'init', array( __CLASS__, 'email_rewrite' ) );
		add_action( 'template_redirect', array( __CLASS__, 'email_output' ) );
	}

	public static function email_rewrite() {
		global $wp;
		$wp->add_query_var( 'ipt_eform_email' );
		$reg_ex = '^eform-email';
		$redirect = 'index.php?ipt_eform_email=true';
		add_rewrite_rule( $reg_ex, $redirect, 'top' );

		// Flush the rewrite rule if necessary
		// Expected is for plugin update or new installation
		if ( get_option( 'ipt_fsqm_flush_rewrite', false ) ) {
			flush_rewrite_rules( true );
			update_option( 'ipt_fsqm_flush_rewrite', false );
		}
	}

	public static function email_output() {
		$eform_email = get_query_var( 'ipt_eform_email' );
		if ( '' == $eform_email ) {
			return;
		}
		// Decrypt the id
		$eform_data = @$_REQUEST['email_id'];
		if ( ! $eform_data ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			return;
		}
		// Check the ID
		$data_id = ( int ) self::decode_trackback_id( $eform_data );
		if ( ! $data_id ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			return;
		}
		// Check if ID exists
		$data = new IPT_FSQM_Form_ELements_Data( $data_id );
		if ( null == $data->data_id ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			return;
		}

		// Add the canonical filters
		add_filter( 'aioseop_canonical_url', array( __CLASS__, 'email_canonical' ) );
		add_filter( 'wpseo_canonical', array( __CLASS__, 'email_canonical' ) );

		// Add the title filter
		add_filter( 'wp_title', array( __CLASS__, 'email_title' ), 20, 2 );
		add_filter( 'wpseo_title', array( __CLASS__, 'email_title' ), 10, 2 );
		add_filter( 'aioseop_title_single', array( __CLASS__, 'email_title' ), 10, 2 );
		add_filter( 'aioseop_title_page', array( __CLASS__, 'email_title' ), 10, 2 );

		// Output the email
		$html = $data->get_email_formatted_html( $data->user_notification_email( '', $data->get_submission_lock_status(), $data->settings['payment']['lock_message'] ), $data->user_email_format( $data->settings['user']['notification_sub'] ) );
		echo $html;

		die();
	}

	public static function email_canonical() {
		return home_url( '/' ) . 'eform-email/';
	}

	public static function email_title( $title, $sep = '|' ) {
		global $wpdb, $ipt_fsqm_info;
		$eform_data = @$_REQUEST['email_id'];
		$data_id = self::decode_trackback_id( $eform_data );
		$form = $wpdb->get_var( $wpdb->prepare( "SELECT f.name FROM {$ipt_fsqm_info['form_table']} f LEFT JOIN {$ipt_fsqm_info['data_table']} d ON d.form_id = f.id WHERE d.id = %d", $data_id ) );
		return sprintf( '%1$s %2$s %3$s', $form, $sep, get_bloginfo( 'name' ) );
	}

	public static function payment_email_rewrite_init() {
		add_action( 'init', array( __CLASS__, 'payment_email_rewrite' ) );
		add_action( 'template_redirect', array( __CLASS__, 'payment_email_output' ) );
	}

	public static function payment_email_rewrite() {
		global $wp;
		$wp->add_query_var( 'ipt_eform_payment_email' );
		$reg_ex = '^eform-payment';
		$redirect = 'index.php?ipt_eform_payment_email=true';
		add_rewrite_rule( $reg_ex, $redirect, 'top' );

		// Flush the rewrite rule if necessary
		// Expected is for plugin update or new installation
		if ( get_option( 'ipt_fsqm_flush_rewrite', false ) ) {
			flush_rewrite_rules( true );
			update_option( 'ipt_fsqm_flush_rewrite', false );
		}
	}

	public static function payment_email_output() {
		$eform_email = get_query_var( 'ipt_eform_payment_email' );
		if ( '' == $eform_email ) {
			return;
		}
		// Decrypt the id
		$eform_data = @$_REQUEST['payment_id'];
		if ( ! $eform_data ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			return;
		}
		// Check the ID
		$data_id = ( int ) self::decode_trackback_id( $eform_data );
		if ( ! $data_id ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			return;
		}
		// Check if ID exists
		$data = new IPT_FSQM_Form_ELements_Data( $data_id );
		if ( null == $data->data_id ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			return;
		}

		// Add the canonical filters
		add_filter( 'aioseop_canonical_url', array( __CLASS__, 'payment_email_canonical' ) );
		add_filter( 'wpseo_canonical', array( __CLASS__, 'payment_email_canonical' ) );

		// Add the title filter
		add_filter( 'wp_title', array( __CLASS__, 'payment_email_title' ), 20, 2 );
		add_filter( 'wpseo_title', array( __CLASS__, 'payment_email_title' ), 10, 2 );
		add_filter( 'aioseop_title_single', array( __CLASS__, 'payment_email_title' ), 10, 2 );
		add_filter( 'aioseop_title_page', array( __CLASS__, 'payment_email_title' ), 10, 2 );

		// Now prepare variables
		$retry = (boolean) @$_REQUEST['retry'];
		$mode = (string) @$_REQUEST['mode'];
		$success = (boolean) @$_REQUEST['success'];
		$cancelled = (boolean) @$_REQUEST['cancelled'];
		$invoice = (string) @$_REQUEST['invoice'];

		// Set the title and message
		if ( $retry ) {
			$title = $data->settings['payment']['retry_uemail_sub'];
			$msg = $data->settings['payment']['retry_uemail_msg'];
		} else {
			$title = $data->settings['payment']['success_sub'];
			$msg = $data->settings['payment']['success_msg'];
		}
		// In case of paypal express mode
		if ( 'paypal_e' == $mode ) {
			$title = $data->settings['payment']['paypal']['conf_sub'];
			$msg = $data->settings['payment']['paypal']['conf_msg'];
		}
		// But in case of error, just override
		if ( false == $success ) {
			$title = $data->settings['payment']['error_sub'];
			$msg = $data->settings['payment']['error_msg'];
			// If in case of cancellation
			if ( true == $cancelled ) {
				$title = $data->settings['payment']['cancel_sub'];
				$msg = $data->settings['payment']['cancel_msg'];
			}
		}

		// Now format everything
		// Get format string
		$format_string_components = $data->get_format_string();
		$title = str_replace( array_keys( $format_string_components ), array_values( $format_string_components ), $title );
		$title = sprintf( $title, $invoice );
		$msg = str_replace( array_keys( $format_string_components ), array_values( $format_string_components ), $msg );

		// Output the email
		$data->format_email_style();
		$html = $data->get_email_formatted_html( $data->user_payment_email( $msg, array(
			'retry' => $retry,
			'mode' => $mode,
			'success' => $success,
			'cancelled' => $cancelled,
			'invoice' => $invoice,
		) ), $title );
		echo $html;

		die();
	}

	public static function payment_email_canonical() {
		return home_url( '/' ) . 'eform-payment/';
	}

	public static function payment_email_title( $title, $sep = '|' ) {
		global $wpdb, $ipt_fsqm_info;
		$eform_data = @$_REQUEST['payment_id'];
		$data_id = self::decode_trackback_id( $eform_data );
		$form = $wpdb->get_var( $wpdb->prepare( "SELECT f.name FROM {$ipt_fsqm_info['form_table']} f LEFT JOIN {$ipt_fsqm_info['data_table']} d ON d.form_id = f.id WHERE d.id = %d", $data_id ) );
		return sprintf( _x( 'Payment: %1$s %2$s %3$s', 'eform-payment-email-page-title', 'ipt_fsqm' ), $form, $sep, get_bloginfo( 'name' ) );
	}

	public static function user_portal_init() {
		// Just for the logged in users, so no need nopriv
		add_action( 'wp_ajax_ipt_fsqm_user_portal', array( __CLASS__, 'user_portal_ajax_response' ) );
	}

	public static function user_portal_ajax_response() {
		global $wpdb, $ipt_fsqm_info;
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

		// Get post variables
		$settings = isset( $_POST['settings'] ) ? (array) $_POST['settings'] : array();
		$nonce = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';
		$user = wp_get_current_user();
		$doing = isset( $_POST['doing'] ) ? (int) $_POST['doing'] : 0;

		// Parse the settings - Basically the shortcode atts
		$settings = wp_parse_args( $settings, array(
			'nosubmission' => __( 'No submissions yet.', 'ipt_fsqm' ),
			'login' => __( 'You need to login in order to view your submissions.', 'ipt_fsqm' ),
			'show_register' => '1',
			'show_forgot' => '1',
			'formlabel' => __( 'Form', 'ipt_fsqm' ),
			'showcategory' => '0',
			'categorylabel' => __( 'Category', 'ipt_fsqm' ),
			'datelabel' => __( 'Date', 'ipt_fsqm' ),
			'showscore' => '1',
			'scorelabel' => __( 'Score', 'ipt_fsqm' ),
			'mscorelabel' => __( 'Max', 'ipt_fsqm' ),
			'pscorelabel' => __( '%-age', 'ipt_fsqm' ),
			'showremarks' => '0',
			'remarkslabel' => __( 'Remarks', 'ipt_fsqm' ),
			'linklabel' => __( 'View', 'ipt_fsqm' ),
			'actionlabel' => __( 'Action', 'ipt_fsqm' ),
			'editlabel' => __( 'Edit', 'ipt_fsqm' ),
			'avatar' => '96',
			'theme' => 'material-default',
			'title' => __( 'eForm User Portal', 'ipt_fsqm' ),
		) );

		// Check for authenticity
		if ( ! is_user_logged_in() || ! ( $user instanceof WP_User ) ) {
			echo json_encode( array(
				'success' => false,
				'error_msg' => __( 'You need to be logged in', 'ipt_fsqm' ),
			) );
			die();
		}

		if ( ! wp_verify_nonce( $nonce, 'ipt_fsqm_up_nonce_' . $user->ID ) ) {
			echo json_encode( array(
				'success' => false,
				'error_msg' => __( 'Invalid Nonce. Cheating?', 'ipt_fsqm' ),
			) );
			die();
		}

		// Prepare the return
		$return = array(
			'success' => true,
			'html' => '',
			'done' => 0,
		);

		// Prepare the db variables
		$total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} WHERE user_id = %d", $user->ID ) );
		$per_page = 100;
		$start_page = $doing * $per_page;
		$data_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$ipt_fsqm_info['data_table']} WHERE user_id = %d ORDER BY date DESC LIMIT %d, %d", $user->ID, $start_page, $per_page ) );
		$data = new IPT_FSQM_Form_Elements_Front();

		// Prepare the UI
		$ui = $data->ui;

		// Check for empty
		if ( empty( $data_ids ) ) {
			$return['html'] .= '';
			$return['done'] = 100;
			echo json_encode( $return );
			die();
		}

		// Loop through and add the html
		foreach ( $data_ids as $id ) {
			$data->init( $id );
			$action_buttons = array();
			$action_buttons[] = array(
				$settings['linklabel'],
				'',
				'medium',
				'secondary',
				'normal',
				array( 'ipt_fsqm_up_tb' ),
				'anchor',
				array(),
				array(),
				$data->get_trackback_url(),
				'newspaper',
			);
			if ( $data->can_user_edit() ) {
				$action_buttons[] = array(
					$settings['editlabel'],
					'',
					'medium',
					'secondary',
					'normal',
					array( 'ipt_fsqm_up_edit' ),
					'anchor',
					array(),
					array(),
					$data->get_edit_url(),
					'pencil',
				);
			}
			$action_buttons = apply_filters( 'ipt_fsqm_up_filter_action_button', $action_buttons, $data );

			$return['html'] .= '<tr>';
			$return['html'] .= '<td class="form_label" data-search="' . esc_attr( $data->name ) . '">' . '<span class="data-id">' . $data->data_id . '</span> ' . $data->name . '</td>';
			$return['html'] .= '<td class="date_label" data-search="' . strtotime( $data->data->date ) . '" data-order="' . strtotime( $data->data->date ) . '">' . date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $data->data->date ) ) . '</td>';
			if ( $settings['showcategory'] == '1' ) {
				$category = IPT_FSQM_Form_Elements_Static::get_category( $data->category );
				if ( $category != null ) {
					$return['html'] .= '<td class="category_label" data-search="' . esc_attr( $category->name ) .'">' . $category->name . '</td>';
				} else {
					$return['html'] .= '<td class="category_label" data-search="' . __( 'None', 'ipt_fsqm' ) . '">' . __( 'None', 'ipt_fsqm' ) . '</td>';
				}

			}
			if ( $settings['showscore'] == '1' ) {
				$return['html'] .= '<td class="score_label ipt_fsqm_up_number" data-score="' . $data->data->score . '">' . number_format_i18n( $data->data->score, 2 ) . '</td>';
				$return['html'] .= '<td class="mscore_label ipt_fsqm_up_number" data-max-score="' . $data->data->max_score . '">' . number_format_i18n( $data->data->max_score, 2 ) . '</td>';
				$percentage = 0;
				if ( $data->data->max_score != 0 ) {
					$percentage = $data->data->score * 100 / $data->data->max_score;
				}
				$return['html'] .= '<td class="pscore_label ipt_fsqm_up_number" data-percentage="' . round( $percentage, 2 ) . '">' . number_format_i18n( $percentage, 2 ) . _x( '%', 'fsqm.UP.Percentage', 'ipt_fsqm' ) . '</td>';
			}
			if ( '1' == $settings['showremarks'] ) {
				$return['html'] .= '<td class="admin_remarks">' . $data->data->comment . '</td>';
			}
			ob_start();
			$ui->buttons( $action_buttons );
			$buttons = ob_get_clean();
			$return['html'] .= '<td class="action_label">' . $buttons . '</td>';
			$return['html'] .= '</tr>';
		}

		$done_till_now = $doing * $per_page + $per_page;
		if ( $done_till_now >= $total ) {
			$return['done'] = 100;
		} else {
			$return['done'] = (float) $done_till_now * 100 / $total;
		}

		echo json_encode( $return );
		die();
	}

	/*==========================================================================
	 * Quick Preview
	 *========================================================================*/
	public static function ipt_fsqm_quick_preview() {
		add_action( 'wp_ajax_ipt_fsqm_quick_preview', array( __CLASS__, 'ipt_fsqm_quick_preview_cb' ) );
	}

	public static function ipt_fsqm_quick_preview_cb() {
		$data_id = $_REQUEST['id'];
		$preview = new IPT_FSQM_Form_Elements_Data( $data_id );
		if ( $preview->data_id == null ) {
			echo 'Invalid ID';
			die();
		}
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$('#ipt_fsqm_quick_preview_print').click(function(){
		$('#ipt_fsqm_quick_preview').printElement({
			leaveOpen:true,
			printMode:'popup'
		});
	});
});
</script>
<div style="text-align: center; margin: 10px">
	<a id="ipt_fsqm_quick_preview_print" class="button-primary"><?php _e( 'Print', 'ipt_fsqm' ); ?></a>
</div>
<div id="ipt_fsqm_quick_preview">
	<?php $preview->show_quick_preview( false, true, false, true ); ?>
</div>
		<?php
		die();
	}

	/*==========================================================================
	 * TrackBack and Admin Preview
	 *========================================================================*/
	public static function ipt_fsqm_full_preview_cb( $form, $score ) {
		// No need to show if not admin and submission locked
		if ( ! is_admin() && $form->get_submission_lock_status() ) {
			$callback = array( array( $form->ui, 'msg_error' ) );
			$format_string_components = $form->get_format_string();
			$callback[1] = array(
				0 => str_replace( array_keys( $format_string_components ), array_values( $format_string_components ), wpautop( $form->settings['payment']['lock_message'] ) ),
				1 => true,
				2 => __( 'Please complete the payment', 'ipt_fsqm' ),
			);
			$form->container( $callback, false );
			return;
		}

		// Proceed as usual
		$buttons = array();
		if ( true == $form->settings['trackback']['show_print'] ) {
			$buttons[] = array(
				__( 'Print', 'ipt_fsqm' ),
				'ipt_fsqm_report_print_' . $form->form_id,
				'medium',
				'secondary',
				'normal',
				array( 'ipt_uif_printelement' ),
				'button',
				array( 'printid' => 'ipt_fsqm_score_data_' . $form->data_id ),
				array(),
				'',
				'print',
			);
		}

		if ( $form->can_user_edit() && ! is_admin() ) {
			$buttons[] = array(
				__( 'Edit', 'ipt_fsqm' ),
				'ipt_fsqm_report_print_' . $form->form_id,
				'medium',
				'secondary',
				'normal',
				array( 'ipt_uif_printelement' ),
				'button',
				array(),
				array( 'onclick' => 'javascript:window.location.href="' . $form->get_edit_url() . '"' ),
				'',
				'pencil',
			);
		}
		$buttons = apply_filters( 'ipt_fsqm_filter_static_report_print', $buttons, $form, $score );
?>
<div class="ipt_uif_front ipt_uif_common">
	<div class="ipt_uif_mother_wrap ui-widget-content ui-widget-default">
		<?php if ( $form->settings['trackback']['show_full'] == true ) : ?>
		<div id="ipt_fsqm_submission_data_<?php echo $form->data_id; ?>" class="ipt_uif_column ipt_uif_column_full">
			<div class="ipt_uif_column ipt_uif_column_full ipt_fsqm_main_heading_column ipt_fsqm_full_preview_sb">
				<div class="ipt_uif_column_inner">
					<?php $form->ui->heading( $form->settings['trackback']['full_title'], 'h2', 'left', 0xe020, false, false, array( 'ipt_fsqm_main_heading' ) ); ?>
				</div>
			</div>
			<?php $form->ui->clear(); ?>
			<?php $form->show_form( false, false, 0, false ); ?>
		</div>
		<?php endif; ?>

		<?php $form->ui->clear(); ?>

		<?php if ( $score->settings['social']['show'] == true ) : ?>
		<div class="ipt_fsqm_social_share" style="margin: 10px auto;">
			<?php echo $score->social_share_buttons( false ); ?>
		</div>
		<?php $form->ui->clear(); ?>
		<?php endif; ?>

		<?php if ( $form->settings['trackback']['show_print'] == true ) : ?>
		<div class="ipt_uif_column ipt_uif_column_full ipt_fsqm_main_heading_column ipt_fsqm_full_preview_print">
			<div class="ipt_uif_column_inner">
				<?php $form->ui->heading( $form->settings['trackback']['print_title'], 'h2', 'left', 0xe08a, false, false, array( 'ipt_fsqm_main_heading' ) ); ?>
			</div>
		</div>
		<?php $form->ui->clear(); ?>

		<div id="ipt_fsqm_score_data_<?php echo $form->data_id; ?>" class="ipt_uif_column ipt_uif_column_full ipt_fsqm_trackback_full_preview">
			<?php $score->show_quick_preview( false, true, false, is_admin() ); ?>
		</div>

		<?php $form->ui->clear(); ?>
		<?php endif; ?>

		<?php
		if ( $form->settings['trackback']['show_full'] == false && $form->settings['trackback']['show_print'] == false ) {
			if ( current_user_can( 'manage_feedback' ) ) {
				echo '<p>' . __( 'Both Full Preview and Print & Summary are hidden. Please show at least one of them.', 'ipt_fsqm' ) . '</p>';
			} else {
				echo '<p>' . __( 'Sorry, the information you are looking for, is not available right now.', 'ipt_fsqm' ) . '</p>';
			}
		}
		?>

		<?php
		if ( ! empty( $buttons ) ) {
			$form->ui->buttons( $buttons, '', 'align-center' );
		}
		?>
		<?php $form->ui->clear(); ?>

		<?php if ( $form->settings['trackback']['show_trends'] == true ) : ?>
		<div class="ipt_uif_column ipt_uif_column_full ipt_fsqm_main_heading_column ipt_fsqm_full_preview_print" style="margin-top: 20px; margin-bottom: 20px;">
			<div class="ipt_uif_column_inner">
				<?php $form->ui->heading( $form->settings['trackback']['trends_title'], 'h2', 'left', 0xe0ba, false, false, array( 'ipt_fsqm_main_heading' ) ); ?>
			</div>
		</div>
		<?php $form->ui->clear(); ?>
		<div class="ipt_fsqm_tb_trends">
			<?php echo do_shortcode( '[ipt_fsqm_trends form_id="' . $form->form_id . '" mcq_ids="all" data=\'{"data":false,"others":false,"names":false,"date":false}\' appearance=\'{"block":false,"heading":true,"description":false,"header":false,"border":false,"material":false,"print":false}\']' ); ?>
			<?php $form->ui->clear(); ?>
		</div>
		<?php endif; ?>
	</div>
	<?php $form->ui->clear(); ?>
</div>
<?php if ( $form->settings['trackback']['show_trends'] == true ) : ?>

<?php endif; ?>
		<?php
	}

	public static function ipt_fsqm_full_preview( $id ) {
		$form = new IPT_FSQM_Form_Elements_Front( $id );
		if ( $form->form_id == null ) {
			$param = array( __( 'The ID you have provided is either invalid or has been deleted. Please go back and try again.', 'ipt_fsqm' ), true, __( 'Invalid ID', 'ipt_fsqm' ) );
			$form->container( array( array( $form->ui, 'msg_error' ), $param ), true );
			return;
		}
		$score = new IPT_FSQM_Form_Elements_Data( $id );
		// $form->settings['type_specific']['normal']['wrapper'] = false;
		$form->type = 0;
		$form->container( array( array( __CLASS__, 'ipt_fsqm_full_preview_cb' ), array( $form, $score ) ), true, true, 'ipt-eform-fullview' );
	}

	public static function ipt_fsqm_form_edit( $id ) {
		$form = new IPT_FSQM_Form_Elements_Front( $id );
		if ( $form->form_id == null ) {
			$param = array( __( 'The ID you have provided is either invalid or has been deleted. Please go back and try again.', 'ipt_fsqm' ), true, __( 'Invalid ID', 'ipt_fsqm' ) );
			$form->container( array( array( $form->ui, 'msg_error' ), $param ), true );
			return;
		}
		if ( $form->can_user_edit() == false ) {
			$param = array( __( 'Invalid request. You can not edit this submission. If you were expecting this, then please contact the administrator of this website.', 'ipt_fsqm' ), true, __( 'Error', 'ipt_fsqm' ) );
			$form->container( array( array( $form->ui, 'msg_error' ), $param ), true );
			return;
		}
		$form->show_form( true, false, null, true, true );
	}

	/**
	 * Backward compatibility for easySubmission Add-on which expects this
	 * method within Static class
	 *
	 * @deprecated
	 *
	 * @param      array        $args     The arguments
	 * @param      string|null  $content  The content
	 *
	 * @return     string       HTML of the form
	 */
	public static function ipt_fsqm_form_cb( $args, $content = null ) {
		return IPT_EForm_Core_Shortcodes::ipt_fsqm_form_cb( $args, $content );
	}

	/*==========================================================================
	 * Payment Related Tasks
	 *========================================================================*/
	public static function ipt_fsqm_handle_payment_tb( $id, $mode, $form ) {
		switch ( $mode ) {
			// If payment retry
			case 'retry' :
				$form->payment_retry();
				break;
			// If came from paypal express checkout
			case 'paypal_e' :
				self::ipt_fsqm_payment_pe_verify( $id, $form );
				break;
			// Hooks for others
			default :
				do_action( 'ipt_fsqm_payment_trackback', $id, $mode, $form );
				break;
		}
	}

	public static function ipt_fsqm_payment_pe_verify( $id, $form ) {
		global $ipt_fsqm_info, $wpdb;

		// Get the data from payment table
		$payment_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_info['payment_table']} WHERE data_id = %d", $id ) );

		// Throw an error if no data was found
		if ( is_null( $payment_data ) ) {
			$form->ui->msg_error( __( 'Could not find payment information in the database. Please contact administrator.', 'ipt_fsqm' ), true, __( 'An error has occured', 'ipt_fsqm' ) );
			return;
		}

		// Check if it is a retry
		$retry = false;

		if ( 0 != $payment_data->status ) {
			$retry = true;
		}

		$invoiceid = str_replace( '{id}', $payment_data->id, $form->settings['payment']['invoicenumber'] );
		// Create a fakish payment status
		// for passing to the email function
		$payment_status = array(
			'needed' => true,
			'success' => false,
			'redirect_url' => false,
			'invoice' => $invoiceid,
		);
		$cancelled = false;

		// Data was found, now process it
		$status = $_GET['psuccess'];
		// If it was a success
		if ( 'true' == $status ) {
			// Probably Successful Payment, so let's do things
			$payment_id = @$_GET['paymentId'];
			$payer_id = @$_GET['PayerID'];
			// Get the API Context
			$paypal = EForm_Payment_Handler_PayPal::instance();
			$form->set_paypal_api_context( $paypal );
			$result = $paypal->execute_express_checkout( $payment_data, $payment_id, $payer_id );
			// If it was successful
			if ( is_object( $result ) && $result->getState() == 'approved' ) {
				$payment_status['success'] = true;
				// Prepare the new db data
				$pdata_update = array(
					'status' => 1, // Approved and processed payment
					'txn' => $result->getId(),
					'meta' => maybe_serialize( array(
						'create_time' => $result->getCreateTime(),
						'update_time' => $result->getUpdateTime(),
						'intent' => $result->getIntent(),
					) ),
				);
				$wpdb->update( $ipt_fsqm_info['payment_table'], $pdata_update, array(
					'id' => $payment_data->id,
				), array(
					'%d', '%s', '%s',
				), '%d' );
				// Update the paid flag
				$form->set_paid_status( 1 );
				// Process the success message
				// This would resolve #305
				// @link https://iptlabz.com/ipanelthemes/wp-fsqm-pro/issues/305
				$payment_success_msg = $form->settings['payment']['success_msg'];
				$format_strings = $form->get_format_string();
				$payment_success_msg = str_replace( array_keys( $format_strings ), array_values( $format_strings ), $payment_success_msg );
				$form->ui->msg_okay( $payment_success_msg, true, __( 'Payment received.', 'ipt_fsqm' ) );
				$form->ui->container( array( array( $form, 'get_transaction_status' ), array( false, true ) ), __( 'Transaction Status', 'ipt_fsqm' ), 'paypal' );
			} else {
				// An error has occured
				// Show retry
				if ( $payment_data->status != 1 ) {
					$wpdb->update( $ipt_fsqm_info['payment_table'], array(
						'status' => 3, // 3 - Paypal unapproved/error
					), array(
						'id' => $payment_data->id,
					), '%d', '%d' );
				}
				$form->ui->msg_error( $form->settings['payment']['error_msg'], true, __( 'Payment Processing Error', 'ipt_fsqm' ) );
				$form->payment_retry();
			}
		} else {
			$cancelled = true;
			// Just show a message and the resubmit form
			// Also update the status to cancelled
			if ( $payment_data->status != 1 ) {
				$wpdb->update( $ipt_fsqm_info['payment_table'], array(
					'status' => 2, // User cancelled
				), array(
					'id' => $payment_data->id,
				), '%d', '%d' );
			}
			$form->ui->msg_error( $form->settings['payment']['cancel_msg'], true, __( 'Payment Processing Error', 'ipt_fsqm' ) );
			$form->payment_retry();
		}
		// Send the email
		$data = new IPT_FSQM_Form_Elements_Data( $form->data_id );
		$data->send_payment_email( $payment_status, false, $cancelled, 'paypal_e' );
		// Send the notification email
		$data->send_user_notification_email();
		// Send payment admin email
		// Also pass in the retry
		// Resolves @link {https://iptlabz.com/ipanelthemes/wp-fsqm-pro/issues/286#reported-bugs}
		$data->send_payment_admin_email( $payment_status, '', $retry );
	}

	public static function ipt_fsqm_get_payment_gateways() {
		$gateways = array(
			'paypal_d' => __( 'Direct Payout from PayPal', 'ipt_fsqm' ),
			'paypal_e' => __( 'PayPal Express Checkout', 'ipt_fsqm' ),
			'stripe' => __( 'Direct Payout from Stripe', 'ipt_fsqm' ),
			'authorizenet' => __( 'Direct Payout from authorize.net', 'ipt_fsqm' ),
			'offline' => __( 'Offline Payment', 'ipt_fsqm' ),
		);
		return apply_filters( 'ipt_fsqm_payment_gateways', $gateways );
	}

	public static function ipt_fsqm_get_payment_status() {
		$payment_status = array(
			0 => __( 'Unpaid', 'ipt_fsqm' ),
			1 => __( 'Paid', 'ipt_fsqm' ),
			2 => __( 'Cancelled', 'ipt_fsqm' ),
			3 => __( 'Unsuccessful', 'ipt_fsqm' ),
			4 => __( 'Awaiting', 'ipt_fsqm' ),
		);
		return apply_filters( 'ipt_fsqm_payment_status', $payment_status );
	}

	public static function ipt_fsqm_sync_payments( $data_id ) {
		global $wpdb, $ipt_fsqm_info;
		$delete_ids = implode( ',', (array) $data_id );
		$wpdb->query( "DELETE FROM {$ipt_fsqm_info['payment_table']} WHERE data_id IN ({$delete_ids})" );
	}

	public static function payment_sync_init() {
		add_action( 'ipt_fsqm_submissions_deleted', array( __CLASS__, 'ipt_fsqm_sync_payments' ), 10, 1 );
	}



	/*==========================================================================
	 * Save a data
	 *========================================================================*/
	public static function ipt_fsqm_save_form() {
		add_action( 'wp_ajax_ipt_fsqm_save_form', array( __CLASS__, 'ipt_fsqm_save_form_cb' ) );
		add_action( 'wp_ajax_nopriv_ipt_fsqm_save_form', array( __CLASS__, 'ipt_fsqm_save_form_cb' ) );

		add_action( 'wp_ajax_ipt_fsqm_refresh_nonce', array( __CLASS__, 'ipt_fsqm_form_refresh_nonce' ) );
		add_action( 'wp_ajax_nopriv_ipt_fsqm_refresh_nonce', array( __CLASS__, 'ipt_fsqm_form_refresh_nonce' ) );

		add_action( 'wp_ajax_ipt_fsqm_retry_payment', array( __CLASS__, 'ipt_fsqm_payment_retry_cb' ) );
		add_action( 'wp_ajax_nopriv_ipt_fsqm_retry_payment', array( __CLASS__, 'ipt_fsqm_payment_retry_cb' ) );

		add_action( 'wp_ajax_ipt_fsqm_instant_subscription', array( __CLASS__, 'ipt_fsqm_instant_subscription_cb' ) );
		add_action( 'wp_ajax_nopriv_ipt_fsqm_instant_subscription', array( __CLASS__, 'ipt_fsqm_instant_subscription_cb' ) );
	}

	public static function ipt_fsqm_instant_subscription_cb() {
		$form_id = isset( $_POST['form_id'] ) ? (int) $_POST['form_id'] : 0;
		$nonce = isset( $_POST['_nonce'] ) ? $_POST['_nonce'] : '';

		$return = [
			'success' => false,
			'msg' => '',
		];

		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

		// first verify the nonce
		if ( ! wp_verify_nonce( $nonce, 'ipt_fsqm_instant_subs_' . $form_id ) ) {
			$return['msg'] = 'Invalid Nonce';
			echo json_encode( $return );
			die();
		}

		$f_name = isset( $_POST['f_name'] ) ? wp_unslash( $_POST['f_name'] ) : '';
		$l_name = isset( $_POST['l_name'] ) ? wp_unslash( $_POST['l_name'] ) : '';
		$email = isset( $_POST['email'] ) ? wp_unslash( $_POST['email'] ) : '';
		$phone = isset( $_POST['phone'] ) ? wp_unslash( $_POST['phone'] ) : '';

		// check for data validity
		if ( mb_strlen( $f_name ) < 3 ) {
			$return['msg'] = 'Invalid First Name';
			echo json_encode( $return );
			die();
		}
		if ( mb_strlen( $l_name ) < 3 ) {
			$return['msg'] = 'Invalid Last Name';
			echo json_encode( $return );
			die();
		}
		if ( ! is_email( $email ) ) {
			$return['msg'] = 'Invalid Email';
			echo json_encode( $return );
			die();
		}

		$data = new IPT_FSQM_Form_Elements_Data( null, $form_id );
		// check if form exists
		if ( $data->form_id === null ) {
			$return['msg'] = 'Invalid Request';
			echo json_encode( $return );
			die();
		}

		$data->data->f_name = $f_name;
		$data->data->l_name = $l_name;
		$data->data->email = $email;
		$data->data->phone = $phone;

		$data->do_integrations( true );

		$return['success'] = true;
		echo json_encode( $return );
		die();
	}

	public static function ipt_fsqm_form_refresh_nonce() {
		global $wpdb, $ipt_fsqm_info;
		$form_id = isset( $_POST['form_id'] ) ? (int) $_POST['form_id'] : null;
		$data_id = isset( $_POST['data_id'] ) ? (int) $_POST['data_id'] : null;
		$return = array(
			'success' => false,
			'save_nonce' => '',
			'edit_nonce' => '',
		);

		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		$admin_update = false;
		$user_update = false;

		// if data id present then this can be either from admin or user
		$user_edit = false;
		if ( $data_id !== null ) {
			$user_edit = isset( $_POST['user_edit'] ) && $_POST['user_edit'] == '1' ? true : false;
		}

		// if from user then check the nonce
		if ( $user_edit ) {
			$user_update = true;
		} else {
			// Maybe Admin request
			// Check for user capability
			if ( $data_id !== null && ( !is_admin() || !current_user_can( 'manage_feedback' ) ) ) {
				$return = array(
					'success' => false,
					'errors' => array(
						0 => array(
							'id' => '',
							'msgs' => array( __( 'Invalid request.', 'ipt_fsqm' ) ),
						),
					),
				);
				echo json_encode( (object) $return );
				die();
			}
			$admin_update = true;
		}

		//Check for validity of form_id
		$form_id_check = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$ipt_fsqm_info['form_table']} WHERE id=%d", $form_id ) );
		if ( null === $form_id_check ) {
			$return = array(
				'success' => false,
				'errors' => array(
					0 => array(
						'id' => '',
						'msgs' => array( __( 'Invalid form. Cheating?', 'ipt_fsqm' ) ),
					),
				),
			);
			echo json_encode( (object) $return );
			die();
		}
		// Check for validity of data_id
		if ( $data_id !== null ) {
			$data_id_check = $wpdb->get_var( $wpdb->prepare( "SELECT form_id FROM {$ipt_fsqm_info['data_table']} WHERE id = %d", $data_id ) );
			if ( $form_id != $data_id_check ) {
				$return = array(
					'success' => false,
					'errors' => array(
						0 => array(
							'id' => '',
							'msgs' => array( __( 'Invalid data. Cheating?', 'ipt_fsqm' ) ),
						),
					),
				);
				echo json_encode( (object) $return );
				die();
			}
		}

		// All set now instantiate and save and return
		$form_data = new IPT_FSQM_Form_Elements_Data( $data_id, $form_id );

		// But again check for the user edit capability
		if ( $user_edit && $form_data->can_user_edit() !== true ) {
			$return = array(
				'success' => false,
				'errors' => array(
					0 => array(
						'id' => '',
						'msgs' => array( __( 'Invalid request.', 'ipt_fsqm' ) ),
					),
				),
			);
			echo json_encode( (object) $return );
			die();
		}

		// At this point, everything is just fine
		$return['success'] = true;
		$return['save_nonce'] = wp_create_nonce( 'ipt_fsqm_form_data_save_' . $form_id );
		if ( $data_id !== null ) {
			$return['edit_nonce'] = wp_create_nonce( 'ipt_fsqm_user_edit_' . $data_id );
		}
		echo json_encode( (object) $return );
		die();
	}


	public static function ipt_fsqm_payment_retry_cb() {
		global $wpdb, $ipt_fsqm_info;
		$post_data = wp_unslash( $_POST );
		$post_data_raw = $_POST;
		if (  isset( $post_data['ipt_ps_send_as_str'] ) && $post_data['ipt_ps_send_as_str'] == 'true' && isset( $post_data['ipt_ps_look_into'] ) ) {
			$parse_post = array();
			IPT_FSQM_Form_Elements_Static::safe_parse_str( $post_data[$post_data['ipt_ps_look_into']], $parse_post );
			$post_data = $parse_post;
		} else if ( isset( $post_data['ipt_ps_send_as_json'] ) && $post_data['ipt_ps_send_as_json'] == 'true' && isset( $post_data['ipt_ps_look_into'] ) ) {
			$json_post = json_decode( $post_data[$post_data['ipt_ps_look_into']], true, 1024 );
			if ( json_last_error() == JSON_ERROR_SYNTAX ) {
				$json_post = json_decode( $post_data_raw[$post_data['ipt_ps_look_into']], true, 1024 );
			}
			$post_data = $json_post;
		}
		$form_id = (int) $post_data['form_id'];
		$data_id = isset( $post_data['data_id'] ) ? (int) $post_data['data_id'] : null;
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

		// Check for valid nonce
		if ( ! isset( $post_data['ipt_fsqm_form_data_payment_retry'] ) || ! wp_verify_nonce( $post_data['ipt_fsqm_form_data_payment_retry'], 'ipt_fsqm_form_data_payment_retry_' . $form_id ) ) {
			$return = array(
				'success' => false,
				'errors' => array(
					0 => array(
						'id' => '',
						'msgs' => array( __( 'Invalid nonce. Cheating?', 'ipt_fsqm' ) ),
					),
				),
			);
			echo json_encode( (object) $return );
			die();
		}

		//Check for validity of form_id
		$form_id_check = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$ipt_fsqm_info['form_table']} WHERE id=%d", $form_id ) );
		if ( null === $form_id_check ) {
			$return = array(
				'success' => false,
				'errors' => array(
					0 => array(
						'id' => '',
						'msgs' => array( __( 'Invalid form. Cheating?', 'ipt_fsqm' ) ),
					),
				),
			);
			echo json_encode( (object) $return );
			die();
		}
		// Check for validity of data_id
		$data_id_check = $wpdb->get_var( $wpdb->prepare( "SELECT form_id FROM {$ipt_fsqm_info['data_table']} WHERE id = %d", $data_id ) );
		if ( $form_id != $data_id_check ) {
			$return = array(
				'success' => false,
				'errors' => array(
					0 => array(
						'id' => '',
						'msgs' => array( __( 'Invalid data. Cheating?', 'ipt_fsqm' ) ),
					),
				),
			);
			echo json_encode( (object) $return );
			die();
		}


		// All set now instantiate and save and return
		$form_data = new IPT_FSQM_Form_Elements_Data( $data_id, $form_id );

		$return = $form_data->retry_payment();
		echo json_encode( (object) $return );
		die();
	}


	/**
	 *
	 *
	 * @global wpdb $wpdb
	 * @global array $ipt_fsqm_info
	 */
	public static function ipt_fsqm_save_form_cb() {
		global $wpdb, $ipt_fsqm_info;
		$post_data = wp_unslash( $_POST );
		$post_data_raw = $_POST;
		if (  isset( $post_data['ipt_ps_send_as_str'] ) && $post_data['ipt_ps_send_as_str'] == 'true' && isset( $post_data['ipt_ps_look_into'] ) ) {
			$parse_post = array();
			IPT_FSQM_Form_Elements_Static::safe_parse_str( $post_data[$post_data['ipt_ps_look_into']], $parse_post );
			$post_data = $parse_post;
		} else if ( isset( $post_data['ipt_ps_send_as_json'] ) && $post_data['ipt_ps_send_as_json'] == 'true' && isset( $post_data['ipt_ps_look_into'] ) ) {
			$json_post = json_decode( $post_data[$post_data['ipt_ps_look_into']], true, 1024 );
			if ( json_last_error() == JSON_ERROR_SYNTAX ) {
				$json_post = json_decode( $post_data_raw[$post_data['ipt_ps_look_into']], true, 1024 );
			}
			$post_data = $json_post;
		}
		$form_id = (int) $post_data['form_id'];
		$data_id = isset( $post_data['data_id'] ) ? (int) $post_data['data_id'] : null;
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		$admin_update = false;
		$user_update = false;

		// if data id present then this can be either from admin or user
		$user_edit = false;
		if ( $data_id !== null ) {
			$user_edit = isset( $post_data['user_edit'] ) && $post_data['user_edit'] == '1' ? true : false;
		}

		// if from user then check the nonce
		if ( $user_edit ) {
			if ( ! isset( $post_data['ipt_fsqm_user_edit_nonce'] ) || ! wp_verify_nonce( $post_data['ipt_fsqm_user_edit_nonce'], 'ipt_fsqm_user_edit_' . $data_id ) ) {
				$return = array(
					'success' => false,
					'errors' => array(
						0 => array(
							'id' => '',
							'msgs' => array( __( 'Invalid nonce. Cheating?', 'ipt_fsqm' ) ),
						),
					),
				);
				echo json_encode( (object) $return );
				die();
			}
			$user_update = true;
		} else {
			// Maybe Admin request
			// Check for user capability
			if ( $data_id !== null && ( !is_admin() || !current_user_can( 'manage_feedback' ) ) ) {
				$return = array(
					'success' => false,
					'errors' => array(
						0 => array(
							'id' => '',
							'msgs' => array( __( 'Invalid request.', 'ipt_fsqm' ) ),
						),
					),
				);
				echo json_encode( (object) $return );
				die();
			}
			$admin_update = true;
		}



		//Check for nonce
		$wpnonce = $post_data['ipt_fsqm_form_data_save'];
		if ( !wp_verify_nonce( $wpnonce, 'ipt_fsqm_form_data_save_' . $form_id ) ) {
			$return = array(
				'success' => false,
				'errors' => array(
					0 => array(
						'id' => '',
						'msgs' => array( __( 'Invalid nonce. Cheating?', 'ipt_fsqm' ) ),
					),
				),
			);
			echo json_encode( (object) $return );
			die();
		}

		//Check for validity of form_id
		$form_id_check = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$ipt_fsqm_info['form_table']} WHERE id=%d", $form_id ) );
		if ( null === $form_id_check ) {
			$return = array(
				'success' => false,
				'errors' => array(
					0 => array(
						'id' => '',
						'msgs' => array( __( 'Invalid form. Cheating?', 'ipt_fsqm' ) ),
					),
				),
			);
			echo json_encode( (object) $return );
			die();
		}
		// Check for validity of data_id
		if ( $data_id !== null ) {
			$data_id_check = $wpdb->get_var( $wpdb->prepare( "SELECT form_id FROM {$ipt_fsqm_info['data_table']} WHERE id = %d", $data_id ) );
			if ( $form_id != $data_id_check ) {
				$return = array(
					'success' => false,
					'errors' => array(
						0 => array(
							'id' => '',
							'msgs' => array( __( 'Invalid data. Cheating?', 'ipt_fsqm' ) ),
						),
					),
				);
				echo json_encode( (object) $return );
				die();
			}
		}

		// All set now instantiate and save and return
		$form_data = new IPT_FSQM_Form_Elements_Data( $data_id, $form_id );

		// But again check for the user edit capability
		if ( $user_edit && $form_data->can_user_edit() !== true ) {
			$return = array(
				'success' => false,
				'errors' => array(
					0 => array(
						'id' => '',
						'msgs' => array( __( 'Invalid request.', 'ipt_fsqm' ) ),
					),
				),
			);
			echo json_encode( (object) $return );
			die();
		}

		$return = $form_data->save_form( $admin_update, $user_update );
		echo json_encode( (object) $return );
		die();
	}

	/*==========================================================================
	 * Report Generator
	 *========================================================================*/
	public static function ipt_fsqm_report() {
		add_action( 'wp_ajax_ipt_fsqm_report', array( __CLASS__, 'ipt_fsqm_report_cb' ) );
		add_action( 'wp_ajax_nopriv_ipt_fsqm_report', array( __CLASS__, 'ipt_fsqm_report_cb' ) );
	}

	public static function ipt_fsqm_report_cb() {
		global $wpdb, $ipt_fsqm_info, $ipt_fsqm_settings;
		$settings = isset( $_POST['settings'] ) ? $_POST['settings'] : array();
		// $survey = isset( $_POST['survey'] ) ? $_POST['survey'] : array();
		// $feedback = isset( $_POST['feedback'] ) ? $_POST['feedback'] : array();
		$doing = isset( $_POST['doing'] ) ? (int) $_POST['doing'] : 0;
		$form_id = isset( $_POST['form_id'] ) ? (int) $_POST['form_id'] : 0;
		$do_data = isset( $_POST['do_data'] ) && 'true' == $_POST['do_data'] ? true : false;
		$do_names = isset( $_POST['do_names'] ) && 'true' == $_POST['do_names'] ? true : false;
		$do_others = isset( $_POST['do_others'] ) && 'true' == $_POST['do_others'] ? true : false;
		$sensitive_data = isset( $_POST['sensitive_data'] ) && 'true' == $_POST['sensitive_data'] ? true : false;
		$do_date = isset( $_POST['do_date'] ) && 'true' == $_POST['do_date'] ? true : false;
		$query_elements = isset( $_POST['query_elements'] ) ? (array) $_POST['query_elements'] : array();
		$query_elements = wp_parse_args( $query_elements, array(
			'mcqs' => array(),
			'freetypes' => array(),
			'pinfos' => array(),
		) );
		$filters = isset( $_POST['filters'] ) ? wp_unslash( $_POST['filters'] ) : array();

		$debug_info = array();

		// Check general nonce
		if ( ! wp_verify_nonce( $_POST['wpnonce'], 'ipt_fsqm_report_ajax_' . $form_id ) ) {
			die( __( 'Cheatin&#8217; uh?' ) . ' none' );
		}

		// Verify do_data nonce for showing data alongside mcqs
		if ( $do_data && ! wp_verify_nonce( $_POST['do_data_nonce'], 'ipt_fsqm_report_ajax_do_data_' . $form_id ) ) {
			die( __( 'Cheatin&#8217; uh?' ) . ' do_data' );
		}

		// Verify do_names nonce for showing names to mcq meta and freetype
		if ( $do_names && ! wp_verify_nonce( $_POST['do_names_nonce'], 'ipt_fsqm_report_ajax_do_names_' . $form_id ) ) {
			die( __( 'Cheatin&#8217; uh?' ) . ' do_names' );
		}

		// Verify do_others for populating meta entries
		if ( $do_others && ! wp_verify_nonce( $_POST['do_others_nonce'], 'ipt_fsqm_report_ajax_do_others_' . $form_id ) ) {
			die( __( 'Cheatin&#8217; uh?' ) . ' do_others' );
		}

		// If sensitive data is enabled, then must verify the nonce and also current user should be able to manage_feedback
		if ( $sensitive_data && ( ! wp_verify_nonce( $_POST['sensitive_data_nonce'], 'ipt_fsqm_report_ajax_sensitive_data_' . $form_id ) || ! current_user_can( 'manage_feedback' ) ) ) {
			die( __( 'Cheatin&#8217; uh?' ) . ' sensitive_data' );
		}

		// Verify do_date for populating meta and freetype entries
		if ( $do_date && ! wp_verify_nonce( $_POST['do_date_nonce'], 'ipt_fsqm_report_ajax_do_date_' . $form_id ) ) {
			die( __( 'Cheatin&#8217; uh?' ) . ' do_date' );
		}

		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

		$return = array(
			'type' => 'success',
			'done' => '0',
			'survey' => array(),
			'feedback' => array(),
			'pinfo' => array(),
			'form_id' => $form_id,
			'debug_info' => array(),
		);

		// First test the form_id
		if ( null == $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$ipt_fsqm_info['form_table']} WHERE id = %d", $form_id ) ) ) {
			die( __( 'Cheatin&#8217; uh?' ) );
		}

		// Calculate the number of data to fetch
		$per_page = 15;
		if ( isset( $settings['load'] ) ) {
			switch ( $settings['load'] ) {
			case '1' :
				$per_page = 30;
				break;
			case '2' :
				$per_page = 50;
			}
		}

		$where = '';
		$where_arr = array();
		// Check for date filter
		if ( isset( $filters['custom_date'] ) && 'true' == $filters['custom_date'] ) {
			if ( isset( $filters['custom_date_start'] ) && $filters['custom_date_start'] != '' ) {
				$where_arr[] = $wpdb->prepare( 'date >= %s', date( 'Y-m-d H:i:s', strtotime( $filters['custom_date_start'] ) ) );
				$debug_info['ds'] = $filters['custom_date_start'];
			}

			if ( isset( $filters['custom_date_end'] ) && $filters['custom_date_end'] != '' ) {
				$where_arr[] = $wpdb->prepare( 'date <= %s', date( 'Y-m-d H:i:s', strtotime( $filters['custom_date_end'] ) ) );
				$debug_info['de'] = $filters['custom_date_end'];
			}
		}

		// Check for score filter
		if ( isset( $filters['score'] ) && is_array( $filters['score'] ) ) {
			// Minimum filter
			if ( isset( $filters['score']['min'] ) && '' != $filters['score']['min'] ) {
				$where_arr[] = $wpdb->prepare( 'score >= %f', $filters['score']['min'] );
				$debug_info['smin'] = $filters['score']['min'];
			}
			// Maximum filter
			if ( isset( $filters['score']['max'] ) && '' != $filters['score']['max'] ) {
				$where_arr[] = $wpdb->prepare( 'score <= %f', $filters['score']['max'] );
				$debug_info['smax'] = $filters['score']['max'];
			}
		}

		// Check for url_track
		if ( isset( $filters['url_track'] ) && is_array( $filters['url_track'] ) ) {
			// Omit if it is just all
			if ( ! in_array( '', $filters['url_track'] ) ) {
				$url_track_where = array();

				foreach ( $filters['url_track'] as $ut ) {
					$url_track_where[] = $wpdb->prepare( 'url_track = %s', $ut );
				}

				if ( ! empty( $url_track_where ) ) {
					$debug_info['url_track'] = $where_arr[] = '( ' . implode( ' OR ', $url_track_where ) . ' )';
				}
			}
		}

		// Check for user meta
		$user_ids = array();
		if ( isset( $filters['meta'] ) && '' != $filters['meta'] ) {
			$get_users_args = array(
				'meta_key' => $filters['meta'],
				'fields' => 'ID',
			);

			if ( isset( $filters['mvalue'] ) && '' != $filters['mvalue'] ) {
				$get_users_args['meta_value'] = $filters['mvalue'];
			}
			$users_from_meta = get_users( $get_users_args );
			if ( ! empty( $users_from_meta ) ) {
				$user_ids = array_merge( $user_ids, $users_from_meta );
			} else {
				$user_ids = array( '-1' );
			}
			$debug_info['uid_meta'] = $user_ids;
		}

		// Check for user_id
		if ( isset( $filters['user_id'] ) && is_array( $filters['user_id'] ) ) {
			// Omit if it is just all
			if ( ! in_array( '', $filters['user_id'] ) ) {
				$user_ids = array_merge( $user_ids, $filters['user_id'] );
				$debug_info['uid_filter'] = $filters['user_id'];
			}
		}

		// Add the user id to the query
		if ( ! empty( $user_ids ) ) {
			$user_ids = array_unique( $user_ids );
			$user_ids = array_map( 'intval', $user_ids );
			$where_arr[] = 'user_id IN (' . implode( ',', $user_ids ) . ')';
			$debug_info['user_id'] = $user_ids;
		}

		// Concatenate the where statement
		if ( ! empty( $where_arr ) ) {
			$where .= ' AND ' . implode( ' AND ', $where_arr );
		}

		$data_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d{$where} ORDER BY id ASC LIMIT %d,%d", $form_id, $doing * $per_page, $per_page ) );
		$total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d{$where}", $form_id ) );

		if ( empty( $data_ids ) ) {
			$return['done'] = 100;
			$return['debug_info'] = (object) $debug_info;
			echo json_encode( (object) $return );
			die();
		}

		// Some helper variables
		$date_formats = array(
			'yy-mm-dd' => 'Y-m-d',
			'mm/dd/yy' => 'm/d/Y',
			'dd.mm.yy' => 'd.m.Y',
			'dd-mm-yy' => 'd-m-Y',
		);
		$time_formats = array(
			'HH:mm:ss' => 'H:i:s',
			'hh:mm:ss TT' => 'h:i:s A',
		);

		foreach ( $data_ids as $data_id ) {
			$data = new IPT_FSQM_Form_Elements_Data( $data_id );

			$data_formatted_date = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $data->data->date ) );
			$data_formatted_email = $data->data->email == '' ? __( 'anonymous', 'ipt_fsqm' ) : '<a href="mailto:' . $data->data->email . '">' . $data->data->email . '</a>';
			$data_formatted_name = ( $data->data->f_name == '' && $data->data->l_name == '' ) ? __( 'anonymous', 'ipt_fsqm' ) : $data->data->f_name . ' ' . $data->data->l_name;

			// blacklist the conditionally hidden elements
			$data->blacklist_conditional_hiddens();

			// Do the survey
			if ( is_array( $query_elements['mcqs'] ) && ! empty( $query_elements['mcqs'] ) ) {
				foreach ( $query_elements['mcqs'] as $m_key ) {
					$m_key = (int) $m_key;
					if ( ! isset( $data->mcq[$m_key] ) || !isset( $data->data->mcq[$m_key] ) ) {
						continue;
					}
					$element = $data->mcq[$m_key];

					if ( isset( $data->conditional_hidden_blacklist[$element['m_type']] ) ) {
						if ( in_array( (string) $m_key, $data->conditional_hidden_blacklist[$element['m_type']] ) ) {
							continue;
						}
					}

					switch ( $element['type'] ) {
						default :
							$definition = $data->get_element_definition($element);
							if ( isset( $definition['callback_report_calculator'] ) && is_callable( $definition['callback_report_calculator'] ) ) {
								if ( ! isset($return['survey']["$m_key"] ) ) {
									$return['survey']["$m_key"] = array();
								}
								$return['survey']["$m_key"] = call_user_func( $definition['callback_report_calculator'], $element, $data->data->mcq[$m_key], $m_key, $do_data, $do_names, $do_others, $sensitive_data, $do_date, $return['survey']["$m_key"], $data );
							}
							break;
						case 'radio' :
						case 'checkbox' :
						case 'select' :
						case 'thumbselect' :
						case 'pricing_table' :
							if ( !isset( $return['survey']["$m_key"] ) ) {
								$return['survey']["$m_key"] = array();
								if ( $do_data ) {
									$return['survey']["$m_key"]['others_data'] = array();
								}
							}
							if ( ! isset( $data->data->mcq[$m_key]['options'] ) || empty( $data->data->mcq[$m_key]['options'] ) ) {
								continue 2;
							}
							foreach ( $data->data->mcq[$m_key]['options'] as $o_key ) {
								$return['survey']["$m_key"]["$o_key"] = isset( $return['survey']["$m_key"]["$o_key"] ) ? $return['survey']["$m_key"]["$o_key"] + 1 : 1;
							}
							if ( isset( $data->data->mcq[$m_key]['others'] ) && ! empty( $data->data->mcq[$m_key]['others'] ) && $do_others ) {
								$others_data = array(
									'value' => esc_textarea( $data->data->mcq[$m_key]['others'] ),
								);
								if ( $do_names ) {
									$others_data['name'] = $data_formatted_name;
								}
								if ( $sensitive_data ) {
									$others_data['email'] = $data_formatted_email;
									$others_data['id'] = $data->data_id;
								}
								if ( $do_date ) {
									$others_data['date'] = $data_formatted_date;
								}
								$return['survey']["$m_key"]['others_data'][] = $others_data;
							}
							break;
						case 'smileyrating' :
							$setting_to_icon_map = array(
								'frown' => 'angry2',
								'sad' => 'sad2',
								'neutral' => 'neutral2',
								'happy' => 'smiley2',
								'excited' => 'happy2',
							);
							if ( !isset( $return['survey']["$m_key"] ) ) {
								$return['survey']["$m_key"] = array(
									'frown' => 0,
									'sad' => 0,
									'neutral' => 0,
									'happy' => 0,
									'excited' => 0,
								);
								$return['survey']["$m_key"]['feedback_data'] = array();
							}
							if ( ! isset( $data->data->mcq[$m_key]['option'] ) || $data->data->mcq[$m_key]['option'] == '' ) {
								continue 2;
							}

							if ( isset( $return['survey']["$m_key"][$data->data->mcq[$m_key]['option']] ) ) {
								$return['survey']["$m_key"][$data->data->mcq[$m_key]['option']]++;
							}
							if ( $do_others && isset( $data->data->mcq[$m_key]['feedback'] ) && $data->data->mcq[$m_key]['feedback'] != '' ) {
								$feedback_data = array(
									'entry' => wpautop( esc_textarea( $data->data->mcq[$m_key]['feedback'] ) ),
									'rating' => '<i class="ipt-icomoon-' . $setting_to_icon_map[$data->data->mcq[$m_key]['option']] . '"></i>',
								);
								if ( $do_names ) {
									$feedback_data['name'] = $data_formatted_name;
								}
								if ( $sensitive_data ) {
									$feedback_data['email'] = $data_formatted_email;
									$feedback_data['id'] = $data->data_id;
								}
								if ( $do_date ) {
									$feedback_data['date'] = $data_formatted_date;
								}
								$return['survey']["$m_key"]['feedback_data'][] = $feedback_data;
							}
							break;
						case 'likedislike' :
							$setting_to_icon_map = array(
								'like' => 'thumbs-o-up',
								'dislike' => 'thumbs-o-down',
							);
							if ( ! isset( $return['survey']["$m_key"] ) ) {
								$return['survey']["$m_key"] = array(
									'like' => 0,
									'dislike' => 0,
								);
								$return['survey']["$m_key"]['feedback_data'] = array();
							}
							if ( ! isset( $data->data->mcq[$m_key]['value'] ) || $data->data->mcq[$m_key]['value'] == '' ) {
								continue 2;
							}
							if ( isset( $return['survey']["$m_key"][$data->data->mcq[$m_key]['value']] ) ) {
								$return['survey']["$m_key"][$data->data->mcq[$m_key]['value']]++;
							}
							if ( $do_others && isset( $data->data->mcq[$m_key]['feedback'] ) && $data->data->mcq[$m_key]['feedback'] != '' ) {
								$feedback_data = array(
									'entry' => wpautop( esc_textarea( $data->data->mcq[$m_key]['feedback'] ) ),
									'rating' => '<i class="ipt-icomoon-' . $setting_to_icon_map[$data->data->mcq[$m_key]['value']] . '"></i>',
								);
								if ( $do_names ) {
									$feedback_data['name'] = $data_formatted_name;
								}
								if ( $sensitive_data ) {
									$feedback_data['email'] = $data_formatted_email;
									$feedback_data['id'] = $data->data_id;
								}
								if ( $do_date ) {
									$feedback_data['date'] = $data_formatted_date;
								}
								$return['survey']["$m_key"]['feedback_data'][] = $feedback_data;
							}
							break;
						case 'slider' :
							if ( ! isset( $return['survey']["$m_key"] ) ) {
								$return['survey']["$m_key"] = array();
							}
							if ( ! isset($data->data->mcq[$m_key]['value']) || '' == $data->data->mcq[$m_key]['value'] ) {
								continue 2;
							}
							$return['survey']["$m_key"]["{$data->data->mcq[$m_key]['value']}"] = isset( $return['survey']["$m_key"]["{$data->data->mcq[$m_key]['value']}"] ) ? $return['survey']["$m_key"]["{$data->data->mcq[$m_key]['value']}"] + 1 : 1;
							break;
						case 'range' :
							if ( ! isset( $return['survey']["$m_key"] ) ) {
								$return['survey']["$m_key"] = array();
							}
							if ( empty( $data->data->mcq[$m_key]['values'] ) ) {
								continue 2;
							}
							$key = "{$data->data->mcq[$m_key]['values']['min']},{$data->data->mcq[$m_key]['values']['max']}";
							$return['survey']["$m_key"][$key] = isset( $return['survey']["$m_key"][$key] ) ? $return['survey']["$m_key"][$key] + 1 : 1;
							break;
						case 'spinners' :
						case 'grading' :
						case 'starrating' :
						case 'scalerating' :
							if ( !isset( $return['survey']["$m_key"] ) ) {
								$return['survey']["$m_key"] = array();
							}
							if ( empty( $data->data->mcq[$m_key]['options'] ) ) {
								continue 2;
							}

							foreach ( $data->mcq[$m_key]['settings']['options'] as $o_key => $o_val ) {
								if ( !isset( $return['survey']["$m_key"]["$o_key"] ) ) {
									$return['survey']["$m_key"]["$o_key"] = array();
								}
								if ( !isset( $data->data->mcq[$m_key]['options'][$o_key] ) ) {
									continue;
								}
								if ( is_array( $data->data->mcq[$m_key]['options'][$o_key] ) ) {
									$key = $data->data->mcq[$m_key]['options'][$o_key]['min'] . ',' . $data->data->mcq[$m_key]['options'][$o_key]['max'];
								} else {
									$key = (string) $data->data->mcq[$m_key]['options'][$o_key];
								}

								if ( $key == '' ) {
									continue;
								}

								$return['survey']["$m_key"]["$o_key"][$key] = isset( $return['survey']["$m_key"]["$o_key"][$key] ) ? $return['survey']["$m_key"]["$o_key"][$key] + 1 : 1;
							}
							break;
						case 'matrix_dropdown' :
							if ( !isset( $return['survey']["$m_key"] ) ) {
								$return['survey']["$m_key"] = array();
							}
							if ( empty( $data->data->mcq[$m_key]['rows'] ) ) {
								continue 2;
							}
							$options_array = array();
							foreach ( $data->mcq[$m_key]['settings']['options'] as $o_key => $op ) {
								$options_array["$o_key"] = 0;
							}
							foreach ( (array) $data->data->mcq[$m_key]['rows'] as $r_key => $columns ) {
								if ( !isset( $return['survey']["$m_key"]["$r_key"] ) ) {
									$return['survey']["$m_key"]["$r_key"] = array();
								}
								foreach ( (array) $columns as $c_key => $o_key ) {
									if ( ! isset( $return['survey']["$m_key"]["$r_key"]["$c_key"] ) ) {
										$return['survey']["$m_key"]["$r_key"]["$c_key"] = $options_array;
									}
									if ( is_array( $o_key ) ) {
										foreach ( $o_key as $option_key ) {
											if ( isset( $return['survey']["$m_key"]["$r_key"]["$c_key"]["$option_key"] ) ) {
												$return['survey']["$m_key"]["$r_key"]["$c_key"]["$option_key"]++;
											}
										}
									} else {
										if ( isset( $return['survey']["$m_key"]["$r_key"]["$c_key"]["$o_key"] ) ) {
											$return['survey']["$m_key"]["$r_key"]["$c_key"]["$o_key"]++;
										}
									}
								}
							}
							break;
						case 'matrix' :
							if ( !isset( $return['survey']["$m_key"] ) ) {
								$return['survey']["$m_key"] = array();
							}
							if ( empty( $data->data->mcq[$m_key]['rows'] ) ) {
								continue 2;
							}
							foreach ( $data->data->mcq[$m_key]['rows'] as $r_key => $columns ) {
								if ( !isset( $return['survey']["$m_key"]["$r_key"] ) ) {
									$return['survey']["$m_key"]["$r_key"] = array();
								}
								foreach ( $columns as $c_key ) {
									$return['survey']["$m_key"]["$r_key"]["$c_key"] = isset( $return['survey']["$m_key"]["$r_key"]["$c_key"] ) ? $return['survey']["$m_key"]["$r_key"]["$c_key"] + 1 : 1;
								}
							}
							break;
						case 'toggle' :
							if ( !isset( $return['survey']["$m_key"] ) ) {
								$return['survey']["$m_key"] = array(
									'on' => 0,
									'off' => 0,
								);
							}
							if ( $data->data->mcq[$m_key]['value'] == false ) {
								$return['survey']["$m_key"]['off']++;
							} else {
								$return['survey']["$m_key"]['on']++;
							}
							break;
						case 'sorting' :
							if ( !isset( $return['survey']["$m_key"] ) ) {
								$return['survey']["$m_key"] = array(
									'preset' => 0,
									'other' => 0,
									'orders' => array(),
								);
							}
							if ( empty( $data->data->mcq[$m_key]['order'] ) ) {
								continue 2;
							}
							$correct_order = implode( '-', array_keys( $data->mcq[$m_key]['settings']['options'] ) );
							$user_order = implode( '-', $data->data->mcq[$m_key]['order'] );
							if ( $correct_order == $user_order ) {
								$return['survey']["$m_key"]['preset']++;
							} else {
								$return['survey']["$m_key"]['other']++;
							}
							$return['survey']["$m_key"]['orders'][$user_order] = isset( $return['survey']["$m_key"]['orders'][$user_order] ) ? $return['survey']["$m_key"]['orders'][$user_order] + 1 : 1;
					}
				}
			}

			// Do the Feedback
			if ( is_array( $query_elements['freetypes'] ) && !empty( $query_elements['freetypes'] ) ) {
				foreach ( $query_elements['freetypes'] as $f_key ) {
					if ( !isset( $data->freetype[$f_key] ) || !isset( $data->data->freetype[$f_key] ) ) {
						continue;
					}
					$element = $data->freetype[$f_key];

					if ( isset( $data->conditional_hidden_blacklist[$element['m_type']] ) ) {
						if ( in_array( (string) $f_key, $data->conditional_hidden_blacklist[$element['m_type']] ) ) {
							continue;
						}
					}

					switch ( $element['type'] ) {
						default :
							$definition = $data->get_element_definition($element);
							if ( isset( $definition['callback_report_calculator'] ) && is_callable( $definition['callback_report_calculator'] ) ) {
								if ( ! isset( $return['feedback']["$f_key"] ) ) {
									$return['feedback']["$f_key"] = array();
								}
								$return['feedback']["$f_key"] = call_user_func( $definition['callback_report_calculator'], $element, $data->data->freetype[$f_key], $f_key, $do_data, $do_names, $sensitive_data, $do_date, $return['feedback']["$f_key"], $data );
							}
							break;
						case 'upload' :
							// Create the array
							if ( ! isset( $return['feedback']["$f_key"] ) ) {
								$return['feedback']["$f_key"] = array();
							}

							// Init the uploader class
							$uploader = new IPT_FSQM_Form_Elements_Uploader( $data->form_id, $f_key );
							$uploads = $uploader->get_uploads( $data->data_id );

							// Loop through all uploads and save the meta
							$upload_array = array();

							if ( ! empty( $uploads ) ) {
								foreach ( $uploads as $upload ) {
									if ( '' == $upload['guid'] ) {
										continue;
									}
									$upload_array[] = array(
										'guid'      => $upload['guid'],
										'thumb_url' => $upload['thumb_url'],
										'name'      => $upload['name'] . ' (' . $upload['mime_type'] . ' )',
										'filename'  => $upload['filename'],
									);
								}
							}
							$feedback_upload = array(
								'uploads' => $upload_array,
							);
							if ( $sensitive_data ) {
								$feedback_upload['id'] = $data->data_id;
								$feedback_upload['email'] = $data_formatted_email;
							}
							if ( $do_names ) {
								$feedback_upload['name'] = $data_formatted_name;
							}
							if ( $do_date ) {
								$feedback_upload['date'] = $data_formatted_date;
							}
							$return['feedback']["$f_key"][] = $feedback_upload;

							break;
						case 'feedback_large' :
						case 'feedback_small' :
							if ( !isset( $return['feedback']["$f_key"] ) ) {
								$return['feedback']["$f_key"] = array();
							}

							if ( empty( $data->data->freetype[$f_key]['value'] ) ) {
								continue 2;
							}
							$feedback_text = array(
								'value' => wpautop( esc_textarea( $data->data->freetype["$f_key"]['value'] ) ),
							);
							if ( $do_names ) {
								$feedback_text['name'] = $data_formatted_name;
							}
							if ( $sensitive_data ) {
								$feedback_text['email'] = $data_formatted_email;
								$feedback_text['phone'] = $data->data->phone;
								$feedback_text['id'] = $data->data_id;
							}
							if ( $do_date ) {
								$feedback_text['date'] = $data_formatted_date;
							}
							$return['feedback']["$f_key"][] = $feedback_text;
							break;
						case 'mathematical' :
							if ( !isset( $return['feedback']["$f_key"] ) ) {
								$return['feedback']["$f_key"] = array();
							}

							if ( empty( $data->data->freetype[$f_key]['value'] ) ) {
								continue 2;
							}

							$ui = IPT_Plugin_UIF_Front::instance();
							ob_start();
							$ui->print_icon( $data->freetype[$f_key]['settings']['icon'], false );
							$icon = ob_get_clean();
							$feedback_math = array(
								'value' => $data->freetype[$f_key]['settings']['prefix'] . ' ' . $icon . ' ' . $data->data->freetype["$f_key"]['value'] . ' ' . $data->freetype[$f_key]['settings']['suffix'],
							);
							if ( $do_names ) {
								$feedback_math['name'] = $data_formatted_name;
							}
							if ( $sensitive_data ) {
								$feedback_math['email'] = $data_formatted_email;
								$feedback_math['phone'] = $data->data->phone;
								$feedback_math['id'] = $data->data_id;
							}
							if ( $do_date ) {
								$feedback_math['date'] = $data_formatted_date;
							}
							$return['feedback']["$f_key"][] = $feedback_math;
							break;
						case 'gps' :
							if ( !isset( $return['feedback']["$f_key"] ) ) {
								$return['feedback']["$f_key"] = array();
							}

							if ( ! is_numeric( $data->data->freetype[$f_key]['lat'] ) || ! is_numeric( $data->data->freetype[$f_key]['long'] ) ) {
								continue 2;
							}

							$feedback_gps = array(
								'map'           => sprintf( '//maps.googleapis.com/maps/api/staticmap?markers=%1$s,%2$s&zoom=%3$s&size=500x300&scale=2&key=%4$s', round( (float) $data->data->freetype[$f_key]['lat'], 6 ), round( (float) $data->data->freetype[$f_key]['long'], 6 ), $data->freetype[$f_key]['settings']['zoom'], $ipt_fsqm_settings['gplaces_api'] ),
								'lat'           => $data->data->freetype[$f_key]['lat'],
								'long'          => $data->data->freetype[$f_key]['long'],
								'location_name' => $data->data->freetype[$f_key]['location_name'],
							);
							if ( $do_names ) {
								$feedback_gps['name'] = $data_formatted_name;
							}
							if ( $sensitive_data ) {
								$feedback_gps['email'] = $data_formatted_email;
								$feedback_gps['id'] = $data->data_id;
							}
							if ( $do_date ) {
								$feedback_gps['date'] = $data_formatted_date;
							}
							$return['feedback']["$f_key"][] = $feedback_gps;
							break;
						case 'feedback_matrix' :
							if ( !isset( $return['feedback']["$f_key"] ) ) {
								$return['feedback']["$f_key"] = array();
							}
							$break_data = true;
							$sanitized_matrix = array();
							foreach ( $data->data->freetype[$f_key]['rows'] as $r_key => $columns ) {
								$sanitized_matrix["$r_key"] = array();
								foreach ( (array) $columns as $c_key => $val ) {
									if ( $val != '' ) {
										$break_data = false;
									}
									$sanitized_matrix["$r_key"]["$c_key"] = wpautop( esc_textarea( $val ) );
								}
							}
							if ( $break_data ) {
								continue 2;
							}
							$feedback_matrix = array(
								'matrix'        => $sanitized_matrix,
							);
							if ( $do_names ) {
								$feedback_matrix['name'] = $data_formatted_name;
							}
							if ( $sensitive_data ) {
								$feedback_matrix['email'] = $data_formatted_email;
								$feedback_matrix['id'] = $data->data_id;
							}
							if ( $do_date ) {
								$feedback_matrix['date'] = $data_formatted_date;
							}
							$return['feedback']["$f_key"][] = $feedback_matrix;
							break;
						case 'signature' :
							if ( !isset( $return['feedback']["$f_key"] ) ) {
								$return['feedback']["$f_key"] = array();
							}

							if ( empty( $data->data->freetype[$f_key]['value'] ) || $data->data->freetype["$f_key"]['value'] == 'image/jsignature;base30,' ) {
								continue 2;
							}

							$feedback_sign = array(
								'value' => $data->convert_jsignature_image( $data->data->freetype["$f_key"]['value'], $element['settings']['color'] ),
							);
							if ( $do_names ) {
								$feedback_sign['name'] = $data_formatted_name;
							}
							if ( $sensitive_data ) {
								$feedback_sign['email'] = $data_formatted_email;
								$feedback_sign['phone'] = $data->data->phone;
								$feedback_sign['id'] = $data->data_id;
							}
							if ( $do_date ) {
								$feedback_sign['date'] = $data_formatted_date;
							}
							$return['feedback']["$f_key"][] = $feedback_sign;
							break;
					}
				}
			}

			// Do the pinfos
			if ( is_array( $query_elements['pinfos'] ) && ! empty( $query_elements['pinfos'] ) ) {
				// Loop through all given pinfos
				foreach ( $query_elements['pinfos'] as $p_key ) {
					// Cast to a true integer, because it is stored as string (string '1' not int 1)
					$p_key = (int) $p_key;
					// If element or data does not exists, then leave it
					if ( ! isset( $data->pinfo[$p_key] ) || ! isset( $data->data->pinfo[$p_key] ) ) {
						continue;
					}

					// Set the element
					$element = $data->pinfo[$p_key];

					// Avoid if it is conditionally hidden
					if ( isset( $data->conditional_hidden_blacklist[$element['m_type']] ) ) {
						if ( in_array( (string) $p_key, $data->conditional_hidden_blacklist[$element['m_type']] ) ) {
							continue;
						}
					}

					// Everything checks out
					// So do for specific elements
					switch ( $element['type'] ) {
						// Open scope for third party integration
						default :
							$definition = $data->get_element_definition( $element );
							if ( isset( $definition['callback_report_calculator'] ) && is_callable( $definition['callback_report_calculator'] ) ) {
								if ( ! isset($return['pinfo']["$p_key"] ) ) {
									$return['pinfo']["$p_key"] = array();
								}
								$return['pinfo']["$p_key"] = call_user_func( $definition['callback_report_calculator'], $element, $data->data->pinfo[$p_key], $p_key, $do_data, $do_names, $do_others, $sensitive_data, $do_date, $return['pinfo']["$p_key"], $data );
							}
							break;
						// Text types
						case 'f_name' :
						case 'l_name' :
						case 'email' :
						case 'phone' :
						case 'p_name' :
						case 'p_email' :
						case 'p_phone' :
						case 'textinput' :
						case 'textarea' :
						case 'password' :
						case 'keypad' :
						case 'hidden' :
							if ( ! isset( $return['pinfo'][ "$p_key" ] ) ) {
								$return['pinfo'][ "$p_key" ] = array();
							}

							if ( empty( $data->data->pinfo[ $p_key ]['value'] ) ) {
								continue 2;
							}

							$pinfo_text = array(
								'value' => wpautop( esc_textarea( $data->data->pinfo["$p_key"]['value'] ) ),
							);
							if ( $do_names ) {
								$pinfo_text['name'] = $data_formatted_name;
							}
							if ( $sensitive_data ) {
								$pinfo_text['email'] = $data_formatted_email;
								$pinfo_text['phone'] = $data->data->phone;
								$pinfo_text['id'] = $data->data_id;
							}
							if ( $do_date ) {
								$pinfo_text['date'] = $data_formatted_date;
							}
							$return['pinfo']["$p_key"][] = $pinfo_text;
							break;
						case 'datetime' :
							if ( !isset( $return['pinfo'][ "$p_key" ] ) ) {
								$return['pinfo'][ "$p_key" ] = array();
							}

							if ( empty( $data->data->pinfo[ $p_key ]['value'] ) ) {
								continue 2;
							}

							$dtvalue = $data->data->pinfo["$p_key"]['value'];
							$current_picker_timestamp = strtotime( $dtvalue );
							if ( $current_picker_timestamp != false ) {
								switch ( $element['settings']['type'] ) {
								case 'date' :
									$dtvalue = date( $date_formats[$element['settings']['date_format']], $current_picker_timestamp );
									break;
								case 'time' :
									$dtvalue = date( $time_formats[$element['settings']['time_format']], $current_picker_timestamp );
									break;
								case 'datetime' :
									$dtvalue = date( $date_formats[$element['settings']['date_format']] . ' ' . $time_formats[$element['settings']['time_format']], $current_picker_timestamp );
									break;
								}
							}
							$pinfo_text = array(
								'value' => $dtvalue,
							);
							if ( $do_names ) {
								$pinfo_text['name'] = $data_formatted_name;
							}
							if ( $sensitive_data ) {
								$pinfo_text['email'] = $data_formatted_email;
								$pinfo_text['phone'] = $data->data->phone;
								$pinfo_text['id'] = $data->data_id;
							}
							if ( $do_date ) {
								$pinfo_text['date'] = $data_formatted_date;
							}
							$return['pinfo']["$p_key"][] = $pinfo_text;
							break;
						case 'address' :
							if ( ! isset( $return['pinfo'][ "$p_key" ] ) ) {
								$return['pinfo'][ "$p_key" ] = array();
							}

							if ( $data->data->pinfo[$p_key]['values'] == array(
								'recipient' => '',
								'line_one' => '',
								'line_two' => '',
								'line_three' => '',
								'country' => '',
							) ) {
								continue 2;
							}

							$address = $data->data->pinfo[$p_key]['values'];
							$address = array_map( 'esc_textarea', $address );
							$pinfo_text = array(
								'values' => $address,
							);
							if ( $do_names ) {
								$pinfo_text['name'] = $data_formatted_name;
							}
							if ( $sensitive_data ) {
								$pinfo_text['email'] = $data_formatted_email;
								$pinfo_text['phone'] = $data->data->phone;
								$pinfo_text['id'] = $data->data_id;
							}
							if ( $do_date ) {
								$pinfo_text['date'] = $data_formatted_date;
							}
							$return['pinfo']["$p_key"][] = $pinfo_text;
							break;
						// Payment
						case 'payment' :
							if ( ! isset( $return['pinfo'][ "$p_key" ] ) ) {
								$return['pinfo'][ "$p_key" ] = array();
							}

							// This needs special data collection
							// Get the data from table
							$payment_db = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_info['payment_table']} WHERE data_id = %d", $data->data_id ) );
							// Skip if not present
							if ( is_null( $payment_db ) ) {
								continue 2;
							}

							// Populate the data
							$invoiceid = str_replace( '{id}', $payment_db->id, $data->settings['payment']['invoicenumber'] );
							if ( $invoiceid == '' ) {
								$invoiceid = $payment_db->id;
							}
							$payment_status = IPT_FSQM_Form_Elements_Static::ipt_fsqm_get_payment_status();
							$payment_modes = IPT_FSQM_Form_Elements_Static::ipt_fsqm_get_payment_gateways();
							$pinfo_payment = array(
								'invoice' => $invoiceid,
								'status' => isset( $payment_status[ $payment_db->status ] ) ? $payment_status[ $payment_db->status ] : __( 'Unknown', 'ipt_fsqm' ),
								'txn' => ( is_null( $payment_db->txn ) ? __( 'N/A', 'ipt_fsqm' ) : $payment_db->txn ) ,
								'gateway' => isset( $payment_modes[ $payment_db->mode ] ) ? $payment_modes[ $payment_db->mode ] : __( 'Unknown', 'ipt_fsqm' ),
								'total' => $payment_db->amount,
							);
							if ( $do_names ) {
								$pinfo_payment['name'] = $data_formatted_name;
							}
							if ( $sensitive_data ) {
								$pinfo_payment['email'] = $data_formatted_email;
								$pinfo_payment['phone'] = $data->data->phone;
								$pinfo_payment['id'] = $data->data_id;
							}
							if ( $do_date ) {
								$pinfo_payment['date'] = $data_formatted_date;
							}
							$return['pinfo']["$p_key"][] = $pinfo_payment;
							break;
						// MCQ
						case 'p_radio' :
						case 'p_checkbox' :
						case 'p_select' :
							if ( ! isset( $return['pinfo']["$p_key"] ) ) {
								$return['pinfo']["$p_key"] = array();
								if ( $do_data ) {
									$return['pinfo']["$p_key"]['others_data'] = array();
								}
							}
							if ( empty( $data->data->pinfo[$p_key]['options'] ) ) {
								continue 2;
							}
							foreach ( $data->data->pinfo[$p_key]['options'] as $o_key ) {
								$return['pinfo']["$p_key"]["$o_key"] = isset( $return['pinfo']["$p_key"]["$o_key"] ) ? $return['pinfo']["$p_key"]["$o_key"] + 1 : 1;
							}
							if ( isset( $data->data->pinfo[$p_key]['others'] ) && ! empty( $data->data->pinfo[$p_key]['others'] ) && $do_others ) {
								$others_data = array(
									'value' => esc_textarea( $data->data->pinfo[$p_key]['others'] ),
								);
								if ( $do_names ) {
									$others_data['name'] = $data_formatted_name;
								}
								if ( $sensitive_data ) {
									$others_data['email'] = $data_formatted_email;
									$others_data['id'] = $data->data_id;
								}
								if ( $do_date ) {
									$others_data['date'] = $data_formatted_date;
								}
								$return['pinfo']["$p_key"]['others_data'][] = $others_data;
							}
							break;
						// Single State
						case 's_checkbox' :
							if ( ! isset( $return['pinfo']["$p_key"] ) ) {
								$return['pinfo']["$p_key"] = array(
									'checked' => 0,
									'unchecked' => 0,
								);
							}
							if ( ! isset( $data->data->pinfo[$p_key]['value'] ) ) {
								continue 2;
							}
							if ( $data->data->pinfo[$p_key]['value'] == false ) {
								$return['pinfo']["$p_key"]['unchecked']++;
							} else {
								$return['pinfo']["$p_key"]['checked']++;
							}
							break;
						// Sorting
						case 'p_sorting' :
							if ( ! isset( $return['pinfo']["$p_key"] ) ) {
								$return['pinfo']["$p_key"] = array(
									'preset' => 0,
									'other' => 0,
									'orders' => array(),
								);
							}
							if ( ! isset( $data->data->pinfo[$p_key]['order'] ) || empty( $data->data->pinfo[$p_key]['order'] ) ) {
								continue 2;
							}
							$correct_order = implode( '-', array_keys( $data->pinfo[$p_key]['settings']['options'] ) );
							$user_order = implode( '-', $data->data->pinfo[$p_key]['order'] );
							if ( $correct_order == $user_order ) {
								$return['pinfo']["$p_key"]['preset']++;
							} else {
								$return['pinfo']["$p_key"]['other']++;
							}
							$return['pinfo']["$p_key"]['orders'][$user_order] = isset( $return['pinfo']["$p_key"]['orders'][$user_order] ) ? $return['pinfo']["$p_key"]['orders'][$user_order] + 1 : 1;
							break;

						// Guest Blog
						case 'guestblog' :
							if ( ! isset( $return['pinfo'][ "$p_key" ] ) ) {
								$return['pinfo'][ "$p_key" ] = array();
							}

							if ( empty( $data->data->pinfo[ $p_key ]['value'] ) ) {
								continue 2;
							}

							$pinfo_text = array(
								'value' => $data->data->pinfo[ "$p_key" ]['value'],
								'title' => $data->data->pinfo[ "$p_key" ]['title'],
								'taxonomy' => array(),
							);
							if ( ! empty( $data->data->pinfo[ "$p_key" ]['taxonomy'] ) ) {
								foreach ( $data->data->pinfo[ "$p_key" ]['taxonomy'] as $taxonomy => $tax_selected ) {
									$tax_data = get_taxonomy( $taxonomy );
									$terms_data = array();
									foreach( (array) $tax_selected as $term ) {
										$terms_data[] = get_term( $term, $taxonomy )->name;
									}
									$pinfo_text['taxonomy'][] = array(
										'tax' => $tax_data->labels->name,
										'terms' => $terms_data,
									);
								}
							}
							if ( $do_names ) {
								$pinfo_text['name'] = $data_formatted_name;
							}
							if ( $sensitive_data ) {
								$pinfo_text['email'] = $data_formatted_email;
								$pinfo_text['phone'] = $data->data->phone;
								$pinfo_text['id'] = $data->data_id;
							}
							if ( $do_date ) {
								$pinfo_text['date'] = $data_formatted_date;
							}
							$return['pinfo']["$p_key"][] = $pinfo_text;
							break;
						case 'repeatable' :
							if ( ! isset( $return['pinfo'][ "$p_key" ] ) ) {
								$return['pinfo'][ "$p_key" ] = array();
							}

							if ( empty( $data->data->pinfo[ $p_key ]['values'] ) ) {
								continue 2;
							}

							$pinfo_text = array(
								'value' => '<table class="data-table"><tbody>',
							);

							$rowspan = count( (array) $data->pinfo[ $p_key ]['settings']['group'] ) + 1;
							$i = 1;
							foreach ( (array) $data->data->pinfo[ $p_key ]['values'] as $i_key => $items ) {
								$pinfo_text['value'] .= '<tr><th rowspan="' . $rowspan . '">' . sprintf( _x( '#%d', 'eform-repetable-heading', 'ipt_fsqm' ), $i++ ) . '</th></tr>';
								foreach ( (array) $data->pinfo[ $p_key ]['settings']['group'] as $g_key => $group ) {
									$pinfo_text['value'] .= '<tr><th>' . $group['title'] . '</th><td>';

									if ( isset( $items[ $g_key ] ) ) {
										switch ( $group['type'] ) {
											case 'radio' :
												$pinfo_text['value'] .= str_replace( '__', ' ', $items[ $g_key ] );
												break;
											case 'checkbox' :
											case 'select' :
											case 'select_multiple' :
												$options = array();
												foreach ( (array) $items[ $g_key ] as $op ) {
													$options[] = str_replace( '__', ' ', $op );
												}
												$pinfo_text['value'] .= implode( '<br />', $options );
												break;
											case 'text' :
											case 'phone' :
											case 'url' :
											case 'email' :
											case 'number' :
											case 'integer' :
											case 'personName' :
											case 'password' :
											case 'textarea' :
												$pinfo_text['value'] .= $items[ $g_key ];
												break;
											case 'date' :
												$date = DateTime::createFromFormat( 'Y-m-d', $items[ $g_key ] );
												if ( ! $date ) {
													$pinfo_text['value'] .= $items[ $g_key ];
												} else {
													$pinfo_text['value'] .= $date->format( get_option( 'date_format' ) );
												}
												break;
											case 'time' :
												$date = DateTime::createFromFormat( 'H:i:s', $items[ $g_key ] );
												if ( ! $date ) {
													$pinfo_text['value'] .= $items[ $g_key ];
												} else {
													$pinfo_text['value'] .= $date->format( get_option( 'time_format' ) );
												}
												break;
											case 'datetime' :
												$date = DateTime::createFromFormat( 'Y-m-d H:i:s', $items[ $g_key ] );
												if ( ! $date ) {
													$pinfo_text['value'] .= $items[ $g_key ];
												} else {
													$pinfo_text['value'] .= $date->format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
												}
												break;
										}
									}

									$pinfo_text['value'] .= '</td></tr>';
								}
							}

							$pinfo_text['value'] .= '</tbody></table>';

							if ( $do_names ) {
								$pinfo_text['name'] = $data_formatted_name;
							}
							if ( $sensitive_data ) {
								$pinfo_text['email'] = $data_formatted_email;
								$pinfo_text['phone'] = $data->data->phone;
								$pinfo_text['id'] = $data->data_id;
							}
							if ( $do_date ) {
								$pinfo_text['date'] = $data_formatted_date;
							}
							$return['pinfo'][ "$p_key" ][] = $pinfo_text;
							break;
					}
				}
			}
		}

		//Calculate the done
		$done_till_now = $doing * $per_page + $per_page;
		if ( $done_till_now >= $total ) {
			$return['done'] = 100;
		} else {
			$return['done'] = (float) $done_till_now * 100 / $total;
		}

		$return['survey'] = (object) $return['survey'];
		$return['feedback'] = (object) $return['feedback'];
		$return['pinfo'] = (object) $return['pinfo'];

		// Add the debug info
		$debug_info['total'] = $total;
		$return['debug_info'] = (object) $debug_info;

		echo json_encode( (object) $return );
		die();
	}

	/*==========================================================================
	 * Database abstractions
	 *========================================================================*/

	/**
	 * Get all of the forms
	 *
	 * @global wpdb $wpdb
	 * @global array $ipt_fsqm_info
	 * @return array
	 */
	public static function get_forms() {
		global $wpdb, $ipt_fsqm_info;
		return $wpdb->get_results( "SELECT * FROM {$ipt_fsqm_info['form_table']} ORDER BY id DESC" );
	}

	public static function get_form( $form_id ) {
		global $wpdb, $ipt_fsqm_info;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_info['form_table']} WHERE id = %d", $form_id ) );
	}

	public static function get_forms_for_select() {
		global $wpdb, $ipt_fsqm_info;
		return $wpdb->get_results( "SELECT id, name FROM {$ipt_fsqm_info['form_table']} ORDER BY id DESC" );
	}

	public static function get_forms_for_select_with_count() {
		global $wpdb, $ipt_fsqm_info;
		return $wpdb->get_results(
			"SELECT f.id id, f.name name, COUNT(d.id) sub FROM {$ipt_fsqm_info['form_table']} f LEFT JOIN {$ipt_fsqm_info['data_table']} d ON f.id = d.form_id GROUP BY f.id ORDER BY f.id DESC"
		);
	}

	public static function get_form_themes_for_select() {
		$form_element = new IPT_FSQM_Form_Elements_Base();
		$themes = $form_element->get_available_themes();
		$return = array();
		foreach ( $themes as $theme_grp ) {
			foreach ( (array) $theme_grp['themes'] as $theme_key => $theme ) {
				$return[$theme_key] = $theme['label'];
			}
		}
		return $return;
	}

	public static function delete_submissions( $ids = array() ) {
		global $wpdb, $ipt_fsqm_info;
		if ( empty( $ids ) ) {
			return;
		}

		if ( ! is_array( $ids ) ) {
			$ids = (array) $ids;
		}

		$ids = array_map( 'intval', $ids );

		$delete_ids = implode( ',', $ids );

		do_action( 'ipt_fsqm_submissions_deleted', $ids );

		return $wpdb->query( "DELETE FROM {$ipt_fsqm_info['data_table']} WHERE id IN ({$delete_ids})" );
	}

	public static function star_submissions( $ids = array() ) {
		global $wpdb, $ipt_fsqm_info;
		if ( empty( $ids ) ) {
			return;
		}

		if ( ! is_array( $ids ) ) {
			$ids = (array) $ids;
		}

		$ids = array_map( 'intval', $ids );

		$update_ids = implode( ',', $ids );

		do_action( 'ipt_fsqm_submissions_starred', $ids );

		return $wpdb->query( "UPDATE {$ipt_fsqm_info['data_table']} SET star = 1 WHERE id IN ({$update_ids})" );
	}

	public static function unstar_submissions( $ids = array() ) {
		global $wpdb, $ipt_fsqm_info;
		if ( empty( $ids ) ) {
			return;
		}

		if ( ! is_array( $ids ) ) {
			$ids = (array) $ids;
		}

		$ids = array_map( 'intval', $ids );

		$update_ids = implode( ',', $ids );

		do_action( 'ipt_fsqm_submissions_unstarred', $ids );

		return $wpdb->query( "UPDATE {$ipt_fsqm_info['data_table']} SET star = 0 WHERE id IN ({$update_ids})" );
	}

	public static function delete_forms( $ids = array() ) {
		global $wpdb, $ipt_fsqm_info;
		if ( empty( $ids ) ) {
			return;
		}

		if ( ! is_array( $ids ) ) {
			$ids = (array) $ids;
		}

		$ids = array_map( 'intval', $ids );

		$delete_ids = implode( ',', $ids );

		$submission_ids = $wpdb->get_col( "SELECT id FROM {$ipt_fsqm_info['data_table']} WHERE form_id IN ({$delete_ids})" );

		self::delete_submissions( $submission_ids );

		do_action( 'ipt_fsqm_forms_deleted', $ids );
		return $wpdb->query( "DELETE FROM {$ipt_fsqm_info['form_table']} WHERE id IN ({$delete_ids})" );
	}

	public static function copy_form( $id ) {
		global $wpdb, $ipt_fsqm_info;
		$prev = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_info['form_table']} WHERE id = %d", $id ) );
		if ( null == $prev ) {
			return;
		}

		$prev->name .= ' Copy';
		$wpdb->insert( $ipt_fsqm_info['form_table'], array(
			'name' => $prev->name,
			'settings' => $prev->settings,
			'layout' => $prev->layout,
			'design' => $prev->design,
			'mcq' => $prev->mcq,
			'freetype' => $prev->freetype,
			'pinfo' => $prev->pinfo,
			'type' => $prev->type,
			'category' => $prev->category,
		), '%s' );

		do_action( 'ipt_fsqm_form_copied', $id, $wpdb->insert_id );
	}

	public static function get_category( $id ) {
		global $wpdb, $ipt_fsqm_info;
		$category = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_info['category_table']} WHERE id = %d", $id ) );
		return $category;
	}

	public static function get_all_categories() {
		global $wpdb, $ipt_fsqm_info;
		$category = $wpdb->get_results( "SELECT * FROM {$ipt_fsqm_info['category_table']}" );
		return $category;
	}

	public static function create_category( $name, $description = '' ) {
		global $wpdb, $ipt_fsqm_info;
		$name = strip_tags( $name );
		$wpdb->insert( $ipt_fsqm_info['category_table'], array(
			'name' => $name,
			'description' => $description,
		), '%s' );
		$cat_id = $wpdb->insert_id;
		do_action( 'ipt_fsqm_form_category_created', $cat_id );
		return $cat_id;
	}

	public static function update_category( $id, $name, $description = '' ) {
		global $wpdb, $ipt_fsqm_info;
		$name = strip_tags( $name );
		$return = $wpdb->update( $ipt_fsqm_info['category_table'], array(
			'name' => $name,
			'description' => $description,
		), array( 'id' => $id ), '%s', '%d' );
		do_action( 'ipt_fsqm_form_category_updated', $id, $return );
		return $return;
	}

	public static function delete_categories( $ids ) {
		global $wpdb, $ipt_fsqm_info;
		if ( ! is_array( $ids ) ) {
			$ids = (array) $ids;
		}
		if ( empty( $ids ) ) {
			return false;
		}
		$ids = array_map( 'intval', $ids );
		$delete_ids = implode( ',', $ids );
		do_action( 'ipt_fsqm_form_category_deleted', $ids );
		$return = $wpdb->query( "DELETE FROM {$ipt_fsqm_info['category_table']} WHERE id IN ({$delete_ids})" );
		// Also unassign the forms
		$wpdb->query( "UPDATE {$ipt_fsqm_info['form_table']} SET category = 0 WHERE category IN ({$delete_ids})" );
		return $return;
	}

	public static function get_existing_submission_users_for_select() {
		global $wpdb, $ipt_fsqm_info;
		$user_ids = $wpdb->get_col( "SELECT distinct user_id FROM {$ipt_fsqm_info['data_table']}" );
		$user_id_items = array();
		$user_id_items[] = array(
			'value' => 'current',
			'label' => __( 'Currently logged in user', 'ipt_fsqm' ),
		);
		foreach ( $user_ids as $uid ) {
			if ( '0' === $uid ) {
				continue;
			}
			$userdata = get_userdata( $uid );
			if ( ! is_wp_error( $userdata ) && is_object( $userdata ) ) {
				$user_id_items[] = array(
					'value' => $uid,
					'label' => $userdata->user_nicename,
				);
			} else {
				$user_id_items[] = array(
					'value' => $uid,
					'label' => sprintf( __( '(Deleted User) ID: %s', 'ipt_fsqm' ), $uid ),
				);
			}
		}
		return $user_id_items;
	}

	/*==========================================================================
	 * Encrypt & Decrypt
	 *========================================================================*/
	/**
	 * Safely encrypt a string with php-encrypt library
	 *
	 * The password is taken internally from the settings.
	 *
	 * This is backward compatible for older systems where mcrypt will be used
	 * instead.
	 *
	 * @param      string  $input_string  The input string
	 * @param      string|boolean $password The encryption password. If omitted use the generic option.
	 *
	 * @return     string  Hashed string
	 */
	public static function encrypt( $input_string, $password = false ) {
		if ( ! $password ) {
			$password = get_option( 'ipt_fsqm_key' );
		}
		// Typecast $input_string to string
		$input_string = (string) $input_string;
		$password = (string) $password;
		// Let's not catch the exception, because it will run on PHP 5.4+ anyway
		return Defuse\Crypto\Crypto::encryptWithPassword( $input_string, $password, false );
	}

	/**
	 * Safely decrypt a string with php-encription library.
	 *
	 * The password is taken internally from the settings.
	 *
	 * This is also backward compatible with both older systems and older
	 * installations. If it fails, then the legecy function with mcrypt will be
	 * used.
	 *
	 * @param      <type>  $encrypted_input_string  The encrypted input string
	 * @param      string|boolean $password The encryption password. If omitted use the generic option.
	 *
	 * @return     <type>  Decrypted string
	 */
	public static function decrypt( $encrypted_input_string, $password = false ) {
		if ( ! $password ) {
			$password = get_option( 'ipt_fsqm_key' );
		}
		try {
			return Defuse\Crypto\Crypto::decryptWithPassword( $encrypted_input_string, $password, false );
		} catch ( Exception $e ) {
			return self::decrypt_legecy( $encrypted_input_string );
		}
	}

	/**
	 * Encrypt with mcrypt.
	 *
	 * @deprecated
	 *
	 * @param      string  $input_string  The input string
	 *
	 * @return     string  Hashed value
	 */
	public static function encrypt_legecy( $input_string ) {
		$key = get_option( 'ipt_fsqm_key' );
		$iv_size = mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB );
		$iv = mcrypt_create_iv( $iv_size, MCRYPT_RAND );
		$h_key = hash( 'sha256', $key, TRUE );
		return base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, $h_key, $input_string, MCRYPT_MODE_ECB, $iv ) );
	}

	/**
	 * Decrypt with mcrypt
	 *
	 * @deprecated
	 *
	 * @param      string  $encrypted_input_string  The encrypted input string
	 *
	 * @return     string  Decrypted string, false if failed
	 */
	public static function decrypt_legecy( $encrypted_input_string ) {
		// Safe guard against new system
		if ( ! function_exists( 'mcrypt_decrypt' ) ) {
			return false;
		}
		$key = get_option( 'ipt_fsqm_key' );
		$iv_size = mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB );
		$iv = mcrypt_create_iv( $iv_size, MCRYPT_RAND );
		$h_key = hash( 'sha256', $key, TRUE );
		return trim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, $h_key, base64_decode( $encrypted_input_string ), MCRYPT_MODE_ECB, $iv ) );
	}

	/**
	 * Generate a random string.
	 *
	 * @param      integer  $char_length  The character length
	 *
	 * @return     string  Random string
	 */
	public static function generate_random_string( $char_length = 16 ) {
		return wp_generate_password( $char_length, false );
	}


	/*==========================================================================
	 * New data trackback ID with token hash
	 *========================================================================*/
	/**
	 * Get the trackback ID for a particular data.
	 *
	 * Will use token if present, if not, will fallback to mcrypt based
	 * encryption.
	 *
	 * @param      int  $data_id  The data identifier
	 *
	 * @return     string  The trackback identifier.
	 */
	public static function get_trackback_id( $data_id ) {
		global $wpdb, $ipt_fsqm_info;
		$tid = $wpdb->get_var( $wpdb->prepare( "SELECT token FROM {$ipt_fsqm_info['data_table']} WHERE id = %d", $data_id ) ); // wpcs: unprepared SQL ok
		// Backward compatibility for old data
		if ( '' == $tid ) {
			// Create a new TID
			$tid = self::generate_trackback_id();
			// Update
			$wpdb->update( $ipt_fsqm_info['data_table'], [
				'token' => $tid,
			], [
				'id' => $data_id,
			], '%s', '%d' );
		}
		return $tid;
	}

	/**
	 * Decode a trackback token for the actual ID by looking up the table.
	 *
	 * Fallsback to the legecy system by passing through the mcrypt function.
	 *
	 * @param      string  $token  The token
	 *
	 * @return     int     Data ID if found.
	 */
	public static function decode_trackback_id( $token ) {
		global $wpdb, $ipt_fsqm_info;
		$data_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$ipt_fsqm_info['data_table']} WHERE token=%s", $token ) ); // wpcs: unprepared SQL ok
		if ( $data_id ) {
			return $data_id;
		}
		return self::decrypt_legecy( $token );
	}

	/**
	 * Generate a random trackback ID to be assigned to a data through the token
	 * column.
	 *
	 * @return     string  A random unique string
	 */
	public static function generate_trackback_id() {
		return bin2hex( openssl_random_pseudo_bytes( 16 ) );
	}

	/*==========================================================================
	 * Some other functions
	 *========================================================================*/
	public static function get_current_url() {
		global $wp;
		$current_url = home_url( add_query_arg( array(), $wp->request ) );
		return $current_url;
	}

	public static function safe_parse_str( $string, &$result ) {
		if( $string === '' )
			return false;
		$result = array();

		// find the pairs "name=value"
		$pairs = explode( '&', $string );
		foreach ( $pairs as $pair ) {

			// use the original parse_str() on each element
			parse_str( $pair, $params );
			foreach ( $params as $key => $param ) {
				if ( ! isset( $result[$key] ) ) {
					$result[$key] = $param;
				} else {
					if ( is_array( $result[$key] ) ) {
						if ( is_array( $param ) ) {
							$result[$key] = self::array_merge_recursive_distinct( $result[$key], $param );
						} else {
							$result[$key][] = $param;
						}
					} else {
						$result[$key] = $param;
					}
				}
			}
		}
		return true;
	}

	// better recursive array merge function listed on the array_merge_recursive PHP page in the comments
	public static function array_merge_recursive_distinct( array $array1, array $array2 ) {
		$merged = $array1;
		foreach ( $array2 as $key => $value ) {
			if ( is_array( $value ) && isset( $merged[$key] ) && is_array( $merged[$key] ) ) {
				$merged[$key] = self::array_merge_recursive_distinct( $merged[$key], $value );
			} else {
				if ( is_numeric( $key ) && isset( $merged[$key] ) ) {
					$merged[] = $value;
				} else {
					$merged[$key] = $value;
				}
			}
		}

		return $merged;
	}

	/**
	 * Gets the valid payment selections.
	 *
	 * @return     array  The valid payment selections. All of them are set to false
	 * so that they can be enabled, disabled manually
	 */
	public static function get_valid_payment_selections() {
		return apply_filters( 'ipt_fsqm_payment_methods', array(
			'paypal_d' => false,
			'paypal_e' => false,
			'stripe' => false,
			'authorizenet' => false,
			'offline' => false,
		) );
	}

	public static function formulate_sda_items( $string ) {
		// First replace to unix new line
		$string = str_replace( "\r\n", "\n", $string );

		// Split
		$raw_options = explode( "\n", $string );
		$options = array();

		foreach ( (array) $raw_options as $r_key => $option ) {
			// Check for first empty option
			if ( 0 == $r_key ) {
				if ( preg_match( '/\[empty\]$/', $option ) ) {
					$options[] = array(
						'value' => '',
						'label' => str_replace( '[empty]', '', $option ),
					);
					continue;
				}
			}

			// Get numeric data
			$op_parts = array();
			if ( preg_match( '/(.*)\[num=(\-?(\d+|\d*\.\d+))\]/', $option, $op_parts ) ) {
				$options[] = array(
					'value' => str_replace( ' ', '__', $op_parts[1] ),
					'label' => $op_parts[1],
					'data' => array(
						'num' => $op_parts[2],
					),
				);
			} else {
				$options[] = array(
					'value' => str_replace( ' ', '__', $option ),
					'label' => $option,
				);
			}
		}
		return $options;
	}

	public static function formulate_sda_attributes( $string ) {
		$attributes = array(
			'min' => '',
			'max' => '',
			'minSize' => '',
			'maxSize' => '',
			'minCheckbox' => '',
			'maxCheckbox' => '',
			'future' => '',
			'past' => '',
		);

		$return = array();
		$matches = array();
		foreach ( $attributes as $r_key => $r_val ) {
			if ( preg_match( '/' . $r_key . '="?([^"]+)"?/im', $string, $matches ) ) {
				$return[ $r_key ] = $matches[1];
			}
		}
		return $return;
	}

	/**
	 * Gets the user metadata.
	 *
	 * Collectively gets userdata from both meta data and user object
	 *
	 * This is a single method to get data from user table like f_name, l_name
	 * or through get_user_meta
	 *
	 * @param      string  $key      The meta or object key
	 * @param      string  $default  The default value to return if not found
	 * @param      int     $user_id  The user identifier Can be null to get current logged in user
	 *
	 * @return     string  The user metadata.
	 */
	public static function get_user_metadata( $key, $default = '', $user_id = null ) {
		// Check if user id is provided
		if ( null == $user_id ) {
			if ( ! is_user_logged_in() ) {
				return $default;
			} else {
				$user_id = get_current_user_id();
			}
		}

		// Now get userdata
		$userdata = get_userdata( $user_id );

		if ( ! $userdata ) {
			return $default;
		}

		// If it has object property
		if ( property_exists( $userdata->data, $key ) ) {
			return strip_tags( $userdata->data->{$key} );
		}

		// Doesn't exist, so check for meta
		$metadata = get_user_meta( $user_id, $key, true );

		// If empty
		if ( empty( $metadata ) ) {
			return $default;
		}

		// Compatibility with array type storage
		if ( is_array( $metadata ) && isset( $metadata['value'] ) ) {
			$metadata = $metadata['value'];
		} else if ( is_array( $metadata ) && ! isset( $metadata['value'] ) ) {
			return $default;
		}

		return strip_tags( $metadata );
	}

	/**
	 * Gets the post metadata.
	 *
	 * @param      string  $key      Meta key. The id of the post can also be
	 *                               given within this key in the format
	 *                               {id}:{key}. If not given, then considers
	 *                               the current post ID of the loop.
	 * @param      string  $default  The default value to return
	 *
	 * @return     string  The fetched post metadata. If no post ID provided and not inside loop, then returns the default value
	 */
	public static function get_post_metadata( $key, $default = '' ) {
		// Check the key parts
		$meta_key = '';
		$post_id = null;
		$key_parts = array();
		// Check if key format specifies post ID
		if ( preg_match( '/([0-9]+)\:(.*)/', $key, $key_parts ) ) {
			$meta_key = $key_parts[2];
			$post_id = $key_parts[1];
		} else {
			// Nothing to do if not in the loop
			$post_id = get_the_ID();
			if ( ! $post_id ) {
				return $default;
			}
			$meta_key = $key;
		}

		// Now retrieve the post meta
		$postmeta = get_post_meta( $post_id, $meta_key, true );
		if ( ! $postmeta ) {
			return $default;
		}
		return $postmeta;
	}

	/**
	 * Gets the request parameter.
	 *
	 * @param      string  $key      The query parameter
	 * @param      string  $default  The default value to return if not found
	 *
	 * @return     string  The request parameter.
	 */
	public static function get_request_parameter( $key, $default = '' ) {
		// If not request set
		if ( ! isset( $_REQUEST[ $key ] ) || empty( $_REQUEST[ $key ] ) ) {
			return $default;
		}

		// Set so process it
		return strip_tags( (string) wp_unslash( $_REQUEST[ $key ] ) );
	}

	/**
	 * Gets the request parameter for mcq.
	 *
	 * @param      string  $key      The metakey
	 * @param      array   $default  The default
	 * @param      array   $options  The options
	 *
	 * @return     array   The request parameter for mcq.
	 */
	public static function get_request_parameter_for_mcq( $key, $default = array(), $options = array() ) {
		// If not request set
		if ( ! isset( $_REQUEST[ $key ] ) || empty( $_REQUEST[ $key ] ) ) {
			return $default;
		}

		// Set so process it
		$metadata = wp_unslash( $_REQUEST[ $key ] );

		// If empty
		if ( empty( $metadata ) ) {
			return $default;
		}

		// Not empty so let's do it
		$selected_values = array();

		// If array
		if ( is_array( $metadata ) ) {
			// Nothing necessary
		// If string
		} else {
			// Split
			$metadata = explode( "\n", $metadata );
		}

		// Search
		foreach ( $metadata as $key => $val ) {
			$o_key = array_search( $val, $options );
			if ( false !== $o_key ) {
				$selected_values[] = "$o_key";
			}
		}

		if ( empty( $selected_values ) ) {
			return $default;
		} else {
			return $selected_values;
		}
	}

	/**
	 * Gets the user metavalues for mcq.
	 *
	 * @param      string  $key      The metakey
	 * @param      array   $default  The default ones to return if not found
	 * @param      int     $user_id  The user identifier ( optional ) use
	 *                               current user if null provided
	 *
	 * @return     array   The user metavalues for mcq.
	 */
	public static function get_user_metavalues_for_mcq( $key, $default = array(), $options = array(), $user_id = null ) {
		// Check if user id is provided
		if ( null == $user_id ) {
			if ( ! is_user_logged_in() ) {
				return $default;
			} else {
				$user_id = get_current_user_id();
			}
		}

		// Now get userdata
		$userdata = get_userdata( $user_id );
		$return = $default;

		if ( ! $userdata ) {
			return $default;
		}

		// If it has object property
		if ( property_exists( $userdata->data, $key ) ) {
			$metadata = strip_tags( $userdata->data->{$key} );
		// Nothing, so check user meta
		} else {
			$metadata = get_user_meta( $user_id, $key, true );
		}

		// If empty
		if ( empty( $metadata ) ) {
			return $default;
		}

		// Not empty so let's do it
		$selected_values = array();

		// If array
		if ( is_array( $metadata ) ) {
			// Nothing necessary
		// If string
		} else {
			// Split
			$metadata = explode( "\n", $metadata );
		}

		// Search
		foreach ( $metadata as $key => $val ) {
			$o_key = array_search( $val, $options );
			if ( false !== $o_key ) {
				$selected_values[] = "$o_key";
			}
		}

		if ( empty( $selected_values ) ) {
			return $default;
		} else {
			return $selected_values;
		}
	}

	/*==========================================================================
	 * Chart Helper - For Compatibility
	 *========================================================================*/
	/**
	 * Gets the chart elements.
	 *
	 * @see IPT_EForm_Core_Shortcodes::get_chart_elements
	 *
	 * @param      int  $form_id  The form identifier
	 *
	 * @return     array  The chart elements.
	 * @codeCoverageIgnore
	 */
	public static function get_chart_elements( $form_id ) {
		return IPT_EForm_Core_Shortcodes::get_chart_elements( $form_id );
	}

	/**
	 * @see IPT_EForm_Core_Shortcodes::get_default_chart_n_toggle
	 *
	 * @codeCoverageIgnore
	 */
	public static function get_default_chart_n_toggle( $mcqs = array(), $pinfos = array() ) {
		return IPT_EForm_Core_Shortcodes::get_default_chart_n_toggle( $mcqs, $pinfos );
	}

	/**
	 * @see IPT_EForm_Core_Shortcodes::get_chart_type_n_toggles
	 *
	 * @codeCoverageIgnore
	 */
	public static function get_chart_type_n_toggles() {
		return IPT_EForm_Core_Shortcodes::get_chart_type_n_toggles();
	}

	/**
	 * @see IPT_EForm_Core_Shortcodes::get_pinfo_chart_elements
	 *
	 * @codeCoverageIgnore
	 */
	public static function get_pinfo_chart_elements() {
		return IPT_EForm_Core_Shortcodes::get_pinfo_chart_elements();
	}
}
