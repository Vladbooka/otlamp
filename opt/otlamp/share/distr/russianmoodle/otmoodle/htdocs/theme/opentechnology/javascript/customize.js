require(['jquery'], function($){
	var footerresize = function(){
		var footerwrapperheight = 0;
		var footerborderheight = $('#page-footer .footerborder').outerHeight();
		$('#footer_wrapper > *').each(function(){
			footerwrapperheight += $(this).outerHeight();
		});
		$('#footer_wrapper').css('height', footerwrapperheight+'px');
		$('#body-inner').css('padding-bottom', footerwrapperheight+footerborderheight+'px');
		$('body:not(.pagelayout-maintenance) #page-footer').css('position', 'absolute');
	};
	footerresize();
	$(function(){
		footerresize();
	})
	$(window).resize(footerresize);
});

// Сворачиваемый раздел
require(['jquery', 'core/ajax'], function($, ajax) {
	
	var getPageLayout = function() {
		
		var classes = $('body').attr('class').split(' ');
		var result = 'base';
	    $(classes).each(function(index, value){
	    	if ( value.indexOf("pagelayout-") !== -1 ) {
				var layout = value.split('-');
				return result = layout[1];
			}
	    });
		return result;
	};

	$('.collapsible-section-switcher-slideup-label').click(function() {
		var switcher = $(this).parent().children('.collapsible-section-switcher').attr('checked','checked');
		var collapsibleSectionTop = $(this).parent().offset().top;
		if( $(document).scrollTop() > collapsibleSectionTop )
		{
			$('html, body').animate({
		        scrollTop: collapsibleSectionTop
		    }, 400);
		}
		$(this).parent().children('.collapsible-section-switcher-slideup-label').first().slideUp();
		$(this).parent().children('.collapsible-section-content').slideUp(function(){
			$(this).parent().removeClass('expanded').addClass('collapsed');
			switcher.trigger('click').removeAttr('checked'); // необходимо для корректного позиционирования док-панели
			save_cs_state(switcher.parent().data('collapsible-section'), 0);
		});
	});
	$('.collapsible-section-switcher-slidedown-label').click(function() {
		var switcher = $(this).parent().children('.collapsible-section-switcher').removeAttr('checked');
		$(this).parent().children('.collapsible-section-switcher-slideup-label').first().slideDown();
		$(this).parent().children('.collapsible-section-content').slideDown(function(){
			$(this).parent().removeClass('collapsed').addClass('expanded');
			switcher.trigger('click').attr('checked','checked'); // необходимо для корректного позиционирования док-панели
			save_cs_state(switcher.parent().data('collapsible-section'), 1);
		});
	});
	
	var save_cs_state = function(collapsiblesection, currentstate){
		var layout = getPageLayout();
		var requests = ajax.call([{
	        methodname : 'theme_opentechnology_set_collapsiblesection_state',
	        args: { 
	        	'collapsiblesection': collapsiblesection,
	        	'state': currentstate,
	        	'layout' : layout
        	}
	    }]);
	}
});

// Скрытие меню выбора языка при расфокусе
require(['jquery'], function($) {
	
	$('#dock_bg .langmenu_wrapper > label, #dock_bg .langmenu_wrapper select.langmenu').on('mouseleave', function(){
		var label = $('#dock_bg .langmenu_wrapper > label');
		label.data('timer', setTimeout(function () {
			$('#dock_bg .langmenu_wrapper > input').prop('checked', false);
		}, 1200));
	});
	
	$('#dock_bg .langmenu_wrapper > label, #dock_bg .langmenu_wrapper select.langmenu').on('focus mouseover', function(){
		var label = $('#dock_bg .langmenu_wrapper > label');
		if (label.data('timer')) {
			clearTimeout(label.data('timer'));
		}
	});
	
	$('#dock_bg .langmenu_wrapper select.langmenu').on('blur', function(){
		$('#dock_bg .langmenu_wrapper > input').prop('checked', false);
	});
	
});

// Слайдер страницы авторизации
require(['jquery'], function($) {
    
	var slides = $('.loginpage_slider_images > .loginpage_slider_image');
    var currentslideindex = 0;
    // Слайдер изображений
	if ( slides.length > 1 )
	{	
		setInterval(function() {
			slidefrom = slides[currentslideindex];
			if ( slides.length == ++currentslideindex )
			{
				currentslideindex = 0;
			}
			
			slideto = slides[currentslideindex];
			slidefrom.style.opacity = "0";
			slideto.style.opacity = "1";
		}, 5000);
	}
});


// Фиксация заголовков для таблицы в отчете по оценкам
require(['jquery'], function($)
{
	var fixElement = function(overflowedElementSelector, sourceElementSelector, cloneElementSelector, fixLeft, fixTop) 
	{
		if ( overflowedElementSelector == undefined )
		{// Не указан контейнер, в котором скроллится таблица
			overflowedElementSelector = null;
		}
		if ( sourceElementSelector == undefined )
		{// Не указана ячейка таблицы с дефолтным позиционированием
			sourceElementSelector = null;
		}
		if ( cloneElementSelector == undefined )
		{// Не указан объект-клон, который будем фиксировать
			cloneElementSelector = null;
		}
		if ( fixLeft == undefined )
		{// Не указано требуется ли фиксировать по горизонтали
			fixLeft = false;
		}
		if ( fixTop == undefined )
		{// Не указано требуется ли фиксировать по вертикали
			fixTop = true;
		}
		
		var overflowedElement = $(overflowedElementSelector);
		var sourceElement = $(sourceElementSelector);
		var cloneElement = $(cloneElementSelector);
		
		if( overflowedElement.length==0 || sourceElement.length==0 || cloneElement.length==0 )
		{// Не найдены ключевые элементы
			return;
		}
		
		// Величина прокрутки по вертикали
		var scrollTop = overflowedElement.scrollTop();
		var scrollLeft = overflowedElement.scrollLeft();
		
		// Позиция ячейки, которая должна быть зафиксирована
		var sourcePosition = sourceElement.position();
        var sourcePositionTop = (sourcePosition.top - scrollTop);
        var sourcePositionLeft = (sourcePosition.left - scrollLeft);
		var fixedTopPosition = (sourcePositionTop < 0 ? 0 : sourcePositionTop);
		var fixedLeftPosition = (sourcePositionLeft < 0 ? 0 : sourcePositionLeft);

		// Столбец не должен занимать больше половины ширины контейнера с прокруткой
		if ( fixLeft && cloneElement.width() > overflowedElement.width()/2 )
		{
			fixLeft = false;
		}
		// Строка не должна занимать больше половины высоты контейнера с прокруткой
		if ( fixTop && cloneElement.height() > overflowedElement.height()/2 )
		{
			fixTop = false;
		}
		
		if ( fixLeft && (sourceElement.width() + sourcePositionLeft) < cloneElement.children('div:first').width() )
		{// Ячейка ушла за пределы просматриваемой области по горизонтали
			cloneElement.addClass('otfloating');
		} 
		else if ( fixTop && sourcePositionTop < 0 )
		{// Ячейка ушла за пределы просматриваемой области по вертикали
			cloneElement.addClass('otfloating');
		} 
		else
		{// Ячейка в пределах просматриваемой области
			cloneElement.removeClass('otfloating');
		}
		
		// Установка положения по горизонтали для основного фиксируемого контейнера
		cloneElement.css({
			'left': ( fixLeft ? fixedLeftPosition : sourcePositionLeft ) + scrollLeft +'px',
			'top': ( fixTop ? fixedTopPosition : sourcePositionTop ) + scrollTop + 'px'
		});
	}
	
	$('body.path-grade-report-grader .gradeparent').scroll(function()
	{
		// Клонирование и отключение мудловского механизма
		$('body.path-grade-report-grader .floater').each(function()
		{
			$(this).clone()
				.removeClass('floater')
				.removeClass('floating')
				.addClass('otfloater')
				.appendTo('.gradeparent');
		}).remove();

		// ФИО пользователей
		fixElement(
			$(this), 
			'body.path-grade-report-grader #user-grades th.header.cell.user:first',
			'body.path-grade-report-grader .otfloater.sideonly:not(.avg):not(.heading):not(.controls)',
			true,
			false
		);
		var shw = $('body.path-grade-report-grader #user-grades th.header.cell.user:first-child');
		var shh = $('body.path-grade-report-grader #user-grades th.header.cell#studentheader');
		$('body.path-grade-report-grader .otfloater.sideonly.heading').css({
			'width': shw.outerWidth()+'px',
			'height': shh.outerHeight()+'px'
		});
		fixElement(
			$(this), 
			'body.path-grade-report-grader #user-grades th.header.cell#studentheader',
			'body.path-grade-report-grader .otfloater.sideonly.heading',
			true,
			true
		);
		// Сводные данные
		fixElement(
			$(this), 
			'body.path-grade-report-grader #user-grades th.header.cell.range',
			'body.path-grade-report-grader .otfloater.sideonly.avg',
			true,
			true
		);
		// Строка заголовков
		fixElement(
			$(this), 
			'body.path-grade-report-grader #user-grades tr.heading',
			'body.path-grade-report-grader .otfloater.heading:not(.sideonly)',
			false,
			true
		);
		// Управляющие элементы
		fixElement(
			$(this), 
			'body.path-grade-report-grader #user-grades td.header.cell.controls',
			'body.path-grade-report-grader .otfloater.sideonly.controls',
			true,
			false
		);
	});
});

// фильтрация таблиц
var otsearchable;
require(['jquery','jqueryui'], function($) {
	otsearchable = function(){
		$('table.ot-searchable').each(function(){
			var table = $(this);
			var config = table.data('searchable-cells');
			var container = $('<div>')
				.addClass('ot-filter-wrapper');
			

			var switcher = $('<input>')
				.attr('type','checkbox')
				.uniqueId()
				.addClass('ot-filter-display');
			$('<label>')
				.attr('for', switcher.attr('id'))
				.addClass('ot-filter-header')
				.text('Фильтрация')
				.appendTo(container);
			switcher.appendTo(container);
			
			var hide = table.data('searchable-filter-hide');
			if( hide === undefined || !hide )
			{
				switcher.attr('checked', 'checked')
			}
			
			var filters = $('<div>').addClass('ot-filter-filters').appendTo(container);
			for(var cellclass in config)
			{
				var filtertitle = config[cellclass];
				$('<input>')
					.attr('placeholder', filtertitle)
					.data('ot-filter-cellclass', cellclass)
					.keyup(function(){
						var searchval = $(this).val();
						var cellclass = $(this).data('ot-filter-cellclass');
						var filteredcells = table.find('td.'+cellclass);
						
						filteredcells.each(function(index){
							var row = $(this).closest('tr');
							if( $(this).text().toLowerCase().indexOf(searchval.toLowerCase()) !== -1 || searchval=='' )
							{// ячейка содержит искомое значение
								var hiders = [];
								if( row.data('ot-filter') !== undefined )
								{
									var hiders = row.data('ot-filter');
									var hiderkey = hiders.indexOf(cellclass);
									if ( hiderkey !== -1 )
									{
										hiders.splice(hiderkey,1);
									}
									row.data('ot-filter', hiders);
								}
								if( hiders.length == 0 )
								{
									row.removeClass('ot-filter-discard');
								}
							} else
							{// в ячейке нет искомого значения
								row.addClass('ot-filter-discard');
								if( row.data('ot-filter') === undefined )
								{
									row.data('ot-filter', [cellclass]);
								} else
								{
									var hiders = row.data('ot-filter');
									if( hiders.indexOf(cellclass) == -1 )
									{
										hiders.push(cellclass);
										row.data('ot-filter', hiders);
									}
								}
								
							}
						});
					})
					.appendTo(filters);
			}
			var parent = table.data('searchable-filter-parent');
			if( parent !== undefined )
			{
				container.appendTo($(parent).first());
			} else
			{
				container.insertBefore(table);
			}
		});
	};
	otsearchable();
});

// сортировка таблиц
var otsortable;
require(['jquery'], function($) {
	var ot_sort_table = function(table, cellclass, desc) {
		if( typeof desc == 'undefined' )
		{
			desc = false;
		}
		
		var rejected = [];
		var suitable = [];
		var header = [];
		table.find('tr').each(function(index){
			if($(this).hasClass('ot-sort-fix-top'))
			{
				header.push($(this));
				return true;
			}
			var suitable_cells = $(this).find('td.'+cellclass);
			if( suitable_cells.length > 0 )
			{
				var suitable_cell = suitable_cells.first();
				var sortablevalue = suitable_cell.data('sort-value');
				if( sortablevalue === undefined )
				{
					sortablevalue = suitable_cell.text();
				}
				suitable[(''+sortablevalue).toLowerCase()+index] = $(this);
			} else
			{
				rejected.push($(this));
			}
		});
		
		if(!desc)
		{
			for(var key in header)
			{
				table.append(header[key]);
			}
			Object.keys(suitable).sort().forEach(function(key) {
				table.append(suitable[key]);
			});
			for(var key in rejected)
			{
				table.append(rejected[key]);
			}
		} else 
		{
			for(var key = (rejected.length-1); key >= 0; key--)
			{
				table.prepend(rejected[key]);
			}
			Object.keys(suitable).sort().forEach(function(key) {
				table.prepend(suitable[key]);
			});
			for(var key = (header.length-1); key >= 0; key--)
			{
				table.prepend(header[key]);
			}
		}
	}
	otsortable = function() {
		$('table.ot-sortable').each(function(){
	
			var table = $(this);
			var config = table.data('sortable-cells');
			for(var headercellclass in config)
			{
				var datacellclass = config[headercellclass];
				table.find('th.'+headercellclass).each(function(){
					var tools = $('<div>')
						.addClass('ot-sort-tools');
					
					// сортировка в убывающем порядке
					$('<div>')
						.addClass('ot-sort-tool-sortup')
						.click(function(){
							ot_sort_table(table, datacellclass, true);
						})
						.appendTo(tools);
					
					// сортировка в возрастающем порядке
					$('<div>')
						.addClass('ot-sort-tool-sortdown')
						.click(function(){
							ot_sort_table(table, datacellclass);
						})
						.appendTo(tools);
					$(this)
						.data('ot-sort-cellclass', datacellclass)
						.append(tools);
					
				});
			}
		});
	};
	otsortable();
});
