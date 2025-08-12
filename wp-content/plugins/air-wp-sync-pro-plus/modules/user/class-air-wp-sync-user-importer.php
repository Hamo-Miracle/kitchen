<?php
/**
 * User Importer.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

/**
 * Class Air_WP_Sync_User_Importer
 */
class Air_WP_Sync_User_Importer extends Air_WP_Sync_Abstract_Importer {

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

		$users = get_users(
			array(
				'exclude'    => $content_ids,
				'fields'     => 'ID',
				'number'     => -1,
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'   => '_air_wp_sync_importer_id',
						'value' => $this->infos()->get( 'id' ),
					),
				),
			)
		);

		$count_deleted = 0;
		foreach ( $users as $user_id ) {
			if ( wp_delete_user( $user_id ) ) {
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
	 * @param mixed|null $user_id WordPress object id.
	 *
	 * @throws \Exception From wp_update_post / wp_insert_post.
	 */
	protected function import_record( $record, $user_id = null ) {
		$this->log( sprintf( $user_id ? '- Update record %s' : '- Create record %s', $record->id ) );

		$record = apply_filters( 'airwpsync/import_record_data', $record, $this );
		$fields = $this->get_mapped_fields( $record );

		$user_metas = array(
			'_air_wp_sync_record_id'   => $record->id,
			'_air_wp_sync_hash'        => $this->generate_hash( $record ),
			'_air_wp_sync_importer_id' => $this->infos()->get( 'id' ),
			'_air_wp_sync_updated_at'  => gmdate( 'Y-m-d H:i:s' ),
		);

		/**
		 * Filters import user data that will be used by wp_update_user and wp_insert_user.
		 *
		 * @param array $post_data Post data.
		 * @param Air_WP_Sync_Post_Importer $importer Post importer.
		 * @param array $fields Fields.
		 * @param \stdClass $record Airtable record.
		 * @param mixed|null $user_id WordPress user id.
		 */
		$user_data = apply_filters( 'airwpsync/import_user_data', array(), $this, $fields, $record, $user_id );

		// Make sure we have mandatory data.
		if ( empty( $user_data['user_pass'] ) && ! $user_id ) {
			$user_data['user_pass'] = wp_generate_password();
		}
		if ( empty( $user_data['role'] ) ) {
			$user_data['role'] = $this->config()->get( 'default_role' );
		}
		if ( empty( $user_data['locale'] ) && 'site-default' !== $this->config()->get( 'default_locale' ) ) {
			$user_data['locale'] = $this->config()->get( 'default_locale' );
		}

		$all_roles_keys = Air_WP_Sync_User_Helpers::get_available_user_roles_keys();
		if ( ! empty( $user_data['role'] ) && ! in_array( $user_data['role'], $all_roles_keys, true ) ) {
			$this->log( sprintf( '- The role "%s" does not exist, it has been ignored. Allowed list: %s', $user_data['role'], implode( ', ', $all_roles_keys ) ) );
			unset( $user_data['role'] );
		}

		$is_new_user = null === $user_id;

		// Insert or update post.
		if ( $user_id ) {
			$user_id = wp_update_user( array_merge( array( 'ID' => $user_id ), $user_data ), true );
		} else {
			$user_id = wp_insert_user( $user_data, true );
		}

		if ( is_wp_error( $user_id ) ) {
			throw new \Exception( esc_html( $user_id->get_error_message() ) );
		}

		// Handle metas.
		foreach ( $user_metas as $meta_key => $meta_value ) {
			update_user_meta( $user_id, $meta_key, $meta_value );
		}

		do_action( 'airwpsync/import_record_after', $this, $fields, $record, $user_id );

		if ( $is_new_user && $this->config()->get( 'send_user_notification' ) ) {
			do_action( 'edit_user_created_user', $user_id, 'both' );
		}

		return $user_id;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param object $record Airtable record.
	 *
	 * @return mixed|false
	 */
	protected function get_existing_content_id( $record ) {
		$record         = apply_filters( 'airwpsync/pre_check_existing_content', $record, $this, array( 'user::user_email' ), $this->get_import_fields_options() );
		$mapping        = ! empty( $this->config()->get( 'mapping' ) ) ? $this->config()->get( 'mapping' ) : array();
		$airtable_value = '';
		foreach ( $mapping as $mapping_field ) {
			if ( 'user::user_email' === $mapping_field['wordpress'] ) {
				$airtable_key   = $mapping_field['airtable'];
				$airtable_value = isset( $record->fields->$airtable_key ) ? $record->fields->$airtable_key : '';
			}
		}
		$user = is_email( $airtable_value ) ? get_user_by( 'email', $airtable_value ) : get_user_by( 'login', $airtable_value );
		return $user ? $user->ID : false;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param mixed $content_id WordPress object id.
	 */
	protected function get_existing_content_hash( $content_id ) {
		return get_user_meta( $content_id, '_air_wp_sync_hash', true );
	}
}
