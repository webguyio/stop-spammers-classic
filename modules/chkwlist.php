<?php

if ( !defined( 'ABSPATH' ) ) {
	status_header( 404 );
	exit;
}

class chkwlist extends be_module { // change name
	public $searchname = 'Allow List IP';
	public function process( $ip, &$stats = array(), &$options = array(), &$post = array() ) {
		// checks the IP from params which has the cache in it
		$gcache		      = $options['wlist'];
		return $this->searchList( $ip, $gcache );
	}
}

?>