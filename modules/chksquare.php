<?php
// Allow List - returns false if not found

if ( !defined( 'ABSPATH' ) ) {
	status_header( 404 );
	exit;
}

// last updated from https://squareup.com/help/us/en/article/6537-square-terminal-troubleshooting on 2/29/24
class chkpaypal extends be_module {
	public $searchname = 'Square';
	public $searchlist = array(
		'74.122.184.0/21',
		'103.31.216.0/22', 
		'185.57.56.0/22'
	);
}

?>