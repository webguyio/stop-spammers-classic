=== Stop Spammers Classic ===

Contributors: bhadaway
Donate link: https://damspam.com/donate
Tags: spam, security, anti-spam, spam protection, no spam
Tested up to: 6.8
Requires at least: 3.0
Requires PHP: 5.0
Stable tag: 2025
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl.html

A simplified, restored, and preserved version of the original Stop Spammers plugin.

== Description ==

A simplified, restored, and preserved version of the original Stop Spammers plugin.

Development for Stop Spammers has slowed down. I recommend switching to the new fork [Dam Spam](https://damspam.com/).

[Why, what happened?](https://github.com/webguyio/dam-spam/issues/8)

However, rest-assured that if you can't or won't migrate to Dam Spam, I'll still continue making sure that Stop Spammers is stable, safe, and supported.

[Get Support](https://github.com/webguyio/dam-spam/issues)

== Installation ==

Go to *Plugins > Add New* from your WP admin menu, search for Stop Spammers, install, and activate.

OR

1. Download the plugin and unzip it.
2. Upload the plugin folder to your wp-content/**plugins** folder.
3. Activate the plugin from the plugins page in the admin.

== Frequently Asked Questions ==

= Can I use Stop Spammers with Cloudflare? =

Yes. But, you may need to restore visitor IPs: [https://support.cloudflare.com/hc/sections/200805497-Restoring-Visitor-IPs](https://support.cloudflare.com/hc/sections/200805497-Restoring-Visitor-IPs).

= Can I use Stop Spammers with WooCommerce (and other ecommerce plugins)? =

Yes. But, in some configurations, you may need to go to Stop Spammers > Protection Options > Toggle on the option for "Only Use the Plugin for Standard WordPress Forms" > Save if you're running into any issues.

= Can I use Stop Spammers with Akismet? =

Yes. Stop Spammers can even check Akismet for an extra layer of protection.

= Can I use Stop Spammers with Jetpack? =

Yes and no. You can use all Jetpack features except for Jetpack Protect, as it conflicts with Stop Spammers.

= Can I use Stop Spammers with Wordfence (and other spam and security plugins)? =

Yes. The two can compliment each other. However, if you have only a small amount of hosting resources (mainly memory) or aren't even allowing registration on your website, using both might be overkill.

= Why is 2FA failing? =

Toggle off the "Check Credentials on All Login Attempts" option and try again.

= Is Stop Spammers GDPR-compliant? =

Yes. See: [https://law.stackexchange.com/questions/28603/how-to-satisfy-gdprs-consent-requirement-for-ip-logging](https://law.stackexchange.com/questions/28603/how-to-satisfy-gdprs-consent-requirement-for-ip-logging). Stop Spammers does not collect any data for any other purpose (like marketing or tracking). It is purely for legitimate security purposes only. Additionally, if any of your users ever requested it, all data can be deleted.

= What third-party services are used and what data is sent to them? =

There are several optional services you may use that involve sending data to third parties including: [Google reCAPTCHA](https://policies.google.com/privacy), [hCaptcha](https://www.hcaptcha.com/privacy), [2Captcha](https://2captcha.com/privacy-policy), [Spamhaus](https://www.spamhaus.org/privacy-notice/), [Stop Forum Spam](https://www.stopforumspam.com/privacy), [Project Honeypot](https://www.projecthoneypot.org/privacy_policy.php), and [BotScout](https://botscout.com/w3c/privacy.htm). You may wish to read each services' privacy policy to see if you're comfortable using them, but generally speaking, whenever someone for example tries to use a contact form on your website, their IP address, name, and email may be sent to these services to check against spam blocklists.

== Changelog ==

= 2025 =
* Recovered version, which has been cleaned up

= 2024.7 =
* Previous version, which I still consider safe to use

Credits: Created and maintained by Keith P. Graham (@kpgraham) from 2010-2017. Adopted and maintained by Web Guy (@bhadaway) from 2017-2025.