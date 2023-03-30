<?php

////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
//                                                                        //
// This program is free software: you can redistribute it and/or modify   //
// it under the terms of the GNU General Public License as published by   //
// the Free Software Foundation, either version 3 of the Licensen.        //
//                                                                        //
// This program is distributed in the hope that it will be useful,        //
// but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// GNU General Public License for more details.                           //
//                                                                        //
// You should have received a copy of the GNU General Public License      //
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
//                                                                        //
////////////////////////////////////////////////////////////////////////////

/**
 * Интерфейс управления причинами отсутствия
 *
 * @package    im
 * @subpackage schabsenteeism
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение базовых функций плагина
global $DOF;
// Подключение библиотеки форм
$DOF->modlib('widgets')->webform();

/**
 * Класс создания/редактирования причины отсутствия
 */
class dof_im_schabsenteeism_save extends dof_modlib_widgets_form
{  
    /**
     * @var dof_control
     */
    protected $dof;
    
    /**
     * ID причины
     * 
     * @var int
     */
    protected $id = 0;
    
    /**
     * GET параметры для ссылки
     * 
     * @var array
     */
    protected $addvars = [];
    
    /**
     * URL для возврата
     * 
     * @var string
     */
    protected $returnurl = NULL;
    
    /**
     * Обьявление полей формы
     *
     * @see dof_modlib_widgets_form::definition()
     */
    protected function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Добавление свойств
        $this->dof = $this->_customdata->dof;
        $this->addvars = $this->_customdata->addvars;
        $this->id = $this->_customdata->id;
        if ( isset($this->_customdata->returnurl) && ! empty($this->_customdata->returnurl) )
        {// Передан url возврата
            $this->returnurl = $this->_customdata->returnurl;
        } else 
        {// Установка url возврата на страницу обработчика
            $this->returnurl = $mform->getAttribute('action');
        }
        
        // Скрытые поля
        $mform->addElement('hidden', 'id', $this->id);
        $mform->setType('id', PARAM_INT);
        
        // Заголовок - Основная информация
        $headergroup = [];
        $headergroup[] = $mform->createElement(
            'header',
            'form_header',
            $this->dof->get_string('form_save_header', 'schabsenteeism')
        );
        $mform->addGroup($headergroup, 'groupheader_main', '', '', true);
        
        // Название причины
        $mform->addElement(
            'text',
            'name',
            $this->dof->get_string('form_save_name_label', 'schabsenteeism')
        );
        $mform->setType('name', PARAM_TEXT);
        
        // Тип причины
        $mform->addElement(
            'select',
            'unexplained',
            $this->dof->get_string('form_save_type_label', 'schabsenteeism'),
            $this->dof->storage('schabsenteeism')->get_types()
        );

        // Сохранить/Отменить
        $this->add_action_buttons(true, $this->dof->get_string('form_save_submit_label', 'schabsenteeism'));
    }
    
    /**
     * Заполнение формы данными
     */
    public function definition_after_data()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        if ( ! empty($this->id) )
        {// Заполнение значениями
            // Получение экземпляра причина отсутствия из базы
            $schabsenteeism = $this->dof->storage('schabsenteeism')->get($this->id);
            
            if ( ! empty($schabsenteeism) )
            {// Заполнение
                $mform->setDefault('name', $schabsenteeism->name);
                $mform->setDefault('unexplained', $schabsenteeism->unexplained);
            }
        }
    }
    
    /**
     * Проверки введенных значений в форме
     */
    public function validation($data, $files)
    {
        // Массив ошибок
        $errors = [];
        
        // Валидация типа
        $types = $this->dof->storage('schabsenteeism')->get_types();
        if ( ! isset($types[$data['unexplained']]) )
        {// Тип невалиден
            $errors['unexplained'] = $this->dof->get_string('form_save_type_error_notvalid', 'schabsenteeism');
        }
        
        // Валидация имени
        if ( empty($data['name']) )
        {// Тип невалиден
            $errors['name'] = $this->dof->get_string('form_save_name_error_empty', 'schabsenteeism');
        }
        
        return $errors;
    }
    
    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        $mform =& $this->_form;
        
        if ( $this->is_cancelled() )
        {
            redirect($this->returnurl);
        }
        
        if ( $this->is_submitted() && confirm_sesskey() && 
             $this->is_validated() && $formdata = $this->get_data()
           )
        {// Обработка данных формы
            
            $save = new stdClass();
            $save->name = $formdata->name;
            $save->unexplained = $formdata->unexplained;
            if ( ! empty($formdata->id) )
            {
                $save->id = $formdata->id;
            }
            
            if ( $this->dof->storage('schabsenteeism')->save($save) )
            {
                redirect($this->returnurl);
            }
            return $this->dof->get_string('form_save_error_save', 'schabsenteeism');
        }
        return null;
    }
}
?>