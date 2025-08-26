<?php

if ( !defined( 'ABSPATH' ) ) {
	http_response_code( 404 );
	die();
}

if ( !current_user_can( 'manage_options' ) ) {
	die( 'Access Blocked' );
}

if ( class_exists( 'Jetpack' ) && Jetpack::is_module_active( 'protect' ) ) {
	echo '<div>Jetpack Protect has been detected. Because of a conflict, Stop Spammers has disabled itself.<br>You do not need to disable Jetpack, just the Protect feature.</div>';
	return;
}

ss_fix_post_vars();
$stats = ss_get_stats();
extract( $stats );
$now = gmdate( 'Y/m/d H:i:s', time() + ( get_option( 'gmt_offset' ) * 3600 ) );

// counter list - this should be copied from the get option utility
// counters should have the same name as the YN switch for the check
// I see lots of missing counters here
$counters = array(
	'cntchkcloudflare'	  => 'Pass Cloudflare',
	'cntchkgcache'		  => 'Pass Good Cache',
	'cntchkakismet'	      => 'Reported by Akismet',
	'cntchkgenallowlist'  => 'Pass Generated Allow List',
	'cntchkgoogle'		  => 'Pass Google',
	'cntchkmiscallowlist' => 'Pass Allow List',
	'cntchkpaypal'		  => 'Pass PayPal',
	'cntchkscripts'	      => 'Pass Scripts',
	'cntchkvalidip'	      => 'Pass Uncheckable IP',
	'cntchkwlem'		  => 'Allow List Email',
	'cntchkuserid'		  => 'Allow Username',
	'cntchkwlist'		  => 'Pass Allow List IP',
	'cntchkyahoomerchant' => 'Pass Yahoo Merchant',
	'cntchk404'		      => '404 Exploit Attempt',
	'cntchkaccept'		  => 'Bad or Missing Accept Header',
	'cntchkadmin'		  => 'Admin Login Attempt',
	'cntchkadminlog'	  => 'Passed Login OK',
	'cntchkagent'		  => 'Bad or Missing User Agent',
	'cntchkamazon'		  => 'Amazon AWS',
	'cntchkaws'		      => 'Amazon AWS Allow',
	'cntchkbcache'		  => 'Bad Cache',
	'cntchkblem'		  => 'Block List Email',
	'cntchkuserid'		  => 'Block Username',
	'cntchkblip'		  => 'Block List IP',
	'cntchkbotscout'	  => 'BotScout',
	'cntchkdisp'		  => 'Disposable Email',
	'cntchkdnsbl'		  => 'DNSBL Hit',
	'cntchkexploits'	  => 'Exploit Attempt',
	'cntchkgooglesafe'	  => 'Google Safe Browsing',
	'cntchkhoney'		  => 'Project Honeypot',
	'cntchkhosting'	      => 'Known Spam Host',
	'cntchkinvalidip'	  => 'Block Invalid IP',
	'cntchklong'		  => 'Long Email',
	'cntchkshort'		  => 'Short Email',
	'cntchkbbcode'		  => 'BBCode in Request',
	'cntchkreferer'	      => 'Bad HTTP_REFERER',
	'cntchksession'	      => 'Session Speed',
	'cntchksfs'		      => 'Stop Forum Spam',
	'cntchkspamwords'	  => 'Spam Words',
	'cntchkurlshort'	  => 'Short URLs',
	'cntchktld'		      => 'Email TLD',
	'cntchkubiquity'	  => 'Ubiquity Servers',
	'cntchkmulti'		  => 'Repeated Hits',
	'cntchkform'		  => 'Check for Standard Form',
	'cntchkAD'			  => 'Andorra',
	'cntchkAE'			  => 'United Arab Emirates',
	'cntchkAF'			  => 'Afghanistan',
	'cntchkAL'			  => 'Albania',
	'cntchkAM'			  => 'Armenia',
	'cntchkAR'			  => 'Argentina',
	'cntchkAT'			  => 'Austria',
	'cntchkAU'			  => 'Australia',
	'cntchkAX'			  => 'Aland Islands',
	'cntchkAZ'			  => 'Azerbaijan',
	'cntchkBA'			  => 'Bosnia And Herzegovina',
	'cntchkBB'			  => 'Barbados',
	'cntchkBD'			  => 'Bangladesh',
	'cntchkBE'			  => 'Belgium',
	'cntchkBG'			  => 'Bulgaria',
	'cntchkBH'			  => 'Bahrain',
	'cntchkBN'			  => 'Brunei Darussalam',
	'cntchkBO'			  => 'Bolivia',
	'cntchkBR'			  => 'Brazil',
	'cntchkBS'			  => 'Bahamas',
	'cntchkBY'			  => 'Belarus',
	'cntchkBZ'			  => 'Belize',
	'cntchkCA'			  => 'Canada',
	'cntchkCD'			  => 'Congo, Democratic Republic',
	'cntchkCH'			  => 'Switzerland',
	'cntchkCL'			  => 'Chile',
	'cntchkCN'			  => 'China',
	'cntchkCO'			  => 'Colombia',
	'cntchkCR'			  => 'Costa Rica',
	'cntchkCU'			  => 'Cuba',
	'cntchkCW'			  => 'CuraÃ§ao',
	'cntchkCY'			  => 'Cyprus',
	'cntchkCZ'			  => 'Czech Republic',
	'cntchkDE'			  => 'Germany',
	'cntchkDK'			  => 'Denmark',
	'cntchkDO'			  => 'Dominican Republic',
	'cntchkDZ'			  => 'Algeria',
	'cntchkEC'			  => 'Ecuador',
	'cntchkEE'			  => 'Estonia',
	'cntchkES'			  => 'Spain',
	'cntchkEU'			  => 'European Union',
	'cntchkFI'			  => 'Finland',
	'cntchkFJ'			  => 'Fiji',
	'cntchkFR'			  => 'France',
	'cntchkGB'			  => 'Great Britain',
	'cntchkGE'			  => 'Georgia',
	'cntchkGF'			  => 'French Guiana',
	'cntchkGI'			  => 'Gibraltar',
	'cntchkGP'			  => 'Guadeloupe',
	'cntchkGR'			  => 'Greece',
	'cntchkGT'			  => 'Guatemala',
	'cntchkGU'			  => 'Guam',
	'cntchkGY'			  => 'Guyana',
	'cntchkHK'			  => 'Hong Kong',
	'cntchkHN'			  => 'Honduras',
	'cntchkHR'			  => 'Croatia',
	'cntchkHT'			  => 'Haiti',
	'cntchkHU'			  => 'Hungary',
	'cntchkID'			  => 'Indonesia',
	'cntchkIE'			  => 'Ireland',
	'cntchkIL'			  => 'Israel',
	'cntchkIN'			  => 'India',
	'cntchkIQ'			  => 'Iraq',
	'cntchkIR'			  => 'Iran, Islamic Republic Of',
	'cntchkIS'			  => 'Iceland',
	'cntchkIT'			  => 'Italy',
	'cntchkJM'			  => 'Jamaica',
	'cntchkJO'			  => 'Jordan',
	'cntchkJP'			  => 'Japan',
	'cntchkKE'			  => 'Kenya',
	'cntchkKG'			  => 'Kyrgyzstan',
	'cntchkKH'			  => 'Cambodia',
	'cntchkKR'			  => 'Korea',
	'cntchkKW'			  => 'Kuwait',
	'cntchkKY'			  => 'Cayman Islands',
	'cntchkKZ'			  => 'Kazakhstan',
	'cntchkLA'			  => 'Lao People\'s Democratic Republic',
	'cntchkLB'			  => 'Lebanon',
	'cntchkLK'			  => 'Sri Lanka',
	'cntchkLT'			  => 'Lithuania',
	'cntchkLU'			  => 'Luxembourg',
	'cntchkLV'			  => 'Latvia',
	'cntchkMD'			  => 'Moldova',
	'cntchkME'			  => 'Montenegro',
	'cntchkMK'			  => 'Macedonia',
	'cntchkMM'			  => 'Myanmar',
	'cntchkMN'			  => 'Mongolia',
	'cntchkMO'			  => 'Macao',
	'cntchkMP'			  => 'Northern Mariana Islands',
	'cntchkMQ'			  => 'Martinique',
	'cntchkMT'			  => 'Malta',
	'cntchkMV'			  => 'Maldives',
	'cntchkMX'			  => 'Mexico',
	'cntchkMY'			  => 'Malaysia',
	'cntchkNC'			  => 'New Caledonia',
	'cntchkNI'			  => 'Nicaragua',
	'cntchkNL'			  => 'Netherlands',
	'cntchkNO'			  => 'Norway',
	'cntchkNP'			  => 'Nepal',
	'cntchkNZ'			  => 'New Zealand',
	'cntchkOM'			  => 'Oman',
	'cntchkPA'			  => 'Panama',
	'cntchkPE'			  => 'Peru',
	'cntchkPG'			  => 'Papua New Guinea',
	'cntchkPH'			  => 'Philippines',
	'cntchkPK'			  => 'Pakistan',
	'cntchkPL'			  => 'Poland',
	'cntchkPR'			  => 'Puerto Rico',
	'cntchkPS'			  => 'Palestinian Territory, Occupied',
	'cntchkPT'			  => 'Portugal',
	'cntchkPW'			  => 'Palau',
	'cntchkPY'			  => 'Paraguay',
	'cntchkQA'			  => 'Qatar',
	'cntchkRO'			  => 'Romania',
	'cntchkRS'			  => 'Serbia',
	'cntchkRU'			  => 'Russian Federation',
	'cntchkSA'			  => 'Saudi Arabia',
	'cntchkSC'			  => 'Seychelles',
	'cntchkSE'			  => 'Sweden',
	'cntchkSG'			  => 'Singapore',
	'cntchkSI'			  => 'Slovenia',
	'cntchkSK'			  => 'Slovakia',
	'cntchkSV'			  => 'El Salvador',
	'cntchkSX'			  => 'Sint Maarten',
	'cntchkSY'			  => 'Syrian Arab Republic',
	'cntchkTH'			  => 'Thailand',
	'cntchkTJ'			  => 'Tajikistan',
	'cntchkTM'			  => 'Turkmenistan',
	'cntchkTR'			  => 'Turkey',
	'cntchkTT'			  => 'Trinidad And Tobago',
	'cntchkTW'			  => 'Taiwan',
	'cntchkUA'			  => 'Ukraine',
	'cntchkUK'			  => 'United Kingdom',
	'cntchkUS'			  => 'United States',
	'cntchkUY'			  => 'Uruguay',
	'cntchkUZ'			  => 'Uzbekistan',
	'cntchkVC'			  => 'Saint Vincent And Grenadines',
	'cntchkVE'			  => 'Venezuela',
	'cntchkVN'			  => 'Viet Nam',
	'cntchkYE'			  => 'Yemen',
	'cntcap'			  => 'Passed CAPTCHA', // captha success
	'cntncap'			  => 'Failed CAPTCHA', // captha not success
	'cntpass'			  => 'Total Pass', // passed
);

$message  = '';
$nonce	  = '';

if ( array_key_exists( 'ss_stop_spammers_control', $_POST ) ) {
	$nonce = sanitize_text_field( wp_unslash( $_POST['ss_stop_spammers_control'] ) );
}

if ( wp_verify_nonce( $nonce, 'ss_stopspam_update' ) ) {
	if ( array_key_exists( 'clear', $_POST ) ) {
		foreach ( $counters as $v1 => $v2 ) {
			$stats[$v1] = 0;
		}
		$addonstats		     = array();
		$stats['addonstats'] = $addonstats;
		$msg			  	 = '<div class="notice notice-success is-dismissible"><p>Summary Cleared</p></div>';
		ss_set_stats( $stats );
		extract( $stats ); // extract again to get the new options
	}
	if ( array_key_exists( 'update_total', $_POST ) ) {
		$stats['spmcount'] = sanitize_text_field( wp_unslash( $_POST['spmcount'] ) );
		$stats['spmdate']  = sanitize_text_field( wp_unslash( $_POST['spmdate'] ) );
		ss_set_stats( $stats );
		extract( $stats ); // extract again to get the new options
	}
}

$nonce = wp_create_nonce( 'ss_stopspam_update' );

?>

<div id="ss-plugin" class="wrap">
	<h1 class="ss_head"><img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) . 'images/stop-spammers-icon.png' ); ?>" class="ss_icon">Summary</h1><br>
	Version: <strong><?php echo esc_html( SS_VERSION ); ?></strong>
	<?php if ( !empty( $summary ) ) { ?>
	<?php }
	$ip = ss_get_ip();
	?>
	| Your current IP address is: <strong><?php echo esc_html( $ip ); ?></strong>
	<?php
	// check the IP to see if we are local
	$ansa = be_load( 'chkvalidip', ss_get_ip() );
	if ( $ansa == false ) {
		$ansa = be_load( 'chkcloudflare', ss_get_ip() );
	}
	if ( $ansa !== false ) { ?>
		<p><?php echo 'This address is invalid for testing for the following reason:
			  <span style="font-weight:bold;font-size:1.2em">' . esc_html( $ansa ) . '</span>.<br>
			  If you working on a local installation of WordPress, this might be
			  OK. However, if the plugin reports that your
			  IP is invalid it may be because you are using Cloudflare or a proxy
			  server to access this page. This will make
			  it impossible for the plugin to check IP addresses. You may want to
			  go to the Stop Spammers Testing page in
			  order to test all possible reasons that your IP is not appearing as
			  the IP of the machine that your using to
			  browse this site.<br>
			  It is possible to use the plugin if this problem appears, but most
			  checking functions will be turned off. The
			  plugin will still perform spam checks which do not require an
			  IP.<br>
			  If the error says that this is a Cloudflare IP address, you can fix
			  this by installing the Cloudflare plugin. If
			  you use Cloudflare to protect and speed up your site then you MUST
			  install the Cloudflare plugin. This plugin
			  will be crippled until you install it.'; ?></p>
	<?php }
	// need the current guy
	$sname = '';
	if ( isset( $_SERVER['REQUEST_URI'] ) ) {
		$sname = sanitize_text_field( wp_unslash( $_SERVER["REQUEST_URI"] ) );
	}
	if ( empty( $sname ) ) {
		$_SERVER['REQUEST_URI'] = sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_NAME'] ) );
		$sname			  	    = sanitize_text_field( wp_unslash( $_SERVER["SCRIPT_NAME"] ) );
	}
	if ( strpos( $sname, '?' ) !== false ) {
		$sname = substr( $sname, 0, strpos( $sname, '?' ) );
	}
	if ( !empty( $msg ) ) {
		echo wp_kses_post( $msg );
	}
	$current_user_name = wp_get_current_user()->user_login;
	if ( $current_user_name == 'admin' ) {
		echo '<span class="notice notice-warning" style="display:block">SECURITY RISK: You are using the username "admin." This is an invitation to hackers to try and guess your password. Please change this.</span>';
	}
	$showcf = false; // hide this for now
	if ( $showcf && array_key_exists( 'HTTP_CF_CONNECTING_IP', $_SERVER ) && !function_exists( 'cloudflare_init' ) && !defined( 'W3TC' ) ) {
		echo '<span class="notice notice-warning" style="display:block">WARNING: Cloudflare Remote IP address detected. Please make sure to <a href="https://support.cloudflare.com/hc/sections/200805497-Restoring-Visitor-IPs" target="_blank">restore visitor IPs</a>.</span>';
	}
	?>
	<h2>Summary of Spam</h2>
	<div class="main-stats">
	<?php if ( $spcount > 0 ) { ?>
		<p><?php echo 'Stop Spammers has stopped <strong>' . esc_html( $spcount ) . '</strong> spammers since ' . esc_html( $spdate ) . '.'; ?></p>
	<?php }
	$num_comm = wp_count_comments();
	$num	  = number_format_i18n( $num_comm->spam );
	if ( $num_comm->spam > 0 && SS_MU != 'Y' ) { ?>
		<p><?php echo 'There are <a href="edit-comments.php?comment_status=spam">' . esc_html( $num ) . '</a> spam comments waiting for you to report.'; ?></p>
	<?php }
	$num_comm = wp_count_comments();
	$num	  = number_format_i18n( $num_comm->moderated );
	if ( $num_comm->moderated > 0 && SS_MU != 'Y' ) { ?>
		<p><?php echo 'There are <a href="edit-comments.php?comment_status=moderated">' . esc_html( $num ) . '</a> comments waiting to be moderated.'; ?></p></div>
	<?php }
	$summary = '';
	foreach ( $counters as $v1 => $v2 ) {
		if ( !empty( $stats[$v1] ) ) {
			  $summary .= "<div class='stat-box'>$v2: " . $stats[$v1] . "</div>";
		} else {
		// echo "  $v1 - $v2 , ";
		}
	}
	$addonstats = $stats['addonstats'];
	foreach ( $addonstats as $key => $data ) {
	// count is in data[0] and use the plugin name
		$summary .= "<div class='stat-box'>$key: " . $data[0] . "</div>";
	} ?>
	<?php
		echo wp_kses_post( $summary );
	?>
	<form method="post" action="">
		<input type="hidden" name="ss_stop_spammers_control" value="<?php echo esc_html( $nonce ); ?>">
		<input type="hidden" name="clear" value="clear summary">
		<p class="submit" style="clear:both"><input class="button-primary" value="Clear Summary" type="submit"></p>
	</form>
	<?php
	function ss_control()  {
		// this is the display of information about the page.
		if ( array_key_exists( 'resetOptions', $_POST ) ) {
			ss_force_reset_options();
		}
		$ip 	 = ss_get_ip();
		$nonce   = wp_create_nonce( 'ss_options' );
		$options = ss_get_options();
		extract( $options );
	}
	function ss_force_reset_options() {
		$ss_opt = sanitize_text_field( wp_unslash( $_POST['ss_opt'] ) );
		if ( !wp_verify_nonce( $ss_opt, 'ss_options' ) ) {	
			echo 'Session Timeout — Please Refresh the Page';
			exit;
		}
		if ( !function_exists( 'ss_reset_options' ) ) {
			ss_require( 'includes/ss-init-options.php' );
		}
		ss_reset_options();
		// clear the cache
		delete_option( 'ss_cache' );
	} ?>
</div>