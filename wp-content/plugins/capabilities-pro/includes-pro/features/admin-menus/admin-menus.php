<?php
/**
 * Capability Manager Admin Menu Permissions.
 * Hide and block selected Admin Menus per-role.
 *
 *    Copyright 2020, PublishPress <help@publishpress.com>
 *
 *    This program is free software; you can redistribute it and/or
 *    modify it under the terms of the GNU General Public License
 *    version 2 as published by the Free Software Foundation.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

global $capsman, $menu, $submenu, $admin_global_menu, $admin_global_submenu;;

$ppc_global_menu     = (array)get_option('ppc_admin_menus_menu');
$ppc_global_submenu  = (array)get_option('ppc_admin_menus_submenu');
$custom_menus        = (array)get_option('ppc_admin_menus_custom_menus', []);
$all_custom_menus    = !empty($custom_menus['menus']) ? $custom_menus['menus'] : [];
$all_custom_submenus = !empty($custom_menus['submenus']) ? $custom_menus['submenus'] : [];

$admin_menu_settings = (array) get_option('ppc_admin_menus_settings', []);
$show_menu_slug      = !empty($admin_menu_settings['show_menu_slug']);
$hide_submenu        = !empty($admin_menu_settings['hide_submenu']);


// We're getting menu issue when admin menus are restricted. So, we need to use menu before restriction

if (is_array($admin_global_menu) && count($admin_global_menu) > count($menu)) {
    $menu   = $admin_global_menu;
    $ppc_global_menu   = $admin_global_menu;
}

if (is_array($admin_global_submenu) && count($admin_global_submenu) > count($submenu)) {
    // for submenu, we need to remove any group not in menu before count for fair count
    $menu_index = array_column($menu, 2);

    $filtered_submenu = array_intersect_key($submenu, 
        array_flip($menu_index)
    );

    $filtered_admin_global_submenu = array_intersect_key($admin_global_submenu, 
        array_flip($menu_index)
    );
    if (count($filtered_admin_global_submenu) > count($filtered_submenu)) {
        $submenu   = $admin_global_submenu;
        $ppc_global_submenu   = $admin_global_submenu;
    }
}

// grouping original menu by their slug to get original position key
$grouped_menu       = [];
$grouped_submenu    = [];
if (is_array($menu)) {
    $grouped_menu = array_reduce($menu, function($carry, $menu_data) use($menu) {
        $carry[$menu_data[2]] = [
            'position' => array_search($menu_data, $menu),
            'menu_data' => $menu_data
        ];
        return $carry;
    }, []);
}
if (is_array($submenu)) {
    $grouped_submenu = array_map(function ($children) {
        return array_reduce($children, function ($carry, $menu_data) use ($children) {
            $position = array_search($menu_data, $children, true);
            $carry[$menu_data[2]] = [
                'position' => $position,
                'menu_data' => $menu_data,
            ];
            return $carry;
        }, []);
    }, $submenu);
}

$roles = $capsman->roles;
$default_role = $capsman->current;

// update menu and submenu for current role
$role_menu = PublishPress\Capabilities\Admin_Menus_Filters::getRoleAdminMenu($ppc_global_menu, $ppc_global_submenu, [$default_role]);
$ppc_global_menu = $role_menu['menu'];
$ppc_global_submenu = $role_menu['submenu'];

$role_caption = translate_user_role($roles[$default_role]);

$admin_menu_option = !empty(get_option('capsman_admin_menus')) ? get_option('capsman_admin_menus') : [];
$admin_menu_option = array_key_exists($default_role, $admin_menu_option) ? (array)$admin_menu_option[$default_role] : [];

$admin_child_menu_option = !empty(get_option('capsman_admin_child_menus')) ? get_option('capsman_admin_child_menus') : [];
$admin_child_menu_option = array_key_exists($default_role, $admin_child_menu_option) ? (array)$admin_child_menu_option[$default_role] : [];

?>

<div class="wrap publishpress-caps-manage pressshack-admin-wrapper pp-capability-menus-wrapper admin-menus">
    <div id="icon-capsman-admin" class="icon32"></div>
    <h2><?php esc_html_e('Admin Menus', 'capabilities-pro'); ?></h2>

    <form method="post" id="ppc-admin-menu-form" action="admin.php?page=pp-capabilities-admin-menus">
        <?php wp_nonce_field('pp-capabilities-admin-menus'); ?>
        <div class="pp-columns-wrapper pp-enable-sidebar clear">
                <div class="pp-column-left">
                <fieldset>
                    <table id="akmin">
                        <tr>
                            <td class="content">

                                <div class="publishpress-filters">
                                    <select name="ppc-admin-menu-role" class="ppc-admin-menu-role">
                                        <?php
                                        foreach ($roles as $role_name => $name) :
                                            $name = translate_user_role($name);
                                            ?>
                                            <option value="<?php echo esc_attr($role_name);?>" <?php selected($default_role, $role_name);?>><?php echo esc_html($name);?></option>
                                        <?php
                                        endforeach;
                                        ?>
                                    </select> &nbsp;

                                    <img class="loading" src="<?php echo esc_url($capsman->mod_url); ?>/images/wpspin_light.gif" style="display: none">

                                    <input type="submit" name="admin-menu-submit"
                                        value="<?php esc_attr_e('Save Changes') ?>"
                                        class="button-primary ppc-admin-menu-submit" style="float:right" />
                                </div>

                                <div id="pp-capability-menu-wrapper" class="postbox">
                                    <div class="pp-capability-menus">

                                        <div class="pp-capability-menus-wrap">
                                            <div id="pp-capability-menus-general"
                                                class="pp-capability-menus-content editable-role" style="display: block;">

                                                <div class="ppc-new-menu-form">
                                                    <div class="new-menu-fields" style="display: none;">
                                                        <table class="menu-form-table">
                                                            <tr>
                                                                <td>
                                                                    <label for="menu-type"><?php esc_html_e('Type:', 'capabilities-pro'); ?></label>
                                                                    <select id="menu-type" class="menu-type">
                                                                        <option value="menu"><?php esc_html_e('Menu Link', 'capabilities-pro'); ?></option>
                                                                        <option value="submenu"><?php esc_html_e('Submenu Link', 'capabilities-pro'); ?></option>
                                                                        <option value="menu-separator"><?php esc_html_e('Menu Separator', 'capabilities-pro'); ?></option>
                                                                        <option value="submenu-separator"><?php esc_html_e('Submenu Separator', 'capabilities-pro'); ?></option>
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <label for="menu-position"><?php esc_html_e('Position:', 'capabilities-pro'); ?></label>
                                                                    <select id="menu-position" class="menu-position">
                                                                        <option value="after"><?php esc_html_e('After', 'capabilities-pro'); ?></option>
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <label for="add-menu-after">&nbsp;</label>
                                                                    <select id="add-menu-after" class="add-menu-after"></select>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <label for="menu-title">
                                                                        <?php esc_html_e('Menu Title:', 'capabilities-pro'); ?>
                                                                        <span class="required">*</span>
                                                                    </label>
                                                                    <input type="text" id="menu-title" class="menu-title" value="<?php esc_attr_e('My Custom Menu', 'capabilities-pro'); ?>" />
                                                                    <span class="required required-message hidden-element">
                                                                        <?php esc_html_e('Menu Title is required.', 'capabilities-pro'); ?>
                                                                    </span>

                                                                    <label for="separator-style" class="hidden-element"><?php esc_html_e('Separator Style', 'capabilities-pro'); ?></label>
                                                                    <select id="separator-style" class="separator-style hidden-element">
                                                                        <option value="line"><?php esc_html_e('Line (____________________)', 'capabilities-pro'); ?></option>
                                                                        <option value="empty"><?php esc_html_e('Empty Space', 'capabilities-pro'); ?></option>

                                                                    </select>
                                                                </td>
                                                                <td colspan="2">
                                                                    <label for="menu-url">
                                                                        <?php esc_html_e('Menu URL:', 'capabilities-pro'); ?>
                                                                        <div class="ppc-tool-tip">
                                                                            <span class="dashicons dashicons-editor-help"></span>
                                                                            <div class="tool-tip-text">
                                                                                <p><?php printf(__( 'The menu and submenu URL must be unique, it can link to an internal page like %1s or external page link like %s', 'capabilities-pro' ), '<strong>edit-tags.php?taxonomy=category</strong>', '<strong>https://example.com/external-link</strong>' ); ?></p>
                                                                                <i></i>
                                                                            </div>
                                                                        </div>
                                                                    </label>
                                                                    <input type="text" value="" id="menu-url" class="menu-url" />
                                                                    <span class="required required-message hidden-element">
                                                                        <?php esc_html_e('Menu with this url already exist.', 'capabilities-pro'); ?>
                                                                    </span></tr>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <label for="menu-capability">
                                                                        <?php esc_html_e('Menu Capability:', 'capabilities-pro'); ?>
                                                                        <span class="required">*</span>
                                                                        <div class="ppc-tool-tip">
                                                                            <span class="dashicons dashicons-editor-help"></span>
                                                                            <div class="tool-tip-text">
                                                                                <p><?php printf(__( 'This is the capability required to see this menu. For example: %1s or %2s If you\'re entering a custom capability, remember to add and assign it to preferred roles on the capabilities page', 'capabilities-pro' ), '<strong>read</strong>', '<strong>manage_options</strong>' ); ?></p>
                                                                                    <i></i>
                                                                            </div>
                                                                        </div>
                                                                    </label>
                                                                    <input type="text" id="menu-capability" class="menu-capability" value="read" />
                                                                    <span class="required required-message hidden-element">
                                                                        <?php esc_html_e('Menu Capability is required.', 'capabilities-pro'); ?>
                                                                    </span>
                                                                </td>
                                                                <td style="vertical-align: bottom;">
                                                                    <button type="button" class="button button-small ppc-icon-selector-button select-icon-button" data-current="admin-generic"><i class="dashicons dashicons-admin-generic"></i> <span><?php esc_html_e('Menu Icon', 'capabilities-pro'); ?></span></button>
                                                                </td>
                                                                <td style="vertical-align: bottom;">
                                                                    <button style="float: right;" type="button" class="save-new-menu button-primary"><?php esc_html_e('Save Menu Link', 'capabilities-pro'); ?></button>
                                                                    <span class="spinner admin-menu-spinner"></span>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                        <div class="admin-menu-response-message"></div>
                                                        
                                                    </div>
                                                </div>

                                                <table class="wp-list-table widefat fixed striped pp-capability-menus-select">

                                                    <thead>
                                                        <tr class="ppc-menu-row parent-menu">

                                                            <td class="restrict-column ppc-menu-checkbox">
                                                                <input id="check-all-item" class="check-item check-all-menu-item" type="checkbox"/>
                                                            </td>
                                                            <td class="menu-column ppc-menu-item">
                                                                <label for="check-all-item">
                                                                <span class="menu-item-link check-all-menu-link">
                                                                    <strong>
                                                                    <?php esc_html_e('Toggle all', 'capabilities-pro'); ?>
                                                                    </strong>
                                                                </span></label>
                                                            </td>
                                                            <td class="menu-column ppc-menu-action" style="text-align: right;">
                                                                <div class="add-new-menu button-secondary">
                                                                    <span class="dashicons dashicons-plus-alt"></span>
                                                                    <?php esc_html_e('New Menu Link', 'capabilities-pro'); ?>
                                                                </div>
                                                            </td>

                                                        </tr>
                                                    </thead>

                                                    <tfoot>
                                                        <tr class="ppc-menu-row parent-menu">

                                                            <td class="restrict-column ppc-menu-checkbox">
                                                                <input id="check-all-item-2" class="check-item check-all-menu-item" type="checkbox"/>
                                                            </td>
                                                            <td class="menu-column ppc-menu-item">
                                                                <label for="check-all-item-2">
                                                                    <span class="menu-item-link check-all-menu-link">
                                                                    <strong>
                                                                        <?php esc_html_e('Toggle all', 'capabilities-pro'); ?>
                                                                    </strong>
                                                                    </span>
                                                                </label>
                                                            </td>
                                                            <td class="menu-column ppc-menu-action">
                                                            </td>

                                                        </tr>
                                                    </tfoot>

                                                    <tbody class="menu-table-body">

                                                    <?php

                                                    if (isset($ppc_global_menu) && '' !== $ppc_global_menu) {

                                                        ksort($ppc_global_menu);

                                                        if (!get_option('link_manager_enabled')) {
                                                            if (isset($ppc_global_menu[15]) && ('edit-tags.php?taxonomy=link_category' == $ppc_global_menu[15][2])) {
                                                                unset($ppc_global_menu[15]);
                                                            }

                                                            unset($ppc_global_submenu['edit-tags.php?taxonomy=link_category']);
                                                        }

                                                        $sn = 0;

                                                        foreach ($ppc_global_menu as $key => $item) {

                                                            $item_menu_slug = $item[2];

                                                            if ('' === $item_menu_slug) {
                                                                continue;
                                                            }

                                                            if (empty($item[0])) {
                                                                // we need to support separator now for proper menu reordering
                                                                $item[0] = $item_menu_slug;
                                                            }

                                                            //disable capmans checkbox if admin is editing own role
                                                            if ($item_menu_slug === 'pp-capabilities-roles' && in_array($default_role, wp_get_current_user()->roles)) {
                                                                $disabled_field = ' disabled';
                                                                $disabled_class = ' disabled';

                                                                $disabled_info = true;

                                                            } else {
                                                                $disabled_field = $disabled_class = $disabled_info = '';
                                                            }

                                                            $menu_title = ppc_process_admin_menu_title($item[0]);

                                                            // Which icon should we use?
                                                            $icon_name = !empty($item[6]) ? $item[6] : null;

                                                            $icon_type = '';
                                                            $icon_style = '';
                                                            if ($icon_name) {
                                                                // Check if the icon_name starts with 'dashicons-'
                                                                if (strpos($icon_name, 'dashicons-') === 0) {
                                                                    $icon_type = 'dashicons';
                                                                } elseif (strpos($icon_name, 'data:image') === 0) {
                                                                    $icon_type = 'data-image';
                                                                    $icon_style = 'background-repeat: no-repeat;background-position: center;color: #0073aa;background-size: 20px auto;background-image: url(' . $icon_name . ');';
                                                                }
                                                            }

                                                            if (empty($icon_name)) {
                                                                switch(wp_strip_all_tags($item[0])) {
                                                                    default:
                                                                        $icon_name = 'dashicons-open-folder';
                                                                    break;
                                                                case 'Dashboard':
                                                                    $icon_name = 'dashicons-dashboard';
                                                                    break;
                                                                case 'Media':
                                                                    $icon_name = 'dashicons-admin-media';
                                                                    break;
                                                                case 'Links':
                                                                    $icon_name = 'dashicons-admin-links';
                                                                    break;
                                                                case 'Posts':
                                                                    $icon_name = 'dashicons-admin-post';
                                                                    break;
                                                                case 'Pages':
                                                                    $icon_name = 'dashicons-admin-page';
                                                                    break;
                                                                case 'Appearance':
                                                                    $icon_name = 'dashicons-admin-appearance';
                                                                    break;
                                                                case 'Plugins':
                                                                    $icon_name = 'dashicons-admin-plugins';
                                                                    break;
                                                                case 'Comments':
                                                                    $icon_name = 'dashicons-admin-comments';
                                                                    break;
                                                                case 'Users':
                                                                    $icon_name = 'dashicons-admin-users';
                                                                    break;
                                                                case 'Tools':
                                                                    $icon_name = 'dashicons-admin-tools';
                                                                    break;
                                                                case 'Settings':
                                                                    $icon_name = 'dashicons-admin-settings';
                                                                    break;
                                                                case 'Capabilities':
                                                                    $icon_name = 'dashicons-admin-network';
                                                                    break;
                                                                case 'PublishPress Blocks':
                                                                    $icon_name = 'dashicons-layout';
                                                                    break;
                                                                case 'Authors':
                                                                    $icon_name = 'dashicons-groups';
                                                                    break;
                                                                case 'Revisions':
                                                                    $icon_name = 'dashicons-backup';
                                                                    break;
                                                                    break;
                                                                case 'PublishPress':
                                                                    $icon_name = 'dashicons-calendar-alt';
                                                                    break;
                                                                case 'Checklists':
                                                                    $icon_name = 'dashicons-yes-alt';
                                                                    break;
                                                                case 'Notifications':
                                                                    $icon_name = 'dashicons-bell';
                                                                    break;

                                                                }
                                                            }

                                                            $editable_menu = true;

                                                            $additional_class = '';
                                                            $separator_menu = false;
                                                            if (strpos($item[2], 'separator') === 0) {
                                                                $icon_name = 'dashicons-minus';
                                                                $additional_class .= ' separator';
                                                                $separator_menu = true;
                                                                $menu_title = 'separator';
                                                                $editable_menu = false;
                                                            }

                                                            if (empty($menu_title)) {
                                                                $editable_menu = false;
                                                                $menu_title = __( '(Can\'t detect menu name)', 'capabilities-pro' );
                                                            }
                                                            
                                                            $item_menu_slug = $item[2];
                                                            $menu_position = isset($grouped_menu[$item[2]]['position']) ? $grouped_menu[$item[2]]['position'] : '';

                                                            $custom_menu = array_key_exists($item[2], $all_custom_menus);
                                                            ?>

                                                            <tr class="ppc-menu-row parent-menu <?php echo esc_attr($additional_class);?>" data-main-slug="<?php echo esc_attr($item_menu_slug);?>" data-position="<?php echo esc_attr($menu_position);?>">

                                                                <td class="restrict-column ppc-menu-checkbox">
                                                                <input id="check-item-<?php echo (int) $sn;?>"<?php echo esc_attr($disabled_field);?> class="check-item" type="checkbox" 
                                                                    name="pp_cababilities_disabled_menu<?php echo esc_attr($disabled_class);?>[]" 
                                                                    value="<?php echo esc_attr($item_menu_slug);?>"<?php checked(in_array($item_menu_slug, $admin_menu_option));?> />
                                                                </td>
                                                                <td class="menu-column ppc-menu-item <?php echo esc_attr($disabled_class);?>">

                                                                    <label for="check-item-<?php echo esc_attr($sn);?>">
                                                                        <span class="menu-item-link<?php echo (in_array($item_menu_slug, $admin_menu_option)) ? ' restricted' : '';?>">
                                                                        <strong>
                                                                            <i class="dashicons <?php echo esc_attr($icon_name) ?>" style="<?php echo esc_attr($icon_style ); ?>"></i>
                                                                            <span class="menu-title" data-menu-slug="<?php echo esc_attr($item_menu_slug); ?>" data-menu-type="menu">
                                                                                <?php echo esc_html($menu_title); ?>
                                                                            </span>
                                                                            <?php if (!$editable_menu && !$separator_menu):?>
                                                                                <span class="ppc-tool-tip"><span class="dashicons dashicons-info-outline"></span><span class="tool-tip-text"><p><?php esc_html_e('This menu name is not available in menu global data or directly registered with the menu', 'capabilities-pro');?>.</p><i></i></span></span>
                                                                            <?php endif;?>
                                                                            <?php if (!$separator_menu) : ?>
                                                                                <small class="admin-menu-slug ppc-other-menu-element" style="<?php echo $show_menu_slug ? '' : 'display: none;'; ?>">
                                                                                    <?php echo esc_html(pp_capabilities_admin_menu_path($item_menu_slug)); ?>
                                                                                </small>
                                                                            <?php endif; ?>
                                                                            <span class="ppc-admin-menu-rename-form" style="display: none;">
                                                                                <input type="text" class="menu-title-input" value="<?php echo esc_attr($menu_title); ?>">
                                        
                <select  class="rename-menu-scope">
                    <option value="role"><?php printf(esc_html__('%s Role', 'capabilities-pro'), $role_caption); ?></option>
                    <option value="all"><?php esc_html_e('All Roles', 'capabilities-pro'); ?></option>
                </select>
                                                                <button type="button" class="button button-small save-menu-title" title="<?php esc_attr_e('Save', 'capabilities-pro'); ?>">
                                                                                    <span class="dashicons dashicons-saved"></span>
                                                                                </button>
                                                                                <button type="button" class="button button-small cancel-menu-title" title="<?php esc_attr_e('Cancel', 'capabilities-pro'); ?>">
                                                                                    <span class="dashicons dashicons-no"></span>
                                                                                </button>
                                                                            </span>
                                                                        </strong>
                                                                    </span>
                                                                    </label> 

                                                                    <?php if (!empty($disabled_info)):?><span class="ppc-tool-tip"><span class="dashicons dashicons-info"></span><span class="tool-tip-text"><p> <?php esc_html_e('This option is disabled to prevent complete lockout', 'capabilities-pro');?>.</p><i></i></span></span>
                                                                    <?php endif;?>
                                                                </td>
                                                                <td class="menu-column ppc-menu-item">
                                                                    <div class="pp-admin-menu-buttons">
                                                                        <?php if ($custom_menu) : ?>
                                                                            <div class="ppc-tool-tip click-tooltip">
                                                                                <span class="dashicons dashicons-trash delete"></span>
                                                                                <div class="tool-tip-text">
                                                                                    <p><?php printf(__( 'Are you sure you want to delete %1s? %2s %3s', 'capabilities-pro' ), '<strong>' . $menu_title . '</strong>', '<a class="delete-admin-menu" href="#">'. esc_html__('Delete', 'capabilities-pro') .'</a>', ' | <a class="cancel-click-tooltip" href="#">'. esc_html__('Cancel', 'capabilities-pro') .'</a>' ); ?></p>
                                                                                    <i></i>
                                                                                </div>
                                                                            </div>
                                                                        <?php else : ?>
                                                                            <a style="visibility: hidden;"><span class="dashicons dashicons-edit"></span></a>
                                                                        <?php endif; ?>
                                                                        <div class="ppc-tool-tip">
                                                                            <span class="dashicons dashicons-editor-help"></span>
                                                                            <div class="tool-tip-text">
                                                                                <p><?php printf(__( 'The capability required to access this menu is %s', 'capabilities-pro' ), '<strong>' . $item[1] . '</strong>' ); ?></p>
                                                                                <i></i>
                                                                            </div>
                                                                        </div>
                                                                        <?php if ($editable_menu):?>
                                                                            <a class="rename-admin-menu" href="#" title="<?php esc_html_e('Rename Menu', 'capabilities-pro');?>">
                                                                                <span class="dashicons dashicons-edit"></span>
                                                                            </a>
                                                                        <?php else : ?>
                                                                            <a style="visibility: hidden;"><span class="dashicons dashicons-edit"></span></a>
                                                                        <?php endif; ?>
                                                                        <a class="move-admin-menu up" href="#" title="<?php esc_html_e('Move Up', 'capabilities-pro');?>">
                                                                            <span class="dashicons dashicons-arrow-up-alt2"></span>
                                                                        </a>
                                                                        <a class="move-admin-menu down" href="#" title="<?php esc_html_e('Move Down', 'capabilities-pro');?>">
                                                                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                                                                        </a>
                                                                    </div>
                                                                </td>

                                                            </tr>

                                                            <?php
                                                            if (!isset($ppc_global_submenu[$item_menu_slug])) {
                                                                continue;
                                                            }

                                                            
                                                            $last_subitem = false;
                                                            $last_sn = 0;

                                                            foreach ($ppc_global_submenu[$item_menu_slug] as $subkey => $subitem) {
                                                                $sn++;
                                                                $submenu_slug = $subitem[2];

                                                                //disable pp-capabilities-admin-menus checkbox if admin is editing own role
                                                                if ( $submenu_slug === 'pp-capabilities-admin-menus' && in_array($default_role, wp_get_current_user()->roles)) {
                                                                    $disabled_field = ' disabled';
                                                                    $disabled_class = ' disabled';

                                                                    $disabled_info = true;

                                                                } else {
                                                                    $disabled_field = $disabled_class = $disabled_info = '';
                                                                }

                                                                $sub_menu_value = $item_menu_slug . $subkey;

                                                                $sub_menu_title = ppc_process_admin_menu_title($subitem[0]);
                                                                $item_menu_slug = $subitem[2];

                                                                
                                                                $menu_position = isset($grouped_submenu[$item[2]][$subitem[2]]['position']) ? $grouped_submenu[$item[2]][$subitem[2]]['position'] : '';

                                                                $custom_menu = array_key_exists($subitem[2], $all_custom_submenus);

                                                                $editable_menu = true;

                                                                $icon_name = 'dashicons-arrow-right';
                                                                $additional_class = '';
                                                                $separator_menu = false;
                                                                if (strpos($subitem[2], 'separator') === 0 || strpos($subitem[2], 'simple_tagsst_separator') === 0) {
                                                                    $icon_name = 'dashicons-minus';
                                                                    $additional_class .= ' separator';
                                                                    $separator_menu = true;
                                                                    $sub_menu_title = 'separator';
                                                                    $editable_menu = false;
                                                                }

                                                                if (empty($sub_menu_title)) {
                                                                    $editable_menu = false;
                                                                    $sub_menu_title = __( '(Can\'t detect menu name)', 'capabilities-pro' );
                                                                }
                                                                ?>
                                                                <tr class="ppc-menu-row child-menu <?php echo esc_attr($additional_class);?>" data-main-slug="<?php echo esc_attr($item_menu_slug);?>"  data-position="<?php echo esc_attr($menu_position);?>" data-parent-slug="<?php echo esc_attr($item[2]);?>" style="<?php echo $hide_submenu ? 'display: none;' : ''; ?>">

                                                                    <td class="restrict-column ppc-menu-checkbox">
                                                                        <input id="check-item-<?php echo esc_attr($sn);?>"<?php echo esc_attr($disabled_field);?> class="check-item" type="checkbox" 
                                                                            name="pp_cababilities_disabled_child_menu<?php echo esc_attr($disabled_class);?>[]" 
                                                                            value="<?php echo esc_attr($sub_menu_value);?>" 
                                                                            <?php echo (in_array($sub_menu_value, $admin_child_menu_option)) ? 'checked' : '';?> 
                                                                            data-val="<?php echo esc_attr($sub_menu_value);?>" />
                                                                    </td>
                                                                    <td class="menu-column ppc-menu-item'<?php echo esc_attr($disabled_class);?>">

                                                                        <label for="check-item-<?php echo esc_attr($sn);?>">
                                                                            <span class="menu-item-link<?php echo (in_array($sub_menu_value, $admin_child_menu_option)) ? ' restricted' : '';?>">
                                                                            <strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <i class="dashicons <?php echo esc_attr($icon_name); ?>"></i>
                                                                            <span class="menu-title" data-menu-slug="<?php echo esc_attr($sub_menu_value); ?>" data-menu-type="submenu" data-parent-slug="<?php echo esc_attr($item_menu_slug); ?>">
                                                                                <?php echo esc_html($sub_menu_title); ?>
                                                                            </span>
                                                                            <?php if (!$editable_menu && !$separator_menu):?>
                                                                                <span class="ppc-tool-tip"><span class="dashicons dashicons-info-outline"></span><span class="tool-tip-text"><p><?php esc_html_e('This menu name is not available in menu global data or directly registered with the menu', 'capabilities-pro');?>.</p><i></i></span></span>
                                                                            <?php endif;?>
                                                                            <?php if (!$separator_menu) : ?>
                                                                                <small class="admin-menu-slug ppc-other-menu-element" style="<?php echo $show_menu_slug ? '' : 'display: none;'; ?>">
                                                                                    <?php echo esc_html(pp_capabilities_admin_menu_path($item_menu_slug)); ?>
                                                                                </small>
                                                                            <?php endif; ?>
                                                                            <span class="ppc-admin-menu-rename-form" style="display: none;">
                                                                                <input type="text" class="menu-title-input" value="<?php echo esc_attr($sub_menu_title); ?>">
                                                                                <select  class="rename-menu-scope">
                    <option value="role"><?php printf(esc_html__('%s Role', 'capabilities-pro'), $role_caption); ?></option>
                    <option value="all"><?php esc_html_e('All Roles', 'capabilities-pro'); ?></option>
                </select>
                         
                                                                           <button type="button" class="button button-small save-menu-title" title="<?php esc_attr_e('Save', 'capabilities-pro'); ?>">
                                                                                    <span class="dashicons dashicons-saved"></span>
                                                                                </button>
                                                                                <button type="button" class="button button-small cancel-menu-title" title="<?php esc_attr_e('Cancel', 'capabilities-pro'); ?>">
                                                                                    <span class="dashicons dashicons-no"></span>
                                                                                </button>
                                                                            </span>
                                                                            </strong></span>
                                                                        </label>

                                                                        <?php if (!empty($disabled_info)):?>
                                                                            <span class="ppc-tool-tip"><span class="dashicons dashicons-info"></span><span class="tool-tip-text"><p><?php esc_html_e('This option is disabled to prevent complete lockout', 'capabilities-pro');?>.</p><i></i></span></span>
                                                                        <?php endif;?>
                                                                    </td>
                                                                    <td class="menu-column ppc-menu-item">
                                                                        <div class="pp-admin-menu-buttons">
                                                                            <?php if ($custom_menu) : ?>
                                                                                <div class="ppc-tool-tip click-tooltip">
                                                                                    <span class="dashicons dashicons-trash delete"></span>
                                                                                    <div class="tool-tip-text">
                                                                                        <p><?php printf(__( 'Are you sure you want to delete %1s? %2s %3s', 'capabilities-pro' ), '<strong>' . $sub_menu_title . '</strong>', '<a class="delete-admin-menu" href="#">'. esc_html__('Delete', 'capabilities-pro') .'</a>', ' | <a class="cancel-click-tooltip" href="#">'. esc_html__('Cancel', 'capabilities-pro') .'</a>' ); ?></p>
                                                                                        <i></i>
                                                                                    </div>
                                                                                </div>
                                                                            <?php else : ?>
                                                                                <a style="visibility: hidden;"><span class="dashicons dashicons-edit"></span></a>
                                                                            <?php endif; ?>
                                                                            <div class="ppc-tool-tip">
                                                                                <span class="dashicons dashicons-editor-help"></span>
                                                                                <div class="tool-tip-text">
                                                                                    <p><?php printf(__( 'The capability required to access this menu is %s', 'capabilities-pro' ), '<strong>' . $subitem[1] . '</strong>' ); ?></p>
                                                                                    <i></i>
                                                                                </div>
                                                                            </div>
                                                                            <?php if ($editable_menu):?>
                                                                                <a class="rename-admin-menu" href="#" title="<?php esc_html_e('Rename Menu', 'capabilities-pro');?>">
                                                                                <span class="dashicons dashicons-edit"></span>
                                                                                </a>
                                                                            <?php else : ?>
                                                                                <a style="visibility: hidden;"><span class="dashicons dashicons-edit"></span></a>
                                                                            <?php endif; ?>
                                                                            <a class="move-admin-menu up" href="#" title="<?php esc_html_e('Move Up', 'capabilities-pro');?>">
                                                                                <span class="dashicons dashicons-arrow-up-alt2"></span>
                                                                            </a>
                                                                            <a class="move-admin-menu down" href="#" title="<?php esc_html_e('Move Down', 'capabilities-pro');?>">
                                                                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                                                                            </a>
                                                                        </div>
                                                                    </td>

                                                                </tr>
                                                            <?php
                                                                if ($last_subitem && ($subitem[2] == $last_subitem[2])) {
                                                                    // If there is ambiguity due to two consective submenu items with the same caption, hide both                                           
                                                                    // Edit: Hide first one
                                                                    ?>
                                                                    <script type="text/javascript">
                                                                    /* <![CDATA[ */
                                                                    /*var elem = document.getElementById('check-item-<?php echo esc_attr($last_sn);?>');
                                                                    var parent = elem.closest('tr');
                                                                    parent.style.display = 'none';*/
                                                                    
                                                                    elem = document.getElementById('check-item-<?php echo esc_attr($sn);?>');
                                                                    parent = elem.closest('tr');
                                                                    parent.style.display = 'none';
                                                                    /* ]]> */
                                                                    </script>
                                                                    <?php
                                                                }

                                                                $last_subitem = $subitem;
                                                                $last_sn = $sn;

                                                            }  // end foreach ppc_global_submenu

                                                            $sn++;

                                                        } // end foreach ppc_global_menu

                                                    } else {
                                                        ?>
                                                        <tr><td style="color: red;"> <?php esc_html_e('No menu found', 'capabilities-pro');?></td></tr>
                                                        <?php
                                                    } 

                                                    ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <input type="submit" name="admin-menu-submit"
                                    value="<?php esc_attr_e('Save Changes') ?>"
                                    class="button-primary ppc-admin-menu-submit" style="float: right; margin-top: 10px;"/>
                            </td>
                        </tr>
                    </table>

                </fieldset>
            </div><!-- .pp-column-left -->
            <div class="pp-column-right pp-capabilities-sidebar">
            <?php 
            $banner_title  = __('How to use Admin Menus', 'capabilities-pro');
            $banner_messages = ['<p>'];
            $banner_messages[] = esc_html__('Admin Menus allows you to edit the admin menu links and control who has access.', 'capabilities-pro');
            $banner_messages[] = '</p><p>';
            $banner_messages[] = sprintf(esc_html__('%1$s = No change', 'capabilities-pro'), '<input type="checkbox" title="'. esc_attr__('usage key', 'capabilities-pro') .'" disabled>') . ' <br />';
            $banner_messages[] = sprintf(esc_html__('%1$s = This feature is denied', 'capabilities-pro'), '<input type="checkbox" title="'. esc_attr__('usage key', 'capabilities-pro') .'" checked disabled>') . ' <br />';
            $banner_messages[] = '</p>';
            $banner_messages[] = '<p><a class="button ppc-checkboxes-documentation-link" href="https://publishpress.com/knowledge-base/admin-menus-screen/"target="blank">' . esc_html__('View Documentation', 'capabilities-pro') . '</a></p>';
            pp_capabilities_sidebox_banner($banner_title, $banner_messages);
            ?>
            <?php 
            $banner_title  = __('Admin Menus Settings', 'capabilities-pro');
            $banner_messages = ['<p>'];
            $banner_messages[] = sprintf(esc_html__('%1$s Hide Submenus', 'capabilities-pro'), '<input type="checkbox" class="admin-menu-setting-field hide-submenu" ' . checked($hide_submenu, true, false) . '>') . ' <br />';
            $banner_messages[] = sprintf(esc_html__('%1$s Show Menu Slugs', 'capabilities-pro'), '<input type="checkbox" class="admin-menu-setting-field show-menu-slug" ' . checked($show_menu_slug, true, false) . '>') . ' <br />';
            $banner_messages[] = '</p>';
            pp_capabilities_sidebox_banner($banner_title, $banner_messages);
            ?>
            <?php 
            $banner_title  = __('Reset Admin Menus', 'capabilities-pro');
            $banner_messages = ['<p>'];
            $banner_messages[] = esc_html__('You can reset admin menu order and name to their original position and names. You can also reset menu cache to fix issue of missing menu.', 'capabilities-pro');
            $banner_messages[] = '</p><p>';
            $banner_messages[] = sprintf(esc_html__('%1$s Reset Menu Order', 'capabilities-pro'), '<input type="checkbox" class="reset-admin-menu-order">') . ' <br />';
            $banner_messages[] = sprintf(esc_html__('%1$s Reset Cached Menu', 'capabilities-pro'), '<input type="checkbox" class="reset-admin-menu-cache">') . ' <br />';
            $banner_messages[] = sprintf(esc_html__('%1$s Reset Menu Names', 'capabilities-pro'), '<input type="checkbox" class="reset-admin-menu-names">') . ' <br />';
            $banner_messages[] = '</p>';
            $banner_messages[] = '<p><a style="color: red; border-color: red;" type="button" class="button ppc-reset-admin-menu-options">' . esc_html__('Reset Selected Option', 'capabilities-pro') . '</a><div class="clear"></div></p>';
            pp_capabilities_sidebox_banner($banner_title, $banner_messages);
            ?>
            </div><!-- .pp-column-right -->
        </div><!-- .pp-columns-wrapper -->
    </form>

    <script type="text/javascript">
        /* <![CDATA[ */
        jQuery(document).ready(function ($) {

            // -------------------------------------------------------------
            //   reload page for instant reflection if user is updating own role
            // -------------------------------------------------------------
            <?php if((int)$ppc_admin_menu_reload === 1){ ?>
                window.location = '<?php echo esc_url_raw(admin_url('admin.php?page=pp-capabilities-admin-menus&role=' . $default_role . '')); ?>';
            <?php } ?>

            // -------------------------------------------------------------
            //   Set form action attribute to include role
            // -------------------------------------------------------------
            $('#ppc-admin-menu-form').attr('action', '<?php echo esc_url_raw(admin_url('admin.php?page=pp-capabilities-admin-menus&role=' . $default_role . '')); ?>');

            // -------------------------------------------------------------
            //   Instant restricted item class
            // -------------------------------------------------------------
            $(document).on('change', '.pp-capability-menus-wrapper .ppc-menu-row .check-item', function () {

                if ($(this).is(':checked')) {
                    //add class if value is checked
                    $(this).closest('tr').find('.menu-item-link').addClass('restricted');

                    //toggle all checkbox
                    if ($(this).hasClass('check-all-menu-item')) {
                        $("input[type='checkbox'][name='pp_cababilities_disabled_menu[]']").prop('checked', true);
                        $("input[type='checkbox'][name='pp_cababilities_disabled_child_menu[]']").prop('checked', true);
                        $('.menu-item-link').addClass('restricted');
                    } else {
                        $('.check-all-menu-link').removeClass('restricted');
                        $('.check-all-menu-item').prop('checked', false);
                    }

                } else {
                    //unchecked value
                    $(this).closest('tr').find('.menu-item-link').removeClass('restricted');

                    //toggle all checkbox
                    if ($(this).hasClass('check-all-menu-item')) {
                        $("input[type='checkbox'][name='pp_cababilities_disabled_menu[]']").prop('checked', false);
                        $("input[type='checkbox'][name='pp_cababilities_disabled_child_menu[]']").prop('checked', false);
                        $('.menu-item-link').removeClass('restricted');
                    } else {
                        $('.check-all-menu-link').removeClass('restricted');
                        $('.check-all-menu-item').prop('checked', false);
                    }

                }

            });

            // -------------------------------------------------------------
            //   Load selected roles menu
            // -------------------------------------------------------------
            $(document).on('change', '.pp-capability-menus-wrapper .ppc-admin-menu-role', function () {

                //disable select
                $('.pp-capability-menus-wrapper .ppc-admin-menu-role').attr('disabled', true);

                //hide button
                $('.pp-capability-menus-wrapper .ppc-admin-menu-submit').hide();

                //show loading
                $('#pp-capability-menu-wrapper').hide();
                $('div.publishpress-caps-manage img.loading').show();

                //go to url
                window.location = '<?php echo esc_url_raw(admin_url('admin.php?page=pp-capabilities-admin-menus&role=')); ?>' + $(this).val() + '';

            });
        });
        /* ]]> */
    </script>

    <?php if (!defined('PUBLISHPRESS_CAPS_PRO_VERSION') || get_option('cme_display_branding')) {
        cme_publishpressFooter();
    }
    ?>
</div>
<?php
