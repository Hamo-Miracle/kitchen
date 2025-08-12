<?php
/**
 * eForm Forms Widget
 *
 * Adds a widget to display forms on sidebars
 *
 * @package eForm - WordPress Form Builder
 * @subpackage Widgets\Form
 * @author Swashata Ghosh <swashata@ipanelthemes.com>
 * @codeCoverageIgnore
 */
class IPT_FSQM_Form_Widget extends WP_Widget {

	/*==========================================================================
	 * Static methods
	 *========================================================================*/
	public static $instantiated = false;
	public static function init() {
		if ( true === self::$instantiated ) {
			return;
		}
		self::$instantiated = true;
		add_action( 'widgets_init', function() {
			register_widget( 'IPT_FSQM_Form_Widget' );
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
			'classname' => 'ipt_fsqm_form_widget',
			'description' => __( 'Insert eForm managed forms to your sidebars.', 'ipt_fsqm' ),
		);
		parent::__construct( 'ipt_fsqm_form_widget', __( 'eForm - Insert Form', 'ipt_fsqm' ), $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		// Get widget arguments
		extract( $args, EXTR_SKIP );
		echo $before_widget;

		// Widget title
		$title = apply_filters( 'widget_title', $instance['title'] );

		if ( $title != '' ) {
			echo $before_title;
			echo $title;
			echo $after_title;
		}

		// Main form
		if ( ! isset( $instance['vertical'] ) ) {
			$instance['vertical'] = false;
		}
		echo '<div class="ipt_fsqm_form_widget_inner' . ( $instance['vertical'] == true ? ' ipt_uif_widget_vertical' : '' ) . '">';

		$form_id = $instance['form_id'];
		$form = new IPT_FSQM_Form_Elements_Front( null, $form_id );
		ob_start();
		if ( $instance['iframe'] && $instance['iframe'] === true ) {
			$form->enqueue_iframeresizer();
			$form->print_iframe_html();
		} else {
			$form->show_form();
		}

		$form_output = ob_get_clean();
		if ( WP_DEBUG !== true ) {
			$form_output = IPT_FSQM_Minify_HTML::minify( $form_output );
		}
		echo $form_output;

		echo '</div>';
		echo $after_widget;
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		// Parse the default form settings
		$instance = wp_parse_args( (array) $instance, array(
			'title' => '',
			'vertical' => false,
			'form_id' => '0',
			'iframe' => false,
		) );
		$forms = (array) IPT_FSQM_Form_Elements_Static::get_forms_for_select();
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><strong><?php _e( 'Title', 'ipt_kb' ) ?></strong></label>
			<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>" id="<?php echo $this->get_field_id( 'title' ); ?>" value="<?php echo esc_html( $instance['title'] ); ?>" />
		</p>
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
			<input type="checkbox" name="<?php echo $this->get_field_name( 'vertical' ); ?>" id="<?php echo $this->get_field_id( 'vertical' ); ?>"<?php checked( $instance['vertical'], true, true ); ?> />
			<label for="<?php echo $this->get_field_id( 'vertical' ); ?>"><?php _e( 'Optimize for small width', 'ipt_fsqm' ); ?></label>
		</p>
		<p class="description"><?php _e( 'If you want to make the form appearance optimized for smaller sidebar size, then enable this option. It will make element label appearance vertical, collapse the tabs and would do a bunch of other small width optimizations.', 'ipt_fsqm' ); ?></p>
		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'iframe' ); ?>" id="<?php echo $this->get_field_id( 'iframe' ); ?>"<?php checked( $instance['iframe'], true, true ); ?> />
			<label for="<?php echo $this->get_field_id( 'iframe' ); ?>"><?php _e( 'Embed using iFrame', 'ipt_fsqm' ); ?></label>
		</p>
		<p class="description"><?php _e( 'In some cases, the theme or any plugin can break eForm functionality or appearance. If you find eForm is not working properly, then enable this option. eForm will be shown in a proper responsive iFrame and will try to avoid all sorts of conflicts.', 'ipt_fsqm' ); ?></p>
		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		// Update the checkbox
		$updated_instance = $new_instance;
		if ( isset( $new_instance['vertical'] ) ) {
			$updated_instance['vertical'] = true;
		} else {
			$updated_instance['vertical'] = false;
		}
		if ( isset( $new_instance['iframe'] ) ) {
			$updated_instance['iframe'] = true;
		} else {
			$updated_instance['iframe'] = false;
		}
		$updated_instance['title'] = isset( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : '';
		return $updated_instance;
	}
}
