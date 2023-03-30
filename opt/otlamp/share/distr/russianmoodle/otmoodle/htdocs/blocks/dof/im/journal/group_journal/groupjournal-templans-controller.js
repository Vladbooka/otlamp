require(['jquery', 'block_dof/dof_ajax', 'block_dof/dof_str'], function($, dof_ajax, dof_str) {
	var Controller = new GTController();
	
	function GTController() {
		
		var GTController = this;
		
		// Состояния
		this.isOneOffBind = false;
		
		// Глобальные переменные
		this.cstreamId = null;
		this.departmentId = null;
		this.showAll = false;
		
		// Кастомные события, которые вызывает текущий плагин
		this.customEvents = {
			dropBlockRefreshEvent : new CustomEvent("dropBlockRefreshEvent"),
			gradesJournalResize : new CustomEvent("gradesJournalResize"),
			journalRefresh : new CustomEvent("journalRefresh"),
			displayLoading : new CustomEvent("displayLoading"),
			removeLoading : new CustomEvent("removeLoading")
		}
		
		this.init = function() {
			
			// Инициализация действий, которые не должны больше обновляться
			GTController.initOneTime();

			GTController.actions = $('.dof-groupjournal-templans-actions').first();
			GTController.wrapper = $('.dof-groupjournal-templans-wrap').first();
			GTController.wrapper_parent = GTController.wrapper.parent().first();
			GTController.table = $('table.dof-groupjournal-templans').first();
			
			// Заполнение переменных
			GTController.initGlobalParams();
			
			// Инициализация смещений
			GTController.initResize();
			
			// Инициализация пользовательского интерфейса
			GTController.initUI()
			
			// Инициализация обработчика скрытия занятий
			GTController.initCollabsible();
			
			// Инициализация событий
			GTController.DataEvents.init();
			
			// Инициализация события создания занятия
			GTController.initLessonCreateEvents();
			
			// Инициализация события редактирования занятия
			GTController.initLessonEditEvents();
	    };
	    
	    /**
	     * Инициализация действий, которые не должны больше обновляться
	     */
	    this.initOneTime = function() {
	    	if ( GTController.isOneOffBind === false ) {
				GTController.isOneOffBind = true;
				
				$(window).on('resize', function() {
					GTController.initResize();
				});
				
		    	// Подписка на событие обновления
		    	$(document).bind("firstJournalRefreshed", function ()  {
		    		GTController.refresh();
		    	});
		    	
		    	// Подписка на обновление дропблоков
		    	$(document).unbind("dropBlockRefreshed").bind("dropBlockRefreshed", function ()  {
		    		GTController.DataEvents.initClickEvents.gradeitemmenu();
		    		window.document.GTController.initGradesEvents();
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
	    		window.document.GTControllerTemplans = GTController;	
	    	}
	    };

	    this.closeModals = function() {
	    	// Закрытие модальных окон
	    	$('.dof_imodal').each(function (i, elem) {
	    		$(elem).remove();
	    		document.dispatchEvent(GTController.customEvents.journalRefresh);
	    	})
	    }
	    
	    /**
	     * Инициализация пользовательского интерфейса
	     */
	    this.initUI = function() {
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
			GTController.gradeCellsHeight = GTController.getGradeCellsHeight();
			
			// Инициализация групп зафиксированных ячеек
			GTController.fixedCellsGroups = GTController.initFixedCellGroups();
			
			// Вычисление размеров зафиксированных элементов для корректировки навигации
			GTController.fixedCellsHeight = GTController.getFixedCellsHeight();
			
			// Определение индексов видимых оцениваемых ячеек при текущем положении таблицы
			GTController.topVisibleCell = GTController.getTopVisibleCell();
			GTController.bottomVisibleCell = GTController.getBottomVisibleCell();
			
			// Фиксация заголовков таблицы
			GTController.fixCells();
	    };
	    
    	this.initLessonCreateEvents = function() {
    		
	    	$('div.dof-lessons-actions-create > a').remove();
	    	$('div.dof-lessons-actions-create > .dof-lessons-actions-create-form').removeClass('hidden');
	    	
	    	$('div.dof-lessons-actions-create .dof-lessons-actions-create-form').unbind('click').click(function (e) {
	    		
	    		// При клике на лейбл инициируются два события (от лейбла и привязанного инпута)
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
					elem.closest('.dof-lessons-actions-create').removeClass('clicked');
					
		    		// Обновление журнала с получение редактируемой колонки
		    		document.dispatchEvent(GTController.customEvents.journalRefresh);
				})
				elem.find('.dof_modal_button_close').unbind('click').click(function (e) {
					e.stopPropagation();
					elem = $(this);
					
					elem.parent('.dof_modal_header').siblings('.dof_modal_body').find('iframe').remove();
					elem.closest('.dof-lessons-actions-create').removeClass('clicked');
					
		    		// Обновление журнала с получение редактируемой колонки
		    		document.dispatchEvent(GTController.customEvents.journalRefresh);
				})
	    	})
	    }
	    
	    /**
	     * Инициализация событий
	     */
	    this.DataEvents = {
	    		status : {
	    				variables : {
	    					status : false,
	    					wrapper_element : null
	    	    		},
	    	    		
	    	    		on : function () {
	    	    			if ( ! this.variables.status ) {
		    	    			this.variables.status = true;
		    	    			GTController.table.closest('.dof-groupjournal-templans-and-tools').addClass('edit-mode');
		    	    			this.variables.wrapper_element = $('<div />').addClass('lesson-info-wrapper-save')
			    	    			.click(function (e) {
						    			e.preventDefault();
						    			e.stopPropagation();
						    			
						    			GTController.DataEvents.status.off();
						    		})
			    	    			.appendTo($(GTController.wrapper_parent));
	    	    			}
	    	    		},
	    	    		
	    	    		off : function () {
	    	    			if ( this.variables.status ) {
	    	    				this.variables.status = false;
		    	    			GTController.table.closest('.dof-groupjournal-templans-and-tools').removeClass('edit-mode');
	    	    				if ( this.variables.wrapper_element != null ) 
	    	    				{
	    	    					this.variables.wrapper_element.remove();
	    	    				}
	    	    				GTController.DataEvents.requestSave.init();
	    	    			}
	    	    		}
	    		},
	    		
	    		init : function () {
	    			this.initClickEvents.passed();
	    			this.initClickEvents.homework();
	    			this.initClickEvents.homeworktime();
	    			this.initClickEvents.gradeitemmenu();
	    		},
	    		
	    		initClickEvents : {
	    			
	    			gradeitemmenu : function () {
	    				
	    				$('.dof-mdlgradeitem-access-ajax').unbind("click").click(function(e) {
    						e.preventDefault();
    						
    						var progressbar = $(this).closest('.dof-mdlgradeitem-info-menu-item').find('.dof-journal-progressbar')
    						if (progressbar.hasClass('dof-journal-progressbar-processing')) {
    							return;
    						}
    						var args = {
    								'cstreamid' : $(this).data('cstreamid'),
    								'planid' : $(this).data('planid'),
    								'newstatus' : !!$(this).data('changeto')
    						}
    				    	var requests = dof_ajax.call([{
    					        methodname : 'im_journal_change_access_to_mdlgradeitem',
    					        args: args
    					    }]);
    						
    						progressbar.addClass('dof-journal-progressbar-processing')
    						progressbar.addClass('dof-journal-progressbar-start')
    						requests[0]
    							.done(function (response) {
    								progressbar.addClass('dof-journal-progressbar-finished')
    								if (response === false) {
    									progressbar.addClass('dof-journal-progressbar-failed')
    								}
    							})
    							.fail(function() {
    								progressbar.addClass('dof-journal-progressbar-finished')
    								progressbar.addClass('dof-journal-progressbar-failed')
    							})
    							.always(function() {
    								setTimeout(function () {
    									progressbar.hide()
    									progressbar.removeClass('dof-journal-progressbar-finished')
    									progressbar.removeClass('dof-journal-progressbar-start')
    									progressbar.removeClass('dof-journal-progressbar-failed')
    									setTimeout(function() {
    										progressbar.show()
    										progressbar.removeClass('dof-journal-progressbar-processing')
    										document.dispatchEvent(GTController.customEvents.journalRefresh);
    									}, 350)
    								}, 700)
    							});
		    			})
		    			
		    			// при активации дропблока, убирать классы кнопок
		    			$('.dof-grade-cell-info > .dof_dropblock_wrapper, .dof_lesson_cell .dof_dropblock_wrapper').on('dropblock:activate', function(){
    						$('.dof_dropblock > #'+$(this).data('id')+' + div .dof-mdlgradeitem-access-tablegrades-ajax')
								.removeClass('dof-journal-progressbar-fullheight-bi')
								.removeClass('dof-journal-progressbar-fullheight-bi-finish-ok')
								.removeClass('dof-journal-progressbar-fullheight-bi-finish-fail')
							$('.dof_dropblock > #'+$(this).data('id')+' + div .dof-mdlgradeitem-access-tablegrades-lesson-ajax')
								.removeClass('dof-journal-progressbar-fullheight-bi')
								.removeClass('dof-journal-progressbar-fullheight-bi-finish-ok')
								.removeClass('dof-journal-progressbar-fullheight-bi-finish-fail')
		    			});
		    			// при клике на кнопку, вызывать сервис, отображать анимацию процесса
		    			$('.dof-mdlgradeitem-access-tablegrades-ajax').unbind("click").click(function(e) {
    						e.preventDefault();
    						
    						var args = {
    								'cstreamid' : $(this).data('cstreamid'),
    								'planid' : $(this).data('planid'),
    								'newstatus' : !!$(this).data('changeto'),
    								'cpassedid' : $(this).data('cpassedid')
    						}
    				    	var requests = dof_ajax.call([{
    					        methodname : 'im_journal_change_user_access_to_mdlgradeitem',
    					        args: args
    					    }]);
    						
    						currElement = $(this)
    						currElement
    							.removeClass('dof-journal-progressbar-fullheight-bi-finish-ok')
    							.removeClass('dof-journal-progressbar-fullheight-bi-finish-fail')
    							.addClass('dof-journal-progressbar-fullheight-bi')
    						requests[0]
    							.done(function (response) {
    								currElement.addClass('dof-journal-progressbar-fullheight-bi-finish-ok')
    							})
    							.fail(function() {
    								currElement.addClass('dof-journal-progressbar-fullheight-bi-finish-fail')
    							}).always(function () {
    								setTimeout(function () {
    									document.dispatchEvent(GTController.customEvents.journalRefresh);
    								}, 350);
    							})
		    			})
		    			
		    			$('.dof-mdlgradeitem-access-tablegrades-lesson-ajax').unbind("click").click(function(e) {
    						e.preventDefault();
    						
    						var args = {
    								'cstreamid' : $(this).data('cstreamid'),
    								'planid' : $(this).data('planid'),
    								'newstatus' : !!$(this).data('changeto'),
    						}
    				    	var requests = dof_ajax.call([{
    					        methodname : 'im_journal_change_access_to_mdlgradeitem',
    					        args: args
    					    }]);
    						
    						currElement = $(this)
    						currElement
    							.removeClass('dof-journal-progressbar-fullheight-bi-finish-ok')
    							.removeClass('dof-journal-progressbar-fullheight-bi-finish-fail')
    							.addClass('dof-journal-progressbar-fullheight-bi')
    						requests[0]
    							.done(function (response) {
    								currElement.addClass('dof-journal-progressbar-fullheight-bi-finish-ok')
    							})
    							.fail(function() {
    								currElement.addClass('dof-journal-progressbar-fullheight-bi-finish-fail')
    							}).always(function () {
    								setTimeout(function () {
    									document.dispatchEvent(GTController.customEvents.journalRefresh);
    								}, 350);
    							})
		    			})
		    			
		    			$('.dof-mdlgradeitem-sync-grades').unbind("click").click(function(e) {
		    				e.preventDefault();
		    				
		    				var progressbar = $(this).closest('.dof-mdlgradeitem-info-menu-item').find('.dof-journal-progressbar')
    						if (progressbar.hasClass('dof-journal-progressbar-processing')) {
    							return;
    						}
    						var args = {
    								'planid' : $(this).data('planid')
    						}
    				    	var requests = dof_ajax.call([{
    					        methodname : 'im_journal_force_plan_sync',
    					        args: args
    					    }]);
    						
    						progressbar.addClass('dof-journal-progressbar-processing')
    						progressbar.addClass('dof-journal-progressbar-start')
    						requests[0]
    							.done(function (response) {
    								progressbar.addClass('dof-journal-progressbar-finished')
    								if (response === false) {
    									progressbar.addClass('dof-journal-progressbar-failed')
    								}
    							})
    							.fail(function() {
    								progressbar.addClass('dof-journal-progressbar-finished')
    								progressbar.addClass('dof-journal-progressbar-failed')
    							})
    							.always(function() {
    								setTimeout(function () {
    									progressbar.hide()
    									progressbar.removeClass('dof-journal-progressbar-finished')
    									progressbar.removeClass('dof-journal-progressbar-start')
    									progressbar.removeClass('dof-journal-progressbar-failed')
    									setTimeout(function() {
    										progressbar.show()
    										progressbar.removeClass('dof-journal-progressbar-processing')
    										document.dispatchEvent(GTController.customEvents.journalRefresh);
    									}, 350)
    								}, 700)
    							});
		    			})
	    			},
	    			
	    			passed : function () {
	    				GTController.table.find('tr[data-plan-editable="1"] td.cell-passed').click(function (e) {
	    					e.preventDefault();
		    		
	    					// Создание подложки для сохранения
	    					GTController.DataEvents.status.on();
	    					
	    					if ( $(this).find('input').length == 0 ) {
	    						
					    		var text = $(this).text();
					    		$(this).html('');
					    		
					    		$('<input type="text" value="' +  text + '" />').appendTo($(this));
	    					}
		    			})
	    			},
	    			
	    			homework : function () {
	    				GTController.table.find('tr[data-plan-editable="1"] td.cell-homework').click(function (e) {
	    					e.preventDefault();
		    		
	    					// Создание подложки для сохранения
	    					GTController.DataEvents.status.on();
	    					
	    					if ( $(this).find('input').length == 0 ) {
	    						
					    		var text = $(this).text();
					    		$(this).html('');
					    		
					    		$('<input type="text" value="' +  text + '" />').appendTo($(this));
	    					}
		    			})
	    			},
	    			
	    			homeworktime : function () {
	    				GTController.table.find('tr[data-plan-editable="1"] td.cell-homework-time').click(function (e) {
	    					e.preventDefault();
		    		
	    					// Создание подложки для сохранения
	    					GTController.DataEvents.status.on();
	    					
	    					if ( $(this).find('input').length == 0 ) {
	    						
					    		var text = $(this).text();
					    		$(this).html('');
					    		
					    		$('<input type="text" value="' +  text + '" />').appendTo($(this));
	    					}
		    			})
	    			}
	    		},
	    		
	    		requestSave : {
	    			init : function () {
	    				
	    				// Массив запросов на сохранение информации
	    				requests = [];
	    				
	    				GTController.table.find('.row-lesson-info').each( function () {
	    					var request = {
	    						methodname : null,
	    						args : {
	    							plan : null,
	    							passed : GTController.DataEvents.requestSave.getPassed($(this)),
	    							homework : GTController.DataEvents.requestSave.getHomework($(this)),
	    							homeworktime : GTController.DataEvents.requestSave.getHomeworktime($(this))
    							}
	    					};
	    					
	    					var has_data_to_save = false;
	    					$.each(request.args, function(index, value) {
							    if ( value != null ) {
							    	has_data_to_save = true;
							    } 
							}); 
    						
    						if ( has_data_to_save && $(this).data('plan') != 0 ) {
    						
    							// Дозаполнение запроса
    							request.args.plan = $(this).data('plan');
    							
    							// Метод сохранения плана
    							request.methodname = 'im_journal_save_plan';
    							
    							// Добавление запроса в пулл
    						 	requests.push(request);
						 	}
	    				})
	    				
	    				// Отправление запроса
	    				GTController.DataEvents.requestSave.process(requests);
	    			},
	    			
	    			process : function (requests) {
				    		
	    				if ( requests.length != 0 ) {
		    				// Загрузочное окно
		    				document.dispatchEvent(GTController.customEvents.displayLoading);
				    		// AJAX запрос на вебсервис деканата
				    		var reqs = dof_ajax.call(requests);
				    		
				    		$.when.apply(null, reqs)
				    			.done(function () {
				    				// Скрытие загрузочного окна
				    				document.dispatchEvent(GTController.customEvents.removeLoading);
					    			// Обновление журнала занятий
					    			document.dispatchEvent(GTController.customEvents.journalRefresh);
					    		})
					    		.fail(function(){
				    				// Скрытие загрузочного окна
				    				document.dispatchEvent(GTController.customEvents.removeLoading);
					    			// Обновление журнала занятий
					    			document.dispatchEvent(GTController.customEvents.journalRefresh);
					    		});
	    				} else
    					{
	    					// Обновление журнала занятий
	    					document.dispatchEvent(GTController.customEvents.journalRefresh);
    					}
	    				
	    			},
	    			
	    			getPassed : function (element) {
	    				if ( element.find('.cell-passed input').length != 0 ) {
	    					return element.find('.cell-passed input').val();
	    				} else {
	    					return null;
	    				}
	    			},
	    			
	    			getHomework : function (element) {
	    				if ( element.find('.cell-homework input').length != 0 ) {
	    					return element.find('.cell-homework input').val();
	    				} else {
	    					return null;
	    				}
	    			},
	    			
	    			getHomeworktime : function (element) {
	    				if ( element.find('.cell-homework-time input').length != 0 ) {
	    					return parseInt(element.find('.cell-homework-time input').val()) * 60;
	    				} else {
	    					return null;
	    				}
	    			}
	    		}
	    };
	    
	    /**
	     * Полное обновление журнала
	     */
	    this.refresh = function() {
	    	
	    	// Параметры для получения журнала
	    	var addvars = {
    			'departmentid' : GTController.departmentId,
    			'showall' : GTController.showAll
			};
	    	
			// Загрузочное окно
			document.dispatchEvent(GTController.customEvents.displayLoading);
	    	// Запрос на получение html кода журнала
	    	var requests = dof_ajax.call([{
		        methodname : 'im_journal_get_themplans_table',
		        args: { 
		        	'cstream_id': GTController.cstreamId,
		        	'addvars': addvars
	        	}
		    }]);
			requests[0]
				.done(function (response) {
					// Смещение таблицы
					var posY = GTController.currentTopPos;
	
					GTController.wrapper.remove();
					GTController.actions.remove();
					GTController.wrapper_parent.append(response);
					GTController.init();
					
					// Смещение таблицы в то положение, в котором оно было до обновления
					GTController.offsetVertical(posY);
					
					// Обновление переменных
					GTController.initResize()
					
					// Инициализация обработчика скрытия занятий
					GTController.initCollabsible();
	
					if( typeof otsortable == 'function')
					{
						otsortable();
					}
					if( typeof otsearchable == 'function')
					{
						otsearchable();
					}
				})
				.fail(function(){
				})
				.always(function () {
					// Обновление дропблоков
					document.dispatchEvent(GTController.customEvents.dropBlockRefreshEvent);
					// Скрытие загрузочного окна
    				document.dispatchEvent(GTController.customEvents.removeLoading);
				});
	    };
	    
	    this.loadingScreen = { 
		    /**
		     * Запуск загрузочного окна
		     */
	    	on: function() {
		    	$('<div />')
		    		.addClass('journal-loading-screen-wrapper')
		    		.appendTo('body');
		    	$('<div/>')
		    		.addClass('journal-loading-screen-loader')
		    		.appendTo('body .journal-loading-screen-wrapper');
		    },
	    
		    /**
		     * Закрытие загрузочного окна
		     */
		    off : function(time) {
		    	if ( time !== 'undefined' && time > 0 ) {
		    		setTimeout(function () {
		    			$('body div.journal-loading-screen-wrapper').remove();
		    		}, time);
		    	} else {
		    		$('body div.journal-loading-screen-wrapper').remove();
		    	}
		    }
	    };
		
	    /**
	     * Инициализация списка групп зафиксированных ячеек
	     */
	    this.initFixedCellGroups = function() {
			var fixedCellsGroups = {
				'top' : []
			}
			
			GTController.table.find('.row-headers > th, .row-divider > th, .cell-showall').each(function(index, node) {
				fixedCellsGroups.top.push(node);
			});

			return fixedCellsGroups;
		}
	    
	    /**
	     * Инициализация обработчика скрытия cписка занятий
	     */
	    this.initCollabsible = function() {
			action_element = this.wrapper.parent().siblings('.actions').children('.action-collapse').unbind("click").on({
				click : function () {
					var mainwrapper = GTController.wrapper.closest('.dof-journal-groupjournal-grades-templans-wrapper');
					var leftcontrol = $('.action-collapse.action-left');
					var rightcontrol = $('.action-collapse.action-right');
					if ( ! mainwrapper.hasClass('grades-full-width') && ! mainwrapper.hasClass('templans-full-width') ) {
						if( $(this).hasClass('action-right') )
						{
							mainwrapper.addClass('grades-full-width');
						} else
						{
							mainwrapper.addClass('templans-full-width');
						}
					} else
					{
						mainwrapper.removeClass('grades-full-width').removeClass('templans-full-width')
					}
					// Инициирование события ресайза журнала
					document.dispatchEvent(GTController.customEvents.gradesJournalResize);
				}
			})
		}
	    
	    /**
	     * Инициализация списка размеров ячеек оценивания
	     */
	    this.getGradeCellsHeight = function() {
			var cells = this.table.find('.row-lesson-info');
			var cellsHeight = [];
			cells.each(function() {
				cellsHeight.push($(this).outerHeight());
			});
			return cellsHeight;
		}
	    
	    /**
	     * Получение высоты зафиксированных ячеек для определения активной области таблицы
	     */
	    this.getFixedCellsHeight = function() {
	    	
	    	var height = GTController.table.find('.row-headers').outerHeight() + GTController.table.find('.row-divider').outerHeight();
			return height;
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

			var offset = correctHeight - this.gradeCellsHeight[this.topVisibleCell];
			
			// Смещение таблицы
			this.offsetVertical(offset);
			this.bottomVisibleCell = this.getBottomVisibleCell();
			
			if( Math.abs(offset) < 10 )
			{// смещение слишком маленькое. ячейку и без того было почти всю видно, сместим еще на одну
				this.moveUpOne();
			}
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

			var offset = this.gradeCellsHeight[this.bottomVisibleCell] - correctHeight;

			// Смещение таблицы
			this.offsetVertical(offset);
			this.topVisibleCell = this.getTopVisibleCell();
			
			if(Math.abs(offset) < 10)
			{// смещение слишком маленькое. ячейку и без того было почти всю видно, сместим еще на одну
				this.moveDownOne();
			}
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
		
		this.collapse = function() {
			// Получение максимального отступа для отображения низа таблицы
			GTController.wrapper.toggleClass("collapsed");
		}
		
		/**
		 * Инициализация навигации таблицы
		 */
		this.initNavigationActions = function() {
			GTController.actions.find('.action-up.action').click(function() {
				GTController.moveUpOne();
		    });
			GTController.actions.find('.action-down.action').click(function() {
				GTController.moveDownOne();
		    });
			GTController.actions.find('.action-uplist.action').click(function() {
				GTController.moveUpPage();
		    });
			GTController.actions.find('.action-downlist.action').click(function() {
				GTController.moveDownPage();
		    });
			GTController.actions.find('.action-totop.action').click(function() {
				GTController.moveToTop();
		    });
			GTController.actions.find('.action-tobottom.action').click(function() {
				GTController.moveToBottom();
		    });
			
			GTController.actions.find('.action-right.action').click(function() {
				GTController.collapse();
		    });
		}

    	this.initLessonEditEvents = function() {
    		$(  'table.dof-groupjournal-templans tr td.cell-number > .dof-lesson-info-form-edit a,'+
				'table.dof-groupjournal-templans tr td.cell-status .dof-lesson-info-form-edit a').click(function(e){
    			e.preventDefault();
    			
    			
    			var imodal = $('<div>').
    				addClass('dof_imodal')
    				.appendTo('body');
    			
    			var imodalback = $('<div>')
    				.addClass('dof_imodal_back')
    				.click(function(){
	    				$('.dof_imodal').remove();
	    				// Обновление журнала
	    				document.dispatchEvent(GTController.customEvents.journalRefresh);
	    			})
	    			.appendTo(imodal);
    			
    			var imodaldialog = $('<div>')
					.addClass('dof_imodal_dialog')
					.appendTo(imodal);
    			var imodalheader = $('<div>')
					.addClass('dof_imodal_header')
					.appendTo(imodaldialog);
    			var imodalbody = $('<div>')
					.addClass('dof_imodal_body')
					.css('height', imodaldialog.height() - imodalheader.outerHeight() - 40 + 'px')
					.appendTo(imodaldialog);
    			
    			$('<h2>')
    				.text('Управление занятием')
    				.appendTo(imodalheader);
    			$('<div>')
    				.text('×')
    				.addClass('dof_imodal_button_close')
    				.click(function(){
	    				$('.dof_imodal').remove();
	    				// Обновление журнала
	    				document.dispatchEvent(GTController.customEvents.journalRefresh);
    				}).appendTo(imodalheader);
    			
				$('<iframe>')
					.attr({
						'src': $(this).attr('href') + '&page_layout=popup',
						'class': 'dof_imodal_iframe'
					})
					.appendTo(imodalbody);
				
				// триггерbм событие добавления загрузочного окна iframe
				$.event.trigger('addIframeLoading', [imodalbody]);
    			
    			return false;
    		});
    		
	    }
		
		GTController.init();
	};
});
