<?php
/**
 * Class for loading the core shortcode functionality
 *
 * It takes care of the front-end output of the following shortcodes
 *
 * 1. Publish Form
 * 2. Publish Trends
 * 3. Trackback Page
 * 4. User Portal Page
 * 5. Popup forms
 *
 * Since these are parts of the core shortcodes written within the plugin, we
 * don't need to handle the tinyMCE enqueue stuff from here. Those are handled
 * by the IPT_EForm_Shortcodes_TinyMCE class
 *
 * @see IPT_EForm_Shortcodes_TinyMCE
 *
 * @package    eForm - WordPress Form Builder
 * @subpackage Shortcodes\Core
 * @author     Swashata Ghosh ( swashata@iptms.co )
 */
class IPT_EForm_Core_Shortcodes {
	/**
	 * Singleton instance variable
	 */
	private static $instance = null;

	/**
	 * Get the instance of this singleton class
	 *
	 * @return     IPT_EForm_Core_Shortcodes  The instance of the class
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new IPT_EForm_Core_Shortcodes();
		}
		return self::$instance;
	}

	/**
	 * The consturctor
	 *
	 * The access is made private so that the class can be singleton
	 */
	private function __construct() {
		// Core shortcodes
		add_shortcode( 'ipt_fsqm_form', array( $this, 'ipt_fsqm_form_cb' ) );
		add_shortcode( 'ipt_fsqm_trackback', array( $this, 'ipt_fsqm_track_cb' ) );
		add_shortcode( 'ipt_fsqm_utrackback', array( $this, 'ipt_fsqm_utrack_cb' ) );
		add_shortcode( 'ipt_fsqm_trends', array( $this, 'ipt_fsqm_trends_cb' ) );
		add_shortcode( 'ipt_fsqm_popup', array( $this, 'ipt_fsqm_popup_cb' ) );
	}

	/*==========================================================================
	 * Shortcode output callbacks
	 *========================================================================*/

	/**
	 * Callback for ipt_fsqm_form shortcode
	 *
	 * @param      array   $args     The arguments
	 * @param      string  $content  The content
	 * @param      string  $context  The context
	 *
	 * @return     string  The HTML output of the shortcode
	 */
	public static function ipt_fsqm_popup_cb( $args, $content = 'Sample Form', $context = '' ) {
		$atts = shortcode_atts( array(
			'form_id' => '1',
			'pos' => 'r', // r => right, br => bottom right, bc => bottom center, bl => bottom left, l => left
			'style' => 'rect', // rect => rectangular, circ => circular
			'header' => '%FORM%', // form name
			'subtitle' => '', // Some subtitle
			'icon' => 'fa fa-file-text', // icon class needs to be supplied by vendor
			'width' => '600', // Initial popup width in pixels
			'color' => '#ffffff',
			'bgcolor' => '#3C609E',
		), $args );
		$config = array(
			'label' => $content,
			'color' => $atts['color'],
			'bgcolor' => $atts['bgcolor'],
			'position' => $atts['pos'],
			'style' => $atts['style'],
			'header' => $atts['header'],
			'subtitle' => $atts['subtitle'],
			'icon' => $atts['icon'],
			'width' => $atts['width'],
		);
		$popup = new EForm_Popup_Helper( $atts['form_id'], $config );
		// Get output
		ob_start();
		$popup->init_js();
		return ob_get_clean();
	}

	/**
	 * Callback function for ipt_fsqm_trends shortcode
	 *
	 * @param      array  $atts     The atts
	 * @param      string  $content  The content
	 * @param      string  $context  The context
	 *
	 * @return     string  HTML output of the shortcode
	 */
	public static function ipt_fsqm_trends_cb( $atts, $content = null, $context = '' ) {
		global $wpdb, $ipt_fsqm_info;

		// Load the default shortcode atts
		$shortcode_config = shortcode_atts( array(
			'form_id' => '0',
			'load' => '1',
			'title' => __( 'Trends', 'ipt_fsqm' ),
			'mcq_ids' => '',
			'mcq_config' => '',
			'freetype_ids' => '',
			'pinfo_ids' => '',
			'pinfo_config' => '',
			'filters' => '',
			'data' => '',
			'appearance' => '',
		), $atts );

		// Backward compatibility
		if ( 'feedback_trend' == $context ) {
			$shortcode_config['form_id'] = isset( $atts['id'] ) ? (int) $atts['id'] : 0;
		}

		// Create the default set of filters
		// and apply if not present in shortcode
		$default_filters = array(
			'users' => 'all',
			'urlTracks' => 'all',
			'mk' => '',
			'mv' => '',
			'smin' => '',
			'smax' => '',
			'dtmin' => '',
			'dtmax' => '',
		);
		if ( '' !== $shortcode_config['filters'] ) {
			$shortcode_config['filters'] = json_decode( $shortcode_config['filters'], true );
			if ( is_null( $shortcode_config['filters'] ) ) {
				$shortcode_config['filters'] = $default_filters;
			}
		} else {
			$shortcode_config['filters'] = $default_filters;
		}

		// Create a front instance
		// For creating the frontend container
		$front = new IPT_FSQM_Form_Elements_Front( null, $shortcode_config['form_id'] );

		// Create a util instance
		// For creating the report output
		$utils = new IPT_FSQM_Form_Elements_Utilities( $shortcode_config['form_id'], $front->ui );

		// Now process and make compatible variable for passing through util class
		// Create settings
		$settings = array(
			'form_id' => $shortcode_config['form_id'],
			'report' => array(),
			'load' => $shortcode_config['load'],
		);

		// Process MCQs
		$mcqs = array();
		if ( isset( $shortcode_config['mcq_ids'] ) && '' !== $shortcode_config['mcq_ids'] ) {
			// Add to the reports
			$settings['report'][] = 'mcq';

			// Generate mcq ids
			if ( 'all' == $shortcode_config['mcq_ids'] ) {
				$mcqs = array_keys( $utils->mcq );
			} else {
				$mcqs = wp_parse_id_list( $shortcode_config['mcq_ids'] );
			}
		}

		// Process Freetypes
		$freetypes = array();
		if ( isset( $shortcode_config['freetype_ids'] ) && '' !== $shortcode_config['freetype_ids'] ) {
			// Add to the reports
			$settings['report'][] = 'freetype';

			// Generate freetype ids
			if ( 'all' == $shortcode_config['freetype_ids'] ) {
				$freetypes = array_keys( $utils->freetype );
			} else {
				$freetypes = wp_parse_id_list( $shortcode_config['freetype_ids'] );
			}
		}

		// Process PInfos
		$pinfos = array();
		if ( isset( $shortcode_config['pinfo_ids'] ) && '' !== $shortcode_config['pinfo_ids'] ) {
			// Add to the reports
			$settings['report'][] = 'pinfo';

			// Generate pinfo ids
			if ( 'all' == $shortcode_config['pinfo_ids'] ) {
				$pinfos = array_keys( $utils->pinfo );
			} else {
				$pinfos = wp_parse_id_list( $shortcode_config['pinfo_ids'] );
			}
		}

		// Get the default chart types and toggles
		$mcqs_in_report = array();
		foreach ( $mcqs as $m_key ) {
			if ( isset( $front->mcq[ $m_key ] ) ) {
				$mcqs_in_report[ $m_key ] = array(
					'type' => $front->mcq[ $m_key ]['type'],
				);
			}
		}
		$pinfos_in_report = array();
		foreach ( $pinfos as $p_key ) {
			if ( isset( $front->pinfo[ $p_key ] ) ) {
				$pinfos_in_report[ $p_key ] = array(
					'type' => $front->pinfo[ $p_key ]['type'],
				);
			}
		}
		$default_chart_n_toggle = self::get_default_chart_n_toggle( $mcqs_in_report, $pinfos_in_report );

		// Deserialize the JSONs of chartconfigs
		if ( '' !== $shortcode_config['mcq_config'] ) {
			$shortcode_config['mcq_config'] = json_decode( $shortcode_config['mcq_config'], true );
			if ( is_null( $shortcode_config['mcq_config'] ) ) {
				$shortcode_config['mcq_config'] = $default_chart_n_toggle['mcq'];
			}
		} else {
			// Backward compatibility
			$shortcode_config['mcq_config'] = $default_chart_n_toggle['mcq'];
		}

		if ( '' !== $shortcode_config['pinfo_config'] ) {
			$shortcode_config['pinfo_config'] = json_decode( $shortcode_config['pinfo_config'], true );
			if ( is_null( $shortcode_config['pinfo_config'] ) ) {
				$shortcode_config['pinfo_config'] = $default_chart_n_toggle['pinfo'];
			}
		} else {
			$shortcode_config['pinfo_config'] = $default_chart_n_toggle['pinfo'];
		}

		// Appearance config
		$rappearance = json_decode( $shortcode_config['appearance'], true );
		if ( is_null( $rappearance ) ) {
			$rappearance = array();
		}
		$rappearance = wp_parse_args( $rappearance, array(
			'block' => true,
			'heading' => true,
			'description' => true,
			'header' => true,
			'border' => true,
			'material' => false,
			'print' => false,
		) );

		$appearance = array(
			'wrap' => ( true == $rappearance['block'] ) ? true : false,
			'heading' => ( true == $rappearance['heading'] ) ? true : false,
			'description' => ( true == $rappearance['description'] ) ? true : false,
			'theader' => ( true == $rappearance['header'] ) ? true : false,
			'tborder' => ( true == $rappearance['border'] ) ? true : false,
			'material' => ( true == $rappearance['material'] ) ? true : false,
			'print' => ( true == $rappearance['print'] ) ? true : false,
		);

		// Data config
		$data = json_decode( $shortcode_config['data'], true );
		if ( is_null( $data ) ) {
			$data = array(
				'data' => true,
				'others' => false,
				'names' => false,
				'date' => false,
			);
		}

		// Chart meta config
		$cmeta = array(
			'mcq' => $shortcode_config['mcq_config'],
			'pinfo' => $shortcode_config['pinfo_config'],
		);

		// Compat the filters
		$filters = array(
			'user_id' => array(),
			'url_track' => array(),
			'meta' => $shortcode_config['filters']['mk'],
			'mvalue' => $shortcode_config['filters']['mv'],
			'score' => array(
				'min' => $shortcode_config['filters']['smin'],
				'max' => $shortcode_config['filters']['smax'],
			),
			'custom_date' => false,
			'custom_date_start' => $shortcode_config['filters']['dtmin'],
			'custom_date_end' => $shortcode_config['filters']['dtmax'],
		);

		// Add user ids
		if ( 'all' == $shortcode_config['filters']['users'] ) {
			$filters['user_id'][] = '';
		} else {
			$filters['user_id'] = wp_parse_id_list( $shortcode_config['filters']['users'] );
			if ( empty( $filters['user_id'] ) ) {
				$filters['user_id'] = array( '' );
			}
		}

		// Query table and check the URL Tracks
		if ( 'all' == $shortcode_config['filters']['urlTracks'] ) {
			$filters['url_track'][] = '';
		} else {
			// Get the URL tracks
			$url_tracks = $wpdb->get_col( sprintf( "SELECT url_track FROM {$ipt_fsqm_info['data_table']} WHERE id IN (%s)", esc_sql( $shortcode_config['filters']['urlTracks'] ) ) );

			if ( empty( $url_tracks ) ) {
				$filters['url_track'][] = '';
			} else {
				foreach ( $url_tracks as $ut ) {
					$filters['url_track'][] = $ut;
				}
			}
		}

		// Custom date
		if ( '' !== $shortcode_config['filters']['dtmin'] || '' !== $shortcode_config['filters']['dtmax'] ) {
			$filters['custom_date'] = true;
		}

		ob_start();
		$front->container( array( array( $utils, 'report_generate_report' ), array( $settings, $mcqs, $freetypes, $pinfos, $data['data'], $data['names'], $data['date'], $data['others'], false, $appearance, $cmeta, $filters, $shortcode_config['title'] ) ), true, true );
		$output = ob_get_clean();

		if ( WP_DEBUG !== true ) {
			$output = IPT_FSQM_Minify_HTML::minify( $output );
		}

		return $output;
	}

	/**
	 * Callback function for ipt_fsqm_form shortcode
	 *
	 * @param      arra    $args     The arguments
	 * @param      string  $content  The content
	 *
	 * @return     string  HTML output of the shortcode
	 */
	public static function ipt_fsqm_form_cb( $args, $content = null ) {
		extract( shortcode_atts( array(
			'id' => '1',
			'iframe' => '0',
		), $args ) );
		$form = new IPT_FSQM_Form_Elements_Front( null, $id );
		ob_start();
		if ( $iframe && $iframe !== '' && $iframe !== '0' ) {
			$form->enqueue_iframeresizer();
			$form->print_iframe_html();
		} else {
			$form->show_form();
		}
		$form_output = ob_get_clean();
		if ( WP_DEBUG !== true ) {
			$form_output = IPT_FSQM_Minify_HTML::minify( $form_output );
		}
		return $form_output;
	}

	/**
	 * Callback function for ipt_fsqm_track shortcode
	 *
	 * @param      array  $args     The arguments
	 * @param      string  $content  The content
	 *
	 * @return     string  HTML output of the shortcode
	 */
	public static function ipt_fsqm_track_cb( $args, $content = null ) {
		extract( shortcode_atts( array(
			'label' => __( 'Track Code:', 'ipt_fsqm' ),
			'submit' => __( 'Submit', 'ipt_fsqm' ),
		), $args ) );
		$id = isset( $_GET['id'] ) ? $_GET['id'] : false;
		$action = isset( $_GET['action'] ) ? $_GET['action'] : 'show';
		ob_start();
?>
<?php if ( $id == false ) : ?>
	<form action="" method="get">
		<?php foreach ( $_GET as $k => $v ) : ?>
		<input type="hidden" name="<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $v ); ?>" />
		<?php endforeach; ?>
		<div class="form-group">
			<label for="id"><?php echo $label; ?></label>
			<input type="text" name="id" id="id" value="" class="form-control" placeholder="<?php echo esc_attr( $label ); ?>" />
		</div>
		<button type="submit" class="btn btn-default"><?php echo $submit; ?></button>
	</form>
<?php else : ?>
<?php
	switch ( $action ) {
		default :
		case 'show' :
			IPT_FSQM_Form_Elements_Static::ipt_fsqm_full_preview( IPT_FSQM_Form_Elements_Static::decode_trackback_id( $id ) );
			break;
		case 'edit' :
			IPT_FSQM_Form_Elements_Static::ipt_fsqm_form_edit( IPT_FSQM_Form_Elements_Static::decode_trackback_id( $id ) );
			break;
		case 'payment' :
			$form = new IPT_FSQM_Form_Elements_Front( IPT_FSQM_Form_Elements_Static::decode_trackback_id( $id ) );
			$mode = isset( $_GET['mode'] ) ? $_GET['mode'] : 'retry';
			$form->container( array( array( 'IPT_FSQM_Form_Elements_Static', 'ipt_fsqm_handle_payment_tb' ), array( IPT_FSQM_Form_Elements_Static::decode_trackback_id( $id ), $mode, $form ) ), true );
			break;
	}
?>
<?php endif; ?>
		<?php
		$form_output = ob_get_clean();
		if ( WP_DEBUG !== true ) {
			$form_output = IPT_FSQM_Minify_HTML::minify( $form_output );
		}
		return $form_output;
	}

	/**
	 * Callback function for `[ipt_fsqm_utrack]` shortcode
	 *
	 * @param      array   $args     The arguments
	 * @param      string  $content  The content
	 *
	 * @return     string  HTML output of the shortcode
	 */
	public static function ipt_fsqm_utrack_cb( $args, $content = null ) {
		global $wpdb, $ipt_fsqm_info, $ipt_fsqm_settings;
		$shortcode_settings = shortcode_atts( array(
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
			'filters' => '0',
			'title' => __( 'eForm User Portal', 'ipt_fsqm' ),
			'logout_r' => '',
		), $args );
		extract( $shortcode_settings );
		$content = shortcode_unautop( $content );
		$showscore = (int) $showscore;
		$show_register = (int) $show_register;
		$show_forgot = (int) $show_forgot;

		if ( $content == null ) {
			$content = __( 'Welcome %NAME%. Below is the list of all submissions you have made.', 'ipt_fsqm' );
		}
		$user = wp_get_current_user();
		// We need a $form_element instance for theme management, only the base should do
		$form_element = new IPT_FSQM_Form_Elements_Base();
		$theme_element = $form_element->get_theme_by_id( $theme );
		// Backward compatibility
		$theme = $theme_element['theme_id'];
		// Check the theme and do the UI
		$themes = $form_element->get_available_themes();
		$theme_info = array();
		foreach ( $themes as $the_theme ) {
			if ( isset( $the_theme['ui-class'] ) ) {
				foreach ( array_keys( $the_theme['themes'] ) as $theme_key ) {
					$theme_info[ $theme_key ] = $the_theme['ui-class'];
				}
			}
		}

		if ( isset( $theme_element['ui-class'] ) && class_exists( $theme_element['ui-class'] ) ) {
			$ui = $theme_element[ 'ui-class' ]::instance();
		} else {
			$ui = IPT_Plugin_UIF_Front::instance();
		}
		$redirect = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		// We set the current URL to redirection after logout, if nothing isn't specified
		if ( '' == $logout_r ) {
			$logout_r = $redirect;
		}
		$ui->enqueue();
		$ui->enqueue_select();
		ob_start();
		$ui->ajax_loader( false, '', array(), true, __( 'Please wait', 'ipt_fsqm' ) );
		$ajax_loader = ob_get_clean();
		if ( isset( $theme_element['user_portal_css'] ) && ! empty( $theme_element['user_portal_css'] ) ) {
			wp_enqueue_style( 'eform-up', $theme_element['user_portal_css'], array(), IPT_FSQM_Loader::$version );
		}
		$ui->enqueue_datatables();
		wp_enqueue_script( 'ipt-fsqm-up-script', IPT_FSQM_Loader::$static_location . 'front/js/jquery.ipt-fsqm-user-portal.min.js', array( 'jquery' ), IPT_FSQM_Loader::$version, true );
		wp_localize_script( 'ipt-fsqm-up-script', 'iptFSQMUP', array(
			'location' => plugins_url( '/static/front/', IPT_FSQM_Loader::$abs_file ),
			'version' => IPT_FSQM_Loader::$version,
			'l10n' => array(
				'sEmptyTable' => __( 'No submissions yet!', 'ipt_fsqm' ),
				'sInfo' => __( 'Showing _START_ to _END_ of _TOTAL_ entries', 'ipt_fsqm' ),
				'sInfoEmpty' => __( 'Showing 0 to 0 of 0 entries', 'ipt_fsqm' ),
				'sInfoFiltered' => __( '(filtered from _MAX_ total entries)', 'ipt_fsqm' ),
				/* translators: %s will be replaced by an empty string */
				'sInfoPostFix' => sprintf( _x( '%s', 'sInfoPostFix', 'ipt_fsqm' ), '' ),
				/* translators: For thousands separator inside datatables */
				'sInfoThousands' => _x( ',', 'sInfoThousands', 'ipt_fsqm' ),
				'sLengthMenu' => __( 'Show _MENU_ entries', 'ipt_fsqm' ),
				'sLoadingRecords' => $ajax_loader,
				'sProcessing' => $ajax_loader,
				'sSearch' => '',
				'sSearchPlaceholder' => __( 'Search submissions', 'ipt_fsqm' ),
				'sZeroRecords' => __( 'No matching records found', 'ipt_fsqm' ),
				'oPaginate' => array(
					'sFirst' => __( '<i title="First" class="ipticm ipt-icomoon-first"></i>', 'ipt_fsqm' ),
					'sLast' => __( '<i title="Last" class="ipticm ipt-icomoon-last"></i>', 'ipt_fsqm' ),
					'sNext' => __( '<i title="Next" class="ipticm ipt-icomoon-forward4"></i>', 'ipt_fsqm' ),
					'sPrevious' => __( '<i title="Previous" class="ipticm ipt-icomoon-backward3"></i>', 'ipt_fsqm' ),
				),
				'oAria' => array(
					'sSortAscending' => __( ': activate to sort column ascending', 'ipt_fsqm' ),
					'sSortDescending' => __( ': activate to sort column descending', 'ipt_fsqm' ),
				),
				'filters' => array(
					'form' => __( 'Select form to filter', 'ipt_fsqm' ),
					'category' => __( 'Select category to filter', 'ipt_fsqm' ),
				),
			),
			'ajax' => array(
				'null_response' => __( 'Some error occured on the server.', 'ipt_fsqm' ),
				'ajax_error' => __( 'Error occured while fetching the content.', 'ipt_fsqm' ),
				'advice' => __( 'Please refresh this page to try again.', 'ipt_fsqm' ),
			),
			'allLabel' => __( 'All', 'ipt_fsqm' ),
			'allFilter' => __( 'Show all', 'ipt_fsqm' ),
			'dpPlaceholderf' => __( 'From', 'ipt_fsqm' ),
			'dpPlaceholdert' => __( 'To', 'ipt_fsqm' ),
			'sPlaceholder' => __( 'Search', 'ipt_fsqm' ),
		) );

		do_action( 'ipt_fsqm_form_elements_up_enqueue' );

		if ( ! is_user_logged_in() || ! ($user instanceof WP_User) ) {
			$defaults = array(
				'echo' => true,
				'redirect' => $redirect,
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
			$args = wp_parse_args( $args, apply_filters( 'login_form_defaults', $defaults ) );
			$login_buttons = array();
			$login_buttons[] = array(
				__( 'Login', 'ipt_fsqm' ),
				'wp-submit',
				'small',
				'none',
				'normal',
				array(),
				'submit',
				array(),
				array(),
				'',
				'switch',
			);

			if ( $show_register && get_option( 'users_can_register', false ) ) {
				$login_buttons[] = array(
					__( 'Register', 'ipt_fsqm' ),
					'ipt_fsqm_up_reg',
					'small',
					'none',
					'normal',
					array(),
					'button',
					array(),
					array( 'onclick' => 'javascript:window.location.href="' . wp_registration_url() . '"' ),
					'',
					'user-2',
				);
			}

			if ( $show_forgot ) {
				$login_buttons[] = array(
					__( 'Forgot Password', 'ipt_fsqm' ),
					'ipt_fsqm_up_rpwd',
					'small',
					'none',
					'normal',
					array(),
					'button',
					array(),
					array( 'onclick' => 'javascript:window.location.href="' . wp_lostpassword_url( $redirect ) . '"' ),
					'',
					'info-2',
				);
			}

			$login_buttons = apply_filters( 'ipt_fsqm_up_filter_login_buttons', $login_buttons );
		} else {
			$total_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} WHERE user_id = %d", $user->ID ) );
			$avg_score = $wpdb->get_var( $wpdb->prepare( "SELECT AVG((score/max_score)) FROM {$ipt_fsqm_info['data_table']} WHERE user_id = %d", $user->ID ) );

			$toolbar_buttons = array();
			$toolbar_buttons[] = array(
				__( 'Logout', 'ipt_fsqm' ),
				'ipt_fsqm_up_logout',
				'',
				'none',
				'normal',
				array(),
				'button',
				array(),
				array( 'onclick' => 'javascript:window.location.href="' . wp_logout_url( $logout_r ) . '"' ),
				'',
				'switch',
			);
			$toolbar_buttons = apply_filters( 'ipt_fsqm_up_filter_toolbar', $toolbar_buttons );
		}

		$main_container_style = 'opacity: 0;';
		if ( ! is_user_logged_in() || ! ( $user instanceof WP_User ) ) {
			$main_container_style .= 'max-width: 600px; margin: 0 auto 1em;';
		}
		ob_start();
		?>
<div class="ipt_fsqm_user_portal ipt_uif_front ipt_uif_common eform-override-element-boxy" data-ui-theme="<?php echo esc_attr( json_encode( $theme_element['include'] ) ); ?>" data-ui-theme-id="<?php echo esc_attr( $theme ); ?>" data-settings="<?php echo esc_attr( json_encode( $shortcode_settings ) ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'ipt_fsqm_up_nonce_' . $user->ID ) ); ?>" data-ajaxurl="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>">
	<noscript>
		<div class="ipt_fsqm_form_message_noscript ui-widget ui-widget-content ui-corner-all">
			<div class="ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
				<h3><?php _e( 'Javascript is disabled', 'ipt_fsqm' ); ?></h3>
			</div>
			<div class="ui-widget-content ui-corner-bottom">
				<p><?php _e( 'Javascript is disabled on your browser. Please enable it in order to use this form.', 'ipt_fsqm' ); ?></p>
			</div>
		</div>
	</noscript>
	<?php $ui->ajax_loader( false, '', array(), true, __( 'Loading', 'ipt_fsqm' ), array( 'ipt_uif_init_loader' ) ); ?>
	<div style="<?php echo esc_attr( $main_container_style ); ?>" class="ipt_uif_hidden_init ui-widget-content ui-corner-all ipt_uif_up_main_container">
		<?php if ( ! is_user_logged_in() || ! ( $user instanceof WP_User ) ) : ?>
		<?php $ui->divider( $login, 'h3', 'left', 0xe10f, false, array( 'eform-up-login-header' ) ); ?>
		<form action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" name="<?php echo $args['form_id']; ?>" id="<?php echo $args['form_id']; ?>" method="post">
			<?php $ui->login_form( $args, $login_buttons ); ?>
		</form>
		<div class="clear"></div>
		<?php else : ?>

		<div class="ipt_fsqm_user_portal_welcome">
			<?php if ( $avatar !== '' || $avatar !== '0' || $avatar > 0 ) : ?>
			<div class="ipt_fsqm_up_profile">
				<?php echo get_avatar( $user->ID, $avatar ); ?>
			</div>
			<?php endif; ?>
			<div class="ipt_fsqm_up_welcome">
				<?php if ( $title != '' ) : ?>
				<h2><?php echo $title; ?></h2>
				<?php endif; ?>
				<div class="ipt_fsqm_up_msg">
					<?php echo do_shortcode( wpautop( str_replace( '%NAME%', '<strong>' . $user->display_name . '</strong>', $content ) ) ); ?>
				</div>
			</div>
			<div class="clear"></div>
			<div class="ipt_fsqm_up_toolbar">
				<?php $ui->buttons( $toolbar_buttons ); ?>
				<div class="ipt_fsqm_up_meta">
					<h6><?php $ui->print_icon_by_class( 'drawer2', false ); ?><?php printf( _n( '%d Submission', '%d Submissions', $total_count, 'ipt_fsqm' ), $total_count ); ?></h6>
					<?php if ( $showscore == '1' ) : ?>
					<h6><?php $ui->print_icon_by_class( 'quill', false ); ?><?php printf( __( '%2$s%% Average %1$s', 'ipt_fsqm' ), $scorelabel, number_format_i18n( $avg_score * 100, 2 ) ); ?></h6>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php $ui->progressbar( '', 0, array( 'ipt_fsqm_up_pb' ) ); ?>
		<?php $ui->ajax_loader( false, '', array(), true, __( 'Fetching Data', 'ipt_fsqm' ), array( 'ipt_fsqm_up_al' ) ); ?>
		<table class="ipt_fsqm_up_table" style="display: none">
			<thead>
			<?php if ( $filters == '0' ) : ?>
				<tr>
					<th class="form_label desktop tablet-l tablet-p mobile-l mobile-p" scope="col"><?php echo $formlabel; ?></th>
					<th class="date_label desktop tablet-l tablet-p mobile-l mobile-p" scope="col"><?php echo $datelabel; ?></th>
					<?php if ( $showcategory == '1' ) : ?>
					<th class="category_label desktop tablet-l"><?php echo $categorylabel; ?></th>
					<?php endif; ?>
					<?php if ( $showscore == '1' ) : ?>
					<th class="score_label desktop tablet-l" scope="col"><?php echo $scorelabel; ?></th>
					<th class="mscore_label desktop tablet-l" scope="col"><?php echo $mscorelabel; ?></th>
					<th class="pscore_label desktop tablet-l" scope="col"><?php echo $pscorelabel; ?></th>
					<?php endif; ?>
					<?php if ( '1' == $showremarks ) : ?>
						<th class="admin_remarks desktop tablet-l" scope="col"><?php echo $remarkslabel; ?></th>
					<?php endif; ?>
					<th class="action_label  desktop tablet-l" scope="col"><?php echo $actionlabel; ?></th>
				</tr>
			<?php else : ?>
				<tr>
					<th class="form_filter filter_th desktop tablet-l tablet-p mobile-l mobile-p" scope="col"></th>
					<th class="date_filter filter_th desktop tablet-l tablet-p mobile-l mobile-p"></th>
					<?php if ( $showcategory == '1' ) : ?>
						<th class="category_filter filter_th desktop tablet-l"></th>
					<?php endif; ?>
					<?php if ( $showscore == '1' ) : ?>
					<th class="score_label desktop tablet-l" rowspan="2" scope="col"><?php echo $scorelabel; ?></th>
					<th class="mscore_label desktop tablet-l" rowspan="2" scope="col"><?php echo $mscorelabel; ?></th>
					<th class="pscore_label desktop tablet-l" rowspan="2" scope="col"><?php echo $pscorelabel; ?></th>
					<?php endif; ?>
					<?php if ( '1' == $showremarks ) : ?>
						<th class="admin_remarks desktop tablet-l" rowspan="2" scope="col"><?php echo $remarkslabel; ?></th>
					<?php endif; ?>
					<th class="action_label desktop tablet-l" rowspan="2" scope="col"><?php echo $actionlabel; ?></th>
				</tr>
				<tr>
					<th class="form_label desktop tablet-l tablet-p mobile-l mobile-p" scope="col"><?php echo $formlabel; ?></th>
					<th class="date_label desktop tablet-l tablet-p mobile-l mobile-p" scope="col"><?php echo $datelabel; ?></th>
					<?php if ( $showcategory == '1' ) : ?>
					<th class="category_label desktop tablet-l"><?php echo $categorylabel; ?></th>
					<?php endif; ?>
				</tr>
			<?php endif; ?>
			</thead>
			<tbody>
				<tr>
					<td><?php echo $nosubmission; ?></td>
					<td></td>
					<?php if ( $showcategory == '1' ) : ?>
					<td></td>
					<?php endif; ?>
					<?php if ( $showscore == '1' ) : ?>
					<td></td>
					<td></td>
					<td></td>
					<?php endif; ?>
					<?php if ( '1' == $showremarks ) : ?>
						<td></td>
					<?php endif; ?>
					<td></td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<th class="form_label desktop tablet-l tablet-p mobile-l mobile-p" scope="col"><?php echo $formlabel; ?></th>
					<th class="date_label desktop tablet-l tablet-p mobile-l mobile-p" scope="col"><?php echo $datelabel; ?></th>
					<?php if ( $showcategory == '1' ) : ?>
					<th class="category_label desktop tablet-l"><?php echo $categorylabel; ?></th>
					<?php endif; ?>
					<?php if ( $showscore == '1' ) : ?>
					<th class="score_label desktop tablet-l" scope="col"><?php echo $scorelabel; ?></th>
					<th class="mscore_label desktop tablet-l" scope="col"><?php echo $mscorelabel; ?></th>
					<th class="pscore_label desktop tablet-l" scope="col"><?php echo $pscorelabel; ?></th>
					<?php endif; ?>
					<?php if ( '1' == $showremarks ) : ?>
						<th class="admin_remarks desktop tablet-l" scope="col"><?php echo $remarkslabel; ?></th>
					<?php endif; ?>
					<th class="action_label  desktop tablet-l" scope="col"><?php echo $actionlabel; ?></th>
				</tr>
			</tfoot>
		</table>
		<?php endif; ?>
	</div>
</div>
		<?php
		$form_output = ob_get_clean();
		if ( WP_DEBUG !== true ) {
			$form_output = IPT_FSQM_Minify_HTML::minify( $form_output );
		}

		return $form_output;
	}


	/*==========================================================================
	 * Chart Helper functions - Statics
	 *========================================================================*/

	/**
	 * Get the chart compatible form elements from a form
	 *
	 * Loops through all mcqs and pinfos and selects elements for which charts
	 * can be drawn
	 *
	 * @param      int    $form_id  The id of the form
	 *
	 * @return     array  Associative array of elements which are chart compatible. All passed through ipt_fsqm_form_chart_elements filter
	 */
	public static function get_chart_elements( $form_id ) {
		// Prepare an empty return
		$return = array(
			'mcqs' => array(),
			'pinfos' => array(),
		);

		// Instantiate the form
		$form = new IPT_FSQM_Form_Elements_Base( $form_id );

		// If invalid form, then just return the empty array
		if ( is_null( $form->form_id ) ) {
			return $return;
		}

		// pinfo types for charts
		$pinfo_charts = self::get_pinfo_chart_elements();

		// Now iterate through all mcq elements
		foreach ( $form->mcq as $m_key => $mcq ) {
			$return['mcqs'][] = array(
				'key' => $m_key,
				'type' => $mcq['type'],
				'title' => $mcq['title'],
			);
		}

		// Iterate through all pinfo elements
		foreach ( $form->pinfo as $p_key => $pinfo ) {
			if ( in_array( $pinfo['type'], $pinfo_charts ) ) {
				$return['pinfos'][] = array(
					'key' => $p_key,
					'type' => $pinfo['type'],
					'title' => $pinfo['title'],
				);
			}
		}

		return apply_filters( 'ipt_fsqm_form_chart_elements', $return, $form_id, $pinfo_charts );
	}

	/**
	 * Get the default chart related configuration options for mcq and pinfo
	 * elements
	 *
	 * @param      array  $mcqs    The mcq elements
	 * @param      array  $pinfos  The pinfo elements
	 *
	 * @return     array  Associative array of chart related configuration options
	 */
	public static function get_default_chart_n_toggle( $mcqs = array(), $pinfos = array() ) {
		// Get the presets
		$chart_type_n_toggle = self::get_chart_type_n_toggles();

		// Create the skeleton
		$cmeta = array(
			'mcq' => array(
				'charttype' => array(),
				'toggles' => array(),
			),
			'pinfo' => array(
				'charttype' => array(),
				'toggles' => array(),
			),
		);

		// Set for mcq
		foreach ( $mcqs as $m_key => $mcq ) {
			// Set the chart type
			if ( isset( $chart_type_n_toggle['possible_chart_types'][ $mcq['type'] ] ) ) {
				$cmeta['mcq']['charttype'][ $m_key ] = current( array_keys( $chart_type_n_toggle['possible_chart_types'][ $mcq['type'] ] ) );
			}
			// Set the toggles
			if ( isset( $chart_type_n_toggle['possible_toggle_types'][ $mcq['type'] ] ) ) {
				$cmeta['mcq']['toggles'][ $m_key ] = array();
				foreach ( $chart_type_n_toggle['possible_toggle_types'][ $mcq['type'] ] as $toggle ) {
					$cmeta['mcq']['toggles'][ $m_key ][ $toggle ] = true;
				}
			}
		}

		// Set for pinfo
		foreach ( $pinfos as $p_key => $pinfo ) {
			// Set the chart type
			if ( isset( $chart_type_n_toggle['possible_chart_types'][ $pinfo['type'] ] ) ) {
				$cmeta['pinfo']['charttype'][ $p_key ] = current( array_keys( $chart_type_n_toggle['possible_chart_types'][ $pinfo['type'] ] ) );
			}
			// Set the toggles
			if ( isset( $chart_type_n_toggle['possible_toggle_types'][ $pinfo['type'] ] ) ) {
				$cmeta['pinfo']['toggles'][ $p_key ] = array();
				foreach ( $chart_type_n_toggle['possible_toggle_types'][ $pinfo['type'] ] as $toggle ) {
					$cmeta['pinfo']['toggles'][ $p_key ][ $toggle ] = true;
				}
			}
		}

		return apply_filters( 'ipt_fsqm_default_chart_elements', $cmeta, $mcqs, $pinfos );
	}


	/**
	 * Get all possible chart types and other configuration options for
	 * different eForm elements
	 *
	 * @return     array  Possible chart types and configuration options
	 */
	public static function get_chart_type_n_toggles() {
		// We need to calculate only once
		static $return = null;
		// If already calculated, then just return
		if ( ! is_null( $return ) ) {
			return $return;
		}

		// Initiate the return
		$return = array(
			'possible_chart_types' => array(),
			'toggle_labels' => array(),
			'possible_toggle_types' => array(),
		);

		// Chart type presets
		$chart_mcq = array(
			'pie' => __( 'Pie Chart', 'ipt_fsqm' ),
			'bar' => __( 'Bar Chart', 'ipt_fsqm' ),
			'column' => __( 'Column Chart', 'ipt_fsqm' ),
		);
		$chart_slider = array(
			'bar' => __( 'Bar Chart', 'ipt_fsqm' ),
			'column' => __( 'Column Chart', 'ipt_fsqm' ),
			'area' => __( 'Area Chart', 'ipt_fsqm' ),
		);
		$chart_matrix = array(
			'bar' => __( 'Bar Chart', 'ipt_fsqm' ),
			'sbar' => __( 'Stacked Bar Chart', 'ipt_fsqm' ),
			'column' => __( 'Column Chart', 'ipt_fsqm' ),
			'scolumn' => __( 'Stacked Column Chart', 'ipt_fsqm' ),
		);

		// Extend for third-party
		$return['possible_chart_types'] = apply_filters( 'ipt_fsqm_chart_types', array(
			// MCQ Elements
			'radio' => $chart_mcq,
			'checkbox' => $chart_mcq,
			'select' => $chart_mcq,
			'thumbselect' => $chart_mcq,
			'pricing_table' => $chart_mcq,
			'slider' => $chart_slider,
			'spinners' => $chart_slider,
			'grading' => $chart_slider,
			'smileyrating' => $chart_mcq,
			'starrating' => $chart_slider,
			'scalerating' => $chart_slider,
			'matrix' => $chart_matrix,
			'matrix_dropdown' => $chart_matrix,
			'likedislike' => $chart_mcq,
			'toggle' => $chart_mcq,
			'sorting' => $chart_mcq,
			// Do not need anything for feedback elements at this moment
			// pinfo (Other) Elements
			'p_radio' => $chart_mcq,
			'p_checkbox' => $chart_mcq,
			's_checkbox' => $chart_mcq,
			'p_select' => $chart_mcq,
			'p_sorting' => $chart_mcq,
		) );

		// Toggle options for graphs
		// Extend for third-party
		$return['toggle_labels'] = apply_filters( 'ipt_fsqm_chart_toggle_values', array(
			'average' => __( 'Percentage Meta Line (Overrides graph to combo)', 'ipt_fsqm' ),
			'title' => __( 'Show Chart Title', 'ipt_fsqm' ),
			'legend' => __( 'Show Chart Legend and Axis Ticks', 'ipt_fsqm' ),
		) );
		$return['possible_toggle_types'] = apply_filters( 'ipt_fsqm_chart_toggles', array(
			// MCQ Elements
			'radio' => array( 'title', 'legend' ),
			'checkbox' => array( 'title', 'legend' ),
			'select' => array( 'title', 'legend' ),
			'thumbselect' => array( 'title', 'legend' ),
			'pricing_table' => array( 'title', 'legend' ),
			'slider' => array( 'title', 'legend', 'average' ),
			'range' => array( 'title', 'legend' ),
			'spinners' => array( 'title', 'legend', 'average' ),
			'grading' => array( 'title', 'legend', 'average' ),
			'smileyrating' => array( 'title', 'legend' ),
			'starrating' => array( 'title', 'legend' ),
			'scalerating' => array( 'title', 'legend' ),
			'matrix' => array( 'title', 'legend' ),
			'matrix_dropdown' => array( 'title', 'legend' ),
			'likedislike' => array( 'title', 'legend' ),
			'toggle' => array( 'title', 'legend' ),
			'sorting' => array( 'title', 'legend' ),
			// pinfo (Other) Elements
			'p_radio' => array( 'title', 'legend' ),
			'p_checkbox' => array( 'title', 'legend' ),
			's_checkbox' => array( 'title', 'legend' ),
			'p_select' => array( 'title', 'legend' ),
			'p_sorting' => array( 'title', 'legend' ),
		) );

		return apply_filters( 'ipt_fsqm_possible_chart_elements', $return );
	}

	public static function get_pinfo_chart_elements() {
		return apply_filters( 'ipt_fsqm_pinfo_chart_elements', array(
			'p_radio',
			'p_checkbox',
			's_checkbox',
			'p_select',
			'p_sorting',
		) );
	}
}
