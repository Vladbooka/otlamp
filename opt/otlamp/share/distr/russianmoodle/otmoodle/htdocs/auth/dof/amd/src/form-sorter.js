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
 * From sorter.
 *
 * @module     auth_dof/form-sorter
 * @class      form-sorter
 * @package    auth_dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery'], function($) {

    return {
        init: function(holder, items, handle) {
            $(holder).sortable({
                items: items,
                handle: handle,
                cursor: 'move',
                helper: function(event, element){
                    return $('<div>').css({
                        'width': element.width()+'px',
                        'height': element.height()/2+'px',
                        'background': '#EEE'
                    });
                },
                opacity: 0.75,
                axis: "y",
                cursorAt: { left: 5 },
                start: function(event, ui) {
                    ui.placeholder.css({
                        'height': ui.helper.height()/2+'px'
                    });
                },
                stop: function(){
                    var result = $(holder).sortable("toArray", {
                        'attribute': 'data-groupname'
                    });
                    var i = 0;
                    result.forEach(function(item) {
                        $(holder).children('div[data-groupname="'+ item + '"]')
                                .find('.order_field').attr('value', i);
                        i++;
                    });
                    $('.heder_line .form_has_chenges').show();
                }
            });
        }
    };
});
