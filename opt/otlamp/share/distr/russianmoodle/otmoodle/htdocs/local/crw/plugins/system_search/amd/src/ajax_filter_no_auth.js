define(['jquery', 'core/notification', 'core/ajax', 'core/templates'],
        function($, notification, ajax, templates) {
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
                    var response = ajax.call([ {
                        methodname : 'crw_system_search_ajax_filter_no_auth',
                        args : {
                            'params' : JSON.stringify(params)
                        }
                    } ]);
                    response[0]
                            .done(function(data) {
                                var jsNodes = $(data.js);
                                var allScript = '';
                                jsNodes.each(function(index, scriptNode) {
                                    scriptNode = $(scriptNode);
                                    var tagName = scriptNode.prop('tagName');
                                    if (tagName && (tagName.toLowerCase() == 'script')) {
                                        if (scriptNode.attr('src')) {
                                            // We only reload the script if it was not loaded already.
                                            var exists = false;
                                            $('script').each(function(index, s) {
                                                if ($(s).attr('src') == scriptNode.attr('src')) {
                                                    exists = true;
                                                }
                                                return !exists;
                                            });
                                            if (!exists) {
                                                allScript += ' { ';
                                                allScript += ' node = document.createElement("script"); ';
                                                allScript += ' node.type = "text/javascript"; ';
                                                allScript += ' node.src = decodeURI("' + encodeURI(scriptNode.attr('src')) + '"); ';
                                                allScript += ' document.getElementsByTagName("head")[0].appendChild(node); ';
                                                allScript += ' } ';
                                            }
                                        } else {
                                            allScript += ' ' + scriptNode.text();
                                        }
                                    }
                                });
                                templates.replaceNodeContents(showcase.get(0), data.html, allScript);
                            })
                            .always(function(){
                                showcase.removeClass('loading');
                            })
                            .fail(function(ex) {
                                notification.alert(ex.message);
                            });
                }
            });
        }
    };
});