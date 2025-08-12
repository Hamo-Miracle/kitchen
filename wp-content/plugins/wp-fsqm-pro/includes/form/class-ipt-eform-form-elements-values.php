<?php
/**
 * Form ELements Values class
 *
 * This is an utility class using which we can get JSON compatible /
 * Stringified values of submission data
 *
 * @todo #474
 *
 * @package eForm - WordPress Form Builder
 * @subpackage Form\Value
 * @codeCoverageIgnore
 */
class IPT_EForm_Form_Elements_Values extends IPT_FSQM_Form_Elements_Data {

	/**
	 * Multi Option Delimiter
	 * @var string
	 */
	protected $option_delimiter = "\n";

	/**
	 * Multi Row Delimiter
	 * @var string
	 */
	protected $row_delimiter = "\n\n";

	/**
	 * Multi Number Range Delimiter
	 * @var string
	 */
	protected $range_delimiter = '/';

	/**
	 * Multi entry delimiter
	 *
	 * @var        string
	 */
	protected $entry_delimiter = ':';

	/*==========================================================================
	 * A few setters for config
	 *========================================================================*/
	/**
	 * Set the Multi Option Delimiter
	 * @param string $delimiter The delimiter character/string
	 */
	public function set_option_delimiter( $delimiter = "\n" ) {
		$this->option_delimiter = $this->filter_delimiters( $delimiter );
	}

	/**
	 * Set the Multi Row Delimiter
	 * @param string $delimiter The delimiter character/string
	 */
	public function set_row_delimiter( $delimiter = "\n\n" ) {
		$this->row_delimiter = $this->filter_delimiters( $delimiter );
	}

	/**
	 * Set the Range Delimiter
	 * @param string $delimiter The range delimiter (Default '/')
	 */
	public function set_range_delimiter( $delimiter = '/' ) {
		$this->range_delimiter = $this->filter_delimiters( $delimiter );
	}

	/**
	 * Sets the entry delimiter.
	 *
	 * @param      string  $delimiter  The delimiter
	 */
	public function set_entry_delimiter( $delimiter = ':' ) {
		$this->entry_delimiter = $this->filter_delimiters( $delimiter );
	}

	/**
	 * Filter a delimiter so that user input for \n etc are parsed properly.
	 *
	 * @param string $delimiter The delimiter from user input.
	 * @return string proper value.
	 */
	public function filter_delimiters( $delimiter ) {
		switch ( $delimiter ) {
			default:
				return $delimiter;
				break;
			case '\t':
				return "\t";
				break;
			case '\n':
				return "\n";
				break;
			case '\n\n':
				return "\n\n";
				break;
			case '\n\n\n':
				return "\n\n\n";
				break;
			case '\n\n\n\n':
				return "\n\n\n\n";
				break;
		}
	}

	/*==========================================================================
	 * Getters for some config
	 *========================================================================*/
	/**
	 * Gets the ID of the RAW database entry.
	 *
	 * @return string
	 */
	public function get_sql_id() {
		return $this->sql_id;
	}

	/**
	 * Gets the CSV Multi Option Delimiter.
	 *
	 * @return string
	 */
	public function get_option_delimiter() {
		return $this->option_delimiter;
	}

	/**
	 * Gets the CSV Multi Row Delimiter.
	 *
	 * @return string
	 */
	public function get_row_delimiter() {
		return $this->row_delimiter;
	}

	/**
	 * Gets the Range Delimiter.
	 *
	 * @return string
	 */
	public function get_range_delimiter() {
		return $this->range_delimiter;
	}

	/**
	 * Gets the entry delimiter.
	 *
	 * @return     string  The entry delimiter.
	 */
	public function get_entry_delimiter() {
		return $this->entry_delimiter;
	}

	/*==========================================================================
	 * Constructor
	 *========================================================================*/

	/**
	 * The constructor function
	 *
	 * @param      int  $data_id  The submission ID
	 */
	public function __construct( $data_id, $form_id = null ) {
		parent::__construct( $data_id, $form_id );
	}

	/*==========================================================================
	 * Reassign
	 *========================================================================*/
	/**
	 * Reassigns the value class to a different data
	 *
	 * It is a simpler approach for bulk operation where
	 * we will save a lot of overheaed.
	 *
	 * Use with caution, since it does not reset the form.
	 *
	 * @param      string  $data_id  The data identifier
	 * @param      object  $data     The database data object
	 */
	public function reassign( $data_id, $data ) {
		// Update the data ID
		$this->data_id = $data_id;
		// Reset the conditional stuff
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
		$this->conditional_hidden_blacklist = array(
			'layout'   => array(),
			'design'   => array(),
			'mcq'      => array(),
			'freetype' => array(),
			'pinfo'    => array(),
		);

		// Assign the data
		$this->data = $data;
		// Unserialize stuff
		$this->data->mcq = maybe_unserialize( $this->data->mcq );
		$this->data->freetype = maybe_unserialize( $this->data->freetype );
		$this->data->pinfo = maybe_unserialize( $this->data->pinfo );
	}


	/*==========================================================================
	 * Utility methods
	 *========================================================================*/

	/**
	 * Gets the value of an element in desired format
	 *
	 * @since      3.4
	 *
	 * @param      string                       $m_type  The m_type of the
	 *                                                   element ( mcq,
	 *                                                   freetype, pinfo )
	 * @param      int                          $key     The key of the element
	 * @param      string                       $type    The type of the value
	 *                                                   to return, this could
	 *                                                   be 'json' for array or
	 *                                                   'string' for
	 *                                                   stringified
	 * @param      string                       $data    The type of data, this
	 *                                                   could be 'label' for
	 *                                                   the actual value or
	 *                                                   'numeric' for numeric
	 *                                                   IDs of the data. This
	 *                                                   is applicable for MCQ
	 *                                                   type elements only, for
	 *                                                   freetype, this will be
	 *                                                   ignored
	 *
	 * @return     mixed(boolean|array|string)  Return the element value in array|boolean or string format if found.
	 *            In case of error null is returned
	 */
	public function get_value( $m_type, $key, $type = 'json', $data = 'label' ) {
		// Construct the element
		$element = $this->get_element_from_layout( array(
			'm_type' => $m_type,
			'key' => $key,
		) );
		if ( empty( $element ) ) {
			return null;
		}
		$submission = $this->get_submission_from_data( array(
			'm_type' => $m_type,
			'key' => $key,
		) );
		if ( empty( $submission ) ) {
			return null;
		}

		// Check for built-in methods
		if ( method_exists( $this, 'value_' . $element['type'] ) ) {
			return call_user_func( array( $this, 'value_' . $element['type'] ), $element, $submission, $type, $data, $key);
		}

		// Not set
		// Check if external
		$definition = $this->get_element_definition( $element );
		if ( isset( $definition['callback_value'] ) && is_callable( $definition['callback_value'] ) ) {
			return call_user_func( $definition['callback_value'], $element, $submission, $type, $data, $key );
		}

		return null;
	}

	/*==========================================================================
	 * Value getter for mcq elements
	 *========================================================================*/
	public function value_checkbox( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_mcqs( $element, $submission, $type, $data );
	}

	public function value_radio( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_mcqs( $element, $submission, $type, $data );
	}

	public function value_select( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_mcqs( $element, $submission, $type, $data );
	}

	public function value_thumbselect( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_mcqs( $element, $submission, $type, $data );
	}

	public function value_pricing_table( $element, $submission, $type = 'json', $data = 'label' ) {
		$return = array();

		if ( ! is_array( $submission['options'] ) ) {
			$submission['options'] = array();
		}

		foreach ( $element['settings']['options'] as $o_key => $op ) {
			if ( in_array( (string) $o_key, $submission['options'] ) ) {
				if ( 'label' == $data ) {
					$return[] = $op['label'] . ' (' . $element['settings']['currency'] . number_format_i18n( $op['price'], 2 ) . ')';
				} else {
					$return[] = $o_key;
				}
			}
		}
		if ( 'string' == $type ) {
			$return = implode( $this->get_option_delimiter(), $return );
		}

		return $return;
	}

	public function value_slider( $element, $submission, $type = 'json', $data = 'label' ) {
		$return = array( 'value' => $submission['value'] );
		if ( 'string' == $type ) {
			$return = $submission['value'];
		}

		if ( isset( $submission['scoredata'] ) && isset( $submission['scoredata']['max_score'] ) && 0 != $submission['scoredata']['max_score'] && ! empty( $submission['scoredata']['max_score'] ) ) {
			if ( 'string' == $type ) {
				$return .= $this->get_row_delimiter() . __( 'Score: ', 'ipt_fsqm' ) . $submission['scoredata']['score'] . $this->get_range_delimiter() . $submission['scoredata']['max_score'];
			} else {
				$return['score_data'] = $submission['scoredata'];
			}
		}

		return $return;
	}

	public function value_range( $element, $submission, $type = 'json', $data = 'label' ) {
		$return = array(
			'min' => $submission['values']['min'],
			'max' => $submission['values']['max'],
		);
		if ( 'string' == $type ) {
			$return = implode( $this->get_range_delimiter(), $return );
		}
		if ( isset( $submission['scoredata'] ) && isset( $submission['scoredata']['max_score'] ) && 0 != $submission['scoredata']['max_score'] && ! empty( $submission['scoredata']['max_score'] ) ) {
			if ( 'string' == $type ) {
				$return .= $this->get_row_delimiter() . __( 'Score: ', 'ipt_fsqm' ) . $submission['scoredata']['score'] . $this->get_range_delimiter() . $submission['scoredata']['max_score'];
			} else {
				$return['score_data'] = $submission['scoredata'];
			}
		}
		return $return;
	}

	public function value_grading( $element, $submission, $type = 'json', $data = 'label' ) {
		$return = array();

		foreach ( $element['settings']['options'] as $o_key => $option ) {
			if ( ! isset( $submission['options'][ $o_key ] ) ) {
				continue;
			}

			$value = array();

			// Honor the data type
			if ( 'label' == $data ) {
				$value['label'] = $option['label'];
			} else {
				$value['key'] = $o_key;
			}

			// Can be range or single
			if ( true == $element['settings']['range'] ) {
				// Add the actual values
				if ( 'string' == $type ) {
					$value['values'] = $submission['options'][ $o_key ]['min'] . $this->get_range_delimiter() . $submission['options'][ $o_key ]['max'];
					$value = implode( $this->get_entry_delimiter(), $value );
				} else {
					$value['min'] = $submission['options'][ $o_key ]['min'];
					$value['max'] = $submission['options'][ $o_key ]['max'];
				}
			} else {
				$value['value'] = $submission['options'][ $o_key ];
				if ( 'string' == $type ) {
					$value = implode( $this->get_entry_delimiter(), $value );
				}
			}

			$return[] = $value;
		}

		if ( 'string' == $type ) {
			$return = implode( $this->get_row_delimiter(), $return );
		}

		if ( isset( $submission['scoredata'] ) && isset( $submission['scoredata']['max_score'] ) && 0 != $submission['scoredata']['max_score'] && ! empty( $submission['scoredata']['max_score'] ) ) {
			if ( 'string' == $type ) {
				$return .= $this->get_row_delimiter() . __( 'Score: ', 'ipt_fsqm' ) . $submission['scoredata']['score'] . $this->get_range_delimiter() . $submission['scoredata']['max_score'];
			} else {
				$return['score_data'] = $submission['scoredata'];
			}
		}

		return $return;
	}

	public function value_smileyrating( $element, $submission, $type = 'json', $data = 'label' ) {
		$return = array();
		if ( 'label' == $data ) {
			if ( isset( $element['settings']['labels'][ $submission['option'] ] ) ) {
				$return['label'] = $element['settings']['labels'][ $submission['option'] ];
			} else {
				$return['label'] = 'N/A';
			}
		} else {
			$return['key'] = $submission['option'];
		}

		if ( '' != $submission['feedback'] && true == $element['settings']['show_feedback'] ) {
			$return['feedback'] = $submission['feedback'];
		}

		if ( 'string' == $type ) {
			$return = implode( $this->get_row_delimiter(), $return );
		}

		if ( isset( $submission['scoredata'] ) && isset( $submission['scoredata']['max_score'] ) && 0 != $submission['scoredata']['max_score'] && ! empty( $submission['scoredata']['max_score'] ) ) {
			if ( 'string' == $type ) {
				$return .= $this->get_row_delimiter() . __( 'Score: ', 'ipt_fsqm' ) . $submission['scoredata']['score'] . $this->get_range_delimiter() . $submission['scoredata']['max_score'];
			} else {
				$return['score_data'] = $submission['scoredata'];
			}
		}

		return $return;
	}

	public function value_starrating( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_numerics( $element, $submission, $type, $data );
	}

	public function value_scalerating( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_numerics( $element, $submission, $type, $data );
	}

	public function value_spinners( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_numerics( $element, $submission, $type, $data );
	}

	public function value_matrix( $element, $submission, $type = 'json', $data = 'label' ) {
		$return = array();

		foreach ( $element['settings']['rows'] as $r_key => $row ) {
			if ( ! isset( $submission['rows'][ $r_key ] ) ) {
				continue;
			}

			$sub = array();
			$sub[] = $row;

			foreach ( $element['settings']['columns'] as $c_key => $col ) {
				if ( in_array( (string) $c_key, $submission['rows'][ $r_key ] ) ) {
					$sub[] = $col;
				}
			}

			if ( 'string' == $type ) {
				$return[] = implode( $this->get_entry_delimiter(), $sub );
			} else {
				$return[] = $sub;
			}
		}

		if ( 'string' == $type ) {
			$return = implode( $this->get_row_delimiter(), $return );
		}

		if ( isset( $submission['scoredata'] ) && isset( $submission['scoredata']['max_score'] ) && 0 != $submission['scoredata']['max_score'] && ! empty( $submission['scoredata']['max_score'] ) ) {
			if ( 'string' == $type ) {
				$return .= $this->get_row_delimiter() . __( 'Score: ', 'ipt_fsqm' ) . $submission['scoredata']['score'] . $this->get_range_delimiter() . $submission['scoredata']['max_score'];
			} else {
				$return['score_data'] = $submission['scoredata'];
			}
		}

		return $return;
	}

	public function value_matrix_dropdown( $element, $submission, $type = 'json', $data = 'label' ) {
		$return = array();

		foreach ( $element['settings']['rows'] as $r_key => $row ) {
			if ( ! isset( $submission['rows'][ $r_key ] ) ) {
				continue;
			}
			foreach ( $element['settings']['columns'] as $c_key => $col ) {
				$sub = array();
				if ( 'label' == $data ) {
					$sub[] = $row;
					$sub[] = $col;
					if ( isset( $submission['rows'][ $r_key ][ $c_key ] ) && is_array( $submission['rows'][ $r_key ][ $c_key ] ) ) {
						$moptions = array();
						foreach ( $submission['rows'][ $r_key ][ $c_key ] as $option_key ) {
							if ( isset( $element['settings']['options'][ (string) $option_key ] ) ) {
								$moptions[] = $element['settings']['options'][ (string) $option_key ]['label'];
							}
						}
						if ( 'string' == $type ) {
							$moptions = implode( $this->get_option_delimiter(), $moptions );
						}
						$sub[] = $moptions;
					} else {
						if ( isset( $submission['rows'][ $r_key ][ $c_key ] ) && isset( $element['settings']['options'][ (string) $submission['rows'][ $r_key ][ $c_key ] ] ) ) {
							$sub[] = $element['settings']['options'][ (string) $submission['rows'][ $r_key ][ $c_key ] ]['label'];
						}
					}
				} else {
					$sub[] = $r_key;
					$sub[] = $c_key;
					if ( isset( $submission['rows'][ $r_key ][ $c_key ] ) && is_array( $submission['rows'][ $r_key ][ $c_key ] ) ) {
						$moptions = array();
						foreach ( $submission['rows'][ $r_key ][ $c_key ] as $option_key ) {
							if ( isset( $element['settings']['options'][ (string) $option_key ] ) ) {
								$moptions[] = $option_key;
							}
						}
						if ( 'string' == $type ) {
							$moptions = implode( $this->get_option_delimiter(), $moptions );
						}
						$sub[] = $moptions;
					} else {
						if ( isset( $submission['rows'][ $r_key ][ $c_key ] ) && isset( $element['settings']['options'][ (string) $submission['rows'][ $r_key ][ $c_key ] ] ) ) {
							$sub[] = $submission['rows'][ $r_key ][ $c_key ];
						}
					}
				}

				if ( 'string' == $type ) {
					$return[] = implode( $this->get_entry_delimiter(), $sub );
				} else {
					$return[] = $sub;
				}
			}
		}
		if ( 'string' == $type ) {
			$return = implode( $this->get_row_delimiter(), $return );
		}
		if ( isset( $submission['scoredata'] ) && isset( $submission['scoredata']['max_score'] ) && 0 != $submission['scoredata']['max_score'] && ! empty( $submission['scoredata']['max_score'] ) ) {
			if ( 'string' == $type ) {
				$return .= $this->get_row_delimiter() . __( 'Score: ', 'ipt_fsqm' ) . $submission['scoredata']['score'] . $this->get_range_delimiter() . $submission['scoredata']['max_score'];
			} else {
				$return['score_data'] = $submission['scoredata'];
			}
		}

		return $return;
	}

	public function value_likedislike( $element, $submission, $type = 'json', $data = 'label' ) {
		$return = $submission['value'];
		if ( 'label' == $data ) {
			if ( 'like' == $submission['value'] ) {
				$return = $element['settings']['like'];
			} else {
				$return = $element['settings']['dislike'];
			}
		}
		return $return;
	}

	public function value_toggle( $element, $submission, $type = 'json', $data = 'label' ) {
		$return = $submission['value'];
		if ( 'label' == $data ) {
			if ( false == $submission['value'] ) {
				$return = $element['settings']['off'];
			} else {
				$return = $element['settings']['on'];
			}
		}
		return $return;
	}

	public function value_sorting( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_sorting( $element, $submission, $type, $data );
	}

	/*==========================================================================
	 * Value getter for freetype elements
	 *========================================================================*/
	public function value_feedback_large( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_text( $element, $submission, $type, $data );
	}

	public function value_feedback_small( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_text( $element, $submission, $type, $data );
	}

	public function value_upload( $element, $submission, $type = 'json', $data = 'label', $e_key = null ) {
		$uploader = new IPT_FSQM_Form_Elements_Uploader( $this->form_id, $e_key );
		$uploads = $uploader->get_uploads( $this->data_id );
		$return = array();

		if ( ! empty( $uploads ) ) {
			foreach ( $uploads as $upload ) {
				if ( '' == $upload['guid'] ) {
					continue;
				}
				$item = array(
					'name' => $upload['name'],
					'guid' => $upload['guid'],
				);
				if ( 'html' == $data ) {
					if ( in_array( $upload['mime_type'], array( 'image/jpeg', 'image/jpg', 'image/png', 'image/gif' ) ) ) {
						$return[] = '<p class="eform-upload-image-caption"><a href="' . esc_attr( $upload['guid'] ) . '"><img src="' . esc_attr( $upload['guid'] ) . '" alt="' . esc_attr( $upload['name'] ) . '" /><br />' .
									'<span class="eform-caption">' . $upload['name'] . '</span></a></p>';
					} else {
						$return[] = '<a href="' . esc_attr( $upload['guid'] ) . '">' . $upload['name'] . '</a>';
					}
				} else {
					if ( 'string' == $type ) {
						$return[] = implode( $this->get_option_delimiter(), $item );
					} else {
						$return[] = $item;
					}
				}
			}
		}
		if ( 'string' == $type ) {
			$return = implode( $this->get_row_delimiter(), $return );
		}
		return $return;
	}

	public function value_mathematical( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_text( $element, $submission, $type, $data );
	}

	public function value_feedback_matrix( $element, $submission, $type = 'json', $data = 'label' ) {
		$return = array();

		foreach ( $element['settings']['rows'] as $r_key => $row ) {
			if ( ! isset( $submission['rows'][ $r_key ] ) ) {
				continue;
			}
			foreach ( $element['settings']['columns'] as $c_key => $col ) {
				$sub = array();
				if ( 'label' == $data ) {
					$sub[] = $row;
					$sub[] = $col;
				} else {
					$sub[] = $r_key;
					$sub[] = $c_key;
				}
				$sub[] = $submission['rows'][ $r_key ][ $c_key ];

				if ( 'string' == $type ) {
					$return[] = implode( $this->get_entry_delimiter(), $sub );
				} else {
					$return[] = $sub;
				}
			}
		}
		if ( 'string' == $type ) {
			$return = implode( $this->get_row_delimiter(), $return );
		}
		return $return;
	}

	public function value_signature( $element, $submission, $type = 'json', $data = 'label' ) {
		$image = $this->convert_jsignature_image( $submission['value'] );
		if ( '' == $image ) {
			return '';
		}
		return 'data:image/png;base64,' . $image;
	}

	public function value_gps( $element, $submission, $type = 'json', $data = 'label' ) {
		$return = array();

		foreach ( array( 'location_name', 'lat', 'long' ) as $pos ) {
			$val = array(
				'label' => $element['settings'][ $pos . '_' . 'label' ],
				'value' => $submission[ $pos ],
			);
			if ( 'string' == $type ) {
				$val = implode( $this->get_entry_delimiter(), $val );
			}
			$return[ $pos ] = $val;
		}

		if ( 'string' == $type ) {
			$return = implode( $this->get_row_delimiter(), $return );
		}

		return $return;
	}

	/*==========================================================================
	 * Value getter for pinfo elements
	 *========================================================================*/
	public function value_payment( $element, $submission, $type = 'json', $data = 'label' ) {
		global $wpdb, $ipt_fsqm_info;

		$payment_db = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_info['payment_table']} WHERE data_id = %d", $this->data_id ) ); // WPCS: unprepared SQL ok.

		if ( is_null( $payment_db ) ) {
			if ( 'string' == $type ) {
				return '';
			} else {
				return array();
			}
		}

		$payment_amount = $submission['value'];
		$discount_amount = 0;
		if ( isset( $submission['couponval'] ) && '' != $submission['couponval'] ) {
			$payment_amount = $submission['couponval'];
			$discount_amount = $submission['value'] - $payment_amount;
		}

		$invoiceid = str_replace( '{id}', $payment_db->id, $this->settings['payment']['invoicenumber'] );
		if ( '' == $invoiceid ) {
			$invoiceid = $payment_db->id;
		}

		$payment_status = IPT_FSQM_Form_Elements_Static::ipt_fsqm_get_payment_status();

		$payment_modes = IPT_FSQM_Form_Elements_Static::ipt_fsqm_get_payment_gateways();

		$returns = array();
		// Date
		$returns['date'] = array(
			'label' => __( 'Date', 'ipt_fsqm_exp' ),
			'value' => date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $payment_db->date ) ),
		);

		// Invoice ID
		$returns['invoiceid'] = array(
			'label' => __( 'Invoice ID', 'ipt_fsqm_exp' ),
			'value' => $invoiceid,
		);

		// Status
		$returns['status'] = array(
			'label' => __( 'Status', 'ipt_fsqm_exp' ),
			'value' => @$payment_status[ $payment_db->status ],
		);

		// TxnID
		$returns['txnid'] = array(
			'label' => __( 'Transaction ID', 'ipt_fsqm_exp' ),
			'value' => $payment_db->txn,
		);

		// Payment gateway
		$returns['payment_gateway'] = array(
			'label' => __( 'Payment gateway', 'ipt_fsqm_exp' ),
			'value' => @$payment_modes[ $payment_db->mode ],
		);

		// Item Name
		$returns['itemname'] = array(
			'label' => __( 'Item name', 'ipt_fsqm_exp' ),
			'value' => $this->settings['payment']['itemname'],
		);

		// Item SKU
		if ( '' != $this->settings['payment']['itemsku'] ) {
			$returns['itemsku'] = array(
				'label' => __( 'Item SKU', 'ipt_fsqm_exp' ),
				'value' => $this->settings['payment']['itemsku'],
			);
		}

		// Item description
		$returns['itemdescription'] = array(
			'label' => __( 'Description', 'ipt_fsqm_exp' ),
			'value' => $this->settings['payment']['itemdescription'],
		);

		// Item price
		$returns['price'] = array(
			'label' => __( 'Price', 'ipt_fsqm_exp' ),
			'value' => $submission['value'],
		);

		// Coupon
		if ( '' != $submission['coupon'] && $discount_amount > 0 ) {
			$returns['coupon'] = array(
				'label' => __( 'Coupon', 'ipt_fsqm_exp' ),
				'value' => $submission['coupon'],
			);
			$returns['discount'] = array(
				'label' => __( 'Discount', 'ipt_fsqm_exp' ),
				'value' => number_format_i18n( $discount_amount, 2 ),
			);
		}

		// Total
		$returns['total'] = array(
			'label' => __( 'Total', 'ipt_fsqm_exp' ),
			'value' => number_format_i18n( $payment_amount, 2 ),
		);

		// Now process it
		if ( 'string' == $type ) {
			foreach ( $returns as $r_key => $return ) {
				$returns[ $r_key ] = implode( $this->get_entry_delimiter(), $return );
			}
			$returns = implode( $this->get_row_delimiter(), $returns );
		}

		return $returns;
	}

	public function value_f_name( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_text( $element, $submission, $type, $data );
	}

	public function value_l_name( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_text( $element, $submission, $type, $data );
	}

	public function value_email( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_text( $element, $submission, $type, $data );
	}

	public function value_phone( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_text( $element, $submission, $type, $data );
	}

	public function value_p_phone( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_text( $element, $submission, $type, $data );
	}

	public function value_p_name( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_text( $element, $submission, $type, $data );
	}

	public function value_p_email( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_text( $element, $submission, $type, $data );
	}

	public function value_textinput( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_text( $element, $submission, $type, $data );
	}

	public function value_textarea( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_text( $element, $submission, $type, $data );
	}

	public function value_password( $element, $submission, $type = 'json', $data = 'label', $key ) {
		// Decrypt password if needed
		if ( isset( $submission['encrypted'] ) && $submission['encrypted'] && '' !== $submission['value'] ) {
			$submission['value'] = $this->decrypt_password_field( $submission['value'] );
		}
		// Hide the value if needed
		$needs_reg_mods = $this->is_element_of_reg_field( 'pinfo', $key, 'password_id' );
		if ( $element['settings']['hide_in_summary'] || $needs_reg_mods ) {
			if ( '' !== $submission['value'] ) {
				$submission['value'] = str_repeat( '*', min( 10, strlen( $submission['value'] ) ) );
			}
		}
		return $this->value_make_text( $element, $submission, $type, $data );
	}

	public function value_keypad( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_text( $element, $submission, $type, $data );
	}

	public function value_datetime( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_text( $element, $submission, $type, $data );
	}

	public function value_p_checkbox( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_mcqs( $element, $submission, $type, $data );
	}

	public function value_p_radio( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_mcqs( $element, $submission, $type, $data );
	}

	public function value_p_select( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_mcqs( $element, $submission, $type, $data );
	}

	public function value_s_checkbox( $element, $submission, $type = 'json', $data = 'label' ) {
		return (bool) $submission['value'];
	}

	public function value_address( $element, $submission, $type = 'json', $data = 'label' ) {
		$return = array();
		foreach ( $submission['values'] as $key => $val ) {
			if ( ! empty( $val ) ) {
				$return[ $key ] = $val;
			}
		}
		if ( 'string' == $type ) {
			$return = implode( $this->get_row_delimiter(), $return );
		}
		return $return;
	}

	public function value_p_sorting( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_sorting( $element, $submission, $type, $data );
	}

	public function value_hidden( $element, $submission, $type = 'json', $data = 'label' ) {
		return $this->value_make_text( $element, $submission, $type, $data );
	}

	public function value_repeatable( $element, $submission, $type = 'json', $data = 'label' ) {
		$return = array();

		// Loop through submissions
		foreach ( (array) $submission['values'] as $i_key => $items ) {
			$new_item = array();
			// Loop through columns
			foreach ( (array) $element['settings']['group'] as $g_key => $group ) {
				// We can not rely on key type data, since there aren't any
				if ( isset( $items[ $g_key ] ) ) {
					// Process the data
					switch ( $group['type'] ) {
						case 'radio' :
							$new_item[] = str_replace( '__', ' ', $items[ $g_key ] );
							break;
						case 'checkbox' :
						case 'select' :
						case 'select_multiple' :
							$options = array();
							foreach ( (array) $items[ $g_key ] as $op ) {
								$options[] = str_replace( '__', ' ', $op );
							}
							$new_item[] = implode( ';', $options );
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
						case 'time' :
						case 'datetime' :
							$new_item[] = $items[ $g_key ];
							break;
					}
				} else {
					// No data found, so insert empty
					$new_item[] = '';
				}
			}
			if ( 'string' == $type ) {
				$new_item = implode( $this->get_entry_delimiter(), $new_item );
			}
			$return[] = $new_item;
		}

		// Finalize return
		if ( 'string' == $type ) {
			$return = implode( $this->get_row_delimiter(), $return );
		}

		return $return;
	}

	public function value_guestblog( $element, $submission, $type = 'json', $data = 'label' ) {
		// Prepare the basic values
		$return = array(
			'title' => $submission['title'],
			'value' => $submission['value'],
			'taxonomy' => array(),
		);

		// Add taxonomy data
		if ( ! empty( $submission['taxonomy'] ) ) {
			foreach ( $submission['taxonomy'] as $taxonomy => $tax_selected ) {
				$terms_selected = array();
				// If label is type of data then add the nicename of tax and terms
				if ( 'label' == $data ) {
					$tax_data = get_taxonomy( $taxonomy );
					if ( ! empty( $tax_selected ) ) {
						foreach ( $tax_selected as $term ) {
							$terms_selected[] = get_term( $term, $taxonomy )->name;
						}
					}
					$return['taxonomy'][] = array(
						'tax' => $tax_data->labels->name,
						'terms' => $terms_selected,
					);
				} else {
					$return['taxonomy'][] = array(
						'tax' => $taxonomy,
						'terms' => $tax_selected,
					);
				}
			}
		}

		// Now serialize if needed
		if ( 'string' == $type ) {
			// Serialize the inner taxonomy
			if ( ! empty( $return['taxonomy'] ) ) {
				foreach ( $return['taxonomy'] as $key => $tax ) {
					// Implode the terms first
					$terms = implode( $this->get_entry_delimiter(), $tax['terms'] );

					// Now implode the tax and terms
					$return['taxonomy'][ $key ] = implode( $this->get_option_delimiter(), array( $tax['tax'], $terms ) );
				}
			}

			$return['taxonomy'] = implode( $this->get_row_delimiter(), $return['taxonomy'] );

			$return = implode( $this->get_row_delimiter(), $return );
		}

		return $return;
	}

	/*==========================================================================
	 * Helper methods for value getters
	 *========================================================================*/

	/**
	 * Shortcut method to quickly calculate and return the values of checkbox,
	 * radio, select type elements
	 *
	 * @param      array                $element     The element with basic
	 *                                               properties
	 * @param      array                $submission  The submission data
	 * @param      string               $type        The type in which the
	 *                                               return to be compiled.
	 *                                               'json' for array and
	 *                                               'string' for simple
	 *                                               stringified value
	 * @param      string               $data        The data with which return
	 *                                               to be compiled. 'label' for
	 *                                               actual value or 'numeric'
	 *                                               for numeric keys of the
	 *                                               options
	 *
	 * @return     mixed(array|string)  If json is passed as $type, then an associative array is returned, otherwise a simplified string with pre-defined delimiters
	 */
	public function value_make_mcqs( $element, $submission, $type = 'json', $data = 'label' ) {
		$return = array();

		if ( ! is_array( $submission['options'] ) ) {
			$submission['options'] = array();
		}

		foreach ( $element['settings']['options'] as $o_key => $op ) {
			if ( in_array( (string) $o_key, $submission['options'] ) ) {
				if ( 'label' == $data ) {
					$return[] = $op['label'];
				} else {
					$return[] = $o_key;
				}
			}
		}

		if ( isset( $element['settings']['others'] ) && true == $element['settings']['others'] ) {
			if ( in_array( 'others', $submission['options'] ) ) {
				if ( 'label' == $data ) {
					$return[] = $element['settings']['o_label'];
				} else {
					$return[] = 'other';
				}
			}
		}

		if ( 'string' == $type ) {
			$return = implode( $this->get_option_delimiter(), $return );
		}

		if ( isset( $element['settings']['others'] ) && true == $element['settings']['others'] && in_array( 'others', $submission['options'] ) ) {
			if ( 'string' == $type ) {
				$return .= $this->get_row_delimiter() . $submission['others'];
			} else {
				$return['o_data'] = $submission['others'];
			}
		}

		if ( isset( $submission['scoredata'] ) && isset( $submission['scoredata']['max_score'] ) && 0 != $submission['scoredata']['max_score'] && ! empty( $submission['scoredata']['max_score'] ) ) {
			if ( 'string' == $type ) {
				$return .= $this->get_row_delimiter() . __( 'Score: ', 'ipt_fsqm' ) . $submission['scoredata']['score'] . $this->get_range_delimiter() . $submission['scoredata']['max_score'];
			} else {
				$return['score_data'] = $submission['scoredata'];
			}
		}

		return $return;
	}

	/**
	 * Make text type elements
	 *
	 * @param      array   $element     The element
	 * @param      array   $submission  The submission
	 * @param      string  $type        The type
	 * @param      string  $data        The data
	 *
	 * @return     array   return value
	 */
	public function value_make_text( $element, $submission, $type, $data ) {
		$value = str_replace( "\r", '', $submission['value'] );
		$value = str_replace( "\n\n" , "\n", $value );
		if ( isset( $element['settings']['inline'] ) && $element['settings']['inline']['enabled'] ) {
			$value = implode( ' ', [ strip_tags( $element['settings']['inline']['prefix'] ), $value, strip_tags( $element['settings']['inline']['suffix'] ) ] );
		}
		$return = array(
			'value' => $value,
		);

		if ( 'string' == $type ) {
			$return = $value;
		}

		if ( isset( $submission['score'] ) && isset( $element['settings']['score'] ) && is_numeric( $element['settings']['score'] ) ) {
			$max_score = $element['settings']['score'];
			$score = $submission['score'];
			if ( '' == trim( $score ) ) {
				$score = __( 'Unassigned', 'ipt_fsqm' );
			}
			if ( 'string' == $type ) {
				$return .= $this->get_row_delimiter() . __( 'Score: ', 'ipt_fsqm' ) . $score . $this->get_range_delimiter() . $max_score;
			} else {
				$return['score_data'] = array(
					'score' => $score,
					'max_score' => $max_score,
				);
			}
		}

		return $return;
	}

	/**
	 * Shortcut method to calculate values for numeric type elements
	 *
	 * @param      array                $element     The element with basic
	 *                                               properties
	 * @param      array                $submission  The submission data
	 * @param      string               $type        The type in which the
	 *                                               return to be compiled.
	 *                                               'json' for array and
	 *                                               'string' for simple
	 *                                               stringified value
	 * @param      string               $data        The data with which return
	 *                                               to be compiled. 'label' for
	 *                                               actual value or 'numeric'
	 *                                               for numeric keys of the
	 *                                               options
	 *
	 * @return     mixed(array|string)  If json is passed as $type, then an associative array is returned, otherwise a simplified string with pre-defined delimiters
	 */
	public function value_make_numerics( $element, $submission, $type = 'json', $data = 'label' ) {
		$return = array();

		foreach ( $element['settings']['options'] as $o_key => $option ) {
			if ( ! isset( $submission['options'][ $o_key ] ) ) {
				continue;
			}
			$label = $option;
			if ( is_array( $option ) && isset( $option['label'] ) ) {
				$label = $option['label'];
			}
			if ( 'label' == $data ) {
				$return[] = array(
					'label' => $label,
					'value' => $submission['options'][ $o_key ],
				);
			} else {
				$return[] = array(
					'key' => $o_key,
					'value' => $submission['options'][ $o_key ],
				);
			}
		}

		if ( 'string' == $type ) {
			foreach ( $return as $r_key => $rdata ) {
				$return[ $r_key ] = implode( $this->get_entry_delimiter(), $rdata );
			}
			$return = implode( $this->get_row_delimiter(), $return );
		}

		if ( isset( $submission['scoredata'] ) && isset( $submission['scoredata']['max_score'] ) && 0 != $submission['scoredata']['max_score'] && ! empty( $submission['scoredata']['max_score'] ) ) {
			if ( 'string' == $type ) {
				$return .= $this->get_row_delimiter() . __( 'Score: ', 'ipt_fsqm' ) . $submission['scoredata']['score'] . $this->get_range_delimiter() . $submission['scoredata']['max_score'];
			} else {
				$return['score_data'] = $submission['scoredata'];
			}
		}

		return $return;
	}

	/**
	 * Shortcut method to calculate sorting type element value
	 *
	 * @param      array   $element     The element with basic properties
	 * @param      array   $submission  The submission data
	 * @param      string  $type        The type in which the return to be
	 *                                  compiled. 'json' for array and 'string'
	 *                                  for simple stringified value
	 * @param      string  $data        The data with which return to be
	 *                                  compiled. 'label' for actual value or
	 *                                  'numeric' for numeric keys of the
	 *                                  options
	 *
	 * @return     mixed(array|string)  If json is passed as $type, then an associative array is returned, otherwise a simplified string with pre-defined delimiters
	 */
	public function value_make_sorting( $element, $submission, $type = 'json', $data = 'label' ) {
		$return = array();
		foreach ( (array) $submission['order'] as $o_key ) {
			if ( 'label' == $data ) {
				$return[] = $element['settings']['options'][ $o_key ]['label'];
			} else {
				$return[] = $o_key;
			}
		}
		if ( 'string' == $type ) {
			$return = implode( $this->get_row_delimiter(), $return );
		}

		if ( isset( $submission['scoredata'] ) && isset( $submission['scoredata']['max_score'] ) && 0 != $submission['scoredata']['max_score'] && ! empty( $submission['scoredata']['max_score'] ) ) {
			if ( 'string' == $type ) {
				$return .= $this->get_row_delimiter() . __( 'Score: ', 'ipt_fsqm' ) . $submission['scoredata']['score'] . $this->get_range_delimiter() . $submission['scoredata']['max_score'];
			} else {
				$return['score_data'] = $submission['scoredata'];
			}
		}

		return $return;
	}

}
