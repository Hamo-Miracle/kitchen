<?php
/**
 * Term Module.
 *
 * @package Air_WP_Sync_Free
 */

namespace Air_WP_Sync_Free;

require_once AIR_WP_SYNC_PLUGIN_DIR . 'modules/term/class-air-wp-sync-term-helpers.php';
require_once AIR_WP_SYNC_PLUGIN_DIR . 'modules/term/class-air-wp-sync-term-importer.php';
require_once AIR_WP_SYNC_PLUGIN_DIR . 'modules/term/destinations/class-air-wp-sync-term-destination.php';
require_once AIR_WP_SYNC_PLUGIN_DIR . 'modules/term/destinations/class-air-wp-sync-term-meta-destination.php';

/**
 * Class Air_WP_Sync_Term_Module.
 */
class Air_WP_Sync_Term_Module extends Air_WP_Sync_Abstract_Module {
	/**
	 * Module slug.
	 *
	 * @var string
	 */
	protected $slug = 'term';

	/**
	 * Module name.
	 *
	 * @var string
	 */
	protected $name = 'Taxonomy Term';

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'admin_enqueue_scripts', array( $this, 'register_styles_scripts' ) );
		add_filter( 'airwpsync/get_l10n_strings', array( $this, 'add_l10n_strings' ) );
		add_action( 'airwpsync/register_destination', array( $this, 'register_destinations' ) );
		add_filter( 'airwpsync/mapping_validation_rules', array( $this, 'add_mapping_validation_rules' ), 10, 1 );
	}

	/**
	 * Register admin styles and scripts
	 */
	public function register_styles_scripts() {
		$screen = get_current_screen();
		if ( is_object( $screen ) && 'airwpsync-connection' === $screen->id ) {
			wp_enqueue_script( 'air-wp-sync-term-hooks', plugins_url( 'modules/term/assets/js/hooks.js', AIR_WP_SYNC_PLUGIN_FILE ), array( 'air-wp-sync-admin' ), AIR_WP_SYNC_VERSION, false );
		}
	}

	/**
	 * Add module l10n strings
	 *
	 * @param array $l10n_strings Localization strings.
	 *
	 * @return array
	 */
	public function add_l10n_strings( $l10n_strings ) {
		return array_merge(
			$l10n_strings,
			array(
				'requiredTermNameErrorMessage' => __( 'It is mandatory to map the term name.', 'air-wp-sync' ),
			)
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param \WP_Post $post Post object holding the importer config.
	 *
	 * @return void
	 */
	public function render_settings( $post ) {
		$taxonomies = Air_WP_Sync_Term_Helpers::get_taxonomies();
		$view       = include_once __DIR__ . '/views/settings.php';
		$view( $taxonomies );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param \WP_Post $post Post object holding the importer config.
	 */
	public function get_importer_instance( $post ) {
		return new Air_WP_Sync_Term_Importer( $post, $this, new Air_WP_Sync_Filters() );
	}

	/**
	 * Register destinations
	 */
	public function register_destinations() {
		new Air_WP_Sync_Term_Destination( new Air_WP_Sync_Markdown_Formatter( new Air_WP_Sync_Parsedown() ), new Air_WP_Sync_Interval_Formatter() );
		new Air_WP_Sync_Term_Meta_Destination( new Air_WP_Sync_Markdown_Formatter( new Air_WP_Sync_Parsedown() ), new Air_WP_Sync_Attachments_Formatter() );
	}

	/**
	 * Add required email rule to mapping rules
	 *
	 * @param array $rules Mapping validation rules.
	 */
	public function add_mapping_validation_rules( $rules ) {
		$rules[] = 'requiredTermName';
		return $rules;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_mapping_options() {
		return apply_filters( 'airwpsync/get_wp_fields', array(), $this->slug );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_extra_config() {
		return array(
			'featuresByTaxonomy' => $this->get_features_by_taxonomy(),
		);
	}

	/**
	 * Get available features by post type
	 *
	 * @return array
	 */
	protected function get_features_by_taxonomy() {
		$features = array();

		foreach ( Air_WP_Sync_Term_Helpers::get_taxonomies() as $taxonomy ) {
			/**
			 * Filters features by taxonomy.
			 *
			 * @param array $features Features.
			 * @param WP_Taxonomy $taxonomy Taxonomy.
			 */
			$features[ $taxonomy->name ] = apply_filters(
				'airwpsync/features_by_taxonomy',
				array(),
				$taxonomy
			);
		}

		return $features;
	}
}
