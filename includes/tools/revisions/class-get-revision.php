<?php
namespace Immens_MCP_Fortress\Tools\Revisions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Revision extends Base_Tool {

	public function get_name() {
		return 'wp_get_revision';
	}

	public function get_description() {
		return 'Get a single revision by parent post ID and revision ID.';
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'parent' => array(
					'type'        => 'integer',
					'description' => 'Parent post ID',
				),
				'id'     => array(
					'type'        => 'integer',
					'description' => 'Revision ID',
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

	public function execute( array $arguments ) {
		$parent_id = $this->parse_required_id( $arguments['parent'], 'Parent ID' );
		$id        = $this->parse_required_id( $arguments['id'] );
		$params    = array( 'context' => 'edit' );

		$post_type = isset( $arguments['post_type'] ) ? trim( $arguments['post_type'] ) : 'post';
		$base      = ( 'page' === $post_type ) ? 'pages' : 'posts';
		return $this->rest_request( 'GET', '/wp/v2/' . $base . '/' . $parent_id . '/revisions/' . $id, $params );
	}
}
