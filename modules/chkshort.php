<?php

if ( !defined( 'ABSPATH' ) ) {
	status_header( 404 );
	exit;
}

class chkshort { // change name
	public $searchname = 'Email/Username Too Short';
	public function process( $ip, &$stats = array(), &$options = array(), &$post = array() ) {
		if ( array_key_exists( 'email', $post ) ) {
			$email = $post['email'];
			if ( !empty( $email ) ) {
				if ( strlen( $email ) < 5 ) {
					return 'Email Too Short: ' . $email;
				}
			}
		}
		if ( array_key_exists( 'author', $post ) ) {
			if ( !empty( $post['author'] ) ) {
				$author = $post['author'];
				// short author is OK?
				if ( strlen( $post['author'] ) < 3 ) {
					return 'Username Too Short: ' . $author;
				}
			}
		}
		return false;
	}
}

?>