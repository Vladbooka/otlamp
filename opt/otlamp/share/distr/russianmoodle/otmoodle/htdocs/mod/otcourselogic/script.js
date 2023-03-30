require(['jquery', 'jqueryui', 'core/ajax'], function($, jqui, ajax) {
	$(document).ready(function () {

		var elem = $('div.otcourselogic_card div.otcourselogic_card_row');
		
		// Первоначальное позиционирование всплывающих блоков
		elem.find('.otcourselogic_actions_action span.otcourselogic_action_hint .dropblock').each(function (i, element) {
			var topPos = 0;
			if ( ($(element).parent().offset().top - $(element).outerHeight(true)) >= 0 ) {
				topPos = -$(element).outerHeight(true)
			}
			
			$(element).css({
				'right' : '-50px',
				'top': topPos
			});
		});
		
		// Всплывающая информация о задаче
		elem.find('.otcourselogic_actions_action span.otcourselogic_action_hint').on({
			mouseenter: function () {
	    		$(this).find('.dropblock').show();
	    	},
	    	
	    	mouseleave: function () {
	    		$(this).find('.dropblock').hide();
	    	}
	    });
	})
	
	// Сортировка действий
	$('.otcourselogic_card').each(function(){
		var card = $(this);
		$(this).sortable({
			items: ".otcourselogic_row_sortable",
			handle: ".otcourselogic_action_name",
			cursor: 'move',
			helper: function(event, element){
				return $('<div>').css({
					'width': '100%',
					'height': element.height()+'px',
					'background': '#EEE'
				});
			},
			opacity: 0.75,
			axis: "y",
			cursorAt: { left: 5 },
			start: function(event, ui) {
				ui.placeholder.css({
					'height': ui.helper.height()+'px'
				});
			},
			stop: function(){
				var result = card.sortable("toArray", {
					'attribute': 'data-id'
				});
				
				var newarr = [];
				// Формирование массива
				result.forEach(function(item, i) {
					newarr.push({'actionid': item, 'sortorder': i});
				});

				card.append('<div class="mod_otcourselogic_loading"></div>');
		    	// Запрос на сортировку
		    	var requests = ajax.call([{
			        methodname : 'mod_otcourselogic_set_sortorder_actions',
			        args: {
			        	'order': newarr,
			        	'processor': card.data('id')
		        	}
			    }]);
		    	
				requests[0].always(function (response) {
					card.find("div.mod_otcourselogic_loading").remove();
				});
			}
		});
	});
});
	
