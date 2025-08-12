<?php
/**
 * WP Feedback, Surver & Quiz Manager - Pro Form Elements Class
 * Data
 * Provides abstraction for submitted data
 *
 * It is backward compatible with version < 2.x
 *
 * @todo #474
 *
 * @package eForm - WordPress Form Builder
 * @subpackage Form\Data
 * @author Swashata Ghosh <swashata@intechgrity.com>
 * @codeCoverageIgnore
 */
class IPT_FSQM_Form_Elements_Data extends IPT_FSQM_Form_Elements_Base {
	public $data;
	public $data_id;
	public $score_img;
	public $email_styling;
	public $esb;

	public $admin_update = false;
	public $user_update = false;
	public $send_mail = true;

	public $doing_update;

	public $smtp_conf = array();
	public $reply_to = array();

	public $conditional_hidden_blacklist = array();
	public $conditional_validation_cache = array();

	public $for_email_quick_preview = false;
	public $for_pdf_quick_preview = false;
	public $for_admin_quick_preview = null;

	private $layout_blacklists = array();

	public $submission_db_elms = array();

	protected $core_reg_errors = [];

	public function __construct( $data_id = null, $form_id = null ) {
		parent::__construct( $form_id, false );
		$this->reset_conditional_blacklists();
		$this->init( $data_id, $form_id );
		$this->doing_update = false;
		$this->submission_db_elms = array(
			'f_name' => array(
				'm_type' => 'pinfo',
				'key' => null,
			),
			'l_name' => array(
				'm_type' => 'pinfo',
				'key' => null,
			),
			'email' => array(
				'm_type' => 'pinfo',
				'key' => null,
			),
			'phone' => array(
				'm_type' => 'pinfo',
				'key' => null,
			),
			'payment' => array(
				'm_type' => 'pinfo',
				'key' => null,
			),
			'guestblog' => array(
				'm_type' => 'pinfo',
				'key' => null,
			),
		);
	}

	protected function get_table_styling() {
		return array(
			'th'              => '',
			'qth'             => 'font-weight: bold; padding: 10px;',
			'headth'          => '',
			'footth'          => '',
			'td'              => '',
			'tdc'             => $this->get_score_highlighted_style_value( false ),
			'tdsm'            => 'text-align: right;',
			'td_upload'       => '',
			'icons'           => 'width: 32px; text-align: center;',
			'iconshead'       => 'width: 32px; text-align: center;',
			'img_icon'         => 'width: 16px; height: 16px; display: block; margin: 0 auto;',
			'td_center'       => '',
			'description'     => 'font-size: 0.7em; text-transform: lowercase; font-style: italic;',
			'descriptionhead' => 'font-size: 0.7em; text-transform: lowercase; font-style: italic;',
			'table'           => '',
			'inner_table'     => '',
			'tr'              => '',
			'headtr'          => '',
			'foottr'          => '',
			'thead'           => '',
			'tfoot'           => '',
			'tbody'           => '',
			'logo_container'  => '',
			'logo'            => '',
			'tdsep'           => 'padding: 0; height: 5px;',
		);
	}

	/**
	 *
	 *
	 * @global wpdb $wpdb
	 * @global type $ipt_fsqm_info
	 * @param type    $data_id
	 * @param type    $form_id
	 */
	public function init( $data_id = null, $form_id = null ) {
		global $wpdb, $ipt_fsqm_info;
		$this->data = null;
		$this->data_id = null;
		$this->core_reg_errors = [];


		if ( $data_id != null ) {
			//get the raw data
			$this->data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_info['data_table']} WHERE id = %d", $data_id ) );

			if ( null != $this->data ) {
				// Unserialize it
				$this->data->mcq = maybe_unserialize( $this->data->mcq );
				$this->data->freetype = maybe_unserialize( $this->data->freetype );
				$this->data->pinfo = maybe_unserialize( $this->data->pinfo );

				// Set the new form id
				$form_id = $this->data->form_id;

				// Set the new data id
				$this->data_id = $data_id;

				// Set epoc
				$this->data->epoc =
					'0000-00-00 00:00:00' === $this->data->epoc ?
						strtotime( $this->data->date ) :
						strtotime( $this->data->epoc );
			} else {
				$this->data_id = null;
			}

		}

		//Now call the parent::init if necessary
		if ( $this->form_id != $form_id || $form_id == null ) {
			parent::init( $form_id ); //Parent will set the form_id for us.
		}

		//Call the data standardization
		$this->compat_data();

		// Set the icon path
		$ui = IPT_Plugin_UIF_Front::instance();
		$theme = $this->get_theme_by_id( $this->settings['theme']['template'] );
		$this->score_img = '<img src="' . $ui->get_image_for_icon( 0xe0d3, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		$this->email_styling = $this->get_table_styling();
	}

	public function format_email_style() {
		$text_align = is_rtl() ? 'right' : 'left';
		$this->email_styling = array(
			'th' => 'border-collapse: collapse; border: 1px solid ' . $this->settings['email_template']['t_color'] . '; color: ' . $this->settings['email_template']['h_color'] . '; background: #fff; vertical-align: top; padding: 5px 5px 5px 10px; text-align: ' . $text_align . '; font-weight: normal;',
			'qth' => 'font-weight: bold; padding: 10px;',
			'headth' => 'border-collapse: collapse; border: 1px solid ' . $this->settings['email_template']['accent_bg'] . '; background-color: ' . $this->settings['email_template']['accent_bg'] . '; color: ' . $this->settings['email_template']['accent_color'] . '; vertical-align: top; padding: 10px 0 10px 20px; text-align: ' . $text_align . '; font-weight: normal;',
			'footth' => 'border-collapse: collapse; border: 1px solid ' . $this->settings['email_template']['accent_bg'] . '; background-color: ' . $this->settings['email_template']['accent_bg'] . '; color: ' . $this->settings['email_template']['accent_color'] . '; vertical-align: middle; padding: 10px 0 10px 20px; text-align: ' . $text_align . '; font-weight: normal;',
			'td' => 'border-collapse: collapse; border: 1px solid ' . $this->settings['email_template']['t_color'] . '; vertical-align: middle; padding: 5px; text-align: ' . $text_align . ';',
			'tdc' => 'border-collapse: collapse; border: 1px solid ' . $this->settings['email_template']['t_color'] . '; vertical-align: middle; padding: 5px; text-align: ' . $text_align . '; color: ' . $this->settings['summary']['correct_color'] . '; font-weight: bold;',
			'tdsm' => 'text-align: right;',
			'td_upload' => 'border-collapse: collapse; border: 1px solid ' . $this->settings['email_template']['t_color'] . '; vertical-align: middle; padding: 5px; text-align: center;',
			'icons' => 'border-collapse: collapse; border: 1px solid ' . $this->settings['email_template']['t_color'] . '; vertical-align: middle; text-align: center; width: 32px; padding: 5px; line-height: 1;',
			'iconshead' => 'border-collapse: collapse; border: 1px solid ' . $this->settings['email_template']['accent_bg'] . '; background-color: ' . $this->settings['email_template']['accent_bg'] . '; vertical-align: middle; text-align: center; width: 32px; padding: 10px; line-height: 1;',
			'img_icon' => 'width: 16px; height: 16px; display: block; margin: 0 auto;',
			'td_center' => 'border-collapse: collapse; border: 1px solid ' . $this->settings['email_template']['t_color'] . '; vertical-align: middle; text-align: center; padding: 5px;',
			'description' => 'display: block; font-size: 70%; color: #aaa; text-transform: lowercase; font-style: italic;',
			'descriptionhead' => 'display: block; font-size: 70%; color: #eee; text-transform: lowercase; font-style: italic;',
			'table' => 'border-collapse: collapse; font-family: Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; margin-top: 10px; margin: bottom: 10px; width: 100%; border-collapse: collapse; border: 0 none; background-color: #fff; color: ' . $this->settings['email_template']['color'] . '; vertical-align: middle; text-align: ' . $text_align . ';',
			'inner_table' => 'border-collapse: collapse; font-family: Helvetica, Arial, sans-serif; font-size: 12px; font-weight: normal; margin: 0px; width: 100%; border-collapse: collapse; border: 0 none; background-color: #fff; color: ' . $this->settings['email_template']['color'] . '; vertical-align: top; text-align: ' . $text_align . ';',
			'tr' => 'border-collapse: collapse; border: 1px solid ' . $this->settings['email_template']['t_color'] . '; vertical-align: middle; padding: 0;',
			'headtr' => 'border-collapse: collapse; border: 1px solid ' . $this->settings['email_template']['accent_bg'] . '; background: ' . $this->settings['email_template']['accent_bg'] . '; color: ' . $this->settings['email_template']['accent_color'] . '; vertical-align: middle; padding: 0;',
			'foottr' => 'border-collapse: collapse; border: 1px solid ' . $this->settings['email_template']['accent_bg'] . '; background: ' . $this->settings['email_template']['accent_bg'] . '; color: ' . $this->settings['email_template']['accent_color'] . '; vertical-align: middle; padding: 0;',
			'thead' => 'border-collapse: collapse; font-family: Helvetica, Arial, sans-serif; font-weight: normal; border: 1px solid ' . $this->settings['email_template']['accent_bg'] . '; background: ' . $this->settings['email_template']['accent_bg'] . '; color: ' . $this->settings['email_template']['accent_color'] . '; vertical-align: middle; padding: 0;',
			'tfoot' => 'border-collapse: collapse; font-family: Helvetica, Arial, sans-serif; font-weight: normal; border: 1px solid ' . $this->settings['email_template']['accent_bg'] . '; background: ' . $this->settings['email_template']['accent_bg'] . '; color: ' . $this->settings['email_template']['accent_color'] . '; vertical-align: middle; padding: 0;',
			'tbody' => 'border-collapse: collapse; font-family: Helvetica, Arial, sans-serif; font-weight: normal; border: 1px solid ' . $this->settings['email_template']['t_color'] . '; color: ' . $this->settings['email_template']['color'] . '; vertical-align: middle; padding: 0;',
			'logo_container' => 'margin: 10px auto 20px; text-align: center;',
			'logo' => 'border: 0 none; max-width: 100%; height: auto;',
			'tdsep' => 'background-color: ' . $this->settings['email_template']['t_color'] . '; padding: 0; height: 5px;',
		);
		$this->email_styling = apply_filters( 'ipt_fsqm_form_elements_quick_preview_email_style', $this->email_styling, $this );
	}

	public function reset_email_styling() {
		$this->esb = $this->email_styling;
		$this->email_styling = $this->get_table_styling();
	}

	public function restore_email_styling() {
		$this->email_styling = $this->esb;
	}

	/*==========================================================================
	 * Form Backend
	 * Can User Edit
	 * Process Save
	 * Email
	 * Score
	 *========================================================================*/
	public function can_user_edit() {
		if ( $this->settings['general']['can_edit'] == false ) {
			return false;
		}

		if ( $this->get_submission_lock_status() ) {
			return false;
		}

		if ( $this->settings['limitation']['expiration_limit'] != '' && $this->settings['limitation']['no_edit_expiration'] == true ) {
			$expiration_instant = strtotime( $this->settings['limitation']['expiration_limit'] );
			$current_time = current_time( 'timestamp' );
			if ( $current_time >= $expiration_instant ) {
				return false;
			}
		}

		$limit = (float) $this->settings['general']['edit_time'];
		if ( $limit == 0 ) {
			return true;
		}

		$difference = current_time( 'timestamp' ) - strtotime( $this->data->date );

		if ( $difference > ( $limit * 3600 ) ) {
			return false;
		} else {
			return true;
		}
	}
	/**
	 * Save the form
	 * @param  bool $admin_update If admin is performing an update Default null = autoguess
	 * @param  bool $user_update  If user is performing an update Default null = autoguess
	 * @param  bool $send_mail    If send emails, default true
	 * @return array              Results
	 */
	public function save_form( $admin_update = null, $user_update = null, $send_mail = null ) {
		$name_prefix = 'ipt_fsqm_form_' . $this->form_id;
		$errors = array();
		//Return if no data
		if ( !isset( $this->post[$name_prefix] ) ) {
			$errors[] = array(
				'id'   => $name_prefix,
				'msgs' => array( __( 'No data submitted.', 'ipt_fsqm' ) ),
			);
			return array(
				'success' => false,
				'errors'  => $errors,
			);
		}

		//Set the data
		$this->data->design   = isset( $this->post[$name_prefix]['design'] )? $this->post[$name_prefix]['design'] : array();
		$this->data->mcq      = isset( $this->post[$name_prefix]['mcq'] )? $this->post[$name_prefix]['mcq'] : array();
		$this->data->freetype = isset( $this->post[$name_prefix]['freetype'] )? $this->post[$name_prefix]['freetype'] : array();
		$this->data->pinfo    = isset( $this->post[$name_prefix ]['pinfo'] )? $this->post[$name_prefix ]['pinfo'] : array();
		if ( isset( $this->post[$name_prefix ]['time'] ) ) {
			$this->data->time = absint( $this->post[$name_prefix ]['time'] );
		}

		//Get the admin remarks if any
		if ( $this->data_id != null && isset( $this->post[$name_prefix]['comment'] ) ) {
			$this->data->comment = $this->post[$name_prefix]['comment'];
		}

		$this->data->url_track = isset( $this->post[$name_prefix ]['url_track'] ) ? strip_tags( $this->post[$name_prefix ]['url_track'] ) : $this->data->url_track;

		// Set the updation
		if ( $this->data_id == null ) {
			$this->doing_update = false;
			$this->admin_update = false;
			$this->user_update = false;
		} else {
			$this->doing_update = true;
			$this->admin_update = true;
			$this->user_update = false;
		}

		// Override update types if set
		if ( $this->data_id != null && $admin_update !== null && is_bool( $admin_update ) ) {
			$this->admin_update = $admin_update;
		}
		if ( $this->data_id != null && $user_update !== null && is_bool( $user_update ) ) {
			$this->user_update = $user_update;
		}
		if ( $send_mail !== null && is_bool( $send_mail ) ) {
			$this->send_mail = $send_mail;
		}


		//Process it
		return $this->process_save();
	}

	public function process_save() {
		global $wpdb, $ipt_fsqm_info, $ipt_fsqm_settings;
		$errors = array();

		// Loop through layout and design elements and blacklist the hidden ones
		$this->reset_conditional_blacklists();
		$this->blacklist_conditional_hiddens();

		//Process the pinfo
		foreach ( (array) $this->pinfo as $p_key => $pinfo ) {
			$data = $this->get_submission_from_data( array(
				'type' => $pinfo['type'],
				'm_type' => $pinfo['m_type'],
				'key' => $p_key,
			) );
			//Validate it
			$error = array();
			$validation_result = $this->validate_data_against_element( $pinfo, $data, $p_key );
			if ( $validation_result['data_tampering'] == true ) {
				$error[] = __( 'Warning! Data tampering detected.', 'ipt_fsqm' );
			}
			if ( $validation_result['required_validation'] == false ) {
				$error[] = __( 'Required', 'ipt_fsqm' );
			}
			if ( !empty( $validation_result['errors'] ) ) {
				$error = array_merge( $error, $validation_result['errors'] );
			}
			if ( !empty( $error ) ) {
				$errors[] = array(
					'id' => 'ipt_fsqm_form_' . $this->form_id . '_pinfo_' . $p_key,
					'msgs' => $error,
				);
			} else {
				$this->data->pinfo[$p_key] = $validation_result['data'];
				//Set the inbuilt dbmaps
				switch ( $data['type'] ) {
				case 'f_name' :
					$this->data->f_name = $this->data->pinfo[$p_key]['value'];
					$this->submission_db_elms['f_name']['key'] = $p_key;
					break;
				case 'l_name' :
					$this->data->l_name = $this->data->pinfo[$p_key]['value'];
					$this->submission_db_elms['l_name']['key'] = $p_key;
					break;
				case 'email' :
					$this->data->email = $this->data->pinfo[$p_key]['value'];
					$this->submission_db_elms['email']['key'] = $p_key;
					break;
				case 'phone' :
					$this->data->phone = $this->data->pinfo[$p_key]['value'];
					$this->submission_db_elms['phone']['key'] = $p_key;
					break;
				case 'payment' :
					$this->submission_db_elms['payment']['key'] = $p_key;
					break;
				case 'guestblog' :
					$this->submission_db_elms['guestblog']['key'] = $p_key;
					break;
				}
			}
		}

		if ( $this->data->f_name == '' ) {
			$this->data->f_name = __( 'Anonymous', 'ipt_fsqm' );
		}

		// Filter the submission_db_elms
		$this->submission_db_elms = apply_filters( 'ipt_fsqm_submission_db_elms', $this->submission_db_elms );

		// Check for email limits, ip limits & user limits
		// But only if this is not an update
		// Now, for an update, we could change the ip address for users
		// But that would hurt the integrity of the submission
		// It is just better to leave the IP address original
		if ( ! $this->doing_update ) {
			if ( $this->settings['limitation']['email_limit'] != 0 && '' != $this->data->email ) {
				$total_emails = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d AND email = %s", $this->form_id, $this->data->email ) );
				if ( $total_emails >= $this->settings['limitation']['email_limit'] ) {
					$errors[] = array(
						'id' => 'fsqm_email_limit',
						'msgs' => array( __( 'Validation Error', 'ipt_fsqm' ), __( 'Submission limit from this email address has been exceeded.', 'ipt_fsqm' ) )
					);
				}
			}
			if ( $this->settings['limitation']['ip_limit'] != 0 && '' != $this->data->ip ) {
				$total_ip = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d AND ip = %s", $this->form_id, $this->data->ip ) );
				if ( $total_ip >= $this->settings['limitation']['ip_limit'] ) {
					$errors[] = array(
						'id' => 'fsqm_ip_limit',
						'msgs' => array( __( 'Validation Error', 'ipt_fsqm' ), __( 'Submission limit from this IP address has been exceeded.', 'ipt_fsqm' ) )
					);
				}
			}

			if ( $this->settings['limitation']['user_limit'] != 0 && 0 != $this->data->user_id ) {
				$total_users = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d AND user_id = %d", $this->form_id, $this->data->user_id ) );
				if ( $total_users >= $this->settings['limitation']['user_limit'] ) {
					$errors[] = array(
						'id' => '',
						'msgs' => array( __( 'Your submission limit has been exceeded.', 'ipt_fsqm' ) ),
					);
				}
			}
		}

		// Now check if the form requires users to be logged in
		if ( $this->admin_update === false ) {
			if ( $this->settings['limitation']['logged_in'] == true && ! is_user_logged_in() ) {
				$errors[] = array(
					'id' => '',
					'msgs' => array( __( 'You need to be logged in.', 'ipt_fsqm' ) ),
				);
			}
		}

		//Process the mcqs
		$score = 0;
		$max_score = 0;
		foreach ( (array) $this->mcq as $m_key => $mcq ) {
			$data = $this->get_submission_from_data( array(
					'type' => $mcq['type'],
					'm_type' => $mcq['m_type'],
					'key' => $m_key,
				) );
			//Validate it
			$error = array();
			$validation_result = $this->validate_data_against_element( $mcq, $data, $m_key );
			if ( $validation_result['data_tampering'] == true ) {
				$error[] = __( 'Warning! Data tampering detected.', 'ipt_fsqm' );
			}
			if ( $validation_result['required_validation'] == false ) {
				$error[] = __( 'Required', 'ipt_fsqm' );
			}
			if ( !empty( $validation_result['errors'] ) ) {
				$error = array_merge( $error, $validation_result['errors'] );
			}
			if ( !empty( $error ) ) {
				$errors[] = array(
					'id' => 'ipt_fsqm_form_' . $this->form_id . '_mcq_' . $m_key,
					'msgs' => $error,
				);
			} else {
				$this->data->mcq[$m_key] = $validation_result['data'];

				// Process the score, if there
				// But first check if the element is conditionally hidden
				if ( $validation_result['conditional_hidden'] === true ) {
					continue; // Go to the first of for loop
				}
				$max_possible_score = 0;
				$actual_score = 0;
				$collect_score = false;
				switch ( $mcq['type'] ) {
					case 'radio' :
					case 'select' :
						$possible_scores = array();
						foreach ( $mcq['settings']['options'] as $o_key => $op ) {
							if ( trim( $op['score'] ) != '' ) {
								$collect_score = true;
								$possible_scores[$o_key] = (float) $op['score'];
							} else {
								$possible_scores[$o_key] = 0;
							}
							if ( in_array( (string) $o_key, $this->data->mcq[$m_key]['options'] ) ) {
								$actual_score = $possible_scores[$o_key];
							}
						}
						if ( ! empty( $possible_scores ) ) {
							$max_possible_score = max( $possible_scores );
						}
						break;
					case 'checkbox' :
						foreach ( $mcq['settings']['options'] as $o_key => $op ) {
							if ( '' != trim( $op['score'] ) ) {
								$collect_score = true;
								if ( $op['score'] > 0 ) {
									$max_possible_score += (float) $op['score'];
								}
							}
							if ( in_array( (string) $o_key, $this->data->mcq[$m_key]['options'] ) ) {
								$actual_score += (float) $op['score'];
							}
						}
						break;
					case 'thumbselect' :
						// If it is checkboxe
						if ( $mcq['settings']['multiple'] ) {
							foreach ( $mcq['settings']['options'] as $o_key => $op ) {
								if ( '' != trim( $op['score'] ) ) {
									$collect_score = true;
									if ( $op['score'] > 0 ) {
										$max_possible_score += (float)  $op['score'];
									}
								}
								if ( in_array( (string) $o_key, $this->data->mcq[$m_key]['options'] ) ) {
									$actual_score += (float) $op['score'];
								}
							}
						// If it is radio
						} else {
							$possible_scores = array();
							foreach ( $mcq['settings']['options'] as $o_key => $op ) {
								if ( trim( $op['score'] ) != '' ) {
									$collect_score = true;
									$possible_scores[$o_key] = (float) $op['score'];
								} else {
									$possible_scores[$o_key] = 0;
								}
								if ( in_array( (string) $o_key, $this->data->mcq[$m_key]['options'] ) ) {
									$actual_score = $possible_scores[$o_key];
								}
							}
							if ( ! empty( $possible_scores ) ) {
								$max_possible_score = max( $possible_scores );
							}
						}
						break;
					case 'smileyrating' :
						$possible_scores = array();
						foreach ( $mcq['settings']['scores'] as $o_key => $op ) {
							if ( trim( $op ) != '' ) {
								$collect_score = true;
								$possible_scores[$o_key] = (float) $op;
							} else {
								$possible_scores[$o_key] = 0;
							}
							if ( $this->data->mcq[$m_key]['option'] == $o_key ) {
								$actual_score = $possible_scores[$o_key];
							}
						}
						if ( ! empty( $possible_scores ) ) {
							$max_possible_score = max( $possible_scores );
						}
						break;
					case 'matrix' :
						// Tricky business
						$possible_scores = array();
						$per_row_scores = array();
						foreach ( $mcq['settings']['rows'] as $r_key => $row ) {
							$possible_scores[$r_key] = array();
							$per_row_scores[$r_key] = array();
							foreach ( $mcq['settings']['columns'] as $c_key => $col ) {
								if ( isset( $mcq['settings']['scores'] ) && is_array( $mcq['settings']['scores'] ) && isset( $mcq['settings']['scores'][$c_key] ) && '' != trim( $mcq['settings']['scores'][$c_key] ) ) {
									$collect_score = true;
									$possible_scores[$r_key][] = (float) $mcq['settings']['scores'][$c_key];

									if ( isset( $this->data->mcq[$m_key]['rows'][$r_key] ) && in_array( (string) $c_key, (array) $this->data->mcq[$m_key]['rows'][$r_key] ) ) {
										$per_row_scores[$r_key][] = (float) $mcq['settings']['scores'][$c_key];
									}
								}
							}
						}

						// Now iterate and set the true score
						if ( $collect_score ) {
							foreach ( $per_row_scores as $r_key => $row_scores ) {
								$actual_score += array_sum( (array) $row_scores );
							}

							if ( $mcq['settings']['multiple'] == true ) {
								foreach ( $possible_scores as $r_key => $pscores ) {
									foreach ( (array) $pscores as $mscore ) {
										if ( $mscore > 0 ) {
											$max_possible_score += $mscore;
										}
									}
								}
							} else {
								foreach ( $possible_scores as $r_key => $pscores ) {
									$max_possible_score += max( (array) $pscores );
								}
							}
						}
						break;

					case 'matrix_dropdown' :
						// Very very tricky business
						$possible_scores = array();
						$per_row_scores = array();
						foreach ( (array) $this->data->mcq[$m_key]['rows'] as $r_key => $row ) {
							if ( isset( $mcq['settings']['rows'][$r_key] ) ) {
								$per_row_scores[$r_key] = array();
								foreach ( (array) $row as $c_key => $column ) {
									if ( is_array( $column ) && isset( $mcq['settings']['columns'][ $c_key ] ) ) {
										foreach ( $column as $o_key ) {
											if ( isset( $mcq['settings']['options'][ $o_key ] ) ) {
												$selected_option = $mcq['settings']['options'][ $o_key ];
												if ( $selected_option['score'] != '' ) {
													$collect_score = true;
													$multiplier = 1;
													if ( $mcq['settings']['scores'][$c_key] != '' && is_numeric( $mcq['settings']['scores'][$c_key] ) ) {
														$multiplier = (float) $mcq['settings']['scores'][$c_key];
													}
													$per_row_scores[$r_key][] = $selected_option['score'] * $multiplier;
												}
											}
										}
										unset( $o_key );
									} else {
										if ( isset( $mcq['settings']['columns'][$c_key] ) && isset( $mcq['settings']['options'][$column] ) ) {
											$selected_option = $mcq['settings']['options'][$column];
											if ( $selected_option['score'] != '' ) {
												$collect_score = true;
												$multiplier = 1;
												if ( $mcq['settings']['scores'][$c_key] != '' && is_numeric( $mcq['settings']['scores'][$c_key] ) ) {
													$multiplier = (float) $mcq['settings']['scores'][$c_key];
												}
												$per_row_scores[$r_key][] = $selected_option['score'] * $multiplier;
											}
										}
									}
								}
							}
						}

						$max_op_score = 0;
						$min_op_score = 0;
						$max_op_total_score = 0;
						$min_op_total_score = 0;
						foreach ( $mcq['settings']['options'] as $op ) {
							if ( $op['score'] != '' ) {
								$collect_score = true;
								$max_op_score = max( array( $op['score'], $max_op_score ) );
								$min_op_score = min( array( $op['score'], $min_op_score ) );
								if ( $op['score'] > 0 ) {
									$max_op_total_score += $op['score'];
								} else {
									$min_op_total_score += $op['score'];
								}
							}
						}
						if ( $collect_score ) {
							$max_possible_score = 0;
							foreach ( (array) $mcq['settings']['columns'] as $c_key => $column ) {
								$multiplier = 1;
								if ( $mcq['settings']['scores'][$c_key] != '' && is_numeric( $mcq['settings']['scores'][$c_key] ) ) {
									$multiplier = (float) $mcq['settings']['scores'][$c_key];
								}
								if ( false == $mcq['settings']['multiple'] ) {
									if ( $multiplier > 0 ) {
										$max_possible_score += $max_op_score * $multiplier * count( $mcq['settings']['rows'] );
									} else {
										$max_possible_score += $min_op_score * $multiplier * count( $mcq['settings']['rows'] );
									}
								} else {
									if ( $multiplier > 0 ) {
										$max_possible_score += $max_op_total_score * $multiplier * count( $mcq['settings']['rows'] );
									} else {
										$max_possible_score += $min_op_total_score * $multiplier * count( $mcq['settings']['rows'] );
									}
								}

							}
							$actual_score = 0;
							foreach ( (array) $per_row_scores as $row_scores ) {
								$actual_score += array_sum( (array) $row_scores );
							}
						}
						break;
					case 'sorting' :
						// Now this is tricky
						$correct = true;
						$correct_positions = array_keys( $mcq['settings']['options'] );
						foreach ( (array) $this->data->mcq[$m_key]['order'] as $o_position => $o_key ) {
							if ( $correct_positions[$o_position] == $o_key ) {
								if ( trim( $mcq['settings']['options'][$o_key]['score'] ) != '' ) {
									$collect_score = true;
								}
								$actual_score += abs( floatval( $mcq['settings']['options'][$o_key]['score'] ) );
							} else {
								$correct = false;
							}
							$max_possible_score += abs( floatval( $mcq['settings']['options'][$o_key]['score'] ) );
						}
						if ( trim( $mcq['settings']['base_score'] ) != '' ) {
							$collect_score = true;
						}
						if ( $mcq['settings']['score_type'] == 'individual' ) {
							if ( $correct == true ) {
								$actual_score += abs( floatval( $mcq['settings']['base_score'] ) );
							}
							$max_possible_score += abs( floatval( $mcq['settings']['base_score'] ) );
						} else {
							$actual_score = $correct ? abs( $mcq['settings']['base_score'] ) : 0;
							$max_possible_score = abs( floatval( $mcq['settings']['base_score'] ) );
						}
						break;
					case 'slider' :
						if ( isset( $mcq['settings']['score'] ) && $mcq['settings']['score'] == true ) {
							$collect_score = true;
							$multiplier = (float) $mcq['settings']['score_multiplier'];
							if ( 0 == $multiplier ) {
								$multiplier = 1;
							}
							$max_possible_score = max( array( ( $mcq['settings']['min'] * $multiplier ), ( $mcq['settings']['max'] * $multiplier ) ) );
							$actual_score = ( (float) $this->data->mcq[$m_key]['value'] * $multiplier );
						}
						break;
					case 'range' :
						if ( isset( $mcq['settings']['score'] ) && $mcq['settings']['score'] == true ) {
							$collect_score = true;
							$multiplier = (float) $mcq['settings']['score_multiplier'];
							if ( 0 == $multiplier ) {
								$multiplier = 1;
							}
							$max_possible_score = max( array( ( $mcq['settings']['min'] * $multiplier ), ( $mcq['settings']['max'] * $multiplier ) ) );
							$actual_score = 0;
							$values = $this->data->mcq[$m_key]['values'];
							switch( $mcq['settings']['formula'] ) {
								case 'avg' :
									$actual_score = ( ( $values['min'] + $values['max'] ) / 2 ) * $multiplier;
									break;
								case 'add' :
									$actual_score = ( ( $values['min'] + $values['max'] ) * $multiplier );
									$max_possible_score = $mcq['settings']['max'] * 2 * $multiplier;
									break;
								case 'diff' :
									$actual_score = ( abs( $values['max'] - $values['min'] ) * $multiplier );
									$max_possible_score = ( abs( $mcq['settings']['max'] - $mcq['settings']['min'] ) * $multiplier );
									break;
								case 'min' :
									$actual_score = ( $values['min'] * $multiplier );
									$max_possible_score = max( array( ( $mcq['settings']['min'] * $multiplier ), ( $mcq['settings']['max'] * $multiplier ) ) );
									break;
								case 'max' :
									$actual_score = ( $values['max'] * $multiplier );
									$max_possible_score = max( array( ( $mcq['settings']['min'] * $multiplier ), ( $mcq['settings']['max'] * $multiplier ) ) );
									break;
							}
						}
						break;
					case 'grading' :
						if ( isset( $mcq['settings']['score'] ) && $mcq['settings']['score'] == true ) {
							$collect_score = true;
							$multiplier = (float) $mcq['settings']['score_multiplier'];
							if ( 0 == $multiplier ) {
								$multiplier = 1;
							}
							$max_possible_score = 0;
							$actual_score = 0;
							foreach ( $mcq['settings']['options'] as $sl_key => $sl_option ) {
								if ( ! is_array( $sl_option ) ) {
									// backward compatibility -2.4.0
									$sl_option = array(
										'label' => $sl_option,
										'prefix' => '',
										'suffix' => '',
									);
								}
								foreach ( array( 'min', 'max', 'step' ) as $ovkey ) {
									if ( ! isset( $sl_option[$ovkey] ) || '' == $sl_option[$ovkey] ) {
										$sl_option[$ovkey] = $mcq['settings'][$ovkey];
									}
								}

								$omax_possible_score = max( array( ( $sl_option['min'] * $multiplier ), ( $sl_option['max'] * $multiplier ) ) );
								$oactual_score = 0;
								$values = $this->data->mcq[$m_key]['options'][$sl_key];

								// Range
								if ( $mcq['settings']['range'] == true ) {
									switch( $mcq['settings']['formula'] ) {
										case 'avg' :
											$oactual_score = ( ( $values['min'] + $values['max'] ) / 2 ) * $multiplier;
											break;
										case 'add' :
											$oactual_score = ( ( $values['min'] + $values['max'] ) * $multiplier );
											$omax_possible_score = $mcq['settings']['max'] * 2 * $multiplier;
											break;
										case 'diff' :
											$oactual_score = ( abs( $values['max'] - $values['min'] ) * $multiplier );
											$omax_possible_score = ( abs( $mcq['settings']['max'] - $mcq['settings']['min'] ) * $multiplier );
											break;
										case 'min' :
											$oactual_score = ( $values['min'] * $multiplier );
											$omax_possible_score = max( array( ( $mcq['settings']['min'] * $multiplier ), ( $mcq['settings']['max'] * $multiplier ) ) );
											break;
										case 'max' :
											$oactual_score = ( $values['max'] * $multiplier );
											$omax_possible_score = max( array( ( $mcq['settings']['min'] * $multiplier ), ( $mcq['settings']['max'] * $multiplier ) ) );
											break;
									}

								// Slider
								} else {
									$oactual_score = ( (float) $values * $multiplier );
								}

								$max_possible_score += $omax_possible_score;
								$actual_score += $oactual_score;
							}
						}
						break;

					case 'starrating' :
					case 'scalerating' :
						if ( isset( $mcq['settings']['score'] ) && $mcq['settings']['score'] == true ) {
							$collect_score = true;
							$multiplier = (float) $mcq['settings']['score_multiplier'];
							if ( 0 == $multiplier ) {
								$multiplier = 1;
							}

							$max_possible_score = (float) $mcq['settings']['max'] * count( $mcq['settings']['options'] ) * $multiplier;
							$actual_score = 0;
							foreach ( $mcq['settings']['options'] as $r_key => $rating ) {
								$actual_score += (float) $this->data->mcq[$m_key]['options'][$r_key] * $multiplier;
							}
						}

						break;
				}
				if ( $collect_score ) {
					$this->data->mcq[$m_key]['scoredata'] = array(
						'score' => (float) $actual_score,
						'max_score' => (float) $max_possible_score,
					);
					$score += $actual_score;
					$max_score += $max_possible_score;
				}
			}
		}

		// Process the freetype
		$emails = array();
		$freetype_score = 0;
		$freetype_max_score = 0;
		$freetype_upload_cache = array();
		foreach ( (array) $this->freetype as $f_key => $freetype ) {
			$data = $this->get_submission_from_data( array(
				'type' => $freetype['type'],
				'm_type' => $freetype['m_type'],
				'key' => $f_key,
			) );

			//Validate it
			$error = array();
			$validation_result = $this->validate_data_against_element( $freetype, $data, $f_key );
			if ( $validation_result['data_tampering'] == true ) {
				$error[] = __( 'Warning! Data tampering detected.', 'ipt_fsqm' );
			}
			if ( $validation_result['required_validation'] == false ) {
				$error[] = __( 'Required', 'ipt_fsqm' );
			}
			if ( !empty( $validation_result['errors'] ) ) {
				$error = array_merge( $error, $validation_result['errors'] );
			}
			if ( !empty( $error ) ) {
				$errors[] = array(
					'id' => 'ipt_fsqm_form_' . $this->form_id . '_freetype_' . $f_key,
					'msgs' => $error,
				);
			} else {
				// Save the data to corresponding key
				$this->data->freetype[$f_key] = $validation_result['data'];

				// Now case specific processing
				switch ( $freetype['type'] ) {
					case 'feedback_large' :
					case 'feedback_small' :
						// Save the email
						if ( isset( $freetype['settings']['email'] ) && trim( $freetype['settings']['email'] ) != '' ) {
							$emails_in_freetype = explode( ',', $freetype['settings']['email'] );
							foreach ( $emails_in_freetype as $email ) {
								$email = trim( $email );
								if ( !isset( $emails[$email] ) ) {
									$emails[$email] = array(
										'title' => $this->admin_email_format( $this->settings['admin']['fsub'], $freetype['title'] ),
										'msgs' => array( '', '<h3>' . $freetype['title'] . '</h3>' . wpautop( $this->data->freetype[$f_key]['value'] ) ),
									);

									if ( $this->settings['admin']['send_from_user'] == true && '' != $this->data->email ) {
										$emails[$email]['from'] = array( $this->data->f_name . ' ' . $this->data->l_name, $this->data->email );
									}
								} else {
									$emails[$email]['msgs'][] = '<h3>' . $freetype['title'] . '</h3>' . wpautop( $this->data->freetype[$f_key]['value'] );
								}
							}
						}

						// Set the score (if any and only if shown)
						if ( $validation_result['conditional_hidden'] === false && isset( $this->freetype[$f_key]['settings']['score'] ) && '' != $this->freetype[$f_key]['settings']['score'] && is_numeric( $this->freetype[$f_key]['settings']['score'] ) ) {
							if ( $this->admin_update ) {
								// If admin is overriding the score
								$freetype_max_score += abs( $this->freetype[$f_key]['settings']['score'] );
								if ( '' != $data['score'] && is_numeric( $data['score'] ) ) {
									if ( $data['score'] > $this->freetype[$f_key]['settings']['score'] ) {
										$data['score'] = $this->freetype[$f_key]['settings']['score'];
									}
									$this->data->freetype[$f_key]['score'] = $data['score'];
									$freetype_score += $data['score'];
								}
							} else if ( $this->freetype[ $f_key ]['settings']['autoscore']['enabled'] ) {
								// Automatic scoring is enabled
								// So increase the freetype max score
								$freetype_max_score += abs( $this->freetype[$f_key]['settings']['score'] );
								// Calculate the score for this element
								$feedback_elm_score = $this->calculate_feedback_score( $this->freetype[ $f_key ]['settings']['score'], $this->freetype[ $f_key ]['settings']['autoscore'], $this->data->freetype[ $f_key ]['value'] );
								// Set it
								$this->data->freetype[ $f_key ]['score'] = (string) $feedback_elm_score;
								// Increase the freetype score counter
								$freetype_score += $feedback_elm_score;
							}

						}
						break;
					case 'upload' :
						// Cache the keys for later update
						if ( ! isset( $data['id'] ) ) {
							$data['id'] = array();
						}
						$freetype_upload_cache = array_merge( $freetype_upload_cache, (array) $data['id'] );
						break;
				}

			}
		}

		// Process the design, mainly captcha
		if ( $this->data_id == null && !$this->doing_update ) {
			$captchas = $this->get_keys_from_layouts_by_types( 'captcha', $this->layout );
			foreach ( $captchas as $c_key ) {
				$captcha = $this->design[$c_key];
				$data = $this->get_submission_from_data( array(
					'type' => 'captcha',
					'm_type' => 'design',
					'key' => $c_key,
				) );
				//Validate it
				$error = array();
				$validation_result = $this->validate_data_against_element( $captcha, $data, $c_key );
				if ( $validation_result['data_tampering'] == true ) {
					$error[] = __( 'Warning! Data tampering detected.', 'ipt_fsqm' );
				}
				if ( $validation_result['required_validation'] == false ) {
					$error[] = __( 'Required', 'ipt_fsqm' );
				}
				if ( !empty( $validation_result['errors'] ) ) {
					$error = array_merge( $error, $validation_result['errors'] );
				}
				if ( !empty( $error ) ) {
					$errors[] = array(
						'id' => 'ipt_fsqm_form_' . $this->form_id . '_design_' . $c_key,
						'msgs' => $error,
					);
				}
			}

			$gcaptchas = $this->get_keys_from_layouts_by_types( 'recaptcha', $this->layout );
			if ( ! empty( $gcaptchas ) ) {
				$recaptcha = $this->get_element_from_layout( array(
					'm_type' => 'design',
					'key' => $gcaptchas[0],
				) );
				if ( ! empty( $recaptcha ) ) {
					$recaptcha_data = $this->get_submission_from_data( array(
						'type' => 'recaptcha',
						'm_type' => 'design',
						'key' => $gcaptchas[0],
					) );
					$rcapi = new \ReCaptcha\ReCaptcha( $recaptcha['settings']['secret_key'] );
					$rcresp = $rcapi->verify( $recaptcha_data['recaptcha'], $_SERVER['REMOTE_ADDR'] );
					if ( ! $rcresp->isSuccess() ) {
						$error = array( __( 'reCaptcha was not verified successfully.', 'ipt_fsqm' ) );
						if ( current_user_can( 'manage_feedback' ) ) {
							foreach ( $rcresp->getErrorCodes() as $rcode ) {
								$error[] = $rcode;
							}
						}
						$errors[] = array(
							'id' => 'ipt_fsqm_form_' . $this->form_id . '_design_' . $gcaptchas[0],
							'msgs' => $error,
						);
					}
				}
			}
		}


		//Set the scores
		$this->data->score = $score + $freetype_score;
		$this->data->max_score = $max_score + $freetype_max_score;

		// Set the referer
		$this->data->referer = isset( $this->post['_wp_http_referer'] ) ? esc_url( 'http' . ( is_ssl() ? 's' : '' ) . '://' . @$_SERVER['HTTP_HOST'] . $this->post['_wp_http_referer'] ) : esc_url( @$_SERVER['HTTP_REFERER'] );
		if ( is_null( $this->data->referer ) ) {
			$this->data->referer = 'http://0.0.0.0'; // PHP-CLI
		}

		// Special case - User Registration through WP CORE
		// Check for username existence in case of user registration
		$this->set_registration_errors();
		if ( ! empty( $this->core_reg_errors ) ) {
			$errors = array_merge( $errors, $this->core_reg_errors );
		}

		// Filter the errors
		$errors = apply_filters( 'ipt_fsqm_filter_data_errors', $errors, $this );

		// Return it
		if ( !empty( $errors ) ) {
			$return = apply_filters( 'ipt_fsqm_filter_save_error', array(
				'success' => false,
				'errors' => $errors,
			), $this );
			do_action( 'ipt_fsqm_hook_save_error', $this );
			return $return;
		} else {
			$payment_status = array(
				'needed' => false,
				'success' => false,
				'redirect_url' => false,
				'requires_action' => null,
			);
			$woodata = array(
				'needed' => false,
				'cart' => null,
				'redirect' => null,
			);
			// Save it
			if ( $this->data_id == null ) {
				// Insert
				$wpdb->insert( $ipt_fsqm_info['data_table'], array(
					'form_id' => $this->form_id,
					'f_name' => $this->data->f_name,
					'l_name' => $this->data->l_name,
					'email' => $this->data->email,
					'phone' => $this->data->phone,
					'mcq' => maybe_serialize( $this->data->mcq ),
					'freetype' => maybe_serialize( $this->data->freetype ),
					'pinfo' => maybe_serialize( $this->data->pinfo ),
					'ip' => $this->data->ip,
					'star' => $this->data->star,
					'score' => $this->data->score,
					'max_score' => $this->data->max_score,
					'date' => $this->data->date,
					'comment' => $this->data->comment,
					'user_id' => $this->data->user_id,
					'url_track' => $this->data->url_track,
					'referer' => $this->data->referer,
					'time' => $this->data->time,
					'paid' => $this->data->paid,
					'token' => IPT_FSQM_Form_Elements_Static::generate_trackback_id(),
					'epoc' => date( 'Y-m-d H:i:s', (int) $this->post['ipt_fsqm_epoc'] ),
				), '%s' );
				$this->data_id = $wpdb->insert_id;
				$old_date = strtotime( $wpdb->get_var( $wpdb->prepare( "SELECT updated FROM {$ipt_fsqm_info['form_table']} WHERE id = %d", $this->form_id ) ) );
				$new_date = strtotime( $this->data->date );
				if ( $new_date > $old_date ) {
					$wpdb->update( $ipt_fsqm_info['form_table'], array(
						'updated' => $this->data->date,
					), array(
						'id' => $this->form_id,
					), '%s', '%d' );
				}
				// Update the file upload if any
				if ( ! empty( $freetype_upload_cache ) ) {
					$fileupload_query = $wpdb->prepare( "UPDATE {$ipt_fsqm_info['file_table']} SET data_id = %d WHERE id IN (" . implode( ',', $freetype_upload_cache ) . ")", $this->data_id );
					$wpdb->query( $fileupload_query );
					do_action( 'ipt_fsqm_hook_save_fileupload', $this );
				}
				do_action( 'ipt_fsqm_hook_save_insert', $this );
				// Do payment
				$payment_status = $this->do_payments();
				$woodata = $this->do_woocommerce();
				// Do integrations
				$this->do_integrations();
				// Do WP Core Integrations
				$this->do_core_integrations();
			} else {
				// Update
				$wpdb->update( $ipt_fsqm_info['data_table'], array(
						'form_id' => $this->form_id,
						'f_name' => $this->data->f_name,
						'l_name' => $this->data->l_name,
						'email' => $this->data->email,
						'phone' => $this->data->phone,
						'mcq' => maybe_serialize( $this->data->mcq ),
						'freetype' => maybe_serialize( $this->data->freetype ),
						'pinfo' => maybe_serialize( $this->data->pinfo ),
						'ip' => $this->data->ip,
						'star' => $this->data->star,
						'score' => $this->data->score,
						'max_score' => $this->data->max_score,
						'comment' => $this->data->comment,
						'user_id' => $this->data->user_id,
						'url_track' => $this->data->url_track,
						'time' => $this->data->time,
						'paid' => $this->data->paid,
					), array(
						'id' => $this->data_id,
					), '%s', '%d' );
				// Update the file upload if any
				if ( ! empty( $freetype_upload_cache ) ) {
					$fileupload_query = $wpdb->prepare( "UPDATE {$ipt_fsqm_info['file_table']} SET data_id = %d WHERE id IN (" . implode( ',', $freetype_upload_cache ) . ")", $this->data_id );
					$wpdb->query( $fileupload_query );
					do_action( 'ipt_fsqm_hook_save_fileupload', $this );
				}
				// Do update integrations
				$this->do_update_integration();
				// Do WP Core Integrations
				$this->do_core_integrations( true );
				do_action( 'ipt_fsqm_hook_save_update', $this );
			}

			//Call the API
			do_action( 'ipt_fsqm_hook_save_success', $this );

			// Format string
			$format_string_components = $this->get_format_string();

			// Redirect components
			$redirect_components = array(
				'redirect' => false,
				'redirect_delay' => 0,
				'redirect_url' => '',
				'redirect_top' => $this->settings['redirection']['top'],
				'redirect_msg' => '',
			);
			if ( $this->settings['redirection']['type'] != 'none' && $this->admin_update == false ) {
				$redirect_components['redirect'] = true;
				$redirect_components['redirect_delay'] = abs( (int) $this->settings['redirection']['delay'] );
				$redirect_components['redirect_url'] = $this->settings['redirection']['url'];
				// Score based redirection
				if ( $this->settings['redirection']['type'] == 'score' && $this->data->max_score != 0 ) {
					// Implement logic for percentage and raw score based redirection
					// #102
					// If redirection is based on percentage
					if ( $this->settings['redirection']['rtype'] == 'percentage' ) {
						$percentage = $this->data->score * 100 / $this->data->max_score;
						foreach ( $this->settings['redirection']['score'] as $score_range ) {
							if ( $percentage <= $score_range['max'] && $percentage >= $score_range['min'] ) {
								$redirect_components['redirect_url'] = $score_range['url'];
								break;
							}
						}
					// If redirection is based on raw score
					} else {
						foreach ( (array) $this->settings['redirection']['rscore'] as $raw_score_range ) {
							if ( $this->data->score <= $raw_score_range['max'] && $this->data->score >= $raw_score_range['min'] ) {
								$redirect_components['redirect_url'] = $raw_score_range['url'];
								break;
							}
						}
					}
				}
				// Condition based redirection
				if ( 'conditional' == $this->settings['redirection']['type'] && ! empty( $this->settings['redirection']['conditional'] ) ) {
					foreach ( $this->settings['redirection']['conditional'] as $redir_condition ) {
						if ( isset( $redir_condition['logics'] ) && ! empty( $redir_condition['url'] ) && $this->validate_logic_state( $redir_condition['logics'] ) ) {
							$redirect_components['redirect_url'] = $redir_condition['url'];
							break;
						}
					}
				}
			}

			// Tidy the feedback email
			$email_message = str_replace( '%ADMINLINK%', admin_url( 'admin.php?page=ipt_fsqm_view_submission&id=' . $this->data_id ), $this->settings['admin']['body'] );
			if ( $this->doing_update == true && $this->user_update == true ) {
				$email_message = str_replace( '%ADMINLINK%', admin_url( 'admin.php?page=ipt_fsqm_view_submission&id=' . $this->data_id ), $this->settings['admin']['ubody'] );
			}

			$user_info = __( '<h4>User Details</h4>', 'ipt_fsqm' );
			$user_info .= '<ul>';
			$user_info .= '<li>' . sprintf( __( '<strong>First Name</strong>: %s', 'ipt_fsqm' ), $this->data->f_name ) . '</li>';
			if ( $this->data->l_name != '' ) {
				$user_info .= '<li>' . sprintf( __( '<strong>Last Name</strong>: %s', 'ipt_fsqm' ), $this->data->l_name ) . '</li>';
			}
			if ( $this->data->email != '' ) {
				$user_info .= '<li>' . sprintf( __( '<strong>Email</strong>: <a href="mailto:%1$s">%1$s</a>', 'ipt_fsqm' ), $this->data->email ) . '</li>';
			}
			if ( $this->data->phone != '' ) {
				$user_info .= '<li>' . sprintf( __( '<strong>Phone</strong>: %s', 'ipt_fsqm' ), $this->data->phone ) . '</li>';
			}
			if ( $this->data->referer != '' ) {
				$user_info .= '<li>' . sprintf( __( '<strong>Referer</strong>: %s', 'ipt_fsqm' ), $this->data->referer ) . '</li>';
			}
			$user_info .= '</ul>';

			foreach ( $emails as $e_key => $email ) {
				if ( ! $this->settings['admin']['send_from_user'] ) {
					$emails[$e_key]['msgs'][0] = $email_message;
				}
				if ( $this->settings['admin']['f_user_info'] == true ) {
					$emails[$e_key]['msgs'][0] .= $user_info;
				}
				$emails[$e_key]['msgs'] = $this->get_admin_email_template( $emails[$e_key]['msgs'], false, $this->settings['admin']['f_summary_header'] );
			}

			// Add the admin notification
			$admin_email = array();
			if ( '' != trim( $this->settings['admin']['email'] ) || ! empty( $this->settings['admin']['conditional'] ) ) {
				$admin_emails = array();
				if ( '' != trim( $this->settings['admin']['email'] ) ) {
					$admin_emails = explode( ',', $this->settings['admin']['email'] );
				}
				// Also add in the conditional emails
				if ( ! empty( $this->settings['admin']['conditional'] ) ) {
					foreach ( $this->settings['admin']['conditional'] as $cond_email ) {
						if ( isset( $cond_email['logics'] ) && ! empty( $cond_email['email'] ) && $this->validate_logic_state( $cond_email['logics'] ) ) {
							$cond_email_addresses = explode( ',', $cond_email['email'] );
							$admin_emails = array_merge( $admin_emails, $cond_email_addresses );
						}
					}
				}
				// Make the emails unique
				$admin_emails = array_unique( $admin_emails );
				foreach ( $admin_emails as $email ) {
					$email = trim( $email );
					$msgs = array();
					if ( ! $this->settings['admin']['send_from_user'] ) {
						$msgs[] = $email_message;
					}
					if ( $this->settings['admin']['user_info'] == true ) {
						$msgs[] = $user_info;
					}
					$msg = $this->get_admin_email_template( $msgs, $this->settings['admin']['mail_submission'], $this->settings['admin']['summary_header'] );
					$admin_email[$email] = array(
						'title' => $this->admin_email_format( $this->settings['admin']['sub'] ),
						'msgs' => $msg,
					);
					if ( $this->doing_update == true && $this->user_update == true ) {
						$admin_email[$email]['title'] = $this->admin_email_format( $this->settings['admin']['usub'] );
					}
					if ( $this->settings['admin']['send_from_user'] == true && '' != $this->data->email ) {
						$admin_email[$email]['from'] = array( $this->data->f_name . ' ' . $this->data->l_name, $this->data->email, $this->settings['admin']['reply_to_only'] );
					} else if ( $this->settings['admin']['from'] != '' ) {
						$admin_email[$email]['from'] = array( $this->settings['admin']['from_name'], $this->settings['admin']['from'] );
					}
					if ( '' != trim( $ipt_fsqm_settings['email'] ) && $email == $admin_emails[0] ) {
						$admin_email[$email]['cc'] = $ipt_fsqm_settings['email'];
					}
					$admin_email[ $email ]['headers'] = $this->settings['admin']['header'];
				}
			}

			// Filter it for third party
			$admin_email = apply_filters( 'ipt_fsqm_admin_email', $admin_email, $this );

			// Mail it
			if ( $this->doing_update == false || $this->user_update == true ) {
				// New submission or user update email
				if ( $this->send_mail == true ) {
					$this->email( $emails );
					$this->email( $admin_email );
					// Send user notification email if checkout caused redirection
					// The email should be handled by what happens after the redirection
					// If WooCommerce is needed, then depending on the settings, send
					// the email directly, or delegate it only after order has been processed.
					if (
						( false == $payment_status['needed'] || false == $payment_status['redirect_url'] ) &&
						( false == $woodata['needed'] || ( true === $woodata['needed'] && false === $this->settings['payment']['woocommerce']['send_email_after_checkout'] ) )
					) {
						$this->send_user_notification_email();
					}
				}
			} elseif ( $this->doing_update == true && $this->admin_update == true && $this->settings['user']['notification_email'] != '' && $this->data->email != '' && isset( $this->post['ipt_fsqm_form_' . $this->form_id]['notify'] ) ) {
				// Admin update email
				$adm_ud_user_email = array();
				$adm_ud_user_email[ $this->data->email ] = array(
					'title' => trim( $this->post['ipt_fsqm_form_' . $this->form_id]['notify_sub'] ),
					'from' => array( $this->settings['user']['notification_from'], $this->settings['user']['notification_email'] ),
					'msgs' => $this->user_notification_email( trim( $this->post['ipt_fsqm_form_' . $this->form_id]['notify_msg'] ), $this->get_submission_lock_status(), $this->settings['payment']['lock_message'] ),
					'smtp' => $this->settings['user']['smtp'],
					'smtp_conf' => $this->settings['user']['smtp_config'],
					'headers' => $this->settings['user']['header'],
				);
				if ( $this->send_mail == true ) {
					$this->email( $adm_ud_user_email );
				}
			}

			// Create the success message
			$success_message = $this->settings['submission']['success_message'];

			// If it was admin update
			if ( true == $this->doing_update && true == $this->admin_update ) {
				$success_message = __( 'If you have a valid user notification email and if you have checked the "Email the surveyee/contributor about this update" button, then the user has been notified with a trackback link.', 'ipt_fsqm' );
			// If it was user update
			} else if ( true == $this->doing_update && true == $this->user_update ) {
				$success_message = $this->settings['submission']['update_message'];
			}

			// Finalize the redirect_url
			$redirect_components['redirect_url'] = str_replace( array_keys( $format_string_components ), array_map( 'urlencode', array_values( $format_string_components ) ), $redirect_components['redirect_url'] );

			// Also just replace the trackback
			$redirect_components['redirect_url'] = str_replace( '%TRACKBACK%', $this->get_trackback_url(), $redirect_components['redirect_url'] );

			// Add the redirection message to a new variable
			// So that JS can beautify it for us
			if ( $this->settings['redirection']['message'] != '' && $this->settings['redirection']['type'] != 'none' && $this->admin_update == false ) {
				$redirect_components['redirect_msg'] = str_replace( array( '%LINK%', '%TIME%' ), array( $redirect_components['redirect_url'], '<span class="ipt_fsqm_redirection_countdown">' . number_format_i18n( ( $redirect_components['redirect_delay'] / 1000 ), 2 )  . '</span>' ), $this->settings['redirection']['message'] );
			}

			// Add the social sharing buttons if needed
			$social_shares = '';
			if ( $this->settings['social']['show'] == true ) {
				$social_shares = '<p class="ipt_fsqm_social_share">' . $this->social_share_buttons( false ) . '</p>';
			}

			// Change some of the components if it was a payment
			if ( $payment_status['needed'] != false ) {
				if ( $payment_status['success'] == true ) {
					if ( $payment_status['redirect_url'] != false ) {
						$redirect_components['redirect'] = true;
						$redirect_components['redirect_url'] = $payment_status['redirect_url'];
						$redirect_components['redirect_delay'] = '2000';
						$success_message = __( 'You will be redirected to the payment gateway for completing your payment. Once done, you will be redirected back to our website for confirmation.', 'ipt_fsqm' );
						$redirect_components['redirect_msg'] = sprintf( __( 'Redirecting you to the payment gateway in %1$s. If you are not redirected automatically, then please <a href="%2$s">click here</a>.', 'ipt_fsqm' ), '<span class="ipt_fsqm_redirection_countdown">' . number_format_i18n( 2, 2 )  . '</span>', $payment_status['redirect_url'] );
					} else {
						$this->reset_email_styling();
						$success_message = $this->get_onetime_payment_success_message();
						$this->restore_email_styling();
					}
				} else {
					$success_message = $this->get_payment_error_message();
				}
			}

			// Check for WooCommerce
			if ( true == $woodata['needed'] ) {
				if ( ! is_null( $woodata['redirect'] ) ) {
					$redirect_components['redirect'] = true;
					$redirect_components['redirect_url'] = $woodata['redirect'];
					$redirect_components['redirect_delay'] = '500';
					$redirect_components['redirect_msg'] = sprintf( __( 'Redirecting you in %1$s. If you are not redirected automatically, then please <a href="%2$s">click here</a>.', 'ipt_fsqm' ), '<span class="ipt_fsqm_redirection_countdown">' . number_format_i18n( 2, 2 )  . '</span>', $woodata['redirect'] );
				}
			}

			$success_message = apply_filters( 'ipt_fsqm_form_success_message', $success_message, $payment_status, $woodata, $this );
			$redirect_components = apply_filters( 'ipt_fsqm_form_redirect_components', $redirect_components, $payment_status, $woodata, $this );

			// Return it
			return apply_filters( 'ipt_fsqm_filter_save_success', array(
				'success' => true,
				'components' => $redirect_components,
				'msg' => str_replace( array_keys( $format_string_components ), array_values( $format_string_components ), wpautop( wptexturize( $success_message ) ) ) . $social_shares,
				'payment_status' => $payment_status,
				'trackback_id' => $this->get_trackback_id(),
			), $this );
		}
	}

	public function get_onetime_payment_success_message() {
		return $this->settings['payment']['success_msg'] . "\n\n" . $this->get_transaction_status();
	}

	public function get_payment_error_message() {
		return $this->settings['payment']['error_msg'] . "\n\n\n\n" . sprintf( __( 'Please <a href="%1$s">click here</a> to retry payment.', 'ipt_fsqm' ), $this->get_repayment_url() );
	}

	public function retry_payment() {
		global $wpdb, $ipt_fsqm_info, $ipt_fsqm_settings;

		$redirect_components = array(
			'redirect' => false,
			'redirect_delay' => 0,
			'redirect_url' => '',
			'redirect_top' => $this->settings['redirection']['top'],
			'redirect_msg' => '',
		);
		$return = array(
			'success' => false,
			'components' => $redirect_components,
			'msg' => '',
		);

		// Get the element data
		$elem_keys = $this->get_keys_from_layouts_by_types( 'payment', $this->layout );
		if ( empty( $elem_keys ) ) {
			$return['msg'] = __( 'No payment data found in the form.', 'ipt_fsqm' );
			return $return;
		}

		if ( $this->settings['payment']['enabled'] == false ) {
			$return['msg'] = __( 'Payment is not enabled.', 'ipt_fsqm' );
			return $return;
		}

		$key = $elem_keys[0];

		$element_data = $this->get_element_from_layout( array(
			'type' => 'payment',
			'm_type' => 'pinfo',
			'key' => $key,
		) );

		if ( false === $this->validate_data_against_conditional_logic( $element_data, $key ) ) {
			$return['msg'] = __( 'Payment is not needed.', 'ipt_fsqm' );
			return $return;
		}

		// First update the database
		$element_raw_data = $payment_data = $this->post['ipt_fsqm_form_' . $this->form_id]['pinfo'][$key];
		$validation_result = $this->validate_data_against_element( $this->pinfo[$key], $element_raw_data, $key );

		/*
		'type' => string 'payment' (length=7)
		  'm_type' => string 'pinfo' (length=5)
		  'value' => string '404.50' (length=6)
		  'coupon' => string 'PER10' (length=5)
		  'couponval' => string '364.05' (length=6)
		  'pmethod' => string 'paypal_d' (length=8)
		*/

		$this->data->pinfo[ $key ]['pmethod'] = $validation_result['data']['pmethod'];

		$wpdb->update( $ipt_fsqm_info['data_table'], array(
			'pinfo' => maybe_serialize( $this->data->pinfo ),
		), array(
			'id' => $this->data_id,
		), '%s', '%d' );

		// Now process the payment
		$payment_status = $this->do_payments( true );

		// Set the default success message
		$this->reset_email_styling();
		$success_message = $this->settings['payment']['success_msg'] . "\n\n" . $this->get_transaction_status();
		$this->restore_email_styling();

		// Change some componenets depending on conditions
		if ( $payment_status['needed'] != false ) {
			// If payment was successful and requires redirection
			if ( $payment_status['success'] == true ) {
				if ( $payment_status['redirect_url'] != false ) {
					$redirect_components['redirect'] = true;
					$redirect_components['redirect_url'] = $payment_status['redirect_url'];
					$redirect_components['redirect_delay'] = '2000';
					$redirect_components['redirect_msg'] = sprintf( __( 'Redirecting you to the payment gateway in %1$s. If you are not redirected automatically, then please <a href="%2$s">click here</a>.', 'ipt_fsqm' ), '<span class="ipt_fsqm_redirection_countdown">' . number_format_i18n( 2, 2 )  . '</span>', $payment_status['redirect_url'] );
					$success_message = __( 'You will be redirected to the payment gateway for completing your payment. Once done, you will be redirected back to our website for confirmation.', 'ipt_fsqm' );
					// send the user notification only if no redirection
					// otherwise, like the original process_save it should be handled
					// by what happens after redirection
				} else {
					$this->send_user_notification_email();
				}
			} else {
				// If payment was not successful
				$success_message = $this->settings['payment']['error_msg'] . "\n\n\n\n" . sprintf( __( 'Please <a href="%1$s">click here</a> to retry payment.', 'ipt_fsqm' ), $this->get_repayment_url() );
			}
		} else {
			// If payment is not needed
			$success_message = __( 'Payment was not needed.', 'ipt_fsqm' );
		}

		$format_string_components = $this->get_format_string();

		// Add the admin notification
		// Only if it is not a 2 step payment
		if ( false == $payment_status['redirect_url'] ) {
			$this->send_payment_admin_email( $payment_status );
		}

		return apply_filters( 'ipt_fsqm_filter_payment_retry', array(
			'success' => true,
			'components' => $redirect_components,
			'msg' => str_replace( array_keys( $format_string_components ), array_values( $format_string_components ), wpautop( wptexturize( $success_message ) ) ),
			'payment_status' => $payment_status,
			'trackback_id' => $this->get_trackback_id(),
		), $payment_status, $this );
	}

	public function user_payment_email( $custom_msg = '', $payment_info = array() ) {
		$format_string_components = $this->get_format_string();
		// prepare the data
		$data = [
			'style' => $this->settings['email_template'],
		];

		$email_template = $this->get_email_template_path( 'user-payment' );
		ob_start();
		include $email_template;
		return ob_get_clean();
	}

	public function get_payment_data() {
		global $ipt_fsqm_info, $wpdb;
		$payment_db = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_info['payment_table']} WHERE data_id = %d", $this->data_id ) );
		return $payment_db;
	}

	public function get_transaction_status( $for_inner = false, $echo = false ) {
		global $ipt_fsqm_info, $wpdb;
		$elem_keys = $this->get_keys_from_layouts_by_types( 'payment', $this->layout );

		if ( empty( $elem_keys ) ) {
			return '';
		}

		$elem_key = $elem_keys[0];

		// Do not do anything if it was conditionally hidden
		$element_data = $this->get_element_from_layout( array(
			'type' => 'payment',
			'm_type' => 'pinfo',
			'key' => $elem_key,
		) );
		if ( false === $this->validate_data_against_conditional_logic( $element_data, $elem_key ) ) {
			return '';
		}

		$payment_status['needed'] = true;
		$payment_data = $this->data->pinfo[$elem_key];
		$payment_amount = $payment_data['value'];
		$discount_amount = 0;
		if ( isset( $payment_data['couponval'] ) && $payment_data['couponval'] != '' ) {
			$payment_amount = $payment_data['couponval'];
			$discount_amount = $payment_data['value'] - $payment_amount;
		}

		$payment_db = $this->get_payment_data();

		if ( is_null( $payment_db ) ) {
			return '<p style="padding: .5em .75em">' . __( '0.00', 'ipt_fsqm' ) . '</p>';
		}

		$invoiceid = str_replace( '{id}', $payment_db->id, $this->settings['payment']['invoicenumber'] );
		if ( $invoiceid == '' ) {
			$invoiceid = $payment_db->id;
		}

		$payment_status = IPT_FSQM_Form_Elements_Static::ipt_fsqm_get_payment_status();

		$payment_modes = IPT_FSQM_Form_Elements_Static::ipt_fsqm_get_payment_gateways();

		$tbody_tr_first = ' border-top: 0 none; border-left: 0 none; border-right: 0 none;';
		$tbody_tr_first_td_middle = ' border-top: 0 none;';
		$tbody_tr_last = ' border-bottom: 0 none;';
		if ( ! $for_inner ) {
			$tbody_tr_first_td_middle = '';
			$tbody_tr_first = '';
			$tbody_tr_last = '';
		}

		$itemname = $this->settings['payment']['itemname'];
		if ( empty( $itemname ) ) {
			$itemname = $this->name; // Replace by formname, if empty
		}
		ob_start();
		?>
<table class="ipt_fsqm_preview bordered devicewidththinner" style="<?php echo ( $for_inner ? $this->email_styling['inner_table'] : $this->email_styling['table'] ); ?>" width="100%">
	<tbody style="<?php echo $this->email_styling['tbody'] . $tbody_tr_first; ?>">
		<tr style="<?php echo $this->email_styling['tr'] . $tbody_tr_first; ?>">
			<th style="<?php echo $this->email_styling['th'] . $tbody_tr_first; ?>"><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<td style="<?php echo $this->email_styling['td'] . $tbody_tr_first_td_middle; ?>"><?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $payment_db->date ) ); ?></td>
			<th style="<?php echo $this->email_styling['th'] . $tbody_tr_first_td_middle; ?>"><?php _e( 'Invoice ID', 'ipt_fsqm' ); ?></th>
			<td style="<?php echo $this->email_styling['td'] . $tbody_tr_first_td_middle; ?>"><a href="<?php echo $this->get_trackback_url(); ?>"><?php echo $invoiceid; ?></a></td>
			<th style="<?php echo $this->email_styling['th'] . $tbody_tr_first_td_middle; ?>"><?php _e( 'Status', 'ipt_fsqm' ); ?></th>
			<td style="<?php echo $this->email_styling['td'] . $tbody_tr_first; ?>"><?php echo $payment_status[$payment_db->status]; ?></td>
		</tr>
		<?php if ( $payment_db->status != 1 ) : ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<td colspan="6" style="<?php echo $this->email_styling['td']; ?> background-color: #fcf8e3; color: <?php echo $this->settings['email_template']['h_color']; ?>">
				<?php if ( 0 == $payment_db->status ) : ?>
				<?php printf( __( 'Payment was not completed. If you want to retry, please <a href="%1$s">click here</a>.', 'ipt_fsqm' ), $this->get_repayment_url() ); ?>
				<?php elseif ( 4 == $payment_db->status && 'offline' == $payment_db->mode ) : ?>
					<?php echo wpautop( sprintf( $this->settings['payment']['offline']['status'], $invoiceid, $payment_db->txn ) ); ?>
					<?php echo wpautop( $this->settings['payment']['offline']['instruction'] ); ?>
				<?php else : ?>
				<?php printf( __( 'Payment was not successful. Please <a href="%1$s">click here</a> to retry.', 'ipt_fsqm' ), $this->get_repayment_url() ); ?>
				<?php endif; ?>
			</td>
		</tr>
		<?php endif; ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<th style="<?php echo $this->email_styling['th']; ?>">
				<?php
					if ( $this->is_subscription_payment_form() ) {
						_e( 'Subscription ID', 'ipt_fsqm' );
					} else {
						_e( 'Transaction ID', 'ipt_fsqm' );
					}
				?>
			</th>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="2"><?php echo $payment_db->txn; ?></td>
			<th style="<?php echo $this->email_styling['th']; ?>"><?php _e( 'Payment gateway', 'ipt_fsqm' ); ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="2"><?php echo $payment_modes[$payment_db->mode]; ?></td>
		</tr>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<th style="<?php echo $this->email_styling['th']; ?>" colspan="2"><?php _e( 'Item name', 'ipt_fsqm' ); ?></th>
			<th style="<?php echo $this->email_styling['th']; ?>" colspan="3"><?php _e( 'Description', 'ipt_fsqm' ); ?></th>
			<th style="<?php echo $this->email_styling['th']; ?>"><?php _e( 'Price', 'ipt_fsqm' ); ?><?php echo ' (' . $payment_db->currency . ')'; ?></th>
		</tr>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="2">
				<?php echo $itemname; ?>
				<?php if ( $this->settings['payment']['itemsku'] != '' ) : ?>
				<br /><span class="description" style="<?php echo $this->email_styling['description']; ?>"><?php printf( __( 'Item SKU: %1$s', 'ipt_fsqm' ), $this->settings['payment']['itemsku'] ); ?></span>
				<?php endif; ?>
			</td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="3">
				<?php echo wpautop( $this->settings['payment']['itemdescription'] ); ?>
			</td>
			<td style="<?php echo $this->email_styling['td'] . ' text-align: right;'; ?>"><?php echo $this->settings['payment']['c_prefix'] . number_format_i18n( $payment_data['value'], 2 ) . $this->settings['payment']['c_suffix']; ?></td>
		</tr>
		<?php if ( $payment_data['coupon'] != '' && $discount_amount > 0 ) : ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<th style="<?php echo $this->email_styling['th']; ?>"><?php _e( 'Coupon', 'ipt_fsqm' ) ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="1">
				<?php
				if ( $payment_data['coupon'] != '' ) {
					echo $payment_data['coupon'];
				} else {
					echo __( 'N/A', 'ipt_fsqm' );
				}
				?>
			</td>
			<th style="<?php echo $this->email_styling['th']; ?>" colspan="3"><?php _e( 'Coupon Discount', 'ipt_fsqm' ) ?></th>
			<td style="<?php echo $this->email_styling['td'] . ' text-align: right;'; ?>">
				<?php echo $this->settings['payment']['c_prefix'] . number_format_i18n( $discount_amount, 2 ) . $this->settings['payment']['c_suffix']; ?>
			</td>
		</tr>
		<?php endif; ?>
		<?php if ( 1 == $payment_db->type ) : ?>
			<?php $this->get_subscription_status( $payment_db ); ?>
		<?php endif; ?>
		<tr style="<?php echo $this->email_styling['tr'] . $tbody_tr_last; ?>">
			<th style="<?php echo $this->email_styling['th'] . $tbody_tr_last; ?>" colspan="5"><?php _e( 'Total', 'ipt_fsqm' ); ?></th>
			<td style="<?php echo $this->email_styling['td'] . ' text-align: right;' . $tbody_tr_last; ?>">
				<?php echo $this->settings['payment']['c_prefix'] . number_format_i18n( $payment_amount, 2 ) . $this->settings['payment']['c_suffix']; ?>
			</td>
		</tr>
	</tbody>
</table>
		<?php
		$return = ob_get_clean();
		if ( $echo ) {
			echo $return;
		}
		return $return;
	}

	public function get_subscription_status( $payment_db ) {
		$rinterval = [
			0 => _n_noop( 'None', 'None' ),
			1 => _n_noop( 'Day', 'Days' ),
			2 => _n_noop( 'Week', 'Weeks' ),
			3 => _n_noop( 'Month', 'Months' ),
			4 => _n_noop( 'Year', 'Years' ),
		];
		$selected_interval = translate_nooped_plural( $rinterval[ $payment_db->rinterval ], $payment_db->rfrequency, 'ipt_fsqm' );
		$plan_frequency = sprintf( $this->settings['payment']['recur_statement'], $payment_db->rfrequency, $selected_interval );
		?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<th style="<?php echo $this->email_styling['th']; ?>"><?php _e( 'Subscription', 'ipt_fsqm' ); ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="1">
				<?php echo $payment_db->rplan; ?>
			</td>
			<th style="<?php echo $this->email_styling['th']; ?>"><?php _e( 'Frequency', 'ipt_fsqm' ); ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="3">
				<?php echo $plan_frequency; ?>
			</td>
		</tr>
		<?php
	}

	public function get_woo_product_id() {
		// Conditional product id
		$product_id = $this->settings['payment']['woocommerce']['product_id'];
		if ( ! empty( $this->settings['payment']['woocommerce']['cond_pid'] ) ) {
			foreach ( $this->settings['payment']['woocommerce']['cond_pid'] as $woo_config ) {
				if ( $this->validate_logic_state( $woo_config['logics'] ) ) {
					$product_id = $woo_config['pid'];
					break;
				}
			}
		}
		// If no product is conditionally selected
		// then return false
		if ( '' === $product_id ) {
			return false;
		}
		// If product Id is not valid WooCommerce product, then return
		$post_type = get_post_type( $product_id );
		if ( ! in_array( $post_type, [ 'product', 'product_variation' ] ) ) {
			return false;
		}

		// Return the product Id
		return $product_id;
	}

	public function get_woo_product_mm_ids() {
		$product_ids = wp_parse_id_list(
			$this->settings['payment']['woocommerce']['mm_product_ids']
		);
		// now add the conditional ids too
		if ( ! empty( $this->settings['payment']['woocommerce']['cond_pid'] ) ) {
			foreach ( $this->settings['payment']['woocommerce']['cond_pid'] as $woo_config ) {
				if ( $this->validate_logic_state( $woo_config['logics'] ) ) {
					$product_ids[] = absint( $woo_config['pid'] );
				}
			}
		}
		$product_ids = array_unique( $product_ids, SORT_NUMERIC );
		// now check for post types
		$product_ids = array_filter( $product_ids, function( $id ) {
			$post_type = get_post_type( $id );
			if ( ! in_array( $post_type, [ 'product', 'product_variation' ] ) ) {
				return false;
			}
			return true;
		} );
		return $product_ids;
	}

	public function do_woocommerce() {
		/**
		 * @var IPT_eForm_WooCommerce
		 */
		global $ipt_eform_wc;

		// Return if not needed
		if (
			false == $this->settings['payment']['woocommerce']['enabled']
			|| is_null( $ipt_eform_wc )
		) {
			return [
				'needed' => false,
				'cart' => null,
				'redirect' => null,
			];
		}

		$mode = $this->settings['payment']['woocommerce']['mode'];
		if ( 'multiple' === $mode ) {
			return $this->do_woocommerce_multiple();
		} else {
			return $this->do_woocommerce_single();
		}
	}

	public function do_woocommerce_multiple() {
		/**
		* @var IPT_eForm_WooCommerce $ipt_eform_wc
		*/
		global $ipt_eform_wc;
		$return = array(
			'needed' => false,
			'cart' => null,
			'redirect' => null,
		);
		$product_ids = $this->get_woo_product_mm_ids();
		if ( empty( $product_ids ) ) {
			return $return;
		}

		// here, so we are going to add some products to cart
		$return['needed'] = true;
		foreach ( $product_ids as $product_id ) {
			$ipt_eform_wc->add_to_cart( $this->data_id, $product_id, [], 1 );
		}

		if ( 'cart' == $this->settings['payment']['woocommerce']['redirect'] ) {
			$return['redirect'] = get_permalink( get_option( 'woocommerce_cart_page_id' ) );
		} elseif ( 'checkout' == $this->settings['payment']['woocommerce']['redirect'] ) {
			$return['redirect'] = get_permalink( get_option( 'woocommerce_checkout_page_id' ) );
		}

		return $return;

	}



	public function do_woocommerce_single() {
		// Check for WooCommerce
		/**
		 * @var IPT_eForm_WooCommerce $ipt_eform_wc
		 */
		global $ipt_eform_wc;
		$return = array(
			'needed' => false,
			'cart' => null,
			'redirect' => null,
		);

		// Conditional product id
		$product_id = $this->get_woo_product_id();

		// If no product is conditionally selected
		// Then return.
		if ( false === $product_id ) {
			return $return;
		}

		// For many things we need the value class
		$ipt_eform_val = new IPT_eForm_Form_Elements_Values( $this->data_id );

		// Optional Quantity
		$quantity = 1;
		if ( '' != $this->settings['payment']['woocommerce']['quantity_item'] ) {
			// Could be a slider or feedback small, just pass and get value from the value class
			// Get the parts data first
			$q_elem_parts = array();
			if ( preg_match( '/(M|F|O)([0-9]+)/', $this->settings['payment']['woocommerce']['quantity_item'], $q_elem_parts ) ) {
				$m_type = 'mcq';
				if ( 'F' == $q_elem_parts[1] ) {
					$m_type = 'freetype';
				} elseif ( 'O' == $q_elem_parts[1] ) {
					$m_type = 'pinfo';
				}
				$ekey = (int) $q_elem_parts[2];
				$quantity = (int) $ipt_eform_val->get_value( $m_type, $ekey, 'string' );
			}
		}
		// Sanity Check
		if ( $quantity < 1 ) {
			$quantity = 1;
		}

		// Get and set the attributes from math formula
		$math_element = $this->get_element_from_layout( array(
			'm_type' => 'freetype',
			// Automatically check for common errors, like convert F10 to just 10
			'key' => intval( preg_replace('/[^0-9]/', '', "{$this->settings['payment']['woocommerce']['mathematical']}") ),
		) );
		$needs_price_adjustment = true;
		$attribute_string = '';
		if ( empty( $math_element ) ) {
			$needs_price_adjustment = false;
		} else {
			$attribute_string = isset( $math_element['settings']['formula'] ) ? $math_element['settings']['formula'] : '';
		}

		// Return if mathematical element is hidden
		if ( $needs_price_adjustment && false == $this->validate_data_against_conditional_logic( $math_element, $this->settings['payment']['woocommerce']['mathematical'] ) ) {
			return $return;
		}

		// Append the additional attributes
		if ( '' != $this->settings['payment']['woocommerce']['additional_attr'] ) {
			$attribute_string .= ' ' . $this->settings['payment']['woocommerce']['additional_attr'];
		}

		// All checks done
		// Now we execute the WooCommerce
		$return['needed'] = true;
		// We will pass on the item data
		$item_data = array();
		// Set the eform presence
		$item_data['eform_cart'] = true;
		// Set eForm data ID
		$item_data['eform_data_id'] = $this->data_id;
		// Formulate the price
		if ( $needs_price_adjustment ) {
			$item_data['eform_price'] = $ipt_eform_val->get_value( 'freetype', $this->settings['payment']['woocommerce']['mathematical'], 'string' );
		} else {
			$item_data['eform_price'] = false;
		}

		// Extract the variables
		$variable_parts = array();
		$item_data['eform_attr'] = array();

		$added_elements = array(
			'mcq' => array(),
			'freetype' => array(),
			'pinfo' => array(),
		);
		if ( preg_match_all( '/([M|F|O])([0-9]+)/', $attribute_string, $variable_parts ) ) {
			foreach ( $variable_parts[0] as $key => $part ) {
				$m_type = 'mcq';
				if ( 'F' == $variable_parts[1][ $key ] ) {
					$m_type = 'freetype';
				} elseif ( 'O' == $variable_parts[1][ $key ] ) {
					$m_type = 'pinfo';
				}
				$ekey = (int) $variable_parts[2][ $key ];

				// Check for duplicates
				if ( in_array( $ekey, $added_elements[ $m_type ] ) ) {
					continue;
				}

				// Get element settings
				$element = $this->get_element_from_layout( array(
					'm_type' => $m_type,
					'key' => $ekey,
				) );

				// Check for conditional
				if ( false == $this->validate_data_against_conditional_logic( $element, $ekey ) ) {
					continue;
				}

				$added_elements[ $m_type ][] = $ekey;

				// Add to the item data
				if ( ! empty( $element ) ) {
					$item_data['eform_attr'][] = array(
						'name' => $element['title'],
						'value' => $ipt_eform_val->get_value( $m_type, $ekey, 'string', 'numeric' ),
						'display' => $ipt_eform_val->get_value( $m_type, $ekey, 'string', 'label' ),
					);
				}
			}
		}

		$return['cart'] = $ipt_eform_wc->add_to_cart( $this->data_id, $product_id, $item_data, $quantity );

		// Add proper redirection URL
		if ( 'cart' == $this->settings['payment']['woocommerce']['redirect'] ) {
			$return['redirect'] = get_permalink( get_option( 'woocommerce_cart_page_id' ) );
		} elseif ( 'checkout' == $this->settings['payment']['woocommerce']['redirect'] ) {
			$return['redirect'] = get_permalink( get_option( 'woocommerce_checkout_page_id' ) );
		}

		return $return;
	}

	public function do_payments( $retry = false ) {
		// Update the DB
		global $ipt_fsqm_info, $wpdb;
		$elem_keys = $this->get_keys_from_layouts_by_types( 'payment', $this->layout );

		$payment_status = array(
			'needed' => false,
			'success' => false,
			'redirect_url' => false,
		);
		if ( empty( $elem_keys ) || $this->settings['payment']['enabled'] == false ) {
			return $payment_status;
		}

		$elem_key = $elem_keys[0];

		// Do not do anything if it was conditionally hidden
		$element_data = $this->get_element_from_layout( array(
			'type' => 'payment',
			'm_type' => 'pinfo',
			'key' => $elem_key,
		) );
		if ( false === $this->validate_data_against_conditional_logic( $element_data, $elem_key ) ) {
			return $payment_status;
		}

		$payment_data = $this->post['ipt_fsqm_form_' . $this->form_id]['pinfo'][$elem_key];
		$payment_amount = $payment_data['value'];
		if ( isset( $payment_data['couponval'] ) && $payment_data['couponval'] != '' ) {
			$payment_amount = $payment_data['couponval'];
		}

		// If payment amount is 0, then there's no need to process it
		if ( $payment_amount == 0 ) {
			return $payment_status;
		}

		// Now we know that payment is needed
		$payment_status['needed'] = true;

		// Create the payment method and go
		$payment_method = $payment_data['pmethod'];

		$prev_record = null;

		// Add record to the database
		// If it is a new entry
		if ( ! $retry ) {
			$db_record = array(
				'txn' => null,
				'form_id' => $this->form_id,
				'data_id' => $this->data_id,
				'user_id' => $this->data->user_id,
				'amount' => $payment_amount,
				'mode' => $payment_method,
				'status' => 0, // 0 -> Processing, 1 -> Paid, 2 -> Payment cancelled, 3 -> Payment failed/error
				'meta' => maybe_serialize( array() ),
				'currency' => $this->settings['payment']['currency'],
				'date' => current_time( 'mysql' ),
				'type' => 'onetime' == $this->settings['payment']['payment_type'] ? 0 : 1,
			);

			$wpdb->insert( $ipt_fsqm_info['payment_table'], $db_record, array( '%s', '%d', '%d', '%d', '%f', '%s', '%d', '%s', '%s', '%s', '%d' ) );
			$invoiceid = $wpdb->insert_id;
		} else {
			// If it is a payment retry
			// Then just grab the ID
			$prev_record = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM {$ipt_fsqm_info['payment_table']} WHERE data_id = %d", $this->data_id )
			);

			$invoiceid = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$ipt_fsqm_info['payment_table']} WHERE data_id = %d", $this->data_id ) );
			if ( null == $invoiceid ) {
				return $payment_status;
			}
			// Also update with the new data
			$db_record = array(
				'txn' => null,
				'amount' => $payment_amount,
				'mode' => $payment_method,
				// 'status' => 0, // 0 -> Processing, 1 -> Paid, 2 -> Payment cancelled, 3 -> Payment failed/error
				'meta' => maybe_serialize( array() ),
				'currency' => $this->settings['payment']['currency'],
				'date' => current_time( 'mysql' ),
			);
			$wpdb->update( $ipt_fsqm_info['payment_table'], $db_record, array(
				'id' => $invoiceid,
			), array( '%s', '%f', '%s', '%s', '%s', '%s' ), '%d' );
		}

		$payment_status['invoice'] = str_replace( '{id}', $invoiceid, $this->settings['payment']['invoicenumber'] );;

		// Check if onetime payment or recurring
		if ( 'onetime' == $this->settings['payment']['payment_type'] ) {
			$payment_status = $this->process_onetime_payment( $payment_method, $payment_status, $payment_amount, $invoiceid, $elem_key, $retry, $prev_record );
		} else {
			$payment_status = $this->process_subscription_payment( $payment_method, $payment_status, $payment_amount, $invoiceid, $elem_key, $retry, $prev_record );
		}

		return $payment_status;
	}

	public function process_subscription_payment( $payment_method, $payment_status, $payment_amount, $invoiceid, $elem_key, $retry, $prev_record = null ) {
		$payment_return = array();
		switch ( $payment_method ) {
			case 'stripe' :
				// Get the source
				$source = isset( $this->post[ 'ipt_fsqm_form_' . $this->form_id ]['pinfo'][ $elem_key ]['stripe_source'] )
					? $this->post[ 'ipt_fsqm_form_' . $this->form_id ]['pinfo'][ $elem_key ]['stripe_source']
					: 'new';
				// If it is a new card
				$stripe_client_data = '';
				if ( 'new' == $source ) {
					$stripe_client_data = wp_unslash( @$_POST['stripe'] );
				}
				$payment_return = $this->stripe_recurring_payment( $payment_amount, $invoiceid, $source, $stripe_client_data, $retry, $prev_record );
				$payment_status = array_merge( $payment_status, $payment_return );
				$this->direct_payment_post_hooks( $payment_status, $retry, 'stripe' );
				break;
			default :
				$payment_return = apply_filters( 'ipt_fsqm_payment_subscription_execute', $payment_return, $payment_amount, $invoiceid, $this->post['ipt_fsqm_form_' . $this->form_id]['pinfo'][$elem_key], $retry, $this, $payment_method );
				$payment_status = array_merge( $payment_status, $payment_return );
		}
		return $payment_status;
	}

	public function process_onetime_payment( $payment_method, $payment_status, $payment_amount, $invoiceid, $elem_key, $retry ) {
		$payment_return = array();
		// Check which payment method was used
		// and act accordingly
		switch ( $payment_method ) {
			case 'paypal_d' :
				$payment_return = $this->paypal_direct_payment( $payment_amount, $invoiceid, $this->post['ipt_fsqm_form_' . $this->form_id]['pinfo'][$elem_key]['cc'] );
				$payment_status = array_merge( $payment_status, $payment_return );
				// Set the payment status to 1 if payment was successful
				$this->direct_payment_post_hooks( $payment_status, $retry, 'paypal_d' );
				break;
			case 'paypal_e' :
				$payment_return = $this->paypal_express_checkout( $payment_amount, $invoiceid );
				$payment_status = array_merge( $payment_status, $payment_return );
				// No need to send email here, because it will be handled later on
				break;
			case 'stripe' :
				$payment_return = $this->stripe_direct_payment( $payment_amount, $invoiceid, wp_unslash( $_POST['stripe'] ) );
				$payment_status = array_merge( $payment_status, $payment_return );
				// Set the payment status to 1 if payment was successful
				$this->direct_payment_post_hooks( $payment_status, $retry, 'stripe' );
				break;
			case 'authorizenet' :
				$payment_return = $this->authorizenet_direct_payment( $payment_amount, $invoiceid, $this->post['ipt_fsqm_form_' . $this->form_id]['pinfo'][$elem_key]['cc'] );
				$payment_status = array_merge( $payment_status, $payment_return );
				// Set the payment status to 1 if payment was successful
				$this->direct_payment_post_hooks( $payment_status, $retry, 'authorizenet' );
				break;
			case 'offline' :
				$payment_return = $this->offline_checkout( $payment_amount, $invoiceid, $retry );
				$payment_status = array_merge( $payment_status, $payment_return );
				$this->send_payment_email( $payment_status, $retry, false, 'offline', true );
				break;
			default :
				$payment_return = apply_filters( 'ipt_fsqm_payment_execute', $payment_return, $payment_amount, $invoiceid, $this->post['ipt_fsqm_form_' . $this->form_id]['pinfo'][$elem_key], $retry, $this, $payment_method );
				$payment_status = array_merge( $payment_status, $payment_return );
				// We do not explicitly send an email here
				break;
		}

		return $payment_status;
	}

	public function direct_payment_post_hooks( $payment_status, $retry, $gateway ) {
		// Set payment status to 1 because it is direct
		if ( true == $payment_status['success'] ) {
			$this->set_paid_status( 1 );
		}
		// Send notification to user
		$this->send_payment_email( $payment_status, $retry, false, $gateway );
	}

	/**
	 * Sets the paid status.
	 *
	 * It does not actually check the payment status
	 * It just updates the db and variable
	 *
	 * @param      integer  $status  The status. 0 if unpaid, 1 if paid
	 */
	public function set_paid_status( $status = 0 ) {
		global $wpdb, $ipt_fsqm_info;
		// Update the db record if payment was successful
		$wpdb->update( $ipt_fsqm_info['data_table'], array(
			'paid' => $status,
		), array(
			'id' => $this->data_id,
		), '%d', '%d' );
		$this->data->paid = $status;
	}

	/**
	 * Sends a payment email.
	 *
	 * @param      array    $payment_status  Associative array containing the
	 *                                       status of payment
	 * @param      boolean  $retry           Whether in retry mode
	 * @param      boolean  $cancelled       Whether or not payment was
	 *                                       cancelled
	 * @param      string   $mode            The mode
	 * @param      boolean  $oflinit         Whether initiating offline payment
	 *
	 * @return     boolean  True if email was sent, false otherwise ( probably because payment was not needed or email was empty )
	 */
	public function send_payment_email( $payment_status, $retry = false, $cancelled = false, $mode = '', $oflinit = false ) {
		// No need to send if payment was not needed
		if ( false == $payment_status['needed'] ) {
			return false;
		}

		// No need if email is empty
		if ( '' == $this->data->email || '' == $this->settings['user']['notification_email'] ) {
			return false;
		}

		// Style for email
		$this->format_email_style();
		// Get format string
		$format_string_components = $this->get_format_string();

		// Now prepare variables
		// Set the title and message
		if ( $retry ) {
			$title = $this->settings['payment']['retry_uemail_sub'];
			$msg = $this->settings['payment']['retry_uemail_msg'];
		} else {
			$title = $this->settings['payment']['success_sub'];
			$msg = $this->settings['payment']['success_msg'];
		}
		// In case of paypal express mode
		if ( 'paypal_e' == $mode ) {
			$title = $this->settings['payment']['paypal']['conf_sub'];
			$msg = $this->settings['payment']['paypal']['conf_msg'];
		}
		// In case of offline mode
		if ( 'offline' == $mode && $oflinit ) {
			$title = $this->settings['payment']['offline']['email_sub'];
			$msg = $this->settings['payment']['offline']['email_msg'];
		}
		// But in case of error, just override
		if ( false == $payment_status['success'] ) {
			$title = $this->settings['payment']['error_sub'];
			$msg = $this->settings['payment']['error_msg'];
			// If in case of cancellation
			if ( true == $cancelled ) {
				$title = $this->settings['payment']['cancel_sub'];
				$msg = $this->settings['payment']['cancel_msg'];
			}
		}

		// Now format everything
		$title = str_replace( array_keys( $format_string_components ), array_values( $format_string_components ), $title );
		$title = sprintf( $title, $payment_status['invoice'] );
		$msg = str_replace( array_keys( $format_string_components ), array_values( $format_string_components ), $msg );

		// Prepare email array
		$email = array();
		$payment_info = array(
			'retry' => $retry,
			'mode' => $mode,
			'success' => $payment_status['success'],
			'cancelled' => $cancelled,
			'invoice' => $payment_status['invoice'],
		);
		$email[ $this->data->email ] = array(
			'title' => $title,
			'from' => array( $this->settings['user']['notification_from'], $this->settings['user']['notification_email'] ),
			'msgs' => $this->user_payment_email( $msg, $payment_info ),
			'smtp' => $this->settings['user']['smtp'],
			'smtp_conf' => $this->settings['user']['smtp_config'],
		);
		$email = apply_filters( 'ipt_fsqm_user_payment_email', $email, $payment_status, $retry, $cancelled, $this );
		if ( '' != $mode ) {
			$email = apply_filters( "ipt_fsqm_user_payment_email_{$mode}", $email, $payment_status, $retry, $cancelled, $this );
		}
		// Mail it
		$this->email( $email );
		// Reset email styling
		$this->reset_email_styling();
	}

	/**
	 * Sends payment retry email to admin
	 *
	 * It always assumes that payment status is being updated. Please change the
	 * retry flag if this is not the case.
	 *
	 * @param      array    $payment_status  Assotiative array of current
	 *                                       payment status.
	 * @param      string   $custom_msg      Custom message to append to the
	 *                                       email.
	 * @param      boolean  $retry           Retry flag. Email subject will be
	 *                                       populated accordingly
	 */
	public function send_payment_admin_email( $payment_status, $custom_msg = '', $retry = true ) {
		global $ipt_fsqm_info, $ipt_fsqm_settings;
		$format_string_components = $this->get_format_string();
		$this->format_email_style();
		$user_info = __( '<h4>User Details</h4>', 'ipt_fsqm' );
		$user_info .= '<ul>';
		$user_info .= '<li>' . sprintf( __( '<strong>First Name</strong>: %s', 'ipt_fsqm' ), $this->data->f_name ) . '</li>';
		if ( $this->data->l_name != '' ) {
			$user_info .= '<li>' . sprintf( __( '<strong>Last Name</strong>: %s', 'ipt_fsqm' ), $this->data->l_name ) . '</li>';
		}
		if ( $this->data->email != '' ) {
			$user_info .= '<li>' . sprintf( __( '<strong>Email</strong>: <a href="mailto:%1$s">%1$s</a>', 'ipt_fsqm' ), $this->data->email ) . '</li>';
		}
		if ( $this->data->phone != '' ) {
			$user_info .= '<li>' . sprintf( __( '<strong>Phone</strong>: %s', 'ipt_fsqm' ), $this->data->phone ) . '</li>';
		}
		$user_info .= '</ul>';
		$admin_email = array();
		if ( '' != trim( $this->settings['admin']['email'] ) ) {
			$admin_emails = explode( ',', $this->settings['admin']['email'] );
			$admin_email_title = ( $retry ? $this->settings['payment']['retry_aemail_sub'] : $this->settings['payment']['redir_aemail_sub'] );
			$admin_email_title = sprintf( str_replace( array_keys( $format_string_components ), array_values( $format_string_components ), $admin_email_title ), $payment_status['invoice'] );
			$msgs = array();
			if ( $retry ) {
				$msgs[] = sprintf( __( 'User has updated payment status for %1$s', 'ipt_fsqm' ), $payment_status['invoice'] );
			} else {
				$msgs[] = sprintf( __( 'A new payment has been made for %1$s', 'ipt_fsqm' ), $payment_status['invoice'] );
			}

			if ( $this->settings['admin']['user_info'] == true ) {
				$msgs[] = $user_info;
			}
			$msgs[] = $this->get_transaction_status( false, false );
			$msg = $this->get_admin_email_template( $msgs, false, false );

			foreach ( $admin_emails as $email ) {
				$email = trim( $email );
				$admin_email[ $email ] = array(
					'title' => $admin_email_title,
					'msgs' => $msg,
				);

				if ( '' != trim( $ipt_fsqm_settings['email'] ) && $email == $admin_emails[0] ) {
					$admin_email[ $email ]['cc'] = $ipt_fsqm_settings['email'];
				}
			}
		}

		$admin_email = apply_filters( 'ipt_fsqm_admin_payment_email', $admin_email, $payment_status, $custom_msg, $this );
		$this->email( $admin_email );
		$this->reset_email_styling();
	}

	/**
	 * Stripe recurring payment method. Creates plan, create customer and
	 * subscribe customer to the plan.
	 *
	 * @param      float    $payment_amount  The payment amount
	 * @param      int      $id              eForm invoide ID
	 * @param      string   $source          Stripe Source
	 * @param      string   $stripe_client_data Stripe token from Stripe.js
	 * @param      boolean  $retry           Whether a retry form
	 *
	 * @return     array    returns a payment status object
	 */
	public function stripe_recurring_payment( $payment_amount, $id, $source, $stripe_client_data, $retry, $prev_record = null ) {
		global $wpdb, $ipt_fsqm_info;
		// Prepare the return
		$payment_status = [
			'success' => false,
			'redirect_url' => false,
			'requires_action' => null,
		];

		// Prepare interval map
		$interval_map = [
			'day' => 1,
			'week' => 2,
			'month' => 3,
			'year' => 4,
		];

		// Shorthand for settings
		$settings = $this->settings['payment']['stripe'];

		// Stripe object
		$stripe = EForm_Payment_Handler_Stripe::instance();

		// Set the API key
		$stripe->set_api_key( $settings['api'] );
		// Get current user id
		$user_id = get_current_user_id();
		// If not logged in, then just fail
		if ( 0 == $user_id ) {
			return $payment_status;
		}
		// Now get the plan
		$plan = '';
		if ( 'dynamic' == $settings['subscription']['type'] ) {
			// Dynamically create a plan
			$format_string = $this->get_format_string();
			$plan_name = str_replace( array_keys( $format_string ), array_values( $format_string ), $settings['subscription']['plan_name'] );
			$recur_amount = $payment_amount;
			if ( false == $this->settings['payment']['stripe']['zero_decimal'] ) {
				$recur_amount = $payment_amount * 100;
			}
			// Create a product for this form if it doesn't exists
			$product_config = [
				'id' => sprintf( 'eform-prod-%1$d', $this->form_id ),
				'name' => $settings['subscription']['product_name'] . '[' . $this->form_id . ']',
				'type' => 'service',
				'statement_descriptor' => $settings['subscription']['statement_descriptor'],
			];
			$product_obj = $stripe->get_product( $product_config['id'] );
			if ( false === $product_obj ) {
				$product_obj = $stripe->create_product( $product_config );
			}
			// Now get the $product_id
			$product_id = null;
			if ( $product_obj && isset( $product_obj->id ) ) {
				$product_id = $product_obj->id;
			}
			// If couldn't create the product, then return failure
			if ( ! $product_id ) {
				return $payment_status;
			}

			// Now create or retrieve the plan
			$plan_config = [
				'id' => sprintf( 'eform-sub-%1$d-%2$d', $this->form_id, $this->data_id ),
				'amount' => $recur_amount, // $1.00 USD
				'currency' => strtolower( $this->settings['payment']['currency'] ),
				'interval' => $settings['subscription']['plan_interval'],
				'nickname' => $plan_name,
				'interval_count' => absint( $settings['subscription']['interval_count'] ),
				'product' => $product_id,
			];
			// If retrying payment, there's a chance that plan exists
			if ( $retry ) {
				$plan_obj = $stripe->get_plan( $plan_config['id'] );
			}
			// At this point, if no plan is still found, then try to create one
			if ( ! isset( $plan_obj ) || ! $plan_obj ) {
				$plan_obj = $stripe->create_plan( $plan_config );
			}
			// If plan is successfully retreived
			if ( false !== $plan_obj ) {
				$plan = $plan_obj->id;
			}
		} else {
			// Statically select a plan and assign
			$plan = $settings['subscription']['default_plan_id'];
			$cond_plans = (array) $settings['subscription']['cond_plans'];
			if ( ! empty( $cond_plans ) ) {
				foreach ( $cond_plans as $cond_plan ) {
					if ( $this->validate_logic_state( $cond_plan['logics'] ) ) {
						$plan = $cond_plan['planid'];
						break;
					}
				}
			}
		}
		// If not a valid plan
		if ( empty( $plan ) ) {
			return $payment_status;
		}

		// Now create a customer if necessary
		if ( empty( $stripe_client_data ) ) {
			$stripe_client_data = null;
		}
		$cust_id = $stripe->create_customer(
			$user_id,
			$this->data_id,
			$stripe_client_data
		);
		if ( ! $cust_id ) {
			return $payment_status;
		}

		// Update the default payment source if provided
		if ( 'new' != $source ) {
			$stripe->update_customer_default_source( $user_id, $source );
		}

		// At this point the customer has been created if necessary, and the
		// payment source has been updated
		// Now assign the plan
		// But do check for payment retry and existing subscription
		if ( $retry && $prev_record !== null && $prev_record->txn !== null ) {
			$prev_subscription_id = $prev_record->txn;
			// cancel it delete_subscription
			$stripe->delete_subscription( $prev_subscription_id );
		}

		$subscription = $stripe->create_subscription( $user_id, $plan );
		if ( is_object( $subscription ) ) {
			// Subscription was created, now check on payment status
			$meta = [
				'plan' => $subscription->plan->id,
				'livemode' => $subscription->livemode,
				'customer' => $subscription->customer,
				'created' => $subscription->created,
				'latest_invoice' => $subscription->latest_invoice->id,
			];
			$pdata_update = [
				'status' => 3, // Error possibly
				'txn' => $subscription->id,
				'meta' => maybe_serialize( $meta ),
				'rplan' => $subscription->plan->nickname,
				'rinterval' => $interval_map[ $subscription->plan->interval ],
				'rfrequency' => $subscription->plan->interval_count,
			];
			$payment_intent = $subscription->latest_invoice->payment_intent;

			if ( 'active' === $subscription->status ) {
				// Success, so save the stuff
				$payment_status['success'] = true;
				$pdata_update['status'] = 1; // Processed and successful

			} elseif (
				'incomplete' === $subscription->status
				&& 'requires_action' === $payment_intent->status
			) {
				// requires user action for completing payment, so pass to the client
				$pdata_update['status'] = 4; // awaiting
				$payment_status['success'] = false;
				$payment_status['requires_action'] = [
					'client_secret' => $payment_intent->client_secret,
					'txn_id' => $pdata_update['txn'],
				];
			} else {
				// there was some failure related to card
				$pdata_update['status'] = 3; // error
				$payment_status['success'] = false;
			}

			// update payment data about this subscription
			$wpdb->update( $ipt_fsqm_info['payment_table'], $pdata_update, array(
				'id' => $id,
			), array(
				'%d', '%s', '%s', '%s', '%d', '%d'
			), '%d' );
		} else {
			// Error so prepare the modified db data
			$this->set_payments_error( $id );
		}
		return $payment_status;
	}

	public function offline_checkout( $payment_amount, $invoice_id ) {
		$payment_status = [
			'needed' => true,
			'success' => false,
			'redirect_url' => false,
		];
		$offline = EForm_Payment_Handler_Offline::instance();
		$product_info = $this->get_payment_product_info( $invoice_id );
		// Initiate the payment and it will automatically handle the DB, since
		// it is built-in system.
		$payment_status['success'] = $offline->initiate_payment( $payment_amount, $invoice_id, $product_info );
		return $payment_status;
	}

	public function stripe_direct_payment( $total_amount, $id, $stripe_client_data ) {
		global $wpdb, $ipt_fsqm_info;
		// Prepare the return
		$payment_status = array(
			'success' => false,
			'redirect_url' => false,
		);

		// Stripe object
		$stripe = EForm_Payment_Handler_Stripe::instance();

		// Set the API key
		$stripe->set_api_key( $this->settings['payment']['stripe']['api'] );

		// Form the parameters
		$product_info = $this->get_payment_product_info( $id );
		$product = array(
			'description' => $product_info['name'],
			'metadata' => array(
				'sku' => $product_info['sku'],
				'invoiceid' => $product_info['invoiceid'],
				'proddescription' => $product_info['description'],
				'data_id' => $this->data_id,
			),
		);

		// Payment amount
		$payment_amount = $total_amount;
		if ( false == $this->settings['payment']['stripe']['zero_decimal'] ) {
			$payment_amount = $total_amount * 100;
		}

		// A valid email address
		$email = $this->data->email;
		$currency = $this->settings['payment']['currency'];

		// Now execute
		// Since we are using SCA, then we call on the verify payment intent method
		$charge = $stripe->verify_intent( $stripe_client_data, $product, $email, $currency, $payment_amount );

		// Save the response
		if ( is_object( $charge ) && 'succeeded' == $charge->status ) {
			// Successful
			$payment_status['success'] = true;
			// Prepare the new db data
			$pdata_update = array(
				'status' => 1, // Approved and processed payment
				'txn' => $charge->balance_transaction,
				'meta' => maybe_serialize( array(
					'id' => $charge->id,
					'created' => $charge->created,
					'invoice' => $charge->invoice,
					'livemode' => $charge->livemode,
				) ),
			);
			$wpdb->update( $ipt_fsqm_info['payment_table'], $pdata_update, array(
				'id' => $id,
			), array(
				'%d', '%s', '%s',
			), '%d' );
		} else {
			// Error so prepare the modified db data
			$this->set_payments_error( $id );
		}

		return $payment_status;
	}

	public function authorizenet_direct_payment( $total_amount, $id, $ccinfo ) {
		global $wpdb, $ipt_fsqm_info;
		// Prepare the return
		$payment_status = array(
			'success' => false,
			'redirect_url' => false,
		);

		// Authorize.net helper
		$authorizenet = EForm_Payment_Handler_AuthorizeNet::instance();
		$authorizenet->set_api_context( $this->settings['payment']['authorizenet']['login_id'], $this->settings['payment']['authorizenet']['transaction_key'] );

		// Product data
		$product = $this->get_payment_product_info( $id );
		// Card Data
		$cc = $this->set_cc_info( $ccinfo );

		// Execute
		$result = $authorizenet->direct_payment( $product, $this->settings['payment']['currency'], $total_amount, $cc, $this->settings['payment']['authorizenet']['mode'] );

		// If it was a success
		if ( is_object( $result ) && is_object( $result->getTransactionResponse() ) && "1" == $result->getTransactionResponse()->getResponseCode() ) {
			$tresponse = $result->getTransactionResponse();
			$payment_status['success'] = true;
			// Prepare the new db data
			$pdata_update = array(
				'status' => 1, // Approved and processed payment
				'txn' => $tresponse->getTransId(),
				'meta' => maybe_serialize( array(
					'auth_code' => $tresponse->getAuthCode(),
					'ref_id' => $tresponse->getRefTransID(),
				) ),
			);
			$wpdb->update( $ipt_fsqm_info['payment_table'], $pdata_update, array(
				'id' => $id,
			), array(
				'%d', '%s', '%s',
			), '%d' );
		} else {
			// Insert failures
			$this->set_payments_error( $id );
		}

		return $payment_status;
	}

	public function paypal_direct_payment( $total_amount, $id, $ccinfo ) {
		global $wpdb, $ipt_fsqm_info;
		// Prepare the return
		$payment_status = array(
			'success' => false,
			'redirect_url' => false,
		);

		// Form the parameters
		$paypal = EForm_Payment_Handler_PayPal::instance();
		$this->set_paypal_api_context( $paypal );

		// Product data
		$product = $this->get_payment_product_info( $id );

		$cc = $this->set_cc_info( $ccinfo );

		$result = $paypal->direct_payment( $product, $this->settings['payment']['currency'], $total_amount, $cc );

		// If it was a success
		if ( is_object( $result ) && $result->getState() == 'approved' ) {
			$payment_status['success'] = true;
			// Prepare the new db data
			$pdata_update = array(
				'status' => 1, // Approved and processed payment
				'txn' => $result->getId(),
				'meta' => maybe_serialize( array(
					'create_time' => $result->getCreateTime(),
					'update_time' => $result->getUpdateTime(),
					'intent' => $result->getIntent(),
				) ),
			);
			$wpdb->update( $ipt_fsqm_info['payment_table'], $pdata_update, array(
				'id' => $id,
			), array(
				'%d', '%s', '%s',
			), '%d' );
		// If it was a failure
		} else {
			// Insert failures
			$this->set_payments_error( $id );
		}

		return $payment_status;
	}

	public function set_payments_error( $id ) {
		global $wpdb, $ipt_fsqm_info;
		$pdata_update = array(
			'status' => 3, // Error
			'txn' => null,
			'meta' => maybe_serialize( array() ),
		);
		$wpdb->update( $ipt_fsqm_info['payment_table'], $pdata_update, array(
			'id' => $id,
		), array(
			'%d', '%s', '%s',
		), '%d' );
	}

	public function set_payments_data_status( $status = 1 ) {
		global $ipt_fsqm_info, $wpdb;
		$pdata_update = [
			'status' => $status,
		];
		$wpdb->update(
			$ipt_fsqm_info['payment_table'],
			$pdata_update,
			[
				'data_id' => $this->data_id,
			],
			'%d',
			'%d'
		);
	}

	public function set_cc_info( $ccinfo ) {
		// Normalize the $ccinfo variable
		$ccinfo = wp_parse_args( $ccinfo, array(
			'ctype' => '',
			'number' => '',
			'name' => '',
			'expiry' => '',
			'cvc' => '',
			'address' => '',
			'country' => '',
			'zip' => '',
		) );
		// Set the credit card information
		$cc = array();
		$cc['type'] = $ccinfo['ctype'];
		$cc['number'] = preg_replace( '/[^0-9]/', '', $ccinfo['number'] );

		$expiry = explode( '/', $ccinfo['expiry'] );
		$cc['em'] = trim( $expiry[0] );
		$cc['ey'] = trim( $expiry[1] );
		if ( strlen( $cc['ey'] ) == '2' ) {
			$current_year = date( 'Y', current_time( 'timestamp' ) );
			$cc['ey'] = substr( $current_year, 0, 2 ) . $cc['ey'];
		}
		$cc['cvv'] = $ccinfo['cvc'];
		$names = explode( ' ', $ccinfo['name'] );
		$cc['fname'] = array_shift( $names );
		$cc['lname'] = implode( ' ', $names );
		$cc['name'] = $ccinfo['name'];
		$country = IPT_FSQM_Form_Elements_Static::get_country_json( $ccinfo['country'] );
		if ( ! empty( $country ) && isset( $country['ISO'] ) ) {
			$cc['country'] = $country['ISO'];
		} else {
			$cc['country'] = array(
				'alpha2' => '',
				'alpha3' => '',
			);
		}
		$cc['address'] = strip_tags( $ccinfo['address'] );
		$cc['zip'] = preg_replace( '/[^0-9\s]/', '', $ccinfo['zip'] );

		return $cc;
	}

	public function paypal_express_checkout( $total_amount, $id ) {
		// PayPal Helper
		$paypal = EForm_Payment_Handler_PayPal::instance();

		// Form the api context
		$this->set_paypal_api_context( $paypal );

		// Set information about the product
		$product = $this->get_payment_product_info( $id );

		// Call the API
		return $paypal->get_express_checkout_url( $product, $this->settings['payment']['currency'], $total_amount, $this->get_trackback_url() );
	}

	public function set_paypal_api_context( $paypal ) {
		$paypal = EForm_Payment_Handler_PayPal::instance();
		// Form the api context
		$paypal->set_api_context( $this->settings['payment']['paypal']['d_settings']['client_id'], $this->settings['payment']['paypal']['d_settings']['client_secret'], $this->settings['payment']['paypal']['mode'], $this->settings['payment']['paypal']['partner'] );
	}

	public function get_payment_product_info( $id ) {
		$name = $this->settings['payment']['itemname'];
		if ( $name == '' ) {
			$name = $this->name;
		}
		$sku = $this->settings['payment']['itemsku'];
		if ( $sku == '' ) {
			$sku = $this->form_id;
		}

		$invoiceid = str_replace( '{id}', $id, $this->settings['payment']['invoicenumber'] );
		if ( $invoiceid == '' ) {
			$invoiceid = $id;
		}

		$description = $this->settings['payment']['itemdescription'];

		$product = array(
			'name' => $name,
			'sku' => $sku,
			'invoiceid' => $invoiceid,
			'description' => $description,
		);

		return $product;
	}

	public function do_core_integrations( $update = false ) {
		global $wpdb, $ipt_fsqm_info;
		// Get the settings
		$op = $this->settings['core'];

		// If updating, then just call the meta update
		if ( $update ) {
			if ( true == $op['user_meta']['enabled'] ) {
				$this->core_integration_user_meta();
			}
			return;
		}

		// Check for registration
		// Also login if necessary
		if ( true == $op['reg']['enabled'] ) {
			$user_id = $this->core_integration_register();
			if ( false !== $user_id ) {
				// Update and assign this submission to newly registered user
				$wpdb->update( $ipt_fsqm_info['data_table'], array( 'user_id' => $user_id ), array( 'id' => $this->data_id ), '%d', '%d' );
				$this->data->user_id = $user_id;
			}
		}

		// Check for guest blogging
		// Attach to the currently logged in user
		// if necessary.
		if ( true == $op['post']['enabled'] ) {
			$this->core_integration_gp();
		}

		// Check for user meta update
		if ( true == $op['user_meta']['enabled'] ) {
			$this->core_integration_user_meta();
		}

		// Third party hooking
		do_action( 'ipt_fsqm_hook_core_integrations', $this, $op );
	}

	public function core_integration_user_meta() {
		// No need if not logged in
		if ( ! is_user_logged_in() ) {
			return;
		}

		// Options
		$op = $this->settings['core']['user_meta'];

		// Check for metas
		if ( empty( $op['meta'] ) ) {
			return;
		}

		// Now get a value class
		$data_values = new IPT_eForm_Form_Elements_Values( $this->data_id );
		// Get userID
		$user_id = ! empty( $this->data->user_id ) ? $this->data->user_id : get_current_user_id();
		// Don't do anything if updating from admin and not logging registered user
		if ( $this->admin_update && empty( $this->data->user_id ) ) {
			return;
		}
		// Possible error??
		if ( 0 == $user_id ) {
			return;
		}
		// Loop through all and add the meta
		foreach ( $op['meta'] as $meta ) {
			$meta_key = $meta['meta_key'];
			$meta_value = $data_values->get_value( $meta['m_type'], $meta['key'], ( true == $op['metaarray'] ? 'json' : 'string' ), 'label' );
			update_user_meta( $user_id, $meta_key, $meta_value );
		}
	}

	public function core_integration_gp() {
		$op = $this->settings['core']['post'];

		if ( is_null( $this->submission_db_elms['guestblog']['key'] ) ) {
			return;
		}
		// Get the data first
		$post_data = $this->data->pinfo[ $this->submission_db_elms['guestblog']['key'] ];
		// Do not do anything if title is empty
		if ( '' == $post_data['title'] ) {
			return;
		}
		// Do not do anything if value is empty
		if ( '' == $post_data['value'] ) {
			return;
		}

		// Get the format strings
		$format_string_components = $this->get_format_string();

		// Prepare the post content
		$content = $post_data['value'];
		// Add additional data
		if ( '' != $this->settings['core']['post']['add_msg'] ) {
			$content .= wpautop( str_replace( array_keys( $format_string_components ), array_values( $format_string_components ), $this->settings['core']['post']['add_msg'] ) );
		}
		// Add the editors message
		if ( '' != $this->settings['core']['post']['guest_msg'] && ! is_user_logged_in() ) {
			$avatar = get_avatar_url( $this->data->email );
			$bio = $post_data['bio'];
			$name = $this->data->f_name . ' ' . $this->data->l_name;
			$editor_message = str_replace( array( '%NAME%', '%AVATAR%', '%BIO%' ), array( $name, $avatar, $bio ), $this->settings['core']['post']['guest_msg'] );
			$editor_message = str_replace( array_keys( $format_string_components ), array_values( $format_string_components ), $editor_message );
			$content .= wpautop( $editor_message );
		}

		// Prepare the metadata
		$meta_data = array();
		if ( '' != $post_data['bio'] ) {
			$meta_data['eform-guestpost-bio'] = $post_data['bio'];
		}
		// Now add additional metadata
		if ( ! empty( $op['meta'] ) ) {
			// Get a value class
			$data_values = new IPT_eForm_Form_Elements_Values( $this->data_id );
			// Loop through all and add the meta
			foreach ( $op['meta'] as $meta ) {
				$meta_data[ $meta['meta_key'] ] = $data_values->get_value( $meta['m_type'], $meta['key'], ( true == $op['metaarray'] ? 'json' : 'string' ), 'label' );
			}
		}

		// Now add the post
		$postarr = array(
			'post_author' => ( 0 == $this->data->user_id ? $this->settings['core']['post']['user_id'] : $this->data->user_id ), // This will take care of admin specified author or logged in user and also the case where the form registers the user
			'post_content' => $content, // Actual post
			'post_title' => $post_data['title'], // Post title
			'post_status' => $op['status'], // set to draft, always draft
			'post_type' => $op['post_type'],
			'meta_input' => $meta_data,
		);

		// Add it
		$post_id = wp_insert_post( $postarr );

		// Now we add the taxonomies manually
		// using tax_input would not work because
		// the newly created user might not have sufficient capability
		// better to use API
		if ( ! empty( $post_data['taxonomy'] ) ) {
			foreach ( $post_data['taxonomy'] as $taxonomy => $terms ) {
				if ( ! empty( $terms ) ) {
					wp_set_object_terms( $post_id, $terms, $taxonomy );
				}
			}
		}

		// Now let's set featured image if config says so
		if ( '' != $this->settings['core']['post']['feature_image'] ) {
			$uploader = new IPT_FSQM_Form_Elements_Uploader( $this->form_id, $this->settings['core']['post']['feature_image'] );
			$uploads = $uploader->get_uploads( $this->data_id );
			// We will loop through and set the first image as the featured image
			if ( ! empty( $uploads ) ) {
				foreach ( $uploads as $upload ) {
					// Check if image
					if ( in_array( $upload['mime_type'], array( 'image/jpeg', 'image/jpg', 'image/png', 'image/gif' ) ) && ! empty( $upload['media_id'] ) ) {
						set_post_thumbnail( $post_id, $upload['media_id'] );
						break;
					}
				}
			}
		}
	}


	/**
	 * Get the lookup (m_type and key) for the Registration Fields, given
	 * the key.
	 *
	 * This is backward compatible. When only a number is given, m_type is
	 * considered pinfo. But when full Id like F12 is given, it calculates it
	 * automatically.
	 *
	 * @param mixed (string|int) $id The Id of the element (F12 or 12 (O12 is considered))
	 * @return array Associative array of element lookup (with m_type and key)
	 */
	protected function get_reg_field_elm_lookup( $id ) {
		$elm_mtype = 'pinfo';
		$elm_key = $id;
		$elm_mtype_lookup = [
			'f' => 'freetype',
			'o' => 'pinfo',
		];
		if ( preg_match( '/(f|o)(\d+)/i', $id, $matches ) ) {
			$elm_mtype = $elm_mtype_lookup[ strtolower( $matches[1] ) ];
			$elm_key = $matches[2];
		}
		return [
			'm_type' => $elm_mtype,
			'key' => $elm_key,
		];
	}


	/**
	 * Get HTML id of the registration field.
	 *
	 * @param string $id Id of the field.
	 * @return string HTML id of the field.
	 */
	protected function get_reg_field_id( $id ) {
		$elm_lookup = $this->get_reg_field_elm_lookup( $id );
		return 'ipt_fsqm_form_' . $this->form_id . '_' . $elm_lookup['m_type'] .
			'_' . $elm_lookup['key'];
	}

	/**
	 * Get username or password given the element Id.
	 *
	 * This is backward compatible with v4.7. Now we have deprecated the
	 * old Small Text element from pinfo. Instead we recommend Text Input
	 * from Feedback Small.
	 *
	 * So it checks if old data format (just number) is used in the value.
	 * If it is, then return the pinfo values.
	 *
	 * Otherwise check with the element Id (F0, O11 etc) and return accordingly.
	 *
	 * @param mixed (int|string) $id The Id of the element.
	 * @return string Username or Password.
	 */
	protected function get_reg_field_value( $id ) {
		$elm_lookup = $this->get_reg_field_elm_lookup( $id );
		$m_type = $elm_lookup['m_type'];
		$key = $elm_lookup['key'];
		if ( ! isset( $this->data->{$m_type}[ $key ] ) ) {
			return '';
		}
		$submission_data = $this->data->{$m_type}[ $key ];
		if ( isset( $submission_data['encrypted'] ) && $submission_data['encrypted'] ) {
			return $this->decrypt_password_field( $submission_data['value'] );
		}
		return $submission_data['value'];
	}

	/**
	 * Check if element is set as registration field.
	 *
	 * @param string $m_type Element m_type.
	 * @param string $key Element key.
	 * @param string $lookup The key to look inside settings.
	 * @return boolean true if it is, false otherwise.
	 */
	protected function is_element_of_reg_field( $m_type, $key, $lookup ) {
		if ( $this->settings['core']['reg']['enabled'] ) {
			$elm_lookup = $this->get_reg_field_elm_lookup( $this->settings['core']['reg'][ $lookup ] );
			if ( $m_type === $elm_lookup['m_type'] && $key == $elm_lookup['key'] ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Set possible registration errors in the instance property.
	 *
	 * Call this during process_save and before all errors are considered.
	 * It doesn't do anything if registration isn't possible.
	 *
	 * @return void
	 */
	protected function set_registration_errors() {
		// Dont do anything if it is not possible to register
		if ( false == $this->is_reg_possible() ) {
			return;
		}

		// Get the settings
		$op = $this->settings['core']['reg'];

		// Get the email, username, password
		$email = $this->data->email;
		// Now we need to do some testing for backward compatibility
		// We have deprecated the old Small Text element and have to use
		// the feedback_small instead.
		$username = $this->get_reg_field_value( $op['username_id'] );
		// $this->data->pinfo[ $op['username_id'] ]['value'];
		$password = $this->get_reg_field_value( $op['password_id'] );
		$username_id = $this->get_reg_field_id( $op['username_id'] );
		$password_id = $this->get_reg_field_id( $op['password_id'] );
		// $password = $this->data->pinfo[ $op['password_id'] ]['value'];
		// Double check for errors
		if ( email_exists( $email ) ) {
			$this->core_reg_errors[] = [
					'id' => 'ipt_fsqm_form_' . $this->form_id . '_pinfo_' . $this->submission_db_elms['email']['key'],
					'msgs' => array( __( 'Email address is already registered', 'ipt_fsqm' ) ),
			];
		}
		if ( empty( $email ) ) {
			$this->core_reg_errors[] = [
					'id' => 'ipt_fsqm_form_' . $this->form_id . '_pinfo_' . $this->submission_db_elms['email']['key'],
					'msgs' => array( __( 'Email address can not be empty.', 'ipt_fsqm' ) ),
			];
		}
		// Check if username is valid
		if ( ! validate_username( $username ) ) {
			$this->core_reg_errors[] = [
				'id' => $username_id,
				'msgs' => [
					__( 'The username is not valid. Only alphanumeric plus _, space, ., -, *, and @ are allowed.', 'ipt_fsqm' ),
				],
			];
		}
		if ( username_exists( $username ) ) {
			$this->core_reg_errors[] = [
				'id' => $username_id,
				'msgs' => [
					__( 'Username already taken. Please use something else.', 'ipt_fsqm' ),
				],
			];
		}

		// Check if password is valid
		if ( empty( $password ) ) {
			$this->core_reg_errors[] = array(
				'id' => $password_id,
				'msgs' => array( __( 'Password field can not be empty.', 'ipt_fsqm' ) ),
			);
		}
	}

	/**
	 * Does the User Registration process of core integration
	 *
	 * It takes the values from the form and properly creates a WP user. If for
	 * some reason, it is not possible to create the user then it stops.
	 *
	 * It does not check whether email or username already exists. It should be
	 * done beforehand during the error process.
	 *
	 * @return     mixed   (bool|int) false if user could not be created, user id otherwise
	 */
	public function core_integration_register() {
		// Dont do anything if it is not possible to register
		if ( false == $this->is_reg_possible() ) {
			return false;
		}

		// If something has errored out, then bail
		if ( ! empty( $this->core_reg_errors ) ) {
			return false;
		}

		// Here we don't check for errors, because we are expecting
		// set_registration_errors to have already been called.

		// Get the settings
		$op = $this->settings['core']['reg'];

		// Get the email, username, password
		$email = $this->data->email;
		$username = $this->get_reg_field_value( $op['username_id'] );
		$password = $this->get_reg_field_value( $op['password_id'] );

		// At this point, everything checks out
		$firstname = '';
		if ( null !== $this->submission_db_elms['f_name']['key'] ) {
			$firstname = $this->data->pinfo[ $this->submission_db_elms['f_name']['key'] ]['value'];
		}
		$lastname = '';
		if ( null !== $this->submission_db_elms['l_name']['key'] ) {
			$lastname = $this->data->pinfo[ $this->submission_db_elms['l_name']['key'] ]['value'];
		}

		$userdata = array(
			'user_pass' => $password,
			'user_login' => $username,
			'user_email' => $email,
			'first_name' => $firstname,
			'last_name' => $lastname,
		);
		// Add the role if needed
		if ( 'wp_default' != $op['role'] && get_role( $op['role'] ) ) {
			$userdata['role'] = $op['role'];
		}

		// Insert the user
		$user_id = wp_insert_user( $userdata );
		if ( is_wp_error( $user_id ) ) {
			return false;
		}

		// Now update the meta
		if ( ! empty( $op['meta'] ) ) {
			// Get a value class
			$data_values = new IPT_eForm_Form_Elements_Values( $this->data_id );
			// Loop through all and add the meta
			foreach ( $op['meta'] as $meta ) {
				$meta_key = $meta['meta_key'];
				$meta_value = $data_values->get_value( $meta['m_type'], $meta['key'], ( true == $op['metaarray'] ? 'json' : 'string' ), 'label' );
				update_user_meta( $user_id, $meta_key, $meta_value );
			}
		}

		return $user_id;
	}


	/**
	 * Determines if registration is possible.
	 *
	 * First email field is checked. If it is not present or conditionally
	 * hidden then it is not possible to register.
	 *
	 * It checks the conditional states of username and password field of the
	 * form. If one of them is hidden, then registration isn't possible.
	 *
	 * It also takes into account if registration is enabled or not
	 *
	 * @return     boolean  True if registration possible, False otherwise.
	 */
	public function is_reg_possible() {
		// No need to register if user is logged in or this is an update
		if ( is_user_logged_in() || $this->admin_update || $this->doing_update ) {
			return false;
		}

		// A little shortcut
		$op = $this->settings['core']['reg'];

		// Is settings enabled?
		if ( false == $op['enabled'] ) {
			return false;
		}

		// Are both username and password field set?
		if ( '' == $op['username_id'] || '' == $op['password_id'] ) {
			return false;
		}

		// Is email field there?
		if ( null === $this->submission_db_elms['email']['key'] ) {
			return false;
		}

		// Is email field conditionally shown?
		$email_elm = $this->get_element_from_layout( array(
			'm_type' => 'pinfo',
			'type' => 'email',
			'key' => $this->submission_db_elms['email']['key'],
		) );
		if ( false == $this->validate_data_against_conditional_logic( $email_elm, $this->submission_db_elms['email']['key'] ) ) {
			return false;
		}

		// Are both conditionally shown
		$username_lookup = $this->get_reg_field_elm_lookup( $op['username_id'] );
		$password_lookup = $this->get_reg_field_elm_lookup( $op['password_id'] );
		$username_elem = $this->get_element_from_layout( $username_lookup );
		$password_elem = $this->get_element_from_layout( $password_lookup );
		if (
			false == $this->validate_data_against_conditional_logic( $username_elem, $username_lookup['key'] )
			|| false == $this->validate_data_against_conditional_logic( $password_elem, $password_lookup['key'] )
		) {
			return false;
		}

		// Everything checks out
		return true;
	}

	public function do_integrations( $instant = false ) {
		// Here we check for conditional integrations
		if (
			! $instant
			&& $this->settings['integration']['conditional']['active'] == true
			&& ! $this->validate_logic_state( $this->settings['integration']['conditional']['logic'] )
		) {
			return;
		}

		// All set, now init the variables
		$su_name = '';
		$su_names = array();
		if ( $this->data->f_name != '' ) {
			$su_names[] = $this->data->f_name;
		}
		if ( $this->data->l_name != '' ) {
			$su_names[] = $this->data->l_name;
		}
		if ( ! empty( $su_names ) ) {
			$su_name = implode( ' ', $su_names );
		}

		// $additional_metadata = [];
		// if (
		// 	$this->settings['integration']['meta']['active'] === true
		// 	&& ! empty( $this->settings['integration']['meta']['data'] )
		// ) {
		// 	// Get a value class
		// 	$data_values = new IPT_eForm_Form_Elements_Values( $this->data_id );
		// 	// Loop through all and add the meta
		// 	foreach ( $this->settings['integration']['meta']['data'] as $metadata ) {
		// 		$meta_key = $metadata['meta_key'];
		// 		$meta_value = $data_values->get_value( $metadata['m_type'], $metadata['key'], 'string', 'label' );
		// 		$additional_metadata[ $meta_key ] = strip_tags( $meta_value );
		// 	}
		// }

		// MailChimp
		if (
			$this->settings['integration']['mailchimp']['enabled'] == true
			&& $this->data->email != ''
			&& '' != $this->settings['integration']['mailchimp']['api']
			&& '' != $this->settings['integration']['mailchimp']['list_id']
		) {
			$mc = new IPT_EForm_Intg_Mailchimp(
				$this->settings['integration']['mailchimp'],
				$this,
				$instant
			);
			$mc->do_integration();
		}

		// Aweber
		if ( $this->settings['integration']['aweber']['enabled'] == true && $this->data->email != '' && '' != $this->settings['integration']['aweber']['authorization_code'] && '' != $this->settings['integration']['aweber']['list_id'] ) {
			try {
				$aw_credentials = array(
					0 => $this->settings['integration']['aweber']['consumerKey'],
					1 => $this->settings['integration']['aweber']['consumerSecret'],
					2 => $this->settings['integration']['aweber']['accessKey'],
					3 => $this->settings['integration']['aweber']['accessSecret'],
				);

				$aw_subscriber = array(
					'email' => $this->data->email,
					'name' => $su_name,
				);

				$aw_application = new AWeberAPI( $aw_credentials[0], $aw_credentials[1] );
				$aw_account = $aw_application->getAccount( $aw_credentials[2], $aw_credentials[3] );
				$aw_list_url = '/accounts/' . $aw_account->id . '/lists/' . str_replace( 'awlist', '', trim( $this->settings['integration']['aweber']['list_id'] ) );
				$aw_list = $aw_account->loadFromUrl( $aw_list_url );
				$aw_new_subscriber = $aw_list->subscribers->create( $aw_subscriber );
			} catch ( Exception $e ) {

			}
		}

		// Get Response
		if (
			$this->settings['integration']['get_response']['enabled'] == true
			&& $this->data->email != ''
			&& '' != $this->settings['integration']['get_response']['api']
			&& '' != $this->settings['integration']['get_response']['campaign_id']
		) {
			$gr_c = new IPT_EForm_Intg_GetResponse(
				$this->settings['integration']['get_response'],
				$this,
				$instant
			);
			$gr_c->do_integration();
		}

		// Campaign Monitor
		if (
			$this->settings['integration']['campaign_monitor']['enabled'] == true
			&& $this->data->email != ''
			&& '' != $this->settings['integration']['campaign_monitor']['api']
			&& '' != $this->settings['integration']['campaign_monitor']['list_id']
		) {
			$cm_intg = new IPT_EForm_Intg_CampaignMonitor(
				$this->settings['integration']['campaign_monitor'],
				$this,
				$instant
			);
			$cm_intg->do_integration();
		}

		// Mailster (MyMail)
		if (
			$this->settings['integration']['mymail']['enabled'] == true
			&& function_exists( 'mailster' ) && $this->data->email != ''
		) {
			$ms_intg = new IPT_EForm_Intg_Mailster(
				$this->settings['integration']['mymail'],
				$this,
				$instant
			);
			$ms_intg->do_integration();
		}

		// Sendy
		if ( $this->settings['integration']['sendy']['enabled'] == true && $this->data->email != '' ) {
			// Setup the post data
			$s_entry = array(
				'name' => $su_name,
				'email' => $this->data->email,
				'list' => $this->settings['integration']['sendy']['list_id'],
				'api_key' => $this->settings['integration']['sendy']['api_key'],
				'boolean' => 'true',
			);
			$surl = trailingslashit( $this->settings['integration']['sendy']['url'] ) . 'subscribe';

			// Now do the post
			$s_remote_obj = wp_remote_post( $surl, array(
				'method' => 'POST',
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array( 'Content-type' => 'application/x-www-form-urlencoded' ),
				'body' => $s_entry,
			) );
		}

		// Active Campaign
		if (
			$this->settings['integration']['active_campaign']['enabled'] == true
			&& $this->data->email != ''
		) {
			$acm_intg = new IPT_EForm_Intg_ActiveCampaign(
				$this->settings['integration']['active_campaign'],
				$this,
				$instant
			);
			$acm_intg->do_integration();
		}

		// MailPoet
		if ( $this->settings['integration']['mailpoet']['enabled'] == true && class_exists( 'WYSIJA' ) && $this->data->email != '' ) {
			$mp_entry = array(
				'user' => array(
					'email' => $this->data->email,
					'firstname' =>  $this->data->f_name,
					'lastname' =>  $this->data->l_name,
				),
			);
			if ( ! empty( $this->settings['integration']['mailpoet']['list_ids'] ) ) {
				$mp_entry['user_list'] = array(
					'list_ids' => array_map( 'intval', $this->settings['integration']['mailpoet']['list_ids'] ),
				);
			}
			WYSIJA::get( 'user', 'helper' )->addSubscriber( $mp_entry );
		}

		// MailPoet 3
		if (
			$this->settings['integration']['mailpoet3']['enabled']
			&& class_exists( '\MailPoet\API\API' )
			&& '' != $this->data->email
		) {
			$mp3_intg = new IPT_EForm_Intg_MailPoet(
				$this->settings['integration']['mailpoet3'],
				$this,
				$instant
			);
			$mp3_intg->do_integration();
		}

		// Enormail
		if (
			true == $this->settings['integration']['enormail']['enabled']
			&& '' != $this->data->email
		) {
			$enm_intg = new IPT_EForm_Intg_Enormail(
				$this->settings['integration']['enormail'],
				$this,
				$instant
			);
			$enm_intg->do_integration();
		}

		// MailerLite
		if (
			true == $this->settings['integration']['mailerlite']['enabled']
			&& '' != $this->data->email
		) {
			$mailerlite = new IPT_EForm_Intg_Mailerlite(
				$this->settings['integration']['mailerlite'],
				$this,
				$instant
			);
			$mailerlite->do_integration();
		}

		// MailWhizz
		if (
			true == $this->settings['integration']['mailwizz']['enabled']
			&& '' != $this->data->email
		) {
			$mw_intg = new IPT_EForm_Intg_MailWizz(
				$this->settings['integration']['mailwizz'],
				$this,
				$instant
			);
			$mw_intg->do_integration();
		}

		// Convertkit
		if (
			$this->settings['integration']['convertkit']['enabled'] &&
			'' !== $this->data->email
		) {
			$cvk_intg = new IPT_EForm_Intg_Convertkit(
				$this->settings['integration']['convertkit'],
				$this,
				$instant
			);
			$cvk_intg->do_integration();
		}

		// URL
		if (
			true == $this->settings['integration']['formhandler']['enabled']
			&& '' != $this->settings['integration']['formhandler']['url']
			&& $instant === false
		) {
			$this->do_webhook_integration();
		}

		do_action( 'ipt_fsqm_hook_integration', $this );
	}

	protected function do_update_integration() {
		// For now we only support webhook integration
		if (
			true == $this->settings['integration']['formhandler']['enabled']
			&& '' != $this->settings['integration']['formhandler']['url']
			&& $this->settings['integration']['formhandler']['resend_on_update']
		) {
			$this->do_webhook_integration();
		}
	}

	protected function do_webhook_integration() {
		$url_entry = array(
			'data_id' => $this->data_id,
			'submission_date' => $this->data->date,
			'user_id' => $this->data->user_id,
			'f_name' => $this->data->f_name,
			'l_name' => $this->data->l_name,
			'email' => $this->data->email,
			'score' => $this->data->score,
			'max_score' => $this->data->max_score,
			'ip' => $this->data->ip,
			'remarks' => $this->data->comment,
			'referrer' => $this->data->referer,
			'url_track' => $this->data->url_track,
			'time' => $this->data->time,
			'link' => admin_url( 'admin.php?page=ipt_fsqm_view_submission&id=' . $this->data_id ),
			'trackback_id' => $this->get_trackback_id(),
			'trackback_url' => $this->get_trackback_url(),
		);

		if ( ! empty( $this->settings['integration']['formhandler']['meta'] ) ) {
			// Get a value class
			$data_values = new IPT_eForm_Form_Elements_Values( $this->data_id );
			$data_values->set_option_delimiter(
				$this->settings['integration']['formhandler']['option_delimiter']
			);
			$data_values->set_row_delimiter(
				$this->settings['integration']['formhandler']['row_delimiter']
			);
			$data_values->set_range_delimiter(
				$this->settings['integration']['formhandler']['range_delimiter']
			);
			$data_values->set_entry_delimiter(
				$this->settings['integration']['formhandler']['entry_delimiter']
			);
			// Loop through all and add the meta
			$format_strings = $this->get_format_string();
			foreach ( $this->settings['integration']['formhandler']['meta'] as $meta ) {
				$meta_key = $meta['meta_key'];
				if ( $meta['m_type'] === 'formatstring' ) {
					// since this is a format string, it can be a hardcoded value
					// or a format string from the data
					$accessor = $meta['key'];
					$meta_value = isset( $format_strings[ $accessor ] )
						? $format_strings[ $accessor ]
						: $accessor;
				} else {
					$meta_value = $data_values->get_value(
						$meta['m_type'],
						$meta['key'],
						( true == $this->settings['integration']['formhandler']['metaarray'] ? 'json' : 'string' ),
						'label'
					);
				}
				$url_entry[ $meta_key ] = $meta_value;
			}
		}

		if ( 'get' == $this->settings['integration']['formhandler']['method'] ) {
			wp_remote_get( $this->settings['integration']['formhandler']['url'], array(
				'headers' => array( 'x-powered-by' => 'eForm - Ultimate WordPress Form Builder' ),
				'body' => $url_entry,
			) );
		} else {
			wp_remote_post( $this->settings['integration']['formhandler']['url'], array(
				'headers' => array( 'x-powered-by' => 'eForm - Ultimate WordPress Form Builder' ),
				'body' => $url_entry,
			) );
		}
	}

	/**
	 * Get Score data of a layout. It loops over all mcq and freetype elements
	 * in the layout and gets their scoredata to calculate.
	 *
	 * @return array [ $max_score, $score_obtained, $percentage ]
	 */
	protected function get_layout_score( $layout ) {
		$all_layout_elements = [
			'mcq' => $this->get_keys_from_layout_by_m_type( 'mcq', $layout ),
			'freetype' => $this->get_keys_from_layout_by_m_type( 'freetype', $layout ),
		];

		$max_score = 0;
		$score_obtained = 0;
		// loop over mcqs and get the score
		foreach ( $all_layout_elements as $m_type => $elements ) {
			if ( ! empty( $elements ) ) {
				foreach ( $elements as $elm_key ) {
					$submission_data = $this->get_submission_from_data(
						[
							'key' => $elm_key,
							'm_type' => $m_type,
						]
					);
					// add to score if scoredata is present
					if (
						isset( $submission_data['scoredata'] )
						&& is_array( $submission_data['scoredata'] )
						&& ! empty( $submission_data['scoredata'] )
					) {
						$max_score += floatval( $submission_data['scoredata']['max_score'] ?? 0 );
						$score_obtained += floatval( $submission_data['scoredata']['score'] ?? 0 );
					}
				}
			}
		}
		$percentage = 0;
		if ( $max_score > 0 ) {
			$percentage = round(
				( $score_obtained / $max_score ) * 100,
				2
			);
		}
		$max_score = round( $max_score, 2 );
		$score_obtained = round( $score_obtained, 2 );
		return [ $max_score, $score_obtained, $percentage ];
	}

	/**
	 * Get the format string for replacing across following components User
	 * Notification Email Success messages Designation
	 *
	 * @return     array  a key => value pair associative, use it like
	 * str_replace( array_keys( $return ), array_values( $return ), subject )
	 */
	public function get_format_string() {
		// Carefully calculate the percentage
		// We do not want to throw any PHP warning for division by zero
		$percentage = 0;
		if ( $this->data->max_score != 0 ) {
			$percentage = $this->data->score * 100 / $this->data->max_score;
			$percentage = round( $percentage, 2 );
		}

		// Format string components for finding and replacing contents on
		// User Notification Email
		// Designation
		// Success messages
		$format_string_components = array(
			'%NAME%' => $this->data->f_name . ' ' . $this->data->l_name,
			'%FNAME%' => $this->data->f_name,
			'%LNAME%' => $this->data->l_name,
			'%EMAIL%' => $this->data->email,
			'%PHONE%' => $this->data->phone,
			'%TRACK_LINK%' => $this->get_trackback_url(),
			'%TRACK%' => '<a href="' . esc_attr( $this->get_trackback_url() ) . '">' . __( 'Click Here', 'ipt_fsqm' ) . '</a>',
			'%SCORE%' => $this->data->score . '/' . $this->data->max_score,
			'%OSCORE%' => $this->data->score,
			'%MSCORE%' => $this->data->max_score,
			'%SCOREPERCENT%' => number_format_i18n( (float) $percentage, $this->settings['ranking']['precision'] ) . __( '%', 'ipt_fsqm' ),
			'%SCOREPERCENTRAW%' => $percentage,
			'%DESIGNATION%' => __( 'N/A', 'ipt_fsqm' ),
			'%DESIGNATIONMSG%' => '',
			'%TRACK_ID%' => $this->get_trackback_id(),
			'%PORTAL%' => $this->get_utrackback_url(),
			'%ID%' => $this->data_id,
			'%SUBMISSION_ID%' => $this->data_id,
			'%FORMATTED_ID%' => sprintf( $this->settings['summary']['id_format'], $this->data_id, date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $this->data->date ) ), date( $this->settings['summary']['id_dt_format'], strtotime( $this->data->date ) ) ),
			'%FORMNAME%' => $this->name,
			'%SITENAME%' => get_bloginfo( 'name' ),
			'%RETRY_LINK%' => $this->get_repayment_url(),
		);

		// add layout/page scores
		$page_num = 1;
		foreach ( $this->layout as $layout ) {
			$page_score = $this->get_layout_score( $layout );
			$format_string_components[ "%SCORE_PAGE_{$page_num}%" ] = $page_score[1] . '/' . $page_score[0];
			$format_string_components[ "%OSCORE_PAGE_{$page_num}%" ] = $page_score[1];
			$format_string_components[ "%MSCORE_PAGE_{$page_num}%" ] = $page_score[0];
			$format_string_components[ "%SCOREPERCENT_PAGE_{$page_num}%" ] = number_format_i18n( (float) $page_score[2], $this->settings['ranking']['precision'] ) . __( '%', 'ipt_fsqm' );
			$format_string_components[ "%SCOREPERCENTRAW_PAGE_{$page_num}%" ] = $page_score[2];
			$page_num = $page_num + 1;
		}

		// Loop through and find the designation
		if ( $this->settings['ranking']['enabled'] == true && $percentage !== null ) {
			// Implement raw score based designation
			// #102
			// If percentage based
			if ( $this->settings['ranking']['rtype'] == 'percentage' ) {
				foreach ( $this->settings['ranking']['ranks'] as $r_key => $rank ) {
					if ( $percentage <= $rank['max'] && $percentage >= $rank['min'] ) {
						$format_string_components['%DESIGNATION%'] = $rank['title'];
						$format_string_components['%DESIGNATIONMSG%'] = str_replace( array_keys( $format_string_components ), array_values( $format_string_components ), $rank['msg'] );
						break;
					}
				}
			// If raw score based
			} else {
				foreach ( $this->settings['ranking']['rranks'] as $rr_key => $rrank ) {
					if ( $this->data->score <= $rrank['max'] && $this->data->score >= $rrank['min'] ) {
						$format_string_components['%DESIGNATION%'] = $rrank['title'];
						$format_string_components['%DESIGNATIONMSG%'] = str_replace( array_keys( $format_string_components ), array_values( $format_string_components ), $rrank['msg'] );
						break;
					}
				}
			}
		}

		// Now get all mathematical elements and add to format string
		// Do it only if the settings say so
		if ( $this->settings['format']['math_format'] == true ) {
			$mathematical_elements = $this->get_keys_from_layouts_by_types( 'mathematical', $this->layout );

			if ( ! empty( $mathematical_elements ) ) {
				foreach ( $mathematical_elements as $math_key ) {
					if ( isset( $this->data->freetype[$math_key] ) ) {
						$format_string_components['%MATH' . $math_key . '%'] = $this->data->freetype[$math_key]['value'];
					}
				}
			}
		}


		return apply_filters( 'ipt_fsqm_format_strings', $format_string_components, $this, $this->form_id, $this->data_id );
	}

	public function email( $emails ) {
		if ( !is_array( $emails ) || empty( $emails ) ) {
			return;
		}
		foreach ( $emails as $email => $data ) {
			$email = trim( $email );
			if ( ! is_email( $email ) ) {
				continue;
			}
			// Prep the msg
			$msgs = $data['msgs'];
			$msgs = $this->get_email_formatted_html( $msgs, $data['title'] );

			// Prep the header
			$header = array( 'Content-Type: text/html; charset=UTF-8' );
			if ( isset( $data['from'] ) && is_email( $data['from'][1] ) ) {
				$this->reply_to = $data['from'];
				add_action( 'phpmailer_init', array( $this, 'phpmailer_replyto' ) );
				add_action( 'wp_mail_from', array( $this, 'wp_mail_from' ) );
				add_action( 'wp_mail_from_name', array( $this, 'wp_mail_from_name' ) );
			}
			if ( isset( $data['cc'] ) && is_email( $data['cc'] ) ) {
				$header[] = 'CC: ' . $data['cc'];
			}

			// Insert other headers
			if ( isset( $data['headers'] ) && '' !== $data['headers'] ) {
				// Get format strings
				$format_string_components = $this->get_format_string();
				// Replace for cross platform compatibility (Windows)
				$data['headers'] = str_replace( "\r\n", "\n", $data['headers'] );
				// Now separate every line and construct the headers
				$headers = explode( "\n", $data['headers'] );
				// Now iterate and set the format strings
				foreach ( $headers as $h_key => $hv ) {
					$headers[ $h_key ] = str_replace( array_keys( $format_string_components ), array_values( $format_string_components ), $hv );
				}
				$header = array_merge( $header, $headers );
			}

			// Prep the attachment
			$attachment = array();
			if ( isset( $data['attachment'] ) ) {
				$attachment = $data['attachment'];
			}

			// Check for SMTP
			// Assumes that a from path is set which will be used as from and from address
			if ( isset( $data['smtp'] ) && $data['smtp'] == true ) {
				$this->smtp_conf = $data['smtp_conf'];

				// We expect the password to be encrypted
				if ( $this->smtp_conf['password'] != '' ) {
					$this->smtp_conf['password'] = $this->decrypt( $this->smtp_conf['password'] );
				}

				// Compat with easy smtp
				if ( function_exists( 'easy_wp_smtp' ) ) {
					remove_action( 'phpmailer_init', 'easy_wp_smtp' );
				}

				// Add our action
				add_action( 'phpmailer_init', array( $this, 'phpmailer_smtp' ) );
			}

			if ( WP_DEBUG !== true ) {
				$msgs = IPT_FSQM_Minify_HTML::minify( $msgs );
			}

			// Mail it
			wp_mail(
				$email,
				$data['title'],
				$msgs,
				$header,
				$attachment
			);

			// Remove our filters
			if ( isset( $data['from'] ) ) {
				$this->reply_to = array();
				remove_action( 'phpmailer_init', array( $this, 'phpmailer_replyto' ) );
				remove_action( 'wp_mail_from', array( $this, 'wp_mail_from' ) );
				remove_action( 'wp_mail_from_name', array( $this, 'wp_mail_from_name' ) );
			}

			if ( isset( $data['smtp'] ) && $data['smtp'] == true ) {
				// Compat with easy smtp
				if ( function_exists( 'easy_wp_smtp' ) ) {
					add_action( 'phpmailer_init', 'easy_wp_smtp' );
				}

				// Remove our action
				remove_action( 'phpmailer_init', array( $this, 'phpmailer_smtp' ) );
				$this->smtp_conf = array();
			}
		}
	}

	public function get_email_formatted_html( $msgs, $title ) {
		if ( is_array( $msgs ) ) {
			$msgs = implode( '<br /><br />', $msgs );
		}

		// prepare the data
		$data = [
			'title' => $title,
			'body' => $msgs,
			'style' => $this->settings['email_template'],
		];

		$email_template = $this->get_email_template_path( 'html-body' );
		ob_start();
		include $email_template;
		return ob_get_clean();
	}

	public function phpmailer_replyto( $phpmailer ) {
		if ( empty( $this->reply_to ) || ! is_array( $this->reply_to ) ) {
			return;
		}
		$add_reply_to_only = false;
		if ( isset( $this->reply_to[2] ) && true === $this->reply_to[2] ) {
			$add_reply_to_only = true;
		}

		if ( ! $add_reply_to_only ) {
			$phpmailer->From = $this->reply_to[1];
			$phpmailer->FromName = $this->reply_to[0];
		}

		$phpmailer->AddReplyTo( $this->reply_to[1], $this->reply_to[0] );

		// Check if this is non smtp
		if ( empty( $this->smtp_conf ) ) {
			// Add the sender header - WordPress way
			// Get the site domain and get rid of www.
			$sitename = strtolower( $_SERVER['SERVER_NAME'] );
			if ( substr( $sitename, 0, 4 ) == 'www.' ) {
				$sitename = substr( $sitename, 4 );
			}

			$sender_email = 'wordpress@' . $sitename;
			$phpmailer->addCustomHeader( 'Sender: <' . $sender_email . '>' );
		}
	}

	public function wp_mail_from( $from_email ) {
		if ( ! empty( $this->reply_to ) && is_array( $this->reply_to ) ) {
			return $this->reply_to[1];
		}

		return $from_email;
	}

	public function wp_mail_from_name( $from_name ) {
		if ( ! empty( $this->reply_to ) && is_array( $this->reply_to ) ) {
			return $this->reply_to[0];
		}

		return $from_name;
	}

	public function phpmailer_smtp( $phpmailer ) {
		if ( ! isset( $this->smtp_conf['host'] ) || empty( $this->smtp_conf['host'] ) ) {
			return;
		}
		$phpmailer->Sender = $this->reply_to[1];
		$phpmailer->Mailer = 'smtp';
		$phpmailer->Host = $this->smtp_conf['host'];
		$phpmailer->SMTPSecure = $this->smtp_conf['enc_type'];
		$phpmailer->Port = $this->smtp_conf['port'];
		$phpmailer->SMTPAuth = true;
		$phpmailer->Username = $this->smtp_conf['username'];
		$phpmailer->Password = $this->smtp_conf['password'];
	}

	/*==========================================================================
	 * Internal APIs - Also Public
	 *========================================================================*/
	public function get_trackback_url() {
		global $ipt_fsqm_settings;
		$query = urlencode( $this->get_trackback_id() );
		return add_query_arg( 'id', $query, get_permalink( $ipt_fsqm_settings['track_page'] ) );
	}

	public function get_email_url() {
		$query = urlencode( $this->get_trackback_id() );
		return add_query_arg( 'email_id', $query, home_url( '/eform-email/' ) );
	}

	public function get_payment_email_url( $payment_info = array() ) {
		$query = array(
			'payment_id' => urlencode( $this->get_trackback_id() ),
		);
		if ( isset( $payment_info['retry'] ) ) {
			$query['retry'] = $payment_info['retry'];
		}
		if ( isset( $payment_info['mode'] ) ) {
			$query['mode'] = $payment_info['mode'];
		}
		if ( isset( $payment_info['success'] ) ) {
			$query['success'] = $payment_info['success'];
		}
		if ( isset( $payment_info['cancelled'] ) ) {
			$query['cancelled'] = $payment_info['cancelled'];
		}
		if ( isset( $payment_info['invoice'] ) ) {
			$query['invoice'] = $payment_info['invoice'];
		}
		return add_query_arg( $query, home_url( '/eform-payment/' ) );
	}

	public function get_utrackback_url() {
		global $ipt_fsqm_settings;
		return esc_url( get_permalink( $ipt_fsqm_settings['utrack_page'] ) );
	}

	public function get_trackback_id() {
		return IPT_FSQM_Form_Elements_Static::get_trackback_id( $this->data_id );
	}

	public function get_edit_url() {
		return add_query_arg( 'action', 'edit', $this->get_trackback_url() );
	}

	public function get_repayment_url() {
		if (
			true == $this->settings['payment']['woocommerce']['enabled']
			&& false !== $this->get_woo_product_id()
		) {
			return get_permalink( get_option( 'woocommerce_checkout_page_id' ) );
		}
		return add_query_arg( array(
			'action' => 'payment',
			'mode' => 'retry',
		), $this->get_trackback_url() );
	}

	public function compat_data() {
		if ( null == $this->data ) {
			$this->prepare_empty_data();
		} else {
			//check for older format

			if ( empty( $this->data->mcq ) || !is_array( $this->data->mcq ) ) {
				$this->prepare_empty_data_mcq();
			} else {
				$m_keys = array_keys( (array) $this->data->mcq );
				if ( is_string( $this->data->mcq[$m_keys[0]] ) || ( is_array( $this->data->mcq[$m_keys[0]] ) && !isset( $this->data->mcq[$m_keys[0]]['type'] ) ) ) {
					$this->compat_data_mcq();
				}
			}

			if ( empty( $this->data->pinfo ) ) {
				$this->prepare_empty_data_pinfo();
			} else {
				$p_keys = array_keys( (array) $this->data->pinfo );
				if ( is_string( $this->data->pinfo[$p_keys[0]] ) || ( is_array( $this->data->pinfo[$p_keys[0]] ) && !isset( $this->data->pinfo[$p_keys[0]]['type'] ) ) ) {
					$this->compat_data_pinfo();
				}
			}

			if ( empty( $this->data->freetype ) ) {
				$this->prepare_empty_data_freetype();
			} else {
				$f_keys = array_keys( (array) $this->data->freetype );
				if ( is_string( $this->data->freetype[$f_keys[0]] ) || ( is_array( $this->data->freetype[$f_keys[0]] ) && !isset( $this->data->freetype[$f_keys[0]]['type'] ) ) ) {
					$this->compat_data_freetype();
				}
			}
		}
	}

	public function get_submission_from_data( $layout_element ) {
		$return = null;
		if ( isset( $this->data->{$layout_element['m_type']} ) && null !== $this->data->{$layout_element['m_type']} ) {
			if ( isset( $this->data->{$layout_element['m_type']}[$layout_element['key']] ) ) {
				$return = $this->data->{$layout_element['m_type']}[$layout_element['key']];
			}
		}
		return $return;
	}

	public function validate_data_against_element( $element, $data, $key ) {

		//First check for data tamper
		if ( !isset( $data['type'] ) || !isset( $data['m_type'] ) || $data['type'] != $element['type'] || $data['m_type'] != $element['m_type'] ) {
			return array(
				'data_tampering' => true,
				'required_validation' => false,
				'errors' => array( __( 'Type mismatch', 'ipt_fsqm' ) ),
			);
		}

		// Init the variables to get data structure
		$validation_result = array();


		// At this point first check if the item was conditionally hidden
		// If it was hidden, then blank out the data
		// and waive the validations
		// Addresses issue #9
		// @link https://iptlabz.com/ipanelthemes/wp-fsqm-pro/issues/9
		if ( false == $this->validate_data_against_conditional_logic( $element, $key ) ) {
			$validation_result = array(
				'data_tampering'      => false, // No tampering
				'required_validation' => true, // Passes required validation
				'errors'              => array(), // No errors
				'conditional_hidden'  => true, // It is conditionally hidden
				'data'                => $this->get_submission_structure( $element['type'] ), // Blank out the data
			);
		// The element is shown so proceed as it would
		} else {
			// Merge the POST data with submission structure
			$data = $this->merge_elements( $data, $this->get_submission_structure( $element['type'] ) );
			// Special Case WP CORE Register
			if ( true == $this->settings['core']['reg']['enabled'] && ! empty( $this->settings['core']['reg']['meta'] ) && true == $this->settings['core']['reg']['hide_meta'] ) {
				// We need to waive the required validation for meta elements
				foreach ( $this->settings['core']['reg']['meta'] as $regmeta ) {
					if ( $element['m_type'] == $regmeta['m_type'] && $key == $regmeta['key'] ) {
						$element['validation']['required'] = false;
					}
				}
			}
			// Create the $param array
			$param = array( $element, $data, $key );

			// Now pass to the validation
			// Check if callback is defined in element definition
			if ( isset( $this->elements[$element['m_type']]['elements'][$element['type']]['callback_data_validation'] ) ) {
				$validation_result = call_user_func_array( $this->elements[$element['m_type']]['elements'][$element['type']]['callback_data_validation'], $param );
			// Not defined, so check if a method exists in this class
			} else {
				if ( method_exists( $this, 'validate_data_against_' . $element['type'] ) ) {
					$validation_result = call_user_func_array( array( $this, 'validate_data_against_' . $element['type'] ), $param );
				} else {
					$validation_result = array(
						'data_tampering'      => false,
						'required_validation' => true,
						'errors'              => array(),
						'conditional_hidden'  => false,
						'data'                => $data,
					);
				}
			}
			$validation_result['conditional_hidden'] = false;
		}

		// But override if a force submission may occur
		if ( $this->settings['timer']['time_limit_type'] != 'none' ) {
			// Reset if invalid data has occured
			if ( $validation_result['required_validation'] != true || !empty( $validation_result['errors'] ) ) {
				$validation_result['required_validation'] = true;
				$validation_result['errors'] = array();
				$validation_result['data'] = $this->get_submission_structure( $element['type'] );
			}
		}

		return $validation_result;
	}

	public function reset_conditional_blacklists() {
		// This variable is for droppable design elements which is conditionally hidden
		// We will loop through all design elements and force blacklist those whose parents are hidden conditionally
		$this->conditional_hidden_blacklist = array(
			'layout'   => array(),
			'design'   => array(),
			'mcq'      => array(),
			'freetype' => array(),
			'pinfo'    => array(),
		);
		// This variable is to cache the conditional validation checks so that
		// even if multiple checks occur, it doesn't eat up too much of memory
		$this->conditional_validation_cache = array();
	}

	public function blacklist_conditional_hiddens() {
		// First loop through all layout elements
		foreach ( $this->layout as $l_key => $layout ) {
			$this->check_conditional_for_nested_elements( $layout, $l_key );
		}
		unset( $l_key, $layout );

		// First loop through all design elements and
		// blacklist those other elements whose parents are hidden
		foreach ( $this->design as $d_key => $design ) {
			$design_element_definition = $this->get_element_definition( array(
				'm_type' => $design['m_type'],
				'type' => $design['type'],
			) );
			// No need to test for non-droppable elements
			if ( ! isset( $design_element_definition['droppable'] ) || $design_element_definition['droppable'] == false ) {
				continue;
			}
			// Call the recursive function
			// It will check subsequently for other nested elements
			$this->check_conditional_for_nested_elements( $design, $d_key );
		}
		// Unset for later use
		unset( $d_key, $design );
	}

	public function check_conditional_for_nested_elements( $droppable_element, $key ) {
		// Don't do anything if no other nested element
		if ( ! isset( $droppable_element['elements'] ) || empty( $droppable_element['elements'] ) ) {
			return;
		}

		if ( $droppable_element['m_type'] == 'layout' ) {
			$element = $droppable_element;
		} else {
			$element = $this->get_element_from_layout( array(
				'm_type' => $droppable_element['m_type'],
				'key' => $key,
			) );
		}

		// Now check the conditional state
		$conditional_state = $this->validate_data_against_conditional_logic( $element, $key );

		// Don't do anything if the return state is true (ie, shown)
		if ( $conditional_state == true ) {
			return;
		}

		// First add this to blacklist
		if ( isset( $this->conditional_hidden_blacklist[$droppable_element['m_type']] ) ) {
			if ( ! in_array( $key, $this->conditional_hidden_blacklist[$droppable_element['m_type']]) ) {
				$this->conditional_hidden_blacklist[$droppable_element['m_type']][] = (string) $key;
			}
		}
		// Now loop through all elements inside it and blacklist them as hidden
		foreach ( $droppable_element['elements'] as $elem ) {
			// Recursively blacklist all elements
			$this->blacklist_recursive_elements( $elem );
		}
	}

	public function blacklist_recursive_elements( $elem ) {
		if ( isset( $this->conditional_hidden_blacklist[$elem['m_type']] ) ) {
			if ( ! in_array( $elem['key'], $this->conditional_hidden_blacklist[$elem['m_type']] ) ) {
				$this->conditional_hidden_blacklist[$elem['m_type']][] = (string) $elem['key'];
				// Also reset the data
				$this->data->{$elem['m_type']}[$elem['key']] = $this->get_submission_structure( $elem['type'] );
			}
		}
		$element = $this->get_element_from_layout( $elem );
		if ( isset( $element['elements'] ) && is_array( $element['elements'] ) && ! empty( $element['elements'] ) ) {
			foreach ( $element['elements'] as $nelem ) {
				$this->blacklist_recursive_elements( $nelem );
			}
		}
	}

	/**
	 * Validates an element against it's conditional logic
	 *
	 * The return value doesn't mean whether the element's conditions are
	 * satisfied The approach is made simpler by returing true if the element is
	 * supposed to be shown and returning false is the element is supposed to be
	 * hidden
	 *
	 * @param      array    $element
	 * @param      int      $elem_key  The key of the element, used for checking
	 *                                 inside the blacklist
	 *
	 * @return     boolean  true if the element is shown, false if the element is hidden
	 */
	public function validate_data_against_conditional_logic( $element, $elem_key ) {
		// If element is nothing
		if ( empty( $element ) ) {
			return false;
		}

		// First check if is already blacklisted
		if ( isset( $this->conditional_hidden_blacklist[$element['m_type']] ) ) {
			if ( in_array( (string) $elem_key, $this->conditional_hidden_blacklist[$element['m_type']] ) ) {
				// It is blacklisted, so return false (ie, hidden because somehow it's parent is also hidden)
				$this->conditional_validation_cache[$element['m_type']]["$elem_key"] = false;
				return false;
			}
		}

		// Check if the valus is cached
		if ( isset( $this->conditional_validation_cache[$element['m_type']] ) ) {
			if ( isset( $this->conditional_validation_cache[$element['m_type']][$elem_key] ) ) {
				if ( $this->conditional_validation_cache[$element['m_type']][$elem_key] == false ) {
					$this->conditional_hidden_blacklist[$element['m_type']][] = $elem_key;
				}
				// If so, return it
				return $this->conditional_validation_cache[$element['m_type']][$elem_key];
			}
		}

		// If no conditional is set, then it is always shown
		if ( ! isset( $element['conditional'] ) || $element['conditional']['active'] == false || empty( $element['conditional']['logic'] ) ) {
			$this->conditional_validation_cache[$element['m_type']]["$elem_key"] = true;
			return true;
		}


		$return_val = $this->validate_logic_state( $element['conditional']['logic'] ); // To see if everything checks out

		if ( $return_val ) { // All conditions checks out
			$this->conditional_validation_cache[$element['m_type']]["$elem_key"] = $element['conditional']['change'];
		} else { // Initial status
			$this->conditional_validation_cache[$element['m_type']]["$elem_key"] = $element['conditional']['status'];
		}

		// Blacklist
		if ( $this->conditional_validation_cache[$element['m_type']][$elem_key] == false ) {
			$this->conditional_hidden_blacklist[$element['m_type']][] = $elem_key;
		}

		return $this->conditional_validation_cache[$element['m_type']]["$elem_key"];
	}

	/**
	 * Validates the state of given logic
	 *
	 * @param      array  $logics  Associative array of logics
	 * @return     bool true of all logic checks out false if fails
	 */
	public function validate_logic_state( $logics ) {

		$relation_check = array();
		$relation_operator = array();

		// Now loop through all logic and check to see if it holds
		foreach ( $logics as $logic_key => $logic ) {
			$cond_elem = $this->get_element_from_layout( array(
				'm_type' => $logic['m_type'],
				'key' => $logic['key'],
			) );

			if ( empty( $cond_elem ) ) {
				continue;
			}

			$cond_data = $this->merge_elements( $this->get_submission_from_data( array(
				'm_type' => $logic['m_type'],
				'key' => $logic['key'],
			) ), $this->get_submission_structure( $cond_elem['type'] ) );

			// What if the logic element, itself is hidden?
			// We wouldn't know unless we do a conditional check on the logic element first
			$this->validate_data_against_conditional_logic( $cond_elem, $logic['key'] );
			// Then simply treat as if the cond_data is emptu
			if ( isset( $this->conditional_hidden_blacklist[$logic['m_type']] ) ) {
				if ( in_array( (string) $logic['key'], $this->conditional_hidden_blacklist[$logic['m_type']] ) ) {
					$cond_data = $this->get_submission_structure( $cond_elem['type'] );
				}
			}


			if ( null == $cond_data || empty( $cond_data ) ) {
				continue;
			}

			// Now switch and check
			$check_against = null;
			switch ( $cond_data['type'] ) {
				case 'radio' :
				case 'p_radio' :
				case 'checkbox' :
				case 'p_checkbox' :
				case 'select' :
				case 'p_select' :
				case 'thumbselect' :
					$check_against = array();
					foreach ( $cond_data['options'] as $o_key ) {
						if ( $o_key === 'others' ) {
							$check_against[] = $cond_elem['settings']['o_label'];
						} else {
							// Special case for select dropdown
							if ( '' == $o_key && in_array( $cond_data['type'], array( 'select', 'p_select' ) ) ) {
								if ( '' != $cond_elem['settings']['e_label'] ) {
									$check_against[] = $cond_elem['settings']['e_label'];
								}
							} else if ( isset( $cond_elem['settings']['options'][ $o_key ] ) ) {
								$check_against[] = $cond_elem['settings']['options'][ $o_key ]['label'];
							}
						}
					}
					break;

				case 'pricing_table' :
					$check_against = array();
					foreach ( $cond_data['options'] as $o_key ) {
						if ( isset( $cond_elem['settings']['options'][ $o_key ] ) ) {
							$check_against[] = $cond_elem['settings']['options'][ $o_key ]['label'];
						}
					}
					break;

				case 'slider' :
					$check_against = $cond_data['value'];
					break;

				case 'range' :
					$check_against = array(
						$cond_data['values']['min'], $cond_data['values']['max'],
					);
					// If type is length
					// Then we make length = 1 if both are not at minimum
					// Otherwise length = 0
					if ( 'val' !== $logic['check'] ) {
						$check_against = 0;
						if ( $cond_elem['settings']['min'] != $cond_data['values']['min']
							&& $cond_elem['settings']['min'] != $cond_data['values']['max'] ) {
							$check_against = 1;
						}
					}
					break;

				case 'spinners' :
				case 'starrating' :
				case 'scalerating' :
					$check_against = array();
					foreach( $cond_data['options'] as $oval ) {
						if ( $oval != '' ) {
							$check_against[] = $oval;
						}
					}
					break;

				case 'grading' :
					$check_against = array();
					foreach ( $cond_data['options'] as $oval ) {
						if ( is_array( $oval ) ) {
							if ( $oval['min'] != '' ) {
								$check_against[] = $oval['min'];
							}
							if ( $oval['max'] != '' ) {
								$check_against[] = $oval['max'];
							}
						} else {
							if ( $oval != '' ) {
								$check_against[] = $oval;
							}
						}
					}
					break;

				case 'matrix' :
					$check_against = array();
					foreach ( $cond_data['rows'] as $cols ) {
						foreach ( $cols as $c_key ) {
							$label = $cond_elem['settings']['columns'][$c_key];
							$check_against[] = $label;
						}
					}
					break;

				case 'toggle' :
				case 's_checkbox' :
					$check_against = ( ! isset( $cond_data['value'] ) || empty( $cond_data['value'] ) || $cond_data['value'] == null ) ? '0' : '1';
					$logic['value'] = absint( $logic['value'] );
					break;

				case 'smileyrating' :
					$smiley_vals = array(
						'frown' => '1',
						'sad' => '2',
						'neutral' => '3',
						'happy' => '4',
						'excited' => '5',
					);
					if ( $cond_data['option'] != '' && isset( $smiley_vals[$cond_data['option']] ) ) {
						$check_against = $smiley_vals[$cond_data['option']];
					}
					$logic['value'] = absint( $logic['value'] );
					break;

				case 'likedislike' :
					if ( $cond_data['value'] == 'like' ) {
						$check_against = '1';
					} elseif ( $cond_data['value'] == 'dislike' ) {
						$check_against = '0';
					}
					$logic['value'] = absint( $logic['value'] );
					break;

				case 'matrix_dropdown' :
					$check_against = array();
					foreach ( (array) $cond_data['rows'] as $r_key => $row ) {
						foreach ( (array) $row as $c_key => $column ) {
							if ( isset( $cond_elem['settings']['options'][$column] ) ) {
								$check_against[] = $cond_elem['settings']['options'][$column]['label'];
							}
						}
					}
					// We remove the array unique because it would give the length improperly
					// #195
					// $check_against = array_unique( $check_against );
					break;

				case 'feedback_small' :
				case 'f_name' :
				case 'l_name' :
				case 'email' :
				case 'phone' :
				case 'p_name' :
				case 'p_email' :
				case 'p_phone' :
				case 'textinput' :
				case 'keypad' :
				case 'feedback_large' :
				case 'textarea' :
					$check_against = $cond_data['value'];
					break;

				// Password
				case 'password' :
					$check_against = $cond_data['value'];
					if ( isset( $cond_data['encrypted'] ) && $cond_data['encrypted'] ) {
						$check_against = $this->decrypt_password_field( $cond_data['value'] );
					}
					break;

				case 'upload' :
					$check_against = count( $cond_data['id'] );
					break;

				case 'mathematical' :
					$check_against = (float) $cond_data['value'];
					break;

				case 'address' :
					$check_against = array(
						$cond_data['recipient'],
						$cond_data['line_one'],
						$cond_data['line_two'],
						$cond_data['line_three'],
						$cond_data['country'],
					);
					break;

				case 'datetime' :
					$check_against = strtotime( $cond_data['value'] );
					$logic['value'] = strtotime( $logic['value'] );
					break;

				// Adding conditional logic for new elements
				// @see #228

				case 'feedback_matrix' :
					$check_against = array();
					foreach ( $cond_data['rows'] as $r_key => $columns ) {
						foreach ( $columns as $c_key => $col ) {
							$val = trim( $col );
							if ( '' !== $val ) {
								$check_against[] = $val;
							}
						}
					}
					break;

				case 'gps' :
					$check_against = array();
					foreach ( array( 'location_name', 'lat', 'long' ) as $key ) {
						if ( '' !== $cond_data[ $key ] ) {
							$check_against[] = $cond_data[ $key ];
						}
					}
					unset( $key );
					break;

				case 'signature' :
					$check_against = '0';
					if ( '' !== $cond_data['value'] && 'image/jsignature;base30,' !== $cond_data['value'] ) {
						$check_against = '1';
					}
					break;

				case 'payment' :
					$check_against = $cond_data['value'];
					if ( isset( $cond_data['couponval'] ) && '' !== $cond_data['couponval'] ) {
						$check_against = $cond_data['couponval'];
					}
					break;

				case 'hidden' :
					$check_against = $cond_data['value'];
					break;

				case 'guestblog' :
					$check_against = array();
					$check_against[] = $cond_data['title'];
					$check_against[] = $cond_data['value'];
					break;

				default :
					// Nothing needed here
					// Just silence
					// Which is golden ;)
					break;
			}

			$this_validated = false;
			$final_compare_against = null;
			$final_compare_with = strip_shortcodes( $logic['value'] );

			// Now strip shortcodes from possible labels
			if ( is_array( $check_against ) ) {
				$check_against = array_map( 'strip_shortcodes', $check_against );
				$check_against = array_map( 'strip_tags', $check_against );
			} else if ( is_string( $check_against ) ) {
				$check_against = strip_tags( strip_shortcodes( $check_against ) );
			}

			if ( $logic['check'] === 'val' ) {
				if ( is_array( $check_against ) ) {
					$final_compare_against = array();
					foreach ( $check_against as $ca ) {
						$final_compare_against[] = trim( strtolower( $ca ) );
					}
				} else {
					$final_compare_against = trim( strtolower( $check_against ) );
				}
				$final_compare_with = trim( strtolower( $final_compare_with ) );
			} else {
				$final_compare_against = is_array( $check_against )
					? count( $check_against )
					: (
							( is_float( $check_against ) || is_int( $check_against ) )
								? (float) $check_against
								: (float) strlen( $check_against )
						);
				$final_compare_with = (float) $final_compare_with;
			}

			$compare_against_array = is_array( $final_compare_against );

			switch ( $logic['operator'] ) {
				case 'eq':
					if ( $compare_against_array ) {
						foreach ( $final_compare_against as $value ) {
							if ( $value !== '' && $value == $final_compare_with ) {
								$this_validated = true;
								break;
							} elseif ( $value === '' && $final_compare_with === '' ) {
								$this_validated = true;
								break;
							}
						}
					} else {
						if ( $final_compare_against !== '' && $final_compare_against == $final_compare_with ) {
							$this_validated = true;
						} elseif ( $final_compare_against === '' && $final_compare_with === '' ) {
							$this_validated = true;
							break;
						}
					}
					break;

				case 'neq':
					if ( $compare_against_array ) {
						$this_validated = true;
						foreach ( $final_compare_against as $value ) {
							if ( $value !== '' && $value == $final_compare_with ) {
								$this_validated = false;
								break;
							}
						}
					} else {
						$this_validated = true;
						if ( $final_compare_against !== '' && $final_compare_against == $final_compare_with ) {
							$this_validated = false;
						}
					}
					break;

				case 'gt':
					if ( $compare_against_array ) {
						foreach ( $final_compare_against as $value ) {
							if ( $value > $final_compare_with ) {
								$this_validated = true;
								break;
							}
						}
					} else {
						if ( $final_compare_against > $final_compare_with ) {
							$this_validated = true;
						}
					}
					break;

				case 'lt':
					if ( $compare_against_array ) {
						foreach ( $final_compare_against as $value ) {
							if ( $value < $final_compare_with ) {
								$this_validated = true;
								break;
							}
						}
					} else {
						if ( $final_compare_against < $final_compare_with ) {
							$this_validated = true;
						}
					}
					break;

				case 'ct':
					if ( $compare_against_array ) {
						// A special case for range
						if ( 'range' == $cond_data['type'] ) {
							$this_validated = false;
							if ( $final_compare_with >= $final_compare_against[0] && $final_compare_with <= $final_compare_against[1] ) {
								$this_validated = true;
							}
						} else {
							foreach ( $final_compare_against as $value ) {
								if ( $value !== '' && strstr( $value, $final_compare_with ) !== FALSE ) {
									$this_validated = true;
									break;
								}
							}
						}
					} else {
						if ( $final_compare_against !== '' && strstr( $final_compare_against, $final_compare_with ) !== FALSE ) {
							$this_validated = true;
						}
					}
					break;

				case 'dct':
					if ( $compare_against_array ) {
						// Special case for range
						if ( 'range' == $cond_data['type'] ) {
							$this_validated = false;
							if ( $final_compare_with < $final_compare_against[0] || $final_compare_with > $final_compare_against[1] ) {
								$this_validated = true;
							}
						} else {
							$this_validated = true;
							foreach ( $final_compare_against as $value ) {
								if ( $value !== '' && strstr( $value, $final_compare_with ) !== FALSE ) {
									$this_validated = false;
									break;
								}
							}
						}
					} else {
						$this_validated = true;
						if ( $final_compare_against !== '' && strstr( $final_compare_against, $final_compare_with ) !== FALSE ) {
							$this_validated = false;
						}
					}
					break;

				case 'sw':
					if ( $compare_against_array ) {
						foreach ( $final_compare_against as $value ) {
							if ( preg_match( '/^' . preg_quote( $final_compare_with, '/' ) . '/im', $value ) ) {
								$this_validated = true;
								break;
							}
						}
					} else {
						if ( preg_match( '/^' . preg_quote( $final_compare_with, '/' ) . '/im', $final_compare_against ) ) {
							$this_validated = true;
						}
					}
					break;

				case 'ew':
					if ( $compare_against_array ) {
						foreach ( $final_compare_against as $value ) {
							if ( preg_match( '/' . preg_quote( $final_compare_with, '/' ) . '$/im', $value ) ) {
								$this_validated = true;
								break;
							}
						}
					} else {
						if ( preg_match( '/' . preg_quote( $final_compare_with, '/' ) . '$/im', $final_compare_against ) ) {
							$this_validated = true;
						}
					}
					break;

				default:
					$this_validated = false;
					break;
			}

			$relation_check[$logic_key] = $this_validated;
			$relation_operator[$logic_key] = $logic['rel'];
		}

		// Now check individual if necessary
		$relation_check_against = null;
		$relation_check_operator = null;
		$relation_check_array = array();
		$relation_check_array_key = 0;
		foreach ( $relation_check as $logic_key => $val ) {
			if ( null === $relation_check_against ) {
				$relation_check_against = $val;
			} else {
				switch ( $relation_check_operator ) {
					case 'and':
						$relation_check_against = $relation_check_against && $val;
						break;

					case 'or' :
						$relation_check_array_key++;
						$relation_check_against = $val;
					default:
						# code...
						break;
				}
			}
			$relation_check_operator = $relation_operator[$logic_key];
			$relation_check_array[$relation_check_array_key] = $relation_check_against;
		}

		$return_val = null;
		foreach ( $relation_check_array as $group_result ) {
			if ( $return_val === null ) {
				$return_val = $group_result;
			} else {
				$return_val = $return_val || $group_result;
			}
		}

		return $return_val;
	}

	public function validate_data_against_captcha( $element, $data ) {
		$data_tampering = false;
		$required_validation = true;
		$errors = array();

		if ( trim( $data['hash'] ) == '' ) {
			$data_tampering = true;
		} else if ( $data['value'] == '' ) {
				$required_validation = false;
			} else {
			$value = $this->decrypt( $data['hash'] );
			if ( $value != $data['value'] ) {
				$errors[] = __( 'Security Captcha is invalid.', 'ipt_fsqm' );
			}
		}


		return array(
			'data_tampering' => $data_tampering,
			'required_validation' => $required_validation,
			'errors' => $errors,
			'data' => $data,
		);
	}

	public function validate_data_against_radio( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'options', 'options', $element );
	}

	public function validate_data_against_checkbox( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'options', 'options', $element );
	}

	public function validate_data_against_thumbselect( $element, $data ) {
		if ( $element['settings']['multiple'] == false ) {
			unset( $element['validation']['filters'] );
		}
		return $this->validate_and_sanitize_data_against_element( $data, 'options', 'options', $element );
	}

	public function validate_data_against_pricing_table( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'options', 'options', $element );
	}

	public function validate_data_against_select( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'options', 'options', $element );
	}

	public function validate_data_against_slider( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'slider', 'value', $element );
	}

	public function validate_data_against_range( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'range', 'values', $element );
	}

	public function validate_data_against_spinners( $element, $data ) {
		$element['validation']['filters']['type'] = 'number';
		return $this->validate_and_sanitize_data_against_element( $data, 'grading', 'options', $element );
	}

	public function validate_data_against_grading( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'grading', 'options', $element );
	}

	public function validate_data_against_starrating( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'ratings', 'options', $element );
	}

	public function validate_data_against_scalerating( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'ratings', 'options', $element );
	}

	public function validate_data_against_likedislike( $element, $data ) {
		$required_validation = true;
		$data_tampering = false;
		$errors = array();

		if ( $element['type'] !== $data['type'] || $element['m_type'] !== $data['m_type'] ) {
			$data_tampering = true;
		}

		if ( empty( $data['value'] ) && $element['validation']['required'] == true ) {
			$required_validation = false;
		}

		return array(
			'required_validation' => $required_validation,
			'data_tampering' => $data_tampering,
			'errors' => $errors,
			'data' => $data,
		);
	}

	public function validate_data_against_smileyrating( $element, $data ) {
		$required_validation = true;
		$data_tampering = false;
		$errors = array();

		if ( $element['type'] !== $data['type'] || $element['m_type'] !== $data['m_type'] ) {
			$data_tampering = true;
		}

		if ( empty( $data['option'] ) && $element['validation']['required'] == true ) {
			$required_validation = false;
		}

		return array(
			'required_validation' => $required_validation,
			'data_tampering' => $data_tampering,
			'errors' => $errors,
			'data' => $data,
		);
	}

	public function validate_data_against_matrix( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'matrix', 'rows', $element );
	}

	public function validate_data_against_matrix_dropdown( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'matrix_dropdown', 'rows', $element );
	}

	public function validate_data_against_toggle( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'toggle', 'value', $element );
	}

	public function validate_data_against_sorting( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'sorting', 'order', $element );
	}

	public function validate_data_against_feedback_large( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		return $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
	}

	public function validate_data_against_feedback_small( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		// If registration is enabled
		$needs_reg_mods = $this->is_element_of_reg_field( 'freetype', $key, 'username_id' );
		// Force the validation to no-special-character
		if ( $needs_reg_mods ) {
			$element['validation']['required'] = false;
			$element['validation']['filters']['type'] = 'noSpecialCharacter';
		}
		$return = $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
		// Now waive the required validation
		// if registration is enabled and user is logged in
		// and this item is set as the username field
		if ( $needs_reg_mods && is_user_logged_in() ) {
			$return['required_validation'] = true;
		}
		return $return;
	}

	public function validate_data_against_feedback_matrix( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'matrix_feedback', 'rows', $element );
	}

	public function validate_data_against_f_name( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		// Bypass validation check if hidden for logged in users
		if ( true == $this->settings['core']['reg']['enabled'] && true == $this->settings['core']['reg']['hide_pinfo'] && is_user_logged_in() ) {
			$element['validation']['required'] = false;
		}
		return $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
	}

	public function validate_data_against_l_name( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		// Bypass validation check if hidden for logged in users
		if ( true == $this->settings['core']['reg']['enabled'] && true == $this->settings['core']['reg']['hide_pinfo'] && is_user_logged_in() ) {
			$element['validation']['required'] = false;
		}
		return $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
	}

	public function validate_data_against_gps( $element, $data, $key ) {
		// This is always a success
		$required_validation = true;
		$data_tampering = false;
		$errors = array();

		if ( $element['type'] !== $data['type'] || $element['m_type'] !== $data['m_type'] ) {
			$data_tampering = true;
		}

		return array(
			'required_validation' => $required_validation,
			'data_tampering' => $data_tampering,
			'errors' => $errors,
			'data' => $data,
		);
	}

	public function validate_data_against_upload( $element, $data, $key ) {
		$required_validation = true;
		$data_tampering = false;
		$errors = array();

		if ( $element['type'] !== $data['type'] || $element['m_type'] !== $data['m_type'] ) {
			$data_tampering = true;
		}

		if ( $element['settings']['max_number_of_files'] >= 1 && $element['validation']['required'] === true && empty( $data['id'] ) ) {
			$required_validation = false;
		}

		if ( $element['settings']['min_number_of_files'] > 1 && count( $data['id'] ) < $element['settings']['min_number_of_files'] ) {
			$errors[] = sprintf( __( 'At least %d files required.', 'ipt_fsqm' ), $element['settings']['min_number_of_files'] );
		}
		return array(
			'required_validation' => $required_validation,
			'data_tampering' => $data_tampering,
			'errors' => $errors,
			'data' => $data,
		);
	}

	public function validate_data_against_signature( $element, $data, $key ) {
		$required_validation = true;
		$data_tampering = false;
		$errors = array();

		if ( $element['type'] !== $data['type'] || $element['m_type'] !== $data['m_type'] ) {
			$data_tampering = true;
		}

		if ( $element['validation']['required'] == true && ( $data['value'] == '' || $data['value'] == 'image/jsignature;base30,' ) ) {
			$required_validation = false;
			$errors[] = __( '* Please sign here', 'ipt_fsqm' );
		}

		return array(
			'required_validation' => $required_validation,
			'data_tampering' => $data_tampering,
			'errors' => $errors,
			'data' => $data,
		);
	}

	public function validate_data_against_email( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		$element['validation']['filters'] = array(
			'type' => 'email',
		);
		// Bypass validation check if hidden for logged in users
		if ( true == $this->settings['core']['reg']['enabled'] && true == $this->settings['core']['reg']['hide_pinfo'] && is_user_logged_in() ) {
			$element['validation']['required'] = false;
		}
		return $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
	}

	public function validate_data_against_phone( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		$element['validation']['filters'] = array(
			'type' => 'phone',
		);
		return $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
	}

	public function validate_data_against_p_name( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		$element['validation']['filters'] = array(
			'type' => 'personName',
		);
		return $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
	}

	public function validate_data_against_p_email( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		$element['validation']['filters'] = array(
			'type' => 'email',
		);
		return $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
	}

	public function validate_data_against_p_phone( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		$element['validation']['filters'] = array(
			'type' => 'phone',
		);
		return $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
	}

	public function validate_data_against_textinput( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );

		// If registration is enabled
		$needs_reg_mods = $this->is_element_of_reg_field( 'pinfo', $key, 'username_id' );
		// Force the validation to no-special-character
		if ( $needs_reg_mods ) {
			$element['validation']['required'] = false;
			$element['validation']['filters']['type'] = 'noSpecialCharacter';
		}
		$return = $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
		// Now waive the required validation
		// if registration is enabled and user is logged in
		// and this item is set as the username field
		if ( $needs_reg_mods && is_user_logged_in() ) {
			$return['required_validation'] = true;
		}
		return $return;
	}

	public function validate_data_against_textarea( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		return $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
	}

	public function validate_data_against_guestblog( $element, $data, $key ) {
		$errors = array();
		$required_validation = true;

		// Check for required things
		// Check if value is present
		if ( '' == $data['value'] ) {
			$required_validation = false;
			$errors[] = __( 'Please write the article', 'ipt_fsqm' );
		}
		// Check if title is present
		if ( '' == $data['title'] ) {
			$required_validation = false;
			$errors[] = __( 'Please write down the title', 'ipt_fsqm' );
		}

		// Now sanitize
		$allowed_tags = wp_kses_allowed_html( 'post' );
		$data['value'] = wp_kses( $data['value'], $allowed_tags );
		$data['title'] = strip_tags( $data['title'] );
		$data['bio'] = strip_tags( $data['bio'] );
		$data['taxonomy'] = (array) $data['taxonomy'];
		foreach ( $data['taxonomy'] as $key => $txn ) {
			$data['taxonomy'][ $key ] = array_map( 'intval', $txn );
		}

		return array(
			'required_validation' => $required_validation,
			'data_tampering' => false,
			'errors' => $errors,
			'data' => $data,
		);
	}

	/**
	 * Encrypt password field value
	 *
	 * @param string $value The unencrypted value of the password field.
	 * @return string New encrypted value.
	 */
	protected function encrypt_password_field( $value ) {
		return IPT_FSQM_Form_Elements_Static::encrypt( $value, 'eform-pwd-enc-' . $this->form_id );
	}

	/**
	 * Decrypt password field value.
	 *
	 * @param string $value The encrypted value of the password field.
	 * @return string Decrypted value.
	 */
	protected function decrypt_password_field( $value ) {
		return IPT_FSQM_Form_Elements_Static::decrypt( $value, 'eform-pwd-enc-' . $this->form_id );
	}

	public function validate_data_against_password( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		$return = $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
		// Now waive the required validation
		// if registration is enabled and user is logged in
		// and this item is set as the username field
		$needs_reg_mods = $this->is_element_of_reg_field( 'pinfo', $key, 'password_id' );
		if ( $needs_reg_mods && is_user_logged_in() ) {
			$return['required_validation'] = true;
		}
		// Encrypt if needed
		if ( '' !== $return['data']['value'] ) {
			$return['data']['value'] = $this->encrypt_password_field( $return['data']['value'] );
			$return['data']['encrypted'] = true;
		} else {
			$return['data']['encrypted'] = false;
		}
		return $return;
	}

	public function validate_data_against_p_radio( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'options', 'options', $element );
	}

	public function validate_data_against_p_checkbox( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'options', 'options', $element );
	}

	public function validate_data_against_p_select( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'options', 'options', $element );
	}

	public function validate_data_against_s_checkbox( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 's_checkbox', 'value', $element );
	}

	public function validate_data_against_address( $element, $data, $key ) {
		foreach ( $data['values'] as $v_key => $val ) {
			$data['values'][$v_key] = strip_tags( $val );
		}
		return $this->validate_and_sanitize_data_against_element( $data, 'address', 'values', $element );
	}

	public function validate_data_against_keypad( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		return $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
	}

	public function validate_data_against_datetime( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		return $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
	}

	public function validate_data_against_p_sorting( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'sorting', 'order', $element );
	}

	public function validate_data_against_hidden( $element, $data, $key ) {
		// Just sanitize the value
		$data['value'] = strip_tags( $data['value'] );
		// No other validation is needed anyway
		return array(
			'required_validation' => true,
			'data_tampering' => false,
			'errors' => array(),
			'data' => $data,
		);
	}

	public function validate_data_against_repeatable( $element, $data, $key ) {
		// Loop through and normalize + sanitize submission data
		$sanitized_data = array();
		foreach ( $data['values'] as $i_key => $items ) {
			$i = 0;
			$sanitized_data[ $i_key ] = array();
			foreach ( $element['settings']['group'] as $g_key => $group ) {
				switch ( $group['type'] ) {
					case 'radio' :
						if ( isset( $items[ $g_key ] ) ) {
							$sanitized_data[ $i_key ][ $g_key ] = strip_tags( $items[ $g_key ] );
						} else {
							$sanitized_data[ $i_key ][ $g_key ] = '';
						}
						break;
					case 'checkbox' :
					case 'select' :
					case 'select_multiple' :
						if ( isset( $items[ $g_key ] ) ) {
							$sanitized_data[ $i_key ][ $g_key ] = array_map( 'strip_tags', $items[ $g_key ] );
						} else {
							$sanitized_data[ $i_key ][ $g_key ] = array();
						}
						break;
					case 'text' :
					case 'phone' :
					case 'url' :
					case 'email' :
					case 'number' :
					case 'integer' :
					case 'personName' :
					case 'password' :
					case 'textarea' :
					case 'date' :
					case 'datetime' :
					case 'time' :
						if ( isset( $items[ $g_key ] ) ) {
							$sanitized_data[ $i_key ][ $g_key ] = strip_tags( $items[ $g_key ] );
						} else {
							$sanitized_data[ $i_key ][ $g_key ] = '';
						}
						break;
				}
			}
		}
		// No other validation is needed anyway
		$data['values'] = $sanitized_data;
		return array(
			'required_validation' => true,
			'data_tampering' => false,
			'errors' => array(),
			'data' => $data,
		);
	}

	/**
	 * Validates all predefined elements and sanitizes data
	 *
	 * @param array   $data        The original user submitted data array
	 * @param string  $check_type  Type of checking
	 * @param string  $check_where The key to the $data array where the submission result is shown
	 * @param array   $element     The element to which the data would be validated
	 * @return An associative array with the following components
	 *                        		'required_validation' => $required_validation, //If true then it passes the required validation test
	 *                          	'data_tampering' => $data_tampering, //If true then the data has been tampered
	 *                        		'errors' => $errors, //If not empty then other errors were found
	 */
	public function validate_and_sanitize_data_against_element( $data, $check_type, $check_where, $element ) {
		$errors = array();
		$required_validation = true;
		$data_tampering = false;
		if ( !isset( $data[$check_where] ) || empty( $data[$check_where] ) ) {
			$required_validation = false;
		}


		$user_submitted = isset( $data[$check_where] ) ? $data[$check_where] : false;

		switch ( $check_type ) {
			case 'string' :
				if ( !is_string( $user_submitted ) ) {
					$data_tampering = true;
				} else {
					if ( '' == $user_submitted ) {
						$required_validation = false;
					} else {
						$required_validation = true;
					}
				}
				break;
			case 'options' :
				// If the submission is empty then cast it to array
				// Issue #10
				if ( empty( $user_submitted ) ) {
					$user_submitted = array();
				}
				if ( !is_array( $user_submitted ) ) {
					$data_tampering = true;
					$errors[] = __( 'Submitted Data is not an array.', 'ipt_fsqm' );
				} else {
					foreach ( $user_submitted as $key => $val ) {
						if ( preg_match( '/[0-9]+/', $val ) ) {
							if ( isset( $element['settings']['options'][(int) $val] ) ) {
								continue;
							} else {
								$data_tampering = true;
								$errors[] = __( 'Invalid Option.', 'ipt_fsqm' );
							}
						} else if ( $val == 'others' ) {
								if ( '' == $data['others'] ) {
									$errors[] = __( 'No opinion provided.', 'ipt_fsqm' );
								}
						} else if ( $val == '' ) {
							$required_validation = false;
						} else {
							$data_tampering = true;
							$errors[] = __( 'Invalid Submission Value', 'ipt_fsqm' );
						}
					}
					if ( !$data_tampering && empty( $errors ) && !empty( $user_submitted ) ) {
						$required_validation = true;
					}
				}
				break;
			case 'value' :
				if ( !is_string( $user_submitted ) ) {
					$data_tampering = true;
				} else if ( '' == $user_submitted ) {
					$required_validation = false;
				} else {
					$required_validation = true;
				}
				break;
			case 'slider' :
				if ( !is_string( $user_submitted ) && !is_int( $user_submitted ) && !is_float( $user_submitted ) ) {
					$data_tampering = true;
				} else if ( $user_submitted == '' || ( '' != $element['settings']['min'] && (float) $user_submitted < $element['settings']['min'] ) || ( '' != $element['settings']['max'] && (float) $user_submitted > $element['settings']['max'] ) ) {
						$data_tampering = true;
						$errors[] = sprintf( __( 'Out of range. Minimum allowed: %s, Maximum allowed: %s, Given: %s', 'ipt_fsqm' ), $element['settings']['min'], $element['settings']['max'], $user_submitted );
					} else {
					$required_validation = true;
				}
				break;
			case 'range' :
				if ( !is_array( $user_submitted ) || !isset( $user_submitted['min'] ) || !isset( $user_submitted['max'] ) ) {
					$data_tampering = true;
				} else if ( ( '' != $element['settings']['min'] && $user_submitted['min'] < $element['settings']['min'] ) || ( '' != $element['settings']['max'] && $user_submitted['max'] > $element['settings']['max'] ) ) {
						$errors[] = sprintf( __( 'Out of range. Minimum allowed: %s, Maximum allowed: %s, Given: %s, %s', 'ipt_fsqm' ), $element['settings']['min'], $element['settings']['max'], isset( $user_submitted['min'] ) ? $user_submitted['min'] : '', isset( $user_submitted['max'] ) ? $user_submitted['max'] : '' );
						$data_tampering = true;
					} else if ( $user_submitted['max'] == '' || $user_submitted['min'] == '' ) {
						$required_validation = false;
					} else {
					$required_validation = true;
				}
				break;
			case 'grading' :
				// If the submission is empty then cast it to array
				// Issue #10
				if ( empty( $user_submitted ) ) {
					$user_submitted = array();
				}
				if ( !is_array( $user_submitted ) ) {
					$data_tampering = true;
				} else {
					//Can either be array or string
					foreach ( $user_submitted as $key => $val ) {
						// Compat with v-2.5.0
						if ( ! is_array( $element['settings']['options']["$key"] ) ) {
							$element['settings']['options']["$key"] = array(
								'label' => $element['settings']['options']["$key"],
							);
						}
						foreach ( array( 'min', 'max', 'step' ) as $validator ) {
							if ( ! isset( $element['settings']['options']["$key"][$validator] ) || $element['settings']['options']["$key"][$validator] == '' ) {
								$element['settings']['options']["$key"][$validator] = $element['settings'][$validator];
							}
						}
						if ( is_string( $val ) || is_float( $val ) || is_int( $val ) ) {
							if ( ( '' != $element['settings']['options']["$key"]['min'] && $val < $element['settings']['options']["$key"]['min'] ) || ( '' != $element['settings']['options']["$key"]['max'] && $val > $element['settings']['options']["$key"]['max'] ) ) {
								if ( $element['type'] != 'spinners' ) {
									$data_tampering = true;
								}
								if ( $element['type'] != 'spinners' || '' != $val )
									$errors[] = sprintf( __( 'Out of range. Minimum allowed: %s, Maximum allowed: %s, Given: %s', 'ipt_fsqm' ), $element['settings']['options']["$key"]['min'], $element['settings']['options']["$key"]['max'], $val );
							} else if ( '' == $val ) {
									$required_validation = false;
								} else {
								$required_validation = true;
							}
						} else if ( is_array( $val ) ) {
								if ( !isset( $val['min'] ) || !isset( $val['max'] ) || ( '' != $element['settings']['options']["$key"]['min'] && $val['min'] < $element['settings']['options']["$key"]['min'] ) || ( '' != $element['settings']['options']["$key"]['max'] && $val['max'] > $element['settings']['options']["$key"]['max'] ) ) {
									$data_tampering = true;
									$errors[] = sprintf( __( 'Out of range. Minimum allowed: %s, Maximum allowed: %s, Given: %s, %s', 'ipt_fsqm' ), $element['settings']['options']["$key"]['min'], $element['settings']['options']["$key"]['max'], isset( $val['min'] ) ? $val['min'] : '', isset( $val['max'] ) ? $val['max'] : '' );
								} else {
									$required_validation = true;
								}
							} else {
							$data_tampering = true;
						}
					}
				}
				break;
			case 'ratings' :
				// If the submission is empty then cast it to array
				// Issue #10
				if ( empty( $user_submitted ) ) {
					$user_submitted = array();
				}
				if ( !is_array( $user_submitted ) ) {
					$data_tampering = true;
				} else {
					//Can either be array or string
					foreach ( $user_submitted as $key => $val ) {
						if ( is_string( $val ) || is_float( $val ) || is_int( $val ) ) {
							if ( ( '' != $element['settings']['max'] && $val > $element['settings']['max'] ) ) {
								$data_tampering = true;
								$errors[] = sprintf( __( 'Out of range. Maximum allowed: %1$s, Given: %2$s', 'ipt_fsqm' ), $element['settings']['max'], $val );
							} else if ( '' == $val ) {
									$required_validation = false;
								} else {
								$required_validation = true;
							}
						} else {
							$data_tampering = true;
						}
					}
				}
				break;
			case 'matrix' :
				// If the submission is empty then cast it to array
				// Issue #10
				if ( empty( $user_submitted ) ) {
					$user_submitted = array();
				}
				if ( ! is_array( $user_submitted ) ) {
					$data_tampering = true;
				} else {
					foreach ( $user_submitted as $key => $val ) {
						if ( !isset( $element['settings']['rows'][$key] ) ) {
							$data_tampering = true;
						} else if ( !is_array( $val ) || empty( $val ) ) {
							$required_validation = false;
						} else {
							foreach ( $val as $col ) {
								if ( !isset( $element['settings']['columns'][$col] ) ) {
									$data_tampering = true;
								}
							}
							if ( !$data_tampering ) {
								$required_validation = true;
							}
						}
					}
				}
				break;
			case 'matrix_dropdown' :
			case 'matrix_feedback' :
				$required_validation = true;
				// If the submission is empty then cast it to array
				// Issue #10
				if ( empty( $user_submitted ) ) {
					$user_submitted = array();
				}
				if ( ! is_array( $user_submitted ) ) {
					$data_tampering = true;
				} else {
					foreach ( $user_submitted as $key => $val ) {
						if ( !isset( $element['settings']['rows'][$key] ) ) {
							$data_tampering = true;
						} else if ( !is_array( $val ) || empty( $val ) ) {
							$required_validation = false;
						} else {
							foreach ( $val as $col_k => $col_v ) {
								if ( ! isset( $element['settings']['columns'][$col_k] ) ) {
									$data_tampering = true;
								} else if ( $col_v == '' ) {
									$required_validation = false;
								}
							}
						}
					}
				}
				break;
			case 'sorting' :
				if ( !is_array( $user_submitted ) || count( $user_submitted ) != count( $element['settings']['options'] ) ) {
					$data_tampering = true;
				} else {
					foreach ( $user_submitted as $o_key ) {
						if ( !isset( $element['settings']['options'][$o_key] ) ) {
							$data_tampering = true;
							break;
						}
					}
					if ( !$data_tampering ) {
						$required_validation = true;
					} else {
						$required_validation = false;
					}
				}
				break;
			case 'toggle' :
				if ( !is_string( $user_submitted ) && !is_bool( $user_submitted ) ) {
					$data_tampering = true;
				} else {
					$required_validation = true;
				}
				break;
			case 's_checkbox' :
				if ( !is_string( $user_submitted ) && !is_bool( $user_submitted ) ) {
					$data_tampering = true;
				} else {
					if ( false == $user_submitted ) {
						$required_validation = false;
					}
				}
				break;
			case 'address' :
				if ( ! is_array( $user_submitted ) ) {
					$data_tampering = true;
				} else {
					if ( '' == $user_submitted['recipient'] && '' == $user_submitted['line_one'] && '' == $user_submitted['line_two'] && '' == $user_submitted['country'] ) {
						$required_validation = false;
					} else {
						$required_validation = true;
					}
				}
		}

		/**
		 * ReqValidation    ElemValidation      Result
		 *      0                   0               1
		 *      0                   1               0
		 *      1                   0               1
		 *      1                   1               1
		 */
		if ( isset( $element['validation'] ) && isset( $element['validation']['required'] ) ) {
			if ( $required_validation == false && $element['validation']['required'] == true ) {
				$required_validation = false;
			} else {
				$required_validation = true;
			}
		} else {
			//It will only fallback to ranges, sliders etc, where it is required to validate
		}

		if ( !$data_tampering && isset( $element['validation'] ) && isset( $element['validation']['filters'] ) ) {
			$errors = array_merge( $errors, $this->validate_reg_exp( $user_submitted, $element['validation']['filters'], $element['validation']['required'], $element ) );
		}

		return array(
			'required_validation' => $required_validation,
			'data_tampering' => $data_tampering,
			'errors' => $errors,
			'data' => $data,
		);
	}

	protected function validate_reg_exp( $value, $validation_filters, $required = false, $element = array() ) {
		$reg_exp = $this->get_reg_exp();
		$errors = array();
		$to_check = $required || !empty( $value );

		//Types
		if ( isset( $validation_filters['type'] ) && isset( $reg_exp[$validation_filters['type']] ) && $to_check ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $val ) {
					if ( !preg_match( $reg_exp[$validation_filters['type']]['regex'], $val ) ) {
						$errors[] = $reg_exp[$validation_filters['type']]['alertText'];
					}
				}
			} else {
				if ( !preg_match( $reg_exp[$validation_filters['type']]['regex'], (string) $value ) ) {
					$errors[] = $reg_exp[$validation_filters['type']]['alertText'];
				}
			}

		}


		$min_max_check = array( 'number', 'integer' );
		$minsize_maxsize_check = array( 'all', 'onlyNumberSp', 'onlyLetterSp', 'onlyLetterNumber', 'onlyLetterNumberSp', 'noSpecialCharacter' );

		//Min & Max (Float)

		if ( isset( $validation_filters['type'] ) && in_array( $validation_filters['type'], $min_max_check ) ) {
			if ( isset( $validation_filters['min'] ) && $validation_filters['min'] != '' && $to_check ) {
				if ( (float) $value < $validation_filters['min'] ) {
					$errors[] = $reg_exp['min']['alertText'] . $validation_filters['min'];
				}
			}
			if ( isset( $validation_filters['max'] ) && $validation_filters['max'] != '' && $to_check ) {
				if ( (float) $value > $validation_filters['max'] ) {
					$errors[] = $reg_exp['max']['alertText'] . $validation_filters['max'];
				}
			}
		}


		//minSize & maxSize
		if ( isset( $validation_filters['type'] ) && in_array( $validation_filters['type'], $minsize_maxsize_check ) ) {
			if ( isset( $validation_filters['minSize'] ) && $validation_filters['minSize'] != '' && $to_check ) {
				if ( mb_strlen( (string) $value ) < $validation_filters['minSize'] ) {
					$errors[] = $reg_exp['minSize']['alertText'] . $validation_filters['minSize'] . $reg_exp['minSize']['alertText2'];
				}
			}
			if ( isset( $validation_filters['maxSize'] ) && $validation_filters['maxSize'] != '' && $to_check ) {
				if ( mb_strlen( (string) $value ) > $validation_filters['maxSize'] ) {
					$errors[] = $reg_exp['maxSize']['alertText'] . $validation_filters['maxSize'] . $reg_exp['maxSize']['alertText2'];
				}
			}
		}

		//minCheckbox & maxCheckbox
		if ( isset( $validation_filters['minCheckbox'] ) && $validation_filters['minCheckbox'] != '' ) {
			if ( count( (array) $value ) < (float) $validation_filters['minCheckbox'] ) {
				$errors[] = $reg_exp['minCheckbox']['alertText'] . $validation_filters['minCheckbox'] . $reg_exp['minCheckbox']['alertText2'];
			}
		}
		if ( isset( $validation_filters['maxCheckbox'] ) && $validation_filters['maxCheckbox'] != '' ) {
			if ( count( (array) $value ) > (float) $validation_filters['maxCheckbox'] ) {
				$errors[] = $reg_exp['maxCheckbox']['alertText'] . $validation_filters['maxCheckbox'] . $reg_exp['maxCheckbox']['alertText2'];
			}
		}

		return $errors;
	}

	protected function get_reg_exp() {
		$reg_exp = array(
			'required' => array(
				'alertText' =>  __( '* This field is required', 'ipt_fsqm' ),
				'alertTextCheckboxMultiple' =>  __( '* Please select an option', 'ipt_fsqm' ),
				'alertTextCheckboxe' =>  __( '* This checkbox is required', 'ipt_fsqm' ),
				'alertTextDateRange' =>  __( '* Both date range fields are required', 'ipt_fsqm' )
			),
			'requiredInFunction' => array(
				'alertText' =>  __( '* Field must equal test', 'ipt_fsqm' )
			),
			'dateRange' => array(
				'alertText' =>  __( '* Invalid ', 'ipt_fsqm' ),
				'alertText2' =>  __( 'Date Range', 'ipt_fsqm' )
			),
			'dateTimeRange' => array(
				'alertText' =>  __( '* Invalid ', 'ipt_fsqm' ),
				'alertText2' =>  __( 'Date Time Range', 'ipt_fsqm' )
			),
			'minSize' => array(
				'alertText' =>  __( '* Minimum ', 'ipt_fsqm' ),
				'alertText2' =>  __( ' characters required', 'ipt_fsqm' )
			),
			'maxSize' => array(
				'alertText' =>  __( '* Maximum ', 'ipt_fsqm' ),
				'alertText2' =>  __( ' characters allowed', 'ipt_fsqm' )
			),
			'groupRequired' => array(
				'alertText' =>  __( '* You must fill one of the following fields', 'ipt_fsqm' )
			),
			'min' => array(
				'alertText' =>  __( '* Minimum value is ', 'ipt_fsqm' )
			),
			'max' => array(
				'alertText' =>  __( '* Maximum value is ', 'ipt_fsqm' )
			),
			'past' => array(
				'alertText' =>  __( '* Date prior to ', 'ipt_fsqm' )
			),
			'future' => array(
				'alertText' =>  __( '* Date past ', 'ipt_fsqm' )
			),
			'maxCheckbox' => array(
				'alertText' =>  __( '* Maximum ', 'ipt_fsqm' ),
				'alertText2' =>  __( ' option(s) allowed', 'ipt_fsqm' )
			),
			'minCheckbox' => array(
				'alertText' =>  __( '* Please select ', 'ipt_fsqm' ),
				'alertText2' =>  __( ' option(s)', 'ipt_fsqm' )
			),
			'equals' => array(
				'alertText' =>  __( '* Fields do not match', 'ipt_fsqm' )
			),
			'creditCard' => array(
				'alertText' =>  __( '* Invalid credit card number', 'ipt_fsqm' )
			),
			'phone' => array(
				// credit => jquery.h5validate.js / orefalo
				'regex' => "/^([\+][0-9]{1,3}[\ \.\-])?([\(]{1}[0-9]{2,6}[\)])?([0-9\ \.\-\/]{3,20})((x|ext|extension)[\ ]?[0-9]{1,4})?$/",
				'alertText' =>  __( '* Invalid phone number', 'ipt_fsqm' )
			),
			'email' => array(
				// HTML5 compatible email regex ( http =>//www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
				'regex' => "/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/",
				'alertText' =>  __( '* Invalid email address', 'ipt_fsqm' )
			),
			'integer' => array(
				'regex' => "/^[\-\+]?\d+$/",
				'alertText' =>  __( '* Not a valid integer', 'ipt_fsqm' )
			),
			'number' => array(
				// Number, including positive, negative, and floating decimal. credit => orefalo
				'regex' => "/^[\-\+]?((([0-9]{1,3})([,][0-9]{3})*)|([0-9]+))?([\.]([0-9]+))?$/",
				'alertText' =>  __( '* Invalid floating decimal number', 'ipt_fsqm' )
			),
			'date' => array(
				// Check if date is valid by leap year
				'alertText' =>  __( '* Invalid date, must be in YYYY-MM-DD format', 'ipt_fsqm' )
			),
			'ipv4' => array(
				'regex' => "/^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/",
				'alertText' =>  __( '* Invalid IP address', 'ipt_fsqm' )
			),
			'url' => array(
				'regex' => "/^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(([a-z]|\d|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])*([a-z]|\d|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])))\.)+(([a-z]|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(([a-z]|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])*([a-z]|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\x{E000}-\x{F8FF}]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/iu",
				'alertText' =>  __( '* Invalid URL', 'ipt_fsqm' )
			),
			'onlyNumberSp' => array(
				'regex' => "/^[0-9\ ]+$/",
				'alertText' =>  __( '* Numbers only', 'ipt_fsqm' )
			),
			'onlyLetterSp' => array(
				'regex' => "/^[a-zA-Z\ \']+$/",
				'alertText' =>  __( '* Letters only', 'ipt_fsqm' )
			),
			'onlyLetterNumber' => array(
				'regex' => "/^[0-9a-zA-Z]+$/",
				'alertText' =>  __( '* No spaces or special characters allowed', 'ipt_fsqm' )
			),
			'onlyLetterNumberSp' => array(
				'regex' => "/^[0-9a-zA-Z\ ]+$/",
				'alertText' =>  __( '* Only letters, number and spaces allowed', 'ipt_fsqm' )
			),
			'zipCode' => array(
				'regex' => "/^[0-9a-zA-Z\ \-]+$/",
				'alertText' => __( '* Not a valid zip code. Only number, letters and dashes are allowed.', 'ipt_fsqm' ),
			),
			'noSpecialCharacter' => array(
				'regex' => "/^[0-9a-zA-Z\ \.\,\?\\\"\']+$/",
				'alertText' => __( '* No special characters allowed', 'ipt_fsqm' ),
			),
			// The reason why we use negative regex because of non-english
			// characters in a person's name.
			'personName' => array(
				'regex' => "/^[^\!\@\#\$\%\^\&\*\(\)\_\+\=\\\|\{\}\[\]\:\;\"\/\?\,\<\>\`\~1-9]+$/",
				'alertText' => __( 'Valid name only, no special characters except dots, dashes and single quote.', 'ipt_fsqm' ),
			),
			//tls warning =>homegrown not fielded
			'dateFormat' => array(
				'regex' => "/^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(?:(?:0?[1-9]|1[0-2])(\/|-)(?:0?[1-9]|1\d|2[0-8]))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(0?2(\/|-)29)(\/|-)(?:(?:0[48]00|[13579][26]00|[2468][048]00)|(?:\d\d)?(?:0[48]|[2468][048]|[13579][26]))$/",
				'alertText' =>  __( '* Invalid Date', 'ipt_fsqm' )
			),
			//tls warning =>homegrown not fielded
			'dateTimeFormat' => array(
				'regex' => "/^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/",
				'alertText' =>  __( '* Invalid Date or Date Format', 'ipt_fsqm' ),
				'alertText2' =>  __( 'Expected Format => ', 'ipt_fsqm' ),
				'alertText3' =>  __( 'mm/dd/yyyy hh =>mm =>ss AM|PM or ', 'ipt_fsqm' ),
				'alertText4' =>  __( 'yyyy-mm-dd hh =>mm =>ss AM|PM', 'ipt_fsqm' )
			)
		);

		return $reg_exp;
	}

	protected function compat_data_mcq() {
		$data = array();
		foreach ( $this->data->mcq as $m_key => $mcq ) {
			if ( is_int( $m_key ) ) {
				$data[$m_key] = array(
					'type' => isset( $this->mcq[$m_key] ) ? $this->mcq[$m_key]['type'] : 'undefined',
					'm_type' => isset( $this->mcq[$m_key] ) ? $this->mcq[$m_key]['m_type'] : 'undefined',
					'options' => (array) $mcq,
					'others' => isset( $this->data->mcq[$m_key . '_others'] ) ? $this->data->mcq[$m_key . '_others'] : '',
				);
			} else {
				continue;
			}
		}
		$this->data->mcq = $data;
	}

	protected function compat_data_pinfo() {
		$data = array();
		$dbmap = array();
		foreach ( $this->data->pinfo as $p_key => $pinfo ) {
			if ( is_int( $p_key ) ) {
				$data[$p_key] = array(
					'type' => isset( $this->mcq[$p_key] ) ? $this->mcq[$p_key]['type'] : 'undefined',
					'm_type' => isset( $this->mcq[$p_key] ) ? $this->mcq[$p_key]['m_type'] : 'undefined',
				);
				switch ( $data[$p_key]['type'] ) {
				default :
					$data[$p_key]['value'] = (string) $pinfo;
				case 'p_radio' :
				case 'p_checkbox' :
					$data[$p_key]['options'] = (array) $pinfo;
					break;
				case 's_checkbox' :
					$data[$p_key]['value'] = true;
				}
			} else {
				$dbmap[$p_key] = $pinfo;
			}
		}

		//Now set the dbmap
		$keys = $this->get_keys_from_layouts_by_types( array( 'f_name', 'l_name', 'email', 'phone' ), $this->layout );
		if ( !empty( $keys ) ) {
			foreach ( $keys as $p_key ) {
				$data[$p_key] = array(
					'type' => isset( $this->pinfo[$p_key] ) ? $this->pinfo[$p_key]['type'] : 'undefined',
					'm_type' => isset( $this->pinfo[$p_key] ) ? $this->pinfo[$p_key]['m_type'] : 'undefined',
					'value' => $this->data->{$this->pinfo[$p_key]['type']},
				);
			}
		}

		$this->data->pinfo = $data;
	}

	protected function compat_data_freetype() {
		$data = array();
		foreach ( $this->data->freetype as $f_key => $freetype ) {
			$data[$f_key] = array(
				'type' => isset( $this->freetype[$f_key] ) ? $this->freetype[$f_key]['type'] : 'undefined',
				'm_type' => isset( $this->freetype[$f_key] ) ? $this->freetype[$f_key]['m_type'] : 'undefined',
				'value' => htmlspecialchars_decode( $freetype ),
			);
		}
		$this->data->freetype = $data;
	}

	public function prepare_empty_data() {
		$current_user = wp_get_current_user();
		$logged_in = is_user_logged_in() && $current_user instanceof WP_User;
		$this->data = new stdClass();
		$this->data->form_id = $this->form_id;
		$this->data->f_name = ( $logged_in && $this->settings['submission']['log_registered_user'] ) ? ( $current_user->user_firstname != '' ? $current_user->user_firstname : $current_user->display_name ) : '';
		$this->data->l_name = ( $logged_in && $this->settings['submission']['log_registered_user'] ) ? $current_user->user_lastname : '';
		$this->data->email = ( $logged_in && $this->settings['submission']['log_registered_user'] ) ? $current_user->user_email : '';
		$this->data->phone = '';
		$this->data->ip = $this->settings['submission']['log_ip'] ? $_SERVER['REMOTE_ADDR'] : '';
		$this->data->star = 0;
		$this->data->score = 0;
		$this->data->max_score = 0;
		$this->data->date = current_time( 'mysql' );
		$this->data->comment = $this->settings['general']['default_comment'];
		$this->data->user_id = ( $logged_in && $this->settings['submission']['log_registered_user'] ) ? $current_user->ID : 0;
		$this->data->url_track = '';
		$this->data->referer = '';
		$this->data->time = 0;
		$this->data->paid = 0;
		$this->data->epoc = current_time( 'timestamp' );

		$this->prepare_empty_data_mcq();
		$this->prepare_empty_data_freetype();
		$this->prepare_empty_data_pinfo();
	}

	protected function prepare_empty_data_mcq() {
		$this->data->mcq = array();
		//prepare the mcq
		foreach ( $this->mcq as $m_key => $mcq ) {
			$this->data->mcq[$m_key] = null;
		}
	}

	protected function prepare_empty_data_freetype() {
		$this->data->freetype = array();
		//prepare the freetype
		foreach ( $this->freetype as $f_key => $freetype ) {
			$this->data->freetype[$f_key] = null;
		}
	}

	protected function prepare_empty_data_pinfo() {
		$this->data->pinfo = array();
		//prepare the pinfo
		foreach ( $this->pinfo as $p_key => $pinfo ) {
			$this->data->pinfo[$p_key] = null;
		}
	}

	/*==========================================================================
	 * Quick Preview, Email & Print APIs
	 *========================================================================*/

	public function get_email_template_path( $name ) {
		$filename = $name . '.php';

		$form_specific_path = WP_CONTENT_DIR . '/wpeform-email-templates/' . $this->form_id . '-' . $filename;
		$global_custom_path = WP_CONTENT_DIR . '/wpeform-email-templates/' . $filename;
		$local_path = IPT_FSQM_Loader::$abs_path . '/email-templates/' . $filename;
		if ( file_exists( $form_specific_path ) ) {
			return $form_specific_path;
		}
		if ( file_exists( $global_custom_path ) ) {
			return $global_custom_path;
		}

		return $local_path;
	}

	public function admin_email_format( $string, $ename = '' ) {
		if ( '' == $string ) {
			return $string;
		}
		$return = str_replace( array( '%FORMNAME%', '%SITENAME%', '%ENAME%', '%FNAME%', '%LNAME%', '%PHONE%', '%EMAIL%' ), array( $this->name, get_bloginfo( 'name' ), $ename, $this->data->f_name, $this->data->l_name, $this->data->phone, $this->data->email ), $string );
		return $return;
	}

	public function user_email_format( $string ) {
		if ( '' == $string ) {
			return $string;
		}
		$return = str_replace( array( '%FORMNAME%', '%SITENAME%', '%FNAME%', '%LNAME%', '%PHONE%', '%EMAIL%' ), array( $this->name, get_bloginfo( 'name' ), $this->data->f_name, $this->data->l_name, $this->data->phone, $this->data->email ), $string );
		return $return;
	}

	public function get_admin_email_template( $msgs, $show_submission = false, $summary_header = true ) {
		if ( is_array( $msgs ) ) {
			$msgs = implode( "\n\n", $msgs );
		}
		$format_string_components = $this->get_format_string();
		// prepare the data
		$data = [
			'style' => $this->settings['email_template'],
		];

		$email_template = $this->get_email_template_path( 'admin-email' );
		ob_start();
		include $email_template;
		return ob_get_clean();
	}

	/**
	 * Get the message of user notification email It also takes care of email
	 * formatting
	 *
	 * @param      string   $notification_message  The notification message. If
	 *                                             empty then the one from
	 *                                             settings would be used
	 * @param      boolean  $submission_lock       Whether the submission should
	 *                                             not be appended, even if
	 *                                             settings say so
	 * @param      string   $lock_message          The lock message. Shown
	 *                                             instead of notification
	 *                                             message if submission is
	 *                                             locked
	 */
	public function user_notification_email( $notification_message = '', $submission_lock = false, $lock_message = '' ) {
		if ( '' == $notification_message ) {
			$notification_message = $this->settings['user']['notification_msg'];
		}
		$format_string_components = $this->get_format_string();
		// prepare the data
		$data = [
			'style' => $this->settings['email_template'],
		];

		$email_template = $this->get_email_template_path( 'user-email' );
		ob_start();
		include $email_template;
		return ob_get_clean();
	}

	public function send_user_notification_email() {
		// Create tha array
		$user_email = array();
		// Get default title and msg
		$title = $this->settings['user']['notification_sub'];
		$msg = $this->settings['user']['notification_msg'];
		// Change the title and msg in case of user update
		if ( true == $this->doing_update && true == $this->user_update ) {
			$title = $this->settings['user']['update_sub'];
			$msg = $this->settings['user']['update_msg'];
		}

		// Create the user email if settings are appropriate
		if ( $this->settings['user']['notification_email'] != '' && $this->data->email != '' ) {
			$user_email[$this->data->email] = array(
				'title' => $this->user_email_format( $title ),
				'from' => array( $this->settings['user']['notification_from'], $this->settings['user']['notification_email'] ),
				'msgs' => $this->user_notification_email( $msg, $this->get_submission_lock_status(), $this->settings['payment']['lock_message'] ),
				'smtp' => $this->settings['user']['smtp'],
				'smtp_conf' => $this->settings['user']['smtp_config'],
				'headers' => $this->settings['user']['header'],
			);
		}
		// Apply the filters
		$user_email = apply_filters( 'ipt_fsqm_user_email', $user_email, $this );
		// Mail it
		$this->format_email_style();
		$this->email( $user_email );
		$this->reset_email_styling();
	}

	/**
	 * Checks whether a submission should be locked/hidden based on payment
	 * status and form settings
	 *
	 * Considers built-in cases of one-page checkouts Also WooCommerce
	 * Integration
	 *
	 * @return     boolean  The submission lock status. True if submission needs to be locked, false otherwise
	 */
	public function get_submission_lock_status() {
		// We need to check for cases, especially with payment
		// If payment is enabled, then see if the processing is done
		// If it is processed, then send the full email, else send the payment status
		// First we assume that full email is to be sent
		$is_locked = false;
		$this->blacklist_conditional_hiddens();

		// If payment system is enabled
		if (
			true == $this->settings['payment']['enabled']
			&& true == $this->settings['payment']['sub_on_success']
		) {
			// We need to check for possibilities
			// Also payment would not be needed if payment element is conditionally hidden
			$elem_keys = $this->get_keys_from_layouts_by_types( 'payment', $this->layout );
			if ( count( $elem_keys ) > 0 ) {
				$payment_element = $this->get_element_from_layout( array(
					'type' => 'payment',
					'm_type' => 'pinfo',
					'key' => $elem_keys[0],
				) );
				if ( true == $this->validate_data_against_conditional_logic( $payment_element, $elem_keys[0] ) && '1' != $this->data->paid ) {
					$is_locked = true;
				}
			}
		}

		// If WooCommerce is enabled
		global $ipt_eform_wc;
		if (
			true == $this->settings['payment']['woocommerce']['enabled']
			&& 'single' === $this->settings['payment']['woocommerce']['mode']
			&& true == $this->settings['payment']['sub_on_success']
			&& ! is_null( $ipt_eform_wc )
		) {
			// We need to check for possibilities
			// Also WooCommerce is not needed for this if the mathematical element is conditionally hidden

			$math_element = $this->get_element_from_layout( array(
				'm_type' => 'freetype',
				'key' => $this->settings['payment']['woocommerce']['mathematical'],
			) );
			// If math element is set and conditionally hidden, then we don't do woocommerce
			if ( ! empty( $math_element ) && true == $this->validate_data_against_conditional_logic( $math_element, $this->settings['payment']['woocommerce']['mathematical'] ) && '1' != $this->data->paid ) {
				$is_locked = true;
			} elseif ( empty( $math_element ) && '1' != $this->data->paid ) {
				$is_locked = true;
			}
		}

		// Return with filter
		return apply_filters( 'ipt_fsqm_submission_lock_status', $is_locked, $this );
	}


	/**
	 * Shows the summary table / quick preview
	 *
	 * @param      boolean  $for_email  If for email, certain styles are applied
	 *                                  if true
	 * @param      boolean  $show_logo  Whether to show the logo
	 * @param      boolean  $for_pdf    If for PDF, certain styles are applied
	 * @param      boolean  $for_admin  If for admin, certain fields, like
	 *                                  hidden fields are shown at per with
	 *                                  settings
	 */
	public function show_quick_preview( $for_email = false, $show_logo = true, $for_pdf = false, $for_admin = false ) {
		global $ipt_fsqm_settings, $ipt_fsqm_info, $wpdb;
		$db_maps = array(
			'l_name' => __( 'Last Name', 'ipt_fsqm' ),
			'email' => __( 'Email', 'ipt_fsqm' ),
			'phone' => __( 'Phone', 'ipt_fsqm' ),
		);
		$user = null;
		if ( $this->data->user_id != 0 ) {
			$user = get_user_by( 'id', $this->data->user_id );
		}

		$this->for_pdf_quick_preview = $for_pdf;
		$this->for_admin_quick_preview = $for_admin;

		if ( $for_email ) {
			$this->for_email_quick_preview = true;
			$this->format_email_style();
		}

		$format_string_components = $this->get_format_string();
		$ui = IPT_Plugin_UIF_Front::instance();

		// Add the template tag filter if needed
		if ( true == $this->settings['general']['pipe'] ) {
			$reactive_helper = new EForm_Reactive_Helper( $this->form_id );
			$reactive_helper->add_value_filters( $this->data_id );
		}

		// Loop through all labels and apply the filter to show latex
		$this->apply_label_filters();
		$css_prefix = '.ipt_fsqm_preview ';
		if ( $this->for_pdf_quick_preview ) {
			$css_prefix = '';
		}

		// Prepare the layout blacklists
		$layout_blacklists = array();
		if ( '' != $this->settings['summary']['blacklist'] ) {
			$layout_blacklists = explode( ',', $this->settings['summary']['blacklist'] );
			// Trip and lowercase
			$layout_blacklists = array_map( 'trim', $layout_blacklists );
			$layout_blacklists = array_map( 'strtolower', $layout_blacklists );
		}
		$this->layout_blacklists = $layout_blacklists;

		// Prepare woo payment status
		$woo_payment_flag = false;
		if (
			$this->settings['payment']['woocommerce']['enabled']
			&& $this->settings['payment']['woocommerce']['show_in_summary']
			&& 'single' === $this->settings['payment']['woocommerce']['mode']
		) {
			$woo_payment_flag = true;
		}

		$additional_rows = '';
		if ( ! empty( $this->settings['summary']['meta'] ) && is_array( $this->settings['summary']['meta'] ) ) {
			$metas = $this->settings['summary']['meta'];
			$data_values = new IPT_eForm_Form_Elements_Values( $this->data_id );
			// Loop through all and add the meta
			$format_strings = $this->get_format_string();
			foreach ( $metas as $meta ) {
				if ( $meta['m_type'] === 'formatstring' ) {
					// since this is a format string, it can be a hardcoded value
					// or a format string from the data
					$accessor = $meta['key'];
					$meta_value = isset( $format_strings[ $accessor ] )
						? $format_strings[ $accessor ]
						: $accessor;
				} else {
					$meta_value = $data_values->get_value(
						$meta['m_type'],
						$meta['key'],
						( true == $this->settings['integration']['formhandler']['metaarray'] ? 'json' : 'string' ),
						'label'
					);
				}
				$additional_rows .= '<tr style="' . $this->email_styling['tr'] . '">'
					. '<th style="' . $this->email_styling['th'] . '" scope="row">' . $meta['meta_key'] . '</th>'
					. '<td style="' . $this->email_styling['td'] . '">' .  esc_html( $meta_value ) . '</td>'
					. '</tr>';
			}
		}
?>
<style type="text/css">
	/* utility classes */
	<?php echo $css_prefix; ?>.alignleft {
		float: left !important;
		margin-right: 10px !important;
	}

	<?php echo $css_prefix; ?>.alignright {
		float: right !important;
		margin-left: 10px !important;
	}

	<?php echo $css_prefix; ?>.textleft {
		text-align: left !important;
	}

	<?php echo $css_prefix; ?>.textright {
		text-align: right !important;
	}

	<?php echo $css_prefix; ?>.clear {
		clear: both !important;
	}
	<?php echo $css_prefix; ?>ul {
		list-style-type: disc !important;
		margin-left: 40px !important;
	}
	<?php echo $css_prefix; ?>ol {
		list-style-type: decimal !important;
		margin-left: 40px !important;
	}
	<?php echo $css_prefix; ?>blockquote {
		padding: 5px !important;
		border-left: 2px solid #efefef !important;
		margin-left: 20px !important;
	}
	<?php echo $css_prefix; ?>a {
		color: <?php echo $this->settings['email_template']['a_color']; ?>;
		text-decoration: none;
	}
	<?php echo $css_prefix; ?> td > p:last-child,
	<?php echo $css_prefix; ?> th > p:last-child {
		margin-bottom: 0 !important;
	}
	<?php echo $css_prefix; ?> td > p:first-child,
	<?php echo $css_prefix; ?> th > p:first-child {
		margin-top: 0 !important;
	}
	<?php if ( is_rtl() ) : ?>
		table,
		.ipt_fsqm_preview {
			direction: rtl;
		}
		table tbody,
		table tr,
		table th,
		table td {
			text-align: right;
		}
	<?php endif; ?>
	/**/
</style>
<?php if ( '' != $this->settings['theme']['logo'] && $show_logo ) : ?>
	<div class="ipt_fsqm_form_logo--in-summary" style="<?php echo esc_attr( $this->email_styling['logo_container'] ); ?>">
		<img style="<?php echo esc_attr( $this->email_styling['logo'] ); ?>; display: block; margin: 0 auto;" src="<?php echo esc_attr( $this->settings['theme']['logo'] ); ?>" alt="<?php echo esc_attr( $this->name ); ?>">
	</div>
<?php endif; ?>
<?php if ( '' != $this->settings['summary']['before'] ) : ?>
<table class="ipt_fsqm_preview bordered devicewidthinner" style="<?php echo $this->email_styling['table']; ?>" width="100%">
	<tbody style="<?php echo $this->email_styling['tbody']; ?>">
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<td style="<?php echo $this->email_styling['td']; ?>"><?php echo wptexturize( wpautop( str_replace( array_keys( $format_string_components ), array_values( $format_string_components ), $this->settings['summary']['before'] ) ) ); ?></td>
		</tr>
	</tbody>
</table>
<?php endif; ?>

<!-- Show_details table -->
<?php if ( $this->settings['summary']['show_details'] ) : ?>
<table class="ipt_fsqm_preview bordered ipt_fsqm_show_details devicewidthinner" style="<?php echo $this->email_styling['table']; ?>" width="100%">
	<thead style="<?php echo $this->email_styling['thead']; ?>">
		<tr style="<?php echo $this->email_styling['headtr']; ?>">
			<th style="<?php echo $this->email_styling['headth']; ?>" scope="col" colspan="2"><?php echo $this->name; ?> <?php printf( $this->settings['summary']['id_format'], $this->data_id, date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $this->data->date ) ), date( $this->settings['summary']['id_dt_format'], strtotime( $this->data->date ) ) ); ?></th>
		</tr>
	</thead>
	<tbody style="<?php echo $this->email_styling['tbody']; ?>">
		<?php if ( $this->settings['summary']['meta_position'] === 'top' ) : ?>
			<?php echo $additional_rows; ?>
		<?php endif; ?>
		<?php if ( true == $this->settings['summary']['f_name'] ) : ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<th style="<?php echo $this->email_styling['th']; ?>" scope="row"><?php _e( 'First Name', 'ipt_fsqm' ); ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>"><?php echo $this->data->f_name; ?></td>
		</tr>
		<?php endif; ?>
		<?php foreach ( $db_maps as $key => $label ) : ?>
		<?php if ( $this->data->{$key} != '' && true == $this->settings['summary'][$key] ) : ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<th style="<?php echo $this->email_styling['th']; ?>" scope="row"><?php echo $label; ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>"><?php echo $key == 'email' ? '<a href="mailto:' . $this->data->{$key} . '">' . $this->data->{$key} . '</a>' : $this->data->{$key}; ?></td>
		</tr>
		<?php endif; ?>
		<?php endforeach; ?>
		<?php if ( $this->settings['summary']['meta_position'] === 'middle' ) : ?>
			<?php echo $additional_rows; ?>
		<?php endif; ?>
		<?php if ( true == $this->settings['summary']['ip'] ) : ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<th style="<?php echo $this->email_styling['th']; ?>" scope="row"><?php _e( 'IP Address', 'ipt_fsqm' ); ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>"><?php echo $this->data->ip; ?></td>
		</tr>
		<?php endif; ?>
		<?php if ( $this->data->max_score != 0 ) : ?>
		<?php if ( true == $this->settings['summary']['total_score'] ) : ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<th style="<?php echo $this->email_styling['th']; ?>" scope="row"><?php echo $this->settings['summary']['tscore_title']; ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>"><?php printf( $this->settings['summary']['tscore_output'], number_format_i18n( (float) $this->data->score, (int) $this->settings['ranking']['precision'] ), number_format_i18n( (float) $this->data->max_score, (int) $this->settings['ranking']['precision'] ), number_format_i18n( $this->data->score * 100 / $this->data->max_score, (int) $this->settings['ranking']['precision'] ) ); ?></td>
		</tr>
		<?php endif; ?>
		<?php if ( true == $this->settings['summary']['average_score'] ) : ?>
			<?php
			$total_submissions = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d", $this->form_id ) );
			$average_score = number_format_i18n( $wpdb->get_var( $wpdb->prepare( "SELECT AVG(score) FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d", $this->form_id ) ), $this->settings['ranking']['precision'] );
			?>
		<?php if ( $total_submissions > 1 ) : ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<th style="<?php echo $this->email_styling['th']; ?>" scope="row"><?php printf( $this->settings['summary']['ascore_title'], $total_submissions ); ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>"><?php printf( $this->settings['summary']['ascore_output'], $average_score, number_format_i18n( $this->data->max_score, $this->settings['ranking']['precision'] ), number_format_i18n( $average_score * 100 / $this->data->max_score, $this->settings['ranking']['precision'] ) ); ?></td>
		</tr>
		<?php endif; ?>
		<?php endif; ?>
		<?php if ( true == $this->settings['summary']['designation'] ) : ?>
		<?php if ( $this->settings['ranking']['enabled'] == true ) : ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<th style="<?php echo $this->email_styling['th']; ?>" scope="row"><?php echo $this->settings['ranking']['title'] ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>"><?php echo $format_string_components['%DESIGNATION%']; ?></td>
		</tr>
		<?php if ( $format_string_components['%DESIGNATIONMSG%'] != '' ) : ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<td colspan="2" style="<?php echo $this->email_styling['td']; ?>"><?php echo $format_string_components['%DESIGNATIONMSG%']; ?></td>
		</tr>
		<?php endif; ?>
		<?php endif; ?>
		<?php endif; ?>
		<?php endif; ?>
		<?php if ( $this->settings['summary']['meta_position'] === 'afterScore' ) : ?>
			<?php echo $additional_rows; ?>
		<?php endif; ?>
		<?php if ( $this->settings['stopwatch']['enabled'] == true && $this->data->time != 0 ) : ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<th style="<?php echo $this->email_styling['th']; ?>" scope="row"><?php echo $this->settings['stopwatch']['title']; ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>"><?php echo $this->seconds_to_words( $this->data->time ); ?></td>
		</tr>
		<?php endif; ?>
		<?php if ( $this->settings['general']['comment_title'] != '' ) : ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<th style="<?php echo $this->email_styling['th']; ?>" scope="row"><?php echo $this->settings['general']['comment_title']; ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>"><?php echo wpautop( $this->data->comment ); ?></td>
		</tr>
		<?php endif; ?>
		<?php if ( $woo_payment_flag ) : ?>
			<tr style="<?php echo $this->email_styling['tr']; ?>">
				<th style="<?php echo $this->email_styling['th']; ?>" scope="row"><?php echo $this->settings['payment']['woocommerce']['summary_title']; ?></th>
				<td style="<?php echo $this->email_styling['td']; ?>">
					<?php echo $this->data->paid ? __( 'Paid', 'ipt_fsqm' ) : __( 'Unpaid', 'ipt_fsqm' ); ?>
				</td>
			</tr>
		<?php endif; ?>
		<?php if ( true == $this->settings['summary']['user_account'] ) : ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<th style="<?php echo $this->email_styling['th']; ?>" scope="row"><?php _e( 'User Account', 'ipt_fsqm' ); ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>">
				<?php if ( $this->data->user_id != 0 && $user instanceof WP_User ) : ?>
				<?php if ( is_admin() ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ipt_fsqm_view_all_submissions&user_id=' . $this->data->user_id ) ); ?>"><?php echo $user->display_name; ?></a>
				<?php else : ?>
				<a href="<?php echo $this->get_utrackback_url(); ?>"><?php echo $user->display_name; ?></a>
				<?php endif; ?>
				<?php else : ?>
				<?php _e( 'Guest', 'ipt_fsqm' ); ?>
				<?php endif; ?>
			</td>
		</tr>
		<?php endif; ?>
		<?php if ( true == $this->settings['summary']['link'] ) : ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<th style="<?php echo $this->email_styling['th']; ?>" scope="row"><?php _e( 'Link', 'ipt_fsqm' ); ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>; word-wrap: break-word;">
				<a href="<?php echo $this->get_trackback_url(); ?>"><?php echo $this->get_trackback_url(); ?></a>
			</td>
		</tr>
		<?php endif; ?>
		<?php if ( '0' != $this->settings['general']['terms_page'] || !empty( $this->settings['general']['terms_page'] ) ) : $link = get_permalink( $this->settings['general']['terms_page'] ); ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<th style="<?php echo $this->email_styling['th']; ?>" scope="row"><?php _e( 'Accepted Terms & Conditions', 'ipt_fsqm' ); ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>">
				<a href="<?php echo $link; ?>"><?php echo $link; ?></a>
			</td>
		</tr>
		<?php endif; ?>
		<?php if ( $this->settings['summary']['meta_position'] === 'bottom' ) : ?>
			<?php echo $additional_rows; ?>
		<?php endif; ?>
		<?php if ( $for_email ) : ?>
		<tr style="border: 0 none;">
			<td height="20" style="border: 0 none;" colspan="2"></td>
		</tr>
		<?php endif; ?>
	</tbody>
</table>
<?php endif; ?>
<!-- end show_details table -->
<?php
if ( false == $this->settings['summary']['show_details'] && false == $this->settings['summary']['show_elements'] ) {
	if ( current_user_can( 'manage_feedback' ) ) {
		echo '<p>' . __( 'Both detail and element tables are hidden. Please show at least one of them.', 'ipt_fsqm' ) . '</p>';
	} else {
		echo '<p>' . __( 'Sorry, the information you are looking for, is not available right now.', 'ipt_fsqm' ) . '</p>';
	}
}
?>

<?php if ( '' != $this->settings['summary']['after'] ) : ?>
<table class="ipt_fsqm_preview bordered devicewidthinner" style="<?php echo $this->email_styling['table']; ?>" width="100%">
	<tbody style="<?php echo $this->email_styling['tbody']; ?>">
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<td style="<?php echo $this->email_styling['td']; ?>"><?php echo wptexturize( wpautop( str_replace( array_keys( $format_string_components ), array_values( $format_string_components ), $this->settings['summary']['after'] ) ) ); ?></td>
		</tr>
	</tbody>
</table>
<?php endif; ?>

<!-- Start show_elements table -->
<?php if ( true == $this->settings['summary']['show_elements'] ) : ?>
<table class="ipt_fsqm_preview bordered ipt_fsqm_show_elements devicewidthinner" style="<?php echo $this->email_styling['table']; ?>" width="100%">
	<tbody style="<?php echo $this->email_styling['tbody']; ?>">
<?php $layout_iterator = 0; ?>
<?php foreach ( $this->layout as $layout_key => $layout ) : ?>
	<?php
	// Skip if conditionally hidden
	if ( $this->validate_data_against_conditional_logic( $layout, $layout_key ) == false ) {
		continue;
	}
	// Check if blacklisted
	if ( in_array( 'l' . $layout_iterator++, $layout_blacklists ) ) {
		continue;
	}

	$layout_title = str_replace( '%%NUM%%', $layout_iterator, $layout['title'] );
	?>
		<tr style="<?php echo $this->email_styling['thead']; ?>" class="head with-bg">

				<?php
				$has_header_icon = false;
				if ( isset( $layout['icon'] ) ) {
					$container_image = $ui->get_image_for_icon( $layout['icon'], '#ffffff' );
					if ( '' != $container_image ) {
						echo '<th style="' . $this->email_styling['iconshead'] . '" class="icons">';
						echo '<img src="' . $container_image . '" height="16" width="16" style="' . $this->email_styling['img_icon'] . '" />';
						echo '</th>';
						$has_header_icon = true;
					}
				}
				?>

			<th style="<?php echo $this->email_styling['headth']; ?>" colspan="<?php echo $has_header_icon ? '4' : '5' ?>">
				<?php echo $layout_title; ?>
				<?php if ( $layout['subtitle'] !== '' ) : ?>
					<br />
					<span class="description" style="<?php echo $this->email_styling['descriptionhead']; ?>">
						<?php echo $layout['subtitle']; ?>
					</span>
				<?php endif; ?>
			</th>
		</tr>
		<?php if ( $layout['description'] != '' ) : ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<td colspan="5" style="<?php echo $this->email_styling['td']; ?>">
				<?php echo apply_filters( 'ipt_uif_richtext', $layout['description'] ); ?>
			</td>
		</tr>
		<?php endif; ?>
		<?php $this->populate_layout( $layout_key, $layout ); ?>
<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>
<!-- end show_elements table -->
		<?php
		// End the value filter
		if ( true == $this->settings['general']['pipe'] ) {
			$reactive_helper->remove_value_filters();
		}
		// Reset admin quick preview??
		$this->for_admin_quick_preview = null;
	}

	public function populate_layout( $layout_key, $layout ) {
?>
		<?php foreach ( (array) $layout['elements'] as $l_key => $layout_element ) : ?>
		<?php
		// Check for manual blacklists
		$bsearch_pref = '';
		switch ( $layout_element['m_type'] ) {
			case 'design' :
				$bsearch_pref = 'd';
				break;
			case 'mcq' :
				$bsearch_pref = 'm';
				break;
			case 'freetype' :
				$bsearch_pref = 'f';
				break;
			case 'pinfo' :
				$bsearch_pref = 'o';
				break;
		}
		if ( in_array( $bsearch_pref . $layout_element['key'], $this->layout_blacklists ) ) {
			continue;
		}
		?>
		<?php
			$element = $layout_element['type'];
			$key = $layout_element['key'];
			$element_data = $this->get_element_from_layout( $layout_element );
			$submission_data = $this->get_submission_from_data( $layout_element );

			if ( $layout_element['m_type'] == 'design' ) {
				// Check for conditional logic
				if ( false === $this->validate_data_against_conditional_logic( $element_data, $key ) ) {
					continue;
				}

				// At this point, so conditional checks out
				// Either it is holder of child elements
				$child_element = $this->get_element_from_layout( $layout_element );

				if ( isset( $child_element['elements'] ) && is_array( $child_element['elements'] ) ) {
					$this->populate_layout( $l_key, $child_element );
				// Or itself is a simple design element
				} else {
					if ( $this->settings['summary']['show_design'] == true && in_array( $layout_element['type'], array( 'heading', 'richtext', 'embed', 'iconbox' ) ) ) {
						?>
					<tr style="<?php echo $this->email_styling['tr']; ?>">
						<?php $this->build_element_html( $element, $key, $element_data, $submission_data, 'ipt_fsqm_form_' . $this->form_id ); ?>
					</tr>
					<tr>
						<td class="td-separator" style="<?php echo $this->email_styling['tdsep']; ?>" colspan="5"></td>
					</tr>
						<?php
					}
				}

				// No need to check any further
				continue;
			}

			// Don't show if conditional logic returns false
			if ( false === $this->validate_data_against_conditional_logic( $element_data, $key ) ) {
				continue;
			}

			// Optionally check for empty elements and dont show if settings says so
			if ( $this->settings['summary']['hide_unattempted'] == true ) {
				// Get an empty submission structure
				$empty_sub_structure = $this->get_submission_structure( $layout_element['type'] );
				$true_sub_structure = $submission_data;

				// Unset the score data
				if ( isset( $empty_sub_structure['scoredata'] ) ) {
					unset( $empty_sub_structure['scoredata'] );
				}
				if ( isset( $true_sub_structure['scoredata'] ) ) {
					unset( $true_sub_structure['scoredata'] );
				}

				// Now compare
				// If it is same as empty sub structure then unattempted
				// But this would not work for feedback_matrix and matrix_dropdown
				// We are leaving it as is, if some new idea comes up then we shall proceed
				if ( empty( $true_sub_structure ) || $empty_sub_structure == $true_sub_structure ) {
					continue;
				}
			}

			// Check if a hidden element and viewable only by admin
			if ( 'hidden' == $element && true == $element_data['settings']['admin_only'] && false === $this->for_admin_quick_preview ) {
				continue;
			}
?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<?php $this->build_element_html( $element, $key, $element_data, $submission_data, 'ipt_fsqm_form_' . $this->form_id ); ?>
		</tr>
		<tr>
			<td class="td-separator" style="<?php echo $this->email_styling['tdsep']; ?>" colspan="5"></td>
		</tr>
		<?php endforeach; ?>
		<?php
	}

	public function apply_label_filters() {
		if ( ! empty( $this->mcq ) && is_array( $this->mcq ) ) :
		foreach ( $this->mcq as $e_key => $element ) {
			$this->mcq[$e_key]['title'] = apply_filters( 'ipt_uif_label', $element['title'] );
			$this->mcq[$e_key]['subtitle'] = apply_filters( 'ipt_uif_label', $element['subtitle'] );
			if ( isset( $element['settings'] ) && isset( $element['settings']['options'] ) && is_array( $element['settings']['options'] ) ) {
				foreach ( $element['settings']['options'] as $o_key => $op ) {
					if ( is_array( $op ) ) {
						if ( isset( $op['label'] ) ) {
							$this->mcq[$e_key]['settings']['options'][$o_key]['label'] = apply_filters( 'ipt_uif_label', $op['label'] );
						}
					} elseif ( is_string( $op ) ) {
						$this->mcq[$e_key]['settings']['options'][$o_key] = apply_filters( 'ipt_uif_label', $op );
					}
				}
			}
			if ( isset( $element['settings'] ) && isset( $element['settings']['rows'] ) && is_array( $element['settings']['rows'] ) ) {
				foreach ( $element['settings']['rows'] as $r_key => $row ) {
					if ( is_string( $row ) ) {
						$this->mcq[$e_key]['settings']['rows'][$r_key] = apply_filters( 'ipt_uif_label', $row );
					}
				}
			}
			if ( isset( $element['settings'] ) && isset( $element['settings']['columns'] ) && is_array( $element['settings']['columns'] ) ) {
				foreach ( $element['settings']['columns'] as $r_key => $row ) {
					if ( is_string( $row ) ) {
						$this->mcq[$e_key]['settings']['columns'][$r_key] = apply_filters( 'ipt_uif_label', $row );
					}
				}
			}
		}
		unset( $e_key, $element );
		endif;

		if ( ! empty( $this->freetype ) && is_array( $this->freetype ) ) :
		foreach ( $this->freetype as $e_key => $element ) {
			$this->freetype[$e_key]['title'] = apply_filters( 'ipt_uif_label', $element['title'] );
			$this->freetype[$e_key]['subtitle'] = apply_filters( 'ipt_uif_label', $element['subtitle'] );
		}
		unset( $e_key, $element );
		endif;

		if ( ! empty( $this->design ) && is_array( $this->design ) ) :
		foreach ( $this->design as $e_key => $element ) {
			$this->design[$e_key]['title'] = apply_filters( 'ipt_uif_label', $element['title'] );
			$this->design[$e_key]['subtitle'] = apply_filters( 'ipt_uif_label', $element['subtitle'] );
		}
		unset( $e_key, $element );
		endif;

		if ( ! empty( $this->pinfo ) && is_array( $this->pinfo ) ) :
		foreach ( $this->pinfo as $e_key => $element ) {
			$this->pinfo[$e_key]['title'] = apply_filters( 'ipt_uif_label', $element['title'] );
			$this->pinfo[$e_key]['subtitle'] = apply_filters( 'ipt_uif_label', $element['subtitle'] );
			if ( isset( $element['settings'] ) && isset( $element['settings']['options'] ) && is_array( $element['settings']['options'] ) ) {
				foreach ( $element['settings']['options'] as $o_key => $op ) {
					if ( is_array( $op ) ) {
						if ( isset( $op['label'] ) ) {
							$this->pinfo[$e_key]['settings']['options'][$o_key]['label'] = apply_filters( 'ipt_uif_label', $op['label'] );
						}
					} elseif ( is_string( $op ) ) {
						$this->pinfo[$e_key]['settings']['options'][$o_key] = apply_filters( 'ipt_uif_label', $op );
					}
				}
			}
		}
		endif;
	}

	public function social_share_buttons( $use_image = false ) {
		if ( $this->settings['social']['show'] == false ) {
			return '';
		}
		$ui = IPT_Plugin_UIF_Front::instance();
		$op = $this->settings['social'];
		$standalone_url = IPT_FSQM_Form_Elements_Static::standalone_permalink_parts( $this->form_id );
		$format_string = $this->get_format_string();
		$op['image'] = str_replace( '%LOGO%', $this->settings['theme']['logo'], $op['image'] );
		$op['url'] = str_replace( '%SELF%', $standalone_url['url'], $op['url'] );
		$op['title'] = str_replace( '%NAME%', $this->name, $op['title'] );
		$op['description'] = str_replace( array_keys( $format_string ), array_values( $format_string ), $op['description'] );
		$active_sites = $op['sites'];
		unset( $op['sites'] );

		// Add tracking information if present
		if ( $this->settings['submission']['url_track'] == true ) {
			if ( $this->settings['social']['follow_on_social'] == true && $this->data->url_track != '' ) {
				$op['url'] = add_query_arg( $this->settings['submission']['url_track_key'], $this->data->url_track, $op['url'] );
			} else if ( $this->settings['social']['auto_append_user'] == true && is_user_logged_in() ) {
				$op['url'] = add_query_arg( $this->settings['submission']['url_track_key'], wp_get_current_user()->user_login, $op['url'] );
			}
		}
		$op = array_map( 'trim', $op );
		$op = array_map( 'urlencode', $op );

		// Facebook
		if ( $op['facebook_app'] == '' ) {
			$facebook_url = 'https://www.facebook.com/sharer.php?s=100&p[url]=' . $op['url'] . '&p[title]=' . $op['title'];
			if ( $op['image'] != '' ) {
				$facebook_url .= '&p[images][0]=' . $op['image'];
			}
			if ( $op['description'] != '' ) {
				$facebook_url .= '&p[summary]=' . $op['description'];
			}
		} else {
			$facebook_url = 'https://www.facebook.com/dialog/share?app_id=' . $op['facebook_app'] .
				'&href=' . $op['url'] .
				'&name=' . $op['title'] .
				'&redirect_uri=' . $op['fb_url'];

			if ( $op['image'] != '' ) {
				$facebook_url .= '&picture=' . $op['image'];
			}
			if ( $op['description'] != '' ) {
				$facebook_url .= '&quote=' . $op['description'];
			}
			// Also add the hash tag from twitter
			if ( '' != $op['fb_hash'] ) {
				$facebook_url .= '&hashtag=' . urlencode( '#' ) . $op['fb_hash'];
			}
		}

		// Twitter
		$twitter_url = 'https://twitter.com/share?url=' . $op['url'] . '&text=' . $op['title'] . ' ' . $op['description'];
		if ( $op['twitter_via'] != '' ) {
			$twitter_url .= '&via=' . $op['twitter_via'];
		}
		if ( $op['twitter_hash'] != '' ) {
			$twitter_url .= '&hashtags=' . $op['twitter_hash'];
		}

		// Google+
		$google_url = 'https://plus.google.com/share?url=' . $op['url'] . '&text=' . $op['title'] . ' ' . $op['description'];

		// Pinterest
		if ( $op['image'] != '' ) {
			$pinterest_url = 'https://pinterest.com/pin/create/bookmarklet/?media=' . $op['image'] . '&url=' . $op['url'] . '&description=' . $op['title'] . ' ' . $op['description'];
		}

		$html = array(
			'facebook_url' => '<i class="ipticm ipt-icomoon-facebook3"></i>',
			'twitter_url' => '<i class="ipticm ipt-icomoon-twitter3"></i>',
			'google_url' => '<i class="ipticm ipt-icomoon-googleplus3"></i>',
		);
		if ( $op['image'] != '' ) {
			$html['pinterest_url'] = '<i class="ipticm ipt-icomoon-pinterest2"></i>';
		}
		if ( $use_image ) {
			$html = array(
				'facebook_url' => '<img src="' . $ui->get_image_for_icon( 0xe028, $this->settings['email_template']['accent_color'] ) . '" alt="Facebook" />',
				'twitter_url' => '<img src="' . $ui->get_image_for_icon( 0xe02b, $this->settings['email_template']['accent_color'] ) . '" alt="Twitter" />',
				'google_url' => '<img src="' . $ui->get_image_for_icon( 0xe025, $this->settings['email_template']['accent_color'] ) . '" alt="Google Plus" />',
			);
			if ( $op['image'] != '' ) {
				$html['pinterest_url'] = '<img src="' . $ui->get_image_for_icon( 0xe03f, $this->settings['email_template']['accent_color'] ) . '" alt="Pinterest" />';
			}
		}
		$return = '';
		foreach ( $html as $key => $val ) {
			if ( $active_sites[$key] == false ) {
				continue;
			}
			$return .= '<a href="' . ${$key} . '" target="_blank">' . $val . '</a>&nbsp;&nbsp;';
		}
		return apply_filters( 'ipt_fsqm_filter_social_buttons', $return, $html, $this->settings['social'], $this->form_id, $this->data_id );
	}

	/*==========================================================================
	 * DEFAULT ELEMENTS - OVERRIDE
	 *========================================================================*/
	public function build_heading( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$icon = '';
		if ( $element_data['settings']['icon'] != 'none' ) {
			$ui = IPT_Plugin_UIF_Front::instance();
			$icon = '<img src="' . $ui->get_image_for_icon( $element_data['settings']['icon'], $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		}
		?>
<?php if ( $icon !== '' ) : ?>
	<td style="<?php echo $this->email_styling['icons']; ?>" class="icons"><?php echo $icon; ?></td>
<?php endif; ?>
<th style="<?php echo $this->email_styling['th']; ?>" scope="row" colspan="<?php echo $icon !== '' ? '4' : '5'; ?>"><?php printf( '<%1$s>%2$s</%1$s>', $element_data['settings']['type'], $element_data['title'] ); ?></th>
		<?php
	}

	public function build_richtext( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$icon = '';
		$ui = IPT_Plugin_UIF_Front::instance();
		if ( $element_data['settings']['icon'] != 'none' ) {
			$icon = '<img src="' . $ui->get_image_for_icon( $element_data['settings']['icon'], $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		}
		?>
<td style="<?php echo $this->email_styling['icons']; ?>" class="icons"><?php echo $icon; ?></td>
<th style="<?php echo $this->email_styling['th']; ?>" scope="row" colspan="4"><?php printf( '<%1$s>%2$s</%1$s>', 'h2', $element_data['title'] ); ?></th>
</tr><tr>
<td colspan="5">
	<div class="ipt_uif_richtext">
		<?php echo apply_filters( 'ipt_uif_richtext', $element_data['description'] ); ?>
		<?php $ui->clear(); ?>
	</div>
</td>
		<?php
	}

	public function build_embed( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		?>
<td colspan="5">
	<div class="ipt_fsqm_embed">
		<?php echo $element_data['description']; ?>
	</div>
</td>
		<?php
	}

	public function build_iconbox( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$ui = IPT_Plugin_UIF_Front::instance();
		?>
<td colspan="5"><div class="ipt_uif_richtext" style="text-align: <?php echo $element_data['settings']['align']; ?>; font-size: 1.5em;">
	<?php foreach ( $element_data['settings']['elements'] as $icon_item ) : ?>
	<?php
	$icon = '';
	if ( $icon_item['icon'] != 'none' ) {
		$icon = '<img src="' . $ui->get_image_for_icon( $element_data['settings']['icon'], $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" /> ';
	}
	?>
	<a href="<?php echo $icon_item['url']; ?>" style="display: inline-block; padding: 5px; background-color: #fefefe; color: #333; border: 1px solid #efefef; text-decoration: none;"><?php echo $icon . $icon_item['text']; ?></a>
	<?php endforeach; ?>
</div></td>
		<?php
	}

	public function build_radio( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_mcqs( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_checkbox( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_mcqs( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_select( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_mcqs( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_thumbselect( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_mcqs( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_pricing_table( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$ui = IPT_Plugin_UIF_Front::instance();
		$checked = '<img src="' . $ui->get_image_for_icon( 0xe190, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		$unchecked = '<img src="' . $ui->get_image_for_icon( 0xe191, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		$tr = false;
		?>
		<?php $this->make_element_headers(
			$element_data['title'],
			$element_data['subtitle'],
			$element_data['description']
		); ?>
		<?php foreach ( $element_data['settings']['options'] as $o_key => $option ) : ?>
			<?php if ( true == $this->settings['summary']['hide_options'] && ! in_array( (string) $o_key, $submission_data['options'], true ) ) : ?>
				<?php continue; ?>
			<?php endif; ?>
			<?php if ( $tr ) : ?>
				</tr><tr style="<?php echo $this->email_styling['tr']; ?>">
			<?php endif; ?>
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
				<?php if ( in_array( (string) $o_key, $submission_data['options'], true ) ) : ?>
					<?php echo $checked; ?>
				<?php else : ?>
					<?php echo $unchecked; ?>
				<?php endif; ?>
			</td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="3">
				<?php echo $option['label']; ?>
			</td>
			<td style="<?php echo $this->email_styling['td']; ?>; text-align: right;" colspan="1">
				<?php echo $element_data['settings']['currency'] . number_format_i18n( $option['price'], 2 ); ?>
			</td>
			<?php $tr = true; ?>
		<?php endforeach; ?>
		<?php
	}

	public function build_slider( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_slider( $element_data['title'], $element_data['subtitle'], $submission_data, $element_data['description'], $element_data['settings']['prefix'], $element_data['settings']['suffix'], true );
	}

	public function build_range( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_range( $element_data['title'], $element_data['subtitle'], $submission_data, $element_data['description'], $element_data['settings']['prefix'], $element_data['settings']['suffix'], true );
	}

	public function build_spinners( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$tr = false;
?>
		<?php $this->make_element_headers(
			$element_data['title'],
			$element_data['subtitle'],
			$element_data['description']
		); ?>
		<?php foreach ( $element_data['settings']['options'] as $o_key => $option ) : ?>
		<?php
		// Compatibility with v-2.5.0
		if ( ! is_array( $option ) ) {
			$option = array(
				'label' => $option,
			);
		}
		// Check if data exists in submission
		if ( ! isset( $submission_data['options'][ $o_key ] ) ) {
			$submission_data['options'][ $o_key ] = '';
		}
		?>
		<?php if ( $tr ) echo '</tr><tr style="' . $this->email_styling['tr'] . '">'; ?>
		<?php $this->make_slider_inner( $option['label'], $submission_data['options'][$o_key] ); ?>
		<?php $tr = true; ?>
		<?php endforeach; ?>
		<?php
	}

	public function build_grading( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$score = $this->settings['summary']['individual_score'] && isset( $submission_data['scoredata'] ) && !empty( $submission_data['scoredata'] ) && isset( $submission_data['scoredata']['max_score'] );
		$tr = false;
?>
		<?php $this->make_element_headers(
			$element_data['title'],
			$element_data['subtitle'],
			$element_data['description']
		); ?>
		<?php foreach ( $element_data['settings']['options'] as $o_key => $option ) : ?>
		<?php // backward compatibility -2.4.0
		if ( !is_array( $option ) ) {
			$option = array(
				'label' => $option,
				'prefix' => '',
				'suffix' => '',
			);
		}
		?>
		<?php if ( $tr ) echo '</tr><tr style="' . $this->email_styling['tr'] . '">'; ?>
		<?php if ( $element_data['settings']['range'] == true ) : ?>
		<?php $this->make_range_inner( $option['label'], $submission_data['options'][$o_key], $option['prefix'], $option['suffix'] ); ?>
		<?php else : ?>
		<?php $this->make_slider_inner( $option['label'], $submission_data['options'][$o_key], $option['prefix'], $option['suffix'] ); ?>
		<?php endif; ?>
		<?php $tr = true; ?>
		<?php endforeach; ?>
		<?php if ( $score ) : ?>
		</tr>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons"><?php echo $this->score_img; ?></td>
			<th style="<?php echo $this->email_styling['th']; ?>" colspan="3"><?php echo $this->settings['summary']['score_title']; ?></th>
			<td style="<?php echo $this->email_styling['td'] . $this->email_styling['tdsm']; ?>" colspan="1">
				<?php echo $submission_data['scoredata']['score']; ?> / <?php echo $submission_data['scoredata']['max_score']; ?>
			</td>
		<?php endif; ?>
		<?php
	}

	public function build_likedislike( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$ui = IPT_Plugin_UIF_Front::instance();
		// Shortcuts for the images
		$likedislike = array(
			'like' => $ui->get_image_for_icon( 0xf164, $this->settings['theme']['icon']['color'] ),
			'dislike' => $ui->get_image_for_icon( 0xf165, $this->settings['theme']['icon']['color'] ),
		);
		if ( isset( $likedislike[$submission_data['value']] ) ) {
			$img = '<img src="' . $likedislike[ $submission_data['value'] ] . '" height="16" width="16" />';
			$value = $element_data['settings'][ $submission_data['value'] ];
		} else {
			$img = '<img src="' . $ui->get_image_for_icon( 0xf057, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
			$value = __( 'None selected', 'ipt_fsqm' );
		}
		?>
		<?php $this->make_element_headers(
			$element_data['title'],
			$element_data['subtitle'],
			$element_data['description']
		); ?>
		<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
			<?php echo $img; ?>
		</td>
		<td style="<?php echo $this->email_styling['td']; ?>" colspan="4">
			<?php echo $value; ?>
		</td>
	<?php if ( $submission_data['feedback'] != '' ) : ?>
	</tr>
	<tr style="<?php echo $this->email_styling['tr']; ?>">
		<td class="icons" style="<?php echo $this->email_styling['icons']; ?>">
			<?php echo '<img src="' . $ui->get_image_for_icon( 0xf040, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />'; ?>
		</td>
		<td colspan="4" style="<?php echo $this->email_styling['td']; ?>"><?php echo wpautop( esc_textarea( $submission_data['feedback'] ) ); ?></td>
	<?php endif; ?>
		<?php
	}

	public function build_smileyrating( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$ui = IPT_Plugin_UIF_Front::instance();
		$score = $this->settings['summary']['individual_score'] && $this->has_scoredata( $submission_data );
		$smileys_selected = array(
			'frown' => $ui->get_image_for_icon( 0xe0ff, $this->settings['theme']['icon']['color'] ),
			'sad' => $ui->get_image_for_icon( 0xe0f7, $this->settings['theme']['icon']['color'] ),
			'neutral' => $ui->get_image_for_icon( 0xe107, $this->settings['theme']['icon']['color'] ),
			'happy' => $ui->get_image_for_icon( 0xe0f3, $this->settings['theme']['icon']['color'] ),
			'excited' => $ui->get_image_for_icon( 0xe0f1, $this->settings['theme']['icon']['color'] ),
		);
		if ( isset( $smileys_selected[$submission_data['option']] ) ) {
			$img = '<img src="' . $smileys_selected[$submission_data['option']] . '" height="16" width="16" />';
			$value = $element_data['settings']['labels'][$submission_data['option']];
		} else {
			$img = '<img src="' . $ui->get_image_for_icon( 0xf057, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
			$value = __( 'None selected', 'ipt_fsqm' );
		}
		?>
		<?php $this->make_element_headers(
			$element_data['title'],
			$element_data['subtitle'],
			$element_data['description']
		); ?>
		<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
			<?php echo $img; ?>
		</td>
		<td style="<?php echo $this->email_styling['td']; ?>" colspan="4">
			<?php echo $value; ?>
		</td>
	<?php if ( $submission_data['feedback'] != '' ) : ?>
	</tr>
	<tr style="<?php echo $this->email_styling['tr']; ?>">
		<td class="icons" style="<?php echo $this->email_styling['icons']; ?>">
			<?php echo '<img src="' . $ui->get_image_for_icon( 0xf040, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />'; ?>
		</td>
		<td colspan="4" style="<?php echo $this->email_styling['td']; ?>"><?php echo wpautop( esc_textarea( $submission_data['feedback'] ) ); ?></td>
	<?php endif; ?>
	<?php if ( $score ) : ?>
	</tr>
	<tr style="<?php echo $this->email_styling['tr']; ?>">
		<td style="<?php echo $this->email_styling['icons']; ?>" class="icons"><?php echo $this->score_img; ?></td>
		<th style="<?php echo $this->email_styling['th']; ?>" colspan="2"><?php echo $this->settings['summary']['score_title']; ?></th>
		<td style="<?php echo $this->email_styling['td'] . $this->email_styling['tdsm']; ?>" colspan="2">
			<?php echo $submission_data['scoredata']['score']; ?> / <?php echo $submission_data['scoredata']['max_score']; ?>
		</td>
	<?php endif; ?>
		<?php
	}

	public function build_starrating( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_ratings( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_scalerating( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_ratings( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context, 'scale' );
	}

	public function build_matrix_dropdown( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$rows = $element_data['settings']['rows'];
		$columns = $element_data['settings']['columns'];
		$values = $submission_data['rows'];

		if ( !is_array( $values ) ) {
			$values = (array) $values;
		}

		$score = $this->settings['summary']['individual_score'] && $this->has_scoredata( $submission_data );
		?>
<?php $this->make_element_headers(
	$element_data['title'],
	$element_data['subtitle'],
	$element_data['description']
); ?>
<td style="<?php echo $this->email_styling['td']; ?> padding: 0;" colspan="5" class="matrix">
	<table style="<?php echo $this->email_styling['inner_table']; ?>">
		<thead style="<?php echo $this->email_styling['thead']; ?> border-top: 0 none; border-left: 0 none; border-right: 0 none;">
			<tr style="<?php echo $this->email_styling['tr']; ?> border-top: 0 none; border-left: 0 none; border-right: 0 none;">
				<th style="<?php echo $this->email_styling['th']; ?> border-top: 0 none; border-left: 0 none;" scope="col"></th>
				<?php foreach ( $columns as $c_key => $column ) : ?>
				<th style="<?php echo $this->email_styling['th']; ?> border-top: 0 none; border-right: 0 none;" scope="col">
					<?php echo $column; ?>
				</th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tfoot style="<?php echo $this->email_styling['tfoot']; ?> border-bottom: 0 none; border-left: 0 none; border-right: 0 none;">
			<tr style="<?php echo $this->email_styling['tr']; ?> border-bottom: 0 none; border-left: 0 none; border-right: 0 none;">
				<th style="<?php echo $this->email_styling['th']; ?> border-bottom: 0 none; border-left: 0 none;" scope="col"></th>
				<?php foreach ( $columns as $c_key => $column ) : ?>
				<th style="<?php echo $this->email_styling['th']; ?> border-bottom: 0 none; border-right: 0 none;" scope="col">
					<?php echo $column; ?>
				</th>
				<?php endforeach; ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ( $rows as $r_key => $row ) : ?>
			<?php
			if ( ! isset( $values[$r_key] ) ) {
				$values[$r_key] = array();
			}
			?>
			<tr style="<?php echo $this->email_styling['tr']; ?> border-left: 0 none; border-right: 0 none;">
				<th style="<?php echo $this->email_styling['th']; ?> border-left: 0 none;" scope="row"><?php echo $row; ?></th>
				<?php foreach ( $columns as $c_key => $column ) : ?>
				<td style="<?php echo $this->email_styling['td']; ?> border-right: 0 none;" class="icons_matrix">
					<?php if ( isset( $values[ $r_key ][ $c_key ] ) ) : ?>
						<?php if ( is_array( $values[ $r_key ][ $c_key ] ) ) : ?>
							<?php foreach ( $values[ $r_key ][ $c_key ] as $o_key ) : ?>
								<?php if ( isset( $element_data['settings']['options'][ $o_key ] ) ) : ?>
									<p><?php echo $element_data['settings']['options'][ $o_key ]['label']; ?></p>
								<?php endif; ?>
							<?php endforeach; ?>
						<?php else : ?>
							<?php if ( isset( $element_data['settings']['options'][ $values[ $r_key ][ $c_key ] ] ) ) : ?>
								<?php echo $element_data['settings']['options'][ $values[ $r_key ][ $c_key ] ]['label']; ?>
							<?php endif; ?>
						<?php endif; ?>
					<?php else : ?>
						<?php _e( 'Unattempted', 'ipt_fsqm' ); ?>
					<?php endif; ?>
				</td>
				<?php endforeach; ?>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</td>
<?php if ( $score ) : ?>
</tr>
<tr style="<?php echo $this->email_styling['tr']; ?>">
	<td style="<?php echo $this->email_styling['icons']; ?>" class="icons"><?php echo $this->score_img; ?></td>
	<th style="<?php echo $this->email_styling['th']; ?>" colspan="2"><?php echo $this->settings['summary']['score_title']; ?></th>
	<td style="<?php echo $this->email_styling['td'] . $this->email_styling['tdsm']; ?>" colspan="2">
		<?php echo $submission_data['scoredata']['score']; ?> / <?php echo $submission_data['scoredata']['max_score']; ?>
	</td>
<?php endif; ?>
		<?php
	}

	public function build_matrix( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		// Shortcut variables
		$rows = $element_data['settings']['rows'];
		$columns = $element_data['settings']['columns'];
		$values = $submission_data['rows'];
		$multiple = $element_data['settings']['multiple'];
		$ui = IPT_Plugin_UIF_Front::instance();

		// Paths to icon
		if ( $multiple ) {
			$checked = '<img src="' . $ui->get_image_for_icon( 0xe192, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
			$unchecked = '<img src="' . $ui->get_image_for_icon( 0xe050, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		} else {
			$checked = '<img src="' . $ui->get_image_for_icon( 0xe190, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
			$unchecked = '<img src="' . $ui->get_image_for_icon( 0xe191, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		}

		if ( !is_array( $values ) ) {
			$values = (array) $values;
		}
		$rowspans = 1;
		$score = $this->settings['summary']['individual_score'] && $this->has_scoredata( $submission_data );
		if ( $score ) {
			$rowspans += 1;
		}
?>
<?php $this->make_element_headers(
	$element_data['title'],
	$element_data['subtitle'],
	$element_data['description']
); ?>
<td style="<?php echo $this->email_styling['td']; ?> padding: 0;" colspan="5" class="matrix">
<table style="<?php echo $this->email_styling['inner_table']; ?>">
	<thead style="<?php echo $this->email_styling['thead']; ?> border-top: 0 none; border-left: 0 none; border-right: 0 none;">
		<tr style="<?php echo $this->email_styling['tr']; ?> border-top: 0 none; border-left: 0 none; border-right: 0 none;">
			<th style="<?php echo $this->email_styling['th']; ?> border-top: 0 none; border-left: 0 none;" scope="col"></th>
			<?php foreach ( $columns as $c_key => $column ) : ?>
			<th style="<?php echo $this->email_styling['th']; ?> border-top: 0 none; border-right: 0 none;" scope="col">
				<?php echo $column; ?>
				<?php if ( $score && isset( $element_data['settings']['scores'] ) && is_array( $element_data['settings']['scores'] ) && isset( $element_data['settings']['scores'][$c_key] ) && '' != trim( $element_data['settings']['scores'][$c_key] ) ) : ?>
				<br />
				<span class="description" style="<?php echo $this->email_styling['description']; ?>"><?php printf( __( 'Score: %s', 'ipt_fsqm' ), $element_data['settings']['scores'][$c_key] ); ?></span>
				<?php endif; ?>
			</th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tfoot style="<?php echo $this->email_styling['tfoot']; ?> border-bottom: 0 none; border-left: 0 none; border-right: 0 none;">
		<tr style="<?php echo $this->email_styling['tr']; ?> border-bottom: 0 none; border-left: 0 none; border-right: 0 none;">
			<th style="<?php echo $this->email_styling['th']; ?> border-bottom: 0 none; border-left: 0 none;" scope="col"></th>
			<?php foreach ( $columns as $c_key => $column ) : ?>
			<th style="<?php echo $this->email_styling['th']; ?> border-bottom: 0 none; border-right: 0 none;" scope="col">
				<?php echo $column; ?>
				<?php if ( $score && isset( $element_data['settings']['scores'] ) && is_array( $element_data['settings']['scores'] ) && isset( $element_data['settings']['scores'][$c_key] ) && '' != trim( $element_data['settings']['scores'][$c_key] ) ) : ?>
				<br />
				<span class="description" style="<?php echo $this->email_styling['description']; ?>"><?php printf( __( 'Score: %s', 'ipt_fsqm' ), $element_data['settings']['scores'][$c_key] ); ?></span>
				<?php endif; ?>
			</th>
			<?php endforeach; ?>
		</tr>
	</tfoot>
	<tbody style="<?php echo $this->email_styling['tbody']; ?>">
		<?php foreach ( $rows as $r_key => $row ) : ?>
		<?php
			if ( !isset( $values[$r_key] ) ) {
				$values[$r_key] = array();
			} else {
			$values[$r_key] = (array) $values[$r_key];
		}
?>
		<tr style="<?php echo $this->email_styling['tr']; ?> border-left: 0 none; border-right: 0 none;">
			<th style="<?php echo $this->email_styling['th']; ?> border-left: 0 none;" scope="row"><?php echo $row; ?></th>
			<?php foreach ( $columns as $c_key => $column ) : ?>
			<td style="<?php echo $this->email_styling['td_center']; ?> border-right: 0 none;" class="icons_matrix">
				<?php if ( in_array( (string) $c_key, $values[$r_key], true ) ) : ?>
				<?php echo $checked; ?>
				<?php else : ?>
				<?php echo $unchecked; ?>
				<?php endif; ?>
			</td>
			<?php endforeach; ?>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
</td>
<?php if ( $score ) : ?>
</tr>
<tr style="<?php echo $this->email_styling['tr']; ?>">
	<td style="<?php echo $this->email_styling['icons']; ?>" class="icons"><?php echo $this->score_img; ?></td>
	<th style="<?php echo $this->email_styling['th']; ?>" colspan="2"><?php echo $this->settings['summary']['score_title']; ?></th>
	<td style="<?php echo $this->email_styling['td'] . $this->email_styling['tdsm']; ?>" colspan="2">
		<?php echo $submission_data['scoredata']['score']; ?> / <?php echo $submission_data['scoredata']['max_score']; ?>
	</td>
<?php endif; ?>
		<?php
	}

	public function build_toggle( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_s_checkbox( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_sorting( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_sortings( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_feedback_large( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_feedback_small( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_feedback_matrix( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$rows = $element_data['settings']['rows'];
		$columns = $element_data['settings']['columns'];
		$values = $submission_data['rows'];

		if ( ! is_array( $values ) ) {
			$values = (array) $values;
		}
		?>
<?php $this->make_element_headers(
	$element_data['title'],
	$element_data['subtitle'],
	$element_data['description']
); ?>
<td style="<?php echo $this->email_styling['td']; ?> padding: 0;" colspan="5" class="matrix">
	<table style="<?php echo $this->email_styling['inner_table']; ?>">
		<thead style="<?php echo $this->email_styling['thead']; ?> border-top: 0 none; border-left: 0 none; border-right: 0 none;">
			<tr style="<?php echo $this->email_styling['tr']; ?> border-top: 0 none; border-left: 0 none; border-right: 0 none;">
				<th style="<?php echo $this->email_styling['th']; ?> border-top: 0 none; border-left: 0 none;" scope="col"></th>
				<?php foreach ( $columns as $c_key => $column ) : ?>
				<th style="<?php echo $this->email_styling['th']; ?> border-top: 0 none; border-right: 0 none;" scope="col">
					<?php echo $column; ?>
				</th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tfoot style="<?php echo $this->email_styling['tfoot']; ?> border-bottom: 0 none; border-left: 0 none; border-right: 0 none;">
			<tr style="<?php echo $this->email_styling['tr']; ?> border-bottom: 0 none; border-left: 0 none; border-right: 0 none;">
				<th style="<?php echo $this->email_styling['th']; ?> border-bottom: 0 none; border-left: 0 none;" scope="col"></th>
				<?php foreach ( $columns as $c_key => $column ) : ?>
				<th style="<?php echo $this->email_styling['th']; ?> border-bottom: 0 none; border-right: 0 none;" scope="col">
					<?php echo $column; ?>
				</th>
				<?php endforeach; ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ( $rows as $r_key => $row ) : ?>
			<?php
			if ( ! isset( $values[$r_key] ) ) {
				$values[$r_key] = array();
			}
			?>
			<tr style="<?php echo $this->email_styling['tr']; ?> border-left: 0 none; border-right: 0 none;">
				<th style="<?php echo $this->email_styling['th']; ?> border-left: 0 none;" scope="row"><?php echo $row; ?></th>
				<?php foreach ( $columns as $c_key => $column ) : ?>
				<td style="<?php echo $this->email_styling['td']; ?> border-right: 0 none;" class="icons_matrix">
					<?php if ( isset( $values[$r_key][$c_key] ) ) : ?>
					<?php echo wpautop( esc_textarea( $values[$r_key][$c_key] ) ); ?>
					<?php else : ?>
					<?php _e( 'Unattempted', 'ipt_fsqm' ); ?>
					<?php endif; ?>
				</td>
				<?php endforeach; ?>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</td>
		<?php
	}

	public function build_gps( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		global $ipt_fsqm_settings;
		$ui = IPT_Plugin_UIF_Front::instance();
		if ( 'none' === $element_data['settings']['icon'] ) {
			$img = '';
		} else {
			$img = '<img src="' . $ui->get_image_for_icon( $element_data['settings']['icon'], $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		}
		$name_img = $ui->get_image_for_icon( 0xe0a9, $this->settings['theme']['icon']['color'] );
		$lat_long_img = $ui->get_image_for_icon( 0xe07c, $this->settings['theme']['icon']['color'] );
		?>
		<?php $this->make_element_headers(
			$element_data['title'],
			$element_data['subtitle'],
			$element_data['description']
		); ?>
		<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
			<?php echo $img; ?>
		</td>
		<td style="<?php echo $this->email_styling['td']; ?>" colspan="4">
			<?php if (  is_numeric( $submission_data['lat'] ) && is_numeric( $submission_data['long'] ) ) : ?>
			<?php
			$scale = 2;
			$size = array( 500, 300 );
			if ( $this->for_pdf_quick_preview ) {
				$scale = 1;
				$size = array( 400, 250 );
			}
			$map_url = sprintf( '//maps.googleapis.com/maps/api/staticmap?markers=%1$s,%2$s&zoom=%3$s&size=%5$s&scale=%4$d&key=%6$s', round( (float) $submission_data['lat'], 6 ), round( (float) $submission_data['long'], 6 ), $element_data['settings']['zoom'], $scale, implode( 'x', $size ), $ipt_fsqm_settings['gplaces_api'] );
			?>
			<img src="<?php echo $map_url; ?>" alt="" width="<?php echo $size[0]; ?>" height="<?php echo $size[1]; ?>" style="max-width: 100%; height: auto;" />
			<?php else : ?>
			<?php echo $element_data['settings']['nolocation_label']; ?>
			<?php endif; ?>
		</td>
	<?php if ( $submission_data['location_name'] != '' ) : ?>
	</tr>
	<tr>
		<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
			<?php echo '<img src="' . $name_img . '" height="16" width="16" />'; ?>
		</td>
		<td style="<?php echo $this->email_styling['td']; ?>" colspan="2">
			<?php echo $element_data['settings']['location_name_label']; ?>
		</td>
		<td style="<?php echo $this->email_styling['td']; ?>" colspan="2">
			<?php echo $submission_data['location_name']; ?>
		</td>
	<?php endif; ?>
	</tr>
	<tr>
		<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
			<?php echo '<img src="' . $lat_long_img . '" height="16" width="16" />'; ?>
		</td>
		<td style="<?php echo $this->email_styling['td']; ?>" colspan="2">
			<?php echo $element_data['settings']['lat_label']; ?>
		</td>
		<td style="<?php echo $this->email_styling['td']; ?>" colspan="2">
			<?php echo round( (float) $submission_data['lat'], 6 ); ?>
		</td>
	</tr>
	<tr>
		<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
			<?php echo '<img src="' . $lat_long_img . '" height="16" width="16" />'; ?>
		</td>
		<td style="<?php echo $this->email_styling['td']; ?>" colspan="2">
			<?php echo $element_data['settings']['long_label']; ?>
		</td>
		<td style="<?php echo $this->email_styling['td']; ?>" colspan="2">
			<?php echo round( (float) $submission_data['long'], 6 ); ?>
		</td>
		<?php
	}

	public function build_upload( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$uploader = new IPT_FSQM_Form_Elements_Uploader( $this->form_id, $key );
		$uploads = $uploader->get_uploads( $this->data_id );
		$ui = IPT_Plugin_UIF_Front::instance();
		if ( 'none' === $element_data['settings']['icon'] ) {
			$img = '';
		} else {
			$img = '<img src="' . $ui->get_image_for_icon( $element_data['settings']['icon'], $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		}
		?>
		<?php $this->make_element_headers(
			$element_data['title'],
			$element_data['subtitle'],
			$element_data['description']
		); ?>
		<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
			<?php echo $img; ?>
		</td>
		<?php if ( empty( $uploads ) ) : ?>
		<td style="<?php echo $this->email_styling['td']; ?>" colspan="4">
			<?php _e( 'No files uploaded.', 'ipt_fsqm' ); ?>
		</td>
		<?php else : ?>
			<?php $tr = false; ?>
			<?php foreach ( $uploads as $upload ) : ?>
			<?php if ( $tr ) echo '</tr><tr style="' . $this->email_styling['tr'] . '">'; ?>
			<?php if ( $tr ) : ?>
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
				<?php echo $img; ?>
			</td>
			<?php endif; ?>
			<td style="<?php echo $this->email_styling['td_upload']; ?>" colspan="4" class="upload_td">
				<?php if ( '' == $upload['guid'] ) : ?>
					<?php _e( 'Deleted', 'ipt_fsqm' ); ?>
				<?php else : ?>
				<a href="<?php echo $upload['guid']; ?>" target="_blank" title="<?php echo esc_attr( $upload['name'] ); ?>">
					<?php if ( $upload['thumb_url'] != '' ) : ?>
					<img src="<?php echo $upload['thumb_url']; ?>" alt="<?php echo esc_attr( $upload['name'] ); ?>" /> <br/>
					<?php endif; ?>
					<?php echo $upload['name'] . ' (' . $upload['mime_type'] . ')'; ?>
				</a>
				<?php endif; ?>
			</td>
			<?php $tr = true; ?>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php
	}

	public function build_signature( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$img = '';
		$ui = IPT_Plugin_UIF_Front::instance();
		if ( isset( $element_data['settings'] ) && isset( $element_data['settings']['icon'] ) ) {
			if ( 'none' === $element_data['settings']['icon'] ) {
				$img = '';
			} else {
				$img = '<img src="' . $ui->get_image_for_icon( $element_data['settings']['icon'], $this->settings['theme']['icon']['color'] ) . '" height="16" width="" />';
			}
		}
		$signature = $this->convert_jsignature_image( $submission_data['value'], $element_data['settings']['color'] );
		$width = 'max-width: 400px;';
		if ( $this->for_email_quick_preview == true ) {
			$width = 'width: 200px;';
		}
		?>
			<?php $this->make_element_headers(
				$element_data['title'],
				$element_data['subtitle'],
				$element_data['description']
			); ?>
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
				<?php echo $img; ?>
			</td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="4">
				<?php if ( $signature != '' ) : ?>
				<img src="data:image/png;base64,<?php echo $signature ?>" alt="<?php _e( 'Signature', 'ipt_fsqm' ); ?>" style="<?php echo $width; ?> height: auto;" />
				<?php endif; ?>
			</td>
		<?php
	}

	public function build_mathematical( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$img = '';
		$ui = IPT_Plugin_UIF_Front::instance();
		if ( isset( $element_data['settings'] ) && isset( $element_data['settings']['icon'] ) ) {
			if ( 'none' === $element_data['settings']['icon'] ) {
				$img = '';
			} else {
				$img = '<img src="' . $ui->get_image_for_icon( $element_data['settings']['icon'], $this->settings['theme']['icon']['color'] ) . '" height="16" width="" />';
			}
		}
?>
			<?php $this->make_element_headers(
				$element_data['title'],
				$element_data['subtitle'],
				$element_data['description']
			); ?>
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
				<?php echo $img; ?>
			</td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="4">
				<?php echo $element_data['settings']['prefix']; ?>
				<?php echo $img; ?>
				<strong style="font-family: Menlo, Monaco, Consolas, 'Courier New', monospace;"><?php echo number_format_i18n( (float) $submission_data['value'], $element_data['settings']['precision'] ); ?></strong>
				<?php echo $element_data['settings']['suffix']; ?>
			</td>
		<?php
	}

	public function build_payment( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$img = '';
		$ui = IPT_Plugin_UIF_Front::instance();
		if ( isset( $element_data['settings'] ) && isset( $element_data['settings']['icon'] ) ) {
			if ( 'none' === $element_data['settings']['icon'] ) {
				$img = '';
			} else {
				$img = '<img src="' . $ui->get_image_for_icon( $element_data['settings']['icon'], $this->settings['theme']['icon']['color'] ) . '" height="16" width="" />';
			}
		}
		?>
		<?php $this->make_element_headers(
			$element_data['title'],
			$element_data['subtitle'],
			$element_data['description']
		); ?>
		<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
			<?php echo $img; ?>
		</td>
		<td style="<?php echo $this->email_styling['td']; ?>; padding: 0;" class="data" colspan="4">
			<?php echo $this->get_transaction_status( true ); ?>
		</td>
		<?php
	}

	public function build_f_name( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_l_name( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_email( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_phone( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_p_name( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_p_email( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_p_phone( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_textinput( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_textarea( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_guestblog( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$ui = IPT_Plugin_UIF_Front::instance();
		if ( 'none' === $element_data['settings']['icon'] ) {
			$img = '';
		} else {
			$img = '<img src="' . $ui->get_image_for_icon( $element_data['settings']['icon'], $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		}
		$rowspan = 2;
		if ( ! empty( $submission_data['taxonomy'] ) ) {
			$rowspan += count( $submission_data['taxonomy'] );
		}
		if ( '' != $submission_data['bio'] ) {
			$rowspan++;
		}
		?>
		<?php $this->make_element_headers(
			$element_data['title'],
			$element_data['subtitle'],
			$element_data['description']
		); ?>
		<td style="<?php echo $this->email_styling['icons']; ?>" class="icons" rowspan="<?php echo $rowspan; ?>">
			<?php echo $img; ?>
		</td>
		<td colspan="4"><h3><?php echo $submission_data['title']; ?></h3></td>
	</tr>
	<tr>
		<td colspan="4"><?php echo '<div class="ipt_uif_richtext">' . $submission_data['value'] . '</div>'; ?></td>
		<?php if ( ! empty( $submission_data['taxonomy'] ) ) : ?>
			<?php foreach ( $submission_data['taxonomy'] as $taxonomy => $tax_selected ) : ?>
				<?php
				$tax_data = get_taxonomy( $taxonomy );
				?>
	</tr>
	<tr>
		<td colspan="4">
			<h4><?php echo $tax_data->labels->name; ?></h4>
			<ul class="ul-disc">
			<?php foreach( (array) $tax_selected as $term ) : ?>
				<?php
				if ( empty( $term ) ) {
					continue;
				}
				?>
				<li><?php echo get_term( $term, $taxonomy )->name; ?></li>
			<?php endforeach; ?>
			</ul>
		</td>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php if ( '' != $submission_data['bio'] ) : ?>
	</tr>
	<tr>
		<td colspan="4"><?php echo wpautop( $submission_data['bio'] ); ?></td>
		<?php endif; ?>
		<?php
	}

	public function build_password( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		// Decrypt password if needed
		if ( isset( $submission_data['encrypted'] ) && $submission_data['encrypted'] && '' !== $submission_data['value'] ) {
			$submission_data['value'] = $this->decrypt_password_field( $submission_data['value'] );
		}
		// Hide the value if needed
		$needs_reg_mods = $this->is_element_of_reg_field( 'pinfo', $key, 'password_id' );
		if ( $element_data['settings']['hide_in_summary'] || $needs_reg_mods ) {
			if ( '' !== $submission_data['value'] ) {
				$submission_data['value'] = str_repeat( '*', min( 10, strlen( $submission_data['value'] ) ) );
			}
		}
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_p_radio( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_mcqs( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context, false );
	}

	public function build_p_checkbox( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_mcqs( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context, false );
	}

	public function build_p_select( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_mcqs( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context, false );
	}

	public function build_s_checkbox( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$ui = IPT_Plugin_UIF_Front::instance();
		$checked = '<img src="' . $ui->get_image_for_icon( 0xe192, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		$unchecked = '<img src="' . $ui->get_image_for_icon( 0xe050, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		$rowspan = 1;
		if ( $element_data['description'] !== '' ) {
			$rowspan = 2;
		}
?>
			<td style="<?php echo $this->email_styling['icons']; ?>" colspan="1" rowspan="<?php echo $rowspan; ?>" class="icons">
				<?php if ( true == $submission_data['value'] ) : ?>
				<?php echo $checked; ?>
				<?php else : ?>
				<?php echo $unchecked; ?>
				<?php endif; ?>
			</td>
			<th style="<?php echo $this->email_styling['th'] . $this->email_styling['qth']; ?>" colspan="4" scope="row">
				<?php echo $element_data['title']; ?>
				<?php if ( $element_data['subtitle'] !== '' ) : ?>
					<br />
					<span class="description" style="<?php echo $this->email_styling['description']; ?>">
						<?php echo $element_data['subtitle']; ?>
					</span>
				<?php endif; ?>
			</th>
			<?php if ( $element_data['description'] !== '' ) : ?>
			</tr><tr>
				<td colspan="5" style="<?php echo $this->email_styling['td']; ?>">
					<div class="ipt_uif_richtext">
						<?php echo apply_filters( 'ipt_uif_richtext', $element_data['description'] ); ?>
					</div>
				</td>
			<?php endif; ?>
		<?php
	}

	public function build_address( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$ui = IPT_Plugin_UIF_Front::instance();

		$address = '<img src="' . $ui->get_image_for_icon( 0xe078, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		$recipient = '<img src="' . $ui->get_image_for_icon( 0xe0a0, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		$flag = '<img src="' . $ui->get_image_for_icon( 0xf024, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		$province = '<img src="' . $ui->get_image_for_icon( 0xf277, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		$zip = '<img src="' . $ui->get_image_for_icon( 0xe07c, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		$placeholders = array(
			'recipient',
			'line_one',
			'line_two',
			'line_three',
			'country',
			'province',
			'zip',
		);
		$rowspan = 0;
		$data_to_print = array();
		foreach ( $placeholders as $placeholder ) {
			if ( '' != $element_data['settings'][ $placeholder ] ) {
				$rowspan++;
				$icon = '';
				switch ( $placeholder ) {
					case 'recipient':
						$icon = $recipient;
						break;
					case 'line_one':
					case 'line_two':
					case 'line_three':
						$icon = $address;
						break;
					case 'country':
						$icon = $flag;
						break;
					case 'province':
						$icon = $province;
						break;
					case 'zip':
						$icon = $zip;
						break;
				}
				$data_to_print[] = array(
					'icon' => $icon,
					'value' => $submission_data['values'][ $placeholder ],
				);
			}
		}
?>
			<?php $this->make_element_headers(
				$element_data['title'],
				$element_data['subtitle'],
				$element_data['description']
			); ?>
			<?php if ( ! empty( $data_to_print ) ) : ?>
				<?php foreach ( $data_to_print as $key => $ad_data ) : ?>
					<td class="icons" style="<?php echo $this->email_styling['icons']; ?>">
						<?php echo $ad_data['icon']; ?>
					</td>
					<td style="<?php echo $this->email_styling['td']; ?>" colspan="4">
						<?php echo esc_html( $ad_data['value'] ); ?>
					</td>
					<?php if ( $key != ( count( $data_to_print ) - 1 ) ) : ?>
						</tr>
					<tr>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php else : ?>
				<td colspan="5"><?php _e( 'N/A', 'ipt_fsqm' ); ?></td>
			<?php endif; ?>
		<?php
	}

	public function build_keypad( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_datetime( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$date_formats = array(
			'yy-mm-dd' => 'Y-m-d',
			'mm/dd/yy' => 'm/d/Y',
			'dd.mm.yy' => 'd.m.Y',
			'dd-mm-yy' => 'd-m-Y',
		);
		$time_formats = array(
			'HH:mm:ss' => 'H:i:s',
			'hh:mm:ss TT' => 'h:i:s A',
		);
		$value = $submission_data['value'];
		$current_picker_timestamp = strtotime( $value );
		if ( $current_picker_timestamp != false ) {
			switch ( $element_data['settings']['type'] ) {
			case 'date' :
				$value = date( $date_formats[$element_data['settings']['date_format']], $current_picker_timestamp );
				break;
			case 'time' :
				$value = date( $time_formats[$element_data['settings']['time_format']], $current_picker_timestamp );
				break;
			case 'datetime' :
				$value = date( $date_formats[$element_data['settings']['date_format']] . ' ' . $time_formats[$element_data['settings']['time_format']], $current_picker_timestamp );
				break;
			}
		}
		$submission_data['value'] = $value;
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_p_sorting( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_sortings( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context, false );
	}

	public function build_hidden( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_repeatable( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$ui = IPT_Plugin_UIF_Front::instance();

		if ( 'none' === $element_data['settings']['icon'] ) {
			$img = '';
		} else {
			$img = '<img src="' . $ui->get_image_for_icon( $element_data['settings']['icon'], $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		}

		$rowspan = count( (array) $element_data['settings']['group'] ) + 1;
		?>
		<?php $this->make_element_headers(
			$element_data['title'],
			$element_data['subtitle'],
			$element_data['description']
		); ?>
		<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
			<?php echo $img; ?>
		</td>
		<td style="<?php echo $this->email_styling['td']; ?>; padding: 0;" class="data" colspan="4">
			<table class="data-table">
				<tbody>
					<?php $i = 1; ?>
					<?php foreach ( (array) $submission_data['values'] as $i_key => $items ) : ?>
						<tr>
							<th style="<?php echo $this->email_styling['th']; ?> text-align: center; width: 4em;" colspan="1" rowspan="<?php echo $rowspan ?>"><?php printf( _x( '#%d', 'eform-repetable-heading', 'ipt_fsqm' ), $i++ ); ?></th>
						</tr>
						<?php foreach ( (array) $element_data['settings']['group'] as $g_key => $group ) : ?>
							<tr>
								<th style="<?php echo $this->email_styling['th']; ?>"><?php echo $group['title']; ?></th>
								<td>
									<?php if ( isset( $items[ $g_key ] ) ) : ?>
										<?php
										switch ( $group['type'] ) {
											case 'radio' :
												echo str_replace( '__', ' ', $items[ $g_key ] );
												break;
											case 'checkbox' :
											case 'select' :
											case 'select_multiple' :
												$options = array();
												foreach ( (array) $items[ $g_key ] as $op ) {
													$options[] = str_replace( '__', ' ', $op );
												}
												echo implode( '<br />', $options );
												break;
											case 'text' :
											case 'phone' :
											case 'url' :
											case 'email' :
											case 'number' :
											case 'integer' :
											case 'personName' :
											case 'password' :
											case 'textarea' :
												echo $items[ $g_key ];
												break;
											case 'date' :
												$date = DateTime::createFromFormat( 'Y-m-d', $items[ $g_key ] );
												if ( $date ) {
													echo $date->format( get_option( 'date_format' ) );
												} else {
													echo $items[ $g_key ];
												}
												break;
											case 'time' :
												$date = DateTime::createFromFormat( 'H:i:s', $items[ $g_key ] );
												if ( $date ) {
													echo $date->format( get_option( 'time_format' ) );
												} else {
													echo $items[ $g_key ];
												}
												break;
											case 'datetime' :
												$date = DateTime::createFromFormat( 'Y-m-d H:i:s', $items[ $g_key ] );
												if ( $date ) {
													echo $date->format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
												} else {
													echo $items[ $g_key ];
												}
												break;
										}
										?>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</tbody>
			</table>
		</td>
		<?php
	}

	/*==========================================================================
	 * Internal helper methods - Also made public
	 *========================================================================*/
	public function make_mcqs( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context, $score = true ) {
		$ui = IPT_Plugin_UIF_Front::instance();
		switch ( $element_data['type'] ) {
			case 'select' :
			case 'p_select' :
			case 'radio' :
			case 'p_radio' :
				$checked = '<img src="' . $ui->get_image_for_icon( 0xe190, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
				$unchecked = '<img src="' . $ui->get_image_for_icon( 0xe191, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
				if ( isset( $element_data['settings']['multiple'] ) && true == $element_data['settings']['multiple'] ) {
					$checked = '<img src="' . $ui->get_image_for_icon( 0xe192, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
					$unchecked = '<img src="' . $ui->get_image_for_icon( 0xe050, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
				}
				break;
			case 'thumbselect' :
				if ( $element_data['settings']['multiple'] ) {
					$checked = '<img src="' . $ui->get_image_for_icon( 0xe192, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
					$unchecked = '<img src="' . $ui->get_image_for_icon( 0xe050, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
				} else {
					$checked = '<img src="' . $ui->get_image_for_icon( 0xe190, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
					$unchecked = '<img src="' . $ui->get_image_for_icon( 0xe191, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
				}
				break;
			default :
				$checked = '<img src="' . $ui->get_image_for_icon( 0xe192, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
				$unchecked = '<img src="' . $ui->get_image_for_icon( 0xe050, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		}

		$score = $this->settings['summary']['individual_score'] && $score && $this->has_scoredata( $submission_data );
		$all_scores = array();
		$correct_ops = array();
		$positive_ops = [];
		// Since the highlight can work while not showing score data, we don't
		// check for `if ( $score && ... )`
		if ( $this->is_highlight_score() && $this->has_scoredata( $submission_data ) ) {
			foreach ( $element_data['settings']['options'] as $o_key => $op ) {
				$all_scores[ $o_key ] = (float) $op['score'];
				if ( (float) $op['score'] > 0 ) {
					$positive_ops[] = $o_key;
				}
			}
			unset( $o_key, $op );
			$maxp_score = max( $all_scores );
			if ( $this->settings['summary']['positive_correct'] ) {
				$correct_ops = $positive_ops;
			} else {
				$correct_ops = array_keys( $all_scores, $maxp_score, true );
			}
		}

		$tr = false;
?>
			<?php $this->make_element_headers(
				$element_data['title'],
				$element_data['subtitle'],
				$element_data['description']
			); ?>
			<?php foreach ( $element_data['settings']['options'] as $o_key => $op ) : ?>
			<?php if ( $this->settings['summary']['hide_options'] == true && ! in_array( (string) $o_key, $submission_data['options'], true ) ) : ?>
			<?php continue; ?>
			<?php endif; ?>
			<?php if ( $tr ) echo '</tr><tr style="' . $this->email_styling['tr'] . '">'; ?>
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
				<?php if ( in_array( (string) $o_key, $submission_data['options'], true ) ) : ?>
				<?php echo $checked; ?>
				<?php else : ?>
				<?php echo $unchecked; ?>
				<?php endif; ?>
			</td>
			<?php if ( $this->is_highlight_score() && in_array( (int) $o_key, $correct_ops ) ) : ?>
			<td style="<?php echo $this->email_styling['tdc']; ?>" colspan="4">
			<?php else : ?>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="4">
			<?php endif; ?>
				<?php if ( $element_data['type'] == 'thumbselect' ) : ?>
					<div style="color: inherit; font-weight: inherit;">
						<img style="display: block; margin: 0 0 10px;" src="<?php echo esc_attr( $op['image'] ); ?>" alt="" height="<?php echo esc_attr( $element_data['settings']['height'] ); ?>" width="<?php echo esc_attr( $element_data['settings']['width'] ); ?>" />
						<?php echo $op['label']; ?>
					</div>
				<?php else: ?>
					<?php echo $op['label']; ?>
				<?php endif; ?>
				<?php if ( $score && trim( $op['score'] ) != '' ) : ?>
					<?php echo ( $element_data['type'] == 'thumbselect' ? '' : '<br />' ); ?>
					<span class="description" style="<?php echo $this->email_styling['description']; ?>">
						(<?php echo __( 'Score', 'ipt_fsqm' ) . ' ' . $op['score']; ?>)
					</span>
				<?php endif; ?>
			</td>
			<?php $tr = true; ?>
			<?php endforeach; ?>
		<?php if ( isset( $element_data['settings']['others'] ) && $element_data['settings']['others'] == 'true' && ( ( $this->settings['summary']['hide_options'] == true && in_array( 'others', $submission_data['options'] ) ) || $this->settings['summary']['hide_options'] != true ) ) : ?>
		</tr><tr style="<?php echo $this->email_styling['tr']; ?>">
			<td class="icons" style="<?php echo $this->email_styling['icons']; ?>">
				<?php if ( in_array( 'others', $submission_data['options'] ) ) : ?>
				<?php echo $checked; ?>
				<?php else : ?>
				<?php echo $unchecked; ?>
				<?php endif; ?>
			</td>
			<td colspan="4" style="<?php echo $this->email_styling['td']; ?>">
				<?php echo $element_data['settings']['o_label']; ?>
			</td>
		<?php endif; ?>
		<?php if ( in_array( 'others', $submission_data['options'] ) ) : ?>
		</tr><tr style="<?php echo $this->email_styling['tr']; ?>">
			<td class="icons" style="<?php echo $this->email_styling['icons']; ?>">
				<?php echo '<img src="' . $ui->get_image_for_icon( 0xf040, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />'; ?>
			</td>
			<td colspan="4" style="<?php echo $this->email_styling['td']; ?>"><?php echo esc_textarea( $submission_data['others'] ); ?></td>
		<?php endif; ?>
		<?php if ( $this->settings['summary']['hide_options'] == true && 0 == count( $submission_data['options'] ) ) : ?>
		<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
			<?php echo $unchecked; ?>
		</td>
		<td style="<?php echo $this->email_styling['td']; ?>" colspan="4">
			<?php _e( 'Unattempted', 'ipt_fsqm' ); ?>
		</td>
		<?php endif; ?>
		<?php if ( $score ) : ?>
		</tr><tr style="<?php echo $this->email_styling['tr']; ?>">
			<td class="icons" style="<?php echo $this->email_styling['icons']; ?>"><?php echo $this->score_img; ?></td>
			<th colspan="2" style="<?php echo $this->email_styling['th']; ?>"><?php echo $this->settings['summary']['score_title']; ?></th>
			<td style="<?php echo $this->email_styling['td'] . $this->email_styling['tdsm']; ?>" colspan="2">
				<?php echo $submission_data['scoredata']['score']; ?> / <?php echo $submission_data['scoredata']['max_score']; ?>
			</td>
		<?php endif; ?>
		<?php
	}

	public function make_s_checkbox( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$ui = IPT_Plugin_UIF_Front::instance();
		$checked = '<img src="' . $ui->get_image_for_icon( 0xe192, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		$unchecked = '<img src="' . $ui->get_image_for_icon( 0xe050, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
?>
			<?php $this->make_element_headers(
				$element_data['title'],
				$element_data['subtitle'],
				$element_data['description']
			); ?>
			<td style="<?php echo $this->email_styling['icons']; ?>" colspan="1" class="icons">
				<?php if ( true == $submission_data['value'] ) : ?>
				<?php echo $checked; ?>
				<?php else : ?>
				<?php echo $unchecked; ?>
				<?php endif; ?>
			</td>
			<?php if ( $element_data['type'] == 'toggle' ) : ?>
			<td colspan="5" style="<?php echo $this->email_styling['td']; ?>">
				<?php if ( true == $submission_data['value'] ) : ?>
				<?php echo $element_data['settings']['on']; ?>
				<?php else : ?>
				<?php echo $element_data['settings']['off']; ?>
				<?php endif; ?>
			</td>
			<?php else : ?>
			<td colspan="5"></td>
			<?php endif; ?>
		<?php
	}

	public function make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$ui = IPT_Plugin_UIF_Front::instance();
		$img = '<img src="' . $ui->get_image_for_icon( 0xe001, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		if ( isset( $element_data['settings'] ) && isset( $element_data['settings']['icon'] ) ) {
			if ( 'none' === $element_data['settings']['icon'] ) {
				$img = '';
			} else {
				$img = '<img src="' . $ui->get_image_for_icon( $element_data['settings']['icon'], $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
			}
		} else {
			$new_image = '';
			switch ( $element_definition['type'] ) {
				case 'f_name' :
				case 'l_name' :
				case 'p_name' :
					$new_image = 0xe0a0;
					break;
				case 'email' :
				case 'p_email' :
					$new_image = 0xf0e0;
					break;
				case 'phone' :
				case 'p_phone' :
					$new_image = 0xe08c;
			}

			if ( $new_image != '' ) {
				$img = '<img src="' . $ui->get_image_for_icon( $new_image, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
			}
		}
		$show_score = false;
		if ( $element_definition['type'] == 'feedback_large' || $element_definition['type'] == 'feedback_small' ) {
			if ( $this->settings['summary']['individual_score'] && '' != $element_data['settings']['score'] && is_numeric( $element_data['settings']['score'] ) ) {
				$show_score = true;
			}
		}

		// Add inline prefix
		$value = esc_textarea( $submission_data['value'] );
		// For inline element, it should show a blank fixed-width line
		if ( '' == $submission_data['value'] && isset( $element_data['settings']['inline'] ) && $element_data['settings']['inline']['enabled'] ) {
			$value = str_repeat( '&nbsp;', 16 );
		}

		// If inline element, then add prefix and suffix
		if ( isset( $element_data['settings']['inline'] ) && $element_data['settings']['inline']['enabled'] ) {
			$prefix = '<span class="eform-inlinefb-prefix">' . $element_data['settings']['inline']['prefix'] . '</span>';
			$suffix = '<span class="eform-inlinefb-suffix">' . $element_data['settings']['inline']['suffix'] . '</span>';
			$value = '<span class="eform-inlinefb-value" style="text-decoration: underline;">' . $value . '</span>';
			$value = implode( ' ', [ $prefix, $value, $suffix ] );
		}

		$value = wpautop( $value );

		// Highlight correct score
		if ( $show_score && $this->is_highlight_score() && floatval( $submission_data['score'] ) > 0 ) {
			$value = $this->get_score_highlighted_value( $value );
		}
?>
			<?php $this->make_element_headers(
				$element_data['title'],
				$element_data['subtitle'],
				$element_data['description']
			); ?>
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
				<?php echo $img; ?>
			</td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="4">
				<?php echo $value; ?>
			</td>
			<?php if ( $show_score ) : ?>
		</tr>
		<tr>
			<td class="icons" style="<?php echo $this->email_styling['icons']; ?>"><?php echo $this->score_img; ?></td>
			<th colspan="2" style="<?php echo $this->email_styling['th']; ?>"><?php echo $this->settings['summary']['score_title']; ?></th>
			<td style="<?php echo $this->email_styling['td'] .  $this->email_styling['tdsm']; ?>" colspan="2">
				<?php
				echo ( $submission_data['score'] == ''
					? __( 'Unassigned', 'ipt_fsqm' )
					: $submission_data['score'] ); ?> / <?php echo $element_data['settings']['score']; ?>
			</td>
			<?php endif; ?>
		<?php
	}

	public function make_sortings( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context, $score = true ) {
		$ui = IPT_Plugin_UIF_Front::instance();
		if ( empty( $submission_data['order'] ) ) {
?>
			<?php $this->make_element_headers(
				$element_data['title'],
				$element_data['subtitle'],
				$element_data['description']
			); ?>
			<td class="icons">
				<?php echo '<img src="' . $ui->get_image_for_icon( 0xe117, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />'; ?>
			</td>
			<td colspan="4">
				<?php _e( 'N/A', 'ipt_fsqm' ); ?>
			</td>
			<?php
			return;
		}
		$rowspans = count( $element_data['settings']['options'] );
		$checked = '<img src="' . $ui->get_image_for_icon( 0xe18e, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		$unchecked = '<img src="' . $ui->get_image_for_icon( 0xe117, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		$point = '<img src="' . $ui->get_image_for_icon( 0xe10b, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		$correct_keys = array_keys( $element_data['settings']['options'] );
		$score = $this->settings['summary']['individual_score'] && $score && isset( $submission_data['scoredata'] ) && ! empty( $submission_data['scoredata'] ) && isset( $submission_data['scoredata']['max_score'] ) && $submission_data['scoredata']['max_score'] != 0;
		if ( $score ) {
			$rowspans += 1;
		}
		$tr = false;
?>
			<?php $this->make_element_headers(
				$element_data['title'],
				$element_data['subtitle'],
				$element_data['description']
			); ?>
			<?php foreach ( $submission_data['order'] as $o_pos => $o_key ) : ?>
			<?php if ( $tr ) echo '</tr><tr style="' . $this->email_styling['tr'] . '">'; ?>
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
				<?php if ( $score ) : ?>
				<?php if ( (string) $correct_keys[$o_pos] == $o_key ) : ?>
				<?php echo $checked; ?>
				<?php else : ?>
				<?php echo $unchecked; ?>
				<?php endif; ?>
				<?php else : ?>
				<?php echo $point; ?>
				<?php endif; ?>
			</td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="4">
				<?php
				if (  (string) $correct_keys[$o_pos] == $o_key && $this->is_highlight_score() ) {
					echo $this->get_score_highlighted_value( $element_data['settings']['options'][$o_key]['label'], 'span' );
				} else {
					echo $element_data['settings']['options'][$o_key]['label'];
				}
				?>
				<?php if ( $score ) : ?>
				<br /><span class="description" style="<?php echo $this->email_styling['description']; ?>">(<?php echo __( 'Correct Position:', 'ipt_fsqm' ) . ' ' . ( array_search( $o_key, $correct_keys ) + 1 ); ?><?php if ( trim( $element_data['settings']['options'][$o_key]['score'] ) != '' && $element_data['settings']['score_type'] == 'individual' ) echo ', ' . __( 'Score:', 'ipt_fsqm' ) . ' ' . $element_data['settings']['options'][$o_key]['score']; ?>)</span>
				<?php endif; ?>
			</td>
			<?php $tr = true; ?>
			<?php endforeach; ?>
		<?php if ( $score ) : ?>
		</tr>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons"><?php echo $this->score_img; ?></td>
			<th style="<?php echo $this->email_styling['th']; ?>" colspan="2"><?php echo $this->settings['summary']['score_title']; ?></th>
			<td style="<?php echo $this->email_styling['td'] . $this->email_styling['tdsm']; ?>" colspan="2">
				<?php echo $submission_data['scoredata']['score']; ?> / <?php echo $submission_data['scoredata']['max_score']; ?>
			</td>
		<?php endif; ?>
		<?php
	}

	public function make_ratings( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context, $icon = 'star' ) {
		$ui = IPT_Plugin_UIF_Front::instance();
		if ( $icon == 'star' ) {
			$fullstar = '<img src="' . $ui->get_image_for_icon( 0xf005, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
			$emptystar = '<img src="' . $ui->get_image_for_icon( 0xe0e8, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		} else {
			$fullstar = '<img src="' . $ui->get_image_for_icon( 0xe190, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
			$emptystar = '<img src="' . $ui->get_image_for_icon( 0xe191, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
		}

		$score = $this->settings['summary']['individual_score'] && isset( $submission_data['scoredata'] ) && !empty( $submission_data['scoredata'] ) && isset( $submission_data['scoredata']['max_score'] );

		$tr = false;
		?>
			<?php $this->make_element_headers(
				$element_data['title'],
				$element_data['subtitle'],
				$element_data['description']
			); ?>
			<?php foreach ( $element_data['settings']['options'] as $o_key => $op ) : ?>
			<?php
			$op_icon = '<img src="' . $ui->get_image_for_icon( 0xf164, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
			if ( isset( $submission_data['options'][$o_key] ) && $submission_data['options'][$o_key] < $element_data['settings']['max'] / 2 ) {
				$op_icon = '<img src="' . $ui->get_image_for_icon( 0xf165, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
			}

			?>
			<?php if ( $tr ) echo '</tr><tr style="' . $this->email_styling['tr'] . '">'; ?>
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons"><?php echo $op_icon; ?></td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="2">
				<?php echo $op; ?>
			</td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="2">
				<?php for ( $i = 1; $i <= $element_data['settings']['max']; $i++ ) : ?>
				<?php if ( isset( $submission_data['options'][$o_key] ) && (int) $submission_data['options'][$o_key] >= $i ) : ?>
				<?php echo $fullstar; ?>
				<?php else : ?>
				<?php echo $emptystar; ?>
				<?php endif; ?>
				<?php endfor; ?>
			</td>

			<?php $tr = true; ?>
			<?php endforeach; ?>
			<?php if ( $score ) : ?>
			</tr>
			<tr style="<?php echo $this->email_styling['tr']; ?>">
				<td style="<?php echo $this->email_styling['icons']; ?>" class="icons"><?php echo $this->score_img; ?></td>
				<th style="<?php echo $this->email_styling['th']; ?>" colspan="2"><?php echo $this->settings['summary']['score_title']; ?></th>
				<td style="<?php echo $this->email_styling['td'] . $this->email_styling['tdsm']; ?>" colspan="2">
					<?php echo $submission_data['scoredata']['score']; ?> / <?php echo $submission_data['scoredata']['max_score']; ?>
				</td>
			<?php endif; ?>
		<?php
	}

	public function make_slider( $title, $subtitle, $submission_data, $description = '', $prefix = '', $suffix = '', $score = false ) {
		$value = $submission_data['value'];
		$score = ( $this->settings['summary']['individual_score'] && $score && isset( $submission_data['scoredata'] ) && ! empty( $submission_data['scoredata']['max_score'] ) );
		$ui = IPT_Plugin_UIF_Front::instance();
		$img = '<img src="' . $ui->get_image_for_icon( 0xe187, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
?>
			<?php $this->make_element_headers(
				$title,
				$subtitle,
				$description
			); ?>
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
				<?php echo $img; ?>
			</td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="4">
				<?php echo $prefix . $value . $suffix; ?>
			</td>
		<?php if ( $score ) : ?>
		</tr>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons"><?php echo $this->score_img; ?></td>
			<th style="<?php echo $this->email_styling['th']; ?>" colspan="2"><?php echo $this->settings['summary']['score_title']; ?></th>
			<td style="<?php echo $this->email_styling['td'] . $this->email_styling['tdsm']; ?>" colspan="2">
				<?php echo $submission_data['scoredata']['score']; ?> / <?php echo $submission_data['scoredata']['max_score']; ?>
			</td>
		<?php endif; ?>
		<?php
	}
	public function make_slider_inner( $title, $value, $prefix = '', $suffix = '' ) {
		$ui = IPT_Plugin_UIF_Front::instance();
		$img = '<img src="' . $ui->get_image_for_icon( 0xe187, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
?>
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
				<?php echo $img; ?>
			</td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="2"><?php echo $title; ?></td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="2">
				<?php echo $prefix . $value . $suffix; ?>
			</td>
		<?php
	}

	public function make_range( $title, $subtitle, $submission_data, $description = '', $prefix = '', $suffix = '', $score = true ) {
		$value = $submission_data['values'];
		$score = ( $this->settings['summary']['individual_score'] && $score && isset( $submission_data['scoredata'] ) && ! empty( $submission_data['scoredata']['max_score'] ) );
		$ui = IPT_Plugin_UIF_Front::instance();
		$img = '<img src="' . $ui->get_image_for_icon( 0xe187, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
?>
			<?php $this->make_element_headers(
				$title,
				$subtitle,
				$description
			); ?>
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
				<?php echo $img; ?>
			</td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="4">
				<?php printf( __( 'from %3$s%1$d%4$s to %3$s%2$d%4$s', 'ipt_fsqm' ), $value['min'], $value['max'], $prefix, $suffix ); ?>
			</td>
		<?php if ( $score ) : ?>
		</tr>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons"><?php echo $this->score_img; ?></td>
			<th style="<?php echo $this->email_styling['th']; ?>" colspan="2"><?php echo $this->settings['summary']['score_title']; ?></th>
			<td style="<?php echo $this->email_styling['td'] . $this->email_styling['tdsm']; ?>" colspan="2">
				<?php echo $submission_data['scoredata']['score']; ?> / <?php echo $submission_data['scoredata']['max_score']; ?>
			</td>
		<?php endif; ?>
		<?php
	}
	public function make_range_inner( $title, $value, $prefix = '', $suffix = '' ) {
		$ui = IPT_Plugin_UIF_Front::instance();
		$img = '<img src="' . $ui->get_image_for_icon( 0xe187, $this->settings['theme']['icon']['color'] ) . '" height="16" width="16" />';
?>
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
				<?php echo $img; ?>
			</td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="2"><?php echo $title; ?></td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="2">
				<?php printf( __( 'from %3$s%1$d%4$s to %3$s%2$d%4$s', 'ipt_fsqm' ), $value['min'], $value['max'], $prefix, $suffix ); ?>
			</td>
		<?php
	}

	/**
	 * Calculates the feedback score.
	 *
	 * @param      float   $total_score  The total score
	 * @param      array   $autoscore    Associative array of autoscore config
	 * @param      string  $value        User input value
	 *
	 * @return     float   The feedback score.
	 */
	protected function calculate_feedback_score( $total_score, $autoscore, $value ) {
		// Normalize the values
		$value = trim( strtolower( $value ) );
		$text = trim( strtolower( $autoscore['text'] ) );
		$obtained_score = 0;
		// If text is not set, then just return 0
		if ( '' == $value || '' == $text ) {
			return $obtained_score;
		}
		// Now compare as needed
		// contains, starts, ends, equals
		switch ( $autoscore['type'] ) {
			case 'contains' :
				if ( false !== strstr( $value, $text ) ) {
					$obtained_score = $total_score;
				}
				break;
			case 'starts' :
				if ( preg_match( '/^' . $text . '/im', $value ) ) {
					$obtained_score = $total_score;
				}
				break;
			case 'ends' :
				if ( preg_match( '/' . $text . '$/im', $value ) ) {
					$obtained_score = $total_score;
				}
				break;
			case 'equals' :
				if ( $value == $text ) {
					$obtained_score = $total_score;
				}
				break;
			default :
				// We don't need to change the score, because obtained_score
				// is set to 0
		}

		return $obtained_score;
	}

	protected function has_scoredata( $submission_data ) {
		return isset( $submission_data['scoredata'] ) && !empty( $submission_data['scoredata'] ) && isset( $submission_data['scoredata']['max_score'] ) && $submission_data['scoredata']['max_score'] != 0;
	}

	protected function is_highlight_score() {
		return $this->settings['summary']['highlight_correct'] || $this->settings['summary']['positive_correct'];
	}

	/**
	 * Determines if subscription payment form.
	 *
	 * @return     boolean  True if subscription payment form, False otherwise.
	 */
	protected function is_subscription_payment_form() {
		return ( true == $this->settings['payment']['enabled'] && 'recurring' == $this->settings['payment']['payment_type'] );
	}

	protected function get_score_highlighted_value( $value, $tag = 'div' ) {
		return '<' . $tag . ' class="eform-fb-correct" style="' . $this->get_score_highlighted_style_value() . '">' . $value . '</' . $tag . '>';
	}

	protected function get_score_highlighted_style_value( $reset_gutter = true ) {
		return 'color: '
			. $this->settings['summary']['correct_color']
			. '; font-weight: bold;'
			. ( $reset_gutter ? 'margin: 0; padding: 0;' : '' );
	}

	protected function make_element_headers( $title, $subtitle, $description ) {
		?>
	<th style="<?php echo $this->email_styling['th'] . $this->email_styling['qth']; ?>" scope="row" colspan="5">
		<?php echo $title; ?>
		<?php if ( $subtitle !== '' ) : ?>
			<br />
			<span class="description" style="<?php echo $this->email_styling['description']; ?>">
				<?php echo $subtitle; ?>
			</span>
		<?php endif; ?>
	</th>
	<?php if ( $description !== '' ) : ?>
	</tr><tr>
	<td colspan="5" style="<?php echo $this->email_styling['td']; ?>">
	<div class="ipt_uif_richtext">
		<?php echo apply_filters( 'ipt_uif_richtext', $description ); ?>
	</div>
	</td>
	<?php endif; ?>
</tr><tr>
		<?php
	}
}
