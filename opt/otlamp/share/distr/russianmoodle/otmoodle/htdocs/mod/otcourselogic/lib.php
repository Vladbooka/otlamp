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
 * Модуль Логика курса. Библиотека функций плагина.
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

use mod_otcourselogic\state_checker;

/**
 * Список поддерживаемых инструментов
 * 
 * @param string $feature - Константа инструмента
 * 
 * @return bool|null True - Поддержка целевого инструмента модулем
 */
function otcourselogic_supports($feature) 
{
    switch($feature) 
    {
        case FEATURE_IDNUMBER :                return false;
        case FEATURE_GROUPS :                  return false;
        case FEATURE_GROUPINGS :               return false;
        case FEATURE_GROUPMEMBERSONLY :        return false;
        case FEATURE_MOD_INTRO :               return false;
        // Поддержка условия выполнения по клику на элемент курса
        case FEATURE_COMPLETION_TRACKS_VIEWS:  return false;
        // Определение уникальных условий выполнения
        case FEATURE_COMPLETION_HAS_RULES:     return true;
        case FEATURE_GRADE_HAS_GRADE :         return true;
        case FEATURE_GRADE_OUTCOMES :          return false;
        case FEATURE_MOD_ARCHETYPE :           return MOD_ARCHETYPE_OTHER;
        // Поддержка бэкапа
        case FEATURE_BACKUP_MOODLE2 :          return true;
        case FEATURE_NO_VIEW_LINK :            return false;
        default :                              return null;
    }
}

/**
 * Процесс добавления нового элемента курса
 * 
 * @param object $data - Данные формы создания элемента курса
 * @param object $mform - Форма создания элемента курса
 * 
 * @return int - ID созданного элемента курса
 */
function otcourselogic_add_instance($data, $mform) 
{
    global $DB;
    
    // Формирование значений по умолчанию
    $instance = new stdClass();
    $instance->course = (int)$data->course;
    $instance->name = get_string('modulename', 'mod_otcourselogic');
    $instance->checkperiod = 86400;
//     $instance->activecond = 'active';
    $instance->catchstatechange = 0;
    $instance->catchcourseviewed = 0;
    $instance->studentshide = 0;
    $instance->redirectmessage = null;
    $instance->redirecturl = null;
    $instance->timecreated = time();
    $instance->completionstate = null;
    $instance->grading = false;
    
    // Формирование элемента для сохранения
    _otcourselogic_set_instance_data($instance, $data);
    
    // Сохранение элемента
    if ( $instanceid = $DB->insert_record('otcourselogic', $instance) )
    {// Сохранение элемента прошло успешно 
        $instance = $DB->get_record(
            'otcourselogic',
            ['id' => $instanceid]
        );
        if( ! empty($instance) )
        {
            otcourselogic_grade_item_update($instance);
            return $instanceid;
        } else 
        {
            return false;
        }
    }
    return false;
}

/**
 * Процесс обновления элемента курса
 * 
 * @param object $data - Данные формы создания элемента курса
 * @param object $mform - Форма создания элемента курса
 * 
 * @return int|bool - ID обновленного элемента курса или false
 */
function otcourselogic_update_instance($data, $mform) 
{
    global $DB;
    
    // Получение данных об элементе
    $instance = $DB->get_record("otcourselogic", [
        "id" => $data->instance
    ]);
    
    // Формирование элемента для сохранения
    _otcourselogic_set_instance_data($instance, $data);
    
    // Сохранение элемента
    if ( $DB->update_record('otcourselogic', $instance) )
    {// Сохранение элемента прошло успешно
        otcourselogic_grade_item_update($instance);
        return $instance->id;
    }
    return false;
}

/**
 * Процесс удаления элемента курса
 *
 * @param int $id - INSTANCEID элемента курса
 *
 * @return bool - Результат удаления
 */
function otcourselogic_delete_instance($id)
{
    global $DB, $CFG;

    $deleteresult = false;
    
    if ( ! $instance = $DB->get_record("otcourselogic", ["id" => (int)$id]) )
    {
        return false;
    }
    
    // Удаление данных о элементе модуля курса
    try {
        $gradeupdateresult = otcourselogic_grade_item_update($instance, 'delete');
        if( $gradeupdateresult !== GRADE_UPDATE_FAILED )
        {
            $deleteresult = $DB->delete_records('otcourselogic', ['id' => (int)$id]);
        }
    } catch(Exception $ex) {
        $deleteresult = false;
    }
    
    return $deleteresult;
}

/**
 * Процесс формирования данных для сохранеия элемента курса
 *
 * @param object $instance - Указатель на элемент модуля курса
 * @param object $data - Данные формы создания элемента курса
 *
 * @return void
 */
function _otcourselogic_set_instance_data(&$instance, $data)
{
    // Время последнего редактирования
    $instance->timemodified = time();
    
    // Имя элемента
    if ( trim($data->name) )
    {
        $name = strip_tags(format_string($data->name, true));
        if (core_text::strlen($name) > 250)
        {
            $name = core_text::substr($name, 0, 250)."...";
        }
        $instance->name = $name;
    }
    
    // Определения условия активности элемента
//     if ( (int)$data->active_state == 0 )
//     {// Элемент активен, пока не доступен пользователю
//         $instance->activecond = 'notactive';
//     } elseif ( (int)$data->active_state == 1 )
//     {// Элемент активен, пока доступен пользователю
//         $instance->activecond = 'active';
//     }
    
//     // Период отсрочки активации логики курса
//     if ( (int)$data->activating_delay )
//     {
//         $instance->activatingdelay = (int)$data->activating_delay;
//     } else
//     {
//         $instance->activatingdelay = 0;
//     }
    
    // Защита от случайных срабатываний
    if ( ! empty($data->protect) )
    {
        $instance->protect = 1;
    } else
    {
        $instance->protect = 0;
    }
    
    // Определение периодической проверки состояния элемента
    if ( (int)$data->check_period < 0 )
    {// Отключение периодической проверки
        $instance->checkperiod = null;
    } else
    {// Установка периода проверки
        $instance->checkperiod = (int)$data->check_period;
    }
    
    // Установка дополнительных триггеров проверки
    if ( (int)$data->check_event_state_switched )
    {
        $instance->catchstatechange = 1;
    } else 
    {
        $instance->catchstatechange = 0;
    }
    if ( (int)$data->check_event_course_viewed )
    {
        $instance->catchcourseviewed = 1;
    } else 
    {
        $instance->catchcourseviewed = 0;
    }
    
    // Скрывать элемент от студентов
    if ( (int)$data->display_to_students )
    {
        $instance->studentshide = 1;
    } else 
    {
        $instance->studentshide = 0;
    }

    // Сообщение при редиректе
    if( is_array($data->delivery_redirect_message) )
    {
        $clean = trim(strip_tags($data->delivery_redirect_message['text']));
        if ( $clean )
        {// Сообщение передано
            $instance->redirectmessage = $data->delivery_redirect_message['text'];
        } else 
        {
            $instance->redirectmessage = '';
        }
    } else 
    {
        $clean = trim(strip_tags($data->delivery_redirect_message));
        if ( $clean )
        {// Сообщение передано
            $instance->redirectmessage = $data->delivery_redirect_message;
        } else 
        {
            $instance->redirectmessage = '';
        }
    }
    
    // URL редиректа
    $instance->redirecturl = clean_param($data->delivery_redirect_url, PARAM_URL);
    
    // Условие выполнения элемента курса
    if ( isset($data->completionstateenabled) && $data->completionstateenabled )
    {
        $instance->completionstate = $data->completionstate;
    } else 
    {
        $instance->completionstate = null;
    }
    
    if( ! empty($data->grading_enabled) )
    {
        $instance->grading = 1;
    } else
    {
        $instance->grading = 0;
    }
}

/**
 * Процесс очистки данных пользователя в курсе
 *
 * @param object $data - Данные для очистки курса
 * 
 * @return array 
 */
function otcourselogic_reset_userdata($data) 
{
    global $DB;

    if ( ! empty($data->reset_gradebook_grades) )
    {
        otcourselogic_reset_gradebook($data->courseid);
    }
    
    // Статус обработки
    $statuses = [];
    
    if ( isset($data->otcourselogic_reset_states) && $data->otcourselogic_reset_states )
    {// Требуется сброс состояний элементов курса
        
        $componentstr = get_string('modulenameplural', 'otcourselogic');
        // Получение всех экземпляров модуля otcourselogic из курса
        $instances = (array)$DB->get_records(
            'otcourselogic',
            ['course' => $data->courseid]
            );
        if ( ! empty($instances) )
        {
            // Обработка каждого элемента курса
            foreach ( $instances as $instanceid => $instance )
            {
                // Результат очистки по умолчанию
                $status = [
                    'component' => $componentstr,
                    'item' => get_string(
                        'form_reset_course_userstates_were_reset', 
                        'otcourselogic', 
                        $instance->name
                    ),
                    'error' => false
                ];
                
                // Попытка удаления состояний
                try {
                    $DB->delete_records(
                        'otcourselogic_state',
                        ['instanceid' => $instanceid]
                    );
                } catch ( dml_exception $e )
                {// Ошибка при удалении состояний
                    $status['error'] = true;
                    $status['item'] = get_string(
                        'form_reset_course_userstates_were_not_reset', 
                        'otcourselogic', 
                        $instance->name
                    );
                }
                
                // Повторная инициализация пользоваельских состояний
                // Получение контроллера состояний
                $statechecker = otcourselogic_get_state_checker();
                // Инициализация состояний элемента для пользователей
                $statechecker->init_cm($instanceid);
                
                $statuses[] = $status;
            }
        }
    }
    return $statuses;
}

/**
 * Блок формы очистки курса с данными по модулю
 * 
 * @param moodleform $mform - Объект формы очистки курса
 */
function otcourselogic_reset_course_form_definition(&$mform) 
{
    // Заголовок формы
    $mform->addElement(
        'header', 
        'otcourselogicheader', 
        get_string('modulenameplural', 'otcourselogic')
    );
    // Запрос на очистку состояний
    $mform->addElement(
        'advcheckbox', 
        'otcourselogic_reset_states', 
        get_string('form_reset_course_reset_state_label', 'otcourselogic')
    );
}

/**
 * Заполнение значениями по умолчанию для формы очистки курса
 * 
 * @param object $course - Объект курса
 * 
 * @return array - Массив значений по умолчанию
 */
function otcourselogic_reset_course_form_defaults($course) 
{
    return ['otcourselogic_reset_states' => 0];
}

/**
 * Сформировать список прав, используемых в элементе курса
 *
 * @return array - Список прав
 */
function otcourselogic_get_extra_capabilities()
{
    return [];
}

/**
 * Сформировать дополнительную информацию для отображения элемента в курсе
 *
 * @param object $coursemodule - Элемент курса
 * 
 * @return cached_cm_info|null
 */
function otcourselogic_get_coursemodule_info($coursemodule) 
{
    global $DB, $USER;
    
    $instance = $DB->get_record('otcourselogic', ['id' => $coursemodule->instance]);
    
    // Получение названия элемента курса
    $name = trim($instance->name);
    if ( empty($name) )
    {// Название не указано
        $name = get_string('modulename', 'mod_otcourselogic');
    }
    
    // Формирование контента
    $info = new cached_cm_info();
    $info->name  = $name;
    return $info;
}

function otcourselogic_cm_info_dynamic(cm_info $cm) {
    global $DB, $USER;
    
    $instance = $DB->get_record('otcourselogic', ['id' => $cm->instance]);
    $context = context_module::instance($cm->id);

    // Получение текущего состояния
    $statechecker = otcourselogic_get_state_checker();
    $statestring = $statechecker->get_state_string($instance->id, $USER->id);
    
    $cm->set_content(get_string('shortuserstate', 'mod_otcourselogic', $statestring));
    
    if( $instance->studentshide == 1 && !has_capability('mod/otcourselogic:view_student_states', $context) )
    {
        $cm->set_extra_classes('otcourselogic_hide ');
    }
}

/**
 * Определеить завершение элемента курса на основе условий
 *
 * @param object $course - Объект курса
 * @param object $cm - Объект элемента курса
 * @param int $userid - ID пользователя, для которго проверяется завершение элемента
 * @param bool $type - Тип проверки (и/или)
 * 
 * @return bool - True, если элемент завершен и false, если нет
 */
function otcourselogic_get_completion_state($course, $cm, $userid, $type) 
{
    global $DB;
    
    $instance = $DB->get_record('otcourselogic', ['id' => $cm->instance]);
    
    // Получение текущего состояния
    $statechecker = otcourselogic_get_state_checker();
    // Запрет получения состояния, если оно не было определено ранее
    $userstate = (int)$statechecker->get_state($cm->instance, $userid);
    
    if ( $userstate !== null && $userstate === 1 && (int)$instance->completionstate === 0 )
    {// Пользовательский статус Активен 
        return true;
    }
    if ( $userstate !== null && $userstate === 0 && (int)$instance->completionstate === 1 )
    {// Пользовательский статус не активен 
        return true;
    }
    if ( $instance->completionstate === null )
    {// Элемент не включен
        return $type;
    }
    return false;
}

/**
 * Получение контроллера состояний
 *
 * @return mod_otcourselogic\state_checker
 */
function otcourselogic_get_state_checker() 
{
    static $statechecker = null;
    
    if ( $statechecker ) 
    {// Механизм ранее определен
        return $statechecker;
    }
    
    // Инициализация контроллера состояний
    $statechecker = new mod_otcourselogic\state_checker();
    
    return $statechecker;
}

/**
 * Обертка для проверки состояния элемента для пользователя
 * 
 * @param int  $instanceid - идентификатор инстанса
 * @param int  $courseid - идентификатор курса
 * @param int  $userid   - идентификатор пользователя
 * @param bool $checkvisible проверять доступность элемента или нет
 * @return void
 */
function otcourselogic_check_user_state($instanceid, $courseid = null, $userid, $checkvisible = true)
{
    // Нормализация входящих данных
    if( is_object($instanceid) )
    {
        if( ! empty($instanceid->id) )
        {
            $instanceid = $instanceid->id;
        } else 
        {
            throw new moodle_exception('invalid_instance_id', 'mod_otcourselogic', '', null, get_string('invalid_instance_id', 'mod_otcourselogic'));
        }
    }
    
    if( is_object($courseid) )
    {
        if( ! empty($courseid->id) )
        {
            $courseid = $courseid->id;
        } else
        {
            throw new moodle_exception('invalid_course_id', 'mod_otcourselogic', '', null, get_string('invalid_course_id', 'mod_otcourselogic'));
        }
    }
    
    if( is_object($userid) )
    {
        if( ! empty($userid->id) )
        {
            $userid = $userid->id;
        } else
        {
            throw new moodle_exception('invalid_user_id', 'mod_otcourselogic', '', null, get_string('invalid_user_id', 'mod_otcourselogic'));
        }
    }
    
    // Если не передали идентификатор курса, а доступность элемента проверять надо
    if( is_null($courseid) && $checkvisible )
    {
        throw new moodle_exception('invalid_course_id', 'mod_otcourselogic', '', null, get_string('invalid_course_id', 'mod_otcourselogic'));
    }
    
    // Инициализация флага запуска определения состояния элемента для целевого пользователя
    $check = true;
    
    if( $checkvisible )
    {
        $cm = get_coursemodule_from_instance('otcourselogic', $instanceid, $courseid);
        $check = (bool)$cm->visible;
    } else 
    {
        $check = true;
    }
    
    if ( $check )
    {
        // Получение контроллера состояний
        $statechecker = otcourselogic_get_state_checker();
        // Определение состояния элемента для целевого пользователя
        $statechecker->check_cm_user($instanceid, $userid);
    }
}

/**
 * Обновление оценок
 * 
 * @param stdClass $modinstance
 * @param array|string $grades - массив с данными по оценкам или строка-команда на удаление оценок
 */
function otcourselogic_grade_item_update($modinstance, $grades=NULL)
{
    global $CFG;
    if (!function_exists('grade_update')) { //workaround for buggy PHP versions
        require_once($CFG->libdir.'/gradelib.php');
    }

    if ( ! isset($modinstance->cmidnumber) || is_null($modinstance->cmidnumber))
    {
        $modinstance->cmidnumber = '';
    }
    
    $params = [
        'itemname' => $modinstance->name,
        'idnumber' => $modinstance->cmidnumber
    ];
    
    if ( ! empty($modinstance->grading) ) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax'] = 1;
        $params['grademin'] = 0;
    } else {
        $params['gradetype'] = GRADE_TYPE_NONE;
    }
    
    if ($grades  === 'reset') {
        $params['reset'] = true;
        $grades = NULL;
    }
    
    if ($grades  === 'delete') {
        $params['deleted'] = 1;
        $grades = NULL;
    }
    
    return grade_update('mod/otcourselogic', $modinstance->course, 'mod', 'otcourselogic', $modinstance->id, 0, $grades, $params);
}

/**
 * Обновление оценок пользователя
 * 
 * @param stdClass $modinstance
 * @param int $userid
 * @param bool $nullifnone
 */
function otcourselogic_update_grades($modinstance, $userid=0, $nullifnone=true)
{
    if ($grades = otcourselogic_get_user_grades($modinstance, $userid)) 
    {
        otcourselogic_grade_item_update($modinstance, $grades);
    } else if ($userid and $nullifnone) 
    {
        $grade = new stdClass();
        $grade->userid   = $userid;
        $grade->rawgrade = NULL;
        otcourselogic_grade_item_update($modinstance, $grade);
    } else 
    {
        otcourselogic_grade_item_update($modinstance);
    }
}

/**
 * Сброс оценок при очистке курса
 * 
 * @param int $courseid - идентификатор курса
 * @param string $type
 */
function otcourselogic_reset_gradebook($courseid, $type='')
{
    global $DB;
    

    $sql = 'SELECT otcl.*, cm.idnumber as cmidnumber, otcl.course as courseid
            FROM {otcourselogic} otcl, {course_modules} cm, {modules} m
            WHERE 
                m.name=:moduletype AND 
                m.id=cm.module AND 
                cm.instance=otcl.id AND 
                otcl.course=:courseid';

    $params = [
        'moduletype' => 'otcourselogic',
        'courseid' => $courseid
    ];

    if ($otcourselogics = $DB->get_records_sql($sql, $params))
    {
        foreach ($otcourselogics as $otcourselogic)
        {
            otcourselogic_grade_item_update($otcourselogic, 'reset');
        }
    }
    
}

/**
 * Получение оценок пользователя
 * 
 * @param stdClass $modinstance
 * @param int $userid
 * @return array|boolean - массив оценок или false в случае ошибки
 */
function otcourselogic_get_user_grades($modinstance, $userid=0)
{
    $grades = [];
    $states = [];

    $statechecker = otcourselogic_get_state_checker();
    
    if( (int)$userid == 0 )
    {
        $stateinfo = $statechecker->get_state_info($modinstance->id);
        if( ! empty($stateinfo) )
        {
            $states = $stateinfo;
        }
    } else
    {
        $stateinfo = $statechecker->get_state_info($modinstance->id, $userid);
        if( ! empty($stateinfo) )
        {
            $states = [$stateinfo];
        }
    }
    
    if( ! empty($states) )
    {
        foreach( $states as $state )
        {
            $grades[$state->userid] = ['userid' => $state->userid, 'rawgrade' => $state->status];
        }
        return $grades;
    }
    
    return false;
}

