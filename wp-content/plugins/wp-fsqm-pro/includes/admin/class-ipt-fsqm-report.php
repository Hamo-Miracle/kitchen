<?php
/**
 * IPT FSQM Report & Analysis
 *
 * Class for handling the Reports & Analysis page under eForm
 *
 * @author Swashata <swashata4u@gmail.com>
 * @package eForm - WordPress Form Builder
 * @subpackage Admin\Report
 * @codeCoverageIgnore
 */
class IPT_FSQM_Report extends IPT_FSQM_Admin_Base {
	public $form_elements_utilities;
	public function __construct() {
		$this->capability = 'view_feedback';
		$this->action_nonce = 'ipt_fsqm_survey_report_nonce';
		parent::__construct();
		$this->icon = 'stats';
		$this->form_elements_utilities = new IPT_FSQM_Form_Elements_Utilities();

		//Add the ajax for Survey
		add_action( 'wp_ajax_ipt_fsqm_survey_report', array( $this->form_elements_utilities, 'report_ajax' ) );
	}

	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'Generate Report for Forms', 'ipt_fsqm' ), __( 'Report &amp; Analysis', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_report', array( &$this, 'index' ) );
		parent::admin_menu();
	}
	public function index() {
		$this->index_head( __( 'eForm <span class="ipt-icomoon-arrow-right2"></span> Report &amp; Analysis', 'ipt_fsqm' ), false );
		$this->form_elements_utilities->report_index();
		$this->index_foot( false );
	}

	public function on_load_page() {
		parent::on_load_page();
		get_current_screen()->add_help_tab( array(
				'id' => 'overview',
				'title' => __( 'Overview', 'ipt_fsqm' ),
				'content' =>
				'<p>' . __( 'This page provides a nice way to view all the survey reports from beginning to end. As this can be a bit database expensive, so reports are pulled 15/30/50 at a time, depending on the server load. You will need JavaScript to view this page.', 'ipt_fsqm' ) . '</p>' .
				'<p>' . __( 'This part of eForm works like a wizard which takes you through the steps necessary to generate just the part of the report you wish to see.', 'ipt_fsqm' ) . '</p>' .
				'<p>' . __( 'Please check out the other help items for more information.', 'ipt_fsqm' ) . '</p>'

			) );
		get_current_screen()->add_help_tab( array(
				'id' => 'first_step',
				'title' => __( 'Selecting Form', 'ipt_fsqm' ),
				'content' =>
				'<p>' . __( 'In this page you have the following options to get started.', 'ipt_fsqm' ) . '</p>' .
				'<ul>' .
				'<li>' . __( '<strong>Select Form:</strong> Select the form for which you want to generate the report.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Report Type:</strong> Please select the type of the report.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Server Load:</strong> Select the load on your server. For shared hosts, Medium Load is recommended.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Custom Date Range:</strong> Tick and select a range of date.', 'ipt_fsqm' ) . '</li>' .
				'</ul>' .
				'<p>' . __( 'Once done, simply click on the <strong>Select Questions</strong> button.', 'ipt_fsqm' ) . '</p>'
			) );
		get_current_screen()->add_help_tab( array(
				'id' => 'second_step',
				'title' => __( 'Selecting Questions', 'ipt_fsqm' ),
				'content' =>
				'<p>' . __( 'From this page, you will be able to select questions for which you want to generate the report.', 'ipt_fsqm' ) . '</p>' .
				'<ul>' .
				'<li>' . __( '<strong>Select the Multiple Choice Type Questions:</strong> This will list down all the MCQs in your form in proper order. Select the one you like.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Select the Feedback Questions:</strong> This will list down all the feedbacks in your form in proper order. Select the one you like.', 'ipt_fsqm' ) . '</li>' .
				'</ul>'

			) );
		get_current_screen()->add_help_tab( array(
				'id' => 'third_step',
				'title' => __( 'Generate Report', 'ipt_fsqm' ),
				'content' =>
				'<p>' . __( 'Now all you have to do it wait until the progress bar reaches 100%. Once done, it will show you the reports of all the questions you have selected in a tabular fashion with charts whenever applicable.', 'ipt_fsqm' ) . '</p>' .
				'<p>' . __( 'If you want to take a printout then scroll to the bottom of the page and click on the big print button.', 'ipt_fsqm' ) . '</p>' .
				'<p>' . __( 'If you wish to put something on this site, then simply use the <strong>Insert Trends</strong> from the eForm editor button.', 'ipt_fsqm' ) . '</p>'

			) );
		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);
	}
}
