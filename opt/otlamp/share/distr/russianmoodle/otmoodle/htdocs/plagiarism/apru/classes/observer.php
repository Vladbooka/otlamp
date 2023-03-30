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
 * Обозреватель событий для плагина plagiarism_apru
 * 
 * @package    plagiarism
 * @subpackage apru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_apru;

use plagiarism_apru\settings_form;
use plagiarism_apru\connection;
use stdClass;
use context;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Обработчик событий для plagiarism_apru
 */
class observer 
{
    /**
     * Обработка события сохранения черновика
     * Отправка происходит только, если включена настройка отправки черновиков
     * @param \assignsubmission_file\event\assessable_uploaded $event объект события
     */
    public static function assessable_uploaded(\assignsubmission_file\event\assessable_uploaded $event)
    {
        // Получение данных события
        $data = $event->get_data();
        
        // Получить контекст
        $context = context::instance_by_id($event->contextid);
        if ( $context->contextlevel != CONTEXT_MODULE )
        {// Передан неверный контекст
            debugging('Сontext type is not a module', DEBUG_DEVELOPER);
            return;
        }
        
        // Получить данные для обработки
        $courseid = (int)$data['courseid'];
        $cmid = (int)$context->instanceid;
        $userid = (int)$data['userid'];
        
        // Получить модуль курса, в котором был дан ответ
        $cm = get_coursemodule_from_id('assign', $cmid);
        $assign = new \assign($context, $cm, $courseid);
        // Получение последней попытки
        $submission = $assign->get_user_submission($userid, false);
        $assigninstance = $assign->get_instance();
        
        // Получение настройки необходимости подтверждения заданий
        $plagiarismvalues = settings_form::get_settings($cmid);
        $requiredconfirm = true;
        if ( isset($plagiarismvalues['mod_assign_confirmation_required']) )
        {
            $requiredconfirm = (boolean)$plagiarismvalues['mod_assign_confirmation_required'];
        }
        if( ! $requiredconfirm )
        {// Если включена настройка отправки черновиков - добавим их в очередь
            self::submit_files($context, $cmid, $courseid, $userid, $assign, $submission, 'apru_files', 'queue_files');
        }
    }
    /**
     * Обработчик события блокировки ответа на задание
     * @param \mod_assign\event\submission_locked $event
     */
    public static function assignment_locked(\mod_assign\event\submission_locked $event)
    {
        // Получение данных события
        $data = $event->get_data();
        
        // Получить контекст
        $context = context::instance_by_id($event->contextid);
        if ( $context->contextlevel != CONTEXT_MODULE )
        {// Передан неверный контекст
            debugging('Сontext type is not a module', DEBUG_DEVELOPER);
            return;
        }
        
        // Получить данные для обработки
        $courseid = (int)$data['courseid'];
        $cmid = (int)$context->instanceid;
        // Блокирует преподаватель, поэтому берем id из relateduserid
        $userid = (int)$data['relateduserid'];
        $cm = get_coursemodule_from_id('assign', $cmid);
        
        // Получить объект задания
        $assign = new \assign($context, $cm, $courseid);
        // Получить объект отправки задания
        $submission = $assign->get_user_submission($userid, false);
        // Получить настройки задания
        $assigninstance = $assign->get_instance();
        
        if( $submission === false || ($submission->status == 'submitted' && (boolean)$assigninstance->submissiondrafts) )
        {// Если в настройках выставлена отправка задания и задание уже было отправлено,
            // то блокируем повторную отправку в Антиплагиат
            return;
        } else 
        {
            self::submit_files($context, $cmid, $courseid, $userid, $assign, $submission, 'apru_files', 'queue_files');
        }
    }

    /**
     * Запускается по событию \mod_assign\event\assessable_submitted
     *
     * @param \assignment_upload\event\assessable_submitted $event
     */
    public static function assessable_submitted(\mod_assign\event\assessable_submitted $event) 
    {
        // Получение данных события
        $data = $event->get_data();
        
        // Получить контекст
        $context = context::instance_by_id($event->contextid);
        if ( $context->contextlevel != CONTEXT_MODULE )
        {// Передан неверный контекст
            debugging('Сontext type is not a module', DEBUG_DEVELOPER);
            return;
        }
        
        // Получить данные для обработки
        $courseid = (int)$data['courseid'];
        $cmid = (int)$context->instanceid;
        $userid = (int)$data['userid'];
        
        // Получить модуль курса, в котором был дан ответ
        $cm = get_coursemodule_from_id('assign', $cmid);
        $assign = new \assign($context, $cm, $courseid);
        // Получение последней попытки 
        $submission = $assign->get_user_submission($userid, false);
        $assigninstance = $assign->get_instance();
        
        if( (boolean)$assigninstance->submissiondrafts )
        {
            self::submit_files($context, $cmid, $courseid, $userid, $assign, $submission, 'apru_files', 'queue_files');
        }
    }
    
    /**
     * Выполнить действия над отправленным файлом и определить дальнейшее действие с
     * записью в таблице "plagiarism_apru_files"
     *
     * @param int $cmid - ID модуля курса, в рамках которого был добавлен файл
     * @param int $userid - ID пользователя - владельца файла
     * @param string $hash - Хэш пути файла. Уникальный идентификатор файла в файловом механизме Moodle
     * @param string $courseid - ID курса
     *
     * @return bool - Результат действий над файлом
     */
    public static function process_submitted_file($cmid, $userid, $hash, $courseid = NULL) 
    {
        global $DB, $PAGE;

        // Нормализация
        if ( empty($courseid) )
        {
            $courseid = $PAGE->course->id;
        }

        // Получение файла по хэшу пути
        $fs = get_file_storage();
        $file = $fs->get_file_by_hash($hash);
        if ( empty($file) )
        {
            return FALSE;
        }
        // Формирование данных для проверки наличия аналогичного текста пользователя
        $contenthash = $file->get_contenthash();
        $identifier  = $file->get_pathnamehash();
        $filename = $file->get_filename();
        $submissiontype = 'file';
        
        $explode = explode('.', $filename);
        if ( is_array($explode) ) 
        {
            $filetype = '.' . strtolower(array_pop($explode));
        } else {
            debugging("file type is unknown; filename: [$filename]", DEBUG_DEVELOPER);
            $filetype = NULL;
        }
        
        $params = [
            'cm'             => $cmid,
            'userid'         => $userid,
            'identifier'     => $identifier,
            'submissiontype' => $submissiontype
        ];

        $existingfile = FALSE;
        $reason = FALSE;
        if ( $DB->record_exists('plagiarism_apru_files', $params) )
        {// Файл найден
            $existingfile = $DB->get_record('plagiarism_apru_files', $params);
            if ( $existingfile->contenthash == $contenthash )
            {// Содержимое не изменилось
                return true;
            }
            $reason = 'update';
        } else
        {// Файл не найден
            $reason = 'create';
        }
        
        // Проверка расширения файла
        $supportedfiles = explode(';', connection::SUPPORTED_TYPES);
        if ( array_search($filetype, $supportedfiles) === FALSE ) 
        {// Документ не поддерживается для загрузки
            return;
        }
        $supportedarchives = explode(';', connection::SUPPORTED_ARCHIVES);
        if ( array_search($filetype, $supportedarchives) !== FALSE ) 
        {// Документ не поддерживается для загрузки
            return;
        }
        
        // Соединение с сервисом
        try
        {
            $connection = new connection();
            if ( ! $connection->is_alive() )
            {// Соединение с сервисом проверки на плагиат отсутствует
                throw new moodle_exception('error_connection', 'plagiarism_apru');
            }
        } catch ( moodle_exception $e )
        {// Нет соединения с сервисом антиплагиата
            switch ( $reason )
            {
                case 'update':
                    // Обновление записи текущего файла в очереди apru при неудачном соединении
                    $existingfile->lastmodified = time();
                    $DB->update_record('plagiarism_apru_files', $existingfile);
                    break;
                case 'create':
                    // Добавление файла в очередь apru при неудачном соединении
                    $params = new \stdClass();
                    $params->cm              = $cmid;
                    $params->userid          = intval($userid);
                    $params->identifier      = $identifier;
                    $params->externalid      = null;
                    $params->externalstatus  = 'NoError';
                    $params->statuscode      = 'not_upload';
                    $params->similarityscore = 100;
                    $params->attempt         = 1;
                    $params->filename        = $filename;
                    $params->contenthash     = $contenthash;
                    $params->lastmodified    = time();
                    $params->submissiontype  = $submissiontype;
                    $params->errorcode       = 0;
                    $params->errormsg        = null;
                    $DB->insert_record('plagiarism_apru_files', $params);
                    break;
                default:
                    break;
            }
            
            return true;
        }
        
        $document = new \stdClass();
        $document->Data = $file->get_content();
        $document->FileName = $filename;
        $document->FileType = $filetype;
        $document->ExternalUserID = $userid;
        
        // Заполнение атрибутов документа
        $attributes = [];
        $attributes['courseid'] = $courseid;
        $attributes['userid'] = $userid;
        $user = $DB->get_record('user', ['id' => $userid]);
        if ( ! empty($user) )
        {// Автор определен
            $attributes['Author'] = fullname($user);
        } else
        {// Автор не определен
            $attributes['Author'] = get_string('notice_author_not_set', 'plagiarism_apru');
        }
        if ( ! empty($cmid) )
        {// Модуль курса определен
            try
            {// Получение модуля курса
                $modinfo = get_fast_modinfo($courseid, $userid);
                $cm = $modinfo->get_cm($cmid);
                $attributes['Name'] = get_string('attribute_name_file', 'plagiarism_apru', $cm->name);
                $url = new \moodle_url('/mod/assign/view.php', ['id' => $cmid]);
                $attributes['Url'] = $url->out();
            } catch ( \moodle_exception $e )
            {// Модуль курса не найден
                debugging('Course module not found. Courseid: '.$courseid.' Cmid: '.$cmid, DEBUG_DEVELOPER);
            }
        }
        
        
        // Исполнение действий над документом
        switch ($reason)
        {
            case 'update':
                $docid  = $existingfile->externalid;
                $additional = unserialize($existingfile->additional);
                if( isset($additional['disable_check']) )
                {
                    unset($additional['disable_check']);
                    $existingfile->additional = serialize($additional);
                }
                try
                {
                    $result = $connection->update_document($docid, $document, $attributes);
                } catch(\moodle_exception $e)
                {
                    $existingfile->externalid      = null;
                    $existingfile->statuscode      = 'not_upload';
                    $existingfile->similarityscore = 100;
                    $existingfile->lastmodified    = time();
                    $existingfile->contenthash     = $contenthash;
                    $DB->update_record('plagiarism_apru_files', $existingfile);
                } catch(\coding_exception $e)
                {
                    $existingfile->externalid      = null;
                    $existingfile->statuscode      = 'not_upload';
                    $existingfile->similarityscore = 100;
                    $existingfile->lastmodified    = time();
                    $existingfile->contenthash     = $contenthash;
                    $DB->update_record('plagiarism_apru_files', $existingfile);
                }
                break;
            case 'create':
                try
                {
                    $result = $connection->upload_document($document, $attributes);
                } catch(\moodle_exception $e)
                {
                    $params = new \stdClass();
                    $params->cm              = $cmid;
                    $params->userid          = intval($userid);
                    $params->identifier      = $identifier;
                    $params->externalid      = null;
                    $params->externalstatus  = 'NoError';
                    $params->statuscode      = 'not_upload';
                    $params->similarityscore = 100;
                    $params->attempt         = 1;
                    $params->filename        = $filename;
                    $params->contenthash     = $contenthash;
                    $params->lastmodified    = time();
                    $params->submissiontype  = $submissiontype;
                    $params->errorcode       = 0;
                    $params->errormsg        = null;
                    $DB->insert_record('plagiarism_apru_files', $params);
                } catch(\coding_exception $e)
                {
                    $params = new \stdClass();
                    $params->cm              = $cmid;
                    $params->userid          = intval($userid);
                    $params->identifier      = $identifier;
                    $params->externalid      = null;
                    $params->externalstatus  = 'NoError';
                    $params->statuscode      = 'not_upload';
                    $params->similarityscore = 100;
                    $params->attempt         = 1;
                    $params->filename        = $filename;
                    $params->contenthash     = $contenthash;
                    $params->lastmodified    = time();
                    $params->submissiontype  = $submissiontype;
                    $params->errorcode       = 0;
                    $params->errormsg        = null;
                    $DB->insert_record('plagiarism_apru_files', $params);
                }
                break;
            default:
                return;
        }

        if( !empty($result->UploadDocumentResult->Uploaded) && is_array($result->UploadDocumentResult->Uploaded) )
        {
            // Обновляем записи в таблице.
            foreach ($result->UploadDocumentResult->Uploaded as $file)
            {
                if( $file->Id )
                {
                    $docid = $file->Id->Id;
                    $statuscode = 'uploaded';
                } else
                {
                    $docid = null;
                    $statuscode = 'not_upload';
                }
            
                $params = new \stdClass();
                $params->cm              = $cmid;
                $params->userid          = intval($userid);
                $params->identifier      = $identifier;
                $params->externalid      = $docid;
                $params->externalstatus  = $file->Reason;
                $params->statuscode      = $statuscode;
                $params->similarityscore = 100;
                $params->attempt         = 1;
                $params->filename        = $file->FileName;
                $params->contenthash     = $contenthash;
                $params->lastmodified    = time();
                $params->submissiontype  = $submissiontype;
                $params->errorcode       = 0;
                $params->errormsg        = $file->FailDetails;
                if ($reason == 'update')
                {
                    $existingfile->externalid      = $docid;
                    $existingfile->externalstatus  = $file->Reason;
                    $existingfile->statuscode      = $statuscode;
                    $existingfile->similarityscore = 100;
                    $existingfile->lastmodified    = time();
                    $existingfile->contenthash     = $contenthash;
                    $existingfile->errorcode       = 0;
                    $existingfile->attempt        += 1;
                    $existingfile->errormsg        = $file->FailDetails;
                    $DB->update_record('plagiarism_apru_files', $existingfile);
                } else if ($reason == 'create')
                {
                    $DB->insert_record('plagiarism_apru_files', $params);
                }
            }
        } else 
        {
            return;
        }
        
        return true;
    }

    /**
     * Обработчик для события очистки курса.
     * 
     * Удаляет все данные плагина, связанные с курсом, из базы,
     * чтобы можно было добавить новые экземпляры
     *
     * @param \core\event\course_reset_ended $event
     * @return boolean
     */
    public static function course_reset(\core\event\course_reset_ended $event) {
        global $DB;
        $eventdata = $event->get_data();
        $courseid = (int)$eventdata['courseid'];
        $resetcourse = true;

        $resetassign = (isset($eventdata['other']['reset_options']['reset_assign_submissions'])) ?
                            $eventdata['other']['reset_options']['reset_assign_submissions'] : 0;
        $resetforum = (isset($eventdata['other']['reset_options']['reset_forum_all'])) ?
                            $eventdata['other']['reset_options']['reset_forum_all'] : 0;

        $supportedmods = ['assign'];
        foreach ($supportedmods as $supportedmod) {
            $module = $DB->get_record('modules', ['name' => $supportedmod]);

            // Get all the course modules that have Antiplagiat enabled
            $sql = "SELECT cm.id
                      FROM {course_modules} cm
                RIGHT JOIN {plagiarism_apru_config} ptc ON cm.id = ptc.cm
                     WHERE cm.module = :moduleid
                           AND cm.course = :courseid
                           AND ptc.name = 'apru_assignid'";
            $params = ['courseid' => $courseid, 'moduleid' => $module->id];
            $modules = $DB->get_records_sql($sql, $params);

            if (count($modules) > 0) {
                $reset = "reset".$supportedmod;
                if (!empty($$reset)) {
                    // Remove Plagiarism plugin submissions and assignment id from DB for this module.
                    foreach ($modules as $mod) {
                        $DB->delete_records('plagiarism_apru_files', ['cm' => $mod->id]);
                        $DB->delete_records('plagiarism_apru_config', ['cm' => $mod->id]);
                    }
                } else {
                    $resetcourse = false;
                }
            }
        }

        return true;
    }
    
    /**
     * Создание файлов, содержащих текст задания и сохранение их очередь на отправку в Антиплагиат
     * @param \context_module $context объект контекста модуля курса
     * @param int $cmid идентификатор модуля курса
     * @param int $courseid идентификатор курса
     * @param int $userid идентификатор пользователя
     * @param \file_storage $fs объект файлового хранилища
     * @param \assign $assign объект задания
     * @param \stdClass $submission объект отправки задания
     * @param string $component компонент
     * @param string $filearea файловая зона
     */
    public static function add_text_to_apru_queue($context, $cmid, $courseid, $userid, $fs, $assign, $submission, $component, $filearea)
    {
        global $DB;
        $cm = get_coursemodule_from_id('assign', $cmid);
        $user = $DB->get_record('user', ['id' => $userid]);
        
        if( $submission )
        {
            $assign_submission_onlinetext = new \assign_submission_onlinetext($assign, 'assignsubmission');
            // Получим текст, отправленный в задании
            $content = $assign_submission_onlinetext->get_editor_text('onlinetext', $submission->id);
            
            if( !empty($content) )
            {// Посмотрим, сохранялись ли уже тексты из задания в виде файлов
                $sql = 'SELECT *
                    FROM {files}
                    WHERE contextid=?
                    AND component=?
                    AND filearea=?
                    AND itemid=?
                    AND filename LIKE \'' . $cmid . '_' . $userid . '_text_%\'
                    ORDER BY timemodified DESC';
                $file = $DB->get_records_sql($sql, [
                    $context->id,
                    $component,
                    $filearea,
                    $submission->id
                ], 0, 1);
                if( $file )
                {// Если сохранялись, получим id последнего и для текущего выставим id на единицу больше
                    $file = array_shift($file);
                    $count = str_replace($cmid . '_' . $userid . '_text_', '', $file->filename);
                    $count = (int)str_replace('.txt', '', $count);
                    $count++;
                } else
                {// Если не было сохранений, запишем файл с id == 0
                    $count = 0;
                }
                if( empty($file) || ( ! empty($file) && $file->contenthash != sha1($content) ) )
                {// Если сохраняем файл впервые или же предыдущий файл не отличается по содержанию
                    // подготовим данные для сохранения файла
                    $filerecord = new \stdClass();
        
                    $filerecord->contextid    = $context->id;
                    $filerecord->component    = $component;
                    $filerecord->filearea     = $filearea;
                    $filerecord->itemid       = $submission->id;
                    $filerecord->sortorder    = 0;
                    $filerecord->filepath     = '/';
                    $filerecord->filename     = $cmid . '_' . $userid . '_text_' . $count . '.txt';
                    $filerecord->timecreated  = time();
                    $filerecord->timemodified = time();
                    $filerecord->userid       = $userid;
                    $filerecord->source       = null;
                    $filerecord->author       = fullname($user);
                    $filerecord->license      = 'allrightsreserved';
                    $filerecord->status       = 0;
                    // и сохраним файл
                    $fs->create_file_from_string($filerecord, $content);
                }
            }
        }
    }
    
    public static function add_files_to_apru_queue($context, $userid, $fs, $component, $filearea)
    {
        // Получим файлы, сохраненные в задании
        $files = $fs->get_area_files($context->id, 'assignsubmission_file', 'submission_files');
        $userfiles = $pathnamehashes = [];
        foreach($files as $file)
        {
            if( !$file->is_directory() && $file->get_userid() == $userid )
            {
                $userfiles[] = $file;
            }
        }
        if ( empty($userfiles) )
        {
            // Файлы не загружены
            return;
        }
        foreach($userfiles as $userfile)
        {// По полученным файлам подготовим данные для добавления копий файлов в очередь на отправку в Антиплагиат
            $userfilerecord = new \stdClass();
            $userfilerecord->contextid = $userfile->get_contextid();
            $userfilerecord->component = $component;
            $userfilerecord->filearea = $filearea;
            $userfilerecord->itemid = $userfile->get_itemid();
            $userfilerecord->sortorder = $userfile->get_sortorder();
            $userfilerecord->mimetype = $userfile->get_mimetype();
            $userfilerecord->userid = $userfile->get_userid();
            $userfilerecord->source = $userfile->get_source();
            $userfilerecord->author = $userfile->get_author();
            $userfilerecord->license = $userfile->get_license();
            $userfilerecord->status = $userfile->get_status();
            $userfilerecord->filepath = $userfile->get_filepath();
            $userfilerecord->filename = $userfile->get_filename();
            $userfilerecord->timecreated = $userfile->get_timecreated();
            $userfilerecord->timemodified = $userfile->get_timemodified();
            $userfilerecord->referencefileid = $userfile->get_referencefileid();
        
            if( $fs->file_exists(
                $userfilerecord->contextid,
                $userfilerecord->component,
                $userfilerecord->filearea,
                $userfilerecord->itemid,
                $userfilerecord->filepath,
                $userfilerecord->filename
                ) )
            {// Если файл в очереди уже есть
                $existingfile = $fs->get_file(
                    $userfilerecord->contextid,
                    $userfilerecord->component,
                    $userfilerecord->filearea,
                    $userfilerecord->itemid,
                    $userfilerecord->filepath,
                    $userfilerecord->filename
                    );
                // заменим его с учетом нового содержимого
                $existingfile->replace_file_with($userfile);
            } else
            {// если нет файла в очереди - добавим его
                $fs->create_file_from_storedfile($userfilerecord, $userfile);
            }
        }
    }
    
    public static function submit_files($context, $cmid, $courseid, $userid, $assign, $submission, $component, $filearea)
    {
        $userfiles = [];
        // Получим объект файлового хранилища
        $fs = get_file_storage();
        // Добавим в очередь текст задания
        self::add_text_to_apru_queue($context, $cmid, $courseid, $userid, $fs, $assign, $submission, $component, $filearea);
        // Добавим в очередь файлы задания
        self::add_files_to_apru_queue($context, $userid, $fs, $component, $filearea);
        // Получим файлы из очереди
        $files = $fs->get_area_files($context->id, $component, $filearea);
        foreach($files as $file)
        {
            if( !$file->is_directory() && $file->get_userid() == $userid )
            {
                $userfiles[] = $file;
            }
        }
        if ( empty($userfiles) )
        {
            // Файлы не загружены
            return;
        }
        foreach($userfiles as $file)
        {// Соберем массив хешей путей файлов в очереди
            $pathnamehashes[] = $file->get_pathnamehash();
        }
        
        if ( ! settings_form::is_enabled($cmid) )
        {// Проверка на плагиат запрещена для текущего модуля курса
            return;
        }
        
        // Обработка загруженных файлов
        foreach ( $pathnamehashes as $hash )
        {// Для каждого файла запустим процесс отправки
            self::process_submitted_file($cmid, $userid, $hash, $courseid);
        }
    }
}
