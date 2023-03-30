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


defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

/**
 * Базовый класс сервиса
 *
 * @package    block_dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class dof_external_api_plugin_base
{
}

/**
 * Класс обработки запросов
 *
 * @package    block_dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_external_api extends external_api
{
    /**
     * Вызов метода сервиса и возврат ответа
     *
     * @param string $function A webservice function name.
     * @param array $args Params array (named params)
     * @param boolean $ajaxonly If true, an extra check will be peformed to see if ajax is required.
     * 
     * @return array containing keys for error (bool), exception and data.
     */
    public static function call_external_function($data, $args) 
    {
        global $PAGE, $COURSE, $CFG, $SITE;
        
        require_once($CFG->libdir . "/pagelib.php");
        
        $currentpage = $PAGE;
        $currentcourse = $COURSE;
        $response = array();
        
        try {
            // Валидация и получение данных для вызова функции сервиса
            $service_data = static::external_function_info($data);
        
            // Taken straight from from setup.php.
            if ( ! empty($CFG->moodlepageclass) ) {
                if (!empty($CFG->moodlepageclassfile)) {
                    require_once($CFG->moodlepageclassfile);
                }
                $classname = $CFG->moodlepageclass;
            } else {
                $classname = 'moodle_page';
            }
            $PAGE = new $classname();
            $COURSE = clone($SITE);
            $PAGE->set_context(context_course::instance(SITEID));
            
            // Do not allow access to write or delete webservices as a public user.
            if (defined('NO_MOODLE_COOKIES') && NO_MOODLE_COOKIES && !PHPUNIT_TEST) {
                throw new moodle_exception('servicenotavailable', 'webservice');
            }
            if (!isloggedin()) {
                throw new moodle_exception('servicenotavailable', 'webservice');
            } else {
                require_sesskey();
            }
            
            // Запуск сервисного метода
            $callable = array($service_data->classname, $service_data->methodname);
            $result = call_user_func_array($callable,
                    array_values($args));
            
            $response['error'] = false;
            $response['data'] = $result;
        } catch (Exception $e) {
            $exception = get_exception_info($e);
            unset($exception->a);
            $exception->backtrace = format_backtrace($exception->backtrace, true);
            if (!debugging('', DEBUG_DEVELOPER)) {
                unset($exception->debuginfo);
                unset($exception->backtrace);
            }
            $response['error'] = true;
            $response['exception'] = $exception;
        }
        
        // Вебсервисы могут изменять настройки $PAGE и $COURSE, поэтому сохраняем перед выполнением сервисной функции и присваиваем сохраненную после выполнения
        $PAGE = $currentpage;
        $COURSE = $currentcourse;
        
        return $response;
    }
    
    /**
     * Валидация и разбор строки функции на более детальную информацию для последующей обработки
     *
     * @param string $data - строка запрошенной функции
     * 
     * @return stdClass $description
     */
    public static function external_function_info($data = '') 
    {
        GLOBAL $DB, $CFG;
        
        if ( empty($data) )
        {// Пустые данные
            throw new coding_exception('Empty data');
        }
        
        // Названия класса
        $class_name = 'dof_external_api_plugin';
        
        $exploded_data = explode('_', $data, 2);
        if( count($exploded_data) == 2 && $exploded_data[0] == 'dof' )
        {
            // Сформирован вызов ajax-метода ядра деканата
            $class_path = $CFG->dirroot . '/blocks/dof/classes/external.php';
            $methodname = $exploded_data[1];
        } else
        {
        $exploded_data = explode('_', $data, 3);
        if ( count($exploded_data) !== 3 )
        {// Неверное название вызываемой функции сервиса
            throw new coding_exception('Invalid function name');
        }
        if ( ! $DB->record_exists('block_dof_plugins', ['type' => $exploded_data[0], 'code' => $exploded_data[1]]) )
        {// Плагин не существует
            throw new coding_exception('DOF plugin does not exist');
        }
        
            $class_path = $CFG->dirroot . '/blocks/dof/' . $exploded_data[0] . '/' . $exploded_data[1] . '/classes/external.php';
            $methodname = $exploded_data[2];
        }
        
        
        // Валидация
        if ( ! class_exists($class_name) ) 
        {
            if ( ! file_exists($class_path) ) 
            {
                throw new coding_exception('Cannot find file with external function implementation');
            }
            require_once($class_path);
            if ( ! class_exists($class_name) ) 
            {
                throw new coding_exception('Cannot find external class');
            }
        }
        if ( ! is_a($class_name, 'dof_external_api_plugin_base', true) )
        {
            throw new coding_exception('Invalid service implementation');
        }
        if ( ! method_exists($class_name, $methodname) ) 
        {
            throw new coding_exception('Missing implementation method of ' . $class_name . '::' . $methodname);
        }

        // Валидация прошла успешно
        // Формирование описания для последующей обработки
        $description = new stdClass();
        $description->classname = $class_name;
        $description->methodname = $methodname;

        return $description;
    }
}
