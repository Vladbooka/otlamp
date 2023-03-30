require(['jquery'], function($){
	$(function(){
		var elementOverflowedX = function(element){
			element.removeClass('overflowedx');
			if( element[0].scrollWidth > element[0].clientWidth )
			{
				element.addClass('overflowedx');
			}
		};
		$(window).resize(function(){
			elementOverflowedX($('#block_dof'));
		});
		$(window).resize();


		var adminpanel = $('#block_dof_maintable_center_top .block_dof_sections .block_dof_section_menu > .block_dof_section_title');
		adminpanel.addClass('btn').addClass('btn-secondary').addClass('dof_button').parent().addClass('prepared');
		$(document).click(function(e){
			var closest = $(e.target).closest("#block_dof_maintable_center_top .block_dof_sections .block_dof_section_menu");
			if ( closest.length === 0 && adminpanel.parent().hasClass('visible') ) {
				adminpanel.click();
			}
		});
		adminpanel.click(function(){
			adminpanel.parent().toggleClass('visible');
		})
	});
});