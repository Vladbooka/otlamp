window.onload = function(e)
{ 
	var modal = document.getElementById('linksocial_modal');
	var bg = document.getElementById('linksocial_wrapper_bg');
	var close = document.getElementById('linksocial_message_close');
	
	function closemodal()
	{
		modal.style.display = 'none';
	}
	
	if (close != undefined) {
		close.addEventListener( "click" , closemodal, false);
	}
	if (bg != undefined) {
		bg.addEventListener( "click" , closemodal, false);
	}
}