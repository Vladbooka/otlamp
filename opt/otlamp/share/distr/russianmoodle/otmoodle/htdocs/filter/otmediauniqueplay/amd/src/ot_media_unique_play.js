define(['jquery'], function($) {
    return {
        /**
         * Базовая инициализация
         */
        init: function(){
            var tags = $('video,audio');
            tags.bind('play', function(){
                tags.not(this).trigger('pause');
            });
        },
    };
});