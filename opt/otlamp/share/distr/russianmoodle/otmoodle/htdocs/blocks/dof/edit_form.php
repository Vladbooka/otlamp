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
 * Настройки экземпляра блока Электронный деканат.
 *
 * @package block
 * @subpackage dof
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_dof_edit_form extends block_edit_form
{
    /**
     * Дополнительные настройки экземпляра блока
     * 
     * @param $mform - Экземпляр менеджера формы
     * 
     * @return void
     */
    protected function specific_definition( $mform )
    {
        // Заголовок дополнительных настроек для блока
        $mform->addElement('header', 'config_header', get_string('config_header', 'block_dof'));
        
        //элементы выпадающего меню с выбором режима отображения (блок или секция)
        $modeoptions = [
            'block' => get_string('config_translation_mode_block', 'block_dof'),
            'section' => get_string('config_translation_mode_section', 'block_dof')
        ];
        //режим отображения (блок или секция)
        $mform->addElement('select', 'config_translation_mode', 
            get_string('config_translation_mode', 'block_dof'), $modeoptions, 'block');
        $mform->setType('config_translation_mode', PARAM_ALPHA);
        
        //выбор интерфейса
        $mform->addElement('select', 'config_translation_im', 
            get_string('config_translation_im', 'block_dof'), $this->get_im_options(), 'standard');
        $mform->setType('config_translation_im', PARAM_ALPHA);
        
        //название блока или секции для отображения
        $mform->addElement('text', 'config_translation_name', 
            get_string('config_translation_name', 'block_dof'));
        $mform->setType('config_translation_name', PARAM_ALPHAEXT);
        
        //элементы выпадающего меню с выбором типа передаваемого идентификатора
        $translationidmodeoptions = [
            'manual' => get_string('config_translation_id_mode_manual', 'block_dof'),
            'userid' => get_string('config_translation_id_mode_userid', 'block_dof'),
            'courseid' => get_string('config_translation_id_mode_courseid', 'block_dof'),
            'personid' => get_string('config_translation_id_mode_personid', 'block_dof')
        ];
        //тип передаваемого идентификатора (указанный вручную, id пользователя moodle, id курса, id персоны деканата)
        $mform->addElement('select', 'config_translation_id_mode', 
            get_string('config_translation_id_mode', 'block_dof'), $translationidmodeoptions, 
            'dof_person_id');
        $mform->setType('config_translation_id_mode', PARAM_ALPHA);
        
        //идентификатор, если выбран тип указанный вручную
        $mform->addElement('text', 'config_translation_id', 
            get_string('config_translation_id', 'block_dof'));
        $mform->setType('config_translation_id', PARAM_INT);
    }

    /**
     * Получение массива интерфейсов деканата
     *
     * @return array - массив с интерфейсами деканата для формирования выпадающего меню
     */
    private function get_im_options()
    {
        require_once (dirname(realpath(__FILE__)) . '/locallib.php');
        
        global $DOF;
        
        // Получение списка зарегистрированных интерфейсов
        $ims = $DOF->plugin_list('im');
        
        // Список доступных интерфейсов Деканата
        $interfaces = [];
        foreach ( $ims as $im )
        {
            if ( $DOF->plugin_files_exists('im', $im['code']) )
            {// Зарегистрированный интерфейс доступен
                $interfaces[$im['code']] = $DOF->get_string('title', $im['code']);
            }
        }
        return $interfaces;
    }
}