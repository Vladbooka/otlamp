define(['jquery', 'core/ajax', 'core/str', 'core/notification'], function($, ajax, strman, notification) {

    var CoursesFlow = function(instance, categoryId, currentPage, perPage, countItems, autoLoad,
            displayFromSubcategories, searchquery, userid, usercoursesnotactive) {

        this.__instance = instance;
        this.__categoryId = categoryId;
        this.__currentPage = currentPage;
        this.__perPage = perPage;
        this.__countItems = countItems;
        this.__loadedCourses = perPage;
        this.__displayFromSubcategories = displayFromSubcategories;
        this.__searchquery = searchquery;
        this.__userid = userid;
        this.__usercoursesnotactive = usercoursesnotactive;

        var _this = this;

        if( this.__countItems > 0 )
        {
            // Больше курсов
            this.__showmorebutton = $('<span>')
                .addClass('local_crw_show_more_button')
                .insertAfter(this.__instance);

            // Обернем в слой
            this.__showmorebutton
                .wrap('<div class="local_crw_show_more"></div>');

            // подгрузка языковой строки
            var showmorestr = strman.get_string('courses_flow_show_more', 'local_crw');

            $.when(showmorestr).done(function(str){
                _this.__showmorebutton
                    .text(str)
                    .click(function(){
                        _this.showMore();
                    });
            });

            // Рядом добавим "Идет загрузка"
            var loader = $('<span>')
                .addClass('local_crw_show_more_loader')
                .insertAfter(this.__showmorebutton);
            // подгрузка языковой строки
            var loadingstr = strman.get_string('courses_flow_loading', 'local_crw');
            $.when(loadingstr).done(function(str){
                loader.text(str);
            });

            // при первом вызове выполняется предзагрузка в невидимый слой
            this.showMore();

            if(this.__perPage > this.__countItems)
            {
                this.__instance.parent().find('.local_crw_top_paging_description span')
                    .text(this.__countItems);
            }

            // включена автозагрузка при достижении конца ленты
            if( autoLoad )
            {
                $(document).scroll(function() {
                    if( $(document).scrollTop() + $(window).height() >= _this.__showmorebutton.offset().top )
                    {
                        // докрутили до конца - загрузим следующий кусок
                        _this.showMore();
                    }
                });
            }
        }
    };

    CoursesFlow.prototype.__categoryId = 0;
    CoursesFlow.prototype.__currentPage = 0;
    CoursesFlow.prototype.__perPage = 8;
    CoursesFlow.prototype.__loadedCourses = 8;
    CoursesFlow.prototype.__countItems = 0;
    CoursesFlow.prototype.__displayFromSubcategories = null;
    CoursesFlow.prototype.__slot = undefined;
    CoursesFlow.prototype.__showmorebutton = undefined;
    CoursesFlow.prototype.__nextPageContent = $('<div>');
    CoursesFlow.prototype.__userid = null;
    CoursesFlow.prototype.__usercoursesnotactive = null;

    CoursesFlow.prototype.loadNextPageContent = function(){
        var ajaxLoad = this;

        if( Math.ceil(ajaxLoad.__countItems / ajaxLoad.__perPage) <= ajaxLoad.__currentPage )
        {
            ajaxLoad.__showmorebutton.parent().addClass('out-of-items').removeClass('loading');
        }

        if (!ajaxLoad.__showmorebutton.parent().hasClass('out-of-items'))
        {
            ajaxLoad.__nextPageContent = $('<div>')
                .addClass('local-crw_next-page-preload')
                .addClass('local-crw_next-page-empty')
                .appendTo(ajaxLoad.__instance);

            var requestargs = {
                'plugincode': ajaxLoad.__instance.parent().data('plugin-code'),
                'categoryid': ajaxLoad.__categoryId,
                'perpage': ajaxLoad.__perPage,
                'page': ++ajaxLoad.__currentPage,
                'params': JSON.stringify(ajaxLoad.getGet()),
                'displayfromsubcategories': ajaxLoad.__displayFromSubcategories,
                'searchquery': ajaxLoad.__searchquery
            };

            if (ajaxLoad.__userid !== null)
            {
                requestargs.userid = ajaxLoad.__userid;
            }

            if (ajaxLoad.__usercoursesnotactive !== null)
            {
                requestargs.usercoursesnotactive = ajaxLoad.__usercoursesnotactive;
            }

            var requests = ajax.call([{
                methodname : 'local_crw_get_courses',
                args: requestargs
            }]);

            requests[0]
                .done(function(response){
                    var decoded = $.parseJSON(response);
                    var html = decoded.html;
                    if (decoded.num_loaded_courses > 0)
                    {
                        ajaxLoad.__loadedCourses = ajaxLoad.__loadedCourses + decoded.num_loaded_courses;
                        ajaxLoad.__nextPageContent
                            .removeClass('local-crw_next-page-empty')
                            .append(html);
                        if( ajaxLoad.__showmorebutton.parent().hasClass('loading') )
                        {
                            ajaxLoad.showPreloadedPage();
                        }
                    } else {
                        ajaxLoad.__showmorebutton.parent().addClass('out-of-items').removeClass('loading');
                    }
                })
                .fail(notification.exception);
        }
    };

    CoursesFlow.prototype.getGet = function(){
        var params = {};
        window.location.search
          .replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str,key,value) {
            params[key] = decodeURIComponent(value);
          }
        );
        return params;
    };

    CoursesFlow.prototype.showPreloadedPage = function(){
        var ajaxLoad = this;

        ajaxLoad.__showmorebutton.parent().removeClass('loading');
        ajaxLoad.__nextPageContent.removeClass('local-crw_next-page-preload');
        ajaxLoad.markCurrentPage();
        ajaxLoad.loadNextPageContent();
    };

    CoursesFlow.prototype.markCurrentPage = function(){

        var ajaxLoad = this;

        ajaxLoad.__instance.parent().find('.paging').each(function(){
            // помечаем отображенные страницы как текущие
            $(this)
                .children('*:not(.previous):not(.next):not(.first):not(.last)')
                .eq(ajaxLoad.__currentPage)
                .addClass('current-page');

            $(this)
                .children('*.next')
                .click(function(event){
                    event.preventDefault();
                    var nextpage = $(this).parent().children('.current-page').last()
                        .next('*:not(.previous):not(.next):not(.first):not(.last)');
                    if(nextpage.length > 0)
                    {
                        window.location.href = nextpage.attr('href');
                    }
                });
            $(this)
                .children('*.previous')
                .click(function(event){
                    event.preventDefault();
                    var prevpage = $(this).parent().children('.current-page').first()
                        .prev('*:not(.previous):not(.next):not(.first):not(.last)');
                    if(prevpage.length > 0)
                    {
                        window.location.href = prevpage.attr('href');
                    }
                });
        });

        // изменение количества отображенных курсов
        ajaxLoad.__instance.parent().find('.local_crw_top_paging_description span')
            .text(ajaxLoad.__loadedCourses);
            //.text(displayedItems > ajaxLoad.__countItems ? ajaxLoad.__countItems : displayedItems)
    };

    CoursesFlow.prototype.showMore = function(){
        var ajaxLoad = this;

        if( ajaxLoad.__nextPageContent.hasClass('local-crw_next-page-empty') )
        {
            ajaxLoad.__showmorebutton.parent().addClass('loading');
        }
        else
        {
            ajaxLoad.showPreloadedPage();
        }
    };

    return {
        /**
         * Базовая инициализация ajax-загрузки
         */
        init: function(splobjecthash, categoryId, currentPage, perPage, countItems, autoLoad,
                displayFromSubcategories, searchquery, userid, usercoursesnotactive) {
            $('#local_crw[data-object-id="'+splobjecthash+'"] .crw_ptype_10 .crw_plugin_body').each(function(){
                return new CoursesFlow($(this), categoryId, currentPage, perPage, countItems, autoLoad,
                    displayFromSubcategories, searchquery, userid, usercoursesnotactive);
            });
        }
    };
});