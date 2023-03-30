/**
 * Панель управления доступом в СДО
 * 
 * @package    local_authcontrol
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(['jquery'], function($) {

	$(document).ready(function() {

		var table = $(".local_authcontrol_content table");
		
		var killsessions = function(userid, token, courseid, element) {
			$.ajax({
				method: "POST",
				dataType: 'json',
				url: "/local/authcontrol/ajax/ajax_reset_sessions.php",
				data: { userid: userid, token: token, courseid: courseid}
		    })
		    .fail(function() {
		    	var previous = element.text();
		    	element.html("error");
	    		setTimeout(function() {
	    			element.html(previous);
	    		}, 2000);
		    })
		    .done(function( data ) {
		    	if ( data.complete && data.complete.length != 0 && element ) {
		    		element.html(data.complete);
		    		setTimeout(function() {
		    			element.html(data.html);
		    		}, 2000);
	    		}
		    });
		};
		
		var changepassword = function(userid, token, courseid, newpassword, element, text) {
			$.ajax({
				method: "POST",
				dataType: 'json',
				url: "/local/authcontrol/ajax/ajax_change_password.php",
				data: { userid: userid, token: token, courseid: courseid, newpassword: newpassword}
		    })
		    .fail(function() {
		    	element.html("error");
		    })
		    .done(function( data ) {
		    	if ( data.complete && data.complete.length != 0 && element ) {
		    		element.html(data.complete);
		    		setTimeout(function() {
		    			element.html(data.html);
		    		}, 3000);
	    		}
		    });
		};
		
		var closeaccess = function(userid, token, courseid, element) {
			$.ajax({
				method: "POST",
				dataType: 'json',
				url: "/local/authcontrol/ajax/ajax_close_access.php",
				data: { userid: userid, token: token, courseid: courseid}
		    })
		    .fail(function() {
		    	element.html("error");
		    	setTimeout(function() {
	    			element.html(text);
	    		}, 2000);
		    })
		    .done(function( data ) {
		    	if ( data.complete && data.complete.length != 0 && element ) {
		    		var parent = element.parent();
		    		element.html(data.complete);
		    		setTimeout(function() {
		    			element.html(data.html);
		    		}, 2000);
		    		if ( data.status && data.status.length != 0 ) {
		    			$(".local_authcontrol_content td.local_authcontrol_status[data-id=" + parent.attr("data-student") + "]").html(data.status);
		    		}
		    		$(".local_authcontrol_content td.local_authcontrol_context[data-id=" + parent.attr("data-student") + "]").html('');
		    		$(".local_authcontrol_content td.local_authcontrol_course[data-id=" + parent.attr("data-student") + "]").html('');
		    		$(".local_authcontrol_content td.local_authcontrol_module[data-id=" + parent.attr("data-student") + "]").html('');
	    		}
		    });
		};
		
		$(".local_authcontrol_content .local_authcontrol_popup").bind({
			mouseenter: function() {
				var value = $(this).attr("data-id");
				$("#student_id_" + value).addClass("local_authcontrol_show");
			},
			mouseleave: function() {
				var value = $(this).attr("data-id");
				$("#student_id_" + value).removeClass("local_authcontrol_show");
				$(this).find('.local_authcontrol_reset_form_hidden').hide();
				$(this).find('.local_authcontrol_change_password_click').show();
			}
		});
		
		$('.local_authcontrol_content .local_authcontrol_reset_sessions_click').bind({
			click: function() {
				var parent = $(this).parent();
				var token = parent.attr("data-token");
				var userid = parent.attr("data-student");
				var courseid = parent.attr("data-course");
				killsessions(userid, token, courseid, $(this));
			}
		});
		
		$('.local_authcontrol_content .local_authcontrol_change_password_click').bind({
			click: function() {
				var parent = $(this).parent();
				var studentid = parent.attr("data-student");
				$(this).hide();
				$("#reset_password_" + studentid).show();
			}
		});
		
		$('.local_authcontrol_content .local_authcontrol_button_password').bind({
			click: function() {
				var parent = $(this).closest("span");
				var token = parent.attr("data-token");
				var userid = parent.attr("data-student");
				var courseid = parent.attr("data-course");
				var newpassword = $("#new_password_" + userid).val();
				var element = $(".local_authcontrol_change_password_click[data-student=" + userid + "]");
				var text = element.text();
				$(this).parent().hide();
				element.html("Loading...").show();
				changepassword(userid, token, courseid, newpassword, element, text);
			}
		});
		
		$('.local_authcontrol_content .local_authcontrol_close_access').bind({
			click: function() {
				var parent = $(this).parent();
				var token = parent.attr("data-token");
				var userid = parent.attr("data-student");
				var courseid = parent.attr("data-course");
				closeaccess(userid, token, courseid, $(this));
			}
		});
	
		$(".local_authcontrol_content table th.local_authcontrol_search_filter").each(function() {
        	var text = $(this).text();
        	$(this).html('<input class="local_authcontrol_searching" type="text" /><br>' + text);
	    });
		
		$('.local_authcontrol_content .local_authcontrol_select_all a').bind({
			click: function() {
				$('.local_authcontrol_content :checkbox').each(function() {
					if ( $(this).is(':visible') ) {
						if ( $(this).prop('checked') ) {
							$(this).prop('checked', false);
						}
						else {
							$(this).prop('checked', true)
						}
					}
				})
			}
		});
		
		$(".local_authcontrol_content .local_authcontrol_searching").each(function () {
			$(this).bind({
				keyup: function() {
					var field = $(this).parent().attr("data-search");
					var value = $(this).val();
					table.find("td[data-field=" + field + "]").each(function(i, val) {
						if ( $(this).text().toLowerCase().indexOf(value.toLowerCase()) == -1 ) {
							$(this).parent().hide();
						}
						else {
							$(this).parent().show();
						};
					});
				}
			})
		});
		
	});
});
