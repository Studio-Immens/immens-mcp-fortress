<?php
namespace Immens_MCP_Fortress\Tools\Taxonomy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Update_Category extends Base_Tool {

	public function get_name() {
		return 'wp_update_category';
	}

	public function get_description() {
		return 'Update an existing category.';
	}

	public function get_required_capability() {
		return 'manage_categories';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'id'          => array(
					'type'        => 'integer',
					'description' => 'Category ID',
				),
				'name'        => array(
					'type'        => 'string',
					'description' => 'Category name',
				),
				'slug'        => array(
					'type'        => 'string',
					'description' => 'Category slug',
				),
				'parent'      => array(
					'type'        => 'integer',
					'description' => 'Parent category ID',
				),
				'description' => array(
					'type'        => 'string',
					'description' => 'Category description',
				),
			),
			'required'   => array( 'id' ),
		);
	}

	public function execute( array $arguments ) {
		$id     = $this->parse_required_id( $arguments['id'] );
		$params = array();

		$fields = array( 'name', 'slug', 'description' );
		foreach ( $fields as $field ) {
			if ( isset( $arguments[ $field ] ) ) {
				$params[ $field ] = $arguments[ $field ];
			}
		}

		if ( isset( $arguments['parent'] ) ) {
			$params['parent'] = (int) $arguments['parent'];
		}

		return $this->rest_request( 'POST', '/wp/v2/categories/' . $id, $params );
	}
}
