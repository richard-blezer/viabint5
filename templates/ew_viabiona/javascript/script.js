/**************************************
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 **************************************/

//
// For each shop client you could create its own javascript file
// script_[shop_id].js
// @example for shop client id 1 name it 'script_1.js'
//

/*******************************************************************************
 * ON DOCUMENT READY
 */
jQuery(document).ready(function () {

    /**
     * GLOBAL CONFIGURATION
     */
    PNotify.prototype.options.delay = 2000;
    var LaddaTimeout = 10000;

    var isSafari = 0;
    if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
        isSafari = 1;
    }

    /**
     * MS OPTIONS
     */
    var msOptionsSelector = jQuery("#ms-options");
    if (msOptionsSelector.length != 0) {
        msOptionsSelector.find(".default_option").addClass('preloader');
        msOptionsSelector.find("select").addClass('form-control');
    }

    /**
     * ew_2to1_coupon modal trigger
     */
    (function () {
        var modal = jQuery("#ew_2to1_coupon");

        if (modal.length) {
            var button = jQuery("#ew_2to1_coupon_button").clone();

            //
            // open modal
            //
            button.removeClass('hidden');
            jQuery(".trigger_ew_2to1_coupon_modal")
                .before(button)
                .removeClass('preloader ladda-button btn-success')
                .addClass('btn-warning')
                .click(function (e) {
                    e.preventDefault();
                    modal.modal('show');
                });

            //
            // form submit
            //
            modal.find(".section:not(.self-submitted)")
                .addClass('cursor-pointer')
                .click(function (e) {
                    var self = jQuery(this),
                        form = self.closest("form");

                    if (form.length) {
                        var submitButton = form.find("[type='submit']");

                        if (submitButton.length) {
                            e.preventDefault();

                            self
                                .addClass('self-submitted');
                            Ladda.create(submitButton.get(0))
                                .start();
                            modal.find(".section:not(.self-submitted)")
                                .animate({
                                   opacity: .2
                                }, 333);
                            form
                                .submit();
                        }
                    }
                });
        }
    })();

    /**
     * BOOTSTRAP SELECT
     * @see http://silviomoreto.github.io/bootstrap-select/
     */
    if (!MSIE || MSIE > 8) {
        jQuery("select").addClass('show-menu-arrow');
        var bsSelctor = "select:not(#countries select):not(#default_address_customers_federal_state_code):not(#customers_federal_state_code)";
        jQuery(bsSelctor).selectpicker();
        if (isMobileDevice()) {
            jQuery(bsSelctor).selectpicker('mobile');
        }

        //click tracker
        jQuery(".bootstrap-select").click(function () {
            jQuery(this).prev("select").addClass('clicked');
        });
    }

    /**
     * TEASER CAROUSEL
     * @see http://getbootstrap.com/javascript/#carousel
     */
    var teaserSelector = jQuery("#ew_viabiona_teaser");
    var teaserInterval = 7000;
    var count = teaserSelector.find('.item').length;

    // random slide
    if (typeof CONFIG_EW_VIABIONA_PLUGIN_RANDOM_TEASER != 'undefined' &&
        CONFIG_EW_VIABIONA_PLUGIN_RANDOM_TEASER === true) {

        var randomSlide = Math.floor(Math.random() * count);
        teaserSelector.carousel(randomSlide);
    }

    //set interval
    teaserSelector.carousel({
        interval: teaserInterval
    });

    //equalize height of nav items and start loader animation
    if (count >= 2) {
        teaserSelector.find(".loader .bar").stop().css('width', '0%').animate({
            width: '100%'
        }, teaserInterval);
    }

    //fires immediately when the slide instance method is invoked
    teaserSelector.on('slide.bs.carousel', function () {
        teaserSelector.find(".loader .bar").stop().css('width', '0%').animate({
            width: '100%'
        }, teaserInterval);
    });

    /**
     * SWIPE FUNCTIONALITY FOR BS CAROUSEL
     */
    if (!MSIE || MSIE > 8) {
        jQuery(".carousel").hammer().on('swiperight', function () {
            jQuery(this).carousel('prev');
        }).on('swipeleft', function () {
            jQuery(this).carousel('next');
        });
    }

    /**
     * PRODUCT CAROUSEL
     * No auto carousel
     * @see http://getbootstrap.com/javascript/#carousel
     */
    var productCarouselSelector = jQuery(".productCarousel");
    productCarouselSelector.carousel({
        interval: false
    });

    /**
     * PRINT DOCKED SIDENAVIGATION OBJECTS
     */
    if (typeof CONFIG_EW_VIABIONA_PLUGIN_SIDEBUTTONS != 'undefined' &&
        CONFIG_EW_VIABIONA_PLUGIN_SIDEBUTTONS === true) {

        var floatingBox = jQuery("#floating-box-container");
        var sideNavigationArea = floatingBox.find(".custom-floating-boxes");
        if (sideNavigationArea.length !== 0) {
            jQuery("[data-orientation-nav-url][data-orientation-nav-classes][data-orientation-nav-icon][data-orientation-nav-label]")
                .each(function (i) {
                    sideNavigationArea.append('<a href="' + jQuery(this).data('orientation-nav-url') + '" title="' + jQuery(this).data('orientation-nav-label') + '" class="obj-' + i + ' ' + jQuery(this).data('orientation-nav-classes') + '"><span class="floating-box">' + jQuery(this).data('orientation-nav-icon') + '</span></a>');
                });
            floatingBox.css({"margin-top": -(floatingBox.outerHeight() / 2) + "px"});
        }
    }

    /**
     * ANCHOR ANIMATION
     * back to top
     */
    jQuery(".backtotop").click(function(e){
        e.preventDefault();
        jQuery('html,body').animate({scrollTop:0}, 500);
    });

    /**
     * ANCHOR ANIMATION
     * slide to id by hash
     */
    jQuery("a.move").bind("click", function(e) {
        z = jQuery(this).get(0).hash;
        o = jQuery(z);
        if (o.length != 0) {
            e.preventDefault();
            jQuery('html,body').animate({
                scrollTop : o.offset().top - 80
            }, 500);
        }
    });

    /**
     * LOGIN PASSWORD TOGGLE
     */
    jQuery("#account-button").click(function () {
        jQuery("#guest-account").show();
    });
    jQuery("#guest-button").click(function () {
        jQuery("#guest-account").hide();
    });

    /**
     * FORM REQUIRED MARK
     */
    jQuery("label:contains('\*')").html(function (_, html) {
        return html.replace(/(\*)/g, '<span class="required">$1</span>');
    });

    /**
     * CHECK SEARCH INPUT
     */
    var searchFormSelector = jQuery(".search-box-form");
    searchFormSelector.find(".submit-button").click(function (e) {
        if (jQuery.trim(searchFormSelector.find(".keywords").val()) == '') {
            searchFormSelector.find(".keywords").focus();
            e.preventDefault();
            Ladda.stopAll();
        }
    });

    /**
     * CHECKOUT
     */
    if (jQuery("#checkout").length) {
        jQuery("#checkout .progress .progress-bar").tooltip({
            placement: 'bottom'
        });
        jQuery("#checkout .list-group .list-group-item input[type=radio]:checked").closest(".list-group-item").addClass('active');
        jQuery("#checkout .list-group .list-group-item").click(function () {
            jQuery("#checkout .list-group .list-group-item .shipping-desc, #checkout .list-group .list-group-item .payment-desc").hide();
            jQuery("#checkout .list-group .list-group-item").removeClass('active').find("input[type=radio]").removeAttr('checked');
            jQuery(this).addClass('active').find("input[type=radio]").attr('checked', 'checked');
            jQuery(this).find(".shipping-desc, .payment-desc").show();
        });
    }

    /**
     * REVIEWS
     */
    jQuery(".product-reviews").not('popover-trigger').tooltip({
        viewport: '#content',
        placement: 'right'
    });

    /**
     * TOOLTIPS
     */
    jQuery("#header .header-info [title], .mnf a, .dummy a").tooltip({
        placement: 'bottom'
    });
    jQuery("#floating-box-container [title]").tooltip({
        placement: 'left'
    });
    jQuery(".vorteile-box img").tooltip({
        placement: 'bottom',
        title: function () {
            var text;
            return (text = jQuery(this).attr('alt').trim()) ? text : null;
        }
    });

    /**
     * PRODUCT DETAIL PAGE CONTENT
     */
    var productContentSelector = jQuery("#product #pcontent");
    if (productContentSelector.length != 0) {
        var productContentSecondarySelector = productContentSelector.find(".secondary");
        if (jQuery.trim(productContentSecondarySelector.text()) == '') {
            productContentSelector.removeAttr('class');
            productContentSecondarySelector.hide();
            productContentSelector.find(".primary").removeAttr('class').addClass('primary');
        }
    }

    /**
     * SWITCHES
     */
    var visibleSwitchItems = 1;
    $(".switch-area").each(function (area) {
        var thisSwitchArea = $(this);
        thisSwitchArea.addClass('switch-area-' + area + ' switch-items-show-' + visibleSwitchItems);
        if (!thisSwitchArea.hasClass('switch-disabled')) {
            var thisSwitchChildren = thisSwitchArea.find(".switch-items").children();
            if (thisSwitchChildren.length > visibleSwitchItems) {
                thisSwitchArea.addClass('switch-enabled');
                thisSwitchChildren.each(function (item) {
                    var thisSwitchItem = $(this);
                    if (item >= visibleSwitchItems) {
                        thisSwitchItem.addClass('switch-default-hidden switch-item switch-item-' + item);
                    } else {
                        thisSwitchItem.addClass('switch-default-visible switch-item switch-item-' + item);
                    }
                });
                thisSwitchChildren.siblings(".switch-default-hidden").wrapAll('<div class="switch-toggle" style="display:none;"></div>');
                thisSwitchArea.find(".switch-button").click(function () {
                    thisSwitchArea.toggleClass('switch-bounce');
                    thisSwitchArea.find(".switch-toggle").slideToggle('fast', function () {
                        thisSwitchArea.toggleClass('switch-bounce-finish');
                    });
                });
            } else {
                thisSwitchArea.addClass('switch-disabled');
            }
        } else {
            thisSwitchArea.addClass('switch-disabled-by-class');
        }
    });

    /**
     * VISUAL FORM VALIDATION
     *
     * Visual validation for required fields
     * @author 8works, Jens Albert
     * @version 1.0
     */
    jQuery(".form-group label:contains('*'), .form-group .label:contains('*'), .form-group .form-required").addClass('control-label').closest(".form-group").find("input:not([type=password]), select, textarea").addClass('form-control').on('blur', function () {
        if (jQuery(this).val().length == 0) {
            jQuery(this).closest(".form-group").removeClass('has-success').addClass('has-error').find(".form-control").addClass('animated shake');
        } else {
            jQuery(this).closest(".form-group").removeClass('has-error').addClass('has-success').find(".form-control").removeClass('animated shake');
        }
    });

    /**
     * checkout-shipping
     * hide selection when only one option is available
     */
    if (jQuery('[name="selected_shipping"]').length < 2 &&
        jQuery('[name="selected_shipping"]:checked').length) {
        jQuery('body').addClass('only-one-shipping');
    }

    /**
     * TEXT COUNTER
     */
    jQuery(".form-counter [maxlength]").each(function (i) {
        var field = this;
        var maxlength = parseInt(jQuery(field).attr('maxlength'));
        if (maxlength && maxlength > 0) {
            jQuery(field).parent().append('<p class="help-block js-input-count count-' + i + '"><span class="txt">' + TEXT_EW_VIABIONA_STILL + ' </span><span class="nr label label-default">' + maxlength + '</span><span class="txt"> ' + TEXT_EW_VIABIONA_CHARACTERS_AVAILABLE + '</span></p>');
            var thisCounter = jQuery(field).parent().find(".js-input-count");

            thisCounter.find(".nr").textCounter({
                target: field,
                stopAtLimit: true,
                count: maxlength,
                alertAt: Math.floor(maxlength / 100 * 30), //30%
                warnAt: Math.floor(maxlength / 100 * 10) //10%
            });

            thisCounter.hide();
            jQuery(field).focus(function () {
                thisCounter.show();
            });
        }
    });

    /**
     * IMAGE RESPONSIVE HELPER
     */
    jQuery("img.img-responsive").removeAttr('width').removeAttr('height');

    /**
     * VERTICAL HELPER LINK HELPER
     * Removes link html whitespaces because of wrong underline by onmouseover
     */
    jQuery("a.vertical-helper").each(function () {
        jQuery(this).html(jQuery.trim(jQuery(this).html()));
    });

    /**
     * !Safari Browser
     */
    if (isSafari == 0) {
        jQuery(".image-link, .usp-container .section").not('no-ripple')
            .addClass('ripple');
        jQuery('.panel-shadow').addClass('transition500');
    }

    /**
     * Set ripple class
     */

    jQuery(".btn-default, .table-hover td a").not('no-ripple')
        .addClass('ripple');

    /**
     * RIPPLE click animation
     * Material Design Hover & Click Effects
     */
    jQuery(".ripple").click(function (e) {
        if (jQuery(this).find(".ink-ripple").length === 0) {
            jQuery(this).prepend('<span class="ink-ripple"></span>');
        }
        var ink = jQuery(this).find(".ink-ripple").removeClass("animate-ripple");
        if (!ink.height() && !ink.width()) {
            var d = Math.max(jQuery(this).outerWidth(), jQuery(this).outerHeight());
            ink.css({height: d, width: d});
        }
        var x = e.pageX - jQuery(this).offset().left - ink.width() / 2, y = e.pageY - jQuery(this).offset().top - ink.height() / 2;
        ink.css({top: y + 'px', left: x + 'px'}).addClass("animate-ripple");
    });

    /**
     * Mobile Search
     *
     * @param element
     * @returns {Array}
     */
    jQuery(".open-mobile-search").click(function () {
        jQuery("#mobile-search").addClass('open animated fadeIn');
    });

    jQuery("#mobile-search .close").click(function () {
        jQuery('#mobile-search').removeClass('open animated fadeIn');
    });

    /**
     * Popovers
     */
    jQuery(".popover-trigger").popover({trigger: "hover", container: 'body'});

    if (typeof CONFIG_EW_VIABIONA_PLUGIN_SOCIALSHARE != 'undefined' && CONFIG_EW_VIABIONA_PLUGIN_SOCIALSHARE === true) {
        jQuery.fn.socialSharePrivacy.settings.order = ['facebook', 'gplus', 'twitter'];
        jQuery.fn.socialSharePrivacy.settings.path_prefix = CONFIG_EW_VIABIONA_PLUGIN_URL + '/assets/components/SocialSharePrivacy/';
        jQuery.fn.socialSharePrivacy.settings.css_path = "";
        jQuery.fn.socialSharePrivacy.settings.txt_help = "";
        jQuery(document).ready(function () {
            jQuery('.share').socialSharePrivacy();
        });
    }

    /**
     * Mega Menu
     */
    if (typeof CONFIG_EW_VIABIONA_PLUGIN_MEGANAV != 'undefined' && CONFIG_EW_VIABIONA_PLUGIN_MEGANAV === true) {

        var megamenu = jQuery(".mega-menu");
        var megamenuVisible = 0;
        var megamenuCover = jQuery(".mega-menu-cover");
        var megamenuTrigger = jQuery(".mega-menu-trigger");
        var megamenuScrolls = 0;
        var mainNavigation = jQuery('#main-navigation');

        megamenuTrigger.click(function () {

            if (jQuery(this).hasClass("mega-visible")) {
                hideMegaMenu();
            } else {
                if (megamenuVisible == 0 || megamenuVisible == 1) {
                	if (!mainNavigation.hasClass('affix')) {
                		megamenuScrolls = 1;
                		jQuery("html, body").stop().animate({ scrollTop: mainNavigation.offset().top - 1 }, 200 , function() {
                			setTimeout(function() {
                				megamenuScrolls = 0;
                			}, 500);
                		});
                	}
                	megamenuVisible = 1;
                    megamenuTrigger.removeClass("mega-visible");
                    jQuery(this).addClass("mega-visible");
                    megamenu
                        .html("")
                        .html(jQuery("#" + jQuery(this).data("megamenu")).html())
                        .removeClass("hidden");
                    jQuery(megamenu).show();
                    megamenu.css({'top': mainNavigation.offset().top + mainNavigation.height()});
                    megamenuCover.stop().fadeIn(300);
                }
            }
            jQuery(".mega-close").click(function () {
                hideMegaMenu();
            });
            jQuery(".mega-menu-cover").click(function () {
                hideMegaMenu();
            });
        });

        jQuery(window).on("scroll resize", function () {
			hideMegaMenu();
        });

        var hideMegaMenu = function () {
            if (megamenuVisible == 1 && megamenuScrolls == 0) {
                megamenuVisible = 2;
                megamenuTrigger.removeClass("mega-visible");
                jQuery(megamenu).hide();
                megamenuCover.stop().fadeOut(300, function () {
                    megamenu
                        .html("")
                        .addClass("hidden");
                    megamenuVisible = 0;
                });
            }
        };
    }

    // Fancybox HTML5 fix
    if(typeof $.fancybox == 'function') {
    	jQuery(".fancybox").fancybox({'type':'image'});
    }

    /**
     * Removes hover css on apple devices
     * because of ipad click issues
     */
    if (isAppleMobileDevice()) {
        jQuery(".hvr-float")
            .removeClass('hvr-float')
            .addClass('hvr-float-removed-from-js');
    }

    /**
     * Cache watcher
     */
    if (typeof EW_VIABIONA_PLUGIN_AJAX_CACHE_WATCHER_LINK != 'undefined') {
        jQuery.get(EW_VIABIONA_PLUGIN_AJAX_CACHE_WATCHER_LINK, function () {
            if (jQuery("#debugbar").length) {
                console.log('Cache watcher executed');
            }
        });
    }

    /**
     * Cookie Alert
     */
    if (!esseKeks('viabiona_shop_cookie_alert')) {
        jQuery('.cookie-alert').show();
    }

    /**
     * IE 9- placeholder fix
     */
    if (MSIE && MSIE <= 9) {
        jQuery("[placeholder]").each(function () {
            var form = jQuery(this),
                placeholder = jQuery.trim(form.attr('placeholder')),
                value = jQuery.trim(form.attr('value'));
            if (value == '' && placeholder != '') {
                form.attr('value', placeholder);
                form.focus(function () {
                    var form = jQuery(this);
                    if (jQuery.trim(form.attr('value')) == placeholder) {
                        form.attr('value', '');
                    }
                });
            }
        });
    }

    /**
     * Ajax cart form
     */
    var cartBox = jQuery(".ajax-box-cart");
    var mobileCartBox = jQuery(".ajax-box-cart-mobile");

    if (cartBox.length && jQuery("form").length) {
        var cartForm = jQuery("input[name='action'][value='add_product']").closest("form");

        if (cartForm.length) {
            var cartBoxContent = cartBox.html();

            cartBox.addClass('ajax-enabled');
            cartForm.find(":submit:not(.no-ajax)").removeClass('preloader').removeClass('ladda-button');
            cartForm.submit(function (e) {
                var self = jQuery(this),
                    btn = self.find(":submit"),
                    success = false;

                if (btn.hasClass('btn-success') || btn.hasClass('no-ajax')) {
                    return btn.blur();
                }

                if (btn.length) {
                    btn.addClass("ladda-button")
                        .attr('data-style', 'zoom-in');
                    var status = Ladda.create(btn.get(0));
                }

                if (typeof status != 'undefined') {
                    status.start();
                }

                jQuery.ajax({
                    type: 'POST',
                    url: self.attr('action'),
                    data: self.serialize(),
                    success: function(data) {
                        var newDom = jQuery(data),
                            cartBoxContentNew = newDom.find(cartBox.selector).html(),
                            mobileCartBoxContentNew = newDom.find(mobileCartBox.selector).html(),
                            alerts = newDom.find("#cart .alert");

                        if (cartBoxContent != cartBoxContentNew) {
                            success = true;
                            cartBox.html(cartBoxContentNew).addClass('ajax-refreshed');
                            mobileCartBox.html(mobileCartBoxContentNew).addClass('ajax-refreshed');
                        }

                        if (alerts.length) {
                            alerts.each(function () {
                                var alert = jQuery(this),
                                    message = jQuery.trim(alert.find(".item:last").text()),
                                    type = 'info',
                                    icon = 'glyphicon glyphicon-info-sign',
                                    autohide = true;

                                if (message != '') {
                                    if (alert.hasClass('alert-danger')) {
                                        type = 'danger';
                                        icon = 'glyphicon glyphicon-warning-sign';
                                        autohide = false;
                                        success = false;
                                    } else if (alert.hasClass('alert-warning')) {
                                        type = 'warning';
                                        icon = 'glyphicon glyphicon-warning-sign';
                                        autohide = false;
                                        success = false;
                                    } else if (alert.hasClass('alert-success')) {
                                        type = 'success';
                                        icon = 'glyphicon glyphicon-ok';
                                        success = true;
                                    }
                                    new PNotify({
                                        text: message,
                                        shadow: false,
                                        type: type,
                                        hide: autohide,
                                        styling: 'bootstrap3',
                                        icon: icon,
                                        opacity: .9,
                                        buttons: {
                                            sticker: false,
                                            closer_hover: autohide
                                        },
                                        animate: {
                                            animate: true,
                                            in_class: 'bounceIn',
                                            out_class: 'fadeOutRight'
                                        }
                                    });
                                } else {
                                    success = false;
                                }
                            });
                        }
                    },
                    complete: function () {
                        if (typeof status != 'undefined') {
                            status.stop();
                        }
                        if (success && btn.length) {
                            btn.blur()
                                .removeClass('btn-cart')
                                .addClass('btn-success');

                            setTimeout(function () {
                                btn.removeClass('btn-success')
                                    .addClass('btn-cart');
                            }, (typeof PNotify.prototype.options.delay != 'undefined') ? PNotify.prototype.options.delay : LaddaTimeout);
                        }
                    }
                });
                e.preventDefault();
            }).addClass('ajax-enabled');
        }
    }

    /**
     * BUTTONS LOADING ANIMATION
     * @see http://msurguy.github.io/ladda-bootstrap/
     */
    if (!MSIE || MSIE > 7) {
        var LaddaElements = ".ladda-button, .preloader";
        var LaddaElementsSelector = jQuery(LaddaElements);
        if (LaddaElementsSelector.length != 0) {
            LaddaElementsSelector.each(function () {
                jQuery(this).addClass("ladda-button").attr('data-style', 'zoom-in');
                if (jQuery(this).hasClass('btn-default')) {
                    jQuery(this).attr('data-spinner-color', '#000');
                } else {
                    jQuery(this).attr('data-spinner-color', '#fff');
                }
                jQuery(this).attr('data-style', 'slide-up');
            });
            Ladda.bind(LaddaElements, { timeout: LaddaTimeout });
        }
    }

});

/**
 * EQUALIZE LISTING HEIGHTS
 * apply your matchHeight on DOM ready (they will be automatically re-applied on load or resize)
 * @see http://brm.io/jquery-match-height/
 */
var equalizeListingHeights = function (rowClass) {
    if ((!MSIE || MSIE > 8) && typeof window.opera == 'undefined') {
        jQuery(function () {
            var listingAutoPanels, listingNoPanels;

            jQuery.fn.matchHeight._throttle = 100;
            jQuery.fn.matchHeight._resizeTimeOut = 100;

            // with panels
            if (typeof rowClass === 'undefined') {
                listingAutoPanels = jQuery(".listing:not(.equalize-no-panels, .equalize-nothing)");
            } else {
                listingAutoPanels = jQuery(".listing:not(.equalize-no-panels, .equalize-nothing)" + rowClass);
            }
            if (listingAutoPanels.length != 0) {
                listingAutoPanels.each(function () {
                    jQuery(this).find(".image-link").matchHeight();
                    jQuery(this).find(".desc").matchHeight();
                    jQuery(this).find(".section").matchHeight();
                });
            }

            // no panels
            if (typeof rowClass === 'undefined') {
                listingNoPanels = jQuery(".listing.equalize-no-panels:not(.equalize-nothing)");
            } else {
                listingNoPanels = jQuery(".listing.equalize-no-panels:not(.equalize-nothing)" + rowClass);
            }
            if (listingNoPanels.length != 0) {
                listingNoPanels.each(function () {
                    jQuery(this).find(".image-link").matchHeight(false);
                    jQuery(this).find(".desc").matchHeight(false);
                    jQuery(this).find(".section").matchHeight(false);
                });
            }
        });
    }
};

(function () {
    equalizeListingHeights();

    //checkout progress bar
    if ((!MSIE || MSIE > 8) && typeof window.opera == 'undefined') {
        jQuery(function () {
            jQuery("#checkout .progress-bar").matchHeight(false);
        });
    }
})();

/**
 * Scroll animations with wow.js
 */
if (typeof CONFIG_EW_VIABIONA_PLUGIN_ANIMATIONS != 'undefined' &&
    CONFIG_EW_VIABIONA_PLUGIN_ANIMATIONS === true) {
    new WOW().init();
}

/**
 * Slide to Element
 */
function slideToElement(element) {
    jQuery(document).ready(function () {
        jQuery('html, body').stop().animate({scrollTop: jQuery(element).offset().top - jQuery(element).height()}, 600);
    });
}

/**
 * Spinner
 */
jQuery(".jq-spinner")
    .spinner('delay', 200) //delay in ms
    .spinner('changed', function (e, newVal, oldVal) {
        //trigger lazed, depend on delay option.
    })
    .spinner('changing', function (e, newVal, oldVal) {
        //trigger immediately
    });

/**
 * Executed before ew_more_article ajax call
 *
 * @param args
 */
function ew_more_article_before_load(args) {
    if (typeof args != 'object')
        return;

    // add custom loader indicator
    jQuery(".navigation-pages .btn-group").append('<div class="btn btn-info loader-status"><i class="fa fa-spin fa-cog"></i></div>');
    if (typeof args.selectorRange !== 'undefined') {
        jQuery(args.selectorRange).append('<div class="loader-status col-xs-12"><div class="progress"><div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"><i class="fa fa-spin fa-cog"></i> Loading</div></div></div>');
    }
}

/**
 * Executed after ew_more_article ajax call
 *
 * @param args
 */
function ew_more_article_after_load(args) {
    if (typeof args != 'object')
        return;

    // remove custom loader indicator
    jQuery(".navigation-pages .btn-group .loader-status").remove();
    if (typeof args.selectorRange !== 'undefined') {
        jQuery(args.selectorRange).find(".loader-status").remove();
    }
}

/**
 * Executed to modify ew_more_article content before append
 *
 * @param args
 * @returns {string}
 */
function ew_more_article_before_append(args) {
    if (typeof args != 'object')
        return '';

    return '<div class="ew-more-article-count-' + args.loadingCount + '">' + args.content + '</div>';
}

/**
 * Executed after ew_more_article append
 * @param args
 */
function ew_more_article_after_append(args) {
    if (typeof args != 'object')
        return;

    equalizeListingHeights(' .ew-more-article-count-' + args.loadingCount);
}

