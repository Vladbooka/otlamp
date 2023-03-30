<?php

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodle.org                                            //
//                                                                       //
//                                                                       //
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation; either version 2 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
//          http://www.gnu.org/copyleft/gpl.html                         //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

global $CFG;
require_once($CFG->libdir . '/formslib.php');


/**
 * SLELECT-элемент с динамической подгрузкой вариантов выбора
 */
class MoodleQuickForm_dof_ajaxselect extends MoodleQuickForm_select
{
    /**
     * @var array массив с параметрами ajax-запроса
     */
    public $_options = [];
    /*
     * строка, имя элемента(в js используется для идентификации id элемента)
     */
    public $_elementName = '';
    /**
     * @var string строка с js-кодом элемента
     */
    public $_js = '';
    /**
     * @var string строка с адресом для запроса, с установленными обязательными параметрами
     */
    public $ajaxurl= '';

    /**
     * Флаг обозначения использования нового API деканата
     *
     * @var string
     */
    protected $usenewapi = false;

    /**
     * Название метода API
     *
     * @var string
     */
    protected $newapimethodname = '';

    /**
     * Поле, на которое срабатываем AJAX заполнение
     *
     * @var array
     */
    protected $newapion = [];

    /**
     * Статические данные для AJAX запроса
     *
     * @var array
     */
    protected $newapistaticvars = [];

   /**
    * Class constructor (for PHP 4)
    *
    * @access public
    * @param  string $elementName Element's name
    * @param  mixed  $elementLabel Label(s) for an element
    * @param  mixed  $attributes Either a typical HTML attribute string or an associative array
    */
    function __construct($elementName = null, $elementLabel = null, $attributes = null, $options=null)
    {
        GLOBAL $DOF;
        parent::__construct($elementName, $elementLabel, $options, $attributes);
        $this->_persistantFreeze = true;
        $this->_appendName = true;
        $this->_type = 'dof_ajaxselect';
        $this->_elementName = $elementName;

        // подключаем скрипты для работы
        $DOF->modlib('widgets')->js_init('ajaxselect');

        // устанавливаем обязательные параметры для ajax-запроса
        $this->setOptions($options);
    }

    /**
     * Отрисовка элемента
     *
     * @return string
     */
    function toHtml()
    {
        if ( $this->_flagFrozen )
        {
            $html = '';
            if ( ! is_null($this->_values[0]) )
            {
                $html = ! empty($this->_options[0]['text']['options'][$this->_values[0]]) ? $this->_options[0]['text']['options'][$this->_values[0]]: '';
                if ( is_array($html) )
                {
                    $html = '';
                }
                if ($this->_persistantFreeze) {
                    $name = $this->getPrivateName();
                    $arr = array(
                        'type'  => 'hidden',
                        'name'  => $name,
                        'value' => $this->_values[0]
                    ) + ['id' => "id_{$this->_elementName}"];
                    $str = $this->_getAttrString($arr);
                    $html .= '<input' . $str .  '/>';
                }
            }
            return $html;
        }
        else
        {
            $tabs    = $this->_getTabs();
            $strHtml = '';

            if ( ! array_key_exists('class', $this->_attributes) )
            {
                $this->_attributes['class'] = 'custom-select dof_ajaxselect';
            } else
            {
                $this->_attributes['class'] = $this->_attributes['class'] . ' custom-select dof_ajaxselect';
            }
            $attrString = $this->_getAttrString($this->_attributes);

            $strHtml .= $tabs . '<select' . $attrString . ">\n";

            $strHtml .= '<option/>';

            return $strHtml . $tabs . '</select>'.$this->get_js();
        }
    }

    /**
     * Формирует js-скрипт и возвращает его
     *
     * @return string
     **/
    function get_js()
    {
        global $DOF;

        // проверка валидности опций
        $this->checkOptions($this->_options);

        $js = '';

        if ( $this->usenewapi )
        {// включено использование нового API деканата
            global $PAGE;

            // так как moodle обязывает объявлять AMD модули в папке amd/, но расположить архитектурно туда будет неверно
            // то объявление модуля происходит в этом месте, где этот модуль необходим
            static $included = false;
            if ( !$included )
            {
                $included = true;
                $DOF->modlib('nvg')->add_js_amd_inline(file_get_contents($DOF->plugin_path('modlib', 'widgets', '/form/elements/dof_ajaxselect/dof_ajax_newapi.js')));
                $DOF->modlib('nvg')->add_css('modlib', 'widgets', '/form/elements/dof_ajaxselect/dof_ajaxselect.css');
            }

            $vars = json_encode($this->newapistaticvars);
            $on = json_encode($this->newapion);
                // вызов функции с параметрами текущего элемента
            $PAGE->requires->js_amd_inline(
                    "require(['block_dof/dof_ajaxselect'], function(module) {
                        module.init('{$this->newapimethodname}','{$on}','{$vars}','#id_{$this->_elementName}','{$this->_values[0]}')
                    })");
        } else
        {
            $js = "<script type=\"text/javascript\">\n//<![CDATA[\n";
            // все функции уже написаны в modlib/widgets,
            // нам остается только обратиться к ним и инициализировать элемент
            $js .= '$(document).ready(
            function () {
                dof_ajaxselect_init(\''.
                $this->_options['parentid'].'\',
                \''.$this->_options['childselectid'].'\',
                \''.$this->_options['url'].'\',
                '.$this->_options['customdata'].',
                \''.$this->_values[0].'\');
        });';
                $js .= "\n //]]>\n</script>";
        }

        return $js;
    }

    /**
     *
     */
    function accept(&$renderer, $required = false, $error = null)
    {
        $renderer->renderElement($this, $required, $error);
    }

    /** Получить данные для ajax-запроса
     * Записывет все параметры запроса во внутреннее поле объекта
     * Функция только записывает данные, но не проверяет их
     *
     * @param array|object $options - массив с данными для ajax-запроса
     *                  plugintype - тип плагина, предоатвляющий данные для запроса
     *                  plugincode - тип плагина, предоатвляющий данные для запроса
     *                  querytype - тип ajax-запроса внутри плагина, предоставляющего данные
     *                  url - url для запроса, со всеми параметрами (необязательно)
     *                  customdata - данные, которые поедут в плагин вместе с запросом (необязательный параметр, массив)
     *                  type - тип запроса в modlib/widgets (необязательно, по умочанию - ajaxselect)
     *                  parentid - id элемента, на значение которого мы ореинтируемся
     *
     * @todo сделать вывод ошибок в более приемлемом виде
     */
    public function setOptions($options)
    {
        global $DOF;
        if ( ! is_array($options) AND ! is_object($options) )
        {// неправильный тип данных для запроса
            return;
        }
        if ( is_object($options) )
        {// преобразовываем данные к нужному типу
            $options = (array)$options;
        }


        if ( ! empty($options['newapi']) )
        {
            // включено использование нового API сервисов деканата
            $this->usenewapi = true;
            $this->newapimethodname = $options['newapi']['methodname'];
            $this->newapion = $options['newapi']['on'];
            $this->newapivars = $options['newapi']['staticvars'];
            return;
        }

        // устанавливаем id родительского и зависимого элементов
        if ( isset($options['parentid']) )
        {
            $this->_options['parentid'] = '#'.$options['parentid'];
        }
        $this->_options['childselectid'] = '#id_'.$this->_elementName;
        // приводим данные к формату json
        if ( isset($options['customdata']) )
        {
            $this->_options['customdata'] = json_encode($options['customdata']);
        }else
        {
            $this->_options['customdata'] = '{}';
        }
        // адрес для запроса (со всеми параметрами)
        if ( isset($options['url']) )
        {// он передан - просто его используем
            $this->_options['url'] = $options['url'];
        }else
        {// не передан - конструируем
            if ( isset($options['plugintype']) AND
                 isset($options['plugincode']) AND
                 isset($options['querytype']) )
            {
                if ( ! isset($options['type']) )
                {
                    $options['type'] = 'ajaxselect';
                }

                $this->_options['url'] = $DOF->url_modlib('widgets', '/json.php',
                    array(
                        'plugincode' => $options['plugincode'],
                        'plugintype' => $options['plugintype'],
                        'querytype'  => $options['querytype'],
                        'type'       => $options['type'],
                        'sesskey'    => sesskey()
                    ));
            }
        }
    }

    /**
    * We check the options and return only the values that _could_ have been
    * selected. We also return a scalar value if select is not "multiple"
    */
    function exportValue(&$submitValues, $assoc = false)
    {
        $value = $this->_findValue($submitValues);
        if (is_null($value)) {
            $value = $this->getValue();
        }
        $value = (array)$value;
        if ( empty($value) )
        {
            return null;
        }

        return $this->_prepareValue($value[0], $assoc);
    }

    /** Проверить параметры AJAX-запроса
     * Прерывает процесс работы скрипты и выдает ошибку,
     * если не все параметры заданы, или некоторые заданы неправильно
     * Функция вызывается после setOptions
     *
     * @return bool
     */
    public function checkOptions($options)
    {
        if ( $this->usenewapi )
        {
            // включен режим использования нового API деканата
            return true;
        }

        if ( ! isset($this->_options['parentid']) or ! $this->_options['parentid'] )
        {
            print_error(get_class($this).': NO REQUIRED PARAMETER parentid');
        }

        if ( ! isset($this->_options['url']) or ! $this->_options['url'] )
        {
            print_error(get_class($this).': NO REQUIRED PARAMETER url OR WRONG URL PARAMS');
        }
    }

    /**
     * Если будет 2-3 дня переделать, то добавим мультиселект, пока он не работает
     *
     * @param     bool    $multiple  Whether the select supports multi-selections
     * @since     1.2
     * @access    public
     * @return    void
     */
    function setMultiple($multiple)
    {
        throw new dof_exception_coding('dof_ajaxselect_connot_be_multiple');
    }

    public function export_for_template(renderer_base $output) {
        $context = $this->export_for_template_base($output);

        $context['options'] = [];
        $context['nameraw'] = $this->getName();
        $context['inlinejs'] = $this->get_js();
        $context['elementrawhtml'] = $this->toHtml();

        return $context;
    }
}
