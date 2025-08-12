<?php
/**
 * The common Front UI class for eForm
 *
 * This is not supposed to be used directly rather all types of themes should
 * extend this and override needed methods.
 *
 * @package    eForm - WordPress Form Builder
 * @subpackage UI\Front
 * @author     Swashata Ghosh <swashata@wpquark.com>
 */
class IPT_Plugin_UIF_Front extends IPT_Plugin_UIF_Base {
	/**
	 * Default Messages
	 *
	 * Shortcut to all the messages
	 *
	 * @var array All the default messages
	 */
	public $default_messages = array();

	/**
	 * The instance variable
	 *
	 * This is a singleton class and we are going to use this
	 * for getting the only instance
	 */
	protected static $instance = false;

	/*==========================================================================
	 * System API
	 *========================================================================*/

	protected function __construct() {
		$this->default_messages = array(
			'pwd_reveal' => __( 'Click to show password', 'ipt_fsqm' ),
			'ajax_loader' => __( 'Please Wait', 'ipt_fsqm' ),
			'timer' => array(
				'Days' => __( 'Days', 'ipt_fsqm' ),
				'Hours' => __( 'Hours', 'ipt_fsqm' ),
				'Minutes' => __( 'Minutes', 'ipt_fsqm' ),
				'Seconds' => __( 'Seconds', 'ipt_fsqm' ),
			),
			'messages' => array(
				'green' => __( 'Success', 'ipt_fsqm' ),
				'okay' => __( 'Success', 'ipt_fsqm' ),
				'update' => __( 'Updated', 'ipt_fsqm' ),
				'yellow' => __( 'Updated', 'ipt_fsqm' ),
				'red' => __( 'Error', 'ipt_fsqm' ),
				'error' => __( 'Error', 'ipt_fsqm' ),
			),
			'ccpl' => array(
				'number' => _x( '•••• •••• •••• ••••', 'ccplaceholder', 'ipt_fsqm' ),
				'name' => _x( 'Full Name', 'ccplaceholder', 'ipt_fsqm' ),
				'expiry' => _x( '••/••', 'ccplaceholder', 'ipt_fsqm' ),
				'cvc' => _x( '•••', 'ccplaceholder', 'ipt_fsqm' ),
			),
			'ccmsg' => array(
				'validDate' => _x( "valid\nthru", 'ccmessage', 'ipt_fsqm' ),
				'monthYear' => _x( "mm/yyyy", 'ccmessage', 'ipt_fsqm' ),
			),
			'uploader' => array(
				'select' => __( 'Select files', 'ipt_fsqm' ),
				'dragdrop' => __( 'Drag \'n Drop files here', 'ipt_fsqm' ),
				'start' => __( 'Start All Uploads', 'ipt_fsqm' ),
				'cancel' => __( 'Cancel All Uploads', 'ipt_fsqm' ),
				'delete' => __( 'Delete Selected', 'ipt_fsqm' ),
				'processing_singular' => __( 'Processing&hellip;', 'ipt_fsqm' ),
				'start_singular' => __( 'Start', 'ipt_fsqm' ),
				'cancel_singular' => __( 'Cancel', 'ipt_fsqm' ),
				'error_singular' => __( 'Error', 'ipt_fsqm' ),
				'delete_singular' => __( 'Delete', 'ipt_fsqm' ),
				'messages' => array(
					'maxNumberOfFiles' => __( 'Maximum number of files exceeded', 'ipt_fsqm' ),
					'acceptFileTypes' => __( 'File type not allowed', 'ipt_fsqm' ),
					'maxFileSize' => __( 'File is too large', 'ipt_fsqm' ),
					'minFileSize' => __( 'File is too small', 'ipt_fsqm' ),
				),
			),
			'validationEngine' => array(
				'required' => array(
					'alertText' =>  __( '* This field is required', 'ipt_fsqm' ),
					'alertTextCheckboxMultiple' =>  __( '* Please select an option', 'ipt_fsqm' ),
					'alertTextCheckboxe' =>  __( '* This checkbox is required', 'ipt_fsqm' ),
					'alertTextDateRange' =>  __( '* Both date range fields are required', 'ipt_fsqm' ),
				),
				'requiredInFunction' => array(
					'alertText' =>  __( '* Incorrect answer. The correct answer is ', 'ipt_fsqm' )
				),
				'requiredSignature' => array(
					'alertText' => __( '* Please sign here', 'ipt_fsqm' ),
				),
				'noMinSlider' => array(
					'alertText' => __( '* Please set a value', 'ipt_fsqm' ),
				),
				'inputMask' => [
					'alertText' => __( 'Incomplete value', 'ipt_fsqm' ),
				],
				'ccValidation' => array(
					'number' => __( '* Invalid card number', 'ipt_fsqm' ),
					'type' => __( '* Unknown card type', 'ipt_fsqm' ),
					'expiry' => __( '* Invalid expiry date', 'ipt_fsqm' ),
					'cvc' => __( '* Invalid CVC number', 'ipt_fsqm' ),
					'name' => __( '* Please enter your full name', 'ipt_fsqm' ),
				),
				'dateRange' => array(
					'alertText' =>  __( '* Invalid ', 'ipt_fsqm' ),
					'alertText2' =>  __( 'Date Range', 'ipt_fsqm' ),
				),
				'dateTimeRange' => array(
					'alertText' =>  __( '* Invalid ', 'ipt_fsqm' ),
					'alertText2' =>  __( 'Date Time Range', 'ipt_fsqm' ),
				),
				'minSize' => array(
					'alertText' =>  __( '* Minimum ', 'ipt_fsqm' ),
					'alertText2' =>  __( ' characters required', 'ipt_fsqm' ),
				),
				'maxSize' => array(
					'alertText' =>  __( '* Maximum ', 'ipt_fsqm' ),
					'alertText2' =>  __( ' characters allowed', 'ipt_fsqm' ),
				),
				'groupRequired' => array(
					'alertText' =>  __( '* You must fill one of the following fields', 'ipt_fsqm' ),
				),
				'min' => array(
					'alertText' =>  __( '* Minimum value is ', 'ipt_fsqm' ),
				),
				'max' => array(
					'alertText' =>  __( '* Maximum value is ', 'ipt_fsqm' ),
				),
				'past' => array(
					'alertText' =>  __( '* Date prior to ', 'ipt_fsqm' ),
				),
				'future' => array(
					'alertText' =>  __( '* Date past ', 'ipt_fsqm' ),
				),
				'maxCheckbox' => array(
					'alertText' =>  __( '* Maximum ', 'ipt_fsqm' ),
					'alertText2' =>  __( ' option(s) allowed', 'ipt_fsqm' ),
				),
				'minCheckbox' => array(
					'alertText' =>  __( '* Please select ', 'ipt_fsqm' ),
					'alertText2' =>  __( ' option(s)', 'ipt_fsqm' ),
				),
				'equals' => array(
					'alertText' =>  __( '* Fields do not match', 'ipt_fsqm' ),
				),
				'creditCard' => array(
					'alertText' =>  __( '* Invalid credit card number', 'ipt_fsqm' ),
				),
				'phone' => array(
					// credit => jquery.h5validate.js / orefalo
					'regex' => "/^([\+][0-9]{1,3}[\ \.\-])?([\(]{1}[0-9]{2,6}[\)])?([0-9\ \.\-\/]{3,20})((x|ext|extension)[\ ]?[0-9]{1,4})?$/",
					'alertText' =>  __( '* Invalid phone number', 'ipt_fsqm' ),
				),
				'email' => array(
					// HTML5 compatible email regex ( http =>//www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
					'regex' => "/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/",
					'alertText' =>  __( '* Invalid email address', 'ipt_fsqm' ),
				),
				'integer' => array(
					'regex' => "/^[\-\+]?\d+$/",
					'alertText' =>  __( '* Not a valid integer', 'ipt_fsqm' ),
				),
				'number' => array(
					// Number, including positive, negative, and floating decimal. credit => orefalo
					'regex' => "/^[\-\+]?((([0-9]{1,3})([,][0-9]{3})*)|([0-9]+))?([\.]([0-9]+))?$/",
					'alertText' =>  __( '* Invalid floating decimal number', 'ipt_fsqm' ),
				),
				'date' => array(
					// Check if date is valid by leap year
					'alertText' =>  __( '* Invalid date, must be in YYYY-MM-DD format', 'ipt_fsqm' ),
				),
				'ipv4' => array(
					'regex' => "/^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/",
					'alertText' =>  __( '* Invalid IP address', 'ipt_fsqm' ),
				),
				'url' => array(
					'regex' => "/^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i",
					'alertText' =>  __( '* Invalid URL', 'ipt_fsqm' ),
				),
				'onlyNumberSp' => array(
					'regex' => "/^[0-9\ ]+$/",
					'alertText' =>  __( '* Numbers only', 'ipt_fsqm' ),
				),
				'onlyLetterSp' => array(
					'regex' => "/^[a-zA-Z\ \']+$/",
					'alertText' =>  __( '* Letters only', 'ipt_fsqm' ),
				),
				'onlyLetterNumber' => array(
					'regex' => "/^[0-9a-zA-Z]+$/",
					'alertText' =>  __( '* No spaces or special characters allowed', 'ipt_fsqm' ),
				),
				'onlyLetterNumberSp' => array(
					'regex' => "/^[0-9a-zA-Z\ ]+$/",
					'alertText' =>  __( '* Only letters, number and spaces allowed', 'ipt_fsqm' ),
				),
				'zipCode' => array(
					'regex' => "/^[0-9a-zA-Z\ \-]+$/",
					'alertText' => __( '* Not a valid zip code. Only numbers, letters and dashes are allowed.', 'ipt_fsqm' ),
				),
				'noSpecialCharacter' => array(
					'regex' => "/^[0-9a-zA-Z\ \.\,\?\\\"\']+$/",
					'alertText' => __( '* No special characters allowed', 'ipt_fsqm' ),
				),
				'personName' => array(
					'regex' => "/^[a-zA-Z\ \.]+$/",
					'alertText' => __( 'Valid name only, no special characters except dots and single quote for salutation', 'ipt_fsqm' ),
				),
				//tls warning =>homegrown not fielded
				'dateFormat' => array(
					'regex' => "/^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(?:(?:0?[1-9]|1[0-2])(\/|-)(?:0?[1-9]|1\d|2[0-8]))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(0?2(\/|-)29)(\/|-)(?:(?:0[48]00|[13579][26]00|[2468][048]00)|(?:\d\d)?(?:0[48]|[2468][048]|[13579][26]))$/",
					'alertText' =>  __( '* Invalid Date', 'ipt_fsqm' ),
				),
				//tls warning =>homegrown not fielded
				'dateTimeFormat' => array(
					'regex' => "/^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/",
					'alertText' =>  __( '* Invalid Date or Date Format', 'ipt_fsqm' ),
					'alertText2' =>  __( 'Expected Format => ', 'ipt_fsqm' ),
					'alertText3' =>  __( 'mm/dd/yyyy hh =>mm =>ss AM|PM or ', 'ipt_fsqm' ),
					'alertText4' =>  __( 'yyyy-mm-dd hh =>mm =>ss AM|PM', 'ipt_fsqm' ),
				),
			),
			'got_it' => __( 'Got it', 'ipt_fsqm' ),
			'help' => __( 'Help!', 'ipt_fsqm' ),
		);
		// Add the filters for the richtext
		add_filter( 'ipt_uif_richtext', 'wptexturize'        );
		add_filter( 'ipt_uif_richtext', 'convert_smilies'    );
		add_filter( 'ipt_uif_richtext', 'convert_chars'      );
		add_filter( 'ipt_uif_richtext', 'wpautop'            );
		add_filter( 'ipt_uif_richtext', 'shortcode_unautop'  );
		add_filter( 'ipt_uif_richtext', 'do_shortcode', 11   );
		add_filter( 'ipt_uif_richtext', 'prepend_attachment' );
		global $wp_embed;
		if ( class_exists( 'WP_Embed' ) && $wp_embed instanceof WP_Embed ) {
			add_filter( 'ipt_uif_richtext', array( $wp_embed, 'run_shortcode' ), 8 );
			add_filter( 'ipt_uif_richtext', array( $wp_embed, 'autoembed' ), 8 );
		}
		parent::__construct();
	}

	/*==========================================================================
	 * FILE DEPENDENCIES
	 *========================================================================*/
	public function enqueue_all( $api = '' ) {
		$this->enqueue();
		$this->enqueue_datatables();
		$this->enqueue_draggable_sortable();
		$this->enqueue_guest_blog();
		$this->enqueue_locationpicker( $api );
		$this->enqueue_mask_js();
		$this->enqueue_math_expr();
		$this->enqueue_payment();
		$this->enqueue_select();
		$this->enqueue_signature();
		$this->enqueue_slider();
		$this->enqueue_timer();
		$this->enqueue_ui_slider();
		$this->enqueue_uploader();
	}
	/**
	 * Enqueues Scripts and Style
	 *
	 * @param      array  $ignore_css      The ignore css
	 * @param      array  $ignore_js       The ignore js
	 * @param      array  $additional_css  The additional css
	 * @param      array  $additional_js   The additional js
	 * @param      string  $static_location  The URL to the static admin directory
	 * @param      string  $version          Version of the scripts/stylesheets
	 */
	public function enqueue( $ignore_css = array(), $ignore_js = array(), $additional_css = array(), $additional_js = array(), $additional_localize = array() ) {
		// Some shortcut URLs
		$static_location = $this->static_location;
		$bower_components = $this->bower_components;
		$bower_builds = $this->bower_builds;
		$version = IPT_FSQM_Loader::$version;

		// Styles
		$styles = array(
			'ipt-plugin-uif-animate-css' => array( $static_location . 'front/css/animations/animate.css', array() ),
			'ipt-js-tooltipster' => array( $bower_components . 'tooltipster/dist/css/tooltipster.bundle.min.css', array() ),
			'ipt-js-tooltipster-theme' => array( $bower_components . 'tooltipster/dist/css/plugins/tooltipster/sideTip/themes/tooltipster-sideTip-shadow.min.css', array( 'ipt-js-tooltipster' ) ),
		);
		// Add the additionals
		$styles = $styles + $additional_css;
		// Scripts
		$scripts = array(
			'jquery-ui-autocomplete' => array(),
			'ipt-plugin-uif-keyboard' => array( $bower_components . 'keyboard/dist/js/jquery.keyboard.min.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-widget' ) ),
			'ipt-plugin-uif-validation-engine' => array( $static_location . 'front/js/jquery.validationEngine.min.js', array( 'jquery' ) ),
			'ipt-plugin-uif-validation-engine-lang' => array( $static_location . 'front/js/jquery.validationEngine-all.min.js', array( 'jquery', 'ipt-plugin-uif-validation-engine' ) ),
			'ipt-plugin-uif-nivo-slider' => array( $bower_components . 'nivo-slider/jquery.nivo.slider.pack.js', array( 'jquery' ) ),
			'ipt-plugin-uif-typewatch' => array( $bower_builds . 'jquery-typewatch/js/jquery.typewatch.min.js', array( 'jquery' ) ),
			'waypoints' => array( $bower_components . 'waypoints/lib/jquery.waypoints.min.js', array( 'jquery' ) ),
			'count-up' => array( $bower_components . 'countUp.js/dist/countUp.min.js', array() ),
			'jquery-tooltipster' => array( $bower_components . 'tooltipster/dist/js/tooltipster.bundle.min.js', array( 'jquery' ) ),
			'ba-throttle-debounce' => array( $bower_components . 'jquery-throttle-debounce/jquery.ba-throttle-debounce.min.js', array( 'jquery' ) ),
			'ipt-plugin-uif-front-js' => array( $static_location . 'front/js/jquery.ipt-plugin-uif-front.min.js', array( 'jquery', 'jquery-ui-widget', 'jquery-ui-mouse', 'jquery-ui-button', 'jquery-touch-punch', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable', 'jquery-ui-datepicker', 'jquery-ui-dialog', 'jquery-ui-tabs', 'jquery-ui-spinner', 'jquery-ui-progressbar', 'jquery-timepicker-addon', 'jquery-print-element', 'jquery-mousewheel', 'jquery-ui-autocomplete', 'ipt-plugin-uif-keyboard', 'ipt-plugin-uif-validation-engine', 'ipt-plugin-uif-validation-engine-lang', 'ipt-plugin-uif-nivo-slider', 'ipt-plugin-uif-typewatch', 'count-up', 'jquery-tooltipster', 'ba-throttle-debounce' ) ),
		);
		// And the additionals
		$scripts = $scripts + $additional_js;
		// Script Localize
		$scripts_localize = array(
			'ipt-plugin-uif-validation-engine-lang' => array(
				'object_name' => 'iptPluginValidationEn',
				'l10n' => array(
					'L10n' => $this->default_messages['validationEngine'],
				),
			),
			'ipt-plugin-uif-front-js' => array(
				'object_name' => 'iptPluginUIFFront',
				'l10n' => array(
					'location' => $static_location,
					'version' => $version,
					'L10n' => $this->default_messages,
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'countries' => IPT_FSQM_Form_Elements_Static::get_countries(),
				),
			),
		);
		// And the additionals
		$scripts_localize = $scripts_localize + $additional_localize;

		parent::enqueue( $ignore_css, $ignore_js, $styles, $scripts, $scripts_localize );
	}

	/**
	 * Enqueue assets for payment JS
	 */
	public function enqueue_payment() {
		wp_enqueue_script( 'jquery-payment', $this->bower_components . 'jquery.payment/lib/jquery.payment.min.js', array( 'jquery' ), $this->version, true );
	}

	/**
	 * Enqueue assets for Input masking
	 */
	public function enqueue_mask_js() {
		wp_enqueue_script( 'jquery-inputmask', $this->bower_components . 'inputmask/dist/min/jquery.inputmask.bundle.min.js', array( 'jquery' ), $this->version, true );
	}

	/**
	 * Enqueue the js expression evaluator asset from bower_builds directory
	 *
	 * Call this before calling <code>math_input</code> otherwise the JS won't
	 * work.
	 */
	public function enqueue_math_expr() {
		// Enqueue
		wp_enqueue_script( 'js-expression-evaluator', $this->bower_builds . 'expr-eval/dist/bundle.min.js', array(), $this->version, true );
	}

	/*==========================================================================
	 * HTML UI ElEMENTS
	 *========================================================================*/
	/**
	 * Prints a group of radio items for a single HTML name
	 *
	 * @param      string          $name         The HTML name of the radio
	 *                                           group
	 * @param      array           $items        Associative array of all the
	 *                                           radio items.
	 * <pre>
	 * [
	 * 		[
	 * 			'value' => 'foo',
	 * 			'label' => 'bar',
	 * 			'disabled' => false,
	 * 			'data' => [
	 * 				'condid' => 'foo-bar',
	 * 				'key' => 'val',
	 * 			],
	 * 		],
	 * 		...
	 * ]
	 * </pre>
	 * @param      string          $checked      The value of the checked item
	 * @param      array           $validation   Array of the validation clauses
	 * @param      int             $column       Number of columns 1|2|3|4
	 * @param      bool            $conditional  Whether the group represents
	 *                                           conditional questions. This
	 *                                           will wrap it inside a
	 *                                           conditional div which will be
	 *                                           fired using jQuery. It does not
	 *                                           populate or create anything
	 *                                           inside the conditional div. The
	 *                                           id of the conditional divs
	 *                                           should be given inside the data
	 *                                           value of the items in the form
	 *                                           condID => 'ID_OF_DIV'
	 * @param      bool            $disabled     Set TRUE if all the items are
	 *                                           disabled
	 * @param      integer|string  $icon         The icon
	 * @return     void
	 */
	public function radios( $name, $items, $checked, $validation = false, $column = 2, $conditional = false, $disabled = false, $icon = 0xe18e, $button_style = false ) {
		if ( !is_array( $items ) || empty( $items ) ) {
			return;
		}
		$validation_class = $this->convert_validation_class( $validation );

		if ( !is_array( $checked ) ) {
			$checked = (array) $checked;
		}


		$id_prefix = $this->generate_id_from_name( $name );

		if ( $conditional == true ) {
			echo '<div class="ipt_uif_conditional_input">';
		}

		$items = $this->standardize_items( $items );

		$icon_attr = '';
		if ( $icon != '' && $icon != 'none' ) {
			$icon_attr = ' data-labelcon="&#x' . dechex( $icon ) . ';"';
		}

		$html_classes = [ trim( $validation_class ), 'ipt_uif_radio', 'with-gap' ];
		if ( $button_style ) {
			$html_classes[] = 'as-eform-button';
		}

		foreach ( (array) $items as $item ) :
		$data = isset( $item['data'] ) ? $item['data'] : '';
		$data_attr = $this->convert_data_attributes( $data );
		$id = $this->generate_id_from_name( '', $id_prefix . '_' . $item['value'] );
		$disabled_item = ( $disabled == true || ( isset( $item['disabled'] ) && true == $item['disabled'] ) ) ? 'disabled' : '';
?>
<div class="ipt_uif_label_column column_<?php echo $column; ?>">
	<input<?php echo in_array( $item['value'], $checked, true ) ? ' checked="checked"' : ''; ?>
		<?php echo $data_attr; ?>
		<?php echo $this->convert_state_to_attribute( $disabled_item ); ?>
		type="radio"
		class="<?php echo implode( ' ', $html_classes ); ?>"
		name="<?php echo $name; ?>"
		id="<?php echo $id; ?>"
		value="<?php echo $item['value']; ?>" />
	<label class="eform-label-with-tabindex" tabindex="0" for="<?php echo $id; ?>"<?php echo $icon_attr; ?>>
		 <?php echo apply_filters( 'ipt_uif_label', $item['label'] ); ?>
	</label>
</div>
			<?php
		endforeach;
		$this->clear();
		if ( $conditional == true ) {
			echo '</div>';
		}
	}

	/**
	 * Creates smiley rating
	 *
	 * @param      string   $name                  The name
	 * @param      string   $value                 The value
	 * @param      boolean  $enabled               If enabled
	 * @param      boolean  $required              If required
	 * @param      array    $labels                The labels
	 * @param      array    $classes               The classes
	 * @param      array    $data                  The data
	 * @param      boolean  $feedback              The feedback
	 * @param      string   $feedback_name         The feedback name
	 * @param      string   $feedback_value        The feedback value
	 * @param      string   $feedback_placeholder  The feedback placeholder
	 * @param      boolean  $reverse               The reverse
	 */
	public function smiley_rating( $name, $value, $enabled, $required, $labels = array(), $classes = array(), $data = array(), $feedback = true, $feedback_name = '', $feedback_value = '', $feedback_placeholder = '', $reverse = false ) {
		$value = (string) $value;
		$enabled = wp_parse_args( (array) $enabled, array(
			'frown' => true,
			'sad' => true,
			'neutral' => true,
			'happy' => true,
			'excited' => true,
		) );
		$data = wp_parse_args( $data, array(
			'frown' => array(),
			'sad' => array(),
			'neutral' => array(),
			'happy' => array(),
			'excited' => array(),
		) );
		$smileys = array(
			'frown',
			'sad',
			'neutral',
			'happy',
			'excited',
		);
		if ( $reverse ) {
			$smileys = array_reverse( $smileys, true );
		}
		$classes = (array) $classes;
		$classes = array_merge( $classes, array(
			'ipt_uif_rating',
			'ipt_uif_rating_smiley',
		) );
		if ( in_array( $value, $smileys ) && $feedback ) {
			$classes[] = 'ipt_uif_smiley_feedback_active';
		}
		if ( '' == $feedback_name ) {
			$feedback_name = $name . '[feedback]';
		}
		?>
<div class="<?php echo implode( ' ', $classes ); ?>">
	<div class="ipt_uif_smiley_rating_inner">
		<?php foreach ( $smileys as $smiley ) : ?>
			<?php
				// Not needed if this smiley is not enabled
				if ( false == $enabled[ $smiley ] ) {
					continue;
				}
				// Get the ID
				$id = $this->generate_id_from_name( $name ) . '_' . $smiley;
				// Add the label to the data attribute
				$data[ $smiley ]['label'] = isset( $labels[ $smiley ] ) ? $labels[ $smiley ] : ucfirst( $smiley );
				// The basics of the input
				$input = '<input ' .
					'type="radio" ' .
					'name="' . esc_attr( $name ) . '" ' .
					'id="' . $id . '" ' .
					'value="' . $smiley . '" ';
				// If checked
				if ( $value === (string) $smiley ) {
					$input .= 'checked="checked" ';
				}
				// Add classes as necessary
				$input .= 'class="ipt_uif_radio ipt_uif_smiley_rating_radio ipt_uif_smiley_rating_radio_' . $smiley;
				if ( $required ) {
					$input .= ' check_me validate[required]';
				}
				$input .= '" ';
				// Add data attributes
				$input .= $this->convert_data_attributes( $data[ $smiley ] );
				// Close
				$input .= ' />';
				// Label tooltip
				$label_tooltip = '';
				if ( isset( $labels[ $smiley ] ) ) {
					$label_tooltip = ' title="' . esc_attr( $labels[ $smiley ] ) . '" class="ipt_uif_tooltip"';
				}
			?>
			<?php echo $input; ?>
			<label <?php echo $label_tooltip; ?> for="<?php echo $id; ?>"></label>
		<?php endforeach; ?>
		<?php $this->clear(); ?>
	</div>
	<?php if ( $feedback ) : ?>
	<div class="ipt_uif_smiley_rating_feedback_wrap">
		<?php $this->textarea( $feedback_name, $feedback_value, $feedback_placeholder, 'normal', array( 'ipt_uif_smiley_rating_feedback' ), false, false, false, 'thumbs_up_down' ); ?>
	</div>
	<?php endif; ?>
</div>
		<?php
	}

	public function rating( $name, $value, $max, $required, $style = 'star', $low = '', $high = '' ) {
		$value = (string) $value;
?>
<div class="ipt_uif_rating ipt_uif_rating_<?php echo esc_attr( $style ); ?>">
	<?php if ( $low != '' ) : ?>
	<h6 class="ipt_uif_rating_heading ipt_uif_rating_label_low"><span class="ipt_uif_span"><?php echo $low; ?></span></h6>
	<?php endif; ?>
	<?php for ( $i = 1; $i <= (int) $max; $i++ ) : ?>
	<?php $id = $this->generate_id_from_name( $name ) . '_' . $i; ?>
	<input<?php echo ( $value === (string) $i ) ? ' checked="checked"' : ''; ?>
		type="radio"
		class="<?php if ( $required ) : ?>check_me validate[required]<?php endif; ?> ipt_uif_radio"
		name="<?php echo $name; ?>"
		id="<?php echo $id; ?>"
		value="<?php echo $i; ?>" />
	<label for="<?php echo $id; ?>"></label>
	<?php endfor; ?>
	<?php if ( $high != '' ) : ?>
	<h6 class="ipt_uif_rating_heading ipt_uif_rating_label_high"><span class="ipt_uif_span"><?php echo $high; ?></span></h6>
	<?php endif; ?>
</div>
		<?php
	}

	/**
	 * Prints a group of checkbox items for a single HTML name
	 *
	 * @param      string          $name         The HTML name of the radio
	 *                                           group
	 * @param      array           $items        Associative array of all the
	 *                                           checkbox items.
	 * <pre>
	 * [
	 * 		[
	 * 			'value' => 'foo',
	 * 			'label' => 'bar',
	 * 			'disabled' => false,
	 * 			'data' => [
	 * 				'condid' => 'foo-bar',
	 * 				'key' => 'val',
	 * 			],
	 * 		],
	 * 		...
	 * ]
	 * </pre>
	 * @param      string          $checked      The value of the checked item
	 * @param      array           $validation   Array of the validation clauses
	 * @param      int             $column       Number of columns 1|2|3|4s
	 * @param      bool            $conditional  Whether the group represents
	 *                                           conditional questions. This
	 *                                           will wrap it inside a
	 *                                           conditional div which will be
	 *                                           fired using jQuery. It does not
	 *                                           populate or create anything
	 *                                           inside the conditional div. The
	 *                                           id of the conditional divs
	 *                                           should be given inside the data
	 *                                           value of the items in the form
	 *                                           condID => 'ID_OF_DIV'
	 * @param      bool            $disabled     Set TRUE if all the items are
	 *                                           disabled
	 * @param      integer|string  $icon         The icon
	 * @return     void
	 */
	public function checkboxes( $name, $items, $checked, $validation = false, $column = 2, $conditional = false, $disabled = false, $icon = 0xe18e, $button_style = false ) {
		if ( !is_array( $items ) || empty( $items ) ) {
			return;
		}

		$validation_class = $this->convert_validation_class( $validation );

		if ( !is_array( $checked ) ) {
			$checked = (array) $checked;
		}

		$id_prefix = $this->generate_id_from_name( $name );

		if ( $conditional == true ) {
			echo '<div class="ipt_uif_conditional_input">';
		}

		$items = $this->standardize_items( $items );

		$icon_attr = '';
		if ( $icon != '' && $icon != 'none' ) {
			$icon_attr = ' data-labelcon="&#x' . dechex( $icon ) . ';"';
		}

		foreach ( (array) $items as $item ) :
			$data = isset( $item['data'] ) ? $item['data'] : '';
			$data_attr = $this->convert_data_attributes( $data );
			$id = $this->generate_id_from_name( '', $id_prefix . '_' . $item['value'] );
			$disabled_item = ( $disabled == true || ( isset( $item['disabled'] ) && true == $item['disabled'] ) ) ? 'disabled' : '';
			$html_classes = [ trim( $validation_class ), 'ipt_uif_checkbox', 'filled-in' ];
			if ( $button_style ) {
				$html_classes[] = 'as-eform-button';
			}
			if ( isset( $item['class'] ) ) {
				$html_classes[] = $item['class'];
			}
			?>
			<div class="ipt_uif_label_column column_<?php echo $column; ?>">
				<input<?php echo in_array( $item['value'], (array) $checked, true ) ? ' checked="checked"' : ''; ?>
					<?php echo $data_attr; ?>
					<?php echo $this->convert_state_to_attribute( $disabled_item ); ?>
					type="checkbox"
					class="<?php echo implode( ' ', $html_classes ); ?>"
					name="<?php echo $name; ?>" id="<?php echo $id; ?>"
					value="<?php echo $item['value']; ?>" />
				<label class="eform-label-with-tabindex" tabindex="0" for="<?php echo $id; ?>"<?php echo $icon_attr; ?>>
					 <?php echo apply_filters( 'ipt_uif_label', $item['label'] ); ?>
				</label>
			</div>
			<?php
		endforeach;
		$this->clear();
		if ( $conditional == true ) {
			echo '</div>';
		}
	}

	public function enqueue_select() {
		wp_deregister_script( 'select2' );
		wp_enqueue_script( 'select2', $this->bower_components . 'select2/dist/js/select2.min.js', array( 'jquery' ), $this->version, true );
	}

	/**
	 * Prints a select dropdown form element
	 *
	 * @param      string   $name          The HTML name of the radio group
	 * @param      array    $items         Associative array of all the radio
	 *                                     items. array( 'value' => '', 'label'
	 *                                     => '', 'data' => array('key' =>
	 *                                     'value'[,...]), //optional HTML 5
	 *                                     data attributes inside an associative
	 *                                     array )
	 * @param      string   $selected      The value of the selected item
	 * @param      array    $validation    Array of the validation clauses
	 * @param      bool     $conditional   Whether the group represents
	 *                                     conditional questions. This will wrap
	 *                                     it inside a conditional div which
	 *                                     will be fired using jQuery. It does
	 *                                     not populate or create anything
	 *                                     inside the conditional div. The id of
	 *                                     the conditional divs should be given
	 *                                     inside the data value of the items in
	 *                                     the form condID => 'ID_OF_DIV'
	 * @param      bool     $print_select  Whether or not to print the select
	 *                                     html
	 * @param      bool     $disabled      Set TRUE if all the items are
	 *                                     disabled
	 * @param      boolean  $multiple      Whether multiple select or not
	 * @return     void
	 */
	public function select( $name, $items, $selected, $validation = false, $conditional = false, $print_select = true, $disabled = false, $multiple = false, $e_label = '', $hide_search = false, $native_select = false ) {
		if ( !is_array( $items ) || empty( $items ) ) {
			return;
		}
		// No need to Enqueue Style
		// It is added to the core CSS file
		// Compatibility with WooCommerce
		$this->enqueue_select();
		$validation_class = $this->convert_validation_class( $validation );

		$classes = array();
		$classes[] = $validation_class;
		$classes[] = 'ipt_uif_select';

		if ( $hide_search ) {
			$classes[] = 'ipt_uif_select_no_search';
		}

		if ( $native_select ) {
			$classes[] = 'ipt_uif_select_native';
		}

		if ( !is_array( $selected ) ) {
			$selected = (array) $selected;
		}

		$id = $this->generate_id_from_name( $name );

		if ( $conditional == true ) {
			echo '<div class="ipt_uif_conditional_select">';
		}

		$items = $this->standardize_items( $items );

		if ( $print_select ) {
			echo '<select class="' . implode( ' ', $classes ) . '" name="' . esc_attr( trim( $name ) ) . '" id="' . $id . '" ' . $this->convert_state_to_attribute( ( $disabled == true ) ? 'disabled' : '' ) . ( $multiple ? ' multiple="multiple"' : '' ) . ( '' != $e_label ? 'data-placeholder="' . esc_attr( $e_label ) . '" data-allow-clear="true"' : '' ) . ' data-theme="eform-material">';
		}

		foreach ( (array) $items as $item ) :
			$data = isset( $item['data'] ) ? $item['data'] : '';
		$data_attr = $this->convert_data_attributes( $data );
?>
<option value="<?php echo $item['value']; ?>"<?php if ( in_array( $item['value'], (array) $selected, true ) ) echo ' selected="selected"'; ?><?php echo $data_attr; ?>><?php echo $item['label']; ?></option>
			<?php
		endforeach;

		if ( $print_select ) {
			echo '</select>';
			$this->clear();
		}

		if ( $conditional == true ) {
			echo '</div>';
		}
	}

	/**
	 * Thumbnail Selector
	 *
	 * Emulates radio or checkboxes with nicely rendered images as labels
	 *
	 * @param      string   $name         The HTML name of the radio group
	 * @param      array    $items        Associative array of all the radio
	 *                                    items. array( 'value' => '', 'label'
	 *                                    => '', 'image' => '', 'disabled' =>
	 *                                    true|false,//optional 'data' =>
	 *                                    array('key' => 'value'[,...]),
	 *                                    //optional HTML 5 data attributes
	 *                                    inside an associative array )
	 * @param      string   $checked      The value of the checked item
	 * @param      boolean  $multi        If true, then render checkboxes,
	 *                                    otherwise radios
	 * @param      array    $validation   Array of the validation clauses
	 * @param      int      $width        Width in pixels
	 * @param      int      $height       Height in pixels
	 * @param      boolean  $caption      Whether or not to show caption
	 * @param      bool     $conditional  Whether the group represents
	 *                                    conditional questions. This will wrap
	 *                                    it inside a conditional div which will
	 *                                    be fired using jQuery. It does not
	 *                                    populate or create anything inside the
	 *                                    conditional div. The id of the
	 *                                    conditional divs should be given
	 *                                    inside the data value of the items in
	 *                                    the form condID => 'ID_OF_DIV'
	 * @param      bool     $disabled     Set TRUE if all the items are disabled
	 * @param      int      $icon         Number of columns 1|2|3|4
	 * @param      boolean  $tooltip      The tooltip
	 * @param      string   $appearance   The appearance
	 * @return     void
	 */
	public function thumbnail_select( $name, $items, $checked, $multi = false, $validation = false, $width = 100, $height = 100, $caption = true, $conditional = false, $disabled = false, $icon = 0xe18e, $tooltip = true, $appearance = 'normal' ) {
		if ( !is_array( $items ) || empty( $items ) ) {
			return;
		}

		if ( $multi == false ) {
			unset( $validation['filters'] );
		}

		$validation_class = $this->convert_validation_class( $validation );

		if ( !is_array( $checked ) ) {
			$checked = (array) $checked;
		}

		$id_prefix = $this->generate_id_from_name( $name );

		if ( $conditional == true ) {
			echo '<div class="ipt_uif_conditional_input">';
		}

		$icon_attr = '';
		if ( $icon != '' && $icon != 'none' ) {
			$icon_attr = ' data-labelcon="&#x' . dechex( $icon ) . ';"';
		}

		$label_classes = [ 'eform-label-with-tabindex' ];
		if ( $tooltip ) {
			$label_classes[] = 'ipt_uif_tooltip';
		}
		$label_classes = implode( ' ', $label_classes );

		foreach ( (array) $items as $item ) :
			$data = isset( $item['data'] ) ? $item['data'] : '';
			$data_attr = $this->convert_data_attributes( $data );
			$id = $this->generate_id_from_name( '', $id_prefix . '_' . $item['value'] );
			$disabled_item = ( $disabled == true || ( isset( $item['disabled'] ) && true == $item['disabled'] ) ) ? 'disabled' : '';
			$swh_attr = '';
			if ( $width != '' ) {
				$swh_attr .= ' width:' . $width . 'px;';
			}
			if ( $height != '' ) {
				$swh_attr .= ' max-height:' . $height . 'px;';
			}
			$wrapper_classes = array( 'ipt_uif_thumbselect_wrap', 'ipt_uif_label_column' );
			if ( 'normal' != $appearance ) {
				$wrapper_classes[] = 'ipt-eform-thumbselect-' . $appearance;
			}
			?>
<div class="<?php echo implode( ' ', $wrapper_classes ); ?>">
	<input<?php echo in_array( $item['value'], (array) $checked, true ) ? ' checked="checked"' : ''; ?>
		<?php echo $data_attr; ?>
		<?php echo $this->convert_state_to_attribute( $disabled_item ); ?>
		type="<?php echo ( $multi ? 'checkbox' : 'radio' ); ?>"
		class="<?php echo trim( $validation_class ); ?> ipt_uif_thumbselect ipt_uif_<?php echo ( $multi ? 'checkbox' : 'radio' ); ?>"
		name="<?php echo $name; ?>" id="<?php echo $id; ?>"
		value="<?php echo $item['value']; ?>" />
	<label class="<?php echo $label_classes; ?>" tabindex="0" for="<?php echo $id; ?>"<?php echo $icon_attr; ?><?php if ( $tooltip ) { ?> title="<?php echo esc_attr( strip_tags( strip_shortcodes( $item['label'] ) ) ); ?>"<?php } ?>>
		<div class="ui-widget-content">
			<div class="thumbselect-img-wrapper">
				<img class="ui-widget-content" src="<?php echo esc_attr( $item['image'] ) ?>" style="<?php echo $swh_attr; ?>" alt="<?php echo esc_attr( $item['label'] ); ?>" />
			</div>
			<?php if ( $caption ) : ?>
			<h5 class="ui-widget-header">
				<?php echo apply_filters( 'ipt_uif_label', $item['label'] ); ?>
			</h5>
			<?php endif; ?>
		</div>
	</label>
</div>
			<?php
		endforeach;
		$this->clear();
		if ( $conditional == true ) {
			echo '</div>';
		}
	}

	/**
	 * Print an estimator slider with support for mathematical formula.
	 *
	 * The slider can be either range or simple slider and it will move around
	 * as the math formula yields different results. A could of styling is
	 * possible through the $slider_style parameter.
	 *
	 * @see print_estimator_math
	 * @see print_math_input
	 *
	 * @param      string  $ui_type       The user interface type. Could be
	 *                                    <code>slider</code>(default) or
	 *                                    <code>range</code>
	 * @param      string  $slider_style  The interface style. Could be
	 *                                    <code>knob</code>(default) or
	 *                                    <code>block</code>.
	 * @param      array   $config        Configuration variables of the
	 *                                    interface. See below for default
	 *                                    options.
	 * <pre>
	 * [
	 * 		'total' => 'M0*M1+F2', // math formula for total. Used when $ui_type is slider
	 * 		'min' => 'M0*M1+F2', // math formula for min. Used when $ui_type is range
	 * 		'max' => 'M0*M1+F2', // math formula for max. Used when $ui_type is range
	 * 		'precision' => 2, // Decimal precision
	 * 		'area' => 1000, // Maximum value after which slider position will be calculated.
	 * 		'use_grouping' => true, // Whether to group number by thousand
	 * 		'separator' => ',', // thousands separator
	 * 		'decimal' => '.', // Decimal separator
	 * ]
	 * </pre>
	 * @param      array   $bubble        Configuration variable for the
	 *                                    optional bubble above the slider. See
	 *                                    below for default options.
	 * <pre>
	 * [
	 * 		'style' => 'modern', // modern | simple | none (to hide the bubble)
	 * 		'prefix' => '', // Shown before the total/max/min amount
	 * 		'suffix' => '', // Shown after the total/max/min amount
	 * 		'heading' => '', // Optional heading shown on top of the bubble
	 * 		'separator' => '/', // Separator in case of range $ui_type
	 * 		'attribute_heading' => '', // Custom heading before the attribute section
	 * 		// Additional list of attributes passed through `ipt_uif_label` filter
	 * 		// and shown below the total value
	 * 		'attributes' => [
	 * 			0 => [
	 * 				'value' => '%M0%',
	 * 				'label' => 'Attr Label',
	 * 			],
	 * 			1 => [
	 * 				'value' => 'Attr static value',
	 * 				'label' => 'Attr Label',
	 * 			],
	 * 		],
	 * ]
	 * </pre>
	 */
	public function estimator_slider( $ui_type, $slider_style, $config, $bubble ) {
		// Enqueue the math assets
		$this->enqueue_math_expr();

		// Normalize the config and bubble
		$config = wp_parse_args( $config, [
			'total' => '',
			'min' => '',
			'max' => '',
			'precision' => 2,
			'area' => 1000,
			'use_grouping' => true,
			'separator' => ',',
			'decimal' => '.',
		] );
		$bubble = wp_parse_args( $bubble, [
			'style' => 'modern',
			'prefix' => '',
			'suffix' => '',
			'heading' => '',
			'separator' => '/',
			'attribute_heading' => '',
			'attributes' => [],
		] );
		// Create the classes of the slider
		$slider_classes = [ 'eform-ui-estimator' ];
		$slider_classes[] = ( 'slider' == $ui_type ) ? 'eform-ui-estimator-slider' : 'eform-ui-estimator-range';

		// Create the bubble classes
		if ( 'none' != $bubble['style'] ) {
			$bubble_classes = [ 'eform-ui-estimator-bubble', 'eform-ui-estimator-bubble-' . $bubble['style'] ];
		}

		// Create the estimator math element helper config
		$math_config = [
			'precision' => $config['precision'],
			'options' => [
				'decimal' => $config['decimal'],
				'useGrouping' => $config['use_grouping'],
				'separator' => $config['separator'],
			],
			'noanim' => false,
		];
		?>
<div class="<?php echo esc_attr( implode( ' ', $slider_classes ) ); ?>" data-config="<?php echo esc_attr( json_encode( (object) $config ) ); ?>" data-ui-type="<?php echo esc_attr( $ui_type ); ?>">
	<div class="eform-ui-est-values">
		<?php if ( 'slider' == $ui_type ) : ?>
			<?php $this->print_math_input( '', '', 'hidden', $config['total'], $math_config, false ); ?>
		<?php else : ?>
			<?php $this->print_math_input( '', '', 'hidden', $config['min'], $math_config, false ); ?>
			<?php $this->print_math_input( '', '', 'hidden', $config['max'], $math_config, false ); ?>
		<?php endif; ?>
	</div>
	<?php if ( 'none' != $bubble ) : ?>
		<div class="<?php echo esc_attr( implode( ' ', $bubble_classes ) ); ?>">
			<?php if ( '' != $bubble['heading'] ) : ?>
				<h4 class="eform-ui-estimator-bubble-heading"><?php echo apply_filters( 'ipt_uif_label', $bubble['heading'] ); ?></h4>
			<?php endif; ?>
			<div class="eform-ui-estimator-bubble-total">
				<?php if ( 'slider' == $ui_type ) : ?>
					<?php
					$this->print_estimator_math( $bubble['prefix'], $bubble['suffix'], $config['total'], $math_config );
					?>
				<?php else : ?>
					<?php $this->print_estimator_math( $bubble['prefix'], $bubble['suffix'], $config['min'], $math_config ); ?>
					<span class="eform-ui-est-bub-separator"><?php echo $bubble['separator']; ?></span>
					<?php $this->print_estimator_math( $bubble['prefix'], $bubble['suffix'], $config['max'], $math_config ); ?>
				<?php endif; ?>
			</div>
			<?php if ( '' != $bubble['attribute_heading'] ) : ?>
				<h5 class="eform-ui-est-bub-attr-head"><?php echo apply_filters( 'ipt_uif_label', $bubble['attribute_heading'] ); ?></h5>
			<?php endif; ?>
			<?php if ( ! empty( $bubble['attributes'] ) ) : ?>
				<div class="eform-ui-estimator-bubble-attr">
					<ul class="eform-ui-est-bub-attr-list">
						<?php foreach ( (array) $bubble['attributes'] as $attr ) : ?>
							<li class="eform-ui-est-bub-attr-listitem">
								<span class="eform-ui-est-bub-attr-label"><?php echo apply_filters( 'ipt_uif_label', $attr['label'] ); ?></span>
								<span class="eform-ui-est-bub-attr-value"><?php echo apply_filters( 'ipt_uif_label', $attr['value'] ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
			<div class="eform-ui-est-bub-tip"></div>
		</div>
	<?php endif; ?>
	<div class="eform-ui-estimator-slide eform-ui-estimator-slide-style-<?php echo esc_attr( $slider_style ); ?>">
		<div class="eform-ui-estimator-slide-area">
			<div class="eform-ui-estimator-slide-active"></div>
		</div>
	</div>
</div>
		<?php
	}

	/**
	 * A helper method to print the math input for estimator with provided
	 * prefix, suffix and formula.
	 *
	 * The formula and config needs to be at per with
	 * <code>print_math_input</code>.
	 *
	 * @see        print_math_input.
	 *
	 * @param      string  $prefix   Prefix string.
	 * @param      string  $suffix   Suffix string.
	 * @param      string  $formula  Math configuration.
	 * @param      array   $config   Associative array of configuration for the
	 *                               math input.
	 */
	protected function print_estimator_math( $prefix, $suffix, $formula, $config ) {
		?>
		<span class="eform-ui-est-b-t-pf"><?php echo $prefix; ?></span><span class="eform-ui-est-bub-t eform-ui-est-bub-math">
			<?php $this->print_math_input( '', '', 'hidden', $formula, $config, true ); ?>
		</span><span class="eform-ui-est-b-t-sf"><?php echo $suffix; ?></span>
		<?php
	}

	/**
	 * Print a pricing table with price, attributes, headings etc.
	 *
	 * Currently we use radio buttons, but can be easily changed to incorporate
	 * checkboxes.
	 *
	 * @param      string  $name        HTML name
	 * @param      array   $items       Pricing table items. Associative array
	 *                                  with the following format.
	 * <pre>
	 * [
	 * 		'label' => '',
	 * 		'price' => '',
	 * 		'numeric' => '',
	 * 		'attr' => '',
	 * 		'highlight' => false,
	 * 		'header' => '',
	 * 		'footer' => '',
	 * 		'scheme' => '',
	 * 		'color' => '',
	 * ]
	 * </pre>
	 * @param      array   $selected    The selected radio/checkbox id. Must
	 *                                  match with the key from the associative
	 *                                  array of items.
	 * @param      string  $currency    The currency sign
	 * @param      array   $validation  Validation array
	 * @param      string  $style       Style class of the pricing table
	 */
	public function pricing_table( $name, $items, $selected, $currency, $validation, $style = 'default' ) {
		// Get the decilam separator
		global $wp_locale;
		if ( isset( $wp_locale ) ) {
			$decimal_separator = $wp_locale->number_format['decimal_point'];
		} else {
			$decimal_separator = '.';
		}
		// Standardize some stuff
		$pricing_items = array();
		$custom_style = '';
		foreach ( $items as $i_key => $item ) {
			$pricing_element = wp_parse_args( $item, array(
				'label' => '',
				'price' => '',
				'numeric' => '',
				'attr' => '',
				'highlight' => false,
				'header' => '',
				'footer' => '',
				'scheme' => '',
				'color' => '',
			) );

			// Split the attributes
			$pricing_element['attr'] = explode( "\n", str_replace( "\r\n", "\n", $pricing_element['attr'] ) );
			// Split the price
			$pricing_element['price'] = explode( '.', $pricing_element['price'] );
			if ( ! isset( $pricing_element['price'][1] ) ) {
				$pricing_element['price'][1] = '00';
			} else {
				$pricing_element['price'][1] = str_pad( $pricing_element['price'][1], 2, '0', STR_PAD_LEFT );
			}
			$pricing_element['id'] = $this->generate_id_from_name( $name ) . '_' . $i_key . '_wrap';
			// Assign colors
			if ( 'custom' == $pricing_element['scheme'] ) {
				$primary_color = $pricing_element['color'];
				$dark_color = $this->color_luminance( $pricing_element['color'], -0.1 );
				$custom_style .= <<<EOT
#{$pricing_element['id']} .eform-pt-ribbon,
#{$pricing_element['id']} .eform-ui-pricing-table-footer {
	background-color: {$dark_color};
}
#{$pricing_element['id']} .eform-pt-header {
	background-color: {$primary_color};
}
#{$pricing_element['id']} .eform-pt-header::after {
	border-top-color: {$primary_color};
}
EOT;
			}
			// Assign highlighted class
			$pricing_element['class'] = '';
			if ( true == $pricing_element['highlight'] && empty( $selected ) ) {
				$pricing_element['class'] = 'eform-pt-highlight';
			}

			// Apply label filter for piping elements
			foreach ( [ 'label', 'header', 'footer' ] as $l_key ) {
				$pricing_element[ $l_key ] = apply_filters( 'ipt_uif_label', $pricing_element[ $l_key ] );
			}
			if ( count( $pricing_element['attr'] ) ) {
				foreach ( $pricing_element['attr'] as $att_key => $att_val ) {
					$pricing_element['attr'][ $att_key ] = apply_filters( 'ipt_uif_label', $att_val );
				}
			}

			// Save
			$pricing_items[ $i_key ] = $pricing_element;
		}
		$selected = (array) $selected;

		$validation_class = $this->convert_validation_class( $validation );
		if ( '' != $custom_style ) {
			echo '<style type="text/css">' . $custom_style . '</style>';
		}
		?>
<div class="eform-ui-pricing-table <?php echo 'eform-ui-pricing-table-style-' . esc_attr( $style ); ?>">
	<div class="eform-ui-pricing-table-content">
		<?php foreach ( $pricing_items as $i_key => $item ) : ?>
			<input type="radio" name="<?php echo esc_attr( $name ); ?>" id="<?php echo $this->generate_id_from_name( $name ) . '_' . $i_key; ?>" class="eform-pricing-table-radio <?php echo $validation_class; ?>" value="<?php echo $i_key; ?>"<?php echo in_array( (string) $i_key, $selected ) ? ' checked="checked"' : ''; ?> data-num="<?php echo esc_attr( $item['numeric'] ); ?>" data-label="<?php echo esc_attr( $item['label'] ); ?>" />
			<div class="eform-ui-pricing-table-element <?php echo $item['class']; ?>" id="<?php echo $item['id']; ?>">
				<div class="eform-ui-pricing-table-item <?php echo $item['scheme']; ?>">
					<div class="eform-pt-ribbon"><?php echo $item['header']; ?></div>
					<div class="eform-pt-header">
						<h5 class="eform-pt-title"><?php echo $item['label']; ?></h5>
						<div class="eform-pt-price">
							<span class="eform-pt-currency"><?php echo $currency; ?></span><span class="eform-pt-price-whole"><?php echo $item['price'][0]; ?></span><span class="eform-pt-price-decimal"><?php echo $decimal_separator . $item['price'][1] ?></span>
						</div>
					</div>
					<?php if ( count( $item['attr'] ) ) : ?>
						<div class="eform-ui-pricing-table-attr">
							<ul class="eform-ui-pricing-table-attr-list">
								<?php foreach ( $item['attr'] as $attr ) : ?>
									<li><?php echo $attr; ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $item['footer'] ) ) : ?>
						<div class="eform-ui-pricing-table-footer">
							<?php echo $item['footer']; ?>
						</div>
					<?php endif; ?>
					<label for="<?php echo $this->generate_id_from_name( $name ) . '_' . $i_key; ?>"></label>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
		<?php
	}

	/**
	 * Prints a single checkbox item
	 *
	 * @param string  $name        The HTML name of the radio group
	 * @param array   $items       Associative array of all the radio items.
	 *  array(
	 *      'value' => '',
	 *      'label' => '',
	 *  )
	 * @param bool    $checked     TRUE if the item is checked, FALSE otherwise
	 * @param array   $validation  Array of the validation clauses
	 * @param array   $conditional Whether or not it will show some conditional clause. If true, then you must add 'data' key to the item
	 * with the following structure
	 *  array(
	 *      'condid' => 'id of the conditional show wrapper'
	 *  )
	 * @param string  $sep         Separator HTML
	 * @param bool    $disabled    Set TRUE if the item is disabled
	 * @return void
	 */
	public function checkbox( $name, $item, $checked, $validation = false, $conditional = false, $disabled = false, $icon = 0xe18e ) {
		if ( !is_array( $item ) || empty( $item ) ) {
			return;
		}

		if ( true === $checked || $item['value'] === $checked ) {
			$checked = $item['value'];
		} else {
			$checked = false;
		}
		if ( ! isset( $item['class'] ) ) {
			$item['class'] = '';
		}
		$item['class'] .= ' ipt_uif_s_checkbox';

		$this->checkboxes( $name, array( $item ), array( $checked ), $validation, 1, $conditional, $disabled, $icon );
	}

	/**
	 * Like Dislike Element
	 *
	 * @param      string   $name                  The name
	 * @param      array    $labels                The labels
	 * @param      array    $values                The values
	 * @param      string   $value                 The value
	 * @param      boolean  $required              If required
	 * @param      array    $classes               The classes
	 * @param      array    $data                  The data
	 * @param      boolean  $feedback              The feedback
	 * @param      string   $feedback_name         The feedback name
	 * @param      string   $feedback_value        The feedback value
	 * @param      string   $feedback_placeholder  The feedback placeholder
	 */
	public function likedislike( $name, $labels, $values, $value, $required, $classes = array(), $data = array(), $feedback = false, $feedback_name = '', $feedback_value = '', $feedback_placeholder = '' ) {
		$value = (string) $value;
		$labels = wp_parse_args( $labels, array(
			'like' => '',
			'dislike' => '',
		) );
		$values = wp_parse_args( $values, array(
			'like' => 'like',
			'dislike' => 'dislike',
		) );
		$data = wp_parse_args( $data, array(
			'like' => array(),
			'dislike' => array(),
		) );

		$classes = (array) $classes;
		$classes = array_merge( $classes, array(
			'ipt_uif_rating',
			'ipt_uif_rating_likedislike',
		) );
		$likes = array(
			'like', 'dislike',
		);
		if ( in_array( $value, $values ) && $feedback ) {
			$classes[] = 'ipt_uif_likedislike_feedback_active';
		}
		if ( '' == $feedback_name ) {
			$feedback_name = $name . '[feedback]';
		}
		?>
<div class="<?php echo implode( ' ', $classes ); ?>">
	<div class="ipt_uif_likedislike_rating_inner">
		<?php foreach ( $likes as $like ) : ?>
			<?php
			// Id
			$id = $this->generate_id_from_name( $name ) . '_' . $like;
			// Add label to the data
			$data[ $like ]['label'] = isset( $labels[ $like ] ) ? $labels[ $like ] : ucfirst( $like );
			// Basics of input
			$input = '<input ' .
				'type="radio" ' .
				'name="' . esc_attr( $name ) . '" ' .
				'id="' . $id . '" ' .
				'value="' . esc_attr( $values[ $like ] ) . '" ';
			// Checked
			if ( (string) $values[ $like ] === $value ) {
				$input .= 'checked="checked" ';
			}
			// Conditional class
			$input .= ' class="ipt_uif_radio ipt_uif_likedislike_rating_radio ipt_uif_likedislike_rating_radio_' . $like;
			if ( $required ) {
				$input .= ' check_me validate[required]';
			}
			$input .= '" ';
			// Add data attributes
			$input .= $this->convert_data_attributes( $data[ $like ] );
			// Close
			$input .= ' />';
			// Label title
			$label_tooltip = '';
			if ( isset( $labels[ $like ] ) ) {
				$label_tooltip = ' title="' . esc_attr( $labels[ $like ] ) . '" class="ipt_uif_tooltip"';
			}
			?>
			<?php echo $input; ?>
			<label for="<?php echo $id; ?>"<?php echo $label_tooltip; ?>></label>
		<?php endforeach; ?>
		<?php $this->clear(); ?>
	</div>
	<?php if ( $feedback ) : ?>
	<div class="ipt_uif_likedislike_rating_feedback_wrap">
		<?php $this->textarea( $feedback_name, $feedback_value, $feedback_placeholder, 'normal', array( 'ipt_uif_likedislike_rating_feedback' ), false, false, false, 'thumbs_up_down' ); ?>
	</div>
	<?php endif; ?>
</div>
		<?php
	}

	/**
	 * Print a Toggle HTML item
	 *
	 * @param      string  $name         The HTML name of the toggle
	 * @param      string  $on           ON text
	 * @param      string  $off          OFF text
	 * @param      bool    $checked      TRUE if checked
	 * @param      string  $value        The HTML value of the toggle checkbox
	 *                                   (Optional, default to '1')
	 * @param      bool    $disabled     True to make it disabled
	 * @param      bool    $conditional  Whether the group represents
	 *                                   conditional questions. This will wrap
	 *                                   it inside a conditional div which will
	 *                                   be fired using jQuery. It does not
	 *                                   populate or create anything inside the
	 *                                   conditional div. The id of the
	 *                                   conditional divs should be given inside
	 *                                   the data value of the items in the form
	 *                                   condID => 'ID_OF_DIV'
	 * @param      array   $data         HTML 5 data attributes in the form
	 *                                   array('key' => 'value'[,...])
	 */
	public function toggle( $name, $on, $off, $checked, $value = '1', $disabled = false, $conditional = false, $data = array() ) {
		if ( '' == trim( $on ) ) {
			$on = '';
		}
		if ( '' == trim( $off ) ) {
			$off = '';
		}

		if ( $conditional == true ) {
			echo '<div class="ipt_uif_conditional_input">';
		}

		$id = $this->generate_id_from_name( $name );
?>
<div class="switch">
	<label class="eform-label-with-tabindex" tabindex="0" for="<?php echo $id; ?>" data-on="<?php echo $on; ?>" data-off="<?php echo $off; ?>">
		<?php echo $off; ?>
		<input<?php echo $this->convert_data_attributes( $data ); ?> type="checkbox"<?php echo $this->convert_state_to_attribute( $disabled == true ? 'disabled' : '' ); ?><?php if ( $checked ) : ?> checked="checked"<?php endif; ?> class="ipt_uif_switch" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="<?php echo esc_attr( $value ); ?>" data-on="<?php echo $on; ?>" data-off="<?php echo $off; ?>" />
		<span class="lever"></span>
		<?php echo $on; ?>
	</label>
</div>
		<?php

		if ( $conditional == true ) {
			echo '</div>';
		}
		$this->clear();
	}

	public function creditcard( $name_prefix, $values, $placeholders ) {
		$validation = $this->get_cc_validation();
		$address_validations = $this->get_address_validations();
		$id = $this->generate_id_from_name( $name_prefix );
		$countries = $this->get_countries( $placeholders['country'] );
		?>
<div class="ipt_uif_card_holder">
	<input type="hidden" name="<?php echo $name_prefix; ?>[ctype]" value="<?php echo esc_attr( $values['ctype'] ); ?>" class="ipt_uif_cc_type" />
	<!-- CardHolder Name -->
	<?php $this->column_head( '', 'full', true, array( 'no_margin_right', 'eform-cc-name' ) ); ?>
		<?php $this->text( $name_prefix . '[name]', $values['name'], '', 'none', 'normal', array( 'ipt_uif_cc_name ipt_fsqm_sayt_exclude' ), $validation, false, array(
			'placeholder' => $placeholders['name'],
			) ); ?>
	<?php $this->column_tail(); ?>
	<!-- Card Number -->
	<?php $this->column_head( '', 'full', true, array( 'no_margin_right', 'eform-cc-cardnumber' ) ); ?>
		<?php $this->text( $name_prefix . '[number]', $values['number'], '', 'credit-card', 'normal', array( 'ipt_uif_cc_number ipt_fsqm_sayt_exclude' ), $validation, false, array(
			'autocomplete' => 'cc-number',
			'type' => 'tel',
			'placeholder' => $placeholders['number'],
			) ); ?>
	<?php $this->column_tail(); ?>
	<!-- Expiry Date -->
	<?php $this->column_head( '', 'half', true, array( 'no_margin_right', 'eform-cc-expiry' ) ); ?>
		<?php $this->text( $name_prefix . '[expiry]', $values['expiry'], '', 'none', 'normal', array( 'ipt_uif_cc_expiry ipt_fsqm_sayt_exclude' ), $validation, false, array(
			'autocomplete' => 'cc-exp',
			'type' => 'tel',
			'placeholder' => $placeholders['expiry'],
			) ); ?>
	<?php $this->column_tail(); ?>
	<!-- CVV/CVC -->
	<?php $this->column_head( '', 'half', true, array( 'no_margin_right', 'eform-cc-cvc' ) ); ?>
		<?php $this->text( $name_prefix . '[cvc]', $values['cvc'], '', 'none', 'normal', array( 'ipt_uif_cc_cvc ipt_fsqm_sayt_exclude' ), $validation, false, array(
			'autocomplete' => 'off',
			'type' => 'tel',
			'placeholder' => $placeholders['cvc'],
			) ); ?>
	<?php $this->column_tail(); ?>
	<!-- Address Line -->
	<?php $this->column_head( '', 'full', true, array( 'no_margin_right', 'eform-cc-address' ) ); ?>
		<?php $this->text( $name_prefix . '[address]', $values['address'], '', 'none', 'normal', array( 'ipt_uif_cc_address ipt_fsqm_sayt_exclude' ), $address_validations['address'], false, array(
			'placeholder' => $placeholders['address'],
			) ); ?>
	<?php $this->column_tail(); ?>
	<!-- Country -->
	<?php $this->column_head( '', 'half', true, array( 'no_margin_right', 'eform-cc-country' ) ); ?>
		<?php $this->select( $name_prefix . '[country]', $countries, $values['country'], $address_validations['country'], false, true, false, false, $placeholders['country'] ); ?>
	<?php $this->column_tail(); ?>
	<!-- ZIP -->
	<?php $this->column_head( '', 'half', true, array( 'no_margin_right', 'eform-cc-zip' ) ); ?>
		<?php $this->text( $name_prefix . '[zip]', $values['zip'], '', 'none', 'normal', array( 'ipt_uif_cc_zip ipt_fsqm_sayt_exclude' ), $address_validations['zip'], false, array(
			'placeholder' => $placeholders['zip'],
			) ); ?>
	<?php $this->column_tail(); ?>
	<?php $this->clear(); ?>
</div>
		<?php
	}

	public function coupon( $name, $placeholder, $button, $data, $formula, $mname, $micon, $msuffix, $mprecision, $moptions, $mnoanim ) {
		?>
<div class="ipt_uif_coupon" data-config="<?php echo esc_attr( json_encode( (object) $data ) ); ?>">
	<?php
	$this->column_head( '', 'half', false );
	$this->text( $name, '', $placeholder, 'tag2', 'normal', array( 'ipt_uif_coupon_text', 'ipt_fsqm_sayt_exclude' ) );
	$this->column_tail();

	$this->column_head( '', 'half', true );
	$this->button( $button, '', 'small', 'primary', 'normal', array( 'ipt_uif_coupon_button' ), 'button', false, array(), array(), '', 'none', $icon_position = 'before' );
	echo '<span class="ipt_uif_coupon_message"></span>';
	$this->column_tail();

	$this->column_head( '', 'full', false, array( 'ipt_uif_coupon_final' ) );
	echo '<div class="ipt_uif_fancy_container">';
	$this->mathematical( $mname, '', $formula, false, $micon, __( 'Discounted amount', 'ipt_fsqm' ), $msuffix, $mprecision, $moptions, 'ipt_uif_coupon_elem', $mnoanim );
	echo '<div class="clear"></div></div>';
	$this->column_tail();
	?>
</div>
		<?php
	}

	/**
	 * Generate input type text HTML
	 *
	 * @param string  $name               HTML name of the text input
	 * @param string  $value              Initial value of the text input
	 * @param string  $placeholder        Default placeholder
	 * @param string  $icon               The icon class or hexcode, none to not
	 *                                    print any icon
	 * @param string  $state              readonly or disabled state
	 * @param array   $classes            Array of additional classes
	 * @param array   $validation         Associative array of all validation
	 *                                    clauses
	 * @param array   $data               HTML 5 data attributes in associative
	 *                                    array
	 * @param boolean $attr               HTML attributes. Will override `type`
	 *                                    if it is there
	 * @param array   $inline             Inline configuration. With following
	 *                                    structure.
	 * @param boolean $native_placeholder If enabled will use native placeholder
	 * <pre>
	 * [
	 * 		'enabled' => false,
	 * 		'prefix' => '',
	 * 		'suffix' => '',
	 * ]
	 * </pre>
	 *
	 * @uses        IPT_Plugin_UIF_Base::convert_validation_class
	 * @uses        IPT_Plugin_UIF_Base::convert_data_attributes
	 */
	public function text( $name, $value, $placeholder, $icon = 'pencil', $state = 'normal', $classes = array(), $validation = false, $data = false, $attr = false, $inline = [], $native_placeholder = false ) {
		$id = $this->generate_id_from_name( $name );
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$validation_attr = $this->convert_validation_class( $validation );
		if ( '' != $validation_attr ) {
			$classes[] = $validation_attr;
		}

		// Max length
		$maxlength = '';
		if ( is_array( $validation ) && isset( $validation['filters']['maxSize'] ) ) {
			$maxlength = 'maxlength="' . esc_attr( $validation['filters']['maxSize'] ) . '"';
		}

		$wrapper_class = array( 'input-field' );
		if ( ! empty( $icon ) && 'none' != $icon ) {
			$wrapper_class[] = 'has-icon';
		}

		$input_type = 'text';

		if ( false == $attr ) {
			$attr = array();
		}

		if ( ! is_array( $attr ) ) {
			$attr = (array) $attr;
		}

		if ( empty( $data ) ) {
			$data = [];
		}

		if ( is_array( $validation ) && isset( $validation['filters'] ) && isset( $validation['filters']['type'] ) ) {
			switch ( $validation['filters']['type'] ) {
				case 'number':
				case 'integer':
					$input_type = 'number';
					if ( in_array( 'ipt_uif_keypad', $classes ) ) {
						$input_type = 'tel';
					}
					if ( isset( $validation['filters']['min'] ) ) {
						$attr['min'] = $validation['filters']['min'];
					}
					if ( isset( $validation['filters']['max'] ) ) {
						$attr['max'] = $validation['filters']['max'];
					}
					$attr['step'] = 'any';
					break;
				case 'phone':
					$input_type = 'tel';
					break;
				case 'url':
					$input_type = 'url';
					break;
				case 'email':
					$input_type = 'email';
					break;
				default :
					// Nothing needed here, because
					// $input_type is already 'text'
			}
		}

		// Inline element
		$inline = wp_parse_args( $inline, [
			'enabled' => false,
			'prefix' => '',
			'suffix' => '',
			'width' => '300px',
		] );

		// Check for masking
		// Helper flag
		$inputmask = false;
		if ( isset( $validation['mask'] ) && $validation['mask']['enabled'] ) {
			// Enqueue mask assets
			$this->enqueue_mask_js();
			// Add to data
			$data = $data + $this->get_mask_data( $validation['mask'] );
			// Add to the class
			$classes[] = 'eform-inputmask';
			$inputmask = true;
		}

		// Add inline placeholder if any of the options are met
		if ( ( $inline['enabled'] || $inputmask || $native_placeholder ) && '' != $placeholder ) {
			$attr['placeholder'] = $placeholder;
		}

		$data_attr = $this->convert_data_attributes( $data );
		$html_attr = $this->convert_html_attributes( $attr );
?>
<?php
if ( $inline['enabled'] ) {
	// Print the prefix, if needed
	if ( '' != $inline['prefix'] ) {
		$this->generate_label( $name, $inline['prefix'], $id, [ 'eform-inline-label', 'eform-inline-label-prefix' ] );
	}
	// Start a wrapper
	echo '<div class="eform-input-inline" style="width: ' . esc_attr( $inline['width'] ) . ';">';
}
?>
<div class="<?php echo implode( ' ', $wrapper_class ); ?>">
	<input class="<?php echo implode( ' ', $classes ); ?> ipt_uif_text"
		<?php echo $data_attr; ?>
		<?php echo $this->convert_state_to_attribute( $state ); ?>
		<?php echo $html_attr; ?>
		<?php if ( ! isset( $attr['type'] ) ) : ?>
		type="<?php echo $input_type; ?>"
		<?php endif; ?>
		name="<?php echo esc_attr( $name ); ?>"
		id="<?php echo $id; ?>"
		<?php echo $maxlength; ?>
		value="<?php echo esc_textarea( $value ); ?>" />
	<?php $this->print_icon_by_class( $icon ); ?>
	<?php if ( '' != $placeholder && ! $inline['enabled'] && ! $inputmask && ! $native_placeholder ) : ?>
		<label for="<?php echo $id ?>"><?php echo $placeholder; ?></label>
	<?php endif; ?>
</div>
<?php
if ( $inline['enabled'] ) {
	// Close the wrapper
	echo '</div>';
	// Show the suffix, if needed
	if ( '' != $inline['suffix'] ) {
		$this->generate_label( $name, $inline['suffix'], $id, [ 'eform-inline-label', 'eform-inline-label-suffix' ] );
	}
}
?>
		<?php
	}

	/**
	 * Generate textarea HTML
	 *
	 * @param      string   $name         HTML name of the text input
	 * @param      string   $value        Initial value of the text input
	 * @param      string   $placeholder  Default placeholder
	 * @param      string   $state        readonly or disabled state
	 * @param      array    $classes      Array of additional classes
	 * @param      array    $validation   Associative array of all validation
	 *                                    clauses
	 * @param      array    $data         HTML 5 data attributes in associative
	 *                                    array
	 * @param      boolean  $attr         HTML attributes and values
	 * @param      boolean  $icon         The icon
	 * @param      boolean  $no_material  No material styling
	 * @param      boolean  $native_placeholder   Use native placeholder
	 * @uses       IPT_Plugin_UIF_Base::convert_validation_class
	 * @uses       IPT_Plugin_UIF_Base::convert_data_attributes
	 */
	public function textarea( $name, $value, $placeholder, $state = 'normal', $classes = array(), $validation = false, $data = false, $attr = false, $icon = false, $no_material = false, $native_placeholder = false ) {
		$id = $this->generate_id_from_name( $name );
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_textarea';
		if ( ! $no_material ) {
			$classes[] = 'materialize-textarea';
		}

		$validation_attr = $this->convert_validation_class( $validation );
		if ( '' != $validation_attr ) {
			$classes[] = $validation_attr;
		}

		if ( empty( $data ) ) {
			$data = [];
		}

		// Check for masking
		// Helper flag
		$inputmask = false;
		if ( isset( $validation['mask'] ) && $validation['mask']['enabled'] ) {
			// Enqueue mask assets
			$this->enqueue_mask_js();
			// Add up to the data
			$data = $data + $this->get_mask_data( $validation['mask'] );
			// Add to the class
			$classes[] = 'eform-inputmask';
			$inputmask = true;
		}

		// Add inline placeholder if any of the options are met
		if ( ( $inputmask || $no_material || $native_placeholder ) && '' != $placeholder ) {
			$attr['placeholder'] = $placeholder;
		}

		$data_attr = $this->convert_data_attributes( $data );
		$html_attr = $this->convert_html_attributes( $attr );

		$maxlength = '';
		if ( is_array( $validation ) && isset( $validation['filters']['maxSize'] ) ) {
			$maxlength = 'maxlength="' . esc_attr( $validation['filters']['maxSize'] ) . '"';
		}
		$wrapper_class = array( 'input-field' );
		if ( ! empty( $icon ) && 'none' != $icon ) {
			$wrapper_class[] = 'has-icon';
		}
?>
<div class="<?php echo implode( ' ', $wrapper_class ); ?>">
	<textarea class="<?php echo implode( ' ', $classes ); ?>"
		rows="4"
		<?php echo $data_attr; ?>
		<?php echo $html_attr; ?>
		<?php echo $this->convert_state_to_attribute( $state ); ?>
		name="<?php echo esc_attr( $name ); ?>"
		<?php echo $maxlength; ?>
		id="<?php echo $id; ?>"><?php echo esc_textarea( $value ); ?></textarea>
		<?php $this->print_icon( $icon ); ?>
		<?php if ( ! $no_material && '' != $placeholder && ! $native_placeholder && ! $inputmask ) : ?>
			<label for="<?php echo $id; ?>"><?php echo $placeholder; ?></label>
		<?php endif; ?>
</div>

		<?php
	}

	public function enqueue_guest_blog() {
		wp_enqueue_style( 'trumbowyg', $this->bower_components . 'trumbowyg/dist/ui/trumbowyg.min.css', array(), $this->version );
		wp_enqueue_script( 'trumbowyg-js', $this->bower_components . 'trumbowyg/dist/trumbowyg.min.js', array( 'jquery' ), $this->version, true );
		// wp_enqueue_script( 'trumbowyg-cleanpaste', $this->bower_components . 'trumbowyg/dist/plugins/cleanpaste/trumbowyg.cleanpaste.min.js', array( 'trumbowyg-js' ), $this->version, true );
		// temporarily enqueue our own version for the copy-paste fix
		// https://github.com/Alex-D/Trumbowyg/pull/977
		wp_enqueue_script( 'trumbowyg-cleanpaste', $this->static_location . 'front/js/trumbowyg.cleanpaste.fixed.min.js', array( 'trumbowyg-js' ), $this->version, true );
		wp_enqueue_script( 'trumbowyg-table', $this->bower_components . 'trumbowyg/dist/plugins/table/trumbowyg.table.min.js', array( 'trumbowyg-js' ), $this->version, true );
	}

	/**
	 * Create a guest blogging element
	 *
	 * This can have WYSIWYG element as well as a list of taxonomies
	 *
	 * @param      string                $name_prefix    The name prefix
	 * @param      mixed(boolean|array)  $trumbowyg      Trumbowyg settings
	 *                                                   which would passed
	 *                                                   directly to the widget.
	 *                                                   Pass false to disable
	 *                                                   WYSIWYG editor
	 * @param      mixed(boolean|array)  $taxonomy_list  The taxonomy list. Pass
	 *                                                   an array of taxonomies
	 *                                                   or false to disable
	 */
	public function guest_blog( $name_prefix, $value, $placeholder, $trumbowyg = array(), $post_title = '', $post_title_label = '', $taxonomy_list = false, $taxonomy_single_list = array(), $taxonomy_required_list = array(), $tax_values = array(), $bio = false, $bio_title = '', $bio_value = '' ) {
		// Enqueue
		if ( false !== $trumbowyg ) {
			$this->enqueue_guest_blog();
		}

		$classes = array( 'ipt-eform-guestpost' );
		$trum_data = array();
		if ( false !== $trumbowyg ) {
			$classes[] = 'ipt-eform-trumbowyg';
			$trum_data['ef-trum'] = json_encode( $trumbowyg );
		}

		// Create the title
		$this->text( $name_prefix . '[title]', $post_title, $post_title_label, 'pushpin', 'normal', array(), array( 'required' => true ) );
		$this->clear();

		// First print the textarea

		$this->textarea( $name_prefix . '[value]', $value, $placeholder, 'normal', $classes, false, $trum_data, false, false, true );
		$this->clear();

		// Now calculate the taxonomies and print them one by one
		// inside checkboxes
		if ( false !== $taxonomy_list && is_array( $taxonomy_list ) && ! empty( $taxonomy_list ) ) {
			$total_taxes = count( $taxonomy_list );
			// 1 column, 2 column or 3 column
			if ( $total_taxes == 1 ) {
				$columns = 1;
			} else if ( 0 == $total_taxes % 3 ) {
				$columns = 3;
			} else {
				$columns = 2;
			}
			echo '<div class="ipt-eform-guestpost-tax-column-wrap ipt-eform-guestpost-tax-column-' . $columns . '">';
			foreach ( $taxonomy_list as $taxonomy ) {
				$tax_data = get_taxonomy( $taxonomy );
				$is_tax_single = false;
				$is_tax_required = false;
				$walker_name = $name_prefix . '[taxonomy][' . $taxonomy . '][]';
				if ( in_array( $taxonomy, $taxonomy_single_list ) ) {
					$is_tax_single = true;
				}
				if ( in_array( $taxonomy, $taxonomy_required_list ) ) {
					$is_tax_required = true;
				}

				$args = array(
					'selected_cats' => ( isset( $tax_values[ $taxonomy ] ) ? $tax_values[ $taxonomy ] : false ),
					'walker' => new IPT_eForm_Tax_Checklist( $walker_name, $is_tax_single, $is_tax_required ),
					'taxonomy' => $taxonomy,
					'name' => $walker_name,
					'is_tax_single' => $is_tax_single,
					'is_tax_required' => $is_tax_required,
				);
				echo '<div class="ipt-eform-guestpost-tax-wrap">';
				$this->question_container( '', $tax_data->labels->name, '', array( array( $this, 'terms_checklist' ), array( $args ) ), $is_tax_required, false, true, '', array( 'ipt-eform-guestpost-tax', 'tax-' . $taxonomy ) );
				echo '</div>';
			}
			echo '<div class="clear"></div></div>';
		}

		// Create a bio
		if ( true == $bio ) {
			$this->textarea( $name_prefix . '[bio]', $bio_value, $bio_title, 'normal', array(), array( 'required' => true ), false, false, 'quill' );
		}
	}

	public function anchor_button( $text, $href, $target = '_self', $size = 'medium', $icon = 'none' ) {
?>
<a target="<?php echo esc_attr( $target ); ?>" class="ipt_uif_anchor_button <?php echo esc_attr( $size ); ?>" href="<?php echo esc_url( $href ); ?>"><?php $this->print_icon_by_data( $icon ); ?><?php echo $text; ?></a>
		<?php
	}

	/**
	 * Print terms select ( previously checklist ) through a custom walker
	 *
	 * @param      array   $params  The parameters
	 *
	 * @return     string  The HTML of the widget
	 */
	public function terms_checklist( $params = array() ) {
		$defaults = array(
			'descendants_and_self' => 0,
			'selected_cats' => false,
			'popular_cats' => false,
			'walker' => null,
			'taxonomy' => 'category',
			'checked_ontop' => true,
			'echo' => true,
			'name' => '',
			'is_tax_single' => '',
			'is_tax_required' => '',
		);

		$r = wp_parse_args( $params, $defaults );

		if ( empty( $r['walker'] ) || ! ( $r['walker'] instanceof Walker ) ) {
			$walker = new IPT_eForm_Tax_Checklist( '' );
		} else {
			$walker = $r['walker'];
		}

		$taxonomy = $r['taxonomy'];
		$descendants_and_self = (int) $r['descendants_and_self'];

		$args = array( 'taxonomy' => $taxonomy );

		$tax = get_taxonomy( $taxonomy );

		$args['list_only'] = ! empty( $r['list_only'] );

		if ( is_array( $r['selected_cats'] ) ) {
			$args['selected_cats'] = $r['selected_cats'];
		} else {
			$args['selected_cats'] = array();
		}

		if ( $descendants_and_self ) {
			$categories = (array) get_terms( $taxonomy, array(
				'child_of' => $descendants_and_self,
				'hierarchical' => 0,
				'hide_empty' => 0
			) );
			$self = get_term( $descendants_and_self, $taxonomy );
			array_unshift( $categories, $self );
		} else {
			$categories = (array) get_terms( $taxonomy, array( 'get' => 'all' ) );
		}

		// Compatibility with WooCommerce
		$this->enqueue_select();

		$output = '<select name="' . $r['name'] . '" id="' . $this->generate_id_from_name( $r['name'] ) . '"';
		if ( true != $r['is_tax_single'] ) {
			$output .= ' multiple="multiple"';
		}
		$output .= ' class="ipt-eform-guestpost-tax-ul';
		if ( true == $r['is_tax_required'] ) {
			$output .= ' check_me validate[required]';
		}
		$output .= ' ipt_uif_select" data-theme="eform-material" data-placeholder="' . esc_attr__( '-- please select --', 'ipt_fsqm' ) . '" data-allow-clear="true">';

		if ( true == $r['is_tax_single'] ) {
			$output .= '<option value=""' . ( empty( $args['selected_cats'] ) ? ' selected="selected"' : '' ) . '>' . esc_attr__( '-- please select --', 'ipt_fsqm' ) . '</option>';
		}

		if ( $r['checked_ontop'] ) {
			// Post process $categories rather than adding an exclude to the get_terms() query to keep the query the same across all posts (for any query cache)
			$checked_categories = array();
			$keys = array_keys( $categories );

			foreach ( $keys as $k ) {
				if ( in_array( $categories[$k]->term_id, $args['selected_cats'] ) ) {
					$checked_categories[] = $categories[$k];
					unset( $categories[$k] );
				}
			}

			// Put checked cats on top
			$output .= call_user_func_array( array( $walker, 'walk' ), array( $checked_categories, 0, $args ) );
		}
		// Then the rest of them
		$output .= call_user_func_array( array( $walker, 'walk' ), array( $categories, 0, $args ) );
		$output .= '</select>';

		if ( $r['echo'] ) {
			echo $output;
		}

		return $output;
	}

	/**
	 * Password Element
	 *
	 * @param      string   $name_prefix  The name prefix
	 * @param      string   $value        The value
	 * @param      string   $placeholder  The placeholder
	 * @param      string   $state        The state
	 * @param      boolean  $confirm      Whether to print two sets of inputs
	 *                                    for confirmation
	 * @param      array    $classes      Extra classes
	 * @param      boolean  $validation   Validation array
	 * @param      boolean  $data         Data array
	 */
	public function password( $name_prefix, $value, $placeholder = '', $state = 'normal', $confirm = false, $classes = array(), $validation = false, $data = false ) {
		$name = $name_prefix . '[value]';
		$id = $this->generate_id_from_name( $name );
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$validation_attr = $this->convert_validation_class( $validation );
		if ( '' != $validation_attr ) {
			$classes[] = $validation_attr;
		}

		$data_attr = $this->convert_data_attributes( $data );
?>
<div class="input-field has-icon ipt-eform-password">
	<input class="<?php echo implode( ' ', $classes ); ?> ipt_uif_text ipt_uif_password"
		<?php echo $data_attr; ?>
		<?php echo $this->convert_state_to_attribute( $state ); ?>
		type="password"
		name="<?php echo esc_attr( $name ); ?>"
		id="<?php echo $id; ?>"
		value="<?php echo esc_textarea( $value ); ?>" />
		<?php $this->print_icon_by_class( 'eye', true, array(), $this->default_messages['pwd_reveal'] ); ?>
		<label for="<?php echo $id; ?>"><?php echo $placeholder; ?></label>
</div>
<?php if ( $confirm !== false ) : ?>
<div class="input-field has-icon ipt-eform-password">
	<input class="ipt_uif_text ipt_uif_password ipt_uif_password_confirm check_me validate[equals[<?php echo $id; ?>]]"
		type="password"
		name="<?php echo esc_attr( $name_prefix ); ?>[confirm]"
		id="<?php echo $this->generate_id_from_name( $name_prefix . '[confirm]' ); ?>"
		value="<?php echo esc_textarea( $value ); ?>" />
		<?php $this->print_icon_by_class( 'eye', true, array(), $this->default_messages['pwd_reveal'] ); ?>
		<label for="<?php echo $this->generate_id_from_name( $name_prefix . '[confirm]' ); ?>"><?php echo __( 'Confirm', 'ipt_fsqm' ); ?></label>
</div>
<?php endif; ?>
		<?php
	}

	/**
	 * Print a simple password element
	 *
	 * @param      string   $name         The name
	 * @param      string   $value        The value
	 * @param      string   $placeholder  The placeholder
	 * @param      string   $state        The state
	 * @param      array    $classes      Extra classes
	 * @param      boolean  $validation   Validation array
	 * @param      boolean  $data         HTML data attributes
	 */
	public function password_simple( $name, $value, $placeholder = '', $state = 'normal', $classes = array(), $validation = false, $data = false ) {
		$id = $this->generate_id_from_name( $name );
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$validation_attr = $this->convert_validation_class( $validation );
		if ( '' != $validation_attr ) {
			$classes[] = $validation_attr;
		}

		$data_attr = $this->convert_data_attributes( $data );
?>
<div class="input-field has-icon ipt-eform-password">
	<input class="<?php echo implode( ' ', $classes ); ?> ipt_uif_text ipt_uif_password"
		<?php echo $data_attr; ?>
		<?php echo $this->convert_state_to_attribute( $state ); ?>
		type="password"
		name="<?php echo esc_attr( $name ); ?>"
		id="<?php echo $id; ?>"
		value="<?php echo esc_textarea( $value ); ?>" />
		<?php $this->print_icon_by_class( 'eye', true, array(), $this->default_messages['pwd_reveal'] ); ?>
		<label for="<?php echo $id; ?>"><?php echo $placeholder; ?></label>
</div>
		<?php
	}

	/**
	 * Print a keypad element
	 *
	 * @param      string   $name         The name
	 * @param      string   $value        The value
	 * @param      array    $settings     The settings
	 * @param      string   $placeholder  The placeholder
	 * @param      boolean  $mask         The mask
	 * @param      boolean  $multiline    The multiline
	 * @param      string   $state        The state
	 * @param      array    $classes      The classes
	 * @param      boolean  $validation   The validation
	 * @param      boolean  $data         The data
	 */
	public function keypad( $name, $value, $settings, $placeholder, $mask = false, $multiline = false, $state = 'normal', $classes = array(), $validation = false, $data = false ) {
		$id = $this->generate_id_from_name( $name );
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_keypad';
		$validation_attr = $this->convert_validation_class( $validation );
		if ( '' != $validation_attr ) {
			$classes[] = $validation_attr;
		}

		$data_attr = $this->convert_data_attributes( $data );
		?>
<?php if ( $mask ) : ?>
	<div class="input-field has-icon ipt-eform-password">
		<input class="<?php echo implode( ' ', $classes ); ?> ipt_uif_text ipt_uif_password"
			autocomplete="off"
			<?php echo $data_attr; ?>
			<?php echo $this->convert_state_to_attribute( $state ); ?>
			data-settings="<?php echo esc_attr( json_encode( (object) $settings ) ); ?>"
			type="password"
			name="<?php echo esc_attr( $name ); ?>"
			id="<?php echo $id; ?>"
			value="<?php echo esc_textarea( $value ); ?>" />
			<?php $this->print_icon_by_class( 'eye', true, array(), $this->default_messages['pwd_reveal'] ); ?>
			<label for="<?php echo $id; ?>"><?php echo $placeholder; ?></label>
	</div>
<?php else : ?>
	<?php if ( $multiline ) : ?>
		<div class="input-field has-icon">
			<textarea class="<?php echo implode( ' ', $classes ); ?> ipt_uif_textarea materialize-textarea"
					  rows="4"
				<?php echo $data_attr; ?>
				<?php echo $this->convert_state_to_attribute( $state ); ?>
				data-settings="<?php echo esc_attr( json_encode( (object) $settings ) ); ?>"
				type="text"
				name="<?php echo esc_attr( $name ); ?>"
				id="<?php echo $id; ?>"><?php echo esc_textarea( $value ); ?></textarea>
				<?php $this->print_icon_by_class( 'keyboard' ); ?>
				<label for="<?php echo $id; ?>"><?php echo $placeholder; ?></label>
		</div>
	<?php else : ?>
		<div class="input-field has-icon">
			<input class="<?php echo implode( ' ', $classes ); ?> ipt_uif_text"
				<?php echo $data_attr; ?>
				<?php echo $this->convert_state_to_attribute( $state ); ?>
				data-settings="<?php echo esc_attr( json_encode( (object) $settings ) ); ?>"
				type="text"
				name="<?php echo esc_attr( $name ); ?>"
				id="<?php echo $id; ?>"
				value="<?php echo esc_textarea( $value ); ?>" />
				<?php $this->print_icon_by_class( 'keyboard' ); ?>
				<label for="<?php echo $id; ?>"><?php echo $placeholder; ?></label>
		</div>
	<?php endif; ?>
<?php endif; ?>
		<?php
	}



	/**
	 * Print a mathematical element input along with span for animation.
	 *
	 * It prints the input element with needed configurations and data
	 * attributes that JS uses for populating stuff. Call this, if you just need
	 * a math element, not a full fledged math UI element.
	 *
	 * @param      string   $name     HTML Name.
	 * @param      string   $value    Initial value.
	 * @param      string   $type     The value of the HTML type attribute.
	 *                                Could be text or hidden.
	 * @param      string   $formula  The mathematical formula.
	 * @param      array    $config   Associative array of configuration. Should
	 *                                contain the following parameters.
	 * <pre>
	 * [
	 * 		'precision' => 2, // Decimal point precision
	 * 		'options' => [
	 * 			'decimal' => '.', // Decimal point in locale
	 * 			'separator' => ',', // thousand separator in locale
	 * 			'useGrouping' => true, // Whether to separate thousands
	 * 		],
	 * 		'noanim' => false, // Whether or not to have animation
	 * ]
	 * </pre>
	 * @param      boolean  $span     (Optional) Whether or not to print the span tag. The
	 *                                span tag is used for the countUp
	 *                                animation. Defaults to true.
	 */
	protected function print_math_input( $name, $value, $type, $formula, $config, $span = true ) {
		// Set the class
		$class = 'ipt_uif_mathematical_input';
		if ( 'text' == $type ) {
			$class .= ' ipt_uif_text check_me validate[custom[number]]';
		}
		// Precalculate some attributes
		$attributes = [];
		if ( ! empty( $name ) ) {
			$attributes['name'] = $name;
		}
		$attributes['type'] = $type;
		$attributes['value'] = $value;
		// Print the input
		?>
		<input<?php echo $this->convert_html_attributes( $attributes ); ?> data-sayt-exclude class="<?php echo esc_attr( $class ); ?>" data-precision="<?php echo $config['precision']; ?>" data-formula="<?php echo esc_attr( $formula ); ?>" data-options="<?php echo esc_attr( json_encode( (object) $config['options'] ) ); ?>" data-noanim="<?php echo ( $config['noanim'] ? 'true' : 'false' ); ?>" />
		<?php if ( $span ) : ?>
			<span class="ipt_uif_mathematical_span"><?php echo esc_attr( $value ); ?></span>
		<?php endif; ?>
		<?php
	}

	/**
	 * Prints a full mathematical UI element for placing inside the form
	 *
	 * @param      string   $name       HTML Name.
	 * @param      string   $value      Initial value.
	 * @param      string   $formula    Mathematical formula which will be
	 *                                  parsed by expr-eval.
	 * @param      boolean  $editable   Whether or not the value is editable.
	 * @param      integer  $icon       The icon hex.
	 * @param      string   $prefix     The prefix.
	 * @param      string   $suffix     The suffix.
	 * @param      integer  $precision  Decimal Precision.
	 * @param      boolean  $options    Associative array of options. Should
	 *                                  contain following parameters.
	 * <pre>
	 * [
	 * 		'decimal' => '.', // Decimal point in locale
	 * 		'separator' => ',', // thousand separator in locale
	 * 		'useGrouping' => true, // Whether to separate thousands
	 * ]
	 * </pre>
	 * @param      array    $classes    Additional classes.
	 * @param      boolean  $noanim     Whether or not to use animation for the
	 *                                  UI.
	 * @param      boolean  $hidden     Whether this is a completely hidden math
	 *                                  element.
	 */
	public function mathematical( $name, $value, $formula, $editable = false, $icon = 0xe074, $prefix = '', $suffix = '', $precision = 2, $options = false, $classes = array(), $noanim = false, $hidden = false ) {
		// Enqueue
		$this->enqueue_math_expr();

		// Get the HTML ID
		$id = $this->generate_id_from_name( $name );

		// Set the precision to a float
		// If it is not set to auto, i.e, empty string
		if ( '' != $precision ) {
			$precision = (float) $precision;
			$value = (float) $value; /* Added for PHP 8 compatibility */
			$value = round( $value, $precision );
		}

		if ( ! is_array( $classes ) ) {
			$classes = (array) $classes;
		}

		$classes[] = 'ipt_uif_richtext';
		$classes[] = 'ipt_uif_mathematical';

		if ( $hidden ) {
			$classes[] = 'ipt_uif_mathematical_hidden';
		}

		$config = [
			'precision' => $precision,
			'options' => $options,
			'noanim' => $noanim,
		];
		?>
<div class="<?php echo esc_attr( implode( ' ', array_unique( $classes ) ) ); ?>">
	<?php if ( true == $hidden ) : ?>
		<?php $this->print_math_input( $name, $value, 'hidden', $formula, $config, false ); ?>
	<?php else : ?>
		<?php $this->print_icon( $icon, false ); ?>
		<?php echo $prefix; ?>
		<?php if ( $editable == true ) : ?>
			<?php $this->print_math_input( $name, $value, 'text', $formula, $config, false ); ?>
		<?php else : ?>
			<?php $this->print_math_input( $name, $value, 'hidden', $formula, $config, true ); ?>
		<?php endif; ?>
		<?php echo $suffix; ?>
	<?php endif; ?>
</div>
		<?php
	}

	private function get_icon_value( $icon, $hide_icon = false ) {
		return $hide_icon ? 'none' : $icon;
	}

	public function address( $name_prefix, $values, $placeholders, $validation = false, $preset_country = '', $hide_icons = false, $use_native_placeholder = false ) {
		$other_validation = array(
			'required' => $validation['required'],
		);
		if ( '' != $placeholders['recipient'] ) {
			$this->column_head( '', 'full', false, array( 'ipt-eform-address-recipient' ) );

			$this->text(
				$name_prefix . '[recipient]',
				$values['recipient'],
				$placeholders['recipient'],
				$this->get_icon_value( 'user', $hide_icons ),
				'normal',
				array(),
				$validation,
				false,
				false,
				[],
				$use_native_placeholder
			);

			$this->column_tail();
		}

		if ( '' != $placeholders['line_one'] ) {
			$this->column_head( '', 'full', false, array( 'ipt-eform-address-line-one' ) );

			$this->text(
				$name_prefix . '[line_one]',
				$values['line_one'],
				$placeholders['line_one'],
				$this->get_icon_value( 'address-book', $hide_icons ),
				'normal',
				array(),
				$other_validation,
				false,
				false,
				[],
				$use_native_placeholder
			);

			$this->column_tail();
		}

		if ( '' != $placeholders['line_two'] ) {
			$this->column_head( '', 'full', false, array( 'ipt-eform-address-line-two' ) );

			$this->text(
				$name_prefix . '[line_two]',
				$values['line_two'],
				$placeholders['line_two'],
				$this->get_icon_value( 'address-book', $hide_icons ),
				'normal',
				array(),
				$other_validation,
				false,
				false,
				[],
				$use_native_placeholder
			);

			$this->column_tail();
		}

		if ( '' != $placeholders['line_three'] ) {
			$this->column_head( '', 'full', false, array( 'ipt-eform-address-line-three' ) );

			$this->text(
				$name_prefix . '[line_three]',
				$values['line_three'],
				$placeholders['line_three'],
				$this->get_icon_value( 'address-book', $hide_icons ),
				'normal',
				array(),
				false,
				false,
				false,
				[],
				$use_native_placeholder
			);
			$this->column_tail();
		}

		$cpz_actives = 0;
		if ( '' != $placeholders['country'] ) {
			$cpz_actives++;
		}
		if ( '' != $placeholders['province'] ) {
			$cpz_actives++;
		}
		if ( '' != $placeholders['zip'] ) {
			$cpz_actives++;
		}
		$cpz_column = 'third';
		if ( 2 == $cpz_actives ) {
			$cpz_column = 'half';
		}
		if ( 1 == $cpz_actives ) {
			$cpz_column = 'full';
		}

		if ( $cpz_actives > 0 ) {
			echo '<div class="ipt-eform-address-cpz cpz-column-' . $cpz_column . '">';
			if ( '' != $placeholders['country'] ) {
				$this->column_head( '', $cpz_column, false, array( 'ipt-eform-address-country' ) );

				$this->autocomplete(
					$name_prefix . '[country]',
					$values['country'],
					$placeholders['country'],
					array(),
					$this->get_icon_value( 'flag', $hide_icons ),
					'normal',
					array(),
					$other_validation,
					false,
					$use_native_placeholder
				);

				$this->column_tail();
			}
			if ( '' != $placeholders['province'] ) {
				$this->column_head( '', $cpz_column, false, array( 'ipt-eform-address-province' ) );

				$this->autocomplete(
					$name_prefix . '[province]',
					$values['province'],
					$placeholders['province'],
					array(),
					$this->get_icon_value( 'map-signs', $hide_icons ),
					'normal',
					array(),
					$other_validation,
					array(
						'preset-country' => $preset_country,
					),
					$use_native_placeholder
				);

				$this->column_tail();
			}
			if ( '' != $placeholders['zip'] ) {
				$this->column_head( '', $cpz_column, false, array( 'ipt-eform-address-zip' ) );

				$this->text(
					$name_prefix . '[zip]',
					$values['zip'],
					$placeholders['zip'],
					$this->get_icon_value( 'location', $hide_icons ),
					'normal',
					array(),
					array(
						'required' => $validation['required'],
						'filters' => array(
							'type' => 'zipCode',
						),
					),
					false,
					false,
					[],
					$use_native_placeholder
				);

				$this->column_tail();
			}
			echo '</div>';
		}

		$this->clear();
	}

	public function autocomplete( $name, $value, $placeholder, $autocomplete, $icon = 'pencil', $state = 'normal', $classes = array(), $validation = false, $data_attr = false, $native_placeholder = false ) {
		$data = array(
			'autocomplete' => json_encode( (array) $autocomplete ),
		);
		if ( is_array( $data_attr ) ) {
			$data = array_merge( $data, $data_attr );
		}
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_autocomplete';
		$this->text( $name, $value, $placeholder, $icon, $state, $classes, $validation, $data, false, [], $native_placeholder );
	}

	/**
	 * Generate more than a single button inside a single container.
	 *
	 * @param      array   $buttons            Associative array of all button
	 *                                         elements. See ::button to find
	 *                                         more.
	 * @param      string  $container_id       The HTML ID of the container
	 *                                         (Optional)
	 * @param      array   $container_classes  Additional Classes of the
	 *                                         container (Optional)
	 */
	public function buttons( $buttons, $container_id = '', $container_classes = '' ) {
		if ( ! is_array( $buttons ) || empty( $buttons ) ) {
			$this->msg_error( 'Please pass a valid arrays to the <code>EForm_Material_UI::buttons</code> method' );
			return;
		}

		$id_attr = '';
		if ( '' != trim( $container_id ) ) {
			$id_attr = ' id="' . esc_attr( trim( $container_id ) ) . '"';
		}

		if ( !is_array( $container_classes ) ) {
			$container_classes = (array) $container_classes;
		}
		$container_classes[] = 'ipt-eform-material-button-container';

		echo "\n" . '<div' . $id_attr . ' class="' . implode( ' ', $container_classes ) . '"><div class="eform-button-container-inner">' . "\n";

		foreach ( $buttons as $button_index ) {
			$button_index = array_values( $button_index );
			$button = array();
			foreach ( array( 'text', 'name', 'size', 'style', 'state', 'classes', 'type', 'data', 'atts', 'url', 'icon', 'icon_position' ) as $b_key => $b_val ) {
				if ( isset( $button_index[$b_key] ) ) {
					$button[$b_val] = $button_index[$b_key];
				}
			}
			if ( !isset( $button['text'] ) || '' == trim( $button['text'] ) ) {
				continue;
			}
			$text = $button['text'];
			$name = isset( $button['name'] ) ? $button['name'] : '';
			$size = isset( $button['size'] ) ? $button['size'] : 'medium';
			$style = isset( $button['style'] ) ? $button['style'] : 'primary';
			$state = isset( $button['state'] ) ? $button['state'] : 'normal';
			$classes = isset( $button['classes'] ) ? $button['classes'] : array();
			$type = isset( $button['type'] ) ? $button['type'] : 'button';
			$data = isset( $button['data'] ) ? $button['data'] : array();
			$atts = isset( $button['atts'] ) ? $button['atts'] : array();
			$url = isset( $button['url'] ) ? $button['url'] : '';
			$icon = isset( $button['icon'] ) ? $button['icon'] : '';
			$icon_position = isset( $button['icon_position'] ) ? $button['icon_position'] : 'before';

			$this->button( $text, $name, $size, $style, $state, $classes, $type, false, $data, $atts, $url, $icon, $icon_position );
		}

		echo "\n" . '</div></div>' . "\n";
	}

	/**
	 * Button for PrintElement
	 *
	 * @param      string  $id     HTML ID of the container to print
	 * @param      string  $text   Button Text
	 */
	public function print_button( $id, $text ) {
		?>
<div class="ipt_uif_button_container">
	<button class="ipt_uif_button ipt_uif_printelement" data-printid="<?php echo esc_attr( $id ); ?>"><span class="button-icon ipt-icomoon-print"></span> <?php echo $text; ?></button>
</div>
		<?php
	}

	/**
	 * Generates a single button
	 *
	 * @param      string  $text           The text of the button
	 * @param      string  $name           HTML name. ID is generated
	 *                                     automatically (unless name is an
	 *                                     array, ID is identical to name).
	 * @param      string  $size           Size large|medium|small
	 * @param      string  $style          Style primary|ui
	 * @param      string  $state          HTML state normal|readonly|disabled
	 * @param      array   $classes        Array of additional classes
	 * @param      string  $type           The HTML type of the button
	 *                                     button|submit|reset|anchor
	 * @param      bool    $container      Whether or not to print the
	 *                                     container.
	 * @param      array   $data           HTML5 data attributes
	 * @param      array   $atts           The atts
	 * @param      string  $url            The url
	 * @param      string  $icon           The icon
	 * @param      string  $icon_position  The icon position
	 */
	public function button( $text, $name = '', $size = 'medium', $style = 'primary', $state = 'normal', $classes = array(), $type = 'button', $container = true, $data = array(), $atts = array(), $url = '', $icon = '', $icon_position = 'before' ) {
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}

		switch ( $size ) {
		case 'large' :
		case 'medium' :
		case 'small' :
		case 'auto' :
			$classes[] = $size;
			break;
		default :
			$classes[] = 'medium';
		}
		switch ( $style ) {
		default :
		case 'primary' :
		case '0' :
			$classes[] = 'primary-button';
			break;
		case 'secondary' :
		case '1' :
			$classes[] = 'secondary-button';
			break;
		case 'ui' :
		case '2' :
			$classes[] = 'ipt-ui-button';
			break;
		}
		$name_id_attr = '';
		if ( '' != trim( $name ) ) {
			$name_id_attr = ' name="' . esc_attr( trim( $name ) ) . '" id="' . $this->generate_id_from_name( $name ) . '"';
		}
		$state_attr = $this->convert_state_to_attribute( $state );

		$type_attr = '';
		if ( '' != trim( $type ) && $type != 'anchor' ) {
			$type_attr = ' type="' . esc_attr( trim( $type ) ) . '"';
		} else {
			$type_attr = ' href="' . esc_url( $url ) . '"';
		}

		$data_attr = '';
		if ( is_array( $data ) ) {
			$data_attr = $this->convert_data_attributes( $data );
		}
		$tag = $type == 'anchor' ? 'a' : 'button';

		$icon_span = '';
		if ( $icon != '' && $icon != 'none' ) {
			$icon_span .= '<i class="ipticm';
			if ( is_numeric( $icon ) ) {
				$icon_span .= '" data-ipt-icomoon="' . '&#x' . dechex( $icon ) . '">';
			} else {
				$icon_span .= ' ipt-icomoon-' . $icon . '">';
			}
			$icon_span .= '</i>';
		}
		if ( $icon_position == 'before' ) {
			$text = $icon_span . ' ' . $text;
		} else {
			$text .= ' ' . $icon_span;
		}

		$html_atts = '';
		if ( ! empty( $atts ) ) {
			$html_atts = $this->convert_html_attributes( $atts );
		}
?>
<?php if ( true == $container ) : ?>
<div class="ipt-eform-material-button-container"><div class="eform-button-container-inner">
<?php endif; ?>
	<<?php echo $tag; ?><?php echo $type_attr . $data_attr . $html_atts; ?> class="ipt_uif_button eform-material-button eform-ripple <?php echo implode( ' ', $classes ); ?>"<?php echo $name_id_attr . $state_attr; ?>><?php echo $text; ?></<?php echo $tag; ?>>
<?php if ( true == $container ) : ?>
</div></div>
<?php endif; ?>
		<?php
	}

	/**
	 * Generate a spinner to select numerical value
	 *
	 * @param string  $name        HTML name
	 * @param string  $value       Initial value of the range
	 * @param string  $placeholder HTML placeholder
	 * @param int     $min         Minimum of the range
	 * @param int     $max         Maximum of the range
	 * @param int     $step        spinner move step
	 */
	public function spinner( $name, $value, $placeholder = '', $min = '', $max = '', $step = 1, $required = false ) {
		$validation = array(
			'required' => $required,
			'filters' => array(
				'type' => 'number',
				'min' => $min,
				'max' => $max,
			),
		);
		$validation_attr = $this->convert_validation_class( $validation );
		if ( '' == $step || ! is_numeric( $step ) ) {
			$step = 'any';
		}
?>
<input type="number" placeholder="<?php echo $placeholder; ?>" class="ipt_uif_text code ipt_uif_uispinner <?php echo esc_attr( $validation_attr ); ?>" min="<?php echo $min; ?>" max="<?php echo $max; ?>" step="<?php echo $step; ?>" name="<?php echo esc_attr( trim( $name ) ); ?>" id="<?php echo $this->generate_id_from_name( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" />
		<?php
	}

	public function enqueue_slider() {
		// Enqueue
		wp_enqueue_script( 'jquery-ui-slider-pips', $this->bower_components . 'jQuery-ui-Slider-Pips/dist/jquery-ui-slider-pips.min.js', array( 'jquery', 'jquery-ui-slider' ), $this->version, true );
		wp_enqueue_style( 'ipt-jq-ui-slider-pips', $this->bower_components . 'jQuery-ui-Slider-Pips/dist/jquery-ui-slider-pips.min.css', array(), $this->version );
	}

	/**
	 * Generate a horizontal slider to select between numerical values
	 *
	 * @param      string   $name        HTML name
	 * @param      string   $value       Initial value of the range
	 * @param      bool     $show_count  Whether or not to show the count
	 * @param      int      $min         Minimum of the range
	 * @param      int      $max         Maximum of the range
	 * @param      int      $step        Slider move step
	 * @param      string   $prefix      The prefix
	 * @param      string   $suffix      The suffix
	 * @param      array    $labels      The labels
	 * @param      boolean  $nomin       The nomin
	 * @param      boolean  $floats      The floats
	 * @param      boolean  $vertical    The vertical
	 * @param      integer  $height      The height
	 */
	public function slider( $name, $value, $show_count = true, $min = 0, $max = 100, $step = 1, $prefix = '', $suffix = '', $labels = array(), $nomin = false, $floats = true, $vertical = false, $height = 300 ) {
		$this->enqueue_slider();
		// Other stuff
		$min = (float) $min;
		$max = (float) $max;
		$step = (float) $step;
		$value = $value == '' ? $min : (float) $value;
		if ( $value < $min )
			$value = $min;
		if ( $value > $max )
			$value = $max;

		$label_data = $this->slider_labels( $labels, $min, $max, $step );
		$container_classes = array( 'ipt_uif_empty_box', 'ipt_uif_slider_box' );
		if ( true == $vertical ) {
			$container_classes[] = 'ipt_uif_slider_vertical';
		}
?>
<div class="<?php echo implode( ' ', $container_classes ); ?>">
	<input data-floats="<?php echo $floats; ?>" type="number" min="<?php echo $min; ?>" max="<?php echo $max; ?>" step="<?php echo $step; ?>" data-nomin="<?php echo $nomin; ?>" data-show-count="<?php echo $show_count; ?>" data-labels="<?php echo esc_attr( json_encode( (object) $label_data ) ); ?>" data-prefix="<?php echo esc_attr( $prefix ); ?>" data-suffix="<?php echo esc_attr( $suffix ); ?>" class="ipt_uif_slider check_me validate[funcCall[iptUIFSliderVal]]" data-min="<?php echo $min; ?>" data-max="<?php echo $max; ?>" data-step="<?php echo $step; ?>" name="<?php echo esc_attr( trim( $name ) ); ?>" id="<?php echo $this->generate_id_from_name( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" data-vertical="<?php echo $vertical; ?>" data-height="<?php echo $height; ?>" />
	<?php if ( $show_count ) : ?>
	<div class="ipt_uif_slider_count">
		<?php echo $prefix; ?><span class="ipt_uif_slider_count_single"><?php echo $value != '' ? $value : $min; ?></span><?php echo $suffix; ?>
	</div>
	<?php endif; ?>
</div>
		<?php
	}

	/**
	 * Generate a horizontal slider to select a range between numerical values
	 *
	 * @param mixed   array|string $names HTML names in the order Min value -> Max value. If string is given the [max] and [min] is added to make an array
	 * @param array   $values     Initial values of the range in the same order
	 * @param bool    $show_count Whether or not to show the count
	 * @param int     $min        Minimum of the range
	 * @param int     $max        Maximum of the range
	 * @param int     $step       Slider move step
	 */
	public function slider_range( $names, $values, $show_count = true, $min = 0, $max = 100, $step = 1, $prefix = '', $suffix = '', $labels = array(), $nomin = false, $floats = true, $vertical = false, $height = 300 ) {
		// Enqueue
		$this->enqueue_slider();
		// Main stuff
		$min = (float) $min;
		$max = (float) $max;
		$step = (float) $step;
		if ( !is_array( $names ) ) {
			$name = (string) $names;
			$names = array(
				$name . '[min]', $name . '[max]',
			);
		}

		if ( !is_array( $values ) ) {
			$value = (int) $values;
			$values = array(
				$value, $value,
			);
		}
		if ( !isset( $values[0] ) ) {
			$values[0] = $values['min'];
			$values[1] = $values['max'];
		}
		$value_min = $values[0] != '' ? $values[0] : $min;
		$value_max = $values[1] != '' ? $values[1] : $min;

		if ( $value_min < $min )
			$value_min = $min;
		if ( $value_min > $max )
			$value_min = $max;
		if ( $value_max < $min )
			$value_max = $min;
		if ( $value_max > $max )
			$value_max = $max;

		$label_data = $this->slider_labels( $labels, $min, $max, $step );
		$container_classes = array( 'ipt_uif_empty_box', 'ipt_uif_slider_box', 'ipt-eform-rangebox' );
		if ( true == $vertical ) {
			$container_classes[] = 'ipt_uif_slider_vertical';
		}
?>
<div class="<?php echo implode( ' ', $container_classes ); ?>">
	<input data-floats="<?php echo $floats; ?>" type="number" min="<?php echo $min; ?>" max="<?php echo $max; ?>" step="<?php echo $step; ?>" data-nomin="<?php echo $nomin; ?>" data-show-count="<?php echo $show_count; ?>" data-labels="<?php echo esc_attr( json_encode( (object) $label_data ) ); ?>" data-prefix="<?php echo esc_attr( $prefix ); ?>" data-suffix="<?php echo esc_attr( $suffix ); ?>" class="ipt_uif_slider slider_range check_me validate[funcCall[iptUIFSliderVal]]" data-min="<?php echo $min; ?>" data-max="<?php echo $max; ?>" data-step="<?php echo $step; ?>" name="<?php echo esc_attr( trim( $names[0] ) ); ?>" id="<?php echo $this->generate_id_from_name( $names[0] ); ?>" value="<?php echo esc_attr( $value_min ); ?>" data-vertical="<?php echo $vertical; ?>" data-height="<?php echo $height; ?>" />
	<input type="number" min="<?php echo $min; ?>" max="<?php echo $max; ?>" step="<?php echo $step; ?>" class="ipt_uif_slider_range_max" name="<?php echo esc_attr( trim( $names[1] ) ); ?>" id="<?php echo $this->generate_id_from_name( $names[1] ); ?>" value="<?php echo esc_attr( $value_max ); ?>" />
	<?php if ( $show_count ) : ?>
	<div class="ipt_uif_slider_count">
		<?php echo $prefix; ?><span class="ipt_uif_slider_count_min"><?php echo $value_min; ?></span><?php echo $suffix; ?>
		 - <?php echo $prefix; ?><span class="ipt_uif_slider_count_max"><?php echo $value_max; ?></span><?php echo $suffix; ?>
	</div>
	<?php endif; ?>
</div>
		<?php
	}

	/**
	 * Populates multiple sliders or ranges
	 *
	 * @param string  $name       The name of slider
	 * @param array   $sliders    An associative array of the sliders
	 * array(
	 *      'type' => 'range' | 'single',
	 *      'name' => 'HTML Name',
	 *      'value' => int|array(min,max),
	 *      'title' => 'Title or Label',
	 * )
	 * @param bool    $show_count Whether or not to show the count
	 * @param int     $min        Minimum of the range
	 * @param int     $max        Maximum of the range
	 * @param int     $step       Slider move step
	 */
	public function sliders( $name, $sliders, $show_count = false, $min = 0, $max = 100, $step = 1, $labels = array(), $nomin = false, $floats = true, $vertical = false, $vheight = 300 ) {
		foreach ( $sliders as $slider ) {
			$params = array( $slider['name'], $slider['value'], $show_count, $min, $max, $step, $slider['prefix'], $slider['suffix'], $labels, $nomin, $floats, $vertical, $vheight );
			// override the min, max, step if present in config
			foreach ( array( 3 => 'min', 4 => 'max', 5 => 'step' ) as $key => $val ) {
				if ( isset( $slider[$val] ) && $slider[$val] != '' ) {
					$params[$key] = $slider[$val];
				}
			}
			// Add the floats
			if ( $slider['type'] == 'range' ) {
				$callback = array( array( $this, 'slider_range' ), $params );
			} else {
				$callback = array( array( $this, 'slider' ), $params );
			}
			$this->question_container( $name, $slider['title'], '', $callback, false );
		}
	}

	public function spinners( $spinners ) {
		foreach ( $spinners as $spinner ) {
			$params = array( $spinner['name'], $spinner['value'], $spinner['placeholder'], $spinner['min'], $spinner['max'], $spinner['step'], $spinner['required'] );
			$this->question_container( $spinner['name'], $spinner['title'], '', array( array( $this, 'spinner' ), $params ), false, false, false, '', array( 'eform-spinner-container' ) );
		}
	}

	public function ratings( $ratings, $style ) {
		foreach ( $ratings as $rating ) {
			$params = array( $rating['name'], $rating['value'], $rating['max'], $rating['required'], $style, $rating['labels']['low'], $rating['labels']['high'] );
			$this->question_container( $rating['name'], $rating['title'], '', array( array( $this, 'rating' ), $params ), false, false, false, '', array( 'eform-rating-container' ) );
		}
	}

	/**
	 * Print Matrix radio and select
	 *
	 * @param      string          $name_prefix  The name prefix
	 * @param      array           $rows         The rows
	 * @param      array           $columns      The columns
	 * @param      array           $values       The values
	 * @param      boolean         $multiple     If checkbox
	 * @param      boolean         $required     If required
	 * @param      integer|string  $icon         The icon
	 * @param      array           $numerics     The numerics
	 */
	public function matrix( $name_prefix, $rows, $columns, $values, $multiple, $required, $icon = 0xe18e, $numerics = array() ) {
		$type = $multiple == true ? 'checkbox' : 'radio';
		$validation = array(
			'required' => $required,
		);
		$validation_attr = $this->convert_validation_class( $validation );
		if ( !is_array( $values ) ) {
			$values = (array) $values;
		}
		$icon_attr = '';
		if ( $icon != '' && $icon != 'none' ) {
			$icon_attr = ' data-labelcon="&#x' . dechex( $icon ) . ';"';
		}
?>
<div class="ipt_uif_matrix_container">
	<table class="ipt_uif_matrix highlight bordered">
		<thead>
			<tr>
				<th scope="col"></th>
				<?php foreach ( $columns as $column ) : ?>
				<th scope="col"><div class="ipt_uif_matrix_div_cell"><?php echo apply_filters( 'ipt_uif_label', $column ); ?></div></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th scope="col"></th>
				<?php foreach ( $columns as $column ) : ?>
				<th scope="col"><div class="ipt_uif_matrix_div_cell"><?php echo apply_filters( 'ipt_uif_label', $column ); ?></div></th>
				<?php endforeach; ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ( $rows as $r_key => $row ) : ?>
			<?php
			if ( !isset( $values[$r_key] ) ) {
				$values[$r_key] = array();
			} else {
			$values[$r_key] = (array) $values[$r_key];
		}
?>
			<tr>
				<th scope="row"><div class="ipt_uif_matrix_div_cell"><?php echo apply_filters( 'ipt_uif_label', $row ); ?></div></th>
				<?php foreach ( $columns as $c_key => $column ) : ?>
				<?php
			$name = $name_prefix . '[rows][' . $r_key . '][]';
		$id = $this->generate_id_from_name( $name ) . '_' . $c_key;
?>
				<td><div class="ipt_uif_matrix_div_cell">
					<input type="<?php echo $type; ?>" class="ipt_uif_<?php echo $type . ' ' . $validation_attr; ?>"
						   value="<?php echo $c_key; ?>"
						   name="<?php echo $name; ?>" id="<?php echo $id; ?>"
						   data-num="<?php echo ( isset( $numerics[$c_key] ) ? floatval( $numerics[$c_key] ) : '0' ); ?>"
						   <?php if ( in_array( (string) $c_key, $values[$r_key], true ) ) echo 'checked="checked"'; ?> />
					<label for="<?php echo $id; ?>"<?php echo $icon_attr; ?>></label>
				</div></td>
				<?php endforeach; ?>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

		<?php
	}

	/**
	 * Matrix dropdown funciton
	 *
	 * @param      string   $name_prefix  The name prefix
	 * @param      array    $rows         The rows
	 * @param      array    $columns      The columns
	 * @param      array    $items        The items
	 * @param      array    $values       The values
	 * @param      array    $validation   The validation
	 * @param      boolean  $multiple     The multiple
	 */
	public function matrix_select( $name_prefix, $rows, $columns, $items, $values, $validation = array(), $multiple = false ) {
		if ( ! is_array( $values ) ) {
			$values = (array) $values;
		}

		// Adjust items for empty ones
		$e_label = '';
		if ( '' == $items[0]['value'] ) {
			//$empty_one = array_shift( $items );
			$e_label = $items[0]['label'];
			if ( $multiple ) {
				array_shift( $items );
			}
		}
		?>
<div class="ipt_uif_matrix_container ipt_uif_matrix_select">
	<table class="ipt_uif_matrix highlight bordered">
		<thead>
			<tr>
				<th scope="col"></th>
				<?php foreach ( $columns as $column ) : ?>
				<th scope="col"><div class="ipt_uif_matrix_div_cell"><?php echo apply_filters( 'ipt_uif_label', $column ); ?></div></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th scope="col"></th>
				<?php foreach ( $columns as $column ) : ?>
				<th scope="col"><div class="ipt_uif_matrix_div_cell"><?php echo apply_filters( 'ipt_uif_label', $column ); ?></div></th>
				<?php endforeach; ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ( $rows as $r_key => $row ) : ?>
			<?php
			if ( !isset( $values[$r_key] ) ) {
				$values[$r_key] = array();
			} else {
				$values[$r_key] = (array) $values[$r_key];
			}
			?>
			<tr>
				<th scope="row"><div class="ipt_uif_matrix_div_cell"><?php echo apply_filters( 'ipt_uif_label', $row ); ?></div></th>
				<?php foreach ( $columns as $c_key => $column ) : ?>
				<?php
				$name = $name_prefix . '[rows][' . $r_key . '][' . $c_key . ']';
				if ( $multiple ) {
					$name .= '[]';
				}
				if ( ! isset( $values[$r_key][$c_key] ) ) {
					$values[$r_key][$c_key] = '';
				}
?>
				<td><div class="ipt_uif_matrix_div_cell">
					<?php $this->select( $name, $items, $values[$r_key][$c_key], $validation, false, true, false, $multiple, $e_label ); ?>
				</div></td>
				<?php endforeach; ?>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
		<?php
	}

	/**
	 * Create matrix feedbacl elements
	 *
	 * @param      string   $name_prefix  The name prefix
	 * @param      array    $rows         The rows
	 * @param      array    $columns      The columns
	 * @param      array    $values       The values
	 * @param      boolear  $multiline    If multiline
	 * @param      array    $validation   The validation
	 * @param      integer  $icon         The icon
	 */
	public function matrix_text( $name_prefix, $rows, $columns, $values, $multiline, $validation, $icon = 0xe18e ) {
		if ( ! is_array( $values ) ) {
			$values = (array) $values;
		}
		?>
<div class="ipt_uif_matrix_container ipt_uif_matrix_feedback">
	<table class="ipt_uif_matrix highlight bordered">
		<thead>
			<tr>
				<th scope="col"></th>
				<?php foreach ( $columns as $column ) : ?>
				<th scope="col"><div class="ipt_uif_matrix_div_cell"><?php echo apply_filters( 'ipt_uif_label', $column ); ?></div></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th scope="col"></th>
				<?php foreach ( $columns as $column ) : ?>
				<th scope="col"><div class="ipt_uif_matrix_div_cell"><?php echo apply_filters( 'ipt_uif_label', $column ); ?></div></th>
				<?php endforeach; ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ( $rows as $r_key => $row ) : ?>
			<?php
			if ( ! isset( $values[$r_key] ) ) {
				$values[$r_key] = array();
			} else {
				$values[$r_key] = (array) $values[$r_key];
			}
			?>
			<tr>
				<th scope="row"><div class="ipt_uif_matrix_div_cell"><?php echo apply_filters( 'ipt_uif_label', $row ); ?></div></th>
				<?php foreach ( $columns as $c_key => $column ) : ?>
				<?php
				$name = $name_prefix . '[rows][' . $r_key . '][' . $c_key . ']';
				if ( ! isset( $values[$r_key][$c_key] ) ) {
					$values[$r_key][$c_key] = '';
				}
?>
				<td><div class="ipt_uif_matrix_div_cell">
					<?php if ( $multiline ) : ?>
					<?php $this->textarea( $name, $values[$r_key][$c_key], $column, 'normal', array( 'ipt_uif_matrix_text' ), $validation, false, false, $icon ); ?>
					<?php else : ?>
					<?php $this->text( $name, $values[$r_key][$c_key], $column, $icon, 'normal', array( 'ipt_uif_matrix_text' ), $validation ); ?>
					<?php endif; ?>
				</div></td>
				<?php endforeach; ?>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
		<?php
	}

	/**
	 * Prints Sortable Elements
	 *
	 * @param      string   $name_prefix  The name prefix
	 * @param      array    $items        The items
	 * @param      array    $order        The order
	 * @param      boolean  $randomize    The randomize
	 */
	public function sortables( $name_prefix, $items, $order = array(), $randomize = false ) {
		if ( !is_array( $items ) || empty( $items ) ) {
			return;
		}
		$keys = array_keys( $items );

		if ( !empty( $order ) ) {
			$keys = $order;
		}

		if ( $randomize && empty( $order ) ) {
			shuffle( $keys );
		}
?>
<div class="ipt_uif_sorting">
	<?php foreach ( $keys as $key ) : ?>
	<div class="ipt_uif_sortme">
		<a class="ipt_uif_sorting_handle" href="javascript:;"><?php $this->print_icon_by_class( 'bars' ); ?></a>
		<input type="hidden" data-sayt-exclude name="<?php echo $name_prefix; ?>" value="<?php echo $key; ?>" />
		<span class="eform-sortable-label"><?php echo apply_filters( 'ipt_uif_label', $items[$key]['label'] ); ?></span>
	</div>
	<?php endforeach; ?>
</div>
		<?php
	}

	/**
	 * Generates a simple jQuery UI Progressbar
	 * Minumum value is 0 and maximum is 100.
	 * So always calculate in percentage.
	 *
	 * @param string  $id      The HTML ID
	 * @param numeric $start   The start value
	 * @param array   $classes Additional classes
	 */
	public function progressbar( $id = '', $start = 0, $classes = array(), $decimals = 2 ) {
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_progress_bar';
		$id_attr = '';
		if ( $id != '' ) {
			$id_attr = ' id="' . esc_attr( $id ) . '"';
		}
?>
<div class="<?php echo implode( ' ', $classes ); ?>" data-start="<?php echo $start; ?>" data-decimals="<?php echo esc_attr( $decimals ); ?>"<?php echo $id_attr; ?>>
	<div class="ipt_uif_progress_value"><span class="eform-progress-value-span"></span></div>
</div>
		<?php
	}

	public function enqueue_ui_slider() {
		wp_enqueue_script( 'jquery-ui-slider' );
	}

	/**
	 * Datetime maker
	 *
	 * @param      string   $name         The name
	 * @param      string   $value        The value
	 * @param      string   $type         The type
	 * @param      string   $state        The state
	 * @param      array    $classes      The classes
	 * @param      boolean  $validation   The validation
	 * @param      string   $date_format  The date format
	 * @param      string   $time_format  The time format
	 * @param      string   $placeholder  The placeholder
	 * @param      array    $data_attr    The data attribute
	 * @param      boolean  $hide_icon    The hide icon
	 */
	public function datetime( $name, $value, $type = 'date', $state = 'normal', $classes = array(), $validation = false, $date_format = 'yy-mm-dd', $time_format = 'HH:mm:ss', $placeholder = '', $data_attr = array(), $hide_icon = false ) {
		$this->enqueue_ui_slider();
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'datepicker';
		$icon = 'calendar';

		switch ( $type ) {
		case 'date' :
			$classes[] = 'ipt_uif_datepicker';
			break;
		case 'time' :
			$classes[] = 'ipt_uif_timepicker';
			$icon = 'clock';
			break;
		case 'datetime' :
			$classes[] = 'ipt_uif_datetimepicker';
			break;
		}

		$data = array(
			'dateFormat' => $date_format,
			'timeFormat' => $time_format,
		);

		$data = array_merge( $data, $data_attr );

		$id = $this->generate_id_from_name( $name );
		$validation_attr = $this->convert_validation_class( $validation );
		if ( '' != $validation_attr ) {
			$classes[] = $validation_attr;
		}

		if ( '' == $value ) {
			$classes[] = 'is-empty';
		}

		$data_attr_html = $this->convert_data_attributes( $data );
		$maxlength = '524288';
		if ( is_array( $validation ) && isset( $validation['filters']['maxSize'] ) ) {
			$maxlength = $validation['filters']['maxSize'];
		}
		$wrapper_class = array( 'input-field', 'eform-dp-input-field' );
		if ( ! $hide_icon ) {
			$wrapper_class[] = 'has-icon';
		}
?>
<div class="<?php echo implode( ' ', $wrapper_class ); ?>">
	<input class="<?php echo implode( ' ', $classes ); ?> ipt_uif_text"
	<?php echo $data_attr_html; ?>
	<?php echo $this->convert_state_to_attribute( $state ); ?>
	type="text"
	readonly="readonly"
	name="<?php echo esc_attr( $name ); ?>"
	id="<?php echo $id; ?>"
	maxlength="<?php echo esc_attr( $maxlength ); ?>"
	value="<?php echo esc_textarea( $value ); ?>" />
	<?php if ( ! $hide_icon ) : ?>
		<?php $this->print_icon_by_class( $icon ); ?>
	<?php endif; ?>
	<?php if ( '' != $placeholder ) : ?>
		<label for="<?php echo $id ?>"><?php echo $placeholder; ?></label>
	<?php endif; ?>
	<a href="javascript:;" class="eform-dp-clear" title="<?php echo __( 'Clear', 'ipt_fsqm' ); ?>">&times;</a>
</div>
		<?php
	}

	public function checkbox_toggler( $id, $label, $selector, $checked = false ) {
		$id = esc_attr( trim( $id ) );
?>
<input<?php echo true == $checked ? ' checked="checked"' : ''; ?>
	data-selector="<?php echo esc_attr( $selector ); ?>"
	type="checkbox"
	class="ipt_uif_checkbox ipt_uif_checkbox_toggler"
	id="<?php echo $id; ?>" />
<label for="<?php echo $id; ?>">
	 <?php echo apply_filters( 'ipt_uif_label', $label ); ?>
</label>
		<?php
	}

	public function enqueue_locationpicker( $api = '' ) {
		wp_enqueue_script( 'google-maps-api', '//maps.google.com/maps/api/js?libraries=places&key=' . $api, array(), $this->version, true );
		wp_enqueue_script( 'jquery-locationpicker', $this->bower_components . 'jquery-locationpicker-plugin/dist/locationpicker.jquery.min.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( 'jquery-geolocation', $this->bower_components . 'jquery-geolocation/jquery.geolocation.min.js', array( 'jquery' ), $this->version, true );
	}

	public function locationpicker( $name_prefix, $values, $manual_control, $labels, $description = '', $error = '', $loader = 'Locating', $radius = 500, $zoom = 15, $scrollwheel = true, $show_ui = true, $required = false, $api = '' ) {
		// Enqueue all scripts
		// The reason why we are calling it from here is
		// It would be overhead to load location API from google
		// if it is not needed
		$this->enqueue_locationpicker( $api );
		$values = wp_parse_args( $values, array(
			'lat' => '',
			'long' => '',
			'location_name' => '',
		) );
		$labels = wp_parse_args( $labels, array(
			'lat' => 'Latitude',
			'long' => 'Longitude',
			'location_name' => 'Location',
			'update' => 'Update Location',
			'auto' => 'Auto Update',
			'nolocation' => 'No location data provided',
		) );
		$gps_settings = array(
			'values' => (object) $values,
			'name_prefix' => $name_prefix,
			'ids' => array(
				'latitudeInput' => $this->generate_id_from_name( $name_prefix . '[lat]' ),
				'longitudeInput' => $this->generate_id_from_name( $name_prefix . '[long]' ),
				'locationNameInput' => $this->generate_id_from_name( $name_prefix . '[location_name]' ),
			),
			'radius' => (float) $radius,
			'zoom' => (float) $zoom,
			'scrollwheel' => $scrollwheel,
			'manualcontrol' => $manual_control,
			'showUI' => $show_ui,
			'nolocationLabel' => $labels['nolocation'],
		);
		$autoupdateid = $this->generate_id_from_name( $name_prefix . '[autoupdate]' );
		?>
<div class="ipt_uif_locationpicker" data-gps-settings="<?php echo esc_attr( json_encode( (object) $gps_settings ) ); ?>">
	<?php if ( '' !== trim( $description ) ) : ?>
	<div class="locationpicker-description ipt_uif_richtext">
		<?php echo wpautop( $description ); ?>
	</div>
	<?php endif; ?>
	<div class="locationpicker-maps ui-state-active">
		<div class="locationpicker-maps-control"></div>
		<div class="locationpicker-maps-locating">
			<?php $this->ajax_loader( false, '', array(), true, $loader ); ?>
			<?php $this->clear(); ?>
		</div>
		<div class="location-maps-error"><p><?php echo $error; ?></p></div>
	</div>
	<?php if ( $show_ui ) : ?>
	<?php if ( $manual_control ) : ?>
	<div class="locationpicker-widget ui-widget-content">
		<?php $this->column_head( '', 'half', true ); ?>
		<?php $this->text( $name_prefix . '[location_name]', $values['location_name'], $labels['location_name'], 'search2', 'normal', array( 'ipt_uif_tooltip' ), $required, false, array( 'title' => $labels['location_name'] ) ); ?>
		<?php $this->column_tail(); ?>
		<?php $this->column_head( '', 'forth', true ); ?>
		<?php $this->text( $name_prefix . '[lat]', $values['lat'], $labels['lat'], 'location', 'normal', array( 'ipt_uif_tooltip' ), $required, false, array( 'title' => $labels['lat'] ) ); ?>
		<?php $this->column_tail(); ?>
		<?php $this->column_head( '', 'forth', true ); ?>
		<?php $this->text( $name_prefix . '[long]', $values['long'], $labels['long'], 'location', 'normal', array( 'ipt_uif_tooltip' ), $required, false, array( 'title' => $labels['long'] ) ); ?>
		<?php $this->column_tail(); ?>
		<?php $this->clear(); ?>
		<div class="locationpicker-controls">
			<button class="location-update ipt_uif_button"><?php echo $labels['update']; ?></button>
		</div>
		<?php $this->clear(); ?>
	</div>
	<?php else : ?>
	<?php foreach ( $values as $key => $value ) : ?>
	<?php $this->hidden_input( $name_prefix . '[' . $key . ']', $value ); ?>
	<?php endforeach; ?>
	<div class="locationpicker-controls">
		<button class="location-update ipt_uif_button"><?php echo $labels['update']; ?></button>
	</div>
	<?php endif; ?>
	<?php endif; ?>
</div>
		<?php
	}

	public function enqueue_uploader() {
		// Enqueue
		wp_enqueue_style( 'blueimp-fileupload.bundle', $this->bower_builds . 'blueimp-fileupload/css/jquery.fileupload.bundle.css', array(), $this->version );
		wp_enqueue_script( 'blueimp-fileupload', $this->bower_builds . 'blueimp-fileupload/js/jquery.fileupload.bundle.js', array( 'jquery', 'jquery-ui-widget' ), $this->version, true );
	}

	/**
	 * Prints a container for BlueIMP Uploader
	 *
	 * @param      string   $name             The HTML name of the type="file"
	 *                                        input field
	 * @param      string   $name_id          The HTML name of the hidden input
	 *                                        fields with ID to the uploaded
	 *                                        file
	 * @param      array    $settings         Array of settings
	 * @param      array    $attributes       An array of other attributes which
	 *                                        would be used by JavaScript. This
	 *                                        has a preset datatype
	 * @param      array    $form_data        An array of data that is being
	 *                                        submitted when uploading or
	 *                                        fetching files. This is program
	 *                                        specific and can be of any type
	 * @param      string   $description      Any description text.
	 * @param      array    $labels           Labels of default fields.
	 * @param      boolean  $validation       The validation
	 * @param      integer  $max_upload_size  The maximum upload size
	 * @param      boolean  $show_ui          The show user interface
	 * @see        $this->default_messages['uploader']
	 */
	public function uploader( $name, $name_id, $settings, $attributes, $form_data, $description = '', $labels = array(), $validation = true, $max_upload_size = 0, $show_ui = true, $recaptcha_fallback = null ) {
		$labels = wp_parse_args( (array) $labels, $this->default_messages['uploader'] );

		$this->enqueue_uploader();

		// Normalize variables
		$settings = wp_parse_args( (array) $settings, array(
			'accept_file_types'    => 'gif,jpeg,png',
			'max_number_of_files'  => '',
			'min_number_of_files'  => '',
			'max_file_size'        => '1000000',
			'min_file_size'        => '1',
			'show_drop_zone'       => true,
			'wp_media_integration' => false,
			'auto_upload'          => false,
			'single_upload'        => false,
			'drag_n_drop'          => true,
			'progress_bar'         => true,
			'preview_media'        => true,
			'can_delete'           => true,
			'required'             => $validation,
			'minimal_ui'           => false,
			'accept'               => 'all',
			'capture'              => 'none',
		) );
		$attributes = wp_parse_args( (array) $attributes, [
			'ajax_upload' => '',
			'ajax_download' => '',
			'fetch_files' => true,
		] );

		// Make the configuration
		$configuration = array(
			'id' => $this->generate_id_from_name( $name ),
			'upload_url' => '?action=' . $attributes['ajax_upload'],
			'download_url' => '?action=' . $attributes['ajax_download'],
			'do_download' => $attributes['fetch_files'],
			'show_ui' => $show_ui,
			'captcha_url' => '?action=ipt_fsqm_fu_captcha',
		);
		$toggler_id = $configuration['id'] . '_toggler';
		// Check the value of max_file_size
		if ( '' == $settings['max_file_size'] || 0 == $settings['max_file_size'] ) {
			// In this case, admin has prompted for No limit
			// Although the limit is actually the system limit
			$settings['max_file_size'] = $max_upload_size;
		}
		// If the passed $max_upload_size is 0, then something is wrong, and we
		// assume the max_file_size is the maximum allowed in this server
		if ( 0 == $max_upload_size ) {
			$max_upload_size = $settings['max_file_size'];
		}

		// Now the max_file_size has to be the lower value between, settings
		// and server configuration
		$settings['max_file_size'] = min( $max_upload_size, $settings['max_file_size'] );

		// Convert to float value
		$settings['max_file_size'] = floatval( $settings['max_file_size'] );

		if ( $validation == true && '' == $settings['min_number_of_files'] ) {
			$settings['min_number_of_files'] = '1';
		}
		$meta = array();
		if ( $settings['max_file_size'] > ( 1024 * 1024 ) ) {
			$meta[] = sprintf( __( 'Max file size: %.2f MB.', 'ipt_fsqm' ), ( $settings['max_file_size'] / (1024 * 1024 ) ) );
		} else {
			$meta[] = sprintf( __( 'Max file size: %.2f KB.', 'ipt_fsqm' ), ( $settings['max_file_size'] / 1024 ) );
		}

		$meta[] = sprintf( __( 'Allowed file types: %s', 'ipt_fsqm' ), $settings['accept_file_types'] );
		if ( $settings['max_number_of_files'] > 0 ) {
			$meta[] = sprintf( _n( 'Max number of file: %d', 'Max number of files: %d', $settings['max_number_of_files'], 'ipt_fsqm' ), $settings['max_number_of_files'] );
		}
		if ( $settings['min_number_of_files'] > 0 ) {
			$meta[] = sprintf( _n( 'Min number of file: %d', 'Min number of files: %d', $settings['min_number_of_files'], 'ipt_fsqm' ), $settings['min_number_of_files'] );
		}
		$colspan = 4;
		// if ( true == $settings['minimal_ui'] ) {
		// 	$colspan = 3;
		// }
		// Calculate the upload button attributes
		$upload_attr = '';
		if ( 'all' != $settings['accept'] ) {
			$upload_attr .= ' accept="' . esc_attr( $settings['accept'] . '/*' ) . '"';
		}
		if ( 'none' != $settings['capture'] ) {
			$upload_attr .= ' capture';
			if ( 'audio' != $settings['capture'] ) {
				$upload_attr .= '="' . $settings['capture'] . '"';
			}
		}
		?>
<div class="ipt_uif_uploader" id="<?php echo esc_attr( $configuration['id'] . '_uploader_wrap' ); ?>" data-settings="<?php echo esc_attr( json_encode( (object) $settings ) ); ?>" data-configuration="<?php echo esc_attr( json_encode( (object) $configuration ) ); ?>" data-formData="<?php echo esc_attr( json_encode( (object) $form_data ) ); ?>">
	<?php if ( '' !== trim( $description ) ) : ?>
	<div class="fileupload-description ipt_uif_richtext">
		<?php echo wpautop( $description ); ?>
	</div>
	<?php endif; ?>
	<?php if ( $settings['drag_n_drop'] == true && $show_ui == true ) : ?>
	<div class="fileinput-dragdrop ui-state-active">
		<span><?php echo $labels['dragdrop']; ?></span>
	</div>
	<?php endif; ?>

	<div class="fileupload-meta">
		<p><?php echo implode( ' | ', $meta ); ?></p>
	</div>


	<!-- The table listing the files available for upload/download -->
	<div class="ipt_fsqm_fileuploader_list_wrap">
		<table role="presentation" class="ipt_fsqm_fileuploader_list">
			<?php if ( $show_ui ) : ?>
			<thead>
				<tr>
					<td colspan="<?php echo $colspan; ?>">
						<div class="fileupload-buttonbar">
							<div class="fileupload-buttons">
								<span role="button" class="fileinput-button secondary-button small ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary">
									<span class="ui-button-icon-primary ui-icon ui-icon-plusthick"></span>
									<span class="ui-button-text">
										<span class="select secondary-button"><?php echo $labels['select']; ?></span>
									</span>
									<input class="ipt_uif_uploader_handle" type="file"<?php if ( ! $settings['single_upload'] ) : ?> multiple="multiple"<?php endif; ?> name="<?php echo esc_attr( $name ); ?>" id="<?php echo $this->generate_id_from_name( $name ); ?>"<?php echo $upload_attr; ?> />
								</span>
								<?php if ( $settings['auto_upload'] === false ) : ?>
								<button type="submit" class="start secondary-button small ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary">
									<span class="ui-button-icon-primary ui-icon ui-icon-circle-arrow-e"></span>
									<span class="ui-button-text">
										<?php echo $labels['start']; ?>
									</span>
								</button>
								<?php endif; ?>
								<?php if ( false == $settings['minimal_ui'] ) : ?>
									<button type="reset" class="cancel secondary-button small ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary">
										<span class="ui-button-icon-primary ui-icon ui-icon-cancel"></span>
										<span class="ui-button-text">
											<?php echo $labels['cancel']; ?>
										</span>
									</button>
								<?php endif; ?>
								<span class="fileupload-process"></span>
							</div>
							<?php if ( $settings['progress_bar'] == true ) : ?>
							<!-- The global progress state -->
							<div class="fileupload-progress fade wp-eform-fileupload-globalprogress" style="display: none;">
								<!-- The global progress bar -->
								<div class="progress ui-progressbar ui-widget ui-widget-content ui-corner-all" role="progressbar" aria-valuemin="0" aria-valuemax="100">
									<div class="progress-bar ui-progressbar-value ui-widget-header ui-corner-left" style="width: 0%;"></div>
								</div>
								<!-- The extended global progress state -->
								<div class="progress-extended">&nbsp;</div>
							</div>
							<?php endif; ?>
						</div>
					</td>

					<?php if ( $settings['can_delete'] ) : ?>
					<td class="delete_button">
						<?php if ( false == $settings['minimal_ui'] ) : ?>
							<div class="fileupload-buttonbar">
								<div class="fileupload-buttons">
									<button type="button" class="delete secondary-button small ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary">
										<span class="ui-button-icon-primary ui-icon ui-icon-trash"></span>
										<span class="ui-button-text">
											<?php echo $labels['delete']; ?>
										</span>
									</button>
								</div>
							</div>
						<?php endif; ?>
					</td>
					<?php if ( false == $settings['minimal_ui'] ) : ?>
						<td class="delete_toggle">
							<div class="fileupload-buttonbar">
								<div class="fileupload-buttons">
									<div class="ipt_uif_label_column">
										<input type="checkbox" class="toggle ipt_uif_checkbox" id="<?php echo $toggler_id; ?>" />
										<label data-labelcon="&#xe18e;" for="<?php echo $toggler_id ?>"></label>
									</div>
								</div>
							</div>
						</td>
					<?php endif;?>
					<?php endif; ?>
				</tr>
			</thead>
			<?php endif; ?>
			<tbody class="files"></tbody>
		</table>
	</div>

	<?php include dirname( __FILE__ ) . '/fileupload-template.php'; ?>

	<?php if ( isset( $settings['recaptcha'] ) && $settings['recaptcha'] ) : ?>
		<div class="ipt_uif_uploader__recaptcha">
			<p class="ipt_uif_uploader__recaptcha-title"><?php _e( 'Please solve captcha to upload files', 'ipt_fsqm' ); ?></p>
			<div class="ipt_uif_uploader__recaptcha-container">
				<?php echo ( $recaptcha_fallback ? $recaptcha_fallback : __( 'Loading captcha…', 'ipt_fsqm' ) ); ?>
			</div>
			<p class="ipt_uif_uploader__recaptcha-feedback" data-wait-text="<?php esc_attr_e( 'Please wait…', 'ipt_fsqm' ); ?>"></p>
		</div>
	<?php endif; ?>
</div>
		<?php
	}

	public function enqueue_signature() {
		// Enqueue
		wp_enqueue_script( 'jsignature', $this->bower_components . 'jSignature/libs/jSignature.min.noconflict.js', array( 'jquery' ), $this->version, true );
	}

	/**
	 * Print a signature Pad
	 *
	 * @param      string  $name         HTML name of the element
	 * @param      string  $data         base30 encoded data
	 * @param      array   $settings     Associative array of settings
	 * @param      string  $description  HTML description
	 * @param      array   $validation   The validation
	 * @param      string  $color        The color
	 */
	public function signature( $name, $data, $settings, $description = '', $validation = array(), $color = '' ) {
		$this->enqueue_signature();
		$id = $this->generate_id_from_name( $name );
		$buttons = array();
		// Undo button
		if ( isset( $settings['undo'] ) && $settings['undo'] != '' ) {
			$buttons[] = array(
				'text' => '<i class="ipticm ipt-icomoon-undo2"></i>',
				'name' => '',
				'size' => 'small',
				'style' => 'primary',
				'state' => 'normal',
				'classes' => array( 'ipt_uif_jsignature_undo', 'ipt_uif_tooltip' ),
				'type' => 'button',
				'data' => array(),
				'atts' => array(
					'title' => $settings['undo'],
				),
				'url' => '',
			);
		}

		// Reset button
		if ( isset( $settings['reset'] ) && $settings['reset'] != '' ) {
			$buttons[] = array(
				'text' => '<i class="ipticm ipt-icomoon-times"></i>',
				'name' => '',
				'size' => 'small',
				'style' => 'ui',
				'state' => 'normal',
				'classes' => array( 'ipt_uif_jsignature_reset', 'ipt_uif_tooltip' ),
				'type' => 'button',
				'data' => array(),
				'atts' => array(
					'title' => $settings['reset'],
				),
				'url' => '',
			);
		}
		$validation_class = $this->convert_validation_class( $validation );

		$style_attr = '';
		if ( '' != $color ) {
			$style_attr = ' style="color: ' . $color . ';"';
		}
		?>
<div class="ipt_uif_jsignature">
	<?php if ( '' !== trim( $description ) ) : ?>
	<div class="jsignature_description ipt_uif_richtext">
		<?php echo wpautop( $description ); ?>
	</div>
	<div class="clear"></div>
	<?php endif; ?>
	<input type="text" style="height: 0; width: 0; padding: 0; margin: 0; opacity: 0;" class="ipt_uif_jsignature_input<?php echo $validation_class; ?>" name="<?php echo esc_attr( $name ); ?>" id="<?php echo $id; ?>" value="<?php echo esc_attr( $data ); ?>">
	<div class="ipt_uif_jsignature_pad ui-state-active"<?php echo $style_attr; ?>>

	</div>
	<?php $this->buttons( $buttons ); ?>
</div>
		<?php
	}

	/*==========================================================================
	 * SORTABLE DRAGGABLE & ADDABLE LIST
	 *========================================================================*/
	public function enqueue_draggable_sortable() {
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-sortable' );
	}
	public function sda_list( $settings, $items, $data, $max_key, $id = '' ) {
		$this->enqueue_draggable_sortable();
		$default = array(
			'key' => '__SDAKEY__',
			'columns' => array(),
			'features' => array(),
			'labels' => array(),
		);
		$settings = wp_parse_args( $settings, $default );
		$settings['labels'] = wp_parse_args( $settings['labels'], array(
			'add' => __( 'Add New Item', 'ipt_fsqm' ),
		) );

		$settings['features'] = wp_parse_args( $settings['features'], array(
			'draggable' => true,
			'addable' => true,
			'max' => '',
			'hide_label' => false,
			'center_content' => false,
		) );
		$data_total = 0;
		$feature_attr = $this->convert_data_attributes( $settings['features'] );

		if ( $max_key == null && empty( $items ) ) { //No items
			$max_key = 0;
		} else { //Passed the largest key for the items, so should start from the very next key
			$max_key = $max_key + 1;
		}

		$sda_body_classes = array( 'ipt_uif_sda_body' );
		if ( true == $settings['features']['draggable'] || true == $settings['features']['addable'] ) {
			$sda_body_classes[] = 'eform-sda-has-toolbar';
		}

		?>
<div class="ipt-eform-sda ipt_uif_sda" <?php echo $feature_attr; ?> id="<?php echo esc_attr( $id ); ?>">
	<div class="<?php echo implode( ' ', $sda_body_classes ); ?>" data-buttontext="<?php printf( _x( 'please click on %1$s button to get started', 'ipt_uif_sda', 'ipt_fsqm' ), strtoupper( $settings['labels']['add'] ) ); ?>">
		<?php foreach ( $items as $item ) : ?>
			<div class="ipt_uif_sda_elem">
				<?php if ( true == $settings['features']['draggable'] || true == $settings['features']['addable'] ) : ?>
					<div class="ipt-eform-sda-toolbar">
						<?php if ( true == $settings['features']['draggable'] ) : ?>
							<div class="ipt_uif_sda_drag"><i class="ipt-icomoon-bars"></i></div>
						<?php endif; ?>
						<?php if ( true == $settings['features']['addable'] ) : ?>
							<div class="ipt_uif_sda_del"><i class="ipt-icomoon-times"></i></div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php foreach ( $settings['columns'] as $col_key => $column ) : ?>
					<?php if ( $column['clear'] ) : ?>
						<?php $this->clear( 'left' ); ?>
					<?php endif; ?>
					<?php $this->column_head( '', $column['size'] ); ?>
						<?php $this->question_container( $item[ $col_key ][0], $column['label'], '', array( array( $this, $column['type'] ), (array) $item[ $col_key ] ), $column['required'], false, true, '', array( 'eform-repeatable-qcontainer' ), array(), $settings['features']['hide_label'], $settings['features']['center_content'] ); ?>
					<?php $this->column_tail(); ?>
				<?php endforeach; ?>
				<div class="clear"></div>
			</div>
			<?php $data_total++; ?>
		<?php endforeach; ?>
	</div>

	<script type="text/html" class="ipt_uif_sda_data">
		<?php ob_start(); ?>

		<?php if ( true == $settings['features']['draggable'] || true == $settings['features']['addable'] ) : ?>
			<div class="ipt-eform-sda-toolbar">
				<?php if ( true == $settings['features']['draggable'] ) : ?>
					<div class="ipt_uif_sda_drag"><i class="ipt-icomoon-bars"></i></div>
				<?php endif; ?>
				<?php if ( true == $settings['features']['addable'] ) : ?>
					<div class="ipt_uif_sda_del"><i class="ipt-icomoon-times"></i></div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php foreach ( $settings['columns'] as $col_key => $column ) : ?>
			<?php if ( $column['clear'] ) : ?>
				<?php $this->clear( 'left' ); ?>
			<?php endif; ?>
			<?php $this->column_head( '', $column['size'] ); ?>
				<?php $this->question_container( $data[ $col_key ][0], $column['label'], '', array( array( $this, $column['type'] ), (array) $data[ $col_key ] ), $column['required'], false, true, '', array( 'eform-repeatable-qcontainer' ), array(), $settings['features']['hide_label'], $settings['features']['center_content'] ); ?>
			<?php $this->column_tail(); ?>
		<?php endforeach; ?>
		<div class="clear"></div>
		<?php
		$output = ob_get_clean();
		echo htmlspecialchars( $output );
		?>
	</script>

	<?php
	if ( true == $settings['features']['addable'] ) {
		$buttons = array();
		$buttons[] = array(
			$settings['labels']['add'],
			'',
			'small',
			'secondary',
			'normal',
			array( 'ipt_uif_sda_button' ),
			'button',
			array(
				'total' => $data_total,
				'count' => $max_key,
				'key' => $settings['key'],
			),
			array(),
			'',
			'plus',
		);

		$this->buttons( $buttons, '', array( 'ipt_uif_sda_foot' ) );
	}
	?>
	<div class="clear"></div>
</div>
		<?php
	}


	/*==========================================================================
	 * TABS AND BOXES
	 *========================================================================*/
	/**
	 * Generate Tabs with callback populators
	 * Generates all necessary HTMLs. No need to write any classes manually.
	 *
	 * @param array   $tabs Associative array of all the tab elements.
	 * $tab = array(
	 *      'id' => 'ipt_fsqm_form_name',
	 *      'label' => 'Form Name',
	 *      'callback' => 'function',
	 *      'scroll' => false,
	 *      'classes' => array(),
	 *      'has_inner_tab' => false,
	 *  );
	 * @param array   $data The HTML 5 data in forms of key => value
	 */
	public function tabs( $tabs, $data = array(), $vertical = false, $classes = array() ) {
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_tabs';
		$data_attr = $this->convert_data_attributes( $data );
		$classes[] = ( $vertical == true ) ? 'vertical' : 'horizontal';
?>
<div<?php echo $data_attr; ?> class="<?php echo implode( ' ', $classes ); ?>">
	<a href="javascript:;" class="ipt_uif_tabs_toggler ipt_uif_button"><span class="ipt_uif_text_icon_no_bg ipt-icomoon-menu2"></span></a>
	<ul>
		<?php foreach ( $tabs as $tab ) : ?>
		<?php $tab = wp_parse_args( $tab, array(
			'id' => '',
			'label' => '',
			'sublabel' => '',
			'callback' => '',
			'icon' => 'none',
			'classes' => array(),
		) ); ?>
		<li id="<?php echo $tab['id'] . '_control_li'; ?>"><a href="#<?php echo $tab['id']; ?>"><?php $this->print_icon( $tab['icon'], false ); ?><?php echo $tab['label']; ?> <?php if ( ! empty( $tab['sublabel'] ) ) echo '<span class="ipt_uif_tab_subtitle">' . $tab['sublabel'] . '</span>'; ?></a></li>
		<?php endforeach; ?>
	</ul>
	<?php foreach ( $tabs as $tab ) : ?>
	<?php
		$tab = wp_parse_args( $tab, array(
			'id' => '',
			'label' => '',
			'callback' => '',
			'icon' => 'none',
			'classes' => array(),
		) );

		if ( !$this->check_callback( $tab['callback'] ) ) {
			$tab['callback'] = array(
				array( $this, 'msg_error' ), 'Invalid Callback',
			);
		}
		$tab['callback'][1][] = $tab;
		$tab_classes = isset( $tab['classes'] ) && is_array( $tab['classes'] ) ? $tab['classes'] : array();
?>
	<div id="<?php echo $tab['id']; ?>" class="<?php echo implode( ' ', $tab_classes ); ?>">
		<?php call_user_func_array( $tab['callback'][0], $tab['callback'][1] ); ?>
		<?php $this->clear(); ?>
	</div>
	<?php endforeach; ?>
</div>
<div class="clear"></div>
		<?php
	}

	public function column( $callback, $size = 'full', $side_margin = true, $id = '', $additional_classes = array(), $tooltip = '' ) {
		if ( !$this->check_callback( $callback ) ) {
			$this->msg_error( 'Invalid Callback supplied' );
			return;
		}
		$this->column_head( $id, $size, $side_margin, $additional_classes );
		call_user_func_array( $callback[0], $callback[1] );
		$this->column_tail();
	}

	public function column_head( $id = '', $size = 'full', $side_margin = true, $additional_classes = array(), $tooltip = '' ) {
		$classes = array( 'ipt_uif_column', 'ipt_uif_column_' . esc_attr( $size ), 'ipt_uif_conditional' );
		if ( $size != 'full' ) {
			$classes[] = 'ipt_uif_column_custom';
		}
		if ( '' != $tooltip ) {
			$classes[] = 'ipt_uif_qtooltip';
		}
		$classes = array_unique( array_merge( $classes, (array) $additional_classes ) );
		$id_attr = '';
		$id = trim( $id );
		if ( '' != $id ) {
			$id_attr .= ' id="' . esc_attr( $id ) . '"';
		}
?>
<div class="<?php echo implode( ' ', $classes ); ?>"<?php echo $id_attr; ?><?php echo ( '' != $tooltip ) ? ' title="' . esc_attr( $tooltip ) . '"' : ''; ?>>
	<div class="ipt_uif_column_inner<?php if ( $side_margin ) echo ' side_margin'; ?>">
		<?php
	}

	public function column_tail() {
		$this->clear();
?>
	</div>
</div>
		<?php
	}

	/**
	 * Creates a nice looking container with an icon on top
	 *
	 * @param string  $label   The heading
	 * @param mixed   (array|string) $callback The callback function to populate.
	 * @param string  $icon    The icon. Consult the /static/fonts/fonts.css to pass class name
	 * @param int     $scroll  The scroll height value in pixels. 0 if no scroll. Default is 400.
	 * @param string  $id      HTML ID
	 * @param array   $classes HTML classes
	 * @return type
	 */
	public function container( $callback, $label, $icon = 'none', $collapsible = false, $opened = false, $after = '',  $scroll_top = false, $desc = '', $classes = array() ) {
		if ( !$this->check_callback( $callback ) ) {
			$this->msg_error( 'Invalid Callback supplied' );
			return;
		}

		$this->container_head( $label, $icon, $collapsible, $opened, $after, $classes );
		if ( '' != $desc ) {
			echo '<div class="ipt_fsqm_container_desc">';
			echo wpautop( $desc );
			echo '</div>';
		}
		call_user_func_array( $callback[0], $callback[1] );
		$this->container_tail( $scroll_top );
	}

	public function container_head( $label, $icon = 'none', $collapsible = false, $opened = false, $after = '', $classes = array() ) {
		if ( '' != $after ) {
			$after = '<div class="ipt_uif_float_right">' . $after . '</div>';
		}
		if ( ! is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_container';
		if ( $collapsible ) {
			$classes[] = 'ipt_uif_collapsible';
		}
		if ( $icon != 'none' ) {
			$classes[] = 'ipt_uif_iconbox';
		}
		$tmp_icon = (int) $icon;
		if ( $tmp_icon != 0 ) {
			$icon = $tmp_icon;
		}
?>
<div class="<?php echo implode( ' ', $classes ); ?>" data-opened="<?php echo $opened; ?>">
	<?php if ( trim( $label ) !== '' || ! empty( $icon ) || true == $collapsible ) : ?>
	<div class="ipt_uif_container_head">
		<?php echo $after; ?>
		<h3><?php if ( $collapsible ) echo '<a href="javascript:;" class="ipt_uif_collapsible_handle_anchor"><span class="ipt-icomoon-arrow-down3 collapsible_state"></span>'; ?>
			<?php if ( is_string( $icon ) ) : ?>
			<?php $this->print_icon_by_class( $icon ); ?>
			<?php else : ?>
			<?php $this->print_icon_by_data( $icon ); ?>
			<?php endif; ?>
			<?php echo '<span class="ipt_uif_container_label">' . $label . '</span>'; ?>
			<?php if ( $collapsible ) echo '</a>'; ?>
		</h3>
	</div>
	<?php endif; ?>
	<div class="ipt_uif_container_inner">
		<?php
	}

	public function container_tail() {
?>
		<?php $this->clear(); ?>
	</div>
</div>
		<?php
	}

	public function iconbox( $callback, $label, $icon, $after = '' ) {
		$this->container( $callback, $label, $icon, false, false, $after );
	}

	public function iconbox_head( $label, $icon, $after = '' ) {
		$this->container_head( $label, $icon, false, false, $after );
	}

	public function iconbox_tail() {
		$this->container_tail();
	}

	public function collapsible( $callback, $label, $icon = 'file3', $after = '', $opened = false ) {
		$this->container( $callback, $label, $icon, true, $opened, $after );
	}

	public function collapsible_head( $label, $opened = false, $icon = 'file3' ) {
		$this->container_head( $label, $icon, true, $opened );
	}

	public function collapsible_tail() {
		$this->container_tail();
	}

	public function fancy_container( $callback ) {
		$this->div( 'ipt_uif_fancy_container', $callback );
	}

	/**
	 * Divider & Heading
	 *
	 * @param      string   $text        The text
	 * @param      string   $type        The type
	 * @param      string   $align       The align
	 * @param      string   $icon        The icon
	 * @param      boolean  $scroll_top  The scroll top
	 * @param      array    $classes     The classes
	 * @param      boolean  $no_bg       No background ( Deprecated )
	 */
	public function divider( $text = '', $type = 'div', $align = 'center', $icon = 'none', $scroll_top = false, $classes = array(), $no_bg = false ) {
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_divider';
		$classes[] = 'ipt_uif_align_' . $align;
		$text = trim( $text );
		if ( '' === $text && ! $scroll_top ) {
			$classes[] = 'ipt_uif_empty_divider';
		}
		if ( 'none' === $icon || '' === $icon ) {
			$classes[] = 'ipt_uif_divider_no_icon';
		}
		if ( $no_bg === true ) {
			$classes[] = 'ipt_uif_divider_icon_no_bg';
		}
		if ( $scroll_top ) {
			$classes[] = 'ipt_uif_divider_has_scroll';
		}
		if ( '' === $text ) {
			$classes[] = 'ipt_uif_divider_no_text';
		}
		if ( 'div' == $type || '' == $text ) {
			$classes[] = 'eform-just-divider';
		}
?>
<<?php echo $type; ?> class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<?php if ( $scroll_top || $text !== '' ) : ?>
		<?php if ( $scroll_top ) : ?>
		<?php $this->print_scroll_to_top(); ?>
		<?php endif; ?>
		<span class="ipt_uif_divider_text">
			<?php $this->print_icon( $icon ); ?>
			<?php if ( $text != '' ) : ?>
				<span class="ipt_uif_divider_text_inner">
				<?php echo $text; ?>
				</span>
			<?php endif; ?>
		</span>
	<?php endif; ?>
</<?php echo $type; ?>>
		<?php
	}

	public function heading( $text, $type = 'h2', $align = 'center', $icon = 'none', $scroll_top = false, $no_bg = false, $classes = array() ) {
		if ( trim( $text ) == '' ) {
			return;
		}
		if ( ! is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_heading';
		$this->divider( $text, $type, $align, $icon, $scroll_top, $classes, $no_bg );
	}

	public function get_page_label_html( $label ) {
		return str_replace( '%%NUM%%', '<span class="eform-page-labels__number"></span>', $label );
	}


	/*==========================================================================
	 * Image Slider
	 *========================================================================*/
	public function imageslider( $id, $images, $settings, $classes = array() ) {
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_image_slider_wrap';
		$classes[] = 'theme-ipt-uif-imageslider';
		$settings = wp_parse_args( $settings, array(
				'autoslide' => true,
				'duration' => 5,
				'transition' => 1,
				'animation' => 'random',
				'on_play' => 'ipt-icomoon-pause2',
				'on_pause' => 'ipt-icomoon-play3',
			) );
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-settings="<?php echo esc_attr( json_encode( (object) $settings ) ); ?>">
	<div id="<?php echo esc_attr( $id ); ?>" class="ipt_uif_image_slider nivoSlider">
		<?php foreach ( $images as $image ) : ?>
		<?php if ( $image['url'] != '' ) : ?>
		<a href="<?php echo esc_attr( $image['url'] ); ?>">
		<?php endif; ?>
			<img src="<?php echo esc_attr( $image['src'] ); ?>" title="<?php echo esc_attr( $image['title'] ); ?>" />
		<?php if ( $image['url'] != '' ) : ?>
		</a>
		<?php endif; ?>
		<?php endforeach; ?>
	</div>
	<div class="ribbon"></div>
</div>
		<?php
	}

	/*==========================================================================
	 * Timer
	 *========================================================================*/
	public function enqueue_timer() {
		// enqueue
		wp_enqueue_script( 'jquery-time-circles', $this->bower_builds . 'timecircles/js/TimeCircles.min.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_style( 'ipt-plugin-uif-time-circles', $this->bower_builds . 'timecircles/css/TimeCircles.min.css', array(), $this->version );
	}

	/**
	 * Create a jQuery Circle Timer
	 *
	 * @param      string  $time     The date time or just time in seconds
	 * @param      string  $type     Type of the timer (Reference Time: date | Stopwatch: timer) Defaults to timer (optional)
	 * @param      array   $classes  array of additional CSS classes (optional)
	 * @param      string  $id       ID of the element (optional)
	 * @param      array   $options  TimeCircles Options (optional)
	 */
	public function timer( $time, $type = 'timer', $classes = array(), $id = '', $options = array() ) {
		$this->enqueue_timer();
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_circle_timer';
		$data_attr = ' data-timer="' . esc_attr( $time ) . '"';
		if ( $type == 'date' ) {
			$data_attr = 'data-date="' . esc_attr( $time ) . '"';
		}
		$id_attr = '';
		if ( $id != '' ) {
			$id_attr = ' id="' . esc_attr( $id ) . '"';
		}
		?>
<div data-coptions="<?php echo esc_attr( json_encode( (object) $options ) ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"<?php echo $id_attr . $data_attr; ?>></div>
		<?php
	}


	/*==========================================================================
	 * Messages
	 *========================================================================*/
	/**
	 * Prints an error message in style.
	 *
	 * @param string  $msg  The message
	 * @param bool    $echo TRUE(default) to echo the output, FALSE to just return
	 * @return string The HTML output
	 */
	public function msg_error( $msg = '', $echo = true, $title = '', $wpautop = true, $close_button = false ) {
		return $this->print_message( 'red', $msg, $echo, $title, 'info', $wpautop, $close_button );
	}

	/**
	 * Prints an update message in style.
	 *
	 * @param string  $msg  The message
	 * @param bool    $echo TRUE(default) to echo the output, FALSE to just return
	 * @return string The HTML output
	 */
	public function msg_update( $msg = '', $echo = true, $title = '', $wpautop = true, $close_button = false ) {
		return $this->print_message( 'yellow', $msg, $echo, $title, 'info', $wpautop, $close_button );
	}

	/**
	 * Prints an okay message in style.
	 *
	 * @param string  $msg  The message
	 * @param bool    $echo TRUE(default) to echo the output, FALSE to just return
	 * @return string The HTML output
	 */
	public function msg_okay( $msg = '', $echo = true, $title = '', $wpautop = true, $close_button = false ) {
		return $this->print_message( 'green', $msg, $echo, $title, 'info', $wpautop, $close_button );
	}

	public function print_message( $style, $msg = '', $echo = true, $title = '', $icon = 'info', $wpautop = true, $close_button = false ) {
		if ( $title == '' ) {
			if ( isset( $this->default_messages['messages'][$style] ) ) {
				$title = $this->default_messages['messages'][$style];
			}
		}
		if ( $icon == 'info' ) {
			switch ( $style ) {
			case 'yellow' :
			case 'update' :
				$icon = 'info3';
				break;
			case 'red' :
			case 'error' :
				$icon = 'warning2';
				break;
			case 'okay' :
			case 'green' :
				$icon = 'checkmark-circle';
				break;
			}
		}

		ob_start();
?>
<div class="ipt_uif_message ipt_fsqm_uif_message_<?php echo $style; ?> ui-widget ui-widget-content ui-corner-all ipt_uif_widget_box">
	<?php if ( $title != '' ) : ?>
	<div class="ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		<h3>
			<?php if ( $close_button ) : ?>
			<a href="javascript:void(0)" class="ipt_uif_message_close"><span class="ipt-icomoon-cancel-circle ipt_uif_text_icon_no_bg"></span></a>
			<?php endif; ?>
			<?php if ( $icon != '' ) : ?>
			<span class="ipt-icomoon-<?php echo $icon; ?> ipt_uif_text_icon_no_bg"></span>
			<?php endif; ?>
			<?php echo $title; ?>
		</h3>
	</div>
	<?php endif; ?>
	<div class="ui-widget-content ui-corner-all">
		<?php if ( $wpautop ) : ?>
			<?php echo wpautop( $msg ); ?>
		<?php else : ?>
			<?php echo $msg; ?>
		<?php endif; ?>
	</div>
</div>
		<?php
		$output = ob_get_clean();
		if ( $echo )
			echo $output;
		return $output;
	}

	/**
	 * Generate Slider Labels data attribute
	 *
	 * This is to be used directly by the slider UI and will be parsed by JS to
	 * do corresponding interface specific task, eg, applying jQuery Slider Pips
	 * and Assigning labels.
	 *
	 * @param      array    $labels  The labels settings array
	 * @param      integer  $min     The minimum
	 * @param      integer  $max     The maximum
	 * @param      integer  $step    The step
	 *
	 * @return     array    Associative array of data that is to be printed in data-labels attribute
	 */
	public function slider_labels( $labels, $min, $max, $step ) {
		$label_data = array(
			'first' => false,
			'last' => false,
			'rest' => false,
			'labels' => array(),
		);
		if ( $step == 0 ) {
			$step = 1;
		}

		// Show if needed
		if ( isset( $labels['show'] ) && $labels['show'] == true ) {
			$label_data['rest'] = 'pip';
			$label_data['first'] = 'pip';
			$label_data['last'] = 'pip';
		} else {
			// Return the empty data
			return $label_data;
		}


		// Actual length is $length + 1 (starting from zeroeth value)
		$length = floor( ($max - $min) / $step );
		// We take the lesser integer value of the mid value
		$mid_length = floor( $length / 2 );
		// From zeroeth value to length
		for ( $i = 0; $i <= $length; $i++ ) {
			$label_data['labels'][$i] = '';
		}

		// Set the first label
		if ( isset( $labels['first'] ) && $labels['first'] != '' ) {
			$label_data['first'] = 'label';
			$label_data['labels'][0] = $labels['first'];
		}

		// Set the last label
		if ( isset( $labels['last'] ) && $labels['last'] != '' ) {
			$label_data['last'] = 'label';
			$label_data['labels'][$length] = $labels['last'];
		}

		// Set the mid label
		if ( isset( $labels['mid'] ) && $labels['mid'] != '' ) {
			$label_data['rest'] = 'label';
			$label_data['labels'][$mid_length] = $labels['mid'];
		}

		// Set the rest of the labels
		if ( isset( $labels['rest'] ) && $labels['rest'] != '' ) {
			$rest_labels = explode( ',', $labels['rest'] );
			$rest_labels = array_map( 'trim', $rest_labels );

			$i = 1;
			foreach ( (array) $rest_labels as $rl ) {
				if ( $i == $mid_length ) {
					$i++;
				}
				if ( $i < $length ) {
					$label_data['labels'][$i] = $rl;
				}
				$i++;
			}
		}

		return $label_data;
	}

	/*==========================================================================
	 * CSS 3 Loader
	 *========================================================================*/
	/**
	 * Creates the HTML for the CSS3 Loader.
	 *
	 * @param      bool    $hidden   TRUE(default) if hidden in inital state
	 *                               (Optional).
	 * @param      string  $id       HTML ID (Optional).
	 * @param      array   $labels   Labels which will be converted to HTML data
	 *                               attribute
	 * @param      bool    $inline   Whether inline(true) or overlay (false)
	 * @param      string  $default  Default text
	 * @param      array   $classes  Array of additional classes (Optional).
	 */
	public function ajax_loader( $hidden = true, $id = '', $labels = array(), $inline = false, $default = null, $classes = array() ) {
		if ( ! is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		if ( ! $inline ) {
			$classes[] = 'ipt-eform-preloader';
		} else {
			$classes[] = 'ipt-eform-preloader-inline';
		}
		$classes[] = 'ipt_uif_ajax_loader';
		$id_attr = '';
		if ( '' != $id ) {
			$id_attr = ' id="' . esc_attr( trim( $id ) ) . '"';
		}
		$style_attr = '';
		if ( true == $hidden ) {
			$style_attr = ' style="display: none;"';
		}
		$data_attr = $this->convert_data_attributes( $labels );
		if ( null === $default ) {
			$default = $this->default_messages['ajax_loader'];
		}

		?>
		<style type="text/css">
			.ipt_uif_ajax_loader,
			.ipt_uif_ajax_loader *,
			ipt_uif_ajax_loader *::before,
			ipt_uif_ajax_loader *::after {
				box-sizing: border-box;
			}
		</style>
		<div class="<?php echo implode( ' ', $classes ); ?>"<?php echo $id_attr . $style_attr . $data_attr; ?>>
			<div class="ipt-eform-preloader-inner">
				<div class="ipt-eform-preloader-circle">
					<div class="preloader-wrapper active">
						<div class="spinner-layer spinner-blue">
							<div class="circle-clipper left">
								<div class="circle"></div>
							</div><div class="gap-patch">
								<div class="circle"></div>
							</div><div class="circle-clipper right">
								<div class="circle"></div>
							</div>
						</div>

						<div class="spinner-layer spinner-red">
							<div class="circle-clipper left">
								<div class="circle"></div>
							</div><div class="gap-patch">
								<div class="circle"></div>
							</div><div class="circle-clipper right">
								<div class="circle"></div>
							</div>
						</div>

						<div class="spinner-layer spinner-yellow">
							<div class="circle-clipper left">
								<div class="circle"></div>
							</div><div class="gap-patch">
								<div class="circle"></div>
							</div><div class="circle-clipper right">
								<div class="circle"></div>
							</div>
						</div>

						<div class="spinner-layer spinner-green">
							<div class="circle-clipper left">
								<div class="circle"></div>
							</div><div class="gap-patch">
								<div class="circle"></div>
							</div><div class="circle-clipper right">
								<div class="circle"></div>
							</div>
						</div>
					</div>
				</div>
				<div class="ipt-eform-preloader-text">
					<div class="ipt-eform-preloader-text-inner ipt_uif_ajax_loader_text"><?php echo $default; ?></div>
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Print icon by class name
	 *
	 * @param      string   $icon                The icon
	 * @param      boolean  $background          The background
	 * @param      array    $additional_classes  The additional classes
	 * @param      string   $title               The title
	 */
	public function print_icon_by_class( $icon = 'none', $background = true, $additional_classes = array(), $title = '' ) {
		if ( is_numeric( $icon ) ) {
			$this->print_icon_by_data( $icon, $background, $additional_classes );
			return;
		}
		if ( ! is_array( $additional_classes ) ) {
			$additional_classes = (array) $additional_classes;
		}
?>
<?php if ( 'none' != $icon && ! empty( $icon ) ) : ?>
<i title="<?php echo esc_attr( $title ); ?>" class="<?php echo implode( ' ', $additional_classes ); ?> ipt-icomoon-<?php echo esc_attr( $icon ); ?> ipticm prefix"></i>
<?php endif; ?>
		<?php
	}

	/**
	 * Print icon by data attribute
	 *
	 * @param      string   $data                The data
	 * @param      boolean  $background          The background
	 * @param      array    $additional_classes  The additional classes
	 * @param      string   $title               The title
	 */
	public function print_icon_by_data( $data = 'none', $background = true, $additional_classes = array(), $title = '' ) {
		if ( ! is_numeric( $data ) ) {
			$this->print_icon_by_class( $data, $background, $additional_classes );
			return;
		}
		if ( ! is_array( $additional_classes ) ) {
			$additional_classes = (array) $additional_classes;
		}
?>
<?php if ( 'none' != $data && ! empty( $data ) ) : ?>
<i title="<?php echo esc_attr( $title ); ?>" class="<?php echo implode( ' ', $additional_classes ); ?> ipticm prefix" data-ipt-icomoon="&#x<?php echo dechex( $data ); ?>;"></i>
<?php endif; ?>
		<?php
	}

	/**
	 * Print an icon by data or classname
	 *
	 * @param      string   $icon                The icon
	 * @param      boolean  $background          The background
	 * @param      array    $additional_classes  The additional classes
	 * @param      string   $title               The title
	 */
	public function print_icon( $icon = 'none', $background = true, $additional_classes = array(), $title = '' ) {
		if ( 'none' == $icon || empty( $icon ) ) {
			return;
		}
		if ( is_numeric( $icon ) ) {
			$this->print_icon_by_data( $icon, $background, $additional_classes, $title );
		} else {
			$this->print_icon_by_class( $icon, $background, $additional_classes, $title );
		}
	}

	/**
	 * Prints scroll to top
	 */
	public function print_scroll_to_top() {
		?>
<a href="#" class="ipt_uif_scroll_to_top"><i class="ipticm ipt-icomoon-chevron-up suffix"></i></a>
		<?php
	}

	public function question_container( $name, $title, $subtitle, $callback, $required = false, $fancy_box = false, $vertical = false, $description = '', $classes = array(), $data_attr = array(), $hidden_label = false, $centered = false ) {
		if ( ! $this->check_callback( $callback ) ) {
			$this->msg_error( __( 'Invalid Callback', 'ipt_fsqm' ) );
			return;
		}
		if ( $required ) {
			$title .= '<span class="ipt_uif_question_required">*</span>';
		}
		$description = trim( (string) $description );
		$classes = (array) $classes;
		$classes[] = 'ipt_uif_question';
		if ( $vertical ) {
			$classes[] = 'ipt_uif_question_vertical';
		}

		if ( $hidden_label || ( '' == $title && '' == $subtitle ) ) {
			$classes[] = 'ipt_uif_question_full';
		}

		if ( $centered ) {
			$classes[] = 'ipt_uif_question_centered';
		}

		$inline_data_attr = $this->convert_data_attributes( (array) $data_attr );
?>
<div class="<?php echo implode( ' ', $classes ); ?>"<?php echo $inline_data_attr; ?>>
	<?php if ( ! $hidden_label && ( '' != $title || '' != $subtitle ) ) : ?>
		<div class="ipt_uif_question_label">
			<?php $this->generate_label( $name, $title, '', 'ipt_uif_question_title' ); ?>
			<?php $this->clear(); ?>
			<?php if ( $subtitle != '' ) : ?>
			<?php $this->generate_label( $name, $subtitle, '', 'ipt_uif_question_subtitle' ); ?>
			<?php endif; ?>
			<?php if ( $description !== '' ) : ?>
			<div class="ipt_uif_richtext">
				<?php echo apply_filters( 'ipt_uif_richtext', $description ); ?>
			</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<div class="ipt_uif_question_content">
		<?php if ( $fancy_box ) : ?>
		<div class="ipt_uif_fancy_container">
		<?php endif; ?>
		<?php call_user_func_array( $callback[0], $callback[1] ); ?>
		<?php $this->clear(); ?>
		<?php if ( $fancy_box ) : ?>
		</div>
		<?php endif; ?>
	</div>
</div>
		<?php
	}

	/**
	 * Buttons and Icons
	 *
	 * @param      array   $items      Associative array of items
	 * @param      string  $alignment  Button alignment
	 * @param      string  $open       Open target, self, _blank
	 * @param      array   $dimension  The dimension
	 */
	public function iconmenu( $items, $alignment = 'center', $open = 'self', $dimension = array(), $style = [] ) {
		$style = wp_parse_args( $style, [
			'size' => 'medium',
			'style' => 'flat',
			'rounded' => true,
			'themed' => false,
		] );
		$classes = [
			'ipt-eform-material-button-container',
			'align-' . $alignment,
			'size-' . $style['size'],
			'ipt_fsqm_form_button_container--' . $style['style'],
		];
		if ( $style['rounded'] ) {
			$classes[] = 'eform-material-rounded-pb';
		}
		if ( $style['themed'] ) {
			$classes[] = 'eform-material-alternate-pb';
		}
		?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"><div class="eform-button-container-inner">
	<?php foreach ( $items as $item ) :
		$href = ( '' == $item['url'] ? 'javascript:;' : esc_attr( $item['url'] ) );
		$text = trim( $item['text'] );
		$icon = '';
		if ( isset( $item['icon'] ) && $item['icon'] !== '' && $item['icon'] != 'none' ) {
			$icon = '<i ';
			if ( is_numeric( $item['icon'] ) ) {
				$icon .= 'class="ipticm" data-ipt-icomoon="&#x' . dechex( $item['icon'] ) . ';"';
			} else {
				$icon .= 'class="ipticm ipt-icomoon-' . esc_attr( $item['icon'] ) . '"';
			}
			$icon .= '></i>';
		}

		$attr = array();
		$attr['class'] = 'ipt_uif_button eform-material-button eform-ripple';
		$has_popup = false;

		if ( 'popup' == $open ) {
			$has_popup = true;
			$attr['data-height'] = $dimension['height'];
			$attr['data-width'] =  $dimension['width'];
		} else if ( 'blank' == $open ) {
			$attr['target'] = '_blank';
			$attr['rel'] = "noopener noreferrer";
		}

		// if the link is to jump
		if ( preg_match( '/^#eform-container-(\d+)$/', $item['url'], $possible_container_jump ) ) {
			$attr['class'] .= ' ipt_fsqm_jump_button';
			$attr['data-pos'] = $possible_container_jump[1];
			$has_popup = false;
		}

		// if the link is to navigate
		if ( preg_match( '/^#eform-(next|prev|submit|reset|save)$/', $item['url'], $possible_page_nav ) ) {
			$attr['class'] .= ' eform-manual-nav-button-' . $possible_page_nav[1];
			$has_popup = false;
		}

		// Add popup class if needed
		if ( $has_popup ) {
			$attr['class'] .= ' eform-icmpopup';
		}
	?>
	<a href="<?php echo $href; ?>" <?php echo $this->convert_html_attributes( $attr ); ?>>
		<?php echo $icon; ?>
		<?php echo $text; ?>
	</a>
	<?php endforeach; ?>
</div></div>
		<?php
	}

	/**
	 * Prints an eForm styled login form
	 *
	 * @param      array  $args           The atts
	 * @param      <type>  $defaults       The defaults
	 * @param      <type>  $login_buttons  The login buttons
	 * @param      <type>  $redirect       The redirect
	 * @param      <type>  $regurl         The regurl
	 */
	public function login_form( $args, $login_buttons ) {
		/**
		 * Fires when the login form is initialized.
		 *
		 * @since 3.2.0
		 */
		// do_action( 'login_init' );

		/**
		 * Enqueue scripts and styles for the login page.
		 *
		 * @since 3.1.0
		 */
		do_action( 'login_enqueue_scripts' );

		// Filter Arguments
		$defaults = array(
			'echo' => true,
			// Default 'redirect' value takes the user back to the request URI.
			'redirect' => ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
			'form_id' => 'loginform',
			'label_username' => __( 'Username or Email Address' ),
			'label_password' => __( 'Password' ),
			'label_remember' => __( 'Remember Me' ),
			'label_log_in' => __( 'Log In' ),
			'id_username' => 'user_login',
			'id_password' => 'user_pass',
			'id_remember' => 'rememberme',
			'id_submit' => 'wp-submit',
			'remember' => true,
			'value_username' => '',
			// Set 'value_remember' to true to default the "Remember me" checkbox to checked.
			'value_remember' => false,
		);
		/**
		 * Filters the default login form output arguments.
		 *
		 * @since 3.0.0
		 *
		 * @see wp_login_form()
		 *
		 * @param array $defaults An array of default login form arguments.
		 */
		$args = wp_parse_args( $args, apply_filters( 'login_form_defaults', $defaults ) );

		// Get filters
		/**
		 * Filters content to display at the top of the login form.
		 *
		 * The filter evaluates just following the opening form tag element.
		 *
		 * @since 3.0.0
		 *
		 * @param string $content Content to display. Default empty.
		 * @param array  $args    Array of login form arguments.
		 */
		$login_form_top = apply_filters( 'login_form_top', '', $args );

		/**
		 * Filters content to display in the middle of the login form.
		 *
		 * The filter evaluates just following the location where the 'login-password'
		 * field is displayed.
		 *
		 * @since 3.0.0
		 *
		 * @param string $content Content to display. Default empty.
		 * @param array  $args    Array of login form arguments.
		 */
		$login_form_middle = apply_filters( 'login_form_middle', '', $args );

		/**
		 * Filters content to display at the bottom of the login form.
		 *
		 * The filter evaluates just preceding the closing form tag element.
		 *
		 * @since 3.0.0
		 *
		 * @param string $content Content to display. Default empty.
		 * @param array  $args    Array of login form arguments.
		 */
		$login_form_bottom = apply_filters( 'login_form_bottom', '', $args );

		foreach ( $login_buttons as $key => $login_button ) {
			$login_button[2] = 'medium';
			$login_button[3] = 'secondary';
			$login_buttons[ $key ] = $login_button;
		}
		// Start the output
		?>
<?php // Login Form Top ?>
<?php if ( $login_form_top != '' ) : ?>
	<?php $this->column_head( '', 'full' ); ?>
		<?php echo $login_form_top; ?>
	<?php $this->column_tail(); ?>
<?php endif; ?>
<?php // Username ?>
<?php $this->column_head( '', 'full' ); ?>
	<?php $this->question_container( 'log', $args['label_username'], '', array( array( $this, 'text' ), array( 'log', $args['value_username'], $args['label_username'], 'user' ) ), true, false, false, '', array(), array(), true ); ?>
<?php $this->column_tail(); ?>
<?php // Password ?>
<?php $this->column_head( '', 'full' ); ?>
	<?php $this->question_container( 'pwd', $args['label_password'], '', array( array( $this, 'password_simple' ), array( 'pwd', '', $args['label_password'], 'console' ) ), true, false, false, '', array(), array(), true ); ?>
<?php $this->column_tail(); ?>
<?php // Login Form Action ?>
<?php do_action( 'login_form' ); ?>
<?php // Login Form Middle ?>
<?php if ( $login_form_middle != '' ) : ?>
	<?php $this->column_head( '', 'full' ); ?>
		<?php echo $login_form_middle; ?>
	<?php $this->column_tail(); ?>
<?php endif; ?>
<?php // Remember Button ?>
<?php if ( $args['remember'] ) : ?>
	<?php $this->column_head( '', 'full' ); ?>
		<?php
		$this->checkbox( 'rememberme', array(
			'value' => 'forever',
			'label' => $args['label_remember'],
		), $args['value_remember'] );
		?>
	<?php $this->column_tail(); ?>
<?php endif; ?>
<?php // Login Buttons ?>
<?php $this->column_head( '', 'full' ); ?>
	<?php $this->buttons( $login_buttons, '', [
		'align-center',
		'ipt_fsqm_form_button_container--flat',
		// 'eform-material-alternate-pb',
	] ) ?>
<?php $this->column_tail(); ?>
<?php // Login Form Bottom ?>
<?php if ( $login_form_bottom != '' ) : ?>
	<?php $this->column_head( '', 'full' ); ?>
		<?php echo $login_form_bottom; ?>
	<?php $this->column_tail(); ?>
<?php endif; ?>
<input type="hidden" name="redirect_to" value="<?php echo esc_url( $args['redirect'] ); ?>" />
		<?php
	}

	public function clear( $direction = 'both' ) {
		echo '<div class="clear-' . $direction . '"></div>';
	}

	public function richtext( $text ) {
		echo apply_filters( 'ipt_uif_richtext', $text );
	}

	public function get_countries( $placeholder ) {
		$countries = IPT_FSQM_Form_Elements_Static::get_countries();
		array_unshift( $countries, array(
			'value' => '',
			'label' => $placeholder,
		) );
		return $countries;
	}

	public function get_address_validations() {
		return array(
			'address' => array(
				'required' => true,
			),
			'country' => array(
				'required' => true,
			),
			'zip' => array(
				'required' => true,
				'filters' => array(
					'type' => 'zipCode',
				),
			),
		);
	}

	public function get_cc_validation() {
		return array(
			'required' => true,
			'funccall' => 'iptUIFValidateCC',
		);
	}

	/**
	 * Get the data attributes for input masking
	 *
	 * This attributes will be binded directly, so no external calls are
	 * required.
	 *
	 * @param      array  $data   Masking data
	 *
	 * @return     array  HTML data attributes for input masking
	 */
	public function get_mask_data( $data ) {
		// Normalize
		$data = wp_parse_args( $data, [
			'type' => 'mask',
			'mask' => '',
			'placeholder' => '_',
			'greedy' => true,
		] );
		$return = [];
		// Mask or regex
		if ( 'mask' == $data['type'] ) {
			$return['inputmask-mask'] = $data['mask'];
		} else {
			$return['inputmask-regex'] = $data['mask'];
		}

		// Greedy
		$return['inputmask-greedy'] = ( $data['greedy'] ? 'true' : 'false' );

		// Placeholder
		$return['inputmask-placeholder'] = $data['placeholder'];

		return $return;
	}
} // End IPT_Plugin_UIF_Front
