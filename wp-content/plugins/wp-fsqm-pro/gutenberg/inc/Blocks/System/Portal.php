<?php
/**
 * Portal with Gutenberg
 *
 * @package EForm
 * @subpackage Blocks\System
 */

namespace EFormV4\Blocks\System;

use EFormV4\Blocks\Base;


/**
 * Class for registering User Portal gutenberg block.
 */
class Portal extends Base {
	/**
	 * {@inheritDoc}
	 *
	 * @return string
	 */
	public function get_block_name() {
		return 'user-portal';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return array
	 */
	public function get_block_config() {
		return [
			'attributes' => [
				'login' => [
					'type' => 'string',
					'default' => __( 'You need to login in order to view your submissions', 'ipt_fsqm' ),
				],
				'title' => [
					'type' => 'string',
					'default' => __( 'eForm User Portal', 'ipt_fsqm' ),
				],
				'nosubmission' => [
					'type' => 'string',
					'default' => __( 'No submissions yet.', 'ipt_fsqm' ),
				],
				'formlabel' => [
					'type' => 'string',
					'default' => __( 'Form', 'ipt_fsqm' ),
				],
				'categorylabel' => [
					'type' => 'string',
					'default' => __( 'Category', 'ipt_fsqm' ),
				],
				'datelabel' => [
					'type' => 'string',
					'default' => __( 'Date', 'ipt_fsqm' ),
				],
				'show_register' => [
					'type' => 'string',
					'default' => '1',
				],
				'show_forgot' => [
					'type' => 'string',
					'default' => '1',
				],
				'showcategory' => [
					'type' => 'string',
					'default' => '0',
				],
				'showscore' => [
					'type' => 'string',
					'default' => '1',
				],
				'scorelabel' => [
					'type' => 'string',
					'default' => __( 'Score', 'ipt_fsqm' ),
				],
				'mscorelabel' => [
					'type' => 'string',
					'default' => __( 'Max', 'ipt_fsqm' ),
				],
				'pscorelabel' => [
					'type' => 'string',
					'default' => __( '%-age', 'ipt_fsqm' ),
				],
				'showremarks' => [
					'type' => 'string',
					'default' => '0',
				],
				'remarkslabel' => [
					'type' => 'string',
					'default' => __( 'Remarks', 'ipt_fsqm' ),
				],
				'linklabel' => [
					'type' => 'string',
					'default' => __( 'View', 'ipt_fsqm' ),
				],
				'actionlabel' => [
					'type' => 'string',
					'default' => __( 'Action', 'ipt_fsqm' ),
				],
				'editlabel' => [
					'type' => 'string',
					'default' => __( 'Edit', 'ipt_fsqm' ),
				],
				'avatar' => [
					'type' => 'string',
					'default' => '96',
				],
				'theme' => [
					'type' => 'string',
					'default' => 'material-default',
				],
				'filters' => [
					'type' => 'string',
					'default' => '0',
				],
				'logout_r' => [
					'type' => 'string',
					'default' => '',
				],
				'content' => [
					'type' => 'string',
					'default' => __( 'Welcome %NAME%. Below is the list of all submissions you have made.', 'ipt_fsqm' ),
				],
			],
			'render_callback' => [ $this, 'render' ],
		];
	}

	public function render( $attributes ) {
		$content = $attributes['content'];
		return \IPT_EForm_Core_Shortcodes::ipt_fsqm_utrack_cb( $attributes, $content );
	}
}
