<?php
/**
 * Leaderboard with Gutenberg
 *
 * @package EFormV4
 * @subpackage Blocks\Report
 */

namespace EFormV4\Blocks\Report;

use EFormV4\Blocks\Base;


/**
 * Define gutenberg block for Leaderboard.
 */
class Leaderboard extends Base {
	/**
	 * {@inheritDoc}
	 *
	 * @return string
	 */
	public function get_block_name() {
		return 'form-leaderboard';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return array
	 */
	public function get_block_config() {
		return [
			'attributes' => [
				'formId'     => [
					'type' => 'string',
					'default' => '',
				],
				// Appearances
				'rank' => [
					'type' => 'boolean',
					'default' => true,
				],
				'avatar' => [
					'type' => 'boolean',
					'default' => true,
				],
				'avatar_size' => [
					'type' => 'boolean',
					'default' => '64',
				],
				'name' => [
					'type' => 'boolean',
					'default' => true,
				],
				'date' => [
					'type' => 'boolean',
					'default' => true,
				],
				'score' => [
					'type' => 'boolean',
					'default' => true,
				],
				'max_score' => [
					'type' => 'boolean',
					'default' => true,
				],
				'percentage' => [
					'type' => 'boolean',
					'default' => true,
				],
				'comment' => [
					'type' => 'boolean',
					'default' => true,
				],
				'heading' => [
					'type' => 'boolean',
					'default' => true,
				],
				'image' => [
					'type' => 'boolean',
					'default' => true,
				],
				'meta' => [
					'type' => 'boolean',
					'default' => true,
				],
				'time' => [
					'type' => 'boolean',
					'default' => true,
				],
				// Labels
				'lname' => [
					'type' => 'string',
					'default' => __( 'Name', 'ipt_fsqm' ),
				],
				'ldate' => [
					'type' => 'string',
					'default' => __( 'Date', 'ipt_fsqm' ),
				],
				'lscore' => [
					'type' => 'string',
					'default' => __( 'Score', 'ipt_fsqm' ),
				],
				'lmax_score' => [
					'type' => 'string',
					'default' => __( 'Out of', 'ipt_fsqm' ),
				],
				'lpercentage' => [
					'type' => 'string',
					'default' => __( 'Percentage', 'ipt_fsqm' ),
				],
				'lcomment' => [
					'type' => 'string',
					'default' => __( 'Remarks', 'ipt_fsqm' ),
				],
				'lrank' => [
					'type' => 'string',
					'default' => __( 'Rank', 'ipt_fsqm' ),
				],
				'ltime' => [
					'type' => 'string',
					'default' => __( 'Time', 'ipt_fsqm' ),
				],
				// Description
				'content' => [
					'type' => 'string',
					'default' => '',
				],
			],
			'render_callback' => [ $this, 'render' ],
		];
	}

	public function render( $attributes ) {
		$shortcode_atts = [
			'form_id' => $attributes['formId'],
			'appearance' => \json_encode( [
				'rank' => $attributes['rank'],
				'avatar' => $attributes['avatar'],
				'avatar_size' => $attributes['avatar_size'],
				'name' => $attributes['name'],
				'date' => $attributes['date'],
				'score' => $attributes['score'],
				'max_score' => $attributes['max_score'],
				'percentage' => $attributes['percentage'],
				'comment' => $attributes['comment'],
				'heading' => $attributes['heading'],
				'image' => $attributes['image'],
				'meta' => $attributes['meta'],
				'time' => $attributes['time'],
			] ),
			'lname' => $attributes['lname'],
			'ldate' => $attributes['ldate'],
			'lscore' => $attributes['lscore'],
			'lmax_score' => $attributes['lmax_score'],
			'lpercentage' => $attributes['lpercentage'],
			'lcomment' => $attributes['lcomment'],
			'lrank' => $attributes['lrank'],
			'ltime' => $attributes['ltime'],
		];
		return \IPT_EForm_LeaderBoard::shortcode_leaderboard_form_cb( $shortcode_atts, $attributes['content'] );
	}
}
