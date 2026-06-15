<?php
namespace Immens_MCP_Fortress;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/class-activator.php';
require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/class-deactivator.php';

class Plugin {

	const CLEANUP_MAX_ITERATIONS = 20;

	private static $instance = null;
	private $server;
	private $tool_registry;
	private $resource_registry;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'register_assets' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		add_action( 'init', array( $this, 'handle_well_known' ), 0 );
		add_action( 'init', array( $this, 'handle_oauth_authorize_request' ), PHP_INT_MAX );

		add_action( 'immens_mcp_fortress_cleanup_audit_log', array( $this, 'cleanup_audit_log' ) );
		add_action( 'immens_mcp_fortress_cleanup_sessions', array( $this, 'cleanup_sessions' ) );
		add_action( 'immens_mcp_fortress_cleanup_oauth', array( $this, 'cleanup_oauth_storage' ) );

		add_action( 'plugins_loaded', array( 'Immens_MCP_Fortress\Activator', 'maybe_upgrade' ) );

		if ( is_multisite() ) {
			add_action( 'wp_initialize_site', array( $this, 'on_new_site' ), 10, 1 );
		}

		if ( is_admin() && ! wp_doing_cron() ) {
			add_action( 'init', array( $this, 'init_admin' ) );
		}
	}

	public function register_assets() {
		wp_register_style(
			'immens-mcp-fortress-consent',
			IMMENS_MCP_FORTRESS_PLUGIN_URL . 'assets/css/consent.css',
			array(),
			IMMENS_MCP_FORTRESS_VERSION
		);
	}

	private function load_mcp_includes() {
		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/mcp/class-error-codes.php';
		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/mcp/class-json-rpc.php';
		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/mcp/class-session.php';
		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/mcp/class-server.php';
		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/mcp/class-transport.php';
		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/access-points/class-access-point-schema.php';
		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/access-points/class-access-point-repository.php';
		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/access-points/class-access-point-manager.php';
		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/access-points/class-access-point-auth.php';
		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/auth/class-permission-guard.php';
		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/tools/class-base-tool.php';
		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/tools/class-tool-registry.php';
		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/resources/class-base-resource.php';
		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/resources/class-resource-registry.php';
	}

	public function init_admin() {
		$admin_lang = get_option( 'immens_mcp_fortress_admin_language', '' );
		if ( ! empty( $admin_lang ) ) {
			$safe_lang = preg_replace( '/[^a-zA-Z_]/', '', $admin_lang );
			$mo_file = IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'languages/immens-mcp-fortress-' . $safe_lang . '.mo';
			if ( file_exists( $mo_file ) ) {
				unload_textdomain( 'immens-mcp-fortress' );
				load_textdomain( 'immens-mcp-fortress', $mo_file );
			}
		}

		$this->load_mcp_includes();

		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/admin/class-admin-page.php';
		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/admin/class-access-points-page.php';
		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/admin/class-settings-page.php';
		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/admin/class-audit-log-page.php';

		new Admin\Admin_Page();
		new Admin\Access_Points_Page();
		new Admin\Settings_Page();
		new Admin\Audit_Log_Page();
	}

	public function register_rest_routes() {
		$this->load_mcp_includes();

		$this->tool_registry = new Tools\Tool_Registry();
		$this->resource_registry = new Resources\Resource_Registry();

		$access_point_manager = new Access_Points\Access_Point_Manager();

		$this->server = new MCP\Server(
			$this->tool_registry,
			$this->resource_registry,
			$access_point_manager
		);

		$this->register_tools();
		$this->register_resources();

		// Extension point: Pro add-on registers integration tools here
		do_action( 'imf_register_tools', $this->tool_registry ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		$transport = new MCP\Transport( $this->server, $access_point_manager );
		$transport->register_routes();

		// Extension point: Pro add-on registers SSE transport here
		do_action( 'imf_register_transports', $this->server, $transport ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		if ( apply_filters( 'immens_mcp_fortress_oauth_enabled', true ) ) {
			require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/oauth/class-oauth-schema.php';
			require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/oauth/class-scope-map.php';
			require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/oauth/class-discovery.php';
			require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/oauth/class-client-registry.php';
			require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/oauth/class-oauth-token-manager.php';
			require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/oauth/class-oauth-token-validator.php';
			require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/oauth/class-authorization-endpoint.php';
			require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/oauth/class-token-endpoint.php';
			require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/oauth/class-consent-screen.php';
			require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/oauth/class-oauth-routes.php';
			$oauth_routes = new OAuth\OAuth_Routes();
			$oauth_routes->register_routes();
		}
	}

	private function register_tools() {
		$tool_dirs = array(
			'posts', 'pages', 'media', 'taxonomy', 'comments',
			'users', 'site', 'menus', 'plugins', 'themes',
			'revisions', 'meta', 'search', 'blocks', 'cpt', 'templates', 'styles',
			'woocommerce', 'yoast', 'rank-math',
			'loco-translate', 'contact-form-7', 'polylang',
			'code-snippets', 'w3-total-cache',
		);

		foreach ( $tool_dirs as $dir ) {
			$tool_path = IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/tools/' . $dir . '/';
			if ( is_dir( $tool_path ) ) {
				$files = glob( $tool_path . 'class-*.php' );
				if ( $files ) {
					foreach ( $files as $file ) {
						require_once $file;
					}
				}
			}
		}

		$this->tool_registry->auto_discover();
	}

	private function register_resources() {
		$files = glob( IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/resources/class-*-resource.php' );
		if ( $files ) {
			foreach ( $files as $file ) {
				require_once $file;
			}
		}
		$this->resource_registry->auto_discover();
	}

	public function handle_well_known() {
		$request_uri_raw = isset( $_SERVER['REQUEST_URI'] )
			? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) )
			: '';

		if ( false === strpos( $request_uri_raw, '/.well-known/' ) ) {
			return;
		}

		if ( ! apply_filters( 'immens_mcp_fortress_oauth_enabled', true ) ) {
			return;
		}

		$request_uri = wp_parse_url( $request_uri_raw, PHP_URL_PATH );

		$home_path = trim( wp_parse_url( home_url(), PHP_URL_PATH ) ?? '', '/' );
		if ( $home_path ) {
			$request_uri = preg_replace( '#^/' . preg_quote( $home_path, '#' ) . '#', '', $request_uri );
		}

		$is_protected = ( '/.well-known/oauth-protected-resource' === $request_uri );
		$is_auth      = ( '/.well-known/oauth-authorization-server' === $request_uri
			|| '/.well-known/openid-configuration' === $request_uri );
		$is_opencode  = ( '/.well-known/opencode' === $request_uri );

		if ( $is_opencode ) {
			require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/mcp/class-transport.php';
			$endpoint_url = rest_url( MCP\Transport::NAMESPACE_V1 . MCP\Transport::ROUTE );
			status_header( 200 );
			header( 'Content-Type: application/json' );
			echo wp_json_encode( array(
				'mcp' => array(
					'immens-mcp-fortress' => array(
						'type' => 'remote',
						'url'  => $endpoint_url,
					),
				),
			) );
			exit;
		}

		if ( ! $is_protected && ! $is_auth ) {
			return;
		}

		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/oauth/class-scope-map.php';
		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/oauth/class-token-endpoint.php';
		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/oauth/class-discovery.php';

		$discovery = new OAuth\Discovery();
		$rest_req  = new \WP_REST_Request( 'GET' );
		$response  = $is_protected
			? $discovery->get_protected_resource_metadata( $rest_req )
			: $discovery->get_authorization_server_metadata( $rest_req );

		if ( is_wp_error( $response ) ) {
			$err_data = $response->get_error_data();
			$status   = is_array( $err_data ) && isset( $err_data['status'] ) ? (int) $err_data['status'] : 400;
			$body     = array(
				'error'             => $response->get_error_code(),
				'error_description' => $response->get_error_message(),
			);
		} elseif ( $response instanceof \WP_REST_Response ) {
			$status = $response->get_status();
			$body   = $response->get_data();
		} else {
			$status = 200;
			$body   = $response;
		}

		status_header( $status );
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Access-Control-Allow-Origin: *' );
		header( 'Cache-Control: no-store' );
		header( 'Pragma: no-cache' );
		echo wp_json_encode( $body );
		exit;
	}

	public function handle_oauth_authorize_request() {
		$oauth_param = isset( $_GET['immens_mcp_fortress_oauth'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			? sanitize_text_field( wp_unslash( $_GET['immens_mcp_fortress_oauth'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			: '';

		if ( 'authorize' !== $oauth_param ) {
			return;
		}

		if ( ! apply_filters( 'immens_mcp_fortress_oauth_enabled', true ) ) {
			return;
		}

		$this->handle_oauth_authorize();
	}

	private function handle_oauth_authorize() {
		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/oauth/class-oauth-schema.php';
		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/oauth/class-scope-map.php';
		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/oauth/class-consent-screen.php';
		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/oauth/class-client-registry.php';
		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/oauth/class-token-endpoint.php';
		require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/oauth/class-authorization-endpoint.php';

		$method  = isset( $_SERVER['REQUEST_METHOD'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
			? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) )
			: 'GET';
		$request = new \WP_REST_Request( $method );

		if ( 'POST' === $method ) {
			$request->set_param( 'client_id', sanitize_text_field( wp_unslash( $_POST['client_id'] ?? '' ) ) );
			$request->set_param( 'redirect_uri', esc_url_raw( wp_unslash( $_POST['redirect_uri'] ?? '' ) ) );
			$request->set_param( 'scope', sanitize_text_field( wp_unslash( $_POST['scope'] ?? '' ) ) );
			$request->set_param( 'state', sanitize_text_field( wp_unslash( $_POST['state'] ?? '' ) ) );
			$request->set_param( 'code_challenge', sanitize_text_field( wp_unslash( $_POST['code_challenge'] ?? '' ) ) );
			$request->set_param( 'code_challenge_method', sanitize_key( $_POST['code_challenge_method'] ?? '' ) );
			$request->set_param( '_imf_oauth_nonce', sanitize_text_field( wp_unslash( $_POST['_imf_oauth_nonce'] ?? '' ) ) );
		} else {
			$request->set_param( 'response_type', sanitize_key( $_GET['response_type'] ?? '' ) );
			$request->set_param( 'client_id', sanitize_text_field( wp_unslash( $_GET['client_id'] ?? '' ) ) );
			$request->set_param( 'redirect_uri', esc_url_raw( wp_unslash( $_GET['redirect_uri'] ?? '' ) ) );
			$request->set_param( 'scope', sanitize_text_field( wp_unslash( $_GET['scope'] ?? '' ) ) );
			$request->set_param( 'state', sanitize_text_field( wp_unslash( $_GET['state'] ?? '' ) ) );
			$request->set_param( 'code_challenge', sanitize_text_field( wp_unslash( $_GET['code_challenge'] ?? '' ) ) );
			$request->set_param( 'code_challenge_method', sanitize_key( $_GET['code_challenge_method'] ?? '' ) );
		}

		$endpoint = new OAuth\Authorization_Endpoint();
		$response = 'POST' === $method ? $endpoint->handle_post( $request ) : $endpoint->handle_get( $request );

		$this->send_authorize_response( $response );
		exit;
	}

	private function send_authorize_response( $response ) {
		if ( is_wp_error( $response ) ) {
			$status = 400;
			$data   = $response->get_error_data();
			if ( is_array( $data ) && ! empty( $data['status'] ) ) {
				$status = (int) $data['status'];
			}
			status_header( $status );
			header( 'Content-Type: text/html; charset=utf-8' );
			header( 'X-Frame-Options: DENY' );
			header( "Content-Security-Policy: frame-ancestors 'none'" );
			header( 'X-Content-Type-Options: nosniff' );
			$code    = esc_html( $response->get_error_code() );
			$message = esc_html( $response->get_error_message() );
			echo '<!DOCTYPE html><html><body><p>' . $code . ': ' . $message . '</p></body></html>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return;
		}

		status_header( $response->get_status() );
		header_remove( 'Content-Security-Policy' );

		$headers      = $response->get_headers();
		$content_type = '';
		foreach ( $headers as $name => $value ) {
			header( $name . ': ' . $value );
			if ( 0 === strcasecmp( $name, 'Content-Type' ) ) {
				$content_type = (string) $value;
			}
		}

		$data = $response->get_data();
		if ( null === $data ) {
			return;
		}

		if ( is_string( $data ) && 0 === strpos( strtolower( $content_type ), 'text/html' ) ) {
			echo $data; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return;
		}

		if ( '' === $content_type ) {
			header( 'Content-Type: application/json; charset=utf-8' );
		}
		echo wp_json_encode( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function cleanup_audit_log() {
		if ( ! wp_doing_cron() ) {
			return;
		}
		global $wpdb;
		$retention = max( 1, (int) get_option( 'immens_mcp_fortress_audit_log_retention', 30 ) );
		$i = 0;
		do {
			$table = esc_sql( $wpdb->prefix . 'immens_mcp_audit_log' );
			$deleted = $wpdb->query( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				"DELETE FROM `{$table}` WHERE created_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL %d DAY) LIMIT 500", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$retention
			) );
		} while ( $deleted > 0 && ++$i < self::CLEANUP_MAX_ITERATIONS );
	}

	public function cleanup_sessions() {
		if ( ! wp_doing_cron() ) {
			return;
		}
		global $wpdb;
		$table = esc_sql( $wpdb->prefix . 'immens_mcp_sessions' );
		$i = 0;
		do {
			$deleted = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				"DELETE FROM `{$table}` WHERE expires_at < UTC_TIMESTAMP() LIMIT 500" // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			);
		} while ( $deleted > 0 && ++$i < self::CLEANUP_MAX_ITERATIONS );
	}

	public function cleanup_oauth_storage() {
		if ( ! wp_doing_cron() ) {
			return;
		}
		global $wpdb;
		$codes_table  = $wpdb->prefix . 'immens_mcp_oauth_codes';
		$tokens_table = $wpdb->prefix . 'immens_mcp_oauth_tokens';

		$i = 0;
		do {
			$deleted = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
				"DELETE FROM `{$codes_table}` WHERE expires_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 DAY) LIMIT 500"
			);
		} while ( $deleted > 0 && ++$i < self::CLEANUP_MAX_ITERATIONS );

		$i = 0;
		do {
			$deleted = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
				"DELETE FROM `{$tokens_table}` WHERE is_active = 0 AND COALESCE(refresh_expires_at, expires_at) < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 7 DAY) LIMIT 500"
			);
		} while ( $deleted > 0 && ++$i < self::CLEANUP_MAX_ITERATIONS );
	}

	public function on_new_site( $site ) {
		switch_to_blog( $site->id );
		try {
			Activator::activate();
		} finally {
			restore_current_blog();
		}
	}
}
