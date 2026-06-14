<?php
namespace Immens_MCP_Fortress\Tools\Media;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Update_Media extends Base_Tool {

	public function get_name() {
		return 'wp_update_media';
	}

	public function get_description() {
		return 'Update a media item\'s title, alt text, caption, and description.';
	}

	public function get_required_capability() {
		return 'edit_posts';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'id'          => array(
					'type'        => 'integer',
					'description' => 'Media item ID',
				),
				'title'       => array(
					'type'        => 'string',
					'description' => 'New title',
				),
				'alt_text'    => array(
					'type'        => 'string',
					'description' => 'Alt text',
				),
				'caption'     => array(
					'type'        => 'string',
					'description' => 'Caption',
				),
				'description' => array(
					'type'        => 'string',
					'description' => 'Description',
				),
			),
			'required'   => array( 'id' ),
		);
	}

	public function execute( array $arguments ) {
		$id = $this->parse_required_id( $arguments['id'] );

		$params = array();

		if ( isset( $arguments['title'] ) ) {
			$params['title'] = $arguments['title'];
		}
		if ( isset( $arguments['alt_text'] ) ) {
			$params['alt_text'] = $arguments['alt_text'];
		}
		if ( isset( $arguments['caption'] ) ) {
			$params['caption'] = $arguments['caption'];
		}
		if ( isset( $arguments['description'] ) ) {
			$params['description'] = $arguments['description'];
		}

		return $this->rest_request( 'POST', '/wp/v2/media/' . $id, $params );
	}
}
