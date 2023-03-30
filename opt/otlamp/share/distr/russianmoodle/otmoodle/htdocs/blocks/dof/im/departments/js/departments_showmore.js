require(['jquery'], function($) {
	$(function() {
		$('span.depnamedots').click(function() {
			$(this).addClass('hidden');
			$(this).prev().find('span.depnametail').removeClass('hidden');
		});
	});
});