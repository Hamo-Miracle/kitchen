<?php

/**
 * Handles all Statistics shortcode functionalities for eForm v3.4.0+
 *
 * It is a singleton class that needs to be instantiated once only
 *
 * @package eForm - WordPress Form Builder
 * @subpackage Shortcodes\Statistics
 */
class IPT_EForm_Stat_Shortcodes {
	/**
	 * Singleton instance variable
	 */
	private static $instance = null;

	/**
	 * Color stack variable
	 * Used to get some nice color codes
	 *
	 * @var        array
	 */
	private $color_stack = array();

	/**
	 * Get the instance of this singleton class
	 *
	 * @return     IPT_EForm_Stat_Shortcodes  The instance of the class
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new IPT_EForm_Stat_Shortcodes();
		}
		return self::$instance;
	}
	/**
	 * Constructor function
	 *
	 * Hooks all shortcodes to the system
	 *
	 * We make it private to make the class singleton
	 */
	private function __construct() {
		// add login shortcode
		add_shortcode( 'ipt_eform_login', array( $this, 'login_form' ) );

		// add form stastistics shortcodes
		add_shortcode( 'ipt_eform_stat', array( $this, 'submissions_stat' ) );
		add_shortcode( 'ipt_eform_substat', array( $this, 'overall_submissions' ) );
		add_shortcode( 'ipt_eform_formscorestat', array( $this, 'form_score' ) );

		// add user statistics shortcodes
		add_shortcode( 'ipt_eform_userstatsub', array( $this, 'user_stat_submissions' ) );
		add_shortcode( 'ipt_eform_usersub', array( $this, 'user_sub' ) );
		add_shortcode( 'ipt_eform_userscorestat', array( $this, 'user_score_stat' ) );
	}

	/*==========================================================================
	 * User Statistics Shortcodes
	 *========================================================================*/

	/**
	 * Handles user submissions shortcode. Shows overall form-based submissions
	 * in pie chart.
	 *
	 * [ipt_eform_usersub form_ids="all" user_id="current" show_login="1"
	 * login_msg="Please login to view statistics" theme="bootstrap" days=""
	 * type="pie" height="400" width="600"]
	 *
	 * @param      array   $atts     Associative array of shortcode attributes
	 * @param      string  $content  Shortcode content ( ignored )
	 *
	 * @return     string  Shortcode output
	 */
	public function user_sub( $atts, $content = null ) {
		// Refer the globals
		global $wpdb, $ipt_fsqm_info;

		// Get shortcode attributes
		$atts = shortcode_atts( array(
			'form_ids' => 'all',
			'user_id' => 'current',
			'show_login' => '1',
			'login_msg' => __( 'Please login to view statistics', 'ipt_fsqm' ),
			'theme' => 'material-default',
			'days' => '', // Can work both as days or since
			'type' => 'pie', // Can be pie, doughnut
			'height' => '400',
			'width' => '600',
			'max' => '0',
			'others' => __( 'Others', 'ipt_fsqm' ),
		), $atts, 'ipt_eform_usersub' );

		// If settings say so and user not logged in
		if ( 'current' == $atts['user_id'] && ! is_user_logged_in() ) {
			if ( '1' == $atts['show_login'] ) {
				// Show the login
				return $this->login_form( array(
					'theme' => $atts['theme'],
					'redir' => '',
				), $atts['login_msg'] );
			} else {
				// Do nothing??
				return '';
			}
		}

		// Prepare the form where clause
		$wheres = array();

		// Calculate the user id
		if ( 'current' == $atts['user_id'] ) {
			$user_id = get_current_user_id();
		} else {
			$user_id = (int) $atts['user_id'];
		}
		if ( 0 == $user_id ) {
			return __( 'Invalid user id', 'ipt_fsqm' );
		}
		$wheres[] = $wpdb->prepare( 'd.user_id = %d', $user_id );

		// Calculate the form ids
		$form_ids = array();
		if ( '' == $atts['form_ids'] ) {
			$atts['form_ids'] = 'all';
		}
		if ( 'all' != $atts['form_ids'] ) {
			$maybe_form_ids = (array) explode( ',', $atts['form_ids'] );
			$maybe_form_ids = array_map( 'absint', $maybe_form_ids );
			if ( ! empty( $maybe_form_ids ) ) {
				$form_ids = $maybe_form_ids;
			}
			unset( $maybe_form_ids );
		}
		if ( ! empty( $form_ids ) ) {
			$wheres[] = 'd.form_id IN ( ' . implode( ',', $form_ids ) . ' )';
		}

		// Set some variables
		$today = current_time( 'timestamp' );

		// Prepare the date where clause
		if ( '' != $atts['days'] ) {
			// If it is integer
			if ( is_numeric( $atts['days'] ) ) {
				// We calculate submissions for past mentioned days
				if ( $atts['days'] > 0 ) {
					$thedate = mktime( 0, 0, 0, date( 'm', $today ), date( 'd', $today ) - $atts['days'], date( 'Y', $today ) );
					$wheres[] = $wpdb->prepare( 'd.date >= %s', date( 'Y-m-d H:i:s', $thedate ) );
				}
			} else {
				// Calculate from the mentioned date-time
				$thedate = date( 'Y-m-d H:i:s', strtotime( $atts['days'] ) );
				$wheres[] = $wpdb->prepare( 'd.date >= %s', $thedate );
			}
		}

		// All set, now prepare the query
		$query = "SELECT COUNT(d.id) count, d.form_id form_id, d.user_id, f.name name FROM {$ipt_fsqm_info['data_table']} d LEFT JOIN {$ipt_fsqm_info['form_table']} f ON d.form_id = f.id";
		if ( ! empty( $wheres ) ) {
			$query .= ' WHERE ' . implode( ' AND ', $wheres );
		}
		$query .= ' GROUP BY d.form_id ORDER BY count DESC';

		$counts = $wpdb->get_results( $query ); // WPCS: unprepared SQL ok.

		if ( ! $counts ) {
			return '<p>' . __( 'No data yet', 'ipt_fsqm' ) . '</p>';
		}

		// Reset the color stack
		$this->reset_color_stack();

		// Start preparing the data for the chart
		$json = array();
		$json['labels'] = array();
		$json['datasets'] = array(
			0 => array(
				'data' => array(),
				'backgroundColor' => array(),
				'borderColor' => array(),
				'borderWidth' => array(),
				'hoverBackgroundColor' => array(),
			),
		);
		$i = 0;
		$max = absint( $atts['max'] );
		$grouping = false;
		foreach ( $counts as $count ) {
			if ( $max > 0 ) {
				if ( $i == $max ) {
					$grouping = $i;
					break;
				}
			}
			$json['labels'][] = $count->name;
			$json['datasets'][0]['data'][] = $count->count;
			$color = $this->random_color( false, 'hex' );
			$json['datasets'][0]['backgroundColor'][] = $this->hex2rgba( $color, 0.8 );
			$json['datasets'][0]['borderColor'][] = '#ffffff';
			$json['datasets'][0]['borderWidth'][] = 2;
			$json['datasets'][0]['hoverBackgroundColor'][] = $color;
			$i++;
		}
		// Continue if grouping
		if ( false !== $grouping ) {
			$total = 0;
			do {
				$total += $counts[ $grouping ]->count;
			} while ( isset( $counts[ ++$grouping ] ) );
			$json['labels'][] = $atts['others'];
			$json['datasets'][0]['data'][] = $total;
			$color = $this->random_color( false, 'hex' );
			$json['datasets'][0]['backgroundColor'][] = $this->hex2rgba( $color, 0.8 );
			$json['datasets'][0]['borderColor'][] = '#ffffff';
			$json['datasets'][0]['borderWidth'][] = 2;
			$json['datasets'][0]['hoverBackgroundColor'][] = $color;
		}

		// Prepare the options
		$options = array();

		// enqueue
		$this->enqueue_stat_scripts();

		// Done, now just start the buffer
		ob_start();
		?>
<div class="ipt-eform-stats" style="max-width: <?php echo esc_attr( $atts['width'] ); ?>px" data-stattype="user_sub" data-charttype="<?php echo esc_attr( $atts['type'] ) ?>" data-chartdata="<?php echo esc_attr( json_encode( $json ) ); ?>" data-chartoptions="<?php echo esc_attr( json_encode( $options ) ); ?>">
	<canvas class="ipt-eform-stats-canvas" width="<?php echo esc_attr( $atts['width'] ); ?>" height="<?php echo esc_attr( $atts['height'] ); ?>"></canvas>
</div>
		<?php
		$output = ob_get_clean();

		// Now compress the output HTML
		if ( WP_DEBUG !== true ) {
			$output = IPT_FSQM_Minify_HTML::minify( $output );
		}

		return $output;
	}

	/**
	 * Handles user score statistics shortcode
	 *
	 * [ipt_eform_userscorestat form_ids="all" user_id="current" show_login="1"
	 * login_msg="Please login to view statistics" theme="bootstrap" label="From
	 * %1$d%% to %2$d%%" days="" type="pie" height="400" width="600"]
	 *
	 * @param      array   $atts     Associative array of shortcode attributes
	 * @param      string  $content  Shortcode content ( ignored )
	 *
	 * @return     string  Output of shortcode
	 */
	public function user_score_stat( $atts, $content = null ) {
		// Refer the globals
		global $wpdb, $ipt_fsqm_info;

		// Get shortcode attributes
		$atts = shortcode_atts( array(
			'form_ids' => 'all',
			'user_id' => 'current',
			'show_login' => '1',
			'login_msg' => __( 'Please login to view statistics', 'ipt_fsqm' ),
			'theme' => 'material-default',
			'label' => __( 'From %1$d%% to %2$d%% ', 'ipt_fsqm' ),
			'days' => '',
			'type' => 'pie', // Can be pie, doughnut
			'height' => '400',
			'width' => '600',
		), $atts, 'ipt_eform_userscorestat' );

		// If settings say so and user not logged in
		if ( 'current' == $atts['user_id'] && ! is_user_logged_in() ) {
			if ( '1' == $atts['show_login'] ) {
				// Show the login
				return $this->login_form( array(
					'theme' => $atts['theme'],
					'redir' => '',
				), $atts['login_msg'] );
			} else {
				// Do nothing??
				return '';
			}
		}

		// Prepare the form where clause
		$wheres = array();

		// Calculate the user id
		if ( 'current' == $atts['user_id'] ) {
			$user_id = get_current_user_id();
		} else {
			$user_id = (int) $atts['user_id'];
		}
		if ( 0 == $user_id ) {
			return __( 'Invalid user id', 'ipt_fsqm' );
		}
		$wheres[] = $wpdb->prepare( 'user_id = %d', $user_id );

		// Set some variables
		$today = current_time( 'timestamp' );

		// Prepare the date where clause
		if ( '' != $atts['days'] ) {
			// If it is integer
			if ( is_numeric( $atts['days'] ) ) {
				// We calculate submissions for past mentioned days
				if ( $atts['days'] > 0 ) {
					$thedate = mktime( 0, 0, 0, date( 'm', $today ), date( 'd', $today ) - $atts['days'], date( 'Y', $today ) );
					$wheres[] = $wpdb->prepare( 'date >= %s', date( 'Y-m-d H:i:s', $thedate ) );
				}
			} else {
				// Calculate from the mentioned date-time
				$thedate = date( 'Y-m-d H:i:s', strtotime( $atts['days'] ) );
				$wheres[] = $wpdb->prepare( 'date >= %s', $thedate );
			}
		}

		// Calculate the form ids
		$form_ids = array();
		if ( '' == $atts['form_ids'] ) {
			$atts['form_ids'] = 'all';
		}
		if ( 'all' != $atts['form_ids'] ) {
			$maybe_form_ids = (array) explode( ',', $atts['form_ids'] );
			$maybe_form_ids = array_map( 'absint', $maybe_form_ids );
			if ( ! empty( $maybe_form_ids ) ) {
				$form_ids = $maybe_form_ids;
			}
			unset( $maybe_form_ids );
		}
		if ( ! empty( $form_ids ) ) {
			$wheres[] = 'form_id IN ( ' . implode( ',', $form_ids ) . ' )';
		}

		// Prepare the query
		// here is a really nice query
		$where = '';
		if ( ! empty( $wheres ) ) {
			$where = ' WHERE ' . implode( ' AND ', $wheres );
		}

		$query = "SELECT
					10 * (per div 10) as min,
					IF( ( 10 * (per div 10) ) = 100, 100, ( 10 * (per div 10) + 9 ) ) as max,
					COUNT(*) as count
				FROM (
					SELECT ( score / max_score * 100 ) as per, id, form_id, date FROM {$ipt_fsqm_info['data_table']}
					{$where}
				) AS p
				GROUP BY (per div 10)";

		$results = $wpdb->get_results( $query ); // WPCS: unprepared SQL ok.

		if ( ! $results ) {
			return '<p>' . __( 'No data yet', 'ipt_fsqm' ) . '</p>';
		}

		// Prepare the chart data
		$json = array();
		$json['labels'] = array();
		$json['datasets'] = array(
			0 => array(
				'data' => array(),
				'backgroundColor' => array(),
				'borderColor' => array(),
				'borderWidth' => array(),
				'hoverBackgroundColor' => array(),
			),
		);
		foreach ( $results as $result ) {
			if ( is_null( $result->min ) || is_null( $result->max ) ) {
				continue;
			}
			$label_sprintf = $atts['label'];
			if ( 100 == $result->max ) {
				$label_sprintf = '%1$d%%';
			}
			$json['labels'][] = sprintf( $label_sprintf, $result->min, $result->max );
			$json['datasets'][0]['data'][] = $result->count;
			$color = $this->random_color( false, 'hex' );
			$json['datasets'][0]['backgroundColor'][] = $this->hex2rgba( $color, 0.8 );
			$json['datasets'][0]['borderColor'][] = '#ffffff';
			$json['datasets'][0]['borderWidth'][] = 2;
			$json['datasets'][0]['hoverBackgroundColor'][] = $color;
		}

		// Prepare the options
		$options = array();

		// Enqueue the scripts and style
		$this->enqueue_stat_scripts();

		// Start the output
		ob_start();
		?>
<div class="ipt-eform-stats" style="max-width: <?php echo esc_attr( $atts['width'] ); ?>px" data-stattype="user_score" data-charttype="<?php echo esc_attr( $atts['type'] ) ?>" data-chartdata="<?php echo esc_attr( json_encode( $json ) ); ?>" data-chartoptions="<?php echo esc_attr( json_encode( $options ) ); ?>">
	<canvas class="ipt-eform-stats-canvas" width="<?php echo esc_attr( $atts['width'] ); ?>" height="<?php echo esc_attr( $atts['height'] ); ?>"></canvas>
</div>
		<?php
		$output = ob_get_clean();

		// Now compress the output HTML
		if ( WP_DEBUG !== true ) {
			$output = IPT_FSQM_Minify_HTML::minify( $output );
		}

		return $output;
	}

	/**
	 * Shortcode handler for user submission statistics
	 *
	 * [ipt_eform_userstatsub user_id="current" form_ids="all" show_login="1"
	 * login_msg="" theme="bootstrap" days="30" totalline="Total Submission"
	 * xlabel="Date" ylabel="Submissions" height="400" width="900"]
	 *
	 * @param      array   $atts     Associative array of shortcode attributes
	 * @param      string  $content  shortcode content ( ignored )
	 *
	 * @return     string  Shortcode output
	 */
	public function user_stat_submissions( $atts, $content = null ) {
		// Refer the globals
		global $wpdb, $ipt_fsqm_info;

		// Get the shortcode attributes
		$atts = shortcode_atts( array(
			'user_id' => 'current',
			'form_ids' => 'all',
			'show_login' => '1',
			'login_msg' => __( 'Please login to view statistics', 'ipt_fsqm' ),
			'theme' => 'material-default',
			'days' => '30',
			'totalline' => __( 'Total Submissions', 'ipt_fsqm' ),
			'xlabel' => __( 'Date', 'ipt_fsqm' ),
			'ylabel' => __( 'Submissions', 'ipt_fsqm' ),
			'height' => '400',
			'width' => '900',
			'max' => '0',
			'others' => __( 'Others', 'ipt_fsqm' ),
		), $atts, 'ipt_eform_userstatsub' );

		// If settings say so and user not logged in
		if ( 'current' == $atts['user_id'] && ! is_user_logged_in() ) {
			if ( '1' == $atts['show_login'] ) {
				// Show the login
				return $this->login_form( array(
					'theme' => $atts['theme'],
					'redir' => '',
				), $atts['login_msg'] );
			} else {
				// Do nothing??
				return '';
			}
		}

		// Prepare the form where clause
		$wheres = array();

		// Calculate the user id
		if ( 'current' == $atts['user_id'] ) {
			$user_id = get_current_user_id();
		} else {
			$user_id = (int) $atts['user_id'];
		}
		if ( 0 == $user_id ) {
			return __( 'Invalid user id', 'ipt_fsqm' );
		}
		// User id clause
		$wheres[] = $wpdb->prepare( 'd.user_id = %d', $user_id );

		// Calculate the form ids
		$form_ids = array();
		if ( '' == $atts['form_ids'] ) {
			$atts['form_ids'] = 'all';
		}
		if ( 'all' != $atts['form_ids'] ) {
			$maybe_form_ids = (array) explode( ',', $atts['form_ids'] );
			$maybe_form_ids = array_map( 'absint', $maybe_form_ids );
			if ( ! empty( $maybe_form_ids ) ) {
				$form_ids = $maybe_form_ids;
			}
			unset( $maybe_form_ids );
		}
		if ( ! empty( $form_ids ) ) {
			$wheres[] = 'd.form_id IN ( ' . implode( ',', $form_ids ) . ' )';
		}

		// Set some variables
		$today = current_time( 'timestamp' );

		// Prepare the date where clause
		if ( '' != $atts['days'] ) {
			// If it is integer
			if ( is_numeric( $atts['days'] ) ) {
				// We calculate submissions for past mentioned days
				if ( $atts['days'] > 0 ) {
					$thedate = mktime( 0, 0, 0, date( 'm', $today ), date( 'd', $today ) - $atts['days'], date( 'Y', $today ) );
					$wheres[] = $wpdb->prepare( 'd.date >= %s', date( 'Y-m-d H:i:s', $thedate ) );
				}
			} else {
				// Calculate from the mentioned date-time
				$thedate = date( 'Y-m-d H:i:s', strtotime( $atts['days'] ) );
				$wheres[] = $wpdb->prepare( 'd.date >= %s', $thedate );
			}
		}

		// Now prepare the query
		$query = "SELECT COUNT(d.id) count, d.form_id form_id, date( d.date ) subdate, f.name name FROM {$ipt_fsqm_info['data_table']} d LEFT JOIN {$ipt_fsqm_info['form_table']} f ON d.form_id = f.id";
		if ( ! empty( $wheres ) ) {
			$query .= ' WHERE ' . implode( ' AND ', $wheres );
		}
		$query .= ' GROUP BY date( d.date ), d.form_id ORDER BY subdate ASC';

		$counts = $wpdb->get_results( $query ); // WPCS: unprepared SQL ok.

		// If no data
		if ( ! $counts ) {
			return '<p>' . __( 'No data yet.', 'ipt_fsqm' ) . '</p>';
		}

		// Prepare the variables
		$forms = array();
		$data = array();
		$dates = array();

		// Loop through and populate the variables
		foreach ( $counts as $count ) {
			// Store the form name
			if ( ! isset( $forms[ $count->form_id ] ) ) {
				$forms[ $count->form_id ] = $count->name;
			}

			// Store the datewise submission data
			if ( ! isset( $data[ $count->form_id ] ) ) {
				$data[ $count->form_id ] = array();
			}
			$data[ $count->form_id ][ $count->subdate ] = $count->count;

			// Store the date sequence and the total
			if ( ! isset( $dates[ $count->subdate ] ) ) {
				$dates[ $count->subdate ] = 0;
			}
			$dates[ $count->subdate ] += $count->count;
		}

		// Now prepare the JSON
		$json = array();
		$json['labels'] = array();
		$json['datasets'] = array();

		// Loop through data and add form submission stat
		foreach ( $dates as $date => $total ) {
			// Add the date first
			$json['labels'][] = date_i18n( get_option( 'date_format' ), strtotime( $date ) );
		}

		// Now loop through forms and add the date information
		$i = 0;
		$max = absint( $atts['max'] );
		$grouping = false;
		$form_keys = array_keys( $forms );
		foreach ( $forms as $form_id => $form ) {
			if ( $max > 0 ) {
				if ( $i == $max ) {
					$grouping = $i;
					break;
				}
			}
			// Create the dataset
			$color = $this->random_color( false, 'hex' );
			$datasets = array(
				'type' => 'bar',
				'label' => $form,
				'backgroundColor' => $this->hex2rgba( $color, 0.4 ),
				'borderColor' => $color,
				'borderWidth' => 1,
				'data' => array(),
			);

			// Now loop through the all available dates and insert data
			foreach ( $dates as $date => $total ) {
				if ( isset( $data[ $form_id ][ $date ] ) ) {
					$datasets['data'][] = $data[ $form_id ][ $date ];
				} else {
					$datasets['data'][] = 0;
				}
			}

			// Add to the final json
			$json['datasets'][] = $datasets;
			$i++;
			unset( $datasets );
		}
		// Continue if grouping
		if ( false !== $grouping ) {
			// Create the dataset
			$color = $this->random_color( false, 'hex' );
			$datasets = array(
				'type' => 'bar',
				'label' => $atts['others'],
				'backgroundColor' => $this->hex2rgba( $color, 0.4 ),
				'borderColor' => $color,
				'borderWidth' => 1,
				'data' => array(),
			);
			$datesets = array();

			do {
				$form_id = $form_keys[ $grouping ];
				$form = $forms[ $form_id ];

				// Now loop through the all available dates and insert data
				foreach ( $dates as $date => $total ) {
					if ( ! isset( $datesets[ $date ] ) ) {
						$datesets[ $date ] = 0;
					}
					if ( isset( $data[ $form_id ][ $date ] ) ) {
						$datesets[ $date ] += $data[ $form_id ][ $date ];
					} else {
						$datesets[ $date ] += 0;
					}
				}
			} while ( isset( $form_keys[ ++$grouping ] ) );

			$datasets['data'] = array_values( $datesets );
			$json['datasets'][] = $datasets;
			unset( $datasets, $datesets );
		}

		// Add the total line if necessary
		if ( '' != $atts['totalline'] ) {
			$json['datasets'][] = array(
				'type' => 'line',
				'label' => $atts['totalline'],
				'data' => array_values( $dates ),
				'borderColor' => 'rgba(77, 77, 77, 0.8)',
				'backgroundColor' => 'rgba(77, 77, 77, 0.1)',
				'borderWidth' => '2',
			);
		}

		// Enqueue
		$this->enqueue_stat_scripts();

		// Create the options
		$options = array(
			'xlabelString' => $atts['xlabel'],
			'ylabelString' => $atts['ylabel'],
		);

		// Start the buffer
		ob_start();
		?>
<div class="ipt-eform-stats" style="max-width: <?php echo esc_attr( $atts['width'] ); ?>px" data-stattype="user_substat" data-charttype="bar" data-chartdata="<?php echo esc_attr( json_encode( $json ) ); ?>" data-chartoptions="<?php echo esc_attr( json_encode( $options ) ); ?>">
	<canvas class="ipt-eform-stats-canvas" width="<?php echo esc_attr( $atts['width'] ); ?>" height="<?php echo esc_attr( $atts['height'] ); ?>"></canvas>
</div>
		<?php
		$output = ob_get_clean();

		// Now compress the output HTML
		if ( WP_DEBUG !== true ) {
			$output = IPT_FSQM_Minify_HTML::minify( $output );
		}

		return $output;
	}


	/*==========================================================================
	 * Form Statistics Shortcodes
	 *========================================================================*/

	/**
	 * Handles the form score statistics shortcode
	 *
	 * [ipt_eform_formscorestat form_id="1" label="From %d%% to %d%%" days=""
	 * type="pie" height="400" width="600"]
	 *
	 * @param      array   $atts     The shortcode attributes
	 * @param      string  $content  The content ( not used )
	 *
	 * @return     string  Output of the shortcode
	 */
	public function form_score( $atts, $content = null ) {
		// Refer the globals
		global $wpdb, $ipt_fsqm_info;

		// Get shortcode attributes
		$atts = shortcode_atts( array(
			'form_ids' => 'all',
			'label' => __( 'From %1$d%% to %2$d%% ', 'ipt_fsqm' ),
			'days' => '',
			'type' => 'pie', // Can be pie, doughnut
			'height' => '400',
			'width' => '600',
		), $atts, 'ipt_eform_formscorestat' );

		// Prepare the form where clause
		$wheres = array();

		// Set some variables
		$today = current_time( 'timestamp' );

		// Prepare the date where clause
		if ( '' != $atts['days'] ) {
			// If it is integer
			if ( is_numeric( $atts['days'] ) ) {
				// We calculate submissions for past mentioned days
				if ( $atts['days'] > 0 ) {
					$thedate = mktime( 0, 0, 0, date( 'm', $today ), date( 'd', $today ) - $atts['days'], date( 'Y', $today ) );
					$wheres[] = $wpdb->prepare( 'date >= %s', date( 'Y-m-d H:i:s', $thedate ) );
				}
			} else {
				// Calculate from the mentioned date-time
				$thedate = date( 'Y-m-d H:i:s', strtotime( $atts['days'] ) );
				$wheres[] = $wpdb->prepare( 'date >= %s', $thedate );
			}
		}

		// Calculate the form ids
		$form_ids = array();
		if ( '' == $atts['form_ids'] ) {
			$atts['form_ids'] = 'all';
		}
		if ( 'all' != $atts['form_ids'] ) {
			$maybe_form_ids = (array) explode( ',', $atts['form_ids'] );
			$maybe_form_ids = array_map( 'absint', $maybe_form_ids );
			if ( ! empty( $maybe_form_ids ) ) {
				$form_ids = $maybe_form_ids;
			}
			unset( $maybe_form_ids );
		}
		if ( ! empty( $form_ids ) ) {
			$wheres[] = 'form_id IN ( ' . implode( ',', $form_ids ) . ' )';
		}

		// Prepare the query
		// here is a really nice query
		$where = '';
		if ( ! empty( $wheres ) ) {
			$where = ' WHERE ' . implode( ' AND ', $wheres );
		}

		$query = "SELECT
					10 * (per div 10) as min,
					IF( ( 10 * (per div 10) ) = 100, 100, ( 10 * (per div 10) + 9 ) ) as max,
					COUNT(*) as count
				FROM (
					SELECT ( score / max_score * 100 ) as per, id, form_id, date FROM {$ipt_fsqm_info['data_table']}
					{$where}
				) AS p
				GROUP BY (per div 10)";

		$results = $wpdb->get_results( $query ); // WPCS: unprepared SQL ok.

		if ( ! $results ) {
			return '<p>' . __( 'No data yet', 'ipt_fsqm' ) . '</p>';
		}

		// Prepare the chart data
		$json = array();
		$json['labels'] = array();
		$json['datasets'] = array(
			0 => array(
				'data' => array(),
				'backgroundColor' => array(),
				'borderColor' => array(),
				'borderWidth' => array(),
				'hoverBackgroundColor' => array(),
			),
		);
		foreach ( $results as $result ) {
			if ( is_null( $result->min ) || is_null( $result->max ) ) {
				continue;
			}
			$label_sprintf = $atts['label'];
			if ( 100 == $result->max ) {
				$label_sprintf = '%1$d%%';
			}
			$json['labels'][] = sprintf( $label_sprintf, $result->min, $result->max );
			$json['datasets'][0]['data'][] = $result->count;
			$color = $this->random_color( false, 'hex' );
			$json['datasets'][0]['backgroundColor'][] = $this->hex2rgba( $color, 0.8 );
			$json['datasets'][0]['borderColor'][] = '#ffffff';
			$json['datasets'][0]['borderWidth'][] = 2;
			$json['datasets'][0]['hoverBackgroundColor'][] = $color;
		}

		// Prepare the options
		$options = array();

		// Enqueue the scripts and style
		$this->enqueue_stat_scripts();

		// Start the output
		ob_start();
		?>
<div class="ipt-eform-stats" style="max-width: <?php echo esc_attr( $atts['width'] ); ?>px" data-stattype="scorestat" data-charttype="<?php echo esc_attr( $atts['type'] ) ?>" data-chartdata="<?php echo esc_attr( json_encode( $json ) ); ?>" data-chartoptions="<?php echo esc_attr( json_encode( $options ) ); ?>">
	<canvas class="ipt-eform-stats-canvas" width="<?php echo esc_attr( $atts['width'] ); ?>" height="<?php echo esc_attr( $atts['height'] ); ?>"></canvas>
</div>
		<?php
		$output = ob_get_clean();

		// Now compress the output HTML
		if ( WP_DEBUG !== true ) {
			$output = IPT_FSQM_Minify_HTML::minify( $output );
		}

		return $output;
	}

	/**
	 * Handles the form submissions shortcode
	 *
	 * [ipt_eform_substat form_ids="all" days="" type="pie" height="400" width="600"]
	 *
	 * @param      array   $atts     The shortcode attributes
	 * @param      string  $content  The content
	 *
	 * @return     string  Output of the shortcode
	 */
	public function overall_submissions( $atts, $content = null ) {
		// Refer the globals
		global $wpdb, $ipt_fsqm_info;

		// Get shortcode attributes
		$atts = shortcode_atts( array(
			'form_ids' => 'all',
			'days' => '', // Can work both as days or since
			'type' => 'pie', // Can be pie, doughnut
			'height' => '400',
			'width' => '600',
			'max' => '0',
			'others' => __( 'Others', 'ipt_fsqm' ),
		), $atts, 'ipt_eform_substat' );

		// Prepare the form where clause
		$wheres = array();

		// Calculate the form ids
		$form_ids = array();
		if ( '' == $atts['form_ids'] ) {
			$atts['form_ids'] = 'all';
		}
		if ( 'all' != $atts['form_ids'] ) {
			$maybe_form_ids = (array) explode( ',', $atts['form_ids'] );
			$maybe_form_ids = array_map( 'absint', $maybe_form_ids );
			if ( ! empty( $maybe_form_ids ) ) {
				$form_ids = $maybe_form_ids;
			}
			unset( $maybe_form_ids );
		}
		if ( ! empty( $form_ids ) ) {
			$wheres[] = 'd.form_id IN ( ' . implode( ',', $form_ids ) . ' )';
		}

		// Set some variables
		$today = current_time( 'timestamp' );

		// Prepare the date where clause
		if ( '' != $atts['days'] ) {
			// If it is integer
			if ( is_numeric( $atts['days'] ) ) {
				// We calculate submissions for past mentioned days
				if ( $atts['days'] > 0 ) {
					$thedate = mktime( 0, 0, 0, date( 'm', $today ), date( 'd', $today ) - $atts['days'], date( 'Y', $today ) );
					$wheres[] = $wpdb->prepare( 'd.date >= %s', date( 'Y-m-d H:i:s', $thedate ) );
				}
			} else {
				// Calculate from the mentioned date-time
				$thedate = date( 'Y-m-d H:i:s', strtotime( $atts['days'] ) );
				$wheres[] = $wpdb->prepare( 'd.date >= %s', $thedate );
			}
		}

		// All set, now prepare the query
		$query = "SELECT COUNT(d.id) count, d.form_id form_id, f.name name FROM {$ipt_fsqm_info['data_table']} d LEFT JOIN {$ipt_fsqm_info['form_table']} f ON d.form_id = f.id";
		if ( ! empty( $wheres ) ) {
			$query .= ' WHERE ' . implode( ' AND ', $wheres );
		}
		$query .= ' GROUP BY d.form_id ORDER BY count DESC';

		$counts = $wpdb->get_results( $query ); // WPCS: unprepared SQL ok.

		if ( ! $counts ) {
			return '<p>' . __( 'No data yet', 'ipt_fsqm' ) . '</p>';
		}

		// Reset the color stack
		$this->reset_color_stack();

		// Start preparing the data for the chart
		$json = array();
		$json['labels'] = array();
		$json['datasets'] = array(
			0 => array(
				'data' => array(),
				'backgroundColor' => array(),
				'borderColor' => array(),
				'borderWidth' => array(),
				'hoverBackgroundColor' => array(),
			),
		);
		$i = 0;
		$max = absint( $atts['max'] );
		$grouping = false;
		foreach ( $counts as $count ) {
			if ( $max > 0 ) {
				if ( $i == $max ) {
					$grouping = $i;
					break;
				}
			}
			$json['labels'][] = $count->name;
			$json['datasets'][0]['data'][] = $count->count;
			$color = $this->random_color( false, 'hex' );
			$json['datasets'][0]['backgroundColor'][] = $this->hex2rgba( $color, 0.8 );
			$json['datasets'][0]['borderColor'][] = '#ffffff';
			$json['datasets'][0]['borderWidth'][] = 2;
			$json['datasets'][0]['hoverBackgroundColor'][] = $color;
			$i++;
		}
		// Continue if grouping
		if ( false !== $grouping ) {
			$total = 0;
			do {
				$total += $counts[ $grouping ]->count;
			} while ( isset( $counts[ ++$grouping ] ) );
			$json['labels'][] = $atts['others'];
			$json['datasets'][0]['data'][] = $total;
			$color = $this->random_color( false, 'hex' );
			$json['datasets'][0]['backgroundColor'][] = $this->hex2rgba( $color, 0.8 );
			$json['datasets'][0]['borderColor'][] = '#ffffff';
			$json['datasets'][0]['borderWidth'][] = 2;
			$json['datasets'][0]['hoverBackgroundColor'][] = $color;
		}

		// Prepare the options
		$options = array();

		// enqueue
		$this->enqueue_stat_scripts();

		// Done, now just start the buffer
		ob_start();
		?>
<div class="ipt-eform-stats" style="max-width: <?php echo esc_attr( $atts['width'] ); ?>px" data-stattype="overall" data-charttype="<?php echo esc_attr( $atts['type'] ) ?>" data-chartdata="<?php echo esc_attr( json_encode( $json ) ); ?>" data-chartoptions="<?php echo esc_attr( json_encode( $options ) ); ?>">
	<canvas class="ipt-eform-stats-canvas" width="<?php echo esc_attr( $atts['width'] ); ?>" height="<?php echo esc_attr( $atts['height'] ); ?>"></canvas>
</div>
		<?php
		$output = ob_get_clean();

		// Now compress the output HTML
		if ( WP_DEBUG !== true ) {
			$output = IPT_FSQM_Minify_HTML::minify( $output );
		}

		return $output;
	}

	/**
	 * Handles the form statistics shortcode
	 *
	 * This outputs a chart for showing form submissions of selected forms for
	 * selected days in a nicely stacked bar graph
	 *
	 * [ipt_eform_stat form_ids="all" days="30" totalline="Total Submissions" xlabel="Date" ylabel="Submissions"]
	 *
	 * It handles the enqueue of the scripts and styles
	 *
	 * @param      array   $atts     Associative array of shortcode options
	 * @param      string  $content  Shortcode content
	 *
	 * @return     string  The output of the shortcode
	 */
	public function submissions_stat( $atts, $content = null ) {
		// Refer the globals
		global $wpdb, $ipt_fsqm_info;

		$atts = shortcode_atts( array(
			'form_ids' => 'all',
			'days' => '30',
			'totalline' => __( 'Total Submissions', 'ipt_fsqm' ),
			'xlabel' => __( 'Date', 'ipt_fsqm' ),
			'ylabel' => __( 'Submissions', 'ipt_fsqm' ),
			'height' => '400',
			'width' => '900',
			'max' => '0',
			'others' => __( 'Others', 'ipt_fsqm' ),
		), $atts, 'ipt_eform_stat' );

		// Prepare the form where clause
		$wheres = array();

		// Calculate the form ids
		$form_ids = array();
		if ( '' == $atts['form_ids'] ) {
			$atts['form_ids'] = 'all';
		}
		if ( 'all' != $atts['form_ids'] ) {
			$maybe_form_ids = (array) explode( ',', $atts['form_ids'] );
			$maybe_form_ids = array_map( 'absint', $maybe_form_ids );
			if ( ! empty( $maybe_form_ids ) ) {
				$form_ids = $maybe_form_ids;
			}
			unset( $maybe_form_ids );
		}
		if ( ! empty( $form_ids ) ) {
			$wheres[] = 'd.form_id IN ( ' . implode( ',', $form_ids ) . ' )';
		}

		// Set some variables
		$today = current_time( 'timestamp' );

		// Prepare the date where clause
		if ( '' != $atts['days'] ) {
			// If it is integer
			if ( is_numeric( $atts['days'] ) ) {
				// We calculate submissions for past mentioned days
				if ( $atts['days'] > 0 ) {
					$thedate = mktime( 0, 0, 0, date( 'm', $today ), date( 'd', $today ) - $atts['days'], date( 'Y', $today ) );
					$wheres[] = $wpdb->prepare( 'd.date >= %s', date( 'Y-m-d H:i:s', $thedate ) );
				}
			} else {
				// Calculate from the mentioned date-time
				$thedate = date( 'Y-m-d H:i:s', strtotime( $atts['days'] ) );
				$wheres[] = $wpdb->prepare( 'd.date >= %s', $thedate );
			}
		}

		// Now prepare the query
		$query = "SELECT COUNT(d.id) count, d.form_id form_id, date( d.date ) subdate, f.name name FROM {$ipt_fsqm_info['data_table']} d LEFT JOIN {$ipt_fsqm_info['form_table']} f ON d.form_id = f.id";
		if ( ! empty( $wheres ) ) {
			$query .= ' WHERE ' . implode( ' AND ', $wheres );
		}
		$query .= ' GROUP BY date( d.date ), d.form_id ORDER BY subdate ASC';

		$counts = $wpdb->get_results( $query ); // WPCS: unprepared SQL ok.

		// If no data
		if ( ! $counts ) {
			return '<p>' . __( 'No data yet.', 'ipt_fsqm' ) . '</p>';
		}

		// Prepare the variables
		$forms = array();
		$data = array();
		$dates = array();

		// Loop through and populate the variables
		foreach ( $counts as $count ) {
			// Store the form name
			if ( ! isset( $forms[ $count->form_id ] ) ) {
				$forms[ $count->form_id ] = $count->name;
			}

			// Store the datewise submission data
			if ( ! isset( $data[ $count->form_id ] ) ) {
				$data[ $count->form_id ] = array();
			}
			$data[ $count->form_id ][ $count->subdate ] = $count->count;

			// Store the date sequence and the total
			if ( ! isset( $dates[ $count->subdate ] ) ) {
				$dates[ $count->subdate ] = 0;
			}
			$dates[ $count->subdate ] += $count->count;
		}

		// Now prepare the JSON
		$json = array();
		$json['labels'] = array();
		$json['datasets'] = array();

		// Loop through data and add form submission stat
		foreach ( $dates as $date => $total ) {
			// Add the date first
			$json['labels'][] = date_i18n( get_option( 'date_format' ), strtotime( $date ) );
		}

		// Now loop through forms and add the date information
		$i = 0;
		$max = absint( $atts['max'] );
		$grouping = false;
		$form_keys = array_keys( $forms );
		foreach ( $forms as $form_id => $form ) {
			if ( $max > 0 ) {
				if ( $i == $max ) {
					$grouping = $i;
					break;
				}
			}
			// Create the dataset
			$color = $this->random_color( false, 'hex' );
			$datasets = array(
				'type' => 'bar',
				'label' => $form,
				'backgroundColor' => $this->hex2rgba( $color, 0.4 ),
				'borderColor' => $color,
				'borderWidth' => 1,
				'data' => array(),
			);

			// Now loop through the all available dates and insert data
			foreach ( $dates as $date => $total ) {
				if ( isset( $data[ $form_id ][ $date ] ) ) {
					$datasets['data'][] = $data[ $form_id ][ $date ];
				} else {
					$datasets['data'][] = 0;
				}
			}

			// Add to the final json
			$json['datasets'][] = $datasets;
			$i++;
			unset( $datasets );
		}

		// Continue if grouping
		if ( false !== $grouping ) {
			// Create the dataset
			$color = $this->random_color( false, 'hex' );
			$datasets = array(
				'type' => 'bar',
				'label' => $atts['others'],
				'backgroundColor' => $this->hex2rgba( $color, 0.4 ),
				'borderColor' => $color,
				'borderWidth' => 1,
				'data' => array(),
			);
			$datesets = array();

			do {
				$form_id = $form_keys[ $grouping ];
				$form = $forms[ $form_id ];

				// Now loop through the all available dates and insert data
				foreach ( $dates as $date => $total ) {
					if ( ! isset( $datesets[ $date ] ) ) {
						$datesets[ $date ] = 0;
					}
					if ( isset( $data[ $form_id ][ $date ] ) ) {
						$datesets[ $date ] += $data[ $form_id ][ $date ];
					} else {
						$datesets[ $date ] += 0;
					}
				}
			} while ( isset( $form_keys[ ++$grouping ] ) );

			$datasets['data'] = array_values( $datesets );
			$json['datasets'][] = $datasets;
			unset( $datasets, $datesets );
		}

		// Add the total line if necessary
		if ( '' != $atts['totalline'] ) {
			$json['datasets'][] = array(
				'type' => 'line',
				'label' => $atts['totalline'],
				'data' => array_values( $dates ),
				'borderColor' => 'rgba(77, 77, 77, 0.8)',
				'backgroundColor' => 'rgba(77, 77, 77, 0.1)',
				'borderWidth' => '2',
			);
		}

		// Enqueue
		$this->enqueue_stat_scripts();

		// Create the options
		$options = array(
			'xlabelString' => $atts['xlabel'],
			'ylabelString' => $atts['ylabel'],
		);

		// Start the buffer
		ob_start();
		?>

<div class="ipt-eform-stats" style="max-width: <?php echo esc_attr( $atts['width'] ); ?>px" data-stattype="submissions" data-charttype="bar" data-chartdata="<?php echo esc_attr( json_encode( $json ) ); ?>" data-chartoptions="<?php echo esc_attr( json_encode( $options ) ); ?>">
	<canvas class="ipt-eform-stats-canvas" width="<?php echo esc_attr( $atts['width'] ); ?>" height="<?php echo esc_attr( $atts['height'] ); ?>"></canvas>
</div>
		<?php
		$output = ob_get_clean();

		// Now compress the output HTML
		if ( WP_DEBUG !== true ) {
			$output = IPT_FSQM_Minify_HTML::minify( $output );
		}

		return $output;
	}

	/**
	 * Handles the login form shortcode output [ipt_eform_login
	 * theme="bootstrap" redir="" register="1" forgot="1"]Custom Message
	 * Here[/ipt_eform_login]
	 *
	 * @param      array   $atts     Associative array of shortcode attributes
	 * @param      string  $content  Content inside shortcode
	 *
	 * @return     string  Shortcode output - The login form
	 */
	public function login_form( $atts, $content = null ) {
		// Do not do anything if user is logged in
		if ( is_user_logged_in() ) {
			return '';
		}
		// Process the attributes
		$atts = shortcode_atts( array(
			'theme' => 'material-default',
			'style' => 'boxy',
			'redir' => '',
			'register' => '1',
			'regurl' => '',
			'forgot' => '1',
			'max_width' => '600px',
		), $atts, 'ipt_eform_login' );

		// Create the redirect URL beforehand
		$redirect = IPT_FSQM_Form_ELements_Static::get_current_url();
		// Override if redirect is set to empty and something is there in request
		if ( '' == $atts['redir'] && isset( $_REQUEST['redirect'] ) && ! empty( $_REQUEST['redirect'] ) ) {
			$redirect = $_REQUEST['redirect'];
		}
		if ( '' == $atts['redir'] && isset( $_REQUEST['redirect_to'] ) && ! empty( $_REQUEST['redirect_to'] ) ) {
			$redirect = $_REQUEST['redirect_to'];
		}
		if ( '' != $atts['redir'] ) {
			$redirect = $atts['redir'];
		}

		// Create the registration URL beforehand
		$regurl = wp_registration_url();
		if ( '' != $atts['regurl'] ) {
			$regurl = $atts['regurl'];
		}

		// Create the defaults arguments for compatibility with login form defaults
		$defaults = array(
			'echo' => true,
			'redirect' => $redirect,
			'form_id' => 'ipt_fsqm_up_login',
			'label_username' => __( 'Username' ),
			'label_password' => __( 'Password' ),
			'label_remember' => __( 'Remember Me' ),
			'label_log_in' => __( 'Log In' ),
			'id_username' => 'ipt_fsqm_up_user_name',
			'id_password' => 'ipt_fsqm_up_user_pwd',
			'id_remember' => 'ipt_fsqm_up_rmm',
			'id_submit' => 'wp-submit',
			'remember' => true,
			'value_username' => '',
			'value_remember' => false, // Set this to true to default the "Remember me" checkbox to checked
		);
		$args = wp_parse_args( array(), apply_filters( 'login_form_defaults', $defaults ) );

		// eForm Buttons array
		$login_buttons = array();
		$login_buttons[] = array(
			__( 'Login', 'ipt_fsqm' ),
			'wp-submit',
			'small',
			'none',
			'normal',
			array(),
			'submit',
			array(),
			array(),
			'',
			'switch',
		);

		if ( '1' == $atts['register'] && get_option( 'users_can_register', false ) ) {
			$login_buttons[] = array(
				__( 'Register', 'ipt_fsqm' ),
				'ipt_fsqm_up_reg',
				'small',
				'none',
				'normal',
				array(),
				'button',
				array(),
				array( 'onclick' => 'javascript:window.location.href="' . $regurl . '"' ),
				'',
				'user',
			);
		}

		if ( '1' == $atts['forgot'] ) {
			$login_buttons[] = array(
				__( 'Forgot Password', 'ipt_fsqm' ),
				'ipt_fsqm_up_rpwd',
				'small',
				'none',
				'normal',
				array(),
				'button',
				array(),
				array( 'onclick' => 'javascript:window.location.href="' . wp_lostpassword_url( $redirect ) . '"' ),
				'',
				'info3',
			);
		}
		$login_buttons = apply_filters( 'ipt_eform_login_sc_buttons', $login_buttons );

		// Get form element instance for theme management
		$form_element = new IPT_FSQM_Form_Elements_Base();
		$theme_element = $form_element->get_theme_by_id( $atts['theme'] );

		// Enqueue stuff
		if ( isset( $theme_element['ui-class'] ) && class_exists( $theme_element['ui-class'] ) ) {
			$ui = $theme_element[ 'ui-class' ]::instance();
		} else {
			$ui = IPT_Plugin_UIF_Front::instance();
		}
		$ui->enqueue();
		wp_enqueue_script( 'ipt-eform-login', IPT_FSQM_Loader::$static_location . 'front/js/jquery.ipt-eform-login.min.js', array( 'ipt-plugin-uif-front-js' ), IPT_FSQM_Loader::$version, true );

		$css_classes = [
			'ipt_eform_login',
			'ipt_uif_front',
			'ipt_uif_common',
		];

		if ( 'boxy' === $atts['style'] ) {
			$css_classes[] = 'eform-override-element-boxy';
		}

		$container_style = 'max-width: ' . $atts['max_width'] . '; margin: 0 auto 1em;';

		// Prepare the output
		ob_start();
		?>
<div style="<?php echo esc_attr( $container_style ); ?>" class="<?php echo esc_attr( implode( ' ', $css_classes ) ); ?>" data-ui-theme="<?php echo esc_attr( json_encode( $theme_element['include'] ) ); ?>" data-ui-theme-id="<?php echo esc_attr( $atts['theme'] ); ?>">
	<noscript>
		<div class="ipt_fsqm_form_message_noscript ui-widget ui-corner-all">
			<div class="ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
				<h3><?php _e( 'Javascript is disabled', 'ipt_fsqm' ); ?></h3>
			</div>
			<div class="ui-widget-content ui-corner-bottom">
				<p><?php _e( 'Javascript is disabled on your browser. Please enable it in order to use this form.', 'ipt_fsqm' ); ?></p>
			</div>
		</div>
	</noscript>

	<?php $ui->ajax_loader( false, '', array(), true, __( 'Loading', 'ipt_fsqm' ), array( 'ipt_uif_init_loader' ) ); ?>

	<div style="opacity: 0;" class="ipt_uif_hidden_init ui-widget ui-widget-content ui-corner-all ipt_uif_mother_wrap eform-styled-widget">
		<?php if ( null != $content ) : ?>
			<div class="ui-widget-header">
				<h3><?php $ui->print_icon( 0xe10f ); ?> <?php echo $content; ?></h3>
				<div class="clear"></div>
			</div>
		<?php endif; ?>
		<div class="ui-widget-content">
			<form action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" name="<?php echo $args['form_id']; ?>" id="<?php echo $args['form_id']; ?>" method="post">
				<?php $ui->login_form( $args, $login_buttons ); ?>
			</form>
			<div class="clear"></div>
		</div>
	</div>
</div>
		<?php
		// Get the output and stop buffering
		$output = ob_get_clean();

		// Now compress the output HTML
		if ( WP_DEBUG !== true ) {
			$output = IPT_FSQM_Minify_HTML::minify( $output );
		}

		return $output;
	}

	/*==========================================================================
	 * Enqueues
	 *========================================================================*/
	private function enqueue_stat_scripts() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'chart.js', IPT_FSQM_Loader::$bower_components . 'chart.js/dist/Chart.bundle.min.js', array( 'jquery' ), IPT_FSQM_Loader::$version, true );
		wp_enqueue_script( 'chart.js-zoom', IPT_FSQM_Loader::$bower_components . 'chartjs-plugin-zoom/chartjs-plugin-zoom.min.js', array( 'chart.js' ), IPT_FSQM_Loader::$version, true );
		wp_enqueue_script( 'eform-form-stats', IPT_FSQM_Loader::$static_location . 'front/js/jquery.ipt-eform-form-stats.min.js', array( 'jquery', 'chart.js' ), IPT_FSQM_Loader::$version, true );
		wp_enqueue_style( 'eform-form-stats', IPT_FSQM_Loader::$static_location . 'front/css/stats/ipt-eform-stats.css', array(), IPT_FSQM_Loader::$version );
	}

	/*==========================================================================
	 * Color related stuff
	 *========================================================================*/

	/**
	 * Lightens/darkens a given colour (hex format), returning the altered
	 * colour in hex format.7
	 *
	 * @link       {https://gist.github.com/stephenharris/5532899}
	 *
	 * @param      str    $hex      Colour as hexadecimal (with or without
	 *                              hash);
	 * @param      float  $percent  Decimal ( 0.2 = lighten by 20%(), -0.4 =
	 *                              darken by 40%() )
	 *
	 * @return     str    Lightened/Darkend colour as hexadecimal (with hash);
	 */
	public function color_luminance( $hex, $percent ) {
		return IPT_Plugin_UIF_Front::instance()->color_luminance( $hex, $percent );
	}

	/**
	 * Generates random color and returns for direct use
	 *
	 * The return value is always in hexadecimal format It makes use of Few's
	 * color pallet and darkens or lightens randomly
	 * @link       {http://www.mulinblog.com/a-color-palette-optimized-for-data-visualization/}
	 *
	 * @param      integer  $opacity  The opacity. False to give just rgb(  )
	 *                                color code
	 * @param      string   $return   Return type, rgb or hex
	 *
	 * @return     string   Returns a valid hex color code, depending on the opacity
	 */
	public function random_color( $opacity = false, $return = 'rgb' ) {
		if ( empty( $this->color_stack ) ) {
			$this->reset_color_stack();
		}

		$selected_color = array_pop( $this->color_stack );

		if ( 'hex' == $return ) {
			return $selected_color;
		}

		return $this->hex2rgba( $selected_color, $opacity );
	}

	public function reset_color_stack() {
		$this->color_stack = array_reverse( $this->create_possible_colors() );
	}

	public function hex2rgba( $color, $opacity = false ) {

		$default = 'rgb(0,0,0)';

		// Return default if no color provided
		if ( empty( $color ) ) {
			return $default;
		}

		// Sanitize $color if "#" is provided
		if ( '#' == $color[0] ) {
			$color = substr( $color, 1 );
		}

		// Check if color has 6 or 3 characters and get values
		if ( 6 == strlen( $color ) ) {
			$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		} elseif ( 3 == strlen( $color ) ) {
			$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
		} else {
			return $default;
		}

		// Convert hexadec to rgb
		$rgb = array_map( 'hexdec', $hex );

		// Check if opacity is set(rgba or rgb)
		if ( $opacity ) {
			// Sanitize
			if ( abs( $opacity ) > 1 ) {
				$opacity = 1.0;
			}
			$output = 'rgba(' . implode( ',', $rgb ) . ',' . $opacity . ')';
		} else {
			$output = 'rgb(' . implode( ',', $rgb ) . ')';
		}

		// Return rgb(a) color string
		return $output;
	}

	private function create_possible_colors() {
		$fews_pallet = array_reverse( array(
			'#4D4D4D', // gray
			'#5DA5DA', // blue
			'#FAA43A', // orange
			'#60BD68', // green
			'#F17CB0', // pink
			'#B2912F', // brown
			'#B276B2', // purple
			'#DECF3F', // yellow
			'#F15854', // red
		) );

		// For all possible colors we just add or subtract 0, 15 and 30% luminance
		$possible_colors = array();
		foreach ( $fews_pallet as $color ) {
			// Add the original color
			$possible_colors[] = $color;
		}

		// Add variations
		for ( $i = -30; $i <= 30; $i = $i + 15 ) {
			foreach ( $fews_pallet as $color ) {
				$possible_colors[] = $this->color_luminance( $color, ( $i / 100 ) );
			}
		}

		return $possible_colors;
	}

}
