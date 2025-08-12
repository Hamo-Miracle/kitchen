<?php
/**
 * Daywise, overall and score based form and user statistics with gutenberg
 *
 * @package EFormV4
 * @subpackage Blocks\Report
 */

namespace EFormV4\Blocks\Report;

use EFormV4\Blocks\Base;


/**
 * Class for managing eform-stat gtb block.
 *
 * all stat shortcodes.
 */
class Stat extends Base {
	/**
	 * {@inheritDoc}
	 *
	 * @return string
	 */
	public function get_block_name() {
		return 'eform-stat';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return array
	 */
	public function get_block_config() {
		return [
			'attributes' => [
				// daywise, overall, score
				'stat_type' => [
					'type' => 'string',
					'default' => 'daywise',
				],
				// daywise, overall, score
				'filter_for_user' => [
					'type' => 'boolean',
					'default' => false,
				],
				// daywise, overall, score
				'user_id' => [
					'type' => 'string',
					'default' => 'current',
				],
				// daywise, overall, score
				'show_login' => [
					'type' => 'boolean',
					'default' => true,
				],
				// daywise, overall, score
				'login_msg' => [
					'type' => 'string',
					'default' => __( 'Please login to view statistics', 'ipt_fsqm' ),
				],
				// daywise, overall, score
				'theme' => [
					'type' => 'string',
					'default' => 'material-default',
				],
				// daywise, overall, score
				'form_ids' => [
					'type' => 'string',
					'default' => 'all',
				],
				// score
				'label' => [
					'type' => 'string',
					'default' => __( 'From %1$d%% to %2$d%% ', 'ipt_fsqm' ),
				],
				// daywise, overall, score
				'days' => [
					'type' => 'string',
					'default' => '30',
				],
				// daywise
				'totalline' => [
					'type' => 'string',
					'default' => __( 'Total Submissions', 'ipt_fsqm' ),
				],
				// daywise
				'xlabel' => [
					'type' => 'string',
					'default' => __( 'Date', 'ipt_fsqm' ),
				],
				// daywise
				'ylabel' => [
					'type' => 'string',
					'default' => __( 'Submissions', 'ipt_fsqm' ),
				],
				// overall, score
				'type' => [
					'type' => 'string',
					'default' => 'pie', // pie or doughnut
				],
				// daywise, overall, score
				'height' => [
					'type' => 'string',
					'default' => '400',
				],
				// daywise, overall, score
				'width' => [
					'type' => 'string',
					'default' => '900',
				],
				// daywise, overall
				'max' => [
					'type' => 'string',
					'default' => '0',
				],
				// daywise, overall
				'others' => [
					'type' => 'string',
					'default' => __( 'Others', 'ipt_fsqm' ),
				],
			],
			'render_callback' => [ $this, 'render' ],
		];
	}

	public function render( $attributes ) {
		// needs much logic
		$stat_type = $attributes['stat_type'];
		$filter_by_user = $attributes['filter_for_user'];

		$shortcode_instance = \IPT_EForm_Stat_Shortcodes::init();

		// Some compatibility
		$attributes['show_login'] = $attributes['show_login'] ? '1' : '0';

		// call the appropirate shortcode callback
		if ( 'daywise' === $stat_type ) {
			if ( $filter_by_user ) {
				return $shortcode_instance->user_stat_submissions( $attributes );
			} else {
				return $shortcode_instance->submissions_stat( $attributes );
			}
		} elseif ( 'overall' === $stat_type ) {
			if ( $filter_by_user ) {
				return $shortcode_instance->user_sub( $attributes );
			} else {
				return $shortcode_instance->overall_submissions( $attributes );
			}
		} elseif ( 'score' === $stat_type ) {
			if ( $filter_by_user ) {
				return $shortcode_instance->user_score_stat( $attributes );
			} else {
				return $shortcode_instance->form_score( $attributes );
			}
		}
	}
}
