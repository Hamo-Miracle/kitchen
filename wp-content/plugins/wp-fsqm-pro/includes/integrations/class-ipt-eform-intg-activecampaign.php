<?php

class IPT_EForm_Intg_ActiveCampaign extends IPT_EForm_Intg_Base {
	/**
	 * {@inheritDoc}
	 *
	 * @return void
	 */
	public function do_integration() {
		try {
			$acobj = new ActiveCampaign(
				$this->config['url'],
				$this->config['api']
			);
			$list_id = $this->config['list_id'];
			$contact = [
				'first_name'         => $this->basic_info['f_name'],
				'last_name'          => $this->basic_info['l_name'],
				'email'              => $this->basic_info['email'],
				'phone'              => $this->basic_info['phone'],
				"p[{$list_id}]"      => $list_id,
				"status[{$list_id}]" => 1, // "Active" status
			];

			// add custom fields
			foreach ( $this->additional_metadata as $ptag => $val ) {
				$contact["field[{$ptag},0]"] = $val;
			}

			$contact_sync = $acobj->api( "contact/sync", $contact );

			if (
				! is_object( $contact_sync )
				|| (
					is_object( $contact_sync )
					&& $contact_sync->success !== 1
				)
			) {
				ipt_error_log( $contact_sync );
			}
		} catch ( Exception $e ) {
			ipt_error_log( $e->getMessage() );
		}
	}
}
