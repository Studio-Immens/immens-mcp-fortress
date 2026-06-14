<?php
namespace Immens_MCP_Fortress\Tools\Taxonomy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Create_Category extends Base_Tool {

	public function get_name() {
		return 'wp_create_category';
	}

	public function get_description() {
		return 'Create a new category.';
	}

	public function get_required_capability() {
		return 'manage_categories';
	}

	public function get_annotations() {
		return array(
			'title'           => $this->get_title(),
			'readOnlyHint'    => false,
			'destructiveHint' => false,
			'openWorldHint'   => true,
		);
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
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
			'required'   => array( 'name' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'name' ) );

		$params = array( 'name' => $arguments['name'] );

		if ( isset( $arguments['slug'] ) ) {
			$params['slug'] = $arguments['slug'];
		}
		if ( isset( $arguments['parent'] ) ) {
			$params['parent'] = (int) $arguments['parent'];
		}
		if ( isset( $arguments['description'] ) ) {
			$params['description'] = $arguments['description'];
		}

		return $this->rest_request( 'POST', '/wp/v2/categories', $params );
	}
}
