<?php

if ( !defined( 'ABSPATH' ) ) {
	status_header( 404 );
	exit;
}

class ss_check_site_get extends be_module {
	public function process(
		$ip, &$stats = array(), &$options = array(), &$post = array() ) {
		// not checking this anymore
		return false;
	}
}

?>