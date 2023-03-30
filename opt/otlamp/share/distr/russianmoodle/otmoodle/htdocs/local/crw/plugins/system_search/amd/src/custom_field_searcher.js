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
 * Custom field sercher module.
 *
 * @package    crw_system_search
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/templates', 'core/str'], function($, Ajax, Templates, Str) {

    /** @var {Number} Maximum number of users to show. */
    var MAXITEMS = 10;

    return /** @alias module:enrol_manual/form-user-selector */ {

        processResults: function(selector, results) {
            var items = [];
            if ($.isArray(results)) {
                $.each(results, function(index, item) {
                    items.push({
                        value: item.svalue,
                        label: item.svalue.length > 40 ? item.svalue.substring(0, 40)+'...' : item.svalue
                    });
                });
                return items;

            } else {
                return results;
            }
        },

        transport: function(selector, query, success, failure) {

            var promise = Ajax.call([{
                methodname: 'crw_system_search_find_by_custom_field',
                args: {
                    search: query,
                    field: $(selector).attr('name'),
                    page: 0,
                    perpage: MAXITEMS + 1
                }
            }]);

            promise[0].then(function(results) {
                var promises = [];

                if (results.length <= MAXITEMS) {
                    // Render the label.
                    $.each(results, function(index, item) {
                        promises.push(Templates.render('crw_system_search/form-custom-field-searcher-suggestion', item));
                    });

                    // Apply the label to the results.
                    return $.when.apply($.when, promises).then(function() {
                        success(results);
                        return;
                    });

                } else {
                    return Str.get_string('toomanyresults', 'crw_system_search', '>' + MAXITEMS).then(function(toomanyresults) {
                        success(toomanyresults);
                        return;
                    });
                }

            }).fail(failure);
        }
    };
});
