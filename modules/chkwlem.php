<?php

if ( !defined( 'ABSPATH' ) ) {
	status_header( 404 );
	exit;
}

class chkwlem extends be_module { // change name
	public $searchname = 'Allow List Email';
	public function process( $ip, &$stats = array(), &$options = array(), &$post = array() ) {
		// checks the email - not sure I want to allow an Allow List on email - maybe won't include
		$email			  = $post['email'];
		if ( empty( $email ) ) {
			return false;
		}
		$wlist = $options['wlist'];
		return $this->searchList( $email, $wlist );
	}
}

?>