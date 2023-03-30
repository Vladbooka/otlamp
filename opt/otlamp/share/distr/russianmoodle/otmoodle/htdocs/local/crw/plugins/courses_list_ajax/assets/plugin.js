function crw_clajax_base(e)
{
	var ajaxwrap = document.getElementById('clajax_ajaxcontent_wrapper');
	var ajax = document.getElementById('clajax_ajaxcontent');
	
	if ( ajaxwrap != null )
	{
		function getContent(id) 
		{
			Y.io('/local/crw/plugins/courses_list_ajax/ajax.php?id=' + id, {
				on: {
		            success: function (x, o) {
		            	ajaxwrap.style.display = 'block';
		                Y.one('#clajax_ajaxcontent').setHTML(o.responseText);
		                var ajaxclose = document.getElementById('crw_cajax_coursetitle_close');
		                ajaxclose.onclick = function()
		        		{
		        			ajaxwrap.style.display = 'none';
		        		}
		            }
		        } 
			});
	    }
		
		ajaxwrap.onclick = function()
		{
			ajaxwrap.style.display = 'none';
		}
		ajax.onclick = function(e)
		{
			e.stopPropagation();
		}
		
		var coursesrow = document.getElementsByClassName('clajax_courselink');
		for( var i=0; i < coursesrow.length; i++ )
		{
			coursesrow[i].onclick = function()
			{
				var id = this.getAttribute("data-courseid");
				getContent(id);
				return false;
			}
	    }
	}
}
document.addEventListener("DOMContentLoaded", crw_clajax_base);