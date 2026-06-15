<?php
namespace Immens_MCP_Fortress\Tools\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Create_Pages extends Base_Tool {

	public function get_name() {
		return 'wp_create_pages';
	}

	public function get_description() {
		return 'Batch create multiple pages in a single call. Each page object requires "title". Supports content, status, parent, slug, excerpt. Returns results for all pages with errors for individual failures.';
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
					'description' => 'Array of page objects to create. Each must have "title". Optional: content, status, parent, slug, excerpt, featured_media.',
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
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
								'enum'        => array( 'publish', 'future', 'draft', 'pending', 'private' ),
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
						'required'   => array( 'title' ),
					),
				),
				'status' => array(
					'type'        => 'string',
					'description' => 'Default status for all pages (can be overridden per page)',
					'enum'        => array( 'publish', 'future', 'draft', 'pending', 'private' ),
					'default'     => 'draft',
				),
				'parent' => array(
					'type'        => 'integer',
					'description' => 'Default parent ID for all pages (can be overridden per page)',
				),
			),
			'required'   => array( 'pages' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'pages' ) );

		$pages   = $arguments['pages'];
		$default_status = isset( $arguments['status'] ) ? $arguments['status'] : 'draft';
		$default_parent = isset( $arguments['parent'] ) ? (int) $arguments['parent'] : null;
		$results = array();

		foreach ( $pages as $index => $page ) {
			try {
				if ( empty( $page['title'] ) ) {
					$results[] = array(
						'index'   => $index,
						'id'      => null,
						'success' => false,
						'error'   => 'Title is required.',
					);
					continue;
				}

				$params = array(
					'title'  => $page['title'],
					'status' => isset( $page['status'] ) ? $page['status'] : $default_status,
				);

				if ( isset( $page['content'] ) ) {
					$params['content'] = $page['content'];
				}
				if ( isset( $page['slug'] ) ) {
					$params['slug'] = $page['slug'];
				} else {
					$params['slug'] = sanitize_title( $page['title'] );
				}
				if ( isset( $page['excerpt'] ) ) {
					$params['excerpt'] = $page['excerpt'];
				}
				if ( isset( $page['parent'] ) ) {
					$params['parent'] = (int) $page['parent'];
				} elseif ( null !== $default_parent ) {
					$params['parent'] = $default_parent;
				}
				if ( isset( $page['featured_media'] ) ) {
					$params['featured_media'] = (int) $page['featured_media'];
				}

				$result = $this->rest_request( 'POST', '/wp/v2/pages', $params );

				$results[] = array(
					'index'   => $index,
					'id'      => isset( $result['id'] ) ? (int) $result['id'] : null,
					'success' => true,
					'title'   => isset( $result['title']['rendered'] ) ? $result['title']['rendered'] : $page['title'],
					'slug'    => isset( $result['slug'] ) ? $result['slug'] : $params['slug'],
					'link'    => isset( $result['link'] ) ? $result['link'] : '',
				);
			} catch ( \Exception $e ) {
				$results[] = array(
					'index'   => $index,
					'id'      => null,
					'success' => false,
					'error'   => $e->getMessage(),
				);
			}
		}

		return array(
			'created' => count( array_filter( $results, function ( $r ) { return ! empty( $r['success'] ); } ) ),
			'failed'  => count( array_filter( $results, function ( $r ) { return empty( $r['success'] ); } ) ),
			'total'   => count( $results ),
			'results' => $results,
		);
	}
}
