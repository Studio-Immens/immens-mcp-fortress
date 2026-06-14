<?php
namespace Immens_MCP_Fortress\Tools\Posts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Update_Post extends Base_Tool {

	public function get_name() {
		return 'wp_update_post';
	}

	public function get_description() {
		return 'Update an existing post by ID (PATCH semantics). Supports title, content, status, excerpt, categories, tags, featured_media, slug, date, format, author, comment_status, and sticky.';
	}

	public function get_required_capability() {
		return 'edit_posts';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'id'             => array(
					'type'        => 'integer',
					'description' => 'Post ID',
				),
				'title'          => array(
					'type'        => 'string',
					'description' => 'Post title',
				),
				'content'        => array(
					'type'        => 'string',
					'description' => 'Post content',
				),
				'status'         => array(
					'type'        => 'string',
					'description' => 'Post status',
					'enum'        => array( 'publish', 'draft', 'pending', 'private', 'future' ),
				),
				'excerpt'        => array(
					'type'        => 'string',
					'description' => 'Post excerpt',
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
				'date'           => array(
					'type'        => 'string',
					'description' => 'Post date (ISO 8601)',
				),
				'format'         => array(
					'type'        => 'string',
					'description' => 'Post format',
					'enum'        => array( 'standard', 'aside', 'chat', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio' ),
				),
				'author'         => array(
					'type'        => 'integer',
					'description' => 'Author user ID',
				),
				'comment_status' => array(
					'type'        => 'string',
					'description' => 'Comment status',
					'enum'        => array( 'open', 'closed' ),
				),
				'sticky'         => array(
					'type'        => 'boolean',
					'description' => 'Make post sticky',
				),
			),
			'required'   => array( 'id' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'id' ) );
		$id = $this->parse_required_id( $arguments['id'], 'Post ID' );

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

		if ( isset( $arguments['date'] ) ) {
			$params['date'] = $arguments['date'];
		}

		if ( isset( $arguments['format'] ) ) {
			$params['format'] = $arguments['format'];
		}

		if ( isset( $arguments['author'] ) ) {
			$params['author'] = (int) $arguments['author'];
		}

		if ( isset( $arguments['comment_status'] ) ) {
			$params['comment_status'] = $arguments['comment_status'];
		}

		if ( isset( $arguments['sticky'] ) ) {
			$params['sticky'] = (bool) $arguments['sticky'];
		}

		if ( empty( $params ) ) {
			throw new \InvalidArgumentException( 'At least one field must be provided to update.' );
		}

		$result = $this->rest_request( 'POST', '/wp/v2/posts/' . $id, $params );
		$this->invalidate_post_cache( $id );

		return $result;
	}
}
