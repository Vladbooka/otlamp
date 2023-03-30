/* global get_initialised_dock */
/* global theme_opentechnology_handle_resize */
define(['jquery'], function($){
    return {
        'nextMarginTop': null,
        'stickMe': function(elToStick, top, zindex) {
            var STICKY = this;

            if (!elToStick.is(":visible"))
            {
                return top;
            }

            elToStick.addClass('ot-sticky').addClass('moodle-has-zindex')
                .css('top', top)
                .css('z-index', zindex);
            var newHeight = top + elToStick.outerHeight();

            if (elToStick.attr('id') == 'dock_bg' || elToStick.find('#dock_bg').length > 0)
            {
                get_initialised_dock().then(function(dock){
                    STICKY.stickMe($(dock), $('#dock_bg').first().offset().top - $(document).scrollTop(), zindex);
                });
            }

            return newHeight;
        },
        'stickUpper': function(elToStick, stickyHeight, zindex){

            var STICKY = this;

            if (elToStick.parent().attr('id') != 'body-inner')
            {
                var parentResults = this.stickUpper(elToStick.parent(), stickyHeight, zindex);
                stickyHeight = parentResults.stickyHeight;
                zindex = parentResults.zindex;
            }

            $(elToStick.prevAll().not('script').not('style').get().reverse()).each(function() {
                if ($(this).find('.dock_bg_wrapper').length > 0)
                {
                    // если прикрепляется нечто обрачивающее dock_bg_wrapper, надо начать прикреплять изнутри с dock_bg_wrapper,
                    // чтобы не было проблем с zindex
                    var res = STICKY.stick($('#page-header .dock_bg_wrapper'), stickyHeight, zindex--, false);
                    stickyHeight = res.stickyHeight;
                    zindex = res.zindex-1;
                } else {
                    stickyHeight = STICKY.stickMe($(this), stickyHeight, zindex--);
                }
            });

            return {
                stickyHeight: stickyHeight,
                zindex: zindex
            };
        },
        'findNext': function(elToStick){

            var STICKY = this;

            var next = elToStick.next();
            if (next.length > 0)
            {
                return next;
            } else
            {
                var parent = elToStick.parent();
                if (parent.length > 0)
                {
                    return STICKY.findNext(parent);
                } else
                {
                    return false;
                }
            }
        },
        'stick': function(elToStick, top, zindex, marginnext){

            var STICKY = this;

            var top = top || 0;
            var zindex = zindex || 999;
            var marginnext = marginnext || true;

            var results = STICKY.stickUpper(elToStick, top, zindex);
            var stickyHeight = STICKY.stickMe(elToStick, results.stickyHeight, results.zindex);

            if (marginnext)
            {
                var next = STICKY.findNext(elToStick);
                if (next)
                {
                    if (STICKY.nextMarginTop === null)
                    {
                        STICKY.nextMarginTop = parseInt(next.css('margin-top'));
                    }
                    next.css('margin-top', STICKY.nextMarginTop + stickyHeight);
                }
            }

            return {
                stickyHeight: stickyHeight,
                zindex: results.zindex
            };
        },
        'init': function(selector) {
            var STICKY = this;
            STICKY.stick($(selector));
            $(window).resize(function(){
                STICKY.stick($(selector));
            });
            document.addEventListener('DockItemsChanged', function (){
                STICKY.stick($(selector));
            }, false);
            $(document).scroll(function(){
                $('body').toggleClass('ot-sticky-scrolled', ($(document).scrollTop() > 0));
            });
            $('body').addClass('ot-sticky-enabled');
            theme_opentechnology_handle_resize();
        }
    };
});