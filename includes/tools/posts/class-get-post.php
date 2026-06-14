<?php
namespace Immens_MCP_Fortress\Tools\Posts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Post extends Base_Tool {

	public function get_name() {
		return 'wp_get_post';
	}

	public function get_description() {
		return 'Get a single post by ID with edit context.';
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'id' => array(
					'type'        => 'integer',
					'description' => 'Post ID',
				),
			),
			'required'   => array( 'id' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'id' ) );
		$id = $this->parse_required_id( $arguments['id'], 'Post ID' );

		return $this->rest_request( 'GET', '/wp/v2/posts/' . $id, array( 'context' => 'edit' ) );
	}
}
