<?php
/**
 * eForm Popup Widget
 *
 * Adds a widget to display popup forms on sidebars
 *
 * @package eForm - WordPress Form Builder
 * @subpackage Widgets\Popup
 * @author Swashata Ghosh <swashata@ipanelthemes.com>
 * @codeCoverageIgnore
 */
class IPT_FSQM_Popup_Widget extends WP_Widget {

	/*==========================================================================
	 * Static methods
	 *========================================================================*/
	public static $instantiated = false;
	public static function init() {
		if ( self::$instantiated === true ) {
			return;
		}
		self::$instantiated = true;
		add_action( 'widgets_init', function() {
			register_widget( 'IPT_FSQM_Popup_Widget' );
		} );
	}


	/*==========================================================================
	 * Widget methods
	 *========================================================================*/

	/**
	 * Constructor
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'ipt_fsqm_popup_widget',
			'description' => __( 'Insert eForm managed popups to your theme.', 'ipt_fsqm' ),
		);
		parent::__construct( 'ipt_fsqm_popup_widget', __( 'eForm - Insert Popup', 'ipt_fsqm' ), $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		// Configs
		$config = array();
		$config['label'] = $instance['button_text'];
		$config['color'] = '#' . $instance['button_color'];
		$config['bgcolor'] = '#' . $instance['button_bg_color'];
		$config['position'] = $instance['button_pos'];
		$config['style'] = $instance['button_style'];
		$config['header'] = $instance['button_header'];
		$config['subtitle'] = $instance['button_subtitle'];
		$config['icon'] = $instance['button_icon'];
		$config['width'] = $instance['button_width'];
		// Popup
		$popup = new EForm_Popup_Helper( $instance['form_id'], $config );
		// Output
		$popup->init_js();
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		// Parse the default form settings
		$instance = wp_parse_args( (array) $instance, array(
			'form_id' => '0',
			'button_text' => __( 'Popup Form', 'ipt_fsqm' ),
			'button_color' => 'ffffff',
			'button_bg_color' => '3c609e',
			'button_pos' => 'br',
			'button_style' => 'rect',
			'button_header' => '%FORM%',
			'button_subtitle' => '',
			'button_icon' => 'fa fa-file-text',
			'button_width' => '600',
		) );
		$positions = array(
			'r' => __( 'Right', 'ipt_fsqm' ),
			'br' => __( 'Bottom Right', 'ipt_fsqm' ),
			'bc' => __( 'Bottom Center', 'ipt_fsqm' ),
			'bl' => __( 'Bottom Left', 'ipt_fsqm' ),
			'l' => __( 'Left', 'ipt_fsqm' ),
			'h' => __( 'Manual Trigger' ),
		);
		$styles = array(
			'circ' => __( 'Circular', 'ipt_fsqm' ),
			'rect' => __( 'Rectangular', 'ipt_fsqm' ),
		);
		$forms = (array) IPT_FSQM_Form_Elements_Static::get_forms_for_select();
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'form_id' ); ?>"><?php _e ( 'Select Form', 'ipt_fsqm' ); ?></label>
			<select class="widefat" name="<?php echo $this->get_field_name( 'form_id' ); ?>" id="<?php echo $this->get_field_id( 'form_id' ); ?>">
				<option value="0"<?php selected( $instance['form_id'], '0', true ); ?>><?php _e( '--please select a form--', 'ipt_fsqm' ); ?></option>
				<?php if ( ! empty( $forms ) ) : ?>
				<?php foreach ( $forms as $form ) : ?>
				<option value="<?php echo $form->id; ?>"<?php selected( $instance['form_id'], $form->id, true ) ?>><?php echo $form->name; ?></option>
				<?php endforeach; ?>
				<?php endif; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'button_text' ); ?>"><?php _e( 'Button Text', 'ipt_fsqm' ); ?></label>
			<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'button_text' ); ?>" id="<?php echo $this->get_field_id( 'button_text' ); ?>" value="<?php echo esc_html( $instance['button_text'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'button_color' ); ?>"><?php _e( 'Button Text Color', 'ipt_fsqm' ); ?></label>
			<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'button_color' ); ?>" id="<?php echo $this->get_field_id( 'button_color' ); ?>" value="<?php echo esc_html( $instance['button_color'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'button_bg_color' ); ?>"><?php _e( 'Button Background Color', 'ipt_fsqm' ); ?></label>
			<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'button_bg_color' ); ?>" id="<?php echo $this->get_field_id( 'button_bg_color' ); ?>" value="<?php echo esc_html( $instance['button_bg_color'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'button_pos' ); ?>"><?php _e ( 'Button Position', 'ipt_fsqm' ); ?></label>
			<select class="widefat" name="<?php echo $this->get_field_name( 'button_pos' ); ?>" id="<?php echo $this->get_field_id( 'button_pos' ); ?>">
				<?php foreach ( $positions as $p_key => $position ) : ?>
				<option value="<?php echo $p_key; ?>"<?php selected( $instance['button_pos'], $p_key, true ) ?>><?php echo $position; ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'button_style' ); ?>"><?php _e ( 'Button Style', 'ipt_fsqm' ); ?></label>
			<select class="widefat" name="<?php echo $this->get_field_name( 'button_style' ); ?>" id="<?php echo $this->get_field_id( 'button_style' ); ?>">
				<?php foreach ( $styles as $s_key => $style ) : ?>
				<option value="<?php echo $s_key; ?>"<?php selected( $instance['button_style'], $s_key, true ) ?>><?php echo $style; ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'button_header' ); ?>"><?php _e( 'Popup Title', 'ipt_fsqm' ); ?></label>
			<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'button_header' ); ?>" id="<?php echo $this->get_field_id( 'button_header' ); ?>" value="<?php echo esc_html( $instance['button_header'] ); ?>" />
		</p>
		<p><span class="description"><?php _e( '%FORM% will be replaced by the form name.', 'ipt_fsqm' ); ?></span></p>
		<p>
			<label for="<?php echo $this->get_field_id( 'button_subtitle' ); ?>"><?php _e( 'Popup Subtitle', 'ipt_fsqm' ); ?></label>
			<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'button_subtitle' ); ?>" id="<?php echo $this->get_field_id( 'button_subtitle' ); ?>" value="<?php echo esc_html( $instance['button_subtitle'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'button_icon' ); ?>"><?php _e( 'Button Icon Class', 'ipt_fsqm' ); ?></label>
			<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'button_icon' ); ?>" id="<?php echo $this->get_field_id( 'button_icon' ); ?>" value="<?php echo esc_html( $instance['button_icon'] ); ?>" />
		</p>
		<p><span class="description"><?php _e( 'Icon class for use in button and popup. Either your theme needs to provide icon CSS or you can use some plugin to include fontawesone icons.', 'ipt_fsqm' ); ?></span></p>
		<p>
			<label for="<?php echo $this->get_field_id( 'button_width' ); ?>"><?php _e( 'Initial Popup Width (pixels)', 'ipt_fsqm' ); ?></label>
			<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'button_width' ); ?>" id="<?php echo $this->get_field_id( 'button_width' ); ?>" value="<?php echo esc_html( $instance['button_width'] ); ?>" />
		</p>
		<p>
			<?php _e( 'If you select Hidden / Manual Trigger, then make sure you have the following code available somewhere in the page:', 'ipt_fsqm' ); ?>
		</p>
		<p>
			<code class="ipt_fsqm_ppw_mt">
&lt;a href=&quot;#ipt-fsqm-popup-form-<?php echo $instance['form_id'] ?>&quot; data-form-id=&quot;<?php echo $instance['form_id'] ?>&quot; data-eform-popup=&quot;1&quot;&gt;<?php echo $instance['button_text'] ?>&lt;/a&gt;
			</code>
		</p>
		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		// Update the instance
		return $new_instance;
	}
}
