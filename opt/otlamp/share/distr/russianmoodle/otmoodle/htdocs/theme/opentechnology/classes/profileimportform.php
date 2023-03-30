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
 * Тема СЭО 3KL. Импорт профиля.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace theme_opentechnology;

global $CFG;
require_once($CFG->libdir.'/formslib.php');

use moodleform;
use moodle_url;
use context_system;
use stdClass;

class profileimportform extends moodleform 
{   
    /**
     * Профиль
     * 
     * @var base
     */
    private $profile = null;
    
    /**
     * Обьявление полей формы
     */
    public function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Добавление свойств формы
        if ( isset($this->_customdata->profile) )
        {
            $this->profile = $this->_customdata->profile;
        }
        
        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);

        // Файл импорта
        $mform->addElement(
            'filemanager',
            'profile_import_archive',
            get_string('profile_import_archive', 'theme_opentechnology'),
            null,
            ['accepted_types' => ['.zip']]
        );
        
        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
        
        
        $group = [];
        $group[] = $mform->createElement(
            'submit', 
            'submit', 
            get_string('profile_import_confirm', 'theme_opentechnology')
        );
        $group[] = $mform->createElement(
            'submit',
            'cancel',
            get_string('profile_import_cancel', 'theme_opentechnology')
        );
        $mform->addGroup($group, 'submit', '', '', false);


        // Поиск пустой пользовательской драфтзоны для подключения к файлпикеру
        $draftitemid = file_get_submitted_draft_itemid('profile_import_pack');
        
        // Подготовка зоны для черновиков импорта
        file_prepare_draft_area(
            $draftitemid,
            context_system::instance()->id,
            'theme_opentechnology',
            'import',
            $draftitemid
        );
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
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
    
        if ( isset($data['cancel']) )
        {// Отмена сохранения профиля
            return [];
        }
        
        // Массив ошибок
        $errors = parent::validation($data, $files);
        
        return $errors;
    }
    
    /**
     * Обработка данных формы
     *
     * @return bool
     */
    public function process()
    {
        global $USER;
        
        if ( $this->is_submitted() && confirm_sesskey() &&
             $this->is_validated() && $formdata = $this->get_data()
           )
        {
            if ( isset($formdata->cancel) )
            {// Отмена импорта профиля
                $returnurl = new moodle_url(
                    '/theme/opentechnology/profiles/index.php'
                );
                redirect($returnurl);
            }
            
            if( isset($this->profile) )
            {
                $profilecode = $this->profile->get_code();
                
                // Получение менеджера файлов
                $fs = get_file_storage();
                
                // Поиск пустой пользовательской драфтзоны для подключения к файлпикеру
                $draftitemid = file_get_submitted_draft_itemid('profile_import_archive');
                
                // Получение загруженных файлов
                $draftfiles = $fs->get_area_files(
                    \context_user::instance($USER->id)->id, 
                    'user', 
                    'draft', 
                    $draftitemid, 
                    'id'
                );
                
                // Распаковываем все, что можно
                foreach($draftfiles as $draftfile)
                {
                    $this->profile->import_settings($draftfile);                
                    break;
                }
            }
        }
    }
}