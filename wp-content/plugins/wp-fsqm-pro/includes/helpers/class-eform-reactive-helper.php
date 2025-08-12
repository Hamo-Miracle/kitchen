<?php
/**
 * A helper class to for making interactive forms using self developed Reactive Helper
 *
 * It will enqueue stuff, provide filters for parsing template tags ( `M0` ) into Reactive
 * templates and values.
 *
 * @package eForm - WordPress Form Builder
 * @subpackage Helpers
 * @author Swashata Ghosh <swashata@wpquark.com>
 */
class EForm_Reactive_Helper {
	/**
	 * Form ID for using inside reactive templates
	 *
	 * @var        int
	 */
	protected $form_id;
	/**
	 * Maps for converting element m_type into reactive templates
	 *
	 * @var        array
	 */
	protected $m_type_maps;

	/**
	 * Array of matched elements during filters
	 *
	 * @var        array
	 */
	protected $matches;

	/**
	 * Object instance for the value class
	 *
	 * @var        IPT_FSQM_Form_Elements_Value
	 */
	protected $value;

	/**
	 * Constructor
	 *
	 * @param      int   $form_id  The ID of the form
	 */
	public function __construct( $form_id ) {
		$this->form_id = $form_id;
		$this->m_type_maps = array(
			'M' => 'mcq',
			'F' => 'freetype',
			'O' => 'pinfo',
		);
		$this->matches = array(
			'mcq' => array(),
			'freetype' => array(),
			'pinfo' => array(),
		);
	}

	/**
	 * Add filter to parse eForm template tags into reactive templates
	 *
	 * It basically converts something like M0 into <span class="eform_react_{form_id}_mcq_0"></span>
	 *
	 * Filters are added to label and richtext
	 *
	 * Do remove these when the function is done
	 */
	public function add_react_filters() {
		// Add filter to labels
		add_filter( 'ipt_uif_label', array( $this, 'react_filter' ), 10, 1 );
		// Add filter to richtext
		add_filter( 'ipt_uif_richtext', array( $this, 'react_filter' ), 10, 1 );
	}

	/**
	 * Remove filter to parse eForm template tags into reactive templates
	 */
	public function remove_react_filters() {
		remove_filter( 'ipt_uif_label', array( $this, 'react_filter' ), 10 );
		remove_filter( 'ipt_uif_richtext', array( $this, 'react_filter' ), 10 );
	}

	/**
	 * Covert eForm template tags into reactive template
	 *
	 * Basically grows a {{mustache}} for the text
	 *
	 * @param      string  $text   The text
	 * @return     string  converted text
	 */
	public function react_filter( $text ) {
		// Match and replace templates
		$text = preg_replace_callback( '/%(?!%)(M|F|O)([0-9]+)%(?!%)/', array( $this, 'react_filter_callback' ), $text );
		// Match and replace escapes
		return $this->remove_escapes( $text );
	}

	/**
	 * A callback function for the preg_replace_callback in react_filter
	 *
	 * It replaces template tags with the well formatted reactive template
	 *
	 * @param      array  $matches  The matches
	 * @return     string  converted text according to matches
	 */
	public function react_filter_callback( $matches ) {
		$elm_id = $matches[2];
		// Add to the matches
		$this->matches[ $this->m_type_maps[ $matches[1] ] ][] = absint( $elm_id );
		return '<span class="eform-react-placeholder eform_react_' .
			$this->form_id . '_' .
			$this->m_type_maps[ $matches[1] ] . '_' .
			absint( $elm_id ) .
			'"></span>';
	}

	/**
	 * Add the filters to convert eForm tags into value
	 *
	 * @param      int  $data_id  ID of the submission
	 */
	public function add_value_filters( $data_id ) {
		// Init the value class
		$this->value = new IPT_EForm_Form_Elements_Values( $data_id );
		$this->value->set_option_delimiter( ', ' );
		$this->value->set_row_delimiter( "\n" );
		$this->value->set_range_delimiter( '/' );
		$this->value->set_entry_delimiter( ' : ' );
		// Add filter to labels
		add_filter( 'ipt_uif_label', array( $this, 'value_filter' ), 10, 1 );
		// Add filter to richtext
		add_filter( 'ipt_uif_richtext', array( $this, 'value_filter' ), 10, 1 );
	}

	/**
	 * Remove the filters which would convert eForm tags into values
	 */
	public function remove_value_filters() {
		$this->value = null;
		remove_filter( 'ipt_uif_label', array( $this, 'value_filter' ), 10 );
		remove_filter( 'ipt_uif_richtext', array( $this, 'value_filter' ), 10 );
	}

	/**
	 * Convert eForm template tags into corresponding value
	 *
	 * @param      string  $text   The text
	 *
	 * @return     string  replaced text
	 */
	public function value_filter( $text ) {
		// Match and replace templates
		$text = preg_replace_callback( '/%(?!%)(M|F|O)([0-9]+)%(?!%)/', array( $this, 'value_filter_callback' ), $text );
		// Match and replace escapes
		return $this->remove_escapes( $text );
	}

	/**
	 * Callback for the preg_replace in value_filter
	 *
	 * Replaces the template tag with the value we get from the value class
	 *
	 * @param      array   $matches  The matches
	 *
	 * @return     string  replaced value
	 */
	public function value_filter_callback( $matches ) {
		$key = $matches[2];
		$m_type = $this->m_type_maps[ $matches[1] ];
		$value = $this->value->get_value( $m_type, $key, 'string', 'label' );
		// Some special use cases
		return $this->normalize_value( $value, $key, $m_type );
	}

	/**
	 * Normalize the values before returing for replacement
	 *
	 * It provides some security by stripping tags. Also for signature it
	 * converts into an image.
	 *
	 * @access private
	 *
	 * @param      string  $value   The value
	 * @param      int     $key     The key
	 * @param      string  $m_type  m_type of the element
	 *
	 * @return     string  Normalized and sanitized value for replacement
	 */
	private function normalize_value( $value, $key, $m_type ) {
		$layout_element = $this->value->get_element_from_layout( array(
			'm_type' => $m_type,
			'key' => $key,
		) );
		switch ( $layout_element['type'] ) {
			case 'signature':
				$value = '<img src="' . esc_attr( $value ) . '" style="max-width: 100%; height: auto" />';
				break;
			default:
				$value = strip_tags( $value );
				break;
		}

		return $value;
	}


	/**
	 * Get the matches grouped by element m_type
	 *
	 * It removes duplicate elements found during replacement filters and
	 * returns a normalized associative array with just unique elements. Use
	 * directly in JS or where element injections are needed.
	 *
	 * @return     array  Associative array grouped by m_type
	 */
	public function get_matches() {
		// Loop through and remove duplicates
		$matches = array();
		foreach ( $this->matches as $m_type => $elements ) {
			$matches[ $m_type ] = array_unique( $elements );
		}
		return $matches;
	}

	/**
	 * Normalize escaped templates.
	 *
	 * We will provide method for escaping template like %%M0%% and it will be
	 * converted into just %M0%.
	 *
	 * @param      string  $text   The string from where text is to be removed
	 * @return     string  converted text according to matches
	 */
	public function remove_escapes( $text ) {
		return preg_replace( '/%%(M|F|O)([0-9]+)%%/', '%$1$2%', $text );
	}

	/**
	 * Enqueue Reactive JS components
	 *
	 * @param      boolean  $debug  Whether to include the debug version
	 */
	public static function enqueue() {
		wp_enqueue_script( 'eform-value-class', IPT_FSQM_Loader::$static_location . 'front/js/eform.value-class.min.js', array( 'jquery' ), IPT_FSQM_Loader::$version, true );
		wp_enqueue_script( 'eform-interactive', IPT_FSQM_Loader::$static_location . 'front/js/jquery.eform-interactive.min.js', array( 'eform-value-class' ), IPT_FSQM_Loader::$version, true );
	}
}
