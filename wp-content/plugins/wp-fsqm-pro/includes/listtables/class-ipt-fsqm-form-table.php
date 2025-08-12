<?php
/**
 * Form Table
 *
 * A child class of WP_List_Table
 *
 * Used for showing all forms available under eForm > View all Forms
 *
 * @author Swashata <swashata4u@gmail.com>
 * @package eForm - WordPress Form Builder
 * @subpackage ListTables\Form
 * @codeCoverageIgnore
 */
class IPT_FSQM_Form_Table extends IPT_FSQM_Listtable {
	public function __construct() {
		parent::__construct( array(
				'singular' => 'ipt_fsqm_form_item',
				'plural' => 'ipt_fsqm_form_items',
				'ajax' => false,
			) );
	}

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" /><label for="cb-select-all-1"></label>',
			'title' => __( 'Name', 'ipt_fsqm' ),
			'shortcode' => __( 'Shortcode', 'ipt_fsqm' ),
			'submission' => __( 'Submissions', 'ipt_fsqm' ),
			'category' => __( 'Category', 'ipt_fsqm' ),
			'updated' => __( 'Last Updated', 'ipt_fsqm' ),
		);
		return $columns;
	}

	public function get_sortable_columns() {
		$sortable = array(
			'title' => array( 'f.name', false ),
			'submission' => array( 'sub', true ),
			'category' => array( 'c.name', false ),
			'updated' => array( 'f.updated', true ),
		);

		return $sortable;
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
		case 'title' :
			$permalinks = IPT_FSQM_Form_Elements_Static::standalone_permalink_parts( $item['id'] );
			$actions = array(
				'form_id' => sprintf( __( 'ID: %d', 'ipt_fsqm' ), $item['id'] ),
				'permalink' => sprintf( '<a class="view" title="%3$s" href="%1$s" target="_blank">%2$s</a>', $permalinks['url'], __( 'Preview', 'ipt_fsqm' ), __( 'Preview the form or copy the permalink', 'ipt_fsqm' ) ),
				'view'      => sprintf( '<a class="view" href="admin.php?page=ipt_fsqm_view_all_submissions&form_id=%d">%s</a>', $item['id'], __( 'View Submissions', 'ipt_fsqm' ) ),
				'download' => sprintf( '<a class="view" href="%1$s" title="%3$s" target="_blank">%2$s</a>', wp_nonce_url( admin_url( 'admin-ajax.php?action=ipt_fsqm_submission_download&form_id=' . $item['id'] ), 'ipt_fsqm_submission_download_' . $item['id'] ), __( 'Export Submissions', 'ipt_fsqm' ), esc_attr__( 'Export all submissions in a CSV file', 'ipt_fsqm' ) ),
				'edit'      => sprintf( '<a class="edit" href="admin.php?page=ipt_fsqm_all_forms&action=edit&form_id=%d">%s</a>', $item['id'], __( 'Edit', 'ipt_fsqm' ) ),
				'copy'      => sprintf( '<a class="copy" href="%s">%s</a>', wp_nonce_url( '?page=' . $_REQUEST['page'] . '&action=copy&id=' . $item['id'], 'ipt_fsqm_form_copy_' . $item['id'] ), __( 'Copy', 'ipt_fsqm' ) ),
				'delete'    => sprintf( '<a class="delete" href="%s">%s</a>', wp_nonce_url( '?page=' . $_REQUEST['page'] . '&action=delete&id=' . $item['id'], 'ipt_fsqm_form_delete_' . $item['id'] ), __( 'Delete', 'ipt_fsqm' ) ),
			);
			return sprintf( '%1$s %2$s', '<strong><a title="' . __( 'View all submissions under this form', 'ipt_fsqm' ) . '" href="admin.php?page=ipt_fsqm_view_all_submissions&form_id=' . $item['id'] . '">' . $item['name'] . '</a></strong>' , $this->row_actions( apply_filters( 'ipt_fsqm_all_forms_row_action', $actions ) ) );
			break;
		case 'shortcode' :
			return '[ipt_fsqm_form id="' . $item['id'] . '"]';
			break;
		case 'submission' :
			return $item['sub'];
			break;
		case 'category' :
			if ( $item['category'] == 0 ) {
				return __( 'Unassigned', 'ipt_fsqm' );
			} else {
				return $item['catname'];
			}
			break;
		case 'updated' :
			if ( 0 == $item['sub'] )
				return __( 'N/A', 'ipt_fsqm' );
			else
				return date_i18n( get_option( 'date_format' ) . __(' \a\t ', 'ipt_fsqm') . get_option( 'time_format' ), strtotime( $item[$column_name] ) );
			break;
		default :
			print_r( $item );
		}
	}

	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="forms[]" id="eform-forms_%1$s" value="%1$s" />', $item['id'] );
	}

	public function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete' ),
		);
		return $actions;
	}

	/**
	 *
	 *
	 * @global wpdb $wpdb
	 * @global array $ipt_fsqm_info
	 */
	public function prepare_items() {
		global $wpdb, $ipt_fsqm_info;

		//prepare our query
		$query = "SELECT f.id id, f.name name, f.updated updated, f.category category, COUNT(d.id) sub, c.name catname FROM {$ipt_fsqm_info['form_table']} f LEFT JOIN {$ipt_fsqm_info['data_table']} d ON f.id = d.form_id LEFT JOIN {$ipt_fsqm_info['category_table']} c ON f.category = c.id";
		$sanitized_order = $this->get_sanitized_orderby( 'f.id', 'desc' );
		$orderby = $sanitized_order['orderby'];
		$order = $sanitized_order['order'];
		$wheres = array();
		$where = '';

		if ( ! empty( $_GET['s'] ) ) {
			$search = '%' . $_GET['s'] . '%';
			$wheres[] = $wpdb->prepare( "f.name LIKE %s", $search );
		}

		if ( isset( $_GET['cat_id'] ) && $_GET['cat_id'] !== '' ) {
			$wheres[] = $wpdb->prepare( "f.category = %d", $_GET['cat_id'] );
		}

		if ( ! empty( $wheres ) ) {
			$where .= ' WHERE ' . implode( ' AND ', $wheres );
		}

		$query .= $where;

		//pagination
		$totalitems = $wpdb->get_var( "SELECT COUNT(id) FROM {$ipt_fsqm_info['form_table']} f{$where}" );
		$perpage = $this->get_items_per_page( 'feedback_forms_per_page', 20 );
		$totalpages = ceil( $totalitems/$perpage );

		$this->set_pagination_args( array(
				'total_items' => $totalitems,
				'total_pages' => $totalpages,
				'per_page' => $perpage,
			) );
		$current_page = $this->get_pagenum();

		//put pagination and order on the query
		$query .= ' GROUP BY f.id ORDER BY ' . $orderby . ' ' . $order . ' LIMIT ' . ( ( $current_page - 1 ) * $perpage ) . ',' . (int) $perpage;

		//register the columns
		$this->_column_headers = $this->get_column_info();

		//fetch the items
		$this->items = $wpdb->get_results( $query, ARRAY_A );
	}

	public function extra_tablenav( $which ) {
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
		switch ( $which ) {
			case 'top' :
			?>
<div class="alignleft actions">
	<select name="cat_id" id="cat_id">
		<option value=""<?php if ( !isset( $_GET['cat_id'] ) || '' == $_GET['cat_id'] ) echo ' selected="selected"'; ?>><?php _e( 'Show forms from all categories', 'ipt_fsqm' ); ?></option>
		<?php foreach ( $form_categories as $form_cat ) : ?>
		<option value="<?php echo $form_cat['value']; ?>"<?php if ( isset( $_GET['cat_id'] ) && (string) $form_cat['value'] == $_GET['cat_id'] ) echo ' selected="selected"' ?>><?php echo $form_cat['label']; ?></option>
		<?php endforeach; ?>
	</select>
	<?php submit_button( __( 'Filter' ), 'secondary', false, false, array( 'id' => 'form-cat-query-submit' ) ); ?>
</div>
			<?php
				break;

			case 'bottom' :
				if ( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {
					?>
<div class="actions alignleft">
	<?php printf( __( 'Showing search results for "%s"', 'ipt_fsqm' ), $_GET['s'] ); ?>
</div>
					<?php
				}
				break;
		}
	}
}
