<?php
/**
 * eForm Install
 *
 * The library of all the installation class
 *
 * @author Swashata <swashata4u@gmail.com>
 * @package eForm - WordPress Form Builder
 * @subpackage Core\Installation
 * @codeCoverageIgnore
 */
class IPT_FSQM_Install {

	/**
	 * Database prefix
	 * Mainly used for MS compatibility
	 *
	 * @var string
	 */
	public $prefix;

	public function __construct() {
		global $wpdb;
		$prefix = '';
		if ( is_multisite() ) {
			global $blog_id;
			$prefix = $wpdb->base_prefix . $blog_id . '_';
		} else {
			$prefix = $wpdb->prefix;
		}

		$this->prefix = $prefix;
	}

	/**
	 * install
	 * Do the things
	 */
	public function install( $networkwide = false ) {
		if ( $networkwide ) {
			deactivate_plugins( plugin_basename( IPT_FSQM_Loader::$abs_file ) );
			wp_die( __( 'The plugin can not be network activated.', 'ipt_fsqm' ) );
		}
		$this->checkversions();
		$this->checkdb();
		$this->checkop();
		$this->set_capability();
	}

	/**
	 * Run an upgrade function
	 *
	 * It is recommended to use this isntead of calling checkdb and checkop separately
	 */
	public function upgrade() {
		$this->checkdb();
		$this->checkop();
	}

	/**
	 * Restores the WP Options to the defaults
	 * Deletes the default options set and calls checkop
	 */
	public function restore_op() {
		delete_option( 'ipt_fsqm_info' );
		delete_option( 'ipt_fsqm_settings' );
		delete_option( 'ipt_fsqm_key' );

		$this->checkop();
	}

	/**
	 * Restores the database
	 * Deletes the current tables and freshly installs the new one
	 *
	 * @global wpdb $wpdb
	 */
	public function restore_db() {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS {$this->prefix}fsq_form" );
		$wpdb->query( "DROP TABLE IF EXISTS {$this->prefix}fsq_data" );
		$this->checkdb();
	}

	/**
	 * Checks whether PHP version 5 or greater is installed or not
	 * Also checks whether WordPress version is greater than or equal to the required
	 *
	 * If fails then it automatically deactivates the plugin
	 * and gives error
	 *
	 * @return void
	 */
	private function checkversions() {
		if ( version_compare( PHP_VERSION, '5.0.0', '<' ) ) {
			deactivate_plugins( plugin_basename( IPT_FSQM_Loader::$abs_file ) );
			wp_die( __( 'The plugin requires PHP version greater than or equal to 5.x.x', 'ipt_fsqm' ) );
			return;
		}

		if ( version_compare( get_bloginfo( 'version' ), '3.5', '<' ) ) {
			deactivate_plugins( plugin_basename( IPT_FSQM_Loader::$abs_file ) );
			wp_die( __( 'The plugin requires WordPress version greater than or equal to 3.5', 'ipt_fsqm' ) );
			return;
		}
	}

	/**
	 * creates the table and options
	 *
	 * @access public
	 * @global string $charset_collate
	 */
	public function checkdb() {
		/**
		 * Include the necessary files
		 * Also the global options
		 */
		if ( file_exists( ABSPATH . 'wp-admin/includes/upgrade.php' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		} else {
			require_once ABSPATH . 'wp-admin/upgrade-functions.php';
		}
		global $charset_collate;


		$prefix = $this->prefix;
		$sqls = array();

		$sqls[] = "CREATE TABLE {$prefix}fsq_form (
			id BIGINT(20) UNSIGNED NOT NULL auto_increment,
			name VARCHAR(255) NOT NULL default '',
			settings LONGTEXT NOT NULL,
			layout LONGTEXT NOT NULL,
			design LONGTEXT NOT NULL,
			mcq LONGTEXT NOT NULL,
			freetype LONGTEXT NOT NULL,
			pinfo LONGTEXT NOT NULL,
			type TINYINT(1) NOT NULL default 1,
			updated DATETIME NOT NULL default '0000-00-00 00:00:00',
			category BIGINT(20) UNSIGNED NOT NULL default 0,
			PRIMARY KEY  (id)
		) $charset_collate;";

		$sqls[] = "CREATE TABLE {$prefix}fsq_data (
			id BIGINT(20) UNSIGNED NOT NULL auto_increment,
			form_id BIGINT(20) UNSIGNED NOT NULL default 0,
			f_name VARCHAR(255) NOT NULL default '',
			l_name VARCHAR(255) NOT NULL default '',
			email VARCHAR(255) NOT NULL default '',
			phone VARCHAR(20) NOT NULL default '',
			mcq LONGTEXT NOT NULL,
			freetype LONGTEXT NOT NULL,
			pinfo LONGTEXT NOT NULL,
			ip VARCHAR(50) NOT NULL default '0.0.0.0',
			star TINYINT(1) NOT NULL default 0,
			score DECIMAL(12, 2) NOT NULL default 0,
			max_score DECIMAL(12, 2) NOT NULL default 0,
			url_track VARCHAR(255) NOT NULL default '',
			date DATETIME NOT NULL default '0000-00-00 00:00:00',
			comment LONGTEXT NOT NULL,
			user_id BIGINT(20) UNSIGNED NOT NULL default 0,
			referer TEXT NOT NULL,
			paid TINYINT(1) NOT NULL default 0,
			time INT(11) UNSIGNED NOT NULL default 0,
			token VARCHAR(50) default NULL,
			epoc DATETIME NOT NULL default '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			UNIQUE KEY token (token)
		) $charset_collate;";

		$sqls[] = "CREATE TABLE {$prefix}fsq_files (
			id BIGINT(20) UNSIGNED NOT NULL auto_increment,
			form_id BIGINT(20) UNSIGNED NOT NULL default 0,
			data_id BIGINT(20) UNSIGNED NOT NULL default 0,
			element_id BIGINT(20) UNSIGNED NOT NULL default 0,
			media_id BIGINT(20) UNSIGNED NOT NULL default 0,
			name VARCHAR(255) NOT NULL default '',
			filename VARCHAR(255) NOT NULL default '',
			mime_type VARCHAR(255) NOT NULL default '',
			size BIGINT(20) UNSIGNED NOT NULL default 0,
			guid VARCHAR(255) NOT NULL default '',
			path VARCHAR(255) NOT NULL default '',
			thumb_url VARCHAR(255) NOT NULL default '',
			date DATETIME NOT NULL default '0000-00-00 00:00:00',
			PRIMARY KEY  (id)
		) $charset_collate;";

		$sqls[] = "CREATE TABLE {$prefix}fsq_category (
			id BIGINT(20) UNSIGNED NOT NULL auto_increment,
			name VARCHAR(255) NOT NULL default '',
			description LONGTEXT NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		// type 0 => onetime, 1 => recurring
		// rinterval 0 => none, 1 => day, 2 => week, 3 => month, 4 => year
		// rfrequency => interval frequency
		// status:
		// ** 0 => Unpaid
		// ** 1 => Paid
		// ** 2 => Cancelled
		// ** 3 => Unsuccessful
		// ** 4 => Awaiting
		$sqls[] = "CREATE TABLE {$prefix}fsq_payment (
			id BIGINT(20) NOT NULL auto_increment,
			txn VARCHAR(30),
			form_id BIGINT(20) UNSIGNED NOT NULL default 0,
			data_id BIGINT(20) UNSIGNED NOT NULL default 0,
			user_id BIGINT(20) UNSIGNED NOT NULL default 0,
			amount decimal(12, 2),
			mode VARCHAR(255) NOT NULL,
			status TINYINT(1) NOT NULL default 0,
			meta LONGTEXT NOT NULL,
			currency VARCHAR(10) NOT NULL default 'USD',
			date DATETIME NOT NULL default '0000-00-00 00:00:00',
			type TINYINT(1) UNSIGNED NOT NULL default 0,
			rplan VARCHAR(255) NOT NULL default '',
			rinterval TINYINT(1) UNSIGNED NOT NULL default 0,
			rfrequency SMALLINT UNSIGNED NOT NULL default 0,
			PRIMARY KEY  (id),
			UNIQUE KEY txn (txn),
			UNIQUE KEY data_id (data_id)
		) $charset_collate";

		foreach ( $sqls as $sql ) {
			dbDelta( $sql );
		}
	}

	/**
	 * Creates the options
	 */
	public function checkop() {
		$prefix = $this->prefix;
		$ipt_fsqm_info = array(
			'version' => IPT_FSQM_Loader::$version,
			'form_table' => $prefix . 'fsq_form',
			'data_table' => $prefix . 'fsq_data',
			'file_table' => $prefix . 'fsq_files',
			'category_table' => $prefix . 'fsq_category',
			'payment_table' => $prefix . 'fsq_payment',
		);

		$ipt_fsqm_settings = array(
			'email' => get_option( 'admin_email' ),
			'track_page' => '0',
			'utrack_page' => '0',
			'delete_uninstall' => false,
			'standalone' => array(
				'base' => 'eforms',
				'before' => '',
				'after' => '',
				'head' => '',
			),
			'disable_un' => false,
			'gplaces_api' => '',
			'purchase_code' => '',
			'sandboxing' => true,
		);

		$ipt_fsqm_key = sha1( uniqid() );
		$old_op = null;

		if ( !get_option( 'ipt_fsqm_info' ) ) {
			//Check for update from v < 2.0.0
			$possible_old_info = get_option( 'wp_feedback_info' );
			if ( $possible_old_info !== false && is_array( $possible_old_info ) && isset( $possible_old_info['data_table'] ) ) {
				//update
				$ipt_fsqm_settings = wp_parse_args( get_option( 'wp_feedback_settings' ), $ipt_fsqm_settings );
				$ipt_fsqm_settings['backward_shortcode'] = true;
				add_option( 'ipt_fsqm_settings', $ipt_fsqm_settings );
				add_option( 'ipt_fsqm_key', get_option( 'wp_feedback_key' ) );

				//delete all persistent cache
				$this->delete_transient( $ipt_fsqm_info, true );
			} else {
				//new installation
				add_option( 'ipt_fsqm_settings', $ipt_fsqm_settings );
				add_option( 'ipt_fsqm_key', $ipt_fsqm_key );
			}
			add_option( 'ipt_fsqm_info', $ipt_fsqm_info );
			$this->populate_data( $ipt_fsqm_info );
		} else {
			$old_op = get_option( 'ipt_fsqm_info' );

			switch ( $old_op['version'] ) {
				default :
					break;
				case '1.0.0' :
				case '1.0.1' :
				case '1.0.2' :
				case '1.0.3' :
				case '2.1.0' :
				case '2.1.1' :
				case '2.1.2' :
				case '2.1.3' :
				case '2.1.4' :
				case '2.1.5' :
				case '2.2.2' :
				case '2.2.4' :
				case '2.2.5' :
				case '2.2.6' :
				case '2.2.7' :
				case '2.2.8' :
				case '2.4.0' :
				case '2.6.3' :
					//nothing needed
					break;
			}

			//Merge the settings
			$ipt_fsqm_settings = wp_parse_args( get_option( 'ipt_fsqm_settings' ), $ipt_fsqm_settings );
			update_option( 'ipt_fsqm_settings', $ipt_fsqm_settings );

			//finally update the info option with the newer version
			update_option( 'ipt_fsqm_info', $ipt_fsqm_info );
		}
		// Flush the rewrite rules for one time
		// It is managed through IPT_FSQM_Form_Elements_Static::standalone_rewrite()
		// which is hooked to init
		update_option( 'ipt_fsqm_flush_rewrite', true );

		// Repopulate global variables
		global $ipt_fsqm_info, $ipt_fsqm_settings;
		$ipt_fsqm_info = get_option( 'ipt_fsqm_info' );
		$ipt_fsqm_settings = get_option( 'ipt_fsqm_settings' );

		// DB Update needed for older eForm than 3.4
		if ( ! is_null( $old_op ) && version_compare( $old_op['version'], '3.4.0' ) === -1 ) {
			global $wpdb;
			// Change the payment status
			$wpdb->query( "UPDATE {$ipt_fsqm_info['data_table']} SET paid = 1 WHERE id IN ( SELECT data_id FROM {$ipt_fsqm_info['payment_table']} WHERE status = 1 )" );
		}
	}

	private function delete_transient( $ipt_fsqm_info, $from_old = false ) {
		//delete all persistent cache
		global $wpdb;
		$all_forms = $wpdb->get_col( "SELECT id FROM {$ipt_fsqm_info['form_table']}" );
		if ( !empty( $all_forms ) && $from_old ) {
			foreach ( $all_forms as $form ) {
				//Old transient cache, if any
				delete_transient( 'wp_feedback_data_t_' . $form );
			}
		}
	}

	/**
	 * Create and set custom capabilities
	 *
	 * @global WP_Roles $wp_roles
	 */
	private function set_capability() {
		global $wp_roles;

		//add capability to admin
		$wp_roles->add_cap( 'administrator', 'manage_feedback' );
		$wp_roles->add_cap( 'administrator', 'view_feedback' );

		//add capability to editor
		$wp_roles->add_cap( 'editor', 'manage_feedback' );
		$wp_roles->add_cap( 'editor', 'view_feedback' );

		//add capability to author
		$wp_roles->add_cap( 'author', 'view_feedback' );
	}

	public function create_sample_forms() {
		global $ipt_fsqm_info;
		$this->populate_data( $ipt_fsqm_info );
	}

	private function populate_data( $ipt_fsqm_info ) {
		global $wpdb;
		include IPT_EFORM_ABSPATH . 'includes/utils/var-wp-fsqm-form.php';
		if ( !isset( $wp_fsq_form ) || !is_array( $wp_fsq_form ) || empty( $wp_fsq_form ) ) {
			return;
		}
		foreach ( $wp_fsq_form as $row ) {
			if ( isset( $row['id'] ) ) {
				unset( $row['id'] );
			}
			$wpdb->insert( $ipt_fsqm_info['form_table'], $row, '%s' );
		}
	}
}
