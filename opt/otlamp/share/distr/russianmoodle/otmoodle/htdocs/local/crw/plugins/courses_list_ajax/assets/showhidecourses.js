require(['jquery'], function($) {
	$(function(){
		$('.clajax_morecourses').on('click', function(){
			if($(this).hasClass('clajax_hidden'))
			{
				var toshow = $(this).data('count_to_show');
				$(this).parents('.clajax_coursescategory').find('tbody tr').each(function(index){
					if ( index >= toshow )
					{
						$(this).addClass('crw_clajax_morethan');
					}
				});
				var img = $(this).find('img').clone().attr('src',$(this).data('morecoursessrc'));
				$(this).text($(this).data('morecourses')).prepend(img).removeClass('clajax_hidden');
			} else
			{
				$(this).parents('.clajax_coursescategory').find('tbody tr.crw_clajax_morethan').removeClass('crw_clajax_morethan');
				var img = $(this).find('img').clone().attr('src',$(this).data('lesscoursessrc'));
				$(this).text($(this).data('lesscourses')).prepend(img).addClass('clajax_hidden');
			}
		});
	});
});