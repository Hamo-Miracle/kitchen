<?php
/**
 * IPT FSQM View All Submissions
 *
 * Class for handling the View All Submissions page under eForm
 *
 * @author Swashata <swashata4u@gmail.com>
 * @package eForm - WordPress Form Builder
 * @subpackage Admin\ViewAllSubmission
 * @codeCoverageIgnore
 */
class IPT_FSQM_View_All_Submissions extends IPT_FSQM_Admin_Base {
	/**
	 * The feedback table class object
	 * Should be instantiated on-load
	 *
	 * @var IPT_FSQM_Data_Table
	 */
	public $table_view;
	public function __construct() {
		$this->capability = 'manage_feedback';
		$this->action_nonce = 'ipt_fsqm_view_all_nonce';

		parent::__construct();
		$this->icon = 'newspaper';
		add_filter( 'set-screen-option', array( $this, 'table_set_option' ), 10, 3 );

		$this->post_result[4] = array(
			'type' => 'update',
			'msg' => __( 'Successfully deleted the submissions', 'ipt_fsqm' ),
		);
		$this->post_result[5] = array(
			'type' => 'error',
			'msg' => __( 'Please select an action', 'ipt_fsqm' ),
		);
		$this->post_result[6] = array(
			'type' => 'update',
			'msg' => __( 'Successfully deleted the submission', 'ipt_fsqm' ),
		);

		$this->post_result[7] = array(
			'type' => 'update',
			'msg' => __( 'Successfully updated the submission', 'ipt_fsqm' ),
		);
		$this->post_result[8] = array(
			'type' => 'update',
			'msg' => __( 'An error has occured updating the submission. Either you haven\'t changed anything or something terrible has happened. Please contact the developer', 'ipt_fsqm' ),
		);
		$this->post_result[9] = array(
			'type' => 'update',
			'msg' => __( 'Successfully starred the submissions', 'ipt_fsqm' ),
		);
		$this->post_result[10] = array(
			'type' => 'update',
			'msg' => __( 'Successfully unstarred the submissions', 'ipt_fsqm' ),
		);
		$this->post_result[11] = array(
			'type' => 'error',
			'msg' => __( 'Please select some submissions to perform the action', 'ipt_fsqm' ),
		);

		add_action( 'wp_ajax_ipt_fsqm_star', array( &$this, 'ajax_star' ) );
	}

	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'View all Submissions', 'ipt_fsqm' ), __( 'View all Submissions', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_view_all_submissions', array( &$this, 'index' ) );
		parent::admin_menu();
	}

	public function index() {
		$this->index_head( __( 'eForm <span class="ipt-icomoon-arrow-right2"></span> View All Submissions', 'ipt_fsqm' ), false );
		$this->table_view->prepare_items();
?>
<style type="text/css">
	.column-star {
		width: 50px;
	}
	.column-title {
		width: 300px;
	}
</style>
<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
	<div class="ipt_uif_box cyan">
		<h3><span class="ipt-icomoon-pencil"></span><?php _e( 'Modify and/or View Submissions', 'ipt_fsqm' ); ?></h3>
	</div>
	<div class="ipt_uif_iconbox_inner">
		<form action="" method="get">
			<?php foreach ( $_GET as $k => $v ) : ?>
				<?php if ( $k == 'order' || $k == 'orderby' || $k == 'page' ) : ?>
				<input type="hidden" name="<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $v ); ?>" />
				<?php endif; ?>
			<?php endforeach; ?>
			<?php $this->table_view->search_box( __( 'Search Submissions', 'ipt_fsqm' ), 'search_id' ); ?>
			<?php $this->table_view->display(); ?>
		</form>
	</div>
</div>
<script type="text/javascript">
(function($) {
	$(document).ready(function() {
		var _ipt_fsqm_nonce = '<?php echo wp_create_nonce( 'ipt_fsqm_star' ); ?>';
		$('a.ipt_fsqm_star').click(function(e) {
			e.preventDefault();
			var $this = this;
			$(this).html('<img src="<?php echo admin_url( '/images/wpspin_light.gif' ); ?>" />');
			var data_id = $(this).parent().siblings('th').find('input').attr('value');
			var data = {
				'id' : data_id,
				'action' : 'ipt_fsqm_star',
				'_wpnonce' : _ipt_fsqm_nonce
			};
			$.post(ajaxurl, data, function(response) {
				$($this).html(response.html);
				_ipt_fsqm_nonce = response.nonce;
			}, 'json');
		});
	});
})(jQuery);
</script>
		<?php
		$this->index_foot();
	}

	public function save_post( $check_referer = true ) {
		parent::save_post();
	}

	public function ajax_star() {
		global $wpdb, $ipt_fsqm_info;
		$id = $_REQUEST['id'];
		$nonce = $_REQUEST['_wpnonce'];
		if ( !wp_verify_nonce( $nonce, 'ipt_fsqm_star' ) ) {
			echo json_encode( array( 'html' => '<img title="Invalid Nonce. Cheating uh?" src="' . plugins_url( '/static/admin/images/error.png', IPT_FSQM_Loader::$abs_file ) . '" />', 'nonce' => 'boundtoFAIL' ) );
			die();
		}

		$data = $wpdb->get_var( $wpdb->prepare( "SELECT star FROM {$ipt_fsqm_info['data_table']} WHERE id = %d", $id ) );
		if ( null == $data ) {
			echo json_encode( array( 'html' => '<img title="Invalid ID associtated. Try Again?" src="' . plugins_url( '/static/admin/images/error.png', IPT_FSQM_Loader::$abs_file ) . '" />', 'nonce' => wp_create_nonce( 'ipt_fsqm_star' ) ) );
			die();
		}

		if ( 0 == $data ) {
			IPT_FSQM_Form_Elements_Static::star_submissions( $id );
			echo json_encode( array( 'html' => '<img title="' . __( 'Click to Unstar', 'ipt_fsqm' ) . '" src="' . plugins_url( '/static/admin/images/star_on.png', IPT_FSQM_Loader::$abs_file ) . '" />', 'nonce' => wp_create_nonce( 'ipt_fsqm_star' ) ) );
		} else {
			IPT_FSQM_Form_Elements_Static::unstar_submissions( $id );
			echo json_encode( array( 'html' => '<img title="' . __( 'Click to Star', 'ipt_fsqm' ) . '" src="' . plugins_url( '/static/admin/images/star_off.png', IPT_FSQM_Loader::$abs_file ) . '" />', 'nonce' => wp_create_nonce( 'ipt_fsqm_star' ) ) );
		}
		die();
	}

	public function on_load_page() {
		global $wpdb, $ipt_fsqm_info;

		$this->table_view = new IPT_FSQM_Data_Table();

		if ( $_SERVER['REQUEST_METHOD'] == 'POST' )
			$this->save_post ();

		$action = $this->table_view->current_action();

		if ( false !== $action ) {
			//check if single delete request
			if ( isset( $_GET['id'] ) ) {
				if ( wp_verify_nonce( $_GET['_wpnonce'], 'ipt_fsqm_delete_' . $_GET['id'] ) ) {
					IPT_FSQM_Form_Elements_Static::delete_submissions( $_GET['id'] );
					wp_redirect( add_query_arg( array( 'post_result' => 6 ), 'admin.php?page=' . $_GET['page'] ) );
				} else {
					wp_die( __( 'Cheatin&#8217; uh?' ) );
				}
				die();
			} else {
				//bulk actions
				if ( !wp_verify_nonce( $_GET['_wpnonce'], 'bulk-ipt_fsqm_table_items' ) ) {
					wp_die( __( 'Cheatin&#8217; uh?' ) );
				}

				if ( empty( $_GET['feedbacks'] ) ) {
					wp_redirect( add_query_arg( array( 'post_result' => 11 ), $_GET['_wp_http_referer'] ) );
					die();
				}


				switch ( $action ) {
				case 'delete' :
					if ( IPT_FSQM_Form_Elements_Static::delete_submissions( $_GET['feedbacks'] ) ) {
						wp_redirect( add_query_arg( array( 'post_result' => 4 ), $_GET['_wp_http_referer'] ) );
					} else {
						wp_redirect( add_query_arg( array( 'post_result' => 2 ), $_GET['_wp_http_referer'] ) );
					}
					break;
				case 'star' :
					if ( IPT_FSQM_Form_Elements_Static::star_submissions( $_GET['feedbacks'] ) ) {
						wp_redirect( add_query_arg( array( 'post_result' => 9 ), $_GET['_wp_http_referer'] ) );
					} else {
						wp_redirect( add_query_arg( array( 'post_result' => 2 ), $_GET['_wp_http_referer'] ) );
					}
					break;
				case 'unstar' :
					if ( IPT_FSQM_Form_Elements_Static::unstar_submissions( $_GET['feedbacks'] ) ) {
						wp_redirect( add_query_arg( array( 'post_result' => 10 ), $_GET['_wp_http_referer'] ) );
					} else {
						wp_redirect( add_query_arg( array( 'post_result' => 2 ), $_GET['_wp_http_referer'] ) );
					}
					break;
				default :
					wp_redirect( add_query_arg( array( 'post_result' => 5 ), $_GET['_wp_http_referer'] ) );
				}
				die();
			}
		}

		//clean up the URL
		if ( !empty( $_GET['_wp_http_referer'] ) ) {
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ) ) );
			die();
		}

		$option = 'per_page';
		$args = array(
			'label' => __( 'Submissions per page', 'ipt_fsqm' ),
			'default' => 20,
			'option' => 'feedbacks_per_page',
		);
		add_screen_option( $option, $args );
		parent::on_load_page();

		get_current_screen()->add_help_tab( array(
				'id'  => 'overview',
				'title'  => __( 'Overview' ),
				'content' =>
				'<p>' . __( 'This screen provides access to all of your submissions & surveys. You can customize the display of this screen to suit your workflow.', 'ipt_fsqm' ) . '</p>' .
				'<p>' . __( 'By default, this screen will show all the submissions and submissions of all the available forms. Please check the Screen Content for more information.', 'ipt_fsqm' ) . '</p>'
			) );
		get_current_screen()->add_help_tab( array(
				'id'  => 'screen-content',
				'title'  => __( 'Screen Content' ),
				'content' =>
				'<p>' . __( 'You can customize the display of this screen&#8217;s contents in a number of ways:' ) . '</p>' .
				'<ul>' .
				'<li>' . __( 'You can select a particular form and filter submissions on that form only.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( 'You can hide/display columns based on your needs and decide how many submissions to list per screen using the Screen Options tab.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( 'You can search a particular submission by using the Search Form. You can type in just the first name or the last name or the email or the ID or even the IP Address.', 'ipt_fsqm' ) . '</li>' .
				'</ul>'
			) );
		get_current_screen()->add_help_tab( array(
				'id'  => 'action-links',
				'title'  => __( 'Available Actions' ),
				'content' =>
				'<p>' . __( 'Hovering over a row in the posts list will display action links that allow you to manage your submissions. You can perform the following actions:', 'ipt_fsqm' ) . '</p>' .
				'<ul>' .
				'<li>' . __( '<strong>Quick Preview</strong>: Pops up a modal window with the detailed preview of the particular submission. You can also print the submission if you wish to.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Full View</strong>: Opens up a page where you can view the form along with the submission data.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Edit Submission</strong>: Lets you edit all the aspects of the submission. Most importantly you can add administrator remarks which will be shown on the track page.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Delete</strong> removes the submission from this list as well as from the database. You can not restore it back, so make sure you want to delete it before you do.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Star Column</strong> lets you star/unstar a submission. Simply click on the star to toggle.', 'ipt_fsqm' ) . '</li>' .
				'</ul>'
			) );
		get_current_screen()->add_help_tab( array(
				'id'  => 'bulk-actions',
				'title'  => __( 'Bulk Actions' ),
				'content' =>
				'<p>' . __( 'There are a number of bulk actions available. Here are the details.', 'ipt_fsqm' ) . '</p>' .
				'<ul>' .
				'<li>' . __( '<strong>Delete</strong>. This will permanently delete the ticked submissions from the database.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Mark Starred</strong>. This will mark the submissions starred.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Mark Unstarred</strong>. This will mark the submissions unstarred.', 'ipt_fsqm' ) . '</li>' .
				'</ul>'
			) );

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);
	}

	public function table_set_option( $status, $option, $value ) {
		return $value;
	}
}
