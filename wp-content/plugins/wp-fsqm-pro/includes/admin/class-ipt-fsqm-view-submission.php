<?php
/**
 * IPT FSQM View a Submission
 *
 * Class for handling the View a Submission page under eForm
 *
 * @author Swashata <swashata4u@gmail.com>
 * @package eForm - WordPress Form Builder
 * @subpackage Admin\ViewSubmission
 * @codeCoverageIgnore
 */
class IPT_FSQM_View_Submission extends IPT_FSQM_Admin_Base {

	public function __construct() {
		$this->capability = 'view_feedback';
		$this->action_nonce = 'ipt_fsqm_view_nonce';
		parent::__construct();

		$this->icon = 'newspaper';
	}

	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'View a Submission', 'ipt_fsqm' ), __( 'View a Submission', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_view_submission', array( &$this, 'index' ) );
		parent::admin_menu();
	}
	public function index() {
		$ui_state = 'back';
		if ( isset( $_GET['id'] ) || isset( $_GET['id2'] ) ) {
			$ui_state = 'clear';
		}
		$this->index_head( __( 'eForm <span class="ipt-icomoon-arrow-right2"></span> View a Submission', 'ipt_fsqm' ), false, $ui_state );
		if ( isset( $_GET['id'] ) || isset( $_GET['id2'] ) ) {
			$this->show_submission();
		} else {
			$this->show_form();
		}
		$this->index_foot();
	}

	/**
	 *
	 *
	 * @global wpdb $wpdb
	 * @global array $ipt_fsqm_info
	 */
	public function save_post( $check_referer = true ) {
		parent::save_post();
		die();
	}

	public function on_load_page() {
		parent::on_load_page();
		get_current_screen()->add_help_tab( array(
				'id' => 'overview',
				'title' => __( 'Overview', 'ipt_fsqm' ),
				'content' =>
				'<p>' . __( 'Using this page, you can view/edit a particular submission either by it\'s ID (which is mailed to the notification email when a submission is being submitted) Or select one from the latest 100.', 'ipt_fsqm' ) . '</p>',
			) );
		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);
	}

	private function show_submission() {
		$id = !empty( $_GET['id'] ) ? (int) $_GET['id'] : $_GET['id2'];
		$edit = isset( $_GET['edit'] ) ? true : false;
		$form = new IPT_FSQM_Form_Elements_Front( $id );

		if ( $edit ) {
			if ( !current_user_can( 'manage_feedback' ) ) {
				wp_die( __( 'Cheatin&#8217; uh?' ) );
			}
			$form->show_form( true, true );
		} else {
			IPT_FSQM_Form_Elements_Static::ipt_fsqm_full_preview( $id );
		}

	}

	private function show_form() {
		global $wpdb, $ipt_fsqm_info;
		$s = array();
		$last100 = $wpdb->get_results( "SELECT d.f_name f_name, d.l_name l_name, d.id id, f.name name FROM {$ipt_fsqm_info['data_table']} d LEFT JOIN {$ipt_fsqm_info['form_table']} f ON d.form_id = f.id ORDER BY `date` DESC LIMIT 0, 100" );
		if ( empty( $last100 ) ) {
			$this->ui->msg_error( __( 'There are no submissions in the database. Please be patient!', 'ipt_fsqm' ) );
			return;
		}

		foreach ( $last100 as $l ) {
			$s[$l->id] = $l->f_name . ' ' . $l->l_name . ' - ' . $l->name;
		}
		$buttons = array(
			array( __( 'View', 'ipt_fsqm' ), 'view', 'medium', 'primary', 'normal', array(), 'submit' ),
		);
		if ( current_user_can( 'manage_feedback' ) ) {
			$buttons[] = array( __( 'Edit', 'ipt_fsqm' ), 'edit', 'medium', 'secondary', 'normal', array( 'equal-height' ), 'submit' );
		}
?>
<?php $this->print_p_update( __( 'Please either enter an ID or select one from the latest 100', 'ipt_fsqm' ) ); ?>
<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
	<div class="ipt_uif_box cyan">
		<h3><span class="ipt-icomoon-menu"></span><?php _e( 'Select a Submission', 'ipt_fsqm' ); ?></h3>
	</div>
	<div class="ipt_uif_iconbox_inner">

		<form action="" method="get">
			<?php foreach ( $_GET as $k => $v ) : ?>
			<input type="hidden" name="<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $v ); ?>" />
			<?php endforeach; ?>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="id"><?php _e( 'Enter the ID', 'ipt_fsqm' ); ?></label>
						</th>
						<td>
							<?php $this->print_input_text( 'id', '', 'regular-text code' ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="id2"><?php _e( 'Or Select One', 'ipt_fsqm' ); ?></label>
						</th>
						<td>
							<select name="id2" id="id2" class="ipt_uif_select">
								<?php $this->print_select_op( $s, null, true ); ?>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
			<?php $this->ui->buttons( $buttons ); ?>
		</form>
	</div>
</div>
		<?php
	}
}
