YUI.add('moodle-availability_activetime-form', function (Y, NAME) {

/**
 * JavaScript for form editing activetime conditions.
 *
 * @module moodle-availability_activetime-form
 */
M.availability_activetime = M.availability_activetime || {};

/**
 * @class M.availability_duration.form
 * @extends M.core_availability.plugin
 */
M.availability_activetime.form = Y.Object(M.core_availability.plugin);
 
/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param param not used now
 */
M.availability_activetime.form.initInner = function(params) {
	var html = '';
	for (var key in params.timeoptions) {
        var timeoption = params.timeoptions[key];
        // String has already been escaped using format_string.
        html += '<option value="' + key + '">' + timeoption + '</option>';
    }
	this.get_timeoptions =  html;
	
};

/**
 * Creating form
 *
 * @method getNode
 * @param {Object} json object with saved data 
 */
M.availability_activetime.form.getNode = function(json) {
	var html = '<label>' + M.util.get_string('title', 'availability_activetime') + '</label>';
	html += '<span class="availability-group form-group">' +
            '<label><input type="checkbox" name="min" class="form-check-input"/>' + 
                M.util.get_string('option_min', 'availability_activetime') + 
            '</label>' +
            '<input type="text" name="minval" class="form-control mx-1" title="' + 
                M.util.get_string('label_min', 'availability_activetime') + '"/>' +
            '<select name="time_min" class="custom-select">';
	html += this.get_timeoptions;
	html += '</select></span>';
	html += '<span class="availability-group form-group">' +
            '<label><input type="checkbox" name="max" class="form-check-input"/>' + 
                M.util.get_string('option_max', 'availability_activetime') + 
            '</label>' +
            '<input type="text" name="maxval" class="form-control mx-1" title="' + 
                M.util.get_string('label_max', 'availability_activetime') + '"/>' +
            '<select name="time_max" class="custom-select">';
	html += this.get_timeoptions;
	html += '</select></span>';
	var node = Y.Node.create('<div>' + html + '</div>');
	
    // Set initial values.
    if (json.min !== undefined) {
        node.one('input[name=min]').set('checked', true);
        node.one('input[name=minval]').set('value', json.min);
        node.one('select[name=time_min]').set('value', json.time_min);
    }
    if (json.max !== undefined) {
        node.one('input[name=max]').set('checked', true);
        node.one('input[name=maxval]').set('value', json.max);
        node.one('select[name=time_max]').set('value', json.time_max);
    }
	
	// Disables/enables text input fields depending on checkbox.
    var updateCheckbox = function(check, focus) {
        var input = check.ancestor().next('input');
        var select = input.next('select');
        var checked = check.get('checked');
        input.set('disabled', !checked);
        select.set('disabled', !checked);
        if (focus && checked) {
            input.focus();
        }
        return checked;
    };
    node.all('input[type=checkbox]').each(updateCheckbox);
    
 // Add event handlers (first time only).
    if (!M.availability_activetime.form.addedEvents) {
        M.availability_activetime.form.addedEvents = true;

        var root = Y.one('.availability-field');
        
        root.delegate('change', function() {
            // For the grade item, just update the form fields.
            M.core_availability.form.update();
        }, '.availability_activetime select[name=time_min]');
        
        root.delegate('change', function() {
            // For the grade item, just update the form fields.
            M.core_availability.form.update();
        }, '.availability_activetime select[name=time_max]');

        root.delegate('click', function() {
            updateCheckbox(this, true);
            M.core_availability.form.update();
        }, '.availability_activetime input[type=checkbox]');
        
        root.delegate('valuechange', function() {
            // For grade values, just update the form fields.
            M.core_availability.form.update();
        }, '.availability_activetime input[type=text]');

    }
	return node;
};

/**
 * Fill value with form-data to send to server
 *
 * @method fillValue
 * @param {Object} value
 * @param {Object} node
 */
M.availability_activetime.form.fillValue = function(value, node) {
    if (node.one('input[name=min]').get('checked')) {
        value.min = this.getValue('minval', node);
        value.time_min = this.getValueOption('minval', node);
    }
    if (node.one('input[name=max]').get('checked')) {
        value.max = this.getValue('maxval', node);
        value.time_max = this.getValueOption('maxval', node);
    }
};

/**
 * Gets the numeric value of an input field. Supports decimal points (using
 * dot or comma).
 *
 * @method getValue
 * @return {Number|String} Value of field as number or string if not valid
 */
M.availability_activetime.form.getValue = function(field, node) {
    // Get field value.
	var input = node.one('input[name=' + field + ']');
	var value = input.get('value');

    // If it is not a valid positive number, return false.
    if (!(/^[0-9]+([.,][0-9]+)?$/.test(value))) {
        return value;
    }
    return parseFloat(value.replace(',', '.'));
};

M.availability_activetime.form.getValueOption = function(field, node) {
    // Get field value.
	var input = node.one('input[name=' + field + ']');
    var timeoption = input.next('select').get('value');

    return timeoption;
};
 
M.availability_activetime.form.fillErrors = function(errors, node) {
    var value = {};
    this.fillValue(value, node);

    // Check numeric values.
    if ((value.min !== undefined && typeof(value.min) === 'string') ||
            (value.max !== undefined && typeof(value.max) === 'string')) {
        errors.push('availability_activetime:error_invalidnumber');
    } else if (value.min !== undefined && value.max !== undefined &&
            value.min * value.time_min >= value.max * value.time_max) {
        errors.push('availability_activetime:error_backwardrange');
    }
};

}, '@VERSION@');
