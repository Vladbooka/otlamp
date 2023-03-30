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
 * Конструктор форм
 *
 * @package    block_otcustomform
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_otcustomform\utils;

class block_otcustomform_edit_form extends block_edit_form
{
    
    /**
     * {@inheritDoc}
     * @see block_edit_form::specific_definition()
     */
    protected function specific_definition($mform) {
        
        
        $mform->addElement('text', 'config_block_name', get_string('block_name', 'block_otcustomform'));
        $mform->setType('config_block_name', PARAM_RAW);
        
        // Показывать ли заголовок
        $mform->addElement('advcheckbox', 'config_hide_header', get_string('hide_header', 'block_otcustomform'));
        $mform->setDefault('config_hide_header', false);
        
        // Разметка формы
        $mform->addElement('textarea', 'config_form_layout', get_string('form_layout', 'block_otcustomform'), [
            'rows' => '15',
            'style' => 'width: 100%;'
        ]);
        $mform->setType('config_form_layout', PARAM_RAW);
        $mform->addHelpButton('config_form_layout', 'form_layout', 'block_otcustomform');
    }
    
    
    /**
     * {@inheritDoc}
     * @see block_edit_form::get_data()
     */
    public function get_data()
    {
        if ( $data = parent::get_data() )
        {
            //TODO: Если форма изменилась - создать в l_ot_sforms; сохранить новый идентификатор; саму разметку не сохранять
            
            // Разметка формы, сохраняемая пользователем
            $formlayout = $data->config_form_layout;
            // Мы не хотим хранить разметку в блоке, поэтому избавляемся сразу
            unset($data->config_form_layout);
            
            // объект записи в БД
            $record = new stdClass();
            $record->layout = $formlayout;
            
            if ( ! empty($this->block->config->customformid) )
            {
                $record->id = $this->block->config->customformid;
            }
            
            // сохраняем в БД
            if ( $id = utils::save_form_record($record) )
            {
                $data->config_customformid = $id;
            }
        }
        
        return $data;
    }
    
    
    /**
     * {@inheritDoc}
     * @see block_edit_form::set_data()
     */
    function set_data($defaults)
    {
        if ( ! empty($this->block->config->customformid) )
        {
            $record = utils::get_form_record($this->block->config->customformid);
            $this->block->config->form_layout = $record->layout;
        }
        parent::set_data($defaults);
    }
}