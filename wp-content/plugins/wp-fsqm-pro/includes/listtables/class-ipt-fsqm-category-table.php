<?php
/**
 * Category Table
 *
 * A child class of WP_List_Table
 *
 * Used for showing all categories available under eForm > Category
 *
 * @author Swashata <swashata4u@gmail.com>
 * @package eForm - WordPress Form Builder
 * @subpackage ListTables\Category
 * @codeCoverageIgnore
 */
class IPT_FSQM_Category_Table extends IPT_FSQM_Listtable {
	public function __construct() {
		parent::__construct( array(
			'singular' => 'ipt_fsqm_category_item',
			'plural' => 'ipt_fsqm_category_items',
			'ajax' => false,
		) );
	}

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Name', 'ipt_fsqm' ),
			'description' => __( 'Description', 'ipt_fsqm' ),
			'forms' => __( 'Total Forms', 'ipt_fsqm' ),
		);
		return $columns;
	}

	public function get_sortable_columns() {
		$sortable = array(
			'title' => 'name',
			'forms' => 'forms',
		);
		return $sortable;
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'title' :
				$actions = array(
					'category_id' => sprintf( __( 'ID: %d', 'ipt_fsqm' ), $item['id'] ),
					'view_form' => sprintf( '<a class="view" href="admin.php?page=ipt_fsqm_all_forms&cat_id=%2$d">%1$s</a>', __( 'View Forms', 'ipt_fsqm' ), $item['id'] ),
					'view_submission' => sprintf( '<a class="view" href="admin.php?page=ipt_fsqm_view_all_submissions&cat_id=%2$d">%1$s</a>', __( 'View Submissions', 'ipt_fsqm' ), $item['id'] ),
					'edit' => sprintf( '<a class="edit" href="admin.php?page=ipt_fsqm_form_category&paction=edit_cat&cat_id=%2$d">%1$s</a>', __( 'Edit', 'ipt_fsqm' ), $item['id'] ),
					'delete' => sprintf( '<a class="edit" href="' . wp_nonce_url( 'admin.php?page=ipt_fsqm_form_category&action=delete&cat_id=' . $item['id'], 'ipt_fsqm_category_delete_' . $item['id'], '_wpnonce' ) . '">%1$s</a>', __( 'Delete', 'ipt_fsqm' ) ),
				);
				return sprintf( '%1$s %2$s', '<strong><a title="' . __( 'Edit Category', 'ipt_fsqm' ) . '" href="ipt_fsqm_form_category&paction=edit_cat&cat_id=' . $item['id'] . '">' . $item['name'] . '</a></strong>', $this->row_actions( $actions ) );
				break;

			case 'forms' :
				return sprintf( '<a class="view" href="admin.php?page=ipt_fsqm_all_forms&cat_id=%1$d">%2$s</a>', $item['id'], $item['forms'] );
				break;

			case 'description' :
				return htmlspecialchars( $item['description'] );
				break;
			default :
				print_r( $item );
				break;
		}
	}

	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="cat_ids[]" value="%s" />', $item['id'] );
	}

	public function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete' ),
		);
		return $actions;
	}

	public function prepare_items() {
		global $wpdb, $ipt_fsqm_info;

		// Prepare our query
		$query = "SELECT c.id id, c.name name, c.description description, COUNT( f.id ) forms FROM {$ipt_fsqm_info['category_table']} c LEFT JOIN {$ipt_fsqm_info['form_table']} f ON c.id = f.category GROUP BY c.id";
		$sanitized_order = $this->get_sanitized_orderby( 'c.id', 'desc' );
		$orderby = $sanitized_order['orderby'];
		$order = $sanitized_order['order'];

		$where = '';

		if ( !empty( $_GET['s'] ) ) {
			$search = '%' . $_GET['s'] . '%';

			$where = $wpdb->prepare( " WHERE name LIKE %s", $search );
		}

		$query .= $where;

		// Pagination
		$totalitems = $wpdb->get_var( "SELECT COUNT(id) FROM {$ipt_fsqm_info['category_table']}{$where}" );
		$perpage = $this->get_items_per_page( 'fsqm_category_per_page', 20 );
		$totalpages = ceil( $totalitems/$perpage );

		$this->set_pagination_args( array(
			'total_items' => $totalitems,
			'total_pages' => $totalpages,
			'per_page' => $perpage,
		) );
		$current_page = $this->get_pagenum();

		// Put pagination and order on the query
		$query .= ' ORDER BY ' . $orderby . ' ' . $order . ' LIMIT ' . ( ( $current_page - 1 ) * $perpage ) . ',' . (int) $perpage;

		// register the columns
		$this->_column_headers = $this->get_column_info();

		// fetch the items
		$this->items = $wpdb->get_results( $query, ARRAY_A );
	}

	public function extra_tablenav( $which ) {
		if ( isset( $_GET['s'] ) && !empty( $_GET['s'] ) && 'top' == $which ) {
?>
<div class="actions alignleft">
	<?php printf( __( 'Showing search results for "%s"', 'ipt_fsqm' ), $_GET['s'] ); ?>
</div>
			<?php
		}
	}

	public function no_items() {
		_e( 'You have not created any category yet. Please click on the <strong>Add New</strong> button to get started.', 'ipt_fsqm' );
	}
}
