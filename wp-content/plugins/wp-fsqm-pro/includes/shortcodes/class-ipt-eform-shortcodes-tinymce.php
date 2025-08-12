<?php
/**
 * Class for initiating tinyMCE builder for eForm related shortcodes
 *
 * It does not concerns with what the shortcode should output in the front-end
 * and does not registers any shortcode. Those should be done by the output
 * classes. This provides an easy way so the eForm core shortcode builder variables
 * can be placed together
 *
 * @package    eForm - WordPress Form Builder
 * @subpackage Shortcodes\tinyMCE
 */
class IPT_EForm_Shortcodes_TinyMCE {
	/**
	 * Singleton instance variable
	 */
	private static $instance = null;

	/**
	 * Get the instance of this singleton class
	 *
	 * @return     IPT_eForm_Shortcodes_TinyMCE  The instance of the class
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new IPT_EForm_Shortcodes_TinyMCE();
		}
		return self::$instance;
	}

	/**
	 * The consturctor
	 *
	 * The access is made private so that the class can be singleton
	 */
	private function __construct() {
		// Initiate the AJAX Helpers needed for shortcode constructor callbacks
		$this->shortcode_ajax_helpers();
		// Add action to the init which would initialize the scripts needed for tinyMCE
		add_action( 'init', array( $this, 'admin_init_hook' ) );
	}

	protected function shortcode_ajax_helpers() {
		add_action( 'wp_ajax_ipt_fsqm_shortcode_get_form_elements_for_mce', array( $this, 'shortcode_get_form_elements_for_mce' ) );
	}

	/**
	 * Get all form elements for a form along with their default configuration
	 *
	 * This is used by the tinyMCE script which would then create a dynamic
	 * configuration modal based on received values
	 *
	 * @return     array  Form Elements and configuration
	 */
	public function shortcode_get_form_elements_for_mce() {
		$form_id = (int) $_REQUEST['form_id'];
		$report_config = (array) $_REQUEST['reportType'];
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		if ( ! wp_verify_nonce( $_REQUEST['wpnonce'], 'ipt_fsqm_shortcode_get_mcqs' ) ) {
			$return = array(
				'html' => __( 'Cheatin&#8217; uh?' ),
			);
			echo json_encode( (object) $return );
			die();
		}

		$return = $this->get_form_elements_for_mce( $form_id, $report_config );

		echo json_encode( (object) $return );
		die();
	}

	public function get_form_elements_for_mce( $form_id, $report_config ) {
		global $wpdb, $ipt_fsqm_info;
		$form_element = new IPT_FSQM_Form_Elements_Utilities( $form_id );
		if ( null == $form_element->form_id ) {
			$return = array(
				'html' => __( 'Please select a form and try again.', 'ipt_fsqm' ),
			);
			echo json_encode( (object) $return );
			die();
		}
		// Now return elements depending on the report type
		$return = array(
			'mcqs' => array(),
			'freetypes' => array(),
			'pinfos' => array(),
			'filters' => array(),
		);

		// Include mcqs
		if ( isset( $report_config['mcq'] ) && 'true' == $report_config['mcq'] ) {
			$mcqs = $form_element->get_keys_from_layouts_by_m_type( 'mcq', $form_element->layout );
			if ( ! empty( $mcqs ) ) {
				$rmcqs = array();
				$rmcqs[] = array(
					'text' => __( 'Show All', 'ipt_fsqm' ),
					'value' => 'all',
				);
				foreach ( $mcqs as $mcq ) {
					$rmcqs[] = array(
						'text' => $form_element->mcq[ $mcq ]['title'],
						'value' => $mcq,
						'type' => $form_element->mcq[ $mcq ]['type'],
					);
				}
				$return['mcqs'] = $rmcqs;
			}
		}

		// Include feedbacks
		if ( isset( $report_config['freetype'] ) && 'true' == $report_config['freetype'] ) {
			$freetypes = $form_element->get_keys_from_layouts_by_m_type( 'freetype', $form_element->layout );
			if ( ! empty( $freetypes ) ) {
				$rfreetypes = array();
				$rfreetypes[] = array(
					'text' => __( 'Show All', 'ipt_fsqm' ),
					'value' => 'all',
				);
				foreach ( $freetypes as $freetype ) {
					$rfreetypes[] = array(
						'text' => $form_element->freetype[ $freetype ]['title'],
						'value' => $freetype,
						'type' => $form_element->freetype[ $freetype ]['type'],
					);
				}
				$return['freetypes'] = $rfreetypes;
			}
		}

		// Include pinfos
		if ( isset( $report_config['pinfo'] ) && 'true' == $report_config['pinfo'] ) {
			$pinfos = $form_element->get_keys_from_layouts_by_m_type( 'pinfo', $form_element->layout );
			if ( ! empty( $pinfos ) ) {
				$rpinfos = array();
				$rpinfos[] = array(
					'text' => __( 'Show All', 'ipt_fsqm' ),
					'value' => 'all',
				);
				foreach ( $pinfos as $pinfo ) {
					$rpinfos[] = array(
						'text' => $form_element->pinfo[ $pinfo ]['title'],
						'value' => $pinfo,
						'type' => $form_element->pinfo[ $pinfo ]['type'],
					);
				}
				$return['pinfos'] = $rpinfos;
			}
		}

		// Include filters
		// Valid users
		$user_ids = $wpdb->get_col( $wpdb->prepare( "SELECT distinct user_id FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d", $form_id ) );
		$user_id_items = array();
		$user_id_items[] = array(
			'value' => 'all',
			'text' => __( 'Show for all users', 'ipt_fsqm' ),
		);
		foreach ( $user_ids as $uid ) {
			if ( '0' === $uid ) {
				continue;
			}
			$userdata = get_userdata( $uid );
			if ( ! is_wp_error( $userdata ) && is_object( $userdata ) ) {
				$user_id_items[] = array(
					'value' => $uid,
					'text' => $userdata->user_nicename,
				);
			} else {
				$user_id_items[] = array(
					'value' => $uid,
					'text' => sprintf( __( '(Deleted User) ID: %s', 'ipt_fsqm' ), $uid ),
				);
			}
		}
		$return['filters']['users'] = $user_id_items;

		// Get valid URL tracking
		$url_tracking = (array) $wpdb->get_results( $wpdb->prepare( "SELECT url_track, id, form_id FROM {$ipt_fsqm_info['data_table']} GROUP BY url_track HAVING form_id = %d AND url_track != ''", $form_id ) );
		$url_tracking_items = array(
			0 => array(
				'value' => 'all',
				'text' => __( 'Show for all', 'ipt_fsqm' ),
			),
		);

		foreach ( $url_tracking as $ut ) {
			if ( null == $ut->url_track || '' == $ut->url_track ) {
				continue;
			}
			$url_tracking_items[] = array(
				'value' => $ut->id,
				'text' => $ut->url_track,
			);
		}
		$return['filters']['urltb'] = $url_tracking_items;

		// Get valid date range
		$return['filters']['dates']['least_date'] = $wpdb->get_var( $wpdb->prepare( "SELECT date FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d ORDER BY date ASC LIMIT 0,1", $form_id ) );
		$return['filters']['dates']['recent_date'] = $wpdb->get_var( $wpdb->prepare( "SELECT date FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d ORDER BY date DESC LIMIT 0,1", $form_id ) );
		return $return;
	}

	/**
	 * Add the tinyMCE shortcode builder scripts and buttons
	 */
	public function admin_init_hook() {
		add_action( 'admin_enqueue_scripts', array( $this, 'fsqm_mce_extendor' ) );
		add_filter( 'mce_external_plugins', array( $this, 'mce_external_plugins' ) );
		add_filter( 'mce_buttons', array( $this, 'mce_buttons' ) );
		add_action( 'before_wp_tiny_mce', array( $this, 'fsqm_mce_icons' ) );
	}

	public function fsqm_mce_icons() {
		// Add icon when tinymce is loaded
		wp_register_style( 'ipt-icomoon-fonts', IPT_FSQM_Loader::$static_location . 'fonts/icomoon/icomoon.min.css', array(), IPT_FSQM_Loader::$version );
		// manually print style because it would be too late anyway
		wp_print_styles( 'ipt-icomoon-fonts' );
	}

	public static function get_localizations() {
		// Charting variables
		$chart_helper = IPT_EForm_Core_Shortcodes::get_chart_type_n_toggles();
		// Change it a little for using with JSON
		// We need to keep the order for defaulting
		$chart_maps = $chart_helper['possible_chart_types'];
		foreach ( $chart_maps as $etype => $echarts ) {
			$chart_helper['possible_chart_types'][ $etype ]['default'] = current( array_keys( $echarts ) );
		}
		$report_type = array(
			array(
				'text' => __( 'Survey (MCQ) Elements', 'ipt_fsqm' ),
				'value' => 'mcq',
				'checked' => true,
			),
			array(
				'text' => __( 'Feedback & Upload Elements', 'ipt_fsqm' ),
				'value' => 'freetype',
				'checked' => true,
			),
			array(
				'text' => __( 'Other Elements', 'ipt_fsqm' ),
				'value' => 'pinfo',
				'checked' => true,
			),
		);
		$report_data = array(
			array(
				'text' => __( 'Show data alongside graphs for mcqs', 'ipt_fsqm' ),
				'value' => 'data',
				'checked' => true,
			),
			array(
				'text' => __( 'Show optional meta entries for mcqs', 'ipt_fsqm' ),
				'value' => 'others',
				'checked' => true,
			),
			array(
				'text' => __( 'Show names for mcq meta entries and feedbacks', 'ipt_fsqm' ),
				'value' => 'names',
				'checked' => true,
			),
			array(
				'text' => __( 'Show date for mcq meta entries and feedbacks', 'ipt_fsqm' ),
				'value' => 'date',
				'checked' => true,
			),
		);
		$report_appearance = array(
			array(
				'text' => __( 'Wrap inside blocks', 'ipt_fsqm' ),
				'value' => 'block',
				'checked' => true,
			),
			array(
				'text' => __( 'Show Element Heading (Shown anyway if Wrap inside blocks is active)', 'ipt_fsqm' ),
				'value' => 'heading',
				'checked' => true,
			),
			array(
				'text' => __( 'Show element Description', 'ipt_fsqm' ),
				'value' => 'description',
				'checked' => true,
			),
			array(
				'text' => __( 'Show table header', 'ipt_fsqm' ),
				'value' => 'header',
				'checked' => true,
			),
			array(
				'text' => __( 'Show table border', 'ipt_fsqm' ),
				'value' => 'border',
				'checked' => true,
			),
			array(
				'text' => __( 'Use Google Material Charts instead of Classic Charts (for Bar & Column charts only)', 'ipt_fsqm' ),
				'value' => 'material',
				'checked' => false,
			),
			array(
				'text' => __( 'Show the print button', 'ipt_fsqm' ),
				'value' => 'print',
				'checked' => false,
			),
		);
		$l10n = array(
			'l10n' => array(
				'label' => __( 'Insert Shortcodes for eForm', 'ipt_fsqm' ),
				'slabel' => __( 'eForm - ', 'ipt_fsqm' ),
				'salabel' => __( 'Select Questions', 'ipt_fsqm' ),
				'fselect' => __( 'Please select a form', 'ipt_fsqm' ),
				'ajax' => __( 'Please wait. Press OK to exit!', 'ipt_fsqm' ),
				'ss' => array(
					'ss' => __( 'System Shortcodes', 'ipt_fsqm' ),
					'up' => __( 'Centralized User Portal Page', 'ipt_fsqm' ),
					'tb' => __( 'Single Submission Trackback', 'ipt_fsqm' ),
					'tbfl' => __( 'Form Label', 'ipt_fsqm' ),
					'tbfll' => __( 'Track Code', 'ipt_fsqm' ),
					'tbfltt' => __( 'Enter the label of the text input where the surveyee will need to paste his/her trackback code.', 'ipt_fsqm' ),
					'tbsbtl' => __( 'Submit Button Text', 'ipt_fsqm' ),
					'tbsbt' => __( 'Submit', 'ipt_fsqm' ),
					'uplabels' => array(
						'llogin_attr' => __( 'Login Page Modifications', 'ipt_fsqm' ),
						'lportal_attr' => __( 'Portal Page Modifications', 'ipt_fsqm' ),
						'login_attr' => array(
							'login' => __( 'Message to logged out users', 'ipt_fsqm' ),
							'show_register' => __( 'Show the registration button', 'ipt_fsqm' ),
							'show_forgot' => __( 'Show password recovery link', 'ipt_fsqm' ),
						),
						'portal_attr' => array(
							'title' => __( 'Welcome Title', 'ipt_fsqm' ),
							'content' => __( 'Welcome message', 'ipt_fsqm' ),
							'contenttt' => __( '%NAME% will be replaced by user name', 'ipt_fsqm' ),
							'nosubmission' => __( 'No submissions message', 'ipt_fsqm' ),
							'formlabel' => __( 'Form Heading Label', 'ipt_fsqm' ),
							'filters' => __( 'Show Filters for Forms and Categories', 'ipt_fsqm' ),
							'filterstt' => __( 'If enabled then users would be able to select forms and categories from dropdown and enter date/time range.', 'ipt_fsqm' ),
							'showcategory' => __( 'Show Category', 'ipt_fsqm' ),
							'categorylabel' => __( 'Category Label', 'ipt_fsqm' ),
							'datelabel' => __( 'Date Heading Label', 'ipt_fsqm' ),
							'showscore' => __( 'Show Score Column', 'ipt_fsqm' ),
							'showscorett' => __( 'If enabled then score obtained, total score and percentage would be shown for relevant forms.', 'ipt_fsqm' ),
							'scorelabel' => __( 'Score Heading Label', 'ipt_fsqm' ),
							'mscorelabel' => __( 'Max Score Heading Label', 'ipt_fsqm' ),
							'pscorelabel' => __( 'Percentage Score Heading Label', 'ipt_fsqm' ),
							'showremarks' => __( 'Show Admin Remarks Column', 'ipt_fsqm' ),
							'showremarkstt' => __( 'If enabled, then administrator remarks will be shown in a column.', 'ipt_fsqm' ),
							'remarkslabel' => __( 'Admin Remarks Label', 'ipt_fsqm' ),
							'actionlabel' => __( 'Action Column Heading Label', 'ipt_fsqm' ),
							'linklabel' => __( 'Trackback Button Label', 'ipt_fsqm' ),
							'editlabel' => __( 'Edit Button Label', 'ipt_fsqm' ),
							'avatar' => __( 'Avatar Size', 'ipt_fsqm' ),
							'theme' => __( 'Portal Theme', 'ipt_fsqm' ),
							'logout_r' => __( 'Redirection after Logout', 'ipt_fsqm' ),
							'logout_r_tt' => __( 'Any valid URL starting with http:// or https://', 'ipt_fsqm' ),
						),
					),
					'updefaults' => array(
						'content' => __( 'Welcome %NAME%. Below is the list of all submissions you have made.', 'ipt_fsqm' ),
						'nosubmission' => __( 'No submissions yet.', 'ipt_fsqm' ),
						'login' => __( 'You need to login in order to view your submissions.', 'ipt_fsqm' ),
						'formlabel' => __( 'Form', 'ipt_fsqm' ),
						'categorylabel' => __( 'Category', 'ipt_fsqm' ),
						'datelabel' => __( 'Date', 'ipt_fsqm' ),
						'scorelabel' => __( 'Score', 'ipt_fsqm' ),
						'mscorelabel' => __( 'Max', 'ipt_fsqm' ),
						'pscorelabel' => __( '%-age', 'ipt_fsqm' ),
						'remarkslabel' => __( 'Remarks', 'ipt_fsqm' ),
						'linklabel' => __( 'View', 'ipt_fsqm' ),
						'actionlabel' => __( 'Action', 'ipt_fsqm' ),
						'editlabel' => __( 'Edit', 'ipt_fsqm' ),
						'avatar' => '96',
						'title' => __( 'eForm User Portal', 'ipt_fsqm' ),
						'logout_r' => '',
					),
					'login' => array(
						'lb' => __( 'Login Form', 'ipt_fsqm' ),
						'rd' => __( 'Redirect To (empty for current URL)', 'ipt_fsqm' ),
						'rg' => __( 'Show Registration', 'ipt_fsqm' ),
						'rgtt' => __( 'If checked then the form will have a Registration button', 'ipt_fsqm' ),
						'rgurl' => __( 'Registration URL (empty for default)', 'ipt_fsqm' ),
						'fg' => __( 'Show Forgot Password', 'ipt_fsqm' ),
						'fgtt' => __( 'If checked then the form will have a Forgot Password button', 'ipt_fsqm' ),
						'theme' => __( 'Login Form Theme', 'ipt_fsqm' ),
						'msg' => __( 'Form Heading', 'ipt_fsqm' ),
						'msgdf' => __( 'Please login to our site', 'ipt_fsqm' ),
					),
				),
				'lb' => array(
					'lb'              => __( 'Insert Leaderboard', 'ipt_fsqm' ),
					'flb'             => __( 'Form Leaderboard', 'ipt_fsqm' ),

					'flba'            => __( 'Appearance Options', 'ipt_fsqm' ),

					'flbark'          => __( 'Show Rank', 'ipt_fsqm' ),
					'flbaa'           => __( 'Show Avatar', 'ipt_fsqm' ),
					'flbaas'          => __( 'Avatar Size', 'ipt_fsqm' ),
					'flban'           => __( 'Show Name', 'ipt_fsqm' ),
					'flbad'           => __( 'Show Date', 'ipt_fsqm' ),
					'flbas'           => __( 'Show Score', 'ipt_fsqm' ),
					'flbams'          => __( 'Show Max Score', 'ipt_fsqm' ),
					'flbap'           => __( 'Show Percentage', 'ipt_fsqm' ),
					'flbac'           => __( 'Show Administrator Comment', 'ipt_fsqm' ),
					'flbah'           => __( 'Show form name as heading', 'ipt_fsqm' ),
					'flbai'           => __( 'Show form header image', 'ipt_fsqm' ),
					'flbam'           => __( 'Show User Meta in table', 'ipt_fsqm' ),
					'flbatm'          => __( 'Show Time', 'ipt_fsqm' ),

					'flbl'            => __( 'Labels', 'ipt_fsqm' ),

					'flblvrk'         => __( 'Rank', 'ipt_fsqm' ),
					'flblvname'       => __( 'Name', 'ipt_fsqm' ),
					'flblvdate'       => __( 'Date', 'ipt_fsqm' ),
					'flblvscore'      => __( 'Score', 'ipt_fsqm' ),
					'flblvmax_score'  => __( 'Out of', 'ipt_fsqm' ),
					'flblvpercentage' => __( 'Percentage', 'ipt_fsqm' ),
					'flblvcomment'    => __( 'Remarks', 'ipt_fsqm' ),
					'flblvtm'         => __( 'Time', 'ipt_fsqm' ),

					'flblrk'          => __( 'Rank Column', 'ipt_fsqm' ),
					'flblname'        => __( 'Name Column', 'ipt_fsqm' ),
					'flbldate'        => __( 'Date Column', 'ipt_fsqm' ),
					'flblscore'       => __( 'Score Column', 'ipt_fsqm' ),
					'flblmax_score'   => __( 'Max Score Column', 'ipt_fsqm' ),
					'flblpercentage'  => __( 'Percentage Column', 'ipt_fsqm' ),
					'flblcomment'     => __( 'Administrator Comment Column', 'ipt_fsqm' ),
					'flbltm'          => __( 'Time Column', 'ipt_fsqm' ),
					'flblcontent'     => __( 'Content', 'ipt_fsqm' ),
				),
				'st' => array(
					'st' => __( 'Insert Statistics Charts', 'ipt_fsqm' ),

					'stfs' => __( 'Form Statistics', 'ipt_fsqm' ),
					'stus' => __( 'User Statistics', 'ipt_fsqm' ),

					'fssb' => __( 'Form Submission Breakdown', 'ipt_fsqm' ),
					'fssbtt' => __( 'Combo bar chart to show submissions per day per form.', 'ipt_fsqm' ),
					'fssb_lbs' => array(
						'form_ids' => __( 'Form IDs', 'ipt_fsqm' ),
						'days' => __( 'Number of days or date', 'ipt_fsqm' ),
						'max' => __( 'Max Number of forms to show before grouping', 'ipt_fsqm' ),
						'others' => __( 'Grouping Label', 'ipt_fsqm' ),
						'totalline' => __( 'Total line title', 'ipt_fsqm' ),
						'xlabel' => __( 'X Axis Label', 'ipt_fsqm' ),
						'ylabel' => __( 'Y Axis Label', 'ipt_fsqm' ),
						'height' => __( 'Graph Height (px)', 'ipt_fsqm' ),
						'width' => __( 'Graph Width (px)', 'ipt_fsqm' ),
					),
					'fssb_df' => array(
						'form_ids' => 'all',
						'days' => '7',
						'max' => '0',
						'others' => __( 'Others', 'ipt_fsqm' ),
						'totalline' => __( 'Total Submissions', 'ipt_fsqm' ),
						'xlabel' => __( 'Date', 'ipt_fsqm' ),
						'ylabel' => __( 'Submissions', 'ipt_fsqm' ),
						'height' => '700',
						'width' => '1920',
					),
					'fssb_tts' => array(
						'form_ids' => __( 'Enter "all" to show all forms. Or Comma separated values like "1,30,51" etc.', 'ipt_fsqm' ),
						'days' => __( 'Either enter date in Y-m-d (2016-12-30) format to show since mentioned date. Or enter number of days, like 20, to show for past 20 days. Leave blank to show for all time.', 'ipt_fsqm' ),
						'max' => __( 'For rather large number of forms, it is advised to group forms with smaller scales into "Others". Mention the maximum number of forms (exclusive) the system will count before grouping others. Leave empty or 0 to disable.', 'ipt_fsqm' ),
						'others' => __( 'Enter the label of the others grouping.', 'ipt_fsqm' ),
						'totalline' => __( 'If you wish to show a total line in the graph then enter the title. If empty, then total line would not be shown.', 'ipt_fsqm' ),
						'xlabel' => __( 'X Axis Label', 'ipt_fsqm' ),
						'ylabel' => __( 'Y Axis Label', 'ipt_fsqm' ),
						'height' => __( 'Graph Height (px)', 'ipt_fsqm' ),
						'width' => __( 'Graph Width (px)', 'ipt_fsqm' ),
					),

					'fsos' => __( 'Overall Submissions', 'ipt_fsqm' ),
					'fsostt' => __( 'Pie or Doughnut chart to show overall submissions per form.', 'ipt_fsqm' ),
					'fsos_lbs' => array(
						'form_ids' => __( 'Form IDs', 'ipt_fsqm' ),
						'days' => __( 'Number of days or date', 'ipt_fsqm' ),
						'max' => __( 'Max Number of forms to show before grouping', 'ipt_fsqm' ),
						'others' => __( 'Grouping Label', 'ipt_fsqm' ),
						'type' => __( 'Type of Graph', 'ipt_fsqm' ),
						'height' => __( 'Graph Height (px)', 'ipt_fsqm' ),
						'width' => __( 'Graph Width (px)', 'ipt_fsqm' ),
					),
					'fsos_df' => array(
						'form_ids' => 'all',
						'days' => '7', // Can work both as days or since
						'max' => '0',
						'others' => __( 'Others', 'ipt_fsqm' ),
						'type' => 'pie', // Can be pie, doughnut
						'height' => '400',
						'width' => '600',
					),
					'fsos_tts' => array(
						'form_ids' => __( 'Enter "all" to show all forms. Or Comma separated values like "1,30,51" etc.', 'ipt_fsqm' ),
						'days' => __( 'Either enter date in Y-m-d (2016-12-30) format to show since mentioned date. Or enter number of days, like 20, to show for past 20 days. Leave blank to show for all time.', 'ipt_fsqm' ),
						'max' => __( 'For rather large number of forms, it is advised to group forms with smaller scales into "Others". Mention the maximum number of forms (exclusive) the system will count before grouping others. Leave empty or 0 to disable.', 'ipt_fsqm' ),
						'others' => __( 'Enter the label of the others grouping.', 'ipt_fsqm' ),
						'type' => __( 'Type of chart to draw', 'ipt_fsqm' ),
						'height' => __( 'Graph Height (px)', 'ipt_fsqm' ),
						'width' => __( 'Graph Width (px)', 'ipt_fsqm' ),
					),

					'fscb' => __( 'Score Breakdown', 'ipt_fsqm' ),
					'fscbtt' => __( 'Pie or Doughnut chart to show score percentage breakdown for selected form.', 'ipt_fsqm' ),
					'fscb_lbs' => array(
						'form_ids' => __( 'Form IDs', 'ipt_fsqm' ),
						'label' => __( 'Graph Legend Format', 'ipt_fsqm' ),
						'days' => __( 'Number of days or date', 'ipt_fsqm' ),
						'type' => __( 'Type of Graph', 'ipt_fsqm' ),
						'height' => __( 'Graph Height (px)', 'ipt_fsqm' ),
						'width' => __( 'Graph Width (px)', 'ipt_fsqm' ),
					),
					'fscb_df' => array(
						'form_ids' => 'all',
						'label' => __( 'From %1$d%% to %2$d%% ', 'ipt_fsqm' ),
						'days' => '',
						'type' => 'pie', // Can be pie, doughnut
						'height' => '400',
						'width' => '600',
					),
					'fscb_tts' => array(
						'form_ids' => __( 'Enter "all" to show all forms. Or Comma separated values like "1,30,51" etc.', 'ipt_fsqm' ),
						'label' => __( 'Enter legend format of the score breakdown. From %d%% to %d%% will be replaced by From 0% to 9%, From 10% to 19% etc. It takes formatting of PHP sprintf. So %1$d will be replaced by min score and %2$d will be replaced by max score, use them if you want to change the scoring order in the labels.', 'ipt_fsqm' ),
						'days' => __( 'Either enter date in Y-m-d (2016-12-30) format to show since mentioned date. Or enter number of days, like 20, to show for past 20 days. Leave blank to show for all time.', 'ipt_fsqm' ),
						'type' => __( 'Type of chart to draw', 'ipt_fsqm' ),
						'height' => __( 'Graph Height (px)', 'ipt_fsqm' ),
						'width' => __( 'Graph Width (px)', 'ipt_fsqm' ),
					),

					'ussb' => __( 'Submission Breakdown', 'ipt_fsqm' ),
					'ussbtt' => __( 'Combo bar chart to show submissions breakdown for selected or current user per day per form.', 'ipt_fsqm' ),
					'ussb_lbs' => array(
						'form_ids' => __( 'Form IDs', 'ipt_fsqm' ),
						'user_id' => __( 'User ID', 'ipt_fsqm' ),
						'show_login' => __( 'Show Login Form', 'ipt_fsqm' ),
						'login_msg' => __( 'Login Message', 'ipt_fsqm' ),
						'theme' => __( 'Login Form Theme', 'ipt_fsqm' ),
						'days' => __( 'Number of days or date', 'ipt_fsqm' ),
						'max' => __( 'Max Number of forms to show before grouping', 'ipt_fsqm' ),
						'others' => __( 'Grouping Label', 'ipt_fsqm' ),
						'totalline' => __( 'Total line title', 'ipt_fsqm' ),
						'xlabel' => __( 'X Axis Label', 'ipt_fsqm' ),
						'ylabel' => __( 'Y Axis Label', 'ipt_fsqm' ),
						'height' => __( 'Graph Height (px)', 'ipt_fsqm' ),
						'width' => __( 'Graph Width (px)', 'ipt_fsqm' ),
					),
					'ussb_df' => array(
						'form_ids' => 'all',
						'user_id' => 'current',
						'show_login' => '1',
						'login_msg' => __( 'Please login to view statistics', 'ipt_fsqm' ),
						'theme' => 'material-default',
						'days' => '7',
						'max' => '0',
						'others' => __( 'Others', 'ipt_fsqm' ),
						'totalline' => __( 'Total Submissions', 'ipt_fsqm' ),
						'xlabel' => __( 'Date', 'ipt_fsqm' ),
						'ylabel' => __( 'Submissions', 'ipt_fsqm' ),
						'height' => '700',
						'width' => '1920',
					),
					'ussb_tts' => array(
						'form_ids' => __( 'Enter "all" to show all forms. Or Comma separated values like "1,30,51" etc.', 'ipt_fsqm' ),
						'user_id' => __( 'ID of the user for which you want to show stat. Enter current to show for currently logged in user.', 'ipt_fsqm' ),
						'show_login' => __( 'If set to checked and if the stat is for currently logged in user, then a login form would be shown if user is not logged in already.', 'ipt_fsqm' ),
						'login_msg' => __( 'The login form heading. Keep it short.', 'ipt_fsqm' ),
						'theme' => __( 'Login form theme. Please select from the presets.', 'ipt_fsqm' ),
						'days' => __( 'Either enter date in Y-m-d (2016-12-30) format to show since mentioned date. Or enter number of days, like 20, to show for past 20 days. Leave blank to show for all time.', 'ipt_fsqm' ),
						'max' => __( 'For rather large number of forms, it is advised to group forms with smaller scales into "Others". Mention the maximum number of forms (exclusive) the system will count before grouping others. Leave empty or 0 to disable.', 'ipt_fsqm' ),
						'others' => __( 'Enter the label of the others grouping.', 'ipt_fsqm' ),
						'totalline' => __( 'If you wish to show a total line in the graph then enter the title. If empty, then total line would not be shown.', 'ipt_fsqm' ),
						'xlabel' => __( 'X Axis Label', 'ipt_fsqm' ),
						'ylabel' => __( 'Y Axis Label', 'ipt_fsqm' ),
						'height' => __( 'Graph Height (px)', 'ipt_fsqm' ),
						'width' => __( 'Graph Width (px)', 'ipt_fsqm' ),
					),

					'usob' => __( 'Overall Submissions', 'ipt_fsqm' ),
					'usobtt' => __( 'Pie or Doughnut chart to show overall submissions for selected or current user for selected forms.', 'ipt_fsqm' ),
					'usob_lbs' => array(
						'form_ids' => __( 'Form IDs', 'ipt_fsqm' ),
						'user_id' => __( 'User ID', 'ipt_fsqm' ),
						'show_login' => __( 'Show Login Form', 'ipt_fsqm' ),
						'login_msg' => __( 'Login Message', 'ipt_fsqm' ),
						'theme' => __( 'Login Form Theme', 'ipt_fsqm' ),
						'days' => __( 'Number of days or date', 'ipt_fsqm' ),
						'max' => __( 'Max Number of forms to show before grouping', 'ipt_fsqm' ),
						'others' => __( 'Grouping Label', 'ipt_fsqm' ),
						'type' => __( 'Type of Graph', 'ipt_fsqm' ),
						'height' => __( 'Graph Height (px)', 'ipt_fsqm' ),
						'width' => __( 'Graph Width (px)', 'ipt_fsqm' ),
					),
					'usob_df' => array(
						'form_ids' => 'all',
						'user_id' => 'current',
						'show_login' => '1',
						'login_msg' => __( 'Please login to view statistics', 'ipt_fsqm' ),
						'theme' => 'material-default',
						'days' => '',
						'max' => '0',
						'others' => __( 'Others', 'ipt_fsqm' ),
						'type' => 'pie', // Can be pie, doughnut
						'height' => '400',
						'width' => '600',
					),
					'usob_tts' => array(
						'form_ids' => __( 'Enter "all" to show all forms. Or Comma separated values like "1,30,51" etc.', 'ipt_fsqm' ),
						'user_id' => __( 'ID of the user for which you want to show stat. Enter current to show for currently logged in user.', 'ipt_fsqm' ),
						'show_login' => __( 'If set to checked and if the stat is for currently logged in user, then a login form would be shown if user is not logged in already.', 'ipt_fsqm' ),
						'login_msg' => __( 'The login form heading. Keep it short.', 'ipt_fsqm' ),
						'theme' => __( 'Login form theme. Please select from the presets.', 'ipt_fsqm' ),
						'days' => __( 'Either enter date in Y-m-d (2016-12-30) format to show since mentioned date. Or enter number of days, like 20, to show for past 20 days. Leave blank to show for all time.', 'ipt_fsqm' ),
						'max' => __( 'For rather large number of forms, it is advised to group forms with smaller scales into "Others". Mention the maximum number of forms (exclusive) the system will count before grouping others. Leave empty or 0 to disable.', 'ipt_fsqm' ),
						'others' => __( 'Enter the label of the others grouping.', 'ipt_fsqm' ),
						'type' => __( 'Type of chart to draw', 'ipt_fsqm' ),
						'height' => __( 'Graph Height (px)', 'ipt_fsqm' ),
						'width' => __( 'Graph Width (px)', 'ipt_fsqm' ),
					),


					'usscb' => __( 'Score Breakdown', 'ipt_fsqm' ),
					'usscbtt' => __( 'Pie or Doughnut chart to show score percentage breakdown for selected forms and selected or current user.', 'ipt_fsqm' ),
					'usscb_lbs' => array(
						'form_ids' => __( 'Form IDs', 'ipt_fsqm' ),
						'user_id' => __( 'User ID', 'ipt_fsqm' ),
						'show_login' => __( 'Show Login Form', 'ipt_fsqm' ),
						'login_msg' => __( 'Login Message', 'ipt_fsqm' ),
						'theme' => __( 'Login Form Theme', 'ipt_fsqm' ),
						'label' => __( 'Graph Legend Format', 'ipt_fsqm' ),
						'type' => __( 'Type of Graph', 'ipt_fsqm' ),
						'height' => __( 'Graph Height (px)', 'ipt_fsqm' ),
						'width' => __( 'Graph Width (px)', 'ipt_fsqm' ),
					),
					'usscb_df' => array(
						'form_ids' => 'all',
						'user_id' => 'current',
						'show_login' => '1',
						'login_msg' => __( 'Please login to view statistics', 'ipt_fsqm' ),
						'theme' => 'material-default',
						'label' => __( 'From %1$d%% to %2$d%% ', 'ipt_fsqm' ),
						'type' => 'pie', // Can be pie, doughnut
						'height' => '400',
						'width' => '600',
					),
					'usscb_tts' => array(
						'form_ids' => __( 'Enter "all" to show all forms. Or Comma separated values like "1,30,51" etc.', 'ipt_fsqm' ),
						'user_id' => __( 'ID of the user for which you want to show stat. Enter current to show for currently logged in user.', 'ipt_fsqm' ),
						'show_login' => __( 'If set to checked and if the stat is for currently logged in user, then a login form would be shown if user is not logged in already.', 'ipt_fsqm' ),
						'login_msg' => __( 'The login form heading. Keep it short.', 'ipt_fsqm' ),
						'theme' => __( 'Login form theme. Please select from the presets.', 'ipt_fsqm' ),
						'label' => __( 'Enter legend format of the score breakdown. From %d%% to %d%% will be replaced by From 0% to 9%, From 10% to 19% etc. It takes formatting of PHP sprintf. So %1$d will be replaced by min score and %2$d will be replaced by max score, use them if you want to change the scoring order in the labels.', 'ipt_fsqm' ),
						'type' => __( 'Type of chart to draw', 'ipt_fsqm' ),
						'height' => __( 'Graph Height (px)', 'ipt_fsqm' ),
						'width' => __( 'Graph Width (px)', 'ipt_fsqm' ),
					),

					'charts' => array(
						0 => array(
							'text' => __( 'Pie Chart', 'ipt_fsqm' ),
							'value' => 'pie',
						),
						1 => array(
							'text' => __( 'Doughnut Chart', 'ipt_fsqm' ),
							'value' => 'doughnut',
						),
					),
				),
				'if' => __( 'Insert Form', 'ipt_fsqm' ),
				'ifl' => __( 'Select Form', 'ipt_fsqm' ),
				'it' => __( 'Insert Trends', 'ipt_fsqm' ),
				'itvc' => __( 'Title of the Visualization Column', 'ipt_fsqm' ),
				'itvcv' => __( 'Trends', 'ipt_fsqm' ),
				'itvsl' => __( 'Server Load', 'ipt_fsqm' ),
				'itvsllb' => array(
					array(
						'text' => __( 'Light Load: 15 queries per hit', 'ipt_fsqm' ),
						'value' => '0',
					),
					array(
						'text' => __( 'Medium Load: 30 queries per hit (Recommended)', 'ipt_fsqm' ),
						'value' => '1',
						'selected' => true,
					),
					array(
						'text' => __( 'Heavy Load: 50 queries per hit', 'ipt_fsqm' ),
						'value' => '2',
					),
				),
				'pf' => __( 'Insert Popup Forms', 'ipt_fsqm' ),
				'pfbt' => __( 'Button Text', 'ipt_fsqm' ),
				'pfbtl' => __( 'Contact Form', 'ipt_fsqm' ),
				'pfbc' => __( 'Button Color', 'ipt_fsqm' ),
				'pfbbc' => __( 'Button Background Color', 'ipt_fsqm' ),
				'pfbp' => __( 'Button Position', 'ipt_fsqm' ),
				'pfbplb' => array(
					array(
						'text' => __( 'Right', 'ipt_fsqm' ),
						'value' => 'r',
					),
					array(
						'text' => __( 'Bottom Right', 'ipt_fsqm' ),
						'value' => 'br',
						'selected' => true,
					),
					array(
						'text' => __( 'Bottom Center', 'ipt_fsqm' ),
						'value' => 'bc',
					),
					array(
						'text' => __( 'Bottom Left', 'ipt_fsqm' ),
						'value' => 'bl',
					),
					array(
						'text' => __( 'Left', 'ipt_fsqm' ),
						'value' => 'l',
					),
					array(
						'text' => __( 'Hidden / Manual Trigger' ),
						'value' => 'h',
					),
				),
				'pfbs' => __( 'Button Style', 'ipt_fsqm' ),
				'pfbslb' => array(
					array(
						'text' => __( 'Rectangular', 'ipt_fsqm' ),
						'value' => 'rect',
					),
					array(
						'text' => __( 'Circular', 'ipt_fsqm' ),
						'value' => 'circ',
					),
				),
				'pfbheader' => __( 'Popup Header Title, %FORM% is form name', 'ipt_fsqm' ), // form name
				'pfbsubtitle' => __( 'Popup Header Subtitle', 'ipt_fsqm' ), // Some subtitle
				'pfbicon' => __( 'Popup Button Icon class', 'ipt_fsqm' ), // icon class needs to be supplied by vendor
				'pfbwidth' => __( 'Initial Popup Width', 'ipt_fsqm' ), // Initial popup width in pixels
				'pfpv' => __( 'Button Preview', 'ipt_fsqm' ),
				'pfmt' => __( 'Since you have chosen manual trigger, you need to insert the following code somewhere in the post. Do you want to insert automatically?', 'ipt_fsqm' ),
				'pfmtf' => __( 'Alternately you can note down the href attribute of the button and put it in any custom button through theme shortcode or any preferred method.', 'ipt_fsqm' ),
				'twb1' => array(
					'rt' => __( 'Report Type', 'ipt_fsqm' ),
					'rdc' => __( 'Report Data Customizations', 'ipt_fsqm' ),
					'ra' => __( 'Report Appearance', 'ipt_fsqm' ),
				),
				'twb2' => array(
					'sm' => __( 'Select the Multiple Choice Type Questions', 'ipt_fsqm' ),
					'sf' => __( 'Select the Feedback Type Questions', 'ipt_fsqm' ),
					'sp' => __( 'Select the Other Form Elements', 'ipt_fsqm' ),
					'sct' => __( 'Chart Type', 'ipt_fsqm' ),
					'sctl' => __( 'Show Chart Title', 'ipt_fsqm' ),
					'scl' => __( 'Show Chart Legend and Axis Ticks', 'ipt_fsqm' ),
					'spm' => __( 'Percentage Meta Line (Overrides graph to combo)', 'ipt_fsqm' ),
					'fl' => __( 'Filter Report', 'ipt_fsqm' ),
					'su' => __( 'Select Users', 'ipt_fsqm' ),
					'surl' => __( 'Select URL Tracks', 'ipt_fsqm' ),
					'umk' => __( 'User Meta Key', 'ipt_fsqm' ),
					'umv' => __( 'User Meta Value', 'ipt_fsqm' ),
					'somin' => __( 'Minimum Score Obtained (Inclusive)', 'ipt_fsqm' ),
					'somax' => __( 'Maximum Score Obtained (Inclusive)', 'ipt_fsqm' ),
					'dtmin' => __( 'Start Date Y-m-d H:i:s ( %s ) (Inclusive)', 'ipt_fsqm' ),
					'dtmax' => __( 'End Date Y-m-d H:i:s ( %s ) (Inclusive)', 'ipt_fsqm' ),
				),
			),
			'addons' => apply_filters( 'ipt_fsqm_mce', array() ),
			'forms' => (array) IPT_FSQM_Form_Elements_Static::get_forms_for_select(),
			'nonce' => wp_create_nonce( 'ipt_fsqm_shortcode_get_mcqs' ),
			'themes' => (array) IPT_FSQM_Form_Elements_Static::get_form_themes_for_select(),
			// Trends variables
			'trends' => array(
				'cTypeToggle' => $chart_helper,
				'reportTypes' => $report_type,
				'reportData' => $report_data,
				'reportAppearance' => $report_appearance,
			),
		);
		return $l10n;
	}

	public function fsqm_mce_extendor() {
		wp_enqueue_script( 'fsqm_mce_extendor', IPT_FSQM_Loader::$static_location . 'admin/js/ipt-fsqm-tinymce-extendor.min.js', IPT_FSQM_Loader::$version );
		wp_localize_script( 'fsqm_mce_extendor', 'iptFSQMTML10n', self::get_localizations() );

		do_action( 'ipt_fsqm_tmce_extendor_script' );
	}

	public function mce_external_plugins( $plugins ) {
		$plugins['iptFSQMv3'] = IPT_FSQM_Loader::$static_location . 'admin/js/ipt-fsqm-tinymce-plugin.min.js';
		return $plugins;
	}

	public function mce_buttons( $buttons ) {
		array_push( $buttons, 'ipt_fsqm_tmce_menubutton' );
		return $buttons;
	}
}
