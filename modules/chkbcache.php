<?php

if ( !defined( 'ABSPATH' ) ) {
	status_header( 404 );
	exit;
}

class chkbcache extends be_module { // change name
	public $searchname = 'Bad Cache';
	public function process( $ip, &$stats = array(), &$options = array(), &$post = array() ) {
		// checks the IP from params which has the cache in it
		$gcache		      = $stats['badips'];
		return $this->searchcache( $ip, $gcache );
	}
}

?>