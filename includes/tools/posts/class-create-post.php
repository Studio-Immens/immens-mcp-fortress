<?php
namespace Immens_MCP_Fortress\Tools\Posts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Create_Post extends Base_Tool {

	public function get_name() {
		return 'wp_create_post';
	}

	public function get_description() {
		return 'Create a new post with title, content, excerpt, status, categories, tags, featured media, and slug.';
	}

	public function get_required_capability() {
		return 'edit_posts';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'title'          => array(
					'type'        => 'string',
					'description' => 'Post title',
				),
				'content'        => array(
					'type'        => 'string',
					'description' => 'Post content',
				),
				'excerpt'        => array(
					'type'        => 'string',
					'description' => 'Post excerpt',
				),
				'status'         => array(
					'type'        => 'string',
					'description' => 'Post status',
					'enum'        => array( 'publish', 'draft', 'pending', 'private', 'future' ),
					'default'     => 'draft',
				),
				'categories'     => array(
					'type'        => 'array',
					'description' => 'Category IDs',
					'items'       => array( 'type' => 'integer' ),
				),
				'tags'           => array(
					'type'        => 'array',
					'description' => 'Tag IDs',
					'items'       => array( 'type' => 'integer' ),
				),
				'featured_media' => array(
					'type'        => 'integer',
					'description' => 'Featured image attachment ID',
				),
				'slug'           => array(
					'type'        => 'string',
					'description' => 'Post slug',
				),
				'convert_to_blocks' => array(
					'type'        => 'boolean',
					'description' => 'Convert HTML content to Gutenberg blocks before saving. Requires extended MCP tools.',
				),
				'builder' => array(
					'type'        => 'string',
					'description' => 'Block builder to target for conversion: auto, greenshift, stackable, core.',
					'enum'        => array( 'auto', 'greenshift', 'stackable', 'core' ),
				),
				'block_config' => array(
					'type'        => 'object',
					'description' => 'Advanced block configuration (e.g. animations). Requires extended MCP tools.',
				),
			),
			'required'   => array( 'title' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'title' ) );
		$this->validate_title_length( $arguments['title'] );

		$params = array(
			'title'  => $arguments['title'],
			'status' => isset( $arguments['status'] ) ? $arguments['status'] : 'draft',
		);

		if ( isset( $arguments['content'] ) ) {
			$params['content'] = $arguments['content'];
		}

		if ( isset( $arguments['excerpt'] ) ) {
			$params['excerpt'] = $arguments['excerpt'];
		}

		if ( isset( $arguments['categories'] ) ) {
			$params['categories'] = $this->parse_json_param( $arguments['categories'], 'categories' );
		}

		if ( isset( $arguments['tags'] ) ) {
			$params['tags'] = $this->parse_json_param( $arguments['tags'], 'tags' );
		}

		if ( isset( $arguments['featured_media'] ) ) {
			$params['featured_media'] = (int) $arguments['featured_media'];
		}

		if ( isset( $arguments['slug'] ) ) {
			$params['slug'] = $arguments['slug'];
		}

		if ( ! empty( $arguments['convert_to_blocks'] ) ) {
			$params['imf_convert_to_blocks'] = true;
		}
		if ( isset( $arguments['builder'] ) ) {
			$params['imf_builder'] = $arguments['builder'];
		}
		if ( isset( $arguments['block_config'] ) ) {
			$params['imf_block_config'] = $arguments['block_config'];
		}

		$this->maybe_force_draft( $params );

		$result = $this->rest_request( 'POST', '/wp/v2/posts', $params );

		if ( isset( $result['id'] ) ) {
			$this->invalidate_post_cache( $result['id'] );
		}

		return $result;
	}
}
