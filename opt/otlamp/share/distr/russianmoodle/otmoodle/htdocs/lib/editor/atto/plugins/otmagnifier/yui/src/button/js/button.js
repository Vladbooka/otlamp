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
 * @package    atto_otmagnifier
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module moodle-atto_otmagnifier-button
 */

/**
 * Atto text editor otmagnifier plugin.
 *
 * @namespace M.atto_otmagnifier
 * @class button
 * @extends M.editor_atto.EditorPlugin
 */

Y.namespace('M.atto_otmagnifier').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {
    initializer: function(){
        this.addButton({
            buttonName: 'otmagnifier',
            icon: 'icon',
            iconComponent: 'atto_otmagnifier',
            tags: 'img',
            callback: this._addOrRemoveClass
        });

        // We need custom highlight logic for this button.
        var self = this;
        this.get('host').on('atto:selectionchanged', function() {
            var imgsData = self._getImgsData();

            this.buttons.otmagnifier.removeClass('highlight');

            if (imgsData.magnified.length > 0) {
                this.buttons.otmagnifier.addClass('has-magnified');
                this.buttons.otmagnifier.addClass('highlight');
            } else {
                this.buttons.otmagnifier.removeClass('has-magnified');
            }
            if (imgsData.notmagnified.length > 0)
            {
                this.buttons.otmagnifier.addClass('has-not-magnified');
                this.buttons.otmagnifier.addClass('highlight');
            } else {
                this.buttons.otmagnifier.removeClass('has-not-magnified');
            }
        }, this);

    },

    _getImgsData: function(){
        var host = this.get('host');
        var nodes = host.getSelectedNodes();
        var magnified = [];
        var notmagnified = [];
        if (nodes) {
            nodes.each(function(node) {
                var img = node.ancestor('img', true);
                if (img)
                {
                    if (img.hasClass('magnifier'))
                    {
                        magnified.push(img);
                    }else {
                        notmagnified.push(img);
                    }
                }
            }, this);
        }
        return {
            'magnified': magnified,
            'notmagnified': notmagnified
        };
    },

    _addOrRemoveClass: function(){
        var host = this.get('host');
        var nodes = host.getSelectedNodes();
        var clickhandler = this.get('clickhandler');
        if (nodes) {
            var imgs = [];
            var imgscount = 0;
            nodes.each(function(node) {
                var img = node.ancestor('img', true);
                if (img)
                {
                    imgscount++;
                    if (!img.hasClass('magnifier'))
                    {// если в выборке есть изображение без класса - класс навешиваем
                        img.addClass('magnifier');
                        if (['open', 'openseparatewindow', 'openseparatewindowfullscreen'].indexOf(clickhandler) !== -1)
                        {
                            img.addClass('magnifier-open');
                        }
                        if (['openseparatewindow', 'openseparatewindowfullscreen'].indexOf(clickhandler) !== -1)
                        {
                            img.addClass('magnifier-separate-window');
                        }
                        if (clickhandler == 'openseparatewindowfullscreen')
                        {
                            img.addClass('magnifier-fullscreen');
                        }
                    }
                    else
                    {// собираем изображения, чтобы не искать заново для случая, когда
                        // все изображения уже были с классом и надо будет убрать
                        imgs.push(img);
                    }
                }
            }, this);
            // все найденные изображения уже были с классом - пользователь хотел отключить magnifier
            if (imgs.length == imgscount)
            {
                imgs.forEach(function(img) {
                    img.removeClass('magnifier');
                    img.removeClass('magnifier-open');
                    img.removeClass('magnifier-separate-window');
                    img.removeClass('magnifier-fullscreen');
                });
            }
        }
    }
}, {
    ATTRS: {
        /**
         * Open image on click
         *
         * @attribute clickhandler
         * @type string
         * @default 'disabled'
         */
        clickhandler: {
            value: 'disabled'
        }
    }
});