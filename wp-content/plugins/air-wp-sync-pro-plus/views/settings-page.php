<?php
/**
 * Display the plugin settings page.
 *
 * @package Air_WP_Sync_Pro
 */

/**
 * Plugin settings page.
 *
 * @param Air_WP_Sync_Options $options {
 *  @type string $options[license_key] License key.
 *  @type string $license_status License status.
 *  @type number $cache_duration Cache duration.
 * }
 */
return function ( $options ) {
	$license_key    = $options->get( 'license_key' );
	$license_status = $options->get( 'license_status' );
	$cache_duration = $options->get( 'cache_duration' );
	?>
<div class="wrap">

	<h2><?php esc_html_e( 'Settings', 'air-wp-sync' ); ?></h2>

	<form method="post">

		<?php wp_nonce_field( 'air-wp-sync-settings-form' ); ?>

		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<label for="license_key">
						<span><?php esc_html_e( 'License Key', 'air-wp-sync' ); ?></span>
						<span class="airwpsync-tooltip" aria-label="<?php echo esc_attr__( 'Get your License Key from your <a href="https://wpconnect.co/my-account/" target="_blank">WP connect account</a>', 'air-wp-sync' ); ?>">?</span>
					</label>
				</th>
				<td>
					<div>
						<input class="regular-text ltr"
							type="text"
							name="license_key"
							value="<?php echo esc_attr( $license_key ); ?>" />
						<?php if ( 'valid' === $license_status ) : ?>
							<button name="air-wp-sync-license-deactivate" class="button airwpsync-button-delete"><?php esc_html_e( 'De-activate', 'air-wp-sync' ); ?></button>
							<p class="description airwpsync-valid"><?php esc_html_e( 'Your license is valid and activated.', 'air-wp-sync' ); ?></p>
						<?php else : ?>
							<button name="air-wp-sync-license-activate" class="button airwpsync-button-success"><?php esc_html_e( 'Activate', 'air-wp-sync' ); ?></button>
						<?php endif; ?>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="cache_duration"><?php esc_html_e( 'Cache duration (minutes)', 'air-wp-sync' ); ?></label>
				</th>
				<td>
					<div>
						<input class="regular-text ltr"
							type="number"
							name="cache_duration"
							min=2
							value="<?php echo esc_attr( $cache_duration ); ?>" />
							<p class="description"><?php esc_html_e( 'WordPress caches your Airtable table structure for 15 minutes by default to improve performance. If changes made in Airtable aren’t showing right away, try clearing the cache or adjusting the duration above.', 'air-wp-sync' ); ?></p>
					</div>
				</td>
			</tr>
		</table>
		<div id="poststuff"></div>

		<p class="submit">
			<input class="button button-primary"
					type="submit"
					name="air-wp-sync-settings-update"
					value="<?php esc_html_e( 'Update settings', 'air-wp-sync' ); ?>"
			/>
			<input class="button button-secondary"
					type="submit"
					name="air-wp-sync-settings-clear-cache"
					value="<?php esc_html_e( 'Clear Cache', 'air-wp-sync' ); ?>"
			/>
		</p>
	</form>
</div>
	<?php
};
