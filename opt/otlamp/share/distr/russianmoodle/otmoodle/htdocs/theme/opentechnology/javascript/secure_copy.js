// Поддержка языковой панели
require(['jquery', 'core/str'], function($, str) {
	$(document).ready(function () {
	    $('body').bind('cut copy', function (e) {
	        e.preventDefault();
	        str.get_string('security_copy_copy_message', 'theme_opentechnology').done(function(s) {
	            alert(s);
	         }).fail(console.log(e));
	    });
	})
});