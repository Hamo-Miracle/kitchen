<?php

class IPT_EForm_Intg_Convertkit extends IPT_EForm_Intg_Base {
	/**
	 * {@inheritDoc}
	 *
	 * @return void
	 */
	public function do_integration() {
		try {
			$tags_cv_def = wp_parse_id_list( $this->config['tags'] );
			$cvsubscriber = [
				'email' => $this->basic_info['email'],
				'first_name' => $this->basic_info['f_name'],
				'fields' => [
					'last_name' => $this->basic_info['l_name'],
				] + $this->additional_metadata,
				// 'tags' => $tags_cv_def,
			];
			// return;
			// Add to form
			if ( '' !== $this->config['form_id'] ) {
				(new \calderawp\convertKit\forms( $this->config['api_key'] ) )->add(
					$this->config['form_id'],
					$cvsubscriber
				);
			}
			// Add to sequence
			if ( '' !== $this->config['sequence_id'] ) {
				(new \calderawp\convertKit\sequences( $this->config['api_key'] ) )->add(
					$this->config['sequence_id'],
					$cvsubscriber
				);
			}
			// Tag them if needed
			if ( ! empty( $tags_cv_def ) ) {
				$cv_tag_client = new \calderawp\convertKit\tags( $this->config['api_key'] );
				foreach ( $tags_cv_def as $tag_cv_def ) {
					$cv_tag_client->subscribe( $tag_cv_def, $cvsubscriber['email'] );
				}
			}
		} catch( Exception $e ) {
			ipt_error_log( $e->getMessage() );
		}
	}
}
