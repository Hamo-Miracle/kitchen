=== BNFW - Custom Fields Add-on ===
Contributors: voltronik
Donate link: https://betternotificationsforwp.com/donate/
Requires at least: 4.3
Tested up to: 6.5
Stable tag: 1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This add-on provides a number of new shortcodes allowing you to include data from custom fields in both the subject and message body of your notifications. It also allows you to set one or multiple custom fields that will 'trigger' the notification, as well as a number of other useful features.

== Changelog ==

= 1.3 =
* Added: New Category/Tag/Term notifications can now show custom field shortcodes in their notifications.
* Added: New formatter options to show a category/tag/term name or slug as opposed to the ID in a notification.
* Added: New formatter options for checkbox and radio custom fields.
* Fixed: Sending multiple Custom Field Updated notifications even if only a single field has changed.
* Fixed: 'New User Registration - For Admin' not showing custom fields as well as some shortcodes.

= 1.2.2 =
* Fixed: Some custom fields weren't showing in notifications triggered by the Block Editor in WordPress 5.7.
* Fixed: Log messages were showing when `WP_DEBUG` was switched on on the 'User Lost Password - For User' notification.

= 1.2.1 =
* Fixed: 'Custom Field Value Updated' notifications weren't being triggered when setting a value to a 1 or a 0.

= 1.2 =
* BIG UPDATE!
* New Notification: 'Post/Page/Custom Post Type' Custom Field Value Updated. Sends out when a specific custom field and specific value is updated on a post/page/custom post type. A highly requested feature!
* New Notification: User Custom Field Updated. Sends out when a specific custom field is updated in a user's profile.
* New Notification: User Custom Field Value Updated. Sends out when a specific custom field and specific value is updated in a user's profile.
* New: Custom field shortcodes can now include 'type' and 'format' parameters so that you can change the output of your custom fields in your notifications.
* New: Thanks to an improvement in WordPress, you can now include user custom field shortcodes in the New User Registration notifications.
* Full documentation for all new features can be found [here](https://betternotificationsforwp.com/documentation/add-ons/custom-fields/).

= 1.1.9 =
* Added: Shortcodes can now be used in the From Name, Email, and Reply To fields and can be filtered using `bnfw_from_field`, `bnfw_reply_name_field`, and `bnfw_reply_email_field`.
* Improved: Only show custom field options in the notification if the post type supports them.
* Improved: `$post` or `$post_id` can now be added as a parameter for the `bnfw_trigger_insert_post` filter hook so that insert notifications can be enabled by post types.
* Improved: `BNFW::send_notification_async()` is now a public function.
* Improved: `BNFW::send_notification()` and `BNFW::send_notification_async()` methods can now accept non-integer values.

= 1.1.8 =
* Fixed: BNFW wasn't triggering notifications for custom field updates when using Toolset Types.
* Fixed: Various PHP warnings.
* Fixed: A js warning on the All Notifications screen.

= 1.1.7 =
* Added: Support for comma-separated lists of email addresses when used in conjunction with the [Send to Any Email add-on](https://betternotificationsforwp.com/downloads/send-to-any-email/).

= 1.1.6 =
* Added: Support for the new global shortcodes.

= 1.1.5 =
* New: Shortcodes are now stripped from custom fields so as to not cause rendering issues within notifications.
* Added: Inline help tips are now available for this add-ons notification fields.

= 1.1.4 =
* Fixed: An issue where Custom Field Updated notifications weren't sending out when used in conjunction with BNFW v1.6.3.

= 1.1.3 =
* Added: Support for [Send to Any Email add-on](https://betternotificationsforwp.com/downloads/send-to-any-email/).
* Fixed: Author shortcodes weren't outputting in custom field update notifications.
* Fixed: An issue with the custom field select2 field.

= 1.1.2 =
* New: Translation file now provided.
* Fixed: A fatal error when used in conjunction with 1.6 of BNFW.

= 1.1.1 =
* Compatibility with BNFW Conditional Notifications add-on.

= 1.1 =
* Added: You can now include custom fields for user profiles created using ACF in any notification that can supports user shortcodes by using the new shortcode `[user_custom_field field="X"]`.

= 1.0 =
* Initial release.
