define(['jquery', 'core/notification'], function($, notification) {
    return {
        display: function(heading, text, linktext, linkurl, closetext) {
            if (linktext !== null && linkurl !== null)
            {
                notification.confirm(heading, text, linktext, closetext, function() {
                    window.open(linkurl, '_blank');
                });
            } else
            {
                notification.alert(heading, text, closetext);
            }
        }
    };
});