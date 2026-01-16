<?php

if ( !defined( 'ABSPATH' ) ) {
	status_header( 404 );
	exit;
}

class chkwlistemail extends be_module { // change name
	public $searchname = 'Allow List Email';
	public function process( $ip, &$stats = array(), &$options = array(), &$post = array() ) {
		if ( is_user_logged_in() ) {
			// checks the email from params which has the cache in it
			$current_user = wp_get_current_user();
			$gcache = isset( $options['wlist'] ) && is_array( $options['wlist'] ) ? $options['wlist'] : array();
			return $this->searchList( $current_user->user_email, $gcache );
		}
		return false;
	}
}

?>