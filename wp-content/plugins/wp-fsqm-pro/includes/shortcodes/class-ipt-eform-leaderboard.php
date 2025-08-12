<?php
/**
 * Class for ipt eform leaderboard.
 *
 * @package    eForm - WordPress Form Builder
 * @subpackage Shortcodes\Leaderboard
 * @author     Swashata Ghosh ( swashata@iptms.co )
 * @since      3.3.0 Although might not have been released publicly, it was there since this version
 */
class IPT_EForm_LeaderBoard {

	/**
	 * Form ID for which leaderboard is to be generated
	 *
	 * @var        int|null
	 */
	protected $form_id = null;

	/**
	 * Category ID for which leaderboard is to be generated
	 *
	 * @var        int|null
	 */
	protected $category_id = null;

	/**
	 * User ID for whom leaderboard is to be generated
	 *
	 * @var        int|null
	 */
	protected $user_id = null;

	/**
	 * The type of leaderboard this class is to handle
	 *
	 * @var        string
	 */
	protected $type = 'form';

	/**
	 * Current user object
	 *
	 * In case if not logged in, then it is false
	 */
	protected static $current_user = null;


	/**
	 * Constructor function
	 *
	 * Here we define the type of handling the class will do and the ID with
	 * respect to which the leaderboard is to be generated.
	 *
	 * @param      int     $id     The identifier
	 * @param      string  $type   The type
	 */
	public function __construct( $id, $type = 'form' ) {
		// Assign the type and the ID accordingly
		switch ( $type ) {
			// Form handler
			default:
			case 'form':
				$this->form_id = $id;
				$this->type = 'form';
				break;

			// Category handler
			case 'category':
				$this->category_id = $id;
				$this->type = 'category';
				break;

			// User handler
			case 'user':
				$this->user_id = $id;
				$this->type = 'user';
				break;
		}

		// Populate the current user
		$this->populate_current_user();
	}

	/**
	 * Enqueues scripts and styles.
	 *
	 * Either hook it up in wp_enqueue_scripts or call directly but not before
	 * the mentioned hook.
	 */
	public function enqueue( $theme ) {
		// Get UI
		if ( isset( $theme['ui-class'] ) && class_exists( $theme['ui-class'] ) ) {
			$ui = $theme[ 'ui-class' ]::instance();
		} else {
			$ui = IPT_Plugin_UIF_Front::instance();
		}
		// Also enqueue the UI stuff
		$ui->enqueue();
		// Start buffering to get the loader HTML
		ob_start();
		$ui->ajax_loader( false, '', array(), true, __( 'Please wait', 'ipt_fsqm' ) );
		$ajax_loader = ob_get_clean();

		// Enqueue the datatable
		$ui->enqueue_datatables();

		// Main CSS
		if ( isset( $theme['leaderboard-css'] ) && ! empty( $theme['leaderboard-css'] ) ) {
			wp_enqueue_style( 'ipt-eform-lb-css', $theme['leaderboard-css'], array(), IPT_FSQM_Loader::$version );
		}


		// Main JS
		wp_enqueue_script( 'ipt-eform-lb-js', IPT_FSQM_Loader::$static_location . 'front/js/jquery.ipt-eform-lb.min.js', array( 'jquery', 'ipt-plugin-uif-front-js' ), IPT_FSQM_Loader::$version );
		wp_localize_script( 'ipt-eform-lb-js', 'ipteFormLB', array(
			'css' => 'ipt-eform-lb-css-css',
			'cssl' => plugins_url( '/static/front/css/ipt-eform-lb.css', IPT_FSQM_Loader::$abs_file ),
			'l10n' => array(
				'sEmptyTable' => __( 'No submissions yet!', 'ipt_fsqm' ),
				'sInfo' => __( 'Showing _START_ to _END_ of _TOTAL_ entries', 'ipt_fsqm' ),
				'sInfoEmpty' => __( 'Showing 0 to 0 of 0 entries', 'ipt_fsqm' ),
				'sInfoFiltered' => __( '(filtered from _MAX_ total entries)', 'ipt_fsqm' ),
				/* translators: %s will be replaced by an empty string */
				'sInfoPostFix' => sprintf( _x( '%s', 'sInfoPostFix', 'ipt_fsqm' ), '' ),
				/* translators: For thousands separator inside datatables */
				'sInfoThousands' => _x( ',', 'sInfoThousands', 'ipt_fsqm' ),
				'sLengthMenu' => __( 'Show _MENU_ entries', 'ipt_fsqm' ),
				'sLoadingRecords' => $ajax_loader,
				'sProcessing' => $ajax_loader,
				'sSearch' => '',
				'sSearchPlaceholder' => __( 'Search submissions', 'ipt_fsqm' ),
				'sZeroRecords' => __( 'No matching records found', 'ipt_fsqm' ),
				'oPaginate' => array(
					'sFirst' => __( '<i title="First" class="ipticm ipt-icomoon-first"></i>', 'ipt_fsqm' ),
					'sLast' => __( '<i title="Last" class="ipticm ipt-icomoon-last"></i>', 'ipt_fsqm' ),
					'sNext' => __( '<i title="Next" class="ipticm ipt-icomoon-forward4"></i>', 'ipt_fsqm' ),
					'sPrevious' => __( '<i title="Previous" class="ipticm ipt-icomoon-backward3"></i>', 'ipt_fsqm' ),
				),
				'oAria' => array(
					'sSortAscending' => __( ': activate to sort column ascending', 'ipt_fsqm' ),
					'sSortDescending' => __( ': activate to sort column descending', 'ipt_fsqm' ),
				),
				'filters' => array(
					'form' => __( 'Select form to filter', 'ipt_fsqm' ),
					'category' => __( 'Select category to filter', 'ipt_fsqm' ),
				),
			),
			'allLabel' => __( 'All', 'ipt_fsqm' ),
			'allFilter' => __( 'Show all', 'ipt_fsqm' ),
			'dpPlaceholderf' => __( 'From', 'ipt_fsqm' ),
			'dpPlaceholdert' => __( 'To', 'ipt_fsqm' ),
			'sPlaceholder' => __( 'Search', 'ipt_fsqm' ),
		) );
	}

	/**
	 * Common initiator for all WP hooks and filters
	 */
	public static function init() {
		add_shortcode( 'ipt_eform_lb_form', array( __CLASS__, 'shortcode_leaderboard_form_cb' ) );
	}

	public function leaderboard( $theme = 'bootstrap' ) {

	}

	/**
	 * Show the form leaderboard
	 *
	 * It takes appearance parameters as an array and displays accordingly
	 *
	 * @param      array        $appearance  The appearance configuration
	 * 											avatar      => true to show avatar
	 * 											avatar_size => avatar size in WIDTHxHEIGHT ( pixels )
	 * 											name        => true to show name
	 * 											date        => true to show date
	 * 											score       => true to show score
	 * 											max_score   => true to show maximum score
	 * 											percentage  => true to show percentage
	 * 											comment     => true to show admin comment ( remarks )
	 * @param      array        $labels      The labels of table heads
	 * @param      string|null  $content     The content inside the welcome
	 *                                       section
	 *
	 * @global     WPDB         $wpdb
	 * @global     array        $ipt_fsqm_info
	 * @uses       IPT_FSQM_Form_Elements_Front To print the themed container
	 */
	public function form_leaderboard( $appearance = array(), $labels = array(), $content = null ) {
		// Global variables
		global $wpdb, $ipt_fsqm_info;

		// Get the basic instance of form
		$form = new IPT_FSQM_Form_Elements_Front( null, $this->form_id );

		// Check if form exists
		if ( null == $form->form_id ) {
			$form->container( array( array( $form->ui, 'msg_error' ), array( __( 'Please check the code.', 'ipt_fsqm' ), true, __( 'Invalid ID', 'ipt_fsqm' ) ) ), true );
			return;
		}

		// Get the form theme
		$theme = $form->settings['theme']['template'];

		// Get the theme element needed for printing
		$theme_element = $form->get_theme_by_id( $theme );

		// Enqueue
		$this->enqueue( $theme_element );

		// Default the appearance array
		$appearance = wp_parse_args( $appearance, array(
			'rank'        => true,
			'avatar'      => true,
			'avatar_size' => '64',
			'name'        => true,
			'date'        => true,
			'score'       => true,
			'max_score'   => true,
			'percentage'  => true,
			'comment'     => true,
			'heading'     => true,
			'image'       => true,
			'meta'        => true,
			'time'        => true,
		) );

		// Calculate the colspan
		$colspan = 0;
		foreach ( array( 'name', 'score', 'max_score', 'percentage', 'date', 'comment', 'rank', 'time' ) as $key ) {
			if ( true === $appearance[ $key ] ) {
				$colspan++;
			}
		}

		// Calculate the avatar size
		$avatar_size = (int) $appearance['avatar_size'];
		if ( 0 == $avatar_size ) {
			$avatar_size = 64;
		}

		// Default the labels array
		$labels = wp_parse_args( $labels, array(
			'rank'       => __( 'Rank', 'ipt_fsqm' ),
			'name'       => __( 'Name', 'ipt_fsqm' ),
			'date'       => __( 'Date', 'ipt_fsqm' ),
			'score'      => __( 'Score', 'ipt_fsqm' ),
			'max_score'  => __( 'Out of', 'ipt_fsqm' ),
			'percentage' => __( 'Percentage', 'ipt_fsqm' ),
			'comment'    => __( 'Remarks', 'ipt_fsqm' ),
			'time'       => __( 'Time', 'ipt_fsqm' ),
		) );

		// Get just the data
		$data = $wpdb->get_results( $wpdb->prepare( "SELECT id, f_name, l_name, email, date, score, max_score, ROUND( ( score / max_score ) * 100, 2 ) as percentage, comment, user_id, time FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d ORDER BY percentage DESC, time ASC", $this->form_id ) );

		// perpare the JS settings
		$settings = array(
			'type' => 'form',
		);

		// Prepare the rank
		$rank = 1;

		// All set, start the output
		?>
		<div class="ipt_eform_leaderboard ipt_eform_leaderboard_form ipt_uif_front ipt_uif_common" data-ui-theme="<?php echo esc_attr( json_encode( $theme_element['include'] ) ); ?>" data-ui-theme-id="<?php echo esc_attr( $theme ); ?>" data-settings="<?php echo esc_attr( json_encode( $settings ) ); ?>">
			<noscript>
				<div class="ipt_fsqm_form_message_noscript ui-widget ui-widget-content ui-corner-all">
					<div class="ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
						<h3><?php _e( 'Javascript is disabled', 'ipt_fsqm' ); ?></h3>
					</div>
					<div class="ui-widget-content ui-corner-bottom">
						<p><?php _e( 'Javascript is disabled on your browser. Please enable it in order to use this form.', 'ipt_fsqm' ); ?></p>
					</div>
				</div>
			</noscript>

			<?php $form->ui->ajax_loader( false, '', array(), true, __( 'Loading', 'ipt_fsqm' ), array( 'ipt_uif_init_loader' ) ); ?>
			<div style="display: none" class="ipt_uif_hidden_init ipt_eform_lb_main_container ui-widget-content ui-corner-all">
				<div class="ipt_eform_leaderboard_welcome">
					<?php if ( '' !== $form->settings['theme']['logo'] && true == $appearance['image'] ) : ?>
						<div class="ipt_eform_leaderboard_form_logo">
							<img src="<?php echo esc_attr( $form->settings['theme']['logo'] ); ?>" class="ipt_eform_lb_logo ui-corner-all" />
						</div>
					<?php endif; ?>
					<?php if ( true === $appearance['heading'] ) : ?>
						<h2 class="ipt_eform_lb_title"><?php echo $form->name; ?></h2>
					<?php endif; ?>
					<?php if ( null !== $content && '' !== $content ) : ?>
						<div class="ipt_fsqm_lb_msg">
							<?php echo do_shortcode( wpautop( $content ) ); ?>
						</div>
					<?php endif; ?>
				</div>

				<div class="ipt_eform_leaderboard_data">
					<table class="ipt_eform_lb_table ipt-eform-lb-table">
						<thead>
							<?php $this->populate_leaderboard_table_head( $appearance, $labels ); ?>
						</thead>
						<tfoot>
							<?php $this->populate_leaderboard_table_head( $appearance, $labels ); ?>
						</tfoot>
						<tbody>
							<?php if ( empty( $data ) ) : ?>
								<tr>
									<td colspan="<?php echo $colspan; ?>"><?php _e( 'No Submissions yet!', 'ipt_fsqm' ); ?></td>
								</tr>
							<?php else : ?>
								<?php foreach ( $data as $et ) : ?>
									<?php
									$good_name = __( 'Anonymous', 'ipt_fsqm' );
									if ( '' !== $et->f_name || '' !== $et->l_name ) {
										$good_name = $et->f_name . ' ' . $et->l_name;
									}
									?>
									<tr>
										<?php if ( true == $appearance['name'] ) : ?>
											<td class="lb-name" data-sort="<?php echo $rank; ?>">
												<?php if ( $appearance['rank'] ) : ?>
													<div class="eform-lb-rank">
														<?php /* translators: %1$s is replaced by rank label, %2$d is replaced by rank */ ?>
														<span class="numeric-id" title="<?php echo esc_attr( sprintf( _x( '%1$s: %2$d', 'ipt_fsqm' ), $labels['rank'], $rank ) ); ?>">
															#<?php echo $rank; ?>
														</span>
													</div>
												<?php endif; ?>
												<?php if ( true == $appearance['avatar'] ) : ?>
													<div class="ipt_eform_lb_avatar">
														<?php echo get_avatar( ( 0 == $et->user_id ? $et->email : $et->user_id ), $avatar_size ); ?>
													</div>
												<?php endif; ?>
												<h5 class="ipt_eform_lb_username"><?php echo $good_name; ?></h5>
												<?php $user_meta = $this->get_user_stat( $et->user_id ); ?>
												<?php if ( $appearance['meta'] && ! empty( $user_meta ) ) : ?>
													<div class="ipt_eform_lb_umeta">
														<h6><?php $form->ui->print_icon_by_class( 'drawer2', false ); ?><?php printf( _n( '%d Submission', '%d Submissions', $user_meta['total'], 'ipt_fsqm' ), $user_meta['total'] ); ?></h6>
														<h6><?php $form->ui->print_icon_by_class( 'quill', false ); ?><?php printf( __( '%1$s%% Average', 'ipt_fsqm' ), number_format_i18n( $user_meta['average'] * 100, 2 ) ); ?></h6>
													</div>
												<?php endif; ?>
											</td>
										<?php endif; ?>
										<?php if ( true == $appearance['score'] ) : ?>
											<td class="lb-score"><?php echo $et->score; ?></td>
										<?php endif; ?>
										<?php if ( true == $appearance['max_score'] ) : ?>
											<td class="lb-mscore"><?php echo $et->max_score; ?></td>
										<?php endif; ?>
										<?php if ( true == $appearance['percentage'] ) : ?>
											<td class="lb-pscore"><?php echo $et->percentage; ?></td>
										<?php endif; ?>
										<?php if ( $appearance['time'] ) : ?>
											<td class="lb-time" data-sort="<?php echo esc_attr( $et->time ); ?>"><?php echo $form->ui->seconds_to_words( $et->time, __( 'N/A', 'ipt_fsqm' ) ); ?></td>
										<?php endif; ?>
										<?php if ( true == $appearance['date'] ) : ?>
											<td class="lb-date"><?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $et->date ) ); ?></td>
										<?php endif; ?>
										<?php if ( true == $appearance['comment'] ) : ?>
											<td><?php echo $et->comment; ?></td>
										<?php endif; ?>
									</tr>
									<?php $rank++; ?>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php
	}

	public function category_leaderboard( $theme = 'bootstrap' ) {

	}

	public function user_leaderboard( $theme = 'bootstrap' ) {

	}

	/*==========================================================================
	 * Some internal helpers
	 *========================================================================*/

	protected function populate_leaderboard_table_head( $appearance, $labels ) {
		?>
		<tr>
			<?php if ( $appearance['name'] ) : ?>
				<th class="lb-name desktop tablet-l tablet-p mobile-l mobile-p"><?php echo $labels['name']; ?></th>
			<?php endif; ?>
			<?php if ( $appearance['score'] ) : ?>
				<th class="lb-score desktop tablet-l tablet-p mobile-l mobile-p"><?php echo $labels['score']; ?></th>
			<?php endif; ?>
			<?php if ( $appearance['max_score'] ) : ?>
				<th class="lb-mscore desktop tablet-l"><?php echo $labels['max_score']; ?></th>
			<?php endif; ?>
			<?php if ( $appearance['percentage'] ) : ?>
				<th class="lb-pscore desktop tablet-l"><?php echo $labels['percentage']; ?></th>
			<?php endif; ?>
			<?php if ( $appearance['time'] ) : ?>
				<th class="lb-time desktop"><?php echo $labels['time']; ?></th>
			<?php endif; ?>
			<?php if ( $appearance['date'] ) : ?>
				<th class="lb-date desktop"><?php echo $labels['date']; ?></th>
			<?php endif; ?>
			<?php if ( $appearance['comment'] ) : ?>
				<th class="lb-comment desktop"><?php echo $labels['comment']; ?></th>
			<?php endif; ?>
		</tr>
		<?php
	}

	/**
	 * Populates the current user variable once
	 *
	 * If called multiple times, then it will make sure
	 * the db heavy things are executed only once
	 *
	 * When it is called, the self::$current_user is populated accordingly
	 */
	protected function populate_current_user() {
		// If it is already populated
		// Then no need to do it again
		if ( ! is_null( self::$current_user ) ) {
			return;
		}

		// Get the current user
		$user = wp_get_current_user();

		// Check if user is logged in and wp gave right instance
		if ( ! is_user_logged_in() || ! ( $user instanceof WP_User ) ) {
			$user = false;
		}

		self::$current_user = $user;
	}

	/**
	 * Gets the user stat.
	 * It returns total submission and average percentage
	 *
	 * @param      int    $user_id  The user identifier
	 *
	 * @return     array   The user stat.
	 */
	protected function get_user_stat( $user_id ) {
		global $wpdb, $ipt_fsqm_info;
		// Static variable for one time calculation
		static $user_stat = array();
		if ( isset( $user_stat[ $user_id ] ) ) {
			return $user_stat[ $user_id ];
		}

		if ( 0 == $user_id ) {
			return array();
		}

		// Now get the meta
		$total_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} WHERE user_id = %d", $user_id ) );
		$avg_score = $wpdb->get_var( $wpdb->prepare( "SELECT AVG((score/max_score)) FROM {$ipt_fsqm_info['data_table']} WHERE user_id = %d", $user_id ) );

		// Assign to the static variable
		$user_stat[ $user_id ] = array(
			'total' => $total_count,
			'average' => $avg_score,
		);

		return $user_stat[ $user_id ];
	}

	/*==========================================================================
	 * Shortcode handlers
	 *========================================================================*/

	/**
	 * Callback for the leaderboard form shortcode
	 * It populates the content of [ipt_eform_lb_form form_id="x"]
	 *
	 * The main class is instantiated and executed inside this
	 *
	 * @param      array  $atts     The attributes
	 * @param      string|null  $content  The content
	 *
	 * @return     string  HTML output of the shortcode
	 */
	public static function shortcode_leaderboard_form_cb( $atts, $content = null ) {
		// Sanitize the attributes
		$atts = shortcode_atts( array(
			'form_id'     => 0,
			'appearance'  => '',
			'lname'       => __( 'Name', 'ipt_fsqm' ),
			'ldate'       => __( 'Date', 'ipt_fsqm' ),
			'lscore'      => __( 'Score', 'ipt_fsqm' ),
			'lmax_score'  => __( 'Out of', 'ipt_fsqm' ),
			'lpercentage' => __( 'Percentage', 'ipt_fsqm' ),
			'lcomment'    => __( 'Remarks', 'ipt_fsqm' ),
			'lrank'       => __( 'Rank', 'ipt_fsqm' ),
			'ltime'       => __( 'Time', 'ipt_fsqm' ),
		), $atts, 'ipt_eform_lb_form' );

		// Now JSON decode
		$default_appearance = array(
			'rank'        => true,
			'avatar'      => true,
			'avatar_size' => '64',
			'name'        => true,
			'date'        => true,
			'score'       => true,
			'max_score'   => true,
			'percentage'  => true,
			'comment'     => true,
			'heading'     => true,
			'image'       => true,
			'meta'        => true,
			'time'        => true,
		);
		if ( '' !== $atts['appearance'] ) {
			$atts['appearance'] = json_decode( $atts['appearance'], true );
			if ( is_null( $atts['appearance'] ) ) {
				$atts['appearance'] = $default_appearance;
			}
		} else {
			$atts['appearance'] = $default_appearance;
		}

		// Create the labels array
		$labels = array(
			'name'       => $atts['lname'],
			'date'       => $atts['ldate'],
			'score'      => $atts['lscore'],
			'max_score'  => $atts['lmax_score'],
			'percentage' => $atts['lpercentage'],
			'comment'    => $atts['lcomment'],
			'rank'       => $atts['lrank'],
			'time'       => $atts['ltime'],
		);

		// Create a new leaderboard instance
		$lb = new IPT_EForm_LeaderBoard( $atts['form_id'], 'form' );

		// Start the buffer
		ob_start();
		// Generate output
		$lb->form_leaderboard( $atts['appearance'], $labels, $content );
		// Get and stop output buffering
		$output = ob_get_clean();

		// Now compress the output HTML
		if ( WP_DEBUG !== true ) {
			$output = IPT_FSQM_Minify_HTML::minify( $output );
		}

		// Return
		return $output;
	}
}
