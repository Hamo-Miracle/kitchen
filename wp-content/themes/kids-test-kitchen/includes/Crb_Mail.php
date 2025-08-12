<?php

/**
 *
 */
class Crb_Mail {
	function __construct() {

	}

	/**
	 * Wrapper for the default WordPress Email
	 */
	function send_mail( $settings = array() ) {
		$to         = array();
		$recipients = carbon_get_theme_option( 'crb_updates_email_recipients', 'complex' );

		if ( ! empty( $recipients ) ) {
			foreach ( $recipients as $email ) {
				array_push( $to, $email['address'] );
			}
		} else {
			array_push( $to, get_bloginfo('admin_email') );
		}

		$settings = wp_parse_args( $settings, array(
			'to'          => $to,
			'subject'     => html_entity_decode(get_bloginfo('name')),
			'message'     => '',
			'headers'     => array(
				'Content-Type: text/html; charset=UTF-8',
				sprintf( 'From: %s &lt;%s&gt;', get_bloginfo( 'name' ), get_bloginfo( 'admin_email' ) )
			),
			'attachments' => array(),
		) );

		try {
			wp_mail( $settings['to']
			, $settings['subject'], $settings['message'], $settings['headers'], $settings['attachments'] );
		} catch (Exception $e) {
			// Prevent mail errors
		}
	}

	

	/**
	 * Send a daily Mail.
	 * This mail is the daily update for specific users
	 * and has been in place for a long time.
	 */
	function send_email_daily_update( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'after' => $this->get_yesterday(),
		) );

		$message = $this->get_body_daily_update( $args );

		$this->send_mail( array(
			'message' => $message,
			'subject' => 'Daily Update from ' . html_entity_decode(get_bloginfo( 'name' )),
		) );
	}

	/**
	 * Return body for the daily dates update mail
	 */
	function get_body_daily_update( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'after' => '1970-01-01',
		) );

		$locations = $this->get_modified_three( $args['after'] );

		ob_start();

		include( locate_template( 'fragments/emails/daily-update.php' ) );

		$content = ob_get_clean();

		return $content;
	}

	//Specific function for staff mail to get recipients
	function send_staff_mail( $settings = array() ) {
		$to         = array();
		$bcc		= array(); //send staff as bcc
		$query_args = [ 'role__in' => [ 'crb_facilitator', 'crb_assistant', 'Administrator' ], //Update roles to match what Emily wants.
                    	'fields' => ['user_email']
                    ];
		
		array_push( $to, get_bloginfo('admin_email') );
		
		$users = get_users($query_args);
		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {
				if($user->{'user_email'} !== "ktk@daveseldon.com") {
					array_push( $bcc, sprintf( 'Bcc: %s',$user->{'user_email'}) );
				}
			}
		}

		$initHeaders = array(
			'Content-Type: text/html; charset=UTF-8',
			sprintf( 'From: %s &lt;%s&gt;', get_bloginfo( 'name' ), get_bloginfo( 'admin_email' ) )
		);
		
		$headers = array_merge($initHeaders,$bcc);

		$settings = wp_parse_args( $settings, array(
			'to'          => $to,
			'subject'     => html_entity_decode(get_bloginfo('name')),
			'message'     => '',
			'headers'     => $headers,
			'attachments' => array(),
		) );

		try {
			wp_mail( $settings['to']
			, $settings['subject'], $settings['message'], $settings['headers'], $settings['attachments'] );
		} catch (Exception $e) {
			// Prevent mail errors
		}
	}

	/**
	 * Send staff daily Mail
	 * Added 11/20/2023 - Email with latest classes
	 * If no classes updated then no email.
	 */
	function send_staff_daily_update( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'after' => $this->get_yesterday(),
		) );

		$message = $this->get_staff_daily_update( $args );

		if (isset($message) && $message !== '') {
			$this->send_staff_mail( array(
				'message' => $message,
				'subject' => 'New Opportunities from ' . html_entity_decode(get_bloginfo( 'name' )),
			) );
		}
		return $message;
	}

	/**
	 * Return body for the staff update mail
	 */
	function get_staff_daily_update( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'after' => '1970-01-01',
		) );

		$locations = $this->get_modified_three( $args['after'], 'pending' );

		if(count($locations)>0)
		{
			ob_start();

			include( locate_template( 'fragments/emails/daily-update-no-user-info.php' ) );

			$content = ob_get_clean();

			return $content;
		}
		return '';
	}

	

	/**
	 * Return Modified Entries based on modified dates.
	 * If a Location / Class has been modified, no info will be returned.
	 * Only modified dates will trigger showing an entry.
	 */
	function get_modified_three( $after, $post_status = 'any', $fields = 'ids') {
		global $wpdb;

		// Get All classes with date that has been modified
		$modified_dates_classes = $wpdb->get_col( $wpdb->prepare("
			SELECT
				meta.meta_value
			FROM
				$wpdb->posts as post
			INNER JOIN
				$wpdb->postmeta as meta
				ON
				post.ID = meta.post_id
			WHERE
				post.post_type = 'crb_date'
				AND
				post.post_date > %s
				AND
				meta.meta_key = '_crb_date_class'
			GROUP BY meta.meta_value
		", $after ) );

		// Populate $classes with entries like `$class_id => (array) $date_ids`
		$classes = array();
		foreach ( $modified_dates_classes as $class_id ) {
			$class = get_post( $class_id );

			if ( ( get_post_status( $class_id ) != 'publish' ) || ( strtotime( $class->post_date ) < strtotime( $after ) ) ) {
				continue;
			}

			if($post_status !== 'any' && $post_status === get_post_status( $class_id ) )
			{
				continue;
			}



			$class_dates = get_posts( array(
				'post_type'      => 'crb_date',
				'post_status'    =>  $post_status,
				'posts_per_page' => -1,
				'meta_key'       => '_crb_date_class',
				'meta_value'     => $class_id,
				'meta_compare'   => '=',
				'fields'         => $fields,
			) );

			if(count($class_dates) === 0){ //No dates to show so don't.
				continue;
			}

			$classes[$class_id] = $class_dates;
		}

		// Populate $locations with entries like `$location_id => (array) $class_ids` => (array) $date_ids
		$locations = array();
		foreach ( $classes as $class_id => $class_dates ) {
			$location_id = carbon_get_post_meta( $class_id, 'crb_class_location' );
			if ( empty( $location_id ) ) {
				continue;
			}

			$locations[$location_id][$class_id] = $class_dates;
		}

		return $locations;
	}

	/**
	 * Return Yesterday
	 */
	function get_yesterday() {
		return date( 'Y-m-d', time() - DAY_IN_SECONDS );
	}
}
