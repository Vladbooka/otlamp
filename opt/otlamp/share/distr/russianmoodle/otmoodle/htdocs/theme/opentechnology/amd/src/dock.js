define(
    ['jquery', 'core/yui', 'core/templates', 'core/ajax', 'core/notification'],
    function($, Y, Templates, ajax, notification) {
        return {
            // количество докедитемов используемое для создания уникальных идентификаторов
            __itemsCount: 0,
            // jquery-элемент дока
            dock: null,
            // иконки блоков
            blockicons: {},
            courseid: null,
            pagetype: null,
            pagelayout: null,
            subpage: null,
            contextid: null,
            defaulticon: null,
            handleResize: function() {

                var dockeditemswidth = 0;
                var custommenu = Y.one('#h_custommenu_wrapper label.custom_menu_mobile_label');
                var headermenuwidth = Y.one("body").get("winWidth") > 425 ? 0 : (custommenu ? custommenu.get('clientWidth') : 0);
                var dockbg = Y.one('#dock_bg');
                var body = Y.one('body');
                if(headermenuwidth > 0)
                {
                    if( dockbg !== null )
                    {
                        dockbg.setStyle('padding-left', headermenuwidth + 'px');
                    }
                } else
                {
                    if( dockbg !== null )
                    {
                        dockbg.setStyle('padding-left', null);
                    }
                }
                var header = Y.one('#dock_bg h1');
                var langmenu = Y.one('#dock_bg .langmenu_wrapper');
                var dockdiv = Y.one('#dock');
                var positionX = 0;
                var positionY = 0;
                var dockfullwidth = 0;
                var dockheight = 0;
                if( dockbg !== null )
                {
                    positionX = dockbg.getX() + parseFloat(dockbg.getComputedStyle('paddingLeft'))
                        - parseFloat(body.getComputedStyle('marginLeft'));
                    positionY = dockbg.getY();
                    dockfullwidth = dockbg.get('clientWidth')
                        - parseFloat(dockbg.getComputedStyle('paddingLeft'))
                        - parseFloat(dockbg.getComputedStyle('paddingRight'));
                    dockheight = dockbg.get('clientHeight');
                }



                // ширина заголовка
                var headerwidth = 0;
                if ( header !== null )
                {
                    // по умолчанию заголовок виден, чтобы мы учитывали его размер
                    // и только если не будет влезать - спрячем потом
                    header.setStyle('display', 'inline-block');
                    var headerwidth = header.get('clientWidth');
                }

                // ширина панели выбора языка
                var langmenuwidth = 0;
                if (langmenu !== null)
                {
                    var langmenuwidth = langmenu.get('clientWidth');
                }

                // тип отображения док-панели
                var dockeditemtitle = Y.one('html').getAttribute('data-dockeditem-title');
                if( dockeditemtitle === null )
                {
                    dockeditemtitle = 0;
                }

                if( parseInt(dockeditemtitle) == 2 || parseInt(dockeditemtitle) == 4 )
                {// включена настройка отображения иконок, если элементы не помещаются
                    //включаем принудительно отображение, которое было настроено,
                    // для просчета суммарной ширины элементов в изначальном виде
                    var dockeditemswidth = 0;
                    Y.all('#dock .dockeditem').each(function(dockeditem){
                        var dockedtitle = dockeditem.one('.dockedtitle');
                        if( parseInt(dockeditemtitle) == 2 )
                        {// включено отображение текстом, если помещается
                            dockedtitle.removeClass('iconview');
                            dockedtitle.removeClass('texthide');
                        }
                        if( parseInt(dockeditemtitle) == 4 )
                        {// включено отображение текст+иконки, если помещаются
                            dockedtitle.addClass('iconview');
                            dockedtitle.removeClass('texthide');
                        }
                        dockeditemswidth+=dockeditem.get('clientWidth');
                    });

                    // место под элементы док-панели
                    var availableplace = dockfullwidth - headerwidth - (headerwidth > 0 ? 10 : 0) - langmenuwidth;
                    if( availableplace < (dockeditemswidth + 10) )
                    {//места под элементы меньше, чем они занимают - включаем отображение иконками
                        dockeditemswidth = 0;
                        // включение отображения иконками и просчет суммарной ширины в таком виде
                        Y.all('#dock .dockeditem').each(function(dockeditem){
                            var dockedtitle = dockeditem.one('.dockedtitle');
                            if ( dockedtitle !== null )
                            {
                                if( ! dockedtitle.hasClass('noicon') )
                                {
                                    dockedtitle.addClass('iconview');
                                    dockedtitle.addClass('texthide');
                                }
                            }
                            dockeditemswidth+=dockeditem.get('clientWidth');
                        });
                    }
                } else
                {// просчет суммарной ширины элементов док-панели
                    Y.all('#dock .dockeditem').each(function(dockeditem){
                        dockeditemswidth+=dockeditem.get('clientWidth');
                    });
                }

                // место, доступное под элементы док-панели
                var availableplace = dockfullwidth - headerwidth - (headerwidth > 0 ? 10 : 0) - langmenuwidth;
                if( availableplace < (dockeditemswidth + 10) )
                {//места под элементы меньше, чем они занимают - прячем заголовок
                    if( header !== null )
                    {
                        header.setStyle('display', 'none');
                    }
                    headerwidth = 0;
                }

                // подсчет итоговой ширины и позиции док-панели по горизонтали
                var left = positionX + headerwidth + (headerwidth > 0 ? 10 : 0);
                var width = dockfullwidth - headerwidth - (headerwidth > 0 ? 10 : 0) - langmenuwidth;

                // установка позиции док-панели
                if(dockdiv)
                {
                    if(body.hasClass('dir-rtl'))
                    {
                        dockdiv.setStyle('right', (left + langmenuwidth) + 'px');
                    } else
                    {
                        dockdiv.setStyle('left', left + 'px');
                    }

                    if (body.hasClass('ot-sticky-enabled'))
                    {
                        if (dockdiv.hasClass('ot-sticky'))
                        {
                            positionY = positionY - window.scrollY;

                        } else
                        {
                            if (dockbg !== null)
                            {
                                positionY = dockbg.get('clientTop');
                            }
                        }
                    }
                    dockdiv.setStyle('top', positionY + 'px');

                    dockdiv.setStyle('width', width + 'px');
                    dockdiv.setStyle('height', dockheight + 'px');
                }
            },
            isDockHasItems: function(){
                $('#page-header').toggleClass('dock-has-items', (this.dock.find('.dockeditem').length > 0));
            },
            hideItems: function(){
                this.dock.find('#dockeditempanel').addClass('dockitempanel_hidden');
                this.dock.find('#dockeditempanel .block').removeClass('active');
                this.dock.find('.buttons_container .dockeditem').removeClass('active');
                this.dock.find('.buttons_container .dockeditem .triangle').remove();
            },
            showItem: function(dockeditem){
                dockeditem.addClass('active');
                var triangle = $('<div>').addClass('triangle').appendTo(dockeditem);

                var dockeditempanel = this.dock.find('#dockeditempanel');
                dockeditempanel.removeClass('dockitempanel_hidden');
                this.dock.find('#dockeditempanel #'+dockeditem.attr('aria-controls')).addClass('active');

                var top = dockeditem.position().top + dockeditem.outerHeight() + 10;
                var right = this.dock.outerWidth() - (dockeditem.position().left + dockeditem.outerWidth());
                dockeditempanel.css({
                    'top': top + 'px',
                    'right': right + 'px',
                    'left': 'auto',
                    'max-height': 'calc(100vh - ' + (this.dock.position().top + top) + 'px)'
                });
                triangle.css({
                    'right': (right + 15) + 'px'
                });
                var offsetleft = dockeditempanel.offset().left;
                if (offsetleft < 0)
                {
                    dockeditempanel.css('right', right + offsetleft);
                }
            },
            initItems: function(){

                var self = this;

                // @TODO: начать вычислять размер менюшки и как следствие докедитемпанели, а не 300px
                $('#dock .block .action-menu-trigger').on({
                    'show.bs.dropdown': () => {
                        $('#dock #dockeditempanel').css('min-height', '300px');
                    },
                    'hide.bs.dropdown': () => {
                        $('#dock #dockeditempanel').css('min-height', 'auto');
                    },
                });

                $('#body-inner').click(function(e){
                    var target = $(e.target);

                    if (target.is('#dock #dockeditempanel') || target.parents('#dock #dockeditempanel').length > 0) {
                        return;
                    }

                    var dockeditem = null;
                    if (target.is('#dock .buttons_container .dockeditem'))
                    {
                        dockeditem = target;
                    } else
                    {
                        var parents = target.parents('#dock .buttons_container .dockeditem');
                        if (parents.length > 0)
                        {
                            dockeditem = parents.first();
                        }
                    }
                    if (dockeditem !== null)
                    {
                        var isactive = dockeditem.hasClass('active');
                        self.hideItems();
                        if (!isactive)
                        {
                            self.showItem(dockeditem);
                        }
                    } else {
                        self.hideItems();
                    }

                });

                var items = self.dock.find('.buttons_container .dockeditem');
                items.addClass('ready');
                self.__itemsCount = items.length;

            },
            setDockeditemIcon: function(dockedtitle, blockinstanceid){

                var self = this;

                if (self.blockicons.hasOwnProperty(blockinstanceid) &&
                    self.blockicons[blockinstanceid].icon !== undefined)
                {
                    // иконка уже подгрузилась - просто отобразим её
                    dockedtitle.css({
                        'background-image': 'url('+self.blockicons[blockinstanceid].icon+')'
                    });
                } else {
                    // иконка еще не подгрузилась - отобразим стандартную
                    dockedtitle.css({'background-image': 'url('+self.defaulticon+')'});
                    // а когда подгрузится, тогда отобразим нужную
                    self.blockicons[blockinstanceid].promise.then(function(icon){
                        dockedtitle.css({'background-image': 'url('+icon+')'});
                    });
                }

            },
            handleDroppedAndHit: function(e) {

                // дропнули в цель. Если убрали из дока, то спрячем докедитем, он больше не нужен

                var self = e.data.dock;

                var dragnode = $(e.detail.dragnode);
                var dropnode = $(e.detail.dropnode);

                var region = dropnode.data('blockregion');
                if (!region)
                {
                    region = dropnode.parent().data('blockregion');
                }

                var dockeditempanel = self.dock.find('#dockeditempanel');

                var dockeditem = $('.buttons_container .dockeditem[aria-controls="'+dragnode.attr('id')+'"]');
                if (region != 'dock')
                {
                    // дропнули блок в зону, которая не является доком
                    dockeditempanel.removeClass('ready-to-drop');
                    self.hideItems();
                    dockeditem.remove();
                    // посчитаем актуальное количество задокенных блоков и установим соответствующий класс
                    self.isDockHasItems();
                    self.handleResize();
                } else if(dockeditem.length == 0) {
                    // дропнули блок в док из другой зоны

                    var dockedtitleclasses = ['dockedtitle'];
                    var dockeditemsconfig = $('html').data('dockeditem-title');
                    if (dockeditemsconfig == 1 || dockeditemsconfig == 3)
                    {
                        dockedtitleclasses.push('iconview');
                        if (dockeditemsconfig == 1)
                        {
                            dockedtitleclasses.push('texthide');
                        }
                    }

                    var blockinstanceid = dragnode.attr('id').slice(4);
                    var dockeditemData = {
                        itemnum: ++self.__itemsCount,
                        title: dragnode.find('.card-title').html(),
                        blockinstanceid: blockinstanceid,
                        setting_dockeditemtitle: dockeditemsconfig
                    };

                    // если иконка уже получена, сразу отправим её в рендерер
                    // если нет, запросим после рендеринга
                    if (self.blockicons.hasOwnProperty(blockinstanceid) &&
                        self.blockicons[blockinstanceid].icon !== undefined)
                    {
                        dockeditemData.icon = self.blockicons[blockinstanceid].icon;
                    }

                    dockeditemData.dockedtitleclasses = dockedtitleclasses.join(' ');

                    var renderPromise = Templates.render('theme_opentechnology/dockeditem', dockeditemData);
                    $.when(renderPromise)
                        .done(function(dockeditemrender){
                            dockeditempanel.removeClass('ready-to-drop');
                            self.dock.find('.buttons_container .dockeditem_container').append(dockeditemrender);
                            self.handleResize();
                            var dockeditem = $('.buttons_container .dockeditem[aria-controls="'+dragnode.attr('id')+'"]');
                            if (!dockeditemData.hasOwnProperty('icon'))
                            {
                                // иконка до рендеринга не была определена - отобразим по возможности
                                self.setDockeditemIcon(dockeditem.children('.dockedtitle'), blockinstanceid);
                            }
                            dockeditem.addClass('ready').click();
                            // посчитаем актуальное количество задокенных блоков и установим соответствующий класс
                            self.isDockHasItems();
                            self.handleResize();

                        }).fail(function(e){
                            dockeditempanel.removeClass('ready-to-drop');
                            window.console.log(e);
                        });
                } else {
                    // дропнули блок в док, хотя отсюда, видать и брали
                    dockeditempanel.removeClass('ready-to-drop');
                    dockeditem.click();
                    // посчитаем актуальное количество задокенных блоков и установим соответствующий класс
                    self.isDockHasItems();
                    self.handleResize();
                }

            },
            handleDragStartedEarly: function(e) {

                // при начале перемещения - отобразим докпанель, чтобы было куда дропать

                var self = e.data.dock;
                self.hideItems();

                // притворяемся, что в доке есть итемы, чтобы было куда дропать драгаемый блок
                $('#page-header').addClass('dock-has-items');
                self.handleResize();

                var dockeditempanel = self.dock.find('#dockeditempanel');
                dockeditempanel.addClass('ready-to-drop');

                var buttonscontainer = $('#dock .buttons_container');
                var top = buttonscontainer.position().top + buttonscontainer.outerHeight();
                dockeditempanel.css({
                    'top': top + 'px',
                    'right': '0'
                });

                var dragnode = $(e.detail.dragnode);
                var blockinstanceid = dragnode.attr('id').replace(/inst/i, '');
                if (!self.blockicons.hasOwnProperty(blockinstanceid))
                {
                    self.blockicons[blockinstanceid] = {
                        promise: new Promise(function(resolve, reject){
                            ajax.call([{
                                methodname : 'theme_opentechnology_get_dock_icon',
                                args: {
                                    courseid: self.courseid,
                                    pagelayout: self.pagelayout,
                                    pagetype: self.pagetype,
                                    subpage: self.subpage,
                                    contextid: self.contextid,
                                    blockinstanceid: blockinstanceid
                                },
                                fail: function(ex){
                                    notification.exception(ex);
                                    reject(ex);
                                },
                                done: function(response){
                                    // сразу создаем объект изображения, чтобы картинка подгрузилась как можно раньше
                                    var icon = new Image;
                                    icon.onload = function(){
                                        self.blockicons[blockinstanceid].icon = this.src;
                                        resolve(this.src);
                                    };
                                    icon.src = response;
                                }
                            }]);
                        })
                    };
                }
            },
            handleDraggedOverDropTarget: function(e) {
                // блок над доком! здесь обработка, чтобы блок стало видно в док-панели
                var dragnode = $(e.detail.dragnode);
                if (dragnode.parents('#dock').length > 0)
                {
                    dragnode.addClass('active');
                }
            },
            init: function(courseid, pagetype, pagelayout, subpage, contextid, defaulticon) {

                this.courseid = courseid;
                this.pagetype = pagetype;
                this.pagelayout = pagelayout;
                this.subpage = subpage;
                this.contextid = contextid;
                this.defaulticon = defaulticon;

                this.dock = $('#dock');
                $('body').addClass('has_dock').addClass('has_dock_top_horizontal');
                this.isDockHasItems();
                this.handleResize();
                this.initItems();
                $(window).on('block_drag_started_early', {dock: this}, this.handleDragStartedEarly);
                $(window).on('block_dragged_over_drop_target', {dock: this}, this.handleDraggedOverDropTarget);
                $(window).on('block_dropped_and_hit', {dock: this}, this.handleDroppedAndHit);

                // сразу создаем объект изображения, чтобы картинка подгрузилась как можно раньше
                var icon = new Image;
                icon.src = this.defaulticon;
            }
        };
    }
);