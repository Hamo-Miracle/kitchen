<?php

namespace Carbon_Fields\Field;

/**
 * Single checkbox field class.
 */
class Checkbox_Required_Field extends Checkbox_Field {
	/**
	 * Return whether this field is mandatory for the user
	 *
	 * @return bool
	 **/
	public function is_required() {
		return $this->required;
	}

	/**
	 * admin_enqueue_scripts()
	 *
	 * This method is called in the admin_enqueue_scripts action. It is called once per field type.
	 * Use this method to enqueue CSS + JavaScript files.
	 *
	 */
	public static function admin_enqueue_scripts() {
		$child_dir = get_stylesheet_directory_uri();

		$dir = plugin_dir_url( __FILE__ );

		# Enqueue JS
		wp_enqueue_script( 'carbon-field-CheckboxRequired', $child_dir . '/js/field-CheckboxRequired.js', array( 'carbon-fields' ) );

		# Enqueue CSS
		wp_enqueue_style( 'carbon-field-CheckboxRequired', $child_dir . '/assets/field-CheckboxRequired.css' );
	}
}
