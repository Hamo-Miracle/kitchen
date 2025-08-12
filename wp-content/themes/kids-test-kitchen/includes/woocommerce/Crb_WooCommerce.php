<?php

include_once( CRB_THEME_DIR . 'includes/woocommerce/wrappers.php' );

class Crb_WooCommerce {
	function __construct() {
		// In case WooCommerce plugin is not active, do nothing
		if ( ! self::is_woocommerce_active() ) {
			return;
		}

		add_action( 'admin_init', array( $this, 'admin_redirect' ), 9 );

		// Define WooCommerce Support
		add_theme_support( 'woocommerce' );
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );
	}

	/**
	 * WooCommerce Support
	 * By default all non-admin users are redirected to the my-account page
	 */
	function admin_redirect() {
		#
		if (
			Crb_Current_User()->is( 'admin' ) ||
			Crb_Current_User()->is( 'crb_session_admin' ) ||
			Crb_Current_User()->is( 'crb_facilitator' ) ||
			Crb_Current_User()->is( 'crb_assistant' )
		) {
			add_filter( 'woocommerce_prevent_admin_access', '__return_false' );
			add_filter( 'woocommerce_disable_admin_bar', '__return_false' );
		}
	}


	public static function is_woocommerce_active() {
		return class_exists( 'WooCommerce' );
	}

	public static function is_woocommerce() {
		return self::is_woocommerce_active() && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() || is_wc_endpoint_url() );
	}

	public static function get_woocommerce_links( $link = '' ) {
		$home_url = home_url( '/' );
		if ( self::is_woocommerce_active() ) {
			global $woocommerce;
			$cart_url = wc_get_cart_url();
			$cart_count = $woocommerce->cart->get_cart_contents_count();

			$myaccount_link = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) );

			$links = array(
				'my account' => $myaccount_link,
				'logout' => $home_url . '?customer-logout=true',
				'login' => $myaccount_link,
				'register' => $myaccount_link . '?customer-register=true',
				'cart' => $cart_url,
				'cart_count' => $cart_count,
			);
		} else {
			$links = array(
				'my account' => admin_url( 'profile.php' ),
				'logout' => wp_logout_url( $home_url ),
				'login' => wp_login_url( $home_url ),
				'register' => wp_registration_url(),
				'cart' => '',
				'cart_count' => 0,
			);
		}

		if ( ! empty( $link ) && ! empty( $links[$link] ) ) {
			return $links[$link];
		}

		return $links;
	}

	public static function get_woocommerce_shop_url() {
		if ( ! self::is_woocommerce_active() ) {
			return home_url( '/' );
		}

		$shop_page_url = get_permalink( wc_get_page_id( 'shop' ) );

		return $shop_page_url;
	}

}

new Crb_WooCommerce();
