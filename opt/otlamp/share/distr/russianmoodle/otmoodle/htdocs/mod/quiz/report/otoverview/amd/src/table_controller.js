// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Initialise the an add question modal on the quiz page.
 *
 * @module    quiz_otoverview/table_controller
 * @package   quiz_otoverview
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(
    ['jquery','core/ajax', 'core/yui', 'core/fragment', 'core/templates', 'core/notification'],
    function($, ajax, Y, fragment, templates, N){
        return {
            init: function() {
                $(document).ready(function () {
                    var openedWindows = [];
                    window._open = window.open;
                    window.open = function(url,name,params){
                        openedWindows.push(window._open(url,name,params));
                    };

                    var quiz = $("#attemptsform");
                    var quizid = 0;
                    if (quiz.length > 0) {
                        quizid = quiz.find('input[name=id]').val();
                    }
                    var attemptsTable = $('#attempts');
                    var attemptsIds = [];
                    if (attemptsTable.length > 0){
                        attemptsTable.find('tbody tr').each(function() {
                            var val = $(this).find('td input[type=checkbox]').val();
                            if (val !== undefined) {
                                attemptsIds.push(val);
                            }
                        });
                    }

                    var refreshAttemptsData = function() {
                        var requests = ajax.call([{
                            methodname : 'quiz_otoverview_get_attempts_data',
                            args: { quizid: quizid, questionattemptsids: attemptsIds}
                        }]);
                        requests[0]
                            .done(function(res){
                                res = $.parseJSON(res);
                                attemptsTable.find('tbody tr').each(function() {
                                    if ($(this).hasClass('otoverview-averagegroups')){
                                        var attemptid = 'averagegroups';
                                    } else if($(this).hasClass('otoverview-averageusers')){
                                        var attemptid = 'averageusers';
                                    } else{
                                        var attemptid = $(this).find('td input[type=checkbox]').val();
                                    }
                                    if (attemptid === undefined ||
                                            res[attemptid] === undefined) {
                                        return;
                                    }
                                    var index = 0;
                                    $.each(this.cells, function(){
                                        if (index !== 0){
                                            $(this).html(res[attemptid][index]);
                                        }
                                        index++;
                                    });
                                    if ($(this).find('.teacher-action-required').length > 0){
                                        if (!$(this).hasClass('teacher-action-required')){
                                            $(this).addClass('teacher-action-required');
                                        }
                                    }else{
                                        $(this).removeClass('teacher-action-required');
                                    }
                                });
                                $('#attempts').find('a.otoverview_action_popup').each(function() {
                                    $(this).click(function(e) {
                                        window.openpopup(e, {
                                            "url":$(this).attr('href'),
                                            "name":$(this).attr('act'),
                                            "options":"height=800,width=600,top=0,left=0,menubar=0,location=0,scrollbars,"+
                                                      "resizable,toolbar,status,directories=0,fullscreen=0,dependent"
                                        });
                                        var windowObjectsToDelete = [];
                                        $.each(openedWindows, function(key, value) {
                                            $(value.document).ready(function() {
                                                value.opener = null;
                                                var checkFunction = function() {
                                                    if ($(this.document).find('.alert-success').length > 0) {
                                                        this.close();
                                                    }
                                                    if (this.closed){
                                                        clearInterval(timer);
                                                    }
                                                };
                                                var timer = setInterval(checkFunction.bind(value), 2000);
                                            });
                                        });
                                        $.each(windowObjectsToDelete, function(key, value) {
                                            openedWindows.splice(value);
                                        });
                                    });
                                });
                            }).fail(N.exception)
                            .always(function() {
                                setTimeout(function() {
                                    refreshAttemptsData();
                                }, 10000);
                            });
                    };
                    if (quiz.length > 0 && attemptsTable.length > 0 && attemptsIds.length > 0) {
                        refreshAttemptsData();
                    }

                    var container = $('.otoverview-charts');
                    var refreshCharts = function(params) {
                        fragment.loadFragment('quiz_otoverview', 'get_charts', 1, params)
                            .done(function(html, js) {
                                if(html.length > 0){
                                    container.css('min-height', container.outerHeight(true) + 'px');
                                    templates.replaceNodeContents(container, html, js);
                                }
                            })
                            .fail(N.exception)
                            .always(function(){
                                setTimeout(function(){
                                    refreshCharts(params);
                                }, 10000);
                            });
                    };

                    if (quizid > 0 && attemptsIds.length > 0) {
                        refreshCharts({quizid: quizid, questionattemptsids: JSON.stringify(attemptsIds)});
                    }
                });
            }
        };
    }
);
