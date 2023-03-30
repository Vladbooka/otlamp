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
 * Плагин определения заимствований Антиплагиат. Библиотека плагина.
 *
 * @package    plagiarism
 * @subpackage apru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if ( ! defined('MOODLE_INTERNAL') ) 
{
    die('Direct access to this script is forbidden.');
}

require_once($CFG->dirroot.'/plagiarism/lib.php');
require_once($CFG->dirroot.'/mod/assign/locallib.php');
require_once($CFG->dirroot.'/mod/assign/submission/onlinetext/locallib.php');

use plagiarism_apru\connection;
use plagiarism_apru\settings_form;

/**
 * Hook the add/edit of the course module.
 *
 * @param stdClass $moduleinfo the module info
 * @param stdClass $course the course of the module
 */
function plagiarism_apru_coursemodule_edit_post_actions($moduleinfo, $course) {
    global $DB;
    
    // Получение глабальной конфигурации плагиаризма в системе
    $configsettings = settings_form::get_config_settings('mod_' . $moduleinfo->modulename);
    if (empty($configsettings['apru_use_mod_' . $moduleinfo->modulename])) { 
        // Плагиаризм не работает с текущим модулем курса
        return $moduleinfo;
    }

    // Получение полей формы
    $settingsfields = settings_form::get_config_settings_fields();
    // Получение ранее сохраненных настроек плагиаризма для данного элемента курса
    $plagiarismvalues = settings_form::get_settings($moduleinfo->coursemodule);

    foreach ($settingsfields as $field) {
        if (isset($moduleinfo->$field)) { 
            // Найдены данные по текущему полю в форме

            // Сохранение настройки
            $optionfield = new stdClass();
            $optionfield->cm = $moduleinfo->coursemodule;
            $optionfield->name = $field;
            $optionfield->value = $moduleinfo->$field;
            if (isset($plagiarismvalues[$field])) { 
                // Настройка была уже ранее сохранена
                $optionfield->id = $DB->get_field('plagiarism_apru_config', 'id', [
                    'cm' => $moduleinfo->coursemodule,
                    'name' => $field
                ]);
                // Обновление настройки
                if (! $DB->update_record('plagiarism_apru_config', $optionfield)) { 
                    // Обновление не удалось
                    print_error('defaultupdateerror', 'plagiarism_apru');
                }
            } else { 
                // Настройка сохраняется впервые
                // Добавление настройки
                if (! $DB->insert_record('plagiarism_apru_config', $optionfield)) { 
                    // Добавление не удалось
                    print_error('defaultinserterror', 'plagiarism_apru');
                }
            }
        }
    }
    return $moduleinfo;
}

/**
 * Inject the competencies elements into all moodle module settings forms.
 *
 * @param moodleform_mod $formwrapper The moodle quickforms wrapper object.
 * @param MoodleQuickForm $mform The actual form object (required to modify the form).
 */
function plagiarism_apru_coursemodule_standard_elements($formwrapper, $mform) {
    $context = $formwrapper->get_context();
    if ($cm = $formwrapper->get_coursemodule()) {
        // Если редактируем модуль
        $cmid = $cm->id;
    } else {
        // Если создаем модуль - будут использованы настройки по умолчанию
        $cmid = 0;
    }
    $matches = [];
    if (!preg_match('/^mod_([^_]+)_mod_form$/', get_class($formwrapper), $matches)) {
        debugging('Rename form to mod_xx_mod_form, where xx is name of your module');
        print_error('unknownmodulename');
    }
    $modulename = 'mod_' . $matches[1];
    /**
     * Альтернативный вариант получения модуля,
     * $module = $DB->get_record('modules', array('id'=>$cm->module));
     * $modulename = 'mod_' . $module->name;
     */
    if (has_capability('plagiarism/apru:enable', $context)) {
        // Есть права на редактирование настроек плагиаризма
        if (!empty($modulename)) {
            // Проверка на включение плагина для данного модуля
            $configsettings = settings_form::get_config_settings($modulename);
            if ( empty($configsettings['apru_use_'.$modulename]) )
            {// Настроек антиплагиата для данного элемента курса не предусмотрено
                return;
            }
        }
        
        // Получение значений настроек плагиаризма для элемента курса
        $plagiarismvalues = settings_form::get_settings($cmid);
        // Получение списка элементов для настройки плагиаризма
        $plagiarismelements = settings_form::get_config_settings_fields();
        
        // Заголовок
        $mform->addElement(
            'header',
            'plugin_header',
            get_string('aprupluginsettings', 'plagiarism_apru')
        );
        
        // Включение плагина
        $options = [
            0 => get_string('no'),
            1 => get_string('yes')
        ];
        $mform->addElement(
            'select',
            'use_apru',
            get_string('useapru', 'plagiarism_apru'),
            $options
        );
        
        // Показывать студентам информацию по плагиаризму
        $mform->addElement(
            'select',
            'plagiarism_show_student_report',
            get_string('studentreports', 'plagiarism_apru'),
            $options
        );
        $mform->addHelpButton(
            'plagiarism_show_student_report',
            'studentreports',
            'plagiarism_apru'
        );
        $mform->disabledIf(
            'plagiarism_show_student_report',
            'use_apru',
            'eq', 0
        );
        
        // Требовать блокировку задания
        $mform->addElement(
            'select',
            'mod_assign_confirmation_required',
            get_string('setting_mod_assign_confirmation_required', 'plagiarism_apru'),
            $options
        );
        $mform->disabledIf(
            'mod_assign_confirmation_required',
            'use_apru',
            'eq', 0
        );
        
        
        // Установка значений полей
        foreach ($plagiarismelements as $element) {
            if (isset($plagiarismvalues[$element])) {
                $mform->setDefault($element, $plagiarismvalues[$element]);
            }
        }
    }
}

/**
 * Класс плагина плагиаризма
 *
 * @package    plagiarism
 * @subpackage apru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plagiarism_plugin_apru extends plagiarism_plugin 
{
    /**
     * 
     * @param int $cmid
     * @param int $userid
     * @param object $file moodle file object
     * @return array - sets of details about specified file, one array of details per plagiarism plugin
     *  - each set contains at least 'analyzed', 'score', 'reporturl'
     */
    public function get_file_results($cmid, $userid, $file) {
        global $OUTPUT;
        return $OUTPUT->notification(get_string('submissioncheck', 'plagiarism_apru'), 'notifysuccess');
    }
    
    /**
     * Отображение дополнительной информации о документе
     *
     * Хук для отображения дополнительной информации пользовательского представления
     * Отображает процент заимствований и ссылки на полный отчёт [если есть право]
     *
     * @param array $linkarray - Массив различных данных для генерации ссылок
     *
     * @return string
     */
    public function get_links($linkarray) 
    {
        global $DB, $OUTPUT, $PAGE;
        
        $html = '';
       
        // Проверка доступности плагина в текущем элементе модуля курса
        if ( ! settings_form::is_enabled($linkarray['cmid']) ) 
        {// Плагин не включён для этого элемента курса
            return $html;
        }
        // Проверка прав на просмотр информации
        if ( ! $this->is_access($linkarray['cmid']) ) 
        {// У пользователя нет прав на просмотр информации
            return $html;
        }
        
        // Базовые параметры
        $cmid = $linkarray['cmid'];
        $userid = $linkarray['userid'];
        
        // Проверка наличия контента
        $file = FALSE;
        if ( ! empty($linkarray['file']) ) 
        {// Передан файл
            $file = $linkarray['file'];
            // ПРоверка зоны хранения файла
            $filearea = $file->get_filearea();
            if ( $filearea == 'feedback_files' ) 
            {// Файл является отзывом преподавателя на ответ
                $file = FALSE;
            }
        }

        if ( empty($file) && empty($linkarray['content']) ) 
        {
            return $html;
        }
        
        // Формирование идентификатора и имени документа
        if ( ! empty($file) ) 
        {// Передан файл
            // Получение файла
            $fs = get_file_storage();
            $aprufile = $fs->get_file(
                $file->get_contextid(), 
                'apru_files', 
                'queue_files', 
                $file->get_itemid(), 
                $file->get_filepath(), 
                $file->get_filename()
            );
            if( $aprufile )
            {
                $identifier = $aprufile->get_pathnamehash();
            } else 
            {// файл в очередь еще не добавлен
                return $html;
            }
        } else 
        {// Передан вручную добавленный текст
            // Получим контекст
            $context = context_module::instance($cmid);
            // Получим объект модуля курса
            $cm = get_coursemodule_from_id('assign', $cmid);
            // Получим id курса
            $courseid = $linkarray['course'];
            // Получить объект задания
            $assign = new assign($context, $cm, $courseid);
            // Получить объект отправки задания
            $submission = $assign->get_user_submission($userid, false);
            // Получим последний файл с текстом задания
            $file = $this->get_last_file_with_text($cmid, $userid, $context, 'apru_files', 'queue_files', $submission);
            if( $file )
            {// если файл найден, проверим совпадают ли хеш файла и хеш текущего текста в задании
                $file = array_shift($file);
                $assign_submission_onlinetext = new assign_submission_onlinetext($assign, 'assignsubmission');
                // Получим текст, отправленный в задании
                $content = $assign_submission_onlinetext->get_editor_text('onlinetext', $submission->id);
                if( $file->contenthash == sha1($content) )
                {// если совпадают, то это текст не менялся
                    $identifier = $file->pathnamehash;
                } else 
                {// если не совпадают, значит загружен уже новый текст и он еще не добавлен в очередь
                    return $html;
                }
                
            } else 
            {// текст задания не был сохранен в файл
                return $html;
            }
        }
        
        // Поиск документа
        $params = [
            'cm'         => $cmid,
            'userid'     => $userid,
            'identifier' => $identifier
        ];
        if ( ! $row = $DB->get_record('plagiarism_apru_files', $params) ) 
        {// Документ не был обработан модулем плагиаризма
            return $html;
        }
        
        $estimated = NULL;
        if ( $row->statuscode == 'ready' ) 
        {// Документ проверен
            
            // Получение данных о проверке
            $score     = $row->similarityscore;
            $reporturl = $row->reporturl;
            $scorelang = get_string('originality', 'plagiarism_apru', $score);
        } elseif ( $row->statuscode == 'not_upload' )
        {// Документ находится в процессе загрузки в антиплагиат
	        $row->similarityscore = $score = 0;
	        $scorelang = get_string('notupload', 'plagiarism_apru', $score);
        } else 
        {// Документ не проверен в системе
            
            // Получение идентификатора документа во внешней системе Антиплагиат
            $docid = $row->externalid;
            
            // Открытие соединения с сервисом Антиплагиат
            try 
            {
                $connection = new connection();
            } catch ( moodle_exception $e) 
            {// Ошибка соединения
                return $html;
            }
            
            if ( ! $connection->is_alive()) 
            {// Соединение отсутствует
                return $OUTPUT->notification(get_string('noconnection', 'plagiarism_apru'));
            }
            
            // Получение статуса проверки документа
            try 
            {
                $status = $connection->get_check_status($docid);
            } catch(moodle_exception $e)
            {
                return $html;
            }

            if ( empty($status) ) 
            {// Статус  не получен
                return $html;
            }
            // Статус документа
            $row->statuscode      = $this->get_statuscode($status->Status);
            // Внешний статус документа в сервисе Антиплагиат
            $row->externalstatus  = $status->Status;

            if ( $row->statuscode == 'processing' || $row->statuscode == 'notchecked' ) 
            {// Документ находится в процессе обработки
                // Процент уникальности текста
                $row->similarityscore = $score = 100;
                $scorelang = get_string('processingyet', 'plagiarism_apru');
            } elseif( $row->statuscode == 'failed' )
            {// Ошибка при загрузке документа
                $row->similarityscore = $score = 0;
                $scorelang = get_string('processingfailed', 'plagiarism_apru');
            } else 
            {// Документ обработан
                // Процент уникальности текста
                $row->similarityscore = $score = 100 - intval($status->Summary->Score);
                $scorelang = get_string('originality', 'plagiarism_apru', $score);
            }
            // Дополнительные данные по документу
            $row->lastmodified    = time();
            $row->errorcode       = 0;
            $row->errormsg        = $status->FailDetails;
            $row->reporturl       = $reporturl = $status->reporturl;
            // Время ожидания (в секундах)
            $estimated            = $status->EstimatedWaitTime;
            
            // Обновление документа
            $DB->update_record('plagiarism_apru_files', $row);
        }

        // Формирование класса для отображения
        $markclass = 'mark-bad';
        if ($score >= 80) 
        {// Уникальность больше 80%
            $markclass = 'mark-excellent';
        } else if ($score >= 50) 
        {// Уникальность больше 50%
            $markclass = 'mark-moderate';
        }
        
        // Блок с результатом проверки
        $html  .= $OUTPUT->box_start('plagiarism_apru_submissioncheck');
        $scorelang = html_writer::tag(
            'span',
            $scorelang,
            ['class' => 'plagiarism_apru_score']
        );
        $bar = html_writer::tag(
            'div',
            $scorelang,
            [
                'class' => 'plagiarism_apru_progressbar', 
                'max' => '100', 
                'value' => $score
            ]
        );
        $html .= html_writer::tag(
            'div', 
            $bar, 
            [
                'class' => 'plagiarism_apru_progress '.$markclass
            ]
        );
        
        // Время до завершения проверки
        if ( ! empty($estimated) ) 
        {
            $html .= html_writer::tag(
                'div', 
                get_string('estimatedwait', 'plagiarism_apru', $estimated)
            );
        }
        
        // Получение контекста элемента модуля курса
        $context = context_module::instance((int)$cmid);
        if ( has_capability('plagiarism/apru:viewfullreport', $context) )
        {
            // Статус нахождения документа в индексе 
            $indexed = 0;
            if ( isset($row->additional) && ! empty($row->additional) )
            {// Есть дополнительные данные по статусу нахождения документа в индексе
                $additional_data = unserialize($row->additional);
                if ( isset($additional_data['index_status']) )
                {// Получение данных о индексации статуса
                    $indexed = (int)$additional_data['index_status'];
                }
            }
            
            // Формирование опций выбора статуса
            if( $indexed )
            {
                if( has_capability('plagiarism/apru:disableindexstatus', $context) )
                {
                    $html .= html_writer::div(get_string('remove_from_index', 'plagiarism_apru'), 'change_index_document_status indexed btn', ['id' => 'change_index_document_status', 'data-docid' => $row->id]);
                } else
                {
                    $html .= html_writer::div(get_string('remove_from_index', 'plagiarism_apru'), 'change_index_document_status disable indexed btn', ['id' => 'change_index_document_status', 'data-docid' => $row->id]);
                }
            
            } else
            {
                if( has_capability('plagiarism/apru:enableindexstatus', $context) )
                {
                    $html .= html_writer::div(get_string('add_to_index', 'plagiarism_apru'), 'change_index_document_status notindexed btn', ['id' => 'change_index_document_status', 'data-docid' => $row->id]);
                } else
                {
                    $html .= html_writer::div(get_string('add_to_index', 'plagiarism_apru'), 'change_index_document_status disable notindexed btn', ['id' => 'change_index_document_status', 'data-docid' => $row->id]);
                }
            }
            $PAGE->requires->js_call_amd('plagiarism_apru/addtoindex', 'init', []);
        }
        // Ссылка на подробный отчет
        if ( ! empty($reporturl) ) 
        {
            $html .= html_writer::tag(
                'a', 
                get_string('reportlink', 'plagiarism_apru'), 
                ['href' => $reporturl]
            );
        }
        $html .= $OUTPUT->box_end(true);
        
        return $html;
    }    
    
    /**
     * Сформировать секцию с уведомлением пользователя о проверке файла на заимствования
     *
     * @param int $cmid - ID модуля курса
     * 
     * @return string - HTML-код
     */
    public function print_disclosure($cmid) 
    {
        global $OUTPUT;

        if ( ! settings_form::is_enabled($cmid) ) 
        {// Плагин не включён для этого элемента курса
            return '';
        }
        
        if ( ! $this->is_access($cmid) ) 
        {// Нет прав на просмотр информации
            return '';
        }
        
        $html = $OUTPUT->notification(get_string('submissioncheck', 'plagiarism_apru'), 'notifysuccess');
        return $html;
    }

    /**
     * Генерация статуса проверки документа из Внешнего статуса сервиса Антиплагиата
     * 
     * @param string $status - Внешний статус Антиплагиата
     * 
     * @return string - Внутренний статус документа для работы в модуле плагиаризма 
     */
    public function get_statuscode($status) 
    {
        switch ($status) 
        {
            // Из документа не удалось выделить текст.
            case 'NoText':
                return 'notext';
                
            // Неподдерживаемый формат документа.
            case 'UnsupportedFormat':
                return 'unsupportedformat';

            // При обработке документа произошла внутренняя ошибка сервиса.
            case 'ServerInternalError':
                return 'servererror';

            // Документ был загружен успешно.
            case 'NoError':
                return 'uploaded';

            // Последняя проверка завершилась успешно.
            case 'Ready':
                return 'ready';

            // Последняя проверка еще не завершилась.
            case 'InProgress':
                return 'processing';

            // Последняя проверка завершилась с ошибкой.
            case 'Failed':
                return 'failed';

            // Проверок по этому документу не проводилось.
            case 'None':
                return 'notchecked';

            default:
                return 'default';
        }
    }
    
    /**
     * Проверка прав на просмотр информации по плагиаризму
     * 
     * @param int $cmid - ID модуля элемента курса
     * 
     * @return boolean - Результат проверки
     */
    public function is_access($cmid) 
    {
        // Получение контекста элемента модуля курса
        $context = context_module::instance((int)$cmid);
       
        if ( ! has_capability('plagiarism/apru:viewfullreport', $context) ) 
        {// Прав на просмотр полной информации по плагиаризму нет
            
            // Получение настроек плагиаризма для текущего модуля курса
            $plagiarismsettings = settings_form::get_settings($cmid);
            
            // Настройка "Отобразить cвидетельства оригинальности для студентов"
            if ( empty($plagiarismsettings['plagiarism_show_student_report']) ) 
            {// Студенты не могут просмотривать данные по оригинальности работы
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * Добавить файл в очередь проверки на заимствования
     * 
     * @param string $pathnamehash - Хэш пути файла
     * @param int $userid - ID пользователя-владельца файла. Если не определен, берется из данных файла
     * @param array $options - Массив дополнительных опций обработки
     *                          array 'additional' - Дополнительные данные по документу
     *                          
     * @throws \moodle_exception - При ошибках добавления файла в очередь
     * 
     * @return int|bool - ID добавленного документа или FALSE при ошибке
     */
    public function add_file_to_queue($pathnamehash, $userid = 0, $options = []) 
    {
        global $DB;
        
        // Нормализация
        $pathnamehash = (string)$pathnamehash;
        
        // Получение файла по хэшу пути
        $fs = get_file_storage();
        $file = $fs->get_file_by_hash($pathnamehash);
        if ( empty($file) )
        {// Файл не найден
            throw new \moodle_exception('error_hashfile_not_found', 'plagiarism_apru', NULL, $pathnamehash);
            return FALSE;
        }
        if ( $file->is_directory() )
        {// Файл является папкой
            throw new \moodle_exception('error_hashfile_is_directory', 'plagiarism_apru', NULL, $pathnamehash);
            return FALSE;
        }
        
        // Поиск аналогичного файла в очереди
        $exist = $DB->get_record('plagiarism_apru_files', ['identifier' => $file->get_pathnamehash()]);
        if ( ! empty($exist) )
        {// Файл найден в очереди
            return $exist->id;
        }
        
        // Формирование данных для добавления файла в очередь на обработку
        $newfile = new \stdClass;
        $newfile->cm              = 0;
        $newfile->userid          = $userid;
        $newfile->identifier      = $file->get_pathnamehash();
        $newfile->externalid      = NULL;
        $newfile->externalstatus  = NULL;
        $newfile->statuscode      = 'not_upload';
        $newfile->similarityscore = NULL;
        $newfile->attempt         = 0;
        $newfile->filename        = (string)$file->get_filename();
        $newfile->contenthash     = (string)$file->get_contenthash();
        $newfile->lastmodified    = time();
        $newfile->submissiontype  = 'file';
        $newfile->parentid        = NULL;
        $newfile->errorcode       = NULL;
        $newfile->errormsg        = NULL;
        $newfile->reporturl       = NULL;
        $newfile->additional      = NULL;
        // Добавление данных по файлу
        $contextid = $file->get_contextid();
        $context = \context::instance_by_id($contextid);
        if ( $context instanceof \context_module )
        {// Прилинковка к модулю курса
            $newfile->cm = $context->instanceid;
        }
        // Получение автора файла
        if ( empty($userid) )
        {// Автозаполнение владельца по файлу
            $newfile->userid = (int)$file->get_userid();
        }
        if ( isset($options['additional']) && is_array($options['additional']) )
        {// Дополнительные данные
            $newfile->additional = serialize($options['additional']);
        }
        
        // Добавление файла в очередь документов
        try 
        {
            $documentid = $DB->insert_record('plagiarism_apru_files', $newfile);
        } catch ( \dml_exception $e )
        {// Ошибка добавления файла в очередь
            throw new \moodle_exception(
                'error_adding_file_to_queue', 
                'plagiarism_apru', 
                NULL, 
                $newfile->identifier, 
                $e->debuginfo
            );
            return FALSE;
        }
        
        return $documentid;
    }
    
    /**
     * Загрузить файл в систему проверки на заимствования
     *
     * @param int $documentid - Идентификатор документа в очереди
     *
     * @throws \plagiarism_apru_document_exception - При ошибках работы с документом
     *         \plagiarism_apru_connection_exception - При ошибках загрузки документа
     *
     * @return string|bool - Внешний идентификатор документа, полученный после загрузки файла
     *                       или FALSE в случае ошибки
     */
    public function upload_file($documentid, $options = [])
    {
        global $DB;
    
        // Получение документа
        $document = $DB->get_record('plagiarism_apru_files', ['id' => $documentid]);
        if ( empty($document) )
        {// Документ не найден
            throw new \plagiarism_apru_document_exception('error_document_not_found', 'plagiarism_apru', NULL, $documentid);
            return FALSE;
        }
        
        if ( $document->externalid )
        {// Документ уже был добавлен в систему
            return $document->externalid;
        }
        
        // Получение файла по хэшу пути
        $fs = get_file_storage();
        $file = $fs->get_file_by_hash($document->identifier);
        if ( empty($file) )
        {// Файл не найден
            // Обновление статуса
            $updatedocument = new \stdClass();
            $updatedocument->id = $documentid;
            $updatedocument->statuscode      = 'error';
            $updatedocument->lastmodified    = time();
            $updatedocument->errormsg        = get_string('error_hashfile_not_found', 'plagiarism_apru', $document->identifier);
            $DB->update_record('plagiarism_apru_files', $updatedocument);
            
            throw new \plagiarism_apru_document_exception('error_hashfile_not_found', 'plagiarism_apru', NULL, $document->identifier);
            return FALSE;
        }
        
        // Проверка контента
        if ( $file->get_contenthash() != $document->contenthash )
        {// Обновление документа в очереди
            $updated = new \stdClass();
            $updated->id = $document->id;
            $updated->contenthash = $file->get_contenthash();
            $updated->lastmodified = time();
            $DB->update_record('plagiarism_apru_files', $updated);
        }
        
        // Проверка запрета загрузки 
        if ( ! empty($document->additional) )
        {// Дополнительные опции найдены
            $additional = unserialize($document->additional);
            if ( isset($additional->disable_upload) && (int)$additional->disable_upload != 0 )
            {// Включен запрет загрузки
                $updated = new \stdClass();
                $updated->id = $document->id;
                $updated->lastmodified = time();
                $DB->update_record('plagiarism_apru_files', $updated);
                return FALSE;
            }
        }
        
        $filename = $file->get_filename();
        $filetype = '';
        // Проверка расширения файла
        $supportedfiles = explode(';', connection::SUPPORTED_TYPES);
        $supportedarchives = explode(';', connection::SUPPORTED_ARCHIVES);
        $explode = explode('.', $filename);
        if ( is_array($explode) )
        {
            $filetype = '.' . strtolower(array_pop($explode));
        }
        if ( array_search($filetype, $supportedfiles) === FALSE && 
             array_search($filetype, $supportedarchives) === FALSE 
           )
        {// Документ не поддерживается для загрузки
            
            $updatedocument = new \stdClass();
            $updatedocument->id = $documentid;
            $updatedocument->statuscode      = 'error';
            $updatedocument->lastmodified    = time();
            $updatedocument->errormsg        = get_string('error_documenttype_not_supported', 'plagiarism_apru', $filetype);
            $DB->update_record('plagiarism_apru_files', $updatedocument);
            
            throw new \plagiarism_apru_document_exception(
                'error_documenttype_not_supported', 'plagiarism_apru', NULL, $filetype
                );
            return FALSE;
        }
        
        // Открытие подключения c сервисом Антиплагиат
        try {
            $connection = new connection();
        } catch ( \moodle_exception $e )
        {
            throw new \plagiarism_apru_connection_exception(
                'error_connection', 'plagiarism_apru', NULL, NULL, $e->getMessage()."\n".$e->debuginfo
                );
            return FALSE;
        }
        
        // Формирование данных для загрузки
        $upload = new \stdClass();
        $upload->Data     = $file->get_content();
        $upload->FileName = $filename;
        $upload->FileType = $filetype;
        $upload->ExternalUserID = $document->userid;
        
        // Формирование атрибутов документа и опций загрузки
        $attributes = [];
        $uploadoptions = [];
        $attributes['userid'] = $document->userid;
        $user = $DB->get_record('user', ['id' => $document->userid]);
        if ( ! empty($user) )
        {// Автор определен
            $attributes['Author'] = fullname($user);
        } else
        {// Автор не определен
            $attributes['Author'] = get_string('notice_author_not_set', 'plagiarism_apru');
        }
        if ( ! empty($document->cm) )
        {// Модуль курса определен
            $cm = get_coursemodule_from_id(NULL, $document->cm);
            if ( ! empty($cm) )
            {// Модуль найден
                $attributes['Name'] = get_string('attribute_name_file', 'plagiarism_apru', $cm->name);
                $url = new \moodle_url('/mod/assign/view.php', ['id' => $document->cm]);
                $attributes['Url'] = $url->out();
            }
        }
        // Переопределение атрибутов из дополнительных настроек
        if ( ! empty($document->additional) )
        {// Дополнительные данные найдены
            $additional = unserialize($document->additional);
            if ( isset($additional['attributes']) && is_array($additional['attributes']) ) 
            {// Переопределение значений атрибутов
                foreach ( $additional['attributes'] as $name => $value )
                {
                    $attributes[$name] = $value;
                }
            }
            if ( isset($additional['uploadoptions']) && is_array($additional['uploadoptions']) )
            {// Переопределение значений атрибутов
                foreach ( $additional['uploadoptions'] as $name => $value )
                {
                    $uploadoptions[$name] = $value;
                }
            }
        }

        try {
            // Загрузка документа в систему Анитплагиат
            $response = $connection->upload_document($upload, $attributes, $uploadoptions);
            
            // Получение данных загруженного файла
            $uploaded = array_pop($response->UploadDocumentResult->Uploaded);
            
            $updatedocument = new \stdClass();
            $updatedocument->id = $documentid;
            if ( isset($uploaded->Id->Id) )
            {// Передан идентификатор документа в системе Антиплагиат
            $updatedocument->externalid = $uploaded->Id->Id;
            } else
            {
                $updatedocument->externalid = NULL;
            }
            $updatedocument->externalstatus  = $uploaded->Reason;
            $updatedocument->statuscode      = $this->get_statuscode($uploaded->Reason);
            $updatedocument->similarityscore = 100;
            $updatedocument->lastmodified    = time();
            $updatedocument->errorcode       = 0;
            $updatedocument->errormsg        = $uploaded->FailDetails;
            $updatedocument->attempt         = $document->attempt + 1;
            if ( isset($uploadoptions['AddToIndex']) )
            {// Документ был добавлен в индекс
                $additional['index_status'] = (int)$uploadoptions['AddToIndex'];
                $updatedocument->additional = serialize($additional);
            }
            $DB->update_record('plagiarism_apru_files', $updatedocument);
            
            return $updatedocument->externalid;
        } catch ( \moodle_exception $e )
        {// Ошибка отправки документа в сервис
            
            // Смена статуса документа
            $updatedocument = new \stdClass();
            $updatedocument->id = $documentid;
            $updatedocument->statuscode      = 'error';
            $updatedocument->lastmodified    = time();
            $updatedocument->errormsg        = $e->getMessage();
            $DB->update_record('plagiarism_apru_files', $updatedocument);
            
            throw new \plagiarism_apru_connection_exception(
                'error_connection_upload_file', 'plagiarism_apru', NULL, NULL, $e->getMessage()."\n".$e->debuginfo
                );
            return FALSE;
        }
    }
    
    /**
     * Инициализировать проверку документа
     *
     * @param int $documentid - Идентификатор документа в очереди
     *
     * @throws \plagiarism_apru_document_exception - При ошибках работы с документом
     *         \plagiarism_apru_connection_exception - При ошибках работы с сервисом Антиплагиат
     *
     * @return bool - Результат инициализации проверки
     */
    public function check_file($documentid, $options = [])
    {
        global $DB;
        
        // Получение документа
        $document = $DB->get_record('plagiarism_apru_files', ['id' => $documentid]);
        if ( empty($document) )
        {// Документ не найден
            throw new \plagiarism_apru_document_exception('error_document_not_found', 'plagiarism_apru', NULL, $documentid);
            return FALSE;
        }
    
        if ( empty($document->externalid) )
        {// Документ не был загружен в систему
            throw new \plagiarism_apru_document_exception('error_document_not_uploaded', 'plagiarism_apru');
            return FALSE;
        }
        
        // Проверка запрета проверки документа
        if ( ! empty($document->additional) )
        {// Дополнительные опции найдены
            $additional = unserialize($document->additional);
            if ( isset($additional->disable_check) && (int)$additional->disable_check != 0 )
            {// Включен запрет загрузки
                $updated = new \stdClass();
                $updated->id = $document->id;
                $updated->lastmodified = time();
                $DB->update_record('plagiarism_apru_files', $updated);
                return FALSE;
            }
        }
        
        // Открытие подключения c сервисом Антиплагиат
        try {
            $connection = new connection();
        } catch ( \moodle_exception $e )
        {
            throw new \plagiarism_apru_connection_exception(
                'error_connection', 'plagiarism_apru', NULL, NULL, $e->getMessage()."\n".$e->debuginfo
                );
            return FALSE;
        }
    
        try {
            // Инициализация проверки документа
            $connection->check_document($document->externalid);
            
            // Смена статуса документа
            $updatedocument = new stdClass();
            $updatedocument->id = $documentid;
            $updatedocument->statuscode      = 'processing';
            $updatedocument->lastmodified    = time();
            $DB->update_record('plagiarism_apru_files', $updatedocument);
            return TRUE;
        } catch ( \moodle_exception $e )
        {// Ошибка отправки документа в сервис
            // Смена статуса документа
            $updatedocument = new stdClass();
            $updatedocument->id = $documentid;
            $updatedocument->statuscode      = 'error';
            $updatedocument->lastmodified    = time();
            $updatedocument->errormsg        = $e->getMessage();
            $DB->update_record('plagiarism_apru_files', $updatedocument);
            
            throw new \plagiarism_apru_connection_exception(
                'error_connection', 'plagiarism_apru', NULL, NULL, $e->getMessage()."\n".$e->debuginfo
            );
            return FALSE;
        }
    }
    
    /**
     * Получить последний файл с текстом задания
     * @param int $cmid идентификатор модуля курса
     * @param int $userid идентификатор пользователя
     * @param context_module $context контекст модуля курса
     * @param string $component компонент, сохранивший файл
     * @param string $filearea файловая зона, в которой сохранен файл
     * @param stdClass $submission объект отправки задания
     */
    public function get_last_file_with_text($cmid, $userid, $context, $component, $filearea, $submission)
    {
        global $DB;
        $sql = 'SELECT *
                    FROM {files}
                   WHERE contextid=?
                     AND component=?
                     AND filearea=?
                     AND itemid=?
                     AND filename LIKE \'' . $cmid . '_' . $userid . '_text_%\'
                ORDER BY timemodified DESC';
        return $DB->get_records_sql($sql, [
            $context->id,
            $component,
            $filearea,
            $submission->id
        ], 0, 1);
    }
    
    /**
     * Обновление записи в очереди на тправку в Антиплагиат
     * @param stdClass $aprurecord
     * @param array $options
     * @throws \moodle_exception
     * @return boolean
     */
    public function update_file_in_queue($aprurecord, $options)
    {
        global $DB;
        $result = false;
        if ( isset($options['additional']) && is_array($options['additional']) )
        {// Дополнительные данные
            $aprurecord->additional = serialize($options['additional']);
        }
        unset($options['additional']);
        foreach($options as $key => $option)
        {
            if( isset($aprurecord->$key) )
            {
                $aprurecord->$key = $option;
            }
        }
        $aprurecord->lastmodified = time();
        
        // Добавление файла в очередь документов
        try
        {
            $result = $DB->update_record('plagiarism_apru_files', $aprurecord);
        } catch ( dml_exception $e )
        {// Ошибка добавления файла в очередь
            throw new moodle_exception(
                'error_adding_file_to_queue',
                'plagiarism_apru',
                null,
                $aprurecord->identifier,
                $e->debuginfo
            );
            return false;
        }
        return $result;
    }
}

/**
 * Класс исключения при работе с документами в очереди плагина
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plagiarism_apru_document_exception extends moodle_exception 
{
}

/**
 * Класс исключения при работе с сервисом Антиплагиата
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plagiarism_apru_connection_exception extends moodle_exception
{
}