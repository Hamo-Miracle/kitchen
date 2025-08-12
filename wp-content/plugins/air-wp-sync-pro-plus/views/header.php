<?php
/**
 * Display connection header.
 *
 * @package Air_WP_Sync_Pro
 */

return function () {
	?>

<div class="airwpsync-admin-header">
	<h2>
		<a class="airwpsync-admin-header-link" href="<?php echo esc_url( admin_url( 'edit.php?post_type=airwpsync-connection' ) ); ?>">
			<img class="airwpsync-admin-header-logo" width="20" src="<?php echo esc_url( plugins_url( 'assets/images/logo-wpconnect-icon-white.svg', __DIR__ ) ); ?>"/>
			<span><?php esc_html_e( 'Air WP Sync', 'air-wp-sync' ); ?></span>
			<span class="airwpsync-admin-header-pro-tag">Pro+</span>
		</a>
	</h2>

	<a class="airwpsync-admin-header-wpco" href="https://wpconnect.co/" target="_blank">
		<img width="105" height="25" src="<?php echo esc_url( plugins_url( 'assets/images/logo-wpconnect-v2.png', __DIR__ ) ); ?>"/>
	</a>
</div>
	<?php
};
