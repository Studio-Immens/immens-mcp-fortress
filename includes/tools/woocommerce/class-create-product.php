<?php
namespace Immens_MCP_Fortress\Tools\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Create_Product extends Base_Tool {

	public function get_name() {
		return 'wc_create_product';
	}

	public function get_description() {
		return 'Create a new WooCommerce product with name, price, type, status, description, and categories.';
	}

	public function get_category() {
		return 'woocommerce';
	}

	public function get_required_capability() {
		return 'manage_woocommerce';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'name'               => array(
					'type'        => 'string',
					'description' => 'Product name/title',
				),
				'type'               => array(
					'type'        => 'string',
					'description' => 'Product type',
					'enum'        => array( 'simple', 'grouped', 'external', 'variable' ),
					'default'     => 'simple',
				),
				'price'              => array(
					'type'        => 'string',
					'description' => 'Product price',
				),
				'regular_price'      => array(
					'type'        => 'string',
					'description' => 'Product regular price',
				),
				'sale_price'         => array(
					'type'        => 'string',
					'description' => 'Product sale price',
				),
				'description'        => array(
					'type'        => 'string',
					'description' => 'Product description (HTML)',
				),
				'short_description'  => array(
					'type'        => 'string',
					'description' => 'Product short description (HTML)',
				),
				'status'             => array(
					'type'        => 'string',
					'description' => 'Product status',
					'enum'        => array( 'publish', 'draft', 'pending', 'private' ),
					'default'     => 'draft',
				),
				'featured'           => array(
					'type'        => 'boolean',
					'description' => 'Featured product',
				),
				'catalog_visibility' => array(
					'type'        => 'string',
					'description' => 'Catalog visibility',
					'enum'        => array( 'visible', 'catalog', 'search', 'hidden' ),
				),
				'virtual'            => array(
					'type'        => 'boolean',
					'description' => 'Is virtual product',
				),
				'downloadable'       => array(
					'type'        => 'boolean',
					'description' => 'Is downloadable product',
				),
				'sku'                => array(
					'type'        => 'string',
					'description' => 'Product SKU',
				),
				'categories'         => array(
					'type'        => 'array',
					'description' => 'Category IDs or objects with id',
					'items'       => array( 'type' => 'integer' ),
				),
				'tags'               => array(
					'type'        => 'array',
					'description' => 'Tag IDs or objects with id',
					'items'       => array( 'type' => 'integer' ),
				),
				'images'             => array(
					'type'        => 'array',
					'description' => 'Image objects with src, alt, name',
				),
			),
			'required'   => array( 'name', 'price' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'name', 'price' ) );

		$params = array(
			'name'  => $arguments['name'],
			'price' => $arguments['price'],
			'type'  => isset( $arguments['type'] ) ? $arguments['type'] : 'simple',
		);

		if ( isset( $arguments['regular_price'] ) ) {
			$params['regular_price'] = $arguments['regular_price'];
		}
		if ( isset( $arguments['sale_price'] ) ) {
			$params['sale_price'] = $arguments['sale_price'];
		}
		if ( isset( $arguments['description'] ) ) {
			$params['description'] = $arguments['description'];
		}
		if ( isset( $arguments['short_description'] ) ) {
			$params['short_description'] = $arguments['short_description'];
		}
		if ( isset( $arguments['status'] ) ) {
			$params['status'] = $arguments['status'];
		}
		if ( isset( $arguments['featured'] ) ) {
			$params['featured'] = (bool) $arguments['featured'];
		}
		if ( isset( $arguments['catalog_visibility'] ) ) {
			$params['catalog_visibility'] = $arguments['catalog_visibility'];
		}
		if ( isset( $arguments['virtual'] ) ) {
			$params['virtual'] = (bool) $arguments['virtual'];
		}
		if ( isset( $arguments['downloadable'] ) ) {
			$params['downloadable'] = (bool) $arguments['downloadable'];
		}
		if ( isset( $arguments['sku'] ) ) {
			$params['sku'] = $arguments['sku'];
		}
		if ( isset( $arguments['categories'] ) ) {
			$params['categories'] = array_map( function ( $c ) {
				return is_int( $c ) ? array( 'id' => $c ) : $c;
			}, $arguments['categories'] );
		}
		if ( isset( $arguments['tags'] ) ) {
			$params['tags'] = array_map( function ( $t ) {
				return is_int( $t ) ? array( 'id' => $t ) : $t;
			}, $arguments['tags'] );
		}
		if ( isset( $arguments['images'] ) ) {
			$params['images'] = $arguments['images'];
		}

		return $this->rest_request( 'POST', '/wc/v3/products', $params );
	}
}
