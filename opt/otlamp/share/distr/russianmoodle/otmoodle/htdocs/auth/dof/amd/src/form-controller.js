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
 * From controller.
 *
 * @module     auth_dof/form-controller
 * @class      form-controller
 * @package    auth_dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.5
 */
define(['jquery'], function($) {

    return {
        init: function() {
            var self = this;

            self.form = $('.signup_form.mform');
            self.fields = self.form.find('.fitem .felement');
            self.errorfields = self.form.find('.fitem .felement input.is-invalid');
            self.labels = self.form.find('.fitem .col-form-label');

            self.displayLabelsInActualState();
            self.displayFirstError(this.form);

            self.fields.on('focus', 'input[type="text"],input[type="password"]', function(){
                var fitem = $(this).parents('.fitem');
                fitem.find('.col-form-label').css('visibility', 'visible');
                // у паролей passwordunmask под полем есть подсказка.. на время её отображения - прячем лейбл след. эл-та
                if (fitem.hasClass('passwordunmask')) {
                    fitem.next().find('.col-form-label').css('visibility', 'hidden');
                }
            });
            self.fields.on('blur', 'input[type="text"],input[type="password"]', function(){
                self.displayLabelsInActualState();
            });

            self.errorfields.on('blur', function(){ self.displayFirstNotEmptyWithError.call(self); });
        },
        hideAllErrors: function(){
            this.form.find('.fitem .invalid-feedback').hide();
        },
        displayFirstError: function(parentElement){
            this.hideAllErrors();

            parentElement.find('.invalid-feedback:not(#id_error_policyagreed)').filter(function(){
                return $(this).html().trim() !== '';
            }).first().attr('style', 'display:block!important');
        },
        displayFirstNotEmptyWithError: function(){
            this.hideAllErrors();

            var firstNotEmpty = this.errorfields.filter(function(){
                return $(this).val() == '';
            }).first().parents('.fitem');

            this.displayFirstError(firstNotEmpty);
        },
        displayLabelsInActualState: function(){
            this.labels.each(function(){
                var visibility = 'visible';
                var fitem = $(this).parents('.fitem');
                if (!fitem.hasClass('passwordunmask')) {
                    // у паролей passwordunmask вместо плейсхолдера подсказка с инструкцией
                    // поэтому у них лейбл с названием поля не скрывается никогда
                    visibility = (fitem.find('input').val() === '' ? 'hidden' : 'visible');
                }
                $(this).css('visibility', visibility);
            });
        }
    };
});
