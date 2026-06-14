<?php
namespace Immens_MCP_Fortress\Tools\Comments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Approve_Comment extends Base_Tool {

	public function get_name() {
		return 'wp_approve_comment';
	}

	public function get_description() {
		return 'Approve a comment.';
	}

	public function get_required_capability() {
		return 'moderate_comments';
	}

	public function get_category() {
		return 'comments';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'comment_id' => array(
					'type'        => 'integer',
					'description' => 'Comment ID to approve',
				),
			),
			'required'   => array( 'comment_id' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'comment_id' ) );

		$id = $this->parse_required_id( $arguments['comment_id'], 'Comment ID' );

		$result = wp_set_comment_status( $id, 'approve', true );
		if ( is_wp_error( $result ) ) {
			throw new \RuntimeException( $result->get_error_message() ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		$params = array( 'context' => 'edit' );
		return $this->rest_request( 'GET', '/wp/v2/comments/' . $id, $params );
	}
}
