<?php
/**
 * Plugin Name: Immens MCP Fortress
 * Description: Military-grade MCP server for WordPress with multi-access-point architecture. Connect Claude, ChatGPT, Cursor and any MCP-compatible AI to manage your site with per-access-point IP whitelisting, tool permissions, and Gutenberg block-level manipulation.
 * Version:     1.1.8
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

define( 'IMMENS_MCP_FORTRESS_VERSION', '1.1.8' );
define( 'IMMENS_MCP_FORTRESS_PLUGIN_FILE', __FILE__ );
define( 'IMMENS_MCP_FORTRESS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'IMMENS_MCP_FORTRESS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'IMMENS_MCP_FORTRESS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once IMMENS_MCP_FORTRESS_PLUGIN_DIR . 'includes/class-plugin.php';

register_activation_hook( __FILE__, array( 'Immens_MCP_Fortress\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Immens_MCP_Fortress\Deactivator', 'deactivate' ) );

Immens_MCP_Fortress\Plugin::instance();

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
