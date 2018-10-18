/**
* jquery.matchHeight.js master
* http://brm.io/jquery-match-height/
* License: MIT
*/

;(function($) {
    /*
    *  internal
    */

    var _previousResizeWidth = -1,
        _updateTimeout = -1,
        _resizeTimer = -1;

    /*
    *  _parse
    *  value parse utility function
    */

    var _parse = function(value) {
        // parse value and convert NaN to 0
        return parseFloat(value) || 0;
    };

    /*
    *  _rows
    *  utility function returns array of jQuery selections representing each row
    *  (as displayed after float wrapping applied by browser)
    */

    var _rows = function(elements) {
        var tolerance = 1,
            $elements = $(elements),
            lastTop = null,
            rows = [];

        // group elements by their top position
        $elements.each(function(){
            var $that = $(this),
                top = $that.offset().top - _parse($that.css('margin-top')),
                lastRow = rows.length > 0 ? rows[rows.length - 1] : null;

            if (lastRow === null) {
                // first item on the row, so just push it
                rows.push($that);
            } else {
                // if the row top is the same, add to the row group
                if (Math.floor(Math.abs(lastTop - top)) <= tolerance) {
                    rows[rows.length - 1] = lastRow.add($that);
                } else {
                    // otherwise start a new row group
                    rows.push($that);
                }
            }

            // keep track of the last row top
            lastTop = top;
        });

        return rows;
    };

    /*
    *  _parseOptions
    *  handle plugin options
    */

    var _parseOptions = function(options) {
        var opts = {
            byRow: true,
            property: 'height',
            target: null,
            remove: false
        };

        if (typeof options === 'object') {
            return $.extend(opts, options);
        }

        if (typeof options === 'boolean') {
            opts.byRow = options;
        } else if (options === 'remove') {
            opts.remove = true;
        }

        return opts;
    };

    /*
    *  matchHeight
    *  plugin definition
    */

    var matchHeight = $.fn.matchHeight = function(options) {
        var opts = _parseOptions(options);

        // handle remove
        if (opts.remove) {
            var that = this;

            // remove fixed height from all selected elements
            this.css(opts.property, '');

            // remove selected elements from all groups
            $.each(matchHeight._groups, function(key, group) {
                group.elements = group.elements.not(that);
            });

            // TODO: cleanup empty groups

            return this;
        }

        if (this.length <= 1 && !opts.target) {
            return this;
        }

        // keep track of this group so we can re-apply later on load and resize events
        matchHeight._groups.push({
            elements: this,
            options: opts
        });

        // match each element's height to the tallest element in the selection
        matchHeight._apply(this, opts);

        return this;
    };

    /*
    *  plugin global options
    */

    matchHeight._groups = [];
    matchHeight._throttle = 80;
    matchHeight._resizeTimeOut = 0;
    matchHeight._maintainScroll = false;
    matchHeight._beforeUpdate = null;
    matchHeight._afterUpdate = null;

    /*
    *  matchHeight._apply
    *  apply matchHeight to given elements
    */

    matchHeight._apply = function(elements, options) {
        var opts = _parseOptions(options),
            $elements = $(elements),
            rows = [$elements];

        // take note of scroll position
        var scrollTop = $(window).scrollTop(),
            htmlHeight = $('html').outerHeight(true);

        // get hidden parents
        var $hiddenParents = $elements.parents().filter(':hidden');

        // cache the original inline style
        $hiddenParents.each(function() {
            var $that = $(this);
            $that.data('style-cache', $that.attr('style'));
        });

        // temporarily must force hidden parents visible
        $hiddenParents.css('display', 'block');

        // get rows if using byRow, otherwise assume one row
        if (opts.byRow && !opts.target) {

            // must first force an arbitrary equal height so floating elements break evenly
            $elements.each(function() {
                var $that = $(this),
                    display = $that.css('display') === 'inline-block' ? 'inline-block' : 'block';

                // cache the original inline style
                $that.data('style-cache', $that.attr('style'));

                $that.css({
                    'display': display,
                    'padding-top': '0',
                    'padding-bottom': '0',
                    'margin-top': '0',
                    'margin-bottom': '0',
                    'border-top-width': '0',
                    'border-bottom-width': '0',
                    'height': '100px'
                });
            });

            // get the array of rows (based on element top position)
            rows = _rows($elements);

            // revert original inline styles
            $elements.each(function() {
                var $that = $(this);
                $that.attr('style', $that.data('style-cache') || '');
            });
        }

        $.each(rows, function(key, row) {
            var $row = $(row),
                targetHeight = 0;

            if (!opts.target) {
                // skip apply to rows with only one item
                if (opts.byRow && $row.length <= 1) {
                    $row.css(opts.property, '');
                    return;
                }

                // iterate the row and find the max height
                $row.each(function(){
                    var $that = $(this),
                        display = $that.css('display') === 'inline-block' ? 'inline-block' : 'block';

                    // ensure we get the correct actual height (and not a previously set height value)
                    var css = { 'display': display };
                    css[opts.property] = '';
                    $that.css(css);

                    // find the max height (including padding, but not margin)
                    if ($that.outerHeight(false) > targetHeight) {
                        targetHeight = $that.outerHeight(false);
                    }

                    // revert display block
                    $that.css('display', '');
                });
            } else {
                // if target set, use the height of the target element
                targetHeight = opts.target.outerHeight(false);
            }

            // iterate the row and apply the height to all elements
            $row.each(function(){
                var $that = $(this),
                    verticalPadding = 0;

                // don't apply to a target
                if (opts.target && $that.is(opts.target)) {
                    return;
                }

                // handle padding and border correctly (required when not using border-box)
                if ($that.css('box-sizing') !== 'border-box') {
                    verticalPadding += _parse($that.css('border-top-width')) + _parse($that.css('border-bottom-width'));
                    verticalPadding += _parse($that.css('padding-top')) + _parse($that.css('padding-bottom'));
                }

                // set the height (accounting for padding and border)
                $that.css(opts.property, targetHeight - verticalPadding);
            });
        });

        // revert hidden parents
        $hiddenParents.each(function() {
            var $that = $(this);
            $that.attr('style', $that.data('style-cache') || null);
        });

        // restore scroll position if enabled
        if (matchHeight._maintainScroll) {
            $(window).scrollTop((scrollTop / htmlHeight) * $('html').outerHeight(true));
        }

        return this;
    };

    /*
    *  matchHeight._applyDataApi
    *  applies matchHeight to all elements with a data-match-height attribute
    */

    matchHeight._applyDataApi = function() {
        var groups = {};

        // generate groups by their groupId set by elements using data-match-height
        $('[data-match-height], [data-mh]').each(function() {
            var $this = $(this),
                groupId = $this.attr('data-mh') || $this.attr('data-match-height');

            if (groupId in groups) {
                groups[groupId] = groups[groupId].add($this);
            } else {
                groups[groupId] = $this;
            }
        });

        // apply matchHeight to each group
        $.each(groups, function() {
            this.matchHeight(true);
        });
    };

    /*
    *  matchHeight._update
    *  updates matchHeight on all current groups with their correct options
    */

    var _update = function(event) {
        if (matchHeight._beforeUpdate) {
            matchHeight._beforeUpdate(event, matchHeight._groups);
        }

        $.each(matchHeight._groups, function() {
            matchHeight._apply(this.elements, this.options);
        });

        if (matchHeight._afterUpdate) {
            matchHeight._afterUpdate(event, matchHeight._groups);
        }
    };

    matchHeight._update = function(throttle, event) {
        // prevent update if fired from a resize event
        // where the viewport width hasn't actually changed
        // fixes an event looping bug in IE8
        if (event && event.type === 'resize') {
            var windowWidth = $(window).width();
            if (windowWidth === _previousResizeWidth) {
                return;
            }
            _previousResizeWidth = windowWidth;
        }
        
        // clear the timeout when matchHeight._resizeTimeOut is not reached
        if (_resizeTimer !== -1) {
            clearTimeout(_resizeTimer);
        }
        
        // if the matchHeight._resizeTimeOut is defined correctly (~50ms), 
        // the matchHeight._update() will run after resizing, 
        // not while resizing
        _resizeTimer = setTimeout(function() {
            // throttle updates
            if (!throttle) {
                _update(event);
            } else if (_updateTimeout === -1) {
                _updateTimeout = setTimeout(function() {
                    _update(event);
                    _updateTimeout = -1;
                }, matchHeight._throttle);
            }
        }, matchHeight._resizeTimeOut);
    };

    /*
    *  bind events
    */

    // apply on DOM ready event
    $(matchHeight._applyDataApi);

    // update heights on load and resize events
    $(window).bind('load', function(event) {
        matchHeight._update(false, event);
    });

    // throttled update heights on resize events
    $(window).bind('resize orientationchange', function(event) {
        matchHeight._update(true, event);
    });

})(jQuery);

/**
* jquery.matchHeight-min.js master
* http://brm.io/jquery-match-height/
* License: MIT
*/
!function(t){var e=-1,i=-1,a=-1,o=function(t){return parseFloat(t)||0},n=function(e){var i=1,a=t(e),n=null,r=[];return a.each(function(){var e=t(this),a=e.offset().top-o(e.css("margin-top")),s=r.length>0?r[r.length-1]:null;null===s?r.push(e):Math.floor(Math.abs(n-a))<=i?r[r.length-1]=s.add(e):r.push(e),n=a}),r},r=function(e){var i={byRow:!0,property:"height",target:null,remove:!1};return"object"==typeof e?t.extend(i,e):("boolean"==typeof e?i.byRow=e:"remove"===e&&(i.remove=!0),i)},s=t.fn.matchHeight=function(e){var i=r(e);if(i.remove){var a=this;return this.css(i.property,""),t.each(s._groups,function(t,e){e.elements=e.elements.not(a)}),this}return this.length<=1&&!i.target?this:(s._groups.push({elements:this,options:i}),s._apply(this,i),this)};s._groups=[],s._throttle=80,s._resizeTimeOut=0,s._maintainScroll=!1,s._beforeUpdate=null,s._afterUpdate=null,s._apply=function(e,i){var a=r(i),c=t(e),h=[c],l=t(window).scrollTop(),p=t("html").outerHeight(!0),u=c.parents().filter(":hidden");return u.each(function(){var e=t(this);e.data("style-cache",e.attr("style"))}),u.css("display","block"),a.byRow&&!a.target&&(c.each(function(){var e=t(this),i="inline-block"===e.css("display")?"inline-block":"block";e.data("style-cache",e.attr("style")),e.css({display:i,"padding-top":"0","padding-bottom":"0","margin-top":"0","margin-bottom":"0","border-top-width":"0","border-bottom-width":"0",height:"100px"})}),h=n(c),c.each(function(){var e=t(this);e.attr("style",e.data("style-cache")||"")})),t.each(h,function(e,i){var n=t(i),r=0;if(a.target)r=a.target.outerHeight(!1);else{if(a.byRow&&n.length<=1)return void n.css(a.property,"");n.each(function(){var e=t(this),i="inline-block"===e.css("display")?"inline-block":"block",o={display:i};o[a.property]="",e.css(o),e.outerHeight(!1)>r&&(r=e.outerHeight(!1)),e.css("display","")})}n.each(function(){var e=t(this),i=0;a.target&&e.is(a.target)||("border-box"!==e.css("box-sizing")&&(i+=o(e.css("border-top-width"))+o(e.css("border-bottom-width")),i+=o(e.css("padding-top"))+o(e.css("padding-bottom"))),e.css(a.property,r-i))})}),u.each(function(){var e=t(this);e.attr("style",e.data("style-cache")||null)}),s._maintainScroll&&t(window).scrollTop(l/p*t("html").outerHeight(!0)),this},s._applyDataApi=function(){var e={};t("[data-match-height], [data-mh]").each(function(){var i=t(this),a=i.attr("data-mh")||i.attr("data-match-height");e[a]=a in e?e[a].add(i):i}),t.each(e,function(){this.matchHeight(!0)})};var c=function(e){s._beforeUpdate&&s._beforeUpdate(e,s._groups),t.each(s._groups,function(){s._apply(this.elements,this.options)}),s._afterUpdate&&s._afterUpdate(e,s._groups)};s._update=function(o,n){if(n&&"resize"===n.type){var r=t(window).width();if(r===e)return;e=r}-1!==a&&clearTimeout(a),a=setTimeout(function(){o?-1===i&&(i=setTimeout(function(){c(n),i=-1},s._throttle)):c(n)},s._resizeTimeOut)},t(s._applyDataApi),t(window).bind("load",function(t){s._update(!1,t)}),t(window).bind("resize orientationchange",function(t){s._update(!0,t)})}(jQuery);