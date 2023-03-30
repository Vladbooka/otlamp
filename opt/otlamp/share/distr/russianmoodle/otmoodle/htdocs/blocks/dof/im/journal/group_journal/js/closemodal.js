require(['jquery'], function($) {
	
	$(document).ready(function () {
		
		// Контроллер таблицы
		var controller = window.parent.document.GTController;
		if ( controller != 'undefined' ) {
			// Закрытие модальных окон
			controller.closeModals();
		}
	})
});
