<?php
/**
 * IPT FSQM All Forms
 *
 * Class for handling the All Forms page under eForm
 *
 * @author Swashata <swashata4u@gmail.com>
 * @package eForm - WordPress Form Builder
 * @subpackage Admin\AllForms
 * @codeCoverageIgnore
 */
class IPT_FSQM_All_Forms extends IPT_FSQM_Admin_Base {
	public $table_view;
	public $form_data;
	/**
	 * Form Element Admin Instance
	 *
	 * @var IPT_FSQM_Form_Elements_Admin
	 */
	public $form_element_admin;

	public function __construct() {
		$this->capability = 'manage_feedback';
		$this->action_nonce = 'ipt_fsqm_form_view_all_nonce';

		parent::__construct();

		$this->icon = 'insert-template';
		add_filter( 'set-screen-option', array( &$this, 'table_set_option' ), 10, 3 );

		$this->post_result[4] = array(
			'type' => 'update',
			'msg' => __( 'Successfully deleted the forms', 'ipt_fsqm' ),
		);
		$this->post_result[5] = array(
			'type' => 'error',
			'msg' => __( 'Please select an action', 'ipt_fsqm' ),
		);
		$this->post_result[6] = array(
			'type' => 'update',
			'msg' => __( 'Successfully deleted the form', 'ipt_fsqm' ),
		);
		$this->post_result[7] = array(
			'type' => 'update',
			'msg' => __( 'Successfully added the form', 'ipt_fsqm' ),
		);
		$this->post_result[8] = array(
			'type' => 'error',
			'msg' => __( 'Could not delete the forms. Please contact developer if problem persists', 'ipt_fsqm' ),
		);
		$this->post_result[9] = array(
			'type' => 'error',
			'msg' => __( 'Could not delete the forms. Please contact developer if problem persists', 'ipt_fsqm' ),
		);
		$this->post_result[10] = array(
			'type' => 'update',
			'msg' => __( 'Successfully updated the form', 'ipt_fsqm' ),
		);
		$this->post_result[11] = array(
			'type' => 'update',
			'msg' => __( 'Successfully copied the form', 'ipt_fsqm' ),
		);

		if ( isset( $_GET['form_id'] ) ) {
			$this->form_element_admin = new IPT_FSQM_Form_Elements_Admin( (int) $_GET['form_id'] );
		} else {
			$this->form_element_admin = new IPT_FSQM_Form_Elements_Admin();
		}

		add_action( 'wp_ajax_' . $this->admin_post_action, array( $this->form_element_admin, 'ajax_save' ) );
		add_action( 'wp_ajax_ipt_fsqm_submission_download', array( $this, 'ajax_csv_download' ) );
		add_action( 'wp_ajax_eform_builder_preview', [ $this, 'ajax_init_sync_page' ] );
	}

	public function ajax_init_sync_page() {
		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'eform_builder_preview' ) ) {
			wp_die( 'Cheating?' );
		}
		$preview = new EForm_Live_View( $_GET['form_id'] );
		$preview->preview();
		die();
	}

	public function admin_menu() {
		$page_title = __( 'View all Forms', 'ipt_fsqm' );
		if ( isset( $_GET['form_id'] ) ) {
			$page_title = __( 'Edit Form', 'ipt_fsqm' );
		}
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', $page_title, __( 'View all Forms', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_all_forms', array( &$this, 'index' ) );
		parent::admin_menu();
	}

	public function index() {
		if ( isset( $_GET['form_id'] ) ) {
			$this->index_head( __( 'eForm <span class="ipt-icomoon-arrow-right2"></span> Update Form <a href="admin.php?page=ipt_fsqm_all_forms" class="add-new-h2">Go Back</a>', 'ipt_fsqm' ), true, 'none', false );
			if ( $this->form_element_admin->form_id != $_GET['form_id'] ) {
				$this->ui->msg_error( __( 'Invalid form ID provided.', 'ipt_fsqm' ) );
			} else {
				$this->form_element_admin->show_form();
			}
			$this->index_foot( false );
		} else {
			$this->index_head( __( 'eForm <span class="ipt-icomoon-arrow-right2"></span> View all forms', 'ipt_fsqm' ) . '<a href="admin.php?page=ipt_fsqm_new_form" class="add-new-h2">' . __( 'Add New', 'ipt_fsqm' ) . '</a>', false );
			$this->table_view->prepare_items();
?>
<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
	<div class="ipt_uif_box cyan">
		<h3><span class="ipt-icomoon-pencil"></span><?php _e( 'Modify and/or View Forms', 'ipt_fsqm' ); ?></h3>
	</div>
	<div class="ipt_uif_iconbox_inner">
		<form action="" method="get">
			<?php foreach ( $_GET as $k => $v ) : ?>
				<?php if ( $k == 'order' || $k == 'orderby' || $k == 'page' ) : ?>
					<input type="hidden" name="<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $v ); ?>" />
				<?php endif; ?>
			<?php endforeach; ?>
			<?php $this->table_view->search_box( __( 'Search Forms', 'ipt_fsqm' ), 'search_id' ); ?>
			<?php $this->table_view->display(); ?>
		</form>
	</div>
</div>
			<?php
			$this->index_foot();
		}
	}

	/**
	 *
	 *
	 * @global wpdb $wpdb
	 * @global array $ipt_fsqm_info
	 */
	public function save_post( $check_referer = true ) {
		parent::save_post();
		$this->form_element_admin->process_save();
		wp_redirect( add_query_arg( array( 'post_result' => '10' ), $_POST['_wp_http_referer'] ) );
		die();
	}

	public function on_load_page() {
		global $wpdb, $ipt_fsqm_info;

		$this->table_view = new IPT_FSQM_Form_Table();
		$action = $this->table_view->current_action();
		if ( $action == 'delete' ) {
			if ( isset( $_GET['id'] ) ) {
				if ( !wp_verify_nonce( $_GET['_wpnonce'], 'ipt_fsqm_form_delete_' . $_GET['id'] ) ) {
					wp_die( __( 'Cheatin&#8217; uh?' ) );
				}
				if ( IPT_FSQM_Form_Elements_Static::delete_forms( $_GET['id'] ) ) {
					wp_redirect( add_query_arg( array( 'post_result' => '6' ), 'admin.php?page=ipt_fsqm_all_forms' ) );
				} else {
					wp_redirect( add_query_arg( array( 'post_result' => '9' ), 'admin.php?page=ipt_fsqm_all_forms' ) );
				}
			} else {
				if ( !wp_verify_nonce( $_GET['_wpnonce'], 'bulk-ipt_fsqm_form_items' ) ) {
					wp_die( __( 'Cheatin&#8217; uh?' ) );
				}

				if ( IPT_FSQM_Form_Elements_Static::delete_forms( $_GET['forms'] ) ) {
					wp_redirect( add_query_arg( array( 'post_result' => '4' ), $_GET['_wp_http_referer'] ) );
				} else {
					wp_redirect( add_query_arg( array( 'post_result' => '8' ), $_GET['_wp_http_referer'] ) );
				}
			}
			die();
		} else if ( $action == 'copy' ) {
				if ( !wp_verify_nonce( $_GET['_wpnonce'], 'ipt_fsqm_form_copy_' . $_GET['id'] ) ) {
					wp_die( __( 'Cheatin&#8217; uh?' ) );
				}

				IPT_FSQM_Form_Elements_Static::copy_form( $_GET['id'] );
				wp_redirect( add_query_arg( array( 'post_result' => '11' ), 'admin.php?page=ipt_fsqm_all_forms' ) );
				die();
			}

		if ( !empty( $_GET['_wp_http_referer'] ) ) {
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ) ) );
			die();
		}

		$option = 'per_page';
		$args = array(
			'label' => __( 'Forms per page', 'ipt_fsqm' ),
			'default' => 20,
			'option' => 'feedback_forms_per_page',
		);
		add_screen_option( $option, $args );

		parent::on_load_page();

		if ( isset( $_GET['form_id'] ) ) {
			$this->form_element_admin->add_help();
			$this->form_element_admin->enqueue_assets();
		} else {
			get_current_screen()->add_help_tab( array(
					'id'  => 'overview',
					'title'  => __( 'Overview', 'ipt_fsqm' ),
					'content' =>
					'<p>' . __( 'This screen provides access to all of your forms. You can customize the display of this screen to suit your workflow.', 'ipt_fsqm' ) . '</p>' .
					'<p>' . __( 'By default, this screen will show all the forms. Please check the Screen Content for more information.', 'ipt_fsqm' ) . '</p>'
				) );
			get_current_screen()->add_help_tab( array(
					'id'  => 'screen-content',
					'title'  => __( 'Screen Content', 'ipt_fsqm' ),
					'content' =>
					'<p>' . __( 'You can customize the display of this screen&#8217;s contents in a number of ways:' ) . '</p>' .
					'<ul>' .
					'<li>' . __( 'You can sort forms based on total submissions or last updated.', 'ipt_fsqm' ) . '</li>' .
					'<li>' . __( 'You can hide/display columns based on your needs and decide how many forms to list per screen using the Screen Options tab.', 'ipt_fsqm' ) . '</li>' .
					'<li>' . __( 'You can search a particular form by using the Search Form. You can type in just the name.', 'ipt_fsqm' ) . '</li>' .
					'</ul>'
				) );
			get_current_screen()->add_help_tab( array(
					'id'  => 'action-links',
					'title'  => __( 'Available Actions', 'ipt_fsqm' ),
					'content' =>
					'<p>' . __( 'Hovering over a row in the posts list will display action links that allow you to manage your submissions. You can perform the following actions:', 'ipt_fsqm' ) . '</p>' .
					'<ul>' .
					'<li>' . __( '<strong>View Submissions</strong> will take you to a page from where you can see all the submissions under that form.', 'ipt_fsqm' ) . '</li>' .
					'<li>' . __( '<strong>Edit</strong> lets you recustomize the form.', 'ipt_fsqm' ) . '</li>' .
					'<li>' . __( '<strong>Delete</strong> removes your from this list as well as from the database along with all the submissions under it. You can not restore it back, so make sure you want to delete it before you do.', 'ipt_fsqm' ) . '</li>' .
					'<li>' . __( '<strong>Copy</strong> creates a copy of the form.', 'ipt_fsqm' ) . '</li>' .
					'</ul>'
				) );
			get_current_screen()->add_help_tab( array(
					'id'  => 'bulk-actions',
					'title'  => __( 'Bulk Actions', 'ipt_fsqm' ),
					'content' =>
					'<p>' . __( 'There are a number of bulk actions available. Here are the details.', 'ipt_fsqm' ) . '</p>' .
					'<ul>' .
					'<li>' . __( '<strong>Delete</strong>. This will permanently delete the ticked forms from the database along with all the submissions under it.', 'ipt_fsqm' ) . '</li>' .
					'</ul>'
				) );
		}

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);
	}

	public function table_set_option( $status, $option, $value ) {
		return $value;
	}

	public function ajax_csv_download() {
		global $wpdb, $ipt_fsqm_info;

		// Cap check
		if ( ! current_user_can( 'manage_feedback' ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}

		// Get form ID and nonce
		$form_id = (int) @$_REQUEST['form_id'];
		$nonce = @$_REQUEST['_wpnonce'];

		// Nonce check
		if ( ! wp_verify_nonce( $nonce, 'ipt_fsqm_submission_download_' . $form_id ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}

		// Get all data ids
		$data_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d", $form_id ) ); // WPCS: unprepared SQL ok.

		if ( empty( $data_ids ) ) {
			wp_die( __( 'No submissions yet!', 'ipt_fsqm' ), __( 'Export Error' ) );
		}

		$big_data = array(); //Psst: Do not blame me for the name

		$form = new IPT_FSQM_Form_Elements_Base( $form_id );

		// Loop through and add the question titles
		$headings = array();

		// Info
		$headings[] = __( 'Submission ID', 'ipt_fsqm' );
		$headings[] = __( 'User ID', 'ipt_fsqm' );
		$headings[] = __( 'Submission Date and Time', 'ipt_fsqm' );
		$headings[] = __( 'First Name', 'ipt_fsqm' );
		$headings[] = __( 'Last Name', 'ipt_fsqm' );
		$headings[] = __( 'Email', 'ipt_fsqm' );

		// Loop through the MCQ
		foreach ( $form->mcq as $m_key => $mcq ) {
			$headings[] = $mcq['title'];
		}

		// Loop through the Feedback
		foreach ( $form->freetype as $f_key => $freetype ) {
			$headings[] = $freetype['title'];
		}

		// Loop through the Pinfo
		foreach ( $form->pinfo as $p_key => $pinfo ) {
			if ( in_array( $pinfo['type'], array( 'f_name', 'l_name', 'email' ) ) ) {
				continue;
			}
			$headings[] = $pinfo['title'];
		}

		// Others
		$headings[] = __( 'IP Address', 'ipt_fsqm' );
		if ( '' != $form->settings['general']['comment_title'] ) {
			$headings[] = $form->settings['general']['comment_title'];
		}

		// Score
		$headings[] = __( 'Score', 'ipt_fsqm' );
		$headings[] = __( 'Max Score', 'ipt_fsqm' );

		// Referer
		$headings[] = __( 'Referer', 'ipt_fsqm' );
		// URL Track
		$headings[] = __( 'URL Track', 'ipt_fsqm' );

		// Time
		$headings[] = __( 'Time', 'ipt_fsqm' );

		// Link
		$headings[] = __( 'Link', 'ipt_fsqm' );

		$big_data[] = $headings;
		unset( $headings );

		// Now loop through all IDs and create the array with data
		foreach ( $data_ids as $data_id ) {
			$data_row = array();
			$data = new IPT_eForm_Form_Elements_Values( $data_id );
			if ( is_null( $data->data_id ) ) {
				continue;
			}

			// Info
			$data_row[] = $data->data_id;
			$data_row[] = $data->data->user_id;
			$data_row[] = $data->data->date;
			$data_row[] = $data->data->f_name;
			$data_row[] = $data->data->l_name;
			$data_row[] = $data->data->email;

			// Loop through the MCQ
			foreach ( $form->mcq as $m_key => $mcq ) {
				$data_row[] = $data->get_value( 'mcq', $m_key, 'string', 'label' );
			}

			// Loop through the Feedback
			foreach ( $form->freetype as $f_key => $freetype ) {
				$data_row[] = $data->get_value( 'freetype', $f_key, 'string', 'label' );
			}

			// Loop through the Pinfo
			foreach ( $form->pinfo as $p_key => $pinfo ) {
				if ( in_array( $pinfo['type'], array( 'f_name', 'l_name', 'email' ) ) ) {
					continue;
				}
				$data_row[] = $data->get_value( 'pinfo', $p_key, 'string', 'label' );
			}

			$data_row[] = $data->data->ip;
			if ( '' != $form->settings['general']['comment_title'] ) {
				$data_row[] = $data->data->comment;
			}

			// Score
			$data_row[] = $data->data->score;
			$data_row[] = $data->data->max_score;

			// Referer
			$data_row[] = $data->data->referer;
			// URL Track
			$data_row[] = $data->data->url_track;

			// Time
			$data_row[] = $data->data->time;

			// Link
			$data_row[] = admin_url( 'admin.php?page=ipt_fsqm_view_submission&id=' . $data->data_id );

			// Add
			$big_data[] = $data_row;
		}

		$csv = $this->array_to_csv( $big_data );

		// Start the download header
		if ( function_exists( 'mb_strlen' ) ) {
		    $size = mb_strlen( $csv, '8bit' );
		} else {
		    $size = strlen( $csv );
		}
		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename=csv-export-' . $form_id . '.csv' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . $size );

		echo $csv;

		die();
	}

	protected function array_to_csv( $array ) {
		if ( empty( $array ) ) {
			return '';
		}
		ob_start();
		$csv = fopen( 'php://output', 'w' );
		foreach ( $array as $row ) {
			fputcsv( $csv, $row );
		}
		fclose( $csv );
		return ob_get_clean();
	}
}
