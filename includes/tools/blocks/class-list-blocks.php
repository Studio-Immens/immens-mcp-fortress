<?php
namespace Immens_MCP_Fortress\Tools\Blocks;

use Immens_MCP_Fortress\Tools\Base_Tool;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class List_Blocks extends Base_Tool {

	public function get_name() {
		return 'wp_list_blocks';
	}

	public function get_description() {
		return 'List all reusable blocks (synced patterns / wp_block post type). Returns id, title, status, and date for each.';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'per_page' => array(
					'type'        => 'integer',
					'description' => 'Number of blocks to return per page.',
					'default'     => 10,
				),
				'page'     => array(
					'type'        => 'integer',
					'description' => 'Page number of results.',
					'default'     => 1,
				),
				'search'   => array(
					'type'        => 'string',
					'description' => 'Search term to filter blocks by title.',
				),
			),
			'required'   => array(),
		);
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_annotations() {
		return array(
			'title'           => 'List Blocks',
			'readOnlyHint'    => true,
			'destructiveHint' => false,
			'openWorldHint'   => false,
		);
	}

	public function get_category() {
		return 'blocks';
	}

	public function execute( array $arguments ) {
		$per_page = isset( $arguments['per_page'] ) ? (int) $arguments['per_page'] : 10;
		$page     = isset( $arguments['page'] ) ? (int) $arguments['page'] : 1;

		$params = array(
			'per_page' => $per_page,
			'page'     => $page,
		);

		if ( isset( $arguments['search'] ) && ! empty( $arguments['search'] ) ) {
			$params['search'] = $arguments['search'];
		}

		$blocks = $this->rest_request( 'GET', '/wp/v2/blocks', $params );

		$result = array();
		foreach ( $blocks as $block ) {
			$result[] = array(
				'id'     => $block['id'],
				'title'  => isset( $block['title']['rendered'] ) ? $block['title']['rendered'] : '',
				'status' => $block['status'],
				'date'   => $block['date'],
			);
		}

		return $result;
	}
}
