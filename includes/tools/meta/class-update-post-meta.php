<?php
namespace Immens_MCP_Fortress\Tools\Meta;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Update_Post_Meta extends Base_Tool {

	public function get_name() {
		return 'wp_update_post_meta';
	}

	public function get_description() {
		return 'Update or create post meta.';
	}

	public function get_required_capability() {
		return 'edit_posts';
	}

	public function get_category() {
		return 'meta';
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
				'post_id'    => array(
					'type'        => 'integer',
					'description' => 'Post ID',
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
			'required'   => array( 'post_id', 'meta_key', 'meta_value' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'post_id', 'meta_key', 'meta_value' ) );

		$post_id   = $this->parse_required_id( $arguments['post_id'] );
		$meta_key  = $arguments['meta_key'];
		$meta_value = $arguments['meta_value'];

		$result = update_post_meta( $post_id, $meta_key, $meta_value );

		if ( false === $result ) {
			throw new \RuntimeException(
				sprintf( 'Failed to update meta key "%s" for post %d.', $meta_key, $post_id )
) ; // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		$this->invalidate_post_cache( $post_id );

		return array(
			'success' => true,
			'post_id' => $post_id,
			'meta_key' => $meta_key,
		);
	}
}
