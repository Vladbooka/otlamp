/**
 * This is JavaScript code that handles drawing on mouse events and painting pre-existing drawings.
 * @package    qtype
 * @subpackage otimagepointer
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

YUI.add('moodle-qtype_otimagepointer-form', function(Y) {
	var CSS = {
	},
	SELECTORS = {
	    TOOLCLEAR: '.qtype_otimagepointer_clear',
	    TOOLPENCIL: '.qtype_otimagepointer_pencil',
	    TOOLARROW: '.qtype_otimagepointer_arrow',
	    TOOLUNDO: '.qtype_otimagepointer_undo',
	    TOOLREDO: '.qtype_otimagepointer_redo',
	    TOOLRECTANGLE: '.qtype_otimagepointer_rectangle',
	    TOOLERASER: '.qtype_otimagepointer_eraser',
	    TOOLCOLORPICKER: '.qtype_otimagepointer_colorpicker',
	    TOOLCOLORPICKERSELECTOR: '.qtype_otimagepointer_colorpicker_wrapper .form-colourpicker',
	    TOOLRADIUSSELECTOR: '.qtype_otimagepointer_radusselector',
	    TOOLBAR: '.qtype_otimagepointer_canvas_actions',
	    CANVAS: 'canvas[class="qtype_otimagepointer_canvas"]',
	};
	
	// Класс обработчика рисования
	var PROCESSOR = function() {
		PROCESSOR.superclass.constructor.apply(this, arguments);
    };
    Y.extend(PROCESSOR, Y.Base, {
    	
    	// Параметры обработчика
    	questionID: null,
    	drawingRadius: 4,
    	canvasInstanceID: null,
    	emptyCanvasDataURL: '',
    	color: '#000',
    	
    	// Базовые данные блоков
    	wrapper: null,
		image: null,
		canvas: null,
		textarea: null,
		toolClear: null,
		toolPencil: null,
		toolArrow: null,
		toolUndo: null,
		toolRedo: null,
		toolRectangle: null,
		toolEraser: null,
		toolColorPicker: null,
		toolRadiusSelector: null,
		
		// Состояние элементов
		canvasLastState: null,
		stateActiveTool: null,
		stateActiveToolType: null,
		stateActiveToolPrev: null,
		drawing: {
			'status': null
		},
		
		// Учет состояния перехватчиков событий
		canvasMousedownSub: null,
		canvasMouseupSub: null,
		canvasTouchstartSub: null,
		canvasTouchmoveSub: null,
		canvasTouchendSub: null,
		canvasMouseoutSub: null,
		canvasToolClearSub: null,
		canvasToolPencilSub: null,
		canvasToolArrowSub: null,
		canvasToolUndoSub: null,
		canvasToolRedoSub: null,
		canvasToolRectangleSub: null,
		canvasToolEraserSub: null,
		canvasToolColorPickerSub: null,
		canvasToolRadiusSelectorSub: null,
		
		/**
		 * Конструктор обработчика
		 */
        initializer : function(params) {
        	// Определение параметров
        	this.questionID = params.questionID;
        	this.canvasInstanceID = params.canvasInstanceID;
        	
        	// Базовая инициализация блока рисования
			this.wrapper = Y.one('#image_editing_' + this.questionID);
			this.canvas = Y.one('#qtype_otimagepointer_canvas_' + this.questionID);
			this.image = Y.one('#qtype_otimagepointer_baseimage_' + this.questionID);
			this.textarea = Y.one('#qtype_otimagepointer_textarea_id_' + this.questionID);
			
			// Синхронизация механизма рисования по загрузке изображения
			if ( this.image ) {
				this.image.getDOMNode().addEventListener('load', function() {
					this.initDrawZone();
				}.bind(this));
				this.initDrawZone();
			}
        },
    
        /**
         * Инициализация механизма рисования
         */
        initDrawZone: function() {
        	// Синхронизация размеров холста
			this.canvasImageSync();
			
			this.emptyCanvasDataURL = this.canvas.getDOMNode().toDataURL();
			this.createCanvasContext();
			
			// Инициализация инструментов
        	this.initToolbar('pencil');
        	
			// Обработка событий
			if ( ! this.canvasMousedownSub ) { 
				this.canvasMousedownSub = Y.delegate('mousedown', this.canvasMousedown,  this.canvas, SELECTORS.CANVAS, this); 
			}
			if ( ! this.canvasMouseupSub ) { 
				this.canvasMouseupSub =  Y.delegate('mouseup', this.canvasMouseup, this.canvas, SELECTORS.CANVAS, this); 
			}
			if ( ! this.canvasTouchstartSub ) { 
				this.canvasTouchstartSub = Y.delegate('touchstart', this.canvasTouchstart, this.canvas, SELECTORS.CANVAS, this); 
			}
			if ( ! this.canvasTouchmoveSub ) { 
				this.canvasTouchmoveSub = Y.delegate('touchmove', this.canvasTouchmove, this.canvas, SELECTORS.CANVAS, this); 
			}
			if ( ! this.canvasTouchendSub ) { 
				this.canvasTouchendSub = Y.delegate('touchend', this.canvasTouchend, this.canvas, SELECTORS.CANVAS, this); 
			}
			if ( ! this.canvasMouseoutSub ) { 
				this.canvasMouseoutSub = Y.delegate('mouseout',   this.canvasMouseout, this.canvas, SELECTORS.CANVAS, this); 
    		}
			if( ! this.canvasToolClearSub) { 
			    this.canvasToolClearSub = Y.delegate('click', this.canvasToolClearClick, this.wrapper, SELECTORS.TOOLCLEAR, this);
			}
			if( ! this.canvasToolPencilSub) { 
			    this.canvasToolPencilSub = Y.delegate('click', this.canvasToolPencilClick, this.wrapper, SELECTORS.TOOLPENCIL, this);
			}
			if( ! this.canvasToolArrowSub) { 
			    this.canvasToolArrowSub = Y.delegate('click', this.canvasToolArrowClick, this.wrapper, SELECTORS.TOOLARROW, this);
			}
			if( ! this.canvasToolUndoSub) { 
			    this.canvasToolUndoSub = Y.delegate('click', this.canvasToolUndoClick, this.wrapper, SELECTORS.TOOLUNDO, this);
			}
			if( ! this.canvasToolRedoSub) { 
			    this.canvasToolRedoSub = Y.delegate('click', this.canvasToolRedoClick, this.wrapper, SELECTORS.TOOLREDO, this);
			}
			if( ! this.canvasToolRectangleSub) { 
			    this.canvasToolRectangleSub = Y.delegate('click', this.canvasToolRectangleClick, this.wrapper, SELECTORS.TOOLRECTANGLE, this);
			}
			if ( ! this.canvasToolEraserSub ) {
				this.canvasToolEraserSub =  Y.delegate('click', this.canvasToolEraserClick, this.wrapper, SELECTORS.TOOLERASER, this);
            }
			if ( ! this.canvasToolColorPickerSub ) { 
			    this.canvasToolColorPickerSub = Y.delegate('click', this.canvasToolColorPickerClick, this.wrapper, SELECTORS.TOOLCOLORPICKER, this);
			}
			if ( ! this.canvasToolRadiusSelectorSub ) { 
			    this.canvasToolRadiusSelectorSub = Y.delegate('click', this.canvasToolRadiusSelectorClick, this.wrapper, SELECTORS.TOOLRADIUSSELECTOR, this);
			}
        },
        
        createCanvasContext: function(applyTextArea) {
    		if ( typeof applyTextArea == 'undefined' ) {
    			applyTextArea = true;
    		}
    		questionID = this.questionID;
    		canvasNode = this.canvas;
    		
    		this.canvasContext = canvasNode.getDOMNode().getContext('2d');
    		this.canvasContext.lineWidth = this.drawingRadius;
    		this.canvasContext.lineJoin = 'round';
    		this.canvasContext.lineCap = 'round';
    		this.canvasContext.strokeStyle = this.color;
    		this.canvasContext.fillStyle = this.color;
            this.canvasContext.globalCompositeOperation = 'source-over';
            
            canvaswidth = canvasNode.getDOMNode().clientWidth;
            canvasheight = canvasNode.getDOMNode().clientHeight;
            
    		if ( this.textarea != null ) {
    			if (applyTextArea == false) {
    				this.textarea.set('value', '');
    			} else {
    				if ( this.textarea.get('value') != '' ) {
    					var img = new Image();
    					img.onload = function() {
    						this.canvasContext.drawImage(img, 0, 0, this.canvas.getDOMNode().clientWidth, this.canvas.getDOMNode().clientHeight);
    					}.bind(this);
    					img.src = this.textarea.get('value');
    				}
    			}
    		}
    	},
    	initToolbar: function(defaultTool) {
    		this.toolClear = this.wrapper.one(SELECTORS.TOOLCLEAR);
    		this.toolPencil = this.wrapper.one(SELECTORS.TOOLPENCIL);
    		this.toolArrow = this.wrapper.one(SELECTORS.TOOLARROW);
    		this.toolUndo = this.wrapper.one(SELECTORS.TOOLUNDO);
    		this.toolRedo = this.wrapper.one(SELECTORS.TOOLREDO);
    		this.toolRectangle = this.wrapper.one(SELECTORS.TOOLRECTANGLE);
    		this.toolEraser = this.wrapper.one(SELECTORS.TOOLERASER);
    		this.toolColorPicker = this.wrapper.one(SELECTORS.TOOLCOLORPICKER);
    		this.toolRadiusSelector = this.wrapper.one(SELECTORS.TOOLRADIUSSELECTOR);
    		
    		var toolColorPickerSelector = this.wrapper.one(SELECTORS.TOOLCOLORPICKERSELECTOR);
    		var toolColorPickerValue = Y.one('#qtype_otimagepointer_colorpicker_' + this.questionID);
    		toolColorPickerSelector.on('click', function() {
    			this.color = toolColorPickerValue.get('value');
    			this.toolColorPicker.setStyles({ background: this.color});
    			this.canvasContext.strokeStyle = this.color;
    			this.canvasContext.fillStyle = this.color;
    		}.bind(this));
    		this.color = toolColorPickerValue.get('value');
			this.toolColorPicker.setStyles({ background: this.color});
			this.canvasContext.strokeStyle = this.color;
			this.canvasContext.fillStyle = this.color;
			
    		var toolRadiusSelectorValue = Y.one('#qtype_otimagepointer_radiusselector_' + this.questionID);
    		toolRadiusSelectorValue.on('valuechange', function(e) {
    			this.drawingRadius = e.newVal;
    			this.canvasContext.lineWidth = this.drawingRadius;
    			this.toolRadiusSelector.setHTML(this.drawingRadius);
    		}.bind(this));
    		toolRadiusSelectorValue.on('click', function(e) {
    			this.drawingRadius = toolRadiusSelectorValue.get('value');
    			this.canvasContext.lineWidth = this.drawingRadius;
    			this.toolRadiusSelector.setHTML(this.drawingRadius);
    		}.bind(this));
    		this.drawingRadius = toolRadiusSelectorValue.get('value');
    		this.canvasContext.lineWidth = this.drawingRadius;
    		this.toolRadiusSelector.setHTML(this.drawingRadius);
    		
    		this.setActiveTool(defaultTool);
    	},
    	setActiveTool: function(activeTool) {
    		if ( ! activeTool ) {
    			activeTool = 'pencil';
    		}
    		
    		var ACTIVETOOL = 'TOOL' + activeTool.toUpperCase();

    		
    		if(this.wrapper.one(SELECTORS[ACTIVETOOL]).hasClass('tool_dropdown'))
    		{
		    	if( ! this.wrapper.one(SELECTORS[ACTIVETOOL]).hasClass('active') )
	    		{
		    		this.closeDropdowns();
    	    		this.wrapper.one(SELECTORS[ACTIVETOOL]).addClass('active');
	    		}
		    	else
	    		{
		    		this.closeDropdowns();
	    		}
			}
    		else if(this.wrapper.one(SELECTORS[ACTIVETOOL]).hasClass('tool_drawing'))
			{
        		this.closeDropdowns();
        		this.stateActiveToolPrev = this.stateActiveTool;
        		this.stateActiveTool = activeTool;
        		
		    	this.wrapper.all(SELECTORS.TOOLBAR + ' .tool_drawing').removeClass('active');
	    		this.wrapper.one(SELECTORS[ACTIVETOOL]).addClass('active');
	    		
	    		src = this.wrapper.one(SELECTORS[ACTIVETOOL]).getAttribute('src');
	    		this.canvas.setStyles({ cursor: "url('" + src + "_cursor') 4 30, default", });
	    		
	    		switch( ACTIVETOOL ) {
				    case 'TOOLPENCIL' :
				    	this.stateActiveToolType = 'path';
				    	this.canvasContext.globalCompositeOperation = 'source-over';
				        break;
				    case 'TOOLARROW' :
				    	this.stateActiveToolType = 'object';
				    	this.canvasContext.globalCompositeOperation = 'source-over';
				        break;
				    case 'TOOLRECTANGLE' :
				    	this.stateActiveToolType = 'object';
				    	this.canvasContext.globalCompositeOperation = 'source-over';
				        break;
	    		    case 'TOOLERASER' :
				    	this.stateActiveToolType = 'path';
	    		    	this.canvasContext.globalCompositeOperation = 'destination-out';
	    		        break;
	    		}
			}
    		else
			{
	    		this.closeDropdowns();
			}
    	},
    	closeDropdowns: function(){
    		this.wrapper.all(SELECTORS.TOOLBAR + ' .tool_dropdown.active').removeClass('active');
    	},
    	prevActiveTool: function() {
    		if ( this.stateActiveToolPrev ) {
    			this.setActiveTool(this.stateActiveToolPrev);
    		} else {
    			this.setActiveTool('pencil');
    		}
    	},
    	canvasToolClearClick: function(e) {
    		if ( this.canvasIsEmpty() == false ) {
    			this.setActiveTool('clear');
    			if ( confirm(M.util.get_string('erase_confirm', 'qtype_otimagepointer')) == true ) {
    				this.canvasSaveState();
    				this.canvasClear();
    				this.drawing.status = 'clearing';
    				this.prevActiveTool();
    			}
    		}
    	},
    	canvasToolPencilClick: function(e) {
    		this.setActiveTool('pencil');
    	},
    	canvasToolArrowClick: function(e) {
    		this.setActiveTool('arrow');
    	},
    	canvasToolUndoClick: function(e) {
    		this.canvasUndo();
    	},
    	canvasToolRedoClick: function(e) {
    		this.canvasRedo();
    	},
    	canvasToolRectangleClick: function(e) {
    		this.setActiveTool('rectangle');
    	},
        canvasToolEraserClick: function(e) {
        	this.setActiveTool('eraser');
    	},
    	canvasToolColorPickerClick: function(e) {
        	this.setActiveTool('colorpicker');
    	},
    	canvasToolRadiusSelectorClick: function(e) {
        	this.setActiveTool('radiusselector');
    	},
    	canvasMousedown: function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.wrapper.all(SELECTORS.TOOLBAR + ' .tool_static').removeClass('active');
            
    		if( ! this.isOutOfField(e) )
    		{
    			this.canvasSaveState();
				var startOffset = e.currentTarget.getXY();
				var startX = e.pageX - startOffset[0];
				var startY = e.pageY - startOffset[1];
    			this.drawing = {
					'startData': e,
					'actualX': startX,
					'actualY': startY,
					'restoreTimeout': null,
					'status': 'drawing'
	    		};
    			Y.on('mousemove', this.canvasMousemove, e.currentTarget, this);
    		}
    	},
    	canvasMousemove: function(e) {
    		if( ! this.isOutOfField(e) )
    		{
        		var offset = e.currentTarget.getXY();
    			this.drawing.actualX = e.pageX - offset[0];
    			this.drawing.actualY = e.pageY - offset[1];
        		if(this.stateActiveToolType=='object')
    			{
        			if(this.stateActiveTool == 'arrow')
    				{
	        			if ( this.drawing.restoreTimeout == null )
	        			{
	        				var _this = this;
	        				this.drawing.restoreTimeout = setTimeout(function(){
	        					_this.canvasRestoreLastState(_this.canvasDrawArrow);
	        				},20);
	        			}
    				}
        			if(this.stateActiveTool == 'rectangle')
    				{
	        			if ( this.drawing.restoreTimeout == null )
	        			{
	        				var _this = this;
	        				this.drawing.restoreTimeout = setTimeout(function(){
	        					_this.canvasRestoreLastState(_this.canvasDrawRectangle);
	        				},20);
	        			}
    				}
    			}
        		else if (this.stateActiveToolType=='path')
    			{
    				var startOffset = this.drawing.startData.currentTarget.getXY();
    				var startX = this.drawing.startData.pageX - startOffset[0];
    				var startY = this.drawing.startData.pageY - startOffset[1];
        			this.canvasDrawLine(this.canvasContext, startX, startY, this.drawing.actualX, this.drawing.actualY);
        			this.drawing.startData = e;
    			}
    		}
    	},
    	canvasMouseup: function(e) {
    		e.currentTarget.detach('mousemove', this.canvasMousemove);
    		if(this.drawing.status != null)
			{
    			this.drawing.status=null;
        		this.canvasMousemove(e);
        		this.textarea.set('value', this.canvas.getDOMNode().toDataURL());
			}
    	},
    	canvasTouchstart: function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.canvasMousedown(e);
    	},
    	canvasTouchmove: function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.canvasMousemove(e);
    	},
    	canvasTouchend: function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.canvasMouseup(e);
    	},
    	canvasMouseout: function(e) {
    		this.canvasMouseup(e);
    	},
    	isOutOfField: function(e){
    		var offset = e.currentTarget.getXY();
    		if (e.pageX - offset[0] < 0 || e.pageY - offset[1] < 0 || e.pageX - offset[0] > e.currentTarget.getDOMNode().width || e.pageY - offset[1] > e.currentTarget.getDOMNode().height) {
    			this.canvasMouseup(e);
    			return true;
    		}
    		return false;
    	},
    	canvasIsEmpty: function() {
    		if (this.emptyCanvasDataURL != 0 && this.canvas.getDOMNode().toDataURL() != this.emptyCanvasDataURL) {
    			return false;
    		}
    		return true;
    	},
        canvasImageSync: function() {
    		image = this.image.getDOMNode();
    		canvas = this.canvas.getDOMNode();
    		canvas.width  = image.clientWidth;
    		canvas.height = image.clientHeight;
    	},
    	canvasRestoreLastState: function(callback, ctx) {
			if( this.drawing.restoreTimeout != null )
			{
				clearTimeout(this.drawing.restoreTimeout);
				this.drawing.restoreTimeout = null;
			}
			this.canvasLoadState(false, callback);
    	},
    	canvasDrawDot: function(ctx, x, y){
			ctx.beginPath();
    		ctx.moveTo(x, y);
			ctx.arc(x, y, ctx.lineWidth/40, 0, 2 * Math.PI, false);
			ctx.fill();
			ctx.closePath();
			//ctx.stroke();
    	},
    	canvasDrawLine: function(ctx, fromX, fromY, toX, toY){
			ctx.beginPath();
    		ctx.moveTo(fromX, fromY);
			ctx.lineTo(toX, toY);
			ctx.stroke();
			ctx.closePath();
    	},
    	canvasDrawArrow: function(ctx, fromX, fromY, toX, toY){

			ctx.beginPath();
		    ctx.moveTo(fromX, fromY);
			ctx.lineTo(toX, toY);
			ctx.stroke();
			ctx.closePath();

			ctx.beginPath();
			var headlen = ctx.lineWidth*3;   // length of head in pixels
		    var angle = Math.atan2(toY-fromY,toX-fromX);
			var arrowTipX = toX+headlen*Math.cos(angle);
			var arrowTipY = toY+headlen*Math.sin(angle);
			ctx.moveTo(arrowTipX,arrowTipY);
			ctx.lineTo(toX-headlen*Math.cos(angle-Math.PI/3)/2,toY-headlen*Math.sin(angle-Math.PI/3)/2);
			ctx.lineTo(toX,toY);
			ctx.lineTo(toX-headlen*Math.cos(angle+Math.PI/3)/2,toY-headlen*Math.sin(angle+Math.PI/3)/2);
			ctx.lineTo(arrowTipX,arrowTipY);
            ctx.fill();
			ctx.closePath();
    	},
    	canvasDrawRectangle: function(ctx, fromX, fromY, toX, toY){
			ctx.beginPath();
		    ctx.rect(fromX, fromY, toX-fromX, toY-fromY);
			ctx.stroke();
    	},
        canvasRedoList: [],
        canvasUndoList: [],
        canvasSaveState : function(list, keep_redo) {
			keep_redo = keep_redo || false;
			if (!keep_redo) {
				this.canvasRedoList = [];
			}
		
			(list || this.canvasUndoList).push(this.canvas.getDOMNode().toDataURL());
			

			this.wrapper.all(SELECTORS.TOOLBAR + ' .tool_history').removeClass('active');
			
			if(this.canvasUndoList.length)
			{
	    		this.wrapper.one(SELECTORS['TOOLUNDO']).addClass('active');
			}
			if(this.canvasRedoList.length)
			{
	    		this.wrapper.one(SELECTORS['TOOLREDO']).addClass('active');
			}
		},
		canvasUndo : function() {
			this.canvasRestoreState(this.canvasUndoList, this.canvasRedoList);
		},
		canvasRedo : function() {
			this.canvasRestoreState(this.canvasRedoList, this.canvasUndoList);
		},
		canvasRestoreState : function(pop, push, callback) {
			if (pop.length) 
			{
				this.canvasSaveState(push, true);
				this.canvasLoadState(pop.pop(), callback);
			}
			
			this.wrapper.all(SELECTORS.TOOLBAR + ' .tool_history').removeClass('active');
			
			if(this.canvasUndoList.length)
			{
	    		this.wrapper.one(SELECTORS['TOOLUNDO']).addClass('active');
			}
			if(this.canvasRedoList.length)
			{
	    		this.wrapper.one(SELECTORS['TOOLREDO']).addClass('active');
			}
		},
		canvasLoadState: function(state, callback){
			if(state === undefined || state==false)
			{
				state = this.canvasUndoList[this.canvasUndoList.length-1];
			}

			var img = new Image();
			img.onload = function() {
				this.canvasClear();
				var lastCompositeOperation = this.canvasContext.globalCompositeOperation;
		    	this.canvasContext.globalCompositeOperation = 'source-over';
				this.canvasContext.drawImage(img, 0, 0);
				this.canvasContext.globalCompositeOperation = lastCompositeOperation;
				if( callback !== undefined && this.drawing.startData !== undefined )
				{
		    		if( !this.isOutOfField(this.drawing.startData) )
					{
						var startOffset = this.drawing.startData.currentTarget.getXY();
						var startX = this.drawing.startData.pageX - startOffset[0];
						var startY = this.drawing.startData.pageY - startOffset[1];
						var ctx = this.canvasContext;
						var curX = this.drawing.actualX;
						var curY = this.drawing.actualY;
		    			callback(ctx, startX, startY, curX, curY);
					}
				}
			}.bind(this);
			img.src = state;
		},
		canvasClear: function(){
			this.canvasContext.clearRect(0, 0, this.canvas.getDOMNode().width, this.canvas.getDOMNode().height);
		}
    });
	
	Y.namespace('Moodle.qtype_otimagepointer.form');

	/**
	 * Инициализация обработчика для блока ризования
	 */
	Y.Moodle.qtype_otimagepointer.initializer = {
		
		// Инициализированные обработчики	
		instances: [],
		
		init: function(questionID, canvasInstanceID) {
			
			// Базовая инициализация объекта поддержки блока рисования
			var wrapper = Y.one('#image_editing_' + questionID);
			var image = Y.one('#qtype_otimagepointer_baseimage_' + questionID);
			
		    if ( wrapper && image ) {
		    	// Инициализация обработчика
				this.instances[questionID] = new PROCESSOR({
					'questionID' : questionID,
					'canvasInstanceID' : canvasInstanceID
				});
		    }
		}
	};
}, '@VERSION@', {requires: ['node', 'event', 'event-valuechange', 'querystring'] });