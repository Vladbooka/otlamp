define(['jquery'], function($) {
	
	return {
		
		/**
		 * Базовая инициализация
		 */
        init: function(url) {
        	if (window.opener !== null) {
        		window.opener.location = url;
        		window.close();
        	} else {
        		window.location = url;
        	}
        }
	};
});