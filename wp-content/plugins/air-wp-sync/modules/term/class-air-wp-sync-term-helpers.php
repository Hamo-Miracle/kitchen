<?php
/**
 * Term module helper functions.
 *
 * @package Air_WP_Sync_Free
 */

namespace Air_WP_Sync_Free;

/**
 * Term Helper functions
 */
class Air_WP_Sync_Term_Helpers {

	/**
	 * Get available taxonomies.
	 *
	 * @return WP_Taxonomy[]
	 */
	public static function get_taxonomies() {
		$taxonomies = get_taxonomies(
			array( 'public' => true ),
			'objects'
		);
		return $taxonomies;
	}
}
