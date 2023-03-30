require(['jquery','core/str'],function($, strman){
	$(function(){
		var gettitle = strman.get_string('mastercourse_title', 'block_mastercourse');
		$.when(gettitle).done(function(title){
			$('.mastercourse-sign').remove();
			var sign = $('<span>').text(title.toLowerCase()).addClass('mastercourse-sign');
			$('ul.breadcrumb > li[data-node-type="20"] a').prepend(sign);
		});
	})
});