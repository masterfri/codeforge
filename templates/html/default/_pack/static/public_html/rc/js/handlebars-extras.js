Handlebars.registerHelper('route', function(name, data) {
	return routing.route(name, data.hash);
});

Handlebars.registerHelper('if_eq', function(a, b, opts) {
	return (a == b) ? opts.fn(this) : opts.inverse(this);
});
			
Handlebars.registerHelper('pagination', function(table, data) {
	var html = '';
	if (table.last_page > 1) {
		var maxlinks = data.hash.links || 5;
		var start = Math.max(1, table.current_page - Math.ceil(maxlinks / 2));
		var end = Math.min(table.last_page, start + maxlinks);
		var url;
		html += '<nav class="page-nav" aria-label="Page navigation">';
		html += '<ul class="pagination">';
		if (table.current_page > 1) {
			url = Handlebars.Utils.escapeExpression(routing.query({'page': table.current_page - 1}));
			html += '<li><a href="' + url + '" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
		}
		for (var i = start; i <= end; i++) {
			url = Handlebars.Utils.escapeExpression(routing.query({'page': i}));
			if (i == table.current_page) {
				html += '<li class="active"><a href="' + url + '">' + i + '</a></li>';
			} else {
				html += '<li><a href="' + url + '">' + i + '</a></li>';
			}
		}
		if (table.current_page < table.last_page) {
			url = Handlebars.Utils.escapeExpression(routing.query({'page': table.current_page + 1}));
			html += '<li><a href="' + url + '" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
		}
		html += '</ul>';
		html += '</nav>';
	}
	return html;
});

Handlebars.registerHelper('options', function(options, data) {
	var key, val, html = '', selected = data.hash.selected || '';
	if (data.hash.placeholder) {
		html += '<option value="">' + Handlebars.Utils.escapeExpression(data.hash.placeholder) + '</option>';
	}
	for (var i = 0; i < options.length; i++) {
		if (typeof options[i] == 'string') {
			key = val = options[i];
		} else {
			key = options[i].key;
			val = options[i].text;
		}
		html += '<option value="' + Handlebars.Utils.escapeExpression(key) + '"';
		if (selected == key) {
			html += ' selected="selected"';
		}
		html += '>' + Handlebars.Utils.escapeExpression(val) + '</option>';
	}
	return html;
});