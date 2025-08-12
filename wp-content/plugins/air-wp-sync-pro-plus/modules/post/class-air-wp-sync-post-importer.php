<?php
/**
 * Post Importer.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

/**
 * Class Air_WP_Sync_Post_Importer.
 */
class Air_WP_Sync_Post_Importer extends Air_WP_Sync_Abstract_Importer {
	/**
	 * Init
	 */
	protected function init() {
		if ( $this->config()->get( 'post_type' ) === 'custom' ) {
			$this->register_post_type();
		}
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param \WP_Post $importer_post_object Post object holding the importer config.
	 */
	protected function load_settings( $importer_post_object ) {
		parent::load_settings( $importer_post_object );

		// Update WordPress destination with new keys (Modules update).
		if ( $this->config->get( 'mapping' ) ) {
			$this->config->set(
				'mapping',
				array_map(
					function ( $mapping ) {
						if ( 'meta::custom_field' === $mapping['wordpress'] ) {
							$mapping['wordpress'] = 'postmeta::custom_field';
						} elseif ( 'meta::_thumbnail_id' === $mapping['wordpress'] ) {
							$mapping['wordpress'] = 'postmeta::_thumbnail_id';
						}
						return $mapping;
					},
					$this->config->get( 'mapping' )
				)
			);
		}
	}

	/**
	 * Register associated post type
	 */
	protected function register_post_type() {
		$cpt_slug = sanitize_key( $this->config()->get( 'post_type_slug' ) );
		$cpt_name = $this->config()->get( 'post_type_name' );

		if ( $cpt_slug && $cpt_name ) {
			$result = register_post_type(
				$cpt_slug,
				array(
					'labels'   => array(
						'name'          => $cpt_name,
						'singular_name' => $cpt_name,
					),
					'public'   => true,
					'supports' => array( 'title', 'editor', 'author', 'excerpt', 'thumbnail', 'custom-fields' ),
					'rewrite'  => array(
						'slug'       => $cpt_slug,
						'with_front' => false,
					),
				)
			);
			if ( is_wp_error( $result ) ) {
				$this->add_error( $result );
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete_removed_contents() {
		if ( 'add_update_delete' !== $this->config()->get( 'sync_strategy' ) ) {
			return;
		}

		$content_ids = get_post_meta( $this->infos()->get( 'id' ), 'content_ids', true ) ?? array();
		if ( ! is_array( $content_ids ) ) {
			$content_ids = array();
		}

		$posts = get_posts(
			array(
				'post_type'      => $this->get_post_type(),
				'post_status'    => 'any',
				'post__not_in'   => $content_ids,
				'fields'         => 'ids',
				'posts_per_page' => -1,
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'relation' => 'OR',
						array(
							'key'   => '_air_wp_sync_importer_id',
							'value' => $this->infos()->get( 'id' ),
						),
						array(
							'key'     => '_air_wp_sync_importer_id',
							'compare' => 'NOT EXISTS',
						),
					),
					array(
						'key'     => '_air_wp_sync_record_id',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		$count_deleted = 0;
		foreach ( $posts as $post_id ) {
			if ( wp_delete_post( $post_id, true ) ) {
				++$count_deleted;
			}
		}
		update_post_meta( $this->infos()->get( 'id' ), 'count_deleted', $count_deleted );
		return $count_deleted;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param object     $record Airtable record.
	 * @param mixed|null $existing_object_id WordPress object id.
	 *
	 * @throws \Exception From wp_update_post / wp_insert_post.
	 */
	protected function import_record( $record, $existing_object_id = null ) {
		$this->log( sprintf( $existing_object_id ? '- Update record %s' : '- Create record %s', $record->id ) );

		$record = apply_filters( 'airwpsync/import_record_data', $record, $this );
		$fields = $this->get_mapped_fields( $record );

		$post_data = array(
			'post_type'  => $this->get_post_type(),
			'post_title' => 'Airtable Imported Content',
		);

		$post_metas = array(
			'_air_wp_sync_record_id'   => $record->id,
			'_air_wp_sync_hash'        => $this->generate_hash( $record ),
			'_air_wp_sync_importer_id' => $this->infos()->get( 'id' ),
			'_air_wp_sync_updated_at'  => gmdate( 'Y-m-d H:i:s' ),
		);

		$importer = $this;
		/**
		 * Filters post data that will be used by wp_update_post or wp_insert_post.
		 *
		 * @param array $post_data Post data.
		 * @param Air_WP_Sync_Post_Importer $importer Post importer.
		 * @param array $fields Fields.
		 * @param \stdClass $record Airtable record.
		 * @param mixed|null $existing_object_id WordPress object id.
		 */
		$post_data = apply_filters( 'airwpsync/import_post_data', $post_data, $importer, $fields, $record, $existing_object_id );

		if ( empty( $post_data['post_author'] ) ) {
			$post_data['post_author'] = $this->config()->get( 'post_author' );
		}
		if ( empty( $post_data['post_status'] ) ) {
			$post_data['post_status'] = $this->config()->get( 'post_status' );
		}

		// Insert or update post.
		if ( $existing_object_id ) {
			$existing_object_id = wp_update_post( array_merge( array( 'ID' => $existing_object_id ), $post_data ), true );
		} else {
			$existing_object_id = wp_insert_post( $post_data, true );
		}

		if ( is_wp_error( $existing_object_id ) ) {
			throw new \Exception( esc_html( $existing_object_id->get_error_message() ) );
		}

		// Handle metas.
		foreach ( $post_metas as $meta_key => $meta_value ) {
			update_post_meta( $existing_object_id, $meta_key, $meta_value );
		}

		/**
		 * Fires after the record has been imported.
		 *
		 * @param Air_WP_Sync_Abstract_Importer $importer Importer.
		 * @param array $fields Fields.
		 * @param \stdClass $record Airtable record.
		 * @param mixed|null $existing_object_id WordPress object id.
		 */
		do_action( 'airwpsync/import_record_after', $this, $fields, $record, $existing_object_id );

		// Force wp_insert_post re-trigger after metas.
		do_action( 'wp_insert_post', $existing_object_id, get_post( $existing_object_id ), true );

		return $existing_object_id;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param object $record Airtable record.
	 *
	 * @return mixed|false
	 */
	protected function get_existing_content_id( $record ) {
		$objects = get_posts(
			array(
				'fields'      => 'ids',
				'post_type'   => $this->get_post_type(),
				'post_status' => 'any',
				'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'relation' => 'OR',
						array(
							'key'   => '_air_wp_sync_importer_id',
							'value' => $this->infos()->get( 'id' ),
						),
						array(
							'key'     => '_air_wp_sync_importer_id',
							'compare' => 'NOT EXISTS',
						),
					),
					array(
						'key'   => '_air_wp_sync_record_id',
						'value' => $record->id,
					),
				),
			)
		);
		if ( count( $objects ) === 0 ) {
			return false;
		}
		return array_shift( $objects );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param mixed $content_id WordPress object id.
	 */
	protected function get_existing_content_hash( $content_id ) {
		return get_post_meta( $content_id, '_air_wp_sync_hash', true );
	}

	/**
	 * Post Type getter
	 *
	 * @return string
	 */
	public function get_post_type() {
		return $this->config()->get( 'post_type' ) === 'custom' ? $this->config()->get( 'post_type_slug' ) : $this->config()->get( 'post_type' );
	}
}
