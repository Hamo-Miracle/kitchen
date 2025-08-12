<?php
/**
 * eForm Trends Widget
 *
 * Adds a widget to display trends/reports on sidebars
 *
 * @package eForm - WordPress Form Builder
 * @subpackage Widgets\Trends
 * @author Swashata Ghosh <swashata@ipanelthemes.com>
 * @codeCoverageIgnore
 */
class IPT_FSQM_Trends_Widget extends WP_Widget {

	/*==========================================================================
	 * Static methods
	 *========================================================================*/
	public static $instantiated = false;

	public static function init() {
		if ( true === self::$instantiated ) {
			return;
		}
		self::$instantiated = true;

		// We use lambda function because PHP 5.3 supports it
		// After introducing Payments Gateway
		// We needed to increase minimum PHP requirement to 5.3
		add_action( 'widgets_init', function() {
			register_widget( 'IPT_FSQM_Trends_Widget' );
		} );
	}


	/*==========================================================================
	 * Widget methods
	 *========================================================================*/

	/**
	 * Constructor
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'ipt_fsqm_trends_widget',
			'description' => __( 'Insert eForm reports and analytics to your sidebars.', 'ipt_fsqm' ),
		);
		parent::__construct( 'ipt_fsqm_trends_widget', __( 'eForm - Insert Trends', 'ipt_fsqm' ), $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		// Get widget arguments
		extract( $args, EXTR_SKIP );
		echo $before_widget;

		// Widget title
		$title = apply_filters( 'widget_title', $instance['title'] );

		if ( '' !== $title ) {
			echo $before_title;
			echo $title;
			echo $after_title;
		}

		// Create the parameters
		// Settings
		$settings = array(
			'form_id' => $instance['form_id'],
			'load' => $instance['load'],
			'report' => array(),
		);
		// Loop through the mcqs
		// And enable report type if needed
		$mcq_report = false;
		$mcqs = array();
		$cmeta = array(
			'mcq' => array(
				'charttype' => array(),
				'toggles' => array(),
			),
		);
		foreach ( $instance['mcqs'] as $m_key => $mcq ) {
			if ( true === $mcq['enabled'] ) {
				// Add to the report type
				if ( ! $mcq_report ) {
					$settings['report'][] = 'mcq';
					$mcq_report = true;
				}

				// Add to the list
				$mcqs[] = $m_key;

				// Add to the chart config
				if ( isset( $mcq['charttype'] ) ) {
					$cmeta['mcq']['charttype'][ $m_key ] = $mcq['charttype'];
				}
				if ( isset( $mcq['toggles'] ) ) {
					$cmeta['mcq']['toggles'][ $m_key ] = $mcq['toggles'];
				}
			}
		}

		// Loop through the pinfos
		// And enable report type if needed
		$pinfo_report = false;
		$pinfos = array();
		$cmeta['pinfo'] = array(
			'charttype' => array(),
			'toggles' => array(),
		);
		// complete the loop
		foreach ( $instance['pinfos'] as $p_key => $pinfo ) {
			if ( true === $pinfo['enabled'] ) {
				// Add to the report type
				if ( ! $pinfo_report ) {
					$settings['report'][] = 'pinfo';
					$pinfo_report = true;
				}

				// Add to the list
				$pinfos[] = $p_key;

				// Add to the chart config
				if ( isset( $pinfo['charttype'] ) ) {
					$cmeta['pinfo']['charttype'][ $p_key ] = $pinfo['charttype'];
				}
				if ( isset( $pinfo['toggles'] ) ) {
					$cmeta['pinfo']['toggles'][ $p_key ] = $pinfo['toggles'];
				}
			}
		}

		// Other presets
		$appearance = array(
			'wrap' => false,
			'heading' => ( true == $instance['heading'] ) ? true : false,
			'description' => false,
			'theader' => false,
			'tborder' => false,
			'material' => ( true == $instance['material'] ) ? true : false,
			'print' => ( true == $instance['print'] ) ? true : false,
		);

		$data = array(
			'data' => false,
			'others' => false,
			'names' => false,
			'date' => false,
		);
		$filters = wp_parse_args( $instance['filters'], array(
			'user_id' => array( '' ),
			'url_track' => array( '' ),
			'meta' => '',
			'mvalue' => '',
			'score' => array(
				'min' => '',
				'max' => '',
			),
			'custom_date_start' => '',
			'custom_date_end' => '',
		) );

		// Main Widget output
		$form = new IPT_FSQM_Form_Elements_Front( null, $instance['form_id'] );
		$utils = new IPT_FSQM_Form_Elements_Utilities( $instance['form_id'], $form->ui );


		ob_start();
		$form->container( array( array( $utils, 'report_generate_report' ), array( $settings, $mcqs, array(), $pinfos, false, false, false, false, false, $appearance, $cmeta, $filters, '' ) ), true, true );
		$output = ob_get_clean();

		if ( WP_DEBUG !== true ) {
			$output = IPT_FSQM_Minify_HTML::minify( $output );
		}

		echo $output;

		echo $after_widget;
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		global $wpdb, $ipt_fsqm_info;

		// Parse the default form settings
		$instance = wp_parse_args( (array) $instance, array(
			'title' => '',
			'form_id' => '0',
			'load' => '1',
			'material' => false,
			'heading' => false,
			'print' => false,
			'mcqs' => array(),
			'pinfos' => array(),
			'filters' => array(
				'user_id' => array( '' ),
				'url_track' => array( '' ),
				'meta' => '',
				'mvalue' => '',
				'score' => array(
					'min' => '',
					'max' => '',
				),
				'custom_date_start' => '',
				'custom_date_end' => '',
			),
			'mcqwrap' => '0',
			'pinfowrap' => '0',
			'filterwrap' => '0',
		) );
		$forms = (array) IPT_FSQM_Form_Elements_Static::get_forms_for_select();

		// Process the chart types beforehand
		$chart_types_n_toggles = IPT_FSQM_Form_Elements_Static::get_chart_type_n_toggles();
		$chart_lists = IPT_FSQM_Form_Elements_Static::get_chart_elements( $instance['form_id'] );

		// Loop through mcqs and process
		$mcqs = array();
		foreach ( $chart_lists['mcqs'] as $mcq ) {
			// Add the main array
			if ( isset( $instance['mcqs'][ $mcq['key'] ] ) ) {
				$mcqs[ $mcq['key'] ] = $instance['mcqs'][ $mcq['key'] ];
			} else {
				$mcqs[ $mcq['key'] ] = array(
					'enabled' => true,
				);
			}

			// Add chart type
			if ( isset( $chart_types_n_toggles['possible_chart_types'][ $mcq['type'] ] )
				&& ! isset( $instance['mcqs'][ $mcq['key'] ]['charttype'] )
				) {
				$array_keys = array_keys( $chart_types_n_toggles['possible_chart_types'][ $mcq['type'] ] );
				$mcqs[ $mcq['key'] ]['charttype'] = $array_keys[0];
				unset( $array_keys );
			}

			// Add toggles
			if ( isset( $chart_types_n_toggles['possible_toggle_types'][ $mcq['type'] ] )
				&& ! isset( $instance['mcqs'][ $mcq['key'] ]['toggles'] )
				) {
				$mcqs[ $mcq['key'] ]['toggles'] = array();
				foreach ( $chart_types_n_toggles['possible_toggle_types'][ $mcq['type'] ] as $toggle ) {
					$mcqs[ $mcq['key'] ]['toggles'][ $toggle ] = true;
				}
			}
		}
		$instance['mcqs'] = $mcqs;

		// Loop through pinfos and process
		$pinfos = array();
		foreach ( $chart_lists['pinfos'] as $pinfo ) {
			// Add the main array
			if ( isset( $instance['pinfos'][ $pinfo['key'] ] ) ) {
				$pinfos[ $pinfo['key'] ] = $instance['pinfos'][ $pinfo['key'] ];
			} else {
				$pinfos[ $pinfo['key'] ] = array(
					'enabled' => true,
				);
			}

			// Add chart type
			if ( isset( $chart_types_n_toggles['possible_chart_types'][ $pinfo['type'] ] )
				&& ! isset( $instance['pinfos'][ $pinfo['key'] ]['charttype'] )
				) {
				$array_keys = array_keys( $chart_types_n_toggles['possible_chart_types'][ $pinfo['type'] ] );
				$pinfos[ $pinfo['key'] ]['charttype'] = $array_keys[0];
				unset( $array_keys );
			}

			// Add toggles
			if ( isset( $chart_types_n_toggles['possible_toggle_types'][ $pinfo['type'] ] )
				&& ! isset( $instance['pinfos'][ $pinfo['key'] ]['toggles'] )
				) {
				$pinfos[ $pinfo['key'] ]['toggles'] = array();
				foreach ( $chart_types_n_toggles['possible_toggle_types'][ $pinfo['type'] ] as $toggle ) {
					$pinfos[ $pinfo['key'] ]['toggles'][ $toggle ] = true;
				}
			}
		}
		$instance['pinfos'] = $pinfos;

		// Prepare the filters
		$user_ids = array();
		$url_tracking = array();
		$least_date = '';
		$recent_date = '';
		// If a form has been selected
		if ( '0' != $instance['form_id'] ) {
			$user_ids = $wpdb->get_col( $wpdb->prepare( "SELECT distinct user_id FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d", $instance['form_id'] ) );
			$url_tracking = $wpdb->get_col( $wpdb->prepare( "SELECT distinct url_track FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d", $instance['form_id'] ) );
			$least_date = $wpdb->get_var( $wpdb->prepare( "SELECT date FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d ORDER BY date ASC LIMIT 0,1", $instance['form_id'] ) );
			$recent_date = $wpdb->get_var( $wpdb->prepare( "SELECT date FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d ORDER BY date DESC LIMIT 0,1", $instance['form_id'] ) );
		}

		$server_loads = array(
			array(
				'label' => __( 'Light Load', 'ipt_fsqm' ),
				'value' => '0',
			),
			array(
				'label' => __( 'Moderate Load (Recommended for Shared Hostings)', 'ipt_fsqm' ),
				'value' => '1',
			),
			array(
				'label' => __( 'Heavy Load (Recommended for VPS or Dedicated Hostings)', 'ipt_fsqm' ),
				'value' => '2',
			),
		);
		?>
		<div class="ipt_fsqm_tw_outer">
			<input type="hidden" class="ipt_fsqm_tw_mcqwrap" value="<?php echo (int) $instance['mcqwrap'] ?>" name="<?php echo $this->get_field_name( 'mcqwrap' ) ?>" />
			<input type="hidden" class="ipt_fsqm_tw_pinfowrap" value="<?php echo (int) $instance['pinfowrap'] ?>" name="<?php echo $this->get_field_name( 'pinfowrap' ) ?>" />
			<input type="hidden" class="ipt_fsqm_tw_filterwrap" value="<?php echo (int) $instance['filterwrap'] ?>" name="<?php echo $this->get_field_name( 'filterwrap' ) ?>" />
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><strong><?php _e( 'Title', 'ipt_kb' ) ?></strong></label>
				<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>" id="<?php echo $this->get_field_id( 'title' ); ?>" value="<?php echo esc_html( $instance['title'] ); ?>" />
			</p>
			<p>
				<input type="checkbox" name="<?php echo $this->get_field_name( 'heading' ); ?>" id="<?php echo $this->get_field_id( 'heading' ); ?>" value="1" <?php checked( $instance['heading'] ); ?> />
				<label for="<?php echo $this->get_field_id( 'heading' ); ?>"><?php _e( 'Show heading (element title) above chart', 'ipt_fsqm' ); ?></label>
			</p>
			<p>
				<input type="checkbox" name="<?php echo $this->get_field_name( 'print' ); ?>" id="<?php echo $this->get_field_id( 'print' ); ?>" value="1" <?php checked( $instance['print'] ); ?> />
				<label for="<?php echo $this->get_field_id( 'print' ); ?>"><?php _e( 'Show print button', 'ipt_fsqm' ); ?></label>
			</p>
			<p>
				<input type="checkbox" name="<?php echo $this->get_field_name( 'material' ); ?>" id="<?php echo $this->get_field_id( 'material' ); ?>" value="1" <?php checked( $instance['material'] ); ?> />
				<label for="<?php echo $this->get_field_id( 'material' ); ?>"><?php _e( 'Show material design chart (when applicable)', 'ipt_fsqm' ); ?></label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'load' ); ?>"><?php _e( 'Server Load', 'ipt_fsqm' ); ?></label>
				<select class="widefat ipt_fsqm_tw_load" name="<?php echo $this->get_field_name( 'load' ); ?>" id="<?php echo $this->get_field_id( 'load' ); ?>">
					<?php foreach ( $server_loads as $sl ) : ?>
					<option value="<?php echo $sl['value']; ?>"<?php selected( $instance['load'], $sl['value'], true ) ?>><?php echo $sl['label']; ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'form_id' ); ?>"><?php _e( 'Select Form', 'ipt_fsqm' ); ?></label>
				<select class="widefat ipt_fsqm_tw_form_id" name="<?php echo $this->get_field_name( 'form_id' ); ?>" id="<?php echo $this->get_field_id( 'form_id' ); ?>">
					<option value="0"<?php selected( $instance['form_id'], '0', true ); ?>><?php _e( '--please select a form--', 'ipt_fsqm' ); ?></option>
					<?php if ( ! empty( $forms ) ) : ?>
					<?php foreach ( $forms as $form ) : ?>
					<option value="<?php echo $form->id; ?>"<?php selected( $instance['form_id'], $form->id, true ) ?>><?php echo $form->name; ?></option>
					<?php endforeach; ?>
					<?php endif; ?>
				</select>
			</p>
			<?php if ( '0' == $instance['form_id'] ) : ?>
				<p><span class="description"><?php _e( 'Please select a form and SAVE in order to continue', 'ipt_fsqm' ); ?></span></p>
			<?php else : ?>
				<p><span class="description"><?php _e( 'Changing the form will reset the following data.', 'ipt_fsqm' ); ?></span></p>

				<?php // Add the mcq list ?>
				<div class="ipt_fsqm_tw_mcqs ipt_fsqm_tw" data-target="ipt_fsqm_tw_mcqwrap">
					<h2 class="ipt_fsqm_tw_qlist_toggle" title="<?php _e( 'Click to expand/collapse', 'ipt_fsqm' ); ?>"><i class="dashicons dashicons-chart-area"></i> <?php _e( 'Select MCQs', 'ipt_fsqm' ); ?></h2>
					<div class="ipt_fsqm_tw_qlist_wrap" style="display: <?php echo ( '0' == $instance['mcqwrap'] ) ? 'none' : 'block'; ?>;">
						<?php foreach ( $chart_lists['mcqs'] as $mcq ) : ?>
							<div id="ipt_fsqm_tw_mcq_<?php echo $mcq['key']; ?>" class="ipt_fsqm_tw_qlist <?php echo ( true !== $instance['mcqs'][ $mcq['key'] ]['enabled'] ) ? 'qlist_hidden' : ''; ?>">
								<h3><input class="ipt_fsqm_tw_mcq_elm ipt_fsqm_tw_elm" type="checkbox" <?php checked( $instance['mcqs'][ $mcq['key'] ]['enabled'] ); ?> name="<?php echo $this->get_field_name( 'mcqs[' . $mcq['key'] . '][enabled]' ); ?>" id="<?php echo $this->get_field_id( 'mcqs[' . $mcq['key'] . '][enabled]' ); ?>" value="1" />
								<label for="<?php echo $this->get_field_id( 'mcqs[' . $mcq['key'] . '][enabled]' ); ?>"><?php echo $mcq['title']; ?></label></h3>

								<div class="ipt_fsqm_tw_cmeta">
									<?php // Add charttype ?>
									<?php if ( isset( $chart_types_n_toggles['possible_chart_types'][ $mcq['type'] ] ) && isset( $instance['mcqs'][ $mcq['key'] ]['charttype'] ) ) : ?>
										<div class="ipt_fsqm_tw_ctype">
											<label for="<?php echo $this->get_field_id( 'mcqs[' . $mcq['key'] . '][charttype]' ); ?>"><?php _e( 'Select Chart', 'ipt_fsqm' ); ?></label>
											<select class="" name="<?php echo $this->get_field_name( 'mcqs[' . $mcq['key'] . '][charttype]' ); ?>" id="<?php echo $this->get_field_id( 'mcqs[' . $mcq['key'] . '][charttype]' ); ?>">
												<?php foreach ( $chart_types_n_toggles['possible_chart_types'][ $mcq['type'] ] as $chart => $clabel ) : ?>
													<option value="<?php echo esc_attr( $chart ); ?>" <?php selected( $instance['mcqs'][ $mcq['key'] ]['charttype'], $chart ); ?>><?php echo $clabel; ?></option>
												<?php endforeach; ?>
											</select>
										</div>
									<?php endif; ?>

									<?php // Add Toggles ?>
									<?php if ( isset( $chart_types_n_toggles['possible_toggle_types'][ $mcq['type'] ] ) && isset( $instance['mcqs'][ $mcq['key'] ]['toggles'] ) ) : ?>
										<div class="ipt_fsqm_tw_toggles">
											<?php foreach ( $chart_types_n_toggles['possible_toggle_types'][ $mcq['type'] ] as $toggle ) : ?>
												<p><input type="checkbox" <?php checked( $instance['mcqs'][ $mcq['key'] ]['toggles'][ $toggle ] ); ?> name="<?php echo $this->get_field_name( 'mcqs[' . $mcq['key'] . '][toggles][' . $toggle . ']' ); ?>" id="<?php echo $this->get_field_id( 'mcqs[' . $mcq['key'] . '][toggles][' . $toggle . ']' ); ?>" value="1" />
												<label for="<?php echo $this->get_field_id( 'mcqs[' . $mcq['key'] . '][toggles][' . $toggle . ']' ); ?>"><?php echo $chart_types_n_toggles['toggle_labels'][ $toggle ]; ?></label></p>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
								</div>

							</div>
						<?php endforeach; ?>
						<?php if ( empty( $chart_lists['mcqs'] ) ) : ?>
						<p><span class="description"><?php _e( 'No Elements found in your form', 'ipt_fsqm' ); ?></span></p>
						<?php endif; ?>
					</div>
				</div>

				<div class="ipt_fsqm_tw_pinfos ipt_fsqm_tw" data-target="ipt_fsqm_tw_pinfowrap">
					<?php // Add the pinfo list ?>
					<h2 class="ipt_fsqm_tw_qlist_toggle" title="<?php _e( 'Click to expand/collapse', 'ipt_fsqm' ); ?>"><i class="dashicons dashicons-chart-area"></i> <?php _e( 'Select Other Elements', 'ipt_fsqm' ); ?></h2>
					<div class="ipt_fsqm_tw_qlist_wrap" style="display: <?php echo ( '0' == $instance['pinfowrap'] ) ? 'none' : 'block'; ?>;">
						<?php foreach ( $chart_lists['pinfos'] as $pinfo ) : ?>
							<div id="ipt_fsqm_tw_pinfo_<?php echo $pinfo['key']; ?>" class="ipt_fsqm_tw_qlist <?php echo ( true !== $instance['pinfos'][ $pinfo['key'] ]['enabled'] ) ? 'qlist_hidden' : ''; ?>">
								<h3><input class="ipt_fsqm_tw_mcq_elm ipt_fsqm_tw_elm" type="checkbox" <?php checked( $instance['pinfos'][ $pinfo['key'] ]['enabled'] ); ?> name="<?php echo $this->get_field_name( 'pinfos[' . $pinfo['key'] . '][enabled]' ); ?>" id="<?php echo $this->get_field_id( 'pinfos[' . $pinfo['key'] . '][enabled]' ); ?>" value="1" />
								<label for="<?php echo $this->get_field_id( 'pinfos[' . $pinfo['key'] . '][enabled]' ); ?>"><?php echo $pinfo['title']; ?></label></h3>

								<div class="ipt_fsqm_tw_cmeta">
									<?php // Add charttype ?>
									<?php if ( isset( $chart_types_n_toggles['possible_chart_types'][ $pinfo['type'] ] ) && isset( $instance['pinfos'][ $pinfo['key'] ]['charttype'] ) ) : ?>
										<div class="ipt_fsqm_tw_ctype">
											<label for="<?php echo $this->get_field_id( 'pinfos[' . $pinfo['key'] . '][charttype]' ); ?>"><?php _e( 'Select Chart', 'ipt_fsqm' ); ?></label>
											<select class="" name="<?php echo $this->get_field_name( 'pinfos[' . $pinfo['key'] . '][charttype]' ); ?>" id="<?php echo $this->get_field_id( 'pinfos[' . $pinfo['key'] . '][charttype]' ); ?>">
												<?php foreach ( $chart_types_n_toggles['possible_chart_types'][ $pinfo['type'] ] as $chart => $clabel ) : ?>
													<option value="<?php echo esc_attr( $chart ); ?>" <?php selected( $instance['pinfos'][ $pinfo['key'] ]['charttype'], $chart ); ?>><?php echo $clabel; ?></option>
												<?php endforeach; ?>
											</select>
										</div>
									<?php endif; ?>

									<?php // Add Toggles ?>
									<?php if ( isset( $chart_types_n_toggles['possible_toggle_types'][ $pinfo['type'] ] ) && isset( $instance['pinfos'][ $pinfo['key'] ]['toggles'] ) ) : ?>
										<div class="ipt_fsqm_tw_toggles">
											<?php foreach ( $chart_types_n_toggles['possible_toggle_types'][ $pinfo['type'] ] as $toggle ) : ?>
												<p><input type="checkbox" <?php checked( $instance['pinfos'][ $pinfo['key'] ]['toggles'][ $toggle ] ); ?> name="<?php echo $this->get_field_name( 'pinfos[' . $pinfo['key'] . '][toggles][' . $toggle . ']' ); ?>" id="<?php echo $this->get_field_id( 'pinfos[' . $pinfo['key'] . '][toggles][' . $toggle . ']' ); ?>" value="1" />
												<label for="<?php echo $this->get_field_id( 'pinfos[' . $pinfo['key'] . '][toggles][' . $toggle . ']' ); ?>"><?php echo $chart_types_n_toggles['toggle_labels'][ $toggle ]; ?></label></p>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
						<?php if ( empty( $chart_lists['pinfos'] ) ) : ?>
						<p><span class="description"><?php _e( 'No Elements found in your form', 'ipt_fsqm' ); ?></span></p>
						<?php endif; ?>
					</div>
				</div>

				<div class="ipt_fsqm_tw_filters ipt_fsqm_tw" data-target="ipt_fsqm_tw_filterwrap">
					<h2 class="ipt_fsqm_tw_qlist_toggle" title="<?php _e( 'Click to expand/collapse', 'ipt_fsqm' ); ?>"><i class="dashicons dashicons-filter"></i> <?php _e( 'Report Filters', 'ipt_fsqm' ); ?></h2>
					<div class="ipt_fsqm_tw_qlist_wrap" style="display: <?php echo ( '0' == $instance['filterwrap'] ) ? 'none' : 'block'; ?>;">
						<p>
							<label for="<?php echo $this->get_field_id( 'filters[user_id][]' ); ?>"><?php _e( 'Select Users <span class="description">Ctrl + hold for multiselect</span>', 'ipt_fsqm' ); ?></label>
							<select class="widefat" name="<?php echo $this->get_field_name( 'filters[user_id][]' ); ?>" id="<?php echo $this->get_field_id( 'filters[user_id][]' ); ?>" multiple="multiple">
								<option value="" <?php echo ( in_array( '', $instance['filters']['user_id'] ) ) ? 'selected="selected"' : ''; ?>><?php _e( 'Show for all users', 'ipt_fsqm' ); ?></option>
								<?php foreach ( $user_ids as $user_id ) : ?>
									<?php
									if ( '0' == $user_id ) {
										continue;
									}
									$userdata = get_userdata( $user_id );
									$label = '';
									if ( ! is_wp_error( $userdata ) && is_object( $userdata ) ) {
										$label = $userdata->user_nicename;
									} else {
										$label = sprintf( __( '(Deleted User) ID: %s', 'ipt_fsqm' ), $user_id );
									}
									?>
									<option value="<?php echo $user_id; ?>" <?php echo ( in_array( $user_id, $instance['filters']['user_id'] ) ) ? 'selected="selected"' : ''; ?>><?php echo $label; ?></option>
								<?php endforeach; ?>
							</select>
						</p>
						<p>
							<label for="<?php echo $this->get_field_id( 'filters[url_track][]' ); ?>"><?php _e( 'Select URL Tracks <span class="description">Ctrl + hold for multiselect</span>', 'ipt_fsqm' ); ?></label>
							<select class="widefat" name="<?php echo $this->get_field_name( 'filters[url_track][]' ); ?>" id="<?php echo $this->get_field_id( 'filters[url_track][]' ); ?>" multiple="multiple">
								<option value="" <?php echo ( in_array( '', $instance['filters']['url_track'] ) ) ? 'selected="selected"' : ''; ?>><?php _e( 'Show for all', 'ipt_fsqm' ); ?></option>
								<?php foreach ( $url_tracking as $ut ) : ?>
									<?php
									if ( '' == $ut ) {
										continue;
									}
									?>
									<option value="<?php echo esc_attr( $ut ); ?>" <?php echo ( in_array( $ut, $instance['filters']['url_track'] ) ) ? 'selected="selected"' : ''; ?>><?php echo esc_textarea( $ut ); ?></option>
								<?php endforeach; ?>
							</select>
						</p>
						<p>
							<label for="<?php echo $this->get_field_id( 'filters[meta]' ); ?>"><?php _e( 'User Meta Key', 'ipt_fsqm' ); ?></label>
							<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'filters[meta]' ); ?>" id="<?php echo $this->get_field_id( 'filters[meta]' ); ?>" value="<?php echo esc_textarea( $instance['filters']['meta'] ); ?>" />
							<span class="description"><?php _e( 'If you want to filter submissions based on user meta key, please specify the meta key here. If the meta value is left empty, then system would check for existence of meta key on a user, no matter the meta value.', 'ipt_fsqm' ); ?></span>
						</p>
						<p>
							<label for="<?php echo $this->get_field_id( 'filters[mvalue]' ); ?>"><?php _e( 'User Meta Value', 'ipt_fsqm' ); ?></label>
							<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'filters[mvalue]' ); ?>" id="<?php echo $this->get_field_id( 'filters[mvalue]' ); ?>" value="<?php echo esc_textarea( $instance['filters']['mvalue'] ); ?>" />
							<span class="description"><?php _e( 'If you have specified a meta key and would like to filter for a particular value, please specify here.', 'ipt_fsqm' ); ?></span>
						</p>
						<p>
							<label for="<?php echo $this->get_field_id( 'filters[score][min]' ); ?>"><?php _e( 'Score Obtained Range', 'ipt_fsqm' ); ?></label><br />
							<label for="<?php echo $this->get_field_id( 'filters[score][min]' ); ?>"><?php _e( 'Min', 'ipt_fsqm' ); ?></label>
							<input style="width: 100px; line-height: 1;" type="number" class="small" name="<?php echo $this->get_field_name( 'filters[score][min]' ); ?>" id="<?php echo $this->get_field_id( 'filters[score][min]' ); ?>" value="<?php echo esc_textarea( $instance['filters']['score']['min'] ); ?>" />
							<label for="<?php echo $this->get_field_id( 'filters[score][max]' ); ?>"><?php _e( 'Max', 'ipt_fsqm' ); ?></label>
							<input style="width: 100px; line-height: 1;" type="number" class="small" name="<?php echo $this->get_field_name( 'filters[score][max]' ); ?>" id="<?php echo $this->get_field_id( 'filters[score][max]' ); ?>" value="<?php echo esc_textarea( $instance['filters']['score']['max'] ); ?>" />
							<br />
							<span class="description"><?php _e( 'If you want to filter for a specific score range, then please mention it here. Minimum and maximum score are inclusive.', 'ipt_fsqm' ); ?></span>
						</p>
						<p>
							<label for="<?php echo $this->get_field_id( 'filters[custom_date_start]' ); ?>"><?php _e( 'Custom Date Range', 'ipt_fsqm' ); ?></label><br />
							<label for="<?php echo $this->get_field_id( 'filters[custom_date_start]' ); ?>"><?php _e( 'Start', 'ipt_fsqm' ); ?></label>
							<input style="width: 150px;" type="text" class="small" name="<?php echo $this->get_field_name( 'filters[custom_date_start]' ); ?>" id="<?php echo $this->get_field_id( 'filters[custom_date_start]' ); ?>" value="<?php echo esc_textarea( $instance['filters']['custom_date_start'] ); ?>" placeholder="<?php echo esc_attr( $least_date ); ?>" />
							<label for="<?php echo $this->get_field_id( 'filters[custom_date_end]' ); ?>"><?php _e( 'End', 'ipt_fsqm' ); ?></label>
							<input style="width: 150px;" type="text" class="small" name="<?php echo $this->get_field_name( 'filters[custom_date_end]' ); ?>" id="<?php echo $this->get_field_id( 'filters[custom_date_end]' ); ?>" value="<?php echo esc_textarea( $instance['filters']['custom_date_end'] ); ?>" placeholder="<?php echo esc_attr( $recent_date ); ?>" />
							<br />
							<span class="description"><?php _e( 'If you want to filter for a specific date range, then please mention it here. Start and End dates are inclusive. Format is <code>Y-m-d H:i:s</code>, i.e, <code>2016-7-27 17:32:03</code>.', 'ipt_fsqm' ); ?></span>
						</p>
					</div>
				</div>
				<p><span class="description"><?php _e( 'Click on the headings to customize this widget options', 'ipt_fsqm' ); ?></span></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		// Update the form
		$updated_instance = array();
		$updated_instance['title'] = isset( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : '';
		$updated_instance['form_id'] = isset( $new_instance['form_id'] ) ? (string) intval( $new_instance['form_id'] ) : '0';
		$updated_instance['load'] = isset( $new_instance['load'] ) ? (string) intval( $new_instance['load'] ) : '1';
		$updated_instance['heading'] = isset( $new_instance['heading'] ) ? true : false;
		$updated_instance['material'] = isset( $new_instance['material'] ) ? true : false;
		$updated_instance['print'] = isset( $new_instance['print'] ) ? true : false;

		if ( ( '0' == $updated_instance['form_id'] ) || ( $new_instance['form_id'] != $old_instance['form_id'] ) ) {
			$updated_instance['mcqs'] = array();
			$updated_instance['pinfos'] = array();
			$updated_instance['filters'] = array(
				'user_id' => array( '' ),
				'url_track' => array( '' ),
				'meta' => '',
				'mvalue' => '',
				'score' => array(
					'min' => '',
					'max' => '',
				),
				'custom_date_start' => '',
				'custom_date_end' => '',
			);
		} else {
			// Process the chart types beforehand
			$chart_types_n_toggles = IPT_FSQM_Form_Elements_Static::get_chart_type_n_toggles();
			$chart_lists = IPT_FSQM_Form_Elements_Static::get_chart_elements( $updated_instance['form_id'] );

			// Loop through and set the mcqs
			$mcqs = array();
			foreach ( $chart_lists['mcqs'] as $mcq ) {
				// Add the main array
				$mcqs[ $mcq['key'] ] = array(
					'enabled' => false,
				);
				if ( isset( $chart_types_n_toggles['possible_chart_types'][ $mcq['type'] ] ) ) {
					$array_keys = array_keys( $chart_types_n_toggles['possible_chart_types'][ $mcq['type'] ] );
					$mcqs[ $mcq['key'] ]['charttype'] = $array_keys[0];
					unset( $array_keys );
				}
				if ( isset( $chart_types_n_toggles['possible_toggle_types'][ $mcq['type'] ] ) ) {
					$mcqs[ $mcq['key'] ]['toggles'] = array();
				}

				// Check if enabled
				if ( isset( $new_instance['mcqs'][ $mcq['key'] ]['enabled'] ) ) {
					$mcqs[ $mcq['key'] ]['enabled'] = true;
				}

				// Set the chart type
				if ( isset( $chart_types_n_toggles['possible_chart_types'][ $mcq['type'] ] ) && isset( $new_instance['mcqs'][ $mcq['key'] ]['charttype'] ) ) {
					$mcqs[ $mcq['key'] ]['charttype'] = $new_instance['mcqs'][ $mcq['key'] ]['charttype'];
				}

				// Set toggles
				if ( isset( $chart_types_n_toggles['possible_toggle_types'][ $mcq['type'] ] ) ) {
					foreach ( $chart_types_n_toggles['possible_toggle_types'][ $mcq['type'] ] as $toggle ) {
						if ( isset( $new_instance['mcqs'][ $mcq['key'] ]['toggles'][ $toggle ] ) ) {
							$mcqs[ $mcq['key'] ]['toggles'][ $toggle ] = true;
						} else {
							$mcqs[ $mcq['key'] ]['toggles'][ $toggle ] = false;
						}
					}
				}
			}
			$updated_instance['mcqs'] = (array) $mcqs;

			// Loop through and set the pinfos
			$pinfos = array();
			foreach ( $chart_lists['pinfos'] as $pinfo ) {
				// Add the main array
				$pinfos[ $pinfo['key'] ] = array(
					'enabled' => false,
				);
				if ( isset( $chart_types_n_toggles['possible_chart_types'][ $pinfo['type'] ] ) ) {
					$array_keys = array_keys( $chart_types_n_toggles['possible_chart_types'][ $pinfo['type'] ] );
					$pinfos[ $pinfo['key'] ]['charttype'] = $array_keys[0];
					unset( $array_keys );
				}
				if ( isset( $chart_types_n_toggles['possible_toggle_types'][ $pinfo['type'] ] ) ) {
					$pinfos[ $pinfo['key'] ]['toggles'] = array();
				}

				// Check if enabled
				if ( isset( $new_instance['pinfos'][ $pinfo['key'] ]['enabled'] ) ) {
					$pinfos[ $pinfo['key'] ]['enabled'] = true;
				}

				// Set the chart type
				if ( isset( $chart_types_n_toggles['possible_chart_types'][ $pinfo['type'] ] ) && isset( $new_instance['pinfos'][ $pinfo['key'] ]['charttype'] ) ) {
					$pinfos[ $pinfo['key'] ]['charttype'] = $new_instance['pinfos'][ $pinfo['key'] ]['charttype'];
				}

				// Set toggles
				if ( isset( $chart_types_n_toggles['possible_toggle_types'][ $pinfo['type'] ] ) ) {
					foreach ( $chart_types_n_toggles['possible_toggle_types'][ $pinfo['type'] ] as $toggle ) {
						if ( isset( $new_instance['pinfos'][ $pinfo['key'] ]['toggles'][ $toggle ] ) ) {
							$pinfos[ $pinfo['key'] ]['toggles'][ $toggle ] = true;
						} else {
							$pinfos[ $pinfo['key'] ]['toggles'][ $toggle ] = false;
						}
					}
				}
			}
			$updated_instance['pinfos'] = (array) $pinfos;

			// Sanitize the filters
			$updated_instance['filters'] = wp_parse_args( (array) $new_instance['filters'], array(
				'user_id' => array( '' ),
				'url_track' => array( '' ),
				'meta' => '',
				'mvalue' => '',
				'score' => array(
					'min' => '',
					'max' => '',
				),
				'custom_date_start' => '',
				'custom_date_end' => '',
			) );
			if ( in_array( '', $updated_instance['filters']['user_id'] ) ) {
				$updated_instance['filters']['user_id'] = array( '' );
			} else {
				$updated_instance['filters']['user_id'] = array_map( 'strip_tags', $updated_instance['filters']['user_id'] );
			}
			if ( in_array( '', $updated_instance['filters']['url_track'] ) ) {
				$updated_instance['filters']['url_track'] = array( '' );
			} else {
				$updated_instance['filters']['url_track'] = array_map( 'strip_tags', $updated_instance['filters']['url_track'] );
			}
			// Reset user_id and url_track if form_id changes
			if ( '0' == $new_instance['form_id'] || $new_instance['form_id'] != $old_instance['form_id'] ) {
				$updated_instance['filters']['user_id'] = array( '' );
				$updated_instance['filters']['url_track'] = array( '' );
			}

			$updated_instance['filters']['meta'] = strip_tags( $updated_instance['filters']['meta'] );
			$updated_instance['filters']['mvalue'] = strip_tags( $updated_instance['filters']['mvalue'] );
			$updated_instance['filters']['score']['min'] = strip_tags( $updated_instance['filters']['score']['min'] );
			$updated_instance['filters']['score']['max'] = strip_tags( $updated_instance['filters']['score']['max'] );
			$updated_instance['filters']['custom_date_start'] = strip_tags( $updated_instance['filters']['custom_date_start'] );
			$updated_instance['filters']['custom_date_end'] = strip_tags( $updated_instance['filters']['custom_date_end'] );
		}

		// Add the collapse states
		$updated_instance['mcqwrap'] = (string) intval( $new_instance['mcqwrap'] );
		$updated_instance['pinfowrap'] = (string) intval( $new_instance['pinfowrap'] );
		$updated_instance['filterwrap'] = (string) intval( $new_instance['filterwrap'] );

		return $updated_instance;
	}
}
