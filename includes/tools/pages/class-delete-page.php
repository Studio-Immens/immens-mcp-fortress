<?php
namespace Immens_MCP_Fortress\Tools\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Delete_Page extends Base_Tool {

	public function get_name() {
		return 'wp_delete_page';
	}

	public function get_description() {
		return 'Delete a page permanently.';
	}

	public function get_required_capability() {
		return 'delete_pages';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'id' => array(
					'type'        => 'integer',
					'description' => 'Page ID',
				),
			),
			'required'   => array( 'id' ),
		);
	}

	public function execute( array $arguments ) {
		$id = $this->parse_required_id( $arguments['id'] );
		$params = array( 'force' => true );
		return $this->rest_request( 'DELETE', '/wp/v2/pages/' . $id, $params );
	}
}
