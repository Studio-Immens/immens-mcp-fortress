<?php
namespace Immens_MCP_Fortress\Tools\W3TotalCache;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Settings extends Base_Tool {

	public function get_name() {
		return 'w3tc_get_settings';
	}

	public function get_description() {
		return 'Get W3 Total Cache configuration and status summary.';
	}

	public function get_category() {
		return 'w3-total-cache';
	}

	public function get_required_capability() {
		return 'manage_options';
	}

	public function get_annotations() {
		return array(
			'title'           => $this->get_title(),
			'readOnlyHint'    => true,
			'destructiveHint' => false,
			'openWorldHint'   => false,
		);
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(),
		);
	}

	public function execute( array $arguments ) {
		$config = get_option( 'w3tc-config', array() );

		if ( empty( $config ) ) {
			return array(
				'active'  => false,
				'message' => 'W3 Total Cache is not configured or not active.',
			);
		}

		$settings = array(
			'active'                => true,
			'version'               => defined( 'W3TC_VERSION' ) ? W3TC_VERSION : 'unknown',
			'page_cache'            => array(
				'enabled' => isset( $config['pgcache.enabled'] ) ? (bool) $config['pgcache.enabled'] : false,
				'engine'  => isset( $config['pgcache.engine'] ) ? $config['pgcache.engine'] : '',
			),
			'database_cache'        => array(
				'enabled' => isset( $config['dbcache.enabled'] ) ? (bool) $config['dbcache.enabled'] : false,
				'engine'  => isset( $config['dbcache.engine'] ) ? $config['dbcache.engine'] : '',
			),
			'object_cache'          => array(
				'enabled' => isset( $config['objectcache.enabled'] ) ? (bool) $config['objectcache.enabled'] : false,
				'engine'  => isset( $config['objectcache.engine'] ) ? $config['objectcache.engine'] : '',
			),
			'minify'                => array(
				'enabled' => isset( $config['minify.enabled'] ) ? (bool) $config['minify.enabled'] : false,
				'engine'  => isset( $config['minify.engine'] ) ? $config['minify.engine'] : '',
			),
			'cdn'                   => array(
				'enabled' => isset( $config['cdn.enabled'] ) ? (bool) $config['cdn.enabled'] : false,
				'engine'  => isset( $config['cdn.engine'] ) ? $config['cdn.engine'] : '',
			),
			'browser_cache'         => isset( $config['browsercache.enabled'] ) ? (bool) $config['browsercache.enabled'] : false,
			'page_cache_lifetime'   => isset( $config['pgcache.lifetime'] ) ? (int) $config['pgcache.lifetime'] : 0,
			'minify_html'           => isset( $config['minify.html.enable'] ) ? (bool) $config['minify.html.enable'] : false,
			'minify_css'            => isset( $config['minify.css.enable'] ) ? (bool) $config['minify.css.enable'] : false,
			'minify_js'             => isset( $config['minify.js.enable'] ) ? (bool) $config['minify.js.enable'] : false,
			'reject_logged_users'   => isset( $config['pgcache.reject.logged'] ) ? (bool) $config['pgcache.reject.logged'] : true,
			'preview_mode'          => function_exists( 'w3tc_is_preview_mode' ) ? w3tc_is_preview_mode() : false,
		);

		return $settings;
	}
}
