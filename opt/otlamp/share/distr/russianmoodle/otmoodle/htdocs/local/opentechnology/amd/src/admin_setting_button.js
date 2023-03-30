define(['jquery', 'core/notification', 'core/templates', 'core/modal_factory', 'core/yui'],
        function($, notification, templates, ModalFactory, Y) {

    return {
        _dialogues: [],

        _composeDialogueObject: function(id, dialogue){
            return {
                id: id,
                dialogue: dialogue,
                get: function(){
                    var result = $.Deferred();
                    result.resolve(this.dialogue);
                    return result.promise();
                },
                setContent: function(content) {
                    var result = $.Deferred();
                    var promise = this.get();
                    promise.done(function(dialogue){
                        dialogue.set('bodyContent', content);
                        dialogue.centered();
                        result.resolve();
                    });
                    return result.promise();
                },
                setHeader: function(header) {
                    var result = $.Deferred();
                    var promise = this.get();
                    promise.done(function(dialogue){
                        dialogue.set('headerContent', header);
                        dialogue.centered();
                        result.resolve();
                    });
                    return result.promise();
                },
                addClass: function(classname) {
                    var result = $.Deferred();
                    var promise = this.get();
                    promise.done(function(dialogue){
                        dialogue.get('boundingBox').addClass(classname);
                        result.resolve();
                    });
                    return result.promise();
                },
                show: function() {
                    var result = $.Deferred();
                    var promise = this.get();
                    promise.done(function(dialogue){
                        dialogue.show();
                        result.resolve();
                    });
                    return result.promise();
                },
                hide: function() {
                    var result = $.Deferred();
                    var promise = this.get();
                    promise.done(function(dialogue){
                        dialogue.hide();
                        result.resolve();
                    });
                    return result.promise();
                }
            };
        },

        getDialogue: function(id) {

            var ASB = this;

            if (!(id in ASB._dialogues)) {
                ASB._dialogues[id] = $.Deferred();

                Y.use('moodle-core-notification', function() {

                    var spinner = Y.Node.create('<img />')
                        .setAttribute('src', M.util.image_url('i/loading', 'moodle'))
                        .addClass('spinner');

                    var dialogue = new M.core.dialogue({
                        headerContent: '&nbsp;',
                        bodyContent: Y.Node.create('<div />').addClass('content-lightbox').append(spinner),
                        draggable: true,
                        visible: false,
                        center: true,
                        modal: true,
                        width: '500px',
                    });

                    ASB._dialogues[id].resolve(ASB._composeDialogueObject(id, dialogue));

                });
            }

            return ASB._dialogues[id].promise();
        },

        initAndRender: function(id, dialogue_header, template_data, template_fullname) {

            var ASB = this;

            var result = $.Deferred();

            ASB.init(id, dialogue_header).done(function(DObj){


                template_data['id'] = id;
                templates.render(template_fullname, template_data)
                    .then(function(html, js) {
                        var content = $('<div>');
                        templates.replaceNodeContents(content, html, js);
                        DObj.setContent(content);
                    }).fail(function(ex) {
                        DObj.setContent('error');
                        notification.alert(ex.message);
                    });

                result.resolve(DObj);
            });

            return result.promise();
        },

        init: function(id, dialogue_header) {
            var ASB = this;

            var result = $.Deferred();

            ASB.getDialogue(id).done(function(DObj){

                $('#'+id+'-button').unbind('click').click(function(e) {
                    e.preventDefault();
                    DObj.show();
                }).removeAttr('disabled');

                $('#'+id).on('readyToSave', function(event, data){
                    $(this).val(JSON.stringify(data));
                    DObj.hide();
                });

                DObj.setHeader(dialogue_header);

                result.resolve(DObj);

            });

            return result.promise();
        }
    };
});