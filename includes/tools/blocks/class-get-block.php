<?php
namespace Immens_MCP_Fortress\Tools\Blocks;

use Immens_MCP_Fortress\Tools\Base_Tool;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Get_Block extends Base_Tool {

	public function get_name() {
		return 'wp_get_block';
	}

	public function get_description() {
		return 'Get a single reusable block (synced pattern) by ID.';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'block_id' => array(
					'type'        => 'integer',
					'description' => 'The ID of the reusable block to retrieve.',
				),
			),
			'required'   => array( 'block_id' ),
		);
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_annotations() {
		return array(
			'title'           => 'Get Block',
			'readOnlyHint'    => true,
			'destructiveHint' => false,
			'openWorldHint'   => false,
		);
	}

	public function get_category() {
		return 'blocks';
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'block_id' ) );
		$block_id = $this->parse_required_id( $arguments['block_id'], 'block_id' );

		$block = $this->rest_request( 'GET', '/wp/v2/blocks/' . $block_id, array(
			'context' => 'edit',
		) );

		return array(
			'id'      => $block['id'],
			'title'   => isset( $block['title']['rendered'] ) ? $block['title']['rendered'] : ( isset( $block['title']['raw'] ) ? $block['title']['raw'] : '' ),
			'content' => isset( $block['content']['rendered'] ) ? $block['content']['rendered'] : ( isset( $block['content']['raw'] ) ? $block['content']['raw'] : '' ),
			'status'  => $block['status'],
			'slug'    => $block['slug'],
			'date'    => $block['date'],
		);
	}
}
