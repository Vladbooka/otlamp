define(['jquery'], function($) {
    return {
        init: function(objectid) {
            // Событие просмотра
            $('.otslider#' + objectid + ' .otslider_slide[data-type="image"][data-zoomview="1"]')
            .bind('click', function() {
                var imgurl = $(this).find('.otslider_image').first().css('background-image');
                // Контейнер для модального окна
                $('<div>')
                    .addClass('otslider_image_preview_modal_wrap moodle-has-zindex')
                    .bind('click', function() {
                        $(this).remove();
                    })
                    .appendTo('body');
                $('<div>')
                    .addClass('otslider_image_preview_modal moodle-has-zindex')
                    .css('background-image', imgurl)
                    .appendTo('.otslider_image_preview_modal_wrap');
            });
        }
    };
});