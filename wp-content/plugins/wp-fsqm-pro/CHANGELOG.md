From version 4.0.0 this file will only contain changelog for the current active
version according to [semver](http://semver.org/). For older changelog, kindly
see files like `CHANGELOG-vX.md` (will be available after releasing version 5.0).

## Version 4.19.1

> Bug fixes.

- **Fix** - Compatibility with modern SMTP/mail plugins like WP Mail SMTP, FluentSMPT etc.
- **Fix** - Dynamic page number not appearing in the summary table and in single layout page mode.
- **Fix** - Escape attributes in listing tables.

## Version 4.19.0

> Form element improvements and Security Update

- **Update** - Country list updated to the latest.
- **New** - New option for native select and placeholder.
- **New** - Add dynamic numbering to the tabs.
- **New** - Made the search in select smarter and added option to disable it.
- **Note** - Internal build dependency cleanup.
- **Fix** - Security issue in the address element.

## Version 4.18.0

> jQuery UI 1.13 compatibility fix.

-  **Fix** - jQuery UI 1.13 compatibility fix.

## Version 4.17.1

> Security update to the file uploader.

-   **New** - Automatically add random characters before filenames which are being uploaded.

## Version 4.17.0

> Integration and Summary Table Improvements.

-   **New** - Accept format string and custom value in integration metadata.
-   **New** - Add format strings for page based scores.
-   **New** - Add new metadata configuration in webhook/zapier.
-   **New** - Add option to show additional rows with custom values in the summary details table.

## Version 4.16.0

> Elementor and File upload improvements.

-   **New** - Elementor integration for forms and popup.
-   **New** - Add new sendy.co API in integration configuration.
-   **New** - reCaptcha integration for securing file uploads.
-   **Fix** - iFrame embed issues in older iOS devices.

## Version 4.15.1 [07-16-2021]

> Bug fixes.

-   **Fix** - PHP8 issue on some quiz forms.
-   **Fix** - Node sass compilation issue.

## Version 4.15.0 [02-26-2021]

> PHP 8 support & general improvements.

-   **New** - Support PHP8.
-   **New** - Add delimiter option in the WebHook integration.
-   **New** - Option to send user notification email right away with WooCommerce integration.
-   **Update** - Automatically pass URL parameters to iframe embedded forms with shortcodes, blocks and widgets.
-   **Fix** - Use divs instead of h3 tags for error messages for SEO.
-   **Fix** - PDFs and Email not respecting table color config.
-   **Fix** - Block UI not working for User Portal block (Gutenberg).
-   **Fix** - Length type conditional logic is incorrect for numeric input.

## Version 4.14.2 [10-07-2020]

> Improve iFrame embeds on iOS devices.

-   **New** - Add iframe embed option to eForm Widget.
-   **Fix** - iFrame embedded form height and width issue on iOS devices.
-   **Fix** - Conflict between popup form and iFrame embedded form.
-   **Fix** - iFrame embed width inside widgets and pages.
-   **Fix** - Fancy math element not hiding properly on conditional logic.
-   **Fix** - Use div in error messages instead of heading tags (SEO).

## Version 4.14.1 [08-20-2020]

-   **Fix** - Email template source not being deployed to production.
-   **Fix** - Issue in form builder where empty form causes live view to fail.

## Version 4.14.0 [08-19-2020]

> Email templates & instant integration.

-   **New** - Email templates to make changing email appearance easier.
-   **New** - Option to embed forms in responsive iframes.
-   **New** - Instant subscription to add to newsletter before form submission.
-   **Fix** - File upload breaking on some themes like avada.
-   **Fix** - Remove deprecated code for PHP 7.4 compatibility.
-   **Fix** - Some styling issues within gutenberg blocks with WP 5.5.

## Version 4.13.2 [07-05-2020]

> MailSter Gmail compatibility fix.

-   **Fix** - Issue when MailSter and eForm would conflict over shared PHP dependency.
-   **Note** - Update `guzzlehttp/psr7` dependency to latest.

---

## Version 4.13.1 [07-02-2020]

> Bug fixes.

-   **Fix** - Issue with keypad on numeric text input breaking form.

---

## Version 4.13.0 [07-01-2020]

> Improve third-party, WooCommerce & Stripe integrations.

-   **New** - Add multiple products with WooCommerce integrations.
-   **New** - Ability to send custom fields data for all supported third-party
    integrations.
-   **New** - Ability to completely disable automatic scroll to invalid elements.
-   **New** - Ability to add scroll offset for the same.
-   **New** - Ability add custom color and bold appearance to correct answers. Now we support
    -   Radio, Checkbox, Dropdown & Thumbselect.
    -   Sortables.
    -   Single line text & multiline text.
-   **New** - Stripe multistep validation. Before form submission, money will be kept on hold and will be captured only after successful submission.
-   **Update** - Show summary table title, subtitle and description in separate row.
-   **Update** - Show scores in summary table with proper alignment.
-   **Fix** - File upload issue from mobile devices.
-   **Fix** - jQuery compatibility issue. We are now ready for WordPress core update to jQuery 3.5.
-   **Fix** - Remove deprecated Stripe JS APIs.
-   **Fix** - Admin appearance issue with list tables.

---

## Version 4.12.2 [05-01-2020]

> Remove unwanted demo forms during first install.

-   **Fix** - Issue with unwanted 51 demo forms installed during first installation.

---

## Version 4.12.1 [05-01-2020]

> Yoast SEO 14 compatibility issue and guestblog bug fix.

-   **Fix** - Issue with latest Yoast SEO Plugin (v14).
-   **Fix** - Issue with guestblog copy paste content and cursor position.
-   **Fix** - Typo in form shortcode help documentation.
-   **Update** - JavaScript dependencies:
    -   `blueimp-canvas-to-blob`: `3.22.0`
    -   `blueimp-file-upload`: `9.34.0`
    -   `blueimp-gallery`: `2.38.0`
    -   `blueimp-load-image`: `5.1.0`
    -   `blueimp-tmpl`: `3.14.0`
    -   `chart.js`: `2.9.3`
    -   `datatables.net`: `1.10.20`
    -   `expr-eval`: `1.2.3`
    -   `jSignature`: `2.1.3`
    -   `jquery-minicolors`: `2.3.4`
    -   `jquery-typewatch`: `3.0.1`
    -   `js-cookie`: `2.2.1`
    -   `keyboard`: `1.30.1`
    -   `select2`: `4.0.13`
    -   `tooltipster`: `4.2.7`
    -   `trumbowyg`: `2.21.0`

---

## Version 4.12.0 [11-18-2019]

> Fix Stripe and Coupon code issue and minor form builder improvement.

-   **New** - Ability to disabled hot keys in form builder.
-   **Fix** - Issue with Stripe and Coupon codes.

---

## Version 4.11.1 [10-18-2019]

> Fix MailSter Integration

-   **Fix** - MailSter integration.

---

## Version 4.11.0 [09-18-2019]

> Stripe SCA support.

-   **Update** - Support Stripe SCA for onetime payment and payment retries.
-   **Update** - Support Stripe SCA for subscription and payment retries.
-   **Fix** - Delete/Cancel previous Subscription incase of payment failure.

---

## Version 4.10.0 [05-20-2019]

> iOS and OpenGraph improvements.

-   **New** - Option to add facebook app id on Standalone form pages.
-   **New** - Option to add custom HTML on head and body section of standalone form pages.
-   **Fix** - Composer dependency issues.
-   **Fix** - iOS popup form width issues.
-   **Fix** - Duplicate OpenGraph image option.

---

## Version 4.9.1 [04-17-2019]

> General bug fixes.

-   **Update** - Make eForm work in conditions where some JS libraries are removed by themes or plugins.
-   **Fix** - Regression of payment form from last release.
-   **Fix** - DIVI theme compatibility.
-   **Fix** - Style flash of form loader.

---

## Version 4.9.0 [04-07-2019]

> Improvements for Gutenberg blocks and general bug fixes.

-   **New** - Option to resubmit webhook during form update.
-   **Update** - Improve eForm Login forms.
-   **Update** - Improve performance and asset loading for standalone form pages.
-   **Fix** - Form Trends Gutenberg block.
-   **Fix** - Category and form filter under User Portal.
-   **Fix** - Missing validation in Stripe Payment Gateway.
-   **Fix** - Stripe subscription issue with multiple API keys.
-   **Fix** - Multiple network request on same CSS file.

---

## Version 4.8.1 [02-05-2019]

> Datepicker improvement and general bug fixes.

-   **Fix** - If payment amount is 0, it will not trigger payment.
-   **Fix** - Special characters breaking conditional logic on server side.
-   **Update** - Today button will set today's date for datepicker.

---

## Version 4.8.0 [01-08-2019]

> Form builder & MCQ improvements.

-   **New** - Option to collapse the form builder sidebar.
-   **New** - Button like appearance for radio and checkboxes.
-   **Update** - Form score now supports fractional values.
-   **Fix** - Server side conditional logic for password elements.
-   **Fix** - Limitation message not visible for logged-in users.
-   **Fix** - Manual submit button not working on single page form.
-   **Fix** - TinyMCE toolbar not visible under form builder settings.
-   **Fix** - Typo in Text element settings.
-   **Fix** - Signature element buttons.
-   **Fix** - Column alignment in material style.
-   **Fix** - File uploader upload button click area.

---

## Version 4.7.0 [09-13-2018]

> Convertkit Integration, Migrate to WooCommerce CRUD API & manual form buttons.

-   **New** - Add option to hide default Form Submit toolbar.
-   **New** - Convertkit integration with support for forms, sequences and tags.
-   **New** - New Buttons element to make form progress, jump to page, submit etc.
-   **New** - New preset datetime related variables for math element.
-   **New** - Option to disable eForm admin side sandboxing. Fixes conflict with wp.com hosting.
-   **New** - Option to hide password field values from summary table.
-   **Update** - Always encrypt the values of password field.
-   **Update** - Improve CSS build system by using autoprefixed SCSS.
-   **Update** - Migrate WooCommerce integration to the new CRUD system. No more deprecation notice.
-   **Fix** - Allow dash(-) in name validation.
-   **Fix** - Math element fancy tag issue on single page forms.
-   **Fix** - Piping tags not appearing for freetype elements in summary table.
-   **Fix** - Use elements from freetype for registration username.

---

## Version 4.6.1 [09-03-2018]

> WooCommerce Integration Improvement.

-   **New** - eForm can now accept product variation id.
-   **New** - You can select multiple order status to mark as paid.

---

## Version 4.6.0 [09-02-2018]

> Form Builder UX improvement.

-   **New** - Compatibility with WordPress Gutenberg.
-   **New** - Major improvement and complete rewrite of form builder UI and UX. Check our onboarding video.
-   **New** - More styles for progress buttons.
-   **New** - Onboarding tutorial when accessing Form Builder for the first time.
-   **New** - Optionally show WooCommerce payment status in summary table and in admin side View all Submissions.
-   **New** - Sandbox eForm admin pages from other plugins to avoid conflict.
-   **New** - Various new form templates.
-   **Update** - Sane default for a few form elements, including imageslider, thumbnail picker etc.
-   **Update** - Skip WooCommerce if product_id is empty, creating room for conditional one-page or WooCommerce checkout.
-   **Fix** - Center alignment for credit card container and stripe.
-   **Fix** - Datetime element not working in live form view.
-   **Fix** - Form Configuration being closed when uploading image.
-   **Fix** - Issue with CONFIG and STYLE change refresh.
-   **Fix** - Issues with Link Button form element.
-   **Fix** - Regenerate form custom css when version upgrades.
-   **Fix** - Set form width unit to pixel, when none is supplied.
-   **Fix** - Show error message when reCaptcha is not being set up correctly.

---

## Version 4.5.2 [07-30-2018]

> Bug fixes.

-   **Fix** - Empty feedback issue when sending on-behalf of user.
-   **Fix** - Issue with security reCaptcha element.
-   **Fix** - Issue with "Submission Limited" forms not appearing within form builder.

---

## Version 4.5.1 [07-27-2018]

> Live form builder improvements.

-   **Update** - Improvement for custom style generation (with checksum).
-   **Update** - Improve response time of live form builder.
-   **Update** - Improve UX of live form builder.

---

## Version 4.5 [07-26-2018]

> Form Builder enhancement & new boxy theme.

### Changes

-   **New** - Live form builder interface.
-   **New** - Boxy form themes.
-   **New** - System and Custom fonts support.
-   **New** - Global element alignments.
-   **Update** - Accessibility for various form elements.
-   **Update** - Update MyMail to Mailster.
-   **Fix** - Math element appearance issue on small devices.
-   **Fix** - Invalid hashtag breaking popup.
-   **Fix** - Blank feedback email when send from user is true.
-   **Fix** - Localize GetResponse PHP Library to avoid Fatal errors.
-   **Fix** - Address box alignment issues.

---

## Version 4.2.1

> Bug fixes and pricing table enhancement.

### Changes

-   **New** - Support piping tags in pricing element header, attributes & footer.
-   **Fix** - A case when hidden element won't restore value if placed inside a container with duplicate conditional logic.

---

## Version 4.2.0

> Bug Fixes, Offline Payment, Mobile Optimization & Moving forward from mcrypt

### Changes

-   **New** - Integration with MailWizz Application.
-   **New** - Offline Payment Gateway integration.
-   **New** - Remove mcrypt dependency with graceful fallback for older instances.
-   **Update** - Option for natively using Mobile Camera with File Uploader.
-   **Update** - Update to new Stripe API for subscription.
-   **Fix** - Full Name Element validation issue with "Everything" filter.
-   **Fix** - Leaderboard and User Portal responsiveness issue.
-   **Fix** - Math element fancy appearance issue with hidden label.
-   **Fix** - Sortable list icons not appearing in summary table.

---

## Version 4.1.3

> Bug fixes and compatibility with easySubmission Add-on

### Changes

-   **Fix** - JS bug with dependent datepicker element.
-   **Fix** - CSS issue with datepicker div, not being hidden under some theme.
-   **Fix** - Compatibility issue with easySubmission add-on.

---

## Version 4.1.2

> Bug fixes

### Changes

-   **Fix** - Blank value appearance issue for inline element
-   **Fix** - Stripe Elements appearance issue for dark themes

---

## Version 4.1.1

> Bug fixes and feature enhancements

### Changes

-   **New** - Add Support for MailPoet 3
-   **Update** - Underline fill in the blank question in summary table
-   **Update** - Highlight correct feedback questions according to settings
-   **Update** - Make score and average score output configurable
-   **Fix** - Auto shrink thumbnail elements on smaller screen

---

## Version 4.1.0

> Implement Subscription Payments

### Changes

-   **New** - Subscription Payment with Stripe.
-   **New** - Option to highlight all positive scores in summary table.
-   **New** - Option to reverse the order of smiley rating.
-   **Fix** - Appearance issue with styled container without icons.
-   **Fix** - Bug in iFrame/GPS inside hidden/collapsible containers.
-   **Fix** - Missing prefix and suffix in feedback small inline appearance.
-   **Fix** - Make Center/Vertical appearance work for payment element.
-   **Fix** - Hidden Stripe Payment causing JS error.
-   **Fix** - Primary fields not getting disabled after adding to the form.
-   **Fix** - GetResponse Integration now updated with v3 API.

### Under the hood

-   **Update** - Improve Auto-Update and error reporting functionality.

---

## Version 4.0.3

> Fix Guest Blogging Element appearance issues.

### Changes

-   **Fix** - Issue with Guest Blog element placeholder.
-   **Fix** - Issue with Guest Blog Editor toolbar modals.

### Under the hood

-   **New** - Option to disable eForm activation notice.

---

## Version 4.0.2

> Fix Payment and ZIP code related issues.

### Changes

-   **Fix** - Issue with payment retry form.
-   **Fix** - Issue with ZIP code field not accepting alphanumeric codes.

### Under the hood

-   **Update** - Move `hiddens` method to `IPT_Plugin_UIF_Base` to expose to all classes.
-   **Update** - Updated Composer dependencies to latest (`Stripe`).

---

## Version 4.0.1

> Quick patch for WordPress MultiSite.

### Changes

-   **Fix** - Static database table naming issue with WordPress MS

---

## Version 4.0.0

> Major code refactor to introduce modern workflow and features focused on payment
> and cost estimation.

Many breaking API changes. Check the [DevOps](https://wpq-develop.wpquark.xyz/wp-fsqm-pro/)
page for more information.

### Changes

-   **New** - Authorize.net payment integration
-   **New** - Auto Update Functionality
-   **New** - Automatic score for feedback elements
-   **New** - Estimation Slider interface for payment forms
-   **New** - Input masking on freetype form elements
-   **New** - Interactive form elements support for piping element values into labels
-   **New** - OpenGraph & Twitter metadata in standalone form pages
-   **New** - Option to change color of summary table icons
-   **New** - Pricing Table Form Element
-   **New** - Row index for checkbox, radio and thumnail numeric values in math element
-   **New** - Zoom for statistics charts
-   **Update** - Better colorpicker for Form Builder
-   **Update** - Better looking payment forms
-   **Update** - Better Signature Element
-   **Update** - Implement changes according to new facebook API
-   **Update** - Inline appearance for feedback small element
-   **Update** - iziModal in popup forms with support for better manual popup
-   **Update** - jQuery UI Sliders are now more responsive
-   **Update** - Leaderboard shows rank and timer value
-   **Update** - Select2 styling is now consistent with inputs
-   **Fix** - Auto fix bad color codes in customizable material theme
-   **Fix** - Auto Save Form Progress UI inconsistency
-   **Fix** - Cookies based limitation not working under IE11
-   **Fix** - Hidden mathematical element appearance issue
-   **Fix** - Issue with file upload size
-   **Fix** - Issue with sort by name in payment listing
-   **Fix** - Issue with User Portal page logout redirect
-   **Fix** - Placeholder issue in multiple grading settings

### Under the hood

-   **New** - Adaptation to modern workflow with modular approach
-   **New** - Grunt based CI/CD with support for automatic plugin updates for clients
-   **New** - Payment module refactoring
-   **New** - PHPUnit testing for a better continuous integration
-   **New** - UI class refactoring
-   **New** - Use bower to manage front-end dependencies
-   **New** - Use composer to manage PHP dependencies
-   **New** - Use NPM to manage dev dependencies
