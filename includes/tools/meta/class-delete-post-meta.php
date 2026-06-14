<?php
namespace Immens_MCP_Fortress\Tools\Meta;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Delete_Post_Meta extends Base_Tool {

	public function get_name() {
		return 'wp_delete_post_meta';
	}

	public function get_description() {
		return 'Delete post meta.';
	}

	public function get_required_capability() {
		return 'edit_posts';
	}

	public function get_category() {
		return 'meta';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'post_id'  => array(
					'type'        => 'integer',
					'description' => 'Post ID',
				),
				'meta_key' => array(
					'type'        => 'string',
					'description' => 'Meta key to delete',
				),
			),
			'required'   => array( 'post_id', 'meta_key' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'post_id', 'meta_key' ) );

		$post_id  = $this->parse_required_id( $arguments['post_id'] );
		$meta_key = $arguments['meta_key'];

		$result = delete_post_meta( $post_id, $meta_key );

		if ( false === $result ) {
			throw new \RuntimeException(
				sprintf( 'Failed to delete meta key "%s" for post %d.', $meta_key, $post_id )
) ; // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		$this->invalidate_post_cache( $post_id );

		return array(
			'success'  => true,
			'post_id'  => $post_id,
			'meta_key' => $meta_key,
			'deleted'  => $result,
		);
	}
}
