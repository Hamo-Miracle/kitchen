The email templates are plain PHP template files. They do not use any additional
templating engine in favor of inlining PHP and HTML.

## General Email Template Variables

The following are the variables used in all of the template files.

```php
$data = [
	'style' => [], // style compatible with settings['email_template']
];
```

## [`html-body.php`] Email Template Variables

The following are the variables used in `html-body.php` template file.

```php
$data = [
	'title' => 'Email Title',
	'body' => 'Email Body',
	'style' => [], // style compatible with settings['email_template']
];
```

## Modification

Copy this directory to `wp-content/wpeform-email-templates` and eForm will use
the files from the new location instead of the ones supplied by the plugin. This
will override all of the emails for all of the forms.

-   `wp-content/wpeform-email-templates/html-body.php` - For overall HTML template.
-   `wp-content/wpeform-email-templates/user-email.php` - Form User email.
-   `wp-content/wpeform-email-templates/user-payment.php` - For Payment email.
-   `wp-content/wpeform-email-templates/admin-email.php` - For Admin notification.

If you want to modify email templates for a particular form, note down the ID
and create the following files.

-   `wp-content/wpeform-email-templates/<<formID>>-html-body.php` - For overall HTML template.
-   `wp-content/wpeform-email-templates/<<formID>>-user-email.php` - Form User email.
-   `wp-content/wpeform-email-templates/<<formID>>-user-payment.php` - For Payment email.
-   `wp-content/wpeform-email-templates/<<formID>>-admin-email.php` - For Admin notification.

So, if the form Id is 33, then the following files need to be present for eForm
to use it.

-   `wp-content/wpeform-email-templates/33-html-body.php` - For overall HTML template.
-   `wp-content/wpeform-email-templates/33-user-email.php` - Form User email.
-   `wp-content/wpeform-email-templates/33-user-payment.php` - For Payment email.
-   `wp-content/wpeform-email-templates/33-admin-email.php` - For Admin notification.
