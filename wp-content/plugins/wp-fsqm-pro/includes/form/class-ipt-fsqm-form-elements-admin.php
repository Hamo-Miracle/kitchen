<?php
/**
 * WP Feedback, Surver & Quiz Manager - Pro Form Elements Class
 * Admin area
 *
 * Populates the form builder
 * Works along with IPT_Plugin_UIF_Admin
 *
 *
 * @package eForm - WordPress Form Builder
 * @subpackage Form\Admin
 * @author Swashata Ghosh <swashata@intechgrity.com>
 * @codeCoverageIgnore
 */
class IPT_FSQM_Form_Elements_Admin extends IPT_FSQM_Form_Elements_Base {
	/**
	 * The UI variable to populate all the necessary HTML
	 *
	 * @var IPT_Plugin_UIF_Admin
	 */
	public $ui;

	public $save_process = array();

	/*==========================================================================
	 * CONSTRUCTOR
	 *========================================================================*/
	public function __construct( $form_id = null ) {
		$this->ui = IPT_Plugin_UIF_Admin::instance();
		parent::__construct( $form_id );
	}

	/*==========================================================================
	 * Help Section
	 *========================================================================*/
	public function add_help() {
		get_current_screen()->add_help_tab( array(
			'id' => 'overview',
			'title' => __( 'Overview', 'ipt_fsqm' ),
			'content' =>
			'<p>' . __( 'This page provides you all the tool you can use to create and customize a form. A form can have any number of containers. Simple click on the Add Containers button and it will add a new container where you can drag and drop new form elements.', 'ipt_fsqm' ) . '</p>' .
			'<p>' . __( 'A form can have a total of 48 elements as of now (without any extensions) which are catrgorized into 4 type.', 'ipt_fsqm' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<strong>Design & Security:</strong> Use the elements to add eye candy or security elements to your form. Check the Design Elements section for more.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>Multiple Choice Questions:</strong> Add MCQs to your form which can be used to generate quiz and/or collect surveys. Elements have scoring option whenever applicable and all of them will appear on the Report & Analysis. Check Multiple Choice Question Elements for more information.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>Feedback Questions:</strong> These are basically freetype questions, where users have to put their own answers. All of the answers can be set to go to one or more specific emails. This becomes handy if you are collecting feedbacks on different topics and have to email different people the answers of different topics.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>Other Form Elements:</strong> Here we have 4 predefined text fields (First Name, Last Name, Email, Phone Number) which you can add to the form. Apart from that, another 14 types of form elements can be added. Check Other Form Elements for more information.', 'ipt_fsqm' ) . '</li>' .
			'</ul>' .
			'<p>' . __( 'You can get more help by clicking the [?] icon beside every options. Or you can check the corresponding tabs inside this help screen...', 'ipt_fsqm' ) . '</p>'
		) );

		get_current_screen()->add_help_tab( array(
			'id' => 'form-customization',
			'title' => __( 'Form Customization', 'ipt_fsqm' ),
			'content' =>
			'<p>' . __( 'Right above the form builder, you will see 4 tabs from where you can customize many aspects of the form.', 'ipt_fsqm' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<strong>Form Name:</strong> As the title suggests, enter the name of the form.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>Form Type:</strong> Select the type of the form. Currently we support 3 kinds of appearances. Each appearance has it\'s own different sets of options. Please check the help icon associated with the option to get more information.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>Form Settings:</strong> Various form related settings. Please read below:', 'ipt_fsqm' ) .
			'<ul>' .
			'<li>' . __( '<strong>General Settings:</strong> Have a Terms & Conditions Page, which shows a single checkbox with a link to your page just before submitting the form, an Administrator Remarks Title and Default Administrator Remarks.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>Submission Limitation:</strong> Limit submission of this form per email address and/or per IP address.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>Progress Buttons:</strong> Change the labels of the Next, Previous and Submit buttons. They will show up, whenever applicable.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>Form Submission:</strong> Enter your own processing and success title along with a success message.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>User Notification:</strong> Customize how users (the one submitting the form) are notified.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>Admin Notification:</strong> Customize how admins are notified.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>Redirection:</strong> Set customized redirection to a page when user submits. They can be redirected to a particular page or specific pages depending on their score.', 'ipt_fsqm' ) . '</li>' .
			'</ul>' .
			'</li>' .
			'<li>' . __( '<strong>Form Theme:</strong> Various jQuery UI Themes are available for you to use directly. We have also added font customization options.', 'ipt_fsqm' ) . '</li>' .
			'</ul>' .
			'<p>' . __( 'You can get more help by clicking the [?] icon beside every options.', 'ipt_fsqm' ) . '</p>'
		) );

		foreach ( $this->elements as $element_type => $elements ) {
			if ( $element_type == 'layout' ) {
				continue;
			}
			$content = '<p>' . $elements['description'] . '</p>';
			if ( isset( $elements['elements'] ) && is_array( $elements['elements'] ) && !empty( $elements['elements'] ) ) {
				$content .= '<ul>';
				foreach ( $elements['elements'] as $element ) {
					$content .= '<li><strong>' . $element['title'] . ':</strong> ' . $element['description'] . '</li>';
				}
				$content .= '</ul>';
			}
			get_current_screen()->add_help_tab( array(
					'id' => 'form-elements-' . $element_type,
					'title' => $elements['title'],
					'content' => $content,
				) );
		}
	}

	/*==========================================================================
	 * PRIMARY APIs
	 *========================================================================*/
	public function enqueue_assets() {
		// Check if user has already checked the onboarding script
		$has_user_onboarded = get_user_meta( get_current_user_id(), 'eform-builder-onboarded', true );
		// If not, then update the value so that she doesn't see
		// it again
		if ( ! $has_user_onboarded ) {
			update_user_meta( get_current_user_id(), 'eform-builder-onboarded', '1' );
		}
		// but delete it, if a special url parameter is present
		if ( isset( $_GET['eform-reset-onboarding'] ) ) {
			delete_user_meta( get_current_user_id(), 'eform-builder-onboarded' );
			$has_user_onboarded = false;
		}
		wp_enqueue_script( 'eform-izi-toast', IPT_FSQM_Loader::$bower_components . 'izitoast/dist/js/iziToast.min.js', [], IPT_FSQM_Loader::$version );
		wp_enqueue_script( 'eform-tippyjs', IPT_FSQM_Loader::$bower_components . 'tippyjs/dist/tippy.all.min.js', [], IPT_FSQM_Loader::$version );
		wp_enqueue_style( 'eform-izitoast-style', IPT_FSQM_Loader::$bower_components . 'izitoast/dist/css/iziToast.min.css', [], IPT_FSQM_Loader::$version );
		wp_enqueue_script( 'eform-onboarding-driver', IPT_FSQM_Loader::$bower_components . 'driver.js/dist/driver.min.js', [], IPT_FSQM_Loader::$version );
		wp_enqueue_style( 'eform-onboarding-css', IPT_FSQM_Loader::$bower_components . 'driver.js/dist/driver.min.css', [], IPT_FSQM_Loader::$version );
		wp_enqueue_script( 'eform-form-builder', IPT_FSQM_Loader::$static_location . 'admin/js/jquery.eform-form-builder.min.js', array( 'jquery', 'eform-onboarding-driver', 'eform-tippyjs', 'eform-izi-toast', 'ipt-plugin-uif-admin-js' ), IPT_FSQM_Loader::$version );
		wp_localize_script( 'eform-form-builder', 'eFormBuilderL10n', [
			'shortcutEnabled' => __( 'Shortcut keys enabled', 'ipt_fsqm' ),
			'shortcutDisabled' => __( 'Shortcut keys disabled', 'ipt_fsqm' ),
			'elementAdded' => __( 'Element added', 'ipt_fsqm' ),
			'elementMoved' => __( 'Element moved', 'ipt_fsqm' ),
			'elementDeleted' => __( 'Element deleted', 'ipt_fsqm' ),
			'elementCopied' => __( 'Element copied', 'ipt_fsqm' ),
			'pageAdded' => __( 'Page added', 'ipt_fsqm' ),
			'pageDeleted' => __( 'Page deleted', 'ipt_fsqm' ),
			'pageCopied' => __( 'Page copied', 'ipt_fsqm' ),
			'pageSorted' => __( 'Page moved', 'ipt_fsqm' ),
			'settingsClose' => __( 'Element config updated', 'ipt_fsqm' ),
			'formSaved' => __( 'Form saved', 'ipt_fsqm' ),
			'nameChanged' => __( 'Form name updated', 'ipt_fsqm' ),
			'customizerClose' => __( 'Form config updated', 'ipt_fsqm' ),
			'done' => __( 'Done', 'ipt_fsqm' ),
			'saveError' => __( 'Could not connect to server. Please try again!', 'ipt_fsqm' ),
			'addingElement' => __( 'Adding new form element', 'ipt_fsqm' ),
			'addingPage' => __( 'Adding new form page', 'ipt_fsqm' ),
			'onBoarding' => [
				'prev' => __( 'Previous', 'ipt_fsqm' ),
				'next' => __( 'Next', 'ipt_fsqm' ),
				'done' => __( 'Done', 'ipt_fsqm' ),
				'close' => __( 'Close', 'ipt_fsqm' ),
				'isOnBoarded' => (bool) $has_user_onboarded,
				'openVideo' => __( 'Video Guide', 'ipt_fsqm' ),
				'startTour' => __( 'Start Tour', 'ipt_fsqm' ),
				'steps' => [
					[
						'title' => __( 'eForm Builder Tour', 'ipt_fsqm' ),
						'description' => __( 'This is your first time using eForm so we can quickly go through the interface to get you upto speed. Click on Video Guide to see a 9 mins video instead. You will not see this dialog again. But you can start the tour or play the video from the sidebar.', 'ipt_fsqm' ),
					],
					[
						'title' => __( 'Editor Pane', 'ipt_fsqm' ),
						'description' => __( 'On left you see the building blocks of your form. You can sort elements by dragging and click on it to open configuration. You can sort pages by dragging horizontally.', 'ipt_fsqm' ),
					],
					[
						'title' => __( 'Preview Pane', 'ipt_fsqm' ),
						'description' => __( 'On right you see the form itself. You can hover on an element and click the gear icon to quickly open it\'s configuration.', 'ipt_fsqm' ),
					],
					[
						'title' => __( 'Toolbar', 'ipt_fsqm' ),
						'description' => __( 'You can toggle the views with these buttons, or change the live view screen size.', 'ipt_fsqm' ),
					],
					[
						'title' => __( 'Add pages', 'ipt_fsqm' ),
						'description' => __( 'Add new page to your form by clicking this button.', 'ipt_fsqm' ),
					],
					[
						'title' => __( 'Add elements', 'ipt_fsqm' ),
						'description' => __( 'Add elements by clicking on them or drag and drop to the page. You can search or click on the help icon to quickly filter through elements.', 'ipt_fsqm' ),
					],
					[
						'title' => __( 'Form Config', 'ipt_fsqm' ),
						'description' => __( 'Configure form settings like notifications, integrations, payment, quiz score, timer, stopwatch, redirection etc.', 'ipt_fsqm' ),
					],
					[
						'title' => __( 'Form Style', 'ipt_fsqm' ),
						'description' => __( 'Personalize your form theme, colors, button styles, fonts and more.', 'ipt_fsqm' ),
					],
					[
						'title' => __( 'Learn More', 'ipt_fsqm' ),
						'description' => __( 'For a quick start video click on this button. Also find links for documentation and support. You are now all set. Have fun. Remember to check the <strong>video</strong> for quick introduction.', 'ipt_fsqm' ),
					],
				],
			],
		] );
	}

	public function show_form() {
		//array( 'text', 'name', 'size', 'style', 'state', 'classes', 'type', 'data', 'atts', 'url', 'icon', 'icon_position' )
		$eform_standalone_permalink = IPT_FSQM_Form_Elements_Static::standalone_permalink_parts( $this->form_id );
		$eform_standalone_url = '';
		if ( is_array( $eform_standalone_permalink ) ) {
			$eform_standalone_url = $eform_standalone_permalink['url'];
		}
		$toolbar_buttons = array(
			1 => array( __( 'Save', 'ipt_fsqm' ), '', 'large', '2', 'normal', array(), 'button', array(), array(), '', 'disk' ),
		);
		$tab_settings = array();

		$tab_settings[] = array(
			'id' => 'ipt_fsqm_form_type',
			'label' => __( 'Form Type', 'ipt_fsqm' ),
			'callback' => array( $this, 'form_type' ),
		);
		$tab_settings[] = array(
			'id' => 'ipt_fsqm_form_settings',
			'label' => __( 'Form Settings', 'ipt_fsqm' ),
			'callback' => array( $this, 'form_settings' ),
			'has_inner_tab' => true,
		);
		$tab_settings[] = array(
			'id' => 'ipt_fsqm_form_quiz_settings',
			'label' => __( 'Quiz Settings', 'ipt_fsqm' ),
			'callback' => array( $this, 'quiz_settings' ),
			'has_inner_tab' => true,
		);
		$tab_settings[] = array(
			'id' => 'ipt_fsqm_form_rmail',
			'label' => __( 'Result & email', 'ipt_fsqm' ),
			'callback' => array( $this, 'result_email' ),
			'has_inner_tab' => true,
		);
		$tab_settings[] = array(
			'id' => 'ipt_fsqm_form_integration',
			'label' => __( 'Integration', 'ipt_fsqm' ),
			'callback' => array( $this, 'integration' ),
			'has_inner_tab' => true,
		);
		// $tab_settings[] = array(
		// 	'id' => 'ipt_fsqm_form_theme',
		// 	'label' => __( 'Theme', 'ipt_fsqm' ),
		// 	'callback' => array( $this, 'theme' ),
		// );
		$tab_settings[] = array(
			'id' => 'ipt_fsqm_core_intg',
			'label' => __( 'WP Core', 'ipt_fsqm' ),
			'callback' => array( $this, 'wp_core' ),
			'has_inner_tab' => true,
		);
		$tab_settings[] = array(
			'id' => 'ipt_fsqm_form_payment',
			'label' => __( 'Payment', 'ipt_fsqm' ),
			'callback' => array( $this, 'payment' ),
			'has_inner_tab' => true,
		);
		$builder_labels = array(
			'design' => __( 'D', 'ipt_fsqm' ),
			'mcq' => __( 'M', 'ipt_fsqm' ),
			'freetype' => __( 'F', 'ipt_fsqm' ),
			'pinfo' => __( 'O', 'ipt_fsqm' ),
		);

		$tab_settings = apply_filters( 'ipt_fsqm_admin_tab_settings', $tab_settings, $this );
?>
<?php wp_nonce_field( 'ipt_fsqm_form_save_ajax', 'ipt_fsqm_form_save_ajax' ); ?>
<?php $this->ui->clear(); ?>
<?php $this->ui->ajax_loader( false, 'ipt_fsqm_fb_p_al', array(), true, __( 'Loading', 'ipt_fsqm' ) ); ?>
<div id="ipt_fsqm_form" class="ipt_uif_container ipt_fsqm_fb_hidden_init">
	<?php if ( $this->form_id != null ) : ?>
	<input type="hidden" name="form_id" id="form_id" value="<?php echo $this->form_id; ?>" />
	<?php endif; ?>
	<!-- Form Settings -> Tabs -->
	<div id="ipt_fsqm_form_customization_wrap" class="wpq-eform-main-settings-wrap" style="display: none">
		<div id="ipt_fsqm_form_customization" class="ipt-eform-backoffice wpq-eform-main-settings">
			<?php $this->ui->tabs( $tab_settings, false ); ?>
			<div class="wpq-eform-main-settings-close"><i class="ipt-icomoon-check"></i></div>
			<?php $this->ui->buttons( $toolbar_buttons, '', [ 'ipt_uif_toolbar', 'wpq-eform-main-settings-toolbar', 'ipt-eform-customizer-save' ] ); ?>
		</div>
	</div>
	<!-- End Form Settings -->

	<!-- Form Theme -> Tabs -->
	<div id="ipt_fsqm_form_theme_settings_wrap" class="wpq-eform-main-settings-wrap" style="display: none">
		<div id="ipt_fsqm_form_theme_settings" class="ipt-eform-backoffice wpq-eform-main-settings">
			<h3 class="wpq-eform-main-settings-head"><?php _e( 'FORM STYLE', 'ipt_fsqm' ); ?></h3>
			<div id="ipt_fsqm_form_theme" class="wpq-eform-main-settings-body">
				<?php $this->theme(); ?>
			</div>
			<div class="wpq-eform-main-settings-close"><i class="ipt-icomoon-check"></i></div>
			<?php $this->ui->buttons( $toolbar_buttons, '', [ 'ipt_uif_toolbar', 'wpq-eform-main-settings-toolbar', 'ipt-eform-customizer-save' ] ); ?>
		</div>
	</div>
	<!-- End Form Theme -->

	<!-- Builder Layout -->
	<?php $this->ui->builder_init( 'ipt_fsqm_form_builder', array( $this, 'builder' ), $builder_labels ); ?>
	<!-- End Builder Layout -->
	<div class="clear"></div>
</div>
		<?php
		$this->ui->ajax_loader( true, 'ipt_fsqm_ajax_loader', array(
			'save' => __( 'Saving', 'ipt_fsqm' ),
			'preview' => __( 'Generating Preview', 'ipt_fsqm' ),
			'success' => __( 'Success', 'ipt_fsqm' ),
		) );
	}

	public function ajax_save() {
		if ( !wp_verify_nonce( $this->post['ipt_fsqm_form_save_ajax'], 'ipt_fsqm_form_save_ajax' ) || ! current_user_can( 'manage_feedback' ) ) {
			echo 'cheating';
			die();
		}

		$id = $this->process_save();
		$live_view = new EForm_Live_View( $id );
		$html_stream = $live_view->stream_html();
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		echo json_encode( $html_stream );
		die();
	}

	/**
	 * Process the save
	 *
	 * @global array $ipt_fsqm_info
	 * @global wpdb $wpdb
	 */
	public function process_save() {
		global $ipt_fsqm_info, $wpdb;
		// Reinit to the current form
		if ( isset( $this->post['form_id'] ) ) {
			$this->init( $this->post['form_id'] );
		}

		//Set all the variables
		$layout = array();
		$this->save_process = array(
			'design' => array(),
			'mcq' => array(),
			'freetype' => array(),
			'pinfo' => array(),
		);

		// Get the settings
		$settings = $this->merge_elements( $this->post['settings'], $this->get_default_settings() );
		if ( '' != $settings['user']['smtp_config']['password'] ) {
			$settings['user']['smtp_config']['password'] = $this->encrypt( $settings['user']['smtp_config']['password'] );
		}
		// Manage Aweber
		// Is the settings enabled?
		if ( $settings['integration']['aweber']['enabled'] == true ) {
			// Can be a new authorization code
			if ( $this->settings['integration']['aweber']['prevac'] != $settings['integration']['aweber']['authorization_code'] ) {
				try {
					$aw_credentials = AWeberAPI::getDataFromAweberID( $settings['integration']['aweber']['authorization_code'] );
					foreach ( array( 'consumerKey', 'consumerSecret', 'accessKey', 'accessSecret' ) as $aweber_key => $aweber_val ) {
						$settings['integration']['aweber'][$aweber_val] = $aw_credentials[$aweber_key];
					}
					$settings['integration']['aweber']['prevac'] = $settings['integration']['aweber']['authorization_code'];
				} catch ( Exception $e ) {
					foreach ( array( 'consumerKey', 'consumerSecret', 'accessKey', 'accessSecret' ) as $aweber_key ) {
						$settings['integration']['aweber'][$aweber_key] = '';
					}
					$settings['integration']['aweber']['authorization_code'] = __( 'Invalid Code provided', 'ipt_fsqm' );
					$settings['integration']['aweber']['prevac'] = '';
				}
			// It is the same one
			} else {
				// Use the same tokens
				foreach ( array( 'consumerKey', 'consumerSecret', 'accessKey', 'accessSecret' ) as $aweber_key ) {
					$settings['integration']['aweber'][$aweber_key] = $this->settings['integration']['aweber'][$aweber_key];
				}
				$settings['integration']['aweber']['prevac'] = $settings['integration']['aweber']['authorization_code'];
			}

		// Reset aweber, if anything was even present
		} else {
			$settings['integration']['aweber']['authorization_code'] = '';
			$settings['integration']['aweber']['prevac'] = '';
			foreach ( array( 'consumerKey', 'consumerSecret', 'accessKey', 'accessSecret' ) as $aweber_key ) {
				$settings['integration']['aweber'][$aweber_key] = '';
			}
		}



		//Get the name
		$form_name = trim( strip_tags( $this->post['name'] ) );
		if ( $form_name == '' ) {
			$form_name = __( 'Untitled', 'ipt_fsqm' );
		}
		//Get the type

		$form_type = (int) $this->post['type'];

		$form_category = (int) $this->post['category'];

		//Process the layout and recursively process all the inner elements as well ;-)
		if ( isset( $this->post['containers'] ) ) {
			foreach ( (array) $this->post['containers'] as $container_key ) {
				//Get default structure
				$layout_new = $this->get_element_structure( 'tab' );

				//Merge with the date sent by user
				$layout_new = $this->merge_elements( $this->post['layout'][$container_key], $layout_new );

				//Reset the elements
				$layout_new['elements'] = array();

				//If no elements, then no need to continue
				if ( !isset( $this->post['layout'][$container_key]['elements']['m_type'] ) ) {
					continue;
				}

				//For all elements, check it and then add it
				foreach ( (array) $this->post['layout'][$container_key]['elements']['m_type'] as $e_key => $m_type ) {
					if ( !isset( $this->save_process[$m_type] ) ) {
						continue;
					}
					$type = $this->post['layout'][$container_key]['elements']['type'][$e_key];
					$key = $this->post['layout'][$container_key]['elements']['key'][$e_key];

					$element = $this->process_element( $m_type, $type, $key );

					if ( false !== $element ) {
						$layout_new['elements'][] = array(
							'm_type' => $m_type,
							'type' => $type,
							'key' => $key,
						);
					}
				}

				if ( !empty( $layout_new['elements'] ) ) {
					$layout[] = $layout_new;
				}
			}
		}

		$return_id = isset( $this->post['form_id'] ) ? $this->post['form_id'] : null;

		if ( $return_id !== null ) {
			$wpdb->update( $ipt_fsqm_info['form_table'], array(
					'name' => $form_name,
					'settings' => maybe_serialize( $settings ),
					'layout' => maybe_serialize( $layout ),
					'design' => maybe_serialize( $this->save_process['design'] ),
					'mcq' => maybe_serialize( $this->save_process['mcq'] ),
					'freetype' => maybe_serialize( $this->save_process['freetype'] ),
					'pinfo' => maybe_serialize( $this->save_process['pinfo'] ),
					'type' => $form_type,
					'category' => $form_category,
				), array( 'id' => $return_id ), '%s', '%d' );
			do_action( 'ipt_fsqm_form_updated', $return_id, $this );
		} else {
			$wpdb->insert( $ipt_fsqm_info['form_table'], array(
					'name' => $form_name,
					'settings' => maybe_serialize( $settings ),
					'layout' => maybe_serialize( $layout ),
					'design' => maybe_serialize( $this->save_process['design'] ),
					'mcq' => maybe_serialize( $this->save_process['mcq'] ),
					'freetype' => maybe_serialize( $this->save_process['freetype'] ),
					'pinfo' => maybe_serialize( $this->save_process['pinfo'] ),
					'type' => $form_type,
					'category' => $form_category,
				) );
			$return_id = $wpdb->insert_id;
			do_action( 'ipt_fsqm_form_created', $return_id, $this );
		}

		// Call for any theme related functions
		$active_theme = $settings['theme']['template'];
		$active_theme_info = $this->get_theme_by_id( $active_theme );
		if ( isset( $active_theme_info['admin_save_cb'] ) && ! is_null( $active_theme_info['admin_save_cb'] ) && is_callable( $active_theme_info['admin_save_cb'] ) ) {
			call_user_func( $active_theme_info['admin_save_cb'], $return_id, $form_name, $settings, $layout, $this->save_process, $form_type, $form_category );
		}

		return $return_id;
	}

	/*==========================================================================
	 * Save Processors
	 *========================================================================*/
	protected function process_element( $m_type, $type, $key ) {
		$element_definition = $this->get_element_definition( array( 'm_type' => $m_type, 'type' => $type ) );
		$element_structure = $this->get_element_structure( $type );

		if ( false === $element_structure ) {
			return false;
		}

		//Infinite recursion check - Who knows what the devil may do
		if ( isset( $this->save_process[$m_type][$key] ) ) {
			return false;
		}

		$element_from_post = isset( $this->post[$m_type][$key] ) ? $this->post[$m_type][$key] : array();

		$element = $this->merge_elements( $element_from_post, $element_structure );

		if ( isset( $element_definition['droppable'] ) && $element_definition['droppable'] == true ) {
			$element['elements'] = array();

			if ( isset( $this->post[$m_type][$key]['elements']['m_type'] ) ) {
				foreach ( (array) $this->post[$m_type][$key]['elements']['m_type'] as $e_key => $child_m_type ) {
					$child_type = $this->post[$m_type][$key]['elements']['type'][$e_key];
					$child_key = $this->post[$m_type][$key]['elements']['key'][$e_key];

					$child_element = $this->process_element( $child_m_type, $child_type, $child_key );

					if ( false !== $child_element ) {
						$element['elements'][] = array(
							'm_type' => $child_m_type,
							'type' => $child_type,
							'key' => $child_key,
						);
					}
				}
			}
		}

		$this->save_process[$m_type][$key] = $element;
		return true;
	}

	/*==========================================================================
	 * BUILDER LAYOUT CALLBACKS
	 *========================================================================*/
	public function builder_contextbar() {
		?>
<div id="ipt-eform-builder-contextbar">
	<div id="ipt-eform-builder-form-actions" class="ipt-eform-builder-contextgroup">
		<a data-tippy-placement="bottom-start" class="ipt-eform-builder-contextbar__link eform-builder-tippy" href="#" id="ipt-eform-builder-toolbar-save" title="<?php esc_attr_e( 'Save Form', 'ipt_fsqm' ); ?>">
			<span class="idle">
				<i class="ipt-icomoon-save2"></i>
			</span>
			<span class="saving">
				<span class="eform-saving-block">
					<span class="eform-saving-block__circle"></span>
					<span class="eform-saving-block__circle"></span>
				</span>
			</span>
		</a>

		<div id="ipt-eform-builder-form-help">
			<span class="eform-builder-main-help">
				<a data-tippy-placement="top-start" class="ipt-eform-builder-contextbar__link ipt-eform-builder-context-help eform-builder-tippy" data-target="eform-builder-help-quickstart" rel="noopener noreferrer" href="https://www.youtube.com/watch?v=UyP7jakeBbE" title="<?php _e( 'Get help', 'ipt_fsqm' ); ?>">
					<i class="ipt-icomoon-live_help"></i>
					<span style="display: none" class="screen-reader-text"><?php _e( 'Quick Start Video', 'ipt_fsqm' ); ?></span>
				</a>
				<ul class="eform-builder-main-help-nested">
					<li>
						<a target="_blank" rel="noopener noreferrer" class="ipt-eform-builder-context-help" data-target="eform-builder-help-quickstart" href="https://www.youtube.com/watch?v=UyP7jakeBbE">
							<i class="ipt-icomoon-rocket"></i>
							<span><?php _e( 'Quick Start Video', 'ipt_fsqm' ); ?></span>
						</a>
					</li>
					<li>
						<a class="ipt-eform-builder-context-help" data-target="eform-builder-help-addtosite" href="#" id="ipt-eform-builder-toolbar-addtosite">
							<i class="ipt-icomoon-add_circle"></i>
							<span><?php _e( 'Add to site', 'ipt_fsqm' ) ?></span>
						</a>
					</li>
					<li>
						<a target="_blank" rel="noopener noreferrer" class="ipt-eform-builder-context-help" data-target="eform-builder-help-shortcut-keys" href="#eform-builder-help-shortcut-keys">
							<i class="ipt-icomoon-keyboard2"></i>
							<span><?php _e( 'Shortcut keys', 'ipt_fsqm' ); ?></span>
						</a>
					</li>
					<li>
						<a href="#" id="eform-onboarding-trigger">
							<i class="ipt-icomoon-directions"></i>
							<span><?php _e( 'Take a tour', 'ipt_fsqm' ); ?></span>
						</a>
					</li>
					<li>
						<a target="_blank" rel="noopener noreferrer" href="https://wpquark.com/kb/fsqm/fsqm-video-tutorials/">
							<i class="ipt-icomoon-video_library"></i>
							<span><?php _e( 'Video Tutorials', 'ipt_fsqm' ); ?></span>
						</a>
					</li>
					<li>
						<a target="_blank" rel="noopener noreferrer" href="https://wpquark.com/kb/fsqm/">
							<i class="ipt-icomoon-library_books"></i>
							<span><?php _e( 'Documentation', 'ipt_fsqm' ); ?></span>
						</a>
					</li>
					<li>
						<a target="_blank" rel="noopener noreferrer" href="https://wpquark.com/kb/support/forum/wordpress-plugins/wp-feedback-survey-quiz-manager-pro/">
							<i class="ipt-icomoon-support2"></i>
							<span><?php _e( 'Get Support', 'ipt_fsqm' ); ?></span>
						</a>
					</li>
				</ul>
			</span>
		</div>

		<?php $this->ui->builder_add_container_button( 'ipt-eform-builder-contextbar__link eform-builder-tippy' ); ?>
	</div>
</div>
		<?php
	}

	public function builder_context_helps() {
		?>
<div class="eform-context-help-element" id="eform-builder-help-quickstart">
	<div class="eform-context-help-element__inner embed-video">
		<div class="embed-container">
			<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/SylAM0uPxMw" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
		</div>
	</div>
</div>
<div class="eform-context-help-element" id="eform-builder-help-shortcut-keys">
	<div class="eform-context-help-element__inner">
		<p><?php _e( 'You can use the following keyboard shortcuts within form builder.', 'ipt_fsqm' ); ?></p>
		<ul class="ul-disc">
			<li><kbd>Ctrl/⌘ + s</kbd>: Save form.</li>
			<li><kbd>Ctrl/⌘ + h</kbd>: Toggle HotKey for the followings.</li>
			<li><kbd>Alt/⌥ + p</kbd>: Add new page.</li>
			<li><kbd>Alt/⌥ + c</kbd>: Add Checkbox element.</li>
			<li><kbd>Alt/⌥ + r</kbd>: Add Radio element.</li>
			<li><kbd>Alt/⌥ + d</kbd>: Add Dropdown element.</li>
			<li><kbd>Alt/⌥ + t</kbd>: Add Textarea element.</li>
			<li><kbd>Alt/⌥ + i</kbd>: Add Input element.</li>
			<li><kbd>Alt/⌥ + f</kbd>: Add First Name element.</li>
			<li><kbd>Alt/⌥ + l</kbd>: Add Last Name element.</li>
			<li><kbd>Alt/⌥ + e</kbd>: Add Email element.</li>
			<li><kbd>Alt/⌥ + g</kbd>: Add Grading(Slider/Range) element.</li>
			<li><kbd>Alt/⌥ + s</kbd>: Add Star Rating element.</li>
			<li><kbd>Alt/⌥ + o</kbd>: Add Sortable element.</li>
			<li><kbd>Alt/⌥ + m</kbd>: Add Matrix element.</li>
			<li><kbd>Alt/⌥ + a</kbd>: Add Repeatable element.</li>
		</ul>
	</div>
</div>
<div class="eform-context-help-element" id="eform-builder-help-addtosite">
	<div class="eform-context-help-element__inner">
		<h3>
			<i class="ipt-icomoon-code3"></i>
			<span><?php _e( 'Using Shortcodes', 'ipt_fsqm' ); ?></span>
		</h3>
		<p><img class="align-right" src="<?php echo IPT_FSQM_Loader::$static_location . 'admin/images/help/tinymce-editor.jpg' ?>" /></p>
		<p><?php _e( 'To embed the form in a post or page use the following shortcode.', 'ipt_fsqm' ); ?></p>
		<pre>[ipt_fsqm_form id="<?php echo $this->form_id; ?>"]</pre>
		<p><?php _e( 'To use popup form, use the shortcode', 'ipt_fsqm' ); ?></p>
		<pre>[ipt_fsqm_popup form_id="<?php echo $this->form_id; ?>"]<?php _e( 'Open Form', 'ipt_fsqm' ); ?>[/ipt_fsqm_popup]</pre>
		<p><?php _e( 'Or simply use <i class="ipt-fsqmic-eform"></i> icon under WordPress editor to customize different shortcode options.', 'ipt_fsqm' ); ?></p>
		<p><?php _e( 'Each of the editor button will take you through a widget with which you can easily insert shortcodes without modifying by hand.', 'ipt_fsqm' ); ?></p>
		<h3>
			<i class="ipt-icomoon-edit"></i>
			<span><?php _e( 'Using Gutenberg Blocks', 'ipt_fsqm' ); ?></span>
		</h3>
		<p><img class="" src="<?php echo IPT_FSQM_Loader::$static_location . 'admin/images/help/gutenberg-editor.jpg' ?>" /></p>
		<p><?php _e( 'If you are using Gutenberg blocks, then it can be found under eForm block category.', 'ipt_fsqm' ); ?></p>
		<p><?php _e( 'The blocks can be customized right within the editor. You can even publish and customize later during update.', 'ipt_fsqm' ); ?></p>
		<h3>
			<i class="ipt-icomoon-widgets"></i>
			<span><?php _e( 'Using Widgets', 'ipt_fsqm' ); ?></span>
		</h3>
		<p><img src="<?php echo IPT_FSQM_Loader::$static_location . 'admin/images/help/widgets.jpg' ?>" /></p>
		<p><?php _e( 'Go to Appearance > Widgets and search for <code>eForm - Insert Form</code>.', 'ipt_fsqm' ); ?></p>
		<h3>
			<i class="ipt-icomoon-code"></i>
			<span><?php _e( 'Advanced Usage', 'ipt_fsqm' ); ?></span>
		</h3>
		<p><?php _e( 'Use the following PHP code to insert anywhere.', 'ipt_fsqm' ); ?></p>
		<pre>
&lt;?php
$form = new \IPT_FSQM_Form_Elements_Front( null, <?php echo $this->form_id; ?> );
$form->show_form();</pre>
	</div>
</div>
		<?php
	}

	public function builder_toolbar() {
		$eform_standalone_permalink = IPT_FSQM_Form_Elements_Static::standalone_permalink_parts( $this->form_id );
		$eform_standalone_url = '';
		if ( is_array( $eform_standalone_permalink ) ) {
			$eform_standalone_url = $eform_standalone_permalink['url'];
		}
		?>
<div id="ipt-eform-builder-toolbar">
	<!-- Loader -->
	<div class="loader-viewer">
		<div class="loader-viewer__load">
			<div class="loader-viewer__bar"></div>
			<div class="loader-viewer__bar"></div>
			<div class="loader-viewer__bar"></div>
		</div>
	</div>
	<span class="toolbar-left toolbar-group">
		<!-- Save & Settings -->
		<span id="ipt-eform-builder-toolbar-save-settings">
			<a data-tippy-placement="bottom-start" href="#" id="ipt-eform-builder-toolbar-settings" class="ipt-eform-customizer-trigger eform-builder-tippy" title="<?php _e( 'Change form settings and integrations.', 'ipt_fsqm' ); ?>" data-target="ipt_fsqm_form_customization_wrap">
				<i class="ipt-icomoon-wrench"></i> <?php _e( 'CONFIG', 'ipt_fsqm' ); ?>
			</a>
			<a href="#" id="ipt-eform-builder-toolbar-theme" class="ipt-eform-customizer-trigger eform-builder-tippy" title="<?php _e( 'Change form themes, colors, progress buttons etc.', 'ipt_fsqm' ); ?>" data-target="ipt_fsqm_form_theme_settings_wrap">
				<i class="ipt-icomoon-paint-format"></i> <?php _e( 'STYLE', 'ipt_fsqm' ); ?>
			</a>
		</span>

		<span id="ipt-eform-builder-toolbar-form-id">
			<span class="eform-badge"><?php printf( __( 'id: %d', 'ipt_fsqm' ), $this->form_id ); ?></span>
		</span>

		<!-- Form name -->
		<span id="ipt-eform-builder-toolbar-formname">
			<?php $this->form_name(); ?>
		</span>
	</span>

	<span class="toolbar-center toolbar-group">
		<!-- Split pane mode -->
		<span id="ipt-eform-builder-toolbar-split-pane-mode">
			<a data-class="builder-only" href="#" id="ipt-eform-builder-toolbar-builder-only" class="eform-builder-tippy" title="<?php esc_attr_e( 'Show Builder Only', 'ipt_fsqm' ); ?>">
				<svg width="14px" height="12px" viewBox="0 0 14 12" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
					<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
						<g fill="currentColor" fill-rule="nonzero">
							<path d="M12.544,0.116 C12.881,0.156 13.14,0.415 13.18,0.753 C13.318,4.246 13.182,7.744 13.182,11.24 C13.168,11.599 12.903,11.888 12.544,11.931 C8.616,12.085 4.683,11.933 0.752,11.933 C0.394,11.919 0.104,11.654 0.062,11.294 C-0.076,7.801 0.059,4.303 0.059,0.807 C0.074,0.449 0.339,0.159 0.698,0.116 C4.644,-0.039 8.598,-0.039 12.544,0.116 Z M0.752,0.807 L0.752,11.24 L12.489,11.24 L12.489,0.807 C8.577,0.807 4.665,0.807 0.752,0.807 Z" id="Shape"></path>
							<g id="Group" transform="translate(2.000000, 2.000000)">
								<rect id="Rectangle-path" x="0.143" y="0.451" width="9.042" height="1"></rect>
								<rect id="Rectangle-path" x="0.143" y="5.288" width="9.042" height="1"></rect>
								<rect id="Rectangle-path" x="0.143" y="3.676" width="9.042" height="1"></rect>
								<rect id="Rectangle-path" x="0.143" y="2.064" width="9.042" height="1"></rect>
								<rect id="Rectangle-path" x="0.143" y="6.9" width="9.042" height="1"></rect>
							</g>
						</g>
					</g>
				</svg>
			</a>
			<a data-class="builder-form" href="#" id="ipt-eform-builder-toolbar-builder-form" class="active eform-builder-tippy" title="<?php esc_attr_e( 'Show Builder and Form', 'ipt_fsqm' ); ?>">
				<svg width="14px" height="12px" viewBox="0 0 14 12" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
					<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
						<g fill="currentColor" fill-rule="nonzero">
							<g>
								<g transform="translate(1.000000, 2.000000)">
									<g id="Rectangle-path">
										<rect x="0.89" y="0.634" width="3.26" height="1"></rect>
										<rect x="0.89" y="5.168" width="3.26" height="1"></rect>
										<rect x="0.89" y="3.657" width="3.26" height="1"></rect>
										<rect x="0.89" y="2.145" width="3.26" height="1"></rect>
										<rect x="0.89" y="6.679" width="3.26" height="1"></rect>
									</g>
								</g>
								<path d="M12.541,0.116 C12.877,0.156 13.135,0.414 13.175,0.75 C13.311,4.218 13.177,7.691 13.177,11.161 C13.163,11.518 12.899,11.807 12.541,11.849 C8.613,12.003 4.68,11.851 0.749,11.851 C0.392,11.837 0.103,11.573 0.061,11.215 C-0.075,7.747 0.059,4.275 0.059,0.805 C0.073,0.447 0.337,0.159 0.695,0.116 C4.641,-0.039 8.595,-0.039 12.541,0.116 Z M0.749,11.161 L6.292,11.161 L6.292,0.805 L0.749,0.805 L0.749,11.161 Z M12.486,11.161 L12.486,0.805 L6.944,0.805 L6.944,11.161 L12.486,11.161 Z" id="Shape"></path>
							</g>
						</g>
					</g>
				</svg>
			</a>
			<a data-class="form-only" href="#" id="ipt-eform-builder-toolbar-form-only" class="eform-builder-tippy" title="<?php esc_attr_e( 'Show Form Only', 'ipt_fsqm' ); ?>">
				<svg width="14px" height="12px" viewBox="0 0 14 12" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
					<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
						<g id="3" fill="currentColor" fill-rule="nonzero">
							<path d="M12.544,0.116 C12.881,0.156 13.14,0.415 13.18,0.753 C13.318,4.246 13.182,7.744 13.182,11.24 C13.168,11.599 12.903,11.888 12.544,11.931 C8.616,12.085 4.683,11.933 0.752,11.933 C0.394,11.919 0.104,11.654 0.062,11.294 C-0.076,7.801 0.059,4.303 0.059,0.807 C0.074,0.449 0.339,0.159 0.698,0.116 C4.644,-0.039 8.598,-0.039 12.544,0.116 Z M0.752,0.807 L0.752,11.24 L12.489,11.24 L12.489,0.807 C8.577,0.807 4.665,0.807 0.752,0.807 Z" id="Shape"></path>
						</g>
					</g>
				</svg>
			</a>
		</span>
	</span>

	<span class="toolbar-right toolbar-group">
		<span id="ipt-eform-builder-toolbar-iframe-size">
			<a title="<?php _e( 'Fit Screen', 'ipt_fsqm' ); ?>" data-stretch="fit" class="active eform-builder-tippy">
				<svg width="24px" height="24px" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
					<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
						<g id="baseline-wallpaper-24px" transform="translate(-2.000000, -2.000000)">
							<path d="M4,4 L11,4 L11,2 L4,2 C2.9,2 2,2.9 2,4 L2,11 L4,11 L4,4 Z M20,2 L13,2 L13,4 L20,4 L20,11 L22,11 L22,4 C22,2.9 21.1,2 20,2 Z M20,20 L13,20 L13,22 L20,22 C21.1,22 22,21.1 22,20 L22,13 L20,13 L20,20 Z M4,13 L2,13 L2,20 C2,21.1 2.9,22 4,22 L11,22 L11,20 L4,20 L4,13 Z" id="Shape" fill="currentColor" fill-rule="nonzero"></path>
							<polygon id="Shape" points="0 0 24 0 24 24 0 24"></polygon>
						</g>
					</g>
				</svg>
			</a>
			<a title="<?php _e( 'Desktop View', 'ipt_fsqm' ); ?>" class="eform-builder-tippy" data-stretch="desktop">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
					<path d="M0 0h24v24H0z" fill="none"/>
					<path fill="currentColor" d="M20 18c1.1 0 1.99-.9 1.99-2L22 5c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v11c0 1.1.9 2 2 2H0c0 1.1.9 2 2 2h20c1.1 0 2-.9 2-2h-4zM4 5h16v11H4V5zm8 14c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1z"/>
				</svg>
			</a>
			<a title="<?php _e( 'Tablet View', 'ipt_fsqm' ); ?>" class="eform-builder-tippy" data-stretch="tablet">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
					<path fill="currentColor" d="M18.5 0h-14C3.12 0 2 1.12 2 2.5v19C2 22.88 3.12 24 4.5 24h14c1.38 0 2.5-1.12 2.5-2.5v-19C21 1.12 19.88 0 18.5 0zm-7 23c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm7.5-4H4V3h15v16z"/>
				</svg>
			</a>
			<a title="<?php _e( 'Mobile View', 'ipt_fsqm' ); ?>" class="eform-builder-tippy" data-stretch="mobile">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
					<path fill="currentColor" d="M15.5 1h-8C6.12 1 5 2.12 5 3.5v17C5 21.88 6.12 23 7.5 23h8c1.38 0 2.5-1.12 2.5-2.5v-17C18 2.12 16.88 1 15.5 1zm-4 21c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm4.5-4H7V4h9v14z"/>
					<path d="M0 0h24v24H0z" fill="none"/>
				</svg>
			</a>
		</span>
		<span id="ipt-eform-builder-toolbar-actions">
			<a class="refresher eform-builder-tippy" title="<?php _e( 'Refresh Live View', 'ipt_fsqm' ); ?>"><i class="ipt-icomoon-refresh"></i></a>
			<a class="eform-builder-tippy" href="<?php echo $eform_standalone_url; ?>" target="<?php echo "eform-preview-" . $this->form_id; ?>" title="<?php _e( 'Open form in new tab', 'ipt_fsqm' ); ?>"><i class="ipt-icomoon-external-link"></i></a>
		</span>
	</span>
</div>
		<?php
	}

	public function builder() {
		$msgs = array(
			'layout_helper_msg' => __( 'You can customize this layout by simply dragging a form element and dropping over the area. You can also have nested elements inside any droppable element. You can and should further set the title and subtitle of this container so that the tabular layout can be properly populated.' ),
			'layout_helper_title' => __( 'Customizable Layout', 'ipt_fsqm' ),
			'deleter_title' => __( 'Confirm Deletion', 'ipt_fsqm' ),
			'deleter_msg' => __( 'Are you sure you want to remove this container? This action can not be undone.', 'ipt_fsqm' ),
			'deldropper_title' => __( 'Confirm Removal', 'ipt_fsqm' ),
			'deldropper_msg' => __( 'Are you sure you want to remove this element? This action can not be undone.', 'ipt_fsqm' ),
		);
		$keys = array(
			'layout' => 0,
			'design' => 0,
			'mcq' => 0,
			'freetype' => 0,
			'pinfo' => 0,
		);
		foreach ( $keys as $type => $key ) {
			if ( !empty( $this->{$type} ) ) {
				$keys[$type] = max( array_keys( $this->{$type} ) ) + 1;
			}
		}
		foreach ( $this->layout as $l_key => $layout ) {
			$this->layout[$l_key]['grayed_out'] = false;
			if ( isset($layout['conditional']) && $layout['conditional']['active'] == true && $layout['conditional']['status'] == false ) {
				$this->layout[$l_key]['grayed_out'] = true;
			}
		}
?>
<style type="text/css">
	#screen-meta-links {
		display: none;
	}
</style>

	<!-- Toolbar -->
	<?php $this->builder_toolbar(); ?>
	<!-- End toolbar -->

	<!-- Add Layout template -->
	<?php $this->ui->builder_adder( 'ipt_fsqm_add_layout', '__LKEY__', array( $this, 'builder_layout_settings' ), array( '__LKEY__', array() ), array( 'm_type' => 'layout', 'type' => 'tab' ), 'layout' ); ?>
	<!-- End Add Layout Template -->

	<!-- End Droppables & Containers & Toolbar -->

	<!-- Element Settings -->
	<div id="ipt-eform-builder-settings-wrap" data-margin-top="32" style="display: none">
		<div class="ipt_uif_tabs" id="ipt-eform-settings-tab-wrapper">
			<ul>
				<li><a href="#ipt-eform-settings-element"><i class="ipt-icomoon-tasks"></i> <?php _e( 'Configuration', 'ipt_fsqm' ); ?></a></li>
				<li id="ipt-eform-settings-editor-li"><a href="#ipt-eform-settings-editor"><i class="ipt-icomoon-pen"></i> <?php _e( 'Description', 'ipt_fsqm' ); ?></a></li>
			</ul>
			<div id="ipt-eform-settings-element">
				<!-- Settings Box -->
				<?php $this->ui->builder_settings_box( 'ipt_fsqm_settings', __( 'Save Settings', 'ipt_fsqm' ) ); ?>
				<!-- End Settings Box -->
			</div>
			<div id="ipt-eform-settings-editor">
				<!-- WP Editor -->
				<?php $this->ui->builder_wp_editor( 'ipt_fsqm_form_richtext', __( 'Element Description (HTML)', 'ipt_fsqm' ) ); ?>
				<!-- End WP Editor -->
			</div>
			<div class="ipt_uif_builder_settings_toolbar">
				<a href="#" class="builder_settings_drag eform-builder-tippy" title="<?php _e( 'Move Window and double click to reset', 'ipt_fsqm' ); ?>">
					<i class="ipt-icomoon-drag_handle"></i>
				</a>
				<a href="#" class="builder_settings_prev eform-builder-tippy" title="<?php _e( 'Previous element', 'ipt_fsqm' ); ?>">
					<i class="ipt-icomoon-angle-left"></i>
				</a>
				<a href="#" class="builder_settings_next eform-builder-tippy" title="<?php _e( 'Next element', 'ipt_fsqm' ); ?>">
					<i class="ipt-icomoon-angle-right"></i>
				</a>
				<a href="#" class="builder_settings_save eform-builder-tippy" title="<?php _e( 'Save and Close', 'ipt_fsqm' ); ?>">
					<i class="ipt-icomoon-check2"></i>
				</a>
			</div>
			<?php $this->ui->button( __( 'Save Settings', 'ipt_fsqm' ), 'ipt-eform-builder-settings-save', 'large', 'ui', 'normal', array( 'ipt_uif_builder_settings_save', 'center' ) ); ?>
		</div>
		<div class="clear"></div>
	</div>
	<!-- End Element Settings -->

	<div id="ipt-eform-builder-app">
		<div id="ipt-eform-builder-app__sidebar">
			<!-- Contextbar -->
			<?php $this->builder_contextbar(); ?>
			<!-- End contextbar -->
			<!-- Element Adder -->
			<div id="ipt-eform-builder-droppables-container" class="ipt-eform-builder-split-window__sidebar">
				<!-- Droppable Search -->
				<?php $this->ui->builder_droppables_search(); ?>
				<!-- Droppable Elements -->
				<?php $this->builder_droppables(); ?>
				<!-- End Droppable Elements -->
			</div>
			<!-- End Element Adder -->
			<!-- Collapse sidebar -->
			<a id="ipt-eform-builder-sidebar-collapse" href="#">
				<i class="ipt-icomoon-arrow-circle-left"></i>
				<span class="sidebar-collapse-label"><?php _e( 'Collapse', 'ipt_fsqm' ) ?></span>
			</a>
			<!-- End collapse sidebar -->
		</div>
		<div id="ipt-eform-builder-app__splitpane">
			<div id="ipt-eform-builder-split-window" class="ipt-eform-builder-split-window builder-form">
				<!-- Left Column -->
				<div id="eform-split-view-left" class="ipt-eform-builder-split-window__builder">
					<div class="ipt-eform-builder-split-window__inner">
						<div id="ipt-eform-builder-layout-wrap">
							<!-- Layout area -->
							<?php $this->ui->builder_sortables( 'ipt_fsqm_form_builder_layout', $this->type, $this->layout, array( $this, 'builder_sortable' ), array( $this, 'builder_layout_settings' ), $msgs, 'layout', $keys ); ?>
							<!-- End Layout Area -->
							<div class="clear"></div>
						</div>
					</div>
				</div>
				<!-- End Left Column -->

				<div class="ipt-eform-builder-split-window__resizer" title="<?php esc_attr_e( 'Double click to reset', 'ipt_fsqm' ); ?>" id="eform-split-view-resize">
					<i class="ipt-icomoon-more"></i>
				</div>

				<!-- Right Column -->
				<div id="eform-split-view-right" class="ipt-eform-builder-split-window__form">
					<div class="ipt-eform-builder-split-window__inner">
						<div class="eform-builder-live-form">
							<div class="eform-builder-form-iframe fit">
								<iframe id="eform-builder-liveview-iframe" src="<?php echo esc_url( add_query_arg( [
									'action' => 'eform_builder_preview',
									'form_id' => $this->form_id,
									'_wpnonce' => wp_create_nonce( 'eform_builder_preview' ),
								], admin_url( 'admin-ajax.php' ) ) ); ?>" allowtransparency="true" width="100%" height="100%"></iframe>
							</div>
							<div class="eform-builder-live-form-loading" id="eform-builder-live-iframe-loading">
								<?php $this->ui->builder_request_animation_block(); ?>
							</div>
						</div>
					</div>
				</div>
				<!-- End Right Column -->
			</div>
		</div>
	</div>
	<div class="clear"></div>
		<?php
		$this->builder_context_helps();
	}

	public function builder_sortable( $layout_element, $layout_key ) {
		$e_key = $layout_element['key'];
		$element = $this->get_element_from_layout( $layout_element );
		$callback = array( $this, 'build_element_html' );
		$parameters = array( $element['type'], $e_key, $element, null, '' );
		$element_definition = $this->get_element_definition( $element );
		$data_attr = $this->ui->builder_data_attr( $element_definition );
		$element_definition['sub_title'] = strip_tags( $element['title'] );
		$element_grayed_out = false;
		if ( isset($element['conditional']) && $element['conditional']['active'] == true && $element['conditional']['status'] == false ) {
			$element_grayed_out = true;
		}

		$element_definition['grayed_out'] = $element_grayed_out;
		return array( $element_definition, $e_key, $layout_key, $element['type'], $callback, $parameters, $data_attr, $element, array( $this, 'builder_sortable' ) );
	}

	public function builder_layout_settings( $layout_key, $layout = array() ) {
		$structure = wp_parse_args( $layout, $this->get_element_structure( 'tab' ) );
		$tab_names = $this->ui->generate_id_from_name( 'layout[' . $layout_key . '][settings_wrap]' );
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Layout', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( 'layout[' . $layout_key . '][title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( 'layout[' . $layout_key . '][title]', $structure['title'], __( 'Title of the Container', 'ipt_fsqm' ), 'large' ); ?></td>
						<td>
							<?php $this->ui->help( __( 'You can put <code>%%NUM%%</code> which would be replaced by the tab number dynamically', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( 'layout[' . $layout_key . '][subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( 'layout[' . $layout_key . '][subtitle]', $structure['subtitle'], __( 'Secondary title of the Container', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<td colspan="3"><p class="description"><?php _e( 'You can also have any rich text which will be shown on the top of the container.', 'ipt_fsqm' ); ?></p></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label('layout[' . $layout_key . '][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->icon_selector( 'layout[' . $layout_key . '][icon]', $structure['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the icon you want to appear before the heading. Select none to disable.', 'ipt_fsqm' ) ) ?></td>
					</tr>
					<tr class="ipt_fsqm_page_specific_time">
						<th><?php $this->ui->generate_label( 'layout[' . $layout_key . '][timer]', __( 'Container Time Limit (Seconds)', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( 'layout[' . $layout_key . '][timer]', $structure['timer'], __( 'Seconds', 'ipt_fsqm' ), '0' ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'Set the time in seconds after which this container will automatically progress.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( 'layout[' . $layout_key . ']', $structure['conditional'] ); ?>
		</div>
	</div>

		<?php

		$this->ui->textarea_linked_wp_editor( 'layout[' . $layout_key . '][description]', $structure['description'], 'Enter' );
		return;

	}


	public function builder_droppables() {
		$id = 'ipt_fsqm_builder_droppable';
		$key = '__EKEY__';
		$layout_key = '__LKEY__';
		$items = $this->elements;
		unset( $items['layout'] );
		foreach ( $items as $i_key => $item ) {
			foreach ( $item['elements'] as $elem_key => $element ) {
				$items[ $i_key ]['elements'][ $elem_key ]['callback'] = array( $this, 'build_element_html' );
				$items[ $i_key ]['elements'][ $elem_key ]['parameters'] = array( $elem_key, $key, null, null, '' );
				$items[ $i_key ]['elements'][ $elem_key ]['sub_title'] = '';
			}
		}
		$this->ui->builder_droppables( $id, $items, $key, $layout_key, __( 'Go Back', 'ipt_fsqm' ) );
	}

	private function icon_tester() {
		$icons = $this->ui->get_valid_icons();
		$i_check = array();
		?>
<table class="widefat">
	<thead>
		<tr>
			<th>Name</th>
			<th>Data</th>
			<th>Icon</th>
			<th>Image</th>
			<th>Duplicate</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $icons as $i_gr ) : ?>
		<?php foreach ( $i_gr['elements'] as $i_key => $ic ) : ?>
			<?php $duplicate = isset( $i_check[$i_key] ) ? true : false; ?>
			<?php $i_check[$i_key] = true; ?>
		<tr>
			<th><?php echo $ic; ?></th>
			<td><?php echo $i_key . ' / ' . dechex( $i_key ); ?></td>
			<td><span data-ipt-icomoon="&#x<?php echo dechex( $i_key ); ?>;" style="font-size: 32px; margin: 5px 0; display: inline-block; color: #333;"></span></td>
			<td><?php echo '<img src="' . plugins_url( '/lib/images/iconmoon/' . $this->ui->get_icon_image_name( $i_key ), IPT_FSQM_Loader::$abs_file ) . '" />'; ?></td>
			<td><?php echo ($duplicate ? 'Yes' : 'No'); ?></td>
		</tr>
		<?php endforeach; ?>
		<?php endforeach; ?>
	</tbody>
</table>
		<?php
	}

	/*==========================================================================
	 * TABBED AND OTHER FORM SETTINGS
	 *========================================================================*/
	public function form_name() {
		$this->ui->text( 'name', $this->name, __( 'Enter the Name of the Form', 'ipt_fsqm' ), 'large' );
	}

	public function form_type() {
		$items = array();
		$items[] = array(
			'value' => '0',
			'label' => __( 'Normal Single Paged', 'ipt_fsqm' ),
			'data' => array(
				'condID' => 'ipt_fsqm_type_zero,ipt_fsqm_type_scroll',
			),
		);
		$items[] = array(
			'value' => '1',
			'label' => __( 'Tabular Appearance', 'ipt_fsqm' ),
			'data' => array(
				'condID' => 'ipt_fsqm_type_one,ipt_fsqm_type_scroll',
			),
		);
		$items[] = array(
			'value' => '2',
			'label' => __( 'Paginated Appearance', 'ipt_fsqm' ),
			'data' => array(
				'condID' => 'ipt_fsqm_type_one,ipt_fsqm_type_two,ipt_fsqm_type_scroll',
			),
		);
?>
<div class="ipt_uif_msg ipt_uif_float_right">
	<a href="javascript:;" class="ipt_uif_msg_icon" title="<?php _e( 'Form Appearance Type', 'ipt_fsqm' ); ?>"><i class="ipt-icomoon-live_help"></i></a>
	<div class="ipt_uif_msg_body">
		<p><?php _e( 'Currently we support 3 kinds of appearances. Each appearance has it\'s own different sets of options.', 'ipt_fsqm' ); ?></p>
		<h3><?php _e( 'Normal Single Paged', 'ipt_fsqm' ); ?></h3>
		<ul class="ul-square">
			<li>
				<?php _e( 'Form will appear with a general single paged layout.', 'ipt_fsqm' ) ?>
			</li>
			<li>
				<?php _e( 'Ideal for small bussiness or contact forms.', 'ipt_fsqm' ); ?>
			</li>
		</ul>
		<h3><?php _e( 'Tabular Appearance', 'ipt_fsqm' ); ?></h3>
		<ul class="ul-square">
			<li>
				<?php _e( 'Form elements can be grouped into tabs.', 'ipt_fsqm' ); ?>
			</li>
			<li>
				<?php _e( 'A user will need to navigate through all the tabs and fill them up before submitting.', 'ipt_fsqm' ); ?>
			</li>
			<li>
				<?php _e( 'You can create as many tabs as you want. Simply click on the <strong>Add Tab/Pagination button</strong>.', 'ipt_fsqm' ); ?>
			</li>
			<li>
				<?php _e( 'You can also select if the user is able to navigate to a previously viewed tab without validating or completely filling the form elements inside the current tab.', 'ipt_fsqm' ); ?>
			</li>
		</ul>
		<h3><?php _e( 'Paginated Appearance', 'ipt_fsqm' ); ?></h3>
		<ul class="ul-square">
			<li>
				<?php _e( 'Form elements can be grouped into pages.', 'ipt_fsqm' ); ?>
			</li>
			<li>
				<?php _e( 'A user will need to navigate through all the pages and fill them up before submitting.', 'ipt_fsqm' ); ?>
			</li>
			<li>
				<?php _e( 'You can create as many pages as you want. Simply click on the <strong>Add Tab/Pagination button</strong>.', 'ipt_fsqm' ); ?>
			</li>
			<li>
				<?php _e( 'You can select to show a progress bar which will show the percentage of completion.', 'ipt_fsqm' ); ?>
			</li>
		</ul>
	</div>
</div>
		<?php
		echo '<div class="align-center">';
		$this->ui->radios( 'type', $items, $this->type, false, true );
		echo '</div>';

		$this->ui->shadowbox( array( 'lifted_corner', 'cyan' ), array( $this, 'type_normal' ), 0, 'ipt_fsqm_type_zero' );
		$this->ui->shadowbox( array( 'lifted_corner', 'cyan' ), array( $this, 'type_pagination' ), 0, 'ipt_fsqm_type_two' );
		$this->ui->shadowbox( array( 'lifted_corner', 'cyan' ), array( $this, 'type_tab' ), 0, 'ipt_fsqm_type_one' );
		$this->ui->shadowbox( array( 'lifted_corner', 'cyan' ), array( $this, 'type_scroll' ), 0, 'ipt_fsqm_type_scroll' );
	}

	public function type_scroll() {
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[type_specific][tab][scroll_on_error]', __( 'Scroll to element on validation error', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->toggle( 'settings[type_specific][tab][scroll_on_error]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $this->settings['type_specific']['tab']['scroll_on_error'] ); ?></td>
			<td><?php $this->ui->help( __( 'If enabled (by default) then when progressing to the next tab/page or submitting the form, if a validation error occurs, then the page will scroll to the element.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr>
			<th style="width: 50%"><?php $this->ui->generate_label( 'settings[type_specific][scroll][progress]', __( 'Scroll to Progress Block', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[type_specific][scroll][progress]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $this->settings['type_specific']['scroll']['progress'] ); ?>
			</td>
			<td style="width: 40px;">
				<?php $this->ui->help( __( 'If enabled, then during form submission, the page will scroll to the progress block.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th style="width: 50%"><?php $this->ui->generate_label( 'settings[type_specific][scroll][message]', __( 'Scroll to Message Block', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[type_specific][scroll][message]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $this->settings['type_specific']['scroll']['message'] ); ?>
			</td>
			<td style="width: 40px;">
				<?php $this->ui->help( __( 'If enabled, then after form submission, the page will scroll to the message block.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th style="width: 50%"><?php $this->ui->generate_label( 'settings[type_specific][scroll][offset]', __( 'Scroll Offset', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->spinner( 'settings[type_specific][scroll][offset]', $this->settings['type_specific']['scroll']['offset'], __( 'Pixels', 'ipt_fsqm' ) ); ?>
			</td>
			<td style="width: 40px;">
				<?php $this->ui->help( __( 'Adjust the scroll offset value. This is useful if your theme has fixed header or similar. The plugin will try to determine the offset automatically, but sometimes it would not be enough (due to varity in HTML and CSS) and you would need to set it manually here.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>

	</tbody>
</table>
		<?php
	}

	public function type_normal() {
?>
	<table class="form-table">
		<tbody>
			<tr>
				<th><?php $this->ui->generate_label( 'settings[type_specific][normal][wrapper]', __( 'Wrap Inside', 'ipt_fsqm' ) ); ?></th>
				<td>
					<?php $this->ui->toggle( 'settings[type_specific][normal][wrapper]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $this->settings['type_specific']['normal']['wrapper'] ); ?>
				</td>
				<td style="width: 40px;">
					<?php $this->ui->help( __( 'If yes then the form will be populated inside a wrapper matching the theme. Otherwise, it will simply try to inherit the default style of your theme. If your form looks bad, then turning this on, might tune things up.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
			<tr>
				<th><?php $this->ui->generate_label( 'settings[type_specific][normal][center_heading]', __( 'Center Main Heading', 'ipt_fsqm' ) ); ?></th>
				<td>
					<?php $this->ui->toggle( 'settings[type_specific][normal][center_heading]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $this->settings['type_specific']['normal']['center_heading'] ); ?>
				</td>
				<td style="width: 40px;">
					<?php $this->ui->help( __( 'If yes then the container heading (if any) will be put on center.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
		</tbody>
	</table>
		<?php
	}

	public function type_pagination() {
?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[type_specific][pagination][show_progress_bar]', __( 'Show Progress Bar', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[type_specific][pagination][show_progress_bar]', __( 'Show', 'ipt_fsqm' ), __( 'Don\'t Show', 'ipt_fsqm' ), $this->settings['type_specific']['pagination']['show_progress_bar'] ); ?>
			</td>
			<td style="width: 40px;">
				<?php $this->ui->help( __( 'You can select to show a progress bar which will show the percentage of completion.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[type_specific][pagination][progress_bar_bottom]', __( 'Place Progress Bar on Bottom', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[type_specific][pagination][progress_bar_bottom]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $this->settings['type_specific']['pagination']['progress_bar_bottom'] ); ?>
			</td>
			<td style="width: 40px;">
				<?php $this->ui->help( __( 'If you want to show the progressbar on the bottom, enable this.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th style="width: 50%"><?php $this->ui->generate_label( 'settings[type_specific][pagination][decimal_point]', __( 'Percentage Decimal Points', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->spinner( 'settings[type_specific][pagination][decimal_point]', $this->settings['type_specific']['pagination']['decimal_point'], __( 'Digits', 'ipt_fsqm' ) ); ?>
			</td>
			<td style="width: 40px;">
				<?php $this->ui->help( __( 'Mention the number of digits that will be shown after decimal point in the progress bar.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
	</tbody>
</table>
		<?php
	}

	public function type_tab() {
?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[type_specific][tab][auto_progress]', __( 'Auto Progress Page if all validates', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[type_specific][tab][auto_progress]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $this->settings['type_specific']['tab']['auto_progress'], '1', false, true, array(
					'condid' => 'ipt_fsqm_ts_t_as_wrap,ipt_fsqm_ts_t_ad_wrap',
				) ); ?>
			</td>
			<td><?php $this->ui->help( __( 'If enabled, then form will automatically progress if all the elements under current page validates.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_ts_t_as_wrap">
			<th><?php $this->ui->generate_label( 'settings[type_specific][tab][auto_submit]', __( 'Auto Submit last page if validates', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[type_specific][tab][auto_submit]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $this->settings['type_specific']['tab']['auto_submit'] ); ?>
			</td>
			<td><?php $this->ui->help( __( 'If enabled, then form will automatically submit if all the elements under last page validates.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_ts_t_ad_wrap">
			<th><?php $this->ui->generate_label( 'settings[type_specific][tab][auto_progress_delay]', __( 'Auto Progress Delay (Milliseconds)', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->spinner( 'settings[type_specific][tab][auto_progress_delay]', $this->settings['type_specific']['tab']['auto_progress_delay'], __( 'Immediate', 'ipt_fsqm' ) ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Number of milliseconds to wait for before progressing since last change.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr>
			<th style="width: 50%"><?php $this->ui->generate_label( 'settings[type_specific][tab][block_previous]', __( 'Block Navigation to Previous Tab/Page', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[type_specific][tab][block_previous]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $this->settings['type_specific']['tab']['block_previous'] ); ?>
			</td>
			<td style="width: 40px;">
				<?php $this->ui->help( __( 'If enabled, then this will prevent users from navigating back to the previous tab once they click on the next button.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th style="width: 50%"><?php $this->ui->generate_label( 'settings[type_specific][tab][can_previous]', __( 'Can navigate to previous Tab without validation?', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[type_specific][tab][can_previous]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $this->settings['type_specific']['tab']['can_previous'] ); ?>
			</td>
			<td style="width: 40px;">
				<?php $this->ui->help( __( 'You can also select if the user is able to navigate to a previously viewed tab without validating or completely filling the form elements inside the current tab.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th style="width: 50%"><?php $this->ui->generate_label( 'settings[type_specific][tab][any_tab]', __( 'Can navigate to any Tab?', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[type_specific][tab][any_tab]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $this->settings['type_specific']['tab']['any_tab'] ); ?>
			</td>
			<td style="width: 40px;">
				<?php $this->ui->help( __( 'If selected, then user can navigate to any tab by clicking the tab button. This overrides all previous settings. If the current tab has any validation error, then it will only be caught after submitting the form. So do this only if all the elements are non-required or only the last tab has required/compulsory elements.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th style="width: 50%"><?php $this->ui->generate_label( 'settings[type_specific][tab][scroll]', __( 'Scroll to the page top', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[type_specific][tab][scroll]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $this->settings['type_specific']['tab']['scroll'] ); ?>
			</td>
			<td style="width: 40px;">
				<?php $this->ui->help( __( 'If enabled then the page will automatically scroll to the page top when next/previous button is pressed.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
	</tbody>
</table>
		<?php
	}

	public function form_settings() {
		$hor_tabs = array();

		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_general',
			'label' => __( 'General Settings', 'ipt_fsqm' ),
			'callback' => array( $this, 'general' ),
		);
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_limitation',
			'label' => __( 'Submission Limitation', 'ipt_fsqm' ),
			'callback' => array( $this, 'limitation' ),
		);
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_buttons',
			'label' => __( 'Progress Buttons', 'ipt_fsqm' ),
			'callback' => array( $this, 'buttons' ),
		);
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_save_progress',
			'label' => __( 'Save Progress', 'ipt_fsqm' ),
			'callback' => array( $this, 'save_progress' ),
		);
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_format',
			'label' => __( 'Format Strings', 'ipt_fsqm' ),
			'callback' => array( $this, 'format_options' ),
		);
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_submission',
			'label' => __( 'Form Submission', 'ipt_fsqm' ),
			'callback' => array( $this, 'submission' ),
		);
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_redirect',
			'label' => __( 'Redirection', 'ipt_fsqm' ),
			'callback' => array( $this, 'redirect' ),
		);
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_ganalytics',
			'label' => __( 'Google Analytics', 'ipt_fsqm' ),
			'callback' => array( $this, 'ganalytics' ),
		);
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_standalone',
			'label' => __( 'Standalone Page', 'ipt_fsqm' ),
			'callback' => array( $this, 'standalone_config' ),
		);
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_opengraph',
			'label' => __( 'Standalone Page OpenGraph', 'ipt_fsqm' ),
			'callback' => array( $this, 'standalone_opengraph' ),
		);

		$this->ui->tabs( $hor_tabs, false, true );
	}

	public function standalone_opengraph() {
		$op = $this->settings['opengraph'];
		EForm_OpenGraph_Helper::admin_settings( 'settings[opengraph]', $this->ui, $op );
	}

	public function standalone_config() {
		$op = $this->settings['standalone'];
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[standalone][title]', __( 'Page Title', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[standalone][title]', $op['title'], __( 'Form Name', 'ipt_fsqm' ), 'large' ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the title of the page. Leaving empty will use the form name.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[standalone][description]', __( 'Meta Description', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[standalone][description]', $op['description'], __( 'Disabled', 'ipt_fsqm' ), 'large' ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the description of the page.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[standalone][head_html]', __( 'Custom HTML on head section', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[standalone][head_html]', $op['head_html'], __( 'Disabled', 'ipt_fsqm' ), 'large' ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter custom HTML to be put on HTML <code>&lt;head&gt;</code>.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[standalone][before_body_html]', __( 'Custom HTML at beginning of body', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[standalone][before_body_html]', $op['before_body_html'], __( 'Disabled', 'ipt_fsqm' ), 'large' ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter custom HTML to be put on at the beginning of opening <code>&lt;body&gt;</code>.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[standalone][after_body_html]', __( 'Custom HTML at end of body', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[standalone][after_body_html]', $op['after_body_html'], __( 'Disabled', 'ipt_fsqm' ), 'large' ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter custom HTML to be put on at the end of closing <code>&lt;/body&gt;</code>.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
	</tbody>
</table>
		<?php
	}

	public function quiz_settings() {
		$hor_tabs = array();
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_ranking',
			'label' => __( 'Ranking System', 'ipt_fsqm' ),
			'callback' => array( $this, 'ranking' ),
		);
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_timer',
			'label' => __( 'Quiz Timer', 'ipt_fsqm' ),
			'callback' => array( $this, 'timer' ),
		);
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_stopwatch',
			'label' => __( 'Quiz Stopwatch', 'ipt_fsqm' ),
			'callback' => array( $this, 'stopwatch' ),
		);
		$this->ui->tabs( $hor_tabs, false, true );
	}

	public function result_email() {
		$hor_tabs = array();

		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_social',
			'label' => __( 'Social Sharing', 'ipt_fsqm' ),
			'callback' => array( $this, 'social' ),
		);
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_user',
			'label' => __( 'User Notification', 'ipt_fsqm' ),
			'callback' => array( $this, 'user' ),
		);
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_admin',
			'label' => __( 'Admin Notification', 'ipt_fsqm' ),
			'callback' => array( $this, 'admin' ),
		);
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_email_template',
			'label' => __( 'Email Design', 'ipt_fsqm' ),
			'callback' => array( $this, 'email_template' ),
		);
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_summary',
			'label' => __( 'Summary Tables', 'ipt_fsqm' ),
			'callback' => array( $this, 'summary' ),
		);
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_trackback',
			'label' => __( 'Trackback Page', 'ipt_fsqm' ),
			'callback' => array( $this, 'trackback' ),
		);

		$this->ui->tabs( $hor_tabs, false, true );
	}

	public function wp_core() {
		$hor_tabs = array();

		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_core_reg',
			'label' => __( 'User Registration', 'ipt_fsqm' ),
			'callback' => array( $this, 'wp_core_reg' ),
		);
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_core_guestpost',
			'label' => __( 'Guest/Frontend Posting', 'ipt_fsqm' ),
			'callback' => array( $this, 'wp_core_guestpost' ),
		);
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_core_user_meta',
			'label' => __( 'User Meta Update', 'ipt_fsqm' ),
			'callback' => array( $this, 'wp_core_user_meta' ),
		);
		$this->ui->tabs( apply_filters( 'ipt_fsqm_settings_core_tabs', $hor_tabs ), false, true );
	}

	public function wp_core_user_meta() {
		$op = $this->settings['core']['user_meta'];

		// Prepare data for sda
		$m_type_select = array(
			0 => array(
				'value' => 'mcq',
				'label' => __( '(M) MCQ', 'ipt_fsqm' ),
			),
			1 => array(
				'value' => 'freetype',
				'label' => __( '(F) Text Input', 'ipt_fsqm' ),
			),
			2 => array(
				'value' => 'pinfo',
				'label' => __( '(O) Others', 'ipt_fsqm' ),
			),
		);
		$sda_columns = array(
			0 => array(
				'label' => __( '(X)', 'ipt_fsqm' ),
				'size' => '25',
				'type' => 'select',
			),
			1 => array(
				'label' => __( '{KEY}', 'ipt_fsqm' ),
				'size' => '25',
				'type' => 'spinner',
			),
			2 => array(
				'label' => __( 'Meta Key (No Space, Underscore and alphabets only)', 'ipt_fsqm' ),
				'size' => '50',
				'type' => 'text',
			),
		);
		$sda_labels = array(
			'add' => __( 'Add New Meta', 'ipt_fsqm' ),
		);
		$sda_data_name_prefix = 'settings[core][user_meta][meta][__SDAKEY__]';
		$sda_data = array(
			0 => array( $sda_data_name_prefix . '[m_type]', $m_type_select, 'mcq', false, false, false, true, array( 'fit-text' ) ),
			1 => array( $sda_data_name_prefix . '[key]', '0', __( '{key}', 'ipt_fsqm' ), 0, 500 ),
			2 => array( $sda_data_name_prefix . '[meta_key]', '', '' ),
		);
		$sda_items = array();
		$sda_max_key = null;
		$sda_items_name_prefix = 'settings[core][user_meta][meta][%d]';
		foreach ( (array) $op['meta'] as $meta_key => $metadata ) {
			$sda_max_key = max( array( $sda_max_key, $meta_key ) );
			$sda_items[] = array(
				0 => array( sprintf( $sda_items_name_prefix . '[m_type]', $meta_key ), $m_type_select, $metadata['m_type'], false, false, false, true, array( 'fit-text' ) ),
				1 => array( sprintf( $sda_items_name_prefix . '[key]', $meta_key ), $metadata['key'], __( '{key}', 'ipt_fsqm' ), 0, 500 ),
				2 => array( sprintf( $sda_items_name_prefix . '[meta_key]', $meta_key ), $metadata['meta_key'], '' ),
			);
		}
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[core][user_meta][enabled]', __( 'Enable User Meta Update', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[core][user_meta][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, array(
					'condid' => 'ipt_fsqm_eform_core_um_ma_wrap,ipt_fsqm_eform_core_um_msda_wrap'
				) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you wish to enable updating user metadata through this form, please enable it first. Other settings will follow.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_eform_core_um_ma_wrap">
			<th><?php $this->ui->generate_label( 'settings[core][user_meta][metaarray]', __( 'Store Array instead of Stringified Value', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[core][user_meta][metaarray]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['metaarray']  ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'The element submission data will be stringified before being added as the metadata. You can change this behavior by changing the toggle.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_eform_core_um_msda_wrap">
			<td colspan="3">
				<?php $this->ui->sda_list( array(
					'columns' => $sda_columns,
					'labels' => $sda_labels,
					'features' => array(
						'draggable' => false,
					),
				), $sda_items, $sda_data, $sda_max_key ); ?>
			</td>
		</tr>
	</tbody>
</table>
		<?php
	}

	public function wp_core_reg() {
		$op = $this->settings['core']['reg'];

		// Hide everything if user registration is turned off
		if ( ! get_option( 'users_can_register' ) ) {
			$this->ui->msg_error( sprintf( __( 'User Registration is disabled on your website. Please enable it first by going to <a href="%1$s" target="_blank">Settings > General</a> from your WordPress Dashboard.', 'ipt_fsqm' ), admin_url( 'options-general.php' ) ) );

			return;
		}

		// Prepare data for sda
		$m_type_select = array(
			0 => array(
				'value' => 'mcq',
				'label' => __( '(M) MCQ', 'ipt_fsqm' ),
			),
			1 => array(
				'value' => 'freetype',
				'label' => __( '(F) Text Input', 'ipt_fsqm' ),
			),
			2 => array(
				'value' => 'pinfo',
				'label' => __( '(O) Others', 'ipt_fsqm' ),
			),
		);
		$sda_columns = array(
			0 => array(
				'label' => __( '(X)', 'ipt_fsqm' ),
				'size' => '25',
				'type' => 'select',
			),
			1 => array(
				'label' => __( '{KEY}', 'ipt_fsqm' ),
				'size' => '25',
				'type' => 'spinner',
			),
			2 => array(
				'label' => __( 'Meta Key (No Space, Underscore and alphabets only)', 'ipt_fsqm' ),
				'size' => '50',
				'type' => 'text',
			),
		);
		$sda_labels = array(
			'add' => __( 'Add New Meta', 'ipt_fsqm' ),
		);
		$sda_data_name_prefix = 'settings[core][reg][meta][__SDAKEY__]';
		$sda_data = array(
			0 => array( $sda_data_name_prefix . '[m_type]', $m_type_select, 'mcq', false, false, false, true, array( 'fit-text' ) ),
			1 => array( $sda_data_name_prefix . '[key]', '0', __( '{key}', 'ipt_fsqm' ), 0, 500 ),
			2 => array( $sda_data_name_prefix . '[meta_key]', '', '' ),
		);
		$sda_items = array();
		$sda_max_key = null;
		$sda_items_name_prefix = 'settings[core][reg][meta][%d]';
		foreach ( (array) $op['meta'] as $meta_key => $metadata ) {
			$sda_max_key = max( array( $sda_max_key, $meta_key ) );
			$sda_items[] = array(
				0 => array( sprintf( $sda_items_name_prefix . '[m_type]', $meta_key ), $m_type_select, $metadata['m_type'], false, false, false, true, array( 'fit-text' ) ),
				1 => array( sprintf( $sda_items_name_prefix . '[key]', $meta_key ), $metadata['key'], __( '{key}', 'ipt_fsqm' ), 0, 500 ),
				2 => array( sprintf( $sda_items_name_prefix . '[meta_key]', $meta_key ), $metadata['meta_key'], '' ),
			);
		}
		$roles_item = array();
		$roles_item[] = array(
			'value' => 'wp_default',
			'label' => __( 'WordPress Default', 'ipt_fsqm' ),
		);
		$editable_roles = get_editable_roles();
		foreach ( $editable_roles as $role_key => $role ) {
			$roles_item[] = array(
				'value' => $role_key,
				'label' => $role['name'] . ' (' . $role_key . ')',
			);
		}
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[core][reg][enabled]', __( 'Enable User Registration', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[core][reg][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, array(
					'condid' => 'ipt_fsqm_core_reg_uid_wrap,ipt_fsqm_core_reg_pid_wrap,ipt_fsqm_core_reg_meta_wrap,ipt_fsqm_core_reg_metaarray_wrap,ipt_fsqm_core_reg_metatitle_wrap,ipt_fsqm_core_reg_role_wrap,ipt_fsqm_core_reg_hide_pinfo_wrap,ipt_fsqm_core_reg_hide_meta_wrap'
				) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you wish to enable user registration through this form, please enable it first. Other settings will follow.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_core_reg_uid_wrap">
			<th><?php $this->ui->generate_label( 'settings[core][reg][username_id]', __( 'Username Field ID', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[core][reg][username_id]', $op['username_id'], __( 'ID', 'ipt_fsqm' ) ); ?>
				<div class="clear"></div>
				<span class="description"><?php _e( 'Mention ID (like <code>F12</code>) of a <strong>Text Input</strong> element from <strong>Text Input & Upload (F)</strong>.', 'ipt_fsqm' ); ?></span>
			</td>
			<td><?php $this->ui->help( __( 'You need to add a Text Input element from Text Input & Upload inside your form and mention the ID here. It will be used to create the username.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_core_reg_pid_wrap">
			<th><?php $this->ui->generate_label( 'settings[core][reg][password_id]', __( 'Password Field ID', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[core][reg][password_id]', $op['password_id'], __( 'ID', 'ipt_fsqm' ) ); ?>
				<div class="clear"></div>
				<span class="description"><?php _e( 'Mention ID (like <code>O13</code>) of a <strong>Password</strong> element from <strong>Other Form Elements (O)</strong>.', 'ipt_fsqm' ); ?></span>
			</td>
			<td><?php $this->ui->help( __( 'You need to add a Password element from Other Form Elements inside your form and mention the ID here. It will be used to create the password.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_core_reg_hide_pinfo_wrap">
			<th><?php $this->ui->generate_label( 'settings[core][reg][hide_pinfo]', __( 'Hide First Name, Last Name & Email for logged in users', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[core][reg][hide_pinfo]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['hide_pinfo'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If the form is viewed by logged in users, then username and password fields would always be hidden. But in case you would like to hide First Name, Last Name and Email fields too, then please enable this option.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_core_reg_role_wrap">
			<th><?php $this->ui->generate_label( 'settings[core][reg][role]', __( 'Set Default Role', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->select( 'settings[core][reg][role]', $roles_item, $op['role'] ); ?>
			</td>
			<td><?php $this->ui->help( __( 'By default the newly created user will have the default WordPress Role that you have set under Settings. If you wish eForm to override this, please set another role here.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_core_reg_metatitle_wrap">
			<th colspan="2"><?php $this->ui->generate_label( 'settings[core][reg][meta]', __( 'Additional User Meta Data', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->help( __( 'If you want to add additional user metadata while registration, please mention the fields here.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_core_reg_metaarray_wrap">
			<th><?php $this->ui->generate_label( 'settings[core][reg][metaarray]', __( 'Store Array instead of Stringified Value', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[core][reg][metaarray]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['metaarray']  ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'The element submission data will be stringified before being added as the metadata. You can change this behavior by changing the toggle.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_core_reg_meta_wrap">
			<td colspan="3">
				<?php $this->ui->sda_list( array(
					'columns' => $sda_columns,
					'labels' => $sda_labels,
					'features' => array(
						'draggable' => false,
					),
				), $sda_items, $sda_data, $sda_max_key ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_core_reg_hide_meta_wrap">
			<th><?php $this->ui->generate_label( 'settings[core][reg][hide_meta]', __( 'Hide Specified Meta Elements for logged in users', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[core][reg][hide_meta]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['hide_meta'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enable to hide the elements you have specified above for logged in users.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
	</tbody>
</table>
		<?php
	}

	public function wp_core_guestpost() {
		$op = $this->settings['core']['post'];

		// Get post types
		$post_types = array(
			'post' => 'post',
			'page' => 'page',
		);
		// Add custom post types
		$post_types = array_merge( $post_types, (array) get_post_types( array(
			'public' => true,
			'_builtin' => false,
		), 'names' ) );

		$post_type_items = array();
		foreach ( $post_types as $post_type ) {
			$post_type_obj = get_post_type_object( $post_type );
			if ( is_null( $post_type_obj ) ) {
				continue;
			}
			$post_type_items[] = array(
				'label' => $post_type_obj->labels->singular_name,
				'value' => $post_type,
				'data' => array(
					'condid' => 'ipt_fsqm_settings_core_bio_post_tax_op_wrap_' . $post_type,
				),
			);
		}

		// Get taxonomies per post type
		$taxonomies = array();
		$taxonomies_single = array();
		$taxonomies_required = array();
		foreach ( $post_types as $post_type ) {
			$object_taxonomies = get_object_taxonomies( $post_type, 'objects' );
			$taxonomies[ $post_type ] = array();
			$taxonomies_single[ $post_type ] = array();
			$taxonomies_required[ $post_type ] = array();
			if ( ! empty( $object_taxonomies ) ) {
				// Create default set of data
				if ( ! isset( $op['taxnomy_single'][ $post_type ] ) ) {
					$op['taxnomy_single'][ $post_type ] = array();
				}
				if ( ! isset( $op['taxonomy_required'][ $post_type ] ) ) {
					$op['taxonomy_required'][ $post_type ] = array();
				}
				if ( ! isset( $op['taxonomies'][ $post_type ] ) ) {
					$op['taxonomies'][ $post_type ] = array();
				}

				// loop through and add taxonomies
				foreach ( $object_taxonomies as $objtxn ) {
					if ( true == $objtxn->public ) {
						$taxonomies[ $post_type ][] = array(
							'label' => $objtxn->label,
							'value' => $objtxn->name,
						);
						$taxonomies_single[ $post_type ][] = array(
							'label' => __( 'Can select only one', 'ipt_fsqm' ),
							'value' => $objtxn->name,
						);
						$taxonomies_required[ $post_type ][] = array(
							'label' => __( 'Compulsory', 'ipt_fsqm' ),
							'value' => $objtxn->name,
						);
					}
				}
			}
		}
		// Post status
		$post_statuses = get_post_statuses();
		// Prepare data for sda
		$m_type_select = array(
			0 => array(
				'value' => 'mcq',
				'label' => __( '(M) MCQ', 'ipt_fsqm' ),
			),
			1 => array(
				'value' => 'freetype',
				'label' => __( '(F) Text Input', 'ipt_fsqm' ),
			),
			2 => array(
				'value' => 'pinfo',
				'label' => __( '(O) Others', 'ipt_fsqm' ),
			),
		);
		$sda_columns = array(
			0 => array(
				'label' => __( '(X)', 'ipt_fsqm' ),
				'size' => '25',
				'type' => 'select',
			),
			1 => array(
				'label' => __( '{KEY}', 'ipt_fsqm' ),
				'size' => '25',
				'type' => 'spinner',
			),
			2 => array(
				'label' => __( 'Meta Key (No Space, Underscore and alphabets only)', 'ipt_fsqm' ),
				'size' => '50',
				'type' => 'text',
			),
		);
		$sda_labels = array(
			'add' => __( 'Add New Meta', 'ipt_fsqm' ),
		);
		$sda_data_name_prefix = 'settings[core][post][meta][__SDAKEY__]';
		$sda_data = array(
			0 => array( $sda_data_name_prefix . '[m_type]', $m_type_select, 'mcq', false, false, false, true, array( 'fit-text' ) ),
			1 => array( $sda_data_name_prefix . '[key]', '0', __( '{key}', 'ipt_fsqm' ), 0, 500 ),
			2 => array( $sda_data_name_prefix . '[meta_key]', '', '' ),
		);
		$sda_items = array();
		$sda_max_key = null;
		$sda_items_name_prefix = 'settings[core][post][meta][%d]';
		foreach ( (array) $op['meta'] as $meta_key => $metadata ) {
			$sda_max_key = max( array( $sda_max_key, $meta_key ) );
			$sda_items[] = array(
				0 => array( sprintf( $sda_items_name_prefix . '[m_type]', $meta_key ), $m_type_select, $metadata['m_type'], false, false, false, true, array( 'fit-text' ) ),
				1 => array( sprintf( $sda_items_name_prefix . '[key]', $meta_key ), $metadata['key'], __( '{key}', 'ipt_fsqm' ), 0, 500 ),
				2 => array( sprintf( $sda_items_name_prefix . '[meta_key]', $meta_key ), $metadata['meta_key'], '' ),
			);
		}
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[core][post][enabled]', __( 'Enable Guest Posting', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[core][post][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, array(
					'condid' => 'ipt_fsqm_settings_core_post_uid_wrap,ipt_fsqm_settings_core_post_bio_wrap,ipt_fsqm_settings_core_post_gms_wrap,ipt_fsqm_settings_core_post_type_wrap,ipt_fsqm_settings_core_post_biotitle_wrap,ipt_fsqm_settings_core_taxnomies_wrap,ipt_fsqm_core_post_status_wrap,ipt_fsqm_core_post_metatitle_wrap,ipt_fsqm_core_post_metaarray_wrap,ipt_fsqm_core_post_meta_wrap,ipt_fsqm_settings_core_post_fm_wrap,ipt_fsqm_settings_core_post_adms_wrap'
				) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you wish to enable guest posting this form, please enable it first. Other settings will follow. You will also need to place a Guest Blogging Element inside your form.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_settings_core_post_uid_wrap">
			<th><?php $this->ui->generate_label( 'settings[core][post][user_id]', __( 'Map Post to User (ID)', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->spinner( 'settings[core][post][user_id]', $op['user_id'], __( 'ID', 'ipt_fsqm' ), '1' ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Mention the ID of the user to whom the guest post would be mapped. If you have registration enabled under the same form then this will be ignored and the guest post would be created under the newly registered user. If user is logged in, then also it will be ignored and the post would be mapped to the current logged in user.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_settings_core_post_bio_wrap">
			<th><?php $this->ui->generate_label( 'settings[core][post][bio]', __( 'Show field for entering author bio', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[core][post][bio]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['bio'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you wish to collect author biography then please enable this option. It will show a textarea through which author can post their biography. It will be saved as a metadata under the created draft. This will not show up for logged in users.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_settings_core_post_biotitle_wrap">
			<th><?php $this->ui->generate_label( 'settings[core][post][bio_title]', __( 'Bio Field Title', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[core][post][bio_title]', $op['bio_title'], __( 'Write here', 'ipt_fsqm' ) ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Enter the label that will be shown beside the bio field.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_settings_core_post_fm_wrap">
			<th><?php $this->ui->generate_label( 'settings[core][post][feature_image]', __( 'Upload Element for Feature Image', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->spinner( 'settings[core][post][feature_image]', $op['feature_image'], __( 'ID of Upload Element', 'ipt_fsqm' ) ); ?>
				<br />
				<span class="description"><?php _e( 'Make sure the upload element has only one image upload and WordPress Media Integration is turned on.', 'ipt_fsqm' ); ?></span>
			</td>
			<td><?php $this->ui->help( __( 'If you want to add feature images through frontend, then enter the ID of the upload element here. Make sure the upload element has only one image upload and WordPress Media Integration is turned on.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_settings_core_post_adms_wrap">
			<th><?php $this->ui->generate_label( 'settings[core][post][add_msg]', __( 'Additional Content', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[core][post][add_msg]', $op['add_msg'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
			</td>
			<td><?php $this->ui->help( __( 'This content will be added at the end of the guest post automatically. All format strings are supported.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_settings_core_post_gms_wrap">
			<th><?php $this->ui->generate_label( 'settings[core][post][guest_msg]', __( 'Editor\'s Note', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[core][post][guest_msg]', $op['guest_msg'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
			</td>
			<td><?php $this->ui->help( __( 'If you want to automatically add some editor\'s note to the submitted article then you can put it here. This field is HTML enabled. Possible format strings are <code>%NAME%, %BIO%, %AVATAR%</code>. This would be added only for non-logged in users.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_settings_core_post_type_wrap">
			<th><?php $this->ui->generate_label( 'settings[core][post][post_type]', __( 'Select Post Type', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->select( 'settings[core][post][post_type]', $post_type_items, $op['post_type'], false, true ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'You can put the draft under any custom post type too. Please select your desired post type.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_settings_core_taxnomies_wrap">
			<th><?php $this->ui->generate_label( 'settings[core][post][taxonomies]', __( 'Select Taxonomies to show', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php foreach ( $taxonomies as $p_type => $p_txm_items ) : ?>
					<div id="ipt_fsqm_settings_core_bio_post_tax_op_wrap_<?php echo $p_type; ?>">
						<?php if ( empty( $p_txm_items ) ) : ?>
							<?php $this->ui->msg_update( __( 'No registered taxonomies for this post type.', 'ipt_fsqm' ) ); ?>
							<?php echo '</div>'; ?>
							<?php continue; ?>
						<?php endif; ?>
						<table style="width: 100%;">
							<tbody>
								<tr>
									<td>
										<?php $this->ui->checkboxes( 'settings[core][post][taxonomies][' . $p_type . '][]', $p_txm_items, $op['taxonomies'][ $p_type ], false, false, '<div class="clear"></div>' ); ?>
									</td>
									<td>
										<?php $this->ui->checkboxes( 'settings[core][post][taxnomy_single][' . $p_type . '][]', $taxonomies_single[ $p_type ], $op['taxnomy_single'][ $p_type ], false, false, '<div class="clear"></div>' ); ?>
									</td>
									<td>
										<?php $this->ui->checkboxes( 'settings[core][post][taxonomy_required][' . $p_type . '][]', $taxonomies_required[ $p_type ], $op['taxonomy_required'][ $p_type ], false, false, '<div class="clear"></div>' ); ?>
									</td>
								</tr>
							</tbody>
						</table>

					</div>
					<?php ?>
				<?php endforeach; ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you wish to let the author select taxonomies while submitting the post, then you can select which taxonomies to show. You can select multiple taxonomies and the list will be populated automatically. If you select "Can select only one" then radio buttons would appear instead of checkboxes.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_core_post_status_wrap">
			<th><?php $this->ui->generate_label( 'settings[core][post][status]', __( 'Set Post Status', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->select( 'settings[core][post][status]', $post_statuses, $op['status'] ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Set the post status you would like the article to publish with. It is recommended to use Draft.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_core_post_metatitle_wrap">
			<th colspan="2"><?php $this->ui->generate_label( 'settings[core][post][meta]', __( 'Additional Post Meta Data', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->help( __( 'If you want to add additional post metadata while submitting, please mention the fields here.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_core_post_metaarray_wrap">
			<th><?php $this->ui->generate_label( 'settings[core][post][metaarray]', __( 'Store Array instead of Stringified Value', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[core][post][metaarray]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['metaarray']  ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'The element submission data will be stringified before being added as the metadata. You can change this behavior by changing the toggle.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_core_post_meta_wrap">
			<td colspan="3">
				<?php $this->ui->sda_list( array(
					'columns' => $sda_columns,
					'labels' => $sda_labels,
					'features' => array(
						'draggable' => false,
					),
				), $sda_items, $sda_data, $sda_max_key ); ?>
			</td>
		</tr>
	</tbody>
</table>
		<?php
	}

	public function integration() {
		$hor_tabs = array();

		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_intg_cond',
			'label' => __( 'Conditional Activation', 'ipt_fsqm' ),
			'callback' => array( $this, 'intg_conditional' ),
		);

		$hor_tabs[] = [
			'id' => 'ipt_fsqm_settings_intg_instant',
			'label' => __( 'Instant Subscription', 'ipt_fsqm' ),
			'callback' => array( $this, 'intg_instant' ),
		];

		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_mailchimp',
			'label' => __( 'MailChimp', 'ipt_fsqm' ),
			'callback' => array( $this, 'mailchimp' ),
		);

		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_aweber',
			'label' => __( 'Aweber', 'ipt_fsqm' ),
			'callback' => array( $this, 'aweber' ),
		);

		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_get_response',
			'label' => __( 'Get Response', 'ipt_fsqm' ),
			'callback' => array( $this, 'get_response' ),
		);

		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_campaign_monitor',
			'label' => __( 'Campaign Monitor', 'ipt_fsqm' ),
			'callback' => array( $this, 'campaign_monitor' ),
		);

		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_mymail',
			'label' => __( 'Mailster (MyMail)', 'ipt_fsqm' ),
			'callback' => array( $this, 'mymail' ),
		);

		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_sendy',
			'label' => __( 'Sendy.co', 'ipt_fsqm' ),
			'callback' => array( $this, 'sendy' ),
		);

		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_active_campaign',
			'label' => __( 'Active Campaign', 'ipt_fsqm' ),
			'callback' => array( $this, 'active_campaign' ),
		);

		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_mailpoet',
			'label' => __( 'MailPoet 2', 'ipt_fsqm' ),
			'callback' => array( $this, 'mailpoet' ),
		);

		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_mailpoet3',
			'label' => __( 'MailPoet 3', 'ipt_fsqm' ),
			'callback' => array( $this, 'mailpoet3' ),
		);

		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_mailerlite',
			'label' => __( 'MailerLite', 'ipt_fsqm' ),
			'callback' => array( $this, 'mailerlite' ),
		);

		$hor_tabs[] = [
			'id' => 'ipt_fsqm_settings_mailwizz',
			'label' => __( 'MailWizz', 'ipt_fsqm' ),
			'callback' => [ $this, 'mailwizz' ],
		];

		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_enormail',
			'label' => __( 'Enormail', 'ipt_fsqm' ),
			'callback' => array( $this, 'enormail' ),
		);

		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_convertkit',
			'label' => __( 'Convertkit', 'ipt_fsqm' ),
			'callback' => array( $this, 'convertkit' ),
		);

		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_formhandler',
			'label' => __( 'Webhook (Custom URL)', 'ipt_fsqm' ),
			'callback' => array( $this, 'formhandler_integration' ),
		);

		$hor_tabs = apply_filters( 'ipt_fsqm_integration_settings_tabs', $hor_tabs, $this );

		$this->ui->tabs( $hor_tabs, false, true );
	}

	public function general() {
		$form_categories = array(
			array(
				'value' => '0',
				'label' => __( 'None', 'ipt_fsqm' ),
			),
		);
		$db_categories = IPT_FSQM_Form_Elements_Static::get_all_categories();
		if ( null != $db_categories ) {
			foreach ( $db_categories as $dbc ) {
				$form_categories[] = array(
					'value' => $dbc->id,
					'label' => $dbc->name,
				);
			}
		}
?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'category', __( 'Form Category', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->select( 'category', $form_categories, $this->category ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Set the category of the form for quick filtering.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[general][pipe]', __( 'Interactive Form', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[general][pipe]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $this->settings['general']['pipe'] ); ?>
				<span class="description"><?php _e( 'Pipe Element Values to Labels and Description with template tags like <code>%M0%, %F2%, %O11%</code> etc.', 'ipt_fsqm' ); ?></span>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enable this settings to have interactive forms. Once enabled, you can use the template tags in the format <code>%(X){KEY}%</code>. For example, if you wish to substitute the element <code>(M){12}</code>, then just write <code>%M12</code> in any label, option label or description or richtext. If for some reason, you would like to just include the keyword as <code>%M12%</code> as is, then you need to escape it like <code>%%M12%%</code>.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[general][terms_page]', __( 'Terms & Condition Page', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->dropdown_pages( array(
				'selected' => $this->settings['general']['terms_page'],
				'name' => 'settings[general][terms_page]',
				'show_option_none' => __( 'None -- Do not show', 'ipt_fsqm' ),
				'option_none_value' => '0'
			) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If any page ID is given here, then user will be presented with a checkbox which he/she has to check before submitting. This will lead to the specified page on click (depending on the terms phrase).', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[general][terms_phrase]', __( 'Terms Phrase', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[general][terms_phrase]', $this->settings['general']['terms_phrase'], __( 'Disabled', 'ipt_fsqm' ), 'large' ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the phrase of the terms and condition. <code>%1$s</code> will be replaced by the link to the page and <code>%2$s</code> will be replaced by the IP Address of the user.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[general][comment_title]', __( 'Administrator Remarks Title', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[general][comment_title]', $this->settings['general']['comment_title'], __( 'Disabled', 'ipt_fsqm' ), 'large' ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the Remarks title that will be shown on the print section and the track page. Leave it empty to disable this feature.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[general][default_comment]', __( 'Default Administrator Remarks', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[general][default_comment]', $this->settings['general']['default_comment'], __( 'Enter default administrator remarks', 'ipt_fsqm' ), 'large' ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the default Remarks that will automatically added to the database while submitting the form.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[general][can_edit]', __( 'Users Can Edit Submission', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[general][can_edit]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $this->settings['general']['can_edit'], '1', false, true, array(
					'condid' => 'ipt_fsqm_general_edit_time_wrap',
				) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If enabled, then registered users can edit their submissions through the User Portal page.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_general_edit_time_wrap">
			<th><?php $this->ui->generate_label( 'settings[general][edit_time]', __( 'Edit Time Limit (in hours)', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->spinner( 'settings[general][edit_time]', $this->settings['general']['edit_time'], __( 'Always', 'ipt_fsqm' ) ); ?></td>
			<td><?php $this->ui->help( __( 'Limit the edit time in hours. Can be fraction. Also a zero value or an empty or a negative value means unlimited.', 'ipt_fsqm' ) ); ?></td>
		</tr>
	</tbody>
</table>
		<?php
	}

	public function user() {
		$op = $this->settings['user'];
?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[user][notification_email]', __( 'Sender\'s Email', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[user][notification_email]', $op['notification_email'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the email which the user will see as the Sender\'s Email on the email he/she receives. It is recommended to use an email from the same domain. Otherwise it might end up into spams. Entering an empty email will stop the user notification service. So leave it empty to disable sending emails to users.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[user][notification_from]', __( 'Sender\'s Name', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[user][notification_from]', $op['notification_from'], __( 'Enter sender\'s name', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the name which the user will see as the Sender\'s Name on the email he/she receives.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[user][notification_sub]', __( 'Notification Subject', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[user][notification_sub]', $op['notification_sub'], __( 'Enter the subject', 'ipt_fsqm' ), 'large' ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
				<p><?php _e( 'Enter the subject of the notification email of the user/surveyee. Following format strings are available.', 'ipt_fsqm' ); ?></p>
				<ul class="ul-square">
					<li><code>%FORMNAME%</code> : <?php _e( 'Replaced by the Form Name.', 'ipt_fsqm' ); ?></li>
					<li><code>%SITENAME%</code> : <?php _e( 'Replaced by the Site name.', 'ipt_fsqm' ); ?></li>
					<li><code>%FNAME%</code> : <?php _e( 'Replaced by the user\'s first name.', 'ipt_fsqm' ); ?></li>
					<li><code>%LNAME%</code> : <?php _e( 'Replaced by the user\'s last name.', 'ipt_fsqm' ); ?></li>
					<li><code>%PHONE%</code> : <?php _e( 'Replaced by the user\'s phone number.', 'ipt_fsqm' ); ?></li>
					<li><code>%EMAIL%</code> : <?php _e( 'Replaced by the user\'s email address.', 'ipt_fsqm' ); ?></li>
				</ul>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[user][notification_msg]', __( 'Notification Message', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[user][notification_msg]', $op['notification_msg'], __( 'Enter the message', 'ipt_fsqm' ), 'large' ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
					<p><?php _e( 'Enter the message that you want to send to the user/surveyee on form submission. Paragraphs and line breaks will be added automatically. In addition you can also put custom HTML code. You can use a few format strings which will be replaced by their corresponding values.', 'ipt_fsqm' ) ?></p>
					<ul class="ul-square">
						<li><strong>%NAME%</strong> : <?php _e( 'Will be replaced by the full name of the user.', 'ipt_fsqm' ); ?></li>
						<li><strong>%TRACK_LINK%</strong> : <?php _e( 'Will be replaced by the raw link from where the user can see the status of his submission.', 'ipt_fsqm' ); ?></li>
						<li><strong>%TRACK%</strong> : <?php _e( 'Will be replaced by a "Click Here" button linked to the track page.', 'ipt_fsqm' ); ?></li>
						<li><strong>%SCORE%</strong> : <?php _e( 'Will be replaced by the score obtained/total score.', 'ipt_fsqm' ); ?></li>
						<li><strong>%OSCORE%</strong> : <?php _e( 'Will be replaced by the score obtained.', 'ipt_fsqm' ); ?></li>
						<li><strong>%MSCORE%</strong> : <?php _e( 'Will be replaced by the total score.', 'ipt_fsqm' ); ?></li>
						<li><strong>%SCOREPERCENT%</strong> : <?php _e( 'Will be replaced by the percentage score obtained.', 'ipt_fsqm' ); ?></li>
						<li><strong>%DESIGNATION%</strong> : <?php _e( 'If the score falls under a valid ranking range, then this will be replaced by the given designation title.', 'ipt_fsqm' ); ?></li>
						<li><strong>%DESIGNATIONMSG%</strong> : <?php _e( 'If the score falls under a valid ranking range, then this will be replaced by the given designation message.', 'ipt_fsqm' ); ?></li>
						<li><strong>%TRACK_ID%</strong> : <?php _e( 'Will be replaced by the Tracking ID of the submission which the user can enter in the track page.', 'ipt_fsqm' ); ?></li>
						<li><strong>%SUBMISSION_ID%</strong> : <?php _e( 'Will be replaced by the ID of the submission.', 'ipt_fsqm' ); ?></li>
						<li><strong>%PORTAL%</strong> : <?php _e( 'Will be replaced by the raw link of the user portal page from where registered users can see all their submissions.', 'ipt_fsqm' ); ?></li>
					</ul>
					<p><?php _e( 'If you are using the %TRACK_LINK% make sure you have placed <code>[ipt_fsqm_track]</code> on some page/post and have entered its ID in the settings section.', 'ipt_fsqm' ); ?></p>
					<p><?php printf( __( 'An updated list can always be found <a href="%1$s" target="_blank">here</a>.', 'ipt_fsqm' ), 'https://wpquark.com/kb/fsqm/form-submission-related/available-format-strings-custom-notifications/' ); ?></p>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[user][update_sub]', __( 'Form Update Subject', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[user][update_sub]', $op['update_sub'], __( 'Enter the subject', 'ipt_fsqm' ), 'large' ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
				<p><?php _e( 'Enter the subject of the notification email if the user updates an existing submission. Following format strings are available.', 'ipt_fsqm' ); ?></p>
				<ul class="ul-square">
					<li><code>%FORMNAME%</code> : <?php _e( 'Replaced by the Form Name.', 'ipt_fsqm' ); ?></li>
					<li><code>%SITENAME%</code> : <?php _e( 'Replaced by the Site name.', 'ipt_fsqm' ); ?></li>
					<li><code>%FNAME%</code> : <?php _e( 'Replaced by the user\'s first name.', 'ipt_fsqm' ); ?></li>
					<li><code>%LNAME%</code> : <?php _e( 'Replaced by the user\'s last name.', 'ipt_fsqm' ); ?></li>
					<li><code>%PHONE%</code> : <?php _e( 'Replaced by the user\'s phone number.', 'ipt_fsqm' ); ?></li>
					<li><code>%EMAIL%</code> : <?php _e( 'Replaced by the user\'s email address.', 'ipt_fsqm' ); ?></li>
				</ul>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[user][update_msg]', __( 'Update Notification Message', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[user][update_msg]', $op['update_msg'], __( 'Enter the message', 'ipt_fsqm' ), 'large' ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
					<p><?php _e( 'Enter the message that you want to send to the user/surveyee on form updation. Paragraphs and line breaks will be added automatically. In addition you can also put custom HTML code. You can use a few format strings which will be replaced by their corresponding values.', 'ipt_fsqm' ) ?></p>
					<ul class="ul-square">
						<li><strong>%NAME%</strong> : <?php _e( 'Will be replaced by the full name of the user.', 'ipt_fsqm' ); ?></li>
						<li><strong>%TRACK_LINK%</strong> : <?php _e( 'Will be replaced by the raw link from where the user can see the status of his submission.', 'ipt_fsqm' ); ?></li>
						<li><strong>%TRACK%</strong> : <?php _e( 'Will be replaced by a "Click Here" button linked to the track page.', 'ipt_fsqm' ); ?></li>
						<li><strong>%SCORE%</strong> : <?php _e( 'Will be replaced by the score obtained/total score.', 'ipt_fsqm' ); ?></li>
						<li><strong>%OSCORE%</strong> : <?php _e( 'Will be replaced by the score obtained.', 'ipt_fsqm' ); ?></li>
						<li><strong>%MSCORE%</strong> : <?php _e( 'Will be replaced by the total score.', 'ipt_fsqm' ); ?></li>
						<li><strong>%SCOREPERCENT%</strong> : <?php _e( 'Will be replaced by the percentage score obtained.', 'ipt_fsqm' ); ?></li>
						<li><strong>%DESIGNATION%</strong> : <?php _e( 'If the score falls under a valid ranking range, then this will be replaced by the given designation title.', 'ipt_fsqm' ); ?></li>
						<li><strong>%DESIGNATIONMSG%</strong> : <?php _e( 'If the score falls under a valid ranking range, then this will be replaced by the given designation message.', 'ipt_fsqm' ); ?></li>
						<li><strong>%TRACK_ID%</strong> : <?php _e( 'Will be replaced by the Tracking ID of the submission which the user can enter in the track page.', 'ipt_fsqm' ); ?></li>
						<li><strong>%SUBMISSION_ID%</strong> : <?php _e( 'Will be replaced by the ID of the submission.', 'ipt_fsqm' ); ?></li>
						<li><strong>%PORTAL%</strong> : <?php _e( 'Will be replaced by the raw link of the user portal page from where registered users can see all their submissions.', 'ipt_fsqm' ); ?></li>
					</ul>
					<p><?php _e( 'If you are using the %TRACK_LINK% make sure you have placed <code>[ipt_fsqm_track]</code> on some page/post and have entered its ID in the settings section.', 'ipt_fsqm' ); ?></p>
					<p><?php printf( __( 'An updated list can always be found <a href="%1$s" target="_blank">here</a>.', 'ipt_fsqm' ), 'https://wpquark.com/kb/fsqm/form-submission-related/available-format-strings-custom-notifications/' ); ?></p>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[user][header]', __( 'Additional Email Header', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[user][header]', $op['header'], __( 'One per line', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'You can enter one custom header per line. This field accepts all format strings. Do note that, headers like <strong><code>Cc, Reply-To</code></strong> are already managed by FSQM and should be avoided.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[user][email_logo]', __( 'Email Logo', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->upload( 'settings[user][email_logo]', $op['email_logo'], '', __( 'Set Logo', 'ipt_fsqm' ), __( 'Choose Image', 'ipt_fsqm' ), __( 'Use Image', 'ipt_fsqm' ), '90%', '150px', 'auto' ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Set the logo image that will be used in the email sent to the user. Image size should be 150X28px and with transparent background.', 'ipt_fsqm' ) ) ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[user][top_line]', __( 'Show the header line', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[user][top_line]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['top_line']); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If enabled then this will show a header line with link to the trackback page. Keep this enabled to allow users to view the submission through browser.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[user][form_name]', __( 'Show form name', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[user][form_name]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['form_name']); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If enabled then this will show the name of the form in a headline manner.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[user][show_submission]', __( 'Attach Submission to user email', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[user][show_submission]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['show_submission']); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you want to attach the complete submission to the user email, then enable it here.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[user][view_online]', __( 'View Online Button', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[user][view_online]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['view_online']); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Show a button with view online text. It will be linked to the trackback page.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[user][view_online_text]', __( 'Button Text', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->text( 'settings[user][view_online_text]', $op['view_online_text'], '', 'large' ); ?></td>
			<td><?php $this->ui->help( __( 'The text of the button.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[user][footer_msg]', __( 'Email Footer Message', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->text( 'settings[user][footer_msg]', $op['footer_msg'], '', 'large' ); ?></td>
			<td><?php $this->ui->help( __( 'The footer message of the email. This is usually unscription link or instruction.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[user][smtp]', __( 'Use SMTP Emailing', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[user][smtp]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['smtp'], '1', false, true, array(
					'condid' => 'ipt_fsqm_form_settings_smtp_enc_type_wrap,ipt_fsqm_form_settings_smtp_host_wrap,ipt_fsqm_form_settings_smtp_port_wrap,ipt_fsqm_form_settings_smtp_username_wrap,ipt_fsqm_form_settings_smtp_password_wrap'
				) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you want to send email using SMTP method then enable it here and enter the settings.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_form_settings_smtp_enc_type_wrap">
			<th><?php $this->ui->generate_label( 'settings[user][smtp_config][enc_type]', __( 'Encryption Type', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->select( 'settings[user][smtp_config][enc_type]', array(
					array(
						'value' => '',
						'label' => __( 'None', 'ipt_fsqm' ),
					),
					array(
						'value' => 'ssl',
						'label' => __( 'SSL', 'ipt_fsqm' ),
					),
					array(
						'value' => 'tls',
						'label' => __( 'TLS', 'ipt_fsqm' ),
					),
				), $op['smtp_config']['enc_type'] ); ?>
			</td>
			<td><?php $this->ui->help( __( 'For most servers SSL is the recommended option.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_form_settings_smtp_host_wrap">
			<th><?php $this->ui->generate_label( 'settings[user][smtp_config][host]', __( 'SMTP Host', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->text( 'settings[user][smtp_config][host]', $op['smtp_config']['host'], __( 'eg: smtp.gmail.com', 'ipt_fsqm' ), 'large' ); ?></td>
			<td><?php $this->ui->help( __( 'Enter the host of your SMTP server.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_form_settings_smtp_port_wrap">
			<th><?php $this->ui->generate_label( 'settings[user][smtp_config][port]', __( 'SMTP port', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->spinner( 'settings[user][smtp_config][port]', $op['smtp_config']['port'], __( 'Port', 'ipt_fsqm' ) ); ?></td>
			<td><?php $this->ui->help( __( 'Enter the port of your SMTP server.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_form_settings_smtp_username_wrap">
			<th><?php $this->ui->generate_label( 'settings[user][smtp_config][username]', __( 'SMTP Username', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->text( 'settings[user][smtp_config][username]', $op['smtp_config']['username'], __( 'eg: smtp.gmail.com', 'ipt_fsqm' ), 'large' ); ?></td>
			<td><?php $this->ui->help( __( 'Enter the username you use to login.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<?php $password = $op['smtp_config']['password']; ?>
		<?php if ( $password != '' ) $password = $this->decrypt( $password ); ?>
		<tr id="ipt_fsqm_form_settings_smtp_password_wrap">
			<th><?php $this->ui->generate_label( 'settings[user][smtp_config][password]', __( 'SMTP password', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->password( 'settings[user][smtp_config][password]', $password, 'large' ); ?></td>
			<td><?php $this->ui->help( __( 'Enter the password you use to login. Please note that it is always encrypted before storing in database.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<?php do_action( 'ipt_eform_settings_user', $op, $this ); ?>
	</tbody>
</table>
		<?php
	}

	public function admin() {
		$op = $this->settings['admin'];
		$cond_email_data = array();
		foreach ( (array) $op['conditional'] as $item_key => $item ) {
			$new_cond_email_data = array();
			foreach ( $item as $data_key => $data ) {
				if ( 'logics' == $data_key ) {
					$new_cond_email_data[ $data_key ] = $data;
				} else {
					$new_cond_email_data[ $data_key ] = array( 'settings[admin][conditional][' . $item_key . '][' . $data_key . ']', $data, __( 'Required', 'ipt_fsqm' ) );
				}
			}
			if ( ! isset( $new_cond_email_data['logics'] ) ) {
				$new_cond_email_data['logics'] = array();
			}
			$cond_email_data[ $item_key ] = $new_cond_email_data;
		}

		$cond_email = array(
			'name_prefix' => 'settings[admin][conditional]',
			'configs' => array(),
			'cond_suffix' => 'logics',
			'cond_id' => 'eform_admin_cond_url_wrap',
			'data' => $cond_email_data,
		);
		$cond_email['configs'][0] = array(
			'label' => __( 'Email (Comma Separated)', 'ipt_fsqm' ),
			'type' => 'text',
			'size' => '100',
			'data' => array( 'settings[admin][conditional][__SDAKEY__][email]', '', __( 'Required', 'ipt_fsqm' ) ),
		);
?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[admin][email]', __( 'Admin Notification Email', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[admin][email]', $op['email'], __( 'Enter admin email', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the email address where the notification email will be sent. Make sure you have set anti-spam filter for wordpress@yourdomain.tld otherwise automated emails might go into spam folder.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr><th colspan="3"><?php $this->ui->generate_label( 'settings[admin][conditional]', __( 'Conditional Admin Email', 'ipt_fsqm' ) ); ?></th></tr>
		<tr><td colspan="3">
		<?php $this->build_conditional_config( $cond_email['name_prefix'], $cond_email['configs'], $cond_email['cond_suffix'], $cond_email['cond_id'], $cond_email['data'] ); ?>
		</td></tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[admin][from]', __( 'Admin Notification From Email', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[admin][from]', $op['from'], __( 'Enter from email', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you want to change the "FROM" for admin notification email, set an email address here. Make sure the email is under the same domain of your website. Otherwise it might get spammed.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[admin][from_name]', __( 'Admin Notification From Name', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[admin][from_name]', $op['from_name'], __( 'Enter sender name', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'By default the senders name is the website name. If you wish to change it, then please specify here.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[admin][header]', __( 'Additional Email Header', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[admin][header]', $op['header'], __( 'One per line', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'You can enter one custom header per line. This field accepts all format strings. Do note that, headers like <strong><code>cc, Reply-To</code></strong> are already managed by FSQM and should be avoided.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[admin][email_logo]', __( 'Email Logo', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->upload( 'settings[admin][email_logo]', $op['email_logo'], '', __( 'Set Logo', 'ipt_fsqm' ), __( 'Choose Image', 'ipt_fsqm' ), __( 'Use Image', 'ipt_fsqm' ), '90%', '150px', 'auto' ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Set the logo image that will be used in the email sent to the admin. Image size should be 150X28px and with transparent background.', 'ipt_fsqm' ) ) ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[admin][sub]', __( 'New Submission Notification Subject', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[admin][sub]', $op['sub'], __( 'Enter Subject Line', 'ipt_fsqm' ), 'fit', 'normal', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
					<p><?php _e( 'Enter a descriptive and short subject line. The following format strings are available.', 'ipt_fsqm' ); ?></p>
					<ul class="ul-square">
						<li><code>%FORMNAME%</code> : <?php _e( 'Replaced by the Form Name.', 'ipt_fsqm' ); ?></li>
						<li><code>%SITENAME%</code> : <?php _e( 'Replaced by the Site name.', 'ipt_fsqm' ); ?></li>
						<li><code>%FNAME%</code> : <?php _e( 'Replaced by the user\'s first name.', 'ipt_fsqm' ); ?></li>
						<li><code>%LNAME%</code> : <?php _e( 'Replaced by the user\'s last name.', 'ipt_fsqm' ); ?></li>
						<li><code>%PHONE%</code> : <?php _e( 'Replaced by the user\'s phone number.', 'ipt_fsqm' ); ?></li>
						<li><code>%EMAIL%</code> : <?php _e( 'Replaced by the user\'s email address.', 'ipt_fsqm' ); ?></li>
					</ul>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[admin][usub]', __( 'Update Notification Subject', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[admin][usub]', $op['usub'], __( 'Enter Subject Line', 'ipt_fsqm' ), 'fit', 'normal', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
					<p><?php _e( 'Enter a descriptive and short subject line. The following format strings are available.', 'ipt_fsqm' ); ?></p>
					<ul class="ul-square">
						<li><code>%FORMNAME%</code> : <?php _e( 'Replaced by the Form Name.', 'ipt_fsqm' ); ?></li>
						<li><code>%SITENAME%</code> : <?php _e( 'Replaced by the Site name.', 'ipt_fsqm' ); ?></li>
						<li><code>%FNAME%</code> : <?php _e( 'Replaced by the user\'s first name.', 'ipt_fsqm' ); ?></li>
						<li><code>%LNAME%</code> : <?php _e( 'Replaced by the user\'s last name.', 'ipt_fsqm' ); ?></li>
						<li><code>%PHONE%</code> : <?php _e( 'Replaced by the user\'s phone number.', 'ipt_fsqm' ); ?></li>
						<li><code>%EMAIL%</code> : <?php _e( 'Replaced by the user\'s email address.', 'ipt_fsqm' ); ?></li>
					</ul>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[admin][fsub]', __( 'Feedback Notification Subject', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[admin][fsub]', $op['fsub'], __( 'Enter Subject Line', 'ipt_fsqm' ), 'fit', 'normal', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
					<p><?php _e( 'Enter a descriptive and short subject line. The following format strings are available.', 'ipt_fsqm' ); ?></p>
					<ul class="ul-square">
						<li><code>%FORMNAME%</code> : <?php _e( 'Replaced by the Form Name.', 'ipt_fsqm' ); ?></li>
						<li><code>%SITENAME%</code> : <?php _e( 'Replaced by the Site name.', 'ipt_fsqm' ); ?></li>
						<li><code>%ENAME%</code> : <?php _e( 'Will be replaced by the title of the element.', 'ipt_fsqm' ); ?></li>
						<li><code>%FNAME%</code> : <?php _e( 'Replaced by the user\'s first name.', 'ipt_fsqm' ); ?></li>
						<li><code>%LNAME%</code> : <?php _e( 'Replaced by the user\'s last name.', 'ipt_fsqm' ); ?></li>
						<li><code>%PHONE%</code> : <?php _e( 'Replaced by the user\'s phone number.', 'ipt_fsqm' ); ?></li>
						<li><code>%EMAIL%</code> : <?php _e( 'Replaced by the user\'s email address.', 'ipt_fsqm' ); ?></li>
					</ul>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[admin][top_line]', __( 'Show the header line', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[admin][top_line]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['top_line']); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If enabled then this will show a header line with link to the admin management page.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[admin][summary_header]', __( 'Show form information on general admin email', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[admin][summary_header]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['summary_header']); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If enabled then this will show basic form information.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[admin][f_summary_header]', __( 'Show form information on feedback email', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[admin][f_summary_header]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['f_summary_header']); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If enabled then this will show basic form information.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[admin][user_info]', __( 'Show user information on general admin email', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[admin][user_info]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['user_info']); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If enabled then this will show basic user information.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[admin][f_user_info]', __( 'Show user information on feedback email', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[admin][f_user_info]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['f_user_info']); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If enabled then this will show basic user information.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[admin][body]', __( 'New Submission Notification Message', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[admin][body]', $op['body'], __( 'Enter message', 'ipt_fsqm' ), 'fit', 'normal', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter a descriptive admin notification message here. <code>%ADMINLINK%</code> will be replaced by administrative link for the submission.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[admin][ubody]', __( 'Submission Update Notification Message', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[admin][ubody]', $op['ubody'], __( 'Enter message', 'ipt_fsqm' ), 'fit', 'normal', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter a descriptive admin notification message here. <code>%ADMINLINK%</code> will be replaced by administrative link for the submission.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[admin][mail_submission]', __( 'Email Submission to Admin', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[admin][mail_submission]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['mail_submission'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Tick this, if you wish to send the full submission detail to the admin email', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[admin][send_from_user]', __( 'Email on behalf of User', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[admin][send_from_user]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['send_from_user'], '1', false, true, array(
					'condid' => 'ipt_fsqm_settings_admin_rto',
				) ); ?>
				<p class="description"><?php _e( 'will remove sensitive links from the email.', 'ipt_fsqm' ); ?></p>
			</td>
			<td>
				<?php $this->ui->help( __( 'Tick this, if you wish to receive the email on behalf of the user. Otherwise email is sent from the WordPress email.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_settings_admin_rto">
			<th><?php $this->ui->generate_label( 'settings[admin][reply_to_only]', __( 'Just add Reply-To', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[admin][reply_to_only]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['reply_to_only'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Instead of changing the FROM address, only add a reply-to header. This is useful for avoiding email blacklisting like <a href="https://yahoomail.tumblr.com/post/82426900353/yahoo-dmarc-policy-change-what-should-senders">Yahoo DMARC</a>.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[admin][footer]', __( 'Admin Notification Footer Message', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[admin][footer]', $op['footer'], __( 'Enter admin footer message', 'ipt_fsqm' ), 'fit', 'normal', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter a descriptive admin notification footer message here.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<?php do_action( 'ipt_eform_settings_admin', $op, $this ); ?>
	</tbody>
</table>
		<?php
	}

	public function limitation() {
		$op = $this->settings['limitation'];
		$login_select = array(
			0 => array(
				'label' => __( 'Show Login Form', 'ipt_fsqm' ),
				'value' => 'show_login',
			),
			1 => array(
				'label' => __( 'Redirect to the mentioned page', 'ipt_fsqm' ),
				'value' => 'redirect',
			),
		);
?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[limitation][email_limit]', __( 'Submission Limit Per Email', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->spinner( 'settings[limitation][email_limit]', $op['email_limit'], __( '0 to disable', 'ipt_fsqm' ), '0' ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Select the maximum number of submissions per email address. Leave 0 to disable this check.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[limitation][ip_limit]', __( 'Submission Limit Per IP Address', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->spinner( 'settings[limitation][ip_limit]', $op['ip_limit'], __( '0 to disable', 'ipt_fsqm' ), '0' ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Select the maximum number of submissions per IP address. Leave 0 to disable this check.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[limitation][user_limit]', __( 'Submission Limit Per Registered User', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->spinner( 'settings[limitation][user_limit]', $op['user_limit'], __( '0 to disable', 'ipt_fsqm' ), '0' ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Select the maximum number of submissions per registered user. Leave 0 to disable this check.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[limitation][ip_limit_msg]', __( 'IP Limitation Message', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[limitation][ip_limit_msg]', $op['ip_limit_msg'], __( 'Please enter a message', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter a message you want to show to when ip limit has been exceeded.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[limitation][user_limit_msg]', __( 'Message Shown to Registered Users', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[limitation][user_limit_msg]', $op['user_limit_msg'], __( 'Please enter a message', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter a message you want to show to registered users who has exceeded their limit. Use placeholder <code>%PORTAL_LINK%</code> to replace it by User Portal Page permalink.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[limitation][total_limit]', __( 'Total Submission Limit', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->spinner( 'settings[limitation][total_limit]', $op['total_limit'], __( '0 to disable', 'ipt_fsqm' ), '0' ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Select the maximum number of overall submissions for the form. Leave 0 to disable this check.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[limitation][total_msg]', __( 'Message Shown if Total Limit is enabled', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[limitation][total_msg]', $op['total_msg'], __( 'Please enter a message', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you want to show how many submissions are left on the top of your form, enter a message here. <code>%1$d</code> will be replaced by total available submissions whereas <code>%2$d</code> will be replaced by total number of submissions.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[limitation][total_limit_msg]', __( 'Message Shown to Exceeded Total Limit', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[limitation][total_limit_msg]', $op['total_limit_msg'], __( 'Please enter a message', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter a message you want to show to users when the limit has exceeded.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[limitation][cookie_limit]', __( 'Cookie Submission Limit', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->spinner( 'settings[limitation][cookie_limit]', $op['cookie_limit'], __( '0 to disable', 'ipt_fsqm' ), '0' ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Select the maximum number of submission per user session. A Cookie will be set on the client browser to prevent future submissions.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[limitation][cookie_limit_msg]', __( 'Message Shown to Exceeded Cookie Limit', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[limitation][cookie_limit_msg]', $op['cookie_limit_msg'], __( 'Please enter a message', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter a message you want to show to users when the cookie limit has exceeded.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[limitation][logged_in]', __( 'Only Logged In user can submit', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[limitation][logged_in]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['logged_in'], '1', false, true, array(
					'condID' => 'ipt_fsqm_settings_limitation_logged_in_fb_wrap,ipt_fsqm_settings_limitation_nlr_wrap',
				) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If enabled, then only logged in users can access the form. If the user is not logged in, then the fallback action is performed.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_settings_limitation_logged_in_fb_wrap">
			<th><?php $this->ui->generate_label( 'settings[limitation][logged_in_fallback]', __( 'What to do when user not logged in', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->select( 'settings[limitation][logged_in_fallback]', $login_select, $op['logged_in_fallback'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Please select what to do when the user is not logged in. Choosing Show Form will print a FSQM Styled (with the same theme as this form) login form. If you use some other login system, then you can redirect to that system page using the redirect option.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_settings_limitation_nlr_wrap">
			<th><?php $this->ui->generate_label( 'settings[limitation][non_logged_redirect]', __( 'Redirection URL', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[limitation][non_logged_redirect]', $op['non_logged_redirect'], __( 'Enter URL', 'ipt_fsqm' ), 'fit', 'normal', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the redirection URL. You can have the placeholder <code>_self_</code> which will be replaced by the current URL.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[limitation][logged_out]', __( 'Only Logged Out user can submit', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[limitation][logged_out]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['logged_out'], '1', false, true, array(
					'condID' => 'ipt_fsqm_settings_limitation_logged_out_msg_wrap',
				) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If enabled, then only logged out users can access the form. If the user is logged in, then the mentioned message is shown. Make sure to disable the option Only Logged In user can submit, otherwise the form wont be shown to anyone.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_settings_limitation_logged_out_msg_wrap">
			<th><?php $this->ui->generate_label( 'settings[limitation][logged_msg]', __( 'Message for Logged In Users', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[limitation][logged_msg]', $op['logged_msg'], __( 'Write Here', 'ipt_fsqm' ), 'fit', 'normal', array( 'code' ) ); ?>
				<br>
				<p class="description"><?php _e( 'Leave empty to silently discard the form.', 'ipt_fsqm' ); ?></p>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the message you want to show when the user is logged in. HTML is enabled. Leave empty to silently discard the form.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<!-- New Limitations v3.0.0 -->
		<tr>
			<th><?php $this->ui->generate_label( 'settings[limitation][interval_limit]', __( 'Submission Interval (Minutes)', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->spinner( 'settings[limitation][interval_limit]', $op['interval_limit'], __( '0 to disable', 'ipt_fsqm' ), '0' ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Set the minimum number of minutes the same user has to wait between successive submissions. This would have no effect if user limit is set to 1.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_settings_limitation_intrmsg_wrap">
			<th><?php $this->ui->generate_label( 'settings[limitation][interval_msg]', __( 'Interval Message', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[limitation][interval_msg]', $op['interval_msg'], __( 'Write Here', 'ipt_fsqm' ), 'fit', 'normal', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the message you want to show when the interval limit violates. HTML is enabled. <code>%1$s</code> is replaced by the remaining minutes user needs to wait.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[limitation][expiration_limit]', __( 'Form Expiration Date', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->datetimepicker( 'settings[limitation][expiration_limit]', $op['expiration_limit'], __( 'Disabled', 'ipt_fsqm' ), false ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you want the form to expire after a centain date, then set it here. Once a form is expired, new submissions can not be made.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_settings_limitation_exmsg_wrap">
			<th><?php $this->ui->generate_label( 'settings[limitation][expiration_msg]', __( 'Expiration Message', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[limitation][expiration_msg]', $op['expiration_msg'], __( 'Write Here', 'ipt_fsqm' ), 'fit', 'normal', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the message you want to show when the form expires. HTML is enabled.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[limitation][starting_limit]', __( 'Form Opening Date', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->datetimepicker( 'settings[limitation][starting_limit]', $op['starting_limit'], __( 'Disabled', 'ipt_fsqm' ), false ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you want the form to be accessible only after a centain date, then set it here. Before a form is open, new submissions can not be made.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_settings_limitation_sttitle_wrap">
			<th><?php $this->ui->generate_label( 'settings[limitation][starting_title]', __( 'Opening Box Title', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[limitation][starting_title]', $op['starting_title'], __( 'Write Here', 'ipt_fsqm' ), 'fit', 'normal', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the title you want to show before the form opens. HTML is enabled. A countdown timer will be added automatically.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_settings_limitation_stmsg_wrap">
			<th><?php $this->ui->generate_label( 'settings[limitation][starting_msg]', __( 'Opening Message', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[limitation][starting_msg]', $op['starting_msg'], __( 'Write Here', 'ipt_fsqm' ), 'fit', 'normal', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the message you want to show before the form opens. HTML is enabled. A countdown timer will be added automatically.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[limitation][no_edit_expiration]', __( 'Disable edit of existing submissions once expired', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[limitation][no_edit_expiration]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['no_edit_expiration'], '1', false, true ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If enabled, then once the form has expired, users would not be able to edit their existing submissions.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[limitation][submission_info]', __( 'Show Notice to users who has submitted previously', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[limitation][submission_info]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['submission_info'], '1', false, true, array(
					'condID' => 'ipt_fsqm_settings_limitation_submsg_wrap',
				) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If enabled, then user has submitted before, then a message would be shown. This would not have any effect if the user limit disables form submission for a user.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_settings_limitation_submsg_wrap">
			<th><?php $this->ui->generate_label( 'settings[limitation][submission_msg]', __( 'Previously Submitted Notice', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[limitation][submission_msg]', $op['submission_msg'], __( 'Write Here', 'ipt_fsqm' ), 'fit', 'normal', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the notice you want to show to the users who has previously submitted the form. HTML is enabled.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
	</tbody>
</table>
		<?php
	}

	public function buttons() {
		$op = $this->settings['buttons'];
?>
<table class="form-table">
	<tbody>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'settings[buttons][hidden]', __( 'Hide Button Toolbar', 'ipt_fsqm' ) ); ?>
			</th>
			<td>
				<?php $this->ui->toggle( 'settings[buttons][hidden]', '', '', $op['hidden'] ); ?>
				<span class="description"><?php _e( 'Useful when manually adding buttons using LINKS BUTTONS element.', 'ipt_fsqm' ); ?></span>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you disable, then make sure to add navigations buttons manually using LINKS BUTTONS element. You need to add them on every page.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[buttons][next]', __( 'Next Button Label', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[buttons][next]', $op['next'], __( 'Enter the label', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the label of the next button', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[buttons][prev]', __( 'Previous Button Label', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[buttons][prev]', $op['prev'], __( 'Enter the label', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the label of the previous button', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[buttons][submit]', __( 'Submit Button Label', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[buttons][submit]', $op['submit'], __( 'Enter the label', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the label of the submit button', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[buttons][supdate]', __( 'Update Button Label', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[buttons][supdate]', $op['supdate'], __( 'Enter the label', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the label of the update button', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[buttons][reset]', __( 'Reset Button Label', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[buttons][reset]', $op['reset'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the label of the reset button. It will be shown using an icon, with the label as title.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[buttons][reset_msg]', __( 'Reset Confirm Message', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[buttons][reset_msg]', $op['reset_msg'], __( 'Direct Reset', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the confirm message that is shown to the user before the reset happens.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[buttons][hide]', __( 'Hide Buttons instead of Disabling', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[buttons][hide]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['hide'] ); ?>
			</td>
			<td><?php $this->ui->help( __( 'By default if the previous/next/submit buttons are not needed then they are disabled. Enabling this option would hide them.', 'ipt_fsqm' ) ); ?></td>
		</tr>
	</tbody>
</table>
		<?php
		$this->build_conditional( 'settings[buttons]', $op['conditional'], __( 'Conditional Logic for Submit Button', 'ipt_fsqm' ) );
		$this->build_conditional( 'settings[buttons]', $op['conditional_next'], __( 'Conditional Logic for Next Button', 'ipt_fsqm' ), true, '[conditional_next]' );
	}

	public function save_progress() {
		$op = $this->settings['save_progress'];
		?>
<table class="form-table" style="margin-bottom: 0">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[save_progress][auto_save]', __( 'Auto Save Form Progress', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[save_progress][auto_save]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['auto_save'], '1', false, true, array(
					'condid' => 'eform_settings_fs_sp_as_wrap',
				) ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Enabling this will automatically save user inputs on the client (user\'s) machine. So, even if they close and want to resume later, it would be possible. Do note that auto saving of file uploads is not possible right now.', 'ipt_fsqm' ) ); ?></td>
		</tr>
	</tbody>
</table>
<table class="form-table" id="eform_settings_fs_sp_as_wrap">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[save_progress][show_restore]', __( 'Show Restore Message', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[save_progress][show_restore]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['show_restore'], '1', false, true, array(
					'condid' => 'ipt_fsqm_settings_sp_rh_wrap,ipt_fsqm_settings_sp_rm_wrap,ipt_fsqm_settings_sp_rr_wrap',
				) ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Enabling this would show a restore message to user. And it will also place a button with which user can reset the form.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_settings_sp_rh_wrap">
			<th><?php $this->ui->generate_label( 'settings[save_progress][restore_head]', __( 'Restore Title', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[save_progress][restore_head]', $op['restore_head'], __( 'Enter title', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'This title will be shown above the restore message.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_settings_sp_rm_wrap">
			<th><?php $this->ui->generate_label( 'settings[save_progress][restore_msg]', __( 'Restore Message', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[save_progress][restore_msg]', $op['restore_msg'], __( 'Enter message', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'The auto restore message that is shown to the user.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_settings_sp_rr_wrap">
			<th><?php $this->ui->generate_label( 'settings[save_progress][restore_reset]', __( 'Restore Reset Button', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[save_progress][restore_reset]', $op['restore_reset'], __( 'Button Text', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Clicking on this button would reset the form.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[save_progress][interval_save]', __( 'Interval Save', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[save_progress][interval_save]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['interval_save'], '1', false, true, array(
					'condid' => 'ipt_fsqm_settings_as_int_wrap,ipt_fsqm_settings_as_int_tt_wrap,ipt_fsqm_settings_as_int_tts_wrap',
				) ); ?>
			</td>
			<td><?php $this->ui->help( __( 'If your form is really large, then live auto-save can slow it down. In this case, please enable this option to save in an interval, instead of saving live.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_settings_as_int_wrap">
			<th><?php $this->ui->generate_label( 'settings[save_progress][interval]', __( 'Save Interval (Seconds)', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->spinner( 'settings[save_progress][interval]', $op['interval'], __( 'Seconds', 'ipt_fsqm' ), '0' ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Mention seconds after which the form would be saved automatically. If value is less than or equals zero, then form would not be saved automatically at all. It will only be saved if user clicks on the save button.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_settings_as_int_tt_wrap">
			<th><?php $this->ui->generate_label( 'settings[save_progress][interval_title]', __( 'Auto Save Button Title', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[save_progress][interval_title]', $op['interval_title'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Clicking this button would do a manual save triggered by the user. Leave empty to disable.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_settings_as_int_tts_wrap">
			<th><?php $this->ui->generate_label( 'settings[save_progress][interval_saved_title]', __( 'Form Saved Title', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[save_progress][interval_saved_title]', $op['interval_saved_title'], __( 'Saved title', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Message to show when button action was successful.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
	</tbody>
</table>
		<?php
	}

	public function format_options() {
		$op = $this->settings['format'];
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[format][math_format]', __( 'Add format strings for mathematical elements', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[format][math_format]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['math_format'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If enabled, then you will have format strings like <code>%MATH{id}%</code> where <code>{id}</code> is the key of the form element (visible in form builder). These format strings would be replaced by values of the mathematical elements. For example, <code>%MATH3%, %MATH14%</code> etc. Use the format string in success message and admin or user notifications.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<?php do_action( 'ipt_fsqm_admin_format_options', $this, $op ); ?>
	</tbody>
</table>
		<?php
	}

	public function submission() {
		$op = $this->settings['submission'];
?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[submission][no_auto_complete]', __( 'Prevent Form Auto Complete', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[submission][no_auto_complete]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['no_auto_complete'] ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Enabling this will prevent form field auto complete from previous entries and page refresh. This will impact all form elements globally and enabling it is not recommended.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[submission][url_track]', __( 'Track Submission from URL data', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[submission][url_track]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['url_track'], '1', false, true, array(
					'condid' => 'ipt_fsqm_submission_utk_wrap'
				) ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Enabling this will give you an option to share the form URL with optional query parameter like <code>http://path.to/form/?url_track_key=value</code>, where <code>value</code> will be stored as the URL track code. You can later filter submissions from the admin side.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_submission_utk_wrap">
			<th><?php $this->ui->generate_label( 'settings[submission][url_track_key]', __( 'Key Parameter of URL Tracking', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[submission][url_track_key]', $op['url_track_key'], __( 'URL Query Key Parameter', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Set the key of the URL tracking. Depending on this, you will need to work out the URL.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[submission][process_title]', __( 'Processing Title', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[submission][process_title]', $op['process_title'], __( 'Shown when ajax submission in progress', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'This title will be shown above the ajax bar during form submission.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[submission][success_title]', __( 'Success Title', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[submission][success_title]', $op['success_title'], __( 'Shown when successfully submitted', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'This title will be shown above the success message.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[submission][success_message]', __( 'Success Message', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[submission][success_message]', $op['success_message'], __( 'Fullbody message', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
				<p><?php _e( 'This message will be shown to the users when they submit the form.', 'ipt_fsqm' ); ?></p>
				<p><?php _e( 'While entering the Message, you have the following format strings available.', 'ipt_fsqm' ); ?></p>
				<ul class="ul-square">
					<li><strong>%NAME%</strong> : <?php _e( 'Will be replaced by the full name of the user.', 'ipt_fsqm' ); ?></li>
					<li><strong>%TRACK_LINK%</strong> : <?php _e( 'Will be replaced by the raw link from where the user can see the status of his submission.', 'ipt_fsqm' ); ?></li>
					<li><strong>%TRACK%</strong> : <?php _e( 'Will be replaced by a "Click Here" button linked to the track page.', 'ipt_fsqm' ); ?></li>
					<li><strong>%SCORE%</strong> : <?php _e( 'Will be replaced by the score obtained/total score.', 'ipt_fsqm' ); ?></li>
					<li><strong>%OSCORE%</strong> : <?php _e( 'Will be replaced by the score obtained.', 'ipt_fsqm' ); ?></li>
					<li><strong>%MSCORE%</strong> : <?php _e( 'Will be replaced by the total score.', 'ipt_fsqm' ); ?></li>
					<li><strong>%SCOREPERCENT%</strong> : <?php _e( 'Will be replaced by the percentage score obtained.', 'ipt_fsqm' ); ?></li>
					<li><strong>%DESIGNATION%</strong> : <?php _e( 'If the score falls under a valid ranking range, then this will be replaced by the given designation title.', 'ipt_fsqm' ); ?></li>
					<li><strong>%DESIGNATIONMSG%</strong> : <?php _e( 'If the score falls under a valid ranking range, then this will be replaced by the given designation message.', 'ipt_fsqm' ); ?></li>
					<li><strong>%TRACK_ID%</strong> : <?php _e( 'Will be replaced by the Tracking ID of the submission which the user can enter in the track page.', 'ipt_fsqm' ); ?></li>
					<li><strong>%SUBMISSION_ID%</strong> : <?php _e( 'Will be replaced by the ID of the submission.', 'ipt_fsqm' ); ?></li>
					<li><strong>%PORTAL%</strong> : <?php _e( 'Will be replaced by the raw link of the user portal page from where registered users can see all their submissions.', 'ipt_fsqm' ); ?></li>
				</ul>
				<p><?php _e( 'Please note that the designation related format string might only work if you have ranking system enabled and the user score falls under a valid ranking range. Head to Ranking System to use this feature.', 'ipt_fsqm' ); ?></p>
				<p><?php printf( __( 'An updated list can always be found <a href="%1$s" target="_blank">here</a>.', 'ipt_fsqm' ), 'https://wpquark.com/kb/fsqm/form-submission-related/available-format-strings-custom-notifications/' ); ?></p>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[submission][update_message]', __( 'Update Message', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[submission][update_message]', $op['update_message'], __( 'Fullbody message', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
				<p><?php _e( 'This message will be shown to the users when they update the submission.', 'ipt_fsqm' ); ?></p>
				<p><?php _e( 'While entering the Message, you have the following format strings available.', 'ipt_fsqm' ); ?></p>
				<ul class="ul-square">
					<li><strong>%NAME%</strong> : <?php _e( 'Will be replaced by the full name of the user.', 'ipt_fsqm' ); ?></li>
					<li><strong>%TRACK_LINK%</strong> : <?php _e( 'Will be replaced by the raw link from where the user can see the status of his submission.', 'ipt_fsqm' ); ?></li>
					<li><strong>%TRACK%</strong> : <?php _e( 'Will be replaced by a "Click Here" button linked to the track page.', 'ipt_fsqm' ); ?></li>
					<li><strong>%SCORE%</strong> : <?php _e( 'Will be replaced by the score obtained/total score.', 'ipt_fsqm' ); ?></li>
					<li><strong>%OSCORE%</strong> : <?php _e( 'Will be replaced by the score obtained.', 'ipt_fsqm' ); ?></li>
					<li><strong>%MSCORE%</strong> : <?php _e( 'Will be replaced by the total score.', 'ipt_fsqm' ); ?></li>
					<li><strong>%SCOREPERCENT%</strong> : <?php _e( 'Will be replaced by the percentage score obtained.', 'ipt_fsqm' ); ?></li>
					<li><strong>%DESIGNATION%</strong> : <?php _e( 'If the score falls under a valid ranking range, then this will be replaced by the given designation title.', 'ipt_fsqm' ); ?></li>
					<li><strong>%DESIGNATIONMSG%</strong> : <?php _e( 'If the score falls under a valid ranking range, then this will be replaced by the given designation message.', 'ipt_fsqm' ); ?></li>
					<li><strong>%TRACK_ID%</strong> : <?php _e( 'Will be replaced by the Tracking ID of the submission which the user can enter in the track page.', 'ipt_fsqm' ); ?></li>
					<li><strong>%SUBMISSION_ID%</strong> : <?php _e( 'Will be replaced by the ID of the submission.', 'ipt_fsqm' ); ?></li>
					<li><strong>%PORTAL%</strong> : <?php _e( 'Will be replaced by the raw link of the user portal page from where registered users can see all their submissions.', 'ipt_fsqm' ); ?></li>
				</ul>
				<p><?php _e( 'Please note that the designation related format string might only work if you have ranking system enabled and the user score falls under a valid ranking range. Head to Ranking System to use this feature.', 'ipt_fsqm' ); ?></p>
				<p><?php printf( __( 'An updated list can always be found <a href="%1$s" target="_blank">here</a>.', 'ipt_fsqm' ), 'https://wpquark.com/kb/fsqm/form-submission-related/available-format-strings-custom-notifications/' ); ?></p>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[submission][log_ip]', __( 'Log IP Address', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[submission][log_ip]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['log_ip'] ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Enable to log user\'s IP Address.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[submission][log_registered_user]', __( 'Log Registered Users', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[submission][log_registered_user]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['log_registered_user'] ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Enable to log registered user accounts during submission.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[submission][reset_on_submit]', __( 'Reset Form after submit', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[submission][reset_on_submit]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['reset_on_submit'], '1', false, true, array(
					'condid' => 'ipt_fsqm_sub_rd_wrap,ipt_fsqm_sub_rm_wrap',
				) ); ?>
			</td>
			<td><?php $this->ui->help( __( 'After submission, reset the form for successive submission. Will not work if redirection is also turned on.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_sub_rd_wrap">
			<th><?php $this->ui->generate_label( 'settings[submission][reset_delay]', __( 'Reset Delay (seconds)', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->spinner( 'settings[submission][reset_delay]', $op['reset_delay'], __( 'Instant reset', 'ipt_fsqm' ), '0' ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Number of seconds to wait before reseting and showing the form. If set to 0 or blanked out, it will be reset immediately. If you want to show a brief success message, then setting this to 10 is recommended.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_sub_rm_wrap">
			<th><?php $this->ui->generate_label( 'settings[submission][reset_msg]', __( 'Reset Message', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[submission][reset_msg]', $op['reset_msg'], __( 'Shown during reset delay', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'This will be shown beside the success message title. <code>%time%</code> will be replaced by a timer.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
	</tbody>
</table>
		<?php
	}

	public function ganalytics() {
		$op = $this->settings['ganalytics'];
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[ganalytics][enabled]', __( 'Enable Google Analytics Integration', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[ganalytics][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, array(
					'condid' => 'ipt_fsqm_ganalytics_wrap'
				) ); ?>
			</td>
			<td><?php $this->ui->help( __( 'If enabled then the form would integrate with Google Analytics Event Tracking.', 'ipt_fsqm' ) ); ?></td>
		</tr>
	</tbody>
</table>
<table class="form-table" id="ipt_fsqm_ganalytics_wrap">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[ganalytics][manual_load]', __( 'Let FSQM Load Google Analytics Source', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[ganalytics][manual_load]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['manual_load'], '1', false, true, array(
					'condid' => 'ipt_fsqm_ganalytics_ml_wrap,ipt_fsqm_ganalytics_ck_wrap'
				) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you are not using any sort of Google Analytics plugin (all plugins following latest analytics integration will work) and want FSQM to load the script, then enable it here. Otherwise FSQM assumes you have already loaded Analytics codes and initiated the <code>ga</code> tracking object. If you let FSQM create a tracker object, then it will be uniquely namespaced as <code>FSQM{form_id}</code>. So even if you have another Google Analytics tracker in your webpages, but want to differentiate tracking for FSQM, it will still work and would not break existing trackers.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_ganalytics_ml_wrap">
			<th><?php $this->ui->generate_label( 'settings[ganalytics][tracking_id]', __( 'Property (Tracking ID)', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[ganalytics][tracking_id]', $op['tracking_id'], __( 'UA-XXXXX-Y', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the Tracking ID or Property related to your Google Analytics account. If you do not know your property ID, you can use the <a target="_blank" href="https://ga-dev-tools.appspot.com/account-explorer/">Account Explorer</a> to find it.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_ganalytics_ck_wrap">
			<th><?php $this->ui->generate_label( 'settings[ganalytics][cookie]', __( 'Cookie Domain', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[ganalytics][cookie]', $op['cookie'], __( 'auto', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the <a href="https://developers.google.com/analytics/devguides/collection/analyticsjs/field-reference#cookieDomain" target="_blank">cookie domain</a>. If you are testing on localhost, set this to none.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
	</tbody>
</table>
<h3><?php _e( 'Custom Dimensions', 'ipt_fsqm' ); ?></h3>
<p><?php _e( 'Following dimension variables are sent when making relevant event trackings', 'ipt_fsqm' ); ?></p>
<ul class="ul-disc">
	<li><strong>dimension1</strong>: <?php _e( 'Would be element category (like Multiple Choice Questions).', 'ipt_fsqm' ); ?></li>
	<li><strong>dimension2</strong>: <?php _e( 'Would be element type (like radios, checkboxes, grading etc).', 'ipt_fsqm' ); ?></li>
	<li><strong>dimension3</strong>: <?php _e( 'Would be element key.', 'ipt_fsqm' ); ?></li>
	<li><strong>dimension4</strong>: <?php _e( 'Would be element calculated and stringified value.', 'ipt_fsqm' ); ?></li>
	<li><strong>dimension5</strong>: <?php _e( 'Would be page number completed in case of paginated/tabbed forms.', 'ipt_fsqm' ); ?></li>
</ul>
<p><?php _e( 'It is recommended that you also <a href="https://support.google.com/analytics/answer/2709829?hl=en" target="_blank">setup dimensions</a> with appropriate index and meaningful names.', 'ipt_fsqm' ); ?></p>
		<?php
	}

	public function redirect() {
		$items = array();
		$items[] = array(
			'value' => 'none',
			'label' => __( 'No Redirection', 'ipt_fsqm' ),
			'data' => array(
				'condid' => 'redirect_none',
			),
		);
		$items[] = array(
			'value' => 'flat',
			'label' => __( 'Flat Redirection', 'ipt_fsqm' ),
			'data' => array(
				'condid' => 'redirect_url,redirect_delay',
			),
		);
		$items[] = array(
			'value' => 'score',
			'label' => __( 'Score Based Redirection', 'ipt_fsqm' ),
			'data' => array(
				'condid' => 'redirect_url,redirect_delay,redirect_score',
			),
		);
		$items[] = array(
			'value' => 'conditional',
			'label' => __( 'Conditional Redirection', 'ipt_fsqm' ),
			'data' => array(
				'condid' => 'redirect_url,redirect_delay,redirect_condtional',
			),
		);
?>
<div class="ipt_uif_msg ipt_uif_float_right">
	<a href="javascript:;" class="ipt_uif_msg_icon" title="<?php _e( 'Redirection', 'ipt_fsqm' ); ?>"><i class="ipt-icomoon-live_help"></i></a>
	<div class="ipt_uif_msg_body">
		<p><?php _e( 'Please select the type of the redirection. Each redirection has it\'s own different sets of options.', 'ipt_fsqm' ); ?></p>
		<h3><?php _e( 'Redirection URL', 'ipt_fsqm' ); ?></h3>
		<ul class="ul-square">
			<li>
				<?php _e( 'The page will be redirected to the mentioned URL for flat redirection.', 'ipt_fsqm' ); ?>
			</li>
			<li>
				<?php _e( 'The Redirection URL for Score based redirection will be used if the score does not satisfy any of the conditions.', 'ipt_fsqm' ); ?>
			</li>
			<li>
				<?php _e( 'You can use the format string <code>%TRACKBACK%</code> to redirect the user to the results page. You need to have a valid trackback page set on the settings for this to work.', 'ipt_fsqm' ); ?>
			</li>
			<li>
				<?php _e( 'All format strings are available for URL redirection. Some of them are listed below.', 'ipt_fsqm' ); ?>
				<ul class="ul-disc">
					<li><code>%NAME%</code> : <?php _e( 'Replaced by the user\'s full name.', 'ipt_fsqm' ); ?></li>
					<li><code>%FNAME%</code> : <?php _e( 'Replaced by the user\'s first name.', 'ipt_fsqm' ); ?></li>
					<li><code>%LNAME%</code> : <?php _e( 'Replaced by the user\'s last name.', 'ipt_fsqm' ); ?></li>
					<li><code>%PHONE%</code> : <?php _e( 'Replaced by the user\'s phone number.', 'ipt_fsqm' ); ?></li>
					<li><code>%EMAIL%</code> : <?php _e( 'Replaced by the user\'s email address.', 'ipt_fsqm' ); ?></li>
					<li><code>%ID%</code> : <?php _e( 'Replaced by the submission ID.', 'ipt_fsqm' ); ?></li>
					<li><code>%TRACK_ID%</code> : <?php _e( 'Replaced by the system generated trackback id.', 'ipt_fsqm' ); ?></li>
					<li><code>%SCORE%</code> : <?php _e( 'Replaced by the score obtained.', 'ipt_fsqm' ); ?></li>
					<li><code>%TSCORE%</code> : <?php _e( 'Replaced by the total score of the form.', 'ipt_fsqm' ); ?></li>
					<li><code>%SCOREPERCENT%</code> : <?php _e( 'Replaced by the percentage score obtained, formatted properly in your locale.', 'ipt_fsqm' ); ?></li>
					<li><code>%DESIGNATION%</code> : <?php _e( 'Replaced by the assigned designation.', 'ipt_fsqm' ); ?></li>
				</ul>
				<?php _e( 'For a more comprehensive list, check our <a href="https://wpquark.com/kb/fsqm/form-submission-related/available-format-strings-custom-notifications/" target="_blank" rel="noopener">knowledgebase</a>. Also you can purchase easySubmission add-on to pass any form element as URL parameters.' ) ?>
			</li>
		</ul>
		<h3><?php _e( 'Score Range', 'ipt_fsqm' ); ?></h3>
		<ul class="ul-square">
			<li>
				<?php _e( 'Select the range of the score (in terms of percentage, which will be calculated automatically) and mentioned the redirection URL.', 'ipt_fsqm' ); ?>
			</li>
			<li>
				<?php _e( 'All the ranges are inclusive.', 'ipt_fsqm' ); ?>
			</li>
			<li>
				<?php _e( 'If more than one range overlaps for a score, then the first range found in the list will be used.', 'ipt_fsqm' ); ?>
			</li>
		</ul>
	</div>
</div>
		<?php
		echo '<div class="align-center">';
		$this->ui->radios( 'settings[redirection][type]', $items, $this->settings['redirection']['type'], false, true );
		echo '</div>';

		$this->ui->div( 'clear', array( $this->ui, 'clear' ), 0, 'redirect_none' );
		$this->ui->shadowbox( array( 'glowy', 'cyan' ), array( $this, 'redirect_url' ), 0, 'redirect_url' );
		$this->ui->shadowbox( array( 'glowy', 'cyan' ), array( $this, 'redirect_delay' ), 0, 'redirect_delay' );
		$this->ui->div( '', array( $this, 'redirect_score' ), 0, 'redirect_score' );
		$this->ui->div( '', array( $this, 'redirect_conditional' ), 0, 'redirect_condtional' );
	}

	public function redirect_conditional() {
		$op = $this->settings['redirection']['conditional'];
		// Conditional Product ID
		//build_conditional_config( $name_prefix, $configs, $cond_suffix, $cond_id, $data )
		$cond_redr_data = array();
		foreach ( (array) $op as $item_key => $item ) {
			$new_cond_redr_data = array();
			foreach ( $item as $data_key => $data ) {
				if ( 'logics' == $data_key ) {
					$new_cond_redr_data[ $data_key ] = $data;
				} else {
					$new_cond_redr_data[ $data_key ] = array( 'settings[redirection][conditional][' . $item_key . '][' . $data_key . ']', $data, __( 'Required', 'ipt_fsqm' ) );
				}
			}
			if ( ! isset( $new_cond_redr_data['logics'] ) ) {
				$new_cond_redr_data['logics'] = array();
			}
			$cond_redr_data[ $item_key ] = $new_cond_redr_data;
		}

		$cond_redr = array(
			'name_prefix' => 'settings[redirection][conditional]',
			'configs' => array(),
			'cond_suffix' => 'logics',
			'cond_id' => 'eform_redirection_cond_url_wrap',
			'data' => $cond_redr_data,
		);
		$cond_redr['configs'][0] = array(
			'label' => __( 'URL', 'ipt_fsqm' ),
			'type' => 'text',
			'size' => '100',
			'data' => array( 'settings[redirection][conditional][__SDAKEY__][url]', '', __( 'Required', 'ipt_fsqm' ) ),
		);

		$this->build_conditional_config( $cond_redr['name_prefix'], $cond_redr['configs'], $cond_redr['cond_suffix'], $cond_redr['cond_id'], $cond_redr['data'] );
	}

	public function redirect_delay() {
?>
	<table class="form-table">
		<tbody>
			<tr>
				<th><?php $this->generate_label( 'settings[redirection][delay]', __( 'Redirection Delay', 'ipt_fsqm' ) ); ?></th>
				<td>
					<?php $this->ui->slider( 'settings[redirection][delay]', $this->settings['redirection']['delay'], 0, 10000, 100 ); ?>
				</td>
				<td>
					<?php $this->ui->help( __( 'Select the delay to the redirection in milliseconds. A value somewhere between 1000 and 5000 is recommended.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
			<tr>
				<th><?php $this->generate_label( 'settings[redirection][message]', __( 'Custom Message', 'ipt_fsqm' ) ); ?></th>
				<td>
					<?php $this->ui->textarea( 'settings[redirection][message]', $this->settings['redirection']['message'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
				</td>
				<td>
					<?php $this->ui->help( __( 'Set a custom message that will be shown below any notification. <code>%LINK%</code> will be replaced by the redirection link and <code>%TIME%</code> will be replaced by time (in seconds). Leave empty to disable this feature.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
			<tr>
				<th><?php $this->ui->generate_label( 'settings[redirection][top]', __( 'Redirect Parent from iFrame', 'ipt_fsqm' ) ); ?></th>
				<td>
					<?php $this->ui->toggle( 'settings[redirection][top]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $this->settings['redirection']['top'] ); ?>
				</td>
				<td><?php $this->ui->help( __( 'If you are planning to load this form inside iFrame, then enabling this option will redirect the parent page, not just the iFrame. Useful to put sidebar widgets as iframe.', 'ipt_fsqm' ) ); ?></td>
			</tr>
		</tbody>
	</table>
		<?php
	}

	public function redirect_url() {
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[redirection][url]', __( 'Redirection URL', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[redirection][url]', $this->settings['redirection']['url'], __( 'https://', 'ipt_fsqm' ), 'large' ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Enter redirection URL here. <code>%TRACKBACK%</code> will be replaced by the trackback/view submission URL.', 'ipt_fsqm' ) ); ?></td>
		</tr>
	</tbody>
</table>
		<?php

	}

	public function redirect_score() {
		$r_select = array();
		$r_select[] = array(
			'value' => 'percentage',
			'label' => __( 'Percentage Score', 'ipt_fsqm' ),
			'data' => array(
				'condid' => 'redirect_percentage',
			),
		);
		$r_select[] = array(
			'value' => 'raw',
			'label' => __( 'Total Score', 'ipt_fsqm' ),
			'data' => array(
				'condid' => 'redirect_raw',
			),
		);
		$settings = array(
			'columns' => array(
				0 => array(
					'label' => __( 'Score Range', 'ipt_fsqm' ),
					'size' => '60',
					'type' => 'slider_range',
				),
				1 => array(
					'label' => __( 'Redirect URL', 'ipt_fsqm' ),
					'size' => '40',
					'type' => 'text',
				),
			),
			'labels' => array(
				'add' => __( 'Add New Range', 'ipt_fsqm' ),
			),
		);
		$items = array();
		$max_key = null;
		foreach ( $this->settings['redirection']['score'] as $s_key => $score ) {
			$max_key = max( array( $max_key, $s_key ) );
			$items[] = array(
				0 => array( 'settings[redirection][score][' . $s_key . ']', array( $score['min'], $score['max'] ), 0, 100.001, 0.01, '%' ),
				1 => array( 'settings[redirection][score][' . $s_key . '][url]', $score['url'], __( 'Enter the Redirect URL', 'ipt_fsqm' ), 'large' ),
			);
		}
		$data = array(
			0 => array( 'settings[redirection][score][__SDAKEY__]', array( 10, 80 ), 0, 100.001, 0.01, '%' ),
			1 => array( 'settings[redirection][score][__SDAKEY__][url]', '', __( 'Enter the Redirect URL', 'ipt_fsqm' ), 'large' ),
		);

		$r_settings = array(
			'columns' => array(
				0 => array(
					'label' => __( 'Score From', 'ipt_fsqm' ),
					'size' => '30',
					'type' => 'spinner',
				),
				1 => array(
					'label' => __( 'Score To', 'ipt_fsqm' ),
					'size' => '30',
					'type' => 'spinner',
				),
				2 => array(
					'label' => __( 'Redirect URL', 'ipt_fsqm' ),
					'size' => '40',
					'type' => 'text',
				),
			),
			'labels' => array(
				'add' => __( 'Add New Range', 'ipt_fsqm' ),
			),
		);
		$r_items = array();
		$r_max_key = null;
		foreach ( $this->settings['redirection']['rscore'] as $rs_key => $rscore ) {
			$r_max_key = max( array( $r_max_key, $rs_key ) );
			$r_items[] = array(
				0 => array( 'settings[redirection][rscore][' . $rs_key . '][min]', $rscore['min'], __( 'Min Score', 'ipt_fsqm' ), 0 ),
				1 => array( 'settings[redirection][rscore][' . $rs_key . '][max]', $rscore['max'], __( 'Max Score', 'ipt_fsqm' ), 0 ),
				2 => array( 'settings[redirection][rscore][' . $rs_key . '][url]', $rscore['url'], __( 'Enter the Redirect URL', 'ipt_fsqm' ), 'large' ),
			);
		}
		$r_data = array(
			0 => array( 'settings[redirection][rscore][__SDAKEY__][min]', '', __( 'Min Score', 'ipt_fsqm' ), 0 ),
			1 => array( 'settings[redirection][rscore][__SDAKEY__][max]', '', __( 'Max Score', 'ipt_fsqm' ), 0 ),
			2 => array( 'settings[redirection][rscore][__SDAKEY__][url]', '', __( 'Enter the Redirect URL', 'ipt_fsqm' ), 'large' ),
		);

		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[redirection][rtype]', __( 'Redirection Based On', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->select( 'settings[redirection][rtype]', $r_select, $this->settings['redirection']['rtype'], false, true ); ?></td>
			<td><?php $this->ui->help( __( 'Here you can set the redirection based on either percentage obtained or total score obtained.', 'ipt_fsqm' ) ); ?></td>
		</tr>
	</tbody>
</table>
		<?php
		$this->ui->sda_list( $settings, $items, $data, $max_key, 'redirect_percentage' );
		$this->ui->sda_list( $r_settings, $r_items, $r_data, $r_max_key, 'redirect_raw' );
	}

	public function theme() {
		$op = $this->settings['theme'];
		$web_fonts = $this->get_available_webfonts();
		$themes = $this->get_available_themes();
		// Get the custom options
		$custom_options = array();
		$callback_cache = array();
		foreach ( $themes as $theme_grp ) {
			foreach ( $theme_grp['themes'] as $theme_key => $theme ) {
				if ( isset( $theme['has_option'] ) && true == $theme['has_option'] ) {
					if ( in_array( $theme['option_container'], $callback_cache ) ) {
						$custom_options[ $theme_key ] = array( $theme['option_container'] );
						continue;
					}
					$callback_cache[] = $theme['option_container'];
					$custom_options[ $theme_key ] = array( $theme['option_container'], $theme['option_callback'] );
				}
			}
		}
		$svg_location = IPT_EFORM_ABSPATH . 'static/admin/images/builder-icons/';
		$svg_boxy = file_get_contents( $svg_location . 'boxy.svg' );
		$svg_material = file_get_contents( $svg_location . 'material.svg' );

		$svg_inherit = file_get_contents( $svg_location . 'inherit.svg' );
		$svg_vertical = file_get_contents( $svg_location . 'vertical.svg' );
		$svg_horizontal = file_get_contents( $svg_location . 'horizontal.svg' );
		$svg_centered = file_get_contents( $svg_location . 'centered.svg' );

		$element_styles = [
			[
				'value' => 'material',
				'label' => __( 'Material', 'ipt_fsqm' ),
				'svg' => $svg_material,
			],
			[
				'value' => 'boxy',
				'label' => __( 'Boxy', 'ipt_fsqm' ),
				'svg' => $svg_boxy,
			],
		];

		$align_overrides = [
			[
				'value' => 'inherit',
				'label' => __( 'Inherit', 'ipt_fsqm' ),
				'svg' => $svg_inherit,
			],
			[
				'value' => 'vertical',
				'label' => __( 'Vertical', 'ipt_fsqm' ),
				'svg' => $svg_vertical,
			],
			[
				'value' => 'centered',
				'label' => __( 'Centered', 'ipt_fsqm' ),
				'svg' => $svg_centered,
			],
			[
				'value' => 'horizontal',
				'label' => __( 'Horizontal', 'ipt_fsqm' ),
				'svg' => $svg_horizontal,
			],
		];

		$font_sources = [
			[
				'value' => 'system',
				'label' => __( 'System Font', 'ipt_fsqm' ),
				'data' => [
					'condid' => '',
				],
			],
			[
				'value' => 'google_webfont',
				'label' => __( 'Google WebFont', 'ipt_fsqm' ),
				'data' => [
					'condid' => 'ipt_fsqm_form_settings_theme_style_head_font_wrap,ipt_fsqm_form_settings_theme_style_body_font_wrap',
				],
			],
			[
				'value' => 'custom',
				'label' => __( 'Custom', 'ipt_fsqm' ),
				'data' => [
					'condid' => 'eform-style-settings-cfont-head,eform-style-settings-cfont-body',
				],
			],
		];

		$custom_font_options = [
			[
				'name' => 'settings[theme][style][head_custom]',
				'label' => __( 'Heading Font Name', 'ipt_fsqm' ),
				'ui' => 'text',
				'id' => 'eform-style-settings-cfont-head',
				'param' => [ 'settings[theme][style][head_custom]', $op['style']['head_custom'], __( 'CSS Font Family', 'ipt_fsqm' ) ],
				'help' => __( 'Specify the font-family for the heading texts.', 'ipt_fsqm' ),
			],
			[
				'name' => 'settings[theme][style][body_custom]',
				'label' => __( 'Body Font Name', 'ipt_fsqm' ),
				'id' => 'eform-style-settings-cfont-body',
				'ui' => 'text',
				'param' => [ 'settings[theme][style][body_custom]', $op['style']['body_custom'], __( 'CSS Font Family', 'ipt_fsqm' ) ],
				'help' => __( 'Specify the font-family for the heading texts.', 'ipt_fsqm' ),
			],
		];
?>
<table class="form-table">
	<tbody>
		<tr>
			<th style="width: 150px;"><?php $this->ui->generate_label( 'settings[theme][template]', __( 'Select Theme', 'ipt_fsqm' ) ); ?></th>
			<td>
				<div class="ipt_uif_conditional_select">
					<select name="settings[theme][template]" id="<?php echo $this->generate_id_from_name( 'settings[theme][template]' ); ?>" class="ipt_uif_select ipt_uif_theme_selector">
						<?php foreach ( $themes as $theme_grp ) : ?>
						<optgroup label="<?php echo $theme_grp['label']; ?>">
							<?php foreach ( $theme_grp['themes'] as $theme_key => $theme ) : ?>
							<option data-colors="<?php echo esc_attr( isset( $theme['colors'] ) ? json_encode( $theme['colors'] ) : json_encode( array() ) ); ?>" value="<?php echo $theme_key; ?>"<?php if ( $op['template'] == $theme_key ) echo ' selected="selected"'; ?><?php echo ( isset( $custom_options[ $theme_key ] ) ? 'data-condID="' . esc_attr( $custom_options[ $theme_key ][0] ) . '"' : '' ); ?>><?php echo $theme['label']; ?></option>
							<?php endforeach; ?>
						</optgroup>
						<?php endforeach; ?>
					</select>
					<div class="ipt_uif_theme_preview"></div>
				</div>
			</td>
			<td style="width: 50px;">
				<?php $this->ui->help( __( 'Select a theme for this form.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<?php foreach ( $custom_options as $theme_key => $option_cb ) : ?>
					<?php
					if ( ! isset( $custom_options[ $theme_key ][1] ) ) {
						continue;
					}
					?>
					<div id="<?php echo esc_attr( $custom_options[ $theme_key ][0] ) ?>">
						<?php call_user_func( $custom_options[ $theme_key ][1], $this ); ?>
					</div>
				<?php endforeach; ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[theme][element_style]', __( 'Element Style', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->svg_picker( 'settings[theme][element_style]', $element_styles, $op['element_style'], false ); ?></td>
			<td>
				<?php $this->ui->help( __( 'Set element style.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[theme][align_override]', __( 'Global Alignment', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->svg_picker( 'settings[theme][align_override]', $align_overrides, $op['align_override'], false ); ?></td>
			<td>
				<?php $this->ui->help( __( 'Override form alignment. If set to inherit, then you can control elements individually, otherwise it will override.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th style="width: 150px"><?php $this->ui->generate_label( 'settings[theme][logo]', __( 'Add a header image/logo', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->upload( 'settings[theme][logo]', $op['logo'], '', __( 'Set Header', 'ipt_fsqm' ), __( 'Choose Image', 'ipt_fsqm' ), __( 'Use Image', 'ipt_fsqm' ), '90%', '150px', 'auto' ); ?>
			</td>
			<td style="width: 50px">
				<?php $this->ui->help( __( 'You can put a logo or header image right before the form if you want. This will shown on the form page, trackback page, emails and also on standalone pages.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th style="width: 150px;"><?php $this->ui->generate_label( 'settings[theme][waypoint]', __( 'Animated Form Elements', 'ipt_fsqm' ) ); ?></th>
			<td>
			<?php $this->ui->toggle( 'settings[theme][waypoint]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['waypoint'], '1' ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Select Yes to create an animating form with CSS3 animation. The form elements will fade and slide once they enter the viewport. Great way to attract user attention.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th style="width: 150px;"><?php $this->ui->generate_label( 'settings[theme][icon][color]', __( 'Icon Color', 'ipt_fsqm' ) ); ?></th>
			<td>
			<?php $this->ui->colorpicker( 'settings[theme][icon][color]', $op['icon']['color'], '', '#333333' ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Set the icon color for summary table.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th style="width: 150px;"><?php $this->ui->generate_label( 'settings[theme][style][custom_font]', __( 'Customize Fonts', 'ipt_fsqm' ) ); ?></th>
			<td>
			<?php $this->ui->toggle( 'settings[theme][style][custom_font]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['style']['custom_font'], '1', false, true, array(
				'condid' => 'eform-style-settings-all-fonts'
			) ); ?>
			</td>
			<td style="width: 50px;">
				<?php $this->ui->help( __( 'If you wish then you can change fonts and also put your own css code.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_form_settings_theme_style_fonts_wrap">
			<td colspan="3">
				<table class="form-table">
					<tbody>

						<tr id="eform-style-settings-all-fonts">
							<td colspan="3">
								<table class="form-table">
									<tbody>
										<tr>
											<th><?php $this->ui->generate_label( 'settings[theme][style][font_source]', __( 'Font Source', 'ipt_fsqm' ) ); ?></th>
											<td>
												<?php
												$this->ui->select( 'settings[theme][style][font_source]', $font_sources, $op['style']['font_source'], false, true );
												?>
											</td>
											<td>
												<?php $this->ui->help( __( 'Select the font source. If you select custom, then you have to make sure the font is present.', 'ipt_fsqm' ) ); ?>
											</td>
										</tr>
										<tr id="ipt_fsqm_form_settings_theme_style_head_font_wrap">
											<th style="width: 150px;"><?php $this->ui->generate_label( 'settings[theme][style][head_font]', __( 'Heading Font', 'ipt_fsqm' ) ); ?></th>
											<td>
												<?php $this->ui->webfonts( 'settings[theme][style][head_font]', $op['style']['head_font'], $web_fonts ); ?>
											</td>
											<td style="width: 50px;">
												<?php $this->ui->help( __( 'Select the font.', 'ipt_fsqm' ) ); ?>
											</td>
										</tr>
										<tr id="ipt_fsqm_form_settings_theme_style_body_font_wrap">
											<th style="width: 150px;"><?php $this->ui->generate_label( 'settings[theme][style][body_font]', __( 'Body Font', 'ipt_fsqm' ) ); ?></th>
											<td>
												<?php $this->ui->webfonts( 'settings[theme][style][body_font]', $op['style']['body_font'], $web_fonts ); ?>
											</td>
											<td style="width: 50px;">
												<?php $this->ui->help( __( 'Select the font.', 'ipt_fsqm' ) ); ?>
											</td>
										</tr>
										<?php $this->ui->form_table( $custom_font_options, false ); ?>
										<tr id="ipt_fsqm_form_settings_theme_style_font_size_wrap">
											<th><?php $this->ui->generate_label( 'settings[theme][style][base_font_size]', __( 'Base Font Size', 'ipt_fsqm' ) ); ?></th>
											<td>
												<?php $this->ui->slider( 'settings[theme][style][base_font_size]', $op['style']['base_font_size'], 10, 20 ); ?>
											</td>
											<td style="width: 50px;">
												<?php $this->ui->help( __( 'Select the base font size. Rest of the sizes will be calculated automatically.', 'ipt_fsqm' ) ); ?>
											</td>
										</tr>
										<tr id="ipt_fsqm_form_settings_theme_style_font_typo_wrap">
											<th><?php $this->ui->generate_label( '', __( 'Heading Font Customization', 'ipt_fsqm' ) ); ?></th>
											<td>
												<?php $this->ui->checkbox( 'settings[theme][style][head_font_typo][bold]', array(
													'label' => __( '<strong>Bold</strong>', 'ipt_fsqm' ),
													'value' => '1',
												), $op['style']['head_font_typo']['bold'] ); ?>
												<div class="clear"></div>
												<?php $this->ui->checkbox( 'settings[theme][style][head_font_typo][italic]', array(
													'label' => __( '<em>Italic</em>', 'ipt_fsqm' ),
													'value' => '1',
												), $op['style']['head_font_typo']['italic'] ); ?>
											</td>
											<td><?php $this->ui->help( __( 'Make the heading fonts bold or italic.', 'ipt_fsqm' ) ); ?></td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
		<tr>
			<th style="width: 150px;"><?php $this->ui->generate_label( 'settings[theme][custom_style]', __( 'Add Custom CSS', 'ipt_fsqm' ) ); ?></th>
			<td>
			<?php $this->ui->toggle( 'settings[theme][custom_style]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['custom_style'], '1', false, true, array(
				'condid' => 'ipt_fsqm_form_settings_theme_style_custom_wrap'
			) ); ?>
			</td>
			<td style="width: 50px;">
				<?php $this->ui->help( __( 'If you wish then you can change fonts and also put your own css code.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_form_settings_theme_style_custom_wrap">
			<th><?php $this->ui->generate_label( 'settings[theme][style][custom]', __( 'Custom CSS', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->textarea( 'settings[theme][style][custom]', $op['style']['custom'], __( 'CSS Code', 'ipt_fsqm' ), 'widefat', array( 'code' ) ); ?></td>
			<td><?php $this->ui->help( __( 'If you are an advanced user and would like to put your own CSS, then this is where you can do so. Please consider having a CSS scope of <code>body #ipt_fsqm_form_wrap_{form_id}</code> to modify only this form.', 'ipt_fsqm' ) ); ?></td>
		</tr>
	</tbody>
</table>
		<?php
	}

	public function payment() {
		$hor_tabs = array();
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_payment_general',
			'label' => __( 'General Settings', 'ipt_fsqm' ),
			'callback' => array( $this, 'payment_general' ),
		);
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_payment_coupon',
			'label' => __( 'Discount Coupons', 'ipt_fsqm' ),
			'callback' => array( $this, 'payment_coupon' ),
		);
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_payment_paypal',
			'label' => __( 'Paypal Settings', 'ipt_fsqm' ),
			'callback' => array( $this, 'payment_paypal' ),
		);
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_payment_stripe',
			'label' => __( 'Stripe Settings', 'ipt_fsqm' ),
			'callback' => array( $this, 'payment_stripe' ),
		);
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_payment_authorize',
			'label' => __( 'Authorize.net Settings', 'ipt_fsqm' ),
			'callback' => array( $this, 'payment_authorize' ),
		);
		$hor_tabs[] = [
			'id' => 'ipt_fsqm_payment_offline',
			'label' => __( 'Offline Settings', 'ipt_fsqm' ),
			'callback' => [ $this, 'payment_offline' ],
		];
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_woocommerce',
			'label' => __( 'WooCommerce', 'ipt_fsqm' ),
			'callback' => array( $this, 'woocommerce' ),
		);
		$hor_tabs[] = array(
			'id' => 'ipt_fsqm_settings_estimation',
			'label' => __( 'Estimation Slider', 'ipt_fsqm' ),
			'callback' => array( $this, 'estimation_slider' ),
		);

		$hor_tabs = apply_filters( 'ipt_fsqm_payment_settings_tabs', $hor_tabs, $this );

		$this->ui->tabs( $hor_tabs, false, true );
	}

	public function payment_general() {
		$op = $this->settings['payment'];

		// Payment items
		$payment_items = [];
		$payment_name_pf = 'settings[payment]';
		// Payment type
		$payment_type_items = [
			0 => [
				'value' => 'onetime',
				'label' => __( 'One Time Payment', 'ipt_fsqm' ),
				'data' => [
					'condid' => '',
				],
			],
			1 => [
				'value' => 'recurring',
				'label' => __( 'Recurring / Subscription', 'ipt_fsqm' ),
				'data' => [
					'condid' => 'eform_pm_gn_rs_wrap',
				],
			],
		];
		$payment_items[] = [
			'name' => $payment_name_pf . '[payment_type]',
			'label' => __( 'Billing Type', 'ipt_fsqm' ),
			'ui' => 'select',
			'param' => [ $payment_name_pf . '[payment_type]', $payment_type_items, $op['payment_type'], false, true ],
			'description' => __( 'For recurring billing, subscription settings needs to be enabled and configured within the payment gateway settings. For now only stripe is supported.', 'ipt_fsqm' ),
			'help' => __( 'Onetime is the regular billing where customers are charged only once. Recurring or subscription is when customers are charged based on given frequency. The settings and frequency of the charge has to be configured individually within the payment gateway settings. This is because different gateway provides different implementation of subscription and to provide maximum functionality, each needs to be configured differently.', 'ipt_fsqm' ),
		];
		// Recurring Statement
		$payment_items[] = [
			'name' => $payment_name_pf . '[recur_statement]',
			'label' => __( 'Invoice Statement for Susbcription', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => [ $payment_name_pf . '[recur_statement]', $op['recur_statement'], __( 'Required', 'ipt_fsqm' ) ],
			'help' => __( 'Set the text which will be shown on the invoice. <code>%1$d</code> is replaced by frequency and <code>%2$s</code> is replaced by interval.', 'ipt_fsqm' ),
			'id' => 'eform_pm_gn_rs_wrap',
		];

		$payment_gateways = IPT_FSQM_Form_Elements_Static::ipt_fsqm_get_payment_gateways();
		$types = array();
		foreach ( $payment_gateways as $pg_key => $pg_val ) {
			$types[] = array(
				'value' => $pg_key,
				'label' => $pg_val,
			);
		}
		// Add notice if not using SSL
		if ( ! is_ssl() ) {
			$this->ui->msg_error( __( 'For payment gateway implementations, you need to have SSL certificate and access this site through <code>https</code>. You may get it for free from <a href="https://letsencrypt.org/">Let\'s Encrypt</a>.', 'ipt_fsqm' ) );
		}
		?>
<table class="form-table no-margin">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][enabled]', __( 'Enable Payment System', 'ipt_fsqm' ) ); ?></th>
			<td>
			<?php $this->ui->toggle( 'settings[payment][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, [
				'condid' => 'eform_set_payment_config',
			] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Payment will work, only if this settings is enabeld.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][sub_on_success]', __( 'Submission Visible after Successful Payment', 'ipt_fsqm' ) ); ?></th>
			<td>
			<?php $this->ui->toggle( 'settings[payment][sub_on_success]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['sub_on_success'], '1', false, true, [
				'condid' => 'eform_payment_config_lock_message'
			] ); ?>
			<span class="description">
				<?php _e( 'Also works for WooCommerce Single Product Checkout', 'ipt_fsqm' ); ?>
			</span>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you would like to send email only when payment is successful, then enable this option. If payment is not successful, then the email will only contain the payment link and relevant retry methods. This will also reflect on the trackback page. If this is enabled, then by no means the user would be able to access the submission, neither from email, nor from trackback.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="eform_payment_config_lock_message">
			<th><?php $this->ui->generate_label( 'settings[payment][lock_message]', __( 'Submission Blocking Message', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[payment][lock_message]', $op['lock_message'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the message that would be shown to the user if Submission blocking is enabled and user have not finished payment. <code>%RETRY_LINK%</code> would be replaced by the URL from which user can retry payment.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
	</tbody>
</table>
<table class="form-table" id="eform_set_payment_config">
	<tbody>
		<?php $this->ui->form_table( $payment_items, false ); ?>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][formula]', __( 'Mathematical Formula', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[payment][formula]', $op['formula'], __( 'Formula', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the mathematical formula to calculate total. More information <a href="">here</a>.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][currency]', __( 'Currency Code', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[payment][currency]', $op['currency'], __( 'Currency Code', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the currency code for the amount.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][c_prefix]', __( 'Currency Prefix', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[payment][c_prefix]', $op['c_prefix'], __( 'Currency prefix', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the code that needs to be inserted before amount.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][c_suffix]', __( 'Currency Suffix', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[payment][c_suffix]', $op['c_suffix'], __( 'Currency suffix', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the code that needs to be inserted after amount.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][type]', __( 'Preferred Payment Type', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->select( 'settings[payment][type]', $types, $op['type'] ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
				<p><?php _e( 'Following types are available for paypal payments.', 'ipt_fsqm' ); ?></p>
				<ul>
					<li><?php _e( '<code>Paypal Direct Payments</code>: User enters his/her card details. When submitting, the card is charged through paypal and user does not leave page.', 'ipt_fsqm' ); ?></li>
					<li><?php _e( '<code>Paypal Express Payment</code>: User is redirected to a paypal checkout page. Once payment is done, he/she is redirected back to the trackback page for updating the details.', 'ipt_fsqm' ); ?></li>
					<li><?php _e( '<code>Stripe Direct Payments</code>: User enters his/her card details. When submitting, the card is charged through stripe and user does not leave page.', 'ipt_fsqm' ); ?></li>
				</ul>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][itemname]', __( 'Item Name', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[payment][itemname]', $op['itemname'], __( 'Form Name', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the name of the item for which the invoice would be created. If empty then form name will be used instead.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][itemdescription]', __( 'Item Description', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[payment][itemdescription]', $op['itemdescription'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the description of the item for which the invoice would be created.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][itemsku]', __( 'Item SKU (Number)', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[payment][itemsku]', $op['itemsku'], __( 'Form ID', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the sku/unique number of the item for which the invoice would be created. If empty then form ID would be used.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][invoicenumber]', __( 'Invoice Number Format', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[payment][invoicenumber]', $op['invoicenumber'], __( 'Form ID', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the invoice number format. <code>{id}</code> will be replaced by a unique ID. If empty then just the ID will be used.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][success_sub]', __( 'Payment Success Subject', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[payment][success_sub]', $op['success_sub'], __( 'Write Here', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the success message subject that would be shown to the user. This will override form success message if payment field was shown. This will also be used to send the email. <code>%1$s</code> will be replaced by the invoice ID. Other format strings are also available.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][success_msg]', __( 'Payment Success Message', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[payment][success_msg]', $op['success_msg'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the success message that would be shown to the user. This will override form success message if payment field was shown. This will also be used to send the email.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][error_sub]', __( 'Payment Error Subject', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[payment][error_sub]', $op['error_sub'], __( 'Write Here', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the error message subject that would be shown to the user. This will also be used to send the email. <code>%1$s</code> will be replaced by the invoice ID. Other format strings are also available.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][error_msg]', __( 'Payment Error Message', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[payment][error_msg]', $op['error_msg'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the error message that would shown to the user. Additionally repayment form will also be shown.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][cancel_sub]', __( 'Payment Cancel Subject', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[payment][cancel_sub]', $op['cancel_sub'], __( 'Write Here', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the cancel message subject that would be shown to the user. This will also be used to send the email. <code>%1$s</code> will be replaced by the invoice ID. Other format strings are also available.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][cancel_msg]', __( 'Payment Cancel Message', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[payment][cancel_msg]', $op['cancel_msg'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the error message that would shown to the user when the payment was cancelled. Additionally repayment form will also be shown.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][retry_uemail_sub]', __( 'Payment Retry User Email Subject', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[payment][retry_uemail_sub]', $op['retry_uemail_sub'], __( 'Required', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the subject of the email that is sent upon processing of a payment retry form. <code>%1$s</code> will be replaced by the invoice ID. Other format strings are also available.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][retry_uemail_msg]', __( 'Payment Retry User Email Message', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[payment][retry_uemail_msg]', $op['retry_uemail_msg'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the message that is shown to the users who has submitted the payment retry form.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][retry_aemail_sub]', __( 'Payment Retry Admin Email Subject', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[payment][retry_aemail_sub]', $op['retry_aemail_sub'], __( 'Required', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the subject of the email that is sent upon processing of a payment retry form.  <code>%1$s</code> will be replaced by the invoice ID. Other format strings are also available.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][redir_aemail_sub]', __( '2 Step Payment Admin Email Subject', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[payment][redir_aemail_sub]', $op['redir_aemail_sub'], __( 'Required', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the admin email subject for 2 step payment gateways. In this case, the first admin notification would say the payment is under processing. When user gets redirected back from the payment gateway, to your website, then only eForm will know the actual status of the payment and would send another admin email to notify. <code>%1$s</code> will be replaced by the invoice ID. Other format strings are also available.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
	</tbody>
</table>
<?php
	}

	public function payment_coupon() {
		$op = $this->settings['payment']['coupons'];
		$coupons = array();
		$coupons[] = array(
			'value' => 'percentage',
			'label' => __( 'Percentage Deduction', 'ipt_fsqm' ),
		);
		$coupons[] = array(
			'value' => 'amount',
			'label' => __( 'Total Deduction', 'ipt_fsqm' ),
		);
		$settings = array(
			'columns' => array(
				0 => array(
					'label' => __( 'Coupon', 'ipt_fsqm' ),
					'size' => '35',
					'type' => 'text',
				),
				1 => array(
					'label' => __( 'Type', 'ipt_fsqm' ),
					'size' => '20',
					'type' => 'select',
				),
				2 => array(
					'label' => __( 'Value', 'ipt_fsqm' ),
					'size' => '15',
					'type' => 'spinner',
				),
				3 => array(
					'label' => __( 'Minimum Amount', 'ipt_fsqm' ),
					'size' => '15',
					'type' => 'spinner',
				),
			),
			'labels' => array(
				'add' => __( 'Add New Coupon', 'ipt_fsqm' ),
			),
		);
		// 0 => array(
		// 	'code' => 'xyz',
		// 	'type' => 'per', // per => percentage, val => value, formula => 10+M1*0.25
		// 	'value' => '',
		// ),
		$items = array();
		$max_key = null;
		foreach ( $op as $c_key => $coupon ) {
			$max_key = max( array( $max_key, $c_key ) );
			$items[] = array(
				0 => array( 'settings[payment][coupons][' . $c_key . '][code]', $coupon['code'], __( 'Code', 'ipt_fsqm' ) ),
				1 => array( 'settings[payment][coupons][' . $c_key . '][type]', $coupons, $coupon['type'] ),
				2 => array( 'settings[payment][coupons][' . $c_key . '][value]', $coupon['value'], __( 'Value', 'ipt_fsqm' ) ),
				3 => array( 'settings[payment][coupons][' . $c_key . '][min]', $coupon['min'], __( 'Minimum', 'ipt_fsqm' ) ),
			);
		}
		$data = array(
			0 => array( 'settings[payment][coupons][__SDAKEY__][code]', '', __( 'Code', 'ipt_fsqm' ) ),
			1 => array( 'settings[payment][coupons][__SDAKEY__][type]', $coupons, 'percentage' ),
			2 => array( 'settings[payment][coupons][__SDAKEY__][value]', '', __( 'Value', 'ipt_fsqm' ) ),
			3 => array( 'settings[payment][coupons][__SDAKEY__][min]', '0', __( 'Minimum', 'ipt_fsqm' ) ),
		);
		$this->ui->sda_list( $settings, $items, $data, $max_key );
	}

	public function payment_stripe() {
		$op = $this->settings['payment']['stripe'];
		$modes = array();
		$modes[] = array(
			'value' => 'sandbox',
			'label' => __( 'Sandbox', 'ipt_fsqm' ),
		);
		$modes[] = array(
			'value' => 'live',
			'label' => __( 'Live', 'ipt_fsqm' ),
		);
		$stripe_items_pf = 'settings[payment][stripe]';
		$sub_item_name_pf = $stripe_items_pf . '[subscription]';
		// Stripe Items
		$stripe_items = [];
		// Enable Subscription
		$stripe_items[] = [
			'name' => $sub_item_name_pf . '[enabled]',
			'label' => __( 'Enable Stripe Subscription', 'ipt_fsqm' ),
			'description' => __( 'Enabling this will disable all payment gateways and will make login mandatory before submitting the form.', 'ipt_fsqm' ),
			'ui' => 'toggle',
			'param' => [ $sub_item_name_pf . '[enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['subscription']['enabled'], '1', false, true, [
				'condid' => 'eform_pm_st_sb_wrap',
			] ],
		];

		// Subscription items
		$subscription_items = [];
		// Plan types
		$sub_plan_types = [
			0 => [
				'label' => __( 'Dynamic Plan', 'ipt_fsqm' ),
				'value' => 'dynamic',
				'data' => [
					'condid' => 'eform_pm_st_sb_pn,eform_pm_st_sb_pif,eform_pm_st_sb_sd,eform_pm_st_sb_pic,eform_pm_st_sb_prn'
				],
			],
			1 => [
				'label' => __( 'Predefined Plans', 'ipt_fsqm' ),
				'value' => 'static',
				'data' => [
					'condid' => 'eform_pm_st_sb_dpi,eform_pm_st_sb_condplans'
				],
			],
		];
		// Intervals
		$sub_plan_intervals = [
			'day' => __( 'Day', 'ipt_fsqm' ),
			'week' => __( 'Week', 'ipt_fsqm' ),
			'month' => __( 'Month', 'ipt_fsqm' ),
			'year' => __( 'Year', 'ipt_fsqm' ),
		];
		// Subscription Type
		$subscription_items[] = [
			'name' => $sub_item_name_pf . '[type]',
			'label' => __( 'Plan Type', 'ipt_fsqm' ),
			'ui' => 'select',
			'param' => [ $sub_item_name_pf . '[type]', $sub_plan_types, $op['subscription']['type'], false, true ],
			'help' => __( 'Select the subscription plan type. If your amount changes based on the form, then select the dynamic. This will create a new plan and will assign the customer to the specific plan. Otherwise, you can use the static plan where you will use a pre defined plan you have made from the Stripe Dashboard and conditionally assign the plan. In this case, the amount will be what you have defined in the plan, not the one calculated by payment element. It is a good idea to have the payment element reflect the same amount.', 'ipt_fsqm' ),
		];
		// Product Name
		$subscription_items[] = [
			'name' => $sub_item_name_pf . '[product_name]',
			'label' => __( 'Product Name', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => [ $sub_item_name_pf . '[product_name]', $op['subscription']['product_name'], __( 'Required', 'ipt_fsqm' ) ],
			'description' => __( 'Product ID will be generated automatically based on form ID', 'ipt_fsqm' ),
			'help' => __( 'Starting with Stripe API Update 2018-02-05 all plans now go under a service type product. eForm leverages this new feature by grouping all dynamic plans under a specific product linked to this form. Please enter a name of the product so that you can easily search. All previous plans will not get automatically assigned to this product. If you want, you can do this manually. Also make sure you have upgraded the Stripe API to latest from Stripe Dashboard.', 'ipt_fsqm' ),
			'id' => 'eform_pm_st_sb_prn',
		];
		// Dynamic Plan Statement Description
		$subscription_items[] = [
			'name' => $sub_item_name_pf . '[statement_descriptor]',
			'label' => __( 'Statement Descriptor', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => [ $sub_item_name_pf . '[statement_descriptor]', $op['subscription']['statement_descriptor'], __( 'Required', 'ipt_fsqm' ), 'fit', 'normal', [], false, false, [
				'maxlength' => '22',
			] ],
			'help' => __( 'Set the description that appears on CC billing. Max 22 characters.', 'ipt_fsqm' ),
			'id' => 'eform_pm_st_sb_sd',
		];
		// Dynamic Plan Name
		$subscription_items[] = [
			'name' => $sub_item_name_pf . '[plan_name]',
			'label' => __( 'Dynamic Plan Name', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => [ $sub_item_name_pf . '[plan_name]', $op['subscription']['plan_name'], __( 'Required', 'ipt_fsqm' ) ],
			'description' => __( 'Plan ID will be generated automatically based on submission ID', 'ipt_fsqm' ),
			'help' => __( 'Select the name of the Plan which will appear in your Stripe Dashboard. Format strings are accepted here.', 'ipt_fsqm' ),
			'id' => 'eform_pm_st_sb_pn',
		];
		// Dynamic Plan Interval Count
		$subscription_items[] = [
			'name' => $sub_item_name_pf . '[interval_count]',
			'label' => __( 'Interval Count', 'ipt_fsqm' ),
			'ui' => 'spinner',
			'param' => [ $sub_item_name_pf . '[interval_count]', $op['subscription']['interval_count'], __( 'Required', 'ipt_fsqm' ), '1', '', '1' ],
			'help' => __( 'The number of intervals between each subscription billing. For example, if count set to 3 and interval set to month, then bills every 3 month. According to Stripe, maximum of one year interval allowed (1 year, 12 months or 52 weeks).', 'ipt_fsqm' ),
			'id' => 'eform_pm_st_sb_pic',
		];
		// Dynamic Plan Interval
		$subscription_items[] = [
			'name' => $sub_item_name_pf . '[plan_interval]',
			'label' => __( 'Plan Interval', 'ipt_fsqm' ),
			'description' => __( 'Maximum 1 year.', 'ipt_fsqm' ),
			'ui' => 'select',
			'param' => [ $sub_item_name_pf . '[plan_interval]', $sub_plan_intervals, $op['subscription']['plan_interval'] ],
			'help' => __( 'Set the interval/billing frequency. If you are setting year, then make sure the interval count is set to one.', 'ipt_fsqm' ),
			'id' => 'eform_pm_st_sb_pif',
		];
		// Static Default Plan ID
		$subscription_items[] = [
			'name' => $sub_item_name_pf . '[default_plan_id]',
			'label' => __( 'Default Plan ID', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => [ $sub_item_name_pf . '[default_plan_id]', $op['subscription']['default_plan_id'], __( 'Required', 'ipt_fsqm' ) ],
			'help' => __( 'Set the default plan ID to subscribe the customer to.', 'ipt_fsqm' ),
			'id' => 'eform_pm_st_sb_dpi',
		];
		// Conditional Plan selector
		$cond_plan_data = [];
		foreach ( (array) $op['subscription']['cond_plans'] as $item_key => $item ) {
			$new_cond_plan_data = [];
			foreach ( $item as $data_key => $data ) {
				if ( 'logics' == $data_key ) {
					$new_cond_plan_data[ $data_key ] = $data;
				} else {
					$new_cond_plan_data[ $data_key ] = [ $sub_item_name_pf . '[cond_plans][' . $item_key . '][' . $data_key . ']', $data, __( 'Required', 'ipt_fsqm' ) ];
				}
			}
			if ( ! isset( $new_cond_plan_data['logics'] ) ) {
				$new_cond_plan_data['logics'] = [];
			}
			$cond_plan_data[ $item_key ] = $new_cond_plan_data;
		}
		$cond_plan_config = [
			0 => [
				'label' => __( 'Stripe Plan ID', 'ipt_fsqm' ),
				'type' => 'text',
				'size' => '100',
				'data' => [ $sub_item_name_pf . '[cond_plans][__SDAKEY__][planid]', '', __( 'Required', 'ipt_fsqm' ) ],
			],
		];
		?>
<table class="form-table" style="margin-bottom: 0;">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][stripe][enabled]', __( 'Enable Stripe Payment', 'ipt_fsqm' ) ); ?></th>
			<td>
			<?php $this->ui->toggle( 'settings[payment][stripe][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, [
				'condid' => 'eform_pm_stripe_config_wrap',
			] ); ?>
			<p class="description">
				<?php _e( 'From V4.12, eForm uses Stripe Strong Customer Authentication (SCA) APIs.', 'ipt_fsqm' ); ?>
			</p>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enable Paypal Payment here. Please make sure you have obtaiend and saved the stripe client id and client secrets.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
	</tbody>
</table>
<div id="eform_pm_stripe_config_wrap">
	<table class="form-table no-margin">
		<tbody>
			<tr>
				<th><?php $this->ui->generate_label( 'settings[payment][stripe][label_stripe]', __( 'Option label for Stripe Checkout', 'ipt_fsqm' ) ); ?></th>
				<td>
					<?php $this->ui->text( 'settings[payment][stripe][label_stripe]', $op['label_stripe'], __( 'Credit Card (Stripe)', 'ipt_fsqm' ) ); ?>
				</td>
				<td>
					<?php $this->ui->help( __( 'Set the label that is shown in the radio buttons for stripe based direct credit card checkout.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
			<tr>
				<th><?php $this->ui->generate_label( 'settings[payment][stripe][zero_decimal]', __( 'Zero Decimal Currency', 'ipt_fsqm' ) ); ?></th>
				<td>
				<?php $this->ui->toggle( 'settings[payment][stripe][zero_decimal]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['zero_decimal'] ); ?>
				</td>
				<td>
					<?php $this->ui->help( __( 'By default eForm would multiply the amount by 100 (as specified in stripe documentation) to generate the resulting amount. But if you have mentioned currency which is zero-decimal (i.e, the currency itself is the lowest/smallest currency unit then you need to enable this feature. More information <a href="https://support.stripe.com/questions/which-zero-decimal-currencies-does-stripe-support" target="_blank">here</a>. For USD always leave it off.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
			<tr>
				<th><?php $this->ui->generate_label( 'settings[payment][stripe][pub]', __( 'Stripe Publishable API Key', 'ipt_fsqm' ) ); ?></th>
				<td>
					<?php $this->ui->text( 'settings[payment][stripe][pub]', $op['pub'], __( 'Required', 'ipt_fsqm' ) ); ?>
					<p><span class="description">
						<?php printf( __( 'Get your <a href="%1$s" target="_blank">Publishable API key</a>.', 'ipt_fsqm' ), 'https://dashboard.stripe.com/account/apikeys' ); ?>
					</span></p>
				</td>
				<td>
					<?php $this->ui->help( __( 'Please enter your stripe API key. The API key can be for live mode or test mode. The result would be in accordance. If you need help on where to find your API key, you can <a href="https://support.stripe.com/questions/where-do-i-find-my-api-keys" target="_blank">read this</a>.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
			<tr>
				<th><?php $this->ui->generate_label( 'settings[payment][stripe][api]', __( 'Stripe Secret API Key', 'ipt_fsqm' ) ); ?></th>
				<td>
					<?php $this->ui->cpassword( 'settings[payment][stripe][api]', $op['api'], __( 'Required', 'ipt_fsqm' ) ); ?>
					<p><span class="description">
						<?php printf( __( 'Get your <a href="%1$s" target="_blank">Secret API key</a>.', 'ipt_fsqm' ), 'https://dashboard.stripe.com/account/apikeys' ); ?>
					</span></p>
				</td>
				<td>
					<?php $this->ui->help( __( 'Please enter your stripe API key. The API key can be for live mode or test mode. The result would be in accordance. If you need help on where to find your API key, you can <a href="https://support.stripe.com/questions/where-do-i-find-my-api-keys" target="_blank">read this</a>.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
			<?php $this->ui->form_table( $stripe_items, false ); ?>
		</tbody>
	</table>
	<div id="eform_pm_st_sb_wrap">
		<table class="form-table">
			<tbody>
				<?php $this->ui->form_table( $subscription_items, false ); ?>
				<tr id="eform_pm_st_sb_condplans">
					<td colspan="3">
						<?php $this->build_conditional_config( $sub_item_name_pf . '[cond_plans]', $cond_plan_config, 'logics', '', $cond_plan_data ); ?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
		<?php
	}

	public function payment_authorize() {
		$op = $this->settings['payment']['authorizenet'];
		$modes = array();
		$modes[] = array(
			'value' => 'sandbox',
			'label' => __( 'Sandbox', 'ipt_fsqm' ),
		);
		$modes[] = array(
			'value' => 'live',
			'label' => __( 'Live', 'ipt_fsqm' ),
		);

		// Table items
		// With new Admin UI API -- YAY
		$items = array();
		// Enabled
		$items[] = array(
			'name' => 'settings[payment][authorizenet][enabled]',
			'label' => __( 'Enable Authorize.net Credit Card Payment', 'ipt_fsqm' ),
			'ui' => 'toggle',
			'param' => array( 'settings[payment][authorizenet][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, array(
				'condid' => 'ipt_fsqm_settings_authnet_la,ipt_fsqm_settings_authnet_li,ipt_fsqm_settings_authnet_tk,ipt_fsqm_settings_authnet_am',
			) ),
			'help' => __( 'Enable this settings if you want to use Authorize.net Payment Gateway.', 'ipt_fsqm' ),
		);
		// Label
		$items[] = array(
			'name' => 'settings[payment][authorizenet][label]',
			'label' => __( 'Option Label for Authorize.net Checkout', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => array( 'settings[payment][authorizenet][label]', $op['label'], __( 'Required', 'ipt_fsqm' ) ),
			'help' => __( 'Set the label that is shown in the radio buttons for authorizenet.net based direct credit card checkout.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_settings_authnet_la',
		);
		// Mode
		$items[] = array(
			'name' => 'settings[payment][authorizenet][mode]',
			'label' => __( 'API Mode', 'ipt_fsqm' ),
			'ui' => 'select',
			'param' => array( 'settings[payment][authorizenet][mode]', $modes, $op['mode'] ),
			'help' => __( 'Set the API mode.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_settings_authnet_am',
			/** translators: %1$s is replaced by a authorize.net kb article link */
			'description' => __( 'Set the API Key mode. It could be either sandbox or live.', 'ipt_fsqm' ),
		);
		// Login ID
		$items[] = array(
			'name' => 'settings[payment][authorizenet][login_id]',
			'label' => __( 'API Login ID', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => array( 'settings[payment][authorizenet][login_id]', $op['login_id'], __( 'Required', 'ipt_fsqm' ) ),
			'help' => __( 'Set the API Login ID you can obtain from authorize.net.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_settings_authnet_li',
			/** translators: %1$s is replaced by a authorize.net kb article link */
			'description' => sprintf( __( 'Get <a href="%1$s">API Login ID</a>.', 'ipt_fsqm' ), 'https://support.authorize.net/authkb/index?page=content&id=A405' ),
		);
		// Transaction Key
		$items[] = array(
			'name' => 'settings[payment][authorizenet][transaction_key]',
			'label' => __( 'API Transaction Key', 'ipt_fsqm' ),
			'ui' => 'password',
			'param' => array( 'settings[payment][authorizenet][transaction_key]', $op['transaction_key'], __( 'Required', 'ipt_fsqm' ) ),
			'help' => __( 'Set the API Transaction Key you can obtain from authorize.net.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_settings_authnet_tk',
			/** translators: %1$s is replaced by a authorize.net kb article link */
			'description' => sprintf( __( 'Get <a href="%1$s">API Transaction Key</a>.', 'ipt_fsqm' ), 'https://support.authorize.net/authkb/index?page=content&id=A405' ),
		);
		$this->ui->form_table( $items );
	}

	public function payment_offline() {
		$op = $this->settings['payment']['offline'];
		$items = [];
		// Enabled
		$items[] = array(
			'name' => 'settings[payment][offline][enabled]',
			'label' => __( 'Enable Offline Payment', 'ipt_fsqm' ),
			'ui' => 'toggle',
			'param' => array( 'settings[payment][offline][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, array(
				'condid' => 'ipt_fsqm_settings_pmofl_la,ipt_fsqm_settings_pmofl_ins,ipt_fsqm_settings_pmofl_sts,ipt_fsqm_settings_pmofl_esub,ipt_fsqm_settings_pmofl_emsg',
			) ),
			'help' => __( 'Enable this settings if you want to use Offline Payment Gateway, such as Bank Transfer, Cash on Delivery.', 'ipt_fsqm' ),
		);
		// Label
		$items[] = array(
			'name' => 'settings[payment][offline][label]',
			'label' => __( 'Option Label for Offline Checkout', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => array( 'settings[payment][offline][label]', $op['label'], __( 'Required', 'ipt_fsqm' ) ),
			'help' => __( 'Set the label that is shown in the radio buttons for offline checkout.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_settings_pmofl_la',
		);
		// Instruction
		$items[] = [
			'name' => 'settings[payment][offline][instruction]',
			'label' => __( 'Instruction of Offline Payment', 'ipt_fsqm' ),
			'ui' => 'textarea',
			'param' => [ 'settings[payment][offline][instruction]', $op['instruction'], __( 'Required', 'ipt_fsqm' ) ],
			'id' => 'ipt_fsqm_settings_pmofl_ins',
			'help' => __( 'Enter the instruction text that will appear when your user chooses this gateway.', 'ipt_fsqm' ),
		];
		// Invoice Status
		$items[] = [
			'name' => 'settings[payment][offline][status]',
			'label' => __( 'Invoice Status', 'ipt_fsqm' ),
			'ui' => 'textarea',
			'param' => [ 'settings[payment][offline][status]', $op['status'], __( 'Required', 'ipt_fsqm' ) ],
			'id' => 'ipt_fsqm_settings_pmofl_sts',
			'help' => __( 'Enter the text that will appear on the invoice when payment is not confirmed yet. <strong><code>%1$s</code></strong> will be replaced by Invoice ID and <strong><code>%2$s</code></strong> will be replaced by Transaction ID.', 'ipt_fsqm' ),
		];
		// Email Subject
		$items[] = array(
			'name' => 'settings[payment][offline][email_sub]',
			'label' => __( 'Payment Email Subject', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => array( 'settings[payment][offline][email_sub]', $op['email_sub'], __( 'Required', 'ipt_fsqm' ) ),
			'help' => __( 'Set the subject of the payment email. All format strings are available.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_settings_pmofl_esub',
		);
		// Email Message
		$items[] = array(
			'name' => 'settings[payment][offline][email_msg]',
			'label' => __( 'Payment Email Message', 'ipt_fsqm' ),
			'ui' => 'textarea',
			'param' => array( 'settings[payment][offline][email_msg]', $op['email_msg'], __( 'Required', 'ipt_fsqm' ) ),
			'help' => __( 'Set the message of the payment email. All format strings are available.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_settings_pmofl_emsg',
		);
		$this->ui->form_table( $items );
	}

	public function payment_paypal() {
		$op = $this->settings['payment']['paypal'];
		$modes = array();
		$modes[] = array(
			'value' => 'sandbox',
			'label' => __( 'Sandbox', 'ipt_fsqm' ),
		);
		$modes[] = array(
			'value' => 'live',
			'label' => __( 'Live', 'ipt_fsqm' ),
		);

		?>
<table class="form-table no-margin">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][paypal][enabled]', __( 'Enable Paypal Payment', 'ipt_fsqm' ) ); ?></th>
			<td>
			<?php $this->ui->toggle( 'settings[payment][paypal][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, [
				'condid' => 'eform_pm_paypal_config',
			] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enable Paypal Payment here. Please make sure you have obtaiend and saved the paypal client id and client secrets.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
	</tbody>
</table>
<table class="form-table" id="eform_pm_paypal_config">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][paypal][mode]', __( 'Paypal Payment Mode', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->select( 'settings[payment][paypal][mode]', $modes, $op['mode'] ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
				<p><?php _e( 'Following modes are available for testing and publishing.', 'ipt_fsqm' ); ?></p>
				<ul>
					<li><?php _e( '<code>Sandbox</code>: Used for testing.' ) ?></li>
					<li><?php _e( '<code>Live</code>: Used for production.' ) ?></li>
				</ul>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][paypal][allow_direct]', __( 'Allow Direct Payment Mode', 'ipt_fsqm' ) ); ?></th>
			<td>
			<?php $this->ui->toggle( 'settings[payment][paypal][allow_direct]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['allow_direct'] ); ?>
			<br /><span class="description"><?php _e( 'PayPal has discontinued new direct credit card integration. Existing ones may still work. We will soon implement BrainTreePayments to complement this. For now, kindly use Stripe.', 'ipt_fsqm' ); ?></span>
			</td>
			<td>
				<?php $this->ui->help( __( 'If enabled, then users would be able to directly pay by entering their credit card information. Do note that if you do not have <a href="https://developer.paypal.com/developer/accountStatus" target="_blank">account eligibility</a> then disable this. Otherwise users might get unintended errors.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][paypal][label_paypal_e]', __( 'Option label for Express Checkout', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[payment][paypal][label_paypal_e]', $op['label_paypal_e'], __( 'PayPal Express', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Set the label that is shown in the radio buttons for express checkout.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][paypal][label_paypal_d]', __( 'Option label for Direct Checkout', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[payment][paypal][label_paypal_d]', $op['label_paypal_d'], __( 'PayPal Direct', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Set the label that is shown in the radio buttons for direct credit card checkout.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][paypal][d_settings]', __( 'PayPal RESTful APIs', 'ipt_fsqm' ) ); ?></th>
			<td>
			<?php $this->ui->generate_label( 'settings[payment][paypal][d_settings][client_id]', __( 'Client ID', 'ipt_fsqm' ) ); ?>
			<?php $this->ui->text( 'settings[payment][paypal][d_settings][client_id]', $op['d_settings']['client_id'], __( 'Client ID', 'ipt_fsqm' ) ); ?>
			<hr />
			<?php $this->ui->generate_label( 'settings[payment][paypal][d_settings][client_secret]', __( 'Client Secret', 'ipt_fsqm' ) ); ?>
			<?php $this->ui->cpassword( 'settings[payment][paypal][d_settings][client_secret]', $op['d_settings']['client_secret'], __( 'Client Secret', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
				<p><?php _e( 'You can obtain your client id and client secret from the following links.', 'ipt_fsqm' ); ?></p>
				<ul>
					<li><?php printf( __( 'Go to <a href="%1$s" target="_blank">Paypal Developer</a>', 'ipt_fsqm' ), 'https://developer.paypal.com/developer/applications/' ); ?></li>
					<li><?php _e( 'Create a new REST API app under Dashboard > My Apps & Credentials', 'ipt_fsqm' ); ?></li>
					<li><?php _e( 'Copy the client ID and client secret.', 'ipt_fsqm' ); ?></li>
				</ul>
				<p><?php _e( 'The REST API app can be created under a sandbox facilitator (business) account.', 'ipt_fsqm' ); ?></p>
				<p><?php _e( 'In some country live transactions are not available. Please check your <a href="https://developer.paypal.com/developer/accountStatus" target="_blank">account eligibility</a>. If Direct credit cards isn\'t available, then do not enable Direct Payment Mode, as this may lead to failures.', 'ipt_fsqm' ); ?></p>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][paypal][partner]', __( 'PayPal Partner ID', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[payment][paypal][partner]', $op['partner'], __( 'Optional', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Use this if you are a PayPal partner. Specify a unique BN Code to receive revenue attribution. To learn more or to request a BN Code, contact your Partner Manager or visit the PayPal Partner Portal', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][paypal][conf_sub]', __( 'Express Checkout Confirmation Email Subject', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[payment][paypal][conf_sub]', $op['conf_sub'], __( 'Required', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the subject of the email that is sent when paypal express checkout has been processed (i.e, user has been sent back to your site). <code>%1$s</code> will be replaced by the invoice ID.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][paypal][conf_msg]', __( 'Express Checkout Confirmation Email Message', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[payment][paypal][conf_msg]', $op['conf_msg'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the email body of the same.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
	</tbody>
</table>
		<?php
	}

	public function ranking() {
		$op = $this->settings['ranking'];
		$r_select = array();
		$r_select[] = array(
			'value' => 'percentage',
			'label' => __( 'Percentage Score', 'ipt_fsqm' ),
			'data' => array(
				'condid' => 'ranking_percentage',
			),
		);
		$r_select[] = array(
			'value' => 'raw',
			'label' => __( 'Total Score', 'ipt_fsqm' ),
			'data' => array(
				'condid' => 'ranking_raw',
			),
		);
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[ranking][precision]', __( 'Score Decimal Precision', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->spinner( 'settings[ranking][precision]', $op['precision'], __( 'Enter Number', 'ipt_fsqm' ) ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Score and Percentage precision after decimal point.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[ranking][enabled]', __( 'Enable Ranking System based on Score', 'ipt_fsqm' ) ); ?></th>
			<td>
			<?php $this->ui->toggle( 'settings[ranking][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, array(
				'condid' => 'ipt_fsqm_ranking_title_wrap,ipt_fsqm_ranking_ranks_wrap,ipt_fsqm_ranking_rtype_wrap'
			) ); ?>
			</td>
			<td style="width: 50px;">
				<?php $this->ui->help_head(); ?>
				<p><?php _e( 'If you wish to designate some rank to your users based on score, then enable this system. You will have option to put titles and custom messages for each of the designations.', 'ipt_fsqm' ); ?></p>
				<p><?php _e( 'While entering the Message, you have the following format strings available.', 'ipt_fsqm' ); ?></p>
				<ul class="ul-square">
					<li><strong>%NAME%</strong> : <?php _e( 'Will be replaced by the full name of the user.', 'ipt_fsqm' ); ?></li>
					<li><strong>%TRACK_LINK%</strong> : <?php _e( 'Will be replaced by the raw link from where the user can see the status of his submission.', 'ipt_fsqm' ); ?></li>
					<li><strong>%TRACK%</strong> : <?php _e( 'Will be replaced by a "Click Here" button linked to the track page.', 'ipt_fsqm' ); ?></li>
					<li><strong>%SCORE%</strong> : <?php _e( 'Will be replaced by the score obtained/total score.', 'ipt_fsqm' ); ?></li>
					<li><strong>%SCOREPERCENT%</strong> : <?php _e( 'Will be replaced by the percentage score obtained.', 'ipt_fsqm' ); ?></li>
					<li><strong>%DESIGNATION%</strong> : <?php _e( 'If the score falls under a valid ranking range, then this will be replaced by the given designation title.', 'ipt_fsqm' ); ?></li>
					<li><strong>%TRACK_ID%</strong> : <?php _e( 'Will be replaced by the Tracking ID of the submission which the user can enter in the track page.', 'ipt_fsqm' ); ?></li>
				</ul>
				<p><?php printf( __( 'An updated list can always be found <a href="%1$s" target="_blank">here</a>.', 'ipt_fsqm' ), 'https://wpquark.com/kb/fsqm/form-submission-related/available-format-strings-custom-notifications/' ); ?></p>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_ranking_title_wrap">
			<th><?php $this->ui->generate_label( 'settings[ranking][title]', __( 'Ranking Title', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[ranking][title]', $op['title'], __( 'Shown on Rank Column on trackback page', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'The title of the ranking system. For eg, Designation.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_ranking_rtype_wrap">
			<th><?php $this->ui->generate_label( 'settings[ranking][rtype]', __( 'Ranking Based On', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->select( 'settings[ranking][rtype]', $r_select, $op['rtype'], false, true ); ?></td>
			<td><?php $this->ui->help( __( 'Here you can set the ranking based on either percentage obtained or total score obtained.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_ranking_ranks_wrap">
			<td colspan="3">
				<?php $this->ranking_ranks(); ?>
			</td>
		</tr>
	</tbody>
</table>
		<?php
	}

	public function ranking_ranks() {
		$op = $this->settings['ranking']['ranks'];
		$settings = array(
			'columns' => array(
				0 => array(
					'label' => __( 'Score From (%)', 'ipt_fsqm' ),
					'size' => '17',
					'type' => 'spinner',
				),
				1 => array(
					'label' => __( 'Score To (%)', 'ipt_fsqm' ),
					'size' => '17',
					'type' => 'spinner',
				),
				2 => array(
					'label' => __( 'Designation', 'ipt_fsqm' ),
					'size' => '25',
					'type' => 'text',
				),
				3 => array(
					'label' => __( 'Message', 'ipt_fsqm' ),
					'size' => '41',
					'type' => 'textarea',
				),
			),
			'labels' => array(
				'add' => __( 'Add New Rank', 'ipt_fsqm' ),
			),
		);
		$items = array();
		$max_key = null;
		foreach ( $op as $r_key => $rank ) {
			$max_key = max( array( $max_key, $r_key ) );
			$items[] = array(
				0 => array( 'settings[ranking][ranks][' . $r_key . '][min]', $rank['min'], __( 'Min Score', 'ipt_fsqm' ) ),
				1 => array( 'settings[ranking][ranks][' . $r_key . '][max]', $rank['max'], __( 'Max Score', 'ipt_fsqm' ) ),
				2 => array( 'settings[ranking][ranks][' . $r_key . '][title]', $rank['title'], __( 'Rank Designation', 'ipt_fsqm' ), 'fit' ),
				3 => array( 'settings[ranking][ranks][' . $r_key . '][msg]', $rank['msg'], __( 'Message Shown', 'ipt_fsqm' ), 'fit' ),
			);
		}
		$data = array(
			0 => array( 'settings[ranking][ranks][__SDAKEY__][min]', 10, __( 'Min Score', 'ipt_fsqm' ) ),
			1 => array( 'settings[ranking][ranks][__SDAKEY__][max]', 80, __( 'Max Score', 'ipt_fsqm' ) ),
			2 => array( 'settings[ranking][ranks][__SDAKEY__][title]', '', __( 'Rank Designation', 'ipt_fsqm' ), 'fit' ),
			3 => array( 'settings[ranking][ranks][__SDAKEY__][msg]', '', __( 'Message Shown', 'ipt_fsqm' ), 'fit' ),
		);
		$this->ui->sda_list( $settings, $items, $data, $max_key, 'ranking_percentage' );

		$r_settings = array(
			'columns' => array(
				0 => array(
					'label' => __( 'Score From', 'ipt_fsqm' ),
					'size' => '17',
					'type' => 'spinner',
				),
				1 => array(
					'label' => __( 'Score To', 'ipt_fsqm' ),
					'size' => '17',
					'type' => 'spinner',
				),
				2 => array(
					'label' => __( 'Designation', 'ipt_fsqm' ),
					'size' => '25',
					'type' => 'text',
				),
				3 => array(
					'label' => __( 'Message', 'ipt_fsqm' ),
					'size' => '41',
					'type' => 'textarea',
				),
			),
			'labels' => array(
				'add' => __( 'Add New Rank', 'ipt_fsqm' ),
			),
		);
		$r_items = array();
		$r_max_key = null;
		foreach ( $this->settings['ranking']['rranks'] as $rs_key => $rranks ) {
			$r_max_key = max( array( $r_max_key, $rs_key ) );
			$r_items[] = array(
				0 => array( 'settings[ranking][rranks][' . $rs_key . '][min]', $rranks['min'], __( 'Min Score', 'ipt_fsqm' ) ),
				1 => array( 'settings[ranking][rranks][' . $rs_key . '][max]', $rranks['max'], __( 'Max Score', 'ipt_fsqm' ) ),
				2 => array( 'settings[ranking][rranks][' . $rs_key . '][title]', $rranks['title'], __( 'Rank Designation', 'ipt_fsqm' ), 'fit' ),
				3 => array( 'settings[ranking][rranks][' . $rs_key . '][msg]', $rranks['msg'], __( 'Message Shown', 'ipt_fsqm' ), 'fit' ),
			);
		}
		$r_data = array(
			0 => array( 'settings[ranking][rranks][__SDAKEY__][min]', '', __( 'Min Score', 'ipt_fsqm' ) ),
			1 => array( 'settings[ranking][rranks][__SDAKEY__][max]', '', __( 'Max Score', 'ipt_fsqm' ) ),
			2 => array( 'settings[ranking][rranks][__SDAKEY__][title]', '', __( 'Rank Designation', 'ipt_fsqm' ), 'fit' ),
			3 => array( 'settings[ranking][rranks][__SDAKEY__][msg]', '', __( 'Message Shown', 'ipt_fsqm' ), 'fit' ),
		);
		$this->ui->sda_list( $r_settings, $r_items, $r_data, $r_max_key, 'ranking_raw' );
	}

	public function timer() {
		$op = $this->settings['timer'];
		$select_types = array(
			0 => array(
				'value' => 'none',
				'label' => __( 'Disabled', 'ipt_fsqm' ),
			),
			1 => array(
				'value' => 'overall',
				'label' => __( 'Overall Time Limit', 'ipt_fsqm' ),
				'data' => array( 'condid' => 'ipt_fsqm_timer_overall_limit_wrap' ),
			),
			2 => array(
				'value' => 'page_specific',
				'label' => __( 'Page Specific Time Limit', 'ipt_fsqm' ),
			),
		);
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[timer][time_limit_type]', __( 'Auto Submit Timer', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->select( 'settings[timer][time_limit_type]', $select_types, $op['time_limit_type'], false, true ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
				<p><?php _e( 'If you want the form to auto submit after a specified time, then enable it from here.', 'ipt_fsqm' ); ?></p>
				<p><?php _e('The auto submission can be of two type:', 'ipt_fsqm'); ?></p>
				<ol>
					<li><strong><?php _e('Overall Time Limit', 'ipt_fsqm'); ?></strong>: <?php _e('The whole of the form is submitted after the specified time (in seconds).', 'ipt_fsqm'); ?></li>
					<li><strong><?php _e('Page Specific Time Limit', 'ipt_fsqm'); ?></strong>: <?php _e('Each of the pages/tabs/containers are automatically progressed after the specified time (in seconds). If this is selected, then it would automatically disable pagination to left.', 'ipt_fsqm'); ?></li>
				</ol>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_timer_overall_limit_wrap">
			<th><?php $this->ui->generate_label( 'settings[timer][overall_limit]', __( 'Overall Time Limit (seconds)', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->spinner( 'settings[timer][overall_limit]', $op['overall_limit'], __( 'Seconds', 'ipt_fsqm' ), '0' ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Set the total time in seconds after which the form will auto submit.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
	</tbody>
</table>
		<?php
	}

	public function stopwatch() {
		$op = $this->settings['stopwatch'];
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[stopwatch][enabled]', __( 'Record Form Submission Time', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[stopwatch][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, array(
					'condid' => 'ipt_fsqm_stopwatch_title,ipt_fsqm_stopwatch_seconds,ipt_fsqm_stopwatch_hours,ipt_fsqm_stopwatch_days,ipt_fsqm_stopwatch_add_on_edit,ipt_fsqm_stopwatch_rotate,ipt_fsqm_stopwatch_hidden'
				) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enabling will record the form submission time. A timer would show up just above the tabs/container.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_stopwatch_title">
			<th><?php $this->ui->generate_label( 'settings[stopwatch][title]', __( 'Summary Table Title', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[stopwatch][title]', $op['title'], __( 'Required', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Title of the summary table row that would present the time spent.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_stopwatch_seconds">
			<th><?php $this->ui->generate_label( 'settings[stopwatch][seconds]', __( 'Show seconds', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[stopwatch][seconds]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['seconds'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Show the seconds circle.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_stopwatch_hours">
			<th><?php $this->ui->generate_label( 'settings[stopwatch][hours]', __( 'Show hours', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[stopwatch][hours]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['hours'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Show the hours circle.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_stopwatch_days">
			<th><?php $this->ui->generate_label( 'settings[stopwatch][days]', __( 'Show days', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[stopwatch][days]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['days'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Show the days circle.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_stopwatch_add_on_edit">
			<th><?php $this->ui->generate_label( 'settings[stopwatch][add_on_edit]', __( 'Add to time when editing', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[stopwatch][add_on_edit]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['add_on_edit'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If enabled, then more time would be added when user comes back to edit the form. Otherwise, it would just stay as is from the first time submission.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_stopwatch_rotate">
			<th><?php $this->ui->generate_label( 'settings[stopwatch][rotate]', __( 'Rotated Position on bigger screen', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[stopwatch][rotate]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['rotate'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If enabled, then on bigger screens the timer would appear on the right side of the form with a rotation of 90 degrees. If this causes trouble with your theme, then disable it.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_stopwatch_hidden">
			<th><?php $this->ui->generate_label( 'settings[stopwatch][hidden]', __( 'Hidden Stopwatch', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[stopwatch][hidden]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['hidden'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If enabled then the stopwatch will stay hidden. The time will be recorded but would not be shown to the user.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
	</tbody>
</table>
		<?php
	}

	public function summary() {
		$op = $this->settings['summary'];
		$booleans = array(
			'show_details'      => __( 'Show Submission Details Table (ID, date etc).', 'ipt_fsqm' ),
			'show_elements'     => __( 'Show Full Form Elements Table', 'ipt_fsqm' ),
			'f_name'            => __( 'Show First Name', 'ipt_fsqm' ),
			'l_name'            => __( 'Show Last Name', 'ipt_fsqm' ),
			'email'             => __( 'Show Email', 'ipt_fsqm' ),
			'phone'             => __( 'Show Phone', 'ipt_fsqm' ),
			'ip'                => __( 'Show IP Address', 'ipt_fsqm' ),
			'total_score'       => __( 'Show Total Score', 'ipt_fsqm' ),
			'average_score'     => __( 'Show Average Score', 'ipt_fsqm' ),
			'designation'       => __( 'Show Designation', 'ipt_fsqm' ),
			'user_account'      => __( 'Show User Account', 'ipt_fsqm' ),
			'link'              => __( 'Show Trackback Link', 'ipt_fsqm' ),
			'individual_score'  => __( 'Show Individual Scores for elements', 'ipt_fsqm' ),
			'hide_options'      => __( 'Hide Unselected Options', 'ipt_fsqm' ),
			'highlight_correct' => __( 'Highlight Correct Option (with max score)', 'ipt_fsqm' ),
			'positive_correct'  => __( 'Highlight All Positive Score Options', 'ipt_fsqm' ),
			'hide_unattempted'  => __( 'Hide Unattempted Questions', 'ipt_fsqm' ),
			'show_design'       => __( 'Show Design Elements', 'ipt_fsqm' ),
		);

		// Prepare data for sda
		$m_type_select = array(
			0 => array(
				'value' => 'mcq',
				'label' => __( '(M) MCQ', 'ipt_fsqm' ),
			),
			1 => array(
				'value' => 'freetype',
				'label' => __( '(F) Text Input', 'ipt_fsqm' ),
			),
			2 => array(
				'value' => 'pinfo',
				'label' => __( '(O) Others', 'ipt_fsqm' ),
			),
			3 => array(
				'value' => 'formatstring',
				'label' => __( 'Custom Value', 'ipt_fsqm' ),
			),
		);
		$sda_columns = array(
			0 => array(
				'label' => __( 'Row Label', 'ipt_fsqm' ),
				'size' => '50',
				'type' => 'text',
			),
			1 => array(
				'label' => __( '(X)', 'ipt_fsqm' ),
				'size' => '25',
				'type' => 'select',
			),
			2 => array(
				'label' => __( '{KEY}', 'ipt_fsqm' ),
				'size' => '25',
				'type' => 'text',
			),
		);
		$sda_labels = array(
			'add' => __( 'Add New Row', 'ipt_fsqm' ),
		);
		$sda_data_name_prefix = 'settings[summary][meta][__SDAKEY__]';
		$sda_data = array(
			0 => array( $sda_data_name_prefix . '[meta_key]', '', '' ),
			1 => array( $sda_data_name_prefix . '[m_type]', $m_type_select, 'mcq', false, false, false, true, array( 'fit' ) ),
			2 => array( $sda_data_name_prefix . '[key]', '0', __( '{key}', 'ipt_fsqm' ) ),
		);
		$sda_items = array();
		$sda_max_key = null;
		$sda_items_name_prefix = 'settings[summary][meta][%d]';
		foreach ( (array) $op['meta'] as $meta_key => $metadata ) {
			$sda_max_key = max( array( $sda_max_key, $meta_key ) );
			$sda_items[] = array(
				0 => array( sprintf( $sda_items_name_prefix . '[meta_key]', $meta_key ), $metadata['meta_key'], '' ),
				1 => array( sprintf( $sda_items_name_prefix . '[m_type]', $meta_key ), $m_type_select, $metadata['m_type'], false, false, false, true, array( 'fit-text' ) ),
				2 => array( sprintf( $sda_items_name_prefix . '[key]', $meta_key ), $metadata['key'], __( '{key}', 'ipt_fsqm' ) ),
			);
		}
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[summary][id_format]', __( 'Submission ID format', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[summary][id_format]', $op['id_format'], __( 'Disabled', 'ipt_fsqm' ), 'widefat', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'A direct <a href="http://php.net/manual/en/function.sprintf.php" target="_blank">sprintf format</a> to customize the output of the ID in the summary table.<br /><ul><li><code>%1$d</code>: Replaced by the submission ID.</li><li><code>%2$s</code>: Replaced by a formatted datetime.</li><li><code>%3$s</code>: Replaced by database stored datetime. It is modified by datetime format of the next field.</li></ul>', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[summary][id_dt_format]', __( 'Submission ID Datetime format', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[summary][id_dt_format]', $op['id_dt_format'], __( 'Disabled', 'ipt_fsqm' ), 'widefat', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you want to modify the output of <code>%3$s</code> in the ID, then do it here. It accepts any <a href="http://php.net/manual/en/function.date.php" target="_blank">datetime formatting string applicable to PHP</a>.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<?php foreach ( $booleans as $key => $val ) : ?>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[summary][' . $key . ']', $val ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[summary][' . $key . ']', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op[$key] ); ?>
			</td>
			<td></td>
		</tr>
		<?php endforeach; ?>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[summary][correct_color]', __( 'Correct Answer Color', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->colorpicker( 'settings[summary][correct_color]', $op['correct_color'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Choose accent color of the correct answer. Works only if highlight correct answer is enabled. Default: <code>#519548</code>', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[summary][score_title]', __( 'Individual Score Title', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[summary][score_title]', $op['score_title'], __( 'Write Here', 'ipt_fsqm' ), 'widefat', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'The title of the individual score cells.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[summary][tscore_title]', __( 'Total Score Title', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[summary][tscore_title]', $op['tscore_title'], __( 'Write Here', 'ipt_fsqm' ), 'widefat', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'The title of the total score cell.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[summary][tscore_output]', __( 'Total Score Output', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[summary][tscore_output]', $op['tscore_output'], __( 'Write Here', 'ipt_fsqm' ), 'widefat', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'The output of the total score cell. <code>%1$s</code> is replaced by formatted obtained score, <code>%2$s</code> is replaced by formatted total score. <code>%3$s</code> is replaced by percentage. Escape <code>%</code> by <code>%%</code> as this string is passed to <code>sprintf</code> family of functions.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[summary][ascore_title]', __( 'Average Score Title', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[summary][ascore_title]', $op['ascore_title'], __( 'Write Here', 'ipt_fsqm' ), 'widefat', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'The title of the average score cell. <code>%1$d</code> will be replaced by total number of submissions.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[summary][ascore_output]', __( 'Average Score Output', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[summary][ascore_output]', $op['ascore_output'], __( 'Write Here', 'ipt_fsqm' ), 'widefat', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'The output of the average score cell. <code>%1$s</code> is replaced by formatted obtained score, <code>%2$s</code> is replaced by formatted total score. <code>%3$s</code> is replaced by percentage. Escape <code>%</code> by <code>%%</code> as this string is passed to <code>sprintf</code> family of functions.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[summary][blacklist]', __( 'Blacklisted Elements', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[summary][blacklist]', $op['blacklist'], __( 'Write Here', 'ipt_fsqm' ), 'widefat', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you want to hide one or more elements, then mention them here in a comma separated way. For example, <code>L0,D0,M2,F1,O2</code> will blacklist (and there by would not show) Tab 0 (first container in the current order of the form), Design Element 0, MCQ element 2, Feedback Element 1 and Other element 2.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[summary][before]', __( 'Before Summary (HTML)', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[summary][before]', $op['before'], __( 'Write Here', 'ipt_fsqm' ), 'widefat', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you want anything to appear before the summary tables, then put it here.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[summary][after]', __( 'After Summary (HTML)', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[summary][after]', $op['after'], __( 'Write Here', 'ipt_fsqm' ), 'widefat', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you want anything to appear after the summary tables, then put it here.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'settings[summary][meta_position]', __( 'Show Additional Rows At', 'ipt_fsqm' ) ); ?>
			</th>
			<td>
				<?php
				$this->ui->select(
					'settings[summary][meta_position]',
					[
						[
							'value' => 'top',
							'label' => __( 'Beginning of the Table', 'ipt_fsqm' ),
						],
						[
							'value' => 'middle',
							'label' => __( 'After User Fields', 'ipt_fsqm' ),
						],
						[
							'value' => 'afterScore',
							'label' => __( 'After Score Data', 'ipt_fsqm' ),
						],
						[
							'value' => 'bottom',
							'label' => __( 'Bottom of the Table', 'ipt_fsqm' ),
						],
					],
					$op['meta_position']
				);
				?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Select the position in the table where the additional rows show up (if any).', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th colspan="2">
				<?php $this->ui->generate_label( 'settings[summary][meta]', __( 'Additional Rows', 'ipt_fsqm' ) ); ?>
			</th>
			<td>
				<?php $this->ui->help( __( 'Add here additional rows you want to show at the specified position. You can select the user entered value of any field or a custom value. In the custom value, you can enter any format string or just a hardcoded value.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<?php
				$this->ui->sda_list(
					[
						'columns' => $sda_columns,
						'labels' => $sda_labels,
						'features' => [
							'draggable' => false,
						],
					],
					$sda_items,
					$sda_data,
					$sda_max_key
				);
				?>
			</td>
		</tr>
	</tbody>
</table>
		<?php
	}

	public function trackback() {
		$op = $this->settings['trackback'];
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[trackback][show_full]', __( 'Show Full Submission', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[trackback][show_full]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['show_full'] ) ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enable if you want to show the full submission form with it\'s unchanged appearance. Do note that tabbed and paginated forms will be shown in a single page.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[trackback][full_title]', __( 'Full Submission Title', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[trackback][full_title]', $op['full_title'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( 'The title that is shown above the full submission. Leave empty to disable.', 'ipt_fsqm' ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[trackback][show_print]', __( 'Show Print Submission', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[trackback][show_print]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['show_print'] ) ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enable if you want to show the print submission summary. This is same as sent in the email and what ever settings you set in the summary area will reflect here.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[trackback][print_title]', __( 'Print Submission Title', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[trackback][print_title]', $op['print_title'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( 'The title that is shown above the print summary. Leave empty to disable.', 'ipt_fsqm' ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[trackback][show_trends]', __( 'Show Trends', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[trackback][show_trends]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['show_trends'] ) ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Whether to show a trends of the same form on the same page. This is to give the user a quick comparison look on his/her submission.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[trackback][trends_title]', __( 'Trends Title', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[trackback][trends_title]', $op['trends_title'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( 'The title shown before the trends/report.', 'ipt_fsqm' ); ?>
			</td>
		</tr>
	</tbody>
</table>
		<?php
	}

	public function email_template() {
		$op = $this->settings['email_template'];
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[email_template][accent_bg]', __( 'Accent Background Color', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->colorpicker( 'settings[email_template][accent_bg]', $op['accent_bg'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Choose accent background color. This is usually the background color of table headings. Default: <code>#0db9ea</code>', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[email_template][accent_color]', __( 'Accent Color', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->colorpicker( 'settings[email_template][accent_color]', $op['accent_color'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Choose accent color. This is usually the text color of table headings. Default: <code>#ffffff</code>', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[email_template][t_color]', __( 'Table Background & Border', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->colorpicker( 'settings[email_template][t_color]', $op['t_color'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Choose email background and table border color. Default: <code>#f6f4f5</code>. Please provide a lighter color code for better readability.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[email_template][color]', __( 'Text Color', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->colorpicker( 'settings[email_template][color]', $op['color'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Choose all text color. Default: <code>#999999</code>', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[email_template][h_color]', __( 'Heading Color', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->colorpicker( 'settings[email_template][h_color]', $op['h_color'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Choose heading text color. Default: <code>#333333</code>', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[email_template][m_color]', __( 'Message Color', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->colorpicker( 'settings[email_template][m_color]', $op['m_color'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Choose message text color. Default: <code>#95a5a6</code>', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[email_template][a_color]', __( 'Anchor Color', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->colorpicker( 'settings[email_template][a_color]', $op['a_color'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Choose anchor (link) text color. Default: <code>#1155cc</code>', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
	</tbody>
</table>
		<?php
	}

	public function social() {
		$op = $this->settings['social'];
		$texts = array(
			'facebook_app' => array( __( 'Facebook APP ID', 'ipt_fsqm' ), sprintf( __( 'Set the facebook app ID. This is required as only dialog feeds now support custom message and images. To learn about getting app id, <a target="_blank" href="%1$s">click here</a>.', 'ipt_fsqm' ), 'https://wpquark.com/kb/misc/social-apps/creating-a-facebook-app-and-enabling-login/' ) ),
			'url' => array( __( 'URL of the Form', 'ipt_fsqm' ), __( 'Set the URL where the form is shown. If you use <code>%SELF%</code> then it will be replaced by the standalone link.', 'ipt_fsqm' ) ),
			'fb_url' => array( __( 'Facebook Redirect URL', 'ipt_fsqm' ), __( 'Set the URL where the user would be redirected after a successful share. Make sure the domain is in apps list.', 'ipt_fsqm' ) ),
			'fb_hash' => array( __( 'Facebook Share Hash', 'ipt_fsqm' ), __( 'Set the single hashtag for facebook share. Write without the hashtag itself.', 'ipt_fsqm' ) ),
			'title' => array( __( 'Share Title', 'ipt_fsqm' ), __( 'Set the share title. Using <code>%NAME%</code> will make use of the form name.', 'ipt_fsqm' ) ),
			'description' => array( __( 'Share Description', 'ipt_fsqm' ), __( 'Set the description that is used by different networks. It has the same set of format string available in notification emails. For twitter, google plus and pinterest, it will be appended after the sharing title.', 'ipt_fsqm' ) ),
			'twitter_via' => array( __( 'Twitter Via', 'ipt_fsqm' ), __( 'Set the name of the twitter profile via which the share is sent.', 'ipt_fsqm' ) ),
			'twitter_hash' => array( __( 'Twitter Hashtags', 'ipt_fsqm' ), __( 'Enter comma separated hashtags to be used in twitter share.', 'ipt_fsqm' ) ),
		);
		$sites_select = array();
		$sites_select[] = array(
			'value' => 'facebook_url',
			'label' => __( 'Facebook', 'ipt_fsqm' ),
		);
		$sites_select[] = array(
			'value' => 'twitter_url',
			'label' => __( 'Twitter', 'ipt_fsqm' ),
		);
		$sites_select[] = array(
			'value' => 'google_url',
			'label' => __( 'Google Plus', 'ipt_fsqm' ),
		);
		$sites_select[] = array(
			'value' => 'pinterest_url',
			'label' => __( 'Pinterest', 'ipt_fsqm' ),
		);
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[social][show]', __( 'Show Social Share Buttons', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[social][show]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['show'], '1', false, true, array(
					'condid' => 'ipt_fsqm_social_sites_wrap,ipt_fsqm_social_image_wrap,ipt_fsqm_social_facebook_app_wrap,ipt_fsqm_social_url_wrap,ipt_fsqm_social_fb_url_wrap,ipt_fsqm_social_title_wrap,ipt_fsqm_social_description_wrap,ipt_fsqm_social_twitter_via_wrap,ipt_fsqm_social_twitter_hash_wrap,ipt_fsqm_social_fos_wrap,ipt_fsqm_social_aau_wrap,ipt_fsqm_social_fb_hash_wrap'
				) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enabling this setting will have effect on trackback pages, emails, success message and downloads (if using the exporter addon).', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_social_sites_wrap">
			<th><?php $this->ui->generate_label( 'settings[social][sites]', __( 'Active Social Sites', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php foreach ( $sites_select as $social ) : ?>
				<div style="display: inline-block; max-width: 150px">
				<?php $this->ui->generate_label( 'settings[social][sites][' . $social['value'] . ']', $social['label'] ); ?>
				<?php $this->ui->toggle( 'settings[social][sites][' . $social['value'] . ']', __( 'On', 'ipt_fsqm' ), __( 'Off', 'ipt_fsqm' ), $op['sites'][$social['value']] ); ?>
				</div>
				<?php endforeach; ?>
			</td>
			<td><?php $this->ui->help( __( 'Select the social networking sites for which you want the button to appear.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_social_image_wrap">
			<th><?php $this->ui->generate_label( 'settings[social][image]', __( 'Share Image', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->upload( 'settings[social][image]', $op['image'], '', __( 'Set Image', 'ipt_fsqm' ), __( 'Choose Image', 'ipt_fsqm' ), __( 'Use Image', 'ipt_fsqm' ), '90%', '300px', 'auto' ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'The share image used mainly by facebook. If given, this will also render the pinterest link.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_social_fos_wrap">
			<th><?php $this->ui->generate_label( 'settings[social][follow_on_social]', __( 'Forward URL tracking to Social Sharing Links', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[social][follow_on_social]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['follow_on_social'] ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Enabling this will forward the URL tracking key/value parameter to any link you have provided for social sharing. Enable this to track social sharing performace for sharers. Do note that this will work only if you have set URL tracking from Form Settings > Submissions.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_social_aau_wrap">
			<th><?php $this->ui->generate_label( 'settings[social][auto_append_user]', __( 'Auto Append username to URL tracking on Social Sharing Links', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[social][auto_append_user]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['auto_append_user'] ); ?>
			</td>
			<td><?php $this->ui->help( __( 'If enabled, then this will automatically append usernames on URL tracking key. This will happen only if no other tracking value is present. Use this for automatically generating trackable URLs for registered users.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<?php foreach ( $texts as $key => $val ) : ?>
		<tr id="ipt_fsqm_social_<?php echo $key; ?>_wrap">
			<th><?php $this->ui->generate_label( 'settings[social][' . $key . ']', $val[0] ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[social][' . $key . ']', $op[$key], '', 'fit', 'normal', array('code') ); ?>
			</td>
			<td>
				<?php $this->ui->help( $val[1] ); ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
		<?php
	}

	public function intg_conditional() {
		$this->build_conditional( 'settings[integration]', $this->settings['integration']['conditional'], __( 'Enable Conditional Logic for integration calls', 'ipt_fsqm' ), false, '[conditional]', __( 'Call integration only if the conditions are met', 'ipt_fsqm' ) );
	}

	public function intg_instant() {
		$op = $this->settings['integration']['instant'];
		$name_prefix = 'settings[integration][instant]';

		$items = [];
		$items[] = [
			'name' => $name_prefix . '[enabled]',
			'label' => __( 'Enable Instant Subscription', 'ipt_fsqm' ),
			'description' => __( 'Calls third-party subscription before submitting the form, as soon as Name and Email are ready.', 'ipt_fsqm' ),
			'ui' => 'toggle',
			'param' => [
				$name_prefix . '[enabled]',
				__( 'Yes', 'ipt_fsqm' ),
				__( 'No', 'ipt_fsqm' ),
				$op['enabled'],
				'1',
				false,
				true,
				[
					'condid' => 'ipt_fsqm_intg_instant_min_tab_wrap,ipt_fsqm_intg_instant_debounce_wrap'
				]
			],
			'help' => __( 'If enabled, eForm will not wait until form is submitted to call third-party integrations. Instead as soon as First Name, Last Name and Email is filled, it will call up the integration. It does not send additional metadata and works only for the Name and Email.', 'ipt_fsqm' ),
		];
		$items[] = [
			'name' => $name_prefix . '[min_tab]',
			'label' => __( 'Active Page to call', 'ipt_fsqm' ),
			'description' => __( 'Starting with 0', 'ipt_fsqm' ),
			'ui' => 'spinner',
			'id' => 'ipt_fsqm_intg_instant_min_tab_wrap',
			'param' => [
				$name_prefix . '[min_tab]',
				$op['min_tab'],
				__( 'Any page', 'ipt_fsqm' ),
				'0',
				'',
				'1'
			],
			'help' => __( 'The user must be in this page before the instant subscription is called.', 'ipt_fsqm' ),
		];
		$items[] = [
			'name' => $name_prefix . '[debounce]',
			'label' => __( 'Wait For', 'ipt_fsqm' ),
			'description' => __( 'milliseconds', 'ipt_fsqm' ),
			'ui' => 'spinner',
			'id' => 'ipt_fsqm_intg_instant_debounce_wrap',
			'param' => [
				$name_prefix . '[debounce]',
				$op['debounce'],
				__( '2500', 'ipt_fsqm' ),
				'0',
				'',
				'1'
			],
			'help' => __( 'Number of milliseconds to wait for before the instant subscription is called.', 'ipt_fsqm' ),
		];

		$this->ui->form_table( $items );
	}

	public function woocommerce() {
		$woocommerce_dir = 'woocommerce/woocommerce.php';
		if ( ! is_plugin_active( $woocommerce_dir ) ) {
			$installation_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . dirname( $woocommerce_dir ) ), 'install-plugin_' . dirname( $woocommerce_dir ) );
			// Check if plugin is installed but not activated
			if ( is_dir( dirname( WP_PLUGIN_DIR . '/' . $woocommerce_dir ) ) && is_plugin_inactive( $woocommerce_dir ) ) {
				$installation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $woocommerce_dir, 'activate-plugin_' . $woocommerce_dir );
			}
			$this->ui->msg_error( sprintf( __( '<strong>WooCommerce Integration</strong> requires <a href="%1$s">WooCommerce Plugin</a> installed and activated.', 'ipt_fsqm_mc' ), $installation_url ) );

			return;
		}
		$op = $this->settings['payment']['woocommerce'];
		$redirects = array(
			0 => array(
				'label' => __( 'Cart Page', 'ipt_fsqm' ),
				'value' => 'cart',
			),
			1 => array(
				'label' => __( 'Checkout Page', 'ipt_fsqm' ),
				'value' => 'checkout',
			),
			2 => array(
				'label' => __( 'Default (as set in Form Settings Redirection)', 'ipt_fsqm' ),
				'value' => 'default',
			),
		);
		$woocommerce_statuses = wc_get_order_statuses();
		$woo_status_items = array();
		foreach ( $woocommerce_statuses as $status => $st_name ) {
			// Standardise status names.
			// https://docs.woocommerce.com/wc-apidocs/source-class-WC_Abstract_Order.html#2340
			$status = 'wc-' === substr( $status, 0, 3 ) ? substr( $status, 3 ) : $status;
			$woo_status_items[] = array(
				'value' => $status,
				'label' => $st_name,
			);
		}
		// Backward compatible prior 4.6.1 to mark the paid flag state
		if ( ! is_array( $op['paid_flag_state'] ) ) {
			$op['paid_flag_state'] = [ $op['paid_flag_state'] ];
		}

		// Conditional Product ID
		//build_conditional_config( $name_prefix, $configs, $cond_suffix, $cond_id, $data )
		$cond_pid_data = array();
		foreach ( (array) $op['cond_pid'] as $item_key => $item ) {
			$new_cond_pid_data = array();
			foreach ( $item as $data_key => $data ) {
				if ( 'logics' == $data_key ) {
					$new_cond_pid_data[ $data_key ] = $data;
				} else {
					$new_cond_pid_data[ $data_key ] = array( 'settings[payment][woocommerce][cond_pid][' . $item_key . '][' . $data_key . ']', $data, __( 'Required', 'ipt_fsqm' ) );
				}
			}
			if ( ! isset( $new_cond_pid_data['logics'] ) ) {
				$new_cond_pid_data['logics'] = array();
			}
			$cond_pid_data[ $item_key ] = $new_cond_pid_data;
		}

		$cond_pid = array(
			'name_prefix' => 'settings[payment][woocommerce][cond_pid]',
			'configs' => array(),
			'cond_suffix' => 'logics',
			'cond_id' => 'eform_woointg_cond_pid_wrap',
			'data' => $cond_pid_data,
		);
		$cond_pid['configs'][0] = array(
			'label' => __( 'Product ID', 'ipt_fsqm' ),
			'type' => 'spinner',
			'size' => '100',
			'data' => array( 'settings[payment][woocommerce][cond_pid][__SDAKEY__][pid]', '', __( 'Required', 'ipt_fsqm' ) ),
		);

		// WooCommerce Modes
		$woo_checkout_modes = [
			[
				'value' => 'single',
				'label' => __( 'Single Product Checkout', 'ipt_fsqm' ),
				'data' => [
					'condid' => 'ipt_fsqm_intg_wc_msg,ipt_fsqm_intg_wc_summary_wrap,ipt_fsqm_intg_wc_math_wrap,ipt_fsqm_intg_wc_aat_wrap,ipt_fsqm_intg_wc_qty_wrap,ipt_fsqm_intg_wc_pfs_wrap,ipt_fsqm_intg_wc_pid_wrap',
				],
			],
			[
				'value' => 'multiple',
				'label' => __( 'Multiple Product Checkout', 'ipt_fsqm' ),
				'data' => [
					'condid' => 'ipt_fsqm_intg_wc_mm_msg,ipt_fsqm_intg_wc_mm_pids_wrap',
				],
			],
		];

		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][woocommerce][enabled]', __( 'Enable WooCommerce Integration', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[payment][woocommerce][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, array(
					'condid' => 'ipt_fsqm_intg_wc_all_config',
				) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'WooCommerce Integration allows you to dynamically add a product to the cart while changing its checkout value through the form. The product purchase will store the attributes from mathematical evaluator that you select.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
	</tbody>
</table>
<table class="form-table" id="ipt_fsqm_intg_wc_all_config">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[payment][woocommerce][send_email_after_checkout]', __( 'Send User Email Only After Checkout', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[payment][woocommerce][send_email_after_checkout]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['send_email_after_checkout'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If this is enabled, then user notification email is sent only when use has successfully checked out the WooCommerce cart. If disabled, then user notification email is sent immediately after form submission.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'settings[payment][woocommerce][mode]', __( 'Working Mode', 'ipt_fsqm' ) ); ?>
			</th>
			<td>
				<?php
					$this->ui->select(
						'settings[payment][woocommerce][mode]',
						$woo_checkout_modes,
						$op['mode'],
						false,
						true
					);
				?>
			</td>
			<td>
				<?php $this->ui->help( __( 'You can either add a single product with the integration, which gives you many features and flexibility, or you can add multiple products which has limited functionality.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_intg_wc_msg">
			<td colspan="3">
				<?php ob_start(); ?>
				<?php _e( 'WooCommerce Single Product Checkout works in a different way. eForm would just add the product you have selected with modified pricing, as calculated from the form.', 'ipt_fsqm' ); ?>
				<?php _e( 'The variables in the mathematical formula will be used to create attributes on the go. eForm will not store any payment information directly as with PayPal or Stripe payment. The checkout functionality would be managed by WooCommerce and eForm would behave just like a form, not a checkout page.', 'ipt_fsqm' ); ?>
				<br/><br/>
				<?php _e( 'eForm will however track the checkout lifecycle and will show whether the product has been checked out by the user or not. This gives you the ability to hide submission if payment has not been made.', 'ipt_fsqm' ); ?>
				<br /><br />
				<?php _e( 'You do not need to enable the Payment settings for WooCommerce integration to work. Also you do not need to place a payment element inside the form.', 'ipt_fsqm' ); ?>
				<?php _e( 'Just place a mathematical element and this will work straight out of the box. If you have conditional logic enabled on the mathematical element, and if the element stays conditionally hidden, then WooCommerce integration would not execute.', 'ipt_fsqm' ); ?>
				<?php _e( 'If you do not mention a mathematical element, then eForm will just add the relevant product without modifying the price.', 'ipt_fsqm' ); ?>
				<?php $this->ui->msg_update( ob_get_clean() ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_intg_wc_mm_msg">
			<td colspan="3">
				<?php ob_start(); ?>
				<?php _e( 'WooCommerce Multiple Product Checkout works as products selector. You can mention multiple product Ids in the <strong>WooCommerce Product IDS</strong> field, separated by comma (like <code>435,567,100</code>) and all of them will be added.', 'ipt_fsqm' ); ?>
				<?php _e( 'Additionally all products from conditional selector, whose condition is truthy will be added to the cart.', 'ipt_fsqm' ); ?>
				<?php _e( 'eForm will not keep track of whether the user checks the product out or not, but can only redirect to the cart or checkout page once the form submits.', 'ipt_fsqm' ); ?>
				<br /><br />
				<?php _e( 'You do not need to enable the Payment settings for WooCommerce integration to work. Also you do not need to place a payment element inside the form.', 'ipt_fsqm' ); ?>
				<?php $this->ui->msg_update( ob_get_clean() ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_intg_wc_summary_wrap">
			<td colspan="3">
				<table class="form-table">
					<tbody>
						<tr>
							<th><?php $this->ui->generate_label( 'settings[payment][woocommerce][show_in_summary]', __( 'Show Payment Status in Summary Table', 'ipt_fsqm' ) ); ?></th>
							<td>
								<?php $this->ui->toggle( 'settings[payment][woocommerce][show_in_summary]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['show_in_summary'], '1', false, true, array(
									'condid' => 'ipt_fsqm_intg_wc_sum_title_wrap',
								) ); ?>
							</td>
							<td>
								<?php $this->ui->help( __( 'Show WooCommerce Payment Status in Summary Table.', 'ipt_fsqm' ) ); ?>
							</td>
						</tr>
						<tr id="ipt_fsqm_intg_wc_sum_title_wrap">
							<th><?php $this->ui->generate_label( 'settings[payment][woocommerce][summary_title]', __( 'Summary Table Title', 'ipt_fsqm' ) ); ?></th>
							<td>
								<?php $this->ui->text( 'settings[payment][woocommerce][summary_title]', $op['summary_title'], __( 'Required', 'ipt_fsqm' ) ); ?>
							</td>
							<td><?php $this->ui->help( __( 'Enter the title text for WooCommerce Payment row on summary table.', 'ipt_fsqm' ) ); ?></td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
		<tr id="ipt_fsqm_intg_wc_pid_wrap">
			<th><?php $this->ui->generate_label( 'settings[payment][woocommerce][product_id]', __( 'WooCommerce Product ID', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->spinner( 'settings[payment][woocommerce][product_id]', $op['product_id'], __( 'Product ID', 'ipt_fsqm' ) ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Please enter the ID of the product which will be used to function the cart.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_intg_wc_mm_pids_wrap">
			<th><?php $this->ui->generate_label( 'settings[payment][woocommerce][mm_product_ids]', __( 'WooCommerce Product IDS (Comma separated)', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[payment][woocommerce][mm_product_ids]', $op['mm_product_ids'], __( 'Product IDs', 'ipt_fsqm' ) ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Please enter the IDs of the product which will be used to function the cart.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_intg_wc_math_wrap">
			<th><?php $this->ui->generate_label( 'settings[payment][woocommerce][mathematical]', __( 'Mathematical Element ID', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->spinner( 'settings[payment][woocommerce][mathematical]', $op['mathematical'], __( 'Math ID', 'ipt_fsqm' ) ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Please enter the ID of the mathematical element which will be used to calculate the total value. The variables inside the mathematical formula will be used to create product variation on the fly. If you have conditional logic enabled on the mathematical element, and if the element stays conditionally hidden, then WooCommerce integration would not execute.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_intg_wc_aat_wrap">
			<th><?php $this->ui->generate_label( 'settings[payment][woocommerce][additional_attr]', __( 'Additional Attributes', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[payment][woocommerce][additional_attr]', $op['additional_attr'], __( 'Comma Separated: F0,M1,M2', 'ipt_fsqm' ) ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Enter here the element ID (<code>F0,M1,M2</code>) in CSV format to include in product attributes. The variables from the mathematical elements are always added, so you dont need to include them here.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_intg_wc_qty_wrap">
			<th><?php $this->ui->generate_label( 'settings[payment][woocommerce][quantity_item]', __( 'Element ID to override Quantity', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[payment][woocommerce][quantity_item]', $op['quantity_item'], __( 'Single Element ID: M8', 'ipt_fsqm' ) ); ?>
			</td>
			<td><?php $this->ui->help( __( 'If you wish to let user choose quantity from the form, then either enter a feedback small element with validation set to numeric only or a single slider element. Then enter the field ID here, like <code>M8</code> or <code>F2</code>.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_intg_wc_pfs_wrap">
			<th><?php $this->ui->generate_label( 'settings[payment][woocommerce][paid_flag_state][]', __( 'Order Status to consider payment complete', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->checkboxes( 'settings[payment][woocommerce][paid_flag_state][]', $woo_status_items, $op['paid_flag_state'] ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Please set the order status where eForm would consider the payment status to be complete. This would essentially unlock submission data to user if <strong>Submission Visible after Successful Payment</strong> is enabled in the general settings.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_intg_wc_rd_wrap">
			<th><?php $this->ui->generate_label( 'settings[payment][woocommerce][redirect]', __( 'Redirect To', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->select( 'settings[payment][woocommerce][redirect]', $redirects, $op['redirect'] ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Please set the redirection type. You can either redirect to the cart page or the checkout page. To set redirection to any other URL, first select Default here and set the URL from Form Settings > Redirection.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_intg_wc_cpid_wrap">
			<th colspan="2"><?php $this->ui->generate_label( 'settings[payment][woocommerce][cond_pid]', __( 'Conditional Product Selector', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->help( __( 'If you wish to control product ID based on conditional logic, then please add logics here. The ones satisfied, in the order top to bottom, will modify the product ID.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_intg_wc_cpidl_wrap">
			<td colspan="3">
				<?php $this->build_conditional_config( $cond_pid['name_prefix'], $cond_pid['configs'], $cond_pid['cond_suffix'], $cond_pid['cond_id'], $cond_pid['data'] ); ?>
			</td>
		</tr>
	</tbody>
</table>
		<?php
	}

	public function estimation_slider() {
		$op = $this->settings['payment']['estimation'];
		$name_prefix = 'settings[payment][estimation]';

		// Enabled
		$enabled = array();
		$enabled[] = array(
			'name' => $name_prefix . '[enabled]',
			'label' => __( 'Enable Payment Estimator Bubble', 'ipt_fsqm' ),
			'ui' => 'toggle',
			'param' => array( $name_prefix . '[enabled]', '', '', $op['enabled'], '1', false, true, array(
				'condid' => 'eform-settings-payment-estimation-config-wrap',
			) ),
			'help' => __( 'Enable this to show a payment estimator slider/range on top of the form.', 'ipt_fsqm' ),
		);
		$items = array();
		// UI Type
		$ui_types = array(
			array(
				'label' => __( 'Slider', 'ipt_fsqm' ),
				'value' => 'slider',
				'data' => array(
					'condid' => 'eform-set-pm-cf-math_total-wrap',
				),
			),
			array(
				'label' => __( 'Range', 'ipt_fsqm' ),
				'value' => 'range',
				'data' => array(
					'condid' => 'eform-set-pm-cf-math_min-wrap,eform-set-pm-cf-math_max-wrap',
				),
			),
		);
		$items[] = array(
			'name' => $name_prefix . '[ui_type]',
			'label' => __( 'Interface Type', 'ipt_fsqm' ),
			'ui' => 'select',
			'param' => array( $name_prefix . '[ui_type]', $ui_types, $op['ui_type'], false, true ),
			'help' => __( 'Select whether you want a slider or a range on the estimator.', 'ipt_fsqm' ),
		);
		// Slider Type
		$slider_types = array(
			array(
				'label' => __( 'Block Appearance', 'ipt_fsqm' ),
				'value' => 'block',
			),
			array(
				'label' => __( 'Rounded with Knob', 'ipt_fsqm' ),
				'value' => 'knob',
			),
		);
		$items[] = array(
			'name' => $name_prefix . '[slider]',
			'label' => __( 'Slider Appearance', 'ipt_fsqm' ),
			'ui' => 'select',
			'param' => array( $name_prefix . '[slider]', $slider_types, $op['slider'] ),
			'help' => __( 'Set the appearance of the slider.', 'ipt_fsqm' ),
			'id' => 'eform-set-pm-cf-slider-wrap',
		);
		// Total
		$items[] = array(
			'name' => $name_prefix . '[math_total]',
			'label' => __( 'Math Formula for Total', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => array( $name_prefix . '[math_total]', $op['math_total'], __( 'Required', 'ipt_fsqm' ) ),
			'help' => __( 'Enter the math formula for the total value.', 'ipt_fsqm' ),
			'id' => 'eform-set-pm-cf-math_total-wrap',
		);
		// Min
		$items[] = array(
			'name' => $name_prefix . '[math_min]',
			'label' => __( 'Math Formula for Min', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => array( $name_prefix . '[math_min]', $op['math_min'], __( 'Required', 'ipt_fsqm' ) ),
			'help' => __( 'Enter the math formula for the minimum value of the range.', 'ipt_fsqm' ),
			'id' => 'eform-set-pm-cf-math_min-wrap',
		);
		// Max
		$items[] = array(
			'name' => $name_prefix . '[math_max]',
			'label' => __( 'Math Formula for Max', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => array( $name_prefix . '[math_max]', $op['math_max'], __( 'Required', 'ipt_fsqm' ) ),
			'help' => __( 'Enter the math formula for the maximum value of the range.', 'ipt_fsqm' ),
			'id' => 'eform-set-pm-cf-math_max-wrap',
		);
		// Max value
		$items[] = array(
			'name' => $name_prefix . '[max]',
			'label' => __( 'Maximum Slider Value', 'ipt_fsqm' ),
			'ui' => 'spinner',
			'param' => array( $name_prefix . '[max]', $op['max'], __( 'Required', 'ipt_fsqm' ) ),
			'help' => __( 'Enter the maximum value that the slider or range can hold.', 'ipt_fsqm' ),
			'id' => 'eform-set-pm-cf-max-wrap',
		);
		// decimal precision
		$items[] = array(
			'name' => $name_prefix . '[decimal]',
			'label' => __( 'Decimal Precision', 'ipt_fsqm' ),
			'ui' => 'spinner',
			'param' => array( $name_prefix . '[decimal]', $op['decimal'], __( 'Required', 'ipt_fsqm' ), '0', '10', '1' ),
			'help' => __( 'Decimal precision of the calculated mathematical value.', 'ipt_fsqm' ),
			'id' => 'eform-set-pm-cf-decimal-wrap',
		);
		// Bubble
		$bubble_types = array(
			array(
				'label' => __( 'Modern', 'ipt_fsqm' ),
				'value' => 'modern',
				'data' => array(
					'condid' => 'eform-set-pm-cf-heading-wrap,eform-set-pm-cf-prefix-wrap,eform-set-pm-cf-suffix-wrap,eform-set-pm-cf-attr-wrap,eform-set-pm-cf-sep-wrap,eform-set-pm-cf-numg-wrap,eform-set-pm-cf-nums-wrap,eform-set-pm-cf-numd-wrap,eform-set-pm-cf-attrh-wrap',
				)
			),
			array(
				'label' => __( 'Simple', 'ipt_fsqm' ),
				'value' => 'simple',
				'data' => array(
					'condid' => 'eform-set-pm-cf-heading-wrap,eform-set-pm-cf-prefix-wrap,eform-set-pm-cf-suffix-wrap,eform-set-pm-cf-attr-wrap,eform-set-pm-cf-sep-wrap,eform-set-pm-cf-numg-wrap,eform-set-pm-cf-nums-wrap,eform-set-pm-cf-numd-wrap,eform-set-pm-cf-attrh-wrap',
				)
			),
			array(
				'label' => __( 'Do not show bubble', 'ipt_fsqm' ),
				'value' => 'none',
				'data' => array(
					'condid' => '',
				),
			),
		);
		$items[] = array(
			'name' => $name_prefix . '[bubble]',
			'label' => __( 'Bubble Type', 'ipt_fsqm' ),
			'ui' => 'select',
			'param' => array( $name_prefix . '[bubble]', $bubble_types, $op['bubble'], false, true ),
			'help' => __( 'Select what kind of bubble you would want to show, or simply disable.', 'ipt_fsqm' ),
		);
		// Heading
		$items[] = array(
			'name' => $name_prefix . '[heading]',
			'label' => __( 'Bubble Heading', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => array( $name_prefix . '[heading]', $op['heading'], __( 'Optional', 'ipt_fsqm' ) ),
			'help' => __( 'Enter the heading shown on top of the bubble.', 'ipt_fsqm' ),
			'id' => 'eform-set-pm-cf-heading-wrap',
		);
		// Prefix
		$items[] = array(
			'name' => $name_prefix . '[prefix]',
			'label' => __( 'Value Prefix', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => array( $name_prefix . '[prefix]', $op['prefix'], __( 'Optional', 'ipt_fsqm' ) ),
			'help' => __( 'Enter the prefix before total value.', 'ipt_fsqm' ),
			'id' => 'eform-set-pm-cf-prefix-wrap',
		);
		// Suffix
		$items[] = array(
			'name' => $name_prefix . '[suffix]',
			'label' => __( 'Value Suffix', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => array( $name_prefix . '[suffix]', $op['suffix'], __( 'Optional', 'ipt_fsqm' ) ),
			'help' => __( 'Enter the suffix before total value.', 'ipt_fsqm' ),
			'id' => 'eform-set-pm-cf-suffix-wrap',
		);
		// Separator
		$items[] = array(
			'name' => $name_prefix . '[separator]',
			'label' => __( 'Range Separator', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => array( $name_prefix . '[separator]', $op['separator'], __( 'Required', 'ipt_fsqm' ) ),
			'help' => __( 'Enter the separator that will be used in case of range.', 'ipt_fsqm' ),
			'id' => 'eform-set-pm-cf-sep-wrap',
		);
		// Grouping
		$items[] = array(
			'name' => $name_prefix . '[use_grouping]',
			'label' => __( 'Group Number', 'ipt_fsqm' ),
			'ui' => 'toggle',
			'param' => array( $name_prefix . '[use_grouping]', '', '', $op['use_grouping'] ),
			'help' => __( 'Whether or not to group number.', 'ipt_fsqm' ),
			'id' => 'eform-set-pm-cf-numg-wrap',
		);
		// Number separator
		$items[] = array(
			'name' => $name_prefix . '[num_separator]',
			'label' => __( 'Number Separator', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => array( $name_prefix . '[num_separator]', $op['num_separator'], __( 'None', 'ipt_fsqm' ) ),
			'help' => __( 'Enter the separator that will be used to format number.', 'ipt_fsqm' ),
			'id' => 'eform-set-pm-cf-nums-wrap',
		);
		// Number Decimal
		$items[] = array(
			'name' => $name_prefix . '[num_decimal]',
			'label' => __( 'Decimal Point Character', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => array( $name_prefix . '[num_decimal]', $op['num_decimal'], __( 'None', 'ipt_fsqm' ) ),
			'help' => __( 'Enter the decimal point character for your locale.', 'ipt_fsqm' ),
			'id' => 'eform-set-pm-cf-numd-wrap',
		);
		// Attribute Heading
		$items[] = array(
			'name' => $name_prefix . '[attribute_heading]',
			'label' => __( 'Attribute Heading', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => array( $name_prefix . '[attribute_heading]', $op['attribute_heading'], __( 'None', 'ipt_fsqm' ) ),
			'help' => __( 'Enter a custom attribute section heading.', 'ipt_fsqm' ),
			'id' => 'eform-set-pm-cf-attrh-wrap',
		);
		// Custom Attributes
		$sda_columns = array(
			0 => array(
				'label' => __( 'Label', 'ipt_fsqm' ),
				'size' => '50',
				'type' => 'text',
			),
			1 => array(
				'label' => __( 'Value/Pipe', 'ipt_fsqm' ),
				'size' => '50',
				'type' => 'text',
			),
		);
		// First **new** array syntax. We have already bumped to PHP5.4
		// Create the sda_items
		$sda_items = [];
		$max_key = 0;
		foreach ( (array) $op['attributes'] as $a_key => $attr ) {
			$max_key = max( [ $a_key, $max_key ] );
			$sda_items[] = [
				0 => [ "{$name_prefix}[attributes][{$a_key}][label]", $attr['label'], __( 'Required', 'ipt_fsqm' ) ],
				1 => [ "{$name_prefix}[attributes][{$a_key}][value]", $attr['value'], __( 'Static/%M0% for pipes', 'ipt_fsqm' ) ],
			];
		}
		// Data
		$sda_data = [
			0 => [ "{$name_prefix}[attributes][__SDAKEY__][label]", '', __( 'Required', 'ipt_fsqm' ) ],
			1 => [ "{$name_prefix}[attributes][__SDAKEY__][value]", '', __( 'Static/%M0% for pipes', 'ipt_fsqm' ) ],
		];
		$items[] = array(
			'name' => $name_prefix . '[attributes]',
			'ui' => 'sda_list',
			'param' => [
				[
					'columns' => $sda_columns,
					'labels' => [
						'add' => __( 'Add New Attribute', 'ipt_fsqm' ),
					],
				],
				$sda_items,
				$sda_data,
				$max_key,
			],
			'help' => __( 'Add additional attributes to the bubble. If you wish to show/pipe values from other elements, then make sure you have the interactive elements active from Form Settings.', 'ipt_fsqm' ),
			'id' => 'eform-set-pm-cf-attr-wrap',
		);
		?>
<table class="form-table" style="margin-bottom: 0;">
	<tbody>
		<?php $this->ui->form_table( $enabled, false ); ?>
	</tbody>
</table>
<table class="form-table" id="eform-settings-payment-estimation-config-wrap">
	<tbody>
		<?php $this->ui->form_table( $items, false ); ?>
	</tbody>
</table>
		<?php
	}

	public function mailchimp() {
		$op = $this->settings['integration']['mailchimp'];
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[integration][mailchimp][enabled]', __( 'Enable MailChimp Integration', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[integration][mailchimp][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, array(
					'condid' => 'ipt_fsqm_intg_mc_full_wrap'
				) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you want the submitter to get subscribed to a mailchimp list, please enable it here. After this, make sure you add the Primary Email field to the form.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
	</tbody>
</table>
<table class="form-table" id="ipt_fsqm_intg_mc_full_wrap">
	<tbody>
		<tr id="ipt_fsqm_intg_mc_api_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][mailchimp][api]', __( 'MailChimp API Key', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[integration][mailchimp][api]', $op['api'], __( 'API Key', 'ipt_fsqm' ) ); ?>
				<br />
				<span class="description"><?php printf( __( 'Get it from <a href="%s" target="_blank">here</a>.', 'ipt_fsqm' ), 'http://kb.mailchimp.com/accounts/management/about-api-keys#Find-or-Generate-Your-API-Key' ); ?></span>
			</td>
			<td>
				<?php $this->ui->help( sprintf( __( 'Set MailChimp API Key. If you need to find your API key, please read <a target="_blank" href="%1$s">this article.</a>', 'ipt_fsqm' ), 'http://kb.mailchimp.com/accounts/management/about-api-keys#Find-or-Generate-Your-API-Key' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_intg_mc_list_id_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][mailchimp][list_id]', __( 'MailChimp List ID', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[integration][mailchimp][list_id]', $op['list_id'], __( 'List ID', 'ipt_fsqm' ) ); ?>
				<br />
				<span class="description"><?php printf( __( 'Get it from <a href="%s" target="_blank">here</a>.', 'ipt_fsqm' ), 'http://kb.mailchimp.com/lists/managing-subscribers/find-your-list-id' ); ?></span>
			</td>
			<td>
				<?php $this->ui->help( sprintf( __( 'Set MailChimp List ID. If you need to find your List ID, please read <a href="%1$s">this article.</a>', 'ipt_fsqm' ), 'http://kb.mailchimp.com/lists/managing-subscribers/find-your-list-id' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_intg_mc_double_optin_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][mailchimp][double_optin]', __( 'Double Optin', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[integration][mailchimp][double_optin]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['double_optin'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Whether a double opt-in confirmation message is sent. <em>Abusing this may cause your account to be suspended.</em>', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<?php
		$this->integration_custommeta(
			$op['meta'],
			'settings[integration][mailchimp][meta]',
			'https://wpquark.com/kb/fsqm/fsqm-integration/mailchimp-integration-on-fsqm-pro/#ipt_kb_toc_4742_6'
		);
		?>
	</tbody>
</table>
		<?php
	}

	public function aweber() {
		$op = $this->settings['integration']['aweber'];
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[integration][aweber][enabled]', __( 'Enable Aweber Integration', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[integration][aweber][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, array(
					'condid' => 'ipt_fsqm_intg_aw_ac_wrap,ipt_fsqm_intg_aw_li_wrap,ipt_fsqm_intg_aw_info_wrap'
				) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you want the submitter to get subscribed to a aweber list, please enable it here. After this, make sure you add the Primary Email field to the form.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_intg_aw_ac_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][aweber][authorization_code]', __( 'Aweber Authorization Code', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->textarea( 'settings[integration][aweber][authorization_code]', $op['authorization_code'], __( 'Authorization Code', 'ipt_fsqm' ) ); ?>
				<br />
				<span class="description">
					<?php printf( __( 'You can get it from <a target="_blank" href="%s">here.</a>', 'ipt_fsqm' ), 'https://auth.aweber.com/1.0/oauth/authorize_app/9d9d3517' ); ?>
				</span>
			</td>
			<td>
				<?php $this->ui->help( __( 'Set the authorization code you get after filling out the form from the link.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_intg_aw_li_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][aweber][list_id]', __( 'List ID', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[integration][aweber][list_id]', $op['list_id'], __( 'List ID', 'ipt_fsqm' ) ); ?>
				<br />
				<span class="description"><?php printf( __( 'Get it from <a href="%s" target="_blank">here</a>.', 'ipt_fsqm' ), 'https://help.aweber.com/hc/en-us/articles/204028426-What-Is-The-Unique-List-ID-' ); ?></span>
			</td>
			<td>
				<?php $this->ui->help( __( 'Put Aweber List ID.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_intg_aw_info_wrap">
			<td colspan="3">
				<?php if ( $op['accessKey'] != '' ) : ?>
				<span class="description"><?php _e( 'Aweber Connected Properly.', 'ipt_fsqm' ); ?></span>
				<?php else : ?>
				<span class="description">
					<?php _e( 'Either you have not entered a valid authorization code, or you have not setup aweber integration. Please give a valid authorization code, save the form and reload this page to see the status.', 'ipt_fsqm' ); ?>
				</span>
				<?php endif; ?>
			</td>
			<?php foreach ( array( 'consumerKey', 'consumerSecret', 'accessKey', 'accessSecret', 'prevac' ) as $aweber_key ) : ?>
				<input type="hidden" name="settings[integration][aweber][<?php echo esc_attr( $aweber_key ); ?>]" value="<?php echo esc_attr( $op[$aweber_key] ); ?>" />
			<?php endforeach; ?>
		</tr>
	</tbody>
</table>
		<?php
	}

	public function get_response() {
		$op = $this->settings['integration']['get_response'];
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[integration][get_response][enabled]', __( 'Enable Get Response Integration', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[integration][get_response][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, array(
					'condid' => 'ipt_fsqm_intg_gr_full_wrap'
				) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you want the submitter to get subscribed to a Get Response list, please enable it here. After this, make sure you add the Primary Email field to the form.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
	</tbody>
</table>
<table class="form-table" id="ipt_fsqm_intg_gr_full_wrap">
	<tbody>
		<tr id="ipt_fsqm_intg_gr_api_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][get_response][api]', __( 'Get Response API Key', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[integration][get_response][api]', $op['api'], __( 'API Key', 'ipt_fsqm' ) ); ?>
				<br />
				<span class="description"><?php printf( __( 'Get it from <a href="%s" target="_blank">here</a>.', 'ipt_fsqm' ), 'http://support.getresponse.com/faq/where-i-find-api-key' ); ?></span>
			</td>
			<td>
				<?php $this->ui->help( sprintf( __( 'Set Get Response API Key. If you need to find your API key, please read <a target="_blank" href="%1$s">this article.</a>', 'ipt_fsqm' ), 'http://support.getresponse.com/faq/where-i-find-api-key' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_intg_gr_campaign_id_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][get_response][campaign_id]', __( 'List Token', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[integration][get_response][campaign_id]', $op['campaign_id'], __( 'List Token', 'ipt_fsqm' ) ); ?>
				<br />
				<span class="description"><?php printf( __( 'Get it from <a href="%s" target="_blank">here</a>.', 'ipt_fsqm' ), 'https://wpquark.com/kb/fsqm/fsqm-integration/get-response-integration-with-fsqm-pro/' ); ?></span>
			</td>
			<td>
				<?php $this->ui->help( sprintf( __( 'Set List Token. This can be found under the settings > general of lists tab. If you need to find your List token, please read <a href="%1$s">this article.</a>', 'ipt_fsqm' ), 'https://wpquark.com/kb/fsqm/fsqm-integration/get-response-integration-with-fsqm-pro/' ) ); ?>
			</td>
		</tr>
		<?php
		$this->integration_custommeta(
			$op['meta'],
			'settings[integration][get_response][meta]',
			'https://wpquark.com/kb/fsqm/fsqm-integration/get-response-integration-with-fsqm-pro/#ipt_kb_toc_4774_3'
		);
		?>
	</tbody>
</table>
		<?php
	}

	public function campaign_monitor() {
		$op = $this->settings['integration']['campaign_monitor'];
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[integration][campaign_monitor][enabled]', __( 'Enable Campaign Monitor Integration', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[integration][campaign_monitor][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, array(
					'condid' => 'ipt_fsqm_intg_cm_full_wrap'
				) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you want the submitter to get subscribed to a campaign monitor list, please enable it here. After this, make sure you add the Primary Email field to the form.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
	</tbody>
</table>
<table class="form-table" id="ipt_fsqm_intg_cm_full_wrap">
	<tbody>
		<tr id="ipt_fsqm_intg_cm_api_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][campaign_monitor][api]', __( 'Campaign Monitor API Key', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[integration][campaign_monitor][api]', $op['api'], __( 'API Key', 'ipt_fsqm' ) ); ?>
				<br />
				<span class="description"><?php printf( __( 'Get it from <a href="%s" target="_blank">here</a>.', 'ipt_fsqm' ), 'http://help.campaignmonitor.com/topic.aspx?t=206' ); ?></span>
			</td>
			<td>
				<?php $this->ui->help( sprintf( __( 'Set campaign monitor API Key. If you need to find your API key, please read <a target="_blank" href="%1$s">this article.</a>', 'ipt_fsqm' ), 'http://help.campaignmonitor.com/topic.aspx?t=206' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_intg_cm_list_id_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][campaign_monitor][list_id]', __( 'Campaign Monitor List ID', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[integration][campaign_monitor][list_id]', $op['list_id'], __( 'List ID', 'ipt_fsqm' ) ); ?>
				<br />
				<span class="description"><?php printf( __( 'Get it from <a href="%s" target="_blank">here</a>.', 'ipt_fsqm' ), 'https://www.campaignmonitor.com/api/getting-started/#your-list-id' ); ?></span>
			</td>
			<td>
				<?php $this->ui->help( sprintf( __( 'Set Campaign Monitor List ID. If you need to find your List ID, please read <a href="%1$s">this article.</a>', 'ipt_fsqm' ), 'https://www.campaignmonitor.com/api/getting-started/#listid' ) ); ?>
			</td>
		</tr>
		<?php
		$this->integration_custommeta(
			$op['meta'],
			'settings[integration][campaign_monitor][meta]',
			'https://wpquark.com/kb/fsqm/fsqm-integration/campaign-monitor-integration-with-fsqm-pro/#ipt_kb_toc_4779_3'
		);
		?>
	</tbody>
</table>
		<?php
	}

	protected function integration_custommeta( $op, $name_prefix, $help = '', $parameters_list = [] ) {
		// Prepare data for sda
		$m_type_select = array(
			0 => array(
				'value' => 'mcq',
				'label' => __( '(M) MCQ', 'ipt_fsqm' ),
			),
			1 => array(
				'value' => 'freetype',
				'label' => __( '(F) Text Input', 'ipt_fsqm' ),
			),
			2 => array(
				'value' => 'pinfo',
				'label' => __( '(O) Others', 'ipt_fsqm' ),
			),
			3 => array(
				'value' => 'formatstring',
				'label' => __( 'Custom Value', 'ipt_fsqm' ),
			),
		);
		$sda_columns = array(
			0 => array(
				'label' => __( '(X)', 'ipt_fsqm' ),
				'size' => '25',
				'type' => 'select',
			),
			1 => array(
				'label' => __( '{KEY}', 'ipt_fsqm' ),
				'size' => '25',
				'type' => 'text',
			),
			2 => array(
				'label' => __( 'Field Key', 'ipt_fsqm' ),
				'size' => '50',
				'type' => 'text',
			),
		);
		if ( ! empty( $parameters_list ) ) {
			$sda_columns[2]['type'] = 'select';
			$sda_columns[2]['label'] = 'Select Field';
		}
		$sda_labels = array(
			'add' => __( 'Add New Parameter', 'ipt_fsqm' ),
		);
		$sda_data_name_prefix = $name_prefix . '[data][__SDAKEY__]';
		$sda_data = array(
			0 => array( $sda_data_name_prefix . '[m_type]', $m_type_select, 'mcq', false, false, false, true, array( 'fit' ) ),
			1 => array( $sda_data_name_prefix . '[key]', '0', __( '{key}/{value}', 'ipt_fsqm' ) ),
			2 => array( $sda_data_name_prefix . '[meta_key]', '', '' ),
		);
		if ( ! empty( $parameters_list ) ) {
			$sda_data[2] = [
				$sda_data_name_prefix . '[meta_key]',
				$parameters_list,
				array_keys( $parameters_list )[0],
				false, false, false, true, array( 'fit' )
			];
		}
		$sda_items = array();
		$sda_max_key = null;
		$sda_items_name_prefix =  $name_prefix . '[data][%d]';
		foreach ( (array) $op['data'] as $meta_key => $metadata ) {
			$sda_max_key = max( array( $sda_max_key, $meta_key ) );
			$new_item = [
				0 => array( sprintf( $sda_items_name_prefix . '[m_type]', $meta_key ), $m_type_select, $metadata['m_type'], false, false, false, true, array( 'fit-text' ) ),
				1 => array( sprintf( $sda_items_name_prefix . '[key]', $meta_key ), $metadata['key'], __( '{key}/{value}', 'ipt_fsqm' ) ),
				2 => array( sprintf( $sda_items_name_prefix . '[meta_key]', $meta_key ), $metadata['meta_key'], '' ),
			];
			if ( ! empty( $parameters_list ) ) {
				$new_item[2] = [
					sprintf( $sda_items_name_prefix . '[meta_key]', $meta_key ),
					$parameters_list,
					$metadata['meta_key'],
					false, false, false, true, array( 'fit' )
				];
			}
			$sda_items[] = $new_item;
		}

		$items = [];
		$id_prefix = $this->ui->generate_id_from_name( $name_prefix );
		// Enabled
		$items[] = [
			'name' => $name_prefix . '[active]',
			'label' => __( 'Send additional data as custom field.', 'ipt_fsqm' ),
			'ui' => 'toggle',
			'param' => [ $name_prefix . '[active]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['active'], '1', false, true, [
				'condid' => $id_prefix . 'custom_metadata_wrap',
			] ],
			'help' => __( 'If you want to send additional metadata to every this integrations then activate it. If you select the Custom Value, then you can enter any format string like <code>%FNAME%</code> or a hard-coded value.', 'ipt_fsqm' ),
			'description' =>
				$help !== ''
					? sprintf(
						__( '<a href="%s" target="_blank">Click here</a> to learn how to setup.', 'ipt_fsqm' ),
						esc_attr( $help )
					)
					: __( 'Send custom data to the integration.', 'ipt_fsqm' ),
		];
		// SDA meta>data
		$items[] = [
			'name' => $name_prefix . '[data]',
			'ui' => 'sda_list',
			'param' => [ [
				'columns' => $sda_columns,
				'labels' => $sda_labels,
				'features' => [
					'draggable' => false,
				],
			], $sda_items, $sda_data, $sda_max_key ],
			'id' => $id_prefix . 'custom_metadata_wrap'
		];
		$this->ui->form_table( $items, false );
	}

	public function formhandler_integration() {
		$op = $this->settings['integration']['formhandler'];
		// Prepare data for sda
		$m_type_select = array(
			0 => array(
				'value' => 'mcq',
				'label' => __( '(M) MCQ', 'ipt_fsqm' ),
			),
			1 => array(
				'value' => 'freetype',
				'label' => __( '(F) Text Input', 'ipt_fsqm' ),
			),
			2 => array(
				'value' => 'pinfo',
				'label' => __( '(O) Others', 'ipt_fsqm' ),
			),
			3 => array(
				'value' => 'formatstring',
				'label' => __( 'Custom Value', 'ipt_fsqm' ),
			),
		);
		$sda_columns = array(
			0 => array(
				'label' => __( '(X)', 'ipt_fsqm' ),
				'size' => '25',
				'type' => 'select',
			),
			1 => array(
				'label' => __( '{KEY}', 'ipt_fsqm' ),
				'size' => '25',
				'type' => 'text',
			),
			2 => array(
				'label' => __( 'Parameter Key (No Space, Underscore and alphabets only)', 'ipt_fsqm' ),
				'size' => '50',
				'type' => 'text',
			),
		);
		$sda_labels = array(
			'add' => __( 'Add New Parameter', 'ipt_fsqm' ),
		);
		$sda_data_name_prefix = 'settings[integration][formhandler][meta][__SDAKEY__]';
		$sda_data = array(
			0 => array( $sda_data_name_prefix . '[m_type]', $m_type_select, 'mcq', false, false, false, true, array( 'fit' ) ),
			1 => array( $sda_data_name_prefix . '[key]', '0', __( '{key}', 'ipt_fsqm' ) ),
			2 => array( $sda_data_name_prefix . '[meta_key]', '', '' ),
		);
		$sda_items = array();
		$sda_max_key = null;
		$sda_items_name_prefix = 'settings[integration][formhandler][meta][%d]';
		foreach ( (array) $op['meta'] as $meta_key => $metadata ) {
			$sda_max_key = max( array( $sda_max_key, $meta_key ) );
			$sda_items[] = array(
				0 => array( sprintf( $sda_items_name_prefix . '[m_type]', $meta_key ), $m_type_select, $metadata['m_type'], false, false, false, true, array( 'fit-text' ) ),
				1 => array( sprintf( $sda_items_name_prefix . '[key]', $meta_key ), $metadata['key'], __( '{key}', 'ipt_fsqm' ) ),
				2 => array( sprintf( $sda_items_name_prefix . '[meta_key]', $meta_key ), $metadata['meta_key'], '' ),
			);
		}
		$http_methods = array(
			'get' => __( 'Get', 'ipt_fsqm' ),
			'post' => __( 'Post', 'ipt_fsqm' ),
		);
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[integration][formhandler][enabled]', __( 'Enable Send to Custom URL', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[integration][formhandler][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, array(
					'condid' => 'ipt_fsqm_formhandler_url_wrap,ipt_fsqm_formhandler_method_wrap,ipt_fsqm_formhandler_metatitle_wrap,ipt_fsqm_formhandler_metaarray_wrap,ipt_fsqm_formhandler_meta_wrap,ipt_fsqm_formhandler_resend_on_update_wrap,ipt_fsqm_formhandler_option_delimiter_wrap,ipt_fsqm_formhandler_row_delimiter_wrap,ipt_fsqm_formhandler_range_delimiter_wrap,ipt_fsqm_formhandler_entry_delimiter_wrap',
				) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you wish to enable sending form data to custom URL, then please check this. Other settings will follow.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_formhandler_url_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][formhandler][url]', __( 'Custom URL', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[integration][formhandler][url]', $op['url'], __( 'https://', 'ipt_fsqm' ), 'large', 'normal', array( 'code' ) ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Mention the URL where the form data would be passed. It needs to start with <code>http://</code> or <code>https://</code>.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_formhandler_method_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][formhandler][method]', __( 'HTTP Method', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->select( 'settings[integration][formhandler][method]', $http_methods, $op['method'] ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Mention which http method would be used. It is recommended to use POST method, since a large number of data might be passed.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_formhandler_metatitle_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][formhandler][meta]', __( 'Data Parameters', 'ipt_fsqm' ) ); ?></th>
			<td>
				<p class="description"><?php printf( __( '%1$s will always be sent with the corresponding values.', 'ipt_fsqm' ), '<code>data_id, submission_date, user_id, f_name, l_name, email, score, max_score, ip, remarks, referrer, url_track, time, link, trackback_id, trackback_url</code>' ); ?></p>
				<p class="description"><?php _e( 'Click the help button to get a snippet to check the form data.', 'ipt_fsqm' ); ?></p>
			</td>
			<td><?php $this->ui->help_head(); ?>
				<p><?php _e( 'Add the query parameters you want to pass to the URL. You must specify a valid URL query key, otherwise the function might fail. <code>data_id, submission_date, user_id, f_name, l_name, email, score, max_score, ip, remarks, referrer, url_track, time, link, trackback_id, trackback_url</code> are always passed.', 'ipt_fsqm' ); ?></p>
				<p><?php _e( 'The following snippet can be used as a starter to get the form data.', 'ipt_fsqm' ); ?></p>
<pre style="max-width: 400px; overflow: auto; max-height: 200px;">&lt;?php
// Check if request is empty
if ( ! empty( $_REQUEST ) ) {
	// Get the request. Works both for GET and POST
	$info = $_REQUEST;
	// Save HTTP METHOD for debugging
	$info[&#39;method&#39;] = $_SERVER[&#39;REQUEST_METHOD&#39;];
	// Create the JSON
	$json = json_encode( $info );
	// Store it in a file
	$filename = dirname( __FILE__ ) . &#39;/eform-url-&#39; . ( isset( $_REQUEST[&#39;data_id&#39;] ) ? $_REQUEST[&#39;data_id&#39;] : uniqid() ) . &#39;.json&#39;;
	// We are not really checking if the file exists or not
	file_put_contents( $filename, $json );
// Request is empty, so exit
} else {
	echo &#39;Bad Request!&#39;;
}</pre>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_formhandler_metaarray_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][formhandler][metaarray]', __( 'Send Array instead of Stringified Value', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[integration][formhandler][metaarray]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['metaarray']  ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'The element submission data will be stringified before being sent. You can change this behavior by changing the toggle.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_formhandler_resend_on_update_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][formhandler][resend_on_update]', __( 'Resend webhook on update', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[integration][formhandler][resend_on_update]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['resend_on_update']  ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enable this if you want to resend the webhook during submission update.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_formhandler_meta_wrap">
			<td colspan="3">
				<?php $this->ui->sda_list( array(
					'columns' => $sda_columns,
					'labels' => $sda_labels,
					'features' => array(
						'draggable' => false,
					),
				), $sda_items, $sda_data, $sda_max_key ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_formhandler_option_delimiter_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][formhandler][option_delimiter]', __( 'Option Delimiter', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[integration][formhandler][option_delimiter]', $op['option_delimiter'], __( 'required', 'ipt_fsqm' ), 'large', 'normal', array( 'code' ) ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Multiple Option Delimiter (For questions like Multiple Options, Matrix etc). Default is <code>\n</code> (New line character).', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_formhandler_row_delimiter_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][formhandler][row_delimiter]', __( 'Row Delimiter', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[integration][formhandler][row_delimiter]', $op['row_delimiter'], __( 'required', 'ipt_fsqm' ), 'large', 'normal', array( 'code' ) ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Multiple Row Delimiter (For questions like Grading, Spinners, Matrix etc). Default is <code>\n\n</code> (Double new line characters).', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_formhandler_range_delimiter_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][formhandler][range_delimiter]', __( 'Range Delimiter', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[integration][formhandler][range_delimiter]', $op['range_delimiter'], __( 'required', 'ipt_fsqm' ), 'large', 'normal', array( 'code' ) ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Range Delimiter (For questions like Grading, Ranges etc). Default is <code>/</code>.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt_fsqm_formhandler_entry_delimiter_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][formhandler][entry_delimiter]', __( 'Entry Delimiter', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[integration][formhandler][entry_delimiter]', $op['entry_delimiter'], __( 'required', 'ipt_fsqm' ), 'large', 'normal', array( 'code' ) ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Entry Delimiter (For the options inside elements like Grading, Spinners, Matrix etc). Default is <code>:</code>.', 'ipt_fsqm' ) ); ?></td>
		</tr>
	</tbody>
</table>
		<?php
	}

	public function hint_plugin_install( $slug, $name ) {
		if ( current_user_can( 'install_plugins' ) ) {
			if ( is_dir( dirname( WP_PLUGIN_DIR . '/' . $slug ) ) && is_plugin_inactive( $slug ) ) {
				$this->ui->msg_error( sprintf( __( '%2$s is installed but inactive. Please <a href="%1$s">activate</a> the plugin.', 'ipt_fsqm' ), wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $slug, 'activate-plugin_' . $slug ), $name ) );
			} else {
				$this->ui->msg_error( sprintf( __( 'Please install <a href="%1$s">%2$s Plugin</a>.', 'ipt_fsqm' ), wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . dirname( $slug ) ), 'install-plugin_' . dirname( $slug ) ), $name ) );
			}
		} else {
			$this->ui->msg_error( sprintf( __( 'Please ask your administrator to install %1$s plugin.', 'ipt_fsqm' ), $name ) );
		}
		return;
	}

	public function mailwizz() {
		$op = $this->settings['integration']['mailwizz'];
		// Create the items
		$items = [];
		$name_prefix = 'settings[integration][mailwizz]';
		// Enabled
		$enabled = [
			'name' => $name_prefix . '[enabled]',
			'label' => __( 'Enable MailWizz Integration', 'ipt_fsqm' ),
			'ui' => 'toggle',
			'param' => [ $name_prefix . '[enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, [
				'condid' => 'ipt_fsqm_intg_mlwizz_full_wrap',
			] ],
			'help' => __( 'If you want the submitter to get subscribed to your selfhosted MailWizz list, please enable it here. After this, make sure you add the Primary Email field to the form.', 'ipt_fsqm' ),
		];
		// API URL
		$items[] = [
			'name' => $name_prefix . '[url]',
			'label' => __( 'API URL', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => [ $name_prefix . '[url]', $op['url'], __( 'API URL of MailWizz', 'ipt_fsqm' ) ],
			'help' => __( 'Enter the API URL of the MailWizz Application. Under API Keys of your Application, on the right hand side you will see a small INFO icon. Clicking on it will give API URL like <code>https://website.tld/api/index.php</code>. Copy-Paste the whole URL here.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_intg_mlwhzz_url_wrap',
		];
		// Public Key
		$items[] = [
			'name' => $name_prefix . '[pub_key]',
			'label' => __( 'Public Key', 'ipt_fsqm' ),
			'ui' => 'password',
			'param' => [ $name_prefix . '[pub_key]', $op['pub_key'], __( 'Public Key of MailWizz', 'ipt_fsqm' ) ],
			'help' => __( 'Enter a Public Key from the MailWizz Application. Under API Keys of your Application, either generate or use an existing pair of public/private keys. Paste the public key here.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_intg_mlwhzz_puk_wrap',
		];
		// Private Key
		$items[] = [
			'name' => $name_prefix . '[priv_key]',
			'label' => __( 'Private Key', 'ipt_fsqm' ),
			'ui' => 'password',
			'param' => [ $name_prefix . '[priv_key]', $op['priv_key'], __( 'Private Key of MailWizz', 'ipt_fsqm' ) ],
			'help' => __( 'Enter a Private Key from the MailWizz Application. Under API Keys of your Application, either generate or use an existing pair of public/private keys. Paste the private key here.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_intg_mlwhzz_pvk_wrap',
		];
		// List ID
		$items[] = [
			'name' => $name_prefix . '[list_id]',
			'label' => __( 'List ID', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => [ $name_prefix . '[list_id]', $op['list_id'], __( 'List ID', 'ipt_fsqm' ) ],
			'help' => __( 'Enter the list ID. You will find it under MailWizz > Lists > Unique ID. Copy the ID here.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_intg_mlwhzz_lid_wrap',
		];
		$this->ui->form_table( [
			$enabled,
		] );
		echo '<table class="form-table" id="ipt_fsqm_intg_mlwizz_full_wrap"><tbody>';
		$this->ui->form_table( $items, false );
		$this->integration_custommeta(
			$op['meta'],
			'settings[integration][mailwizz][meta]',
			'https://wpquark.com/kb/fsqm/fsqm-integration/mailwizz-integration-eform/#ipt_kb_toc_26257_2'
		);
		echo '</tbody></table>';
	}

	public function mailpoet3() {
		$op = $this->settings['integration']['mailpoet3'];
		$slug = 'mailpoet/mailpoet.php';
		if ( ! class_exists( '\MailPoet\API\API' ) ) {
			$this->hint_plugin_install( $slug, __( 'MailPoet 3', 'ipt_fsqm' ) );
			return;
		}
		// Get available lists
		$subscription_lists = \MailPoet\API\API::MP( 'v1' )->getLists();
		$lists = [];
		foreach ( (array) $subscription_lists as $list ) {
			$lists[] = [
				'value' => $list['id'],
				'label' => $list['name'],
			];
		}
		$custom_fields = \MailPoet\API\API::MP( 'v1' )->getSubscriberFields();
		$custom_fields_list = [];
		foreach ( $custom_fields as $cf ) {
			$custom_fields_list[ $cf['id'] ] = $cf['name'];
		}
		// Create the items
		$items = [];
		$name_prefix = 'settings[integration][mailpoet3]';
		// Enabled
		$enabled = [
			'name' => $name_prefix . '[enabled]',
			'label' => __( 'Enable MailPoet 3 Integration', 'ipt_fsqm' ),
			'ui' => 'toggle',
			'param' => [ $name_prefix . '[enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, [
				'condid' => 'ipt_fsqm_intg_mp3_full_wrap',
			] ],
			'help' => __( 'If you want the submitter to get subscribed to one or more MailPoet list, please enable it here. After this, make sure you add the Primary Email field to the form.', 'ipt_fsqm' ),
		];
		// List
		if ( empty( $lists ) ) {
			$items[] = [
				'ui' => 'msg_error',
				'param' => [ __( 'You have not created any list in MailPoet yet. Please create some list before getting started.', 'ipt_fsqm' ) ],
				'id' => 'ipt_fsqm_intg_mp3_list_wrap',
			];
		} else {
			$items[] = [
				'name' => $name_prefix . '[list_ids][]',
				'label' => __( 'Subscriber Lists', 'ipt_fsqm' ),
				'ui' => 'checkboxes',
				'param' => [ $name_prefix . '[list_ids][]', $lists, $op['list_ids'] ],
				'help' => __( 'Select all the lists where you would like to add the subscriber.', 'ipt_fsqm' ),
				'id' => 'ipt_fsqm_intg_mp3_list_wrap',
			];
		}
		// Confirmation
		$items[] = [
			'name' => $name_prefix . '[confirmation]',
			'label' => __( 'Send Confirmation Email', 'ipt_fsqm' ),
			'ui' => 'toggle',
			'param' => [ $name_prefix . '[confirmation]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['confirmation'] ],
			'help' => __( 'Enable to send confirmation email.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_intg_mp3_cnf_wrap',
		];
		// Welcome
		$items[] = [
			'name' => $name_prefix . '[welcome]',
			'label' => __( 'Send Welcome Email', 'ipt_fsqm' ),
			'ui' => 'toggle',
			'param' => [ $name_prefix . '[welcome]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['welcome'] ],
			'help' => __( 'Enable to send welcome email.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_intg_mp3_wlc_wrap',
		];
		$this->ui->form_table( [
			$enabled,
		] );
		echo '<table class="form-table" id="ipt_fsqm_intg_mp3_full_wrap"><tbody>';
		$this->ui->form_table( $items, false );
		$this->integration_custommeta(
			$op['meta'],
			'settings[integration][mailpoet3][meta]',
			'https://wpquark.com/kb/fsqm/fsqm-integration/mailpoet-3-integration-eform/#ipt_kb_toc_21565_3',
			$custom_fields_list
		);
		echo '</tbody></table>';
	}

	public function mailpoet() {
		$op = $this->settings['integration']['mailpoet'];
		$slug = 'wysija-newsletters/index.php';
		if ( ! class_exists( 'WYSIJA' ) ) {
			$this->hint_plugin_install( $slug, __( 'MailPoet 2', 'ipt_fsqm' ) );
			return;
		}

		$model_list = WYSIJA::get( 'list','model' );
		$mailpoet_lists = $model_list->get( array( 'name', 'list_id' ), array( 'is_enabled' => 1 ) );
		$mailpoet_list_selections = array();
		if ( ! empty( $mailpoet_lists ) ) {
			foreach ( $mailpoet_lists as $ml ) {
				$mailpoet_list_selections[] = array(
					'value' => $ml['list_id'],
					'label' => $ml['name'],
				);
			}
		}

		?>
		<table class="form-table">
			<tbody>
				<tr>
					<th><?php $this->ui->generate_label( 'settings[integration][mailpoet][enabled]', __( 'Enable MailPoet Integration', 'ipt_fsqm' ) ); ?></th>
					<td>
						<?php $this->ui->toggle( 'settings[integration][mailpoet][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, array(
							'condid' => 'ipt_fsqm_intg_mp_list_wrap'
						) ); ?>
					</td>
					<td>
						<?php $this->ui->help( __( 'If you want the submitter to get subscribed to one or more MailPoet list, please enable it here. After this, make sure you add the Primary Email field to the form.', 'ipt_fsqm' ) ); ?>
					</td>
				</tr>
				<tr id="ipt_fsqm_intg_mp_list_wrap">
					<?php if ( empty( $mailpoet_lists ) ) : ?>
					<td colspan="3">
						<?php $this->ui->msg_error( __( 'You have not created any list in Mailpoet yet. Please create at least one list and it will appear here.', 'ipt_fsqm' ) ); ?>
					</td>
					<?php else : ?>
					<th><?php $this->ui->generate_label( 'settings[integration][mailpoet][list_ids]', __( 'Select Subscriber Lists', 'ipt_fsqm' ) ); ?></th>
					<td>
						<?php $this->ui->checkboxes( 'settings[integration][mailpoet][list_ids][]', $mailpoet_list_selections, $op['list_ids'] ); ?>
					</td>
					<td>
						<?php $this->ui->help( __( 'Select the lists to which you want your subscribers to be added automatically. Select none to disable adding to a list.', 'ipt_fsqm' ) ); ?>
					</td>
					<?php endif; ?>
				</tr>
			</tbody>
		</table>
		<?php
	}

	public function mymail() {
		$op = $this->settings['integration']['mymail'];
		if ( ! function_exists( 'mailster' ) ) {
			$this->ui->msg_error( sprintf( __( 'Please install <a href="%1$s" target="_blank">MailSter WordPress Plugin</a> to begin integration.', 'ipt_fsqm' ), 'http://codecanyon.net/item/mymail-email-newsletter-plugin-for-wordpress/3078294?ref=WPQuark' ) );
			return;
		}

		// Get the lists
		$mymail_lists = mailster( 'lists' )->get();
		$mymail_list_selection = array();
		if ( ! empty( $mymail_lists ) ) {
			foreach ( $mymail_lists as $mlist ) {
				$mymail_list_selection[] = array(
					'value' => $mlist->ID,
					'label' => $mlist->name,
				);
			}
		}
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[integration][mymail][enabled]', __( 'Enable MyMail Integration', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[integration][mymail][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, array(
					'condid' => 'ipt_fsqm_intg_mm_full_wrap'
				) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you want the submitter to get subscribed to one or more Mailster list, please enable it here. After this, make sure you add the Primary Email field to the form.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
	</tbody>
</table>
<table class="form-table" id="ipt_fsqm_intg_mm_full_wrap">
	<tbody>
		<tr id="ipt_fsqm_intg_mm_list_wrap">
			<?php if ( empty( $mymail_list_selection ) ) : ?>
			<td colspan="3">
				<?php $this->ui->msg_error( __( 'You have not created any list in Mailster yet. Please create at least one list and it will appear here.', 'ipt_fsqm' ) ); ?>
			</td>
			<?php else : ?>
			<th><?php $this->ui->generate_label( 'settings[integration][mymail][list_ids]', __( 'Select Subscriber Lists', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->checkboxes( 'settings[integration][mymail][list_ids][]', $mymail_list_selection, $op['list_ids'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Select the lists to which you want your subscribers to be added automatically. Select none to disable adding to a list.', 'ipt_fsqm' ) ); ?>
			</td>
			<?php endif; ?>
		</tr>
		<tr id="ipt_fsqm_intg_mm_ow_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][mymail][overwrite]', __( 'Overwrite Existing Subscriber Info', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[integration][mymail][overwrite]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['overwrite'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If this is enabled, then existing user information would be updated (first name and last name) if required.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<?php
		$this->integration_custommeta(
			$op['meta'],
			'settings[integration][mymail][meta]',
			'https://wpquark.com/kb/fsqm/fsqm-integration/mymail-inegration-fsqm-pro/#ipt_kb_toc_8498_2'
		);
		?>
	</tbody>
</table>
		<?php
	}

	public function sendy() {
		$op = $this->settings['integration']['sendy'];
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[integration][sendy][enabled]', __( 'Enable Sendy.co Integration', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[integration][sendy][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, array(
					'condid' => 'ipt_fsqm_intg_sc_list_wrap,ipt_fsqm_intg_sc_url_wrap'
				) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you want the submitter to get subscribed to a <a href="https://sendy.co">Sendy.co</a> Newsletter list, please enable it here. After this, make sure you add the Primary Email field to the form.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_intg_sc_list_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][sendy][list_id]', __( 'List ID', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[integration][sendy][list_id]', $op['list_id'], __( 'List ID', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'The list id you want to subscribe a user to. This encrypted & hashed id can be found under View all lists section named ID', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_intg_sc_url_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][sendy][url]', __( 'Sendy Installation URL', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[integration][sendy][url]', $op['url'], __( 'Installation URL', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'The URL where Sendy.co is installed.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_intg_sc_url_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][sendy][api_key]', __( 'Sendy.co API Key', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[integration][sendy][api_key]', $op['url'], __( 'Sendy.co API Key', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'API key, available in Sendy.co Settings.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
	</tbody>
</table>
		<?php
	}

	public function convertkit() {
		$op = $this->settings['integration']['convertkit'];
		$items = [];
		// Toggle
		$enabled = [
			'name' => 'settings[integration][convertkit][enabled]',
			'ui' => 'toggle',
			'label' => __( 'Enable Convertkit Integration', 'ipt_fsqm' ),
			'param' => [ '%', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, [
				'condid' => 'ipt_fsqm_intg_cvk_full_wrap',
			] ],
			'description' => __( '<a href="https://wp.me/p3Zesg-8Fm">Learn here</a> about finding different Ids.', 'ipt_fsqm' ),
			'help' => __( 'Enable to send submitting users to convertkit subscribers.', 'ipt_fsqm' ),
		];
		// Api key
		$items[] = [
			'name' => 'settings[integration][convertkit][api_key]',
			'ui' => 'password',
			'label' => __( 'API Key', 'ipt_fsqm' ),
			'param' => [ '%', $op['api_key'] ],
			'help' => __( 'You can find your API Key under convertkit Account Settings.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_intg_cvk_api_key_wrap',
		];
		// Form Id
		$items[] = [
			'name' => 'settings[integration][convertkit][form_id]',
			'label' => __( 'Form Id', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => [ '%', $op['form_id'], __( 'Disabled', 'ipt_fsqm' ) ],
			'help' => __( 'Edit a convertkit form and look in the URL for <code>forms/designers/<strong>1231</strong>/edit</code> part. More information can be found <a href="https://wp.me/p3Zesg-8Fm">here</a>.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_intg_cvk_form_id_wrap',
		];
		// Sequence Id
		$items[] = [
			'name' => 'settings[integration][convertkit][sequence_id]',
			'label' => __( 'Sequence Id', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => [ '%', $op['sequence_id'], __( 'Disabled', 'ipt_fsqm' ) ],
			'help' => __( 'Edit a convertkit sequence and look in the URL for <code>/sequences/<strong>265794</strong></code> part. More information can be found <a href="https://wp.me/p3Zesg-8Fm">here</a>.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_intg_cvk_sequence_id_wrap',
		];
		// Tag Id
		$items[] = [
			'name' => 'settings[integration][convertkit][tags]',
			'label' => __( 'Tag Ids (Comma Separated)', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => [ '%', $op['tags'], __( 'Disabled', 'ipt_fsqm' ) ],
			'help' => __( 'Edit a convertkit tag and look in the URL for <code>subscribers?subscribable_ids=<strong>265794</strong>&subscribable_type=Tag</code> part. More information can be found <a href="https://wp.me/p3Zesg-8Fm">here</a>.', 'ipt_fsqm' ),
			'description' => __( 'Enter multiple tags separated by comma.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_intg_cvk_tags_wrap',
		];
		$this->ui->form_table( [
			$enabled,
		] );
		echo '<table class="form-table" id="ipt_fsqm_intg_cvk_full_wrap"><tbody>';
		$this->ui->form_table( $items, false );
		$this->integration_custommeta(
			$op['meta'],
			'settings[integration][convertkit][meta]',
			'https://wpquark.com/kb/fsqm/fsqm-integration/convertkit-integration-with-eform-wordpress-form-builder/#ipt_kb_toc_33316_4'
		);
		echo '</tbody></table>';
	}

	public function enormail() {
		$op = $this->settings['integration']['enormail'];
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[integration][enormail][enabled]', __( 'Enable Enormail Integration', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[integration][enormail][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, array(
					'condid' => 'ipt_fsqm_intg_en_full_wrap'
				) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you want the submitter to get subscribed to a <a href="https://enormail.eu/">Enormail</a> Newsletter list, please enable it here. After this, make sure you add the Primary Email field to the form.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
	</tbody>
</table>
<table class="form-table" id="ipt_fsqm_intg_en_full_wrap">
	<tbody>
		<tr id="ipt_fsqm_intg_en_api_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][enormail][api]', __( 'Enormail API', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[integration][enormail][api]', $op['api'], __( 'API Key', 'ipt_fsqm' ) ); ?>
				<br />
				<span class="description"><?php _e( 'Access <a href="https://app.enormail.eu/account/api" rel="noopener nofollow" target="_blank">this link</a> while being logged in and generate and paste an API key here.', 'ipt_fsqm' ); ?></span>
			</td>
			<td>
				<?php $this->ui->help( __( 'API key found in your account settings.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_intg_en_list_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][enormail][list_id]', __( 'Enormail List ID', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[integration][enormail][list_id]', $op['list_id'], __( 'LIST ID', 'ipt_fsqm' ) ); ?>
				<br />
				<span class="description"><?php _e( 'Access <a href="https://app.enormail.eu/contacts" rel="noopener nofollow" target="_blank">this link</a> while being logged in click on settings of your list. It will show the List ID.', 'ipt_fsqm' ); ?></span>
			</td>
			<td>
				<?php $this->ui->help( __( 'List ID of your mailing list where the contact would be added.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<?php
		$this->integration_custommeta(
			$op['meta'],
			'settings[integration][enormail][meta]',
			'https://wpquark.com/kb/fsqm/fsqm-integration/enormail-integration-eform/#ipt_kb_toc_14795_2'
		);
		?>
	</tbody>
</table>
		<?php
	}

	public function mailerlite() {
		$op = $this->settings['integration']['mailerlite'];
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[integration][mailerlite][enabled]', __( 'Enable MailerLite Integration', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[integration][mailerlite][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, array(
					'condid' => 'ipt_fsqm_intg_ml_full_wrap'
				) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you want the submitter to get subscribed to a <a href="https://mailerlite.com/">MailerLite</a> Newsletter list, please enable it here. After this, make sure you add the Primary Email field to the form.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
	</tbody>
</table>
<table class="form-table" id="ipt_fsqm_intg_ml_full_wrap">
	<tbody>
		<tr id="ipt_fsqm_intg_ml_api_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][mailerlite][api]', __( 'MailerLite API', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[integration][mailerlite][api]', $op['api'], __( 'API Key', 'ipt_fsqm' ) ); ?>
				<br />
				<span class="description"><?php _e( 'Access <a href="https://app.mailerlite.com/integrations/" rel="noopener nofollow" target="_blank">this link</a> while being logged in and click on <a href="https://app.mailerlite.com/integrations/api/" rel="nofollow noopener" target="_blank">Developer API</a>. Paste API and Group ID.', 'ipt_fsqm' ); ?></span>
			</td>
			<td>
				<?php $this->ui->help( __( 'API key found in your account settings.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_intg_ml_list_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][mailerlite][group_id]', __( 'Mailerlite Group ID', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[integration][mailerlite][group_id]', $op['group_id'], __( 'Group ID', 'ipt_fsqm' ) ); ?>
				<br />
				<span class="description"><?php _e( 'After creating a group, access <a href="https://app.mailerlite.com/integrations/api/" rel="nofollow noopener" target="_blank">Developer API</a> and paste the ID of the group.', 'ipt_fsqm' ); ?></span>
			</td>
			<td>
				<?php $this->ui->help( __( 'Group ID of your mailing list where the contact would be added.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<?php
		$this->integration_custommeta(
			$op['meta'],
			'settings[integration][mailerlite][meta]',
			'https://wpquark.com/kb/fsqm/fsqm-integration/mailerlite-integration-eform/#ipt_kb_toc_14787_2'
		);
		?>
	</tbody>
</table>
		<?php
	}

	public function active_campaign() {
		$op = $this->settings['integration']['active_campaign'];
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[integration][active_campaign][enabled]', __( 'Enable Active Campaign Integration', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'settings[integration][active_campaign][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['enabled'], '1', false, true, array(
					'condid' => 'ipt_fsqm_intg_ac_full_wrap'
				) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'If you want the submitter to get subscribed to a <a href="http://www.activecampaign.com/">Active Campaign</a> Newsletter list, please enable it here. After this, make sure you add the Primary Email field to the form.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
	</tbody>
</table>
<table class="form-table" id="ipt_fsqm_intg_ac_full_wrap">
	<tbody>
		<tr id="ipt_fsqm_intg_ac_url_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][active_campaign][url]', __( 'Active Campaign URL', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[integration][active_campaign][url]', $op['url'], __( 'Active Campaign URL', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'The URL found in your developers settings.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_intg_ac_api_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][active_campaign][api]', __( 'Active Campaign API', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[integration][active_campaign][api]', $op['api'], __( 'API Key', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'API key found in your developers settings.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_intg_ac_list_wrap">
			<th><?php $this->ui->generate_label( 'settings[integration][active_campaign][list_id]', __( 'List ID', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'settings[integration][active_campaign][list_id]', $op['list_id'], __( 'List ID', 'ipt_fsqm' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'The ID of the list. It can be found by looking at the URL of your Active Campaign List. <code>https://xyz.activehosted.com/contact/?listid=<strong>1</strong>&status=1</code>, here the ID is <code>1</code>.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<?php
		$this->integration_custommeta(
			$op['meta'],
			'settings[integration][active_campaign][meta]',
			'https://wpquark.com/kb/fsqm/fsqm-integration/active-campaign-integration-fsqm-pro/#ipt_kb_toc_8503_2'
		);
		?>
	</tbody>
</table>
		<?php
	}

	/*==========================================================================
	 * DROPPABLE UI HTML
	 * Overrides the parent
	 *========================================================================*/
	/* DESIGN */
	public function build_heading( $element, $key, $data, $default_data, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
<div class="ipt_uif_tabs">
	<ul>
		<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
		<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
	</ul>
	<div id="<?php echo $tab_names; ?>_elm">
		<table class="form-table">
			<tbody>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
					<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
					<td></td>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[settings][type]', __( 'Heading Type', 'ipt_fsqm' ) ); ?></th>
					<td>
						<?php $this->ui->heading_type( $name_prefix . '[settings][type]', $data['settings']['type'] ); ?>
					</td>
					<td><?php $this->ui->help( __( 'Select the html heading type.', 'ipt_fsqm' ) ); ?></td>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[settings][align]', __( 'Heading Alignment', 'ipt_fsqm' ) ); ?></th>
					<td>
						<?php $this->ui->alignment_radio( $name_prefix . '[settings][align]', $data['settings']['align'] ); ?>
					</td>
					<td><?php $this->ui->help( __( 'Select the alignment of the heading.', 'ipt_fsqm' ) ); ?></td>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
					<td>
						<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
					</td>
					<td><?php $this->ui->help( __( 'Select the icon you want to appear before the heading. Select none to disable.', 'ipt_fsqm' ) ) ?></td>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[settings][show_top]', __( 'Show Scroll to Top', 'ipt_fsqm' ) ); ?></th>
					<td>
						<?php $this->ui->toggle( $name_prefix . '[settings][show_top]', __( 'Show', 'ipt_fsqm' ), __( 'Don\'t Show', 'ipt_fsqm' ), $data['settings']['show_top'] ); ?>
					</td>
					<td><?php $this->ui->help( __( 'Turn the feature on to show a scroll to top anchor beside the heading.', 'ipt_fsqm' ) ); ?></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div id="<?php echo $tab_names; ?>_logic">
		<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
	</div>
</div>

		<?php
	}

	public function build_richtext( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the icon you want to appear before the heading. Select none to disable.', 'ipt_fsqm' ) ) ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][styled]', __( 'Styled Container Appearance', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][styled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['styled'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enable this option to print like styled container.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>

		<?php
	}

	public function build_embed( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
<div class="ipt_uif_tabs">
	<ul>
		<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
		<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
	</ul>
	<div id="<?php echo $tab_names; ?>_elm">
		<table class="form-table">
			<tbody>
				<tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][full_size]', __( 'Make iframes and objects full size', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][full_size]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['full_size'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enable this option to force all iframes and objects inside this element full size. While keeping this option to No, you can manually add class <code>resize</code> to your iframes or objects to make them full size.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[description]', __( 'Embed Code', 'ipt_fsqm' ) ); ?></th>
					<td><?php $this->ui->textarea( $name_prefix . '[description]', $data['description'], __( 'Embed Code', 'ipt_fsqm' ), 'large', 'normal', array( 'code' ) ); ?></td>
					<td></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div id="<?php echo $tab_names; ?>_logic">
		<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
	</div>
</div>

		<?php
	}

	public function build_collapsible( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
<div class="ipt_uif_tabs">
	<ul>
		<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
		<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
	</ul>
	<div id="<?php echo $tab_names; ?>_elm">
		<table class="form-table">
			<tbody>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
					<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
					<td></td>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
					<td>
						<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
					</td>
					<td><?php $this->ui->help( __( 'Select the icon you want to appear before the title. Select none to disable.', 'ipt_fsqm' ) ) ?></td>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[settings][expanded]', __( 'Show Expanded', 'ipt_fsqm' ) ); ?></th>
					<td>
						<?php $this->ui->toggle( $name_prefix . '[settings][expanded]', __( 'Expanded', 'ipt_fsqm' ), __( 'Collapsed', 'ipt_fsqm' ), $data['settings']['expanded'] ); ?>
					</td>
					<td><?php $this->ui->help( __( 'If you wish to make the collapsible appear as expanded by default, then enable this feature.', 'ipt_fsqm' ) ); ?></td>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
					<td>
						<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
					</td>
					<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div id="<?php echo $tab_names; ?>_logic">
		<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
	</div>
</div>
		<?php
	}

	public function build_container( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
<div class="ipt_uif_tabs">
	<ul>
		<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
		<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
	</ul>
	<div id="<?php echo $tab_names; ?>_elm">
		<table class="form-table">
			<tbody>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
					<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
					<td></td>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
					<td>
						<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
					</td>
					<td><?php $this->ui->help( __( 'Select the icon you want to appear before the title. Select none to disable.', 'ipt_fsqm' ) ) ?></td>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
					<td>
						<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
					</td>
					<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div id="<?php echo $tab_names; ?>_logic">
		<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
	</div>
</div>
		<?php
	}

	public function build_blank_container( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
	?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
	}

	public function build_iconbox( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
		$columns = array(
			array(
				'label' => __( 'Icon', 'ipt_fsqm' ),
				'size' => '30',
				'type' => 'icon_selector',
			),
			array(
				'label' => __( 'Text', 'ipt_fsqm' ),
				'size' => '35',
				'type' => 'text',
			),
			array(
				'label' => __( 'Link', 'ipt_fsqm' ),
				'size' => '21',
				'type' => 'text',
			),
		);
		$labels = array(
			'confirm' => __( 'Confirm delete. This action can not be undone.', 'ipt_fsqm' ),
			'add' => __( 'Add New Button', 'ipt_fsqm' ),
			'del' => __( 'Click to delete', 'ipt_fsqm' ),
			'drag' => __( 'Drag this to rearrange', 'ipt_fsqm' ),
		);
		$new_name_prefix = $name_prefix . '[settings][elements][__SDAKEY__]';
		$data_list = array(
			array( $new_name_prefix . '[icon]', (string) hexdec( '0xe0d7' ) ),
			array( $new_name_prefix . '[text]', '', __( 'Optional text', 'ipt_fsqm' ), 'fit' ),
			array( $new_name_prefix . '[url]', '', __( 'Link', 'ipt_fsqm' ), 'fit' ),
		);
		$items = array();
		$max_key = null;
		foreach ( (array) $data['settings']['elements'] as $e_key => $elem ) {
			$max_key = max( array( $max_key, $e_key ) );
			$new_name_prefix = $name_prefix . '[settings][elements][' . $e_key . ']';
			if ( ! isset( $elem['icon'] ) ) {
				$elem['icon'] = 'none';
			}
			$items[] = array(
				array( $new_name_prefix . '[icon]', $elem['icon'] ),
				array( $new_name_prefix . '[text]', $elem['text'], __( 'Optional text', 'ipt_fsqm' ), 'fit' ),
				array( $new_name_prefix . '[url]', $elem['url'], __( 'Link', 'ipt_fsqm' ), 'fit' ),
			);
		}

		$open_types = array(
			0 => array(
				'label' => __( 'Current Window/Tab (_self)', 'ipt_fsqm' ),
				'value' => 'self',
			),
			1 => array(
				'label' => __( 'New Window/Tab (_blank)', 'ipt_fsqm' ),
				'value' => 'blank',
			),
			2 => array(
				'label' => __( 'Popup Window', 'ipt_fsqm' ),
				'value' => 'popup',
				'data' => array(
					'condid' => $this->generate_id_from_name( $name_prefix . '[settings][popup][width]_wrap' ) . ',' . $this->generate_id_from_name( $name_prefix . '[settings][popup][height]_wrap' ),
				),
			),
		);

		$size_items = [
			0 => [
				'label' => __( 'Small', 'ipt_fsqm' ),
				'value' => 'small',
			],
			1 => [
				'label' => __( 'Medium', 'ipt_fsqm' ),
				'value' => 'medium',
			],
			2 => [
				'label' => __( 'Large', 'ipt_fsqm' ),
				'value' => 'large',
			],
		];

		$style_items = [
			0 => [
				'label' => __( 'Flat', 'ipt_fsqm' ),
				'value' => __( 'flat', 'ipt_fsqm' ),
			],
			1 => [
				'label' => __( '3d Bordered', 'ipt_fsqm' ),
				'value' => __( 'border', 'ipt_fsqm' ),
			],
			2 => [
				'label' => __( 'Gradient', 'ipt_fsqm' ),
				'value' => __( 'gradient', 'ipt_fsqm' ),
			],
			3 => [
				'label' => __( 'Outline', 'ipt_fsqm' ),
				'value' => __( 'outline', 'ipt_fsqm' ),
			],
		];

		$new_config_items = [];
		// Font Size
		$new_config_items[] = [
			'name' => $name_prefix . '[settings][size]',
			'label' => __( 'Button Size', 'ipt_fsqm' ),
			'ui' => 'select',
			'param' => [ $name_prefix . '[settings][size]', $size_items, $data['settings']['size'] ],
			'help' => __( 'Set the size of the buttons.', 'ipt_fsqm' ),
		];

		// Button Style
		$new_config_items[] = [
			'name' => $name_prefix . '[settings][style]',
			'label' => __( 'Button Style', 'ipt_fsqm' ),
			'ui' => 'select',
			'param' => [ $name_prefix . '[settings][style]', $style_items, $data['settings']['style'] ],
			'help' => __( 'Set the style of the buttons.', 'ipt_fsqm' ),
		];

		// Rounded
		$new_config_items[] = [
			'name' => $name_prefix . '[settings][rounded]',
			'label' => __( 'Rounded Button', 'ipt_fsqm' ),
			'ui' => 'toggle',
			'param' => [ $name_prefix . '[settings][rounded]', '', '', $data['settings']['rounded'] ],
			'help' => __( 'Set button appearance rounder.', 'ipt_fsqm' ),
		];

		// Themed
		$new_config_items[] = [
			'name' => $name_prefix . '[settings][themed]',
			'label' => __( 'Button Color', 'ipt_fsqm' ),
			'ui' => 'toggle',
			'param' => [ $name_prefix . '[settings][themed]', __( 'Themed', 'ipt_fsqm' ), __( 'Grey', 'ipt_fsqm' ), $data['settings']['themed'] ],
			'help' => __( 'Set button color to greyscale or to primary color of your theme.', 'ipt_fsqm' ),
		];
?>
<div class="ipt_uif_tabs">
	<ul>
		<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
		<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
		<li><a href="#<?php echo $tab_names; ?>_buttons"><?php _e( 'Buttons', 'ipt_fsqm' ); ?></a></li>
		<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
	</ul>
	<div id="<?php echo $tab_names; ?>_elm">
		<table class="form-table">
			<tbody>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[settings][align]', __( 'Button Alignment', 'ipt_fsqm' ) ); ?></th>
					<td>
						<?php $this->ui->alignment_radio( $name_prefix . '[settings][align]', $data['settings']['align'] ); ?>
					</td>
					<td><?php $this->ui->help( __( 'Select the alignment of the icons.', 'ipt_fsqm' ) ); ?></td>
				</tr>
				<?php $this->ui->form_table( $new_config_items, false ); ?>
			</tbody>
		</table>
	</div>
	<div id="<?php echo $tab_names; ?>_ifs">
		<table class="form-table">
			<tbody>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[settings][open]', __( 'Open Link In', 'ipt_fsqm' ) ); ?></th>
					<td><?php $this->ui->select( $name_prefix . '[settings][open]', $open_types, $data['settings']['open'], false, true ); ?></td>
					<td><?php $this->ui->help( __( 'Set how you would like the links to open.', 'ipt_fsqm' ) ); ?></td>
				</tr>
				<tr id="<?php echo $this->generate_id_from_name( $name_prefix . '[settings][popup][width]_wrap' ); ?>">
					<th><?php $this->ui->generate_label( $name_prefix . '[settings][popup][width]', __( 'Popup width (Pixel)', 'ipt_fsqm' ) ); ?></th>
					<td><?php $this->ui->text( $name_prefix . '[settings][popup][width]', $data['settings']['popup']['width'], __( 'Pixels', 'ipt_fsqm' ) ); ?></td>
					<td><?php $this->ui->help( __( 'Set the popup width in pixels.', 'ipt_fsqm' ) ); ?></td>
				</tr>
				<tr id="<?php echo $this->generate_id_from_name( $name_prefix . '[settings][popup][height]_wrap' ); ?>">
					<th><?php $this->ui->generate_label( $name_prefix . '[settings][popup][height]', __( 'Popup height (Pixel)', 'ipt_fsqm' ) ); ?></th>
					<td><?php $this->ui->text( $name_prefix . '[settings][popup][height]', $data['settings']['popup']['height'], __( 'Pixels', 'ipt_fsqm' ) ); ?></td>
					<td><?php $this->ui->help( __( 'Set the popup height in pixels.', 'ipt_fsqm' ) ); ?></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div id="<?php echo $tab_names; ?>_buttons">
		<table class="form-table">
			<tbody>
				<tr>
					<th colspan="2"><?php $this->ui->generate_label( '', __( 'Button List', 'ipt_fsqm' ) ); ?></th>
					<td>
						<?php $this->ui->help( __( 'Choose your icons. You can enter url and optional text.', 'ipt_fsqm' ) ); ?>
					</td>
				</tr>
				<tr>
					<td colspan="3">
						<?php $this->ui->sda_list( array(
							'columns' => $columns,
							'labels' => $labels,
						), $items, $data_list, $max_key ); ?>
						<?php $this->ui->clear(); ?>
					</td>
				</tr>
				<tr>
					<td colspan="3">
						<p class="description">
							<?php _e( 'Some special LINK values (apart from URLs) are available.', 'ipt_fsqm' ); ?>
						</p>
						<ul class="ul-disc">
							<li><code>#eform-next</code>: <?php _e( 'Take to next page.', 'ipt_fsqm' ); ?></li>
							<li><code>#eform-prev</code>: <?php _e( 'Take to previous page.', 'ipt_fsqm' ); ?></li>
							<li><code>#eform-submit</code>: <?php _e( 'Submit button.', 'ipt_fsqm' ); ?></li>
							<li><code>#eform-reset</code>: <?php _e( 'Reset button.', 'ipt_fsqm' ); ?></li>
							<li><code>#eform-container-1</code>: <?php _e( 'Jump to container #1. Container numbering starts from 1.', 'ipt_fsqm' ); ?></li>
							<li><code>#eform-save</code>: <?php _e( 'Save form data on browser. Only if enabled under CONFIG > FORM SETTINGS > SAVE PROGRESS.', 'ipt_fsqm' ); ?></li>
						</ul>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div id="<?php echo $tab_names; ?>_logic">
		<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
	</div>
</div>
		<?php
	}

	public function build_col_half( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$this->build_col( $name_prefix, $data );
	}

	public function build_col_third( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$this->build_col( $name_prefix, $data );
	}

	public function build_col_two_third( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$this->build_col( $name_prefix, $data );
	}

	public function build_col_forth( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$this->build_col( $name_prefix, $data );
	}

	public function build_col_three_forth( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$this->build_col( $name_prefix, $data );
	}

	public function build_clear( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		?>
<table class="form-table">
	<tbody>
		<tr>
			<td colspan="3">
				<?php echo '<p class="description">' . __( 'This element clears the floating content to avoid unexpected appearance.', 'ipt_fsqm' ) . '</p>'; ?>
			</td>
		</tr>
	</tbody>
</table>
		<?php

	}

	public function build_horizontal_line( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {

		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][show_top]', __( 'Show Scroll to Top', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][show_top]', __( 'Show', 'ipt_fsqm' ), __( 'Don\'t Show', 'ipt_fsqm' ), $data['settings']['show_top'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Turn the feature on to show a scroll to top anchor below the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>

		<?php
	}

	public function build_divider( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
<div class="ipt_uif_tabs">
	<ul>
		<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
		<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
	</ul>
	<div id="<?php echo $tab_names; ?>_elm">
		<table class="form-table">
			<tbody>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
					<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
					<td></td>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[settings][align]', __( 'Text Alignment', 'ipt_fsqm' ) ); ?></th>
					<td>
						<?php $this->ui->alignment_radio( $name_prefix . '[settings][align]', $data['settings']['align'] ); ?>
					</td>
					<td><?php $this->ui->help( __( 'Select the alignment of the text.', 'ipt_fsqm' ) ); ?></td>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
					<td>
						<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
					</td>
					<td><?php $this->ui->help( __( 'Select the icon you want to appear before the heading. Select none to disable.', 'ipt_fsqm' ) ) ?></td>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[settings][show_top]', __( 'Show Scroll to Top', 'ipt_fsqm' ) ); ?></th>
					<td>
						<?php $this->ui->toggle( $name_prefix . '[settings][show_top]', __( 'Show', 'ipt_fsqm' ), __( 'Don\'t Show', 'ipt_fsqm' ), $data['settings']['show_top'] ); ?>
					</td>
					<td><?php $this->ui->help( __( 'Turn the feature on to show a scroll to top anchor beside the divider.', 'ipt_fsqm' ) ); ?></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div id="<?php echo $tab_names; ?>_logic">
		<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
	</div>
</div>
		<?php
	}

	public function build_button( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
		$sizes = array(
			0 => array(
				'label' => __( 'Small', 'ipt_fsqm' ),
				'value' => 'small',
			),
			1 => array(
				'label' => __( 'Medium', 'ipt_fsqm' ),
				'value' => 'medium',
			),
			2 => array(
				'label' => __( 'Large', 'ipt_fsqm' ),
				'value' => 'large'
			),
		);
?>
<div class="ipt_uif_tabs">
	<ul>
		<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
		<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
	</ul>
	<div id="<?php echo $tab_names; ?>_ifs">
		<table class="form-table">
			<tbody>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
					<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
					<td></td>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[settings][container]', __( 'Container Number', 'ipt_fsqm' ) ); ?></th>
					<td>
						<?php $this->ui->spinner( $name_prefix . '[settings][container]', $data['settings']['container'], __( 'Container Number', 'ipt_fsqm' ), '0', '', 1 ); ?>
					</td>
					<td>
						<?php $this->ui->help( __( 'Enter the container number. Starts from 1 and any number represents nth container from start. So 1 would represent first, 3 would represent third etc.', 'ipt_fsqm' ) ); ?>
					</td>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[settings][size]', __( 'Button Size', 'ipt_fsqm' ) ); ?></th>
					<td>
						<?php $this->ui->select( $name_prefix . '[settings][size]', $sizes, $data['settings']['size'] ); ?>
					</td>
					<td><?php $this->ui->help( __( 'Select the size of the button.', 'ipt_fsqm' ) ); ?></td>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
					<td>
						<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
					</td>
					<td><?php $this->ui->help( __( 'Select the icon you want to appear before the title. Select none to disable.', 'ipt_fsqm' ) ) ?></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div id="<?php echo $tab_names; ?>_logic">
		<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
	</div>
</div>
		<?php
	}

	public function build_imageslider( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$name_prefix = $name_prefix . '[settings]';
		$animations = array( "sliceDown", "sliceDownLeft", "sliceUp", "sliceUpLeft", "sliceUpDown", "sliceUpDownLeft", "fold", "fade", "random", "slideInRight", "slideInLeft", "boxRandom", "boxRain", "boxRainReverse", "boxRainGrow", "boxRainGrowReverse" );

		$sda_column = array(
			0 => array(
				'label' => __( 'Image', 'ipt_fsqm' ),
				'size' => '30',
				'type' => 'upload'
			),
			1 => array(
				'label' => __( 'Title', 'ipt_fsqm' ),
				'size' => '25',
				'type' => 'text',
			),
			2 => array(
				'label' => __( 'Link', 'ipt_fsqm' ),
				'size' => '25',
				'type' => 'text',
			),
		);
		$labels = array(
			'confirm' => __( 'Confirm delete. This action can not be undone.', 'ipt_fsqm' ),
			'add' => __( 'Add New Image', 'ipt_fsqm' ),
			'del' => __( 'Click to delete', 'ipt_fsqm' ),
			'drag' => __( 'Drag this to rearrange', 'ipt_fsqm' ),
		);
		$data_sda = array(
			0 => array( $name_prefix . '[images][__SDAKEY__][src]', '', $name_prefix . '[images][__SDAKEY__][title]' ),
			1 => array( $name_prefix . '[images][__SDAKEY__][title]', '', __( 'Optional', 'ipt_fsqm' ), 'fit' ),
			2 => array( $name_prefix . '[images][__SDAKEY__][url]', '', __( 'Optional', 'ipt_fsqm' ), 'fit' ),
		);
		$items = array();
		$max_key = null;
		foreach ( $data['settings']['images'] as $i_key => $image ) {
			$max_key = max( array( $max_key, $i_key ) );
			$items[] = array(
				0 => array( $name_prefix . '[images][' . $i_key . '][src]', $image['src'], $name_prefix . '[images][' . $i_key . '][title]' ),
				1 => array( $name_prefix . '[images][' . $i_key . '][title]', $image['title'], __( 'Optional', 'ipt_fsqm' ), 'fit' ),
				2 => array( $name_prefix . '[images][' . $i_key . '][url]', $image['url'], __( 'Optional', 'ipt_fsqm' ), 'fit' ),
			);
		}

		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
<div class="ipt_uif_tabs">
	<ul>
		<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
		<li><a href="#<?php echo $tab_names; ?>_images"><?php _e( 'Images', 'ipt_fsqm' ); ?></a></li>
		<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
	</ul>
	<div id="<?php echo $tab_names; ?>_ifs">
		<table class="form-table">
			<tbody>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[autoslide]', __( 'Automatic Slide', 'ipt_fsqm' ) ); ?></th>
					<td>
						<?php $this->ui->toggle( $name_prefix . '[autoslide]', __( 'Enabled', 'ipt_fsqm' ), __( 'Disabled', 'ipt_fsqm' ), $data['settings']['autoslide'] ); ?>
					</td>
					<td><?php $this->ui->help( __( 'Enable or disable the autoslide feature.', 'ipt_fsqm' ) ); ?></td>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[duration]', __( 'Slide Duration', 'ipt_fsqm' ) ); ?></th>
					<td>
						<?php $this->ui->slider( $name_prefix . '[duration]', $data['settings']['duration'], 2, 100 ); ?>
					</td>
					<td><?php $this->ui->help( __( 'Enter the time duration between two slides (in seconds).', 'ipt_fsqm' ) ); ?></td>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[transition]', __( 'Transition Time', 'ipt_fsqm' ) ); ?></th>
					<td>
						<?php $this->ui->slider( $name_prefix . '[transition]', $data['settings']['transition'], 0.2, 100, 0.1 ); ?>
					</td>
					<td><?php $this->ui->help( __( 'Enter the transition time between two slides (in seconds).', 'ipt_fsqm' ) ); ?></td>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[animation]', __( 'Transition Animation', 'ipt_fsqm' ) ); ?></th>
					<td>
						<?php $this->ui->select( $name_prefix . '[animation]', $animations, $data['settings']['animation'] ); ?>
					</td>
					<td><?php $this->ui->help( __( 'Select the type of transition animation.', 'ipt_fsqm' ) ); ?></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div id="<?php echo $tab_names; ?>_images">
		<table class="form-table">
			<tbody>
				<tr>
					<th colspan="2"><?php $this->ui->generate_label( '', __( 'Image List', 'ipt_fsqm' ) ); ?></th>
					<td><?php $this->ui->help( __( 'Upload the images which you would like to use inside the slider. It your sole responsibility to select image files only. Otherwise, the slider may not work.', 'ipt_fsqm' ) ); ?></td>
				</tr>
				<tr>
					<td colspan="3">
						<?php $this->ui->sda_list( array(
							'columns' => $sda_column,
							'labels' => $labels,
						), $items, $data_sda, $max_key ); ?>
						<div class="clear"></div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div id="<?php echo $tab_names; ?>_logic">
		<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
	</div>
</div>
		<?php
	}

	public function build_captcha( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
		?>
<div class="ipt_uif_tabs">
	<ul>
		<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
		<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
	</ul>
	<div id="<?php echo $tab_names; ?>_elm">
		<p class="description"><?php _e( 'This will give the surveyee a maths challenge.', 'ipt_fsqm' ); ?></p>
	</div>
	<div id="<?php echo $tab_names; ?>_logic">
		<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
	</div>
</div>
		<?php
	}

	public function build_recaptcha( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$captcha_types = array(
			'image' => __( 'Image', 'ipt_fsqm' ),
			'audio' => __( 'Audio', 'ipt_fsqm' ),
		);
		$captcha_themes = array(
			'light' => __( 'Light Color Scheme', 'ipt_fsqm' ),
			'dark' => __( 'Dark Color Scheme', 'ipt_fsqm' ),
		);
		$captcha_sizes = array(
			'compact' => __( 'Compact', 'ipt_fsqm' ),
			'normal' => __( 'Normal', 'ipt_fsqm' ),
		);
		$captcha_hls = array(
			'ar'     => 'Arabic',
			'af'     => 'Afrikaans',
			'am'     => 'Amharic',
			'hy'     => 'Armenian',
			'az'     => 'Azerbaijani',
			'eu'     => 'Basque',
			'bn'     => 'Bengali',
			'bg'     => 'Bulgarian',
			'ca'     => 'Catalan',
			'zh-HK'  => 'Chinese (Hong Kong)',
			'zh-CN'  => 'Chinese (Simplified)',
			'zh-TW'  => 'Chinese (Traditional)',
			'hr'     => 'Croatian',
			'cs'     => 'Czech',
			'da'     => 'Danish',
			'nl'     => 'Dutch',
			'en-GB'  => 'English (UK)',
			'en'     => 'English (US)',
			'et'     => 'Estonian',
			'fil'    => 'Filipino',
			'fi'     => 'Finnish',
			'fr'     => 'French',
			'fr-CA'  => 'French (Canadian)',
			'gl'     => 'Galician',
			'ka'     => 'Georgian',
			'de'     => 'German',
			'de-AT'  => 'German (Austria)',
			'de-CH'  => 'German (Switzerland)',
			'el'     => 'Greek',
			'gu'     => 'Gujarati',
			'iw'     => 'Hebrew',
			'hi'     => 'Hindi',
			'hu'     => 'Hungarain',
			'is'     => 'Icelandic',
			'id'     => 'Indonesian',
			'it'     => 'Italian',
			'ja'     => 'Japanese',
			'kn'     => 'Kannada',
			'ko'     => 'Korean',
			'lo'     => 'Laothian',
			'lv'     => 'Latvian',
			'lt'     => 'Lithuanian',
			'ms'     => 'Malay',
			'ml'     => 'Malayalam',
			'mr'     => 'Marathi',
			'mn'     => 'Mongolian',
			'no'     => 'Norwegian',
			'fa'     => 'Persian',
			'pl'     => 'Polish',
			'pt'     => 'Portuguese',
			'pt-BR'  => 'Portuguese (Brazil)',
			'pt-PT'  => 'Portuguese (Portugal)',
			'ro'     => 'Romanian',
			'ru'     => 'Russian',
			'sr'     => 'Serbian',
			'si'     => 'Sinhalese',
			'sk'     => 'Slovak',
			'sl'     => 'Slovenian',
			'es'     => 'Spanish',
			'es-419' => 'Spanish (Latin America)',
			'sw'     => 'Swahili',
			'sv'     => 'Swedish',
			'ta'     => 'Tamil',
			'te'     => 'Telugu',
			'th'     => 'Thai',
			'tr'     => 'Turkish',
			'uk'     => 'Ukrainian',
			'ur'     => 'Urdu',
			'vi'     => 'Vietnamese',
			'zu'     => 'Zulu',
		);
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
		?>
<div class="ipt_uif_tabs">
	<ul>
		<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
		<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
	</ul>
	<div id="<?php echo $tab_names; ?>_ifs">
	<table class="form-table">
		<tbody>
			<tr>
				<th><?php $this->ui->generate_label( $name_prefix . '[settings][site_key]', __( 'Site Key', 'ipt_fsqm' ) ); ?></th>
				<td>
					<?php $this->ui->text( $name_prefix . '[settings][site_key]', $data['settings']['site_key'], __( 'Site Key', 'ipt_fsqm' ) ); ?>
				</td>
				<td><?php $this->ui->help( __( 'Enter the sitekey for your domain. You can get the sitekey from <a href="https://www.google.com/recaptcha/admin" rel="noopener" target="_blank">here</a>.', 'ipt_fsqm' ) ); ?></td>
			</tr>
			<tr>
				<th><?php $this->ui->generate_label( $name_prefix . '[settings][secret_key]', __( 'Secret Key', 'ipt_fsqm' ) ); ?></th>
				<td>
					<?php $this->ui->text( $name_prefix . '[settings][secret_key]', $data['settings']['secret_key'], __( 'Secret Key', 'ipt_fsqm' ) ); ?>
				</td>
				<td><?php $this->ui->help( __( 'Enter the secret key for your domain. You can get the secret key from <a href="https://www.google.com/recaptcha/admin" rel="noopener" target="_blank">here</a>.', 'ipt_fsqm' ) ); ?></td>
			</tr>
			<tr>
				<th><?php $this->ui->generate_label( $name_prefix . '[settings][type]', __( 'reCaptcha Type', 'ipt_fsqm' ) ); ?></th>
				<td>
					<?php $this->ui->select( $name_prefix . '[settings][type]', $captcha_types, $data['settings']['type'] ); ?>
				</td>
				<td><?php $this->ui->help( __( 'Set the preferred type of captcha to use inside reCaptcha. Default is image.', 'ipt_fsqm' ) ); ?></td>
			</tr>
			<tr>
				<th><?php $this->ui->generate_label( $name_prefix . '[settings][hl]', __( 'reCaptcha Language', 'ipt_fsqm' ) ); ?></th>
				<td>
					<?php $this->ui->select( $name_prefix . '[settings][hl]', $captcha_hls, $data['settings']['hl'] ); ?>
				</td>
				<td><?php $this->ui->help( __( 'Enter the recaptcha language code.', 'ipt_fsqm' ) ); ?></td>
			</tr>
		</tbody>
	</table>
	</div>
	<div id="<?php echo $tab_names; ?>_elm">
		<table class="form-table">
		<tbody>
			<tr>
				<th><?php $this->ui->generate_label( $name_prefix . '[settings][theme]', __( 'reCaptcha Theme', 'ipt_fsqm' ) ); ?></th>
				<td>
					<?php $this->ui->select( $name_prefix . '[settings][theme]', $captcha_themes, $data['settings']['theme'] ); ?>
				</td>
				<td><?php $this->ui->help( __( 'Set light or dark version of reCaptcha.', 'ipt_fsqm' ) ); ?></td>
			</tr>
			<tr>
				<th><?php $this->ui->generate_label( $name_prefix . '[settings][size]', __( 'reCaptcha Size', 'ipt_fsqm' ) ); ?></th>
				<td>
					<?php $this->ui->select( $name_prefix . '[settings][size]', $captcha_sizes, $data['settings']['size'] ); ?>
				</td>
				<td><?php $this->ui->help( __( 'Set size of reCaptcha widget.', 'ipt_fsqm' ) ); ?></td>
			</tr>
		</tbody>
		</table>
	</div>
</div>
		<?php
	}

	/* MCQ */
	public function build_radio( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$this->build_mcq_option_questions( $element, $key, $data, $element_structure, $name_prefix );
	}

	public function build_checkbox( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$this->build_mcq_option_questions( $element, $key, $data, $element_structure, $name_prefix );
	}

	public function build_select( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$this->build_mcq_option_questions( $element, $key, $data, $element_structure, $name_prefix, true );
	}

	public function build_thumbselect( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$sda_columns = array(
			0 => array(
				'label' => __( 'Image', 'ipt_fsqm' ),
				'type' => 'upload',
				'size' => '30',
			),
			1 => array(
				'label' => __( 'Option', 'ipt_fsqm' ),
				'type' => 'text',
				'size' => '25',
			),
			2 => array(
				'label' => __( 'Score', 'ipt_fsqm' ),
				'type' => 'spinner',
				'size' => '15',
			),
			3 => array(
				'label' => __( 'Numeric', 'ipt_fsqm' ),
				'type' => 'spinner',
				'size' => '15',
			),
			4 => array(
				'label' => __( 'Default', 'ipt_fsqm' ),
				'type' => 'toggle',
				'size' => '15',
			),
		);
		$labels = array(
			'confirm' => __( 'Confirm delete. This action can not be undone.', 'ipt_fsqm' ),
			'add' => __( 'Add New Option', 'ipt_fsqm' ),
			'del' => __( 'Click to delete', 'ipt_fsqm' ),
			'drag' => __( 'Drag this to rearrange', 'ipt_fsqm' ),
		);
		$sda_data = array(
			0 => array( $name_prefix . '[settings][options][__SDAKEY__][image]', '', __( 'Option Image', 'ipt_fsqm' ) ),
			1 => array( $name_prefix . '[settings][options][__SDAKEY__][label]', '', __( 'Enter Option Label', 'ipt_fsqm' ), 'fit' ),
			2 => array( $name_prefix . '[settings][options][__SDAKEY__][score]', '', __( 'Score (Optional)', 'ipt_fsqm' ), 'fit' ),
			3 => array( $name_prefix . '[settings][options][__SDAKEY__][num]', '', __( 'Numeric Value', 'ipt_fsqm' ), 'fit' ),
			4 => array( $name_prefix . '[settings][options][__SDAKEY__][default]', '', '', false )
		);

		$sda_items = array();
		$max_key = null;
		foreach ( (array) $data['settings']['options'] as $o_key => $option ) {
			$max_key = max( array( $max_key, $o_key ) );
			$sda_items[] = array(
				0 => array( $name_prefix . '[settings][options][' . $o_key . '][image]', $option['image'], __( 'Option Image', 'ipt_fsqm' ) ),
				1 => array( $name_prefix . '[settings][options][' . $o_key . '][label]', $option['label'], __( 'Enter Option Label', 'ipt_fsqm' ), 'fit' ),
				2 => array( $name_prefix . '[settings][options][' . $o_key . '][score]', $option['score'], __( 'Score (Optional)', 'ipt_fsqm' ), 'fit' ),
				3 => array( $name_prefix . '[settings][options][' . $o_key . '][num]', $option['num'], __( 'Numeric Value', 'ipt_fsqm' ), 'fit' ),
				4 => array( $name_prefix . '[settings][options][' . $o_key . '][default]', '', '', ( isset( $option['default'] ) && true == $option['default'] ? true : false ) ),
			);
		}

		$appearance_choices = array(
			0 => array(
				'value' => 'normal',
				'label' => __( 'Regular with Checkbox/Radio', 'ipt_fsqm' ),
			),
			1 => array(
				'value' => 'border',
				'label' => __( 'Highlight Border', 'ipt_fsqm' ),
			),
			2 => array(
				'value' => 'color',
				'label' => __( 'Highlight Color', 'ipt_fsqm' ),
			),
		);
		$prefill_types = array(
			0 => array(
				'value' => 'none',
				'label' => __( 'None', 'ipt_fsqm' ),
			),
			1 => array(
				'value' => 'url',
				'label' => __( 'URL Parameter Based', 'ipt_fsqm' ),
			),
			2 => array(
				'value' => 'meta',
				'label' => __( 'User Meta Based', 'ipt_fsqm' ),
			),
		);
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
		?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_options"><?php _e( 'Options', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the icon you want to appear inside the selected radio/checkbox.', 'ipt_fsqm' ) ) ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][multiple]', __( 'Multi Select', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][multiple]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['multiple'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Whether or not, multiple options can be selected.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][show_label]', __( 'Show Image Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][show_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['show_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Whether or not, labels will be shown along with the image. Will render a captioned image look.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][appearance]', __( 'Interface Appearance', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->select( $name_prefix . '[settings][appearance]', $appearance_choices, $data['settings']['appearance'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Choose how you would like the thumbnails to appear..', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][tooltip]', __( 'Hide Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][tooltip]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['tooltip'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Whether to show tooltip on hover.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][width]', __( 'Image Width', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][width]', $data['settings']['width'], __( 'Enter Number', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter Image Width, in pixels.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][height]', __( 'Image Height', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][height]', $data['settings']['height'], __( 'Enter Number', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter Image Height, in pixels.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][type]', __( 'Prefill Type', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->select( $name_prefix . '[settings][type]', $prefill_types, $data['settings']['type'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Set the type of the prefill value the field will get. It can be based on URL parameter or user meta key. Leave to None if you do not wish to prefill the value.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][parameter]', __( 'Key Parameter', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][parameter]', $data['settings']['parameter'], __( 'Required', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the key parameter. In case of URL type value, <code>$_REQUEST[ $key ]</code> would be used. In case of User meta type value, the mentioned metakey would be used to retrieve the metavalue. It can not be empty or no value would be generated.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_options">
			<table class="form-table">
				<tbody>
					<tr>
						<th colspan="2"><?php _e( 'Option List', 'ipt_fsqm' ); ?></th>
						<td>
							<?php $this->ui->help( __( 'Enter the options. You can also have score associated to the options. The value of the score should be numeric positive or negative number.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<?php $this->ui->sda_list( array(
								'columns' => $sda_columns,
								'labels' => $labels,
							), $sda_items, $sda_data, $max_key ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>

		<?php
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	public function build_pricing_table( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$appearance_items = array();
		// Title
		$appearance_items[] = array(
			'name' => $name_prefix . '[title]',
			'label' => __( 'Title', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => array( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ),
		);
		// Center Content
		$appearance_items[] = array(
			'name' => $name_prefix . '[settings][centered]',
			'label' => __( 'Center Content', 'ipt_fsqm' ),
			'ui' => 'toggle',
			'param' => array( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ),
			'help' => __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ),
		);
		// Hidden label
		$appearance_items[] = array(
			'name' => $name_prefix . '[settings][hidden_label]',
			'label' => __( 'Hide Label', 'ipt_fsqm' ),
			'ui' => 'toggle',
			'param' => array( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ),
			'help' => __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ),
		);
		// Tooltip
		$appearance_items[] = array(
			'name' => $name_prefix . '[tooltip]',
			'label' => __( 'Tooltip', 'ipt_fsqm' ),
			'ui' => 'textarea',
			'param' => array( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ),
			'help' => __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ),
		);
		// Interface items
		$interface_items = array();
		$prefill_types = array(
			0 => array(
				'value' => 'none',
				'label' => __( 'None', 'ipt_fsqm' ),
			),
			1 => array(
				'value' => 'url',
				'label' => __( 'URL Parameter Based', 'ipt_fsqm' ),
			),
			2 => array(
				'value' => 'meta',
				'label' => __( 'User Meta Based', 'ipt_fsqm' ),
			),
		);
		$available_styles = array(
			0 => array(
				'value' => 'default',
				'label' => __( 'Sharp Tables', 'ipt_fsqm' ),
			),
			1 => array(
				'value' => 'rounded',
				'label' => __( 'Rounded Tables', 'ipt_fsqm' ),
			),
		);
		// Currency Sign
		$interface_items[] = array(
			'name' => $name_prefix . '[settings][currency]',
			'label' => __( 'Currency Sign', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => array( $name_prefix . '[settings][currency]', $data['settings']['currency'], __( 'Required', 'ipt_fsqm' ) ),
			'help' => __( 'Enter the currency sign that will be shown before the price.', 'ipt_fsqm' ),
		);
		// Styles
		$interface_items[] = array(
			'name' => $name_prefix . '[settings][style]',
			'label' => __( 'Table Style', 'ipt_fsqm' ),
			'ui' => 'select',
			'param' => array( $name_prefix . '[settings][style]', $available_styles, $data['settings']['style'] ),
			'help' => __( 'Set the style of the table. You can modify the colors in the OPTIONS tab.', 'ipt_fsqm' ),
		);
		// Prefill type
		$interface_items[] = array(
			'name' => $name_prefix . '[settings][type]',
			'label' => __( 'Prefill Type', 'ipt_fsqm' ),
			'ui' => 'select',
			'param' => array( $name_prefix . '[settings][type]', $prefill_types, $data['settings']['type'] ),
			'help' => __( 'Set the type of the prefill value the field will get. It can be based on URL parameter or user meta key. Leave to None if you do not wish to prefill the value.', 'ipt_fsqm' ),
		);
		// Prefill key
		$interface_items[] = array(
			'name' => $name_prefix . '[settings][parameter]',
			'label' => __( 'Key Parameter', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => array( $name_prefix . '[settings][parameter]', $data['settings']['parameter'], __( 'Required', 'ipt_fsqm' ) ),
			'help' => __( 'Enter the key parameter. In case of URL type value, <code>$_REQUEST[ $key ]</code> would be used. In case of User meta type value, the mentioned metakey would be used to retrieve the metavalue. It can not be empty or no value would be generated.', 'ipt_fsqm' ),
		);

		// Options SDA
		$sda_columns = array(
			0 => array(
				'label' => __( 'Title', 'ipt_fsqm' ),
				'type' => 'text',
				'size' => 50,
			),
			1 => array(
				'label' => __( 'Price', 'ipt_fsqm' ),
				'type' => 'spinner',
				'size' => 25,
			),
			2 => array(
				'label' => __( 'Numeric', 'ipt_fsqm' ),
				'type' => 'spinner',
				'size' => 25,
			),
			3 => array(
				'label' => __( 'Attributes', 'ipt_fsqm' ),
				'type' => 'textarea',
				'size' => 50,
			),
			4 => array(
				'label' => __( 'Highlighted', 'ipt_fsqm' ),
				'type' => 'toggle',
				'size' => 20,
			),
			5 => array(
				'label' => __( 'Feature Header', 'ipt_fsqm' ),
				'type' => 'text',
				'size' => 30,
				'clear' => true,
			),
			6 => array(
				'label' => __( 'Selected', 'ipt_fsqm' ),
				'type' => 'toggle',
				'size' => 15,
			),
			7 => array(
				'label' => __( 'Footer', 'ipt_fsqm' ),
				'type' => 'text',
				'size' => 30,
			),
			8 => array(
				'label' => __( 'Color Scheme', 'ipt_fsqm' ),
				'type' => 'select',
				'size' => 25,
			),
			9 => array(
				'label' => __( 'Custom Color', 'ipt_fsqm' ),
				'type' => 'colorpicker',
				'size' => 30,
			),
		);
		$color_schemes = array(
			array(
				'label' => __( 'Shamrock', 'ipt_fsqm' ),
				'value' => 'shamrock',
			),
			array(
				'label' => __( 'Biloba Flower', 'ipt_fsqm' ),
				'value' => 'biloba-flower',
			),
			array(
				'label' => __( 'Cinnabar', 'ipt_fsqm' ),
				'value' => 'cinnabar',
			),
			array(
				'label' => __( 'Bright Turquoise', 'ipt_fsqm' ),
				'value' => 'bright-turquoise',
			),
			array(
				'label' => __( 'Charade', 'ipt_fsqm' ),
				'value' => 'charade',
			),
			array(
				'label' => __( 'Meteorite', 'ipt_fsqm' ),
				'value' => 'meteorite',
			),
			array(
				'label' => __( 'Vivid Violet', 'ipt_fsqm' ),
				'value' => 'vivid-violet',
			),
			array(
				'label' => __( 'Tango', 'ipt_fsqm' ),
				'value' => 'tango',
			),
			array(
				'label' => __( 'Tree Poppy', 'ipt_fsqm' ),
				'value' => 'tree-poppy',
			),
			array(
				'label' => __( 'Cerulean', 'ipt_fsqm' ),
				'value' => 'cerulean',
			),
			array(
				'label' => __( 'Bahama Blue', 'ipt_fsqm' ),
				'value' => 'bahama-blue',
			),
			array(
				'label' => __( 'Aqua Deep', 'ipt_fsqm' ),
				'value' => 'aqua-deep',
			),
			array(
				'label' => __( 'Theme', 'ipt_fsqm' ),
				'value' => 'theme',
			),
			array(
				'label' => __( 'Custom', 'ipt_fsqm' ),
				'value' => 'custom',
			),
		);

		$labels = array(
			'confirm' => __( 'Confirm delete. This action can not be undone.', 'ipt_fsqm' ),
			'add' => __( 'Add New Option', 'ipt_fsqm' ),
			'del' => __( 'Click to delete', 'ipt_fsqm' ),
			'drag' => __( 'Drag this to rearrange', 'ipt_fsqm' ),
		);

		$sda_data = array(
			0 => array( $name_prefix . '[settings][options][__SDAKEY__][label]', '', __( 'Title', 'ipt_fsqm' ) ),
			1 => array( $name_prefix . '[settings][options][__SDAKEY__][price]', '', __( 'Shown Price', 'ipt_fsqm' ), 'fit' ),
			2 => array( $name_prefix . '[settings][options][__SDAKEY__][numeric]', '', __( 'Numeric Value', 'ipt_fsqm' ), 'fit' ),
			3 => array( $name_prefix . '[settings][options][__SDAKEY__][attr]', '', __( 'One Per Line', 'ipt_fsqm' ), 'fit' ),
			4 => array( $name_prefix . '[settings][options][__SDAKEY__][highlight]', '', '', false ),
			5 => array( $name_prefix . '[settings][options][__SDAKEY__][header]', '', __( 'Header', 'ipt_fsqm' ), 'fit' ),
			6 => array( $name_prefix . '[settings][options][__SDAKEY__][selected]', '', '', false ),
			7 => array( $name_prefix . '[settings][options][__SDAKEY__][footer]', '', __( 'Footer', 'ipt_fsqm' ), 'fit' ),
			8 => array( $name_prefix . '[settings][options][__SDAKEY__][scheme]', $color_schemes, 'theme' ),
			9 => array( $name_prefix . '[settings][options][__SDAKEY__][color]', '#F44336', __( 'Accent', 'ipt_fsqm' ) ),
		);

		$sda_items = array();
		$max_key = 0;
		foreach ( (array) $data['settings']['options'] as $o_key => $option ) {
			$max_key = max( array( $max_key, $o_key ) );
			$sda_items[] = array(
				0 => array( $name_prefix . '[settings][options][' . $o_key . '][label]', $option['label'], __( 'Title', 'ipt_fsqm' ) ),
				1 => array( $name_prefix . '[settings][options][' . $o_key . '][price]', $option['price'], __( 'Shown Price', 'ipt_fsqm' ), 'fit' ),
				2 => array( $name_prefix . '[settings][options][' . $o_key . '][numeric]', $option['numeric'], __( 'Numeric Value', 'ipt_fsqm' ), 'fit' ),
				3 => array( $name_prefix . '[settings][options][' . $o_key . '][attr]', $option['attr'], __( 'One Per Line', 'ipt_fsqm' ), 'fit' ),
				4 => array( $name_prefix . '[settings][options][' . $o_key . '][highlight]', '', '', ( isset( $option['highlight'] ) && true == $option['highlight'] ? true : false ) ),
				5 => array( $name_prefix . '[settings][options][' . $o_key . '][header]', $option['header'], __( 'Header', 'ipt_fsqm' ), 'fit' ),
				6 => array( $name_prefix . '[settings][options][' . $o_key . '][selected]', '', '', ( isset( $option['selected'] ) && true == $option['selected'] ? true : false ) ),
				7 => array( $name_prefix . '[settings][options][' . $o_key . '][footer]', $option['footer'], __( 'Footer', 'ipt_fsqm' ), 'fit' ),
				8 => array( $name_prefix . '[settings][options][' . $o_key . '][scheme]', $color_schemes, $option['scheme'] ),
				9 => array( $name_prefix . '[settings][options][' . $o_key . '][color]', $option['color'], __( 'Accent', 'ipt_fsqm' ) ),
			);
		}
		$options_items = array(
			0 => array(
				'ui' => 'sda_list',
				'param' => array(
					array(
						'columns' => $sda_columns,
						'labels' => $labels,
					),
					$sda_items,
					$sda_data,
					$max_key,
				),
			),
		);

		// Generate output
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
		?>
		<div class="ipt_uif_tabs">
			<ul>
				<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
				<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
				<li><a href="#<?php echo $tab_names; ?>_options"><?php _e( 'Options', 'ipt_fsqm' ); ?></a></li>
				<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
				<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
			</ul>
			<div id="<?php echo $tab_names; ?>_elm">
				<?php $this->ui->form_table( $appearance_items ); ?>
			</div>
			<div id="<?php echo $tab_names; ?>_ifs">
				<?php $this->ui->form_table( $interface_items ); ?>
			</div>
			<div id="<?php echo $tab_names; ?>_options">
				<?php $this->ui->form_table( $options_items ); ?>
			</div>
			<div id="<?php echo $tab_names; ?>_validation">
				<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
			</div>
			<div id="<?php echo $tab_names; ?>_logic">
				<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
			</div>
		</div>
		<?php
	}

	public function build_slider( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$id_label_prefix = $this->ui->generate_id_from_name( $name_prefix . '[settings][label]' );

		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_score"><?php _e( 'Scoring', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical_ui]', __( 'Vertical Slider Interface', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical_ui]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['vertical_ui'], '1', false, true, array( 'condid' => $this->ui->generate_id_from_name( $name_prefix . '_st_vertical_slider_wrap' ) ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want the UI of slider to be vertical, then enable this option. You will also need to set the height.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr id="<?php echo $this->ui->generate_id_from_name( $name_prefix . '_st_vertical_slider_wrap' ); ?>">
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][height]', __( 'Slider Height (px)', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->spinner( $name_prefix . '[settings][height]', $data['settings']['height'], __( 'Height in pixel', 'ipt_fsqm' ) ); ?></td>
						<td><?php $this->ui->help( __( 'Since you have chosen vertical slider, set the height of the slider.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][min]', __( 'Minimum Slider Value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][min]', $data['settings']['min'], __( 'Enter Number', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the minimum value of the slider.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][max]', __( 'Maximum Slider Value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][max]', $data['settings']['max'], __( 'Enter Number', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the maximum value of the slider.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][step]', __( 'Slider Step Value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][step]', $data['settings']['step'], __( 'Enter Number', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the step value of the slider.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][dmin]', __( 'Default Slider Value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][dmin]', $data['settings']['dmin'], __( 'Defined minimum', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Should be in between minimum and maximum. If left blank, the minimum value will be considered.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][nomin]', __( 'Do not accept minimum value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][nomin]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['nomin'] ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'If enabled, then the element would not accept the minimum value and it will trigger a validation error unless user selects anything but the minimum value.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][show_count]', __( 'Show Count', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][show_count]', __( 'Show', 'ipt_fsqm' ), __( 'Don\'t Show', 'ipt_fsqm' ), $data['settings']['show_count'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If turned on, then it will show the slider value count to the user.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][prefix]', __( 'Count Prefix', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][prefix]', $data['settings']['prefix'], __( 'Prefix', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter a string that is displayed before the count. Space is not included, so make sure you provide a space if you want to separate the prefix from the count.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][suffix]', __( 'Count Suffix', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][suffix]', $data['settings']['suffix'], __( 'Suffix', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter a string that is displayed after the count. Space is not included, so make sure you provide a space if you want to separate the prefix from the count.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][label][show]', __( 'Show labels on slider', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][label][show]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['label']['show'], '1', false, true, array(
								'condid' => $id_label_prefix . '_first_wrap,' . $id_label_prefix . '_last_wrap,' . $id_label_prefix . '_mid_wrap,' . $id_label_prefix . '_rest_wrap',
							) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Whether or not to show labels below slider pips.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr id="<?php echo $id_label_prefix . '_first_wrap'; ?>">
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][label][first]', __( 'First Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][label][first]', $data['settings']['label']['first'], __( 'Label', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The label for the first value of the slider.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr id="<?php echo $id_label_prefix . '_mid_wrap'; ?>">
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][label][mid]', __( 'Middle Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][label][mid]', $data['settings']['label']['mid'], __( 'Label', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The label for the mid value of the slider.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr id="<?php echo $id_label_prefix . '_last_wrap'; ?>">
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][label][last]', __( 'Last Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][label][last]', $data['settings']['label']['last'], __( 'Label', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The label for the last value of the slider.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr id="<?php echo $id_label_prefix . '_rest_wrap'; ?>">
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][label][rest]', __( 'Other Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][label][rest]', $data['settings']['label']['rest'], __( 'Comma separated', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The labels for the rest of the values of the slider. You have to enter comma separated values and it should match the slider steps less three. The first, last and middle labels will be positioned automatically.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][floats]', __( 'Floating Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][floats]', __( 'Show', 'ipt_fsqm' ), __( 'Don\'t Show', 'ipt_fsqm' ), $data['settings']['floats'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If turned on, then a floating tooltip will appear with the selected number in it.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_score">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][score]', __( 'Assign Score', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][score]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['score'] ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'If you want this slider contribute to the score obtained, then please enable it here.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][score_multiplier]', __( 'Score Multiplier', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][score_multiplier]', $data['settings']['score_multiplier'], '1' ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'If you want to adjust the score by multiplying the selected value with something, then please mention it here. By default it is 1.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>

		<?php
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	public function build_range( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$id_label_prefix = $this->ui->generate_id_from_name( $name_prefix . '[settings][label]' );
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_score"><?php _e( 'Scoring', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical_ui]', __( 'Vertical Range Interface', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical_ui]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['vertical_ui'], '1', false, true, array( 'condid' => $this->ui->generate_id_from_name( $name_prefix . '_st_vertical_slider_wrap' ) ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want the UI of slider to be vertical, then enable this option. You will also need to set the height.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr id="<?php echo $this->ui->generate_id_from_name( $name_prefix . '_st_vertical_slider_wrap' ); ?>">
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][height]', __( 'Range Height (px)', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->spinner( $name_prefix . '[settings][height]', $data['settings']['height'], __( 'Height in pixel', 'ipt_fsqm' ) ); ?></td>
						<td><?php $this->ui->help( __( 'Since you have chosen vertical slider, set the height of the slider.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][min]', __( 'Minimum Slider Value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][min]', $data['settings']['min'], __( 'Enter Number', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the minimum value of the slider.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][max]', __( 'Maximum Slider Value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][max]', $data['settings']['max'], __( 'Enter Number', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the maximum value of the slider.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][step]', __( 'Slider Step Value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][step]', $data['settings']['step'], __( 'Enter Number', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the step value of the slider.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][dmin]', __( 'Default Slider Value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][dmin]', $data['settings']['dmin'], __( 'Defined minimum', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Should be in between minimum and maximum. If left blank, the minimum value will be considered.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][dmax]', __( 'Default Maximum Value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][dmax]', $data['settings']['dmax'], __( 'Defined minimum', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Should be in between minimum and maximum. If left blank, the minimum value + step will be considered.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][nomin]', __( 'Do not accept minimum value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][nomin]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['nomin'] ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'If enabled, then the element would not accept the minimum value and it will trigger a validation error unless user selects anything but the minimum value.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][show_count]', __( 'Show Count', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][show_count]', __( 'Show', 'ipt_fsqm' ), __( 'Don\'t Show', 'ipt_fsqm' ), $data['settings']['show_count'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If turned on, then it will show the slider value count to the user.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][prefix]', __( 'Count Prefix', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][prefix]', $data['settings']['prefix'], __( 'Prefix', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter a string that is displayed before the count. Space is not included, so make sure you provide a space if you want to separate the prefix from the count.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][suffix]', __( 'Count Suffix', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][suffix]', $data['settings']['suffix'], __( 'Suffix', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter a string that is displayed after the count. Space is not included, so make sure you provide a space if you want to separate the prefix from the count.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][label][show]', __( 'Show labels on slider', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][label][show]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['label']['show'], '1', false, true, array(
								'condid' => $id_label_prefix . '_first_wrap,' . $id_label_prefix . '_last_wrap,' . $id_label_prefix . '_mid_wrap,' . $id_label_prefix . '_rest_wrap',
							) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Whether or not to show labels below slider pips.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr id="<?php echo $id_label_prefix . '_first_wrap'; ?>">
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][label][first]', __( 'First Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][label][first]', $data['settings']['label']['first'], __( 'Label', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The label for the first value of the slider.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr id="<?php echo $id_label_prefix . '_mid_wrap'; ?>">
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][label][mid]', __( 'Middle Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][label][mid]', $data['settings']['label']['mid'], __( 'Label', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The label for the mid value of the slider.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr id="<?php echo $id_label_prefix . '_last_wrap'; ?>">
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][label][last]', __( 'Last Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][label][last]', $data['settings']['label']['last'], __( 'Label', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The label for the last value of the slider.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr id="<?php echo $id_label_prefix . '_rest_wrap'; ?>">
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][label][rest]', __( 'Other Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][label][rest]', $data['settings']['label']['rest'], __( 'Comma separated', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The labels for the rest of the values of the slider. You have to enter comma separated values and it should match the slider steps less three. The first, last and middle labels will be positioned automatically.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][floats]', __( 'Floating Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][floats]', __( 'Show', 'ipt_fsqm' ), __( 'Don\'t Show', 'ipt_fsqm' ), $data['settings']['floats'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If turned on, then a floating tooltip will appear with the selected number in it.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_score">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][score]', __( 'Assign Score', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][score]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['score'] ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'If you want this slider contribute to the score obtained, then please enable it here.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][score_multiplier]', __( 'Score Multiplier', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][score_multiplier]', $data['settings']['score_multiplier'], '1' ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'If you want to adjust the score by multiplying the selected value with something, then please mention it here. By default it is 1.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<?php
					$score_formula = array(
						array(
							'label' => __( 'Average of two', 'ipt_fsqm' ),
							'value' => 'avg',
						),
						array(
							'label' => __( 'Addition of two', 'ipt_fsqm' ),
							'value' => 'add',
						),
						array(
							'label' => __( 'Difference of two', 'ipt_fsqm' ),
							'value' => 'diff',
						),
						array(
							'label' => __( 'Minimum of two', 'ipt_fsqm' ),
							'value' => 'min',
						),
						array(
							'label' => __( 'Maximum of two', 'ipt_fsqm' ),
							'value' => 'max',
						),
					);
					?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][formula]', __( 'Score Calculation Formula', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->select( $name_prefix . '[settings][formula]', $score_formula, $data['settings']['formula'] ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'Since the value is actually a range please specify how the resulting score will be calculated.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	public function build_spinners( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$sda_columns = array(
			0 => array(
				'label' => __( 'Option', 'ipt_fsqm' ),
				'type' => 'text',
				'size' => '50',
			),
			1 => array(
				'label' => __( 'Min', 'ipt_fsqm' ),
				'type' => 'spinner',
				'size' => '12',
			),
			2 => array(
				'label' => __( 'Max', 'ipt_fsqm' ),
				'type' => 'spinner',
				'size' => '12',
			),
			3 => array(
				'label' => __( 'Step', 'ipt_fsqm' ),
				'type' => 'spinner',
				'size' => '12',
			),
		);
		$labels = array(
			'confirm' => __( 'Confirm delete. This action can not be undone.', 'ipt_fsqm' ),
			'add' => __( 'Add New Option', 'ipt_fsqm' ),
			'del' => __( 'Click to delete', 'ipt_fsqm' ),
			'drag' => __( 'Drag this to rearrange', 'ipt_fsqm' ),
		);
		$sda_data = array(
			0 => array( $name_prefix . '[settings][options][__SDAKEY__][label]', '', __( 'Enter label', 'ipt_fsqm' ) ),
			1 => array( $name_prefix . '[settings][options][__SDAKEY__][min]', '', __( 'Minimum', 'ipt_fsqm' ) ),
			2 => array( $name_prefix . '[settings][options][__SDAKEY__][max]', '', __( 'Maximum', 'ipt_fsqm' ) ),
			3 => array( $name_prefix . '[settings][options][__SDAKEY__][step]', '', __( 'Step', 'ipt_fsqm' ) ),
		);
		$sda_items = array();
		$max_key = null;
		foreach ( (array)$data['settings']['options'] as $o_key => $option ) {
			if ( ! is_array( $option ) ) {
				// backward compatibility -2.5.0
				$option = array(
					'label' => $option,
				);
			}
			// Add overrideable min, max and step
			// With backward compatibility with -2.5.0
			foreach ( array( 'min', 'max', 'step' ) as $ovkey ) {
				if ( ! isset( $option[$ovkey] ) ) {
					$option[$ovkey] = '';
				}
			}
			$max_key = max( array( $max_key, $o_key ) );
			$sda_items[] = array(
				0 => array( $name_prefix . '[settings][options][' . $o_key . '][label]', $option['label'], __( 'Enter label', 'ipt_fsqm' ) ),
				1 => array( $name_prefix . '[settings][options][' . $o_key . '][min]', $option['min'], __( 'Minimum', 'ipt_fsqm' ) ),
				2 => array( $name_prefix . '[settings][options][' . $o_key . '][max]', $option['max'], __( 'Maximum', 'ipt_fsqm' ) ),
				3 => array( $name_prefix . '[settings][options][' . $o_key . '][step]', $option['step'], __( 'Step', 'ipt_fsqm' ) ),
			);
		}
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_items"><?php _e( 'Items', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_items">
			<table class="form-table">
				<tbody>
					<tr>
						<th colspan="2"><?php $this->ui->generate_label( '', __( 'Item List', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->help( __( 'Enter the options. Any minimum, maximum and/or step value you set here, will override the global one.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<td colspan="3">

							<?php $this->ui->sda_list( array(
								'columns' => $sda_columns,
								'labels' => $labels,
							), $sda_items, $sda_data, $max_key ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<table class="form-table">
				<tbody>
					<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'], false ); ?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][min]', __( 'Minimum Value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][min]', $data['settings']['min'], __( 'No bound', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the minimum value of the spinner.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][max]', __( 'Maximum Value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][max]', $data['settings']['max'], __( 'No bound', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the maximum value of the spinner.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][step]', __( 'Step Value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][step]', $data['settings']['step'], __( 'Enter Number', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the step value of the spinner.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	public function build_grading( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$id_label_prefix = $this->ui->generate_id_from_name( $name_prefix . '[settings][label]' );
		$sda_columns = array(
			0 => array(
				'label' => __( 'Option', 'ipt_fsqm' ),
				'type' => 'text',
				'size' => '30',
			),
			1 => array(
				'label' => __( 'Prefix', 'ipt_fsqm' ),
				'type' => 'text',
				'size' => '14',
			),
			2 => array(
				'label' => __( 'Suffix', 'ipt_fsqm' ),
				'type' => 'text',
				'size' => '14',
			),
			3 => array(
				'label' => __( 'Min', 'ipt_fsqm' ),
				'type' => 'spinner',
				'size' => '14',
			),
			4 => array(
				'label' => __( 'Max', 'ipt_fsqm' ),
				'type' => 'spinner',
				'size' => '14',
			),
			5 => array(
				'label' => __( 'Step', 'ipt_fsqm' ),
				'type' => 'spinner',
				'size' => '14',
			),
		);
		$labels = array(
			'confirm' => __( 'Confirm delete. This action can not be undone.', 'ipt_fsqm' ),
			'add' => __( 'Add New Option', 'ipt_fsqm' ),
			'del' => __( 'Click to delete', 'ipt_fsqm' ),
			'drag' => __( 'Drag this to rearrange', 'ipt_fsqm' ),
		);
		$sda_data = array(
			0 => array( $name_prefix . '[settings][options][__SDAKEY__][label]', '', __( 'Enter label', 'ipt_fsqm' ) ),
			1 => array( $name_prefix . '[settings][options][__SDAKEY__][prefix]', '', __( 'Prefix', 'ipt_fsqm' ) ),
			2 => array( $name_prefix . '[settings][options][__SDAKEY__][suffix]', '', __( 'Suffix', 'ipt_fsqm' ) ),
			3 => array( $name_prefix . '[settings][options][__SDAKEY__][min]', '', __( 'Minimum', 'ipt_fsqm' ) ),
			4 => array( $name_prefix . '[settings][options][__SDAKEY__][max]', '', __( 'Maximum', 'ipt_fsqm' ) ),
			5 => array( $name_prefix . '[settings][options][__SDAKEY__][step]', '', __( 'Step', 'ipt_fsqm' ) ),
		);
		$sda_items = array();
		$max_key = null;
		foreach ( (array)$data['settings']['options'] as $o_key => $option ) {
			if ( ! is_array( $option ) ) {
				// backward compatibility -2.4.0
				$option = array(
					'label' => $option,
					'prefix' => '',
					'suffix' => '',
				);
			}
			// Add overrideable min, max and step
			// With backward compatibility with -2.5.0
			foreach ( array( 'min', 'max', 'step' ) as $ovkey ) {
				if ( ! isset( $option[$ovkey] ) ) {
					$option[$ovkey] = '';
				}
			}

			$max_key = max( array( $max_key, $o_key ) );
			$sda_items[] = array(
				0 => array( $name_prefix . '[settings][options][' . $o_key . '][label]', $option['label'], __( 'Enter label', 'ipt_fsqm' ) ),
				1 => array( $name_prefix . '[settings][options][' . $o_key . '][prefix]', $option['prefix'], __( 'Prefix', 'ipt_fsqm' ) ),
				2 => array( $name_prefix . '[settings][options][' . $o_key . '][suffix]', $option['suffix'], __( 'Suffix', 'ipt_fsqm' ) ),
				3 => array( $name_prefix . '[settings][options][' . $o_key . '][min]', $option['min'], __( 'Minimum', 'ipt_fsqm' ) ),
				4 => array( $name_prefix . '[settings][options][' . $o_key . '][max]', $option['max'], __( 'Maximum', 'ipt_fsqm' ) ),
				5 => array( $name_prefix . '[settings][options][' . $o_key . '][step]', $option['step'], __( 'Step', 'ipt_fsqm' ) ),
			);
		}
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_items"><?php _e( 'Items', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_score"><?php _e( 'Scoring', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][range]', __( 'Use Range', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][range]', __( 'Ranged Input', 'ipt_fsqm' ), __( 'Single Input', 'ipt_fsqm' ), $data['settings']['range'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If turned on, then it will prompt the user to select a range of values instead of a single value.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical_ui]', __( 'Vertical Slider Interface', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical_ui]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['vertical_ui'], '1', false, true, array( 'condid' => $this->ui->generate_id_from_name( $name_prefix . '_st_vertical_slider_wrap' ) ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want the UI of slider to be vertical, then enable this option. You will also need to set the height.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr id="<?php echo $this->ui->generate_id_from_name( $name_prefix . '_st_vertical_slider_wrap' ); ?>">
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][height]', __( 'Slider Height (px)', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->spinner( $name_prefix . '[settings][height]', $data['settings']['height'], __( 'Height in pixel', 'ipt_fsqm' ) ); ?></td>
						<td><?php $this->ui->help( __( 'Since you have chosen vertical slider, set the height of the slider.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][min]', __( 'Minimum Value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][min]', $data['settings']['min'], __( 'Enter Number', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the minimum value of the slider.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][max]', __( 'Maximum Value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][max]', $data['settings']['max'], __( 'Enter Number', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the maximum value of the slider.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][step]', __( 'Step Value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][step]', $data['settings']['step'], __( 'Enter Number', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the step value of the slider.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][dmin]', __( 'Default Minimum Value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][dmin]', $data['settings']['dmin'], __( 'Defined minimum', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Should be in between minimum and maximum. If left blank, the minimum value will be considered.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][dmax]', __( 'Default Maximum Value (for range)', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][dmax]', $data['settings']['dmax'], __( 'Defined maximum', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Should be in between minimum and maximum. If left blank, the minimum value + step will be considered.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][nomin]', __( 'Do not accept minimum value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][nomin]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['nomin'] ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'If enabled, then the element would not accept the minimum value and it will trigger a validation error unless user selects anything but the minimum value.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][show_count]', __( 'Show Count', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][show_count]', __( 'Show', 'ipt_fsqm' ), __( 'Don\'t Show', 'ipt_fsqm' ), $data['settings']['show_count'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If turned on, then it will show the slider value count to the user.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][label][show]', __( 'Show labels on slider', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][label][show]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['label']['show'], '1', false, true, array(
								'condid' => $id_label_prefix . '_first_wrap,' . $id_label_prefix . '_last_wrap,' . $id_label_prefix . '_mid_wrap,' . $id_label_prefix . '_rest_wrap',
							) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Whether or not to show labels below slider pips.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr id="<?php echo $id_label_prefix . '_first_wrap'; ?>">
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][label][first]', __( 'First Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][label][first]', $data['settings']['label']['first'], __( 'Label', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The label for the first value of the slider.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr id="<?php echo $id_label_prefix . '_mid_wrap'; ?>">
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][label][mid]', __( 'Middle Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][label][mid]', $data['settings']['label']['mid'], __( 'Label', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The label for the mid value of the slider.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr id="<?php echo $id_label_prefix . '_last_wrap'; ?>">
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][label][last]', __( 'Last Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][label][last]', $data['settings']['label']['last'], __( 'Label', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The label for the last value of the slider.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr id="<?php echo $id_label_prefix . '_rest_wrap'; ?>">
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][label][rest]', __( 'Other Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][label][rest]', $data['settings']['label']['rest'], __( 'Comma separated', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The labels for the rest of the values of the slider. You have to enter comma separated values and it should match the slider steps less three. The first, last and middle labels will be positioned automatically.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][floats]', __( 'Floating Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][floats]', __( 'Show', 'ipt_fsqm' ), __( 'Don\'t Show', 'ipt_fsqm' ), $data['settings']['floats'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If turned on, then a floating tooltip will appear with the selected number in it.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_items">
			<table class="form-table">
				<tbody>
					<tr>
						<th colspan="2"><?php $this->ui->generate_label( '', __( 'Item List', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->help( __( 'Enter the options. Any minimum, maximum and/or step value you set here, will override the global one.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<td colspan="3">
							<?php $this->ui->sda_list( array(
								'columns' => $sda_columns,
								'labels' => $labels,
							), $sda_items, $sda_data, $max_key ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_score">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][score]', __( 'Assign Score', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][score]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['score'] ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'If you want this slider contribute to the score obtained, then please enable it here.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][score_multiplier]', __( 'Score Multiplier', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][score_multiplier]', $data['settings']['score_multiplier'], '1' ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'If you want to adjust the score by multiplying the selected value with something, then please mention it here. By default it is 1.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<?php
					$score_formula = array(
						array(
							'label' => __( 'Average of two', 'ipt_fsqm' ),
							'value' => 'avg',
						),
						array(
							'label' => __( 'Addition of two', 'ipt_fsqm' ),
							'value' => 'add',
						),
						array(
							'label' => __( 'Difference of two', 'ipt_fsqm' ),
							'value' => 'diff',
						),
						array(
							'label' => __( 'Minimum of two', 'ipt_fsqm' ),
							'value' => 'min',
						),
						array(
							'label' => __( 'Maximum of two', 'ipt_fsqm' ),
							'value' => 'max',
						),
					);
					?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][formula]', __( 'Score Calculation Formula', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->select( $name_prefix . '[settings][formula]', $score_formula, $data['settings']['formula'] ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'Since the value is actually a range please specify how the resulting score will be calculated.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>

		<?php
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	public function build_smileyrating( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$sda_columns = array(
			0 => array(
				'label' => __( 'Show', 'ipt_fsqm' ),
				'type' => 'checkbox',
				'size' => '10',
			),
			1 => array(
				'label' => __( 'Smiley', 'ipt_fsqm' ),
				'type' => 'print_icon',
				'size' => '15',
			),
			2 => array(
				'label' => __( 'Feedback Label', 'ipt_fsqm' ),
				'type' => 'text',
				'size' => '30',
			),
			3 => array(
				'label' => __( 'Score', 'ipt_fsqm' ),
				'type' => 'spinner',
				'size' => '20',
			),
			4 => array(
				'label' => __( 'Numeric', 'ipt_fsqm' ),
				'type' => 'spinner',
				'size' => '20',
			),
		);
		$sda_items = array();
		$sda_data = array(
			0 => array( $name_prefix . '[settings][enabled][__SDAKEY__]', array( 'value' => '1', 'label' => '' ), '' ),
			1 => array( 'none', 24 ),
			2 => array( $name_prefix . '[settings][labels][__SDAKEY__]', '', '' ),
			3 => array( $name_prefix . '[settings][scores][__SDAKEY__]', '', __( 'Optional', 'ipt_fsqm' ) ),
			4 => array( $name_prefix . '[settings][num][__SDAKEY__]', '', __( 'Optional', 'ipt_fsqm' ) ),
		);
		$setting_to_icon_map = array(
			'frown' => 'angry',
			'sad' => 'sad',
			'neutral' => 'neutral',
			'happy' => 'smiley',
			'excited' => 'happy',
		);
		foreach ( array( 'frown', 'sad', 'neutral', 'happy', 'excited' ) as $srkey ) {
			$sda_items[] = array(
				0 => array( $name_prefix . '[settings][enabled][' . $srkey . ']', array( 'value' => '1', 'label' => '' ), $data['settings']['enabled'][$srkey] ),
				1 => array( $setting_to_icon_map[$srkey], 24 ),
				2 => array( $name_prefix . '[settings][labels][' . $srkey . ']', $data['settings']['labels'][$srkey], '' ),
				3 => array( $name_prefix . '[settings][scores][' . $srkey . ']', $data['settings']['scores'][$srkey], __( 'Optional', 'ipt_fsqm' ) ),
				4 => array( $name_prefix . '[settings][num][' . $srkey . ']', $data['settings']['num'][$srkey], __( 'Optional', 'ipt_fsqm' ) ),
			);
		}
		$max_key = 4;
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
		?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][show_feedback]', __( 'Optional Feedback', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][show_feedback]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['show_feedback'] ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'If you want to ask for feedback, then enable it here and a textbox will appear upon selecting a smiley.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][feedback_label]', __( 'Feedback Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][feedback_label]', $data['settings']['feedback_label'], __( 'Enter label', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the label that will shown on an empty feedback textarea.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][reverse_order]', __( 'Reverse Smiley Order', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][reverse_order]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['reverse_order'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'By default smileys appear from frown to happy. Enable this to reverse the order.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th colspan="3">
							<?php $this->ui->sda_list(
								array(
									'columns' => $sda_columns,
									'features' => array(
										'draggable' => false,
										'addable' => false,
									),
								), $sda_items, $sda_data, $max_key
							); ?>
						</th>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>

	<?php
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	public function build_starrating( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$sda_columns = array(
			0 => array(
				'label' => __( 'Option', 'ipt_fsqm' ),
				'type' => 'text',
				'size' => '85',
			),
		);
		$labels = array(
			'confirm' => __( 'Confirm delete. This action can not be undone.', 'ipt_fsqm' ),
			'add' => __( 'Add New Option', 'ipt_fsqm' ),
			'del' => __( 'Click to delete', 'ipt_fsqm' ),
			'drag' => __( 'Drag this to rearrange', 'ipt_fsqm' ),
		);
		$sda_data = array(
			0 => array( $name_prefix . '[settings][options][__SDAKEY__]', '', __( 'Enter label', 'ipt_fsqm' ), 'fit' ),
		);
		$sda_items = array();
		$max_key = null;
		foreach ( (array)$data['settings']['options'] as $o_key => $option ) {
			$max_key = max( array( $max_key, $o_key ) );
			$sda_items[] = array(
				0 => array( $name_prefix . '[settings][options][' . $o_key . ']', $option, __( 'Enter label', 'ipt_fsqm' ), 'fit' ),
			);
		}
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ratings"><?php _e( 'Items', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_score"><?php _e( 'Scoring', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ratings">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][max]', __( 'Maximum Rating Value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][max]', $data['settings']['max'], __( 'Enter Number', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the maximum value of the rating.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][label_low]', __( 'Label for lowest value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][label_low]', $data['settings']['label_low'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter a string that is displayed before the lowest value.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][label_high]', __( 'Label for highest value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][label_high]', $data['settings']['label_high'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter a string that is displayed after the highest value.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th colspan="2"><?php $this->ui->generate_label( '', __( 'Option List', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->help( __( 'Enter the options', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<td colspan="3">
							<?php $this->ui->sda_list( array(
								'columns' => $sda_columns,
								'labels' => $labels,
							), $sda_items, $sda_data, $max_key ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_score">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][score]', __( 'Assign Score', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][score]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['score'] ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'If you want this slider contribute to the score obtained, then please enable it here.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][score_multiplier]', __( 'Score Multiplier', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][score_multiplier]', $data['settings']['score_multiplier'], '1' ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'If you want to adjust the score by multiplying the selected value with something, then please mention it here. By default it is 1.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>

		<?php
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	public function build_scalerating( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$sda_columns = array(
			0 => array(
				'label' => __( 'Option', 'ipt_fsqm' ),
				'type' => 'text',
				'size' => '85',
			),
		);
		$labels = array(
			'confirm' => __( 'Confirm delete. This action can not be undone.', 'ipt_fsqm' ),
			'add' => __( 'Add New Option', 'ipt_fsqm' ),
			'del' => __( 'Click to delete', 'ipt_fsqm' ),
			'drag' => __( 'Drag this to rearrange', 'ipt_fsqm' ),
		);
		$sda_data = array(
			0 => array( $name_prefix . '[settings][options][__SDAKEY__]', '', __( 'Enter label', 'ipt_fsqm' ), 'fit' ),
		);
		$sda_items = array();
		$max_key = null;
		foreach ( (array)$data['settings']['options'] as $o_key => $option ) {
			$max_key = max( array( $max_key, $o_key ) );
			$sda_items[] = array(
				0 => array( $name_prefix . '[settings][options][' . $o_key . ']', $option, __( 'Enter label', 'ipt_fsqm' ), 'fit' ),
			);
		}
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ratings"><?php _e( 'Items', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_score"><?php _e( 'Scoring', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ratings">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][max]', __( 'Maximum Rating Value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][max]', $data['settings']['max'], __( 'Enter Number', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the maximum value of the rating.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][label_low]', __( 'Label for lowest value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][label_low]', $data['settings']['label_low'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter a string that is displayed before the lowest value.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][label_high]', __( 'Label for highest value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][label_high]', $data['settings']['label_high'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter a string that is displayed after the highest value.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th colspan="2"><?php $this->ui->generate_label( '', __( 'Option List', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->help( __( 'Enter the options', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<td colspan="3">
							<?php $this->ui->sda_list( array(
								'columns' => $sda_columns,
								'labels' => $labels,
							), $sda_items, $sda_data, $max_key ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_score">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][score]', __( 'Assign Score', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][score]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['score'] ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'If you want this slider contribute to the score obtained, then please enable it here.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][score_multiplier]', __( 'Score Multiplier', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][score_multiplier]', $data['settings']['score_multiplier'], '1' ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'If you want to adjust the score by multiplying the selected value with something, then please mention it here. By default it is 1.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	public function build_matrix( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$sda_columns = array(
			0 => array(
				'label' => __( 'Label', 'ipt_fsqm' ),
				'type' => 'text',
				'size' => '100',
			),
		);
		$labels = array(
			'confirm' => __( 'Confirm delete. This action can not be undone.', 'ipt_fsqm' ),
			'add' => __( 'Add New Item', 'ipt_fsqm' ),
			'del' => __( 'Click to delete', 'ipt_fsqm' ),
			'drag' => __( 'Drag this to rearrange', 'ipt_fsqm' ),
		);
		$sda_data_row = array(
			0 => array( $name_prefix . '[settings][rows][__SDAKEY__]', '', __( 'Enter label', 'ipt_fsqm' ), 'fit' ),
		);
		$sda_items_rows = array();
		$max_key_row = null;
		foreach ( (array)$data['settings']['rows'] as $o_key => $option ) {
			$max_key_row = max( array( $max_key_row, $o_key ) );
			$sda_items_rows[] = array(
				0 => array( $name_prefix . '[settings][rows][' . $o_key . ']', $option, __( 'Enter label', 'ipt_fsqm' ), 'fit' ),
			);
		}

		$sda_col_columns = array(
			0 => array(
				'label' => __( 'Label', 'ipt_fsqm' ),
				'type' => 'text',
				'size' => '60',
			),
			1 => array(
				'label' => __( 'Score', 'ipt_fsqm' ),
				'type' => 'spinner',
				'size' => '20',
			),
			2 => array(
				'label' => __( 'Numeric', 'ipt_fsqm' ),
				'type' => 'spinner',
				'size' => '20',
			),
		);
		$sda_data_column = array(
			0 => array( $name_prefix . '[settings][columns][__SDAKEY__]', '', __( 'Enter label', 'ipt_fsqm' ), 'fit' ),
			1 => array( $name_prefix . '[settings][scores][__SDAKEY__]', '', __( 'Score (Optional)', 'ipt_fsqm' ), 'fit' ),
			2 => array( $name_prefix . '[settings][numerics][__SDAKEY__]', '', __( 'Numeric (Optional)', 'ipt_fsqm' ), 'fit' ),
		);
		$sda_items_columns = array();
		$max_key_column = null;
		foreach ( (array) $data['settings']['columns'] as $o_key => $option ) {
			$max_key_column = max( array( $max_key_column, $o_key ) );
			$sda_items_columns[] = array(
				0 => array( $name_prefix . '[settings][columns][' . $o_key . ']', $option, __( 'Enter label', 'ipt_fsqm' ), 'fit' ),
				1 => array( $name_prefix . '[settings][scores][' . $o_key . ']', isset( $data['settings']['scores'][$o_key] ) ? $data['settings']['scores'][$o_key] : '', __( 'Score (Optional)', 'ipt_fsqm' ), 'fit' ),
				2 => array( $name_prefix . '[settings][numerics][' . $o_key . ']', isset( $data['settings']['numerics'][$o_key] ) ? $data['settings']['numerics'][$o_key] : '', __( 'Numeric (Optional)', 'ipt_fsqm' ), 'fit' ),
			);
		}
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_rows"><?php _e( 'Rows', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_columns"><?php _e( 'Cols', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][multiple]', __( 'Multiple Values', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][multiple]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['multiple'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If turned on, then the user will be able to select multiple values across the row.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the icon you want to appear inside the selected radio/checkbox.', 'ipt_fsqm' ) ) ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_rows">
			<table class="form-table">
				<tbody>
					<tr>
						<th colspan="2"><?php $this->ui->generate_label( '', __( 'List of Rows', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->help( __( 'Enter the Rows. These are basically the primary ratings.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<td colspan="3">
							<?php $this->ui->sda_list( array(
								'columns' => $sda_columns,
								'labels' => $labels,
							), $sda_items_rows, $sda_data_row, $max_key_row ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_columns">
			<table class="form-table">
				<tbody>
					<tr>
						<th colspan="2"><?php $this->ui->generate_label( '', __( 'List of Columns', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->help( __( 'Enter the Columns. These are basically the selection options.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<td colspan="3">
							<?php $this->ui->sda_list( array(
								'columns' => $sda_col_columns,
								'labels' => $labels,
							), $sda_items_columns, $sda_data_column, $max_key_column ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	public function build_matrix_dropdown( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$sda_columns = array(
			0 => array(
				'label' => __( 'Label', 'ipt_fsqm' ),
				'type' => 'text',
				'size' => '100',
			),
		);
		$labels = array(
			'confirm' => __( 'Confirm delete. This action can not be undone.', 'ipt_fsqm' ),
			'add' => __( 'Add New Item', 'ipt_fsqm' ),
			'del' => __( 'Click to delete', 'ipt_fsqm' ),
			'drag' => __( 'Drag this to rearrange', 'ipt_fsqm' ),
		);
		$sda_data_row = array(
			0 => array( $name_prefix . '[settings][rows][__SDAKEY__]', '', __( 'Enter label', 'ipt_fsqm' ), 'fit' ),
		);
		$sda_items_rows = array();
		$max_key_row = null;
		foreach ( (array)$data['settings']['rows'] as $o_key => $option ) {
			$max_key_row = max( array( $max_key_row, $o_key ) );
			$sda_items_rows[] = array(
				0 => array( $name_prefix . '[settings][rows][' . $o_key . ']', $option, __( 'Enter label', 'ipt_fsqm' ), 'fit' ),
			);
		}

		$sda_col_columns = array(
			0 => array(
				'label' => __( 'Label', 'ipt_fsqm' ),
				'type' => 'text',
				'size' => '70',
			),
			1 => array(
				'label' => __( 'Score Multiplier', 'ipt_fsqm' ),
				'type' => 'spinner',
				'size' => '30',
			),
		);
		$sda_data_column = array(
			0 => array( $name_prefix . '[settings][columns][__SDAKEY__]', '', __( 'Enter label', 'ipt_fsqm' ), 'fit' ),
			1 => array( $name_prefix . '[settings][scores][__SDAKEY__]', '', __( 'Score (Optional)', 'ipt_fsqm' ), 'fit' ),
		);
		$sda_items_columns = array();
		$max_key_column = null;
		foreach ( (array)$data['settings']['columns'] as $o_key => $option ) {
			$max_key_column = max( array( $max_key_column, $o_key ) );
			$sda_items_columns[] = array(
				0 => array( $name_prefix . '[settings][columns][' . $o_key . ']', $option, __( 'Enter label', 'ipt_fsqm' ), 'fit' ),
				1 => array( $name_prefix . '[settings][scores][' . $o_key . ']', isset( $data['settings']['scores'][$o_key] ) ? $data['settings']['scores'][$o_key] : '', __( 'Score (Optional)', 'ipt_fsqm' ), 'fit' ),
			);
		}

		$sda_opt_columns = array(
			0 => array(
				'label' => __( 'Option', 'ipt_fsqm' ),
				'type' => 'text',
				'size' => '60',
			),
			1 => array(
				'label' => __( 'Score', 'ipt_fsqm' ),
				'type' => 'spinner',
				'size' => '20',
			),
			2 => array(
				'label' => __( 'Numeric', 'ipt_fsqm' ),
				'type' => 'spinner',
				'size' => '20',
			),
		);
		$sda_opt_data = array(
			0 => array( $name_prefix . '[settings][options][__SDAKEY__][label]', '', __( 'Enter Option Label', 'ipt_fsqm' ), 'fit' ),
			1 => array( $name_prefix . '[settings][options][__SDAKEY__][score]', '', __( 'Score (Optional)', 'ipt_fsqm' ), 'fit' ),
			2 => array( $name_prefix . '[settings][options][__SDAKEY__][num]', '', __( 'Numeric Value', 'ipt_fsqm' ), 'fit' ),
		);

		$sda_opt_items = array();
		$max_opt_key = null;
		foreach ( $data['settings']['options'] as $o_key => $option ) {
			$max_opt_key = max( array( $max_opt_key, $o_key ) );
			$new_data = array(
				0 => array( $name_prefix . '[settings][options][' . $o_key . '][label]', $option['label'], __( 'Enter Option Label', 'ipt_fsqm' ), 'fit' ),
				1 => array( $name_prefix . '[settings][options][' . $o_key . '][score]', $option['score'], __( 'Score (Optional)', 'ipt_fsqm' ), 'fit' ),
				2 => array( $name_prefix . '[settings][options][' . $o_key . '][num]', $option['num'], __( 'Numeric Value', 'ipt_fsqm' ), 'fit' ),
			);

			$sda_opt_items[] = $new_data;
		}

		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_rows"><?php _e( 'Rows', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_columns"><?php _e( 'Cols', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_options"><?php _e( 'Options', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_rows">
			<table class="form-table">
				<tbody>
					<tr>
						<th colspan="2"><?php $this->ui->generate_label( '', __( 'List of Rows', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->help( __( 'Enter the Rows. These are basically the primary ratings.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<td colspan="3">
							<?php $this->ui->sda_list( array(
								'columns' => $sda_columns,
								'labels' => $labels,
							), $sda_items_rows, $sda_data_row, $max_key_row ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_columns">
			<table class="form-table">
				<tbody>
					<tr>
						<th colspan="2"><?php $this->ui->generate_label( '', __( 'List of Columns', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->help( __( 'Enter the Columns. These are basically the selection options.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<td colspan="3">
							<?php $this->ui->sda_list( array(
								'columns' => $sda_col_columns,
								'labels' => $labels,
							), $sda_items_columns, $sda_data_column, $max_key_column ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_options">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][empty]', __( 'Empty Option Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][empty]', $data['settings']['empty'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the first empty option that is shown to the user.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][multiple]', __( 'Multiple Values', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][multiple]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['multiple'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If turned on, then the user will be able to select multiple values across the row.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th colspan="2"><?php $this->ui->generate_label( '', __( 'Dropdown Options', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->help( __( 'Enter the Options. These will appear inside every column.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<td colspan="3">
							<?php $this->ui->sda_list( array(
								'columns' => $sda_opt_columns,
								'labels' => $labels,
							), $sda_opt_items, $sda_opt_data, $max_opt_key ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	public function build_likedislike( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
	?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][liked]', __( 'liked by Default', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][liked]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['liked'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Turn this feature on to make it liked by default.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][like]', __( 'Liked State Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][like]', $data['settings']['like'], __( 'Enter label', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the liked state label that will be shown to the user.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][dislike]', __( 'Disliked State Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][dislike]', $data['settings']['dislike'], __( 'Enter label', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the disliked state label that will be shown to the user.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][show_feedback]', __( 'Optional Feedback', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][show_feedback]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['show_feedback'] ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'If you want to ask for feedback, then enable it here and a textbox will appear upon selecting a smiley.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][feedback_label]', __( 'Feedback Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][feedback_label]', $data['settings']['feedback_label'], __( 'Enter label', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the label that will shown on an empty feedback textarea.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	public function build_toggle( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
	?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_states"><?php _e( 'States', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_states">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][checked]', __( 'Checked by Default', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][checked]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['checked'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Turn this feature on to make the checkbox checked by default.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][on]', __( 'Checked State Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][on]', $data['settings']['on'], __( 'Enter label', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the checked state label that will be shown to the user.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][off]', __( 'Unchecked State Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][off]', $data['settings']['off'], __( 'Enter label', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the unchecked state label that will be shown to the user.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>

		<?php
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	public function build_sorting( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$this->build_user_sortable( $element, $key, $data, $element_structure, $name_prefix, true );
	}

	public function build_feedback_large( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$types = array(
			array(
				'label' => 'Standard Qwerty Keyboard',
				'value' => 'qwerty',
			),
			array(
				'label' => 'International Qwerty Keyboard',
				'value' => 'qwerty',
			),
			array(
				'label' => 'Numerical Keyboard (ten-key)',
				'value' => 'num',
			),
			array(
				'label' => 'Alphabetical Keyboard',
				'value' => 'alpha',
			),
			array(
				'label' => 'Dvorak Simplified Keyboard',
				'value' => 'dvorak',
			),
		);
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
	?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_score"><?php _e( 'Scoring', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][placeholder]', __( 'Placeholder Text', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][placeholder]', $data['settings']['placeholder'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Text that is shown by default when the field is empty.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][native_placeholder]', __( 'Use Native Placeholder', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][native_placeholder]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['native_placeholder'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the placeholder will not animate and move above the element.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the icon you want to appear before the text. Select none to disable.', 'ipt_fsqm' ) ) ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][keypad]', __( 'Show Keyboard', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][keypad]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['keypad'], '1', false, true, array(
								'condid' => 'ipt_fsqm_builder_fl_' . $key . '_wrap',
							) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Whether or not to show a keyboard on this element.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr id="<?php echo 'ipt_fsqm_builder_fl_' . $key . '_wrap'; ?>">
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][ktype]', __( 'Keyboard Type', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->select( $name_prefix . '[settings][ktype]', $types, $data['settings']['ktype'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the keyboard type.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][email]', __( 'Send to Address', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][email]', $data['settings']['email'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The email address to which this submission will be sent. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<?php $this->_helper_build_prefil_text( $name_prefix, $data ); ?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][readonly]', __( 'Readonly', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][readonly]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['readonly'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the recorded value would not be editable by user. Make sure the validation matches, otherwise it might lead to error.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_score">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][score]', __( 'Score', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][score]', $data['settings']['score'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The score admin can assign for this question. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<?php $this->feedback_auto_score( $name_prefix, $key, $data ); ?>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	public function build_feedback_small( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$types = array(
			array(
				'label' => 'Standard Qwerty Keyboard',
				'value' => 'qwerty',
			),
			array(
				'label' => 'International Qwerty Keyboard',
				'value' => 'qwerty',
			),
			array(
				'label' => 'Numerical Keyboard (ten-key)',
				'value' => 'num',
			),
			array(
				'label' => 'Alphabetical Keyboard',
				'value' => 'alpha',
			),
			array(
				'label' => 'Dvorak Simplified Keyboard',
				'value' => 'dvorak',
			),
		);
		// Inline Appearance
		$inline_items = [];
		// Enabled
		$inline_items[] = [
			'name' => $name_prefix . '[settings][inline][enabled]',
			'label' => __( 'Fill in the Blank Type', 'ipt_fsqm' ),
			'ui' => 'toggle',
			'param' => [ $name_prefix . '[settings][inline][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['inline']['enabled'], '1', false, true, [
				'condid' => 'ipt_fsqm_builder_fs_' . $key . '_inline_prefix_wrap,ipt_fsqm_builder_fs_' . $key . '_inline_suffix_wrap,ipt_fsqm_builder_fs_' . $key . '_inline_width_wrap',
			] ],
			'help' => __( 'Enabled inline appearance or fill in the blank type appearance for this element.', 'ipt_fsqm' ),
		];
		// Prefix
		$inline_items[] = [
			'name' => $name_prefix . '[settings][inline][prefix]',
			'label' => __( 'Inline Prefix', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => [ $name_prefix . '[settings][inline][prefix]', $data['settings']['inline']['prefix'], __( 'HTML Enabled', 'ipt_fsqm' ) ],
			'help' => __( 'Put the HTML prefix before the element. You can also put interactive tags if the settings are enabled.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_builder_fs_' . $key . '_inline_prefix_wrap',
		];
		// Suffix
		$inline_items[] = [
			'name' => $name_prefix . '[settings][inline][suffix]',
			'label' => __( 'Inline Suffix', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => [ $name_prefix . '[settings][inline][suffix]', $data['settings']['inline']['suffix'], __( 'HTML Enabled', 'ipt_fsqm' ) ],
			'help' => __( 'Put the HTML suffix after the element. You can also put interactive tags if the settings are enabled.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_builder_fs_' . $key . '_inline_suffix_wrap',
		];
		// Width
		$inline_items[] = [
			'name' => $name_prefix . '[settings][inline][width]',
			'label' => __( 'Inline Width', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => [ $name_prefix . '[settings][inline][width]', $data['settings']['inline']['width'], __( 'px', 'ipt_fsqm' ) ],
			'help' => __( 'Mention the width, in pixels of the inline element.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_builder_fs_' . $key . '_inline_width_wrap',
		];
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
	?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_score"><?php _e( 'Scoring', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][placeholder]', __( 'Placeholder Text', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][placeholder]', $data['settings']['placeholder'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Text that is shown by default when the field is empty.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][native_placeholder]', __( 'Use Native Placeholder', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][native_placeholder]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['native_placeholder'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the placeholder will not animate and move above the element.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the icon you want to appear before the text. Select none to disable.', 'ipt_fsqm' ) ) ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][keypad]', __( 'Show Keyboard', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][keypad]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['keypad'], '1', false, true, array(
								'condid' => 'ipt_fsqm_builder_fs_' . $key . '_wrap',
							) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Whether or not to show a keyboard on this element.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr id="<?php echo 'ipt_fsqm_builder_fs_' . $key . '_wrap'; ?>">
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][ktype]', __( 'Keyboard Type', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->select( $name_prefix . '[settings][ktype]', $types, $data['settings']['ktype'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the keyboard type.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][email]', __( 'Send to Address', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][email]', $data['settings']['email'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The email address to which this submission will be sent. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<?php $this->_helper_build_prefil_text( $name_prefix, $data ); ?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][readonly]', __( 'Readonly', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][readonly]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['readonly'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the recorded value would not be editable by user. Make sure the validation matches, otherwise it might lead to error.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<?php $this->ui->form_table( $inline_items, false ); ?>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_score">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][score]', __( 'Score', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][score]', $data['settings']['score'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The score admin can assign for this question. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<?php $this->feedback_auto_score( $name_prefix, $key, $data ); ?>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	public function build_upload( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
		$upload_ifs_items = [];
		// Upload Button Label
		$upload_ifs_items[] = [
			'name' => $name_prefix . '[settings][upload_label]',
			'label' => __( 'Upload Button Label', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => [ $name_prefix . '[settings][upload_label]', $data['settings']['upload_label'], __( 'Required', 'ipt_fsqm' ) ],
			'help' => __( 'Enter the label of the upload button.', 'ipt_fsqm' ),
		];
		// Upload Specific Type
		$upload_accept_types = [
			'all' => __( 'All Files', 'ipt_fsqm' ),
			'image' => __( 'Image Files Only', 'ipt_fsqm' ),
			'video' => __( 'Video Files Only', 'ipt_fsqm' ),
			'audio' => __( 'Audio Files Only', 'ipt_fsqm' ),
		];
		$upload_ifs_items[] = [
			'name' => $name_prefix . '[settings][accept]',
			'label' => __( 'Upload Button will accept', 'ipt_fsqm' ),
			'ui' => 'select',
			'param' => [ $name_prefix . '[settings][accept]', $upload_accept_types, $data['settings']['accept'] ],
			'help' => __( 'You can force accepted files types through the upload button here. Use it in conjunction with Capture Mode to force camera upload through front/back facing camera on mobile or compatible devices. <strong>DO NOT FORGET TO SET THE VALIDATION</strong> for this to work properly. Also enabling Select One file at a time is useful.', 'ipt_fsqm' ),
		];
		$upload_capture_types = [
			'none' => __( 'None', 'ipt_fsqm' ),
			'user' => __( 'User/Front Facing Image/Video', 'ipt_fsqm' ),
			'environment' => __( 'Environment/Rear Facing Image/Video', 'ipt_fsqm' ),
			'audio' => __( 'Audio Capture', 'ipt_fsqm' ),
		];
		$upload_ifs_items[] = [
			'name' => $name_prefix . '[settings][capture]',
			'label' => __( 'Capture Mode', 'ipt_fsqm' ),
			'ui' => 'select',
			'param' => [ $name_prefix . '[settings][capture]', $upload_capture_types, $data['settings']['capture'] ],
			'help' => __( 'When supported you can force the capture mode. This is useful while uploading images, videos or audio through mobile or compatible devices.', 'ipt_fsqm' ),
		];

		$captcha_types = array(
			'image' => __( 'Image', 'ipt_fsqm' ),
			'audio' => __( 'Audio', 'ipt_fsqm' ),
		);
		$captcha_themes = array(
			'light' => __( 'Light Color Scheme', 'ipt_fsqm' ),
			'dark' => __( 'Dark Color Scheme', 'ipt_fsqm' ),
		);
		$captcha_hls = array(
			'ar'     => 'Arabic',
			'af'     => 'Afrikaans',
			'am'     => 'Amharic',
			'hy'     => 'Armenian',
			'az'     => 'Azerbaijani',
			'eu'     => 'Basque',
			'bn'     => 'Bengali',
			'bg'     => 'Bulgarian',
			'ca'     => 'Catalan',
			'zh-HK'  => 'Chinese (Hong Kong)',
			'zh-CN'  => 'Chinese (Simplified)',
			'zh-TW'  => 'Chinese (Traditional)',
			'hr'     => 'Croatian',
			'cs'     => 'Czech',
			'da'     => 'Danish',
			'nl'     => 'Dutch',
			'en-GB'  => 'English (UK)',
			'en'     => 'English (US)',
			'et'     => 'Estonian',
			'fil'    => 'Filipino',
			'fi'     => 'Finnish',
			'fr'     => 'French',
			'fr-CA'  => 'French (Canadian)',
			'gl'     => 'Galician',
			'ka'     => 'Georgian',
			'de'     => 'German',
			'de-AT'  => 'German (Austria)',
			'de-CH'  => 'German (Switzerland)',
			'el'     => 'Greek',
			'gu'     => 'Gujarati',
			'iw'     => 'Hebrew',
			'hi'     => 'Hindi',
			'hu'     => 'Hungarain',
			'is'     => 'Icelandic',
			'id'     => 'Indonesian',
			'it'     => 'Italian',
			'ja'     => 'Japanese',
			'kn'     => 'Kannada',
			'ko'     => 'Korean',
			'lo'     => 'Laothian',
			'lv'     => 'Latvian',
			'lt'     => 'Lithuanian',
			'ms'     => 'Malay',
			'ml'     => 'Malayalam',
			'mr'     => 'Marathi',
			'mn'     => 'Mongolian',
			'no'     => 'Norwegian',
			'fa'     => 'Persian',
			'pl'     => 'Polish',
			'pt'     => 'Portuguese',
			'pt-BR'  => 'Portuguese (Brazil)',
			'pt-PT'  => 'Portuguese (Portugal)',
			'ro'     => 'Romanian',
			'ru'     => 'Russian',
			'sr'     => 'Serbian',
			'si'     => 'Sinhalese',
			'sk'     => 'Slovak',
			'sl'     => 'Slovenian',
			'es'     => 'Spanish',
			'es-419' => 'Spanish (Latin America)',
			'sw'     => 'Swahili',
			'sv'     => 'Swedish',
			'ta'     => 'Tamil',
			'te'     => 'Telugu',
			'th'     => 'Thai',
			'tr'     => 'Turkish',
			'uk'     => 'Ukrainian',
			'ur'     => 'Urdu',
			'vi'     => 'Vietnamese',
			'zu'     => 'Zulu',
		);
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the icon you want to appear before the title. Select none to disable.', 'ipt_fsqm' ) ) ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<?php $this->ui->form_table( $upload_ifs_items, false ); ?>
					<tr>
						<th>
							<?php $this->ui->generate_label( $name_prefix . '[settings][wp_media_integration]', __( 'Integrate to WP Media', 'ipt_fsqm' ) ); ?>
						</th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][wp_media_integration]', __( 'Enabled', 'ipt_fsqm' ), __( 'Disabled', 'ipt_fsqm' ), $data['settings']['wp_media_integration'] ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'Enable to automatically add the uploads to WordPress Media List. You can then easily put them inside posts or use any media functions on them.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th>
							<?php $this->ui->generate_label( $name_prefix . '[settings][auto_upload]', __( 'Immediate Upload', 'ipt_fsqm' ) ); ?>
						</th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][auto_upload]', __( 'Enabled', 'ipt_fsqm' ), __( 'Disabled', 'ipt_fsqm' ), $data['settings']['auto_upload'] ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'Enable to start upload the files immediately after added. Otherwise user would need to click on the Start Upload button to actually upload the files.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th>
							<?php $this->ui->generate_label( $name_prefix . '[settings][minimal_ui]', __( 'Minimal UI', 'ipt_fsqm' ) ); ?>
						</th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][minimal_ui]', __( 'Enabled', 'ipt_fsqm' ), __( 'Disabled', 'ipt_fsqm' ), $data['settings']['minimal_ui'] ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'Enabling this will only show the upload button and just the list of uploaded files without checkboxes and bulk action buttons.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>

					<tr>
						<th>
							<?php $this->ui->generate_label( $name_prefix . '[settings][single_upload]', __( 'Select one file at a time', 'ipt_fsqm' ) ); ?>
						</th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][single_upload]', __( 'Enabled', 'ipt_fsqm' ), __( 'Disabled', 'ipt_fsqm' ), $data['settings']['single_upload'] ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'Enabling this will make the user to select only one file at a time when browsing. This is recommended only if you want to have access to Upload from camera feature on iOS devices.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th>
							<?php $this->ui->generate_label( $name_prefix . '[settings][drag_n_drop]', __( 'Drag and Drop Interface', 'ipt_fsqm' ) ); ?>
						</th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][drag_n_drop]', __( 'Enabled', 'ipt_fsqm' ), __( 'Disabled', 'ipt_fsqm' ), $data['settings']['drag_n_drop'] ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'If enabled, the upload container will have a nice drag and drop zone where users can simply put their files for upload.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th>
							<?php $this->ui->generate_label( $name_prefix . '[settings][dragdrop]', __( 'Drag and Drop Instruction', 'ipt_fsqm' ) ); ?>
						</th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][dragdrop]', $data['settings']['dragdrop'], __( 'Required', 'ipt_fsqm' ) ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'Enter the instruction text for the drag and drop zone. Should be compact and precise.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th>
							<?php $this->ui->generate_label( $name_prefix . '[settings][progress_bar]', __( 'Show Progress Bar', 'ipt_fsqm' ) ); ?>
						</th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][progress_bar]', __( 'Enabled', 'ipt_fsqm' ), __( 'Disabled', 'ipt_fsqm' ), $data['settings']['progress_bar'] ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'If enabled, users will be shown a progress bar to track upload progress.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th>
							<?php $this->ui->generate_label( $name_prefix . '[settings][preview_media]', __( 'Preview Media', 'ipt_fsqm' ) ); ?>
						</th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][preview_media]', __( 'Enabled', 'ipt_fsqm' ), __( 'Disabled', 'ipt_fsqm' ), $data['settings']['preview_media'] ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'If enabled, users will have options to preview uploaded media - images, audio and video files.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th>
							<?php $this->ui->generate_label( $name_prefix . '[settings][can_delete]', __( 'Delete Capability', 'ipt_fsqm' ) ); ?>
						</th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][can_delete]', __( 'Enabled', 'ipt_fsqm' ), __( 'Disabled', 'ipt_fsqm' ), $data['settings']['can_delete'] ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'If enabled, users can delete their uploaded files before making the final submission.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th>
							<?php $this->ui->generate_label( $name_prefix . '[settings][recaptcha]', __( 'Enable reCaptcha', 'ipt_fsqm' ) ); ?>
						</th>
						<td>
							<?php $this->ui->toggle(
								$name_prefix . '[settings][recaptcha]',
								__( 'Enabled', 'ipt_fsqm' ),
								__( 'Disabled', 'ipt_fsqm' ),
								$data['settings']['recaptcha'],
								'1',
								false,
								true,
								[
									'condid' => 'ipt_fsqm_builder_upload_recaptcha' . $key . '_config_wrap'
								]
							); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'If enabled, users can only upload files after solving a reCaptcha.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
				</tbody>
			</table>
			<table class="form-table" id="<?php echo esc_attr( 'ipt_fsqm_builder_upload_recaptcha' . $key . '_config_wrap' ); ?>">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][site_key]', __( 'Site Key', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][site_key]', $data['settings']['site_key'], __( 'Site Key', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the sitekey for your domain. You can get the sitekey from <a href="https://www.google.com/recaptcha/admin" rel="noopener" target="_blank">here</a>.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][secret_key]', __( 'Secret Key', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][secret_key]', $data['settings']['secret_key'], __( 'Secret Key', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the secret key for your domain. You can get the secret key from <a href="https://www.google.com/recaptcha/admin" rel="noopener" target="_blank">here</a>.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][type]', __( 'reCaptcha Type', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->select( $name_prefix . '[settings][type]', $captcha_types, $data['settings']['type'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Set the preferred type of captcha to use inside reCaptcha. Default is image.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hl]', __( 'reCaptcha Language', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->select( $name_prefix . '[settings][hl]', $captcha_hls, $data['settings']['hl'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the recaptcha language code.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][theme]', __( 'reCaptcha Theme', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->select( $name_prefix . '[settings][theme]', $captcha_themes, $data['settings']['theme'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Set light or dark version of reCaptcha.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<table class="form-table">
				<tbody>
					<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'], false ); ?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][accept_file_types]', __( 'Accepted File Types', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][accept_file_types]', $data['settings']['accept_file_types'], __( 'Required', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter a comma separated list of extensions of files that you would allow the user to upload and this is required. But for security purpose we are still going to disable uploading of .php files and other executable files.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th>
							<?php $this->ui->generate_label( $name_prefix . '[settings][max_number_of_files]', __( 'Maximum Number of Files', 'ipt_fsqm' ) ); ?>
						</th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][max_number_of_files]', $data['settings']['max_number_of_files'], __( 'No limit', 'ipt_fsqm' ), 1, 100, 1 ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'Enter maximum number of files. Leave blank for unlimited files. Please note that PHP file limit may still be restricting the overall size.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th>
							<?php $this->ui->generate_label( $name_prefix . '[settings][min_number_of_files]', __( 'Minimum Number of Files', 'ipt_fsqm' ) ); ?>
						</th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][min_number_of_files]', $data['settings']['min_number_of_files'], __( 'Validation Dependent', 'ipt_fsqm' ), 1, 100, 1 ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'Enter minimum number of files. Leave blank for fallback to validation.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th>
							<?php $this->ui->generate_label( $name_prefix . '[settings][max_file_size]', __( 'Max File Size (bytes)', 'ipt_fsqm' ) ); ?>
						</th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][max_file_size]', $data['settings']['max_file_size'], __( 'No limit', 'ipt_fsqm' ), 1, 100000000, 1000 ); ?>
							<p class="description"><?php printf( __( '<strong>PHP Upload Limit:</strong> <code>%s</code> bytes', 'ipt_fsqm' ), $this->get_maximum_file_upload_size() ); ?></p>
						</td>
						<td>
							<?php $this->ui->help( __( 'Enter maximum file size in bytes. Leave blank for unlimited file size. Please note that PHP file limit may still be restricting the actual size.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th>
							<?php $this->ui->generate_label( $name_prefix . '[settings][min_file_size]', __( 'Min File Size (bytes)', 'ipt_fsqm' ) ); ?>
						</th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][min_file_size]', $data['settings']['min_file_size'], __( 'No limit', 'ipt_fsqm' ), 1, 100000000, 1000 ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'Enter minimum file size in bytes. Minimum will always be 1.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
	}

	public function build_mathematical( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
		?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the icon you want to appear before the title. Select none to disable.', 'ipt_fsqm' ) ) ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden]', __( 'Not visible inside form', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If this option is enabled, then the element will not be visible inside the form. It will be visible in the summary table though (if you do not explicitly disable it) and also you can put conditional logic on it.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][editable]', __( 'Value Editable By User', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][editable]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['editable'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Whether or not the calculated value would be editable by user.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][right]', __( 'Align Right ( Row )', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][right]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['right'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the mathematical element will have a row like look aligned to the right. Works only with material themes.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][fancy]', __( 'Fancy Appearance', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][fancy]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['fancy'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the mathematical element will have a slick look attached to the right side of the form. Works only with material themes. Will override the Align Right appearance.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][noanim]', __( 'Disable countUp/Down Animation', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][noanim]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['noanim'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled then the numbers would not animate when a change occurs.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][formula]', __( 'Formula Input', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][formula]', $data['settings']['formula'], __( 'Valid Mathematical String', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter mathematical formula here. <code>(M1+M2)/(M3+F1)</code>. More advanced formula can be inserted. Please <a href="https://wp.me/p3Zesg-1gM" target="_blank">follow this guide</a>.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th>
							<?php $this->ui->generate_label( $name_prefix . '[settings][precision]', __( 'Decimal Precision', 'ipt_fsqm' ) ); ?>
						</th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][precision]', $data['settings']['precision'], __( 'Automatic', 'ipt_fsqm' ), 1, 10, 1 ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'Enter the number of digits that should be rounded off after decimal point. Leaving empty will automate the process and will take into consideration of the final number.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][grouping]', __( 'Use Grouping', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][grouping]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['grouping'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled then number will be grouped by thousands separator.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][separator]', __( 'Thousands Separator', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][separator]', $data['settings']['separator'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the thousands separator. Default <code>,</code>.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][decimal]', __( 'Decimal Separator', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][decimal]', $data['settings']['decimal'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the decimal separator. Default <code>.</code>.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][prefix]', __( 'Prefix', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[settings][prefix]', $data['settings']['prefix'], __( 'HTML allowed', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the prefix text here.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][suffix]', __( 'Suffix', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[settings][suffix]', $data['settings']['suffix'], __( 'HTML allowed', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the suffix text here.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
	}

	public function build_payment( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
		$countries = IPT_FSQM_Form_Elements_Static::get_countries();
		?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the icon you want to appear before the heading. Select none to disable.', 'ipt_fsqm' ) ) ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][country]', __( 'Preselected Country', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->select( $name_prefix . '[settings][country]', $countries, $data['settings']['country'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'User needs to enter their country for Credit Card Payments. Please select the country which should be pre selected.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][ptitle]', __( 'Payment Mode Selection Title', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][ptitle]', $data['settings']['ptitle'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the title that will be shown against the payment mode selection.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][ctitle]', __( 'Credit Card Form Title', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][ctitle]', $data['settings']['ctitle'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the title that will be shown against the cc form.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][ppmsg]', __( 'PayPal Express Checkout Message', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[settings][ppmsg]', $data['settings']['ppmsg'], __( 'HTML allowed', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the message that will be shown to users who chooses paypal express checkout.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][noanim]', __( 'Disable countUp/Down Animation', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][noanim]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['noanim'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled then the numbers would not animate when a change occurs.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][right]', __( 'Align Right ( Row )', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][right]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['right'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the mathematical element will have a row like look aligned to the right. Works only with material themes.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][fancy]', __( 'Fancy Appearance', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][fancy]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['fancy'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the mathematical element will have a slick look attached to the right side of the form. Works only with material themes. Will override the Align Right appearance.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th>
							<?php $this->ui->generate_label( $name_prefix . '[settings][precision]', __( 'Decimal Precision', 'ipt_fsqm' ) ); ?>
						</th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][precision]', $data['settings']['precision'], __( 'Automatic', 'ipt_fsqm' ), 1, 10, 1 ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'Enter the number of digits that should be rounded off after decimal point.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][grouping]', __( 'Use Grouping', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][grouping]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['grouping'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled then number will be grouped by thousands separator.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][separator]', __( 'Thousands Separator', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][separator]', $data['settings']['separator'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the thousands separator. Default <code>,</code>.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][decimal]', __( 'Decimal Separator', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][decimal]', $data['settings']['decimal'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the decimal separator. Default <code>.</code>.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][prefix]', __( 'Prefix', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[settings][prefix]', $data['settings']['prefix'], __( 'HTML allowed', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the prefix text here.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][suffix]', __( 'Suffix', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[settings][suffix]', $data['settings']['suffix'], __( 'HTML allowed', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the suffix text here.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	public function build_feedback_matrix( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$sda_columns = array(
			0 => array(
				'label' => __( 'Label', 'ipt_fsqm' ),
				'type' => 'text',
				'size' => '100',
			),
		);
		$labels = array(
			'confirm' => __( 'Confirm delete. This action can not be undone.', 'ipt_fsqm' ),
			'add' => __( 'Add New Item', 'ipt_fsqm' ),
			'del' => __( 'Click to delete', 'ipt_fsqm' ),
			'drag' => __( 'Drag this to rearrange', 'ipt_fsqm' ),
		);
		$sda_data_row = array(
			0 => array( $name_prefix . '[settings][rows][__SDAKEY__]', '', __( 'Enter label', 'ipt_fsqm' ), 'fit' ),
		);
		$sda_items_rows = array();
		$max_key_row = null;
		foreach ( (array)$data['settings']['rows'] as $o_key => $option ) {
			$max_key_row = max( array( $max_key_row, $o_key ) );
			$sda_items_rows[] = array(
				0 => array( $name_prefix . '[settings][rows][' . $o_key . ']', $option, __( 'Enter label', 'ipt_fsqm' ), 'fit' ),
			);
		}

		$sda_col_columns = array(
			0 => array(
				'label' => __( 'Label', 'ipt_fsqm' ),
				'type' => 'text',
				'size' => '100',
			),
		);
		$sda_data_column = array(
			0 => array( $name_prefix . '[settings][columns][__SDAKEY__]', '', __( 'Enter label', 'ipt_fsqm' ), 'fit' ),
		);
		$sda_items_columns = array();
		$max_key_column = null;
		foreach ( (array)$data['settings']['columns'] as $o_key => $option ) {
			$max_key_column = max( array( $max_key_column, $o_key ) );
			$sda_items_columns[] = array(
				0 => array( $name_prefix . '[settings][columns][' . $o_key . ']', $option, __( 'Enter label', 'ipt_fsqm' ), 'fit' ),
			);
		}
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_rows"><?php _e( 'Rows', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_columns"><?php _e( 'Cols', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][multiline]', __( 'Multiline Values', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][multiline]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['multiline'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If turned on, then the user will be given textareas instead of text inputs across the row.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the icon you want to appear inside the selected radio/checkbox.', 'ipt_fsqm' ) ) ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_rows">
			<table class="form-table">
				<tbody>
					<tr>
						<th colspan="2"><?php $this->ui->generate_label( '', __( 'List of Rows', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->help( __( 'Enter the Rows. These are basically the primary ratings.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<td colspan="3">
							<?php $this->ui->sda_list( array(
								'columns' => $sda_columns,
								'labels' => $labels,
							), $sda_items_rows, $sda_data_row, $max_key_row ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_columns">
			<table class="form-table">
				<tbody>
					<tr>
						<th colspan="2"><?php $this->ui->generate_label( '', __( 'List of Columns', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->help( __( 'Enter the Columns. These are basically the selection options.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<td colspan="3">
							<?php $this->ui->sda_list( array(
								'columns' => $sda_col_columns,
								'labels' => $labels,
							), $sda_items_columns, $sda_data_column, $max_key_column ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	public function build_gps( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
	?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the icon you want to appear before the title. Select none to disable.', 'ipt_fsqm' ) ) ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][radius]', __( 'Accuracy Radius', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][radius]', $data['settings']['radius'], __( 'None', 'ipt_fsqm' ) ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'Enter the accuracy circle radius.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][zoom]', __( 'Map Zoom', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][zoom]', $data['settings']['zoom'], __( 'None', 'ipt_fsqm' ) ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'Enter the zoom in value for map.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][scrollwheel]', __( 'Make Scroll using Mouse Wheel', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][scrollwheel]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['scrollwheel'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then user will be able to zoom in or out using scroll wheel.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][manualcontrol]', __( 'Manual Control', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][manualcontrol]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['manualcontrol'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then user will be able to manually set address.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][lat_label]', __( 'Latitude Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][lat_label]', $data['settings']['lat_label'], __( 'Enter label', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the label that will shown on an empty Latitude text.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][long_label]', __( 'Longitude Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][long_label]', $data['settings']['long_label'], __( 'Enter label', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the label that will shown on an empty Longitude text.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][location_name_label]', __( 'Location Name Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][location_name_label]', $data['settings']['location_name_label'], __( 'Enter label', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the label that will shown on an empty Location Name text. This field will be populated by google places API.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][update_label]', __( 'Update Button Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][update_label]', $data['settings']['update_label'], __( 'Enter label', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the label that will shown on the manual update button.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][nolocation_label]', __( 'No Location Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][nolocation_label]', $data['settings']['nolocation_label'], __( 'Enter label', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the label that will shown when no location is given.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>


	<?php
	$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	public function build_signature( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
		?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the icon you want to appear before the title. Select none to disable.', 'ipt_fsqm' ) ) ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][color]', __( 'Pen Color', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->colorpicker( $name_prefix . '[settings][color]', $data['settings']['color'] ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'Choose signature pen color. Default: <code>#212121</code>.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][reset]', __( 'Reset Button Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][reset]', $data['settings']['reset'], __( 'Enter label', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the label that will shown when to the reset button.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][undo]', __( 'Undo Button Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][undo]', $data['settings']['undo'], __( 'Enter label', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the label that will shown when to the undo button.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	public function build_f_name( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][placeholder]', __( 'Placeholder Text', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][placeholder]', $data['settings']['placeholder'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Text that is shown by default when the field is empty.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][native_placeholder]', __( 'Use Native Placeholder', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][native_placeholder]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['native_placeholder'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the placeholder will not animate and move above the element.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the icon you want to appear before the text. Select none to disable.', 'ipt_fsqm' ) ) ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<?php $this->_helper_build_prefil_text( $name_prefix, $data ); ?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][readonly]', __( 'Readonly', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][readonly]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['readonly'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the recorded value would not be editable by user. Make sure the validation matches, otherwise it might lead to error.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
	}

	public function build_l_name( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][placeholder]', __( 'Placeholder Text', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][placeholder]', $data['settings']['placeholder'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Text that is shown by default when the field is empty.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][native_placeholder]', __( 'Use Native Placeholder', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][native_placeholder]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['native_placeholder'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the placeholder will not animate and move above the element.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the icon you want to appear before the text. Select none to disable.', 'ipt_fsqm' ) ) ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<?php $this->_helper_build_prefil_text( $name_prefix, $data ); ?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][readonly]', __( 'Readonly', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][readonly]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['readonly'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the recorded value would not be editable by user. Make sure the validation matches, otherwise it might lead to error.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
	}

	public function build_email( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][placeholder]', __( 'Placeholder Text', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][placeholder]', $data['settings']['placeholder'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Text that is shown by default when the field is empty.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][native_placeholder]', __( 'Use Native Placeholder', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][native_placeholder]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['native_placeholder'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the placeholder will not animate and move above the element.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the icon you want to appear before the text. Select none to disable.', 'ipt_fsqm' ) ) ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<?php $this->_helper_build_prefil_text( $name_prefix, $data ); ?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][readonly]', __( 'Readonly', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][readonly]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['readonly'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the recorded value would not be editable by user. Make sure the validation matches, otherwise it might lead to error.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
	}

	public function build_phone( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][placeholder]', __( 'Placeholder Text', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][placeholder]', $data['settings']['placeholder'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Text that is shown by default when the field is empty.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][native_placeholder]', __( 'Use Native Placeholder', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][native_placeholder]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['native_placeholder'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the placeholder will not animate and move above the element.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the icon you want to appear before the text. Select none to disable.', 'ipt_fsqm' ) ) ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<?php $this->_helper_build_prefil_text( $name_prefix, $data ); ?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][readonly]', __( 'Readonly', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][readonly]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['readonly'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the recorded value would not be editable by user. Make sure the validation matches, otherwise it might lead to error.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
	}

	public function build_p_name( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][placeholder]', __( 'Placeholder Text', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][placeholder]', $data['settings']['placeholder'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Text that is shown by default when the field is empty.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][native_placeholder]', __( 'Use Native Placeholder', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][native_placeholder]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['native_placeholder'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the placeholder will not animate and move above the element.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the icon you want to appear before the text. Select none to disable.', 'ipt_fsqm' ) ) ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<?php $this->_helper_build_prefil_text( $name_prefix, $data ); ?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][readonly]', __( 'Readonly', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][readonly]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['readonly'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the recorded value would not be editable by user. Make sure the validation matches, otherwise it might lead to error.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
	}

	public function build_p_email( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][placeholder]', __( 'Placeholder Text', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][placeholder]', $data['settings']['placeholder'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Text that is shown by default when the field is empty.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][native_placeholder]', __( 'Use Native Placeholder', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][native_placeholder]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['native_placeholder'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the placeholder will not animate and move above the element.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the icon you want to appear before the text. Select none to disable.', 'ipt_fsqm' ) ) ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<?php $this->_helper_build_prefil_text( $name_prefix, $data ); ?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][readonly]', __( 'Readonly', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][readonly]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['readonly'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the recorded value would not be editable by user. Make sure the validation matches, otherwise it might lead to error.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
	}

	public function build_p_phone( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][placeholder]', __( 'Placeholder Text', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][placeholder]', $data['settings']['placeholder'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Text that is shown by default when the field is empty.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][native_placeholder]', __( 'Use Native Placeholder', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][native_placeholder]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['native_placeholder'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the placeholder will not animate and move above the element.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the icon you want to appear before the text. Select none to disable.', 'ipt_fsqm' ) ) ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<?php $this->_helper_build_prefil_text( $name_prefix, $data ); ?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][readonly]', __( 'Readonly', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][readonly]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['readonly'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the recorded value would not be editable by user. Make sure the validation matches, otherwise it might lead to error.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
	}

	public function build_textinput( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][placeholder]', __( 'Placeholder Text', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][placeholder]', $data['settings']['placeholder'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Text that is shown by default when the field is empty.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][native_placeholder]', __( 'Use Native Placeholder', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][native_placeholder]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['native_placeholder'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the placeholder will not animate and move above the element.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the icon you want to appear before the text. Select none to disable.', 'ipt_fsqm' ) ) ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<?php $this->_helper_build_prefil_text( $name_prefix, $data ); ?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][readonly]', __( 'Readonly', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][readonly]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['readonly'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the recorded value would not be editable by user. Make sure the validation matches, otherwise it might lead to error.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	public function build_textarea( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][placeholder]', __( 'Placeholder Text', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][placeholder]', $data['settings']['placeholder'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Text that is shown by default when the field is empty.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][native_placeholder]', __( 'Use Native Placeholder', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][native_placeholder]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['native_placeholder'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the placeholder will not animate and move above the element.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the icon you want to appear before the text. Select none to disable.', 'ipt_fsqm' ) ) ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<?php $this->_helper_build_prefil_text( $name_prefix, $data ); ?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][readonly]', __( 'Readonly', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][readonly]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['readonly'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the recorded value would not be editable by user. Make sure the validation matches, otherwise it might lead to error.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	public function build_guestblog( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
		?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the icon you want to appear before the text. Select none to disable.', 'ipt_fsqm' ) ) ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<tr>
						<td colspan="3"><p class="description"><?php _e( 'This element would simply act like a textinput supporting rich text editor if Guest Blogging is not enabled explicitly from the WP Core settings. If enabled, then an actual guest blog would be published respecting the settings.', 'ipt_fsqm' ); ?></p></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][title_label]', __( 'Article Title Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][title_label]', $data['settings']['title_label'], __( 'Write Here', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Text that is shown to the article title textinput.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][placeholder]', __( 'Placeholder Text', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][placeholder]', $data['settings']['placeholder'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Text that is shown by default when the field is empty.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][editor_type]', __( 'Editor Type', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->select( $name_prefix . '[settings][editor_type]', array(
								'rich' => __( 'Rich Text Editor', 'ipt_fsqm' ),
								'html' => __( 'RAW HTML Editor', 'ipt_fsqm' ),
							), $data['settings']['editor_type'] ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'Set the editor type you would like to use for the guest blogging. Rich text would render a light weight WYSIWYG editor, whereas HTML would provide a large textarea.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	public function build_password( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][placeholder]', __( 'Placeholder Text', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][placeholder]', $data['settings']['placeholder'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Text that is shown by default when the field is empty.', 'ipt_fsqm' ) ); ?></td>
					</tr>

					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][confirm_duplicate]', __( 'Enter Password Twice', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][confirm_duplicate]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['confirm_duplicate'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Turn this feature on to make the user enter the password twice for validation.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hide_in_summary]', __( 'Do not reveal password', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hide_in_summary]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hide_in_summary'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Turn this feature on to never reveal password, neither in the form, nor in the summary table or email.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
	}

	public function build_p_radio( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$this->build_mcq_option_questions( $element, $key, $data, $element_structure, $name_prefix, false, false );
	}

	public function build_p_checkbox( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$this->build_mcq_option_questions( $element, $key, $data, $element_structure, $name_prefix, false, false );
	}

	public function build_p_select( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$this->build_mcq_option_questions( $element, $key, $data, $element_structure, $name_prefix, true, false );
	}

	public function build_s_checkbox( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_states"><?php _e( 'States', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the icon you want to appear inside the selected radio/checkbox.', 'ipt_fsqm' ) ) ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_states">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][checked]', __( 'Checked by Default', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][checked]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['checked'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Turn this feature on to make the checkbox checked by default.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>

		<?php
	}

	public function build_address( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$placeholders = array(
			'recipient' => __( 'Recipient', 'ipt_fsqm' ),
			'line_one' => __( 'Address line one', 'ipt_fsqm' ),
			'line_two' => __( 'Address line two', 'ipt_fsqm' ),
			'line_three' => __( 'Address line three', 'ipt_fsqm' ),
			'country' => __( 'Country', 'ipt_fsqm' ),
			'province' => __( 'Province', 'ipt_fsqm' ),
			'zip' => __( 'Postal Code', 'ipt_fsqm' ),
		);

		$country_list = IPT_FSQM_Form_Elements_Static::get_country_list();
		$country_list_items = array(
			0 => array(
				'value' => '',
				'label' => __( 'Disabled', 'ipt_fsqm' ),
			),
		);
		foreach ( $country_list as $ctkey => $ctval ) {
			$country_list_items[] = array(
				'value' => $ctkey,
				'label' => $ctval
			);
		}
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_items"><?php _e( 'Items', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hide_icon]', __( 'Hide Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hide_icon]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hide_icon'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the icons will be hidden.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][native_placeholder]', __( 'Use Native Placeholder', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][native_placeholder]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['native_placeholder'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the placeholder will not animate and move above the element.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_items">
			<table class="form-table">
				<tbody>
					<tr>
						<td colspan="2"><label for=""><?php _e( 'Placeholders and Fields', 'ipt_fsqm' ); ?></label></td>
						<td><?php $this->ui->help( __( 'eForm supports several fields inside the address field. Each field should have a placeholder, if you leave the placeholder blank, then the field will not be shown.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<?php foreach ( $placeholders as $p_key => $ph ) : ?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][' . $p_key . ']', $ph ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][' . $p_key . ']', $data['settings'][$p_key], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Text that is shown by default when the field is empty. If left empty, then the field would not be shown.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<?php endforeach; ?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][preset_country]', __( 'Preset Country', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->select( $name_prefix . '[settings][preset_country]', $country_list_items, $data['settings']['preset_country'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the preset country. If you do not want to show the country dropdown at all, then simply remove the placeholder of country field.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	public function build_keypad( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$types = array(
			array(
				'label' => 'Standard Qwerty Keyboard',
				'value' => 'qwerty',
			),
			array(
				'label' => 'International Qwerty Keyboard',
				'value' => 'qwerty',
			),
			array(
				'label' => 'Numerical Keyboard (ten-key)',
				'value' => 'num',
			),
			array(
				'label' => 'Alphabetical Keyboard',
				'value' => 'alpha',
			),
			array(
				'label' => 'Dvorak Simplified Keyboard',
				'value' => 'dvorak',
			),
		);
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][placeholder]', __( 'Placeholder Text', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][placeholder]', $data['settings']['placeholder'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Text that is shown by default when the field is empty.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][mask]', __( 'Mask Input', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][mask]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['mask'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Turn this feature on to take masked inputs (just like passwords).', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][multiline]', __( 'Accept Multiline', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][multiline]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['multiline'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Turn this feature on to take multiline inputs.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][type]', __( 'Keyboard Type', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->select( $name_prefix . '[settings][type]', $types, $data['settings']['type'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the keyboard type.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	public function build_datetime( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$types = array(
			array(
				'label' => __( 'Date Only', 'ipt_fsqm' ),
				'value' => 'date',
				'data' => array(
					'condid' => 'ipt_fsqm_form_builder_datetime_' . $key . '_date_wrap',
				),
			),
			array(
				'label' => __( 'Time Only', 'ipt_fsqm' ),
				'value' => 'time',
				'data' => array(
					'condid' => 'ipt_fsqm_form_builder_datetime_' . $key . '_time_wrap',
				),
			),
			array(
				'label' => __( 'Date & Time', 'ipt_fsqm' ),
				'value' => 'datetime',
				'data' => array(
					'condid' => 'ipt_fsqm_form_builder_datetime_' . $key . '_time_wrap,ipt_fsqm_form_builder_datetime_' . $key . '_date_wrap',
				),
			),
		);
		$date_formats = array(
			'yy-mm-dd' => date_i18n( 'Y-m-d', current_time( 'timestamp' ) ),
			'mm/dd/yy' => date_i18n( 'm/d/Y', current_time( 'timestamp' ) ),
			'dd.mm.yy' => date_i18n( 'd.m.Y', current_time( 'timestamp' ) ),
			'dd-mm-yy' => date_i18n( 'd-m-Y', current_time( 'timestamp' ) ),
		);
		$time_formats = array(
			'HH:mm:ss' => date_i18n( 'H:i:s', current_time( 'timestamp' ) ),
			'hh:mm:ss TT' => date_i18n( 'h:i:s A', current_time( 'timestamp' ) ),
		);
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
		?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][placeholder]', __( 'Placeholder Text', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][placeholder]', $data['settings']['placeholder'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Text that is shown by default when the field is empty.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hide_icon]', __( 'Hide Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hide_icon]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hide_icon'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enable this if you do not wish to show the icon beside the element.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][show_current]', __( 'Show Current Time', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][show_current]', __( 'Show', 'ipt_fsqm' ), __( 'Don\'t Show', 'ipt_fsqm' ), $data['settings']['show_current'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The current time will be calculated on the browser.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][type]', __( 'Picker Type', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->select( $name_prefix . '[settings][type]', $types, $data['settings']['type'], false, true ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the date and/or time picker type.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr id="ipt_fsqm_form_builder_datetime_<?php echo $key; ?>_date_wrap">
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][date_format]', __( 'Picker Date Format', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->select( $name_prefix . '[settings][date_format]', $date_formats, $data['settings']['date_format'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the date and/or time picker date format. It will be translated automatically and will change the older date times if you happen to change the format in future.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr id="ipt_fsqm_form_builder_datetime_<?php echo $key; ?>_time_wrap">
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][time_format]', __( 'Picker Time Format', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->select( $name_prefix . '[settings][time_format]', $time_formats, $data['settings']['time_format'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the date and/or time picker time format. It will be translated automatically and will change the older date times if you happen to change the format in future.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][year_range]', __( 'Default Year Range', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][year_range]', $data['settings']['year_range'], __( '50', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the default range that appears initially in the year dropdown of the calendar.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	public function build_p_sorting( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$this->build_user_sortable( $element, $key, $data, $element_structure, $name_prefix );
	}

	public function build_hidden( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		$types = array(
			0 => array(
				'value' => 'url',
				'label' => __( 'URL Parameter Based', 'ipt_fsqm' ),
			),
			1 => array(
				'value' => 'meta',
				'label' => __( 'User Meta Based', 'ipt_fsqm' ),
			),
			2 => array(
				'value' => 'logged_in',
				'label' => __( 'If logged in', 'ipt_fsqm' ),
			),
			3 => array(
				'value' => 'postmeta',
				'label' => __( 'Post Meta Based', 'ipt_fsqm' ),
			),
			4 => array(
				'value' => 'prefedined',
				'label' => __( 'Predefined (Static)', 'ipt_fsqm' ),
			),

		);
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
		?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][type]', __( 'Value Type', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->select( $name_prefix . '[settings][type]', $types, $data['settings']['type'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Set the type of value the hidden field will get. It can be based on URL parameter or user meta key. You can even make it constant by setting it to predefined and defining a default value. In case of <strong>If logged in</strong> the default value would be set if the user is logged in. Otherwise no value (effectively empty value) would be set. You can use this behavior to apply conditional logic on other elements too. For post meta based values, the post where this form is published through shortcode, would be considered. If you enter parameter like <code>10:key_value</code> then post meta <code>key_value</code> of post <code>10</code> would be considered, regardless of where the form is published.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][default]', __( 'Default Value', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][default]', $data['settings']['default'], __( 'None', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the default value of this element. This would be set if URL or meta parameter does not override. Empty value can also override the default value. But the value has to be set, i.e, either URL parameter or user metakey needs to be present.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][parameter]', __( 'Key Parameter', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][parameter]', $data['settings']['parameter'], __( 'Required', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the key parameter. In case of URL type value, <code>$_REQUEST[ $key ]</code> would be used. In case of User meta type value, the mentioned metakey would be used to retrieve the metavalue. It can not be empty or no value would be generated.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][admin_only]', __( 'Only Admin Can View', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][admin_only]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['admin_only'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then the recorded value would be visible by admins only.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>

		<?php
	}

	public function build_repeatable( $element, $key, $data, $element_structure, $name_prefix, $submission_data = null, $submission_structure = null, $context = null ) {
		// Element Type
		$element_types = array();
		$element_types[] = array(
			'value' => 'radio',
			'label' => __( 'Radio', 'ipt_fsqm' ),
		);
		$element_types[] = array(
			'value' => 'checkbox',
			'label' => __( 'Checkbox', 'ipt_fsqm' ),
		);
		$element_types[] = array(
			'value' => 'select',
			'label' => __( 'Dropdown', 'ipt_fsqm' ),
		);
		$element_types[] = array(
			'value' => 'select_multiple',
			'label' => __( 'Multiple Dropdown', 'ipt_fsqm' ),
		);
		$element_types[] = array(
			'value' => 'text',
			'label' => __( 'Text Input', 'ipt_fsqm' ),
		);
		$element_types[] = array(
			'value' => 'password',
			'label' => __( 'Password', 'ipt_fsqm' ),
		);
		$element_types[] = array(
			'value' => 'textarea',
			'label' => __( 'Textarea', 'ipt_fsqm' ),
		);
		$element_types[] = array(
			'value' => 'phone',
			'label' => __( 'Phone Number', 'ipt_fsqm' ),
		);
		$element_types[] = array(
			'value' => 'url',
			'label' => __( 'Anchor Links (URL)', 'ipt_fsqm' ),
		);
		$element_types[] = array(
			'value' => 'email',
			'label' => __( 'Email Address', 'ipt_fsqm' ),
		);
		$element_types[] = array(
			'value' => 'number',
			'label' => __( 'Only Numbers (Float or Integers)', 'ipt_fsqm' ),
		);
		$element_types[] = array(
			'value' => 'integer',
			'label' => __( 'Only Integers', 'ipt_fsqm' ),
		);
		$element_types[] = array(
			'value' => 'personName',
			'label' => __( 'Person\'s Name - eg, Mr. John Doe', 'ipt_fsqm' ),
		);
		$element_types[] = array(
			'value' => 'date',
			'label' => __( 'Date Picker', 'ipt_fsqm' ),
		);
		$element_types[] = array(
			'value' => 'datetime',
			'label' => __( 'DateTime Picker', 'ipt_fsqm' ),
		);
		$element_types[] = array(
			'value' => 'time',
			'label' => __( 'Time Picker', 'ipt_fsqm' ),
		);

		$column_sizes = array();
		$column_sizes[] = array(
			'value' => 'full',
			'label' => __( 'Full', 'ipt_fsqm' ),
		);
		$column_sizes[] = array(
			'value' => 'half',
			'label' => __( 'Half', 'ipt_fsqm' ),
		);
		$column_sizes[] = array(
			'value' => 'third',
			'label' => __( 'One Third', 'ipt_fsqm' ),
		);
		$column_sizes[] = array(
			'value' => 'two_third',
			'label' => __( 'Two Third', 'ipt_fsqm' ),
		);
		$column_sizes[] = array(
			'value' => 'forth',
			'label' => __( 'One Fourth', 'ipt_fsqm' ),
		);
		$column_sizes[] = array(
			'value' => 'three_forth',
			'label' => __( 'Three Fourth', 'ipt_fsqm' ),
		);

		// SDA Config
		$sda_columns = array(
			0 => array(
				'label' => __( 'Title', 'ipt_fsqm' ),
				'type' => 'text',
				'size' => '50'
			),
			1 => array(
				'label' => __( 'Type', 'ipt_fsqm' ),
				'type' => 'select',
				'size' => '20'
			),
			2 => array(
				'label' => __( 'Size', 'ipt_fsqm' ),
				'type' => 'select',
				'size' => '20'
			),
			3 => array(
				'label' => __( 'Req', 'ipt_fsqm' ),
				'type' => 'checkbox',
				'size' => '10',
			),
			4 => array(
				'label' => __( 'Options', 'ipt_fsqm' ),
				'type' => 'textarea',
				'size' => '50'
			),
			5 => array(
				'label' => __( 'Filters', 'ipt_fsqm' ),
				'type' => 'textarea',
				'size' => '40'
			),
			6 => array(
				'label' => __( 'Clear', 'ipt_fsqm' ),
				'type' => 'checkbox',
				'size' => '10',
			),
		);

		$labels = array(
			'confirm' => __( 'Confirm delete. This action can not be undone.', 'ipt_fsqm' ),
			'add' => __( 'Add New Element', 'ipt_fsqm' ),
			'del' => __( 'Click to delete', 'ipt_fsqm' ),
			'drag' => __( 'Drag this to rearrange', 'ipt_fsqm' ),
		);
		$sda_data = array(
			0 => array( $name_prefix . '[settings][group][__SDAKEY__][title]', __( 'Title' ), __( 'Required', 'ipt_fsqm' ) ),
			1 => array( $name_prefix . '[settings][group][__SDAKEY__][type]', $element_types, 'choice', false, false, false, true, array( 'fit' ) ),
			2 => array( $name_prefix . '[settings][group][__SDAKEY__][column]', $column_sizes, 'half', false, false, false, true, array( 'fit' ) ),
			3 => array( $name_prefix . '[settings][group][__SDAKEY__][required]', array( 'value' => '1', 'label' => '' ), false ),
			4 => array( $name_prefix . '[settings][group][__SDAKEY__][options]', '', __( 'Choices/Placeholder', 'ipt_fsqm' ) ),
			5 => array( $name_prefix . '[settings][group][__SDAKEY__][attr]', '', __( 'Filters', 'ipt_fsqm' ) ),
			6 => array( $name_prefix . '[settings][group][__SDAKEY__][clear]', array( 'value' => '1', 'label' => '' ), false ),
		);

		$sda_items = array();
		$max_key = null;
		foreach ( $data['settings']['group'] as $o_key => $option ) {
			$max_key = max( array( $max_key, $o_key ) );
			$sda_items[] = array(
				0 => array( $name_prefix . '[settings][group][' . $o_key . '][title]', $option['title'], __( 'Required', 'ipt_fsqm' ) ),
				1 => array( $name_prefix . '[settings][group][' . $o_key . '][type]', $element_types, $option['type'], false, false, false, true, array( 'fit' ) ),
				2 => array( $name_prefix . '[settings][group][' . $o_key . '][column]', $column_sizes, $option['column'], false, false, false, true, array( 'fit' ) ),
				3 => array( $name_prefix . '[settings][group][' . $o_key . '][required]', array( 'value' => '1', 'label' => '' ), isset( $option['required'] ) ? true : false ),
				4 => array( $name_prefix . '[settings][group][' . $o_key . '][options]', $option['options'], __( 'Choices/Placeholder', 'ipt_fsqm' ) ),
				5 => array( $name_prefix . '[settings][group][' . $o_key . '][attr]', $option['attr'], __( 'Filters', 'ipt_fsqm' ) ),
				6 => array( $name_prefix . '[settings][group][' . $o_key . '][clear]', array( 'value' => '1', 'label' => '' ), isset( $option['clear'] ) ? true : false ),
			);
		}
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
		?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_options"><?php _e( 'Options', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the heading icon.', 'ipt_fsqm' ) ) ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][show_icons]', __( 'Icons on Elements', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][show_icons]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['show_icons'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then input type elements will have relevant icons beside them.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hide_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hide_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hide_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][button]', __( 'Button Text', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][button]', $data['settings']['button'], __( 'None', 'ipt_fsqm' ) ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'Enter the Text of the add button.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][sortable]', __( 'Sortable', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][sortable]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['sortable'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If user can sort the group of elements.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][deletable]', __( 'Deletable/Addable', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][deletable]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['deletable'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If user can delete the group of elements.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_options">
			<table class="form-table">
				<tbody>
					<tr>
						<td colspan="2">
							<?php $this->ui->generate_label( '', __( 'Item Group List', 'ipt_fsqm' ) ); ?>
						</td>
						<td>
							<?php $this->ui->help_head(); ?>
							<p><?php _e( 'Create the repeatable groups here.', 'ipt_fsqm' ); ?></p>
							<h3><?php _e( 'Element Type', 'ipt_fsqm' ); ?></h3>
							<p><?php _e( 'Select the type of the element you would like to show here.' ) ?></p>
							<h3><?php _e( 'Element Options', 'ipt_fsqm' ); ?></h3>
							<p><?php _e( 'For radio, checkbox and dropdowns, this would be used to populate options. You need to write one option per line. You can even have an empty placeholder option on the first line and assign numeric values. Please see the example below.', 'ipt_fsqm' ); ?></p>
<pre>Please select[empty]
Option 1[num=10]
Option 2[num=-10]</pre>
							<p><?php _e( 'For text type elements, this would act as the placeholder.', 'ipt_fsqm' ); ?></p>
							<h3><?php _e( 'Element Filters', 'ipt_fsqm' ); ?></h3>
							<p><?php _e( 'Custom validation attributes. There can be a total of 8 attributes.', 'ipt_fsqm' ); ?></p>
<pre>min="10" max="20" minSize="1" maxSize="4" minCheckbox="1" maxCheckbox="2" future="NOW" past="NOW"</pre>
							<ul class="ul-disc">
								<li><?php _e( 'min: Determines minimum numeric value. Works for numbers or integers.', 'ipt_fsqm' ); ?></li>
								<li><?php _e( 'max: Determines maximum numeric value. Works for numbers or integers.', 'ipt_fsqm' ); ?></li>
								<li><?php _e( 'minSize: Determines minimum length of value. Works for text inputs.', 'ipt_fsqm' ); ?></li>
								<li><?php _e( 'maxSize: Determines maximum length of value. Works for text inputs.', 'ipt_fsqm' ); ?></li>
								<li><?php _e( 'minCheckbox: Determines minimum checkbox items to be selected.', 'ipt_fsqm' ); ?></li>
								<li><?php _e( 'maxCheckbox: Determines maximum checkbox items to be selected.', 'ipt_fsqm' ); ?></li>
								<li><?php _e( 'futute: Determines what the date should be future of. Could be yy-mm-dd formatted date string or NOW for current date. Works only with datepicker.', 'ipt_fsqm' ); ?></li>
								<li><?php _e( 'past: Determines what the date should be past of. Could be yy-mm-dd formatted date string or NOW for current date. Works only with datepicker.', 'ipt_fsqm' ); ?></li>
							</ul>
							<h3><?php _e( 'Required', 'ipt_fsqm' ); ?></h3>
							<p><?php _e( 'Enable to make this element compulsory.', 'ipt_fsqm' ); ?></p>
							<?php $this->ui->help_tail(); ?>
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<?php $this->ui->sda_list( array(
								'columns' => $sda_columns,
								'labels' => $labels,
							), $sda_items, $sda_data, $max_key ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][num]', __( 'Initial Number of Elements', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][num]', $data['settings']['num'], __( 'None', 'ipt_fsqm' ) ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'Enter the initial number of element groups that will be shown for empty form.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][min]', __( 'Minimum Number of Elements', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][min]', $data['settings']['min'], __( 'None', 'ipt_fsqm' ) ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'Enter the minimum number of element groups.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][max]', __( 'Maximum Number of Elements', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->spinner( $name_prefix . '[settings][max]', $data['settings']['max'], __( 'None', 'ipt_fsqm' ) ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'Enter the maximum number of element groups.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
	}

	/*==========================================================================
	 * SOME INTERNAL FUNCTIONS
	 *========================================================================*/
	protected function build_col($name_prefix, $data) {
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
		?>
<div class="ipt_uif_tabs">
	<ul>
		<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
		<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
	</ul>
	<div id="<?php echo $tab_names; ?>_elm">
		<table class="form-table">
			<tbody>
				<tr>
					<td colspan="3">
						<p class="description"><?php _e( 'Please expand the column by clicking the <span class="ipt-icomoon-arrow-down"></span> Expand Icon and drop more elements inside.', 'ipt_fsqm' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
					<td>
						<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
					</td>
					<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div id="<?php echo $tab_names; ?>_logic">
		<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
	</div>
</div>

		<?php
	}

	protected function build_user_sortable( $element, $key, $data, $element_structure, $name_prefix, $score = false ) {
		$sda_columns = array(
			0 => array(
				'label' => __( 'Label', 'ipt_fsqm' ),
				'type' => 'text',
				'size' => '100'
			),
		);
		if ( $score ) {
			$sda_columns[0]['size'] = '70';
			$sda_columns[1] = array(
				'label' => __( 'Score', 'ipt_fsqm' ),
				'type' => 'spinner',
				'size' => '30',
			);
		}

		$labels = array(
			'confirm' => __( 'Confirm delete. This action can not be undone.', 'ipt_fsqm' ),
			'add' => __( 'Add New Item', 'ipt_fsqm' ),
			'del' => __( 'Click to delete', 'ipt_fsqm' ),
			'drag' => __( 'Drag this to rearrange', 'ipt_fsqm' ),
		);
		$sda_data = array(
			0 => array( $name_prefix . '[settings][options][__SDAKEY__][label]', '', __( 'Option Label', 'ipt_fsqm' ), 'fit' ),
		);
		if ( $score ) {
			$sda_data[1] = array( $name_prefix . '[settings][options][__SDAKEY__][score]', '', __( 'Score (Optional)', 'ipt_fsqm' ), 'fit' );
		}

		$sda_items = array();
		$max_key = null;
		foreach ( $data['settings']['options'] as $o_key => $option ) {
			$max_key = max( array( $max_key, $o_key ) );
			$new_data = array(
				0 => array( $name_prefix . '[settings][options][' . $o_key . '][label]', $option['label'], __( 'Enter Option Label', 'ipt_fsqm' ), 'fit' ),
			);
			if ( $score ) {
				$new_data[1] = array( $name_prefix . '[settings][options][' . $o_key . '][score]', $option['score'], __( 'Score (Optional)', 'ipt_fsqm' ), 'fit' );
			}
			$sda_items[] = $new_data;
		}
		$types = array(
			array(
				'label' => 'Individual Positioning',
				'value' => 'individual',
			),
			array(
				'label' => 'Combined Positioning',
				'value' => 'combined',
			),
		);
		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_items"><?php _e( 'Items', 'ipt_fsqm' ); ?></a></li>
			<?php if ( $score ) : ?>
				<li><a href="#<?php echo $tab_names; ?>_score"><?php _e( 'Scoring', 'ipt_fsqm' ); ?></a></li>
			<?php endif; ?>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_items">
			<table class="form-table">
				<tbody>
					<tr>
						<td colspan="3">
							<p class="description">
								<?php if ( $score ) : ?>
									<?php _e( 'The correct sorting order is the order you give. The output will be randomized and the surveyee will need to put it into the correct order to get the maximum score.', 'ipt_fsqm' ); ?>
								<?php else : ?>
									<?php _e( 'The output of the sortable list will be the order you give. The surveyee can order the items the way he or she wishes.', 'ipt_fsqm' ); ?>
								<?php endif; ?>
							</p>
						</td>
					</tr>
					<?php if ( isset( $data['settings']['no_shuffle'] ) ) : ?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][no_shuffle]', __( 'Shuffling', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][no_shuffle]', __( 'No shuffle', 'ipt_fsqm' ), __( 'Shuffle', 'ipt_fsqm' ), $data['settings']['no_shuffle'] ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'By default the output of the list will be shuffled. If you wish to prevent it, then customize the toggle button.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<?php endif; ?>
					<tr>
						<th colspan="2"><?php $this->ui->generate_label( '', __( 'Item List', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->help( __( 'Enter the options. ', 'ipt_fsqm' ) . ( $score ? __( 'You can also have score associated to the options. The value of the score should be numeric positive or negative number.', 'ipt_fsqm' ) : '' ) ); ?></td>
					</tr>
					<tr>
						<td colspan="3">
							<?php $this->ui->sda_list( array(
								'columns' => $sda_columns,
								'labels' => $labels,
							), $sda_items, $sda_data, $max_key ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php if ( $score ) : ?>
			<div id="<?php echo $tab_names; ?>_score">
				<table class="form-table">
					<tbody>
						<tr>
							<th><?php $this->ui->generate_label( $name_prefix . '[settings][base_score]', __( 'Base Score', 'ipt_fsqm' ) ); ?></th>
							<td>
								<?php $this->ui->spinner( $name_prefix . '[settings][base_score]', $data['settings']['base_score'], __( 'None', 'ipt_fsqm' ) ); ?>
							</td>
							<td>
								<?php $this->ui->help( __( 'Enter the base score for a perfect sort. Consult to the help of Score Calculation Type to get more information.', 'ipt_fsqm' ) ); ?>
							</td>
						</tr>
						<tr>
							<th><?php $this->ui->generate_label( $name_prefix . '[settings][score_type]', __( 'Score Calculation Type', 'ipt_fsqm' ) ); ?></th>
							<td>
								<?php $this->ui->select( $name_prefix . '[settings][score_type]', $types, $data['settings']['score_type'] ); ?>
							</td>
							<td>
								<?php $this->ui->help_head(); ?>
								<?php _e( 'First all the items will be scrambled randomly. Then the user will need to sort them in the provided order to get score. Scoring can be of two types.', 'ipt_fsqm' ); ?>
								<ul class="ul-disc">
									<li>
										<strong><?php _e( 'Individual Positioning:', 'ipt_fsqm' ) ?></strong> <?php _e( 'Individual scores will be added to all items positioned at the right place. If all are in right places, then the Base Score will also be added.', 'ipt_fsqm' ); ?>
									</li>
									<li>
										<strong><?php _e( 'Combined Positioning:', 'ipt_fsqm' ) ?></strong> <?php _e( 'If all are in right places, then the Base Score will be added. Otherwise no score will be given.', 'ipt_fsqm' ); ?>
									</li>
								</ul>
								<?php $this->ui->help_tail(); ?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>
		<?php
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	/**
	 *
	 *
	 * @param type    $element
	 * @param type    $key
	 * @param type    $data
	 * @param type    $element_structure
	 * @param type    $name_prefix
	 * @param type    $for_select
	 * @param type    $score
	 */
	protected function build_mcq_option_questions( $element, $key, $data, $element_structure, $name_prefix, $for_select = false, $score = true ) {
		$sda_columns = array(
			0 => array(
				'label' => __( 'Option', 'ipt_fsqm' ),
				'type' => 'text',
				'size' => '70',
			),
		);
		if ( $score ) {
			$sda_columns[0]['size'] = '55';
			$sda_columns[1] = array(
				'label' => __( 'Score', 'ipt_fsqm' ),
				'type' => 'spinner',
				'size' => '15',
			);
		}
		$sda_columns[] = array(
			'label' => __( 'Numeric', 'ipt_fsqm' ),
			'type' => 'spinner',
			'size' => '15',
		);
		$sda_columns[] = array(
			'label' => __( 'Default', 'ipt_fsqm' ),
			'type' => 'toggle',
			'size' => '15',
		);

		$labels = array(
			'confirm' => __( 'Confirm delete. This action can not be undone.', 'ipt_fsqm' ),
			'add' => __( 'Add New Option', 'ipt_fsqm' ),
			'del' => __( 'Click to delete', 'ipt_fsqm' ),
			'drag' => __( 'Drag this to rearrange', 'ipt_fsqm' ),
		);
		$sda_data = array(
			0 => array( $name_prefix . '[settings][options][__SDAKEY__][label]', '', __( 'Enter Option Label', 'ipt_fsqm' ), 'fit' ),
		);
		if ( $score ) {
			$sda_data[1] = array( $name_prefix . '[settings][options][__SDAKEY__][score]', '', __( 'Score (Optional)', 'ipt_fsqm' ), 'fit' );
		}
		$sda_data[] = array( $name_prefix . '[settings][options][__SDAKEY__][num]', '', __( 'Numeric Value', 'ipt_fsqm' ), 'fit' );
		$sda_data[] = array( $name_prefix . '[settings][options][__SDAKEY__][default]', '', '', false );

		$sda_items = array();
		$max_key = null;
		foreach ( $data['settings']['options'] as $o_key => $option ) {
			$max_key = max( array( $max_key, $o_key ) );
			$new_data = array(
				0 => array( $name_prefix . '[settings][options][' . $o_key . '][label]', $option['label'], __( 'Enter Option Label', 'ipt_fsqm' ), 'fit' ),
			);
			if ( $score ) {
				$new_data[1] = array( $name_prefix . '[settings][options][' . $o_key . '][score]', $option['score'], __( 'Score (Optional)', 'ipt_fsqm' ), 'fit' );
			}

			if ( ! isset( $option['num'] ) ) {
				$option['num'] = '';
			}
			$new_data[] = array( $name_prefix . '[settings][options][' . $o_key . '][num]', $option['num'], __( 'Numeric Value', 'ipt_fsqm' ), 'fit' );
			$new_data[] = array( $name_prefix . '[settings][options][' . $o_key . '][default]', '', '', ( isset( $option['default'] ) && true == $option['default'] ? true : false ) );

			$sda_items[] = $new_data;
		}

		$prefill_types = array(
			0 => array(
				'value' => 'none',
				'label' => __( 'None', 'ipt_fsqm' ),
			),
			1 => array(
				'value' => 'url',
				'label' => __( 'URL Parameter Based', 'ipt_fsqm' ),
			),
			2 => array(
				'value' => 'meta',
				'label' => __( 'User Meta Based', 'ipt_fsqm' ),
			),
		);

		$tab_names = $this->ui->generate_id_from_name( $name_prefix ) . '_settings_tab_';
?>
	<div class="ipt_uif_tabs">
		<ul>
			<li><a href="#<?php echo $tab_names; ?>_elm"><?php _e( 'Appearance', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_ifs"><?php _e( 'Interface', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_options"><?php _e( 'Options', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_validation"><?php _e( 'Validation', 'ipt_fsqm' ); ?></a></li>
			<li><a href="#<?php echo $tab_names; ?>_logic"><?php _e( 'Logic', 'ipt_fsqm' ); ?></a></li>
		</ul>
		<div id="<?php echo $tab_names; ?>_elm">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[title]', __( 'Title', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[title]', $data['title'], __( 'Enter Primary Label', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[subtitle]', __( 'Subtitle', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( $name_prefix . '[subtitle]', $data['subtitle'], __( 'Description Text (Optional)', 'ipt_fsqm' ), 'large' ); ?></td>
						<td></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][vertical]', __( 'Label Alignment', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][vertical]', __( 'Vertical', 'ipt_fsqm' ), __( 'Horizontal', 'ipt_fsqm' ), $data['settings']['vertical'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'The alignment of the label(question) and options. Making Horizontal will show the label on left, whereas making vertical will show it on top.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<?php if ( in_array( $element['type'], [ 'radio', 'checkbox' ] ) ) : ?>
						<tr>
							<th>
								<?php $this->ui->generate_label( $name_prefix . '[settings][button_type]', __( 'Appear as blocks', 'ipt_fsqm' ) ); ?>
							</th>
							<td>
								<?php $this->ui->toggle( $name_prefix . '[settings][button_type]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['button_type'], '1' ); ?>
							</td>
							<td><?php $this->ui->help( __( 'If enabled, then options appear as blocks instead of plain radio or checkboxes.', 'ipt_fsqm' ) ); ?></td>
						</tr>
					<?php endif; ?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][centered]', __( 'Center Content', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][centered]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['centered'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then labels and elements will be centered. This will force vertical the content.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][hidden_label]', __( 'Hide Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][hidden_label]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['hidden_label'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If enabled, then label along with subtitle and description would be hidden on the form. It would be visible only on the summary table and on emails. When using this, place a meaningful text in the placeholder.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<?php if ( isset( $data['settings']['icon'] ) ) : ?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][icon]', __( 'Select Icon', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->icon_selector( $name_prefix . '[settings][icon]', $data['settings']['icon'], __( 'Do not use any icon', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the icon you want to appear inside the selected radio/checkbox.', 'ipt_fsqm' ) ) ?></td>
					</tr>
					<?php endif; ?>
					<?php if ( $for_select ) : ?>
						<tr>
							<th><?php $this->ui->generate_label( $name_prefix . '[settings][show_search]', __( 'Show search', 'ipt_fsqm' ) ); ?></th>
							<td>
								<?php $this->ui->toggle( $name_prefix . '[settings][show_search]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['show_search'], '1' ); ?>
							</td>
							<td><?php $this->ui->help( __( 'If disabled then the search will not be shown. If enabled the search will be shown when there are 5 items or more.', 'ipt_fsqm' ) ); ?></td>
						</tr>
						<tr>
							<th><?php $this->ui->generate_label( $name_prefix . '[settings][use_native_select]', __( 'Use Native Select', 'ipt_fsqm' ) ); ?></th>
							<td>
								<?php $this->ui->toggle( $name_prefix . '[settings][use_native_select]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['use_native_select'], '1' ); ?>
							</td>
							<td><?php $this->ui->help( __( 'If enabled, then the advanced select will not be used, instead will use HTML select.', 'ipt_fsqm' ) ); ?></td>
						</tr>
					<?php endif; ?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[tooltip]', __( 'Tooltip', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( $name_prefix . '[tooltip]', $data['tooltip'], __( 'HTML Enabled', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'If you want to show tooltip, then please enter it here. You can write custom HTML too. Leave empty to disable.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_ifs">
			<table class="form-table">
				<tbody>
					<?php if ( $for_select ) : ?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][e_label]', __( 'Placeholder Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][e_label]', $data['settings']['e_label'], __( 'Enter the label', 'ipt_fsqm' ), 'large' ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'Enter the label of the first option which will correspond to an empty answer. Leaving it blank will disable this feature.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<?php endif; ?>
					<?php if ( isset( $data['settings']['multiple'] ) ) : ?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][multiple]', __( 'Select Multiple', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][multiple]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['multiple'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Whether to allow user to select multiple options.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<?php endif; ?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][type]', __( 'Prefill Type', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->select( $name_prefix . '[settings][type]', $prefill_types, $data['settings']['type'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Set the type of the prefill value the field will get. It can be based on URL parameter or user meta key. Leave to None if you do not wish to prefill the value.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][parameter]', __( 'Key Parameter', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][parameter]', $data['settings']['parameter'], __( 'Required', 'ipt_fsqm' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Enter the key parameter. In case of URL type value, <code>$_REQUEST[ $key ]</code> would be used. In case of User meta type value, the mentioned metakey would be used to retrieve the metavalue. It can not be empty or no value would be generated.', 'ipt_fsqm' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_options">
			<table class="form-table">
				<tbody>
					<?php if ( !$for_select ) : ?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][columns]', __( 'Options Columns', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->layout_select( $name_prefix . '[settings][columns]', $data['settings']['columns'] ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Select the number of columns in which you want the options to appear. Ideally it should be left to 2.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<?php endif; ?>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][shuffle]', __( 'Shuffle Options', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][shuffle]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['shuffle'], '1' ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Shuffle the options.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][others]', __( 'Show Others Option', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->toggle( $name_prefix . '[settings][others]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['others'], '1', false, true, array( 'condid' => $this->ui->generate_id_from_name( $name_prefix . '[settings][o_label]' )  . '_wrap' ) ); ?>
						</td>
						<td><?php $this->ui->help( __( 'Turn the feature on to show user enterable option.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr id="<?php echo $this->ui->generate_id_from_name( $name_prefix . '[settings][o_label]' )  . '_wrap'; ?>">
						<th><?php $this->ui->generate_label( $name_prefix . '[settings][o_label]', __( 'Others Label', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( $name_prefix . '[settings][o_label]', $data['settings']['o_label'], __( 'Enter the label', 'ipt_fsqm' ), 'large' ); ?>
						</td>
						<td>
							<?php $this->ui->help( __( 'Enter the label of the "Other" option.', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>

					<tr>
						<th colspan="2"><?php _e( 'Option List', 'ipt_fsqm' ); ?></th>
						<td>
							<?php $this->ui->help( __( 'Enter the options. ', 'ipt_fsqm' ) . ( $score ? __( 'You can also have score associated to the options. The value of the score should be numeric positive or negative number.', 'ipt_fsqm' ) : '' ) ); ?>
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<?php $this->ui->sda_list( array(
								'columns' => $sda_columns,
								'labels' => $labels,
							), $sda_items, $sda_data, $max_key ); ?>
							<div class="clear"></div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="<?php echo $tab_names; ?>_validation">
			<?php $this->build_validation( $name_prefix, $element_structure['validation'], $data['validation'] ); ?>
		</div>
		<div id="<?php echo $tab_names; ?>_logic">
			<?php $this->build_conditional( $name_prefix, $data['conditional'] ); ?>
		</div>
	</div>

		<?php
		$this->ui->textarea_linked_wp_editor( $name_prefix . '[description]', $data['description'], '' );
	}

	public function build_conditional_config( $name_prefix, $configs, $cond_suffix, $cond_id, $data, $outer_key = '__SDAKEY__' ) {
		$sda_outer = array();
		$sda_conditional = array();

		// Create the inner one with conditional logic
		$cond_name_prefix = $name_prefix . '[' . $outer_key . ']' . '[' . $cond_suffix . ']' . '[__CONDKEY__]';
		$sda_conditional['columns'] = array(
			0 => array(
				'label' => __( '(X)', 'ipt_fsqm' ),
				'size' => '16',
				'type' => 'select',
			),
			1 => array(
				'label' => __( '{KEY}', 'ipt_fsqm' ),
				'size' => '16',
				'type' => 'spinner',
			),
			2 => array(
				'label' => __( 'has', 'ipt_fsqm' ),
				'size' => '16',
				'type' => 'select',
			),
			3 => array(
				'label' => __( 'which', 'ipt_fsqm' ),
				'size' => '15',
				'type' => 'select',
			),
			4 => array(
				'label' => __( 'this value', 'ipt_fsqm' ),
				'size' => '24',
				'type' => 'text',
			),
			5 => array(
				'label' => __( 'rel', 'ipt_fsqm' ),
				'size' => '13',
				'type' => 'select',
			),
		);
		$sda_conditional['items'] = array();
		$m_type_select = array(
			0 => array(
				'value' => 'mcq',
				'label' => __( '(M) MCQ', 'ipt_fsqm' ),
			),
			1 => array(
				'value' => 'freetype',
				'label' => __( '(F) Text Input', 'ipt_fsqm' ),
			),
			2 => array(
				'value' => 'pinfo',
				'label' => __( '(O) Others', 'ipt_fsqm' ),
			),
		);
		$has_select = array( // check logic
			0 => array(
				'value' => 'val',
				'label' => __( 'value', 'ipt_fsqm' ),
			),
			1 => array(
				'value' => 'len',
				'label' => __( 'length', 'ipt_fsqm' ),
			),
		);
		$which_is_select = array( // operator logic
			0 => array(
				'value' => 'eq',
				'label' => __( 'equals to', 'ipt_fsqm' ),
			),
			1 => array(
				'value' => 'neq',
				'label' => __( 'not equals to', 'ipt_fsqm' ),
			),
			2 => array(
				'value' => 'gt',
				'label' => __( 'greater than', 'ipt_fsqm' ),
			),
			3 => array(
				'value' => 'lt',
				'label' => __( 'less than', 'ipt_fsqm' ),
			),
			4 => array(
				'value' => 'ct',
				'label' => __( 'contains', 'ipt_fsqm' ),
			),
			5 => array(
				'value' => 'dct',
				'label' => __( 'does not contain', 'ipt_fsqm' ),
			),
			6 => array(
				'value' => 'sw',
				'label' => __( 'starts with', 'ipt_fsqm' ),
			),
			7 => array(
				'value' => 'ew',
				'label' => __( 'ends with', 'ipt_fsqm' ),
			),
		);
		$rel_select = array(
			0 => array(
				'value' => 'and',
				'label' => __( 'AND', 'ipt_fsqm' ),
			),
			1 => array(
				'value' => 'or',
				'label' => __( 'OR', 'ipt_fsqm' ),
			),
		);
		$sda_conditional['data'] = array(
			0 => array( $cond_name_prefix . '[m_type]', $m_type_select, 'mcq', false, false, false, true, array( 'fit-text' ) ),
			1 => array( $cond_name_prefix . '[key]', '0', __( '{key}', 'ipt_fsqm' ), 0, 500 ),
			2 => array( $cond_name_prefix . '[check]', $has_select, 'val', false, false, false, true, array( 'fit-text' ) ),
			3 => array( $cond_name_prefix . '[operator]', $which_is_select, 'eq', false, false, false, true, array( 'fit-text' ) ),
			4 => array( $cond_name_prefix . '[value]', '', '' ),
			5 => array( $cond_name_prefix . '[rel]', $rel_select, 'and', false, false, false, true, array( 'fit-text' ) ),
		);
		$sda_conditional['max_key'] = 0;

		// Setup conditional SDA additional settings
		$sda_conditional['labels'] = array(
			'add' => __( 'Add New Logic', 'ipt_fsqm' ),
		);
		$sda_conditional['key'] = '__CONDKEY__';

		// Now complete the outer one
		$sda_outer['columns'] = array();
		$sda_outer['items'] = array();
		$sda_outer['data'] = array();
		$sda_outer['max_key'] = 0;
		// Loop through and create the column and data
		foreach ( $configs as $config_key => $config ) {
			$sda_outer['columns'][] = array(
				'label' => $config['label'],
				'size' => $config['size'],
				'type' => $config['type']
			);
			$sda_outer['data'][] = $config['data'];
		}

		// Loop through data and create items
		foreach ( $data as $item_key => $item ) {
			$new_sda_outer_item = array();
			foreach ( $item as $data_key => $values ) {
				// If for the logics
				if ( $cond_suffix == $data_key ) {
					// Create the basic items config
					$sda_cond_item_data = $sda_conditional['data'];
					foreach ( $sda_cond_item_data as $scidkey => $scidval ) {
						$scidval[0] = str_replace( $outer_key, $item_key, $scidval[0] );
						$sda_cond_item_data[ $scidkey ] = $scidval;
					}
					$sda_conditional_items = array(
						'settings' => array(
							'key' => $sda_conditional['key'],
							'columns' => $sda_conditional['columns'],
							'labels' => $sda_conditional['labels'],
						),
						'items' => array(),
						'data' => $sda_cond_item_data,
						'max_key' => 0,
						'id' => $cond_id . $data_key . '_logics',
					);
					// Now loop through and add data to the conditional
					$cond_items_name_prefix = $name_prefix . '[' . $item_key . ']' . '[' . $cond_suffix . ']' . '[%d]';
					foreach ( $values as $cond_key => $logic ) {
						$sda_conditional_items['max_key'] = max( $sda_conditional_items['max_key'], $cond_key );
						$sda_conditional_items['items'][ $cond_key ] = array(
							0 => array( sprintf( $cond_items_name_prefix . '[m_type]', $cond_key ), $m_type_select, $logic['m_type'], false, false, false, true, array( 'fit-text' ) ),
							1 => array( sprintf( $cond_items_name_prefix . '[key]', $cond_key ), $logic['key'], __( '{key}', 'ipt_fsqm' ), 0, 500 ),
							2 => array( sprintf( $cond_items_name_prefix . '[check]', $cond_key ), $has_select, $logic['check'], false, false, false, true, array( 'fit-text' ) ),
							3 => array( sprintf( $cond_items_name_prefix . '[operator]', $cond_key ), $which_is_select, $logic['operator'], false, false, false, true, array( 'fit-text' ) ),
							4 => array( sprintf( $cond_items_name_prefix . '[value]', $cond_key ), $logic['value'], '' ),
							5 => array( sprintf( $cond_items_name_prefix . '[rel]', $cond_key ), $rel_select, $logic['rel'], false, false, false, true, array( 'fit-text' ) ),
						);
					}
					$new_sda_outer_item[] = array_values( $sda_conditional_items );
				// For other config items
				} else {
					$new_sda_outer_item[] = $values;
				}
				$sda_outer['items'][ $item_key ] = $new_sda_outer_item;
			}
		}

		// Calculate max keys
		if ( count( $sda_outer['items'] ) ) {
			$sda_outer['max_key'] = max( array_keys( $sda_outer['items'] ) );
		}

		// Add the conditional SDA inside outer SDA
		$sda_outer['columns'][] = array(
			'label' => __( 'Conditional Logic', 'ipt_fsqm' ),
			'size' => '100',
			'type' => 'sda_list',
		);
		$sda_outer['data'][] = array_values( array(
			'settings' => array(
				'key' => $sda_conditional['key'],
				'columns' => $sda_conditional['columns'],
				'labels' => $sda_conditional['labels'],
			),
			'items' => array(),
			'data' => $sda_conditional['data'],
			'max_key' => $sda_conditional['max_key'],
			'id' => $cond_id . $sda_conditional['key'] . '_logics',
		) );

		// Done, now print
		echo '<div class="eform-conditional-config">';
		$this->ui->sda_list( array(
			'key' => $outer_key,
			'columns' =>  $sda_outer['columns'],
			'labels' => array(
				'add' => __( 'Add New Config', 'ipt_fsqm' ),
			),
		), $sda_outer['items'], $sda_outer['data'], $sda_outer['max_key'], $cond_id );
		echo '</div>';
	}

	public function build_conditional( $name_prefix, $data, $header_title = '', $show_status = true, $name_suffix = '[conditional]', $toggle_title = '' ) {
		$name_prefix = $name_prefix . $name_suffix;
		$cond_id = $this->generate_id_from_name( $name_prefix ) . '_conditional_type_wrap';

		$sda_columns = array(
			0 => array(
				'label' => __( '(X)', 'ipt_fsqm' ),
				'size' => '16',
				'type' => 'select',
			),
			1 => array(
				'label' => __( '{KEY}', 'ipt_fsqm' ),
				'size' => '16',
				'type' => 'spinner',
			),
			2 => array(
				'label' => __( 'has', 'ipt_fsqm' ),
				'size' => '16',
				'type' => 'select',
			),
			3 => array(
				'label' => __( 'which', 'ipt_fsqm' ),
				'size' => '15',
				'type' => 'select',
			),
			4 => array(
				'label' => __( 'this value', 'ipt_fsqm' ),
				'size' => '24',
				'type' => 'text',
			),
			5 => array(
				'label' => __( 'rel', 'ipt_fsqm' ),
				'size' => '13',
				'type' => 'select',
			),
		);
		$sda_labels = array(
			'add' => __( 'Add New Logic', 'ipt_fsqm' ),
		);
		$m_type_select = array(
			0 => array(
				'value' => 'mcq',
				'label' => __( '(M) MCQ', 'ipt_fsqm' ),
			),
			1 => array(
				'value' => 'freetype',
				'label' => __( '(F) Text Input', 'ipt_fsqm' ),
			),
			2 => array(
				'value' => 'pinfo',
				'label' => __( '(O) Others', 'ipt_fsqm' ),
			),
		);
		$has_select = array( // check logic
			0 => array(
				'value' => 'val',
				'label' => __( 'value', 'ipt_fsqm' ),
			),
			1 => array(
				'value' => 'len',
				'label' => __( 'length', 'ipt_fsqm' ),
			),
		);
		$which_is_select = array( // operator logic
			0 => array(
				'value' => 'eq',
				'label' => __( 'equals to', 'ipt_fsqm' ),
			),
			1 => array(
				'value' => 'neq',
				'label' => __( 'not equals to', 'ipt_fsqm' ),
			),
			2 => array(
				'value' => 'gt',
				'label' => __( 'greater than', 'ipt_fsqm' ),
			),
			3 => array(
				'value' => 'lt',
				'label' => __( 'less than', 'ipt_fsqm' ),
			),
			4 => array(
				'value' => 'ct',
				'label' => __( 'contains', 'ipt_fsqm' ),
			),
			5 => array(
				'value' => 'dct',
				'label' => __( 'does not contain', 'ipt_fsqm' ),
			),
			6 => array(
				'value' => 'sw',
				'label' => __( 'starts with', 'ipt_fsqm' ),
			),
			7 => array(
				'value' => 'ew',
				'label' => __( 'ends with', 'ipt_fsqm' ),
			),
		);
		$rel_select = array(
			0 => array(
				'value' => 'and',
				'label' => __( 'AND', 'ipt_fsqm' ),
			),
			1 => array(
				'value' => 'or',
				'label' => __( 'OR', 'ipt_fsqm' ),
			),
		);
		$sda_data_name_prefix = $name_prefix . '[logic][__SDAKEY__]';
		$sda_data = array(
			0 => array( $sda_data_name_prefix . '[m_type]', $m_type_select, 'mcq', false, false, false, true, array( 'fit-text' ) ),
			1 => array( $sda_data_name_prefix . '[key]', '0', __( '{key}', 'ipt_fsqm' ), 0, 500 ),
			2 => array( $sda_data_name_prefix . '[check]', $has_select, 'val', false, false, false, true, array( 'fit-text' ) ),
			3 => array( $sda_data_name_prefix . '[operator]', $which_is_select, 'eq', false, false, false, true, array( 'fit-text' ) ),
			4 => array( $sda_data_name_prefix . '[value]', '', '' ),
			5 => array( $sda_data_name_prefix . '[rel]', $rel_select, 'and', false, false, false, true, array( 'fit-text' ) ),
		);

		$sda_items = array();
		$sda_max_key = null;
		$sda_items_name_prefix = $name_prefix . '[logic][%d]';
		foreach ( (array) $data['logic'] as $s_key => $logic ) {
			$sda_max_key = max( array( $sda_max_key, $s_key ) );
			$sda_items[] = array(
				0 => array( sprintf( $sda_items_name_prefix . '[m_type]', $s_key ), $m_type_select, $logic['m_type'], false, false, false, true, array( 'fit-text' ) ),
				1 => array( sprintf( $sda_items_name_prefix . '[key]', $s_key ), $logic['key'], __( '{key}', 'ipt_fsqm' ), 0, 500 ),
				2 => array( sprintf( $sda_items_name_prefix . '[check]', $s_key ), $has_select, $logic['check'], false, false, false, true, array( 'fit-text' ) ),
				3 => array( sprintf( $sda_items_name_prefix . '[operator]', $s_key ), $which_is_select, $logic['operator'], false, false, false, true, array( 'fit-text' ) ),
				4 => array( sprintf( $sda_items_name_prefix . '[value]', $s_key ), $logic['value'], '' ),
				5 => array( sprintf( $sda_items_name_prefix . '[rel]', $s_key ), $rel_select, $logic['rel'], false, false, false, true, array( 'fit-text' ) ),
			);
		}
		if ( $toggle_title == '' ) {
			$toggle_title = __( 'Use conditional logic on this element', 'ipt_fsqm' );
		}
		?>
<?php if ( '' != $header_title ) : ?>
	<h3><?php echo $header_title; ?></h3>
<?php endif; ?>
<table class="form-table">
	<thead>
		<tr>
			<th>
				<?php $this->ui->generate_label( $name_prefix . '[active]', $toggle_title ); ?>
			</th>
			<td>
				<?php $this->ui->toggle( $name_prefix . '[active]', __( 'YES', 'ipt_fsqm' ), __( 'NO', 'ipt_fsqm' ), $data['active'], '1', false, true, array(
					'condid' => $cond_id,
				) ); ?>
			</td>
			<td>
				<?php $this->ui->help( sprintf( __( 'Enable or disable conditional logic for this element. More information can be found <a href="%1$s" target="_blank">at this link</a>.', 'ipt_fsqm' ), 'https://wpquark.com/kb/fsqm/conditional-logic/' ) ); ?>
			</td>
		</tr>
	</thead>
	<tbody id="<?php echo $cond_id ?>">
		<?php if ( $show_status == true ) : ?>
		<tr>
			<th>
				<?php $this->ui->generate_label( $name_prefix . '[status]', __( 'Initial Status', 'ipt_fsqm' ) ); ?>
			</th>
			<td>
				<?php $this->ui->toggle( $name_prefix . '[status]', __( 'Shown', 'ipt_fsqm' ), __( 'Hidden', 'ipt_fsqm' ), $data['status'] ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Initial visual status of this element. You can hide it initially and conditionally show it.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php $this->ui->generate_label( $name_prefix . '[change]', __( 'Change status to', 'ipt_fsqm' ) ); ?>
			</th>
			<td>
				<?php $this->ui->toggle( $name_prefix . '[change]', __( 'Show', 'ipt_fsqm' ), __( 'Hide', 'ipt_fsqm' ), $data['change'] ); ?>
			</td>
			<td>
				<?php $this->ui->help_head( __( 'Conditional Logic', 'ipt_fsqm' ) ); ?>
				<p>
					<?php printf( __( 'Here you can build the conditional logic based on existing elements and comparing their value and/or length. When conditional logic is active, the validation logic will have implicit effect, i.e, the validation logic will only be considered, when according to the conditional logic the field is shown. So, you can make an element required, but hidden at first which would only be shown for certain cases. When the case criteria is matched, it would become mandatory for the users to fill this element. More information can be found <a href="%1$s" target="_blank">at this link</a>.', 'ipt_fsqm' ), 'https://wpquark.com/kb/fsqm/conditional-logic/' ); ?>
				</p>
				<p>
					<?php _e( 'Conditional logics are also grouped automatically against the OR operator.', 'ipt_fsqm' ); ?>
				</p>
				<p>
					<?php _e( 'So for instance if you have a logic defined as:<code>C1 AND C2 OR C2 AND C3 AND C4 OR C5 AND C6</code> it will be interpreted as <code>(C1 AND C2) OR (C2 AND C3 AND C4) OR (C5 AND C6)</code>.', 'ipt_fsqm' ); ?>
				</p>
				<p>
					<?php _e( 'If any of the conditions separated by OR is true, the logic is regared as true.', 'ipt_fsqm' ); ?>
				</p>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<tr>
			<td colspan="3"><p class="description"><?php _e( 'If the following conditions are met.', 'ipt_fsqm' ); ?></p></td>
		</tr>
		<?php else : ?>
		<tr>
			<td colspan="3">
				<?php $this->ui->help_head( __( 'Conditional Logic', 'ipt_fsqm' ) ); ?>
				<p>
					<?php printf( __( 'Here you can build the conditional logic based on existing elements and comparing their value and/or length. When conditional logic is active, the validation logic will have implicit effect, i.e, the validation logic will only be considered, when according to the conditional logic the field is shown. So, you can make an element required, but hidden at first which would only be shown for certain cases. When the case criteria is matched, it would become mandatory for the users to fill this element. More information can be found <a href="%1$s" target="_blank">at this link</a>.', 'ipt_fsqm' ), 'https://wpquark.com/kb/fsqm/conditional-logic/' ); ?>
				</p>
				<p>
					<?php _e( 'Conditional logics are also grouped automatically against the OR operator.', 'ipt_fsqm' ); ?>
				</p>
				<p>
					<?php _e( 'So for instance if you have a logic defined as:<code>C1 AND C2 OR C2 AND C3 AND C4 OR C5 AND C6</code> it will be interpreted as <code>(C1 AND C2) OR (C2 AND C3 AND C4) OR (C5 AND C6)</code>.', 'ipt_fsqm' ); ?>
				</p>
				<p>
					<?php _e( 'If any of the conditions separated by OR is true, the logic is regared as true.', 'ipt_fsqm' ); ?>
				</p>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<?php endif; ?>
		<tr>
			<td colspan="3">
				<?php $this->ui->sda_list( array(
					'columns' => $sda_columns,
					'labels' => $sda_labels,
				), $sda_items, $sda_data, $sda_max_key ); ?>
			</td>
		</tr>
	</tbody>
</table>
		<?php
	}

	public function build_validation( $name_prefix, $validation, $data, $close_table = true ) {
		$name_prefix = $name_prefix . '[validation]';
		$cond_id = $this->generate_id_from_name( $name_prefix ) . '_validation_type_wrap_';
		$valid_types = array( //phone, url, email, date, number, integer, ipv4, onlyNumberSp, onlyLetterSp, onlyLetterNumber
			array(
				'value' => 'all',
				'label' => __( 'Everything', 'ipt_fsqm' ),
				'data' => array( 'condid' => $cond_id . 'minsize,' . $cond_id . 'maxsize' ),
			),
			array(
				'value' => 'phone',
				'label' => __( 'Phone Number', 'ipt_fsqm' ),
				'data' => array( 'condid' => $cond_id . 'minsize,' . $cond_id . 'maxsize' ),
			),
			array(
				'value' => 'url',
				'label' => __( 'Anchor Links (URL)', 'ipt_fsqm' ),
				'data' => array( 'condid' => $cond_id . 'none' ),
			),
			array(
				'value' => 'email',
				'label' => __( 'Email Address', 'ipt_fsqm' ),
				'data' => array( 'condid' => $cond_id . 'none' ),
			),
			array(
				'value' => 'ipv4',
				'label' => __( 'IP V4 Address Format', 'ipt_fsqm' ),
				'data' => array( 'condid' => $cond_id . 'none' ),
			),
			array(
				'value' => 'number',
				'label' => __( 'Only Numbers (Float or Integers)', 'ipt_fsqm' ),
				'data' => array( 'condid' => $cond_id . 'min,' . $cond_id . 'max' ),
			),
			array(
				'value' => 'integer',
				'label' => __( 'Only Integers', 'ipt_fsqm' ),
				'data' => array( 'condid' => $cond_id . 'min,' . $cond_id . 'max' ),
			),
			array(
				'value' => 'onlyNumberSp',
				'label' => __( 'Only Numbers and Spaces', 'ipt_fsqm' ),
				'data' => array( 'condid' => $cond_id . 'minsize,' . $cond_id . 'maxsize' ),
			),
			array(
				'value' => 'onlyLetterSp',
				'label' => __( 'Only Letters and Spaces', 'ipt_fsqm' ),
				'data' => array( 'condid' => $cond_id . 'minsize,' . $cond_id . 'maxsize' ),
			),
			array(
				'value' => 'onlyLetterNumber',
				'label' => __( 'Only Letters and Numbers', 'ipt_fsqm' ),
				'data' => array( 'condid' => $cond_id . 'minsize,' . $cond_id . 'maxsize' ),
			),
			array(
				'value' => 'onlyLetterNumberSp',
				'label' => __( 'Only Letters, Numbers and Spaces', 'ipt_fsqm' ),
				'data' => array( 'condid' => $cond_id . 'minsize,' . $cond_id . 'maxsize' ),
			),
			array(
				'value' => 'noSpecialCharacter',
				'label' => __( 'No Special Characters', 'ipt_fsqm' ),
				'data' => array( 'condid' => $cond_id . 'minsize,' . $cond_id . 'maxsize' ),
			),
			array(
				'value' => 'personName',
				'label' => __( 'Person\'s Name - eg, Mr. John Doe', 'ipt_fsqm' ),
				'data' => array( 'condid' => $cond_id . 'none' ),
			),
		);

		// Input masking
		$masking_items = [];
		if ( isset( $validation['mask'] ) ) {
			// Enabled
			$masking_items[] = [
				'name' => $name_prefix . '[mask][enabled]',
				'label' => __( 'Mask Input', 'ipt_fsqm' ),
				'ui' => 'toggle',
				'param' => [ $name_prefix . '[mask][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['mask']['enabled'], '1', false, true, [
					'condid' => $cond_id . '_mask_type,' . $cond_id . '_mask_mask,' . $cond_id . '_mask_placeholder,' . $cond_id . '_mask_greedy',
				] ],
				'help' => __( 'Enable Input masking for this freetype element. Please follow the <a rel="noopener" target="_blank" href="https://github.com/RobinHerbots/Inputmask#static-masks">official guide</a> for setting up proper masking values.', 'ipt_fsqm' ),
			];
			// Type
			$mask_types = [
				'mask' => __( 'Static Mask', 'ipt_fsqm' ),
				'regex' => __( 'Regex Based Mask', 'ipt_fsqm' ),
			];
			$masking_items[] = [
				'name' => $name_prefix . '[mask][type]',
				'label' => __( 'Masking Definition Type', 'ipt_fsqm' ),
				'ui' => 'select',
				'param' => [ $name_prefix . '[mask][type]', $mask_types, $data['mask']['type'] ],
				'help' => __( 'Set the masking type. Static Masks are easy to implement, but you can have regex based masks too.', 'ipt_fsqm' ),
				'id' => $cond_id . '_mask_type',
			];
			// Mask
			$masking_items[] = [
				'name' => $name_prefix . '[mask][mask]',
				'label' => __( 'Static Mask or Regex', 'ipt_fsqm' ),
				'ui' => 'textarea',
				'param' => [ $name_prefix . '[mask][mask]', $data['mask']['mask'], __( 'Required', 'ipt_fsqm' ) ],
				'help' => __( 'Enter static or regex definition here.', 'ipt_fsqm' ),
				'id' => $cond_id . '_mask_mask',
			];
			// Placeholder
			$masking_items[] = [
				'name' => $name_prefix . '[mask][placeholder]',
				'label' => __( 'Mask Placeholder', 'ipt_fsqm' ),
				'ui' => 'text',
				'param' => [ $name_prefix . '[mask][placeholder]', $data['mask']['placeholder'], __( 'Required', 'ipt_fsqm' ) ],
				'help' => __( 'Enter placeholder. Multi character also supported.', 'ipt_fsqm' ),
				'id' => $cond_id . '_mask_placeholder',
			];
			// Greedy
			$masking_items[] = [
				'name' => $name_prefix . '[mask][greedy]',
				'label' => __( 'Greedy Comparison', 'ipt_fsqm' ),
				'ui' => 'toggle',
				'param' => [ $name_prefix . '[mask][greedy]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['mask']['greedy'] ],
				'help' => __( 'EToggle to allocate as much possible or the opposite. Non-greedy repeat function. With the non-greedy option set to false, you can specify * as repeat. This makes an endless repeat.', 'ipt_fsqm' ),
				'id' => $cond_id . '_mask_greedy',
			];
		}
		?>
	<?php if ( $close_table ) : ?>
	<table class="form-table">
		<tbody>
	<?php endif; ?>
			<?php if ( isset( $validation['required'] ) ) : ?>
			<tr>
				<th><?php $this->ui->generate_label( $name_prefix . '[required]', __( 'Compulsory', 'ipt_fsqm' ) ); ?></th>
				<td colspan="2">
					<?php $this->ui->toggle( $name_prefix . '[required]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['required'] ); ?>
				</td>
			</tr>
			<?php endif; ?>
			<?php if ( isset( $validation['equals'] ) ) : ?>
				<tr>
					<th><?php $this->ui->generate_label( $name_prefix . '[equals]', __( 'Equals to', 'ipt_fsqm' ) ); ?></th>
					<td>
						<?php $this->ui->text( $name_prefix . '[equals]', $data['equals'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
					</td>
					<td>
						<?php $this->ui->help( sprintf( __( 'Set the field ID of the element with which this must be equal to. Works only for freetype elements. Mention the full id of the element like this <code>F10</code> or <code>O11</code>.', 'ipt_fsqm' ), date( 'Y-m-d' ) ) ); ?>
					</td>
				</tr>
			<?php endif; ?>
			<?php if ( isset( $validation['filters'] ) ) : ?>

			<?php if ( isset( $validation['filters']['type'] ) ) : ?>
			<tr>
				<th><?php $this->ui->generate_label( $name_prefix . '[filters][type]', __( 'Input Filter', 'ipt_fsqm' ) ); ?></th>
				<td colspan="2">
					<?php $this->ui->select( $name_prefix . '[filters][type]', $valid_types, $data['filters']['type'], false, true ); ?>
				</td>
			</tr>
			<?php endif; ?>
			<?php if ( isset( $validation['filters']['min'] ) ) : ?>
			<tr id="<?php echo $cond_id . 'min'; ?>">
				<th><?php $this->ui->generate_label( $name_prefix . '[filters][min]', __( 'Minimum Value', 'ipt_fsqm' ) ); ?></th>
				<td>
					<?php $this->ui->spinner( $name_prefix . '[filters][min]', $data['filters']['min'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
				</td>
				<td>
					<?php $this->ui->help( __( 'Validates when the field\'s value is less than, or equal to, the given parameter. Can contain floating number.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
			<?php endif; ?>
			<?php if ( isset( $validation['filters']['max'] ) ) : ?>
			<tr id="<?php echo $cond_id . 'max'; ?>">
				<th><?php $this->ui->generate_label( $name_prefix . '[filters][max]', __( 'Maximum Value', 'ipt_fsqm' ) ); ?></th>
				<td>
					<?php $this->ui->spinner( $name_prefix . '[filters][max]', $data['filters']['max'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
				</td>
				<td>
					<?php $this->ui->help( __( 'Validates when the field\'s value is more than, or equal to, the given parameter. Can contain floating number.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
			<?php endif; ?>
			<?php if ( isset( $validation['filters']['minSize'] ) ) : ?>
			<tr id="<?php echo $cond_id . 'minsize'; ?>">
				<th><?php $this->ui->generate_label( $name_prefix . '[filters][minSize]', __( 'Minimum Size', 'ipt_fsqm' ) ); ?></th>
				<td>
					<?php $this->ui->spinner( $name_prefix . '[filters][minSize]', $data['filters']['minSize'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
				</td>
				<td>
					<?php $this->ui->help( __( 'Validates if the element content size (in characters) is more than, or equal to, the given integer.<br /><code>integer <= input.value.length</code>', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
			<?php endif; ?>
			<?php if ( isset( $validation['filters']['maxSize'] ) ) : ?>
			<tr id="<?php echo $cond_id . 'maxsize'; ?>">
				<th><?php $this->ui->generate_label( $name_prefix . '[filters][maxSize]', __( 'Maximum Size', 'ipt_fsqm' ) ); ?></th>
				<td>
					<?php $this->ui->spinner( $name_prefix . '[filters][maxSize]', $data['filters']['maxSize'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
				</td>
				<td>
					<?php $this->ui->help( __( 'Validates if the element content size (in characters) is less than, or equal to, the given integer.<br /><code>input.value.length <= integer</code>', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
			<?php endif; ?>
			<?php if ( isset( $validation['filters']['past'] ) ) : ?>
			<tr>
				<th><?php $this->ui->generate_label( $name_prefix . '[filters][past]', __( 'Before', 'ipt_fsqm' ) ); ?></th>
				<td>
					<?php $this->ui->text( $name_prefix . '[filters][past]', $data['filters']['past'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
				</td>
				<td>
					<?php $this->ui->help( sprintf( __( 'Checks if the element\'s value (which is implicitly a date) is less than the given date. When <code>NOW</code> is used as a parameter, the date will be calculate in the server only, in accordance with the timezone you have set for your website. You can also use arithmetic like <code>NOW+5</code> or <code>NOW-10</code> to add or subtract <strong>days</strong> from current date. You have to enter date in <code>YYYY-MM-DD</code> (Strict ISO Standard) format, for example %1$s. Also you can refer to other datepicker element, by entering their ID, like <code>O12</code> where the element is represented by <code>(O){12}</code>. This can be used for creating date ranges. This works for date pickers only, not for datetime or time pickers.', 'ipt_fsqm' ), date( 'Y-m-d' ) ) ); ?>
				</td>
			</tr>
			<?php endif; ?>
			<?php if ( isset( $validation['filters']['future'] ) ) : ?>
			<tr>
				<th><?php $this->ui->generate_label( $name_prefix . '[filters][future]', __( 'After', 'ipt_fsqm' ) ); ?></th>
				<td>
					<?php $this->ui->text( $name_prefix . '[filters][future]', $data['filters']['future'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
				</td>
				<td>
					<?php $this->ui->help( sprintf( __( 'Checks if the element\'s value (which is implicitly a date) is greater than the given date. When <code>NOW</code> is used as a parameter, the date will be calculate in the server only, in accordance with the timezone you have set for your website. You can also use arithmetic like <code>NOW+5</code> or <code>NOW-10</code> to add or subtract <strong>days</strong> from current date. You have to enter date in <code>YYYY-MM-DD</code> (Strict ISO Standard) format, for example %1$s. Also you can refer to other datepicker element, by entering their ID, like <code>O12</code> where the element is represented by <code>(O){12}</code>. This can be used for creating date ranges. This works for date pickers only, not for datetime or time pickers.', 'ipt_fsqm' ), date( 'Y-m-d' ) ) ); ?>
				</td>
			</tr>
			<?php endif; ?>
			<?php if ( isset( $validation['filters']['minCheckbox'] ) ) : ?>
			<tr>
				<th><?php $this->ui->generate_label( $name_prefix . '[filters][minCheckbox]', __( 'Minimum Selected Checkboxes', 'ipt_fsqm' ) ); ?></th>
				<td>
					<?php $this->ui->spinner( $name_prefix . '[filters][minCheckbox]', $data['filters']['minCheckbox'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
				</td>
				<td>
					<?php $this->ui->help( __( 'Validates when a minimum of integer checkboxes are selected.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
			<?php endif; ?>
			<?php if ( isset( $validation['filters']['maxCheckbox'] ) ) : ?>
			<tr>
				<th><?php $this->ui->generate_label( $name_prefix . '[filters][maxCheckbox]', __( 'Maximum Selected Checkboxes', 'ipt_fsqm' ) ); ?></th>
				<td>
					<?php $this->ui->spinner( $name_prefix . '[filters][maxCheckbox]', $data['filters']['maxCheckbox'], __( 'Disabled', 'ipt_fsqm' ) ); ?>
				</td>
				<td>
					<?php $this->ui->help( __( 'Limits the maximum number of selected check boxes.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
			<?php endif; ?>

			<?php endif; ?>

			<?php if ( isset( $validation['mask'] ) ) : ?>
				<?php $this->ui->form_table( $masking_items, false ); ?>
			<?php endif; ?>
		<?php if ( $close_table ) : ?>
		</tbody>
	</table>
		<?php endif; ?>
		<?php
	}

	public function material_options( $form ) {
		$skins = array(
			0 => array(
				'label' => __( 'Light Background', 'ipt_fsqm' ),
				'value' => 'light',
			),
			1 => array(
				'label' => __( 'Dark Background', 'ipt_fsqm' ),
				'value' => 'dark',
			),
		);

		$bg_repeat = array(
			'repeat' => __( 'Repeat both', 'ipt_fsqm' ),
			'repeat-x' => __( 'Repeat in x axis', 'ipt_fsqm' ),
			'repeat-y' => __( 'Repeat in y axis', 'ipt_fsqm' ),
			'no-repeat' => __( 'No repeat', 'ipt_fsqm' ),
		);
		$bg_origin = array(
			'padding-box' => __( 'Padding Box (Upper Left)', 'ipt_fsqm' ),
			'border-box' => __( 'Border Box (Upper Left of Border)', 'ipt_fsqm' ),
			'content-box' => __( 'Content Box (Upper Left of Content)', 'ipt_fsqm' ),
		);
		$bg_clip = array(
			'padding-box' => __( 'Padding Box (Upper Left)', 'ipt_fsqm' ),
			'border-box' => __( 'Border Box (Upper Left of Border)', 'ipt_fsqm' ),
			'content-box' => __( 'Content Box (Upper Left of Content)', 'ipt_fsqm' ),
		);
		$bg_attachment = array(
			'scroll' => __( 'Scroll with element', 'ipt_fsqm' ),
			'fixed' => __( 'Fixed in viewport', 'ipt_fsqm' ),
			'local' => __( 'Scroll with element content', 'ipt_fsqm' ),
		);

		$colors = array(
			'primary-color-dark' => __( 'Dark Primary Color', 'ipt_fsqm' ),
			'primary-color' => __( 'Primary Color', 'ipt_fsqm' ),
			'primary-color-light' => __( 'Light Primary Color', 'ipt_fsqm' ),
			'primary-color-text' => __( 'Text Color on Primary BG', 'ipt_fsqm' ),
			'accent-color' => __( 'Accent Color', 'ipt_fsqm' ),
			'background-color' => __( 'Background Color', 'ipt_fsqm' ),
			'primary-text-color' => __( 'Primary Text Color', 'ipt_fsqm' ),
			'secondary-text-color' => __( 'Secondary Text Color', 'ipt_fsqm' ),
			'border-color' => __( 'Border Color', 'ipt_fsqm' ),
			'divider-color' => __( 'Divider Color', 'ipt_fsqm' ),
			'disabled-color' => __( 'Disabled Background Color', 'ipt_fsqm' ),
			'disabled-color-text' => __( 'Disabled Text Color', 'ipt_fsqm' ),
			'ui-bg-color' => __( 'Small UI BG Color', 'ipt_fsqm' ),
			'widget-bg-color' => __( 'Large Widget BG Color', 'ipt_fsqm' ),
		);

		$button_variants = [];
		$button_variants[] = [
			'value' => 'classic',
			'label' =>  __( 'Classic Full Width', 'ipt_fsqm' ),
		];
		$button_variants[] = [
			'value' => 'flat',
			'label' =>  __( 'Flat', 'ipt_fsqm' ),
			'data' => [
				'condid' => 'ipt-eform-builder-config-style-material-br-wrap',
			],
		];
		$button_variants[] = [
			'value' => 'border',
			'label' =>  __( '3d Bordered', 'ipt_fsqm' ),
			'data' => [
				'condid' => 'ipt-eform-builder-config-style-material-br-wrap',
			],
		];
		$button_variants[] = [
			'value' => 'gradient',
			'label' =>  __( 'Gradient', 'ipt_fsqm' ),
			'data' => [
				'condid' => 'ipt-eform-builder-config-style-material-br-wrap',
			],
		];
		$button_variants[] = [
			'value' => 'outline',
			'label' =>  __( 'Outline', 'ipt_fsqm' ),
			'data' => [
				'condid' => 'ipt-eform-builder-config-style-material-br-wrap',
			],
		];

		$op = $this->settings['theme']['material'];
		$defaults = $this->get_default_settings()['theme']['material'];
		?>
<script type="text/javascript">
	jQuery( document ).ready( function( $ ) {
		var checkMaterialColor = function() {
			var theme = $( '#settings_theme_template' ).val(),
			elm = $( '#eform-material-custom-skin, #eform-material-custom-color' );
			if ( 'material-custom' == theme ) {
				elm.fadeIn( 'fast' );
			} else {
				elm.fadeOut( 'fast' );
			}
		};
		checkMaterialColor();
		$( '#settings_theme_template' ).on( 'change', checkMaterialColor );
	} );
</script>
<table class="form-table">
	<tbody>
		<tr id="eform-material-custom-skin" style="display: none;">
			<th><?php $this->ui->generate_label( 'settings[theme][material][skin]', __( 'Skin' ) ); ?></th>
			<td>
				<?php $this->ui->select( 'settings[theme][material][skin]', $skins, $op['skin'], false, true ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Select the skin for the material theme. You can choose one of the presets or create your own.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="eform-material-custom-color" style="display: none;">
			<td colspan="3">
				<table class="form-table">
					<tbody>
						<?php $c_i = 1; ?>
						<tr>
						<?php foreach ( $colors as $color_key => $color_label ) : ?>
							<th><?php $this->ui->generate_label( 'settings[theme][material][colors][' . $color_key . ']', $color_label ); ?></th>
							<td><?php $this->ui->colorpicker( 'settings[theme][material][colors][' . $color_key . ']', $op['colors'][ $color_key ], '', $defaults['colors'][ $color_key ] ); ?></td>
							<?php if ( 0 == ( $c_i % 2 ) && $c_i != count( $colors ) ) : ?>
						</tr>
						<tr>
							<?php endif; ?>
							<?php $c_i++; ?>
						<?php endforeach; ?>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[theme][material][width]', __( 'Form Width', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->text( 'settings[theme][material][width]', $op['width'], __( '100%', 'ipt_fsqm' ) ); ?></td>
			<td><?php $this->ui->help( __( 'Set the width of your form. This will be the maximum width, if the viewport width is less, then the form will always take up on the width of the viewport..', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[theme][material][button_style]', __( 'Progress Button Style', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->select( 'settings[theme][material][button_style]', $button_variants, $op['button_style'], false, true ); ?></td>
			<td><?php $this->ui->help( __( 'Set progress button style.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[theme][material][alternate_pb]', __( 'Button Color', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->toggle( 'settings[theme][material][alternate_pb]', __( 'Theme', 'ipt_fsqm' ), __( 'Grey', 'ipt_fsqm' ), $op['alternate_pb'] ) ?></td>
			<td><?php $this->ui->help( __( 'Alternate design for the progress buttons. Enable this if you want dark button toolbar design with primary color scheme.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="ipt-eform-builder-config-style-material-br-wrap">
			<th><?php $this->ui->generate_label( 'settings[theme][material][button_rounded]', __( 'Rounded Buttons', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->toggle( 'settings[theme][material][button_rounded]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['button_rounded'] ) ?></td>
			<td><?php $this->ui->help( __( 'Enable to have the inline buttons rounded appearance.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'settings[theme][material][bg][enabled]', __( 'Modify Form Background', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->toggle( 'settings[theme][material][bg][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $op['bg']['enabled'], '1', false, true, array(
				'condid' => 'eform-material-bg-config-image,eform-material-bg-config-position,eform-material-bg-config-size,eform-material-bg-config-repeat,eform-material-bg-config-origin,eform-material-bg-config-clip,eform-material-bg-config-attachment',
			) ) ?></td>
			<td><?php $this->ui->help( __( 'Customize the background of your form', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="eform-material-bg-config-image">
			<th><?php $this->ui->generate_label( 'settings[theme][material][bg][background-image]', __( 'Background Image', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->upload( 'settings[theme][material][bg][background-image]', $op['bg']['background-image'], __( 'Form Background Image', 'ipt_fsqm' ) ); ?></td>
			<td><?php $this->ui->help( __( 'Set the background image of your form.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="eform-material-bg-config-position">
			<th><?php $this->ui->generate_label( 'settings[theme][material][bg][background-position]', __( 'Background Position', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->text( 'settings[theme][material][bg][background-position]', $op['bg']['background-position'], __( 'auto', 'ipt_fsqm' ) ); ?></td>
			<td><?php $this->ui->help( __( 'Set the <a href="http://www.w3schools.com/cssref/pr_background-position.asp" target="_blank">background image position</a> of your form.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="eform-material-bg-config-size">
			<th><?php $this->ui->generate_label( 'settings[theme][material][bg][background-size]', __( 'Background Size', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->text( 'settings[theme][material][bg][background-size]', $op['bg']['background-size'], __( 'auto', 'ipt_fsqm' ) ); ?></td>
			<td><?php $this->ui->help( __( 'Set the <a href="http://www.w3schools.com/cssref/css3_pr_background-size.asp" target="_blank">background image size</a> of your form.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="eform-material-bg-config-repeat">
			<th><?php $this->ui->generate_label( 'settings[theme][material][bg][background-repeat]', __( 'Background Repeat', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->select( 'settings[theme][material][bg][background-repeat]', $bg_repeat, $op['bg']['background-repeat'] ); ?></td>
			<td><?php $this->ui->help( __( 'Set the <a href="http://www.w3schools.com/cssref/pr_background-repeat.asp" target="_blank">background image repeat</a> of your form.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="eform-material-bg-config-origin">
			<th><?php $this->ui->generate_label( 'settings[theme][material][bg][background-origin]', __( 'Background Origin', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->select( 'settings[theme][material][bg][background-origin]', $bg_origin, $op['bg']['background-origin'] ); ?></td>
			<td><?php $this->ui->help( __( 'Set the <a href="http://www.w3schools.com/cssref/css3_pr_background-origin.asp" target="_blank">background image origin</a> of your form.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="eform-material-bg-config-clip">
			<th><?php $this->ui->generate_label( 'settings[theme][material][bg][background-clip]', __( 'Background Clip', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->select( 'settings[theme][material][bg][background-clip]', $bg_clip, $op['bg']['background-clip'] ); ?></td>
			<td><?php $this->ui->help( __( 'Set the <a href="http://www.w3schools.com/cssref/css3_pr_background-clip.asp" target="_blank">background image clip</a> of your form.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr id="eform-material-bg-config-attachment">
			<th><?php $this->ui->generate_label( 'settings[theme][material][bg][background-attachment]', __( 'Background Scroll', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->select( 'settings[theme][material][bg][background-attachment]', $bg_attachment, $op['bg']['background-attachment'] ); ?></td>
			<td><?php $this->ui->help( __( 'Set the <a href="http://www.w3schools.com/cssref/pr_background-attachment.asp" target="_blank">background image scroll behavior</a> of your form.', 'ipt_fsqm' ) ); ?></td>
		</tr>
	</tbody>
</table>
		<?php
	}

	public function material_custom( $return_id, $name, $settings, $layout, $save_process, $form_type, $form_category ) {
		// Init the save path
		$wp_upload_dir = wp_upload_dir();
		$save_path = $wp_upload_dir['basedir'] . '/eform-custom-material';
		$save_file = $save_path . '/form-theme-' . $return_id . '.css';
		$checksum_file = $save_path . '/.form-theme-' . $return_id . '.checksum';
		@wp_mkdir_p( $save_path );

		// Get the checksum beforehand
		$existing_checksum = '';
		if ( file_exists( $checksum_file ) ) {
			$existing_checksum = file_get_contents( $checksum_file );
		}

		// Get our color settings
		$colors = $settings['theme']['material']['colors'];

		// Set import path
		$import_path = IPT_EFORM_ABSPATH . 'static/front/css/source/';

		// Create variables
		$variables = array(
			'selector'                             => 'ipt-uif-custom-material-custom',
			'img-path'                             => str_replace( array( 'http://', 'https://' ), array( '//', '//' ), IPT_FSQM_Loader::$static_location . 'front/images/' ),
			'primary-color-dark'                   => $colors['primary-color-dark'],
			'primary-color'                        => $colors['primary-color'],
			'primary-color-light'                  => $colors['primary-color-light'],
			'primary-color-text'                   => $colors['primary-color-text'],
			'accent-color'                         => $colors['accent-color'],
			'primary-text-color'                   => $colors['primary-text-color'],
			'heading-text-color'                   => 'darken( $primary-text-color, 10% )',
			'passive-tab-notifier'                 => 'lighten( $primary-color, 5% )',
			'secondary-text-color'                 => $colors['secondary-text-color'],
			'divider-color'                        => $colors['divider-color'], //l3
			'disabled-color'                       => $colors['disabled-color'], //l4
			'disabled-color-text'                  => $colors['disabled-color-text'], //l3
			'preset-bg'                            => $colors['background-color'],
			'preset-button-container'              => $colors['disabled-color'], //l4
			'preset-button-container-button-hover' => $colors['disabled-color-text'], //l3
			'input-border-color'                   => $colors['border-color'], //base
			'switch-unchecked-bg'                  => $colors['disabled-color'], //l4
			'switch-unchecked-lever-bg'            => $colors['ui-bg-color'], //l2
			'slider-bg-color'                      => $colors['ui-bg-color'], //l2
			'select2-highlight-selected'           => $colors['ui-bg-color'],//l2
			'sortable-icon-color'                  => $colors['secondary-text-color'],//l1
			'sortable-border-color'                => $colors['divider-color'],//l3
			'table-striped-color'                  => $colors['disabled-color'],//l4
			'keyboard-bg'                          => $colors['widget-bg-color'],//l5
			'keyboard-num-border-color'            => $colors['disabled-color'],//l4
			'keyboard-action-bg'                   => $colors['disabled-color'], //l4
			'up-button-container'                  => $colors['disabled-color'], //l4
			'styled-container-bg'                  => $colors['widget-bg-color'], //l5
			'estimator-slider-bg'                  => 'lighten($primary-color, 30%)',
			'estimator-slider-slide'               => 'darken($primary-color, 30%)',
		);

		// Create new checksum
		$new_checksum_string = implode( '::', array_values( $variables ) ) . '::' . $import_path . '::' . IPT_FSQM_Loader::$version;
		$new_checksum = (string) crc32( $new_checksum_string );

		// If checksum matches, then do nothing
		if ( $existing_checksum === $new_checksum ) {
			// Do nothing
			return;
		}

		// Create Compiler
		$compiler = new Leafo\ScssPhp\Compiler();

		// Set import path
		$compiler->setImportPaths( $import_path );

		// Set variables
		$compiler->setVariables( $variables );

		// Set formatter
		$compiler->setFormatter( 'Leafo\ScssPhp\Formatter\Compressed' );

		// Create source code accordingly
		$scss_code = '@import "compile"';
		if ( 'dark' == $settings['theme']['material']['skin'] ) {
			$scss_code = '@import "compile-dark";';
		}

		// Get compiled code
		try {
			$compiled_css = $compiler->compile( $scss_code );
		} catch ( Exception $e ) {
			// Some compilation exception, so use the defaults
			// Error with the default?? Something wrong with the SCSS Files?
			$compiled_css =
			'#ipt_fsqm_form_wrap_' . $this->form_id . '::before {
				display: block;
				content: "' . $e->getMessage() . ': If the error is about variable, then check your color codes. Common mistakes are #transparent whereas it should be just transparent. Otherwise something wrong with SCSS source. Clean installation of eForm might help.";
				background: #EF5350;
				padding: 16px;
				text-align: center;
				color: #fff;
				font-size: 20px;
				border-bottom: 8px solid #D32F2F;
				margin: 8px 0;
			}';
		}

		// Save css file
		file_put_contents( $save_file, $compiled_css );
		file_put_contents( $checksum_file, $new_checksum );
		// Save checksum
	}

	public function _helper_build_prefil_text( $name_prefix, $data ) {
		$prefill_types = array(
			0 => array(
				'value' => 'none',
				'label' => __( 'None', 'ipt_fsqm' ),
			),
			1 => array(
				'value' => 'url',
				'label' => __( 'URL Parameter Based', 'ipt_fsqm' ),
			),
			2 => array(
				'value' => 'meta',
				'label' => __( 'User Meta Based', 'ipt_fsqm' ),
			),
			3 => array(
				'value' => 'postmeta',
				'label' => __( 'Post Meta Based', 'ipt_fsqm' ),
			),
		);
		?>
		<tr>
			<th><?php $this->ui->generate_label( $name_prefix . '[settings][default]', __( 'Default Value', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( $name_prefix . '[settings][default]', $data['settings']['default'], __( 'None', 'ipt_fsqm' ) ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Enter the default value of this element. This would be set if URL or meta parameter does not override. Empty value can also override the default value. But the value has to be set, i.e, either URL parameter or user metakey needs to be present.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( $name_prefix . '[settings][type]', __( 'Prefill Type', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->select( $name_prefix . '[settings][type]', $prefill_types, $data['settings']['type'] ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Set the type of the prefill value the field will get. It can be based on URL parameter or user meta key. Leave to None if you do not wish to prefill the value. For post meta based values, the post where this form is published through shortcode, would be considered. If you enter parameter like <code>10:key_value</code> then post meta <code>key_value</code> of post <code>10</code> would be considered, regardless of where the form is published.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( $name_prefix . '[settings][parameter]', __( 'Key Parameter', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( $name_prefix . '[settings][parameter]', $data['settings']['parameter'], __( 'Required', 'ipt_fsqm' ) ); ?>
			</td>
			<td><?php $this->ui->help( __( 'Enter the key parameter. In case of URL type value, <code>$_REQUEST[ $key ]</code> would be used. In case of User meta type value, the mentioned metakey would be used to retrieve the metavalue. It can not be empty or no value would be generated.', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<?php
	}

	/**
	 * A helper method to print score config for feedback elements
	 *
	 * @param      string  $name_prefix  The name prefix
	 * @param      string  $key          The key
	 * @param      array   $data         Configuration data
	 */
	protected function feedback_auto_score( $name_prefix, $key, $data ) {
		// Score types
		$score_types = [
			'contains' => __( 'Text Contains', 'ipt_fsqm' ),
			'starts' => __( 'Text Starts With', 'ipt_fsqm' ),
			'ends' => __( 'Text Ends With', 'ipt_fsqm' ),
			'equals' => __( 'Exactly Equals To', 'ipt_fsqm' ),
		];
		// Items
		$items = [];
		// Enabled
		$items[] = [
			'name' => $name_prefix . '[settings][autoscore][enabled]',
			'label' => __( 'Automatic Scoring', 'ipt_fsqm' ),
			'ui' => 'toggle',
			'param' => [ $name_prefix . '[settings][autoscore][enabled]', __( 'Yes', 'ipt_fsqm' ), __( 'No', 'ipt_fsqm' ), $data['settings']['autoscore']['enabled'], '1', false, true, [
				'condid' => 'ipt_fsqm_builder_fs_' . $key . '_as_type_wrap,ipt_fsqm_builder_fs_' . $key . '_as_text_wrap',
			] ],
			'help' => __( 'Enable automatic score calculation for this element.', 'ipt_fsqm' ),
		];
		// Type
		$items[] = [
			'name' => $name_prefix . '[settings][autoscore][type]',
			'label' => __( 'Assign Score if Text', 'ipt_fsqm' ),
			'ui' => 'select',
			'param' => [ $name_prefix . '[settings][autoscore][type]', $score_types, $data['settings']['autoscore']['type'] ],
			'id' => 'ipt_fsqm_builder_fs_' . $key . '_as_type_wrap',
			'help' => __( 'Enter the condition which will be used to check the relation between user text and your text.', 'ipt_fsqm' ),
		];
		// Text
		$items[] = [
			'name' => $name_prefix . '[settings][autoscore][text]',
			'label' => __( 'Check Against', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => [ $name_prefix . '[settings][autoscore][text]', $data['settings']['autoscore']['text'], __( 'Required', 'ipt_fsqm' ) ],
			'id' => 'ipt_fsqm_builder_fs_' . $key . '_as_text_wrap',
		];
		$this->ui->form_table( $items, false );
	}
}
