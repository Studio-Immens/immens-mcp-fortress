<?php
namespace Immens_MCP_Fortress\Tools\Blocks;

use Immens_MCP_Fortress\Tools\Base_Tool;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Create_Block extends Base_Tool {

	public function get_name() {
		return 'wp_create_block';
	}

	public function get_description() {
		return 'Create a new reusable block (synced pattern) with title and block markup content.';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'title'   => array(
					'type'        => 'string',
					'description' => 'The title of the reusable block.',
				),
				'content' => array(
					'type'        => 'string',
					'description' => 'Block markup content for the reusable block.',
				),
				'status'  => array(
					'type'        => 'string',
					'description' => 'Post status for the block.',
					'enum'        => array( 'publish', 'draft' ),
					'default'     => 'publish',
				),
				'slug'    => array(
					'type'        => 'string',
					'description' => 'Optional slug for the block.',
				),
			),
			'required'   => array( 'title', 'content' ),
		);
	}

	public function get_required_capability() {
		return 'edit_posts';
	}

	public function get_annotations() {
		return array(
			'title'           => 'Create Block',
			'readOnlyHint'    => false,
			'destructiveHint' => false,
			'openWorldHint'   => true,
		);
	}

	public function get_category() {
		return 'blocks';
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'title', 'content' ) );
		$this->validate_title_length( $arguments['title'] );

		$status = isset( $arguments['status'] ) ? $arguments['status'] : 'publish';

		$params = array(
			'title'   => $arguments['title'],
			'content' => $arguments['content'],
			'status'  => $status,
		);

		if ( isset( $arguments['slug'] ) && ! empty( $arguments['slug'] ) ) {
			$params['slug'] = $arguments['slug'];
		}

		$this->maybe_force_draft( $params );

		$block = $this->rest_request( 'POST', '/wp/v2/blocks', $params );

		return array(
			'id'      => $block['id'],
			'title'   => isset( $block['title']['rendered'] ) ? $block['title']['rendered'] : $params['title'],
			'status'  => $block['status'],
			'slug'    => $block['slug'],
			'date'    => $block['date'],
		);
	}
}
