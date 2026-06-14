<?php
namespace Immens_MCP_Fortress\Tools\Media;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Delete_Media extends Base_Tool {

	public function get_name() {
		return 'wp_delete_media';
	}

	public function get_description() {
		return 'Permanently delete a media item.';
	}

	public function get_required_capability() {
		return 'delete_posts';
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
		$params = array( 'force' => true );
		return $this->rest_request( 'DELETE', '/wp/v2/media/' . $id, $params );
	}
}
