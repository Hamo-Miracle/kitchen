<?php

class IPT_EForm_Intg_MailWizz extends IPT_EForm_Intg_Base {
	/**
	 * {@inheritDoc}
	 *
	 * @return void
	 */
	public function do_integration() {
		try {
			$config = new MailWizzApi_Config( [
				'apiUrl' => $this->config['url'],
				'publicKey' => $this->config['pub_key'],
				'privateKey' => $this->config['priv_key'],
			] );
			MailWizzApi_Base::setConfig( $config );
			$endpoint = new MailWizzApi_Endpoint_ListSubscribers();
			$response = $endpoint->create( $this->config['list_id'], [
				'EMAIL' => $this->basic_info['email'],
				'FNAME' => $this->basic_info['f_name'],
				'LNAME' => $this->basic_info['l_name'],
				'details'  => [
					'ip_address' => $this->basic_info['ip'],
				],
			] + $this->additional_metadata );
			if (
				! is_object( $response )
				|| ! isset( $response->body )
				|| ! isset( $response->body->toArray()['status'] )
				|| $response->body->toArray()['status'] !== 'success'
			) {
				ipt_error_log( $response->body->toArray() );
			}
		} catch( Exception $e ) {
			ipt_error_log( $e->getMessage() );
		}
	}
}
