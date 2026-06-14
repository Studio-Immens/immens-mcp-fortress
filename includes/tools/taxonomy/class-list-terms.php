<?php
namespace Immens_MCP_Fortress\Tools\Taxonomy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class List_Terms extends Base_Tool {

	public function get_name() {
		return 'wp_list_terms';
	}

	public function get_description() {
		return 'List terms from any taxonomy with pagination and search.';
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'taxonomy'   => array(
					'type'        => 'string',
					'description' => 'Taxonomy name (e.g. category, post_tag)',
				),
				'per_page'   => array(
					'type'        => 'integer',
					'description' => 'Terms per page',
					'default'     => 50,
				),
				'page'       => array(
					'type'        => 'integer',
					'description' => 'Page number',
					'default'     => 1,
				),
				'search'     => array(
					'type'        => 'string',
					'description' => 'Search term',
				),
				'hide_empty' => array(
					'type'        => 'boolean',
					'description' => 'Hide terms with no posts',
					'default'     => false,
				),
			),
			'required'   => array( 'taxonomy' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'taxonomy' ) );

		$args = array_merge(
			array(
				'per_page'   => 50,
				'page'       => 1,
				'hide_empty' => false,
			),
			$arguments
		);

		$params = array(
			'taxonomy'   => $args['taxonomy'],
			'hide_empty' => (bool) $args['hide_empty'],
			'offset'     => ( (int) $args['page'] - 1 ) * (int) $args['per_page'],
			'number'     => (int) $args['per_page'],
		);

		if ( ! empty( $args['search'] ) ) {
			$params['search'] = $args['search'];
		}

		$terms = get_terms( $params );

		if ( is_wp_error( $terms ) ) {
			throw new \RuntimeException( $terms->get_error_message() ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		return array(
			'success' => true,
			'data'    => $terms,
		);
	}
}
