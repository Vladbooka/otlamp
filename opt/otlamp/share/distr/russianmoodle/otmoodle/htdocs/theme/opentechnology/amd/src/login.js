define(
    ['jquery', 'core/str', 'core/yui', 'core/ajax', 'core/notification', 'core/templates'],
    function($, strman, Y, ajax, notification, templates) {
        return {
            defaultPageUrl: null,
            loginformDescription: '',
            dialogueContentReady: $.Deferred(),
            cleanModalRender: null,
            dialogue: $.Deferred(),
            cookiebtn: null,
            setCookieButton: function(dialogue, buttonContent) {

                var login = this;

                if (login.cookiebtn === null)
                {
                    login.cookiebtn = Y.Node.create('<div></div>');
                    dialogue.addButton({
                        srcNode: login.cookiebtn,
                        action: function(){},
                        section: Y.WidgetStdMod.HEADER
                    });
                    login.cookiebtn.replace(buttonContent);
                }

            },


            getDialogue: function() {

                var login = this;

                if (login.dialogue.state() != 'resolved')
                {
                    Y.use('moodle-core-notification', function() {

                        var spinner = Y.Node.create('<img />')
                            .setAttribute('src', M.util.image_url('i/loading', 'moodle'))
                            .addClass('spinner');

                        login.dialogue.resolve(new M.core.dialogue({
                            headerContent: '&nbsp;',
                            bodyContent: Y.Node.create('<div />').addClass('content-lightbox').append(spinner),
                            draggable: true,
                            visible: false,
                            center: true,
                            modal: true,
                            width: '400px',
                            extraClasses: ['otloginform'],
                        }));

                    });
                }

                return login.dialogue.promise();
            },


            initDialogue: function(button){

                var login = this;
                button.unbind('click').click(function(e){

                    e.preventDefault();

                    if (!localStorage.getItem('otJustRegistered')) {
                        // пользователь не находится в стадии, когда только что зарегистрировался
                        // можно затереть вонтсурл и поставить новый
                        // если затирать всегда, то велик шанс потерять страницу,
                        // с которой пользователь ушёл на регистрацию

                        // проверяем, есть ли кастомный вонтсурл в дата-атрибуте кнокпки
                        var customPageurl = $(this).data('customPageurl');
                        if (customPageurl) {
                            // кастомный вонтсурл
                            localStorage.setItem('otLoginWantsUrl', customPageurl);
                        } else {
                            // дефолтный вонтсурл (просто страница, на которой мы находимся)
                            localStorage.setItem('otLoginWantsUrl', login.defaultPageUrl);
                        }
                    }

                    var alert = $(this).data('loginformAlert');
                    if (alert) {
                        var alertType = $(this).data('loginformAlertType') || 'info';
                        alert = '<div class="alert alert-'+alertType+'" role="alert">'+alert+'</div>';
                    } else {
                        alert = '';
                    }

                    login.dialogueContentReady.promise().done(function(dialogue){
                        dialogue.set('bodyContent', alert + login.cleanModalRender);
                        // после set bodyContent эта гадина зачем-то восстанавливает подмененную мной кнопку
                        // надо подчистить
                        $('.moodle-dialogue.otloginform .yui3-widget-buttons > .yui3-button:not(.closebutton)').remove();
                        dialogue.centered();
                        dialogue.show();
                    });

                });
            },

            initRedirect: function(button){

                var login = this;

                login.getDialogue().done(function(dialogue){

                    if (dialogue.get('visible'))
                    {
                        document.location.href = "/login/";
                    }
                    else
                    {
                        button.unbind('click').click(function(){
                            document.location.href = "/login/";
                        });
                    }
                });

            },

            setDialogueContent: function(modalrender, cookierender){

                var login = this;

                login.getDialogue().done(function(dialogue){
                    login.cleanModalRender = modalrender;
                    login.cleanCookieRender = cookierender;

                    dialogue.set('bodyContent', modalrender);
                    login.setCookieButton(dialogue, cookierender);
                    dialogue.centered();

                    if (login.dialogueContentReady.state() != 'resolved')
                    {
                        login.dialogueContentReady.resolve(dialogue);
                    }
                });

            },


            setDialogueHeader: function(header){

                var login = this;
                login.getDialogue().done(function(dialogue){
                    dialogue.set('headerContent', header);
                });

            },


            /**
             * Базовая инициализация ajax-загрузки
             */
            init: function(contextid, buttons, pageurl, isloginorsignup) {

                var login = this;

                if (!isloginorsignup && !localStorage.getItem('otJustRegistered')) {
                    // очищаем вонтсурл если все-таки не авторизовались и не зарегистрировались,
                    // а посетили страницу отличную от логина или регистрации
                    localStorage.removeItem('otLoginWantsUrl');
                }

                login.defaultPageUrl = pageurl;

                Y.use('moodle-core-notification', function() {
                    $(buttons).each(function(){
                        login.initDialogue($(this));
                    });
                });

                var strings = strman.get_strings([
                    { key: 'ajaxpopup_loginheader', component: 'theme_opentechnology' },
                ]);
                $.when(strings).done(function(strs){
                    login.setDialogueHeader(strs[0]);
                }).fail(function(){
                    login.setDialogueHeader('Вход');
                });

                var loginform = ajax.call([{
                    methodname : 'theme_opentechnology_get_login_form',
                    args: {}
                }]);
                $.when(loginform[0])
                    .done(function(loginformresponse){
                        var loginformdata = JSON.parse(loginformresponse);

                        var modal = templates.render('theme_opentechnology/ot-loginform-modal', loginformdata);
                        var cookie = templates.render('theme_opentechnology/ot-loginform-modal-cookie', loginformdata);

                        $.when(modal, cookie)
                            .done(function(modalrender, cookierender){
                                login.setDialogueContent(modalrender[0], cookierender[0]);
                            }).fail(function(){
                                login.initRedirect($(buttons));
                            });
                    }).fail(function(){
                        login.initRedirect($(buttons));
                    });
            }
        };
});