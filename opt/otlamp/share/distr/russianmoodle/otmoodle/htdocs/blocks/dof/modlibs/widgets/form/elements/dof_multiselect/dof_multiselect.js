/**
 * OTselect - мультиселект
 * @package    formslib
 * @subpackage dof_multiselect
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(['jquery', 'jqueryui'], function($) {

	$.OTselect = function(element, options) {
		var plugin = this,
			$element = $(element),
			$options = $element.children('option'),
			$container = $('<div class="OTselect-container"></div>'),
			$field_wrapper = $('<div class="OTselect-field-wrapper"></div>'),
			$field = $('<input class="OTselect-field form-control" type="text">'),
			$box = $('<div class="OTselect-box"></div>'),
			$list = $('<ul class="OTselect-list"></ul>'),
			$list_wrapper = $('<div class="OTselect-list-wrapper"></div>'),

			defaults = {
				// Статус заморозки элемента
				isFrozen: false,
				// Плейсхолдер
				placeholder: ''
			},

			list = {
				// Очищаем список
				clear: function() {
					list.items.active = 0;
					list.items.collection = null;
					$list.children('li').removeClass('active filtered');
				},

				// Фильтруем список
				filter: function(pattern) {
					var _first = 0,
						_count = 0;
					$list.children('li').removeClass('active').each(function(index) {
						if ($(this).text().toUpperCase().indexOf(pattern.toUpperCase()) != -1)
						{
							$(this).addClass('filtered');
							if (_first === 0)
							{
								$(this).addClass('active');
								_first++;
							}
							_count++;
						}
					});
				},

				// Генерируем список согласно нашему селекту
				generate: function(refresh) {
					var _optionList = '',
						_boxList = '';

					if (refresh) {
						$options = $element.children('option');
					}

					$options.each(function() {
						if ($(this).is(':selected'))
						{
							_boxList += '<a href="" class="OTselect-box-item" data-value="' + $(this).val() + '">' + $(this).text() + '<span>&nbsp;&#x2716;</span>' + '</a>';
						}
						else
						{
							_optionList += '<li data-value="' + $(this).val() + '">' + $(this).text() + '</li>';
						}
					});

					if (_boxList) {
						$box.removeClass('empty');
					}
					else
					{
						$box.addClass('empty');
					}

					// Биндим ивенты для элементов бокса
					$box.html(_boxList);
					$box.children('.OTselect-box-item').on('click', box.removeItem);

					// Биндим ивенты для элементов листа
					$list.html(_optionList);
					$list.children('li').on('click', list.items.onClick).on('mouseenter', list.items.onHover);
				},

				// Итемы листа
				items: {
					active: 0,
					collection: 0,
					count: 0,

					get: function() {
						list.items.collection = $list.children('.filtered');
						list.items.count = list.items.collection.length;
					},

					set: function() {
						list.items.collection.removeClass('active');
						list.items.collection.eq(list.items.active).addClass('active');
					},

					move: function(down) {
						if (!list.items.collection)
						{
							list.items.get();
						}
						if (down)
						{
							if (list.items.active < list.items.count - 1)
							{
								list.items.active++;
							}
							else
							{
								list.items.active = 0;
							}
						}
						else
						{
							if (list.items.active > 0)
							{
								list.items.active--;
							}
							else
							{
								list.items.active = list.items.count - 1;
							}
						}
						list.items.set();
					},

					onClick: function() {
						box.addItem($(this).attr('data-value'));
					},

					onHover: function() {
						list.items.get();
						list.items.active = $(this).index('.filtered');
						list.items.set();
					}
				}
			},

			// Бокс
			box = {
				// Добавляем в бокс элемент и selected аттрибут в селект
				addItem: function(value) {
					if (!plugin.settings.isFrozen)
					{
						if ($.prop)
						{
							$options.filter('[value=' + value + ']').prop('selected', true);
							$options.filter('[value=' + value + ']').attr('selected', 'selected');
						}
						$field.val('');
						list.generate();
					}
				},

				// Удаляем аттрибут selected и удаляем элемента из бокса
				removeItem: function(e) {
					e.preventDefault();
					if (!plugin.settings.isFrozen)
					{
						$options.filter('[value=' + $(this).attr('data-value') + ']').prop("selected", false);//.removeAttr('selected');
						window.console.log($(this), list);
						$(this).remove();
						list.generate();
					}
				}
			},

			// Инпут поле
			field = {
				onKeyup: function(e) {
					list.clear();
					if ($(this).val()) {
						list.filter($(this).val());
					}
				},

				onFocus: function () {
					$list.children('li').addClass('filtered');
				},

				onBlur: function () {
					$field.blur();
					plugin.refresh();
				},

				checkField: function () {
					if (plugin.settings.isFrozen)
					{
						$field.attr('disabled', true);
					}
					if (plugin.settings.placeholder)
					{
						$field.attr('placeholder', plugin.settings.placeholder);
					}
				}
			};

		plugin.settings = {};

		plugin.init = function() {
			// Переопределяем опции
			plugin.settings = $.extend({}, defaults, options);

			// Создаем элементы
			$container.append($field_wrapper, $box);
			$element.hide().after($container);
			$field_wrapper.append($field).css('width',$field.outerWidth());
			$field_wrapper.append($list_wrapper.append($list));
			list.generate();

			field.checkField();
			// Бинды на инпут
			// Ввод символа
			$field.bind('keyup', field.onKeyup);
			// Фокус
			$field.bind('focus', field.onFocus);
			// Отводится курсор
			$container.bind('mouseleave', field.onBlur);
		};

		plugin.refresh = function() {
			list.generate(true);
		};

		plugin.init();
	};

	$.fn.OTselect = function (options) {
		return this.each(function() {
			if (!$.data(this, "OTselect"))
			{
				$.data(this, "OTselect", new $.OTselect(this, options));
			}
		});
	};

    var dof_multiselect_pile = $('.dof_multiselect select[multiple]');

	dof_multiselect_pile.each(function() {

		var attrubutes = {
			isFrozen: $(this).attr('disabled'),
			placeholder: $(this).attr('data-placeholder')
		};

		$(this).OTselect(attrubutes);
	});

});