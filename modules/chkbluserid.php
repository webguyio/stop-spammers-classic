<?php

if ( !defined( 'ABSPATH' ) ) {
	status_header( 404 );
	exit;
}

class chkbluserid extends be_module { // change name
	public $searchname = 'Allow List Email';
	public function process( $ip, &$stats = array(), &$options = array(), &$post = array() ) {
		// checks the user author or login ID
		$user			  = $post['author'];
		if ( empty( $user ) ) {
			return false;
		}
		$blist = $options['blist'];
		return $this->searchList( $user, $blist );
	}
}

?>