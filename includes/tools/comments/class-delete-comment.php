<?php
namespace Immens_MCP_Fortress\Tools\Comments;

use Immens_MCP_Fortress\Tools\Base_Tool;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Delete_Comment extends Base_Tool {

	public function get_name() {
		return 'wp_delete_comment';
	}

	public function get_description() {
		return 'Permanently delete a comment by ID. Sets the comment status to trash or deletes it permanently.';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'comment_id' => array(
					'type'        => 'integer',
					'description' => 'The ID of the comment to delete.',
				),
				'force'      => array(
					'type'        => 'boolean',
					'description' => 'Whether to bypass trash and force deletion.',
					'default'     => false,
				),
			),
			'required'   => array( 'comment_id' ),
		);
	}

	public function get_required_capability() {
		return 'moderate_comments';
	}

	public function get_category() {
		return 'comments';
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'comment_id' ) );
		$comment_id = $this->parse_required_id( $arguments['comment_id'], 'comment_id' );
		$force      = ! empty( $arguments['force'] );

		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			throw new \RuntimeException( 'Comment not found.' );
		}

		if ( $force ) {
			$result = wp_delete_comment( $comment_id, true );
		} else {
			$result = wp_trash_comment( $comment_id );
		}

		if ( ! $result ) {
			throw new \RuntimeException( 'Failed to delete comment.' );
		}

		return array(
			'deleted'     => true,
			'comment_id'  => $comment_id,
			'force'       => $force,
			'former_author' => $comment->comment_author,
		);
	}
}
