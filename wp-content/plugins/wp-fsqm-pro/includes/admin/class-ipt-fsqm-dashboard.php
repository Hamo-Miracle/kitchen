<?php
/**
 * IPT FSQM Dashboard
 *
 * Class for handling the Dashboard page under eForm
 *
 * @author Swashata <swashata4u@gmail.com>
 * @package eForm - WordPress Form Builder
 * @subpackage Admin\Dashboard
 * @codeCoverageIgnore
 */
class IPT_FSQM_Dashboard extends IPT_FSQM_Admin_Base {
	public function __construct() {
		$this->capability = 'view_feedback';
		$this->action_nonce = 'ipt_fsqm_dashboard_nonce';

		parent::__construct();

		$this->icon = 'dashboard';
	}

	/*==========================================================================
	 * SYSTEM METHODS
	 *========================================================================*/

	public function admin_menu() {
		$this->pagehook = add_menu_page( __( 'eForm - WordPress Form Builder', 'ipt_fsqm' ), __( 'eForm', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_dashboard', array( $this, 'index' ), 'dashicons-fsqm', 25 );
		add_submenu_page( 'ipt_fsqm_dashboard', __( 'eForm - WordPress Form Builder', 'ipt_fsqm' ), __( 'Dashboard', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_dashboard', array( $this, 'index' ) );
		parent::admin_menu();
	}
	public function index() {
		$this->index_head( __( 'eForm <span class="ipt-icomoon-arrow-right2"></span> Dashboard', 'ipt_fsqm' ), false );
?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		var protocol = window.location.protocol;
		$.getScript(protocol + '//www.google.com/jsapi', function() {
			google.load('visualization', '1.0', {
				packages : ['corechart'],
				callback : function() {
					if ( typeof( drawLatestTen ) == 'function' ) {
						drawLatestTen();
					}
					if ( typeof( drawOverallPie ) == 'function' ) {
						drawOverallPie();
					}
				}
			});
		});
	});
</script>
<div class="ipt_uif_left_col"><div class="ipt_uif_col_inner">
	<?php $this->ui->iconbox( __( 'Latest Submission Statistics', 'ipt_fsqm' ), array( $this, 'meta_stat' ), 'stats' ); ?>
</div></div>
<div class="ipt_uif_right_col"><div class="ipt_uif_col_inner">
	<?php $this->ui->iconbox( __( 'Overall Submission Statistics', 'ipt_fsqm' ), array( $this, 'meta_overall' ), 'pie' ); ?>
</div></div>
<div class="clear"></div>
<?php $this->ui->iconbox( __( 'Latest 10 Submissions', 'ipt_fsqm' ) . '<a class="button ipt_uif_button" href="' . admin_url( 'admin.php?page=ipt_fsqm_view_all_submissions' ) . '">' . __( 'View all', 'ipt_fsqm' ) . '</a>', array( $this, 'meta_ten' ), 'list2' ); ?>
<div class="clear"></div>
<?php $this->ui->iconbox( __( 'Generate Embed Code for Standalone Forms', 'ipt_fsqm' ), array( $this, 'meta_embed_generator' ), 'embed' ); ?>
<div class="clear"></div>
		<?php
		$this->index_foot( false );
	}



	/*==========================================================================
	 * METABOX CB
	 *========================================================================*/

	public function meta_embed_generator() {
		$forms = IPT_FSQM_Form_Elements_Static::get_forms();
		if ( null == $forms || empty( $forms ) ) {
			$this->ui->msg_error( __( 'You have not created any forms yet.', 'ipt_fsqm' ) );
			return;
		}
		$default_permalink = IPT_FSQM_Form_Elements_Static::standalone_permalink_parts( $forms[0]->id );
		$default_code = '<iframe src="' . $default_permalink['url'] . '" width="960" height="480" style="width: 960px; height: 480px; border: 0 none; overflow-y: auto;" frameborder="0">&nbsp;</iframe>';
		$items = array();
		foreach ( $forms as $form ) {
			$items[] = array(
				'label' => $form->name,
				'value' => $form->id,
			);
		}
		?>
<table class="form-table" id="embed_generator_table">
	<tbody>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'standalone_form_id', __( 'Select Form', 'ipt_fsqm' ) ); ?>
			</th>
			<td>
				<div class="ipt_uif_float_right" style="height: 68px; width: 200px;">
					<?php $this->ui->ajax_loader( true, 'ipt_fsqm_embed_generator_al', array(), true ); ?>
				</div>
				<?php $this->ui->select( 'standalone_form_id', $items, '' ); ?>
			</td>
			<td>
				<?php $this->ui->help_head( __( 'Embed Code', 'ipt_fsqm' ) ); ?>
				<p><?php _e( 'Embed codes are useful for embedding your forms on some external sites. Think of it as a YouTube share/embed code.', 'ipt_fsqm' ); ?></p>
				<p><?php _e( 'To use it simply select a form and select width and height. The system will generate the code automatically. Press <kbd>Ctrl</kbd> + <kbd>c</kbd> to copy. Paste it where you want.' ) ?></p>
				<p><?php _e( 'You can also use the URL to link to the standalone page.', 'ipt_fsqm' ); ?></p>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'standalone_width', __( 'Width', 'ipt_fsqm' ) ); ?>
			</th>
			<td colspan="2">
				<?php $this->ui->slider( 'standalone_width', '960', '320', '2560', '20' ); ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'standalone_height', __( 'Height', 'ipt_fsqm' ) ); ?>
			</th>
			<td colspan="2">
				<?php $this->ui->slider( 'standalone_height', '480', '320', '2560', '20' ); ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'standalone_permalink', __( 'Permalink', 'ipt_fsqm' ) ); ?>
			</th>
			<td colspan="2">
				<?php $this->ui->text( 'standalone_permalink', $default_permalink['url'], __( 'Adjust settings to update this', 'ipt_fsqm' ), 'large', 'normal', 'code' ) ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'standalone_shortlink', __( 'Short Link', 'ipt_fsqm' ) ); ?>
			</th>
			<td colspan="2">
				<?php $this->ui->text( 'standalone_shortlink', $default_permalink['shortlink'], __( 'Adjust settings to update this', 'ipt_fsqm' ), 'large', 'normal', 'code' ) ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'standalone_code', __( 'Embed Code', 'ipt_fsqm' ) ); ?>
			</th>
			<td colspan="2">
				<?php $this->ui->textarea( 'standalone_code', $default_code, __( 'Adjust settings to update this', 'ipt_fsqm' ), 'widefat', 'normal', 'code' ); ?>
			</td>
		</tr>
	</tbody>
</table>
<div class="clear"></div>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#standalone_code, #standalone_permalink, #standalone_shortlink').on('focus', function() {
			var $this = $(this);
			$this.select();

			$this.on('mouseup', function() {
				$this.off('mouseup');
				return false;
			});
		});
		$('#standalone_form_id').on('change keyup', function() {
			generate_embed();
		});

		$('#embed_generator_table').on('slidestop', function() {
			generate_embed();
		});

		var generate_embed = function() {
			var form_id = $('#standalone_form_id').val(),
			width = $('#standalone_width').val(),
			height = $('#standalone_height').val(),
			permalink = $('#standalone_permalink'),
			shortlink = $('#standalone_shortlink'),
			code = $('#standalone_code'),
			ajax_loader = $('#ipt_fsqm_embed_generator_al'),
			self = $(this);

			// Get the query parameters
			var data = {
				action : 'ipt_fsqm_standalone_embed_generate',
				form_id : form_id
			};

			ajax_loader.fadeIn('fast');

			// Query it
			$.get(ajaxurl, data, function(response) {
				if ( response == false || response === null ) {
					alert('Invalid Form Selected');
					return;
				}

				var embed_code = '<iframe src="' + response.url + '" width="' + width + '" height="' + height + '" style="width: ' + width + 'px; height: ' + height + 'px; border: 0 none; overflow-y: auto;" frameborder="0">&nbsp;</iframe>';
				code.text(embed_code);
				permalink.val(response.url);
				shortlink.val(response.shortlink);
				code.trigger('focus');
			}, 'json').fail(function() {
				alert('AJAX Error');
			}).always(function() {
				ajax_loader.fadeOut('fast');
			});
		}
	});
</script>
		<?php
	}

	public function meta_thank_you() {
		global $ipt_fsqm_info;
?>
<p>
	<?php _e( 'Thank you for Purchasing eForm Plugin.', 'ipt_fsqm' ); ?>
</p>
<ul class="ipt_uif_ul_menu">
	<li><a href="http://wpquark.com/kb/fsqm/fsqm-video-tutorials/"><i class="ipt-icomoon-play"></i> <?php _e( 'Getting Started', 'ipt_fsqm' ) ?></a></li>
	<li><a href="http://wpquark.com/kb/fsqm/"><i class="ipt-icomoon-file3"></i> <?php _e( 'Documentation', 'ipt_fsqm' ); ?></a></li>
	<li><a href="http://wpquark.com/kb/support/forum/wordpress-plugins/wp-feedback-survey-quiz-manager-pro/"><i class="ipt-icomoon-support"></i> <?php _e( 'Get Support', 'ipt_fsqm' ); ?></a></li>
</ul>
<?php $this->ui->help_head( __( 'Plugin Version', 'ipt_fsqm' ), true ); ?>
	<?php _e( 'If the Script version and DB version do not match, then deactivate the plugin and reactivate again. This should solve the problem. If the problem persists then contact the developer.', 'ipt_fsqm' ); ?>
<?php $this->ui->help_tail(); ?>
<p>
	<?php printf( __( '<strong>Plugin Version:</strong> <em>%s(Script)/%s(DB)</em>', 'ipt_fsqm' ), IPT_FSQM_Loader::$version, $ipt_fsqm_info['version'] ); ?> | <?php _e( 'Icons Used from: ', 'ipt_fsqm' ); ?> <a href="http://icomoon.io/" title="IcoMoon" target="_blank">IcoMoon</a>
</p>
<div class="clear"></div>
		<?php
	}

	public function meta_stat() {
		global $wpdb, $ipt_fsqm_info;
		$today = current_time( 'timestamp' );
		$forms = $wpdb->get_results( "SELECT id, name FROM {$ipt_fsqm_info['form_table']} ORDER BY id ASC", ARRAY_A );
		$info = array();
		$valid_forms = array();
		for ( $i = 30; $i >= 0; $i-- ) {
			$thedate = date( 'Y-m-d', mktime( 0, 0, 0, date( 'm', $today ), date( 'd', $today ) - $i, date( 'Y', $today ) ) );
			$start_date = $thedate . ' 00:00:00';
			$end_date = $thedate . ' 23:59:59';
			//var_dump($thedate, $start_date, $end_date);
			$info[$thedate] = array();
			$total = 0;

			$counts = $wpdb->get_results( $wpdb->prepare( "SELECT COUNT(id) count, form_id FROM {$ipt_fsqm_info['data_table']} WHERE date <= %s AND date >= %s GROUP BY form_id HAVING count > 0", $end_date, $start_date ), ARRAY_A );

			//var_dump($counts);
			foreach ( (array) $counts as $count ) {
				$info[$thedate][$count['form_id']] = (int) $count['count'];
				$total += $count['count'];
				$valid_forms[] = $count['form_id'];
			}

			//ksort( $info[$thedate] );
			$info[$thedate]['total'] = $total;
		}
		if ( empty( $valid_forms ) ) {
			echo '<div style="height: 300px;">';
			$this->ui->msg_error( __( 'No submissions for past 30 days. Please be patient.', 'ipt_fsqm' ) );
			echo '</div>';
			return;
		}
		$valid_forms = array_unique( $valid_forms );

		sort( $valid_forms );

		$json = array();
		$json[0] = array();
		$json[0][0] = __( 'Date', 'ipt_fsqm' );
		foreach ( $forms as $form ) {
			if ( !in_array( $form['id'], $valid_forms ) ) {
				continue;
			}
			$json[0][] = $form['name'];
		}
		$json[0][] = __( 'Total', 'ipt_fsqm' );
		$i = 1;
		foreach ( $info as $date => $count_data ) {
			$json[$i][0] = $date;
			foreach ( $valid_forms as $form ) {
				$json[$i][] = isset( $count_data[$form] ) ? $count_data[$form] : 0;
			}
			$json[$i][] = $count_data['total'];
			$i++;
		}

		//var_dump($json);
?>
<?php $this->ui->ajax_loader( false, 'ipt_fsqm_ten_stat', array(), true ); ?>
<script type="text/javascript">

function drawLatestTen() {
	var data = google.visualization.arrayToDataTable(<?php echo json_encode( $json ); ?>);

	var ac = new google.visualization.ComboChart(document.getElementById('ipt_fsqm_ten_stat'));
	ac.draw(data, {
		title : '<?php _e( 'Last 30 days form submission statistics', 'ipt_fsqm' ); ?>',
		height : 300,
		vAxis : {title : '<?php _e( 'Submission Hits', 'ipt_fsqm' ) ?>'},
		hAxis : {title : '<?php _e( 'Date', 'ipt_fsqm' ); ?>'},
		seriesType : 'bars',
		series : {<?php echo count( $json[0] ) - 2; ?> : {type : 'line'}},
		legend : {position : 'top'},
		tooltip : {isHTML : true}
	});
}

</script>
		<?php
	}

	public function meta_overall() {
		global $wpdb, $ipt_fsqm_info;
		$query = "SELECT f.name name, COUNT(d.id) subs FROM {$ipt_fsqm_info['form_table']} f LEFT JOIN {$ipt_fsqm_info['data_table']} d ON f.id = d.form_id GROUP BY f.id HAVING subs > 0";
		$json = array();
		$json[] = array( __( 'Form', 'ipt_fsqm' ), __( 'Submissions', 'ipt_fsqm' ) );
		$db_data = $wpdb->get_results( $query );

		if ( !empty( $db_data ) ) {
			foreach ( $db_data as $db ) {
				if ( $db->subs == 0 ) {
					continue;
				}
				$json[] = array( $db->name, (int) $db->subs );
			}
		} else {
			echo '<div style="height: 300px;">';
			$this->ui->msg_error( __( 'No submissions yet. Please be patient.', 'ipt_fsqm' ) );
			echo '</div>';
			return;
		}
?>
<?php $this->ui->ajax_loader( false, 'ipt_fsqm_pie_stat', array(), true ); ?>
<script type="text/javascript">

function drawOverallPie() {
	var data = google.visualization.arrayToDataTable(<?php echo json_encode( $json ); ?>);

	var ac = new google.visualization.PieChart(document.getElementById('ipt_fsqm_pie_stat'));
	ac.draw(data, {
		title : '<?php _e( 'Overall form submission statistics', 'ipt_fsqm' ); ?>',
		height : 300,
		is3D : true,
		legend : {position : 'right'},
		tooltip : {isHTML : true}
	});
}

</script>
		<?php
	}

	/**
	 *
	 *
	 * @global wpdb $wpdb
	 * @global array $ipt_fsqm_info
	 */
	public function meta_ten() {
		global $wpdb, $ipt_fsqm_info;
		$rows = $wpdb->get_results( "SELECT d.id id, d.f_name f_name, d.l_name l_name, d.email email, d.phone phone, d.ip ip, d.date date, d.star star, d.comment comment, f.name name, f.id form_id FROM {$ipt_fsqm_info['data_table']} d LEFT JOIN {$ipt_fsqm_info['form_table']} f ON d.form_id = f.id ORDER BY d.date DESC LIMIT 0,10", ARRAY_A );
?>
<table class="widefat">
	<thead>
		<tr>
			<th scope="col">
				<img src="<?php echo plugins_url( '/static/admin/images/star_on.png', IPT_FSQM_Loader::$abs_file ); ?>" />
			</th>
			<th scope="col">
				<?php _e( 'Name', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'Email', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'Phone', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'Date', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'IP Address', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'Form', 'ipt_fsqm' ); ?>
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th scope="col">
				<img src="<?php echo plugins_url( '/static/admin/images/star_on.png', IPT_FSQM_Loader::$abs_file ); ?>" />
			</th>
			<th scope="col">
				<?php _e( 'Name', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'Email', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'Phone', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'Date', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'IP Address', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'Form', 'ipt_fsqm' ); ?>
			</th>
		</tr>
	</tfoot>
	<tbody>
		<?php if ( empty( $rows ) ) : ?>
		<tr>
			<td colspan="7"><?php _e( 'No submissions yet', 'ipt_fsqm' ); ?></td>
		</tr>
		<?php else : ?>
		<?php foreach ( $rows as $item ) : ?>
		<tr>
			<th scope="row"><img src="<?php echo plugins_url( $item['star'] == 1 ? '/static/admin/images/star_on.png' : '/static/admin/images/star_off.png', IPT_FSQM_Loader::$abs_file ) ?>" /></th>
			<td>
				<?php printf( '<strong><a class="thickbox" title="%s" href="admin-ajax.php?action=ipt_fsqm_quick_preview&id=' . $item['id'] . '&width=640&height=500">%s</a></strong>', sprintf( __( 'Submission of %s under %s', 'ipt_fsqm' ), $item['f_name'], $item['name'] ), $item['f_name'] . ' ' . $item['l_name'] ); ?>
			</td>
			<td>
				<?php if ( trim( $item['email'] ) !== '' ) : ?>
				<?php echo '<a href="mailto:' . $item['email'] . '">' . $item['email'] . '</a>'; ?>
				<?php else : ?>
				<?php _e( 'anonymous', 'ipt_fsqm' ); ?>
				<?php endif; ?>
			</td>
			<td>
				<?php echo $item['phone']; ?>
			</td>
			<td>
				<?php echo date_i18n( get_option( 'date_format' ) . __( ' \a\t ', 'ipt_fsqm' ) . get_option( 'time_format' ), strtotime( $item['date'] ) ); ?>
			</td>
			<td>
				<?php echo $item['ip']; ?>
			</td>
			<td>
			<?php if ( current_user_can( 'manage_feedback' ) ) : ?>
				<?php echo '<a href="admin.php?page=ipt_fsqm_view_all_submissions&form_id=' . $item['form_id'] . '">' . $item['name'] . '</a>'; ?>
			<?php else : ?>
				<?php echo $item['name']; ?>
			<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>
		<?php
	}

	public function on_load_page() {
		parent::on_load_page();

		get_current_screen()->add_help_tab( array(
			'id' => 'overview',
			'title' => __( 'Overview', 'ipt_fsqm' ),
			'content' =>
			'<p>' . __( 'Thank you for choosing eForm Plugin. This screen provides some basic information of the plugin and Latest Submission Statistics. The design is integrated from WordPress\' own framework. So you should feel like home!', 'ipt_fsqm' ) . '<p>' .
			'<p>' . __( 'The concept and working of the Plugin is very simple.', 'ipt_fsqm' ) . '</p>' .
			'<ul>' .
			'<li>' . __( 'You setup a form from the <a href="admin.php?page=ipt_fsqm_new_form">New Form</a>.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( 'You use the Shortcodes (check the Shortcodes tab on this help screen) for displaying on your Site/Blog. Simply create a page and you will see a new button added to your editor from where you can put the shortcodes automatically. If you want to use the codes manually, then check the Shortcode section of this help.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( 'Finally use the <a href="admin.php?page=ipt_fsqm_report">Report & Analysis</a> Or <a href="admin.php?page=ipt_fsqm_view_all_submissions">View all Submissions</a> pages to analyze the submissions.', 'ipt_fsqm' ) . '</li>' .
			'</ul>' .
			'<p>' . __( 'Sounds easy enough? Then get started by going to the <a href="admin.php?page=ipt_fsqm_new_form">New Form</a> now. You can always click on the <strong>HELP</strong> button above the screen to know more.', 'ipt_fsqm' ) . '</p>' .
			'<p>' . __( 'If you have any suggestions or have encountered any bug, please feel free to use the Linked support forum', 'ipt_fsqm' ) . '</p>',
		) );

		get_current_screen()->add_help_tab( array(
			'id' => 'shortcodes',
			'title' => __( 'Shortcodes', 'ipt_fsqm' ),
			'content' =>
			'<p>' . __( 'This plugin comes with three shortcodes. One for displaying the FORM and other for displaying the Trends (The same Latest 100 Survey Reports you see on this screen)', 'ipt_fsqm' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<code>[ipt_fsqm_form id="form_id"]</code> : Just use this inside a Post/Page and the form will start appearing.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<code>[ipt_fsqm_trends form_id="form_id"]</code> : Use this to show the Trends based on all available MCQs. Just like the <strong>Report & Analysis</strong>.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<code>[ipt_fsqm_trackback]</code> : A page from where your users can track their submission. If it is thre in the notification email, then the surveyee should receive a confirmation email with the link to the track page.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<code>[ipt_fsqm_utrackback]</code> : A central page from where your registered users can track all their submissions. It integrates with your wordpress users and if they are not logged in, it will simply show a login form.', 'ipt_fsqm' ) . '</li>' .
			'</ul>' .
			'<p>' . __( 'If the output of the shortcodes look weird, then probably you have copied them from the list above with the <code>&lt;code&gt;</code> HTML markup. Please delete them and manually write the shortcode.', 'ipt_fsqm' ) . '</p>',
		) );

		get_current_screen()->add_help_tab( array(
			'id' => 'credits',
			'title' => __( 'Credits', 'ipt_fsqm' ),
			'content' =>
			'<p>' . __( 'This is a Pro version of the Free <a href="http://wordpress.org/extend/plugins/wp-feedback-survey-manager/">WP Feedback & Survey Manager</a> Plugin.', 'ipt_fsqm' ) . '</p>' .
			'<p>' . __( 'The plugin uses a few free and/or open source products, which are:', 'ipt_fsqm' ) .
			'<ul>' .
			'<li>' . __( '<strong><a href="http://www.google.com/webfonts/">Google WebFont</a></strong> : To make the form look better.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong><a href="http://jqueryui.com/">jQuery UI</a></strong> : Renders many elements along with the "Tab Like" appearance of the form.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong><a href="https://developers.google.com/chart/">Google Charts Tool</a></strong> : Renders the report charts on both backend as well as frontend.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong><a href="https://github.com/posabsolute/jQuery-Validation-Engine">jQuery Validation Engine</a></strong> : Wonderful form validation plugin from Position-absolute.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>Icons</strong> : <a href="http://www.icomoon.io/" target="_blank">IcoMoon Icons</a> The wonderful and free collection of Font Icons.', 'ipt_fsqm' ) . '</li>' .
			'</ul>',
		) );

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'ipt_fsqm' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);
	}
}
