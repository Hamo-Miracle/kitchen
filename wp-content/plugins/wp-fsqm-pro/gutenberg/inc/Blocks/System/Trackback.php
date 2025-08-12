<?php
/**
 * Trackback with Gutenberg
 *
 * @package EFormV4
 * @subpackage Blocks\System
 */

namespace EFormV4\Blocks\System;

use EFormV4\Blocks\Base;


/**
 * Define gutenberg block for Trackback.
 */
class Trackback extends Base {
	/**
	 * {@inheritDoc}
	 *
	 * @return string
	 */
	public function get_block_name() {
		return 'trackback';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return array
	 */
	public function get_block_config() {
		return [
			'attributes' => [
				'label' => [
					'type' => 'string',
					'default' => __( 'Track Code:', 'ipt_fsqm' ),
				],
				'submit' => [
					'type' => 'string',
					'default' => __( 'Submit', 'ipt_fsqm' ),
				],
			],
			'render_callback' => [ $this, 'render' ],
		];
	}

	public function render( $attributes ) {
		return \IPT_EForm_Core_Shortcodes::ipt_fsqm_track_cb( $attributes );
	}
}
