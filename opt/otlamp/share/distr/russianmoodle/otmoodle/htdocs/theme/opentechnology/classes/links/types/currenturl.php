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
 * Тема СЭО 3KL. Класс привязки по URL
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_opentechnology\links\types;

use theme_opentechnology\links\base;
use theme_opentechnology\links\manager;
use stdClass;
use dml_exception;
use context_course;
use moodle_page;
use moodle_url;

class currenturl extends base
{
    /**
     * Конструктор
     *
     * @param int - ID профиля
     */
    public function __construct($profileid = null)
    {
    }
    
    /**
     * Проверка доступности привязки
     *
     * @return bool
     */
    public function is_enabled()
    {
        return true;
    }
    
    /**
     * Получить код привязки
     *
     * @return string
     */
    public function get_code()
    {
        return 'currenturl';
    }
    
    /**
     * Получить локализованное название привязки
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('link_currenturl_name', 'theme_opentechnology');
    }
    
    /**
     * Получить локализованное описание привязки
     *
     * @return string
     */
    public function get_description()
    {
        return get_string('link_currenturl_descripton', 'theme_opentechnology');
    }
    
    /**
     * Получить информацию о привязке
     *
     * @return string
     */
    public function get_info()
    {
        // Получение привязанного URL
        $url = $this->get_url();
        if ( $url )
        {
            return get_string('profile_link_currenturl_info', 'theme_opentechnology', $url);
        }
        return get_string('profile_link_currenturl_error_defaultlinkinfo', 'theme_opentechnology');
    }
    
    /**
     * Получить информацию о привязке
     *
     * @return string
     */
    public function get_url()
    {
        $data = $this->get_data();
        if ( $data )
        {
            return (string)$data;
        }
        return null;
    }
    
    /**
     * Получить приоритетную привязку текущего типа, направленную на целевую страницу
     *
     * @param $page - Целевая страница
     *
     * @return array - Массив привязок
     */
    public function get_link($page)
    {
        global $DB;
        
        // Получение текущего URL страницы
        if( $page->has_set_url() )
        {
            $pageurl = $page->url;
            $pageurlstring = $pageurl->get_path();
            
            // Поиск всех привязок, указывающих на текущий хост
            $linkdatafieldname = $DB->sql_compare_text('linkdata');
            $where = $linkdatafieldname.' LIKE :url AND linktype = :linktype';
            $placeholders = ['url' => '%'.$pageurl->get_host().'%', 'linktype' => $this->get_code()];
            $linkrecords = (array)$DB->get_records_select(
                'theme_opentechnology_plinks',
                $where,
                $placeholders
            );
            
            // Поиск наиболее точно подходящего URL среди привязок
            $targetlink = null;
            foreach ( $linkrecords as $linkrecord )
            {
                // URL привязки
                $linkurl = new moodle_url($linkrecord->linkdata);
                $linkurlstring = $linkurl->get_path();
            
                // Фильтрация с учетом WWW
                if ( $linkurl->get_host() !== $pageurl->get_host() )
                {
                    continue;
                }
            
                // Посимвольная проверка URL привязки на соответствие текущему URL
                for ( $sumbol = 0; $sumbol < mb_strlen($linkurlstring); $sumbol++ )
                {
                    $linkurlpart = $linkurlstring{$sumbol};
                    $pageurlpart = mb_substr($pageurlstring, $sumbol, 1);
            
                    if ( $pageurlpart !== $linkurlpart )
                    {// URL привязки не соответствует текущему
                        continue 2;
                    }
                }
            
                if ( $targetlink === null )
                {// Найдена первая подходящая привязка
                    $targetlink = $linkrecord;
                } else
                {// Требуется сравнить привзки
            
                    $targetlinkurl = new moodle_url($targetlink->linkdata);
                    if ( mb_strlen($targetlinkurl->get_path()) > mb_strlen($linkurlstring) )
                    {// Текущая линковка более точно соответствует URL страницы
                        $targetlink = $linkrecord;
                    }
                }
            }
            
            if ( $targetlink )
            {// Найдена привязка
                // Инициализация привязки страницы
                return manager::instance()->get_link((int)$targetlink->id);
            }
        }
        
        // Не найдено ни одной привязки
        return null;
    }
    
    /**
     * Добавление полей в форму сохранения привязки
     *
     * @param formsave $saveform - Объект формы
     * @param MoodleQuickForm $mform - Объект конструктора формы
     *
     * @return void
     */
    public function saveform_definition($saveform, $mform)
    {
        // Указание URL, для которого будет назначаться профиль
        $mform->addElement(
            'text',
            'linkedurl',
            get_string('profile_link_currenturl_seturl_name', 'theme_opentechnology')
        );
        $mform->setType('linkedurl', PARAM_URL);
        $mform->addRule(
            'linkedurl',
            get_string('profile_link_currenturl_seturl_error_url', 'theme_opentechnology'),
            'required',
            PARAM_URL
        );
    }
    
    /**
     * Предварительная обработка полей формы сохранения привязки
     *
     * Организация заполнения полей данными
     *
     * @param base $profile - Профиль-владелец привязки
     * @param formsave $saveform - Объект формы
     * @param MoodleQuickForm $mform - Объект конструктора формы
     *
     * @return void
     */
    public function saveform_set_data($saveform, $mform)
    {
    }
    
    /**
     * Валидация полей формы сохранения экземпляра вопроса
     *
     * @param array $errors - Массив ошибок валидации
     * @param formsave $saveform - Объект формы
     * @param MoodleQuickForm $mform - Объект конструктора формы
     * @param array $data - Данные формы сохранения
     * @param array $files - Загруженные файлы формы сохранения
     *
     * @return void
     */
    public function saveform_validation(&$errors, $saveform, $mform, $data, $files)
    {
    }
    
    /**
     * Прероцесс сохранения вопроса
     *
     * @param formsave $saveform - Объект формы
     * @param MoodleQuickForm $mform - Объект конструктора формы
     * @param stdClass $formdata - Данные формы сохранения
     * @param stdClass $linkrecord - Запись для сохранения в БД
     *
     * @return void
     */
    public function saveform_preprocess($saveform, $mform, $formdata, &$linkrecord)
    {
        $linkrecord->linkdata = $formdata->linkedurl;
    }
    
    /**
     * Постпроцесс сохранения вопроса
     *
     * @param formsave $saveform - Объект формы
     * @param MoodleQuickForm $mform - Объект конструктора формы
     * @param stdClass $formdata - Данные формы сохранения
     * @param int $id - ID сохраненного вопроса
     *
     * @return void
     */
    public function saveform_postprocess($saveform, $mform, $formdata, $id)
    {
    }
}