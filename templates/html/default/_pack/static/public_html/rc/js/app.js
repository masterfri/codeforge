app = {};
app.extend = function(ns, stuff) {
	var p = ns.split('.'), o = app, k;
	while (p.length > 1) {
		k = p.shift();
		if (o[k] === undefined) {
			o[k] = {};
		}
		o = o[k];
	}
	k = p.shift();
	o[k] = stuff;
	return stuff;
}

app.extend('init', function() {
	(function(sidebar) {
		$('[data-toggle="sidebar"]').on('click', function() {
			sidebar.toggleClass("toggled");
		});
	})($('#wrapper'));
});

app.extend('form.addErrors', function(form, errors) {
	for (var name in errors) {
		var input = form.find('[name="' + name + '"],[name="' + name + '[]"]').eq(0);
		app.form.addError(input, errors[name]);
	}
});

app.extend('form.addError', function(element, errors) {
	var error = $('<div class="help-block error-message"></div>').text(errors[0]);
	element.closest('.form-group').addClass('has-error');
	element.closest('.input').append(error);
});

app.extend('form.clearErrors', function(form) {
	form.find('.error-message').remove();
	form.find('.form-group.has-error').removeClass('has-error');
});

app.extend('ajaxik.isPageLoading', function(e, opts) {
	if (e.is('a') || e.is('form')) {
		return (opts.target || '@maincontainer') == '@maincontainer';
	} else {
		return e.is(opts.mainContainer);
	}
});

app.extend('ajaxik.init', function(opts) {
	var pb = new MiniProgressBar();
	return app.extend('ajaxik.instance', $('body').ajaxik($.extend({
		templatePath: '/rc/templates/',
		'mainContainer': '#maincontainer',
		'start': function(e, opts) {
			if (app.ajaxik.isPageLoading(e, opts)) {
				routing.baseUrl = opts.url;
				pb.start();
			}
			if (e.is('form')) {
				app.form.clearErrors(e);
			}
		},
		'always': function(e, opts) {
			if (app.ajaxik.isPageLoading(e, opts)) {
				pb.finish();
			}
		},
		'fail': function(element, options, xhr, status, error) {
			if (element && element.is('form') && xhr.status == 422) {
				app.form.addErrors(element, xhr.responseJSON);
			} else {
				bootbox.alert('<h3>Error: ' + xhr.status + '</h3><p>' + error + '</p>');
			}
		},
		'stateChanged': function(data) {
			routing.baseUrl = location.href;
		},
		'confirm': function(message, ok) {
			bootbox.confirm(message, function(result) {
				if (result) {
					ok();
				}
			});
		},
		'processors': {
			'message': function(message) {
				if (typeof message == 'string') {
					toastr.success(message);
				} else {
					toastr[message.type](message.text, message.title);
				}
			}
		}
	}, opts || {}), true));
});

$.ajaxik.addEventListener('login', function(params) {
	location.href = params.intended || '/';
});

$.ajaxik.addEventListener('logout', function(params) {
	location.href = '/';
});

$.ajaxik.registerPlugin('datepicker', function(params) {
	this.wrap('<div class="input-group date"></div>')
		.after('<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>')
		.parent().datetimepicker($.extend({
			'format': 'YYYY-MM-DD'
		}, params || {}));
});

$.ajaxik.registerPlugin('timepicker', function(params) {
	this.wrap('<div class="input-group date"></div>')
		.after('<span class="input-group-addon"><span class="glyphicon glyphicon-time"></span></span>')
		.parent().datetimepicker($.extend({
			'format': 'HH:mm'
		}, params || {}));
});

$.ajaxik.registerPlugin('datetimepicker', function(params) {
	this.wrap('<div class="input-group date"></div>')
		.after('<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>')
		.parent().datetimepicker($.extend({
			'format': 'YYYY-MM-DD HH:mm'
		}, params || {}));
});

$.ajaxik.registerPlugin('select2-remote', function(params) {
	this.select2({
		'theme': 'bootstrap',
		'ajax': {
			'url': params.source,
			'dataType': 'json',
			'delay': 250,
			'data': function (params) {
				return {
					'term': params.term,
					'page': params.page
				};
			},
			'processResults': function (data, params) {
				params.page = params.page || 1;
				return {
					results: data.items,
					pagination: {
						more: data.more
					}
				};
			},
			cache: true
		},
	});
});

$(function() {
	app.init();
});