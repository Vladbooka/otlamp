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
 * Плагин подписки через форму связи с менеджером. 
 * Страница сохранения экземпляра подписки
 *
 * @package    enrol
 * @subpackage sitecall
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once('edit_form.php');

// Получаем ID курса
$courseid = required_param('courseid', PARAM_INT);
// Получаем объект курса
$course = $DB->get_record(
        'course',
        array('id' => $courseid),
         '*', 
        MUST_EXIST
);
// Формируем контекст
$context = context_course::instance($course->id, MUST_EXIST);

// Доступ
require_login($course);
require_capability('enrol/sitecall:config', $context);

// Установим данные страницы
$PAGE->set_url('/enrol/sitecall/edit.php', array('courseid' => $course->id));
$PAGE->set_pagelayout('admin');

// Страница возврата
$return = new moodle_url('/enrol/instances.php', array('id' => $course->id));
if ( ! enrol_is_enabled('sitecall') ) 
{// Подписка не доступна
    redirect($return);
}

// Получим плагин подписки
$plugin = enrol_get_plugin('sitecall');
// Получим экземпляры плагина подписок
$instances = $DB->get_records('enrol', array('courseid' => $course->id, 'enrol' => 'sitecall'), 'id ASC');
if ( ! empty($instances) ) 
{
    // Извлечем первый экземпляр подписки из массива
    $instance = array_shift($instances);
    if ( ! empty($instances) ) 
    {// Только один экземпляр подписки может существовать в курсе, удалим остальные
        foreach ( $instances as $del ) 
        {
            $plugin->delete_instance($del);
        }
    }
    
    // Слияние значений
    if ($instance->notifyall and $instance->expirynotify) 
    {
        $instance->expirynotify = 2;
    }
    unset($instance->notifyall);
} else 
{// Экземпляров нет - создадим
    // Доступ
    require_capability('moodle/course:enrolconfig', $context);
    
    navigation_node::override_active_url(new moodle_url('/enrol/instances.php', array('id' => $course->id)));
    $instance = new stdClass();
    $instance->id              = null;
    $instance->courseid        = $course->id;
    $instance->expirynotify    = $plugin->get_config('expirynotify');
    $instance->expirythreshold = $plugin->get_config('expirythreshold');
}

$mform = new enrol_sitecall_edit_form(null, array($instance, $plugin, $context));

if ( $mform->is_cancelled() ) 
{// Отмена изменений
    redirect($return);
}

if ($data = $mform->get_data()) 
{// Получены данные формы
    if ($instance->id) 
    {
        // Статус
        $instance->timemodified = time();
        
        // Письма студент и преподавателю
        $instance->customtext1 = serialize($data->messageteacher);
        $instance->customtext2 = serialize($data->messagestudent);
        $instance->customint1 = $data->messageteacher_send;
        $instance->customint2 = $data->messagestudent_send;
        $DB->update_record('enrol', $instance);
        // Обновление статуса
        if ($instance->status != $data->status) 
        {
            $instance = $DB->get_record('enrol', array('id' => $instance->id));
            $plugin->update_status($instance, $data->status);
            $context->mark_dirty();
        }
    } else 
    {
        $fields = [
                        'status' => $data->status,
                        'customtext1' => serialize($data->messageteacher),
                        'customtext2' => serialize($data->messagestudent),
                        'customint1' => $data->messageteacher_send,
                        'customint2' => $data->messagestudent_send
        ];
        $plugin->add_instance($course, $fields);
    }
    redirect($return);
}

$PAGE->set_title(get_string('pluginname', 'enrol_sitecall'));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'enrol_sitecall'));
$mform->display();
echo $OUTPUT->footer();