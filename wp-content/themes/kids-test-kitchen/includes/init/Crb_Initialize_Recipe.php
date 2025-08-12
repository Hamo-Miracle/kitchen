<?php

use Carbon_Fields\Container\Container;
use Carbon_Fields\Field\Field;

/**
 * Initialize the Recipes
 */
class Crb_Initialize_Recipe extends Crb_Initialize_Base {
	public $singular_name;
	public $plural_name;
	public $slug;

	function __construct() {
		$this->singular_name = __( 'Recipe', 'crb' );
		$this->plural_name = __( 'Recipes', 'crb' );
		$this->slug = 'crb_recipe';

		$this->base_init();

		add_filter( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

		// UI Modifications
		add_filter( 'post_type_labels_' . $this->slug, array( $this, 'modify_labels' ) );
		add_filter( 'enter_title_here', array( $this, 'enter_title_here' ) );
		add_filter( 'crb_submit_button_label_schedule', array( $this, 'label_schedule' ), 10 );
		add_filter( 'crb_submit_button_label_publish', array( $this, 'label_publish' ), 10 );
		add_filter( 'crb_submit_button_label_submit', array( $this, 'label_submit' ), 10 );
		add_filter( 'crb_submit_button_label_update', array( $this, 'label_update' ), 10 );
		add_filter( 'template_redirect', array( $this, 'template_redirect' ), 10 );
	}

	/* ==========================================================================
		# Main Interface
	========================================================================== */

		/**
		 * Returns Array of additional post type arguments
		 */
		function get_post_type_args() {
			return array(
				'public' => true,
				'exclude_from_search' => true,
				'menu_icon' => 'dashicons-carrot',
				'supports' => array( 'title', 'editor', 'author' ),
			);
		}

		/**
		 * Register Carbon Custom Fields for the current post type
		 */
		function register_custom_fields() {
			// No additional custom filds are required.

			/**
			Container::make( 'post_meta', sprintf( __( '%s settings', 'crb' ), $this->singular_name ) )
				->show_on_post_type( $this->slug )
				->add_fields( array(
				) );
			*/
		}

		/**
		 * Register Custom Taxonomies
		 */
		function register_taxonomies() {
			$taxonomy_slug = 'crb_recipe_flavor';
			$capabilities = $this->generate_taxonomy_capabilities( $taxonomy_slug );
			$this->taxonomy_capabilities[$taxonomy_slug] = $capabilities;

			register_taxonomy(
				$taxonomy_slug, # Taxonomy name
				array( $this->slug ), # Post Types
				array( # Arguments
					'labels'            => array(
						'name'              => __( 'Flavors', 'crb' ),
						'singular_name'     => __( 'Flavor', 'crb' ),
						'search_items'      => __( 'Search Flavors', 'crb' ),
						'all_items'         => __( 'All Flavors', 'crb' ),
						'parent_item'       => __( 'Parent Flavor', 'crb' ),
						'parent_item_colon' => __( 'Parent Flavor:', 'crb' ),
						'view_item'         => __( 'View Flavor', 'crb' ),
						'edit_item'         => __( 'Edit Flavor', 'crb' ),
						'update_item'       => __( 'Update Flavor', 'crb' ),
						'add_new_item'      => __( 'Add New Flavor', 'crb' ),
						'new_item_name'     => __( 'New Flavor Name', 'crb' ),
						'menu_name'         => __( 'Flavors', 'crb' ),
					),
					'hierarchical'      => true,
					'show_ui'           => true,
					'show_admin_column' => true,
					'query_var'         => true,
					'rewrite'           => array( 'slug' => 'recipe-flavor' ),
					'capabilities'      => $capabilities,
				)
			);

			$taxonomy_slug = 'crb_recipe_temperature';
			$capabilities = $this->generate_taxonomy_capabilities( $taxonomy_slug );
			$this->taxonomy_capabilities[$taxonomy_slug] = $capabilities;

			register_taxonomy(
				$taxonomy_slug, # Taxonomy name
				array( $this->slug ), # Post Types
				array( # Arguments
					'labels'            => array(
						'name'              => __( 'Temperatures', 'crb' ),
						'singular_name'     => __( 'Temperature', 'crb' ),
						'search_items'      => __( 'Search Temperatures', 'crb' ),
						'all_items'         => __( 'All Temperatures', 'crb' ),
						'parent_item'       => __( 'Parent Temperature', 'crb' ),
						'parent_item_colon' => __( 'Parent Temperature:', 'crb' ),
						'view_item'         => __( 'View Temperature', 'crb' ),
						'edit_item'         => __( 'Edit Temperature', 'crb' ),
						'update_item'       => __( 'Update Temperature', 'crb' ),
						'add_new_item'      => __( 'Add New Temperature', 'crb' ),
						'new_item_name'     => __( 'New Temperature Name', 'crb' ),
						'menu_name'         => __( 'Temperatures', 'crb' ),
					),
					'hierarchical'      => true,
					'show_ui'           => true,
					'show_admin_column' => true,
					'query_var'         => true,
					'rewrite'           => array( 'slug' => 'recipe-temperature' ),
					'capabilities'      => $capabilities,
				)
			);

            $taxonomy_slug = 'crb_recipe_season';
            $capabilities = $this->generate_taxonomy_capabilities( $taxonomy_slug );
            $this->taxonomy_capabilities[$taxonomy_slug] = $capabilities;

            register_taxonomy(
                $taxonomy_slug, # Taxonomy name
                array( $this->slug ), # Post Types
                array(
                // This array of options controls the labels displayed in the WordPress Admin UI
                'labels' => array(
                    'name' => _x( 'Season', 'taxonomy general name' ),
                    'singular_name' => _x( 'Season', 'taxonomy singular name' ),
                    'search_items' =>  __( 'Search Seasons' ),
                    'all_items' => __( 'All Seasons' ),
                    'parent_item' => __( 'Parent Season' ),
                    'parent_item_colon' => __( 'Parent Season:' ),
                    'view_item'         => __( 'View Season', 'crb' ),
                    'edit_item' => __( 'Edit Season' ),
                    'update_item' => __( 'Update Season' ),
                    'add_new_item' => __( 'Add New Season' ),
                    'new_item_name' => __( 'New Season Name' ),
                    'menu_name' => __( 'Seasons' ),
                ),
                // Control the slugs used for this taxonomy
                'rewrite' => array(
                    'slug' => 'recipe-season', // This controls the base slug that will display before each term
                    'with_front' => false, // Don't display the category base before "/locations/"
                    'hierarchical' => true // This will allow URL's like "/locations/boston/cambridge/"
                ),
                // Hierarchical taxonomy (like categories)
                'hierarchical' => true,
                'show_ui'           => true,
                'show_admin_column' => true,
                'query_var'         => true,
//                'capabilities'      => $capabilities,
            ));

            $taxonomy_slug = 'crb_recipe_alert';
            register_taxonomy(
                $taxonomy_slug, # Taxonomy name
                array( $this->slug ), # Post Types
                array(
                    // This array of options controls the labels displayed in the WordPress Admin UI
                    'labels' => array(
                        'name' => _x( 'Alert', 'taxonomy general name' ),
                        'singular_name' => _x( 'Alert', 'taxonomy singular name' ),
                        'search_items' =>  __( 'Search Alerts' ),
                        'all_items' => __( 'All Alerts' ),
                        'parent_item' => __( 'Parent Alert' ),
                        'parent_item_colon' => __( 'Parent Alert:' ),
                        'view_item'         => __( 'View Alert', 'crb' ),
                        'edit_item' => __( 'Edit Alert' ),
                        'update_item' => __( 'Update Alert' ),
                        'add_new_item' => __( 'Add New Alert' ),
                        'new_item_name' => __( 'New Alert Name' ),
                        'menu_name' => __( 'Alerts' ),
                    ),
                    // Control the slugs used for this taxonomy
                    'rewrite' => array(
                        'slug' => 'recipe-alert', // This controls the base slug that will display before each term
                        'with_front' => false, // Don't display the category base before "/locations/"
                        'hierarchical' => true // This will allow URL's like "/locations/boston/cambridge/"
                    ),
                    // Hierarchical taxonomy (like categories)
                    'hierarchical' => true,
                    'show_ui'           => true,
                    'show_admin_column' => true,
                    'query_var'         => true,
//                'capabilities'      => $capabilities,
                ));

            $taxonomy_slug = 'crb_recipe_modification';
            register_taxonomy(
                $taxonomy_slug, # Taxonomy name
                array( $this->slug ), # Post Types
                array(
                    // This array of options controls the labels displayed in the WordPress Admin UI
                    'labels' => array(
                        'name' => _x( 'Modification', 'taxonomy general name' ),
                        'singular_name' => _x( 'Modification', 'taxonomy singular name' ),
                        'search_items' =>  __( 'Search Modifications' ),
                        'all_items' => __( 'All Modifications' ),
                        'parent_item' => __( 'Parent Modification' ),
                        'parent_item_colon' => __( 'Parent Modification:' ),
                        'view_item'         => __( 'View Modification', 'crb' ),
                        'edit_item' => __( 'Edit Modification' ),
                        'update_item' => __( 'Update Modification' ),
                        'add_new_item' => __( 'Add New Modification' ),
                        'new_item_name' => __( 'New Modification Name' ),
                        'menu_name' => __( 'Modifications' ),
                    ),
                    // Control the slugs used for this taxonomy
                    'rewrite' => array(
                        'slug' => 'recipe-modification', // This controls the base slug that will display before each term
                        'with_front' => false, // Don't display the category base before "/locations/"
                        'hierarchical' => true // This will allow URL's like "/locations/boston/cambridge/"
                    ),
                    // Hierarchical taxonomy (like categories)
                    'hierarchical' => true,
                    'show_ui'           => true,
                    'show_admin_column' => true,
                    'query_var'         => true,
//                'capabilities'      => $capabilities,
                ));
		}

		/**
		 * Register Custom Admin Columns
		 */
		function register_admin_columns() {
			// No custom admin columns are required
		}

		/**
		 * Return options for dropdown
		 */
		static function get_select_options( $post_type = '' ) {
			return PARENT::get_select_options( 'crb_recipe' );
		}

	/* ==========================================================================
		# Actions, Filters
	========================================================================== */

		/**
		 * Modify the default Post Type listing Order
		 */
		public function pre_get_posts( $query ) {
			if (
				$this->is_edit_post_type_screen &&
				! isset( $_GET['orderby'] )
			) {

				$query->set( 'orderby', 'title' );
				$query->set( 'order', 'ASC' );
			}
		}

		/**
		 * Redirect Single Recipe back to home for non-logged in users
		 */
		public function template_redirect() {
			// Do nothing on non-singular recipe
			if ( ! is_singular( $this->slug ) ) {
				return;
			}

			// Do nothing - If the user is administrator or crb_facilitator, they can see the recipe entry
			if ( Crb_Current_User()->is( 'administrator' ) || Crb_Current_User()->is( 'crb_facilitator' ) ) {
				return;
			}

			wp_redirect( home_url( '/' ) );
			exit;
		}

	/* ==========================================================================
		# Callbacks
	========================================================================== */

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
				$title = __( 'Enter Recipe Name here', 'crb' );
			}

			return $title;
		}

		// Change "Schedule" button Text
		function label_schedule( $label ) {
			if ( get_post_type() == $this->slug ) {
				$label = __( 'Schedule Recipe', 'crb' );
			}

			return $label;
		}

		// Change "Publish" button Text
		function label_publish( $label ) {
			if ( get_post_type() == $this->slug ) {
				$label = __( 'Create Recipe', 'crb' );
			}

			return $label;
		}

		// Change "Submit" button Text
		function label_submit( $label ) {
			if ( get_post_type() == $this->slug ) {
				$label = __( 'Submit Recipe for Review', 'crb' );
			}

			return $label;
		}

		// Change "Update" button Text
		function label_update( $label ) {
			if ( get_post_type() == $this->slug ) {
				$label = __( 'Update Recipe', 'crb' );
			}

			return $label;
		}
}

global $Crb_Initialize_Recipe;
$Crb_Initialize_Recipe = new Crb_Initialize_Recipe();
