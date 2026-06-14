<?php
namespace Immens_MCP_Fortress\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Audit_Log_Page {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_page' ), 30 );
	}

	public function add_page() {
		add_submenu_page(
			'immens-mcp-fortress',
			__( 'Audit Log', 'immens-mcp-fortress' ),
			__( 'Audit Log', 'immens-mcp-fortress' ),
			'manage_options',
			'immens-mcp-fortress-audit-log',
			array( $this, 'render' )
		);
	}

	public function render() {
		global $wpdb;
		$table = $wpdb->prefix . 'immens_mcp_audit_log';

		$per_page = 20;
		$current_page = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$offset = ( $current_page - 1 ) * $per_page;

		$where = array( '1=1' );
		$where_args = array();

		$filter_tool = isset( $_GET['tool_name'] ) ? sanitize_text_field( wp_unslash( $_GET['tool_name'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $filter_tool ) {
			$where[] = 'tool_name LIKE %s';
			$where_args[] = '%' . $wpdb->esc_like( $filter_tool ) . '%';
		}

		$filter_status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $filter_status ) {
			$where[] = 'result_status = %s';
			$where_args[] = $filter_status;
		}

		$filter_date = isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $filter_date ) {
			$where[] = 'created_at >= %s';
			$where_args[] = $filter_date . ' 00:00:00';
		}

		$where_sql = implode( ' AND ', $where ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$table_escaped = esc_sql( $table );

		if ( ! empty( $where_args ) ) {
			$total = (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM `{$table_escaped}` WHERE {$where_sql}", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$where_args
			) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		} else {
			$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table_escaped}`" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		$query = ! empty( $where_args )
			? $wpdb->prepare(
				"SELECT * FROM `{$table_escaped}` WHERE {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				array_merge( $where_args, array( $per_page, $offset ) ) // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			)
			: $wpdb->prepare(
				"SELECT * FROM `{$table_escaped}` ORDER BY created_at DESC LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$per_page,
				$offset
			);
		$rows = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared

		$total_pages = ceil( $total / $per_page );

		$statuses = $wpdb->get_col( "SELECT DISTINCT result_status FROM `{$table_escaped}` ORDER BY result_status" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Audit Log', 'immens-mcp-fortress' ); ?></h1>

			<form method="get" class="imf-filter-form" style="margin-bottom: 15px;">
				<input type="hidden" name="page" value="immens-mcp-fortress-audit-log">
				<div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
					<input type="text" name="tool_name" placeholder="<?php esc_attr_e( 'Tool name...', 'immens-mcp-fortress' ); ?>"
						value="<?php echo esc_attr( $filter_tool ); ?>">
					<select name="status">
						<option value=""><?php esc_html_e( 'All statuses', 'immens-mcp-fortress' ); ?></option>
						<?php foreach ( $statuses as $s ) : ?>
							<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $filter_status, $s ); ?>>
								<?php echo esc_html( $s ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<input type="date" name="date_from" value="<?php echo esc_attr( $filter_date ); ?>">
					<button type="submit" class="button">
						<?php esc_html_e( 'Filter', 'immens-mcp-fortress' ); ?>
					</button>
				</div>
			</form>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Time', 'immens-mcp-fortress' ); ?></th>
						<th><?php esc_html_e( 'Tool', 'immens-mcp-fortress' ); ?></th>
						<th><?php esc_html_e( 'Access Point', 'immens-mcp-fortress' ); ?></th>
						<th><?php esc_html_e( 'Status', 'immens-mcp-fortress' ); ?></th>
						<th><?php esc_html_e( 'IP', 'immens-mcp-fortress' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $rows ) ) : ?>
						<tr>
							<td colspan="5"><?php esc_html_e( 'No log entries found.', 'immens-mcp-fortress' ); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ( $rows as $row ) : ?>
							<tr>
								<td><?php echo esc_html( $row['created_at'] ); ?></td>
								<td>
									<strong><?php echo esc_html( $row['tool_name'] ); ?></strong>
									<?php if ( ! empty( $row['arguments'] ) ) : ?>
										<button type="button" class="button button-small imf-log-details"
											data-args="<?php echo esc_attr( $row['arguments'] ); ?>"
											onclick="document.getElementById('imf-args-modal').style.display='block';document.getElementById('imf-args-content').textContent=this.dataset.args">
											<?php esc_html_e( 'args', 'immens-mcp-fortress' ); ?>
										</button>
									<?php endif; ?>
								</td>
								<td><?php echo esc_html( $row['access_point_id'] ); ?></td>
								<td>
									<span class="imf-status-<?php echo esc_attr( $row['result_status'] ); ?>">
										<?php echo esc_html( $row['result_status'] ); ?>
									</span>
								</td>
								<td><?php echo esc_html( $row['ip_address'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>

			<?php if ( $total_pages > 1 ) : ?>
				<div class="tablenav bottom" style="margin-top: 10px;">
					<div class="tablenav-pages">
						<?php
						$paginate = paginate_links( array(
							'base'      => add_query_arg( 'paged', '%#%' ),
							'format'    => '',
							'prev_text' => '&laquo;',
							'next_text' => '&raquo;',
							'total'     => $total_pages,
							'current'   => $current_page,
						) );
						echo wp_kses_post( $paginate );
						?>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<div id="imf-args-modal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.7);z-index:100000;"
			onclick="if(event.target===this)this.style.display='none'">
			<div style="background:linear-gradient(135deg,#0e1528,#121828);border:1px solid rgba(0,229,255,0.2);margin:60px auto;max-width:620px;padding:24px;border-radius:12px;max-height:70vh;overflow:auto;box-shadow:0 12px 48px rgba(0,0,0,0.5);">
				<pre id="imf-args-content" style="white-space:pre-wrap;word-break:break-all;font-size:12px;color:#06d6a0;font-family:'SF Mono','Fira Code','Consolas',monospace;background:rgba(0,0,0,0.4);padding:14px;border-radius:8px;border:1px solid rgba(0,229,255,0.1);"></pre>
				<button type="button" class="button" onclick="document.getElementById('imf-args-modal').style.display='none'" style="margin-top:12px;">
					<?php esc_html_e( 'Close', 'immens-mcp-fortress' ); ?>
				</button>
			</div>
		</div>
		<?php
	}
}
