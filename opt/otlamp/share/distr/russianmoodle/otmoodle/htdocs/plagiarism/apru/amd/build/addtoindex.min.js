define(['jquery', 'core/ajax', 'core/str'], function($, ajax, strman) {
	return {
		addToIndex: function(id, status) {
			var method = status ? 'add_to_index' : 'remove_from_index';
			var response = ajax.call([{
		        methodname : 'plagiarism_apru_'+method,
		        args: { 
		        	'id': id
	        	}
		    }]);
			response[0]
				.done(function(res) {
					var getstring;
					var answer = JSON.parse(res);
					if( answer.result )
					{
						var el = $('div.change_index_document_status[data-docid="'+id+'"]');
						
						if( status )
						{
							getstring = strman.get_string('remove_from_index', 'plagiarism_apru');
							getstring.done(function(title) {
								el.text(title);
							});
							
							el.removeClass('notindexed');
							el.addClass('indexed');
						} else
						{
							getstring = strman.get_string('add_to_index', 'plagiarism_apru');
							getstring.done(function(title) {
								el.text(title);
							});
							
							el.removeClass('indexed');
							el.addClass('notindexed');
						}
						
						if( ! answer.capability )
						{
							el.addClass('disable');
						}
					}
				})
		        .fail(function(ex) {
		        	// Если получили ошибку при обработке аякс запроса - выведем стек в консоль
		        	console.log(ex.message);
		        });
		},
		init: function() {
			var obj = this;
			$(window).on('load', function() {
				$('div#change_index_document_status').click(function() {
					obj.addToIndex($(this).data('docid'), ! $(this).hasClass('indexed'));
				});
			});
		}
	};
});