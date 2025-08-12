<?php

/**
 * Modify User Registration Form
 */
class Crb_User_Register {
	function __construct() {
		$this->fields = array(
			'first_name' => __( 'First Name', 'crb' ),
			'last_name' => __( 'Last Name', 'crb' ),
			'crb_address' => __( 'Address', 'crb' ),
			'crb_city' => __( 'City', 'crb' ),
			'crb_state' => __( 'State', 'crb' ),
			'crb_zip' => __( 'Zip', 'crb' ),
			'crb_phone' => __( 'Phone', 'crb' ),
			'crb_company_name' => __( 'Company Name', 'crb' ),
		);

		add_action( 'register_form', array( $this, 'register_form' ) );
		add_filter( 'registration_errors', array( $this, 'registration_errors' ), 10, 3 );
		add_action( 'user_register', array( $this, 'user_register' ), 10, 1 );
		add_filter( 'insert_user_meta', array( $this, 'insert_user_meta' ), 10, 3 );
	}

	/**
	 * Display Extra Fields
	 */
	function register_form() {
		foreach ( $this->fields as $slug => $name ) :
			$value = ( ! empty( $_POST[$slug] ) ) ? trim( $_POST[$slug] ) : '';
			?>
			<p>
				<label for="<?php echo $slug; ?>">
					<?php echo $name; ?>

					<br />

					<?php if ( $slug === 'crb_address' ): ?>
						<textarea
							name="<?php echo $slug; ?>"
							id="<?php echo $slug; ?>"
							class="input"
							size="25"
							rows="2"
							required
						 ><?php echo esc_attr( wp_unslash( $value ) ); ?></textarea>
					<?php else: ?>
						<input
							type="text"
							name="<?php echo $slug; ?>"
							id="<?php echo $slug; ?>"
							class="input"
							value="<?php echo esc_attr( wp_unslash( $value ) ); ?>"
							size="25"
							rows="2"
							required
						 />
					<?php endif; ?>
				</label>
			</p>
			<?php
		endforeach;
	}

	/**
	 * Validation, all fields required
	 */
	function registration_errors( $errors, $sanitized_user_login, $user_email ) {
		foreach ( $this->fields as $slug => $name ) {
			if ( empty( $_POST[$slug] ) || empty( trim( $_POST[$slug] ) ) ) {
				$errors->add( $slug . '_error', sprintf( __( '<strong>ERROR</strong>: Please type your %s.', 'crb' ), $name ) );
			}
		}

		return $errors;
	}

	/**
	 * Save the post data into User Meta
	 */
	function user_register( $user_id ) {
		foreach ( $this->fields as $slug => $name ) {
			if ( ! empty( $_POST[$slug] ) ) {
				$value = trim( $_POST[$slug] );

				if ( in_array( $slug, array( 'first_name', 'last_name' ) ) ) {
					wp_update_user( array(
						'ID' => $user_id,
						$slug => $value,
					) );
				} else {
					update_user_meta( $user_id, '_' . $slug, $value );
				}
			}
		}
	}

	/**
	 * Hook here to force the Default Role to a "KTK Session Admin"
	 * This is just before getting the "default_role" option.
	 */
	function insert_user_meta( $meta, $user, $update ) {
		update_option( 'default_role', 'crb_session_admin' );

		return $meta;
	}
}

new Crb_User_Register();
