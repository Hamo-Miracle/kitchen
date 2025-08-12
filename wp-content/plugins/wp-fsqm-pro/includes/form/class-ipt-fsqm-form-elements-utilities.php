<?php
/**
 * WP Feedback, Surver & Quiz Manager - Pro Form Elements Class
 * Utilities
 *
 * @todo #474
 *
 * @package eForm - WordPress Form Builder
 * @subpackage Form\Utilities
 * @author Swashata Ghosh <swashata@intechgrity.com>
 * @codeCoverageIgnore
 */
class IPT_FSQM_Form_Elements_Utilities extends IPT_FSQM_Form_Elements_Base {
	/*==========================================================================
	 * Internal Variables
	 *========================================================================*/
	/**
	 * UI Variable
	 *
	 * @var IPT_Plugin_UIF_Admin
	 */
	public $ui;

	public $possible_chart_types;
	public $possible_toggle_types;
	public $toggle_labels;

	public static $enum = 0;

	/*==========================================================================
	 * Constructor
	 *========================================================================*/
	public function __construct( $form_id = null, $ui = null ) {
		if ( null == $ui ) {
			$this->ui = IPT_Plugin_UIF_Admin::instance();
		} else {
			$this->ui = $ui;
		}

		// Set chart types and toggles
		$this->set_chart_type_n_toggles();

		parent::__construct( $form_id );
	}

	public function set_chart_type_n_toggles() {
		// Get the chart types
		$types = IPT_EForm_Core_Shortcodes::get_chart_type_n_toggles();
		// Set internal variables
		$this->possible_chart_types = $types['possible_chart_types'];
		$this->toggle_labels = $types['toggle_labels'];
		$this->possible_toggle_types = $types['possible_toggle_types'];
	}

	public function enqueue() {
		wp_enqueue_script( 'ba-throttle-debounce', IPT_FSQM_Loader::$bower_components . 'jquery-throttle-debounce/jquery.ba-throttle-debounce.min.js', array( 'jquery' ), IPT_FSQM_Loader::$version );
		wp_enqueue_script( 'google-charts-loader', '//www.gstatic.com/charts/loader.js', array(), IPT_FSQM_Loader::$version );
		wp_enqueue_script( 'ipt-fsqm-report', IPT_FSQM_Loader::$static_location . 'front/js/jquery.ipt-fsqm-reports.min.js', array( 'jquery', 'google-charts-loader' ), IPT_FSQM_Loader::$version );
		wp_localize_script( 'ipt-fsqm-report', 'iptFSQMReport', apply_filters( 'ipt_fsqm_report_js', array(
			'range_text'       => __( ' to ', 'ipt_fsqm' ),
			'option'           => __( 'Option', 'ipt_fsqm' ),
			'avg_slider'       => __( 'Avg', 'ipt_fsqm' ),
			'avg_range'        => __( 'Avg', 'ipt_fsqm' ),
			'avg'              => __( 'based on', 'ipt_fsqm' ),
			'avg_count'        => __( 'submission(s)', 'ipt_fsqm' ),
			'rating_img_full'  => '<img height="16" width="16" alt="1" src="' . $this->ui->get_image_for_icon( 0xe0ea ) . '" />',
			'rating_img_half'  => '<img height="16" width="16" alt="0.5" src="' . $this->ui->get_image_for_icon( 0xe0e9 ) . '" />',
			'rating_img_empty' => '<img height="16" width="16" alt="0" src="' . $this->ui->get_image_for_icon( 0xe0e8 ) . '" />',
			'sorting_img'      => '<img height="16" width="16" src="' . $this->ui->get_image_for_icon( 0xe10b ) . '" />',
			'rating'           => __( 'Rating', 'ipt_fsqm' ),
			'count'            => __( 'Count', 'ipt_fsqm' ),
			'grading'          => __( 'Grading', 'ipt_fsqm' ),
			'value'            => __( 'Value', 'ipt_fsqm' ),
			'noupload'         => __( 'No files uploaded.', 'ipt_fsqm' ),
			'charts'           => __( 'Generating Charts', 'ipt_fsqm' ),
			'g_data'           => array(
				'op_label'        => __( 'Option', 'ipt_fsqm' ),
				'ct_label'        => __( 'Count', 'ipt_fsqm' ),
				'en_label'        => __( 'Entry', 'ipt_fsqm' ),
				'tg_label'        => __( 'Label', 'ipt_fsqm' ),
				'sc_label'        => __( 'State', 'ipt_fsqm' ),
				'scon_label'      => __( 'Checked', 'ipt_fsqm' ),
				'scoff_label'     => __( 'Unchecked', 'ipt_fsqm' ),
				'sl_label'        => __( 'Value', 'ipt_fsqm' ),
				'rg_label'        => __( 'Range', 'ipt_fsqm' ),
				'sl_head_label_s' => __( 'entry', 'ipt_fsqm' ),
				'sl_head_label_p' => __( 'entries', 'ipt_fsqm' ),
				'avg'             => __( 'Average', 'ipt_fsqm' ),
				's_presets'       => __( 'Predefined/Correct Sorting', 'ipt_fsqm' ),
				's_others'        => __( 'Custom Sorting', 'ipt_fsqm' ),
				's_breakdown'     => __( 'Overall Sorting breakdown', 'ipt_fsqm' ),
				's_order'         => __( 'Sorting order', 'ipt_fsqm' ),
				's_order_custom'  => __( 'Customized order', 'ipt_fsqm' ),
				'per'             => __( 'Percentage', 'ipt_fsqm' ),
				'perc'            => __( 'Percentage Entry', 'ipt_fsqm' ),
				'perv'            => __( 'Percentage Value', 'ipt_fsqm' ),
			),
			'callbacks'        => array(),
			'gcallbacks'       => array(),
		) ) );
		do_action( 'ipt_fsqm_report_enqueue', $this );
	}


	/*==========================================================================
	 * Common Reports APIs
	 *========================================================================*/
	public function report_index() {
		echo '<form method="' . ( isset( $_GET['select_questions'] ) ? 'post' : 'get' ) . '" action="' . ( isset( $_GET['select_questions'] ) ? 'admin.php?page=' . $_GET['page'] : '' ) . '">';
		if ( ! isset( $_REQUEST['generate_report'] ) ) {
			echo '<input type="hidden" name="page" value="' . $_GET['page'] . '" />';
		}
		if ( isset( $_REQUEST['generate_report'] ) ) {
			$form_id = (int) $_REQUEST['form_id'];
			$settings = array(
				'form_id' => $form_id,
				'report' => $_REQUEST['report'],
				'load' => $_REQUEST['load'],
			);
			$mcqs = isset( $_REQUEST['mcqs'] ) ? (array) $_REQUEST['mcqs'] : array();
			$freetypes = isset( $_REQUEST['freetypes'] ) ? (array) $_REQUEST['freetypes'] : array();
			$pinfos = isset( $_REQUEST['pinfos'] ) ? (array) $_REQUEST['pinfos'] : array();
			$rdata = isset( $_REQUEST['rdata'] ) ? (array) $_REQUEST['rdata'] : array();
			$rappearance = isset( $_REQUEST['rappearance'] ) ? (array) $_REQUEST['rappearance'] : array();
			$cmeta = isset( $_REQUEST['cmeta'] ) ? (array) $_REQUEST['cmeta'] : array();
			$do_data = in_array( 'data', $rdata ) ? true : false;
			$do_names = in_array( 'names', $rdata ) ? true : false;
			$do_date = in_array( 'date', $rdata ) ? true : false;
			$do_others = in_array( 'others', $rdata ) ? true : false;
			$sensitive_data = in_array( 'sensitive', $rdata ) ? true : false;
			$appearance = array(
				'wrap' => in_array( 'block', $rappearance ) ? true : false,
				'heading' => in_array( 'heading', $rappearance ) ? true : false,
				'description' => in_array( 'description', $rappearance ) ? true : false,
				'theader' => in_array( 'header', $rappearance ) ? true : false,
				'tborder' => in_array( 'border', $rappearance ) ? true : false,
				'material' => in_array( 'material', $rappearance ) ? true : false,
			);

			$filters = array();
			if ( isset( $_REQUEST['filter'] ) ) {
				$filters = wp_unslash( $_REQUEST['filter'] );
			}
			if ( ! isset( $filters['custom_date'] ) ) {
				$filters['custom_date'] = false;
			} else {
				$filters['custom_date'] = true;
			}

			$this->report_generate_report( $settings, $mcqs, $freetypes, $pinfos, $do_data, $do_names, $do_date, $do_others, $sensitive_data, $appearance, $cmeta, $filters );
		} elseif ( isset( $_GET['select_questions'] ) ) {
			$this->report_select_questions( true );
		} else {
			$this->report_show_forms();
		}
		echo '</form>';
	}

	public function report_generate_report( $settings, $mcqs = array(), $freetypes = array(), $pinfos = array(), $do_data = false, $do_names = false, $do_date = false, $do_others = false, $sensitive_data = false, $appearance = array(), $cmeta = array(), $filters = array(), $visualization = '', $ajax_action = 'ipt_fsqm_report' ) {
		$this->enqueue();

		extract( $settings = wp_parse_args( $settings, array(
			'form_id' => 0,
			'report' => array(
				'mcq',
				'freetype',
				'pinfo',
			),
			'load' => '1',
		) ) );

		$this->init( $form_id );

		if ( null == $this->form_id ) {
			$this->ui->msg_error( __( 'Invalid form ID Provided.', 'ipt_fsqm' ) );
			return;
		}

		// Default appearance
		$appearance = wp_parse_args( $appearance, array(
			'wrap' => true,
			'heading' => true,
			'description' => true,
			'theader' => true,
			'tborder' => true,
			'material' => true,
			'print' => true,
		) );

		// Buttons
		$buttons = array();

		if ( true == $appearance['print'] ) {
			$buttons[] = array(
				__( 'Print', 'ipt_fsqm' ),
				'ipt_fsqm_report_print_' . $this->form_id,
				'medium',
				'secondary',
				'normal',
				array( 'ipt_fsqm_report_print' ),
				'button',
				array(),
				array(),
				'',
				'print',
			);
		}

		$buttons = apply_filters( 'ipt_fsqm_filter_utilities_report_print', $buttons, $this );

		//data check
		$total_data = $this->get_total_submissions();
		if ( null == $total_data || $total_data < 1 ) {
			$this->ui->msg_error( __( 'Not enough data to populate report. Please be patient.', 'ipt_fsqm' ), true, __( 'No data', 'ipt_fsqm' ) );
			return;
		}
		$survey = array();
		$feedback = array();
		$pinfo = array();

		// Generate ID and classes
		$wrap_id = 'ipt_fsqm_' . $this->form_id . '_report_' . self::$enum++;
		$wrap_classes = array( 'ipt_fsqm_report' );
		if ( ! $appearance['tborder'] ) {
			$wrap_classes[] = 'ipt_fsqm_report_no_border';
		}
		if ( ! $appearance['wrap'] ) {
			$wrap_classes[] = 'ipt_fsqm_report_no_wrap';
		}
?>
<div class="<?php echo implode( ' ', $wrap_classes ); ?>" id="<?php echo $wrap_id; ?>">
<?php $this->ui->progressbar( '', '0', 'ipt_fsqm_report_progressbar' ); ?>
<?php $this->ui->clear(); ?>
<?php $this->ui->ajax_loader( false, '', array(), true, null, array( 'ipt_fsqm_report_ajax' ) ); ?>
	<?php
	$this->apply_label_filters();
	$report_error = true;
	if ( in_array( 'mcq', $report ) ) {
		$survey = $this->survey_generate_report( $mcqs, $do_data, $do_names, $do_date, $do_others, $sensitive_data, $visualization, $appearance );
		$report_error = false;
	}
	if ( in_array( 'freetype', $report ) ) {
		$feedback = $this->feedback_generate_report( $freetypes, $do_names, $do_date, $sensitive_data, $appearance );
		$report_error = false;
	}
	if ( in_array( 'pinfo', $report ) ) {
		$pinfo = $this->pinfo_generate_report( $pinfos, $do_data, $do_names, $do_date, $do_others, $sensitive_data, $visualization, $appearance );
		$report_error = false;
	}
	if ( $report_error ) {
		$this->ui->msg_error( __( 'Invalid report type selected.', 'ipt_fsqm' ) );
		return;
	}

	if ( ! empty( $survey ) ) {
		$survey['data'] = (object) $survey['data'];
		$survey['elements'] = (object) $survey['elements'];
	}
	if ( ! empty( $feedback ) ) {
		$feedback['data'] = (object) $feedback['data'];
		$feedback['elements'] = (object) $feedback['elements'];
	}
	if ( ! empty( $pinfo ) ) {
		$pinfo['data'] = (object) $pinfo['data'];
		$pinfo['elements'] = (object) $pinfo['elements'];
	}

	$query_elements = array(
		'mcqs' => $mcqs,
		'freetypes' => $freetypes,
		'pinfos' => $pinfos,
	);
?>
</div>
<?php if ( ! empty( $buttons ) ) : ?>
	<?php $this->ui->buttons( $buttons, 'ipt_fsqm_report_button_container_' . $this->form_id, array( 'align-center center ipt_fsqm_report_print' ) ); ?>
<?php endif; ?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		var reportOptions = {
			form_id: <?php echo $this->form_id ?>,
			survey: <?php echo json_encode( (object) $survey ); ?>,
			feedback: <?php echo json_encode( (object) $feedback ); ?>,
			pinfo: <?php echo json_encode( (object) $pinfo ); ?>,
			query_elements: <?php echo json_encode( (object) $query_elements ); ?>,
			filters: <?php echo json_encode( (object) $filters ); ?>,
			settings: <?php echo json_encode( (object) $settings ); ?>,
			wpnonce: '<?php echo wp_create_nonce( 'ipt_fsqm_report_ajax_' . $this->form_id ); ?>',
			ajaxurl: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
			do_data: <?php echo $do_data ? 'true' : 'false'; ?>,
			do_data_nonce: '<?php echo $do_data ? wp_create_nonce( 'ipt_fsqm_report_ajax_do_data_' . $this->form_id ) : ''; ?>',
			do_names: <?php echo $do_names ? 'true' : 'false' ?>,
			do_names_nonce: '<?php echo $do_names ? wp_create_nonce( 'ipt_fsqm_report_ajax_do_names_' . $this->form_id ) : ''; ?>',
			do_others: <?php echo $do_others ? 'true' : 'false' ?>,
			do_others_nonce: '<?php echo $do_others ? wp_create_nonce( 'ipt_fsqm_report_ajax_do_others_' . $this->form_id ) : ''; ?>',
			sensitive_data: <?php echo $sensitive_data ? 'true' : 'false'; ?>,
			sensitive_data_nonce: '<?php echo $sensitive_data ? wp_create_nonce( 'ipt_fsqm_report_ajax_sensitive_data_' . $this->form_id ) : ''; ?>',
			do_date: <?php echo $do_date ? 'true' : 'false'; ?>,
			do_date_nonce: '<?php echo $do_date ? wp_create_nonce( 'ipt_fsqm_report_ajax_do_date_' . $this->form_id ) : ''; ?>',
			cmeta: <?php echo json_encode( (object) $cmeta ); ?>,
			material: <?php echo $appearance['material'] ? 'true' : 'false'; ?>,
			action: '<?php echo $ajax_action; ?>'
		};

		$('#<?php echo $wrap_id; ?>').iptFSQMReport( reportOptions );
	});
</script>
		<?php
	}

	public function report_select_questions( $show_chart_type = true, $show_form_filter = true ) {
		$form_id = (int) $_GET['form_id'];
		$hiddens = array(
			'form_id' => $form_id,
			// 'report' => $_GET['report'],
			'load' => $_GET['load'],
		);

		$this->init( $form_id );
		$this->ui->hiddens( $hiddens );

		$reports = (array) $_GET['report'];
		foreach ( $reports as $report ) {
			echo '<input type="hidden" name="report[]" value="' . esc_attr( $report ) . '" />';
			unset( $report );
		}

		if ( isset( $_GET['rappearance'] ) ) {
			foreach ( (array) $_GET['rappearance'] as $ra ) {
				echo '<input type="hidden" name="rappearance[]" value="' . esc_attr( $ra ) . '" />';
			}
			unset( $ra );
		}

		if ( isset( $_GET['rdata'] ) ) {
			foreach ( (array) $_GET['rdata'] as $rd ) {
				echo '<input type="hidden" name="rdata[]" value="' . esc_attr( $rd ) . '" />';
			}
			unset( $rd );
		}

		if ( null == $this->form_id ) {
			$this->ui->msg_error( __( 'Invalid form ID Provided.', 'ipt_fsqm' ) );
			return;
		} else {
			$report_error = true;
			if ( in_array( 'mcq', $reports ) ) {
				$this->survey_select_questions( $show_chart_type );
				$report_error = false;
			}
			if ( in_array( 'freetype', $reports ) ) {
				$this->feedback_select_questions();
				$report_error = false;
			}
			if ( in_array( 'pinfo', $reports ) ) {
				$this->pinfo_select_questions( $show_chart_type );
				$report_error = false;
			}
			if ( $report_error ) {
				$this->ui->msg_error( __( 'Invalid report type selected.', 'ipt_fsqm' ) );
				return;
			}

			// Show the filtering options
			if ( $show_form_filter ) {
				$this->form_filters();
			}

			echo '<div class="clear"></div>';
			$this->ui->button( __( 'Generate Report', 'ipt_fsqm' ), 'generate_report', 'large', 'primary', 'normal', array(), 'submit', true, array(), array( 'value' => '1' ) );
		}
	}

	public function form_filters() {
		global $wpdb, $ipt_fsqm_info;
		$this->ui->iconbox_head( __( 'Filter Report', 'ipt_fsqm' ), 'filter' );
		// Get valid users
		$user_ids = $wpdb->get_col( $wpdb->prepare( "SELECT distinct user_id FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d", $this->form_id ) );
		$user_id_items = array();
		$user_id_items[] = array(
			'value' => '',
			'label' => __( 'Show for all users', 'ipt_fsqm' ),
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

		// Get valid URL tracking
		$url_tracking = $wpdb->get_col( $wpdb->prepare( "SELECT distinct url_track FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d", $this->form_id ) );
		$url_tracking_items = array(
			0 => array(
				'value' => '',
				'label' => __( 'Show for all', 'ipt_fsqm' ),
			),
		);

		foreach ( $url_tracking as $ut ) {
			if ( null == $ut || '' == $ut ) {
				continue;
			}
			$url_tracking_items[] = array(
				'value' => $ut,
				'label' => $ut,
			);
		}

		// Get valid date range
		$least_date = $wpdb->get_var( $wpdb->prepare( "SELECT date FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d ORDER BY date ASC LIMIT 0,1", $this->form_id ) );
		$recent_date = $wpdb->get_var( $wpdb->prepare( "SELECT date FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d ORDER BY date DESC LIMIT 0,1", $this->form_id ) );
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'filter[user_id][]', __( 'Select Users<br /><span class="description">Ctrl + hold for multiselect</span>', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->select( 'filter[user_id][]', $user_id_items, '', false, false, false, true, array(), true ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Select users for which you want to generate the report.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'filter[url_track][]', __( 'Select URL Tracks<br /><span class="description">Ctrl + hold for multiselect</span>', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->select( 'filter[url_track][]', $url_tracking_items, '', false, false, false, true, array(), true ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Select URL Track codes for which you want to generate the report.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'filter[meta]', __( 'User Meta Key', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'filter[meta]', '', __( 'Disabled', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you want to filter submissions based on user meta key, please specify the meta key here. If the meta value is left empty, then system would check for existence of meta key on a user, no matter the meta value.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'filter[mvalue]', __( 'User Meta Value', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'filter[mvalue]', '', __( 'Disabled', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you have specified a meta key and would like to filter for a particular value, please specify here.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'filter[score]', __( 'Score Obtained Range', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->generate_label( 'filter[score][min]', __( 'Minimum Score (Inclusive)', 'ipt_fsqm' ) ); ?>
				<?php $this->ui->spinner( 'filter[score][min]', '', __( 'Disabled', 'ipt_fsqm' ) ); ?>
				<br />
				<?php $this->ui->generate_label( 'filter[score][max]', __( 'Maximum Score (Inclusive)', 'ipt_fsqm' ) ); ?>
				<?php $this->ui->spinner( 'filter[score][max]', '', __( 'Disabled', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you want to filter for a specific score range, then please mention it here. Minimum and maximum score are inclusive.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'filter[custom_date]', __( 'Custom Date Range', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'filter[custom_date]', __( 'YES', 'ipt_fsqm' ), __( 'NO', 'ipt_fsqm' ), false, '1', false, true, array( 'condid' => 'ipt_fsqm_custom_date_start,ipt_fsqm_custom_date_end' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Tick to enter custom date range for the report.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_custom_date_start">
			<th scope="col">
				<label for="filter[custom_date_start]"><?php _e( 'Start Date:', 'ipt_fsqm' ) ?></label>
			</th>
			<td>
				<?php $this->ui->datetimepicker( 'filter[custom_date_start]', $least_date ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
					<?php _e( 'Please select the start date and time, inclusive', 'ipt_fsqm' ); ?>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_custom_date_end">
			<th scope="col">
				<label for="filter[custom_date_end]"><?php _e( 'End Date:', 'ipt_fsqm' ) ?></label>
			</th>
			<td>
				<?php $this->ui->datetimepicker( 'filter[custom_date_end]', $recent_date ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
					<?php _e( 'Please select the end date and time, inclusive', 'ipt_fsqm' ); ?>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
	</tbody>
</table>
		<?php
		$this->ui->iconbox_tail();
	}

	/**
	 * Show the First Form
	 *
	 * @global wpdb $wpdb
	 * @global array $ipt_fsqm_info
	 *
	 * @param bool $show_data Whether or not to show data options
	 * @param bool $show_appearance Whether or not to show appearance options
	 */
	public function report_show_forms( $show_data = true, $show_appearance = true ) {
		global $wpdb, $ipt_fsqm_info;

		$forms = $wpdb->get_results( "SELECT f.id id, f.name name, COUNT(d.id) subs FROM {$ipt_fsqm_info['form_table']} f LEFT JOIN {$ipt_fsqm_info['data_table']} d ON f.id = d.form_id GROUP BY f.id HAVING COUNT(d.id) > 0 ORDER BY f.id DESC" );
		$select_items = array();

		if ( ! empty( $forms ) ) {
			foreach ( $forms as $form ) {
				$select_items[] = array(
					'label' => sprintf( __( '%1$s (Submissions %2$d)', 'ipt_fsqm' ), $form->name, $form->subs ),
					'value' => $form->id,
				);
			}
		}
		$this->ui->iconbox_head( __( 'Select the Form and Date Range', 'ipt_fsqm' ), 'settings' );

		$server_loads = array(
			array(
				'label' => __( 'Light Load', 'ipt_fsqm' ),
				'value' => '0',
			),
			array(
				'label' => __( 'Moderate Load (Recommended for Shared Hostings)', 'ipt_fsqm' ),
				'value' => '1',
			),
			array(
				'label' => __( 'Heavy Load (Recommended for VPS or Dedicated Hostings)', 'ipt_fsqm' ),
				'value' => '2',
			),
		);

		$report_type = array(
			array(
				'label' => __( 'Survey (MCQ) Elements', 'ipt_fsqm' ),
				'value' => 'mcq',
			),
			array(
				'label' => __( 'Feedback & Upload Elements', 'ipt_fsqm' ),
				'value' => 'freetype',
			),
			array(
				'label' => __( 'Other Elements', 'ipt_fsqm' ),
				'value' => 'pinfo',
			),
		);
		$report_data = array(
			array(
				'label' => __( 'Show data alongside graphs for mcqs', 'ipt_fsqm' ),
				'value' => 'data',
			),
			array(
				'label' => __( 'Show optional meta entries for mcqs', 'ipt_fsqm' ),
				'value' => 'others',
			),
			array(
				'label' => __( 'Show names for mcq meta entries and feedbacks', 'ipt_fsqm' ),
				'value' => 'names',
			),
			array(
				'label' => __( 'Show date for mcq meta entries and feedbacks', 'ipt_fsqm' ),
				'value' => 'date',
			),
			array(
				'label' => __( 'Show sensitive data (email, phone number etc) when applicable', 'ipt_fsqm' ),
				'value' => 'sensitive',
			),
		);
		$report_appearance = array(
			array(
				'label' => __( 'Wrap inside blocks', 'ipt_fsqm' ),
				'value' => 'block',
			),
			array(
				'label' => __( 'Show Element Heading (Shown anyway if Wrap inside blocks is active)', 'ipt_fsqm' ),
				'value' => 'heading',
			),
			array(
				'label' => __( 'Show element Description', 'ipt_fsqm' ),
				'value' => 'description',
			),
			array(
				'label' => __( 'Show table header', 'ipt_fsqm' ),
				'value' => 'header',
			),
			array(
				'label' => __( 'Show table border', 'ipt_fsqm' ),
				'value' => 'border',
			),
			array(
				'label' => __( 'Use Google Material Charts instead of Classic Charts (for Bar & Column charts only)', 'ipt_fsqm' ),
				'value' => 'material',
			),
		);
?>
<?php if ( empty( $forms ) ) : ?>
<?php $this->ui->msg_error( __( 'Not enough data for any of the forms to populate report.', 'ipt_fsqm' ) ); ?>
<?php else : ?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'form_id', __( 'Select Form', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->select( 'form_id', $select_items, false ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Please select the form whose report you want to generate.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th scope="col">
				<?php $this->ui->generate_label( 'report', __( 'Report Type', 'ipt_fsqm' ) ); ?>
			</th>
			<td>
				<?php $this->ui->checkboxes( 'report[]', $report_type, array( 'mcq', 'freetype', 'pinfo' ), false, false, '<div class="clear"></div>' ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
				<p><?php _e( 'Please select the type of the report.', 'ipt_fsqm' ) ?></p>
				<ul class="ul-disc">
					<li><strong><?php _e( 'Survey (MCQ) Elements', 'ipt_fsqm' ); ?>:</strong> <?php _e( 'Shows survey or multiple choice type questions.', 'ipt_fsqm' ); ?></li>
					<li><strong><?php _e( 'Feedback & Upload Elements', 'ipt_fsqm' ); ?>:</strong> <?php _e( 'Shows feedback type questions.', 'ipt_fsqm' ); ?></li>
					<li><strong><?php _e( 'Other Elements', 'ipt_fsqm' ); ?>:</strong> <?php _e( 'Shows other elements.', 'ipt_fsqm' ); ?></li>
				</ul>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<?php if ( $show_data ) : ?>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'rdata', __( 'Report Data Customizations', 'ipt_fsqm' ) ); ?>
			</th>
			<td>
				<?php $this->ui->checkboxes( 'rdata[]', $report_data, array( 'data', 'names', 'others', 'sensitive', 'date' ), false, false, '<div class="clear"></div>' ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
				<p><?php _e( 'Please select the type of data shown in the report.', 'ipt_fsqm' ) ?></p>
				<ul class="ul-disc">
					<li><strong><?php _e( 'Show data alongside graphs for mcqs', 'ipt_fsqm' ); ?>:</strong> <?php _e( 'For MCQs data column would be shown alongside charts.', 'ipt_fsqm' ); ?></li>
					<li><strong><?php _e( 'Show optional meta entries for mcqs', 'ipt_fsqm' ); ?></strong>: <?php _e( 'Show optional text entries for elements like Single Options, Multiple Options, Dropdown, Smiley Rating, LikeDislike etc.', 'ipt_fsqm' ); ?></li>
					<li><strong><?php _e( 'Show names for mcq meta entries and feedbacks', 'ipt_fsqm' ) ?></strong>: <?php _e( 'Show names of contributor/user for form elements (if applicable).', 'ipt_fsqm' ); ?></li>
					<li><strong><?php _e( 'Show date for mcq meta entries and feedbacks', 'ipt_fsqm' ) ?></strong>: <?php _e( 'Show submission date of entries.', 'ipt_fsqm' ); ?></li>
					<li><strong><?php _e( 'Show sensitive data (email, phone number etc) when applicable', 'ipt_fsqm' ); ?>:</strong> <?php _e( 'For feedback and some other elements, sensitive data, like email, phone number, link to quick preview etc will be shown.', 'ipt_fsqm' ); ?></li>
				</ul>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<?php endif; ?>
		<?php if ( $show_appearance ) : ?>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'rappearance', __( 'Report Appearance', 'ipt_fsqm' ) ); ?>
			</th>
			<td>
				<?php $this->ui->checkboxes( 'rappearance[]', $report_appearance, array( 'block', 'header', 'border', 'heading', 'description' ), false, false, '<div class="clear"></div>' ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
				<p><?php _e( 'Change the appearance of the report. Wrapping inside would print a nice FSQM block styled container inside which graphs would be printed. If you simply want a chart, then disable all of the settings and uncheck "Show data alongside graphs for mcqs".', 'ipt_fsqm' ); ?></p>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<?php endif; ?>
		<tr>
			<th scope="col">
				<?php $this->ui->generate_label( 'load', __( 'Server Load:', 'ipt_fsqm' ) ); ?>
			</th>
			<td>
				<?php $this->ui->select( 'load', $server_loads, '1' ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
					<?php _e( 'Please select the calculation load for the queries.', 'ipt_fsqm' ); ?>
					<ul class="ul-disc">
						<li><strong><?php _e( 'Light Load', 'ipt_fsqm' ); ?></strong> : <?php _e( '15 queries per hit. Use this if you are experiencing problems.', 'ipt_fsqm' ); ?></li>
						<li><strong><?php _e( 'Medium Load', 'ipt_fsqm' ); ?></strong> : <?php _e( '30 queries per hit. Recommended for most of the shared hosting environments.', 'ipt_fsqm' ); ?></li>
						<li><strong><?php _e( 'Heavy Load', 'ipt_fsqm' ); ?></strong> : <?php _e( '50 queries per hit. Use only if you own a VPS or Dedicated Hosting.', 'ipt_fsqm' ); ?></li>
					</ul>
					<?php _e( 'It is recommended to go with Medium Load for most of the shared servers.', 'ipt_fsqm' ); ?>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
	</tbody>
</table>
<?php endif; ?>
		<?php
		$this->ui->iconbox_tail();
		if ( !empty( $forms ) ) {
			$this->ui->button( __( 'Select Questions', 'ipt_fsqm' ), 'select_questions', 'large', 'primary', 'normal', array(), 'submit', true, array(), array( 'value' => '1' ) );
		}
	}

	/*==========================================================================
	 * Survey Reports APIs
	 *========================================================================*/

	/**
	 *
	 */
	public function survey_select_questions( $show_chart_type = true ) {
		$keys = $this->get_keys_from_layouts_by_m_type( 'mcq', $this->layout );
		$items = array();
		if ( ! empty( $keys ) ) {
			foreach ( $keys as $key ) {
				$label = isset( $this->mcq[ $key ] ) ? $this->mcq[ $key ]['title'] : null;

				if ( null === $label ) {
					continue;
				}

				$items[] = array(
					'label' => $label,
					'value' => $key,
				);
			}
		}

		// Get the checkbox toggler
		ob_start();
		$this->ui->checkbox_toggler( 'ipt_fsqm_survey_toggler', __( 'Toggle All', 'ipt_fsqm' ), '#ipt_fsqm_survey_select_questions input.ipt_fsqm_survey_sq' );
		$toggler = ob_get_clean();
?>
<?php if ( null === $this->form_id ) : ?>
<?php $this->ui->msg_error( __( 'Invalid Form ID Supplied. Please press the back button and check again.', 'ipt_fsqm' ) ); ?>
<?php elseif ( empty( $items ) ) : ?>

<?php else : ?>
<?php $this->ui->iconbox_head( __( 'Select the Multiple Choice Type Questions', 'ipt_fsqm' ), 'checkbox-checked', $toggler ); ?>
<table class="form-table">
	<tbody>
		<tr>
			<td id="ipt_fsqm_survey_select_questions">
				<?php foreach ( $items as $item ) : ?>
					<?php
					$id = $this->ui->generate_id_from_name( '', 'mcqs_' . $item['value'] );
					$elm = $this->get_element_from_layout( array(
						'm_type' => 'mcq',
						'key' => $item['value'],
					) );
					$type = isset( $elm['type'] ) ? $elm['type'] : '';
					?>
					<div class="ipt_fsqm_rw_item">
						<div class="ipt_uif_conditional_input">
							<input data-condid="<?php echo $id . '_cond'; ?>" type="checkbox" class="ipt_uif_checkbox ipt_fsqm_survey_sq" value="<?php echo $item['value'] ?>" name="mcqs[]" id="<?php echo $id; ?>" />
							<label for="<?php echo $id; ?>"><?php echo $item['label']; ?></label>
						</div>
						<div class="clear"></div>
						<?php if ( $show_chart_type && ( isset( $this->possible_chart_types[ $type ] ) || isset( $this->possible_toggle_types[ $type ] ) ) ) : ?>
						<div id="<?php echo $id . '_cond'; ?>" class="ipt_fsqm_sci">
							<table class="form-table">
								<tbody>
									<?php
									// Show the chart type
									if ( isset( $this->possible_chart_types[ $type ] ) ) {
										echo '<tr><th>';
										$this->ui->generate_label( 'cmeta[mcq][charttype][' . $item['value'] . ']', __( 'Chart type', 'ipt_fsqm' ) );
										echo '</th><td>';
										$this->ui->select( 'cmeta[mcq][charttype][' . $item['value'] . ']', $this->possible_chart_types[ $type ], '' );
										echo '</td></tr>';
									}
									// Show the toggles
									if ( isset( $this->possible_toggle_types[ $type ] ) ) {
										foreach ( $this->possible_toggle_types[ $type ] as $ttype ) {
											echo '<tr><th>';
											$this->ui->generate_label( 'cmeta[mcq][toggles][' . $item['value'] . '][' . $ttype . ']', $this->toggle_labels[ $ttype ] );
											echo '</th><td>';
											$this->ui->toggle( 'cmeta[mcq][toggles][' . $item['value'] . '][' . $ttype . ']', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), true );
											echo '</td></tr>';
										}
									}
									?>
								</tbody>
							</table>
						</div>
						<div class="clear"></div>
						<?php endif; ?>
					</div>

				<?php endforeach; ?>
				<?php // $this->ui->checkboxes( 'mcqs[]', $items, false, false, false, '<div class="clear"></div>' ); ?>
			</td>
		</tr>
	</tbody>
</table>
<?php $this->ui->iconbox_tail(); ?>
<?php endif; ?>
		<?php
	}

	public function survey_generate_report( $mcqs, $do_data, $do_names, $do_date, $do_others, $sensitive_data, $visualization = '', $appearance = array() ) {
		if ( $this->form_id === null ) {
			$this->ui->msg_error( __( 'Invalid form ID supplied.', 'ipt_fsqm' ) );
			return;
		}

		if ( !is_array( $mcqs ) || empty( $mcqs ) ) {
			// $this->ui->msg_error( __( 'No multiple choice type questions selected and/or found in the form.', 'ipt_fsqm' ) );
			return;
		}
		if ( $visualization == '' ) {
			$visualization = __( 'Graphical Representation', 'ipt_fsqm' );
		}
		$elements = array();
		$data = array();
		$appearance = wp_parse_args( $appearance, array(
			'wrap' => false,
			'heading' => false,
			'description' => false,
			'theader' => false,
			'tborder' => false,
		) );
?>
<?php foreach ( $mcqs as $mcq ) : ?>
<div class="ipt_fsqm_report_survey_<?php echo $mcq; ?> ipt_fsqm_report_container" style="display: none;">
	<?php
	if ( $appearance['wrap'] ) {
		$this->ui->iconbox_head( $this->mcq[$mcq]['title'] . ( $this->mcq[$mcq]['subtitle'] != '' ? '<span class="subtitle">' . $this->mcq[$mcq]['subtitle'] . '</span>' : '' ), 'pie' );
		if ( $this->mcq[$mcq]['description'] != '' && $appearance['description'] ) {
			echo apply_filters( 'ipt_uif_richtext', $this->mcq[$mcq]['description'] );
		}
	} else {
		if ( $appearance['heading'] ) {
			echo '<h3 class="ipt_fsqm_report_simple_title">' . $this->mcq[$mcq]['title'] . '</h3>';
		}
		if ( $this->mcq[$mcq]['description'] != '' && $appearance['description'] ) {
			echo apply_filters( 'ipt_uif_richtext', $this->mcq[$mcq]['description'] );
		}
	}
	?>
	<?php $elements["$mcq"] = $this->mcq[$mcq]; ?>
	<?php $data["$mcq"] = $this->survey_generate_report_container( $visualization, $this->mcq[$mcq], $do_data, $do_names, $do_date, $do_others, $sensitive_data, $appearance['theader'] ); ?>
	<?php
	if ( $appearance['wrap'] ) {
		$this->ui->iconbox_tail();
	}
	?>
</div>
<?php endforeach; ?>
		<?php
		return array(
			'elements' => $elements,
			'data' => $data,
		);
	}

	public function survey_generate_report_container( $visualization, $mcq, $do_data, $do_names, $do_date, $do_others, $sensitive_data, $theader ) {
		$data = array();
		switch ( $mcq['type'] ) {
		case 'radio' :
		case 'checkbox' :
		case 'select' :
		case 'thumbselect' :
		case 'pricing_table' :
			foreach ( $mcq['settings']['options'] as $o_key => $o_val ) {
				$data["$o_key"] = 0;
			}
			$data['others'] = 0;
			$ocolspan = 1;
			if ( $do_names ) {
				$ocolspan++;
			}
			if ( $sensitive_data ) {
				$ocolspan++;
			}
			if ( $do_date ) {
				$ocolspan++;
			}

?>
<table class="ipt_fsqm_preview table_to_update">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 50%"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 50%"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<tr>
			<td style="width: 50%" class="visualization"><!-- Pie --></td>
			<?php if ( $do_data ) : ?>
			<td style="width: 50%" class="data">
				<table class="ipt_fsqm_preview">
					<thead>
						<tr>
							<th style="width: 80%"><?php _e( 'Options', 'ipt_fsqm' ); ?></th>
							<th style="width: 20%"><?php _e( 'Count', 'ipt_fsqm' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th style="width: 80%"><?php _e( 'Options', 'ipt_fsqm' ); ?></th>
							<th style="width: 20%"><?php _e( 'Count', 'ipt_fsqm' ); ?></th>
						</tr>
					</tfoot>
					<tbody>
						<?php foreach ( $mcq['settings']['options'] as $o_key => $o_val ) : ?>
						<tr>
							<th>
								<?php if ( 'pricing_table' == $mcq['type'] ) : ?>
									<?php echo $o_val['label'] . ' (' . $mcq['settings']['currency'] . number_format_i18n( $o_val['price'], 2 ) . ')'; ?>
								<?php else : ?>
									<?php if ( $mcq['type'] == 'thumbselect' ) : ?>
										<div style="text-align: center; margin: 0 0 10px 0;">
											<img style="max-width: 100%; height: auto;" src="<?php echo esc_attr( $o_val['image'] ); ?>" alt="" height="<?php echo esc_attr( $mcq['settings']['height'] ); ?>" width="<?php echo esc_attr( $mcq['settings']['width'] ); ?>" />
										</div>
									<?php endif; ?>
									<?php echo $o_val['label']; ?> <?php if ( $o_val['score'] != '' ) echo '<br /><span class="description">Score: ' . $o_val['score'] . '</span>'; ?>
								<?php endif; ?>
							</th>
							<td class="data_op_<?php echo $o_key; ?>">0</td>
						</tr>
						<?php endforeach; ?>
						<?php if ( isset( $mcq['settings']['others'] ) && $mcq['settings']['others'] == true ) : ?>
						<tr>
							<th><?php echo $mcq['settings']['o_label']; ?></th>
							<td class="data_op_others">0</td>
						</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</td>
			<?php endif; ?>
		</tr>
	</tbody>
</table>
<?php if ( isset( $mcq['settings']['others'] ) && $mcq['settings']['others'] == true && $do_others ) : ?>
<?php $this->ui->collapsible_head( $mcq['settings']['o_label'], true ); ?>
<table class="ipt_fsqm_preview others">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<th style="width: 60%"><?php _e( 'Entry', 'ipt_fsqm' ); ?></th>
			<?php if ( $do_names ) : ?>
			<th style="width: 20%"><?php _e( 'Name', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
			<?php if ( $sensitive_data ) : ?>
			<th style="width: 10%"><?php _e( 'Email', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 60%"><?php _e( 'Entry', 'ipt_fsqm' ); ?></th>
			<?php if ( $do_names ) : ?>
			<th style="width: 20%"><?php _e( 'Name', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
			<?php if ( $sensitive_data ) : ?>
			<th style="width: 10%"><?php _e( 'Email', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<tr class="empty">
			<td colspan="<?php echo $ocolspan; ?>"><?php _e( 'No data yet!', 'ipt_fsqm' ); ?></td>
		</tr>
	</tbody>
</table>
<?php $this->ui->collapsible_tail(); ?>
<?php endif; ?>
				<?php
			break;
		case 'slider' :
		case 'range' :
			$mcq = $this->sanitize_min_max_step( $mcq );
			$data = array();
?>
<table class="ipt_fsqm_preview table_to_update">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 50%"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 50%"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<tr>
			<td style="width: 50%" class="visualization"><!-- Bar --></td>
			<?php if ( $do_data ) : ?>
			<td style="width: 50%" class="data">
				<table class="ipt_fsqm_preview">
					<thead>
						<tr>
							<th style="width: 60%"><?php _e( 'Value', 'ipt_fsqm' ); ?></th>
							<th style="width: 40%"><?php _e( 'Count', 'ipt_fsqm' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th style="width: 60%"><span class="avg"><?php _e( 'N/A', 'ipt_fsqm' ); ?></span> <?php _e( 'average based on', 'ipt_fsqm' ); ?> <span class="avg_count"><?php _e( 'N/A', 'ipt_fsqm' ); ?></span> <?php _e( 'submission(s)', 'ipt_fsqm' ); ?></th>
							<th style="width: 40%"><?php _e( 'Average', 'ipt_fsqm' ); ?></th>
						</tr>
					</tfoot>
					<tbody>

					</tbody>
				</table>
			</td>
			<?php endif; ?>
		</tr>
	</tbody>
</table>
				<?php
			break;
		case 'spinners' :
		case 'grading' :
			$mcq = $this->sanitize_min_max_step( $mcq );
			foreach ( $mcq['settings']['options'] as $o_key => $o_val ) {
				$data["$o_key"] = array();
			}
?>
<table class="ipt_fsqm_preview table_to_update">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<th style="width: 50%;"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 30%"><?php _e( 'Option', 'ipt_fsqm' ); ?></th>
			<th style="width: 20%" colspan="2"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 50%;"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 30%"><?php _e( 'Option', 'ipt_fsqm' ); ?></th>
			<th style="width: 30%" colspan="2"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>

	</tbody>
</table>
				<?php
			break;
		case 'starrating' :
		case 'scalerating' :
			foreach ( $mcq['settings']['options'] as $o_key => $o_val ) {
				$data["$o_key"] = array();
			}
?>
<table class="ipt_fsqm_preview table_to_update">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<th style="width: 50%;"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 18%"><?php _e( 'Option', 'ipt_fsqm' ); ?></th>
			<th style="width: 32%" colspan="2"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 50%;"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 18%"><?php _e( 'Option', 'ipt_fsqm' ); ?></th>
			<th style="width: 32%" colspan="2"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>

	</tbody>
</table>
				<?php
			break;
		case 'smileyrating' :
			$data = array(
				'frown' => 0,
				'sad' => 0,
				'neutral' => 0,
				'happy' => 0,
				'excited' => 0,
			);
			$smiley_class_map = array(
				'frown' => 'angry2',
				'sad' => 'sad2',
				'neutral' => 'neutral2',
				'happy' => 'smiley2',
				'excited' => 'happy2',
			);
			$ocolspan = 2;
			if ( $do_names ) {
				$ocolspan++;
			}
			if ( $sensitive_data ) {
				$ocolspan++;
			}
			if ( $do_date ) {
				$ocolspan++;
			}
			?>
<table class="ipt_fsqm_preview table_to_update">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 50%"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 50%"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<tr>
			<td style="width: 50%" class="visualization"><!-- Pie --></td>
			<?php if ( $do_data ) : ?>
			<td style="width: 50%" class="data matrix">
				<table class="ipt_fsqm_preview">
					<thead>
						<tr>
							<th style="width: 80%" colspan="2"><?php _e( 'Rating', 'ipt_fsqm' ); ?></th>
							<th style="width: 20%"><?php _e( 'Count', 'ipt_fsqm' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th style="width: 80%" colspan="2"><?php _e( 'Rating', 'ipt_fsqm' ); ?></th>
							<th style="width: 20%"><?php _e( 'Count', 'ipt_fsqm' ); ?></th>
						</tr>
					</tfoot>
					<tbody>
						<?php foreach ( $data as $key => $val ) : ?>
						<tr>
							<th style="width: 10%; text-align: center"><?php $this->ui->print_icon( $smiley_class_map[$key] ); ?></th>
							<th style="width: 70%"><?php echo $mcq['settings']['labels'][$key]; ?></th>
							<td class="<?php echo $key; ?>"><?php echo $val; ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</td>
			<?php endif; ?>
		</tr>
	</tbody>
</table>
<?php if ( isset( $mcq['settings']['show_feedback'] ) && $mcq['settings']['show_feedback'] == true && $do_others ) : ?>
<?php $this->ui->collapsible_head( $mcq['settings']['feedback_label'], true ); ?>
<table class="ipt_fsqm_preview others">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<th style="width: 50%"><?php _e( 'Entry', 'ipt_fsqm' ); ?></th>
			<th style="width: 10%"><?php _e( 'Rating', 'ipt_fsqm' ); ?></th>
			<?php if ( $do_names ) : ?>
			<th style="width: 20%"><?php _e( 'Name', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
			<?php if ( $sensitive_data ) : ?>
			<th style="width: 10%"><?php _e( 'Email', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 50%"><?php _e( 'Entry', 'ipt_fsqm' ); ?></th>
			<th style="width: 10%"><?php _e( 'Rating', 'ipt_fsqm' ); ?></th>
			<?php if ( $do_names ) : ?>
			<th style="width: 20%"><?php _e( 'Name', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
			<?php if ( $sensitive_data ) : ?>
			<th style="width: 10%"><?php _e( 'Email', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<tr class="empty">
			<td colspan="<?php echo $ocolspan; ?>"><?php _e( 'No data yet!', 'ipt_fsqm' ); ?></td>
		</tr>
	</tbody>
</table>
<?php $this->ui->collapsible_tail(); ?>
<?php endif; ?>
			<?php
			break;
		case 'likedislike' :
			$data = array(
				'like' => 0,
				'dislike' => 0,
			);
			$smiley_class_map = array(
				'like' => 'thumbs-o-up',
				'dislike' => 'thumbs-o-down',
			);
			$ocolspan = 2;
			if ( $do_names ) {
				$ocolspan++;
			}
			if ( $do_date ) {
				$ocolspan++;
			}
			if ( $sensitive_data ) {
				$ocolspan++;
			}
			?>
<table class="ipt_fsqm_preview table_to_update">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 50%"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 50%"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<tr>
			<td style="width: 50%" class="visualization"><!-- Pie --></td>
			<?php if ( $do_data ) : ?>
			<td style="width: 50%" class="data matrix">
				<table class="ipt_fsqm_preview">
					<thead>
						<tr>
							<th style="width: 80%" colspan="2"><?php _e( 'Rating', 'ipt_fsqm' ); ?></th>
							<th style="width: 20%"><?php _e( 'Count', 'ipt_fsqm' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th style="width: 80%" colspan="2"><?php _e( 'Rating', 'ipt_fsqm' ); ?></th>
							<th style="width: 20%"><?php _e( 'Count', 'ipt_fsqm' ); ?></th>
						</tr>
					</tfoot>
					<tbody>
						<?php foreach ( $data as $key => $val ) : ?>
						<tr>
							<th style="width: 10%; text-align: center"><?php $this->ui->print_icon( $smiley_class_map[$key] ); ?></th>
							<th style="width: 70%"><?php echo $mcq['settings'][$key]; ?></th>
							<td class="<?php echo $key; ?>"><?php echo $val; ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</td>
			<?php endif; ?>
		</tr>
	</tbody>
</table>
<?php if ( isset( $mcq['settings']['show_feedback'] ) && $mcq['settings']['show_feedback'] == true && $do_others ) : ?>
<?php $this->ui->collapsible_head( $mcq['settings']['feedback_label'], true ); ?>
<table class="ipt_fsqm_preview others">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<th style="width: 50%"><?php _e( 'Entry', 'ipt_fsqm' ); ?></th>
			<th style="width: 10%"><?php _e( 'Rating', 'ipt_fsqm' ); ?></th>
			<?php if ( $do_names ) : ?>
			<th style="width: 20%"><?php _e( 'Name', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
			<?php if ( $sensitive_data ) : ?>
			<th style="width: 10%"><?php _e( 'Email', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 50%"><?php _e( 'Entry', 'ipt_fsqm' ); ?></th>
			<th style="width: 10%"><?php _e( 'Rating', 'ipt_fsqm' ); ?></th>
			<?php if ( $do_names ) : ?>
			<th style="width: 20%"><?php _e( 'Name', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
			<?php if ( $sensitive_data ) : ?>
			<th style="width: 10%"><?php _e( 'Email', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<tr class="empty">
			<td colspan="<?php echo $ocolspan; ?>"><?php _e( 'No data yet!', 'ipt_fsqm' ); ?></td>
		</tr>
	</tbody>
</table>
<?php $this->ui->collapsible_tail(); ?>
<?php endif; ?>
			<?php
			break;
		case 'matrix_dropdown' :
			$options_array = array();
			foreach ( $mcq['settings']['options'] as $o_key => $op ) {
				$options_array["$o_key"] = 0;
			}
			foreach ( $mcq['settings']['rows'] as $r_key => $row ) {
				$data["$r_key"] = array();
				foreach ( (array) $mcq['settings']['columns'] as $c_key => $column ) {
					$data["$r_key"]["$c_key"] = $options_array;
				}
			}
			?>
<table class="ipt_fsqm_preview table_to_update">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 50%" colspan="3"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 50%" colspan="3"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<?php foreach ( (array) $mcq['settings']['rows'] as $r_key => $row ) : ?>
		<?php if ( $theader ) : ?>
		<tr class="row-<?php echo $r_key; ?>-head head">
			<th colspan="4"><?php echo $row; ?></th>
		</tr>
		<?php endif; ?>
		<tr>
			<td style="width: 50%" class="visualization row-<?php echo $r_key; ?>" rowspan="<?php echo $do_data ? ( count( (array) $mcq['settings']['columns'] ) * count( (array) $options_array ) ) : 1; ?>"><!-- Combo --></td>
		<?php if ( $do_data ) : ?>
		<?php $last_foreach = 0; ?>
		<?php foreach ( (array) $mcq['settings']['columns'] as $c_key => $column ) : ?>
		<?php $last_foreach++; ?>
			<th style="width: 20%" rowspan="<?php echo count( $options_array ); ?>"><?php echo $column; ?></th>
		<?php $last_op_foreach = 0; ?>
		<?php foreach ( $options_array as $o_key => $op ) : ?>
		<?php $last_op_foreach++; ?>
			<td style="20%"><?php echo $mcq['settings']['options'][$o_key]['label']; ?></td>
			<td style="width: 10%" class="<?php printf( 'row-%1$d-column-%2$d-op-%3$d', $r_key, $c_key, $o_key ); ?>"><?php echo $data["$r_key"]["$c_key"]["$o_key"]; ?></td>
		<?php if ( $last_op_foreach != count( (array) $options_array ) ) : ?>
		</tr>
		<tr>
		<?php endif; ?>
		<?php endforeach; ?>
		<?php if ( $last_foreach != count( (array) $mcq['settings']['columns'] ) ) : ?>
		</tr>
		<tr>
		<?php endif; ?>
			<?php endforeach; ?>
		<?php endif; ?>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
			<?php
			break;
		case 'matrix' :
			foreach ( $mcq['settings']['rows'] as $r_key => $row ) {
				$data["$r_key"] = array();
				foreach ( $mcq['settings']['columns'] as $c_key => $column ) {
					$data["$r_key"]["$c_key"] = 0;
				}
			}
?>
<table class="ipt_fsqm_preview table_to_update">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 50%"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 50%"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<tr>
			<td style="width: 50%" class="visualization"><!-- Combo --></td>
			<?php if ( $do_data ) : ?>
			<td style="width: 50%" class="data matrix">
				<table class="ipt_fsqm_preview">
					<thead>
						<tr>
							<th></th>
							<?php foreach ( $mcq['settings']['columns'] as $c_key => $column ) : ?>
							<th><?php echo $column; ?></th>
							<?php endforeach; ?>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th></th>
							<?php foreach ( $mcq['settings']['columns'] as $c_key => $column ) : ?>
							<th><?php echo $column; ?></th>
							<?php endforeach; ?>
						</tr>
					</tfoot>
					<tbody>
						<?php foreach ( $mcq['settings']['rows'] as $r_key => $row ) : ?>
						<tr>
							<th><?php echo $row; ?></th>
							<?php foreach ( $mcq['settings']['columns'] as $c_key => $column ) : ?>
							<td class="row_<?php echo $r_key; ?>_col_<?php echo $c_key; ?>">0</td>
							<?php endforeach; ?>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</td>
			<?php endif; ?>
		</tr>
	</tbody>
</table>
				<?php
			break;
		case 'toggle' :
			$data['on'] = 0;
			$data['off'] = 0;
?>
<table class="ipt_fsqm_preview table_to_update">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 50%"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 50%"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<tr>
			<td style="width: 50%" class="visualization"><!-- Pie --></td>
			<?php if ( $do_data ) : ?>
			<td style="width: 50%" class="data">
				<table class="ipt_fsqm_preview">
					<thead>
						<tr>
							<th style="width: 80%"><?php _e( 'Options', 'ipt_fsqm' ); ?></th>
							<th style="width: 20%"><?php _e( 'Count', 'ipt_fsqm' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th style="width: 80%"><?php _e( 'Options', 'ipt_fsqm' ); ?></th>
							<th style="width: 20%"><?php _e( 'Count', 'ipt_fsqm' ); ?></th>
						</tr>
					</tfoot>
					<tbody>
						<?php foreach ( array( 'on', 'off' ) as $o_val ) : ?>
						<tr>
							<th><?php echo $mcq['settings'][$o_val]; ?></th>
							<td class="data_op_<?php echo $o_val; ?>">0</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</td>
			<?php endif; ?>
		</tr>
	</tbody>
</table>
				<?php
			break;
		case 'sorting' :
			//Too many to permute, just leave it here and choose depending on the user submissions
			//But we can just make the preset order
			$data['preset'] = 0;
			$data['other'] = 0;
			$data['orders'] = array();
?>
<table class="ipt_fsqm_preview table_to_update">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 50%"><?php _e( 'Sortings', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 50%"><?php _e( 'Sortings', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<tr>
			<td style="width: 50%" class="visualization"><!-- Pie --></td>
			<?php if ( $do_data ) : ?>
			<td style="width: 50%" class="data">
				<table class="ipt_fsqm_preview">
					<thead>
						<tr>
							<th colspan="2"><?php _e( 'Sorting', 'ipt_fsqm' ); ?></th>
							<th style="width: 50px"><?php _e( 'Count', 'ipt_fsqm' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th colspan="2"><?php _e( 'Sorting', 'ipt_fsqm' ); ?></th>
							<th style="width: 50px"><?php _e( 'Count', 'ipt_fsqm' ); ?></th>
						</tr>
					</tfoot>
					<tbody>

					</tbody>
				</table>
			</td>
			<?php endif; ?>
		</tr>
	</tbody>
</table>
				<?php
			break;
			default :
				$definition = $this->get_element_definition( $mcq );
				if( isset( $definition['callback_report'] ) && is_callable( $definition['callback_report'] ) ) {
					$data = call_user_func( $definition['callback_report'], $visualization, $mcq, $do_data, $do_names, $do_date, $do_others, $sensitive_data, $theader, $this );
				} else {
					$this->ui->msg_update( __( 'Can generate report only for built in elements.', 'ipt_fsqm' ) );
				}
		}
		return $data;
	}


	/*==========================================================================
	 * FeedBack Reports APIs
	 *========================================================================*/
	public function feedback_select_questions() {
		$keys = $this->get_keys_from_layouts_by_m_type( 'freetype', $this->layout );
		$items = array();
		if ( !empty( $keys ) ) {
			foreach ( $keys as $key ) {
				$label = isset( $this->freetype[$key] ) ? $this->freetype[$key]['title'] : null;

				if ( $label === null ) {
					continue;
				}

				$items[] = array(
					'label' => $label,
					'value' => $key,
				);
			}
		}

		ob_start();
		$this->ui->checkbox_toggler( 'ipt_fsqm_feedback_toggler', __( 'Toggle All', 'ipt_fsqm' ), '#ipt_fsqm_feedback_select_questions input.ipt_uif_checkbox' );
		$toggler = ob_get_clean();
?>
<?php if ( $this->form_id == null ) : ?>
<?php $this->ui->msg_error( __( 'Invalid Form ID Supplied. Please press the back button and check again.', 'ipt_fsqm' ) ); ?>
<?php elseif ( empty( $items ) ) : ?>

<?php else : ?>
<?php $this->ui->iconbox_head( __( 'Select the Feedback Type Questions', 'ipt_fsqm' ), 'checkbox-checked', $toggler ); ?>
<table class="form-table">
	<tbody>
		<tr>
			<td id="ipt_fsqm_feedback_select_questions">
				<?php $this->ui->checkboxes( 'freetypes[]', $items, false, false, false, '<div class="clear"></div>' ); ?>
			</td>
		</tr>
	</tbody>
</table>
<?php $this->ui->iconbox_tail(); ?>
<?php endif; ?>
		<?php
	}

	public function feedback_generate_report( $freetypes, $do_names, $do_date, $sensitive_data, $appearance = array() ) {
		if ( $this->form_id === null ) {
			$this->ui->msg_error( __( 'Invalid form ID supplied.', 'ipt_fsqm' ) );
			return;
		}

		if ( !is_array( $freetypes ) || empty( $freetypes ) ) {
			// $this->ui->msg_error( __( 'No feedback type questions selected and/or found in the form.', 'ipt_fsqm' ) );
			return;
		}
		$elements = array();
		$data = array();

		$appearance = wp_parse_args( $appearance, array(
			'wrap' => false,
			'heading' => false,
			'description' => false,
			'theader' => false,
			'tborder' => false,
		) );
?>
<?php foreach ( $freetypes as $freetype ) : ?>
<div class="ipt_fsqm_report_feedback_<?php echo $freetype; ?> ipt_fsqm_report_container" style="display: none;">
	<?php
	if ( $appearance['wrap'] ) {
		$this->ui->iconbox_head( $this->freetype[$freetype]['title'] . ( $this->freetype[$freetype]['subtitle'] != '' ? '<span class="subtitle">' . $this->freetype[$freetype]['subtitle'] . '</span>' : '' ), 'bubbles2' );
		if ( $this->freetype[$freetype]['description'] != '' && $appearance['description'] ) {
			echo apply_filters( 'ipt_uif_richtext', $this->freetype[$freetype]['description'] );
		}
	} else {
		if ( $appearance['heading'] ) {
			echo '<h3 class="ipt_fsqm_report_simple_title">' . $this->freetype[$freetype]['title'] . '</h3>';
		}
		if ( $this->freetype[$freetype]['description'] != '' && $appearance['description'] ) {
			echo apply_filters( 'ipt_uif_richtext', $this->freetype[$freetype]['description'] );
		}
	}
	?>
	<?php $elements["$freetype"] = $this->freetype[$freetype]; ?>
	<?php $data["$freetype"] = $this->feedback_generate_report_container( $this->freetype[$freetype], $do_names, $do_date, $sensitive_data, $appearance['theader'] ); ?>
	<?php
	if ( $appearance['wrap'] ) {
		$this->ui->iconbox_tail();
	}
	?>
</div>
<?php endforeach; ?>
		<?php
		return array(
			'elements' => $elements,
			'data' => $data,
		);
	}

	public function feedback_generate_report_container( $freetype, $do_names, $do_date, $sensitive_data, $theader ) {
		$data = array();
		$pinfo_titles = array(
			'name' => __( 'Name', 'ipt_fsqm' ),
			'email' => __( 'Email', 'ipt_fsqm' ),
			'phone' => __( 'Phone', 'ipt_fsqm' ),
		);
		foreach ( $this->pinfo as $pinfo ) {
			if ( in_array( $pinfo['type'], array_keys( $pinfo_titles ) ) ) {
				$pinfo_titles[$pinfo['type']] = $pinfo['title'];
			}
		}

		if ( ! $sensitive_data ) {
			unset( $pinfo_titles['email'] );
			unset( $pinfo_titles['phone'] );
		}

		if ( ! $do_names ) {
			unset( $pinfo_titles['name'] );
		}

		// NOTE: We increase pinfo count if do_date is set to true
		$pinfo_count = count( $pinfo_titles );
		if ( $do_date ) {
			$pinfo_count++;
		}

		switch( $freetype['type'] ) {
			case 'feedback_large' :
			case 'feedback_small' :
				?>
<table class="ipt_fsqm_preview table_to_update">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<th style="width: 40%;"><?php _e( 'Feedback', 'ipt_fsqm' ); ?></th>
			<?php foreach ( $pinfo_titles as $p_val ) : ?>
			<th><?php echo $p_val; ?></th>
			<?php endforeach; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 40%;"><?php _e( 'Feedback', 'ipt_fsqm' ); ?></th>
			<?php foreach ( $pinfo_titles as $p_val ) : ?>
			<th><?php echo $p_val; ?></th>
			<?php endforeach; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<tr class="empty">
			<td colspan="<?php echo ( $pinfo_count + 1 ); ?>"><?php _e( 'No data yet!', 'ipt_fsqm' ); ?></td>
		</tr>
	</tbody>
</table>
				<?php
				break;
			case 'mathematical' :
				?>
<table class="ipt_fsqm_preview table_to_update">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<th style="width: 40%;"><?php _e( 'Value', 'ipt_fsqm' ); ?></th>
			<?php foreach ( $pinfo_titles as $p_val ) : ?>
			<th><?php echo $p_val; ?></th>
			<?php endforeach; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 40%;"><?php _e( 'Value', 'ipt_fsqm' ); ?></th>
			<?php foreach ( $pinfo_titles as $p_val ) : ?>
			<th><?php echo $p_val; ?></th>
			<?php endforeach; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<tr class="empty">
			<td colspan="<?php echo ( $pinfo_count + 1 ); ?>"><?php _e( 'No data yet!', 'ipt_fsqm' ); ?></td>
		</tr>
	</tbody>

</table>
				<?php
				break;
			case 'upload' :
				$ocolspan = 1;
				if ( $do_names ) {
					$ocolspan++;
				}
				if ( $sensitive_data ) {
					$ocolspan++;
				}
				if ( $do_date ) {
					$ocolspan++;
				}
				?>
<table class="ipt_fsqm_preview table_to_update">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<?php if ( $do_names ) : ?>
			<th style="width: 30%;"><?php echo $pinfo_titles['name']; ?></th>
			<?php endif; ?>
			<?php if ( $sensitive_data ) : ?>
			<th><?php echo $pinfo_titles['email']; ?></th>
			<?php endif; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
			<th><?php _e( 'Uploads', 'ipt_fsqm' ); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<?php if ( $do_names ) : ?>
			<th style="width: 30%;"><?php echo $pinfo_titles['name']; ?></th>
			<?php endif; ?>
			<?php if ( $sensitive_data ) : ?>
			<th><?php echo $pinfo_titles['email']; ?></th>
			<?php endif; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
			<th><?php _e( 'Uploads', 'ipt_fsqm' ); ?></th>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<tr class="empty">
			<td colspan="<?php echo $ocolspan; ?>"><?php _e( 'No data yet!', 'ipt_fsqm' ); ?></td>
		</tr>
	</tbody>
</table>
				<?php
				break;
			case 'gps' :
				$ocolspan = 2;
				if ( $do_names ) {
					$ocolspan++;
				}
				if ( $sensitive_data ) {
					$ocolspan++;
				}
				if ( $do_date ) {
					$ocolspan++;
				}
				?>
<table class="ipt_fsqm_preview table_to_update">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<?php if ( $do_names ) : ?>
			<th><?php echo $pinfo_titles['name']; ?></th>
			<?php endif; ?>
			<th style="width: 50%;" colspan="2"><?php _e( 'Location', 'ipt_fsqm' ); ?></th>
			<?php if ( $sensitive_data ) : ?>
			<th><?php echo $pinfo_titles['email']; ?></th>
			<?php endif; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<?php if ( $do_names ) : ?>
			<th><?php echo $pinfo_titles['name']; ?></th>
			<?php endif; ?>
			<th style="width: 50%;" colspan="2"><?php _e( 'Location', 'ipt_fsqm' ); ?></th>
			<?php if ( $sensitive_data ) : ?>
			<th><?php echo $pinfo_titles['email']; ?></th>
			<?php endif; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<tr class="empty">
			<td colspan="<?php echo $ocolspan; ?>"><?php _e( 'No data yet!', 'ipt_fsqm' ); ?></td>
		</tr>
	</tbody>
</table>
				<?php
				break;
			case 'feedback_matrix' :
				$ocolspan = 1;
				if ( $do_names ) {
					$ocolspan++;
				}
				if ( $sensitive_data ) {
					$ocolspan++;
				}
				if ( $do_date ) {
					$ocolspan++;
				}
				?>
<table class="ipt_fsqm_preview table_to_update">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<?php if ( $do_names ) : ?>
			<th><?php echo $pinfo_titles['name']; ?></th>
			<?php endif; ?>
			<th style="width: 50%;"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
			<?php if ( $sensitive_data ) : ?>
			<th><?php echo $pinfo_titles['email']; ?></th>
			<?php endif; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<?php if ( $do_names ) : ?>
			<th><?php echo $pinfo_titles['name']; ?></th>
			<?php endif; ?>
			<th style="width: 50%;"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
			<?php if ( $sensitive_data ) : ?>
			<th><?php echo $pinfo_titles['email']; ?></th>
			<?php endif; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<tr class="empty">
			<td colspan="<?php echo $ocolspan; ?>"><?php _e( 'No data yet!', 'ipt_fsqm' ); ?></td>
		</tr>
	</tbody>
</table>
				<?php
				break;
			case 'signature' :
				?>
<table class="ipt_fsqm_preview table_to_update">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<th style="width: 40%;"><?php _e( 'Signature', 'ipt_fsqm' ); ?></th>
			<?php foreach ( $pinfo_titles as $p_val ) : ?>
			<th><?php echo $p_val; ?></th>
			<?php endforeach; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 40%;"><?php _e( 'Signature', 'ipt_fsqm' ); ?></th>
			<?php foreach ( $pinfo_titles as $p_val ) : ?>
			<th><?php echo $p_val; ?></th>
			<?php endforeach; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<tr class="empty">
			<td colspan="<?php echo ( $pinfo_count + 1 ); ?>"><?php _e( 'No data yet!', 'ipt_fsqm' ); ?></td>
		</tr>
	</tbody>
</table>
				<?php
				break;
			default :
				$definition = $this->get_element_definition( $freetype );
				if( isset( $definition['callback_report'] ) && is_callable( $definition['callback_report'] ) ) {
					$data = call_user_func( $definition['callback_report'], $freetype, $do_names, $do_date, $sensitive_data, $theader, $this );
				}
				break;
		}

		return $data;
	}

	/*==========================================================================
	 * pinfo related APIs
	 *========================================================================*/
	public function pinfo_select_questions( $show_chart_type = true ) {
		$keys = $this->get_keys_from_layouts_by_m_type( 'pinfo', $this->layout );
		$items = array();
		if ( !empty( $keys ) ) {
			foreach ( $keys as $key ) {
				$label = isset( $this->pinfo[$key] ) ? $this->pinfo[$key]['title'] : null;

				if ( $label === null ) {
					continue;
				}

				$items[] = array(
					'label' => $label,
					'value' => $key,
				);
			}
		}

		ob_start();
		$this->ui->checkbox_toggler( 'ipt_fsqm_pinfo_toggler', __( 'Toggle All', 'ipt_fsqm' ), '#ipt_fsqm_pinfo_select_questions input.ipt_fsqm_pinfo_sq' );
		$toggler = ob_get_clean();
?>
<?php if ( $this->form_id == null ) : ?>
<?php $this->ui->msg_error( __( 'Invalid Form ID Supplied. Please press the back button and check again.', 'ipt_fsqm' ) ); ?>
<?php elseif ( empty( $items ) ) : ?>

<?php else : ?>
<?php $this->ui->iconbox_head( __( 'Select the Other Form Elements', 'ipt_fsqm' ), 'checkbox-checked', $toggler ); ?>
<table class="form-table">
	<tbody>
		<tr>
			<td id="ipt_fsqm_pinfo_select_questions">
				<?php foreach ( $items as $item ) : ?>
					<?php
					$id = $this->ui->generate_id_from_name( '', 'pinfos_' . $item['value'] );
					$elm = $this->get_element_from_layout( array(
						'm_type' => 'pinfo',
						'key' => $item['value'],
					) );
					$type = isset( $elm['type'] ) ? $elm['type'] : '';
					?>
					<div class="ipt_fsqm_rw_item">
						<div class="ipt_uif_conditional_input">
							<input data-condid="<?php echo $id . '_cond'; ?>" type="checkbox" class="ipt_uif_checkbox ipt_fsqm_pinfo_sq" value="<?php echo $item['value'] ?>" name="pinfos[]" id="<?php echo $id; ?>" />
							<label for="<?php echo $id; ?>"><?php echo $item['label']; ?></label>
						</div>
						<div class="clear"></div>
						<?php if ( $show_chart_type && ( isset( $this->possible_chart_types[ $type ] ) || isset( $this->possible_toggle_types[ $type ] ) ) ) : ?>
						<div id="<?php echo $id . '_cond'; ?>" class="ipt_fsqm_sci">
							<table class="form-table">
								<tbody>
									<?php
									// Show the chart type
									if ( isset( $this->possible_chart_types[ $type ] ) ) {
										echo '<tr><th>';
										$this->ui->generate_label( 'cmeta[pinfo][charttype][' . $item['value'] . ']', __( 'Chart type', 'ipt_fsqm' ) );
										echo '</th><td>';
										$this->ui->select( 'cmeta[pinfo][charttype][' . $item['value'] . ']', $this->possible_chart_types[ $type ], '' );
										echo '</td></tr>';
									}
									// Show the toggles
									if ( isset( $this->possible_toggle_types[ $type ] ) ) {
										foreach ( $this->possible_toggle_types[ $type ] as $ttype ) {
											echo '<tr><th>';
											$this->ui->generate_label( 'cmeta[pinfo][toggles][' . $item['value'] . '][' . $ttype . ']', $this->toggle_labels[ $ttype ] );
											echo '</th><td>';
											$this->ui->toggle( 'cmeta[pinfo][toggles][' . $item['value'] . '][' . $ttype . ']', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), true );
											echo '</td></tr>';
										}
									}
									?>
								</tbody>
							</table>
						</div>
						<div class="clear"></div>
						<?php endif; ?>
					</div>

				<?php endforeach; ?>
				<?php // $this->ui->checkboxes( 'pinfos[]', $items, false, false, false, '<div class="clear"></div>' ); ?>
			</td>
		</tr>
	</tbody>
</table>
<?php $this->ui->iconbox_tail(); ?>
<?php endif; ?>
		<?php
	}

	public function pinfo_generate_report( $pinfos, $do_data, $do_names, $do_date, $do_others, $sensitive_data, $visualization = '', $appearance = array() ) {
		if ( null === $this->form_id ) {
			$this->ui->msg_error( __( 'Invalid form ID supplied.', 'ipt_fsqm' ) );
			return;
		}

		if ( ! is_array( $pinfos ) || empty( $pinfos ) ) {
			// $this->ui->msg_error( __( 'No other elements selected and/or found in the form.', 'ipt_fsqm' ) );
			return;
		}
		if ( $visualization == '' ) {
			$visualization = __( 'Graphical Representation', 'ipt_fsqm' );
		}
		$elements = array();
		$data = array();

		$appearance = wp_parse_args( $appearance, array(
			'wrap' => false,
			'heading' => false,
			'description' => false,
			'theader' => false,
			'tborder' => false,
		) );
?>
<?php foreach ( $pinfos as $pinfo ) : ?>
<div class="ipt_fsqm_report_pinfo_<?php echo $pinfo; ?> ipt_fsqm_report_container" style="display: none;">
	<?php
	if ( $appearance['wrap'] ) {
		$this->ui->iconbox_head( $this->pinfo[$pinfo]['title'] . ( $this->pinfo[$pinfo]['subtitle'] != '' ? '<span class="subtitle">' . $this->pinfo[$pinfo]['subtitle'] . '</span>' : '' ), 'pie' );
		if ( $this->pinfo[$pinfo]['description'] != '' && $appearance['description'] ) {
			echo apply_filters( 'ipt_uif_richtext', $this->pinfo[$pinfo]['description'] );
		}
	} else {
		if ( $appearance['heading'] ) {
			echo '<h3 class="ipt_fsqm_report_simple_title">' . $this->pinfo[$pinfo]['title'] . '</h3>';
		}
		if ( $this->pinfo[$pinfo]['description'] != '' && $appearance['description'] ) {
			echo apply_filters( 'ipt_uif_richtext', $this->pinfo[$pinfo]['description'] );
		}
	}
	?>
	<?php $elements[ "$pinfo" ] = $this->pinfo[ $pinfo ]; ?>
	<?php $data[ "$pinfo" ] = $this->pinfo_generate_report_container( $visualization, $this->pinfo[ $pinfo ], $do_data, $do_names, $do_date, $do_others, $sensitive_data, $appearance['theader'] ); ?>
	<?php
	if ( $appearance['wrap'] ) {
		 $this->ui->iconbox_tail();
	}
	?>
</div>
<?php endforeach; ?>
		<?php
		return array(
			'elements' => $elements,
			'data' => $data,
		);
	}

	public function pinfo_generate_report_container( $visualization, $pinfo, $do_data, $do_names, $do_date, $do_others, $sensitive_data, $theader ) {
		$data = array();
		$pinfo_titles = array();
		$pinfo_titles = array(
			'name' => __( 'Name', 'ipt_fsqm' ),
			'email' => __( 'Email', 'ipt_fsqm' ),
			'phone' => __( 'Phone', 'ipt_fsqm' ),
		);
		foreach ( $this->pinfo as $pinfod ) {
			if ( in_array( $pinfod['type'], array_keys( $pinfo_titles ) ) ) {
				$pinfo_titles[$pinfod['type']] = $pinfod['title'];
			}
		}

		if ( ! $do_names ) {
			unset( $pinfo_titles['name'] );
		}

		if ( ! $sensitive_data ) {
			unset( $pinfo_titles['email'] );
			unset( $pinfo_titles['phone'] );
		}

		$header_colspan = count( $pinfo_titles );
		// NOTE: We increase header_colspan if do_date is true
		if ( $do_date ) {
			$header_colspan++;
		}

		switch ( $pinfo['type'] ) {
			default :
				$definition = $this->get_element_definition( $pinfo );
				if( isset( $definition['callback_report'] ) && is_callable( $definition['callback_report'] ) ) {
					$data = call_user_func( $definition['callback_report'], $visualization, $pinfo, $do_data, $do_names, $do_date, $do_others, $sensitive_data, $theader, $this );
				} else {
					$this->ui->msg_update( __( 'Can generate report only for built in elements.', 'ipt_fsqm' ) );
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
			case 'datetime' :
			case 'hidden' :
				?>
<table class="ipt_fsqm_preview table_to_update">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<th style="width: 40%;"><?php _e( 'Entry', 'ipt_fsqm' ); ?></th>
			<?php foreach ( $pinfo_titles as $p_val ) : ?>
			<th><?php echo $p_val; ?></th>
			<?php endforeach; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 40%;"><?php _e( 'Entry', 'ipt_fsqm' ); ?></th>
			<?php foreach ( $pinfo_titles as $p_val ) : ?>
			<th><?php echo $p_val; ?></th>
			<?php endforeach; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<tr class="empty">
			<td colspan="<?php echo ( $header_colspan + 1 ); ?>"><?php _e( 'No data yet!', 'ipt_fsqm' ); ?></td>
		</tr>
	</tbody>

</table>
				<?php
				break;
			case 'address' :
				?>
<table class="ipt_fsqm_preview table_to_update">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<th style="width: 20%;"><?php echo $pinfo['settings']['recipient']; ?></th>
			<?php foreach ( array( 'line_one', 'line_two', 'line_three', 'country' ) as $ad_key ) : ?>
			<th style="width: 10%"><?php echo $pinfo['settings'][ $ad_key ]; ?></th>
			<?php endforeach; ?>
			<?php foreach ( $pinfo_titles as $p_val ) : ?>
			<th><?php echo $p_val; ?></th>
			<?php endforeach; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 20%;"><?php echo $pinfo['settings']['recipient']; ?></th>
			<?php foreach ( array( 'line_one', 'line_two', 'line_three', 'country' ) as $ad_key ) : ?>
			<th style="width: 10%"><?php echo $pinfo['settings'][ $ad_key ]; ?></th>
			<?php endforeach; ?>
			<?php foreach ( $pinfo_titles as $p_val ) : ?>
			<th><?php echo $p_val; ?></th>
			<?php endforeach; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<tr class="empty">
			<td colspan="<?php echo ( $header_colspan + 5 ); ?>"><?php _e( 'No data yet!', 'ipt_fsqm' ); ?></td>
		</tr>
	</tbody>

</table>
				<?php
				break;
			// Payment
			case 'payment' :
				?>
<table class="ipt_fsqm_preview table_to_update">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<th><?php _e( 'Invoice ID', 'ipt_fsqm' ); ?></th>
			<th><?php _e( 'Status', 'ipt_fsqm' ); ?></th>
			<th><?php _e( 'Txn ID', 'ipt_fsqm' ); ?></th>
			<th><?php _e( 'Gateway', 'ipt_fsqm' ); ?></th>
			<th><?php _e( 'Total', 'ipt_fsqm' ); ?></th>
			<?php foreach ( $pinfo_titles as $p_val ) : ?>
			<th><?php echo $p_val; ?></th>
			<?php endforeach; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th><?php _e( 'Invoice ID', 'ipt_fsqm' ); ?></th>
			<th><?php _e( 'Status', 'ipt_fsqm' ); ?></th>
			<th><?php _e( 'Txn ID', 'ipt_fsqm' ); ?></th>
			<th><?php _e( 'Gateway', 'ipt_fsqm' ); ?></th>
			<th><?php _e( 'Total', 'ipt_fsqm' ); ?></th>
			<?php foreach ( $pinfo_titles as $p_val ) : ?>
			<th><?php echo $p_val; ?></th>
			<?php endforeach; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<tr class="empty">
			<td colspan="<?php echo ( $header_colspan + 5 ); ?>"><?php _e( 'No data yet!', 'ipt_fsqm' ); ?></td>
		</tr>
	</tbody>
</table>
				<?php
				break;
			// MCQ
			case 'p_radio' :
			case 'p_checkbox' :
			case 'p_select' :
				// Prepare the data
				foreach ( $pinfo['settings']['options'] as $o_key => $o_val ) {
					$data["$o_key"] = 0;
				}
				$data['others'] = 0;
				$ocolspan = 1;
				if ( $do_names ) {
					$ocolspan++;
				}
				if ( $sensitive_data ) {
					$ocolspan++;
				}
				if ( $do_date ) {
					$ocolspan++;
				}
				?>
<table class="ipt_fsqm_preview table_to_update">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 50%"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 50%"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<tr>
			<td style="width: 50%" class="visualization"><!-- Pie --></td>
			<?php if ( $do_data ) : ?>
			<td style="width: 50%" class="data">
				<table class="ipt_fsqm_preview">
					<thead>
						<tr>
							<th style="width: 80%"><?php _e( 'Options', 'ipt_fsqm' ); ?></th>
							<th style="width: 20%"><?php _e( 'Count', 'ipt_fsqm' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th style="width: 80%"><?php _e( 'Options', 'ipt_fsqm' ); ?></th>
							<th style="width: 20%"><?php _e( 'Count', 'ipt_fsqm' ); ?></th>
						</tr>
					</tfoot>
					<tbody>
						<?php foreach ( $pinfo['settings']['options'] as $o_key => $o_val ) : ?>
						<tr>
							<th>
								<?php echo $o_val['label']; ?>
							</th>
							<td class="data_op_<?php echo $o_key; ?>">0</td>
						</tr>
						<?php endforeach; ?>
						<?php if ( isset( $pinfo['settings']['others'] ) && $pinfo['settings']['others'] == true ) : ?>
						<tr>
							<th><?php echo $pinfo['settings']['o_label']; ?></th>
							<td class="data_op_others">0</td>
						</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</td>
			<?php endif; ?>
		</tr>
	</tbody>
</table>
<?php if ( isset( $pinfo['settings']['others'] ) && $pinfo['settings']['others'] == true && $do_others ) : ?>
<?php $this->ui->collapsible_head( $pinfo['settings']['o_label'], true ); ?>
<table class="ipt_fsqm_preview others">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<th style="width: 60%"><?php _e( 'Entry', 'ipt_fsqm' ); ?></th>
			<?php if ( $do_names ) : ?>
			<th style="width: 20%"><?php _e( 'Name', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
			<?php if ( $sensitive_data ) : ?>
			<th style="width: 10%"><?php _e( 'Email', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 60%"><?php _e( 'Entry', 'ipt_fsqm' ); ?></th>
			<?php if ( $do_names ) : ?>
			<th style="width: 20%"><?php _e( 'Name', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
			<?php if ( $sensitive_data ) : ?>
			<th style="width: 10%"><?php _e( 'Email', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<tr class="empty">
			<td colspan="<?php echo $ocolspan; ?>"><?php _e( 'No data yet!', 'ipt_fsqm' ); ?></td>
		</tr>
	</tbody>
</table>
<?php $this->ui->collapsible_tail(); ?>
<?php endif; ?>
				<?php
				break;
			// Single State
			case 's_checkbox' :
				// Prepare the data
				$data = array(
					'checked' => 0,
					'unchecked' => 0,
				);
				?>
<table class="ipt_fsqm_preview table_to_update">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 50%"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 50%"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<tr>
			<td style="width: 50%" class="visualization"><!-- Pie --></td>
			<?php if ( $do_data ) : ?>
			<td style="width: 50%" class="data">
				<table class="ipt_fsqm_preview">
					<thead>
						<tr>
							<th style="width: 80%"><?php _e( 'State', 'ipt_fsqm' ); ?></th>
							<th style="width: 20%"><?php _e( 'Count', 'ipt_fsqm' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th style="width: 80%"><?php _e( 'State', 'ipt_fsqm' ); ?></th>
							<th style="width: 20%"><?php _e( 'Count', 'ipt_fsqm' ); ?></th>
						</tr>
					</tfoot>
					<tbody>
						<tr>
							<th style="width: 80%"><?php _e( 'Checked', 'ipt_fsqm' ); ?></th>
							<td style="width: 20%" class="data_op_checked">0</td>
						</tr>
						<tr>
							<th style="width: 80%"><?php _e( 'Unchecked', 'ipt_fsqm' ); ?></th>
							<td style="width: 20%" class="data_op_unchecked">0</td>
						</tr>
					</tbody>
				</table>
			</td>
			<?php endif; ?>
		</tr>
	</tbody>
</table>
				<?php
				break;
			// Sorting
			case 'p_sorting' :
			// Too many to permute, just leave it here and choose depending on the user submissions
			// But we can just make the preset order
			$data['preset'] = 0;
			$data['other'] = 0;
			$data['orders'] = array();
?>
<table class="ipt_fsqm_preview table_to_update">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 50%"><?php _e( 'Sortings', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<?php if ( $do_data ) : ?>
			<th style="width: 50%"><?php _e( 'Sortings', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<tr>
			<td style="width: 50%" class="visualization"><!-- Pie --></td>
			<?php if ( $do_data ) : ?>
			<td style="width: 50%" class="data">
				<table class="ipt_fsqm_preview">
					<thead>
						<tr>
							<th colspan="2"><?php _e( 'Sorting', 'ipt_fsqm' ); ?></th>
							<th style="width: 50px"><?php _e( 'Count', 'ipt_fsqm' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th colspan="2"><?php _e( 'Sorting', 'ipt_fsqm' ); ?></th>
							<th style="width: 50px"><?php _e( 'Count', 'ipt_fsqm' ); ?></th>
						</tr>
					</tfoot>
					<tbody>

					</tbody>
				</table>
			</td>
			<?php endif; ?>
		</tr>
	</tbody>
</table>
				<?php
				break;

			case 'guestblog' :
				?>
<table class="ipt_fsqm_preview table_to_update">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<th style="width: 40%;"><?php _e( 'Entry', 'ipt_fsqm' ); ?></th>
			<?php foreach ( $pinfo_titles as $p_val ) : ?>
			<th rowspan="3"><?php echo $p_val; ?></th>
			<?php endforeach; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%" rowspan="3"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 40%;"><?php _e( 'Entry', 'ipt_fsqm' ); ?></th>
			<?php foreach ( $pinfo_titles as $p_val ) : ?>
			<th rowspan="3"><?php echo $p_val; ?></th>
			<?php endforeach; ?>
			<?php if ( $do_date ) : ?>
			<th rowspan="3" style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<tr class="empty">
			<td colspan="<?php echo ( $header_colspan + 1 ); ?>"><?php _e( 'No data yet!', 'ipt_fsqm' ); ?></td>
		</tr>
	</tbody>

</table>
				<?php
				break;
			case 'repeatable' :
				?>
<table class="ipt_fsqm_preview table_to_update">
	<?php if ( $theader ) : ?>
	<thead>
		<tr>
			<th style="width: 40%;"><?php _e( 'Entry', 'ipt_fsqm' ); ?></th>
			<?php foreach ( $pinfo_titles as $p_val ) : ?>
			<th rowspan="3"><?php echo $p_val; ?></th>
			<?php endforeach; ?>
			<?php if ( $do_date ) : ?>
			<th style="width: 10%" rowspan="3"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 40%;"><?php _e( 'Entry', 'ipt_fsqm' ); ?></th>
			<?php foreach ( $pinfo_titles as $p_val ) : ?>
			<th rowspan="3"><?php echo $p_val; ?></th>
			<?php endforeach; ?>
			<?php if ( $do_date ) : ?>
			<th rowspan="3" style="width: 10%"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<?php endif; ?>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<tr class="empty">
			<td colspan="<?php echo ( $header_colspan + 1 ); ?>"><?php _e( 'No data yet!', 'ipt_fsqm' ); ?></td>
		</tr>
	</tbody>
</table>
				<?php
				break;
		}

		return $data;
	}


	/*==========================================================================
	 * Filters for labels
	 *========================================================================*/

	public function apply_label_filters() {
		if ( ! empty( $this->mcq ) && is_array( $this->mcq ) ) :
		foreach ( $this->mcq as $e_key => $element ) {
			$this->mcq[$e_key]['title'] = apply_filters( 'ipt_uif_label', $element['title'] );
			$this->mcq[$e_key]['subtitle'] = apply_filters( 'ipt_uif_label', $element['subtitle'] );
			if ( isset( $element['settings'] ) && isset( $element['settings']['options'] ) && is_array( $element['settings']['options'] ) ) {
				foreach ( $element['settings']['options'] as $o_key => $op ) {
					if ( is_array( $op ) ) {
						if ( isset( $op['label'] ) ) {
							$this->mcq[$e_key]['settings']['options'][$o_key]['label'] = apply_filters( 'ipt_uif_label', $op['label'] );
						}
					} elseif ( is_string( $op ) ) {
						$this->mcq[$e_key]['settings']['options'][$o_key] = apply_filters( 'ipt_uif_label', $op );
					}
				}
			}
			if ( isset( $element['settings'] ) && isset( $element['settings']['rows'] ) && is_array( $element['settings']['rows'] ) ) {
				foreach ( $element['settings']['rows'] as $r_key => $row ) {
					if ( is_string( $row ) ) {
						$this->mcq[$e_key]['settings']['rows'][$r_key] = apply_filters( 'ipt_uif_label', $row );
					}
				}
			}
			if ( isset( $element['settings'] ) && isset( $element['settings']['columns'] ) && is_array( $element['settings']['columns'] ) ) {
				foreach ( $element['settings']['columns'] as $r_key => $row ) {
					if ( is_string( $row ) ) {
						$this->mcq[$e_key]['settings']['columns'][$r_key] = apply_filters( 'ipt_uif_label', $row );
					}
				}
			}
		}
		unset( $e_key, $element );
		endif;

		if ( ! empty( $this->feedback ) && is_array( $this->feedback ) ) :
		foreach ( $this->feedback as $e_key => $element ) {
			$this->feedback[$e_key]['title'] = apply_filters( 'ipt_uif_label', $element['title'] );
			$this->feedback[$e_key]['subtitle'] = apply_filters( 'ipt_uif_label', $element['subtitle'] );
		}
		unset( $e_key, $element );
		endif;

		if ( ! empty( $this->design ) && is_array( $this->design ) ) :
		foreach ( $this->design as $e_key => $element ) {
			$this->design[$e_key]['title'] = apply_filters( 'ipt_uif_label', $element['title'] );
			$this->design[$e_key]['subtitle'] = apply_filters( 'ipt_uif_label', $element['subtitle'] );
		}
		unset( $e_key, $element );
		endif;

		if ( ! empty( $this->pinfo ) && is_array( $this->pinfo ) ) :
		foreach ( $this->pinfo as $e_key => $element ) {
			$this->pinfo[$e_key]['title'] = apply_filters( 'ipt_uif_label', $element['title'] );
			$this->pinfo[$e_key]['subtitle'] = apply_filters( 'ipt_uif_label', $element['subtitle'] );
			if ( isset( $element['settings'] ) && isset( $element['settings']['options'] ) && is_array( $element['settings']['options'] ) ) {
				foreach ( $element['settings']['options'] as $o_key => $op ) {
					if ( is_array( $op ) ) {
						if ( isset( $op['label'] ) ) {
							$this->pinfo[$e_key]['settings']['options'][$o_key]['label'] = apply_filters( 'ipt_uif_label', $op['label'] );
						}
					} elseif ( is_string( $op ) ) {
						$this->pinfo[$e_key]['settings']['options'][$o_key] = apply_filters( 'ipt_uif_label', $op );
					}
				}
			}
		}
		endif;
	}
}
