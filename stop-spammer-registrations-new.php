<?php
/*
Plugin Name: Stop Spammers
Plugin URI: https://damspam.com/
Description: A simplified, restored, and preserved version of the original Stop Spammers plugin.
Version: 2025
Requires at least: 3.0
Requires PHP: 5.0
Author: Web Guy
Author URI: https://webguy.io/
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl.html
*/

// networking requires a couple of globals
define( 'SS_VERSION', '2025' );
define( 'SS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SS_PLUGIN_FILE', plugin_dir_path( __FILE__ ) );
define( 'SS_PLUGIN_DATA', plugin_dir_path( __FILE__ ) . 'data/' );
$ss_check_sempahore = false;

if ( !defined( 'ABSPATH' ) ) {
	http_response_code( 404 );
	die();
}

function ss_assets_version() {
	return defined( 'WP_DEBUG' ) && WP_DEBUG ? ( string ) time() : SS_VERSION;
}

// load admin styles
function ss_styles() {
	$version = ss_assets_version();
	wp_enqueue_style(
		'ss-admin',
		plugin_dir_url( __FILE__ ) . 'css/admin.css',
		array(),
		$version
	);
}
add_action( 'admin_print_styles', 'ss_styles' );

// admin notice for users
function ss_admin_notice() {
	$user_id = get_current_user_id();
	$admin_url = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http' ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$param = ( count( $_GET ) ) ? '&' : '?';
	if ( !get_user_meta( $user_id, 'ss_notice_dismissed_100' ) && current_user_can( 'manage_options' ) ) {
		echo '<div class="notice notice-info"><p><a href="' . esc_url( $admin_url ), esc_url_raw( $param ) . 'dismiss" class="alignright" style="text-decoration:none"><big>Ⓧ</big></a><big><strong>Stop Spammers Development Has Slowed Down</strong></big><p><a href="https://github.com/webguyio/dam-spam/issues/8" class="button-primary" style="border-color:#4aa863;background:#4aa863" target="_blank">What happened?</a></p></div>';
	}
}
add_action( 'admin_notices', 'ss_admin_notice' );

// dismiss admin notice for users
function ss_notice_dismissed() {
	$user_id = get_current_user_id();
	if ( isset( $_GET['dismiss'] ) ) {
		add_user_meta( $user_id, 'ss_notice_dismissed_100', 'true', true );
	}
}
add_action( 'admin_init', 'ss_notice_dismissed' );

// WooCommerce warning for users
function ss_wc_admin_notice() {
	if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		$user_id = get_current_user_id();
		if ( !get_user_meta( $user_id, 'ss_wc_notice_dismissed' ) && current_user_can( 'manage_options' ) ) {
			echo '<div class="notice notice-info"><p style="color:purple"><big><strong>WooCommerce Detected</strong></big> | We recommend <a href="admin.php?page=ss_options">adjusting these options</a> if you experience any issues using WooCommerce and Stop Spammers together.<a href="?sswc-dismiss" class="alignright">Dismiss</a></p></div>';
		}
	}
}
add_action( 'admin_notices', 'ss_wc_admin_notice' );

// dismiss WooCommerce warning for users
function ss_wc_notice_dismissed() {
	if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		$user_id = get_current_user_id();
		if ( isset( $_GET['sswc-dismiss'] ) ) {
			add_user_meta( $user_id, 'ss_wc_notice_dismissed', 'true', true );
		}
	}
}
add_action( 'admin_init', 'ss_wc_notice_dismissed' );

// hook the init event to start work
add_action( 'init', 'ss_init', 0 );

// dummy filters for addons
add_filter( 'ss_addons_allow', 'ss_addons_d', 0 );
add_filter( 'ss_addons_block', 'ss_addons_d', 0 );
add_filter( 'ss_addons_get', 'ss_addons_d', 0 );

// done - the reset will be done in the init event
/*******************************************************************
 * How it works:
 * 1) Network blog MU installation
 * if networked switch then install network filters for options
 * all options will redirect to Blog #1 options so no need to set up for each blog
 * else
 * normal install
 * 2) Case when user is logged in
 * setup user and comment actions
 * if networked
 * set up right-now and options to install only on Network Admin page
 * else
 * install hooks and filters for right-now options screens
 * 3) When user is not logged in
 * hook template redirect
 * hook init
 * 4) on template redirect check for bad requests and 404s on exploit items
 * 5) on init check for POST or GET
 * 6) on post gather post variables and check for spam, logins, or exploits
 * 7) on get check for access blocking
 * 8) on block
 * update counters
 * update cache
 * update log
 * present rejection screen - could contain email request form or CAPTCHA
 * 9) on email request form send email to admin requesting Allow List
 * 10) on CAPTCHA success add to Good Cache, remove from Bad Cache, update counters, log success
 */
function ss_init() {
	remove_action( 'init', 'ss_init' );
	add_filter( 'pre_user_login', 'ss_user_reg_filter', 1, 1 );
	// incompatible with a Jetpack submit
	if ( !empty( $_POST ) && array_key_exists( 'jetpack_protect_num', $_POST ) ) {
		return;
	}
	// eMember trying to log in - disable plugin for eMember logins
	if ( function_exists( 'wp_emember_is_member_logged_in' ) ) {
		// only eMember function I could find after 30 seconds of Googling
		if ( !empty( $_POST ) && array_key_exists( 'login_pwd', $_POST ) ) {
			return;
		}
	}
	// set up the Akismet hit
	add_action( 'akismet_spam_caught', 'ss_log_akismet' ); // hook Akismet spam
	$muswitch = 'N';
	if ( function_exists( 'is_multisite' ) && is_multisite() ) {
		switch_to_blog( 1 );
		$muswitch = get_option( 'ss_muswitch' );
		if ( $muswitch != 'N' ) {
			$muswitch = 'Y';
		}
		restore_current_blog();
		if ( $muswitch == 'Y' ) {
			// install the hooks for options
			define( 'SS_MU', $muswitch );
			ss_sp_require( 'includes/ss-mu-options.php' );
			ssp_global_setup();
		}
	} else {
		define( 'SS_MU', $muswitch );
	}
	if ( function_exists( 'is_user_logged_in' ) ) {
		// check to see if we need to hook the settings
		// load the settings if logged in
		if ( is_user_logged_in() ) {
			remove_filter( 'pre_user_login', 'ss_user_reg_filter', 1 );
			if ( current_user_can( 'manage_options' ) ) {
				ss_sp_require( 'includes/ss-admin-options.php' );
				return;
			}
		}
	}
	// user is not logged in - we can do checks
	// add the new user hooks
	global $wp_version;
	if ( !version_compare( $wp_version, "3.1", "<" ) ) { // only in newer versions
		add_action( 'user_register', 'ss_new_user_ip' );
		add_action( 'wp_login', 'ss_log_user_ip', 10, 2 );
	}
	// don't do anything else if the eMember is logged in
	if ( function_exists( 'wp_emember_is_member_logged_in' ) ) {
		if ( wp_emember_is_member_logged_in() ) {
			return;
		}
	}
	// can we check for $_GET registrations?
	if ( isset( $_POST ) && !empty( $_POST ) ) {
		// see if we are returning from a block
		if ( array_key_exists( 'ss_block', $_POST ) && array_key_exists( 'kn', $_POST ) ) {
			// block form hit
			if ( !empty( $_POST['kn'] ) && wp_verify_nonce( $_POST['kn'], 'ss_stopspam_block' ) ) {
				// call the checker program
				sfs_errorsonoff();
				$options = ss_get_options();
				$stats   = ss_get_stats();
				$post	 = get_post_variables();
				be_load( 'ss_challenge', ss_get_ip(), $stats, $options, $post );
				// if we come back we continue as normal
				sfs_errorsonoff( 'off' );
				return;
			}
		}
		// need to check that we are not Allow Listed
		// don't check if IP is Google, etc.
		// check to see if we are doing a post with values
		// better way to check for Jetpack
		if ( class_exists( 'Jetpack' ) && Jetpack::is_module_active( 'protect' ) ) {
			return;
		}
		$post = get_post_variables();
		if ( !empty( $post['email'] ) || !empty( $post['author'] ) || !empty( $post['comment'] ) ) { // must be a login or a comment which require minimum stuff
			// remove_filter( 'pre_user_login', 'ss_user_reg_filter', 1 );
			// sfs_debug_msg( 'email or author ' . print_r( $post, true ) );
			$reason = ss_check_white();
			if ( $reason !== false ) {
				// sfs_debug_msg( "return from white $reason" );
				return;
			}
			// sfs_debug_msg( 'past white ' );
			ss_check_post(); // on POST check if we need to stop comments or logins
		} else {
		// sfs_debug_msg( 'no email or author ' . print_r( $post, true ) );
		}
	} else {
		// this is a get - check for get addons
		$addons = array();
		$addons = apply_filters( 'ss_addons_get', $addons );
		// these are the allow before addons
		// returns array
		// [0] = class location
		// [1] = class name (also used as counter)
		// [2] = addon name
		// [3] = addon author
		// [4] = addon description
		if ( !empty( $addons ) && is_array( $addons ) ) {
			foreach ( $addons as $add ) {
				if ( !empty( $add ) && is_array( $add ) ) {
					$options = ss_get_options();
					$stats   = ss_get_stats();
					$post	 = get_post_variables();
					$reason  = be_load( $add, ss_get_ip(), $stats, $options );
					if ( $reason !== false ) {
						// need to log a passed hit on post here
						remove_filter( 'pre_user_login', 'ss_user_reg_filter', 1 );
						ss_log_bad( ss_get_ip(), $reason, $add[1], $add );
						return;
					}
				}
			}
		}
	}
	add_action( 'template_redirect', 'ss_check_404s' ); // check missed hits for robots scanning for exploits
	add_action( 'ss_stop_spam_caught', 'ss_caught_action', 10, 2 ); // hook stop spam  - for testing
	add_action( 'ss_stop_spam_ok', 'ss_stop_spam_ok', 10, 2 ); // hook stop spam - for testing

	// captcha for forms
	$options = ss_get_options();
	if ( isset( $options['form_captcha_login'] ) and $options['form_captcha_login'] === 'Y' ) {
		add_action( 'login_form', 'ss_add_captcha' );
	}
	if ( isset( $options['form_captcha_registration'] ) and $options['form_captcha_registration'] === 'Y' ) {
		add_action( 'register_form', 'ss_add_captcha' );
	}
	if ( isset( $options['form_captcha_comment'] ) and $options['form_captcha_comment'] === 'Y' ) {
		add_action( 'comment_form_after_fields', 'ss_add_captcha' );
	}
}

// start of loadable functions
function ss_sp_require( $file ) {
	require_once( $file );
}

/************************************************************
 * function ss_sfs_check_admin()
 * checks to see if the current admin can login
 *************************************************************/
function ss_sfs_check_admin() {
	ss_sfs_reg_add_user_to_allowlist(); // also saves options
}

function ss_sfs_reg_add_user_to_allowlist() {
	$options = ss_get_options();
	$stats   = ss_get_stats();
	$post	 = get_post_variables();
	return be_load( 'ss_addtoallowlist', ss_get_ip(), $stats, $options );
}

function ss_set_stats( &$stats, $addon = array() ) {
	// this sets the stats
	if ( empty( $addon ) || !is_array( $addon ) ) {
		// need to know if the spam count has changed
		if ( $stats['spcount'] == 0 || empty( $stats['spdate'] ) ) {
			$stats['spdate'] = date( 'Y/m/d', time() + ( get_option( 'gmt_offset' ) * 3600 ) );
		}
		if ( $stats['spmcount'] == 0 || empty( $stats['spmdate'] ) ) {
			$stats['spmdate'] = date( 'Y/m/d', time() + ( get_option( 'gmt_offset' ) * 3600 ) );
		}
	} else {
		// update addon stats
		// addon stats are kept in addonstats array in stats
		$addonstats = array();
		if ( array_key_exists( 'addonstats', $stats ) ) {
			$addonstats = $stats['addonstats'];
		}
		$addstats = array();
		if ( array_key_exists( $addon[1], $addonstats ) ) {
			$addstats = $addonstats[$addon[1]];
		} else {
			$addstats = array( 0, $addon );
		}
		$addstats[0] ++;
		$addonstats[$addon[1]] = $addstats;
		$stats['addonstats']	 = $addonstats;
	}
	// other checks? - I might start compressing this, since it can get large
	update_option( 'ss_stop_sp_reg_stats', $stats );
}

function ss_get_stats() {
	$stats = get_option( 'ss_stop_sp_reg_stats' );
	if ( !empty( $stats ) && is_array( $stats ) && array_key_exists( 'version', $stats ) && $stats['version'] == SS_VERSION ) {
		return $stats;
	}
	return be_load( 'ss_get_stats', '' );
}

function ss_get_options() {
	$options = get_option( 'ss_stop_sp_reg_options' );
	$st	     = array();
	if ( !empty( $options ) && is_array( $options ) && array_key_exists( 'version', $options ) && $options['version'] == SS_VERSION ) {
		return $options;
	}
	return be_load( 'ss_get_options', '' );
}

function ss_set_options( $options ) {
	update_option( 'ss_stop_sp_reg_options', $options );
}

function ss_get_ip() {
	return $_SERVER['REMOTE_ADDR'];
}

function ss_admin_menu() {
	if ( !function_exists( 'ss_admin_menu_l' ) ) {
		ss_sp_require( 'settings/settings.php' );
	}
	sfs_errorsonoff();
	ss_admin_menu_l();
	sfs_errorsonoff( 'off' );
}

function ss_check_site_get() {
	$options = ss_get_options();
	$stats   = ss_get_stats();
	$post	 = get_post_variables();
	sfs_errorsonoff();
	$ret = be_load( 'ss_check_site_get', ss_get_ip(), $stats, $options, $post );
	sfs_errorsonoff( 'off' );
	return $ret;
}

function ss_check_post() {
	sfs_errorsonoff();
	$options = ss_get_options();
	$stats   = ss_get_stats();
	$post	 = get_post_variables();
	$ret	 = be_load( 'ss_check_post', ss_get_ip(), $stats, $options, $post );
	sfs_errorsonoff( 'off' );
	return $ret;
}

function ss_check_404s() { // check for exploits on 404s
	sfs_errorsonoff();
	$options = ss_get_options();
	$stats   = ss_get_stats();
	$ret	 = be_load( 'ss_check_404s', ss_get_ip(), $stats, $options );
	sfs_errorsonoff( 'off' );
	return $ret;
}

function ss_log_bad( $ip, $reason, $chk, $addon = array() ) {
	$options		= ss_get_options();
	$stats		    = ss_get_stats();
	$post		    = get_post_variables();
	$post['reason'] = $reason;
	$post['chk']	= $chk;
	$post['addon']  = $addon;
	return be_load( 'ss_log_bad', ss_get_ip(), $stats, $options, $post );
}

function ss_log_akismet() {
	sfs_errorsonoff();
	$options = ss_get_options();
	$stats   = ss_get_stats();
	if ( $options['chkakismet'] != 'Y' ) {
		return false;
	}
	// check whitelists first
	$reason = ss_check_white();
	if ( $reason !== false ) {
		return;
	}
	// not on Allow Lists
	$post		    = get_post_variables();
	$post['reason'] = 'from Akismet';
	$post['chk']	= 'chkakismet';
	$ansa		    = be_load( 'ss_log_bad', ss_get_ip(), $stats, $options, $post );
	sfs_errorsonoff( 'off' );
	return $ansa;
}

function ss_log_good( $ip, $reason, $chk, $addon = array() ) {
	$options		= ss_get_options();
	$stats		    = ss_get_stats();
	$post		    = get_post_variables();
	$post['reason'] = $reason;
	$post['chk']	= $chk;
	$post['addon']  = $addon;
	return be_load( 'ss_log_good', ss_get_ip(), $stats, $options, $post );
}

function ss_check_white() {
	sfs_errorsonoff();
	$options = ss_get_options();
	$stats   = ss_get_stats();
	$post	 = get_post_variables();
	$ansa	 = be_load( 'ss_check_white', ss_get_ip(), $stats, $options, $post );
	sfs_errorsonoff( 'off' );
	return $ansa;
}

function ss_check_white_block() {
	sfs_errorsonoff();
	$options	   = ss_get_options();
	$stats		   = ss_get_stats();
	$post		   = get_post_variables();
	$post['block'] = true;
	$ansa		   = be_load( 'ss_check_white', ss_get_ip(), $stats, $options, $post );
	sfs_errorsonoff( 'off' );
	return $ansa;
}

function be_load( $file, $ip, &$stats = array(), &$options = array(), &$post = array() ) {
	// all classes have a process() method
	// all classes have the same name as the file being loaded
	// only executes the file if there is an option set with value 'Y' for the name
	if ( empty( $file ) ) {
		return false;
	}
	// load the be_module if does not exist
	if ( !class_exists( 'be_module' ) ) {
		require_once( 'classes/be_module.class.php' );
	}
	// if ( $ip == null ) $ip = ss_get_ip();
	// for some loads we use an absolute path
	// if it is an addon, it has the absolute path to the be_module
	if ( is_array( $file ) ) { // add-ons pass their array
		// this is an absolute location so load it directly
		if ( !file_exists( $file[0] ) ) {
			sfs_debug_msg( 'not found ' . print_r( $add, true ) );
			return false;
		}
		// require_once( $file[0] );
		// this loads a be_module class
		$class  = new $file[1]();
		$result = $class->process( $ip, $stats, $options, $post );
		$class  = null;
		unset( $class ); // doesn't do anything
		// memory_get_usage( true ); // force a garage collection
		return $result;
	}
	$ppath = plugin_dir_path( __FILE__ ) . 'classes/';
	$fd	= $ppath . $file . '.php';
	$fd	= str_replace( "/", DIRECTORY_SEPARATOR, $fd ); // Windows fix
	if ( !file_exists( $fd ) ) {
		// echo "<br><br>Missing $file $fd<br><br>";
		$ppath = plugin_dir_path( __FILE__ ) . 'modules/';
		$fd	= $ppath . $file . '.php';
		$fd	= str_replace( "/", DIRECTORY_SEPARATOR, $fd ); // Windows fix
	}
	if ( !file_exists( $fd ) ) {
		$ppath = plugin_dir_path( __FILE__ ) . 'modules/countries/';
		$fd	= $ppath . $file . '.php';
		$fd	= str_replace( "/", DIRECTORY_SEPARATOR, $fd ); // Windows fix
	}
	if ( !file_exists( $fd ) ) {
		echo '<br><br>Missing ' . esc_html( $file ), esc_html( $fd ) . '<br><br>';
		return false;
	}
	require_once( $fd );
	// this loads a be_module class
	// sfs_debug_msg( "loading $fd" );
	$class  = new $file();
	$result = $class->process( $ip, $stats, $options, $post );
	$class  = null;
	unset( $class ); // does nothing - take out
	return $result;
}

// this should be moved to a dynamic load, perhaps - it is one of the most common things
function get_post_variables() {
	// for WordPress and other login and comment programs
	// need to find: login password comment author email
	// copied from stop spammers plugin
	// made generic so it also checks "head" and "get" (as well as cookies)
	$ansa = array(
		'email'   => '',
		'author'  => '',
		'pwd'	  => '',
		'comment' => '',
		'subject' => '',
		'url'	  => ''
	);
	if ( empty( $_POST ) || !is_array( $_POST ) ) {
		return $ansa;
	}
	$p = $_POST;
	$search  = array(
		'email'   => array(
			'email',
			'e-mail',
			'user_email',
			'email-address',
			'your-email'
		),
		// 'input_' = WooCommerce forms
		'author'  => array(
			'author',
			'name',
			'username',
			'user_login',
			'signup_for',
			'log',
			'user',
			'_id',
			'your-name'
		),
		'pwd'	  => array(
			'pwd',
			'password',
			'psw',
			'pass',
			'secret'
		),
		'comment' => array(
			'comment',
			'message',
			'reply',
			'body',
			'excerpt',
			'your-message'
		),
		'subject' => array(
			'subject',
			'subj',
			'topic',
			'your-subject'
		),
		'url'	  => array(
			'url',
			'link',
			'site',
			'website',
			'blog_name',
			'blogname',
			'your-website'
		)
	);
	$emfound = false;
	// rewrite this
	foreach ( $search as $var => $sa ) {
		foreach ( $sa as $srch ) {
			foreach ( $p as $pkey => $pval ) {
				// see if the things in $srch live in post
				if ( stripos( $pkey, $srch ) !== false ) {
					// got a hit
					if ( is_array( $pval ) ) {
						$pval = print_r( $pval, true );
					}
					$ansa[$var] = $pval;
					break;
				}
			}
			if ( !empty( $ansa[$var] ) ) {
				break;
			}
		}
		if ( empty( $ansa[$var] ) && $var == 'email' ) { // empty email
			// did not get a hit so we need to try again and look for something that looks like an email
			foreach ( $p as $pkey => $pval ) {
				if ( stripos( $pkey, 'input_' ) ) {
					// might have an email
					if ( is_array( $pval ) ) {
						$pval = print_r( $pval, true );
					}
					if ( strpos( $pval, '@' ) !== false && strrpos( $pval, '.' ) > strpos( $pval, '@' ) ) {
						// close enough
						$ansa[$var] = $pval;
						break;
					}
				}
			}
		}
	}
	// sanitize input - some of this is stored in history and needs to be cleaned up
	foreach ( $ansa as $key => $value ) {
		// clean the variables even more
		$ansa[$key] = sanitize_text_field( $value ); // really clean gets rid of high value characters
	}
	if ( strlen( $ansa['email'] ) > 80 ) {
		$ansa['email'] = substr( $ansa['email'], 0, 77 ) . '...';
	}
	if ( strlen( $ansa['author'] ) > 80 ) {
		$ansa['author'] = substr( $ansa['author'], 0, 77 ) . '...';
	}
	if ( strlen( $ansa['pwd'] ) > 32 ) {
		$ansa['pwd'] = substr( $ansa['pwd'], 0, 29 ) . '...';
	}
	if ( strlen( $ansa['comment'] ) > 999 ) {
		$ansa['comment'] = substr( $ansa['comment'], 0, 996 ) . '...';
	}
	if ( strlen( $ansa['subject'] ) > 80 ) {
		$ansa['subject'] = substr( $ansa['subject'], 0, 77 ) . '...';
	}
	if ( strlen( $ansa['url'] ) > 80 ) {
		$ansa['url'] = substr( $ansa['url'], 0, 77 ) . '...';
	}
	// print_r( $ansa );
	// exit;
	return $ansa;
}

function ss_addons_d( $config = array() ) {
	// dummy function for testing
	return $config;
}

function ss_caught_action( $ip = '', $post = array() ) {
	// this is hit on spam detect for addons - added this for a template for testing - not needed
	// $post has all the standardized post variables plus reason and the chk that found the problem
	// good add-on would be a plugin to manage an SQL table where this stuff is stored
}

function ss_stop_spam_ok( $ip = '', $post = array() ) {
	// dummy function for testing
	// unreports spam
}

function really_clean( $s ) {
	// try to get all non-7-bit things out of the string
	if ( empty( $s ) ) {
		return '';
	}
	$ss = array_slice( unpack( "c*", "\0" . $s ), 1 );
	if ( empty( $ss ) ) {
		return $s;
	}
	$s = '';
	for ( $j = 0; $j < count( $ss ); $j ++ ) {
		if ( $ss[$j] < 127 && $ss[$j] > 31 ) {
			$s .= pack( 'C', $ss[$j] );
		}
	}
	return $s;
}

function load_be_module() {
	if ( !class_exists( 'be_module' ) ) {
		require_once( 'classes/be_module.class.php' );
	}
}

function ss_new_user_ip( $user_id ) {
	$ip = ss_get_ip();
	// sfs_debug_msg( "Checking reg filter login $x ( ss_user_ip ) = " . $ip . ", method = " . $_SERVER['REQUEST_METHOD'] . ", request = " . print_r( $_REQUEST, true ) );
	// check to see if the user is OK
	// add the users IP to new users
	update_user_meta( $user_id, 'signup_ip', $ip );
}

function ss_sfs_ip_column_head( $column_headers ) {
	$column_headers['signup_ip'] = 'IP Address';
	return $column_headers;
}

function ss_log_user_ip( $user_login = "", $user = "" ) {
	if ( empty( $user ) ) {
		return;
	}
	if ( empty( $user_login ) ) {
		return;
	}
	// add the user's IP to new users
	if ( !isset( $user->ID ) ) {
		return;
	}
	$user_id = $user->ID;
	// $ip = ss_get_ip();
	$ip		 = $_SERVER['REMOTE_ADDR'];
	$oldip   = get_user_meta( $user_id, 'signup_ip', true );
	if ( empty( $oldip ) || $ip != $oldip ) {
		update_user_meta( $user_id, 'signup_ip', $ip );
	}
}

// add registration date column to Users admin page
class SSRegDate {
	public function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
	}
	public function init() {
		add_filter( 'manage_users_columns', array( $this, 'users_columns' ) );
		add_action( 'manage_users_custom_column', array( $this, 'users_custom_column' ), 10, 3 );
		add_filter( 'manage_users_sortable_columns', array( $this, 'users_sortable_columns' ) );
		add_filter( 'request', array( $this, 'users_orderby_column' ) );
	}
	public static function users_columns( $columns ) {
		$columns['registerdate'] = 'Registered';
		return $columns;
	}
	public static function users_custom_column( $value, $column_name, $user_id ) {
		global $mode;
		$mode = empty( $_REQUEST['mode'] ) ? 'list' : $_REQUEST['mode'];
		if ( 'registerdate' != $column_name ) {
			return $value;
		} else {
			$user = get_userdata( $user_id );
			if ( is_multisite() && ( 'list' == $mode ) ) {
				$formatted_date = 'F jS, Y';
			} else {
				$formatted_date = 'F jS, Y \a\t g:i a';
			}
			$registered = strtotime( get_date_from_gmt( $user->user_registered ) );
			$registerdate = '<span>' . date_i18n( $formatted_date, $registered ) . '</span>' ;
			return $registerdate;
		}
	}
	public static function users_sortable_columns( $columns ) {
		$custom = array(
			'registerdate' => 'registered',
		);
		return wp_parse_args( $custom, $columns );
	}
	public static function users_orderby_column( $vars ) {
		if ( isset( $vars['orderby'] ) && 'registerdate' == $vars['orderby'] ) {
			$vars = array_merge( $vars, array(
				'meta_key' => 'registerdate',
				'orderby' => 'meta_value'
			) );
		}
		return $vars;
	}
}
new SSRegDate();

/***********************************
 * $user_email = apply_filters( 'user_registration_email', $user_email );
 * I am going to start checking this filter for registrations
 * add_filter( 'user_registration_email', ss_user_reg_filter, 1, 1 );
 ***********************************/
function ss_user_reg_filter( $user_login ) {
	// the plugin should be all initialized
	// check the IP, etc.
	sfs_errorsonoff();
	$options		= ss_get_options();
	$stats		    = ss_get_stats();
	// fake out the post variables
	$post		    = get_post_variables();
	$post['author'] = $user_login;
	$post['addon']  = 'chkRegister'; // not really an add-on - but may be moved out when working
	if ( $options['filterregistrations'] != 'Y' ) {
		remove_filter( 'pre_user_login', 'ss_user_reg_filter', 1 );
		sfs_errorsonoff( 'off' );
		return $user_login;
	}
	// if the suspect is already in the Bad Cache he does not get a second chance?
	// prevents looping
	$reason = be_load( 'chkbcache', ss_get_ip(), $stats, $options, $post );
	sfs_errorsonoff();
	if ( $reason !== false ) {
		$rejectmessage  = $options['rejectmessage'];
		$post['reason'] = 'Failed Registration: Bad Cache';
		$host['chk']	= 'chkbcache';
		$ansa		    = be_load( 'ss_log_bad', ss_get_ip(), $stats, $options, $post );
		wp_die( '$rejectmessage', 'Login Access Blocked', array( 'response' => 403 ) );
		exit();
	}
	// check periods
	$reason = be_load( 'chkperiods', ss_get_ip(), $stats, $options, $post );
	if ( $reason !== false ) {
		wp_die( 'Registration Access Blocked', 'Login Access Blocked', array( 'response' => 403 ) );
	}
	// check the whitelist
	$reason = ss_check_white();
	sfs_errorsonoff();
	if ( $reason !== false ) {
		$post['reason'] = 'Passed Registration:' . $reason;
		$ansa		    = be_load( 'ss_log_good', ss_get_ip(), $stats, $options, $post );
		sfs_errorsonoff( 'off' );
		return $user_login;
	}
	// check the blacklist
	// sfs_debug_msg( "Checking blacklist on registration: /r/n" . print_r( $post, true ) );
	$ret			= be_load( 'ss_check_post', ss_get_ip(), $stats, $options, $post );
	$post['reason'] = 'Passed Registration ' . $ret;
	$ansa		    = be_load( 'ss_log_good', ss_get_ip(), $stats, $options, $post );
	return $user_login;
}

// private mode
function ss_login_redirect() {
	global $pagenow, $post;
	$options = ss_get_options();
	if ( get_option( 'ssp_enable_custom_login', '' ) and $options['ss_private_mode'] == "Y" and ( !is_user_logged_in() && $post->post_name != 'login' ) ) {
		wp_redirect( site_url( 'login' ) ); exit;
	} else if ( $options['ss_private_mode'] == "Y" and ( !is_user_logged_in() && ( $pagenow != 'wp-login.php' and $post->post_name != 'login' ) ) ) {
		auth_redirect();
	}
}
add_action( 'wp', 'ss_login_redirect' );

function ss_add_captcha() {
	$options = ss_get_options();
	$html    = '';
	switch ( $options['chkcaptcha'] ) {
		case 'G':
			// reCAPTCHA
			$recaptchaapisite = $options['recaptchaapisite'];
			$html  = '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
			$html .= '<input type="hidden" name="recaptcha" value="recaptcha">';
			$html .= '<div class="g-recaptcha" data-sitekey="' . $recaptchaapisite . '"></div>';
		break;
		case 'H':
			// hCaptcha
			$hcaptchaapisite = $options['hcaptchaapisite'];
			$html  = '<script src="https://hcaptcha.com/1/api.js" async defer></script>';
			$html .= '<input type="hidden" name="h-captcha" value="h-captcha">';
			$html .= '<div class="h-captcha" data-sitekey="' . $hcaptchaapisite . '"></div>';
		break;
		case 'S':
			$solvmediaapivchallenge = $options['solvmediaapivchallenge'];
			$html   = '<script src="https://api-secure.solvemedia.com/papi/challenge.script?k=' . $solvmediaapivchallenge . '"></script>';
			$html  .= '<noscript>';
			$html  .= '<iframe src="https://api-secure.solvemedia.com/papi/challenge.noscript?k=' . $solvmediaapivchallenge . '" height="300" width="500" frameborder="0"></iframe><br>';
			$html  .= '<textarea name="adcopy_challenge" rows="3" cols="40"></textarea>';
			$html  .= '<input type="hidden" name="adcopy_response" value="manual_challenge">';
			$html  .= '</noscript>';
		break;
	}
	echo wp_kses_post( $html );
}

function ss_captcha_verify() {
	global $wpdb;
	$options = ss_get_options();
	$ip 	 = ss_get_ip();
	switch ( $options['chkcaptcha'] ) {
		case 'G':
			if ( array_key_exists( 'recaptcha', $_POST ) && !empty( $_POST['recaptcha'] ) && array_key_exists( 'g-recaptcha-response', $_POST ) ) {
				// check reCAPTCHA
				$recaptchaapisecret = $options['recaptchaapisecret'];
				$recaptchaapisite   = $options['recaptchaapisite'];
				if ( empty( $recaptchaapisecret ) || empty( $recaptchaapisite ) ) {
					return '<strong>Error:</strong> reCAPTCHA keys are not set.';
				} else {
					$g    = sanitize_textarea_field( $_REQUEST['g-recaptcha-response'] );
					$url  = "https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaapisecret&response=$g&remoteip=$ip";
					$resp = ss_read_file( $url );
					if ( strpos( $resp, '"success": true' ) === false ) {
						$msg = '<strong>Error:</strong> Google reCAPTCHA entry does not match. Try again.';
					}
				}
			}
		break;
		case 'H':
			if ( array_key_exists( 'h-captcha', $_POST ) && !empty( $_POST['h-captcha'] ) && array_key_exists( 'h-captcha-response', $_POST ) ) {
				// check hCaptcha
				$hcaptchaapisecret = $options['hcaptchaapisecret'];
				$hcaptchaapisite   = $options['hcaptchaapisite'];
				if ( empty( $hcaptchaapisecret ) || empty( $hcaptchaapisite ) ) {
					return '<strong>Error:</strong> hCaptcha keys are not set.';
				} else {
					$h    = sanitize_textarea_field( $_REQUEST['h-captcha-response'] );
					$url  = "https://hcaptcha.com/siteverify?secret=$hcaptchaapisecret&response=$h&remoteip=$ip";
					$resp = ss_read_file( $url );
					$response = json_decode( $resp );
					if ( !isset( $response->success ) or $response->success !== true ) {
						return '<strong>Error:</strong> hCaptcha entry does not match. Try again.';
					}
				}
			}
		break;
		case 'S':
			if ( array_key_exists( 'adcopy_challenge', $_POST ) && !empty( $_POST['adcopy_challenge'] ) ) {
				$solvmediaapivchallenge = $options['solvmediaapivchallenge'];
				$solvmediaapiverify	    = $options['solvmediaapiverify'];
				$adcopy_challenge	    = sanitize_textarea_field( $_REQUEST['adcopy_challenge'] );
				$adcopy_response		= sanitize_textarea_field( $_REQUEST['adcopy_response'] );
				$postdata = http_build_query(
					array(
						'privatekey' => $solvmediaapiverify,
						'challenge'  => $adcopy_challenge,
						'response'   => $adcopy_response,
						'remoteip'   => $ip
					)
				);
				$opts = array(
					'http' =>
						array(
							'method'  => 'POST',
							'header'  => 'Content-type: application/x-www-form-urlencoded',
							'content' => $postdata
						)
				);
				$body = array(
					'privatekey' => $solvmediaapiverify,
					'challenge'  => $adcopy_challenge,
					'response'   => $adcopy_response,
					'remoteip'   => $ip
				);
				$args = array(
					'user-agent'  => 'WordPress/' . '4.2' . '; ' . get_bloginfo( 'url' ),
					'blocking'	  => true,
					'headers'	  => array( 'Content-type: application/x-www-form-urlencoded' ),
					'method'	  => 'POST',
					'timeout'	  => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'body'		  => $body,
					'cookies'	  => array()
				);
				$url = 'https://verify.solvemedia.com/papi/verify/';
				$resultarray = wp_remote_post( $url, $args );
				$result	     = $resultarray['body'];
				if ( strpos( $result, 'true' ) === false ) {
					return '<strong>Error:</strong> CAPTCHA entry does not match. Try again.';
				}
			}
		break;
	}
	return true;
}

function ss_login_captcha_verify( $user ) {
	$options = ss_get_options();
	if ( !isset( $options['form_captcha_login'] ) or $options['form_captcha_login'] !== 'Y' ) {
		return $user;
	}
	$response = ss_captcha_verify();
	if ( $response !== true ) {
		return new WP_Error( 'ss_captcha_error', $response );
	}
	return $user;
}
add_filter( 'authenticate', 'ss_login_captcha_verify', 99 );

function ss_registration_captcha_verify( $errors ) {
	$options = ss_get_options();
	if ( !isset( $options['form_captcha_registration'] ) or $options['form_captcha_registration'] !== 'Y' ) {
		return $errors;
	}
	$response = ss_captcha_verify();
	if ( $response !== true ) {
		$errors->add( 'ss_captcha_error', $response );
	}
	return $errors;
}
add_filter( 'registration_errors', 'ss_registration_captcha_verify', 10 );

function ss_comment_captcha_verify( $approved ) {
	$options = ss_get_options();
	if ( !isset( $options['form_captcha_comment'] ) or $options['form_captcha_comment'] !== 'Y' ) {
		return $approved;
	}
	$response = ss_captcha_verify();
	if ( $response !== true ) {
		return new WP_Error( 'ss_captcha_error', $response, 403	);
	}
	return $approved;
}
add_filter( 'pre_comment_approved', 'ss_comment_captcha_verify', 99, 1 );

// action links
function ss_summary_link( $links ) {
	$links = array_merge( array( '<a href="' . admin_url( 'admin.php?page=stop_spammers' ) . '">' . 'Settings' . '</a>' ), $links );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'ss_summary_link' );

require_once( 'includes/stop-spam-utils.php' );

?>
