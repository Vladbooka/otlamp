define(['jquery', 'core/ajax', 'core/notification', 'core/str'], function($, ajax, notification, str){
    return {
        init: function() {
            $('.mform.otcustomform').submit(function(e) {
                e.preventDefault();
                var form = $(this);
                var errors = $(this).find('.error');
                if (errors.length < 1) {
                    form.addClass('loading');
                    var data = $(this).serializeArray().reduce(function(obj, item) {
                        obj[item.name] = item.value;
                        return obj;
                    }, {});
                    var id = parseInt($(this).closest('.block_otcustomform').attr('id').replace(/inst/, ''));
                    var responses = ajax.call([{
                        methodname : 'block_otcustomform_save_data',
                        args: {
                            'id' : id,
                            'data': JSON.stringify(data)
                        }
                    }]);
                    responses[0]
                    .done(function () {
                        str.get_strings([
                            { key: 'formsaved', component: 'block_otcustomform' }
                        ]).done(function(strs) {
                            notification.alert('', strs[0]);
                        }).fail(notification.exception)
                        .always(function(){
                            form.removeClass('loading');
                        });
                    })
                    .fail(function () {
                        str.get_strings([
                            { key: 'formsavefailed', component: 'block_otcustomform' }
                        ]).done(function(strs) {
                            notification.alert('', strs[0]);
                        }).fail(notification.exception)
                        .always(function(){
                            form.removeClass('loading');
                        });
                    });
                }
            });
        }
    };
});