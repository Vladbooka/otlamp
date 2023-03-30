require(['jquery'], function($) {

	// добавление загрузочного окна
	$(document).off('displayLoading').on('displayLoading', function() {
		if( $('.journal-loading-screen-wrapper').length == 0 )
		{
	    	var loading = $('<div>')
				.addClass('journal-loading-screen-wrapper')
				.appendTo('body');
	    	$('<div>')
				.addClass('journal-loading-screen-loader')
				.appendTo(loading);
		}
	});
	
	// удаление загрузочного окна
	$(document).off('removeLoading').on('removeLoading', function() {
		$('body > div.journal-loading-screen-wrapper').remove();
	});
	
	// добавление анимации загрузки iframe
	$(document).off('addIframeLoading').on('addIframeLoading', function(event, elem) {
		if ( elem != undefined ) {
			
			// кладем загрузочный блок в элемент
			var loader = $('<div>')
				.addClass('journal-loading-screen-wrapper-in-elem')
				.appendTo(elem);
			
			// добавляем элемент загрузки
			$('<div>')
				.addClass('journal-loading-screen-loader')
				.appendTo(loader);
			
			// по готовности скрываем загрузчик
			setTimeout(function () {
				$(elem).find('iframe').ready(function () { 
					loader.fadeOut('slow') 
					});
			}, 1000);
		}
	});
});