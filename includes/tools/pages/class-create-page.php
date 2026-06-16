<?php
namespace Immens_MCP_Fortress\Tools\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Create_Page extends Base_Tool {

	public function get_name() {
		return 'wp_create_page';
	}

	public function get_description() {
		return 'Create a new page.';
	}

	public function get_required_capability() {
		return 'edit_pages';
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
				'title'          => array(
					'type'        => 'string',
					'description' => 'Page title',
				),
				'content'        => array(
					'type'        => 'string',
					'description' => 'Page content (HTML)',
				),
				'status'         => array(
					'type'        => 'string',
					'description' => 'Page status',
					'enum'        => array( 'publish', 'future', 'draft', 'pending', 'private' ),
					'default'     => 'draft',
				),
				'parent'         => array(
					'type'        => 'integer',
					'description' => 'Parent page ID',
				),
				'template'       => array(
					'type'        => 'string',
					'description' => 'Page template filename',
				),
				'slug'           => array(
					'type'        => 'string',
					'description' => 'Page slug',
				),
				'excerpt'        => array(
					'type'        => 'string',
					'description' => 'Page excerpt',
				),
				'featured_media' => array(
					'type'        => 'integer',
					'description' => 'Featured media attachment ID',
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
		$this->validate_title_length( isset( $arguments['title'] ) ? $arguments['title'] : null );

		if ( ! isset( $arguments['slug'] ) && ! empty( $arguments['title'] ) ) {
			$arguments['slug'] = sanitize_title( $arguments['title'] );
		}

		$params = array(
			'title'  => $arguments['title'],
			'status' => isset( $arguments['status'] ) ? $arguments['status'] : 'draft',
		);

		if ( isset( $arguments['content'] ) ) {
			$params['content'] = $arguments['content'];
		}
		if ( isset( $arguments['parent'] ) ) {
			$params['parent'] = (int) $arguments['parent'];
		}
		if ( isset( $arguments['template'] ) ) {
			$params['template'] = $arguments['template'];
		}
		if ( isset( $arguments['slug'] ) ) {
			$params['slug'] = $arguments['slug'];
		}
		if ( isset( $arguments['excerpt'] ) ) {
			$params['excerpt'] = $arguments['excerpt'];
		}
		if ( isset( $arguments['featured_media'] ) ) {
			$params['featured_media'] = (int) $arguments['featured_media'];
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

		return $this->rest_request( 'POST', '/wp/v2/pages', $params );
	}
}
