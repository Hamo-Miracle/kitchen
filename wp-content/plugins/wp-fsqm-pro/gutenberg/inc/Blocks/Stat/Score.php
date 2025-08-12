<?php
/**
 * Score form and user statistics with gutenberg
 *
 * @package EFormV4
 * @subpackage Blocks\Stat
 */

namespace EFormV4\Blocks\Stat;

use EFormV4\Blocks\Base;


/**
 * Class for managing following gtb block.
 *
 * [ipt_eform_formscorestat] + [ipt_eform_userscorestat]
 */
class Score extends Base {
	/**
	 * {@inheritDoc}
	 *
	 * @return string
	 */
	public function get_block_name() {
		return 'eform-stat-score';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return array
	 */
	public function get_block_config() {
		return [
			'attributes' => [
				'filter_for_user' => [
					'type' => 'boolean',
					'default' => false,
				],
				'user_id' => [
					'type' => 'string',
					'default' => 'current',
				],
				'show_login' => [
					'type' => 'string',
					'default' => '1',
				],
				'login_msg' => [
					'type' => 'string',
					'default' => __( 'Please login to view statistics', 'ipt_fsqm' ),
				],
				'theme' => [
					'type' => 'string',
					'default' => 'material-default',
				],
				'form_ids' => [
					'type' => 'string',
					'default' => 'all',
				],
				'label' => [
					'type' => 'string',
					'default' => __( 'From %1$d%% to %2$d%% ', 'ipt_fsqm' ),
				],
				'days' => [
					'type' => 'string',
					'default' => '',
				],
				'type' => [
					'type' => 'string',
					'default' => 'pie', // pie or doughnut
				],
				'height' => [
					'type' => 'string',
					'default' => '400',
				],
				'width' => [
					'type' => 'string',
					'default' => '900',
				],
			],
			'render_callback' => [ $this, 'render' ],
		];
	}

	public function render( $attributes ) {
		// needs much logic
	}
}
