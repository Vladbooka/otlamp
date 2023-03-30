require(['jquery', 'block_dof/dof_ajax'], function($, dof_ajax) {
	var Controller = new GTController();
	
	function GTController() {
		
		var GTController = this;
		
		// Глобальные переменные
		this.cstreamId = null;
		this.departmentId = null;
		this.showAll = false;
		
		// Состояния
		this.isOneOffBind = false;
		
		// Кастомные события, которые вызывает текущий плагин
		this.customEvents = {
			// Событие обновления инвентов плагина дропблока
			dropBlockRefreshEvent : new CustomEvent("dropBlockRefreshEvent"),
			journalRefresh: new CustomEvent("journalRefresh"),
			firstJournalRefreshed : new CustomEvent("firstJournalRefreshed"),
			displayLoading : new CustomEvent("displayLoading"),
			removeLoading : new CustomEvent("removeLoading")
		}
		
		this.init = function() {
			// Инициализация действий, которые не должны больше обновляться
			GTController.OneTimeBind.init();
			
			GTController.wrapper = $('.dof-groupjournal-grades-wrap').first();
			GTController.wrapper.on('classToggled', GTController.initResize);
			GTController.wrapper_parent = GTController.wrapper.parent().first();
			GTController.table = $('table.dof-groupjournal-grades').first();
			
			// Заполнение переменных
			GTController.initGlobalParams();
			
			// Установка смещений
			GTController.initResize();
			
			// Инициализация события выставления оценок
			GTController.initGradesEvents();
			
			// Инициализация события выставления присутствия
			GTController.initPresencesEvents();
			
			// Инициализация события редактирования успеваемости студента
			GTController.initUserProgressEvents();
			
			// Инициализация события редактирования занятия
			GTController.initLessonEditEvents();
			
			// Инициализация события обновления журнала
			GTController.initRefreshJournalEvents();
			
			// Инициализация пользовательского интерфейса
			GTController.initUI();
	    };
	    
	    /**
	     * Инициализация действий, которые не должны больше обновляться
	     */
	    this.OneTimeBind = {
    		init : function() {
    			
		    	if ( GTController.isOneOffBind === false ) {
					GTController.isOneOffBind = true;
					
					$(window).unbind("resize").on('resize', function() {
						GTController.initResize();
						window.document.GTControllerTemplans.initResize();
					});
			    	
			    	// Кастомные события журнала
			    	this.initCustomEvents();
		    	}
    		},
    		
    		// Кастомные события, которые слушает текущий плагин
    		initCustomEvents : function () {
    			
    			// Подписка на событие ресайза
		    	$(document).unbind("gradesJournalResize").bind("gradesJournalResize", function ()  {
	    			GTController.initResize();
		    	});
		    	
		    	// Подписка на событие обновления
		    	$(document).unbind("journalRefresh").bind("journalRefresh", function ()  {
	    			GTController.refreshJournal();
		    	});
    		}
	    };
	    
	    /**
	     * Инициализация переменных
	     */
	    this.initGlobalParams = function() {
	    	if ( GTController.table !== 'undefined' ) {
	    		GTController.cstreamId = GTController.table.data('cstream');		
	    		GTController.departmentId = GTController.table.data('department');
	    		GTController.showAll = GTController.table.data('showall');

	    		// Кладется контроллер для последующего использования другими JS плагинами
	    		window.document.GTController = GTController;
	    	}
	    };
	    
	    /**
	     * Инициализация пользовательского интерфейса
	     */
	    this.initUI = function() {
			// Инициализация сортировки слушателей
			this.initSortCpasseds();
			
			// Инициализация пользовательской навигации в таблице
			this.initNavigationActions();
	    };
	    
	    /**
	     * Инициализация смещений
	     */
	    this.initResize = function() {
			// Получение текущих смещений таблицы
			GTController.currentLeftPos = Math.abs(parseFloat(GTController.table.position().left));
			GTController.currentTopPos = Math.abs(parseFloat(GTController.table.position().top));
			
			// Формирование массивов с размерами оцениваемых ячеек
			GTController.gradeCellsWidth = GTController.getGradeCellsWidth();
			GTController.gradeCellsHeight = GTController.getGradeCellsHeight();
			
			// Инициализация групп зафиксированных ячеек
			GTController.fixedCellsGroups = GTController.initFixedCellGroups();
			// Вычисление размеров зафиксированных элементов для корректировки навигации
			GTController.fixedCellsWidth = GTController.getFixedCellsWidth();
			GTController.fixedCellsHeight = GTController.getFixedCellsHeight();
			
			// Определение индексов видимых оцениваемых ячеек при текущем положении таблицы
			GTController.rightVisibleCell = GTController.getRightVisibleCell();
			GTController.leftVisibleCell = GTController.getLeftVisibleCell();
			GTController.topVisibleCell = GTController.getTopVisibleCell();
			GTController.bottomVisibleCell = GTController.getBottomVisibleCell();
			
			// Фиксация заголовков таблицы
			GTController.fixCells();
	    };
		
	    /**
	     * Инициализация списка групп зафиксированных ячеек
	     */
	    this.initFixedCellGroups = function() {
			var fixedCellsGroups = {
				'top' : [],
				'left' : [],
				'right' : [],
				'bottom': [],
				'selfsize': []
			}
			
			// Ячейки месяцев
			GTController.table.find('.row-months > td').each(function(index, node) {
				fixedCellsGroups.top.push(node);
			});
			// Ячейки дней
			GTController.table.find('.row-days > td').each(function(index, node) {
				fixedCellsGroups.top.push(node);
			});
			// Ячейки занятий
			GTController.table.find('.row-lessons > td').each(function(index, node) {
				fixedCellsGroups.top.push(node);
			});
			// Ячейки занятий
			GTController.table.find('.row-divider > td').each(function(index, node) {
				fixedCellsGroups.top.push(node);
			});
			// Первые ячейки
			GTController.table.find('tbody > tr').each(function(index, node) {
				fixedCellsGroups.left.push($(this).children('td').first());
			});
			// Ячейки имен
			GTController.table.find('.cell-cpassedinfo').each(function() {
				fixedCellsGroups.left.push($(this));
			});
			// Ячейка для подгрузки всех занятий
			GTController.table.find('.cell-showall').each(function() {
				fixedCellsGroups.left.push($(this));
			});
			// Последние ячейки
			GTController.table.find('tbody > tr').each(function(index, node) {
				fixedCellsGroups.right.push($(this).children('td').last());
			});
			
			// Ячейки, призванные занять свободную ширину
			GTController.table.find('tbody > tr').each(function(index, node) {
				fixedCellsGroups.selfsize.push($(this).children('td.cell-selfsize'));
			});
			
			return fixedCellsGroups;
		}
	    
	    this.initPresencesEvents = function() {	    
	    	
	    	// РЕЖИМ БЫСТРОГО ПРОСТАВЛЕНИЯ ПОСЕЩАЕМОСТИ
	    	// Загрузка формы с проставлением посещаемости
	    	$('.dof_dropblock .dof-lesson-info-rollcall-form.internal-form')
	    		.unbind("click").not('.disabled').click(function() {
	    			
	    			GTController.closeClosestDropblock($(this));
		    		
		    		// Обновление журнала с получение редактируемой колонки
		    		GTController.refreshJournal({
	    				'edit_presence_event_id' : $(this).data('event-id')
		    		});
		    	});
	    	
	    	// РЕЖИМ ДОЛГОГО ПРОСТАВЛЕНИЯ ПОСЕЩАЕМОСТИ
	    	$('.dof_dropblock a.dof-lesson-info-rollcall-form.fullsize-form').remove();
	    	$('.dof_dropblock div.dof-lesson-info-rollcall-form.fullsize-form').removeClass('hidden');
	    	
	    	$('.dof_dropblock .dof-lesson-info-rollcall-form.fullsize-form').unbind('click').click(function (e) {
	    		
	    		// При клике на лейбл инициируются два события (от лейбла и привязанного инпута)
				if ( e.target.tagName == 'LABEL' ) {
					
					form_elem = $(this);
					elem = form_elem.parent();
					
					if ( ! elem.hasClass('clicked') ) {
						body = elem.find('.dof_modal_body');
						
						$('<iframe />').attr({
							'src': form_elem.data('iframe') + '&page_layout=popup',
							'class' : 'dof_modal_iframe'
						}).appendTo(body);
						
						// триггерим событие добавления загрузочного окна iframe
						$.event.trigger('addIframeLoading', [body]);
						
						elem.addClass('clicked');
					}
					
					elem.find('.dof_modal_overlay').unbind('click').click(function (e) {
						e.stopPropagation();
						elem = $(this);
						
						elem.siblings('.dof_modal_dialog').find('iframe').remove();
						elem.closest('.dof-lesson-info-rollcall').removeClass('clicked');
									    		
			    		// Cкрытие дропблока
			    		GTController.closeClosestDropblock(elem);
			    		
			    		// Обновление журнала с получение редактируемой колонки
			    		document.dispatchEvent(GTController.customEvents.journalRefresh);
					})
					elem.find('.dof_modal_button_close').unbind('click').click(function (e) {
						e.stopPropagation();
						elem = $(this);
						
						elem.parent('.dof_modal_header').siblings('.dof_modal_body').find('iframe').remove();
						elem.closest('.dof-lesson-info-rollcall').removeClass('clicked');
						
						// Cкрытие дропблока
			    		GTController.closeClosestDropblock(elem);
						
			    		// Обновление журнала с получение редактируемой колонки
			    		document.dispatchEvent(GTController.customEvents.journalRefresh);
					})
				}
	    	})
	    }
	    
    	this.initUserProgressEvents = function() {
    		
	    	$('.dof_dropblock div.dof-grade-info-edit > a').remove();
	    	$('.dof_dropblock div.dof-grade-info-edit > .dof-lesson-info-userprogress-form').removeClass('hidden');
	    	
	    	$('.dof_dropblock div.dof-grade-info-edit .dof-lesson-info-userprogress-form').unbind('click').click(function (e) {
	    		
	    		// При клике на лейбл инициируются два события (от лейбла и привязанного инпута)
				if ( e.target.tagName == 'IMG' ) {
					form_elem = $(this);
					elem = form_elem.parent();
					
					if ( ! elem.hasClass('clicked') ) {
						body = elem.find('.dof_modal_body');
						
						$('<iframe />').attr({
							'src': form_elem.data('iframe') + '&page_layout=popup',
							'class': 'dof_modal_iframe'
						}).appendTo(body);
						
						// триггерим событие добавления загрузочного окна iframe
						$.event.trigger('addIframeLoading', [body]);
						
						elem.addClass('clicked');
					}
					elem.find('.dof_modal_overlay').unbind('click').click(function (e) {
						e.stopPropagation();
						elem = $(this);
						
						elem.siblings('.dof_modal_dialog').find('iframe').remove();
						elem.closest('.dof-grade-info-edit').removeClass('clicked');
			    		
			    		// Cкрытие дропблока
			    		GTController.closeClosestDropblock(elem);
			    		
			    		// Обновление журнала с получение редактируемой колонки
			    		document.dispatchEvent(GTController.customEvents.journalRefresh);
					})
					elem.find('.dof_modal_button_close').unbind('click').click(function (e) {
						e.stopPropagation();
						elem = $(this);
						
						elem.parent('.dof_modal_header').siblings('.dof_modal_body').find('iframe').remove();
						elem.closest('.dof-grade-info-edit').removeClass('clicked');
						
						// Cкрытие дропблока
			    		GTController.closeClosestDropblock(elem);
						
			    		// Обновление журнала с получение редактируемой колонки
			    		document.dispatchEvent(GTController.customEvents.journalRefresh);
					})
				}
	    	})
	    }
    	
    	this.initLessonEditEvents = function() {
    		
	    	$('.dof_dropblock div.dof-lesson-info-edit > a').remove();
	    	$('.dof_dropblock div.dof-lesson-info-edit > .dof-lesson-info-form-edit').removeClass('hidden');
	    	
	    	$('.dof_dropblock div.dof-lesson-info-edit .dof-lesson-info-form-edit').unbind('click').click(function (e) {
	    		
	    		// При клике на лейбл инициируются два события (от лейбла и привязанного инпута)
				if ( e.target.tagName == 'IMG' ) {
					form_elem = $(this);
					elem = form_elem.parent();
					
					if ( ! elem.hasClass('clicked') ) {
						
						body = elem.find('.dof_modal_body');
						
						$('<iframe />').attr({
							'src': form_elem.data('iframe') + '&page_layout=popup',
							'class': 'dof_modal_iframe'
						}).appendTo(body);
						
						// триггерим событие добавления загрузочного окна iframe
						$.event.trigger('addIframeLoading', [body]);
						
						elem.addClass('clicked');
					}
					
					elem.find('.dof_modal_overlay').unbind('click').click(function (e) {
						e.stopPropagation();
						elem = $(this);
						
						elem.siblings('.dof_modal_dialog').find('iframe').remove();
						elem.closest('.dof-lesson-info-edit').removeClass('clicked');
									    		
			    		// Cкрытие дропблока
			    		GTController.closeClosestDropblock(elem);
			    		
			    		// Обновление журнала с получение редактируемой колонки
			    		document.dispatchEvent(GTController.customEvents.journalRefresh);
					})
					elem.find('.dof_modal_button_close').unbind('click').click(function (e) {
						e.stopPropagation();
						elem = $(this);
						
						elem.parent('.dof_modal_header').siblings('.dof_modal_body').find('iframe').remove();
						elem.closest('.dof-lesson-info-edit').removeClass('clicked');
						
						// Cкрытие дропблока
			    		GTController.closeClosestDropblock(elem);
						
			    		// Обновление журнала с получение редактируемой колонки
			    		document.dispatchEvent(GTController.customEvents.journalRefresh);
					})
				}
	    	})
	    }
    	
	    this.closeClosestDropblock = function(dropblockElement) {
	    	var dropblock = dropblockElement.closest('.dof_dropblock');
	    	var DBController = dropblock.data('DBController');
	    	if ( typeof DBController !== "undefined" ) {
	    		DBController.closeDropblock(dropblock);
	    	}
	    }
	    
	    this.closeModals = function() {
	    	window.document.GTControllerTemplans.closeModals();
	    	// Закрытие модальных окон
	    	$('.dof_modal_iframe').each(function (i, elem) {
	    		$(elem).closest('.dof_modal_modalwrap').find('.dof_modal_overlay').trigger('click');
	    	})
	    }
	    
	    this.initRefreshJournalEvents = function() {

	    	GTController.wrapper.find('.lesson-grades-wrapper-save').click(function (e) {
	    		e.preventDefault();
	    		
	    		// Формирование данных для AJAX запроса
	    		var data = {
    				cstream: GTController.cstreamId,
    				plan : $(this).data('plan'),
    				event : $(this).data('event'),
    				department: GTController.departmentId
	    		};
	    		

				// Загрузочное окно
				document.dispatchEvent(GTController.customEvents.displayLoading);
	    		
	    		switch( $(this).data('edit-type') )
	    		{
	    			case 'presence_edit':
	    				// Редактирование присутствия
	    				if( data.presenceinfo === undefined )
    					{
	    					data.presenceinfo = [];
    					}
	    	    		GTController.table.find('.cell-lesson-cpassed.cell-lesson-cpassed-edit .cpassed-presencecell-save').each(function () {
	    	    			var presentinput = $(this).find('input[type=checkbox]');
	    	    			var info = {
	    	    				personid : parseInt(presentinput.val()),
	    	    				present : presentinput.is(':checked')
	    	    			}
	    	    			data.presenceinfo.push(info);
	    	    		})
	    	    		
			    		// AJAX запрос на вебсервис деканата
			    		var requests = dof_ajax.call([{
					        methodname : 'im_journal_save_presence',
					        args: { 
					        	'event' : data.event,
					        	'presenceinfo' : data.presenceinfo,
					        	'department': data.department
				        	}
					    }]);
	    				break;
	    			case 'grades_edit':
	    			default:
	    				// Редактирование оценок
	    				if( data.grades === undefined )
    					{
	    					data.grades = [];
    					}
	    	    		GTController.table.find('.cell-lesson-cpassed.cell-lesson-cpassed-edit .cpassed-gradecell-save').each(function () {
	    	    			if ($(this).find('select').attr('disabled') === undefined) {
	    	    				info = {
    	        					cpassedid : $(this).data('cpassed'),
    	        					grade : $(this).find('select option:selected').val()
    	    	    			}
    	    	    			data.grades.push(info);
	    	    			}
	    	    		})
	    	    		
			    		// AJAX запрос на вебсервис деканата
			    		var requests = dof_ajax.call([{
					        methodname : 'im_journal_save_grades',
					        args: { 
					        	'cstream': data.cstream,
					        	'plan' : data.plan,
					        	'department': data.department,
					        	'grades' : data.grades
				        	}
					    }]);
	    	    		
	    				break;
	    		}
	    		
				requests[0]
					.done(function (response) {
	    				// Скрытие загрузочного окна
	    				document.dispatchEvent(GTController.customEvents.removeLoading);
						// Запрос успешно обработан, обновление журнала
						document.dispatchEvent(GTController.customEvents.journalRefresh);
					})
					.fail(function(){
	    				// Скрытие загрузочного окна
	    				document.dispatchEvent(GTController.customEvents.removeLoading);
						// Запрос успешно обработан, обновление журнала
						document.dispatchEvent(GTController.customEvents.journalRefresh);
					});
	    	});
	    }
	    
	    /**
	     * Инициализация выпадающего меню изменения оценки
	     */
	    this.initGradesEvents = function() {
	    	
	    	// закрытие сведений об оценке крестиком
	    	$('.dof_dropblock_node .dof-grade-info-close').unbind("click").click(function(){
	    		GTController.closeClosestDropblock($(this));
	    	});
	    	

	    	// Загрузка формы с заполнением оценок
	    	GTController.table.find('.cell-lesson a.dof-lesson-edit-cell').unbind("click").click(function (e) {
	    		e.preventDefault();
	    		
	    		// Обновление журнала с получение редактируемой колонки
	    		GTController.refreshJournal({
    				'edit_grades_plan_id' : $(this).data('plan')
	    		});
	    	})
	    };
	    
	    /**
	     * Полное обновление таблицы
	     */
	    this.refreshJournal = function(somevars) {
	    	// Параметры для получения журнала
	    	var addvars = {
    			'departmentid' : GTController.departmentId,
    			'showall' : GTController.showAll
			};
	    	
	    	// Дополнительные параметры
	    	if (somevars !== 'undefined') {
	    		$.extend(addvars, somevars);
	    	}

			// Загрузочное окно
			document.dispatchEvent(GTController.customEvents.displayLoading);
			
	    	// Запрос на получение html кода журнала
	    	var requests = dof_ajax.call([{
		        methodname : 'im_journal_get_grades_table',
		        args: { 
		        	'cstream_id': GTController.cstreamId,
		        	'addvars': addvars
	        	}
		    }]);
			requests[0]
				.done(function (response) {
    				// Скрытие загрузочного окна
    				document.dispatchEvent(GTController.customEvents.removeLoading);
					// Смещение таблицы
					var posX = GTController.currentLeftPos;
					var posY = GTController.currentTopPos;
					
					GTController.wrapper_parent.html('');
					GTController.wrapper_parent.append(response);
					
					// Инициализация контроллера журнала
					GTController.init();
					
					// Смещение таблицы в то положение, в котором оно было до обновления
					GTController.offsetVertical(posY);
					GTController.offsetHorisontal(posX);
					
					// Обновление переменных
					GTController.initResize()
				})
				.always(function () {
					// отправим событие о том, что журнал оценок обновился для обновления второго журнала
					document.dispatchEvent(GTController.customEvents.firstJournalRefreshed);
				});
	    };
	    
	    /**
	     * Инициализация списка размеров ячеек оценивания
	     */
	    this.getGradeCellsWidth = function() {
			var cells = this.table.find('.cell-lesson');
			var cellsWidth = [];
			cells.each(function() {
				cellsWidth.push($(this).outerWidth());
			});
			return cellsWidth;
		}
	    
	    /**
	     * Инициализация списка размеров ячеек оценивания
	     */
	    this.getGradeCellsHeight = function() {
			var cells = this.table.find('.cell-cpassedinfo');
			var cellsHeight = [];
			cells.each(function() {
				cellsHeight.push($(this).outerHeight());
			});
			return cellsHeight;
		}
	    
	    /**
	     * Получение ширины зафиксированных ячеек для определения активной области таблицы
	     */
	    this.getFixedCellsWidth = function() {
			var width = 0;
			GTController.table.find('.row-days > .cell-actions-nav').each(function() {
				width += $(this).outerWidth();
			});
			return width;
		}
	    
	    /**
	     * Получение высоты зафиксированных ячеек для определения активной области таблицы
	     */
	    this.getFixedCellsHeight = function() {
	    	var height = GTController.table.find('.row-months').outerHeight() + GTController.table.find('.row-days').outerHeight() + GTController.table.find('.row-lessons').outerHeight();
			return height;
		}
	    
	    /**
	     * Получение индекса левой полностью видимой ячейки
	     */
	    this.getLeftVisibleCell = function() {
	    	var leftpos = this.currentLeftPos;
	    	var CellIndex = 0;
	    	
	    	$.each(this.gradeCellsWidth, function(index, cellWidth) {
	    		leftpos -= cellWidth;
	    		CellIndex = index;
				if ( leftpos <= 0 && -1 * leftpos >= cellWidth ) {
					return false;
				}
			});
	    	
	    	return CellIndex;
		}
	    
	    /**
	     * Получение индекса правой полностью видимой ячейки
	     */
	    this.getRightVisibleCell = function() {
	    	var rightpos = this.currentLeftPos + this.wrapper.outerWidth() - this.fixedCellsWidth;
	    	var CellIndex = 0;
	    	$.each(this.gradeCellsWidth, function(index, cellWidth) {
	    		rightpos -= cellWidth;
	    		if ( rightpos <= 0 && -1 * rightpos <= cellWidth ) {
					return false;
				}
				CellIndex = index;
			});
	    	return CellIndex;
		}
	    
	    /**
	     * Получение индекса верхней полностью видимой ячейки
	     */
	    this.getTopVisibleCell = function() {
	    	var toppos = this.currentTopPos;
	    	var CellIndex = 0;
	    	$.each(this.gradeCellsHeight, function(index, cellHeight) {
	    		toppos -= cellHeight;
	    		CellIndex = index;
	    		if ( toppos <= 0 && -1 * toppos >= cellHeight ) {
					return false;
				}
				
			});
	    	return CellIndex;
		}
	    
	    /**
	     * Получение индекса нижней полностью видимой ячейки
	     */
	    this.getBottomVisibleCell = function() {
	    	var botpos = this.currentTopPos + this.wrapper.outerHeight() - this.fixedCellsHeight;
	    	var CellIndex = 0;
	    	$.each(this.gradeCellsHeight, function(index, cellHeight) {
	    		botpos -= cellHeight;
	    		if ( botpos <= 0 && -1 * botpos <= cellHeight ) {
					return false;
				}
				CellIndex = index;
			});
	    	return CellIndex;
		}
	    
		/**
		 * Зафиксировать информационные ячейки таблицы
		 */
		this.fixCells = function() {
			// Фиксация верхних ячеек
			$.each(this.fixedCellsGroups.top, function(index, node) {
				$(node).css({'top': GTController.currentTopPos + 'px'})
			});
			
			// Фиксация левых ячеек
			$.each(this.fixedCellsGroups.left, function(index, node) {
				$(node).css({'left': GTController.currentLeftPos + 'px'});
			});	

			// Заполнение свободного места
			var selfsizeCellsWidth = this.wrapper.outerWidth() - this.table.outerWidth();
			// Фиксация правых ячеек
			$.each(this.fixedCellsGroups.selfsize, function(index, node) {
				var width = selfsizeCellsWidth + node.outerWidth();
				$(node).css('width', (width < 0 ? 0 : width) + 'px');
				
				//var right = GTController.table.outerWidth() - GTController.wrapper.outerWidth() - GTController.currentLeftPos;
				//$(node).css('right', right + 'px');
			});
			
			// Фиксация правых ячеек
			var rightCellsWidth = this.wrapper.outerWidth() - this.table.outerWidth();
			// Фиксация правых ячеек
			$.each(this.fixedCellsGroups.right, function(index, node) {
//				var width = rightCellsWidth + node.outerWidth();
//				$(node).css('width', (width < 40 ? 40 : width) + 'px');
				
				var right = GTController.table.outerWidth() - GTController.wrapper.outerWidth() - GTController.currentLeftPos;
				
				$(node).css('right', (right > 0 ? right + 'px' : 'auto'));
			});
		}
		
		/**
		 * Сортировка слушателей
		 */
		this.sortCpasseds = function (node, field, dir) {
	    	
	    	var table = node.closest('.dof-groupjournal-grades');
			var cpasseds = table.find('.cell-cpassedinfo');
			var startrow = table.find('.row-divider').first();
			var rows = [];

			cpasseds.each(function(index) {
				var namenode = $(this).find('.dof-cpassed-info-fullname').first();
				
				var firstname = namenode.data('firstname');
				var lastname = namenode.data('lastname');
				var item = {
					'num' : index, 
					'firstname' : firstname, 
					'lastname' : lastname,
					'node' : namenode
				};
				rows.push(item);
			});
			
			// Сортировка по пользовательскому полю
			rows.sort( function(a, b ) {
				
				if ( dir == 'desc' ) {
					if(a[field].toLowerCase() < b[field].toLowerCase()) return -1;
				    if(a[field].toLowerCase() > b[field].toLowerCase()) return 1;
				} else {
					if(a[field].toLowerCase() > b[field].toLowerCase()) return -1;
				    if(a[field].toLowerCase() < b[field].toLowerCase()) return 1;
				}
				
			    return 0;
			});

			rows.forEach(function(item, i, arr) {
				var node = item['node'].closest('.row-cpassed');
				node.find('.cell-cpassednum > div').first().text(arr.length - i);
				startrow.after(node);
			});
			
			var showall = table.find('.cell-showall');
			if (showall.length > 0)
			{
				table.find('tr.row-cpassed:first .cell-cpassedinfo').first().after(showall);
			}
	    };
	    
		/**
		 * Инициализация сортировки
		 */
		this.initSortCpasseds = function() {
			
			GTController.table.find('.dof-cpassed-sortblock-lastname').click( function(event) {
				GTController.table.find('.dof-cpassed-sortblock-firstname').removeClass('active').removeClass('desc');

		    	if ( $(this).hasClass('active') ) {
		    		$(this).toggleClass('desc');
		    	} else {
		    		$(this).addClass('active');
		        }
		    	if ( $(this).hasClass('desc') ) {
		    		GTController.sortCpasseds($(this), 'lastname', 'desc');
		    	} else {
		    		GTController.sortCpasseds($(this), 'lastname', 'asc');
		        }
		    });
			GTController.table.find('.dof-cpassed-sortblock-firstname').click( function(event) {
				GTController.table.find('.dof-cpassed-sortblock-lastname').removeClass('active').removeClass('desc');
		    	if ( $(this).hasClass('active') ) {
		    		$(this).toggleClass('desc');
		    	} else {
		    		$(this).addClass('active');
		        }
		    	if ( $(this).hasClass('desc') ) {
		    		GTController.sortCpasseds($(this), 'firstname', 'desc');
		    	} else {
		    		GTController.sortCpasseds($(this), 'firstname', 'asc');
		        }
		    });
		}
		
		/**
		 * Глобальное горизонтальное смещение
		 */
		this.offsetHorisontal = function(offset) {
			this.currentLeftPos += offset;
			var currentLeftPos = this.currentLeftPos;
			
			// Смещение самой таблицы
			this.table.css({left : -1 * this.currentLeftPos});
			
			// Смещение зафиксированных вертикальных ячеек
			$.each(this.fixedCellsGroups.left, function(){
				$(this).css({left : currentLeftPos});
			});
			var rightWidth = this.table.outerWidth() - this.wrapper.outerWidth();
			$.each(this.fixedCellsGroups.right, function(){
				$(this).css({right : rightWidth - currentLeftPos});
			});
		}
		
		/**
		 * Глобальное вертикальное смещение
		 */
		this.offsetVertical = function(offset) {
			this.currentTopPos += offset;
			var currentTopPos = this.currentTopPos;
			
			// Смещение самой таблицы
			this.table.css({top : -1 * this.currentTopPos});
			
			// Смещение зафиксированных горизонтальных ячеек
			$.each(this.fixedCellsGroups.top, function(){
				$(this).css({top : currentTopPos});
			});
			var bottomHeight = this.table.outerHeight() - this.wrapper.outerHeight();
			$.each(this.fixedCellsGroups.bottom, function(){
				$(this).css({bottom : bottomHeight - currentTopPos});
			});

			GTController.table.find('.cell-showall').css('top', this.currentTopPos+'px');
		}
		
		/**
		 * Смещение таблицы на одну ячейку вправо
		 */
		this.moveRightOne = function() {
			if ( this.rightVisibleCell + 1 >= this.gradeCellsWidth.length ) {
				// Достигнут край таблицы
				return;
			}
			
			// Вычисление корректировки, связанной с частичным отображением предыдущей ячейки
			var visibleCellCoord = 0;
			for ( var index = 0; index <= this.rightVisibleCell; index++ ) {
				visibleCellCoord += this.gradeCellsWidth[index];
			}
			var correctWidth = this.currentLeftPos + this.wrapper.outerWidth() - this.fixedCellsWidth - visibleCellCoord;
			this.rightVisibleCell += 1;
			
			// Смещение таблицы
			this.offsetHorisontal(this.gradeCellsWidth[this.rightVisibleCell] - correctWidth);
			this.leftVisibleCell = this.getLeftVisibleCell();
		}
		
		this.moveLeftOne = function() {
			if ( this.leftVisibleCell == 0 ) {
				// Достигнут край таблицы
				return;
			}
			
			// Вычисление корректировки, связанной с частичным отображением предыдущей ячейки
			var visibleCellCoord = 0;
			for ( var index = 0; index < this.leftVisibleCell; index++ ) {
				visibleCellCoord += this.gradeCellsWidth[index];
			}
			var correctWidth = visibleCellCoord - this.currentLeftPos;
			
			this.leftVisibleCell -= 1;
			
			// Смещение таблицы
			this.offsetHorisontal(correctWidth - this.gradeCellsWidth[this.leftVisibleCell]);
			
			this.rightVisibleCell = this.getRightVisibleCell();
		}
		
		this.moveUpOne = function() {
			
			if ( this.topVisibleCell == 0 ) {
				// Достигнут край таблицы
				return;
			}
			
			// Вычисление корректировки, связанной с частичным отображением предыдущей ячейки
			var visibleCellCoord = 0;
			for ( var index = 0; index < this.topVisibleCell; index++ ) {
				visibleCellCoord += this.gradeCellsHeight[index];
			}
			var correctHeight = visibleCellCoord - this.currentTopPos;
			
			this.topVisibleCell -= 1;
			
			// Смещение таблицы
			this.offsetVertical(correctHeight - this.gradeCellsHeight[this.topVisibleCell]);
			
			this.bottomVisibleCell = this.getBottomVisibleCell();
		}
		
		this.moveDownOne = function() {
			if ( this.bottomVisibleCell + 1 >= this.gradeCellsHeight.length ) {
				// Достигнут край таблицы
				return;
			}
			
			// Вычисление корректировки, связанной с частичным отображением предыдущей ячейки
			var visibleCellCoord = 0;
			for ( var index = 0; index <= this.bottomVisibleCell; index++ ) {
				visibleCellCoord += this.gradeCellsHeight[index];
			}
			var correctHeight = this.currentTopPos + this.wrapper.outerHeight() - this.fixedCellsHeight - visibleCellCoord;
			this.bottomVisibleCell += 1;

			// Смещение таблицы
			this.offsetVertical(this.gradeCellsHeight[this.bottomVisibleCell] - correctHeight);
			this.topVisibleCell = this.getTopVisibleCell();
		}
		
		this.moveRightPage = function() {
			
			// Вычисление корректировки, связанной с частичным отображением предыдущей ячейки
			var visibleCellCoord = 0;
			for ( var index = 0; index <= this.rightVisibleCell; index++ ) {
				visibleCellCoord += this.gradeCellsWidth[index];
			}
			var correctWidth = this.currentLeftPos + this.wrapper.outerWidth() - this.fixedCellsWidth - visibleCellCoord;
			
			// Вычисление смещения для перехода к следующей части таблицы
			var nextPageOffset = this.wrapper.outerWidth() - this.fixedCellsWidth - correctWidth;
			
			// Вычисление правой координаты после перемещения
			var nextPageRightCoord = this.currentLeftPos + nextPageOffset + this.wrapper.outerWidth();
			
			// Учет конца таблицы
			if ( nextPageRightCoord >= this.table.outerWidth() ) {
				nextPageOffset = nextPageOffset - (nextPageRightCoord - this.table.outerWidth());
			}
			
			// Смещение таблицы
			this.offsetHorisontal(nextPageOffset);
			this.leftVisibleCell = this.getLeftVisibleCell();
			this.rightVisibleCell = this.getRightVisibleCell();
		}
		
		this.moveLeftPage = function() {
			// Вычисление корректировки, связанной с частичным отображением предыдущей ячейки
			var visibleCellCoord = 0;
			for ( var index = 0; index < this.leftVisibleCell; index++ ) {
				visibleCellCoord += this.gradeCellsWidth[index];
			}
			var correctWidth = visibleCellCoord - this.currentLeftPos;
			
			// Вычисление смещения для перехода к предыдущей части таблицы
			var prevPageOffset = this.wrapper.outerWidth() - this.fixedCellsWidth - correctWidth;
			
			// Учет начала таблицы
			if ( this.currentLeftPos - prevPageOffset < 0 ) {
				prevPageOffset = this.currentLeftPos;
			}
			
			// Смещение таблицы
			this.offsetHorisontal(-1 * prevPageOffset);
			this.leftVisibleCell = this.getLeftVisibleCell();
			this.rightVisibleCell = this.getRightVisibleCell();
		}
		
		this.moveUpPage = function() {
			// Вычисление корректировки, связанной с частичным отображением предыдущей ячейки
			var visibleCellCoord = 0;
			for ( var index = 0; index < this.topVisibleCell; index++ ) {
				visibleCellCoord += this.gradeCellsHeight[index];
			}
			var correctHeight = visibleCellCoord - this.currentTopPos;
			
			// Вычисление смещения для перехода к предыдущей части таблицы
			var prevPageOffset = this.wrapper.outerHeight() - this.fixedCellsHeight - correctHeight;
			
			// Учет начала таблицы
			if ( this.currentTopPos - prevPageOffset < 0 ) {
				prevPageOffset = this.currentTopPos;
			}
			
			// Смещение таблицы
			this.offsetVertical(-1 * prevPageOffset);
			this.topVisibleCell = this.getTopVisibleCell();
			this.bottomVisibleCell = this.getBottomVisibleCell();
		}
		
		this.moveDownPage = function() {
			if(this.table.outerHeight() < this.wrapper.outerHeight()) 
			{
				var nextPageOffset = 0;
			} 
			else
			{
				// Вычисление корректировки, связанной с частичным отображением предыдущей ячейки
				var visibleCellCoord = 0;
				for ( var index = 0; index <= this.bottomVisibleCell; index++ ) {
					visibleCellCoord += this.gradeCellsHeight[index];
				}
				
				var correctHeight = this.currentTopPos + this.wrapper.outerHeight() - this.fixedCellsHeight - visibleCellCoord;
				
				// Вычисление смещения для перехода к следующей части таблицы
				var nextPageOffset = this.wrapper.outerHeight() - this.fixedCellsHeight - correctHeight;
				
				// Вычисление правой координаты после перемещения
				var nextPageBottomCoord = this.currentTopPos + nextPageOffset + this.wrapper.outerHeight();
				
				// Учет конца таблицы
				if ( nextPageBottomCoord >= this.table.outerHeight() ) {
					nextPageOffset = nextPageOffset - (nextPageBottomCoord - this.table.outerHeight());
				}
			}
			
			// Смещение таблицы
			this.offsetVertical(nextPageOffset);
			this.topVisibleCell = this.getTopVisibleCell();
			this.bottomVisibleCell = this.getBottomVisibleCell();
		}
		
		this.moveToTop = function() {
			// Смещение таблицы на текущий отступ
			this.offsetVertical(-1 * this.currentTopPos);
			this.topVisibleCell = this.getTopVisibleCell();
			this.bottomVisibleCell = this.getBottomVisibleCell();
		}
		
		this.moveToBottom = function() {

			if(this.table.outerHeight() < this.wrapper.outerHeight()) 
			{
				var bottomOffset = 0;
			} 
			else
			{
				// Получение максимального отступа для отображения низа таблицы
				var offset = this.table.outerHeight() - this.wrapper.outerHeight();
				var bottomOffset = offset - this.currentTopPos;
			}
			// Смещение таблицы
			this.offsetVertical(bottomOffset);
			this.topVisibleCell = this.getTopVisibleCell();
			this.bottomVisibleCell = this.getBottomVisibleCell();
		}
		
		/**
		 * Инициализация навигации таблицы
		 */
		this.initNavigationActions = function() {
			GTController.table.find('.action-right.action').click(function() {
				GTController.moveRightOne();
		    });
			GTController.table.find('.action-left.action').click(function() {
				GTController.moveLeftOne();
		    });
			GTController.table.find('.action-up.action').click(function() {
				GTController.moveUpOne();
		    });
			GTController.table.find('.action-down.action').click(function() {
				GTController.moveDownOne();
		    });
			GTController.table.find('.action-tostart.action').click(function() {
				GTController.moveLeftPage();
		    });
			GTController.table.find('.action-toend.action').click(function() {
				GTController.moveRightPage();
		    });
			GTController.table.find('.action-uplist.action').click(function() {
				GTController.moveUpPage();
		    });
			GTController.table.find('.action-downlist.action').click(function() {
				GTController.moveDownPage();
		    });
			GTController.table.find('.action-totop.action').click(function() {
				GTController.moveToTop();
		    });
			GTController.table.find('.action-tobottom.action').click(function() {
				GTController.moveToBottom();
		    });
		}
		
		GTController.init();
	};
});
