<?php
/**
 * Embed Form with Gutenberg
 */

namespace EFormV4\Blocks\Form;
use EFormV4\Blocks\Base;

/**
 * Gutenberg block for embedding forms.
 */
class Embed extends Base {
	public function get_block_name() {
		return 'embed-form';
	}

	public function get_block_config() {
		return [
			'attributes' => [
				'formId' => [
					'type' => 'string',
					'default' => '',
				],
				'iframe' => [
					'type' => 'string',
					'default' => '0',
				],
			],
			'render_callback' => [ $this, 'render' ],
		];
	}

	public function render( $attributes, $content ) {
		return \IPT_EForm_Core_Shortcodes::ipt_fsqm_form_cb([
			'id' => $attributes['formId'],
			'iframe' => isset( $attributes['iframe'] )
				? $attributes['iframe']
				: '0'
		]);
	}
}
