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
 * Тема СЭО 3KL. Форма создания привязки профиля к пользователю
 *
 * @package    theme_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace theme_opentechnology\links;

global $CFG;
require_once($CFG->libdir.'/formslib.php');

use moodleform;
use moodle_url;
use stdClass;
use theme_opentechnology\profilemanager;
use theme_opentechnology\links\manager as linksmanager;

class form_link_to_user extends moodleform
{
    /**
     * Обьявление полей формы
     */
    public function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Добавление свойств формы
        $this->user = $this->_customdata->user;
        $this->returnto = $this->_customdata->returnto ?? new moodle_url();
        
        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        
        
        // Инициализация менеджера профилей
        $profilemanager = profilemanager::instance();
        
        // Получение профиля текущей страницы
        $userlink = linksmanager::instance()->get_link_by_type('user', $this->user);
        $currentprofileid = isset($userlink) ? $userlink->get_profile_id() : -1;
        
        $options = [-1 => get_string('link_to_user_dont_override', 'theme_opentechnology')];
        $profiles = $profilemanager->get_profiles();
        foreach ($profiles as $profile)
        {
            $options[$profile->get_id()] = $profile->get_name();
        }
        $mform->addElement('select', 'profileid', get_string('link_to_user_select_profile', 'theme_opentechnology'), $options);
        $mform->setDefault('profileid', array_key_exists($currentprofileid, $options) ? $currentprofileid : -1);
        
        $this->add_action_buttons();
    }
    
    /**
     * Проверка данных формы
     *
     * @param array $data - данные, пришедшие из формы
     *
     * @return array - массив ошибок, или пустой массив, если ошибок нет
     */
    function validation($data, $files)
    {
        global $USER;
        
        // Массив ошибок
        $errors = parent::validation($data, $files);
        
        // Получение системного контекста
        $systemcontext = \context_system::instance();
        
        // требуется право на управление привязками
        $accessallowed = has_capability('theme/opentechnology:profile_links_manage', $systemcontext);
        if (!$accessallowed && $this->user->id == $USER->id)
        {// если права на управление привязками нет, но пользователь привязывает профиль для себя
            // будет достаточно права доступа для назнчения профиля себе
            $accessallowed = has_capability('theme/opentechnology:profile_link_self', $systemcontext);
        }
        if (!$accessallowed)
        {// никаких прав на назначение профиля не найдено
            $errors['profileid'] = get_string('profile_link_save_error_access_denied', 'theme_opentechnology');
        }
        
        return $errors;
    }
    
    
    /**
     * Обработка данных формы
     *
     * @return bool
     */
    public function process()
    {
        global $DB;
        
        if ($data = $this->get_data())
        {
            // Удаление хранящихся ранее объектов привязки профилей к текущему пользователю
            $DB->delete_records_select(
                'theme_opentechnology_plinks',
                "linktype = :linktype AND " . $DB->sql_compare_text('linkdata') . " = :userid",
                ['userid' => $this->user->id, 'linktype' => 'user']
            );
            
            if ($data->profileid != -1)
            {
                // Объект привязки
                $linkrecord = new stdClass();
                $linkrecord->profileid = $data->profileid;
                $linkrecord->linktype = 'user';
                $linkrecord->linkdata = $this->user->id;
                
                // Добавление нового объекта привязки
                $DB->insert_record('theme_opentechnology_plinks', $linkrecord);
            }
            
            redirect($this->returnto);
        } elseif ($this->is_cancelled())
        {
            redirect($this->returnto);
        }
            
    }
}