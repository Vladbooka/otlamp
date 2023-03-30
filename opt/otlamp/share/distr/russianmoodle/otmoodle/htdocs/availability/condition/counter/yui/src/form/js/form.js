M.availability_counter = M.availability_counter || {};

M.availability_counter.form = Y.Object(M.core_availability.plugin);

M.availability_counter.form.grades = null;

/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param {Array} grades Array of objects containing gradeid => name
 */
M.availability_counter.form.initInner = function(grades) 
{
    this.grades = grades;
};

// Сформировать форму для установки данных
M.availability_counter.form.getNode = function(json) 
{
    // Языковые строки
    var strings = M.str.availability_counter;
    
    var html = '<div class="availability-group availability-group-counter">';
    
    if ( this.grades.length > 0 )
    {// Есть элементы
    	html += '<div class="availability-table">'+
    	    '<table class="table table-striped table-sm table-bordered">'+
        	    '<tr>'+
                    '<th>' + strings.cond_form_th_enable + '</th>'+
                    '<th>' + strings.cond_form_th_element + '</th>'+
                    '<th>' + strings.cond_form_th_condition + '</th>'+
                '</tr>';
    	
    	// Для каждого элемента курса с оценкой создадим строку
        for ( var g = 0; g < this.grades.length; g++ ) 
        {
            var grade = this.grades[g];
            
            html += '<tr class="availability-element availability-counter-element">' + 
            		'<td>'+
            		    '<input id="e' + grade.id + 
            		        '" type="checkbox" class="form-check-input" value="' + grade.id + '" name="enable"/>'+
        		    '</td>' + 
            		'<td>' + grade.name + '</td>' + 
            		'<td>' + 
            		    '<div class="form-group">'+
            		        '<label>'+
    	            		    '<input type="checkbox" name="min" class="form-check-input"/>' + 
    	            		    strings.cond_form_option_min +
    	                    '</label>'+
    	                    '<label>'+
    	                        '<span class="accesshide">' + strings.cond_form_label_min + '</span>'+
    	                        '<input type="text" name="minval" class="form-control" title="' +
	                            strings.cond_form_label_min + '"/>'+
	                        '</label>%'+
                        '</div>' +
                        '<div class="form-group">' +
                            '<label>'+
                                '<input type="checkbox" name="max" class="form-check-input"/>' + 
                                strings.cond_form_option_max +
                            '</label>'+
                            '<label>'+
                                '<span class="accesshide">' + strings.cond_form_label_max + '</span>'+
                                '<input type="text" name="maxval" class="form-control" title="' +
                                strings.cond_form_label_max + '"/>'+
                            '</label>%'+
                        '</div>' + 
                    '</td></tr>';
        }
        // Добавим поле учета числа успешно выполненых условий
        html += '</table><div class="form-group"><label>' + 
        		strings.cond_form_label_counter + 
        		'</label><input type="text" name="counter" class="form-control"/></div></div>';
    } else
    {
    	html += '<span class="availability-element-notice">' + strings.cond_form_notice_nooneelements + '</span>';
    }
    html += '</div>';
    
    var node = Y.Node.create('<div class="availability-group-wrapper availability-group-counter-wrapper">' + 
            html + '</div>');
    
    if (json.counter !== undefined )
    {// Число достаточных условий
    	node.one('input[name=counter]').set('value', json.counter);
    }
    if (json.elements !== undefined )
    {// Массив включенных условий передан
    	for (var i = 0; i < json.elements.length; i++) 
        {
    		if ( json.elements[i].id !== undefined )
    		{
    			var checkbox = node.one('#e' + json.elements[i].id );
    			if(checkbox !== null)
				{
    				checkbox.set('checked', true);
	    			var row = checkbox.ancestor('.availability-counter-element');
	    			if ( json.elements[i].min !== undefined ) 
		    		{
	    				row.one('input[name=min]').set('checked', true);
	    				row.one('input[name=minval]').set('value', json.elements[i].min);
		    	    }
		    	    if ( json.elements[i].max !== undefined ) 
		    	    {
		    	    	row.one('input[name=max]').set('checked', true);
		    	    	row.one('input[name=maxval]').set('value', json.elements[i].max);
		    	    }
				}
    		}
        }
    }
    
    // Добавление слушателей событий
    if ( ! M.availability_counter.form.addedEvents ) 
    {
        M.availability_counter.form.addedEvents = true;

        var root = Y.one('.availability-field');
        
        // Сохранение данных формы при клике по чекбоксу
        root.delegate('click', function() 
        {
            M.core_availability.form.update();
        }, '.availability_counter input[type=checkbox]');
        // Сохранение данных формы при изменении содержимого текстовых полей
        root.delegate('valuechange', function() 
        {
            M.core_availability.form.update();
        }, '.availability_counter input[type=text]');
    }

    return node;
};

/**
 * Сохранение значений
 */
M.availability_counter.form.fillValue = function(value, node) 
{
	value.elements = [];
	
	var rows = node.all('.availability-element');
	
	// Функция сбора данных из одной строки таблицы
	var getrowdata = function(node) 
	{
		// Новый элемент
		var element = {};
		
		if ( node.one('input[name=enable]').get('checked') ) 
		{// Элемент включен
			// ID элемента курса
			element.id = parseInt(node.one('input[name=enable]').get('value'), 10);
			if ( node.one('input[name=min]').get('checked') ) 
			{
				element.min = parseFloat(node.one('input[name=minval]').get('value'));
		    }
			if ( node.one('input[name=max]').get('checked') ) 
			{
				element.max = parseFloat(node.one('input[name=maxval]').get('value'));
		    }
			value.elements.push(element);
	    }
    };
    
    // Собрать данные из всех строк таблицы
	rows.each(getrowdata);
	
	// Добавить данные по счетчику
	var counter = node.one('input[name=counter]').get('value');
	value.counter = counter;
};

M.availability_counter.form.fillErrors = function(){};
