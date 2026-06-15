<?php
namespace Immens_MCP_Fortress\Tools\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Update_Pages extends Base_Tool {

	public function get_name() {
		return 'wp_update_pages';
	}

	public function get_description() {
		return 'Batch update multiple pages in a single call. Each page object requires "id". Returns results for all pages with errors for individual failures.';
	}

	public function get_category() {
		return 'pages';
	}

	public function get_required_capability() {
		return 'edit_pages';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'pages' => array(
					'type'        => 'array',
					'description' => 'Array of page objects to update. Each must have "id". Optional: title, content, status, parent, slug, excerpt, featured_media.',
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'             => array(
								'type'        => 'integer',
								'description' => 'Page ID',
							),
							'title'          => array(
								'type'        => 'string',
								'description' => 'Page title',
							),
							'content'        => array(
								'type'        => 'string',
								'description' => 'Page content (HTML)',
							),
							'status'         => array(
								'type'        => 'string',
								'description' => 'Page status',
								'enum'        => array( 'publish', 'future', 'draft', 'pending', 'private', 'trash' ),
							),
							'parent'         => array(
								'type'        => 'integer',
								'description' => 'Parent page ID',
							),
							'slug'           => array(
								'type'        => 'string',
								'description' => 'Page slug',
							),
							'excerpt'        => array(
								'type'        => 'string',
								'description' => 'Page excerpt',
							),
							'featured_media' => array(
								'type'        => 'integer',
								'description' => 'Featured media attachment ID',
							),
						),
						'required'   => array( 'id' ),
					),
				),
			),
			'required'   => array( 'pages' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'pages' ) );

		$pages   = $arguments['pages'];
		$results = array();
		$fields  = array( 'title', 'content', 'status', 'parent', 'slug', 'excerpt' );

		foreach ( $pages as $index => $page ) {
			try {
				$id = $this->parse_required_id( $page['id'], 'Page ID' );
				$params = array();

				foreach ( $fields as $field ) {
					if ( isset( $page[ $field ] ) ) {
						$params[ $field ] = $page[ $field ];
					}
				}
				if ( isset( $page['featured_media'] ) ) {
					$params['featured_media'] = (int) $page['featured_media'];
				}

				$result = $this->rest_request( 'POST', '/wp/v2/pages/' . $id, $params );

				$results[] = array(
					'index'   => $index,
					'id'      => $id,
					'success' => true,
					'title'   => isset( $result['title']['rendered'] ) ? $result['title']['rendered'] : '',
				);
			} catch ( \Exception $e ) {
				$results[] = array(
					'index'   => $index,
					'id'      => isset( $page['id'] ) ? (int) $page['id'] : null,
					'success' => false,
					'error'   => $e->getMessage(),
				);
			}
		}

		return array(
			'updated' => count( array_filter( $results, function ( $r ) { return ! empty( $r['success'] ); } ) ),
			'failed'  => count( array_filter( $results, function ( $r ) { return empty( $r['success'] ); } ) ),
			'total'   => count( $results ),
			'results' => $results,
		);
	}
}
