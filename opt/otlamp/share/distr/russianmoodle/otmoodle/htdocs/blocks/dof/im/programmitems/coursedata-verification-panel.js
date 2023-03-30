require(['jquery', 'block_dof/dof_ajax', 'core/ajax'], function($, dajax, majax){

	$('.dof_im_pitem').each(function(){
		var verificationRequest = function(editingpitem, method){
			editingpitem
				.find('.dof_im_pitem_request_verification,.dof_im_pitem_accept_coursedata,.dof_im_pitem_decline_coursedata')
				.addClass('disabled')
				.unbind('click');
			editingpitem.find('.loadingfailed').remove();
			editingpitem.addClass('loading');
			
			var pitemid = editingpitem.closest('.dof_im_pitem').data('programmitem-id');
			var panel = editingpitem.closest('.coursedata_verification_panel');
			var isblock = panel.data('block-mastercourse');
			
			var ajax;
			console.log(isblock);
			if(isblock=='1')
			{
				ajax = majax;
				method = 'block_mastercourse_' + method;
			} else
			{
				ajax = dajax;
				method = 'im_programmitems_' + method;
			}
			
			var responses = ajax.call([{
		        methodname : method,
		        args: {
		        	'programmitemid': pitemid
		    	}
		    }]);
			responses[0]
				.done(function (response) {
					if(response !== false)
					{
						editingpitem.replaceWith(response);
						var editedpitem = $('#dof_im_pitem_'+pitemid);
						editedpitem.data('programmitem-verificationrequested', editedpitem.attr('data-programmitem-verificationrequested'));
						initPitem(editedpitem);								
					}
				})
				.fail(function(){
					var loadingFailedText = panel.data('str-verification-state-loading-failed');
					var loadingFailedElement = $('<div>').addClass('loadingfailed').text(loadingFailedText)
					editingpitem.removeClass('loading');
					editingpitem.find('.dof_im_pitem_name').first().after(loadingFailedElement);
					initPitem(editingpitem);
					//editingpitem.replaceWith();
				});
		}
		var initPitem = function(pitem){
			
			var requestverificationbutton = pitem.find('.dof_im_pitem_request_verification').first();
			var acceptcoursedata = pitem.find('.dof_im_pitem_accept_coursedata').first();
			var declinecoursedata = pitem.find('.dof_im_pitem_decline_coursedata').first();
			
			if(pitem.data('programmitem-verificationrequested')==1)
			{
				requestverificationbutton
					.unbind('click')
					.text(pitem.closest('.coursedata_verification_panel').data('str-verification-requested'))
					.addClass('disabled');
				acceptcoursedata
					.unbind('click')
					.removeClass('disabled')
					.click(function(){
						var editingpitem = $(this).closest('.dof_im_pitem');
						verificationRequest(editingpitem,'accept_coursedata');
					});
				declinecoursedata
					.unbind('click')
					.removeClass('disabled')
					.click(function(){
						var editingpitem = $(this).closest('.dof_im_pitem');
						verificationRequest(editingpitem,'decline_coursedata');
					});
			} else
			{
				requestverificationbutton
					.unbind('click')
					.text(pitem.closest('.coursedata_verification_panel').data('str-request-verification'))
					.removeClass('disabled')
					.click(function(){
						var editingpitem = $(this).closest('.dof_im_pitem');
						verificationRequest(editingpitem,'request_coursedata_verification');
					});
			}
		}
		initPitem($(this));
		
	});
});