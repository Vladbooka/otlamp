define(['jquery'], function($) {
    return {
        /**
         * Базовая инициализация ajax-загрузки
         */
        init: function() {

            var minform = $('#local_crw .crw_formsearch.minimalism');

            // селекты, автокомплиты, чекбоксы - отправляют форму по событию change
            var selects = [
                'form > .custom-field select',
                '.fitem[data-groupname^="searchform_coursecontact_group"] select',
                '.felement[data-fieldtype="autocomplete"] > select:not([name="topblock[crws]"])',
                '.fitem[data-groupname="bottomdate"] input[type="checkbox"]',
                '.felement select[name=searchform_sorttype]',
            ];
            minform.find(selects.join(',')).change(function(){
                if (minform.data('submit-state') != 'submit')
                {
                    minform.data('submit-state', 'submit').find('input[type=submit]').click();
                }
            });

            // даты
            var dates = [
                '.fitem[data-groupname="bottomdate"] select[name^="bottomdate"]'
            ];
            minform.find(dates.join(','))
                .focus(function(){
                    var fitem = $(this).parents('.fitem');
                    clearTimeout(fitem.data('submitFormTimeout'));
                })
                .blur(function(){
                    $(this).parents('.fitem').data('submitFormTimeout', setTimeout(function(){
                        if (minform.data('submit-state') != 'submit')
                        {
                            minform.data('submit-state', 'submit').find('input[type=submit]').click();
                        }
                    }, 500));
                })
                .change(function(){
                    var fitem = $(this).parents('.fdate_selector');
                    fitem.find('input[type="checkbox"]').prop('checked', true);
                });

            // текстовые поля и сами отправляют форму по Enter, надо только потерю фокуса добавить
            var texts = [
                '.fitem[data-groupname="topblock"] input[type="text"]',
                'form > .custom-field  input[type="text"]',
                '.fitem[data-groupname="bottomprice"] input',
            ];

            minform.find(texts.join(','))
                .focus(function(){
                    $(this).data('oldval', $(this).val());
                })
                .blur(function(){
                    if ($(this).val() != $(this).data('oldval'))
                    {
                        if (minform.data('submit-state') != 'submit')
                        {
                            minform.data('submit-state', 'submit').find('input[type=submit]').click();
                        }
                    }
                });
        }
    };
});