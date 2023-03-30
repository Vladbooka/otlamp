define(['jquery'], function($) {
    return {
        reset: function(resbutton) {
            var form = resbutton.closest('#crw_formsearch');
            var defaultvalue;
            if( form.length !== 0 )
            {
                // Выставляем дефолтные значения для текстовых полей
                form.find('input[type="text"]').each(function() {
                    defaultvalue = $(this).attr('data-default');
                    $(this).val(defaultvalue == undefined ? '' : defaultvalue);
                });
                // Выставляем дефолтные значения для выпадающих списков
                form.find('form > .custom-field select').each(function() {
                    $(this).val($(this).children('option:first').val());
                });
                // Выставляем дефолтные значения для дат
                var mindateday = form.find('.crw_system_search_form_mindate select[name="bottomdate[mindate][day]"]');
                var mindatemonth = form.find('.crw_system_search_form_mindate select[name="bottomdate[mindate][month]"]');
                var mindateyear = form.find('.crw_system_search_form_mindate select[name="bottomdate[mindate][year]"]');
                var mindateenabled = form.find('.crw_system_search_form_mindate input[name="bottomdate[mindate][enabled]"]');
                var maxdateday = form.find('.crw_system_search_form_maxdate select[name="bottomdate[maxdate][day]"]');
                var maxdatemonth = form.find('.crw_system_search_form_maxdate select[name="bottomdate[maxdate][month]"]');
                var maxdateyear = form.find('.crw_system_search_form_maxdate select[name="bottomdate[maxdate][year]"]');
                var maxdateenabled = form.find('.crw_system_search_form_maxdate input[name="bottomdate[maxdate][enabled]"]');
                var date = new Date(mindateday.attr('data-default') * 1000);
                mindateday.val(date.getDate());
                mindatemonth.val(date.getMonth()+1);
                mindateyear.val(date.getFullYear());
                mindateenabled.attr('checked', false);
                maxdateday.val(date.getDate());
                maxdatemonth.val(date.getMonth()+1);
                maxdateyear.val(date.getFullYear());
                maxdateenabled.attr('checked', false);
                // Выставляем дефолтные значения для селектов
                form.find('.fitem[data-groupname^="searchform_coursecontact_group"] select').each(function() {
                    defaultvalue = $(this).attr('data-default');
                    if( defaultvalue !== undefined )
                    {
                        $(this).val(defaultvalue);
                    }
                });
                // Выставляем дефолтные значения для автокомплита с тегами
                $('select#id_searchform_filter_tag option[selected]').each(function() {
                    $(this).closest('.felement').find('span[data-value="' + $(this).attr('value') + '"]').remove();
                    $(this).removeAttr("selected");
                });
                $('[role=listitem]').click();
            }
        },
        /**
         * Базовая инициализация
         */
        init: function() {
            var obj = this;
            $('.crw_system_search_form_resetbutton').click(function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                obj.reset($(this));
            });
        }
    };
});