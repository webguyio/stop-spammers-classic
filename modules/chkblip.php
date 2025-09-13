<?php

if ( !defined( 'ABSPATH' ) ) {
	status_header( 404 );
	exit;
}

class chkblip extends be_module { // change name
	public $searchname = 'Block List IP';
	public function process( $ip, &$stats = array(), &$options = array(), &$post = array() ) {
		// checks the IP from params which has the cache in it
		$gcache		      = $options['blist'];
		return $this->searchList( $ip, $gcache );
	}
}

?>