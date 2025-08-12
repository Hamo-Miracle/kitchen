<?php
// no direct access
if ( ! defined('ABSPATH') ) {
	die('');
}
?>
<?php if ( $this->settings['user']['top_line'] == true ) : ?>
	<div class="block">
		<!-- Start of preheader -->
		<table width="100%" bgcolor="<?php echo $data['style']['t_color'] ?>" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="preheader">
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
								<td align="right" valign="middle" style="font-family: Helvetica, arial, sans-serif; font-size: 10px;color: <?php echo $data['style']['color']; ?>;" st-content="preheader">
									<?php printf( __( 'If you can not read this email, please <a class="hlite" style="text-decoration: none; color: %2$s" href="%1$s">click here</a>.', 'ipt_fsqm' ), $this->get_payment_email_url( $payment_info ), $data['style']['accent_bg'] ); ?>
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
	<table width="100%" bgcolor="<?php echo $data['style']['t_color'] ?>" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="header">
		<tbody>
			<tr>
				<td>
					<table width="960" bgcolor="<?php echo $data['style']['accent_bg']; ?>" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth" hlitebg="edit" shadow="edit">
						<tbody>
							<tr>
								<td>
									<?php if ( '' != $this->settings['user']['email_logo'] ) : ?>
									<!-- logo -->
									<table width="450" cellpadding="0" cellspacing="0" border="0" align="left" class="devicewidth">
										<tbody>
											<tr>
												<td valign="middle" width="270" style="padding: 10px 0 10px 20px;" class="logo">
													<div class="imgpop">
														<a href="<?php echo  $this->get_trackback_url(); ?>"><img src="<?php echo esc_attr( $this->settings['user']['email_logo'] ); ?>" alt="logo" border="0" style="display:block; border:none; outline:none; text-decoration:none;" st-image="edit" class="logo"></a>
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
	<table width="100%" bgcolor="<?php echo $data['style']['t_color'] ?>" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="bigimage">
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
											<?php if ( '' != $this->settings['theme']['logo'] ) : ?>
											<tr>
												<!-- start of image -->
												<td align="center">
													<img width="540" border="0" alt="" style="display:block; border:none; outline:none; text-decoration:none;" src="<?php echo $this->settings['theme']['logo']; ?>" class="bigimage" />
												</td>
											</tr>
											<!-- end of image -->
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
											<!-- content -->
											<tr>
												<td style="font-family: Helvetica, arial, sans-serif; font-size: 13px; color: <?php echo $data['style']['m_color']; ?>; text-align:left;line-height: 24px;" st-content="rightimage-paragraph">
													<?php
													if ( $custom_msg == '' ) {
														echo wptexturize( wpautop( $this->settings['payment']['retry_uemail_msg'] ) );
													} else {
														echo wptexturize( wpautop( $custom_msg ) );
													}
													?>
												</td>
											</tr>
											<!-- end of content -->
											<?php if ( $this->settings['user']['view_online'] == true ) : ?>
											<!-- Spacing -->
											<tr>
												<td width="100%" height="10"></td>
											</tr>
											<!-- button -->
											<tr>
												<td>
													<table height="30" align="left" valign="middle" border="0" cellpadding="0" cellspacing="0" class="tablet-button" st-button="edit">
														<tbody>
														<tr>
															<td width="auto" align="center" valign="middle" height="30" style=" background-color:<?php echo $data['style']['accent_bg']; ?>; border-top-left-radius:4px; border-bottom-left-radius:4px;border-top-right-radius:4px; border-bottom-right-radius:4px; background-clip: padding-box;font-size:13px; font-family:Helvetica, arial, sans-serif; text-align:center;  color: <?php echo $data['style']['accent_color']; ?>; font-weight: 300; padding-left:18px; padding-right:18px;">

																<span style="color: #ffffff; font-weight: 300;">
																	<a style="color: #ffffff; text-align:center;text-decoration: none;" href="<?php echo $this->get_trackback_url(); ?>"><?php echo $this->settings['user']['view_online_text']; ?></a>
																</span>
															</td>
														</tr>
														</tbody>
													</table>
												</td>
											</tr>
											<!-- /button -->
											<?php endif; ?>
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

<div class="block">
	<table width="100%" bgcolor="<?php echo $data['style']['t_color'] ?>" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="bigimage">
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
									<table width="920" align="center" cellspacing="0" cellpadding="0" border="0" class="devicewidthinner">
										<tbody>
											<tr>
												<td>
													<?php $this->get_transaction_status( false, true ); ?>
												</td>
											</tr>
										</tbody>
									</table>
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

<div class="block">
	<!-- Start of preheader -->
	<table width="100%" bgcolor="<?php echo $data['style']['t_color'] ?>" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="postfooter">
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
							<?php echo $this->settings['user']['footer_msg']; ?>
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
