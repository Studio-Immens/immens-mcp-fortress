<?php
namespace Immens_MCP_Fortress\Tools\Posts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Delete_Post extends Base_Tool {

	public function get_name() {
		return 'wp_delete_post';
	}

	public function get_description() {
		return 'Delete a post (trash or force delete).';
	}

	public function get_required_capability() {
		return 'delete_posts';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'id'    => array(
					'type'        => 'integer',
					'description' => 'Post ID',
				),
				'force' => array(
					'type'        => 'boolean',
					'description' => 'Force delete (bypass trash)',
					'default'     => false,
				),
			),
			'required'   => array( 'id' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'id' ) );
		$id    = $this->parse_required_id( $arguments['id'], 'Post ID' );
		$force = ! empty( $arguments['force'] );

		$this->invalidate_post_cache( $id );

		return $this->rest_request( 'DELETE', '/wp/v2/posts/' . $id, array( 'force' => $force ) );
	}
}
