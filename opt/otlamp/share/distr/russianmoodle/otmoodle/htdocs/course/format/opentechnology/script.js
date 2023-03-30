
// Карусель
require(['jquery', 'core/str'], function($, strman){
	$(function(){
		
		$(window).bind('hashchange', function(){
			// При изменении секции через хэш - обрабатываем
			setHashSlide();
		});
		
		// Все секции курса (кроме нулевой)
		var courseSections = $('ul.format_opentechnology_sections.format_opentechnology_carousel.not-editing > li.section:not(#section-0)');
		
		var historyPushState = function(sectionNumber){
			if(history.pushState) {
			    history.pushState(null, null, '#section-'+sectionNumber);
			}
			else {
			    location.hash = '#section-'+sectionNumber;
			}
		}
		
		var getHashSection = function(){
			var hash = window.location.hash.slice(1);
			var sectionNumber = hash.split('-')[1];
			if( $.isNumeric(sectionNumber) )
			{
				var sectionSlide = $('ul.format_opentechnology_sections.format_opentechnology_carousel.not-editing .slideSection'+parseInt(sectionNumber));
				if(sectionSlide.length > 0)
				{
					return sectionNumber;
				}
			}
			return false;
		}
		
		// Функция выбора секции из хэша
		var setHashSlide = function()
		{
			var slideToSelect = $('ul.format_opentechnology_sections.format_opentechnology_carousel.not-editing .slideNumber1');
			var hashSection = getHashSection();
			if ( hashSection !== false )
			{
				slideToSelect = $('ul.format_opentechnology_sections.format_opentechnology_carousel.not-editing .slideSection'+parseInt(hashSection));
			}
			
			slideToSelect.click();
		};
		
		// Функция выбора текущего слайда
		var carouselSelectSlide = function(slideNumber)
		{
			// Сброс классов секций
			courseSections
				.removeClass('preparetoprev')
				.removeClass('preparetonext')
				.removeClass('selected');
			
			// Сброс классов переключалок 
			$('.slideNumber.selected')
				.removeClass('selected')
				.css('flex-grow', courseSections.length);
			
			// Отмечаем слайд выбранным
			var selectedSlide = $('.slideNumber'+slideNumber)
				.addClass('selected')
				.css('flex-grow', courseSections.length*2);

			// Подготавливаем данные для переключения на слайд вперед
			var nextSlideNumber = slideNumber+1;
			if( $('.slideNumber'+nextSlideNumber).length == 0 )
			{// слайда с номером +1 не существует
				nextSlideNumber = 1;
			}
			$('.slidenext').data('slideNumber', nextSlideNumber);

			// Подготавливаем данные для переключения на слайд назад
			var prevSlideNumber = slideNumber-1;
			if( $('.slideNumber'+prevSlideNumber).length == 0 )
			{// слайда с номером -1 не существует
				prevSlideNumber = $('#course-format-tools').children('div.slideNumber').length;
			}
			$('.slideprev').data('slideNumber', prevSlideNumber);

			var hashSection = getHashSection();
			// Отображаем нужные секции
			$.each(selectedSlide.data('slideSections'), function(index, sectionNumber){
				$('#section-'+sectionNumber)
					.addClass('preparetonext')
					.addClass('selected')
					.css('top','auto');
				
				if( ( $.inArray(hashSection, selectedSlide.data('slideSections')) == -1 && index == 0 )
						|| hashSection == sectionNumber )
				{
					$('html,body').stop().animate({ 
						scrollTop: $('#section-'+sectionNumber).offset().top - 60 
					}, 1000);

					historyPushState(sectionNumber);
				}
				
			});
		}
		
		// Функция инициализации переключалок карусели
		var carouselInit = function(callback)
		{
            var stringkeys = [
                {
                    key: 'slideprev',
                    component: 'format_opentechnology'
                },
                {
                    key: 'slidenext',
                    component: 'format_opentechnology'
                }
            ];
			strman.get_strings(stringkeys).then(function(langStrings){

				// Слой для расположения инструментов курса
				var cft = $('#course-format-tools').addClass('slideNumbers');
				
				// Текущий порядковый номер слайда
				var slideNumber = 0;
				
				// Добавление переключалки на предыдущий слайд
				$('<div>')
					.html('')//&#9668;
					.addClass('slideprev')
					.data('slideNumber',1)
					.on('click', function(e){
						carouselSelectSlide($(this).data('slideNumber'));
					})
					.attr('title', langStrings[0])
					.appendTo(cft);
				
				courseSections.each(function(index){
					
					var courseSection = $(this);
					// номер секции
					var courseSectionNum = courseSection.attr('id').split('-')[1];

					if( ! $.isNumeric(courseSectionNum) )
					{
						return;
					}
					
					if ( typeof slideSections == "undefined" )
					{
						// инициализация массива секций, отображающихся на одном слайде
						slideSections = [];
					}
					slideSections.push(courseSectionNum)
					
					
					
					if ( courseSection.hasClass('cf_ot_section_lastinrow') || courseSections.length == (index + 1) )
					{// Слайд завершен 

						// Добавляем слайд в переключалку с указанием его секций
						$('<div>')
							.addClass('slideNumber')
							.addClass('slideNumber'+(++slideNumber)) // класс с номером слайда
							.addClass('slideSection'+slideSections.join(' slideSection')) // классы с номерами секций в слайде
							.data('slideNumber',slideNumber)
							.data('slideSections',slideSections)
							.css('flex-grow', courseSections.length)
							.text(slideSections.join(' | '))
							.on('click',function(e){
								carouselSelectSlide($(this).data('slideNumber'));
							})
							.appendTo(cft);
						
						// Обнуляем массив с секциями слайда
						slideSections = [];
					}
					
				});
				

				// Добавление переключалки на следующий слайд
				$('<div>')
					.html('')//&#9658;
					.addClass('slidenext')
					.data('slideNumber',courseSections.length)
					.on('click', function(e){
						carouselSelectSlide($(this).data('slideNumber'));
					})
					.attr('title', langStrings[1])
					.appendTo(cft);
				
				// Формирвоание переключалки слайдов для размещения под контентом
				var cftbottom = $('<div>')
					.addClass('slideNumbers')
					.attr('id','course-format-tools-bottom')
					.appendTo(cft.parent());
				cft.children('div').each(function(){
					$(this).clone()
						.data('slideNumber', $(this).data('slideNumber'))
						.data('slideSections', $(this).data('slideSections'))
						.appendTo(cftbottom)
						.on('click', function(e){
							carouselSelectSlide($(this).data('slideNumber'));
						})
				})
				
				if ( callback !== undefined )
				{
					callback();
				}
			});

		}
		
		if( $('ul.format_opentechnology_sections.format_opentechnology_carousel.not-editing').length > 0 )
		{// формат курса настроен в режиме карусели
			carouselInit(function() {
				// Установка секции, пришедшей из хэша
				setHashSlide();
				// Удаление анимации загрузки 
				$('ul.format_opentechnology_sections.format_opentechnology_carousel.not-editing .format_opentechnology_carousel_loader').remove();
			});
		} 
		
	});
});


//Смена секции для аккордеона
require(['jquery'], function($){
	$(function(){
		
		$(window).bind('hashchange', function(){
			// При изменении секции через хэш - обрабатываем
			setHashSlide();
		});
		
		// Функция выбора секции из хэша
		var setHashSlide = function()
		{
			var hash = window.location.hash.slice(1);
			var sectionNumber = hash.split('-')[1];
			if( ! $.isNumeric(sectionNumber) )
			{
				sectionNumber = 1;
			}

			if( $('ul.format_opentechnology_sections.format_opentechnology_accordion.not-editing').length > 0 )
			{// формат курса настроен в режиме аккордеона
				$('#section-'+parseInt(sectionNumber)+' .content .sectionhead.toggle').click();
			}
		};

		if( $('ul.format_opentechnology_sections.format_opentechnology_accordion.not-editing').length > 0 )
		{
			setTimeout(setHashSlide,100);
		}
	})
});