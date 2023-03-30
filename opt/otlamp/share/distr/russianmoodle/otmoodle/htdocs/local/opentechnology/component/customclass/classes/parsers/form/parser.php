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

/**
 * Настраиваемые формы
 *
 * @package    local_opentechnology
 * @subpackage otcomponent_customclass
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace otcomponent_customclass\parsers\form;

require_once("$CFG->libdir/formslib.php");
require_once("$CFG->libdir/accesslib.php");

use otcomponent_customclass\parsers\base;

/**
 * Базовый класс парсера
 *
 * @package    local_opentechnology
 * @subpackage otcomponent_customclass
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class parser extends base
{
    /**
     * Приведение типов полей парсера
     * 
     * @var array
     */
    protected static $cast_types = [
        'advcheckbox' => 'advcheckbox',
        'autocomplete' => 'autocomplete',
        'button' => 'button',
        'cancel' => 'cancel',
        'checkbox' => 'checkbox',
        'course' => 'course',
        'date' => 'date_selector',
        'date_selector' => 'date_selector',
        'date_time_selector' => 'date_time_selector',
        'duration' => 'duration',
        'editor' => 'editor',
        'filemanager' => 'filemanager',
        'filepicker' => 'filepicker',
        'grading' => 'grading',
        'group' => 'group',
        'header' => 'header',
        'hidden' => 'hidden',
        'htmleditor' => 'htmleditor',
        'listing' => 'listing',
        'modgrade' => 'modgrade',
        'modvisible' => 'modvisible',
        'password' => 'password',
        'passwordunmask' => 'passwordunmask',
        'questioncategory' => 'questioncategory',
        'radio' => 'radio',
        'recaptcha' => 'recaptcha',
        'searchableselector' => 'searchableselector',
        'select' => 'select',
        'selectgroups' => 'selectgroups',
        'selectwithlink' => 'selectwithlink',
        'selectyesno' => 'selectyesno',
        'static' => 'static',
        'submit' => 'submit',
        'submitlink' => 'submitlink',
        'tags' => 'tags',
        'text' => 'text',
        'textarea' => 'textarea',
        'url' => 'url',
        'warning' => 'warning',
        'html' => 'html',
        'htmleditor' => 'htmleditor',
        'country' => 'country',
        'hiddenselect' => 'hiddenselect',
        'hierselect' => 'hierselect',
        'image' => 'image',
        'link' => 'link',
        'element' => 'element',
        'reset' => 'reset',
        'xbutton' => 'xbutton'
    ];
    
    /**
     * Парсинг пришедших данных в форму
     *
     * @param array $fields
     *
     * @return customform
     */
    protected  static function execute($fields)
    {
        $form = new customform();
        $form->set_fields($fields);
        return $form;
    }
}
