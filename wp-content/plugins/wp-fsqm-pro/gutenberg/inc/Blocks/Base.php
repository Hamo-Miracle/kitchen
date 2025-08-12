<?php
/**
 * Gutenberg block system basic architecture
 *
 * @package EForm
 * @subpackage Blocks
 */
namespace EFormV4\Blocks;

/**
 * Abstract base class for all gutenberg blocks.
 */
abstract class Base {
	/**
	 * Get block name
	 *
	 * @return string
	 */
	abstract public function get_block_name();

	/**
	 * Get block config.
	 *
	 * This is passed directly to register_block_type
	 *
	 * @return array Associative array of block config.
	 */
	abstract public function get_block_config();

	/**
	 * Register the block to WordPress Backend
	 *
	 * @return void
	 */
	public function register() {
		$block_config = array_merge( $this->get_block_config(), [
			'editor_script' => 'eform-gutenberg-blocks',
		] );
		\register_block_type( 'eformv4/' . $this->get_block_name(), $block_config );
	}
}
