document.addEventListener("DOMContentLoaded", function() {
	
	var activeelements = document.querySelectorAll('.dof_formbuilder li.active label'); 
	for ( var i = 0; i < activeelements.length; i++ ) {
		activeelements[i].click();
	}
		
	var elements = document.querySelectorAll('.dof_formbuilder .nav-tabs');
	for ( var i = 0; i < elements.length; i++ ) {
	    elements[i].addEventListener('click', function(e) {
	    	
	    	
	    	var elements = e.target.closest('.dof_formbuilder').querySelectorAll('.dof_formbuilder li.active');
	    	for ( var i = 0; i < elements.length; i++ ) {
	    	    elements[i].className = "";
	    	}
	    	if ( e.target.tagName.toUpperCase() == 'A' ) {
	    		e.target.parentNode.className = "active"
	    	} else {
	    		e.target.parentNode.parentNode.className = "active"
	    	}
	    });
	}
}, false);