<?php
namespace Immens_MCP_Fortress\Tools\Taxonomy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Update_Tag extends Base_Tool {

	public function get_name() {
		return 'wp_update_tag';
	}

	public function get_description() {
		return 'Update an existing tag.';
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
					'description' => 'Tag ID',
				),
				'name'        => array(
					'type'        => 'string',
					'description' => 'Tag name',
				),
				'slug'        => array(
					'type'        => 'string',
					'description' => 'Tag slug',
				),
				'description' => array(
					'type'        => 'string',
					'description' => 'Tag description',
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

		return $this->rest_request( 'POST', '/wp/v2/tags/' . $id, $params );
	}
}
