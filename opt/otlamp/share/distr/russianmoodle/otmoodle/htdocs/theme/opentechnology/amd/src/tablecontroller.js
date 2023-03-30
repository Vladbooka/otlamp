define(['jquery'], function($) {

    return {
        __table: null,

        /**
         * Проверка видимости элемента на странице
         */
        isVisible : function() {
            var target = this.__table;
            var visible = true;
            var rightPosition = target.width() + target.offset().left;
            var screenWidth = $(window).width();
            if ( screenWidth < rightPosition )
            {
                //Таблица не влезает по ширине
                visible = false;
            }
            var topPosition = target.height() + target.offset().top;
            var screenHeight = $(window).height();
            if ( screenHeight < topPosition )
            {
                //Таблица не влезает по высоте
                visible = false;
            }
            return visible;
        },

        dropController : function() {
            var target = this.__table;
            if ( target.parent().is('div.tablecontroller_wrapper') )
            {
                target.unwrap();
            }
        },

        setController : function() {
            var target = this.__table;
            this.dropController(target);

            var wrapper = $('<div class="tablecontroller_wrapper"></div>');
            wrapper.css({
                'overflow': 'auto',
                'position' : 'relative',
                'max-height' : $(window).height() * 0.8 + 'px'
            });
            target.wrap(wrapper);

            var topHeaderCells = target.find('thead > tr > th');

            var leftHeaderCells = [];
            target.find('tbody > tr').each(function() {
                $(this).children('td,th').each(function(){
                    if( $(this).prop("tagName").toLowerCase() == 'th' )
                    {
                        if( leftHeaderCells instanceof $ && leftHeaderCells.length > 0 )
                        {
                            leftHeaderCells = leftHeaderCells.add($(this));
                        } else
                        {
                            leftHeaderCells = $(this);
                        }
                    } else
                    {
                        return false;
                    }
                });
            });

            target.parent().on('scroll', function() {
                if( topHeaderCells instanceof $ && topHeaderCells.length > 0 )
                {
                    topHeaderCells.addClass('fixed');
                    topHeaderCells.css({
                        'top': -1 * target.position().top + 'px',
                        'position': 'relative',
                        'z-index' : 9999
                    });
                }
                if( leftHeaderCells instanceof $ && leftHeaderCells.length > 0 )
                {
                    leftHeaderCells.addClass('fixed');
                    leftHeaderCells.css({
                        'left': -1 * target.position().left + 'px',
                        'position': 'relative',
                        'z-index' : 9998
                    });
                }
            });
        },

        /**
         * Инициализация контроллера для конкретной таблицы
         */
        initSingleController : function(table) {
            var tableController = this;
            tableController.__table = table;
            $(window).on('resize', function() {
                if ( tableController.isVisible() )
                {
                    // Удаление контроллера для полностью отображаемых таблиц
                    tableController.dropController();
                } else
                {
                    // Добавление контроллера для широких таблиц
                    tableController.setController();
                }
            });
            $(window).resize();
        },

        /**
         * Базовая инициализация контроллера таблиц
         */
        init: function() {
            var tableController = this;
            $('table.generaltable').each(function(){
                tableController.initSingleController($(this));
            });
        }
    };
});