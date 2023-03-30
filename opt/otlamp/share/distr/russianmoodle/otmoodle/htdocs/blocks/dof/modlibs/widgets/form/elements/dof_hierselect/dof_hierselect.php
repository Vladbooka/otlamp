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

/**
 * Класс поля для ввода телефона.
 *
 * @package    modlib
 * @subpackage widgets
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG, $DOF;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/form/templatable_form_element.php');
require_once($CFG->libdir . '/pear/HTML/QuickForm/hierselect.php');
require_once($DOF->plugin_path('modlib','widgets','/form/group_export_for_template.php'));

class MoodleQuickForm_dof_hierselect extends HTML_QuickForm_hierselect implements templatable {

    use templatable_form_element, group_export_for_template {
        templatable_form_element::export_for_template as export_for_template_base;
        group_export_for_template::export_for_template as export_for_template_dof;
    }


    public function __construct($elementName=null, $elementLabel=null, $attributes=null, $separator=null) {
        parent::__construct($elementName, $elementLabel, $attributes, $separator);

        $this->_type = 'dof_hierselect';
    }
    
    public function export_for_template(renderer_base $output) {
        foreach($this->_elements as $element) {
            $classes = $element->getAttribute('class');
            $classes .= (empty($classes)?'':' ') . 'custom-select';
            $element->updateAttributes(['class' => $classes]);
        }
        return $this->export_for_template_dof($output);
    }
}
?>