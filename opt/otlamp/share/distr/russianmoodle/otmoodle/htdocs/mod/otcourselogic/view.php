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
 * Модуль Логика курса. Страница просмотра элемента курса.
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_otcourselogic\info;

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.'/user/lib.php');

// ID экземпляра модуля курса
$cmid = optional_param('id', 0, PARAM_INT);
// ID экземпляра логики курса для получения настроек
$instanceid = optional_param('instanceid', 0, PARAM_INT);

// Идентификатора модуля курса определен
if ( $cmid ) 
{
    $PAGE->set_url('/mod/otcourselogic/view.php', ['id' => $cmid]);
    if ( ! $cm = get_coursemodule_from_id('otcourselogic', $cmid) ) 
    {
        print_error('invalidcoursemodule');
    }

    if ( ! $course = $DB->get_record('course', ['id' => $cm->course])) 
    {
        print_error('coursemisconf');
    }

    if ( ! $instance = $DB->get_record('otcourselogic', ['id' => $cm->instance])) 
    {
        print_error('invalidcoursemodule');
    }
} else 
{
    $PAGE->set_url('/mod/otcourselogic/view.php', ['instanceid' => $instanceid]);
    if ( ! $instance = $DB->get_record('otcourselogic', ['id' => $instanceid]) ) 
    {
        print_error('invalidcoursemodule');
    }
    if ( ! $course = $DB->get_record('course', ['id' => $instanceid->course]) )
    {
        print_error('coursemisconf');
    }
    if ( ! $cm = get_coursemodule_from_instance('otcourselogic', $instanceid->id, $course->id)) 
    {
        print_error('invalidcoursemodule');
    }
}

// Требуется вход в систему
require_course_login($course, true, $cm);

// Получение контекста страницы
$context = context_module::instance($cm->id);

// Проверка прав доступа
require_capability('mod/otcourselogic:view', $context);

// Установка параметров страницы
$PAGE->set_title($course->shortname.': '.$instance->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_activity_record($instance);

$html = '';

// Инициализация менеждера состояний
$state_checker = otcourselogic_get_state_checker();

// Кнопка возврата в курс
$courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);
$html .=  html_writer::link(
    $courseurl,
    get_string('return_to_course', 'mod_otcourselogic'),
    ['class' => 'btn button btn-secondary']
).PHP_EOL;

if ( has_capability('mod/otcourselogic:view_student_states', $context) )
{// Отображеие полной таблицы состояний элемента
    
    // Редирект на страницу управления действиями, если отсутствуют обработчики
    \mod_otcourselogic\apanel\helper::check_newly_created($cm->instance);
    \mod_otcourselogic\apanel\helper::check_states($cm->instance, $course->id);
    
    // Подготовка таблицы
    $table = new html_table();
    $table->attributes['class'] = "generaltable ot-searchable otcourselogic_states_table";
    $table->attributes['data-searchable-cells'] = json_encode([
        'otcl-role' => get_string('caption_roles_all', 'mod_otcourselogic'),
        'otcl-group' => get_string('caption_groups', 'mod_otcourselogic')
    ]);
    $table->head = [
        '',
        get_string('caption_username', 'mod_otcourselogic'),
        get_string('caption_roles_all', 'mod_otcourselogic'),
        get_string('caption_groups', 'mod_otcourselogic'),
        get_string('caption_state_element', 'mod_otcourselogic'),
        get_string('caption_last_change_state', 'mod_otcourselogic')
    ];
    $table->data = [];
    
    // Получение данных по всем пользователям
    $otcourselogic_state = (array)$state_checker->get_state_info($cm->instance);
    
    // Подготовка данных
    $userids = $user_info = [];
    foreach ( $otcourselogic_state as $userstate )
    {
        $userids[] = $userstate->userid;
        $user_info[$userstate->userid] = ['changetime' => $userstate->changetime];
    }
    
    // Формирование таблицы
    if ( $userids )
    {// Пользователи найдены
        $users = user_get_users_by_id($userids);
        
        // Добавление данных по каждому пользователю
        foreach ( $users as $user )
        {
            if ( ! is_enrolled($context, $user, 'mod/otcourselogic:is_student', true) )
            {
                continue;
            }
            $data = [];
            global $OUTPUT;
                // Ссылка на журнал логов
            $data[] = html_writer::link(
                    new moodle_url('/mod/otcourselogic/logs_journal.php', [
                        'id' => $cmid,
                        'userid' => $user->id
                    ]),
                    html_writer::img($OUTPUT->image_url('log', 'mod_otcourselogic'), 'Журнал исполнения групп действий',
                            [
                                'class' => 'otcourselogic_log_img'
                            ]),
                    ['class' => 'otcourselogic_log_button btn btn-secondary']);
            
            // Пользователь
            $userurl = new moodle_url(
                '/user/view.php',
                ['id' => $user->id, 'course' => $course->id]
            );
            $data[] = html_writer::link($userurl, fullname($user));
            
            // Роли пользователя в курсе
            $userroles = \mod_otcourselogic\info::get_user_roles($user, $course);
            $rolecell = new html_table_cell($userroles);
            $rolecell->attributes['class'] = 'otcl-role';
            $data[] = $rolecell;
            
            // Группы пользователя в курсе
            $usergroups = \mod_otcourselogic\info::get_user_groups($user, $course);
            $groupcell = new html_table_cell($usergroups);
            $groupcell->attributes['class'] = 'otcl-group';
            $data[] = $groupcell;
        
            // Состояние элемента
            $data[] = $state_checker->get_state_string($cm->instance, $user->id);
            // Дата последнего изменения элемента
            $data[] = date('Y-m-d H:i:s', $user_info[$user->id]['changetime']);
            $table->data[] = $data;
        }
    }
    
    // Панель управления текущим инстансом логики курса
    $apanel_url = new moodle_url('/mod/otcourselogic/apanel/index.php', ['instance' => $cm->instance]);
    
    $html .=  html_writer::link(
        $apanel_url, 
        get_string('admin_panel', 'mod_otcourselogic'), 
        ['class' => 'mod_otcourselogic btn btn-secondary']
    ).PHP_EOL;
    
    // Кнопка принудительного пересчета
    $url = new moodle_url('/mod/otcourselogic/view.php', ['id' => $cmid, 'reset_states' => 1]);
    $html .= html_writer::link(
        $url,
        get_string('reset_states', 'mod_otcourselogic'),
        ['class' => 'mod_otcourselogic btn btn-secondary']
    ).PHP_EOL;
    
    // Отображение данных
    $html .=  html_writer::table($table);
    
} else 
{// Отображение состояния для текущего пользователя
    $state = $state_checker->get_state_info($cm->instance, $USER->id);
    if ( $state )
    {
        // Состояние элемента
        $content = get_string('caption_state_element', 'mod_otcourselogic').': ';
        $content .= $state_checker->get_state_string($cm->instance, $USER->id);
        // Дата последнего изменения элемента
        $content .= '<br>';
        $content .= get_string('caption_last_change_state', 'mod_otcourselogic').': ';
        $content .= date('Y-m-d H:i:s', $state->changetime);
        $content .= '<br>';
        
        $html .=  html_writer::div($content);
    }
}

// Шапка страницы
echo $OUTPUT->header();

echo $html;

echo $OUTPUT->footer();


