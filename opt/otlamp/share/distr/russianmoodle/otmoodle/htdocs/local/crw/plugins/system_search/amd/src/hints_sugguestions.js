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

define(['jquery'], function($) {

    return {
        init: function(){
            // отправка формы поиска со страницы просмотра всех результатов
            $('#local_crw .crw-search-results.form-autocomplete-suggestions > li').click(function(){
                var query = $(this).children('div').data('query');
                var option = $('<option>').prop('value', query).text(query);
                $('form.crw_system_search_form select[name="topblock[crws]"]')
                    .html('')
                    .append(option)
                    .val(query)
                    .change();
            });

            // автоотправка формы при выборе элемента
            $('form.crw_system_search_form select[name="topblock[crws]"]').on('change', function(event){
                event.stopPropagation();
                var form = $(this).closest('form');
                if (form.data('submit-state') != 'submit')
                {
                    $(this).nextAll('input[type=text]').val('');
                    form.data('submit-state', 'submit').find('input[type=submit]').first().click();
                }
            });

            $('form.crw_system_search_form select[name="topblock[crws]"] + .form-autocomplete-selection + input[type=text]')
                .focus(function(){
                    if($(this).val() !== '')
                    {
                        $(this).trigger('input');
                    }
                })
                .off('blur')
                .on('blur', function(){
                    var textinput = $(this);
                    window.setTimeout(function() { textinput.nextAll('.form-autocomplete-suggestions').hide(); }, 200);
                });
        }
    };
});