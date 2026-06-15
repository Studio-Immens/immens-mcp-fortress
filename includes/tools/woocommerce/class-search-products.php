<?php
namespace Immens_MCP_Fortress\Tools\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Search_Products extends Base_Tool {

	public function get_name() {
		return 'wc_search_products';
	}

	public function get_description() {
		return 'Search WooCommerce products by name or SKU.';
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'search'   => array(
					'type'        => 'string',
					'description' => 'Search term (minimum 2 characters)',

				),
				'per_page' => array(
					'type'        => 'integer',
					'description' => 'Results per page',
					'default'     => 10,
				),
			),
			'required'   => array( 'search' ),
		);
	}

	public function get_category() {
		return 'woocommerce';
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'search' ) );

		$search = trim( $arguments['search'] );
		if ( strlen( $search ) < 2 ) {
			throw new \InvalidArgumentException( 'Search term must be at least 2 characters.' );
		}

		$args = array_merge(
			array(
				'per_page' => 10,
			),
			$arguments,
			array(
				'search' => $search,
			)
		);

		$results = array();

		try {
			$results = $this->rest_request( 'GET', '/wc/v3/products', array(
				'search'   => $args['search'],
				'per_page' => (int) $args['per_page'],
				'context'  => 'edit',
			) );
		} catch ( \RuntimeException $e ) {
			$results = array();
		}

		$sku_results = array();
		if ( function_exists( 'wc_get_product_id_by_sku' ) ) {
			$sku_id = wc_get_product_id_by_sku( $args['search'] );
			if ( $sku_id ) {
				try {
					$sku_product = $this->rest_request( 'GET', '/wc/v3/products/' . $sku_id );
					if ( $sku_product ) {
						$sku_results[] = $sku_product;
					}
				} catch ( \RuntimeException $e ) {
					$sku_results = array();
				}
			}
		}

		if ( ! empty( $sku_results ) ) {
			$existing_ids = array();
			if ( is_array( $results ) ) {
				foreach ( $results as $p ) {
					if ( isset( $p['id'] ) ) {
						$existing_ids[ $p['id'] ] = true;
					}
				}
			}
			foreach ( $sku_results as $sp ) {
				if ( is_array( $results ) && isset( $sp['id'] ) && ! isset( $existing_ids[ $sp['id'] ] ) ) {
					if ( is_array( $results ) ) {
						array_unshift( $results, $sp );
					} else {
						$results = array( $sp );
					}
				}
			}
		}

		return is_array( $results ) ? $results : array();
	}
}
