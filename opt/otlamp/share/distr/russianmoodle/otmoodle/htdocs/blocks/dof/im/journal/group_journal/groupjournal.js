require(['jquery'], function($) {
	var commentblocks = document.querySelectorAll('.dof_cpassedgradeform .dof_modal_wrapper');
	
	if ( commentblocks ) {
		for (var i = 0; i < commentblocks.length; i++) {
			
			var label = commentblocks[i].querySelector('.grpjournal_comment_modal_label');
			var textareas = commentblocks[i].getElementsByTagName('textarea');
			
			if ( textareas ) {
				for (var t = 0; t < textareas.length; t++) {
					var textarea = textareas[t];
					textareas[t].addEventListener("blur", function( event ) {
						event = event || window.event;
						
						var currenttextareas = event.target.parentNode.getElementsByTagName('textarea');
						var has_comment = false;
						for (var temp = 0; temp < currenttextareas.length; temp++) {
							if ( currenttextareas[temp].value != "" ) {
								has_comment = true;
							}
						}
						
						var clabel = event.target.closest('.dof_modal_wrapper').querySelector('.grpjournal_comment_modal_label');
						if ( has_comment ) {
							clabel.classList.add('grpjournal_has_comments');
						} else {
							clabel.classList.remove('grpjournal_has_comments');
						}
					} );
				}
				
			}
		}
	};

	// Отображение модального окна с причиной отсутствия
	$('div.show_modal_caller').hover(function(){
	    $('.group_journal_modal_wrapper_reason').remove();
		// Элемент с data-атрибутами
		data_elem = $(this).find('span');
		// Название причины
		reason_name = data_elem.data('name');
		// Тип причины
		reason_type = data_elem.data('type');
		$('<div>')
		.css({
			'left':  $(this).offset().left + 'px',
			'top': $(this).offset().top + 'px'
		}).hover(function() {
				 if($(this).data('removetimeout')!==undefined){
					 clearTimeout($(this).data('removetimeout'));
				 }
		     }, function() {
				 var _this = $(this) 
				 removetimeout = setTimeout(function(){
					 _this.remove();
				 }, 100);
				 $(this).data('removetimeout', removetimeout);
		     })
			.addClass('group_journal_modal_wrapper_reason')
			.html(reason_type + '<br />' + reason_name)
			.appendTo('body');
		
	 }, function(){
		 $('.group_journal_modal_wrapper_reason').each(function(){
			 var _this = $(this) 
			 removetimeout = setTimeout(function(){
				 _this.remove();
			 }, 100);
			 $(this).data('removetimeout', removetimeout);
	 	 });
	 });
	
	// Отображение модального окна с ссылкой на занятие
    $('div.show_modal_url a:first-of-type').hover(function(){
        $('.group_journal_modal_wrapper_url').remove();
        // Элемент с data-атрибутами
        data_elem = $(this).parent('.show_modal_url');
        if ( data_elem.length ===0 ) {
            data_elem = $(this).parent().parent('.show_modal_url');
        }
        // Ссылка
        var url = data_elem.data('url');
        console.log(url);
        $('<div>')
        .css({
            'left':  $(this).offset().left + 'px',
            'top': $(this).offset().top + 'px'
        }).hover(function() {
                 if($(this).data('removetimeout')!==undefined){
                     clearTimeout($(this).data('removetimeout'));
                 }
             }, function() {
                 var _this = $(this) 
                 removetimeout = setTimeout(function(){
                     _this.remove();
                 }, 100);
                 $(this).data('removetimeout', removetimeout);
             })
            .addClass('group_journal_modal_wrapper_url')
            .html('<a style="color:#000;" href="' + url + '">' + url + '</a>')
            .appendTo('body');
        
     }, function(){
         $('.group_journal_modal_wrapper_url').each(function(){
             var _this = $(this) 
             removetimeout = setTimeout(function(){
                 _this.remove();
             }, 100);
             $(this).data('removetimeout', removetimeout);
         });
     });
});
