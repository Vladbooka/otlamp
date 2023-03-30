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
 * Плагин определения заимствований Руконтекст. Обработчик действий по событию.
 *
 * @package    plagiarism
 * @subpackage rucont
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_rucont;

use plagiarism_rucont\settings_form;
use plagiarism_rucont\connection;
use stdClass;
use context;
use Exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Обработчик событий для plagiarism_rucont
 */
class observer 
{
    /**
     * Общее событие при добавлении ответа на элемент курса "Задание"
     *
     * @param \assignment_upload\event\assessable_uploaded $event
     */
    public static function assignment_uploaded(\assignment_upload\event\assessable_uploaded $event) 
    {
        return true;
    }

    /**
     * Событие сохранения ответа для оценки преподавателем
     *
     * @param \assignment_upload\event\assessable_submitted $event
     */
    public static function assessable_submitted(\assignment_upload\event\assessable_submitted $event) 
    {
        return true;
    }
    
    /**
     * Событие удаления курса
     * 
     * Удаляет все данные плагина, связанные с курсом из БД
     *
     * @param \core\event\course_reset_ended $event
     * 
     * @return boolean
     */
    public static function course_reset(\core\event\course_reset_ended $event)
    {
        global $DB;
        
        // Получение данных события
        $eventdata = $event->get_data();
        $courseid = (int)$eventdata['courseid'];
        $resetcourse = true;
    
        // Поддерживаемые модули
        $supportedmods = ['assign'];
        
        // Опция сброса данных для модулей задание
        $resetassign = ( isset($eventdata['other']['reset_options']['reset_assign_submissions']) ) ?
                                  $eventdata['other']['reset_options']['reset_assign_submissions'] : 0;
        
        // Обработка каждого типа модуля
        foreach ($supportedmods as $supportedmod)
        {
            // Получение данных модуля
            $module = $DB->get_record('modules', array('name' => $supportedmod));
    
            // Получение экземпляров модулей в очищаемом курсе
            $sql = "SELECT cm.id
                      FROM {course_modules} cm
                RIGHT JOIN {plagiarism_rucont_config} ptc ON cm.id = ptc.cm
                     WHERE cm.module = :moduleid
                           AND cm.course = :courseid
                           AND ptc.name = 'rucont_assignid'";
            $params = ['courseid' => $courseid, 'moduleid' => $module->id];
            $modules = $DB->get_records_sql($sql, $params);
    
            if ( ! empty($modules) )
            {// Модули в курсе найдены
                // Определение необходимости очистки модуля курса
                $reset = "reset".$supportedmod;
                if ( isset($$reset) && ! empty($$reset) )
                {// Данные необходимо удалить
                    foreach ($modules as $mod) 
                    {// Удаление загруженных файлов и конфигурации
                        $DB->delete_records('plagiarism_rucont_files', ['cm' => $mod->id]);
                        $DB->delete_records('plagiarism_rucont_config', ['cm' => $mod->id]);
                    }
                } else
                {
                    $resetcourse = false;
                }
            }
        }
        return true;
    }
    
    /**
     * Обработчик события отправки введенного через модуль "Задание"
     *
     * @param \assignsubmission_file\event\assessable_uploaded $event - Событие
     */
    public static function assignsubmission_text_uploaded(\assignsubmission_onlinetext\event\assessable_uploaded $event) 
    {
        // Получение данных события
        $data = $event->get_data();
        if ( ! isset($data['other']['content']) )
        {// Данные не получены
            return;
        }
        // Очистка от тегов html
        $data['other']['content'] = html_entity_decode(strip_tags($data['other']['content']));
        if ( empty($data['other']['content']) )
        {// Данные не получены
            return;
        }
        
        // Определение модуля по событию
        $context = context::instance_by_id($event->contextid);
        if ( $context->contextlevel != CONTEXT_MODULE ) 
        {// Контекст не является контекстом модуля
            debugging('context is not module type', DEBUG_DEVELOPER);
            return;
        }
        // Получение ID элемента модуля курса
        $cmid = $context->instanceid;
        
        // Определение активности плагиаризма для элемента модуля курса
        if ( ! settings_form::is_enabled($cmid) ) 
        {// Плагиаризм не включен
            return;
        }
        
        // Загрузка данных в Руконтекст
        self::process_submitted_onlinetext($cmid, $data['userid'], $data['other']['content']);
    }
    
    /**
     * Обработчик события отправки файла через модуль "Задание"
     *
     * @param \assignsubmission_file\event\assessable_uploaded $event - Событие
     */
    public static function assignsubmission_file_uploaded(\assignsubmission_file\event\assessable_uploaded $event) 
    {
        // Получение данных события
        $data = $event->get_data();
        
        if ( ! isset($data['other']['pathnamehashes']) )
        {// Файлы не получены
            return;
        }
        if ( empty($data['other']['pathnamehashes']) || ! is_array($data['other']['pathnamehashes']) )
        {// Файлы не получены
            return;
        }
        
        // Определение модуля по событию
        $context = context::instance_by_id($event->contextid);
        if ( $context->contextlevel != CONTEXT_MODULE ) 
        {// Контекст не является контекстом модуля
            debugging('context is not module type', DEBUG_DEVELOPER);
            return;
        }
        // Получение ID элемента модуля курса
        $cmid = $context->instanceid;

        // Определение активности плагиаризма для элемента модуля курса
        if ( ! settings_form::is_enabled($cmid) ) 
        {// Плагиаризм не включен
            return;
        }

        foreach ( $data['other']['pathnamehashes'] as $hash ) 
        {
            // Загрузка данных в Руконтекст
            self::process_submitted_file($cmid, $data['userid'], $hash);
        }
    }

    /**
     * Отправить файл на проверку в систему Руконтекст
     *
     * @param int $cmid - ID элемента модуля курса
     * @param int $userid - ID владельца ответа
     * @param string $hash - Хэш пути файла
     * 
     * @return bool - Результат работы
     */
    public static function process_submitted_file($cmid, $userid, $hash) 
    {
        global $DB;
        
        // Получение загруженного файла по хэшу пути
        $fs = get_file_storage();
        $file = $fs->get_file_by_hash($hash);
        $filename = $file->get_filename();
        
        // Получение типа файла
        $explode = explode('.', $filename);
        if ( is_array($explode) ) 
        {// Тип определен
            $filetype = '.' . array_pop($explode);
        } else {
            debugging("file type is unknown; filename: [$filename]", DEBUG_DEVELOPER);
            $filetype = NULL;
        }
        // Проверка типа файла
        $supportedfiles = explode(';', connection::SUPPORTED_TYPES);
        if (array_search($filetype, $supportedfiles) === false)
        {// Документ не поддерживается
            return;
        }
        $supportedarchives = explode(';', connection::SUPPORTED_ARCHIVES);
        if (array_search($filetype, $supportedarchives) !== false)
        {// Документ не поддерживается
            return;
        }

        // Проверка файла на необходимость отправки на сервер.
        // Если contenthash различается, или файл ранее не загружался, то необходимо отправлять.
        $contenthash = $file->get_contenthash();
        $identifier  = $file->get_pathnamehash();
        $params = [
            'cm'             => $cmid,
            'userid'         => $userid,
            'filename'       => $filename,
            'identifier'     => $identifier,
            'submissiontype' => 'file'
        ];
        $existingfile = false;
        $action = NULL;
        if ( $DB->record_exists('plagiarism_rucont_files', $params) ) 
        {// Файл найден
            $existingfile = $DB->get_record('plagiarism_rucont_files', $params);
            if ( $existingfile->contenthash == $contenthash ) 
            {// Содержимое файла не изменилось
                return true;
            }
            // Содержимое файла изменилось
            $action = 'update';
        } else 
        {// Файл не найден
            $action = 'create';
        }

        // Подключение к API Руконтекст
        $connection = new connection();

        // Формирование данных для загрузки
        $data = [];
        $data['content'] = (string)$file->get_content();
        $data['filename'] = $filename;
        $user = $DB->get_record('user', ['id' => (integer)$userid ]);
        if ( ! empty($user) )
        {// Пользователь найден
            // Добавление только имени
            // @todo - Добавить ФИО, как только Руконтент заблокирует публичный доступ к отчетам
            $data['autor'] = $user->firstname;
        }
        $data['title'] = 'Answer';
        $data['tester'] = '';
        $data['comment'] = 'plagiarism_rucont';
        
        // Взаимодействие с сервисом Руконтекст
        $response = NULL;
        switch ($action) 
        {
            case 'update':
            case 'create':
                try {
                    $response = $connection->upload_document($data);
                } catch (Exception $e) 
                {
                    $response = new stdClass();
                    $response->error = new stdClass();
                    $response->error->code = $e->getCode();
                    $response->error->message = $e->getMessage();
                }
                break;
            default:
                return;
        }
        
        // Параметры записи в БД по умолчанию
        $default = new stdClass();
        $default->cm              = (integer)$cmid;
        $default->userid          = (integer)$userid;
        $default->identifier      = (string)$identifier;
        $default->externalid      = NULL;
        $default->externalstatus  = NULL;
        $default->statuscode      = NULL;
        $default->similarityscore = 0;
        $default->attempt         = 1;
        $default->filename        = $filename;
        $default->contenthash     = $contenthash;
        $default->lastmodified    = time();
        $default->submissiontype  = 'file';
        $default->parentid        = NULL;
        $default->errorcode       = NULL;
        $default->errormsg        = NULL;
        $default->reporturl       = NULL;
        
        if ( isset($response->error) )
        {// Ошибка при отправке файла
            $default->errorcode = (integer)$response->error->code;
            $default->errormsg = (string)$response->error->message;
        }
        if ( isset($response->result->requestId) )
        {// Загрузка прошла успешно
            $default->externalid = $response->result->requestId;
            $default->statuscode = 'upload';
        } 
        if ( isset($response->result->message) )
        {// Загрузка прошла успешно
            $default->externalstatus = $response->result->message;
        }
        
        // Формирование в зависимости от задачи
        if ($action == 'update') 
        {
            $existingfile->externalid      = $default->externalid;
            $existingfile->externalstatus  = $default->externalstatus;
            $existingfile->statuscode      = $default->statuscode;
            $existingfile->similarityscore = $default->similarityscore;
            $existingfile->lastmodified    = $default->lastmodified;
            $existingfile->contenthash     = $default->contenthash;
            $existingfile->errorcode       = $default->errorcode;
            $existingfile->attempt        += 1;
            $existingfile->errormsg        = $default->errormsg;
            $existingfile->reporturl       = $default->reporturl;
            $DB->update_record('plagiarism_rucont_files', $existingfile);
        } else if ($action == 'create') 
        {
            $DB->insert_record('plagiarism_rucont_files', $default);
        }
        return true;
    }

    

    /**
     * Выполнить действия над отправленным текстом и определить дальнейшее действие с
     *  записью в таблице "plagiarism_rucont_files"
     *
     * @param int $cmid - ID элемента модуля курса
     * @param int $userid - ID владельца ответа
     * @param string $content - Текст ответа
     * 
     * @return bool - Результат работы
     */
    public static function process_submitted_onlinetext($cmid, $userid, $content) 
    {
        global $DB;
        
        // Проверка файла на необходимость отправки на сервер.
        // Если contenthash различается, или файл ранее не загружался, то необходимо отправлять.
        $contenthash = sha1($content);
        $filename = "{$cmid}_{$userid}_text.txt";
        $identifier  = "{$cmid}_{$userid}";
        $params = [
            'cm'             => $cmid,
            'userid'         => $userid,
            'filename'       => $filename,
            'identifier'     => $identifier,
            'submissiontype' => 'text-content'
        ];
        $existingfile = false;
        $action = NULL;
        if ( $DB->record_exists('plagiarism_rucont_files', $params) )
        {// Файл найден
            $existingfile = $DB->get_record('plagiarism_rucont_files', $params);
            if ( $existingfile->contenthash == $contenthash )
            {// Содержимое файла не изменилось
                return true;
            }
            // Содержимое файла изменилось
            $action = 'update';
        } else
        {// Файл не найден
            $action = 'create';
        }
        
        // Подключение к API Руконтекст
        $connection = new connection();
        
        // Формирование данных для загрузки
        $data = [];
        $data['content'] = (string)$content;
        $data['filename'] = $filename;
        $user = $DB->get_record('user', ['id' => (integer)$userid ]);
        if ( ! empty($user) )
        {// Пользователь найден
            // Добавление только имени
            // @todo - Добавить ФИО, как только Руконтент заблокирует публичный доступ к отчетам
            $data['autor'] = $user->firstname;
        }
        $data['title'] = 'Answer';
        $data['tester'] = '';
        $data['comment'] = 'plagiarism_rucont';
        
        // Взаимодействие с сервисом Руконтекст
        $response = NULL;
        switch ($action) 
        {
            case 'update':
            case 'create':
                try {
                    $response = $connection->upload_document($data);
                } catch (Exception $e) 
                {
                    $response = new stdClass();
                    $response->error = new stdClass();
                    $response->error->code = $e->getCode();
                    $response->error->message = $e->getMessage();
                }
                break;
            default:
                return;
        }

        // Параметры записи в БД по умолчанию
        $default = new stdClass();
        $default->cm              = (integer)$cmid;
        $default->userid          = (integer)$userid;
        $default->identifier      = (string)$identifier;
        $default->externalid      = NULL;
        $default->externalstatus  = NULL;
        $default->statuscode      = NULL;
        $default->similarityscore = 0;
        $default->attempt         = 1;
        $default->filename        = $filename;
        $default->contenthash     = $contenthash;
        $default->lastmodified    = time();
        $default->submissiontype  = 'text-content';
        $default->parentid        = NULL;
        $default->errorcode       = NULL;
        $default->errormsg        = NULL;
        $default->reporturl       = NULL;
        
        if ( isset($response->error) )
        {// Ошибка при отправке файла
            $default->errorcode = (integer)$response->error->code;
            $default->errormsg = (string)$response->error->message;
        }
        if ( isset($response->result->requestId) )
        {// Загрузка прошла успешно
            $default->externalid = $response->result->requestId;
            $default->statuscode = 'upload';
        } 
        if ( isset($response->result->message) )
        {// Загрузка прошла успешно
            $default->externalstatus = $response->result->message;
        }
        
        // Формирование в зависимости от задачи
        if ($action == 'update') 
        {
            $existingfile->externalid      = $default->externalid;
            $existingfile->externalstatus  = $default->externalstatus;
            $existingfile->statuscode      = $default->statuscode;
            $existingfile->similarityscore = $default->similarityscore;
            $existingfile->lastmodified    = $default->lastmodified;
            $existingfile->contenthash     = $default->contenthash;
            $existingfile->errorcode       = $default->errorcode;
            $existingfile->attempt        += 1;
            $existingfile->errormsg        = $default->errormsg;
            $existingfile->reporturl       = $default->reporturl;
            $DB->update_record('plagiarism_rucont_files', $existingfile);
        } else if ($action == 'create') 
        {
            $DB->insert_record('plagiarism_rucont_files', $default);
        }
        return true;
    }
}
