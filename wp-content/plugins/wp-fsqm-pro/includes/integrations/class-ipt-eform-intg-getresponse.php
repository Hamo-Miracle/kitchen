<?php

class IPT_EForm_Intg_GetResponse extends IPT_EForm_Intg_Base {
	/**
	 * {@inheritDoc}
	 *
	 * @return void
	 */
	public function do_integration() {
		try {
			$gr_client = new EForm_GetResponse_API(
				$this->config['api']
			);
			$custom_field_values = [];
			// we have taken field name from admin, but we need to convert them
			// to fieldId which is abstracted away in getresponse
			// so we query them and match, and if match is found, then we add
			// them to our custom_field_values
			foreach ( $this->additional_metadata as $amkey => $amval ) {
				$gr_customfields = $gr_client->getCustomFields( [
					'query' => [
						'name' => $amkey,
					],
				] );
				foreach ( (array) $gr_customfields as $gr_cf ) {
					if ( $gr_cf->name ===  $amkey ) {
						$custom_field_values[] = [
							'customFieldId' => $gr_cf->customFieldId,
							'value' => [ $amval ],
						];
						break; // break the foreach loop
					}
				}
			}
			$gr_contact = [
				'name'              => $this->basic_info['full_name'],
				'email'             => $this->basic_info['email'],
				'dayOfCycle'        => 0,
				'campaign'          => [
					'campaignId' => $this->config['campaign_id'],
				],
				'ipAddress'         => $this->basic_info['ip'],
				'customFieldValues' => $custom_field_values,
			];
			$gr_result = $gr_client->addContact( $gr_contact );
			if (
				$gr_result
				&& is_object( $gr_result )
				&& isset ( $gr_result->httpStatus )
			) {
				ipt_error_log( $gr_result );
			}
		} catch ( Exception $e ) {
			ipt_error_log( $e->getMessage() );
		}
	}
}
