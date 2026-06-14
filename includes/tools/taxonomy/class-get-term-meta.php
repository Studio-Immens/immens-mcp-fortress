<?php
namespace Immens_MCP_Fortress\Tools\Taxonomy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Term_Meta extends Base_Tool {

	public function get_name() {
		return 'wp_get_term_meta';
	}

	public function get_description() {
		return 'Get term meta. If meta_key is omitted, returns all meta for the term.';
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
				'meta_key' => array(
					'type'        => 'string',
					'description' => 'Specific meta key to retrieve (optional)',
				),
			),
			'required' => array( 'term_id' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'term_id' ) );

		$term_id = $this->parse_required_id( $arguments['term_id'] );

		if ( isset( $arguments['meta_key'] ) && '' !== $arguments['meta_key'] ) {
			$meta = get_term_meta( $term_id, $arguments['meta_key'], true );
		} else {
			$meta = get_term_meta( $term_id );
		}

		return array(
			'success' => true,
			'data'    => $meta,
		);
	}
}
