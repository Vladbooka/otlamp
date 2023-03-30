define(['jquery'], function($) {

    return {
        body: $('body'),
        types: ['success', 'error', 'warning', 'info'],

        addMessage: function(type, string) {

            if ( this.types.indexOf(type) === -1 ) {
                type = 'fail';
            }

            var elem = $('<div/>', {
                'class': 'opentechnology-flashmessage-container moodle-has-zindex'
            }).append(
                $('<div/>', {
                    'class': 'opentechnology-flashmessage opentechnology-flashmessage-' + type,
                }).append($('<div/>', {
                    'class': 'opentechnology-flashmessage-progress'
                })).append($('<div/>', {
                    'class': 'opentechnology-flashmessage-message', 'text': string
            }))).appendTo(this.body);

            $(elem).find('.opentechnology-flashmessage-progress')
                .animate({width: 'toggle'}, 3000, function () {
                    $(elem).fadeOut();
                });
        }
    };
});
