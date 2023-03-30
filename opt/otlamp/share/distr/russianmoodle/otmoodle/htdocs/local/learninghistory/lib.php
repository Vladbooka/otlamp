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
 * @package    local_learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Обновить учебные процессы
 *
 * @return bool
 */
function local_learninghistory_cron()
{
    mtrace('Updating learninghistory:');
    \local_learninghistory\local\utilities::update_active_snapshots();
    mtrace('Finished updating learninghistory.');
}

/**
 * Метод, добавляющий ссылку на страницу дополнительных настроек курса
 *
 * @param unknown $settingsnav
 * @param unknown $context
 */
function local_learninghistory_before_http_headers()
{
    global $PAGE, $CFG, $USER;

    // Добавим новоую страницу настроек для страниц курса
    if ( $PAGE->course && $PAGE->course->id != SITEID && isloggedin())
    {
        $context = $PAGE->context;
        $activetime = local_learninghistory_get_course_config($PAGE->course->id, 'activetime');
        if( $activetime == 1 && is_enrolled(context_course::instance($PAGE->course->id)) )
        {// Если включено отслеживание времени изучения курса
            // Если выбран продвинутый режим, добавим скрипт, отправляющий аякс запросы на добавление дополнительных логов присутствия
            $mode = local_learninghistory_get_course_config($PAGE->course->id, 'mode');
            if( $mode == 1 )
            {
                $delay = local_learninghistory_get_course_config($PAGE->course->id, 'delay');
                $PAGE->requires->js_call_amd('local_learninghistory/activetime_controller', 'init', [$delay, $USER->id, $PAGE->course->id, $PAGE->context->id]);
            }

            // Если включено отображение таймера, добавим фейковый блок с таймером
            $timer = local_learninghistory_get_course_config($PAGE->course->id, 'timer');
            if( $timer == 1 )
            {
                $refresh = (int)local_learninghistory_get_course_config($PAGE->course->id, 'timer_refresh');
                $totaltime = (int)local_learninghistory_get_course_config($PAGE->course->id, 'available_time');
                $PAGE->requires->js_call_amd('local_learninghistory/time_left', 'init', [$refresh, $totaltime, $USER->id, $PAGE->course->id]);
                $bc = new block_contents(['class' => 'block activetimer moodle-has-zindex']);
                
                if( $totaltime > 0 )
                {
                    $bc->title = get_string('until_the_course_is_closed', 'local_learninghistory');
                } else
                {
                    $bc->title = get_string('total_time_in_course', 'local_learninghistory');
                }
                
                $bc->content .= html_writer::start_div('content');
                $bc->content .= html_writer::div('', 'local_learninghistory_time_left');
                $bc->content .= html_writer::end_div();
                $canaddblock = true;
                $region = local_learninghistory_get_course_config($PAGE->course->id, 'region');
                if( empty($region) || ! $PAGE->blocks->is_known_region($region) )
                {
                    $region = $PAGE->blocks->get_default_region();
                }
                if( $context instanceof context_module )
                {
                    $modinfo = get_fast_modinfo($PAGE->course->id);
                    $cm = $modinfo->get_cm($context->instanceid);
                    if( $cm->modname == 'quiz' )
                    {
                        $attemptid = optional_param('attempt', 0, PARAM_INT);
                        if( $attemptid > 0 )
                        {
                            $attemptobj = quiz_attempt::create($attemptid);
                            if( empty($attemptobj->get_quiz()->showblocks) )
                            {
                                $canaddblock = false;
                            }
                        }
                    }
                }
                if( $canaddblock )
                {
                    try
                    {
                        $PAGE->blocks->add_fake_block($bc, $region);
                    } catch ( coding_exception $e )
                    {
                        // для отдельно взятых тем (boost на moodle 3.5) добавлять фейковый блок на текущий момент уже поздно
                        // что приводит к фаталу, поэтому поставлена заглушка
                    }
                }
            }
        }
    }
}


/**
 * Хук переопределения навигации настроек
 *
 * @param stdClass $settingsnav - Объект навигации
 * @param stdClass $context - Текущий контекст
 *
 * @return void
 */
function local_learninghistory_extend_settings_navigation($settingsnav, $context)
{
    global $PAGE;

    // Добавим новоую страницу настроек для страниц курса
    if ($PAGE->course && $PAGE->course->id != SITEID && isloggedin())
    {
        $coursecontext = context_course::instance($PAGE->course->id);
        // Добавим ссылку на настройки
        // Ссылку на страницу увидят люди только с соответствующими правами
        if (has_capability('local/learninghistory:activetimemanage', $coursecontext)) {
            if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE))
            {// Есть вкладка "Управление курсом"
                // Ссылка на страницу настроек
                $urlparams = ['id' => $PAGE->course->id];
                $url = new moodle_url('/local/learninghistory/activetime_settings.php', $urlparams);
                // Добавим новый пункт меню
                $node = navigation_node::create(
                    get_string('activetime_settings', 'local_learninghistory'),
                    $url,
                    navigation_node::NODETYPE_LEAF,
                    'activetime_manage',
                    'activetime_manage',
                    new pix_icon('i/settings', '')
                );
                if ($PAGE->url->compare($url, URL_MATCH_BASE))
                {
                    $node->make_active();
                }
                $settingnode->add_node($node);
            }
        }
    }
}
/**
 * Запуск пересчета времени затраченного на изучение курса
 * @param boolean $refresh флаг обновления, если указано true - сделает полный пересчет времени с момента начала подписки
 *                                          если указано false - добавит не посчитанное время к последнему посчитанному значению
 */
function local_learninghistory_check_activetime($refresh = false)
{
    global $DB, $CFG;
    $sql = 'SELECT courseid
        FROM {llhistory_properties}
        WHERE name = :name
        AND ' . $DB->sql_compare_text('value') . ' = ' . $DB->sql_compare_text(':value');
    $params = ['name' => 'activetime', 'value' => 1];
    $courses = $DB->get_records_sql($sql, $params);
    if( ! empty($courses) )
    {
        require_once($CFG->dirroot . '/local/learninghistory/classes/activetime.php');
        foreach($courses as $course)
        {
            $activetime = new local_learninghistory\activetime($course->courseid);
            if( ! empty($activetime) )
            {
                $activetime->check_activetime($refresh);
            }
        }
    }
}

/**
 * Получить дополнительные свойства курса
 *
 * @param int $courseid
 *            - ID курса
 * @param string $name
 *            - имя свойства
 * @param bool $multiple
 *            - комплексное свойство
 *
 * @return mixed - значение(я) свойства
 */
function local_learninghistory_get_course_config($courseid, $name, $multiple = false)
{
    global $DB;

    if ( $multiple )
    { // Настройка комплексная
        // Получим свойства
        $config = $DB->get_records('llhistory_properties', [
            'courseid' => $courseid,
            'name' => $name
        ]);
        return $config;
    } else
    { // Настройка состоит из 1 записи
        // Получим свойство
        $config = $DB->get_record('llhistory_properties', [
            'courseid' => $courseid,
            'name' => $name
        ]);
        if ( ! empty($config) )
        {
            return $config->value;
        } else
        {
            return false;
        }
    }
}