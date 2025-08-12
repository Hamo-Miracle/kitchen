<?php
// no direct access
if ( ! defined('ABSPATH') ) {
	die('');
}
?>

<?php if (
	$this->settings['admin']['top_line'] == true
	&& ! $this->settings['admin']['send_from_user']
) : ?>
	<div class="block">
		<!-- Start of preheader -->
		<table width="100%" bgcolor="<?php echo $data['style']['t_color'] ?>" cellpadding="0" cellspacing="0" border="0" class="backgroundTable" st-sortable="preheader">
			<tbody>
				<tr>
					<td width="100%">
						<table width="960" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
							<tbody>
								<!-- Spacing -->
								<tr>
									<td width="100%" height="5"></td>
								</tr>
								<!-- Spacing -->
								<tr>
									<td align="right" valign="middle" style="font-family: Helvetica, arial, sans-serif; font-size: 10px;color: <?php echo $data['style']['color']; ?>" st-content="preheader">
										<?php printf(
											__( 'To see the full submission, please <a class="hlite" style="text-decoration: none; color: %2$s" href="%1$s">click here</a> (May require administrative access).', 'ipt_fsqm' ),
											admin_url( 'admin.php?page=ipt_fsqm_view_submission&id=' . $this->data_id ), $data['style']['accent_bg']
										); ?>
									</td>
								</tr>
								<!-- Spacing -->
								<tr>
									<td width="100%" height="5"></td>
								</tr>
								<!-- Spacing -->
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
		<!-- End of preheader -->
	</div>
<?php endif; ?>

<div class="block">
	<!-- start of header -->
	<table width="100%" bgcolor="<?php echo $data['style']['t_color'] ?>" cellpadding="0" cellspacing="0" border="0" class="backgroundTable" st-sortable="header">
		<tbody>
			<tr>
				<td>
					<table width="960" bgcolor="<?php echo $data['style']['accent_bg']; ?>" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth" hlitebg="edit" shadow="edit">
						<tbody>
							<tr>
								<td>
									<?php if ( '' != $this->settings['admin']['email_logo'] ) : ?>
										<!-- logo -->
										<table width="450" cellpadding="0" cellspacing="0" border="0" align="left" class="devicewidth">
											<tbody>
												<tr>
												<td valign="middle" width="270" style="padding: 10px 0 10px 20px;" class="logo">
													<div class="imgpop">
														<a href="<?php echo  $this->get_trackback_url(); ?>"><img src="<?php echo esc_attr( $this->settings['admin']['email_logo'] ); ?>" alt="logo" border="0" style="display:block; border:none; outline:none; text-decoration:none;" st-image="edit" class="logo"></a>
													</div>
												</td>
												</tr>
											</tbody>
										</table>
										<!-- End of logo -->
									<?php endif; ?>
									<!-- menu -->
									<table width="450" cellpadding="0" cellspacing="0" border="0" align="right" class="devicewidth">
										<tbody>
											<tr>
												<td width="450" valign="middle" style="font-family: Helvetica, Arial, sans-serif;font-size: 14px; color: <?php echo $data['style']['accent_color']; ?>;line-height: 24px; padding: 10px 0;" align="right" class="menu" st-content="menu">
													<?php echo get_bloginfo( 'name' ); ?>
												</td>
												<td width="20"></td>
											</tr>
										</tbody>
									</table>
									<!-- End of Menu -->
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>
	<!-- end of header -->
</div>

<div class="block">
	<!-- image + text -->
	<table width="100%" bgcolor="<?php echo $data['style']['t_color'] ?>" cellpadding="0" cellspacing="0" border="0" class="backgroundTable" st-sortable="bigimage">
		<tbody>
			<tr>
				<td>
					<table bgcolor="#ffffff" width="960" align="center" cellspacing="0" cellpadding="0" border="0" class="devicewidth" modulebg="edit">
						<tbody>
							<tr>
								<td width="100%" height="20"></td>
							</tr>
							<tr>
								<td>
									<table width="920" align="center" cellspacing="0" cellpadding="0" border="0" class="devicewidthinner">
										<tbody>
											<?php if ( $summary_header == true ) : ?>
												<?php if ( '' != $this->settings['theme']['logo'] ) : ?>
													<tr>
														<!-- start of image -->
														<td align="center">
															<img width="540" border="0" alt="" style="display:block; border:none; outline:none; text-decoration:none;" src="<?php echo $this->settings['theme']['logo']; ?>" class="bigimage" />
														</td>
														<!-- end of image -->
													</tr>
													<!-- Spacing -->
													<tr>
														<td width="100%" height="20"></td>
													</tr>
													<!-- Spacing -->
												<?php endif; ?>
												<!-- title -->
												<tr>
													<td style="font-family: Helvetica, arial, sans-serif; font-size: 18px; color: <?php echo $data['style']['h_color']; ?>; text-align:left;line-height: 20px;" st-title="rightimage-title">
														<?php echo $this->name; ?>
													</td>
												</tr>
												<!-- end of title -->
												<!-- Spacing -->
												<tr>
													<td width="100%" height="20"></td>
												</tr>
												<!-- Spacing -->
											<?php endif; ?>
											<!-- content -->
											<tr>
												<td style="font-family: Helvetica, arial, sans-serif; font-size: 13px; color: <?php echo $data['style']['m_color']; ?>; text-align:left;line-height: 24px;" st-content="rightimage-paragraph">
													<?php echo str_replace( array_keys( $format_string_components ), array_values( $format_string_components ),  wptexturize( wpautop( $msgs ) ) ); ?>
												</td>
											</tr>
											<!-- end of content -->
											<!-- Spacing -->
											<tr>
												<td width="100%" height="20"></td>
											</tr>
											<!-- Spacing -->
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<?php if ( $show_submission == true ) : ?>
	<div class="block">
		<table width="100%" bgcolor="<?php echo $data['style']['t_color'] ?>" cellpadding="0" cellspacing="0" border="0" class="backgroundTable" st-sortable="bigimage">
			<tbody>
				<tr>
					<td>
						<table bgcolor="#ffffff" width="960" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth" modulebg="edit">
							<tbody>
								<!-- Spacing -->
								<tr>
									<td width="100%" height="20"></td>
								</tr>
								<!-- Spacing -->
								<tr>
									<td>
										<?php $this->show_quick_preview( true, false, false, true ); ?>
									</td>
								</tr>
								<!-- Spacing -->
								<tr>
									<td width="100%" height="20"></td>
								</tr>
								<!-- Spacing -->
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
<?php endif; ?>

<div class="block">
	<!-- Start of preheader -->
	<table width="100%" bgcolor="<?php echo $data['style']['t_color'] ?>" cellpadding="0" cellspacing="0" border="0" class="backgroundTable" st-sortable="postfooter">
		<tbody>
			<tr>
				<td width="100%">
					<table width="960" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
						<tbody>
							<!-- Spacing -->
							<tr>
								<td width="100%" height="5"></td>
							</tr>
							<!-- Spacing -->
							<tr>
								<td align="center" valign="middle" style="font-family: Helvetica, arial, sans-serif; font-size: 10px;color: <?php echo $data['style']['color']; ?>" st-content="preheader">
									<?php echo $this->settings['admin']['footer']; ?>
								</td>
							</tr>
							<!-- Spacing -->
							<tr>
								<td width="100%" height="5"></td>
							</tr>
							<!-- Spacing -->
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>
	<!-- End of preheader -->
</div>
