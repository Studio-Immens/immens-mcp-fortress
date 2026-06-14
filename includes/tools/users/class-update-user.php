<?php
namespace Immens_MCP_Fortress\Tools\Users;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Update_User extends Base_Tool {

	public function get_name() {
		return 'wp_update_user';
	}

	public function get_description() {
		return 'Update a WordPress user.';
	}

	public function get_required_capability() {
		return 'promote_users';
	}

	public function get_category() {
		return 'users';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'user_id'    => array(
					'type'        => 'integer',
					'description' => 'User ID to update',
				),
				'email'      => array(
					'type'        => 'string',
					'description' => 'User email address',
				),
				'first_name' => array(
					'type'        => 'string',
					'description' => 'User first name',
				),
				'last_name'  => array(
					'type'        => 'string',
					'description' => 'User last name',
				),
				'role'       => array(
					'type'        => 'string',
					'description' => 'User role',
				),
				'password'   => array(
					'type'        => 'string',
					'description' => 'User password',
				),
			),
			'required'   => array( 'user_id' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'user_id' ) );

		$id      = $this->parse_required_id( $arguments['user_id'], 'User ID' );
		$userdata = array( 'ID' => $id );

		if ( isset( $arguments['email'] ) ) {
			$userdata['user_email'] = $arguments['email'];
		}
		if ( isset( $arguments['first_name'] ) ) {
			$userdata['first_name'] = $arguments['first_name'];
		}
		if ( isset( $arguments['last_name'] ) ) {
			$userdata['last_name'] = $arguments['last_name'];
		}
		if ( isset( $arguments['role'] ) ) {
			$userdata['role'] = $arguments['role'];
		}
		if ( isset( $arguments['password'] ) ) {
			$userdata['user_pass'] = $arguments['password'];
		}

		$result = wp_update_user( $userdata );
		if ( is_wp_error( $result ) ) {
			throw new \RuntimeException( $result->get_error_message() ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		$params = array( 'context' => 'edit' );
		return $this->rest_request( 'GET', '/wp/v2/users/' . $id, $params );
	}
}
