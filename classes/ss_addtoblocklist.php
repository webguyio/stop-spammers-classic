<?php

if ( !defined( 'ABSPATH' ) ) {
	status_header( 404 );
	exit;
}

// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified when called via AJAX; email parameter only processed with valid nonce
class ss_addtoblocklist {
	public function process( $ip, &$stats = array(), &$options = array(), &$post = array() ) {
		// adds to Block List
		$now = gmdate( 'Y/m/d H:i:s', time() + ( get_option( 'gmt_offset' ) * 3600 ) );
		$blist = is_array( $options['blist'] ) ? $options['blist'] : array();
		// add this IP to your Block List
		$sanitized_ip = filter_var( $ip, FILTER_VALIDATE_IP );
		if ( $sanitized_ip && !in_array( $sanitized_ip, $blist, true ) ) {
			$blist[] = $sanitized_ip;
		}
		// add this email to your Block List
		if ( isset( $_POST['email'] ) && is_email( sanitize_email( wp_unslash( $_POST['email'] ) ) ) ) {
			if ( !isset( $_POST['func_nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['func_nonce'] ) ), 'sfs_process_add_black' ) ) {
				return false;
			}
			$sanitized_email = sanitize_email( wp_unslash( $_POST['email'] ) );
			if ( !in_array( $sanitized_email, $blist, true ) ) {
				$blist[] = $sanitized_email;
			}
		}
		$options['blist'] = $blist;
		ss_set_options( $options );
		// need to remove from caches
		$badips = $stats['badips'];
		if ( array_key_exists( $ip, $badips ) ) {
			unset( $badips[$ip] );
			$stats['badips'] = $badips;
		}
		$goodips = $stats['goodips'];
		if ( array_key_exists( $ip, $goodips ) ) {
			unset( $goodips[$ip] );
			$stats['goodips'] = $goodips;
		}
		ss_set_stats( $stats );
		return false;
	}
}

?>