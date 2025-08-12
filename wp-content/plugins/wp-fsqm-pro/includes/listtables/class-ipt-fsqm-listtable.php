<?php

abstract class IPT_FSQM_Listtable extends WP_List_Table {
	public function get_sanitized_orderby( $default_orderby, $default_order = 'desc' ) {
		$sortable_column_defs = $this->get_sortable_columns();
		$sortable_columns = [];
		foreach ( $sortable_column_defs as $sc ) {
			$sortable_columns[] = is_array( $sc ) ? $sc[0] : $sc;
		}
		$orderby = !empty( $_GET['orderby'] ) ? esc_sql( $_GET['orderby'] ) : $default_orderby;
		$order = !empty( $_GET['order'] ) ? esc_sql( $_GET['order'] ) : $default_order;

		// Protect against injection
		if ( ! in_array( $orderby, $sortable_columns ) ) {
			$orderby = $default_orderby;
		}
		if ( ! in_array( strtolower( $order ), [ 'asc', 'desc' ] ) ) {
			$order = $default_order;
		}

		return [
			'orderby' => $orderby,
			'order' => $order,
		];
	}
}
