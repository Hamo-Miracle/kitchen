<?php
/**
 * Trends with Gutenberg
 */
namespace EFormV4\Blocks\Report;
use EFormV4\Blocks\Base;

class Trends extends Base {
	public function get_block_name() {
		return 'trends';
	}

	public function get_block_config()
	{
		return [
			'attributes' => [
				'form_id' => [
					'type' => 'string',
					'default' => '',
				],
				'load' => [
					'type' => 'string',
					'default' => '1',
				],
				'title' => [
					'type' => 'string',
					'default' => __('Trends', 'ipt_fsqm'),
				],
				'types' => [
					'type' => 'object',
					'default' => [
						'mcq' => true,
						'freetype' => true,
						'pinfo' => true,
					],
				],
				'mcq_ids' => [
					'type' => ['array', 'string'],
					'default' => 'all',
				],
				'mcq_config' => [
					'type' => 'object',
					'default' => [],
				],
				'freetype_ids' => [
					'type' => ['array', 'string'],
					'default' => 'all',
				],
				'pinfo_ids' => [
					'type' => ['array', 'string'],
					'default' => 'all',
				],
				'pinfo_config' => [
					'type' => 'object',
					'default' => [],
				],
				'filters' => [
					'type' => 'object',
					'default' => [
						'users' => 'all', // available users
						'urlTracks' => 'all', // available urlTracks
						'mk' => '', // User metakey
						'mv' => '', // User Meta value
						'smin' => '', // Min score
						'smax' => '', // Max score
						'dtmin' => '', // Date min
						'dtmax' => '', // Date max
					],
				],
				'data' => [
					'type' => 'object',
					'default' => [
						'data' => true,
						'others' => true,
						'names' => true,
						'date' => true,
					],
				],
				'appearance' => [
					'type' => 'object',
					'default' => [
						'block' => true,
						'heading' => true,
						'description' => true,
						'header' => true,
						'border' => true,
						'material' => true,
						'print' => false,
					],
				],
			],
			'render_callback' => [ $this, 'render' ],
		];
	}

	public function render( $attributes, $content ) {
		// Make it compatible with the shortcode cb
		$attributes['mcq_config'] = json_encode($attributes['mcq_config']);
		$attributes['pinfo_config'] = json_encode($attributes['pinfo_config']);
		$attributes['filters'] = json_encode($attributes['filters']);
		$attributes['data'] = json_encode($attributes['data']);
		$attributes['appearance'] = json_encode($attributes['appearance']);
		foreach( [ 'mcq', 'freetype', 'pinfo' ] as $key ) {
			if ( $attributes['types'][ $key ] ) {
				if ( 'all' !== $attributes[ "{$key}_ids" ] ) {
					$attributes[ "{$key}_ids" ] = implode( ',', (array) $attributes[ "{$key}_ids" ] );
				}
			} else {
				$attributes[ "{$key}_ids" ] = '';
			}
		}

		return \IPT_EForm_Core_Shortcodes::ipt_fsqm_trends_cb( $attributes );
	}
}
