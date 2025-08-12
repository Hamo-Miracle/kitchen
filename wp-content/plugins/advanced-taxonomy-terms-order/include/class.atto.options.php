<?php

    if ( ! defined( 'ABSPATH' ) ) { exit;}
    
    class ATTO_options_interface
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
                    
                    add_action( 'init',         array($this, 'options_update'), 1 );
                }        
            
            
            function admin_styles()
                {
                    wp_register_style('options.css', ATTO_URL . '/css/options.css');
                    wp_enqueue_style( 'options.css');
                }
            
            
            function options_update()
                {
                    
                    if (isset($_POST['atto_licence_form_submit']))
                        {
                            $this->licence_form_submit();
                        }
                    
                    if (isset($_POST['atto_form_submit']) &&  wp_verify_nonce($_POST['atto_form_nonce'],'atto_form_submit') )
                        {
                            $this->options_submit();                                 
                        }   
                    
                }
            
            
            function options_interface()
                {
             
                    $options = ATTO_functions::get_settings();
                        
                    //build an array containing the user role and capability
                    $user_roles = array();
                    $user_roles['Subscriber']       = apply_filters('atto_user_role_capability', 'read',            __('Subscriber', 'atto'));
                    $user_roles['Contributor']      = apply_filters('atto_user_role_capability', 'edit_posts',      __('Contributor', 'atto'));
                    $user_roles['Author']           = apply_filters('atto_user_role_capability', 'publish_posts',   __('Author', 'atto'));
                    $user_roles['Editor']           = apply_filters('atto_user_role_capability', 'publish_pages',   __('Editor', 'atto'));
                    $user_roles['Administrator']    = apply_filters('atto_user_role_capability', 'manage_options',  __('Administrator', 'atto'));
                    
                    //allow to add custom roles
                    $user_roles = apply_filters( 'atto/interface/options/atto_user_roles_and_capabilities', $user_roles );
                                    
                    ?>
                        <div class="wrap"> 
                            
                            
                            <?php
                            
                            
                                if( !   $this->licence->licence_key_verify())
                                    {
                                        $this->licence_form();
                                        return;
                                    }
                                    
                                if( $this->licence->licence_key_verify())
                                    {
                                        $this->licence_deactivate_form();
                                    }
                            
                            
                            ?>
                            
                            <form id="form_data" name="form" method="post">
                                <br />
                                <h2><?php _e('General Settings', 'atto') ?></h2>
                                <table class="form-table">
                                    <tbody>
                                        <tr valign="top">
                                            <th scope="row" style="text-align: right;"><label><?php esc_html_e('Show / Hide re-order interface', 'atto') ?></label></th>
                                            <td>
                                                <p><?php esc_html_e("Display the Taxonomy Order interface for the specified post types.", 'atto') ?></p>
                                                <br />
                                                <div class="pt-list">
                                                <?php
                                            
                                                    foreach ($this->functions->get_available_menu_locations() as $location    =>  $location_data)
                                                        {
                                                            ?>
                                                            <p class="pt-item"><label>
                                                                <select name="show_reorder_interfaces[<?php echo $location ?>]">
                                                                    <option value="show" <?php if(isset($options['show_reorder_interfaces'][$location]) && $options['show_reorder_interfaces'][$location] == 'show') {echo ' selected="selected"';} ?>><?php _e( "Show", 'atto' ) ?></option>
                                                                    <option value="hide" <?php if(isset($options['show_reorder_interfaces'][$location]) && $options['show_reorder_interfaces'][$location] == 'hide') {echo ' selected="selected"';} ?>><?php _e( "Hide", 'atto' ) ?></option>
                                                                </select> &nbsp;&nbsp;<?php echo esc_html ( $location_data['name'] ); ?>
                                                            </label><br />&nbsp;</p>
                                                            <?php
                                                        }
                                                
                                                ?>
                                                </div>
                                            </td>
                                             
                                        </tr>

                                        <tr valign="top">
                                            <th scope="row" style="text-align: right;"><label><?php _e( "Minimum Level to use this plugin", 'atto' ) ?></label></th>
                                            <td>
                                                <select id="role" name="capability">
                                                    <?php

                                                        foreach ($user_roles as $user_role => $user_capability)
                                                            {
                                                                ?><option value="<?php echo $user_capability ?>" <?php if (isset($options['capability']) && $options['capability'] == $user_capability) echo 'selected="selected"'?>><?php _e($user_role, 'atto') ?></option><?php   
                                                            }



                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        
                                        
                                        <tr valign="top">
                                            <th scope="row" style="text-align: right;"><label><?php _e('Auto Sort', 'atto') ?></label></th>
                                            <td>
                                                <label for="autosort">
                                                    <select id="autosort" name="autosort">
                                                        <option value="0" <?php if ($options['autosort'] == "0") echo 'selected="selected"'?>><?php _e('OFF', 'atto') ?></option>
                                                        <option value="1" <?php if ($options['autosort'] == "1") echo 'selected="selected"'?>><?php _e('ON', 'atto') ?></option>
                                                    </select> <?php _e('If checked, the plug-in automatically update the WordPress queries to use the new sort (<b>No code update is necessarily</b>)', 'atto') ?>. <?php _e('Additional details about this setting can be found at', 'atto') ?> <a href="http://www.nsp-code.com/how-to-use-the-autosort-setting-for-advanced-taxonomy-terms-order/" target="_blank">Autosort Details</a>
                                                    <?php _e('If ON selected, the plug-in automatically update the WordPress queries to use the new sort (<b>No code update is necessary</b>)', 'atto') ?>. <?php printf( __('Additional details about this setting can be found at <a href="%s" target="_blank">Autosort Details</a>', 'atto') , esc_url('http://www.nsp-code.com/how-to-use-the-autosort-setting-for-advanced-taxonomy-terms-order/') ); ?>
                                                </label>
                                            </td>
                                        </tr>
                                        
                                        <tr valign="top">
                                            <th scope="row" style="text-align: right;"><label><?php _e('Admin Sort', 'atto') ?></label></th>
                                            <td>
                                                <label for="adminsort">
                                                    <select id="adminsort" name="adminsort">
                                                        <option value="0" <?php if ($options['adminsort'] == "0") echo 'selected="selected"'?>><?php _e('OFF', 'atto') ?></option>
                                                        <option value="1" <?php if ($options['adminsort'] == "1") echo 'selected="selected"'?>><?php _e('ON', 'atto') ?></option>
                                                    </select>
                                                    <?php _e("Update order of terms within the admin interface per customised sort", 'atto') ?>.
                                                </label>
                                            </td>
                                        </tr>
                                        
                                        <tr valign="top">
                                            <th scope="row" style="text-align: right;"><label><?php _e('Default Interface Sort', 'atto') ?></label></th>
                                            <td>
                                                <p>
                                                <?php esc_html_e("Enable sortable drag-and-drop functionality within the default WordPress taxonomy interface. An " ); ?><img style="max-width: 15px; vertical-align: middle;" class="atto-dd-icon" src="<?php echo ATTO_URL ?>/images/grip_icon.png" /><?php  esc_html_e(" icon will be displayed on the items, allowing for customization of the order. The option can be further adjusted by the users, through the Screen Options on the taxonomy page.", 'atto') ?>
                                                <br />
                                                <?php  esc_html_e("Admin Sort feature must be activated for this functionality.", 'atto') ?>
                                                </p>
                                                <br />
                                                <div class="pt-list">
                                                <?php
                                                    
                                                    $args = array(
                                                                      'show_ui' => true,
                                                                      
                                                                    );
                                                    $taxonomies = get_taxonomies( $args, 'objects' );

                                                    foreach ( $taxonomies as $taxonomy ) 
                                                        {
                                                            ?>
                                                            <p class="pt-item"><label>
                                                                <select name="allow_reorder_default_interfaces[<?php echo $taxonomy->name ?>]">
                                                                    <option value="yes" <?php if(isset($options['allow_reorder_default_interfaces'][$taxonomy->name]) && $options['allow_reorder_default_interfaces'][$taxonomy->name] === 'yes') {echo ' selected="selected"';} ?>><?php esc_html_e( "Yes", 'atto' ) ?></option>
                                                                    <option value="no" <?php if(isset($options['allow_reorder_default_interfaces'][$taxonomy->name]) && $options['allow_reorder_default_interfaces'][$taxonomy->name] === 'no') {echo ' selected="selected"';} ?>><?php esc_html_e( "No", 'atto' ) ?></option>
                                                                </select> &nbsp;&nbsp;<?php echo esc_html ( $taxonomy->label ); ?>
                                                            </label><br />&nbsp;</p><?php  
                                                        
                                                        } ?>
                                                </div>
                                            </td>
                                            
                                        </tr>
                                        
                                        <?php if( defined ( 'ICL_LANGUAGE_CODE' ) && defined( 'ICL_SITEPRESS_VERSION' ) ) { ?>
                                        <tr valign="top">
                                            <th scope="row" style="text-align: right;"><label><?php _e('WPML Sort Synchronization', 'atto') ?></label></th>
                                            <td>
                                                <label for="wpml_sort_synchronization">
                                                    <select id="wpml_sort_synchronization" name="wpml_sort_synchronization">
                                                        <option value="0" <?php if ($options['wpml_sort_synchronization'] === "0") echo 'selected="selected"'?>><?php _e('OFF', 'atto') ?></option>
                                                        <option value="1" <?php if ($options['wpml_sort_synchronization'] === "1") echo 'selected="selected"'?>><?php _e('ON', 'atto') ?></option>
                                                    </select>
                                                    <?php _e("The WPML Sort Synchronization feature ensures that custom sorting applied to taxonomy terms is seamlessly synchronized across all translations managed by the WPML (WordPress Multilingual) plugin", 'atto') ?>.
                                                </label>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                                
                                   
                                <p class="submit">
                                    <input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Settings', 'atto') ?>">
                                </p>
                            
                                
                                <?php wp_nonce_field('atto_form_submit','atto_form_nonce'); ?>
                                <input type="hidden" name="atto_form_submit" value="true" />
                                
                            </form>
                            
                        </div>
                        
                    <?php          
                    
                }
                
                
            
            function options_submit()
                {
                    global $atto_interface_messages;
                        
                    $options = ATTO_functions::get_settings();
                    $previous_options   =   $options;
                    
                    $options['show_reorder_interfaces']             =   preg_replace( '/[^a-z]/', '', $_POST['show_reorder_interfaces'] );
                    
                    $options['capability']                          =   preg_replace( '/[^a-zA-Z0-9_\-]/', '', $_POST['capability'] );
                            
                    $options['autosort']                            =   isset($_POST['autosort'])                 ? preg_replace( '/[^a-zA-Z0-9_\-]/', '', $_POST['autosort'] )                 : '';
                    $options['adminsort']                           =   isset($_POST['adminsort'])                ? preg_replace( '/[^a-zA-Z0-9_\-]/', '', $_POST['adminsort'] )                : '';
                    
                    $options['allow_reorder_default_interfaces']    =   preg_replace( '/[^a-z]/', '', (array) $_POST['allow_reorder_default_interfaces'] );
                    
                    $options['wpml_sort_synchronization']           =   isset($_POST['wpml_sort_synchronization'])                ? preg_replace( '/[^0-9]/', '', $_POST['wpml_sort_synchronization'] )                : '0';
                    
                    $atto_interface_messages[] = array(
                                                                                    'type'  =>  'updated',
                                                                                    'text'  =>  __('Settings Saved', 'atto'));

                    ATTO_functions::update_settings( $options );    
                    
                }
                
                
            function licence_form_submit()
                {
                    global $atto_interface_messages; 
                    
                    //check for de-activation
                    if (isset($_POST['atto_licence_form_submit']) && isset($_POST['atto_licence_deactivate']) && wp_verify_nonce($_POST['atto_license_nonce'],'atto_licence'))
                        {
                            
                            $licence_data = get_site_option('atto_licence');                        
                            $licence_key = $licence_data['key'];

                            //build the request query
                            $args = array(
                                                'woo_sl_action'           => 'deactivate',
                                                'licence_key'           => $licence_key,
                                                'product_unique_id'     => ATTO_PRODUCT_ID,
                                                'domain'                => ATTO_INSTANCE
                                            );
                            $request_uri    = ATTO_UPDATE_API_URL . '?' . http_build_query( $args , '', '&');
                            $data           = wp_remote_get( $request_uri );
                            
                            if(is_wp_error( $data ) || $data['response']['code'] != 200)
                                {
                                    $atto_interface_messages[] = array(
                                                                            'type'  =>  'error',
                                                                            'text'  =>  sprintf( __('There was a problem connecting to %s', 'atto'), ATTO_UPDATE_API_URL));
                                    return;  
                                }
                                
                            $response_block = json_decode($data['body']);
                            $response_block = $response_block[count($response_block) - 1];
                            
                            if(isset($response_block->status))
                                {
                                    if($response_block->status == 'success' && $response_block->status_code == 's201')
                                        {
                                            //the license is active and the software is active
                                            $atto_interface_messages[] = array(
                                                                                    'type'  =>  'updated',
                                                                                    'text'  =>  $response_block->message);
                                            
                                            $licence_data = get_site_option('atto_licence');
                                            
                                            //save the license
                                            $licence_data['key']          = '';
                                            $licence_data['last_check']   = time();
                                            
                                            update_site_option('atto_licence', $licence_data);
                                        }
                                        else
                                        {
                                            $atto_interface_messages[] = __('There was a problem deactivating the licence on other side, however locally the key is now removed: ', 'atto') . $response_block->message;
                                            
                                            $licence_data = get_site_option('atto_licence');
                                            
                                            //save the license
                                            $licence_data['key']          = '';
                                            $licence_data['last_check']   = time();
                                            
                                            update_site_option('atto_licence', $licence_data);
                                            
                                            return;
                                        }   
                                }
                                else
                                {
                                    $atto_interface_messages[] =   array(  
                                                                                    'type'  =>  'error',
                                                                                    'text'  =>  sprintf(__('There was a problem with the data block received from %s', 'atto'), ATTO_UPDATE_API_URL));
                                    return;
                                }
                                       
                        }   
                    

                    
                    if (isset($_POST['atto_licence_form_submit']) && isset($_POST['atto_licence_activate']) && wp_verify_nonce($_POST['atto_license_nonce'],'atto_licence'))
                        {
                            
                            $licence_key = isset($_POST['licence_key'])? preg_replace( '/[^a-zA-Z0-9_\-]/', '', trim($_POST['licence_key']) ) : '';

                            if($licence_key == '')
                                {
                                    $atto_interface_messages[] =   array(  
                                                                                    'type'  =>  'error',
                                                                                    'text'  =>  __("Licence Key can't be empty", 'atto'));
                                    return;
                                }
                                
                            //build the request query
                            $args = array(
                                                'woo_sl_action'           => 'activate',
                                                'licence_key'           => $licence_key,
                                                'product_unique_id'     => ATTO_PRODUCT_ID,
                                                'domain'                => ATTO_INSTANCE
                                            );
                            $request_uri    = ATTO_UPDATE_API_URL . '?' . http_build_query( $args , '', '&');
                            $data           = wp_remote_get( $request_uri );
                            
                            if(is_wp_error( $data ) || $data['response']['code'] != 200)
                                {
                                    $atto_interface_messages[] =   array(  
                                                                                    'type'  =>  'error',
                                                                                    'text'  =>  sprintf( __('There was a problem connecting to %s', 'atto'), ATTO_UPDATE_API_URL ));
                                    return;  
                                }
                                
                            $response_block = json_decode($data['body']);
                            //retrieve the last message within the $response_block
                            $response_block = $response_block[count($response_block) - 1];
                            
                            if(isset($response_block->status))
                                {
                                    if($response_block->status == 'success' && ( $response_block->status_code == 's100' || $response_block->status_code == 's101' ) )
                                        {
                                            //the license is active and the software is active
                                            $atto_interface_messages[] =   array(  
                                                                                    'type'  =>  'updated',
                                                                                    'text'  =>  $response_block->message);
                                            
                                            $licence_data = get_site_option('atto_licence');
                                            
                                            //save the license
                                            $licence_data['key']          = $licence_key;
                                            $licence_data['last_check']   = time();
                                            
                                            update_site_option('atto_licence', $licence_data);

                                        }
                                        else
                                        {
                                            $atto_interface_messages[] =   array(  
                                                                                    'type'  =>  'error',
                                                                                    'text'  =>  sprintf(__('There was a problem activating the licence: %s', 'atto'), $response_block->message));
                                            return;
                                        }   
                                }
                                else
                                {
                                    $atto_interface_messages[] =   array(  
                                                                                    'type'  =>  'error',
                                                                                    'text'  =>  sprintf(__('There was a problem with the data block received from %s', 'atto'), ATTO_UPDATE_API_URL));
                                    return;
                                }
            
                        }   
                    
                }
                
            function licence_form()
                {
                    ?>
                        <div class="wrap"> 
                            <h2><?php _e( "Advanced Taxonomy Terms Order License", 'atto' ) ?></h2>
                            <br />
                            <div class="start-container">
                                <h3><?php _e( "Licence Key", 'atto' ) ?></h3>
                                <form id="form_data" name="form" method="post">
                             
                                            <?php wp_nonce_field('atto_licence','atto_license_nonce'); ?>
                                            <input type="hidden" name="atto_licence_form_submit" value="true" />
                                            <input type="hidden" name="atto_licence_activate" value="true" />
             
                                            <div class="section section-text ">
                                                <div class="option">
                                                    <div class="controls">
                                                        <input type="text" value="" name="licence_key" class="text-input">
                                                    </div>
                                                    <div class="explain"><?php printf( __( 'Enter the Licence Key you received when you purchased this product. If you lost the key, you can always retrieve it from <a href="%s" target="_blank">My Account</a>.', 'atto' ), esc_url('https://www.nsp-code.com/premium-plugins/my-account') ); ?></div>
                                                </div> 
                                            </div>
                                    
                                    <p class="submit">
                                        <input type="submit" name="Submit" class="button-primary" value="<?php _e('Save', 'atto') ?>">
                                    </p>
                                </form> 
                            </div>
                        </div> 
                    <?php  
     
                }
            
            function licence_deactivate_form()
                {
                    
                    $licence_data = get_site_option('atto_licence');
                    
                    ?>
                        <div class="wrap"> 
                            <h2><?php _e( "Advanced Taxonomy Terms Order License", 'atto' ) ?></h2>
                            <br />
                            <div class="start-container">
                                <h3><?php _e( "Licence Key", 'atto' ) ?></h3>
                                <div id="form_data">
                                    

                                        <form id="form_data" name="form" method="post">    
                                            <?php wp_nonce_field('atto_licence','atto_license_nonce'); ?>
                                            <input type="hidden" name="atto_licence_form_submit" value="true" />
                                            <input type="hidden" name="atto_licence_deactivate" value="true" />

                                            <div class="section section-text ">
                                                <div class="option">
                                                    <div class="controls">
                                                        <?php  
                                                            if( $this->licence->is_local_instance() )
                                                                {
                                                                    ?>
                                                                        <p>Local instance, no key applied.</p>
                                                                    <?php   
                                                                }
                                                            else 
                                                                {
                                                                ?>
                                                                <p><b><?php echo substr($licence_data['key'], 0, 20) ?>-xxxxxxxx-xxxxxxxx</b> &nbsp;&nbsp;&nbsp;<a class="button-secondary" title="Deactivate" href="javascript: void(0)" onclick="jQuery(this).closest('form').submit();"><?php _e('Deactivate', 'atto'); ?></a></p>
                                                        <?php } ?>
                                                    </div>
                                                    <div class="explain"><?php printf( __( 'You can generate more keys from <a href="%s" target="_blank">My Account</a>', 'atto' ), esc_url('https://www.nsp-code.com/premium-plugins/my-account/') ); ?> </div>
                                                </div> 
                                            </div>
                                        </form>

                                </div>
                            </div>
                        </div> 
                    <?php  
            
                }
                
                
              
            
            
        }
        
        
    

?>