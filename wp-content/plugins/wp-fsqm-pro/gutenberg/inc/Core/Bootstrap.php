<?php
/**
 * EForm Gutenberg Block bootstrapping
 *
 * @package EForm
 * @subpackage Gutenberg
 */

namespace EFormV4\Core;

/**
 * Bootstrap all EForm related Gutenberg blocks.
 */
class Bootstrap {
	public static function get_blocks() {
		return [
			// Forms
			'\\EFormV4\\Blocks\\Form\\Embed',
			'\\EFormV4\\Blocks\\Form\\Popup',
			// Reports
			'\\EFormV4\\Blocks\\Report\\Trends',
			'\\EFormV4\\Blocks\\Report\\Leaderboard',
			'\\EFormV4\\Blocks\\Report\\Stat',
			// System
			'\\EFormV4\\Blocks\\System\\Portal',
			'\\EFormV4\\Blocks\\System\\Trackback',
			'\\EFormV4\\Blocks\\System\\Login',
		];
	}

	public static function enqueue_block_assets() {
		// prepare all forms and themes
		$all_forms = \IPT_FSQM_Form_Elements_Static::get_forms_for_select_with_count();
		$all_themes = \IPT_FSQM_Form_Elements_Static::get_form_themes_for_select();
		$all_users = \IPT_FSQM_Form_Elements_Static::get_existing_submission_users_for_select();
		unset( $all_themes['material-custom'] );

		// prepare the attributes
		$attributes = [];
		foreach ( self::get_blocks() as $block ) {
			/**
			 * @var \EFormV4\Blocks\Base $block_instance
			 */
			$block_instance = new $block();
			$attributes[ $block_instance->get_block_name() ] = $block_instance->get_block_config()['attributes'];
		}

		\wp_enqueue_script(
			'eform-gutenberg-blocks',
			plugins_url( 'gutenberg/dist/eform-gutenberg.js', \IPT_FSQM_Loader::$abs_file ),
			[ 'wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-editor' ],
			\IPT_FSQM_Loader::$version
		);
		// Also gutenberg localization
		\wp_set_script_translations(
			'eform-gutenberg-blocks',
			'ipt_fsqm',
			basename( dirname( \IPT_FSQM_Loader::$abs_file ) ) . '/translations'
		);
		// Pass in some data
		\wp_localize_script( 'eform-gutenberg-blocks', 'eFormGTB', [
			'forms' => $all_forms,
			'themes' => $all_themes,
			'users' => $all_users,
			'attributes' => $attributes,
			'i18n' => [
				'embedEForm' => __( 'Embed eForm', 'ipt_fsqm' ),
				'selectForm' => __( 'Select Form', 'ipt_fsqm' ),
				'selectFormOption' => __( '--please select a form--', 'ipt_fsqm' ),
			],
		] );

		\wp_localize_script( 'eform-gutenberg-blocks', 'iptFSQMTML10n', \IPT_EForm_Shortcodes_TinyMCE::get_localizations() );

		// Enqueue style
		\wp_enqueue_style(
			'eform-gutenberg-blocks',
			plugins_url( 'gutenberg/dist/eform-gutenberg-style.css', \IPT_FSQM_Loader::$abs_file ),
			[],
			\IPT_FSQM_Loader::$version
		);
	}

	public static function register() {
		$eform_blocks = self::get_blocks();
		foreach ( $eform_blocks as $block ) {
			( new $block() )->register();
		}
	}

	public static function extend_gutenberg_categories( $categories, $post ) {
		$categories = array_merge(
			$categories,
			[
				[
					'slug' => 'eform-v4',
					'title' => __( 'eForm', 'ipt_fsqm' ),
				],
			]
		);
		return $categories;
	}

	public static function init() {
		add_action( 'init', [ __CLASS__, 'register' ] );
		add_action( 'enqueue_block_editor_assets', [ __CLASS__, 'enqueue_block_assets' ] );
		add_filter( 'block_categories', [ __CLASS__, 'extend_gutenberg_categories' ], 10, 2 );
	}
}
