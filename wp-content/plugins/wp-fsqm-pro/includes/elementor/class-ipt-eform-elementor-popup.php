<?php

if ( class_exists( '\Elementor\Widget_Base' ) ) {
	class IPT_EForm_Elementor_Popup extends \Elementor\Widget_Base {
		public function get_name() {
			return 'eform_popup';
		}

		public function get_title() {
			return __( 'Embed Popup Form - eForm', 'ipt_fsqm' );
		}

		public function get_icon() {
			return 'eicon-container';
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

			// Main content
			$this->start_controls_section(
				'eform-popup-form-section',
				[
					'label' => __( 'Form', 'ipt_fsqm' ),
					'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
				]
			);
			$this->add_control(
				'form_id',
				[
					'label' => __( 'Select Form', 'ipt_fsqm' ),
					'type' => \Elementor\Controls_Manager::SELECT2,
					'label_block' => true,
					'options' => $options,
					'default' => '',
				]
			);
			$this->add_control(
				'button_text',
				[
					'label' => __( 'Button Text', 'ipt_fsqm' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'label_block' => true,
					'default' => __( 'Popup Form', 'ipt_fsqm' ),
				]
			);
			$this->add_control(
				'button_header',
				[
					'label' => __( 'Popup header', 'ipt_fsqm' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'label_block' => true,
					'default' => '%FORM%',
					'description' => __( '%FORM% is replaced by the form name.', 'ipt_fsqm' ),
				]
			);
			$this->add_control(
				'button_subtitle',
				[
					'label' => __( 'Popup Subtitle', 'ipt_fsqm' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'label_block' => true,
					'default' => '',
				]
			);
			$this->add_control(
				'button_pos',
				[
					'label' => __( 'Button Position', 'ipt_fsqm' ),
					'type' => \Elementor\Controls_Manager::SELECT2,
					'label_block' => true,
					'default' => 'br',
					'options' => [
						'r' => __( 'Right', 'ipt_fsqm' ),
						'br' => __( 'Bottom Right', 'ipt_fsqm' ),
						'bc' => __( 'Bottom Center', 'ipt_fsqm' ),
						'bl' => __( 'Bottom Left', 'ipt_fsqm' ),
						'l' => __( 'Left', 'ipt_fsqm' ),
						'h' => __( 'Manual Trigger' ),
					],
				]
			);
			$this->add_control(
				'button_width',
				[
					'label' => __( 'Popup Width (px)', 'ipt_fsqm' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'label_block' => true,
					'default' => 600,
					'min' => 300,
				]
			);
			$this->end_controls_section();

			// Button Style
			$this->start_controls_section(
				'eform-popup-style-section',
				[
					'label' => __( 'Style', 'ipt_fsqm' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);
			$this->add_control(
				'button_bg_color',
				[
					'label' => __( 'Button Color', 'ipt_fsqm' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'default' => '#3c609e',
					'alpha' => true,
				]
			);
			$this->add_control(
				'button_color',
				[
					'label' => __( 'Text Color', 'ipt_fsqm' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'default' => '#ffffff',
					'alpha' => true,
				]
			);
			$this->add_control(
				'button_style',
				[
					'label' => __( 'Button Style', 'ipt_fsqm' ),
					'type' => \Elementor\Controls_Manager::SELECT2,
					'default' => 'rect',
					'options' => [
						'circ' => __( 'Circular', 'ipt_fsqm' ),
						'rect' => __( 'Rectangular', 'ipt_fsqm' ),
					],
				]
			);
			$this->add_control(
				'button_icon',
				[
					'label' => __( 'Button Icon', 'ipt_fsqm' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'default' => [
						'value' => 'fas fa-life-ring',
						'library' => 'solid'
					],
					'skin' => 'inline',
					'label_block' => false,
					'description' => __( 'Choose an icon for the button.', 'ipt_fsqm' ),
				]
			);

			$this->end_controls_section();
		}

		protected function render() {
			$settings = $this->get_settings_for_display();
			// Configs
			$config = array();
			$config['label'] = $settings['button_text'];
			$config['color'] = $settings['button_color'];
			$config['bgcolor'] = $settings['button_bg_color'];
			$config['position'] = $settings['button_pos'];
			$config['style'] = $settings['button_style'];
			$config['header'] = $settings['button_header'];
			$config['subtitle'] = $settings['button_subtitle'];
			ob_start();
			\Elementor\Icons_Manager::render_icon( $settings['button_icon'] );
			$config['icon'] = ob_get_clean();
			$config['isElementor'] = true;
			$config['width'] = $settings['button_width'];
			// Popup
			$popup = new EForm_Popup_Helper( $settings['form_id'], $config );
			$edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();

			if ( $edit_mode ) {
				?>
				<p><?php _e( 'Popup button will show up in the page once you exit elementor edit mode.', 'ipt_fsqm' ); ?></p>
				<?php
				if ( $settings['button_pos'] === 'h' ) {
					?>
					<p>
						<?php _e( 'Since you have selected Hidden / Manual Trigger, make sure you have the following code available somewhere in the page:', 'ipt_fsqm' ); ?>
					</p>
					<p>
						<pre><code>
&lt;a href=&quot;#ipt-fsqm-popup-form-<?php echo $settings['form_id'] ?>&quot;&gt;<?php echo $settings['button_text'] ?>&lt;/a&gt;
						</code></pre>
					</p>
					<?php
				}
			} else {
				$popup->init_js();
			}
		}
	}
}
