<?php
/**
Plugin Name: BNFW - Send to Any Email Add-on
Requires Plugins: bnfw
Plugin Script: bnfw-send-to-any-email.php
Plugin URI: https://betternotificationsforwp.com/
Description: Send to Any Email Add-on for Better Notifications for WP
Version: 1.1.1
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

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

require_once 'includes/class-bnfw-send-to-any-email.php';
BNFW_Send_To_Any_Email::factory();

function bnfw_send_to_any_email_setup() {
	if ( class_exists( 'BNFW_License' ) ) {
		$license = new BNFW_License( __FILE__, 'Send to Any Email Add-on', '1.1.1', 'Made with Fuel' );
	}
}
add_action( 'plugins_loaded', 'bnfw_send_to_any_email_setup' );
