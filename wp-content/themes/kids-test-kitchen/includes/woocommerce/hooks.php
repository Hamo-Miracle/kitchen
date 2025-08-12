<?php
/**
 * Add custom tracking code to the thank-you page
 */
add_action( 'woocommerce_thankyou', 'my_custom_tracking' );
function my_custom_tracking( $order_id ) {
	$order = wc_get_order( $order_id );
	?>
	<script>
		fbq('track', 'Purchase', {value: <?php echo $order->get_total(); ?>, currency: 'USD'});
	</script>
	<?php
}