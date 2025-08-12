<?php


    class ATTO_Terms_Walker extends Walker 
        {

            var $db_fields = array ('parent' => 'parent', 'id' => 'term_id');


            function start_lvl(&$output, $depth = 0, $args = array()) 
                {
                    extract($args, EXTR_SKIP);
                    
                    $indent = str_repeat("\t", $depth);
                    $output .= "\n$indent<ul class='children sortable'>\n";
                }


            function end_lvl(&$output, $depth = 0, $args = array()) 
                {
                    extract($args, EXTR_SKIP);
                        
                    $indent = str_repeat("\t", $depth);
                    $output .= "$indent</ul>\n";
                }


            function start_el(&$output, $term, $depth = 0, $args = array(), $current_object_id = 0) 
                {
                    if ( $depth )
                        $indent = str_repeat("\t", $depth);
                    else
                        $indent = '';

                    //extract($args, EXTR_SKIP);
                    $post_type      = isset($_GET['post_type']) ? preg_replace( '/[^a-zA-Z0-9_\-]/', '', trim($_GET['post_type']) ) : 'post'; 
                    
                    $output .= $indent . '<li class="tt_li" id="item_'.$term->term_id.'"><div class="item">';
                    
                    $term_children  = get_term_children ( $term->term_id, $term->taxonomy );
                    if ( count ( $term_children ) > 0) 
                        {
                            $output .=  '<span class="disclose">
                                                <span class="dashicons dashicons-plus-alt2"></span>
                                                <span class="dashicons dashicons-minus"></span>
                                            </span>';
                        }
                    $output .= '<div class="options">
                        <span class="option move_top dashicons dashicons-arrow-up-alt2" title="'. __( "Move to Top", 'atto' ) .'"></span>
                        <span class="option move_bottom dashicons dashicons-arrow-down-alt2" title="'. __( "Move to Bottom", 'atto' ) .'"></span>
                        <a href="'. admin_url( 'edit-tags.php') .'?action=edit&taxonomy='. $term->taxonomy .'&tag_ID='.$term->term_id.'&post_type='. $post_type .'"><span class="option sticky dashicons dashicons-edit" title="Edit"></span></a>
                    </div>
                    <span class="pnfo">'.apply_filters( 'term_name', $term->name, $term->term_id ).' ('.$term->term_id.')</span></div>';
                }


            function end_el(&$output, $post_type, $depth = 0, $args = array()) 
                {
                    $output .= "</li>\n";
                }

        }

?>