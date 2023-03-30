import $ from 'jquery';

export const init = (otpayinstanceid) => {
    var instance = $('.otpay_instance[data-id="'+otpayinstanceid+'"]');
    if (instance.hasClass('otpay_accountgenerate')) {
        instance.find('.otpay_modal.btn').click();
    } else {
        instance.find('.otpay_instance_form form').submit();
    }
};