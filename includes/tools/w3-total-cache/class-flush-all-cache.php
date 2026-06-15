<?php
namespace Immens_MCP_Fortress\Tools\W3TotalCache;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Flush_All_Cache extends Base_Tool {

	public function get_name() {
		return 'w3tc_flush_all_cache';
	}

	public function get_description() {
		return 'Flush all W3 Total Cache caches including page, database, object, minify, and CDN.';
	}

	public function get_category() {
		return 'w3-total-cache';
	}

	public function get_required_capability() {
		return 'manage_options';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(),
		);
	}

	public function execute( array $arguments ) {
		if ( ! defined( 'W3TC' ) && ! function_exists( 'w3tc_flush_all' ) ) {
			throw new \RuntimeException( 'W3 Total Cache plugin is not active.' );
		}

		if ( function_exists( 'w3tc_flush_all' ) ) {
			w3tc_flush_all();
		} elseif ( function_exists( 'w3tc_pgcache_flush' ) ) {
			w3tc_pgcache_flush();
			w3tc_dbcache_flush();
			w3tc_objectcache_flush();
			w3tc_minify_flush();
		} else {
			throw new \RuntimeException( 'No W3 Total Cache flush functions available.' );
		}

		return array(
			'success' => true,
			'message' => 'All W3 Total Cache caches flushed successfully.',
		);
	}
}
