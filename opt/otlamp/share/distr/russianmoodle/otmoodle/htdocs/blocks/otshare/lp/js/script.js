require(['jquery'], function($) 
{
    $('.block_otshare_lp_page_progressbar').each(function(){
        var percent = $(this).data('percent');
        $(this).addClass('jsed');
        var _this = this;
        setTimeout(function(){
            $('<div>')
                .addClass('after')
                .appendTo($(_this))
                .animate({
                    'width': percent + '%'
                }, 500);
        }, 300);
        
    });
});