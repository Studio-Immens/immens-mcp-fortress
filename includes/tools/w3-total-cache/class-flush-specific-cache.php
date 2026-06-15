<?php
namespace Immens_MCP_Fortress\Tools\W3TotalCache;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Flush_Specific_Cache extends Base_Tool {

	public function get_name() {
		return 'w3tc_flush_cache';
	}

	public function get_description() {
		return 'Flush a specific W3 Total Cache type: page, database, object, minify, opcode, or all.';
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
			'properties' => array(
				'type' => array(
					'type'        => 'string',
					'description' => 'Cache type to flush: page, database, object, minify, opcode, post, or all',
					'enum'        => array( 'page', 'database', 'object', 'minify', 'opcode', 'post', 'all' ),
				),
				'post_id' => array(
					'type'        => 'integer',
					'description' => 'Post ID to flush (only when type is "post")',
				),
			),
			'required'   => array( 'type' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'type' ) );

		if ( ! defined( 'W3TC' ) && ! function_exists( 'w3tc_flush_all' ) ) {
			throw new \RuntimeException( 'W3 Total Cache plugin is not active.' );
		}

		$type = $arguments['type'];
		$result = false;
		$label = '';

		switch ( $type ) {
			case 'all':
				if ( function_exists( 'w3tc_flush_all' ) ) {
					w3tc_flush_all();
					$result = true;
					$label = 'all caches';
				}
				break;

			case 'page':
				if ( function_exists( 'w3tc_flush_page_cache' ) ) {
					w3tc_flush_page_cache();
					$result = true;
					$label = 'page cache';
				} elseif ( function_exists( 'w3tc_pgcache_flush' ) ) {
					w3tc_pgcache_flush();
					$result = true;
					$label = 'page cache';
				}
				break;

			case 'database':
				if ( function_exists( 'w3tc_flush_database' ) ) {
					w3tc_flush_database();
					$result = true;
					$label = 'database cache';
				} elseif ( function_exists( 'w3tc_dbcache_flush' ) ) {
					w3tc_dbcache_flush();
					$result = true;
					$label = 'database cache';
				}
				break;

			case 'object':
				if ( function_exists( 'w3tc_flush_object_cache' ) ) {
					w3tc_flush_object_cache();
					$result = true;
					$label = 'object cache';
				} elseif ( function_exists( 'w3tc_objectcache_flush' ) ) {
					w3tc_objectcache_flush();
					$result = true;
					$label = 'object cache';
				}
				break;

			case 'minify':
				if ( function_exists( 'w3tc_flush_minify' ) ) {
					w3tc_flush_minify();
					$result = true;
					$label = 'minify cache';
				} elseif ( function_exists( 'w3tc_minify_flush' ) ) {
					w3tc_minify_flush();
					$result = true;
					$label = 'minifiy cache';
				}
				break;

			case 'opcode':
				if ( function_exists( 'w3tc_flush_opcode' ) ) {
					w3tc_flush_opcode();
					$result = true;
					$label = 'opcode cache';
				}
				break;

			case 'post':
				$post_id = isset( $arguments['post_id'] ) ? (int) $arguments['post_id'] : 0;
				if ( $post_id > 0 && function_exists( 'w3tc_flush_post' ) ) {
					w3tc_flush_post( $post_id );
					$result = true;
					$label = 'cache for post ' . $post_id;
				} elseif ( function_exists( 'w3tc_pgcache_flush_post' ) ) {
					w3tc_pgcache_flush_post( $post_id );
					$result = true;
					$label = 'cache for post ' . $post_id;
				} else {
					throw new \InvalidArgumentException( 'post_id is required when type is "post".' );
				}
				break;

			default:
				throw new \InvalidArgumentException(
					sprintf( 'Invalid cache type: %s. Valid types: page, database, object, minify, opcode, post, all', esc_html( $type ) )
				); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		if ( ! $result ) {
			throw new \RuntimeException( sprintf( 'Failed to flush %s. The cache type may not be enabled.', esc_html( $type ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		return array(
			'success' => true,
			'type'    => $type,
			'message' => sprintf( 'W3 Total Cache %s flushed successfully.', $label ),
		);
	}
}
