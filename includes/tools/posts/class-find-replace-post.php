<?php
namespace Immens_MCP_Fortress\Tools\Posts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Find_Replace_Post extends Base_Tool {

	public function get_name() {
		return 'wp_find_replace_post';
	}

	public function get_description() {
		return 'Find and replace text in post content.';
	}

	public function get_required_capability() {
		return 'edit_posts';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'id'        => array(
					'type'        => 'integer',
					'description' => 'Post ID',
				),
				'find'      => array(
					'type'        => 'string',
					'description' => 'Text to find',
				),
				'replace'   => array(
					'type'        => 'string',
					'description' => 'Replacement text',
				),
				'post_type' => array(
					'type'        => 'string',
					'description' => 'Post type (post or page)',
					'enum'        => array( 'post', 'page' ),
					'default'     => 'post',
				),
			),
			'required'   => array( 'id', 'find', 'replace' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'id', 'find', 'replace' ) );
		$id        = $this->parse_required_id( $arguments['id'], 'Post ID' );
		$post_type = isset( $arguments['post_type'] ) ? $arguments['post_type'] : 'post';
		$base      = ( 'page' === $post_type ) ? 'pages' : 'posts';

		$post = $this->rest_request( 'GET', '/wp/v2/' . $base . '/' . $id, array( 'context' => 'edit' ) );

		$content    = isset( $post['content']['raw'] ) ? $post['content']['raw'] : '';
		$find       = $arguments['find'];
		$replace    = $arguments['replace'];
		$new_content = str_replace( $find, $replace, $content );

		$result = $this->rest_request( 'POST', '/wp/v2/' . $base . '/' . $id, array( 'content' => $new_content ) );
		$this->invalidate_post_cache( $id );

		if ( $content === $new_content ) {
			return array(
				'id'        => $id,
				'post_type' => $post_type,
				'replaced'  => false,
				'message'   => 'No matches found.',
				'post'      => $result,
			);
		}

		return array(
			'id'        => $id,
			'post_type' => $post_type,
			'replaced'  => true,
			'message'   => 'Text replaced successfully.',
			'post'      => $result,
		);
	}
}
