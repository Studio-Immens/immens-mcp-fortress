<?php
namespace Immens_MCP_Fortress\Tools\Taxonomy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Update_Term_Meta extends Base_Tool {

	public function get_name() {
		return 'wp_update_term_meta';
	}

	public function get_description() {
		return 'Update term meta for a given key.';
	}

	public function get_required_capability() {
		return 'manage_categories';
	}

	public function get_annotations() {
		return array(
			'title'           => $this->get_title(),
			'readOnlyHint'    => false,
			'destructiveHint' => false,
			'openWorldHint'   => true,
		);
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'term_id'    => array(
					'type'        => 'integer',
					'description' => 'Term ID',
				),
				'meta_key'   => array(
					'type'        => 'string',
					'description' => 'Meta key',
				),
				'meta_value' => array(
					'type'        => 'string',
					'description' => 'Meta value',
				),
			),
			'required' => array( 'term_id', 'meta_key', 'meta_value' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'term_id', 'meta_key', 'meta_value' ) );

		$term_id = $this->parse_required_id( $arguments['term_id'] );

		$result = update_term_meta( $term_id, $arguments['meta_key'], $arguments['meta_value'] );

		if ( is_wp_error( $result ) ) {
			throw new \RuntimeException( $result->get_error_message() ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		return array(
			'success' => true,
			'data'    => array(
				'updated' => $result,
			),
		);
	}
}
