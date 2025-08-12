<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}
if ( 'uninstall.php' == basename( $_SERVER['SCRIPT_FILENAME'] ) )
	die ( '<h2>Direct File Access Prohibited</h2>' );
/**
 * The plugin uninstallation script
 * 1. Remove the tables
 * 2. Remove options
 * Done
 */

/**
 * Set Global variable
 * @var wpdb
 */
global $wpdb;

/** Remove databases */
$prefix = '';
if(is_multisite()) {
	$prefix = $wpdb->base_prefix;
	$blogs = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
	$old_blog = $wpdb->blogid;
	foreach($blogs as $blog) {
		switch_to_blog( $blog );
		$settings = get_blog_option( $blog, 'ipt_fsqm_settings', array() );
		$msprefix = $prefix . $blog . '_';


		if ( isset( $settings['delete_uninstall'] ) && $settings['delete_uninstall'] === true ) {
			if ( $wpdb->get_var( "show tables like '" . $msprefix . "fsq_form'" ) ) {
				//delete it
				$wpdb->query( "DROP TABLE IF EXISTS " . $msprefix . "fsq_form" );
			}

			if ($wpdb->get_var("show tables like '" . $msprefix . "fsq_data'")) {
				//delete it
				$wpdb->query("DROP TABLE IF EXISTS " . $msprefix . "fsq_data");
			}

			if ($wpdb->get_var("show tables like '" . $msprefix . "fsq_files'")) {
				//delete it
				$wpdb->query("DROP TABLE IF EXISTS " . $msprefix . "fsq_files");
			}

			delete_blog_option($blog, 'ipt_fsqm_info');
			delete_blog_option($blog, 'ipt_fsqm_settings');
			delete_blog_option($blog, 'ipt_fsqm_key');

			//Remove capabilities
			/**
			 * @global WP_Roles
			 */
			global $wp_roles;
			//remove capability to admin
			$wp_roles->remove_cap('administrator', 'manage_feedback');
			$wp_roles->remove_cap('administrator', 'view_feedback');

			//remove capability to editor
			$wp_roles->remove_cap('editor', 'manage_feedback');
			$wp_roles->remove_cap('editor', 'view_feedback');

			//remove capability to author
			$wp_roles->remove_cap('author', 'view_feedback');
		} else {
			continue;
		}
		switch_to_blog( $old_blog );
	}
} else {
	$prefix = $wpdb->prefix;
	$settings = get_option( 'ipt_fsqm_settings', array() );

	if ( isset( $settings['delete_uninstall'] ) && $settings['delete_uninstall'] === true ) {
		if ($wpdb->get_var("show tables like '" . $prefix . "fsq_form'")) {
			//delete it
			$wpdb->query("DROP TABLE IF EXISTS " . $prefix . "fsq_form");
		}

		if ($wpdb->get_var("show tables like '" . $prefix . "fsq_data'")) {
			//delete it
			$wpdb->query("DROP TABLE IF EXISTS " . $prefix . "fsq_data");
		}

		if ($wpdb->get_var("show tables like '" . $prefix . "fsq_files'")) {
			//delete it
			$wpdb->query("DROP TABLE IF EXISTS " . $prefix . "fsq_files");
		}
		// Delete options
		delete_option('ipt_fsqm_info');
		delete_option('ipt_fsqm_settings');
		delete_option('ipt_fsqm_key');

		//Remove capabilities
		/**
		 * @global WP_Roles
		 */
		global $wp_roles;
		//remove capability to admin
		$wp_roles->remove_cap('administrator', 'manage_feedback');
		$wp_roles->remove_cap('administrator', 'view_feedback');

		//remove capability to editor
		$wp_roles->remove_cap('editor', 'manage_feedback');
		$wp_roles->remove_cap('editor', 'view_feedback');

		//remove capability to author
		$wp_roles->remove_cap('author', 'view_feedback');
	}
}

