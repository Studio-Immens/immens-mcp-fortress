<?php
namespace Immens_MCP_Fortress\Tools\Users;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Delete_User extends Base_Tool {

	public function get_name() {
		return 'wp_delete_user';
	}

	public function get_description() {
		return 'Delete a user.';
	}

	public function get_required_capability() {
		return 'delete_users';
	}

	public function get_category() {
		return 'users';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'user_id'     => array(
					'type'        => 'integer',
					'description' => 'User ID to delete',
				),
				'reassign_id' => array(
					'type'        => 'integer',
					'description' => 'User ID to reassign content to (default: 1)',
				),
			),
			'required'   => array( 'user_id' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'user_id' ) );

		$id       = $this->parse_required_id( $arguments['user_id'], 'User ID' );
		$reassign = isset( $arguments['reassign_id'] ) ? (int) $arguments['reassign_id'] : 1;

		$result = wp_delete_user( $id, $reassign );
		if ( ! $result ) {
			throw new \RuntimeException( 'Failed to delete user.' );
		}

		return array(
			'deleted' => true,
			'user_id' => $id,
		);
	}
}
