define(['jquery', 'core/str'], function($, str) {
	return {
		_component: 'filter_otspoiler',
		/**
		 * Базовая инициализация
		 */
        init: function(){
        	var _this = this,
        		show = '',
        		hide = '';
        	$(function(){
        		
        		str.get_string('show', _this._component).done(function(string){
            		show = '<span class="show-otspoiler"><span class="show-otspoiler-inner">'+string+'</span></span>';
            		str.get_string('hide', _this._component).done(function(string){
            			hide = '<span class="hide-otspoiler"><span class="hide-otspoiler-inner">'+string+'</span></span>';
            			$('div.otspoiler').each(function(){
            				if($(this).parents('.editor_atto_content').length === 0)
            				{// Спан не в редакторе
            					var spoilercontent = '<div class="otspoiler-inner">'+$(this).html()+'</div>';
                    			$(this).html(show+hide+spoilercontent);
                    			$(this).unbind('click').click(function(){
                            		$(this).toggleClass('expand');
                            	});
            				}
                		});
            		});
    			});
        	});
        }
	};
});