<?php

namespace Air_WP_Sync_Free;

require_once AIR_WP_SYNC_PLUGIN_DIR . 'includes/admin/class-air-wp-sync-admin-connections-list.php';
require_once AIR_WP_SYNC_PLUGIN_DIR . 'includes/admin/class-air-wp-sync-admin-connection.php';
require_once AIR_WP_SYNC_PLUGIN_DIR . 'includes/admin/metaboxes/class-air-wp-sync-metabox-field-mapping.php';
require_once AIR_WP_SYNC_PLUGIN_DIR . 'includes/admin/metaboxes/class-air-wp-sync-metabox-global-settings.php';
require_once AIR_WP_SYNC_PLUGIN_DIR . 'includes/admin/metaboxes/class-air-wp-sync-metabox-importer-settings.php';
require_once AIR_WP_SYNC_PLUGIN_DIR . 'includes/admin/metaboxes/class-air-wp-sync-metabox-sync-settings.php';
require_once AIR_WP_SYNC_PLUGIN_DIR . 'includes/admin/metaboxes/class-air-wp-sync-metabox-import-infos.php';

/**
 * Admin
 */
class Air_WP_Sync_Admin {
	/**
	 * Constructor
	 */
	public function __construct() {

		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'in_admin_header', array( $this, 'in_admin_header' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_styles_scripts' ) );
		add_action( 'admin_notices', array( $this, 'add_notices' ) );

		add_filter( 'plugin_action_links_' . AIR_WP_SYNC_BASENAME, array( $this, 'plugin_action_links' ) );

		new Air_WP_Sync_Admin_Connections_List();
		new Air_WP_Sync_Admin_Connection();
	}

	/**
	 * Add menu
	 */
	public function add_menu() {
		add_menu_page(
			__( 'Air WP Sync', 'air-wp-sync' ),
			__( 'Air WP Sync', 'air-wp-sync' ),
			apply_filters( 'airwpsync/manage_options_capability', 'manage_options' ),
			'edit.php?post_type=airwpsync-connection',
			false,
			'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48c3ZnIGlkPSJ1dWlkLTQ5NzQxOTJhLTZkYWMtNDUwNy1hN2JjLTA0YTUxMmU4Njk1MyIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB2aWV3Qm94PSIwIDAgMjA4Ljk3IDE4Mi40OSI+PGRlZnM+PHN0eWxlPi51dWlkLTM4NDE5Y2Y0LWZkYWUtNDNiYi1hZmYwLTJiNzk0NzQzODkxZXtmaWxsOiNmZmY7fTwvc3R5bGU+PC9kZWZzPjxnIGlkPSJ1dWlkLThhZTk0YWUyLTRiY2UtNDQ5YS05MjhiLThhYTNlYmU5Mjg1MSI+PHBhdGggY2xhc3M9InV1aWQtMzg0MTljZjQtZmRhZS00M2JiLWFmZjAtMmI3OTQ3NDM4OTFlIiBkPSJtMTY4LjExLDE0LjY1bDI0Ljk4LDQzLjI3YzEuMTYsMi4wMSwxLjE2LDQuNDksMCw2LjVsLTI0Ljk4LDQzLjI3Yy0xLjE2LDIuMDEtMy4zMSwzLjI1LTUuNjMsMy4yNWgtNDkuOTZjLTIuMzIsMC00LjQ3LTEuMjQtNS42My0zLjI1bC0xNS42LTI3LjAyYy0xLjE2LTIuMDEtMy4zMS0zLjI1LTUuNjQtMy4yNWgwYy01LDAtOC4xMSw1LjQyLTUuNjEsOS43NWwxOS4zNSwzMy41MmMxLjE2LDIuMDEsMy4zMSwzLjI1LDUuNjMsMy4yNWg2NC45N2MyLjMyLDAsNC40Ny0xLjI0LDUuNjMtMy4yNWwzMi40OC01Ni4yNmMxLjE2LTIuMDEsMS4xNi00LjQ5LDAtNi41bC0yOS41NC01MS4xNi0xMC40NSw3Ljg5WiIvPjxwYXRoIGNsYXNzPSJ1dWlkLTM4NDE5Y2Y0LWZkYWUtNDNiYi1hZmYwLTJiNzk0NzQzODkxZSIgZD0ibTE2OS43OSw0OC4xMmMtMy4zMywwLTYuMDMtMi43LTYuMDMtNi4wM1Y2Ljc2YzAtMy4zMywyLjctNi4wMyw2LjAzLTYuMDNzNi4wMywyLjcsNi4wMyw2LjAzdjM1LjMzYzAsMy4zMy0yLjcsNi4wMy02LjAzLDYuMDNaIi8+PHBhdGggY2xhc3M9InV1aWQtMzg0MTljZjQtZmRhZS00M2JiLWFmZjAtMmI3OTQ3NDM4OTFlIiBkPSJtMjAyLjAxLDI3LjI2Yy0uODMsMC0xLjY3LS4xNy0yLjQ3LS41M2wtMzIuMjMtMTQuNDdjLTMuMDQtMS4zNy00LjQtNC45NC0zLjAzLTcuOTgsMS4zNy0zLjA0LDQuOTQtNC40LDcuOTgtMy4wM2wzMi4yMywxNC40N2MzLjA0LDEuMzcsNC40LDQuOTQsMy4wMyw3Ljk4LTEsMi4yNC0zLjIsMy41Ni01LjUxLDMuNTZaIi8+PHBhdGggY2xhc3M9InV1aWQtMzg0MTljZjQtZmRhZS00M2JiLWFmZjAtMmI3OTQ3NDM4OTFlIiBkPSJtNzkuMTYsNTQuNjhjLTEuMTIsMC0yLjI2LS4zNC0zLjI2LTEuMDUtMi40My0xLjc0LTIuOTgtNS4xMi0xLjQtNy42NUwxMDEuNTgsMi42M0MxMDIuNjEsMSwxMDQuNCwwLDEwNi4zMywwaDQ5LjI2YzMuMDIsMCw1LjYxLDIuMzIsNS43Niw1LjMzLjE1LDMuMjItMi40MSw1Ljg3LTUuNiw1Ljg3aC00My4yMWMtMS45MywwLTMuNzMsMS00Ljc1LDIuNjNsLTIzLjg4LDM4LjJjLTEuMDYsMS43LTIuODksMi42My00Ljc2LDIuNjNaIi8+PHBhdGggY2xhc3M9InV1aWQtMzg0MTljZjQtZmRhZS00M2JiLWFmZjAtMmI3OTQ3NDM4OTFlIiBkPSJtNDAuODYsMTY3Ljg0bC0yNC45OC00My4yN2MtMS4xNi0yLjAxLTEuMTYtNC40OSwwLTYuNWwyNC45OC00My4yN2MxLjE2LTIuMDEsMy4zMS0zLjI1LDUuNjMtMy4yNWg0OS45NmMyLjMyLDAsNC40NywxLjI0LDUuNjMsMy4yNWwxNS42LDI3LjAyYzEuMTYsMi4wMSwzLjMxLDMuMjUsNS42NCwzLjI1aDBjNSwwLDguMTEtNS40Miw1LjYxLTkuNzVsLTE5LjM1LTMzLjUyYy0xLjE2LTIuMDEtMy4zMS0zLjI1LTUuNjMtMy4yNUgzOC45OGMtMi4zMiwwLTQuNDcsMS4yNC01LjYzLDMuMjVMLjg3LDExOC4wOGMtMS4xNiwyLjAxLTEuMTYsNC40OSwwLDYuNWwyOS41NCw1MS4xNiwxMC40NS03Ljg5WiIvPjxwYXRoIGNsYXNzPSJ1dWlkLTM4NDE5Y2Y0LWZkYWUtNDNiYi1hZmYwLTJiNzk0NzQzODkxZSIgZD0ibTM5LjE5LDEzNC4zN2MzLjMzLDAsNi4wMywyLjcsNi4wMyw2LjAzdjM1LjMzYzAsMy4zMy0yLjcsNi4wMy02LjAzLDYuMDNzLTYuMDMtMi43LTYuMDMtNi4wM3YtMzUuMzNjMC0zLjMzLDIuNy02LjAzLDYuMDMtNi4wM1oiLz48cGF0aCBjbGFzcz0idXVpZC0zODQxOWNmNC1mZGFlLTQzYmItYWZmMC0yYjc5NDc0Mzg5MWUiIGQ9Im02Ljk2LDE1NS4yM2MuODMsMCwxLjY3LjE3LDIuNDcuNTNsMzIuMjMsMTQuNDdjMy4wNCwxLjM3LDQuNCw0Ljk0LDMuMDMsNy45OC0xLjM3LDMuMDQtNC45NCw0LjQtNy45OCwzLjAzbC0zMi4yMy0xNC40N2MtMy4wNC0xLjM3LTQuNC00Ljk0LTMuMDMtNy45OCwxLTIuMjQsMy4yLTMuNTYsNS41MS0zLjU2WiIvPjxwYXRoIGNsYXNzPSJ1dWlkLTM4NDE5Y2Y0LWZkYWUtNDNiYi1hZmYwLTJiNzk0NzQzODkxZSIgZD0ibTEyOS44MiwxMjcuODJjMS4xMiwwLDIuMjYuMzQsMy4yNiwxLjA1LDIuNDMsMS43NCwyLjk4LDUuMTIsMS40LDcuNjVsLTI3LjA5LDQzLjM0Yy0xLjAyLDEuNjQtMi44MiwyLjYzLTQuNzUsMi42M2gtNDkuMjZjLTMuMDIsMC01LjYxLTIuMzItNS43Ni01LjMzLS4xNS0zLjIyLDIuNDEtNS44Nyw1LjYtNS44N2g0My4yMWMxLjkzLDAsMy43My0xLDQuNzUtMi42M2wyMy44OC0zOC4yYzEuMDYtMS43LDIuODktMi42Myw0Ljc2LTIuNjNaIi8+PC9nPjwvc3ZnPg=='
		);
		add_submenu_page(
			'edit.php?post_type=airwpsync-connection',
			__( 'All Connections', 'air-wp-sync' ),
			__( 'All Connections', 'air-wp-sync' ),
			apply_filters( 'airwpsync/manage_options_capability', 'manage_options' ),
			'edit.php?post_type=airwpsync-connection'
		);
		add_submenu_page(
			'edit.php?post_type=airwpsync-connection',
			__( 'Add New', 'air-wp-sync' ),
			__( 'Add New', 'air-wp-sync' ),
			apply_filters( 'airwpsync/manage_options_capability', 'manage_options' ),
			'post-new.php?post_type=airwpsync-connection'
		);
	}

	/**
	 * Display plugin header
	 */
	public function in_admin_header() {
		$screen = get_current_screen();
		if ( 'airwpsync-connection' === $screen->post_type ) {
			include_once AIR_WP_SYNC_PLUGIN_DIR . 'views/header.php';
		}
	}

	/**
	 * Register admin styles and scripts
	 */
	public function register_styles_scripts() {
		wp_enqueue_style( 'air-wp-sync-admin', plugins_url( 'assets/css/admin-page.css', AIR_WP_SYNC_PLUGIN_FILE ), false, AIR_WP_SYNC_VERSION );
	}

	/**
	 * Show action links on the plugin screen
	 */
	public function plugin_action_links( $links ) {
		return array_merge(
			$links,
			array(
				'upgrade' => '<a href="https://wpconnect.co/air-wp-sync-plugin/" target="_blank"><b>' . esc_html__( 'Upgrade to pro+ version', 'air-wp-sync' ) . '</b></a>',
			)
		);
	}

	/**
	 * Add admin notices
	 */
	public function add_notices() {
		// Add notice if some importers have deprecated api keys
		$deprecated_key_importers = array_filter(
			Air_WP_Sync_Helper::get_importers(),
			function ( $importer ) {
				return strpos( $importer->config()->get( 'api_key' ), 'key' ) === 0;
			}
		);

		if ( $deprecated_key_importers ) {
			$list = array_map(
				function ( $importer ) {
					return '<a href="' . get_edit_post_link( $importer->infos()->get( 'id' ) ) . '">' . get_the_title( $importer->infos()->get( 'id' ) ) . '</a>';
				},
				$deprecated_key_importers
			);
			$list = implode( ', ', $list );
			/* translators: %s = list of connections using deprecated API keys */
			$message = sprintf( __( '<strong>Air WP Sync:</strong> The following connections use API Keys that will be deprecated. To benefit from all the features of our plugin in a more secure way, please use a personal access token instead: %s', 'air-wp-sync' ), $list );
			echo wp_kses(
				"<div class='notice notice-warning'><p>{$message}</p></div>",
				array(
					'div'    => array(
						'class' => array(),
					),
					'p'      => array(),
					'strong' => array(),
				)
			);
		}
	}
}
