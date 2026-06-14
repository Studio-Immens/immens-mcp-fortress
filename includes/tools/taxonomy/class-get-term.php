<?php
namespace Immens_MCP_Fortress\Tools\Taxonomy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Term extends Base_Tool {

	public function get_name() {
		return 'wp_get_term';
	}

	public function get_description() {
		return 'Get a single term by ID and taxonomy.';
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'term_id'  => array(
					'type'        => 'integer',
					'description' => 'Term ID',
				),
				'taxonomy' => array(
					'type'        => 'string',
					'description' => 'Taxonomy name (e.g. category, post_tag)',
				),
			),
			'required' => array( 'term_id', 'taxonomy' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'term_id', 'taxonomy' ) );

		$term_id = $this->parse_required_id( $arguments['term_id'] );
		$term    = get_term( $term_id, $arguments['taxonomy'] );

		if ( is_wp_error( $term ) ) {
			throw new \RuntimeException( $term->get_error_message() ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		if ( null === $term ) {
			throw new \RuntimeException( 'Term not found.' );
		}

		return array(
			'success' => true,
			'data'    => $term,
		);
	}
}
