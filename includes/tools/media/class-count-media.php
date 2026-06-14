<?php
namespace Immens_MCP_Fortress\Tools\Media;

use Immens_MCP_Fortress\Tools\Base_Tool;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Count_Media extends Base_Tool {

	public function get_name() {
		return 'wp_count_media';
	}

	public function get_description() {
		return 'Count media items, optionally filtered by mime type.';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'mime_type' => array(
					'type'        => 'string',
					'description' => 'Filter by mime type (e.g. image, image/jpeg, application/pdf).',
				),
			),
		);
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_category() {
		return 'media';
	}

	public function execute( array $arguments ) {
		$mime_type = isset( $arguments['mime_type'] ) ? sanitize_text_field( $arguments['mime_type'] ) : '';

		$args = array(
			'post_type'      => 'attachment',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'no_found_rows'  => true,
		);

		if ( ! empty( $mime_type ) ) {
			if ( false === strpos( $mime_type, '/' ) ) {
				$args['post_mime_type'] = $mime_type;
			} else {
				$args['post_mime_type'] = $mime_type;
			}
		}

		$query = new \WP_Query( $args );

		$counts = wp_count_attachments( $mime_type ?: null );

		if ( ! empty( $mime_type ) && false !== strpos( $mime_type, '/' ) ) {
			$total = 0;
			foreach ( $counts as $type => $count ) {
				if ( $type === $mime_type ) {
					$total = (int) $count;
					break;
				}
			}
		} else {
			$total = (int) $query->found_posts;

			if ( $total === 0 && ! empty( $mime_type ) ) {
				$all = wp_count_attachments();
				foreach ( $all as $type => $count ) {
					if ( strpos( $type, $mime_type ) === 0 ) {
						$total += (int) $count;
					}
				}
			}

			if ( empty( $mime_type ) ) {
				$total = array_sum( (array) $counts );
			}
		}

		return array(
			'total'     => $total,
			'mime_type' => $mime_type ?: 'all',
		);
	}
}
