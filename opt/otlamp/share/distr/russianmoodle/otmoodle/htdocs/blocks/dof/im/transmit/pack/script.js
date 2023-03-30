require(['jquery', 'block_dof/dof_ajax', 'jqueryui'], function($, dof_ajax, $ui) {
	var strings = {
		'pack_reload_failed': 'Обновление данных завершилось неудачей. Перезагрузите страницу, чтобы увидеть актуальные данные.',
		'pack_action_execution_failed': 'Исполнение действия заврешилось неудачей',
		'pack_delete_confirm': 'Вы уверены, что хотите удалить пакет настроек?',
		'pack_sorting_failed': 'Не удалось сохранить новый порядок исполнения пакетов'
	};
	var reloadpack = function(packid) {
		$('#pack_'+packid).addClass('in-process');
		var requests = dof_ajax.call([{
	        methodname : 'im_transmit_get_pack',
	        args: {
	        	'packid': packid
	    	}
	    }]);
		requests[0].done(function (response) {
			$('#pack_'+packid).replaceWith(response);
			initpack($('#pack_'+packid));
		}).fail(function(){
			alert(strings.pack_reload_failed);
			$('#pack_'+packid).removeClass('in-progress');
		});
	}
	var initpack = function(pack) {
		pack.find('.packtool').each(function(){
			$(this).off('click').on('click', function(){
				var action = $(this).data('action');
				if( action != 'delete' || confirm(strings.pack_delete_confirm) )
				{
					pack.addClass('in-process');
					var requests = dof_ajax.call([{
				        methodname : 'im_transmit_do_pack_action',
				        args: {
				        	'packid': pack.data('id'),
				        	'action': action
				    	}
				    }]);
					requests[0].done(function (response) {

						if(!response)
						{
							alert(strings.pack_action_execution_failed);
							reloadpack(pack.data('id'));
						} else
						{
							if ( action == 'delete' )
							{
								pack.remove();
							} else
							{
								reloadpack(pack.data('id'));
							}
						}
					}).fail(function(){
						alert(strings.pack_action_execution_failed);
						reloadpack(pack.data('id'));
					});
				}
			})
		})
	}
	
	$('.pack').each(function(){
		initpack($(this));
	});

	$('.transmitpacks').first().sortable({
		'cancel': '.packtools-wrapper',
		'items': '.pack',
		'update': function (event, ui) {

			var packsholder = $(this);
			packsholder.addClass('sorting');

			var requests = dof_ajax.call([{
		        methodname : 'im_transmit_set_packs_order',
		        args: {
		        	'packs': packsholder.sortable('toArray', {'attribute': "data-id"})
		    	}
		    }]);
			requests[0].done(function (response) {
				if(!response)
				{
					alert(strings.pack_sorting_failed);
				}
				packsholder.removeClass('sorting');
			}).fail(function(){
				alert(strings.pack_sorting_failed);
				packsholder.removeClass('sorting');
			});
        }
	}).disableSelection();
});