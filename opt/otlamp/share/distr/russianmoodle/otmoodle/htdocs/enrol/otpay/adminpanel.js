require(['jquery', 'core/ajax'], function($, ajax) {
	
	$('.admin_panel_row select').change(function () {
		$element = $(this);
		$recordid = $(this).closest('tr.admin_panel_row').data('id');
		$newstatus = $element.val();
		$newstatusname = $element.find(":selected").text();
		
		// AJAX Запрос на смену статуса
		requests = ajax.call([
	        {
	            methodname: 'enrol_otpay_update_record_status',
	            args: { instanceid: $recordid, newstatus: $newstatus}
	        }
	    ]);

		requests[0].done(function(result) {
			if ( result ) {
				// Статус успешно сменился
				$element.parent().html($newstatusname);
			}
		}).fail(function(result) {
			// Ошибка
			$element.parent().html('Error while processing...');
		})
	})
});