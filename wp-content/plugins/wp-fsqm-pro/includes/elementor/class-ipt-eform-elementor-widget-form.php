<?php
/**
 * Elementor Widget for eForm Form.
 */
if ( class_exists( '\Elementor\Widget_Base' ) ) {
	class IPT_EForm_Elementor_Widget_Form extends \Elementor\Widget_Base {
		protected $eform_enqueues = [];

		public function __construct( $data = [], $args = null ) {
			parent::__construct( $data, $args );
			$this->eform_enqueues = IPT_FSQM_Form_Elements_Front::register_iframeresizer();
		}

		public function get_script_depends() {
			return $this->eform_enqueues;
		}

		public function get_name() {
			return 'eform_form';
		}

		public function get_title() {
			return __( 'Embed Form - eForm', 'ipt_fsqm' );
		}

		public function get_icon() {
			return 'eicon-form-horizontal';
		}

		public function get_categories() {
			return [ 'eform-widgets' ];
		}

		protected function _register_controls() {
			$all_forms = \IPT_FSQM_Form_Elements_Static::get_forms_for_select();
			$options = [];
			if ( is_array( $all_forms ) ) {
				$all_forms = array_reverse( $all_forms );
				foreach( $all_forms as $form ) {
					$options[ $form->id ] = esc_html( $form->name );
				}
			}

			$this->start_controls_section(
				'eform-form-section',
				[
					'label' => __( 'Form', 'ipt_fsqm' ),
					'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
				]
			);

			$this->add_control(
				'formId',
				[
					'label' => __( 'Select Form', 'ipt_fsqm' ),
					'type' => \Elementor\Controls_Manager::SELECT2,
					'label_block' => true,
					'options' => $options,
					'default' => '',
				]
			);
			$this->add_control(
				'iframe',
				[
					'label' => __( 'Render in iFrame', 'ipt_fsqm' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'description' => __( 'Enable to avoid theme and style conflicts.', 'ipt_fsqm' ),
				]
			);
			$this->add_control(
				'vertical',
				[
					'label' => __( 'Optimize for narrow container', 'ipt_fsqm' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'description' => __( 'If you are rendering the form in a narrow container, enable this settings. It will make sure the columns and other grids are rendered properly. It is not needed if rendering inside iFrame.', 'ipt_fsqm' ),
				]
			);


			$this->end_controls_section();
		}

		protected function render() {
			$settings = $this->get_settings_for_display();
			if ( ! $settings['formId'] ) {
				echo '<p style="border: 1px solid #a21318; padding: 20px; border-radius: 8px; border-left: 4px solid #a21318;">Please select a form from elementor widget</p>';
				return;
			}

			$edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();
			$form = new IPT_FSQM_Form_Elements_Front( null, $settings['formId'] );
			if ( $edit_mode || $settings['iframe'] === 'yes' ) {
				$form->enqueue_iframeresizer();
				$form->print_iframe_html( true );
			} else {
				ob_start();
				if ( ! isset( $instance['vertical'] ) ) {
					$instance['vertical'] = false;
				}
				echo '<div class="ipt_fsqm_form_widget_inner' . ( $settings['vertical'] == true ? ' ipt_uif_widget_vertical' : '' ) . '">';
				$form->show_form();
				echo '</div>';
				$form_output = ob_get_clean();
				if ( WP_DEBUG !== true ) {
					$form_output = IPT_FSQM_Minify_HTML::minify( $form_output );
				}
				echo $form_output;
			}
		}

		public function render_plain_content() {
			$settings = $this->get_settings_for_display();
			return '[ipt_fsqm_form id="' . $settings['formId'] . '" iframe="' . ( $settings['iframe'] === 'yes' ? '1' : '0' ) . '"]';
		}
	}
}
