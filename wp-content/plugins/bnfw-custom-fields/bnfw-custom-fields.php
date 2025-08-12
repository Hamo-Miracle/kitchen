<?php
/**
Plugin Name: BNFW - Custom Fields Add-on
Requires Plugins: bnfw
Plugin Script: bnfw-custom-fields.php
Plugin URI: https://betternotificationsforwp.com/
Description: Custom Fields Add-on for Better Notifications for WP
Version: 1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Author: Made with Fuel
Author URI: https://betternotificationsforwp.com/
Text Domain: bnfw
*/

/**
 * Copyright © 2024 Made with Fuel Ltd. (hello@betternotificationsforwp.com)
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

require_once 'includes/class-bnfw-custom-field-shortcode.php';
BNFW_Custom_Field_Shortcode::factory();

require_once 'includes/class-bnfw-custom-field-notification.php';
BNFW_Custom_Field_Notification::factory();

function bnfw_custom_fields_setup() {
	if ( class_exists( 'BNFW_License' ) ) {
		$license = new BNFW_License( __FILE__, 'Custom Fields Add-on', '1.3', 'Made with Fuel' );
	}
}
add_action( 'plugins_loaded', 'bnfw_custom_fields_setup' );
