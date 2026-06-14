<?php
namespace Immens_MCP_Fortress\Admin;

use Immens_MCP_Fortress\Access_Points\Access_Point_Manager;
use Immens_MCP_Fortress\Access_Points\Access_Point_Schema;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Access_Points_Page {

	private $manager;

	public function __construct() {
		$this->manager = new Access_Point_Manager();

		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_post_immens_mcp_create_access_point', array( $this, 'handle_create' ) );
		add_action( 'admin_post_immens_mcp_update_access_point', array( $this, 'handle_update' ) );
		add_action( 'admin_post_immens_mcp_delete_access_point', array( $this, 'handle_delete' ) );
		add_action( 'admin_post_immens_mcp_regenerate_key', array( $this, 'handle_regenerate_key' ) );
		add_action( 'wp_ajax_immens_mcp_toggle_access_point', array( $this, 'ajax_toggle' ) );
	}

	public function add_page() {
		add_submenu_page(
			'immens-mcp-fortress',
			__( 'Access Points', 'immens-mcp-fortress' ),
			__( 'Access Points', 'immens-mcp-fortress' ),
			'manage_options',
			'immens-mcp-fortress-access-points',
			array( $this, 'render' )
		);
	}

	public function render() {
		$access_points = $this->manager->get_all_access_points();
		$categories    = Access_Point_Schema::get_all_tool_categories();
		$users         = get_users( array( 'fields' => array( 'ID', 'user_login', 'display_name' ) ) );

		$edit_id = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$editing = $edit_id ? $this->manager->get_access_point( $edit_id ) : null;

		if ( $editing ) {
			$edit_permissions = $editing['tool_permissions']
				? json_decode( $editing['tool_permissions'], true )
				: Access_Point_Schema::get_default_tool_permissions();
		}

		$show_new_key = isset( $_GET['new_key'] ) ? sanitize_text_field( wp_unslash( $_GET['new_key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		?>
		<div class="wrap imf-wrap">
			<h1><?php esc_html_e( 'Access Points', 'immens-mcp-fortress' ); ?></h1>

			<?php if ( $show_new_key ) : ?>
				<div class="notice notice-success is-dismissible">
					<p>
						<strong><?php esc_html_e( 'New API Key (shown only once):', 'immens-mcp-fortress' ); ?></strong><br>
						<code style="word-break: break-all; user-select: all;"><?php echo esc_html( $show_new_key ); ?></code>
					</p>
					<p class="description">
						<?php esc_html_e( 'Copy this key now. It will not be displayed again.', 'immens-mcp-fortress' ); ?>
					</p>
				</div>
			<?php endif; ?>

			<?php if ( $editing ) : ?>
				<div class="imf-card">
					<?php /* translators: %s: access point name */ ?>
					<h2><?php echo esc_html( sprintf( __( 'Edit: %s', 'immens-mcp-fortress' ), $editing['name'] ) ); ?></h2>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="immens_mcp_update_access_point">
						<input type="hidden" name="id" value="<?php echo esc_attr( $editing['id'] ); ?>">
						<?php wp_nonce_field( 'immens_mcp_update_access_point_' . $editing['id'] ); ?>

						<table class="form-table">
							<tr>
								<th><label for="ap_name"><?php esc_html_e( 'Name', 'immens-mcp-fortress' ); ?></label></th>
								<td>
									<input type="text" name="name" id="ap_name" class="regular-text"
										value="<?php echo esc_attr( $editing['name'] ); ?>" required>
								</td>
							</tr>
							<tr>
								<th><label><?php esc_html_e( 'API Key Prefix', 'immens-mcp-fortress' ); ?></label></th>
								<td>
									<code><?php echo esc_html( $editing['api_key_prefix'] ); ?>...</code>
								</td>
							</tr>
							<tr>
								<th><label for="ap_wp_user"><?php esc_html_e( 'WordPress User', 'immens-mcp-fortress' ); ?></label></th>
								<td>
									<select name="wp_user_id" id="ap_wp_user">
										<option value="0"><?php esc_html_e( '— None (Admin capabilities) —', 'immens-mcp-fortress' ); ?></option>
										<?php foreach ( $users as $user ) : ?>
											<option value="<?php echo esc_attr( $user->ID ); ?>"
												<?php selected( $editing['wp_user_id'], $user->ID ); ?>>
												<?php echo esc_html( $user->display_name . ' (' . $user->user_login . ')' ); ?>
											</option>
										<?php endforeach; ?>
									</select>
									<p class="description">
										<?php esc_html_e( 'The AI will act as this WordPress user. Leave empty for full admin access.', 'immens-mcp-fortress' ); ?>
									</p>
								</td>
							</tr>
							<tr>
								<th><label for="ap_ip_whitelist"><?php esc_html_e( 'IP Whitelist', 'immens-mcp-fortress' ); ?></label></th>
								<td>
									<textarea name="ip_whitelist" id="ap_ip_whitelist" rows="4" class="large-text code"
										placeholder="<?php esc_attr_e( '192.168.1.0/24&#10;10.0.0.5&#10;Leave empty to allow all IPs', 'immens-mcp-fortress' ); ?>"
									><?php echo esc_textarea( $editing['ip_whitelist'] ); ?></textarea>
									<p class="description">
										<?php esc_html_e( 'One IP address or CIDR range per line. Leave empty to allow all IPs.', 'immens-mcp-fortress' ); ?>
									</p>
								</td>
							</tr>
							<tr>
								<th><label for="ap_rate_limit"><?php esc_html_e( 'Rate Limit', 'immens-mcp-fortress' ); ?></label></th>
								<td>
									<input type="number" name="rate_limit" id="ap_rate_limit" min="1" max="1000"
										value="<?php echo esc_attr( $editing['rate_limit'] ); ?>" class="small-text">
									<span class="description"><?php esc_html_e( 'requests per minute', 'immens-mcp-fortress' ); ?></span>
								</td>
							</tr>
						</table>

						<h3><?php esc_html_e( 'Tool Permissions', 'immens-mcp-fortress' ); ?></h3>
						<table class="imf-permissions-table widefat striped">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Category', 'immens-mcp-fortress' ); ?></th>
									<th style="width: 80px;"><?php esc_html_e( 'Read', 'immens-mcp-fortress' ); ?></th>
									<th style="width: 80px;"><?php esc_html_e( 'Write', 'immens-mcp-fortress' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $categories as $key => $label ) : ?>
									<tr>
										<td><?php echo esc_html( $label ); ?></td>
										<td>
											<input type="checkbox" name="tool_permissions[<?php echo esc_attr( $key ); ?>][read]" value="1"
												<?php checked( ! empty( $edit_permissions[ $key ]['read'] ) ); ?>>
										</td>
										<td>
											<input type="checkbox" name="tool_permissions[<?php echo esc_attr( $key ); ?>][write]" value="1"
												<?php checked( ! empty( $edit_permissions[ $key ]['write'] ) ); ?>>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>

						<p class="submit">
							<button type="submit" class="button button-primary">
								<?php esc_html_e( 'Save Changes', 'immens-mcp-fortress' ); ?>
							</button>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=immens-mcp-fortress-access-points' ) ); ?>" class="button">
								<?php esc_html_e( 'Cancel', 'immens-mcp-fortress' ); ?>
							</a>
						</p>
					</form>

					<hr>
					<h3><?php esc_html_e( 'Danger Zone', 'immens-mcp-fortress' ); ?></h3>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display: inline-block; margin-right: 10px;">
						<input type="hidden" name="action" value="immens_mcp_regenerate_key">
						<input type="hidden" name="id" value="<?php echo esc_attr( $editing['id'] ); ?>">
						<?php wp_nonce_field( 'immens_mcp_regenerate_key_' . $editing['id'] ); ?>
						<button type="submit" class="button" onclick="return confirm(IMF_Admin.confirm_regenerate);">
							<?php esc_html_e( 'Regenerate API Key', 'immens-mcp-fortress' ); ?>
						</button>
					</form>

					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display: inline-block;">
						<input type="hidden" name="action" value="immens_mcp_delete_access_point">
						<input type="hidden" name="id" value="<?php echo esc_attr( $editing['id'] ); ?>">
						<?php wp_nonce_field( 'immens_mcp_delete_access_point_' . $editing['id'] ); ?>
						<button type="submit" class="button button-link-delete" onclick="return confirm(IMF_Admin.confirm_delete);">
							<?php esc_html_e( 'Delete Access Point', 'immens-mcp-fortress' ); ?>
						</button>
					</form>
				</div>
			<?php endif; ?>

			<div class="imf-card">
				<h2>
					<?php esc_html_e( 'All Access Points', 'immens-mcp-fortress' ); ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=immens-mcp-fortress-access-points&new=1' ) ); ?>" class="page-title-action">
						<?php esc_html_e( 'Add New', 'immens-mcp-fortress' ); ?>
					</a>
				</h2>

				<?php if ( isset( $_GET['new'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
					<div class="imf-new-form" style="margin-bottom: 20px; padding: 15px; background: #fff; border: 1px solid #c3c4c7;">
						<h3><?php esc_html_e( 'Create Access Point', 'immens-mcp-fortress' ); ?></h3>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="immens_mcp_create_access_point">
							<?php wp_nonce_field( 'immens_mcp_create_access_point' ); ?>
							<table class="form-table">
								<tr>
									<th><label for="new_ap_name"><?php esc_html_e( 'Name', 'immens-mcp-fortress' ); ?></label></th>
									<td>
										<input type="text" name="name" id="new_ap_name" class="regular-text"
											placeholder="<?php esc_attr_e( 'My Claude Desktop', 'immens-mcp-fortress' ); ?>" required>
									</td>
								</tr>
								<tr>
									<th><label for="new_ap_user"><?php esc_html_e( 'WordPress User', 'immens-mcp-fortress' ); ?></label></th>
									<td>
										<select name="wp_user_id" id="new_ap_user">
											<option value="0"><?php esc_html_e( '— None (Admin capabilities) —', 'immens-mcp-fortress' ); ?></option>
											<?php foreach ( $users as $user ) : ?>
												<option value="<?php echo esc_attr( $user->ID ); ?>">
													<?php echo esc_html( $user->display_name . ' (' . $user->user_login . ')' ); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</td>
								</tr>
							</table>
							<p>
								<button type="submit" class="button button-primary">
									<?php esc_html_e( 'Create Access Point', 'immens-mcp-fortress' ); ?>
								</button>
							</p>
						</form>
					</div>
				<?php endif; ?>

				<?php if ( empty( $access_points ) ) : ?>
					<p><?php esc_html_e( 'No access points configured yet. Create your first one above.', 'immens-mcp-fortress' ); ?></p>
				<?php else : ?>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Name', 'immens-mcp-fortress' ); ?></th>
								<th><?php esc_html_e( 'Key Prefix', 'immens-mcp-fortress' ); ?></th>
								<th><?php esc_html_e( 'Status', 'immens-mcp-fortress' ); ?></th>
								<th><?php esc_html_e( 'IP Whitelist', 'immens-mcp-fortress' ); ?></th>
								<th><?php esc_html_e( 'Rate Limit', 'immens-mcp-fortress' ); ?></th>
								<th><?php esc_html_e( 'Last Used', 'immens-mcp-fortress' ); ?></th>
								<th><?php esc_html_e( 'Actions', 'immens-mcp-fortress' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $access_points as $ap ) : ?>
								<tr>
									<td>
										<strong><?php echo esc_html( $ap['name'] ); ?></strong>
										<?php if ( $ap['is_pro'] ) : ?>
											<span class="imf-pro-badge">PRO</span>
										<?php endif; ?>
									</td>
									<td><code><?php echo esc_html( $ap['api_key_prefix'] ); ?>...</code></td>
									<td>
										<label class="imf-toggle">
											<input type="checkbox" class="imf-toggle-input"
												data-ap-id="<?php echo esc_attr( $ap['id'] ); ?>"
												<?php checked( $ap['is_enabled'] ); ?>>
											<span class="imf-toggle-slider"></span>
										</label>
									</td>
									<td>
										<?php if ( ! empty( $ap['ip_whitelist'] ) ) : ?>
											<span class="dashicons dashicons-lock" title="<?php esc_attr_e( 'IP restricted', 'immens-mcp-fortress' ); ?>"></span>
											<?php esc_html_e( 'Restricted', 'immens-mcp-fortress' ); ?>
										<?php else : ?>
											<span class="dashicons dashicons-unlock" title="<?php esc_attr_e( 'Open to all IPs', 'immens-mcp-fortress' ); ?>"></span>
											<?php esc_html_e( 'Open', 'immens-mcp-fortress' ); ?>
										<?php endif; ?>
									</td>
									<td><?php echo esc_html( $ap['rate_limit'] ); ?>/min</td>
									<td>
										<?php echo $ap['last_used_at']
											? esc_html( human_time_diff( strtotime( $ap['last_used_at'] ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'immens-mcp-fortress' ) )
											: esc_html__( 'Never', 'immens-mcp-fortress' ); ?>
									</td>
									<td>
										<a href="<?php echo esc_url( add_query_arg( 'edit', $ap['id'], admin_url( 'admin.php?page=immens-mcp-fortress-access-points' ) ) ); ?>" class="button button-small">
											<?php esc_html_e( 'Edit', 'immens-mcp-fortress' ); ?>
										</a>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	public function handle_create() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'immens-mcp-fortress' ) );
		}

		check_admin_referer( 'immens_mcp_create_access_point' );

		$name       = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$wp_user_id = isset( $_POST['wp_user_id'] ) ? absint( $_POST['wp_user_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( empty( $name ) ) {
			wp_die( esc_html__( 'Name is required.', 'immens-mcp-fortress' ) );
		}

		$result = $this->manager->create_access_point( $name, $wp_user_id );

		if ( is_wp_error( $result ) ) {
			wp_die( esc_html( $result->get_error_message() ) );
		}

		$redirect = add_query_arg(
			array(
				'page'    => 'immens-mcp-fortress-access-points',
				'new_key' => urlencode( $result['raw_key'] ),
			),
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect );
		exit;
	}

	public function handle_update() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'immens-mcp-fortress' ) );
		}

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		check_admin_referer( 'immens_mcp_update_access_point_' . $id );

		$data = array();

		if ( isset( $_POST['name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$data['name'] = sanitize_text_field( wp_unslash( $_POST['name'] ) );
		}
		if ( isset( $_POST['wp_user_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$data['wp_user_id'] = absint( $_POST['wp_user_id'] );
		}
		$data['ip_whitelist'] = isset( $_POST['ip_whitelist'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
			? sanitize_textarea_field( wp_unslash( $_POST['ip_whitelist'] ) )
			: '';
		if ( isset( $_POST['rate_limit'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$data['rate_limit'] = absint( $_POST['rate_limit'] );
		}
		if ( isset( $_POST['tool_permissions'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$raw_permissions = wp_unslash( $_POST['tool_permissions'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$permissions = array();
			if ( is_array( $raw_permissions ) ) {
				foreach ( $raw_permissions as $category => $perms ) {
					$category = sanitize_key( $category );
					$permissions[ $category ] = array(
						'read'  => ! empty( $perms['read'] ),
						'write' => ! empty( $perms['write'] ),
					);
				}
			}
			$data['tool_permissions'] = $permissions;
		}

		$this->manager->update_access_point( $id, $data );

		wp_safe_redirect( add_query_arg(
			array( 'page' => 'immens-mcp-fortress-access-points', 'updated' => 1 ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	public function handle_delete() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'immens-mcp-fortress' ) );
		}

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		check_admin_referer( 'immens_mcp_delete_access_point_' . $id );

		$this->manager->delete_access_point( $id );

		wp_safe_redirect( add_query_arg(
			array( 'page' => 'immens-mcp-fortress-access-points', 'deleted' => 1 ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	public function handle_regenerate_key() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'immens-mcp-fortress' ) );
		}

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		check_admin_referer( 'immens_mcp_regenerate_key_' . $id );

		$result = $this->manager->regenerate_key( $id );

		wp_safe_redirect( add_query_arg(
			array(
				'page'    => 'immens-mcp-fortress-access-points',
				'new_key' => urlencode( $result['raw_key'] ),
			),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	public function ajax_toggle() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		check_ajax_referer( 'immens_mcp_fortress_admin' );

		$id      = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$enabled = isset( $_POST['enabled'] ) ? (int) $_POST['enabled'] : 1; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$this->manager->toggle_access_point( $id, $enabled );

		wp_send_json_success( array( 'enabled' => $enabled ) );
	}
}
