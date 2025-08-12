<?php
/**
 * A unique identifier is defined to store the options in the database and reference them from the theme.
 * By default it uses the theme name, in lowercase and without spaces, but this can be changed if needed.
 * If the identifier changes, it'll appear as if the options have been reset.
 */

function optionsframework_option_name() {

	// This gets the theme name from the stylesheet
	$themename = wp_get_theme();
	$themename = preg_replace("/\W/", "_", strtolower($themename) );

	$optionsframework_settings = get_option( 'optionsframework' );
	$optionsframework_settings['id'] = $themename;
	update_option( 'optionsframework', $optionsframework_settings );
}

/**
 * Defines an array of options that will be used to generate the settings page and be saved in the database.
 * When creating the 'id' fields, make sure to use all lowercase and no spaces.
 */

function optionsframework_options() {

	$options = array();
	
	//Basic Settings
	
	$options[] = array(
		'name' => __('Basic Settings', 'pluto'),
		'type' => 'heading');
		
	$options[] = array(
		'name' => __('Site Logo', 'pluto'),
		'desc' => __('Size of Logo should be Exactly 360x125px For Best Results. Leave Blank to use text Heading.', 'pluto'),
		'id' => 'logo',
		'class' => '',
		'type' => 'upload');		
		 
		
	$options[] = array(
		'name' => __('Copyright Text', 'pluto'),
		'desc' => __('Some Text regarding copyright of your site, you would like to display in the footer.', 'pluto'),
		'id' => 'footertext2',
		'std' => '',
		'type' => 'text');
		
	$options[] = array(
		'name' => __('Custom CSS', 'pluto'),
		'desc' => __('Some Custom CSS for your site.', 'pluto'),
		'id' => 'customcss1',
		'std' => '',
		'type' => 'textarea');	

	$options[] = array(
		'name' => __('Theme Credits', 'pluto'),
		'desc' => __('Check this if you want to you do not want to give us credit in your site footer.', 'pluto'),
		'id' => 'credit1',
		'std' => '0',
		'type' => 'checkbox');		
	
	//SLIDER SETTINGS

	$options[] = array(
		'name' => __('Slider Settings', 'pluto'),
		'type' => 'heading');

	$options[] = array(
		'name' => __('Enable Slider', 'pluto'),
		'desc' => __('Check this to Enable Slider.', 'pluto'),
		'id' => 'slider_enabled',
		'type' => 'checkbox',
		'std' => '0' );
	 
	$options[] = array(
		'name' => __('Using the Slider', 'pluto'),
		'desc' => __('This Slider supports upto 5 Images. To show only 3 Slides in the slider, upload only 3 images. Leave the rest Blank. For best results, upload images of width more than 1100px', 'pluto'),
		'type' => 'info');

	$options[] = array(
		'name' => __('Slider Image 1', 'pluto'),
		'desc' => __('First Slide', 'pluto'),
		'id' => 'slide1',
		'class' => '',
		'type' => 'upload');
	
	$options[] = array(
		'desc' => __('Title', 'pluto'),
		'id' => 'slidetitle1',
		'std' => '',
		'type' => 'text');	
		
	$options[] = array(
		'desc' => __('Url', 'pluto'),
		'id' => 'slideurl1',
		'std' => '',
		'type' => 'text');		
	
	$options[] = array(
		'name' => __('Slider Image 2', 'pluto'),
		'desc' => __('Second Slide', 'pluto'),
		'class' => '',
		'id' => 'slide2',
		'type' => 'upload');
	
	$options[] = array(
		'desc' => __('Title', 'pluto'),
		'id' => 'slidetitle2',
		'std' => '',
		'type' => 'text');	
		
	$options[] = array(
		'desc' => __('Url', 'pluto'),
		'id' => 'slideurl2',
		'std' => '',
		'type' => 'text');	
		
	$options[] = array(
		'name' => __('Slider Image 3', 'pluto'),
		'desc' => __('Third Slide', 'pluto'),
		'id' => 'slide3',
		'class' => '',
		'type' => 'upload');	
	
	$options[] = array(
		'desc' => __('Title', 'pluto'),
		'id' => 'slidetitle3',
		'std' => '',
		'type' => 'text');	
		
	$options[] = array(
		'desc' => __('Url', 'pluto'),
		'id' => 'slideurl3',
		'std' => '',
		'type' => 'text');		
	
	$options[] = array(
		'name' => __('Slider Image 4', 'pluto'),
		'desc' => __('Fourth Slide', 'pluto'),
		'id' => 'slide4',
		'class' => '',
		'type' => 'upload');	
		
	$options[] = array(
		'desc' => __('Title', 'pluto'),
		'id' => 'slidetitle4',
		'std' => '',
		'type' => 'text');	
		
	$options[] = array(
		'desc' => __('Url', 'pluto'),
		'id' => 'slideurl4',
		'std' => '',
		'type' => 'text');		
	
	$options[] = array(
		'name' => __('Slider Image 5', 'pluto'),
		'desc' => __('Fifth Slide', 'pluto'),
		'id' => 'slide5',
		'class' => '',
		'type' => 'upload');	
		
	$options[] = array(
		'desc' => __('Title', 'pluto'),
		'id' => 'slidetitle5',
		'std' => '',
		'type' => 'text');	
		
	$options[] = array(
		'desc' => __('Url', 'pluto'),
		'id' => 'slideurl5',
		'std' => '',
		'type' => 'text');	
			
	//Social Settings
	
	$options[] = array(
	'name' => __('Social Settings', 'pluto'),
	'type' => 'heading');

	$options[] = array(
		'name' => __('Facebook', 'pluto'),
		'desc' => __('Facebook Profile or Page URL i.e. http://facebook.com/username/ ', 'pluto'),
		'id' => 'facebook',
		'std' => '',
		'class' => 'mini',
		'type' => 'text');
	
	$options[] = array(
		'name' => __('Twitter', 'pluto'),
		'desc' => __('Twitter profile url, including "http://"', 'pluto'),
		'id' => 'twitter',
		'std' => '',
		'class' => 'mini',
		'type' => 'text');
	
	$options[] = array(
		'name' => __('Google Plus', 'pluto'),
		'desc' => __('Google Plus profile url, including "http://"', 'pluto'),
		'id' => 'google',
		'std' => '',
		'class' => 'mini',
		'type' => 'text');
		
	$options[] = array(
		'name' => __('Pinterest', 'pluto'),
		'desc' => __('Pinterest profile url, including "http://"', 'pluto'),
		'id' => 'pinterest',
		'std' => '',
		'class' => 'mini',
		'type' => 'text');	
		
	$options[] = array(
		'name' => __('Linked In', 'pluto'),
		'desc' => __('Linkedin profile url, including "http://"', 'pluto'),
		'id' => 'linkedin',
		'std' => '',
		'class' => 'mini',
		'type' => 'text');
		
	$options[] = array(
		'name' => __('Instagram', 'pluto'),
		'desc' => __('Instagram profile url, including "http://"', 'pluto'),
		'id' => 'instagram',
		'std' => '',
		'class' => 'mini',
		'type' => 'text');		
		
	$options[] = array(
		'name' => __('YouTube', 'pluto'),
		'desc' => __('YouTube Channel url, including "http://"', 'pluto'),
		'id' => 'youtube',
		'std' => '',
		'class' => 'mini',
		'type' => 'text');	
		
	$options[] = array(
		'name' => __('Feeburner', 'pluto'),
		'desc' => __('URL for your RSS Feeds', 'pluto'),
		'id' => 'feedburner',
		'std' => '',
		'class' => 'mini',
		'type' => 'text');		
	
	  
	return $options;
}