
require(['jquery'], function($) {
	
	class configratio {

		addControls() {
			var controller = this;
			
			var controlsWrap = $('<div>');
			this.standardRatio = [
				$('<div class="btn btn-primary configratiosetbtn" data-ratio="75">4x3</div>'),
				$('<div class="btn btn-primary configratiosetbtn" data-ratio="56">16x9</div>')
			];
			controlsWrap.append(this.standardRatio);
			this.input.after(controlsWrap);
			this.input.after(this.resizeWrap);
		}
		
		setValue(percent) {
			this.input.val(percent);
		}
		
		/**
		 * Получение лимита строк сетки
		 */
		initRatioBlock() {
			var controller = this;
			
			$.each(this.standardRatio, function() {
				$(this).on('click', function() {
					controller.setValue($(this).attr('data-ratio'));
			    });
			});
		}
		
		constructor(input) {
			this.input = input;
			
			this.addControls();
			this.initRatioBlock();
			
		}
	}
	
	$('input.configratio').each(function() {
		new configratio($(this));
	});
});