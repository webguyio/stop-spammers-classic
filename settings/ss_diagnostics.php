<?php

if ( !defined( 'ABSPATH' ) ) {
	status_header( 404 );
	exit;
}

$stats   = ss_get_stats();
$options = ss_get_options();

if ( !current_user_can( 'manage_options' ) ) {
	die( 'Access Blocked' );
}

ss_fix_post_vars();
$now = gmdate( 'Y/m/d H:i:s', time() + ( get_option( 'gmt_offset' ) * 3600 ) );

// for session speed checks
// if ( !isset( $_POST ) || empty( $_POST ) ) { // no post defined
// $_SESSION['ss_stop_spammers_time'] = time();
// if ( !isset( $_COOKIE['ss_stop_spammers_time'] ) ) { // if previous set do not reset
// setcookie( 'ss_stop_spammers_time', strtotime( "now" ), strtotime( '+1 min' ) );
// }
// }
$ip  = ss_get_ip();
$hip = "unknown";

if ( array_key_exists( 'SERVER_ADDR', $_SERVER ) ) {
	$hip = sanitize_text_field( wp_unslash( $_SERVER["SERVER_ADDR"] ) );
}

$email   = '';
$author  = '';
$subject = '';
$body	 = '';

if ( array_key_exists( 'ip', $_POST ) ) {
	if ( filter_var( $_POST['ip'], FILTER_VALIDATE_IP ) ) {
		$ip = sanitize_text_field( wp_unslash( $_POST['ip'] ) );
	}
}

if ( array_key_exists( 'email', $_POST ) ) {
	$email = sanitize_email( wp_unslash( $_POST['email'] ) );
}

if ( array_key_exists( 'author', $_POST ) ) {
	$author = sanitize_text_field( wp_unslash( $_POST['author'] ) );
}

if ( array_key_exists( 'subject', $_POST ) ) {
	$subject = sanitize_text_field( wp_unslash( $_POST['subject'] ) );
}

if ( array_key_exists( 'body', $_POST ) ) {
	$body = sanitize_textarea_field( wp_unslash( $_POST['body'] ) );
}

$nonce = wp_create_nonce( 'ss_stopspam_update' );

?>

<div id="ss-plugin" class="wrap">
	<h1 class="ss_head"><img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) . 'images/stop-spammers-icon.png' ); ?>" class="ss_icon">Diagnostics & Threat Scan</h1>
	<form method="post" action="">
		<div class="ss_info_box">
			<input type="hidden" name="action" value="update">
			<input type="hidden" name="ss_stop_spammers_control" value="<?php echo esc_attr( $nonce ); ?>">
			<div class="mainsection">Option Testing
				<sup class="ss_sup"><a href="https://github.com/webguyio/stop-spammers/wiki/Docs:-Diagnostics-&-Threat-Scan#option-testing" target="_blank">?</a></sup>
			</div>
			<p>Run the settings against an IP address to see the results.</p>IP Address:<br>
			<input id="ssinput" name="ip" type="text" value="<?php echo esc_attr( $ip ); ?>">
			(Your server address is <?php echo esc_html( $hip ); ?>)<br><br>
			Email:<br>
			<input id="ssinput" name="email" type="text" value="<?php echo esc_attr( $email ); ?>"><br><br>
			Author/User:<br>
			<input id="ssinput" name="author" type="text" value="<?php echo esc_attr( $author ); ?>"><br><br>
			Subject:<br>
			<input id="ssinput" name="subject" type="text" value="<?php echo esc_attr( $subject ); ?>"><br><br>
			Comment:<br>
			<textarea name="body"><?php echo esc_html( $body ); ?></textarea><br>
			<div style="width:50%;float:left">
				<p class="submit"><input name="testopt" class="button-primary" value="Test Options" type="submit"></p>
			</div>
			<div style="width:50%;float:right">
				<p class="submit"><input name="testcountry" class="button-primary" value="Test Countries" type="submit"></p>
			</div>
			<br style="clear:both">
			<?php

			$nonce = '';

			if ( array_key_exists( 'ss_stop_spammers_control', $_POST ) ) {
					$nonce = sanitize_text_field( wp_unslash( $_POST['ss_stop_spammers_control'] ) );
			}

			if ( !empty( $nonce ) && wp_verify_nonce( $nonce, 'ss_stopspam_update' ) ) {
				$post = get_post_variables();
				if ( array_key_exists( 'testopt', $_POST ) ) {
					// do the test
					$optionlist = array(
						'chkaws',
						'chkcloudflare',
						'chkgcache',
						'chkgenallowlist',
						'chkgoogle',
						'chkmiscallowlist',
						'chkpaypal',
						'chkscripts',
						'chkvalidip',
						'chkwlem',
						'chkwluserid',
						'chkwlist',
						'chkwlistemail',
						'chkform',
						'chkyahoomerchant'
					);
					$m1 = memory_get_usage( true );
					$m2 = memory_get_peak_usage( true );
					echo '<br>Memory Used: ' . esc_html( $m1 ) . ' Peak: ' . esc_html( $m2 ) . '<br>';
					echo '<ul>Allow Checks<br>';
					foreach ( $optionlist as $chk ) {
						$ansa = be_load( $chk, $ip, $stats, $options, $post );
						if ( empty( $ansa ) ) {
							$ansa = 'OK';
						}
						echo wp_kses_post( "$chk: $ansa<br>" );
					}
					echo "</ul>";
					$optionlist = array(
						'chk404',
						'chkaccept',
						'chkadmin',
						'chkadminlog',
						'chkagent',
						'chkamazon',
						'chkbbcode',
						'chkbcache',
						'chkblem',
						'chkbluserid',
						'chkblip',
						'chkbotscout',
						'chkdisp',
						'chkdnsbl',
						'chkexploits',
						'chkgooglesafe',
						'chkhoney',
						'chkhosting',
						'chkinvalidip',
						'chklong',
						'chkmulti',
						'chkperiods',
						'chkreferer',
						'chksession',
						'chksfs',
						'chkshort',
						'chkspamwords',
						'chktld',
						'chkubiquity',
						'chkurlshort',
						'chkurls'
					);
					$m1 = memory_get_usage( true );
					$m2 = memory_get_peak_usage( true );
					echo '<br>Memory Used: ' . esc_html( $m1 ) . ' Peak: ' . esc_html( $m2 ) . '<br>';
					echo '<ul>Block Checks<br>';
					foreach ( $optionlist as $chk ) {
						$ansa = be_load( $chk, $ip, $stats, $options, $post );
						if ( empty( $ansa ) ) {
							$ansa = 'OK';
						}
						echo wp_kses_post( "$chk: $ansa<br>" );
					}
					echo "</ul>";
					$optionlist = array();
					$a1		    = apply_filters( 'ss_addons_allow', $optionlist );
					$a3		    = apply_filters( 'ss_addons_block', $optionlist );
					$a5		    = apply_filters( 'ss_addons_get', $optionlist );
					$optionlist = array_merge( $a1, $a3, $a5 );
					if ( !empty( $optionlist ) ) {
						echo "<ul>Add-on Checks<br>";
						foreach ( $optionlist as $chk ) {
							$ansa = be_load( $chk, $ip, $stats, $options, $post );
							if ( empty( $ansa ) ) {
								$ansa = 'OK';
							}
							$nm = $chk[1];
							echo wp_kses_post( "$nm: $ansa<br>" );
						}
						echo "</ul>";
					}
					$m1 = memory_get_usage( true );
					$m2 = memory_get_peak_usage( true );
					echo '<br>Memory Used: ' . esc_html( $m1 ) . ' Peak: ' . esc_html( $m2 ) . '<br>';
				}
				if ( array_key_exists( 'testcountry', $_POST ) ) {
					$optionlist = array(
						'chkAD',
						'chkAE',
						'chkAF',
						'chkAL',
						'chkAM',
						'chkAR',
						'chkAT',
						'chkAU',
						'chkAX',
						'chkAZ',
						'chkBA',
						'chkBB',
						'chkBD',
						'chkBE',
						'chkBG',
						'chkBH',
						'chkBN',
						'chkBO',
						'chkBR',
						'chkBS',
						'chkBY',
						'chkBZ',
						'chkCA',
						'chkCD',
						'chkCH',
						'chkCL',
						'chkCN',
						'chkCO',
						'chkCR',
						'chkCU',
						'chkCW',
						'chkCY',
						'chkCZ',
						'chkDE',
						'chkDK',
						'chkDO',
						'chkDZ',
						'chkEC',
						'chkEE',
						'chkES',
						'chkEU',
						'chkFI',
						'chkFJ',
						'chkFR',
						'chkGB',
						'chkGE',
						'chkGF',
						'chkGI',
						'chkGP',
						'chkGR',
						'chkGT',
						'chkGU',
						'chkGY',
						'chkHK',
						'chkHN',
						'chkHR',
						'chkHT',
						'chkHU',
						'chkID',
						'chkIE',
						'chkIL',
						'chkIN',
						'chkIQ',
						'chkIR',
						'chkIS',
						'chkIT',
						'chkJM',
						'chkJO',
						'chkJP',
						'chkKE',
						'chkKG',
						'chkKH',
						'chkKR',
						'chkKW',
						'chkKY',
						'chkKZ',
						'chkLA',
						'chkLB',
						'chkLK',
						'chkLT',
						'chkLU',
						'chkLV',
						'chkMD',
						'chkME',
						'chkMK',
						'chkMM',
						'chkMN',
						'chkMO',
						'chkMP',
						'chkMQ',
						'chkMT',
						'chkMV',
						'chkMX',
						'chkMY',
						'chkNC',
						'chkNI',
						'chkNL',
						'chkNO',
						'chkNP',
						'chkNZ',
						'chkOM',
						'chkPA',
						'chkPE',
						'chkPG',
						'chkPH',
						'chkPK',
						'chkPL',
						'chkPR',
						'chkPS',
						'chkPT',
						'chkPW',
						'chkPY',
						'chkQA',
						'chkRO',
						'chkRS',
						'chkRU',
						'chkSA',
						'chkSC',
						'chkSE',
						'chkSG',
						'chkSI',
						'chkSK',
						'chkSV',
						'chkSX',
						'chkSY',
						'chkTH',
						'chkTJ',
						'chkTM',
						'chkTR',
						'chkTT',
						'chkTW',
						'chkUA',
						'chkUK',
						'chkUS',
						'chkUY',
						'chkUZ',
						'chkVC',
						'chkVE',
						'chkVN',
						'chkYE'
					);
					// KE - Kenya
					// chkMA missing
					// SC - Seychelles
					$m1 = memory_get_usage( true );
					$m2 = memory_get_peak_usage( true );
					echo '<br>Memory Used: ' . esc_html( $m1 ) . ' Peak: ' . esc_html( $m2 ) . '<br>';
					foreach ( $optionlist as $chk ) {
						$ansa = be_load( $chk, $ip, $stats, $options, $post );
						if ( empty( $ansa ) ) {
							$ansa = 'OK';
						}
						echo wp_kses_post( "$chk: $ansa<br>" );
					}
					$m1 = memory_get_usage( true );
					$m2 = memory_get_peak_usage( true );
					echo '<br>Memory Used: ' . esc_html( $m1 ) . ' Peak: ' . esc_html( $m2 ) . '<br>';
				}
			}
			?>
		</div>
		<div class="ss_info_box">
			<div class="mainsection">Information Display
				<sup class="ss_sup"><a href="https://github.com/webguyio/stop-spammers/wiki/Docs:-Diagnostics-&-Threat-Scan#information-display" target="_blank">?</a></sup>
			</div>
			<div style="width:50%;float:left">
				<h2>Display All Options</h2>
				<p>You can dump all options here (useful for debugging):</p>
				<p class="submit"><input name="dumpoptions" class="button-primary" value="Dump Options" type="submit"></p>
			</div>
			<div style="width:50%;float:right">
				<h2>Display All Stats</h2>
				<p>You can dump all stats here:</p>
				<p class="submit"><input name="dumpstats" class="button-primary" value="Dump Stats" type="submit"></p>
			</div>
			<br style="clear:both">
			<?php
			if ( array_key_exists( 'ss_stop_spammers_control', $_POST ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_POST['ss_stop_spammers_control'] ) );
			}
			if ( !empty( $nonce ) && wp_verify_nonce( $nonce, 'ss_stopspam_update' ) ) {
				if ( array_key_exists( 'dumpoptions', $_POST ) ) { ?>
					<?php
					echo '<pre>';
					echo "\r\n";
					$options = ss_get_options();
					foreach ( $options as $key => $val ) {
						if ( is_array( $val ) ) {
							$val = print_r( $val, true );
						}
						echo wp_kses_post( "<strong>&bull; $key</strong> = $val\r\n" );
					}
					echo "\r\n";
					echo '</pre>';
					?>
				<?php }
			}
			?>
			<?php
			if ( array_key_exists( 'ss_stop_spammers_control', $_POST ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_POST['ss_stop_spammers_control'] ) );
			}
			if ( !empty( $nonce ) && wp_verify_nonce( $nonce, 'ss_stopspam_update' ) ) {
				if ( array_key_exists( 'dumpstats', $_POST ) ) { ?>
					<?php
					$stats = ss_get_stats();
					echo '<pre>';
					echo "\r\n";
					foreach ( $stats as $key => $val ) {
						if ( is_array( $val ) ) {
							$val = print_r( $val, true );
						}
						echo wp_kses_post( "<strong>&bull; $key</strong> = $val\r\n" );
					}
					echo "\r\n";
					echo '</pre>';
					?>
				<?php }
			}
			?>
			<p>&nbsp;</p>
		</div>
	</form>
	<div class="ss_info_box">
		<div class="mainsection">Debugging</div>
		<?php
		// if there is a log file we can display it here
		$dfile = SS_PLUGIN_DATA . 'debug.txt';
		if ( file_exists( $dfile ) ) {
			if ( array_key_exists( 'ss_stop_spammers_control', $_POST ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_POST['ss_stop_spammers_control'] ) );
			}
			if ( !empty( $nonce ) && wp_verify_nonce( $nonce, 'ss_stopspam_update' ) ) {
				if ( array_key_exists( 'killdebug', $_POST ) ) {
					$f = unlink( $dfile );
					echo '<p>File Deleted<p>';
				}
			}
		}
		if ( file_exists( $dfile ) ) {
			// we have a file - we can view it or delete it
			$nonce = '';
			$to	   = get_option( 'admin_email' );
			$f	   = file_get_contents( $dfile );
			$ff	   = wordwrap( $f, 70, "\r\n" );
		} else {
			$f = '';
		}
		if ( array_key_exists( 'ss_stop_spammers_control', $_POST ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_POST['ss_stop_spammers_control'] ) );
		}
		if ( !empty( $nonce ) && wp_verify_nonce( $nonce, 'ss_stopspam_update' ) ) {
			if ( array_key_exists( 'showdebug', $_POST ) ) {
				echo wp_kses_post( "<p><strong>Debug Output:</strong></p><pre>$f</pre><p><strong>end of file (if empty, there are no errors to display)</p></strong>" );
			}
		}
		$nonce = wp_create_nonce( 'ss_stopspam_update' );
		?>
		<div style="width:50%;float:left">
			<form method="post" action="">
				<input type="hidden" name="update_options" value="update">
				<input type="hidden" name="ss_stop_spammers_control" value="<?php echo esc_html( $nonce ); ?>">
				<p class="submit"><input class="button-primary" name="showdebug" value="Show Debug File" type="submit"></p>
			</form>
		</div>
		<div style="width:50%;float:right">
			<form method="post" action="">
				<input type="hidden" name="update_options" value="update">
				<input type="hidden" name="ss_stop_spammers_control" value="<?php echo esc_html( $nonce ); ?>">
				<p class="submit"><input class="button-primary" name="killdebug" value="Delete Debug File" type="submit"></p>
			</form>
		</div>
	<br style="clear:both">
	</div>
	<?php
	$ini  = function_exists( 'ini_get' ) ? ini_get( 'disable_functions' ) : '';
	$pinf = empty( $ini ) || !in_array( 'phpinfo', explode( ',', $ini ) );
	if ( $pinf ) { ?>
		<a href="#phpinfo" onclick="togglePhpInfo(); return false;" class="button-primary">Show PHP Info</a>
		<div id="phpinfo" class="phpinfodisplay" style="display:none">
		<?php
		ob_start();
		phpinfo();
		$phpinfo = ob_get_clean();
		preg_match( '%<style type="text/css">(.*?)</style>.*?(<body>.*</body>)%s', $phpinfo, $matches );
		if ( isset( $matches[1] ) && isset( $matches[2] ) ) {
			echo wp_kses(
				"<style type=\"text/css\">\n
					#phpinfo{max-width:100%}
					#phpinfo pre{margin:0;font-family:monospace}
					#phpinfo a:link{color:#009;text-decoration:none;background-color:#fff}
					#phpinfo a:hover{text-decoration:underline}
					#phpinfo table{border-collapse:collapse;border:0;width:100%;box-shadow:1px 2px 3px #ccc}
					#phpinfo .center{text-align:center}
					#phpinfo .center table{margin:1em auto;text-align:left}
					#phpinfo .center th{text-align:center !important}
					#phpinfo td, th{border:1px solid #666;font-size:75%;vertical-align:baseline;padding:10px}
					#phpinfo h1{font-size:150%}
					#phpinfo h2{font-size:125%}
					#phpinfo .p{text-align:left}
					#phpinfo .e{background-color:#ccf;max-width:300px;font-weight:bold}
					#phpinfo .h{background-color:#99c;font-weight:bold}
					#phpinfo .v{background-color:#ddd;max-width:300px;overflow-x:auto;word-wrap:break-word}
					#phpinfo .v i{color:#999}
					#phpinfo img{float:right;border:0}
					#phpinfo hr{width:100%;background-color:#ccc;border:0;height:1px}
				</style>\n" .
				$matches[2],
				array(
					'style' => array( 'type' => array() ),
					'div'   => array( 'class' => array(), 'id' => array(), 'style' => array() ),
					'h1'    => array( 'class' => array(), 'id' => array() ),
					'h2'    => array( 'class' => array(), 'id' => array() ),
					'h3'    => array( 'class' => array(), 'id' => array() ),
					'h4'    => array( 'class' => array(), 'id' => array() ),
					'h5'    => array( 'class' => array(), 'id' => array() ),
					'h6'    => array( 'class' => array(), 'id' => array() ),
					'p'     => array( 'class' => array(), 'id' => array() ),
					'table' => array( 'class' => array(), 'id' => array() ),
					'tr'    => array( 'class' => array(), 'id' => array() ),
					'td'    => array( 'class' => array(), 'id' => array() ),
					'th'    => array( 'class' => array(), 'id' => array() ),
					'ul'    => array( 'class' => array(), 'id' => array() ),
					'ol'    => array( 'class' => array(), 'id' => array() ),
					'li'    => array( 'class' => array(), 'id' => array() ),
					'a'     => array( 'href' => array(), 'title' => array(), 'class' => array(), 'id' => array() ),
				)
			);
		}
		?>
		</div>
		<?php
		wp_register_script( 'ss-phpinfo', '' );
		wp_enqueue_script( 'ss-phpinfo' );
		wp_add_inline_script( 'ss-phpinfo', '
			function togglePhpInfo() {
				var phpInfoDiv = document.getElementById("phpinfo");
				phpInfoDiv.style.display = (phpInfoDiv.style.display === "none" || phpInfoDiv.style.display === "") ? "block" : "none";
			}
		' );
	} ?>
	<?php
	ss_fix_post_vars();
	global $wpdb;
	global $wp_query;
	$pre	 = $wpdb->prefix;
	$runscan = false;
	$nonce   = '';
	if ( array_key_exists( 'ss_stop_spammers_control', $_POST ) ) {
		$nonce = sanitize_text_field( wp_unslash( $_POST['ss_stop_spammers_control'] ) );
	}
	if ( !empty( $nonce ) && wp_verify_nonce( $nonce, 'ss_stopspam_update' ) ) {
		if ( array_key_exists( 'update_options', $_POST ) ) {
			$runscan = true;
		}
	}
	$nonce = wp_create_nonce( 'ss_stopspam_update' );
	?>
	<div class="ss_info_box">
		<div class="mainsection">Threat Scan
			<sup class="ss_sup"><a href="https://github.com/webguyio/stop-spammers/wiki/Docs:-Diagnostics-&-Threat-Scan#threat-scan" target="_blank">?</a></sup>
		</div>
		<p>A very simple scan that looks for things out of place in the content directory as well as the database.</p>
		<form method="post" action="#scan">
			<input type="hidden" name="update_options" value="update">
			<input type="hidden" name="ss_stop_spammers_control" value="<?php echo esc_html( $nonce ); ?>">
			<p id="scan" class="submit"><input class="button-primary" value="Run Scan" type="submit"></p>
		</form>
	</div>
	<?php if ( $runscan ) { ?>
		<h2>A clean scan does not mean you are safe. Please keep regular backups and ensure your installation up-to-date!</h2>
		<hr>
		<?php
		$disp = false;
		flush();
		// lets try the posts - looking for script tags in data
		echo '<br><br>Testing Posts<br>';
		$ptab = $pre . 'posts';
		// suspicious patterns to search
		$suspicious_patterns = [
			'<script', 
			'eval(', 
			'eval (', 
			'document.write(unescape(',
			'try{window.onload',
			'setAttribute(\'src\''
		];
		// prepare the SQL query with placeholders
		$sql = "
			SELECT ID, post_author, post_title, post_name, guid, post_content, post_mime_type
			FROM $ptab 
			WHERE (
				LOWER(post_author) LIKE %s OR 
				LOWER(post_title) LIKE %s OR 
				LOWER(post_name) LIKE %s OR 
				LOWER(guid) LIKE %s OR 
				LOWER(post_content) LIKE %s OR
				LOWER(post_mime_type) LIKE %s
			)
		";
		// prepare bind parameters
		$bind_params = [];
		foreach ( $suspicious_patterns as $pattern ) {
			$bind_params = array_merge(
				$bind_params, 
				array_fill( 0, 6, '%' . $wpdb->esc_like( strtolower( $pattern ) ) . '%' )
			);
		}
		$query = $wpdb->prepare(
			$sql,
			$bind_params[0],
			$bind_params[1],
			$bind_params[2],
			$bind_params[3],
			$bind_params[4],
			$bind_params[5]
		);
		$myrows = $wpdb->get_results( $query );
		if ( $myrows ) {
			foreach ( $myrows as $myrow ) {
				$disp = true;
				$reason = '';
				// check each suspicious pattern in different fields
				foreach ( $suspicious_patterns as $pattern ) {
					if (
						stripos( $myrow->post_author, $pattern ) !== false ||
						stripos( $myrow->post_title, $pattern ) !== false ||
						stripos( $myrow->post_name, $pattern ) !== false ||
						stripos( $myrow->guid, $pattern ) !== false ||
						stripos( $myrow->post_content, $pattern ) !== false ||
						stripos( $myrow->post_mime_type, $pattern ) !== false
					) {
						$reason .= "Found: " . esc_html( $pattern ) . " ";
					}
				}
				if ( !empty( $reason ) ) {
					echo 'Found possible problems in post (<span style="color:red">' . esc_html( $reason ) . '</span>) ID: ' . esc_html( $myrow->ID ) . '<br>';
				}
			}
		} else {
			echo '<br>No suspicious patterns found in posts.<br>';
			$disp = false;
		}
		echo '<hr>';
		// comments: comment_ID: author_url, comment_agent, comment_author, comment_email
		$ptab = $pre . 'comments';
		echo '<br><br>Testing Comments<br>';
		flush();
		// suspicious patterns to search
		$suspicious_patterns = [
			'<script', 
			'eval(', 
			'eval (', 
			'document.write(unescape(',
			'try{window.onload',
			'setAttribute(\'src\'',
			'javascript:'
		];
		// prepare the SQL query with placeholders
		$sql = "
			SELECT comment_ID, comment_author_url, comment_agent, comment_author, comment_author_email, comment_content
			FROM $ptab 
			WHERE (
				LOWER(comment_author_url) LIKE %s OR 
				LOWER(comment_agent) LIKE %s OR 
				LOWER(comment_author) LIKE %s OR 
				LOWER(comment_author_email) LIKE %s OR 
				LOWER(comment_content) LIKE %s
			)
		";
		// prepare bind parameters
		$bind_params = [];
		foreach ( $suspicious_patterns as $pattern ) {
			$bind_params = array_merge(
				$bind_params, 
				array_fill( 0, 5, '%' . $wpdb->esc_like( strtolower( $pattern ) ) . '%' )
			);
		}
		$query = $wpdb->prepare(
			$sql,
			$bind_params[0],
			$bind_params[1],
			$bind_params[2],
			$bind_params[3],
			$bind_params[4]
		);
		$myrows = $wpdb->get_results( $query );
		if ( $myrows ) {
			foreach ( $myrows as $myrow ) {
				$disp = true;
				$reason = '';
				// check each suspicious pattern in different fields
				foreach ( $suspicious_patterns as $pattern ) {
					$fields_to_check = [
						'comment_author_url' => $myrow->comment_author_url,
						'comment_agent' => $myrow->comment_agent,
						'comment_author' => $myrow->comment_author,
						'comment_author_email' => $myrow->comment_author_email,
						'comment_content' => $myrow->comment_content
					];
					foreach ( $fields_to_check as $field_name => $field_value ) {
						if ( stripos( $field_value, $pattern ) !== false ) {
							$reason .= $field_name . ":" . htmlspecialchars( $pattern ) . " ";
						}
					}
				}
				if ( !empty( $reason ) ) {
					echo 'Found possible problems in comment (<span style="color:red">' . esc_html( $reason ) . '</span>) ID: ' . esc_html( $myrow->comment_ID ) . '<br>';
				}
			}
		} else {
			echo '<br>No suspicious patterns found in comments.<br>';
			$disp = false;
		}
		flush();
		echo '<hr>';
		// links: links_id: link_url, link_image, link_description, link_notes, link_rss, link_rss
		$ptab = $pre . 'links';
		echo '<br><br>Testing Links<br>';
		flush();
		// suspicious patterns to search
		$suspicious_patterns = [
			'<script', 
			'eval(', 
			'eval (', 
			'javascript:'
		];
		// prepare the SQL query with placeholders
		$sql = "
			SELECT link_ID, link_url, link_image, link_description, link_notes, link_rss
			FROM $ptab
			WHERE (
				LOWER(link_url) LIKE %s OR
				LOWER(link_image) LIKE %s OR
				LOWER(link_description) LIKE %s OR
				LOWER(link_notes) LIKE %s OR
				LOWER(link_rss) LIKE %s
			)
		";
		// prepare bind parameters
		$bind_params = [];
		foreach ( $suspicious_patterns as $pattern ) {
			$bind_params = array_merge(
				$bind_params,
				array_fill( 0, 5, '%' . $wpdb->esc_like( strtolower( $pattern ) ) . '%' )
			);
		}
		$query = $wpdb->prepare(
			$sql,
			$bind_params[0],
			$bind_params[1],
			$bind_params[2],
			$bind_params[3],
			$bind_params[4]
		);
		$myrows = $wpdb->get_results( $query );
		if ( $myrows ) {
			foreach ( $myrows as $myrow ) {
				$reason = '';
				// check each suspicious pattern in different fields
				foreach ( $suspicious_patterns as $pattern ) {
					$fields_to_check = [
						'link_url' => $myrow->link_url,
						'link_image' => $myrow->link_image,
						'link_description' => $myrow->link_description,
						'link_notes' => $myrow->link_notes,
						'link_rss' => $myrow->link_rss
					];
					foreach ( $fields_to_check as $field_name => $field_value ) {
						if ( stripos( $field_value, $pattern ) !== false ) {
							$reason .= $field_name . ":" . htmlspecialchars( $pattern ) . " ";
						}
					}
				}
				if ( !empty( $reason ) ) {
					echo 'Found possible problems in links (<span style="color:red">' . esc_html( $reason ) . '</span>) ID: ' . esc_html( $myrow->link_ID ) . '<br>';
				}
			}
		} else {
			echo '<br>No suspicious patterns found in links.<br>';
		}
		flush();
		echo '<hr>';
		// users: ID: user_login, user_nicename, user_email, user_url, display_name
		$ptab = $pre . 'users';
		echo '<br><br>Testing Users<br>';
		flush();
		// suspicious patterns to search
		$suspicious_patterns = [
			'<script', 
			'eval(', 
			'eval (', 
			'javascript:'
		];
		// prepare the SQL query with placeholders
		$sql = "
			SELECT ID, user_login, user_nicename, user_email, user_url, display_name
			FROM $ptab
			WHERE (
				LOWER( user_login ) LIKE %s OR
				LOWER( user_nicename ) LIKE %s OR
				LOWER( user_email ) LIKE %s OR
				LOWER( user_url ) LIKE %s OR
				LOWER( display_name ) LIKE %s
			)
		";
		// prepare bind parameters
		$bind_params = [];
		foreach ( $suspicious_patterns as $pattern ) {
			$bind_params = array_merge(
				$bind_params, 
				array_fill( 0, 5, '%' . $wpdb->esc_like( strtolower( $pattern ) ) . '%' )
			);
		}
		$query = $wpdb->prepare(
			$sql,
			$bind_params[0],
			$bind_params[1],
			$bind_params[2],
			$bind_params[3],
			$bind_params[4]
		);
		$myrows = $wpdb->get_results( $query );
		if ( $myrows ) {
			foreach ( $myrows as $myrow ) {
				$reason = '';
				// check each suspicious pattern in different fields
				foreach ( $suspicious_patterns as $pattern ) {
					$fields_to_check = [
						'user_login' => $myrow->user_login,
						'user_nicename' => $myrow->user_nicename,
						'user_email' => $myrow->user_email,
						'user_url' => $myrow->user_url,
						'display_name' => $myrow->display_name
					];
					foreach ( $fields_to_check as $field_name => $field_value ) {
						if ( stripos( $field_value, $pattern ) !== false ) {
							$reason .= $field_name . ":" . htmlspecialchars( $pattern ) . " ";
						}
					}
				}
				if ( !empty( $reason ) ) {
					echo 'Found possible problems in Users (<span style="color:red">' . esc_html( $reason ) . '</span>) ID: ' . esc_html( $myrow->ID ) . '<br>';
				}
			}
		} else {
			echo '<br>No suspicious patterns found in users.<br>';
		}
		flush();
		echo '<hr>';
		// options: option_id option_value, option_name
		// I may have to update this as new websites show up
		$ptab = $pre . 'options';
		echo '<br><br>Testing Options Table for HTML<br>';
		flush();
		$badguys = array(
			'eval('							     => 'eval function found',
			'eval ('							 => 'eval function found',
			'networkads'						 => 'unexpected network ads reference',
			'document.write(unescape('		     => 'javascript document write unescape',
			'try{window.onload'				     => 'javascript onload event',
			'escape(document['				     => 'javascript checking document array',
			'escape(navigator['				     => 'javascript checking navigator',
			'document.write(string.fromcharcode' => 'obsfucated javascript write',
			'(base64' . '_decode'				 => 'base64 decode to hide code',
			'(gz' . 'inflate'					 => 'gzip inflate often used to hide code',
			'UA-27917097-1'					     => 'Bogus Google Analytics code',
			'w.wpquery.o'						 => 'Malicious jquery in bootleg plugin or theme',
			'<scr\\\'+'						     => 'Obfuscated script tag, usually in bootleg plugin or theme'
		);
		// prepare the SQL query with placeholders
		$sql = "SELECT option_id, option_value, option_name FROM $ptab WHERE ";
		$conditions = [];
		$bind_params = [];
		foreach ( $badguys as $baddie => $reas ) {
			$conditions[] = "LOWER( option_value ) LIKE %s";
			$bind_params[] = '%' . $wpdb->esc_like( strtolower( $baddie ) ) . '%';
		}
		$sql .= implode( ' OR ', $conditions );
		$query = $wpdb->prepare( $sql, $bind_params );
		$myrows = $wpdb->get_results( $query );
		if ( $myrows ) {
			foreach ( $myrows as $myrow ) {
				// skip transient feeds
				if ( strpos( $myrow->option_name, '_transient_feed_' ) !== false ) {
					continue;
				}
				$id     = $myrow->option_id;
				$name   = $myrow->option_name;
				$line   = htmlentities( $myrow->option_value );
				$line   = strtolower( $line );
				$reason = '';
				foreach ( $badguys as $baddie => $reas ) {
					if ( strpos( $line, strtolower( $baddie ) ) !== false ) {
						$line = ss_make_red( $baddie, $line );
						$reason .= $reas . ' ';
					}
				}
				if ( !empty( $reason ) ) {
					echo '<strong>Found possible problems in Option ' . esc_html( $name ) . ' (<span style="color:red">' . esc_html( $reason ) . '</span>)</strong> option_id: ' . esc_html( $myrow->option_id ) . ', value: ' . esc_html( $line ) . '<br><br>';
				}
			}
		} else {
			echo '<br>No suspicious patterns found in options.<br>';
		}
		echo '<hr>';
		echo '<h2>Scanning Themes and Plugins for eval</h2>';
		flush();
		if ( ss_scan_for_eval() ) {
			$disp = true;
		}
		if ( $disp ) { ?>
			<h2>Possible Problems Found!</h2>
			<p>These are warnings only. Some content and plugins might not be
				malicious, but still contain one or more
				of these indicators. Please investigate all indications of
				problems. The plugin may err on the side of
				caution.</p>
			<p>'Although there are legitimate reasons for using the eval
				function, and JavaScript uses it frequently,
				finding eval in PHP code is in the very least bad practice, and
				the worst is used to hide malicious
				code. If eval() comes up in a scan, try to get rid of it.</p>
			<p>Your code could contain "eval", or "document.write(unescape(" or
				"try{window.onload" or
				setAttribute("src". These are markers for problems such as SQL
				injection or cross-browser JavaScript.
				&lt;script&gt; tags should occur in your posts, if you added
				them, but should not be found anywhere
				else, except options. Options often have scripts for displaying
				Facebook, Twitter, etc. Be careful,
				though, if one appears in an option. Most of the time it is OK,
				but make sure.</p>
		<?php } else { ?>
			<h2>No Problems Found</h2>
			<p>It appears that there are no eval or suspicious JavaScript
				functions in the code in your wp-content
				directory. That does not mean that you are safe, only that a
				threat may be well-hidden.</p>
		<?php }
		flush();
	} // end if runscan
	function ss_scan_for_eval() {
		// scan content completely
		// WP_CONTENT_DIR is supposed to have the content dir
		$phparray = array();
		// use get_home_path()
		// $phparray=ss_scan_for_eval_recurse(WP_CONTENT_DIR.'/..',$phparray);
		$phparray = ss_scan_for_eval_recurse( realpath( get_home_path() ), $phparray );
		// phparray should have a list of all of the PHP files
		$disp = false;
		echo 'Files: <ol>';
		for ( $j = 0; $j < count( $phparray ); $j ++ ) {
		// ignore my work on this subject
			if ( strpos( $phparray[$j], 'threat_scan' ) === false && strpos( $phparray[$j], 'threat-scan' ) === false ) {
				$ansa = ss_look_in_file( $phparray[$j] );
				if ( count( $ansa ) > 0 ) {
					$disp = true;
					// echo "Think we got something<br>";
					echo '<li>' . esc_html( $phparray[$j] ) . ' <br> ';
					for ( $k = 0; $k < count( $ansa ); $k ++ ) {
						echo wp_kses_post( $ansa[$k] ) . ' <br>';
					}
					echo '</li>';
				}
			}
		}
		echo '</ol>';
		return $disp;
	} // end of function
	// recursive walk of directory structure.
	function ss_scan_for_eval_recurse( $dir, $phparray ) {
		if ( !@is_dir( $dir ) ) {
			return $phparray;
		}
		// if (substr($dir,0,1)='.') return $phparray;
		$dh = null;
		// can't protect this - turn off the error capture for a moment.
		sfs_errorsonoff( 'off' );
		try {
			$dh = @opendir( $dir );
		} catch ( Exception $e ) {
			sfs_errorsonoff();
			return $phparray;
		}
		sfs_errorsonoff();
		if ( $dh !== null && $dh !== false ) {
			while ( ( $file = readdir( $dh ) ) !== false ) {
				if ( @is_dir( $dir . '/' . $file ) ) {
					if ( $file != '.' && $file != '..' && $file != ':'
						 && strpos( '/', $file ) === false
					) { // that last one does some symbolics?
						$phparray = ss_scan_for_eval_recurse( $dir . '/' . $file, $phparray );
					}
				} else if ( strpos( $file, '.php' ) > 0 ) {
					$phparray[count( $phparray )] = $dir . '/' . $file;
				} else {
				// echo "can't find .php in $file <br>";
				}
			}
			closedir( $dh );
		}
		return $phparray;
	}
	function ss_look_in_file( $file ) {
		if ( !file_exists( $file ) ) {
			return false;
		}
		// don't look in this plugin because it finds too much stuff
		// only look for .php files - no more javascript
		if ( strpos( $file, '.php' ) === false ) {
			return false;
		}
		// initialize the WordPress filesystem
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}
		// read the file contents
		$file_contents = $wp_filesystem->get_contents( $file );
		if ( $file_contents === false ) {
			return array();
		}
		$ansa	 = array();
		$n	     = 0;
		$idx	 = 0;
		$badguys = array(
			'eval(',
			'eval (',
			'document.write(unescape(',
			'try{window.onload',
			'escape(document[',
			'escape(navigator[',
			"setAttribute('src'",
			'document.write(string.fromcharcode',
			'base64' . '_decode',
			'gzun' . 'compress',
			'gz' . 'inflate',
			'if(!isset($GLOBALS[' . "\\'\\a\\e\\0",
			'passssword',
			'Bruteforce protection',
			'w.wpquery.o',
			"<scr'+"
		);
		// split the file contents into lines
		$lines = explode( "\n", $file_contents );
		foreach ( $lines as $line ) {
			$line = htmlentities( $line );
			$n ++;
			foreach ( $badguys as $baddie ) {
				if ( !( strpos( $line, $baddie ) === false ) ) {
					// bad boy
					if ( ss_ok_list( $file, $n ) ) {
						$line		  = ss_make_red( $baddie, $line );
						$ansa[$idx] = $n . ': ' . $line;
						$idx ++;
					}
				}
			}
			// search line for $xxxxx() type things
			$m	    = 0;
			$f	    = false;
			$vchars = '!@#$%^&*),.;:\"[]{}?/+=_- \t\\|~`<>' . "'"; // not part of variable names
			while ( $m < strlen( $line ) - 2 ) {
				$m = strpos( $line, '$', $m );
				if ( $m === false ) {
					break;
				}
				if ( substr( $line, $m, 7 ) != '$class(' ) { // used often and correctly
					$mi = $m;
					$mi ++;
					for ( $mm = $mi; ( $mm < $mi + 8 && $mm < strlen( $line ) ); $mm ++ ) {
						$c = substr( $line, $mm, 1 );
						if ( $c == '(' && $mm > $mi ) { // need at least a character so as not to kill jQuery
							$f = true;
							break;
						}
						if ( strpos( $vchars, $c ) !== false ) {
							break;
						}
					}
				}
				if ( $f ) {
					break;
				}
				$m ++;
			}
			if ( $f ) {
				if ( ss_ok_list( $file, $n ) ) {
					$ll		      = substr( $line, $m, 7 );
					$line		  = ss_make_red( $ll, $line );
					$ansa[$idx] = $n . ': ' . $line;
					$idx ++;
				}
			}
		}
		return $ansa;
	}
	function ss_make_red( $needle, $haystack ) {
		// turns error red
		$j = strpos( $haystack, $needle );
		$s = substr_replace( $haystack, '</span>', $j + strlen( $needle ), 0 );
		$s = substr_replace( $s, '<span style="color:red">', $j, 0 );
		return $s;
	}
	function ss_ok_list( $file, $line ) {
		// more advanced excluder file=>array(start,end,start,end,start,end
		// start and end are loose to allow for varuous versions - hope that they don't hide some bad code
		$exclude = array(
			'class-pclzip.php'								   => array(
				3700,
				4300
			),
			'wp-admin/includes/file.php'					   => array(
				450,
				550
			),
			'wp-admin/press-this.php'						   => array(
				200,
				250,
				400,
				450
			),
			'jetpack/class.jetpack.php'						   => array(
				5000,
				5100
			),
			'jetpack/locales.php'							   => array(
				25,
				75
			),
			'custom-css/preprocessors/lessc.inc.php'		   => array(
				25,
				75,
				1500,
				1600
			),
			'preprocessors/scss.inc.php'					   => array(
				800,
				900,
				1800,
				1900
			),
			'ss_challenge.php'								   => array(
				0,
				300
			),
			'modules/chkexploits.php'						   => array(
				10,
				30
			),
			'wp-includes/class-http.php'					   => array(
				2000,
				2300
			),
			'class-IXR.php'									   => array(
				300,
				350
			),
			'all-in-one-seo-pack/JSON.php'					   => array(
				10,
				30
			),
			'all-in-one-seo-pack/OAuth.php'					   => array(
				240,
				300
			),
			'all-in-one-seo-pack/aioseop_sitemap.php'		   => array(
				500,
				600
			),
			'wp-includes/class-json.php'					   => array(
				10,
				30
			),
			'p-includes/class-smtp.php'						   => array(
				300,
				400
			),
			'wp-includes/class-snoopy.php'					   => array(
				650,
				700
			),
			'wp-includes/class-feed.php'					   => array(
				100,
				150
			),
			'wp-includes/class-wp-customize-widgets.php'	   => array(
				1100,
				1250
			),
			'wp-includes/compat.php'						   => array(
				40,
				60
			),
			'/jsonwrapper/JSON/JSON.php'					   => array(
				10,
				30
			),
			'wp-includes/functions.php'						   => array(
				200,
				250
			),
			'wp-includes/ID3/module.audio-video.quicktime.php' => array(
				450,
				550
			),
			'wp-includes/ID3/module.audio.ogg.php'			   => array(
				550,
				650
			),
			'wp-includes/ID3/module.tag.id3v2.php'			   => array(
				550,
				650
			),
			'wp-includes/pluggable.php'						   => array(
				1750,
				1850
			),
			'wp-includes/session.php'						   => array(
				25,
				75
			),
			'wp-includes/SimplePie/File.php'				   => array(
				200,
				300
			),
			'wp-includes/SimplePie/gzdecode.php'			   => array(
				300,
				350
			),
			'wp-includes/SimplePie/Sanitize.php'			   => array(
				225,
				275,
				300,
				350
			),
			'stop-spammer-registrations-new.php'			   => array(
				250,
				400
			)
		);
		foreach ( $exclude as $f => $ln ) {
			if ( stripos( $file, $f ) !== false ) {
				// found a file
				for ( $j = 0; $j < count( $ln ) / 2; $j ++ ) {
					$t1 = $ln[$j * 2];
					$t2 = $ln[( $j * 2 ) + 1];
					// echo "checking $file, $f for $line and '$ln'<br>";
					if ( $line >= $t1 && $line <= $t2 ) {
						return false;
					}
				}
			}
		}
		// if ( strpos( $file, 'stop-spammers' ) !== false ) return false;
		return true;
	}
	?>
</div>