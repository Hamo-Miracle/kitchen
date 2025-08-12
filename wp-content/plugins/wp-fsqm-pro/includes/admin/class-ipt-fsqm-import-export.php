<?php
/**
 * IPT FSQM Import Export
 *
 * Class for handling the Import Export page under eForm
 *
 * @author Swashata <swashata4u@gmail.com>
 * @package eForm - WordPress Form Builder
 * @subpackage Admin\ImportExport
 * @codeCoverageIgnore
 */
class IPT_FSQM_Import_Export extends IPT_FSQM_Admin_Base {
	public function __construct() {
		$this->capability = 'manage_feedback';
		$this->action_nonce = 'ipt_fsqm_import_export_nonce';

		parent::__construct();

		$this->icon = 'code';
		add_action( 'wp_ajax_ipt_fsqm_generate_export', array( $this, 'generate_export' ) );
		add_action( 'wp_ajax_ipt_fsqm_generate_import', array( $this, 'generate_import' ) );
	}

	/*==========================================================================
	 * SYSTEM METHODS
	 *========================================================================*/

	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'Import & Export Forms - eForm', 'ipt_fsqm' ), __( 'Import/Export Forms', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_import_export', array( $this, 'index' ) );
		parent::admin_menu();
	}

	public function index() {
		$this->index_head( __( 'eForm <span class="ipt-icomoon-arrow-right2"></span> Import/Export Forms', 'ipt_fsqm' ), false );
		wp_nonce_field( 'ipt_fsqm_import_export_nonce', 'ipt_fsqm_ie_nonce' );
		$this->ui->iconbox( __( 'Generate Export Code', 'ipt_fsqm' ), array( $this, 'export_code_html' ), 'copy' );
		$this->ui->iconbox( __( 'Import Form from Code', 'ipt_fsqm' ), array( $this, 'import_code_html' ), 'paste2' );
		$this->index_foot( false );
	}

	public function on_load_page() {
		get_current_screen()->add_help_tab( array(
			'id' => 'overview',
			'title' => __( 'Overview', 'ipt_fsqm' ),
			'content' =>
			'<p>' . __( 'This screen provides tools to export and/or import forms among different sites of yours or friends.', 'ipt_fsqm' ) . '<p>' .
			'<p>' . __( 'Using the export code is pretty easy. You are presented with two options:', 'ipt_fsqm' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<strong>Generate Export Code:</strong> Simply select the form and hit Generate Code button. It will give you the export code of the form. Copy the code and keep it handy somewhere.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>Import Form from Code:</strong> Here you can insert previously generated code to recreate the form. Enter form name (if you wish to override the name) and the code in respected fields and hit the Import from Code button. It will automatically generate the form. It will also notify you should any problem is found.', 'ipt_fsqm' ) . '</li>' .
			'</ul>' .
			'<p>' . __( 'Also as a bonus, click on the help icon beside <strong>Enter Export Code</strong> and you will get an amazing form.', 'ipt_fsqm' ) . '</p>',
		) );

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'ipt_fsqm' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);
	}

	/*==========================================================================
	 * Form HTML
	 *========================================================================*/

	public function export_code_html() {
		global $wpdb, $ipt_fsqm_info;
		$forms = $wpdb->get_results( "SELECT id, name FROM {$ipt_fsqm_info['form_table']} ORDER BY id DESC" );
		$form_select = array();
		$form_select[] = array(
			'label' => __( '--Please select a form--', 'ipt_fsqm' ),
			'value' => '',
		);
		foreach ( $forms as $form ) {
			$form_select[] = array(
				'label' => $form->name,
				'value' => $form->id,
			);
		}
		?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#ipt_fsqm_export_form').on('submit', function(e) {
			// Prevent form submission
			e.preventDefault();

			// Init the variables
			var self = $(this),
			button = self.find('#export_code_generate'),
			ajax_loader = self.find('#ipt_fsqm_export_code_generator_ajax'),
			textarea = self.find('#export_code'),
			tr_to_hide = self.find('.ipt_fsqm_tr_hide'),
			ajax_data = {
				form_id: self.find('#form_id').val(),
				_wpnonce: $('#ipt_fsqm_ie_nonce').val(),
				action: 'ipt_fsqm_generate_export'
			};

			// Hide things first
			tr_to_hide.fadeOut('fast');

			// Disable the submit button
			button.prop('disabled', true);

			// Show the ajax loader
			ajax_loader.fadeIn('fast');

			$.get(ajaxurl, ajax_data, function(data) {
				// Get the message box
				var msg_tr = self.find('.ipt_fsqm_tr_hide.msg_error'),
				// Get the textarea tr
				txt_tr = self.find('.ipt_fsqm_tr_hide.export_code');

				if ( data.error ) { // There is an error, so show the error
					msg_tr.find('.ipt_uif_box.red').html('<p><strong>Error</strong>: ' + data.code + ';</p>');
					msg_tr.fadeIn('fast');
				} else { // It was successful, so show the code
					textarea.val(data.code);
					txt_tr.fadeIn('fast');
				}
			}, 'json').always(function() {
				// Enable submit button
				button.prop('disabled', false);
				// Show the ajax loader
				ajax_loader.fadeOut('fast');
			}).fail(function(jqXHR, textStatus, errorThrown) {
				// Show the message
				var msg_tr = self.find('.ipt_fsqm_tr_hide.msg_error');
				msg_tr.find('.ipt_uif_box.red').html('<p><strong>Ajax Error</strong>: Status: ' + textStatus + '; Error: ' + errorThrown + ';</p>');
				msg_tr.fadeIn('fast');
			});
		});
	});
</script>
<form action="" method="get" id="ipt_fsqm_export_form">
	<table class="form-table">
		<tbody>
			<tr>
				<th><?php $this->ui->generate_label( 'form_id', __( 'Select a form', 'ipt_fsqm' ) ); ?></th>
				<td>
					<?php $this->ui->select( 'form_id', $form_select, '' ); ?>
				</td>
				<td>
					<?php $this->ui->help( __( 'Please select a form for which you want to generate the export code.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
			<tr style="display: none" class="ipt_fsqm_tr_hide export_code">
				<td colspan="3">
					<?php $this->ui->msg_okay( __( 'Please copy the code below', 'ipt_fsqm' ) ); ?>
					<?php $this->ui->textarea( 'export_code', '', '', 'fit', 'normal', array( 'code' ), false, false, 10 ); ?>
				</td>
			</tr>
			<tr class="ipt_fsqm_tr_hide msg_error" style="display: none">
				<td colspan="3">
					<?php $this->ui->msg_error( '' ); ?>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="ipt_uif_float_left">
		<?php $this->ui->button( __( 'Generate Code', 'ipt_fsqm' ), 'export_code_generate', 'large', 'primary', 'normal', array(), 'submit' ); ?>
	</div>
	<div class="ipt_uif_float_left">
		<?php $this->ui->ajax_loader( true, 'ipt_fsqm_export_code_generator_ajax', array(), true, __( 'Generating Code', 'ipt_fsqm' ) ); ?>
	</div>
	<?php $this->ui->clear(); ?>
</form>
		<?php
	}

	public function import_code_html() {
		?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#ipt_fsqm_import_form').on('submit', function(e) {
			// Prevent the submit
			e.preventDefault();

			// Get all variables
			var self = $(this),
			button = self.find('#import_code_generate'),
			ajax_loader = self.find('#ipt_fsqm_import_code_generator_ajax'),
			divs_to_hide = self.find('.hide_div'),
			okay_box = self.find('#ipt_fsqm_import_result_okay'),
			error_box = self.find('#ipt_fsqm_import_result_error'),
			ajax_data = {
				form_name: self.find('#form_name').val(),
				form_code: self.find('#form_code').val(),
				_wpnonce: $('#ipt_fsqm_ie_nonce').val(),
				action: 'ipt_fsqm_generate_import'
			};

			// Hide things first
			divs_to_hide.fadeOut('fast');

			// Disable the submit button
			button.prop('disabled', true);

			// Show the ajax loader
			ajax_loader.fadeIn('fast');

			// Post the data
			$.post(ajaxurl, ajax_data, function( data ) {
				// Get the okay box
				if ( data.error ) {
					error_box.find('.ipt_uif_message').html('<p>' + data.code + '</p>');
					error_box.fadeIn('fast');
				} else {
					okay_box.find('.ipt_uif_message').html('<p>' + data.code + '</p>');
					okay_box.fadeIn('fast');
				}
			}).always(function() {
				// Enable submit button
				button.prop('disabled', false);
				// Hide the ajax loader
				ajax_loader.fadeOut('fast');
				// Reset the values
				self.find('#form_name').val('');
				self.find('#form_code').val('');
			}).fail(function(jqXHR, textStatus, errorThrown) {
				error_box.find('.ipt_uif_message').html('<p><strong>Ajax Error</strong>: Status: ' + textStatus + '; Error: ' + errorThrown + ';</p>');
				error_box.fadeIn('fast');
			});
		});
	});
</script>
<form action="" method="get" id="ipt_fsqm_import_form">
	<table class="form-table">
		<tbody>
			<tr>
				<th>
					<?php $this->ui->generate_label( 'form_name', __( 'Enter Form Name', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->text( 'form_name', '', __( 'Leave empty to use from the code', 'ipt_fsqm' ), 'large' ); ?>
				</td>
				<td>
					<?php $this->ui->help( __( 'You can override the form name from the code. Leaving it empty will simply use the form name available on the import code.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
			<tr>
				<th>
					<?php $this->ui->generate_label( 'form_code', __( 'Enter Export Code', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->textarea( 'form_code', '', __( 'Paste the export code', 'ipt_fsqm' ), 'fit', 'normal', array( 'code' ), false, false, 10 ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
					<p><?php _e( 'Please copy paste the export code here. Try the following for fun:', 'ipt_fsqm' ); ?></p>
					<code style="display: block; height: 200px; overflow: auto;">
<pre><?php $this->print_sample_import_code(); ?></pre>
					</code>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="ipt_uif_float_left">
		<?php $this->ui->button( __( 'Import from Code', 'ipt_fsqm' ), 'import_code_generate', 'large', 'primary', 'normal', array(), 'submit' ); ?>
	</div>
	<div class="ipt_uif_float_left">
		<?php $this->ui->ajax_loader( true, 'ipt_fsqm_import_code_generator_ajax', array(), true, __( 'Importing Form', 'ipt_fsqm' ) ); ?>
	</div>
	<?php $this->ui->clear(); ?>
	<div class="hide_div" style="display: none;" id="ipt_fsqm_import_result_okay">
		<?php $this->ui->msg_okay( '' ); ?>
	</div>
	<div class="hide_div" style="display: none;" id="ipt_fsqm_import_result_error">
		<?php $this->ui->msg_error( '' ); ?>
	</div>
</form>
		<?php
	}

	public function print_sample_import_code() {
		?>
YToxMDp7czoyOiJpZCI7czoyOiI3NSI7czo0OiJuYW1lIjtzOjExOiJSZXN1bWUgRm9ybSI7czo4
OiJzZXR0aW5ncyI7czoyMTkxOiJhOjEwOntzOjc6ImdlbmVyYWwiO2E6Njp7czoxMDoidGVybXNf
cGFnZSI7czoxOiIwIjtzOjEyOiJ0ZXJtc19waHJhc2UiO3M6MTgwOiJCeSBzdWJtaXR0aW5nIHRo
aXMgZm9ybSwgeW91IGhlcmVieSBhZ3JlZSB0byBhY2NlcHQgb3VyIDxhIGhyZWY9IiUxJHMiIHRh
cmdldD0iX2JsYW5rIj5UZXJtcyAmIENvbmRpdGlvbnM8L2E+LiBZb3VyIElQIGFkZHJlc3MgPHN0
cm9uZz4lMiRzPC9zdHJvbmc+IHdpbGwgYmUgc3RvcmVkIGluIG91ciBkYXRhYmFzZS4iO3M6MTM6
ImNvbW1lbnRfdGl0bGUiO3M6MjE6IkFkbWluaXN0cmF0b3IgUmVtYXJrcyI7czoxNToiZGVmYXVs
dF9jb21tZW50IjtzOjEwOiJQcm9jZXNzaW5nIjtzOjg6ImNhbl9lZGl0IjtiOjE7czo5OiJlZGl0
X3RpbWUiO3M6MDoiIjt9czo0OiJ1c2VyIjthOjY6e3M6MTY6Im5vdGlmaWNhdGlvbl9zdWIiO3M6
MjU6IldlIGhhdmUgZ290IHlvdXIgYW5zd2Vycy4iO3M6MTY6Im5vdGlmaWNhdGlvbl9tc2ciO3M6
MTk1OiJUaGFuayB5b3UgJU5BTUUlIGZvciB0YWtpbmcgdGhlIHF1aXovc3VydmV5L2ZlZWRiYWNr
Lg0KV2UgaGF2ZSByZWNlaXZlZCB5b3VyIGFuc3dlcnMuIFlvdSBjYW4gdmlldyBpdCBhbnl0aW1l
IGZyb20gdGhpcyBsaW5rIGJlbG93Og0KJVRSQUNLX0xJTkslDQpIZXJlIGlzIGEgY29weSBvZiB5
b3VyIHN1Ym1pc3Npb246DQolU1VCTUlTU0lPTiUiO3M6MTc6Im5vdGlmaWNhdGlvbl9mcm9tIjtz
OjM0OiJpUGFuZWxUaGVtZXMgTG9jYWxob3N0IERldmVsb3BtZW50IjtzOjE4OiJub3RpZmljYXRp
b25fZW1haWwiO3M6MjQ6InN3YXNoYXRhQGxvY2FsaG9zdC5sb2NhbCI7czo0OiJzbXRwIjtiOjA7
czoxMToic210cF9jb25maWciO2E6NTp7czo4OiJlbmNfdHlwZSI7czozOiJzc2wiO3M6NDoiaG9z
dCI7czoxNDoic210cC5nbWFpbC5jb20iO3M6NDoicG9ydCI7czozOiI0NjUiO3M6ODoidXNlcm5h
bWUiO3M6NToiYWRtaW4iO3M6ODoicGFzc3dvcmQiO3M6NDQ6Im04aFJnY1ZTcEpzMHdGbXlXbmpM
TTFGbHRHT0hIdGd3OVJnLzBnMDlXS0U9Ijt9fXM6NToiYWRtaW4iO2E6Mzp7czo1OiJlbWFpbCI7
czoyNDoic3dhc2hhdGFAbG9jYWxob3N0LmxvY2FsIjtzOjE1OiJtYWlsX3N1Ym1pc3Npb24iO2I6
MDtzOjE0OiJzZW5kX2Zyb21fdXNlciI7YjowO31zOjEwOiJsaW1pdGF0aW9uIjthOjM6e3M6MTE6
ImVtYWlsX2xpbWl0IjtzOjE6IjAiO3M6ODoiaXBfbGltaXQiO3M6MToiMCI7czoxMDoidXNlcl9s
aW1pdCI7czoxOiIwIjt9czoxMzoidHlwZV9zcGVjaWZpYyI7YTozOntzOjEwOiJwYWdpbmF0aW9u
IjthOjE6e3M6MTc6InNob3dfcHJvZ3Jlc3NfYmFyIjtiOjE7fXM6MzoidGFiIjthOjE6e3M6MTI6
ImNhbl9wcmV2aW91cyI7YjoxO31zOjY6Im5vcm1hbCI7YToxOntzOjc6IndyYXBwZXIiO2I6MDt9
fXM6NzoiYnV0dG9ucyI7YTozOntzOjQ6Im5leHQiO3M6NDoiTmV4dCI7czo0OiJwcmV2IjtzOjg6
IlByZXZpb3VzIjtzOjY6InN1Ym1pdCI7czo2OiJTdWJtaXQiO31zOjEwOiJzdWJtaXNzaW9uIjth
OjM6e3M6MTM6InByb2Nlc3NfdGl0bGUiO3M6MjI6IlByb2Nlc3NpbmcgeW91IHJlcXVlc3QiO3M6
MTM6InN1Y2Nlc3NfdGl0bGUiO3M6Mjg6IllvdXIgZm9ybSBoYXMgYmVlbiBzdWJtaXR0ZWQiO3M6
MTU6InN1Y2Nlc3NfbWVzc2FnZSI7czozMzoiVGhhbmsgeW91IGZvciBnaXZpbmcgeW91ciBhbnN3
ZXJzIjt9czoxMToicmVkaXJlY3Rpb24iO2E6NTp7czo0OiJ0eXBlIjtzOjQ6Im5vbmUiO3M6NToi
ZGVsYXkiO3M6NDoiMTAwMCI7czozOiJ0b3AiO2I6MDtzOjM6InVybCI7czoxMToiJVRSQUNLQkFD
SyUiO3M6NToic2NvcmUiO2E6MDp7fX1zOjc6InJhbmtpbmciO2E6Mzp7czo3OiJlbmFibGVkIjti
OjA7czo1OiJ0aXRsZSI7czoxMToiRGVzaWduYXRpb24iO3M6NToicmFua3MiO2E6MDp7fX1zOjU6
InRoZW1lIjthOjQ6e3M6ODoidGVtcGxhdGUiO3M6NzoiZGVmYXVsdCI7czo0OiJsb2dvIjtzOjA6
IiI7czoxMjoiY3VzdG9tX3N0eWxlIjtiOjA7czo1OiJzdHlsZSI7YTo1OntzOjk6ImhlYWRfZm9u
dCI7czo2OiJvc3dhbGQiO3M6OToiYm9keV9mb250IjtzOjY6InJvYm90byI7czoxNDoiYmFzZV9m
b250X3NpemUiO3M6MjoiMTIiO3M6MTQ6ImhlYWRfZm9udF90eXBvIjthOjI6e3M6NDoiYm9sZCI7
YjowO3M6NjoiaXRhbGljIjtiOjA7fXM6NjoiY3VzdG9tIjtzOjA6IiI7fX19IjtzOjY6ImxheW91
dCI7czoyMDQ5OiJhOjI6e2k6MDthOjc6e3M6NDoidHlwZSI7czozOiJ0YWIiO3M6NToidGl0bGUi
O3M6ODoiSWRlbnRpZnkiO3M6ODoic3VidGl0bGUiO3M6ODoieW91cnNlbGYiO3M6MTE6ImRlc2Ny
aXB0aW9uIjtzOjA6IiI7czo2OiJtX3R5cGUiO3M6NjoibGF5b3V0IjtzOjg6ImVsZW1lbnRzIjth
OjE4OntpOjA7YTozOntzOjY6Im1fdHlwZSI7czo2OiJkZXNpZ24iO3M6NDoidHlwZSI7czo4OiJj
b2xfaGFsZiI7czozOiJrZXkiO3M6MToiMCI7fWk6MTthOjM6e3M6NjoibV90eXBlIjtzOjY6ImRl
c2lnbiI7czo0OiJ0eXBlIjtzOjg6ImNvbF9oYWxmIjtzOjM6ImtleSI7czoxOiIxIjt9aToyO2E6
Mzp7czo2OiJtX3R5cGUiO3M6MzoibWNxIjtzOjQ6InR5cGUiO3M6ODoiY2hlY2tib3giO3M6Mzoi
a2V5IjtzOjE6IjEiO31pOjM7YTozOntzOjY6Im1fdHlwZSI7czozOiJtY3EiO3M6NDoidHlwZSI7
czo2OiJzbGlkZXIiO3M6Mzoia2V5IjtzOjE6IjIiO31pOjQ7YTozOntzOjY6Im1fdHlwZSI7czoz
OiJtY3EiO3M6NDoidHlwZSI7czo2OiJzbGlkZXIiO3M6Mzoia2V5IjtzOjE6IjMiO31pOjU7YToz
OntzOjY6Im1fdHlwZSI7czozOiJtY3EiO3M6NDoidHlwZSI7czo2OiJ0b2dnbGUiO3M6Mzoia2V5
IjtzOjI6IjExIjt9aTo2O2E6Mzp7czo2OiJtX3R5cGUiO3M6MzoibWNxIjtzOjQ6InR5cGUiO3M6
MTA6InN0YXJyYXRpbmciO3M6Mzoia2V5IjtzOjE6IjQiO31pOjc7YTozOntzOjY6Im1fdHlwZSI7
czozOiJtY3EiO3M6NDoidHlwZSI7czo2OiJ0b2dnbGUiO3M6Mzoia2V5IjtzOjI6IjEyIjt9aTo4
O2E6Mzp7czo2OiJtX3R5cGUiO3M6MzoibWNxIjtzOjQ6InR5cGUiO3M6NjoidG9nZ2xlIjtzOjM6
ImtleSI7czoxOiI1Ijt9aTo5O2E6Mzp7czo2OiJtX3R5cGUiO3M6ODoiZnJlZXR5cGUiO3M6NDoi
dHlwZSI7czoxNDoiZmVlZGJhY2tfc21hbGwiO3M6Mzoia2V5IjtzOjE6IjMiO31pOjEwO2E6Mzp7
czo2OiJtX3R5cGUiO3M6ODoiZnJlZXR5cGUiO3M6NDoidHlwZSI7czoxNDoiZmVlZGJhY2tfc21h
bGwiO3M6Mzoia2V5IjtzOjE6IjQiO31pOjExO2E6Mzp7czo2OiJtX3R5cGUiO3M6ODoiZnJlZXR5
cGUiO3M6NDoidHlwZSI7czoxNDoiZmVlZGJhY2tfc21hbGwiO3M6Mzoia2V5IjtzOjE6IjUiO31p
OjEyO2E6Mzp7czo2OiJtX3R5cGUiO3M6ODoiZnJlZXR5cGUiO3M6NDoidHlwZSI7czoxNDoiZmVl
ZGJhY2tfbGFyZ2UiO3M6Mzoia2V5IjtzOjE6IjYiO31pOjEzO2E6Mzp7czo2OiJtX3R5cGUiO3M6
MzoibWNxIjtzOjQ6InR5cGUiO3M6NjoibWF0cml4IjtzOjM6ImtleSI7czoxOiI2Ijt9aToxNDth
OjM6e3M6NjoibV90eXBlIjtzOjM6Im1jcSI7czo0OiJ0eXBlIjtzOjU6InJhbmdlIjtzOjM6Imtl
eSI7czoxOiI4Ijt9aToxNTthOjM6e3M6NjoibV90eXBlIjtzOjM6Im1jcSI7czo0OiJ0eXBlIjtz
OjU6InJhbmdlIjtzOjM6ImtleSI7czoxOiI5Ijt9aToxNjthOjM6e3M6NjoibV90eXBlIjtzOjM6
Im1jcSI7czo0OiJ0eXBlIjtzOjY6InRvZ2dsZSI7czozOiJrZXkiO3M6MjoiMTMiO31pOjE3O2E6
Mzp7czo2OiJtX3R5cGUiO3M6MzoibWNxIjtzOjQ6InR5cGUiO3M6NToicmFuZ2UiO3M6Mzoia2V5
IjtzOjI6IjEwIjt9fXM6NDoiaWNvbiI7czo1OiI1NzUwNCI7fWk6MTthOjc6e3M6NDoidHlwZSI7
czozOiJ0YWIiO3M6NToidGl0bGUiO3M6NjoiVXBsb2FkIjtzOjg6InN1YnRpdGxlIjtzOjExOiJ5
b3VyIHJlc3VtZSI7czoxMToiZGVzY3JpcHRpb24iO3M6MDoiIjtzOjY6Im1fdHlwZSI7czo2OiJs
YXlvdXQiO3M6ODoiZWxlbWVudHMiO2E6Mzp7aTowO2E6Mzp7czo2OiJtX3R5cGUiO3M6ODoiZnJl
ZXR5cGUiO3M6NDoidHlwZSI7czo2OiJ1cGxvYWQiO3M6Mzoia2V5IjtzOjE6IjAiO31pOjE7YToz
OntzOjY6Im1fdHlwZSI7czo4OiJmcmVldHlwZSI7czo0OiJ0eXBlIjtzOjY6InVwbG9hZCI7czoz
OiJrZXkiO3M6MToiMSI7fWk6MjthOjM6e3M6NjoibV90eXBlIjtzOjg6ImZyZWV0eXBlIjtzOjQ6
InR5cGUiO3M6NjoidXBsb2FkIjtzOjM6ImtleSI7czoxOiIyIjt9fXM6NDoiaWNvbiI7czo1OiI1
NzQyOCI7fX0iO3M6NjoiZGVzaWduIjtzOjYxMToiYToyOntpOjA7YTo2OntzOjQ6InR5cGUiO3M6
ODoiY29sX2hhbGYiO3M6NToidGl0bGUiO3M6MDoiIjtzOjg6InN1YnRpdGxlIjtzOjA6IiI7czox
MToiZGVzY3JpcHRpb24iO3M6MDoiIjtzOjY6Im1fdHlwZSI7czo2OiJkZXNpZ24iO3M6ODoiZWxl
bWVudHMiO2E6Mjp7aTowO2E6Mzp7czo2OiJtX3R5cGUiO3M6NToicGluZm8iO3M6NDoidHlwZSI7
czo2OiJmX25hbWUiO3M6Mzoia2V5IjtzOjE6IjAiO31pOjE7YTozOntzOjY6Im1fdHlwZSI7czo1
OiJwaW5mbyI7czo0OiJ0eXBlIjtzOjY6ImxfbmFtZSI7czozOiJrZXkiO3M6MToiMSI7fX19aTox
O2E6Njp7czo0OiJ0eXBlIjtzOjg6ImNvbF9oYWxmIjtzOjU6InRpdGxlIjtzOjA6IiI7czo4OiJz
dWJ0aXRsZSI7czowOiIiO3M6MTE6ImRlc2NyaXB0aW9uIjtzOjA6IiI7czo2OiJtX3R5cGUiO3M6
NjoiZGVzaWduIjtzOjg6ImVsZW1lbnRzIjthOjI6e2k6MDthOjM6e3M6NjoibV90eXBlIjtzOjU6
InBpbmZvIjtzOjQ6InR5cGUiO3M6NToiZW1haWwiO3M6Mzoia2V5IjtzOjE6IjIiO31pOjE7YToz
OntzOjY6Im1fdHlwZSI7czozOiJtY3EiO3M6NDoidHlwZSI7czo2OiJzbGlkZXIiO3M6Mzoia2V5
IjtzOjE6IjAiO319fX0iO3M6MzoibWNxIjtzOjc4ODU6ImE6MTM6e2k6MDthOjg6e3M6NDoidHlw
ZSI7czo2OiJzbGlkZXIiO3M6NToidGl0bGUiO3M6MzoiQWdlIjtzOjEwOiJ2YWxpZGF0aW9uIjth
OjA6e31zOjg6InN1YnRpdGxlIjtzOjc6ImluIHllYXIiO3M6MTE6ImRlc2NyaXB0aW9uIjtzOjA6
IiI7czoxMToiY29uZGl0aW9uYWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6MDtzOjY6InN0YXR1cyI7
YjowO3M6NjoiY2hhbmdlIjtiOjA7czo1OiJsb2dpYyI7YTowOnt9fXM6NjoibV90eXBlIjtzOjM6
Im1jcSI7czo4OiJzZXR0aW5ncyI7YTo0OntzOjM6Im1pbiI7czoyOiIxOCI7czozOiJtYXgiO3M6
MjoiNjAiO3M6NDoic3RlcCI7aToxO3M6MTA6InNob3dfY291bnQiO2I6MTt9fWk6MTthOjg6e3M6
NDoidHlwZSI7czo4OiJjaGVja2JveCI7czo1OiJ0aXRsZSI7czoyMToiU2VsZWN0IGFsbCB0aGF0
IGFwcGx5IjtzOjEwOiJ2YWxpZGF0aW9uIjthOjI6e3M6ODoicmVxdWlyZWQiO2I6MTtzOjc6ImZp
bHRlcnMiO2E6Mjp7czoxMToibWluQ2hlY2tib3giO3M6MDoiIjtzOjExOiJtYXhDaGVja2JveCI7
czowOiIiO319czo4OiJzdWJ0aXRsZSI7czoyMToiYnV0IGRvIG5vdCBleGFnZ2VyYXRlIjtzOjEx
OiJkZXNjcmlwdGlvbiI7czowOiIiO3M6MTE6ImNvbmRpdGlvbmFsIjthOjQ6e3M6NjoiYWN0aXZl
IjtiOjA7czo2OiJzdGF0dXMiO2I6MDtzOjY6ImNoYW5nZSI7YjoxO3M6NToibG9naWMiO2E6Mjp7
aTowO2E6Njp7czo2OiJtX3R5cGUiO3M6ODoiZnJlZXR5cGUiO3M6Mzoia2V5IjtzOjE6IjAiO3M6
NToiY2hlY2siO3M6MzoibGVuIjtzOjg6Im9wZXJhdG9yIjtzOjI6ImVxIjtzOjU6InZhbHVlIjtz
OjE6IjIiO3M6MzoicmVsIjtzOjM6ImFuZCI7fWk6MTthOjY6e3M6NjoibV90eXBlIjtzOjg6ImZy
ZWV0eXBlIjtzOjM6ImtleSI7czoxOiIxIjtzOjU6ImNoZWNrIjtzOjM6ImxlbiI7czo4OiJvcGVy
YXRvciI7czozOiJuZXEiO3M6NToidmFsdWUiO3M6MToiMSI7czozOiJyZWwiO3M6MzoiYW5kIjt9
fX1zOjY6Im1fdHlwZSI7czozOiJtY3EiO3M6ODoic2V0dGluZ3MiO2E6NDp7czo3OiJvcHRpb25z
IjthOjQ6e2k6MDthOjI6e3M6NToibGFiZWwiO3M6MTY6IkkgYW0gYSBQSFAgTmluamEiO3M6NToi
c2NvcmUiO3M6MDoiIjt9aToxO2E6Mjp7czo1OiJsYWJlbCI7czoxNjoiSSBsb3ZlIFdvcmRQcmVz
cyI7czo1OiJzY29yZSI7czowOiIiO31pOjI7YToyOntzOjU6ImxhYmVsIjtzOjU4OiJDU1MzIGFu
ZCBqUXVlcnkgaXMgd2hhdCBJIHVzZSB0byBwZXJzb25pZnkgbXkgaW1hZ2luYXRpb25zIjtzOjU6
InNjb3JlIjtzOjA6IiI7fWk6MzthOjI6e3M6NToibGFiZWwiO3M6NDA6Ik15U1FMIHNpbXBseSBt
ZWFucyBhIHNwYWNlIHRvIHN0b3JlIGRhdGEiO3M6NToic2NvcmUiO3M6MDoiIjt9fXM6NzoiY29s
dW1ucyI7czoxOiIxIjtzOjY6Im90aGVycyI7YjoxO3M6Nzoib19sYWJlbCI7czo2OiJPdGhlcnMi
O319aToyO2E6ODp7czo0OiJ0eXBlIjtzOjY6InNsaWRlciI7czo1OiJ0aXRsZSI7czozNToiWWVh
cnMgb2YgUEhQIGRldmVsb3BtZW50IGV4cGVyaWVuY2UiO3M6MTA6InZhbGlkYXRpb24iO2E6MDp7
fXM6ODoic3VidGl0bGUiO3M6MDoiIjtzOjExOiJkZXNjcmlwdGlvbiI7czowOiIiO3M6MTE6ImNv
bmRpdGlvbmFsIjthOjQ6e3M6NjoiYWN0aXZlIjtiOjE7czo2OiJzdGF0dXMiO2I6MDtzOjY6ImNo
YW5nZSI7YjoxO3M6NToibG9naWMiO2E6MTp7aTowO2E6Njp7czo2OiJtX3R5cGUiO3M6MzoibWNx
IjtzOjM6ImtleSI7czoxOiIxIjtzOjU6ImNoZWNrIjtzOjM6InZhbCI7czo4OiJvcGVyYXRvciI7
czoyOiJjdCI7czo1OiJ2YWx1ZSI7czozOiJQSFAiO3M6MzoicmVsIjtzOjM6ImFuZCI7fX19czo2
OiJtX3R5cGUiO3M6MzoibWNxIjtzOjg6InNldHRpbmdzIjthOjQ6e3M6MzoibWluIjtzOjE6IjAi
O3M6MzoibWF4IjtzOjI6IjUwIjtzOjQ6InN0ZXAiO2k6MTtzOjEwOiJzaG93X2NvdW50IjtiOjE7
fX1pOjM7YTo4OntzOjQ6InR5cGUiO3M6Njoic2xpZGVyIjtzOjU6InRpdGxlIjtzOjI5OiJZZWFy
cyBvZiBXb3JkUHJlc3MgZXhwZXJpZW5jZSI7czoxMDoidmFsaWRhdGlvbiI7YTowOnt9czo4OiJz
dWJ0aXRsZSI7czowOiIiO3M6MTE6ImRlc2NyaXB0aW9uIjtzOjA6IiI7czoxMToiY29uZGl0aW9u
YWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6MTtzOjY6InN0YXR1cyI7YjowO3M6NjoiY2hhbmdlIjti
OjE7czo1OiJsb2dpYyI7YToxOntpOjA7YTo2OntzOjY6Im1fdHlwZSI7czozOiJtY3EiO3M6Mzoi
a2V5IjtzOjE6IjEiO3M6NToiY2hlY2siO3M6MzoidmFsIjtzOjg6Im9wZXJhdG9yIjtzOjI6ImN0
IjtzOjU6InZhbHVlIjtzOjk6IldvcmRQcmVzcyI7czozOiJyZWwiO3M6MzoiYW5kIjt9fX1zOjY6
Im1fdHlwZSI7czozOiJtY3EiO3M6ODoic2V0dGluZ3MiO2E6NDp7czozOiJtaW4iO3M6MToiMCI7
czozOiJtYXgiO3M6MjoiNTAiO3M6NDoic3RlcCI7aToxO3M6MTA6InNob3dfY291bnQiO2I6MTt9
fWk6MTE7YTo4OntzOjQ6InR5cGUiO3M6NjoidG9nZ2xlIjtzOjU6InRpdGxlIjtzOjIxOiJZb3Ug
YSBXb3JkUHJlc3MgTmluamEiO3M6MTA6InZhbGlkYXRpb24iO2E6MDp7fXM6ODoic3VidGl0bGUi
O3M6MTc6Im5vdyBkb24ndCBiZSBzaHkhIjtzOjExOiJkZXNjcmlwdGlvbiI7czowOiIiO3M6MTE6
ImNvbmRpdGlvbmFsIjthOjQ6e3M6NjoiYWN0aXZlIjtiOjE7czo2OiJzdGF0dXMiO2I6MDtzOjY6
ImNoYW5nZSI7YjoxO3M6NToibG9naWMiO2E6MTp7aTowO2E6Njp7czo2OiJtX3R5cGUiO3M6Mzoi
bWNxIjtzOjM6ImtleSI7czoxOiIzIjtzOjU6ImNoZWNrIjtzOjM6InZhbCI7czo4OiJvcGVyYXRv
ciI7czoyOiJndCI7czo1OiJ2YWx1ZSI7czoyOiI0MCI7czozOiJyZWwiO3M6MzoiYW5kIjt9fX1z
OjY6Im1fdHlwZSI7czozOiJtY3EiO3M6ODoic2V0dGluZ3MiO2E6Mzp7czoyOiJvbiI7czo0OiJZ
ZWFwIjtzOjM6Im9mZiI7czo0OiJOb3BlIjtzOjc6ImNoZWNrZWQiO2I6MDt9fWk6NDthOjg6e3M6
NDoidHlwZSI7czoxMDoic3RhcnJhdGluZyI7czo1OiJ0aXRsZSI7czo3OiJSYXRlIGl0IjtzOjEw
OiJ2YWxpZGF0aW9uIjthOjE6e3M6ODoicmVxdWlyZWQiO2I6MTt9czo4OiJzdWJ0aXRsZSI7czox
NjoidGhlIHdheSB5b3Ugd2FudCI7czoxMToiZGVzY3JpcHRpb24iO3M6MDoiIjtzOjExOiJjb25k
aXRpb25hbCI7YTo0OntzOjY6ImFjdGl2ZSI7YjowO3M6Njoic3RhdHVzIjtiOjA7czo2OiJjaGFu
Z2UiO2I6MTtzOjU6ImxvZ2ljIjthOjA6e319czo2OiJtX3R5cGUiO3M6MzoibWNxIjtzOjg6InNl
dHRpbmdzIjthOjI6e3M6Nzoib3B0aW9ucyI7YToyOntpOjA7czoxNDoiVXNlciBJbnRlcmZhY2Ui
O2k6MTtzOjg6Ik5pY2VuZXNzIjt9czozOiJtYXgiO3M6MjoiMTAiO319aToxMjthOjg6e3M6NDoi
dHlwZSI7czo2OiJ0b2dnbGUiO3M6NToidGl0bGUiO3M6MTU6IlNvIHlvdSBsaWtlIHVzPyI7czox
MDoidmFsaWRhdGlvbiI7YTowOnt9czo4OiJzdWJ0aXRsZSI7czowOiIiO3M6MTE6ImRlc2NyaXB0
aW9uIjtzOjA6IiI7czoxMToiY29uZGl0aW9uYWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6MTtzOjY6
InN0YXR1cyI7YjowO3M6NjoiY2hhbmdlIjtiOjE7czo1OiJsb2dpYyI7YToxOntpOjA7YTo2Ontz
OjY6Im1fdHlwZSI7czozOiJtY3EiO3M6Mzoia2V5IjtzOjE6IjQiO3M6NToiY2hlY2siO3M6Mzoi
dmFsIjtzOjg6Im9wZXJhdG9yIjtzOjI6Imd0IjtzOjU6InZhbHVlIjtzOjE6IjYiO3M6MzoicmVs
IjtzOjM6ImFuZCI7fX19czo2OiJtX3R5cGUiO3M6MzoibWNxIjtzOjg6InNldHRpbmdzIjthOjM6
e3M6Mjoib24iO3M6NDoiWWVhaCI7czozOiJvZmYiO3M6NDoiTm9wZSI7czo3OiJjaGVja2VkIjti
OjA7fX1pOjU7YTo4OntzOjQ6InR5cGUiO3M6NjoidG9nZ2xlIjtzOjU6InRpdGxlIjtzOjM0OiJX
YW5uYSBhbnN3ZXIgYSBmZXcgbW9yZSBxdWVzdGlvbnM/IjtzOjEwOiJ2YWxpZGF0aW9uIjthOjA6
e31zOjg6InN1YnRpdGxlIjtzOjI0OiJjb21tb24gdGhhdCB3aWxsIGJlIGZ1biEiO3M6MTE6ImRl
c2NyaXB0aW9uIjtzOjA6IiI7czoxMToiY29uZGl0aW9uYWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6
MDtzOjY6InN0YXR1cyI7YjowO3M6NjoiY2hhbmdlIjtiOjE7czo1OiJsb2dpYyI7YTowOnt9fXM6
NjoibV90eXBlIjtzOjM6Im1jcSI7czo4OiJzZXR0aW5ncyI7YTozOntzOjI6Im9uIjtzOjQ6Illl
YWgiO3M6Mzoib2ZmIjtzOjQ6Ik5vcGUiO3M6NzoiY2hlY2tlZCI7YjowO319aTo2O2E6ODp7czo0
OiJ0eXBlIjtzOjY6Im1hdHJpeCI7czo1OiJ0aXRsZSI7czoxMzoiWW91ciBzdWJqZWN0cyI7czox
MDoidmFsaWRhdGlvbiI7YToxOntzOjg6InJlcXVpcmVkIjtiOjE7fXM6ODoic3VidGl0bGUiO3M6
MjY6ImZvciBkaWZmZXJlbnQgaW5zdGl0dXRpb25zIjtzOjExOiJkZXNjcmlwdGlvbiI7czowOiIi
O3M6MTE6ImNvbmRpdGlvbmFsIjthOjQ6e3M6NjoiYWN0aXZlIjtiOjE7czo2OiJzdGF0dXMiO2I6
MDtzOjY6ImNoYW5nZSI7YjoxO3M6NToibG9naWMiO2E6MTp7aTowO2E6Njp7czo2OiJtX3R5cGUi
O3M6MzoibWNxIjtzOjM6ImtleSI7czoxOiI1IjtzOjU6ImNoZWNrIjtzOjM6InZhbCI7czo4OiJv
cGVyYXRvciI7czoyOiJlcSI7czo1OiJ2YWx1ZSI7czoxOiIxIjtzOjM6InJlbCI7czozOiJhbmQi
O319fXM6NjoibV90eXBlIjtzOjM6Im1jcSI7czo4OiJzZXR0aW5ncyI7YTo0OntzOjQ6InJvd3Mi
O2E6Mzp7aTowO3M6MTE6IkhpZ2ggU2Nob29sIjtpOjE7czo3OiJDb2xsZWdlIjtpOjI7czoxMDoi
VW5pdmVyc2l0eSI7fXM6NzoiY29sdW1ucyI7YTozOntpOjA7czo3OiJQaHlzaWNzIjtpOjE7czox
MToiTWF0aGVtYXRpY3MiO2k6MjtzOjk6IkNoZW1pc3RyeSI7fXM6Njoic2NvcmVzIjthOjM6e2k6
MDtzOjA6IiI7aToxO3M6MDoiIjtpOjI7czowOiIiO31zOjg6Im11bHRpcGxlIjtiOjE7fX1pOjg7
YTo4OntzOjQ6InR5cGUiO3M6NToicmFuZ2UiO3M6NToidGl0bGUiO3M6MTk6IlBoeXNpY3MgU2Nv
cmUgUmFuZ2UiO3M6MTA6InZhbGlkYXRpb24iO2E6MDp7fXM6ODoic3VidGl0bGUiO3M6MzQ6Im1p
bmltdW0gdG8gbWF4aW11bSAoaW4gcGVyY2VudGFnZSkiO3M6MTE6ImRlc2NyaXB0aW9uIjtzOjA6
IiI7czoxMToiY29uZGl0aW9uYWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6MTtzOjY6InN0YXR1cyI7
YjowO3M6NjoiY2hhbmdlIjtiOjE7czo1OiJsb2dpYyI7YToyOntpOjA7YTo2OntzOjY6Im1fdHlw
ZSI7czozOiJtY3EiO3M6Mzoia2V5IjtzOjE6IjYiO3M6NToiY2hlY2siO3M6MzoidmFsIjtzOjg6
Im9wZXJhdG9yIjtzOjI6ImN0IjtzOjU6InZhbHVlIjtzOjc6InBoeXNpY3MiO3M6MzoicmVsIjtz
OjM6ImFuZCI7fWk6MTthOjY6e3M6NjoibV90eXBlIjtzOjM6Im1jcSI7czozOiJrZXkiO3M6MToi
NSI7czo1OiJjaGVjayI7czozOiJ2YWwiO3M6ODoib3BlcmF0b3IiO3M6MjoiZXEiO3M6NToidmFs
dWUiO3M6MToiMSI7czozOiJyZWwiO3M6MzoiYW5kIjt9fX1zOjY6Im1fdHlwZSI7czozOiJtY3Ei
O3M6ODoic2V0dGluZ3MiO2E6NDp7czozOiJtaW4iO3M6MToiMCI7czozOiJtYXgiO3M6MzoiMTAw
IjtzOjQ6InN0ZXAiO2k6MTtzOjEwOiJzaG93X2NvdW50IjtiOjE7fX1pOjk7YTo4OntzOjQ6InR5
cGUiO3M6NToicmFuZ2UiO3M6NToidGl0bGUiO3M6MjM6Ik1hdGhlbWF0aWNzIFNjb3JlIFJhbmdl
IjtzOjEwOiJ2YWxpZGF0aW9uIjthOjA6e31zOjg6InN1YnRpdGxlIjtzOjM0OiJtaW5pbXVtIHRv
IG1heGltdW0gKGluIHBlcmNlbnRhZ2UpIjtzOjExOiJkZXNjcmlwdGlvbiI7czowOiIiO3M6MTE6
ImNvbmRpdGlvbmFsIjthOjQ6e3M6NjoiYWN0aXZlIjtiOjE7czo2OiJzdGF0dXMiO2I6MDtzOjY6
ImNoYW5nZSI7YjoxO3M6NToibG9naWMiO2E6Mjp7aTowO2E6Njp7czo2OiJtX3R5cGUiO3M6Mzoi
bWNxIjtzOjM6ImtleSI7czoxOiI2IjtzOjU6ImNoZWNrIjtzOjM6InZhbCI7czo4OiJvcGVyYXRv
ciI7czoyOiJjdCI7czo1OiJ2YWx1ZSI7czo0OiJtYXRoIjtzOjM6InJlbCI7czozOiJhbmQiO31p
OjE7YTo2OntzOjY6Im1fdHlwZSI7czozOiJtY3EiO3M6Mzoia2V5IjtzOjE6IjUiO3M6NToiY2hl
Y2siO3M6MzoidmFsIjtzOjg6Im9wZXJhdG9yIjtzOjI6ImVxIjtzOjU6InZhbHVlIjtzOjE6IjEi
O3M6MzoicmVsIjtzOjM6ImFuZCI7fX19czo2OiJtX3R5cGUiO3M6MzoibWNxIjtzOjg6InNldHRp
bmdzIjthOjQ6e3M6MzoibWluIjtzOjE6IjAiO3M6MzoibWF4IjtzOjM6IjEwMCI7czo0OiJzdGVw
IjtpOjE7czoxMDoic2hvd19jb3VudCI7YjoxO319aToxMzthOjg6e3M6NDoidHlwZSI7czo2OiJ0
b2dnbGUiO3M6NToidGl0bGUiO3M6MzQ6IkRvIHlvdSBrbm93IGRpZmZlcmVudGlhbCBjYWxjdWx1
cz8iO3M6MTA6InZhbGlkYXRpb24iO2E6MDp7fXM6ODoic3VidGl0bGUiO3M6MzE6IkF0IHlvdXIg
c2NvcmUgaXQgc2hvdWxkIGJlIGVhc3kiO3M6MTE6ImRlc2NyaXB0aW9uIjtzOjA6IiI7czoxMToi
Y29uZGl0aW9uYWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6MTtzOjY6InN0YXR1cyI7YjowO3M6Njoi
Y2hhbmdlIjtiOjE7czo1OiJsb2dpYyI7YTozOntpOjA7YTo2OntzOjY6Im1fdHlwZSI7czozOiJt
Y3EiO3M6Mzoia2V5IjtzOjE6IjkiO3M6NToiY2hlY2siO3M6MzoidmFsIjtzOjg6Im9wZXJhdG9y
IjtzOjI6Imd0IjtzOjU6InZhbHVlIjtzOjI6IjY5IjtzOjM6InJlbCI7czozOiJhbmQiO31pOjE7
YTo2OntzOjY6Im1fdHlwZSI7czozOiJtY3EiO3M6Mzoia2V5IjtzOjE6IjYiO3M6NToiY2hlY2si
O3M6MzoidmFsIjtzOjg6Im9wZXJhdG9yIjtzOjI6ImN0IjtzOjU6InZhbHVlIjtzOjQ6Im1hdGgi
O3M6MzoicmVsIjtzOjM6ImFuZCI7fWk6MjthOjY6e3M6NjoibV90eXBlIjtzOjM6Im1jcSI7czoz
OiJrZXkiO3M6MToiNSI7czo1OiJjaGVjayI7czozOiJ2YWwiO3M6ODoib3BlcmF0b3IiO3M6Mjoi
ZXEiO3M6NToidmFsdWUiO3M6MToiMSI7czozOiJyZWwiO3M6MzoiYW5kIjt9fX1zOjY6Im1fdHlw
ZSI7czozOiJtY3EiO3M6ODoic2V0dGluZ3MiO2E6Mzp7czoyOiJvbiI7czo0OiJZZWFoIjtzOjM6
Im9mZiI7czo0OiJOb3BlIjtzOjc6ImNoZWNrZWQiO2I6MDt9fWk6MTA7YTo4OntzOjQ6InR5cGUi
O3M6NToicmFuZ2UiO3M6NToidGl0bGUiO3M6MjE6IkNoZW1pc3RyeSBTY29yZSBSYW5nZSI7czox
MDoidmFsaWRhdGlvbiI7YTowOnt9czo4OiJzdWJ0aXRsZSI7czozNDoibWluaW11bSB0byBtYXhp
bXVtIChpbiBwZXJjZW50YWdlKSI7czoxMToiZGVzY3JpcHRpb24iO3M6MDoiIjtzOjExOiJjb25k
aXRpb25hbCI7YTo0OntzOjY6ImFjdGl2ZSI7YjoxO3M6Njoic3RhdHVzIjtiOjA7czo2OiJjaGFu
Z2UiO2I6MTtzOjU6ImxvZ2ljIjthOjM6e2k6MDthOjY6e3M6NjoibV90eXBlIjtzOjM6Im1jcSI7
czozOiJrZXkiO3M6MToiNiI7czo1OiJjaGVjayI7czozOiJ2YWwiO3M6ODoib3BlcmF0b3IiO3M6
MjoiY3QiO3M6NToidmFsdWUiO3M6OToiY2hlbWlzdHJ5IjtzOjM6InJlbCI7czozOiJhbmQiO31p
OjE7YTo2OntzOjY6Im1fdHlwZSI7czozOiJtY3EiO3M6Mzoia2V5IjtzOjE6IjUiO3M6NToiY2hl
Y2siO3M6MzoidmFsIjtzOjg6Im9wZXJhdG9yIjtzOjI6ImVxIjtzOjU6InZhbHVlIjtzOjE6IjEi
O3M6MzoicmVsIjtzOjM6ImFuZCI7fWk6MjthOjY6e3M6NjoibV90eXBlIjtzOjM6Im1jcSI7czoz
OiJrZXkiO3M6MToiMCI7czo1OiJjaGVjayI7czozOiJ2YWwiO3M6ODoib3BlcmF0b3IiO3M6Mjoi
ZXEiO3M6NToidmFsdWUiO3M6MDoiIjtzOjM6InJlbCI7czozOiJhbmQiO319fXM6NjoibV90eXBl
IjtzOjM6Im1jcSI7czo4OiJzZXR0aW5ncyI7YTo0OntzOjM6Im1pbiI7czoxOiIwIjtzOjM6Im1h
eCI7czozOiIxMDAiO3M6NDoic3RlcCI7aToxO3M6MTA6InNob3dfY291bnQiO2I6MTt9fX0iO3M6
ODoiZnJlZXR5cGUiO3M6NTc1MDoiYTo3OntpOjM7YTo4OntzOjQ6InR5cGUiO3M6MTQ6ImZlZWRi
YWNrX3NtYWxsIjtzOjU6InRpdGxlIjtzOjE4OiJXaGVyZSBkbyB5b3UgbGl2ZT8iO3M6MTA6InZh
bGlkYXRpb24iO2E6Mjp7czo4OiJyZXF1aXJlZCI7YjoxO3M6NzoiZmlsdGVycyI7YTo1OntzOjQ6
InR5cGUiO3M6MzoiYWxsIjtzOjM6Im1pbiI7czowOiIiO3M6MzoibWF4IjtzOjA6IiI7czo3OiJt
aW5TaXplIjtzOjA6IiI7czo3OiJtYXhTaXplIjtzOjA6IiI7fX1zOjg6InN1YnRpdGxlIjtzOjMw
OiJqdXN0IHRoZSBjb3VudHJ5IHdvdWxkIGJlIGZpbmUiO3M6MTE6ImRlc2NyaXB0aW9uIjtzOjA6
IiI7czoxMToiY29uZGl0aW9uYWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6MTtzOjY6InN0YXR1cyI7
YjowO3M6NjoiY2hhbmdlIjtiOjE7czo1OiJsb2dpYyI7YToxOntpOjA7YTo2OntzOjY6Im1fdHlw
ZSI7czozOiJtY3EiO3M6Mzoia2V5IjtzOjE6IjUiO3M6NToiY2hlY2siO3M6MzoidmFsIjtzOjg6
Im9wZXJhdG9yIjtzOjI6ImVxIjtzOjU6InZhbHVlIjtzOjE6IjEiO3M6MzoicmVsIjtzOjM6ImFu
ZCI7fX19czo2OiJtX3R5cGUiO3M6ODoiZnJlZXR5cGUiO3M6ODoic2V0dGluZ3MiO2E6NDp7czo1
OiJlbWFpbCI7czowOiIiO3M6NDoiaWNvbiI7czo1OiI1NzM0NSI7czoxMToicGxhY2Vob2xkZXIi
O3M6MTA6IldyaXRlIGhlcmUiO3M6NToic2NvcmUiO3M6MDoiIjt9fWk6NDthOjg6e3M6NDoidHlw
ZSI7czoxNDoiZmVlZGJhY2tfc21hbGwiO3M6NToidGl0bGUiO3M6MzY6IkluZGlhPyBUaGF0J3Mg
Z3JlYXQhIEluIHdoaWNoIHN0YXRlPyI7czoxMDoidmFsaWRhdGlvbiI7YToyOntzOjg6InJlcXVp
cmVkIjtiOjE7czo3OiJmaWx0ZXJzIjthOjU6e3M6NDoidHlwZSI7czozOiJhbGwiO3M6MzoibWlu
IjtzOjA6IiI7czozOiJtYXgiO3M6MDoiIjtzOjc6Im1pblNpemUiO3M6MDoiIjtzOjc6Im1heFNp
emUiO3M6MDoiIjt9fXM6ODoic3VidGl0bGUiO3M6MjI6IldlIGxvdmUgSW5kaWEgZG9uJ3Qgd2Ui
O3M6MTE6ImRlc2NyaXB0aW9uIjtzOjA6IiI7czoxMToiY29uZGl0aW9uYWwiO2E6NDp7czo2OiJh
Y3RpdmUiO2I6MTtzOjY6InN0YXR1cyI7YjowO3M6NjoiY2hhbmdlIjtiOjE7czo1OiJsb2dpYyI7
YToyOntpOjA7YTo2OntzOjY6Im1fdHlwZSI7czo4OiJmcmVldHlwZSI7czozOiJrZXkiO3M6MToi
MyI7czo1OiJjaGVjayI7czozOiJ2YWwiO3M6ODoib3BlcmF0b3IiO3M6MjoiZXEiO3M6NToidmFs
dWUiO3M6NToiaW5kaWEiO3M6MzoicmVsIjtzOjM6ImFuZCI7fWk6MTthOjY6e3M6NjoibV90eXBl
IjtzOjM6Im1jcSI7czozOiJrZXkiO3M6MToiNSI7czo1OiJjaGVjayI7czozOiJ2YWwiO3M6ODoi
b3BlcmF0b3IiO3M6MjoiZXEiO3M6NToidmFsdWUiO3M6MToiMSI7czozOiJyZWwiO3M6MzoiYW5k
Ijt9fX1zOjY6Im1fdHlwZSI7czo4OiJmcmVldHlwZSI7czo4OiJzZXR0aW5ncyI7YTo0OntzOjU6
ImVtYWlsIjtzOjA6IiI7czo0OiJpY29uIjtzOjU6IjU3MzQ1IjtzOjExOiJwbGFjZWhvbGRlciI7
czoxMDoiV3JpdGUgaGVyZSI7czo1OiJzY29yZSI7czowOiIiO319aTo1O2E6ODp7czo0OiJ0eXBl
IjtzOjE0OiJmZWVkYmFja19zbWFsbCI7czo1OiJ0aXRsZSI7czozMzoiUGxlYXNlIGFsc28gbGV0
IHVzIGtub3cgeW91ciBjaXR5IjtzOjEwOiJ2YWxpZGF0aW9uIjthOjI6e3M6ODoicmVxdWlyZWQi
O2I6MTtzOjc6ImZpbHRlcnMiO2E6NTp7czo0OiJ0eXBlIjtzOjM6ImFsbCI7czozOiJtaW4iO3M6
MDoiIjtzOjM6Im1heCI7czowOiIiO3M6NzoibWluU2l6ZSI7czowOiIiO3M6NzoibWF4U2l6ZSI7
czowOiIiO319czo4OiJzdWJ0aXRsZSI7czozMDoiQ2F1c2Ugd2UnZCBhbHdheXMgbGlrZSB0byBr
bm93IjtzOjExOiJkZXNjcmlwdGlvbiI7czowOiIiO3M6MTE6ImNvbmRpdGlvbmFsIjthOjQ6e3M6
NjoiYWN0aXZlIjtiOjE7czo2OiJzdGF0dXMiO2I6MDtzOjY6ImNoYW5nZSI7YjoxO3M6NToibG9n
aWMiO2E6Mzp7aTowO2E6Njp7czo2OiJtX3R5cGUiO3M6ODoiZnJlZXR5cGUiO3M6Mzoia2V5Ijtz
OjE6IjQiO3M6NToiY2hlY2siO3M6MzoibGVuIjtzOjg6Im9wZXJhdG9yIjtzOjI6Imd0IjtzOjU6
InZhbHVlIjtzOjE6IjEiO3M6MzoicmVsIjtzOjM6ImFuZCI7fWk6MTthOjY6e3M6NjoibV90eXBl
IjtzOjM6Im1jcSI7czozOiJrZXkiO3M6MToiNSI7czo1OiJjaGVjayI7czozOiJ2YWwiO3M6ODoi
b3BlcmF0b3IiO3M6MjoiZXEiO3M6NToidmFsdWUiO3M6MToiMSI7czozOiJyZWwiO3M6MzoiYW5k
Ijt9aToyO2E6Njp7czo2OiJtX3R5cGUiO3M6ODoiZnJlZXR5cGUiO3M6Mzoia2V5IjtzOjE6IjMi
O3M6NToiY2hlY2siO3M6MzoidmFsIjtzOjg6Im9wZXJhdG9yIjtzOjI6ImVxIjtzOjU6InZhbHVl
IjtzOjU6ImluZGlhIjtzOjM6InJlbCI7czozOiJhbmQiO319fXM6NjoibV90eXBlIjtzOjg6ImZy
ZWV0eXBlIjtzOjg6InNldHRpbmdzIjthOjQ6e3M6NToiZW1haWwiO3M6MDoiIjtzOjQ6Imljb24i
O3M6NToiNTczNDUiO3M6MTE6InBsYWNlaG9sZGVyIjtzOjEwOiJXcml0ZSBoZXJlIjtzOjU6InNj
b3JlIjtzOjA6IiI7fX1pOjY7YTo4OntzOjQ6InR5cGUiO3M6MTQ6ImZlZWRiYWNrX2xhcmdlIjtz
OjU6InRpdGxlIjtzOjE4OiJHaXZlIHlvdXIgYWRkcmVzcz8iO3M6MTA6InZhbGlkYXRpb24iO2E6
MTp7czo4OiJyZXF1aXJlZCI7YjowO31zOjg6InN1YnRpdGxlIjtzOjIyOiJ3ZSBsaXZlIGF0IGtv
bGthdGEgdG9vIjtzOjExOiJkZXNjcmlwdGlvbiI7czowOiIiO3M6MTE6ImNvbmRpdGlvbmFsIjth
OjQ6e3M6NjoiYWN0aXZlIjtiOjE7czo2OiJzdGF0dXMiO2I6MDtzOjY6ImNoYW5nZSI7YjoxO3M6
NToibG9naWMiO2E6NDp7aTowO2E6Njp7czo2OiJtX3R5cGUiO3M6MzoibWNxIjtzOjM6ImtleSI7
czoxOiI1IjtzOjU6ImNoZWNrIjtzOjM6InZhbCI7czo4OiJvcGVyYXRvciI7czoyOiJlcSI7czo1
OiJ2YWx1ZSI7czoxOiIxIjtzOjM6InJlbCI7czozOiJhbmQiO31pOjE7YTo2OntzOjY6Im1fdHlw
ZSI7czo4OiJmcmVldHlwZSI7czozOiJrZXkiO3M6MToiMyI7czo1OiJjaGVjayI7czozOiJ2YWwi
O3M6ODoib3BlcmF0b3IiO3M6MjoiZXEiO3M6NToidmFsdWUiO3M6NToiaW5kaWEiO3M6MzoicmVs
IjtzOjM6ImFuZCI7fWk6MjthOjY6e3M6NjoibV90eXBlIjtzOjg6ImZyZWV0eXBlIjtzOjM6Imtl
eSI7czoxOiI0IjtzOjU6ImNoZWNrIjtzOjM6ImxlbiI7czo4OiJvcGVyYXRvciI7czoyOiJndCI7
czo1OiJ2YWx1ZSI7czoxOiIxIjtzOjM6InJlbCI7czozOiJhbmQiO31pOjM7YTo2OntzOjY6Im1f
dHlwZSI7czo4OiJmcmVldHlwZSI7czozOiJrZXkiO3M6MToiNSI7czo1OiJjaGVjayI7czozOiJ2
YWwiO3M6ODoib3BlcmF0b3IiO3M6MjoiZXEiO3M6NToidmFsdWUiO3M6Nzoia29sa2F0YSI7czoz
OiJyZWwiO3M6MzoiYW5kIjt9fX1zOjY6Im1fdHlwZSI7czo4OiJmcmVldHlwZSI7czo4OiJzZXR0
aW5ncyI7YTozOntzOjU6ImVtYWlsIjtzOjA6IiI7czoxMToicGxhY2Vob2xkZXIiO3M6MTA6Ildy
aXRlIGhlcmUiO3M6NToic2NvcmUiO3M6MDoiIjt9fWk6MDthOjg6e3M6NDoidHlwZSI7czo2OiJ1
cGxvYWQiO3M6NToidGl0bGUiO3M6MjU6IlBsZWFzZSB1cGxvYWQgeW91ciByZXN1bWUiO3M6MTA6
InZhbGlkYXRpb24iO2E6MTp7czo4OiJyZXF1aXJlZCI7YjoxO31zOjg6InN1YnRpdGxlIjtzOjA6
IiI7czoxMToiZGVzY3JpcHRpb24iO3M6NTQ6IkRvY3VtZW50cyBvbmx5LiBTaG91bGQgY29udGFp
biB5b3VyIHNjYW5uZWQgc2lnbmF0dXJlLiI7czoxMToiY29uZGl0aW9uYWwiO2E6NDp7czo2OiJh
Y3RpdmUiO2I6MDtzOjY6InN0YXR1cyI7YjowO3M6NjoiY2hhbmdlIjtiOjA7czo1OiJsb2dpYyI7
YTowOnt9fXM6NjoibV90eXBlIjtzOjg6ImZyZWV0eXBlIjtzOjg6InNldHRpbmdzIjthOjEyOntz
OjQ6Imljb24iO3M6NToiNTc3ODciO3M6MTc6ImFjY2VwdF9maWxlX3R5cGVzIjtzOjEyOiJkb2Ms
ZG9jeCxwZGYiO3M6MTk6Im1heF9udW1iZXJfb2ZfZmlsZXMiO3M6MToiMiI7czoxOToibWluX251
bWJlcl9vZl9maWxlcyI7czowOiIiO3M6MTM6Im1heF9maWxlX3NpemUiO3M6NzoiODM4ODYwOCI7
czoxMzoibWluX2ZpbGVfc2l6ZSI7czoxOiIxIjtzOjIwOiJ3cF9tZWRpYV9pbnRlZ3JhdGlvbiI7
YjowO3M6MTE6ImF1dG9fdXBsb2FkIjtiOjA7czoxMToiZHJhZ19uX2Ryb3AiO2I6MTtzOjEyOiJw
cm9ncmVzc19iYXIiO2I6MTtzOjEzOiJwcmV2aWV3X21lZGlhIjtiOjE7czoxMDoiY2FuX2RlbGV0
ZSI7YjoxO319aToxO2E6ODp7czo0OiJ0eXBlIjtzOjY6InVwbG9hZCI7czo1OiJ0aXRsZSI7czox
NzoiVXBsb2FkIHlvdXIgcGhvdG8iO3M6MTA6InZhbGlkYXRpb24iO2E6MTp7czo4OiJyZXF1aXJl
ZCI7YjoxO31zOjg6InN1YnRpdGxlIjtzOjA6IiI7czoxMToiZGVzY3JpcHRpb24iO3M6NDk6Iklt
YWdlIG9ubHkuIFNob3VsZCBiZSBhdCBsZWFzdCA2MDBYNjAwcHggaW4gc2l6ZS4iO3M6MTE6ImNv
bmRpdGlvbmFsIjthOjQ6e3M6NjoiYWN0aXZlIjtiOjA7czo2OiJzdGF0dXMiO2I6MDtzOjY6ImNo
YW5nZSI7YjowO3M6NToibG9naWMiO2E6MDp7fX1zOjY6Im1fdHlwZSI7czo4OiJmcmVldHlwZSI7
czo4OiJzZXR0aW5ncyI7YToxMjp7czo0OiJpY29uIjtzOjU6IjU3MzQ2IjtzOjE3OiJhY2NlcHRf
ZmlsZV90eXBlcyI7czoxNjoiZ2lmLGpwZWcscG5nLGpwZyI7czoxOToibWF4X251bWJlcl9vZl9m
aWxlcyI7czoxOiIyIjtzOjE5OiJtaW5fbnVtYmVyX29mX2ZpbGVzIjtzOjE6IjIiO3M6MTM6Im1h
eF9maWxlX3NpemUiO3M6NzoiODM4ODYwOCI7czoxMzoibWluX2ZpbGVfc2l6ZSI7czoxOiIxIjtz
OjIwOiJ3cF9tZWRpYV9pbnRlZ3JhdGlvbiI7YjoxO3M6MTE6ImF1dG9fdXBsb2FkIjtiOjA7czox
MToiZHJhZ19uX2Ryb3AiO2I6MTtzOjEyOiJwcm9ncmVzc19iYXIiO2I6MTtzOjEzOiJwcmV2aWV3
X21lZGlhIjtiOjE7czoxMDoiY2FuX2RlbGV0ZSI7YjoxO319aToyO2E6ODp7czo0OiJ0eXBlIjtz
OjY6InVwbG9hZCI7czo1OiJ0aXRsZSI7czoyODoiVXBsb2FkIHJlY29tbWVuZGF0aW9uIGxldHRl
ciI7czoxMDoidmFsaWRhdGlvbiI7YToxOntzOjg6InJlcXVpcmVkIjtiOjA7fXM6ODoic3VidGl0
bGUiO3M6MDoiIjtzOjExOiJkZXNjcmlwdGlvbiI7czo4MjoiVGhpcyBpcyBvcHRpb25hbC4gQSBy
ZWNvbW1lbmRhdGlvbiB3aWxsIGFsd2F5cyBoZWxwIHlvdSBmaW5kIGEgYmV0dGVyIGluIG91ciBm
aXJtLiI7czoxMToiY29uZGl0aW9uYWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6MDtzOjY6InN0YXR1
cyI7YjowO3M6NjoiY2hhbmdlIjtiOjA7czo1OiJsb2dpYyI7YTowOnt9fXM6NjoibV90eXBlIjtz
Ojg6ImZyZWV0eXBlIjtzOjg6InNldHRpbmdzIjthOjEyOntzOjQ6Imljb24iO3M6NToiNTc3MjIi
O3M6MTc6ImFjY2VwdF9maWxlX3R5cGVzIjtzOjM3OiJkb2MsZG9jeCxqcGcsanBlZyxnaWYscG5n
LHBkZixtcDQsbXAzIjtzOjE5OiJtYXhfbnVtYmVyX29mX2ZpbGVzIjtzOjE6IjIiO3M6MTk6Im1p
bl9udW1iZXJfb2ZfZmlsZXMiO3M6MDoiIjtzOjEzOiJtYXhfZmlsZV9zaXplIjtzOjc6IjEwMDAw
MDAiO3M6MTM6Im1pbl9maWxlX3NpemUiO3M6MToiMSI7czoyMDoid3BfbWVkaWFfaW50ZWdyYXRp
b24iO2I6MDtzOjExOiJhdXRvX3VwbG9hZCI7YjoxO3M6MTE6ImRyYWdfbl9kcm9wIjtiOjE7czox
MjoicHJvZ3Jlc3NfYmFyIjtiOjE7czoxMzoicHJldmlld19tZWRpYSI7YjoxO3M6MTA6ImNhbl9k
ZWxldGUiO2I6MTt9fX0iO3M6NToicGluZm8iO3M6OTkzOiJhOjM6e2k6MDthOjg6e3M6NDoidHlw
ZSI7czo2OiJmX25hbWUiO3M6NToidGl0bGUiO3M6MTA6IkZpcnN0IE5hbWUiO3M6MTA6InZhbGlk
YXRpb24iO2E6MTp7czo4OiJyZXF1aXJlZCI7YjoxO31zOjg6InN1YnRpdGxlIjtzOjA6IiI7czox
MToiZGVzY3JpcHRpb24iO3M6MDoiIjtzOjExOiJjb25kaXRpb25hbCI7YTo0OntzOjY6ImFjdGl2
ZSI7YjowO3M6Njoic3RhdHVzIjtiOjA7czo2OiJjaGFuZ2UiO2I6MDtzOjU6ImxvZ2ljIjthOjA6
e319czo2OiJtX3R5cGUiO3M6NToicGluZm8iO3M6ODoic2V0dGluZ3MiO2E6MTp7czoxMToicGxh
Y2Vob2xkZXIiO3M6MTA6IldyaXRlIGhlcmUiO319aToxO2E6ODp7czo0OiJ0eXBlIjtzOjY6Imxf
bmFtZSI7czo1OiJ0aXRsZSI7czo5OiJMYXN0IE5hbWUiO3M6MTA6InZhbGlkYXRpb24iO2E6MTp7
czo4OiJyZXF1aXJlZCI7YjoxO31zOjg6InN1YnRpdGxlIjtzOjA6IiI7czoxMToiZGVzY3JpcHRp
b24iO3M6MDoiIjtzOjExOiJjb25kaXRpb25hbCI7YTo0OntzOjY6ImFjdGl2ZSI7YjowO3M6Njoi
c3RhdHVzIjtiOjA7czo2OiJjaGFuZ2UiO2I6MDtzOjU6ImxvZ2ljIjthOjA6e319czo2OiJtX3R5
cGUiO3M6NToicGluZm8iO3M6ODoic2V0dGluZ3MiO2E6MTp7czoxMToicGxhY2Vob2xkZXIiO3M6
MTA6IldyaXRlIGhlcmUiO319aToyO2E6ODp7czo0OiJ0eXBlIjtzOjU6ImVtYWlsIjtzOjU6InRp
dGxlIjtzOjU6IkVtYWlsIjtzOjEwOiJ2YWxpZGF0aW9uIjthOjE6e3M6ODoicmVxdWlyZWQiO2I6
MTt9czo4OiJzdWJ0aXRsZSI7czowOiIiO3M6MTE6ImRlc2NyaXB0aW9uIjtzOjA6IiI7czoxMToi
Y29uZGl0aW9uYWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6MDtzOjY6InN0YXR1cyI7YjowO3M6Njoi
Y2hhbmdlIjtiOjA7czo1OiJsb2dpYyI7YTowOnt9fXM6NjoibV90eXBlIjtzOjU6InBpbmZvIjtz
Ojg6InNldHRpbmdzIjthOjE6e3M6MTE6InBsYWNlaG9sZGVyIjtzOjEwOiJXcml0ZSBoZXJlIjt9
fX0iO3M6NDoidHlwZSI7czoxOiIxIjtzOjc6InVwZGF0ZWQiO3M6MTk6IjIwMTQtMDQtMTkgMTU6
MzY6MDkiO30=
		<?php
	}

	/*==========================================================================
	 * AJAX Methods
	 *========================================================================*/
	public function generate_import() {
		// First set the JSON header
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

		// Init the global arrays
		global $wpdb, $ipt_fsqm_info;

		// Get the variables
		$form_name = @$this->post['form_name'];
		$form_code = @$this->post['form_code'];
		$nonce = @$this->post['_wpnonce'];

		// Init the return
		$return = array(
			'error' => false,
			'code' => '',
		);

		// First check the nonce
		if ( ! wp_verify_nonce( $nonce, 'ipt_fsqm_import_export_nonce' ) || ! current_user_can( 'manage_feedback' ) ) {
			$return['error'] = true;
			$return['code'] = __( 'Cheatin&#8217; uh?' );
			die( json_encode( (object) $return ) );
		}

		// Decode the form
		$form = maybe_unserialize( base64_decode( $form_code ) );

		// Check it's integrity
		if ( ! is_array( $form ) ) {
			$return['error'] = true;
			$return['code'] = __( 'Invalid import code', 'ipt_fsqm' );
			die( json_encode( (object) $return ) );
		}

		// So it is an array, now check for required fields
		$required_fields = array(
			'id', 'name', 'settings', 'layout', 'design', 'mcq', 'freetype', 'pinfo', 'type',
		);
		foreach ( $required_fields as $field_key ) {
			if ( ! isset( $form[$field_key] ) ) {
				$return['error'] = true;
				$return['code'] = __( 'Import code missing required argument: ', 'ipt_fsqm' ) . $field_key;
				die( json_encode( (object) $return ) );
			}
		}

		// Override the name
		if ( $form_name != '' ) {
			$form['name'] = $form_name;
		}

		// Sanitize the name
		if ( $form['name'] == '' ) {
			$form['name'] = __( 'Untitled', 'ipt_fsqm' );
		} else {
			$form['name'] = strip_tags( $form['name'] );
		}

		// All set, now import it
		$wpdb->insert( $ipt_fsqm_info['form_table'], array(
			'name'     => $form['name'],
			'settings' => $form['settings'],
			'layout'   => $form['layout'],
			'design'   => $form['design'],
			'mcq'      => $form['mcq'],
			'freetype' => $form['freetype'],
			'pinfo'    => $form['pinfo'],
			'type'     => $form['type'],
			'category' => 0,
		), '%s' );

		$new_form_id = $wpdb->insert_id;

		$return['code'] = sprintf( __( 'Form successfully imported. <a href="%1$s">Click here to edit: %2$s</a>', 'ipt_fsqm' ), admin_url( 'admin.php?page=ipt_fsqm_all_forms&action=edit&form_id=' . $new_form_id ), $form['name'] );
		die( json_encode( (object) $return ) );
	}

	public function generate_export() {
		// First set the JSON header
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

		// Init the global arrays
		global $wpdb, $ipt_fsqm_info;

		// Get the variables
		$form_id = (int) @$_GET['form_id'];
		$nonce = @$_GET['_wpnonce'];

		// Init the return
		$return = array(
			'error' => false,
			'code' => '',
		);

		// First check the nonce
		if ( ! wp_verify_nonce( $nonce, 'ipt_fsqm_import_export_nonce' ) || ! current_user_can( 'manage_feedback' ) ) {
			$return['error'] = true;
			$return['code'] = __( 'Cheatin&#8217; uh?' );
			die( json_encode( (object) $return ) );
		}

		// Now get the form
		$form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_info['form_table']} WHERE id = %d", $form_id ), ARRAY_A );

		// If it is invalid
		if ( null == $form ) {
			$return['error'] = true;
			$return['code'] = __( 'Invalid Form', 'ipt_fsqm' );
			die( json_encode( (object) $return ) );
		}

		// Now prepare the export
		$export = base64_encode( maybe_serialize( $form ) );
		$return['code'] = chunk_split( $export );
		die( json_encode( (object) $return ) );
	}
}
