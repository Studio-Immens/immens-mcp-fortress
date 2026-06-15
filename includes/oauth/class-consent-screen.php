<?php
namespace Immens_MCP_Fortress\OAuth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Consent_Screen {

	public function render( $client_id, $redirect_uri, $scope, $state, $code_challenge, $code_challenge_method ) {
		$scopes_list = array_filter( explode( ' ', $scope ) );
		$scope_map   = Scope_Map::get_all();
		$client_name = substr( $client_id, 0, 20 );

		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="utf-8">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title><?php esc_html_e( 'Authorize MCP Client', 'immens-mcp-fortress' ); ?></title>
			<?php
				wp_enqueue_style( 'immens-mcp-fortress-consent' );
				wp_print_styles( array( 'immens-mcp-fortress-consent' ) );
			?>
		</head>
		<body>
			<div class="card">
				<h1><?php esc_html_e( 'Authorize Application', 'immens-mcp-fortress' ); ?></h1>
				<p>
					<strong><?php echo esc_html( $client_name ); ?></strong>
					<?php esc_html_e( 'wants to access your WordPress site via MCP.', 'immens-mcp-fortress' ); ?>
				</p>
				<p><?php esc_html_e( 'Requested permissions:', 'immens-mcp-fortress' ); ?></p>
				<ul class="scope-list">
					<?php foreach ( $scopes_list as $s ) : ?>
						<li><?php echo isset( $scope_map[ $s ] ) ? esc_html( $scope_map[ $s ] ) : esc_html( $s ); ?></li>
					<?php endforeach; ?>
					<?php if ( empty( $scopes_list ) ) : ?>
						<li><?php esc_html_e( 'Basic read access', 'immens-mcp-fortress' ); ?></li>
					<?php endif; ?>
				</ul>
				<form method="post">
					<?php wp_nonce_field( 'imf_oauth_consent', '_imf_oauth_nonce' ); ?>
					<input type="hidden" name="client_id" value="<?php echo esc_attr( $client_id ); ?>">
					<input type="hidden" name="redirect_uri" value="<?php echo esc_attr( $redirect_uri ); ?>">
					<input type="hidden" name="state" value="<?php echo esc_attr( $state ); ?>">
					<input type="hidden" name="code_challenge" value="<?php echo esc_attr( $code_challenge ); ?>">
					<input type="hidden" name="code_challenge_method" value="<?php echo esc_attr( $code_challenge_method ); ?>">
					<input type="hidden" name="scope" value="<?php echo esc_attr( $scope ); ?>">
					<div class="actions">
						<button type="submit" class="btn btn-approve"><?php esc_html_e( 'Approve', 'immens-mcp-fortress' ); ?></button>
						<a href="<?php echo esc_url( home_url() ); ?>" class="btn btn-deny"><?php esc_html_e( 'Deny', 'immens-mcp-fortress' ); ?></a>
					</div>
				</form>
			</div>
		</body>
		</html>
		<?php
		$html = ob_get_clean();

		$response = new \WP_REST_Response( $html, 200 );
		$response->header( 'Content-Type', 'text/html; charset=utf-8' );
		$response->header( 'X-Frame-Options', 'DENY' );
		return $response;
	}
}
