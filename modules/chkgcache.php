<?php

if ( !defined( 'ABSPATH' ) ) {
	status_header( 404 );
	exit;
}

class chkgcache extends be_module { // change name
	public $searchname = 'Good Cache';
	public function process( $ip, &$stats = array(), &$options = array(), &$post = array() ) {
		// checks the IP from params which has the cache in it
		$gcache		      = $stats['goodips'];
		return $this->searchcache( $ip, $gcache );
	}
}

?>