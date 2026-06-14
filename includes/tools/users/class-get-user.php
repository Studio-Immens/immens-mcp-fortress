<?php
namespace Immens_MCP_Fortress\Tools\Users;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_User extends Base_Tool {

	public function get_name() {
		return 'wp_get_user';
	}

	public function get_description() {
		return 'Get a single user by ID.';
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
					'description' => 'User ID',
				),
			),
			'required'   => array( 'id' ),
		);
	}

	public function execute( array $arguments ) {
		$id     = $this->parse_required_id( $arguments['id'] );
		$params = array( 'context' => 'edit' );
		return $this->rest_request( 'GET', '/wp/v2/users/' . $id, $params );
	}
}
