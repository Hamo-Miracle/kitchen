<?php
/**
 * Display the sync strategy options: manual, recurring...
 *
 * @package Air_WP_Sync_Pro
 */

/**
 * Metabox sync view.
 *
 * @param array $sync_strategies Sync strategies (manual, recurring...)
 * @param array $schedules Frequency schedules
 * @param string|boolean $webhook_url The webhook URL or false if the connection has not been saved yet.
 */
return function ( $sync_strategies, $schedules, $webhook_url ) {
	?>
<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<label for="sync_strategy">
				<span><?php esc_html_e( 'Strategy', 'air-wp-sync' ); ?></span>
				<span class="airwpsync-tooltip" aria-label="<?php echo esc_attr__( 'Select the method to synchronize your Airtable content with WordPress.<br><br><strong>Add:</strong> only adds new content.<br><br><strong>Add & Update:</strong> same + updates content from modified records.<br><br><strong>Add, Update & Delete:</strong> same + deletes content that is no longer in Airtable.', 'air-wp-sync' ); ?>">?</span>
			</label>
		</th>
		<td>
			<select class="regular-text ltr" name="airwpsync::sync_strategy" x-model="config.sync_strategy" x-init="config.sync_strategy = config.sync_strategy || $el.value;">
			<?php foreach ( $sync_strategies as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="sync_type"><?php esc_html_e( 'Trigger', 'air-wp-sync' ); ?></label>
		</th>
		<td>
			<fieldset class="airwpsync-radiogroup">
				<label>
					<input name="airwpsync::scheduled_sync::type" type="radio" value="manual" x-model="config.scheduled_sync.type" />
					<span><?php esc_html_e( 'Manual only', 'air-wp-sync' ); ?></span>
					<span class="airwpsync-tooltip" aria-label="<?php echo esc_attr__( 'Disables automatic synchronization of your Airtable content. You can still do it manually by clicking on the \'Sync Now\' button in the side panel.', 'air-wp-sync' ); ?>">?</span>
				</label>

				<label>
					<input name="airwpsync::scheduled_sync::type" type="radio" value="cron" x-model="config.scheduled_sync.type" />
					<span><?php esc_html_e( 'Recurring', 'air-wp-sync' ); ?></span>
					<span class="airwpsync-tooltip" aria-label="<?php echo esc_attr__( 'Enables recurring synchronization of your Airtable content. Choose a frequency and it will take effect from the date and time the connection is updated.', 'air-wp-sync' ); ?>">?</span>
				</label>
				<div class="airwpsync-field" x-show="config.scheduled_sync.type === 'cron'">
					<label for="recurrence"><?php esc_html_e( 'Frequency', 'air-wp-sync' ); ?></label>
					<select class="regular-text ltr" name="airwpsync::scheduled_sync::recurrence" type="text" x-model="config.scheduled_sync.recurrence" x-init="config.scheduled_sync.recurrence = config.scheduled_sync.recurrence || $el.value;">
						<?php foreach ( $schedules as $schedule ) : ?>
							<option value="<?php echo esc_attr( $schedule['value'] ); ?>" <?php echo ! $schedule['enabled'] ? 'disabled="disabled"' : ''; ?>><?php echo esc_html( $schedule['label'] ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description">
					<?php
					echo wp_kses(
						__( 'We recommend <a href="https://wpconnect.co/blog/setup-cron-wordpress" target="_blank">setting up WP-Cron</a> as a cron job for better performance.', 'air-wp-sync' ),
						array(
							'a' => array(
								'href'   => true,
								'target' => true,
							),
						)
					);
					?>
					</p>
				</div>
				<div class="airwpsync-field-group airwpsync-field-group-inline" x-show="config.scheduled_sync.type === 'cron'">
					<div class="airwpsync-field" x-show="config.scheduled_sync.type === 'cron' && config.scheduled_sync.recurrence === 'weekly'">
						<label for="airwpsync::scheduled_sync::weekday"><?php esc_html_e( 'Day of week', 'air-wp-sync' ); ?></label>
						<select class="regular-text ltr" name="airwpsync::scheduled_sync::weekday" type="text" x-model="config.scheduled_sync.weekday" x-init="config.scheduled_sync.weekday = config.scheduled_sync.weekday || $el.value;">
							<option value=""></option>
							<option value="monday"><?php echo esc_html__( 'Monday', 'air-wp-sync' ); ?></option>
							<option value="tuesday"><?php echo esc_html__( 'Tuesday', 'air-wp-sync' ); ?></option>
							<option value="wednesday"><?php echo esc_html__( 'Wednesday', 'air-wp-sync' ); ?></option>
							<option value="thursday"><?php echo esc_html__( 'Thursday', 'air-wp-sync' ); ?></option>
							<option value="friday"><?php echo esc_html__( 'Friday', 'air-wp-sync' ); ?></option>
							<option value="saturday"><?php echo esc_html__( 'Saturday', 'air-wp-sync' ); ?></option>
							<option value="sunday"><?php echo esc_html__( 'Sunday', 'air-wp-sync' ); ?></option>
						</select>
					</div>
					<div class="airwpsync-field" x-show="config.scheduled_sync.type === 'cron' && Array('weekly', 'daily').indexOf(config.scheduled_sync.recurrence) > -1">
						<label for="airwpsync::scheduled_sync::time"><?php esc_html_e( 'Time', 'air-wp-sync' ); ?></label>
						<input type="time" name="airwpsync::scheduled_sync::time" x-model="config.scheduled_sync.time"/>
					</div>
				</div>

				<label>
					<input name="airwpsync::scheduled_sync::type" type="radio" value="instant" x-model="config.scheduled_sync.type" />
					<span><?php esc_html_e( 'Instant via Webhook', 'air-wp-sync' ); ?></span>
					<span class="airwpsync-tooltip" aria-label="<?php echo esc_attr__( 'Enables instant synchronization of your content. Using an Airtable automation, you can choose your own trigger and the connection will be updated as soon as the webhook url below is called.', 'air-wp-sync' ); ?>">?</span>
				</label>
				<div class="airwpsync-field" x-show="config.scheduled_sync.type === 'instant'">
					<?php if ( $webhook_url ) : ?>
						<label for="webhook_url"><?php esc_html_e( 'Webhook URL', 'air-wp-sync' ); ?></label>
						<input class="regular-text code" type="url" name="webhook_url" value="<?php echo esc_attr( $webhook_url ); ?>"/>
						<p class="description">
						<?php
						echo wp_kses(
							__( 'To create an automation and call a webhook, <a href="https://wpconnect.co/blog/create-airtable-automation" target="_blank">click here</a>', 'air-wp-sync' ),
							array(
								'a' => array(
									'href'   => true,
									'target' => true,
								),
							)
						);
						?>
												</p>
					<?php else : ?>
						<p><?php esc_html_e( 'Webhook URL will be available once you have saved this connection.', 'air-wp-sync' ); ?></p>
					<?php endif; ?>
				</div>
			</fieldset>
		</td>
	</tr>
</table>
	<?php
};
