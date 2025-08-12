<?php
/**
 * User Destination.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

/**
 * Class Air_WP_Sync_User_Destination
 */
class Air_WP_Sync_User_Destination extends Air_WP_Sync_Abstract_Destination {
	/**
	 * Destination slug.
	 *
	 * @var string
	 */
	protected $slug = 'user';

	/**
	 * Module slug.
	 *
	 * @var string
	 */
	protected $module = 'user';

	/**
	 * Markdown formatter.
	 *
	 * @var Air_WP_Sync_Markdown_Formatter
	 */
	protected $markdown_formatter;

	/**
	 * Interval formatter.
	 *
	 * @var Air_WP_Sync_Interval_Formatter
	 */
	protected $interval_formatter;

	/**
	 * String supported sources.
	 *
	 * @var string[]
	 */
	protected $string_supported_sources = array(
		'autoNumber',
		'barcode.type',
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
	 * Constructor
	 *
	 * @param Air_WP_Sync_Markdown_Formatter $markdown_formatter Markdown formatter.
	 * @param Air_WP_Sync_Interval_Formatter $interval_formatter Interval formatter.
	 */
	public function __construct( $markdown_formatter, $interval_formatter ) {
		parent::__construct();

		$this->markdown_formatter = $markdown_formatter;
		$this->interval_formatter = $interval_formatter;

		add_filter( 'airwpsync/import_user_data', array( $this, 'add_to_user_data' ), 20, 3 );
		add_action( 'airwpsync/metabox_mapping_wordpress_after', array( $this, 'add_username_notice' ) );
	}

	/**
	 * Handle user data importing
	 *
	 * @param array                     $post_data Post data.
	 * @param Air_WP_Sync_Post_Importer $importer Post importer.
	 * @param array                     $fields Fields.
	 */
	public function add_to_user_data( $post_data, $importer, $fields ) {
		$mapped_fields = $this->get_destination_mapping( $importer );

		foreach ( $mapped_fields as $mapped_field ) {
			$value                                   = $this->get_airtable_value( $fields, $mapped_field['airtable'], $importer );
			$post_data[ $mapped_field['wordpress'] ] = $this->format( $value, $mapped_field, $importer );
		}
		return $post_data;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return array
	 */
	protected function get_group() {
		return array(
			'label' => __( 'User', 'air-wp-sync' ),
			'slug'  => 'user',
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return array
	 */
	protected function get_mapping_fields() {
		return array(
			array(
				'value'             => 'user_login',
				'label'             => __( 'Username', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => $this->string_supported_sources,
				'notice'            => __( 'Please note that usernames cannot be changed once created.', 'air-wp-sync' ),
			),
			array(
				'value'             => 'first_name',
				'label'             => __( 'First name', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => $this->string_supported_sources,
			),
			array(
				'value'             => 'last_name',
				'label'             => __( 'Last name', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => $this->string_supported_sources,
			),
			array(
				'value'             => 'nickname',
				'label'             => __( 'Nickname', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => $this->string_supported_sources,
			),
			array(
				'value'             => 'user_email',
				'label'             => __( 'Email', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => $this->string_supported_sources,
			),
			array(
				'value'             => 'user_url',
				'label'             => __( 'Website', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => $this->string_supported_sources,
			),
			array(
				'value'             => 'description',
				'label'             => __( 'Biographical Info', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => $this->string_supported_sources,
			),
			array(
				'value'             => 'role',
				'label'             => __( 'Role', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => $this->string_supported_sources,
			),
			array(
				'value'             => 'locale',
				'label'             => __( 'Locale', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => $this->string_supported_sources,
			),
			array(
				'value'             => 'user_registered',
				'label'             => __( 'Registered Date', 'air-wp-sync' ),
				'enabled'           => true,
				'supported_sources' => array(
					'date',
					'dateTime',
					'createdTime',
					'lastModifiedTime',
				),
			),
		);
	}

	/**
	 * Format imported value
	 *
	 * @param mixed                     $value Field value.
	 * @param array                     $mapped_field Field mapping conf.
	 * @param Air_WP_Sync_User_Importer $importer User importer.
	 */
	protected function format( $value, $mapped_field, $importer ) {
		$airtable_id = $mapped_field['airtable'];
		$destination = $mapped_field['wordpress'];
		$source_type = $this->get_source_type( $airtable_id, $importer );

		if ( 'user_registered' === $destination ) {
			if ( ! empty( $value ) && false === strtotime( $value ) ) {
				$value = null;
			}
			$value = iso8601_to_datetime( $value );
		} elseif ( 'richText' === $source_type ) {
				// Markdown.
				$value = $this->markdown_formatter->format( $value );
		} elseif ( in_array( $source_type, array( 'date', 'dateTime' ), true ) ) {
			// Date.
			$value = date_i18n( get_option( 'date_format' ), strtotime( $value ) );
		} elseif ( 'duration' === $source_type ) {
			$field = $this->get_field_by_id( $airtable_id, $importer );
			$value = $this->interval_formatter->format( $value, $field );
		} elseif ( is_array( $value ) ) {
			// Multiple values.
			$value = implode( ', ', $value );
		} else {
			// Default string.
			$value = strval( $value );
		}

		return $value;
	}
}
