YUI.add('moodle-availability_badge-form', function (Y, NAME) {

/**
 * JavaScript for form editing badge conditions.
 *
 * @module moodle-availability_badge-form
 */
M.availability_badge = M.availability_badge || {};

/**
 * @class M.availability_badge.form
 * @extends M.core_availability.plugin
 */
M.availability_badge.form = Y.Object(M.core_availability.plugin);
 
/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param param not used now
 */
M.availability_badge.form.initInner = function(badges) {
	this.badges=badges;
};

/**
 * Creating form
 *
 * @method getNode
 * @param {Object} json object with saved data 
 */
M.availability_badge.form.getNode = function(json) {
    var strings = M.str.availability_badge;
    var html = '';
    
    html += '<div class="form-group"><label>' + strings.holdbadge + 
        '</label>: <select name="badgeid" class="custom-select">';
    for (var k in this.badges)
    {
    	var badge = this.badges[k];
    	var type = '';
    	switch(badge.type)
    	{
    		case '1': 
    			type = strings.site;
    			break;
    		case '2':
    			type = strings.course;
    			break;
    	}
        html += '<option value="' + badge.id + '">' + 
        	badge.name + ' [' + type + badge.coursename + ']'+
        	'</option>';
    }
    html += '</select></div>';
    
    var node = Y.Node.create(html);

    if(json.badgeid!==undefined) {
    	node.one('select[name=badgeid]').set('value', json.badgeid);
	}

    //настройка событий
    if (!M.availability_badge.form.addedEvents) {
        M.availability_badge.form.addedEvents = true;
        var root = Y.one('.availability-field');

        root.delegate('valuechange', function() {
            M.core_availability.form.update();
        }, '.availability_badge select');
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
M.availability_badge.form.fillValue = function(value, node) {
	
	var badgeid = node.one('select[name=badgeid]');
	if( badgeid != null ) {
		value.badgeid = badgeid.get('value');
	}
};
 
M.availability_badge.form.fillErrors = function() {};


}, '@VERSION@', {"requires": ["base", "node", "event", "moodle-core_badge-form"]});
