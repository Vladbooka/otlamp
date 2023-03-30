require(['jquery'], function($) {
	$(function(){
		$('#showhidecodes')
			.data('visible',false)
			.bind('click',function(){
				var othercodes = $('.coupon_system.mform #fitem_id_coupon_code2,'+
								   '.coupon_system.mform #fitem_id_coupon_code3,'+
								   '.coupon_system.mform #fitem_id_coupon_code4,'+
								   '.coupon_system.mform #fitem_id_coupon_code5');
				if($(this).data('visible')==true)
				{
					othercodes.hide();
					$(this)
						.data('visible',false)
						.text(M.util.get_string('coupon_more_codes', 'enrol_otpay'));
				} else
				{
					othercodes.show();
					$(this)
						.data('visible',true)
						.text(M.util.get_string('coupon_hide_codes', 'enrol_otpay'));
				}
			});
	});
});