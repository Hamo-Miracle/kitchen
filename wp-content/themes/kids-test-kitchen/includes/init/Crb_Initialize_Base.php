<?php

/**
 * Base functionality
 */
abstract class Crb_Initialize_Base {
	public $singular_name;
	public $plural_name;
	public $slug = 'crb_entry';
	public $is_edit_post_type_screen = false;
	public $taxonomy_capabilities = array();

	function _construct() {
		$this->singular_name = __( 'Entry', 'crb' );
		$this->plural_name = __( 'Entries', 'crb' );
	}

	function base_init() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		add_action( 'init', array( $this, 'register_admin_columns' ) );
		add_action( 'carbon_register_fields', array( $this, 'register_custom_fields' ) );
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts_init' ), 0 );
	}

	/**
	 * Populate object property
	 */
	function pre_get_posts_init( $query ) {
		global $pagenow;

		$this->is_edit_post_type_screen = is_admin() &&
			$query->is_main_query() &&
			$pagenow == 'edit.php' &&
			isset( $_GET['post_type'] ) &&
			$_GET['post_type'] == $this->slug;
	}

	function register_post_type() {
		$args = array(
			'labels' => array(
				'name' => sprintf( __( '%s', 'crb' ), $this->plural_name ),
				'singular_name' => sprintf( __( '%s', 'crb' ), $this->singular_name ),
				'add_new' => sprintf( __( 'Add New', 'crb' ), $this->singular_name ),
				'add_new_item' => sprintf( __( 'Add new %s', 'crb' ), $this->singular_name ),
				'view_item' => sprintf( __( 'View %s', 'crb' ), $this->singular_name ),
				'edit_item' => sprintf( __( 'Edit %s', 'crb' ), $this->singular_name ),
				'new_item' => sprintf( __( 'New %s', 'crb' ), $this->singular_name ),
				'view_item' => sprintf( __( 'View %s', 'crb' ), $this->singular_name ),
				'search_items' => sprintf( __( 'Search %s', 'crb' ), $this->plural_name ),
				'not_found' =>  sprintf( __( 'No %s found', 'crb' ), $this->plural_name ),
				'not_found_in_trash' => sprintf( __( 'No %s found in trash', 'crb' ), $this->plural_name ),
				'rest_base' => $this->plural_name ,
    			'rest_controller_class' => 'WP_REST_Posts_Controller',
			),
			'public' => true,
			'publicly_queryable' => true,
			'query_var' => true,
			'show_ui' => true,
			'show_in_rest' => true,
			'capability_type' => $this->slug,
			'capabilities' => $this->get_post_type_capabilities(),
			'hierarchical' => true,
			'menu_icon' => 'dashicons-admin-post',
			'supports' => array( 'title', 'editor', 'page-attributes', 'author' ),
		);

		$args = wp_parse_args( $this->get_post_type_args(), $args );

		register_post_type( $this->slug, $args );
	}

	function get_post_type_capabilities() {
		return array(
			// Meta capabilities
			'edit_post'              => sprintf( 'edit_%s', $this->slug ),
			'read_post'              => sprintf( 'read_%s', $this->slug ),
			'delete_post'            => sprintf( 'delete_%s', $this->slug ),

			// Primitive capabilities used outside of map_meta_cap():
			'edit_posts'             => sprintf( 'edit_%ss', $this->slug ),
			'edit_others_posts'      => sprintf( 'edit_others_%ss', $this->slug ),
			'publish_posts'          => sprintf( 'publish_%ss', $this->slug ),
			'read_private_posts'     => sprintf( 'read_private_%ss', $this->slug ),

			// Primitive capabilities used within map_meta_cap():
			// 'read'                   =>          'read',
			'delete_posts'           => sprintf( 'delete_%ss', $this->slug ),
			'delete_private_posts'   => sprintf( 'delete_private_%ss', $this->slug ),
			'delete_published_posts' => sprintf( 'delete_published_%ss', $this->slug ),
			'delete_others_posts'    => sprintf( 'delete_others_%ss', $this->slug ),
			'edit_private_posts'     => sprintf( 'edit_private_%ss', $this->slug ),
			'edit_published_posts'   => sprintf( 'edit_published_%ss', $this->slug ),
			'create_posts'           => sprintf( 'create_%ss', $this->slug ),
		);
	}

	function get_taxonomy_capabilities() {
		return $this->taxonomy_capabilities;
	}

	function generate_taxonomy_capabilities( $taxonomy_slug ) {
		return array(
			'manage_terms' => sprintf ( 'manage_%s', $taxonomy_slug ),
			'edit_terms' => sprintf ( 'edit_%s', $taxonomy_slug ),
			'delete_terms' => sprintf ( 'delete_%s', $taxonomy_slug ),
			'assign_terms' => sprintf ( 'assign_%s', $taxonomy_slug ),
		);
	}

	static function get_select_options( $post_type = '' ) {
		if ( empty( $post_type ) ) {
			return array();
		}

		$args = array(
			'post_type' => $post_type,
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'order' => 'ASC',
			'orderby' => 'title',
		);

		// Limit results to only personally posted entries
		// For admin and assistants all posts will be returned
		if ( ! Crb_Current_User()->is( 'administrator' ) && ! Crb_Current_User()->is( 'crb_assistant' ) ) {
			$args['author'] = get_current_user_id();
		}

		$entries = get_posts( $args );
		$entries = wp_list_pluck( $entries, 'post_title', 'ID' );

		global $post;
		if ( $post_type === 'crb_recipe' ) {
			foreach ( $entries as $recipe_id => $title ) {
				$recipe = new Crb_Recipe( $recipe_id );

				$entries[$recipe_id] = $recipe->get_admin_label();
			}
		}

		$entries = array( ' --- Select --- ' ) + $entries;

		return $entries;
	}

	/**
	 * Return Edit Link to an entry by Post ID
	 */
	function get_post_edit_link( $post_id ) {
		if ( empty( $post_id ) ) {
			return;
		}

		$name = get_the_title( $post_id );

		return $this->get_post_edit_link_with_custom_name( $post_id, $name );
	}

	/**
	 * Return Edit Link to an entry by Post ID and custom name
	 */
	function get_post_edit_link_with_custom_name( $post_id, $name ) {
		$post = get_post( $post_id );

		if ( empty( $post ) ) {
			return sprintf( __( 'Post with ID <strong>%s</strong> was deleted', 'crb' ), $post_id );
		}

		if ( current_user_can( 'edit_' . $post->post_type . 's' ) && $post->post_author == Crb_Current_User()->get_id() ) {
			$link = get_edit_post_link( $post_id );
			return sprintf( '<a href="%s">%s</a>', $link, $name );
		} elseif ( current_user_can( 'edit_others_' . $post->post_type . 's' ) ) {
			$link = get_edit_post_link( $post_id );
			return sprintf( '<a href="%s">%s</a>', $link, $name );
		} else {
			return sprintf( '%s', $name );
		}
	}

	/**
	 * Return Edit Link to an entry by Post ID
	 */
	function get_user_edit_link( $user_id ) {
		if ( empty( $user_id ) ) {
			return;
		}

		$user_obj = get_user_by( 'ID', $user_id );
		if ( empty( $user_obj ) ) {
			return;
		}

		$link = get_edit_user_link( $user_id );
		$name = $user_obj->data->display_name;

		return sprintf( '<a href="%s">%s</a>', $link, $name );
	}

	/**
	 * Return Edit Link to an entry by Term ID
	 */
	function get_term_edit_link( $term_obj ) {
		if ( empty( $term_obj ) ) {
			return;
		}

		$link = get_term_link( $term_obj );
		$name = $term_obj->name;

		return sprintf( '<a href="%s">%s</a>', $link, $name );
	}

	/**
	 * Returns Array of additional post type arguments
	 */
	abstract function get_post_type_args();

	/**
	 * Register Carbon Custom Fields for the current post type
	 */
	abstract function register_custom_fields();

	/**
	 * Register Custom Taxonomies
	 */
	abstract function register_taxonomies();

	/**
	 * Register Custom Admin Columns
	 */
	abstract function register_admin_columns();

}
