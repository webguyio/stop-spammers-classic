<?php
// this checks 404 entries for attacks

if ( !defined( 'ABSPATH' ) ) {
	status_header( 404 );
	exit;
}

class ss_check_404s {
	public function process( $ip, &$stats = array(), &$options = array(), &$post = array() ) {
		// load the chk404 module
		if ( $options['chk404'] !== 'Y' ) {
			return false;
		}
		$reason = be_load( 'chk404', $ip );
		if ( $reason === false ) {
			return;
		}
		// update log
		ss_log_bad( $ip, $reason, 'chk404' );
		// need to block access
		$rejectmessage = $options['rejectmessage'];
		wp_die( '$rejectmessage', 'Login Access Blocked', array( 'response' => 403 ) );
		exit();
	}
}

?>