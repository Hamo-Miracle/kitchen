<?php
    
    if ( ! defined( 'ABSPATH' ) ) { exit;}
    
    class ATTO_admin 
        {
            var $functions;
            var $licence;
            
            var $interface;
            var $options_interface;
            
            /**
            * 
            * Run on class construct
            * 
            */
            function __construct( ) 
                {
                    include (ATTO_PATH . '/include/class.atto.interface.php');
                    include (ATTO_PATH . '/include/class.atto.terms_walker.php');
                    include (ATTO_PATH . '/include/class.atto.options.php');
                    
                    $this->functions            =   new ATTO_functions();
                    
                    $this->licence              =   new ATTO_licence();
                    
                    $this->options_interface    =   new ATTO_options_interface();
                    $this->interface            =   new ATTO_interface();
                    
                    $this->_init();
                    
                     
                }
            
                
            function _init()
                {
                    add_action('admin_menu',                                    array ( $this,      'plugin_menus'), 99    );
                    add_action('admin_notices' ,                                array ( $this,      'admin_notices'));
                    
                    add_action('admin_notices',                                 array ( $this,      'admin_no_key_notices'));
                    
                    add_action( 'wp_ajax_update-taxonomy-order',                array ( $this,      'SaveAjaxOrder' ) );
                    
                    
                    add_action('current_screen',                                array ( $this,      'admin_init') );
                    add_action('wp_ajax_update-taxonomy-order-default-list',    array ( $this,      'update_taxonomy_order_default_list' ) );
                                        
                }
            
            
            /**
            * Load the scripts and the AJAX hooks, if the re-order within the default interface is active for the current area
            * 
            */
            function admin_init()
                {
                    $current_screen =   get_current_screen();
                    
                    if ( ! is_object ( $current_screen )    ||  ! isset ( $current_screen->taxonomy ) ||  empty ( $current_screen->taxonomy ) )
                        return;
                    
                    $options =   ATTO_functions::get_settings();
                    
                    $current_taxonomy   =   $current_screen->taxonomy;
                    
                    if ( isset ( $options['allow_reorder_default_interfaces'][ $current_taxonomy ] )    &&  $options['allow_reorder_default_interfaces'][ $current_taxonomy ]   ==  'no'  )
                        return;
                    
                    add_action( 'admin_enqueue_scripts',        array ( $this, 'admin_enqueue_scripts' ) );                            
                            
                    $this->register_custom_taxonomy_columns();    
                }
                
            
            /**
            * Output the scripts requred for the default interface
            * 
            * @param mixed $hook
            * @return mixed
            */
            function admin_enqueue_scripts(  $hook ) 
                {
                    
                    $screen =   get_current_screen();
                               
                    wp_enqueue_script('jquery');
                    wp_enqueue_script('jquery-ui-sortable');
                    
                    wp_enqueue_script('atto-drag-drop', ATTO_URL . '/js/atto-drag-drop.js', array('jquery'), null, true);
                    
                    $vars = array(
                                    'nonce'         =>  wp_create_nonce( 'taxonomy-default-interface-sort-update' ),
                                    'taxonomy'      =>  $screen->taxonomy,
                                    'paged'         =>  isset($_GET['paged'])   ?   intval($_GET['paged'])  :   '1',
                                    
                                    'ajaxurl'       => admin_url( 'admin-ajax.php' )
                                );
                    wp_localize_script( 'atto-drag-drop', 'ATTO_vars', $vars );
                    
                    wp_enqueue_style('atto-drag-drop-style', ATTO_URL . '/css/to.css');
                    
                }
            
            
            /**
            * Register the filters for the visible taxonomies
            * 
            */
            function register_custom_taxonomy_columns()
                {
                    $taxonomies = get_taxonomies(array('show_ui' => true), 'objects');

                    foreach ($taxonomies as $taxonomy) 
                        {
                            $taxonomy_name = $taxonomy->name;
                            add_filter  ( "manage_edit-{$taxonomy_name}_columns",  array ( $this, 'add_custom_taxonomy_column') );
                            add_filter  ( "manage_{$taxonomy_name}_custom_column", array ( $this, 'populate_custom_taxonomy_column' ), 10, 3);
                            //add_filter  ( 'hidden_columns', array ( $this, 'hidden_columns' ), 10, 3 );
                        }              
                }
            
            
            /**
            * Add the extrac olumn with the drag handler
            *     
            * @param mixed $columns
            */
            function add_custom_taxonomy_column($columns) 
                {
                
                    // Find the position of "cb-select" column
                    $cb_select_position = array_search('cb', array_keys($columns));

                    // Insert the new column before "cb-select"
                    $columns = array_slice($columns, 0, $cb_select_position, true) +
                               array('atto_sort' => '<span class="hidden">' . __('Sort ', 'atto') . '</span><span class="dashicons dashicons-editor-code"></span>') +
                               array_slice($columns, $cb_select_position, null, true);

                    return $columns;

                }
            
            
            /**
            * Populate the column with the icon
            * 
            * @param mixed $content
            * @param mixed $column_name
            * @param mixed $term_id
            */
            function populate_custom_taxonomy_column( $content, $column_name, $term_id ) 
                {
                    if ($column_name == 'atto_sort') 
                        $content    =   '<img class="atto-dd-icon" src="' . ATTO_URL . '/images/grip_icon.png" />';   
                        
                    return $content;
                }
                
                
            
            /**
            * Adjust the options depending on hide/show the reorder column
            *     
            * @param mixed $hidden
            * @param mixed $screen
            * @param mixed $use_defaults
            */
            /*
            function hidden_columns( $hidden, $screen, $use_defaults )
                {
                    if ( ! is_object ( $screen )  ||    ! isset ( $screen->taxonomy )   ||   empty ( $screen->taxonomy ) )
                        return $hidden;
                        
                    $taxonomy   =   $screen->taxonomy;
                    
                    $options = ATTO_functions::get_settings();
                    
                    if ( array_search ( 'atto_sort', $hidden ) !== FALSE    &&  ( ! isset ( $options['allow_reorder_default_interfaces'][ $taxonomy ] )  || ( isset ( $options['allow_reorder_default_interfaces'][ $taxonomy ] )   && $options['allow_reorder_default_interfaces'][ $taxonomy ]   ===  'yes'  ) ) )
                        {
                            $options['allow_reorder_default_interfaces'][ $taxonomy ]   =   'no';
                            ATTO_functions::update_settings( $options );
                            
                            return $hidden;   
                        }
                        
                    if ( array_search ( 'atto_sort', $hidden ) === FALSE    &&  isset ( $options['allow_reorder_default_interfaces'][ $taxonomy ] )   && $options['allow_reorder_default_interfaces'][ $taxonomy ]   ===  'no'  ) 
                        {
                            $options['allow_reorder_default_interfaces'][ $taxonomy ]   =   'yes';
                            ATTO_functions::update_settings( $options );
                            
                            return $hidden;   
                        }
                    
                    return $hidden;
                    
                }
            */    

                
            /**
            * Save the interface sorting
            * 
            */
            function SaveAjaxOrder()
                {
                    global $wpdb; 
            
                    //avoid using parse_Str due to the max_input_vars for large amount of data
                    $_data = explode("&", $_POST['order']);   
                    $_data  =   array_filter($_data);
                    
                    $data =   array();
                    foreach ($_data as $_data_item)
                        {
                            list($data_key, $value) = explode("=", $_data_item);
                            
                            if ( $value !== 'null' )
                                $value  =   intval ( $value );
                            
                            $data_key = str_replace("item[", "", $data_key);
                            $data_key = str_replace("]", "", $data_key);
                            
                            $data_key   =   intval ( $data_key );
                            
                            $data[$data_key] = trim( $value );
                        }
                    

                    $taxonomy   =   preg_replace( '/[^a-zA-Z0-9_\-]/', '', trim($_POST['taxonomy']) );
                    
                    //retrieve the taxonomy details 
                    $taxonomy_info = get_taxonomy($taxonomy);
                    if( $taxonomy_info->hierarchical === TRUE )    
                        $is_hierarchical = TRUE;
                        else
                        $is_hierarchical = FALSE;
                    
                    //WPML fix
                    if (defined('ICL_LANGUAGE_CODE'))
                        {
                            global $iclTranslationManagement, $sitepress;
                            
                            remove_action('edit_term',  array($iclTranslationManagement, 'edit_term'),11, 2);
                            remove_action('edit_term',  array($sitepress, 'create_term'),1, 2);
                        }
                    
                    if (is_array($data))
                        {
                                
                            //prepare the var which will hold the item childs current order
                            $childs_current_order = array();
                            
                            foreach($data as $term_id => $parent_id ) 
                                {
                                    if( $parent_id !== 'null' )
                                        {
                                            $childs_current_order   =   array();
                                            $childs_current_order[$parent_id] = $current_item_term_order;
                                                
                                            $current_item_term_order    = $childs_current_order[$parent_id];
                                            $term_parent                = $parent_id;
                                        }
                                        else
                                            {
                                                                                
                                                $current_item_term_order    = isset($current_item_term_order) ? $current_item_term_order : 0;
                                                $term_parent                = 0;
                                            }
                                        
                                    //update the term_order
                                    $args = array(
                                                    'term_order'    =>  $current_item_term_order,
                                                    'parent'        =>  $term_parent
                                                    );
                                    //wp_update_term($term_id, $taxonomy, $args);
                                    //attempt a faster method
                                    
                                    //update the term_order as the above function can't do that !!
                                    $wpdb->update( $wpdb->terms,            array('term_order'      =>  $current_item_term_order), array('term_id' => $term_id) );
                                    
                                    if ( $is_hierarchical === TRUE )
                                        $wpdb->update( $wpdb->term_taxonomy,    array('parent'          =>  $term_parent), array('term_id' => $term_id) );
                                    
                                    //Deprecated, rely on the new action 
                                    do_action('atto_order_update_hierarchical', array('term_id' =>  $term_id, 'position' =>  $current_item_term_order, 'term_parent'    =>  $term_parent));
                                    
                                    do_action('atto/order_update', array ( 'term_id' =>  $term_id, 'position' =>  $current_item_term_order, 'term_parent'    =>  $term_parent ) );
                                    
                                    $current_item_term_order++;
                      
                
                                }
                
                            //cache clear
                            clean_term_cache(array_keys( $data ), $taxonomy);
                            
                            do_action('atto/update-order-completed');
                            do_action('atto/update-order-reorder-interface');
                        }

                    die();
                }
                 
            
            /**
            * Save the default interface sorting
            * 
            */
            function update_taxonomy_order_default_list()
                {
                    //check the nonce
                    if ( ! wp_verify_nonce( $_POST['nonce'], 'taxonomy-default-interface-sort-update' ) ) 
                        die();
                    
                    set_time_limit(600);
                        
                    global $wpdb, $userdata;

                    parse_str($_POST['order'], $data);
                    
                    if (!is_array($data)    ||  count($data)    <   1)
                        die();

                    $curent_list_ids = array();
                    reset($data);
                    foreach (current($data) as $position => $term_id) 
                        {
                            $curent_list_ids[] = (int)$term_id;
                        }

                    $taxonomy   =   isset($_POST['taxonomy'])   ?   preg_replace( '/[^a-zA-Z0-9_\-]/', '', trim($_POST['taxonomy']) )  :   '';
                    if(empty($taxonomy))
                        die();
                        
                    $options =   ATTO_functions::get_settings();
                    
                    if ( isset ( $options['allow_reorder_default_interfaces'][ $taxonomy ] )    &&  $options['allow_reorder_default_interfaces'][ $taxonomy ]   ==  'no'  )
                        die();
                        
                    $objects_per_page   =   get_user_meta($userdata->ID, 'edit_'. $taxonomy .'_per_page', true);
                    if(empty($objects_per_page))
                        $objects_per_page   =   get_option('posts_per_page');

                    $current_page   =   isset($_POST['paged'])  ?   intval($_POST['paged']) :   1;
                    
                    $insert_at_index  =   ($current_page -1 ) * $objects_per_page;
                    
                    $args   =   array(
                                        'taxonomy'          =>  $taxonomy,
                                        'hide_empty'        =>  false,
                                        'orderby'           =>  'term_order',
                                        'order'             =>  'ASC',
                                        'fields'            =>  'ids'
                                        );
                        
                    $existing_terms = get_terms( $args  );
                    
                    //exclude the items in the list  $curent_list_ids
                    foreach ($curent_list_ids as $key => $term_id) 
                        {
                            if(in_array($term_id, $existing_terms))
                                {
                                    unset($existing_terms[ array_search($term_id, $existing_terms) ]);   
                                }
                        }
                    
                    //reindex
                    $existing_terms =   array_values($existing_terms);
                    array_splice( $existing_terms, $insert_at_index, 0, $curent_list_ids );
                    
                    
                    //save the sort indexes
                    foreach ( $existing_terms as $position => $term_id ) 
                        {
                            $wpdb->update(  
                                            $wpdb->terms, 
                                            array(
                                                    'term_order' => $position ), 
                                                    array(
                                                            'term_id' => intval($term_id)
                                                            )
                                            );
                                            
                            do_action('atto/order_update', array ( 'term_id' =>  $term_id, 'position' =>  $position, 'term_parent'    =>  FALSE ) );
                        }
                    
                    do_action('atto/update-order-completed');
                    
                    do_action('atto/update-order-default-interface');
                    
                    die();
                    
                }
                
                
                
            function plugin_menus()
                {
                    
                    add_action('admin_print_styles' , array ($this, 'admin_print_general_styles')); 
                     
                    $hookID =   add_options_page('Taxonomy Terms Order', '<img class="menu_tto" src="'. ATTO_URL .'/images/menu-icon.png" alt="" />Taxonomy Terms Order', 'manage_options', 'to-options', array( $this->options_interface, 'options_interface') );
                    
                    add_action('admin_print_styles-' . $hookID , array($this->options_interface,  'admin_styles' ) );
                    
                    
                    if( $this->licence->licence_key_verify()  === FALSE )
                        return;
                                                
                    $options = $this->functions->get_settings();
                    
                    if(isset($options['capability']) && !empty($options['capability']))
                            {
                                $capability = $options['capability'];
                            }
                        else if (is_numeric($options['level']))
                            {
                                //maintain the old user level compatibility
                                $capability = $this->functions->userdata_get_user_level();
                            }
                            else
                                {
                                    $capability = 'manage_options';  
                                }
                    
                    $settings  = ATTO_functions::get_settings();
                    
                    //put a menu within all custom types if apply
                    $post_types = get_post_types();
                    $location_menus = $this->functions->get_available_menu_locations();
                    foreach( $location_menus as $location_menu_slug    =>  $location_menu_data ) 
                        {
                            
                            $hide_reorder_interface =   FALSE;
                            //check settings for hide
                            if(isset($settings['show_reorder_interfaces'][$location_menu_slug]) && $settings['show_reorder_interfaces'][$location_menu_slug] == 'hide')
                                $hide_reorder_interface =   TRUE;                                
                            //filter
                            $hide_reorder_interface =   apply_filters('atto/admin/reorder-interface/hide', $hide_reorder_interface, $location_menu_data);
                                
                            if($hide_reorder_interface  === TRUE)
                                continue;
                            
                            $post_type  =   $location_menu_data['post_type'];
                                    
                            //check if there are any taxonomy for this post type
                            $post_type_taxonomies = get_object_taxonomies( $post_type, 'objects' );
                            
                            //remove the non-public
                            /*
                            foreach ( $post_type_taxonomies as  $key    =>  $post_type_taxonomy )
                                {
                                    if ( $post_type_taxonomy->public    !== TRUE )
                                        unset ( $post_type_taxonomies[ $key ] );
                                }
                            */
                                  
                            if ( count ( $post_type_taxonomies ) < 1 )
                                continue;                
                            
                            $menu_title =   apply_filters('atto/admin/menu_title', __('Taxonomy Order', 'atto'), $post_type);
                            
                            if ($post_type == 'post')
                                $hookID =   add_submenu_page('edit.php', $menu_title, $menu_title, $capability, 'to-interface-'.$post_type, array($this->interface, 'admin_interface') );
                                elseif ($post_type == 'attachment')
                                $hookID =   add_submenu_page('upload.php', $menu_title, $menu_title, $capability, 'to-interface-'.$post_type, array($this->interface, 'admin_interface') );
                                elseif($post_type == 'shopp_product'   &&  is_plugin_active('shopp/Shopp.php'))
                                    {
                                        $hookID =   add_submenu_page('shopp-products', $menu_title, $menu_title, $capability, 'to-interface-'.$post_type, array($this->interface, 'admin_interface') );
                                    }
                                else
                                $hookID =   add_submenu_page('edit.php?post_type='.$post_type, $menu_title, $menu_title, $capability, 'to-interface-'.$post_type, array($this->interface, 'admin_interface') );
                                
                            add_action('admin_print_styles-' . $hookID , array($this->interface,  'admin_styles' ) );
                            add_action('admin_print_scripts-' . $hookID , array($this->interface, 'admin_scripts' ) );
                        }
                                        
                }
                
                
            function admin_print_general_styles()
                {
                    wp_register_style('ATTO_GeneralStyleSheet', ATTO_URL . '/css/general.css');
                    wp_enqueue_style( 'ATTO_GeneralStyleSheet');    
                }    
                
                
                
            function admin_no_key_notices()
                {
                    
                    if( $this->licence->licence_key_verify()  === TRUE )
                        return;
                    
                    if ( !current_user_can('manage_options'))
                        return;
                    
                    $screen = get_current_screen();
                        
                    if(is_multisite()   &&  is_network_admin())
                        {

                        }
                        else
                        {
                               
                            ?><div class="error fade"><p><?php _e( "Advanced Taxonomy Terms Order plugin is inactive, please enter your", 'atto' ) ?> <a href="options-general.php?page=to-options"><?php _e( "Licence Key", 'atto' ) ?></a></p></div><?php
                        }
                }    
                
                
            function admin_notices()
                {
                    global $atto_interface_messages;
            
                    if(!is_array($atto_interface_messages))
                        return;
                              
                    if(count($atto_interface_messages) > 0)
                        {
                            foreach ($atto_interface_messages  as  $message)
                                {
                                    echo "<div class='". $message['type'] ." fade'><p>". $message['text']  ."</p></div>";
                                }
                        }

                }    
                
                
        
            
        } 
    
    
    
?>