/*
 * Ajax integration kit
 * 
 * @license
 * Copyright (c) 2016 Grigory Ponomar.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

(function($) {
	
	var pluginFactories = {};
	var eventListeners = {};
	var templates = {};
	
	$.ajaxik = {
		defaults: {
			linkSelector: 'a:not([data-ajaxik="ignore"],[target="_blank"],[href^="#"]),button[data-url]',
			formSelector: 'form:not([data-ajaxik="ignore"],[target="_blank"])',
			mainContainer: 'body',
			enableHistory: true,
			processors: {},
			start: function() {},
			success: function() {},
			always: function() {},
			init: function() {},
			updated: function() {},
			stateChanged: function() {},
			fail: function(element, options, xhr, status, error) {
				alert('Error: ' + xhr.status + ' ' + error);
			},
			confirm: function(message, ok) {
				if (confirm(message)) {
					ok();
				}
			},
			disableUI: function(context) {
				var elements;
				if (context.is('form')) {
					elements = $(context.get(0).elements);
				} else {
					elements = context.filter('a,button');
				}
				return elements.filter(':not([disabled])').attr('disabled', 'disabled');
			},
			enableUI: function(elements) {
				elements.removeAttr('disabled');
			},
			templateEngine: function(template, data, send) {
				var that = this;
				this.getCompiledTemplate(template, function(compiled) {
					send(compiled(data));
				});
			},
			templatePath: 'templates/',
			templateSuffix: '.hbs'
		},
		registerPlugin: function(name, factory) {
			pluginFactories[name] = factory;
		},
		unregisterPlugin: function(name) {
			delete pluginFactories[name];
		},
		addEventListener: function(type, handler) {
			if (type in eventListeners) {
				eventListeners[type].push(handler);
			} else {
				eventListeners[type] = [handler];
			}
		},
		removeEventListener: function(type, handler) {
			if (type in eventListeners) {
				if (handler !== undefined) {
					var i = eventListeners[type].indexOf(handler);
					if (i != -1) {
						eventListeners[type].splice(i, 1);
					}
				} else {
					delete eventListeners[type];
				}
			}
		}
	};
	
	var Ajaxik = function(options) {
		var that = this;
		this.options = $.extend({}, $.ajaxik.defaults, options || {});
		if (this.options.enableHistory && history.replaceState) {
			history.replaceState($(this.options.mainContainer).html(), document.title, location.href);
			if (window.addEventListener) {
				window.addEventListener("popstate", function(event) {
					if (event.state != null) {
						that.returnState(event.state);
						that.options.stateChanged(event.state);
					}
				});
			} else {
				window.attachEvent("onpopstate", function(event) {
					if (event.state != null) {
						that.returnState(event.state);
						that.options.stateChanged(event.state);
					}
				});
			}
		}
	}
	
	Ajaxik.prototype.init = function(element) {
		var that = this;
		var opts = $.extend({}, this.options, element.data());
		element.filter(opts.linkSelector).add(element.find(opts.linkSelector)).on('click', function() {
			that.load($(this));
			return false;
		});
		element.filter(opts.formSelector).add(element.find(opts.formSelector)).on('submit', function() {
			that.submit($(this));
			return false;
		});
		element.filter('[data-plugin]').add(element.find('[data-plugin]')).each(function() {
			var $this = $(this);
			var plugin = $this.data('plugin');
			if (plugin in pluginFactories) {
				var params = $this.data('params');
				pluginFactories[plugin].call($this, params);
			}
		});
		element.filter('[data-ajaxik=autoload]').add(element.find('[data-ajaxik=autoload]')).each(function() {
			that.load($(this));
		});
		opts.init(element, opts);
		this.trigger(element, 'ajaxik.init', {options: opts});
	}
	
	Ajaxik.prototype.load = function(element, url) {
		var that = this;
		var opts = $.extend({}, this.options, element.data());
		var confirmed = opts.confirmation === undefined ? function(m, c) {c();} : opts.confirm;
		confirmed(opts.confirmation, function() {
			if (url !== undefined) {
				opts.url = url;
			} else if (element.is('a')) {
				opts.url = element.prop('href');
			}
			var settings = {
				url: opts.url || location.href,
				type: (element.data('method') || 'GET').toUpperCase()
			};
			if (settings.type == 'POST' && opts.postdata !== undefined) {
				settings.data = opts.postdata;
			}
			var disabledElements = that.disableUI(element);
			opts.start(element, opts);
			that.trigger(element, 'ajaxik.loading', {options: opts});
			$.ajax(settings)
			.done(function(response, status, xhr) {
				that.trigger(element, 'ajaxik.loaded', {response: response, options: opts});
				that.processResponse(element, response, xhr, opts);
				opts.success(response, element, opts, status, xhr);
			})
			.fail(function(xhr, status, error) {
				opts.fail(element, opts, xhr, status, error);
				that.trigger(element, 'ajaxik.fail', {xhr: xhr, options: opts});
			})
			.always(function(data, status, xhr) {
				that.enableUI(disabledElements, element);
				opts.always(element, opts, data, status, xhr);
			});
		});
	}
	
	Ajaxik.prototype.submit = function(element) {
		var that = this;
		var opts = $.extend({}, this.options, element.data());
		var confirmed = opts.confirmation === undefined ? function(m, c) {c();} : opts.confirm;
		confirmed(opts.confirmation, function() {
			opts.url = element.prop('action');
			var settings = {
				url: opts.url || location.href,
				type: (element.attr('method') || 'GET').toUpperCase()
			};
			if ((element.attr('enctype') || '').toLowerCase() == 'multipart/form-data') {
				var data = new FormData();
				element.serializeArray().forEach(function(input) {
					data.append(input.name, input.value);
				});
				element.find('input[type=file]').each(function() {
					for (var i = 0; i < this.files.length; i++) {
						data.append(this.name, this.files[i]);
					}
				});
				settings.data = data;
				settings.processData = false;
				settings.contentType = false;
			} else {
				settings.data = element.serialize();
			}
			var disabledElements = that.disableUI(element);
			opts.start(element, opts);
			that.trigger(element, 'ajaxik.loading', {options: opts});
			$.ajax(settings)
			.done(function(response, status, xhr) {
				that.trigger(element, 'ajaxik.loaded', {response: response, options: opts});
				that.processResponse(element, response, xhr, opts);
				opts.success(response, element, opts, status, xhr);
			})
			.fail(function(xhr, status, error) {
				opts.fail(element, opts, xhr, status, error);
				that.trigger(element, 'ajaxik.fail', {xhr: xhr, options: opts});
			})
			.always(function(data, status, xhr) {
				that.enableUI(disabledElements, element);
				opts.always(element, opts, data, status, xhr);
			});
		});
	}
	
	Ajaxik.prototype.populate = function(element, content) {
		var that = this;
		var opts = $.extend({}, this.options, element.data());
		var $content = $(content);
		if (opts.populate == 'replace') {
			element.replaceWith($content);
		} else if (opts.populate == 'append') {
			element.append($content);
		} else if (opts.populate == 'prepend') {
			element.prepend($content);
		} else {
			element.html($content);
		}
		opts.updated(element, $content, opts);
		this.trigger(element, 'ajaxik.updated', {content: $content, options: opts});
		$content.each(function() {
			that.init($(this));
		});
	}
	
	Ajaxik.prototype.populateTemplate = function(element, template) {
		var opts = $.extend({'url': location.href}, this.options, element.data());
		this.processTemplate(template, element, opts);
	}
	
	Ajaxik.prototype.disableUI = function (element) {
		if (this.options.disableUI !== false) {
			return this.options.disableUI(element);
		} else {
			return false;
		}
	}
	
	Ajaxik.prototype.enableUI = function (elements, context) {
		if (this.options.enableUI !== false) {
			return this.options.enableUI(elements, context);
		}
	}
	
	Ajaxik.prototype.responseIsJSON = function (xhr) {
		var ct = (xhr.getResponseHeader("content-type") || "").toLowerCase();
		return ct.indexOf('application/json') != -1;
	}
	
	Ajaxik.prototype.getDestination = function (context, opts) {
		if (context === null || context === undefined) {
			return $(opts.mainContainer);
		} else if (context.is('a') || context.is('form')) {
			if (opts.target) {
				if ('@self' === opts.target) {
					return context;
				} else if ('@parent' === opts.target) {
					return context.parent();
				} else if ('@maincontainer' === opts.target) {
					return $(opts.mainContainer);
				} else {
					return $(opts.target);
				}
			} else {
				return $(opts.mainContainer);
			}
		} else {
			return context;
		}
	}
	
	Ajaxik.prototype.processResponse = function (context, response, xhr, opts) {
		var that = this;
		this.trigger(context, 'ajaxik.processing', {response: response, options: opts});
		if (this.responseIsJSON(xhr)) {
			for (var action in response) {
				switch (action) {
					case 'event':
						this.processEvent(response.event, context, opts);
						break;
						
					case 'template':
						this.processTemplate(response.template, context, opts);
						break;
						
					case 'update':
						this.processUpdate(response.update, context, opts);
						break;
						
					case 'redirect':
						this.processRedirect(response.redirect, context, opts);
						break;
						
					default:
						if (action in opts.processors) {
							opts.processors[action].call(this, response[action], context, opts);
						}
						break;
				}
			}
		} else {
			var dest = this.getDestination(context, opts);
			if (this.isStateChangeRequired(opts, dest)) {
				this.changeState(opts.url, response);
			}
			this.populate(dest, response);
		}
		this.trigger(context, 'ajaxik.processed', {response: response, options: opts});
	}
	
	Ajaxik.prototype.processEvent = function (event, context, opts) {
		if (typeof event == 'object') {
			this.fireEvent(event.name, event.params);
		} else {
			this.fireEvent(event, {});
		}
	}
	
	Ajaxik.prototype.processRedirect = function (url, context, opts) {
		this.load($(opts.mainContainer), url);
	}
	
	Ajaxik.prototype.processTemplate = function (template, context, opts) {
		var that = this;
		opts.templateEngine.call(this, template.name, template.data || {}, function(html) {
			var dest = that.getDestination(context, opts);
			if (that.isStateChangeRequired(opts, dest)) {
				that.changeState(opts.url, html);
			}
			that.populate(dest, html);
		});
	}
	
	Ajaxik.prototype.getCompiledTemplate = function(name, callback) {
		if (name in templates) {
			if (templates[name].constructor == Array) {
				templates[name].push(callback);
			} else {
				callback(templates[name]);
			}
		} else {
			var that = this;
			var uri = this.options.templatePath + name.split('.').join('/') + this.options.templateSuffix;
			templates[name] = [callback];
			$.ajax({url: uri})
			.done(function(response, status, xhr) {
				var compiled = Handlebars.compile(response);
				var queue = templates[name];
				templates[name] = compiled;
				queue.forEach(function(callback) {
					callback(compiled);
				});
			})
			.fail(function(xhr, status, error) {
				that.options.fail(null, that.options, xhr, status, error);
			})
		}
	}
	
	Ajaxik.prototype.processUpdate = function (updates, context, opts) {
		var that = this;
		updates.forEach(function(update) {
			var dest = $('#' + update.id);
			if (update.content !== undefined) {
				that.populate(dest, update.content);
			} else {
				opts.templateEngine.call(that, update.template, update.data || {}, function(html) {
					that.populate(dest, html);
				});
			}
		});
	}
	
	Ajaxik.prototype.isStateChangeRequired = function (opts, dest) {
		return  opts.enableHistory && 
				opts.url && 
				dest.is(opts.mainContainer);
	}
	
	Ajaxik.prototype.changeState = function (url, response, title) {
		if (history.pushState) {
			if (url == location.href) {
				history.replaceState(response, title || '', url);
			} else {
				history.pushState(response, title || '', url);
			}
			this.options.stateChanged(response);
		}
	}
	
	Ajaxik.prototype.returnState = function (state) {
		var dest = $(this.options.mainContainer);
		this.populate(dest, state);
	}
	
	Ajaxik.prototype.fireEvent = function(event, params) {
		if (event in eventListeners) {
			var handlers = eventListeners[event];
			for (var i = 0; i < handlers.length; i++) {
				if (handlers[i].call(this, params) === false) {
					break;
				}
			}
		}
	}
	
	Ajaxik.prototype.trigger = function(element, event, params) {
		element.trigger($.Event(event, {
			invokeArgs: $.extend({ajaxik: this}, params || {})
		}));
	}
	
	var methods = {
		init : function(options, returnInstance) {
			var ajaxik = new Ajaxik(options);
			this.each(function() {
				ajaxik.init($(this));
			});
			return returnInstance === true ? ajaxik : this;
		},
		load: function(options, returnInstance) {
			if (typeof options == 'string') {
				options = {url: options};
			} else if (typeof options == 'function') {
				options = {success: options};
			}
			var ajaxik = new Ajaxik(options);
			this.each(function() {
				ajaxik.load($(this));
			});
			return returnInstance === true ? ajaxik : this;
		},
		submit: function(options, returnInstance) {
			if (typeof options == 'string') {
				options = {url: options};
			} else if (typeof options == 'function') {
				options = {success: options};
			}
			var ajaxik = new Ajaxik(options);
			$(this).each(function() {
				ajaxik.submit($(this));
			});
			return returnInstance === true ? ajaxik : this;
		},
		populate: function(content, options, returnInstance) {
			var ajaxik = new Ajaxik(options);
			$(this).each(function() {
				ajaxik.populate($(this), content);
			});
			return returnInstance === true ? ajaxik : this;
		},
		populateTemplate: function(template, options, returnInstance) {
			var ajaxik = new Ajaxik(options);
			$(this).each(function() {
				ajaxik.populateTemplate($(this), template);
			});
			return returnInstance === true ? ajaxik : this;
		}
	};
	
	$.fn.ajaxik = function(method) {
		if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else if (method in methods) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		}
	}
	
})(jQuery);
