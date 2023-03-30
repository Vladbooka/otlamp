require(['jquery'], function($) {
	$(function() {
		$('.achievementcat-header').click(function() {
			$(this).parent('.achievementcat').toggleClass('hide');
		});
	});
});