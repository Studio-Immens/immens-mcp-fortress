<?php
namespace Immens_MCP_Fortress\Tools\Revisions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Restore_Revision extends Base_Tool {

	public function get_name() {
		return 'wp_restore_revision';
	}

	public function get_description() {
		return 'Restore a post or page to a previous revision.';
	}

	public function get_category() {
		return 'revisions';
	}

	public function get_required_capability() {
		return 'edit_posts';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'parent'    => array(
					'type'        => 'integer',
					'description' => 'Parent post/page ID',
				),
				'id'        => array(
					'type'        => 'integer',
					'description' => 'Revision ID to restore',
				),
				'post_type' => array(
					'type'        => 'string',
					'description' => 'Post type (post or page)',
					'enum'        => array( 'post', 'page' ),
					'default'     => 'post',
				),
			),
			'required'   => array( 'parent', 'id' ),
		);
	}

	public function get_annotations() {
		return array(
			'title'           => 'Restore Revision',
			'readOnlyHint'    => false,
			'destructiveHint' => true,
			'openWorldHint'   => false,
		);
	}

	public function execute( array $arguments ) {
		$parent_id = $this->parse_required_id( $arguments['parent'], 'Parent ID' );
		$id        = $this->parse_required_id( $arguments['id'], 'Revision ID' );
		$post_type = isset( $arguments['post_type'] ) ? $arguments['post_type'] : 'post';
		$base      = ( 'page' === $post_type ) ? 'pages' : 'posts';

		$revision = $this->rest_request(
			'GET',
			'/wp/v2/' . $base . '/' . $parent_id . '/revisions/' . $id,
			array( 'context' => 'edit' )
		);

		$content = '';
		$title   = '';

		if ( isset( $revision['content']['raw'] ) ) {
			$content = $revision['content']['raw'];
		}
		if ( isset( $revision['title']['raw'] ) ) {
			$title = $revision['title']['raw'];
		}

		$update = array();
		if ( $content ) {
			$update['content'] = $content;
		}
		if ( $title ) {
			$update['title'] = $title;
		}

		if ( empty( $update ) ) {
			throw new \RuntimeException( 'Revision has no restorable content.' );
		}

		$result = $this->rest_request( 'POST', '/wp/v2/' . $base . '/' . $parent_id, $update );
		$this->invalidate_post_cache( $parent_id );

		return array(
			'parent_id'      => $parent_id,
			'revision_id'    => $id,
			'post_type'      => $post_type,
			'restored'       => true,
			'title'          => isset( $result['title']['rendered'] ) ? $result['title']['rendered'] : '',
			'message'        => 'Post restored to revision successfully.',
		);
	}
}
