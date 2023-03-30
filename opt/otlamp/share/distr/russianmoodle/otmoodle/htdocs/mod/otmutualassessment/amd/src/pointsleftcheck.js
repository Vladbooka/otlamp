define(['jquery'], function($) {
    return {
    	_points: null,
    	_leftpointsblock: null,
		_submit: null,
		_hiddenpoints: null,
		_leftpoints: null,
        init: function(points) {
        	var obj = this;
        	obj._points = points;
        	obj._leftpointsblock = $('.otmutualassessment-grades-form div.leftpoints'),
        	obj._submit = $('.otmutualassessment-grades-form input[type="submit"]'),
        	obj._hiddenpoints = $('.otmutualassessment-grades-form input[name="pointsleft"]');
        	
        	obj.setLeftPoints();
        	$('.otmutualassessment-grades-form input[type="number"]').each(function() {
        		$(this).on('change keyup', function() {
        			obj.setLeftPoints();
        			if(obj._leftpoints < 0) {
        				obj._leftpointsblock.addClass('negative');
        				$(this).val(Number(obj._hiddenpoints.val()) + Number($(this).val()));
        				obj._leftpointsblock.html(0);
        				obj._hiddenpoints.val(0);
        			} else {
        				obj._leftpointsblock.removeClass('negative');
        			}
        			if(obj._leftpoints > 0) {
        				obj._submit.prop('disabled', true);
        			} else {
        				obj._submit.prop('disabled', false);
        			}
        			
        		});
        	});
        },
        getPointsSumm: function() {
        	var summ = 0;
        	$('.otmutualassessment-grades-form input[type="number"]').each(function() {
        		summ += Number($(this).val());
        	});
        	return summ;
        },
        setLeftPoints: function() {
        	var obj = this,
        		summ = obj.getPointsSumm();
        	obj._leftpoints = obj._points - summ;
        	obj._leftpointsblock.html(obj._leftpoints);
        	obj._hiddenpoints.val(obj._leftpoints);
        },
    };
});

