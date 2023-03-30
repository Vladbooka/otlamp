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
 * Тема СЭО 3KL. Класс привязки профиля к выбранному языку
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

class lang extends base
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
        return 'lang';
    }

    /**
     * Получить локализованное название привязки
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('link_lang_name', 'theme_opentechnology');
    }

    /**
     * Получить локализованное описание привязки
     *
     * @return string
     */
    public function get_description()
    {
        return get_string('link_lang_descripton', 'theme_opentechnology');
    }

    /**
     * Получить информацию о привязке
     *
     * @return string
     */
    public function get_info()
    {
        global $DB;
        // Получение кода языка
        $lang = $this->get_lang();
        if ( $lang )
        {
            $langs = get_string_manager()->get_list_of_translations();
            if ($langs)
            {
                return $langs[$lang];
            } else {
                return get_string('profile_link_lang_error_notfound', 'theme_opentechnology');
            }
        }
        return get_string('profile_link_lang_error_defaultlinkinfo', 'theme_opentechnology');
    }

    /**
     * Получить информацию о привязке
     *
     * @return string
     */
    public function get_lang()
    {
        $data = $this->get_data();
        if ( $data )
        {
            return $data;
        }
        return null;
    }

    /**
     * Получить приоритетную привязку текущего типа, направленную на выбранный пользователем язык
     *
     * @param $lang - Код язык
     *
     * @return array - Массив привязок
     */
    public function get_link($lang)
    {
        global $DB;

        // Найти все привязки, нацеленные на выбранный пользователем язык
        $linkdatafieldname = $DB->sql_compare_text('linkdata');
        $where = $linkdatafieldname.' = :lang AND linktype = :linktype';
        $placeholders = ['lang' => $lang, 'linktype' => $this->get_code()];

        $linkrecords = $DB->get_records_select(
            'theme_opentechnology_plinks',
            $where,
            $placeholders
        );

        if (!empty($linkrecords))
        {// Найдены привязки к выбранному языку

            // Получение последней привязки
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
        $langs = get_string_manager()->get_list_of_translations();
        if( ! empty($langs) )
        {
            $mform->addElement('select', 'linkedlang', get_string('selectlang', 'theme_opentechnology'), $langs);
            $mform->setType('linkedlang', PARAM_TEXT);
        } else 
        {
            $mform->addElement('static', 'linkedlang', get_string('langsnotfound', 'theme_opentechnology'));
        }
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
        $langs = get_string_manager()->get_list_of_translations();
        if( ! array_key_exists($data['linkedlang'], $langs) )
        {
            $errors['linkedlang'] = get_string('profile_link_lang_error_notfound', 'theme_opentechnology');
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
        $linkrecord->linkdata = $formdata->linkedlang;
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