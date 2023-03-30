require(['jquery', 'jqueryui'], function($) {
	$(function(){
		
		var sortbutton = $('<div>')
			.addClass('dof_button btn-primary btn')
			.attr('id','achievementcats_order_button')
			.text('Включить режим сортировки')
			.bind('click', function(){
				sortmode();
			})
			.appendTo('#sortachievementcats');
		
		var sortmode = function(){
			var sortarea = $('<div>')
				.attr('id','achievementcats_sort_area')
				.appendTo('#sortachievementcats');

			var sortul = $('<ul>');
			$('#sortachievementcats input[name^=achievementcat_]').each(function(){
				var categotyname = $(this).attr('title');
				var categoryid = $(this).attr('name').split('_')[1];
				var sortli = $('<li>')
					.text(categotyname)
					.data('achievementcatid', categoryid);
				sortul.append(sortli);
			});
			sortul
				.sortable({
					placeholder: "sortachievementcat_placeholder"
				})
				.disableSelection()
				.appendTo(sortarea);

			
			var savebutton = $('<div>')
				.addClass('dof_button btn-primary btn')
				.text('Сохранить')
				.bind('click', function(){
					var sortorder = 1;
					$('#achievementcats_sort_area ul li').each(function(){
						$('#sortachievementcats input[name=achievementcat_'+
							$(this).data('achievementcatid')+']')
							.val(sortorder++);
					});
					$('#sortachievementcats_submit').click();
				})
				.appendTo(sortarea);
			
			var cancelbutton = $('<div>')
				.addClass('dof_button btn-primary btn')
				.text('Отменить')
				.bind('click',function(){
					$('#achievementcats_order_button').show();
					$('#achievementcats_sort_area').remove();
					$('#achievementcats').show();
				})
				.appendTo(sortarea);
			
			$('#achievementcats_order_button').hide();
			$('#achievementcats').hide();
		};
		
	});
});