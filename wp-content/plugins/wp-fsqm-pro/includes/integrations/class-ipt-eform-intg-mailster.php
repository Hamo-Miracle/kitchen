<?php

class IPT_EForm_Intg_Mailster extends IPT_EForm_Intg_Base {
	/**
	 * {@inheritDoc}
	 *
	 * @return void
	 */
	public function do_integration() {
		try {
			$mm_entry = array(
				'firstname' => $this->basic_info['f_name'],
				'lastname' => $this->basic_info['l_name'],
				'email' => $this->basic_info['email'],
				'referer' => esc_url( $_SERVER['HTTP_REFERER'] ),
			);

			// Add the subscriber
			$mm_s_id = mailster( 'subscribers' )->add(
				$this->additional_metadata + $mm_entry,
				$this->config['overwrite']
			);

			// Now add to list if needed
			if ( ! is_wp_error( $mm_s_id ) && ! empty( $this->config['list_ids'] ) ) {
				$mm_list_ids = (array) $this->config['list_ids'];
				mailster( 'subscribers' )->assign_lists( $mm_s_id, $mm_list_ids, false );
			}
		} catch ( Exception $e ) {
			ipt_error_log( $e->getMessage() );
		}
	}
}
