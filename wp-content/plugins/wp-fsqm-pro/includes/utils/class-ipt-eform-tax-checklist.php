<?php

/**
 * Walker class to create taxonomy checklist for guest-post element
 *
 * We have copied functionality from Category_Checklist
 *
 * @package eForm - WordPress Form Builder
 * @subpackage Walker\Tax_Checklist
 *
 * @codeCoverageIgnore
 */
class IPT_eForm_Tax_Checklist extends Walker {
	public $tree_type = 'category';
	public $db_fields = array ('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this
	public $name = '';
	public $is_single = false;
	public $is_required = false;

	public function __construct( $name, $is_single = false, $is_required = false ) {
		$this->name = $name;
		$this->is_single = $is_single;
		$this->is_required = $is_required;
	}

	/**
	 * Starts the list before the elements are added.
	 *
	 * @see Walker:start_lvl()
	 *
	 * @since 2.5.1
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of category. Used for tab indentation.
	 * @param array  $args   An array of arguments. @see wp_terms_checklist()
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {

	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see Walker::end_lvl()
	 *
	 * @since 2.5.1
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of category. Used for tab indentation.
	 * @param array  $args   An array of arguments. @see wp_terms_checklist()
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {

	}

	/**
	 * Start the element output.
	 *
	 * @see Walker::start_el()
	 *
	 * @since 2.5.1
	 *
	 * @param string $output   Passed by reference. Used to append additional content.
	 * @param object $category The current term object.
	 * @param int    $depth    Depth of the term in reference to parents. Default 0.
	 * @param array  $args     An array of arguments. @see wp_terms_checklist()
	 * @param int    $id       ID of the current term.
	 */
	public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		$args['selected_cats'] = empty( $args['selected_cats'] ) ? array() : $args['selected_cats'];

		$output .= '<option value="' . $category->term_id . '" ' .
					( in_array( $category->term_id, $args['selected_cats'] ) ? ' selected="selected"' : '' ) . '>' .
					str_repeat( "&nbsp;&nbsp;&nbsp;&nbsp;", $depth ) . esc_html( apply_filters( 'the_category', $category->name ) );
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @see Walker::end_el()
	 *
	 * @since 2.5.1
	 *
	 * @param string $output   Passed by reference. Used to append additional content.
	 * @param object $category The current term object.
	 * @param int    $depth    Depth of the term in reference to parents. Default 0.
	 * @param array  $args     An array of arguments. @see wp_terms_checklist()
	 */
	public function end_el( &$output, $category, $depth = 0, $args = array() ) {
		$output .= "</option>\n";
	}

	public function generate_id_from_name( $name ) {
		return esc_attr( str_replace( array( '[', ']' ), array( '_', '' ), trim( $name ) ) );
	}
}
