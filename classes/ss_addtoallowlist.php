<?php

if ( !defined( 'ABSPATH' ) ) {
	status_header( 404 );
	exit;
}

class ss_addtoallowlist {
	public function process( $ip, &$stats = array(), &$options = array(), &$post = array() ) {
		// adds to Allow List - used to add admin to Allow List or to add a comment author to Allow List
		$now = gmdate( 'Y/m/d H:i:s', time() + ( get_option( 'gmt_offset' ) * 3600 ) );
		$wlist = is_array( $options['wlist'] ) ? $options['wlist'] : array();
		$wlist_email = isset( $options['wlist_email'] ) ? $options['wlist_email'] : array();
		// add this IP to your Allow List
		$sanitized_ip = filter_var( $ip, FILTER_VALIDATE_IP );
		if ( $sanitized_ip && ! in_array( $sanitized_ip, $wlist, true ) ) {
			$wlist[] = $sanitized_ip;
		}
		$options['wlist'] = $wlist;
		// add this email to your Allow List
		if ( isset( $_GET['email'] ) && is_email( sanitize_email( wp_unslash( $_GET['email'] ) ) ) && ! in_array( sanitize_email( wp_unslash( $_GET['email'] ) ), $wlist_email, true ) ) {
			$wlist_email[] = sanitize_email( wp_unslash( $_GET['email'] ) );
		}
		$options['wlist_email'] = $wlist_email;
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
		if ( isset( $_GET['func'] ) && sanitize_text_field( wp_unslash( $_GET['func'] ) ) === 'add_white' ) {
			$this->ss_send_approval_email( $ip, $stats, $options, $post );
		}
		return false;
	}
	public function ss_send_approval_email( $ip, $stats = array(), $options = array(), $post = array() ) {
		if ( !array_key_exists( 'emailrequest', $options ) ) {
			return false;
		}
		if ( $options['emailrequest'] === 'N' ) {
			return false;
		}
		if ( !isset( $_GET['ip'] ) ) {
			return false;
		}
		$wlrequests = $stats['wlrequests'];
		$request    = array();
		foreach ( $wlrequests as $r ) {
			if ( $r[0] === $_GET['ip'] ) {
				$request = $r;
				break;
			}
		}
		if ( empty( $request ) || !isset( $request[1] ) ) {
			return false;
		}
		$to = $request[1];
		if ( ! is_email( $to ) ) {
			return false;
		}
		$ke 	 = sanitize_text_field( $to );
		$blog    = get_bloginfo( 'name' );
		$subject = sprintf( '%1$s: Your Request Has Been Approved', $blog );
		$subject = str_replace( '&', 'and', $subject );
		$message = sprintf( 'Apologies for the inconvenience. You\'ve now been cleared for landing on %1$s.', $blog );
		$message = str_replace( '&', 'and', $message );
		$headers = array( 'From: ' . get_option( 'admin_email' ) );
		wp_mail( $to, $subject, $message, $headers );
		return true;
	}
}

?>