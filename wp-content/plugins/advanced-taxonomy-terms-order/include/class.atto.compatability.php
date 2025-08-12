<?php
    
    
    class ATTO_compatibility
        {
            
            function __construct()
                {
                    add_filter ( 'wp_tag_cloud',                                        array ( $this, 'atto__wp_tag_cloud' ), 1, 2 );
                     
                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                    
                    if ( is_plugin_active( 'polylang/polylang.php' ) )
                        {
                            add_action ( 'atto/interface/post_type_taxonomies',         array ( $this, 'polylang_interface_post_type_taxonomies' ) );
                            add_action ( 'atto/terms_clauses/ignore',                   array ( $this, 'polylang_terms_clauses_ignore' ), 10, 4 );
                        }
                        
                    if ( is_plugin_active( 'co-authors-plus/co-authors-plus.php' ) )
                        {
                            add_action ( 'atto/get_terms_orderby/ignore',               array ( $this, 'atto__get_terms_orderby__ignore', 10, 3 ) );
                            add_action ( 'atto/terms_clauses/ignore',                   array ( $this,  'atto__get_terms_clauses__ignore', 10, 4 ) );
                        }
                        
                    if ( is_plugin_active( 'sfwd-lms/sfwd_lms.php' ) )
                        {
                            add_filter('admin_menu',                                    array( $this,       'sfwd_plugin_menus')    );
                            add_filter('atto/interface/post_type',                      array( $this,       'sfwd_interface_post_type')    );
                            add_filter('atto/interface/post_type_taxonomies',           array( $this,       'sfwd_interface_post_type_taxonomies')    );
                            add_filter('atto/interface/current_page',                   array( $this,       'sfwd_interface_current_page'), 10, 2    );
                            add_filter('atto/interface/ignore_field',                   array( $this,       'sfwd_interface_ignore_field') );
                        }
                        
                    if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) 
                        {
                            add_action('atto/order_update',                   array( $this,       'wpml_order_update') );     
                        }
                    
                }
            
            
            
            /**
            * Exclude post_translations taxonomy when Polylang is active
            * 
            * @param mixed $post_type_taxonomies
            */
            function polylang_interface_post_type_taxonomies( $post_type_taxonomies )
                {
                    
                    if ( is_array($post_type_taxonomies) && count( $post_type_taxonomies ) > 0  &&  array_search('post_translations', $post_type_taxonomies) !== FALSE )
                        {
                            unset( $post_type_taxonomies[ array_search('post_translations', $post_type_taxonomies) ]);
                        }
                    
                    return $post_type_taxonomies;
                    
                }
                
            
            /**
            * Ignore the language taxonomy
            *     
            * @param mixed $ignore
            * @param mixed $orderby
            * @param mixed $args
            */
            function polylang_terms_clauses_ignore ( $ignore, $pieces, $taxonomies, $args )
                {                    
                    if ( ! isset( $args['taxonomy'] ) ||  count( $args['taxonomy'] ) <    1  )
                        return $ignore;
                        
                    if( in_array( 'language', $args['taxonomy'] ) )
                        return TRUE;    
                        
                    return $ignore;
                    
                } 
        
        
            /**
            * Co-Authors Plus fix
            * 
            * @param mixed $ignore
            * @param mixed $orderby
            * @param mixed $args
            */
            function atto__get_terms_orderby__ignore( $ignore, $orderby, $args )
                {
                    
                    if ( ! isset($args['taxonomy']) ||  count($args['taxonomy']) !==    1 ||    array_search('author', $args['taxonomy'])   === FALSE )
                        return $ignore;    
                        
                    return TRUE;
                    
                }
                

            /**
            * 
            * 
            * @param mixed $ignore
            * @param mixed $pieces
            * @param mixed $taxonomies
            * @param mixed $args
            */
            function atto__get_terms_clauses__ignore( $ignore, $pieces, $taxonomies, $args )
                {
                    
                    if ( ! isset($args['taxonomy']) ||  count($args['taxonomy']) !==    1 ||    array_search('author', $args['taxonomy'])   === FALSE )
                        return $ignore;    
                        
                    return TRUE;
                    
                }
                
                
        
            /**
            * Add the Taxonomy Order interfce within the sfwd menu
            * 
            */
            function sfwd_plugin_menus()
                {
                    $atto_function  =   new ATTO_functions();
                    
                    $options = $atto_function->get_settings();
                    
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
                    
                    $menu_title =   apply_filters('atto/admin/menu_title', __('Taxonomy Order', 'atto'), 'sfwd-quiz');
                    
                    $atto_interface =   new ATTO_interface();
                    
                    $hookID =   add_submenu_page( 'learndash-lms', $menu_title, $menu_title, $capability, 'learndash-hub-atto', array( $atto_interface, 'admin_interface') );   
                    add_action('admin_print_styles-' . $hookID , array( $atto_interface,  'admin_styles' ) );
                    add_action('admin_print_scripts-' . $hookID , array( $atto_interface, 'admin_scripts' ) );
                }
                
            
            function sfwd_interface_post_type( $post_type )
                {
                    if ( isset ( $_GET['page']) &&  $_GET['page']   ===  'learndash-hub-atto' )
                        $post_type  =   'sfwd-quiz';
                    
                    return $post_type;
                }
                
            function sfwd_interface_post_type_taxonomies ( $taxonomies )
                {
                    if ( ! isset ( $_GET['page'] )  ||  $_GET['page']   !==  'learndash-hub-atto'  )
                        return $taxonomies;
                    
                    $post_types =   array ( 
                                            'sfwd-quiz',
                                            'groups'
                                            );
                    foreach ( $post_types   as  $post_type )
                        {
                            $taxonomies   =   array_merge( $taxonomies, get_object_taxonomies( $post_type ) );
                        }
                    
                    $taxonomies =   array_unique( $taxonomies );
                    
                    return $taxonomies;   
                }
                
                
            function sfwd_interface_current_page( $current_interface_page, $post_type )
                {
                    $post_types =   array ( 
                                            'sfwd-quiz',
                                            'groups'
                                            );
                    if ( in_array ( $post_type, $post_types ) )
                        $current_interface_page =   'learndash-hub-atto';
                    
                    return $current_interface_page;   
                }
                
                
            function sfwd_interface_ignore_field ( $interface_ignore_field )
                {
                    $interface_ignore_field[]   =   'sfwd-quiz';
                    $interface_ignore_field[]   =   'groups';
                    
                    return $interface_ignore_field;
                }
        
    
            function atto__wp_tag_cloud( $return, $args )
                {
                    $atto_function  =   new ATTO_functions();
                    
                    $options = $atto_function->get_settings();
                    
                    if ( $options['autosort'] != 1 )
                        return $return;
                    
                    if ( ! isset ( $args['taxonomy'] )  ||  $args['taxonomy']   !=  'product_tag' )
                        return $return;
                        
                    $tags = get_terms(
                        array_merge(
                            $args,
                            array()
                        )
                    ); // Always query top tags.

                    if ( empty( $tags ) || is_wp_error( $tags ) ) {
                        return;
                    }

                    foreach ( $tags as $key => $tag ) {
                        if ( 'edit' === $args['link'] ) {
                            $link = get_edit_term_link( $tag, $tag->taxonomy, $args['post_type'] );
                        } else {
                            $link = get_term_link( $tag, $tag->taxonomy );
                        }

                        if ( is_wp_error( $link ) ) {
                            return;
                        }

                        $tags[ $key ]->link = $link;
                        $tags[ $key ]->id   = $tag->term_id;
                    }
                    
                    
                    
                    $defaults = array(
                        'smallest'                   => 8,
                        'largest'                    => 22,
                        'unit'                       => 'pt',
                        'number'                     => 0,
                        'format'                     => 'flat',
                        'separator'                  => "\n",
                        'orderby'                    => 'name',
                        'order'                      => 'ASC',
                        'topic_count_text'           => null,
                        'topic_count_text_callback'  => null,
                        'topic_count_scale_callback' => 'default_topic_count_scale',
                        'filter'                     => 1,
                        'show_count'                 => 0,
                    );
                    
                    
                    $args = wp_parse_args( $args, $defaults );

                    $return = ( 'array' === $args['format'] ) ? array() : '';

                    if ( empty( $tags ) ) {
                        return;
                    }

                    // Juggle topic counts.
                    if ( isset( $args['topic_count_text'] ) ) {
                        // First look for nooped plural support via topic_count_text.
                        $translate_nooped_plural = $args['topic_count_text'];
                    } elseif ( ! empty( $args['topic_count_text_callback'] ) ) {
                        // Look for the alternative callback style. Ignore the previous default.
                        if ( 'default_topic_count_text' === $args['topic_count_text_callback'] ) {
                            /* translators: %s: Number of items (tags). */
                            $translate_nooped_plural = _n_noop( '%s item', '%s items' );
                        } else {
                            $translate_nooped_plural = false;
                        }
                    } elseif ( isset( $args['single_text'] ) && isset( $args['multiple_text'] ) ) {
                        // If no callback exists, look for the old-style single_text and multiple_text arguments.
                        // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralSingle,WordPress.WP.I18n.NonSingularStringLiteralPlural
                        $translate_nooped_plural = _n_noop( $args['single_text'], $args['multiple_text'] );
                    } else {
                        // This is the default for when no callback, plural, or argument is passed in.
                        /* translators: %s: Number of items (tags). */
                        $translate_nooped_plural = _n_noop( '%s item', '%s items' );
                    }

                 
                    if ( $args['number'] > 0 ) {
                        $tags = array_slice( $tags, 0, $args['number'] );
                    }

                    $counts      = array();
                    $real_counts = array(); // For the alt tag.
                    foreach ( (array) $tags as $key => $tag ) {
                        $real_counts[ $key ] = $tag->count;
                        $counts[ $key ]      = call_user_func( $args['topic_count_scale_callback'], $tag->count );
                    }

                    $min_count = min( $counts );
                    $spread    = max( $counts ) - $min_count;
                    if ( $spread <= 0 ) {
                        $spread = 1;
                    }
                    $font_spread = $args['largest'] - $args['smallest'];
                    if ( $font_spread < 0 ) {
                        $font_spread = 1;
                    }
                    $font_step = $font_spread / $spread;

                    $aria_label = false;
                    /*
                     * Determine whether to output an 'aria-label' attribute with the tag name and count.
                     * When tags have a different font size, they visually convey an important information
                     * that should be available to assistive technologies too. On the other hand, sometimes
                     * themes set up the Tag Cloud to display all tags with the same font size (setting
                     * the 'smallest' and 'largest' arguments to the same value).
                     * In order to always serve the same content to all users, the 'aria-label' gets printed out:
                     * - when tags have a different size
                     * - when the tag count is displayed (for example when users check the checkbox in the
                     *   Tag Cloud widget), regardless of the tags font size
                     */
                    if ( $args['show_count'] || 0 !== $font_spread ) {
                        $aria_label = true;
                    }

                    // Assemble the data that will be used to generate the tag cloud markup.
                    $tags_data = array();
                    foreach ( $tags as $key => $tag ) {
                        $tag_id = isset( $tag->id ) ? $tag->id : $key;

                        $count      = $counts[ $key ];
                        $real_count = $real_counts[ $key ];

                        if ( $translate_nooped_plural ) {
                            $formatted_count = sprintf( translate_nooped_plural( $translate_nooped_plural, $real_count ), number_format_i18n( $real_count ) );
                        } else {
                            $formatted_count = call_user_func( $args['topic_count_text_callback'], $real_count, $tag, $args );
                        }

                        $tags_data[] = array(
                            'id'              => $tag_id,
                            'url'             => ( '#' !== $tag->link ) ? $tag->link : '#',
                            'role'            => ( '#' !== $tag->link ) ? '' : ' role="button"',
                            'name'            => $tag->name,
                            'formatted_count' => $formatted_count,
                            'slug'            => $tag->slug,
                            'real_count'      => $real_count,
                            'class'           => 'tag-cloud-link tag-link-' . $tag_id,
                            'font_size'       => $args['smallest'] + ( $count - $min_count ) * $font_step,
                            'aria_label'      => $aria_label ? sprintf( ' aria-label="%1$s (%2$s)"', esc_attr( $tag->name ), esc_attr( $formatted_count ) ) : '',
                            'show_count'      => $args['show_count'] ? '<span class="tag-link-count"> (' . $real_count . ')</span>' : '',
                        );
                    }

                    /**
                     * Filters the data used to generate the tag cloud.
                     *
                     * @since 4.3.0
                     *
                     * @param array[] $tags_data An array of term data arrays for terms used to generate the tag cloud.
                     */
                    $tags_data = apply_filters( 'wp_generate_tag_cloud_data', $tags_data );

                    $a = array();

                    // Generate the output links array.
                    foreach ( $tags_data as $key => $tag_data ) {
                        $class = $tag_data['class'] . ' tag-link-position-' . ( $key + 1 );
                        $a[]   = sprintf(
                            '<a href="%1$s"%2$s class="%3$s" style="font-size: %4$s;"%5$s>%6$s%7$s</a>',
                            esc_url( $tag_data['url'] ),
                            $tag_data['role'],
                            esc_attr( $class ),
                            esc_attr( str_replace( ',', '.', $tag_data['font_size'] ) . $args['unit'] ),
                            $tag_data['aria_label'],
                            esc_html( $tag_data['name'] ),
                            $tag_data['show_count']
                        );
                    }

                    switch ( $args['format'] ) {
                        case 'array':
                            $return =& $a;
                            break;
                        case 'list':
                            /*
                             * Force role="list", as some browsers (sic: Safari 10) don't expose to assistive
                             * technologies the default role when the list is styled with `list-style: none`.
                             * Note: this is redundant but doesn't harm.
                             */
                            $return  = "<ul class='wp-tag-cloud' role='list'>\n\t<li>";
                            $return .= implode( "</li>\n\t<li>", $a );
                            $return .= "</li>\n</ul>\n";
                            break;
                        default:
                            $return = implode( $args['separator'], $a );
                            break;
                    }

                    if ( $args['filter'] ) {
                        /**
                         * Filters the generated output of a tag cloud.
                         *
                         * The filter is only evaluated if a true value is passed
                         * to the $filter argument in wp_generate_tag_cloud().
                         *
                         * @since 2.3.0
                         *
                         * @see wp_generate_tag_cloud()
                         *
                         * @param string[]|string $return String containing the generated HTML tag cloud output
                         *                                or an array of tag links if the 'format' argument
                         *                                equals 'array'.
                         * @param WP_Term[]       $tags   An array of terms used in the tag cloud.
                         * @param array           $args   An array of wp_generate_tag_cloud() arguments.
                         */
                        return apply_filters( 'wp_generate_tag_cloud', $return, $tags, $args );
                    } else {
                        return $return;
                    }
                    
                    
                    
                }
                
            
            
            /**
            * Synchroniz the terms to other languages
            *     
            * @param mixed $args
            */
            function wpml_order_update ( $args )
                {
                    if ( ! defined ( 'ICL_LANGUAGE_CODE' ) || ! defined( 'ICL_SITEPRESS_VERSION' ) )  
                        return;
                    
                    $options = ATTO_functions::get_settings();
                    
                    if ( $options['wpml_sort_synchronization'] !== "1" )
                        return;
                    
                    global $wpdb;
                    
                    $current_language   = ICL_LANGUAGE_CODE;
                    $site_languages     = icl_get_languages('skip_missing=0');
                    
                    $term_data  =   get_term ( $args['term_id'] );
                    
                    foreach ( $site_languages   as  $language_code  =>  $site_language )
                        {
                            if ( $language_code ==  $current_language )
                                continue;
                                
                            $translated_term_id = apply_filters(
                                                                    'wpml_object_id',
                                                                    $term_data->term_id,
                                                                    $term_data->taxonomy,
                                                                    false, // Return the term ID even if there's no translation
                                                                    $language_code
                                                                );
                                                                
                            if ( ! ( $translated_term_id ) ||   is_null ( $translated_term_id ) )
                                continue;
                                                        
                            do_action( 'wpml_switch_language', $language_code );
                                
                            //get the term data
                            $language_term_data =   get_term ( $translated_term_id );
                            
                            $wpdb->update( $wpdb->terms,            array( 'term_order'      =>  $args['position'] ), array( 'term_id' => $translated_term_id ) );
                            
                            do_action( 'wpml_switch_language', $current_language );
                        
                        }
                }
                        
        }
        
    new ATTO_compatibility();    
    
?>