=== Air WP Sync Pro+ - Airtable to WordPress ===
Author: WP connect
Author URI: https://wpconnect.co/
Contributors: wpconnectco, thomascharbit, vincentdubroeucq
Tags: airtable, api, automation, synchronization
Requires at least: 5.7
Tested up to: 6.8
Requires PHP: 7.0
Stable tag: 2.9.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Swiftly sync Airtable to your WordPress website!

== Description ==

By connecting your Airtable database platform to your WordPress website, this plugin helps you manage your content better. Identify the Airtable fields you wish to link to WordPress and choose the format for your imported contents: articles, pages, products, etc. Simply define your ideal update frequency. Your plugin will automatically sync everything swiftly!

== Features ==

= Create connections to your Airtable tables =
* Set up as many connections as you want
* Publish an unlimited number of connections (Pro version)

= Choose the content format you want to import =
* Import your contents in your preferred format: articles, pages, etc.
* Link Airtable columns to WordPress fields

= Sync your personalized data =
* Create new custom fields (Pro version)
* Use specific content formats "Custom Post Types" (Pro version)

= Define synchronization setups =
* Sync your data manually or automatically
* Select update frequency (Pro version)
* Set up synchronization method (add, update, delete)

= ACF & Major SEO plugins support = (Pro+ version)
* ACF major fields support
* AllInOne SEO, Yoast SEO, RankMath & SEOPress support

== Installation ==

1. From your WordPress Dashboard, go to "Plugins > Add New".

2. Look for our plugin into the search bar: Air WP Sync.

3. Click on the 'Install Now' button of the plugin, and wait a few seconds.

4. Click on the "Activate" button (also available in "Plugins > Installed Plugins").

5. That's it, Air WP Sync is ready to use, find it in the sidebar!


== How to unleash your plugin's full potential? ==

1. Go to the Air WP Sync plugin page, click on “Add New” next to “Connections”.

2. Enter a name for your new connection.

3. Fill in the Airtable Settings (Airtable Access Token available [here](https://airtable.com/account)).

4. Select the form in which you want to import your content from your table (article, page, etc.) and set up the additional settings.

5. Link your table fields with WordPress fields. Be careful to respect the exact name of the Airtable columns (names are case sensitive).

6. Choose the Sync Settings (Strategy and Trigger).

7. Publish the connection, and you’re done!

8. Tip: By pressing the "Sync Now" button, you can synchronize your contents for the first time (even if you didn’t choose “Manual only” trigger).


== Frequently Asked Questions ==

= What is Airtable? =
Offered in SaaS mode, Airtable is a database tool designed to create a simple online form and a project management environment or even a custom CRM. Equipped with an automatic translation function, Airtable manages multiple views: maps, calendars, Kanban boards, Gantt charts, etc.

= Why do I need an Airtable account? =
Air WP Sync plugin uses the Airtable Access token to link columns with fields and send data. Creating an account on Airtable is free. Once connected, you can get the Airtable Access token [here](https://airtable.com/account)).
= Do I have to pay to use the plugin and Airtable? =
We offer three versions of our plugin:
- Free Version: This version is available to all users at no cost [Learn more and get the Free Version here](https://wordpress.org/plugins/air-wp-sync/).
- Pro Version: This is a paid version that offers enhanced features and capabilities. [Learn more and get the Pro Version here](https://wpconnect.co/air-wp-sync-plugin/#pricing-plan).
- Pro+ Version: An even more advanced paid version with extended functionalities, designed for those who need the most from our plugin. [Learn more and get the Pro+ Version here](https://wpconnect.co/air-wp-sync-plugin/#pricing-plan).
Airtable offers a free plan for an unlimited number of databases and small teams of up to 5 people. You will be able to add up to 1000 entries in each database and store up to 1 GB of attachments, but you will be limited to 1,000 API calls per workspace/per month.
Depending on your needs, several paid subscriptions allow you to unlock these limitations while accessing more advanced features ([see prices](https://www.airtable.com/pricing)).

= How are my databases synchronized? =
Once you have defined the synchronization frequency and published your connection, relax: everything is automatic. It is also possible to manually synchronize the connection - whenever you want - using the 'Sync Now' button.

= How can I get support? =
If you need some assistance, open a ticket on our Support center at [https://support.wpconnect.co/](https://support.wpconnect.co/).


== Screenshots ==
1. All Connections
2. Airtable and Import Settings
3. Field Mapping
4. Sync Settings


== Changelog ==

= 2.9.0 - 04/06/2025 =
Fix: Added missing translations.
Fix: Added WPConnect logos.
Fix: Fixed bug caused by empty ACF Repeater fields.
Improvement: Added number of post processed on sync status metabox.
Improvement: Added cache duration setting and clear cache button.
Feature: Added support for The Events Calendar.

= 2.8.0 - 11/03/2025 =
Fix: Moved menu declaration to default priority
Fix: Switched logo on connection page
Feature: Added support for Yoast SEO data for taxonomy terms
Improvement: Changed required capabilities for connections

= 2.7.0 - 26/11/2024 =
Compatibility with WordPress 6.7
Fix: Fixed creation of dynamic property deprecation warning.
Fix: Prevent empty ACF relationship values from linking to home page.
Feature: Added checkbox to allow comma-seperated lists of taxonomy terms.
Improvement: Added support for Rollup field in ACF number fields.
Improvement: Added hook to Air_WP_Sync_Helper::maybe_convert_emoji() helper function.
Improvement: Minor code cleanup.

= 2.6.1 - 18/09/2024 =
Fix: Added support for singleSelect and multipleSelect fields returned by formula fields. 

= 2.6.0 - 06/08/2024 =
Compatibility with WordPress 6.6
Feature: Added new UI for Airtable views filters
Improvement: Support for Multiple Link to Another Record for ACF taxonomy field
Improvement: Support for comma-seperated list of terms for taxonomy fields

= 2.5.0 - 11/06/2024 =
Feature: Added ability to import taxonomy terms
Improvement: Author mapping support both email and username
Improvement: Delete options on uninstall

= 2.4.0 - 02/04/2024 =
Fix: Prevent password reset when updating users from Airtable.
Fix: Added missing default features for custom post type mapping
Improvement: Whitelisting values mapped to post_status
Improvement: Remove shorter problematic synchronization schedules
Improvement: Better emoji support
Improvement: Force updates for posts with images when importer started more than 2 hours ago

= 2.3.0 - 10/01/2024 =
Improvement: Better update notice if license status has changed
Fix: Empty column mapped to a taxonomy no longer trigger error log.
Improvement: Better ACF field filters in the mapping table

= 2.2.0 - 21/11/2023 =
Fix: Added support for emoji
Feature: Mapping for post_parent
Feature: Added support for RankMath SEO plugin
Feature: Added support for SEOPress SEO plugin
Feature: Added support for All In One SEO plugin

= 2.1.0 =
Feature: Extend ACF field support to taxonomy field
Fix: better error management

= 2.0.4 =
Fix: Filter image urls from record hashes to avoid unnecessary updates

= 2.0.3 =
Feature: Option to enable support for Link to another record fields

= 2.0.2 =
Fix: Added cache for table data

= 2.0.1 =
Fix: formula field was missing from the mapping

= 2.0.0 =
Feature: Import airtable content as users
Feature: Added Post Status and Post Author options when importing posts
Feature: Extend ACF field support to Gallery, Color picker, Post object, User, Relationship and Page link fields
Feature: Support for "Link to another record" field type

= 1.1.0 =
Fix: In field mapping reordering
Fix: Synchronization declared finished too soon
Compatibility with WordPress 6.2
Feature: Support for Yoast SEO

= 1.0.0 =
Initial release



== Support ==
If you need some assistance, open a ticket on our Support center at [https://support.wpconnect.co/](https://support.wpconnect.co/).


== Troubleshooting ==
Make sure you have created your databases and Airtable columns names before adding a new connection. If you don't see it, wait 15 minutes. For performance reasons, your Airtable elements are cached for 15 minutes.
If needed, you can access to logs from a FTP server in this folder: /wp-content/uploads/airwpsync-logs