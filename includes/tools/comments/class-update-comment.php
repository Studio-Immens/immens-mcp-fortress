<?php
namespace Immens_MCP_Fortress\Tools\Comments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Update_Comment extends Base_Tool {

	public function get_name() {
		return 'wp_update_comment';
	}

	public function get_description() {
		return 'Update an existing comment.';
	}

	public function get_required_capability() {
		return 'edit_posts';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'id'      => array(
					'type'        => 'integer',
					'description' => 'Comment ID',
				),
				'content' => array(
					'type'        => 'string',
					'description' => 'Comment content',
				),
				'status'  => array(
					'type'        => 'string',
					'description' => 'Comment status',
					'enum'        => array( 'hold', 'approve', 'spam', 'trash' ),
				),
			),
			'required'   => array( 'id' ),
		);
	}

	public function execute( array $arguments ) {
		$id     = $this->parse_required_id( $arguments['id'] );
		$params = array();

		if ( isset( $arguments['content'] ) ) {
			$params['content'] = $arguments['content'];
		}
		if ( isset( $arguments['status'] ) ) {
			$params['status'] = $arguments['status'];
		}

		return $this->rest_request( 'POST', '/wp/v2/comments/' . $id, $params );
	}
}
