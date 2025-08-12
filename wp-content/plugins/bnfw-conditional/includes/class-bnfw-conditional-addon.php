<?php

/**
 * Class BNFW_Conditional_Addon.
 *
 * @since 1.0
 */
class BNFW_Conditional_Addon {

    /**
     * Load everything.
     */
    public function load() {
        add_action( 'bnfw_after_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        add_action( 'bnfw_after_notification_dropdown', array( $this, 'add_conditional_selects' ) );
        add_action( 'bnfw_after_notification_dropdown', array( $this, 'add_user_role_selects' ) );
        add_action( 'bnfw_after_notification_dropdown', array( $this, 'add_user_role_select' ) );
        add_action( 'bnfw_after_notification_dropdown', array( $this, 'add_taxonomy_select' ) );

        add_filter( 'bnfw_notification_setting_fields', array( $this, 'add_notification_setting_field' ) );
        add_filter( 'bnfw_notification_setting', array( $this, 'save_notification_setting' ) );

        add_action( 'wp_ajax_bnfw_get_notification_post_type', array( $this, 'ajax_get_notification_post_type' ) );
        add_action( 'wp_ajax_bnfw_get_taxonomies', array( $this, 'ajax_get_taxonomies' ) );
        add_action( 'wp_ajax_bnfw_get_terms', array( $this, 'ajax_get_terms' ) );

        add_filter( 'bnfw_notification_disabled', array( $this, 'disable_notification' ), 10, 3 );

        add_filter( 'bnfw_trigger_admin-role_notification', array( $this, 'disable_notification_based_on_two_roles' ), 10, 4 );
        add_filter( 'bnfw_trigger_user-role_notification', array( $this, 'disable_notification_based_on_two_roles' ), 10, 4 );

        add_filter( 'bnfw_trigger_user-role-added_notification', array( $this, 'disable_notification_user_role_added_on_two_roles' ), 10, 4 );

        add_filter( 'bnfw_trigger_welcome-email_notification', array( $this, 'disable_notification_based_on_role' ), 10, 3 );
        add_filter( 'bnfw_trigger_new-user_notification', array( $this, 'disable_notification_based_on_role' ), 10, 3 );
        add_filter( 'bnfw_trigger_user-login_notification', array( $this, 'disable_notification_based_on_role' ), 10, 3 );
        add_filter( 'bnfw_trigger_multisite_notification_based_on_role', array( $this, 'disable_multisite_notification_based_on_role' ), 10, 3 );
        add_action( 'bnfw_after_notification_options', array( $this, 'add_notification_option' ), 10, 3 );
        add_filter( 'bnfw_notification_name', array( $this, 'set_notification_name' ), 10, 2 );
        add_action( 'pre_post_update', array( $this, 'before_post_update' ), 10 );
        //add_filter( 'bnfw_shortcodes', array( $this, 'handle_shortcodes' ), 10, 4 );
    }

    /**
     * Enqueue additional scripts.
     *
     * @since 1.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script( 'bnfw-conditional-addon', plugins_url( 'assets/js/bnfw-conditional.js', dirname( __FILE__ ) ), array( 'jquery' ), '', true );
    }

    /**
     * Send Notification when a post term is changed.
     *
     * @since 1.2
     *
     * @param int    $post_id      Post id
     *
     */
    public function before_post_update( $post_id ) {
        $bnfw          = BNFW::factory();
        $notifications = $bnfw->notifier->get_notifications( $this->get_notification_details( get_post_type( $post_id ) ) );
        foreach ( $notifications as $notification ) {
            /**
             * BNFW - Whether notification is disabled?
             *
             * @since 1.3.6
             */
            $setting               = $bnfw->notifier->read_settings( $notification->ID );
            $notification_disabled = apply_filters( 'bnfw_notification_disabled', false, $post_id, $setting );
            if ( ! $notification_disabled ) {
                $term_from = $setting[ 'term-changed-from' ];
                $term_to   = $setting[ 'term-changed-to' ];
                $old_terms = get_the_terms( $post_id, $setting[ 'term-taxonomy' ] );
                if ( isset( $setting[ 'term-taxonomy' ] ) && $setting[ 'term-taxonomy' ] == 'category' ) {
                    $new_terms = $_POST[ 'post_category' ];
                    $this->checkConditionalNotification( $old_terms, $term_from,
                                                         $term_to, $new_terms, $setting, $post_id );
                } else {
                    $new_terms = $_POST[ 'tax_input' ][ $setting[ 'term-taxonomy' ] ];

                    if ( is_array( $new_terms ) ) {
                        $this->checkConditionalNotification( $old_terms, $term_from,
                                                             $term_to, $new_terms, $setting, $post_id );
                    } else {
                        $new_terms_id   = array();
                        $new_terms_name = explode( ',', $new_terms );
                        foreach ( $new_terms_name as $key => $new_term_name ) {
                            $term                 = get_term_by( 'name', trim( $new_term_name ), $setting[ 'term-taxonomy' ] );
                            $new_terms_id[ $key ] = $term->term_id;
                        }
                        $this->checkConditionalNotification( $old_terms, $term_from,
                                                             $term_to, $new_terms_id, $setting, $post_id );
                    }
                }
            }
        }
    }

    /**
     * Check conditional for term changed notification.
     *
     * @since 1.2
     *
     * @param array $old_terms    Old post terms.
     * @param int $term_from      Term changed from
     * @param int $term_to        Term changed to
     * @param array $new_terms    New post terms
     * @param array $setting       Settings array.
     * @param int    $post_id      Post id
     *
     * @return string
     */
    public function checkConditionalNotification( $old_terms, $term_from,
                                                  $term_to, $new_terms,
                                                  $setting, $post_id ) {
        $bnfw = BNFW::Factory();
        foreach ( $old_terms as $key => $old_term ) {
            if ( $old_term->term_id == $term_from && in_array( $term_to, $new_terms ) && ! in_array( $old_term->term_id, $new_terms ) ) {
                $bnfw->engine->send_notification( $setting, $post_id );
            }
        }
    }

    /**
     * Handle shortcodes.
     *
     * @since 1.0
     *
     * @param string $message      Message
     * @param string $notification Notification name
     * @param int    $post_id      Post id
     * @param object $engine       BNFW Engine
     *
     * @return string
     */
    public function handle_shortcodes( $message, $notification, $post_id,
                                       $engine ) {
        $bnfw         = BNFW::Factory();
        $notification = explode( '-', $notification );
        if ( 'termchanged' === $notification[ 0 ] ) {
            $message = $engine->post_shortcodes( $message, $post_id );
            return $message;
        }
    }

    /**
     * Build notification name based on post type.
     *
     * @param string $post_type Post type
     *
     * @return string Notification Type
     */
    protected function get_notification_details( $post_type ) {
        return 'termchanged-' . $post_type;
    }

    /**
     * Add Notification to the list.
     *
     * @since 1.0
     *
     * @param string $post_type Post type for which notification should be added.
     * @param string $label     CPT Label.
     * @param string $setting   Notification Settings.
     */
    public function add_notification_option( $post_type, $label, $setting ) {
        $post_taxs = get_object_taxonomies( $post_type, 'objects' );
        if ( count( $post_taxs ) > 0 ) {
            $post_obj = get_post_type_object( $post_type );
            $label    = $post_obj->labels->singular_name;
            ?> 
            <option value="termchanged-<?php echo $post_type; ?>" <?php selected( 'termchanged-' . $post_type, $setting[ 'notification' ] ); ?>><?php esc_html_e( $label . ' Term Changed', 'bnfw' ); ?></option>
            <?php
        }
    }

    /**
     * Set the name of the notification.
     *
     * @since 1.0
     */
    public function set_notification_name( $name, $notification ) {
        $slug = explode( '-', $notification );
        if ( 'termchanged' === $slug[ 0 ] ) {
            $post_type = $this->get_notification_post_type( $notification );
            return __( ucfirst( $post_type ) . ' Term Changed', 'bnfw' );
        }
        return $name;
    }

    /**
     * Add user role conditional selects.
     *
     * @since 1.0.4
     *
     * @param array $setting Settings array.
     */
    public function add_user_role_selects( $setting ) {
        $from_user_role = isset( $setting[ 'from-user-role' ] ) ? $setting[ 'from-user-role' ] : '';
        $to_user_role   = isset( $setting[ 'to-user-role' ] ) ? $setting[ 'to-user-role' ] : '';
        ?>

        <tr valign="top" id="bnfw-user-role-selects" class="hidden">
            <th scope="row">
                <?php _e( 'Send Notification only if', 'bnfw' ); ?>
                <div class="bnfw-help-tip"><p><?php esc_html_e( 'Only send this notification if the user\'s role is changed between these two roles.', 'bnfw' ); ?></p></div>
            </th>

            <td>
                <?php esc_html_e( 'User Role changed from ', 'bnfw' ); ?>
                <select id="from-user-role" name="from-user-role" style="width:20%">
                    <option value="0"><?php esc_html_e( 'Select User Role', 'bnfw' ); ?></option>
                    <?php wp_dropdown_roles( $from_user_role ); ?>
                </select>

                <span><?php _e( 'to', 'bnfw' ); ?></span>

                <select id="to-user-role" name="to-user-role" style="width:20%">
                    <option value="0"><?php esc_html_e( 'Select User Role', 'bnfw' ); ?></option>
                    <?php wp_dropdown_roles( $to_user_role ); ?>
                </select>
            </td>
        </tr>
        <?php
    }

    /**
     * Add user role conditional select.
     *
     * @since 1.0.4
     *
     * @param array $setting Settings array.
     */
    public function add_user_role_select( $setting ) {
        $new_user_role = isset( $setting[ 'new-user-role' ] ) ? $setting[ 'new-user-role' ] : '';
        ?>

        <tr valign="top" id="bnfw-user-role-select" class="hidden">
            <th scope="row">
                <?php _e( 'Send Notification only if the user belongs to', 'bnfw' ); ?>
                <div class="bnfw-help-tip"><p><?php esc_html_e( 'Only send this notification if the user is assigned to this user role.', 'bnfw' ); ?></p></div>
            </th>

            <td>
                <select id="new-user-role" name="new-user-role" style="width:20%">
                    <option value="0"><?php esc_html_e( 'All User Roles', 'bnfw' ); ?></option>
                    <?php wp_dropdown_roles( $new_user_role ); ?>
                </select>
            </td>
        </tr>
        <?php
    }

    /**
     * Add Taxonomy Term conditional select.
     *
     * @since 1.0.4
     *
     * @param array $setting Settings array.
     */
    public function add_taxonomy_select( $setting ) {
        $css_class         = 'hidden';
        $taxonomies        = '';
        $select_taxonomies = isset( $setting[ 'term-taxonomy' ] ) ? $setting[ 'term-taxonomy' ] : '';
        $select_terms_from = isset( $setting[ 'term-changed-from' ] ) ? $setting[ 'term-changed-from' ] : '';
        $select_terms_to   = isset( $setting[ 'term-changed-to' ] ) ? $setting[ 'term-changed-to' ] : '';

        if ( ! empty( $select_taxonomies ) ) {
            $post_type = $this->get_notification_post_type( $setting[ 'notification' ] );
            $css_class = '';
        }
        ?>

        <tr valign="top" id="bnfw-taxonomy-term-select" class="<?php echo $css_class; ?>">
            <th scope="row">
                <?php _e( 'Send Notification only if the post belongs to', 'bnfw' ); ?>
                <div class="bnfw-help-tip"><p><?php esc_html_e( 'Only send this notification if the post is assigned to this taxonomy and if term is changed between selected two terms.', 'bnfw' ); ?></p></div>
            </th>

            <td>
                <select id="bnfw-post-term-taxonomy" name="bnfw-post-term-taxonomy" style="width:27%">
                    <option value=""><?php esc_html_e( '- Choose Taxonomy -', 'bnfw' ); ?></option>
                    <?php
                    if ( ! empty( $post_type ) ) {
                        $taxonomies = get_object_taxonomies( $post_type, 'objects' );

                        foreach ( $taxonomies as $taxonomy ) {
                            ?>
                            <option
                                value="<?php echo $taxonomy->name; ?>" <?php selected( $taxonomy->name, $select_taxonomies ); ?>>
                                    <?php echo $taxonomy->label; ?>
                            </option>
                            <?php
                        }
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr valign="top" id="bnfw-taxonomy-term-select-from" class="<?php echo $css_class; ?>">
            <th scope="row">
                <?php esc_html_e( 'Term changed from ', 'bnfw' ); ?>
            </th>

            <td>
                <select id="bnfw-terms-changed-from" name="bnfw-terms-changed-from" style="min-width: 250px;" size="1"
                        data-placeholder="<?php _e( 'Any', 'bnfw' ); ?>">
                            <?php
                            if ( ! empty( $post_type ) && ! empty( $select_taxonomies ) ) {
                                $terms = get_terms( array( 'taxonomy' => $select_taxonomies, 'hide_empty' => false ) );

                                if ( ! is_wp_error( $terms ) ) {
                                    foreach ( $terms as $term ) {
                                        ?>
                                <option
                                    value="<?php echo $term->term_id; ?>" <?php selected( $term->term_id, $select_terms_from ); ?>>
                                        <?php echo $term->name; ?>
                                </option>
                                <?php
                            }
                        }
                    }
                    ?>
                </select>
                <span> to </span>
                <select id="bnfw-terms-changed-to" name="bnfw-terms-changed-to" style="min-width: 250px;" size="1"
                        data-placeholder="<?php _e( 'Any', 'bnfw' ); ?>">
                            <?php
                            if ( ! empty( $post_type ) && ! empty( $select_taxonomies ) ) {
                                $terms = get_terms( array( 'taxonomy' => $select_taxonomies, 'hide_empty' => false ) );

                                if ( ! is_wp_error( $terms ) ) {
                                    foreach ( $terms as $term ) {
                                        ?>
                                <option
                                    value="<?php echo $term->term_id; ?>" <?php selected( $term->term_id, $select_terms_to ); ?>>
                                        <?php echo $term->name; ?>
                                </option>
                                <?php
                            }
                        }
                    }
                    ?>
                </select>
            </td>
        </tr>
        <?php
    }

    /**
     * Add conditional selects.
     *
     * @since 1.0
     *
     * @param array $setting Settings array.
     */
    public function add_conditional_selects( $setting ) {
	    $post_type         = '';
	    $select_taxonomies = $setting['taxonomies'] ?? '';
	    $terms_relations   = $setting['terms-relation'] ?? '';
	    $select_terms      = $setting['terms'] ?? array();
	    $css_class         = 'hidden';

        if ( ! empty( $select_taxonomies ) ) {
            $post_type = $this->get_notification_post_type( $setting[ 'notification' ] );
            $css_class = '';
        }
        ?>

        <style>
            #bnfw-terms + .select2 {
                /* width: 100% !important; */
                max-width: 935px !important;
            }
        </style>

        <tr valign="top" id="bnfw-conditional-selects" class="<?php echo $css_class; ?>">
            <th scope="row">
                <?php _e( 'Send Notification only if', 'bnfw' ); ?>
                <div class="bnfw-help-tip"><p><?php esc_html_e( 'Only send this notification if the post/page is in a chosen category / tag / taxonomy. You can limit the notification by one or multiple categories / tags / terms. You can leave these fields blank if you don\'t want to use them for this notification.', 'bnfw' ); ?></p></div>
            </th>
            <td>
                <select id="bnfw-taxonomies" name="bnfw-taxonomies" style="width:20%" data-placeholder="<?php _e( 'Select Taxonomy', 'bnfw' ); ?>">
                    <option value="-1" <?php selected( '-1', $select_taxonomies ); ?>>
                        <?php _e( '- Choose Taxonomy -', 'bnfw' ); ?>
                    </option>
                    <?php
                    if ( ! empty( $post_type ) ) {
                        $taxonomies = get_object_taxonomies( $post_type, 'objects' );

                        foreach ( $taxonomies as $taxonomy ) {
                            ?>
                            <option
                                value="<?php echo $taxonomy->name; ?>" <?php selected( $taxonomy->name, $select_taxonomies ); ?>>
                                    <?php echo $taxonomy->label; ?>
                            </option>
                            <?php
                        }
                    }
                    ?>
                </select>

                <span><?php _e( 'match', 'bnfw' ); ?></span>

	            <select name="bnfw-terms-relation" id="bnfw-terms-relation" style="min-width: 70px;" size="1">
		            <option value="any" <?php selected( 'any', $terms_relations ); ?>><?php _e( 'Any', 'bnfw' ); ?></option>
		            <option value="all" <?php selected( 'all', $terms_relations ); ?>><?php _e( 'All', 'bnfw' ); ?></option>
	            </select>

                <select id="bnfw-terms" name="bnfw-terms[]" multiple="multiple" style="min-width: 48.1%;" size="1"
                        data-placeholder="<?php _e( 'Select', 'bnfw' ); ?>">
                            <?php
                            if ( ! empty( $post_type ) && ! empty( $select_taxonomies ) ) {
                                $terms = get_terms( array( 'taxonomy' => $select_taxonomies, 'hide_empty' => false ) );

                                if ( ! is_wp_error( $terms ) ) {
                                    foreach ( $terms as $term ) {
                                        ?>
                                <option
                                    value="<?php echo $term->term_id; ?>" <?php selected( in_array( $term->term_id, $select_terms ) ); ?>>
                                        <?php echo $term->name; ?>
                                </option>
                                <?php
                            }
                        }
                    }
                    ?>
                </select>
            </td>
        </tr>
        <?php
    }

    /**
     * Get post type from Notification name.
     *
     * @param string $notification Notification name.
     *
     * @return string Post type.
     */
    private function get_notification_post_type( $notification ) {
        $post_type              = '';
        $moderate_post_prefixes = array();
        $approve_post_prefixes  = array();
        $post_prefixes          = array( 'new', 'update', 'pending', 'private', 'future', 'comment', 'moderate', 'commentreply' );
        $post_notifications     = array( 'new-comment', 'moderate-post-comment', 'approve-comment', 'new-trackback', 'new-pingback', 'reply-comment' );
        $exclude                = array( 'new-user', 'multisite-new-user-invited' );

        $post_types = get_post_types();
        foreach ( $post_types as $key => $existing_post_type ) {
            $moderate_post_prefixes[] = 'moderate-' . esc_attr( $existing_post_type ) . '-comment';
            $approve_post_prefixes [] = 'approve-' . esc_attr( $existing_post_type ) . '-comment';
            $term_notification []     = 'termchanged-' . esc_attr( $existing_post_type );
        }

        if ( ! in_array( $notification, $exclude ) ) {
            if ( in_array( $notification, $post_notifications ) ) {
                $post_type = 'post';
            } else {
                $splits = explode( '-', $notification );
                if ( count( $splits ) >= 2 ) {
                    if ( in_array( $splits[ 0 ], $post_prefixes ) ) {
                        $post_type = implode( '-', array_slice( $splits, 1 ) );
                    }
                }
            }
        }

        if ( in_array( $notification, $moderate_post_prefixes ) || in_array( $notification, $approve_post_prefixes ) ) {
            $splits = explode( '-', $notification );
            $count  = count( $splits );

            if ( $count >= 2 ) {
                unset( $splits[ 0 ] );
                unset( $splits[ $count - 1 ] );
                $post_type = implode( '-', $splits );
            }
        }

        if ( in_array( $notification, $term_notification ) ) {
            $splits = explode( '-', $notification );
            $count  = count( $splits );

            if ( $count >= 2 ) {
                $post_type = $splits[ 1 ];
            }
        }

        return apply_filters( 'bnfw_notification_post_type', $post_type, $notification );
    }

    /**
     * Is the notification a comment notification?
     *
     * @param string $notification Notification name.
     *
     * @return bool True if it is a comment notification, False otherwise.
     */
    private function is_comment_notification( $notification ) {
        $is_comment_notification = false;
        $comment_notifications   = array( 'new-comment', 'moderate-post-comment', 'new-trackback', 'new-pingback', 'reply-comment' );
        $comment_prefixes        = array( 'comment', 'moderate', 'commentreply', 'approve' );

        if ( in_array( $notification, $comment_notifications ) ) {
            $is_comment_notification = true;
        } else {
            $splits = explode( '-', $notification );
            if ( count( $splits ) >= 2 ) {
                if ( in_array( $splits[ 0 ], $comment_prefixes ) ) {
                    $is_comment_notification = true;
                }
            }
        }

        return apply_filters( 'bnfw_comment_notification', $is_comment_notification, $notification );
    }

    /**
     * Handle ajax request to get notification post type.
     */
    public function ajax_get_notification_post_type() {
        $notification = sanitize_text_field( $_GET[ 'notification' ] );

        echo $this->get_notification_post_type( $notification );
        wp_die();
    }

    /**
     * Get the list of taxonomies for a post type.
     */
    public function ajax_get_taxonomies() {
        $data       = array();
        $post_type  = sanitize_text_field( $_GET[ 'post_type' ] );
        $taxonomies = get_object_taxonomies( $post_type, 'objects' );

        foreach ( $taxonomies as $taxonomy ) {
            $data[] = array(
                'id'   => $taxonomy->name,
                'text' => $taxonomy->label,
            );
        }

        echo json_encode( $data );
        wp_die();
    }

    /**
     * Get the list of terms for a taxonomy.
     */
    public function ajax_get_terms() {
        $data     = array();
        $taxonomy = sanitize_text_field( $_GET[ 'taxonomy' ] );
        $terms    = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );

        foreach ( $terms as $term ) {
            $data[] = array(
                'id'   => $term->term_id,
                'text' => $term->name,
            );
        }

        echo json_encode( $data );
        wp_die();
    }

    /**
     * Add new fields to notification settings.
     *
     * @since 1.0
     *
     * @param array $fields List of existing fields.
     *
     * @return array New list of fields.
     */
    public function add_notification_setting_field( $fields ) {
	    $fields['taxonomies']      = '';
	    $fields['terms-relation'] = '';
	    $fields['terms']           = array();

        $fields[ 'from-user-role' ] = '';
        $fields[ 'to-user-role' ]   = '';

        $fields[ 'new-user-role' ] = '';

        $fields[ 'term-taxonomy' ]     = '';
        $fields[ 'term-changed-from' ] = '';
        $fields[ 'term-changed-to' ]   = '';

        return $fields;
    }

    /**
     * Save Notification setting.
     *
     * @since 1.0
     *
     * @param array $setting Notification setting
     *
     * @return array Modified Notification setting
     */
    public function save_notification_setting( $setting ) {

        if ( isset( $_POST[ 'bnfw-taxonomies' ] ) ) {
            $setting[ 'taxonomies' ] = sanitize_text_field( $_POST[ 'bnfw-taxonomies' ] );
        } else {
            $setting[ 'taxonomies' ] = '-1';
        }

        if ( (isset( $_POST[ 'bnfw-post-term-taxonomy' ] ) && $_POST[ 'bnfw-post-term-taxonomy' ]) && (isset( $_POST[ 'bnfw-terms-changed-from' ] ) && $_POST[ 'bnfw-terms-changed-from' ]) && (isset( $_POST[ 'bnfw-terms-changed-to' ] ) && $_POST[ 'bnfw-terms-changed-to' ]) ) {
            $setting[ 'term-taxonomy' ]     = sanitize_text_field( $_POST[ 'bnfw-post-term-taxonomy' ] );
            $setting[ 'term-changed-from' ] = $_POST[ 'bnfw-terms-changed-from' ];
            $setting[ 'term-changed-to' ]   = $_POST[ 'bnfw-terms-changed-to' ];
        }

	    $setting[ 'terms-relation' ] = isset( $_POST[ 'bnfw-terms-relation' ] ) ? sanitize_text_field( $_POST[ 'bnfw-terms-relation' ] ) : '';

        if ( isset( $_POST[ 'bnfw-terms' ] ) ) {
            if ( ! is_array( $_POST[ 'bnfw-terms' ] ) ) {
                $terms = array( $_POST[ 'bnfw-terms' ] );
            } else {
                $terms = $_POST[ 'bnfw-terms' ];
            }

            $setting[ 'terms' ] = array_map( 'absint', $terms );
        } else {
            $setting[ 'terms' ] = array();
        }

        if ( isset( $_POST[ 'from-user-role' ] ) ) {
            $setting[ 'from-user-role' ] = sanitize_text_field( $_POST[ 'from-user-role' ] );
        } else {
            $setting[ 'from-user-role' ] = '';
        }

        if ( isset( $_POST[ 'to-user-role' ] ) ) {
            $setting[ 'to-user-role' ] = sanitize_text_field( $_POST[ 'to-user-role' ] );
        } else {
            $setting[ 'to-user-role' ] = '';
        }

        if ( isset( $_POST[ 'new-user-role' ] ) ) {
            $setting[ 'new-user-role' ] = sanitize_text_field( $_POST[ 'new-user-role' ] );
        } else {
            $setting[ 'new-user-role' ] = '';
        }

        return $setting;
    }

    /**
     * Should a notification be disabled.
     *
     * @param bool  $disabled Current disabled state.
     * @param int   $id       Post id.
     * @param array $setting  Notification settings.
     *
     * @return bool True if notification should be disabled, False otherwise
     */
    public function disable_notification( $disabled, $id, $setting ) {
        $post_type = $this->get_notification_post_type( $setting[ 'notification' ] );
        $post_id   = $id;

        if ( ! empty( $post_type ) ) {
            if (
				isset( $setting[ 'taxonomies' ] ) &&
				'-1' != $setting[ 'taxonomies' ] &&
				! empty( $setting[ 'terms' ] ) &&
				is_array( $setting[ 'terms' ] )
            ) {

                if ( $this->is_comment_notification( $setting[ 'notification' ] ) ) {
                    $the_comment = get_comment( $id );
                    $post_id     = $the_comment->comment_post_ID;
                }

                $terms = get_the_terms( $post_id, $setting[ 'taxonomies' ] );

	            if ( 'all' === $setting['terms-relation'] ) {
		            $term_ids = array_column( $terms, 'term_id' );

		            if ( ! $disabled && empty( array_diff( $setting['terms'], $term_ids ) ) ) {
			            return false;
		            }
	            } else {
		            if ( is_array( $terms ) ) {
			            foreach ( $terms as $term ) {
				            if ( ! $disabled && in_array( $term->term_id, $setting['terms'] ) ) {
					            return false;
				            }
			            }
		            }
	            }

                return true;
            }
        }

        return $disabled;
    }

    /**
     * Disable new user notification based on user role.
     *
     * @param bool     $enabled True, if enabled, false otherwise.
     * @param array    $setting Notification settings.
     * @param \WP_User $user    User object.
     *
     * @return bool True, if enabled, False otherwise.
     */
    public function disable_new_user_notification( $enabled, $setting, $user ) {
        $user_role = $setting[ 'new-user-role' ];

        if ( empty( $user_role ) ) {
            return $enabled;
        }

        if ( empty( $user->roles ) or ! is_array( $user->roles ) ) {
            return $enabled;
        }

        return in_array( $user_role, $user->roles );
    }

    /**
     * Disable user role notification if needed.
     *
     * @param bool     $enabled      True, if enabled, false otherwise.
     * @param \WP_Post $notification Notification Post object.
     * @param string   $new_role     New user role.
     * @param array    $old_roles    Old user roles.
     *
     * @return bool True, if enabled, False otherwise.
     */
    public function disable_notification_based_on_two_roles( $enabled,
                                                             $notification,
                                                             $new_role,
                                                             $old_roles ) {
        $bnfw    = BNFW::factory();
        $setting = $bnfw->notifier->read_settings( $notification->ID );

        $from_user_role = $setting[ 'from-user-role' ];
        $to_user_role   = $setting[ 'to-user-role' ];

        if ( empty( $from_user_role ) && empty( $to_user_role ) ) {
            return $enabled;
        }

        if ( empty( $from_user_role ) ) {
            return ( $new_role === $to_user_role );
        }

        if ( empty( $to_user_role ) ) {
            return in_array( $from_user_role, $old_roles );
        }

        return ( ( $new_role === $to_user_role ) ) && ( in_array( $from_user_role, $old_roles ) );
    }

    /**
     * Disable user role notification if needed added support User Role Editor by Members Plugin..
     *
     * @param bool     $enabled      True, if enabled, false otherwise.
     * @param \WP_Post $notification Notification Post object.
     * @param string   $new_role     New user role.
     * @param array    $old_roles    Old user roles.
     *
     * @return bool True, if enabled, False otherwise.
     */
    public function disable_notification_user_role_added_on_two_roles( $enabled,
                                                                       $notification,
                                                                       $new_roles,
                                                                       $old_roles ) {
        $bnfw    = BNFW::factory();
        $setting = $bnfw->notifier->read_settings( $notification->ID );

        $from_user_role = $setting[ 'from-user-role' ];
        $to_user_role   = $setting[ 'to-user-role' ];

        if ( empty( $from_user_role ) && empty( $to_user_role ) ) {
            return $enabled;
        }

        if ( empty( $from_user_role ) ) {
            return ( in_array( $to_user_role, $new_roles ) );
        }

        if ( empty( $to_user_role ) ) {
            return in_array( $from_user_role, $new_roles );
        }

        $to_array = array_diff( $new_roles, $old_roles );

        $from_array = array_diff( $old_roles, $new_roles );

        if ( in_array( $from_user_role, $from_array ) && in_array( $to_user_role, $to_array ) ) {
            return $enabled;
        } else {
            /**
             * Return notification status if to "Role" is added in the user with multiple roles
             * @since 1.0.16
             */
            if(in_array($from_user_role, $old_roles) && in_array($to_user_role, $new_roles)){
                if((in_array($from_user_role, $old_roles) && in_array($to_user_role, $old_roles)) && (in_array($from_user_role, $new_roles) && in_array($to_user_role, $new_roles))){
                    return false;
                }
                return $enabled;
            }

            return false;
        }
    }

    /**
     * Should the welcome email notification be disabled?
     *
     * @param bool     $enabled Is the welcome email notification disabled? Default True.
     * @param array    $setting Notification setting.
     * @param \WP_User $user    User object.
     *
     * @return bool Whether the welcome email notification should be disabled.
     */
    public function disable_notification_based_on_role( $enabled, $setting,
                                                        $user ) {
        $new_user_role = $setting[ 'new-user-role' ];

        if ( empty( $new_user_role ) ) {
            return $enabled;
        }

        if ( empty( $user->roles ) or ! is_array( $user->roles ) ) {
            return $enabled;
        }

        if ( ! in_array( $new_user_role, $user->roles ) ) {
            return false;
        }

        return $enabled;
    }

    /**
     * Should the welcome email notification be disabled for multisite?
     *
     * @param bool     $enabled Is the welcome email notification disabled? Default True.
     * @param array    $setting Notification setting.
     * @param \WP_User $user    User object.
     *
     * @return bool Whether the welcome email notification should be disabled.
     */
    public function disable_multisite_notification_based_on_role( $enabled,
                                                                  $setting,
                                                                  $user ) {
        $new_user_role = $setting[ 'new-user-role' ];

        if ( empty( $new_user_role ) ) {
            return $enabled;
        }

        if ( empty( $user[ 'roles' ] ) or ! is_array( $user[ 'roles' ] ) ) {
            return $enabled;
        }

        if ( ! in_array( $new_user_role, $user[ 'roles' ] ) ) {
            return false;
        }

        return $enabled;
    }

}