<?php
namespace Immens_MCP_Fortress\Tools\Comments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Create_Comment extends Base_Tool {

	public function get_name() {
		return 'wp_create_comment';
	}

	public function get_description() {
		return 'Create a new comment on a post.';
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_annotations() {
		return array(
			'title'           => $this->get_title(),
			'readOnlyHint'    => false,
			'destructiveHint' => false,
			'openWorldHint'   => true,
		);
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'post'    => array(
					'type'        => 'integer',
					'description' => 'Post ID to comment on',
				),
				'content' => array(
					'type'        => 'string',
					'description' => 'Comment content',
				),
				'parent'  => array(
					'type'        => 'integer',
					'description' => 'Parent comment ID for replies',
				),
				'status'  => array(
					'type'        => 'string',
					'description' => 'Comment status',
					'enum'        => array( 'hold', 'approve', 'spam', 'trash' ),
					'default'     => 'approve',
				),
			),
			'required'   => array( 'post', 'content' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'post', 'content' ) );

		$params = array(
			'post'    => (int) $arguments['post'],
			'content' => $arguments['content'],
			'status'  => isset( $arguments['status'] ) ? $arguments['status'] : 'approve',
		);

		if ( isset( $arguments['parent'] ) ) {
			$params['parent'] = (int) $arguments['parent'];
		}

		return $this->rest_request( 'POST', '/wp/v2/comments', $params );
	}
}
