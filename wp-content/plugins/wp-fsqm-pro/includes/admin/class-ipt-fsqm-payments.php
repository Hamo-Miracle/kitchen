<?php
/**
 * IPT FSQM Payments
 *
 * Class for handling the Payments page under eForm
 *
 * @author Swashata <swashata4u@gmail.com>
 * @package eForm - WordPress Form Builder
 * @subpackage Admin\Payments
 * @codeCoverageIgnore
 */
class IPT_FSQM_Payments extends IPT_FSQM_Admin_Base {
	/**
	 * @var        IPT_Payments_Table  $table_view  payment table class
	 */
	public $table_view;

	public function __construct() {
		$this->capability = 'manage_feedback';
		$this->action_nonce = 'ipt_fsqm_payment_nonce';

		parent::__construct();

		$this->icon = 'file-text';
		add_filter( 'set-screen-option', array( $this, 'table_set_option' ), 10, 3 );

		$this->post_result[4] = array(
			'type' => 'okay',
			'msg' => __( 'Successfully updated the payment.', 'ipt_fsqm' ),
		);

		$this->post_result[5] = array(
			'type' => 'okay',
			'msg' => __( 'Successfully deleted the payment.', 'ipt_fsqm' ),
		);
		$this->post_result[6] = array(
			'type' => 'okay',
			'msg' => __( 'Successfully deleted the payments.', 'ipt_fsqm' ),
		);

		// Add ajax actions
		add_action( 'wp_ajax_ipt_fsqm_payment_set_status', [ $this, 'ajax_set_status' ], 10 );
	}

	public function ajax_set_status() {
		if ( ! current_user_can( 'manage_feedback' ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}
		$id = @$_REQUEST['id'];
		$status = @$_REQUEST['status'];
		// Verify nonce
		if ( ! wp_verify_nonce( @$_REQUEST['_wpnonce'], 'eform_set_' . $status . '_' . $id ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}
		// All set, now set the status
		$payment_offline = EForm_Payment_Handler_Offline::instance();
		$success = $payment_offline->set_payment_status( $id, $status );
		$return = [
			'success' => $success,
			'msg' => __( 'Something went wrong, please retry.', 'ipt_fsqm' ),
		];
		if ( $success ) {
			$return['msg'] = __( 'Operation successful', 'ipt_fsqm' );
		}
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		echo json_encode( $return );
		die();
	}

	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'eForm Payments', 'ipt_fsqm' ), __( 'Payments', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_payments', array( $this, 'index' ) );
		parent::admin_menu();
	}

	public function index() {
		$this->index_head( __( 'eForm <span class="ipt-icomoon-arrow-right2"></span> Payments', 'ipt_fsqm' ), false );
		$this->table_view->prepare_items();
		?>
		<style type="text/css">
			.column-title {
				width: 250px;
			}
			.column-txn {
				width: 300px;
			}
		</style>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$('#eform-payments-box').on( 'click', 'a.payment-action', function( e ) {
					e.preventDefault();
					var elm = $( this ),
					link = elm.attr( 'href' ),
					main = elm.closest( '.row-actions' ),
					helper = $( '<div>Please Wait...</div>' );
					main.before( helper );
					elm.hide();
					$.ajax({
						url: link,
						type: 'GET',
						dataType: 'json',
						data: {param1: 'value1'},
					})
					.done( function( data ) {
						helper.html( data.msg );
						elm.remove();
					} )
					.fail(function() {
						helper.html( 'Error' );
						elm.show();
					})
					.always(function() {
						setTimeout( function() {
							helper.fadeOut( 'slow', function() {
								helper.remove();
							} );
						}, 2000 );
					});
				} );
			});
		</script>
		<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
			<div class="ipt_uif_box cyan">
				<h3><span class="ipt-icomoon-pencil"></span><?php _e( 'Modify and/or View Payments', 'ipt_fsqm' ); ?></h3>
			</div>
			<div class="ipt_uif_iconbox_inner" id="eform-payments-box">
				<form action="" method="get">
					<?php foreach ( $_GET as $k => $v ) : ?>
						<?php if ( $k == 'order' || $k == 'orderby' || $k == 'page' ) : ?>
						<input type="hidden" name="<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $v ); ?>" />
						<?php endif; ?>
					<?php endforeach; ?>
					<?php $this->table_view->search_box( __( 'Search Tranasction ID', 'ipt_fsqm' ), 'search_id' ); ?>
					<?php $this->table_view->display(); ?>
				</form>
				<div class="clear"></div>
			</div>
		</div>
		<?php $this->index_foot( false );
	}

	public function save_post( $check_referer = true ) {
		parent::save_post( $check_referer );
	}

	public function on_load_page() {
		global $wpdb, $ipt_fsqm_info;

		$this->table_view = new IPT_FSQM_Payments_Table();

		if ( !empty( $_GET['_wp_http_referer'] ) ) {
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ) ) );
			die();
		}

		$option = 'per_page';
		$args = array(
			'label' => __( 'Payments per page', 'ipt_fsqm' ),
			'default' => 20,
			'option' => 'fsqm_payment_per_page',
		);

		add_screen_option( $option, $args );


		parent::on_load_page();

		get_current_screen()->add_help_tab( array(
			'id'  => 'overview',
			'title'  => __( 'Overview' ),
			'content' =>
			'<p>' . __( 'This screen provides access to all of your payments. You can customize the display of this screen to suit your workflow.', 'ipt_fsqm' ) . '</p>' .
			'<p>' . __( 'By default, this screen will show all the payments of all the available forms. Please check the Screen Content for more information.', 'ipt_fsqm' ) . '</p>'
		) );
		get_current_screen()->add_help_tab( array(
			'id'  => 'screen-content',
			'title'  => __( 'Screen Content' ),
			'content' =>
			'<p>' . __( 'You can customize the display of this screen&#8217;s contents in a number of ways:' ) . '</p>' .
			'<ul>' .
			'<li>' . __( 'You can select a particular form and filter payments on that form only.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( 'You can select a particular payment method and filter payments on that method only.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( 'You can select a particular payment status and filter payments on that status only.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( 'You can hide/display columns based on your needs and decide how many payments to list per screen using the Screen Options tab.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( 'You can search a particular payment by transaction ID through the Search Form.', 'ipt_fsqm' ) . '</li>' .
			'</ul>'
		) );
		get_current_screen()->add_help_tab( array(
			'id'  => 'action-links',
			'title'  => __( 'Available Actions' ),
			'content' =>
			'<p>' . __( 'Hovering over a row in the payments list will display action links that allow you to manage your payments. You can perform the following actions:', 'ipt_fsqm' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<strong>Quick Preview</strong>: Pops up a modal window with the detailed preview of the particular payment. You can also print the payment if you wish to.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>Full View</strong>: Opens up a page where you can view the form along with the payment data.', 'ipt_fsqm' ) . '</li>' .
			'</ul>'
		) );
	}

	public function table_set_option( $status, $option, $value ) {
		return $value;
	}
}
