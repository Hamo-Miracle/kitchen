<?php
/**
* Plugin Name: Advanced Taxonomy Terms Order
* Plugin URI: https://www.nsp-code.com
* Description: Re-Order Categories and all custom taxonomies terms using a Drag and Drop Sortable javascript capability.
* Version: 3.7.8
* Author: Nsp Code
* Author URI: https://www.nsp-code.com
* Author Email: contact@nsp-code.com
*/


    define('ATTO_PATH',    plugin_dir_path(__FILE__));
    define('ATTO_URL',     str_replace(array('https:', 'http:'), "", plugins_url('', __FILE__)));

    define('ATTO_VERSION',              '3.7.8');
    define('ATTO_PRODUCT_ID',           'ATTO');
    define('ATTO_INSTANCE',             preg_replace('/:[0-9]+/', '', str_replace(array ("https://" , "http://"), "", trim(network_site_url(), '/'))));
    define('ATTO_UPDATE_API_URL',       'https://api.nsp-code.com/index.php');     

    //load language files
    add_action( 'plugins_loaded', 'atto_load_textdomain'); 
    function atto_load_textdomain() 
        {            
            $locale             =   get_locale();
            $plugin_textdomain  =   'atto';

            // Check if the specific translation file exists
            if (file_exists( ATTO_PATH . "/languages/$plugin_textdomain-$locale.mo")) {
                load_textdomain( $plugin_textdomain, ATTO_PATH . "/languages/$plugin_textdomain-$locale.mo" );
            } else {
                $general_locale = substr($locale, 0, 2);
                $general_mofile = ATTO_PATH . "/languages/$plugin_textdomain-$general_locale.mo";
                
                if (file_exists($general_mofile))
                    load_textdomain( $plugin_textdomain, $general_mofile );
            }
        }
    
    include (ATTO_PATH . '/include/class.atto.functions.php'); 
    include (ATTO_PATH . '/include/class.atto.php');
    include (ATTO_PATH . '/include/class.atto.rest.php');
    include (ATTO_PATH . '/include/class.atto.compatability.php');
    
    include (ATTO_PATH . '/include/class.atto.licence.php');
    include (ATTO_PATH . '/include/class.atto.plugin-updater.php');
    
    register_deactivation_hook(__FILE__, 'ATTO_deactivated');
    register_activation_hook(__FILE__, 'ATTO_activated');

    function ATTO_activated($network_wide) 
        {
            global $wpdb;
                 
            // check if it is a network activation
            if ( $network_wide ) 
                {                   
                    // Get all blog ids
                    $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
                    foreach ($blogids as $blog_id) 
                        {
                            switch_to_blog($blog_id);
                            ATTO_functions::plugin_activated_actions();
                            restore_current_blog();
                        }
                    
                    return;
                }
                else
                ATTO_functions::plugin_activated_actions();
        }
        
    add_action( 'wp_initialize_site', 'ATTO_wp_initialize_site', 99, 2 );       
    function ATTO_wp_initialize_site( $blog_data, $args )
        {
            global $wpdb;
         
            if (is_plugin_active_for_network('advanced-taxonomy-terms-order/taxonomy-order.php')) 
                {                    
                    switch_to_blog( $blog_data->blog_id );
                    ATTO_functions::plugin_activated_actions();
                    restore_current_blog();
                }
        }
        
    function ATTO_deactivated() 
        {
            
        }
        
        
    add_filter('plugins_loaded', 'ATTO_plugins_loaded');
    function ATTO_plugins_loaded()
        {
            
            new ATTO();

        }
        
                
?>