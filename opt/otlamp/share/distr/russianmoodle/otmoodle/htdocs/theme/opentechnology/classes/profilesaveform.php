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
 * Тема СЭО 3KL. Сохранение профиля.
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
use moodle_exception;

class profilesaveform extends moodleform 
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
            $record = $this->profile->get_record();
        }
        
        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'id', $this->profile->get_id());
        $mform->setType('id', PARAM_INT);
        
        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement(
            'static',
            'hidden',
            ''
        );
        
        // Название
        $mform->addElement(
            'text', 
            'name', 
            get_string('profile_save_name', 'theme_opentechnology')
        );
        $mform->setType('name', PARAM_TEXT);
        if ( isset($record->name) )
        {// Редактирование профиля
            $mform->setDefault('name', $record->name);
        }
        
        // Код
        $mform->addElement(
            'text',
            'code',
            get_string('profile_save_code', 'theme_opentechnology')
        );
        $mform->setType('code', PARAM_ALPHANUM);
        if (isset($this->_customdata->profilecode))
        {// создание из файла настроек
            $mform->setDefault('code', $this->_customdata->profilecode);
            $mform->freeze('code');
        }
        if ( isset($record->code) )
        {// Редактирование профиля
            $mform->setDefault('code', $record->code);
            $mform->freeze('code');
        }
          
        // Профиль по умолчанию
        $mform->addElement(
            'selectyesno',
            'default',
            get_string('profile_save_default', 'theme_opentechnology')
        );
        $mform->setType('default', PARAM_BOOL);
        if ( isset($record->defaultprofile) )
        {// Редактирование профиля
            $mform->setDefault('default', $record->defaultprofile);
        }
        
        $editoroptions = [
            'subdirs' => 0,
            'maxbytes' => 0,
            'maxfiles' => 0,
            'changeformat' => 0,
            'context' => context_system::instance(),
            'noclean' => 0,
            'trusttext' => 0
        ];
        // Описание
        $mform->addElement(
            'editor',
            'description',
            get_string('profile_save_description', 'theme_opentechnology'),
            $editoroptions
        );
        $mform->setType('description', PARAM_RAW_TRIMMED);
        if ( isset($record->description) )
        {// Редактирование профиля
            $mform->setDefault('description', 
                [
                    'text' => $record->description,
                    'format' => $record->descriptionformat
                ]
            ); 
        }
        
        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
        
        // Поле для сохранения base64 фотографии
        $group = [];
        $group[] = $mform->createElement(
            'submit', 
            'submit', 
            get_string('profile_save_confirm', 'theme_opentechnology')
        );
        $group[] = $mform->createElement(
            'submit',
            'cancel',
            get_string('profile_save_cancel', 'theme_opentechnology')
        );
        $mform->addGroup($group, 'submit', '', '', false);
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
    
        // Валидация имени
        if ( ! trim($data['name']) )
        {// Имя не указано
            $errors['name'] = get_string('profile_save_error_name_empty', 'theme_opentechnology');
        }
        
        // Валидация кода
        if ( isset($data['code']) )
        {// Код доступен для редактирования
            if (  ! trim($data['code']) )
            {// Код не указан
                $errors['code'] = get_string('profile_save_error_code_empty', 'theme_opentechnology');
            } else 
            {
                // Проверка уникальности кода
                $profile = profilemanager::instance()->get_profile($data['code']);
                if ( ! empty($profile) && ! empty($this->profile) &&
                    $profile->get_id() !== $this->profile->get_id() )
                {// Код не уникален
                    $errors['code'] = get_string('profile_save_error_code_notunique', 'theme_opentechnology');
                }
            }
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
        global $PAGE;
        
        if ( $this->is_submitted() && confirm_sesskey() &&
             $this->is_validated() && $formdata = $this->get_data()
           )
        {
            if ( isset($formdata->cancel) )
            {// Отмена сохранения профиля
                
                $returnurl = new moodle_url(
                    '/theme/opentechnology/profiles/index.php'
                );
                redirect($returnurl);
            }
            
            // Сохранение данных
            $profile = new stdClass();
            $profile->name = $formdata->name;
            $profile->code = $formdata->code;
            if (isset($this->_customdata->profilecode))
            {
                $profile->code = $this->_customdata->profilecode;
            }
            $profile->description = $formdata->description['text'];
            $profile->descriptionformat = $formdata->description['format'];
            $profile->defaultprofile = $formdata->default;
            if ( ! empty($this->profile) )
            {// Обновление профиля
                $profile->id = $this->profile->get_id();
            }
            
            // Попытка сохранения профиля
            try 
            {
                $profile = profilemanager::instance()->save_profile($profile);
                
                if (isset($this->_customdata->profilecode))
                {
                    $profile->import_settings($PAGE->theme->dir . '/profiles/overrides/'.$this->_customdata->profilecode.'/settings.zip');
                }
                
                // Редирект на страницу просмотра профиля
                $returnurl = new moodle_url(
                    '/theme/opentechnology/profiles/save.php',
                    ['id' => $profile->get_id()]
                );
                redirect($returnurl);
            } catch ( moodle_exception $e )
            {// Ошибка сохранения  
                print_error($e->errorcode, 'theme_opentechnology');
            }
        }
    }
}