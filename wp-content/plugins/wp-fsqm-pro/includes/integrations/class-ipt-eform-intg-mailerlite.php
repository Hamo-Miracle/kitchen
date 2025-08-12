<?php

class IPT_EForm_Intg_Mailerlite extends IPT_EForm_Intg_Base {
	/**
	 * {@inheritDoc}
	 *
	 * @return void
	 */
	public function do_integration() {
		try {
			$ml = new MailerLiteApi\MailerLite( $this->config['api'] );
			$result = $ml->groups()->addSubscriber( $this->config['group_id'], [
				'email' => $this->basic_info['email'],
				'name' => $this->basic_info['f_name'],
				'fields' => [
					'last_name' => $this->basic_info['l_name'],
					'phone' => $this->basic_info['phone'],
				] + $this->additional_metadata,
			] );
			if (
				is_object( $result )
				&& isset( $result->error )
			) {
				ipt_error_log( $result );
			}
		} catch( Exception $e ) {
			ipt_error_log( $e->getMessage() );
		}
	}
}
