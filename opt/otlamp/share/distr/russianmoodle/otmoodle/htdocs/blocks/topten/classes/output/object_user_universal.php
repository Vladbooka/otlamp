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
 * Подготовка данных для рендеринга универсального темплейта
 */
namespace block_topten\output;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/topten/lib.php');

use renderable;
use renderer_base;
use templatable;
use block_topten\reports\user_selection as user_selection;

class object_user_universal implements renderable, templatable {
    
    protected $data;
    protected $aditiondata;

    public function __construct($data, $aditiondata) {
        $this->data = $data;
        $this->aditiondata = $aditiondata;
    }

    public function export_for_template(renderer_base $output) {
        $dat = [];
        foreach ($this->data as $userid) {
            $dat['elements'][] = $this->get_prepared_fields($userid);
        }
        return $dat;
    }
    /**
     * Получение пользовательских полей по ид
     *
     * @param integer $userid
     * @return \stdClass
     */
    public function get_prepared_fields($userid) {
        global $OUTPUT, $DB;
        $fieldslist = [];
        $dof = block_topten_get_dof();
        if (!is_null($dof)) {
            $user = $DB->get_record('user', ['id' => $userid]);
            $ammauser = $dof->modlib('ama')->user($userid);
            $fieldslist['user_fields'] = $ammauser->get_all_user_fields_data(['middlename', 'description']);
            // добавляем изображение
            $img = new \stdClass();
            $img->shortname = 'img';
            $img->displayvalue = $OUTPUT->user_picture($user, ['size' => 150, 'class' => 'pics']);
            $img->value = '';
            $img->name = get_string('user_img', 'block_topten');
            $img->template_field_img = true;
            $fieldslist['template_fields'][0] = $img;
            // полное имя пользователя
            $fullname = new \stdClass();
            $fullname->shortname = 'fullname';
            $fullname->displayvalue = fullname($user);
            $fullname->value = fullname($user);
            $fullname->name = get_string('fullname', 'block_topten');
            $fullname->template_field_fullname = true;
            $fieldslist['template_fields'][1] = $fullname;
            // настраиваемые поля пользователя
            if (!empty($this->aditiondata->additionfields)) {
                foreach ($this->aditiondata->additionfields as $key => $value) {
                    if (isset($fieldslist['user_fields'][$value->field])) {
                        $fieldslist['additionfields'][$key]['field'] = $fieldslist['user_fields'][$value->field];
                        $fieldslist['additionfields'][$key]['text_field'] = $value->text_field;
                        $fieldslist['additionfields'][$key]['field_' . $key] = true;
                    }
                }
            }
            foreach ($fieldslist['user_fields'] as $key => $value) {
                $value->$key = true;
            }
            $fieldslist['user_fields'] = array_values($fieldslist['user_fields']);
        }
        return $fieldslist;
    }  
}
