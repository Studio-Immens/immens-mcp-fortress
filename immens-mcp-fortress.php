<?php
/**
 * Plugin Name: Immens MCP Fortress — AI-Powered WordPress Manager for Claude, ChatGPT, Gemini & Cursor
 * Description: Let AI manage your WordPress site. Connect Claude, ChatGPT, Gemini or Cursor to create content, manage media, and control settings — securely, no developer needed.
 * Version:     1.3.5
 * Author:      Studio Immens
 * Author URI:  https://studioimmens.com
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: immens-mcp-fortress
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'IMMENS_MCP_FORTRESS_VERSION', '1.3.5' );
define( 'IMMENS_MCP_FORTRESS_PLUGIN_FILE', __FILE__ );
define( 'IMMENS_MCP_FORTRESS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'IMMENS_MCP_FORTRESS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'IMMENS_MCP_FORTRESS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/class-plugin.php';

register_activation_hook( __FILE__, array( 'Immens_MCP_Fortress\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Immens_MCP_Fortress\Deactivator', 'deactivate' ) );

Immens_MCP_Fortress\Plugin::instance();

// Lead capture AJAX handler
add_action( 'wp_ajax_imf_lead_submit', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Unauthorized', 403 );
	}
	check_ajax_referer( 'imf_lead_nonce', 'nonce' );

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$name  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	if ( empty( $email ) ) {
		wp_send_json_error( 'Email is required.', 400 );
	}

	$leads   = get_option( 'imf_leads', array() );
	$leads[] = array(
		'name'        => $name,
		'email'       => $email,
		'source'      => 'immens-mcp-fortress',
		'lead_magnet' => 'Complete MCP Setup Guide for WordPress',
		'timestamp'   => current_time( 'mysql' ),
	);
	update_option( 'imf_leads', $leads );

	$webhook_url = get_option( 'imf_lead_webhook_url', '' );
	if ( ! empty( $webhook_url ) ) {
		wp_remote_post( $webhook_url, array(
			'body'    => array(
				'name'        => $name,
				'email'       => $email,
				'source'      => 'immens-mcp-fortress',
				'lead_magnet' => 'Complete MCP Setup Guide for WordPress',
			),
			'timeout' => 10,
		) );
	}

	wp_send_json_success( array( 'message' => 'Thank you! Check your email for the free guide.' ) );
} );

add_filter(
	'plugin_action_links_' . IMMENS_MCP_FORTRESS_PLUGIN_BASENAME,
	function ( $links ) {
		$prepend = array(
			'dashboard'     => '<a href="' . esc_url( admin_url( 'admin.php?page=immens-mcp-fortress' ) ) . '">' . esc_html__( 'Dashboard', 'immens-mcp-fortress' ) . '</a>',
			'access_points' => '<a href="' . esc_url( admin_url( 'admin.php?page=immens-mcp-fortress-access-points' ) ) . '">' . esc_html__( 'Access Points', 'immens-mcp-fortress' ) . '</a>',
		);
		$append = array(
			'settings' => '<a href="' . esc_url( admin_url( 'admin.php?page=immens-mcp-fortress-settings' ) ) . '">' . esc_html__( 'Settings', 'immens-mcp-fortress' ) . '</a>',
		);
		return array_merge( $prepend, $links, $append );
	}
);
