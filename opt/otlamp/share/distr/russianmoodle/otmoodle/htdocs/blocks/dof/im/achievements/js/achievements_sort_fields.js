require(['jquery', 'block_dof/dof_ajax', 'jqueryui'], function($, dof_ajax) {
	$(function(){
		var sort;
		$('ul.sortable').each(function(){
			sort = $(this).sortable({
				placeholder: "sort_area_placeholder"
			});
		});
		var departmentid = $('.fields_sort_area').attr('data-departmentid');
		
		var savebutton = $('<div>')
			.addClass('dof_button btn btn-primary save_sort_area')
			.text('Сохранить')
			.on('click', function(){
				var fields = {};
				$('ul.sortable').each(function(){
					fields[$(this).attr('data-position')] = $(this).sortable("toArray");
				});
				// Запрос на получение html кода журнала
		    	var requests = dof_ajax.call([{
			        methodname : 'im_achievements_save_fields_sort_indexes',
			        args: { 
			        	'fields': fields,
			        	'departmentid': departmentid
		        	}
			    }]);
				requests[0]
					.done(function (response) {
	    				// кладем загрузочный блок в элемент
						var loading = $('<div>')
						.addClass('journal-loading-screen-wrapper')
						.appendTo('body');
						
						var loader = $('<div>')
						.addClass('journal-loading-screen-wrapper-in-elem')
						.appendTo(loading);
					
						// добавляем элемент загрузки
						$('<div>')
							.addClass('journal-loading-screen-loader')
							.appendTo(loader);
						
						// по готовности скрываем загрузчик
						setTimeout(function () {
							loader.fadeOut('slow');
							loading.remove();
							window.location = "/blocks/dof/im/achievements/plugins/userinfo/settings.php?departmentid="+departmentid+"&limitnum=30";
						}, 1000);
					})
					.fail(function(ex){
						console.log(ex.message);
					});
		}).appendTo($('.fields_sort_area'));
	});
});