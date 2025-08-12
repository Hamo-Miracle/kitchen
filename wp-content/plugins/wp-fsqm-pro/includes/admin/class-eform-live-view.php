<?php
/**
 * Helper class for Eform live view
 * @since 4.5.0
 */

class EForm_Live_View {
	/**
	 * Form Instance
	 *
	 * @var IPT_FSQM_Form_Elements_Front
	 */
	protected $form;

	public function __construct( $form_id )	{
		$this->form = new IPT_FSQM_Form_Elements_Front( null, $form_id );
		$this->form->enable_live_view();
	}

	public function preview() {
		?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title><?php echo $this->form->name; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<style type="text/css">
		body {
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
		}
		/**/
	</style>
	<?php $this->enqueue(); ?>
</head>
<body class="ipt_uif_common">
	<div id="eform-live-view-messages" style="display: none;"></div>
	<div id="eform-live-view-loading" style="display: none;">
		<div class="eform-refresh">
			<div class="eform-refresh-bar"></div>
			<div class="eform-refresh-bar"></div>
			<div class="eform-refresh-bar"></div>
			<div class="eform-refresh-bar"></div>
		</div>
	</div>
	<div id="eform-live-view">
		<div class="eform-live-view-removable">
			<?php $this->form->show_form( true, false ); ?>
		</div>
	</div>
	<div id="eform-live-demo-mode-token"></div>
	<?php
	wp_print_scripts();
	wp_print_styles();
	?>
</body>
</html>
		<?php
	}

	public function stream_html() {
		ob_start();
		$this->form->show_form( true, false );
		$form = ob_get_clean();
		return [
			'success' => true,
			'html' => $form,
			'id' => $this->form->form_id,
		];
	}

	protected function enqueue() {
		global $ipt_fsqm_settings;
		// Enqueue
		$this->form->ui->enqueue_all( $ipt_fsqm_settings['gplaces_api'] );
		$this->form->enqueue( true );
		wp_enqueue_script( 'eform-live-sync', IPT_FSQM_Loader::$static_location . 'admin/js/eform-live-sync.min.js', [], IPT_FSQM_Loader::$version );
		wp_enqueue_style( 'eform-live-sync-css', IPT_FSQM_Loader::$static_location . 'admin/css/eform-live-sync.css', [], IPT_FSQM_Loader::$version );
		wp_localize_script( 'eform-live-sync', 'eFormLiveSync', [
			'ajaxurl' => admin_url('admin-ajax.php'),
			'_wpnonce' => wp_create_nonce( 'eform_builder_form_html' ),
			'form_id' => $this->form->form_id,
		] );
		wp_print_styles();
		wp_print_scripts();
	}
}
