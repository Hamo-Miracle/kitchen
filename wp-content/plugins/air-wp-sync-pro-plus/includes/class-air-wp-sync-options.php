<?php
/**
 * Plugin options.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

/**
 * Class Air_WP_Sync_Options
 */
class Air_WP_Sync_Options extends Air_WP_Sync_Abstract_Settings {
	/**
	 * WP option slug
	 *
	 * @var string
	 */
	protected $option_slug = 'air_wp_sync_options';

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( $this->load_options() );
	}

	/**
	 * Save settings to DB
	 */
	public function save() {
		update_option( $this->option_slug, $this->settings );
	}

	/**
	 * Load options from DB
	 */
	protected function load_options() {
		return get_option( $this->option_slug );
	}

	/**
	 * Delete options from DB
	 */
	public function delete_options() {
		return delete_option( $this->option_slug );
	}
}
