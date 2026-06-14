<?php
namespace Immens_MCP_Fortress\Tools\Media;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Media extends Base_Tool {

	public function get_name() {
		return 'wp_get_media';
	}

	public function get_description() {
		return 'Get a single media item by ID with edit context.';
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
					'description' => 'Media item ID',
				),
			),
			'required'   => array( 'id' ),
		);
	}

	public function execute( array $arguments ) {
		$id     = $this->parse_required_id( $arguments['id'] );
		$params = array( 'context' => 'edit' );
		return $this->rest_request( 'GET', '/wp/v2/media/' . $id, $params );
	}
}
