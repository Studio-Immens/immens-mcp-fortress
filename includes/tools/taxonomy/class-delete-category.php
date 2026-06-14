<?php
namespace Immens_MCP_Fortress\Tools\Taxonomy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Delete_Category extends Base_Tool {

	public function get_name() {
		return 'wp_delete_category';
	}

	public function get_description() {
		return 'Permanently delete a category.';
	}

	public function get_required_capability() {
		return 'manage_categories';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'id' => array(
					'type'        => 'integer',
					'description' => 'Category ID',
				),
			),
			'required'   => array( 'id' ),
		);
	}

	public function execute( array $arguments ) {
		$id     = $this->parse_required_id( $arguments['id'] );
		$params = array( 'force' => true );
		return $this->rest_request( 'DELETE', '/wp/v2/categories/' . $id, $params );
	}
}
