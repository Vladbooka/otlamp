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
 * Слайдер изображений. Форма сохранения слайда.
 * 
 * @package    block
 * @subpackage otslider
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_otslider\slides;

global $CFG;
require_once($CFG->libdir.'/formslib.php');

use moodleform;
use moodle_url;
use block_otslider\slider as slider;
use block_otslider\exception\slider as exception_slider;
use block_otslider;

class formsave extends moodleform 
{   
    /**
     * Слайдер
     *
     * @var slider
     */
    private $slider = null;
    
    /**
     * Экземпляр блока
     *
     * @var block_otslider
     */
    private $block = null;
    
    /**
     * Обьявление полей формы
     */
    public function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Добавление свойств формы
        $this->block = $this->_customdata->block;
        $this->slider = $this->_customdata->slider;
        $this->backurl = $this->_customdata->backurl;

        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'blockid', $this->block->instance->id);
        $mform->setType('blockid', PARAM_INT);
        $mform->addElement('hidden', 'backurl', $this->backurl);
        $mform->setType('backurl', PARAM_URL);
        
        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement(
            'static',
            'hidden',
            ''
        );
        
        // Получение слайдов
        $slides = $this->slider->get_slides();
        foreach ( $slides as $slideid => $slide )
        {
            $prefix = 'slide_'.$slideid;
            
            // Блок настройки текущего слайда
            $mform->addElement('header', 'slide_header', $slide->get_name());
            
            // Блок управления текущим слайдом
            $group = [];
            // Перемещение вверх
            $group[] = $mform->createElement(
                'submit',
                'orderup',
                get_string('slidemanager_formsave_slide_orderup_label', 'block_otslider')
            );
            // Перемещение вниз
            $group[] = $mform->createElement(
                'submit',
                'orderdown',
                get_string('slidemanager_formsave_slide_orderdown_label', 'block_otslider')
            );
            // Удаление
            $group[] = $mform->createElement(
                'submit',
                'delete',
                get_string('slidemanager_formsave_slide_delete_label', 'block_otslider')
            );
            $mform->addGroup($group, 'actions_'.$prefix, '', '', true);
            
            // Добавление настроек слайда
            $slide->saveform_definition($this, $mform, $prefix);
            
            // Значения настроек слайда
            $slide->saveform_set_data($this, $mform, $prefix);
        }
        
        if ( ! empty($slides) )
        {
            // Действия формы
            $group = [];
            $group[] = $mform->createElement(
                'submit',
                'submit',
                get_string('slidemanager_formsave_confirm_label', 'block_otslider')
            );
            $mform->addGroup($group, 'submit', '', '', false);
        }
        
        // Блок добавления нового слайда
        $group = [];
        $mform->addElement('header', 'createslide_header', get_string('slidemanager_formsave_createslide_header_label', 'block_otslider'));
        $mform->setExpanded('createslide_header');
        $slidetypes = $this->slider->get_slide_types();
        $select = [
            '' => get_string('slidemanager_formsave_createslide_select_select', 'block_otslider')
        ];
        foreach ( $slidetypes as $slidetypecode => $slidetypeclass )
        {
            $select[$slidetypecode] = $slidetypeclass::get_name();
        }
        $typeselect = $mform->createElement(
            'select',
            'type',
            get_string('slidemanager_formsave_createslide_select_label', 'block_otslider'),
            $select
        );
        $group[] = $typeselect;
        
        // Подтверждение типа слайда
        $group[] = $mform->createElement(
            'submit',
            'selecttype',
            get_string('slidemanager_formsave_createslide_submit_label', 'block_otslider')
        );
        $mform->addGroup($group, 'createslide', '', '', true);
        
        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
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
    
        // Массив ошибок
        $errors = parent::validation($data, $files);
        
        // Валидация добавления нового слайда
        if ( isset($data['createslide']['selecttype']) )
        {// Запрос добавления нового слайда
            $avasilabletypes = $this->slider->get_slide_types();
            if ( empty($data['createslide']['type']) )
            {// Не выбран тип добавляемого слайда
                $errors['createslide'] = get_string('slidemanager_formsave_createslide_select_error_empty', 'block_otslider');
            } elseif ( ! isset($avasilabletypes[$data['createslide']['type']]) )
            {// Выбран невалидный тип слайда
                $errors['createslide'] = get_string('slidemanager_formsave_createslide_select_error_notvalid', 'block_otslider');
            }
            return $errors;
        } 
        
        // Получение слайдов
        $slides = $this->slider->get_slides();
        
        // Валидация исполнения задачи по слайду
        foreach ( $slides as $slideid => $slide )
        {
            $prefix = 'actions_slide_'.$slideid;
        
            if ( isset($data[$prefix]) )
            {// Найдена задача по слайду
                return $errors;
            }
        }
       
        // Валидация настроек слайда 
        foreach ( $slides as $slideid => $slide )
        {
            $prefix = 'slide_'.$slideid;
            
            // Валидация настроек слайда
            $slide->saveform_validation($errors, $this, $mform, $data, $files, $prefix);
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
            if ( isset($formdata->createslide['selecttype']) )
            {// Задача по добавлению нового слайда
                // Попытка добавить слайд указанного типа в слайдер
                try
                {
                    $this->slider->add_slide($formdata->createslide['type']);
                } catch ( exception_slider $e )
                {// Ошибка добавления слайда
                    return get_string($e->errorcode, 'block_otslider');
                }
                
                // Перезагрузка формы
                $url = new moodle_url(
                    $mform->getAttribute('action'), 
                    [
                        'blockid' => $formdata->blockid,
                        'backurl' => $formdata->backurl
                    ]
                );
                redirect($url);
            }
            
            // Получение слайдов
            $slides = $this->slider->get_slides();
            
            // Исполнение задач по слайду
            foreach ( $slides as $slideid => $slide )
            {
                $prefix = 'actions_slide_'.$slideid;
                if ( isset($formdata->$prefix) )
                {// Найдена задача по слайду
                    // Получение задачи
                    $action = key($formdata->$prefix);
                    
                    switch ($action)
                    {
                        case 'delete' :
                            
                            // Попытка удаления слайда
                            try 
                            {
                                $this->slider->delete_slide($slideid);
                            } catch ( exception_slider $e )
                            {// Ошибка удаления слайда
                                return get_string($e->errorcode, 'block_otslider');
                            }
                            break;
                        case 'orderup' :
                            // Попытка перемещения слайда
                            try
                            {
                                $this->slider->orderup_slide($slideid);
                            } catch ( exception_slider $e )
                            {// Ошибка удаления слайда
                                return get_string($e->errorcode, 'block_otslider');
                            }
                            break;
                        case 'orderdown' :
                            // Попытка перемещения слайда
                            try
                            {
                                $this->slider->orderdown_slide($slideid);
                            } catch ( exception_slider $e )
                            {// Ошибка удаления слайда
                                return get_string($e->errorcode, 'block_otslider');
                            }
                            break;
                        default :
                            return get_string('error_slider_slide_action_error_notvalid', 'block_otslider');
                    }
                    
                    // Перезагрузка формы
                    $url = new moodle_url(
                        $mform->getAttribute('action'),
                        [
                            'blockid' => $formdata->blockid,
                            'backurl' => $formdata->backurl
                        ]
                    );
                    redirect($url);
                }
            }

            // Сохранение слайдов
            foreach ( $slides as $slideid => $slide )
            {
                $prefix = 'slide_'.$slideid;
            
                // Сохранение настроек слайда
                $slide->saveform_process($this, $mform, $formdata, $prefix);
            }
            
            // Перезагрузка формы
            $url = new moodle_url(
                $mform->getAttribute('action'),
                [
                    'blockid' => $formdata->blockid,
                    'backurl' => $formdata->backurl
                ]
            );
            redirect($url);
        }
    }
}