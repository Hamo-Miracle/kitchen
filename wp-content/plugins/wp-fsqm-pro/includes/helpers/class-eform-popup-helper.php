<?php
/**
 * A helper class to properly populate PopUp forms both for widgets and shortcode
 *
 * @package eForm - WordPress Form Builder
 * @subpackage Helpers
 * @author Swashata Ghosh <swashata@wpquark.com>
 */
class EForm_Popup_Helper {
	/**
	 * Form ID for which popup is to be generated
	 *
	 * @var        integer
	 */
	protected $form_id = 0;

	/**
	 * Configuration array
	 *
	 * @var        array
	 */
	protected $config = array();

	/**
	 * Constructor
	 *
	 * @param      int    $form_id  The form identifier
	 * @param      array  $config   The configuration array
	 */
	public function __construct( $form_id, $config ) {
		$this->form_id = $form_id;
		$config = wp_parse_args( $config, array(
			'label' => '',
			'color' => '',
			'bgcolor' => '',
			'position' => '',
			'style' => '',
			'header' => '',
			'subtitle' => '',
			'icon' => '',
			'width' => '',
			'isElementor' => false,
		) );
		$this->config = $config;
	}

	/**
	 * Initialize the popup JS which would be used by our JS to populate the
	 * popups
	 *
	 * Call this to actually print the popup forms and buttons
	 *
	 * @return     boolean  false if an invalid form was passed, true on success
	 */
	public function init_js() {
		// Globals
		global $ipt_fsqm_popup_forms, $ipt_eform_popup_config;
		self::init_globals();
		// Get the Form and URL
		$form = IPT_FSQM_Form_Elements_Static::get_form( $this->form_id );
		if ( ! $form ) {
			return false;
		}
		$form_urls = IPT_FSQM_Form_Elements_Static::standalone_permalink_parts( $this->form_id );
		// Configure the JSON
		$config = $this->config;
		$config['formID'] = $this->form_id;
		$config['header'] = str_replace( '%FORM%', $form->name, $config['header'] );
		$config['url'] = $form_urls['url'];
		$ipt_fsqm_popup_forms[] = $this->form_id;
		$ipt_eform_popup_config[] = $config;
		// Print stuff
		?>
<script type="text/javascript">
	if ( window.iptFSQMModalPopupForms == undefined ) {
		window.iptFSQMModalPopupForms = [];
	}
	window.iptFSQMModalPopupForms[window.iptFSQMModalPopupForms.length] = <?php echo json_encode( $config ); ?>;
</script>
		<?php
		return true;
	}

	/**
	 * Print popup form modal divs
	 *
	 * Call this at wp_footer to print the divs needed for iziModal to showup
	 *
	 * @return     boolean  true if popup was configured false if no popups
	 */
	public static function print_popup_modals() {
		global $ipt_fsqm_popup_forms, $ipt_eform_popup_config;
		self::init_globals();
		if ( count( $ipt_fsqm_popup_forms ) == 0 ) {
			return false;
		}
		// Add the scripts + style
		wp_enqueue_script( 'iziModal', IPT_FSQM_Loader::$bower_components . 'izimodal/js/iziModal.min.js', array( 'jquery' ), IPT_FSQM_Loader::$version, true );
		wp_enqueue_style( 'iziModal.css', IPT_FSQM_Loader::$bower_components . 'izimodal/css/iziModal.min.css', array(), IPT_FSQM_Loader::$version );
		wp_enqueue_script( 'ba-throttle-debounce', IPT_FSQM_Loader::$bower_components . 'jquery-throttle-debounce/jquery.ba-throttle-debounce.min.js', array( 'jquery' ), IPT_FSQM_Loader::$version, true );
		wp_enqueue_script( 'ipt-fsqm-modal-popup', IPT_FSQM_Loader::$static_location . 'front/js/ipt-fsqm-modal-popup.min.js', array( 'jquery', 'iziModal', 'ba-throttle-debounce' ), IPT_FSQM_Loader::$version );
		wp_enqueue_style( 'ipt-fsqm-modal-popup-css', IPT_FSQM_Loader::$static_location . 'front/css/modal-popup/ipt-fsqm-modal-popup.css', array(), IPT_FSQM_Loader::$version );

		// Make things unique
		$ipt_fsqm_popup_forms = array_unique( $ipt_fsqm_popup_forms );

		// Now print them all
		?>
		<?php foreach ( $ipt_fsqm_popup_forms as $key => $form_id ) : ?>
			<div id="ipt-fsqm-popup-form-<?php echo $form_id ?>" data-eform-popup="<?php echo esc_attr( json_encode( $ipt_eform_popup_config[ $key ] ) ) ?>" class="eform-popup-modal"></div>
		<?php endforeach; ?>
		<?php
		return true;
	}

	/**
	 * Initialize the globals needed by and for popups
	 */
	protected static function init_globals() {
		global $ipt_fsqm_popup_forms, $ipt_eform_popup_config;
		if ( ! is_array( $ipt_fsqm_popup_forms ) ) {
			$ipt_fsqm_popup_forms = array();
		}
		if ( ! isset( $ipt_eform_popup_config ) ) {
			$ipt_eform_popup_config = array();
		}
	}
}
