<?php
/**
 * IPT FSQM New Form
 *
 * Class for handling the New Form page under eForm
 *
 * @author Swashata <swashata4u@gmail.com>
 * @package eForm - WordPress Form Builder
 * @subpackage Admin\NewForm
 * @codeCoverageIgnore
 */
class IPT_FSQM_New_Form extends IPT_FSQM_Admin_Base {
	public function __construct() {
		$this->capability = 'manage_feedback';
		$this->action_nonce = 'ipt_fsqm_forms_nonce';

		parent::__construct();

		$this->icon = 'insert-template';
		$this->is_metabox = false;

		add_action( 'wp_ajax_eform_wizard_preview', array( $this, 'ajax_preview' ) );
	}

	/*==========================================================================
	 * SYSTEM METHODS
	 *========================================================================*/
	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'New Form', 'ipt_fsqm' ), __( 'New Form', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_new_form', array( &$this, 'index' ) );
		parent::admin_menu();
	}

	public function index() {
		$this->index_head( __( 'eForm <span class="ipt-icomoon-arrow-right2"></span> New Form', 'ipt_fsqm' ) );
		$templates = $this->scan_template_directory();
		$siteurl = parse_url( get_bloginfo( 'url' ) );
		$domain = $siteurl['host'];
		$senderemail = 'no-reply@' . $domain;
		$success_message = __( 'Thank you %NAME% for taking the quiz/survey/feedback.' . "\n" . 'We have received your answers. You can view it anytime from this link below:' . "\n" . '<a href="%TRACK_LINK%">%TRACK_LINK%</a>' . "\n" . 'We have also attached a copy of your submission.', 'ipt_fsqm' );

		$responsive_buttons = array(
			0 => array( '', '', 'small', 'secondary', 'normal', 'eform-nfw-res-large', 'button', array(), array(), '', 'desktop', 'before' ),
			1 => array( '', '', 'small', 'secondary', 'normal', 'eform-nfw-res-medium active', 'button', array(), array(), '', 'laptop2', 'before' ),
			2 => array( '', '', 'small', 'secondary', 'normal', 'eform-nfw-res-small', 'button', array(), array(), '', 'mobile', 'before' ),
		);
		?>
		<?php $this->ui->ajax_loader( false, 'ipt-eform-new-form-wizard-loader', array(), true, __( 'Loading', 'ipt_fsqm' ) ); ?>
<div id="ipt-eform-new-form-wizard" style="display: none;">
	<div id="ipt-eform-new-form-main-tab" class="ipt_uif_tabs">
		<ul>
			<li><a href="#ipt-eform-new-form-blank"><i class="ipt-icomoon-file"></i> <?php _e( 'Blank' ) ?></a></li>
			<?php foreach ( $templates as $form_cat_key => $form_cat_val ) : ?>
				<?php if ( empty( $form_cat_val['forms'] ) ) {
					continue;
				} ?>
				<li><a href="#ipt-eform-new-form-<?php echo esc_attr( $form_cat_key ); ?>"><i class="<?php echo $this->get_form_cat_icon( $form_cat_key ); ?>"></i> <?php echo $form_cat_val['label'] ?></a></li>
			<?php endforeach; ?>
		</ul>
		<div id="ipt-eform-new-form-blank"></div>
		<?php foreach ( $templates as $form_cat_key => $form_cat_val ) : ?>
			<?php if ( empty( $form_cat_val['forms'] ) ) {
				continue;
			} ?>
			<div id="ipt-eform-new-form-<?php echo esc_attr( $form_cat_key ); ?>" class="has-inner-tab">
				<div class="ipt_uif_tabs vertical eform-form-template">
					<ul>
						<?php foreach ( $form_cat_val['forms'] as $form_key => $form_name ) : ?>
							<li class="eform-form-template-li" data-form-cat-key="<?php echo esc_attr( $form_cat_key ); ?>" data-form-key="<?php echo esc_attr( $form_key ); ?>">
								<a href="#ipt-eform-new-form-<?php echo esc_attr( $form_cat_key ); ?>-<?php echo esc_attr( str_replace( '.', '', $form_key ) ); ?>"><?php echo $form_name; ?></a>
							</li>
						<?php endforeach; ?>
					</ul>
					<?php foreach ( $form_cat_val['forms'] as $form_key => $form_name ) : ?>
						<div id="ipt-eform-new-form-<?php echo esc_attr( $form_cat_key ); ?>-<?php echo esc_attr( str_replace( '.', '', $form_key )  ); ?>" class="eform-new-form-wizard-previewer prv-medium">
							<div class="eform-new-form-wizard-loader">
								<?php $this->ui->ajax_loader( false, '', array(), true, __( 'Loading Preview', 'ipt_fsqm' ) ); ?>
							</div>
							<?php $this->ui->buttons( $responsive_buttons, '', array( 'align-right', 'eform-new-form-wizard-responsive-btns' ) ); ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
		<div id="ipt-eform-new-form-values">
			<input type="hidden" name="eform[form_cat]" id="eform_form_cat" value="" />
			<input type="hidden" name="eform[form_key]" id="eform_form_key" value="" />
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( 'eform[name]', __( 'Form Name', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( 'eform[name]', '', __( 'Required', 'ipt_fsqm' ), 'large', 'normal', array(), false, false, array( 'required' => 'required' ) ); ?></td>
						<td><?php $this->ui->help( __( 'Enter the Name of the Form', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( 'eform[admin_email]', __( 'Admin Notification Email', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( 'eform[admin_email]', wp_get_current_user()->user_email, __( 'Required', 'ipt_fsqm' ), 'fit', 'normal', array(), false, false, array( 'required' => 'required', 'type' => 'email' ) ); ?></td>
						<td><?php $this->ui->help( __( 'Your email address where new form submission notifications would go.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( 'eform[user_email]', __( 'User Notification Sender\'s Email', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( 'eform[user_email]', $senderemail, __( 'Required', 'ipt_fsqm' ), 'fit', 'normal', array(), false, false, array( 'required' => 'required', 'type' => 'email' ) ); ?></td>
						<td><?php $this->ui->help( __( 'Enter the email which the user will see as the Sender\'s Email on the email he/she receives. It is recommended to use an email from the same domain. Otherwise it might end up into spams. Entering an empty email will stop the user notification service. So leave it empty to disable sending emails to users', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( 'eform[success_message]', __( 'Form Submission Success Message', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->textarea( 'eform[success_message]', $success_message, __( 'Required', 'ipt_fsqm' ), 'large', 'normal', array(), false, false, 5, array( 'required' => 'required' ) ); ?></td>
						<td><?php $this->ui->help( __( 'What to show when the form is submitted. ', 'ipt_fsqm' ) . sprintf( __( 'An updated list can always be found <a href="%1$s" target="_blank">here</a>.', 'ipt_fsqm' ), 'https://wpquark.com/kb/fsqm/form-submission-related/available-format-strings-custom-notifications/' ) ); ?></td>
					</tr>
				</tbody>
			</table>
			<?php $this->ui->button( __( 'Create Form', 'ipt_fsqm' ), 'eform[submit]', 'large', 'secondary', 'normal', array(), 'submit', true, array(), array(), '', 'plus' ); ?>
		</div>
	</div>
</div>
		<?php
		$this->index_foot( false );
	}

	/**
	 *
	 *
	 * @global wpdb $wpdb
	 */
	public function save_post( $check_referer = true ) {
		global $wpdb, $ipt_fsqm_info;

		parent::save_post( $check_referer );

		// Get variables
		$siteurl = parse_url( get_bloginfo( 'url' ) );
		$domain = $siteurl['host'];
		$senderemail = 'no-reply@' . $domain;
		$success_message = __( 'Thank you %NAME% for taking the quiz/survey/feedback.' . "\n" . 'We have received your answers. You can view it anytime from this link below:' . "\n" . '<a href="%TRACK_LINK%">%TRACK_LINK%</a>' . "\n" . 'We have also attached a copy of your submission.', 'ipt_fsqm' );
		$eform = @$_REQUEST['eform'];
		// Pass through wp_unslash
		$eform = wp_unslash( $eform );

		$eform = wp_parse_args( $eform, array(
			'form_cat' => '',
			'form_key' => '',
			'name' => __( 'Untitled', 'ipt_fsqm' ),
			'admin_email' => wp_get_current_user()->user_email,
			'user_email' => $senderemail,
			'success_message' => $success_message,
		) );

		// Create blank form
		$form = new IPT_FSQM_Form_Elements_Front();
		// Check if a template is used
		$filename = IPT_FSQM_Loader::$abs_path . '/templates/' . $eform['form_cat'] . '/' . $eform['form_key'];
		if ( is_file( $filename ) && file_exists( $filename ) ) {
			// Create the template form
			$formdata = maybe_unserialize( base64_decode( file_get_contents( $filename ) ) );
			if ( ! $formdata ) {
				wp_die( __( 'Cheatin&#8217; uh?' ) );
			}

			$form->settings = maybe_unserialize( $formdata['settings'] );
			$form->mcq = maybe_unserialize( $formdata['mcq'] );
			$form->pinfo = maybe_unserialize( $formdata['pinfo'] );
			$form->freetype = maybe_unserialize( $formdata['freetype'] );
			$form->design = maybe_unserialize( $formdata['design'] );
			$form->layout = maybe_unserialize( $formdata['layout'] );
			$form->name = $formdata['name'];
			$form->type = $formdata['type'];
			$form->form_id = -9999;
			$form->compat_layout();
		}
		// Change admin provided settings
		$form->name = $eform['name'];
		$form->settings['user']['notification_email'] = $eform['user_email'];
		$form->settings['admin']['email'] = $eform['admin_email'];
		$form->settings['submission']['success_message'] = $eform['success_message'];

		// Insert
		// All set, now import it
		$wpdb->insert( $ipt_fsqm_info['form_table'], array(
			'name'     => $form->name,
			'settings' => maybe_serialize( $form->settings ),
			'layout'   => maybe_serialize( $form->layout ),
			'design'   => maybe_serialize( $form->design ),
			'mcq'      => maybe_serialize( $form->mcq ),
			'freetype' => maybe_serialize( $form->freetype ),
			'pinfo'    => maybe_serialize( $form->pinfo ),
			'type'     => $form->type,
			'category' => 0,
		), '%s' );
		// Get form ID
		$new_form_id = $wpdb->insert_id;
		// Redirect
		wp_redirect( add_query_arg( array( 'form_id' => $new_form_id ), 'admin.php?page=ipt_fsqm_all_forms&action=edit' ) );
		die();
	}

	public function on_load_page() {
		parent::on_load_page();

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);
	}

	/*==========================================================================
	 * Form Wizard Helpers
	 *========================================================================*/

	private function get_category_name_from_dir( $label ) {
		$label = preg_replace( '/^([0-9]+\-)(.*)/', '$2', $label );
		$label = str_replace( '-Form', '', $label );
		return str_replace( '-', ' ', $label );
	}

	private function get_form_name_from_file( $form ) {
		return str_replace( array( 'eForm', '-', '.txt' ), array( ' ', ' ', '' ), $form );
	}

	private function scan_template_directory() {
		$templates = array();
		$path = IPT_FSQM_Loader::$abs_path . '/templates';
		$scan = @scandir( $path );
		if ( ! $scan ) {
			return $templates;
		}
		foreach ( $scan as $dir ) {
			if ( '.' != $dir && '..' != $dir && @is_dir( $path . '/' . $dir ) ) {
				$category_name = $this->get_category_name_from_dir( $dir );
				$templates[ $dir ] = array(
					'label' => trim( $category_name ),
					'forms' => array(),
				);
				$forms = @scandir( $path . '/' . $dir );
				if ( ! $forms ) {
					continue;
				}
				foreach ( $forms as $form ) {
					if ( '.' != $form && '..' != $form && @is_file( $path . '/' . $dir . '/' . $form ) ) {
						$formname = $this->get_form_name_from_file( $form );
						$templates[ $dir ]['forms'][ $form ] = trim( $formname );
					}
				}
			}
		}
		return $templates;
	}


	private function get_form_cat_icon( $cat_key ) {
		$cat_key = preg_replace( '/^([0-9]+\-)(.*)/', '$2', $cat_key );
		$icon = 'ipt-icomoon-file-text-o';
		switch ( $cat_key ) {
			case 'Demo-Form':
				$icon = 'ipt-icomoon-file4';
				break;
			case 'Feedback-Form':
				$icon = 'ipt-icomoon-envelope';
				break;
			case 'Integrations-Form':
				$icon = 'ipt-icomoon-plus-square';
				break;
			case 'Mathematical-Form':
				$icon = 'ipt-icomoon-calculator';
				break;
			case 'Order-Form':
			case 'Payment-Form':
				$icon = 'ipt-icomoon-shopping-cart';
				break;
			case 'Quiz-Form':
				$icon = 'ipt-icomoon-certificate';
				break;
			case 'Survey-Form':
				$icon = 'ipt-icomoon-signup';
				break;
			case 'Element-Demo-Form':
				$icon = 'ipt-icomoon-puzzle-piece';
				break;
		}
		return apply_filters( 'ipt_eform_new_form_wizard_tab_icons', $icon, $cat_key );
	}

	public function ajax_preview() {
		if ( ! current_user_can( 'manage_feedback' ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}

		$form_cat = @$_REQUEST['formcat'];
		$form_key = @$_REQUEST['formkey'];
		$filename = IPT_FSQM_Loader::$abs_path . '/templates/' . $form_cat . '/' . $form_key;
		if ( ! is_file( $filename ) || ! file_exists( $filename ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}
		$formdata = maybe_unserialize( base64_decode( file_get_contents( $filename ) ) );
		if ( ! $formdata ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}
		$form = new IPT_FSQM_Form_Elements_Front();
		$form->settings = maybe_unserialize( $formdata['settings'] );
		$form->mcq = maybe_unserialize( $formdata['mcq'] );
		$form->pinfo = maybe_unserialize( $formdata['pinfo'] );
		$form->freetype = maybe_unserialize( $formdata['freetype'] );
		$form->design = maybe_unserialize( $formdata['design'] );
		$form->layout = maybe_unserialize( $formdata['layout'] );
		$form->name = $formdata['name'];
		$form->type = $formdata['type'];
		$form->form_id = -9999;
		$form->compat_layout();
		?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<title><?php echo $form->name; ?></title>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php // wp_enqueue_style( 'ipt-eform-material-font', 'https://fonts.googleapis.com/css?family=Noto+Sans|Roboto:300,400,400i,700', array(), IPT_FSQM_Loader::$version ); ?>
	<style type="text/css">
	/* =Reset
	-------------------------------------------------------------- */

	html, body, div, span, applet, object, iframe, h1, h2, h3, h4, h5, h6, p, blockquote, pre, a, abbr, acronym, address, big, cite, code, del, dfn, em, img, ins, kbd, q, s, samp, small, strike, strong, sub, sup, tt, var, b, u, i, center, dl, dt, dd, ol, ul, li, fieldset, form, label, legend, table, caption, tbody, tfoot, thead, tr, th, td, article, aside, canvas, details, embed, figure, figcaption, footer, header, hgroup, menu, nav, output, ruby, section, summary, time, mark, audio, video {
		margin: 0;
		padding: 0;
		border: 0;
		font-size: 100%;
		vertical-align: baseline;
	}
	body {
		line-height: 1;
	}
	html body:before,
	html body:after {
		display: none;
	}
	ol,
	ul {
		list-style: none;
	}
	blockquote,
	q {
		quotes: none;
	}
	blockquote:before,
	blockquote:after,
	q:before,
	q:after {
		content: '';
		content: none;
	}
	table {
		border-collapse: collapse;
		border-spacing: 0;
	}
	caption,
	th,
	td {
		font-weight: normal;
		text-align: left;
	}
	h1,
	h2,
	h3,
	h4,
	h5,
	h6 {
		clear: both;
	}
	html {
		overflow-y: auto;
		font-size: 100%;
		-webkit-text-size-adjust: 100%;
		-ms-text-size-adjust: 100%;
		margin-top: 0 !important;
	}
	a:focus {
		outline: thin dotted;
	}
	article,
	aside,
	details,
	figcaption,
	figure,
	footer,
	header,
	hgroup,
	nav,
	section {
		display: block;
	}
	audio,
	canvas,
	video {
		display: inline-block;
	}
	audio:not([controls]) {
		display: none;
	}
	del {
		color: #333;
	}
	ins {
		background: #fff9c0;
		text-decoration: none;
	}
	hr {
		background-color: #ccc;
		border: 0;
		height: 1px;
		margin: 24px;
		margin-bottom: 1.714285714rem;
	}
	sub,
	sup {
		font-size: 75%;
		line-height: 0;
		position: relative;
		vertical-align: baseline;
	}
	sup {
		top: -0.5em;
	}
	sub {
		bottom: -0.25em;
	}
	small {
		font-size: smaller;
	}
	img {
		border: 0;
		-ms-interpolation-mode: bicubic;
		max-width: 100%;
		height: auto;
	}
	h1, h2, h3, h4, h5, h6, p, ul, ol {
		line-height: 1.3;
		margin: 0 0 20px 0;
	}
	h1, h2, h3, h4, h5, h6 {
		font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
		font-weight: normal;
		font-style: normal;
	}
	h1 {
		font-size: 2em;
	}
	h2 {
		font-size: 1.8em;
	}
	h3 {
		font-size: 1.6em;
	}
	h4 {
		font-size: 1.4em;
	}
	h5 {
		font-size: 1.2em;
	}
	h6 {
		font-size: 1em;
	}
	html {
		overflow-y: auto;
	}
	ul {
		list-style-type: disc;
		list-style-position: inside;
	}
	ol {
		list-style-type: decimal;
		list-style-position: inside;
	}
	body {
		background-color: #fff;
		background-image: none;
		font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
		font-weight: normal;
		font-style: normal;
		font-size: 12px;
		color: #333;
		min-width: 320px;
	}
	#fsqm_form {
		max-width: 1200px;
		padding: 20px;
		margin: 0 auto;
	}
	html {
		margin-top: 0 !important;
	}
	</style>
	<?php
		wp_print_styles();
		wp_print_scripts();
	?>
</head>
<body <?php body_class( 'ipt_uif_common' ); ?>>
	<div id="fsqm_form">
		<?php $form->show_form(); ?>
	</div>
	<?php
		wp_print_styles();
		wp_print_scripts();
	?>
</body>
</html>
		<?php
		die();
	}
}
