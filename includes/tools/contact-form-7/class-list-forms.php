<?php
namespace Immens_MCP_Fortress\Tools\CF7;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class List_Forms extends Base_Tool {

	public function get_name() {
		return 'cf7_list_forms';
	}

	public function get_description() {
		return 'List all Contact Form 7 forms with ID, title, and locale.';
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
				'page'     => array(
					'type'        => 'integer',
					'description' => 'Page number',
					'default'     => 1,
				),
				'per_page' => array(
					'type'        => 'integer',
					'description' => 'Forms per page',
					'default'     => 20,
				),
				'search'   => array(
					'type'        => 'string',
					'description' => 'Search term for form title',
				),
			),
		);
	}

	public function execute( array $arguments ) {
		$args = array_merge(
			array(
				'page'     => 1,
				'per_page' => 20,
			),
			$arguments
		);

		$query_args = array(
			'post_type'      => 'wpcf7_contact_form',
			'posts_per_page' => (int) $args['per_page'],
			'paged'          => (int) $args['page'],
			'post_status'    => 'any',
		);

		if ( ! empty( $args['search'] ) ) {
			$query_args['s'] = $args['search'];
		}

		$forms = get_posts( $query_args );

		$result = array();
		foreach ( $forms as $form ) {
			$locale = get_post_meta( $form->ID, '_locale', true );
			$result[] = array(
				'id'     => $form->ID,
				'title'  => $form->post_title,
				'locale' => $locale ? $locale : 'en_US',
				'status' => $form->post_status,
				'date'   => $form->post_date,
			);
		}

		return array(
			'total' => (int) wp_count_posts( 'wpcf7_contact_form' )->publish,
			'page'  => (int) $args['page'],
			'forms' => $result,
		);
	}
}
