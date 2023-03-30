<?PHP
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
 * Панель управления доступом в СДО
 * 
 * Центральные функции плагина
 * 
 * @package    local_authcontrol
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/editlib.php');

/**
 * Хук переопределения навигации настроек
 * 
 * @param stdClass $settingsnav - Объект навигации
 * @param stdClass $context - Текущий контекст
 * 
 * @return void
 */
function local_authcontrol_extend_settings_navigation($settingsnav, $context) 
{
    global $CFG, $PAGE, $USER, $SITE;
    
    if ( ! empty($USER->id) && ! is_siteadmin($USER->id) )
    {// Проверим доступ пользователя
        $status = get_config('local_authcontrol', 'authcontrol_select');
        if ( ! empty($status) )
        {
            $courseid = null;
            $cmid = null;
            if ( $PAGE->course && $PAGE->course->id )
            {
                $courseid = $PAGE->course->id;
            }
            if ( $PAGE->cm && $PAGE->cm->id )
            {
                $cmid = $PAGE->cm->id;
            }
            local_authcontrol_check_user($USER->id, $courseid, $cmid);
        }
    }
    
    // Добавим новую страницу настроек для страниц курса
    if ( $PAGE->course && $PAGE->course->id != $SITE->id ) 
    {
        // Получение контекста текущего курса
        $context = context_course::instance($PAGE->course->id);
        
        // Проверка прав на работу с панелью управления
        if ( ! has_capability('local/authcontrol:use', $context) && 
             ! has_capability('local/authcontrol:view', $context) )
        {// У пользователя отсутствует доступ к интерфейсу
            return null;
        }
       
        if ( $settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE) ) 
        {// Вкладка "Управление курсом" найдена
            
            // Ссылка на страницу настроек
            $url = new moodle_url(
                '/local/authcontrol/controlpage.php', 
                ['id' => $PAGE->course->id]
            );
            
            // Добавим новый пункт меню
            $node = navigation_node::create(
                get_string('additional_authcontrol_coursesettings', 'local_authcontrol'),
                $url,
                navigation_node::NODETYPE_LEAF,
                'additionalauthcontrolcoursesettings',
                'additionalauthcontrolcoursesettings',
                new pix_icon('i/settings', '')
            );
            if ( $PAGE->url->compare($url, URL_MATCH_BASE) ) 
            {
                $node->make_active();
            }
            $settingnode->add_node($node);
        }
    }
}

/**
 * Проверка текущей сессии пользователя
 * 
 * Проверка сесси пользователя на доступ к СДО. 
 * Пользователю будет закрыт доступ, если такой аккаунт уже авторизован 
 * в системе(сессия не первая), или же в панели управления ему запрещена авторизация
 * 
 * @param int $userid
 * 
 * @return boolean
 */
function local_authcontrol_check_sessions($userid = null) 
{
    global $DB;
    
    // Нормализация данных
    if ( empty($userid) || ! is_numeric($userid) )
    {
        return false;
    }
    
    // Получим сессии пользователя
    $sessions = (array)$DB->get_records('sessions', ['userid' => $userid], 'timecreated DESC', 'sid');
    if ( ! empty($sessions) && count($sessions) > 1 )
    {// Оставим первую сессию, остальные убьем
        array_pop($sessions);
        foreach ( $sessions as $session ) 
        {
            // Сброс сессии пользователя
            \core\session\manager::kill_session($session->sid);
        }
    }
    
    // Успешно обработали сессии пользователя
    return true;
}

/**
 * Проверка доступа пользователя в целевой курс
 * 
 * @param context_course $context - Контекст курса
 * @param stdClass $user - Объект пользователя
 * 
 * @return boolean
 */
function local_authcontrol_can_view_course($context = null, $user = null)
{
    global $USER;
    
    // Нормализация данных
    if ( empty($context) || ! ( $context instanceof context_course ) )
    {
        return false;
    }
    
    if( is_null($user) )
    {
        $user = clone($USER);
    }
    
    // Проверка права доступа 
    return (
        has_capability('local/authcontrol:use', $context, $user->id) || 
        has_capability('local/authcontrol:view', $context, $user->id)
    );
}

/**
 * Получим модуль курса для пользователя
 * @param int $courseid
 * @param int $userid
 * @param boolean $ignore_visible
 * @return boolean|stdClass
 */
function local_authcontrol_get_module($courseid = null, $userid = null, $ignore_visible = false)
{
    global $DB;

    // Нормализация данных
    if ( empty($courseid) || ! is_numeric($courseid) )
    {
        return false;
    }
    if ( empty($userid) || ! is_numeric($userid) )
    {
        return false;
    }

    if ( $record = $DB->get_record('authcontrol_access_users', ['courseid' => $courseid, 'userid' => $userid]) )
    {
        if ( ! empty($record->moduleid) && ! empty($record->modulename) )
        {
            // Проверим, что такой модуль курса существует и вернем false, если его нету
            try 
            {
                $cm_info = get_course_and_cm_from_cmid($record->moduleid, '', '', $userid)[1];
                if ( ! empty($cm_info->uservisible) || $ignore_visible )
                {
                    return $cm_info->get_course_module_record(true);
                }
            }
            catch (dml_exception $e) 
            {
                // Модуль курса не сущестует
                local_authcontrol_user_restore($record->id);
                return false;
            }
        }
    }
    return false;
}

/**
 * Получение информации об области системы, в которой открыт доступ пользователю
 * 
 * @param int $userid
 * 
 * @return array|boolean
 */
function local_authcontrol_get_context_info($userid = null)
{
    global $DB;
    
    $info = [];
    $info['context'] = null;
    $info['course'] = null;
    $info['module'] = null;
    
    // Проверим
    if ( empty($userid) || ! is_numeric($userid) )
    {
        return false;
    }
    if ( $record = $DB->get_record('authcontrol_access_users', ['userid' => $userid, 'status' => 1]) )
    {
        $coursedeleted = false;
        try 
        {
            $course = get_course($record->courseid);
        } catch ( dml_exception $e )
        {
            $coursedeleted = true;
            $course = new stdClass();
            $course->fullname = get_string('course_not_exists', 'local_authcontrol');
        }
        $info['context'] = get_string('course', 'local_authcontrol');
        $info['course'] = $course->fullname;
        if ( ! empty($record->moduleid) && !$coursedeleted )
        {
            $cm = local_authcontrol_get_module($record->courseid, $userid, true);
            if ( ! empty($cm) )
            {
                $info['context'] = get_string('course', 'local_authcontrol');
                $info['module'] = $cm->name;
            }
        }
    }

    return $info;
}

/**
 * Получение данных об области системы, в которой открыт доступ пользователю
 *
 * @param int $courseid
 * @param []int $userids
 *
 * @return array|boolean
 */
function local_authcontrol_get_context_info_data($courseid, $userids = [])
{
    global $DB;
    if ( empty($courseid) || ! is_numeric($courseid) )
    {
        return false;
    }
    list($insql, $params) = $DB->get_in_or_equal($userids);
    
    $sql = 'SELECT * FROM {authcontrol_access_users} WHERE courseid = ? AND status = 1 AND userid ' . $insql;
    $params = array_merge([$courseid], $params);
    $records = $DB->get_records_sql($sql, $params);
    return $records;
}

/**
 * Сохранение настроек доступа пользователей в СДО
 * 
 * @param int $courseid - ID курса для ограничения зоны доступа
 * @param array $users - ID пользователей со статусом
 * @param int $cmid - ID модуля курса для ограничения зоны доступа
 * @param int $accessstate - Статус доступности СДО
 *      (1 - Открыть доступ\0 - Закрыть доступ)
 * 
 * @return boolean
 */
function local_authcontrol_save_access_info($courseid = 0, $users = [], $cmid = 0, $openaccess = 1)
{
    global $DB;
    
    // Валидация входных данных
    if ( empty($courseid) || ! is_numeric($courseid) )
    {
        return false;
    }
    if ( empty($users) || ! is_array($users) )
    {
        return false;
    }
    
    // Сборка данных для сохранения информации по доступу к СДО
    $accessdata = new stdClass();
    $accessdata->courseid = (int)$courseid;
    $accessdata->status = 0;
    $accessdata->moduleid = 0;
    $accessdata->modulename = '';
    if ( (int)$openaccess > 0 )
    {// Доступ к СДО открыт
        
        $accessdata->status = 1;
        // Уточнение области доступа
        if ( ! empty($cmid) && is_numeric($cmid) )
        {// Указана зона модуля курса
            $cm = get_module_from_cmid($cmid)[1];
            $accessdata->moduleid = $cmid;
            $accessdata->modulename = $cm->modname;
        }
    }
    
    // Получение контекста курса
    $context = context_course::instance((int)$courseid);
    
    foreach ( $users as $userid )
    {
        // Проверка доступа пользователя к указанной зоне
        if ( local_authcontrol_under_controlled($userid, $context) )
        {// Доступ к указанной зоне присутствует
            
            // Блокировка всех ранее выданных доступов
            local_authcontrol_close_user_accesses($userid);
            
            unset($accessdata->userid);
            unset($accessdata->id);
            $accessdata->userid = $userid;
            
            // Сохранение настроек
            $useraccess = $DB->get_record(
                'authcontrol_access_users', 
                ['userid' => $userid, 'courseid' => $courseid]
            );
            if ( $useraccess )
            {// Процесс обновления записи
                $accessdata->id = $useraccess->id;
                $DB->update_record('authcontrol_access_users', $accessdata);
            }  else 
            {// Процесс добавления записи
                $DB->insert_record('authcontrol_access_users', $accessdata);
            }
        }
    }
    return true;
}

/**
 * Проверка наличия у пользователя открытой зоны доступа в СДО
 * 
 * @param int $userid
 * 
 * @return boolean
 */
function local_authcontrol_user_has_context($userid = null)
{
    global $DB;
    
    // Валидация
    if ( empty($userid) || ! is_numeric($userid) )
    {
        return false;
    }
    
    // Получение доступа пользователя
    $access = $DB->get_record(
        'authcontrol_access_users', 
        ['userid' => $userid, 'status' => 1]
    );
    if ( $access )
    {
        $cm = null;
        
        // Проверка существования курса
        try 
        {
            $course = get_course($access->courseid);
        } catch (dml_exception $e) 
        {// Курс не найден
            
            // Сброс целевого доступа
            local_authcontrol_user_restore($access->id);
            return false;
        }
        
        if ( ! empty($access->moduleid) )
        {
            // Проверка существования модуля
            $cm = local_authcontrol_get_module($access->courseid, $userid);
            if ( empty($cm) )
            {// Модуль курса не найден
                return false;
            }
        }
        
        // Проверка доступа в целевую зону
        if ( ! empty($course->visible) )
        {// Доступ открыт в открытый курс
            if ( $cm )
            {
                if ( ! empty($cm->visible) )
                {// Доступ к модулю открыт
                    return true;
                }
            } else 
            {// Доступ к курсу открыт
                return true;
            }
        }
    }
    return false;
}

/**
 * Закрыть все доступы для пользователя
 * 
 * @param int $userid
 * 
 * @return boolean
 */
function local_authcontrol_close_user_accesses($userid = null)
{
    global $DB;

    // Валидация
    if ( empty($userid) || ! is_numeric($userid) )
    {
        return false;
    }

    $access = (array)$DB->get_records('authcontrol_access_users', ['userid' => $userid, 'status' => 1]);
    if ( ! empty($access) )
    {
        foreach ( $access as $record )
        {
            $update_record = new stdClass();
            $update_record->id = $record->id;
            $update_record->status = 0;
            $update_record->moduleid = '';
            $update_record->modulename = '';
            // Обновим запись
            $DB->update_record('authcontrol_access_users', $update_record);
        }
    }
    return true;
}

/**
 * Принудительный выход из системы
 * 
 * @param int $userid
 * 
 * @return boolean
 */
function local_authcontrol_kill_user_sessions($userid = null)
{
    global $DB;
    
    // Нормализация данных
    if ( empty($userid) || ! is_numeric($userid) )
    {
        return false;
    }

    // Убьем все сессии пользователя
    \core\session\manager::kill_user_sessions($userid);
    return true;
}

/**
* Редирект целевого пользователя в зону доступа СДО
* 
* @param int $userid
* @param int $courseid - Текущий курс пользователя
* @param int $cmid - Текущий модуль курса пользователя
* 
* @return void
*/
function local_authcontrol_user_redirect($userid = null, $courseid = null, $cmid = null)
{
    global $DB;

    // Проверим
    if ( empty($userid) || ! is_numeric($userid) )
    {
        return false;
    }

    $access = $DB->get_record(
        'authcontrol_access_users', 
        ['userid' => $userid, 'status' => 1]
    );
    if ( $access )
    {// Найден открытый доступ в СДО
        if ( ! empty($access->moduleid) )
        {
            // Получение информации о зоне доступа пользователя
            $info = get_course_and_cm_from_cmid($access->moduleid);
            
            if ( ! empty($cmid) && is_numeric($cmid) )
            {// Указано текущее местоположение пользователя
                if ( $cmid != $access->moduleid )
                {// Пользователь в заблокированной зоне
                    if ( isset($info[1]) )
                    {// Редирект в открытую зону
                        local_authcontrol_redirect($info[1]->url);
                    } else 
                    {// Сброс пользовательской сессии
                        local_authcontrol_kill_user_sessions($userid);
                    }
                }
            } else
            {// Текущее местоположение не указано
                if ( isset($info[1]) )
                {// Редирект в открытую зону
                    local_authcontrol_redirect($info[1]->url);
                } else 
                {// Сброс пользовательской сессии
                    local_authcontrol_kill_user_sessions($userid);
                }
            }
        } else 
        {// Указано ограничение по курсу
            if ( ! empty($courseid) && is_numeric($courseid) )
            {
                if ( $courseid != $access->courseid )
                {
                    local_authcontrol_redirect(new moodle_url('/course/view.php', ['id' => $access->courseid]));
                }
            } else 
            {
                local_authcontrol_redirect(new moodle_url('/course/view.php', ['id' => $access->courseid]));
            }
        }
    }
}


/**
 * Редирект на указанный урл
 * 
 * @param moodle_url $url
 */
function local_authcontrol_redirect(moodle_url $url)
{
    try {
        redirect($url);
    } catch(coding_exception $ex)
    {// Если возникла ошибка при попытке обработки ситуации мудлом, пытаемся редиректить по-своему, игнорируя советы мудла
        ob_clean();
        header('Location: ' . $url->out());
        exit;
    }
}

/**
 * ПРоверка доступа пользователя
 * 
 * @param int $userid
 * @param int $courseid
 * @param int $cmid
 * 
 * @return boolean
 */
function local_authcontrol_check_user($userid = null, $courseid = null, $cmid = null)
{
    // Валидация
    if ( empty($userid) || ! is_numeric($userid) || is_object($courseid) || is_object($cmid) )
    {
        return false;
    }
    
    // Получение курсов, в которых активирована подсистема ограничения доступа для указанного пользователя
    $controlled_courses = get_user_capability_course('local/authcontrol:access_control', $userid);
    if ( ! empty($controlled_courses) )
    {// Пользователь находится под контролем подсистемы ограничения доступа
        
        // Проверка статуса подсистемы контроля сессий
        $status_session = get_config('local_authcontrol', 'authcontrol_select_session');
        if ( ! empty($status_session) )
        {// Подсистема  контроля сессий активна
            // Валидация пользовательской сессии
            local_authcontrol_check_sessions($userid);
        }
        
        // Проверка области доступа пользователя
        if ( ! local_authcontrol_user_has_context($userid) )
        {// Пользователь не имеет открытой зоны доступа в СДО
            // Сброс авторизации пользователя в СДО
            local_authcontrol_kill_user_sessions($userid);
            // Перенаправим пользователя на домашнюю страницу
            redirect(new moodle_url('/'));
        } else
        {// Пользователь открыта зона СДО
            // Редирект в открытую зону 
            local_authcontrol_user_redirect($userid, $courseid, $cmid);
        }
    }
    return true;
}

/**
 * Проверка состояния системы контроля доступа для указанного пользователя
 * 
 * @param int $userid - ID пользователя
 * @param context_course $course_context
 * 
 * @return boolean
 */
function local_authcontrol_under_controlled($userid = null, $course_context = null) 
{
    // Нормализация данных
    if ( empty($userid) || ! is_numeric($userid) )
    {
        return false;
    }
    // Нормализация данных
    if ( empty($course_context) || ! ($course_context instanceof context_course) )
    {
        return false;
    }
    
    return (
        has_capability('local/authcontrol:access_control', $course_context, $userid) && 
        ! has_capability('local/authcontrol:use', $course_context, $userid)
    );
}

/**
 * Нормализация доступа пользователя
 * 
 * Если у пользователя открыт доступ в удаленный курс/модуль, удалим запись
 * 
 * @param int $accessid
 * 
 * @return boolean
 */
function local_authcontrol_user_restore($accessid = null)
{
    global $DB;
    
    // Нормализация данных
    if ( empty($accessid) || ! is_numeric($accessid) )
    {
        return false;
    }

    return $DB->delete_records('authcontrol_access_users', ['id' => $accessid]);
}

/**
 * Вернуть число пользователей онлайн.
 *
 * @param array - Массив опций подсчета пользователей онлайн
 *          ['updatecache'] = true - Обновить кэш
 *
 * @return int - Число пользователей онлайн или NULL, если подсчет невозможен
 */
function local_authcontrol_count_users_online($options = [])
{
    global $DB;
    
    // Формирование параметров для определения онлайн пользователей
    $now = time();
    $params = [];
    $params['timefrom'] = $now - 300;
    $params['now'] = $now;
    
    // Формирование запроса
    $sql = "SELECT COUNT(id)
            FROM {user}
            WHERE lastaccess > :timefrom
            AND lastaccess <= :now
            AND deleted = 0";
    // Подсчет пользователей
    return $DB->count_records_sql($sql, $params);
}

/**
 * Проверяет находится ли пользователь онлайн
 * @param int $userid идентификатор пользователя
 * @return bool если пользователь онлайн, возвращает true, если нет - false
 */
function local_authcontrol_is_online($userid)
{
    global $DB;

    $now = time();
    $params = [];
    $params['timefrom'] = $now - 300;
    $params['now'] = $now;
    $params['userid'] = $userid;
    $params['deleted'] = 0;
    // Формирование запроса
    $sql = "SELECT COUNT(id)
                 FROM {user}
                 WHERE lastaccess > :timefrom
                 AND lastaccess <= :now
                 AND id = :userid
                 AND deleted = :deleted
                ";
    return (bool)$DB->count_records_sql($sql, $params);
}


