<?php
namespace Immens_MCP_Fortress\Tools\Taxonomy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Create_Term extends Base_Tool {

	public function get_name() {
		return 'wp_create_term';
	}

	public function get_description() {
		return 'Create a new term in any taxonomy.';
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
				'name'        => array(
					'type'        => 'string',
					'description' => 'Term name',
				),
				'taxonomy'    => array(
					'type'        => 'string',
					'description' => 'Taxonomy name (e.g. category, post_tag)',
				),
				'slug'        => array(
					'type'        => 'string',
					'description' => 'Term slug',
				),
				'description' => array(
					'type'        => 'string',
					'description' => 'Term description',
				),
				'parent'      => array(
					'type'        => 'integer',
					'description' => 'Parent term ID',
				),
			),
			'required'   => array( 'name', 'taxonomy' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'name', 'taxonomy' ) );

		$args = array();

		if ( isset( $arguments['slug'] ) ) {
			$args['slug'] = $arguments['slug'];
		}
		if ( isset( $arguments['description'] ) ) {
			$args['description'] = $arguments['description'];
		}
		if ( isset( $arguments['parent'] ) ) {
			$args['parent'] = (int) $arguments['parent'];
		}

		$result = wp_insert_term( $arguments['name'], $arguments['taxonomy'], $args );

		if ( is_wp_error( $result ) ) {
			throw new \RuntimeException( $result->get_error_message() ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		return array(
			'success' => true,
			'data'    => $result,
		);
	}
}
