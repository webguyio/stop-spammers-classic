<?php

if ( !defined( 'ABSPATH' ) ) {
	status_header( 404 );
	exit;
}

class chkurlshort {
	// look on option list for spam words
	public function process( $ip, &$stats = array(), &$options = array(), &$post = array() ) {
		// Checks for links from common URL shortening services
		// 'email', 'author', 'pwd', 'comment', 'subject'
		$blockurlshortners = $options['blockurlshortners'];
		foreach ( $post as $key => $data ) {
			if ( !empty( $data ) ) {
				foreach ( $blockurlshortners as $urlshort ) {
					if ( stripos( $data, $urlshort ) !== false and ( stripos( $data, $urlshort ) == 0 or substr( $data, stripos( $data, $urlshort ) - 1, 1 ) == " " or substr( $data, stripos( $data, $urlshort ) - 1, 1 ) == "/" or substr( $data, stripos( $data, $urlshort ) - 1, 1 ) == "@" or substr( $data, stripos( $data, $urlshort ) - 1, 1 ) == "." ) ) {
						return 'URL Shortener: ' . $urlshort . ' in ' . $key;
					}
				}
			}
		}
		return false;
	}
}

?>