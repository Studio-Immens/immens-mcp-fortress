<?php
namespace Immens_MCP_Fortress\Tools\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Store_Stats extends Base_Tool {

	public function get_name() {
		return 'wc_get_store_stats';
	}

	public function get_description() {
		return 'Get WooCommerce store statistics including revenue, product count, and order count.';
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(),
		);
	}

	public function get_category() {
		return 'woocommerce';
	}

	public function execute( array $arguments ) {
		$stats = array(
			'total_products' => 0,
			'total_orders'   => 0,
			'revenue'        => 0,
		);

		$currency = get_option( 'woocommerce_currency' );
		if ( $currency ) {
			$stats['currency'] = $currency;
		}

		$product_counts = wp_count_posts( 'product' );
		if ( $product_counts ) {
			$published = isset( $product_counts->publish ) ? (int) $product_counts->publish : 0;
			$draft     = isset( $product_counts->draft ) ? (int) $product_counts->draft : 0;
			$pending   = isset( $product_counts->pending ) ? (int) $product_counts->pending : 0;
			$private   = isset( $product_counts->private ) ? (int) $product_counts->private : 0;
			$stats['total_products'] = $published + $draft + $pending + $private;
		}

		$order_counts = wp_count_posts( 'shop_order' );
		if ( $order_counts ) {
			$total_orders = 0;
			foreach ( array( 'wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled', 'wc-refunded', 'wc-failed' ) as $status ) {
				if ( isset( $order_counts->$status ) ) {
					$total_orders += (int) $order_counts->$status;
				}
			}
			$stats['total_orders'] = $total_orders;
		}

		try {
			$report = $this->rest_request( 'GET', '/wc/v3/reports/orders/totals' );
			if ( is_array( $report ) ) {
				$stats['report_totals'] = $report;
			}
		} catch ( \RuntimeException $e ) {
			$stats['report_totals_error'] = $e->getMessage();
		}

		try {
			$completed_orders = $this->rest_request( 'GET', '/wc/v3/reports/revenue/stats', array(
				'period' => 'all',
			) );
			if ( is_array( $completed_orders ) && isset( $completed_orders['totals'] ) ) {
				$stats['revenue'] = isset( $completed_orders['totals']['gross_sales'] )
					? (float) $completed_orders['totals']['gross_sales']
					: 0;
			}
		} catch ( \RuntimeException $e ) {
			$order_ids = wc_get_orders( array(
				'status'      => array( 'wc-completed', 'wc-processing' ),
				'limit'       => -1,
				'return'      => 'ids',
			) );

			$total_revenue = 0;
			if ( function_exists( 'wc_get_order' ) ) {
				foreach ( $order_ids as $order_id ) {
					$order = wc_get_order( $order_id );
					if ( $order ) {
						$total_revenue += (float) $order->get_total();
					}
				}
			}
			$stats['revenue'] = $total_revenue;
		}

		return $stats;
	}
}
