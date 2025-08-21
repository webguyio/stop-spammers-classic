<?php

if ( !defined( 'ABSPATH' ) ) {
	http_response_code( 404 );
	die();
}

function ss_admin_menu_l() {
	$iconpng = SS_PLUGIN_URL . 'images/sticon.png';
	add_menu_page(
		'Stop Spammers', // $page_title,
		'Stop Spammers', // $menu_title,
		'manage_options', // $capability,
		'stop_spammers', // $menu_slug,
		'ss_summary', // $function
		$iconpng, // $icon_url,
		78.92   // $position
	);
	if ( class_exists( 'Jetpack' ) && Jetpack::is_module_active( 'protect' ) ) {
		return;
	}
	add_submenu_page(
		'stop_spammers', // plugins parent
		'Summary — Stop Spammers', // $page_title,
		'Summary', // $menu_title,
		'manage_options', // $capability,
		'stop_spammers', // $menu_slug,
		'ss_summary' // $function
	);
	add_submenu_page(
		'stop_spammers', // plugins parent
		'Protection Options — Stop Spammers', // $page_title,
		'Protection Options', // $menu_title,
		'manage_options', // $capability,
		'ss_options', // $menu_slug,
		'ss_options' // function
	);
	add_submenu_page(
		'stop_spammers', // plugins parent
		'Allow Lists — Stop Spammers', // $page_title,
		'Allow Lists', // $menu_title,
		'manage_options', // $capability,
		'ss_allow_list', // $menu_slug,
		'ss_allowlist_settings' // function
	);
	add_submenu_page(
		'stop_spammers', // plugins parent
		'Block Lists — Stop Spammers', // $page_title,
		'Block Lists', // $menu_title,
		'manage_options', // $capability,
		'ss_block_list', // $menu_slug,
		'ss_blocklist_settings' // function
	);
	add_submenu_page(
		'stop_spammers', // plugins parent
		'Challenge & Block — Stop Spammers', // $page_title,
		'Challenge & Block', // $menu_title,
		'manage_options', // $capability,
		'ss_challenge', // $menu_slug,
		'ss_challenges' // function
	);
	add_submenu_page(
		'stop_spammers', // plugins parent
		'Web Services — Stop Spammers', // $page_title,
		'Web Services', // $menu_title,
		'manage_options', // $capability,
		'ss_webservices_settings', // $menu_slug,
		'ss_webservices_settings'
	);
	add_submenu_page(
		'stop_spammers', // plugins parent
		'Cache — Stop Spammers', // $page_title,
		'Cache', // $menu_title,
		'manage_options', // $capability,
		'ss_cache', // $menu_slug,
		'ss_cache' // function
	);
	add_submenu_page(
		'stop_spammers', // plugins parent
		'Log Report — Stop Spammers', // $page_title,
		'Log Report', // $menu_title,
		'manage_options', // $capability,
		'ss_reports', // $menu_slug,
		'ss_reports' // function
	);
	add_submenu_page(
		'stop_spammers', // plugins parent
		'Diagnostics — Stop Spammers', // $page_title,
		'Diagnostics', // $menu_title,
		'manage_options', // $capability,
		'ss_diagnostics', // $menu_slug,
		'ss_diagnostics' // function
	);
	if ( function_exists( 'is_multisite' ) && is_multisite() ) {
		add_submenu_page(
			'stop_spammers', // plugins parent
			'Multisite — Stop Spammers', // $page_title,
			'Multisite', // $menu_title,
			'manage_options', // $capability,
			'ss_network', // $menu_slug,
			'ss_network'
		);
	}
}

function ss_summary() {
	include_setting( "ss_summary.php" );
}

function ss_network() {
	include_setting( "ss_network.php" );
}

function ss_webservices_settings() {
	include_setting( "ss_webservices_settings.php" );
}

function ss_allowlist_settings() {
	include_setting( "ss_allowlist_settings.php" );
}

function ss_blocklist_settings() {
	include_setting( "ss_blocklist_settings.php" );
}

function ss_options() {
	include_setting( "ss_options.php" );
}

function ss_access() {
	include_setting( "ss_access.php" );
}

function ss_reports() {
	include_setting( "ss_reports.php" );
}

function ss_cache() {
	include_setting( "ss_cache.php" );
}

function ss_change_admin() {
	include_setting( "ss_change_admin.php" );
}

function ss_challenges() {
	include_setting( "ss_challenge.php" );
}

function ss_contribute() {
	include_setting( "ss_contribute.php" );
}

function ss_diagnostics() {
	include_setting( "ss_diagnostics.php" );
}

function include_setting( $file ) {
	sfs_errorsonoff();
	$ppath = plugin_dir_path( __FILE__ );
	if ( file_exists( $ppath . $file ) ) {
		require_once( $ppath . $file );
	} else {
		echo '<br>Missing File: ' . $ppath, $file . ' <br>';
	}
	sfs_errorsonoff( 'off' );
}

function ss_fix_post_vars() {
	if ( !empty( $_POST ) ) {
		$keys = isset( $_POST ) ? ( array ) array_keys( $_POST ) : array();
		foreach ( $keys as $key ) {
			try {
				$key = sanitize_key( $key ); 
				if ( is_string( $_POST[$key] ) ) {
					if ( strpos( $_POST[$key], "\n" ) !== false ) {
						$val2 = sanitize_textarea_field( $_POST[$key] );
					} else {
						$val2 = sanitize_text_field( $_POST[$key] );
					}
					$_POST[$key] = $val2;
				}
			} catch ( Exception $e ) {}
		}
	}
}

?>
