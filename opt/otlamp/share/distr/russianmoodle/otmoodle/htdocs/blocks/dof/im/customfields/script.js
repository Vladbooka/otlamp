var singleselects = document.querySelectorAll('select[data-dofsingleselect="true"]');
if ( singleselects ) {
	for (var i = 0; i < singleselects.length; i++) {
		
		var singleselect = singleselects[i];
		var fieldset = singleselect.closest('fieldset');
		var submit = fieldset.querySelector('input[type="submit"]');
		
		if ( submit ) {
			submit.style.display = "none";
			singleselect.addEventListener("change", function( event ) {
				var fieldset = event.target.closest('fieldset');
				var submit = fieldset.querySelector('input[type="submit"]');
				submit.click();
			});
		}
	}
}

require(['jquery', 'jqueryui', 'block_dof/dof_ajax'], function($, jqui, dof_ajax){
	$('.dof_customfield_sortable').each(function(){
		var _table = $(this);
		$(this).sortable({
			items: "tr.dof_customfield_item",
			handle: ".dof_customfield_sort_handler",
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
				var result = _table.sortable("toArray", {
					'attribute': 'data-customfield-id'
				});

				_table.wrap('<div class="dof_customfields_sorting"></div>');
				
		    	// Запрос на сортировку
		    	var requests = dof_ajax.call([{
			        methodname : 'im_customfields_sort_customfields',
			        args: {
			        	'sorteditems': result
		        	}
			    }]);
		    	
		    	
		    	var __table = _table;
				requests[0].done(function (response) {
					__table.unwrap('.dof_customfields_sorting');
					if( response !== true )
					{
						__table.sortable('cancel');
						$('<div>').text('Не для всех элементов успшено сохранен порядок сортировки').attr('title','Ошибка').dialog();
					}
				}).fail(function (response) {
					__table.unwrap('.dof_customfields_sorting');
					__table.sortable('cancel');
					$('<div>').text(response.message).attr('title','Ошибка').dialog();
				});
			}
		});
	});
});