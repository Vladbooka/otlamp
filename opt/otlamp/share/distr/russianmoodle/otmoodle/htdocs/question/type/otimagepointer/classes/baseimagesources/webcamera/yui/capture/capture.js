/**
 * Тип вопроса Объекты на изображении. JS-поддержка источника изображения.
 *
 * @package    qtype
 * @subpackage otimagepointer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

YUI.add('moodle-qtype_otimagepointer-imagesource_webcamera-capture', function(Y) {
	// Установка области видимости
	Y.namespace('Moodle.qtype_otimagepointer.imagesource_webcamera.capture');
	
	// Объявление глобальных переменных
	var CSS = {
	},
	SELECTORS = {
		MESSAGEBLOCK: '#imgs_wbc_messages',
		VIDEOBLOCK: '#imgs_wbc_video',
		CANVAS: '#imgs_wbc_canvas',
		CAPTUREBLOCK: '#imgs_wbc_capture',
		VCWRAPPER: '#imgs_wbc_capturewrapper',
		CONTROLWRAPPER: '#imgs_wbc_controlwrapper',
		CONTROLCAPTURE: '#imgs_wbc_control_capture',
		CAPTUREBUTTON: '#imgs_wbc_capturebtn',
		CONTROLSUBMIT: '#imgs_wbc_control_submit',
		CANCELBUTTON: '#imgs_wbc_cancelbtn',
		SUBMITBUTTON: '#imgs_wbc_submitbtn',
		CLOSEBUTTON: '#imgs_wbc_closebtn',
		FORM: '#imgs_wbc_form'
	};
	
	// Инициализация глобальных свойств
	navigator.getMedia = navigator.getUserMedia || 
    	navigator.webkitGetUserMedia || 
    	navigator.mozGetUserMedia || 
    	navigator.msGetUserMedia; 
	
	// Поддержка видеозахвата
	Y.Moodle.qtype_otimagepointer.imagesource_webcamera.capture = {
	
		messages: new Array(),
		
		init: function() {
			// Действия по нажатию кнопки закрытия окна
			Y.one(SELECTORS.CLOSEBUTTON).on('click', this.close);
			if (typeof navigator.getMedia === 'undefined'
					&& (typeof navigator.mediaDevices === 'undefined'
						 || typeof navigator.mediaDevices.getUserMedia === 'undefined'))
			{
				Y.one(SELECTORS.MESSAGEBLOCK).setHTML(
						'<h3 style="color:white;">No web API or your browser need to use a secure https connection</h3>');
			} else {
				// Подготовка блока трансляции видео
				this.startStreaming();
			}
		},
	    
		/**
		 * Попытка начать трансляцию
		 */
		startStreaming: function() {
			
			var helper = this;
			var videoElement = document.getElementById('imgs_wbc_video');
			var attempts = 0;
			var cameraStream;
			var spinner = M.util.add_spinner(Y, Y.one(SELECTORS.VCWRAPPER));
			var readyListener = function(event) {
	            findVideoSize();
	        };
	        var findVideoSize = function() {
	            if(videoElement.videoWidth > 0 && videoElement.videoHeight > 0) {
	                videoElement.removeEventListener('loadeddata', readyListener);
	                onDimensionsReady(videoElement.videoWidth, videoElement.videoHeight);
	            } else {
	                if(attempts < 10) {
	                    attempts++;
	                    setTimeout(findVideoSize, 200);
	                } else {
	                    onDimensionsReady(640, 480);
	                }
	            }
	        };
	        var onDimensionsReady = function(width, height) {
	        };
	        
	        spinner.show();
	        videoElement.autoplay = true;
	        videoElement.addEventListener('loadeddata', readyListener);
	        // Navigator.getUserMedia() is deprecated, advised to use MediaDevices.getUserMedia()
        	if (typeof navigator.mediaDevices === 'undefined'
				 || typeof navigator.mediaDevices.getUserMedia === 'undefined') {
        	    navigator.getMedia({
        	    	video : true,
    				audio : false
        	    }, function (stream) {
    				
    				spinner.hide();
    				
    				helper.prepeareStreaming();
    				
    				if ("srcObject" in videoElement) 
    				{
    					videoElement.srcObject = stream;
    				} else 
    				{
    					videoElement.src = window.URL.createObjectURL(stream);
    				}
    	            cameraStream = stream;
    	            videoElement.play();

    	        }, function(error) {
    	        	spinner.hide();
    				helper.riseMessage(error.message, 'error', 0);
    			});
        	} else {
        	    navigator.mediaDevices.getUserMedia({
        	    	video : true,
    				audio : false
        	    }).then(function (stream) {
    				
    				spinner.hide();
    				
    				helper.prepeareStreaming();
    				
    				if ("srcObject" in videoElement) 
    				{
    					videoElement.srcObject = stream;
    				} else 
    				{
    					videoElement.src = window.URL.createObjectURL(stream);
    				}
    	            cameraStream = stream;
    	            videoElement.play();

    	        }).catch(function(error) {
    	        	spinner.hide();
    				helper.riseMessage(error.message, 'error', 0);
    			});
        	}
		},
		
		/**
		 * Подготовка трансляции
		 */
		prepeareStreaming: function(e) {
			
			Y.one(SELECTORS.CONTROLCAPTURE).addClass('active');
			
			// Действия по нажатию кнопки захвата изображения
			Y.one(SELECTORS.CAPTUREBUTTON).on('click', this.capture);
			
			// Действия по нажатию кнопки сохранения изображения
			Y.one(SELECTORS.SUBMITBUTTON).on('click', this.submit);
			
			// Действия по нажатию кнопки повторного захвата изображения
			Y.one(SELECTORS.CANCELBUTTON).on('click', this.cancel);
		},
        
		/**
		 * Действия по захвату изображения
		 */
		capture: function(e) {
			
			// Инициализация переменных, методы не имеют обертки  в yui
			var video = document.getElementById('imgs_wbc_video'),
			    canvas = document.getElementById('imgs_wbc_canvas'),
				context = Y.one(SELECTORS.CANVAS).invoke('getContext', '2d');

			// Установка значений
			Y.one(SELECTORS.CANVAS).setAttribute("height", video.videoHeight + 'px');
			Y.one(SELECTORS.CANVAS).setAttribute("width", video.videoWidth + 'px');
			
			var v = document.getElementById("imgs_wbc_video");
			    var width = v.videoWidth,
			        height = v.videoHeight;
			
			// Формирование скриншота
		    context.drawImage(video, 0, 0, video.videoWidth, video.videoHeight);
		    // Запись base64 скриншота для сохранения
		    Y.one(SELECTORS.CAPTUREBLOCK).setHTML(canvas.toDataURL("image/png"));
		    
		    // Сразу отправим в форму, если стоит флаг на загрузку без подтверждения
		    if ( Y.one(SELECTORS.CONTROLCAPTURE).hasClass('force_save') ) {
		    	Y.one(SELECTORS.FORM).submit();
		    } else {
		    	// Отображение скриншота
			    Y.one(SELECTORS.VIDEOBLOCK).removeClass('active');
			    Y.one(SELECTORS.CANVAS).addClass('active');
			    
			    // Отображение блока управления изображением
			    Y.one(SELECTORS.CONTROLWRAPPER + ' .active').removeClass('active');
			    Y.all(SELECTORS.CONTROLSUBMIT).addClass('active');
		    } 	
		},
		
		/**
		 * Действия по захвату изображения
		 */
		submit: function(e) {
			// Отправка формы
			Y.one(SELECTORS.FORM).submit();
		},
		
		/**
		 * Действия по нажатию кнопки отмены
		 */
		cancel: function(e) {
			
			// Очистка поля сохранения
		    Y.one(SELECTORS.CAPTUREBLOCK).setHTML('');
		    
			// Отображение видео
		    Y.one(SELECTORS.VIDEOBLOCK).addClass('active');
		    Y.one(SELECTORS.CANVAS).removeClass('active');
		    
		    // Отображение блока звхвата изображением
		    Y.one(SELECTORS.CONTROLWRAPPER + ' .active').removeClass('active');
		    Y.all(SELECTORS.CONTROLCAPTURE).addClass('active');
		},
		
		/**
		 * Действия по закрытию окна
		 */
		close: function(e) {
			window.close_window(e);
		},
		
		/**
		 * Добавление сообщения
		 */
		riseMessage: function(messageText, messageType, messageLiveTime) {
			
			// Создание блока сообщения
			message = Y.Node.create('<div>' + messageText + '</div>');
			// Добавление класса
			switch(messageType) {
			  case 'error':
				  message.addClass('alert-danger')
				  break;
			  case 'notice':
				  message.addClass('alert-info')
				  break;
			  default:
				  message.addClass('alert-success')
			}
			// Добавление сообщения в стэк
			Y.one(SELECTORS.MESSAGEBLOCK).insert(message);
			
			if ( messageLiveTime ) {
				
			}
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