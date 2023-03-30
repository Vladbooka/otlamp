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
 * Jquery модуль для работы с вебсервисами электронного деканата
 *
 * @module     dof_str
 * @package    block_dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'block_dof/dof_ajax'], function($, dajax) {
	
    var promiseCache = [];

    return /** @alias module:block/dof_str */ {

        get_string: function(key, ptype, pcode, param) {
            var request = this.get_strings([{
                key: key,
                ptype: ptype,
                pcode: pcode,
                param: param
            }]);

            return request.then(function(results) {
                return results[0];
            });
        },
        get_strings: function(requests) {
        	
            var deferred = $.Deferred();
            var results = [];
            
        	var ajaxrequests = []
            var fetchpromises = [];

            // Done handler for ajax call. Must be bound to the current fetchpromise. We do this
            // to avoid creating a function in a loop.
            var doneFunc = function(str) {
                this.resolve(str);
            };

            var failFunc = function(reason) {
                this.reject(reason);
            };
        	
            for (i = 0; i < requests.length; i++) {
                request = requests[i];
                request.cacheKey = 'dof_str/' + request.key + '/' + request.ptype + '/' + request.pcode + '/ru';
                // If we ever fetched this string with a promise, reuse it.
                if (typeof promiseCache[request.cacheKey] !== 'undefined') {
                    fetchpromises.push(promiseCache[request.cacheKey]);
                } else {
                    // Add this to the list we need to really fetch.
                    var fetchpromise = $.Deferred();

	                ajaxrequests.push({
	                    methodname: 'dof_get_string',
	                    args: {
	                        stringid: request.key,
	                        ptype: request.ptype,
	                        pcode: request.pcode,
	                        param: request.param
	                    },
	                    done: doneFunc.bind(fetchpromise),
	                    fail: failFunc.bind(fetchpromise)
	                });

                    promiseCache[request.cacheKey] = fetchpromise.promise();
                    fetchpromises.push(promiseCache[request.cacheKey]);
                }
            }

            // Everything might already be queued so we need to check if we have real ajax requests to run.
            if (ajaxrequests.length > 0) {
            	dajax.call(ajaxrequests, true, false);
            }

            $.when.apply(null, fetchpromises).done(
                function() {
                    // Turn the list of arguments (unknown length) into a real array.
                    for (var i = 0; i < arguments.length; i++) {
                    	results[i] = arguments[i];
                    }
                    deferred.resolve(results);
                }
            ).fail(
                function(ex) {
                    deferred.reject(ex);
                }
            );
            
            return deferred.promise();
        }
    };
});
