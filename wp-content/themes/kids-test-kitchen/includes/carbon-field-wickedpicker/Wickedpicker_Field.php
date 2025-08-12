<?php

namespace Carbon_Fields\Field;

class Wickedpicker_Field extends Field {
	/**
	 * Changes the options array structure. This is needed to keep the array items order when it is JSON encoded.
	 * Will also work with a callable that returns an array.
	 *
	 * @param array|callable $options
	 * @return array
	 */
	public function parse_options( $options ) {
		$parsed = array();

		if ( is_callable( $options ) ) {
			$options = call_user_func( $options );
		}

		foreach ( $options as $key => $value ) {
			$parsed[] = array(
				'name' => $value,
				'value' => $key,
			);
		}

		return $parsed;
	}

	/**
	 * to_json()
	 *
	 * You can use this method to modify the field properties that are added to the JSON object.
	 * The JSON object is used by the Backbone Model and the Underscore template.
	 *
	 * @param bool $load  Should the value be loaded from the database or use the value from the current instance.
	 * @return array
	 */
	public function to_json( $load ) {
		$field_data = parent::to_json( $load ); // do not delete

		$field_data = $field_data + array(
			'options' => array(
				'hours' => $this->parse_options( array(
					'' => ' --- Hours --- ',
					'12' => '12',
					'01' => '01',
					'02' => '02',
					'03' => '03',
					'04' => '04',
					'05' => '05',
					'06' => '06',
					'07' => '07',
					'08' => '08',
					'09' => '09',
					'10' => '10',
					'11' => '11',
				) ),
				'minutes' => $this->parse_options( array(
					'' => ' --- Minutes --- ',
					'00' => '00',
					'05' => '05',
					'10' => '10',
					'15' => '15',
					'20' => '20',
					'25' => '25',
					'30' => '30',
					'35' => '35',
					'40' => '40',
					'45' => '45',
					'50' => '50',
					'55' => '55',
				) ),
				'mer' => $this->parse_options( array(
					'' => ' --- AM/PM --- ',
					'AM' => 'AM',
					'PM' => 'PM',
				) ),
			),
		);

		return $field_data;
	}

	/**
	 * template()
	 *
	 * Prints the main Underscore template
	 **/
	public function template() {
		?>

		<select class="hours">
			<# _.each(options.hours, function(option) { #>
				<option value="{{ option.value }}" {{{ option.value == hours ? 'selected="selected"' : '' }}}>
					{{{ option.name }}}
				</option>
			<# }) #>
		</select>

		<select class="minutes">
			<# _.each(options.minutes, function(option) { #>
				<option value="{{ option.value }}" {{{ option.value == minutes ? 'selected="selected"' : '' }}}>
					{{{ option.name }}}
				</option>
			<# }) #>
		</select>

		<!-- Ante meridiem -->
		<select class="mer">
			<# _.each(options.mer, function(option) { #>
				<option value="{{ option.value }}" {{{ option.value == mer ? 'selected="selected"' : '' }}}>
					{{{ option.name }}}
				</option>
			<# }) #>
		</select>

		<input id="{{{ id }}}" type="hidden" name="{{{ name }}}" value="{{ value }}" class="crb-timepicki-unformatted-value regular-text" />

		<?php
	}

	/**
	 * admin_enqueue_scripts()
	 *
	 * This method is called in the admin_enqueue_scripts action. It is called once per field type.
	 * Use this method to enqueue CSS + JavaScript files.
	 *
	 */
	public static function admin_enqueue_scripts() {
		# Enqueue JS
		crb_enqueue_script( 'carbon-field-wickedpicker', CRB_WICKEDPICKER_URL . '/js/field.js', array( 'carbon-fields', 'jquery' ) );

		# Enqueue CSS
		crb_enqueue_style( 'carbon-field-wickedpicker', CRB_WICKEDPICKER_URL . '/css/field.css' );
	}
}
