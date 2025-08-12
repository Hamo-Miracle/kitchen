<?php
    
    if ( ! defined( 'ABSPATH' ) ) { exit;}
    
    class ATTO_functions
        {
               
            /**
            * Return default plugin options
            * 
            */
            static public function get_settings()
                {
                    
                    $settings = get_option('tto_options'); 
                    
                    $defaults   = array (
                                            'show_reorder_interfaces'           =>  array(),
                                            
                                            'capability'                        =>  'manage_options',
                                            'autosort'                          =>  '1',
                                            'adminsort'                         =>  '1',
                                            'allow_reorder_default_interfaces'  =>  array(),
                                            
                                            'wpml_sort_synchronization'         =>  '0'
                                        );
                    $settings          = wp_parse_args( $settings, $defaults );
                    
                    return $settings;   
                    
                }
                
            
            static public function update_settings( $settings )
                {
                    
                    $settings = update_option('tto_options', $settings); 
                        
                }
                
            /**
            * 
            * Return UserLevel
            * 
            */
            static public function userdata_get_user_level($return_as_numeric = FALSE)
                {
                    global $userdata;
                    
                    $user_level = '';
                    for ($i=10; $i >= 0;$i--)
                        {
                            if (current_user_can('level_' . $i) === TRUE)
                                {
                                    $user_level = $i;
                                    if ($return_as_numeric === FALSE)
                                        $user_level = 'level_'.$i; 
                                    break;
                                }    
                        }        
                    return ($user_level);
                }
                
                
            /**
            * Return hierarchy
            *     
            * @param mixed $taxonomy
            */
            static public function get_term_hierarchy($taxonomy)
                {
                    if ( !is_taxonomy_hierarchical($taxonomy) )
                        return array();
                    
                    global $wpdb;
                        
                    //$children = get_option("{$taxonomy}_children");
                    //return $children;
                    
                    //retrieve all terms of this taxonomy and set a hierarchy array data
                    $sql_query  =   "SELECT t.term_id, tt.parent, tt.count, tt.taxonomy FROM ".  $wpdb->terms ." AS t 
                                        INNER JOIN ".  $wpdb->term_taxonomy ." AS tt ON t.term_id = tt.term_id 
                                        WHERE tt.taxonomy IN ('"    .   $taxonomy   ."') 
                                        ORDER BY t.term_order ASC";
                    $results            =   $wpdb->get_results($sql_query);

                    $children = array();
                    
                    if(count($results)  >   0)
                    foreach($results    as  $result)
                        {
                            if($result->parent  <   1)
                                continue;
                                
                            $children[$result->parent][]    =   $result->term_id;
                            
                        }
               
                    return $children;   
                    
                }
                
                
                
            /**
            * Disable the free plugin if active
            * 
            */
            static public function atto_disable_category_terms_order()
                {
                    if ( is_network_admin() ) 
                        {
                            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                            if ( is_plugin_active_for_network( 'taxonomy-terms-order/taxonomy-terms-order.php' ) ) 
                                {
                                    deactivate_plugins( 'taxonomy-terms-order/taxonomy-terms-order.php' );
                                    
                                    $url_scheme =   is_ssl() ?  'https://'  :   'http://';
                                    
                                    //reload the page
                                    $current_url = set_url_scheme( $url_scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ); 
                                    wp_redirect($current_url);
                                    die();
                                }     
                            
                        }
                        else
                        {
                            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                            if ( is_plugin_active( 'taxonomy-terms-order/taxonomy-terms-order.php' ) ) 
                                {
                                    deactivate_plugins( 'taxonomy-terms-order/taxonomy-terms-order.php' );
                                    
                                    $url_scheme =   is_ssl() ?  'https://'  :   'http://';
                                    
                                    //reload the page
                                    $current_url = set_url_scheme( $url_scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ); 
                                    wp_redirect($current_url);
                                    die();
                                } 
                        }   
                }
                
                
                
            static public function plugin_activated_actions()
                {
                    global $wpdb;
                         
                    //make sure the vars are set as default
                    $options = ATTO_functions::get_settings();                
                    update_option('tto_options', $options);
                    
                    //try to create the term_order column in case is not created
                    $query = "SHOW COLUMNS FROM `". $wpdb->terms ."` 
                                LIKE 'term_order'";
                    $result = $wpdb->get_row($query);
                    if(!$result) 
                        {
                            $query = "ALTER TABLE `". $wpdb->terms ."` 
                                        ADD `term_order` INT NULL DEFAULT '0'";
                            $result = $wpdb->get_results($query);   
                        }            
                }
                
                
            static function get_available_menu_locations()
                {
                    global $menu;
                                                            
                    $location_menus = array();
                    
                    $allow_areas =   array(
                                            'edit.php',
                                            'upload.php'
                                            );
                    
                    //filter the menus
                    foreach($menu as $key   =>  $menu_item)
                        {
                            foreach($allow_areas as $allow_area)
                                {
                                    if(strpos($menu_item[2], $allow_area) === 0)   
                                        $location_menus[]   =   $menu_item;   
                                }
                        }
                    
                    $locations  =   array();    
                    foreach($location_menus as $location_menus_item)
                        {
                            $menu_title     =   $location_menus_item[0];
                            $tags           =   array( 'p', 'span');
                            $menu_title     =   preg_replace( '#<(' . implode( '|', $tags) . ')[^>]+>.*?</\1>#s', '', $menu_title);
                            $menu_title     =   trim(strip_tags($menu_title));
                            
                            $post_type      =   '';
                            if(strpos($location_menus_item[2], "?")    === FALSE)
                                {
                                    if ( $location_menus_item[2]    ==  'upload.php' )   
                                        $post_type      =   'attachment';
                                        else
                                        $post_type      =   'post';
                                }
                                else
                                {
                                    $link_query         =   parse_url($location_menus_item[2]);
                                    parse_str($link_query['query'], $output);
                                    
                                    $post_type  =   $output['post_type'];
                                }
                            $locations[$location_menus_item[2]] =   array(
                                                                                            'slug'      =>  sanitize_title($location_menus_item[2]),  
                                                                                            'name'      =>  $menu_title,
                                                                                            'post_type' =>  $post_type 
                                                                                            );
                        }

                    return $locations;
                }
                 
                
        }

        
        
    
    
?>