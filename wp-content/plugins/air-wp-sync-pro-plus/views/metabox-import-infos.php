<?php
/**
 * Display import infos and Sync button.
 *
 * @package Air_WP_Sync_Pro
 */

/**
 * Metabox import infos.
 *
 * @param Air_WP_Sync_Abstract_Importer|bool $importer
 * @param int $importer_id
 * @param boolean $importer_is_running
 * @param Air_WP_Sync_Metabox_Import_Infos $metabox_import_infos
 */
return function ( $importer, $importer_id, $importer_is_running, $metabox_import_infos ) {
	?>
	<?php wp_nonce_field( 'air-wp-sync-trigger-update', 'air-wp-sync-trigger-update-nonce' ); ?>

<div id="airwpsync-import">
	<button id="airwpsync-import-button"
			type="button"
			class="button <?php echo esc_attr( $importer_is_running ? 'loading' : '' ); ?>"
			data-importer="<?php echo esc_attr( $importer_id ); ?>"
			x-bind:disabled="<?php echo ( 'publish' === get_post_status() ) ? 'configHasChanged()' : 'true'; ?>">
		<span class="dashicons dashicons-update"></span>
		<span class="label"><?php esc_html_e( 'Sync now', 'air-wp-sync' ); ?></span>
	</button>
	<span id="airwpsync-import-feedback"></span>
</div>
<button id="airwpsync-cancel-button" type="button"><?php esc_html_e( 'Cancel', 'air-wp-sync' ); ?></button>
<template x-if="<?php echo ( 'publish' === get_post_status() ) ? 'configHasChanged()' : 'true'; ?>">
	<p><?php echo esc_html__( 'You will be able to sync your Airtable content once you have saved this connection.', 'air-wp-sync' ); ?></p>
</template>

<div id="airwpsync-import-stats">
	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $metabox_import_infos->get_stats_html( $importer_id );
	?>
</div>

	<?php
};
