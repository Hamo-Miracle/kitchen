<?php

/**
 * BNFW Custom Field Notification
 */
class BNFW_Custom_Field_Notification {

    /**
     * Constructor.
     *
     * @since 1.0
     */
    function __construct() {
        $this->hooks();
    }

    /**
     * Factory method to return the instance of the class.
     *
     * Makes sure that only one instance is created.
     *
     * @return object Instance of the class
     */
    public static function factory() {
        static $instance = false;
        if (!$instance) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * List of post meta notifications.
     *
     * @var array
     */
    protected $post_meta_notifications = array();

    /**
     * List of user meta notifications.
     *
     * @var array
     */
    protected $user_meta_notifications = array();

    /**
     * List of user posted meta before saving profile to DB.
     *
     * @var array
     */
    protected $user_get_meta_after_save = array();

	/**
	 * List of meta key save to avoid duplicate notifications.
	 *
	 * @since 1.2.3
	 *
	 * @var array
	 */
	protected $cf_notification_meta_keys = array();

    /**
     * Setup hooks.
     *
     * @since 1.0
     */
    public function hooks() {
        add_action('bnfw_after_enqueue_scripts', array($this, 'enqueue_scripts'));

        add_action('bnfw_after_transactional_notifications', array($this, 'add_transactional_notification'));
        add_action('bnfw_after_notification_options', array($this, 'add_notification_option'), 10, 3);

        add_action('bnfw_after_notification_dropdown', array($this, 'add_custom_field_selects'));
        add_filter('bnfw_notification_setting_fields', array($this, 'add_notification_setting_field'));

        add_filter('bnfw_notification_setting', array($this, 'save_notification_setting'));

        add_filter('bnfw_notification_name', array($this, 'set_notification_name'), 10, 2);
        add_filter('bnfw_post_notifications', array($this, 'add_to_post_notification'), 10, 2);
        add_filter('bnfw_notification_post_type', array($this, 'get_notification_post_type'), 10, 2);

        add_filter('bnfw_non_wp_emails', array($this, 'append_non_wp_emails'), 10, 3);

        add_action('updated_post_meta', array($this, 'after_post_meta_update'), 10, 4);
        add_action('updated_user_meta', array($this, 'after_user_meta_update'), 10, 4);

        add_action('edit_user_profile_update',function($user_id){
            $this->user_get_meta_after_save = $_POST;
        },10, 1);

        add_action('wpcf_post_field_saved', array($this, 'after_toolset_type_meta_update'), 10, 2);

        add_action('shutdown', array($this, 'on_shutdown'));

        add_filter('bnfw_shortcodes', array($this, 'handle_shortcodes'), 10, 4);

	    add_action( 'shutdown', array( $this, 'bnfw_cf_clear_meta' ), PHP_INT_MAX );
    }

    /**
     * Enqueue additional scripts.
     *
     * @since 1.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script('bnfw-custom-field-notification', plugins_url('assets/js/bnfw-custom-field-notification.js', dirname(__FILE__)), array('jquery'), '1.0', true);
    }

    /**
     * Add Notification to the list.
     *
     * @since 
     *
     * @param string $setting   Notification Settings.
     */
    public function add_transactional_notification($setting) {
        ?>

        <option value="user-customfield" <?php selected('user-customfield', $setting['notification']); ?>>
            <?php _e('User Custom Field Updated', 'bnfw'); ?>
        </option>
        <option value="user-customfieldvalue" <?php selected('user-customfieldvalue', $setting['notification']); ?>>
            <?php _e('User Custom Field Value Updated', 'bnfw'); ?>
        </option>

        <?php
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
    public function add_notification_option($post_type, $label, $setting) {
        if (!post_type_supports($post_type, 'custom-fields')) {
            return;
        }
        ?>
        <option value="customfield-<?php echo $post_type; ?>" <?php selected('customfield-' . $post_type, $setting['notification']); ?>>
            <?php echo "'$label' ", __('Custom Field Updated', 'bnfw'); ?>
        </option>
        <option value="customfieldvalue-<?php echo $post_type; ?>" <?php selected('customfieldvalue-' . $post_type, $setting['notification']); ?>>
            <?php echo "'$label' ", __('Custom Field Value Updated', 'bnfw'); ?>
        </option>
        <?php
    }

    /**
     * Add custom field selects.
     *
     * @param $setting
     */
    public function add_custom_field_selects($setting) {
        $this->add_post_custom_field_select($setting);
        $this->add_user_custom_field_select($setting);
    }

    /**
     * Add custom field select dropdown.
     *
     * @since 1.0
     *
     * @param array $setting Settings array.
     */
    protected function add_post_custom_field_select($setting) {
        $meta_keys = $this->get_unique_post_meta_keys();
        ?>
        <tr valign="top" id="bnfw-custom-field">
            <th scope="row">
                <label><?php _e('Send when these Custom Fields are Updated', 'bnfw'); ?></label>
                <div class="bnfw-help-tip"><p><?php esc_html_e('Only send this notification if any of these custom fields are updated. Usable custom fields are those that don\'t start with an underscore.', 'bnfw'); ?></p></div>
            </th>
            <td>
                <select class="bnfw-select2" name="custom-field[]" data-placeholder="Select Custom Field(s)"
                        style="width:75%" multiple>
                            <?php foreach ($meta_keys as $meta_key) { ?>
                        <option
                            value="<?php echo $meta_key; ?>" <?php selected(true, in_array($meta_key, $setting['custom-field'])); ?>><?php echo $meta_key; ?></option>
                        <?php } ?>
                </select>
            </td>
        </tr>
        <tr valign="top" id="bnfw-custom-field-value">
            <th scope="row">
                <label><?php _e('Send when this Custom Field value Updated', 'bnfw'); ?></label>
                <div class="bnfw-help-tip"><p><?php esc_html_e('Only send this notification if this custom field updated (uses OR logic).', 'bnfw'); ?></p></div>
            </th>

            <td>
                <input type="hidden" name="post-custom-field-notification" id="post-custom-field-notification" value="false">

                <style>
                    .bnfw-custom-field-row {
                        margin-bottom: 4px;
                    }

                    #bnfw-custom-field-value .select2 {
                        width: 25% !important;
                    }

                    #bnfw-custom-field .select2.select2-container.select2-container--default {
                        margin-top: -3.5px;
                    }

                    #bnfw-custom-field input[type="text"] {
                        display: inline;
                        width: 25%;
                        height: 28px;
                        border-radius: 4px;
                        border: 1px solid #aaa;
                        background-color: #fff;
                        padding-left: 8px;
                        padding-right: 0px;
                        line-height: 28px;
                        color: #444;
                    }

                    .bnfw-custom-field-remove,
                    .bnfw-custom-field-add {
                        display: inline-block;
                    }

                    #custom-field-template select {
                        width: 25%;
                    }
                </style>

                <div id="custom-field-template" class="bnfw-custom-field-row">
                    <select name="custom-value-field" class="bnfw-custom-field-value">
                        <?php foreach ($meta_keys as $meta_key) : ?>
                            <option value="<?php echo $meta_key; ?>" <?php selected($meta_key, $setting['custom-value-field']); ?>>
                                <?php echo $meta_key; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <?php 
                    // Bypass the read_settings to read the value of 0 to display the value accordingly
                    // since version 1.2
                    $meta_value = get_post_meta($setting['id'],BNFW_Notification::META_KEY_PREFIX.'custom-field-value',true);
                    $meta_value = (is_array($meta_value))? implode(',',$meta_value) : $meta_value;
                    ?>

                    <input type="text" name="custom-field-value" placeholder="<?php _e('Meta Value', 'bnfw'); ?>" value="<?php echo $meta_value; ?>">
                </div>
            </td>
        </tr>
        <?php
    }

    /**
     * Add user custom field select dropdown.
     *
     * @since 1.2
     *
     * @param array $setting Settings array.
     */
    protected function add_user_custom_field_select($setting) {
        
        $meta_keys = $this->get_unique_user_meta_keys();
    
        ?>
        <tr valign="top" id="bnfw-user-custom-field">
            <th scope="row">
                <label><?php _e('Send when these User Custom Fields are Updated', 'bnfw'); ?></label>
                <div class="bnfw-help-tip"><p><?php esc_html_e('Only send this notification if any of these custom fields are updated. Usable custom fields are those that don\'t start with an underscore.', 'bnfw'); ?></p></div>
            </th>
            <td>
                <select class="bnfw-select2" name="user-custom-field[]" data-placeholder="Select Custom Field(s)"
                        style="width:75%" multiple>
                            <?php foreach ($meta_keys as $meta_key) { ?>
                        <option
                            value="<?php echo $meta_key; ?>" <?php selected(true, in_array($meta_key, $setting['user-custom-field'])); ?>><?php echo $meta_key; ?></option>
                        <?php } ?>
                </select>
            </td>
        </tr>
        <tr valign="top" id="bnfw-user-custom-field-value">
            <th scope="row">
                <label><?php _e('Send when this User Custom Field Value Updated', 'bnfw'); ?></label>

                <div class="bnfw-help-tip">
                    <p>
                        <?php esc_html_e('Only send this notification if this user custom field updated (uses OR logic).', 'bnfw'); ?>
                    </p>
                </div>
            </th>

            <td>
                <input type="hidden" name="user-custom-field-notification" id="user-custom-field-notification" value="false">

                <style>
                    .bnfw-user-custom-field-row {
                        margin-bottom: 4px;
                    }

                    #bnfw-user-custom-field-value .select2 {
                        width: 25% !important;
                    }

                    #bnfw-user-custom-field .select2.select2-container.select2-container--default {
                        margin-top: -3.5px;
                    }

                    #bnfw-user-custom-field input[type="text"] {
                        display: inline;
                        width: 25%;
                        height: 28px;
                        border-radius: 4px;
                        border: 1px solid #aaa;
                        background-color: #fff;
                        padding-left: 8px;
                        padding-right: 0px;
                        line-height: 28px;
                        color: #444;
                    }

                    .bnfw-user-custom-field-remove,
                    .bnfw-user-custom-field-add {
                        display: inline-block;
                    }

                    #user-custom-field-template select {
                        width: 25%;
                    }
                </style>

                <div id="user-custom-field-template" class="bnfw-user-custom-field-row">
                    <select name="user-custom-value-field" class="bnfw-user-custom-field">
                        <?php foreach ($meta_keys as $meta_key) : ?>
                            <option value="<?php echo $meta_key; ?>" <?php selected($meta_key, $setting['user-custom-value-field']); ?>>
                                <?php echo $meta_key; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <?php 
                    // Bypass the read_settings to read the value of 0 to display the value accordingly
                    // since version 1.2
                    $meta_value = get_post_meta($setting['id'],BNFW_Notification::META_KEY_PREFIX.'user-custom-field-value',true);
                    $meta_value = (is_array($meta_value))? implode(',',$meta_value) : $meta_value;
                    ?>

                    <input type="text" name="user-custom-field-value" placeholder="<?php _e('Meta Value', 'bnfw'); ?>" value="<?php echo $meta_value; ?>">
                </div>
            </td>
        </tr>
        <?php
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
    public function add_notification_setting_field($fields) {
        $fields['custom-field'] = array();
        $fields['user-custom-field'] = array();

        $fields['custom-value-field'] = '';
        $fields['custom-field-value'] = '';

        $fields['user-custom-value-field'] = '';
        $fields['user-custom-field-value'] = '';

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
    public function save_notification_setting($setting) {
        
        $bnfw = BNFW::Factory();
        if ($bnfw->notifier->starts_with($_POST['notification'], 'customfield-')) {
            if (isset($_POST['custom-field'])) {
                $tmp = $_POST['custom-field'];
                $setting['custom-field'] = $_POST['custom-field'];
            }else{
                $setting['custom-field'] = array();
            }
        }elseif ($bnfw->notifier->starts_with($_POST['notification'], 'customfieldvalue-')) {
            $setting['custom-value-field'] = $_POST['custom-value-field'];
            $setting['custom-field-value'] = isset($_POST['custom-field-value']) ? $_POST['custom-field-value'] : '';
        } elseif ($_POST['notification'] == 'user-customfield') {
            if (isset($_POST['user-custom-field'])) {
                $setting['user-custom-field'] = $_POST['user-custom-field'];
            }else{
                $setting['user-custom-field'] = array();
            }
        }elseif ($_POST['notification'] == 'user-customfieldvalue') {
            $setting['user-custom-value-field'] = $_POST['user-custom-value-field'];
            $setting['user-custom-field-value'] = isset($_POST['user-custom-field-value']) ? $_POST['user-custom-field-value'] : '';
        }

        //check for the 0, parse value to string so that post_meta will accept.
        //since ver 1.2
        foreach($setting as $index => $value){
            if(is_array($value))
                continue;
            $value = strval($value);
            if($value === '0'){
                $setting[$index] = strval($value);
            }
        }

        return $setting;
    }

    /**
     * Set the name of the notification.
     *
     * @since 1.0
     */
    public function set_notification_name($name, $slug) {
        $bnfw = BNFW::Factory();

        if ('user-customfield' === $slug) {
            return __('User Custom Field Updated', 'bnfw');
        }

        if ('user-customfieldvalue' === $slug) {
            return __('User Custom Field Value Updated', 'bnfw');
        }

        if ($bnfw->notifier->starts_with($slug, 'customfield-')) {
            $splited = explode('-', $slug, 2);
            $label = $splited[1];
            $post_obj = get_post_type_object($splited[1]);

            if (null != $post_obj) {
                $label = $post_obj->labels->singular_name;
            }

            $name = $label . ' - ' . __('Custom Field Updated', 'bnfw');
        }

        if ($bnfw->notifier->starts_with($slug, 'customfieldvalue-')) {
            $splited = explode('-', $slug, 2);
            $label = $splited[1];
            $post_obj = get_post_type_object($splited[1]);

            if (null != $post_obj) {
                $label = $post_obj->labels->singular_name;
            }

            $name = $label . ' - ' . __('Custom Field Value Updated', 'bnfw');
        }

        return $name;
    }

    /**
     * Add this notification to the list of post notifications.
     *
     * @since 1.0
     */
    public function add_to_post_notification($notifications, $post_type) {
        $notifications[] = $this->get_notification_details($post_type);

        return $notifications;
    }

    /**
     * Filter notification post type.
     *
     * @param string $post_type    Post type.
     * @param string $notification Notification name.
     *
     * @return string Notification post type.
     */
    public function get_notification_post_type($post_type, $notification) {
        $splits = explode('-', $notification);

        if (count($splits) == 2) {
            if ('customfield' == $splits[0]) {
                return $splits[1];
            }

            if ('customfieldvalue' == $splits[0]) {
                return $splits[1];
            }
        }

        return $post_type;
    }

    /**
     * After a Toolset Type meta update.
     *
     * @param int $post_id Post ID.
     * @param array $field Field details.
     */
    public function after_toolset_type_meta_update($post_id, $field) {
        //Ask client the copy for this plugin
        $this->send_notification($post_id, $field['meta_key']);
        $this->send_post_custom_field_updated_notification($post_id, $field['meta_key']);
    }

    /**
     * When the metadata is updated.
     *
     * @since 1.0
     *
     * @param int    $meta_id    ID of updated metadata entry.
     * @param int    $post_id    Post ID.
     * @param string $meta_key   Meta key.
     * @param mixed  $meta_value Meta value.
     */
    public function after_post_meta_update($meta_id, $post_id, $meta_key, $meta_value) {
        if ('_edit_lock' != $meta_key) {
            
            /** 
             * 
             * get value generated by ACF
             * 
             */
            if(class_exists('ACF') && get_post_type($post_id) != 'bnfw_notification'){
                $acf_meta_value = get_field_object($meta_key, $post_id);
                $this->user_get_meta_after_save  = array( $meta_key => $acf_meta_value['value']);
            }
            
            $this->send_notification($post_id, $meta_key);
            $this->send_post_custom_field_updated_notification($post_id, $meta_key, $meta_value);
        }
    }

    /**
     * When the user metadata is updated.
     *
     * @since 1.2
     *
     * @param int    $meta_id    ID of updated metadata entry.
     * @param int    $post_id    Post ID.
     * @param string $meta_key   Meta key.
     * @param mixed  $meta_value Meta value.
     */
    public function after_user_meta_update($meta_id, $user_id, $meta_key, $meta_value) {
        $this->send_user_notification($user_id, $meta_key);
        $this->send_user_custom_field_updated_notification($user_id, $meta_key, $meta_value);
    }

    /**
     * Send notification for custom field.
     *
     * @since 1.0
     *
     * @param int    $post_id  Post Id
     * @param string $meta_key Meta key that got changed
     */
    public function send_notification($post_id, $meta_key) {
        $bnfw = BNFW::factory();

	    $notification_type = $this->get_notification_details( get_post_type( $post_id ) );
	    $notifications     = $bnfw->notifier->get_notifications( $notification_type );
	    $post_meta_key     = '_bnfw_' . $notification_type;

        foreach ($notifications as $notification) {
            /**
             * BNFW - Whether notification is disabled?
             *
             * @since 1.3.6
             */
            $setting = $bnfw->notifier->read_settings($notification->ID);
            $notification_disabled = apply_filters('bnfw_notification_disabled', false, $post_id, $setting);

            if (!$notification_disabled) {
                if (!empty($setting['custom-field']) && in_array($meta_key, $setting['custom-field'])) {
                    $transient = get_transient('bnfw-cf-notifications');
                    if (!is_array($transient)) {
                        $transient = array();
                    }

                    $transient[] = array('post_id' => $post_id, 'notification_id' => $notification->ID);
                    set_transient('bnfw-cf-notifications', $transient, 600);

	                // Add post meta to identify that the custom field notification is registered.
	                update_post_meta( $post_id, $post_meta_key, true );

	                // Save it in global variable to delete it after use.
	                if ( ! isset( $this->cf_notification_meta_keys[ $post_id ] ) || ! in_array( $post_meta_key, $this->cf_notification_meta_keys[ $post_id ], true ) ) {
		                $this->cf_notification_meta_keys[ $post_id ][] = $post_meta_key;
	                }
                }
            }
        }
    }

    /**
     * Send notification for custom field.
     *
     * @param int    $post_id    Post Id
     * @param string $meta_key   Meta key that got changed
     * @param string $meta_value Meta value.
     *
     * @since 1.0
     */
    protected function send_post_custom_field_updated_notification($post_id, $meta_key, $meta_value = '') {

        $bnfw = BNFW::factory();
        $notifications = $bnfw->notifier->get_notifications($this->get_notification_custom_details(get_post_type($post_id)));

        foreach ($notifications as $notification) {
            $setting = $bnfw->notifier->read_settings($notification->ID);

            /**
             * BNFW - Whether notification is disabled?
             *
             * @since 1.3.6
             */
            $notification_disabled = apply_filters('bnfw_notification_disabled', false, $post_id, $setting);

            if (!$notification_disabled) {

                if ($setting['custom-value-field'] == $meta_key) {

                    /** 
                     * check value for true, false, 0 & 1
                     * @since 1.3.6
                     */
                    $meta_value = $this->check_value_for_acf_true_false(
                        array(
                            'setting'   =>  $setting,
                            'meta_key'  =>  $meta_key
                        ),
                        $meta_value
                    );
                    /** 
                     * if setting is empty check for the 0
                     * @since 1.3.6
                     */
                    $is_zero = $this->get_direct_meta_for_zero($setting['id'],'custom-field-value');
                    if($is_zero === '0'){
                        $setting['custom-field-value'] = true;
                    }

                    if (!empty($setting['custom-field-value']) && $setting['custom-field-value'] != $meta_value) {
                        continue;
                    }
                    $notification_details = array(
                        'post_id' => $post_id,
                        'notification_id' => $notification->ID,
                    );

                    if (in_array($notification_details, $this->post_meta_notifications, true)) {
                        continue;
                    }

                    $this->post_meta_notifications[] = $notification_details;
                }
            }
        }
    }

    /**
     * Send notification for custom field.
     *
     * @since 1.0
     *
     * @param int    $post_id  Post Id
     * @param string $meta_key Meta key that got changed
     */
    public function send_user_notification($user_id, $meta_key) {
        $bnfw = BNFW::factory();
        $notifications = $bnfw->notifier->get_notifications('user-customfield');

        foreach ($notifications as $notification) {
            $setting = $bnfw->notifier->read_settings($notification->ID);

            /**
             * BNFW - Whether notification is disabled?
             *
             * @since 1.3.6
             */
            $notification_disabled = apply_filters('bnfw_notification_disabled', false, $user_id, $setting);

            if (!$notification_disabled) {
                if (!empty($setting['user-custom-field'])) {
                   if(in_array($meta_key, $setting['user-custom-field'])){
                    $transient = get_transient('bnfw-cf-notifications');
                    if (!is_array($transient)) {
                        $transient = array();
                    }

                    $transient[] = array('post_id' => $user_id, 'notification_id' => $notification->ID);
                    set_transient('bnfw-cf-notifications', $transient, 600);
                  }
                }
            }
        }
    }

    /**
     * Send notification for user custom field.
     *
     * @param int    $user_id    Post Id
     * @param string $meta_key   Meta key that got changed
     * @param string $meta_value Meta value.
     *
     * @since 1.2
     */
    protected function send_user_custom_field_updated_notification($user_id, $meta_key, $meta_value = '') {
        $bnfw = BNFW::factory();
        $notifications = $bnfw->notifier->get_notifications('user-customfieldvalue');

        foreach ($notifications as $notification) {
            $setting = $bnfw->notifier->read_settings($notification->ID);

            /**
             * BNFW - Whether notification is disabled?
             *
             * @since 1.3.6
             */
            $notification_disabled = apply_filters('bnfw_notification_disabled', false, $user_id, $setting);

            if (!$notification_disabled) {


                if ($setting['user-custom-value-field'] == $meta_key) {


                    /**
                     * check the value if has 'true' by calling the meta_data directly
                     */
                    if(strtolower($setting['user-custom-field-value']) == 'true' || $setting['user-custom-field-value'] == 1){
                        if(isset($this->user_get_meta_after_save[$meta_key])){
                            $meta_value = $setting['user-custom-field-value'];
                        }else{
                            $meta_value = null;
                        }
                    }

                    /**
                     * check the value if has 'false' by calling the meta_data directly
                     * @since 1.3.6
                     */
                    if(strtolower($setting['user-custom-field-value']) == 'false'){
                        if(!isset($this->user_get_meta_after_save[$meta_key])){
                            $meta_value = $setting['user-custom-field-value'];
                        }else{
                            $meta_value = null;
                        }
                    }

                    /**
                     * check the value if has empty 0 by calling the meta_data directly
                     * @since 1.3.6
                     */
                    $direct_meta = get_post_meta($setting['id'],BNFW_Notification::META_KEY_PREFIX.'user-custom-field-value',true);
                    if($direct_meta === '0'){
                        if(!isset($this->user_get_meta_after_save[$meta_key])){
                            $meta_value = 'BNFW_TRIGGER';
                        }else{
                            continue;
                        }
                    }

                    if (!empty($setting['user-custom-field-value']) && $setting['user-custom-field-value'] != $meta_value) {
                        continue;
                    }

                    $notification_details = array(
                        'user_id' => $user_id,
                        'notification_id' => $notification->ID,
                    );

                    if (in_array($notification_details, $this->user_meta_notifications, true)) {
                        continue;
                    }

                    $this->user_meta_notifications[] = $notification_details;
                }
            }
        }
    }

    /**
     * Send notification emails on shutdown.
     */
    public function on_shutdown() {
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }

        $transient = get_transient('bnfw-cf-notifications');
        if (is_array($transient)) {
            foreach ($transient as $id_pairs) {
                $bnfw = BNFW::Factory();
                $setting = $bnfw->notifier->read_settings($id_pairs['notification_id']);
                $bnfw->engine->send_notification($setting, $id_pairs['post_id']);
            }
            delete_transient('bnfw-cf-notifications');
        }

        foreach ($this->post_meta_notifications as $id_pairs) {
            $bnfw = BNFW::Factory();
            $setting = $bnfw->notifier->read_settings($id_pairs['notification_id']);
            $bnfw->engine->send_notification($setting, $id_pairs['post_id']);
        }

        foreach ($this->user_meta_notifications as $id_pairs) {
            $bnfw = BNFW::Factory();
            $setting = $bnfw->notifier->read_settings($id_pairs['notification_id']);
            $bnfw->engine->send_notification($setting, $id_pairs['user_id']);
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
    public function handle_shortcodes($message, $notification, $post_id, $engine) {
        $bnfw = BNFW::Factory();

        if ('user-customfield' === $notification) {
            $user = get_user_by('ID', $post_id);
            $message = $engine->user_shortcodes($message, $post_id);

            return $message;
        }
        
        if ('user-customfieldvalue' === $notification) {
            $user = get_user_by('ID', $post_id);
            $message = $engine->user_shortcodes($message, $post_id);

            return $message;
        }

        if ($bnfw->notifier->starts_with($notification, 'customfield-')) {
            $post = get_post($post_id);

            if ($post instanceof WP_Post) {
                $message = $engine->post_shortcodes($message, $post_id);
                $message = $engine->user_shortcodes($message, $post->post_author);
            }
        }

        if ($bnfw->notifier->starts_with($notification, 'customfieldvalue-')) {
            $post = get_post($post_id);

            if ($post instanceof WP_Post) {
                $message = $engine->post_shortcodes($message, $post_id);
                $message = $engine->user_shortcodes($message, $post->post_author);
            }
        }

        return $message;
    }

    /**
     * Append Non WordPress emails that are specified in post meta.
     *
     * @param array $emails  List of emails.
     * @param array $users   List of users.
     * @param int   $post_id Post Id.
     *
     * @return array Appended list of emails.
     */
    public function append_non_wp_emails($emails, $users, $post_id) {
        if ($post_id > 0) {
            foreach ($users as $user) {
                if (!is_email($user)) {
                    $shortcode_emails = apply_filters('bnfw_shortcodes_post_meta', $user, $post_id);

                    $shortcode_emails = explode(',', $shortcode_emails);
                    $shortcode_emails = array_map('trim', $shortcode_emails);

                    foreach ($shortcode_emails as $shortcode_email) {
                        if (!empty($shortcode_email) && is_email($shortcode_email)) {
                            $emails[] = $shortcode_email;
                        }
                    }
                }
            }
        }

        return $emails;
    }

    /**
     * Get the list of unique post meta keys.
     *
     * @since 1.0
     * @return array Array of meta keys.
     */
    private function get_unique_post_meta_keys() {
        global $wpdb;

        $query = "SELECT DISTINCT({$wpdb->postmeta}.meta_key) FROM {$wpdb->postmeta} WHERE {$wpdb->postmeta}.meta_key != ''";
        $meta_keys = $wpdb->get_col($query);
        $new_meta_keys = array();
        foreach ($meta_keys as $key => $meta_key) {
            $check_bnfw = explode('_', $meta_key);
            if ($check_bnfw[0] == 'bnfw' || (isset($check_bnfw[1]) && $check_bnfw[1] == 'bnfw')) {
                continue;
            }
            $check_meta_key = ltrim($meta_key, "_");
            if (!in_array($check_meta_key, $new_meta_keys)) {
                $new_meta_keys[] = $meta_key;
            }
        }
        return $new_meta_keys;
    }

    /**
     * Get the list of unique user meta keys.
     *
     * @since 1.2
     *
     * @return array Array of meta keys.
     */
    protected function get_unique_user_meta_keys() {
        global $wpdb;

        $query = "SELECT DISTINCT({$wpdb->usermeta}.meta_key) FROM {$wpdb->usermeta} WHERE {$wpdb->usermeta}.meta_key != ''";
        $meta_keys = $wpdb->get_col($query);
        $new_meta_keys = array();
        foreach ($meta_keys as $key => $meta_key) {
            $check_bnfw = explode('_', $meta_key);
            if ($check_bnfw[0] == 'bnfw' || $check_bnfw[0] == 'bnfw') {
                continue;
            }
            $check_meta_key = ltrim($meta_key, "_");
            if (!in_array($check_meta_key, $new_meta_keys)) {
                $new_meta_keys[] = $meta_key;
            }
        }
        return $new_meta_keys;
    }

    /**
     * Build notification name based on post type.
     *
     * @param string $post_type Post type
     *
     * @return string Notification Type
     */
    protected function get_notification_details($post_type) {
        return 'customfield-' . $post_type;
    }

    /**
     * Build notification name based on post type.
     *
     * @param string $post_type Post type
     *
     * @return string Notification Type
     */
    protected function get_notification_custom_details($post_type) {
        return 'customfieldvalue-' . $post_type;
    }


    /**
     * 
     * Get the meta value directly from get_post_meta instead of using the BNFW settings
     * to check if the value has 0 value
     * 
     * @param $post_id - int
     * @param $field - string
     * 
     * @return 'string' | null
     * 
     * @since 1.3.6
     * 
     */
    public function get_direct_meta_for_zero($post_id, $field){
        return get_post_meta($post_id,BNFW_Notification::META_KEY_PREFIX.$field,true);
    }

    /**
     * 
     * Check what value is being pass
     * Check for the values for true, false, 0 and 1
     * If the value is not from the above do as it is
     * Check if the ACF is active and check for array values
     * 
     * @param $param - array()
     * @param $meta_value - string
     * 
     * @return 'string' | null
     * 
     * @since 1.3.6
     * 
     */
    public function check_value_for_acf_true_false($param = null, $meta_value = ''){

        if(!$param)
            return;

        $is_boolstring = false;

        //check for true or 1
        if(strtolower($param['setting']['custom-field-value']) == 'true' || $param['setting']['custom-field-value'] == 1){
            if(!empty($this->user_get_meta_after_save[$param['meta_key']])){

                //check for array   
                if(is_array($this->user_get_meta_after_save[$param['meta_key']])){
                    foreach($this->user_get_meta_after_save[$param['meta_key']] as $row){
                        $row = trim($row);
                        if(strlen($row) > 0){
                            $meta_value = $param['setting']['custom-field-value'];
                            $is_boolstring = true;
                            break;
                        }
                    }
                }else{
                    $meta_value = $param['setting']['custom-field-value'];
                    $is_boolstring = true;
                }

            }else{
                $meta_value = null;
            }
        }

        //check for false
        if(strtolower($param['setting']['custom-field-value']) == 'false'){

            if(is_array($this->user_get_meta_after_save[$param['meta_key']])){            
                if(empty($this->user_get_meta_after_save[$param['meta_key']])){
                    $meta_value = $param['setting']['custom-field-value'];
                    $is_boolstring = true;
                }else{
                    foreach($this->user_get_meta_after_save[$param['meta_key']] as $row){
                        $row = trim($row);
                        if(strlen($row) == 0){
                            $meta_value = $param['setting']['custom-field-value'];
                            $is_boolstring = true;
                        }
                    }
                }
            }else{
                if(empty($this->user_get_meta_after_save[$param['meta_key']])){
                    $meta_value = $param['setting']['custom-field-value'];
                    $is_boolstring = true;
                }else{
                    $meta_value = null;
                }
            }
            
        }

        //check if 0
        //direct calling of the meta_value because somewhere in BNFW main plugin the setting is checking for true/false
        $direct_meta = $this->get_direct_meta_for_zero($param['setting']['id'],'custom-field-value');
        if($direct_meta === '0'){
            //check for array value
            if(is_array($this->user_get_meta_after_save[$param['meta_key']])){

                if(empty($this->user_get_meta_after_save[$param['meta_key']])){
                    $meta_value = 'BNFW_TRIGGER';
                    $is_boolstring = true;
                }else{

                    foreach($this->user_get_meta_after_save[$param['meta_key']] as $row){
                        $row = trim($row);
                        if(strlen($row) == 0){
                            $meta_value = 'BNFW_TRIGGER';
                            $is_boolstring = true;
                        }
                    }

                    if($meta_value != 'BNFW_TRIGGER'){
                        $meta_value = null;
                        $is_boolstring = true;
                    }
                }

            }else{
                error_log(print_r($this->user_get_meta_after_save,true));

                if(empty($this->user_get_meta_after_save[$param['meta_key']])){
                    $meta_value = 'BNFW_TRIGGER';
                    $is_boolstring = true;
                }else{
                    $meta_value = 'BNFW_TRIGGER';
                    $is_boolstring = true;
                    $meta_value = null;
                }
            }
            
        }

        //check if ACF/array value
        if(is_array($meta_value) && !$is_boolstring){
            foreach($meta_value as $val){
                $meta_v = strtolower($val);
                $meta_a = strtolower($param['setting']['custom-field-value']);
                if($meta_v == $meta_a){
                    $meta_value = $param['setting']['custom-field-value'];
                    break;
                }
            }
        }
        
        return $meta_value;
    }

	/**
	 * Function to add Conditional tab arguments.
	 *
	 * @since 2.0
	 *
	 * @param array $tab Array of tab.
	 *
	 * @return array
	 */
	public function add_custom_fields_setting_tab( $tabs ) {
		$tabs[] = array(
			'label' => __( 'Custom Fields', 'bnfw' ),
			'order' => 3,
			'tab_id' => 'bnfw-custom-fields-settings-tab'
		);

		return $tabs;
	}

	/**
	 * Function to delete post meta to registered custom field update notifications.
	 *
	 * @since 1.2.3
	 *
	 * @return void
	 */
	public function bnfw_cf_clear_meta() {
		if ( ! empty( $this->cf_notification_meta_keys ) ) {
			foreach ( $this->cf_notification_meta_keys as $post_id => $meta_keys ) {
				if ( ! empty( $meta_keys ) ) {
					foreach ( $meta_keys as $meta_key ) {
						delete_post_meta( $post_id, $meta_key );
					}
				}

				unset( $this->cf_notification_meta_keys[ $post_id ] );
            }
		}
	}

}
