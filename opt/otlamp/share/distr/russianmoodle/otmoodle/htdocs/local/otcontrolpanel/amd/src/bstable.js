define(
    [
        'jquery',
        'core/str',
        'core/notification',
        'core/templates',
        'core/fragment',
        'core/modal_factory',
        'core/yui',
        'local_opentechnology/bootstrap-my-table'
    ],
    function($, str, notification, templates, fragment, ModalFactory, Y, bsmt) {
        function bstable() {
            this.table = null;
            this.toolbar = null;
            this.selection = {};
            this.contextid = null;
            this.getDialogue = function() {
                var dialogue = $.Deferred();

                Y.use('moodle-core-notification', function() {

                    var spinner = Y.Node.create('<img />')
                        .setAttribute('src', M.util.image_url('i/loading', 'moodle'))
                        .addClass('spinner');

                    dialogue.resolve(new M.core.dialogue({
                        headerContent: '&nbsp;',
                        bodyContent: Y.Node.create('<div />').addClass('content-lightbox').append(spinner),
                        draggable: true,
                        visible: false,
                        center: true,
                        modal: true,
                        width: '600px',
                        extraClasses: ['otcp_actionform'],
                    }));

                });

                return dialogue.promise();
            };
            this.loadActionForm = function(jsonformdata){
                var self = this;

                // соберем идентификаторы прочеканных строк в таблице
                var ids = [];
                $.each(self.selection, function(index, row){
                    ids.push(row._data['record-id']);
                });

                // сформируем аргументы, необходимые для создания формы
                var args = {
                    ids: JSON.stringify(ids),
                    viewcode: self.table.parents('.view').data('view-code'),
                };
                // дополним данными самой формы, когда её отправляют (не первая загрузка)
                if (jsonformdata !== undefined)
                {
                    args.jsonformdata = jsonformdata;
                }

                // запрос на получение формы, возвращающий промис
                return fragment.loadFragment('local_otcontrolpanel', 'actionform', self.contextid, args);
            };
            this.processActionformSubmit = function(e){
                // на месте e может прилетать всевдоэвент при первой загрузке формы,
                // поэтому проверяем есть ли у него preventDefault
                if (typeof e.preventDefault === 'function')
                {
                    e.preventDefault();
                }
                // собираем данные, прилетевшие с событием
                var self = e.data.bstable;
                var dialogue = e.data.dialogue;

                var spinner = $('<img/>')
                    .addClass('spinner')
                    .attr('src', M.util.image_url('i/loading', 'moodle'));
                var wrapper = $('<div>').addClass('content-lightbox').append(spinner);
                dialogue.set('bodyContent', wrapper.prop('outerHTML'));


                // подготавливаем данные формы
                var formdata = '';
                if (!(this instanceof bstable))
                {// мы попали сюда благодаря запуску отправки формы - сериализуем её данные
                    formdata = $(this).serialize();
                }
                if (typeof formdata !== "undefined") {
                    var jsonformdata = JSON.stringify(formdata);

                    // отправляем запрос с данными из формы (если были)
                    var fragmentFormAction = self.loadActionForm(jsonformdata);
                    fragmentFormAction.done(function(response, js) {
                        var decodedresponse = $.parseJSON(response);
                        var html = decodedresponse.html;
                        var header = decodedresponse.header;

                        dialogue.set('headerContent', header);
                        // создаем и кладем его в диалог
                        var dialogueBody = $('<div>').addClass('dialogueAction');
                        dialogue.set('bodyContent', dialogueBody);

                        // наполняем созданный элемент кодом формы
                        templates.replaceNodeContents(dialogueBody, html, js);
                        // находим саму добавленную на предыдущем шаге форму
                        var form = dialogueBody.find('form.actionform');
                        // формируем параметры, из которых станет возможно обратиться к
                        // главному объекту, диалогу, и нашему элементу, который мы заполняем формой
                        var formsubmitparams = {
                            bstable: self,
                            dialogue: dialogue,
                        };
                        // навешиваем своё событие на отправку формы вместо обычного, чтобы
                        // работала она через аякс
                        form.on('submit', formsubmitparams, self.processActionformSubmit);
                        // после наполнения формы данными, отцентрируем её, ведь размеры изменились
                        dialogue.centered();
                    });
                    // ошибок быть не должно, если будут - отображаем через стандартный нотификейшн
                    fragmentFormAction.fail(notification.exception);
                }
            };
            this.actionClickHandle = function(self){
                self.getDialogue().done(function(dialogue){
                    dialogue.show();
                    dialogue.after("visibleChange", function() {
                        if (!dialogue.get('visible')) {
                            dialogue.destroy(true);
                        }
                    });

                    if (Object.keys(self.selection).length == 0)
                    {
                        // чтобы отображалась загрузка во время запроса строки ошибки,
                        // воспользуемся этим же диалогом, который уже работает как надо
                        var actionstrsPromise = str.get_strings([
                            {
                                key: 'action_no_rows_selected',
                                component: 'local_otcontrolpanel'
                            },
                            {
                                key: 'action_select_rows',
                                component: 'local_otcontrolpanel'
                            },
                        ]);
                        $.when(actionstrsPromise).done(function(actionstrs){
                            dialogue.set('headerContent', actionstrs[0]);
                            dialogue.set('bodyContent', actionstrs[1]);
                            dialogue.centered();
                        });
                    } else {
                        var pseudoevent = {data: {
                            bstable: self,
                            dialogue: dialogue
                        }};
                        self.processActionformSubmit(pseudoevent);
                    }
                });
            };
            this.init = function(bsTableSelector, contextid){
                var self = this;
                self.table = $(bsTableSelector);
                self.contextid = contextid;

                self.toolbar = $('<div>').insertBefore(self.table);


                bsmt.init(self.table, ['destroy']);
                bsmt.init(self.table, [{
                    locale: 'ru-RU',
                    showExport: true,
                    exportDataType: 'all',
                    exportTypes: ['json', 'xml', 'csv', 'txt', 'sql', 'excel'],
                    refreshOptions: {
                        buttonsOrder: ['advancedSearch', 'executeAction']
                    },
                    toolbar: self.toolbar,
                    toolbarAlign: 'left',
                    onCheck: function(row, el){
                        self.selection[el.parents('tr').data('index')] = row;
                    },
                    onUncheck: function(row, el){
                        delete self.selection[el.parents('tr').data('index')];
                    }
                }]);

                self.table.on('page-changed.bs.table', function(){
                    $.each(self.selection, function(index){
                        bsmt.init(self.table, [
                            'updateCell',
                            {
                                index: index,
                                field: 'state',
                                value: true
                            }
                        ]);
                    });
                });

                var actionstrPromise = str.get_string('action_execute', 'local_otcontrolpanel');
                $.when(actionstrPromise).done(function(actionstr){
                    var icon = $('<i>').addClass('fa').addClass('fa-bolt').addClass('pr-1');
                    var actionBtn = $('<div>').addClass('btn btn-secondary');
                    actionBtn.off('click').on('click', function(){ self.actionClickHandle(self); });
                    actionBtn.append(icon);
                    actionBtn.append(' '+actionstr);
                    self.toolbar.append(actionBtn);
                });
            };
        }
        return new bstable();
    }
);