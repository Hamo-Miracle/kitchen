jQuery( document ).ready( function ( $ ) {
    var $notification = $( '#notification' ),
        $conditionalSelects = $( '#bnfw-conditional-selects' ),
        $taxonomy = $( '#bnfw-taxonomies' ),
        $newtaxonomy = $( '#bnfw-post-term-taxonomy' ),
        $terms_from = $( '#bnfw-terms-changed-from' ),
        $terms_to = $( '#bnfw-terms-changed-to' ),
        $terms = $( '#bnfw-terms' );

    if ( !$notification.length ) {
        return;
    }

    /**
     * Show/Hide Selects based on notification selected.
     */
    function handleSelects() {
        var notification = $notification.val();
        var texonomy_notification = notification.split( '-' );

        var multisiteNotification = notification.search( 'multisite-new-user' );

        if ( 'user-role' === notification || 'admin-role' === notification ) {
            $( '#bnfw-user-role-selects' ).show();
        } else {
            $( '#bnfw-user-role-selects' ).hide();
        }

        if ( 'termchanged' === texonomy_notification[0] ) {
            var $conditionalTaxonomySelects = $( '#bnfw-taxonomy-term-select' );
            get_taxonomy_select( $conditionalTaxonomySelects, notification );
        } else {
            $( '#bnfw-taxonomy-term-select' ).hide();
            $( '#bnfw-taxonomy-term-select-from' ).hide();
        }

        /*
		*
		*
		* Show conditional fields if the notification is not belong to excluded
		* @since 1.0.15
		*
		* 
		*/
        var exclude_from = ['Admin','Transactional','Media'];

        var selected = $('#notification :selected');
    	var group = selected.parent().attr('label');

        var bailout = false;

        if(exclude_from.indexOf(group) >= 0){
            $( '#bnfw-conditional-selects' ).hide();
            bailout = true;
        }


        if ( 'welcome-email' === notification || 'new-user' === notification || 'user-login' === notification || 'admin-user-login' === notification || 'multisite-new-user-welcome' === notification || 'multisite-new-user-invited' === notification ) {
            $( '#bnfw-user-role-select' ).show();
        } else {
            $( '#bnfw-user-role-select' ).hide();
        }

        /*
		*
		*
		* Terminate ajax execution if the conditional fields are hidden
		* @since 1.0.15
		*
		* 
		*/
		if(bailout)
			return;

        $.ajax( {
            url: ajaxurl,
            data: {
                'action': 'bnfw_get_notification_post_type',
                'notification': notification
            }
        } )
            .done(
                function ( postType ) {
                    if ( '' != postType ) {
                        $conditionalSelects.show();
                        loadTaxonomy( postType );
                        $terms.select2();
                    } else {
                        $conditionalSelects.hide();
                    }
                }
            );
    }

    function get_taxonomy_select( $conditionalTaxonomySelects, notification ) {
        $taxonomy = $newtaxonomy;
        $.ajax( {
            url: ajaxurl,
            data: {
                'action': 'bnfw_get_notification_post_type',
                'notification': notification
            }
        } )
            .done( function ( postType ) {
                if ( '' != postType ) {
                    $conditionalTaxonomySelects.show();
                    loadTaxonomy( postType );
                } else {
                    $conditionalTaxonomySelects.hide();
                }
            }
            );
    }

    /**
     * Load Taxonomy.
     *
     * @param postType
     */
    function loadNewTaxonomy( postType ) {
        $newtaxonomy.select2( {
            allowClear: true,
            ajax: {
                url: ajaxurl,
                dataType: 'json',
                data: function ( params ) {
                    return {
                        action: 'bnfw_get_taxonomies',
                        post_type: postType
                    };
                },
                processResults: function ( data ) {
                    return {
                        results: data
                    };
                }
            }
        } );

    }

    /**
     * Load Taxonomy.
     *
     * @param postType
     */
    function loadTaxonomy( postType ) {
        $taxonomy.select2( {
            allowClear: true,
            ajax: {
                url: ajaxurl,
                dataType: 'json',
                data: function ( params ) {
                    return {
                        action: 'bnfw_get_taxonomies',
                        post_type: postType
                    };
                },
                processResults: function ( data ) {
                    return {
                        results: data
                    };
                }
            }
        } );
    }

    /**
     * Load Terms for Term change notification.
     *
     * @param taxonomy
     */
    function loadNewTerms( taxonomy ) {
        $terms_from.val( '' );
        $terms_to.val( '' );
        $terms_from.select2( {
            ajax: {
                url: ajaxurl,
                dataType: 'json',
                data: function ( params ) {
                    return {
                        action: 'bnfw_get_terms',
                        taxonomy: taxonomy
                    };
                },
                processResults: function ( data ) {
                    return {
                        results: data
                    };
                }
            }
        } );
        $terms_to.select2( {
            ajax: {
                url: ajaxurl,
                dataType: 'json',
                data: function ( params ) {
                    return {
                        action: 'bnfw_get_terms',
                        taxonomy: taxonomy
                    };
                },
                processResults: function ( data ) {
                    return {
                        results: data
                    };
                }
            }
        } );
    }

    /**
     * Load Terms.
     *
     * @param taxonomy
     */
    function loadTerms( taxonomy ) {
        $terms.select2( {
            ajax: {
                url: ajaxurl,
                dataType: 'json',
                data: function ( params ) {
                    return {
                        action: 'bnfw_get_terms',
                        taxonomy: taxonomy
                    };
                },
                processResults: function ( data ) {
                    return {
                        results: data
                    };
                }
            }
        } );
    }

    /**
     * Show/Hide Subject when a user role is selcted for New User Created For User notification (Miltisite addon).
     */

    function handleSubject() {
        var notification = $notification.val();
        var selectedRole = $( "#new-user-role" ).value;

        if ( 'multisite-new-user-welcome' === notification ) {
            if ( selectedRole !== '0' ) {
                $( '#subject-wrapper' ).hide();
            } else {
                $( '#subject-wrapper' ).show();
            }
        }
    }

    handleSelects();
    $notification.on( 'change', handleSelects );
    $taxonomy.on( 'change', function () {
        var taxonomy = $taxonomy.val();
        loadTerms( taxonomy );
    } );
    $newtaxonomy.on( 'change', function () {
        $( '#bnfw-taxonomy-term-select-from' ).show();
        var taxonomy = $newtaxonomy.val();
        console.log( taxonomy );
        loadNewTerms( taxonomy );
    } );
    handleSubject();
    $( '#new-user-role' ).on( 'change', handleSubject );
} );