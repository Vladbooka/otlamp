YUI.add('moodle-availability_otcompare-form', function (Y, NAME) {

/**
 * JavaScript for form editing compare conditions.
 *
 * @module moodle-availability_otcompare-form
 */
M.availability_otcompare = M.availability_otcompare || {};

/**
 * @class M.availability_otcompare.form
 * @extends M.core_availability.plugin
 */
M.availability_otcompare.form = Y.Object(M.core_availability.plugin);
 
/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param param not used now
 */
M.availability_otcompare.form.initInner = function(params) {
//	if(params!==undefined)
//	{
//		if(params.otcourselogics!==undefined)
//		{
//			this.otcourselogics = params.otcourselogics;
//		}
//	}
	
    
};

/**
 * Creating form
 *
 * @method getNode
 * @param {Object} json object with saved data 
 */
M.availability_otcompare.form.getNode = function(json) {
    var strings = M.str.availability_otcompare;
    
    //форма настроек ограничения доступа
    var html = '';
//    html += '<div><label><input name="source" type="radio" value="coursedate"/> ' + strings.setcoursedate + '</label></div>';
//    html += '<div><label><input name="source" type="radio" value="enrollmentdate"/> ' + strings.setenrollmentdate + '</label></div>';
//    html += '<div><label><input name="source" type="radio" value="unenrollmentdate"/> ' + strings.setunenrollmentdate + '</label></div>';
//    html += '<div><label><input name="source" type="radio" value="courselastaccessdate"/> ' + strings.setcourselastaccessdate + '</label></div>';
//    
//    if( this.otcourselogics.length > 0 )
//	{
//    	html += '<div><label><input name="source" type="radio" value="sincecourselogicactivate"/> ' + strings.setcourselogicactivatedate + '</label></div>';
//    	html += '<select name="courselogiccminstance">';
//    	for (var i = 0; i < this.otcourselogics.length; i++)
//    	{
//            var otcl = this.otcourselogics[i];
//            // String has already been escaped using format_string.
//            html += '<option value="' + otcl.cminstance + '">' + otcl.name + '</option>';
//        }
//		html += '</select>';
//	}
//    
//    html += '<div><label>' + strings.setduration + ': <input name="duration" type="text"/>\
//    		<select name="durationmeasure">\
//				<option value="w">' + strings.durationmeasurew + '\
//				<option value="d" selected="true">' + strings.durationmeasured + '\
//				<option value="h">' + strings.durationmeasureh + '\
//    			<option value="m">' + strings.durationmeasurem + '\
//    		</select>\
//    		</label></div>';
    var node = Y.Node.create('<div>' + html + '</div>');

//    if(json.instanceid!==undefined){
//    	node.all('input[name=source]').each(function(){
//    		this.setAttribute('name', 'source['+json.instanceid+']');
//    	});
//    }
//    
//    //установка сохраненных настроек
//    if ( json.source !== undefined ) {
//    	node.one('input[name^=source][value='+json.source+']').set('checked', true);
//    	if ( json.source == 'sincecourselogicactivate' )
//		{
//    		if ( json.courselogiccminstance !== undefined )
//			{
//    			node.one('select[name=courselogiccminstance]').set('value', json.courselogiccminstance);
//			}
//		}
//	} else
//	{
//		node.one('input[name^=source]').set('checked',true);
//	}
//    
//    if(json.duration!==undefined) {
//    	node.one('input[name=duration]').set('value', json.duration);
//	} else {
//		//значение по умолчанию
//		node.one('input[name=duration]').set('value', 1);
//	}
//    
//    if(json.durationmeasure!==undefined) {
//    	node.one('select[name=durationmeasure]').set('value', json.durationmeasure);
//	}
//    
//    //настройка событий
//    if (!M.availability_otcompare.form.addedEvents) {
//        M.availability_otcompare.form.addedEvents = true;
//        var root = Y.one('#fitem_id_availabilityconditionsjson');
//        root.delegate('click', function() {
//            M.core_availability.form.update();
//        }, '.availability_duration input[type=radio]');
//
//        root.delegate('valuechange', function() {
//            M.core_availability.form.update();
//        }, '.availability_duration input[type=text], .availability_duration select');
//    }
 
    return node;
};

/**
 * Fill value with form-data to send to server
 *
 * @method fillValue
 * @param {Object} value
 * @param {Object} node
 */
M.availability_otcompare.form.fillValue = function(value, node) {
//	
//	var instanceid = node.get('id');
//	value.instanceid = instanceid;
//	node.all('input[type=radio]').each(function(){
//		if(this.get('name')=='source')
//		{
//			this.setAttribute('name', 'source['+instanceid+']');			
//		}
//	});
//	
//	var source=node.one('input[name^=source]:checked');
//	if(source!=null) {
//		value.source = source.get('value');
//	}
//	
//    var duration = node.one('input[name=duration]');
//    if(duration!=null) {
//    	value.duration = parseInt(duration.get('value'));
//    }
//    
//    var durationmeasure = node.one('select[name=durationmeasure]');
//    if(durationmeasure!=null) {
//    	value.durationmeasure = durationmeasure.get('value');
//    }
//    
//    var courselogiccminstance = node.one('select[name=courselogiccminstance]');
//    if(courselogiccminstance!=null) {
//    	value.courselogiccminstance = courselogiccminstance.get('value');
//    }   
};
 
M.availability_otcompare.form.fillErrors = function(errors, node) {
};

}, '@VERSION@', {"requires": ["base", "node", "event", "moodle-core_availability-form"]});
