<?php
namespace Immens_MCP_Fortress\Tools\CF7;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class List_Submissions extends Base_Tool {

	public function get_name() {
		return 'cf7_list_submissions';
	}

	public function get_description() {
		return 'List recent Contact Form 7 submissions. Uses Flamingo if available, otherwise reads form meta.';
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_category() {
		return 'contact-form-7';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'form_id' => array(
					'type'        => 'integer',
					'description' => 'Filter by form ID',
				),
				'limit'   => array(
					'type'        => 'integer',
					'description' => 'Maximum submissions to return',
					'default'     => 10,
				),
			),
		);
	}

	public function execute( array $arguments ) {
		$limit  = isset( $arguments['limit'] ) ? (int) $arguments['limit'] : 10;
		$form_id = isset( $arguments['form_id'] ) ? (int) $arguments['form_id'] : 0;

		if ( post_type_exists( 'flamingo_inbound' ) ) {
			return $this->get_flamingo_submissions( $form_id, $limit );
		}

		return $this->get_meta_submissions( $form_id, $limit );
	}

	private function get_flamingo_submissions( $form_id, $limit ) {
		$query_args = array(
			'post_type'      => 'flamingo_inbound',
			'posts_per_page' => $limit,
			'post_status'    => 'any',
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		if ( $form_id > 0 ) {
			$query_args['meta_key']   = '_cf7_form_id'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$query_args['meta_value'] = $form_id; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		}

		$submissions = get_posts( $query_args );

		$result = array();
		foreach ( $submissions as $sub ) {
			$posted_data = get_post_meta( $sub->ID, '_posted_data', true );
			$form_id_val = get_post_meta( $sub->ID, '_cf7_form_id', true );

			$result[] = array(
				'id'          => $sub->ID,
				'form_id'     => $form_id_val ? (int) $form_id_val : 0,
				'date'        => $sub->post_date,
				'subject'     => $sub->post_title,
				'posted_data' => $posted_data,
				'source'      => 'flamingo',
			);
		}

		return array(
			'source'       => 'flamingo',
			'total'        => count( $result ),
			'submissions'  => $result,
		);
	}

	private function get_meta_submissions( $form_id, $limit ) {
		$query_args = array(
			'post_type'      => 'wpcf7_contact_form',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		);

		if ( $form_id > 0 ) {
			$query_args['post__in'] = array( $form_id );
		}

		$forms  = get_posts( $query_args );
		$result = array();

		foreach ( $forms as $form ) {
			$submissions = get_post_meta( $form->ID, '_wpcf7_posted_data' );
			foreach ( $submissions as $data ) {
				$result[] = array(
					'form_id'     => $form->ID,
					'form_title'  => $form->post_title,
					'posted_data' => $data,
					'source'      => 'meta',
				);
				if ( count( $result ) >= $limit ) {
					break;
				}
			}
			if ( count( $result ) >= $limit ) {
				break;
			}
		}

		return array(
			'source'      => 'meta',
			'total'       => count( $result ),
			'submissions' => array_slice( $result, 0, $limit ),
		);
	}
}
