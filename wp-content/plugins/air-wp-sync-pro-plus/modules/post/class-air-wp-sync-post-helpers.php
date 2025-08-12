<?php
/**
 * Post Module helpers.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

/**
 * Class Air_WP_Sync_Post_Helpers
 */
class Air_WP_Sync_Post_Helpers {
	/**
	 * Get available post types
	 *
	 * @return array
	 */
	public static function get_post_types() {
		$excluded = array(
			// WP.
			'attachment',
			// WPC.
			'ntwpsync-connection',
			'ntwpsync-content',
			'airwpsync-connection',
			// Others known incompatible post types.
			'acf-field-group',
			'acf-field',
			'wpforms',
			'e-landing-page',
			'elementor_library',
			'elementor_snippet',
			'elementor_font',
			'elementor_icons',
			'elementor-hf',
			'wpcf7_contact_form',
			'et_tb_item',
			'et_code_snippet',
			'et_theme_builder',
			'et_template',
			'et_header_layout',
			'et_body_layout',
			'et_pb_layout',
			'et_footer_layout',
			'fl-builder-template',
			'fl-theme-layout',
			'mc4wp-form',
			'polylang_mo',
			'edd_payment',
			'edd_discount',
			'edd_license',
			'edd_license_log',
			'edd_receipt',
			'edd_subscription_log',
			'product_variation',
			'shop_order',
			'shop_order_refund',
			'shop_coupon',
			'shop_order_placehold',
			'nf_sub',
		);

		$post_types = array();

		$wp_post_types = get_post_types( null, 'objects' );

		foreach ( $wp_post_types as $wp_post_type ) {
			// Skip excluded post types.
			if ( in_array( $wp_post_type->name, $excluded, true ) ) {
				continue;
			}
			// Skip WP private post types.
			if ( $wp_post_type->_builtin && ! $wp_post_type->public ) {
				continue;
			}

			$post_types[] = array(
				'value'   => $wp_post_type->name,
				'label'   => $wp_post_type->labels->singular_name,
				'enabled' => true,
			);
		}

		$post_types[] = array(
			'value'   => 'custom',
			'label'   => __( 'Create new post type...', 'air-wp-sync' ),
			'enabled' => true,
		);

		/**
		 * Filter available post types.
		 *
		 * @param array $post_types Available post types.
		 */
		return apply_filters( 'airwpsync/get_post_types', $post_types );
	}

	/**
	 * Get available post stati.
	 *
	 * @return array
	 */
	public static function get_post_stati() {
		$post_stati    = array();
		$wp_post_stati = get_post_stati(
			array( 'internal' => false ),
			'objects'
		);

		foreach ( $wp_post_stati as $wp_post_status ) {
			$post_stati[] = array(
				'value'   => $wp_post_status->name,
				'label'   => $wp_post_status->label,
				'enabled' => true,
			);
		}

		/**
		 * Filters available post stati
		 *
		 * @param array $post_stati Post stati.
		 */
		return apply_filters( 'airwpsync/get_post_stati', $post_stati );
	}

	/**
	 * Get post authors
	 *
	 * @return array
	 */
	public static function get_post_authors() {
		$authors    = array();
		$wp_authors = get_users( array( 'role__in' => array( 'administrator', 'editor', 'author', 'contributor' ) ) );
		foreach ( $wp_authors as $wp_author ) {
			$authors[] = array(
				'value'   => $wp_author->ID,
				'label'   => $wp_author->display_name,
				'enabled' => true,
			);
		}

		/**
		 * Filters available post authors
		 *
		 * @param array $authors Post authors.
		 */
		return apply_filters( 'airwpsync/get_post_authors', $authors );
	}
}
