define(['jquery', 'core/ajax', 'core/templates', 'core/notification', 'core/str', 'core/modal_factory', 'core/yui'],
function($, ajax, templates, notification, strman, ModalFactory, Y) {
    return {
        // Содержит массив хлебных крошек, состоящий из элементов catname - имя категории или раздела,
        // categorydata - base64 содержащий catid, parentid, sourcename
        _breadcrumbs: [],
        // Сохраняет старые хлебные крошки на случай если пользователь отменит выбор категории.
        _oldbreadcrumbs: [],
        // Хранит сформированную ссылку для iframe просмотра материала.
        _iframeuri: null,
        // Курс в котором расположен экземпляр библиотеки ресурсов.
        _courseid: null,
        // Предзагрузка для окна поиска, просмотра материала и выбора категорий.
        _dialogue: [$.Deferred(), $.Deferred(), $.Deferred()],
        // Имеется 3 контейнера в которые помещается результат рендеринга 'mp' - предпросмотр, 
        // 'ss' - выбор категории, 'sr' - результаты поиска, 'bread' - хлебные крошки, 'sub' - выбор подкатегории.
        _containers: [],
        // Хранит настройки передаваемые в скрытый элемент формы настроек (sourcename, resourceid, pagenum,
        // chapter, fragment)
        _hidden: null,
        // Кнопка открытия модального окна на форме.
        _button: null,
        // Поисковая фраза
        _searchtext: null,
        // Ид выбранной категории для поиска материалов
        _categoryid: null,
        // Имена ресурсов для поиска материалов
        _sourcenames: null,
        // Количество отображаемых материалов на 1 аякс запрос
        _showquantity: 10,
        _showstartid: 0,
        /**
         * Делает кнопку активной
         */
        enableButton: function() {
            this._button.removeClass('disabled');
        },
        /**
         * Делает кнопку не активной
         */
        disableButton: function() {
            this._button.addClass('disabled');
        },
        /**
         * Обработка данных и размещение в скрытый элемент
         */
        saveInModForm: function(sourcename, resourceid, pagenum, chapter, fragment, title){
            var material = this._containers['sr'].find('div.otrl_material');
            if (material.length > 0)
            {
                if (sourcename == '' || sourcename === undefined || resourceid == '' || resourceid ==undefined)
                {// Если не передавать параметров то может очистить скрытый инпут
                    this._hidden.val('');
                } else {
                    var params = {};
                    params.sourcename = sourcename;
                    params.resourceid = resourceid;
                    // Тут видимо ошибка проектирования так-как дальше используется pointertype pointerval,
                    // требуется найти единое решение.
                    if (pagenum === undefined || pagenum == '') {
                        params.pagenum = 0;
                    } else {
                        params.pagenum = pagenum;
                    }
                    if (chapter === undefined || chapter == '') {
                        params.chapter = 0;
                    } else {
                        params.chapter = chapter;
                    }
                    if (fragment === undefined || fragment == '') {
                        params.fragment = 0;
                    } else {
                        params.fragment = fragment;
                    }
                    // Запишем реквизиты материала в скрытый инпут формы настроек
                    this._hidden.val(JSON.stringify(params));
                    var sourcenameinfo = $('.otrl_sourcename_info');
                    var resourceinfo = $('.otrl_resource_info');
                    // Запишем информацию о выбранном материале в  "Наименование источника" и "Ресурс" формы
                    sourcenameinfo.empty();
                    resourceinfo.empty();
                    sourcenameinfo.text(sourcename);
                    resourceinfo.text(title);
                }
            }
        },
        /**
         * Отобразим найденые материалы
         */
        InsertSearchResults: function(searchtext, clean, categoryid, sourcenames) {
            var orl = this;
            // Для предварительной очистки контейнера используется параметр clean
            if (clean) {
                orl._containers['sr'].find('#search_results').empty().append("<div class='loading'></div>");
                orl._categoryid = categoryid;
                orl._searchtext = searchtext;
                orl._sourcenames = sourcenames;
                orl._showstartid = 0;
            } else {
                orl._showstartid = orl._showstartid + orl._showquantity;
                orl._containers['sr'].find('#otrl_more_holder>div').addClass('loading');
            }
            var requestargs = {
                'sourcenames': orl._sourcenames,
                'categoryid': orl._categoryid,
                'q': orl._searchtext,
                'courseid': orl._courseid,
                'showstartid': orl._showstartid,
                'showquantity': orl._showquantity,
            };

            var response = ajax.call([ {
                methodname: 'mod_otresourcelibrary_insert_search_results',
                args: requestargs
            } ]);
            response[0]
                    .done(function(searchresults){
                        var srdecode = JSON.parse(atob(searchresults));
                        orl.RenderContent('search_results', srdecode, function(html, js) {
                            var searchresultsholder = orl._containers['sr'].find('#search_results');
                            // Требуется оставить кнопку показать больше или нет.
                            if (clean) {
                                searchresultsholder.empty();
                            } else {
                                orl._containers['sr'].find('#otrl_more_holder').remove();
                            }
                            templates.appendNodeContents(searchresultsholder, html, js);
                            orl.initAfterSearchResults();
                        });
                    })
                    .fail(function(ex) {
                        orl._containers['sr'].find('.loading').removeClass('loading');
                        // Если получили ошибку при обработке аякс запроса - выведем стек в консоль
                        notification.exception(ex);
                    });
        },
        /**
         * Отобразим хедер с поиском
         */
        InsertSearchHeader: function() {
            var orl = this;
            var response = ajax.call([ {
                methodname : 'mod_otresourcelibrary_insert_search_header',
                // Спроектирована область справа от строки поиска где должны располагаться фильтры материала,
                // а тут должна передаваться дата этих фильтров при дальнейшей разработке.
                args : {
                    'data': 'here can bee data'
                }
            } ]);
            response[0]
                    .done(function(searchheader){
                        var shdecode = JSON.parse(atob(searchheader));
                        orl.RenderContent('search_header', shdecode, function(html, js){
                            orl._containers['sr'].empty();
                            templates.appendNodeContents(orl._containers['sr'], html, js);
                            orl.getDialogueSR().done(function(dialogue) {
                                // Так как до этого отображалось маленькое окошко с иконкой загрузки, 
                                // приведем размер модального окна к нормальному.
                                dialogue.set('width', 'auto');
                                dialogue.set('height', '100%');
                            });
                            orl._dialogue[2].resolve();
                            orl._containers['sr'].closest('div.moodle-dialogue')
                                .addClass('moodle-dialogue-otresourcelibrary-material_search');
                            orl.initAfterSearchHeader();
                        });
                    })
                    .fail(function(ex) {
                        orl._containers['sr'].find('.loading').removeClass('loading');
                        // Если получили ошибку при обработке аякс запроса
                        notification.exception(ex);
                    });
        },
        /**
         * Отобразим предпросмотр материала
         */
        InsertMaterialPreview: function(sourcename, resourceid, pagenum, chapter, fragment, title, selectedmaterial) {
            var orl = this;
            orl._iframeuri = '/mod/otresourcelibrary/view.php?co=1&force=embeded&cid=' + orl._courseid
                + '&sourcename=' + sourcename + '&resourceid=' + resourceid;
            var anchor = '';
            // Так как якорь может быть только один, он будет выбран по приоритету,
            // в дальнейшем стоит подумать над этим моментом.
            if (fragment === undefined) {
                var hasfragment = 0;
            } else {
                // Булево значение для рендера, сообщаюшее что фрагмент доступен
                var hasfragment = 1;
                if (fragment != '') {
                    anchor = '&pointertype=fragment&pointerval=' + fragment + '#' + fragment;
                }
            }
            if (chapter === undefined) {
                var haschapter = 0;
            } else {
                var haschapter = 1;
                if (chapter != '') {
                    anchor = '&pointertype=chapter&pointerval=' + chapter + '#' + chapter;
                }
            }
            if (pagenum === undefined) {
                var haspagenum = 0;
            } else {
                var haspagenum = 1;
                if (pagenum != '') {
                    anchor = '&pointertype=pagenum&pointerval=' + pagenum + '#' + pagenum;
                }
            }
            var uri = orl._iframeuri + anchor;
            var material = {
                    'key' : selectedmaterial,
                    'title' : title,
                    'urltoview' : encodeURI(uri),
                    'fragmentvalue' : fragment,
                    'chaptervalue' : chapter,
                    'pagenumvalue' : pagenum,
                    'fragment' : hasfragment,
                    'chapter' : haschapter,
                    'pagenum' : haspagenum
                   };
            orl.RenderContent('material_preview', material, function(html, js){
                orl._containers['mp'].empty();
                templates.appendNodeContents(orl._containers['mp'], html, js);
                orl.getDialogueMP().done(function(dialogue) {
                    // Так как до этого отображалось маленькое окошко с иконкой загрузки, 
                    // приведем размер модального окна к нормальному.
                    dialogue.set('width', 'auto');
                    dialogue.set('height', '100%');
                });
                orl._containers['mp'].closest('div.moodle-dialogue').addClass('moodle-dialogue-otresourcelibrary-material_preview');
                orl.initAfterMaterialPreview();
            });
        },
        /**
         * Отобразим выбор ресурсов
         */
        InsertSectionSelection: function() {
            var orl = this;
            var response = ajax.call([ {
                methodname : 'mod_otresourcelibrary_insert_section_selection',
                args : {}
            } ]);
            response[0]
                .done(function(data){
                    orl._dialogue[2].done(function() {
                        var datadecode = JSON.parse(atob(data));
                        orl.RenderContent('section_selection', datadecode, function(html, js){
                            orl._containers['ss'].empty();
                            templates.appendNodeContents(orl._containers['ss'], html, js);
                            orl.initAfterSectionSelection();
                            // Проставляет класс для выбранной категории для дальнейшей стилизации
                            orl.SetCategorySelected();
                        });
                    });
                })
                .fail(function(ex) {
                    // Если получили ошибку при обработке аякс запроса
                    notification.exception(ex);
                });
        },
        /**
         * Отобразим выбор категории
         */
        InsertCategorySelection: function(sourcename) {
            var orl = this;
            orl._containers['ss'].find('.otrl_source').addClass('loading');

            var arguments = {};
            if (sourcename != undefined) {
                arguments.sourcename = sourcename;
            }

            var categoryselection = orl._containers['ss'].find('#otrl_category_selection');
            categoryselection.empty().hide();

            var response = ajax.call([ {
                methodname : 'mod_otresourcelibrary_insert_category_selection',
                args : arguments
            } ]);

            response[0]
                    .done(function(data){
                        var datadecode = JSON.parse(atob(data));
                        orl.RenderContent('category_selection', datadecode, function(html, js){
                            categoryselection.hide();
                            if (!/^\s*$/.test(html))
                            {
                                templates.appendNodeContents(categoryselection, html, js);
                                categoryselection.show();
                            }
                            orl._containers['ss'].find('.otrl_source').removeClass('loading');
                            orl.initAfterCategorySelection();
                            orl.SetCategorySelected();
                        });
                    })
                    .fail(function(ex) {
                        orl._containers['sr'].find('.loading').removeClass('loading');
                        // Если получили ошибку при обработке аякс запроса
                        notification.exception(ex);
                    });
        },
        /**
         * Вставим подкатегорию
         */
        InsertSubcategories: function(categorydata) {
            var orl = this;
            orl._containers['sub'].append("<div class='otrl_subcategory_holder'><div class='loading'></div></div>");
            var response = ajax.call([ {
                methodname : 'mod_otresourcelibrary_insert_subcategories',
                args : {
                    'sourcename' : categorydata['sourcename'],
                    'parentid' : categorydata['catid']
                }
            } ]);
            response[0]
                    .done(function(data){
                        var datadecode = JSON.parse(atob(data));
                        orl.RenderContent('subcategories', datadecode, function(html, js){
                            orl._containers['sub'].empty();
                            templates.appendNodeContents(orl._containers['sub'], html, js);
                            if (datadecode['subcategories'][0]['subcategory']['level'] > 1) {
                                orl._containers['ss'].find('.otrl_cat1nd, .otrl_cat2nd').addClass('otrl_subcat');
                            } else {
                                orl._containers['ss'].find('.otrl_cat1nd, .otrl_cat2nd').removeClass('otrl_subcat');
                            }
                            orl.initAfterSubcategories();
                            orl.SetCategorySelected();
                        });
                    })
                    .fail(function(ex) {
                        orl._containers['sr'].find('.loading').removeClass('loading');
                        // Если получили ошибку при обработке аякс запроса
                        notification.exception(ex);
                    });
        },
        /**
         * Вставим хлебные крошки
         */
        InsertBreadcrumbs: function(categorydata, catname) {
            var orl = this;
            // Если categorydata отсутствует то выбран ресурс
            if (categorydata != '') {
                var catdatadecode = JSON.parse(atob(categorydata));
                // Если категория нулевого уровня то отбросим всю цепочку ранее выбраных подкатегорий
                if (catdatadecode['parentid'] == 0) {
                    orl._breadcrumbs.splice(1, orl._breadcrumbs.length - 1);
                }
                if (orl._breadcrumbs.length >= 1) {
                    var lastcatdata = orl._breadcrumbs[orl._breadcrumbs.length - 1]['categorydata'];
                    if (lastcatdata != '') {
                        lastcatdata = JSON.parse(atob(lastcatdata));
                        // Если мы поднялись на один уровень и этот уровень не ресурс удалим последний элемент
                        if (catdatadecode['parentid'] == lastcatdata['parentid']) {
                            orl._breadcrumbs.pop();
                        }
                    }
                    // Найдем выбранный элемент, удалим все что после него
                    orl._breadcrumbs.forEach(function(item, i) {
                        if (item['categorydata'] != '') {
                            var itemdecode = JSON.parse(atob(item['categorydata']));
                            if (itemdecode['catid'] == catdatadecode['catid']) {
                                orl._breadcrumbs.splice(i, orl._breadcrumbs.length - 1);
                            }
                        }
                    });
                    // Если первым элементом хлебных крошек оказался не источник, добавим его.
                    if (orl._breadcrumbs[0]['categorydata'] != '') {
                        orl._breadcrumbs.unshift({'catname': catdatadecode['sourcename'], 'categorydata': ''});
                    }
                }
                orl._breadcrumbs.push({'catname': catname, 'categorydata': categorydata});
            } else {
                // тка-как выбран ресурс очистим хлебные крошки, установим в качестве первой название ресурса
                orl._breadcrumbs = [];
                orl._breadcrumbs.push({'catname': catname, 'categorydata': ''});
            }
            orl.RenderContent('breadcrumbs', {'breadcrumbs': orl._breadcrumbs}, function(html, js) {
                orl._containers['bread'].empty();
                templates.appendNodeContents(orl._containers['bread'], html, js);
                orl.initAfterBreadcrumbs();
            });
        },
        /**
         * Проставляет категориям класс для последующий стилизации
         */
        SetCategorySelected: function() {
            var orl = this;

            orl._containers['ss'].find(".otrl_sources .otrl_source").removeClass('otrl_selected');

            var sourceitem = orl.getCurrentSourceItem();
            if (sourceitem !== null)
            {
                sourceitem.addClass('otrl_selected');
            }
        },
        /**
         * Рендеринг контента с callback
         */
        RenderContent: function(type, data, callback) {
            var orl = this;
            // This will call the function to load and render our template.
            templates.render('otresourcelibrary/' + type, data)
                .then(function(html, js) {
                    callback(html, js);
                }).fail(function(ex) {
                    orl._containers['sr'].find('.loading').removeClass('loading');
                    notification.exception(ex);
                });
        },
        /**
         * Инициализация событий после загрузки шаблона c хлебными крошками
         */
        initAfterBreadcrumbs: function() {
            var orl = this;
            var btnselectcategory = orl._containers['sr'].find(".btn-select-category");
            btnselectcategory.off('click');
            btnselectcategory.on('click', function() {
                // Отменим событие по клику вне контейнера категорий
                orl._containers['sr'].off('mouseup');
                orl.SearchInputDisabled();
                orl._containers['bread'] = orl._containers['sr'].find('.otrl_breadcrumbs');
                orl.RenderContent('breadcrumbs', {'breadcrumbs': orl._breadcrumbs}, function(html, js) {
                    orl._containers['bread'].empty();
                    templates.appendNodeContents(orl._containers['bread'], html, js);
                    orl.initAfterBreadcrumbs();
                });
                orl._containers['ss'].find('#otrl_category_selection').attr({'style': 'display:none'});
                $(this).attr({'style': 'display:none'});

                var categoryid = orl.getCurrentCategoryId();
                var sourcename = orl.getCurrentSourcename();
                // Выбор категории/источника нажатием кнопки "Выбрать"
                orl.InsertSearchResults(orl._searchtext, true, categoryid, sourcename);
            });
            var breadcrumbsbtn = orl._containers['sr'].find(".otrl_breadcrumbs .breadcrumb a");
            breadcrumbsbtn.off('click');
            breadcrumbsbtn.on('click', function() {
                orl._containers['ss'].find('#otrl_category_selection').attr({'style': 'display:none'});
                orl._containers['sr'].find('.btn-select-category').attr({'style': 'display:none'});
                var resoursename = $(this).find("span").text();
                var categorydata = $(this).attr('data');
                orl.InsertBreadcrumbs(categorydata, resoursename);
                orl.SearchInputDisabled();
                orl.SetCategorySelected();
                if (categorydata != '') {
                    categorydata = JSON.parse(atob(categorydata));
                    if (categorydata['parentid'] == 0) {
                        orl.InsertCategorySelection(categorydata['sourcename']);
                    } else {
                        orl._containers['sub'].empty();
                        orl.InsertSubcategories(categorydata);
                    }

                    var categoryid = orl.getCurrentCategoryId();
                    var sourcename = orl.getCurrentSourcename();
                    // Клик по категории в хлебных крошках
                    orl.InsertSearchResults(orl._searchtext, true, categoryid, sourcename);

                } else {
                    // Клик по источнику в хлебных крошках
                    orl.InsertSearchResults(orl._searchtext, true, null, resoursename);
                }
                orl._containers['ss'].find('#otrl_category_selection').attr({'style': 'display:none'});
                orl._containers['sr'].find('.btn-select-category').attr({'style': 'display:none'});
            });
        },
        /**
         * Получение информации о последней хлебной крошке (если нет - null)
         */
        getLastBreadcrumbsItem: function(){
            var orl = this;
            var result = null;

            if (orl._breadcrumbs.length > 0)
            {
                result = orl._breadcrumbs[orl._breadcrumbs.length - 1];
            }

            return result;
        },
        /**
         * Получение информации о текущей категории (если нет - null)
         */
        getCurrentCategory: function() {
            var orl = this;
            var currentcat = null;

            var breadcrumb = orl.getLastBreadcrumbsItem();
            if (breadcrumb !== null)
            {
                if (breadcrumb.categorydata != '')
                {
                    var currentcat = breadcrumb;
                    currentcat.decodedinfo = JSON.parse(atob(currentcat.categorydata));
                }
            }

            return currentcat;
        },
        /**
         * Получение идентификатора текущей категори (если нет - null)
         */
        getCurrentCategoryId: function() {
            var orl = this;
            var result = null;

            var currentcat = orl.getCurrentCategory();
            if (currentcat !== null)
            {
                result = currentcat.decodedinfo.catid;
            }

            return result;
        },
        /**
         * Получение текущего источника
         * (вычисляется по текущей категории, если нет - по текущему источнику, если нет - null)
         */
        getCurrentSourcename: function() {
            var orl = this;
            var result = null;

            var breadcrumb = orl.getLastBreadcrumbsItem();
            if (breadcrumb !== null)
            {
                if (breadcrumb.categorydata != '')
                {
                    breadcrumb.decodedinfo = JSON.parse(atob(breadcrumb.categorydata));
                    result = breadcrumb.decodedinfo.sourcename;
                } else
                {
                    result = breadcrumb.catname;
                }
            }

            return result;
        },
        /**
         * Получение DOM-элемента текущего источника
         */
        getCurrentSourceItem: function(){
            var orl = this;
            var result = null;
            var sourcename = orl.getCurrentSourcename();

            orl._containers['ss'].find(".otrl_sources .otrl_source").each(function(){
                if ($(this).html() == sourcename)
                {
                    result = $(this);
                    return;
                }
            });

            return result;
        },
        /**
         * Получение DOM-элемента источника текущей категории
         */
        getCurrentCategorySourceItem: function() {

            var orl = this;
            var result = null;

            var currentcat = orl.getCurrentCategory();
            if (currentcat !== null)
            {
                var sourcename = currentcat.decodedinfo.sourcename;
                orl._containers['ss'].find(".otrl_sources .otrl_source").each(function(){
                    if ($(this).html() == sourcename)
                    {
                        result = $(this);
                        return;
                    }
                });
            }

            return result;
        },
        /**
         * Отвечает за скрытие кнопки найти и инпута поиска материала
         */
        SearchInputDisabled: function () {
            var orl = this;
            var searchinput = orl._containers['sr'].find('input[name="otrl_searchinput"]');
            var searchbtn = orl._containers['sr'].find('input.otrl_search_button');

            var sourceitem = orl.getCurrentCategorySourceItem();
            if (sourceitem !== null && sourceitem.data('feature-search_in_category') != 1) {
                // выбрана категория ресурса, не поддерживающего поиск в категории
                orl._searchtext = null;
                searchinput.val('').attr('disabled', true);
                searchbtn.attr('disabled', true);
            } else {
                searchinput.removeAttr('disabled');
                searchbtn.removeAttr('disabled');
            }
        },
        /**
         * Инициализация событий после загрузки шаблона c подкатегориями
         */
        initAfterSubcategories: function() {
            var orl = this;
            var categories = orl._containers['sub'].find(".otrl_category .otrl_hassubcat");
            categories.off('click');
            // Если есть подкатегории то срабатывает событие рендеренга подкатегории в тот-же контейнер
            categories.on('click', function() {
                orl._containers['sub'].empty();
                var categorydata = $(this).closest('.otrl_category').attr('data');
                orl.InsertBreadcrumbs(categorydata, $(this).text());
                categorydata = JSON.parse(atob(categorydata));
                orl.InsertSubcategories(categorydata);
            });
            var endcategories = orl._containers['sub'].find(".otrl_category .otrl_nosubcat");
            endcategories.off('click');
            // Если подкатегорий нет то только обнавляем хлебные крошки и добавляем класс для стилизации
            endcategories.on('click', function() {
                endcategories.removeClass('otrl_selected');
                $(this).addClass('otrl_selected');
                var categorydata = $(this).closest('.otrl_category').attr('data');
                orl.InsertBreadcrumbs(categorydata, $(this).text());
            });
        },
        /**
         * Инициализация событий после загрузки шаблона c категориями 0 уровня
         */
        initAfterCategorySelection: function() {
            var orl = this;
            orl._containers['bread'] = $('.otrl_breadcrumbs');
            orl._containers['sub'] = $('.otrl-section-selection-container #otrl_subcategories');
            var cat0elements = orl._containers['ss'].find(".otrl_category0 a");
            cat0elements.off('click');
            // При клике на категории нулевого уровня открывет подкатегории обновляет хлебные крошки
            cat0elements.on('click', function() {
                orl._containers['sub'].empty();
                var catli = $(this).closest('.otrl_category0');
                var categorydata = catli.attr('data');
                orl.InsertBreadcrumbs(categorydata, $(this).text());
                if (catli.data('has-children'))
                {
                    categorydata = JSON.parse(atob(categorydata));
                    orl.InsertSubcategories(categorydata);
                }
            });
            // При клике вне контейнера категорий скроем его и вернем старые хлебные крошки
            orl._containers['sr'].mouseup(function (e) {
                var div = orl._containers['ss'].find('#otrl_category_selection');
                if (!div.is(e.target)
                        && div.has(e.target).length === 0
                        && !orl._containers['sr'].find('.btn-select-category').is(e.target)
                        && !orl._containers['ss'].find(".otrl_sources .otrl_source").is(e.target)) {
                    orl._containers['sr'].off('mouseup');
                    div.attr({'style': 'display:none'});
                    orl.RestoreBreadcrumbs();
                    orl.SetCategorySelected();
                }
            });
        },
        /**
         * Инициализация событий после загрузки шаблона c источниками
         */
        initAfterSectionSelection: function() {
            var orl = this;
            orl._containers['bread'] = $('.otrl_breadcrumbs');
            var sources = orl._containers['ss'].find(".otrl_sources .otrl_source");
            sources.off('click');
            // По клику на ресурс сохраним старые хлебные крошки и загрузим категории
            sources.on('click', function() {
                orl.SaveBreadcrumbs();
                orl.InsertCategorySelection($(this).text());
                orl.InsertBreadcrumbs('', $(this).text());
                orl._containers['sr'].find('.btn-select-category').removeAttr('style');
//                orl._containers['ss'].find('#otrl_category_selection').removeAttr('style');
            });
        },
        /**
         * Сохраняет хлебные крошки в контейнер _oldbreadcrumbs
         */
        SaveBreadcrumbs: function() {
            var orl = this;
            orl._oldbreadcrumbs = orl._breadcrumbs;
        },
        /**
         * Восстанавливает хлебные крошки из контейнера _oldbreadcrumbs
         */
        RestoreBreadcrumbs: function() {
            var orl = this;
            orl._breadcrumbs = orl._oldbreadcrumbs;
            orl.RenderContent('breadcrumbs', {'breadcrumbs': orl._breadcrumbs}, function(html, js) {
                orl._containers['bread'].empty();
                orl._containers['sr'].find('.btn-select-category').attr({'style': 'display:none'});
                templates.appendNodeContents(orl._containers['bread'], html, js);
                orl.initAfterBreadcrumbs();
            });
        },
        /**
         * Инициализация событий после загрузки шаблона предварительного просмотра
         */
        initAfterMaterialPreview: function() {
            var orl = this;
            var btnreturn = orl._containers['mp'].find(".otrl_material_btn_holder .btn-secondary");
            btnreturn.off('click');
            // Событие по нажатию кнопки вернуться назад, тоже самое что и крест...
            btnreturn.on('click', function() {
                orl.getDialogueMP().done(function(dialogue) {
                    dialogue.hide();
                });
            });
            var inputssubmit = orl._containers['mp'].find(".otrl_material_btn_holder .btn-primary");
            inputssubmit.off('click');
            // Событие сохранения материала и скрытия модалок.
            inputssubmit.on('click', function() {
                var thismaterial = $(this).closest('div.otrl_material');
                var data = orl.getInputAndMaterialData(thismaterial);
                orl.saveInModForm(
                        data.sourcename,
                        data.resourceid,
                        data.pagenum,
                        data.chapter,
                        data.fragment,
                        data.title
                );
                orl.getDialogueSR().done(function(dialogue) {
                    dialogue.hide();
                });
                orl.getDialogueMP().done(function(dialogue) {
                    dialogue.hide();
                });
            });
            var anchors  = orl._containers['mp'].find(".anchors input");
            var iframe = orl._containers['mp'].find('.otrl_iframe iframe');
            // Во фрейме не получается сделать переход к якорю, ниже реализован плавный переход за счет js
            iframe.on('load',function() {
                var url = iframe.attr("src");
                if (url.indexOf("#") > 0) {
                    var loc = url.substring(url.indexOf("#")+1);
                    if (loc != "" && iframe.contents().find("#" + loc).length > 0) {
                        var destination = $("#" + loc, iframe.contents()).offset().top;
                        iframe.contents().find('body').animate({ scrollTop: destination}, 1000 );
                    }
                }
            });
            // При потере фопуса установит ссылку в src фрейма, очистит другие якоря.
            anchors.focusout(function(){
                var inpit = $(this);
                var uri = orl._iframeuri;
                var inputval = inpit.val();
                if (inputval != '') {
                    var inputname = inpit.attr('name').replace('material_', '');
                    uri = uri + '&pointertype=' + inputname + '&pointerval=' + inputval + '#' + inputval;
                    iframe.attr('src', uri);
                    for (var index = 0; index < anchors.length; ++index) {
                        if ($(anchors[index]).attr('name') != inpit.attr('name')) {
                            $(anchors[index]).val('');
                        }
                    }
                }
            });
        },
        /**
         * Инициализация событий после загрузки шаблона шапки поиска
         */
        initAfterSearchHeader: function() {
            var orl = this;
            var searchinput = orl._containers['sr'].find('input[name="otrl_searchinput"]');
            // Событие по нажатию кнопки найти
            $(document)
            .off('click touchstart', 'input.otrl_search_button')
            .on('click touchstart', 'input.otrl_search_button', function() {
                orl.searchHandler(searchinput.val());
            });
            // срабатывание поиска при нажатии на кнопку ввод
            searchinput.keydown(function(eventObject){
                if (eventObject.keyCode == 13) {
                    eventObject.preventDefault();
                    orl.searchHandler(searchinput.val());
                }
            });
            orl._containers['ss'] = $('.otrl-section-selection-container');
            var selectcategory = orl._containers['sr'].find('.otrl_select_category');
            selectcategory.off('click');
            // Клик по кнопке "Все источники"
            selectcategory.on('click', function() {
                orl._breadcrumbs = [];
                $('.otrl_breadcrumbs').empty();
                orl._containers['ss'].find('.otrl_source').removeClass('otrl_selected');
                orl._categoryid = null;
                orl._sourcenames = null;
                orl.SearchInputDisabled();
                orl._containers['ss'].find('#otrl_category_selection').empty().attr({'style': 'display:none'});
                orl._containers['sr'].find('.btn-select-category').attr({'style': 'display:none'});
                orl.InsertSearchResults(orl._searchtext, true);
            });
        },
        /**
         * Инициализация событий после рендеренга результатов поиска
         */
        initAfterSearchResults: function() {
            var orl = this;
            var inputssubmit = orl._containers['sr'].find(".otrl_material_btn_holder .btn-primary");
            inputssubmit.off('click');
            // Событие сохранения материала и скрытия модалок.
            inputssubmit.on('click', function() {
                var thismaterial = $(this).closest('div.otrl_material');
                var data = orl.getInputAndMaterialData(thismaterial);
                orl.saveInModForm(
                        data.sourcename,
                        data.resourceid,
                        data.pagenum,
                        data.chapter,
                        data.fragment,
                        data.title
                );
                orl.getDialogueSR().done(function(dialogue) {
                    dialogue.hide();
                });
            });
            var morebtn = orl._containers['sr'].find('#otrl_more_holder>div');
            morebtn.off('click');
         // Событие по нажаию кнопки Показать больше (подгрузка следующей страницы)
            morebtn.on('click', function() {
                orl.InsertSearchResults(orl._searchtext, false);
            });
            orl._containers['sr'].find('.spoiler-body').hide(300);
            orl._containers['sr'].off('click','.spoiler-head');
            orl._containers['sr'].on('click','.spoiler-head',function (e) {
                e.preventDefault();
                $(this).parents('.spoiler-wrap').toggleClass("active").find('.spoiler-body').slideToggle();
            });
            strman
            .get_strings([
                { key: 'preview_header', component: 'mod_otresourcelibrary' }
            ])
            .done(function(strs) {
                orl.getDialogueMP().done(function(dialogue){
                    dialogue.set('headerContent', strs[0]);
                });
            });
            var btnpreview = orl._containers['sr'].find('.otrl_material_btn_holder .btn-secondary');
            btnpreview.off('click');
            // Событие по нажатию кнопки Перейти к просмотру
            btnpreview.on('click', function(e){
                e.preventDefault();
                var thismaterial = $(this).closest('div.otrl_material');
                var data = orl.getInputAndMaterialData(thismaterial);
                orl.getDialogueMP().done(function(dialogue) {
                    dialogue.show();
                    orl._containers['mp'] = $('.otrl-material-preview-container');
                    orl.InsertMaterialPreview(
                            data.sourcename,
                            data.resourceid,
                            data.pagenum,
                            data.chapter,
                            data.fragment,
                            data.title,
                            data.selectedmaterial
                    );
                });
            });
            var anchors  = orl._containers['sr'].find(".anchors input");
            // При потере фопуса установит ссылку в src фрейма, очистит другие якоря.
            anchors.focusout(function(){
                var inpit = $(this);
                var inputval = inpit.val();
                if (inputval != '') {
                    for (var index = 0; index < anchors.length; ++index) {
                        if ($(anchors[index]).attr('name') != inpit.attr('name')) {
                            $(anchors[index]).val('');
                        }
                    }
                }
            });
        },
        /**
         * Обработчик строки поиска
         */
        searchHandler: function(searchtext) {
            var orl = this;
            // Нажатие кнопки "Найти" рядом с поисковой фразой (или отправка нажатием Enter)
            orl.InsertSearchResults(searchtext, true, orl._categoryid, orl._sourcenames);
        },
        /**
         * Получим данные из инпутов и данные материала
         */
        getInputAndMaterialData: function(material) {
            var data = [];
            data.pagenum  = material.find("input[name='material_pagenum']").val();
            data.chapter  = material.find("input[name='material_chapter']").val();
            data.fragment = material.find("input[name='material_fragment']").val();
            data.title    = material.find(".material_name .name").text();
            data.selectedmaterial = material.attr('data');
            var parsedmaterial = JSON.parse(atob(data.selectedmaterial));
            data.sourcename = parsedmaterial['sourcename'];
            data.resourceid = parsedmaterial['resourceid'];
            return data;
        },
        /**
         * Настройки диологового окна
         */
        dialogConfig: function(classname) {
            var spinner = Y.Node.create('<img />')
            .setAttribute('src', M.util.image_url('i/loading', 'moodle'))
            .addClass('spinner');
            var data = {
                    headerContent: '&nbsp;',
                    // В качестве контента страницы иконка загрузки
                    bodyContent: Y.Node.create('<div />').addClass(classname).append(spinner),
                    draggable: false,
                    visible: false,
                    center: true,
                    modal: true,
                    // Это начальные размеры для отображения иконки загрузки, далее размеры будут переопределены.
                    width: '400px',
                    height: '200px'
            };
            return data;
        },
        /**
         * Сформируем модальное окно для поиска
         */
        getDialogueSR: function() {
            var orl = this;
            if (orl._dialogue[0].state() != 'resolved') {
                Y.use('moodle-core-notification', function() {
                    var classname = 'otrl-search-results-container';
                    orl._dialogue[0].resolve(new M.core.dialogue(orl.dialogConfig(classname)));
                });
            }
            return orl._dialogue[0].promise();
        },
        /**
         * Сформируем модальное окно для предварительного просмотра
         */
        getDialogueMP: function() {
            var orl = this;
            if (orl._dialogue[1].state() != 'resolved') {
                Y.use('moodle-core-notification', function() {
                    var classname = 'otrl-material-preview-container';
                    orl._dialogue[1].resolve(new M.core.dialogue(orl.dialogConfig(classname)));
                });
            }
            return orl._dialogue[1].promise();
        },
        /**
         * Базовая инициализация инструментов
         */
        init: function(courseid) {
            var orl = this;
            orl._courseid = courseid;
            var form = $('form.mform.mod_otresourcelibrary-js').first();
            // Элемент с реквизитами выбранного материала из скрытого элемента формы настроек
            orl._hidden = form.find('input[name="khipu_setting"]').first();
            // Кнопка "Настройки материала"
            orl._button = form.find('.otresourcelibrary_settings_button').first();
            strman
            .get_strings([
                { key: 'modal_form_header', component: 'mod_otresourcelibrary' }
            ])
            .done(function(strs) {
                orl.getDialogueSR().done(function(dialogue){
                    dialogue.set('headerContent', strs[0]);
                });
            })
            .fail(notification.exception)
            .always(function(){orl.enableButton.call(orl);});
            orl._button.off('click').on('click', function(e){
                e.preventDefault();
                if (orl._containers['sr']) {
                    orl._containers['sr'].empty();
                }
                // На случай если окно будет открыто повторно
                orl._dialogue[2] = $.Deferred();
                orl.disableButton();
                orl.getDialogueSR().done(function(dialogue) {
                    dialogue.show();
                    orl._containers['sr'] = $('.otrl-search-results-container');
                    orl.enableButton();
                    orl.InsertSearchHeader();
                    // Это отобразит панель выбора источника
                    orl.InsertSectionSelection();
                });
            });
        }
    };
});