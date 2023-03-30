function crw_catlb_base(e)
{ 
	var blocks = document.getElementsByClassName('block_catlb_categories_list');
	for( var i=0; i < blocks.length; i++ )
	{
		var dataregion = blocks[i].getAttribute('data-region');
		var dataweight = blocks[i].getAttribute('data-weight');
		
		var region = document.getElementById('block-region-' + dataregion );
		
		if ( region == null )
		{
			return;
		}
		var before = region.childNodes[dataweight];
		region.insertBefore(blocks[i], before);
    }
}
document.addEventListener("DOMContentLoaded", crw_catlb_base);