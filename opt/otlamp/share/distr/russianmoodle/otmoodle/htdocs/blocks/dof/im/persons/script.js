require(['jquery', 'jqueryui'], function($) {
	$('#maincheck').on('change',function () {
	    var $that = $(this);
	    $that.closest('table').find('tr td .checkbox').prop('checked', $that.prop('checked'));
	});
	$('.checkbox').on('change', function(){
	    var $that = $(this),
	        ul = $that.closest('table').find('tr td'),
	        main = $('#maincheck');
	    main.prop('checked', $(':checkbox',ul).length == $(':checkbox:checked',ul).length);
	});
});