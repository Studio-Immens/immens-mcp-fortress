<?php
namespace Immens_MCP_Fortress\Tools\Meta;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Post_Meta extends Base_Tool {

	public function get_name() {
		return 'wp_get_post_meta';
	}

	public function get_description() {
		return 'Get all meta fields for a post.';
	}

	public function get_required_capability() {
		return 'edit_posts';
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
		$id = $this->parse_required_id( $arguments['id'] );
		return $this->rest_request( 'GET', '/wp/v2/posts/' . $id . '/meta' );
	}
}
