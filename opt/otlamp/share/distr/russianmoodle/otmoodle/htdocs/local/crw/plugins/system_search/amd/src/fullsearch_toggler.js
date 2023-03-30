define(['jquery'], function($) {
    return {
        init: function() {
            var morelink = '.crw_system_search_form .crw_system_search_form_morelink button';
            $('.crw_formsearch').find(morelink).click(function(){
                $(this).parents('.crw_formsearch').toggleClass('fullsearch');
            });
        }
    };
});