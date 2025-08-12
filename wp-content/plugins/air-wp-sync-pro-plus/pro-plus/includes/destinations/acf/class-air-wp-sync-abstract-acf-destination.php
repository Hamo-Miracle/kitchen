<?php
/**
 * Manages import as ACF field.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

/**
 * Air_WP_Sync_ACF_Destination class.
 */
abstract class Air_WP_Sync_Abstract_ACF_Destination extends Air_WP_Sync_Abstract_Destination {

	/**
	 * Destination slug.
	 *
	 * @var string
	 */
	protected $slug = 'acf';

	/**
	 * Markdown formatter.
	 *
	 * @var Air_WP_Sync_Markdown_Formatter
	 */
	protected $markdown_formatter;

	/**
	 * Attachment formatter.
	 *
	 * @var Air_WP_Sync_Attachments_Formatter
	 */
	protected $attachment_formatter;

	/**
	 * Interval formatter.
	 *
	 * @var Air_WP_Sync_Interval_Formatter
	 */
	protected $interval_formatter;

	/**
	 * Term formatter.
	 *
	 * @var Air_WP_Sync_Term_Formatter
	 */
	protected $term_formatter;


	/**
	 * Map ACF types if local types.
	 *
	 * Array[][{acf_type}] a config as array it should contain the key 'supported_sources' or 'sub_fields' if the field is a composed one (e.g ACF Link field).
	 * Array[][{acf_type}]['supported_sources'] an array of supported sources types (e.g "date", "email", "number", ...).
	 * Array[][{acf_type}]['format'] (optional) a function to format the value returned by the local type to be compatible the value ACF is expecting for this field.
	 * Array[][{acf_type}]['extra_filter'] (optional) a function to set the field config at runtime (e.g if the config should change based on ACF field options)
	 * Array[][{acf_type}]['sub_fields'] an array of subfields.
	 * Array[][{acf_type}]['sub_fields'][{subfield key}] subfield config
	 * Array[][{acf_type}]['sub_fields'][{subfield key}]['label'] a label to be displayed in the connection screen
	 * Array[][{acf_type}]['sub_fields'][{subfield key}]['supported_sources'] an array of supported sources types (e.g "date", "email", "number", ...).
	 *
	 * @var array
	 */
	protected $acf_to_local_types;

	/**
	 * String supported sources.
	 *
	 * @var string[]
	 */
	protected $string_supported_sources = array(
		'autoNumber',
		'barcode.text',
		'count',
		'createdTime',
		'createdBy.id',
		'createdBy.email',
		'createdBy.name',
		'currency',
		'date',
		'dateTime',
		'duration',
		'email',
		'externalSyncSource',
		'lastModifiedBy.id',
		'lastModifiedBy.email',
		'lastModifiedBy.name',
		'lastModifiedTime',
		'multipleCollaborators.id',
		'multipleCollaborators.email',
		'multipleCollaborators.name',
		'multipleRecordLinks',
		'multipleSelects',
		'multilineText',
		'number',
		'percent',
		'phoneNumber',
		'rating',
		'richText',
		'rollup',
		'singleCollaborator.id',
		'singleCollaborator.email',
		'singleCollaborator.name',
		'singleLineText',
		'singleSelect',
		'url',
	);

	/**
	 * Number supported sources.
	 *
	 * @var string[]
	 */
	protected $number_supported_sources = array(
		'autoNumber',
		'count',
		'currency',
		'duration',
		'number',
		'percent',
		'rating',
		'rollup',
	);

	/**
	 * Datetime supported sources.
	 *
	 * @var string[]
	 */
	protected $datetime_supported_sources = array(
		'date',
		'dateTime',
		'createdTime',
		'lastModifiedTime',
	);


	/**
	 * Constructor
	 *
	 * @param Air_WP_Sync_Markdown_Formatter    $markdown_formatter Markdown formatter.
	 * @param Air_WP_Sync_Attachments_Formatter $attachment_formatter Attachment formatter.
	 * @param Air_WP_Sync_Interval_Formatter    $interval_formatter Interval formatter.
	 * @param Air_WP_Sync_Terms_Formatter       $term_formatter Term formatter.
	 */
	public function __construct( $markdown_formatter, $attachment_formatter, $interval_formatter, $term_formatter ) {
		parent::__construct();

		$this->markdown_formatter   = $markdown_formatter;
		$this->attachment_formatter = $attachment_formatter;
		$this->interval_formatter   = $interval_formatter;
		$this->term_formatter       = $term_formatter;

		$this->acf_to_local_types = array(
			'text'             => array(
				'supported_sources' => $this->string_supported_sources,
				'format'            => array( $this, 'format_string' ),
			),
			'textarea'         => array(
				'supported_sources' => $this->string_supported_sources,
				'format'            => array( $this, 'format_string' ),
			),
			'number'           => array(
				'supported_sources' => $this->number_supported_sources,
				'format'            => array( $this, 'format_string' ),
			),
			'range'            => array(
				'supported_sources' => $this->number_supported_sources,
				'format'            => array( $this, 'format_string' ),
			),
			'email'            => array(
				'supported_sources' => array( 'email', 'createdBy.email', 'lastModifiedBy.email', 'multipleCollaborators.email', 'singleCollaborator.email' ),
				'format'            => function ( $value ) {
					if ( ! filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
						$value = '';
					}
					return $value;
				},
			),
			'url'              => array(
				'supported_sources' => array( 'url' ),
				'format'            => function ( $value, $context = array() ) {
					// check based on acf_field_url::validate_value.
					if ( strpos( $value, '://' ) === false && strpos( $value, '//' ) !== 0 ) {
						$value = '';
					}
					return $value;
				},
			),
			'password'         => array(
				'supported_sources' => $this->string_supported_sources,
				'format'            => array( $this, 'format_string' ),
			),
			'image'            => array(
				'supported_sources' => array( 'multipleAttachments' ),
				'format'            => function ( $value, $context = array() ) {
					$value = $this->validate_images( $value );
					return count( $value ) > 0 ? $value[0] : '';
				},
			),
			'gallery'          => array(
				'supported_sources' => array( 'multipleAttachments', 'airwpsyncProxyRecordLinks|multipleAttachments' ),
				'format'            => function ( $value, $context = array() ) {
					$values = array( $value );
					if ( count( $value ) > 0 && is_array( $value[0] ) ) {
						$values = $value;
					}
					$values = array_map(
						function ( $value ) {
							return $this->validate_images( $value );
						},
						$values
					);
					return array_merge( ...$values );
				},
			),
			'file'             => array(
				'supported_sources' => array( 'multipleAttachments' ),
				'format'            => function ( $value, $context = array() ) {
					$value = $this->validate_files( $value );
					return count( $value ) > 0 ? $value[0] : '';
				},
			),
			'wysiwyg'          => array(
				'supported_sources' => $this->string_supported_sources,
				'format'            => array( $this, 'format_string' ),
			),
			'select'           => array(
				'supported_sources' => array( 'singleSelect' ),
				'extra_filter'      => function ( $acf_type, $field ) {
					if ( isset( $field['multiple'] ) && $field['multiple'] ) {
						$acf_type['supported_sources'][] = 'multipleSelects';
					}
					return $acf_type;
				},
			),
			'checkbox'         => array(
				'supported_sources' => array( 'multipleSelects' ),
			),
			'radio'            => array(
				'supported_sources' => array( 'singleSelect' ),
			),
			'button_group'     => array(
				'supported_sources' => array( 'singleSelect' ),
			),
			'true_false'       => array(
				'supported_sources' => array( 'checkbox' ),
			),

			'link'             => array(
				'sub_fields' => array(
					'title' => array(
						'label'             => __( 'Title', 'air-wp-sync' ),
						'supported_sources' => $this->string_supported_sources,
						'format'            => array( $this, 'format_string' ),
					),
					'url'   => array(
						'label'             => __( 'URL', 'air-wp-sync' ),
						'supported_sources' => array( 'url' ),
					),
				),
			),

			'google_map'       => array(
				'sub_fields' => array(
					'address' => array(
						'label'             => __( 'Address', 'air-wp-sync' ),
						'supported_sources' => array( 'singleLineText', 'multilineText' ),
					),
					'lat'     => array(
						'label'             => __( 'Latitude', 'air-wp-sync' ),
						'supported_sources' => array( 'singleLineText' ),
					),
					'lng'     => array(
						'label'             => __( 'Longitude', 'air-wp-sync' ),
						'supported_sources' => array( 'singleLineText' ),
					),
				),
			),

			'date_picker'      => array(
				'supported_sources' => $this->datetime_supported_sources,
				'format'            => function ( $value, $context = array() ) {
					if ( $value instanceof \DateTimeInterface ) {
						$value = $value->format( 'Ymd' );
					} else {
						$value = '';
					}
					return $value;
				},
			),

			'date_time_picker' => array(
				'supported_sources' => $this->datetime_supported_sources,
				'format'            => function ( $value, $context = array() ) {
					if ( $value instanceof \DateTimeInterface ) {
						$value = $value->format( 'Y-m-d H:i:s' );
					} else {
						$value = '';
					}
					return $value;
				},
			),

			'time_picker'      => array(
				'supported_sources' => $this->datetime_supported_sources,
				'format'            => function ( $value, $context = array() ) {
					if ( $value instanceof \DateTimeInterface ) {
						$value = $value->format( 'H:i:s' );
					} else {
						$value = '';
					}
					return $value;
				},
			),

			'page_link'        => array(
				'supported_sources' => array( 'url' ),
				'extra_filter'      => function ( $acf_type, $field ) {
					if ( isset( $field['multiple'] ) && $field['multiple'] ) {
						$acf_type['supported_sources'] = array_merge( $acf_type['supported_sources'], Air_WP_Sync_Link_To_Another_Record_Source::prefix_types( $acf_type['supported_sources'] ) );
					}
					return $acf_type;
				},
				'format'            => function ( $value, $context = array() ) {
					$values = $value;
					if ( ! is_array( $values ) ) {
						$values = array( $value );
					}
					foreach ( $values as &$field_value ) {
						// check based on acf_field_url::validate_value.
						if ( strpos( $field_value, '://' ) === false && strpos( $field_value, '//' ) !== 0 ) {
							$field_value = '';
						}
						if ( $field_value ) {
							$post_id = url_to_postid( $field_value );
							if ( 0 === $post_id ) {
								$field_value = '';
							}
						}
					}
					return $values;
				},
			),

			'user'             => array(
				'supported_sources' => array( 'email', 'createdBy.email', 'lastModifiedBy.email', 'multipleCollaborators.email', 'singleCollaborator.email' ),
				'extra_filter'      => function ( $acf_type, $field ) {
					if ( isset( $field['multiple'] ) && $field['multiple'] ) {
						$acf_type['supported_sources'] = array_merge( $acf_type['supported_sources'], Air_WP_Sync_Link_To_Another_Record_Source::prefix_types( $acf_type['supported_sources'] ) );
					}
					return $acf_type;
				},
				'format'            => function ( $value, $context = array() ) {
					$values = $value;
					if ( ! is_array( $values ) ) {
						$values = array( $value );
					}
					foreach ( $values as &$field_value ) {
						if ( ! filter_var( $field_value, FILTER_VALIDATE_EMAIL ) ) {
							$field_value = false;
						} else {
							$user = get_user_by( 'email', $field_value );
							if ( $user ) {
								$field_value = $user->ID;
							} else {
								$field_value = false;
							}
						}
					}
					return array_values( array_filter( $values ) );
				},
			),

			'post_object'      => array(
				'supported_sources' => array( 'singleLineText', 'singleSelect' ),
				'extra_filter'      => function ( $acf_type, $field ) {
					if ( isset( $field['multiple'] ) && $field['multiple'] ) {
						$acf_type['supported_sources'] = array_merge( $acf_type['supported_sources'], Air_WP_Sync_Link_To_Another_Record_Source::prefix_types( $acf_type['supported_sources'] ) );
						$acf_type['supported_sources'][] = 'multipleSelects';
					}
					return $acf_type;
				},
				'format'            => function ( $value, $context = array() ) {
					$values = $value;
					if ( ! is_array( $values ) ) {
						$values = array( $value );
					}
					foreach ( $values as &$field_value ) {
						if ( is_string( $field_value ) ) {
							$field_value = url_to_postid( home_url( $field_value ) );
							if ( 0 === $field_value ) {
								$field_value = false;
							}
						} else {
							$field_value = false;
						}
					}
					return array_values( array_filter( $values ) );
				},
			),

			'relationship'     => array(
				'supported_sources' => array( 'singleLineText', 'singleSelect' ),
				'extra_filter'      => function ( $acf_type, $field ) {
					if ( ! $field['max'] || $field['max'] > 1 ) {
						$acf_type['supported_sources'] = array_merge( $acf_type['supported_sources'], Air_WP_Sync_Link_To_Another_Record_Source::prefix_types( $acf_type['supported_sources'] ) );
						$acf_type['supported_sources'][] = 'multipleSelects';
					}
					return $acf_type;
				},
				'format'            => function ( $value, $context = array() ) {
					$values = $value;
					if ( ! is_array( $values ) ) {
						$values = array( $value );
					}
					foreach ( $values as &$field_value ) {
						if ( is_string( $field_value ) && ! empty( $field_value ) ) {
							$field_value = url_to_postid( home_url( $field_value ) );
							if ( 0 === $field_value ) {
								$field_value = false;
							}
						} else {
							$field_value = false;
						}
					}

					return array_values( array_filter( $values ) );
				},
			),

			'color_picker'     => array(
				'supported_sources' => array( 'singleLineText', 'singleSelect' ),
			),

			'taxonomy'         => array(
				'supported_sources' => array( 'singleLineText', 'singleSelect', 'multipleSelects' ),
				'extra_filter'      => function ( $acf_type, $field ) {
					if ( isset( $field['field_type'] ) && in_array( $field['field_type'], array( 'checkbox', 'multi_select' ), true ) ) {
						$acf_type['supported_sources'] = array_merge( $acf_type['supported_sources'], Air_WP_Sync_Link_To_Another_Record_Source::prefix_types( $acf_type['supported_sources'] ) );
					}
					return $acf_type;
				},
				'form_options'      => array(
					array(
						'name'  => 'split_comma_separated_string_into_terms',
						'type'  => 'checkbox',
						'label' => __( 'Split comma-separated string into terms', 'air-wp-sync' ),
					),
				),
				'format'            => function ( $value, $context = array() ) {
					$importer = $context['importer'];
					$wp_field_mapping = $context['wp_field_mapping'];
					$split_comma_separated_string_into_terms = ! empty( $context['options']['form_options_values']['split_comma_separated_string_into_terms'] );
					return $this->term_formatter->format( $value, $importer, $wp_field_mapping['acf_field']['taxonomy'], $split_comma_separated_string_into_terms );
				},
			),
		);

		add_filter( 'airwpsync/acf/get-field-conf', array( $this, 'generate_repeater_conf' ), 10, 2 );

		add_action( 'airwpsync/import_record_after', array( $this, 'add_acf_fields' ), 10, 4 );
	}

	/**
	 * Generate repeater field conf on the fly.
	 *
	 * @param array $field_conf Field conf as defined acf_to_local_types.
	 * @param array $field ACF field.
	 *
	 * @return mixed
	 */
	public function generate_repeater_conf( $field_conf, $field ) {
		if ( strpos( $field['type'], 'repeater' ) !== 0 ) {
			return $field_conf;
		}
		if ( strpos( $field['type'], '.' ) === false ) {
			// Dynamically build subfields for the repeater.
			$field_conf['sub_fields'] = array();
			foreach ( $field['sub_fields'] as $sub_field ) {
				$acf_field_conf = $this->get_acf_field_conf( $sub_field );
				if ( 'repeater' === $sub_field['type'] ) {
					// Display a warning, we don't support nested repeater. Make sure we display the message once.
					remove_action( 'airwpsync/metabox/after_mapping', __NAMESPACE__ . '\Air_WP_Sync_Abstract_ACF_Destination::display_repeater_warning' );
					add_action( 'airwpsync/metabox/after_mapping', __NAMESPACE__ . '\Air_WP_Sync_Abstract_ACF_Destination::display_repeater_warning' );
					continue;
				}
				if ( $acf_field_conf ) {
					$sub_field_conf = $this->get_acf_field_conf( $sub_field );
					if ( ! $sub_field_conf ) {
						continue;
					}
					$sub_field_conf          = $this->override_format_and_supported_sources( $sub_field_conf );
					$sub_field_conf['label'] = $sub_field['label'];
					if ( isset( $sub_field_conf['sub_fields'] ) ) {
						foreach ( $sub_field_conf['sub_fields'] as &$sub_sub_field ) {
							$sub_sub_field = $this->override_format_and_supported_sources( $sub_sub_field );
						}
					}
					$field_conf['sub_fields'][ $sub_field['key'] ] = $sub_field_conf;
				}
			}
		} else {
			$key_parts = explode( '.', $field['key'] );

			$field_conf = $this->get_acf_field_conf( get_field_object( $key_parts[0] ) );
			array_shift( $key_parts );
			$count_key_parts = count( $key_parts );
			while ( $count_key_parts > 0 ) {
				$key        = array_shift( $key_parts );
				$field_conf = $field_conf['sub_fields'][ $key ];
				--$count_key_parts;
			}
		}
		return $field_conf;
	}

	/**
	 * Override format and supported_value_types to manage Link to another record field.
	 *
	 * @param array $field_conf Field conf.
	 *
	 * @return array
	 */
	protected function override_format_and_supported_sources( $field_conf ) {
		// If there is a format on the subfield apply this format to all Airtable Fields.
		if ( isset( $field_conf['format'] ) ) {
			$format = $field_conf['format'];
			// Field format should be applied on all fields values.
			$field_conf['format'] = function ( $values, $context ) use ( $format ) {
				$destination_parts = explode( '.', $context['destination'] );
				$final_destination = array_pop( $destination_parts );
				// If we have a subfield, the context should be that of the subfield.
				if ( ! empty( $context['wp_field_mapping']['acf_field']['sub_fields'] ) ) {
					$context['wp_field_mapping']['acf_field'] = array_reduce(
						$context['wp_field_mapping']['acf_field']['sub_fields'],
						function ( $final_config, $sub_field_config ) use ( $final_destination ) {
							if ( $sub_field_config['key'] === $final_destination ) {
								$final_config = $sub_field_config;
							}
							return $final_config;
						},
						$context['wp_field_mapping']['acf_field']
					);
				}
				return array_map(
					function ( $value ) use ( $format, $context ) {
						return call_user_func( $format, $value, $context );
					},
					$values
				);
			};
		}

		if ( isset( $field_conf['supported_sources'] ) ) {
			// Override supported_value_types.
			$field_conf['supported_sources'] = Air_WP_Sync_Link_To_Another_Record_Source::prefix_types( $field_conf['supported_sources'] );
		}
		return $field_conf;
	}

	/**
	 * Reformat composed fields for repeater.
	 * The value is formatted by subfield: [ 'repeater_field_key' => [ 'sub_field_key' => [ value1, value2 ] ] ]
	 * Reformat like: [ 'repeater_field_key' => [ [ 'sub_field_key' => value1 ], [ 'sub_field_key' => value2 ] ]
	 *
	 * @param array $composed_fields Composed fields as built by `add_acf_fields` method.
	 *
	 * @return array
	 */
	public function reformat_repeater_composed_fields( $composed_fields ) {

		foreach ( $composed_fields as $composed_field_key => $composed_field ) {
			$acf_field = get_field_object( $composed_field_key );
			if ( 'repeater' === $acf_field['type'] ) {
				$new_value = array();
				foreach ( $composed_field as $composed_sub_field_key => $values ) {
					foreach ( $values as $index => $value ) {
						if ( is_numeric( $index ) ) {
							if ( ! isset( $new_value[ $index ] ) ) {
								$new_value[] = array();
							}
							$new_value[ $index ][ $composed_sub_field_key ] = $value;
						} else {
							// We need to go deeper... (ex link -> url).
							foreach ( $value as $sub_index => $sub_value ) {
								$new_value[ $sub_index ][ $composed_sub_field_key ][ $index ] = $sub_value;
							}
						}
					}
				}
				$composed_fields[ $composed_field_key ] = $new_value;
			}
		}
		return $composed_fields;
	}

	/**
	 * Display warning about repeaters, we don't support nested repeater yet.
	 *
	 * @return void
	 */
	public static function display_repeater_warning() {
		$message = __( 'Please note that we don\'t support nested repeater yet.', 'air-wp-sync' );
		echo '<div class="notice notice-warning inline" style="margin: 20px;"><p>' . esc_html( $message ) . '</p></div>';
	}

	/**
	 * Get field config from the "acf_to_local_types" property.
	 * The field type could be a type from ACF or a composed one like "link.url".
	 *
	 * @param array $field The ACF field.
	 *
	 * @return array
	 */
	protected function get_acf_field_conf( $field ) {
		$confs = $this->acf_to_local_types;
		$type  = $field['type'];
		if ( strpos( $type, '.' ) !== false ) {
			$type_parts = explode( '.', $type );
			$type       = array_pop( $type_parts );
			foreach ( $type_parts as $sub_type ) {
				if ( isset( $confs[ $sub_type ]['sub_fields'] ) ) {
					$confs = $confs[ $sub_type ]['sub_fields'];
				}
			}
		}
		$acf_field_conf = $confs[ $type ] ?? array();

		if ( isset( $acf_field_conf['extra_filter'] ) ) {
			$acf_field_conf = $acf_field_conf['extra_filter']( $acf_field_conf, $field );
		}
		return apply_filters( 'airwpsync/acf/get-field-conf', $acf_field_conf, $field );
	}

	/**
	 * Handle ACF field importing.
	 *
	 * @param Air_WP_Sync_Importer $importer Importer.
	 * @param array                $fields Fields.
	 * @param \stdClass            $record The Airtable object.
	 * @param int                  $post_id The post id.
	 */
	public function add_acf_fields( $importer, $fields, $record, $post_id ) {
		$mapped_fields   = $this->get_destination_mapping( $importer, $fields );
		$composed_fields = array();

		foreach ( $mapped_fields as $mapped_field ) {

			// Get ACF field key.
			$key = $mapped_field['wordpress'];

			// Save ACF field.
			if ( ! empty( $key ) ) {
				$key_parts = explode( '.', $key );

				// Remove group from key.
				array_shift( $key_parts );
				$key = implode( '.', $key_parts );

				// Get meta value.
				$value = $this->get_airtable_value( $fields, $mapped_field['airtable'], $importer );

				// Get value.
				$value = $this->format( $value, $mapped_field, $importer, $post_id );

				// if it's a composed field add it to the $composed_fields variable.
				// e.g link.url -> $composed_fields['link']['url'].
				if ( count( $key_parts ) > 1 ) {
					if ( ! isset( $composed_fields[ $key_parts[0] ] ) ) {
						$composed_fields[ $key_parts[0] ] = array();
					}
					// At some point the code below should be recursive (e.g manage sub repeaters).
					if ( isset( $key_parts[2] ) ) {
						if ( ! isset( $composed_fields[ $key_parts[0] ][ $key_parts[1] ] ) ) {
							$composed_fields[ $key_parts[0] ][ $key_parts[1] ] = array();
						}
						$composed_fields[ $key_parts[0] ][ $key_parts[1] ][ $key_parts[2] ] = $value;
					} else {
						$composed_fields[ $key_parts[0] ][ $key_parts[1] ] = $value;
					}

					continue;
				}
				$this->update_field( $key, $value, $post_id );
			}
		}

		$composed_fields = $this->reformat_repeater_composed_fields( $composed_fields );

		// All composed fields have been regrouped, we can now import them.
		foreach ( $composed_fields as $composed_field_key => $composed_field_values ) {
			$current_value = get_field( $composed_field_key, $post_id );
			$new_value     = $composed_field_values;
			// Merge only if the last value is not an indexed array.
			if ( is_array( $current_value ) && is_array( $composed_field_values ) && ! isset( $composed_field_values[0] ) ) {
				// Merge current value and new value so if some subfields are unmapped there are not lost.
				$new_value = array_merge( $current_value, $composed_field_values );
			}
			$this->update_field( $composed_field_key, $new_value, $post_id );
		}
	}

	/**
	 * Update ACF field.
	 *
	 * @param string $key Field key.
	 * @param mixed  $value Field valye.
	 * @param int    $destination_id WordPress destination object id.
	 *
	 * @return void
	 */
	abstract public function update_field( $key, $value, $destination_id );


	/**
	 * Retrieve ACF fields. Optionally filter them with the $filters param (@see acf_get_field_groups).
	 *
	 * @param array $filters Filters passed to `acf_get_field_groups`.
	 *
	 * @return array
	 */
	protected function get_acf_fields( $filters = array() ) {
		$all_fields = array();
		$groups     = $this->acf_get_field_groups( $filters );

		foreach ( $groups as $group ) {
			$fields     = acf_get_fields( $group['key'] );
			$fields     = array_reduce(
				$fields,
				function ( $result, $field ) use ( $group ) {
					$result = $this->flatten_conf( $result, $group, $field );

					return $result;
				},
				array()
			);
			$all_fields = array_merge( $all_fields, $fields );
		}

		return $all_fields;
	}

	/**
	 * Returned filtered ACF fields groups.
	 *
	 * @param array $filters Filters.
	 *
	 * @return array
	 */
	protected function acf_get_field_groups( $filters = array() ) {
		return acf_get_field_groups( $filters );
	}

	/**
	 * Flatten config array.
	 *
	 * @param array $result Carry.
	 * @param array $group ACF Field group config.
	 * @param array $field ACF Field config.
	 *
	 * @return array
	 */
	protected function flatten_conf( $result, $group, $field ) {
		$acf_field_conf = $this->get_acf_field_conf( $field );

		$field['group'] = $group;
		if ( isset( $field['_clone'] ) ) {
			$field['key'] = $field['__key'];
		}
		if ( isset( $acf_field_conf['sub_fields'] ) ) {
			$result = $this->flatten_sub_fields_conf( $result, $acf_field_conf['sub_fields'], $field );
		} else {
			$result[] = $field;
		}

		return $result;
	}

	/**
	 * Flatten sub fields config array.
	 *
	 * @param array $result Carry.
	 * @param array $sub_fields_conf Sub fields config array.
	 * @param array $field ACF Field config.
	 * @param array $sub_fields_labels List of sub fields labels.
	 *
	 * @return array
	 */
	protected function flatten_sub_fields_conf( $result, $sub_fields_conf, $field, $sub_fields_labels = array() ) {
		foreach ( $sub_fields_conf as $sub_field_key => $sub_field ) {
			$sub_sub_fields_labels = array_merge( $sub_fields_labels, array( $sub_field['label'] ) );
			$field_edited          = array_merge(
				array(),
				$field,
				array(
					'type' => $field['type'] . '.' . $sub_field_key,
					'key'  => $field['key'] . '.' . $sub_field_key,
				)
			);
			if ( isset( $sub_field['sub_fields'] ) ) {
				$result = $this->flatten_sub_fields_conf( $result, $sub_field['sub_fields'], $field_edited, $sub_sub_fields_labels );
			} else {
				$field_edited['label'] = $field_edited['label'] . ' (' . implode( ' > ', $sub_sub_fields_labels ) . ')';
				$result[]              = $field_edited;
			}
		}
		return $result;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return array
	 */
	protected function get_group() {
		return array(
			'label' => __( 'ACF', 'air-wp-sync' ),
			'slug'  => 'acf',
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return array
	 */
	protected function get_mapping_fields() {
		$fields = $this->get_acf_fields();

		return array_map(
			function ( $field ) {
				$acf_field_conf = $this->get_acf_field_conf( $field );
				return array(
					'value'             => $field['group']['key'] . '.' . $field['key'],
					'label'             => $field['group']['title'] . ' / ' . $field['label'],
					'enabled'           => true,
					'supported_sources' => $acf_field_conf['supported_sources'] ?? array(),
					'acf_type'          => $field['type'],
					'acf_field'         => $field,
					'form_options'      => $acf_field_conf['form_options'] ?? array(),
				);
			},
			$fields
		);
	}

	/**
	 * Format imported value
	 *
	 * @param mixed                         $value The Airtable field value.
	 * @param array                         $mapped_field Mapped field config.
	 * @param Air_WP_Sync_Abstract_Importer $importer Importer.
	 * @param int                           $post_id The post id.
	 *
	 * @return mixed
	 */
	protected function format( $value, $mapped_field, $importer, $post_id ) {
		$airtable_id      = $mapped_field['airtable'];
		$destination      = $mapped_field['wordpress'];
		$options          = $mapped_field['options'];
		$wp_field_mapping = $this->get_field_mapping( $destination );
		$source_type      = $this->get_source_type( $airtable_id, $importer );
		if ( ! $wp_field_mapping ) {
			return $value;
		}

		$value = $this->pre_format_value( $value, $source_type, $mapped_field, $importer, $post_id );

		// Additional formatting for this field?
		$acf_field_conf = $this->get_acf_field_conf( $wp_field_mapping['acf_field'] );
		if ( isset( $acf_field_conf['format'] ) ) {
			$value = call_user_func(
				$acf_field_conf['format'],
				$value,
				array(
					'importer'         => $importer,
					'destination'      => $destination,
					'wp_field_mapping' => $wp_field_mapping,
					'options'          => $options,
				)
			);
		}
		return $value;
	}

	/**
	 * Preformat value based on its source type.
	 *
	 * @param mixed                         $value Field value.
	 * @param string                        $source_type Source type.
	 * @param array                         $mapped_field Field mapping conf.
	 * @param Air_WP_Sync_Abstract_Importer $importer Importer.
	 * @param mixed                         $post_id WordPress object id.
	 *
	 * @return mixed
	 */
	protected function pre_format_value( $value, $source_type, $mapped_field, $importer, $post_id ) {

		if ( 'richText' === $source_type ) {
			$value = $this->markdown_formatter->format( $value );
		} elseif ( in_array( $source_type, $this->datetime_supported_sources, true ) ) {
			if ( strtotime( $value ) ) {
				$value = new \DateTimeImmutable( $value );
				if ( $value instanceof \DateTimeInterface ) {
					$value = $value->setTimezone( new \DateTimeZone( wp_timezone_string() ) );
				}
			} else {
				$value = '';
			}
		} elseif ( 'duration' === $source_type ) {
			$field = $this->get_field_by_id( $mapped_field['airtable'], $importer );
			$value = $this->interval_formatter->format( $value, $field );
		} elseif ( 'multipleAttachments' === $source_type ) {
			$value = $this->attachment_formatter->format( $value, $importer, $post_id );
		} elseif ( strpos( $source_type, 'airwpsyncProxyRecordLinks|' ) === 0 ) {
			$proxy_type = explode( '|', $source_type )[1];
			if ( $proxy_type ) {
				$value = array_map(
					function ( $field_value ) use ( $proxy_type, $mapped_field, $importer, $post_id ) {
						return $this->pre_format_value( $field_value, $proxy_type, $mapped_field, $importer, $post_id );
					},
					$value
				);
			}
		}

		return $value;
	}

	/**
	 * Make sure we have a string at the end.
	 *
	 * @param mixed $value The value to format.
	 * @param array $context Importer and mapping context.
	 *
	 * @return string
	 */
	protected function format_string( $value, $context = array() ) {
		if ( $value instanceof \DateTimeInterface ) {
			$value = date_i18n( get_option( 'date_format' ), $value->getTimestamp() );
		} elseif ( is_array( $value ) ) {
			$value = implode( ', ', $value );
		} else {
			$value = strval( $value );
		}

		return $value;
	}

	/**
	 * Filter a list of attachment id, return only image attachments.
	 *
	 * @param int[] $value list of attachment id.
	 *
	 * @return array
	 */
	protected function validate_images( $value ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$image_mime_types = array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp' );
		$value            = array_filter(
			$value,
			function ( $attachment_id ) use ( $image_mime_types ) {
				return in_array( get_post_mime_type( $attachment_id ), $image_mime_types, true );
			}
		);

		return array_values( $value );
	}

	/**
	 * Filter a list of attachment id, return only ones > 0.
	 *
	 * @param int[] $value list of attachment id.
	 *
	 * @return array
	 */
	protected function validate_files( $value ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$value = array_filter(
			$value,
			function ( $attachment_id ) {
				return $attachment_id > 0;
			}
		);

		return array_values( $value );
	}

	/**
	 * Return field mapping based on the field type.
	 *
	 * @param string $field_id The field id.
	 *
	 * @return array|null
	 */
	public function get_field_mapping( $field_id ) {
		$fields        = $this->get_mapping_fields();
		$field_mapping = null;
		foreach ( $fields as $field ) {
			if ( $field['value'] === $field_id ) {
				$field_mapping = $field;
				break;
			}
		}
		return $field_mapping;
	}
}
