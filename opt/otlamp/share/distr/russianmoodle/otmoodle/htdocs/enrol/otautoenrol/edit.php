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
 * otautoenrol enrolment plugin.
 *
 * This plugin automatically enrols a user onto a course the first time they try to access it.
 *
 * @package    enrol
 * @subpackage otautoenrol
 * @date       July 2013
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once('edit_form.php');

$courseid = required_param('courseid', PARAM_INT);
$instanceid = optional_param('id', 0, PARAM_INT); // The instanceid.

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id);

require_login($course);
require_capability('enrol/otautoenrol:config', $context);

$PAGE->set_url('/enrol/otautoenrol/edit.php', array('courseid' => $course->id));
$PAGE->set_pagelayout('admin');

$return = new moodle_url('/enrol/instances.php', array('id' => $course->id));
if (!enrol_is_enabled('otautoenrol')) {
    redirect($return);
}

$plugin = enrol_get_plugin('otautoenrol');

if ($instanceid) {
    $instance = $DB->get_record(
            'enrol', array('courseid' => $course->id, 'enrol' => 'otautoenrol', 'id' => $instanceid), '*', MUST_EXIST);
} else {
    require_capability('moodle/course:enrolconfig', $context);
    // No instance yet, we have to add new instance.
    navigation_node::override_active_url(new moodle_url('/enrol/instances.php', array('id' => $course->id)));
    $instance = new stdClass();
    $instance->id = null;
    $instance->courseid = $course->id;
}

$mform = new enrol_otautoenrol_edit_form(null, array($instance, $plugin, $context));

if ($mform->is_cancelled())
{
    redirect($return);

} else if ($mform->is_validated() && $data = $mform->get_data())
{
    $conditions = [];
    if( isset($data->conditionfield) && is_array($data->conditionfield) )
    {
        foreach($data->conditionfield as $k=>$field)
        {
            if ( ! empty($field) && isset($data->conditionfieldval[$k]) )
            {
                $conditions[] = [
                    'conditionfield' => $field,//customchar3
                    'conditionfieldval' => $data->conditionfieldval[$k],//customchar1
                    'conditionfieldsoftmatch' => ! empty($data->conditionfieldsoftmatch[$k])//customint4
                ];
            }

        }
    }

    // Универсальный массив для хранения разных конфигов (что-то свободных полей у этого энрола всё меньше)
    $config = [];

    // Добавление конфига по полю профиля с группами, на которые надо учащегося подписать (если есть)
    if (!empty($data->has_groups_field) && !empty($data->groups_field)) {
        // поставлена галка добавления в группы по полю профиля и задано поле профиля

        // заданное настройками поле профиля
        $field = $data->groups_field;
        // дефолтная регулярка
        $regex = '/(?<group_idnumber>[^,]*)@(?<course_shortname>[^,]*)/';
        $iscustomregex = false;

        if (!empty($data->groups_field_edit_regex) && !empty($data->groups_field_regex)) {
            // поставлена галка для редактирования регулярки и отправлена регулярка
            $regex = $data->groups_field_regex;
            $iscustomregex = true;
        }

        $config['groups_field_config'] = [
            'field' => $field,
            'regex' => $regex,
            'custom' => $iscustomregex,
            'autocreate' => !empty($data->groups_field_autocreate)
        ];
    }

    /**
     * Описание полей инстанса
     *
     * @var string customchar2 - Название способа записи
     *
     * @var int customint1 - Когда подписывать
     * @var int customint2 - Создать группу в соответствии с фильтром и подписать в нее пользователя
     * @var int customint3 - Роль
     * @var int customint5 - Ограничение по количеству подписок
     * @var int customint6 - Случайное распределение по выбранным группам
     * @var int customint7 - Отписывать пользователей, если они перестанут соответствовать условиям подписки
     * @var int customint8 - Подписывать всегда
     *
     * @var string customtext1 - Добавить пользователей в группы
     * @var string customtext3 - JSON условий
     * @var string customtext4 - JSON-encoded config, универсальный конфиг
     *                           {"groups_field_config":{"field":"profile_field_groups","regex":"/(?<group_idnumber>[^,]*)@(?<course_shortname>[^,]*)/"}}
     */

    if ($instance->id)
    {
        if ($data->customint5 < 0) {
            $data->customint5 = 0;
        }
        $instance->customint5 = $data->customint5;
        if ($data->customint8 != 0 && $data->customint8 != 1) {
            $data->customint8 = 0;
        }

        $instance->timemodified = time();
        if (has_capability('enrol/otautoenrol:method', $context)) {
            $instance->customint1 = $data->customint1;
            $instance->customint3 = $data->customint3;
        }
        $instance->customtext3 = json_encode($conditions);
        $instance->customtext4 = json_encode($config);

        $instance->customint7 = $data->customint7;
        $instance->customint8 = $data->customint8;
        $instance->customchar2 = $data->customchar2;

        // Запись в группы
        if (!array_key_exists('groups_field_config', $config)) {
            // определение групп по полю профиля выключено - обрабатываем другие способы определения групп

            // группы выбранные в настройках способа записи
            $instance->customtext1 = '';
            if (!empty($data->customtext1)) {
                $instance->customtext1 = implode(',', $data->customtext1);
            }
            // создать группу, названную в соовтетствии с условиями подписки и подписать туда
            $instance->customint2 = $data->customint2;
            // случайное распределение
            $instance->customint6 = $data->customint6;
        }

        $DB->update_record('enrol', $instance);

        if ( ! empty($instance->customint7) )
        {
            $plugin->check_users_to_unenrol($instance);
        }

        // Do not add a new instance if one already exists (someone may have added one while we are looking at the edit form).
    } else {
        if ($data->customint5 < 0) {
            $data->customint5 = 0;
        }
        if ($data->customint8 != 0 && $data->customint8 != 1) {
            $data->customint8 = 0;
        }
        $fields = [
            'customint1' => 0,
            'customint3' => 5,
            'customint5' => $data->customint5,
            'customint7' => $data->customint7,
            'customint8' => $data->customint8,
            'customchar2' => $data->customchar2,
            'customtext3' => json_encode($conditions),
            'customtext4' => json_encode($config),
        ];
        if (has_capability('enrol/otautoenrol:method', $context)) {
            $fields['customint1'] = $data->customint1;
            $fields['customint3'] = $data->customint3;
        }

        // Запись в группы
        if (!array_key_exists('groups_field_config', $config)) {
            // определение групп по полю профиля выключено - обрабатываем другие способы определения групп

            // группы выбранные в настройках способа записи
            $fields['customtext1'] = '';
            if (!empty($data->customtext1)) {
                $fields['customtext1'] = implode(',', $data->customtext1);
            }
            // создать группу, названную в соовтетствии с условиями подписки и подписать туда
            $fields['customint2'] = $data->customint2;
            // случайное распределение
            $fields['customint6'] = $data->customint6;
        }

        $plugin->add_instance($course, $fields);

    }

    redirect($return);
}

$PAGE->set_title(get_string('pluginname', 'enrol_otautoenrol'));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'enrol_otautoenrol'));
$mform->display();
echo $OUTPUT->footer();
