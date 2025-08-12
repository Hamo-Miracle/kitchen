<?php

namespace Carbon_Fields\Field;

class Select_Recipe_Field extends Select_Field {
	/**
	 * Returns an array that holds the field data, suitable for JSON representation.
	 * This data will be available in the Underscore template and the Backbone Model.
	 *
	 * @param bool $load  Should the value be loaded from the database or use the value from the current instance.
	 * @return array
	 */
	public function to_json( $load ) {
		$field_data = parent::to_json( $load );

		$field_data = array_merge( $field_data, array(
			'options' => $this->parse_options( $this->get_options() ),
			'class_id' => get_the_id(),
		) );

		return $field_data;
	}

	public function parse_options( $options ) {
		$parsed = array();

		if ( is_callable( $options ) ) {
			$options = call_user_func( $options );
		}

		foreach ( $options as $key => $value ) {
			$parsed[] = array(
				'name' => $value,
				'value' => $key,

				'text' => $value,
				'id' => $key,
				'class' => '',
			);
		}

		return $parsed;
	}

	/**
	 * template()
	 *
	 * Prints the main Underscore template
	 **/
	public function template() {
		?>
		<# if (_.isEmpty(options)) { #>
			<em><?php _e( 'no options', 'carbon-fields' ); ?></em>
		<# } else { #>
			<select id="{{{ id }}}" name="{{{ name }}}">
				<# _.each(options, function(option) { #>
					<option value="{{ option.value }}" {{{ option.value == value ? 'selected="selected"' : '' }}} class="{{{ option.class }}}" data-test="test">
						{{{ option.name }}}
					</option>
				<# }) #>
			</select>
		<# } #>

		<p class="carbon-error-select-recipe"></p>
		<p class="current-date-is-latest-use">(The is currently the last use for the selected recipe.)</p>

		<?php
		/**
		*/
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
		wp_enqueue_script( 'carbon-field-select-recipe-select2', CRB_SELECT_RECIPE_URL . '/js/select2.full.min.js', array( 'carbon-fields' ) );
		wp_enqueue_script( 'carbon-field-select-recipe', CRB_SELECT_RECIPE_URL . '/js/field.js', array( 'carbon-fields' ) );

		wp_localize_script(
			'carbon-field-select-recipe',
			'php_passed_vars',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' )
			)
		);

		# Enqueue CSS
		wp_enqueue_style( 'carbon-field-select-recipe-select2', CRB_SELECT_RECIPE_URL . '/css/select2.min.css' );
		wp_enqueue_style( 'carbon-field-select-recipe', CRB_SELECT_RECIPE_URL . '/css/field.css' );
	}
}
