define(['jquery'], function($) {
    return {
        init: function(){
            // Модальное окно галереи
            var galmodal = $('#crw_ci_image_preview_modal_wrap').click(function(){
                $(this).hide();
            });
            // Блок превью
            var galpreview = $('#crw_ci_image_preview').click(function(){
                galmodal.show();
            });

            $('.crw_ci_courseblock_image').each(function(){
                $(this).click(function(){
                    galpreview.css('background-image', 'url(' + $(this).data('src') +' )');
                    $('#crw_ci_image_preview_modal').css('background-image', 'url(' + $(this).data('src') +' )');
                });
            });
        }
    };
});