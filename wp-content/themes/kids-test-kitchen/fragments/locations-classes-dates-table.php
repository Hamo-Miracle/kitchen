<?php

$output_rows = array();

foreach ( $locations as $location_id => $classes ) {
	$location = get_post( $location_id );

	$user_data_html = '';

	// Generate User Data, only when needed
	if ( !empty( $show_user_info )  && $show_user_info ) {
		$user_data = array();

		$user = get_user_by( 'ID', $location->post_author );

		$user_data = array(
			'User' => crb_get_user_edit_link( $user->ID ),
			'User Address' => carbon_get_user_meta( $user->ID, 'crb_address' ),
			'User City' => carbon_get_user_meta( $user->ID, 'crb_city' ),
			'User State' => carbon_get_user_meta( $user->ID, 'crb_state' ),
			'User Zip' => carbon_get_user_meta( $user->ID, 'crb_zip' ),
			'User Email' => $user->data->user_email,
			'User Phone' => carbon_get_user_meta( $user->ID, 'crb_phone' ),
		);
		$user_data = array_filter( $user_data );

		foreach ( $user_data as $label => $value ){
			$user_data[$label] = sprintf( '<strong>%s</strong>: %s', $label, $value );
		}

		$user_data_html = '<br />' . implode('<br />', $user_data );
	}

	$output_rows[] = array(
		array(
			'value' => '<strong>' . crb_get_post_edit_link( $location->ID ) . '</strong>' . $user_data_html,
			'rowspan' => 1,
		),
		array(
			'value' => 'Classes',
			'th' => true,
			'colspan' => '2',
		),
	);

	$last_location_index = count( $output_rows ) - 1;

	if ( !empty( $classes ) ) {
		foreach ( $classes as $class_id => $dates ) {
			$class = get_post( $class_id );
			$class_facilitator = carbon_get_post_meta( $class->ID, 'crb_class_facilitator' );
			$class_facilitator_display = "Facilitator: <strong style='color:green;'>Unassigned</strong><br/>";
			if($class_facilitator !== '0'){
				$user = get_userdata($class_facilitator);
				$class_facilitator_display = 'Facilitator: <strong>' . $user->display_name . '</strong> <br/>';
			}
			if(!$show_facilitator) 
			{
				$class_facilitator_display = '';
			}

			$output_rows[] = array(
				array(
					'value' => crb_get_post_edit_link( $class->ID ),
				),
				array(
					'value' => 'Dates',
					'th' => true,
				),
			);

			$last_class_index = count( $output_rows ) - 1;

			$confirmed_dates = array();
			$requested_dates = array();

			if ( !empty( $dates ) ) {
				foreach ( $dates as $date_id ) {
					$date       = get_post( $date_id );
					$start_date = carbon_get_post_meta( $date->ID, 'crb_date_start' );
					$end_time   = date( 'h:ia', strtotime( carbon_get_post_meta( $date->ID, 'crb_date_time_end' ) ) );
					$start_time = date( 'h:ia', strtotime( carbon_get_post_meta( $date->ID, 'crb_date_time_start' ) ) );
					$the_date   = sprintf( '%s (%s to %s)', $start_date, $start_time, $end_time );
					$facilitator  = carbon_get_post_meta( $date->ID, 'crb_date_facilitator' );
					
					if($show_facilitator && $facilitator !== '0' && $facilitator !== $class_facilitator){
						$user = get_userdata($facilitator);
						$facilitator = ' <strong>Sub - ' . $user->display_name . '</strong>';
					}
					else{
						$facilitator = '';
					}

				    if ( $date->post_status === 'publish' ) {
						$confirmed_dates[] = array(
							'date' => $start_date,
							'link' => crb_get_post_edit_link( $date->ID, $the_date ) . $facilitator,
						);
					} else {
						$requested_dates[] = array(
							'date' => $start_date,
							'link' => crb_get_post_edit_link( $date->ID, $the_date ) . $facilitator,
						);
					}
				}
			} else {
				$output_rows[] = array(
					array(
						'value' => 'No sessions scheduled.',
					),
				);
			}

			if ( !empty( $confirmed_dates ) ) {
				usort($confirmed_dates, "crb_date_sort");
				$confirmed_dates = array_column($confirmed_dates, 'link');
				$output_rows[]   = array(
					array(
						'value' => $class_facilitator_display . 'Confirmed Dates: <br/>' . implode( '<br/> ', $confirmed_dates ) . '<br/>',
					),
				);
			}

			if ( !empty( $requested_dates ) ) {
				usort($requested_dates, 'crb_date_sort');
				$requested_dates = array_column($requested_dates, 'link');
				$output_rows[]   = array(
					array(
						'value' => $class_facilitator_display . 'Requested Dates: <br/>' . implode( '<br/> ', $requested_dates ) , '<br/>',
					),
				);
			}

			$output_rows[$last_class_index][0]['rowspan'] = count( $output_rows ) - $last_class_index;
		}
	} else {
		$output_rows[] = array(
			array(
				'value' => 'No classes created yet.',
				'colspan' => 2,
			),
		);
	}

	$output_rows[$last_location_index][0]['rowspan'] = count( $output_rows ) - $last_location_index;
}
?>

<?php if ( !empty( $output_rows ) ): ?>
	<table border="1" cellspacing="0" cellpadding="5" style="text-align: left; border: 1px solid #000; " class="locations-classes-dates-table">
		<tr>
			<th colspan="3">Locations:</th>
		</tr>

		<?php foreach ( $output_rows as $columns ) : ?>
			<tr>
		 		<?php foreach ( $columns as $index => $column_settings ) : ?>
					<?php
					$column_settings = wp_parse_args( $column_settings, array(
						'th' => false,
						'colspan' => false,
						'value' => false,
						'rowspan' => false,
					) );

					printf(
						'<%s %s %s>%s</%1$s>',
						$column_settings['th'] ? 'th' : 'td',
						$column_settings['colspan'] ? 'colspan="' . $column_settings['colspan'] . '"' : '',
						$column_settings['rowspan'] ? 'rowspan="' . $column_settings['rowspan'] . '"' : '',
						$column_settings['value']
					);
					?>
				<?php endforeach; ?>
			</tr>
		<?php endforeach; ?>
	</table>
<?php else: ?>
	<h3>
		<?php if ( !empty( $args['after'] ) ): ?>
			No recent activity after <?php echo $args['after']; ?> to report.
		<?php else: ?>
			You have not created any location yet. Click here to <a href="<?php echo admin_url( 'post-new.php?post_type=crb_location' ); ?>">Add New Location</a>
		<?php endif; ?>
	</h3>
<?php endif; ?>
