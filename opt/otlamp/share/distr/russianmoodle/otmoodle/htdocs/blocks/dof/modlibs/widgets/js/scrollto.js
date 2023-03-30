require(['jquery'], function($) {
	$(function(){
		var scrollto = $('#block_dof_content').data('scrollto');
		if ( typeof scrollto !== undefined )
		{
			var scrolltop = $('#'+scrollto).offset().top;
			var quarterwinheight = $(window).height()/4;
			if( scrolltop - quarterwinheight >= 0)
			{
				scrolltop -= quarterwinheight;
			}
		    $('html, body').animate({
		        scrollTop: scrolltop
		    }, {
		    	duration: 1000,
		        complete: function(){
		        	var scrollto = $('#block_dof_content').data('scrollto');
		        	$('#'+scrollto).addClass('dof_scrolled_here');
		        }
		    });
		}
	});
});