<?php

if ( !defined( 'ABSPATH' ) ) {
	http_response_code( 404 );
	die();
}

class chklong { // change name
	public $searchname = 'Email/Username/Password Too Long';
	public function process( $ip, &$stats = array(), &$options = array(), &$post = array() ) {
		if ( array_key_exists( 'email', $post ) ) {
			$email = $post['email'];
			if ( !empty( $email ) ) {
				if ( strlen( $email ) > 64 ) {
					return 'Email Too Long: ' . $email;
				}
			}
		}
		if ( array_key_exists( 'author', $post ) ) {
			if ( !empty( $post['author'] ) ) {
				$author = $post['author'];
				if ( strlen( $post['author'] ) > 64 ) {
					return 'Username Too Long: ' . $author;
				}
			}
		}
		if ( array_key_exists( 'psw', $post ) ) {
			if ( !empty( $post['psw'] ) ) {
				$psw = $post['psw'];
				if ( strlen( $post['psw'] ) > 32 ) {
					return 'Password Too Long: ' . $psw;
				}
			}
		}
		return false;
	}
}

?>