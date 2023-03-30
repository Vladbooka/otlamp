require(['jquery'], function($) 
{
	$(function(){
		$('.block_otshare .otshare_wrapper').each(function(){
			var sharelink = $(this).children('.block_otshare .otshare_link');
			var sharebutton = $(this).children('.block_otshare .otshare_button');

			var serviceshortname = $(this).data('serviceshortname');
			var url = $(this).data('shareurl');
			var type = $(this).data('buttontype');

			switch(serviceshortname)
			{
				case 'ok':
					OK.CONNECT.insertShareWidget(sharebutton.first().attr('id'), url, type);
					break;
				case 'vk':
					sharebutton.html(VK.Share.button({'url': url}, {'type': type}));
					break;
				default: break;
			}
			
			sharelink.remove();
			sharebutton.show();
		});
	})
});