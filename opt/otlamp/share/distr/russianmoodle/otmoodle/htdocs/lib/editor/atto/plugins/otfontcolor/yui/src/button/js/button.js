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
 * @package    atto_otfontcolor
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module moodle-atto_otfontcolor-button
 */

/**
 * Atto text editor otfontcolor plugin.
 *
 * @namespace M.atto_otfontcolor
 * @class button
 * @extends M.editor_atto.EditorPlugin
 */

var colornames = [
    'turquoise','green-sea','emerald','nephritis','peter-river','belize-hole','amethyst',
    'wisteria','wet-asphalt','midnight-blue','sunflower','orange','carrot','pumpkin',
    'alizarin','pomegranate','clouds','silver','concrete','asbestos'
];
var colors = {
	'turquoise-50': '#e8f8f5',
	'turquoise-100': '#d1f2eb',
	'turquoise-200': '#a3e4d7',
	'turquoise-300': '#76d7c4',
	'turquoise-400': '#48c9b0',
	'turquoise-500': '#1abc9c',
	'turquoise-600': '#17a589',
	'turquoise-700': '#148f77',
	'turquoise-800': '#117864',
	'turquoise-900': '#0e6251',
	'green-sea-50': '#e8f6f3',
	'green-sea-100': '#d0ece7',
	'green-sea-200': '#a2d9ce',
	'green-sea-300': '#73c6b6',
	'green-sea-400': '#45b39d',
	'green-sea-500': '#16a085',
	'green-sea-600': '#138d75',
	'green-sea-700': '#117a65',
	'green-sea-800': '#0e6655',
	'green-sea-900': '#0b5345',
	'emerald-50': '#eafaf1',
	'emerald-100': '#d5f5e3',
	'emerald-200': '#abebc6',
	'emerald-300': '#82e0aa',
	'emerald-400': '#58d68d',
	'emerald-500': '#2ecc71',
	'emerald-600': '#28b463',
	'emerald-700': '#239b56',
	'emerald-800': '#1d8348',
	'emerald-900': '#186a3b',
	'nephritis-50': '#e9f7ef',
	'nephritis-100': '#d4efdf',
	'nephritis-200': '#a9dfbf',
	'nephritis-300': '#7dcea0',
	'nephritis-400': '#52be80',
	'nephritis-500': '#27ae60',
	'nephritis-600': '#229954',
	'nephritis-700': '#1e8449',
	'nephritis-800': '#196f3d',
	'nephritis-900': '#145a32',
	'peter-river-50': '#ebf5fb',
	'peter-river-100': '#d6eaf8',
	'peter-river-200': '#aed6f1',
	'peter-river-300': '#85c1e9',
	'peter-river-400': '#5dade2',
	'peter-river-500': '#3498db',
	'peter-river-600': '#2e86c1',
	'peter-river-700': '#2874a6',
	'peter-river-800': '#21618c',
	'peter-river-900': '#1b4f72',
	'belize-hole-50': '#eaf2f8',
	'belize-hole-100': '#d4e6f1',
	'belize-hole-200': '#a9cce3',
	'belize-hole-300': '#7fb3d5',
	'belize-hole-400': '#5499c7',
	'belize-hole-500': '#2980b9',
	'belize-hole-600': '#2471a3',
	'belize-hole-700': '#1f618d',
	'belize-hole-800': '#1a5276',
	'belize-hole-900': '#154360',
	'amethyst-50': '#f5eef8',
	'amethyst-100': '#ebdef0',
	'amethyst-200': '#d7bde2',
	'amethyst-300': '#c39bd3',
	'amethyst-400': '#af7ac5',
	'amethyst-500': '#9b59b6',
	'amethyst-600': '#884ea0',
	'amethyst-700': '#76448a',
	'amethyst-800': '#633974',
	'amethyst-900': '#512e5f',
	'wisteria-50': '#f4ecf7',
	'wisteria-100': '#e8daef',
	'wisteria-200': '#d2b4de',
	'wisteria-300': '#bb8fce',
	'wisteria-400': '#a569bd',
	'wisteria-500': '#8e44ad',
	'wisteria-600': '#7d3c98',
	'wisteria-700': '#6c3483',
	'wisteria-800': '#5b2c6f',
	'wisteria-900': '#4a235a',
	'wet-asphalt-50': '#ebedef',
	'wet-asphalt-100': '#d6dbdf',
	'wet-asphalt-200': '#aeb6bf',
	'wet-asphalt-300': '#85929e',
	'wet-asphalt-400': '#5d6d7e',
	'wet-asphalt-500': '#34495e',
	'wet-asphalt-600': '#2e4053',
	'wet-asphalt-700': '#283747',
	'wet-asphalt-800': '#212f3c',
	'wet-asphalt-900': '#1b2631',
	'midnight-blue-50': '#eaecee',
	'midnight-blue-100': '#d5d8dc',
	'midnight-blue-200': '#abb2b9',
	'midnight-blue-300': '#808b96',
	'midnight-blue-400': '#566573',
	'midnight-blue-500': '#2c3e50',
	'midnight-blue-600': '#273746',
	'midnight-blue-700': '#212f3d',
	'midnight-blue-800': '#1c2833',
	'midnight-blue-900': '#17202a',
	'sunflower-50': '#fef9e7',
	'sunflower-100': '#fcf3cf',
	'sunflower-200': '#f9e79f',
	'sunflower-300': '#f7dc6f',
	'sunflower-400': '#f4d03f',
	'sunflower-500': '#f1c40f',
	'sunflower-600': '#d4ac0d',
	'sunflower-700': '#b7950b',
	'sunflower-800': '#9a7d0a',
	'sunflower-900': '#7d6608',
	'orange-50': '#fef5e7',
	'orange-100': '#fdebd0',
	'orange-200': '#fad7a0',
	'orange-300': '#f8c471',
	'orange-400': '#f5b041',
	'orange-500': '#f39c12',
	'orange-600': '#d68910',
	'orange-700': '#b9770e',
	'orange-800': '#9c640c',
	'orange-900': '#7e5109',
	'carrot-50': '#fdf2e9',
	'carrot-100': '#fae5d3',
	'carrot-200': '#f5cba7',
	'carrot-300': '#f0b27a',
	'carrot-400': '#eb984e',
	'carrot-500': '#e67e22',
	'carrot-600': '#ca6f1e',
	'carrot-700': '#af601a',
	'carrot-800': '#935116',
	'carrot-900': '#784212',
	'pumpkin-50': '#fbeee6',
	'pumpkin-100': '#f6ddcc',
	'pumpkin-200': '#edbb99',
	'pumpkin-300': '#e59866',
	'pumpkin-400': '#dc7633',
	'pumpkin-500': '#d35400',
	'pumpkin-600': '#ba4a00',
	'pumpkin-700': '#a04000',
	'pumpkin-800': '#873600',
	'pumpkin-900': '#6e2c00',
	'alizarin-50': '#fdedec',
	'alizarin-100': '#fadbd8',
	'alizarin-200': '#f5b7b1',
	'alizarin-300': '#f1948a',
	'alizarin-400': '#ec7063',
	'alizarin-500': '#e74c3c',
	'alizarin-600': '#cb4335',
	'alizarin-700': '#b03a2e',
	'alizarin-800': '#943126',
	'alizarin-900': '#78281f',
	'pomegranate-50': '#f9ebea',
	'pomegranate-100': '#f2d7d5',
	'pomegranate-200': '#e6b0aa',
	'pomegranate-300': '#d98880',
	'pomegranate-400': '#cd6155',
	'pomegranate-500': '#c0392b',
	'pomegranate-600': '#a93226',
	'pomegranate-700': '#922b21',
	'pomegranate-800': '#7b241c',
	'pomegranate-900': '#641e16',
	'clouds-50': '#fdfefe',
	'clouds-100': '#fbfcfc',
	'clouds-200': '#f7f9f9',
	'clouds-300': '#f4f6f7',
	'clouds-400': '#f0f3f4',
	'clouds-500': '#ecf0f1',
	'clouds-600': '#d0d3d4',
	'clouds-700': '#b3b6b7',
	'clouds-800': '#979a9a',
	'clouds-900': '#7b7d7d',
	'silver-50': '#f8f9f9',
	'silver-100': '#f2f3f4',
	'silver-200': '#e5e7e9',
	'silver-300': '#d7dbdd',
	'silver-400': '#cacfd2',
	'silver-500': '#bdc3c7',
	'silver-600': '#a6acaf',
	'silver-700': '#909497',
	'silver-800': '#797d7f',
	'silver-900': '#626567',
	'concrete-50': '#f4f6f6',
	'concrete-100': '#eaeded',
	'concrete-200': '#d5dbdb',
	'concrete-300': '#bfc9ca',
	'concrete-400': '#aab7b8',
	'concrete-500': '#95a5a6',
	'concrete-600': '#839192',
	'concrete-700': '#717d7e',
	'concrete-800': '#5f6a6a',
	'concrete-900': '#4d5656',
	'asbestos-50': '#f2f4f4',
	'asbestos-100': '#e5e8e8',
	'asbestos-200': '#ccd1d1',
	'asbestos-300': '#b2babb',
	'asbestos-400': '#99a3a4',
	'asbestos-500': '#7f8c8d',
	'asbestos-600': '#707b7c',
	'asbestos-700': '#616a6b',
	'asbestos-800': '#515a5a',
	'asbestos-900': '#424949'
};

Y.namespace('M.atto_otfontcolor').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {
    initializer: function() {

        var items = [];
        Y.Array.each([50,100,200,300,400,500,600,700,800,900], function(colorindex){
            Y.Array.each(colornames, function(colorname) {
                items.push({
                    text: '<div'+
                            ' class="atto_otfontcolor_color ' + colorname+'-'+colorindex + '"' +
                            ' style="background-color: ' + colors[colorname+'-'+colorindex] +'"'+
                        '></div>',
                    callbackArgs: colors[colorname+'-'+colorindex],
                    callback: this._changeStyle
                });
            });
        });

        this.addToolbarMenu({
            icon: 'icon',
            iconComponent: 'atto_otfontcolor',
            overlayWidth: '4',
            globalItemConfig: {
                inlineFormat: true,
                callback: this._changeStyle
            },
            items: items
        });
    },

    /**
     * Change the font color to the specified color.
     *
     * @method _changeStyle
     * @param {EventFacade} e
     * @param {string} color The new font color
     * @private
     */
    _changeStyle: function(e, color) {
        this.get('host').formatSelectionInlineStyle({
            color: color
        });
    }
});
