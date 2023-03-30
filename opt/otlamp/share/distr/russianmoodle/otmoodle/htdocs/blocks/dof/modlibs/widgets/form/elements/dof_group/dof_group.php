<?php
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

global $CFG;
require_once($CFG->libdir . '/form/group.php');
require_once($CFG->libdir . '/formslib.php');

/**
 * Класс поля группировки элементов
 *
 * Класс с расширенным набором настроек поля
 *
 * @package    formslib
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleQuickForm_dof_group extends MoodleQuickForm_group
{
    use group_export_for_template {
        group_export_for_template::export_for_template as export_for_template_dof;
    }

//     protected $currentTemplate = null;

//     public $options = [];

//     protected $availableTemplates = ['modal'];
    /**
     * Конструктор
     *
     * @param string $elementName (optional) name of the group
     * @param string $elementLabel (optional) group label
     * @param array $elements (optional) array of HTML_QuickForm_element elements to group
     * @param string $separator (optional) string to seperate elements.
     * @param array $options - Опции поля
     *  'template'
     */
    function __construct($elementName=null, $elementLabel=null, $elements=null, $separator=null, $options = [])
    {
//         if ( isset($options['template']) )
//         {// Целевой шаблон указан
//             $this->setTemplate((string)$options['template']);
//             unset($options['template']);
//         }
        $this->options = $options;
        parent::__construct($elementName, $elementLabel, $elements, $separator, true);

        $this->_type = 'dof_group';
    }

//     /**
//      * Установка шаблона отображения группы
//      *
//      * @param string $templatename - Имя шаблона
//      */
//     protected function setTemplate($templatename)
//     {
//         if ( in_array($templatename, $this->availableTemplates) )
//         {// Шаблон доступен
//             $this->currentTemplate = $templatename;
//         }
//     }

//     /**
//      * Получить базовое имя для шаблона отображения поля
//      *
//      * @return string
//      */
//     protected function baseElementTemplateType()
//     {
//         return parent::getElementTemplateType();
//     }

//     /**
//      * Вернуть шаблон отображения поля
//      *
//      * @return string
//      */
//     public function getElementTemplateType()
//     {
//         if ( $this->currentTemplate )
//         {
//             return 'dof_'.$this->currentTemplate;
//         }
//         return $this->baseElementTemplateType();
//     }


//     public function accept(&$renderer, $required = false, $error = null)
//     {
//         global $DOF;

//         switch ( $this->getElementTemplateType() )
//         {
//             case 'dof_modal' :
//                 $title = $this->_label;
//                 if ( isset($this->options['title']) )
//                 {
//                     $title = $this->options['title'];
//                 }
//                 $renderer->_elementTemplates[$this->getElementTemplateType()] =
//                     $DOF->modlib('widgets')->modal(
//                         $this->_label,
//                         $renderer->_elementTemplates[$this->baseElementTemplateType()],
//                         $title,
//                         $this->options
//                     );
//                 break;
//             default:
//                 break;
//         }
//     }

    public function export_for_template(renderer_base $output) {
        global $DOF;

        $context = $this->export_for_template_dof($output);
//         if ($this->getElementTemplateType() == 'dof_modal')
//         {
            $context['elementTemplateDofModal'] = true;
            $label = $this->_label;
            $text = '';
            $title = $this->options['title'];
            $options = $this->options;
            $context['elementTemplateData'] = $DOF->modlib('widgets')->modal_data($label, $text, $title, $options);
//         }
        return $context;
    }
}
