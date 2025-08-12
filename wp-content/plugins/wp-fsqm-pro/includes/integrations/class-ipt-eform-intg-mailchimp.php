<?php

class IPT_EForm_Intg_Mailchimp extends IPT_EForm_Intg_Base {
	/**
	 * {@inheritDoc}
	 *
	 * @return void
	 */
	public function do_integration() {
		$mc_intg = new DrewM\MailChimp\MailChimp( $this->config['api'] );
		$mc_merge_vars = [];
		$mc_merge_vars['FNAME'] = $this->basic_info['f_name'];

		if ( $this->basic_info['l_name'] != '' ) {
			$mc_merge_vars['LNAME'] = $this->basic_info['l_name'];
		}
		try {
			$status = $this->config['double_optin'] ? 'pending' : 'subscribed';
			$mc_result = $mc_intg->post( "lists/{$this->config['list_id']}/members/", array(
				'email_address' => $this->basic_info['email'],
				'status' => $status,
				'merge_fields' => $mc_merge_vars + $this->additional_metadata,
			) );
			if (
				! $mc_result
				|| ! isset( $mc_result['status'] )
				|| $mc_result['status'] !== $status
			) {
				ipt_error_log( $mc_result );
			}
		} catch(Exception $e ) {
			ipt_error_log( $e );
		}
	}
}
