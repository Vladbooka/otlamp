M.format_opentechnology = M.format_opentechnology || {};

M.format_opentechnology.togglestate;
M.format_opentechnology.courseid;
M.format_opentechnology.ourYUI;
M.format_opentechnology.numSections;

/**
 * Инициализация механизма сохранения статуса разделов
 * 
 * @param {Object} Y YUI instance
 * @param {String} theCourseId the id of the current course to allow for settings for each course.
 * @param {String} theToggleState the current state of the toggles.
 * @param {Integer} theNumSections the number of sections in the course.
 * @param {Integer} theTogglePersistence Persistence on (1) or off (0).
 * @param {Integer} theDefaultTogglePersistence Persistence all open (1) or all closed (0) when thetogglestate is null.
 */
M.format_opentechnology.init = function(Y, theCourseId, theToggleState, theNumSections) {
    
	// Инициализация
    this.ourYUI = Y;
    this.courseid = theCourseId;
    this.togglestate = theToggleState;
    this.numSections = parseInt(theNumSections);
    
    // Создание слушателя событий для всех переключателей
    Y.delegate('click', this.toggleClick, Y.config.doc, 'ul.format_opentechnology_sections .toggle', this);
    
    // Создание слушателя события для сворачивания/разврачивания всех разделов
    var allopen = Y.one("#toggles-all-opened");
    if (allopen) 
    {
        allopen.on('click', this.allOpenClick);
    }
    var allclosed = Y.one("#toggles-all-closed");
    if (allclosed) 
    {
        allclosed.on('click', this.allCloseClick);
    }

    // выделим данные в отдельные блоки для отображения во всплывающем окне
    this.separateModInfo();
    // слушаем события для управления всплывающими окнками
    Y.delegate('mouseenter', this.showModInfo, Y.config.doc, 'ul.format_opentechnology_sections.icon_elements_view.not-editing .section .activity .activityinstance img.activityicon', this);
    Y.delegate('mouseleave', this.hideModInfo, Y.config.doc, 'ul.format_opentechnology_sections.icon_elements_view.not-editing .section .activity .activityinstance img.activityicon', this); 
    Y.delegate('mouseenter', this.showModInfo, Y.config.doc, '.format_opentechnology_mod_info', this);
    Y.delegate('mouseleave', this.hideModInfo, Y.config.doc, '.format_opentechnology_mod_info', this);
    // слушаем клик по отметке о выполнении вручную
    Y.delegate('click', this.changeState, Y.config.doc, '.format_opentechnology_mod_info input[type=image]', this);
    
};

var checkStateInterval;
M.format_opentechnology.changeState = function(e){
	//при отметке о выполнении вручную - проверяем новое значение и меняем картинку на нужную
	
	//получим старое значение
	var oldval = e.currentTarget.ancestor().one('input[name=completionstate]').get('value');
	//по интервалу будем проверять отличается ли новое значение от старого
	checkStateInterval = setInterval(function(){
		var newval = e.currentTarget.ancestor().one('input[name=completionstate]').get('value'); 
		if ( oldval!=newval)
		{
			var activityid = e.currentTarget.ancestor('.format_opentechnology_mod_info').get('id').split('format_opentechnology_mod_info_')[1];
			var activityicon = Y.one('#'+activityid+' img.activityicon');
			if ( activityicon != null )
			{
				var activityiconsrc = activityicon.get('src').split('/');
				if(newval==0)
				{//элемент выполнен
					activityiconsrc[activityiconsrc.length-1] = activityiconsrc[activityiconsrc.length-1].split('_')[0] + "_complete";
				}
				else
				{//элемент не выполнен
					activityiconsrc[activityiconsrc.length-1] = activityiconsrc[activityiconsrc.length-1].split('_')[0] + "_nonecomplete";
				}
				
				//установим новое изображение только тогда, когда оно загрузится
				var statusimage = new Image();
				statusimage.onload = function(){
					//устанавливаем новый адрес изображения
					activityicon.set('src',this.src);
				};
				statusimage.src = activityiconsrc.join('/');
			}
			
			//убраем интервал
			clearInterval(checkStateInterval);
		}		
	},250);
	//ставим ограничение по времени, чтобы не проверять статус вечно, в случае если что-то пойдет не так
	setTimeout(function(){
		clearInterval(checkStateInterval);
	},30000);
}

M.format_opentechnology.separateModInfo = function(){
	var activities = Y.all('ul.format_opentechnology_sections.icon_elements_view.not-editing .section .activity'); 
	activities.each(function(activity){

		var modInfo = Y.Node.create('<div id="format_opentechnology_mod_info_' + activity.get('id') + '">')
			.addClass('format_opentechnology_mod_info')
			.hide()
			.appendTo(Y.one('body'));
		
		var actionsNode = activity.one('.actions');
		if (actionsNode)
		{
			//var actionsClone = actionsNode.cloneNode(true);
			modInfo.append(actionsNode);
		}

		var instanceNameNode = activity.one('.instancename');
		if (instanceNameNode)
		{
			if ( activity.hasClass('otformat-modindent-0') )
			{
				var instanceNameClone = instanceNameNode.cloneNode(true);
				modInfo.append(instanceNameClone);
			}
			else
			{
				modInfo.append(instanceNameNode);				
			}
		}

		var availabilityInfoNode = activity.one('.availabilityinfo');
		if (availabilityInfoNode)
		{
			//var availabilityInfoClone = availabilityInfoNode.cloneNode(true);
			modInfo.append(availabilityInfoNode);
		}
		
		var contentAfterLinkNode = activity.one('.contentafterlink');
		if (contentAfterLinkNode)
		{
			//var availabilityInfoClone = availabilityInfoNode.cloneNode(true);
			modInfo.append(contentAfterLinkNode);
		}
	});
}

var closeModInfoTimeout; 
M.format_opentechnology.showModInfo = function(e) {
	clearTimeout(closeModInfoTimeout);
		
	if ( !e.currentTarget.hasClass('format_opentechnology_mod_info') )
	{//мы навели не на саму подсказку (иначе она уже отображена, делать ничего не надо)
		//значит навели на элемент, способный вызвать подсказку
		var activity = e.currentTarget.ancestor('.activity');
		if (activity)
		{
			var modInfo = Y.one('#format_opentechnology_mod_info_' + activity.get('id'));
			if ( modInfo )
			{//проверим, не повторно ли мы тут
	
				//похоже, мы здесь впервые, надо скрыть все подсказки и отобразить свою
				Y.all('.format_opentechnology_mod_info').hide();
				modInfo.show();
				
				var iconNode = activity.one('img.iconlarge');
				if (iconNode)
				{
					modInfo.setY(iconNode.getY()+iconNode.get('clientHeight')/4*3);
					//e.currentTarget.hasClass('otformat-modindent-0')
					if ( activity.getStyle('float')=='left' || (iconNode.getX()-400) <= 0 )
					{
						modInfo.setX(iconNode.getX()+iconNode.get('clientWidth')/4*3);
					}
					else
					{
						modInfo.setX(iconNode.getX()+iconNode.get('clientWidth')/4-400);
					}
				}
			}
		}
	}
}

M.format_opentechnology.hideModInfo = function(e) {
	closeModInfoTimeout = setTimeout(function(){
		Y.all('.format_opentechnology_mod_info').hide();
	},150);
}

/**
 * Обработчик клика по переключателю раздела
 * 
 * @param e - Событие клика
 */
M.format_opentechnology.toggleClick = function(e) {
	
	e.preventDefault();
	// Получение номера раздела
    var toggleIndex = parseInt(e.currentTarget.get('id').replace("toggle-", ""));
    // Переключить состояние раздела
    this.toggle_topic(e.currentTarget, toggleIndex, e);
};

/**
 * Переключить состояние раздела
 * 
 * @param targetNode
 * @param toggleNum
 */
M.format_opentechnology.toggle_topic = function(targetNode, toggleNum, e) {
	
    var state; 
    if ( ! targetNode.hasClass('accordion') ) {
    	// Спойлер
    	if ( ! targetNode.hasClass('toggle_open') ) {
    		targetNode.addClass('toggle_open').removeClass('toggle_closed');
            targetNode.next('.toggled_section').addClass('sectionopen');
            state = true;
        } else {
        	targetNode.addClass('toggle_closed').removeClass('toggle_open');
            targetNode.next('.toggled_section').removeClass('sectionopen');
            state = false;
        }
    } else {
    	// Аккордеон
    	if ( ! targetNode.hasClass('toggle_open') ) {
        	this.allCloseClick(e);
    		targetNode.addClass('toggle_open').removeClass('toggle_closed');
            targetNode.next('.toggled_section').addClass('sectionopen');
            state = true;
            if(history.pushState) {
                history.pushState(null, null, '#'+targetNode.ancestor('li.section').get('id'));
            }
            else {
                location.hash = '#'+targetNode.ancestor('li.section').get('id');
            }
        } else {
        	this.allCloseClick(e);
            return true;
        }
    }
    // Установить состояние раздела
    this.set_toggle_state(toggleNum, state);
    this.save_toggles();
};

M.format_opentechnology.set_toggle_state = function(toggleNum, state) {
	
	if ( this.togglestate[toggleNum] ) {
		this.togglestate = this.togglestate.substring(0, toggleNum) + (+ state) + this.togglestate.substring((toggleNum + 1));
	}
}

M.format_opentechnology.save_toggles = function(state) {
	if ( state ) {
		this.togglestate = state;
	}
	M.format_opentechnology.set_user_preference('format_opentechnology_sectionscollapsestate_' + this.courseid, this.togglestate);
};

M.format_opentechnology.set_user_preference = function(name, value) {
    YUI().use('io', function(Y) {
        var url = M.cfg.wwwroot + '/course/format/opentechnology/setuserpref.php?sesskey=' +
                M.cfg.sesskey + '&pref=' + encodeURI(name) + '&value=' + encodeURI(value);
        
        var cfg = {
                method: 'get',
                on: {}
            };
        if (M.cfg.developerdebug) {
            cfg.on.failure = function(id, o, args) {
                console.log("Error updating format_opentechnology preference '" + name + "' using AJAX.  Almost certainly your session has timed out.  Clicking this link will repeat the AJAX call that failed so you can see the error: ");
            }
        }
        Y.io(url, cfg);
    });
};

M.format_opentechnology.allOpenClick = function(e) {
    e.preventDefault();
    M.format_opentechnology.ourYUI.all(".format_opentechnology_sections .toggled_section").addClass('sectionopen');
    M.format_opentechnology.ourYUI.all(".format_opentechnology_sections .toggle").addClass('toggle_open').removeClass('toggle_closed');
    var toggles = M.format_opentechnology.ourYUI.all(".toggled_section");
    var state = '1';
    toggles.each(function (taskNode) {
    	state = state + '1';
    });
    M.format_opentechnology.save_toggles(state);
};

M.format_opentechnology.allCloseClick = function(e) {
    e.preventDefault();
    M.format_opentechnology.ourYUI.all(".format_opentechnology_sections .toggled_section").removeClass('sectionopen');
    M.format_opentechnology.ourYUI.all(".format_opentechnology_sections .toggle").addClass('toggle_closed').removeClass('toggle_open');
    var toggles = M.format_opentechnology.ourYUI.all(".toggled_section");
    var state = '1';
    toggles.each(function (taskNode) {
    	state = state + '0';
    });
    M.format_opentechnology.save_toggles(state);
};
