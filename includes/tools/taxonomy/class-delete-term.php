<?php
namespace Immens_MCP_Fortress\Tools\Taxonomy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Delete_Term extends Base_Tool {

	public function get_name() {
		return 'wp_delete_term';
	}

	public function get_description() {
		return 'Delete a term by ID.';
	}

	public function get_required_capability() {
		return 'manage_categories';
	}

	public function get_annotations() {
		return array(
			'title'           => $this->get_title(),
			'readOnlyHint'    => false,
			'destructiveHint' => true,
			'openWorldHint'   => false,
		);
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

		$result = wp_delete_term( $term_id, $arguments['taxonomy'] );

		if ( is_wp_error( $result ) ) {
			throw new \RuntimeException( $result->get_error_message() ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		if ( false === $result ) {
			throw new \RuntimeException( 'Failed to delete term.' );
		}

		return array(
			'success' => true,
			'data'    => array(
				'deleted' => true,
				'term_id' => $term_id,
			),
		);
	}
}
