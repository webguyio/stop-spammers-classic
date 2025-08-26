<?php

if ( !defined( 'ABSPATH' ) ) {
	http_response_code( 404 );
	die();
}

if ( !current_user_can( 'manage_options' ) ) {
	die( 'Access Blocked' );
}

ss_fix_post_vars();
$now	 = gmdate( 'Y/m/d H:i:s', time() + ( get_option( 'gmt_offset' ) * 3600 ) );
$options = ss_get_options();
extract( $options );
// $ip = ss_get_ip();
$nonce   = '';
$msg	 = '';

if ( array_key_exists( 'ss_stop_spammers_control', $_POST ) ) {
	$nonce = sanitize_text_field( wp_unslash( $_POST['ss_stop_spammers_control'] ) );
}

if ( wp_verify_nonce( $nonce, 'ss_stopspam_update' ) ) {
	if ( array_key_exists( 'action', $_POST ) ) {
		$optionlist = array( 'redir', 'notify', 'emailrequest', 'wlreq' );
		foreach ( $optionlist as $check ) {
			$v = 'N';
			if ( array_key_exists( $check, $_POST ) ) {
				$v = sanitize_text_field( wp_unslash( $_POST[$check] ) );
				if ( $v != 'Y' ) {
					$v = 'N';
				}
			}
			$options[$check] = $v;
		}
		// other options
		if ( array_key_exists( 'redirurl', $_POST ) ) {
			$redirurl			 = sanitize_url( trim( wp_unslash( $_POST['redirurl'] ) ) );
			$options['redirurl'] = esc_url( $redirurl );
		}
		if ( array_key_exists( 'wlreqmail', $_POST ) ) {
			$wlreqmail			  = sanitize_email( trim( wp_unslash( $_POST['wlreqmail'] ) ) );
			$options['wlreqmail'] = esc_html( $wlreqmail );
		}
		if ( array_key_exists( 'rejectmessage', $_POST ) ) {
			$rejectmessage			  = sanitize_textarea_field( trim( wp_unslash( $_POST['rejectmessage'] ) ) );
			$options['rejectmessage'] = wp_kses_post( $rejectmessage );
		}
		if ( array_key_exists( 'chkcaptcha', $_POST ) ) {
			$chkcaptcha			   = sanitize_text_field( trim( wp_unslash( $_POST['chkcaptcha'] ) ) );
			$options['chkcaptcha'] = esc_html( $chkcaptcha );
		}
		if ( array_key_exists( 'form_captcha_login', $_POST ) and ( $chkcaptcha == 'G' or $chkcaptcha == 'H' or $chkcaptcha == 'S' ) ) {
			$form_captcha_login			   = sanitize_text_field( trim( wp_unslash( $_POST['form_captcha_login'] ) ) );
			$options['form_captcha_login'] = esc_html( $form_captcha_login );
		} else {
			$options['form_captcha_login'] = 'N';
		}
		if ( array_key_exists( 'form_captcha_registration', $_POST ) and ( $chkcaptcha == 'G' or $chkcaptcha == 'H' or $chkcaptcha == 'S' ) ) {
			$form_captcha_registration					  = sanitize_text_field( trim( wp_unslash( $_POST['form_captcha_registration'] ) ) );
			$options['form_captcha_registration'] = esc_html( $form_captcha_registration );
		} else {
			$options['form_captcha_registration'] = 'N';
		}
		if ( array_key_exists( 'form_captcha_comment', $_POST ) and ( $chkcaptcha == 'G' or $chkcaptcha == 'H' or $chkcaptcha == 'S' ) ) {
			$form_captcha_comment				 = sanitize_text_field( trim( wp_unslash( $_POST['form_captcha_comment'] ) ) );
			$options['form_captcha_comment'] = esc_html( $form_captcha_comment );
		} else {
			$options['form_captcha_comment'] = 'N';
		}
		// added the API key stiff for Captchas
		if ( array_key_exists( 'recaptchaapisecret', $_POST ) ) {
			$recaptchaapisecret			   = sanitize_text_field( wp_unslash( $_POST['recaptchaapisecret'] ) );
			$options['recaptchaapisecret'] = esc_html( $recaptchaapisecret );
		}
		if ( array_key_exists( 'recaptchaapisite', $_POST ) ) {
			$recaptchaapisite			 = sanitize_text_field( wp_unslash( $_POST['recaptchaapisite'] ) );
			$options['recaptchaapisite'] = esc_html( $recaptchaapisite );
		}
		if ( array_key_exists( 'hcaptchaapisecret', $_POST ) ) {
			$hcaptchaapisecret			  = sanitize_text_field( wp_unslash( $_POST['hcaptchaapisecret'] ) );
			$options['hcaptchaapisecret'] = esc_html( $hcaptchaapisecret );
		}
		if ( array_key_exists( 'hcaptchaapisite', $_POST ) ) {
			$hcaptchaapisite			= sanitize_text_field( wp_unslash( $_POST['hcaptchaapisite'] ) );
			$options['hcaptchaapisite'] = esc_html( $hcaptchaapisite );
		}
		if ( array_key_exists( 'solvmediaapivchallenge', $_POST ) ) {
			$solvmediaapivchallenge			   = sanitize_text_field( wp_unslash( $_POST['solvmediaapivchallenge'] ) );
			$options['solvmediaapivchallenge'] = esc_html( $solvmediaapivchallenge );
		}
		if ( array_key_exists( 'solvmediaapiverify', $_POST ) ) {
			$solvmediaapiverify			   = sanitize_text_field( wp_unslash( $_POST['solvmediaapiverify'] ) );
			$options['solvmediaapiverify'] = esc_html( $solvmediaapiverify );
		}
		// validate the chkcaptcha variable
		if ( $chkcaptcha == 'G' && ( $recaptchaapisecret == '' || $recaptchaapisite == '' ) ) {
			$chkcaptcha			   = 'Y';
			$options['chkcaptcha'] = esc_html( $chkcaptcha );
			$msg				   = esc_html( 'You cannot use Google reCAPTCHA unless you have entered an API key.' );
		}
		if ( $chkcaptcha == 'H' && ( $hcaptchaapisecret == '' || $hcaptchaapisite == '' ) ) {
			$chkcaptcha			   = 'Y';
			$options['chkcaptcha'] = esc_html( $chkcaptcha );
			$msg				   = esc_html( 'You cannot use HCAPTCHA unless you have entered an API key.' );
		}
		if ( $chkcaptcha == 'S' && ( $solvmediaapivchallenge == '' || $solvmediaapiverify == '' ) ) {
			$chkcaptcha			   = 'Y';
			$options['chkcaptcha'] = esc_html( $chkcaptcha );
			$msg				   = esc_html( 'You cannot use Solve Media CAPTCHA unless you have entered an API key.' );
		}
		ss_set_options( $options );
		extract( $options ); // extract again to get the new options
	}
	$update = '<div class="notice notice-success is-dismissible"><p>' . 'Options Updated' . '</p></div>';
 }

$nonce = wp_create_nonce( 'ss_stopspam_update' );

?>

<div id="ss-plugin" class="wrap">
	<h1 class="ss_head"><img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) . 'images/stop-spammers-icon.png' ); ?>" class="ss_icon">Challenge & Block</h1>
	<?php if ( !empty( $update ) ) {
		echo wp_kses_post( "$update" );
	} ?>
	<?php if ( !empty( $msg ) ) {
		echo '<span style="color:red;font-size:1.2em">' . wp_kses_post( $msg ) . '</span>';
	} ?>
	<form method="post" action="">
		<input type="hidden" name="ss_stop_spammers_control" value="<?php echo esc_html( $nonce ); ?>">
		<input type="hidden" name="action" value="update challenge">
		<br>
		<div class="mainsection">Access Blocked Message
			<sup class="ss_sup"><a href="https://github.com/webguyio/stop-spammers/wiki/Docs:-Challenge-&-Block#access-blocked-message" target="_blank">?</a></sup>
		</div>
		<textarea id="rejectmessage" name="rejectmessage" cols="40" rows="5"><?php echo wp_kses_post( $rejectmessage ); ?></textarea>
		<br>
		<div class="mainsection">Routing and Notifications
			<sup class="ss_sup"><a href="https://github.com/webguyio/stop-spammers/wiki/Docs:-Challenge-&-Block#send-visitor-to-another-web-page" target="_blank">?</a></sup>
		</div>
		<div class="checkbox switcher">
			<label id="ss_subhead" for="redir">
				<input class="ss_toggle" type="checkbox" id="redir" name="redir" value="Y" onclick="ss_show_option()" <?php if ( $redir == 'Y' ) { echo 'checked="checked"'; } ?>><span><small></small></span>
		  		<small><span style="font-size:16px!important">Send Visitor to Another Web Page</span></small>
			</label>
		</div>
		<br>
		<span id="ss_show_option" style="margin-bottom:15px;display:none">Redirect URL:
		<input size="77" name="redirurl" type="text" placeholder="e.g. https://example.com/privacy-policy/" value="<?php echo esc_url( $redirurl ); ?>"></span>
		<script>
		function ss_show_option() {
			var checkBox = document.getElementById("redir");
			var text = document.getElementById("ss_show_option");
			if (checkBox.checked == true) {
				text.style.display = "block";
			} else {
				text.style.display = "none";
			}
		}
		ss_show_option();
		</script>
		<div class="checkbox switcher">
			<label id="ss_subhead" for="wlreq">
				<input class="ss_toggle" type="checkbox" id="wlreq" name="wlreq" value="Y" <?php if ( $wlreq == 'Y' ) { echo 'checked="checked"'; } ?>><span><small></small></span>
				<small><span style="font-size:16px!important">Blocked users see the Allow Request form</span></small>
			</label>
		</div>
		<br>
		<div class="checkbox switcher">
			<label id="ss_subhead" for="notify">
				<input class="ss_toggle" type="checkbox" id="notify" name="notify" value="Y" onclick="ss_show_notify()" <?php if ( $notify == 'Y' ) { echo 'checked="checked"'; } ?>><span><small></small></span>
		  		<small><span style="font-size:16px!important">Notify Web Admin when a user requests to be added to the Allow List</span></small>
			</label>
		</div>
		<br>
		<span id="ss_show_notify" style="margin-bottom:15px;display:none">(Optional) Specify where email requests are sent:
		<input id="ssinput" size="48" name="wlreqmail" type="text" value="<?php echo esc_attr( $wlreqmail ); ?>"></span>
		<script>
		function ss_show_notify() {
			var checkBox = document.getElementById("notify");
			var text = document.getElementById("ss_show_notify");
			if (checkBox.checked == true){
				text.style.display = "block";
			} else {
				text.style.display = "none";
			}
		}
		ss_show_notify();
		</script>
		<div class="checkbox switcher">
			<label id="ss_subhead" for="emailrequest">
				<input class="ss_toggle" type="checkbox" id="emailrequest" name="emailrequest" value="Y" <?php if ( $emailrequest == 'Y' ) { echo 'checked="checked"'; } ?>><span><small></small></span>
				<small><span style="font-size:16px!important">Notify Requester when a Web Admin has approved their request to be added to the Allow List</span></small>
			</label>
		</div>
		<br>
		<div class="mainsection">CAPTCHA
			<sup class="ss_sup"><a href="https://github.com/webguyio/stop-spammers/wiki/Docs:-Challenge-&-Block#captcha" target="_blank">?</a></sup>
		</div>
		<p>Second Chance CAPTCHA Challenge</p>
		<div>
			<?php
			if ( !empty( $msg ) ) {
				echo '<span style="color:red;font-size:1.2em">' . wp_kses_post( $msg ) . '</span>';
			}
			?>
		</div>
		<div class="checkbox switcher">
			<label id="ss_subhead" for="chkcaptcha1">
				<input class="ss_toggle" type="radio" id="chkcaptcha1" name="chkcaptcha" value="N" <?php if ( $chkcaptcha == 'N' ) { echo 'checked="checked"'; } ?>><span><small></small></span>
		  		<small><span style="font-size:16px!important">No CAPTCHA (default)</span></small>
			</label>
		</div>
		<br>
		<div class="checkbox switcher">
			<label id="ss_subhead" for="chkcaptcha2">
				<input class="ss_toggle" type="radio" id="chkcaptcha2" name="chkcaptcha" value="G" <?php if ( $chkcaptcha == 'G' ) { echo 'checked="checked"'; } ?>><span><small></small></span>
		  		<small><span style="font-size:16px!important">Google reCAPTCHA</span></small>
			</label>
		</div>
		<br>
		<div class="checkbox switcher">
			<label id="ss_subhead" for="chkcaptcha3">
				<input class="ss_toggle" type="radio" id="chkcaptcha3" name="chkcaptcha" value="H" <?php if ( $chkcaptcha == 'H' ) { echo 'checked="checked"'; } ?>><span><small></small></span>
		  		<small><span style="font-size:16px!important">hCaptcha</span></small>
			</label>
		</div>
		<br>
		<div class="checkbox switcher">
			<label id="ss_subhead" for="chkcaptcha4">
				<input class="ss_toggle" type="radio" id="chkcaptcha4" name="chkcaptcha" value="S" <?php if ( $chkcaptcha == 'S' ) { echo 'checked="checked"'; } ?>><span><small></small></span>
		  		<small><span style="font-size:16px!important">Solve Media CAPTCHA</span></small>
			</label>
		</div>
		<br>
		<div class="checkbox switcher">
			<label id="ss_subhead" for="chkcaptcha5">
				<input class="ss_toggle" type="radio" id="chkcaptcha5" name="chkcaptcha" value="A" <?php if ( $chkcaptcha == 'A' ) { echo 'checked="checked"'; } ?>><span><small></small></span>
		  		<small><span style="font-size:16px!important">Arithmetic Question</span></small>
			</label>
		</div>
		<div>
			<p>To use either the Solve Media, Google reCAPTCHA, or hCaptcha, you will need an API key.</p>
		</div>
		<p>CAPTCHA for Forms (works with reCAPTCHA, hCaptcha, and Solve Media CAPTCHA)</p>
		<div class="checkbox switcher">
			<label id="ss_subhead" for="form_captcha_login">
				<input class="ss_toggle" type="checkbox" id="form_captcha_login" name="form_captcha_login" value="Y" <?php if ( isset( $form_captcha_login ) and $form_captcha_login == 'Y' ) { echo 'checked="checked"'; } ?>><span><small></small></span>
		  		<small><span style="font-size:16px!important">Login</span></small>
			</label>
		</div>
		<br>
		<div class="checkbox switcher">
			<label id="ss_subhead" for="form_captcha_registration">
				<input class="ss_toggle" type="checkbox" id="form_captcha_registration" name="form_captcha_registration" value="Y" <?php if ( isset( $form_captcha_registration ) and $form_captcha_registration == 'Y' ) { echo 'checked="checked"'; } ?>><span><small></small></span>
		  		<small><span style="font-size:16px!important">Registration</span></small>
			</label>
		</div>
		<br>
		<div class="checkbox switcher">
			<label id="ss_subhead" for="form_captcha_comment">
				<input class="ss_toggle" type="checkbox" id="form_captcha_comment" name="form_captcha_comment" value="Y" <?php if ( isset( $form_captcha_comment ) and $form_captcha_comment == 'Y' ) { echo 'checked="checked"'; } ?>><span><small></small></span>
		  		<small><span style="font-size:16px!important">Comment</span></small>
			</label>
		</div>
		<br>
		<br>
		<div>
			<small><span style="font-size:16px!important">Google reCAPTCHA v2 API Key</span></small><br>
			<input size="64" name="recaptchaapisite" type="text" placeholder="Site Key" value="<?php echo esc_attr( $recaptchaapisite ); ?>">
			<br>
			<input size="64" name="recaptchaapisecret" type="text" placeholder="Secret Key" value="<?php echo esc_attr( $recaptchaapisecret ); ?>">
			<br>
			<?php if ( !empty( $recaptchaapisite ) ) { ?>
				<?php wp_enqueue_script( 'ss-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), '1', true, array( 'async' => true, 'defer' => true ) ); ?>
				<div class="g-recaptcha" data-sitekey="<?php echo esc_attr( $recaptchaapisite ); ?>"></div>
			<?php } ?>
			<br>
			<small><span style="font-size:16px!important">hCaptcha API Key</span></small><br>
			<input size="64" name="hcaptchaapisite" type="text" placeholder="Site Key" value="<?php echo esc_attr( $hcaptchaapisite ); ?>">
			<br>
			<input size="64" name="hcaptchaapisecret" type="text" placeholder="Secret Key" value="<?php echo esc_attr( $hcaptchaapisecret ); ?>">
			<br>
			<?php if ( !empty( $hcaptchaapisite ) ) { ?>
				<?php wp_enqueue_script( 'ss-hcaptcha', 'https://hcaptcha.com/1/api.js', array(), '1', true, array( 'async' => true, 'defer' => true ) ); ?>
				<div class="h-captcha" data-sitekey="<?php echo esc_attr( $hcaptchaapisite ); ?>"></div>
			<?php } ?>
			<br>
			<small><span style="font-size:16px!important">Solve Media CAPTCHA API Key</span></small><br>
			<input size="64" name="solvmediaapivchallenge" type="text" placeholder="Challenge Key" value="<?php echo esc_attr( $solvmediaapivchallenge ); ?>">
			<br>
			<input size="64" name="solvmediaapiverify" type="text" placeholder="Verification Key" value="<?php echo esc_attr( $solvmediaapiverify ); ?>">
			<br>
			<?php if ( !empty( $solvmediaapivchallenge ) ) { ?>
				<?php wp_enqueue_script( 'ss-solvemedia', 'https://api-secure.solvemedia.com/papi/challenge.script?k=' . $solvmediaapivchallenge, array(), '1', true, array( 'async' => true, 'defer' => true ) ); ?>
			<?php } ?>
		</div>
		<br>
		<br>
		<p class="submit"><input class="button-primary" value="Save Changes" type="submit"></p>
	</form>
</div>
