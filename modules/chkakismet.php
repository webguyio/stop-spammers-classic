<?php
// this check never seems to work, so I'll leave it for now, but not use it

if ( !defined( 'ABSPATH' ) ) {
	status_header( 404 );
	exit;
}

class chkakismet {
	public function process( $ip, &$stats = array(), &$options = array(), &$post = array() ) {
		// do a lookup on Akismet
		if ( !function_exists( 'get_option' ) ) {
			return false;
		}
		if ( !function_exists( 'site_url' ) ) {
			return false;
		}
		$api_key = get_option( 'wordpress_api_key' );
		if ( empty( $api_key ) ) {
			return false;
		}
		$agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		$blogurl = site_url();
		$api_key = urlencode( $api_key );
		$agent   = urlencode( $agent );
		$blogurl = urlencode( $blogurl );
		if ( empty( $api_key ) || empty( $agent ) || empty( $blogurl ) ) {
			return false;
		}
		$refer = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
		$data	 = array(
			'blog'				   => $blogurl,
			'user_ip'			   => $ip,
			'user_agent'		   => $agent,
			'referrer'			   => $refer,
			'permalink'			   => '',
			'comment_type'		   => 'comment',
			'comment_author'	   => '',
			'comment_author_email' => '',
			'comment_author_url'   => '',
			'comment_content'	   => ''
		);
		$response = $this->akismet_comment_check( 'YourAPIKey', $data );
		return $response;
	}
	function akismet_comment_check( $key, $data ) {
		$data = wp_unslash( $data );
		$request = array(
			'blog'                 => esc_url_raw( $data['blog'] ),
			'user_ip'              => sanitize_text_field( $data['user_ip'] ),
			'user_agent'           => sanitize_text_field( $data['user_agent'] ),
			'referrer'             => esc_url_raw( $data['referrer'] ),
			'permalink'            => esc_url_raw( $data['permalink'] ),
			'comment_type'         => sanitize_text_field( $data['comment_type'] ),
			'comment_author'       => sanitize_text_field( $data['comment_author'] ),
			'comment_author_email' => sanitize_email( $data['comment_author_email'] ),
			'comment_author_url'   => esc_url_raw( $data['comment_author_url'] ),
			'comment_content'      => sanitize_textarea_field( $data['comment_content'] ),
		);
		$host = sanitize_text_field( $key ) . '.rest.akismet.com';
		$path = '/1.1/comment-check';
		$url  = 'https://' . $host . $path;
		$akismet_ua = sprintf( 'WordPress/%s | Akismet/%s', $GLOBALS['wp_version'], constant( 'AKISMET_VERSION' ) );
		$args = array(
			'body'       => $request,
			'user-agent' => $akismet_ua,
			'timeout'    => 10,
		);
		$response = wp_remote_post( $url, $args );
		if ( is_wp_error( $response ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Legitimate error logging for production API failures
			error_log( 'Akismet request failed: ' . $response->get_error_message() );
			return false;
		}
		$response_body = wp_remote_retrieve_body( $response );
		if ( 'true' === trim( $response_body ) ) {
			return true; // comment is spam
		} else {
			return false; // comment is not spam
		}
	}
}

?>