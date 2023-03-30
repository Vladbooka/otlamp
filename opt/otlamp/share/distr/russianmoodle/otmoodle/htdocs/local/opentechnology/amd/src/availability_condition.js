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

import $ from 'jquery';
import templates from 'core/templates';
import notification from 'core/notification';
import ajax from 'core/ajax';

export const init = (elementId, contextid) => {

    const setting = $('#'+elementId);
    const settingHolder = setting.parent();
    const settingForm = $('<div>').addClass('availability_condition_form').appendTo(settingHolder);
    var form = null;

    setting.hide();
    settingForm.html('<div class="spinner-border text-info" role="status"><span class="sr-only">Loading...</span></div>');

    const settingToForm = async () => {
        var logicalgroups = await getLogicalGroups().catch(notification.exception);
        logicalgroups = JSON.parse(JSON.stringify(logicalgroups));
        var comparisonoperators = await getComparisonOperators().catch(notification.exception);
        comparisonoperators = JSON.parse(JSON.stringify(comparisonoperators));

        var templateName = 'local_opentechnology/ac_root';
        var templateData = {
            'logicalgroups': Object.values(logicalgroups),
            'comparisonoperators': Object.values(comparisonoperators),
        };
        var response = await templates.render(templateName, templateData).catch(notification.exception);

        var config = JSON.parse(setting.val());
        form = $(response);
        configToForm(config, form.children('.content')).finally(() => {
            settingForm.html(form);
            addHandlers();
        });
    };

    const formToSetting = function() {
        var config = formToConfig();
        var jsonedConfig = JSON.stringify(config);
        setting.val(jsonedConfig);
    };

    const addHandlers = () => {
        var rootOrGroup = '.ac-item[data-type="logicalgroup"] > legend button.add-item, .ac-root > legend button.add-item';
        $(rootOrGroup).off('click').on('click', function(){
            var content = $(this).closest('.ac-item[data-type="logicalgroup"], .ac-root').children('.content');
            var itemcode = $(this).data('code');
            switch ($(this).data('type')) {
                case 'logicalgroup':
                    getLogicalGroups().then(logicalgroups => {
                        logicalgroups = JSON.parse(JSON.stringify(logicalgroups));
                        if (logicalgroups.hasOwnProperty(itemcode)) {
                            createLogicalGroupElement(logicalgroups[itemcode]).then(group => {
                                group.appendTo(content);
                                formToSetting();
                                addHandlers();
                            });
                        }
                    });
                    break;
                case 'comparisonoperator':
                    getComparisonOperators().then(comparisonoperators => {
                        comparisonoperators = JSON.parse(JSON.stringify(comparisonoperators));
                        if (comparisonoperators.hasOwnProperty(itemcode)) {
                            createComparisonOperatorElement(comparisonoperators[itemcode]).then(operator => {
                                operator.appendTo(content);
                                formToSetting();
                                addHandlers();
                            });
                        }
                    });
                    break;
            }
        });

        $('.ac-item[data-type="comparisonoperator"] > .content button.change-arg').off('click').on('click', function(){
            var newcode = $(this).data('code');
            var inputgroup = $(this).parents('.input-group');
            getReplacements().then(replacements => {
                replacements = JSON.parse(JSON.stringify(replacements));
                if (!replacements.hasOwnProperty(newcode)) {
                    throw 'unknown replacement type';
                }
                var replacement = replacements[newcode];

                var typebtn = inputgroup.find('.replacement-type');
                typebtn.data('code', replacement.code);
                if (replacement.hasOwnProperty('icon')) {
                    typebtn.html('<i class="icon fa fa-'+replacement.icon+' fa-fw"></i>');
                } else {
                    typebtn.html(replacement.displayname);
                }

                inputgroup.find('.operator-arg').hide().filter('[data-replacement-code="'+newcode+'"]').show();

                formToSetting();
            });
        });

        $('.ac-item > legend button.remove-item').off('click').on('click', function(){
            $(this).closest('.ac-item').remove();
            formToSetting();
            if ($('.ac-root > .content').html() == '') {
                $('.ac-root > legend').show();
            }
        });

        var args = $('.ac-item[data-type="comparisonoperator"] > .content .operator-arg');
        args.off('change paste keyup').on('change paste keyup', function(){
            formToSetting();
        });

        if ($('.ac-root > .content').html() != '') {
            $('.ac-root > legend').hide();
        }
    };

    const configToForm = async (config, parent) => {
        var logicalgroups = await getLogicalGroups().catch(notification.exception);
        logicalgroups = JSON.parse(JSON.stringify(logicalgroups));
        var comparisonoperators = await getComparisonOperators().catch(notification.exception);
        comparisonoperators = JSON.parse(JSON.stringify(comparisonoperators));

        return new Promise(resolve => {
            parent.html('');

            var configKeys = Object.keys(config);

            if (typeof config !== 'object' || configKeys.length != 1) {
                throw 'Every level of config should be an associative array (object) with one item only';
            }

            var code = configKeys[0];
            var data = config[code];
            code = code.toLowerCase();

            if (!Array.isArray(data) || data.length == 0) {
                throw 'Config-level value must be presented as not empty sequenced array';
            }

            if (logicalgroups.hasOwnProperty(code)) {
                createLogicalGroupElement(logicalgroups[code]).then(group => {
                    var groupcontent = group.children('.content').first();
                    var promises = [];
                    data.forEach(subconfig => {
                        promises.push(configToForm(subconfig, groupcontent));
                    });

                    Promise.all(promises).then(() => {
                        group.appendTo(parent);
                        resolve(true);
                    });
                });
            } else if (comparisonoperators.hasOwnProperty(code)) {
                createComparisonOperatorElement(comparisonoperators[code], data).then(operator => {
                    operator.appendTo(parent);
                    resolve(true);
                });
            } else {
                resolve(false);
            }


        });
    };

    const createLogicalGroupElement = async (logicalGroup) => {
        var logicalgroups = await getLogicalGroups().catch(notification.exception);
        logicalgroups = JSON.parse(JSON.stringify(logicalgroups));
        var comparisonoperators = await getComparisonOperators().catch(notification.exception);
        comparisonoperators = JSON.parse(JSON.stringify(comparisonoperators));
        var templateName = 'local_opentechnology/ac_logical_group';
        var templateData = {
            'logicalgroup': logicalGroup,
            'logicalgroups': Object.values(logicalgroups),
            'comparisonoperators': Object.values(comparisonoperators),
        };
        var response = await templates.render(templateName, templateData).catch(notification.exception);
        var element = $(response);
        return element;
    };

    const createComparisonOperatorElement = async (comparisonOperator, data) => {

        if (typeof data === 'undefined') {
            data = [];
        }

        comparisonOperator.arguments = [];
        for(var i = 0; i < comparisonOperator.argsnum; i++) {
            var arg = {
                'index': i,
                'value': (i in data ? data[i] : null),
            };

            var replacements = await getReplacements().catch(notification.exception);
            replacements = JSON.parse(JSON.stringify(replacements));

            const regex = /{([^\.^}]+)\.([^}]+)}/;
            let m = regex.exec(arg.value);
            var currentreplacement;
            if (m !== null && replacements.hasOwnProperty(m[1])) {
                currentreplacement = replacements[m[1]];
                if (currentreplacement.properties.hasOwnProperty(m[2])) {
                    currentreplacement.properties[m[2]].selected = true;
                }
            } else {
                currentreplacement = replacements.string;
            }
            currentreplacement.current = true;

            for (const [r, replacement] of Object.entries(replacements)) {
                replacements[r].propertiescount = Object.keys(replacement.properties).length;
                replacements[r].properties = Object.values(replacements[r].properties);
            }

            arg.replacements = Object.values(replacements);
            arg.currentreplacement = currentreplacement;
            comparisonOperator.arguments.push(arg);
        }

        var templateName = 'local_opentechnology/ac_comparison_operator';
        var templateData = {'comparisonoperator': comparisonOperator};
        var response = await templates.render(templateName, templateData).catch(notification.exception);
        var element = $(response);
        return element;
    };

    const formToConfig = function(item) {

        if (typeof item === 'undefined') {
            item = form.children('.content').children('.ac-item');
        }

        var config = {};

        if (item.length == 1) {
            config[item.data('code')] = [];
            switch(item.data('type')) {
                case 'logicalgroup':
                    item.children('.content').children('.ac-item').each(function(){
                        var subconfig = formToConfig($(this));
                        config[item.data('code')].push(subconfig);
                    });
                    break;
                case 'comparisonoperator':
                    item.children('.content').find('.input-group').sort(function (a, b) {
                        return $(a).data('index') - $(b).data('index');
                    }).each(function(){
                        var typebtn = $(this).find('.replacement-type');
                        var replacementcode = typebtn.data('code');
                        var prop = $(this).find('.operator-arg[data-replacement-code="'+replacementcode+'"]');
                        if (replacementcode == 'string') {
                            config[item.data('code')].push(prop.val());
                        } else {
                            config[item.data('code')].push('{'+replacementcode+'.'+prop.val()+'}');
                        }

                    });
                    break;
            }
        }

        return config;
    };

    const getLogicalGroups = () => {
        if (typeof getLogicalGroups.result == 'undefined') {
            getLogicalGroups.result = new Promise((resolve) => {
                var ajaxPromises = ajax.call([{
                    methodname: 'local_opentechnology_get_ac_logical_groups',
                    args: [],
                }]);
                ajaxPromises[0].done(jsonResponse => {
                    resolve(JSON.parse(jsonResponse));
                });
            });
        }
        return getLogicalGroups.result;
    };

    const getComparisonOperators = () => {
        if (typeof getComparisonOperators.result == 'undefined') {
            getComparisonOperators.result = new Promise((resolve) => {
                var ajaxPromises = ajax.call([{
                    methodname: 'local_opentechnology_get_ac_comparison_operators',
                    args: [],
                }]);
                ajaxPromises[0].done(jsonResponse => {
                    resolve(JSON.parse(jsonResponse));
                });
            });
        }
        return getComparisonOperators.result;
    };

    const getReplacements = () => {
        if (typeof getReplacements.result == 'undefined') {
            getReplacements.result = new Promise((resolve) => {
                var ajaxPromises = ajax.call([{
                    methodname : 'local_opentechnology_get_ac_replacements',
                    args: {'contextid': contextid}
                }]);
                ajaxPromises[0].done(jsonResponse => {
                    resolve(JSON.parse(jsonResponse));
                });
            });
        }
        return getReplacements.result;
    };

    settingToForm();
};