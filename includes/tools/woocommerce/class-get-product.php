<?php
namespace Immens_MCP_Fortress\Tools\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Product extends Base_Tool {

	public function get_name() {
		return 'wc_get_product';
	}

	public function get_description() {
		return 'Get a single WooCommerce product by ID.';
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'id' => array(
					'type'        => 'integer',
					'description' => 'Product ID',
					'required'    => true,
				),
			),
			'required'   => array( 'id' ),
		);
	}

	public function get_category() {
		return 'woocommerce';
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'id' ) );
		$id = $this->parse_required_id( $arguments['id'], 'Product ID' );

		return $this->rest_request( 'GET', '/wc/v3/products/' . $id );
	}
}
