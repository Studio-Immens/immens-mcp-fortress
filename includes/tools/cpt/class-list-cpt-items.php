<?php
namespace Immens_MCP_Fortress\Tools\Cpt;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class List_Cpt_Items extends Base_Tool {

	public function get_name() {
		return 'wp_list_cpt_items';
	}

	public function get_description() {
		return 'List items for any custom post type. Specify the post_type parameter.';
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'post_type' => array(
					'type'        => 'string',
					'description' => 'Custom post type slug (e.g. "product", "event", "portfolio")',
				),
				'page'      => array(
					'type'        => 'integer',
					'description' => 'Page number',
					'default'     => 1,
				),
				'per_page'  => array(
					'type'        => 'integer',
					'description' => 'Items per page',
					'default'     => 10,
				),
				'search'    => array(
					'type'        => 'string',
					'description' => 'Search term',
				),
				'status'    => array(
					'type'        => 'string',
					'description' => 'Post status',
					'enum'        => array( 'publish', 'future', 'draft', 'pending', 'private', 'trash', 'auto-draft', 'inherit', 'any' ),
					'default'     => 'any',
				),
				'orderby'   => array(
					'type'        => 'string',
					'description' => 'Sort field',
					'default'     => 'date',
				),
				'order'     => array(
					'type'        => 'string',
					'description' => 'Sort direction',
					'enum'        => array( 'asc', 'desc' ),
					'default'     => 'desc',
				),
			),
			'required'   => array( 'post_type' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'post_type' ) );

		$post_type = sanitize_key( $arguments['post_type'] );

		if ( ! post_type_exists( $post_type ) ) {
			throw new \InvalidArgumentException(
				sprintf( 'Post type "%s" does not exist or is not registered.', $post_type )
			);
		}

		$args = array_merge(
			array(
				'page'     => 1,
				'per_page' => 10,
				'status'   => 'any',
				'orderby'  => 'date',
				'order'    => 'desc',
			),
			$arguments
		);

		$params = array(
			'page'     => (int) $args['page'],
			'per_page' => (int) $args['per_page'],
			'status'   => $args['status'],
			'orderby'  => $args['orderby'],
			'order'    => $args['order'],
			'context'  => 'edit',
		);

		if ( ! empty( $args['search'] ) ) {
			$params['search'] = $args['search'];
		}

		$request  = new \WP_REST_Request( 'GET', '/wp/v2/' . $post_type );
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
			'items'       => $response->get_data(),
			'post_type'   => $post_type,
			'total'       => isset( $headers['X-WP-Total'] ) ? (int) $headers['X-WP-Total'] : 0,
			'total_pages' => isset( $headers['X-WP-TotalPages'] ) ? (int) $headers['X-WP-TotalPages'] : 0,
			'page'        => (int) $args['page'],
			'per_page'    => (int) $args['per_page'],
		);
	}
}
