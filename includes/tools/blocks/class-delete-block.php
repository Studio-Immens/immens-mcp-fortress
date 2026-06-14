<?php
namespace Immens_MCP_Fortress\Tools\Blocks;

use Immens_MCP_Fortress\Tools\Base_Tool;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Delete_Block extends Base_Tool {

	public function get_name() {
		return 'wp_delete_block';
	}

	public function get_description() {
		return 'Delete a reusable block (synced pattern) by ID.';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'block_id' => array(
					'type'        => 'integer',
					'description' => 'The ID of the reusable block to delete.',
				),
			),
			'required'   => array( 'block_id' ),
		);
	}

	public function get_required_capability() {
		return 'delete_posts';
	}

	public function get_annotations() {
		return array(
			'title'           => 'Delete Block',
			'readOnlyHint'    => false,
			'destructiveHint' => true,
			'openWorldHint'   => false,
		);
	}

	public function get_category() {
		return 'blocks';
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'block_id' ) );
		$block_id = $this->parse_required_id( $arguments['block_id'], 'block_id' );

		$result = $this->rest_request( 'DELETE', '/wp/v2/blocks/' . $block_id, array(
			'force' => true,
		) );

		return array(
			'deleted'  => true,
			'block_id' => $block_id,
			'previous' => isset( $result['id'] ) ? $result['id'] : $block_id,
		);
	}
}
