<?php
/**
 * Abstract Module.
 * Base class to define custom module (e.g post, user importer).
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

/**
 * Abstract Module
 */
abstract class Air_WP_Sync_Abstract_Module {
	/**
	 * Module slug
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * Module name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Constructor
	 *
	 * @throws \Exception If the $slug or the $name property have not been set.
	 */
	public function __construct() {
		if ( ! isset( $this->slug ) ) {
			throw new \Exception( esc_html( get_class( $this ) . ' must have a $slug property' ) );
		}
		if ( ! isset( $this->name ) ) {
			throw new \Exception( esc_html( get_class( $this ) . ' must have a $name property' ) );
		}

		add_filter( 'airwpsync/get_modules', array( $this, 'register' ) );
	}

	/**
	 * Register module
	 *
	 * @param Air_WP_Sync_Abstract_Module[] $modules Registered modules.
	 */
	public function register( $modules ) {
		return array_merge( $modules, array( $this->get_slug() => $this ) );
	}

	/**
	 * Slug getter
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Name getter
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Render module settings
	 *
	 * @param \WP_Post $post Post object holding the importer config.
	 *
	 * @return void
	 */
	abstract public function render_settings( $post );

	/**
	 * Get importer instance
	 *
	 * @param \WP_Post $post Post object holding the importer config.
	 *
	 * @return Air_WP_Sync_Abstract_Importer
	 */
	abstract public function get_importer_instance( $post );

	/**
	 * Get mapping options
	 *
	 * @return array
	 */
	abstract public function get_mapping_options();

	/**
	 * Get extra config
	 *
	 * @return array
	 */
	public function get_extra_config() {
		return array();
	}
}
