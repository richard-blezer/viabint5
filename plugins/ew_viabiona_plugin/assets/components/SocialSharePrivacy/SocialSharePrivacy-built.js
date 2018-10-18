jQuery(document).ready(function ($) {
	$('*[data-social-share-privacy=true]:not([data-init=true])').
	socialSharePrivacy().attr('data-init','true');
});

/*
 * jquery.socialshareprivacy.js | 2 Klicks fuer mehr Datenschutz
 *
 * Copyright (c) 2012 Mathias Panzenböck
 *
 * is released under the MIT License http://www.opensource.org/licenses/mit-license.php
 *
 * Spread the word, link to us if you can.
 */
(function ($, undefined) {
	"use strict";

	$.extend($.fn.socialSharePrivacy.settings, {
		// Set perma_option to true.
		// Initially it is only set to true if jQuery.cookie is available.
		perma_option: true,
		set_perma_option: function (service_name) {
			localStorage.setItem('socialSharePrivacy_'+service_name, 'perma_on');
		},
		del_perma_option: function (service_name) {
			localStorage.removeItem('socialSharePrivacy_'+service_name);
		},
		// Only one of the two methods "get_perma_options" and "get_perma_option" has
		// to be implemented. Though the other has to be set to null, so the default
		// cookie based method is not used.
		get_perma_options: null,
		get_perma_option: function (service_name) {
			return localStorage.getItem('socialSharePrivacy_'+service_name) === 'perma_on';
		}
	});
})(jQuery);

/**
 * @license
 * jquery.socialshareprivacy.js | 2 Klicks fuer mehr Datenschutz
 *
 * Copyright (c) 2012-2013 Mathias Panzenböck
 *
 * is released under the MIT License http://www.opensource.org/licenses/mit-license.php
 *
 * Spread the word, link to us if you can.
 */

// load global settings
jQuery(document).ready(function ($) {
	"use strict";

	$('script[type="application/x-social-share-privacy-settings"]').each(function () {
		var settings = (new Function('return ('+(this.textContent||this.innerText||this.text)+');')).call(this);

		if (typeof settings === "object") {
			$.extend(true, $.fn.socialSharePrivacy.settings, settings);
		}
	});
});

/**
 * @license
 * jquery.socialshareprivacy.js | 2 Klicks fuer mehr Datenschutz
 *
 * http://www.heise.de/extras/socialshareprivacy/
 * http://www.heise.de/ct/artikel/2-Klicks-fuer-mehr-Datenschutz-1333879.html
 *
 * Copyright (c) 2011 Hilko Holweg, Sebastian Hilbig, Nicolas Heiringhoff, Juergen Schmidt,
 * Heise Zeitschriften Verlag GmbH & Co. KG, http://www.heise.de
 *
 * Copyright (c) 2012-2013 Mathias Panzenböck
 *
 * is released under the MIT License http://www.opensource.org/licenses/mit-license.php
 *
 * Spread the word, link to us if you can.
 */
(function ($, undefined) {
	"use strict";

	/*
	 * helper functions
	 */ 

	/**
	 * Build an absolute url using a base url.
	 * The provided base url has to be a valid absolute url. It will not be validated!
	 * If no base url is given the document location is used.
	 * Schemes that behave other than http might not work.
	 * This function tries to support file:-urls, but might fail in some cases.
	 * email:-urls aren't supported at all (don't make sense anyway).
	 */
	function absurl (url, base) {
		if (!base) base = document.baseURI || $("html > head > base").last().attr("href") || document.location.href;
		if (!url) {
			return base;
		}
		else if (/^[a-z][-+\.a-z0-9]*:/i.test(url)) {
			// The scheme actually could contain any kind of alphanumerical unicode
			// character, but JavaScript regular expressions don't support unicode
			// character classes. Maybe /^[^:]+:/ or even /^.*:/ would be sufficient?
			return url;
		}
		else if (url.slice(0,2) === '//') {
			return /^[^:]+:/.exec(base)[0]+url;
		}
		
		var ch = url.charAt(0);
		if (ch === '/') {
			if (/^file:/i.test(base)) {
				// file scheme has no hostname
				return 'file://'+url;
			}
			else {
				return /^[^:]+:\/*[^\/]+/i.exec(base)[0]+url;
			}
		}
		else if (ch === '#') {
			// assume "#" only occures at the end indicating the fragment
			return base.replace(/#.*$/,'')+url;
		}
		else if (ch === '?') {
			// assume "?" and "#" only occure at the end indicating the query
			// and the fragment
			return base.replace(/[\?#].*$/,'')+url;
		}
		else {
			var path;
			if (/^file:/i.test(base)) {
				path = base.replace(/^file:\/{0,2}/i,'');
				base = "file://";
			}
			else {
				var match = /^([^:]+:\/*[^\/]+)(\/.*?)?(\?.*?)?(#.*)?$/.exec(base);
				base = match[1];
				path = match[2]||"/";
			}
		
			path = path.split("/");
			path.pop();
			if (path.length === 0) {
				// Ensure leading "/". Of course this is only valid on
				// unix like filesystems. More magic would be needed to
				// support other filesystems.
				path.push("");
			}
			path.push(url);
			return base+path.join("/");
		}
	}

	function formatNumber (number) {
		number = Number(number);

		var prefix = "";
		var suffix = "";
		if (number < 0) {
			prefix = "-";
			number = -number;
		}

		if (number === Infinity) {
			return prefix + "Infinity";
		}

		if (number > 9999) {
			number = number / 1000;
			suffix = "K";
		}

		number = Math.round(number);
		if (number === 0) {
			return "0";
		}

		var buf = [];
		while (number > 0) {
			var part = String(number % 1000);

			number = Math.floor(number / 1000);
			if (number) {
				while (part.length < 3) {
					part = "0"+part;
				}
			}

			buf.unshift(part);
		}

		return prefix + buf.join(",") + suffix;
	}

	// helper function that gets the title of the current page
	function getTitle (options, uri, settings) {
		var title = settings && settings.title;
		if (typeof title === "function") {
			title = title.call(this, options, uri, settings);
		}

		if (title) {
			return title;
		}

		var title = $('meta[name="DC.title"]').attr('content');
		var creator = $('meta[name="DC.creator"]').attr('content');

		if (title && creator) {
			return title + ' - ' + creator;
		} else {
			return title || $('meta[property="og:title"]').attr('content') || $('title').text();
		}
	}

	function getDescription (options, uri, settings) {
		var description = settings && settings.description;
		if (typeof description === "function") {
			description = description.call(this, options, uri, settings);
		}

		if (description) {
			return description;
		}

		return abbreviateText(
			$('meta[name="twitter:description"]').attr('content') ||
			$('meta[itemprop="description"]').attr('content') ||
			$('meta[name="description"]').attr('content') ||
			$.trim($('article, p').first().text()) || $.trim($('body').text()), 3500);
	}

	var IMAGE_ATTR_MAP = {
		META   : 'content',
		IMG    : 'src',
		A      : 'href',
		IFRAME : 'src',
		LINK   : 'href'
	};
	
	// find the largest image of the website
	// if no image at all is found use googles favicon service, which
	// defaults to a small globe (so there is always some image)
	function getImage (options, uri, settings) {
		var imgs, img = settings && settings.image;
		if (typeof img === "function") {
			img = img.call(this, options, uri, settings);
		}

		if (!img) {
			imgs = $('meta[property="image"], meta[property="og:image"], meta[property="og:image:url"], meta[name="twitter:image"], link[rel="image_src"], itemscope *[itemprop="image"]').first();
			if (imgs.length > 0) {
				img = imgs.attr(IMAGE_ATTR_MAP[imgs[0].nodeName]);
			}
		}

		if (img) {
			return absurl(img);
		}

		imgs = $('img').filter(':visible').filter(function () {
			return $(this).parents('.social_share_privacy_area').length === 0;
		});
		if (imgs.length === 0) {
			img = $('link[rel~="shortcut"][rel~="icon"]').attr('href');
			if (img) return absurl(img);
			return 'http://www.google.com/s2/favicons?'+$.param({domain:location.hostname});
		}
		imgs.sort(function (lhs, rhs) {
			return rhs.offsetWidth * rhs.offsetHeight - lhs.offsetWidth * lhs.offsetHeight;
		});
		// browser makes src absolute:
		return imgs[0].src;
	}
	
	// abbreviate at last blank before length and add "\u2026" (horizontal ellipsis)
	function abbreviateText (text, length) {
		// length of UTF-8 encoded string
		if (unescape(encodeURIComponent(text)).length <= length) {
			return text;
		}

		// "\u2026" is actually 3 bytes long in UTF-8
		// TODO: if any of the last 3 characters is > 1 byte long this truncates too much
		var abbrev = text.slice(0, length - 3);

		if (!/\W/.test(text.charAt(length - 3))) {
			var match = /^(.*)\s\S*$/.exec(abbrev);
			if (match) {
				abbrev = match[1];
			}
		}
		return abbrev + "\u2026";
	}
	
	var HTML_CHAR_MAP = {
		'<': '&lt;',
		'>': '&gt;',
		'&': '&amp;',
		'"': '&quot;',
		"'": '&#39;'
	};

	function escapeHtml (s) {
		return s.replace(/[<>&"']/g, function (ch) {
			return HTML_CHAR_MAP[ch];
		});
	}

	function getEmbed (options, uri, settings) {
		var embed = settings && settings.embed;
		if (typeof embed === "function") {
			embed = embed.call(this, options, uri, settings);
		}

		if (embed) {
			return embed;
		}

		embed = ['<iframe scrolling="no" frameborder="0" style="border:none;" allowtransparency="true"'];
		var embed_url = $('meta[name="twitter:player"]').attr('content');

		if (embed_url) {
			var width  = $('meta[name="twitter:player:width"]').attr('content');
			var height = $('meta[name="twitter:player:height"]').attr('content');

			if (width)  embed.push(' width="',escapeHtml(width),'"');
			if (height) embed.push(' height="',escapeHtml(height),'"');
		}
		else {
			embed_url = uri + options.referrer_track;
		}

		embed.push(' src="',escapeHtml(embed_url),'"></iframe>');
		return embed.join('');
	}

	// build URI from rel="canonical" or document.location
	function getURI (options) {
		var uri = document.location.href;
		var canonical = $('head meta[property="og:url"]').attr("content") || $("link[rel=canonical]").attr("href");

		if (canonical) {
			uri = absurl(canonical);
		}
		else if (options && options.ignore_fragment) {
			uri = uri.replace(/#.*$/,'');
		}

		return uri;
	}

	function buttonClickHandler (service_name) {
		function onclick (event) {
			var $container = $(this).parents('li.help_info').first();
			var $share = $container.parents('.social_share_privacy_area').first().parent();
			var options = $share.data('social-share-privacy-options');
			var service = options.services[service_name];
			var button_class = service.button_class || service_name;
			var uri = options.uri;
			if (typeof uri === 'function') {
				uri = uri.call($share[0], options);
			}
			var $switch = $container.find('span.switch');
			if ($switch.hasClass('off')) {
				$container.addClass('info_off');
				$switch.addClass('on').removeClass('off').html(service.txt_on||'\u00a0');
				$container.find('img.privacy_dummy').replaceWith(
					typeof(service.button) === "function" ?
					service.button.call($container.parent().parent()[0],service,uri,options) :
					service.button);
				$share.trigger({type: 'socialshareprivacy:enable', serviceName: service_name, isClick: !event.isTrigger});
			} else {
				$container.removeClass('info_off');
				$switch.addClass('off').removeClass('on').html(service.txt_off||'\u00a0');
				$container.find('.dummy_btn').empty().
					append($('<img/>').addClass(button_class+'_privacy_dummy privacy_dummy').
						attr({
							alt: service.dummy_alt,
							src: service.path_prefix + (options.layout === 'line' ?
								service.dummy_line_img : service.dummy_box_img)
						}).click(onclick));
				$share.trigger({type: 'socialshareprivacy:disable', serviceName: service_name, isClick: !event.isTrigger});
			}
		};
		return onclick;
	}

	// display info-overlays a tiny bit delayed
	function enterHelpInfo () {
		var $info_wrapper = $(this);
		if ($info_wrapper.hasClass('info_off')) return;
		var timeout_id = window.setTimeout(function () {
			$info_wrapper.addClass('display');
			$info_wrapper.removeData('timeout_id');
		}, 500);
		$info_wrapper.data('timeout_id', timeout_id);
	}

	function leaveHelpInfo () {
		var $info_wrapper = $(this);
		var timeout_id = $info_wrapper.data('timeout_id');
		if (timeout_id !== undefined) {
			window.clearTimeout(timeout_id);
		}
		$info_wrapper.removeClass('display');
	}

	function permCheckChangeHandler () {
		var $input = $(this);
		var $share = $input.parents('.social_share_privacy_area').first().parent();
		var options = $share.data('social-share-privacy-options');
		if ($input.is(':checked')) {
			options.set_perma_option($input.attr('data-service'), options);
			$input.parent().addClass('checked');
		} else {
			options.del_perma_option($input.attr('data-service'), options);
			$input.parent().removeClass('checked');
		}
	}

	function enterSettingsInfo () {
		var $settings = $(this);
		var timeout_id = window.setTimeout(function () {
			$settings.find('.settings_info_menu').removeClass('off').addClass('on');
			$settings.removeData('timeout_id');
		}, 500);
		$settings.data('timeout_id', timeout_id);
	}
	
	function leaveSettingsInfo () {
		var $settings = $(this);
		var timeout_id = $settings.data('timeout_id');
		if (timeout_id !== undefined) {
			window.clearTimeout(timeout_id);
		}
		$settings.find('.settings_info_menu').removeClass('on').addClass('off');
	}

	function setPermaOption (service_name, options) {
		$.cookie('socialSharePrivacy_'+service_name, 'perma_on', options.cookie_expires, options.cookie_path, options.cookie_domain);
	}
	
	function delPermaOption (service_name, options) {
		$.cookie('socialSharePrivacy_'+service_name, null, -1, options.cookie_path, options.cookie_domain);
	}

	function getPermaOption (service_name, options) {
		return !!options.get_perma_options(options)[service_name];
	}
	
	function getPermaOptions (options) {
		var cookies = $.cookie();
		var permas = {};
		for (var name in cookies) {
			var match = /^socialSharePrivacy_(.+)$/.exec(name);
			if (match) {
				permas[match[1]] = cookies[name] === 'perma_on';
			}
		}
		return permas;
	}


	// extend jquery with our plugin function
	function socialSharePrivacy (options) {

		if (typeof options === "string") {
			var command = options;
			if (arguments.length === 1) {
				switch (command) {
					case "enable":
						this.find('.switch.off').click();
						break;

					case "disable":
						this.find('.switch.on').click();
						break;

					case "toggle":
						this.find('.switch').click();
						break;

					case "options":
						return this.data('social-share-privacy-options');

					case "destroy":
						this.trigger({type: 'socialshareprivacy:destroy'});
						this.children('.social_share_privacy_area').remove();
						this.removeData('social-share-privacy-options');
						break;

					case "enabled":
						var enabled = {};
						this.each(function () {
							var $self = $(this);
							var options = $self.data('social-share-privacy-options');
							for (var name in options.services) {
								enabled[name] = $self.find('.'+(options.services[name].class_name||name)+' .switch').hasClass('on');
							}
						});
						return enabled;

					case "disabled":
						var disabled = {};
						this.each(function () {
							var $self = $(this);
							var options = $self.data('social-share-privacy-options');
							for (var name in options.services) {
								disabled[name] = $self.find('.'+(options.services[name].class_name||name)+' .switch').hasClass('off');
							}
						});
						return disabled;
	
					default:
						throw new Error("socialSharePrivacy: unknown command: "+command);
				}
			}
			else {
				var arg = arguments[1];
				switch (command) {
					case "enable":
						this.each(function () {
							var $self = $(this);
							var options = $self.data('social-share-privacy-options');
							$self.find('.'+(options.services[arg].class_name||arg)+' .switch.off').click();
						});
						break;

					case "disable":
						this.each(function () {
							var $self = $(this);
							var options = $self.data('social-share-privacy-options');
							$self.find('.'+(options.services[arg].class_name||arg)+' .switch.on').click();
						});
						break;

					case "toggle":
						this.each(function () {
							var $self = $(this);
							var options = $self.data('social-share-privacy-options');
							$self.find('.'+(options.services[arg].class_name||arg)+' .switch').click();
						});
						break;

					case "option":
						if (arguments.length > 2) {
							var value = {};
							value[arg] = arguments[2];
							this.each(function () {
								$.extend(true, $(this).data('social-share-privacy-options'), value);
							});
						}
						else {
							return this.data('social-share-privacy-options')[arg];
						}
						break;

					case "options":
						$.extend(true, options, arg);
						break;

					case "enabled":
						var options = this.data('social-share-privacy-options');
						return this.find('.'+(options.services[arg].class_name||arg)+' .switch').hasClass('on');

					case "disabled":
						var options = this.data('social-share-privacy-options');
						return this.find('.'+(options.services[arg].class_name||arg)+' .switch').hasClass('off');

					default:
						throw new Error("socialSharePrivacy: unknown command: "+command);
				}
			}
			return this;
		}

		return this.each(function () {
			// parse options passed via data-* attributes:
			var data = {};
			if (this.lang) data.language = this.lang;
			for (var i = 0, attrs = this.attributes; i < attrs.length; ++ i) {
				var attr = attrs[i];
				if (/^data-./.test(attr.name)) {
					var path = attr.name.slice(5).replace(/-/g,"_").split(".");
					var ctx = data, j = 0;
					for (; j < path.length-1; ++ j) {
						var name = path[j];
						if (name in ctx) {
							ctx = ctx[name];
							if (typeof ctx === "string") {
								ctx = (new Function("$", "return ("+ctx+");")).call(this, $);
							}
						}
						else {
							ctx = ctx[name] = {};
						}
					}
					var name = path[j];
					if (typeof ctx[name] === "object") {
						ctx[name] = $.extend(true, (new Function("$", "return ("+attr.value+");")).call(this, $), ctx[name]);
					}
					else {
						ctx[name] = attr.value;
					}
				}
			}
			// parse global option values:
			if ('cookie_expires'   in data) data.cookie_expires  = Number(data.cookie_expires);
			if ('perma_option'     in data) data.perma_option    = $.trim(data.perma_option).toLowerCase()    === "true";
			if ('ignore_fragment'  in data) data.ignore_fragment = $.trim(data.ignore_fragment).toLowerCase() === "true";
			if ('set_perma_option' in data) {
				data.set_perma_option = new Function("service_name", "options", data.set_perma_option);
			}
			if ('del_perma_option' in data) {
				data.del_perma_option = new Function("service_name", "options", data.del_perma_option);
			}
			if ('get_perma_option' in data) {
				data.get_perma_option = new Function("service_name", "options", data.get_perma_option);
			}
			if ('get_perma_options' in data) {
				data.get_perma_options = new Function("options", data.get_perma_options);
			}
			if ('order' in data) {
				data.order = $.trim(data.order);
				if (data.order) {
					data.order = data.order.split(/\s+/g);
				}
				else {
					delete data.order;
				}
			}
			if (typeof data.services === "string") {
				data.services = (new Function("$", "return ("+data.services+");")).call(this, $);
			}
			if ('options' in data) {
				data = $.extend(data, (new Function("$", "return ("+data.options+");")).call(this, $));
				delete data.options;
			}
			if ('services' in data) {
				for (var service_name in data.services) {
					var service = data.services[service_name];
					if (typeof service === "string") {
						data.services[service_name] = (new Function("$", "return ("+service+");")).call(this, $);
					}
					// only values of common options are parsed:
					if (typeof service.status === "string") {
						service.status = $.trim(service.status).toLowerCase() === "true";
					}
					if (typeof service.perma_option === "string") {
						service.perma_option = $.trim(service.perma_option).toLowerCase() === "true";
					}
				}
			}
			// overwrite default values with user settings
			var this_options = $.extend(true,{},socialSharePrivacy.settings,options,data);
			var order = this_options.order || [];

			var dummy_img  = this_options.layout === 'line' ? 'dummy_line_img' : 'dummy_box_img';
			var any_on     = false;
			var any_perm   = false;
			var any_unsafe = false;
			var unordered  = [];
			for (var service_name in this_options.services) {
				var service = this_options.services[service_name];
				if (service.status) {
					any_on = true;
					if ($.inArray(service_name, order) === -1) {
						unordered.push(service_name);
					}
					if (service.privacy !== 'safe') {
						any_unsafe = true;
						if (service.perma_option) {
							any_perm = true;
						}
					}
				}
				if (!('language' in service)) {
					service.language = this_options.language;
				}
				if (!('path_prefix' in service)) {
					service.path_prefix = this_options.path_prefix;
				}
				if (!('referrer_track' in service)) {
					service.referrer_track = '';
				}
			}
			unordered.sort();
			order = order.concat(unordered);

			// check if at least one service is activated
			if (!any_on) {
				return;
			}

			// insert stylesheet into document and prepend target element
			if (this_options.css_path) {
				var css_path = (this_options.path_prefix||"") + this_options.css_path;
				// IE fix (needed for IE < 9 - but done for all IE versions)
				if (document.createStyleSheet) {
					document.createStyleSheet(css_path);
				} else if ($('link[href="'+css_path+'"]').length === 0) {
					$('<link/>',{rel:'stylesheet',type:'text/css',href:css_path}).appendTo(document.head);
				}
			}

			// get stored perma options
			var permas;
			if (this_options.perma_option && any_perm) {
				if (this_options.get_perma_options) {
					permas = this_options.get_perma_options(this_options);
				}
				else {
					permas = {};
					for (var service_name in this_options.services) {
						permas[service_name] = this_options.get_perma_option(service_name, this_options);
					}
				}
			}

			// canonical uri that will be shared
			var uri = this_options.uri;
			if (typeof uri === 'function') {
				uri = uri.call(this, this_options);
			}

			var $context = $('<ul class="social_share_privacy_area"></ul>').addClass(this_options.layout);
			var $share = $(this);

			$share.prepend($context).data('social-share-privacy-options',this_options);

			for (var i = 0; i < order.length; ++ i) {
				var service_name = order[i];
				var service = this_options.services[service_name];

				if (service && service.status) {
					var class_name = service.class_name || service_name;
					var button_class = service.button_class || service_name;
					var $help_info;

					if (service.privacy === 'safe') {
						$help_info = $('<li class="help_info"><div class="info">' +
							service.txt_info + '</div><div class="dummy_btn"></div></li>').addClass(class_name);
						$help_info.find('.dummy_btn').
							addClass(button_class).
							append(service.button.call(this,service,uri,this_options));
					}
					else {
						$help_info = $('<li class="help_info"><div class="info">' +
							service.txt_info + '</div><span class="switch off">' + (service.txt_off||'\u00a0') +
							'</span><div class="dummy_btn"></div></li>').addClass(class_name);
						$help_info.find('.dummy_btn').
							addClass(button_class).
							append($('<img/>').addClass(button_class+'_privacy_dummy privacy_dummy').
								attr({
									alt: service.dummy_alt,
									src: service.path_prefix + service[dummy_img]
								}));
					
						$help_info.find('.dummy_btn img.privacy_dummy, span.switch').click(
							buttonClickHandler(service_name));
					}
					$context.append($help_info);
				}
			}
			
			//
			// append Info/Settings-area
			//
			if (any_unsafe) {
				var $settings_info = $('<li class="settings_info"><div class="settings_info_menu off perma_option_off"><a>' +
					'<span class="help_info icon"><span class="info">' + this_options.txt_help + '</span></span></a></div></li>');
				var $info_link = $settings_info.find('> .settings_info_menu > a').attr('href', this_options.info_link);
				if (this_options.info_link_target) {
					$info_link.attr("target",this_options.info_link_target);
				}
				$context.append($settings_info);

				$context.find('.help_info').on('mouseenter', enterHelpInfo).on('mouseleave', leaveHelpInfo);

				// menu for permanently enabling of service buttons
				if (this_options.perma_option && any_perm) {

					// define container
					var $container_settings_info = $context.find('li.settings_info');

					// remove class that fomrats the i-icon, because perma-options are shown
					var $settings_info_menu = $container_settings_info.find('.settings_info_menu');
					$settings_info_menu.removeClass('perma_option_off');

					// append perma-options-icon (.settings) and form (hidden)
					$settings_info_menu.append(
						'<span class="settings">' + this_options.txt_settings + '</span><form><fieldset><legend>' +
						this_options.settings_perma + '</legend></fieldset></form>');

					// write services with <input> and <label> and checked state from cookie
					var $fieldset = $settings_info_menu.find('form fieldset');
					for (var i = 0; i < order.length; ++ i) {
						var service_name = order[i];
						var service = this_options.services[service_name];

						if (service && service.status && service.perma_option && service.privacy !== 'safe') {
							var class_name = service.class_name || service_name;
							var perma = permas[service_name];
							var $field = $('<label><input type="checkbox"' + (perma ? ' checked="checked"/>' : '/>') +
								service.display_name + '</label>');
							$field.find('input').attr('data-service', service_name);
							$fieldset.append($field);

							// enable services when cookie set and refresh cookie
							if (perma) {
								$context.find('li.'+class_name+' span.switch').click();
								this_options.set_perma_option(service_name, this_options);
							}
						}
					}

					// indicate clickable setings gear
					$container_settings_info.find('span.settings').css('cursor', 'pointer');

					// show settings menu on hover
					$container_settings_info.on('mouseenter', enterSettingsInfo).on('mouseleave', leaveSettingsInfo);

					// interaction for <input> to enable services permanently
					$container_settings_info.find('fieldset input').on('change', permCheckChangeHandler);
				}
			}
			$share.trigger({type: 'socialshareprivacy:create', options: this_options});
		});
	};

	// expose helper functions:
	socialSharePrivacy.absurl     = absurl;
	socialSharePrivacy.escapeHtml = escapeHtml;
	socialSharePrivacy.getTitle   = getTitle;
	socialSharePrivacy.getImage   = getImage;
	socialSharePrivacy.getEmbed   = getEmbed;
	socialSharePrivacy.getDescription = getDescription;
	socialSharePrivacy.abbreviateText = abbreviateText;
	socialSharePrivacy.formatNumber   = formatNumber;

	socialSharePrivacy.settings = {
		'services'          : {},
		'info_link'         : 'http://panzi.github.io/SocialSharePrivacy/',
		'info_link_target'  : '',
		'txt_settings'      : 'Settings',
		'txt_help'          : 'If you activate these fields via click, data will be sent to a third party (Facebook, Twitter, Google, ...) and stored there. For more details click <em>i</em>.',
		'settings_perma'    : 'Permanently enable share buttons:',
		'layout'            : 'line', // possible values: 'line' (~120x20) or 'box' (~58x62)
		'set_perma_option'  : setPermaOption,
		'del_perma_option'  : delPermaOption,
		'get_perma_options' : getPermaOptions,
		'get_perma_option'  : getPermaOption,
		'perma_option'      : !!$.cookie,
		'cookie_path'       : '/',
		'cookie_domain'     : document.location.hostname,
		'cookie_expires'    : 365,
		'path_prefix'       : '',
		'css_path'          : "stylesheets/socialshareprivacy.css",
		'uri'               : getURI,
		'language'          : 'en',
		'ignore_fragment'   : true
	};

	$.fn.socialSharePrivacy = socialSharePrivacy;
}(jQuery));

/*
 * jquery.socialshareprivacy.js | 2 Klicks fuer mehr Datenschutz
 *
 * http://www.heise.de/extras/socialshareprivacy/
 * http://www.heise.de/ct/artikel/2-Klicks-fuer-mehr-Datenschutz-1333879.html
 *
 * Copyright (c) 2011 Hilko Holweg, Sebastian Hilbig, Nicolas Heiringhoff, Juergen Schmidt,
 * Heise Zeitschriften Verlag GmbH & Co. KG, http://www.heise.de
 *
 * Copyright (c) 2012 Mathias Panzenböck
 *
 * is released under the MIT License http://www.opensource.org/licenses/mit-license.php
 *
 * Spread the word, link to us if you can.
 */
(function ($, undefined) {
	"use strict";

	function get (self, options, uri, settings, name) {
		var value = options[name];
		if (typeof value === "function") {
			return value.call(self, options, uri, settings);
		}
		return String(value);
	}

	$.fn.socialSharePrivacy.settings.services.buffer = {
		'status'            : true,
		'dummy_line_img'    : 'images/dummy_buffer.png',
		'dummy_box_img'     : 'images/dummy_box_buffer.png',
		'dummy_alt'         : '"Buffer"-Dummy',
		'txt_info'          : 'Two clicks for more privacy: The Buffer button will be enabled once you click here. Activating the button already sends data to Buffer &ndash; see <em>i</em>.',
		'txt_off'           : 'not connected to Buffer',
		'txt_on'            : 'connected to Buffer',
		'perma_option'      : true,
		'display_name'      : 'Buffer',
		'referrer_track'    : '',
		'via'               : '',
		'text'              : $.fn.socialSharePrivacy.getTitle,
		'picture'           : $.fn.socialSharePrivacy.getImage,
		'button'            : function (options, uri, settings) {
			return $('<iframe allowtransparency="true" frameborder="0" scrolling="no"></iframe>').attr(
				'src', 'https://widgets.bufferapp.com/button/?'+$.param({
					count   : settings.layout === 'line' ? 'horizontal' : 'vertical',
					via     : get(this, options, uri, settings, 'via'),
					text    : $.fn.socialSharePrivacy.abbreviateText(
						get(this, options, uri, settings, 'text'), 120),
					picture : get(this, options, uri, settings, 'picture'),
					url     : uri + options.referrer_track,
					source  : 'button'
				}));
		}
	};
})(jQuery);

/*
 * jquery.socialshareprivacy.js
 *
 * Copyright (c) 2012 Mathias Panzenböck
 *
 * is released under the MIT License http://www.opensource.org/licenses/mit-license.php
 *
 * Code inspired by Delicious Button v1.1:
 * http://code.google.com/p/delicious-button/
 *
 * Warning: this button uses plaintext http and can be harmful to users under opressive regimes
 *
 */
(function ($, undefined) {
	"use strict";

	$.fn.socialSharePrivacy.settings.services.delicious = {
		'status'            : true,
		'dummy_line_img'    : 'images/dummy_delicious.png',
		'dummy_box_img'     : 'images/dummy_box_delicious.png',
		'dummy_alt'         : '"Delicious"-Dummy',
		'txt_info'          : 'Two clicks for more privacy: The Delicious button will be enabled once you click here. Activating the button already sends data to Delicious &ndash; see <em>i</em>.',
		'txt_off'           : 'not connected to Delicious',
		'txt_on'            : 'connected to Delicious',
		'perma_option'      : true,
		'display_name'      : 'Delicious',
		'txt_button'        : 'Save',
		'referrer_track'    : '',
		'title'             : $.fn.socialSharePrivacy.getTitle,
		'button'            : function (options, uri, settings) {
			var $button = $('<div class="delicious-widget"/>');
			var url = uri + options.referrer_track;

			$.ajax({
				url: "http://feeds.delicious.com/v2/json/urlinfo/data",
				data: {url: url},
				dataType: "jsonp",
				success: function (counts) {
					var hash, total_posts, title, txt_button;
					for (var i = 0; i < counts.length; ++ i) {
						var count = counts[i];
						if (count.url === url) {
							total_posts = parseInt(count.total_posts, 10);
							hash = count.hash;
							title = count.title;
							break;
						}
					}
					if (total_posts) txt_button = $.fn.socialSharePrivacy.formatNumber(total_posts);
					else txt_button = options.txt_button;
					var save_url = "http://delicious.com/save?"+$.param({
						v:     "5",
						url:   url,
						title: (typeof options.title === "function" ?
							options.title.call(this, options, uri, settings) :
							String(options.title)) || title
					});

					$button.html('<a target="delicious" class="icon"><div class="delicious1"></div><div class="delicious2"></div><div class="delicious3"></div></a><a class="count" target="delicious"><i></i><b></b></a>');
					$button.find('i').text(options.txt_button);
					$button.find('b').text(txt_button);
					$button.find('a.icon').attr("href", hash ? "http://delicious.com/url/" + hash : save_url);
					var $count = $button.find('a.count').attr("href", save_url).click(function (event) {
						window.open(save_url + "&noui&jump=close", "delicious", "toolbar=no,width=555,height=555");
						event.preventDefault();
					});
					
					if (total_posts) {
						$count.hover(function () {
							var $self = $(this);
							$self.find("b").stop(1, 1).css("display", "none");
							$self.find("i").fadeIn();
						}, function () {
							var $self = $(this);
							$self.find("i").stop(1, 1).css("display", "none");
							$self.find("b").fadeIn();
						});
					}
				}
			});

			return $button;
		}
	};
})(jQuery);

/*
 * jquery.socialshareprivacy.js
 *
 * Copyright (c) 2012 Mathias Panzenböck
 *
 * is released under the MIT License http://www.opensource.org/licenses/mit-license.php
 *
 */
(function ($, undefined) {
	"use strict";

	var DISQUSWIDGETS = {
		displayCount: function (data) {
			$('.social_share_privacy_area .disqus .disqus-widget:not(.init)').each(function () {
				var $widget = $(this);
				var uri = data.counts[0].id;
				if ($widget.attr("data-uri") === uri) {
					var key = $widget.attr("data-count");
					var count = data.counts[0][key];
					var text = data.text[key];
					var scount = $.fn.socialSharePrivacy.formatNumber(count);
					$widget.attr('title', count === 0 ? text.zero : count === 1 ? text.one : text.multiple.replace('{num}', scount));
					$widget.find('.count a').text(scount);
					$widget.addClass('init');
				}
			});
		}
	};

	$.fn.socialSharePrivacy.settings.services.disqus = {
		'status'            : true,
		'dummy_line_img'    : 'images/dummy_disqus.png',
		'dummy_box_img'     : 'images/dummy_box_disqus.png',
		'dummy_alt'         : '"Disqus"-Dummy',
		'txt_info'          : 'Two clicks for more privacy: The Disqus button will be enabled once you click here. Activating the button already sends data to Disqus &ndash; see <em>i</em>.',
		'txt_off'           : 'not connected to Disqus',
		'txt_on'            : 'connected to Disqus',
		'perma_option'      : true,
		'display_name'      : 'Disqus',
		'referrer_track'    : '',
		'shortname'         : '',
		'count'             : 'comments',
		'onclick'           : null,
		'button'            : function (options, uri, settings) {
			var shortname = options.shortname || window.disqus_shortname || '';
			var $code;
			if (settings.layout === 'line') {
				$code = $('<div class="disqus-widget">'+
					'<a href="#disqus_thread" class="name">Disq<span class="us">us</span></a>'+
					'<span class="count"><i></i><u></u><a href="#disqus_thread">&nbsp;</a></span></div>');
			}
			else {
				$code = $('<div class="disqus-widget">'+
					'<div class="count"><i></i><u></u><a href="#disqus_thread">&nbsp;</a></div>'+
					'<a href="#disqus_thread" class="name">Disq<span class="us">us</span></a></div>');
			}

			$code.attr({
				'data-count'     : options.count,
				'data-shortname' : shortname,
				'data-uri'       : uri + options.referrer_track
			});

			if (options.onclick) {
				$code.find('a').click(typeof options.onclick === "function" ?
					options.onclick : new Function("event", options.onclick));
			}

			// this breaks every other usage of the disqus count API:
			window.DISQUSWIDGETS = DISQUSWIDGETS;

			$.getScript('https://'+shortname+'.disqus.com/count-data.js?2='+encodeURIComponent(uri + options.referrer_track));

			return $code;
		}
	};
})(jQuery);

/*
 * jquery.socialshareprivacy.js | 2 Klicks fuer mehr Datenschutz
 *
 * http://www.heise.de/extras/socialshareprivacy/
 * http://www.heise.de/ct/artikel/2-Klicks-fuer-mehr-Datenschutz-1333879.html
 *
 * Copyright (c) 2011 Hilko Holweg, Sebastian Hilbig, Nicolas Heiringhoff, Juergen Schmidt,
 * Heise Zeitschriften Verlag GmbH & Co. KG, http://www.heise.de
 *
 * Copyright (c) 2012 Mathias Panzenböck
 *
 * is released under the MIT License http://www.opensource.org/licenses/mit-license.php
 *
 * Spread the word, link to us if you can.
 */
(function ($, undefined) {
	"use strict";

	var locales = {"af":["ZA"],"ar":["AR"],"az":["AZ"],"be":["BY"],"bg":["BG"],"bn":["IN"],"bs":["BA"],"ca":["ES"],"cs":["CZ"],"cy":["GB"],"da":["DK"],"de":["DE"],"el":["GR"],"en":["GB","PI","UD","US"],"eo":["EO"],"es":["ES","LA"],"et":["EE"],"eu":["ES"],"fa":["IR"],"fb":["LT"],"fi":["FI"],"fo":["FO"],"fr":["CA","FR"],"fy":["NL"],"ga":["IE"],"gl":["ES"],"he":["IL"],"hi":["IN"],"hr":["HR"],"hu":["HU"],"hy":["AM"],"id":["ID"],"is":["IS"],"it":["IT"],"ja":["JP"],"ka":["GE"],"km":["KH"],"ko":["KR"],"ku":["TR"],"la":["VA"],"lt":["LT"],"lv":["LV"],"mk":["MK"],"ml":["IN"],"ms":["MY"],"nb":["NO"],"ne":["NP"],"nl":["NL"],"nn":["NO"],"pa":["IN"],"pl":["PL"],"ps":["AF"],"pt":["BR","PT"],"ro":["RO"],"ru":["RU"],"sk":["SK"],"sl":["SI"],"sq":["AL"],"sr":["RS"],"sv":["SE"],"sw":["KE"],"ta":["IN"],"te":["IN"],"th":["TH"],"tl":["PH"],"tr":["TR"],"uk":["UA"],"vi":["VN"],"zh":["CN","HK","TW"]};

	$.fn.socialSharePrivacy.settings.services.facebook = {
		'status'            : true,
		'button_class'      : 'fb_like',
		'dummy_line_img'    : 'images/dummy_facebook.png',
		'dummy_box_img'     : 'images/dummy_box_facebook.png',
		'dummy_alt'         : 'Facebook "Like"-Dummy',
		'txt_info'          : 'Two clicks for more privacy: The Facebook Like button will be enabled once you click here. Activating the button already sends data to Facebook &ndash; see <em>i</em>.',
		'txt_off'           : 'not connected to Facebook',
		'txt_on'            : 'connected to Facebook',
		'perma_option'      : true,
		'display_name'      : 'Facebook Like/Recommend',
		'referrer_track'    : '',
		'action'            : 'like',
		'colorscheme'       : 'light',
		'font'              : '',
		'button'            : function (options, uri, settings) {
			// ensure a locale that is supported by facebook
			// otherwise facebook renders nothing
			var match = /^([a-z]{2})_([A-Z]{2})$/.exec(options.language);
			var locale = "en_US";

			if (match) {
				if (match[1] in locales) {
					var subs = locales[match[1]];
					if ($.inArray(match[2], subs) !== -1) {
						locale = options.language;
					}
					else {
						locale = match[1]+"_"+subs[0];
					}
				}
			}
			else if (options.language in locales) {
				locale = options.language+"_"+locales[options.language][0];
			}

			var params = {
				locale     : locale,
				href       : uri + options.referrer_track,
				send       : 'false',
				show_faces : 'false',
				action     : options.action,
				colorscheme: options.colorscheme
			};
			if (options.font) params.font = options.font;

			if (settings.layout === 'line') {
				params.width  = '120';
				params.height = '20';
				params.layout = 'button_count';
			}
			else {
				params.width  = '62';
				params.height = '61';
				params.layout = 'box_count';
			}
			return $('<iframe scrolling="no" frameborder="0" allowtransparency="true"></iframe>').attr(
				'src', 'https://www.facebook.com/plugins/like.php?'+$.param(params));
		}
	};
})(jQuery);

/*
 * Facebook share module for jquery.socialshareprivacy.js | 2 Klicks fuer mehr Datenschutz
 *
 * http://www.heise.de/extras/socialshareprivacy/
 * http://www.heise.de/ct/artikel/2-Klicks-fuer-mehr-Datenschutz-1333879.html
 *
 * Copyright (c) 2011 Hilko Holweg, Sebastian Hilbig, Nicolas Heiringhoff, Juergen Schmidt,
 * Heise Zeitschriften Verlag GmbH & Co. KG, http://www.heise.de
 *
 * Copyright (c) 2012 Mathias Panzenböck
 *
 * Fbshare module:
 * copyright (c) 2013 zzzen.com
 *
 * is released under the MIT License http://www.opensource.org/licenses/mit-license.php
 *
 * Spread the word, link to us if you can.
 */
(function ($, undefined) {
	"use strict";

	$.fn.socialSharePrivacy.settings.services.fbshare = {
		'status'            : true,
		'privacy'           : 'safe',
		'button_class'      : 'fbshare',
		'line_img'          : 'images/fbshare.png',
		'box_img'           : 'images/box_fbshare.png',
		'txt_info'          : 'Share via facebook.',
		'txt_button'        : 'Facebook Share',
		'display_name'      : 'Facebook Share',
		'referrer_track'    : '',
		'button'            : function (options, uri, settings) {
			return $('<a/>', {target: '_blank', href: 'https://www.facebook.com/sharer/sharer.php?'+$.param({u:uri + options.referrer_track})}).append(
				$('<img>', {alt: options.txt_button,
					src: options.path_prefix + (settings.layout === 'line' ? options.line_img : options.box_img)}));
		}
	};
})(jQuery);

/*
 * jquery.socialshareprivacy.js | 2 Klicks fuer mehr Datenschutz
 *
 * Copyright (c) 2012 Mathias Panzenböck
 *
 * is released under the MIT License http://www.opensource.org/licenses/mit-license.php
 *
 * Spread the word, link to us if you can.
 */

(function ($, undefined) {
	"use strict";

	function get (self, options, uri, settings, name) {
		var value = options[name];
		if (typeof value === "function") {
			return value.call(self, options, uri, settings);
		}
		return String(value);
	}

	// using an unsupported language breaks the flattr button
	var langs = {en:true,sq:true,ar:true,be:true,bg:true,ca:true,zh:true,hr:true,cs:true,da:true,nl:true,eo:true,et:true,fi:true,fr:true,es:true,de:true,el:true,iw:true,hi:true,hu:true,is:true,'in':true,ga:true,it:true,ja:true,ko:true,lv:true,lt:true,mk:true,ms:true,mt:true,no:true,nn:true,fa:true,pl:true,pt:true,ro:true,ru:true,sr:true,sk:true,sl:true,sv:true,th:true,tr:true,uk:true,vi:true};

	$.fn.socialSharePrivacy.settings.services.flattr = {
		'status'            : true, 
		'button_class'      : 'flattr',
		'dummy_line_img'    : 'images/dummy_flattr.png',
		'dummy_box_img'     : 'images/dummy_box_flattr.png',
		'dummy_alt'         : '"Flattr"-Dummy',
		'txt_info'          : 'Two clicks for more privacy: The Flattr button will be enabled once you click here. Activating the button already sends data to Flattr &ndash; see <em>i</em>.',
		'txt_off'           : 'not connected to Flattr',
		'txt_on'            : 'connected to Flattr',
		'perma_option'      : true,
		'display_name'      : 'Flattr',
		'referrer_track'    : '',
		'title'             : $.fn.socialSharePrivacy.getTitle,
		'description'       : $.fn.socialSharePrivacy.getDescription,
		'uid'               : '',
		'category'          : '',
		'tags'              : '',
		'popout'            : '',
		'hidden'            : '',
		'button'            : function (options, uri, settings) {
			var attrs = {
				href                   : uri + options.referrer_track,
				title                  : get(this, options, uri, settings, 'title')
			};
			if (options.uid)      attrs['data-flattr-uid']      = options.uid;
			if (options.hidden)   attrs['data-flattr-hidden']   = options.hidden;
			if (options.popout)   attrs['data-flattr-popout']   = options.popout;
			if (options.category) attrs['data-flattr-category'] = options.category;
			if (options.tags)     attrs['data-flattr-tags']     = options.tags;
			if (options.language) {
				var lang = String(options.language).replace('-','_');
				var baselang = lang.split('_')[0];
				if (langs[baselang] === true) {
					attrs['data-flattr-language'] = attrs.lang = lang;
				}
			}
			if (settings.layout === 'line') attrs['data-flattr-button'] = 'compact';

			var $code = $('<a class="FlattrButton">' + get(this, options, uri, settings, 'description') +
				'</a><script text="text/javscript" src="'+
				'https://api.flattr.com/js/0.6/load.js?mode=auto"></script>');

			$code.filter('a').attr(attrs);

			return $code;
		}
	};
})(jQuery);

/*
 * jquery.socialshareprivacy.js | 2 Klicks fuer mehr Datenschutz
 *
 * http://www.heise.de/extras/socialshareprivacy/
 * http://www.heise.de/ct/artikel/2-Klicks-fuer-mehr-Datenschutz-1333879.html
 *
 * Copyright (c) 2011 Hilko Holweg, Sebastian Hilbig, Nicolas Heiringhoff, Juergen Schmidt,
 * Heise Zeitschriften Verlag GmbH & Co. KG, http://www.heise.de
 *
 * Copyright (c) 2012 Mathias Panzenböck
 *
 * is released under the MIT License http://www.opensource.org/licenses/mit-license.php
 *
 * Spread the word, link to us if you can.
 */
(function ($, undefined) {
	"use strict";

	$.fn.socialSharePrivacy.settings.services.gplus = {
		'status'            : true,
		'button_class'      : 'gplusone',
		'dummy_line_img'    : 'images/dummy_gplus.png',
		'dummy_box_img'     : 'images/dummy_box_gplus.png',
		'dummy_alt'         : '"Google+1"-Dummy',
		'txt_info'          : 'Two clicks for more privacy: The Google+ button will be enabled once you click here. Activating the button already sends data to Google &ndash; see <em>i</em>.',
		'txt_off'           : 'not connected to Google+',
		'txt_on'            : 'connected to Google+',
		'perma_option'      : true,
		'display_name'      : 'Google+',
		'referrer_track'    : '',
		'button'            : function (options, uri, settings) {
			// we use the Google+ "asynchronous" code, standard code is flaky if inserted into dom after load
			var $code = $('<div class="g-plusone"></div><script type="text/javascript">window.___gcfg = {lang: "' +
				options.language.replace('_','-') + '"}; (function() { var po = document.createElement("script"); ' +
				'po.type = "text/javascript"; po.async = true; po.src = "https://apis.google.com/js/plusone.js"; ' +
				'var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(po, s); })(); </script>');
			$code.filter('.g-plusone').attr({
				'data-href': uri + options.referrer_track,
				'data-size': settings.layout === 'line' ? 'medium' : 'tall'
			});
			return $code;
		}
	};
})(jQuery);

/*
 * jquery.socialshareprivacy.js
 *
 * Copyright (c) 2012 Mathias Panzenböck
 *
 * is released under the MIT License http://www.opensource.org/licenses/mit-license.php
 *
 */
(function ($, undefined) {
	"use strict";

	$.fn.socialSharePrivacy.settings.services.hackernews = {
		'status'            : true,
		'dummy_line_img'    : 'images/dummy_hackernews.png',
		'dummy_box_img'     : 'images/dummy_box_hackernews.png',
		'dummy_alt'         : '"Hacker News"-Dummy',
		'txt_info'          : 'Two clicks for more privacy: The Hacker News button will be enabled once you click here. Activating the button already sends data to Hacker News &ndash; see <em>i</em>.',
		'txt_off'           : 'not connected to Hacker News',
		'txt_on'            : 'connected to Hacker News',
		'perma_option'      : true,
		'display_name'      : 'Hacker News',
		'txt_n_points'      : '{points} points',
		'txt_one_point'     : '1 point',
		'referrer_track'    : '',
		'title'             : $.fn.socialSharePrivacy.getTitle,
		'button'            : function (options, uri, settings) {
			var url = uri + options.referrer_track;
			var title = typeof(options.title) === 'function' ?
				options.title.call(this, options, uri, settings) :
				String(options.title);

			var $code;
			if (settings.layout === 'line') {
				$code = $('<div class="hackernews-widget">'+
					'<a class="name" target="_blank">Y</a>'+
					'<span class="points"><i></i><u></u><a target="_blank">submit</a></span></div>');
			}
			else {
				$code = $('<div class="hackernews-widget">'+
					'<div class="points"><i></i><u></u><a target="_blank">submit</a></div>'+
					'<a class="name" target="_blank">Y</a></div>');
			}

			$code.find("a").attr("href", "https://news.ycombinator.com/submitlink?"+$.param({
				"u": url,
				"t": title
			}));

			$.ajax("https://api.thriftdb.com/api.hnsearch.com/items/_search?filter[fields][url][]="+encodeURIComponent(url), {
				dataType: "jsonp",
				success: function (data) {
					var item = data.results[0];
					if (item) {
						item = item.item;
						var points = $.fn.socialSharePrivacy.formatNumber(item.points);
						$code.find("a").attr("href", "https://news.ycombinator.com/item?id="+item.id);
						$code.find(".points a").text(points).attr('title',
							item.points === 1 ?
							options.txt_one_point :
							options.txt_n_points.replace(/{points}/g, points));
					}
				}
			});

			return $code;
		}
	};
})(jQuery);

/*
 * jquery.socialshareprivacy.js | 2 Klicks fuer mehr Datenschutz
 *
 * http://www.heise.de/extras/socialshareprivacy/
 * http://www.heise.de/ct/artikel/2-Klicks-fuer-mehr-Datenschutz-1333879.html
 *
 * Copyright (c) 2011 Hilko Holweg, Sebastian Hilbig, Nicolas Heiringhoff, Juergen Schmidt,
 * Heise Zeitschriften Verlag GmbH & Co. KG, http://www.heise.de
 *
 * Copyright (c) 2012 Mathias Panzenböck
 *
 * is released under the MIT License http://www.opensource.org/licenses/mit-license.php
 *
 * Spread the word, link to us if you can.
 */
(function ($, undefined) {
	"use strict";

	$.fn.socialSharePrivacy.settings.services.linkedin = {
		'status'            : true,
		'dummy_line_img'    : 'images/dummy_linkedin.png',
		'dummy_box_img'     : 'images/dummy_box_linkedin.png',
		'dummy_alt'         : '"LinkedIn"-Dummy',
		'txt_info'          : 'Two clicks for more privacy: The Linked in button will be enabled once you click here. Activating the button already sends data to Linked in &ndash; see <em>i</em>.',
		'txt_off'           : 'not connected to LinkedIn',
		'txt_on'            : 'connected to LinkedIn',
		'perma_option'      : true,
		'display_name'      : 'LinkedIn',
		'referrer_track'    : '',
		'onsuccess'         : null,
		'onerror'           : null,
		'showzero'          : false,
		'button'            : function (options, uri, settings) {
			var attrs = {
				'data-counter' : settings.layout === 'line' ? 'right' : 'top',
				'data-url'     : uri + options.referrer_track,
				'data-showzero': String(options.showzero)
			};
			if (options.onsuccess) attrs['data-onsuccess'] = options.onsuccess;
			if (options.onerror)   attrs['data-onerror']   = options.onerror;
			var $code = $('<script type="IN/Share"></script>').attr(attrs);

			if (window.IN && window.IN.parse) {
				$code = $code.add('<script type="text/javascript">IN.parse(document.body);</script>');
			}
			else if ($('script[src^="https://platform.linkedin.com/"]').length === 0) {
				$code = $code.add('<script type="text/javascript" src="https://platform.linkedin.com/in.js"></script>');
			}

			return $code;
		}
	};
})(jQuery);

/*
 * jquery.socialshareprivacy.js | 2 Klicks fuer mehr Datenschutz
 *
 * Copyright (c) 2012 Mathias Panzenböck
 *
 * is released under the MIT License http://www.opensource.org/licenses/mit-license.php
 *
 * Spread the word, link to us if you can.
 */
(function ($, undefined) {
	"use strict";

	function get (self, options, uri, settings, name) {
		var value = options[name];
		if (typeof value === "function") {
			value = value.call(self, options, uri, settings);
		}
		return String(value);
	}

	var getDescription = $.fn.socialSharePrivacy.getDescription;

	function getBody (options, uri, settings) {
		return getDescription.call(this, options, uri, settings) + '\n\n' + uri + options.referrer_track;
	}

	$.fn.socialSharePrivacy.settings.services.mail = {
		'status'            : true,
		'privacy'           : 'safe',
		'button_class'      : 'mail',
		'line_img'          : 'images/mail.png',
		'box_img'           : 'images/box_mail.png',
		'txt_info'          : 'Send this via email to a friend.',
		'txt_button'        : 'Send Email',
		'display_name'      : 'Mail',
		'referrer_track'    : '',
		'subject'           : $.fn.socialSharePrivacy.getTitle,
		'body'              : getBody,
		'button'            : function (options, uri, settings) {
			return $('<a/>').attr(
				'href', 'mailto:?'+$.param({
					subject : get(this, options, uri, settings, 'subject'),
					body    : get(this, options, uri, settings, 'body')
				}).replace(/\+/g,'%20')).append($('<img>', {
					alt: options.txt_button,
					src: options.path_prefix + (settings.layout === 'line' ? options.line_img : options.box_img)
				}));
		}
	};
})(jQuery);

/*
 * jquery.socialshareprivacy.js | 2 Klicks fuer mehr Datenschutz
 *
 * http://www.heise.de/extras/socialshareprivacy/
 * http://www.heise.de/ct/artikel/2-Klicks-fuer-mehr-Datenschutz-1333879.html
 *
 * Copyright (c) 2011 Hilko Holweg, Sebastian Hilbig, Nicolas Heiringhoff, Juergen Schmidt,
 * Heise Zeitschriften Verlag GmbH & Co. KG, http://www.heise.de
 *
 * Copyright (c) 2012 Mathias Panzenböck
 *
 * is released under the MIT License http://www.opensource.org/licenses/mit-license.php
 *
 * Spread the word, link to us if you can.
 */

(function ($, undefined) {
	"use strict";

	function get (self, options, uri, settings, name) {
		var value = options[name];
		if (typeof value === "function") {
			return value.call(self, options, uri, settings);
		}
		return String(value);
	}

	var loadingScript = false;
	function loadScript () {
		// prevent already loaded buttons from being broken:
		$('.social_share_privacy_area .pinterest .pinit a[data-pin-log]').attr('data-pin-do','ignore');
		$.ajax({
			url      : 'https://assets.pinterest.com/js/pinit.js',
			dataType : 'script',
			cache    : true
		});
		// because there is no callback yet I have no choice but to do this now:
		loadingScript = false;
	}

	$.fn.socialSharePrivacy.settings.services.pinterest = {
		'status'            : true, 
		'button_class'      : 'pinit',
		'dummy_line_img'    : 'images/dummy_pinterest.png',
		'dummy_box_img'     : 'images/dummy_box_pinterest.png',
		'dummy_alt'         : '"Pin it"-Dummy',
		'txt_info'          : 'Two clicks for more privacy: The Pin it button will be enabled once you click here. Activating the button already sends data to Pinterest &ndash; see <em>i</em>.',
		'txt_off'           : 'not connected to Pinterest',
		'txt_on'            : 'connected to Pinterest',
		'perma_option'      : true,
		'display_name'      : 'Pinterest',
		'referrer_track'    : '',
		'title'             : $.fn.socialSharePrivacy.getTitle,
		'description'       : $.fn.socialSharePrivacy.getDescription,
		'media'             : $.fn.socialSharePrivacy.getImage,
		'button'            : function (options, uri, settings) {
			var params = {
				url    : uri + options.referrer_track,
				media  : get(this, options, uri, settings, 'media')
			};
			var title       = get(this, options, uri, settings, 'title');
			var description = get(this, options, uri, settings, 'description');
			if (title)       params.title       = title;
			if (description) params.description = description;

			var $code = $('<a data-pin-do="buttonPin"><img /></a>');

			$code.filter('a').attr({
				'data-pin-config' : settings.layout === 'line' ? 'beside' : 'above',
				href              : 'https://pinterest.com/pin/create/button/?'+$.param(params)
			}).find('img').attr('src', 'https://assets.pinterest.com/images/pidgets/pin_it_button.png');

			// This way when the user has permanently enabled pinterest and there are several pinterest
			// buttons on one webpage it will load the script only once and so the buttons will work:
			if (!loadingScript) {
				loadingScript = true;
				setTimeout(loadScript, 10);
			}

			return $code;
		}
	};
})(jQuery);

/*
 * jquery.socialshareprivacy.js | 2 Klicks fuer mehr Datenschutz
 *
 * http://www.heise.de/extras/socialshareprivacy/
 * http://www.heise.de/ct/artikel/2-Klicks-fuer-mehr-Datenschutz-1333879.html
 *
 * Copyright (c) 2011 Hilko Holweg, Sebastian Hilbig, Nicolas Heiringhoff, Juergen Schmidt,
 * Heise Zeitschriften Verlag GmbH & Co. KG, http://www.heise.de
 *
 * Copyright (c) 2012 Mathias Panzenböck
 *
 * is released under the MIT License http://www.opensource.org/licenses/mit-license.php
 *
 * Spread the word, link to us if you can.
 */

(function ($, undefined) {
	"use strict";

	function get (self, options, uri, settings, name) {
		var value = options[name];
		if (typeof value === "function") {
			return value.call(self, options, uri, settings);
		}
		return String(value);
	}

	$.fn.socialSharePrivacy.settings.services.reddit = {
		'status'            : true, 
		'button_class'      : 'reddit',
		'dummy_line_img'    : 'images/dummy_reddit.png',
		'dummy_box_img'     : 'images/dummy_box_reddit.png',
		'dummy_alt'         : '"Reddit this!"-Dummy',
		'txt_info'          : 'Two clicks for more privacy: The reddit this! button will be enabled once you click here. Activating the button already sends data to reddit &ndash; see <em>i</em>.',
		'txt_off'           : 'not connected to reddit',
		'txt_on'            : 'connected to reddit',
		'perma_option'      : true,
		'display_name'      : 'Reddit',
		'referrer_track'    : '',
		'title'             : $.fn.socialSharePrivacy.getTitle,
		'target'            : '',
		'newwindow'         : '1',
		'bgcolor'           : 'transparent',
		'bordercolor'       : '',
		'button'            : function (options, uri, settings) {
			var base_url, w, layout;
			if (settings.layout === 'line') {
				w = 120;
				layout = '/button/button1.html?';
			}
			else {
				w = 58;
				layout = '/button/button2.html?';
			}
			base_url = 'https://redditstatic.s3.amazonaws.com';
			var params = {
				url   : uri + options.referrer_track,
				width : String(w)
			};
			var title  = get(this, options, uri, settings, 'title');
			var target = get(this, options, uri, settings, 'target');
			if (title)  params.title  = title;
			if (target) params.target = target;
			if (options.bgcolor)     params.bgcolor     = options.bgcolor;
			if (options.bordercolor) params.bordercolor = options.bordercolor;
			if (options.newwindow)   params.newwindow   = options.newwindow;

			return $('<iframe allowtransparency="true" frameborder="0" scrolling="no"></iframe>').attr(
				'src', base_url+layout+$.param(params));
		}
	};
})(jQuery);

/*
 * jquery.socialshareprivacy.js | 2 Klicks fuer mehr Datenschutz
 *
 * http://www.heise.de/extras/socialshareprivacy/
 * http://www.heise.de/ct/artikel/2-Klicks-fuer-mehr-Datenschutz-1333879.html
 *
 * Copyright (c) 2011 Hilko Holweg, Sebastian Hilbig, Nicolas Heiringhoff, Juergen Schmidt,
 * Heise Zeitschriften Verlag GmbH & Co. KG, http://www.heise.de
 *
 * Copyright (c) 2012 Mathias Panzenböck
 *
 * is released under the MIT License http://www.opensource.org/licenses/mit-license.php
 *
 * Warning: this button uses plaintext http and can be harmful to users under opressive regimes
 *
 */

(function ($, undefined) {
	"use strict";

	$.fn.socialSharePrivacy.settings.services.stumbleupon = {
		'status'            : true, 
		'button_class'      : 'stumbleupon',
		'dummy_line_img'    : 'images/dummy_stumbleupon.png',
		'dummy_box_img'     : 'images/dummy_box_stumbleupon.png',
		'dummy_alt'         : '"Stumble!"-Dummy',
		'txt_info'          : 'Two clicks for more privacy: The Stumble! button will be enabled once you click here. Activating the button already sends data to StumbleUpon &ndash; see <em>i</em>.',
		'txt_off'           : 'not connected to StumbleUpon',
		'txt_on'            : 'connected to StumbleUpon',
		'perma_option'      : true,
		'display_name'      : 'Stumble Upon',
		'referrer_track'    : '',
		'button'            : function (options, uri, settings) {
			var base_url = 'https:' === document.location.protocol ? 'https://' : 'http://';
			var w, h;

			if (settings.layout === 'line') {
				w = '74';
				h = '18';
				base_url += 'badge.stumbleupon.com/badge/embed/1/?';
			}
			else {
				w = '50';
				h = '60';
				base_url += 'badge.stumbleupon.com/badge/embed/5/?';
			}

			return $('<iframe allowtransparency="true" frameborder="0" scrolling="no"></iframe>').attr({
				src:    base_url+$.param({url: uri + options.referrer_track}),
				width:  w,
				height: h
			});
		}
	};
})(jQuery);

/*
 * jquery.socialshareprivacy.js | 2 Klicks fuer mehr Datenschutz
 *
 * Copyright (c) 2012 Mathias Panzenböck
 *
 * is released under the MIT License http://www.opensource.org/licenses/mit-license.php
 *
 * Spread the word, link to us if you can.
 */
(function ($, undefined) {
	"use strict";

	function getQuote (options, uri, settings) {
		var text = $.trim($('article, p').text());
		
		if (text.length <= 600) {
			return text;
		}

		var abbrev = text.slice(0, 597);
		if (/^\w+$/.test(text.slice(596,598))) {
			var match = /^(.*)\s\S*$/.exec(abbrev);
			if (match) {
				abbrev = match[1];
			}
		}
		return $.trim(abbrev) + "\u2026";
	}

	function getClickthru (options, uri) {
		return uri + options.referrer_track;
	}

	function get (self, options, uri, settings, name) {
		var value = options[name];
		if (typeof value === "function") {
			return value.call(self, options, uri, settings);
		}
		return String(value);
	}

	function openTumblr (event) {
		var winx = window.screenX || window.screenLeft;
		var winy = window.screenY || window.screenTop;
		var winw = window.outerWidth || window.innerWidth;
		var winh = window.outerHeight || window.innerHeight;
		var width = 450;
		var height = 430;
		var x = Math.round(winx + (winw - width)  * 0.5);
		var y = Math.round(winy + (winh - height) * 0.5);
		window.open(this.href, 't', 'left='+x+',top='+y+',toolbar=0,resizable=0,status=0,menubar=0,width='+width+',height='+height);
		event.preventDefault();
	}

	$.fn.socialSharePrivacy.settings.services.tumblr = {
		'status'            : true,
		'privacy'           : 'safe',
		'button_class'      : 'tumblr',
		'line_img'          : 'images/tumblr.png',
		'box_img'           : 'images/box_tumblr.png',
		'txt_info'          : 'Post this on Tumblr.',
		'txt_button'        : 'Share on Tumblr',
		'display_name'      : 'Tumblr',
		'referrer_track'    : '',
		'type'              : 'link', // possible values are 'link', 'quote', 'photo' or 'video'
		// type: 'link':
		'name'              : $.fn.socialSharePrivacy.getTitle,
		'description'       : $.fn.socialSharePrivacy.getDescription,
		// type: 'quote':
		'quote'             : getQuote,
		// type: 'photo':
		'photo'             : $.fn.socialSharePrivacy.getImage,
		'clickthrou'        : getClickthru,
		// type: 'video':
		'embed'             : $.fn.socialSharePrivacy.getEmbed,
		// type: 'photo' or 'video':
		'caption'           : $.fn.socialSharePrivacy.getDescription,
		'button'            : function (options, uri, settings) {
			var $code = $('<a target="_blank"/>').click(openTumblr);
			$('<img>', {
				alt: options.txt_button,
				src: options.path_prefix + (settings.layout === 'line' ? options.line_img : options.box_img)
			}).appendTo($code);
			switch (options.type) {
				case 'link':
					return $code.attr('href', 'https://www.tumblr.com/share/link?'+$.param({
						url         : uri + options.referrer_track,
						name        : get(this, options, uri, settings, 'name'),
						description : get(this, options, uri, settings, 'description')
					}));

				case 'quote':
					return $code.attr('href', 'https://www.tumblr.com/share/quote?'+$.param({
						source      : uri + options.referrer_track,
						quote       : get(this, options, uri, settings, 'quote')
					}));

				case 'photo':
					return $code.attr('href', 'https://www.tumblr.com/share/photo?'+$.param({
						source      : get(this, options, uri, settings, 'photo'),
						caption     : get(this, options, uri, settings, 'caption'),
						clickthrou  : get(this, options, uri, settings, 'clickthrou')
					}));

				case 'video':
					return $code.attr('href', 'https://www.tumblr.com/share/video?'+$.param({
						embed       : get(this, options, uri, settings, 'embed'),
						caption     : get(this, options, uri, settings, 'caption')
					}));
			}
		}
	};
})(jQuery);

/*
 * jquery.socialshareprivacy.js | 2 Klicks fuer mehr Datenschutz
 *
 * http://www.heise.de/extras/socialshareprivacy/
 * http://www.heise.de/ct/artikel/2-Klicks-fuer-mehr-Datenschutz-1333879.html
 *
 * Copyright (c) 2011 Hilko Holweg, Sebastian Hilbig, Nicolas Heiringhoff, Juergen Schmidt,
 * Heise Zeitschriften Verlag GmbH & Co. KG, http://www.heise.de
 *
 * Copyright (c) 2012 Mathias Panzenböck
 *
 * is released under the MIT License http://www.opensource.org/licenses/mit-license.php
 *
 * Spread the word, link to us if you can.
 */

(function ($, undefined) {
	"use strict";

	$.fn.socialSharePrivacy.settings.services.twitter = {
		'status'            : true,
		'button_class'      : 'tweet',
		'dummy_line_img'    : 'images/dummy_twitter.png',
		'dummy_box_img'     : 'images/dummy_box_twitter.png',
		'dummy_alt'         : '"Tweet this"-Dummy',
		'txt_info'          : 'Two clicks for more privacy: The Tweet this button will be enabled once you click here. Activating the button already sends data to Twitter &ndash; see <em>i</em>.',
		'txt_off'           : 'not connected to Twitter',
		'txt_on'            : 'connected to Twitter',
		'perma_option'      : true,
		'display_name'      : 'Twitter',
		'referrer_track'    : '',
		'via'               : '',
		'related'           : '',
		'hashtags'          : '',
		'dnt'               : true,
		'text'              : $.fn.socialSharePrivacy.getTitle,
		'button'            : function (options, uri, settings) {
			var text = typeof(options.text) === 'function' ?
				options.text.call(this, options, uri, settings) :
				String(options.text);
			// 120 is the max character count left after twitters automatic
			// url shortening with t.co
			text = $.fn.socialSharePrivacy.abbreviateText(text, 120);

			var params = {
				url     : uri + options.referrer_track,
				counturl: uri,
				text    : text,
				count   : settings.layout === 'line' ? 'horizontal' : 'vertical',
				lang    : options.language
			};
			if (options.via)      params.via      = options.via;
			if (options.related)  params.related  = options.related;
			if (options.hashtags) params.hashtags = options.hashtags;
			if (options.dnt)      params.dnt      = options.dnt;

			return $('<iframe allowtransparency="true" frameborder="0" scrolling="no"></iframe>').attr(
				'src', 'https://platform.twitter.com/widgets/tweet_button.html?' +
				$.param(params).replace(/\+/g,'%20'));
		}
	};
})(jQuery);

/*
 * jquery.socialshareprivacy.js | 2 Klicks fuer mehr Datenschutz
 *
 * http://www.heise.de/extras/socialshareprivacy/
 * http://www.heise.de/ct/artikel/2-Klicks-fuer-mehr-Datenschutz-1333879.html
 *
 * Copyright (c) 2011 Hilko Holweg, Sebastian Hilbig, Nicolas Heiringhoff, Juergen Schmidt,
 * Heise Zeitschriften Verlag GmbH & Co. KG, http://www.heise.de
 *
 * Copyright (c) 2012 Mathias Panzenböck
 *
 * is released under the MIT License http://www.opensource.org/licenses/mit-license.php
 *
 * Spread the word, link to us if you can.
 */
(function ($, undefined) {
	"use strict";

	$.fn.socialSharePrivacy.settings.services.xing = {
		'status'            : true,
		'dummy_line_img'    : 'images/dummy_xing.png',
		'dummy_box_img'     : 'images/dummy_box_xing.png',
		'dummy_alt'         : '"XING"-Dummy',
		'txt_info'          : 'Two clicks for more privacy: The XING button will be enabled once you click here. Activating the button already sends data to XING &ndash; see <em>i</em>.',
		'txt_off'           : 'not connected to XING',
		'txt_on'            : 'connected to XING',
		'perma_option'      : true,
		'display_name'      : 'XING',
		'referrer_track'    : '',
		'button'            : function (options, uri, settings) {
			var $code = $('<script type="XING/Share"></script>').attr({
				'data-counter' : settings.layout === 'line' ? 'right' : 'top',
				'data-url'     : uri + options.referrer_track,
				'data-lang'    : options.language
			});

			return $code.add("<script type='text/javascript'>(function(d, s) { var x = d.createElement(s); s = d.getElementsByTagName(s)[0]; x.src = 'https://www.xing-share.com/js/external/share.js'; s.parentNode.insertBefore(x, s); })(document, 'script');</script>");
		}
	};
})(jQuery);
