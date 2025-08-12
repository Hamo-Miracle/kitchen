<?php
/**
 * IPT FSQM Loader
 * The library of loader class
 *
 * @author Swashata <swashata4u@gmail.com>
 * @package eForm - WordPress Form Builder
 * @subpackage Core\Loader
 * @codeCoverageIgnore
 */
class IPT_FSQM_Loader {
	/**
	 *
	 *
	 * @deprecated
	 * @var array stores the options
	 */
	public $op;

	/**
	 * The init classes used to generate the admin menu
	 * The class should initialize and hook itself
	 *
	 * @see /classes/admin-class.php and extend from the base abstract class
	 * @staticvar array
	 */
	static $init_classes = array();

	/**
	 *
	 *
	 * @staticvar string
	 * Holds the absolute path of the main plugin file directory
	 */
	static $abs_path;

	/**
	 *
	 *
	 * @staticvar string
	 * Holds the absolute path of the main plugin file
	 */
	static $abs_file;

	/**
	 * Holds the text domain
	 * Use the string directly instead
	 * But still set this for some methods, especially the loading of the textdomain
	 */
	static $text_domain;

	/**
	 *
	 *
	 * @staticvar string
	 * The current version of the plugin
	 */
	static $version;

	/**
	 *
	 *
	 * @staticvar string
	 * The abbreviated name of the plugin
	 * Mainly used for the enqueue style and script of the default admin.css and admin.js file
	 */
	static $abbr;

	/**
	 * The Documentation Link - From InTechgrity
	 *
	 * @var string
	 */
	static $documentation;

	/**
	 * The support forum link - From WordPress Extends
	 *
	 * @var string
	 */
	static $support_forum;

	/**
	 * URL to the bower_components directory From this we load libraries as-is
	 *
	 * @var        string $bower_components plugins_url to bower_components
	 * directory
	 */
	static $bower_components;
	/**
	 * URL to the bower_builds directory from where we load concatenated and
	 * compressed libraries
	 *
	 * @var        string  $bower_builds  plugins_url to bower_builds directory
	 */
	static $bower_builds;

	/**
	 * URL to the static directory from where we load eForm static CSS and JS
	 * files
	 *
	 * @var        string  $static_location  plugins_url to the static directory
	 */
	static $static_location;


	/**
	 * Constructor function
	 *
	 * @global array $ipt_fsqm_info The information option variable
	 * @param type    $file_loc
	 * @param type    $classes
	 * @param type    $text_domain
	 * @param type    $version
	 * @param type    $abbr
	 */
	public function __construct( $file_loc, $text_domain = 'default', $version = '1.0.0', $abbr = '', $doc = '', $sup = '' ) {
		self::$abs_path = dirname( $file_loc );
		self::$abs_file = $file_loc;
		self::$text_domain = $text_domain;
		self::$version = $version;
		self::$abbr = $abbr;
		self::$init_classes = array( 'IPT_FSQM_Dashboard', 'IPT_FSQM_All_Forms', 'IPT_FSQM_New_Form', 'IPT_FSQM_Import_Export', 'IPT_FSQM_Form_Category', 'IPT_FSQM_Report', 'IPT_FSQM_View_Submission', 'IPT_FSQM_View_All_Submissions', 'IPT_FSQM_Payments', 'IPT_FSQM_Settings', 'IPT_FSQM_About' );
		self::$documentation = $doc;
		self::$support_forum = $sup;
		self::$bower_components = trailingslashit( plugins_url( 'bower_components', self::$abs_file ) );
		self::$bower_builds = trailingslashit( plugins_url( 'bower_builds', self::$abs_file ) );
		self::$static_location = trailingslashit( plugins_url( 'static', self::$abs_file ) );
		global $ipt_fsqm_info, $ipt_fsqm_settings, $ipt_eform_wc;
		$ipt_fsqm_info = get_option( 'ipt_fsqm_info' );
		$ipt_fsqm_settings = get_option( 'ipt_fsqm_settings' );
		$ipt_eform_wc = null;
	}

	public function load() {
		global $ipt_eform_wc;
		// Populate db info
		self::populate_db_info();

		// activation hook
		register_activation_hook( self::$abs_file, array( $this, 'plugin_install' ) );
		// deactivation hook
		register_deactivation_hook( self::$abs_file, array( $this, 'plugin_deactivate' ) );
		// Load Text Domain For Translations //
		add_action( 'plugins_loaded', array( $this, 'plugin_textdomain' ) );
		// Check for version and database compatibility //
		add_action( 'plugins_loaded', array( $this, 'database_version' ) );
		// Check for initial redirect
		add_action( 'init', array( $this, 'initial_redirect' ) );

		// admin area
		if ( is_admin() ) {
			IPT_FSQM_Form_Elements_Static::admin_init();
			// admin menu items
			add_action( 'plugins_loaded', array( $this, 'init_admin_menus' ), 20 );
			add_action( 'admin_init', array( $this, 'gen_admin_menu' ), 20 );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_menu_style' ) );
			// eForm shortcode builder for tinyMCE
			IPT_EForm_Shortcodes_TinyMCE::init();
		} else {
			// add frontend script + style
			add_action( 'wp_print_styles', array( $this, 'enqueue_script_style' ) );

			// add the footer script for popup forms
			add_action( 'wp_footer', array( $this, 'popup_forms' ), 8 );
		}

		// init the shortcodes
		IPT_EForm_Core_Shortcodes::init();
		IPT_EForm_Stat_Shortcodes::init();
		IPT_EForm_LeaderBoard::init();

		// Some basic init coming from the static class. We will eventually
		// remove them and add as different functionality class
		IPT_FSQM_Form_Elements_Static::common_init();

		// Check for WooCommerce
		if ( function_exists( 'WC' ) ) {
			$ipt_eform_wc = IPT_eForm_WooCommerce::instant();
		}

		// Include our widgets
		IPT_FSQM_Form_Widget::init();
		IPT_FSQM_Popup_Widget::init();
		IPT_FSQM_Trends_Widget::init();

		// If gutenberg is present, then load it
		if ( function_exists( 'register_block_type' ) ) {
			\EFormV4\Core\Bootstrap::init();
		}

		// Initialize elementor
		IPT_EForm_Elementor_Init::instance();
	}

	public function init_admin_menus() {
		self::$init_classes = apply_filters( 'ipt_fsqm_admin_menus', self::$init_classes );
		foreach ( (array) self::$init_classes as $class ) {
			if ( class_exists( $class ) ) {
				global ${'admin_menu' . $class};
				${'admin_menu' . $class} = new $class();
			}
		}
	}


	public function gen_admin_menu() {
		$admin_menus = array();
		foreach ( (array) self::$init_classes as $class ) {
			if ( class_exists( $class ) ) {
				global ${'admin_menu' . $class};
				$admin_menus[] = ${'admin_menu' . $class}->get_pagehook();
			}
		}

		foreach ( $admin_menus as $menu ) {
			add_action( 'admin_print_styles-' . $menu, array( $this, 'admin_enqueue_script_style' ) );
		}

	}

	public function admin_menu_style() {
		global $pagenow;
		wp_enqueue_style( 'ipt_fsqm_font', self::$static_location . 'fonts/fsqm-icons/fsqm-icons.css', array(), self::$version );
		// Include widgets CSS and JS

		if ( 'widgets.php' == $pagenow || 'customize.php' == $pagenow ) {
			wp_enqueue_script( 'ipt_fsqm_widget_js', self::$static_location . 'admin/js/widget.min.js', array( 'jquery' ), self::$version );
			wp_enqueue_style( 'ipt_fsqm_widget_css', self::$static_location . 'admin/css/widget.css', array(), self::$version );
		}
	}

	public function admin_enqueue_script_style() {
		$ui = IPT_Plugin_UIF_Admin::instance();
		$ui->enqueue();
		wp_enqueue_style( 'ipt_fsqm_ui', self::$static_location . 'admin/css/ipt-fsqm-ui.css', array(), self::$version );
		wp_enqueue_script( 'ipt_fsqm_admin_js', self::$static_location . 'admin/js/ipt-fsqm-admin.min.js', array( 'jquery' ), self::$version );
	}

	public function enqueue_script_style() {
		/* Everything is handled by the shortcode or the form class */
	}

	public function popup_forms() {
		EForm_Popup_Helper::print_popup_modals();
	}

	public function plugin_install( $networkwide = false ) {
		$install = new IPT_FSQM_Install();
		$install->install( $networkwide );
		self::populate_db_info();
	}

	public function plugin_deactivate() {
		flush_rewrite_rules();
		delete_option( 'ipt_fsqm_initial_page' );
	}

	public function database_version() {
		global $ipt_fsqm_info;
		$s_version = self::$version;

		if ( ! isset( $ipt_fsqm_info['version'] ) || version_compare( $ipt_fsqm_info['version'], $s_version, '<' ) ) {
			$install = new IPT_FSQM_Install();
			$install->upgrade();
			update_option( 'ipt_fsqm_initial_page', false );
			self::populate_db_info();
		}
	}

	public function initial_redirect() {
		if ( ! current_user_can( 'manage_options' ) || ! is_admin() ) {
			return;
		}
		if ( ! get_option( 'ipt_fsqm_initial_page', false ) ) {
			// We flush rewrite here too
			update_option( 'ipt_fsqm_flush_rewrite', true );
			update_option( 'ipt_fsqm_initial_page', true );
			wp_safe_redirect( admin_url( 'admin.php?page=ipt_fsqm_about#!fsqm-whats-new' ) );
			exit;
		}
	}

	/**
	 * Load the text domain on plugin load
	 * Hooked to the plugins_loaded via the load method
	 *
	 * dirname( plugin_basename( self::$abs_file ) )
	 */
	public function plugin_textdomain() {
		load_plugin_textdomain( 'ipt_fsqm', false, basename( dirname( self::$abs_file ) ) . '/translations' );
	}

	public static function populate_db_info() {
		global $wpdb, $ipt_fsqm_info;
		if ( ! is_array( $ipt_fsqm_info ) ) {
			$ipt_fsqm_info = [];
		}
		// Here we override the database table name variable stored
		// This will make the things dynamic and easier for site copying
		$prefix = '';
		if ( is_multisite() ) {
			global $blog_id;
			$prefix = $wpdb->base_prefix . $blog_id . '_';
		} else {
			$prefix = $wpdb->prefix;
		}
		$ipt_fsqm_info['form_table'] = $prefix . 'fsq_form';
		$ipt_fsqm_info['data_table'] = $prefix . 'fsq_data';
		$ipt_fsqm_info['file_table'] = $prefix . 'fsq_files';
		$ipt_fsqm_info['category_table'] = $prefix . 'fsq_category';
		$ipt_fsqm_info['payment_table'] = $prefix . 'fsq_payment';
	}
}
