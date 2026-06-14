<?php
namespace Immens_MCP_Fortress\Tools\Users;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Create_User extends Base_Tool {

	public function get_name() {
		return 'wp_create_user';
	}

	public function get_description() {
		return 'Create a WordPress user.';
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
				'email'      => array(
					'type'        => 'string',
					'description' => 'User email address',
				),
				'username'   => array(
					'type'        => 'string',
					'description' => 'User login username',
				),
				'password'   => array(
					'type'        => 'string',
					'description' => 'User password',
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
					'default'     => 'subscriber',
				),
			),
			'required'   => array( 'email', 'username', 'password' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'email', 'username', 'password' ) );

		$userdata = array(
			'user_email' => $arguments['email'],
			'user_login' => $arguments['username'],
			'user_pass'  => $arguments['password'],
			'role'       => isset( $arguments['role'] ) ? $arguments['role'] : 'subscriber',
		);

		if ( isset( $arguments['first_name'] ) ) {
			$userdata['first_name'] = $arguments['first_name'];
		}
		if ( isset( $arguments['last_name'] ) ) {
			$userdata['last_name'] = $arguments['last_name'];
		}

		$user_id = wp_insert_user( $userdata );
		if ( is_wp_error( $user_id ) ) {
			throw new \RuntimeException( $user_id->get_error_message() ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		$params = array( 'context' => 'edit' );
		return $this->rest_request( 'GET', '/wp/v2/users/' . $user_id, $params );
	}
}
