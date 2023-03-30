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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Плагин подписки через форму связи с менеджером,
 * страница для обработки ajax запроса формы при отправке данных и валидации полей
 *
 * @package enrol
 * @subpackage sitecall
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once ("../../config.php");
require_once ($CFG->dirroot . '/enrol/sitecall/forms.php');
require_once ($CFG->dirroot . '/message/lib.php');
require_once ($CFG->libdir . '/messagelib.php');

global $PAGE;

$PAGE->set_context(context_system::instance());

$form = optional_param('sitecall', NULL, PARAM_RAW);
if ( ! empty($form) )
{// Получение данных формы
    $form = (array) json_decode($form);
}   

// Код формы
if ( isset($form['form_key']) )
{
    $code = $form['form_key'];
} else
{
    $code = NULL;
}
// Тип запроса
if ( isset($form['send_type']) )
{
    $type = $form['send_type'];
} else
{
    $type = NULL;
}
// Ключ формы
if ( isset($form['key']) )
{
    $key = $form['key'];
} else
{
    $key = NULL;
}
// Имя поля с которого последний раз произошла отправка на проверку
if ( isset($form['blurout']) )
{
    $blurout = $form['blurout'];
} else
{
    $blurout = NULL;
}

// Очистка от системных данных
unset($form['send_type'], $form['key'], $form['blurout'], $form['form_key']);

// Проверка на наличие формы по коду
if ( empty($code) || ! is_object($forms[$code]) )
{
    // Ошибка
    // Ключа формы нет, валидацию и отправку сделать не можем
    $response = [
            'form' => [
                    'status' => 'error',
                    'text' => get_string('no_form_error', 'enrol_sitecall')
        ]
    ];
} else
{// Форма найдена

    // Выполнение действий в зависимости от типа запроса
    switch ( $type )
    {
        case 'check' :
            // Проверка формы
            $response = $forms[$code]->checkData($form);
            break;
        case 'send' :
            // Сохранение данных
            if ( sitecall_save($form) )
            {
                // Генерируем ответ пользователю
                $response = array (
                        'form' => array (
                                'status' => 'ok',
                            	'text' => get_string('form_success_text', 'enrol_sitecall'),
                            	'header' => get_string('form_success_header', 'enrol_sitecall') 
                        ) 
                );
            } else
            {
                $response = array (
                        'form' => array (
                                'status' => 'error',
                            	'text' => get_string('form_request_error', 'enrol_sitecall') 
                        ) 
                );
            }
            break;
        default :
            // Неизвестное действие
            $response = array (
                    'form' => array (
                            'status' => 'error',
                            'text' => get_string('form_request_error', 'enrol_sitecall') 
                    ) 
            );
            break;
    }
}

// Отправка данных в форму
if ( ! empty($response) )
{
    echo json_encode($response);
}


/**
 * Сохранение данных формы
 * 
 * @param unknown $data
 * @return boolean
 */
function sitecall_save($data)
{
    global $USER, $DB, $PAGE, $COURSE;
    
    // СБОР ИНФОРМАЦИИ
    $save_data = [];
    
    // IP пользователя
    $ip = $_SERVER["REMOTE_ADDR"];
    // Время сохранения
    $save_data['date'] = time();
    if ( ! isset($data['firstname']) )
    {// Данные по имени пользователя не переданы
        $save_data['firstname'] = $USER->firstname;
    } else
    {// Данные по имени пользователя переданы
    	$save_data['firstname'] = $data['firstname'];
    }
    if ( ! isset($data['lastname']) )
    {// Данные по фамилии пользователя не переданы
        $save_data['lastname'] = $USER->lastname;
    } else
    {// Данные по фамилии пользователя переданы
    	$save_data['lastname'] = $data['lastname'];
    }
    if ( ! isset($data['email']) )
    {// Данные по почте пользователя не переданы
        $save_data['email'] = $USER->email;
    }
    else
    {// Данные по почте пользователя переданы
    	$save_data['email'] = $data['email'];
    }
    $save_data['userid'] = $USER->id;
    $save_data['courseid'] = optional_param('id', NULL, PARAM_INT);
    
    // Дополнительные данные по запросу
    $additional = ['ip' => $ip];
    // Комментарий
    if ( isset($data['comment']) )
    {
        $additional['comment'] = $data['comment'];
    } else
    {
        $additional['comment'] = '';
    }
    // Номер телефона
    if ( isset($data['phone']) )
    {
        $additional['phone'] = $data['phone'];
    } else 
    { 
        if ( !empty($USER->phone2) )
        {
            $additional['phone'] = $USER->phone2;
        } 
        elseif ( !empty($USER->phone1) )
        {
            $additional['phone'] = $USER->phone1;
        } else
        {
            $additional['phone'] = '';
        }
    }
    // Наименование организации
    if ( isset($data['orgname']) )
    {
    	$additional['orgname'] = $data['orgname'];
    } else 
    {
        $additional['orgname'] = $USER->institution;
    }
    // Источники
    if ( isset($data['origins']) )
    {
    	$additional['origins'] = $data['origins'];
    } else
    {
        $additional['origins'] = '';
    }
    
    $save_data['additional'] = serialize($additional);
    $save_data['status'] = 'ok';
    $result = $DB->insert_record("enrol_sitecall", $save_data);
    if ( $result )
    {// Данные сохранены
        sitecall_messaging($result);
    }
    return ! empty($result);
}

/**
 * Рассылка сообщений 
 * 
 * @param unknown $id
 * @return boolean
 */
function sitecall_messaging($id)
{
    global $CFG, $DB;
    
    // Получение запроса
    $request = $DB->get_record('enrol_sitecall', ['id' => $id]);
    if ( empty($request) )
    {// Запрос не найден
        return false;
    }
    // Получение курса
    $course = $DB->get_record('course', ['id' => $request->courseid]);
    if ( empty($course) )
    {// Курс не найден
        return false;
    }
    // Получение пользователя, отправившего запрос
    $user = $DB->get_record('user', ['id' => $request->userid]);
    if ( empty($user) )
    {
        $user = NULL;
    }
    // Получение экземпляра подписки
    $instances = $DB->get_records('enrol', ['courseid' => $course->id, 'enrol' => 'sitecall'], 'id ASC');
    
    if ( empty($instances) )
    {
        return false;
    }
    $instance = array_shift($instances);
    // Получение администратора системы
    $admin = get_admin();
    if ( empty($admin) )
    {
        return false;
    }
    
    // Подготовка данных для формирования сообщения
    $messagedata = new stdClass();
    $messagedata->firstname = $request->firstname;
    $messagedata->lastname = $request->lastname;
    $messagedata->userid = $request->userid;
    $messagedata->email = $request->email;
    $messagedata->courseid = $request->courseid;
    $messagedata->coursefullname = $course->fullname;
    $messagedata->courseshortname = $course->shortname;
    $additional = unserialize($request->additional);
    if ( isset($additional['phone']) )
    {
        $messagedata->phone = $additional['phone'];
    } else
    {
        $messagedata->phone = '';
    }
    if ( isset($additional['comment']) )
    {
        $messagedata->comment = $additional['comment'];
    } else
    {
        $messagedata->comment = '';
    }
    if ( isset($additional['origins']) )
    {
        $messagedata->origins = $additional['origins'];
    } else
    {
        $messagedata->origins = '';
    }
    if ( isset($additional['orgname']) )
    {
        $messagedata->orgname = $additional['orgname'];
    } else
    {
        $messagedata->orgname = '';
    }
    // Данные для сообщения с поддержкой html
    $messagedatahtml = $messagedata;
    $courseurl = new moodle_url("/course/view.php", ['id' => $course->id]);
    $messagedatahtml->coursefullname = html_writer::link($courseurl, $course->fullname);
    $messagedatahtml->courseshortname = html_writer::link($courseurl, $course->shortname);
    
    // Формирование сообщения
    $eventdata = new \core\message\message();
    $eventdata->component = 'enrol_sitecall';
    $eventdata->name = 'sitecall_request';
    $eventdata->userfrom = $admin;
    $eventdata->smallmessage = '';
    $eventdata->notification = 1;
    // Получение контекста
    $context = context_course::instance($course->id);
    
    $messagesend = true;
    
    if ( $instance->customint2 == 1 && ! empty($user) && ! isguestuser($user))
    {// Отправка письма студенту
        $studenttemplate = $instance->customtext2;
        if ( ! empty($studenttemplate) )
        {
            $studenttemplate = unserialize($studenttemplate);
        }
        // Опредеделние формата сообщения
        if ( isset($studenttemplate['format']) )
        {
            $eventdata->fullmessageformat = $studenttemplate['format'];
        } else 
        {
            $eventdata->fullmessageformat = FORMAT_MOODLE;
        }
        $eventdata->subject = get_string('newrequest', 'enrol_sitecall', $course->shortname);
        
        // Опредеделние шаблона сообщения
        if ( isset($studenttemplate['text']) )
        {
            $text = $studenttemplate['text'];
        } else 
        {
            $text = '';
        }
        
        // Формирование сообщений
        $eventdata->fullmessage = prepeare_message($text, $messagedata);
        $eventdata->fullmessagehtml = prepeare_message($text, $messagedatahtml);
        
        // Отправка сообщения
        $eventdata->userto = $user;
        $messagesend = ( message_send($eventdata) & $messagesend );
    }
    // Получение контактов
    $courseil = new \core_course_list_element($course);
    $coursecontacts = $courseil->get_course_contacts();
    if ( $instance->customint1 == 1 && ! empty($coursecontacts) )
    {// Отправка письма преподавателям
        $teachertemplate = $instance->customtext1;
        if ( ! empty($teachertemplate) )
        {
            $teachertemplate = unserialize($teachertemplate);
        }
        // Опредеделние формата сообщения
        if ( isset($teachertemplate['format']) )
        {
            $eventdata->fullmessageformat = $teachertemplate['format'];
        } else 
        {
            $eventdata->fullmessageformat = FORMAT_MOODLE;
        }
        $eventdata->subject = get_string('newrequest', 'enrol_sitecall', $course->shortname);
        
        // Опредеделние шаблона сообщения
        if ( isset($teachertemplate['text']) )
        {
            $text = $teachertemplate['text'];
        } else 
        {
            $text = '';
        }
        
        // Формирование сообщений
        $eventdata->fullmessage = prepeare_message($text, $messagedata);
        $eventdata->fullmessagehtml = prepeare_message($text, $messagedatahtml);
        
        foreach ( $coursecontacts as $contact )
        {
            if ( isset($contact['user']->id) )
            {// Отправка сообщения
                $eventdata->userto = $contact['user']->id;
                $messagesend = ( message_send($eventdata) & $messagesend );
            }
        }
    }
    
    $admintemplate = $instance->customtext1;
    if ( ! empty($admintemplate) )
    {
        $admintemplate = unserialize($admintemplate);
    }
    // Опредеделние формата сообщения
    if ( isset($admintemplate['format']) )
    {
        $eventdata->fullmessageformat = $admintemplate['format'];
    } else
    {
        $eventdata->fullmessageformat = FORMAT_MOODLE;
    }
    $eventdata->subject = get_string('newrequest', 'enrol_sitecall', $course->shortname);
    
    // Опредеделние шаблона сообщения
    if ( isset($admintemplate['text']) )
    {
        $text = $admintemplate['text'];
    } else
    {
        $text = '';
    }
    
    // Формирование сообщений
    $eventdata->fullmessage = prepeare_message($text, $messagedata);
    $eventdata->fullmessagehtml = prepeare_message($text, $messagedatahtml);
    
    // Отправка администратору
    $eventdata->userto = $admin;
    $messagesend = ( message_send($eventdata) & $messagesend );
    
    return $messagesend;
}

/**
 * Подготовить сообщение
 * 
 * @return string
 */
function prepeare_message($message, $data)
{
    if ( ! empty($data) )
    {// Есть подставляемые значения
        foreach ( $data as $key => $value )
        {
            // Название значения в шаблоне
            $name = '{'.strtoupper($key).'}';
            $message = str_replace($name, $value, $message);
        }
    }
    return $message;
}