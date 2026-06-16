<?php
namespace Immens_MCP_Fortress\Tools\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Update_Page extends Base_Tool {

	public function get_name() {
		return 'wp_update_page';
	}

	public function get_description() {
		return 'Update an existing page.';
	}

	public function get_required_capability() {
		return 'edit_pages';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'id'             => array(
					'type'        => 'integer',
					'description' => 'Page ID',
				),
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
					'enum'        => array( 'publish', 'future', 'draft', 'pending', 'private', 'trash' ),
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
			'required'   => array( 'id' ),
		);
	}

	public function execute( array $arguments ) {
		$id = $this->parse_required_id( $arguments['id'] );
		$this->validate_title_length( isset( $arguments['title'] ) ? $arguments['title'] : null );

		$params = array();

		$fields = array( 'title', 'content', 'status', 'parent', 'template', 'slug', 'excerpt' );
		foreach ( $fields as $field ) {
			if ( isset( $arguments[ $field ] ) ) {
				$params[ $field ] = $arguments[ $field ];
			}
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

		return $this->rest_request( 'POST', '/wp/v2/pages/' . $id, $params );
	}
}
