var singleselects = document.querySelectorAll('select[data-dofsingleselect="true"]');
if ( singleselects ) {
	for (var i = 0; i < singleselects.length; i++) {
		
		var singleselect = singleselects[i];
		var fieldset = singleselect.closest('fieldset');
		var submit = fieldset.querySelector('input[type="submit"]');
		
		if ( submit ) {
			submit.style.display = "none";
			singleselect.addEventListener("change", function( event ) {
				var fieldset = event.target.closest('fieldset');
				var submit = fieldset.querySelector('input[type="submit"]');
				submit.click();
			});
		}
	}
}