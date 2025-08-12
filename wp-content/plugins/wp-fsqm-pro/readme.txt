=== eForm - WordPress Form Builder ===
Contributors: swashata, wpquark
Tags: form, quiz, survey, payment, woocommerce
Requires at least: 4.0
Tested up to: 6.3
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Requires PHP: 5.6

Gather feedbacks and run surveys on your WordPress Blog. Stores the gathered data in database. Displays the form & trends with shortcodes.

== Description ==

eForm is an advanced and flexible form builder that can be integrated into your existing WordPress site. This is a complete form management solution, for quizzes, surveys, data collection and user feedback of all kinds.

With the quick and easy drag and drop form builder, you can build unlimited forms and manage them from your admin dashboard. All submissions are stored in your eForm database, so you can view, track, analyze and act on the data you have captured. A user portal also allows registered users to review and track their submissions.

We have integrated eForm with the best in class e-mail newsletter providers and payment services, for even greater flexibility and security.

This robust and comprehensive form builder is the perfect combination of style and functionality: packed with all the elements you need, while clean and elegant to use.

== Installation ==

After you have downloaded eForm from codecanyon, install it as a manual plugin for the first time.

e.g.

1. Upload `wp-fsqm-pro` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Activate eForm from eForm > Settings with your purchase code and get auto updates.

== Frequently Asked Questions ==

= Where can I find documentation? =

Updated version of documentation can always be found at [WPQuark Knowledgebase](https://wpquark.com/kb/fsqm)

= I am stuck, where can I get support? =

Please visit our [support system](https://wpquark.ticksy.com).

== Changelog ==

= Version 4.19.1 =

* **Fix** - Compatibility with modern SMTP/mail plugins like WP Mail SMTP, FluentSMPT etc.
* **Fix** - Dynamic page number not appearing in the summary table and in single layout page mode.
* **Fix** - Escape attributes in listing tables.

= Version 4.19.0 =

* **Update** - Country list updated to the latest.
* **New** - New option for native select and placeholder.
* **New** - Add dynamic numbering to the tabs.
* **New** - Made the search in select smarter and added option to disable it.
* **Note** - Internal build dependency cleanup.
* **Fix** - Security issue in the address element.

= Version 4.18.0 =

* **Fix** - jQuery UI 1.13 compatibility fix.

= 4.17.1 =

* **New** - Automatically add random characters before filenames which are being uploaded.

= 4.17.0 =

* **New** - Accept format string and custom value in integration metadata.
* **New** - Add format strings for page based scores.
* **New** - Add new metadata configuration in webhook/zapier.
* **New** - Add option to show additional rows with custom values in the summary details table.

= 4.16.0 =

* **New** - Elementor integration for forms and popup.
* **New** - Add new sendy.co API in integration configuration.
* **New** - reCaptcha integration for securing file uploads.
* **Fix** - iFrame embed issues in older iOS devices.

= 4.15.1 =

* **Fix** - PHP8 issue on some quiz forms.
* **Fix** - Node sass compilation issue.

= 4.15.0 =

* **New** - Support PHP8.
* **New** - Add delimiter option in the WebHook integration.
* **New** - Option to send user notification email right away with WooCommerce integration.
* **Update** - Automatically pass URL parameters to iframe embedded forms with shortcodes, blocks and widgets.
* **Fix** - Use divs instead of h3 tags for error messages for SEO.
* **Fix** - PDFs and Email not respecting table color config.
* **Fix** - Block UI not working for User Portal block (Gutenberg).
* **Fix** - Length type conditional logic is incorrect for numeric input.

= 4.14.2 =

* **New** - Add iframe embed option to eForm Widget.
* **Fix** - iFrame embedded form height and width issue on iOS devices.
* **Fix** - Conflict between popup form and iFrame embedded form.
* **Fix** - iFrame embed width inside widgets and pages.
* **Fix** - Fancy math element not hiding properly on conditional logic.
* **Fix** - Use div in error messages instead of heading tags (SEO).

= 4.14.1 =

* **Fix** - Email template source not being deployed to production.
* **Fix** - Issue in form builder where empty form causes live view to fail.

= 4.14.0 =

* **New** - Email templates to make changing email appearance easier.
* **New** - Option to embed forms in responsive iframes.
* **New** - Instant subscription to add to newsletter before form submission.
* **Fix** - File upload breaking on some themes like avada.
* **Fix** - Remove deprecated code for PHP 7.4 compatibility.
* **Fix** - Some styling issues within gutenberg blocks with WP 5.5.

= 4.13.2 =

* **Fix** - Issue when MailSter and eForm would conflict over shared PHP dependency.
* **Note** - Update `guzzlehttp/psr7` dependency to latest.

= 4.13.1 =

* **Fix** - Issue with keypad on numeric text input breaking form.

= 4.13.0 =

* **New** - Add multiple products with WooCommerce integrations.
* **New** - Ability to send custom fields data for all supported third-party
* integrations.
* **New** - Ability to completely disable automatic scroll to invalid elements.
* **New** - Ability to add scroll offset for the same.
* **New** - Ability add custom color and bold appearance to correct answers. Now we support Radio, Checkbox, Dropdown & Thumbselect, Sortables and Single line text & multiline text.
* **New** - Stripe multistep validation. Before form submission, money will be kept on hold and will be captured only after successful submission.
* **Update** - Show summary table title, subtitle and description in separate row.
* **Update** - Show scores in summary table with proper alignment.
* **Fix** - File upload issue from mobile devices.
* **Fix** - jQuery compatibility issue. We are now ready for WordPress core update to jQuery 3.5.
* **Fix** - Remove deprecated Stripe JS APIs.
* **Fix** - Admin appearance issue with list tables.

= 4.12.2 =

* **Fix** - Issue with unwanted 51 demo forms installed during first installation.

= 4.12.1 =

* **Fix** - Issue with latest Yoast SEO Plugin (v14).
* **Fix** - Issue with guestblog copy paste content and cursor position.
* **Fix** - Typo in form shortcode help documentation.
* **Update** - JavaScript dependencies:

= 4.12.0 =

* **New** - Ability to disabled hot keys in form builder.
* **Fix** - Issue with Stripe and Coupon codes.

= 4.11.1 =

* **Fix** - MailSter integration.

= 4.11.0 =

* **Update** - Support Stripe SCA for onetime payment and payment retries.
* **Update** - Support Stripe SCA for subscription and payment retries.
* **Fix** - Delete/Cancel previous Subscription incase of payment failure.

= 4.10.0 =

* **New** - Option to add facebook app id on Standalone form pages.
* **New** - Option to add custom HTML on head and body section of standalone form pages.
* **Fix** - Composer dependency issues.
* **Fix** - iOS popup form width issues.
* **Fix** - Duplicate OpenGraph image option.

= 4.9.1 =

* **Update** - Make eForm work in conditions where some JS libraries are removed by themes or plugins.
* **Fix** - Regression of payment form from last release.
* **Fix** - DIVI theme compatibility.
* **Fix** - Style flash of form loader.

= 4.9.0 =

* **New** - Option to resubmit webhook during form update.
* **Update** - Improve eForm Login forms.
* **Update** - Improve performance and asset loading for standalone form pages.
* **Fix** - Form Trends Gutenberg block.
* **Fix** - Category and form filter under User Portal.
* **Fix** - Missing validation in Stripe Payment Gateway.
* **Fix** - Stripe subscription issue with multiple API keys.
* **Fix** - Multiple network request on same CSS file.

= 4.8.1 =

* **Fix** - If payment amount is 0, it will not trigger payment.
* **Fix** - Special characters breaking conditional logic on server side.
* **Update** - Today button will set today's date for datepicker.

= 4.8.0 =

* **New** - Option to collapse the form builder sidebar.
* **New** - Button like appearance for radio and checkboxes.
* **Update** - Form score now supports fractional values.
* **Fix** - Server side conditional logic for password elements.
* **Fix** - Limitation message not visible for logged-in users.
* **Fix** - Manual submit button not working on single page form.
* **Fix** - TinyMCE toolbar not visible under form builder settings.
* **Fix** - Typo in Text element settings.
* **Fix** - Signature element buttons.
* **Fix** - Column alignment in material style.
* **Fix** - File uploader upload button click area.

= 4.7.0 =

* **New** - Add option to hide default Form Submit toolbar.
* **New** - Convertkit integration with support for forms, sequences and tags.
* **New** - New Buttons element to make form progress, jump to page, submit etc.
* **New** - New preset datetime related variables for math element.
* **New** - Option to disable eForm admin side sandboxing. Fixes conflict with wp.com hosting.
* **New** - Option to hide password field values from summary table.
* **Update** - Always encrypt the values of password field.
* **Update** - Improve CSS build system by using autoprefixed SCSS.
* **Update** - Migrate WooCommerce integration to the new CRUD system. No more deprecation notice.
* **Fix** - Allow dash(-) in name validation.
* **Fix** - Math element fancy tag issue on single page forms.
* **Fix** - Piping tags not appearing for freetype elements in summary table.
* **Fix** - Use elements from freetype for registration username.

= 4.6.1 =

* **New** - eForm can now accept product variation id.
* **New** - You can select multiple order status to mark as paid.

= 4.6.0 =

* **New** - Compatibility with WordPress Gutenberg.
* **New** - Major improvement and complete rewrite of form builder UI and UX. Check our onboarding video.
* **New** - More styles for progress buttons.
* **New** - Onboarding tutorial when accessing Form Builder for the first time.
* **New** - Optionally show WooCommerce payment status in summary table and in admin side View all Submissions.
* **New** - Sandbox eForm admin pages from other plugins to avoid conflict.
* **New** - Various new form templates.
* **Update** - Sane default for a few form elements, including imageslider, thumbnail picker etc.
* **Update** - Skip WooCommerce if product_id is empty, creating room for conditional one-page or WooCommerce checkout.
* **Fix** - Center alignment for credit card container and stripe.
* **Fix** - Datetime element not working in live form view.
* **Fix** - Form Configuration being closed when uploading image.
* **Fix** - Issue with CONFIG and STYLE change refresh.
* **Fix** - Issues with Link Button form element.
* **Fix** - Regenerate form custom css when version upgrades.
* **Fix** - Set form width unit to pixel, when none is supplied.
* **Fix** - Show error message when reCaptcha is not being set up correctly.

= 4.5.2 =

* **Fix** - Empty feedback issue when sending on-behalf of user.
* **Fix** - Issue with security reCaptcha element.
* **Fix** - Issue with "Submission Limited" forms not appearing within form builder.

= 4.5.1 =

* **Update** - Improvement for custom style generation (with checksum).
* **Update** - Improve response time of live form builder.
* **Update** - Improve UX of live form builder.

= 4.5.0 =

* **New** - Live form builder interface.
* **New** - Boxy form themes.
* **New** - System and Custom fonts support.
* **New** - Global element alignments.
* **Update** - Accessibility for various form elements.
* **Update** - Update MyMail to Mailster.
* **Fix** - Math element appearance issue on small devices.
* **Fix** - Invalid hashtag breaking popup.
* **Fix** - Blank feedback email when send from user is true.
* **Fix** - Localize GetResponse PHP Library to avoid Fatal errors.
* **Fix** - Address box alignment issues.

= 4.2.1 =

* **New** - Support piping tags in pricing element header, attributes & footer.
* **Fix** - A case when hidden element won't restore value if placed inside a container with duplicate conditional logic.

= 4.2.0 =

* **New** - Integration with MailWizz Application.
* **New** - Offline Payment Gateway integration.
* **New** - Remove mcrypt dependency with graceful fallback for older instances.
* **Update** - Option for natively using Mobile Camera with File Uploader.
* **Update** - Update to new Stripe API for subscription.
* **Fix** - Full Name Element validation issue with "Everything" filter.
* **Fix** - Leaderboard and User Portal responsiveness issue.
* **Fix** - Math element fancy appearance issue with hidden label.
* **Fix** - Sortable list icons not appearing in summary table.

= 4.1.3 =

* **Fix** - JS bug with dependent datepicker element.
* **Fix** - CSS issue with datepicker div, not being hidden under some theme.
* **Fix** - Compatibility issue with easySubmission add-on.

= 4.1.2 =

* **Fix** - Blank value appearance issue for inline element
* **Fix** - Stripe Elements appearance issue for dark themes

= 4.1.1 =

* **New** - Add Support for MailPoet 3
* **Update** - Underline fill in the blank question in summary table
* **Update** - Highlight correct feedback questions according to settings
* **Update** - Make score and average score output configurable
* **Fix** - Auto shrink thumbnail elements on smaller screen

= 4.1.0 =

* **New** - Subscription Payment with Stripe.
* **New** - Option to highlight all positive scores in summary table.
* **New** - Option to reverse the order of smiley rating.
* **Fix** - Appearance issue with styled container without icons.
* **Fix** - Bug in iFrame/GPS inside hidden/collapsible containers.
* **Fix** - Missing prefix and suffix in feedback small inline appearance.
* **Fix** - Make Center/Vertical appearance work for payment element.
* **Fix** - Hidden Stripe Payment causing JS error.
* **Fix** - Primary fields not getting disabled after adding to the form.
* **Fix** - GetResponse Integration now updated with v3 API.

= 4.0.3 =

* **Fix** - Issue with Guest Blog element placeholder.
* **Fix** - Issue with Guest Blog Editor toolbar modals.
* **New** - Option to disable eForm activation notice.

= 4.0.2 =

* **Fix** - Issue with payment retry form.
* **Fix** - Issue with ZIP code field not accepting alphanumeric codes.

= 4.0.1 =

* **Fix** - Static database table naming issue with WordPress MS

= 4.0.0 =

* **New** - Authorize.net payment integration
* **New** - Auto Update Functionality
* **New** - Automatic score for feedback elements
* **New** - Estimation Slider interface for payment forms
* **New** - Input masking on freetype form elements
* **New** - Interactive form elements support for piping element values into labels
* **New** - OpenGraph & Twitter metadata in standalone form pages
* **New** - Option to change color of summary table icons
* **New** - Pricing Table Form Element
* **New** - Row index for checkbox, radio and thumnail numeric values in math element
* **New** - Zoom for statistics charts
* **Update** - Better colorpicker for Form Builder
* **Update** - Better looking payment forms
* **Update** - Better Signature Element
* **Update** - Implement changes according to new facebook API
* **Update** - Inline appearance for feedback small element
* **Update** - iziModal in popup forms with support for better manual popup
* **Update** - jQuery UI Sliders are now more responsive
* **Update** - Leaderboard shows rank and timer value
* **Update** - Select2 styling is now consistent with inputs
* **Fix** - Auto fix bad color codes in customizable material theme
* **Fix** - Auto Save Form Progress UI inconsistency
* **Fix** - Cookies based limitation not working under IE11
* **Fix** - Hidden mathematical element appearance issue
* **Fix** - Issue with file upload size
* **Fix** - Issue with sort by name in payment listing
* **Fix** - Issue with User Portal page logout redirect
* **Fix** - Placeholder issue in multiple grading settings

= 3.7.5 =
* Fixed: Typo in the default process title
* Fixed: Empty space in login form
* Fixed: IE11 bug which wouldn't let thumbnail pickers work properly

= 3.7.4 =
* Added: Clear button for datetime pickers
* Added: Changable default year range in datepicker dropdown
* Added: Option to hide datepicker icon
* Fixed: Issue with toggle element and conditional logic under a special edge case
* Fixed: Issue with repeatable element and floating number values

== Upgrade Notice ==

Plugin updates are automatic starting version 4.0.0. You will not loose any of your data. But you may want to backup following tables, just to be sure.

* `wp_fsq_form` - Holds all your forms.
* `wp_fsq_data` - Holds all your submission data.
* `wp_fsq_files` - Holds information about uploaded files.
* `wp_fsq_category` - Holds form categories.
* `wp_fsq_payment` - Holds all your payment and invoice related data.
