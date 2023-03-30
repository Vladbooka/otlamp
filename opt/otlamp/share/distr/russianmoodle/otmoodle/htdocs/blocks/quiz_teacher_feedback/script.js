require(['jquery','core/ajax', 'core/fragment', 'core/templates', 'core/notification'], function($, ajax, fragment, templates, notification) {
	
	$(function(){
		
		var destination = $("body.path-mod-quiz form#responseform div.submitbtns");
		if (destination.length > 0) {
			$('.block_quiz_teacher_feedback__switch_request_status_wrapper.block_quiz_teacher_feedback__switch_request_status_wrapper_replace_checkbox').each(function (index, element) {
				$(this).appendTo(destination[0])
			})
		}
		$('input.block_quiz_teacher_feedback__switch_request_status').click(function(e){
			var requests = ajax.call([{
				methodname : 'block_quiz_teacher_feedback_switch_request_status',
				args: {
					qubaid: $(this).data('qubaid'),
					slot: $(this).data('slot'),
					token: $(this).data('token'),
					instance: $(this).data('instance'),
					status: $(this).prop('checked') 
					}
			}]);
		})
		
		var initQTF = function(block){
			clearTimeout(block.data('feedbacktimer'));
			
			var modal = $('#quiz_teacher_feedback_modal');
			if ( modal )
			{
				$('body').append(modal[0].outerHTML);
				modal.remove();
				new_modal = $('body #quiz_teacher_feedback_modal');
				$('body #quiz_teacher_feedback_modal_wrapper_bg').unbind('click').bind('click', function () { $('body #quiz_teacher_feedback_modal').hide(); });
				$('body #quiz_teacher_feedback_modal_message_close').unbind('click').bind('click', function () { $('body #quiz_teacher_feedback_modal').hide(); });
			}
	
			
			var instance = block.attr('id').substr(4);
			block.find('.qtc_feedback').each(function() {
				var qaid = this.getAttribute('data-qa');
				var qubaid = this.getAttribute('data-qubaid');
				var slot = this.getAttribute('data-slot');
				var token = this.getAttribute('data-token');
				block.data('feedbacktimer', setInterval(function() { getfeedback(qaid, qubaid, slot, token, instance) }, 5000));
			});
	
			block.find('.qtf_filter_group').first().unbind('change').change(function(){
				var form = $(this).closest('form');
				form.css('pointer-events','none');

				var data = form.serializeArray().reduce(function(obj, item) {
				    obj[item.name] = item.value;
				    return obj;
				}, {});
				
				var params = {
		        	'formdata': JSON.stringify(data),
		        	'quizid': form.find('.quiz_block_teacher_feedback_header').data('quizid'),
		        	'instanceid': instance
	        	};
				

				var container = form.parent();
				
				fragment.loadFragment('block_quiz_teacher_feedback', 'filtered_group', block.data('contextid'), params)
					.done(function(html, js) {
						templates.replaceNodeContents(container, html, js);
		    			initQTF(block);
					})
					.fail(function(ex){
						console.log(ex);
					})
			})
			$('.block_quiz_teacher_feedback .qtf_filter_group').closest('form').unbind('submit').submit(function(event){
				event.preventDefault();
			})
			
			if (block.data('questionProcessing') == false)
			{
				get_questions(block);
			}
		}
		
		
	
		var getfeedback = function(qaid, qubaid, slot, token, instance) {
			$('#qtc_' + qaid).addClass('loading');
			var requests = ajax.call([{
		        methodname : 'block_quiz_teacher_feedback_get_feedback',
		        args: { qubaid: qubaid, slot: slot, token: token, instance: instance}
		    }]);
	
		    requests[0]
		    	.done(function(response){
		    		var data = JSON.parse(response);
			    	$('#qtc_' + qaid).removeClass('loading');
			    	$('#qtc_' + qaid + ' .feedback').html(data.feedback);
			    	var gradeblock = $('#qtc_' + qaid + ' .grade');
			    	if ( gradeblock ) {
			    		gradeblock.removeClass('set');
			    		$('#qtc_' + qaid + ' .grade .current').html(data.grade);
				    	if ( data && data.grade !== '' ) {
				    		gradeblock.addClass('set');
				    	}
			    	}
			    	var completeblock = $('#qtc_' + qaid + ' .complete');
			    	if ( completeblock ) {
			    		completeblock.text(data.completedstring);
			    	}
			    	
			    	var checkbox = $('input.block_quiz_teacher_feedback__switch_request_status');
			    	if ( checkbox.length > 0 ) {
			    		checkbox.prop('checked', !!data.checkboxstatus);
			    	}
			    	
			    	var modalmessage = $('#quiz_teacher_feedback_modal .quiz_teacher_feedback_modal_message .quiz_teacher_feedback_modal_message_content');
			    	var modalmessagecomplete = $('#quiz_teacher_feedback_modal .quiz_teacher_feedback_modal_message .quiz_teacher_feedback_modal_message_content_complete');
			    	
			    	if ( new_modal && ! new_modal.hasClass('showed') && data.completeall == 'complete' && ! new_modal.hasClass('modalajax') )
		    		{
		    			modalmessage.hide();
			    		modalmessagecomplete.show();
			    		new_modal.addClass('showed');
			    		new_modal.show();
		    		}
		    	})
		    	.fail(function(ex){
			    	$('#qtc_' + qaid).removeClass('loading');
		    	})
		};
		
		
		var get_questions = function(block) {
			block.data('questionProcessing', true);
			
			var info_data = { 
				quizid: null, 
				students_info: [], 
				instance: $('.block_quiz_teacher_feedback.block').attr('id').substr(4)
			};
			var checker = $('.block_quiz_teacher_feedback .quiz_block_teacher_feedback_header');
			if ( checker.length !== 0 ) {
				info_data.quizid = $(checker)[0].getAttribute('data-quizid');
				info_data.groupid = block.find('select.qtf_filter_group').first().val();
				$('.block_quiz_teacher_feedback.block .content fieldset.clearfix.collapsible').each(function() {
					// Сбор данных о студенте
					var student = { fullname: null, attempt_id: null, pages: [] };
					// Имя студента
					student.fullname = $(this).find('legend a').text();
					// Элемент с именем и id
					var elem = $(this).find('.fcontainer.clearfix .quiz_block_teacher_feedback_wrapper_header')[0];
					// Имя студента
					student.studentid = elem.getAttribute('data-studentid');
					// ID попытки
					student.attempt_id = elem.getAttribute('data-attemptid');
				   
					// Сбор об текущей информации на странице для сравнения на стороне сервера
					$(this).find('.quiz_teacher_feedback_elements_wrapper a').each(function() {
						page_info = {};
						// ID Страницы
						page_info.pageid = this.getAttribute('data-pageid');
						// Статус вопроса
						page_info.status = this.getAttribute('data-status');
						// Статус ответа студентом
						page_info.answered = this.getAttribute('data-answered');
						// Кладем данные в массив
						student.pages.push(page_info);
					});
				   
					// Кладем в массив данных
					info_data.students_info.push(student);
				});
				
				var requests = ajax.call([{
					methodname : 'block_quiz_teacher_feedback_get_questions_list',
					args: { data: JSON.stringify(info_data) }
				}]);
	
	
			    requests[0]
			    	.done(function(response){
			    		var data = JSON.parse(response);
						if ( data && data.length !== 0 ) {
							data.forEach(function(item) {
								wrapper = $('#id_header_' + item.studentid + '_' + item.attemptid);
								if (wrapper) {
									if (item.new_pages.length !== 0) {
										if ( item.new_attempt === 1 ) {
											// Формируем новую попытку и вставляем в html код клиента
											var form = $('.block_quiz_teacher_feedback.block .content form');
											if ($('a#' + item.attemptid + '_' + item.studentid).length === 0 &&
												info_data.groupid == block.find('select.qtf_filter_group').first().val()) 
											{
												
												form.append('<a id="' + item.attemptid + '_' + item.studentid + '" href="/mod/quiz/review.php?attempt=' + item.attemptid + '&showall=0"> Появилась новая попытка: ' + item.fullname + '</a>');
											}
										} else {
											item.new_pages.forEach(function (item) {
												elem = $(wrapper).find("a[data-pageid='" + item.pageid + "']");
												$(elem).attr('data-hint', item.hint);
												$(elem).attr('data-status', item.status);
												$(elem).attr('data-answered', item.answered);
												$(elem).removeAttr('class');
												$(elem).attr('class', 'quiz_teacher_feedback_button ' + item.class);
												$(elem).parent().find('.quiz_block_teacher_feedback_name_of_slot').html(item.name_of_slot);
											});
										}
									}
								}
							});
						}
			    	})
			    	.fail(function (err) {
			    		console.log(err)
			    	})
			    	.always(function(){
						setTimeout(function(){ 
							get_questions(block);
						}, 3000);
			    	})
			} else
			{
				block.data('questionProcessing', false);
			}
		}
		
		$('.block_quiz_teacher_feedback').each(function(){
			$(this).data('questionProcessing', false);
			initQTF($(this));
		})
		
	});

});