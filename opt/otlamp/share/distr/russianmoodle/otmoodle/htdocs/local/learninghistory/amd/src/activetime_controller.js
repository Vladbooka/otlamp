define(['core/ajax', 'jquery'], function(ajax, $) {
    return {
        _timer: null,
        _delay: null,
        _userid : null,
        _courseid: null,
        _contextid: null,
        _overiframe: false,
        startMonitoring: function() {
            var atmon = this;
            // включаем позитивную индикацию (пользователь должен видеть, что подсчет времени идет)
            $('.block.activetimer').removeClass('noprogress').addClass('inprogress');
            atmon._changeActivetime();
            // Запускаем повторение функции _changeActivetime с интервалом atmon._delay
            atmon._timer = setInterval(function() {
                atmon._changeActivetime();
            }, atmon._delay);
        },
        _changeActivetime: function() {
            var atmon = this;
            // Отправим аякс запрос нашему обработчику на бэкенде
            var response = ajax.call([{
                methodname : 'local_learninghistory_add_activetime_updated_log',
                args: {
                    'userid': atmon._userid,
                    'courseid': atmon._courseid,
                    'contextid': atmon._contextid
                }
            }]);
            response[0]
                .fail(function(ex) {
                    // Если получили ошибку при обработке аякс запроса - выведем стек в консоль
                    window.console.log(ex.message);
                });
        },
        stopMonitoring: function() {
            var atmon = this;
            if (atmon._overiframe) {
                // во время потери фокуса курсор был над iframe
                // считаем что мы по прежнему на странице и не отключаем мониторинг
                return;
            }
            // отключаем позитивную индикацию (пользователь должен видеть, что подсчет времени больше не идет)
            $('.block.activetimer').removeClass('inprogress').addClass('noprogress');
            // Отключаем повторение вызовов функции в setInterval
            clearInterval(atmon._timer);
        },
        /**
         * Базовая инициализация
         */
        init: function(delay, userid, courseid, contextid) {
            // Задержка должна быть в милисекундах
            this._delay = parseInt(delay) * 1000;
            if(isNaN(this._delay) || this._delay < 10000)
            {// Если задержка между запросами не пришла или меньше 10 сек
                // Выставим принудительно 10 сек
                this._delay = 10000;
            }
            this._userid = userid;
            this._courseid = courseid;
            this._contextid = contextid;

            var atmon = this;

            var vis = (function(){
                var stateKey,
                    eventKey,
                    keys = {
                            hidden: "visibilitychange",
                            webkitHidden: "webkitvisibilitychange",
                            mozHidden: "mozvisibilitychange",
                            msHidden: "msvisibilitychange"
                };
                for (stateKey in keys) {
                    if (stateKey in document) {
                        eventKey = keys[stateKey];
                        break;
                    }
                }
                return function(c) {
                    if (c) {document.addEventListener(eventKey, c);}
                    return !document[stateKey];
                };
            })();

            vis(function(){
                if(vis()){
                    setTimeout(function(){
                        atmon.stopMonitoring();
                        atmon.startMonitoring();
                    },300);
                } else {
                    atmon.stopMonitoring();
                }
            });

            $(function(){
                atmon.startMonitoring();
            });

            var notIE = (document.documentMode === undefined),
                isChromium = window.chrome;
            if (notIE && !isChromium) {

                // checks for Firefox and other  NON IE Chrome versions
                $(window).on("focusin", function () {
                    atmon.stopMonitoring();
                    atmon.startMonitoring();
                }).on("focusout", function () {
                    atmon.stopMonitoring();
                });
            } else
            {
                // checks for IE and Chromium versions
                if (window.addEventListener) {
                    // bind focus event
                    window.addEventListener("focus", function () {
                        atmon.stopMonitoring();
                        atmon.startMonitoring();
                    }, false);

                    // bind blur event
                    window.addEventListener("blur", function () {
                        atmon.stopMonitoring();
                    }, false);

                } else {
                    // bind focus event
                    window.attachEvent("focus", function () {
                        atmon.stopMonitoring();
                        atmon.startMonitoring();
                    });

                    // bind focus event
                    window.attachEvent("blur", function () {
                        atmon.stopMonitoring();
                    });
                }
            }
            // попытка отслеживать курсор над iframe чтобы игнорировать потерю фокуса в него
            $('iframe').on('mouseover', {'ctx': atmon}, function(event) {
                var atmon = event.data.ctx;
                atmon._overiframe = true;
            });
            $('iframe').on('mouseout', {'ctx': atmon}, function(event) {
                var atmon = event.data.ctx;
                atmon._overiframe = false;
                $(window).focus();
            });
        }
    };
});