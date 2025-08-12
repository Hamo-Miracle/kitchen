<?php
define('CRB_THEME_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);

if ( /*true ||*/
(isset($_GET) && isset($_GET['after_switch_theme']))) {
    add_action('wp_loaded', function () {
        do_action('after_switch_theme');
    });
}

# Enqueue JS and CSS assets on the front-end
add_action('wp_enqueue_scripts', 'crb_wp_enqueue_scripts', 11);
function crb_wp_enqueue_scripts()
{
    // Fix the plugin "Meta Generator and Version Info Remover" breaking carbon version parameter
    remove_filter('style_loader_src', 'pkm_remove_appended_version_script_style', 20000);
    remove_filter('script_loader_src', 'pkm_remove_appended_version_script_style', 20000);

    $template_dir = get_template_directory_uri();
    $child_dir = get_stylesheet_directory_uri();

    # Enqueue jQuery
    wp_enqueue_script('jquery');

    # Enqueue Custom JS files
    # @crb_enqueue_script attributes -- id, location, dependencies, in_footer = false
    crb_enqueue_script('theme-functions', $child_dir . '/js/functions.js', array('jquery'));

    # Parent Theme Styles
    wp_dequeue_style('pluto-style');
    crb_enqueue_style('pluto-parent-style', $template_dir . '/style.css', array('pluto-slider-style', 'pluto-fonts'));

    # Enqueue Custom CSS files
    # @crb_enqueue_style attributes -- id, location, dependencies, media = all
    // crb_enqueue_style( 'theme-custom-styles', $child_dir . '/assets/bundle.css' );
    crb_enqueue_style('theme-styles', $child_dir . '/style.css');
}

# Enqueue JS and CSS assets on admin pages
add_action('admin_enqueue_scripts', 'crb_admin_enqueue_scripts');
function crb_admin_enqueue_scripts()
{
    $template_dir = get_template_directory_uri();
    $child_dir = get_stylesheet_directory_uri();

    # Enqueue Scripts
    # @crb_enqueue_script attributes -- id, location, dependencies, in_footer = false
    crb_enqueue_script('theme-admin-functions', $child_dir . '/js/admin-functions.js', array('jquery'));

    # Enqueue Styles
    # @crb_enqueue_style attributes -- id, location, dependencies, media = all
    crb_enqueue_style('theme-admin-styles', $child_dir . '/assets/admin-style.css');
}

# Enqueue JS and CSS assets on Login pages
add_action('login_enqueue_scripts', 'crb_login_enqueue_scripts');
function crb_login_enqueue_scripts()
{
    $template_dir = get_template_directory_uri();
    $child_dir = get_stylesheet_directory_uri();

    # Enqueue Scripts
    # @crb_enqueue_script attributes -- id, location, dependencies, in_footer = false
    # crb_enqueue_script( 'theme-login-functions', $child_dir . '/js/login-functions.js', array( 'jquery' ) );

    # Enqueue Styles
    # @crb_enqueue_style attributes -- id, location, dependencies, media = all
    crb_enqueue_style('theme-login-styles', $child_dir . '/assets/login-style.css');
}

add_action('after_setup_theme', 'crb_setup_theme');

include_once(CRB_THEME_DIR . 'includes/Crb_Replace_Submit_Meta_Box.php');

# To override theme setup process in a child theme, add your own crb_setup_theme() to your child theme's
# functions.php file.
if (!function_exists('crb_setup_theme')) {
    function crb_setup_theme()
    {
        # Make this theme available for translation.
        load_theme_textdomain('crb', get_stylesheet_directory() . '/languages');

        # Autoload dependencies
        $autoload_dir = CRB_THEME_DIR . 'vendor/autoload.php';
        if (!is_readable($autoload_dir)) {
            wp_die(__('Please, run <code>composer install</code> to download and install the theme dependencies.', 'crb'));
        }
        include_once($autoload_dir);

        /* ==========================================================================
            # Additional libraries and includes
        ========================================================================== */

        # Initialize Custom Fields
        include_once(CRB_THEME_DIR . 'includes/carbon-field-wickedpicker/Wickedpicker_Field-plugin.php');
        include_once(CRB_THEME_DIR . 'includes/carbon-field-select_recipe/Select_Recipe_Field-plugin.php');

        # Helpers
        include_once(CRB_THEME_DIR . 'includes/helpers.php');
        include_once(CRB_THEME_DIR . 'includes/ajax.php');
        include_once(CRB_THEME_DIR . 'includes/admin-modifications.php');
        include_once(CRB_THEME_DIR . 'includes/required-post-title.php');
        include_once(CRB_THEME_DIR . 'includes/Crb_User.php');
        include_once(CRB_THEME_DIR . 'includes/Crb_Current_User.php');
        include_once(CRB_THEME_DIR . 'includes/Crb_Date.php');
        include_once(CRB_THEME_DIR . 'includes/Crb_Class.php');
        include_once(CRB_THEME_DIR . 'includes/Crb_Location.php');
        include_once(CRB_THEME_DIR . 'includes/Crb_Recipe.php');
        include_once(CRB_THEME_DIR . 'includes/Crb_Cache_Booster.php');
        include_once(CRB_THEME_DIR . 'includes/woocommerce/Crb_WooCommerce.php');
        include_once(CRB_THEME_DIR . 'includes/woocommerce/hooks.php');
        include_once(CRB_THEME_DIR . 'includes/custom-status.php');

        # Initialize Post Types
        include_once(CRB_THEME_DIR . 'includes/init/Crb_Initialize_Base.php');
        include_once(CRB_THEME_DIR . 'includes/init/Crb_Initialize_Org.php');
        include_once(CRB_THEME_DIR . 'includes/init/Crb_Initialize_Location.php');
        include_once(CRB_THEME_DIR . 'includes/init/Crb_Initialize_Class.php');
        include_once(CRB_THEME_DIR . 'includes/init/Crb_Initialize_Date.php');
        include_once(CRB_THEME_DIR . 'includes/init/Crb_Initialize_Recipe.php');

        # Initialize Users
        include_once(CRB_THEME_DIR . 'includes/init/Crb_User_Initialize_Base.php');
        include_once(CRB_THEME_DIR . 'includes/init/Crb_User_Initialize_Facilitator.php');
        include_once(CRB_THEME_DIR . 'includes/init/Crb_User_Initialize_Session_Admin.php');
        include_once(CRB_THEME_DIR . 'includes/init/Crb_User_Initialize_Assistant.php');
        include_once(CRB_THEME_DIR . 'includes/init/Crb_User_Initialize_Admin.php');

        # Initialize Custom User Form
        include_once(CRB_THEME_DIR . 'includes/init/Crb_User_Register.php');

        # Initialize Emails api
        include_once(CRB_THEME_DIR . 'includes/Crb_Mail.php');
        include_once(CRB_THEME_DIR . 'includes/Crb_Cron.php');

        #Initialize API Endpoints
        include_once(CRB_THEME_DIR . 'api/Api_Common.php');
        include_once(CRB_THEME_DIR . 'api/Api_Org.php');

        # Add Actions
        add_action('carbon_register_fields', 'crb_attach_theme_options');

    }
}

// Load Theme Options container
function crb_attach_theme_options()
{
    # Attach fields
    include_once(CRB_THEME_DIR . 'options/theme-options.php');
}

/**
 * Add API endpoints
 */
add_action( 'rest_api_init', function () {
    register_rest_route( 'ktk/v1', '/org/(?P<id>\d+)/users', array(
      'methods' => 'GET',
      'callback' => array( 'Api_Org', 'get_allowed_users_by_org' ),
    ) );
  } );

add_action( 'rest_api_init', function () {
register_rest_route( 'ktk/v1', '/org/(?P<id>\d+)/locations', array(
    'methods' => 'GET',
    'callback' => array( 'Api_Org', 'get_location_list' ),
) );
} );

add_action( 'rest_api_init', function () {
    register_rest_route( 'ktk/v1', '/common/item/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => array( 'Api_Common', 'get_item' ),
    ) );
    
    
    register_rest_route( 'ktk/v1', '/common/testdailyemail', array(
        'methods' => 'GET',
        'callback' => array( 'Api_Common', 'test_daily_email' ),
    ) );
    

    } );

/**
 * Attach Custom Carbon Fields
 */
add_action('after_setup_theme', 'crb_init_carbon_fields_custom', 15);
function crb_init_carbon_fields_custom()
{
    if (class_exists('Carbon_Fields\\Field\\Field')) {
        include_once(CRB_THEME_DIR . 'includes/Checkbox_Required_Field.php');
    }
}

// Inject Ratings to the "page-full-width.php" and "default" templates
add_filter('the_content', 'crb_inject_ratings_to_content');
function crb_inject_ratings_to_content($content)
{
    if (!function_exists('the_ratings')) {
        return $content;
    }

    if (!is_page_template('page-full-width.php') && !is_page_tempalte('default')) {
        return $content;
    }

    $ratings = '';
    ob_start();
    the_ratings();
    $ratings = ob_get_clean();

    $content .= $ratings;

    return $content;
}

// Login - redirect to Locations screen
add_filter('login_redirect', function ($redirect_to, $requested_redirect_to, $user) {
    if (is_wp_error($user)) {
        return $redirect_to;
    }

    $crb_user = new Crb_User($user->ID);
    if ($crb_user->is('crb_facilitator')) {
        $redirect_to = admin_url();
    } elseif ($crb_user->is('crb_session_admin')) {
        $redirect_to = admin_url('/admin.php?page=crbn-site-instructions.php');
    }

    return $redirect_to;
}, 1000, 3);

// Add Downloadable Products to Woocommerce Completed Order & Invoice Emails as Attachments
function woocommerce_emails_attach_downloadables($attachments, $status, $order)
{
    if (!is_object($order) || !isset($status)) {
        return $attachments;
    }
    if (empty($order)) {
        return $attachments;
    }
    if (!$order->has_downloadable_item()) {
        return $attachments;
    }
    $allowed_statuses = array('customer_invoice', 'customer_completed_order');
    if (isset($status) && in_array($status, $allowed_statuses)) {
        foreach ($order->get_items() as $item_id => $item) {
            foreach ($order->get_item_downloads($item) as $download) {
                $attachments[] = str_replace(content_url(), WP_CONTENT_DIR, $download['file']);
            }
        }
    }
    return $attachments;
}

add_filter('woocommerce_email_attachments', 'woocommerce_emails_attach_downloadables', 10, 3);

// Remove the Product Description Title
add_filter('woocommerce_product_description_heading', 'remove_product_description_heading');
function remove_product_description_heading()
{
    return '';
}

/**
 * Auto Complete all WooCommerce orders.
 *
 * Core WC gateways:
 *
 * (class WC_Gateway_.* extends WC_Payment_Gateway)
 * extends WC_Payment_Gateway:
 *    WC_Gateway_BACS => bacs
 *    WC_Gateway_Cheque => cheque
 *    WC_Gateway_COD => cod
 *    WC_Gateway_Paypal => paypal
 *    WC_Gateway_Stripe => stripe
 * extends WC_Payment_Gateway:
 *    WC_Gateway_Simplify_Commerce => simplify_commerce
 *    WC_Gateway_Stripe => stripe
 */
add_action('woocommerce_thankyou', 'crb_woocommerce_auto_complete_order');
function crb_woocommerce_auto_complete_order($order_id)
{
    if (empty($order_id)) {
        return;
    }

    $targeted_gateways = array(
        'paypal',
        'stripe',
        // 'cod',
    );

    $order = wc_get_order($order_id);
    $current_payment_gateway = $order->get_payment_method();

    if (in_array($current_payment_gateway, $targeted_gateways)) {
        $order->update_status('completed');
    }
}

/**
 * Helper function get getting roles that the user is allowed to create/edit/delete.
 *
 * @param WP_User $user
 * @return  array
 */
function crb_get_allowed_roles($user)
{
    $allowed = array();

    if (in_array('administrator', $user->roles)) { // Admin can edit all roles
        $allowed = array_keys($GLOBALS['wp_roles']->roles);
    } elseif (in_array('crb_assistant', $user->roles)) {
        $allowed[] = 'crb_session_admin';
        $allowed[] = 'crb_facilitator';
    }

    return $allowed;
}

/**
 * Remove roles that are not allowed for the current user role.
 */
add_filter('editable_roles', 'crb_editable_roles');
function crb_editable_roles($roles)
{
    if ($user = wp_get_current_user()) {
        $allowed = crb_get_allowed_roles($user);

        foreach ($roles as $role => $caps) {
            if (!in_array($role, $allowed)) {
                unset($roles[$role]);
            }
        }
    }

    return $roles;
}

/**
 * Prevent users deleting/editing users with a role outside their allowance.
 */
add_filter('map_meta_cap', 'crb_map_meta_cap', 10, 4);
function crb_map_meta_cap($caps, $cap, $user_ID, $args)
{
    if (($cap === 'edit_user' || $cap === 'delete_user') && $args) {
        $the_user = get_userdata($user_ID); // The user performing the task
        $user = get_userdata($args[0]); // The user being edited/deleted

        if ($the_user && $user && $the_user->ID != $user->ID /* User can always edit self */) {
            $allowed = crb_get_allowed_roles($the_user);

            if (array_diff($user->roles, $allowed)) {
                // Target user has roles outside of our limits
                $caps[] = 'not_allowed';
            }
        }
    }

    return $caps;
}


/*add_action('admin_menu' , 'add_to_cpt_menu');

function add_to_cpt_menu() {
    add_submenu_page('edit.php?post_type=crb_class', 'Custom Post Type Admin', 'Download Classes', 'edit_posts', basename(__FILE__), '_locations_page');
}  
	
function _locations_page(){
	get_template_part('template_pdfdownload');
	}
*/

function add_private_admin_menu_item() {
    if (
        Crb_Current_User()->is( 'crb_facilitator' ) || Crb_Current_User()->is( 'crb_assistant' )
    ) {
        add_menu_page( 'linked_url', 'Team Bulletin', 'read', home_url('/teambulletin'), '', 'dashicons-text', 2 );
    }
    if (
        Crb_Current_User()->is( 'crb_session_admin' )
    ) {
        add_menu_page( 'linked_url', 'PARTNER ORIENTATION', 'read', home_url('/partner-orientation'), '', 'dashicons-text', 2 );
    }
} /*close whole function*/

add_action('admin_menu', 'add_private_admin_menu_item');



/**
 * Adds a submenu page under a custom post type parent.
 */
add_action('admin_menu', 'admin_download_class_page');
function admin_download_class_page()
{
    add_submenu_page(
        'edit.php?post_type=crb_class',
        __('Download Classes', 'textdomain'),
        __('Download Classes', 'textdomain'),
        'manage_options',
        'download_page',
        'download_class_page_callback'
    );
}

/**
 * Display callback for the unapproved page.
 */
function download_class_page_callback()
{
    get_template_part('template_pdfdownload');
}

/**
 * Adds a submenu page under a custom post type parent for facilitator.
 */
add_action('admin_menu', 'facilitator_unapproved_page');
function facilitator_unapproved_page()
{
    add_submenu_page(
        'edit.php?post_type=crb_class',
        __('Job Opportunities', 'textdomain'),
        __('Job Opportunities', 'textdomain'),
        'job_opportunities',
        'unapproved',
        'unapproved_page_callback'
    );
}

/**
 * Display callback for the unapproved page.
 */
function unapproved_page_callback()
{
    get_template_part('template_unapproved');
}


add_action('manage_posts_extra_tablenav', 'add_extra_button');
function add_extra_button($where)
{
    global $post_type_object;
    if ($post_type_object->name === 'crb_class') {
        ?>
        <input class="button button-primary subit_value" type="button" value="Download PDF">

        <input type="hidden" id="main_check_values" value="">

        <script>

            jQuery(document).ready(function ($) {
                $(function () {
                    $('.subit_value').attr('disabled', 'disabled');
                    $('#cb-select-all-1,input[type="checkbox"]').click(function () {
                        if ($(this).is(':checked')) {
                            $('.subit_value').removeAttr('disabled');
                        } else {
                            $('.subit_value').attr('disabled', 'disabled');
                        }
                    });
                });
            });


            jQuery(document).ready(function ($) {
                var main_check_values = $('#main_check_values');

                $('#cb-select-all-1').change(function (e) {
                    main_check_values.val(getSelectedFruits());
                });
                $('input[name="post[]"]').change(function (e) {
                    main_check_values.val(getSelectedFruits());
                });

                function getSelectedFruits() {
                    var main_check_valuess = "";
                    $('input[name="post[]"]').each(function (i, cb) {
                        if ($(this).is(":checked")) {
                            main_check_valuess += $(this).val() + ",";
                        }
                    });
                    return main_check_valuess;
                }

                $(".subit_value").click(function (event) {
                    window.open('<?php bloginfo('stylesheet_directory')?>/generate_pdf.php?post_ids=' + $("#main_check_values").val());
                });


            });
        </script>


        <?php
    }
}


function post_start_date()
{
    ob_start();
    echo get_post_meta(get_the_ID(), '_crb_class_dates_-_start_0', true);
    return ob_get_clean();
}

add_shortcode("post_start_date", "post_start_date");

// Creating a Deals Custom Post Type
function crunchify_deals_custom_post_type() {
    $labels = array(
        'name'                => __( 'Models' ),
        'singular_name'       => __( 'Models'),
        'menu_name'           => __( 'Models'),
        'parent_item_colon'   => __( 'Parent Models'),
        'all_items'           => __( 'All Models'),
        'view_item'           => __( 'View Models'),
        'add_new_item'        => __( 'Add New Models'),
        'add_new'             => __( 'Add New'),
        'edit_item'           => __( 'Edit Models'),
        'update_item'         => __( 'Update Models'),
        'search_items'        => __( 'Search Models'),
        'not_found'           => __( 'Not Found'),
        'not_found_in_trash'  => __( 'Not found in Trash')
    );
    $args = array(
        'label'               => __( 'events'),
        'labels'              => $labels,
        'supports'            => array( 'title', 'thumbnail', 'editor','tag'),
        'public'              => true,
        'hierarchical'        => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'has_archive'         => true,
        'can_export'          => true,
        'exclude_from_search' => false,
        'yarpp_support'       => true,
        'publicly_queryable'  => true,
        'capability_type'     => 'page'
    );
    register_post_type( 'events', $args );
}
add_action( 'init', 'crunchify_deals_custom_post_type', 0 );


// Let us create Taxonomy for Custom Post Type
add_action( 'init', 'crunchify_create_deals_custom_taxonomy', 0 );

//create a custom taxonomy name it "type" for your posts
function crunchify_create_deals_custom_taxonomy() {

    $labels = array(
        'name' => _x( 'Models Category', 'taxonomy general name' ),
        'singular_name' => _x( 'Models Category', 'taxonomy singular name' ),
        'search_items' =>  __( 'Search Models Category' ),
        'all_items' => __( 'All Models Category' ),
        'parent_item' => __( 'Parent Models Category' ),
        'parent_item_colon' => __( 'Parent Models Category:' ),
        'edit_item' => __( 'Edit Models Category' ),
        'update_item' => __( 'Update Models Category' ),
        'add_new_item' => __( 'Add New Models Category' ),
        'new_item_name' => __( 'New Models Category Name' ),
        'menu_name' => __( 'Models Category' ),
    );

    register_taxonomy('events_category',array('events'), array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array( 'slug' => 'events_category' ),
    ));
}


add_action( 'init', 'create_tag_taxonomies', 0 );

//create two taxonomies, genres and tags for the post type "tag"
function create_tag_taxonomies()
{
    // Add new taxonomy, NOT hierarchical (like tags)
    $labels = array(
        'name' => _x( 'Tags', 'taxonomy general name' ),
        'singular_name' => _x( 'Tag', 'taxonomy singular name' ),
        'search_items' =>  __( 'Search Tags' ),
        'popular_items' => __( 'Popular Tags' ),
        'all_items' => __( 'All Tags' ),
        'parent_item' => null,
        'parent_item_colon' => null,
        'edit_item' => __( 'Edit Tag' ),
        'update_item' => __( 'Update Tag' ),
        'add_new_item' => __( 'Add New Tag' ),
        'new_item_name' => __( 'New Tag Name' ),
        'separate_items_with_commas' => __( 'Separate tags with commas' ),
        'add_or_remove_items' => __( 'Add or remove tags' ),
        'choose_from_most_used' => __( 'Choose from the most used tags' ),
        'menu_name' => __( 'Tags' ),
    );

    register_taxonomy('tag','events',array(
        'hierarchical' => false,
        'labels' => $labels,
        'show_ui' => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var' => true,
        'rewrite' => array( 'slug' => 'tag' ),
    ));
}



function class_dates(){
    ob_start();
    $initilizeclass = new Crb_Initialize_Class();
    $dates = $initilizeclass->column_callback_get_dates_for_download(get_the_ID());
    echo $dates;
    return ob_get_clean();
}
add_shortcode("class_dates","class_dates");

function class_location(){

    ob_start();
    $initilizeclass = new Crb_Initialize_Class();
    $location = $initilizeclass->column_callback_location_pdf(get_the_ID());
    echo $location;
    return ob_get_clean();
}
add_shortcode("class_location","class_location");

function class_age(){
    ob_start();
    $terms = get_the_terms(get_the_ID(), 'crb_class_age');
    foreach($terms as $wcatTerm) :
        ?>
        <?php echo $wcatTerm->name.',&nbsp;'; ?>
    <?php
    endforeach;
    return ob_get_clean();
}
add_shortcode("class_age","class_age");

add_action('wp_ajax_send_email_communication', 'send_email_communication');
function send_email_communication()
{
    $post_id = $_POST['post_id'];

//    $to = 'Emily@kidstestkitchen.com';
    $to = 'efseward@gmail.com';
    $subject = "Job Opportunity Request";
    $headers = array('Content-Type: text/html; charset=UTF-8');

    //get the post title
    $class_name = get_the_title($post_id);
    //get Age ranges
    $terms = get_the_terms($post_id, 'crb_class_age');
    if (!empty($terms)) {
        $temp_name = '';
        foreach ($terms as $term){
            $temp_name.= $term->name.', ';
        }

        $age_range = substr($temp_name, 0, -2);
    }
    //get Date(Recipe)
    $initilizeclass = new Crb_Initialize_Class();
    $dates = $initilizeclass->column_callback_get_dates_opportunities($post_id);

    //get the location info
    $loc = $initilizeclass->column_callback_location_pdf($post_id);

    global $current_user;
    $userName = $current_user->display_name;
    $message = $userName. ' would like to be considered for placement<br><br>';
    $message.= '<b>Class Name:</b> '.$class_name. '<br><br>';
    $message.= "<b>Age Range:</b> ".$age_range. '<br><br>';
    $message.= "<b>Date(Recipe):</b><br> ".$dates. '<br><br>';
    $message.= "<b>Location:</b><br> ".$loc. '<br>';

    $sent = wp_mail($to, $subject, $message, $headers);

    if($sent) {
        echo 'success';
    }//message sent!
    else  {
        echo 'fail';
    }//message wasn't sent

    wp_die();
}


//Removing a Profile Field
function modify_contact_methods($profile_fields) {

    // Add new fields

    // Remove old fields
//    unset($profile_fields['facebook']);
//    unset($profile_fields['instagram']);
    unset($profile_fields['linkedin']);
    unset($profile_fields['myspace']);
    unset($profile_fields['pinterest']);
    unset($profile_fields['soundcloud']);
    unset($profile_fields['tumblr']);
    unset($profile_fields['twitter']);
    unset($profile_fields['youtube']);
    unset($profile_fields['wikipedia']);

    return $profile_fields;
}

function change_profile_style(){
//    if ( ! current_user_can('manage_options') ) { // 'update_core' may be more appropriate
        if (
            Crb_Current_User()->is( 'crb_facilitator' ) || Crb_Current_User()->is( 'crb_session_admin' )
        ) {
            echo '<script type="text/javascript">jQuery(document).ready(function($) {
            let accountManagementLabelElem = $("h2:contains(\'Account Management\')");
            let personalOptionsLabelElem = $("h2:contains(\'Personal Options\')");
            let personalOptionsElems = personalOptionsLabelElem.next();
            let applicationPasswordsLabelElem = $("h2:contains(\'Application Passwords\')");
            let aboutYourselfLabelElem = $("h2:contains(\'About Yourself\')");
            let adminColorSchemeLabelElem = $("th:contains(\'Admin Color Scheme\')");

			applicationPasswordsLabelElem.next().hide();
            applicationPasswordsLabelElem.hide();
            
            //KTK Session Admin
            $("form#your-profile #ADDRESS1-wrapper").insertBefore("h2:contains(\'Contact Info\')");
            //KTK Facilitator
            $("form#your-profile #ADDRESS-wrapper").insertBefore("h2:contains(\'Contact Info\')");
            
			$(personalOptionsLabelElem).insertAfter(accountManagementLabelElem.next());
			$(personalOptionsElems).insertAfter(personalOptionsLabelElem);
            personalOptionsLabelElem.hide();
            aboutYourselfLabelElem.hide();
            
            adminColorSchemeLabelElem.text("PERSONALIZE THE PANTRY")
		});</script>';
        }
        if (
            Crb_Current_User()->is( 'crb_assistant' ) || Crb_Current_User()->is( 'administrator' )
        ) {

            echo '<script type="text/javascript">jQuery(document).ready(function($) {
                    let personalOptionsLabelElem = $("h2:contains(\'Personal Options\')");
                    let personalOptionsElems = personalOptionsLabelElem.next();
                    let accountManagementLabelElem = $("h2:contains(\'Account Management\')");

                    $("form#your-profile #ADDRESS-wrapper").insertBefore("h2:contains(\'Personal Options\')");
                    $("form#your-profile #ADDRESS1-wrapper").insertBefore("h2:contains(\'Personal Options\')");
                    $(personalOptionsLabelElem).insertAfter(accountManagementLabelElem.next());
			        $(personalOptionsElems).insertAfter(personalOptionsLabelElem);
                });</script>';
			
			 echo '<script type="text/javascript">
                window.onload=function (){
                   const upcomingSessions = document.querySelector(\'.carbon-field.carbon-Html\').innerHTML;
                        if(upcomingSessions !== null){
                            const accountManagement = document.querySelector(\'.AccountManagement\');
                            accountManagement.insertAdjacentHTML(\'beforebegin\',upcomingSessions);
                        } 
                }
            </script>';
			
			echo '<style>
				/*.carbon-container.carbon-container-User_Meta .carbon-field:last-child{
					display: none!important;
				}*/
				.field-holder {
					display: flex;
					flex-wrap: wrap;
					column-gap: 96px;
					row-gap: 5px;
				}          
				.field-holder label{
					font-weight: 600;
					font-size: 14px;
				}          
				.locations-classes-dates-table{
					max-width: 770px;
				}
            </style>';
        }
//    }
}

add_action('add_meta_boxes', 'change_author_metabox');
function change_author_metabox() {
    global $wp_meta_boxes;
    if(isset($wp_meta_boxes['crb_location']['normal']['core']['authordiv']['title'])){
        $wp_meta_boxes['crb_location']['normal']['core']['authordiv']['title']= 'Customer/Partner';
        $wp_meta_boxes['crb_class']['normal']['core']['authordiv']['title']= 'Customer/Partner';
    }
}

function mycustom_comment_form_title_reply( $defaults ) {
    $defaults['title_reply'] = 'START THE CONVERSATION';
    return $defaults;
}
add_filter( 'comment_form_defaults', 'mycustom_comment_form_title_reply' );

// Force template assignment for user schedule page
add_filter('template_include', function($template) {
    if (is_page('user-schedule')) { // Change 'user-schedule' to your page slug
        return get_template_directory() . '/page-user-schedule.php';
    }
    return $template;
});

// Handle getting average rating
add_action('wp_ajax_get_user_avg_rating', 'get_user_avg_rating');
add_action('wp_ajax_nopriv_get_user_avg_rating', 'get_user_avg_rating');
function get_user_avg_rating() {
    $user_id = intval($_GET['user_id']);
    
    // Get rating from Airtable-synced user meta
    $rating = get_user_meta($user_id, 'rating', true);
    
    // If rating is empty or not a number, return 0
    if (empty($rating) || !is_numeric($rating)) {
        $rating = 0;
    }
    
    wp_send_json(['avg_rating' => round($rating, 2)]);
}

// Handle submitting a rating
add_action('wp_ajax_submit_user_rating', 'submit_user_rating');
add_action('wp_ajax_nopriv_submit_user_rating', 'submit_user_rating');
function submit_user_rating() {
    $user_id = intval($_POST['user_id']);
    $rating = intval($_POST['rating']);
    if ($rating < 1 || $rating > 5) {
        wp_send_json(['message' => 'Invalid rating.'], 400);
    }
    $ratings = get_user_meta($user_id, 'user_ratings', true);
    if (!$ratings) $ratings = [];
    if (!is_array($ratings)) $ratings = [];
    $ratings[] = $rating;
    update_user_meta($user_id, 'user_ratings', $ratings);
    wp_send_json(['message' => 'Thank you for your rating!']);
}

add_action('wp_ajax_get_user_data', 'get_user_data');
function get_user_data() {
    $user_id = intval($_GET['user_id']);
    // Get schedules
    $schedules = get_posts([
        'post_type' => 'schedule',
        'meta_key' => 'user_id',
        'meta_value' => $user_id,
        'posts_per_page' => -1
    ]);
    $events = [];
    foreach ($schedules as $schedule) {
        $events[] = [
            'title' => get_the_title($schedule),
            'start' => get_post_meta($schedule->ID, 'start_date', true),
            'end' => get_post_meta($schedule->ID, 'end_date', true),
        ];
    }
    // Get ratings
    $ratings = get_user_meta($user_id, 'user_ratings', true);
    $avg_rating = is_array($ratings) && count($ratings) ? array_sum($ratings) / count($ratings) : 0;
    wp_send_json([
        'events' => $events,
        'avg_rating' => round($avg_rating, 2)
    ]);
}

add_action('wp_ajax_get_airtable_classes', 'get_airtable_classes');
add_action('wp_ajax_nopriv_get_airtable_classes', 'get_airtable_classes');
function get_airtable_classes() {
    $access_token = 'patONWy6xQVO0zOvS.287cc19f96f321d2daa4ca0a9ea594adff6b59ef22de7df1b9bab4cb4b420284';
    $base_id = 'appwucJ3VAIrqPAQQ';
    $table_name = 'class detail';
    $user_name = sanitize_text_field($_GET['user_name']);

    // Use the correct Airtable field names
    $user_field = 'TEAM Links';

    // Use FIND() for case-insensitive partial matching
    $url = "https://api.airtable.com/v0/$base_id/" . rawurlencode($table_name) . 
           "?filterByFormula=" . urlencode("FIND(LOWER('$user_name'), LOWER({{$user_field}}))>0");

    $response = wp_remote_get($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type'  => 'application/json'
        ]
    ]);

    if (is_wp_error($response)) {
        wp_send_json(['error' => 'Request error', 'details' => $response->get_error_message()]);
        return;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['error'])) {
        wp_send_json(['error' => 'Airtable error', 'details' => $data['error']]);
        return;
    }

    $events = [];
    if (!empty($data['records'])) {
        foreach ($data['records'] as $record) {
            $fields = $record['fields'];
            
            // Skip if no start date
            if (empty($fields['START DATE'])) {
                continue;
            }
            
            // Parse the date string from "Wednesday, July 30, 2025 12:30 PM" format
            $start_date = date_create_from_format('l, F j, Y g:i A', $fields['START DATE']);
            if (!$start_date) {
                // Try alternative date formats if the first one fails
                $start_date = strtotime($fields['START DATE']);
                if (!$start_date) {
                    continue; // Skip if date parsing fails
                }
                $start_date = date('Y-m-d\TH:i:s', $start_date);
            } else {
                $start_date = $start_date->format('Y-m-d\TH:i:s');
            }
            
            // Get enrollment number for color coding
            $enrollment = isset($fields['ENROLLMENT']) ? intval($fields['ENROLLMENT']) : 0;
            
            // Determine color based on enrollment
            $color = '#28a745'; // Default green
            if ($enrollment >= 50) {
                $color = '#dc3545'; // Red for high enrollment
            } elseif ($enrollment >= 20) {
                $color = '#ffc107'; // Yellow for medium enrollment
            }
            
            // Format recipes for display
            $recipes = isset($fields['RECIPES (from SCHEDULE Links)']) ? $fields['RECIPES (from SCHEDULE Links)'] : 'N/A';
            
            // Create properly formatted event for FullCalendar
            $event = [
                'id' => $record['id'],
                'title' => isset($fields['TOWN LOCATION']) ? $fields['TOWN LOCATION'] : 'Class',
                'start' => $start_date,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'enrollment' => $enrollment,
                    'recipes' => $recipes,
                    'location' => isset($fields['TOWN LOCATION']) ? $fields['TOWN LOCATION'] : 'N/A',
                ],
                // Create a description for the tooltip/popup
                'description' => sprintf(
                    "Enrollment: %s\nRecipes: %s\nLocation: %s",
                    $enrollment,
                    $recipes,
                    isset($fields['TOWN LOCATION']) ? $fields['TOWN LOCATION'] : 'N/A'
                )
            ];
            
            $events[] = $event;
        }
    }
    
    wp_send_json($events);
}

function get_user_filter_url($user_name) {
	$user_filter_urls = [
        'Alexa Downs' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlYzU5bmU2cmJTZk5WSEZZIl1dXQ',
		'Ali Geronimo' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlY0dMRVNRbFJTNld4WXdJIl1dXQ',
		'Alisa Owen' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlY1lYR3pISU85TnZBWU10Il1dXQ',
		'Amy Alicyn Merritt' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlY0xZZjR3M1ZXUlpPOTF0Il1dXQ',
		'Anisha Espinosa' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlY1pxbTVEeVdZSVZrTUI1Il1dXQ',
		'Branda Noun' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlY1hnWVRpQUJJV0JSU09LIl1dXQ',
		'Catherine Thomas' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlYzBQOHJweTI0SFBxY3ZXIl1dXQ',
        'Christine DiCesare' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlY0pVMDVENWtDN2NHeEdRIl1dXQ',
		'Cindy Elmore' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlY0hKQXBtS29rV3ZYZHhZIl1dXQ',
		'Colleen Quinn' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlYzZvY0JMUGZoZjNZYlJKIl1dXQ',
		'Di Summer' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlY0VJOWRGSWxQa3Q3U1luIl1dXQ',
		'Dina DeAmicis' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlYzNGWVNrQzl6eEhYRWkyIl1dXQ',
		'Doreen Bettano Iovanna' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlY0hmbFFIcmxDVTBTamZDIl1dXQ',
		'Elena Higgins' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlY0dFbzM2QlFmWG9zNjA4Il1dXQ',
		'Elizabeth Hummel' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlYzdBQ1Y1d1RsREFBYVNaIl1dXQ',
		'Gabriella Lazzarino' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlYzZqTDc3d3dkZEhaY1BUIl1dXQ',
		'Holly Flanagan' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlY1J6Q3NFem5TemIwaXF6Il1dXQ',
		'Jeanette Schaible' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlY0dQT05KVkFaZm5GTThRIl1dXQ',
		'Joana Marques' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlY2lIcGNOb2JadUFJSEFRIl1dXQ',
		'Karen Modell Conrad' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlY2U0RHdnVWYwNHZaR1NIIl1dXQ',
		'Kristen Funk' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlY2loYTVVckxUakxwYjhKIl1dXQ',
        'Kristin Dolan' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlY05rQkJZYjhRVVdSZG8yIl1dXQ',
		'LeeAnn Gardner' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlY1lVajNFc1VuejRxQU1JIl1dXQ',
		'Mary Palen' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlY1FIWE5JMFh5ZVBDcnA5Il1dXQ',
        'Mikayla Porcaro' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlY3owVnZpM1RlczZMSUxGIl1dXQ',
		'Preethi Shivaraman' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlYzg0UXhaR3huUXZCNGlHIl1dXQ',
		'Qianzhi Jiang' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlY3hldUdIMFN4RmZwYVpCIl1dXQ',
		'Sheila Jewer' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlY20zTmYyV1pGeXBnTGM5Il1dXQ',
		'Sheila M Harney' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlY1RuTXRtSTM1T3JQanhlIl1dXQ',
		'Sivan Avramovich' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlY2RWaTdXNVNpM2VCNFdwIl1dXQ',
		'Sonya Good' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlYzRjZjR6dWFjOU9vTzNMIl1dXQ',
        'EMILY TEST' => '?ywyKN=b%3AWzAsWyJ1eUJvbiIsNixbInJlY3Y5eTVUY1k3THoxODNZIl1dXQ',
        // Add more users and their specific filter URLs here
    ];

	$filter_url = isset($user_filter_urls[$user_name]) 
        ? $user_filter_urls[$user_name] 
        : '';
	return $filter_url;
}

/**
 * Add Schedule admin page accessible at wp-admin/schedule.php
 */
add_action('admin_menu', 'add_schedule_admin_page');
function add_schedule_admin_page()
{
    add_menu_page(
        'Schedule', // Page title
        'Schedule', // Menu title
        'read', // Capability required
        'schedule.php', // Menu slug (this makes it accessible at wp-admin/schedule.php)
        'schedule_admin_page_callback', // Callback function
        'dashicons-calendar-alt', // Icon
        3 // Position
    );
}

/**
 * Display callback for the schedule admin page.
 */
function schedule_admin_page_callback()
{
    // Check if user has permission to view this page
    if (!current_user_can('read')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    
    // Get current WordPress user
    $current_user = wp_get_current_user();
    
    // If user is not logged in, show a message
    if (!$current_user->exists()) {
        ?>
        <div class="wrap">
            <h1>Schedule</h1>
            <div style="text-align: center; padding: 50px 20px; font-size: 18px; color: #666;">
                Please log in to view your schedule.
            </div>
        </div>
        <?php
        return;
    }
    
    // Get user's display name for filtering
    $user_name = esc_attr($current_user->user_login);
    $filter_param = get_user_filter_url($user_name);
    
    // Create the embed URL
    $embed_url = 'https://airtable.com/embed/appwucJ3VAIrqPAQQ/shrcLkdMj5dKnTo9N' . $filter_param;
    
    // Create the filtered embed code
    $embed_code = '<iframe class="airtable-embed"
      src="' . esc_url($embed_url) . '" 
      frameborder="0" onmousewheel="" width="100%" height="733" 
      style="background: transparent; border: 1px solid #ccc;">
    </iframe>';
    
    // Display the schedule page with embed code
    ?>
    <div class="wrap">
            <?php echo $embed_code; ?>
    </div>
    <?php
}
/**
 * Display user rating in profile page
 */
function crb_display_user_profile() {
    $user_id = get_current_user_id();
    if (!$user_id) return 'User not found';
    
    // Get the main rating from Airtable-synced user meta
    $profile = get_user_meta($user_id, 'Profile', true);
    
    $output = '<div>';
    
    if (!empty($profile)) {
        $output .= '<div style="display: flex; align-items: center; gap: 10px;">';
        $output .= '<input type="text" name="profile_link" id="profile_link" value="' . $profile . '" readonly="readonly" class="regular-text">';
        $output .= '<a href="' . esc_url($profile) . '" target="_blank" class="button button-secondary">';
        $output .= '<span class="dashicons dashicons-external" style="margin-right: 5px;"></span>';
        $output .= 'Open Profile Link';
        $output .= '</a>';
        $output .= '</div>';
        
    }
    
    $output .= '</div>';
    
    return $output;
}
