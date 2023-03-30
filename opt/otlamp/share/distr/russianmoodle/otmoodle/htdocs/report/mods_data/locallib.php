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
 * Блок объединения отчетов. Библиотека блока.
 * 
 * @package    block
 * @subpackage reports_union
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(realpath(__FILE__)) . '/../../config.php');
require_once($CFG->libdir . '/completionlib.php');

define('COMPLETION_IGNORE', -2);

define('ATTEMPT_COMPLETION_INCOMPLETE', 0);
define('ATTEMPT_COMPLETION_COMPLETE', 1);
define('ATTEMPT_COMPLETION_UNKNOWN', -1);
define('ATTEMPT_COMPLETION_IGNORE', -2);

/**
 * Получить список модулей, которые поддерживаются блоком
 */
function report_mods_data_get_supported_modules()
{
    $modules = [];
    $modules['quiz'] = [
                    'code' => 'quiz',
                    'xlslink' => '/mod/quiz/report.php?id=44&mode=overview&download=excel',
                    'urlparams' => ['download' => 'excel', 'mode' => 'overview']
    ];
    $modules['feedback'] = [
                    'code' => 'feedback',
                    'xlslink' => '/mod/feedback/analysis_to_excel.php',
                    'listname' => 'detailed'
    ];
    return $modules;
}

/**
 * Получить кастомные поля
 * @param string $shortname краткое имя кастомного поля
 */
function report_mods_data_get_custom_fields($shortname = '')
{
    global $DB;
    /**
     * @todo Сделать настройку по выбору списка типов кастомных полей
     */
    $fieldtypes = "'checkbox','datetime','menu','text','textarea'";
    $where = ' WHERE f.datatype IN (' . $fieldtypes . ') ';
    $params = [];
    if( ! empty($shortname) )
    {
        $where = 'AND f.shortname=? ';
        $params = [$shortname];
    }
    return $DB->get_records_sql("SELECT f.*
        FROM {user_info_field} f
        JOIN {user_info_category} c ON f.categoryid=c.id
        $where
        ORDER BY c.sortorder ASC, f.sortorder ASC", $params);
}

/**
 * Получение api работы с персонами Деканата
 *
 * @return object|bool - Плагин персон или false
 */
function report_mods_data_get_dof_persons_api()
{
    global $DB, $CFG;
    // Добавление полей деканата
    $dofexist = $DB->record_exists('block_instances', ['blockname' => 'dof']);
    if ( ! empty($dofexist) )
    {// Блок деканата найден в системе
        $plugin = block_instance('dof');
        if ( ! empty($plugin) )
        {// Экземпляр деканата получен
            // Подключение библиотек деканата
            require_once($CFG->dirroot .'/blocks/dof/locallib.php');
            global $DOF;
            // Проверка существования API
            $exist = $DOF->plugin_exists('storage', 'persons');
            if ( ! empty($exist) )
            {// API доступен
                // Проверка версии плагина
                $version = $DOF->storage('persons')->version();
                if ( $version >= 2015111100 )
                {// Версия плагина подход ит
                    return $DOF->storage('persons');
                }
            }
        }
    }
    return false;
}

/**
 * Возвращает объект dof
 * @return NULL|dof_control
 */
function report_mods_data_get_dof()
{
    global $CFG;
    $dof = null;
    if( file_exists($CFG->dirroot . '/blocks/dof/locallib.php') )
    {
        require_once($CFG->dirroot . '/blocks/dof/locallib.php');
        global $DOF;
        $dof = & $DOF;
    }
    return $dof;
}

/**
 * Вернуть статус выполнения элемента
 * @param stdClass $course объект курса
 * @param stdClass $cm объект модуля курса
 * @param int $userid идентификатор пользователя
 * @return int
 */
function report_mods_data_get_completion($course, $cm, $userid)
{
    if( ! empty($course->enablecompletion) && ! empty($cm->completion) )
    {
        $completioninfo = new completion_info($course);
        $completiondata = $completioninfo->get_data($cm, false, $userid);
        return $completiondata->completionstate;
    } else 
    {
        $completionmode = get_config('report_mods_data', 'completionmode');
        switch($completionmode)
        {
            case 'complete':
                return COMPLETION_COMPLETE;
                break;
            case 'ignore':
                return COMPLETION_IGNORE;
                break;
            case 'unknown':
                return COMPLETION_UNKNOWN;
                break;
            case 'incomplete':
            default:
                return COMPLETION_INCOMPLETE;
                break;
        }
    }
}

/**
 * Вернуть статус выполнения попытки прохождения элемента
 * @param int $attemptid идентификатор попытки
 * @param string $modname имя модуля
 * @param stdClass $course объект курса
 * @param stdClass $cm объект модуля курса
 * @param int $userid идентификатор пользователя
 * @return int
 */
function report_mods_data_get_attempt_completion($attemptid, $modname, $course, $cm, $userid)
{
    global $CFG;
    $filename = $CFG->dirroot . '/report/mods_data/classes/attemptcompletion/mod/' . $modname . '.php';
    if( file_exists($filename) )
    {
        require_once($filename);
        $classname = 'mods_data\attemptcompletion\mod\\' . $modname;
        $ac = new $classname($attemptid, $course, $cm, $userid);
        return $ac->get_attempt_completion();
    } else 
    {
        return report_mods_data_get_completion($course, $cm, $userid);
    }
}

function report_mods_data_attempt_completion_string($completion)
{
    switch($completion)
    {
        case ATTEMPT_COMPLETION_INCOMPLETE:
            return get_string('attempt_completion_incomplete', 'report_mods_data');
            break;
        case ATTEMPT_COMPLETION_COMPLETE:
            return get_string('attempt_completion_complete', 'report_mods_data');
            break;
        case ATTEMPT_COMPLETION_UNKNOWN:
        case ATTEMPT_COMPLETION_IGNORE:
        default:
            return get_string('attempt_completion_unknown', 'report_mods_data');
            break;
    }
}

function report_mods_data_require_any_capability($capabilities, context $context, $userid = null, $doanything = true,
    $errormessage = 'nopermissions', $stringfile = '') {
        if (! has_any_capability($capabilities, $context, $userid, $doanything)) {
            throw new required_capability_exception($context, $capability, $errormessage, $stringfile);
        }
}

/**
 * Автозагрузчик PHPWord библиотеки
 */
spl_autoload_register(function ($classname)
{
    if ( strpos($classname, 'Mpdf') !== false )
    {
        global $CFG;
        $classname = str_replace('Mpdf\\', '', $classname);
        $filepath =  $CFG->dirroot . '/report/mods_data/classes/lib/mpdf-7.1.6/src/' . str_replace('\\', '/', $classname) . '.php';
        if ( file_exists($filepath) )
        {
            require_once ($filepath);
        }
    }
    if ( strpos($classname, 'Psr\Log') !== false )
    {
        global $CFG;
        $classname = str_replace('Psr\Log\\', '', $classname);
        $filepath =  $CFG->dirroot . '/report/mods_data/classes/lib/log-1.0.2/Psr/Log/' . str_replace('\\', '/', $classname) . '.php';
        if ( file_exists($filepath) )
        {
            require_once ($filepath);
        }
    }
});
