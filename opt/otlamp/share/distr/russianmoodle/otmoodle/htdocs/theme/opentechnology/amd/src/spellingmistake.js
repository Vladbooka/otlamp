define(['jquery', 'core/ajax', 'core/str', 'theme_opentechnology/flashmessage'], function($, ajax, strman, flashmessage) {
    return {
        body: $('body'),
        forbiddenTags: ['script', 'style', 'frame', 'iframe', 'meta', 'link', 'img'],
        entityMap: {"&": "&amp;", "<": "&lt;", ">": "&gt;", '"': '&quot;', "'": '&#39;', "/": '&#x2F;'},
        STRINGS: {
            'spelling_mistake_modal_title': '...progress',
            'spelling_mistake_modal_question_one': '...progress',
            'spelling_mistake_modal_question_two': '...progress',
            'spelling_mistake_modal_comment_placeholder': '...progress',
            'spelling_mistake_modal_close': '...progress',
            'spelling_mistake_modal_ok': '...progress',
            'spelling_mistake_modal_success': '...progress',
            'spelling_mistake_modal_fail': '...progress'
        },
        ORFOMODAL: null,

        label: function (n) {
            var CONTROLLER = this;
            return CONTROLLER.STRINGS[n];
        },
        clearText: function(txt){
            var CONTROLLER = this;
            if (/\S/.test(txt)) {
                return txt.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').replace(/[&<>"'\/]/g, function (s) {
                    return CONTROLLER.entityMap[s];
                });
            }
        },
        extractTextFromNode: function(n){
            var CONTROLLER = this;
            for (var j = 0; j < CONTROLLER.forbiddenTags.length; j++) {
                $(CONTROLLER.forbiddenTags[j], n).remove();
            }
            var nHtml = n.innerHTML.replace(/</gm, ' <');
            var txt = (new DOMParser()).parseFromString(nHtml, 'text/html').body.innerText || '';
            txt = txt.replace(/(\n|\r)/gm, ' ').replace(/^\s+|\s+$/g, '').replace(/\s{2,}/g, ' ');
            return txt;
        },
        extractTextFromRange: function(range){
            var CONTROLLER = this;
            var result = '';
            try {
                var clonedContent = range.cloneContents();
                var nodes = clonedContent.children || clonedContent.childNodes;
                var texts = [];
                for (var i = 0, len = nodes.length; i < len; i++) {
                    var txt = CONTROLLER.extractTextFromNode(nodes[i]);
                    if (/\S/.test(txt)) {
                        texts.push(txt);
                    }
                }
                result = texts.join(' ');
            } catch(e) {
                result = CONTROLLER.clearText(range.toString());
            }
            return result;
        },
        getSelectionText: function(win) {
            var CONTROLLER = this;
            var text = '';

            if (win === undefined)
            {
                win = window;
            }

            var doc = win.document;

            if (win.getSelection) {
                text = win.getSelection().toString();
            } else if (doc.selection && doc.selection.type != 'Control') {
                text = doc.selection.createRange().text;
            }
            return CONTROLLER.clearText(text);
        },
        getSelectedPhrase: function(){
            var CONTROLLER = this;
            try{
                var nodes = [], texts = [], selection;
                if (window.getSelection) {
                    selection = getSelection();
                    for (var i = 0, len = selection.rangeCount; i < len; i++) {
                        var rangeObj = selection.getRangeAt(i),
                            startContainer = rangeObj.startContainer,
                            endContainer = rangeObj.endContainer;
                        if (startContainer) {
                            nodes.push(startContainer.nodeName === "#text" ? startContainer.parentNode : startContainer);
                        }
                        if (endContainer) {
                            nodes.push(endContainer.nodeName === "#text" ? endContainer.parentNode : endContainer);
                        }
                    }
                    if (nodes.length === 0) {
                        nodes = [selection.anchorNode];
                    }
                }
                if (!nodes.length && document.selection) {
                    selection = document.selection;
                    var range = selection.getRangeAt ? selection.getRangeAt(0) : selection.createRange();
                    var node = range.commonAncestorContainer ? range.commonAncestorContainer :
                            range.parentElement ? range.parentElement() : range.item(0);
                    if (node) {
                        nodes = [node];
                    }
                }

                for (var i = 0, nLen = nodes.length; i < nLen; i++) {
                    var node = nodes[i];
                    if (node.nodeName == "#text") {
                        node = node.parentNode;
                    }
                    try {
                        texts.push(CONTROLLER.extractTextFromNode(node.cloneNode()));
                    } catch(e) {
                        texts.push($(node).text());
                    }
                }
                return texts.join(' ');
            } catch (e) {
            }
        },
        getAroundSelectedText: function(containerEl){
            var CONTROLLER = this;
            if (!containerEl) {
                containerEl = CONTROLLER.body.get(0);
            }
            var sel, range, tempRange, before = "", after = "";
            if (typeof window.getSelection != "undefined") {
                sel = window.getSelection();
                if (sel.rangeCount) {
                    range = sel.getRangeAt(0);
                } else {
                    range = document.createRange();
                    range.collapse(true);
                }
                tempRange = document.createRange();
                tempRange.selectNodeContents(containerEl);
                tempRange.setEnd(range.startContainer, range.startOffset);
                before = CONTROLLER.extractTextFromRange(tempRange);

                tempRange.selectNodeContents(containerEl);
                tempRange.setStart(range.endContainer, range.endOffset);
                after = CONTROLLER.extractTextFromRange(tempRange);
            } else if ((sel = document.selection) && sel.type != "Control") {
                range = sel.createRange();
                tempRange = document.body.createTextRange();
                tempRange.moveToElementText(containerEl);
                tempRange.setEndPoint("EndToStart", range);
                before = CONTROLLER.clearText(tempRange.text || '');

                tempRange.moveToElementText(containerEl);
                tempRange.setEndPoint("StartToEnd", range);
                after = CONTROLLER.clearText(tempRange.text || '');
            }

            return {'before': before, 'after': after};
        },
        displayModal: function(drama){
            var CONTROLLER = this;
            var html = '<div class="opentechnology-modal moodle-has-zindex" tabindex="-1" style="display: block;">'+
                '<div class="opentechnology-modal-dialog moodle-has-zindex">'+
                  '<div class="opentechnology-modal-content">'+
                    '<div class="opentechnology-modal-header">'+
                      '<h2 class="opentechnology-modal-header-info">' + CONTROLLER.label('spelling_mistake_modal_title') + '</h2>'+
                      '<div class="opentechnology-modal-header-close moodle-has-zindex"></div>'+
                    '</div>'+
                    '<div class="opentechnology-modal-body">'+
                        '<div class="opentechnology-modal-drama">&laquo;' + drama + '&raquo;</div>'+
                        '<div>' + CONTROLLER.label('spelling_mistake_modal_question_one') + '</div>'+
                        '<input placeholder="' + CONTROLLER.label('spelling_mistake_modal_comment_placeholder') +
                            '" name="comment" type="text" style="margin-bottom:0 !important;">'+
                    '</div>'+
                    '<div class="opentechnology-modal-footer">'+
                        '<button class="button btn btn-primary" type="button" role="submit">'
                            + CONTROLLER.label('spelling_mistake_modal_ok') + '</button>\r\n'+
                        '<button class="button btn btn-primary" type="button" role="close">'
                            + CONTROLLER.label('spelling_mistake_modal_close') + '</button>\r\n'+
                        '<div class="opentechnology-notice">' + CONTROLLER.label('spelling_mistake_modal_question_two') + '</div>'+
                    '</div>'+
                '</div>'+
            '</div>'+
            '</div>'.replace(/(\n|\r|\r\n)/gm, '').replace(/\s+/g, ' ');

            CONTROLLER.ORFOMODAL = $(html);
            CONTROLLER.body.addClass('opentechnology-modal-open').append(CONTROLLER.ORFOMODAL);
            return CONTROLLER.ORFOMODAL;
        },
        hideModal: function(){
            var CONTROLLER = this;
            CONTROLLER.body.removeClass('opentechnology-modal-open');
            if (CONTROLLER.ORFOMODAL && CONTROLLER.ORFOMODAL.length > 0) {
                CONTROLLER.ORFOMODAL.remove();
            }
        },

        setStrings: function() {
            var CONTROLLER = this;

            // получение языковых строк
            strman.get_strings([
                { key: 'spelling_mistake_modal_title', component: 'theme_opentechnology' },
                { key: 'spelling_mistake_modal_question_one', component: 'theme_opentechnology' },
                { key: 'spelling_mistake_modal_question_two', component: 'theme_opentechnology' },
                { key: 'spelling_mistake_modal_comment_placeholder', component: 'theme_opentechnology' },
                { key: 'spelling_mistake_modal_close', component: 'theme_opentechnology' },
                { key: 'spelling_mistake_modal_ok', component: 'theme_opentechnology' },
                { key: 'spelling_mistake_modal_success', component: 'theme_opentechnology' },
                { key: 'spelling_mistake_modal_fail', component: 'theme_opentechnology' }
            ]).done(function (strs) {

                // установка языковых строк
                CONTROLLER.STRINGS['spelling_mistake_modal_title'] = strs[0];
                CONTROLLER.STRINGS['spelling_mistake_modal_question_one'] = strs[1];
                CONTROLLER.STRINGS['spelling_mistake_modal_question_two'] = strs[2];
                CONTROLLER.STRINGS['spelling_mistake_modal_comment_placeholder'] = strs[3];
                CONTROLLER.STRINGS['spelling_mistake_modal_close'] = strs[4];
                CONTROLLER.STRINGS['spelling_mistake_modal_ok'] = strs[5];
                CONTROLLER.STRINGS['spelling_mistake_modal_success'] = strs[6];
                CONTROLLER.STRINGS['spelling_mistake_modal_fail'] = strs[7];
            });
        },
        keydownHandler: function(e){
            var CONTROLLER = this;
            if ((e.ctrlKey || e.metaKey) && e.keyCode == 13) {
                var doc = e.currentTarget;
                var win = doc.parentWindow || doc.defaultView;
                var text = CONTROLLER.getSelectionText(win);
                if (text !== undefined && /\S/.test(text) && text.length > 1) {
                    var phrase = CONTROLLER.getSelectedPhrase(),
                        aroundText = CONTROLLER.getAroundSelectedText(),
                        aroundTextStart = aroundText.before.slice(-50),
                        aroundTextEnd = aroundText.after.slice(0, 50),
                        previewText = '&hellip;' + aroundTextStart +
                            ' <strong>' + text + '</strong> ' + aroundTextEnd + '&hellip;';

                        CONTROLLER.displayModal(previewText).on('click', 'button[role="close"]', function(){
                            CONTROLLER.hideModal();
                        }).on('click', '.opentechnology-modal-header-close', function () {
                            CONTROLLER.hideModal();
                        }).on('click', 'button[role="submit"]', function(){

                            var promises = ajax.call([{
                                methodname : 'theme_opentechnology_send_spelling_mistake',
                                args: {
                                    'url'        : window.location.toString(),
                                    'mistake'    : text,
                                    'phrase'    : phrase,
                                    'start'        : aroundTextStart,
                                    'end'        : aroundTextEnd,
                                    'comment'    : $(this).closest('.opentechnology-modal').find('input[name="comment"]').val()
                                }
                            }]);

                            promises[0]
                                .done(function(){
                                    flashmessage.addMessage('success', CONTROLLER.label('spelling_mistake_modal_success'));
                                })
                                .fail(function(){
                                    if (window.console) {
                                        flashmessage.addMessage('fail', CONTROLLER.label('spelling_mistake_modal_fail'));
                                    }
                                }).always(function () {
                                    CONTROLLER.hideModal();
                                });
                        });
                }
            }
        },
        keyupHandler: function(e){
            var CONTROLLER = this;
            if (e.keyCode == 27) {
                CONTROLLER.hideModal();
            }
        },
        init: function() {
            var CONTROLLER = this;

            CONTROLLER.setStrings();

            var kdh = CONTROLLER.keydownHandler.bind(CONTROLLER);
            var kuh = CONTROLLER.keyupHandler.bind(CONTROLLER);

            $(document)
                .off('keydown keyup')
                .on('keydown', kdh)
                .on('keyup', kuh);

            // добавление обработки нажатий внутри фреймов
            $("iframe").each(function(){
                $(this).on('load', function(){
                    // надеемся, что нет таких умных, которые меняют location.href в iframe, как scorm
                    if (this.contentWindow)
                    {
                        $(this.contentWindow.document).off('keydown keyup').on('keydown', kdh).on('keyup', kuh);
                    }
                });
                // событие может и не вызваться в случае, если контент добавлен не динамически
                if (this.contentWindow)
                {
                    $(this.contentWindow.document).off('keydown keyup').on('keydown', kdh).on('keyup', kuh);
                }
            });

            if ($('#scorm_content').length > 0)
            {
                // скорм умеет создавать DOM-элемент динамически, поэтому через mutation observer отлавливаем
                var observer = new MutationObserver(function() {
                    $("#scorm_content iframe").each(function(){
                        $(this).on('load', function(){
                            // скорм реально подгружает документ через изменение location.href
                            // отловить это изменение нельзя из-за секурности, поэтому таймер
                            var iframe = this;

                            // пытаемся навесить обработку каждые полсек
                            var timerid = setInterval(function(){
                                if (iframe.contentWindow)
                                {
                                    $(iframe.contentWindow.document)
                                        .off('keydown keyup')
                                        .on('keydown', kdh)
                                        .on('keyup', kuh);
                                }
                            }, 500);

                            // а через 5 сек останавливаем нашу деятельность
                            setTimeout(function(){ clearInterval(timerid); }, 5000);
                        });
                        // событие может и не вызваться в случае, если контент добавлен не динамически
                        if (this.contentWindow)
                        {
                            $(this.contentWindow.document).off('keydown keyup').on('keydown', kdh).on('keyup', kuh);
                        }
                    });
                });
                observer.observe($('#scorm_content').get(0), {
                    subtree: true,
                    attributes: true,
                    childList: true,
                    characterData: true
                });
            }
        }
    };
});
