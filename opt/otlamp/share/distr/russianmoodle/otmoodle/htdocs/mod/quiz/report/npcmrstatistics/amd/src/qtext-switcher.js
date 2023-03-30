define(['jquery', 'core/notification', 'core/str'], function($, notification, strman){
    return {
        init: function() {
            var shortened = $('#questionnpcmrstatistics .qtext-cell .fulltext').parent().addClass('collapsed').length;
            if( shortened > 0 )
            {
                $('#questionnpcmrstatistics .qtext-cell .qtext-expander').click(function(){
                $(this).parent().toggleClass('collapsed expanded');
                });

                strman.get_strings([
                    { key: 'fulltext', component: 'quiz_npcmrstatistics' },
                    { key: 'shorttext', component: 'quiz_npcmrstatistics' }
                ]).done(function(strs) {

                    var multiexpanderstates = {
                        1: strs[0],
                        2: strs[1]
                    };

                    var multiexpander = $('<div>')
                        .addClass('btn btn-primary fulltext-toggler')
                        .text(multiexpanderstates[1])
                        .data('state', 1)
                        .click(function(){
                            if ($(this).data('state') == 1)
                            {
                                $('#questionnpcmrstatistics .qtext-cell .qtext-expander')
                                    .parent().removeClass('collapsed').addClass('expanded');
                                $(this).data('state', 2).text(multiexpanderstates[2]);
                            } else
                            {
                                $('#questionnpcmrstatistics .qtext-cell .qtext-expander')
                                    .parent().removeClass('expanded').addClass('collapsed');
                                $(this).data('state', 1).text(multiexpanderstates[1]);
                            }
                        });
                    $('.npcmrstatistics-tablecontainer').prepend(multiexpander);
                }).fail(notification.exception);
            }
        }
    };
});