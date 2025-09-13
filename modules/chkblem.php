<?php

if ( !defined( 'ABSPATH' ) ) {
	status_header( 404 );
	exit;
}

class chkblem extends be_module { // change name
	public $searchname = 'Block List Email';
	public function process( $ip, &$stats = array(), &$options = array(), &$post = array() ) {
		// checks the IP from params which has the cache in it
		$email			  = $post['email'];
		if ( empty( $email ) ) {
			return false;
		}
		$blist = $options['blist'];
		return $this->searchList( $email, $blist );
	}
}

?>