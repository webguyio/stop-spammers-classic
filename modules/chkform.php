<?php

if ( !defined( 'ABSPATH' ) ) {
	status_header( 404 );
	exit;
}

class chkform extends be_module {
	public function process( $ip, &$stats = array(), &$options = array(), &$post = array() ) {
		// checks to see if we are in 
		// wp-comments-post.php and wp-login.php
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		if ( strpos( $uri, 'wp-comments-post.php' ) !== false ) {
			// sfs_debug_msg( "continue check wp-comments-post.php $ip" );
			return false;
		}
		if ( strpos( $uri, 'wp-login.php' ) !== false ) {
			// sfs_debug_msg( "continue check wp-login.php $ip" );
			return false;
		}
		// sfs_debug_msg( "Allowed $uri $ip" );
		return 'Post request not in wp-comments-post.php or wp-login.php — ' . $uri;
	}
}

?>