<?php
namespace Immens_MCP_Fortress\Tools\Users;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class List_Users extends Base_Tool {

	public function get_name() {
		return 'wp_list_users';
	}

	public function get_description() {
		return 'List users with pagination, search, and role filter.';
	}

	public function get_required_capability() {
		return 'list_users';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'page'     => array(
					'type'        => 'integer',
					'description' => 'Page number',
					'default'     => 1,
				),
				'per_page' => array(
					'type'        => 'integer',
					'description' => 'Users per page',
					'default'     => 10,
				),
				'search'   => array(
					'type'        => 'string',
					'description' => 'Search term',
				),
				'roles'    => array(
					'type'        => 'array',
					'description' => 'Filter by role(s)',
					'items'       => array( 'type' => 'string' ),
				),
				'orderby'  => array(
					'type'        => 'string',
					'description' => 'Sort field',
					'default'     => 'name',
				),
				'order'    => array(
					'type'        => 'string',
					'description' => 'Sort direction',
					'enum'        => array( 'asc', 'desc' ),
					'default'     => 'asc',
				),
			),
		);
	}

	public function execute( array $arguments ) {
		$args = array_merge(
			array(
				'page'     => 1,
				'per_page' => 10,
				'orderby'  => 'name',
				'order'    => 'asc',
			),
			$arguments
		);

		$params = array(
			'page'     => (int) $args['page'],
			'per_page' => (int) $args['per_page'],
			'orderby'  => $args['orderby'],
			'order'    => $args['order'],
			'context'  => 'edit',
		);

		if ( ! empty( $args['search'] ) ) {
			$params['search'] = $args['search'];
		}
		if ( ! empty( $args['roles'] ) ) {
			$params['roles'] = (array) $args['roles'];
		}

		$request  = new \WP_REST_Request( 'GET', '/wp/v2/users' );
		foreach ( $params as $key => $value ) {
			$request->set_param( $key, $value );
		}
		$response = rest_do_request( $request );

		if ( $response->is_error() ) {
			$error = $response->as_error();
			throw new \RuntimeException( $error->get_error_message() ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		$headers = $response->get_headers();

		return array(
			'users'       => $response->get_data(),
			'total'       => isset( $headers['X-WP-Total'] ) ? (int) $headers['X-WP-Total'] : 0,
			'total_pages' => isset( $headers['X-WP-TotalPages'] ) ? (int) $headers['X-WP-TotalPages'] : 0,
			'page'        => (int) $args['page'],
			'per_page'    => (int) $args['per_page'],
		);
	}
}
