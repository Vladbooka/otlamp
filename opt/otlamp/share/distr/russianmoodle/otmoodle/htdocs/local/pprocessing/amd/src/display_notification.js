define(['jquery', 'core/ajax', 'core/str', 'core/notification'], function($, ajax, strman, notification) {

    return {
        /**
         * Базовая инициализация ajax-загрузки
         */
        init: function(type, heading, text) {
            console.log('display_notification', type, heading, text)
            switch(type)
            {
                case 'alert':
                    notification.alert(heading, text);
                    break;
                default:
                    break;
            }
        }
    };
});