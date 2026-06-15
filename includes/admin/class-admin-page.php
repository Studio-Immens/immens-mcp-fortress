<?php
namespace Immens_MCP_Fortress\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin_Page {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu_pages' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function add_menu_pages() {
		add_menu_page(
			__( 'Immens MCP Fortress', 'immens-mcp-fortress' ),
			__( 'MCP Fortress', 'immens-mcp-fortress' ),
			'manage_options',
			'immens-mcp-fortress',
			array( $this, 'render_dashboard' ),
			plugins_url( 'assets/menu-icon.png', IMMENS_MCP_FORTRESS_PLUGIN_FILE ),
			30
		);
	}

	public function enqueue_assets( $hook ) {
		if ( false === strpos( $hook, 'immens-mcp-fortress' ) ) {
			return;
		}

		wp_enqueue_style(
			'immens-mcp-fortress-admin',
			IMMENS_MCP_FORTRESS_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			IMMENS_MCP_FORTRESS_VERSION
		);

		wp_enqueue_script(
			'immens-mcp-fortress-admin',
			IMMENS_MCP_FORTRESS_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			IMMENS_MCP_FORTRESS_VERSION,
			true
		);

		wp_localize_script( 'immens-mcp-fortress-admin', 'IMF_Admin', array(
			'nonce'     => wp_create_nonce( 'immens_mcp_fortress_admin' ),
			'ajax_url'  => admin_url( 'admin-ajax.php' ),
			'confirm_delete' => __( 'Are you sure you want to delete this access point? This action cannot be undone.', 'immens-mcp-fortress' ),
			'confirm_regenerate' => __( 'Regenerating the key will invalidate the current key immediately. Continue?', 'immens-mcp-fortress' ),
		) );
	}

	public function render_dashboard() {
		$manager = new \Immens_MCP_Fortress\Access_Points\Access_Point_Manager();
		$count   = $manager->count_access_points();
		$endpoint_url = rest_url( \Immens_MCP_Fortress\MCP\Transport::NAMESPACE_V1 . \Immens_MCP_Fortress\MCP\Transport::ROUTE );

		$pro_installed = class_exists( 'Immens_MCP_Fortress_Pro_Bootstrap' );
		$pro_active    = false;
		if ( $pro_installed && class_exists( 'Immens_MCP_Fortress_Pro_License' ) ) {
			try {
				$pro_active = Immens_MCP_Fortress_Pro_License::is_active();
			} catch ( \Throwable $e ) {
				$pro_active = false;
			}
		}
		?>
		<div class="wrap imf-wrap">
			<?php if ( ! $pro_active ) :
				$notice_type = $pro_installed ? 'warning' : 'info';
				$notice_msg  = $pro_installed
					? __( 'Activate your Pro license to unlock all integration tools and features.', 'immens-mcp-fortress' )
					: __( 'Get Immens MCP Fortress Pro for Gutenberg block-level editing, Primary Source, Immens CRM, ClassyBlocks, SEO Framework, Greenshift, Stackable, TranslatePress integrations, unlimited access points, SSE transport and more.', 'immens-mcp-fortress' );
				$notice_btn  = $pro_installed
					? __( 'Activate Now →', 'immens-mcp-fortress' )
					: __( 'Learn More →', 'immens-mcp-fortress' );
				$notice_url  = $pro_installed
					? admin_url( 'admin.php?page=immens-mcp-fortress-pro-license' )
					: 'https://studioimmens.com/immens-mcp-fortress-pro';
			?>
			<div class="notice notice-<?php echo esc_attr( $notice_type ); ?> is-dismissible">
				<p>
					<strong><?php esc_html_e( 'Unlock 131 additional MCP tools!', 'immens-mcp-fortress' ); ?></strong>
					<?php echo esc_html( $notice_msg ); ?>
					<a href="<?php echo esc_url( $notice_url ); ?>" target="<?php echo $pro_installed ? '_self' : '_blank'; ?>" class="button button-primary button-small" style="margin-left: 10px;">
						<?php echo esc_html( $notice_btn ); ?>
					</a>
				</p>
			</div>
			<?php endif; ?>

			<h1><?php echo esc_html__( 'Immens MCP Fortress — Dashboard', 'immens-mcp-fortress' ); ?></h1>

			<div class="imf-dashboard-cards">
				<div class="imf-card">
					<h2><?php esc_html_e( 'MCP Endpoint', 'immens-mcp-fortress' ); ?></h2>
					<div class="imf-endpoint-box">
						<code id="imf-endpoint-url"><?php echo esc_url( $endpoint_url ); ?></code>
						<button type="button" class="button imf-copy-btn" data-clipboard-target="#imf-endpoint-url">
							<?php esc_html_e( 'Copy', 'immens-mcp-fortress' ); ?>
						</button>
					</div>
					<p class="description">
						<?php esc_html_e( 'Use this URL in Claude Desktop, ChatGPT, Cursor, or any MCP-compatible client.', 'immens-mcp-fortress' ); ?>
					</p>
				</div>

				<div class="imf-card">
					<h2><?php esc_html_e( 'Quick Stats', 'immens-mcp-fortress' ); ?></h2>
					<div class="imf-stats">
						<div class="imf-stat">
							<span class="imf-stat-value"><?php echo esc_html( $count ); ?></span>
							<span class="imf-stat-label"><?php esc_html_e( 'Access Points', 'immens-mcp-fortress' ); ?></span>
						</div>
					</div>
					<?php if ( ! $pro_active && $count >= 2 ) : ?>
						<p class="description" style="color: #d63638;">
							<?php esc_html_e( 'Free tier limited to 2 access points. Upgrade to Pro for unlimited.', 'immens-mcp-fortress' ); ?>
						</p>
					<?php endif; ?>
				</div>
			</div>

			<div class="imf-card">
				<h2><?php esc_html_e( 'Quick Setup Guides', 'immens-mcp-fortress' ); ?></h2>

				<div class="imf-setup-guide">
					<h3><?php esc_html_e( 'Claude Desktop', 'immens-mcp-fortress' ); ?></h3>
					<ol>
						<li><?php esc_html_e( 'Go to Settings → Connectors → Add custom connector', 'immens-mcp-fortress' ); ?></li>
						<li><?php esc_html_e( 'Paste the MCP endpoint URL above', 'immens-mcp-fortress' ); ?></li>
						<li><?php esc_html_e( 'Enter your Access Point API key when prompted', 'immens-mcp-fortress' ); ?></li>
					</ol>
				</div>

				<div class="imf-setup-guide">
					<h3><?php esc_html_e( 'OpenCode', 'immens-mcp-fortress' ); ?></h3>
					<ol>
						<li><?php esc_html_e( 'Add to your opencode.json:', 'immens-mcp-fortress' ); ?></li>
					</ol>
					<pre style="background: #f0f0f1; padding: 10px; border-radius: 3px; font-size: 12px; overflow-x: auto;">{
  "mcp": {
    "immens-mcp-fortress": {
      "type": "remote",
      "url": "<?php echo esc_url( $endpoint_url ); ?>",
      "enabled": true,
      "headers": {
        "Authorization": "Bearer YOUR_API_KEY"
      },
      "oauth": false
    }
  }
}</pre>
					<ol start="2">
						<li><?php esc_html_e( 'Run opencode mcp list to verify connection', 'immens-mcp-fortress' ); ?></li>
					</ol>
				</div>

				<div class="imf-setup-guide">
					<h3><?php esc_html_e( 'OpenClaw', 'immens-mcp-fortress' ); ?></h3>
					<ol>
						<li><?php esc_html_e( 'Tell OpenClaw in plain English:', 'immens-mcp-fortress' ); ?></li>
					</ol>
					<pre style="background: #f0f0f1; padding: 10px; border-radius: 3px; font-size: 12px; overflow-x: auto;"><?php echo esc_textarea( sprintf( 'Add this as an MCP server with Bearer Token authentication: %s and use this token: YOUR_API_KEY', $endpoint_url ) ); ?></pre>
					<ol start="2">
						<li><?php esc_html_e( 'OpenClaw will auto-configure itself. Or install the WordPress MCP skill from ClawHub.', 'immens-mcp-fortress' ); ?></li>
					</ol>
				</div>

				<div class="imf-setup-guide">
					<h3><?php esc_html_e( 'Cursor / Windsurf / Cline', 'immens-mcp-fortress' ); ?></h3>
					<ol>
						<li><?php esc_html_e( 'Add MCP server in your client settings', 'immens-mcp-fortress' ); ?></li>
						<li><?php esc_html_e( 'Set transport to "streamable-http"', 'immens-mcp-fortress' ); ?></li>
						<li><?php esc_html_e( 'Use the URL and your Access Point API key', 'immens-mcp-fortress' ); ?></li>
					</ol>
				</div>
			</div>
		</div>
		<?php
	}
}
