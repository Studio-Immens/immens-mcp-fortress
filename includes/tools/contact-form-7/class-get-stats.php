<?php
namespace Immens_MCP_Fortress\Tools\CF7;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Stats extends Base_Tool {

	public function get_name() {
		return 'cf7_get_stats';
	}

	public function get_description() {
		return 'Get Contact Form 7 statistics: total forms, total submissions, and submissions per form.';
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
			'properties' => array(),
		);
	}

	public function execute( array $arguments ) {
		$total_forms = (int) wp_count_posts( 'wpcf7_contact_form' )->publish;

		$submissions_per_form = array();
		$total_submissions    = 0;

		if ( post_type_exists( 'flamingo_inbound' ) ) {
			$counts = wp_count_posts( 'flamingo_inbound' );
			$total_submissions = (int) $counts->publish + (int) $counts->draft;

			$forms = get_posts( array(
				'post_type'      => 'wpcf7_contact_form',
				'posts_per_page' => -1,
				'post_status'    => 'any',
			) );

			foreach ( $forms as $form ) {
				$sub_count = $this->count_flamingo_for_form( $form->ID );
				$submissions_per_form[] = array(
					'form_id'     => $form->ID,
					'form_title'  => $form->post_title,
					'submissions' => $sub_count,
				);
			}
		} else {
			$forms = get_posts( array(
				'post_type'      => 'wpcf7_contact_form',
				'posts_per_page' => -1,
				'post_status'    => 'any',
			) );

			foreach ( $forms as $form ) {
				$submissions = get_post_meta( $form->ID, '_wpcf7_posted_data' );
				$count = count( $submissions );
				$total_submissions += $count;
				$submissions_per_form[] = array(
					'form_id'     => $form->ID,
					'form_title'  => $form->post_title,
					'submissions' => $count,
				);
			}
		}

		return array(
			'total_forms'        => $total_forms,
			'total_submissions'  => $total_submissions,
			'submissions_source' => post_type_exists( 'flamingo_inbound' ) ? 'flamingo' : 'meta',
			'per_form'           => $submissions_per_form,
		);
	}

	private function count_flamingo_for_form( $form_id ) {
		$query = new \WP_Query( array(
			'post_type'      => 'flamingo_inbound',
			'post_status'    => 'any',
			'meta_key'       => '_cf7_form_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $form_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'fields'         => 'ids',
			'posts_per_page' => -1,
		) );

		return $query->found_posts;
	}
}
