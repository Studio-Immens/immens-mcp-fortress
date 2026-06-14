<?php
namespace Immens_MCP_Fortress\Tools\Blocks;

use Immens_MCP_Fortress\Tools\Base_Tool;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Get_Block_Types extends Base_Tool {

	public function get_name() {
		return 'wp_get_block_types';
	}

	public function get_description() {
		return 'List all registered Gutenberg block types with their name, title, description, category, and attributes.';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => new \stdClass(),
		);
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_category() {
		return 'blocks';
	}

	public function get_annotations() {
		return array(
			'title'           => 'Get Block Types',
			'readOnlyHint'    => true,
			'destructiveHint' => false,
			'openWorldHint'   => false,
		);
	}

	public function execute( array $arguments ) {
		if ( ! class_exists( 'WP_Block_Type_Registry' ) ) {
			throw new \RuntimeException( 'WP_Block_Type_Registry not available.' );
		}

		$registry = \WP_Block_Type_Registry::get_instance();
		$blocks   = $registry->get_all_registered();

		$result = array();
		foreach ( $blocks as $name => $block_type ) {
			$entry = array(
				'name'        => $name,
				'title'       => isset( $block_type->title ) ? $block_type->title : '',
				'description' => isset( $block_type->description ) ? $block_type->description : '',
				'category'    => isset( $block_type->category ) ? $block_type->category : '',
				'icon'        => isset( $block_type->icon ) ? ( is_string( $block_type->icon ) ? $block_type->icon : 'dashicon' ) : '',
			);

			if ( ! empty( $block_type->attributes ) ) {
				$attrs = array();
				foreach ( $block_type->attributes as $attr_name => $attr_config ) {
					$attrs[ $attr_name ] = array(
						'type' => isset( $attr_config['type'] ) ? $attr_config['type'] : 'mixed',
					);
				}
				$entry['attributes'] = $attrs;
			}

			$result[] = $entry;
		}

		return $result;
	}
}
