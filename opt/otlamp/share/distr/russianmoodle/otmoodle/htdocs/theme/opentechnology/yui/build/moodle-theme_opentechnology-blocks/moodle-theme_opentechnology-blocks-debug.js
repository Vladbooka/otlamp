YUI.add('moodle-theme_opentechnology-blocks', function (Y, NAME) {

/**
 * This file contains the drag and drop manager class.
 *
 * Provides drag and drop functionality for blocks.
 *
 * @module moodle-core-blockdraganddrop
 */

M.theme_ot = M.theme_opentechnology || {};
M.theme_ot.blocks = M.theme_ot.blocks || {};
M.theme_ot.blocks.dragdropcontrol = M.theme_ot.blocks.dragdropcontrol || {};

M.theme_ot.blocks.init = function(){
    Y.DD.DDM.on('ddm:start', this.dragdropcontrol.drag_started_early, this.dragdropcontrol);
    Y.DD.DDM.on('drop:over', this.dragdropcontrol.dragged_over_drop_target, this.dragdropcontrol);
    Y.DD.DDM.on('drop:hit', this.dragdropcontrol.dropped_and_hit, this.dragdropcontrol);
};
M.theme_ot.blocks.dragdropcontrol.__group = 'block';
M.theme_ot.blocks.dragdropcontrol.drag_started_early = function(){
    var groups = Y.DD.DDM.activeDrag.get('groups');
    if (!groups || Y.Array.indexOf(groups, this.__group) === -1) {
        return;
    }
    window.dispatchEvent(new CustomEvent('block_drag_started_early', {detail: {
        dragnode: Y.DD.DDM.activeDrag.get('node').getDOMNode()
    }}));
};
M.theme_ot.blocks.dragdropcontrol.dragged_over_drop_target = function(e){
    if (!e.drop || !e.drop.inGroup([this.__group])) {
        return;
    }
    window.dispatchEvent(new CustomEvent('block_dragged_over_drop_target', {detail: {
        dragnode: e.drag.get('node').getDOMNode(),
        dropnode: e.drop.get('node').getDOMNode()
    }}));
};
M.theme_ot.blocks.dragdropcontrol.dropped_and_hit = function(e){
    if (!e.drop || !e.drop.inGroup([this.__group])) {
        return;
    }
    window.dispatchEvent(new CustomEvent('block_dropped_and_hit', {detail: {
        dragnode: e.drag.get('node').getDOMNode(),
        dropnode: e.drop.get('node').getDOMNode()
    }}));
};

}, '@VERSION@');
