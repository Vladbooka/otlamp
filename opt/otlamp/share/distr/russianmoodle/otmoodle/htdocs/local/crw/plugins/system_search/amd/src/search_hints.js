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
 * Формирование подсказок с результатами поиска для автокоплита
 *
 * @package    local_crw
 * @subpackage crw_system_search
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/templates'], function($, ajax, templates) {

    return {

        processResults: function(selector, results) {
            var hints = [];
            $.each(results, function(index, hint) {
                hints.push({
                    value: hint.hintvalue,
                    label: hint._label
                });
            });
            return hints;
        },

        transport: function(selector, query, success, failure) {

            var promise = ajax.call([{
                methodname: 'crw_system_search_show_hints',
                args: {
                    'query': query
                }
            }]);

            promise[0].then(function(response) {
                var results = $.parseJSON(response);

                var promises = [],
                    i = 0;

                // Render the label.
                $.each(results.hints, function(index, hint) {
                    promises.push(templates.render('crw_system_search/search_hints', hint));
                });

                // Apply the label to the results.
                return $.when.apply($.when, promises).then(function() {
                    var args = arguments;
                    $.each(results.hints, function(index, hint) {
                        hint._label = args[i];
                        i++;
                    });

                    success(results.hints);
                });

            }, failure);
        }
    };
});
