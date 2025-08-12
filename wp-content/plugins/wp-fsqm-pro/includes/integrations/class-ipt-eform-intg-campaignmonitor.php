<?php

class IPT_EForm_Intg_CampaignMonitor extends IPT_EForm_Intg_Base {
	/**
	 * {@inheritDoc}
	 *
	 * @return void
	 */
	public function do_integration() {
		try {
			$cm_auth = [
				'api_key' => $this->config['api']
			];
			$cm_wrap = new CS_REST_Subscribers(
				$this->config['list_id'],
				$cm_auth
			);
			$cm_customfields = [];
			foreach ( $this->additional_metadata as $admkey => $admval ) {
				$cm_customfields[] = [
					'Key' => $admkey,
					'Value' => $admval,
				];
			}
			$cm_subscriber = array(
				'EmailAddress' => $this->basic_info['email'],
				'Name' => $this->basic_info['full_name'],
				'Resubscribe' => true,
				'CustomFields' => $cm_customfields,
			);
			$cm_result = $cm_wrap->add( $cm_subscriber );
			if (
				! is_object( $cm_result )
				|| ! isset( $cm_result->http_status_code )
				|| (
					$cm_result->http_status_code !== 201
					&& $cm_result->http_status_code !== 200
				)
			) {
				ipt_error_log( $cm_result );
			}
		} catch ( Exception $e ) {
			ipt_error_log( $e->getMessage() );
		}
	}
}
