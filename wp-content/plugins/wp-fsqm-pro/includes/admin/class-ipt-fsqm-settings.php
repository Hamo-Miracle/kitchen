<?php
/**
 * IPT FSQM Settings
 *
 * Class for handling the Settings page under eForm
 *
 * @author Swashata <swashata4u@gmail.com>
 * @package eForm - WordPress Form Builder
 * @subpackage Admin\Settings
 * @codeCoverageIgnore
 */
class IPT_FSQM_Settings extends IPT_FSQM_Admin_Base {
	public function __construct() {
		$this->capability = 'manage_feedback';
		$this->action_nonce = 'ipt_fsqm_settings_nonce';

		parent::__construct();

		$this->icon = 'settings';

		$this->post_result[4] = array(
			'type' => 'okay',
			'msg' => __( 'Successfully saved the options as well as created sample forms. You may now head to <a href="admin.php?page=ipt_fsqm_all_forms">View all Forms</a> to start editing them.', 'ipt_fsqm' ),
		);
	}

	/*==========================================================================
	 * SYSTEM METHODS
	 *========================================================================*/

	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'eForm Settings', 'ipt_fsqm' ), __( 'Settings', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_settings', array( &$this, 'index' ) );
		parent::admin_menu();
	}

	public function index() {
		global $ipt_fsqm_settings;
		$ipt_fsqm_key = get_option( 'ipt_fsqm_key' );
		$updater = EForm_AutoUpdate::instance();
		$this->index_head( __( 'eForm <span class="ipt-icomoon-arrow-right2"></span> Settings', 'ipt_fsqm' ) );
		$eform_activation_status = $updater->current_activation_status( $ipt_fsqm_settings['purchase_code'] );
		$purchase_code_items = array();
		$purchase_code_items[] = array(
			'name' => 'global[purchase_code]',
			'label' => __( 'eForm Purchase Code', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => array( 'global[purchase_code]', $ipt_fsqm_settings['purchase_code'], __( 'Required', 'ipt_fsqm' ), 'fit', 'normal', array( 'code' ) ),
			'help' => __( 'Enter your purchase code to activate.', 'ipt_fsqm' ),
			// translators: %1$s is the URL of the purchase code guide
			'description' => sprintf( __( '<a href="%1$s" target="_blank" rel="noopener">Follow this</a> to find out your purchase code.', 'ipt_fsqm' ), 'https://iptms.co/envatopc' ),
		);
		$purchase_code_items[] = array(
			'name' => '',
			'ui' => true == $eform_activation_status['activated'] ? 'msg_okay' : 'msg_error',
			'param' => array( $eform_activation_status['msg'] ),
		);
		$purchase_code_items = apply_filters( 'eform_purchase_code_form', $purchase_code_items );
?>
<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
	<div class="ipt_uif_box cyan">
		<h3><span class="ipt-icomoon-shield2"></span><?php _e( 'Activate eForm', 'ipt_fsqm' ); ?></h3>
	</div>
	<div class="ipt_uif_iconbox_inner">
		<?php $this->ui->form_table( $purchase_code_items ); ?>
	</div>
</div>
<?php $this->print_form_buttons( __( 'Save Changes', 'ipt_fsqm' ), false ); ?>
<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
	<div class="ipt_uif_box cyan">
		<h3><span class="ipt-icomoon-cog"></span><?php _e( 'Global API Keys', 'ipt_fsqm' ); ?></h3>
	</div>
	<div class="ipt_uif_iconbox_inner">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="global_email"><?php _e( 'Google Places API Key', 'ipt_fsqm' ); ?></label>
					</th>
					<td>
						<?php $this->ui->password( 'global[gplaces_api]', $ipt_fsqm_settings['gplaces_api'] ); ?>
					</td>
					<td>
						<?php $this->ui->help_head(); ?>
							<p><?php _e( 'You need to enter a valid Google Places API to make sure the localtion picker (GPS) element works.', 'ipt_fsqm' ); ?></p>
							<p><?php _e( 'More instructions can be found <a href="https://wpquark.com/kb/?p=9859">here</a>.', 'ipt_fsqm' ); ?></p>
						<?php $this->ui->help_tail(); ?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<?php $this->print_form_buttons( __( 'Save Changes', 'ipt_fsqm' ), false ); ?>
<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
	<div class="ipt_uif_box cyan">
		<h3><span class="ipt-icomoon-cog"></span><?php _e( 'Modify Plugin Settings', 'ipt_fsqm' ); ?></h3>
	</div>
	<div class="ipt_uif_iconbox_inner">
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="global_email"><?php _e( 'Global Notification Email', 'ipt_fsqm' ); ?></label>
				</th>
				<td>
					<?php $this->print_input_text( 'global[email]', $ipt_fsqm_settings['email'], 'regular-text code' ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
						<?php _e( 'Enter the email where you want to send notifications for all the feedback forms.', 'ipt_fsqm' ); ?>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="global_track_page"><?php _e( 'Single Submission Trackback Page for Unregistered Users', 'ipt_fsqm' ); ?></label>
				</th>
				<td>
					<?php $this->ui->dropdown_pages( array(
						'name' => 'global[track_page]',
						'selected' => $ipt_fsqm_settings['track_page'],
						'show_option_none' => __( 'Please select a page', 'ipt_fsqm' ),
					) ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
						<?php _e( 'Select the page where you\'ve put the <code>[ipt_fsqm_trackback]</code> shortcode. The page will be linked throughout all the notification email.', 'ipt_fsqm' ); ?>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="global_utrack_page"><?php _e( 'Central Trackback page for Registered Users', 'ipt_fsqm' ); ?></label>
				</th>
				<td>
					<?php $this->ui->dropdown_pages( array(
						'name' => 'global[utrack_page]',
						'selected' => $ipt_fsqm_settings['utrack_page'],
						'show_option_none' => __( 'Please select a page', 'ipt_fsqm' ),
					) ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
						<?php _e( 'Select the page where you\'ve put the <code>[ipt_fsqm_utrackback]</code> shortcode. The page will be linked throughout all the notification email.', 'ipt_fsqm' ); ?>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php $this->ui->generate_label( 'global[key]', __( 'Secret Encryption Key', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->text( 'global[key]', $ipt_fsqm_key, __( 'Can not be empty', 'ipt_fsqm' ), 'fit', 'normal', array( 'code' ) ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
					<p><?php _e( 'This key is used to generate the trackback keys. If you change this, then all the trackback codes will get reset.', 'ipt_fsqm' ); ?></p>
					<p><?php _e( 'Use this with extreme caution and change only if necessary. The new trackback keys will not be sent to the users.', 'ipt_fsqm' ); ?></p>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php $this->ui->generate_label( 'global[disable_un]', __( 'Disable Activation Notice', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->toggle( 'global[disable_un]', __( 'yes', 'ipt_fsqm' ), 'no', $ipt_fsqm_settings['disable_un'] ); ?>
				</td>
				<td>
					<?php $this->ui->help( __( 'Starting eForm version 4.0, eForm and add-ons will show a notice if you haven\'t activated with a purchase code. Enable this option to hide the notice.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php $this->ui->generate_label( 'global[delete_uninstall]', __( 'Delete all Data when uninstalling plugin', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->toggle( 'global[delete_uninstall]', __( 'yes', 'ipt_fsqm' ), 'no', $ipt_fsqm_settings['delete_uninstall'] ); ?>
				</td>
				<td>
					<?php $this->ui->help( __( 'If you want to completely wipe out all data when uninstalling, then have this enabled. Keep it disabled, if you are planning to update the plugin by uninstalling and then reinstalling.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php $this->ui->generate_label( 'global[sandboxing]', __( 'Sandbox eForm Admin Pages', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->toggle( 'global[sandboxing]', __( 'yes', 'ipt_fsqm' ), 'no', $ipt_fsqm_settings['sandboxing'] ); ?>
				</td>
				<td>
					<?php $this->ui->help( __( 'Some plugins may enqueue CSS and JS assets all over the admin pages which could potentially break eForm functionality. Enabling it ensures that only WordPress core and eForm assets are loaded when accessing eForm related admin pages.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
		</table>
	</div>
</div>
<?php $this->print_form_buttons( __( 'Save Changes', 'ipt_fsqm' ), false ); ?>
<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
	<div class="ipt_uif_box cyan">
		<h3><span class="ipt-icomoon-file2"></span><?php _e( 'Modify Standalone Forms Settings', 'ipt_fsqm' ); ?></h3>
	</div>
	<div class="ipt_uif_iconbox_inner">
		<table class="form-table">
			<tbody>
				<tr>
				<th scope="row">
					<?php $this->ui->generate_label( 'global[standalone][base]', __( 'Permalink Base', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->text( 'global[standalone][base]', $ipt_fsqm_settings['standalone']['base'], __( 'Can not be empty', 'ipt_fsqm' ), 'fit', 'normal', 'code' ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
					<p><?php _e( 'This will be the base of any permalink generated for your standalone forms.', 'ipt_fsqm' ); ?></p>
					<p><?php _e( 'If you want the links to be like <code>http://example.com/<strong>webforms</strong>/my-awesome-form/01/</code> then use <code>webforms</code> as the base.', 'ipt_fsqm' ); ?></p>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php $this->ui->generate_label( 'global[standalone][head]', __( 'HTML Head Section', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->textarea( 'global[standalone][head]', $ipt_fsqm_settings['standalone']['head'], __( 'CSS or JS or Meta Tags', 'ipt_fsqm' ), 'widefat', 'normal', 'code' ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
					<p><?php _e( 'If you want to put any custom CSS code or other HTML tags inside the <code>&lt;head&gt;</code> section, then do it here.', 'ipt_fsqm' ); ?></p>
					<p><?php _e( 'Please note that, if a css file named fsqm-pro.css or fsqm-pro-{form_id}.css is present inside your current theme directory, then it will be included by default.', 'ipt_fsqm' ); ?></p>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php $this->ui->generate_label( 'global[standalone][before]', __( 'Before Form HTML', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->wp_editor( 'global[standalone][before]', $ipt_fsqm_settings['standalone']['before'] ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
					<p><?php _e( 'This content will be appended before the output of the form.', 'ipt_fsqm' ); ?></p>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php $this->ui->generate_label( 'global[standalone][after]', __( 'After Form HTML', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->wp_editor( 'global[standalone][after]', $ipt_fsqm_settings['standalone']['after'] ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
					<p><?php _e( 'This content will be appended after the output of the form.', 'ipt_fsqm' ); ?></p>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
			</tbody>
		</table>
	</div>
</div>
		<?php
		$this->index_foot();
	}

	public function save_post( $check_referer = true ) {
		parent::save_post();

		$settings = array(
			'email' => $this->post['global']['email'],
			'track_page' => $this->post['global']['track_page'],
			'utrack_page' => $this->post['global']['utrack_page'],
			'delete_uninstall' => isset( $this->post['global']['delete_uninstall'] ) && '' != $this->post['global']['delete_uninstall'] ? true : false,
			'sandboxing' => isset( $this->post['global']['sandboxing'] ) && '' != $this->post['global']['sandboxing'] ? true : false,
			'standalone' => array(
				'base' => $this->post['global']['standalone']['base'],
				'before' => $this->post['global']['standalone']['before'],
				'after' => $this->post['global']['standalone']['after'],
				'head' => $this->post['global']['standalone']['head'],
			),
			'disable_un' => isset( $this->post['global']['disable_un'] ) && '' != $this->post['global']['disable_un'] ? true : false,
			'gplaces_api' => $this->post['global']['gplaces_api'],
			'purchase_code' => $this->post['global']['purchase_code'],
		);

		if ( trim( $settings['standalone']['base'] ) == '' ) {
			$settings['standalone']['base'] = 'eforms';
		}

		$settings['standalone']['base'] = sanitize_title( $settings['standalone']['base'] );

		update_option( 'ipt_fsqm_settings', $settings );

		$key = $this->post['global']['key'];
		if ( trim( $key ) == '' ) {
			$key = NONCE_SALT;
		}
		update_option( 'ipt_fsqm_key', $key );

		// Get the activation token
		$license = EForm_AutoUpdate::instance();
		$license->set_token_from_code( $this->post['global']['purchase_code'] );

		wp_redirect( add_query_arg( array( 'post_result' => 1 ), $_POST['_wp_http_referer'] ) );
		die();
	}

	public function on_load_page() {
		flush_rewrite_rules();
		parent::on_load_page();
		get_current_screen()->add_help_tab( array(
				'id' => 'track',
				'title' => __( 'Settings', 'ipt_fsqm' ),
				'content' =>
				'<p>' . __( 'There are five settings which you can change.', 'ipt_fsqm' ) . '<p>' .
				'<ul>' .
				'<li>' . __( '<strong>Global Notification Email:</strong> Enter an email where the notification will be sent everytime a user submits any of the forms.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Single Submission Trackback Page for Unregistered Users:</strong> Select the page where you\'ve put the <code>[ipt_fsqm_trackback]</code> shortcode. From this page users can see their submission and print if they want. The page will be linked throughout all the notification email.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Central Trackback page for Registered Users:</strong> Select the page where you\'ve put the [ipt_fsqm_utrackback] shortcode. From this page, logged in users will be able to see all their submissions and also they will be getting a link to the trackback page. The page will be linked throughout all the trackbacks whenever applicable.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Backward Compatible Shortcode:</strong> If you are coming from older version (prior to version 2.x) then you need to leave it enabled in order to make the older format of shortcodes work. Since version 2.x, the shortcode format was changed to a more localized form.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Secret Encryption Key:</strong> This key is used to generate the trackback keys. If you change this, then all the trackback codes will get reset.', 'ipt_fsqm' ) . '</li>' .
				'</ul>' .
				'<p>' . __( 'Please set the settings up before going live with your forms.', 'ipt_fsqm' ) . '</p>',
			) );
		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'ipt_fsqm' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);
	}
}
