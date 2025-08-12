<?php
/**
 * Popup Form with Gutenberg
 */

namespace EFormV4\Blocks\Form;
use EFormV4\Blocks\Base;

class Popup extends Base {
	public function get_block_name() {
		return 'popup-form';
	}

	public function get_block_config() {
		return [
			'render_callback' => [ $this, 'render' ],
			'attributes' => [
				'form_id' => [
					'type' => 'string',
					'default' => '',
				],
				'pos' => [
					'type' => 'string',
					'default' => 'r',
				],
				'style' => [
					'type' => 'string',
					'default' => 'rect',
				],
				'header' => [
					'type' => 'string',
					'default' => '%FORM%',
				],
				'subtitle' => [
					'type' => 'string',
					'default' => '',
				],
				'icon' => [
					'type' => 'string',
					'default' => 'fa fa-file-text',
				],
				'width' => [
					'type' => 'string',
					'default' => '600',
				],
				'color' => [
					'type' => 'string',
					'default' => '#ffffff',
				],
				'bgcolor' => [
					'type' => 'string',
					'default' => '#3c609e',
				],
				'label' => [
					'type' => 'string',
					'default' => __('Sample Form', 'ipt_fsqm'),
				],
			]
		];
	}

	public function render( $attributes ) {
		$atts = $attributes;
		$content = $attributes['label'];
		return \IPT_EForm_Core_Shortcodes::ipt_fsqm_popup_cb($atts, $content);
	}
}
