<?php
namespace Immens_MCP_Fortress\Tools\Yoast;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Social_Settings extends Base_Tool {

	public function get_name() {
		return 'yoast_get_social_settings';
	}

	public function get_description() {
		return 'Get Yoast social settings from the wpseo_social option. Returns Facebook, Twitter, Instagram, LinkedIn, MySpace, Pinterest, YouTube, and Wikipedia URLs.';
	}

	public function get_required_capability() {
		return 'manage_options';
	}

	public function get_category() {
		return 'yoast';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(),
		);
	}

	public function execute( array $arguments ) {
		$social = get_option( 'wpseo_social', array() );

		return array(
			'facebook_site'    => isset( $social['facebook_site'] ) ? $social['facebook_site'] : null,
			'twitter_site'     => isset( $social['twitter_site'] ) ? $social['twitter_site'] : null,
			'instagram_url'    => isset( $social['instagram_url'] ) ? $social['instagram_url'] : null,
			'linkedin_url'     => isset( $social['linkedin_url'] ) ? $social['linkedin_url'] : null,
			'myspace_url'      => isset( $social['myspace_url'] ) ? $social['myspace_url'] : null,
			'pinterest_url'    => isset( $social['pinterest_url'] ) ? $social['pinterest_url'] : null,
			'youtube_url'      => isset( $social['youtube_url'] ) ? $social['youtube_url'] : null,
			'wikipedia_url'    => isset( $social['wikipedia_url'] ) ? $social['wikipedia_url'] : null,
		);
	}
}
