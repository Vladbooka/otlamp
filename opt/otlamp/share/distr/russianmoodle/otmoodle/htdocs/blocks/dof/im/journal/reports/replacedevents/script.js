require(['jquery', 'jqueryui'], function($) {
    // JQuery is available via $
	$(document).ready(function(){
		$('.replacedevents div.toggle').click(function(){
			$(this).parent().parent().toggleClass('expanded');
	    });
		$('#tabs').tabs();
	});
});