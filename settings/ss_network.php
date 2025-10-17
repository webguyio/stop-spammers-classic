<?php

if ( !defined( 'ABSPATH' ) ) {
	status_header( 404 );
	exit;
}

if ( !current_user_can( 'manage_options' ) ) {
	die( 'Access Blocked' );
}

ss_fix_post_vars();

?>

<div id="ss-plugin" class="wrap">
	<h1 class="ss_head"><img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) . 'images/stop-spammers-icon.png' ); ?>" class="ss_icon">Multisite</h1>
	<?php
	$now	  = gmdate( 'Y/m/d H:i:s', time() + ( get_option( 'gmt_offset' ) * 3600 ) );
	// $ip=ss_get_ip();
	$ip 	  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$nonce	  = '';
	$muswitch = get_option( 'ss_muswitch' );
	if ( empty( $muswitch ) ) {
		$muswitch = 'N';
	}
	if ( array_key_exists( 'ss_stop_spammers_control', $_POST ) ) {
		$nonce = sanitize_text_field( wp_unslash( $_POST['ss_stop_spammers_control'] ) );
	}
	if ( wp_verify_nonce( $nonce, 'ss_stopspam_update' ) ) {
		if ( array_key_exists( 'action', $_POST ) ) {
			if ( array_key_exists( 'muswitch', $_POST ) ) {
				$muswitch = trim( sanitize_text_field( wp_unslash( $_POST['muswitch'] ) ) );
			}
			if ( empty( $muswitch ) ) {
				$muswitch = 'N';
			}
			if ( $muswitch != 'Y' ) {
				$muswitch = 'N';
			}
			update_option( 'ss_muswitch', $muswitch );
			echo '<h2>Options Updated</h2>';
		}
	} else {
	// echo "no nonce<br>";
	}
	$nonce = wp_create_nonce( 'ss_stopspam_update' );
	?>
	<form method="post" action="">
		<input type="hidden" name="ss_stop_spammers_control" value="<?php echo esc_html( $nonce ); ?>">
		<input type="hidden" name="action" value="update mu settings">
		<span style="font-weight:bold;font-size:1.2em">Network Blog Option</span>
		<p>Networked ON: <input name="muswitch" type="radio" value='Y' <?php if ( $muswitch == 'Y' ) { echo 'checked="true"'; } ?>>
		<br>
		Networked OFF: <input name="muswitch" type="radio" value='N' <?php if ( $muswitch != 'Y' ) { echo 'checked="true"'; } ?>>
		<br>
		If you are running WPMU and want to control options and history through the main login admin panel, select ON. If you select OFF, each blog will have to configure the plugin separately, and each blog will have a separte history.</p>
		<p class="submit"><input class="button-primary" value="Save Changes" type="submit"></p>
	</form>
</div>