<?php
/**
 * Manages import as ACF field on posts.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

/**
 * Air_WP_Sync_ACF_Destination_Post class.
 */
class Air_WP_Sync_ACF_Destination_Post extends Air_WP_Sync_Abstract_ACF_Destination {

	/**
	 * Module slug.
	 *
	 * @var string
	 */
	protected $module = 'post';

	/**
	 * {@inheritDoc}
	 *
	 * @param Air_WP_Sync_Markdown_Formatter    $markdown_formatter Markdown formatter.
	 * @param Air_WP_Sync_Attachments_Formatter $attachment_formatter Attachment formatter.
	 * @param Air_WP_Sync_Interval_Formatter    $interval_formatter Interval formatter.
	 * @param Air_WP_Sync_Terms_Formatter       $term_formatter Term formatter.
	 */
	public function __construct( $markdown_formatter, $attachment_formatter, $interval_formatter, $term_formatter ) {
		parent::__construct( $markdown_formatter, $attachment_formatter, $interval_formatter, $term_formatter );

		add_filter( 'airwpsync/features_by_post_type', array( $this, 'add_features_by_post_type' ), 10, 2 );
	}

	/**
	 * Update post's ACF field.
	 *
	 * @param string $key Field key.
	 * @param mixed  $value Field valye.
	 * @param int    $destination_id WordPress destination object id.
	 *
	 * @return void
	 */
	public function update_field( $key, $value, $destination_id ) {
		update_field( $key, $value, $destination_id );
	}

	/**
	 * Add field features for each post types
	 *
	 * @param array  $features Features list.
	 * @param string $post_type Post type.
	 *
	 * @return string[]
	 */
	public function add_features_by_post_type( $features, $post_type ) {
		$destination_features = array();

		$fields = $this->get_acf_fields( array( 'post_type' => $post_type ) );
		foreach ( $fields as $field ) {
			$destination_features[] = $field['group']['key'] . '.' . $field['key'];
		}

		$features[ $this->slug ] = $destination_features;

		return $features;
	}

	/**
	 * Change filter logic to include all groups with at least one rule about the post_type in the filters.
	 *
	 * @param array $filters Filters passed to `acf_get_field_groups`.
	 *
	 * @return array
	 */
	protected function acf_get_field_groups( $filters = array() ) {
		if ( isset( $filters['post_type'] ) ) {
			$groups = acf_get_field_groups( array() );
			$args   = $filters;
			$groups = array_filter(
				$groups,
				function ( $field_group ) use ( $args ) {
					// Check if active.
					if ( ! $field_group['active'] ) {
						return false;
					}

					// Check if location rules exist.
					if ( $field_group['location'] ) {

						// Get the current screen.
						$screen = acf_get_location_screen( $args );

						// Loop through location groups.
						foreach ( $field_group['location'] as $group ) {

							// ignore group if no rules.
							if ( empty( $group ) ) {
								continue;
							}

							// Loop over rules and determine if at least one rule match.
							$match_group = false;
							foreach ( $group as $rule ) {
								if ( acf_match_location_rule( $rule, $screen, $field_group ) ) {
									$match_group = true;
									break;
								}
							}

							// If this group matches, show the field group.
							if ( $match_group ) {
								return true;
							}
						}
					}

					// Default.
					return false;
				}
			);

			return $groups;
		} else {
			return parent::acf_get_field_groups( $filters );
		}
	}
}
