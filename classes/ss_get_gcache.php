<?php

if ( !defined( 'ABSPATH' ) ) {
	status_header( 404 );
	exit;
}

class ss_get_gcache {
	public function process( $ip, &$stats = array(), &$options = array(), &$post = array() ) {
		// gets the innerhtml for cache - same as get gcache except for names
		$goodips   = $stats['goodips'];
		$cachedel  = 'delete_gcache';
		$container = 'goodips';
		$trash	   = SS_PLUGIN_URL . 'images/trash.png';
		$tdown	   = SS_PLUGIN_URL . 'images/tdown.png';
		$tup	   = SS_PLUGIN_URL . 'images/tup.png';
		$whois	   = SS_PLUGIN_URL . 'images/whois.png';
		$stophand  = SS_PLUGIN_URL . 'images/stop.png';
		$search	   = SS_PLUGIN_URL . 'images/search.png';
		$ajaxurl   = admin_url( 'admin-ajax.php' );
		$show	   = '';
		foreach ( $goodips as $key => $value ) {
			$who	 = "<a title=\"Look Up WHOIS\" target=\"_stopspam\" href=\"https://whois.domaintools.com/$key\"><img src=\"$whois\" class=\"icon-action\"></a>";
			$show   .= "<a href=\"https://www.stopforumspam.com/search?q=$key\" target=\"_stopspam\">$key: $value</a>";
			// try AJAX on the delete from bad cache
			$onclick = "onclick=\"sfs_ajax_process($key,$container,$cachedel,$ajaxurl);return false;\"";
			$show   .= " <a href=\"\" $onclick title=\"Delete $key from Cache\" alt=\"Delete $key from Cache\" ><img src=\"$trash\" class=\"icon-action\"></a>";
			$onclick = "onclick=\"sfs_ajax_process($key,$container,add_black,$ajaxurl);return false;\"";
			$show   .= " <a href=\"\" $onclick title=\"Add to $key Block List\" alt=\"Add to Block List\" ><img src=\"$tdown\" class=\"icon-action\"></a>";
			$onclick = "onclick=\"sfs_ajax_process($key,$container,add_white,$ajaxurl);return false;\"";
			$show   .= " <a href=\"\" $onclick title=\"Add to $key Allow List\" alt=\"Add to Allow List\" ><img src=\"$tup\" class=\"icon-action\"></a>";
			$show   .= $who;
			$show   .= "<br>";
		}
		return $show;
	}
}

?>