function getMdlgradeitemName(element) {
	el = element.closest('.lessonedit-form').find('#id_mdlgradeitemid')
	if (el != undefined) {
		val = el.val()
		if (val != 0) {
			option = el.find('option[value="' + val + '"]')
			textarea = element.closest('.lessonedit-form').find('textarea[name="name"]')
			textarea.val(textarea.val() + option.text())
		}
	}
}
$(document).ready(function () {
	function switchFormElements() {
		// кнопка занятие/событие/кт
		var mode = $('.switch-lessontype-wrapper__button_active').data('mode');
		if(mode===undefined){
			var elem = $('.switch-lessontype-wrapper__button');
			elem.addClass('switch-lessontype-wrapper__button_active');
			mode = elem.data('mode');
		}

		// false - упрощенный, true - полный
		var currentCheckboxStatus = $('#lesson-switch').is(':checked');

		// вычисляем текущий код формы
		var code = 0;
		if(currentCheckboxStatus){
			code += 3;
		}
		if(mode=='event'){
			code += 1;
		} else if(mode=='plan'){
			code += 2;
		}
		$('div.lessonedit-form input[name=formtypecode]').val(code);

		// выставляем галку "создать событие", если текущий код формы предполагает наличие события
		var newState = false;
		if([0,1,3,4].indexOf(code) !== -1){
			newState = true;
			$('div.lessonedit-form input[name=create_event]').prop('checked', !newState);
		}else{
			newState = false;
			$('div.lessonedit-form input[name=create_event]').prop('checked', !newState);
		}
		$('div.lessonedit-form input[name=create_event]').click();

		// скрытие/отображение элементво в зависимости от параметров mode и currentCheckboxStatus
		$('div.lessonedit-form form div.fitem').filter(function(){
		    // фильтруем чтобы не скрывать дочерние с таким же классом (с версткой на 3.9 такое бывает)
		    return $(this).parents('.fitem').length == 0;
		}).addClass('dof-journal-hard-hide')
		$('div.lessonedit-form form div.fitem').each(function(i,val){
			if(!currentCheckboxStatus && !$(this).hasClass('dof-journal-is_simple')){
				return;
			}
			if(mode == 'lesson' && $(this).hasClass('dof-journal-ingore_lesson')){
				return;
			}
			if(mode == 'plan' || mode == 'event'){
				if (!$(this).hasClass('dof-journal-is_' + mode)){
					return;
				}
			}
			$(this).removeClass('dof-journal-hard-hide');
		})

		// обработка чекбокса события
		if (!$('div.lessonedit-form form input[name=create_event]').closest('.fitem').hasClass('dof-journal-hard-hide')){
			$('div.lessonedit-form form input[name=create_event]').closest('.fitem').addClass('dof-journal-hard-hide');
		}
		// обработка радио баттона создания контрольной точки
		if (!$('div.lessonedit-form form input[name=plan_creation_type][value=none]').closest('.fitem').hasClass('dof-journal-hard-hide')){
			$('div.lessonedit-form form input[name=plan_creation_type][value=none]').closest('.fitem').addClass('dof-journal-hard-hide');
		}
		if(mode=='plan' || mode=='lesson'){
			switchPlanProcessFormElements();
			$('div.lessonedit-form form input[name=plan_creation_type][value=create]').click();
		} else{
			$('div.lessonedit-form form input[name=plan_creation_type][value=none]').click();
		}

		// скрытие/отображение заголовков спойлеров
		$('div.lessonedit-form form > fieldset').each(function(i,v){
			if(!currentCheckboxStatus){
				$(this).removeClass('collapsed');
				$(this).find('legend').addClass('dof-journal-hard-hide');
			} else{
				$(this).find('legend').removeClass('dof-journal-hard-hide');
			}

			var fitems = $(v).find('.fitem').filter(function(){
                // фильтруем чтобы получить только основные fitem'ы без дочерних элементов
			    // (с версткой на 3.9 бывают дочерние с таким же классом)
                return $(this).parents('.fitem').length == 0;
            })
			if(fitems.not('.dof-journal-hard-hide').length > 0){
				$(this).removeClass('dof-journal-hard-hide')
			} else{
				$(this).addClass('dof-journal-hard-hide')
			}
		})
	}

	$('#lesson-switch').change(switchFormElements)

	$('.switch-lessontype-wrapper__button').click(function(){
		$('.switch-lessontype-wrapper__button').removeClass('switch-lessontype-wrapper__button_active')
		$(this).addClass('switch-lessontype-wrapper__button_active')
		switchFormElements()
	})
	switchFormElements();

	// контроль свитчера
	function fixSwitchButtonState(buttonSelector, inputSelector, firstButtonSelector, secondButtonSelector) {
		$(buttonSelector).removeClass(buttonSelector.slice(1) + '_active');
		if(!$(inputSelector).is(':checked')){
			$(firstButtonSelector).addClass(buttonSelector.slice(1) + '_active');
		}else{
			$(secondButtonSelector).addClass(buttonSelector.slice(1) + '_active');
		}
	}

	// переключатель простого/полнофункционального режимов
	$('#lesson-switch').click(function () {
		fixSwitchButtonState(
				'.switch-simplecomplex-wrapper__button',
				'#lesson-switch',
				'.switch-simplecomplex-wrapper__button-simple',
				'.switch-simplecomplex-wrapper__button-complex')
	});

	$('.switch-simplecomplex-wrapper__button').click(function () {
		var input = $('#lesson-switch');
		if($(this).hasClass('switch-simplecomplex-wrapper__button-simple')){
			input.prop('checked', false);
		}else{
			input.prop('checked', true);
		}
		switchFormElements();
		fixSwitchButtonState(
				'.switch-simplecomplex-wrapper__button',
				'#lesson-switch',
				'.switch-simplecomplex-wrapper__button-simple',
				'.switch-simplecomplex-wrapper__button-complex');
	});

	// постобработка элементов после клика на свитчер
	function switchPlanProcessFormElements() {
		if ( $('#switch-plan').length > 0 ){
			var mode = $('.switch-lessontype-wrapper__button_active').data('mode');
			if(mode===undefined){
				var elem = $('.switch-lessontype-wrapper__button');
				elem.addClass('switch-lessontype-wrapper__button_active');
				mode = elem.data('mode');
			}

			if(!$('#switch-plan').is(':checked')){
				$('label[for=id_plan_creation_type_select]').click();
			}else{
				$('label[for=id_plan_creation_type_create]').click();
			}
			// false - выбрать тему, true - создать тему
			if(!$('#switch-plan').is(':checked')){
				// отображаем только кнопку выбора темы
				$('div.fitem.dof-journal-is_plan').addClass('dof-journal-hard-hide');
				$('div#fitem_id_existing_point').removeClass('dof-journal-hard-hide');
				if ( mode == 'plan' ) {
					$('div#fitem_id_save_plan').removeClass('dof-journal-hard-hide');
				}
				$('div#fitem_id_cancel').removeClass('dof-journal-hard-hide');
			}else{
				// отображаем все кнопки кроме выбора темы
				$('div#fitem_id_existing_point').addClass('dof-journal-hard-hide');
			}
		}
	}
	// переключатель создания темы/выбора темы у КТ при создании
	if ( $('#switch-plan').length > 0 ){
		$('#switch-plan').click(function () {
			fixSwitchButtonState(
					'.switch-plan-wrapper__button',
					'#switch-plan',
					'.switch-plan-wrapper__button_left',
					'.switch-plan-wrapper__button_right');
			switchFormElements();
			switchPlanProcessFormElements();
		});

		$('.switch-plan-wrapper__button').click(function () {
			var input = $('#switch-plan');
			if($(this).hasClass('switch-plan-wrapper__button_left')){
				input.prop('checked', false);
			}else{
				input.prop('checked', true);
			}
			fixSwitchButtonState(
					'.switch-plan-wrapper__button',
					'#switch-plan',
					'.switch-plan-wrapper__button_left',
					'.switch-plan-wrapper__button_right');
			switchFormElements();
			switchPlanProcessFormElements();
		});
		if($('div.lessonedit-form form input[name=plan_creation_type]:checked').val() != 'none'){
			switchFormElements();
			switchPlanProcessFormElements();
		}
	}
})
