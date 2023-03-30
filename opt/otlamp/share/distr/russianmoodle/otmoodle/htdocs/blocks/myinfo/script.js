require([ 'core/ajax', 'jquery' ], function(ajax, $) 
{  
	
	
	get_actual_not_graded = function(element){
		$(element).data('requireupdate', true);
		get_notgraded(element);
	};
	
	get_notgraded = function(element){
		$(element).addClass('loading');
		var userid = $(element).data('userid');
		var requireupdate = $(element).data('requireupdate');
		if( requireupdate === undefined )
		{
			requireupdate = false;
		}
		$(element).data('requireupdate', false);

		var ajaxelement = element;
		var requests = ajax.call([{
	        methodname : 'block_notgraded_get_count',
	        args: { 
	        	'userid': parseInt(userid),
	        	'requireupdate': requireupdate
        	}
	    }]);

	    requests[0]
	    	.done(function(response){
	    		$(ajaxelement)
	    			.removeClass('loading')
					.attr('data-value', response);
	    		
	    		var oldcounterlabel = $(ajaxelement).next('.block_myinfo_counters_counter_label');
	    		var newcounterlabel;
	    		if( parseInt(response) > 0 )
    			{
	    			var mainwrapper = $(ajaxelement).parent('.block_myinfo_counters_notgraded');
	    			if( mainwrapper.hasClass('block_myinfo_counters_counter_hidden'))
    				{
	    				mainwrapper.fadeIn('slow', function(){
	    					$(this).removeClass('block_myinfo_counters_counter_hidden');
	    				});
    				}
	    			
	    			newcounterlabel = $('<a>')
		    			.attr('href','/blocks/notgraded/notgraded_courses.php?userid='+userid);
    			} else
				{
    				newcounterlabel = $('<span>');
				}
	    		newcounterlabel
					.addClass(oldcounterlabel.attr('class'))
    				.html(oldcounterlabel.html());
	    		
	    		oldcounterlabel.replaceWith(newcounterlabel);
	    		
	    		if (requireupdate == false)
    			{
		    		setTimeout(function(){
		    			get_notgraded(ajaxelement);
		    		}, $(ajaxelement).data('cache-lifetime')*1000);
    			}
		    })
		    .fail(function(ex){
		    	$(ajaxelement)
		    		.removeClass('loading')
					.attr('data-value', ' ! ');
		    	
	    		var oldcounterlabel = $(ajaxelement).next('.block_myinfo_counters_counter_label');
	    		var newcounterlabel = $('<span>')
	    			.addClass(oldcounterlabel.attr('class'))
	    			.html(oldcounterlabel.html());
	    		oldcounterlabel.replaceWith(newcounterlabel);
		    	
		    	var message = 'Во время получения данных произошла ошибка. Вероятно, у вас нет прав просматривать непроверенные работы других пользователей или сервер не доступен. Обратитесь к специалисту, обслуживающему вашу систему для решения вопроса.';
		    	if( ex !== undefined && ex.message !== undefined )
	    		{
		    		message = ex.message;
	    		}
		    	$(ajaxelement)
		    		.hover(function(){
		    			var p = $(this).offset();
		    			$('<div>')
		    				.html(message)
		    				.addClass('block_myinfo_counters_counter_value_loading_failed_helper')
		    				.css({
		    					'position': 'absolute',
		    					'width': '250px',
		    					'padding': '10px',
		    					'box-sizing': 'border-box',
		    					'background': '#FFF',
		    					'border': '1px solid #CCC',
		    					'left': ( (p.left-250) < 0? 0 : (p.left-250) )+'px',
		    					'top': p.top+'px'
		    				})
		    				.appendTo('body');
			    		},function(){
			    			$('.block_myinfo_counters_counter_value_loading_failed_helper').remove();
			    		});
		    });
	};
	
	$('.block_myinfo_counters_notgraded .block_myinfo_counters_counter_value').each(function(){
		var cacheremainedtime = $(this).data('cache-remained-time');
		var _this = this;
		setTimeout(function(){
			get_notgraded(_this);
		}, cacheremainedtime*1000);
		$(this).bind('dblclick', function(){
			get_actual_not_graded(_this);
		});
	});  
});