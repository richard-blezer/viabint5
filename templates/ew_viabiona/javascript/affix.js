/**************************************
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 **************************************/

;
(function () {

    var backToTop, mainNav, mainNavWrap, prodInfo, prodInfoWrap, footer, content, cartButton;

    jQuery(window).load(function () {

        /**
         * Init back to top button
         */
        backToTop = jQuery("#back-to-top");
        if (backToTop.length != 0) {
            setTimeout(function () {
                backToTop.affix({
                    offset: calcOffsetBackToTop()
                });
            }, 100);
        }

        /**
         * Init fixed main navigation
         */
        if (typeof CONFIG_EW_VIABIONA_PLUGIN_FLOATINGNAVIGATION != 'undefined' &&
            CONFIG_EW_VIABIONA_PLUGIN_FLOATINGNAVIGATION === true) {

            mainNav = jQuery("#main-navigation");
            if (mainNav.length !== 0) {
                footer = jQuery("#footer");
                mainNav.wrap(jQuery("<div/>", {
                    "height": mainNav.outerHeight()
                }));
                mainNavWrap = mainNav.parent();
                setTimeout(function () {
                    mainNav.affix({
                        offset: calcOffsetMainNav()
                    });
                }, 100);
            }
        }

        /**
         * Init fixed product information sidebar
         */
        if (typeof CONFIG_EW_VIABIONA_PLUGIN_FLOATING != 'undefined' &&
            CONFIG_EW_VIABIONA_PLUGIN_FLOATING === true && !isAppleMobileDevice()) {

            prodInfo = jQuery("#product-informations-affix-container");
            if (prodInfo.length !== 0) {
                footer = jQuery("#footer");
                content = jQuery("#content");
                prodInfo.wrap(jQuery("<div/>", {
                    "height": prodInfo.outerHeight()
                }));
                prodInfoWrap = prodInfo.parent();
                cartButton = jQuery("#product-infos .add-to-cart");
                if (cartButton.length !== 0) {
                    jQuery(window).scroll(function () {
                        if (!affixedProdInfo.isCartButtonInViewport()) {
                            affixedProdInfo.showInfos();
                        }
                        if (affixedProdInfo.isCartButtonInViewport()) {
                            if (prodInfo.hasClass("show-infos")) {
                                affixedProdInfo.hideInfos();
                            }
                            prodInfo.removeClass('was-bottom');
                        }
                    });
                    setTimeout(function () {
                        if (!affixedProdInfo.isCartButtonInViewport()) {
                            affixedProdInfo.showInfos();
                        }
                        affixedProdInfo.setPosition();
                        prodInfo.affix({
                            offset: affixedProdInfo.calcOffset()
                        }).on('affixed-top.bs.affix', function () {
                            prodInfo.css('top', 'auto');
                        }).on('affixed.bs.affix', function () {
                            affixedProdInfo.setPosition();
                        }).on('affixed-bottom.bs.affix', function () {
                            prodInfo.css('top', 'auto')
                                .addClass('was-bottom');
                            if (!affixedProdInfo.isCartButtonInViewport()) {
                                affixedProdInfo.showInfos();
                            }
                        });
                    }, 100);
                }
            }
        }
    });

    jQuery(window).resize(function () {

        /**
         * Recalculate back to top button offset
         */
        if (typeof backToTop != 'undefined' &&
            typeof backToTop.data('bs.affix') != 'undefined') {

            setTimeout(function () {
                backToTop.data('bs.affix').options.offset = calcOffsetBackToTop();
            }, 100);
        }

        /**
         * Recalculate fixed main navigation offset
         */
        if (typeof mainNav != 'undefined' &&
            typeof mainNav.data('bs.affix') != 'undefined') {

            mainNavWrap.height(mainNav.outerHeight());
            setTimeout(function () {
                mainNav.data('bs.affix').options.offset = calcOffsetMainNav();
            }, 100);
        }

        /**
         * Recalculate fixed product information sidebar offset
         */
        if (typeof prodInfo != 'undefined' &&
            typeof prodInfo.data('bs.affix') != 'undefined') {

            prodInfoWrap.height(prodInfo.outerHeight());
            setTimeout(function () {
                affixedProdInfo.setPosition();
                prodInfo.data('bs.affix').options.offset = affixedProdInfo.calcOffset();
            }, 100);
        }

    });

    /**
     * Calculate back to top button offset
     * @returns {{top: Function}}
     */
    var calcOffsetBackToTop = function () {
        return {
            top: function () {
                return this.top = jQuery(window).height() / 3;
            }
        };
    };

    /**
     * Calculate fixed main navigation offset
     * @returns {{top: Function, bottom: Function}}
     */
    var calcOffsetMainNav = function () {
        return {
            top: function () {
                return this.top = mainNavWrap.offset().top;
            }
        };
    };

    var affixedProdInfo = {

        /**
         * Calculate fixed product information sidebar offset
         * @returns {{top: Function, bottom: Function}}
         */
        calcOffset: function () {
            return {
                top: function () {
                    var o = prodInfoWrap.offset().top, mo = 0, c = 0;
                    if (typeof content != 'undefined') {
                        c = parseInt(content.css("margin-top"));
                    }
                    if (typeof mainNavWrap != 'undefined') {
                        mo = mainNavWrap.outerHeight();
                    }
                    return this.top = o - mo - c;
                },
                bottom: function () {
                    var c = 0;
                    if (typeof content != 'undefined') {
                        c = parseInt(content.css("margin-bottom"));
                    }
                    return this.bottom = footer.outerHeight() + c;
                }
            };
        },

        /**
         * Calculate top position
         */
        setPosition: function () {
            var mo = 0, c = 0;
            if (typeof CONFIG_EW_VIABIONA_PLUGIN_FLOATINGNAVIGATION != 'undefined' &&
                CONFIG_EW_VIABIONA_PLUGIN_FLOATINGNAVIGATION === true) {

                mo = mainNav.outerHeight();
            }
            if (typeof content != 'undefined') {
                c = parseInt(content.css("margin-top"));
            }
            prodInfo.css('top', mo + c);
        },

        /**
         * Check if cart button is in viewport
         */
        isCartButtonInViewport: function () {
            if (!cartButton.length)
                return false;

            return (cartButton.offset().top > jQuery(document).scrollTop());
        },

        /**
         * Hide product infos
         * @param speed
         */
        hideInfos: function (speed) {
            var h = prodInfo.removeClass('show-infos')
                .find(".hidden-affix").hide().removeClass('hidden-affix');
            prodInfo.find(".visible-affix")
                .removeClass('visible-affix animated fadeInUp')
                .stop().fadeOut(speed, function () {
                    jQuery(this).addClass('visible-affix').removeAttr('style');
                    h.removeAttr('style').addClass('hidden-affix animated fadeInUp');
                });
        },

        /**
         * Show product infos
         */
        showInfos: function () {
            prodInfo.addClass('show-infos')
                .find(".hidden-affix").removeClass('animated fadeInUp');
            prodInfo.not(".was-bottom").find(".visible-affix")
                .addClass('animated fadeInUp');
        }
    };
})();