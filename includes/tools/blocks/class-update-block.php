<?php
namespace Immens_MCP_Fortress\Tools\Blocks;

use Immens_MCP_Fortress\Tools\Base_Tool;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Update_Block extends Base_Tool {

	public function get_name() {
		return 'wp_update_block';
	}

	public function get_description() {
		return 'Update an existing reusable block (synced pattern) by ID.';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'block_id' => array(
					'type'        => 'integer',
					'description' => 'The ID of the reusable block to update.',
				),
				'title'    => array(
					'type'        => 'string',
					'description' => 'New title for the block.',
				),
				'content'  => array(
					'type'        => 'string',
					'description' => 'New block markup content.',
				),
				'status'   => array(
					'type'        => 'string',
					'description' => 'New status for the block.',
				),
			),
			'required'   => array( 'block_id' ),
		);
	}

	public function get_required_capability() {
		return 'edit_posts';
	}

	public function get_annotations() {
		return array(
			'title'           => 'Update Block',
			'readOnlyHint'    => false,
			'destructiveHint' => false,
			'openWorldHint'   => true,
		);
	}

	public function get_category() {
		return 'blocks';
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'block_id' ) );
		$block_id = $this->parse_required_id( $arguments['block_id'], 'block_id' );

		$params = array();

		if ( isset( $arguments['title'] ) ) {
			$this->validate_title_length( $arguments['title'] );
			$params['title'] = $arguments['title'];
		}

		if ( isset( $arguments['content'] ) ) {
			$params['content'] = $arguments['content'];
		}

		if ( isset( $arguments['status'] ) ) {
			$params['status'] = $arguments['status'];
		}

		if ( empty( $params ) ) {
			throw new \InvalidArgumentException(
				'At least one of title, content, or status must be provided.'
			);
		}

		$block = $this->rest_request( 'POST', '/wp/v2/blocks/' . $block_id, $params );

		return array(
			'id'      => $block['id'],
			'title'   => isset( $block['title']['rendered'] ) ? $block['title']['rendered'] : '',
			'status'  => $block['status'],
			'slug'    => $block['slug'],
		);
	}
}
