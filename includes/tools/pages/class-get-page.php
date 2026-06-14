<?php
namespace Immens_MCP_Fortress\Tools\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Page extends Base_Tool {

	public function get_name() {
		return 'wp_get_page';
	}

	public function get_description() {
		return 'Get a single page by ID with edit context.';
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
					'description' => 'Page ID',
				),
			),
			'required'   => array( 'id' ),
		);
	}

	public function execute( array $arguments ) {
		$id = $this->parse_required_id( $arguments['id'] );
		$params = array( 'context' => 'edit' );
		return $this->rest_request( 'GET', '/wp/v2/pages/' . $id, $params );
	}
}
