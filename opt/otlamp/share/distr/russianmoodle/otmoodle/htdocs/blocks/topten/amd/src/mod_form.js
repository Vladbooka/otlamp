define(['jquery', 'core/ajax', 'core/fragment', 'core/templates', 'core/notification', 'core/str'],
        function($, ajax, fragment, templates, notification, strman) {
    return {
        _dialogue: $.Deferred(),
        _formElement: null,
        /**
         * Начальная инициализация
         */
        init: function () {
            var tmf = this;
            tmf.resetDeferred();
            tmf._formElement = $('form.mform.block_topten-js');
            // При смене селекта выбора отчета, редиректим с добавлением доп настроек
            tmf._formElement.find('fieldset select#id_config_rating_type').change(function () {
                var idValString = '';
                var idElement = tmf._formElement.find('input[name=id]');
                if ( idElement.length > 0  ) {
                    idValString = '&id=' + idElement.val();
                }
                var cidValString = '';
                var cidElement = tmf._formElement.find('input[name=cid]');
                if ( cidElement.length > 0 ) {
                    cidValString = '&cid=' + cidElement.val();
                }
                // При клике на отчет, редиректим для догрузки доп настроек
                window.location.replace(
                        tmf._formElement.attr('action') + '?' + 'bui_editid=' +
                            tmf._formElement.find('input[name=bui_editid]').val() +
                            '&sesskey=' + tmf._formElement.find('input[name=sesskey]').val()+
                            '&type=' + this.value + idValString + cidValString);
            });

            // Форма фильтрации курсов в списке курсов по выбранным критериям
            var button = tmf._formElement.find('.system_search_button');
            button.unbind('click').click(function() {
                var button = $(this);
                button.addClass('disabled');
                strman.get_strings([
                    { key: 'courses_search_filter_header', component: 'block_topten' },
                    { key: 'courses_search_filter_save', component: 'block_topten' },
                    { key: 'courses_search_filter_cancel', component: 'block_topten' }
                ]).done(function(strs) {
                    notification.confirm(strs[0],
                            '<div class="search-form-container loading"></div>', strs[1], strs[2], function() {
                        tmf.saveFormdata(
                            $('.search-form-container').find('form'),
                            $('form.mform.block_topten-js').find('input[name="config_courses_search_crws"]')
                        );
                    });
                    // не смог найти способ запустить обработку после отображения окна, 
                    // используется таймаут, пока не будет найдено решение получше
                    setTimeout(function(){
                        tmf._dialogue.resolve();
                    }, 500);
                }).fail(notification.exception)
                .always(function(){
                    button.removeClass('disabled');
                });
            });
            button.removeClass('disabled');
        },
        /**
         * Перезапуск defired
         */
        resetDeferred: function() {
            var tmf = this;
            tmf._dialogue = $.Deferred();
            $.when(tmf._dialogue).done(function() {
                    $('.search-form-container').closest('.moodle-dialogue-wrap').addClass('moodle-dialogue-top_ten-courses_search');
                    tmf.loadForm($('.search-form-container'));
                    tmf.resetDeferred();
            });
        },
        /**
         * Получение сохраненной строки поиска из скрытого инпута
         */
        getSavedCrws: function() {
            var element = $('form.mform.block_topten-js').find('input[name="config_courses_search_crws"]');
            if (element.length > 0 && element.val() != '') {
                return element.val();
            } else {
                return '';
            }
        },
        /**
         * Обработка формы поиска
         */
        processForm: function(done, formdata){
            var tmf = this;
            var contextid = tmf._formElement.find('input[name="contextid"]').val();
            var params = {
                'rating_type': 'courses_search',
                'output_fragment': 'search_form',
                'crws': tmf.getSavedCrws()
            };
            if (formdata !== undefined) {
                params.formdata = JSON.stringify(formdata);
            }
            fragment.loadFragment('block_topten', 'router', contextid, params)
                .done(done)
                .fail(notification.exception);
        },
        /**
         * Загрузка формы в контейнер
         */
        loadForm: function(container) {
            var tmf = this;
            tmf.processForm(function(result, js) {
                result = JSON.parse(result);
                templates.replaceNodeContents(container, result['html'], js);
                var crwform = $('.crw_system_search_form');
                crwform.submit(function (e) {
                    e.preventDefault();
                    var dialogholder = $('.moodle-dialogue-top_ten-courses_search');
                    dialogholder.find('.confirmation-dialogue > .confirmation-buttons > .btn-primary')
                    .trigger('click');

                });
                container.removeClass('loading');
            });
        },
        /**
         * Обработка данных формы и размещение строки поиска в скрытый элемент
         */
        saveFormdata: function(form, element){
            var tmf = this;
            var crws = '';
            if (form.length > 0) {
                tmf.processForm(function(result) {
                    result = JSON.parse(result);
                    crws = result['crws'];
                    if (element.length > 0) {
                        element.val(crws);
                    }
                }, form.serialize());
            }
        }
    };
});