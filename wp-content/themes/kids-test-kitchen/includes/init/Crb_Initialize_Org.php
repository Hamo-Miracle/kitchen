<?php

use Carbon_Fields\Container\Container;
use Carbon_Fields\Field\Field;

/**
 * Initialize the Locations
 */
class Crb_Initialize_Org extends Crb_Initialize_Base {
	public $singular_name;
	public $plural_name;
	public $slug;
	public $is_edit_post_type_screen = false;

	public function __construct() {
		$this->singular_name = __( 'Organization', 'crb' );
		$this->plural_name = __( 'Organizations', 'crb' );
		$this->slug = 'crb_organization';

		$this->base_init();

		add_filter( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

		// UI Modifications
		add_filter( 'post_type_labels_' . $this->slug, array( $this, 'modify_labels' ) );
		add_filter( 'enter_title_here', array( $this, 'enter_title_here' ) );
		add_filter( 'crb_submit_button_label_schedule', array( $this, 'label_schedule' ), 10 );
		add_filter( 'crb_submit_button_label_publish', array( $this, 'label_publish' ), 10 );
		add_filter( 'crb_submit_button_label_submit', array( $this, 'label_submit' ), 10 );
		add_filter( 'crb_submit_button_label_update', array( $this, 'label_update' ), 10 );
	}

	/* ==========================================================================
		# Main Interface
	========================================================================== */

		/**
		 * Returns Array of additional post type arguments
		 */
		public function get_post_type_args() {
			return array(
				'menu_icon' => 'dashicons-admin-site-alt3',
				'supports' => array( 'title', 'author' ),
			);
		}

		/**
		 * Register Carbon Custom Fields for the current post type
		 */
		public function register_custom_fields() {
			
			Container::make( 'post_meta', sprintf( __( '%s settings', 'crb' ), $this->singular_name ) )
				->show_on_post_type( $this->slug )
				->add_fields( array(
					//Field::make( 'textarea', 'crb_org_address', __( 'Address', 'crb' ) ),
					//Field::make( 'text', 'crb_org_phone', __( 'Phone', 'crb' ) ),
					//Field::make( 'text', 'crb_org_contact_name', __( 'Contact Person Name', 'crb' ) ),
					//Field::make( 'text', 'crb_org_contact_email', __( 'Contact Email', 'crb' ) ),
					//Field::make( 'textarea', 'crb_org_Notes', __( 'Helpful organization details: (parking, specific entry directions, etc)', 'crb' ) ),
					Field::make("html", "crb_locations_text")
					->set_html('<div id="org-locations-list"></div>'),
				) );
			
			
				
		}

		/**
		 * Register Custom Taxonomies
		 */
		function register_taxonomies() {
		}

		/**
		 * Register Custom Admin Columns
		 */
		function register_admin_columns() {
			$columns_to_be_removed = array();
		}

		/**
		 * Return options for dropdown
		 */
		static function get_select_options( $post_type = '' ) {
			return PARENT::get_select_options( 'crb_organization' );
		}

	/* ==========================================================================
		# Actions, Filters
	========================================================================== */

		/**
		 * Modify the default Post Type listing Order
		 */
		public function pre_get_posts( $query ) {
			$is_correct_screen = $this->is_edit_post_type_screen && ! isset( $_GET['orderby'] );

			if ( $is_correct_screen ) {
				$query->set( 'orderby', 'title' );
				$query->set( 'order', 'ASC' );
			}
		}

		/* ==========================================================================
		# UI Modifications
	========================================================================== */

		// Change Some Labels Throughout Admin
		function modify_labels( $labels ) {
			return $labels;
		}

		// Change Some Labels Throughout Admin
		function enter_title_here( $title ) {
			global $pagenow;
			if ( $pagenow == 'post-new.php' && !empty( $_GET['post_type'] ) && $_GET['post_type'] == $this->slug ) {
				$title = __( 'Enter Organization Name here', 'crb' );
			}

			return $title;
		}

		// Change "Schedule" button Text
		function label_schedule( $label ) {
			if ( get_post_type() == $this->slug ) {
				$label = __( 'Schedule Organization', 'crb' );
			}

			return $label;
		}

		// Change "Publish" button Text
		function label_publish( $label ) {
			if ( get_post_type() == $this->slug ) {
				$label = __( 'Create Organization', 'crb' );
			}

			return $label;
		}

		// Change "Submit" button Text
		function label_submit( $label ) {
			if ( get_post_type() == $this->slug ) {
				$label = __( 'Submit Organization for Review', 'crb' );
			}

			return $label;
		}

		// Change "Update" button Text
		function label_update( $label ) {
			if ( get_post_type() == $this->slug ) {
				$label = __( 'Update Organization', 'crb' );
			}

			return $label;
		}
		
}

global $Crb_Initialize_Org;
$Crb_Initialize_Org = new Crb_Initialize_Org();
