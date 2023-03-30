define(['jquery', 'core/notification', 'core/fragment', 'core/templates'], function($, notification, fragment, templates) {
    return {

        /**
         * Базовая инициализация ajax-загрузки
         */
        init: function(contextid, categoryid) {
            $('#local_crw .crw_ptype_30 .crw_plugin_body form[data-ajax-filter="1"]').submit(function(e){
                e.preventDefault();

                $(this).data('submit-state', 'submit');
                if ($(this).data('submition-state') != 'submition')
                {
                    $(this).data('submition-state', 'submition');

                    var showcase = $(this).parents('#local_crw');
                    showcase.addClass('loading');

                    var params = {
                        'categoryid': categoryid,
                        'splobjecthash': showcase.data('object-id')
                    };
                    var formdata = $(this).serialize();
                    if (typeof formdata !== "undefined") {
                        params.jsonformdata = JSON.stringify(formdata);
                    }

                    fragment.loadFragment('crw_system_search', 'search', contextid, params)
                        .done(function(html, js) {
                            templates.replaceNodeContents(showcase.get(0), html, js);
                        })
                        .fail(notification.exception)
                        .always(function(){
                            showcase.removeClass('loading');
                        });

                }
            });
        }
    };
});