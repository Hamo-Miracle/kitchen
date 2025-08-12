<?php

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Class BNFW_Send_To_Any_Email
 */
class BNFW_Send_To_Any_Email {
	/**
	 * Constructor.
	 *
	 * @since 1.0
	 */
	function __construct() {
		$this->hooks();
	}

	/**
	 * Factory method to return the instance of the class.
	 *
	 * Makes sure that only one instance is created.
	 *
	 * @return object Instance of the class
	 */
	public static function factory() {
		static $instance = false;
		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Setup hooks.
	 *
	 * @since 1.0
	 */
	public function hooks() {
		add_filter( 'bnfw_localize_script', array( $this, 'add_tags' ) );
		add_filter( 'bnfw_non_wp_emails', array( $this, 'append_non_wp_emails' ), 10, 2 );
		add_filter( 'bnfw_email_dropdown_placeholder', array( $this, 'change_email_dropdown_placeholder' ) );
		add_filter( 'bnfw_ppo_email_dropdown_placeholder', array( $this, 'change_ppo_email_dropdown_placeholder' ) );
	}

	/**
	 * Filter the localize strings.
	 *
	 * @param array $strings Localized strings.
	 *
	 * @return array Filtered Localized strings.
	 */
	public function add_tags( $strings ) {
		$strings['enableTags'] = true;
                $strings['enabletokenSeparators'] = [','];
		return $strings;
	}

	/**
	 * Append non WordPress user emails.
	 *
	 * @param array $emails List of emails.
	 * @param array $users  List of users.
	 *
	 * @return array List of non wp emails.
	 */
	public function append_non_wp_emails( $emails, $users ) {
		foreach ( $users as $user ) {
			if ( is_email( $user ) ) {
				$emails[] = $user;
			}
		}

		return $emails;
	}

	/**
	 * Change the placeholder of email dropdown.
	 *
	 * @param string $placeholder Placeholder.
	 *
	 * @return string Modified string.
	 */
	public function change_email_dropdown_placeholder( $placeholder ) {
		return __( 'Select User Roles / Users or type an email address', 'bnfw' );
	}

	/**
	 * Change the placeholder of email dropdown used in PPO addon.
	 *
	 * @param string $placeholder Placeholder.
	 *
	 * @return string Modified string.
	 */
	public function change_ppo_email_dropdown_placeholder( $placeholder ) {
		return __( 'Override User Roles / Users or type an email address', 'bnfw' );
	}
}
