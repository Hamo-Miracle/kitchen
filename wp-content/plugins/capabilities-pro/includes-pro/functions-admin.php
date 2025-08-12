<?php
/*
 * PublishPress Capabilities Pro
 *
 * Pro admin functions with broad scope, which are not contained within a class
 */

add_filter('pp_capabilities_backup_sections', 'pp_capabilities_pro_backup_sections');
add_action('pp_capabilities_after_role_copied', 'pp_capabilities_pro_after_role_copied', 10, 2);

require_once (dirname(__FILE__) . '/features/custom.php');
\PublishPress\Capabilities\EditorFeaturesCustom::instance();

require_once (dirname(__FILE__) . '/features/metaboxes.php');
\PublishPress\Capabilities\EditorFeaturesMetaboxes::instance();

//admin features css hide integration
require_once (dirname(__FILE__) . '/features/admin-features-css-hide.php');
\PublishPress\Capabilities\AdminFeaturesCssHide::instance();

//admin features block url integration
require_once (dirname(__FILE__) . '/features/admin-features-block-url.php');
\PublishPress\Capabilities\AdminFeaturesBlockUrl::instance();


function pp_capabilities_pro_backup_sections($backup_sections){

    //Admin Features Pro
    $backup_sections['capsman_admin_features_backup']['options'][] = 'ppc_admin_feature_block_url_custom_data';
    $backup_sections['capsman_admin_features_backup']['options'][] = 'ppc_admin_feature_css_hide_custom_data';

    //Editor Features Pro
    $backup_sections['capsman_editor_features_backup']['options'][] = 'ppc_feature_post_gutenberg_custom_data';
    $backup_sections['capsman_editor_features_backup']['options'][] = 'ppc_feature_post_classic_custom_data';

    //Admin Menu
    $admin_menu_options = ['capsman_admin_menus', 'capsman_admin_child_menus', 'ppc_admin_menus_order', 'ppc_admin_menus_names', 'ppc_admin_menus_custom_menus', 'ppc_admin_menus_settings'];
    $backup_sections['capsman_admin_menu_backup']['label'] = esc_html__('Admin Menu', 'capabilities-pro');
    foreach($admin_menu_options as $admin_menu_option){
        $backup_sections['capsman_admin_menu_backup']['options'][] = $admin_menu_option;
    }
    

    return $backup_sections;
}

function pp_capabilities_pro_after_role_copied($role_slug, $copied_role) {

    //Copy Admin Menu
    $admin_menu_option = !empty(get_option('capsman_admin_menus')) ? get_option('capsman_admin_menus') : [];
    if (is_array($admin_menu_option) && array_key_exists($copied_role, $admin_menu_option)) {
        $admin_menu_option[$role_slug] = $admin_menu_option[$copied_role];
        update_option('capsman_admin_menus', $admin_menu_option, false);
    }
    $admin_child_menu_option = !empty(get_option('capsman_admin_child_menus')) ? get_option('capsman_admin_child_menus') : [];
    if (is_array($admin_child_menu_option) && array_key_exists($copied_role, $admin_child_menu_option)) {
        $admin_child_menu_option[$role_slug] = $admin_child_menu_option[$copied_role];
        update_option('capsman_admin_child_menus', $admin_child_menu_option, false);
    }
    $admin_menus_names_option = !empty(get_option('ppc_admin_menus_names')) ? get_option('ppc_admin_menus_names') : [];
    if (is_array($admin_menus_names_option) && array_key_exists($copied_role, $admin_menus_names_option)) {
        $admin_menus_names_option[$role_slug] = $admin_menus_names_option[$copied_role];
        update_option('ppc_admin_menus_names', $admin_menus_names_option, false);
    }
}

function pp_capabilities_admin_menu_access($slug)
{
    $url = (isset($_SERVER['REQUEST_URI'])) ? esc_url_raw($_SERVER['REQUEST_URI']) : '';
    $url = basename($url);
    $url = htmlspecialchars($url);

    if (is_multisite() && is_super_admin() && !defined('PP_CAPABILITIES_RESTRICT_SUPER_ADMIN')) {
        return true;
    }

    if (!isset($url)) {
        return false;
    }

    $uri = wp_parse_url($url);

    if (!isset($uri['path'])) {
        return false;
    }

    if (!isset($uri['query']) && strpos($uri['path'], $slug) !== false) {
        add_action('load-' . $slug, 'pp_capabilities_admin_menu_access_denied');
        return true;
    }

    if ($slug === $url || ($uri['path'] === 'customize.php' && strpos($slug, $uri['path']) !== false)) {
        add_action('load-' . basename($uri['path']), 'pp_capabilities_admin_menu_access_denied');
        return true;
    }

    if ($url == "admin.php?page=$slug") {
        pp_capabilities_admin_menu_access_denied();
    }
}

function pp_capabilities_admin_menu_access_denied()
{
    $forbidden = esc_attr__('You do not have permission to access this page.', 'capabilities-pro');
    wp_die(esc_html($forbidden));
}

function ppc_process_admin_menu_title($title)
{
    if (!empty($title)) {
        // Match and replace nested HTML tags and their entire content
        $title = preg_replace('/<[^>]+>.*?<\/[^>]+>/s', '', $title);
        $title = preg_replace('/<[^>]+>/s', '', $title); // Remove orphan tags if any
    }

    return $title;
}

/**
 * Clone copy of WordPress add_menu_page with support for capabilities separator and class
 * 
 * Adds a top-level menu page.
 *
 * This function takes a capability which will be used to determine whether
 * or not a page is included in the menu.
 *
 * The function which is hooked in to handle the output of the page must check
 * that the user has the required capability as well.
 *
 * @since 1.5.0
 *
 * @global array $menu
 * @global array $admin_page_hooks
 * @global array $_registered_pages
 * @global array $_parent_pages
 *
 * @param string    $page_title The text to be displayed in the title tags of the page when the menu is selected.
 * @param string    $menu_title The text to be used for the menu.
 * @param string    $capability The capability required for this menu to be displayed to the user.
 * @param string    $menu_slug  The slug name to refer to this menu by. Should be unique for this menu page and only
 *                              include lowercase alphanumeric, dashes, and underscores characters to be compatible
 *                              with sanitize_key().
 * @param callable  $callback   Optional. The function to be called to output the content for this page.
 * @param string    $icon_url   Optional. The URL to the icon to be used for this menu.
 *                              * Pass a base64-encoded SVG using a data URI, which will be colored to match
 *                                the color scheme. This should begin with 'data:image/svg+xml;base64,'.
 *                              * Pass the name of a Dashicons helper class to use a font icon,
 *                                e.g. 'dashicons-chart-pie'.
 *                              * Pass 'none' to leave div.wp-menu-image empty so an icon can be added via CSS.
 * @param int|float $position   Optional. The position in the menu order this item should appear.
 * @param array $menu_data original menu data from form (Added by Capabilities)
 * @return string The resulting page's hook_suffix.
 */
function ppc_add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $callback = '', $icon_url = '', $position = null, $menu_data = [] ) {
	global $menu, $admin_page_hooks, $_registered_pages, $_parent_pages;

	$menu_slug = plugin_basename( $menu_slug );

	$admin_page_hooks[ $menu_slug ] = sanitize_title( $menu_title );

	$hookname = get_plugin_page_hookname( $menu_slug, '' );

	if ( ! empty( $callback ) && ! empty( $hookname ) && current_user_can( $capability ) ) {
		add_action( $hookname, $callback );
	}

	if ( empty( $icon_url ) ) {
		$icon_url   = 'dashicons-admin-generic';
		$icon_class = 'menu-icon-generic ';
	} else {
		$icon_url   = set_url_scheme( $icon_url );
		$icon_class = '';
	}

    if (!empty($menu_data['form_menu_type']) && $menu_data['form_menu_type'] == 'menu-separator') {
	    $new_menu = array( '', $capability, $menu_slug, '', $menu_data['menu_data'][4], $icon_url );
    } else {
	    $new_menu = array( $menu_title, $capability, $menu_slug, $page_title, 'menu-top ' . $icon_class . $hookname, $hookname, $icon_url );
    }

	if ( null !== $position && ! is_numeric( $position ) ) {
		_doing_it_wrong(
			__FUNCTION__,
			sprintf(
				/* translators: %s: add_menu_page() */
				__( 'The seventh parameter passed to %s should be numeric representing menu position.' ),
				'<code>add_menu_page()</code>'
			),
			'6.0.0'
		);
		$position = null;
	}

	if ( null === $position || ! is_numeric( $position ) ) {
		$menu[] = $new_menu;
	} elseif ( isset( $menu[ (string) $position ] ) ) {
		$collision_avoider = base_convert( substr( md5( $menu_slug . $menu_title ), -4 ), 16, 10 ) * 0.00001;
		$position          = (string) ( $position + $collision_avoider );
		$menu[ $position ] = $new_menu;
	} else {
		/*
		 * Cast menu position to a string.
		 *
		 * This allows for floats to be passed as the position. PHP will normally cast a float to an
		 * integer value, this ensures the float retains its mantissa (positive fractional part).
		 *
		 * A string containing an integer value, eg "10", is treated as a numeric index.
		 */
		$position          = (string) $position;
		$menu[ $position ] = $new_menu;
	}

	$_registered_pages[ $hookname ] = true;

	// No parent as top level.
	$_parent_pages[ $menu_slug ] = false;

	return $hookname;
}

/**
 * Clone copy of WordPress add_submenu_page with support for capabilities separator and class
 * 
 * Adds a submenu page.
 *
 * This function takes a capability which will be used to determine whether
 * or not a page is included in the menu.
 *
 * The function which is hooked in to handle the output of the page must check
 * that the user has the required capability as well.
 *
 * @since 1.5.0
 * @since 5.3.0 Added the `$position` parameter.
 *
 * @global array $submenu
 * @global array $menu
 * @global array $_wp_real_parent_file
 * @global bool  $_wp_submenu_nopriv
 * @global array $_registered_pages
 * @global array $_parent_pages
 *
 * @param string    $parent_slug The slug name for the parent menu (or the file name of a standard
 *                               WordPress admin page).
 * @param string    $page_title  The text to be displayed in the title tags of the page when the menu
 *                               is selected.
 * @param string    $menu_title  The text to be used for the menu.
 * @param string    $capability  The capability required for this menu to be displayed to the user.
 * @param string    $menu_slug   The slug name to refer to this menu by. Should be unique for this menu
 *                               and only include lowercase alphanumeric, dashes, and underscores characters
 *                               to be compatible with sanitize_key().
 * @param callable  $callback    Optional. The function to be called to output the content for this page.
 * @param int|float $position    Optional. The position in the menu order this item should appear.
 * @param array $submenu_data original sub menu data from form (Added by Capabilities)
 * @return string|false The resulting page's hook_suffix, or false if the user does not have the capability required.
 */
function ppc_add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback = '', $position = null, $submenu_data = [] ) {
	global $submenu, $menu, $_wp_real_parent_file, $_wp_submenu_nopriv,
		$_registered_pages, $_parent_pages;

	$menu_slug   = plugin_basename( $menu_slug );
	$parent_slug = plugin_basename( $parent_slug );

	if ( isset( $_wp_real_parent_file[ $parent_slug ] ) ) {
		$parent_slug = $_wp_real_parent_file[ $parent_slug ];
	}

	if ( ! current_user_can( $capability ) ) {
		$_wp_submenu_nopriv[ $parent_slug ][ $menu_slug ] = true;
		return false;
	}

	/*
	 * If the parent doesn't already have a submenu, add a link to the parent
	 * as the first item in the submenu. If the submenu file is the same as the
	 * parent file someone is trying to link back to the parent manually. In
	 * this case, don't automatically add a link back to avoid duplication.
	 */
	if ( ! isset( $submenu[ $parent_slug ] ) && $menu_slug !== $parent_slug ) {
		foreach ( (array) $menu as $parent_menu ) {
			if ( $parent_menu[2] === $parent_slug && current_user_can( $parent_menu[1] ) ) {
				$submenu[ $parent_slug ][] = array_slice( $parent_menu, 0, 4 );
			}
		}
	}


    if (!empty($submenu_data['form_menu_type']) && $submenu_data['form_menu_type'] == 'submenu-separator') {
	    $new_sub_menu = array( '', $capability, $menu_slug, '', $submenu_data['menu_data'][4] );
    } else {
	    $new_sub_menu = array( $menu_title, $capability, $menu_slug, $page_title );
    }

	if ( null !== $position && ! is_numeric( $position ) ) {
		_doing_it_wrong(
			__FUNCTION__,
			sprintf(
				/* translators: %s: add_submenu_page() */
				__( 'The seventh parameter passed to %s should be numeric representing menu position.' ),
				'<code>add_submenu_page()</code>'
			),
			'5.3.0'
		);
		$position = null;
	}

	if (
		null === $position ||
		( ! isset( $submenu[ $parent_slug ] ) || $position >= count( $submenu[ $parent_slug ] ) )
	) {
		$submenu[ $parent_slug ][] = $new_sub_menu;
	} else {
		// Test for a negative position.
		$position = max( $position, 0 );
		if ( 0 === $position ) {
			// For negative or `0` positions, prepend the submenu.
			array_unshift( $submenu[ $parent_slug ], $new_sub_menu );
		} else {
			$position = absint( $position );
			// Grab all of the items before the insertion point.
			$before_items = array_slice( $submenu[ $parent_slug ], 0, $position, true );
			// Grab all of the items after the insertion point.
			$after_items = array_slice( $submenu[ $parent_slug ], $position, null, true );
			// Add the new item.
			$before_items[] = $new_sub_menu;
			// Merge the items.
			$submenu[ $parent_slug ] = array_merge( $before_items, $after_items );
		}
	}

	// Sort the parent array.
	ksort( $submenu[ $parent_slug ] );

	$hookname = get_plugin_page_hookname( $menu_slug, $parent_slug );
	if ( ! empty( $callback ) && ! empty( $hookname ) ) {
		add_action( $hookname, $callback );
	}

	$_registered_pages[ $hookname ] = true;

	/*
	 * Backward-compatibility for plugins using add_management_page().
	 * See wp-admin/admin.php for redirect from edit.php to tools.php.
	 */
	if ( 'tools.php' === $parent_slug ) {
		$_registered_pages[ get_plugin_page_hookname( $menu_slug, 'edit.php' ) ] = true;
	}

	// No parent as top level.
	$_parent_pages[ $menu_slug ] = $parent_slug;

	return $hookname;
}

function pp_capabilities_admin_menu_path($menu_slug) {
    // Check if the menu slug is a full URL (starts with http or https)
    if (preg_match('/^https?:\/\//', $menu_slug)) {
        return $menu_slug;
    }

    // Check if the menu slug contains `.php`
    if (strpos($menu_slug, '.php') !== false) {
        return $menu_slug;
    }

    // For other cases, prepend with `admin.php?page=`
    return 'admin.php?page=' . $menu_slug;
}