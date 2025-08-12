<?php
/**
 * Term Importer.
 *
 * @package Air_WP_Sync_Free
 */

namespace Air_WP_Sync_Free;

/**
 * Class Air_WP_Sync_Term_Importer
 */
class Air_WP_Sync_Term_Importer extends Air_WP_Sync_Abstract_Importer {

	/**
	 * {@inheritDoc}
	 *
	 * @param object     $record Airtable record.
	 * @param mixed|null $term_id WordPress object id.
	 *
	 * @throws \Exception From wp_update_term / wp_insert_term.
	 */
	protected function import_record( $record, $term_id = null ) {
		$this->log( sprintf( $term_id ? '- Update record %s' : '- Create record %s', $record->id ) );

		$record = apply_filters( 'airwpsync/import_record_data', $record, $this );
		$fields = $this->get_mapped_fields( $record );

		$term_metas = array(
			'_air_wp_sync_record_id'   => $record->id,
			'_air_wp_sync_hash'        => $this->generate_hash( $record ),
			'_air_wp_sync_importer_id' => $this->infos()->get( 'id' ),
			'_air_wp_sync_updated_at'  => gmdate( 'Y-m-d H:i:s' ),
		);

		/**
		 * Filters import term data that will be used by wp_update_term and wp_insert_term.
		 *
		 * @param array $term_data Term data.
		 * @param Air_WP_Sync_Term_Importer $importer Term importer.
		 * @param array $fields Fields.
		 * @param \stdClass $record Airtable record.
		 * @param mixed|null $term_id WordPress term id.
		 */
		$term_data = apply_filters( 'airwpsync/import_term_data', array(), $this, $fields, $record, $term_id );

		// Make sure we have mandatory data.
		if ( empty( $term_data['name'] ) ) {
			throw new \Exception( esc_html__( 'Term name is missing', 'air-wp-sync' ) );
		}

		$taxonomy = ! empty( $term_data['taxonomy'] ) ? $term_data['taxonomy'] : $this->config()->get( 'taxonomy' );

		$term_data = array_merge(
			$term_data,
			array(
				'slug'        => ! empty( $term_data['slug'] ) ? $term_data['slug'] : '',
				'parent'      => ! empty( $term_data['parent'] ) ? (int) $term_data['parent'] : 0,
				'description' => ! empty( $term_data['description'] ) ? $term_data['description'] : '',
			)
		);

		$is_new_term = null === $term_id;

		// Insert or update post.
		if ( $term_id ) {
			$result = wp_update_term( $term_id, $taxonomy, $term_data );
		} else {
			$result = wp_insert_term( $term_data['name'], $taxonomy, $term_data );
		}

		if ( is_wp_error( $result ) ) {
			throw new \Exception( esc_html( $result->get_error_message() ) );
		}

		$term_id          = $result['term_id'];
		$term_taxonomy_id = $result['term_taxonomy_id'];

		// Handle metas.
		foreach ( $term_metas as $meta_key => $meta_value ) {
			update_term_meta( $term_id, $meta_key, $meta_value );
		}

		do_action( 'airwpsync/import_record_after', $this, $fields, $record, $term_id );

		return $term_id;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param object $record Airtable record.
	 *
	 * @return mixed|false
	 */
	protected function get_existing_content_id( $record ) {
		$terms = get_terms(
			array(
				'fields'     => 'ids',
				'hide_empty' => false,
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
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

		if ( count( $terms ) === 0 ) {
			return false;
		}

		return array_shift( $terms );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param mixed $content_id WordPress object id.
	 */
	protected function get_existing_content_hash( $content_id ) {
		return get_term_meta( $content_id, '_air_wp_sync_hash', true );
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

		$terms = get_terms(
			array(
				'exclude'    => $content_ids,
				'number'     => 0,
				'hide_empty' => false,
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'   => '_air_wp_sync_importer_id',
						'value' => $this->infos()->get( 'id' ),
					),
				),
			)
		);

		foreach ( $terms as $term ) {
			wp_delete_term( $term->term_id, $term->taxonomy );
		}
	}
}
