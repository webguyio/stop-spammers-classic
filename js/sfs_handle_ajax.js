var sfs_ajax_who = "";

function sfs_ajax_process(sip, contx, sfunc, url, email = '') {
	sfs_ajax_who = contx;
	var func_nonce = StopSpammersAjaxConfig.func_nonces[sfunc] || '';
	var data = {
		action: 'sfs_process',
		ip: sip,
		email: email,
		cont: contx,
		func: sfunc,
		ajax_url: url,
		_ajax_nonce: StopSpammersAjaxConfig.actions.sfs_process,
		func_nonce: func_nonce,
	};
	jQuery.post(StopSpammersAjaxConfig.ajax_url, data, sfs_ajax_return_process)
	.fail(sfs_ajax_error_handler);
}

function sfs_ajax_error_handler(xhr, status, error) {
	try {
		var response = JSON.parse(xhr.responseText);
		if (response.data) {
			alert(response.data);
		}
	} catch (exception) {
		alert(error);
	}
}

function sfs_ajax_return_process(response) {
	var el = "";
	if (response.data) {
		if (!response.success) {
			alert(response.data);
		}
		return false;
	}
	if (response == "OK") {
		return false;
	}
	if (response.substring(0, 3) == "err") {
		alert(response);
		return false;
	}
	if (response.substring(0, 4) == "\r\n\r\n") {
		alert(response);
		return false;
	}
	if (sfs_ajax_who != "") {
		var el = document.getElementById(sfs_ajax_who);
		el.innerHTML = response;
	}
	return false;
}

function sfs_ajax_report_spam(t, id, blog, url, email, ip, user) {
	sfs_ajax_who = t;
	var data = {
		action: 'sfs_sub',
		blog_id: blog,
		comment_id: id,
		ajax_url: url,
		email: email,
		ip: ip,
		user: user,
		_ajax_nonce: StopSpammersAjaxConfig.actions.sfs_sub,
	};
	jQuery.post(StopSpammersAjaxConfig.ajax_url, data, sfs_ajax_return_spam)
	.fail(sfs_ajax_error_handler);
}

function sfs_ajax_return_spam(response) {
	sfs_ajax_who.innerHTML = " Spam Reported";
	sfs_ajax_who.style.color = "green";
	sfs_ajax_who.style.fontWeight = "bolder";
	if (response.indexOf('data submitted successfully') > 0) {
		return false;
	}
	if (response.indexOf('recent duplicate entry') > 0) {
		sfs_ajax_who.innerHTML = " Spam Already Reported";
		sfs_ajax_who.style.color = "yellow";
		sfs_ajax_who.style.fontWeight = "bolder";
		return false;
	}
	sfs_ajax_who.innerHTML = " Status: " + response;
	sfs_ajax_who.style.color = "black";
	sfs_ajax_who.style.fontWeight = "bolder";
	alert(response);
	return false;
}