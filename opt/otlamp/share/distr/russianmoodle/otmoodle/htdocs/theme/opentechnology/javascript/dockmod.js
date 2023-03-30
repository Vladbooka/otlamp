function get_initialised_dock()
{
    return new Promise(function(resolve, reject){
        var dock = document.getElementById('dock'); 
        if (dock !== null)
        {
            resolve(dock);
        } else
        {
            document.addEventListener('DockInitialised', function (e) {
                resolve(document.getElementById('dock'));
            }, false);
        }
    })
}

function customise_dock_for_theme() {
    var dock = M.core_dock;
    dock.cfg.position = 'top';
    
    dock.set('orientation', 'horizontal');
    
    dock.on('dock:initialised', function(){
        var event = document.createEvent('Event');
        event.initEvent('DockInitialised', true, true);
        document.dispatchEvent(event);
    })
    
    dock.on('dock:resizepanelcomplete', function(){
    	resizeDockedItemPanel();
        activeItemTriangle();
    });

    dock.on('dock:itemschanged', function(){
        var event = document.createEvent('Event');
        event.initEvent('DockItemsChanged', true, true);
        document.dispatchEvent(event);
    });
    
    dock.on('dock:itemremoved', function(){
    	is_dock_has_items(dock);
    	theme_opentechnology_handle_resize();
    });
    
    dock.on('dock:itemadded', function(item){
    	is_dock_has_items(dock);
    	
    	var dockeditemtitle = Y.one('html').getAttribute('data-dockeditem-title');
    	if( dockeditemtitle == null )
		{
    		dockeditemtitle = 0;
		}
    	if( parseInt(dockeditemtitle) > 0 )
		{
        	var block = Y.one('#inst'+item.get('blockinstanceid'));
        	if( block !== null )
    		{
				//установим изображение только тогда, когда оно загрузится
				var iconimage = new Image();
				iconimage.onload = function(){
	        		var dockedtitleh2 = item.get('title');
	        		var dockedtitle = dockedtitleh2.ancestor();
	        		dockedtitle
	        			.addClass('prepared')
	    				.setStyle('background-image','url('+this.src+')');
	        		
	        		if( parseInt(dockeditemtitle) == 1 || parseInt(dockeditemtitle) == 3 )
        			{
	        			dockedtitle.addClass('iconview');
        			}
	        		if( parseInt(dockeditemtitle) == 1 )
        			{
	        			dockedtitle.addClass('texthide');
        			}
				};
				iconimage.onerror = function(){
	        		var dockedtitleh2 = item.get('title');
	        		var dockedtitle = dockedtitleh2.ancestor();
	        		dockedtitle
	        			.addClass('prepared')
	        			.addClass('noicon')
	        			.removeClass('texthide')
	    				.setStyle('background-image','none');
				}
				iconimage.src = block.getAttribute('data-block-icon');
    		}
		} else
		{
			Y.all('#dock .dockeditem .dockedtitle').each(function(dockedtitle){
        		dockedtitle.addClass('prepared');
			});
		}
    	theme_opentechnology_handle_resize();
    });
    

}

YUI().use('event', function(Y) {
	Y.on('windowresize', function() {
		theme_opentechnology_handle_resize();
	});
	var collapsiblesectionswitcher = Y.all('.collapsible-section-switcher');
	if( collapsiblesectionswitcher )
	{
		collapsiblesectionswitcher.on('click', function(){
    		theme_opentechnology_handle_resize();
    	});
	}
});

function is_dock_has_items(dock)
{
	var pageheader = Y.one('#page-header');
	if (pageheader !== null)
	{
		if (dock.count > 0)
		{
			pageheader.addClass('dock-has-items');
		} else
		{
			pageheader.removeClass('dock-has-items');
		}
	}
}
	
function theme_opentechnology_handle_resize() {
	var dockeditemswidth = 0;
	var custommenu = Y.one('#h_custommenu_wrapper label.custom_menu_mobile_label'); 
    var headermenuwidth = Y.one("body").get("winWidth") > 425 ? 0 : (custommenu ? custommenu.get('clientWidth') : 0);
    var dockbg = Y.one('#dock_bg');
    var body = Y.one('body');
    if(headermenuwidth > 0)
	{
    	if( dockbg !== null )
    	{
    		dockbg.setStyle('padding-left', headermenuwidth + 'px');
    	}
	} else
	{
		if( dockbg !== null )
		{
			dockbg.setStyle('padding-left', null);
		}
	}
    var header = Y.one('#dock_bg h1');
    var langmenu = Y.one('#dock_bg .langmenu_wrapper');
    var dockdiv = Y.one('#dock');
    var positionX = 0;
    var positionY = 0;
    var dockfullwidth = 0;
    var dockheight = 0;
    if( dockbg !== null )
    {
    	positionX = dockbg.getX() + parseFloat(dockbg.getComputedStyle('paddingLeft')) 
		- parseFloat(body.getComputedStyle('marginLeft'));
        positionY = dockbg.getY();
        dockfullwidth = dockbg.get('clientWidth') - parseFloat(dockbg.getComputedStyle('paddingLeft')) - parseFloat(dockbg.getComputedStyle('paddingRight'));
        dockheight = dockbg.get('clientHeight');
    }
    
    
	
    // ширина заголовка
	var headerwidth = 0;
    if ( header != null ) 
    {
    	// по умолчанию заголовок виден, чтобы мы учитывали его размер
    	// и только если не будет влезать - спрячем потом
		header.setStyle('display', 'inline-block');
    	var headerwidth = header.get('clientWidth');
    }
    
    // ширина панели выбора языка
    var langmenuwidth = 0;
    if (langmenu != null)
	{
    	var langmenuwidth = langmenu.get('clientWidth');
	}
    
    // тип отображения док-панели
	var dockeditemtitle = Y.one('html').getAttribute('data-dockeditem-title');
	if( dockeditemtitle == null )
	{
		dockeditemtitle = 0;
	}

	if( parseInt(dockeditemtitle) == 2 || parseInt(dockeditemtitle) == 4 )
	{// включена настройка отображения иконок, если элементы не помещаются
		//включаем принудительно отображение, которое было настроено, для просчета суммарной ширины элементов в изначальном виде
		var dockeditemswidth = 0;
		Y.all('#dock .dockeditem').each(function(dockeditem){
			var dockedtitle = dockeditem.one('.dockedtitle');
			if( parseInt(dockeditemtitle) == 2 )
			{// включено отображение текстом, если помещается
				dockedtitle.removeClass('iconview');
				dockedtitle.removeClass('texthide');
			}
			if( parseInt(dockeditemtitle) == 4 )
			{// включено отображение текст+иконки, если помещаются
				dockedtitle.addClass('iconview');
				dockedtitle.removeClass('texthide');
			}
			dockeditemswidth+=dockeditem.get('clientWidth');
		});
		
		// место под элементы док-панели
		var availableplace = dockfullwidth - headerwidth - (headerwidth > 0 ? 10 : 0) - langmenuwidth;
	    if( availableplace < (dockeditemswidth + 10) )
		{//места под элементы меньше, чем они занимают - включаем отображение иконками
			dockeditemswidth = 0;
			// включение отображения иконками и просчет суммарной ширины в таком виде
    		Y.all('#dock .dockeditem').each(function(dockeditem){
    			var dockedtitle = dockeditem.one('.dockedtitle');
        		if ( dockedtitle !== null )
    			{
        			if( ! dockedtitle.hasClass('noicon') )
    				{
            			dockedtitle.addClass('iconview');
            			dockedtitle.addClass('texthide');
    				}
    			}
        		dockeditemswidth+=dockeditem.get('clientWidth');
    		});
		}
	} else
	{// просчет суммарной ширины элементов док-панели
		Y.all('#dock .dockeditem').each(function(dockeditem){
			dockeditemswidth+=dockeditem.get('clientWidth');
		});
	}

	// место, доступное под элементы док-панели
	var availableplace = dockfullwidth - headerwidth - (headerwidth > 0 ? 10 : 0) - langmenuwidth;
    if( availableplace < (dockeditemswidth + 10) )
	{//места под элементы меньше, чем они занимают - прячем заголовок
    	if( header != null )
		{
        	header.setStyle('display', 'none');
		}
    	headerwidth = 0;
	}
    
    // подсчет итоговой ширины и позиции док-панели по горизонтали
    var left = positionX + headerwidth + (headerwidth > 0 ? 10 : 0);
	var width = dockfullwidth - headerwidth - (headerwidth > 0 ? 10 : 0) - langmenuwidth;
    
    // установка позиции док-панели
	if(dockdiv)
	{
		if(body.hasClass('dir-rtl'))
		{
			dockdiv.setStyle('right', (left + langmenuwidth) + 'px');
		} else
		{
			dockdiv.setStyle('left', left + 'px');
		}
		
		if (body.hasClass('ot-sticky-enabled'))
	    {
		    if (dockdiv.hasClass('ot-sticky'))
	        {
	            positionY = positionY - window.scrollY;
	            
	        } else
            {
	            if (dockbg !== null)
                {
	                positionY = dockbg.get('clientTop');
                }
            }
	    }
		dockdiv.setStyle('top', positionY + 'px');
		
	    dockdiv.setStyle('width', width + 'px');
	    dockdiv.setStyle('height', dockheight + 'px');
	}
}

function resizeDockedItemPanel()
{

    var html = Y.one('html');
    var body = Y.one('body');
    var dockdiv = Y.one('#dock');
    var dpan = Y.one('#dockeditempanel');
    if ( dpan != null )
	{

        dpan.setStyle('width', 'auto');
        
        var screenheight = parseInt(html.get('clientHeight')) - parseInt(body.getComputedStyle('paddingTop'));
        var screenwidth = parseInt(html.get('clientWidth'));
        var offsetX = dpan.getX();
        var offsetY = dpan.getY();
        
        dpan.setStyle('top', parseInt(dockdiv.get('clientHeight')) + 5 + 'px');
        
        if ( ( offsetX + dpan.get('clientWidth') + 20 > screenwidth ) ) {
        	// вылезает за правый край, прилепим к правому краю
        	dpan.setStyle('left', 'auto');
        	dpan.setStyle('right', '0');
        } else {
        	// отображаем под элементом
        	dpan.setStyle('right', 'auto');
        	dpan.setStyle('left', offsetX - dockdiv.getX() + 'px');
        }
        
        if ( offsetX < 0 ) {
        	// вылезает за левый край, прилепим к левому краю
        	dpan.setStyle('left', '0');
        }

        
        if (body.hasClass('ot-sticky-enabled'))
        {
            // в режиме прилипающей шапки нельзя прокрутить страницу вниз так, чтобы достать до докпанели, которая длиннее страницы
            // поэтому вычисляем максимальную высоту в этом случае по другому
            dpan.setStyle('max-height', (screenheight - (offsetY - window.scrollY) - 5) + 'px');
        } else
        {
            // установка максимальной высоты блока
            if ( screenheight - offsetY > 300 ) {
                dpan.setStyle('max-height', screenheight - offsetY + 'px');
            } else {
                dpan.setStyle('max-height', body.get('clientHeight') - offsetY + 'px');
            }
        }
	}
}


require(['jquery'], function($){
	$(function(){
		$('#dock .buttons_container').scroll(function(){
			activeItemTriangle();
		});
	})
});

function activeItemTriangle()
{
	require(['jquery'], function($){
		var activeitem = $('#dock .activeitem');
		if( activeitem.length > 0)
		{
			var activeitempos = activeitem.position();
		    var triangle = activeitem.parent().find('.triangle');
			if ( triangle.length == 0 )
			{
				triangle = $('<div>').addClass('triangle').appendTo(activeitem.parent());
			}
			var triangleleft = activeitempos.left + activeitem.outerWidth()/2 - 5;
			var dpanstart = $('#dockeditempanel').position().left;
			var dpanend = dpanstart + $('#dockeditempanel').outerWidth();
			if( triangleleft+triangle.outerWidth() > dpanend || triangleleft < dpanstart)
			{
				triangle.hide();
			} else
			{
				triangle.show().css('left', triangleleft+'px');
			}
		}
	});
}