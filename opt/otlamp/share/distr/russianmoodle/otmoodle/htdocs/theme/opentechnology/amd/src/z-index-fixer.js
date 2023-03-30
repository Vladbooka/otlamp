define(['jquery'], function($){
    return {
        'fix': function(selector) {
            $(selector).each(function(){
                var zindex = $(this).css('z-index');
                if ($.isNumeric(zindex))
                {
                    $(this).addClass('moodle-has-zindex');
                }
            });
        }
    };
});