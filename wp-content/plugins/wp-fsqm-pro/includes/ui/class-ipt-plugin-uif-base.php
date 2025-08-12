<?php
/**
 * The base UI Class
 *
 * This will inject some useful stuff needed by all types of UI classes.
 *
 * Generates all user interface/form elements
 * It needs to have the ipt-plugin-uif.css and ipt-plugin-uif.js file
 *
 * @depends jQuery, jQueryUI{core, widget, tabs, slider, spinner, dialog, mouse, datepicker, draggable, droppable, sortable, progressbar}
 *
 * @package eForm - WordPress Form Builder
 * @subpackage UI\Base
 * @author Swashata Ghosh <swashata@wpquark.com>
 * @abstract
 */
abstract class IPT_Plugin_UIF_Base {
	/**
	 * URL to the bower_components directory From this we load libraries as-is
	 *
	 * @var        string $bower_components plugins_url to bower_components
	 * directory
	 */
	public $bower_components;
	/**
	 * URL to the bower_builds directory from where we load concatenated and
	 * compressed libraries
	 *
	 * @var        string  $bower_builds  plugins_url to bower_builds directory
	 */
	public $bower_builds;

	/**
	 * URL to the static directory from where we load eForm static CSS and JS
	 * files
	 *
	 * @var        string  $static_location  plugins_url to the static directory
	 */
	public $static_location;

	/**
	 * Plugin Version used within enqueue
	 *
	 * It reflects the IPT_FSQM_Loader::$version
	 *
	 * @see        IPT_FSQM_Loader::$version
	 */
	public $version;

	/*==========================================================================
	 * FILE DEPENDENCIES
	 *========================================================================*/
	/**
	 * Enqueue assets through wp_enqueue API
	 *
	 * Handles all kinds of CSS and JS enqueue
	 *
	 * @param      array  $ignore_css           The ignore css
	 * @param      array  $ignore_js            The ignore js
	 * @param      array  $additional_css       The additional css
	 * @param      array  $additional_js        The additional js
	 * @param      array  $additional_localize  The additional localize
	 */
	public function enqueue( $ignore_css = array(), $ignore_js = array(), $additional_css = array(), $additional_js = array(), $additional_localize = array() ) {
		global $wp_locale;
		$datetime_l10n = array(
			'closeText'         => __( 'Done', 'ipt_fsqm' ),
			'currentText'       => __( 'Today', 'ipt_fsqm' ),
			'tcurrentText' => __( 'Now', 'ipt_fsqm' ),
			'monthNames'        => array_values( $wp_locale->month ),
			'monthNamesShort'   => array_values( $wp_locale->month_abbrev ),
			'monthStatus'       => __( 'Show a different month', 'ipt_fsqm' ),
			'dayNames'          => array_values( $wp_locale->weekday ),
			'dayNamesShort'     => array_values( $wp_locale->weekday_abbrev ),
			'dayNamesMin'       => array_values( $wp_locale->weekday_initial ),
			// get the start of week from WP general setting
			'firstDay'          => get_option( 'start_of_week' ),
			// is Right to left language? default is false
			'isRTL'             => $wp_locale->is_rtl(),
			'amNames' => array( __( 'AM', 'ipt_fsqm' ), __( 'A', 'ipt_fsqm' ) ),
			'pmNames' => array( __( 'PM', 'ipt_fsqm' ), __( 'P', 'ipt_fsqm' ) ),
			/* translators: Change %s to the time suffix. %s is always replaced by an empty string */
			'timeSuffix' => sprintf( _x( '%s', 'timeSuffix', 'ipt_fsqm' ), '' ),
			'timeOnlyTitle' => __( 'Choose Time', 'ipt_fsqm' ),
			'timeText' => __( 'Time', 'ipt_fsqm' ),
			'hourText' => __( 'Hour', 'ipt_fsqm' ),
			'minuteText' => __( 'Minute', 'ipt_fsqm' ),
			'secondText' => __( 'Second', 'ipt_fsqm' ),
			'millisecText' => __( 'Millisecond', 'ipt_fsqm' ),
			'microsecText' => __( 'Microsecond', 'ipt_fsqm' ),
			'timezoneText' => __( 'Timezone', 'ipt_fsqm' ),
		);

		// Some shortcut URLs
		$static_location = $this->static_location;
		$bower_components = $this->bower_components;
		$bower_builds = $this->bower_builds;
		$version = IPT_FSQM_Loader::$version;

		// Styles
		$styles = array(
			'ipt-plugin-uif-jquery-icon' => array( $static_location . 'fonts/jquery.iconfont/jquery-ui.icon-font.min.css', array() ),
			'ipt-icomoon-fonts' => array( $static_location . 'fonts/icomoon/icomoon.min.css', array() ),
		);
		// Add the additionals
		$styles = $styles + $additional_css;
		foreach ( $styles as $style_id => $style_prop ) {
			if ( ! in_array( $style_id, $ignore_css ) ) {
				if ( empty( $style_prop ) ) {
					wp_enqueue_style( $style_id );
				} else {
					wp_enqueue_style( $style_id, $style_prop[0], $style_prop[1], $version );
				}
			}
		}

		// Scripts
		$scripts = array(
			'jquery-ui-core' => array(),
			'jquery-ui-widget' => array(),
			'jquery-ui-mouse' => array(),
			'jquery-ui-button' => array(),
			// Add touch punch #45
			'jquery-touch-punch' => array(),
			'jquery-ui-draggable' => array(),
			'jquery-ui-droppable' => array(),
			'jquery-ui-sortable' => array(),
			'jquery-ui-datepicker' => array(),
			'jquery-ui-dialog' => array(),
			'jquery-ui-tabs' => array(),
			'jquery-ui-slider' => array(),
			'jquery-ui-spinner' => array(),
			'jquery-ui-progressbar' => array(),
			/* Give more generic names to libraries #19 */
			'jquery-timepicker-addon' => array( $bower_components . 'jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.js', array( 'jquery', 'jquery-ui-datepicker' ) ),
			'jquery-print-element' => array( $bower_components . 'jQuery.printElement/dist/jquery.printelement.min.js', array( 'jquery' ) ),
			'jquery-mousewheel' => array( $bower_components . 'jquery-mousewheel/jquery.mousewheel.min.js', array( 'jquery' ) ),
			'jquery-json' => array( $bower_components . 'jquery-json/dist/jquery.json.min.js', array( 'jquery' ) ),
		);
		// Add additionals
		$scripts = $scripts + $additional_js;

		// Localization information
		$scripts_localize = array(
			'jquery-timepicker-addon' => array(
				'object_name' => 'iptPluginUIFDTPL10n',
				'l10n' => $datetime_l10n,
			),
		);
		// Add the additional ones
		$scripts_localize = $scripts_localize + $additional_localize;

		foreach ( $scripts as $script_id => $script_prop ) {
			if ( ! in_array( $script_id, $ignore_js ) ) {
				if ( empty( $script_prop ) ) {
					wp_enqueue_script( $script_id );
				} else {
					wp_enqueue_script( $script_id, $script_prop[0], $script_prop[1], $version );
				}
				if ( isset( $scripts_localize[$script_id] ) && is_array( $scripts_localize[$script_id] ) && isset( $scripts_localize[$script_id]['object_name'] ) && isset( $scripts_localize[$script_id]['l10n'] ) ) {
					wp_localize_script( $script_id, $scripts_localize[$script_id]['object_name'], $scripts_localize[$script_id]['l10n'] );
				}
			}
		}

		do_action( 'ipt_plugin_ui_enqueue', $this );
	}

	public function enqueue_datatables() {
		wp_enqueue_style( 'ipt-fsqm-yadcf-css', IPT_FSQM_Loader::$bower_builds . 'yadcf/css/jquery.dataTables.yadcf.min.css', array(), IPT_FSQM_Loader::$version, 'all' );
		wp_enqueue_script( 'ipt-fsqm-datatable', IPT_FSQM_Loader::$bower_components . 'datatables.net/js/jquery.dataTables.min.js', array( 'jquery' ), IPT_FSQM_Loader::$version, true );
		wp_enqueue_script( 'ipt-fsqm-datatable-jqui', IPT_FSQM_Loader::$bower_components . 'datatables.net-jqui/js/dataTables.jqueryui.min.js', [ 'jquery' ], IPT_FSQM_Loader::$version, true );
		wp_enqueue_script( 'ipt-fsqm-datatable-yadcf', IPT_FSQM_Loader::$bower_builds . 'yadcf/js/jquery.dataTables.yadcf.min.js', array( 'ipt-fsqm-datatable' ), IPT_FSQM_Loader::$version, true );
		wp_enqueue_script( 'ipt-fsqm-datatable-responsive', IPT_FSQM_Loader::$bower_components . 'datatables.net-responsive/js/dataTables.responsive.min.js', [ 'ipt-fsqm-datatable' ], IPT_FSQM_Loader::$version, true );
		wp_enqueue_script( 'ipt-fsqm-datatable-responsive-jqui', IPT_FSQM_Loader::$bower_components . 'datatables.net-responsive-jqui/js/responsive.jqueryui.min.js', [ 'ipt-fsqm-datatable' ], IPT_FSQM_Loader::$version, true );
	}

	/*==========================================================================
	 * IcoMoon Data and File Names
	 *========================================================================*/
	 public function get_icon_image_names() {
	 	include dirname( __FILE__ ) . '/var-ipt-icomoon-icons.php';
		return apply_filters( 'ipt_uif_valid_icons_image', $icomoon_images );
	}

	public function get_icon_image_name( $hex ) {
		$icons = $this->get_icon_image_names();
		if ( isset( $icons[$hex] ) ) {
			return $icons[$hex];
		} else {
			return false;
		}
	}

	public function get_valid_icons() {
		include dirname( __FILE__ ) . '/var-ipt-icomoon-icons.php';
		return apply_filters( 'ipt_uif_valid_icons_hex', $icomoon_icons );
	}

	/**
	 * Get the PNG image for an icon
	 *
	 * It dynamically creates the image if not present in
	 * `wp-content/uploads/eform-icons` directory and serves the URL to the
	 * image
	 *
	 * @param      integer  $hex    The hexadecimal code for the icon
	 * @param      string   $color  (Optional) The color of the icon. Defaults
	 *                              #333333
	 * @param      integer  $fz     Font Size in which the image will be
	 *                              generated
	 *
	 * @return     string   The URL of the image for the provided icon
	 */
	public function get_image_for_icon( $hex, $color = '#333333', $fz = 32 ) {
		// Firstly we normalize the color
		try {
			$gd_color = GDText\Color::parseString( $color );
		} catch ( Exception $e ) {
			// Invalid color, so use the default one
			return $this->get_image_for_icon( $hex, '#333333', $fz );
		}

		// First check if the hex is valie
		$image = $this->get_icon_image_name( $hex );
		if ( false == $image ) {
			return '';
		}

		// Calculate the upload directory
		$upload_dir = wp_upload_dir();
		$color_simple = str_replace( '#', '', $color );

		// Image path base
		$image_path_base = $upload_dir['basedir'] . '/eform-icons/' . $color_simple . '/';

		// Check if the image is already present in the system
		$image_name = str_replace( '.png', '', $image ) . '-' . $fz . '.png';
		$image_url = $upload_dir['baseurl'] . '/eform-icons/' . $color_simple . '/' . $image_name;
		if ( file_exists( $image_path_base . $image_name ) ) {
			return $image_url;
		}

		// Image not present, so lets create
		// First make the directory
		if ( ! wp_mkdir_p( $image_path_base ) ) {
			error_log( 'Could not create directory for storing eForm icon image' );
			return '';
		}

		// Let's create the image
		$im = imagecreatetruecolor( $fz + 4, $fz + 4 );
		// For transparency
		imagesavealpha( $im, true );
		$transparent = imagecolorallocatealpha( $im, 0, 0, 0, 127 );
		imagefill( $im, 0, 0, $transparent );

		// Now make use of the box
		$box = new GDText\Box( $im );
		$box->setFontFace( IPT_EFORM_ABSPATH . 'static/fonts/icomoon/ipt-icomoon.ttf' );
		$box->setFontColor( $gd_color );
		$box->setFontSize( $fz );
		$box->setBox( 2, 2, $fz + 2, $fz + 2 );
		$box->setTextAlign( 'center', 'center' );
		// A clever implementation to get the actual UTF-8 character for the font
		// Maybe we could use pack, but let's make peace with it.
		$box->draw( html_entity_decode( '&#x' . dechex( $hex ) . ';', ENT_NOQUOTES, 'UTF-8' ) );

		if ( imagepng( $im, $image_path_base . $image_name ) ) {
			return $image_url;
		} else {
			error_log( 'Could not create eForm icon image file' );
			return '';
		}
	}

	/*==========================================================================
	 * ICON MENU
	 *========================================================================*/
	public function iconmenu( $items, $alignment = 'center' ) {
?>
<ul class="ipt_uif_ul_menu ipt_uif_align_<?php echo esc_attr( 'center' ); ?>">
	<?php foreach ( $items as $item ) : ?>
	<?php
		$href = '' == $item['url'] ? 'javascript:;' : esc_attr( $item['url'] );
		$text = '' == trim( $item['text'] ) ? '' : $item['text'];
?>
	<li>
		<a href="<?php echo $href; ?>">
			 <?php if ( isset( $item['icon'] ) && $item['icon'] !== '' && $item['icon'] != 'none' ) : ?>
			<i <?php if ( is_numeric( $item['icon'] ) ) : ?> class="ipticm" data-ipt-icomoon="&#x<?php echo dechex( $item['icon'] ) ?>;"<?php else : ?> class="ipt-icomoon-<?php echo esc_attr( $item['icon'] ); ?> ipticm"<?php endif; ?>>
			</i>
			<?php endif; ?>
			<?php echo $text; ?></a>
	</li>
	<?php endforeach; ?>
</ul>
		<?php
	}

	/*==========================================================================
	 * SYSTEM API
	 *========================================================================*/
	/**
	 * Returns an instance object.
	 *
	 * Creates one if not already instantiated.
	 *
	 * @throws     LogicException       When the extending class has not declared the static variable
	 *
	 * @return     IPT_Plugin_UIF_Base
	 */
	public static function instance() {
		if ( ! isset( static::$instance ) ) {
			throw new LogicException( 'Child Class have not declared the instance variable', 99 );
		}
		if ( false === static::$instance ) {
			// @codeCoverageIgnoreStart
			static::$instance = new static();
			// @codeCoverageIgnoreEnd
		}
		return static::$instance;
	}

	/**
	 * Constructor method
	 *
	 * We declare it as protected so that the class can be truly singleton
	 */
	protected function __construct() {
		$this->text_domain = 'ipt_fsqm';
		$this->bower_components = IPT_FSQM_Loader::$bower_components;
		$this->bower_builds = IPT_FSQM_Loader::$bower_builds;
		$this->static_location = IPT_FSQM_Loader::$static_location;
		$this->version = IPT_FSQM_Loader::$version;
	}

	/*==========================================================================
	 * INTERNAL HTML FORM ELEMENTS METHODS
	 * Can also be used publicly
	 *========================================================================*/
	/**
	 * Prints an hidden input
	 * @param  string $name    Form Element Name
	 * @param  string $value   Form Element Value
	 * @param  array  $classes Additional CSS classes
	 * @return void
	 */
	public function hidden_input( $name, $value, $classes = array(), $sayt_exclude = false ) {
		$classes = (array) $classes;
		$classes[] = 'ipt_uif_hidden_input';
		?>
<input<?php if ( $sayt_exclude ) echo ' data-sayt-exclude'; ?> class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" type="hidden" id="<?php echo $this->generate_id_from_name( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" />
		<?php
	}

	/**
	 * Print multiple hidden input
	 *
	 * @param      array   $hiddens      Associative array of hidden inputs with
	 *                                   key => val pairing
	 * @param      string  $name_prefix  HTML name prefix
	 */
	public function hiddens( $hiddens, $name_prefix = '' ) {
		if ( !is_array( $hiddens ) || empty( $hiddens ) ) {
			return;
		}
		?>
<?php foreach ( $hiddens as $h_key => $h_val ) : ?>
	<?php $name = $name_prefix != '' ? $name_prefix . '[' . $h_key . ']' : $h_key; ?>
	<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $h_val ); ?>" id="<?php echo $this->generate_id_from_name( $name ); ?>" />
<?php endforeach; ?>
		<?php
	}


	/**
	 * Create a div
	 *
	 * @param      mixed(array|string)  $styles    The HTML style. Can be a
	 *                                             single string when only one
	 *                                             div will be produced, or
	 *                                             array in which case the 0th
	 *                                             style will be used to create
	 *                                             the main div and other styles
	 *                                             will be nested inside as
	 *                                             individual divs.
	 * @param      mixed(array|string)  $callback  The callback function to
	 *                                             populate.
	 * @param      int                  $scroll    The scroll height value in
	 *                                             pixels. 0 if no scroll.
	 * @param      string               $id        HTML ID
	 * @param      array                $classes   HTML classes
	 */
	public function div( $styles, $callback, $scroll = 0, $id = '', $classes = array() ) {
		if ( ! $this->check_callback( $callback ) ) {
			echo 'Invalid Callback supplied';
			return;
		}
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}

		if ( is_array( $styles ) && count( $styles ) > 1 ) {
			$classes = array_merge( $classes, (array) $styles[0] );
		} else {
			$classes[] = (string) $styles;
		}
		$style_attr = '';
		if ( (int) $scroll != 0 ) {
			$style_attr = ' style="max-height: ' . (int) $scroll . 'px; overflow: auto;"';
			$classes[] = 'ipt_uif_scroll';
		}
		$id_attr = '';
		if ( trim( $id ) != '' ) {
			$id_attr = ' id="' . esc_attr( trim( $id ) ) . '"';
		}
?>
<div class="<?php echo implode( ' ', $classes ); ?>"<?php echo $id_attr . $style_attr; ?>>
	<?php if ( is_array( $styles ) && count( $styles ) > 1 ) : ?>
	<?php for ( $i = 1; $i < count( $styles ); $i++ ) : ?>
	<div class="<?php echo implode( ' ', (array) $styles[$i] ); ?>">
	<?php endfor; ?>
	<?php endif; ?>

	<?php call_user_func_array( $callback[0], $callback[1] ); ?>

	<?php if ( is_array( $styles ) && count( $styles ) > 1 ) : ?>
	<?php for ( $i = 1; $i < count( $styles ); $i++ ) : ?>
	</div>
	<?php endfor; ?>
	<?php endif; ?>
</div>
		<?php
	}

	public function clear() {
		echo '<div class="clear"></div>';
	}

	/**
	 * Convert a valid state of HTML form elements to proper attribute="value" pair
	 *
	 * @param string  $state The state of the HTML item
	 * @return string
	 */
	public function convert_state_to_attribute( $state ) {
		$output = '';
		switch ( $state ) {
		case 'disable' :
		case 'disabled' :
			$output = ' disabled="disabled"';
			break;
		case 'readonly' :
		case 'noedit' :
			$output = ' readonly="readonly"';
			break;
		}
		return $output;
	}

	/**
	 * Converts valid size string to proper HTML class value
	 *
	 * @param string  $size Valid size string
	 * @return string
	 */
	public function convert_size_to_class( $size ) {
		$class = '';
		switch ( $size ) {
		case 'regular' :
		case 'medium' :
			$class = 'regular-text';
			break;
		case 'large' :
		case 'big' :
			$class = 'large-text';
			break;
		case 'small' :
		case 'tiny' :
			$class = 'small-text';
			break;
		case 'fit' :
			$class = 'fit-text';
			break;
		default :
			$class = esc_attr( $size );
		}
		return $class;
	}

	/**
	 * Generate Label for an element
	 *
	 * @param string  $name The name of the element
	 * @param string  $text
	 * @param string $id
	 * @param array $classes
	 */
	public function generate_label( $name, $text, $id = '', $classes = array() ) {
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_label';
		$label_for = $this->generate_id_from_name( $name, $id );
?>
<label class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"<?php echo ! empty( $label_for ) ? ' for="' . $label_for . '"' : ''; ?>><?php echo apply_filters( 'ipt_uif_label', $text ); ?></label>
		<?php
	}

	public function generate_id_from_name( $name, $id = '' ) {
		if ( '' == trim( $id ) ) {
			return esc_attr( str_replace( array( '[', ']' ), array( '_', '' ), trim( $name ) ) );
		} else {
			return esc_attr( trim( $id ) );
		}
	}

	public function convert_data_attributes( $data ) {
		if ( false == $data || !is_array( $data ) || empty( $data ) ) {
			return '';
		}

		$data_attr = '';
		foreach ( $data as $d_key => $d_val ) {
			$data_value = is_string( $d_val ) ? $d_val : json_encode( $d_val );
			$data_attr .= ' data-' . esc_attr( $d_key ) . '="' . esc_attr( $data_value ) . '"';
		}

		return $data_attr;
	}

	public function convert_html_attributes( $atts ) {
		if ( false == $atts || ! is_array( $atts ) || empty( $atts ) ) {
			return '';
		}

		$html_atts = '';
		foreach ( $atts as $attr => $val ) {
			$html_atts .= ' ' . $attr . '="' . esc_attr( $val ) . '"';
		}

		return $html_atts;
	}


	public function convert_validation_class( $validation = false ) {
		if ( $validation == false || !is_array( $validation ) || empty( $validation ) ) {
			return ' check_me ';
		}

		$classes = array();

		//check if required
		if ( true == $validation['required'] ) {
			$classes[] = 'required';
		}

		//check for any custom regex
		if ( isset( $validation['filters'] ) && is_array( $validation['filters'] ) ) {
			if ( isset( $validation['filters']['type'] ) ) {
				if ( 'all' != $validation['filters']['type'] ) {
					$classes[] = 'custom[' . esc_attr( $validation['filters']['type'] ) . ']';
				}

				// Now delete the unnecessary filters
				if ( in_array( $validation['filters']['type'], array( 'number', 'integer' ) ) ) {
					$validation['filters']['minSize'] = '';
					$validation['filters']['maxSize'] = '';
				}
				if ( in_array( $validation['filters']['type'], array( 'all', 'onlyNumberSp', 'onlyLetterSp', 'onlyLetterNumber', 'onlyLetterNumberSp', 'noSpecialCharacter' ) ) ) {
					$validation['filters']['min'] = '';
					$validation['filters']['max'] = '';
				}
			}



			//check for others
			foreach ( $validation['filters'] as $f_key => $f_val ) {
				if ( 'type' == $f_key ) {
					continue;
				}

				if ( $f_val != '' ) {
					$classes[] = esc_attr( $f_key ) . '[' . esc_attr( $f_val ) . ']';
				}
			}
		}

		if ( isset( $validation['funccall'] ) && is_string( $validation['funccall'] ) ) {
			$classes[] = 'funcCall[' . $validation['funccall'] . ']';
		}

		if ( isset( $validation['equals'] ) && ! empty( $validation['equals'] ) ) {
			$classes[] = 'equals[' . $validation['equals'] . ']';
		}

		// Input masking
		if ( isset( $validation['mask'] ) && $validation['mask']['enabled'] ) {
			$classes[] = 'funcCall[eFormInputMaskValidate]';
		}

		$added = implode( ',', $classes );

		if ( $added != '' ) {
			return ' check_me validate[' . $added . ']';
		} else {
			return ' check_me ';
		}
	}

	/**
	 * Get the first image from a string
	 *
	 * @param string  $html
	 * @return mixed string|bool The src value on success or boolean false if no src found
	 */
	public function get_first_image( $html ) {
		$matches = array();
		$output = preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $html, $matches );
		if ( !$output ) {
			return false;
		}
		else {
			$src = $matches[1][0];
			return trim( $src );
		}
	}


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
		$text = strip_tags( strip_shortcodes( $text ) );
		$text = substr( $text, 0, $char ); //First chop the string to the given character length
		if ( substr( $text, 0, strrpos( $text, ' ' ) )!='' ) $text = substr( $text, 0, strrpos( $text, ' ' ) ); //If there exists any space just before the end of the chopped string take upto that portion only.
		//In this way we remove any incomplete word from the paragraph
		$text = $text.$cont; //Add continuation ... sign
		return $text; //Return the value
	}

	/**
	 * Wrap a RAW JS inside <script> tag
	 *
	 * @param String  $string The JS
	 * @return String The wrapped JS to be used under HTMl document
	 */
	public function js_wrap( $string ) {
		return "\n<script type='text/javascript'>\n" . $string . "\n</script>\n";
	}

	/**
	 * Wrap a RAW CSS inside <style> tag
	 *
	 * @param String  $string The CSS
	 * @return String The wrapped CSS to be used under HTMl document
	 */
	public function css_wrap( $string ) {
		return "\n<style type='text/css'>\n" . $string . "\n</style>\n";
	}

	/*==========================================================================
	 * OTHER INTERNAL METHODS
	 *========================================================================*/

	public function standardize_items( $items ) {
		$new_items = array();
		if ( !is_array( $items ) ) {
			$items = (array) $items;
		}
		foreach ( $items as $i_key => $item ) {
			if ( is_array( $item ) ) {
				if ( isset( $item['value'] ) ) {
					$new_items[] = array(
						'label' => isset( $item['label'] ) ? $item['label'] : ucfirst( $item['value'] ),
						'value' => esc_attr( (string) $item['value'] ),
						'data' => isset( $item['data'] ) ? (array) $item['data'] : array(),
						'class' => isset( $item['class'] ) ? $item['class'] : '',
						'attr' => isset( $item['attr'] ) ? (array) $item['attr'] : array(),
					);
				}
			} else if ( is_string( $item ) ) {
				if ( is_numeric( $i_key ) ) {
					$new_items[] = array(
						'label' => ucfirst( $item ),
						'value' => esc_attr( (string) $item ),
						'data' => array(),
						'class' => '',
						'attr' => array(),
					);
				} else {
					$new_items[] = array(
						'label' => $item,
						'value' => esc_attr( (string) $i_key ),
						'data' => array(),
						'class' => '',
						'attr' => array(),
					);
				}
			}
		}

		return $new_items;
	}

	public function convert_old_items( $ops, $inner = false ) {
		$items = array();
		foreach ( $ops as $o_key => $op ) {
			if ( !is_array( $op ) ) {
				if ( !$inner ) {
					$items[] = array(
						'label' => ucfirst( $op ),
						'value' => $op,
					);
				} else {
					$items[] = array(
						'label' => $op,
						'value' => $o_key,
					);
				}
			} else {
				$items[] = array(
					'label' => $op['label'],
					'value' => $op['val'],
				);
			}
		}
		return $items;
	}

	/**
	 * Checks if passed variable is a valid callback with proper format
	 *
	 * Normalizes some use cases
	 *
	 * @param      array    $callback  The callback method/function with
	 *                                 parameters
	 *
	 * @return     boolean  true if a proper callback with parameters, false otherwise
	 */
	public function check_callback( &$callback ) {
		//var_dump($callback);
		// Can not be callback if not string or array
		if ( ! is_array( $callback ) && ! is_string( $callback ) ) {
			return false;
		}

		// Create a backup of the callback
		$callback_backup = $callback;

		// Standardize the variable
		// Should be an array with 0 => callable, 1 => parameters
		// So array( 'function', $params ) or array( array( obj, 'method' ), $params )
		if ( is_string( $callback ) ) {
			// Possibility of single function name
			$callback = array( $callback_backup, array() );
		} elseif ( is_array( $callback ) ) {
			// Possibility of
			// array( object, method )
			// or array( array( object,method ), arguments )
			// or array( function, arguments )
			if ( is_array( $callback[0] ) ) {
				// definitely array( array( object,method ), arguments )
				// just create null argument if not present
				if ( ! isset( $callback[1] ) ) {
					$callback[1] = array();
				} else {
					$callback[1] = (array) $callback[1];
				}
			} elseif ( is_object( $callback[0] ) || class_exists( $callback[0] ) ) {
				// definitely array( object, method ) or array( class, method )
				// put it under $callback[0] and append null set of arguments
				$callback = array( $callback_backup, array() );
			} else {
				// definitely array( function, $params )
				// just create null arguments if not present
				if ( ! isset( $callback[1] ) ) {
					$callback[1] = array();
				} else {
					$callback[1] = (array) $callback[1];
				}
			}
		}

		// All normalized
		// Now just check if callable
		if ( is_callable( $callback[0] ) ) {
			return true;
		}
		return false;
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

	/**
	 * Lightens/darkens a given colour (hex format), returning the altered
	 * colour in hex format.7
	 *
	 * @link       {https://gist.github.com/stephenharris/5532899}
	 *
	 * @param      str    $hex      Colour as hexadecimal (with or without
	 *                              hash);
	 * @param      float  $percent  Decimal ( 0.2 = lighten by 20%(), -0.4 =
	 *                              darken by 40%() )
	 *
	 * @return     str    Lightened/Darkend colour as hexadecimal (with hash);
	 */
	public function color_luminance( $hex, $percent ) {
		// validate hex string
		$hex = preg_replace( '/[^0-9a-f]/i', '', $hex );
		$hex = strtolower( $hex );

		// Is invalid color?
		if ( strlen( $hex ) < 3 ) {
			$hex = '000';
		}

		// Is short notation?
		if ( strlen( $hex ) < 6 ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		$new_hex = '#';
		// convert to decimal and change luminosity
		for ( $i = 0; $i < 3; $i++ ) {
			$dec = hexdec( substr( $hex, $i * 2, 2 ) );
			$dec = min( max( 0, ( $dec + 1 ) * ( 1 + $percent ) ), 255 );
			$new_hex .= str_pad( dechex( $dec ), 2, 0, STR_PAD_LEFT );
		}

		return $new_hex;
	}

	/**
	 * Converts seconds to readable W days, X hours, Y minutes, Z seconds
	 *
	 * @param      integer  $seconds   The number of second
	 * @param      string   $for_zero  What to return when 0 is passed as
	 *                                 seconds. If empty string (default) then 0
	 *                                 seconds will be returned.
	 *
	 * @return     string
	 */
	public function seconds_to_words( $seconds, $for_zero = '' ) {
		// If zero second, then return zero
		if ( empty( $seconds ) || $seconds <= 0 ) {
			return '' == $for_zero ? __( '0 seconds', 'ipt_fsqm' ) : $for_zero;
		}

		// Prepare return
		$ret = array();

		/*** get the days ***/
		$days = intval( intval( $seconds ) / ( 3600 * 24 ) );
		if ( $days > 0 ) {
			$ret[] = sprintf( _n( '%1$d day', '%1$d days', $days, 'ipt_fsqm' ), $days );
		}

		/*** get the hours ***/
		$hours = ( intval( $seconds ) / 3600 ) % 24;
		if ( $hours > 0 ) {
			$ret[] = sprintf( _n( '%1$d hour', '%1$d hours', $hours, 'ipt_fsqm' ), $hours );
		}

		/*** get the minutes ***/
		$minutes = ( intval( $seconds ) / 60 ) % 60;
		if ( $minutes > 0 ) {
			$ret[] = sprintf( _n( '%1$d minute', '%1$d minutes', $minutes, 'ipt_fsqm' ), $minutes );
		}

		/*** get the seconds ***/
		$seconds = intval( $seconds ) % 60;
		if ( $seconds > 0 ) {
			$ret[] = sprintf( _n( '%1$d second', '%1$d seconds', $seconds, 'ipt_fsqm' ), $seconds );
		}

		return implode( _x( ', ', 'secondstowords', 'ipt_fsqm' ), $ret );
	}
}
