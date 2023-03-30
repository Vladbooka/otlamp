define(['jquery'], function($) {
	
	return {
		
		/**
		 * Базовая инициализация
		 */
        init: function(provider) {
        	var w = 800,
        	    h = 600,
        	    left = ($(window).width()/2)-(w/2),
        	    top = ($(window).height()/2)-(h/2),
        	    proxyurl = '/auth/otoauth/enter.php?provider=' + provider + '&popup=0';
    		var popup = window.open(proxyurl, "Request popup", "width=" + w + ",height=" + h + ",top=" + top +
    	        	",left=" + left + ",location=1,status=0,menubar=0,resizable=0,scrollbars=0");
    		if(popup == null || typeof(popup) == 'undefined'){
    			window.location = '/auth/otoauth/enter.php?provider=' + provider + '&popup=0';
    		} else {
    			popup.focus();
    		}
        }
	};
});