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

/*
 * @package    atto_otiframe
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module moodle-atto_otiframe-button
 */

/**
 * Atto text editor otiframe plugin.
 *
 * @namespace M.atto_otiframe
 * @class button
 * @extends M.editor_atto.EditorPlugin
 */

var COMPONENTNAME = 'atto_otiframe',
    CSS = {
        URLINPUT: 'atto_otiframe_urlentry',
        AUTOSIZE: 'atto_otiframe_autosize',
        MANUALSIZE: 'atto_otiframe_manualsize',
        INPUTWIDTH: 'atto_otiframe_widthentry',
        INPUTHEIGHT: 'atto_otiframe_heightentry',
    },
    SELECTORS = {
        URLINPUT: '.atto_otiframe_urlentry',
        AUTOSIZE: '.atto_otiframe_autosize',
        INPUTWIDTH: '.atto_otiframe_widthentry',
        INPUTHEIGHT: '.atto_otiframe_heightentry',
    },
    TEMPLATE = '' +
            '<form class="atto_form">' +
                '{{#if showFilepicker}}' +
                    '<label for="{{elementid}}_atto_otiframe_urlentry">{{get_string "enterurl" component}}</label>' +
                    '<div class="input-group input-append w-100 m-b-1">' +
                        '<input class="form-control url {{CSS.URLINPUT}}" type="url" ' +
                        'id="{{elementid}}_atto_otiframe_urlentry"/>' +
                        '<span class="input-group-append">' +
                            '<button class="btn btn-default openotiframebrowser" type="button">' +
                            '{{get_string "browserepositories" component}}</button>' +
                        '</span>' +
                    '</div>' +
                '{{else}}' +
                    '<div class="m-b-1">' +
                        '<label for="{{elementid}}_atto_otiframe_urlentry">{{get_string "enterurl" component}}</label>' +
                        '<input class="form-control fullwidth url {{CSS.URLINPUT}}" type="url" ' +
                        'id="{{elementid}}_atto_otiframe_urlentry" size="32"/>' +
                    '</div>' +
                '{{/if}}' +
                
                // Add the auto-size checkbox
                '<div class="form-check">' +
                    '<input type="checkbox" class="form-check-input fullscreen {{CSS.AUTOSIZE}}" '+
                        'id="{{elementid}}_{{CSS.AUTOSIZE}}" checked="checked"/>' +
                    '<label class="form-check-label" for="{{elementid}}_{{CSS.AUTOSIZE}}">' +
                    '{{get_string "size_auto" component}}' +
                    '</label>' +
                '</div>' +

                // Add the size entry boxes.
                '<div class="mb-1">' +
                '<label class="" for="{{elementid}}_{{CSS.INPUTSIZE}}">{{get_string "size_manual" component}}</label>' +
                '<div id="{{elementid}}_{{CSS.MANUALSIZE}}" class="form-inline {{CSS.MANUALSIZE}}">' +
                
                // Add the width entry box.
                '<label class="accesshide" for="{{elementid}}_{{CSS.INPUTWIDTH}}">{{get_string "width" component}}</label>' +
                '<input type="text" class="form-control mr-1 input-mini {{CSS.INPUTWIDTH}}" ' +
                'id="{{elementid}}_{{CSS.INPUTWIDTH}}" size="4" disabled="disabled"/> x' +

                // Add the height entry box.
                '<label class="accesshide" for="{{elementid}}_{{CSS.INPUTHEIGHT}}">{{get_string "height" component}}</label>' +
                '<input type="text" class="form-control ml-1 input-mini {{CSS.INPUTHEIGHT}}" ' +
                'id="{{elementid}}_{{CSS.INPUTHEIGHT}}" size="4" disabled="disabled"/>' +

                '</div>' +
                '</div>' +
                // Adding size entry boxes ended
                
                '<div class="mdl-align">' +
                    '<br/>' +
                    '<button type="submit" class="btn btn-default submit">{{get_string "createotiframe" component}}</button>' +
                '</div>' +
            '</form>',
    IFRAMETEMPLATE = '<iframe '+
            'class="otiframe {{#if autosize}}autosize{{/if}}" '+
            '{{#if haswidth}}width="{{width}} "{{/if}}'+
            '{{#if hasheight}}height="{{height}} "{{/if}}'+
            'srcdoc="{{#if srcdoc}}{{srcdoc}}{{else}}{{get_string "srcdoc" component}}{{/if}}" '+
            'data-source="{{url}}" '+
        '/>';

Y.namespace('M.atto_otiframe').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {

    /**
     * A reference to the current selection at the time that the dialogue
     * was opened.
     *
     * @property _currentSelection
     * @type Range
     * @private
     */
    _currentSelection: null,

    /**
     * A reference to the dialogue content.
     *
     * @property _content
     * @type Node
     * @private
     */
    _content: null,

    initializer: function() {
        // Add the otiframe button first.
        this.addButton({
            icon: 'icon',
            iconComponent: 'atto_otiframe',
            keys: '75',
            callback: this._displayDialogue,
            tags: 'iframe',
            tagMatchRequiresAll: false
        });

        var iframe = false;
        this.editor.delegate('mouseover', function(e){ iframe = e.target; this.editor.focus(); }, 'iframe', this);
        this.editor.delegate('mouseout', function(){ iframe = false; }, 'iframe', this);
        this.editor.delegate('blur', function(e) {
            if (iframe) {
                e.preventDefault();
                this._handleClick.call(this, iframe);
            }
        }, null, this);
    },

    /**
     * Handle a click on an iframe.
     *
     * @method _handleClick
     * @param {EventFacade} e
     * @private
     */
    _handleClick: function(iframe) {
        var host = this.get('host');
        host.focus();
        host.setSelection(host.getSelectionFromNode(iframe));
        host.focus();
    },
    
    /**
     * Display the otiframe editor.
     *
     * @method _displayDialogue
     * @private
     */
    _displayDialogue: function() {
        // Store the current selection.
        this._currentSelection = this.get('host').getSelection();
        if (this._currentSelection === false) {
            return;
        }

        var dialogue = this.getDialogue({
            headerContent: M.util.get_string('createotiframe', COMPONENTNAME),
            width: 'auto',
            focusAfterHide: true,
            focusOnShowSelector: SELECTORS.URLINPUT
        });

        // Set the dialogue content, and then show the dialogue.
        dialogue.set('bodyContent', this._getDialogueContent());

        // Resolve anchors in the selected text.
        this._resolveAnchors();
        dialogue.show();
    },

    /**
     * If there is selected text and it is part of an anchor (iframe),
     * extract the url from the iframe (and set them in the form).
     *
     * @method _resolveAnchors
     * @private
     */
    _resolveAnchors: function() {
        // Find the first anchor tag in the selection.
        var host = this.get('host'),
            selectednode = host.getSelectionParentNode(),
            anchornodes,
            anchornode,
            url,
            width,
            height;
        
        this.__srcdoc = '';

        // Note this is a document fragment and YUI doesn't like them.
        if (!selectednode) {
            return;
        }

        anchornodes = this._findSelectedAnchors(Y.one(selectednode));
        if (anchornodes.length > 0) {
            anchornode = anchornodes[0];
            this._currentSelection = host.getSelectionFromNode(anchornode);
            
            url = anchornode.getAttribute('data-source');
            if (url !== '') {
                this._content.one(SELECTORS.URLINPUT).setAttribute('value', url);
            }
            
            this.__srcdoc = anchornode.getAttribute('srcdoc');

            width = anchornode.getAttribute('width');
            height = anchornode.getAttribute('height');
            if (width == '' && height == '') {
                this._content.one(SELECTORS.INPUTWIDTH).setAttribute('disabled', 'disabled');
                this._content.one(SELECTORS.INPUTHEIGHT).setAttribute('disabled', 'disabled');
                this._content.one(SELECTORS.AUTOSIZE).setAttribute('checked', 'checked');
            } else {
                this._content.one(SELECTORS.INPUTWIDTH).removeAttribute('disabled');
                this._content.one(SELECTORS.INPUTHEIGHT).removeAttribute('disabled');
                this._content.one(SELECTORS.AUTOSIZE).removeAttribute('checked');
                if (width !== '') {
                    this._content.one(SELECTORS.INPUTWIDTH).setAttribute('value', width);
                }
    
                if (height !== '') {
                    this._content.one(SELECTORS.INPUTHEIGHT).setAttribute('value', height);
                }
            }
        }
    },

    /**
     * Update the dialogue after a otiframe was selected in the File Picker.
     *
     * @method _filepickerCallback
     * @param {object} params The parameters provided by the filepicker
     * containing information about the otiframe.
     * @private
     */
    _filepickerCallback: function(params) {
        this.getDialogue()
                .set('focusAfterHide', null)
                .hide();
        if (params.url !== '') {
            // Add the otiframe.
            this._setOtiframeOnSelection(params.url);
        }
    },

    /**
     * The otiframe was inserted, so make changes to the editor source.
     *
     * @method _setOtiframe
     * @param {EventFacade} e
     * @private
     */
    _setOtiframe: function(e) {
        var input,
            value;

        e.preventDefault();
        this.getDialogue({
            focusAfterHide: null
        }).hide();

        input = this._content.one('.url');

        value = input.get('value');
        if (value !== '') {

            // We add a prefix if it is not already prefixed.
            value = value.trim();
            var expr = new RegExp(/^[a-zA-Z]*\.*\/|^#|^[a-zA-Z]*:/);
            if (!expr.test(value)) {
                value = 'http://' + value;
            }

            // Add the otiframe.
            this._setOtiframeOnSelection(value);
        }
    },

    /**
     * Final step setting the anchor on the selection.
     *
     * @private
     * @method _setOtiframeOnSelection
     * @param  {String} url URL the otiframe will point to.
     * @return {Node} The added Node.
     */
    _setOtiframeOnSelection: function(url) {
        var host = this.get('host'),
            iframehtml,
            selectednode,
            width=this._content.one(SELECTORS.INPUTWIDTH).get('value'),
            height=this._content.one(SELECTORS.INPUTHEIGHT).get('value'),
            autosize=this._content.one(SELECTORS.AUTOSIZE).get('checked');

        // Focus on the editor in preparation for inserting the image.
        host.focus();
        if (url !== '') {

            host.setSelection(this._currentSelection);

            var template = Y.Handlebars.compile(IFRAMETEMPLATE);
            var templatecontext = {
                url: url,
                autosize: autosize,
                haswidth: (!autosize && width !== ''),
                width: width,
                hasheight: (!autosize && height !== ''),
                height: height,
                component: COMPONENTNAME
            };
            if (this.__srcdoc) {
                templatecontext.srcdoc = this.__srcdoc;
            }
            iframehtml = template(templatecontext);

            selectednode = host.insertContentAtFocusPoint(iframehtml);
            host.setSelection(host.getSelectionFromNode(selectednode));
            
            // Mark the text area as updated.
            this.markUpdated();
        }
    },

    /**
     * Look up and down for the nearest anchor tags that are least partly contained in the selection.
     *
     * @method _findSelectedAnchors
     * @param {Node} node The node to search under for the selected anchor.
     * @return {Node|Boolean} The Node, or false if not found.
     * @private
     */
    _findSelectedAnchors: function(node) {
        var tagname = node.get('tagName'),
            hit, hits;

        // Direct hit.
        if (tagname && tagname.toLowerCase() === 'iframe') {
            return [node];
        }

        // Search down but check that each node is part of the selection.
        hits = [];
        node.all('iframe').each(function(n) {
            if (!hit && this.get('host').selectionContainsNode(n)) {
                hits.push(n);
            }
        }, this);
        if (hits.length > 0) {
            return hits;
        }
        // Search up.
        hit = node.ancestor('iframe');
        if (hit) {
            return [hit];
        }
        return [];
    },

    /**
     * Generates the content of the dialogue.
     *
     * @method _getDialogueContent
     * @return {Node} Node containing the dialogue content
     * @private
     */
    _getDialogueContent: function() {
        var canShowFilepicker = this.get('host').canShowFilepicker('link'),
            template = Y.Handlebars.compile(TEMPLATE);

        this._content = Y.Node.create(template({
            showFilepicker: canShowFilepicker,
            component: COMPONENTNAME,
            CSS: CSS
        }));

        this._content.one('.' + CSS.INPUTHEIGHT).on('blur', this._autoAdjustSize, this, true);
        this._content.one(SELECTORS.AUTOSIZE).on('change', function(event) {
            if (event.target.get('checked')) {
                this._content.one(SELECTORS.INPUTWIDTH).setAttribute('disabled', 'disabled');
                this._content.one(SELECTORS.INPUTHEIGHT).setAttribute('disabled', 'disabled');
            } else {
                this._content.one(SELECTORS.INPUTWIDTH).removeAttribute('disabled');
                this._content.one(SELECTORS.INPUTHEIGHT).removeAttribute('disabled');
            }
        }, this);

        this._content.one('.submit').on('click', this._setOtiframe, this);
        if (canShowFilepicker) {
            this._content.one('.openotiframebrowser').on('click', function(e) {
                e.preventDefault();
                this.get('host').showFilepicker('link', this._filepickerCallback, this);
            }, this);
        }

        return this._content;
    },
});
