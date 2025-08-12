<?php
/**
 * Payments Table
 *
 * A child class of WP_List_Table
 *
 * Used for showing all payments records available under eForm > Payments
 *
 * @author Swashata <swashata4u@gmail.com>
 * @package eForm - WordPress Form Builder
 * @subpackage ListTables\Payments
 * @codeCoverageIgnore
 */
class IPT_FSQM_Payments_Table extends IPT_FSQM_Listtable {
	public function __construct() {
		parent::__construct( array(
			'singular' => 'ipt_fsqm_payment_item',
			'plural' => 'ipt_fsqm_payment_items',
			'ajax' => true,
		) );
	}

	public function get_columns() {
		$columns = array(
			'title' => __( 'Name', 'ipt_fsqm' ),
			'txn' => __( 'Transaction ID', 'ipt_fsqm' ),
			'user_id' => __( 'User', 'ipt_fsqm' ),
			'email' => __( 'Email', 'ipt_fsqm' ),
			'date' => __( 'Date', 'ipt_fsqm' ),
			'amount' => __( 'Amount', 'ipt_fsqm' ),
			'currency' => __( 'Currency', 'ipt_fsqm' ),
			'mode' => __( 'Gateway', 'ipt_fsqm' ),
			'status' => __( 'Status', 'ipt_fsqm' ),
			'form' => __( 'Form', 'ipt_fsqm' ),
		);
		return $columns;
	}

	public function get_sortable_columns() {
		$sortable = array(
			'title' => array( 'd.f_name', false ),
			'date' => array( 'p.date', true ),
			'user_id' => array( 'p.user_id', false ),
			'email' => array( 'd.email', false ),
			'amount' => array( 'p.amount', true ),
			'mode' => array( 'p.mode', false ),
			'status' => array( 'p.status', true ),
			'currency' => array( 'p.currency', false ),
			'form' => array( 'f.id', false ),
		);
		return $sortable;
	}

	protected function get_set_paid_link( $id ) {
		$link = add_query_arg( [
			'action' => 'ipt_fsqm_payment_set_status',
			'status' => 'paid',
			'id' => $id,
		], admin_url( 'admin-ajax.php' ) );
		$link = wp_nonce_url( $link, 'eform_set_paid_' . $id, '_wpnonce' );
		return '<a href="' . $link . '" class="payment-action">' . __( 'Set Status to Paid', 'ipt_fsqm' ) . '</a>';
	}
	protected function get_set_cancel_link( $id ) {
		$link = add_query_arg( [
			'action' => 'ipt_fsqm_payment_set_status',
			'status' => 'cancelled',
			'id' => $id,
		], admin_url( 'admin-ajax.php' ) );
		$link = wp_nonce_url( $link, 'eform_set_cancelled_' . $id, '_wpnonce' );
		return '<a href="' . $link . '" class="payment-action">' . __( 'Set Status to Cancelled', 'ipt_fsqm' ) . '</a>';
	}

	public function column_default( $item, $column_name ) {
		$payment_methods = IPT_FSQM_Form_Elements_Static::ipt_fsqm_get_payment_gateways();
		$payment_status = IPT_FSQM_Form_Elements_Static::ipt_fsqm_get_payment_status();
		switch ( $column_name ) {
			case 'title':
				$actions = array(
					'qview' => sprintf( '<a class="thickbox" title="%s" href="admin-ajax.php?action=ipt_fsqm_quick_preview&id=%d&width=640&height=500">%s</a>', esc_attr( sprintf( __( 'Payment of %s under %s', 'ipt_fsqm' ), $item['f_name'] . ' ' . $item['l_name'], $item['formname'] ) ), $item['data_id'], __( 'Quick Preview', 'ipt_fsqm' ) ),
					'view' => sprintf( '<a href="admin.php?page=ipt_fsqm_view_submission&id=%d">%s</a>', (int) $item['data_id'], __( 'Full View', 'ipt_fsqm' ) ),
				);
				// Add additional actions for offline-builtin payment system
				if ( 'offline' == $item['mode'] && current_user_can( 'manage_feedback' ) ) {
					// If it is awaiting, then add both paid/cancel state
					if ( '4' == $item['status'] ) {
						$actions['set_paid'] = $this->get_set_paid_link( $item['id'] );
						$actions['set_cancel'] = $this->get_set_cancel_link( $item['id'] );
					} elseif ( '1' == $item['status'] ) {
						// It is paid, so set cancel state
						$actions['set_cancel'] = $this->get_set_cancel_link( $item['id'] );
					} else {
						// It is not paid, so set paid state
						$actions['set_paid'] = $this->get_set_paid_link( $item['id'] );
					}
				}

				return sprintf( '%1$s %2$s', '<strong><a class="thickbox" title="' . esc_attr( sprintf( __( 'Payment of %s under %s', 'ipt_fsqm' ), $item['f_name'] . ' ' . $item['l_name'], $item['formname'] ) ) . '" href="admin-ajax.php?action=ipt_fsqm_quick_preview&id=' . $item['data_id'] . '&width=640&height=500">' . $item['f_name'] . ' ' . $item['l_name'] . '</a></strong>', $this->row_actions( $actions ) );
				break;
			case 'txn' :
				return $item[$column_name];
				break;
			case 'user_id' :
				$return = __( 'Guest', 'ipt_fsqm' );
				if ( $item['user_id'] != 0 ) {
					$user = get_user_by( 'id', $item['user_id'] );
					if ( $user instanceof WP_User ) {
						$return = '<a title="' . __( 'Edit user', 'ipt_fsqm' ) . '" href="user-edit.php?user_id=' . $user->ID . '">' . $user->display_name . '</a>';
					}
				}
				return $return;
				break;
			case 'email' :
				if ( trim( $item['email'] ) !== '' ) {
					return '<a href="mailto:' . $item[$column_name] . '">' . $item[$column_name] . '</a>';
				} else {
					return __( 'Unknown', 'ipt_fsqm' );
				}
				break;
			case 'date' :
				return date_i18n( get_option( 'date_format' ) . __(' \a\t ', 'ipt_fsqm') . get_option( 'time_format' ), strtotime( $item[$column_name] ) );
				break;
			case 'amount' :
				return $item[$column_name];
				break;
			case 'currency' :
				return $item[$column_name];
				break;
			case 'mode' :
				if ( $item['mode'] == '' ) {
					return __( 'N/A', 'ipt_fsqm' );
				}
				return '<a href="admin.php?page=ipt_fsqm_payments&pmethod=' . $item['mode'] . '">' . $payment_methods[$item[$column_name]] . '</a>';
				break;
			case 'status' :
				return '<a href="admin.php?page=ipt_fsqm_payments&pstatus=' . $item['status'] . '">' . $payment_status[$item[$column_name]] . '</a>';
				break;
			case 'form' :
				return '<a href="admin.php?page=ipt_fsqm_payments&form_id=' . $item['form_id'] . '">' . $item['formname'] . '</a>';
			break;
			default:
				print_r( $item[$column_name] );
				break;
		}
	}

	public function prepare_items() {
		global $wpdb, $ipt_fsqm_info;

		// prepare the query
		$query = "SELECT p.id id, p.txn txn, p.amount amount, p.status status, p.currency currency, p.date date, p.user_id user_id, p.mode mode, p.data_id data_id, d.f_name f_name, d.l_name l_name, d.email email, f.name formname, f.id form_id FROM {$ipt_fsqm_info['payment_table']} p LEFT JOIN {$ipt_fsqm_info['data_table']} d ON p.data_id = d.id LEFT JOIN {$ipt_fsqm_info['form_table']} f ON d.form_id = f.id";
		$sanitized_order = $this->get_sanitized_orderby( 'p.date', 'desc' );
		$orderby = $sanitized_order['orderby'];
		$order = $sanitized_order['order'];

		$where = '';
		$wheres = array();

		if ( isset( $_GET['form_id'] ) && !empty( $_GET['form_id'] ) ) {
			$wheres[] = $wpdb->prepare( "d.form_id = %d", $_GET['form_id'] );
		}
		if ( isset( $_GET['user_id'] ) && '' != $_GET['user_id'] ) {
			$wheres[] = $wpdb->prepare( "d.user_id = %d", $_GET['user_id'] );
		}
		if ( isset( $_GET['pmethod'] ) && '' != $_GET['pmethod'] ) {
			$wheres[] = $wpdb->prepare( "p.mode = %s", $_GET['pmethod'] );
		}
		if ( isset( $_GET['pstatus'] ) && '' != $_GET['pstatus'] ) {
			$wheres[] = $wpdb->prepare( "p.status = %s", $_GET['pstatus'] );
		}

		if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) {
			$search = '%' . $_GET['s'] . '%';
			$wheres[] = $wpdb->prepare( "p.txn LIKE %s", $search );
		}
		if ( !empty( $wheres ) ) {
			$where .= ' WHERE ' . implode( ' AND ', $wheres );
		}

		$query .= $where;

		// Pagination
		$totalitems = $wpdb->get_var( "SELECT COUNT(p.id) FROM {$ipt_fsqm_info['payment_table']} p LEFT JOIN {$ipt_fsqm_info['data_table']} d ON p.data_id = d.id{$where}" );
		$perpage = $this->get_items_per_page( 'fsqm_payment_per_page', 20 );
		$totalpages = ceil( $totalitems/$perpage );

		$this->set_pagination_args( array(
				'total_items' => $totalitems,
				'total_pages' => $totalpages,
				'per_page' => $perpage,
			) );
		$current_page = $this->get_pagenum();

		//put pagination and order on the query
		$query .= ' ORDER BY ' . $orderby . ' ' . $order . ' LIMIT ' . ( ( $current_page - 1 ) * $perpage ) . ',' . (int) $perpage;
		//print_r($query);

		//register the columns
		$this->_column_headers = $this->get_column_info();

		//fetch the items
		$this->items = $wpdb->get_results( $query, ARRAY_A );
	}

	public function no_items() {
		_e( 'No payments yet.', 'ipt_fsqm' );
	}

	public function extra_tablenav( $which ) {
		global $wpdb, $ipt_fsqm_info;

		// Get filter by forms
		$forms = $wpdb->get_results( "SELECT f.id id, f.name name, COUNT(p.id) ptotal FROM {$ipt_fsqm_info['payment_table']} p LEFT JOIN {$ipt_fsqm_info['data_table']} d ON p.data_id = d.id LEFT JOIN {$ipt_fsqm_info['form_table']} f ON d.form_id = f.id GROUP BY f.id ORDER BY f.id DESC" );

		// Filter by users
		$users = $wpdb->get_col( "SELECT distinct user_id FROM {$ipt_fsqm_info['payment_table']}" );

		// Filter by payment methods
		$payment_methods = IPT_FSQM_Form_Elements_Static::ipt_fsqm_get_payment_gateways();

		// Filter by status
		$payment_status = IPT_FSQM_Form_Elements_Static::ipt_fsqm_get_payment_status();

		switch ( $which ) {
			case 'top' :
?>
<div class="alignleft actions" style="margin-left: -9px;">
	<select name="form_id">
		<option value=""<?php if ( ! isset( $_GET['form_id'] ) || empty( $_GET['form_id'] ) ) echo ' selected="selected"'; ?>><?php _e( 'Show all forms', 'ipt_fsqm' ); ?></option>
		<?php if ( null != $forms ) : ?>
		<?php foreach ( $forms as $form ) : ?>
		<option value="<?php echo $form->id; ?>"<?php if ( isset( $_GET['form_id'] ) && $_GET['form_id'] == $form->id ) echo ' selected="selected"'; ?>><?php echo $form->name; ?> (<?php echo $form->ptotal; ?>)</option>
		<?php endforeach; ?>
		<?php else : ?>
		<option value=""><?php _e( 'No Forms in the payments database', 'ipt_fsqm' ); ?></option>
		<?php endif; ?>
	</select>

	<select name="user_id">
		<option value=""<?php if ( ! isset( $_GET['user_id'] ) || '' == $_GET['user_id'] ) echo ' selected="selected"'; ?>><?php _e( 'Show all users', 'ipt_fsqm' ); ?></option>
		<?php if ( null != $users ) : ?>
		<?php foreach ( $users as $user_id ) : ?>
		<?php if ( $user_id == 0 ) : ?>
		<option value="0"<?php if ( isset( $_GET['user_id'] ) && '0' == $_GET['user_id'] ) echo ' selected="selected"'; ?>><?php _e( 'Guests (Unregistered)', 'ipt_fsqm' ); ?></option>
		<?php else : ?>
		<?php $user = get_user_by( 'id', $user_id ); ?>
		<option value="<?php echo $user_id; ?>"<?php if ( isset( $_GET['user_id'] ) && (string) $user_id == $_GET['user_id'] ) echo ' selected="selected"'; ?>><?php echo $user->display_name; ?></option>
		<?php endif; ?>
		<?php endforeach; ?>
		<?php endif; ?>
	</select>

	<select name="pmethod" id="pmethod">
		<option value=""<?php if ( ! isset( $_GET['pmethod'] ) || '' === $_GET['pmethod'] ) echo ' selected="selected"'; ?>><?php _e( 'All payment methods', 'ipt_fsqm' ); ?></option>
		<?php foreach ( $payment_methods as $pm_key => $pm_val ) : ?>
		<option value="<?php echo esc_attr( $pm_key ); ?>" <?php selected( @$_GET['pmethod'], $pm_key, true ); ?>><?php echo $pm_val; ?></option>
		<?php endforeach; ?>
	</select>

	<select name="pstatus" id="pstatus">
		<option value=""<?php if ( ! isset( $_GET['pstatus'] ) || '' === $_GET['pstatus'] ) echo ' selected="selected"'; ?>><?php _e( 'Transaction Status', 'ipt_fsqm' ); ?></option>
		<?php foreach ( $payment_status as $ps_key => $ps_val ) : ?>
		<option value="<?php echo esc_attr( $ps_key ); ?>" <?php selected( @$_GET['pstatus'], $ps_key, true ); ?>><?php echo $ps_val; ?></option>
		<?php endforeach; ?>
	</select>

	<?php submit_button( __( 'Filter' ), 'secondary', false, false, array( 'id' => 'form-query-submit' ) ); ?>
</div>
<?php
				break;
		}
	}
}
