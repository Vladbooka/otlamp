define(['jquery', 'core/ajax', 'core/notification', 'core/templates', 'core/str'],
    function($, ajax, notification, templates, str) {
        return {
           __config_structure: {},
           __configured_views: {},
           restoreDefaults: function(){
               var self = this;
               ajax.call([{
                   methodname : 'local_otcontrolpanel_restore_default_config',
                   args: [],
                   fail: notification.exception,
                   done: function() {
                       self.init();
                   },
               }]);
           },
           saveConfig: function(){
               var self = this;
               var yaml = self.getYaml();
               window.console.log(yaml);
               ajax.call([{
                   methodname : 'local_otcontrolpanel_save_config',
                   args: {yaml: yaml},
                   fail: notification.exception,
                   done: function() {
                       self.init();
                   },
               }]);
           },
           generateYamlFromConfig: function(obj, prop, spacescount){
               var self = this;
               var yaml = '';
               var spaces = new Array(spacescount + 1).join(' ');
               if (obj.hasOwnProperty(prop))
               {
                   var leftside = '';
                   if (/^\+?(0|[1-9]\d*)$/.test(prop)){
                       // нумернованный массив
                       leftside = spaces+'- ';
                   } else {
                       // именованный массив
                       leftside = spaces+prop+': ';
                   }

                   if (obj[prop] !== null && (Array.isArray(obj[prop]) || typeof obj[prop] === 'object'))
                   {
                       if (obj[prop].length == 0)
                       {// пустой массив - отобразим на этой же строке короткую запись
                           yaml += leftside+'{  }\r\n';
                       } else {
                           yaml += leftside+'\r\n';
                           $.each(obj[prop], function(propkey){
                               yaml += self.generateYamlFromConfig(obj[prop], propkey, spacescount+2);
                           });
                       }
                   } else {
                       yaml += leftside+obj[prop]+'\r\n';
                   }
               }
               return yaml;
           },
           /* Собрать строку yaml-конфига из конфига, хранящегося в __configured_views */
           getYaml: function(){
               var self = this;
               var yaml = '';
               $.each(self.__configured_views, function(v, view){
                   var config = view['view-config'];

                   if (!config.hasOwnProperty('entitycode'))
                   {
                       return;
                   }

                   var fieldsconfigyaml = '';
                   $.each(config.fields, function(fld, fieldconfig){
                       var fieldconfigyaml = '';
                       if (fieldconfig.hasOwnProperty('relationcode') &&
                           fieldconfig.hasOwnProperty('fields'))
                       {
                           // ПОЛЕ СО СВЯЗЬЮ И НАСТРОЕННЫМИ ДЛЯ НЕГО ВЛОЖЕННЫМИ ПОЛЯМИ
                           fieldconfigyaml += '    relationcode: '+fieldconfig.relationcode+'\r\n';
                           if (fieldconfig.fields.length == 0){
                               fieldconfigyaml += '    fields: {  }\r\n';
                           } else {
                               fieldconfigyaml += '    fields: \r\n';
                               $.each(fieldconfig.fields, function(fld, fielddata){
                                   fieldconfigyaml += '      - fieldcode: '+fielddata.fieldcode+'\r\n';

                                   fieldconfigyaml += self.generateYamlFromConfig(fielddata, 'modifiers', 8);
                                   fieldconfigyaml += self.generateYamlFromConfig(fielddata, 'template', 8);
                                   fieldconfigyaml += self.generateYamlFromConfig(fielddata, 'filterparams', 8);

                                   if (fielddata.hasOwnProperty('displayname') && fielddata.displayname)
                                   {
                                       fieldconfigyaml += '        displayname: '+fielddata.displayname+'\r\n';
                                   }
                               });
                           }
                       }
                       else if(fieldconfig.hasOwnProperty('fieldcode')) {
                           fieldconfigyaml += '    fieldcode: '+fieldconfig.fieldcode+'\r\n';
                       }

                       fieldconfigyaml += self.generateYamlFromConfig(fieldconfig, 'modifiers', 4);
                       fieldconfigyaml += self.generateYamlFromConfig(fieldconfig, 'template', 4);
                       fieldconfigyaml += self.generateYamlFromConfig(fieldconfig, 'filterparams', 4);

                       // раньше мы не добавляли поля связанных таблиц если в них не было полей
                       // теперь по умолчанию поля без полей показывают количество найденных записей
                       if (fieldconfig.hasOwnProperty('displayname') && fieldconfig.displayname)
                       {
                           fieldconfigyaml = '    displayname: '+fieldconfig.displayname+'\r\n'+fieldconfigyaml;
                       }
                       fieldsconfigyaml += fieldconfigyaml.replace(/ {4}/, '  - ');
                   });

                   if (fieldsconfigyaml != '')
                   {
                       if (!config.hasOwnProperty('displayname') || !config.displayname)
                       {
                           config.displayname = view.entity['entity-displayname'];
                       }
                       yaml += '- displayname: ' + config.displayname+'\r\n';
                       yaml += '  entitycode: ' + config.entitycode+'\r\n';

                       yaml += self.generateYamlFromConfig(config, 'filterform', 2);
                       yaml += self.generateYamlFromConfig(config, 'filters', 2);

                       yaml += '  fields: \r\n';
                       yaml += fieldsconfigyaml;
                   }
               });
               return yaml;
           },
           findRelationInConfig: function(config, relationcode) {
               var fields = [];
               var relation = null;
               if (config.hasOwnProperty('fields'))
               {
                   fields = config.fields;
               }
               $.each(fields, function(fld, field){
                   if (field.hasOwnProperty('relationcode') && field.relationcode == relationcode)
                   {
                       relation = field;
                       return;
                   }
               });
               return relation;
           },
           findFieldInConfig: function(config, fieldcode, relationcode){
               var self = this;
               var result = null;
               var endfields = [];
               if (relationcode === undefined && config.hasOwnProperty('fields'))
               {
                   endfields = config.fields;
               } else {
                   var relation = self.findRelationInConfig(config, relationcode);
                   if (relation && fieldcode === null)
                   {
                       return relation;
                   }
                   if (relation && relation.hasOwnProperty('fields'))
                   {
                       endfields = relation.fields;
                   }
               }

               $.each(endfields, function(fld, endfield){
                   if(endfield.hasOwnProperty('fieldcode') && endfield.fieldcode == fieldcode)
                   {
                       result = endfield;
                       return;
                   }
               });
               return result;
           },
           /* Принимает код редактируемой вкладки, собирает инфу из DOM и кладет в __configured_views */
           applyViewChanges: function(viewcode) {
               var self = this;
               var viewcard = $('.view[data-view-code="'+viewcode+'"]');
               var view = self.getViewByCode(viewcode);
               var oldconfig = Object.assign({}, view['view-config']);

               var displayname = viewcard.find('.view-displayname').val();
               view['view-displayname'] = displayname;
               view['view-config']['displayname'] = displayname;

               if (oldconfig.hasOwnProperty('filterform')) {
                   view['view-config']['filterform'] = oldconfig.filterform;
               }
               if (oldconfig.hasOwnProperty('filters')) {
                   view['view-config']['filters'] = oldconfig.filters;
               }
               if (oldconfig.hasOwnProperty('filterparams')) {
                   view['view-config']['filterparams'] = oldconfig.filterparams;
               }

               var fields = view['view-config']['fields'] = [];
               var checksSelector = ' > .entity-fields > .field > input[type="checkbox"]:checked';

               viewcard.find('.entity'+checksSelector).each(function(){
                   var fielditem = $(this).parent('.field').first();
                   var field = {fieldcode: fielditem.data('field-code')};
                   var fielddisplayname = fielditem.find('.field-displayname').val();
                   if (fielddisplayname)
                   {
                       field.displayname = fielddisplayname;
                   }
                   var oldfield = self.findFieldInConfig(oldconfig, field.fieldcode);
                   if (oldfield && oldfield.hasOwnProperty('modifiers'))
                   {
                       field.modifiers = oldfield.modifiers;
                   }
                   if (oldfield && oldfield.hasOwnProperty('template'))
                   {
                       field.template = oldfield.template;
                   }
                   fields.push(field);
               });

               viewcard.find('.entity-relation').each( function(){
                   var relation = $(this);
                   if (relation.children('input[type=checkbox]:checked').length == 0)
                   {
                       // данное поле со связанной сущностью не помечено, как необходимое для отображения
                       // раньше отображали все с полями, теперь без полей - значит отобразить количество строк
                       // не помечено - пропускаем
                       return;
                   }
                   var relatedentityfields = [];
                   relation.find('.relation-relatedentity'+checksSelector).each(function(){
                       var fielditem = $(this).parent('.field').first();

                       var relatedentityfield = {
                           fieldcode: fielditem.data('field-code')
                       };
                       var fielddisplayname = fielditem.find('.field-displayname').val();
                       if (fielddisplayname)
                       {
                           relatedentityfield.displayname = fielddisplayname;
                       }

                       // полям связанной таблицы тоже можно настраивать шаблоны и модификаторы
                       var oldfield = self.findFieldInConfig(
                           oldconfig,
                           relatedentityfield.fieldcode,
                           relation.data('relation-code')
                       );
                       if (oldfield && oldfield.hasOwnProperty('modifiers'))
                       {
                           relatedentityfield.modifiers = oldfield.modifiers;
                       }
                       if (oldfield && oldfield.hasOwnProperty('template'))
                       {
                           relatedentityfield.template = oldfield.template;
                       }

                       relatedentityfields.push(relatedentityfield);
                   });

                   var field = {
                       relationcode: relation.data('relation-code'),
                       fields: relatedentityfields,
                   };

                   var oldfield = self.findFieldInConfig(oldconfig, null, field.relationcode);
                   if (oldfield && oldfield.hasOwnProperty('modifiers'))
                   {
                       field.modifiers = oldfield.modifiers;
                   }
                   if (oldfield && oldfield.hasOwnProperty('template'))
                   {
                       field.template = oldfield.template;
                   }
                   // для полей связанных таблиц есть возможность прокинуть параметры фильтрации в
                   // связанную с полем сущность - сохраним эту возможность
                   if (oldfield && oldfield.hasOwnProperty('filterparams'))
                   {
                       field.filterparams = oldfield.filterparams;
                   }

                   var fielddisplayname = relation.find('.relation-displayname').val();
                   if (fielddisplayname)
                   {
                       field.displayname = fielddisplayname;
                   }
                   fields.push(field);
               });

               // сохранить конфиг
               // по результату будет запущена полная инициализация, всё с нуля перерисуется
               self.saveConfig();
           },
           /* После рендера блока редактирования навешивет нужные события,
            * чтобы все изменения сохранялись в __configured_views */
           addEditHandlers: function(editEntitySection){
               var self = this;

               editEntitySection.find('.save-view-config').click(function(){
                   var spinner = $('<span>')
                       .addClass('spinner-grow')
                       .addClass('spinner-grow-sm')
                       .attr('status', 'status')
                       .attr('aria-hidden', 'true');
                   $(this).append(spinner).attr('disabled', 'disabled').off('click');
                   var viewtosave = $(this).parents('.view').first();
                   self.applyViewChanges(viewtosave.data('view-code'));
               });
               editEntitySection.find('.cancel-view-config').click(function(){
                   self.displayViews();
                   $('.otcontrolpanel_config .add-view-section').show();
                   $('.otcontrolpanel_config .edit-view-section').html('').show();
               });
           },
           /* После рендера блока редактирования с потенциально возможными настройками,
            * отмечает и заполняет те поля, которые уже сохранены в конфиге __configured_views */
           applyConfig: function(editEntitySection){
               var self = this;
               $.each(self.__configured_views, function(v, view){
                   var viewcode = view['view-code'];
                   var config = view['view-config'];
                   var entitycode = config['entitycode'];

                   var viewcard = editEntitySection.find('.view[data-view-code="'+viewcode+'"]');

                   if (viewcard.find('.entity').data('entity-code') != entitycode)
                   {
                       return;
                   }

                   // название вкладки
                   if (!config.hasOwnProperty('displayname') || !config.displayname)
                   {
                       config.displayname = view.entity['entity-displayname'];
                   }
                   viewcard.find('.view-displayname').val(config.displayname);

                   $.each(config.fields, function(fld, fieldconfig){

                       var fieldorig = null;
                       $.each(view.entity['entity-fields'], function(f, field){
                           if (field['field-code'] == fieldconfig.fieldcode)
                           {
                               fieldorig = field;
                           }
                       });
                       if (!fieldconfig.hasOwnProperty('displayname') || !fieldconfig.displayname)
                       {
                           if (fieldorig)
                           {
                               fieldconfig.displayname = fieldorig['field-displayname'];
                           } else {
                               fieldconfig.displayname = fieldconfig.fieldcode;
                           }
                       }
                       if (fieldconfig.hasOwnProperty('relationcode'))
                       {
                           var relationorig = null;
                           $.each(view.entity['entity-relations'], function(r, relation){
                               if (relation['relation-code'] == fieldconfig.relationcode)
                               {
                                   relationorig = relation;
                               }
                           });

                           var relsel = '.entity-relation[data-relation-code="'+fieldconfig.relationcode+'"]';
                           var relation = viewcard.find(relsel);
                           if(!fieldconfig.hasOwnProperty('displayname') || !fieldconfig.displayname)
                           {
                               if (relationorig) {
                                   fieldconfig.displayname = relationorig['relation-relatedentity']['entity-displayname'];
                               } else {
                                   fieldconfig.displayname = fieldconfig.relationcode;
                               }
                           }
                           relation.find('.relation-displayname').val(fieldconfig.displayname);
                           // раз связь в конфиге есть - значит она должна быть помечена,
                           // чтобы сохранилась при обновлении конфига
                           relation.children('input[type=checkbox]').prop('checked', true);


                           $.each(fieldconfig.fields, function(fc, fielddata){
                               var cbxsel = '.field[data-field-code="'+fielddata.fieldcode+'"] > input[type="checkbox"]';
                               relation.find(cbxsel).prop('checked', true);


                               var relatedentityfieldorig = null;
                               var relatedentityfields = relationorig['relation-relatedentity']['entity-fields'];
                               $.each(relatedentityfields, function(f, field){
                                   if (field['field-code'] == fielddata.fieldcode)
                                   {
                                       relatedentityfieldorig = field;
                                   }
                               });

                               if (!fielddata.hasOwnProperty('displayname') || !fielddata.displayname)
                               {
                                   if (relatedentityfieldorig)
                                   {
                                       fielddata.displayname = relatedentityfieldorig['field-displayname'];
                                   } else {
                                       fielddata.displayname = fielddata.fieldcode;
                                   }
                               }
                               var inputsel = '.field[data-field-code="'+fielddata.fieldcode+'"] > .field-displayname';
                               relation.find(inputsel).val(fielddata.displayname);
                           });
                       } else {
                           var entityfields = viewcard.find('.entity > .entity-fields');
                           var cbxsel = '.field[data-field-code="'+fieldconfig.fieldcode+'"] > input[type="checkbox"]';
                           entityfields.find(cbxsel).prop('checked', true);
                           var inputsel = '.field[data-field-code="'+fieldconfig.fieldcode+'"] > .field-displayname';
                           entityfields.find(inputsel).val(fieldconfig.displayname);
                       }
                   });
               });
           },
           /* Рендерит блок редактирования для указанной вкладки,
            * вызывает навешивание событий редактирования и заполнение текущим состоянием конфига */
           editView: function(viewcode)
           {
               var self = this;

               $('.otcontrolpanel_config .add-view-section').hide();

               var spinner = $('.otcontrolpanel_config .views-section .views li[data-view-code="'+viewcode+'"] .spinner');
               spinner.removeClass('d-none').addClass('d-inline-block');
               var view = self.getViewByCode(viewcode);
               if (!view.hasOwnProperty('entity'))
               {
                   view.entity = self.getEntityByCode(view['view-config']['entitycode']);
               }
               if (!view['view-config'].hasOwnProperty('displayname'))
               {
                   view['view-config']['displayname'] = view['view-displayname'];
               }

               templates.render('local_otcontrolpanel/config-edit-view', {'view': view})
                   .then(function(html, js) {
                       var editEntitySection = $('.otcontrolpanel_config .edit-view-section');
                       editEntitySection.show();
                       templates.replaceNodeContents(editEntitySection, html, js);
                       $('.otcontrolpanel_config .views-section').hide();
                       self.addEditHandlers(editEntitySection);
                       self.applyConfig(editEntitySection);
                   })
                   .fail(notification.exception)
                   .done(function(){ spinner.removeClass('d-inline-block').addClass('d-none'); });
           },
           /* Находит и возвращает вкладку по коду в __configured_views
            * Если не находит, возвращает null */
           getViewByCode: function(viewcode){
               var self = this;
               var result = null;
               $.each(self.__configured_views, function(v, view){
                   if(view['view-code'] == viewcode)
                   {
                       result = view;
                       return;
                   }
               });
               return result;
           },
           /* Находит по коду сущность в __config_structure и возвращает её
            * Если не находит возвращает null
            * Нужно чтобы получить все возможные варианты настроек/полей */
           getEntityByCode: function(entitycode){
               var self = this;
               var result = null;
               $.each(self.__config_structure.entities, function(e, entity){
                   if(entity['entity-code'] == entitycode)
                   {
                       result = entity;
                       return;
                   }
               });
               return result;
           },
           /* Возвращает следующий порядковый номер вкладки */
           getNextViewcode: function(){
               var self = this;
               var max = 0;
               $.each(self.__configured_views, function(v, view){
                   if (view['view-code'] > max)
                   {
                       max = view['view-code'];
                   }
               });
               return max + 1;
           },
           /* Рендерит блок добавления новой вкладки, обрабатывает добавление */
           displayAddView: function(){
               var self = this;

               templates.render('local_otcontrolpanel/config-add-view', {entities: self.__config_structure.entities})
                   .then(function(html, js) {
                       var addViewSection = $('.otcontrolpanel_config .add-view-section');
                       templates.replaceNodeContents(addViewSection, html, js);

                       addViewSection.show();
                       addViewSection.find('input[type="button"]').click(function(){
                           var entitycode = addViewSection.find('select option:selected').val();
                           var entity = self.getEntityByCode(entitycode);
                           var viewcode = self.getNextViewcode();
                           self.__configured_views.push({
                               'view-code': viewcode,
                               'view-config': {
                                   'entitycode': entity['entity-code'],
                                   'displayname': entity['entity-displayname'],
                                   'fields': []
                               },
                               'view-displayname': entity['entity-displayname']
                           });
                           self.displayViews(function(){
                               self.editView(viewcode);
                           });
                       });
                   })
                   .fail(notification.exception);
           },
           /* Отображает список настроенных вкладок, навешивае запуск редактирования по клику */
           displayViews: function(callback) {
               var self = this;

               $.each(self.__configured_views, function(v, view){
                   view['fields-count'] = view['view-config'].fields.length;
               });

               window.console.log(self.__configured_views);

               templates.render('local_otcontrolpanel/config-views-list', {views: self.__configured_views})
                   .then(function(html, js) {
                       var viewsSection = $('.otcontrolpanel_config .views-section');
                       viewsSection.show();
                       templates.replaceNodeContents(viewsSection, html, js);
                       viewsSection.find('.views > li')
                           .filter(function(){
                               return $(this).data('view-edit-disabled') != 1;
                           }).click(function(){
                               self.editView($(this).data('view-code'));
                           });
                       viewsSection.find('.restore-default-config').click(function(){
                           var spinner = $('<span>')
                               .addClass('spinner-grow')
                               .addClass('spinner-grow-sm')
                               .attr('status', 'status')
                               .attr('aria-hidden', 'true');
                           $(this).append(spinner).attr('disabled', 'disabled').off('click');
                           self.restoreDefaults();
                       });
                       if (callback && {}.toString.call(callback) === '[object Function]')
                       {
                           callback();
                       }
                   })
                   .fail(notification.exception);
           },
           /* получение конфига через аякс и инициализация всех остальных механизмов */
           init: function() {
               var self = this;

               $('.otcontrolpanel_config .views-section').html('').show();
               $('.otcontrolpanel_config .add-view-section').html('').show();
               $('.otcontrolpanel_config .edit-view-section').html('').show();

               var strLoading = str.get_string('config_is_loading', 'local_otcontrolpanel');
               $.when(strLoading).done(function(strLoading){
                   $('.otcontrolpanel_config .views-section').html(strLoading);
                   ajax.call([{
                       methodname : 'local_otcontrolpanel_get_config_data',
                       args: [],
                       fail: notification.exception,
                       done: function(response) {
                           var decodedresponse = $.parseJSON(response);
                           self.__config_structure = decodedresponse.config_structure;
                           self.__configured_views = decodedresponse.configured_views;
                           self.displayViews();
                           self.displayAddView();
                       },
                   }]);
               });
           }
       };
    }
);
