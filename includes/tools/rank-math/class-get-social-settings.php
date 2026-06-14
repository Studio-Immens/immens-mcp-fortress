<?php
namespace Immens_MCP_Fortress\Tools\RankMath;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Social_Settings extends Base_Tool {

	public function get_name() {
		return 'rankmath_get_social_settings';
	}

	public function get_description() {
		return 'Get Rank Math social media settings from the rank-math-options-general option. Returns Facebook, Twitter, and other social profile URLs.';
	}

	public function get_required_capability() {
		return 'manage_options';
	}

	public function get_category() {
		return 'rank-math';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(),
		);
	}

	public function execute( array $arguments ) {
		$general = get_option( 'rank-math-options-general', array() );

		return array(
			'facebook_author'     => isset( $general['facebook_author'] ) ? $general['facebook_author'] : null,
			'facebook_admin_id'   => isset( $general['facebook_admin_id'] ) ? $general['facebook_admin_id'] : null,
			'facebook_app_id'     => isset( $general['facebook_app_id'] ) ? $general['facebook_app_id'] : null,
			'facebook_secret'     => isset( $general['facebook_secret'] ) ? ( $general['facebook_secret'] ? '[redacted]' : null ) : null,
			'twitter_author'      => isset( $general['twitter_author'] ) ? $general['twitter_author'] : null,
			'twitter_card_type'   => isset( $general['twitter_card_type'] ) ? $general['twitter_card_type'] : null,
			'social_url_facebook' => isset( $general['social_url_facebook'] ) ? $general['social_url_facebook'] : null,
			'social_url_twitter'  => isset( $general['social_url_twitter'] ) ? $general['social_url_twitter'] : null,
			'social_url_linkedin' => isset( $general['social_url_linkedin'] ) ? $general['social_url_linkedin'] : null,
			'social_url_youtube'  => isset( $general['social_url_youtube'] ) ? $general['social_url_youtube'] : null,
			'social_url_pinterest' => isset( $general['social_url_pinterest'] ) ? $general['social_url_pinterest'] : null,
			'social_url_instagram' => isset( $general['social_url_instagram'] ) ? $general['social_url_instagram'] : null,
		);
	}
}
