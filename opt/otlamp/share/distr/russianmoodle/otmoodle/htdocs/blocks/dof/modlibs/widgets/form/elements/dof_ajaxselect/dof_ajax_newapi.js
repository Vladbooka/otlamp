define('block_dof/dof_ajaxselect', ['jquery', 'block_dof/dof_ajax', 'block_dof/dof_str'], function($, dof_ajax, dof_str) {
	
	return {
		
		init : function (methodName, on, staticvars, currentElementSelector, value) {
			
			var args = {};
			var on = JSON.parse(on)
			var staticvars = JSON.parse(staticvars)
			staticvars.forEach(function(item, i, vars) {
				args[item.varname] = item.staticvalue
			})
			var currentElement = $(currentElementSelector);
			var focusElement = $(on.selector);
			var choosestr = '--- Не выбрано ---';
			if (on.choosestr !== undefined) {
				dof_str.get_strings([
		            { key: on.choosestr.key, ptype: on.choosestr.ptype, pcode: on.choosestr.pcode, param: null },
		        ]).done(function (result) {
		        	choosestr = result[0]
		        })
			}
			
			if (currentElement !== undefined && focusElement !== undefined) {
				
				focusElement.on('change',function () {

					// отображение спиннера
					currentElement.parent().addClass("dof_ajaxselect_spinner")
					args[on.varname] = focusElement.val()
					var requests = dof_ajax.call([{
				        methodname : methodName,
				        args: args
				    }]);
					requests[0]
						.done(function (response) {
							currentElement.empty();
							currentElement.append("<option value='0'>"+choosestr+"</option>");
							var found = false
						    // устанавливаем новые варианты в select
							$.each(response, function(key, val) {
								if (key == value) {
									found = true
									currentElement.append("<option value='"+key+"'>"+val+"</option>");
									currentElement.val(value)
						        } else {
						        	currentElement.append("<option value='"+key+"'>"+val+"</option>");
						        }
							}); 
							if (!found) {
								currentElement.val(0)
							}
						})
						.fail(function(e){
							currentElement.empty();
							console.log(e)
						})
						.always(function(){
							currentElement.change();
							// удаление спиннера
							setTimeout(function(){
								currentElement.parent().removeClass("dof_ajaxselect_spinner")
							}, 350)
						})
				})
				focusElement.change()
			}
		}
	}
});