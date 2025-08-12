<?php
/**
 * Data Table
 *
 * A child class of WP_List_Table
 *
 * Used for showing all data/submissions available under eForm > View all Submissions
 *
 * @author Swashata <swashata4u@gmail.com>
 * @package eForm - WordPress Form Builder
 * @subpackage ListTables\Data
 * @codeCoverageIgnore
 */
class IPT_FSQM_Data_Table extends IPT_FSQM_Listtable {
	public $feedback;

	public function __construct() {
		$this->feedback = get_option( 'ipt_fsqm_feedback' );

		parent::__construct( array(
				'singular' => 'ipt_fsqm_table_item',
				'plural' => 'ipt_fsqm_table_items',
				'ajax' => true,
			) );
	}

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Name', 'ipt_fsqm' ),
			'email' => __( 'Email', 'ipt_fsqm' ),
			'phone' => __( 'Phone', 'ipt_fsqm' ),
			'date' => __( 'Date', 'ipt_fsqm' ),
			'ip' => __( 'IP Address', 'ipt_fsqm' ),
			'score' => __( 'Score', 'ipt_fsqm' ),
			'user' => __( 'Account', 'ipt_fsqm' ),
			'form' => __( 'Form', 'ipt_fsqm' ),
			'category' => __( 'Category', 'ipt_fsqm' ),
			'track' => __( 'URL Track', 'ipt_fsqm' ),
			'referer' => __( 'Referer', 'ipt_fsqm' ),
			'time' => __( 'Time', 'ipt_fsqm' ),
			'paid' => __( 'Payment', 'ipt_fsqm' ),
			'star' => __( 'Star', 'ipt_fsqm' ),
		);
		return $columns;
	}

	public function get_sortable_columns() {
		$sortable = array(
			'title' => array( 'd.f_name', false ),
			'date' => array( 'd.date', true ),
			'email' => array( 'd.email', false ),
			'phone' => array( 'd.phone', false ),
			'score' => array( 'd.score', true ),
			'user' => array( 'd.user_id', true ),
			'ip' => array( 'd.ip', false ),
			'form' => array( 'd.form_id', false ),
			'category' => array( 'c.name', false ),
			'track' => array( 'd.url_track', false ),
			'referer' => array( 'd.referer', false ),
			'time' => array( 'd.time', true ),
			'paid' => [ 'd.paid', true ],
			'star' => array( 'd.star', true ),
		);
		return $sortable;
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
		case 'star' :
			return '<a href="javascript: void(null)" class="ipt_fsqm_star"><img title="' . ( $item['star'] == 1 ? __( 'Click to Unstar', 'ipt_fsqm' ) : __( 'Click to Star', 'ipt_fsqm' ) ) . '" src="' . plugins_url( ( $item['star'] == 1 ? '/static/admin/images/star_on.png' : '/static/admin/images/star_off.png' ), IPT_FSQM_Loader::$abs_file ) . '" /></a>';
		case 'title' :
			$actions = array(
				'data_id' => sprintf( __( 'ID: %d', 'ipt_fsqm' ), $item['id'] ),
				'qview' => sprintf( '<a class="thickbox" title="%s" href="admin-ajax.php?action=ipt_fsqm_quick_preview&id=%d&width=640&height=500">%s</a>', esc_attr( sprintf( __( 'Submission of %s under %s', 'ipt_fsqm' ), $item['f_name'] . ' ' . $item['l_name'], $item['name'] ) ), $item['id'], __( 'Quick Preview', 'ipt_fsqm' ) ),
				'view' => sprintf( '<a href="admin.php?page=ipt_fsqm_view_submission&id=%d">%s</a>', (int) $item['id'], __( 'Full View', 'ipt_fsqm' ) ),
				'edit' => '<a class="edit" href="admin.php?page=ipt_fsqm_view_submission&id=' . $item['id'] . '&edit=Edit">' . __( 'Edit Submission', 'ipt_fsqm' ) . '</a>',
				'delete' => '<a class="delete" href="' . wp_nonce_url( '?page=' . $_REQUEST['page'] . '&action=delete&id=' . $item['id'], 'ipt_fsqm_delete_' . $item['id'] ) . '">' . __( 'Delete', 'ipt_fsqm' ) . '</a>',
			);

			return sprintf( '%1$s %2$s', '<strong><a class="thickbox" title="' . esc_attr( sprintf( __( 'Submission of %s under %s', 'ipt_fsqm' ), $item['f_name'] . ' ' . $item['l_name'], $item['name'] ) ) . '" href="admin-ajax.php?action=ipt_fsqm_quick_preview&id=' . $item['id'] . '&width=640&height=500">' . $item['f_name'] . ' ' . $item['l_name'] . '</a></strong>', $this->row_actions( apply_filters( 'ipt_fsqm_all_data_row_action', $actions, $item ) ) );
			break;
		case 'email' :
			if ( trim( $item['email'] ) !== '' ) {
				return '<a href="mailto:' . $item[$column_name] . '">' . $item[$column_name] . '</a>';
			} else {
				return __( 'Unknown', 'ipt_fsqm' );
			}
			break;
		case 'phone' :
		case 'ip' :
			return $item[$column_name];
			break;
		case 'date' :
			return date_i18n( get_option( 'date_format' ) . __(' \a\t ', 'ipt_fsqm') . get_option( 'time_format' ), strtotime( $item[$column_name] ) );
			break;
		case 'form' :
			return '<a href="admin.php?page=ipt_fsqm_view_all_submissions&form_id=' . $item['form_id'] . '">' . $item['name'] . '</a> <code>' . $item['form_id'] . '</code>';
			break;
		case 'category' :
			return ( $item['catname'] == '' ? __( 'Unassigned', 'ipt_fsqm' ) : $item['catname'] );
			break;
		case 'score' :
			$score = __( 'N/A', 'ipt_fsqm' );
			if ( $item['max_score'] != 0 ) {
				$percent = number_format_i18n( $item['score'] * 100 / $item['max_score'], 2 );
				$score = $item['score'] . '/' . $item['max_score'] . ' <code>(' . $percent . '%)</code>';
			}
			return $score;
			break;
		case 'user' :
			$return = __( 'Guest', 'ipt_fsqm' );
			if ( $item['user_id'] != 0 ) {
				$user = get_user_by( 'id', $item['user_id'] );
				if ( $user instanceof WP_User ) {
					$return = '<a title="' . __( 'Edit user', 'ipt_fsqm' ) . '" href="user-edit.php?user_id=' . $user->ID . '">' . $user->display_name . '</a>';
				}
			}
			return $return;
			break;
		case 'track' :
			if ( $item['track'] == '' ) {
				return __( 'Unknown', 'ipt_fsqm' );
			} else {
				return '<a href="' . esc_attr( 'admin.php?page=ipt_fsqm_view_all_submissions&track_id=' . $item['track'] ) . '">' . $item['track'] . '</a>';
			}
			break;
		case 'referer' :
			if ( $item['referer'] == '' ) {
				return __( 'Unknown', 'ipt_fsqm' );
			} else {
				return '<a href="' . esc_attr( 'admin.php?page=ipt_fsqm_view_all_submissions&referer=' . $item['referer'] ) . '">' . $item['referer'] . '</a>';
			}
			break;
		case 'time' :
			return $this->seconds_to_words( $item['time'], __( 'N/A', 'ipt_fsqm' ) );
			break;
		case 'paid' :
			$statuses = IPT_FSQM_Form_Elements_Static::ipt_fsqm_get_payment_status();
			$pstatus = $item['pstatus'];
			if ( $pstatus ) {
				return $statuses[ $item['pstatus'] ];
			} else {
				if ( '1' === $item['paid'] ) {
					return __( 'Paid through WooCommerce', 'ipt_fsqm' );
				} else {
					// could be woocommerce or not needed
					return __( 'Not needed or processing through WooCommerce', 'ipt_fsqm' );
				}
			}
			break;
		default :
			return print_r( $item[$column_name], true );
		}
	}

	/**
	 * Converts seconds to readable W days, X hours, Y minutes, Z seconds
	 *
	 * @param      integer  $seconds   The number of second
	 * @param      string   $for_zero  What to return when time is zero
	 *
	 * @return     string
	 */
	public function seconds_to_words( $seconds, $for_zero = '' ) {
		return IPT_Plugin_UIF_Admin::instance()->seconds_to_words( $seconds, $for_zero );
	}

	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="feedbacks[]" value="%s" />', $item['id'] );
	}

	public function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete' ),
			'star' => __( 'Mark Starred', 'ipt_fsqm' ),
			'unstar' => __( 'Mark Unstarred', 'ipt_fsqm' ),
		);
		return $actions;
	}

	/**
	 *
	 *
	 * @global wpdb $wpdb
	 * @global type $_wp_column_headers
	 * @global type $ipt_fsqm_info
	 */
	public function prepare_items() {
		global $wpdb, $ipt_fsqm_info;

		// prepare our query
		$sortable_column_defs = $this->get_sortable_columns();
		$sortable_columns = [];
		foreach ( $sortable_column_defs as $sc ) {
			$sortable_columns[] = is_array( $sc ) ? $sc[0] : $sc;
		}
		$query = "SELECT d.id id, d.f_name f_name, d.l_name l_name, d.email email, d.phone phone, d.ip ip, d.date date, d.star star, d.score score, d.max_score max_score, d.user_id user_id, d.url_track track, d.referer referer, d.time time, d.paid paid, p.status pstatus, f.name name, f.id form_id, c.name catname FROM {$ipt_fsqm_info['data_table']} d LEFT JOIN {$ipt_fsqm_info['form_table']} f ON d.form_id = f.id LEFT JOIN {$ipt_fsqm_info['category_table']} c ON f.category = c.id LEFT JOIN {$ipt_fsqm_info['payment_table']} p ON p.data_id = d.id";
		$sanitized_order = $this->get_sanitized_orderby( 'd.date', 'desc' );
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
		if ( isset( $_GET['cat_id'] ) && $_GET['cat_id'] !== '' ) {
			$wheres[] = $wpdb->prepare( "f.category = %d", $_GET['cat_id'] );
		}
		if ( isset( $_GET['track_id'] ) && $_GET['track_id'] !== '' ) {
			$wheres[] = $wpdb->prepare( "d.url_track = %s", stripslashes( $_GET['track_id'] ) );
		}
		if ( isset( $_GET['referer'] ) && $_GET['referer'] !== '' ) {
			$wheres[] = $wpdb->prepare( "d.referer = %s", stripslashes( $_GET['referer'] ) );
		}

		if ( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {
			$search = '%' . $_GET['s'] . '%';
			$wheres[] = $wpdb->prepare( "(f_name LIKE %s OR l_name LIKE %s OR email LIKE %s OR phone LIKE %s OR ip LIKE %s)", $search, $search, $search, $search, $search );
		}

		if ( !empty( $wheres ) ) {
			$where .= ' WHERE ' . implode( ' AND ', $wheres );
		}

		$query .= $where;

		//pagination
		$totalitems = $wpdb->get_var( "SELECT COUNT(d.id) FROM {$ipt_fsqm_info['data_table']} d LEFT JOIN {$ipt_fsqm_info['form_table']} f ON d.form_id = f.id{$where}" ); // d is alias for data_table which is used in where clause
		$perpage = $this->get_items_per_page( 'feedbacks_per_page', 20 );
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

		//var_dump($this->items);
	}

	public function no_items() {
		_e( 'No Feedbacks/Surveys/Quiz Results yet! Please be patient.', 'ipt_fsqm' );
	}

	public function extra_tablenav( $which ) {
		global $wpdb, $ipt_fsqm_info;
		$forms = $wpdb->get_results( "SELECT id, name FROM {$ipt_fsqm_info['form_table']} ORDER BY id DESC" );
		$users = $wpdb->get_col( "SELECT distinct user_id FROM {$ipt_fsqm_info['data_table']}" );
		$form_categories = array(
			array(
				'value' => '0',
				'label' => __( 'Unassigned Forms', 'ipt_fsqm' ),
			),
		);
		$db_categories = IPT_FSQM_Form_Elements_Static::get_all_categories();
		if ( null != $db_categories ) {
			foreach ( $db_categories as $dbc ) {
				$form_categories[] = array(
					'value' => $dbc->id,
					'label' => $dbc->name,
				);
			}
		}
		$tracks = $wpdb->get_col( "SELECT distinct url_track FROM {$ipt_fsqm_info['data_table']} WHERE url_track != ''" );
		$referers = $wpdb->get_col( "SELECT distinct referer FROM {$ipt_fsqm_info['data_table']} WHERE referer != ''" );
		switch ( $which ) {
		case 'top' :
?>
<div class="alignleft actions">
	<select name="form_id">
		<option value=""<?php if ( !isset( $_GET['form_id'] ) || empty( $_GET['form_id'] ) ) echo ' selected="selected"'; ?>><?php _e( 'Show all forms', 'ipt_fsqm' ); ?></option>
		<?php if ( null != $forms ) : ?>
		<?php foreach ( $forms as $form ) : ?>
		<option value="<?php echo $form->id; ?>"<?php if ( isset( $_GET['form_id'] ) && $_GET['form_id'] == $form->id ) echo ' selected="selected"'; ?>><?php echo $form->name; ?></option>
		<?php endforeach; ?>
		<?php else : ?>
		<option value=""><?php _e( 'No Forms in the database', 'ipt_fsqm' ); ?></option>
		<?php endif; ?>
	</select>

	<select name="user_id">
		<option value=""<?php if ( !isset( $_GET['user_id'] ) || '' == $_GET['user_id'] ) echo ' selected="selected"'; ?>><?php _e( 'Show all users', 'ipt_fsqm' ); ?></option>
		<?php if ( null != $users ) : ?>
		<?php foreach ( $users as $user_id ) : ?>
		<?php if ( $user_id == 0 ) : ?>
		<option value="0"<?php if ( isset( $_GET['user_id'] ) && '0' == $_GET['user_id'] ) echo ' selected="selected"'; ?>><?php _e( 'Guests (Unregistered)', 'ipt_fsqm' ); ?></option>
		<?php else : ?>
		<?php $user = get_user_by( 'id', $user_id ); ?>
		<?php
		if ( ! $user ) {
			$user = new stdClass();
			$user->display_name = __( 'Deleted User', 'ipt_fsqm' ) . ' (' . $user_id . ')';
		}
		?>
		<option value="<?php echo $user_id; ?>"<?php if ( isset( $_GET['user_id'] ) && (string) $user_id == $_GET['user_id'] ) echo ' selected="selected"'; ?>><?php echo $user->display_name; ?></option>
		<?php endif; ?>
		<?php endforeach; ?>
		<?php endif; ?>
	</select>

	<select name="cat_id" id="cat_id">
		<option value=""<?php if ( !isset( $_GET['cat_id'] ) || '' == $_GET['cat_id'] ) echo ' selected="selected"'; ?>><?php _e( 'Show forms from all categories', 'ipt_fsqm' ); ?></option>
		<?php foreach ( $form_categories as $form_cat ) : ?>
		<option value="<?php echo $form_cat['value']; ?>"<?php if ( isset( $_GET['cat_id'] ) && (string) $form_cat['value'] == $_GET['cat_id'] ) echo ' selected="selected"' ?>><?php echo $form_cat['label']; ?></option>
		<?php endforeach; ?>
	</select>

	<select name="track_id" id="track_id">
		<option value=""<?php if ( !isset( $_GET['track_id'] ) || '' == $_GET['track_id'] ) echo ' selected="selected"'; ?>><?php _e( 'Show all URL Tracks', 'ipt_fsqm' ); ?></option>
		<?php foreach ( $tracks as $track ) : ?>
		<option value="<?php echo esc_attr( $track ); ?>"<?php if ( isset( $_GET['track_id'] ) && (string) $track == $_GET['track_id'] ) echo ' selected="selected"'; ?>><?php echo $track; ?></option>
		<?php endforeach; ?>
	</select>

	<select id="referer" name="referer">
		<option value=""<?php if ( !isset( $_GET['referer'] ) || '' == $_GET['referer'] ) echo ' selected="selected"'; ?>><?php _e( 'Show all Referers', 'ipt_fsqm' ); ?></option>
		<?php foreach ( $referers as $referer ) : ?>
		<option value="<?php echo esc_attr( $referer ); ?>"<?php if ( isset( $_GET['referer'] ) && (string) $referer == $_GET['referer'] ) echo ' selected="selected"'; ?>><?php echo $referer; ?></option>
		<?php endforeach; ?>
	</select>

	<?php submit_button( __( 'Filter' ), 'secondary', false, false, array( 'id' => 'form-query-submit' ) ); ?>
</div>
				<?php
			break;
		case 'bottom' :
			echo '<div class="alignleft"><p>';
			_e( 'You can also print a submission. Just select Quick Preview from the list and click on the print button.', 'ipt_fsqm' );
			echo '</p></div>';
		}
	}
}
