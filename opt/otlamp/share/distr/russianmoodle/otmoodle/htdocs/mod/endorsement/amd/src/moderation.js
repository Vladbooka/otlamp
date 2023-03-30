define(['jquery', 'core/ajax', 'core/str', 'core/notification', 'core/fragment', 'core/templates'],
        function($, ajax, strman, notification, fragment, templates) {

    return {
        __contextid: null,
        __pageurl: null,

        /**
         * Инициализация инструментов для указанного объекта
         */
        initInstance: function(item){

            var moderation = this;

            var itemwrapper = item.parents('.mod_endorsement_item');
            item.parent().addClass('loading');
            var itemid = itemwrapper.data('item-id');
            var radiogroupname = 'item_' + itemid + '_status';

            item.find('input[type=radio][name='+radiogroupname+']').on('change', function(){
                $(this).closest('form').submit();
            });

            item.find('input[type=radio][name='+radiogroupname+'] + label').each(function(){
                $(this)
                    .attr('data-toggle','tooltip')
                    .prop('title',$(this).text())
                    .text('');

                $('<div>')
                    .addClass('statusimg')
                    .appendTo($(this));

                $(this).addClass('ready');
            });

            item.find('form').on('submit', function(e){
                e.preventDefault();

                var field = $(this).parents('.mod_endorsement_item_field.mod_endorsement_item_moderator_tools');
                field.parent().addClass('loading');

                var formData = $(this).serialize();
                if (typeof formData !== "undefined") {
                    var params = {
                        'jsonformdata': JSON.stringify(formData),
                        'itemid': itemid,
                        'baseurl': moderation.__pageurl
                    };

                    // Get the content of the modal.
                    fragment.loadFragment('mod_endorsement', 'change_status', moderation.__contextid, params)
                    .done(function(html, js) {
                        var formparent = $(this).parents('.mod_endorsement_item_field.mod_endorsement_item_moderator_tools');
                        templates.replaceNodeContents(formparent, html, js);
                        moderation.initInstance(formparent);
                    })
                    .fail(notification.exception)
                    .always(function(){
                        field.parent().removeClass('loading');
                    });
                }
            });

            item.parent().removeClass('loading');
        },

        /**
         * Базовая инициализация инструментов
         */
        init: function(contextid, pageurl) {

            var moderation = this;
            moderation.__contextid = contextid;
            moderation.__pageurl = pageurl;

            $('.mod_endorsement_list.moderatorside .mod_endorsement_item_moderator_tools').each(function(){
                moderation.initInstance($(this));
            });
        }
    };
});