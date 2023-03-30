require(['jquery', 'core/ajax'], function($, ajax) {
	$('.otpay_instance.otpay_accountgenerate div.otpay_instance_form div.felement.otpay_modal.button').click(function () {
		var form = $(this).closest('form'); 
		$('.otpay_modal_loading').remove();
		$('<div>')
			.addClass('otpay_modal_loading')
			.click(function(){
				form.find('div.otpay_modal_content').addClass('otpay_modal_hide');
				$(this).remove();
			})
			.appendTo('body');
		form.find('.close').click(function () {
			form.find('div.otpay_modal_content').addClass('otpay_modal_hide');
			$('.otpay_modal_loading').remove();
		})
		form.find('div.otpay_modal_content.otpay_modal_hide').removeClass('otpay_modal_hide');
		form.find('.otpay_tab').first().click();
	});

	$('.otpay_tab').click(function(){
		$('.otpay_tab').removeClass('selected');
		$(this).addClass('selected');
	});
});