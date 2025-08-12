<?php
/**
 * Display the destination options: post type, post properties (status, author...).
 *
 * @package Air_WP_Sync_Pro
 */

/**
 * Metabox post settings view.
 *
 * @param array $post_types Post types.
 * @param array $post_authors Post authors.
 * @param array $post_stati Post stati.
 */
return function ( $post_types, $post_authors, $post_stati ) {
	?>

	<table class="form-table">
		<tr valign="top">
			<th scope="row">
				<label for="post_type"><?php esc_html_e( 'Post Type', 'air-wp-sync' ); ?></label>
			</th>
			<td>
				<select class="regular-text ltr" name="airwpsync::post_type" x-model="config.post_type" x-init="config.post_type = config.post_type || $el.value;" @change="updateWordPressOptions();">
					<?php foreach ( $post_types as $post_type ) : ?>
						<option value="<?php echo esc_attr( $post_type['value'] ); ?>" <?php echo ! $post_type['enabled'] ? 'disabled="disabled"' : ''; ?>><?php echo esc_html( $post_type['label'] ); ?></option>
					<?php endforeach; ?>
				</select>

				<template x-if="config.post_type === 'custom'">
					<div class="airwpsync-field-subgroup">
						<div class="airwpsync-field">
							<label for="post_type_name">
								<span><?php esc_html_e( 'Name', 'air-wp-sync' ); ?></span>
								<span class="airwpsync-required" aria-hidden="true">*</span>
								<span class="screen-reader-text"><?php esc_html_e( ' (required)', 'air-wp-sync' ); ?></span>
							</label>
							<input
									x-model="config.post_type_name"
									type="text"
									name="post_type_name"
									class="regular-text ltr"
									:class="{'airwpsync-field--invalid': hasErrors('post_type_name')}"
									data-rules='["required"]'
									@change="updateWordPressOptions();"
							>
							<template x-for="message in getErrorMessages('post_type_name')">
								<p class="airwpsync-validation-message" x-text="message"></p>
							</template>
							<p class="description"><?php esc_html_e( 'The name of your Custom Post Type.', 'air-wp-sync' ); ?></p>
						</div>
						<div class="'airwpsync-field">
							<label for="post_type_slug">
								<span><?php esc_html_e( 'Url Prefix', 'air-wp-sync' ); ?></span>
								<span class="airwpsync-required" aria-hidden="true">*</span>
								<span class="screen-reader-text"><?php esc_html_e( ' (required)', 'air-wp-sync' ); ?></span>
							</label>
							<input
									x-model="config.post_type_slug"
									type="text"
									name="post_type_slug"
									class="regular-text ltr"
									:class="{'airwpsync-field--invalid': hasErrors('post_type_slug')}"
									data-rules='["required", "slug", "allowedCptSlug", "slugLength"]'
							>
							<template x-for="message in getErrorMessages('post_type_slug')">
								<p class="airwpsync-validation-message" x-text="message"></p>
							</template>
							<p class="description">
								<?php
								wp_kses(
									/* translators: %s = home url */
									sprintf( __( 'The prefix used in the URL structure as in <code>%s/<b>prefix/</b>post-name/</code>.', 'air-wp-sync' ), home_url() ),
									array(
										'code' => array(),
										'b'    => array(),
									)
								);
								?>
							</p>
						</div>
					</div>
				</template>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="post_status"><?php esc_html_e( 'Default Post Status', 'air-wp-sync' ); ?></label>
			</th>
			<td>
				<select class="regular-text ltr" name="airwpsync::post_status" x-model="config.post_status" x-init="config.post_status = config.post_status || $el.value;">
					<?php foreach ( $post_stati as $post_status ) : ?>
						<option value="<?php echo esc_attr( $post_status['value'] ); ?>" <?php echo ! $post_status['enabled'] ? 'disabled="disabled"' : ''; ?>><?php echo esc_html( $post_status['label'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="post_author"><?php esc_html_e( 'Default Post Author', 'air-wp-sync' ); ?></label>
			</th>
			<td>
				<select class="regular-text ltr" name="airwpsync::post_author" x-model="config.post_author" x-init="config.post_author = config.post_author || $el.value;">
					<?php foreach ( $post_authors as $post_author ) : ?>
						<option value="<?php echo esc_attr( $post_author['value'] ); ?>" <?php echo ! $post_author['enabled'] ? 'disabled="disabled"' : ''; ?>><?php echo esc_html( $post_author['label'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
	</table>
	<?php
};
