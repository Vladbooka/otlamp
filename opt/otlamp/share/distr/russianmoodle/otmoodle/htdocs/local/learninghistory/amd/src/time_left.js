define(['jquery', 'core/ajax', 'core/templates'], function($, ajax, templates) {
    return {
        _timer: null,
        _delay: null,
        _userid : null,
        _courseid: null,
        _totaltime: 0,
        startRefresh: function() {
            var refresh = this;
            // Запускаем повторение функции _refreshTimer с интервалом refresh._delay
            refresh._timer = setInterval(function() {
                refresh._refreshTimer();
            }, refresh._delay);
        },

        stopRefresh: function() {
            var refresh = this;
            // Отключаем повторение вызовов функции в setInterval
            clearInterval(refresh._timer);
        },

        _refreshTimer: function() {
            var refresh = this;
            var response = ajax.call([{
                methodname : 'local_learninghistory_get_current_activetime',
                args: {
                    'userid': refresh._userid,
                    'courseid': refresh._courseid
                }
            }]);
            response[0]
                .done(function(response) {
                    var activetimedata = $.parseJSON(response);
                    var t = refresh.getTimeRemaining(activetimedata.activetime);

                    var activetimestr;
                    if( t.total > 0 || (t.total === 0 && refresh._totaltime === 0) )
                    {
                        activetimestr = refresh.rusDateInterval(t);
                    } else
                    {
                        clearInterval(refresh._timer);
                        activetimestr = 'Время вышло';
                    }

                    var templatedata = {
                        'activetimestr': activetimestr,
                        'atlastupdate': activetimedata.atlastupdate
                    };
                    templates.render('local_learninghistory/timeleft', templatedata)
                        .then(function(html, js) {
                            templates.replaceNodeContents($('.block.activetimer > .card-body > .card-text > .content'), html, js);
                        });
                })
                .fail(function(ex) {
                    // Если получили ошибку при обработке аякс запроса - выведем стек в консоль
                    window.console.log(ex.message);
                });
        },

        getTimeRemaining: function (activetime) {
            var refresh = this;
            var t;
            if( refresh._totaltime > 0 )
            {
                t = refresh._totaltime - activetime;
                if ( t < 0 )
                {
                    t = 0;
                }
            } else
            {
                t = activetime;
            }

            var seconds = Math.floor( (t) % 60 );
            var minutes = Math.floor( (t/60) % 60 );
            var hours = Math.floor( (t/(60*60)) % 24 );
            var days = Math.floor( t/(60*60*24) );
            return {
                'total': t,
                'days': days,
                'hours': hours,
                'minutes': minutes,
                'seconds': seconds
            };
        },

        rusDateInterval: function(t) {
            var refresh = this;
            var format = [];

            if(t.days !== 0) {
                format.push(refresh.rusNum(t.days, ['день','дня','дней']));
            }
            if(t.hours !== 0) {
                format.push(refresh.rusNum(t.hours, ['час','часа','часов']));
            }
            if(t.minutes !== 0) {
                format.push(refresh.rusNum(t.minutes, ['минута','минуты','минут']));
            }
            if(t.days === 0 && t.hours === 0 && t.minutes === 0) {
                format.push(refresh.rusNum(t.seconds, ['секунда','секунды','секунд']));
            }

            return format.join(' ');
        },

        rusNum: function(num, word) {
            if (num % 10 == 1 && num % 100 != 11)
            {
               return num + ' ' + word[0];
            } else if (num % 10 >= 2 && num % 10 <= 4 && (num % 100 < 11 || num % 100 > 14) )
            {
               return num + ' ' + word[1];
            } else
            {
               return num + ' ' + word[2];
            }
        },

        /**
         * Базовая инициализация
         */
        init: function(delay, totaltime, userid, courseid) {
            // Задержка должна быть в милисекундах
            this._delay = parseInt(delay) * 1000;
            if(isNaN(this._delay) || this._delay < 20000)
            {// Если задержка между запросами не пришла или меньше 20 сек
                // Выставим принудительно 20 сек (чаще нет смысла, запуск крона чаще раза в минуту не происходит)
                this._delay = 20000;
            }
            this._userid = userid;
            this._courseid = courseid;
            this._totaltime = totaltime;
            var refresh = this;

            refresh._refreshTimer();

            $(function() {
                // Запуск аякса после загрузки страницы
                refresh.startRefresh();
                $(window).blur(function() {
                    // При переключении вкладки перестаем посылать аякс запросы
                    refresh.stopRefresh();
                });
                $(window).focus(function() {
                    // При возврате во вкладку снова включаем отправку аякс запросов
                    refresh.startRefresh();
                });
            });
        }
    };
});