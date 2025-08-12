<?php
/**
 * Login with Gutenberg
 *
 * @package EFormV4
 * @subpackage Blocks\System
 */

namespace EFormV4\Blocks\System;

use EFormV4\Blocks\Base;


/**
 * Define gutenberg block for Login.
 */
class Login extends Base {
	/**
	 * {@inheritDoc}
	 *
	 * @return string
	 */
	public function get_block_name() {
		return 'login';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return array
	 */
	public function get_block_config() {
		return [
			'attributes' => [
				'theme' => [
					'type' => 'string',
					'default' => 'material-default',
				],
				'style' => [
					'type' => 'string',
					'default' => 'boxy',
				],
				'redir' => [
					'type' => 'string',
					'default' => '',
				],
				'register' => [
					'type' => 'string',
					'default' => '1',
				],
				'regurl' => [
					'type' => 'string',
					'default' => '',
				],
				'forgot' => [
					'type' => 'string',
					'default' => '1',
				],
				'max_width' => [
					'type' => 'string',
					'default' => '600px',
				],
				'content' => [
					'type' => 'string',
					'default' => __( 'Please login to our site', 'ipt_fsqm' ),
				],
			],
			'render_callback' => [ $this, 'render' ],
		];
	}

	public function render( $attributes ) {
		$shortcode = \IPT_EForm_Stat_Shortcodes::init();
		return $shortcode->login_form( $attributes, $attributes['content'] );
	}
}
