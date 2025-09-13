<?php

if ( !defined( 'ABSPATH' ) ) {
	status_header( 404 );
	exit;
}

class chkwluserid extends be_module { // change name
	public $searchname = 'Allow List Email';
	public function process( $ip, &$stats = array(), &$options = array(), &$post = array() ) {
		// checks the user - dangerous to allow a whitelisted user - spammers could use it
		$user			  = $post['author'];
		if ( empty( $user ) ) {
			return false;
		}
		$wlist = $options['wlist'];
		return $this->searchList( $user, $wlist );
	}
}

?>