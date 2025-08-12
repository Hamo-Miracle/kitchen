<?php
/**
 * eForm Material UI class
 *
 * It extends and overrides the front UI to print in material style
 *
 * It also overrides some enqueues, especially with JS validation and UI
 * initiator
 *
 * @package eForm - WordPress Form Builder
 * @subpackage UI\Material
 * @author Swashata Ghosh <swashata@wpquark.com>
 */
class EForm_Material_UI extends IPT_Plugin_UIF_Front {
	/**
	 * The instance variable
	 *
	 * This is a singleton class and we are going to use this
	 * for getting the only instance
	 */
	protected static $instance = false;

	protected function __construct() {
		parent::__construct();
	}

	public function enqueue( $ignore_css = array(), $ignore_js = array(), $additional_css = array(), $additional_js = array(), $additional_localize = array() ) {
		// Enqueue from the IPT_Plugin_UIF_Front
		parent::enqueue( $ignore_css, $ignore_js );
		// Now we need some specific CSS and JS for our material themes
		// Some shortcut URLs
		$static_location = $this->static_location;
		$bower_components = $this->bower_components;
		$bower_builds = $this->bower_builds;
		$version = IPT_FSQM_Loader::$version;

		// Styles
		$styles = array(
			'ipt-eform-material-jquery-ui-structure' => array( $static_location . 'front/css/jquery-ui/jquery-ui.structure.css', array() ),
		);
		// If RTL
		if ( is_rtl() ) {
			$styles['eform-material-rtl'] = array( $static_location . 'front/css/material-themes/rtl/eform-rtl.css', array() );
		}
		// Add the additionals
		$styles = $styles + $additional_css;

		// Scripts
		$scripts = array(
			'eform-material-waves' => array( $bower_components . 'Waves/dist/waves.min.js', array() ),
			'eform-material-js' => array( $static_location . 'front/js/jquery.eform-material.min.js', array( 'jquery', 'eform-material-waves', 'ipt-plugin-uif-front-js' ) ),
		);
		// And the additionals
		$scripts = $scripts + $additional_js;
		// Script Localize
		$scripts_localize = array();
		// And the additionals
		$scripts_localize = $scripts_localize + $additional_localize;
		// Enqueue
		parent::enqueue( $ignore_css, $ignore_js, $styles, $scripts, $scripts_localize );

		do_action( 'ipt_eform_material_enqueue', $this );
	}

	/*==========================================================================
	 * UI Elements
	 *========================================================================*/
	/**
	 * Create jQuery UI Tabs
	 *
	 * @param      array    $tabs      Associative array of tabs
	 * @param      array    $data      HTML data attributes
	 * @param      boolean  $vertical  Whether to print vertical tab ( not used
	 *                                 )
	 * @param      array    $classes   Additional classes
	 */
	public function tabs( $tabs, $data = array(), $vertical = false, $classes = array() ) {
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_tabs';
		$data_attr = $this->convert_data_attributes( $data );
		$classes[] = ( $vertical == true ) ? 'vertical' : 'horizontal';
?>
<div<?php echo $data_attr; ?> class="<?php echo implode( ' ', $classes ); ?>">
	<div class="ipt-eform-tab-nav-wrap">
		<ul>
			<?php foreach ( $tabs as $tab ) : ?>
			<?php $tab = wp_parse_args( $tab, array(
				'id' => '',
				'label' => '',
				'sublabel' => '',
				'callback' => '',
				'icon' => 'none',
				'classes' => array(),
			) ); ?>
			<li id="<?php echo $tab['id'] . '_control_li'; ?>"><a class="eform-ripple" href="#<?php echo $tab['id']; ?>"><?php $this->print_icon( $tab['icon'], false ); ?><span class="eform-tab-labels"><?php echo $this->get_page_label_html( $tab['label'] ); ?> <?php if ( ! empty( $tab['sublabel'] ) ) echo '<span class="ipt_uif_tab_subtitle">' . $tab['sublabel'] . '</span>'; ?></span></a></li>
			<?php endforeach; ?>
		</ul>
		<i class="eform-tab-nav eform-tab-nav-right ipt-icomoon-angle-right disabled eform-ripple"></i>
		<i class="eform-tab-nav eform-tab-nav-left ipt-icomoon-angle-left disabled eform-ripple"></i>
		<span class="eform-tab-passive-notifier"></span>
		<span class="eform-tab-active-notifier"></span>
	</div>

	<?php foreach ( $tabs as $tab ) : ?>
	<?php
		$tab = wp_parse_args( $tab, array(
			'id' => '',
			'label' => '',
			'callback' => '',
			'icon' => 'none',
			'classes' => array(),
		) );

		if ( !$this->check_callback( $tab['callback'] ) ) {
			$tab['callback'] = array(
				array( $this, 'msg_error' ), 'Invalid Callback',
			);
		}
		$tab['callback'][1][] = $tab;
		$tab_classes = isset( $tab['classes'] ) && is_array( $tab['classes'] ) ? $tab['classes'] : array();
?>
	<div id="<?php echo $tab['id']; ?>" class="<?php echo implode( ' ', $tab_classes ); ?>">
		<?php call_user_func_array( $tab['callback'][0], $tab['callback'][1] ); ?>
		<?php $this->clear(); ?>
	</div>
	<?php endforeach; ?>
</div>
<div class="clear"></div>
		<?php
	}
}
