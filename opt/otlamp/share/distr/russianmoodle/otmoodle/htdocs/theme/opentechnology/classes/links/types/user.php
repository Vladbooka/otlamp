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
 * Тема СЭО 3KL. Класс привязки профиля к пользователю
 *
 * @package    theme_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_opentechnology\links\types;

use theme_opentechnology\links\base;
use theme_opentechnology\links\manager;
use stdClass;
use dml_exception;
use context_course;
use moodle_page;

class user extends base
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
        return 'user';
    }
    
    /**
     * Получить локализованное название привязки
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('link_user_name', 'theme_opentechnology');
    }
    
    /**
     * Получить локализованное описание привязки
     *
     * @return string
     */
    public function get_description()
    {
        return get_string('link_user_descripton', 'theme_opentechnology');
    }
    
    /**
     * Получить информацию о привязке
     *
     * @return string
     */
    public function get_info()
    {
        global $DB;
        // Получение ID привязанного курса
        $userid = $this->get_userid();
        if ( $userid )
        {
            $user = $DB->get_record('user', ['id' => $userid]);
            if ($user)
            {
                return fullname($user);
            } else {
                return get_string('profile_link_user_error_notfound', 'theme_opentechnology');
            }
        }
        return get_string('profile_link_user_error_defaultlinkinfo', 'theme_opentechnology');
    }
    
    /**
     * Получить информацию о привязке
     *
     * @return string
     */
    public function get_userid()
    {
        $data = $this->get_data();
        if ( $data )
        {
            return (int)$data;
        }
        return null;
    }
    
    /**
     * Получить приоритетную привязку текущего типа, направленную на целевого пользователя
     *
     * @param $user - Целевой пользователь
     *
     * @return array - Массив привязок
     */
    public function get_link($user)
    {
        global $DB;
        
        // Найти все привязки, нацеленные на указанного пользователя
        $linkdatafieldname = $DB->sql_compare_text('linkdata');
        $where = $linkdatafieldname.' = :userid AND linktype = :linktype';
        $placeholders = ['userid' => $user->id, 'linktype' => $this->get_code()];
        
        $linkrecords = $DB->get_records_select(
            'theme_opentechnology_plinks',
            $where,
            $placeholders
        );
        
        if (!empty($linkrecords))
        {// Найдены привязки к текущему пользователю
            
            // Получение последней привязки к пользователю
            $linkrecord = array_pop($linkrecords);
            
            // Инициализация привязки страницы
            $link = manager::instance()->get_link((int)$linkrecord->id);
            
            return $link;
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
        // Выбор пользователя, к которому будет установлена привязка
        $attributes = [
            'ajax' => 'theme_opentechnology/form-user-selector'
        ];
        $options = [
            'multiple' => false
        ];
        
        $mform->addElement('autocomplete', 'linkeduser', get_string('selectusers', 'enrol_manual'), $options, $attributes);
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
        global $DB, $USER;
        
        $user = $DB->get_record('user', ['id' => $data['linkeduser'], 'deleted' => 0]);
        if (empty($user))
        {
            $errors['linkeduser'] = get_string('profile_link_user_error_notfound', 'theme_opentechnology');
        }
        
        // Получение системного контекста
        $systemcontext = \context_system::instance();
        
        // требуется право на управление привязками
        $accessallowed = has_capability('theme/opentechnology:profile_links_manage', $systemcontext);
        if (!$accessallowed && $data['linkeduser'] == $USER->id)
        {// если права на управление привязками нет, но пользователь привязывает профиль для себя
            // будет достаточно права доступа для назнчения профиля себе
            $accessallowed = has_capability('theme/opentechnology:profile_link_self', $systemcontext);
        }
        if (!$accessallowed)
        {// никаких прав на назначение профиля не найдено
            $errors['linkeduser'] = get_string('profile_link_save_error_access_denied', 'theme_opentechnology');
        }
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
        $linkrecord->linkdata = $formdata->linkeduser;
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