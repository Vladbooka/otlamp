/**
 * JavaScript for form editing assignfeedback conditions.
 *
 * @module moodle-availability_assignfeedback-form
 */
M.availability_assignfeedback = M.availability_assignfeedback || {};

/**
 * @class M.availability_assignfeedback.form
 * @extends M.core_availability.plugin
 */
M.availability_assignfeedback.form = Y.Object(M.core_availability.plugin);
 
/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param param not used now
 */
M.availability_assignfeedback.form.initInner = function(params) {
	this.assigns = {
		'values': [],
		'options': [],
		'feedbacks': []
	};

	if( params !== undefined )
	{
		if( params.suitablemodules !== undefined && params.suitablemodules.length > 0 )
		{
			var assignoptions = [];
			var assignvalues = [];
			var assignfeedbacks = [];
	    	for (var i = 0; i < params.suitablemodules.length; i++)
	    	{
	            var assign = params.suitablemodules[i];
	            assignvalues.push(assign.cmid);
	            assignoptions.push('<option value="' + assign.cmid + '" >' + assign.name + '</option>');
	            
	            assignfeedbacks[assign.cmid] = {
	            	'options': [],
	            	'values': []
	            };
	            for(var assignfeedbackcode in assign.feedbacks)
	        	{
	            	var assignfeedbackname = assign.feedbacks[assignfeedbackcode];
	            	assignfeedbacks[assign.cmid].options.push('<option value="' + assignfeedbackcode + '">' + 
	            	        assignfeedbackname + '</option>');
	            	assignfeedbacks[assign.cmid].values.push(assignfeedbackcode);
	        	}
	        }
			this.assigns = {
				'values': assignvalues,
				'options': assignoptions,
				'feedbacks': assignfeedbacks
			};
		}
	}
	
    
};

/**
 * Creating form
 *
 * @method getNode
 * @param {Object} json object with saved data 
 */
M.availability_assignfeedback.form.getNode = function(json) {
	// Языковые строки
    var strings = M.str.availability_assignfeedback;

    var assigndisabled = '';
    if( this.assigns.options.length == 0 )
	{
    	assigndisabled = ' disabled="disabled" ';
	}
    
    // Форма настроек ограничения доступа
    var assignselect = '<select name="assign" '+assigndisabled+' class="custom-select mx-1">'+
    	'<option value="0">'+strings.chooseassign+'</option>'+
    	this.assigns.options.join('')+'</select>';
    var defaultassignfeedbackoption = '<option value="0" selected="selected">'+strings.chooseassignfeedback+'</option>';
    var assignfeedbackselect = '<select name="assignfeedback" class="custom-select mx-1">'+defaultassignfeedbackoption+'</select>';
    var html = strings.inassign + assignselect + strings.needfeedback + assignfeedbackselect;
    var node = Y.Node.create('<div class="form-group">' + html + '</div>');

    // Установка сохраненных значений
    if ( json.assign !== undefined ) 
    {
		var inarray = this.assigns.values.indexOf(json.assign);
		if ( inarray !== -1 )
		{
			node.one('select[name=assign]').set('value', json.assign);
		}
	}
    
    // Настройка событий
    if (!M.availability_assignfeedback.form.addedEvents) {
        M.availability_assignfeedback.form.addedEvents = true;
        var root = Y.one('.availability-field');

        root.delegate('valuechange', function() {
            M.core_availability.form.update();
        }, '.availability_assignfeedback select');
    }
    
    node.one('select[name=assign]').on('change', function(){
    	var _node = node;
    	M.availability_assignfeedback.form.loadAssignFeedbacks(_node);
    });
    
    M.availability_assignfeedback.form.loadAssignFeedbacks(node, json.assignfeedback);
    
    return node;
};

M.availability_assignfeedback.form.loadAssignFeedbacks = function(node, savedvalue) {
	// Языковые строки
    var strings = M.str.availability_assignfeedback;
    
	var assignselect = node.one('select[name=assign]');
	var assignfeedbackselect = node.one('select[name=assignfeedback]');
	var assignfeedbackvalue;
	
	if( typeof savedvalue === 'undefined')
	{
		// Попытка получить указанное значение в отзыве, чтобы сохранить при изменении задания при возможности 
		var assignfeedbackoption = assignfeedbackselect.one('option:checked');

    	if( assignfeedbackoption !== null )
		{
    		assignfeedbackvalue = assignfeedbackoption.get('value');
		}
	} else
	{
		assignfeedbackvalue = savedvalue;
	}
	
	
	
    if( assignselect !== null && assignfeedbackselect !== null )
    {
    	var assignoption = assignselect.one('option:checked');
    	
    	if( assignoption !== null && assignoption.get('value') !== 0)
		{
    		assignfeedbackselect.removeAttribute('disabled');
    		var defaultassignfeedbackoption = '<option value="0" selected="selected">'+strings.chooseassignfeedback+'</option>';
        	if( typeof this.assigns.feedbacks[assignoption.get('value')] !== 'undefined')
    		{
        		assignfeedbackselect.setHTML(defaultassignfeedbackoption + 
        				this.assigns.feedbacks[assignoption.get('value')].options.join(''));
    		}
        	
        	if( assignfeedbackvalue !== null )
    		{
        	    var inarray;
        		if( typeof this.assigns.feedbacks[assignoption.get('value')] === 'undefined')
    			{
        			inarray = -1;
    			} else
    			{
    				inarray = this.assigns.feedbacks[assignoption.get('value')].values.indexOf(assignfeedbackvalue);
    			}
        		
        		if ( inarray !== -1 )
    			{
    	        	assignfeedbackselect.set('value', assignfeedbackvalue);
    			}
    		}
		} else
		{
			assignfeedbackselect.set('disabled','disabled');
		}
    }
};

/**
 * Fill value with form-data to send to server
 *
 * @method fillValue
 * @param {Object} value
 * @param {Object} node
 */
M.availability_assignfeedback.form.fillValue = function(value, node) {

	var assign = node.one('select[name=assign] option:checked');
	if( assign !== null ) {
		value.assign = assign.get('value');
	}

	var assignfeedback = node.one('select[name=assignfeedback] option:checked');
	if( assignfeedback !== null ) {
		value.assignfeedback = assignfeedback.get('value');
	}
	
};
 
M.availability_assignfeedback.form.fillErrors = function(errors, node) {
    var cmid = parseInt(node.one('select[name=assign]').get('value'), 10);
    if (cmid === 0) {
        errors.push('availability_assignfeedback:error_selectcmid');
    }
    var feedbackcode = parseInt(node.one('select[name=assignfeedback]').get('value'), 10);
    if (feedbackcode === 0) {
        errors.push('availability_assignfeedback:error_selectfeedbackcode');
    }
};