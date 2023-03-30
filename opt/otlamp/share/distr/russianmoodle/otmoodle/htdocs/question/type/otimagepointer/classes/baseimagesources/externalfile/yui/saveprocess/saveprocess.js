/**
 * Тип вопроса Объекты на изображении. JS-поддержка источника изображения.
 *
 * @package    qtype
 * @subpackage otimagepointer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

YUI.add('moodle-qtype_otimagepointer-imagesource_externalfile-saveprocess', function(Y) {
	
	// Установка области видимости
	Y.namespace('Moodle.qtype_otimagepointer.imagesource_externalfile.saveprocess');

	// Инициализация глобальных свойств
	window.URL = window.URL || 
		window.webkitURL || 
		window.mozURL || 
		window.msURL;
	navigator.getMedia = navigator.getUserMedia || 
    	navigator.webkitGetUserMedia || 
    	navigator.mozGetUserMedia || 
    	navigator.msGetUserMedia; 
	
	Y.Moodle.qtype_otimagepointer.imagesource_externalfile.saveprocess = {
		init: function() {
		},
		
		/**
		 * Действие по добавлению изображения
		 */
		savepostprocess: function(qaid, contenthash, pathnamehash) 
		{
			// Родительский блок
			var image = window.opener.Y.one('#qtype_otimagepointer_baseimage_' + qaid);
			var contenthashinput = window.opener.Y.one('#qtype_otimagepointer_baseimage_ch_' + qaid);
			var pathnamehashinput = window.opener.Y.one('#qtype_otimagepointer_baseimage_pathnamehash_ch_' + qaid);
			var edit = window.opener.Y.one('#image_editing_' + qaid);
			if ( image && contenthashinput ) {
				var url = Y.QueryString.parse(image.get('src'));
				var refresh = new Date().getTime();
				if ( url.refresh ) {
					url.refresh = refresh;
					var encoded = Y.QueryString.stringify(url);
					var decoded = decodeURIComponent(encoded);
					if ( image.set('src', decoded) ) {
						// Обновление значения хэша
						contenthashinput.set('value', contenthash);
						pathnamehashinput.set('value', pathnamehash);
						// Отображение изображения
						edit.removeClass('hidden');
					}
				}
		    }
			window.close();
		}
	};
	
}, '@VERSION@', {requires: ['node', 'event', 'querystring'] });