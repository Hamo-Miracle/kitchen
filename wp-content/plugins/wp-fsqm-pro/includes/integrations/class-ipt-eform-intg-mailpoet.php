<?php

class IPT_EForm_Intg_MailPoet extends IPT_EForm_Intg_Base {
	/**
	 * {@inheritDoc}
	 *
	 * @return void
	 */
	public function do_integration() {
		$mp3_subscriber_data = [
			'email' => $this->basic_info['email'],
			'first_name' => $this->basic_info['f_name'],
			'last_name' => $this->basic_info['l_name'],
		] + $this->additional_metadata;

		$mp3_lists = array_map(
			'intval',
			$this->config['list_ids']
		);
		$mp3_options = [
			'send_confirmation_email' => (bool) $this->config['confirmation'], // default: true
			'schedule_welcome_email' => (bool) $this->config['welcome'], // default: true
		];
		try {
			\MailPoet\API\API::MP( 'v1' )
				->addSubscriber(
					$mp3_subscriber_data,
					$mp3_lists,
					$mp3_options
				);
		} catch ( Exception $e ) {
			ipt_error_log( $e->getMessage() );
		}
	}
}
