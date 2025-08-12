<?php
    
    if ( ! defined( 'ABSPATH' ) ) { exit;}
    
    class ATTO
        {
            var $functions;
            var $licence;
            
            /**
            * 
            * Run on class construct
            * 
            */
            function __construct( ) 
                {
                 
                    $this->functions            =   new ATTO_functions();
                    
                    $this->licence              =   new ATTO_licence();
                    
                    $this->_init();

                     
                }
                
                
            function _init()
                {
                    
                    //disable the free plugin if still active
                    ATTO_functions::atto_disable_category_terms_order();
                    
                    $this->_init_rest();

                    $this->_wp_ecommerce_patch();
                    
                    if (is_admin())
                        {
                            include (ATTO_PATH . '/include/class.atto.admin.php');
                            
                            new ATTO_admin();    
                        }
                    
                    if( $this->licence->licence_key_verify()  === TRUE )
                        {
                            add_filter('get_terms_orderby', array( $this,   '_get_terms_orderby'),      10,     2);
                            add_filter('terms_clauses',     array( $this,   '_terms_clauses'),          999,    3);
                            
                            /**
                            *   
                            * wp_get_object_terms term_order support, Backward compatibility
                            * Only for WordPress 4.6 and older
                            * 
                            */
                            global $wp_version;
                            if( version_compare( $wp_version, '4.6' , '<=' ) ) 
                                {    
                                    add_filter('wp_get_object_terms',   array( $this,   '_wp_get_object_terms'),    99,     4);
                                    add_filter('get_the_terms',         array( $this,   '_wp_get_object_terms'),    999,    3);
                                }
                        }
                    
                }
                
            
            /**
            * Add REST endpoints
            *                 
            */
            function _init_rest()
                {
                    //prepare rest data
                    new ATTO_rest();   
                    
                }
                
            
            /**
            * Wp E-commerce fix, remove the term filter in case we use autosort
            * 
            */
            function _wp_ecommerce_patch()
                {
                    $options = $this->functions->get_settings();
            
                    if (is_admin())
                        {
                            if ($options['adminsort'] == "1")
                                remove_filter('get_terms','wpsc_get_terms_category_sort_filter');
                        }
                        else
                        {
                            if ($options['autosort'] == 1)
                                remove_filter('get_terms','wpsc_get_terms_category_sort_filter');   
                        }   
                    
                }
            
           
           
            function _get_terms_orderby($orderby, $args)
                {
                    
                    if ( apply_filters( 'atto/get_terms_orderby/ignore', FALSE, $orderby, $args ) )
                        return $orderby;
                    
                    //make sure the requested orderby follow the original args data
                    if ($args['orderby'] == 'term_order')
                        $orderby = 't.term_order';

                    return $orderby;
                    
                }
                
                
            function _terms_clauses( $pieces, $taxonomies, $args )
                {
                    //no need to order when count terms for this query
                    if(isset($args['fields']) && strtolower($args['fields'])    ===  'count')
                        return $pieces;
                        
                    if ( apply_filters('atto/terms_clauses/ignore', FALSE, $pieces, $taxonomies, $args) )
                        return $pieces;    
                    
                    //check for sort ignore
                    if(isset($args['ignore_custom_sort']) && $args['ignore_custom_sort']    === TRUE)
                        return $pieces;
                    
                    $options = $this->functions->get_settings(); 

                    //if admin make sure use the admin setting
                    if (is_admin() && !defined('DOING_AJAX'))
                        {
                            if($options['adminsort'] !== "1" && (isset($args['orderby']) && $args['orderby'] !== 'term_order'))
                                return $pieces;
                                
                            //ignore sorting when using columns sort
                            if ( $options['adminsort'] === "1" && isset($_GET['orderby']) && $_GET['orderby'] !==  'term_order')
                                return $pieces;
                        } 
                        else 
                            {
                                if($options['autosort'] !== '1' && (isset($args['orderby']) && $args['orderby'] !== 'term_order'))
                                    return $pieces;
                            }
                    
                    if ( count ( $taxonomies ) == 1 )
                        {
                            //check the current setting for current taxonomy
                            reset ( $taxonomies );
                            $taxonomy = current ( $taxonomies );
                            $order_type = (isset($options['taxonomy_settings'][$taxonomy]['order_type'])) ? $options['taxonomy_settings'][$taxonomy]['order_type'] : 'manual'; 
                            
                            //if manual
                            if ($order_type == 'manual')
                                {
                        
                                    $taxonomy_info = get_taxonomy($taxonomy);
                                    
                                    //check if is hierarchical
                                    if ($taxonomy_info->hierarchical !== TRUE)
                                        {
                                            $pieces['orderby'] = 'ORDER BY t.term_order';
                                        }
                                        else
                                        {
                                            //customise the order
                                            global $wpdb;
                                            
                                            /*
                                            $query_pieces = array( 'fields', 'join', 'where', 'orderby', 'order', 'limits' );
                                            foreach ( $query_pieces as $piece )
                                                $$piece = isset( $pieces[$piece] ) ? $pieces[$piece] : '';
                                            */

                                            $pieces['orderby'] = 'ORDER BY t.term_order';     
                                            
                                            $query = "SELECT " . $pieces['distinct'] ." ". $pieces['fields'] ." FROM $wpdb->terms AS t ".$pieces['join'] ." WHERE ".$pieces['where'] ."  ".$pieces['orderby'] ." ASC ".$pieces['limits'];
                                            $results = $wpdb->get_results($query);
                                            
                                            $children = $this->functions->get_term_hierarchy( $taxonomy );
                                            
                                            $parent = isset($args['parent']) && is_numeric($args['parent']) ? $args['parent'] : 0;
                                            $terms_order_raw = $this->_process_hierarhically( $taxonomy, $results, $children, $parent );
                                            $terms_order_raw = rtrim($terms_order_raw, ",");
                                            
                                            if(!empty($terms_order_raw))                        
                                                $pieces['orderby'] = 'ORDER BY FIELD(t.term_id, '. $terms_order_raw .')';
                                                
                                        }
                                                     
                                    //no need to continue; return original order
                                    return $pieces;   
                                }
                                
                            //if auto
                            $auto_order_by = isset($options['taxonomy_settings'][$taxonomy]['auto']['order_by']) ? $options['taxonomy_settings'][$taxonomy]['auto']['order_by'] : 'name';
                            $auto_order = isset($options['taxonomy_settings'][$taxonomy]['auto']['order']) ? $options['taxonomy_settings'][$taxonomy]['auto']['order'] : 'desc';
                            
                            
                            $order_by = "";
                            switch ($auto_order_by)
                                {
                                    case 'default':
                                                        return $pieces;
                                                        break;
                                    
                                    case 'id':
                                                $order_by = "t.term_id";
                                                break;
                                    case 'name':
                                                $order_by = 't.name';
                                                break;
                                    case 'slug':
                                                $order_by = 't.slug';
                                                break;
                                    case 'count':
                                                $order_by = 'tt.count';
                                                break;
                                                
                                    case 'random':
                                                $order_by = 'RAND()';
                                                break;
                                }
                            
                            $pieces['orderby']  = 'ORDER BY '. $order_by; 
                            $pieces['order']    =  strtoupper($auto_order); 
                            
                            return $pieces; 
                        }
                        else
                        {
                            //if autosort, then force the term_order
                            if ($options['autosort'] == 1)
                                {
                                    $pieces['orderby'] = 'ORDER BY t.term_order';
                            
                                    return $pieces; 
                                }
                                
                            return $pieces;
                                
                        }

                }
                
                
                
            function _process_hierarhically($taxonomy, &$terms, &$children, $parent = 0, $level = 0 )
                {

                    $output = '';
                    foreach ( $terms as $key => $term ) 
                        {
                            if( ! isset ( $term->parent ) )
                                {
                                    $output .= $term->term_id . ",";
                                    
                                    unset( $terms[$key] );

                                    if ( isset( $children[$term->term_id] ) )
                                        $output .= $this->_process_hierarhically( $taxonomy, $terms, $children,  $term->term_id, $level + 1 );   
                                }
                                else
                                {
                                    // ignore if not search?!?
                                    if ( $term->parent != $parent || empty( $_REQUEST['s'] ) )
                                        continue;
                            
                                    $output .= $term->term_id . ",";
                            
                                    unset( $terms[$key] );

                                    if ( isset( $children[$term->term_id] ) )
                                        $output .= $this->_process_hierarhically( $taxonomy, $terms, $children,  $term->term_id, $level + 1 );
                                }
                        }

                    return $output;
                
                }
           
        
               
            function _wp_get_object_terms($terms, $object_ids, $taxonomies, $args = array())
                {
                    if(!is_array($terms) || count($terms) < 1)
                        return $terms;
                            
                    global $wpdb;
                   
                    $options = $this->functions->get_settings();
                   
                    if (is_admin() && !defined('DOING_AJAX'))
                        {
                            if ($options['adminsort'] != "1" && (!isset($args['orderby']) || $args['orderby']   !=  'term_order'))
                                return $terms;    
                        }
                        else
                        {
                            if ($options['autosort'] != "1" && (!isset($args['orderby']) || $args['orderby']   !=  'term_order'))
                                return $terms;                        
                        }
                        
                    //check for ignore filter
                    if(apply_filters('atto/ignore_get_object_terms', $terms, $object_ids, $taxonomies, $args) === TRUE)
                        return $terms; 
                                    
                    //check for sort ignore
                    if(isset($args['ignore_custom_sort']) && $args['ignore_custom_sort']    === TRUE)
                        return $terms;
                        
                    // return $terms;
                        
                    if(!is_array($object_ids))
                        $object_ids =   explode(",", $object_ids);
                    $object_ids = array_map('trim', $object_ids);
                    
                    if ( !is_array($taxonomies) )
                        $taxonomies = explode(",", $taxonomies);
                    $taxonomies = array_map('trim', $taxonomies);

                    foreach ( $taxonomies as $key   =>  $taxonomy ) 
                        {
                            $taxonomies[$key]   =   trim($taxonomy, "'");
                        }
            
                    //no need if multiple objects
                    if(count($object_ids) > 1)
                        return $terms;
                        
                    //check if there are terms and if they belong to current taxonomies list, oterwise return as there's nothign to sort
                    foreach($terms  as $term)
                        {
                            if(!isset($term->taxonomy))
                                return $terms;
                                
                            if(!in_array($term->taxonomy, $taxonomies))
                                return $terms;
                        }
                    
                    $object_id  =   $object_ids[0];
                                        
                    $terms = array();
                        
                    if(!isset($args['order']))
                        $args['order']    =   '';
                        
                    if(!isset($args['fields']))
                        $args['fields']    =   'all';
                    
                    extract($args, EXTR_SKIP);
                    
                    $select_this = '';
                    if ( 'all' == $fields )
                        $select_this = 't.*, tt.*';
                    else if ( 'ids' == $fields )
                        $select_this = 't.term_id';
                    else if ( 'names' == $fields )
                        $select_this = 't.name';
                    else if ( 'slugs' == $fields )
                        $select_this = 't.slug';
                    else if ( 'all_with_object_id' == $fields )
                        $select_this = 't.*, tt.*, tr.object_id';
                    
                    foreach ( $taxonomies as $key   =>  $taxonomy ) 
                        {
                            $order_type = (isset($options['taxonomy_settings'][$taxonomy]['order_type'])) ? $options['taxonomy_settings'][$taxonomy]['order_type'] : 'manual'; 
                            
                            //if manual
                            if ($order_type == 'manual')
                                {
                                    $orderby    =   't.term_order';
                                    
                                    // tt_ids queries can only be none or tr.term_taxonomy_id
                                    if ( 'tt_ids' == $fields )
                                        $orderby = 'tr.term_taxonomy_id';

                                    if ( !empty($orderby) )
                                        $orderby = "ORDER BY $orderby";

                                    $order = strtoupper( $order );
                                    if ( '' !== $order && ! in_array( $order, array( 'ASC', 'DESC' ) ) )
                                        $order = 'ASC';
                                }
                                else
                                {
                                    if(isset($options['taxonomy_settings'][$taxonomy])  &&  isset($options['taxonomy_settings'][$taxonomy]['auto'])  &&  isset($options['taxonomy_settings'][$taxonomy]['auto']['order_by']))
                                        $orderby    =   'ORDER BY t.' . $options['taxonomy_settings'][$taxonomy]['auto']['order_by'];
                                        else
                                        {
                                            if(isset($args['orderby']))
                                                $orderby    =   'ORDER BY t.' . $args['orderby'];
                                                else
                                                $orderby    =   'ORDER BY t.name';
                                        }
                                    
                                    
                                    if(isset($options['taxonomy_settings'][$taxonomy])  &&  isset($options['taxonomy_settings'][$taxonomy]['auto'])  &&  isset($options['taxonomy_settings'][$taxonomy]['auto']['order']))
                                        $order    =   strtoupper($options['taxonomy_settings'][$taxonomy]['auto']['order']);
                                        else
                                        {
                                            if(isset($args['order']))
                                                $order    =   $args['order'];
                                                else
                                                $order    =   'ASC';
                                        }
                                    
                                    //$order      =   strtoupper($options['taxonomy_settings'][$taxonomy]['auto']['order']);
                                }
                            
                                                
                            $query = "SELECT $select_this FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON tt.term_id = t.term_id INNER JOIN $wpdb->term_relationships AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy IN ('$taxonomy') AND tr.object_id IN ($object_id) $orderby $order";

                            if ( 'all' == $fields || 'all_with_object_id' == $fields ) 
                            {
                                $_terms = $wpdb->get_results($query);
                                foreach ( $_terms as $key => $term ) 
                                    {
                                        $_terms[$key] = sanitize_term( $term, $term->taxonomy, 'raw' );
                                    }
                                    
                                $object_id_index = array();
                                foreach ( $_terms as $key => $term ) 
                                    {
                                        $term = sanitize_term( $term, $taxonomy, 'raw' );
                                        $_terms[ $key ] = $term;

                                        if ( isset( $term->object_id ) ) 
                                            {
                                                $object_id_index[ $key ] = $term->object_id;
                                            }
                                    }
                                
                                update_term_cache($_terms, $taxonomy);
                                $_terms = array_map( 'get_term', $_terms );

                                // Re-add the object_id data, which is lost when fetching terms from cache.
                                if ( 'all_with_object_id' === $fields ) 
                                    {
                                        foreach ( $_terms as $key => $_term ) 
                                            {
                                                if ( isset( $object_id_index[ $key ] ) ) 
                                                    {
                                                        $_term->object_id = $object_id_index[ $key ];
                                                    }
                                            }
                                    }
                                
                                $terms = array_merge($terms, $_terms);
                            } 
                            else if ( 'ids' == $fields || 'names' == $fields || 'slugs' == $fields ) 
                            {
                                $_terms = $wpdb->get_col( $query );
                                $_field = ( 'ids' == $fields ) ? 'term_id' : 'name';
                                foreach ( $_terms as $key => $term ) 
                                    {
                                        $_terms[$key] = $term;
                                    }
                                $terms = array_merge($terms, $_terms);
                            } 
                            else if ( 'tt_ids' == $fields ) 
                            {
                                $terms = $wpdb->get_col("SELECT tr.term_taxonomy_id FROM $wpdb->term_relationships AS tr INNER JOIN $wpdb->term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tr.object_id IN ($object_id) AND tt.taxonomy IN ('$taxonomy') $orderby $order");
                                foreach ( $terms as $key => $tt_id ) 
                                    {
                                        $terms[$key] = $tt_id;
                                    }
                            }    
                        }

                        
                    return $terms;
                    
                }

            
        
        } 
    
    
    
?>