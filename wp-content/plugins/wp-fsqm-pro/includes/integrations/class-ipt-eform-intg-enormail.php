<?php

class IPT_EForm_Intg_Enormail extends IPT_EForm_Intg_Base {
	/**
	 * {@inheritDoc}
	 *
	 * @return void
	 */
	public function do_integration() {
		try {
			$em = new Enormail\ApiClient(
				$this->config['api'],
				'json'
			);
			$result = $em->contacts->add(
				$this->config['list_id'],
				$this->basic_info['full_name'],
				$this->basic_info['email'],
				$this->additional_metadata
			);
			$response = json_decode( $result );
			if (
				! is_object( $response )
				|| ! isset( $response->state )
				|| $response->state !== 'active'
			) {
				ipt_error_log( $result );
			}
		} catch( Exception $e ) {
			ipt_error_log( $e->getMessage() );
		}
	}
}
