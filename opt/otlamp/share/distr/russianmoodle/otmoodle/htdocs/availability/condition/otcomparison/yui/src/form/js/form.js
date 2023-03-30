/**
 * JavaScript for form editing compare conditions.
 *
 * @module moodle-availability_otcomparison-form
 */
M.availability_otcomparison = M.availability_otcomparison || {};

/**
 * @class M.availability_otcomparison.form
 * @extends M.core_availability.plugin
 */
M.availability_otcomparison.form = Y.Object(M.core_availability.plugin);
 
/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param param not used now
 */
M.availability_otcomparison.form.initInner = function(vars) {
	this.fields = [];
	this.preprocessors = [];
	this.operators = [];
	this.date_example = '';
	this.days_explanation = '';
	
	if ( vars !== undefined )
	{
		if ( vars.fields !== undefined )
		{
			this.fields = vars.fields;
		}
		if ( vars.preprocessors !== undefined )
		{
			this.preprocessors = vars.preprocessors;
		}
		if ( vars.operators !== undefined )
		{
			this.operators = vars.operators;
		}
		if ( vars.date_example !== undefined )
		{
			this.date_example = vars.date_example;
		}
		if ( vars.days_explanation !== undefined )
		{
			this.days_explanation = vars.days_explanation;
		}
	}
};

/**
 * Creating form
 *
 * @method getNode
 * @param {Object} json object with saved data 
 */
M.availability_otcomparison.form.getNode = function(json) {

    var otcomparison = this;
    
	// Языковые строки
    var strings = M.str.availability_otcomparison;
    
    // Элементы настройки условия ограничения доступа
    var elements = [];
    
    // Поля профиля, включая настраиваемые. Выпадающий список.
    var fieldsoptions = [];
	fieldsoptions.push('<option value="0">' + strings.choose_source + '</option>');
    for (var fieldshortname in otcomparison.fields)
	{
    	fieldsoptions.push('<option value="' + fieldshortname + '">' + 
    	        otcomparison.fields[fieldshortname].name + '</option>');
	}
    elements.push('<select name="source" class="custom-select col-12 mb-3">'+fieldsoptions.join('')+'</select>');

    // Предобработка. Выпадающий список.
    fieldsoptions = [];
	fieldsoptions.push('<option value="0">' + strings.choose_preprocessor + '</option>');
    for (var k in this.preprocessors)
	{
    	fieldsoptions.push('<option value="' + k + '">' + this.preprocessors[k] + '</option>');
	}
    elements.push('<select name="preprocessor" class="custom-select col-5">'+fieldsoptions.join('')+'</select>');

    // Операции сравнения. Выпадающий список.
    fieldsoptions = [];
	fieldsoptions.push('<option value="0">' + strings.choose_operator + '</option>');
    for (k in this.operators)
	{
    	fieldsoptions.push('<option value="' + k + '">' + this.operators[k] + '</option>');
	}
    elements.push('<select name="operator" class="custom-select col-4">'+fieldsoptions.join('')+'</select>');

    // Значение. Текстовое поле.
    elements.push('<input type="text" name="amount" value="" class="form-control col-2"/>');

    // Пример даты
    elements.push('<div class="otcomparison_date_example" style="display: none;">'+
            otcomparison.date_example+'</div>');
    // Пояснение к указанию количества дней
    elements.push('<div class="otcomparison_days_explanation" style="display: none;">'+
            otcomparison.days_explanation+'</div>');
    
    var node = Y.Node.create('<div class="row justify-content-between">' + elements.join('') + '</div>');
    

    // Установка сохраненных значений
    if ( json.source !== undefined ) 
    {
		if ( node.one('select[name=source]>option[value='+json.source+']') != null )
		{
			node.one('select[name=source]').set('value', json.source);
		}
	}
	M.availability_otcomparison.form.loadPreprocessors(otcomparison, node, json.preprocessor);
    if ( json.operator !== undefined ) 
    {
		if ( node.one('select[name=operator]>option[value='+json.operator+']') != null )
		{
			node.one('select[name=operator]').set('value', json.operator);
		}
	}
    if ( json.amount !== undefined ) 
    {
		if ( node.one('input[name=amount]') != null )
		{
			node.one('input[name=amount]').set('value', json.amount);
		}
	}
    
    
    //настройка событий
    if (!M.availability_otcomparison.form.addedEvents) 
    {
        M.availability_otcomparison.form.addedEvents = true;

        Y.one('.availability-field').delegate(
        		'valuechange', 
        		function() {
    	            M.core_availability.form.update();
    	        }, 
    	        '.availability_otcomparison select[name=source],'+
    	        '.availability_otcomparison select[name=preprocessor],'+
    	        '.availability_otcomparison select[name=operator],'+
    	        '.availability_otcomparison input[type=text][name=amount]'
            );
    }
 

    // при изменении источника, подгружать допустимые препроцессоры
    node.one('select[name=source]').on('change', function(){
    	var _node = node;
    	M.availability_otcomparison.form.loadPreprocessors(otcomparison, _node);
    });
    // при изменении препроцессора показывать пример даты, но только при необходимости
    var preprocessorElement = node.one('select[name=preprocessor]');
    preprocessorElement.on('change', function(e){
    	var _node = node;
    	M.availability_otcomparison.form.loadExample(otcomparison, _node, e.currentTarget);
    });
	M.availability_otcomparison.form.loadExample(otcomparison, node, preprocessorElement);
    
    return node;
};

M.availability_otcomparison.form.loadExample = function(otcomparison, node, preprocessorElement) {
	
	var dateExampleElement = node.one('div.otcomparison_date_example');
	if ( dateExampleElement != null && preprocessorElement != null)
	{
		if ( preprocessorElement.get('value') == 'date' )
		{
			dateExampleElement.show();
		} else
		{
			dateExampleElement.hide();
		}
    }
	
	var daysExplanationElement = node.one('div.otcomparison_days_explanation');
	if ( daysExplanationElement != null && preprocessorElement != null)
	{
		if ( preprocessorElement.get('value') == 'days' )
		{
			daysExplanationElement.show();
		} else
		{
			daysExplanationElement.hide();
		}
    }
};

M.availability_otcomparison.form.loadPreprocessors = function(otcomparison, node, savedValue) {
    
    // Выпадающий список с полями профиля
	var sourceElement = node.one('select[name=source]');
	// Выпадающий список с предобработками
	var preprocessorElement = node.one('select[name=preprocessor]');
	var preprocessorValue;
	
	if( typeof savedValue === 'undefined')
	{// Сохраненное значение не передано - проверяем выбирали ли в этом поле значение ранее (до смены поля профиля)
		var preprocessorOption = preprocessorElement.one('option:checked');
    	if( preprocessorOption !== null )
		{// Есть выбранное значение - будем использовать его
    		preprocessorValue = preprocessorOption.get('value');
		}
	} else
	{// Сохраненное значение
		preprocessorValue = savedValue;
	}

    if( sourceElement !== null && preprocessorElement !== null )
    {
    	var sourceOption = sourceElement.one('option:checked');
    	
    	if( sourceOption !== null && sourceOption.get('value') !== "0")
		{// Поле профиля выбрано
    		// Снимаем блокировку с поля выбора предобработки, если была
    		preprocessorElement.removeAttribute('disabled');
    		
    		// Снимаем блокировку с опция поля выбора предобработки, если были
    		preprocessorElement.all('option').removeAttribute('disabled');
    		var sourceValue = sourceOption.get('value');
    		if( typeof otcomparison.fields[sourceValue] !== 'undefined' && otcomparison.fields[sourceValue].type != 'datetime' )
    		{// Опции указания даты и дней только для полей профиля типа Дата, для остальных - блокируем
    			preprocessorElement.one('option[value=date]').set('disabled','disabled');
    			preprocessorElement.one('option[value=days]').set('disabled','disabled');
    		}

        	if( preprocessorValue !== null )
    		{// Имеется сохраненное значение (или выбранное до смены поля профиля)
        		// Попытка получить опцию по имеющемуся значению
        		var optionToSelect = preprocessorElement.one('option[value='+preprocessorValue+']');
        		if( optionToSelect !== null )
				{// Есть такая опция
        			if ( optionToSelect.get('disabled') !== null && optionToSelect.get('disabled') !== false )
    				{// Опция заблокирована для выбора - сбрасываем на нулевую ("Выберите опцию")
        				preprocessorElement.set('value', "0");	
    				} else
					{
        				preprocessorElement.set('value', preprocessorValue);
					}
				}
    		}
		} else
		{// Поле профиля не выбрано - запрещаем выбирать предобработку
			preprocessorElement.set('value', "0");	
			preprocessorElement.set('disabled','disabled');
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
M.availability_otcomparison.form.fillValue = function(value, node) 
{
	// Поле профиля
	var source = node.one('select[name=source] option:checked');
	if( source !== null ) {
		value.source = source.get('value');
	}
	
	// Предобработка
	var preprocessor = node.one('select[name=preprocessor] option:checked');
	if( preprocessor !== null ) {
		value.preprocessor = preprocessor.get('value');
	}
	
	// Оператор
	var operator = node.one('select[name=operator] option:checked');
	if( operator !== null ) {
		value.operator = operator.get('value');
	}
	
	// Значение
	var amount = node.one('input[type=text][name=amount]');
	if( amount !== null ) {
		value.amount = amount.get('value');
	}
};
 
M.availability_otcomparison.form.fillErrors = function(errors, node) 
{
	// Поле профиля должно быть выбрано
	var source = node.one('select[name=source] option:checked');
    if ( source !== null && source.get('value') == "0" ) 
    {
        errors.push('availability_otcomparison:error_selectsource');
    }
    
    // Предобработка должна быть выбрана
	var preprocessor = node.one('select[name=preprocessor] option:checked');
    if ( preprocessor !== null && preprocessor.get('value') == "0" ) 
    {
        errors.push('availability_otcomparison:error_selectpreprocessor');
    }
    
    // Оператор должен быть выбран
	var operator = node.one('select[name=operator] option:checked');
    if ( operator !== null && operator.get('value') == "0" ) 
    {
        errors.push('availability_otcomparison:error_selectoperator');
    }
    
    // Значение должно быть указано
	var amount = node.one('input[type=text][name=amount]');
    if ( amount !== null ) 
    {
    	var amountval = amount.get('value');
    	if( amountval == "")
		{
    		errors.push('availability_otcomparison:error_fillvalue');
		}
    	
    	if ( preprocessor !== null && preprocessor.get('value') != 'date')
		{// препроцессор - не дата, нужно проверить, что укзаано целое число
    		if ( ! /^-?[0-9]+$/.test(''+amountval) )
			{
        		errors.push('availability_otcomparison:error_invalidfilledvalue');
			}
		}
    }
    
};