<?php
namespace Immens_MCP_Fortress\Tools\Site;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Update_Site_Settings extends Base_Tool {

	public function get_name() {
		return 'wp_update_site_settings';
	}

	public function get_description() {
		return 'Update site settings.';
	}

	public function get_required_capability() {
		return 'manage_options';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'title'                => array(
					'type'        => 'string',
					'description' => 'Site title',
				),
				'description'          => array(
					'type'        => 'string',
					'description' => 'Site tagline / description',
				),
				'url'                  => array(
					'type'        => 'string',
					'description' => 'Site URL',
				),
				'email'                => array(
					'type'        => 'string',
					'description' => 'Admin email',
				),
				'timezone'             => array(
					'type'        => 'string',
					'description' => 'Timezone string',
				),
				'date_format'          => array(
					'type'        => 'string',
					'description' => 'Date format',
				),
				'time_format'          => array(
					'type'        => 'string',
					'description' => 'Time format',
				),
				'start_of_week'        => array(
					'type'        => 'integer',
					'description' => 'Start of the week (0=Sun, 1=Mon, ...)',
				),
				'language'             => array(
					'type'        => 'string',
					'description' => 'Site language',
				),
				'posts_per_page'       => array(
					'type'        => 'integer',
					'description' => 'Posts per page',
				),
				'default_category'     => array(
					'type'        => 'integer',
					'description' => 'Default post category ID',
				),
				'default_post_format'  => array(
					'type'        => 'string',
					'description' => 'Default post format',
				),
				'default_ping_status'  => array(
					'type'        => 'string',
					'description' => 'Default ping status',
				),
				'default_comment_status' => array(
					'type'        => 'string',
					'description' => 'Default comment status',
				),
			),
		);
	}

	public function execute( array $arguments ) {
		$params = array();

		$fields = array(
			'title', 'description', 'url', 'email', 'timezone',
			'date_format', 'time_format', 'start_of_week', 'language',
			'posts_per_page', 'default_category', 'default_post_format',
			'default_ping_status', 'default_comment_status',
		);

		foreach ( $fields as $field ) {
			if ( isset( $arguments[ $field ] ) ) {
				$params[ $field ] = $arguments[ $field ];
			}
		}

		return $this->rest_request( 'POST', '/wp/v2/settings', $params );
	}
}
