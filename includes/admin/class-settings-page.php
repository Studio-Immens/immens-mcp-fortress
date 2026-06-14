<?php
namespace Immens_MCP_Fortress\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings_Page {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_post_immens_mcp_save_settings', array( $this, 'handle_save' ) );
	}

	public function add_page() {
		add_submenu_page(
			'immens-mcp-fortress',
			__( 'Settings', 'immens-mcp-fortress' ),
			__( 'Settings', 'immens-mcp-fortress' ),
			'manage_options',
			'immens-mcp-fortress-settings',
			array( $this, 'render' )
		);
	}

	public function render() {
		$audit_enabled   = get_option( 'immens_mcp_fortress_audit_log_enabled', true );
		$audit_retention = get_option( 'immens_mcp_fortress_audit_log_retention', 30 );
		$force_draft     = get_option( 'immens_mcp_fortress_force_draft_on_create', false );
		$max_title       = get_option( 'immens_mcp_fortress_max_title_length', 0 );
		?>
		<div class="wrap imf-wrap">
			<h1><?php esc_html_e( 'Settings', 'immens-mcp-fortress' ); ?></h1>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="immens_mcp_save_settings">
				<?php wp_nonce_field( 'immens_mcp_save_settings' ); ?>

				<div class="imf-card">
					<h2><?php esc_html_e( 'Security', 'immens-mcp-fortress' ); ?></h2>
					<table class="form-table">
						<tr>
							<th><label for="force_draft"><?php esc_html_e( 'Force Draft on Create', 'immens-mcp-fortress' ); ?></label></th>
							<td>
								<input type="checkbox" name="force_draft_on_create" id="force_draft" value="1"
									<?php checked( $force_draft ); ?>>
								<span class="description">
									<?php esc_html_e( 'AI-created posts are always saved as drafts, regardless of what the AI requests.', 'immens-mcp-fortress' ); ?>
								</span>
							</td>
						</tr>
						<tr>
							<th><label for="max_title"><?php esc_html_e( 'Max Title Length', 'immens-mcp-fortress' ); ?></label></th>
							<td>
								<input type="number" name="max_title_length" id="max_title" min="0" max="500"
									value="<?php echo esc_attr( $max_title ); ?>" class="small-text">
								<span class="description">
									<?php esc_html_e( 'Maximum characters for post titles. 0 = no limit.', 'immens-mcp-fortress' ); ?>
								</span>
							</td>
						</tr>
					</table>
				</div>

				<div class="imf-card">
					<h2><?php esc_html_e( 'Audit Log', 'immens-mcp-fortress' ); ?></h2>
					<table class="form-table">
						<tr>
							<th><label for="audit_enabled"><?php esc_html_e( 'Enable Audit Log', 'immens-mcp-fortress' ); ?></label></th>
							<td>
								<input type="checkbox" name="audit_log_enabled" id="audit_enabled" value="1"
									<?php checked( $audit_enabled ); ?>>
								<span class="description">
									<?php esc_html_e( 'Log every MCP tool call with timestamp, tool name, and result.', 'immens-mcp-fortress' ); ?>
								</span>
							</td>
						</tr>
						<tr>
							<th><label for="audit_retention"><?php esc_html_e( 'Audit Log Retention', 'immens-mcp-fortress' ); ?></label></th>
							<td>
								<input type="number" name="audit_log_retention" id="audit_retention" min="1" max="365"
									value="<?php echo esc_attr( $audit_retention ); ?>" class="small-text">
								<span class="description"><?php esc_html_e( 'days', 'immens-mcp-fortress' ); ?></span>
							</td>
						</tr>
					</table>
				</div>

				<p class="submit">
					<button type="submit" class="button button-primary">
						<?php esc_html_e( 'Save Settings', 'immens-mcp-fortress' ); ?>
					</button>
				</p>
			</form>

			<div class="imf-card">
				<h2><?php esc_html_e( 'Pro Features', 'immens-mcp-fortress' ); ?></h2>
				<p><?php esc_html_e( 'Upgrade to Immens MCP Fortress Pro for:', 'immens-mcp-fortress' ); ?></p>
				<ul style="list-style: disc; margin-left: 20px;">
					<li><?php esc_html_e( 'Change History with before/after snapshots', 'immens-mcp-fortress' ); ?></li>
					<li><?php esc_html_e( 'Gutenberg block-level editing (add/remove/reorder blocks)', 'immens-mcp-fortress' ); ?></li>
					<li><?php esc_html_e( '131 integration tools: Primary Source, Immens CRM, ClassyBlocks, SEO, and more', 'immens-mcp-fortress' ); ?></li>
					<li><?php esc_html_e( 'SSE Transport for legacy MCP clients', 'immens-mcp-fortress' ); ?></li>
					<li><?php esc_html_e( 'Extended audit log retention', 'immens-mcp-fortress' ); ?></li>
				</ul>
				<p>
					<a href="https://studioimmens.com/immens-mcp-fortress-pro" target="_blank" class="button button-primary">
						<?php esc_html_e( 'Learn More →', 'immens-mcp-fortress' ); ?>
					</a>
				</p>
			</div>
		</div>
		<?php
	}

	public function handle_save() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'immens-mcp-fortress' ) );
		}

		check_admin_referer( 'immens_mcp_save_settings' );

		update_option( 'immens_mcp_fortress_force_draft_on_create', ! empty( $_POST['force_draft_on_create'] ) );
		update_option( 'immens_mcp_fortress_max_title_length', isset( $_POST['max_title_length'] ) ? absint( $_POST['max_title_length'] ) : 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		update_option( 'immens_mcp_fortress_audit_log_enabled', ! empty( $_POST['audit_log_enabled'] ) );
		update_option( 'immens_mcp_fortress_audit_log_retention', isset( $_POST['audit_log_retention'] ) ? max( 1, absint( $_POST['audit_log_retention'] ) ) : 30 ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		wp_safe_redirect( add_query_arg(
			array( 'page' => 'immens-mcp-fortress-settings', 'saved' => 1 ),
			admin_url( 'admin.php' )
		) );
		exit;
	}
}
