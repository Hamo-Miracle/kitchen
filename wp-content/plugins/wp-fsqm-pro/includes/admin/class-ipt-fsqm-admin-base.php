<?php
/**
 * The base abstract admin class
 *
 * @author Swashata <swashata4u@gmail.com>
 * @package eForm - WordPress Form Builder
 * @subpackage Admin\Base
 * @abstract
 * @codeCoverageIgnore
 */
abstract class IPT_FSQM_Admin_Base {
	/**
	 * Duplicates the $_POST content and properly process it
	 * Holds the typecasted (converted int and floats properly and escaped html) value after the constructor has been called
	 *
	 * @var array
	 */
	public $post = array();

	/**
	 * Holds the hook of this page
	 *
	 * @var string Pagehook
	 * Should be set during the construction
	 */
	public $pagehook;

	/**
	 * The nonce for admin-post.php
	 * Should be set the by extending class
	 *
	 * @var string
	 */
	public $action_nonce;

	/**
	 * The class of the admin page icon
	 * Should be set by the extending class
	 *
	 * @var string
	 */
	public $icon;

	/**
	 * This gets passed directly to current_user_can
	 * Used for security and should be set by the extending class
	 *
	 * @var string
	 */
	public $capability;

	/**
	 * Holds the URL of the static directories
	 * Just the /static/admin/ URL and sub directories under it
	 * access it like $url['js'], ['images'], ['css'], ['root'] etc
	 *
	 * @var array
	 */
	public $url = array();

	/**
	 * Set this to true if you are going to use the WordPress Metabox appearance
	 * This will enqueue all the scripts and will also set the screenlayout option
	 *
	 * @var bool False by default
	 */
	public $is_metabox = false;

	/**
	 * Default number of columns on metabox
	 *
	 * @var int
	 */
	public $metabox_col = 2;

	/**
	 * Holds the post result message string
	 * Each entry is an associative array with the following options
	 *
	 * $key : The code of the post_result value =>
	 *
	 *      'type' => 'update' : The class of the message div update | error
	 *
	 *      'msg' => '' : The message to be displayed
	 *
	 * @var array
	 */
	public $post_result = array();

	/**
	 * The action value to be used for admin-post.php
	 * This is generated automatically by appending _post_action to the action_nonce variable
	 *
	 * @var string
	 */
	public $admin_post_action;

	/**
	 * Whether or not to print form on the admin wrap page
	 * Mainly for manually printing the form
	 *
	 * @var bool
	 */
	public $print_form;

	/**
	 * The USER INTERFACE Object
	 *
	 * @var IPT_Plugin_UIF_Admin
	 */
	public $ui;

	/**
	 * The constructor function
	 * 1. Properly copies the $_POST to $this->post on POST request
	 * 2. Calls the admin_menu() function
	 * You should have parent::__construct() for all these to happen
	 *
	 * @param boolean $gets_hooked Should be true if you wish to actually put this inside an admin menu. False otherwise
	 * It basically hooks into admin_menu and admin_post_ if true
	 */
	public function __construct( $gets_hooked = true ) {
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
			//$this->post = $_POST;

			//we do not need to check on magic quotes
			//as wordpress always adds magic quotes
			//@link http://codex.wordpress.org/Function_Reference/stripslashes_deep
			$this->post = wp_unslash( $_POST );

			//convert html to special characters
			//array_walk_recursive ($this->post, array($this, 'htmlspecialchar_ify'));
		}

		$this->ui = IPT_Plugin_UIF_Admin::instance();

		$plugin = IPT_FSQM_Loader::$abs_file;

		$this->url = array(
			'root' => plugins_url( '/static/admin/', $plugin ),
			'js' => plugins_url( '/static/admin/js/', $plugin ),
			'images' => plugins_url( '/static/admin/images/', $plugin ),
			'css' => plugins_url( '/static/admin/css/', $plugin ),
		);

		$this->post_result = array(
			1 => array(
				'type' => 'update',
				'msg' => __( 'Successfully saved the options.', 'ipt_fsqm' ),
			),
			2 => array(
				'type' => 'error',
				'msg' => __( 'Either you have not changed anything or some error has occured. Please contact the developer.', 'ipt_fsqm' ),
			),
			3 => array(
				'type' => 'okay',
				'msg' => __( 'The Master Reset was successful.', 'ipt_fsqm' ),
			),
		);

		$this->admin_post_action = $this->action_nonce . '_post_action';

		if ( $gets_hooked ) {
			//register admin_menu hook
			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );

			//register admin-post.php hook
			add_action( 'admin_post_' . $this->admin_post_action, array( &$this, 'save_post' ) );

			// Create sandbox within the admin page
			add_action( 'admin_enqueue_scripts', [ $this, 'sandbox_assets' ], 99, 1 );
		}
	}

	/*==========================================================================
	 * SYSTEM METHODS
	 *========================================================================*/


	/**
	 * Hook to the admin menu
	 * Should be overriden and also the hook should be saved in the $this->pagehook
	 * In the end, the parent::admin_menu() should be called for load to hooked properly
	 */
	public function admin_menu() {
		add_action( 'load-' . $this->pagehook, array( &$this, 'on_load_page' ) );
		//$this->pagehook = add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
		//do the above or similar in the overriden callback function
	}

	/**
	 * Use this to generate the admin page
	 * always call parent::index() so the save post is called
	 * also call $this->index_foot() after the generation of page (the last line of this function)
	 * to give some compatibility (mainly with the metaboxes)
	 *
	 * @access public
	 */
	abstract public function index();

	protected function index_head( $title = '', $print_form = true, $ui_state = 'back', $print_title = true ) {
		$this->print_form = $print_form;
		$ui_class = 'ipt_uif';

		switch ( $ui_state ) {
		case 'back' :
			$ui_class = 'ipt_uif ipt-eform-backoffice';
			break;
		case 'front' :
			$ui_class = 'ipt_uif_front ipt-eform-backoffice';
			break;
		case 'clear':
			$ui_class = '';
			break;
		default :
		case 'none' :
			$ui_class = 'ipt_uif';
		}
?>
<style type="text/css">
	<?php echo '#' . $this->pagehook; ?>-widgets .meta-box-sortables {
		margin: 0 8px;
	}
</style>
<div class="wrap ipt_uif_common <?php echo $ui_class; ?>" id="<?php echo $this->pagehook; ?>_widgets">
	<?php if ( $print_title ) : ?>
	<div class="icon32">
		<span class="ipt-icomoon-<?php echo $this->icon; ?>"></span>
	</div>
	<h2><?php echo $title; ?></h2>
	<?php $this->ui->clear(); ?>
	<?php endif; ?>
	<?php
		if ( isset( $_GET['post_result'] ) ) {
			$msg = $this->post_result[(int) $_GET['post_result']];
			if ( !empty( $msg ) ) {
				if ( $msg['type'] == 'update' || $msg['type'] == 'updated' ) {
					$this->print_update( $msg['msg'] );
				} else if ( $msg['type'] == 'okay' ) {
						$this->print_p_okay( $msg['msg'] );
					} else {
					$this->print_error( $msg['msg'] );
				}
			}
		}
?>
	<?php if ( $this->print_form ) : ?>
	<form method="post" action="admin-post.php" id="<?php echo $this->pagehook; ?>_form_primary">
		<input type="hidden" name="action" value="<?php echo $this->admin_post_action; ?>" />
		<?php wp_nonce_field( $this->action_nonce, $this->action_nonce ); ?>
		<?php if ( $this->is_metabox ) : ?>
		<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
		<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
		<?php endif; ?>
	<?php endif; ?>
	<?php do_action(  "efrom_admin_{$this->pagehook}_page_before", $this ); ?>
		<?php
	}

	/**
	 * Include this to the end of index function so that metaboxes work
	 */
	protected function index_foot( $submit = true, $save = 'Save Changes', $reset = 'Reset', $do_action = true ) {
?>
	<?php if ( $this->print_form ) : ?>
		<?php if ( true == $submit ) : ?>
		<?php $this->print_form_buttons( $save, $reset ); ?>
		<?php endif; ?>
	</form>
	<?php endif; ?>
	<div class="clear"></div>
	<?php if ( $do_action ) : ?>
	<?php do_action( "eform_admin_{$this->pagehook}_page_after", $this ); ?>
	<?php endif; ?>
</div>
<?php if ( $this->is_metabox ) : ?>
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready( function($) {
	if(postboxes) {
		// close postboxes that should be closed
		$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
		// postboxes setup
		postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
	}
});
//]]>
</script>
<?php endif; ?>
		<?php
	}

	/**
	 * Print form submit and reset buttons.
	 *
	 * @param string $save Save button text
	 * @param string|boolean $reset Reset button text, false to not print
	 * @return void
	 */
	protected function print_form_buttons( $save = 'Save Changes', $reset = 'Reset' ) {
		$buttons = array(
			array( $save, '', 'medium', 'primary', 'normal', array(), 'submit' ),
		);
		if ( $reset ) {
			$buttons[] = array( $reset, '', 'medium', 'secondary', 'normal', array(), 'reset' );
		}
		$this->ui->clear();
		$this->ui->buttons( $buttons );
	}

	/**
	 * Override to manage the save_post
	 * This should be written by all the classes extending this
	 *
	 *
	 * * General Template
	 *
	 * //process here your on $_POST validation and / or option saving
	 *
	 * //lets redirect the post request into get request (you may add additional params at the url, if you need to show save results
	 * wp_redirect(add_query_arg(array(), $_POST['_wp_http_referer']));
	 *
	 *
	 */
	public function save_post( $check_referer = true ) {
		//user permission check
		if ( !current_user_can( $this->capability ) )
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		//check nonce
		if ( $check_referer ) {
			if ( !wp_verify_nonce( $_POST[$this->action_nonce], $this->action_nonce ) )
				wp_die( __( 'Cheatin&#8217; uh?' ) );
		}

		//process here your on $_POST validation and / or option saving

		//lets redirect the post request into get request (you may add additional params at the url, if you need to show save results
		//wp_redirect(add_query_arg(array(), $_POST['_wp_http_referer']));
		//The above should be done by the extending after calling parent::save_post and processing post
	}

	/**
	 * Remove all external styles and scripts coming from other plugins
	 * which may cause compatibility issue, especially with React.
	 *
	 * You can override it for even fine tuning.
	 *
	 * @param string $hook Current page hook.
	 * @return void
	 */
	public function sandbox_assets( $hook ) {
		// Don't do anything if hook is not pointing to current page
		if ( $this->get_pagehook() !== $hook ) {
			return;
		}

		// Don't do anything if settings are disabled
		global $ipt_fsqm_settings;
		if ( ! isset( $ipt_fsqm_settings['sandboxing'] ) || ! $ipt_fsqm_settings['sandboxing'] ) {
			return;
		}
		// Get all styles and scripts, then filter
		$assets = [
			'styles'  => wp_styles(),
			'scripts' => wp_scripts(),
		];
		foreach ( $assets as $type => $asset ) {
			foreach ( $asset->registered as $handle => $dep ) {
				$src = $dep->src;
				// test if the src is coming from /wp-admin/ or /wp-includes/ or /wp-fsqm-pro/.
				if (
					is_string( $src ) &&
					strpos( $src, 'wp-admin' ) === false &&
					strpos( $src, 'wp-include' ) === false &&
					strpos( $src, 'eform-' ) === false &&
					strpos( $src, 'woocommerce' ) === false &&
					strpos( $src, 'jetpack' ) === false &&
					strpos( $src, 'debug-bar' ) === false &&
					strpos( $src, 'fsqm-' ) === false &&
					strpos( $src, 'wpq-eform' ) === false
				) {
					if ( 'scripts' === $type ) {
						wp_dequeue_script( $handle );
					} else {
						wp_dequeue_style( $handle );
					}
				}
			}
		}
	}


	/**
	 * Hook to the load plugin page
	 * This should be overriden
	 * Also call parent::on_load_page() for screenoptions
	 *
	 * @uses add_meta_box
	 */
	public function on_load_page() {
		$this->sandbox_assets( $this->get_pagehook() );
	}

	/**
	 * Get the pagehook of this class
	 *
	 * @return string
	 */
	public function get_pagehook() {
		return $this->pagehook;
	}

	/**
	 * Prints the metaboxes of a custom context
	 * Should atleast pass the $context, others are optional
	 *
	 * The screen defaults to the $this->pagehook so make sure it is set before using
	 * This should be the return value given by add_admin_menu or similar function
	 *
	 * The function automatically checks the screen layout columns and prints the normal/side columns accordingly
	 * If screen layout column is 1 then even if you pass with context side, it will be hidden
	 * Also if screen layout is 1 and you pass with context normal, it will get full width
	 *
	 * @param string  $context           The context of the metaboxes. Depending on this HTML ids are generated. Valid options normal | side
	 * @param string  $container_classes (Optional) The HTML class attribute of the container
	 * @param string  $container_style   (Optional) The RAW inline CSS style of the container
	 */
	public function print_metabox_containers( $context = 'normal', $container_classes = '', $container_style = '' ) {
		global $screen_layout_columns;
		$style = 'width: 50%;';

		//check to see if only one column has to be shown

		if ( isset( $screen_layout_columns ) && $screen_layout_columns == 1 ) {
			//normal?
			if ( 'normal' == $context ) {
				$style = 'width: 100%;';
			} else if ( 'side' == $context ) {
					$style = 'display: none;';
				}
		}

		//override for the special debug area (1 column)
		if ( 'debug' == $context ) {
			$style = 'width: 100%;';
			$container_classes .= ' debug-metabox';
		}
?>
<div class="postbox-container <?php echo $container_classes; ?>" style="<?php echo $style . $container_style; ?>" id="<?php echo ( 'normal' == $context )? 'postbox-container-1' : 'postbox-container-2'; ?>">
	<?php do_meta_boxes( $this->pagehook, $context, '' ); ?>
</div>
		<?php
	}


	/*==========================================================================
	 * INTERNAL METHODS
	 *========================================================================*/

	/**
	 * Prints error msg in WP style
	 *
	 * @param string  $msg
	 */
	protected function print_error( $msg = '', $echo = true ) {
		return $this->ui->msg_error( $msg, $echo );
	}

	protected function print_update( $msg = '', $echo = true ) {
		return $this->ui->msg_update( $msg, $echo );
	}

	protected function print_p_error( $msg = '', $echo = true ) {
		return $this->ui->msg_error( $msg, $echo );
	}

	protected function print_p_update( $msg = '', $echo = true ) {
		return $this->ui->msg_update( $msg, $echo );
	}

	protected function print_p_okay( $msg = '', $echo = true ) {
		return $this->ui->msg_okay( $msg, $echo );
	}

	/**
	 * stripslashes gpc
	 * Strips Slashes added by magic quotes gpc thingy
	 *
	 * @access protected
	 * @param string  $value
	 */
	protected function stripslashes_gpc( &$value ) {
		$value = stripslashes( $value );
	}

	protected function htmlspecialchar_ify( &$value ) {
		$value = htmlspecialchars( $value );
	}

	/*==========================================================================
	 * SHORTCUT HTML METHODS
	 *========================================================================*/


	/**
	 * Shortens a string to a specified character length.
	 * Also removes incomplete last word, if any
	 *
	 * @param string  $text The main string
	 * @param string  $char Character length
	 * @param string  $cont Continue character(…)
	 * @return string
	 */
	public function shorten_string( $text, $char, $cont = '…' ) {
		return $this->ui->shorten_string( $text, $char, $cont );
	}

	/**
	 * Get the first image from a string
	 *
	 * @param string  $html
	 * @return mixed string|bool The src value on success or boolean false if no src found
	 */
	public function get_first_image( $html ) {
		return $this->ui->get_first_image( $html );
	}

	/**
	 * Wrap a RAW JS inside <script> tag
	 *
	 * @param String  $string The JS
	 * @return String The wrapped JS to be used under HTMl document
	 */
	public function js_wrap( $string ) {
		return $this->ui->js_wrap( $string );
	}

	/**
	 * Wrap a RAW CSS inside <style> tag
	 *
	 * @param String  $string The CSS
	 * @return String The wrapped CSS to be used under HTMl document
	 */
	public function css_wrap( $string ) {
		return $this->ui->css_wrap( $string );
	}

	public function print_datetimepicker( $name, $value, $dateonly = false ) {
		if ( $dateonly ) {
			$this->ui->datepicker( $name, $value );
		} else {
			$this->ui->datetimepicker( $name, $value );
		}
	}

	/**
	 * Prints options of a selectbox
	 *
	 * @param array   $ops Should pass either an array of string ('label1', 'label2') or associative array like array('val' => 'val1', 'label' => 'label1'),...
	 * @param string  $key The key in the haystack, if matched a selected="selected" will be printed
	 */
	public function print_select_op( $ops, $key, $inner = false ) {
		$items = $this->ui->convert_old_items( $ops, $inner );
		$this->ui->select( '', $items, $key, false, false, false, false );
	}

	/**
	 * Prints a set of checkboxes for a single HTML name
	 *
	 * @param string  $name    The HTML name of the checkboxes
	 * @param array   $items   The associative array of items array('val' => 'value', 'label' => 'label'),...
	 * @param array   $checked The array of checked items. It matches with the 'val' of the haystack array
	 * @param string  $sep     (Optional) The seperator, HTML non-breaking-space (&nbsp;) by default. Can be <br /> or anything
	 */
	public function print_checkboxes( $name, $items, $checked, $sep = '&nbsp;&nbsp;' ) {
		$items = $this->ui->convert_old_items( $items );
		$this->ui->checkboxes( $name, $items, $checked, false, false, $sep );
	}

	/**
	 * Prints a set of radioboxes for a single HTML name
	 *
	 * @param string  $name    The HTML name of the checkboxes
	 * @param array   $items   The associative array of items array('val' => 'value', 'label' => 'label'),...
	 * @param string  $checked The value of checked radiobox. It matches with the val of the haystack
	 * @param string  $sep     (Optional) The seperator, two HTML non-breaking-space (&nbsp;) by default. Can be <br /> or anything
	 */
	public function print_radioboxes( $name, $items, $checked, $sep = '&nbsp;&nbsp;' ) {
		$items = $this->ui->convert_old_items( $items );
		$this->ui->radios( $name, $items, $checked, false, false, $sep );
	}

	/**
	 * Print a single checkbox
	 * Useful for printing a single checkbox like for enable/disable type
	 *
	 * @param string  $name  The HTML name
	 * @param string  $value The value attribute
	 * @param mixed   (string|bool) $checked Can be true or can be equal to the $value for adding checked attribute. Anything else and it will not be added.
	 */
	public function print_checkbox( $name, $value, $checked ) {
		if ( $value === $checked || true === $checked ) {
			$checked = true;
		}
		$this->ui->toggle( $name, '', $value, $checked );
	}

	/**
	 * Prints a input[type="text"]
	 * All attributes are escaped except the value
	 *
	 * @param string  $name  The HTML name attribute
	 * @param string  $value The value of the textbox
	 * @param string  $class (Optional) The css class defaults to regular-text
	 */
	public function print_input_text( $name, $value, $class = 'regular-text' ) {
		$this->ui->text( $name, $value, '', $class );
	}

	/**
	 * Prints a <textarea> with custom attributes
	 * All attributes are escaped except the value
	 *
	 * @param string  $name  The HTML name attribute
	 * @param string  $value The value of the textbox
	 * @param string  $class (Optional) The css class defaults to regular-text
	 * @param int     $rows  (Optional) The number of rows in the rows attribute
	 * @param int     $cols  (Optional) The number of columns in the cols attribute
	 */
	public function print_textarea( $name, $value, $class = 'regular-text', $rows = 3, $cols = 20 ) {
		$this->ui->textarea( $name, $value, '', $class );
	}


	/**
	 * Displays a jQuery UI Slider to the page
	 *
	 * @param string  $name  The HTML name of the input box
	 * @param int     $value The initial/saved value of the input box
	 * @param int     $max   The maximum of the range
	 * @param int     $min   The minimum of the range
	 * @param int     $step  The step value
	 */
	public function print_ui_slider( $name, $value, $max = 100, $min = 0, $step = 1 ) {
		$this->ui->slider( $name, $value, $min, $max, $step );
	}

	/**
	 * Prints a ColorPicker
	 *
	 * @param string  $name  The HTML name of the input box
	 * @param string  $value The HEX color code
	 */
	public function print_cpicker( $name, $value ) {
		$this->ui->colorpicker( $name, $value );
	}

	/**
	 * Prints a input box with an attached upload button
	 *
	 * @param string  $name  The HTML name of the input box
	 * @param string  $value The value of the input box
	 */
	public function print_uploadbutton( $name, $value ) {
		$this->ui->upload( $name, $value );
	}
}
