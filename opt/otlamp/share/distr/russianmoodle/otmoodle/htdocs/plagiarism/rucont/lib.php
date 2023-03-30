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
 * Плагин определения заимствований Руконтекст. Дополнительные функции.
 *
 * @package    plagiarism
 * @subpackage rucont
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Подключение дополнительных библиотек
require_once($CFG->dirroot.'/plagiarism/lib.php');

use plagiarism_rucont\connection;
use plagiarism_rucont\settings_form;

/**
 * Hook the add/edit of the course module.
 *
 * @param stdClass $moduleinfo the module info
 * @param stdClass $course the course of the module
 */
function plagiarism_rucont_coursemodule_edit_post_actions($moduleinfo, $course) {
    global $DB;
    
    // Проверка конфигурации плагиаризма на доступность в данном типе модуля
    $configsettings = settings_form::get_config_settings('mod_'.$moduleinfo->modulename);
    if ( empty($configsettings['rucont_use_mod_'.$moduleinfo->modulename]) )
    {
        return $moduleinfo;
    }
    
    // Получение настроек плагиаризма для текущего элемента курса
    $settingsfields = settings_form::get_config_settings_fields();
    $plagiarismvalues = settings_form::get_settings($moduleinfo->coursemodule, false);
    
    // Сохранение настроек по текущему элементу курса
    foreach ( $settingsfields as $field )
    {
        if ( isset($moduleinfo->$field) )
        {// Поле найдено в данных формы
            $optionfield = new stdClass();
            $optionfield->cm = $moduleinfo->coursemodule;
            $optionfield->name = $field;
            $optionfield->value = $moduleinfo->$field;
            // Сохранение настройки
            if ( isset($plagiarismvalues[$field]) )
            {// Обновление значения
                $optionfield->id = $DB->get_field('plagiarism_rucont_config', 'id',
                    (array('cm' => $moduleinfo->coursemodule, 'name' => $field)));
                if ( ! $DB->update_record('plagiarism_rucont_config', $optionfield))
                {// Обновление не удалось
                    print_error('defaultupdateerror', 'plagiarism_rucont');
                }
            } else
            {// Добавление новой настройки
                if (!$DB->insert_record('plagiarism_rucont_config', $optionfield))
                {
                    print_error('defaultinserterror', 'plagiarism_rucont');
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
function plagiarism_rucont_coursemodule_standard_elements($formwrapper, $mform) {
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
    if ( has_capability('plagiarism/rucont:enable', $context) )
    {// Плагиаризм включен в модуле курса
        if ( ! empty($modulename) )
        {// Проверка на включение плагина для данного модуля
            $configsettings = settings_form::get_config_settings($modulename);
            if ( empty($configsettings['rucont_use_'.$modulename]) )
            {// Плагиаризм не включен в модуле
                return;
            }
        }
        
        // ДОБАВЛЕНИЕ СЕКЦИИ НАСТРОЕК ПЛАГИАРИЗМА
        $mform->addElement('header', 'plagiarism_rucont_header', get_string('rucontpluginsettings', 'plagiarism_rucont'));
        
        // Включить плагиаризм в модуле
        $options = [
            0 => get_string('no'),
            1 => get_string('yes')
        ];
        $mform->addElement('select', 'use_rucont', get_string('userucont', 'plagiarism_rucont'), $options);
        // Показывать информацию плагиаризма студентам
        $mform->addElement('select', 'plagiarism_rucont_show_student_report', get_string("studentreports", "plagiarism_rucont"), $options);
        $mform->addHelpButton('plagiarism_rucont_show_student_report', 'studentreports', 'plagiarism_rucont');
        $mform->disabledIf('plagiarism_rucont_show_student_report', 'use_rucont', 'eq', 0);
        
        // Получение настроек плагиаризма для текущего модуля
        $plagiarismvalues   = settings_form::get_settings($cmid);
        $plagiarismelements = settings_form::get_config_settings_fields();
        
        // Установка значений по умолчанию.
        foreach ($plagiarismelements as $element)
        {
            if ( isset($plagiarismvalues[$element]) )
            {
                $mform->setDefault($element, $plagiarismvalues[$element]);
            }
        }
    }
}

class plagiarism_plugin_rucont extends \plagiarism_plugin 
{
    /**
     * Выполнение cron
     */
    public function cron() 
    {
        global $DB, $OUTPUT;
        
        // Получение файлов, которые были отправлены на проверку
        $params = [
            'statuscode' => 'upload'
        ];
        $files = $DB->get_records('plagiarism_rucont_files', $params);
        
        if ( ! empty($files) )
        {// Найдены файлы
            $connection = new connection();
            // Получение статуса по каждому статусу
            foreach ( $files as $file )
            {
                if ( empty($file->externalid) || $file->lastmodified + (DAYSECS*14) < time() )
                {// Идентификатор не установлен, или документ не проверен за 14 дней
                    continue;
                }
                $status = $connection->get_result($file->externalid);
                if ( isset($status->result) )
                {// Результат получен
                    $needupdate = false;
                    if ( isset($status->result->hash) )
                    {// Добавление отчета
                        $needupdate = true;
                        $file->reporturl = 'http://text.rucont.ru/History/ReviewItem?h='.$status->result->hash;
                    }
                    if ( isset($status->result->misuse_rating) )
                    {// Добавление отчета
                        $needupdate = true;
                        $file->similarityscore = 100 * (float)$status->result->misuse_rating;
                    }
                    if ( $needupdate )
                    {// Данные получены
                        $file->statuscode = 'ready';
                        $DB->update_record('plagiarism_rucont_files', $file);
                    }
                }
            }
        }
        
        return true;
    }
    
    /**
     * Отобразить в форме добавления текста уведомление о тестировании на заимствования
     *
     * @param int $cmid - ID модуля курса
     *
     * @return string
     */
    public function print_disclosure($cmid)
    {
        global $OUTPUT;
        
        // Проверка на доступность плагина в модуле
        if ( ! settings_form::is_enabled($cmid) )
        {// Плагин не включен в модуле курса
            return '';
        }
        // Проверка прав доступа
        if ( ! $this->is_access($cmid) )
        {// Нет прав доступа на просмотр
            return '';
        }
        // Формирование уведомления
        $output = $OUTPUT->notification(get_string('submissioncheck', 'plagiarism_rucont'), 'notifysuccess');
        
        return $output;
    }
    
    /**
     *
     * @param int $cmid
     * @param int $userid
     * @param object $file moodle file object
     * @return array - sets of details about specified file, one array of details per plagiarism plugin
     *  - each set contains at least 'analyzed', 'score', 'reporturl'
     */
    public function get_file_results($cmid, $userid, $file)
    {
        global $OUTPUT;
        return $OUTPUT->notification(get_string('submissioncheck', 'plagiarism_rucont'), 'notifysuccess');
    }
    
    /**
     * Проверка прав доступа к модулю курса
     *
     * @param integer $cmid
     *
     * @return boolean
     */
    public function is_access($cmid)
    {
        // Если снята галочка "Отобразить cвидетельства оригинальности для студентов".
        $context = context_module::instance($cmid);
        if ( ! has_capability('plagiarism/rucont:viewfullreport', $context) )
        {// Прав доступа нет
            // Получение настроек плагиаризма для текущего модуля
            $plagiarismsettings = settings_form::get_settings($cmid);
            if ( empty($plagiarismsettings['plagiarism_rucont_show_student_report']) )
            {// Настройками закрыто уведомление пользоваелей о проведении проверки
                return false;
            }
        }
        return true;
    }
    
    /**
     * Хук для отображения дополнительной информации пользовательского представления
     * 
     * Отображает процент заимствований и ссылки на полный отчёт [если есть право] 
     * для одного элемента ответа. Если элементов несколько(два файла, файл и текст),
     * хук вызывается для каждого из них
     *
     * @param array $linkarray - Масив данных по ответу
     * 
     * @return string - HTML-код дополнительной информации
     */
    public function get_links($linkarray) 
    {
        global $DB, $OUTPUT;
        
        // Плагин не включён для этого элемента курса
        if ( ! settings_form::is_enabled($linkarray['cmid']) ) 
        {
            return '';
        }
        // Права на просмотр
        if ( ! $this->is_access($linkarray['cmid']) ) 
        {
            return '';
        }
        
        // Сбор информации по ответу
        $cmid = $linkarray['cmid'];
        $userid = $linkarray['userid'];

        if ( isset($linkarray['file']) && ! empty($linkarray['file']) ) 
        {// Передан файл
            if ( ! is_object($linkarray['file']) )
            {
                return '';
            }
            $filearea = $linkarray['file']->get_filearea();
            if ( $filearea == 'feedback_files' ) 
            {
                return '';
            }
            $identifier = $linkarray['file']->get_pathnamehash();
            $filename   = $linkarray['file']->get_filename();
        }
        if ( isset($linkarray['content']) && ! empty($linkarray['content']) )
        {// Передан текст
            if ( ! is_string($linkarray['content']) )
            {
                return '';
            }
            $identifier = "{$cmid}_{$userid}";
            $filename   = "{$cmid}_{$userid}_text.txt";
        }
        
        // Получить данные по ответу из плагиаризма
        $params = array(
            'cm'         => $cmid,
            'userid'     => $userid,
            'filename'   => $filename,
            'identifier' => $identifier
        );
        if ( ! $data = $DB->get_records('plagiarism_rucont_files', $params) ) 
        {
            return '';
        }
        if ( ! empty($data) )
        {
            $data = current($data);
        } else
        {
            return '';
        }
        
        $markclass = '';
        if ( $data->statuscode == 'ready' ) 
        {// Отчет по элементу готов
            $score     = $data->similarityscore;
            $reporturl = $data->reporturl;
            $scorelang = get_string('similarityscore', 'plagiarism_rucont', $score);
            $markclass = 'mark-bad';
            if ($score <= 20) 
            {
                $markclass = 'mark-excellent';
            } else if ($score <= 50) 
            {
                $markclass = 'mark-moderate';
            }
        } elseif ( $data->statuscode == 'upload' && is_null($data->errorcode) )
        {// Элемент еще не проверен
            $scorelang = get_string('processingyet', 'plagiarism_rucont');
            $reporturl = '';
        } else
        {// Ошибка при обработке
            $scorelang = get_string('error_process', 'plagiarism_rucont');
            $reporturl = '';
        }
        
        // Формирование информации по отчету
        $notification  = $OUTPUT->box_start('submissioncheck');
        // Прогресс-бар с процентом оригинальности или сообщением "В прогрессе".
        $bar      = html_writer::tag('div', $scorelang, array('class' => "ap bar {$markclass}"));
        $progress = html_writer::tag('div', $bar, array('class' => 'ap progress'));
        $notification .= $progress;
        if ( ! empty($reporturl) ) 
        {// Ссылка на отчёт
            $notification .= html_writer::tag('a', get_string('reportlink', 'plagiarism_rucont'), array('href' => $reporturl));
        }
        $notification .= $OUTPUT->box_end(true);
        return $notification;
    }
}
