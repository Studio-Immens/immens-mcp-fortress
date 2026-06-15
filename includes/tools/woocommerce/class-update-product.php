<?php
namespace Immens_MCP_Fortress\Tools\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Update_Product extends Base_Tool {

	public function get_name() {
		return 'wc_update_product';
	}

	public function get_description() {
		return 'Update a WooCommerce product by ID. Supports name, description, short_description, price, sale_price, status, categories, tags, featured, and catalog_visibility.';
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
				'id'                 => array(
					'type'        => 'integer',
					'description' => 'Product ID',
				),
				'name'               => array(
					'type'        => 'string',
					'description' => 'Product name/title',
				),
				'description'        => array(
					'type'        => 'string',
					'description' => 'Product description (HTML)',
				),
				'short_description'  => array(
					'type'        => 'string',
					'description' => 'Product short description (HTML)',
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
				'status'             => array(
					'type'        => 'string',
					'description' => 'Product status',
					'enum'        => array( 'publish', 'draft', 'pending', 'private' ),
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
				'stock_status'       => array(
					'type'        => 'string',
					'description' => 'Stock status',
					'enum'        => array( 'instock', 'outofstock', 'onbackorder' ),
				),
				'purchase_note'      => array(
					'type'        => 'string',
					'description' => 'Purchase note shown after order',
				),
				'sold_individually'  => array(
					'type'        => 'boolean',
					'description' => 'Limit to one per order',
				),
			),
			'required'   => array( 'id' ),
		);
	}

	public function execute( array $arguments ) {
		$id = $this->parse_required_id( $arguments['id'], 'Product ID' );

		$params = array();

		$string_fields = array(
			'name', 'description', 'short_description', 'price', 'regular_price',
			'sale_price', 'status', 'catalog_visibility', 'stock_status',
			'purchase_note',
		);
		foreach ( $string_fields as $field ) {
			if ( isset( $arguments[ $field ] ) ) {
				$params[ $field ] = $arguments[ $field ];
			}
		}

		$bool_fields = array( 'featured', 'virtual', 'downloadable', 'sold_individually' );
		foreach ( $bool_fields as $field ) {
			if ( isset( $arguments[ $field ] ) ) {
				$params[ $field ] = (bool) $arguments[ $field ];
			}
		}

		$result = $this->rest_request( 'PUT', '/wc/v3/products/' . $id, $params );

		return $result;
	}
}
