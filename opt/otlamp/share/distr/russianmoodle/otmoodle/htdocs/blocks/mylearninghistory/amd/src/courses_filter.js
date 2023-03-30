define(['jquery', 'local_opentechnology/admin_setting_button', 'core/ajax', 'core/notification'],
    function($, ASB, ajax, notification) {
    return {
        _id: null,

        init: function(id, dialogue_header, options) {

            var coursesFilter = this;

            ASB.init(id, dialogue_header).done(function(DObj) {

                DObj.addClass(options['class']);
                DObj.dialogue.set('width', '700px');

                var coursesFilterForm = ajax.call([{
                    methodname : options['methodname'],
                    args: {'config': options['config']}
                }]);

                $.when(coursesFilterForm[0])
                    .done(function(coursesFilterFormResponse) {
                        var content = $('<div>');
                        content.attr('id', id+'-dialogue-content-form-wrapper');
                        content.append(JSON.parse(coursesFilterFormResponse));

                        DObj.setContent(content).done(function(){
                            coursesFilter.initEvents(id);
                        });
                    })
                    .fail(function(ex) {
                        DObj.setContent('error');
                        notification.alert(ex.message);
                    });
            });


        },

        initEvents: function(id) {

            $('#'+id+'-dialogue-content-form-wrapper form').on('submit', function(e) {

                e.preventDefault();

                if ($(this).find('.error').length < 1) {

                    var data = $(this).serializeArray().reduce(function(obj, item) {
                        obj[item.name] = item.value;
                        return obj;
                    }, {});

                    $('#'+id).trigger('readyToSave', [data]);
                }
            });
        }
    };
});