<?php
namespace Immens_MCP_Fortress\Tools\Search;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Search extends Base_Tool {

	public function get_name() {
		return 'wp_search';
	}

	public function get_description() {
		return 'Search across posts, pages, and other content types.';
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'search'   => array(
					'type'        => 'string',
					'description' => 'Search term',
				),
				'type'     => array(
					'type'        => 'string',
					'description' => 'Content type to search',
					'enum'        => array( 'post', 'page', 'any' ),
					'default'     => 'any',
				),
				'subtype'  => array(
					'type'        => 'string',
					'description' => 'Subtype (e.g. "post" or "page")',
				),
				'page'     => array(
					'type'        => 'integer',
					'description' => 'Page number',
					'default'     => 1,
				),
				'per_page' => array(
					'type'        => 'integer',
					'description' => 'Results per page',
					'default'     => 10,
				),
			),
			'required'   => array( 'search' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'search' ) );

		$args = array_merge(
			array(
				'type'     => 'any',
				'page'     => 1,
				'per_page' => 10,
			),
			$arguments
		);

		$params = array(
			'search'    => $args['search'],
			'type'      => $args['type'],
			'page'      => (int) $args['page'],
			'per_page'  => (int) $args['per_page'],
			'context'   => 'edit',
		);

		if ( ! empty( $args['subtype'] ) ) {
			$params['subtype'] = $args['subtype'];
		}

		$request  = new \WP_REST_Request( 'GET', '/wp/v2/search' );
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
			'results'     => $response->get_data(),
			'total'       => isset( $headers['X-WP-Total'] ) ? (int) $headers['X-WP-Total'] : 0,
			'total_pages' => isset( $headers['X-WP-TotalPages'] ) ? (int) $headers['X-WP-TotalPages'] : 0,
			'page'        => (int) $args['page'],
			'per_page'    => (int) $args['per_page'],
		);
	}
}
