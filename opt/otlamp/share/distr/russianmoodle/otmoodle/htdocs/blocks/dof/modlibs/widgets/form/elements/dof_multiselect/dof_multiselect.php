<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
//                                                                        //
// This program is free software: you can redistribute it and/or modify   //
// it under the terms of the GNU General Public License as published by   //
// the Free Software Foundation, either version 3 of the Licensen.        //
//                                                                        //
// This program is distributed in the hope that it will be useful,        //
// but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// GNU General Public License for more details.                           //
//                                                                        //
// You should have received a copy of the GNU General Public License      //
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
//                                                                        //
////////////////////////////////////////////////////////////////////////////

global $CFG;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/pear/HTML/QuickForm/Renderer/Default.php');
require_once($CFG->libdir . '/form/group.php');

/**
 * OTselect - мультиселект
 * @package    formslib
 * @subpackage dof_multiselect
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleQuickForm_dof_multiselect extends MoodleQuickForm_group
{
    /**
     * @var string - список для селекта
     */
    private $_list = [];
    /**
     * @var string - Плейсхолдер инпута
     */
    private $_placeholder = '';
    /**
     * @var array - Какие пункты должны быть выбраны по умолчанию
     */
    private $_default_selected_items = [];

   /**
    * Class constructor
    * @access public
    * @param  string $elementName Element's name
    * @param  mixed  $elementLabel Label(s) for an element
    * @param  mixed  $attributes Either a typical HTML attribute string or an associative array
    */
    function __construct($elementName = null, $elementLabel = null, $options=null)
    {
        GLOBAL $DOF;

        parent::__construct($elementName, $elementLabel);

        $this->_persistantFreeze = true;
        $this->_appendName = true;
        $this->_type = 'dof_multiselect';
        $this->_elementName = $elementName;
        $this->_elementLabel = $elementLabel;

        // Проверим данные, которые пришли
        $this->checkOptions($options);

         // Подключение JS-поддержки
        $DOF->modlib('nvg')->add_js('modlib', 'widgets', '/form/elements/dof_multiselect/dof_multiselect.js', false);
        // Подключение CSS
        $DOF->modlib('nvg')->add_css('modlib', 'widgets', '/form/elements/dof_multiselect/dof_multiselect.css', false);
    }

    /**
     * Создание элемента
     *
     * @return void
     */
    function _createElements()
    {
        $name = 'multiselect'.$this->_elementName;
        $label = $this->_elementLabel;
        $options = $this->_list;
        // Генерация атрибутов
        $attrs = [
            'class' => 'dof_multiselect',
            'data-placeholder' => (string)$this->_placeholder,
        ];
        if ($this->_flagFrozen)
        {// Поле залокирвано
            $attrs['disabled'] = 'disabled';
        }
        // Добавление множественного выпадающего списка
        $select = $this->createFormElement('select', $name, $label, $options, $attrs);
        $select->setMultiple(true);

        if (!empty($this->_default_selected_items) && is_array($this->_default_selected_items))
        {// Заполним дефолтные
            $check_empty = array_diff($this->_default_selected_items, array_keys($this->_list));
            if (empty($check_empty))
            {
                $select->setSelected($this->_default_selected_items);
            }
        }
        $this->_elements[] = $select;
    }

//     /**
//      * Отрисовка элемента
//      * @return $string - html код мультиселекта
//      */
//     function toHtml()
//     {
//         include_once('HTML/QuickForm/Renderer/Default.php');
//         $renderer = new HTML_QuickForm_Renderer_Default();
//         $renderer->setElementTemplate('{element}');
//         parent::accept($renderer);
//         return $renderer->toHtml();
//     }

    function accept(&$renderer, $required = false, $error = null)
    {
        $this->_createElementsIfNotExist();
        $element = array_shift($this->_elements);
        $name = $this->getName();
        $key = 0;
        $elementname = '';
        if ($this->_appendName) {
            $elementname = $element->getName();
            if (isset($elementname)) {
                $element->setName($name . '['. (strlen($elementname) ? $elementname : $key) .']');
            } else {
                $element->setName($name);
            }
        }

        $required = !$element->isFrozen() && in_array($element->getName(), $this->_required);

        $element->accept($renderer, $required);

        // Restore the element's name.
        if ($this->_appendName) {
            $element->setName($elementname);
        }

//         $renderer->renderElement($this, $required, $error);
    }

    /** Преобразовать полученные данные
     * @param array $submitValues
     * @param bool $assoc
     * @return array
     */
    function exportValue(&$submitValues, $assoc = false)
    {
        // Массив
        $ids = [];
        if ( ! empty($submitValues[$this->_elementName]['multiselect' . $this->_elementName])
                    && ($submitValues[$this->_elementName]['multiselect' . $this->_elementName] != '_qf__force_multiselect_submission') )
        {// Ок, есть выбранные
            $ids = $submitValues[$this->_elementName]['multiselect' . $this->_elementName];
        }
        return [$this->_elementName => $ids];
    }

    /** Проверим данные, которые пришли для формирования мультиселекта
     * @param array $options - пришедшии данные для мультиселекта
     * @return void
     */
    function checkOptions($options)
    {
        if ( is_array($options) && ! empty($options) )
        {
            $this->_list = $options;
        }
    }

    /**
     * Устанавливаем флаг disabled для мультиселекта
     * @return void
     */
    function freeze()
    {
        $this->_flagFrozen = true;
    }

    /**
     * Удаляем флаг disabled для мультиселекта
     * @return void
     */
    function unfreeze()
    {
        $this->_flagFrozen = false;
    }

    /**
     * Переопределяем updateValue (сабмит/setDefault)
     *
     * @param     string    $event  Name of event
     * @param     mixed     $arg    event arguments
     * @param     object    $caller calling object
     * @since     1.0
     * @access    public
     * @return    void
     */
    function onQuickFormEvent($event, $arg, &$caller)
    {
        $this->setMoodleForm($caller);
        switch ($event) {
            case 'updateValue':
                // Поймали дефолтные значения, установим
                $value = $this->_findValue($caller->_constantValues);
                if (null === $value) {
                    $value = $this->_findValue($caller->_submitValues);
                    if (null === $value) {
                        $value = $this->_findValue($caller->_defaultValues);
                    }
                }
                if ( ! is_null($value) && is_array($value) )
                {
                    if ( isset($value['multiselect' . $this->_elementName]) )
                    {
                        $this->_default_selected_items = $value['multiselect' . $this->_elementName];
                    }
                    else
                    {
                        $this->_default_selected_items = $value;
                    }
                }
                break;
            case 'createElement':
                if( isset($arg[3]) && ! empty($arg[3]) )
                {
                    if( isset($arg[3]['placeholder']))
                    {
                        $this->_placeholder = $arg[3]['placeholder'];
                    }
                }
                parent::onQuickFormEvent($event, $arg, $caller);
                break;
            default:
                parent::onQuickFormEvent($event, $arg, $caller);
                break;
        }
        return true;
    }

    public function export_for_template(renderer_base $output) {
        $context = parent::export_for_template($output);
        $context['elementrawhtml'] = $this->toHtml();
        return $context;
    }
}
?>