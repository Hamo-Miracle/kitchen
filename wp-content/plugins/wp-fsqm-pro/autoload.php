<?php
/**
 * Auto load eForm files
 *
 * This also includes the composer autoload So if you haven't done `composer
 * install` then this would definitely fail - which is the intended behavior!
 */
class IPT_EForm_Autoloader {
	public static function load_admin( $name ) {
		// Is it required
		if ( false === self::is_eform_class( $name ) ) {
			// Go ahead with other libraries
			return;
		}
		// Include if found
		self::include_class( 'admin', $name );
	}

	public static function load_core( $name ) {
		// Is it required
		if ( false === self::is_eform_class( $name ) ) {
			// Go ahead with other libraries
			return;
		}
		// Include if found
		self::include_class( 'core', $name );
	}

	public static function load_form( $name ) {
		// Is it required
		if ( false === self::is_eform_class( $name ) ) {
			// Go ahead with other libraries
			return;
		}
		// Include if found
		self::include_class( 'form', $name );
	}

	public static function load_helpers( $name ) {
		// Is it required
		if ( false === self::is_eform_class( $name ) ) {
			// Go ahead with other libraries
			return;
		}
		// Include if found
		self::include_class( 'helpers', $name );
	}

	public static function load_integrations( $name ) {
		// Is it required
		if ( false === self::is_eform_class( $name ) ) {
			// Go ahead with other libraries
			return;
		}
		// Include if found
		self::include_class( 'integrations', $name );
	}

	public static function load_listtables( $name ) {
		// Is it required
		if ( false === self::is_eform_class( $name ) ) {
			// Go ahead with other libraries
			return;
		}
		// Include if found
		if ( self::include_class( 'listtables', $name ) ) {
			// Also the WP_List_Table class
			if ( ! class_exists( 'WP_List_Table' ) ) {
				require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
			}
		}
	}

	public static function load_payments( $name ) {
		// Is it required
		if ( false === self::is_eform_class( $name ) ) {
			// Go ahead with other libraries
			return;
		}
		// Include if found
		self::include_class( 'payments', $name );
	}

	public static function load_shortcodes( $name ) {
		// Is it required
		if ( false === self::is_eform_class( $name ) ) {
			// Go ahead with other libraries
			return;
		}
		// Include if found
		self::include_class( 'shortcodes', $name );
	}

	public static function load_ui( $name ) {
		// Is it required
		if ( false === self::is_eform_class( $name ) ) {
			// Go ahead with other libraries
			return;
		}
		// Include if found
		self::include_class( 'ui', $name );
	}

	public static function load_utils( $name ) {
		// Is it required
		if ( false === self::is_eform_class( $name ) ) {
			// Go ahead with other libraries
			return;
		}
		// Include if found
		self::include_class( 'utils', $name );
	}

	public static function load_widgets( $name ) {
		// Is it required
		if ( false === self::is_eform_class( $name ) ) {
			// Go ahead with other libraries
			return;
		}
		// Include if found
		self::include_class( 'widgets', $name );
	}

	public static function load_elementor( $name ) {
		// Is it required
		if ( false === self::is_eform_class( $name ) ) {
			// Go ahead with other libraries
			return;
		}
		// Include if found
		self::include_class( 'elementor', $name );
	}

	/**
	 * Include a class based on the map
	 *
	 * @param      string   $path   The path inside includes
	 * @param      string   $name   Class name
	 *
	 * @return     boolean  true if file was found and included and false otherwise
	 */
	public static function include_class( $path, $name ) {
		// absolute path of the file in question
		$abspath = trailingslashit( IPT_EFORM_ABSPATH . 'includes/' . $path );
		// Calculated file name
		$filename = 'class-' . str_replace( '_', '-', strtolower( $name ) ) . '.php';
		$filepath = $abspath . $filename;
		// Now include if found
		if ( file_exists( $filepath ) ) {
			require_once $filepath;
			return true;
		}
		return false;
	}

	public static function is_eform_class( $name ) {
		$name = strtolower( $name );
		if ( false === strpos( $name, 'fsqm' ) && false === strpos( $name, 'eform' ) && false === strpos( $name, 'ipt' ) && false === strpos( $name, 'jsignature' ) ) {
			return false;
		}
		return true;
	}
}

// Now register the function
foreach ( array( 'admin', 'core', 'form', 'helpers', 'integrations', 'listtables', 'payments', 'shortcodes', 'ui', 'utils', 'widgets', 'elementor' ) as $inc ) {
	spl_autoload_register( 'IPT_EForm_Autoloader::load_' . $inc );
}

// And the composer as well
require_once IPT_EFORM_ABSPATH . 'vendor/autoload.php';
