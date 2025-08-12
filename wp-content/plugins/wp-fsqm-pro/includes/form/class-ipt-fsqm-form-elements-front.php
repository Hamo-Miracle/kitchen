<?php
/**
 * WP Feedback, Surver & Quiz Manager - Pro Form Elements Class
 * Frontend APIs
 *
 * Populates the actual form with all the hooks and filters
 *
 * @package eForm - WordPress Form Builder
 * @subpackage Form\Front
 * @author Swashata Ghosh <swashata@intechgrity.com>
 * @codeCoverageIgnore
 */
class IPT_FSQM_Form_Elements_Front extends IPT_FSQM_Form_Elements_Data {
	/**
	 * UI Instance
	 *
	 * @var IPT_Plugin_UIF_Front
	 */
	public $ui;

	public $doing_admin;

	public $can_submit;

	public $user_update;

	/**
	 * A special flag for live_view mode.
	 *
	 * This is intended to be used within the builder.
	 *
	 * @var boolean
	 */
	private $live_view = false;

	/**
	 * Enable the live view mode.
	 *
	 * Now the form will behave differently. It will not check
	 * for limitations and validations.
	 *
	 * @return void
	 */
	public function enable_live_view() {
		$this->live_view = true;
	}

	public function __construct( $data_id = null, $form_id = null ) {
		$this->doing_admin = false;
		$this->can_submit = true;
		parent::__construct( $data_id, $form_id );

		// Check the theme and do the UI
		$themes = $this->get_available_themes();
		$theme_info = array();
		foreach ( $themes as $theme ) {
			if ( isset( $theme['ui-class'] ) ) {
				foreach ( array_keys( $theme['themes'] ) as $theme_key ) {
					$theme_info[ $theme_key ] = $theme['ui-class'];
				}
			}
		}
		$active_theme = $this->settings['theme']['template'];
		if ( isset( $theme_info[ $active_theme ] ) && class_exists( $theme_info[ $active_theme ] ) ) {
			$this->ui = $theme_info[ $active_theme ]::instance();
		} else {
			$this->ui = IPT_Plugin_UIF_Front::instance();
		}

		$this->ui->enqueue();
		$this->enqueue();
	}

	/**
	 * Print the iframe markup needed to embed a form. It also gets the URL
	 * parameters and passes them to the form.
	 *
	 * @param bool $with_height Whether to print the iFrame with height.
	 * @return void
	 */
	public function print_iframe_html( $with_height = false ) {
		// get the URL
		$url = IPT_FSQM_Form_Elements_Static::standalone_permalink_parts( $this->form_id );
		// populate the parameters from the current parent
		$url_params = isset( $_GET ) ? $_GET : [];
		$url_params = map_deep( $url_params, 'wp_unslash' );
		// add the needed parameter
		$url_params['iframe_embed'] = true;
		$style = '';
		if ( $with_height ) {
			$style .= ' height: 500px;';
		}
		echo '<style type="text/css">';
		echo 'iframe.wpq-eform-iframe {width: 1px; min-width: 100%; border: 0 none; outline: none;}';
		echo '</style>';
		echo '<div>'; // so that it plays nice with themes with centered css
		echo '<div style="overflow: auto; -webkit-overflow-scrolling:touch;">'; // negate the padding within the iframe
		echo '<iframe scrolling="no" style="' . $style . '" allowtransparency="true" class="wpq-eform-iframe" src="' . esc_attr( add_query_arg( $url_params, $url['url'] ) ) . '"></iframe>';
		echo '</div>';
		echo '</div>';
	}

	/*==========================================================================
	 * File dependencies and enqueue
	 *========================================================================*/

	public static function register_iframeresizer() {
		wp_register_script(
			'iframe-resizer',
			IPT_FSQM_Loader::$bower_components . 'iframe-resizer/js/iframeResizer.min.js',
			[],
			IPT_FSQM_Loader::$version,
			true
		);
		wp_register_script(
			'wpq-eform-iframe-resizer',
			IPT_FSQM_Loader::$static_location . 'front/js/eform.iframe.min.js',
			array( 'iframe-resizer' ),
			IPT_FSQM_Loader::$version,
			true
		);
		return [ 'iframe-resizer', 'wpq-eform-iframe-resizer' ];
	}

	public function enqueue_iframeresizer() {
		wp_enqueue_script(
			'iframe-resizer',
			IPT_FSQM_Loader::$bower_components . 'iframe-resizer/js/iframeResizer.min.js',
			[],
			IPT_FSQM_Loader::$version,
			true
		);
		wp_enqueue_script(
			'wpq-eform-iframe-resizer',
			IPT_FSQM_Loader::$static_location . 'front/js/eform.iframe.min.js',
			array( 'iframe-resizer' ),
			IPT_FSQM_Loader::$version,
			true
		);
	}

	public function enqueue_iframeresizer_contentwindow() {
		wp_enqueue_script(
			'iframe-resizer-content-window',
			IPT_FSQM_Loader::$bower_components . 'iframe-resizer/js/iframeResizer.contentWindow.min.js',
			[],
			IPT_FSQM_Loader::$version,
			true
		);
	}

	public function enqueue( $hard = false ) {
		wp_enqueue_script( 'js-cookie', IPT_FSQM_Loader::$bower_builds . 'js-cookie/js/js.cookie.min.js', array(), IPT_FSQM_Loader::$version, true );
		wp_enqueue_script( 'ipt-fsqm-front-js', IPT_FSQM_Loader::$static_location . 'front/js/jquery.ipt-fsqm-form.min.js', array( 'jquery', 'ipt-plugin-uif-front-js', 'js-cookie' ), IPT_FSQM_Loader::$version, true );
		// Special Case for Stripe Elements Integration
		if ( $hard
			|| (
					true == $this->settings['payment']['enabled']
					&& true == $this->settings['payment']['stripe']['enabled']
				)
			) {
			// Enqueue Stripe.js
			wp_enqueue_script( 'stripe-js', 'https://js.stripe.com/v3/', array(), '3', true );
		}

		$current_datetime = current_time( 'timestamp' );
		if ( null != $this->data_id ) {
			$current_datetime = strtotime( $this->data->date );
		}
		wp_localize_script( 'ipt-fsqm-front-js', 'iptFSQM', array(
				'location' => trailingslashit( plugins_url( '/static/front/', IPT_FSQM_Loader::$abs_file ) ),
				'version' => IPT_FSQM_Loader::$version,
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'l10n' => array(
					'uploader_active_upload' => __( 'File upload in progress, please wait.', 'ipt_fsqm' ),
					'uploader_required' => __( 'Please select at least one file.', 'ipt_fsqm' ),
					'uploader_required_number' => __( 'Minimum number of required files:', 'ipt_fsqm' ),
					'validation_on_submit' => __( 'Please go through all containers and validate the marked items.', 'ipt_fsqm' ),
					'customizer_msg' => __( 'Customizer Active', 'ipt_fsqm' ),
					'reset_confirm' => $this->settings['buttons']['reset_msg'],
					'recaptcha' => __( 'Please solve the Captcha challenge', 'ipt_fsqm' ),
					'invalid_payment_form' => __( 'Please complete your payment information', 'ipt_fsqm' ),
					'require_payment_action' => __( 'Completing payment', 'ipt_fsqm' ),
				),
				'core' => array(
					'logged_in' => is_user_logged_in(),
					'siteurl' => site_url( '/' ),
				),
		) );
		do_action( 'ipt_fsqm_form_elements_front_enqueue', $this );

		// Load the styles beforehand
		// For faster loading
		// 2. Theme CSS
		$theme = $this->get_theme_by_id( $this->settings['theme']['template'] );

		if ( isset( $theme['include'] ) && is_array( $theme['include'] ) && ! empty( $theme['include'] ) ) {
			foreach ( $theme['include'] as $theme_pos => $theme_url ) {
				wp_enqueue_style( $this->settings['theme']['template'] . '_' . $theme_pos, $theme_url, array(), IPT_FSQM_Loader::$version );
			}
		}
	}

	public function custom_style() {
		$style = $this->settings['theme']['style'];
		if ( true === $style['custom_font'] ) {
			// Assume printing system-ui
			$head_font = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"';
			$body_font = $head_font;
			$link = '';
			if ( 'google_webfont' === $style['font_source'] ) {
				// If font_source is google_webfont
				$webfonts = $this->get_available_webfonts();
				$head_font_def = $webfonts[ $style['head_font'] ];
				$body_font_def = $webfonts[ $style['body_font'] ];
				$head_font = $head_font_def['label'];
				$body_font = $body_font_def['label'];
				$link = '<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family='
					. esc_attr( $head_font_def['include'] . '|' . $body_font_def['include'] ) . '" />';
			} else if ( 'custom' === $style['font_source'] ) {
				// If custom source
				$head_font = $style['head_custom'];
				$body_font = $style['body_custom'];
			}

			// Now get common stuff
			$font_size = (int) $style['base_font_size'];
			if( $font_size < 10 ) {
				$font_size = 12;
			}
			$head_font_typo = $style['head_font_typo'];
			?>
<?php echo $link; ?>
<style type="text/css">
	/*==============================================================================
	 * Font Family
	 *============================================================================*/
	body #ipt_fsqm_form_wrap_<?php echo $this->form_id ?>,
	body #ipt_fsqm_form_wrap_<?php echo $this->form_id ?>.ipt_uif_common,
	body #ipt_fsqm_form_wrap_<?php echo $this->form_id ?>.ipt_uif_common .ui-widget,
	body #ipt_fsqm_form_wrap_<?php echo $this->form_id ?>.ipt_uif_tabs.ui-tabs .ui-tabs-nav li a span,
	body #ipt_fsqm_form_wrap_<?php echo $this->form_id ?>.ipt_uif_common .ui-widget input,
	body #ipt_fsqm_form_wrap_<?php echo $this->form_id ?>.ipt_uif_common .ui-widget select,
	body #ipt_fsqm_form_wrap_<?php echo $this->form_id ?>.ipt_uif_common .ui-widget textarea,
	body #ipt_fsqm_form_wrap_<?php echo $this->form_id ?>.ipt_uif_common .ui-widget button,
	body #ipt_fsqm_form_wrap_<?php echo $this->form_id ?>.ipt_uif_common .ipt_uif_divider span.ipt_uif_divider_text span.subtitle {
		font-family: <?php echo $body_font; ?>;
	}
	body #ipt_fsqm_form_wrap_<?php echo $this->form_id ?> h1,
	body #ipt_fsqm_form_wrap_<?php echo $this->form_id ?> h2,
	body #ipt_fsqm_form_wrap_<?php echo $this->form_id ?> h3,
	body #ipt_fsqm_form_wrap_<?php echo $this->form_id ?> h4,
	body #ipt_fsqm_form_wrap_<?php echo $this->form_id ?> h5,
	body #ipt_fsqm_form_wrap_<?php echo $this->form_id ?> h6,
	body .ipt_fsqm_form_tabs .ui-tabs-nav,
	#ipt_fsqm_form_wrap_<?php echo $this->form_id ?> .ipt_uif_matrix thead,
	#ipt_fsqm_form_wrap_<?php echo $this->form_id ?> .ipt_uif_matrix th,
	body .ipt_fsqm_form_sda .ipt_fsqm_form_sda_head,
	body .ui-dialog .ui-dialog-title,
	body #ipt_fsqm_form_wrap_<?php echo $this->form_id ?> ul.ipt_fsqm_form_ul_menu li a,
	body #ipt_fsqm_form_wrap_<?php echo $this->form_id ?> .ipt_fsqm_form_message,
	body #ipt_fsqm_form_wrap_<?php echo $this->form_id ?> .ipt_uif_tabs.ui-tabs .ui-tabs-nav li,
	body #ipt_fsqm_form_wrap_<?php echo $this->form_id ?> .ipt_uif_question .ipt_uif_question_label .ipt_uif_question_title,
	body #ipt_fsqm_form_wrap_<?php echo $this->form_id ?> .ipt_uif_divider {
		font-family: <?php echo $head_font; ?>;
		font-weight: <?php echo ( $head_font_typo['bold'] == true ? 'bold' : 'normal' ); ?>;
		font-style: <?php echo ( $head_font_typo['italic'] == true ? 'italic' : 'normal' ); ?>;
	}

	body #ipt_fsqm_form_wrap_<?php echo $this->form_id ?> {
		font-size: <?php echo $font_size; ?>px;
	}
/**/
</style>
			<?php
		}
		?>
<?php if ( true === $this->settings['theme']['custom_style'] ) : ?>
<style type="text/css">
	<?php echo $this->settings['theme']['style']['custom']; ?>
/**/
</style>
<?php endif; ?>
		<?php
	}

	public function no_script() {
		?>
<noscript>
	<div class="ipt_fsqm_form_message_noscript ui-widget ui-widget-content ui-corner-all">
		<div class="ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<div><?php _e( 'Javascript is disabled', 'ipt_fsqm' ); ?></div>
		</div>
		<div class="ui-widget-content ui-corner-bottom">
			<p><?php _e( 'Javascript is disabled on your browser. Please enable it in order to use this form.', 'ipt_fsqm' ); ?></p>
		</div>
	</div>
</noscript>
		<?php
	}

	public function container( $callback, $init_loader = false, $form_class = true, $additional_classes = array() ) {
		if ( !$this->ui->check_callback( $callback ) ) {
			$this->ui->msg_error( __( 'System fault (invalid cb)', 'ipt_fsqm' ) );
			return;
		}
		$theme = $this->get_theme_by_id( $this->settings['theme']['template'] );
		// Include the JS
		// @since v2.4.0
		if ( isset( $theme['js'] ) && ! empty( $theme['js'] ) ) {
			foreach ( (array) $theme['js'] as $js_id => $js_src ) {
				wp_enqueue_script( 'ipt_fsqm_custom_js-' . $js_id, $js_src, array( 'ipt-fsqm-front-js' ), IPT_FSQM_Loader::$version );
			}
		}
		$this->custom_style();

		// Generate the CSS classes
		$classes = array( 'ipt_uif_front', 'ipt_uif_common', 'type_' . $this->type, 'ui-front' );
		if ( true == $form_class ) {
			$classes[] = 'ipt_fsqm_form';
		}
		$classes = array_merge( $classes, (array) $additional_classes );
		if ( is_rtl() ) {
			$classes[] = 'eform-rtl';
		} else {
			$classes[] = 'eform-ltr';
		}
		// Add overriding element style and alignment
		$classes[] = 'eform-override-element-' . $this->settings['theme']['element_style'];
		$classes[] = 'eform-override-alignment-' . $this->settings['theme']['align_override'];
		?>
<div
	id="ipt_fsqm_form_wrap_<?php echo $this->form_id ?>"
	class="<?php echo implode( ' ', $classes ); ?>"
	data-ui-type="<?php echo esc_attr( $this->type ); ?>"
	data-ui-theme="<?php echo esc_attr( json_encode( $theme['include'] ) ); ?>"
	data-ui-theme-id="<?php echo esc_attr( $this->settings['theme']['template'] ); ?>"
	data-animation="<?php echo ( $this->settings['theme']['waypoint'] == true ? 1 : 0 ); ?>"
	data-subscription-form="<?php echo ( $this->is_subscription_payment_form() ? 'true' : 'false' ); ?>"
>
	<?php $this->no_script(); ?>
	<?php if ( $init_loader ) : ?>
	<?php $this->ui->ajax_loader( false, '', array(), true, __( 'Loading', 'ipt_fsqm' ), array( 'ipt_uif_init_loader' ) ); ?>
	<div style="display: none;" class="ipt_uif_hidden_init">
	<?php endif; ?>
	<?php call_user_func_array( $callback[0], $callback[1] ); ?>
	<?php if ( $init_loader ) : ?>
	</div>
	<?php endif; ?>
</div>
		<?php
	}

	public function print_login_message() {
		$current_url = IPT_FSQM_Form_Elements_Static::get_current_url();
		ob_start();
		if ( $this->settings['limitation']['logged_in_fallback'] == 'redirect' ) {
			$redirect_url = str_replace( '_self_', urlencode( $current_url ), $this->settings['limitation']['non_logged_redirect'] );
			?>
<p><?php printf( __( 'You will be redirected to the login page in 5 seconds. If you wish to proceed immediately then please <a href="%1$s">click here</a>', 'ipt_fsqm' ), $redirect_url ); ?></p>
<script type="text/javascript">
	setTimeout(function() {
		window.location.href = '<?php echo $redirect_url; ?>';
	}, 5000);
</script>
			<?php
		} else {
			$ui = $this->ui;
			$defaults = array(
				'echo' => true,
				'redirect' => $current_url,
				'form_id' => 'ipt_fsqm_up_login',
				'label_username' => __( 'Username' ),
				'label_password' => __( 'Password' ),
				'label_remember' => __( 'Remember Me' ),
				'label_log_in' => __( 'Log In' ),
				'id_username' => 'ipt_fsqm_up_user_name',
				'id_password' => 'ipt_fsqm_up_user_pwd',
				'id_remember' => 'ipt_fsqm_up_rmm',
				'id_submit' => 'wp-submit',
				'remember' => true,
				'value_username' => '',
				'value_remember' => false, // Set this to true to default the "Remember me" checkbox to checked
			);
			$args = wp_parse_args( array(), apply_filters( 'login_form_defaults', $defaults ) );
			$login_buttons = array();
			$login_buttons[] = array(
				__( 'Login', 'ipt_fsqm' ),
				'wp-submit',
				'normal',
				'none',
				'normal',
				array(),
				'submit',
				array(),
				array(),
				'',
				'switch',
			);

			if ( get_option( 'users_can_register', false ) ) {
				$login_buttons[] = array(
					__( 'Register', 'ipt_fsqm' ),
					'ipt_fsqm_up_reg',
					'normal',
					'none',
					'normal',
					array(),
					'button',
					array(),
					array( 'onclick' => 'javascript:window.location.href="' . wp_registration_url() . '"' ),
					'',
					'signup',
				);
			}

			$login_buttons[] = array(
				__( 'Forgot Password', 'ipt_fsqm' ),
				'ipt_fsqm_up_rpwd',
				'normal',
				'none',
				'normal',
				array(),
				'button',
				array(),
				array( 'onclick' => 'javascript:window.location.href="' . wp_lostpassword_url( $current_url ) . '"' ),
				'',
				'info3',
			);
			?>

<form action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" name="<?php echo $args['form_id']; ?>" id="<?php echo $args['form_id']; ?>" method="post">
	<?php $this->ui->login_form( $args, $login_buttons ); ?>
	<div class="clear"></div>
</form>
			<?php
		}
		return ob_get_clean();
	}

	public function startup_limitation( $startup_instance, $current_time ) {
		ob_start();
		$this->ui->timer( $startup_instance - $current_time, 'timer', array( 'ipt_fsqm_form_startup_timer' ) );
		$timer = ob_get_clean();
		$this->ui->msg_error( $this->settings['limitation']['starting_msg'] . $timer, true, $this->settings['limitation']['starting_title'] );
	}

	public function payment_retry() {
		// First check for all errors
		if ( null == $this->data_id ) {
			$this->ui->msg_error( __( 'Data does not exist.', 'ipt_fsqm' ) );
			return;
		}

		$elem_keys = $this->get_keys_from_layouts_by_types( 'payment', $this->layout );
		if ( empty( $elem_keys ) ) {
			$this->ui->msg_error( __( 'No payment elements exist.', 'ipt_fsqm' ) );
			return;
		}

		if ( $this->settings['payment']['enabled'] == false ) {
			$this->ui->msg_error( __( 'Payment is not enabled.', 'ipt_fsqm' ) );
			return;
		}

		$key = $elem_keys[0];

		$element_data = $this->get_element_from_layout( array(
			'type' => 'payment',
			'm_type' => 'pinfo',
			'key' => $key,
		) );

		if ( false === $this->validate_data_against_conditional_logic( $element_data, $key ) ) {
			$this->ui->msg_error( __( 'Payment is not needed.', 'ipt_fsqm' ) );
			return;
		}

		global $wpdb, $ipt_fsqm_info;
		$payment_status = $wpdb->get_var( $wpdb->prepare( "SELECT status FROM {$ipt_fsqm_info['payment_table']} WHERE data_id = %d", $this->data_id ) );
		if ( $payment_status == 1 ) {
			$this->ui->msg_error( __( 'Payment is already processed.', 'ipt_fsqm' ) );
			return;
		}

		$submission_data = $this->data->pinfo[$key];
		$name_prefix = 'ipt_fsqm_form_' . $this->form_id . '[' . $element_data['m_type'] . '][' . $key . ']';
		$hidden_input = array(
			'type' => $submission_data['type'],
			'm_type' => $submission_data['m_type'],
			'value' => $submission_data['value'],
			'coupon' => $submission_data['coupon'],
			'couponval' => $submission_data['couponval'],
		);

		$this->ui->enqueue_payment();

		echo '<div class="ipt-eform-content eform-payment-retry">';

		// All errors accounted for
		// Now show the form
		// We shall show a simple form
		// wrapped inside
		?>
<form method="post" action="" class="ipt_uif_validate_form ipt_fsqm_main_form ipt_fsqm_payment_retry_form" id="ipt_fsqm_form_<?php echo $this->form_id; ?>" autocomplete="<?php echo ( $this->settings['submission']['no_auto_complete'] ? 'off' : 'on' ); ?>">
	<input type="hidden" data-sayt-exclude name="action" id="action" value="ipt_fsqm_retry_payment" />

	<input type="hidden" data-sayt-exclude name="data_id" value="<?php echo esc_attr( $this->data_id ); ?>" />

	<input type="hidden" data-sayt-exclude name="form_id" value="<?php echo esc_attr( $this->form_id ); ?>" />

	<input type="hidden" data-sayt-exclude class="ipt_fsqm_form_data_payment_retry_value" value="<?php echo esc_attr( $submission_data['couponval'] === '' ?  $submission_data['value'] : $submission_data['couponval'] ); ?>" />
	<?php printf( '<input type="hidden" id="ipt_fsqm_form_data_payment_retry" name="ipt_fsqm_form_data_payment_retry" value="%2$s" data-sayt-exclude />', $this->form_id, wp_create_nonce( 'ipt_fsqm_form_data_payment_retry_' . $this->form_id ) ); ?>
	<?php wp_referer_field( true ); ?>
	<div class="ipt_uif_mother_wrap">
		<div class="ipt_uif_column ipt_uif_column_full ipt_fsqm_main_heading_column">
			<div class="ipt_uif_column_inner">
				<?php $this->ui->heading( __( 'Retry payment form', 'ipt_fsqm' ), 'h2', 'left', $element_data['settings']['icon'], false, false, array( 'ipt_fsqm_main_heading' ) ); ?>
			</div>
		</div>
		<?php $this->ui->hiddens( $hidden_input, $name_prefix ); ?>
		<?php
		$this->ui->column_head( 'eform_payment_retry_' . $this->data_id . '_transaction', 'full', true, 'ipt_fsqm_container_checkout' );
		$this->get_transaction_status( false, true );
		$this->ui->column_tail();

		$this->ui->column_head( 'eform_payment_retry_' . $this->data_id . '_checkout', 'full', true, 'ipt_fsqm_container_checkout' );
		$payment_form = new EForm_Payment_Form( $this->form_id, $name_prefix, $this->ui, $element_data, $key, $this->settings['payment'] );
		$payment_form->show_form();
		$this->ui->column_tail();

		do_action( 'ipt_fsqm_payment_retry_form', $this );

		$buttons = array();
		$buttons[] = array(
			'text' => $this->settings['buttons']['submit'],
			'name' => 'ipt_fsqm_form_' . $this->form_id . '_button_submit',
			'size' => 'small',
			'style' => 'primary',
			'state' => 'normal',
			'classes' => array( 'ipt_fsqm_form_button_submit' ),
			'type' => 'submit',
		);
		$this->ui->clear();
		?>
	</div>
	<?php $this->ui->buttons( $buttons, 'ipt_fsqm_form_' . $this->form_id . '_button_container', 'ipt_fsqm_form_button_container' ); ?>
</form>
<?php $this->ui->clear(); ?>
<div style="display: none;" class="ipt_fsqm_form_message_success ui-widget ui-widget-content ui-corner-all ipt_uif_widget_box">
	<div class="ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		<h3><?php $this->ui->print_icon_by_class( 'checkmark-circle', false ); ?><?php echo $this->settings['submission']['success_title']; ?></h3>
	</div>
	<div class="ui-widget-content ui-corner-all ipt_fsqm_success_wrap">
		<?php echo wpautop( $this->settings['submission']['success_message'] ); ?>
	</div>
</div>
<div style="display: none;" class="ipt_fsqm_form_message_error ui-widget ui-widget-content ui-corner-all ipt_uif_widget_box">
	<div class="ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		<div><?php $this->ui->print_icon_by_class( 'laptop', false ); ?><?php _e( 'Server Side Error', 'ipt_fsqm' ); ?></div>
	</div>
	<div class="ui-widget-content ui-corner-all">
		<p><?php _e( 'We faced problems while connecting to the server or receiving data from the server. Please wait for a few seconds and try again.', 'ipt_fsqm' ); ?></p>
		<p><?php _e( 'If the problem persists, then check your internet connectivity. If all other sites open fine, then please contact the administrator of this website with the following information.', 'ipt_fsqm' ); ?></p>
		<p class="jqXHR">
			<strong><?php _e( 'TextStatus: ', 'ipt_fsqm' ) ?></strong><span class="textStatus"><?php _e( 'undefined', 'ipt_fsqm' ) ?></span><br />
			<strong><?php _e( 'HTTP Error: ', 'ipt_fsqm' ) ?></strong><span class="errorThrown"><?php _e( 'undefined', 'ipt_fsqm' ) ?></span>
		</p>
	</div>
</div>
<div style="display: none" class="ipt_uif_widget_box ipt_fsqm_form_message_process">
	<div class="ui-widget ui-widget-header ui-corner-all">
		<?php $this->ui->ajax_loader( false, '', array(), true, $this->settings['submission']['process_title'] ); ?>
	</div>
</div>
		<?php
		echo '</div>';
	}

	/*==========================================================================
	 * Form Frontend
	 * Show Form
	 * Trackback
	 * Edit for Admin
	 *========================================================================*/
	/**
	 * Prints the payment estimator slider
	 *
	 * Creates the structure of parameters and calls the corresponding method
	 * from the UI class.
	 */
	public function print_payment_estimator() {
		$op = $this->settings['payment']['estimation'];
		$config = [
			'total' => $op['math_total'],
			'min' => $op['math_min'],
			'max' => $op['math_max'],
			'precision' => $op['decimal'],
			'area' => $op['max'],
			'use_grouping' => $op['use_grouping'],
			'separator' => $op['num_separator'],
			'decimal' => $op['num_decimal'],
		];
		$bubble = [
			'style' => $op['bubble'],
			'prefix' => $op['prefix'],
			'suffix' => $op['suffix'],
			'heading' => $op['heading'],
			'separator' => $op['separator'],
			'attribute_heading' => $op['attribute_heading'],
			'attributes' => $op['attributes'],
		];
		$this->ui->estimator_slider( $op['ui_type'], $op['slider'], $config, $bubble );
	}

	/**
	 * Show the form
	 *
	 * @param      boolean  $can_submit       Whether the user can submit the
	 *                                        form
	 * @param      boolean  $doing_admin      If admin is doing an update
	 *                                        request
	 * @param      int      $type_override    Override the type 0|1|2
	 * @param      boolean  $print_container  Whether or not to print the
	 *                                        container
	 * @param      boolean  $user_update      Whether the user is an update
	 *                                        request, it will be overriden if
	 *                                        the form settings doesn't support
	 */
	public function show_form( $can_submit = true, $doing_admin = false, $type_override = null, $print_container = true, $user_update = false ) {
		global $wpdb, $ipt_fsqm_info;
		$auto_restore = true;
		if ( null == $this->form_id ) {
			$this->container( array( array( $this->ui, 'msg_error' ), array( __( 'Please check the code.', 'ipt_fsqm' ), true, __( 'Invalid ID', 'ipt_fsqm' ) ) ), true );
			return;
		}
		if ( empty( $this->layout ) ) {
			$this->container( array( array( $this->ui, 'msg_error' ), array( __( 'This form does not contain anything.', 'ipt_fsqm' ), true, __( 'Incomplete Form', 'ipt_fsqm' ) ) ), true );
			return;
		}
		if ( $type_override !== null ) {
			$this->type = $type_override;
		}

		if ( true === $doing_admin ) {
			$this->doing_admin = true;
			$auto_restore = false;
		}

		if ( false === $can_submit ) {
			$this->can_submit = false;
			$auto_restore = false;
		}
		if ( false == $this->settings['save_progress']['auto_save'] ) {
			$auto_restore = false;
		}

		if ( $this->settings['save_progress']['auto_save'] ) {
			wp_enqueue_script( 'jquery-sayt', IPT_FSQM_Loader::$static_location . 'front/js/sayt.jquery.min.js', array( 'jquery', 'js-cookie' ), IPT_FSQM_Loader::$version, true );
		}

		// Check for user limitation and logged_in limitation
		// only if it is not admin or not update and not in live_view
		if ( ! $this->live_view && ! $this->doing_admin && ! $user_update ) {
			// Startup Check
			if ( $this->settings['limitation']['starting_limit'] != '' && $can_submit ) {
				$startup_instance = strtotime( $this->settings['limitation']['starting_limit'] );
				$current_time = current_time( 'timestamp' );
				if ( $current_time < $startup_instance ) {
					$this->container( array( array( $this, 'startup_limitation' ), array( $startup_instance, $current_time ) ), true );
					return;
				}
			}


			// Expiration check
			if ( $this->settings['limitation']['expiration_limit'] != '' ) {
				$expiration_instant = strtotime( $this->settings['limitation']['expiration_limit'] );
				$current_time = current_time( 'timestamp' );
				if ( $current_time >= $expiration_instant ) {
					$this->container( array( array( $this->ui, 'msg_error' ), array( $this->settings['limitation']['expiration_msg'], true, __( 'Oops!', 'ipt_fsqm' ) ) ), true );
					return;
				}
			}

			// Login check
			if ( ( $this->settings['limitation']['logged_in'] == true || true == $this->is_subscription_payment_form() ) && ! is_user_logged_in() && $can_submit ) {
				echo '<div class="eform-form-login-wrap" style="margin: 0 auto 1em; max-width: 600px;">';
				$this->container( array( array( $this->ui, 'msg_error' ), array( $this->print_login_message(), true, __( 'Please login to continue', 'ipt_fsqm' ), false ) ), true );
				echo '</div>';
				return;
			}

			// User limit check
			if ( $this->settings['limitation']['user_limit'] == true && is_user_logged_in() && $can_submit ) {
				$total_users = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d AND user_id = %d", $this->form_id, $this->data->user_id ) );
				if ( $total_users >= $this->settings['limitation']['user_limit'] ) {
					$this->container( array( array( $this->ui, 'msg_error' ), array( str_replace('%PORTAL_LINK%', $this->get_utrackback_url(), $this->settings['limitation']['user_limit_msg'] ), true, __( 'Attention!', 'ipt_fsqm' ) ) ), true );
					return;
				}
			}

			// Total Limit Check
			if ( $this->settings['limitation']['total_limit'] == true && $can_submit ) {
				$total_submissions = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d", $this->form_id ) );
				if ( $total_submissions >= $this->settings['limitation']['total_limit'] ) {
					$this->container( array( array( $this->ui, 'msg_error' ), array( $this->settings['limitation']['total_limit_msg'], true, __( 'Attention!', 'ipt_fsqm' ) ) ), true );
					return;
				}
			}

			// Interval check
			if ( (int) $this->settings['limitation']['interval_limit'] > 0 && is_user_logged_in() && $can_submit ) {
				$interval_limit = (int) $this->settings['limitation']['interval_limit'] * 60;
				$last_submission_time = $wpdb->get_var( $wpdb->prepare( "SELECT date FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d AND user_id = %d ORDER BY date DESC LIMIT 0, 1", $this->form_id, $this->data->user_id ) );
				$current_time = current_time( 'timestamp' );
				if ( null != $last_submission_time ) {
					$time_difference = $current_time - strtotime( $last_submission_time );
					if ( $interval_limit > $time_difference ) {
						$this->container( array( array( $this->ui, 'msg_error' ), array( sprintf( $this->settings['limitation']['interval_msg'], $this->seconds_to_words( abs( $time_difference - $interval_limit ) ) ), true, __( 'Oops!', 'ipt_fsqm' ) ) ), true );
						return;
					}
				}
			}

			// Cookie Check
			if ( 0 < (int) $this->settings['limitation']['cookie_limit'] && isset( $_COOKIE['eform-submission-' . $this->form_id ] ) ) {
				if ( $_COOKIE['eform-submission-' . $this->form_id ] >= $this->settings['limitation']['cookie_limit'] ) {
					$this->container( array( array( $this->ui, 'msg_error' ), array( $this->settings['limitation']['cookie_limit_msg'], true, __( 'Attention!', 'ipt_fsqm' ) ) ), true );
					return;
				}
			}

			// IP Check
			if ( 0 < (int) $this->settings['limitation']['ip_limit'] ) {
				$total_ips = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d AND ip = %s", $this->form_id, $this->data->ip ) );
				if ( $total_ips >= $this->settings['limitation']['ip_limit'] ) {
					$this->container( array( array( $this->ui, 'msg_error' ), array( $this->settings['limitation']['ip_limit_msg'], true, __( 'Attention!', 'ipt_fsqm' ) ) ), true );
					return;
				}
			}

			// Logged In Check
			if ( true == $this->settings['limitation']['logged_out'] && is_user_logged_in() ) {
				if ( ! empty( $this->settings['limitation']['logged_msg'] ) ) {
					$this->container( array( array( $this->ui, 'msg_error' ), array( $this->settings['limitation']['logged_msg'], true, __( 'Attention!', 'ipt_fsqm' ) ) ), true );
				}
				return;
			}
		}

		// Expiration check for updation
		if ( $this->settings['limitation']['expiration_limit'] != '' && $user_update && $this->settings['limitation']['no_edit_expiration'] == true ) {
			$expiration_instant = strtotime( $this->settings['limitation']['expiration_limit'] );
			$current_time = current_time( 'timestamp' );
			if ( $current_time >= $expiration_instant ) {
				$this->container( array( array( $this->ui, 'msg_error' ), array( $this->settings['limitation']['expiration_msg'], true, __( 'Oops!', 'ipt_fsqm' ) ) ), true );
				return;
			}
		}

		if ( $this->type != 0 ) {
			$tabs = array();
			foreach ( $this->layout as $l_key => $layout ) {
				$tabs[] = array(
					'id' => 'ipt_fsqm_form_' . $this->form_id . '_tab_' . $l_key,
					'label' => $layout['title'],
					'sublabel' => $layout['subtitle'],
					'classes' => array( 'ipt_fsqm_form_tab_panel' ),
					'icon' => $layout['icon'],
					'callback' => array( array( $this, 'populate_layout' ), array( $l_key, $layout ) )
				);
			}
		}
		$theme = $this->get_theme_by_id( $this->settings['theme']['template'] );
		// Call theme options
		if ( isset( $theme['option_callback'] ) && is_callable( $theme['option_callback'] ) ) {
			call_user_func( $theme['option_callback'], $this );
		}
		// Include the JS
		// @since v2.4.0
		if ( isset( $theme['js'] ) && ! empty( $theme['js'] ) ) {
			foreach ( (array) $theme['js'] as $js_id => $js_src ) {
				wp_enqueue_script( 'ipt_fsqm_custom_js-' . $js_id, $js_src, array( 'ipt-fsqm-front-js' ), IPT_FSQM_Loader::$version );
			}
		}
		$user_update = $user_update && $this->settings['general']['can_edit'];
		$this->user_update = $user_update;

		$conditionals = $this->populate_conditional_logic();

		// Get the timer
		$timer = $this->populate_timer();

		// Set the sayt settings
		$sayt_save = array(
			'auto_save' => $this->settings['save_progress']['auto_save'],
			'show_restore' => $this->settings['save_progress']['show_restore'],
			'restore' => $auto_restore,
			'admin_override' => $this->doing_admin,
			'user_update' => $user_update,
			'interval_save' => $this->settings['save_progress']['interval_save'],
			'interval' => $this->settings['save_progress']['interval'],
		);

		// Get the Google Analytics
		$fsqm_ga = array();
		if ( ! $this->doing_admin && $can_submit ) {
			$fsqm_ga = $this->settings['ganalytics'];
			$fsqm_ga['user_update'] = $this->user_update;
			$fsqm_ga['name'] = $this->name;
			$fsqm_ga['form_id'] = $this->form_id;
		}

		// Set Form Reset
		$form_reset = array(
			'reset' => false,
			'delay' => absint( $this->settings['submission']['reset_delay'] ),
		);
		if ( ! $doing_admin && ! $user_update && $can_submit ) {
			$form_reset['reset'] = $this->settings['submission']['reset_on_submit'];
		}

		// Set core registration data
		$reg_data = array(
			'enabled' => $this->settings['core']['reg']['enabled'],
			'username_id' => $this->get_reg_field_id( $this->settings['core']['reg']['username_id'] ),
			'password_id' => $this->get_reg_field_id( $this->settings['core']['reg']['password_id'] ),
			'hide_pinfo' => $this->settings['core']['reg']['hide_pinfo'],
			'hide_meta' => $this->settings['core']['reg']['hide_meta'],
			'meta' => array(),
		);
		if ( true == $this->settings['core']['reg']['enabled'] && ! empty( $this->settings['core']['reg']['meta'] ) ) {
			foreach ( $this->settings['core']['reg']['meta'] as $regmeta ) {
				$reg_data['meta'][] = array(
					'm_type' => $regmeta['m_type'],
					'key' => $regmeta['key'],
				);
			}
		}

		// Set scroll data
		$scroll_config = $this->settings['type_specific']['scroll'];
		// can't really change the settings location because of old relic, but we
		// can make it work.
		$scroll_config['scrollOnError'] = $this->settings['type_specific']['tab']['scroll_on_error'];

		// Interactive form
		if ( true == $this->settings['general']['pipe'] ) {
			$reactive_helper = new EForm_Reactive_Helper( $this->form_id );
			$reactive_helper->add_react_filters();
			EForm_Reactive_Helper::enqueue( defined( 'WP_DEBUG' ) && WP_DEBUG );
		}

		// Epoc timing generated from server
		$epoc_data = [
			'CURRENTYEAR' => (int) date( 'Y', $this->data->epoc ),
			'CURRENTDATE' => $this->data->epoc * 1000, // because in javascript it is in milliseconds
			'CURRENTDAY' => (int) date( 'd', $this->data->epoc ), // with leading zero
			'CURRENTMONTH' => (int) date( 'm', $this->data->epoc ), // with leading zero
			'CURRENTHOUR' => (int) date( 'H', $this->data->epoc ), // with leading zero
			'CURRENTMINUTE' => (int) date( 'i', $this->data->epoc ), // always leading zero
			'CURRENTSECOND' => (int) date( 's', $this->data->epoc ), // always leading zero
			'CURRENTWEEK' => (int) date( 'W', $this->data->epoc ), // ISO-8601 week number
			'CURRENTEPOC' => floor( $this->data->epoc / DAY_IN_SECONDS ),
		];

		// Instant subscription
		$instant_subscription = [
			'doable' => ( $can_submit && ! $doing_admin && ! $user_update ),
			'config' => $this->settings['integration']['instant'],
			'nonce' => ( $can_submit && ! $doing_admin && ! $user_update )
				? wp_create_nonce( 'ipt_fsqm_instant_subs_' . $this->form_id )
				: null,
		];
?>
<!-- Form Created using eForm - Ultimate WordPress Form Builder https://eform.live -->
<?php if ( $print_container ) : ?>
<?php
$eform_container_classes = [ 'ipt_uif_front', 'ipt_uif_common', 'ipt_fsqm_form', 'type_' . $this->type, 'ui-front' ];
$eform_container_classes[] = is_rtl() ? 'eform-rtl' : 'eform-ltr';
$eform_container_classes[] = 'eform-override-element-' . $this->settings['theme']['element_style'];
$eform_container_classes[] = 'eform-override-alignment-' . $this->settings['theme']['align_override'];
// Old class string
/* ipt_uif_front ipt_uif_common ipt_fsqm_form type_<?php echo $this->type; ?> ui-front <?php echo ( is_rtl() ? 'eform-rtl' : 'eform-ltr' ); ?> */
?>
<div
	id="ipt_fsqm_form_wrap_<?php echo $this->form_id ?>"
	class="<?php echo implode( ' ', $eform_container_classes ); ?>"
	data-fsqmsayt="<?php echo esc_attr( json_encode( (object) $sayt_save ) ); ?>"
	data-ui-type="<?php echo esc_attr( $this->type ); ?>"
	data-ui-theme="<?php echo esc_attr( json_encode( $theme['include'] ) ); ?>"
	data-ui-theme-id="<?php echo esc_attr( $this->settings['theme']['template'] ); ?>"
	data-eformanim="<?php echo ( $this->settings['theme']['waypoint'] == true ? 1 : 0 ); ?>"
	data-fsqmga="<?php echo esc_attr( json_encode( (object) $fsqm_ga ) ); ?>"
	data-fsqmreset="<?php echo esc_attr( json_encode( (object) $form_reset ) ); ?>"
	data-eformreg="<?php echo esc_attr( json_encode( (object) $reg_data ) ); ?>"
	data-eformscroll="<?php echo esc_attr( json_encode( (object) $scroll_config ) ); ?>"
	data-eform-cookie="<?php echo $this->settings['limitation']['cookie_limit']; ?>"
	data-subscription-form="<?php echo ( $this->is_subscription_payment_form() ? 'true' : 'false' ); ?>"
	data-instant-subscription="<?php echo esc_attr( json_encode( $instant_subscription ) ); ?>"
>
	<?php $this->custom_style(); ?>
	<?php $this->no_script(); ?>
	<?php $this->ui->ajax_loader( false, '', array(), true, __( 'Loading', 'ipt_fsqm' ), array( 'ipt_uif_init_loader' ) ); ?>
<?php endif; ?>
	<input type="hidden" data-sayt-exclude class="ipt_uif_conditional_logic" id="ipt_uif_conditional_logic_<?php echo $this->form_id ?>" value="<?php echo esc_attr( json_encode( (object) $conditionals ) ); ?>" />
	<input type="hidden" data-sayt-exclude class="ipt_fsqm_timer_data" id="ipt_fsqm_timer_data_<?php echo $this->form_id; ?>" value="<?php echo esc_attr( json_encode( (object) $timer ) ); ?>" />
	<input type="hidden" data-sayt-exclude class="ipt_fsqm_epoc_data" id="ipt_fsqm_epoc_data_<?php echo $this->form_id; ?>" value="<?php echo esc_attr( json_encode( $epoc_data ) ); ?>" />

	<div style="display: none;" class="ipt_uif_hidden_init">
		<?php
		// Notice check
		if ( $this->settings['limitation']['submission_info'] == true && is_user_logged_in() && $can_submit && ! $this->doing_admin && ! $user_update ) {
			$previous_submissions = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d AND user_id = %d", $this->form_id, $this->data->user_id ) );
			if ( $previous_submissions > 0 ) {
				echo '<div class="ipt-eform-width-restrain">';
				$this->ui->msg_error( $this->settings['limitation']['submission_msg'], true, __( 'Attention', 'ipt_fsqm' ), true, true );
				echo '</div>';
			}
		}

		// Total Submission Check
		if ( $this->settings['limitation']['total_limit'] == true && $can_submit && ! $this->doing_admin && ! $user_update ) {
			// Show message
			if ( '' != $this->settings['limitation']['total_msg'] ) {
				echo '<div class="ipt-eform-width-restrain">';
				$this->ui->msg_update( sprintf( $this->settings['limitation']['total_msg'], ( $this->settings['limitation']['total_limit'] - $total_submissions ), $total_submissions ), true, __( 'Attention', 'ipt_fsqm' ), true, true );
				echo '</div>';
			}
		}
		?>
		<?php if ( $sayt_save['auto_save'] && ! $this->doing_admin ) : ?>
		<div style="display: none;" class="ipt_fsqm_form_message_restore ui-widget ui-widget-content ui-corner-all ipt_uif_widget_box">
			<div class="ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
				<h3><a href="#" title="<?php _e( 'Hide', 'ipt_fsqm' ); ?>" class="ipt_fsqm_form_message_close"><?php $this->ui->print_icon_by_class( 'times', false ); ?></a><?php $this->ui->print_icon_by_class( 'checkmark-circle', false ); ?><?php echo $this->settings['save_progress']['restore_head']; ?></h3>
			</div>
			<div class="ui-widget-content ui-corner-all">
				<?php echo wpautop( $this->settings['save_progress']['restore_msg'] ); ?>
				<button style="margin-bottom: 0" class="ipt_uif_button ipt_fsqm_sayt_reset secondary-button"><?php echo $this->settings['save_progress']['restore_reset']; ?></button>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( $can_submit ) : ?>
		<form method="post" action="" class="ipt_uif_validate_form ipt_fsqm_main_form" id="ipt_fsqm_form_<?php echo $this->form_id ?>" autocomplete="<?php echo ( $this->settings['submission']['no_auto_complete'] ? 'off' : 'on' ); ?>">
			<?php
			// Add the price estimator
			if ( true == $this->settings['payment']['estimation']['enabled'] && ! $this->doing_admin ) {
				$this->print_payment_estimator();
			}
			?>
			<input type="hidden" data-sayt-exclude name="action" id="action" value="ipt_fsqm_save_form" />
			<input type="hidden" data-sayt-exclude name="ipt_fsqm_epoc" class="eform-epoc-value" value="<?php echo esc_attr( $this->data->epoc ); ?>" />
			<input type="hidden" class="ipt_fsqm_form_tab_pos" name="fsqm_form<?php echo $this->form_id; ?>_tab_pos" value="0" id="fsqm_form<?php echo $this->form_id; ?>_tab_pos" />
			<?php if ( $this->data_id !== null ) : ?>
			<input type="hidden" data-sayt-exclude name="data_id" value="<?php echo esc_attr( $this->data_id ); ?>" />
			<?php endif; ?>
			<?php printf( '<input type="hidden" id="ipt_fsqm_form_data_save" name="ipt_fsqm_form_data_save" value="%2$s" data-sayt-exclude />', $this->form_id, wp_create_nonce( 'ipt_fsqm_form_data_save_' . $this->form_id ) ); ?>
			<?php wp_referer_field( true ); ?>
			<?php //wp_nonce_field( 'ipt_fsqm_form_data_save_' . $this->form_id, 'ipt_fsqm_form_data_save' ); ?>
			<?php if ( $user_update && $this->data_id !== null ) : ?>
			<input type="hidden" data-sayt-exclude name="user_edit" value="1" />
			<?php printf( '<input type="hidden" id="ipt_fsqm_user_edit_nonce" name="ipt_fsqm_user_edit_nonce" value="%2$s" data-sayt-exclude />', $this->data_id, wp_create_nonce( 'ipt_fsqm_user_edit_' . $this->data_id ) ); ?>
			<?php // wp_nonce_field( 'ipt_fsqm_user_edit_' . $this->data_id, 'ipt_fsqm_user_edit_nonce', false, true ); ?>
			<?php endif; ?>
			<?php if ( ! $doing_admin && true == $this->settings['submission']['url_track'] ) : ?>
			<?php
			$url_track_val = isset( $_GET[$this->settings['submission']['url_track_key']] ) ? strip_tags( stripslashes( $_GET[$this->settings['submission']['url_track_key']] ) ) : $this->data->url_track;
			$this->ui->hidden_input( 'ipt_fsqm_form_' . $this->form_id . '[url_track]', $url_track_val, array(), true );
			?>
			<?php endif; ?>

			<?php do_action( 'ipt_fsqm_hook_form_before', $this ); ?>
		<?php endif; ?>
			<?php do_action( 'ipt_fsqm_hook_form_fullview_before', $this ); ?>
			<input type="hidden" data-sayt-exclude name="form_id" value="<?php echo esc_attr( $this->form_id ); ?>" />
			<?php //var_dump($this->data); ?>
			<?php if ( $doing_admin ) : ?>
			<?php do_action( 'ipt_fsqm_hook_form_doing_admin_before', $this ); ?>
			<div class="ipt_uif_mother_wrap ipt-eform-admin-edit-ui ui-widget-content ui-widget" style="margin-bottom: 20px">
			<?php $this->ui->column_head( '', 'full', false ); ?>
			<?php $this->ui->heading( __( 'Submission Administration', 'ipt_fsqm' ), 'h3', 'center', 'none' ); ?>
			<?php $this->ui->column_tail(); ?>
			<?php endif; ?>
			<?php if ( $doing_admin ) : ?>
			<?php if ( $this->settings['general']['comment_title'] != '' ) : ?>
			<div class="ipt_uif_column ipt_uif_column_full" style="margin-bottom: 20px">
				<?php $this->ui->heading( $this->settings['general']['comment_title'], 'h3', 'left', 0xe0a2 ); ?>
				<?php $this->ui->clear(); ?>
				<?php $this->ui->textarea( 'ipt_fsqm_form_' . $this->form_id . '[comment]', $this->data->comment, __( 'Enter remarks', 'ipt_fsqm' ) ); ?>
				<?php $this->ui->clear(); ?>
			</div>
			<?php endif; ?>

			<?php if ( $this->settings['submission']['url_track'] == true ) : ?>
			<div class="ipt_uif_column ipt_uif_column_full">
				<?php $this->ui->question_container(
					'ipt_fsqm_form_' . $this->form_id . '[url_track]',
					__( 'Change URL Track Code', 'ipt_fsqm' ),
					__( 'manually enter the value', 'ipt_fsqm' ),
					array(
						array( $this->ui, 'text' ),
						array( 'ipt_fsqm_form_' . $this->form_id . '[url_track]', $this->data->url_track, __( 'Disabled', 'ipt_fsqm' ) ),
					)
				); ?>
			</div>
			<?php endif; ?>

			<?php if ( $this->settings['user']['notification_email'] != '' ) : ?>
			<div class="ipt_uif_column ipt_uif_column_full">
				<?php $this->ui->heading( __( 'Notify the surveyee/contributor', 'ipt_fsqm' ), 'h3', 'left', 0xe1a4 ); ?>
				<?php $this->ui->clear(); ?>
				<?php $this->ui->checkbox( 'ipt_fsqm_form_' . $this->form_id . '[notify]', array(
					'label' => __( 'Email the surveyee/contributor about this update.', 'ipt_fsqm' ),
					'value' => '1',
				), true ); ?>
				<?php $this->ui->clear(); ?>
				<?php $this->ui->column_head(); ?>
				<?php $this->ui->question_container(
					'ipt_fsqm_form_' . $this->form_id . '[notify_sub]',
					__( 'Notification Subject', 'ipt_fsqm' ),
					__( 'subject of the email', 'ipt_fsqm' ),
					array(
						array( $this->ui, 'text' ),
						array( 'ipt_fsqm_form_' . $this->form_id . '[notify_sub]', '[' . get_bloginfo( 'name' ) . '] ' . __( 'Your submission has been reviewed', 'ipt_fsqm' ), __( 'Please enter a subject', 'ipt_fsqm' ) ),
					)
				); ?>
				<?php $this->ui->column_tail(); ?>
				<?php $this->ui->clear(); ?>
				<?php $this->ui->column_head(); ?>
				<?php $this->ui->question_container(
					'ipt_fsqm_form_' . $this->form_id . '[notify_msg]',
					__( 'Notification Message', 'ipt_fsqm' ),
					__( 'message body of the email', 'ipt_fsqm' ),
					array(
						array( $this->ui, 'textarea' ),
						array( 'ipt_fsqm_form_' . $this->form_id . '[notify_msg]', __( "Hi %NAME%,\n\nYour submission has been reviewed by our team. To see it, please follow the link below.\n\n%TRACK_LINK%\n\nWe have also attached a copy of your submission.\n\nRegards,\n\n" . get_bloginfo( 'name' ), 'ipt_fsqm' ), __( 'Please enter a message', 'ipt_fsqm' ) ),
					)
				); ?>
				<?php $this->ui->column_tail(); ?>
			</div>
			<?php endif; ?>

			<?php $this->ui->clear(); ?>
			<?php endif; ?>

			<?php if ( $doing_admin ) : ?>
			<?php $this->ui->clear(); ?>
			</div>
			<?php endif; ?>
			<div class="ipt-eform-width-restrain">

			<?php // Add stopwatch ?>
			<?php if ( ! $doing_admin && $can_submit && true == $this->settings['stopwatch']['enabled'] && ( ! $user_update || $this->settings['stopwatch']['add_on_edit'] == true ) ) : ?>
			<?php
				$stopwatch_timer = array(
					'time' => array(
						'Days' => array(
							'show' => $this->settings['stopwatch']['days'],
						),
						'Hours' => array(
							'show' => $this->settings['stopwatch']['hours'],
						),
						'Seconds' => array(
							'show' => $this->settings['stopwatch']['seconds'],
						),
					),
				);
				$stopwatch_timer_val = 0;
				if ( $user_update && $this->settings['stopwatch']['add_on_edit'] == true ) {
					$stopwatch_timer_val = $this->data->time;
				}
				$stopwatch_timer_val = $stopwatch_timer_val * -1;
				$stopwatch_no_elem = 0;
				if ( $this->settings['stopwatch']['days'] == false ) {
					$stopwatch_no_elem++;
				}
				if ( $this->settings['stopwatch']['hours'] == false ) {
					$stopwatch_no_elem++;
				}
				if ( $this->settings['stopwatch']['seconds'] == false ) {
					$stopwatch_no_elem++;
				}
				$stopwatch_css_classes = array( 'ipt_fsqm_form_stopwatch', 'ipt_fsqm_form_stopwatch_noelem_' . $stopwatch_no_elem );
				if ( $this->settings['stopwatch']['rotate'] == true ) {
					$stopwatch_css_classes[] = 'rotate';
				}
				if ( true == $this->settings['stopwatch']['hidden'] ) {
					$stopwatch_css_classes[] = 'stp-hidden';
				}
				// var_dump($stopwatch_timer);
				$this->ui->timer( $stopwatch_timer_val, 'timer', $stopwatch_css_classes, '', $stopwatch_timer );
				$stopwatch_exclude_sayt = false;
				if ( $user_update ) {
					$stopwatch_exclude_sayt = true;
				}
				$this->ui->hidden_input( 'ipt_fsqm_form_' . $this->form_id . '[time]', $stopwatch_timer_val, 'ipt_fsqm_form_stopwatch_val', $stopwatch_exclude_sayt );
			?>
			<?php endif; // End stopwatch ?>

			<?php // Add logo ?>
			<?php if ( '' != $this->settings['theme']['logo'] ) : ?>
			<div class="ipt_fsqm_form_logo">
				<img src="<?php echo esc_attr( $this->settings['theme']['logo'] ); ?>" alt="<?php echo esc_attr( $this->name ); ?>">
			</div>
			<?php endif; // end logo ?>

			<div class="ipt-eform-content <?php echo ( 0 == $this->type && true != $this->settings['type_specific']['normal']['wrapper'] ) ? 'ipt-eform-no-wrap' : '' ?>">
			<?php if ( $this->type != 0 ) : ?>
			<?php if ( $this->type == 2 && true == $this->settings['type_specific']['pagination']['show_progress_bar'] && false == $this->settings['type_specific']['pagination']['progress_bar_bottom'] ) : ?>
			<?php $this->ui->progressbar( 'ipt_fsqm_form_' . $this->form_id . '_progressbar', '0', array( 'ipt_fsqm_main_pb', 'eform-mainpb-top' ), $this->settings['type_specific']['pagination']['decimal_point'] ); ?>
			<?php endif; ?>
			<?php
				$eform_main_tab_classes = array( 'ipt_fsqm_main_tab' );
				if ( $this->type == 2 && true == $this->settings['type_specific']['pagination']['show_progress_bar'] ) {
					$classes[] = 'eform-mtab-has-pb';
					if ( false == $this->settings['type_specific']['pagination']['progress_bar_bottom'] ) {
						$classes[] = 'eform-mtab-pb-top';
					} else {
						$classes[] = 'eform-mtab-pb-bottom';
					}
				}
			?>
			<?php $this->ui->tabs( $tabs, array( 'settings' => json_encode( (object) array(
						'can_previous' => $this->settings['type_specific']['tab']['can_previous'],
						'show_progress_bar' => $this->settings['type_specific']['pagination']['show_progress_bar'],
						'progress_bar_bottom' => $this->settings['type_specific']['pagination']['progress_bar_bottom'],
						// @see issue #6
						// @link https://iptlabz.com/ipanelthemes/wp-fsqm-pro/issues/6
						'block_previous' => $this->settings['type_specific']['tab']['block_previous'],
						'any_tab' => $this->settings['type_specific']['tab']['any_tab'],
						'type' => $this->type,
						'scroll' => $this->settings['type_specific']['tab']['scroll'],
						'decimal_point' => (int) $this->settings['type_specific']['pagination']['decimal_point'],
						'auto_progress' => $this->settings['type_specific']['tab']['auto_progress'],
						'auto_progress_delay' => $this->settings['type_specific']['tab']['auto_progress_delay'],
						'auto_submit' => $this->settings['type_specific']['tab']['auto_submit'],
						'hidden_buttons' => $this->settings['buttons']['hide'],
						'scroll_on_error' => $this->settings['type_specific']['tab']['scroll_on_error'],
				  ) ) ), false, $eform_main_tab_classes ); ?>
			<?php else : ?>

			<?php foreach ( $this->layout as $l_key => $layout ) : ?>
			<?php $this->populate_layout( $l_key, $layout ); ?>
			<?php endforeach; ?>

			<?php endif; ?>
			<?php if ( !$doing_admin && ( '0' != $this->settings['general']['terms_page'] || !empty( $this->settings['general']['terms_page'] ) ) ) : $link = get_permalink( $this->settings['general']['terms_page'] ); ?>
			<?php $terms_checked = $this->data_id != null ? true : false; ?>
			<?php $terms_ip_addr = $this->data_id != null ? $this->data->ip : $_SERVER['REMOTE_ADDR']; ?>
			<div class="ipt_fsqm_terms_wrap ui-widget-content">
				<?php $this->ui->column_head(); ?>
				<?php $this->ui->checkbox( 'ipt_fsqm_terms_' . $this->form_id, array(
				'label' => sprintf( $this->settings['general']['terms_phrase'], $link, $terms_ip_addr ),
				'value' => '1',
			), $terms_checked, array( 'required' => true ) ); ?>
				<?php $this->ui->column_tail(); ?>
			</div>
			<?php $this->ui->clear(); ?>
			<?php endif; ?>

			<?php if ( $can_submit ) : ?>
			<?php $this->submit_buttons(); ?>
			<?php endif; ?>

			<?php if ( $this->type == 2 && true == $this->settings['type_specific']['pagination']['show_progress_bar'] && true == $this->settings['type_specific']['pagination']['progress_bar_bottom'] ) : ?>
				<?php $this->ui->clear(); ?>
				<?php $this->ui->progressbar( 'ipt_fsqm_form_' . $this->form_id . '_progressbar', '0', array( 'ipt_fsqm_main_pb', 'eform-mainpb-bottom' ), $this->settings['type_specific']['pagination']['decimal_point'] ); ?>
				<?php $this->ui->clear(); ?>
			<?php endif; ?>

			<?php $this->ui->clear(); ?>
		</div> <!-- end .ipt-eform-content -->
		</div> <!-- end .ipt-eform-width-restrain -->
		<?php if ( $can_submit ) : ?>
		<?php do_action( 'ipt_fsqm_hook_form_after', $this ); ?>
		</form>
		<?php endif; ?>
	</div>
	<?php if ( $can_submit ) : ?>
	<div style="display: none;" class="ipt_fsqm_form_message_success ui-widget ui-widget-content ui-corner-all ipt_uif_widget_box">
		<div class="ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<h3><?php $this->ui->print_icon_by_class( 'checkmark-circle', false ); ?><?php echo $doing_admin ? __( 'Updation was successful', 'ipt_fsqm' ) : $this->settings['submission']['success_title']; ?></h3>
		</div>
		<div class="ui-widget-content ui-corner-all ipt_fsqm_success_wrap">
			<?php if ( $doing_admin ) : ?>
			<p><?php _e( 'The update process was successful.', 'ipt_fsqm' ); ?></p>
			<p><?php _e( 'If you have a valid user notification email and if you have checked the "Email the surveyee/contributor about this update" button, then the user has been notified with a trackback link.', 'ipt_fsqm' ) ?></p>
			<?php else : ?>
			<?php echo wpautop( $this->settings['submission']['success_message'] ); ?>
			<?php endif; ?>
		</div>
		<?php if ( false === $user_update && false === $doing_admin && true === $this->settings['submission']['reset_on_submit'] ) : ?>
		<div class="ui-widget-content ui-corner-all ipt_fsqm_form_reset ipt_fsqm_sm_meta">
			<p class="ipt_fsqm_form_reset_p ipt_fsqm_sm_meta_p"><?php echo str_replace( '%time%', '<span class="ipt_fsqm_form_reset_cu"></span>', $this->settings['submission']['reset_msg'] ); ?></p>
		</div>
		<?php endif; ?>
	</div>
	<div style="display: none;" class="ipt_fsqm_form_message_error ui-widget ui-widget-content ui-corner-all ipt_uif_widget_box">
		<div class="ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<div><?php $this->ui->print_icon_by_class( 'laptop', false ); ?><?php _e( 'Server Side Error', 'ipt_fsqm' ); ?></div>
		</div>
		<div class="ui-widget-content ui-corner-all">
			<p><?php _e( 'We faced problems while connecting to the server or receiving data from the server. Please wait for a few seconds and try again.', 'ipt_fsqm' ); ?></p>
			<p><?php _e( 'If the problem persists, then check your internet connectivity. If all other sites open fine, then please contact the administrator of this website with the following information.', 'ipt_fsqm' ); ?></p>
			<p class="jqXHR">
				<strong><?php _e( 'TextStatus: ', 'ipt_fsqm' ) ?></strong><span class="textStatus"><?php _e( 'undefined', 'ipt_fsqm' ) ?></span><br />
				<strong><?php _e( 'HTTP Error: ', 'ipt_fsqm' ) ?></strong><span class="errorThrown"><?php _e( 'undefined', 'ipt_fsqm' ) ?></span>
			</p>
		</div>
	</div>
	<div style="display: none" class="ipt_uif_widget_box ipt_fsqm_form_message_process">
		<div class="ui-widget ui-widget-header ui-corner-all">
			<?php $this->ui->ajax_loader( false, '', array(), true, $this->settings['submission']['process_title'] ); ?>
		</div>
	</div>
	<div style="display: none;" class="ipt_fsqm_form_validation_error ui-widget ui-widget-content ui-corner-all ipt_uif_widget_box">
		<div class="ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<div><?php $this->ui->print_icon_by_class( 'warning2', false ); ?> <span class="fsqm_ve_text"><?php _e( 'Error', 'ipt_fsqm' ); ?></span></div>
		</div>
		<div class="ui-widget-content ui-corner-all">
			<p class="fsqm_ve_msg"><?php _e( 'Some error has occured.', 'ipt_fsqm' ); ?></p>
		</div>
	</div>
	<?php endif; ?>

	<?php if ( isset( $timer->type ) && $timer->type != 'none' ) : ?>
	<div class="ipt_fsqm_timer fixed"><div class="ipt_fsqm_timer_inner"></div></div>
	<div class="ipt_fsqm_timer_spacer"></div>
	<?php endif; ?>

	<?php
	if ( true == $this->settings['general']['pipe'] ) {
		$reactive_helper->remove_react_filters();
		$matches = $reactive_helper->get_matches();
		echo '<input type="hidden" class="eform-react-helper-matches" data-matches="' . esc_attr( json_encode( (object) $matches ) ) . '" value="1" />';
	}
	?>
<?php if ( $print_container ) : ?>
</div>
<?php endif; ?>
		<?php
	}

	public function populate_layout( $layout_key, $layout, $make_wrapper = true ) {
		$layout_title = $this->ui->get_page_label_html( $layout['title'] );
?>
<?php if ( $make_wrapper ) : ?>
	<?php
	$heading_align = 'left';
	if ( true == $this->settings['type_specific']['normal']['center_heading'] ) {
		$heading_align = 'center';
	}
	?>
<div id="<?php echo 'ipt_fsqm_form_' . $this->form_id . '_layout_' . $layout_key; ?>_inner" class="ipt-eform-layout-wrapper">
	<?php if ( $this->type != 1 ) : ?>
	<?php $this->ui->column_head( '', 'full', false, array( 'ipt_fsqm_main_heading_column' ) ); ?>
	<?php if ( trim( $layout_title ) != '' ) : ?>
	<?php $this->ui->heading( $layout_title . '<span class="subtitle">' . $layout['subtitle'] . '</span>', 'h3', $heading_align, $layout['icon'], false, true, array( 'ipt_fsqm_main_heading' ) ); ?>
	<?php endif; ?>
	<?php $this->ui->column_tail(); ?>
	<?php endif; ?>
	<?php if ( isset( $layout['description'] ) && $layout['description'] != '' ) : ?>
	<?php $this->ui->column_head( '', 'full', true ); ?>
	<?php echo apply_filters( 'ipt_uif_richtext', $layout['description'] ); ?>
	<?php $this->ui->column_tail(); ?>
	<?php endif; ?>
<?php endif; ?>
	<?php foreach ( (array) $layout['elements'] as $layout_element ) : ?>
	<?php $this->tamper_protection( $layout_element ); ?>
	<?php $element = $layout_element['type']; $key = $layout_element['key']; $element_data = $this->get_element_from_layout( $layout_element ); $submission_data = $this->get_submission_from_data( $layout_element ); ?>
	<?php $this->build_element_html( $element, $key, $element_data, $submission_data, 'ipt_fsqm_form_' . $this->form_id ); ?>
	<?php endforeach; ?>
<?php if ( $make_wrapper ) : ?>
</div>
<?php endif; ?>
		<?php
	}

	public function populate_conditional_logic() {
		$logics = array();
		$indexes = array();

		// Loop through containers
		foreach ( $this->layout as $l_key => $layout ) {
			if ( isset( $layout['conditional'] ) && is_array( $layout['conditional'] ) ) {
				if ( $layout['conditional']['active'] == false ) {
					continue;
				}
				$this->process_logic( $layout, $l_key, $logics );
				$this->process_index( $layout, $l_key, $indexes );
			}
		}

		// Loop through design elements
		foreach ( $this->design as $d_key => $design ) {
			if ( isset( $design['conditional'] ) && is_array( $design['conditional'] ) ) {
				if ( $design['conditional']['active'] == false ) {
					continue;
				}
				$this->process_logic( $design, $d_key, $logics );
				$this->process_index( $design, $d_key, $indexes );
			}
		}

		// Loop through freetype elements
		foreach ( $this->freetype as $f_key => $freetype ) {
			if ( isset( $freetype['conditional'] ) && is_array( $freetype['conditional'] ) ) {
				if ( $freetype['conditional']['active'] == false ) {
					continue;
				}
				$this->process_logic( $freetype, $f_key, $logics );
				$this->process_index( $freetype, $f_key, $indexes );
			}
		}

		// Loop through mcq elements
		foreach ( $this->mcq as $m_key => $mcq ) {
			if ( isset( $mcq['conditional'] ) && is_array( $mcq['conditional'] ) ) {
				if ( $mcq['conditional']['active'] == false ) {
					continue;
				}
				$this->process_logic( $mcq, $m_key, $logics );
				$this->process_index( $mcq, $m_key, $indexes );
			}
		}

		// Loop through pinfo
		foreach ( $this->pinfo as $p_key => $pinfo ) {
			if ( isset( $pinfo['conditional'] ) && is_array( $pinfo['conditional'] ) ) {
				if ( $pinfo['conditional']['active'] == false ) {
					continue;
				}
				$this->process_logic( $pinfo, $p_key, $logics );
				$this->process_index( $pinfo, $p_key, $indexes );
			}
		}

		// Submit Button Logic
		if ( isset( $this->settings['buttons']['conditional'] ) && $this->settings['buttons']['conditional']['active'] == true ) {
			$button_id = 'ipt_fsqm_form_' . $this->form_id . '_button_submit';
			$button_conditional = (object) $this->settings['buttons']['conditional'];
			$button_conditional_logic = array();
			foreach ( (array) $this->settings['buttons']['conditional']['logic'] as $blogic ) {
				$button_conditional_logic[] = (object) $blogic;
				$bindex_key = 'ipt_fsqm_form_' . $this->form_id . '_' . $blogic['m_type'] . '_' . $blogic['key'];
				if ( ! isset( $indexes[$bindex_key] ) ) {
					$indexes[$bindex_key] = array();
				}
				$indexes[$bindex_key][] = $button_id;
			}
			$button_conditional->logic = (object) $button_conditional_logic;
			$logics[$button_id] = $button_conditional;
		}

		if ( isset( $this->settings['buttons']['conditional_next'] ) && $this->settings['buttons']['conditional_next']['active'] == true ) {
			$button_id = 'ipt_fsqm_form_' . $this->form_id . '_button_next';
			$button_conditional = (object) $this->settings['buttons']['conditional_next'];
			$button_conditional_logic = array();
			foreach ( (array) $this->settings['buttons']['conditional_next']['logic'] as $blogic ) {
				$button_conditional_logic[] = (object) $blogic;
				$bindex_key = 'ipt_fsqm_form_' . $this->form_id . '_' . $blogic['m_type'] . '_' . $blogic['key'];
				if ( ! isset( $indexes[$bindex_key] ) ) {
					$indexes[$bindex_key] = array();
				}
				$indexes[$bindex_key][] = $button_id;
			}
			$button_conditional->logic = (object) $button_conditional_logic;
			$logics[$button_id] = $button_conditional;
		}

		return array(
			'logics' => $logics,
			'indexes' => $indexes,
			'base' => $this->form_id,
		);
	}

	protected function process_logic( $element, $key, &$logics ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element['m_type'] . '_' . $key;
		// Set the control li for layout elements
		if ( $element['m_type'] == 'layout' ) {
			if ( $this->type == '0' ) {
				$id = 'ipt_fsqm_form_' . $this->form_id . '_layout_' . $key . '_inner';
			} else {
				$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element['type'] . '_' . $key . '_control_li';
			}
		}
		if ( ! isset( $logics[$id] ) ) {
			// Make the conditional logic objects, otherwise will lose order
			$conditional = $element['conditional'];
			$conditional_logic = array();
			foreach ( $conditional['logic'] as $logic ) {
				$conditional_logic[] = $logic;
			}
			$conditional = (object) $conditional;
			$conditional->logic = (object) $conditional_logic;

			$logics[$id] = $conditional;
			$logics[$id]->type = $element['type'];
			$logics[$id]->m_type = $element['m_type'];
		}
	}

	protected function process_index( $element, $key, &$indexes ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element['m_type'] . '_' . $key;
		// Set the control li for layout elements
		if ( $element['m_type'] == 'layout' ) {
			if ( $this->type == '0' ) {
				$id = 'ipt_fsqm_form_' . $this->form_id . '_layout_' . $key . '_inner';
			} else {
				$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element['type'] . '_' . $key . '_control_li';
			}
		}
		if ( isset( $element['conditional']['logic'] ) ) {
			foreach ( (array) $element['conditional']['logic'] as $logic ) {
				$index_key = 'ipt_fsqm_form_' . $this->form_id . '_' . $logic['m_type'] . '_' . $logic['key'];
				if ( ! isset( $indexes[$index_key] ) ) {
					$indexes[$index_key] = array();
				}
				$indexes[$index_key][] = $id;
			}
		}
	}

	public function populate_timer() {
		$timer = array(
			'type' => 'none',
		);
		// Only if not admin functions
		// And submission is enabled
		if ( ! $this->doing_admin && $this->can_submit ) {
			if ( $this->settings['timer']['time_limit_type'] != 'none' ) {
				switch ( $this->settings['timer']['time_limit_type'] ) {
					case 'overall' :
						$timer = array(
							'type' => 'overall',
							'time' => $this->settings['timer']['overall_limit'],
						);
						break;

					case 'page_specific' :
						$timer = array(
							'type' => 'page_specific',
							'time' => array(),
						);
						foreach ( $this->layout as $l_key => $layout ) {
							$timer['time'][$l_key] = $layout['timer'];
						}
						$timer['time'] = (object) $timer['time'];
						break;

					default :
					case 'none' :
						$timer['type'] = 'none';
						break;
				}
			}
		}

		// Enqueue if needed
		if ( 'none' != $timer['type'] ) {
			wp_enqueue_script( 'jquery-time-circles', IPT_FSQM_Loader::$bower_builds . 'timecircles/js/TimeCircles.min.js', array( 'jquery' ), IPT_FSQM_Loader::$version, true );
			wp_enqueue_style( 'ipt-plugin-uif-time-circles', IPT_FSQM_Loader::$bower_builds . 'timecircles/css/TimeCircles.min.css', array(), IPT_FSQM_Loader::$version );
		}

		return (object) $timer;
	}

	public function submit_buttons() {
		$buttons = array();
		if ( count( $this->layout ) > 1 && $this->type != '0' ) {
			$buttons[0] = array(
				'text' => $this->settings['buttons']['prev'],
				'name' => 'ipt_fsqm_form_' . $this->form_id . '_button_prev',
				'size' => 'normal',
				'style' => 'primary',
				'state' => 'normal',
				'classes' => array( 'ipt_fsqm_form_button_prev' ),
				'type' => 'button',
			);
			$buttons[2] = array(
				'text' => $this->settings['buttons']['next'],
				'name' => 'ipt_fsqm_form_' . $this->form_id . '_button_next',
				'size' => 'normal',
				'style' => 'primary',
				'state' => 'normal',
				'classes' => array( 'ipt_fsqm_form_button_next' ),
				'type' => 'button',
			);
		}
		$buttons[1] = array(
			'text' => ( $this->user_update ? $this->settings['buttons']['supdate'] : $this->settings['buttons']['submit'] ),
			'name' => 'ipt_fsqm_form_' . $this->form_id . '_button_submit',
			'size' => 'normal',
			'style' => 'primary',
			'state' => 'normal',
			'classes' => array( 'ipt_fsqm_form_button_submit' ),
			'type' => 'submit',
		);
		// Reset
		if ( $this->settings['buttons']['reset'] != '' ) {
			$buttons[] = array(
				'text' => '<i class="ipticm ipt-icomoon-refresh"></i>',
				'name' => 'ipt_fsqm_form_' . $this->form_id . '_button_reset',
				'size' => 'normal',
				'style' => 'primary',
				'state' => 'normal',
				'classes' => array( 'ipt_fsqm_form_button_reset ipt_uif_tooltip' ),
				'type' => 'reset',
				'data' => array(),
				'atts' => array(
					'title' => $this->settings['buttons']['reset'],
				),
			);
		}
		// Interval save
		if ( ! $this->user_update && ! $this->doing_admin && true == $this->settings['save_progress']['interval_save'] && '' != $this->settings['save_progress']['interval_title'] ) {
			$buttons[] = array(
				'text' => '<i class="ipticm ipt-icomoon-save"></i>',
				'name' => 'ipt_fsqm_form_' . $this->form_id . '_button_interval_save',
				'size' => 'normal',
				'style' => 'primary',
				'state' => 'normal',
				'classes' => array( 'ipt_fsqm_form_button_interval_save ipt_uif_tooltip' ),
				'type' => 'button',
				'data' => array(
					'otitle' => $this->settings['save_progress']['interval_title'],
					'stitle' => $this->settings['save_progress']['interval_saved_title'],
				),
				'atts' => array(
					'title' => $this->settings['save_progress']['interval_title'],
				),
			);
		}
		ksort( $buttons );
		$buttons = apply_filters( 'ipt_fsqm_form_progress_buttons', $buttons, $this );
		// Add a filter to the button container
		$container_classes = array( 'ipt_fsqm_form_button_container' );
		$container_classes[] = 'ipt_fsqm_form_button_container--' . esc_attr( $this->settings['theme']['material']['button_style'] );
		if ( true === $this->settings['theme']['material']['alternate_pb'] ) {
			$container_classes[] = 'eform-material-alternate-pb';
		}
		if (
			true === $this->settings['theme']['material']['button_rounded']
			&& 'classic' !== $this->settings['theme']['material']['button_style']
		) {
			$container_classes[] = 'eform-material-rounded-pb';
		}

		// Hide container buttons if needed
		if ( $this->settings['buttons']['hidden'] ) {
			$container_classes[] = 'eform-progress-button-hidden';
		}
		$container_classes = apply_filters( 'ipt_fsqm_form_progress_container_classes', $container_classes );
		$this->ui->buttons( $buttons, 'ipt_fsqm_form_' . $this->form_id . '_button_container', $container_classes );
	}

	/*==========================================================================
	 * DEFAULT ELEMENTS - OVERRIDE
	 *========================================================================*/
	public function build_heading( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', false, 'ipt_fsqm_container_heading' );
?>
<?php $this->ui->heading( $element_data['title'], $element_data['settings']['type'], $element_data['settings']['align'], $element_data['settings']['icon'], $element_data['settings']['show_top'] ); ?>
		<?php
		$this->ui->column_tail();
	}

	public function build_richtext( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', 'ipt_fsqm_container_richtext' );
		if ( false == $element_data['settings']['styled'] ) {
			?>
			<?php $this->ui->heading( $element_data['title'], 'h2', 'left', $element_data['settings']['icon'] ); ?>
			<div class="ipt_uif_richtext">
				<?php echo apply_filters( 'ipt_uif_richtext', $element_data['description'] ); ?>
				<?php $this->ui->clear(); ?>
			</div>
			<?php
		} else {
			$params = array( $element_data['description'] );
			$this->ui->container( array( array( $this->ui, 'richtext' ), $params ), $element_data['title'], $element_data['settings']['icon'], false, true, '', false, '', array( 'eform-styled-container', 'ipt_uif_richtext' ) );
		}

		$this->ui->column_tail();
	}

	public function build_embed( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_embed' );
		$classes = array( 'ipt_fsqm_embed' );
		if ( true == $element_data['settings']['full_size'] ) {
			$classes[] = 'full-size';
		}
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<?php echo $element_data['description']; ?>
</div>
		<?php
		$this->ui->column_tail();
	}

	public function build_collapsible( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_collapsible', $element_data['tooltip'] );
		$params = array( $key, $element_data, false );
		$this->ui->container( array( array( $this, 'populate_layout' ), $params ), $element_data['title'], $element_data['settings']['icon'], true, $element_data['settings']['expanded'], '' );
		$this->ui->column_tail();
	}

	public function build_container( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_container', $element_data['tooltip'] );
		$params = array( $key, $element_data, false );
		$this->ui->container( array( array( $this, 'populate_layout' ), $params ), $element_data['title'], $element_data['settings']['icon'], false, true, '', false, '', 'eform-styled-container' );
		$this->ui->column_tail();
	}

	public function build_blank_container( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', false, 'ipt_fsqm_container_blank_container', $element_data['tooltip'] );
		$params = array( $key, $element_data, false );
		$this->ui->div( 'ipt_uif_blank_container', array( array( $this, 'populate_layout' ), $params ) );
		$this->ui->column_tail();
	}

	public function build_iconbox( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_iconbox' );
		$this->ui->iconmenu( $element_data['settings']['elements'], $element_data['settings']['align'], $element_data['settings']['open'], $element_data['settings']['popup'], [
			'size' => $element_data['settings']['size'],
			'style' => $element_data['settings']['style'],
			'rounded' => $element_data['settings']['rounded'],
			'themed' => $element_data['settings']['themed'],
		] );
		$this->ui->column_tail();
	}

	public function build_col_half( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'half', false, 'ipt_fsqm_container_col_half', $element_data['tooltip'] );
		$this->populate_layout( $key, $element_data, false );
		$this->ui->column_tail();
	}

	public function build_col_third( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'third', false, 'ipt_fsqm_container_col_third', $element_data['tooltip'] );
		$this->populate_layout( $key, $element_data, false );
		$this->ui->column_tail();
	}

	public function build_col_two_third( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'two_third', false, 'ipt_fsqm_container_col_two_third', $element_data['tooltip'] );
		$this->populate_layout( $key, $element_data, false );
		$this->ui->column_tail();
	}

	public function build_col_forth( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'forth', false, 'ipt_fsqm_container_col_forth', $element_data['tooltip'] );
		$this->populate_layout( $key, $element_data, false );
		$this->ui->column_tail();
	}

	public function build_col_three_forth( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'three_forth', false, 'ipt_fsqm_container_col_three_forth', $element_data['tooltip'] );
		$this->populate_layout( $key, $element_data, false );
		$this->ui->column_tail();
	}

	public function build_clear( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->ui->clear();
	}

	public function build_horizontal_line( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', false, 'ipt_fsqm_container_horizontal_line' );
		$this->ui->divider( '', 'div', 'center', 'none', $element_data['settings']['show_top'] );
		$this->ui->column_tail();
	}

	public function build_divider( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', false, 'ipt_fsqm_container_divider' );
		$this->ui->divider( $element_data['title'], 'div', $element_data['settings']['align'], $element_data['settings']['icon'], $element_data['settings']['show_top'] );
		$this->ui->column_tail();
	}

	public function build_button( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_button' );
		$this->ui->button( $element_data['title'], 'ipt_fsqm_jump_button_' . $key, $element_data['settings']['size'], 'secondary', 'normal', 'ipt_fsqm_jump_button', 'button', true, array(
			'pos' => $element_data['settings']['container'],
		), array(), '', $element_data['settings']['icon'], 'before' );
		$this->ui->column_tail();
	}

	public function build_imageslider( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_imageslider' );
		$data = array(
			'autoslide' => $element_data['settings']['autoslide'],
			'duration' => $element_data['settings']['duration'],
			'transition' => $element_data['settings']['transition'],
			'animation' => $element_data['settings']['animation'],
		);
		$images = $element_data['settings']['images'];
		$id = 'ipt_fsqm_slider_' . $this->form_id . $key;
		if ( empty( $images ) ) {
			$this->ui->msg_error( __( 'Please insert some images to show in the slider.', 'ipt_fsqm' ), true, __( 'No images', 'ipt_fsqm' ) );
		} else {
			$this->ui->imageslider( $id, $images, $data );
		}
		$this->ui->column_tail();
	}

	public function build_captcha( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		// No need to show if just viewing
		if ( false == $this->can_submit ) {
			return;
		}
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_captcha' );
		$num1 = rand( 1, 10 );
		$num2 = rand( 1, 10 );
		$sum = $num1 + $num2;
		$hashed = $this->encrypt( $sum );
?>
<input type="hidden" name="<?php echo esc_attr( $name_prefix ); ?>[hash]" value="<?php echo esc_attr( $hashed ); ?>" data-sayt-exclude />
		<?php
		$title = sprintf( __( '%d plus %d equals?', 'ipt_fsqm' ), $num1, $num2 );
		$subtitle = __( 'Prove you are a human', 'ipt_fsqm' );
		$data = array(
			'sum' => $sum,
		);
		$validation = array(
			'required' => true,
			'funccall' => 'ipt_uif_front_captcha'
		);
		$params = array( $name_prefix . '[value]', '', __( 'Write here', 'ipt_fsqm' ), 'calculate', 'normal', array(), $validation, $data, array( 'data-sayt-exclude' => 'true' ) );
		$this->ui->question_container( $name_prefix . '[value]', $title, $subtitle, array( array( $this->ui, 'text' ), $params ), true );
		$this->ui->column_tail();
	}

	public function build_recaptcha( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		// No need to show if just viewing
		if ( false == $this->can_submit ) {
			return;
		}
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_recaptcha' );
		if ( '' === $element_data['settings']['secret_key'] || '' === $element_data['settings']['site_key'] ) {
			$this->ui->msg_error( sprintf( __( 'Please add Google Recaptcha <a href="%1$s">site and secret keys</a> to make this element work', 'ipt_fsqm' ), 'https://iptms.co/6' ), true, __( 'reCaptcha Keys Needed', 'ipt_fsqm' ) );
			$this->ui->column_tail();
			return;
		}
		if ( $this->live_view ) {
			$this->ui->msg_okay( __( 'reCaptcha is configured and will show up in the real form.', 'ipt_fsqm' ) );
			$this->ui->column_tail();
			return;
		}
		$data_attr = array(
			'sitekey' => $element_data['settings']['site_key'],
			'theme' => $element_data['settings']['theme'],
			'type' => $element_data['settings']['type'],
			'size' => $element_data['settings']['size'],
		);
		wp_enqueue_script( 'eform-recaptcha-' . $element_data['settings']['hl'], 'https://www.google.com/recaptcha/api.js?onload=eFormreCaptchaLoad&render=explicit&hl=' . $element_data['settings']['hl'], array( 'ipt-fsqm-front-js' ), '2.0.0', true );
		?>
<input type="hidden" name="<?php echo $name_prefix; ?>[recaptcha]" id="<?php echo $id . '_recaptcha' ?>" value="" />
<div class="eform-g-recaptcha"<?php echo $this->ui->convert_data_attributes( $data_attr ); ?>></div>
		<?php
		$this->ui->column_tail();
	}

	public function build_radio( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_radio', $element_data['tooltip'] );
		$this->make_mcqs( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure );
		$this->ui->column_tail();
	}

	public function build_checkbox( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_checkbox', $element_data['tooltip'] );
		$this->make_mcqs( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure );
		$this->ui->column_tail();
	}

	public function build_select( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_select', $element_data['tooltip'] );
		$this->make_mcqs( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure );
		$this->ui->column_tail();
	}

	public function build_pricing_table( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'eform-container-pricing-table', $element_data['tooltip'] );

		// Check for prefils and defaults
		if ( null == $this->data_id && empty( $submission_data['options'] ) ) {
			$default = array();
			// Loop through all options and get the highligted as the defaults
			foreach ( $element_data['settings']['options'] as $o_key => $option ) {
				if ( isset( $option['selected'] ) && true == $option['selected'] ) {
					$default[] = "$o_key";
				}
			}

			// Check for URL & User Meta
			// value key
			$parameter = $element_data['settings']['parameter'];
			$elm_options = array();
			// Make the options
			foreach ( $element_data['settings']['options'] as $o_key => $option ) {
				$elm_options[ "$o_key" ] = $option['label'];
			}
			// Check for the type of data we need to process
			switch ( $element_data['settings']['type'] ) {
				case 'url':
					$default = IPT_FSQM_Form_Elements_Static::get_request_parameter_for_mcq( $parameter, $default, $elm_options );
					break;
				case 'meta':
					$default = IPT_FSQM_Form_Elements_Static::get_user_metavalues_for_mcq( $parameter, $default, $elm_options );
					break;
			}
			// Set the value
			$submission_data['options'] = $default;
		}

		$param = array( $name_prefix . '[options][]', $element_data['settings']['options'], $submission_data['options'], $element_data['settings']['currency'], $element_data['validation'], $element_data['settings']['style'] );
		$this->ui->question_container( $name_prefix, $element_data['title'], '', array( array( $this->ui, 'pricing_table' ), $param ), $element_data['validation']['required'], false, true, '', array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );

		$this->ui->column_tail();
	}

	public function build_thumbselect( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_thumbselect', $element_data['tooltip'] );
		$items = array();
		foreach ( (array) $element_data['settings']['options'] as $o_key => $option ) {
			$items[] = array(
				'label' => $option['label'],
				'value' => (string) $o_key,
				'image' => $option['image'],
				'data' => array(
					'num' => $option['num'],
					'label' => $option['label'],
				),
			);
		}

		// Check for defaults
		if ( null == $this->data_id && empty( $submission_data['options'] ) ) {
			$default = array();
			// Loop through all options
			foreach ( $element_data['settings']['options'] as $o_key => $option ) {
				if ( isset( $option['default'] ) && true == $option['default'] ) {
					$default[] = "$o_key";
				}
			}

			// Check for URL & User Meta
			// value key
			$parameter = $element_data['settings']['parameter'];
			$elm_options = array();
			// Make the options
			foreach ( $element_data['settings']['options'] as $o_key => $option ) {
				$elm_options[ "$o_key" ] = $option['label'];
			}
			// Check for the type of data we need to process
			switch ( $element_data['settings']['type'] ) {
				case 'url':
					$default = IPT_FSQM_Form_Elements_Static::get_request_parameter_for_mcq( $parameter, $default, $elm_options );
					break;
				case 'meta':
					$default = IPT_FSQM_Form_Elements_Static::get_user_metavalues_for_mcq( $parameter, $default, $elm_options );
					break;
			}
			// Set the value
			$submission_data['options'] = $default;
		}

		$param = array( $name_prefix . '[options][]', $items, $submission_data['options'], $element_data['settings']['multiple'], $element_data['validation'], $element_data['settings']['width'], $element_data['settings']['height'], $element_data['settings']['show_label'], false, false, $element_data['settings']['icon'], ! $element_data['settings']['tooltip'], $element_data['settings']['appearance'] );
		$this->ui->question_container( $name_prefix, $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'thumbnail_select' ), $param ), $element_data['validation']['required'], false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_slider( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_slider', $element_data['tooltip'] );
		// In case of new submission, override the default value
		if ( null === $this->data_id && '' != $element_data['settings']['dmin'] ) {
			$submission_data['value'] = $element_data['settings']['dmin'];
		}
		$params = array( $name_prefix . '[value]', $submission_data['value'], $element_data['settings']['show_count'], $element_data['settings']['min'], $element_data['settings']['max'], $element_data['settings']['step'], $element_data['settings']['prefix'], $element_data['settings']['suffix'], $element_data['settings']['label'], $element_data['settings']['nomin'], $element_data['settings']['floats'], $element_data['settings']['vertical_ui'], $element_data['settings']['height'] );
		$this->ui->question_container( $name_prefix . '[value]', $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'slider' ), $params ), true, false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_range( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_range', $element_data['tooltip'] );
		// In case of new submission, override the default value
		if ( null === $this->data_id && '' != $element_data['settings']['dmin'] ) {
			$submission_data['values']['min'] = $element_data['settings']['dmin'];
		}
		if ( null === $this->data_id && '' != $element_data['settings']['dmax'] ) {
			$submission_data['values']['max'] = $element_data['settings']['dmax'];
		}
		$params = array( $name_prefix . '[values]', $submission_data['values'], $element_data['settings']['show_count'], $element_data['settings']['min'], $element_data['settings']['max'], $element_data['settings']['step'], $element_data['settings']['prefix'], $element_data['settings']['suffix'], $element_data['settings']['label'], $element_data['settings']['nomin'], $element_data['settings']['floats'], $element_data['settings']['vertical_ui'], $element_data['settings']['height'] );
		$this->ui->question_container( $name_prefix . '[values]', $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'slider_range' ), $params ), true, false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_spinners( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_spinners', $element_data['tooltip'] );
		$spinners = array();
		foreach ( $element_data['settings']['options'] as $sp_key => $sp_option ) {
			// backward compatibility with v-2.5.0
			if ( ! is_array( $sp_option ) ) {
				$sp_option = array(
					'label' => $sp_option,
				);
			}
			foreach ( array( 'min', 'max', 'step' ) as $ovkey ) {
				if ( ! isset( $sp_option[$ovkey] ) || $sp_option[$ovkey] == '' ) {
					$sp_option[$ovkey] = $element_data['settings'][$ovkey];
				}
			}
			$sp_title = $sp_option['label'];
			$spinners[] = array(
				'name' => $name_prefix . '[options][' . $sp_key . ']',
				'value' => isset( $submission_data['options'][$sp_key] ) ? $submission_data['options'][$sp_key] : $sp_option['min'],
				'placeholder' => __( 'Enter a number', 'ipt_fsqm' ),
				'min' => $sp_option['min'],
				'max' => $sp_option['max'],
				'step' => $sp_option['step'],
				'title' => $sp_option['label'],
				'required' => $element_data['validation']['required'],
			);
		}
		$this->ui->question_container( $name_prefix, $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'spinners' ), array( $spinners ) ), $element_data['validation']['required'], true, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_grading( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_grading', $element_data['tooltip'] );
		$type = $element_data['settings']['range'] == true ? 'range' : 'single';
		$sliders = array();
		foreach ( $element_data['settings']['options'] as $sl_key => $sl_option ) {
			if ( ! is_array( $sl_option ) ) {
				// backward compatibility -2.4.0
				$sl_option = array(
					'label' => $sl_option,
					'prefix' => '',
					'suffix' => '',
				);
			}
			foreach ( array( 'min', 'max', 'step' ) as $ovkey ) {
				if ( ! isset( $sl_option[ $ovkey ] ) ) {
					$sl_option[ $ovkey ] = '';
				}
			}
			// Modify the submission data if new submission
			if ( null == $this->data_id && isset( $submission_data['options'] ) ) {
				// Range
				if ( 'range' == $type ) {
					// Set the default ones
					$submission_data['options'][ $sl_key ] = array(
						'min' => '',
						'max' => '',
					);
					// In case of new submission, override the default value
					if ( '' != $element_data['settings']['dmin'] ) {
						$submission_data['options'][ $sl_key ]['min'] = $element_data['settings']['dmin'];
					}
					if ( '' != $element_data['settings']['dmax'] ) {
						$submission_data['options'][ $sl_key ]['max'] = $element_data['settings']['dmax'];
					}
				// Slider
				} else {
					if ( '' != $element_data['settings']['dmin'] ) {
						$submission_data['options'][ $sl_key ] = $element_data['settings']['dmin'];
					}
				}
			}
			$sliders[] = array(
				'name' => $name_prefix . '[options][' . $sl_key . ']',
				'value' => isset( $submission_data['options'][$sl_key] ) ? $submission_data['options'][$sl_key] : '',
				'title' => $sl_option['label'],
				'type' => $type,
				'prefix' => $sl_option['prefix'],
				'suffix' => $sl_option['suffix'],
				'min' => $sl_option['min'],
				'max' => $sl_option['max'],
				'step' => $sl_option['step'],
			);
		}
		$params = array( $name_prefix, $sliders, $element_data['settings']['show_count'], $element_data['settings']['min'], $element_data['settings']['max'], $element_data['settings']['step'], $element_data['settings']['label'], $element_data['settings']['nomin'], $element_data['settings']['floats'], $element_data['settings']['vertical_ui'], $element_data['settings']['height'] );
		$this->ui->question_container( $name_prefix, $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'sliders' ), $params ), true, true, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_smileyrating( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_smileyrating', $element_data['tooltip'] );
		$enabled = (array) $element_data['settings']['enabled'];
		$data_attr = array();
		foreach ( (array) $element_data['settings']['num'] as $key => $val ) {
			if ( $val != '' ) {
				$data_attr[$key] = array(
					'num' => $val,
				);
			}
		}
		$feedback_placeholder = $element_data['settings']['feedback_label'];
		$param = array( $name_prefix . '[option]', $submission_data['option'], $enabled, $element_data['validation']['required'], $element_data['settings']['labels'], array(), $data_attr, $element_data['settings']['show_feedback'], $name_prefix . '[feedback]', $submission_data['feedback'], $feedback_placeholder, $element_data['settings']['reverse_order'] );
		$this->ui->question_container( $name_prefix, $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'smiley_rating' ), $param ), $element_data['validation']['required'], false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_starrating( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_starrating', $element_data['tooltip'] );
		$ratings = array();
		foreach ( $element_data['settings']['options'] as $r_key => $r_title ) {
			$ratings[] = array(
				'name' => $name_prefix . '[options][' . $r_key . ']',
				'value' => isset( $submission_data['options'][$r_key] ) ? $submission_data['options'][$r_key] : '',
				'max' => $element_data['settings']['max'],
				'required' => $element_data['validation']['required'],
				'title' => $r_title,
				'labels' => array(
					'low' => $element_data['settings']['label_low'],
					'high' => $element_data['settings']['label_high'],
				),
			);
		}
		$param = array( $ratings, 'star' );
		$this->ui->question_container( $name_prefix, $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'ratings' ), $param ), $element_data['validation']['required'], true, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_scalerating( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_scalerating', $element_data['tooltip'] );
		$ratings = array();
		foreach ( $element_data['settings']['options'] as $r_key => $r_title ) {
			$ratings[] = array(
				'name' => $name_prefix . '[options][' . $r_key . ']',
				'value' => isset( $submission_data['options'][$r_key] ) ? $submission_data['options'][$r_key] : '',
				'max' => $element_data['settings']['max'],
				'required' => $element_data['validation']['required'],
				'title' => $r_title,
				'labels' => array(
					'low' => $element_data['settings']['label_low'],
					'high' => $element_data['settings']['label_high'],
				),
			);
		}
		$param = array( $ratings, 'scale' );
		$this->ui->question_container( $name_prefix, $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'ratings' ), $param ), $element_data['validation']['required'], true, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_matrix( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_matrix', $element_data['tooltip'] );
		$params = array( $name_prefix, $element_data['settings']['rows'], $element_data['settings']['columns'], $submission_data['rows'], $element_data['settings']['multiple'], $element_data['validation']['required'], $element_data['settings']['icon'], $element_data['settings']['numerics'] );
		$this->ui->question_container( $name_prefix, $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'matrix' ), $params ), $element_data['validation']['required'], false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_matrix_dropdown( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_matrix_dropdown ipt_fsqm_container_matrix', $element_data['tooltip'] );
		$items = array();
		if ( $element_data['settings']['empty'] != '' ) {
			$items[] = array(
				'label' => $element_data['settings']['empty'],
				'value' => '',
			);
		}
		foreach ( $element_data['settings']['options'] as $o_key => $option ) {
			$items[] = array(
				'label' => $option['label'],
				'value' => (string) $o_key,
				'data'  => array(
					'num' => $option['num'],
				),
			);
		}
		$params = array( $name_prefix, $element_data['settings']['rows'], $element_data['settings']['columns'], $items, $submission_data['rows'], $element_data['validation'], $element_data['settings']['multiple'] );
		$this->ui->question_container( $name_prefix, $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'matrix_select' ), $params ), $element_data['validation']['required'], false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_likedislike( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_likedislike', $element_data['tooltip'] );
		$feedback_placeholder = $element_data['settings']['feedback_label'];
		$value = $submission_data['value'];
		if ( $this->data_id == null ) {
			if ( $element_data['settings']['liked'] == true ) {
				$value = 'like';
			}
		}
		$param = array( $name_prefix . '[value]', array(
			'like' => $element_data['settings']['like'],
			'dislike' => $element_data['settings']['dislike'],
		), array(
			'like' => 'like',
			'dislike' => 'dislike',
		), $value, $element_data['validation']['required'], array(), array(), $element_data['settings']['show_feedback'], $name_prefix . '[feedback]', $submission_data['feedback'], $feedback_placeholder );
		$this->ui->question_container( $name_prefix, $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'likedislike' ), $param ), $element_data['validation']['required'], false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_toggle( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_toggle', $element_data['tooltip'] );
		$value = $submission_data['value'];
		if ( $this->data_id == null ) {
			$value = $element_data['settings']['checked'];
		}
		$param = array( $name_prefix . '[value]', $element_data['settings']['on'], $element_data['settings']['off'], $value, '1', false, false, array(
			'defaultState' => $element_data['settings']['checked'],
		) );
		$this->ui->question_container( $name_prefix . '[value]', $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'toggle' ), $param ), false, false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_sorting( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_sorting', $element_data['tooltip'] );
		$params = array( $name_prefix . '[order][]', $element_data['settings']['options'], $submission_data['order'], ! $element_data['settings']['no_shuffle'] );
		$this->ui->question_container( $name_prefix, $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'sortables' ), $params ), true, false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_feedback_large( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_feedback_large', $element_data['tooltip'] );
		$eclasses = array();
		$edata = array();
		if ( $element_data['settings']['keypad'] == true ) {
			$eclasses[] = 'ipt_uif_keypad';
			$edata = array(
				'settings' => json_encode( array(
					'layout' => $element_data['settings']['ktype'],
				) ),
			);
		}
		// Get default
		if ( null == $this->data_id && empty( $submission_data['value'] ) ) {
			$default = $element_data['settings']['default'];

			// Check for URL & User Meta
			// value key
			$parameter = $element_data['settings']['parameter'];
			// Check for the type of data we need to process
			switch ( $element_data['settings']['type'] ) {
				case 'url':
					$default = IPT_FSQM_Form_Elements_Static::get_request_parameter( $parameter, $default );
					break;
				case 'meta':
					$default = IPT_FSQM_Form_Elements_Static::get_user_metadata( $parameter, $default );
					break;
				case 'postmeta':
					$default = IPT_FSQM_Form_Elements_Static::get_post_metadata( $parameter, $default );
					break;
			}
			// Set the value
			$submission_data['value'] = $default;
		}

		$params = array(
			$name_prefix . '[value]',
			$submission_data['value'],
			$element_data['settings']['placeholder'],
			( true == $element_data['settings']['readonly'] ? 'readonly' : 'normal' ),
			$eclasses,
			$element_data['validation'],
			$edata,
			false,
			$element_data['settings']['icon'],
			false,
			$element_data['settings']['native_placeholder']
		);
		$this->ui->question_container( $name_prefix . '[value]', $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'textarea' ), $params ), $element_data['validation']['required'], false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		if ( $this->doing_admin && '' != $element_data['settings']['score'] && is_numeric( $element_data['settings']['score'] ) ) {
			if ( ! isset( $submission_data['score'] ) ) {
				$submission_data['score'] = '';
			}
			$score_params = array( $name_prefix . '[score]', $submission_data['score'], __( 'Unassigned', 'ipt_fsqm' ), '', $element_data['settings']['score'] );
			$this->ui->question_container( $name_prefix . '[score]', __( '[Administrate] Score the result', 'ipt_fsqm' ), sprintf( __( 'out of %s', 'ipt_fsqm' ), abs( $element_data['settings']['score'] ) ), array( array( $this->ui, 'spinner' ), $score_params ) );
		}
		$this->ui->column_tail();
	}

	public function build_feedback_small( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_feedback_small', $element_data['tooltip'] );
		$eclasses = array();
		$edata = array();
		if ( $element_data['settings']['keypad'] == true ) {
			$eclasses[] = 'ipt_uif_keypad';
			$edata = array(
				'settings' => json_encode( array(
					'layout' => $element_data['settings']['ktype'],
				) ),
			);
		}
		// Modify the validation
		$element_data['validation'] = $this->validation_mods( $element_data['validation'] );
		// If registration is from this field, then force filter to noSpecialCharacter
		if ( $this->is_element_of_reg_field( 'freetype', $key, 'username_id' ) ) {
			$element_data['validation']['filters']['type'] = 'noSpecialCharacter';
		}
		// Get default
		if ( null == $this->data_id && empty( $submission_data['value'] ) ) {
			$default = $element_data['settings']['default'];

			// Check for URL & User Meta
			// value key
			$parameter = $element_data['settings']['parameter'];
			// Check for the type of data we need to process
			switch ( $element_data['settings']['type'] ) {
				case 'url':
					$default = IPT_FSQM_Form_Elements_Static::get_request_parameter( $parameter, $default );
					break;
				case 'meta':
					$default = IPT_FSQM_Form_Elements_Static::get_user_metadata( $parameter, $default );
					break;
				case 'postmeta':
					$default = IPT_FSQM_Form_Elements_Static::get_post_metadata( $parameter, $default );
					break;
			}
			// Set the value
			$submission_data['value'] = $default;
		}

		$params = array(
			$name_prefix . '[value]',
			$submission_data['value'],
			$element_data['settings']['placeholder'],
			$element_data['settings']['icon'],
			( true == $element_data['settings']['readonly'] ? 'readonly' : 'normal' ),
			$eclasses,
			$element_data['validation'],
			$edata,
			false,
			$element_data['settings']['inline'],
			$element_data['settings']['native_placeholder']
		);
		$this->ui->question_container( $name_prefix . '[value]', $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'text' ), $params ), $element_data['validation']['required'], false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		if ( $this->doing_admin && '' != $element_data['settings']['score'] && is_numeric( $element_data['settings']['score'] ) ) {
			if ( ! isset( $submission_data['score'] ) ) {
				$submission_data['score'] = '';
			}
			$score_params = array( $name_prefix . '[score]', $submission_data['score'], __( 'Unassigned', 'ipt_fsqm' ), '', $element_data['settings']['score'] );
			$this->ui->question_container( $name_prefix . '[score]', __( '[Administrate] Score the result', 'ipt_fsqm' ), sprintf( __( 'out of %s', 'ipt_fsqm' ), abs( $element_data['settings']['score'] ) ), array( array( $this->ui, 'spinner' ), $score_params ) );
		}
		$this->ui->column_tail();
	}

	public function build_feedback_matrix( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_feedback_matrix', $element_data['tooltip'] );
		$params = array( $name_prefix, $element_data['settings']['rows'], $element_data['settings']['columns'], $submission_data['rows'], $element_data['settings']['multiline'], $element_data['validation'], $element_data['settings']['icon'] );
		$this->ui->question_container( $name_prefix, $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'matrix_text' ), $params ), $element_data['validation']['required'], false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_gps( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		global $ipt_fsqm_settings;
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$classes = array( 'ipt_fsqm_container_gps' );
		if ( true == $element_data['settings']['centered'] ) {
			$classes[] = 'column-centered-heading';
		}
		$this->ui->column_head( $id, 'full', true, $classes, $element_data['tooltip'] );
		// If no api key, then point to documentation
		if ( '' === $ipt_fsqm_settings['gplaces_api'] ) {
			$this->ui->msg_error( sprintf( __( 'You have not added a Google Places API key to eForm Settings. Please read <a href="%1$s">this article</a> to get started.', 'ipt_fsqm' ), 'https://iptms.co/5' ), true, __( 'No Google Places API Key', 'ipt_fsqm' ) );
			$this->ui->column_tail();
			return;
		}
		/**
		 * Override Cases:
		 *
		 * 1. First time form submit ($this->data_id === null) => $show_ui = true
		 * 2. Updating form && can update ( $this->data_id !== null && $this->can_submit && $this->can_user_edit() ) => $show_ui = true
		 * 3. Viewing from trackback ( $this->data_id !== null && ! $this->can_submit ) => $show_ui = false, $can_delete = false
		 * 4. Admin update ( $this->data_id !== null && $this->doing_admin ) => $show_ui = true, $can_delete = true;
		 */
		$show_ui = false;
		if ( $this->data_id === null ) {
			$show_ui = true;
		} elseif ( $this->data_id !== null && $this->can_submit && $this->can_user_edit() ) {
			$show_ui = true;
		} elseif ( $this->data_id !== null && $this->doing_admin ) {
			$show_ui = true;
			$element_data['settings']['manualcontrol'] = true;
		} else {
			$show_ui = false;
			$element_data['settings']['manualcontrol'] = false;
		}

		$error = __( 'We could not determine your location. Make sure you are connected to a network and you have GPS and location service turned on.', 'ipt_fsqm' );

		$params = array( $name_prefix, array(
			'lat' => $submission_data['lat'],
			'long' => $submission_data['long'],
			'location_name' => $submission_data['location_name'],
		), $element_data['settings']['manualcontrol'], array(
			'lat' => $element_data['settings']['lat_label'],
			'long' => $element_data['settings']['long_label'],
			'location_name' => $element_data['settings']['location_name_label'],
			'update' => $element_data['settings']['update_label'],
			'nolocation' => $element_data['settings']['nolocation_label'],
		), $element_data['description'], $error, __( 'Locating', 'ipt_fsqm' ), $element_data['settings']['radius'], $element_data['settings']['zoom'], $element_data['settings']['scrollwheel'], $show_ui, $element_data['validation']['required'], $ipt_fsqm_settings['gplaces_api'] );
		$this->ui->container( array( array( $this->ui, 'locationpicker' ), $params ), $element_data['title'], $element_data['settings']['icon'], false, true, '' );
		$this->ui->column_tail();
	}

	public function build_upload( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$files_key = 'ipt_fsqm_file_upload_' . $this->form_id . '_' . $key;
		$classes = array( 'ipt_fsqm_container_upload' );
		if ( true == $element_data['settings']['centered'] ) {
			$classes[] = 'column-centered-heading';
		}
		$this->ui->column_head( $id, 'full', true, $classes, $element_data['tooltip'] );
		$attributes = array(
			'ajax_upload' => 'ipt_fsqm_fu_upload',
			'ajax_download' => 'ipt_fsqm_fu_download',
			'fetch_files' => $this->data_id == null ? false : true,
		);
		$form_data = array(
			'data_id' => $this->data_id,
			'form_id' => $this->form_id,
			'nonce' => wp_create_nonce( 'ipt_fsqm_upload_' . $this->form_id . '_' . $this->data_id . '_' . $key ),
			'element_key' => $key,
			'files_key' => $files_key,
		);
		if ( $attributes['fetch_files'] ) {
			$form_data['download_nonce'] = wp_create_nonce( 'ipt_fsqm_download_' . $this->form_id . '_' . $this->data_id . '_' . $key );
		}

		/**
		 * Override Cases:
		 *
		 * 1. First time form submit ($this->data_id === null) => $show_ui = true
		 * 2. Updating form && can update ( $this->data_id !== null && $this->can_submit && $this->can_user_edit() ) => $show_ui = true
		 * 3. Viewing from trackback ( $this->data_id !== null && ! $this->can_submit ) => $show_ui = false, $can_delete = false
		 * 4. Admin update ( $this->data_id !== null && $this->doing_admin ) => $show_ui = true, $can_delete = true;
		 */
		$show_ui = false;
		if ( $this->data_id === null ) {
			$show_ui = true;
		} elseif ( $this->data_id !== null && $this->can_submit && $this->can_user_edit() ) {
			$show_ui = true;
		} elseif ( $this->data_id !== null && $this->doing_admin ) {
			$show_ui = true;
			$element_data['settings']['can_delete'] = true;
		} else {
			$element_data['settings']['can_delete'] = false;
		}

		// Add the Label #391
		$labels = array(
			'dragdrop' => $element_data['settings']['dragdrop'],
			'select' => $element_data['settings']['upload_label'],
		);

		$max_upload_size = $this->get_maximum_file_upload_size();
		$settings = $element_data['settings'];
		// enqueue recaptcha if needed
		if ( $settings['recaptcha'] === true ) {
			wp_enqueue_script( 'eform-recaptcha-' . $element_data['settings']['hl'], 'https://www.google.com/recaptcha/api.js?onload=eFormreCaptchaLoad&render=explicit&hl=' . $element_data['settings']['hl'], array( 'ipt-fsqm-front-js' ), '2.0.0', true );
		}
		// remove secret key from client
		unset( $settings['secret_key'] );
		$recaptcha_fallback = __( 'Loading captcha…', 'ipt_fsqm' );
		if ( $this->live_view ) {
			$recaptcha_fallback = __( 'Captcha will show up in the real form.', 'ipt_fsqm' );
		}
		$params = array( $files_key . '[]', $name_prefix . '[id][]', $settings, $attributes, $form_data, $element_data['description'], $labels, $element_data['validation']['required'], $max_upload_size, $show_ui, $recaptcha_fallback );
		$this->ui->container( array( array( $this->ui, 'uploader' ), $params ), $element_data['title'], $element_data['settings']['icon'], false, true, '' );
		$this->ui->column_tail();
	}

	public function build_signature( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$classes = array( 'ipt_fsqm_container_jsignature' );
		if ( true == $element_data['settings']['centered'] ) {
			$classes[] = 'column-centered-heading';
		}
		$this->ui->column_head( $id, 'full', true, $classes, $element_data['tooltip'] );
		$element_data['validation']['funccall'] = 'iptUIFSigVal';
		$params = array( $name_prefix . '[value]', $submission_data['value'], $element_data['settings'], $element_data['description'], $element_data['validation'], $element_data['settings']['color'] );
		$this->ui->container( array( array( $this->ui, 'signature' ), $params ), $element_data['title'], $element_data['settings']['icon'], false, true, '' );
		$this->ui->column_tail();
	}

	public function build_mathematical( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;

		$options = array(
			'useGrouping' => $element_data['settings']['grouping'],
			'separator' => $element_data['settings']['separator'],
			'decimal' => $element_data['settings']['decimal'],
		);

		$classes = array();
		if ( true == $element_data['settings']['fancy'] && false == $element_data['settings']['hidden'] ) {
			$classes[] = 'ipt-eform-math-fancy';
		} else if ( true == $element_data['settings']['right'] ) {
			$classes[] = 'ipt-eform-math-row';
		}

		$params = array( $name_prefix . '[value]', $submission_data['value'], $element_data['settings']['formula'], $element_data['settings']['editable'], $element_data['settings']['icon'], $element_data['settings']['prefix'], $element_data['settings']['suffix'], $element_data['settings']['precision'], $options, $classes, $element_data['settings']['noanim'], $element_data['settings']['hidden'] );

		if ( true == $element_data['settings']['hidden'] ) {
			$this->ui->column_head( $id, 'full', false, array( 'ipt_fsqm_container_mathematical_hidden', 'no_margin_top', 'no_margin_bottom' ), $element_data['tooltip'] );
			call_user_func_array( array( $this->ui, 'mathematical' ), $params );
		} else {
			$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_mathematical ' . implode( ' ', $classes ), $element_data['tooltip'] );

			$this->ui->question_container( $name_prefix . '[value]', $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'mathematical' ), $params ), false, true, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		}

		$this->ui->column_tail();
	}

	public function build_payment( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->enqueue_payment();
		$classes = array( 'ipt_fsqm_container_payment' );
		if ( $element_data['settings']['vertical'] ) {
			$classes[] = 'column-vertical-heading';
		}
		if ( true == $element_data['settings']['centered'] ) {
			$classes[] = 'column-centered-heading';
		}
		$this->ui->column_head( $id, 'full', false, $classes, $element_data['tooltip'] );
		$options = array(
			'useGrouping' => $element_data['settings']['grouping'],
			'separator' => $element_data['settings']['separator'],
			'decimal' => $element_data['settings']['decimal'],
		);
		$classes = array();
		if ( true == $element_data['settings']['fancy'] ) {
			$classes[] = 'ipt-eform-math-fancy';
		} else if ( true == $element_data['settings']['right'] ) {
			$classes[] = 'ipt-eform-math-row';
		}
		$classes[] = 'ipt_fsqm_payment_mathematical';
		$this->ui->column_head( $id . '_mathematical', 'full', true, 'ipt_fsqm_container_mathematical ' . implode( ' ', $classes ) );
		$params = array( $name_prefix . '[value]', $submission_data['value'], $this->settings['payment']['formula'], false, $element_data['settings']['icon'], $element_data['settings']['prefix'], $element_data['settings']['suffix'], $element_data['settings']['precision'], $options, $classes, $element_data['settings']['noanim'] );
		$this->ui->question_container( $name_prefix . '[value]', $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'mathematical' ), $params ), false, true, false, $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();

		if ( true !== $this->settings['payment']['enabled'] ) {
			$this->ui->column_head( $id . '_payment_error', 'full', true, 'ipt_fsqm_container_payment_error' );
			$this->ui->msg_error( __( 'Please enable payment from CONFIG > Payment to use this element.', 'ipt_fsqm' ), true, __( 'Form Payment not enabled', 'ipt_fsqm' ) );
			$this->ui->column_tail();
		}

		if ( $this->settings['payment']['enabled'] === true && $this->data_id == null ) {
			$this->ui->column_head( $id . '_checkout', 'full', true, 'ipt_fsqm_container_checkout' );
			if ( ! empty( $this->settings['payment']['coupons'] ) ) {
				$cparams = array( $name_prefix . '[coupon]', __( 'enter your code', 'ipt_fsqm' ), __( 'Apply', 'ipt_fsqm' ), array(
					'action' => 'ipt_fsqm_validate_coupon',
					'form_id' => $this->form_id,
					'cnonce' => wp_create_nonce( 'ipt_fsqm_coupon_' . $this->form_id ),
					'wait' => __( 'Please wait', 'ipt_fsqm' ),
					'valid' => __( 'Successfully applied coupon', 'ipt_fsqm' ),
					'invalid' => __( 'Invalid coupon code', 'ipt_fsqm' ),
					'normal' => __( 'Apply', 'ipt_fsqm' ),
					'http_error' => __( 'Unable to contact server. Please report to website admin', 'ipt_fsqm' ),
				), $this->settings['payment']['formula'], $name_prefix . '[couponval]', $element_data['settings']['icon'], $element_data['settings']['suffix'], $element_data['settings']['precision'], $options, $element_data['settings']['noanim'] );
				$this->ui->question_container( $name_prefix, __( 'Enter Discount Coupon', 'ipt_fsqm' ), '', array( array( $this->ui, 'coupon' ), $cparams ), false );
			}

			$payment_form = new EForm_Payment_Form( $this->form_id, $name_prefix, $this->ui, $element_data, $key, $this->settings['payment'] );
			$payment_form->show_form();

			$this->ui->column_tail();
		}
		$this->ui->column_tail();
	}

	public function build_f_name( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_f_name', $element_data['tooltip'] );
		// Get default
		if ( null == $this->data_id && empty( $submission_data['value'] ) ) {
			$default = $element_data['settings']['default'];
			if ( '' != $this->data->f_name ) {
				$default = $this->data->f_name;
			}

			// Check for URL & User Meta
			// value key
			$parameter = $element_data['settings']['parameter'];
			// Check for the type of data we need to process
			switch ( $element_data['settings']['type'] ) {
				case 'url':
					$default = IPT_FSQM_Form_Elements_Static::get_request_parameter( $parameter, $default );
					break;
				case 'meta':
					$default = IPT_FSQM_Form_Elements_Static::get_user_metadata( $parameter, $default );
					break;
				case 'postmeta':
					$default = IPT_FSQM_Form_Elements_Static::get_post_metadata( $parameter, $default );
					break;
			}
			// Set the value
			$submission_data['value'] = $default;
		}

		$params = array(
			$name_prefix . '[value]',
			$submission_data['value'],
			$element_data['settings']['placeholder'],
			$element_data['settings']['icon'],
			( true == $element_data['settings']['readonly'] ? 'readonly' : 'normal' ),
			array(),
			$element_data['validation'],
			false,
			false,
			[],
			$element_data['settings']['native_placeholder']
		);
		$this->ui->question_container( $name_prefix . '[value]', $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'text' ), $params ), $element_data['validation']['required'], false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_l_name( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_l_name', $element_data['tooltip'] );
		// Get default
		if ( null == $this->data_id && empty( $submission_data['value'] ) ) {
			$default = $element_data['settings']['default'];
			if ( '' != $this->data->l_name ) {
				$default = $this->data->l_name;
			}

			// Check for URL & User Meta
			// value key
			$parameter = $element_data['settings']['parameter'];
			// Check for the type of data we need to process
			switch ( $element_data['settings']['type'] ) {
				case 'url':
					$default = IPT_FSQM_Form_Elements_Static::get_request_parameter( $parameter, $default );
					break;
				case 'meta':
					$default = IPT_FSQM_Form_Elements_Static::get_user_metadata( $parameter, $default );
					break;
				case 'postmeta':
					$default = IPT_FSQM_Form_Elements_Static::get_post_metadata( $parameter, $default );
					break;
			}
			// Set the value
			$submission_data['value'] = $default;
		}

		$params = array(
			$name_prefix . '[value]',
			$submission_data['value'],
			$element_data['settings']['placeholder'],
			$element_data['settings']['icon'],
			( true == $element_data['settings']['readonly'] ? 'readonly' : 'normal' ),
			array(),
			$element_data['validation'],
			false,
			false,
			[],
			$element_data['settings']['native_placeholder']
		);
		$this->ui->question_container( $name_prefix . '[value]', $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'text' ), $params ), $element_data['validation']['required'], false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_email( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_email', $element_data['tooltip'] );
		$element_data['validation']['filters'] = array(
			'type' => 'email',
		);
		// Modify the validation
		$element_data['validation'] = $this->validation_mods( $element_data['validation'] );
		// Get default
		if ( null == $this->data_id && empty( $submission_data['value'] ) ) {
			$default = $element_data['settings']['default'];
			if ( '' != $this->data->email ) {
				$default = $this->data->email;
			}

			// Check for URL & User Meta
			// value key
			$parameter = $element_data['settings']['parameter'];
			// Check for the type of data we need to process
			switch ( $element_data['settings']['type'] ) {
				case 'url':
					$default = IPT_FSQM_Form_Elements_Static::get_request_parameter( $parameter, $default );
					break;
				case 'meta':
					$default = IPT_FSQM_Form_Elements_Static::get_user_metadata( $parameter, $default );
					break;
				case 'postmeta':
					$default = IPT_FSQM_Form_Elements_Static::get_post_metadata( $parameter, $default );
					break;
			}
			// Check if default value is email
			if ( ! is_email( $default ) ) {
				$default = '';
			}
			// Set the value
			$submission_data['value'] = $default;
		}

		$params = array(
			$name_prefix . '[value]',
			$submission_data['value'],
			$element_data['settings']['placeholder'],
			$element_data['settings']['icon'],
			( true == $element_data['settings']['readonly'] ? 'readonly' : 'normal' ),
			array(),
			$element_data['validation'],
			false,
			false,
			[],
			$element_data['settings']['native_placeholder']
		);
		$this->ui->question_container( $name_prefix . '[value]', $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'text' ), $params ), $element_data['validation']['required'], false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_phone( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_phone', $element_data['tooltip'] );
		if ( ! isset( $element_data['validation']['filters'] ) ) {
			$element_data['validation']['filters'] = array(
				'type' => 'phone',
			);
		} else {
			$element_data['validation']['filters']['type'] = 'phone';
		}
		// Modify the validation
		$element_data['validation'] = $this->validation_mods( $element_data['validation'] );

		// Get default
		if ( null == $this->data_id && empty( $submission_data['value'] ) ) {
			$default = $element_data['settings']['default'];

			// Check for URL & User Meta
			// value key
			$parameter = $element_data['settings']['parameter'];
			// Check for the type of data we need to process
			switch ( $element_data['settings']['type'] ) {
				case 'url':
					$default = IPT_FSQM_Form_Elements_Static::get_request_parameter( $parameter, $default );
					break;
				case 'meta':
					$default = IPT_FSQM_Form_Elements_Static::get_user_metadata( $parameter, $default );
					break;
				case 'postmeta':
					$default = IPT_FSQM_Form_Elements_Static::get_post_metadata( $parameter, $default );
					break;
			}
			// Set the value
			$submission_data['value'] = $default;
		}

		$params = array(
			$name_prefix . '[value]',
			$submission_data['value'],
			$element_data['settings']['placeholder'],
			$element_data['settings']['icon'],
			( true == $element_data['settings']['readonly'] ? 'readonly' : 'normal' ),
			array(),
			$element_data['validation'],
			false,
			false,
			[],
			$element_data['settings']['native_placeholder']
		);
		$this->ui->question_container( $name_prefix . '[value]', $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'text' ), $params ), $element_data['validation']['required'], false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_p_name( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_p_name', $element_data['tooltip'] );
		// Modify the validation
		$element_data['validation'] = $this->validation_mods( $element_data['validation'] );
		// Get default
		if ( null == $this->data_id && empty( $submission_data['value'] ) ) {
			$default = $element_data['settings']['default'];

			// Check for URL & User Meta
			// value key
			$parameter = $element_data['settings']['parameter'];
			// Check for the type of data we need to process
			switch ( $element_data['settings']['type'] ) {
				case 'url':
					$default = IPT_FSQM_Form_Elements_Static::get_request_parameter( $parameter, $default );
					break;
				case 'meta':
					$default = IPT_FSQM_Form_Elements_Static::get_user_metadata( $parameter, $default );
					break;
				case 'postmeta':
					$default = IPT_FSQM_Form_Elements_Static::get_post_metadata( $parameter, $default );
					break;
			}
			// Set the value
			$submission_data['value'] = $default;
		}

		$params = array( $name_prefix . '[value]', $submission_data['value'], $element_data['settings']['placeholder'], $element_data['settings']['icon'], ( true == $element_data['settings']['readonly'] ? 'readonly' : 'normal' ), array(), $element_data['validation'] );
		$this->ui->question_container( $name_prefix . '[value]', $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'text' ), $params ), $element_data['validation']['required'], false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_p_email( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_p_email', $element_data['tooltip'] );
		$element_data['validation']['filters'] = array(
			'type' => 'email',
		);
		// Modify the validation
		$element_data['validation'] = $this->validation_mods( $element_data['validation'] );
		// Get default
		if ( null == $this->data_id && empty( $submission_data['value'] ) ) {
			$default = $element_data['settings']['default'];

			// Check for URL & User Meta
			// value key
			$parameter = $element_data['settings']['parameter'];
			// Check for the type of data we need to process
			switch ( $element_data['settings']['type'] ) {
				case 'url':
					$default = IPT_FSQM_Form_Elements_Static::get_request_parameter( $parameter, $default );
					break;
				case 'meta':
					$default = IPT_FSQM_Form_Elements_Static::get_user_metadata( $parameter, $default );
					break;
				case 'postmeta':
					$default = IPT_FSQM_Form_Elements_Static::get_post_metadata( $parameter, $default );
					break;
			}
			// Check if email
			if ( ! is_email( $default ) ) {
				$default = '';
			}
			// Set the value
			$submission_data['value'] = $default;
		}
		$params = array( $name_prefix . '[value]', $submission_data['value'], $element_data['settings']['placeholder'], $element_data['settings']['icon'], ( true == $element_data['settings']['readonly'] ? 'readonly' : 'normal' ), array(), $element_data['validation'] );
		$this->ui->question_container( $name_prefix . '[value]', $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'text' ), $params ), $element_data['validation']['required'], false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_p_phone( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_p_phone', $element_data['tooltip'] );
		if ( ! isset( $element_data['validation']['filters'] ) ) {
			$element_data['validation']['filters'] = array(
				'type' => 'phone',
			);
		} else {
			$element_data['validation']['filters']['type'] = 'phone';
		}
		// Modify the validation
		$element_data['validation'] = $this->validation_mods( $element_data['validation'] );
		// Get default
		if ( null == $this->data_id && empty( $submission_data['value'] ) ) {
			$default = $element_data['settings']['default'];

			// Check for URL & User Meta
			// value key
			$parameter = $element_data['settings']['parameter'];
			// Check for the type of data we need to process
			switch ( $element_data['settings']['type'] ) {
				case 'url':
					$default = IPT_FSQM_Form_Elements_Static::get_request_parameter( $parameter, $default );
					break;
				case 'meta':
					$default = IPT_FSQM_Form_Elements_Static::get_user_metadata( $parameter, $default );
					break;
				case 'postmeta':
					$default = IPT_FSQM_Form_Elements_Static::get_post_metadata( $parameter, $default );
					break;
			}
			// Set the value
			$submission_data['value'] = $default;
		}
		$params = array(
			$name_prefix . '[value]',
			$submission_data['value'],
			$element_data['settings']['placeholder'],
			$element_data['settings']['icon'],
			( true == $element_data['settings']['readonly'] ? 'readonly' : 'normal' ),
			array(),
			$element_data['validation'],
			false,
			false,
			[],
			$element_data['settings']['native_placeholder']
		);
		$this->ui->question_container( $name_prefix . '[value]', $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'text' ), $params ), $element_data['validation']['required'], false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_textinput( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_textinput', $element_data['tooltip'] );
		// Force the validation to no-special-character
		// If registration is enabled
		if ( $this->is_element_of_reg_field( 'pinfo', $key, 'username_id' ) ) {
			$element_data['validation']['filters']['type'] = 'noSpecialCharacter';
		}
		// Get default
		if ( null == $this->data_id && empty( $submission_data['value'] ) ) {
			$default = $element_data['settings']['default'];

			// Check for URL & User Meta
			// value key
			$parameter = $element_data['settings']['parameter'];
			// Check for the type of data we need to process
			switch ( $element_data['settings']['type'] ) {
				case 'url':
					$default = IPT_FSQM_Form_Elements_Static::get_request_parameter( $parameter, $default );
					break;
				case 'meta':
					$default = IPT_FSQM_Form_Elements_Static::get_user_metadata( $parameter, $default );
					break;
				case 'postmeta':
					$default = IPT_FSQM_Form_Elements_Static::get_post_metadata( $parameter, $default );
					break;
			}
			// Set the value
			$submission_data['value'] = $default;
		}
		$params = array( $name_prefix . '[value]', $submission_data['value'], $element_data['settings']['placeholder'], $element_data['settings']['icon'], ( true == $element_data['settings']['readonly'] ? 'readonly' : 'normal' ), array(), $element_data['validation'] );
		$this->ui->question_container( $name_prefix . '[value]', $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'text' ), $params ), $element_data['validation']['required'], false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_textarea( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_textarea', $element_data['tooltip'] );
		// Get default
		if ( null == $this->data_id && empty( $submission_data['value'] ) ) {
			$default = $element_data['settings']['default'];

			// Check for URL & User Meta
			// value key
			$parameter = $element_data['settings']['parameter'];
			// Check for the type of data we need to process
			switch ( $element_data['settings']['type'] ) {
				case 'url':
					$default = IPT_FSQM_Form_Elements_Static::get_request_parameter( $parameter, $default );
					break;
				case 'meta':
					$default = IPT_FSQM_Form_Elements_Static::get_user_metadata( $parameter, $default );
					break;
				case 'postmeta':
					$default = IPT_FSQM_Form_Elements_Static::get_post_metadata( $parameter, $default );
					break;
			}
			// Set the value
			$submission_data['value'] = $default;
		}
		$params = array( $name_prefix . '[value]', $submission_data['value'], $element_data['settings']['placeholder'], ( true == $element_data['settings']['readonly'] ? 'readonly' : 'normal' ), array(), $element_data['validation'], false, false, $element_data['settings']['icon'] );
		$this->ui->question_container( $name_prefix . '[value]', $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'textarea' ), $params ), $element_data['validation']['required'], false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_guestblog( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$classes = array( 'ipt_fsqm_container_guestblog' );
		if ( true == $element_data['settings']['centered'] ) {
			$classes[] = 'column-centered-heading';
		}
		$this->ui->column_head( $id, 'full', true, $classes, $element_data['tooltip'] );
		$post_title_label = $element_data['settings']['title_label'];
		$post_title = $submission_data['title'];
		$trumbowyg = false;
		$taxonomy_list = false;
		if ( ! empty( $this->settings['core']['post']['taxonomies'][ $this->settings['core']['post']['post_type'] ] ) ) {
			$taxonomy_list = $this->settings['core']['post']['taxonomies'][ $this->settings['core']['post']['post_type'] ];
		}
		$taxonomy_singles = array();
		if ( ! empty( $this->settings['core']['post']['taxnomy_single'][ $this->settings['core']['post']['post_type'] ] ) ) {
			$taxonomy_singles = $this->settings['core']['post']['taxnomy_single'][ $this->settings['core']['post']['post_type'] ];
		}
		$taxonomy_required = array();
		if ( ! empty( $this->settings['core']['post']['taxonomy_required'][ $this->settings['core']['post']['post_type'] ] ) ) {
			$taxonomy_required = $this->settings['core']['post']['taxonomy_required'][ $this->settings['core']['post']['post_type'] ];
		}
		$bio = false;
		$bio_title = '';
		$bio_value = $submission_data['bio'];
		if ( ! is_user_logged_in() && true == $this->settings['core']['post']['enabled'] && true == $this->settings['core']['post']['bio'] ) {
			$bio = true;
			$bio_title = $this->settings['core']['post']['bio_title'];
		}
		if ( 'rich' == $element_data['settings']['editor_type'] ) {
			$trumbowyg = array(
				'autogrow' => true,
				'semantic' => true,
				'resetCss' => true,
			);
		}
		$params = array( $name_prefix, $submission_data['value'], $element_data['settings']['placeholder'], $trumbowyg, $post_title, $post_title_label, $taxonomy_list, $taxonomy_singles, $taxonomy_required, $submission_data['taxonomy'], $bio, $bio_title );

		$this->ui->container( array( array( $this->ui, 'guest_blog' ), $params ), $element_data['title'], $element_data['settings']['icon'], false, true, '', false, $element_data['description'] );
		$this->ui->clear();
		$this->ui->column_tail();
	}

	public function build_password( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_password', $element_data['tooltip'] );
		// Decrypt password if needed
		if ( isset( $submission_data['encrypted'] ) && $submission_data['encrypted'] && '' !== $submission_data['value'] ) {
			$submission_data['value'] = $this->decrypt_password_field( $submission_data['value'] );
		}
		// Hide it if needed
		$classes = [];
		$needs_reg_mods = $this->is_element_of_reg_field( 'pinfo', $key, 'password_id' );
		if ( $element_data['settings']['hide_in_summary'] || $needs_reg_mods ) {
			$classes[] = 'eform-password-noreveal';
		}
		$params = array( $name_prefix, $submission_data['value'], $element_data['settings']['placeholder'], 'normal', $element_data['settings']['confirm_duplicate'] == true ? __( 'Please confirm', 'ipt_fsqm' ) : false, $classes, $element_data['validation'] );
		$callback = array( array( $this->ui, 'password' ), $params );
		$this->ui->question_container( $name_prefix . '[value]', $element_data['title'], $element_data['subtitle'], $callback, $element_data['validation']['required'], false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_p_radio( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_p_radio', $element_data['tooltip'] );
		$this->make_mcqs( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure );
		$this->ui->column_tail();
	}

	public function build_p_checkbox( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_p_checkbox', $element_data['tooltip'] );
		$this->make_mcqs( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure );
		$this->ui->column_tail();
	}

	public function build_p_select( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_p_select', $element_data['tooltip'] );
		$this->make_mcqs( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure );
		$this->ui->column_tail();
	}

	public function build_s_checkbox( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$classes = array( 'ipt_fsqm_container_s_checkbox' );
		if ( true == $element_data['settings']['centered'] ) {
			$classes[] = 'column-centered';
		}
		$this->ui->column_head( $id, 'full', true, $classes, $element_data['tooltip'] );
		$item = array(
			'label' => $element_data['title'],
			'value' => '1'
		);
		$checked = false;
		if ( $this->data_id == null ) {
			$checked = $element_data['settings']['checked'];
		} else {
			$checked = $submission_data['value'];
		}

		$this->ui->checkbox( $name_prefix . '[value]', $item, $checked, $element_data['validation'], false, false, $element_data['settings']['icon'] );
		$this->ui->column_tail();
	}

	public function build_address( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_address', $element_data['tooltip'] );
		$placeholders = array(
			'recipient' => __( 'Recipient', 'ipt_fsqm' ),
			'line_one' => __( 'Address line one', 'ipt_fsqm' ),
			'line_two' => __( 'Address line two', 'ipt_fsqm' ),
			'line_three' => __( 'Address line three', 'ipt_fsqm' ),
			'country' => __( 'Country', 'ipt_fsqm' ),
			'province' => __( 'Province', 'ipt_fsqm' ),
			'zip' => __( 'Postal Code', 'ipt_fsqm' ),
		);
		$placeholders = wp_parse_args( $element_data['settings'], $placeholders );
		if ( null == $this->data_id && '' != $element_data['settings']['preset_country'] ) {
			$country_list = IPT_FSQM_Form_Elements_Static::get_country_list();
			$submission_data['values']['country'] = $country_list[ $element_data['settings']['preset_country'] ];
		}

		$ui_params = array(
			$name_prefix . '[values]',
			$submission_data['values'],
			$placeholders,
			$element_data['validation'],
			$element_data['settings']['preset_country'],
			$element_data['settings']['hide_icon'],
			$element_data['settings']['native_placeholder']
		);

		$this->ui->question_container( $name_prefix, $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'address' ), $ui_params ), $element_data['validation']['required'], false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_keypad( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_keypad', $element_data['tooltip'] );
		$params = array( $name_prefix . '[value]', $submission_data['value'], array( 'layout' => $element_data['settings']['type'] ), $element_data['settings']['placeholder'], $element_data['settings']['mask'], $element_data['settings']['multiline'], 'normal', array(), $element_data['validation'] );
		$this->ui->question_container( $name_prefix . '[value]', $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'keypad' ), $params ), $element_data['validation']['required'], false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_datetime( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_datetime', $element_data['tooltip'] );
		$value = $submission_data['value'];
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
		$current_picker_timestamp = ( $value == '' ) ? current_time( 'timestamp' ) : strtotime( $value );
		if ( ( $element_data['settings']['show_current'] == true && $this->data_id == null ) || ( $value != '' && $current_picker_timestamp != false ) ) {
			switch ( $element_data['settings']['type'] ) {
			case 'date' :
				$value = date( $date_formats[$element_data['settings']['date_format']], $current_picker_timestamp );
				break;
			case 'time' :
				$value = date( $time_formats[$element_data['settings']['time_format']], $current_picker_timestamp );
				break;
			case 'datetime' :
				$value = date( $date_formats[$element_data['settings']['date_format']] . ' ' . $time_formats[$element_data['settings']['time_format']], $current_picker_timestamp );
				break;
			}
		}

		// Modify the past & future for date range
		$matches = array();
		$now = current_time( 'timestamp' );
		$data_attr = array();
		if ( preg_match( '/^now\s?(\+|\-)\s?(\d+)/i', $element_data['validation']['filters']['past'], $matches ) ) {
			$validation_date = date( 'Y-m-d', strtotime( $matches[1] . $matches[2] . ' days', $now ) );
			$element_data['validation']['filters']['past'] = $validation_date;
			$data_attr['past'] = $validation_date;
		} else if ( '' != $element_data['validation']['filters']['past'] && 'NOW' != strtoupper( $element_data['validation']['filters']['past'] ) ) {
			if ( preg_match( '/^O([0-9]+)/i', $element_data['validation']['filters']['past'], $matches ) ) {
				$data_attr['past'] = 'ipt_fsqm_form_' . $this->form_id . '_pinfo_' . $matches[1] . '_value';
				$element_data['validation']['filters']['past'] = 'ipt_fsqm_form_' . $this->form_id . '_pinfo_' . $matches[1] . '_value';
			}
		} else if ( 'NOW' == strtoupper( $element_data['validation']['filters']['past'] ) ) {
			$element_data['validation']['filters']['past'] = date( 'Y-m-d', $now );
			$data_attr['past'] = date( 'Y-m-d', $now );
		}
		if ( preg_match( '/^now\s?(\+|\-)\s?(\d+)/i', $element_data['validation']['filters']['future'], $matches ) ) {
			// Get the values
			$validation_date = date( 'Y-m-d', strtotime( $matches[1] . $matches[2] . ' days', $now ) );
			$element_data['validation']['filters']['future'] = $validation_date;
			$data_attr['future'] = $validation_date;
		} else if ( '' != $element_data['validation']['filters']['future'] && 'NOW' != strtoupper( $element_data['validation']['filters']['future'] ) ) {
			if ( preg_match( '/^O([0-9]+)/i', $element_data['validation']['filters']['future'], $matches ) ) {
				$data_attr['future'] = 'ipt_fsqm_form_' . $this->form_id . '_pinfo_' . $matches[1] . '_value';
				$element_data['validation']['filters']['future'] = 'ipt_fsqm_form_' . $this->form_id . '_pinfo_' . $matches[1] . '_value';
			}
		} else if ( 'NOW' == strtoupper( $element_data['validation']['filters']['future'] ) ) {
			$element_data['validation']['filters']['future'] = date( 'Y-m-d', $now );
			$data_attr['future'] = date( 'Y-m-d', $now );
		}

		$data_attr['year_range'] = 50;
		if ( ! empty( $element_data['settings']['year_range'] ) ) {
			$data_attr['year_range'] = absint( $element_data['settings']['year_range'] );
		}

		$params = array( $name_prefix . '[value]', $value, $element_data['settings']['type'], 'normal', array(), $element_data['validation'], $element_data['settings']['date_format'], $element_data['settings']['time_format'], $element_data['settings']['placeholder'], $data_attr, $element_data['settings']['hide_icon'] );
		$this->ui->question_container( $name_prefix . '[value]', $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'datetime' ), $params ), $element_data['validation']['required'], false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_p_sorting( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$this->ui->column_head( $id, 'full', true, 'ipt_fsqm_container_p_sorting', $element_data['tooltip'] );
		$params = array( $name_prefix . '[order][]', $element_data['settings']['options'], $submission_data['order'], false );
		$this->ui->question_container( $name_prefix, $element_data['title'], $element_data['subtitle'], array( array( $this->ui, 'sortables' ), $params ), true, false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
		$this->ui->column_tail();
	}

	public function build_hidden( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		// Container ID
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;

		// Set the value first
		$value = $submission_data['value'];
		$classes = array( 'ipt-eform-hidden-field' );
		$classes[] = 'ipt-eform-hidden-field-' . $element_data['settings']['parameter'];

		// If it is a new submission, then change accordingly
		if ( null == $this->data_id ) {
			// value key
			$parameter = $element_data['settings']['parameter'];
			// Check for the type of data we need to process
			switch ( $element_data['settings']['type'] ) {
				case 'url':
					$value = IPT_FSQM_Form_Elements_Static::get_request_parameter( $parameter, $element_data['settings']['default'] );
					break;
				case 'meta':
					$value = IPT_FSQM_Form_Elements_Static::get_user_metadata( $parameter, $element_data['settings']['default'] );
					break;
				case 'logged_in':
					if ( is_user_logged_in() ) {
						$value = $element_data['settings']['default'];
					}
					break;
				case 'postmeta':
					$value = IPT_FSQM_Form_Elements_Static::get_post_metadata( $parameter, $element_data['settings']['default'] );
					break;
				default:
				case 'prefedined':
					$value = $element_data['settings']['default'];
					break;
			}
		}

		// All set, now print inside a conditional container
		// Also check if doing admin, then let admin edit the stuff
		?>
<div class="ipt_uif_conditional ipt_fsqm_container_hidden" id="<?php echo $id; ?>">
	<?php if ( $this->doing_admin ) : ?>
		<?php $params = array( $name_prefix . '[value]', $submission_data['value'], '' ); ?>
		<?php /* translators: Edit message for hidden field */ ?>
		<?php $this->ui->question_container( $name_prefix . '[value]', sprintf( __( 'Edit Hidden Element: %1$s', 'ipt_fsqm' ), $element_data['title'] ), $element_data['subtitle'], array( array( $this->ui, 'textarea' ), $params ), false, false, false ); ?>
	<?php else : ?>
		<?php $this->ui->hidden_input( $name_prefix . '[value]', strip_tags( $value ), $classes, true ); ?>
	<?php endif; ?>
</div>
		<?php
	}

	public function build_repeatable( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$id = 'ipt_fsqm_form_' . $this->form_id . '_' . $element_structure['m_type'] . '_' . $key;
		$classes = array( 'ipt_fsqm_container_sda_list' );
		if ( true == $element_data['settings']['centered'] ) {
			$classes[] = 'column-centered-heading';
		}
		$this->ui->column_head( $id, 'full', true, $classes );

		// Create the parameters
		$sda_columns = array();
		$sda_data = array();
		$sda_items = array();
		$max_key = 0;

		// Data and columns
		foreach ( $element_data['settings']['group'] as $g_key => $group ) {
			$new_column = array(
				'label' => $group['title'],
				'size' => $group['column'],
				'required' => isset( $group['required'] ) ? true : false,
				'clear' => isset( $group['clear'] ) ? true : false,
			);
			$group_name_prefix = $name_prefix . '[values][__SDAKEY__][' . $g_key . ']';

			switch ( $group['type'] ) {
				case 'radio' :
					$new_column['type'] = 'radios';
					$sda_data[] = array( $group_name_prefix, $this->formulate_sda_items( $group['options'] ), array(), array( 'required' => isset( $group['required'] ) ? true : false ), 'random' );
					break;
				case 'checkbox' :
					$new_column['type'] = 'checkboxes';
					$attributes = $this->formulate_sda_attributes( $group['attr'] );
					$sda_data[] = array( $group_name_prefix . '[]', $this->formulate_sda_items( $group['options'] ), array(), array( 'required' => isset( $group['required'] ) ? true : false, 'filters' => $attributes ), 'random' );
					break;
				case 'select' :
				case 'select_multiple' :
					$new_column['type'] = 'select';
					$select_options = $this->formulate_sda_items( $group['options'] );
					$e_label = '';
					if ( '' == $select_options[0]['value'] ) {
						$e_label = $select_options[0]['label'];
						if ( 'select_multiple' == $group['type'] ) {
							array_shift( $select_options );
						}
					}
					$sda_data[] = array( $group_name_prefix . '[]', $select_options, array(), array( 'required' => isset( $group['required'] ) ? true : false ), false, true, false, ( 'select_multiple' == $group['type'] ? true : false ), $e_label );
					break;
				case 'text' :
				case 'phone' :
				case 'url' :
				case 'email' :
				case 'number' :
				case 'integer' :
				case 'personName' :
					$new_column['type'] = 'text';
					$attributes = $this->formulate_sda_attributes( $group['attr'] );
					$validation = array(
						'required' => isset( $group['required'] ) ? true : false,
						'filters' => $attributes,
					);
					$validation['filters']['type'] = ( 'text' == $group['type'] ) ? 'all' : $group['type'];
					$icon = 'pen';
					if ( 'phone' == $group['type'] ) {
						$icon = 'mobile';
					} else if ( 'url' == $group['type'] ) {
						$icon = 'link';
					} else if ( 'email' == $group['type'] ) {
						$icon = 'envelope';
					} else if ( 'number' == $group['type'] ) {
						$icon = 'hashtag';
					} else if ( 'integer' == $group['type'] ) {
						$icon = 'hashtag';
					} else if ( 'personName' == $group['type'] ) {
						$icon = 'user';
					}
					if ( false == $element_data['settings']['show_icons'] ) {
						$icon = 'none';
					}
					$sda_data[] = array( $group_name_prefix, '', $group['options'], $icon, 'normal', array(), $validation );
					break;
				case 'date' :
				case 'datetime' :
				case 'time' :
					$new_column['type'] = 'datetime';
					$attributes = $this->formulate_sda_attributes( $group['attr'] );
					$validation = array(
						'required' => isset( $group['required'] ) ? true : false,
						'filters' => $attributes,
					);
					$sda_data[] = array( $group_name_prefix, '', $group['type'], 'normal', array(), $validation, 'yy-mm-dd', 'HH:mm:ss', $group['options'] );
					break;
				case 'password' :
					$new_column['type'] = 'password_simple';
					$validation = array(
						'required' => isset( $group['required'] ) ? true : false,
					);
					$sda_data[] = array( $group_name_prefix, '', $group['options'], 'normal', array(), $validation );
					break;
				case 'textarea' :
					$new_column['type'] = 'textarea';
					$attributes = $this->formulate_sda_attributes( $group['attr'] );
					$validation = array(
						'required' => isset( $group['required'] ) ? true : false,
						'filters' => $attributes,
					);
					$icon = 'pen';
					if ( false == $element_data['settings']['show_icons'] ) {
						$icon = 'none';
					}
					$sda_data[] = array( $group_name_prefix, '', $group['options'], 'normal', array(), $validation, false, false, $icon );
					break;
			}
			$sda_columns[] = $new_column;
		}

		// Populate items
		foreach ( $submission_data['values'] as $i_key => $items ) {
			$i = 0;
			$inner_item = array();
			foreach ( $element_data['settings']['group'] as $g_key => $group ) {
				// Start with empty data
				$item = $sda_data[ $i ];
				// Replace the name
				$item[0] = str_replace( '__SDAKEY__', $i_key, $item[0] );

				// Now insert the value
				// Or leave empty if not set
				if ( isset( $items[ $g_key ] ) ) {
					switch ( $group['type'] ) {
						case 'radio' :
							$item[2] = (string) $items[ $g_key ];
							break;
						case 'checkbox' :
						case 'select' :
						case 'select_multiple' :
							$item[2] = (array) $items[ $g_key ];
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
						case 'date' :
						case 'datetime' :
						case 'time' :
							$item[1] = (string) $items[ $g_key ];
							break;
					}
				}
				$i++;
				$inner_item[] = $item;
			}
			$sda_items[ $i_key ] = $inner_item;
		}

		// If minimum required and sda is empty
		$key = 0;
		if ( null == $this->data_id && $element_data['settings']['num'] > 0 && count( $sda_items ) < $element_data['settings']['num'] ) {
			$diff = ( $element_data['settings']['num'] - count( $sda_items ) );
			for ( $i = 0; $i < $diff; $i++ ) {
				$new_item = $sda_data;
				foreach ( $new_item as $n_key => $nitem ) {
					$new_item[ $n_key ][0] = str_replace( '__SDAKEY__', $key, $nitem[0] );
				}
				$sda_items[ $key ] = $new_item;
				$key++;
			}
		}

		// Get maximum key
		if ( ! empty( $sda_items ) ) {
			$max_key = max( array_keys( $sda_items ) );
		}

		// Parameter array
		$params = array(
			array(
				'columns' => $sda_columns,
				'labels' => array(
					'add' => $element_data['settings']['button'],
				),
				'features' => array(
					'draggable' => $element_data['settings']['sortable'],
					'addable' => $element_data['settings']['deletable'],
					'max' => $element_data['settings']['max'],
					'min' => $element_data['settings']['min'],
					'hide_label' => $element_data['settings']['hide_label'],
					'center_content' => $element_data['settings']['centered'],
				),
			),
			$sda_items,
			$sda_data,
			$max_key,
			$id . '_sda_container',
		);
		$container_classes = array( 'ipt-eform-repeatable-container' );
		if ( false == $element_data['settings']['show_icons'] ) {
			$container_classes[] = 'eform-repeatable-container-noicon';
		}
		$this->ui->container( array( array( $this->ui, 'sda_list' ), $params ), $element_data['title'], $element_data['settings']['icon'], false, true, '', false, '', $container_classes );

		$this->ui->column_tail();
	}


	/*==========================================================================
	 * Internal HTML Elements
	 * Just a few shortcuts
	 *========================================================================*/
	public function make_mcqs( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context = null, $score = null ) {
		$items = array();
		foreach ( $element_data['settings']['options'] as $o_key => $option ) {
			if ( ! isset( $option['num'] ) ) {
				$option['num'] = '';
			}
			$items[] = array(
				'label' => $option['label'],
				'value' => (string) $o_key,
				'data' => array(
					'num' => $option['num'],
				),
			);
		}
		if ( isset( $element_data['settings']['shuffle'] ) && true == $element_data['settings']['shuffle'] ) {
			shuffle( $items );
		}
		$conditional = false;
		if ( true == $element_data['settings']['others'] && '' != $element_data['settings']['o_label'] ) {
			$items[] = array(
				'label' => $element_data['settings']['o_label'],
				'value' => 'others',
				'data' => array(
					'condid' => $this->generate_id_from_name( $name_prefix ) . '_others_wrap',
				),
			);
			$conditional = true;
		}

		// Check for defaults
		if ( null == $this->data_id && empty( $submission_data['options'] ) ) {
			$default = array();
			// Loop through all options
			foreach ( $element_data['settings']['options'] as $o_key => $option ) {
				if ( isset( $option['default'] ) && true == $option['default'] ) {
					$default[] = "$o_key";
				}
			}

			// Check for URL & User Meta
			// value key
			$parameter = $element_data['settings']['parameter'];
			$elm_options = array();
			// Make the options
			foreach ( $element_data['settings']['options'] as $o_key => $option ) {
				$elm_options[ "$o_key" ] = $option['label'];
			}
			// Check for the type of data we need to process
			switch ( $element_data['settings']['type'] ) {
				case 'url':
					$default = IPT_FSQM_Form_Elements_Static::get_request_parameter_for_mcq( $parameter, $default, $elm_options );
					break;
				case 'meta':
					$default = IPT_FSQM_Form_Elements_Static::get_user_metavalues_for_mcq( $parameter, $default, $elm_options );
					break;
			}
			// Set the value
			$submission_data['options'] = $default;
		}

		$param = array( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $items, $conditional );
		$this->ui->question_container( $name_prefix, $element_data['title'], $element_data['subtitle'], array( array( $this, 'make_mcqs_conditional_check' ), $param ), $element_data['validation']['required'], false, $element_data['settings']['vertical'], $element_data['description'], array(), array(), $element_data['settings']['hidden_label'], $element_data['settings']['centered'] );
	}

	public function make_mcqs_conditional_check( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $items, $conditional ) {
		$name = $name_prefix . '[options][]';
		$button_type = isset( $element_data['settings']['button_type'] )
			? $element_data['settings']['button_type']
			: false;
		switch ( $element_data['type'] ) {
		case 'checkbox' :
		case 'p_checkbox' :
			$this->ui->checkboxes( $name, $items, $submission_data['options'], $element_data['validation'], $element_data['settings']['columns'], $conditional, false, $element_data['settings']['icon'], $button_type );
			break;
		case 'radio' :
		case 'p_radio' :
			$this->ui->radios( $name, $items, $submission_data['options'], $element_data['validation'], $element_data['settings']['columns'], $conditional, false, $element_data['settings']['icon'], $button_type );
			break;
		case 'select' :
		case 'p_select' :
			$multiple = ( isset( $element_data['settings']['multiple'] ) ? $element_data['settings']['multiple'] : false );
			if ( $element_data['settings']['e_label'] !== '' && ! $multiple ) {
				array_unshift( $items, array(
					'label' => $element_data['settings']['e_label'],
					'value' => '',
				) );
			}
			$this->ui->select(
				$name,
				$items,
				$submission_data['options'],
				$element_data['validation'],
				$conditional,
				true,
				false,
				$multiple,
				$element_data['settings']['e_label'],
				! $element_data['settings']['show_search'],
				$element_data['settings']['use_native_select']
			);
			break;
		}

		if ( $conditional ) {
			echo '<div class="ipt_uif_box ipt_uif_question_others" id="' . esc_attr( $this->generate_id_from_name( $name_prefix ) . '_others_wrap' ) . '">';
			$this->ui->text( $name_prefix . '[others]', $submission_data['others'], __( 'Write here', 'ipt_fsqm' ), 'pen', 'normal', array(), array( 'required' => true ) );
			echo '</div>';
		}
	}

	public function tamper_protection( $element_data ) {
		$name_prefix = 'ipt_fsqm_form_' . $this->form_id . '[' . $element_data['m_type'] . '][' . $element_data['key'] . ']';
?>
<input type="hidden" data-sayt-exclude name="<?php echo $name_prefix; ?>[m_type]" id="<?php echo $this->ui->generate_id_from_name( $name_prefix . '[m_type]' ); ?>" value="<?php echo $element_data['m_type']; ?>" class="ipt_fsqm_hf_m_type" />
<input type="hidden" data-sayt-exclude name="<?php echo $name_prefix; ?>[type]" id="<?php echo $this->ui->generate_id_from_name( $name_prefix . '[type]' ); ?>" value="<?php echo $element_data['type']; ?>" class="ipt_fsqm_hf_type" />
		<?php
	}


	public function material_options( $form ) {
		// Get settings
		$op = $this->settings['theme']['material'];

		// Set the fonts
		if ( true !== $this->settings['theme']['style']['custom_font'] ) {
			wp_enqueue_style( 'ipt-eform-material-font', 'https://fonts.googleapis.com/css?family=Noto+Sans|Roboto:300,400,400i,700', array(), IPT_FSQM_Loader::$version );
		}

		// Get selector
		$selector = '#ipt_fsqm_form_wrap_' . $this->form_id;

		// Add unit to width if unit is not present
		$max_width = $op['width'];
		if ( preg_match( '/^([0-9]+)$/', $max_width ) ) {
			$max_width = $max_width . 'px';
		}

		// Printout custom CSS
		?>
<style type="text/css">
	<?php echo $selector; ?> .ipt_fsqm_form_logo img,
	<?php echo $selector; ?> .ipt-eform-width-restrain,
	<?php echo $selector; ?> .ipt_fsqm_form_message_restore,
	<?php echo $selector; ?> .ipt_fsqm_form_message_success,
	<?php echo $selector; ?> .ipt_fsqm_form_message_error,
	<?php echo $selector; ?> .ipt_fsqm_form_message_process,
	<?php echo $selector; ?> .ipt_fsqm_form_validation_error,
	<?php echo $selector; ?> .eform-ui-estimator {
		max-width: <?php echo $max_width; ?>;
		min-width: 240px;
	}

	<?php if ( true == $op['bg']['enabled'] ) :; ?>
		<?php echo $selector; ?> .ipt-eform-content {
			background-image: <?php echo ( '' == $op['bg']['background-image'] ? 'none' : 'url("' . $op['bg']['background-image'] . '")' ); ?>;
			background-position: <?php echo $op['bg']['background-position']; ?>;
			background-size: <?php echo $op['bg']['background-size']; ?>;
			background-repeat: <?php echo $op['bg']['background-repeat']; ?>;
			background-origin: <?php echo $op['bg']['background-origin']; ?>;
			background-clip: <?php echo $op['bg']['background-clip']; ?>;
			background-attachment: <?php echo $op['bg']['background-attachment']; ?>;
		}
	<?php endif; ?>
/**/
</style>
		<?php
	}

	public function mt_alternate_pb( $classes ) {
		$classes[] = 'eform-material-alternate-pb';
		return $classes;
	}

	public function formulate_sda_items( $string ) {
		return IPT_FSQM_Form_Elements_Static::formulate_sda_items( $string );
	}

	public function formulate_sda_attributes( $string ) {
		return IPT_FSQM_Form_Elements_Static::formulate_sda_attributes( $string );
	}

	public function validation_mods( $validation ) {
		if ( isset( $validation['equals'] ) && ! empty( $validation['equals'] ) ) {
			$elem_breakdowns = array();
			if ( preg_match( '/(F|O)([0-9]+)/i', $validation['equals'], $elem_breakdowns ) ) {
				$m_type = ( 'F' == strtoupper( $elem_breakdowns[1] ) ? 'freetype' : 'pinfo' );
				$e_key = $elem_breakdowns[2];
				$validation['equals'] = 'ipt_fsqm_form_' . $this->form_id . '_' . $m_type . '_' . $e_key . '_value';
			}
		}
		return $validation;
	}
}
