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
 * Тема СЭО 3KL. Форма создания привязки.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace theme_opentechnology\links;

global $CFG;
require_once($CFG->libdir.'/formslib.php');

use moodleform;
use moodle_url;
use context_system;
use stdClass;
use moodle_exception;
use theme_opentechnology\profiles\base as profilebase;
use theme_opentechnology\profilemanager;

class formsave extends moodleform 
{   
    /**
     * Профиль
     * 
     * @var profilebase
     */
    private $profile = null;
    
    private $linktype = '';
    
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
        if ( isset($this->_customdata->linktype) )
        {
            $this->linktype = $this->_customdata->linktype;
        }
        
        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'profileid', $this->profile->get_id());
        $mform->setType('profileid', PARAM_INT);
        
        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement(
            'static',
            'hidden',
            ''
        );
        
        // Блок выбора типа привязки
        $group = [];
        $linktypes = manager::instance()->get_link_types();
        foreach ( $linktypes as &$linktype )
        {
            $linktype = $linktype->get_name();
        }
        $linktypes = ['' => get_string('profile_link_type_select', 'theme_opentechnology')] + $linktypes;
        $typeselect = $mform->createElement(
            'select',
            'type',
            get_string('profile_link_type_name', 'theme_opentechnology'),
            $linktypes
        );
        if ( isset($linktypes[$this->linktype]) )
        {// Профиль определен
            $typeselect->setValue($this->linktype);
        }
        $group[] = $typeselect;
        
        // Подтверждение типа привязки
        $group[] = $mform->createElement(
            'submit',
            'selecttype',
            get_string('profile_link_selecttype_name', 'theme_opentechnology')
        );
        $mform->addGroup($group, 'linktypegroup', '', '', false);
        
        // Инициализация менеджера привязок
        $linkmanager = manager::instance();
        
        // Получение текущей привязки
        $link = $linkmanager->get_link($this->linktype);
        
        // Добавление полей, определенных в привязке
        if ( $link )
        {
            // Добавление специфичных полей привязки в форму
            $link->saveform_definition($this, $mform);
            
            // Действия формы
            $group = [];
            $group[] = $mform->createElement(
                'submit',
                'submit',
                get_string('profile_link_confirm_name', 'theme_opentechnology')
            );
            $group[] = $mform->createElement(
                'submit',
                'cancel',
                get_string('profile_link_cancel_name', 'theme_opentechnology')
            );
            $mform->addGroup($group, 'submit', '', '', false);
            
            // Значения по умолчанию
            $link->saveform_set_data($this, $mform);
            
            // Применение проверки ко всем элементам
            $mform->applyFilter('__ALL__', 'trim');
        }
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

        if ( empty($data['profileid']) )
        {// Профиль-владелец не указан
            $errors['static'] = get_string('profile_link_save_error_profileid_empty', 'theme_opentechnology');
        } else 
        {// Проверка существования профиля
            $profile = profilemanager::instance()->get_profile((int)$data['profileid']);
            $id = $profile->get_id();
            if ( empty($id) )
            {// Профиль не найден
                $errors['static'] = get_string('profile_link_save_error_profileid_notfound', 'theme_opentechnology');
            }
        }
        if ( ! empty($data['type']) && (string)$this->linktype === (string)$data['type'] )
        {// Указан тип привязки
            // Инициализация менеджера привязок
            $linkmanager = manager::instance();
            
            // Получение текущей привязки
            $link = $linkmanager->get_link($data['type']);
            
            if ( empty($link) )
            {// Привязка не найдена
                $errors['type'] = get_string('profile_link_save_error_type_notfound', 'theme_opentechnology');
            } else 
            {
                // Дополнительная валидация
                $link->saveform_validation($errors, $this, $mform, $data, $files);
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
        global $DB;
        
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        if ( $this->is_submitted() && confirm_sesskey() &&
             $this->is_validated() && $formdata = $this->get_data()
           )
        {
            if ( isset($formdata->cancel) )
            {// Отмена сохранения привязки
                $returnurl = new moodle_url(
                    '/theme/opentechnology/profiles/links.php',
                    ['id' => $this->profile->get_id()]
                );
                redirect($returnurl);
            }
            if ( (string)$this->linktype !== (string)$formdata->type )
            {// Форма целевой привязки
                $redirect = new moodle_url(
                    '/theme/opentechnology/profiles/link_create.php',
                    [
                        'profileid' => $this->profile->get_id(),
                        'linktype' => $formdata->type
                    ]
                );
                redirect($redirect);
            }
            
            // Инициализация менеджера привязок
            $linkmanager = manager::instance();
            
            // Получение текущей привязки
            $link = $linkmanager->get_link($this->linktype);
            
            if ( $link )
            {// Привязка определена
                $transaction = $DB->start_delegated_transaction();
                
                
                // Инициализация привязки
                $linkrecord = new stdClass();
                $linkrecord->profileid = $this->profile->get_id();
                $linkrecord->linktype = $this->linktype;
                $linkrecord->linkdata = null;
                
                // Препроцесс
                $link->saveform_preprocess($this, $mform, $formdata, $linkrecord);
                
                // Удаление хранящихся ранее объектов привязки профилей к указанному объекту указанного типа
                $DB->delete_records_select(
                    'theme_opentechnology_plinks',
                    "linktype = :linktype AND " . $DB->sql_compare_text('linkdata') . " = :linkdata",
                    [
                        'linkdata' => $linkrecord->linkdata, 
                        'linktype' => $linkrecord->linktype
                    ]
                );
                
                // Добавление привязки
                $id = $DB->insert_record('theme_opentechnology_plinks', $linkrecord);
                if ( $id )
                {
                    $DB->commit_delegated_transaction($transaction);
                    
                    // Постпроцесс
                    $link->saveform_postprocess($this, $mform, $formdata, $id);
                } else 
                {
                    $DB->rollback_delegated_transaction($transaction);
                }
                
                $returnurl = new moodle_url(
                    '/theme/opentechnology/profiles/links.php',
                    ['id' => $this->profile->get_id()]
                );
                redirect($returnurl);
            }
        }
    }
}