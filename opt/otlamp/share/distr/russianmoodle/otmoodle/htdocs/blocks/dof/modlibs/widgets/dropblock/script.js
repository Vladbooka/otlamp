require(['jquery'], function($) {
	
var dropBlockController = new DBController();
	
	function DBController() {
		
		var DBController = this;
		
		// Состояния
		this.isResizeEvent = false;
		
		/**
		 * Базовая инициализация
		 */
		this.init = function() {
			if ( DBController.isResizeEvent === false ) {
				DBController.isResizeEvent = true;
				$(window).on('resize', function() {
					DBController.resetPosition();
				});
			}
			
			// Перемещение всех выпадающих блоков в пул
			var dropblockNode = $('<div class="dof_dropblock_node"></div>');
			$('.dof_dropblock').each(function() {
				$(this).appendTo(dropblockNode);
			})
			dropblockNode.appendTo('body');
			
			DBController.resetPosition();
			DBController.initActions();
	    };
	    
	    /**
	     * Бинд событий
	     */
	    this.initActions = function() {
	    	// Подписка на события дропблока
	    	document.addEventListener("dropBlockRefreshEvent", DBController.refresh, false);
	    	
	    	// Отображение выпадающео блока
	    	$('.dof_dropblock_control').click(function(e) {
	    		if ( ! $(this).is(':checked') ) {
	    			// Скрытие всех дочерних выпадающих блоков
					DBController.hideChildrenByDropBlock($(this).parent());
					$('#dof_dropblock_wrapper_' + $(this).attr('id')).
	    				removeClass('active').trigger('dropblock:deactivate');
	    		} else {
	    			// Отображение блока
					DBController.showByController($(this));
	    		}
	    		
	    	});
			
			// Блокировка скрытия при клике внутри выпадающего блока
			$('.dof_dropblock').click(function(e) {
				e.stopPropagation();
				
				// Скрытие всех дочерних выпадающих блоков
				DBController.hideChildrenByDropBlock($(this));
			});
			
			// Действия
			$(document).on('click', function(e) {
				DBController.reset();
			});
	    };
	    
	    /**
	     * Обновление 
	     */
	    this.refresh = function() {
	    	
	    	// Удаление существующих дропблоков
	    	$('body > div.dof_dropblock_node').remove();
	    	
	    	// Инициализация новых дропблоков
	    	DBController.init();
	    	
	    	// Событие о отом, что дропблоки переиницилизировались
	    	document.dispatchEvent(new CustomEvent("dropBlockRefreshed"),);
	    };
	    
	    /**
	     * Пересчет позиций элементов
	     */
	    this.resetPosition = function() {
	    	$('.dof_dropblock_control:checked').each(function() {
	    		var dropblock = $(this).parent();
		    	var label = $('label[for='+  $(this).attr('id')  +'].dof_dropblock_actionblock');
		    	
		    	// Позиционирование выпадающего блока относительно кнопки активации
		    	DBController.setPosition(dropblock, label);
	    	});
	    };
	    
	    /**
	     * Сброс состояний всех выпадающих блоков
	     */
	    this.reset = function() {
	    	$('.dof_dropblock_control:checked').each(function() {
	    		DBController.closeDropblock($(this).parent())
	    	});
	    };

	    
	    /**
	     * Закрытие одного выпадающего блока
	     */
	    this.closeDropblock = function(dropblock) {
	    	var dropblockController = dropblock.children('input.dof_dropblock_control').first();
	    	dropblockController.prop('checked', false);
    		$('#dof_dropblock_wrapper_' + dropblockController.attr('id')).
    			removeClass('active').trigger('dropblock:deactivate');
	    }
	    
	    /**
	     * Скрытие всех дочерних выпадающих блоков
	     */
	    this.hideChildrenByDropBlock = function(dropblock) {
	    	
	    	// Поиск дочерних
	    	var wrappers = dropblock.find('.dof_dropblock_content .dof_dropblock_wrapper');
	    	
	    	// Скрытие всех дочерних выпадающих блоков
	    	wrappers.each(function() {
				var controller = $('#' + $(this).attr('data-id')).prop('checked', false);
				$(this).removeClass('active').trigger('dropblock:deactivate');
				
				// Поиск дочерних выпадающих блоков
				dropblock = controller.parent();
				DBController.hideChildrenByDropBlock(dropblock);
			});
	    }
	    
	    /**
	     * Позиционирование выпадающего блока относительно целевого блока
	     */
	    this.setPosition = function(dropblock, targetBlock) {
			
	    	var direction = targetBlock.parent().data('direction');
	    	if (direction === undefined)
    		{
	    		direction = auto;
    		}
	    	
			// Базовая активная область для позиционирования выпадающего блока
			var activeArea = {
				'top' : $(document).scrollTop(),
				'right' : $(document).scrollLeft() + $(window).outerWidth(),
				'bottom' : $(document).scrollTop() + $(window).outerHeight(),
				'left' : $(document).scrollLeft()
			};
			
			// Опрос всех родительских блоков на корректировку активной области
			targetBlock.parents().each(function() {
				if ( $(this).css('overflow') == 'hidden' ) {
					// Найдена нода, в рамках которой необходимо высчитывать позиционирование
					var offset = $(this).offset();
					
					// Корректировка активной зоны
					if ( activeArea.top < offset.top ) {
						activeArea.top = offset.top;
					}
					if ( activeArea.right > offset.left + $(this).outerWidth() ) {
						activeArea.right = offset.left + $(this).outerWidth();
					}
					if ( activeArea.bottom > offset.top + $(this).outerHeight() ) {
						activeArea.bottom = offset.top + $(this).outerHeight();
					}
					if ( activeArea.left < offset.left ) {
						activeArea.left = offset.left;
					}
				}
			});

			// Определение позиции целевого блока
			var targetBlockOffset = targetBlock.offset();
			var targetBlockPosition = {
				'top' : targetBlockOffset.top,
				'right' : targetBlockOffset.left + targetBlock.outerWidth(),
				'bottom' : targetBlockOffset.top + targetBlock.outerHeight(),
				'left' : targetBlockOffset.left
			};
			
			var dropBlockContent = dropblock.children('.dof_dropblock_content');
			
			if (direction == 'auto')
			{
			// Поиск оптимальной позиции по высоте
			var baseYCoord = targetBlockPosition.top;
			var bottomspace = activeArea.bottom - targetBlockPosition.bottom - dropBlockContent.outerHeight();
			var upspace = targetBlockPosition.top - activeArea.top - dropBlockContent.outerHeight();
			
			if ( upspace > bottomspace ) {
				// Места сверху больше
				dropBlockContent.data('v','bottom');
				baseYCoord = targetBlockPosition.bottom - dropBlockContent.outerHeight();
				if ( upspace < 0 ) {
					// Коррекция по высоте
					baseYCoord -= upspace;
				}
			} else {
				dropBlockContent.data('v','top');
				if ( bottomspace < 0 ) {
					// Коррекция по высоте
					baseYCoord += bottomspace;
				}
			}
			baseYCoord -= 3;
			
			// Поиск оптимальной позиции по ширине
			var baseXCoord = targetBlockPosition.right;
			var rightspace = activeArea.right - targetBlockPosition.right - dropBlockContent.outerWidth();
			var leftspace = targetBlockPosition.left - activeArea.left - dropBlockContent.outerWidth();
			
			if ( leftspace > rightspace ) {
				// Места слева больше
				dropBlockContent.data('h','right');
				baseXCoord = targetBlockPosition.left - dropBlockContent.outerWidth();
				if ( leftspace < 0 ) {
					// Коррекция по ширине
					baseXCoord -= leftspace;
				}
				baseXCoord -= 10;
			} else {
				dropBlockContent.data('h','left');
				if ( rightspace < 0 ) {
					// Коррекция по ширине
					baseXCoord += rightspace;
				}
				baseXCoord += 5;
			}
			}
			if (direction == 'down')
			{
				var baseYCoord = targetBlockPosition.bottom;
				var baseXCoord = targetBlockPosition.left;
				dropBlockContent.data('v','no');
				dropBlockContent.data('h','-');
			}

			// Позиционирование блока
			dropBlockContent
				.removeClass('topleftarrow')
				.removeClass('toprightarrow')
				.removeClass('bottomleftarrow')
				.removeClass('bottomrightarrow')
				.addClass(dropBlockContent.data('v')+dropBlockContent.data('h')+'arrow')
				.css({
					'top' : baseYCoord,
					'left' : baseXCoord,
				});
	    }
	    
	    /**
	     * Показать выпадающий блок
	     */
	    this.showByController = function(controller) {
	    	
	    	var dropblock = controller.parent();
	    	dropblock.data('DBController', this);
	    	var label = $('label[for='+  controller.attr('id')  +'].dof_dropblock_actionblock');
			
	    	// Добавление класса обертки
			label.parent().addClass('active').trigger('dropblock:activate');
			
			// Позиционирование выпадающего блока относительно кнопки активации
	    	this.setPosition(dropblock, label);
	    }
	    
		DBController.init();
	};
});