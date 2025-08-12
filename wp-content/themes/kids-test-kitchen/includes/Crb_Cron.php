<?php

/**
 * Automated Emails
 */
class Crb_Cron {
	// Init crons
	function __construct() {
		// Init Hooks
		add_action( 'send_email_daily_update', array( $this, 'send_email_daily_update' ) );

		// Automatic Daily Email
		if ( ! wp_next_scheduled( 'send_email_daily_update' ) ) {
			wp_schedule_event( time(), 'daily', 'send_email_daily_update' );
		}

		if ( crb_request_param( 'trigger-send-email' ) ) {
			$this->send_email_daily_update();
		}
	}

	/**
	 * Send Daily Update
	 */
	function send_email_daily_update() {
		$Crb_Mail = new Crb_Mail();
		$Crb_Mail->send_email_daily_update();
		$Crb_Mail->send_staff_daily_update(); //Added second email
	}
}

new Crb_Cron();
