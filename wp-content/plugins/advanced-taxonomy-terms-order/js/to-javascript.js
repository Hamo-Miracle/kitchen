
    
    var ATTO = {
    
        change_taxonomy: function(element)
            {
                jQuery('#to_form').submit();
            },
            
        interface_reverse_order :   function()
                {
                    //keep the height to prevent browser scroll
                    jQuery("#sortable").css('min-height', 'inherit');
                    jQuery("#sortable").css('min-height', jQuery("#sortable").height() + 'px');
                    
                    jQuery("#sortable").append(jQuery('#sortable > li').hide().get().reverse());
                    jQuery('#sortable > li').slideDown(100);
                },
                
            interface_title_order :   function( order_type )
                {
                    //keep the height to prevent browser scroll
                    jQuery("#sortable").css('min-height', 'inherit');
                    jQuery("#sortable").css('min-height', jQuery("#sortable").height() + 'px');
                    
                    ATTO._sortabl_level_sort( '#sortable', '#sortable > li', order_type);
                },
                
            _sortabl_level_sort : function ( $sortable_list, $sortable_li, order_type )
                {
                    var $sortable_list = jQuery( $sortable_list ),
                        $sortable_li = jQuery( $sortable_li );
                        
                    jQuery.each ( $sortable_li, function ( index, value ) {
                        var child_elements = jQuery( value).find(' > ul > li');
                        if ( child_elements.length > 0 )
                            ATTO._sortabl_level_sort( jQuery( value).find(' > ul'), jQuery( value).find(' > ul > li'), order_type);
                    })
                         
                    $sortable_li.sort(function(a,b){
                        var an = jQuery(a).find('.pnfo').html().toLowerCase(),
                            bn = jQuery(b).find('.pnfo').html().toLowerCase();
                        
                        if(order_type == 'ASC')
                            {
                                if(an > bn) 
                                    {
                                        return 1;
                                    }
                                if(an < bn) 
                                    {
                                        return -1;
                                    }
                            }
                            
                        if(order_type == 'DESC')
                            {
                                if(an < bn) 
                                    {
                                        return 1;
                                    }
                                if(an > bn) 
                                    {
                                        return -1;
                                    }
                            }
                        
                        return 0;
                    });

                    $sortable_li.detach().hide().appendTo($sortable_list).slideDown(100);                        
                },
                
            interface_id_order :   function(order_type)
                {
                    //keep the height to prevent browser scroll
                    jQuery("#sortable").css('min-height', 'inherit');
                    jQuery("#sortable").css('min-height', jQuery("#sortable").height() + 'px');
                            
                    var $sortable_list = jQuery('#sortable'),
                        $sortable_li = jQuery('#sortable > li');

                    $sortable_li.sort(function(a,b){
                        var an = parseInt ( jQuery(a).attr('id').toLowerCase().replace("item_", "") ),
                            bn = parseInt ( jQuery(b).attr('id').toLowerCase().replace("item_", "") );

                        if(order_type == 'ASC')
                            {
                                if(an > bn) 
                                    {
                                        return 1;
                                    }
                                if(an < bn) 
                                    {
                                        return -1;
                                    }
                            }
                            
                        if(order_type == 'DESC')
                            {
                                if(an < bn) 
                                    {
                                        return 1;
                                    }
                                if(an > bn) 
                                    {
                                        return -1;
                                    }
                            }
                        
                        return 0;
                    });

                    $sortable_li.detach().hide().appendTo($sortable_list).slideDown(100);
                },
                
            interface_random_order  :   function ()
                {
                    jQuery("#sortable").css('min-height', 'inherit');
                    jQuery("#sortable").css('min-height', jQuery("#sortable").height() + 'px');
                            
                    var $sortable_list = jQuery('#sortable'),
                        $sortable_li = jQuery('#sortable > li');

                        
                    const shuffleArray = this.shuffle( $sortable_li );

                        
                    $sortable_li.sort(function(a,b){
                        var an = jQuery(a).find('.pnfo').html().toLowerCase(),
                            bn = jQuery(b).find('.pnfo').html().toLowerCase();

                        const min = parseInt( -1 );
                        const max = parseInt( 1 );

                        // generating a random number
                        a = Math.floor(Math.random() * (max - min + 1)) + min;
                        
                        return a;
                        
                    });

                    $sortable_li.detach().hide().appendTo($sortable_list).slideDown(100);   

                },
                
                
            shuffle :   function( a )
                {
                    for (let i = a.length - 1; i > 0; i--) {
                        const j = Math.floor(Math.random() * (i + 1));
                        [a[i], a[j]] = [a[j], a[i]];
                    }
                    return a;   

                },
                
            interface_collapse : function () 
                {
                    jQuery( '#sortable  li > ul').each( function() {
                        jQuery(this).parent( 'li' ).addClass('minimized');
                        jQuery(this).closest('li').find(' > ul > li').slideUp();
                    })
                },
                
            interface_expand : function () 
                {
                    jQuery( '#sortable  li > ul').each( function() {
                        jQuery(this).parent( 'li' ).removeClass('minimized');
                        jQuery(this).closest('li').find(' > ul > li').slideDown();
                    })   
                },
                
            move_element : function (element, position)
            {
                var sortable_holder = jQuery(element).closest('ul');
                
                switch(position)
                    {
                        case    'top'   :
                                            jQuery(element).slideUp('fast', function() {
                                                jQuery(sortable_holder).prepend(jQuery(element));
                                                jQuery(element).slideDown('fast');
                                            });       
                                            break; 
                       
                       case    'bottom'   :
                                            jQuery(element).slideUp('fast', function() {
                                                jQuery(sortable_holder).append(jQuery(element));
                                                jQuery(element).slideDown('fast');
                                            });       
                                            break; 
                        
                    }
                
            }
        
    }
    
    
    
    jQuery( document ).ready(function() {
    
        jQuery('.disclose').on('click', function() {
            if ( jQuery(this).closest('li').hasClass( 'minimized' ) )
                {
                    jQuery(this).closest('li').removeClass('minimized');
                    jQuery(this).closest('li').find(' > ul > li').slideDown();
                }
                else
                {
                    jQuery(this).closest('li').addClass('minimized');
                    jQuery(this).closest('li').find(' > ul > li').slideUp();
                }
                });
                
                
        jQuery('#sortable .options').on('click', '.option.move_top', function() {
                        ATTO.move_element(jQuery(this).closest('li'), 'top');
                    })
        jQuery('#sortable .options').on('click', '.option.move_bottom', function() {
                        ATTO.move_element(jQuery(this).closest('li'), 'bottom');
                    })
            
    })
            