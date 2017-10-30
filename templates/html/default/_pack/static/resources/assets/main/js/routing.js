var routing = {};
routing.routes = {};
routing.baseUrl = location.href;

(function() {
	var prx = new RegExp('/[{](.+?)([?]?)[}]', 'g');
	var hrx = new RegExp('[#].*$');
	var qrx = new RegExp('[?].*$');
	routing.route = function(name, params) {
		if (routing.routes[name] == undefined) {
			throw "Unknown route name: " + name;
		}
		var paramscopy = $.extend(true, {}, params);
		var url = location.protocol + '//' + location.host + '/' + routing.routes[name].replace(prx, function(m, key, opt) {
			if (params[key] != undefined) {
				delete paramscopy[key];
				return '/' + params[key];
			} else if (opt == '?') {
				return '';
			} else {
				throw "Required parameter '" + key + "' is missing";
			}
		});
		return routing.query(url, paramscopy);
	}
	routing.query = function(url, params) {
		if (params == undefined) {
			params = url;
			url = routing.baseUrl;
		}
		var urlhash = url.split('#');
		var hash = '';
		if (urlhash.length > 1) {
			url = urlhash.shift();
			hash = '#' + urlhash.join('#');
		}
		var query = $.query.load(url);
		for (var key in params) {
			query.SET(key, params[key]);
		}
		url = url.replace(hrx, '');
		url = url.replace(qrx, '');
		return url + query.toString() + hash;
	}
})();