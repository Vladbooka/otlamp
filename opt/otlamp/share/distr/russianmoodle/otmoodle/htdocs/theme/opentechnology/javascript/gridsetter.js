
require(['jquery'], function($) {
	
	class gridsetter {

		/**
		 * Установка размерности сетки
		 */
		setGridlength(gridlength) {
			this.gridlength = parseInt(gridlength);
		}
		
		/**
		 * Получение размерности сетки
		 */
		getGridlength() {
			return this.gridlength;
		}
		
		/**
		 * Установка лимита строк сетки
		 */
		setRowsLimit(rowslimit) {
			this.rowslimit = parseInt(rowslimit);
		}
		
		/**
		 * Получение лимита строк сетки
		 */
		getRowsLimit() {
			return this.rowslimit;
		}
		
		/**
		 * Получение лимита строк сетки
		 */
		getData() {
			return this.input.val();
		}
		
		/**
		 * Получить последнюю строку
		 */
		getLastRow() {
			if ( this.countRows() ) {
				return this.getRow(this.countRows());
			}
			return null;
		}
		
		/**
		 * Получить последнюю строку
		 */
		getRow(num) {
			if ( this.rows[num - 1] !== void 0 ) {
				return this.rows[num - 1];
			}
			return null;
		}
		
		/**
		 * Получить последнюю строку
		 */
		countRows() {
			return this.rows.length;
		}
		
		/**
		 * Проверка возможности увеличивать длину ячейки
		 */
		canIncreaseCell(cell) {
			var gridlength = this.getGridlength();
			var currentLength = 0;
			cell.parent().find('.gridsetter_cell').each(function() {
				var length = $(this).attr('data-length');
				currentLength = +length + currentLength;
			});
			if ( currentLength >= gridlength ) {
				return false;
			}
			return true;
		}
		
		/**
		 * Проверка возможности увеличивать длину ячейки
		 */
		canAddCell(row) {
			var gridlength = this.getGridlength();
			var currentLength = 0;
			row.find('.gridsetter_cell').each(function() {
				var length = $(this).attr('data-length');
				currentLength = +length + currentLength;
			});
			if ( currentLength >= gridlength ) {
				return false;
			}
			return true;
		}
		
		/**
		 * Проверка возможности уменьшать длину ячейки
		 */
		canDecreaseCell(cell) {
			if ( cell.attr('data-length') <= 1) {
				return false;
			}
			return true;
		}
		
		increaseCell(cell) {
			var gridlength = this.getGridlength();
			if ( this.canIncreaseCell(cell) ) {
				var length = cell.attr('data-length');
				cell.attr('data-length', +length + 1);
				cell.css('width', ((+length + 1) * 100 / gridlength) + '%');
			}
		}
		
		decreaseCell(cell) {
			var gridlength = this.getGridlength();
			if ( this.canDecreaseCell(cell) ) {
				var length = cell.attr('data-length');
				cell.attr('data-length', +length - 1);
				cell.css('width', ((+length - 1) * 100 / gridlength) + '%');
			}
		}
		
		removeCell(cell) {
			cell.remove()
		}
		
		addCell(row, length = 1) {
			var controller = this;
			var gridlength = this.getGridlength();
			
			if ( this.canAddCell(row) ) {
				// Добавление ячейки
				var cell = $('<div class="gridsetter_cell" data-length="' + length +'" ></div>');
				row.append(cell);
				cell.css('width', (length * 100 / gridlength) + '%');
				
				// Добавление кнопок
				var increaseBtn = $('<div class="btn btn-primary gridsetter_cell_increase">+</div>');
				var decreaseBtn = $('<div class="btn btn-primary gridsetter_cell_decrease">-</div>');
				var removeBtn = $('<div class="btn btn-primary gridsetter_cell_remove">x</div>');
				cell.append(increaseBtn);
				cell.append(decreaseBtn);
				cell.append(removeBtn);
				
				increaseBtn.on('click', function() {
					controller.increaseCell($(this).parent());
					controller.saveState();
			    });
				decreaseBtn.on('click', function() {
					controller.decreaseCell($(this).parent());
					controller.saveState();
				});
				removeBtn.on('click', function() {
					controller.removeCell($(this).parent());
					controller.saveState();
				});
			}
	    }

		/**
		 * Добавить строку
		 */
		addRow(cells) {
			
			var controller = this;
			
			var lastRow = this.getLastRow();
			var gridlength = this.getGridlength();
			
			if ( this.getRowsLimit() > 0 && this.getRowsLimit() <= this.countRows() ) {
				return;
			}
			
			var row = $('<div class="gridsetter_row gridsetter_row' + (this.countRows() + 1) + '"></div>');
			if ( lastRow == null ) {
				this.input.after(row);
			} else {
				lastRow.after(row);
			}
			this.rows.push(row);
			
			$.each(cells, function(index, value) {
				controller.addCell(row, value);
			});
			
			var addCellBtn = $('<div class="btn btn-primary gridsetter_row_add">+</div>');
			row.append(addCellBtn);
			addCellBtn.on('click', function() {
				controller.addCell($(this).parent());
				controller.saveState();
			});
		}
		
		
		
		
		
		
		
		/**
		 * Добавить строку
		 */
		removeRow() {
			var lastRow = this.getLastRow();
			if ( lastRow == null ) {
				return;
			} else {
				lastRow.remove();
			}
			this.rows.pop();
		}
		
		saveState() {
			var countRows = this.countRows();
			var gridlength = this.getGridlength();
			
			if ( countRows < 1 ) {
				this.removeRowBtn.addClass('disabled');
			} else {
				this.removeRowBtn.removeClass('disabled');
			}
			
			if ( this.getRowsLimit() > 0 && this.getRowsLimit() <= countRows ) {
				this.addRowBtn.addClass('disabled');
			} else {
				this.addRowBtn.removeClass('disabled');
			}
			
			var data = [];
			this.input.siblings('.gridsetter_row').each(function() {
				var row = [];
				$(this).find('.gridsetter_cell').each(function() {
					var length = parseInt($(this).attr('data-length'));
					row.push(length);
				});
				data.push(row);
			});
			this.input.attr('value',JSON.stringify(data));
		}
		
		/**
		 * Получение лимита строк сетки
		 */
		initGrid() {
			var gridData = this.getData();
			var controller = this;
			
			this.addRowBtn =  $('<div class="btn btn-primary gridsetter_addrow">+</div>');
			this.removeRowBtn =  $('<div class="btn btn-primary gridsetter_removerow">-</div>');
			var controls = $('<div class="gridsetter_controls"></div>');
			controls.append(this.addRowBtn);
			controls.append(this.removeRowBtn);
			this.input.after(controls);
			
			this.addRowBtn.on('click', function() {
				controller.addRow();
				controller.saveState();
		    });
			this.removeRowBtn.on('click', function() {
				controller.removeRow();
				controller.saveState();
			});
			
			if ( gridData ) {
				gridData = JSON.parse(gridData);
				$.each(gridData, function(index, value) {
					
					// Добавление строки
					controller.addRow(value);
				});
			}
		}
		
		constructor(input) {
			this.input = input;
			
			this.rows = [];
			
			// Установка опций
			this.setRowsLimit(this.input.data('rowslimit'));
			this.setGridlength(this.input.data('gridlength'));
			
			this.initGrid();
			
			// Скрытие поля с данными
			this.input.hide();
		}
	}
	
	$('input.gridsetter').each(function() {
		new gridsetter($(this));
	});
});