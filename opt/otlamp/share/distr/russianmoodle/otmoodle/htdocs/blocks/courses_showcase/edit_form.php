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
 * Блок Витрина курсов
 *
 * @package    block
 * @subpackage courses_showcase
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


class block_courses_showcase_edit_form extends block_edit_form
{
    /**
     * {@inheritDoc}
     * @see block_edit_form::specific_definition()
     */
    protected function specific_definition($mform)
    {
        global $PAGE;
        
        $PAGE->requires->js_call_amd('block_courses_showcase/view_type_chooser', 'init', []);
        // Добавление дополнительного класса форме для JS манипуляций
        $mform->updateAttributes(['class' => "{$mform->getAttribute('class')} block-courses-showcase-js"]);
        
        
        // Заголовок настроек
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));
        
        // Заголовок блока. Если не определить, не будет отображаться.
        $mform->addElement('text', 'config_title', get_string('block_title', 'block_courses_showcase'));
        $mform->setType('config_title', PARAM_RAW);
        
        // Тип отображения
        $mform->addElement(
            'select',
            'config_view_type',
            get_string('view_type', 'block_courses_showcase'),
            [
                'crw_default' => get_string('view_type__crw_default', 'block_courses_showcase'),
                'courses_list' => get_string('view_type__courses_list', 'block_courses_showcase'),
                'categories_list' => get_string('view_type__categories_list', 'block_courses_showcase'),
            ]
        );
        $mform->setType('config_view_type', PARAM_RAW_TRIMMED);
        
        
        // Тип отображения, полученный в результате перезагрузки страницы
        $viewtype = 'crw_default';
        
        if (!empty($this->block->config->view_type))
        {
            $viewtype = $this->block->config->view_type;
        }

        $viewtype = optional_param('viewtype', $viewtype, PARAM_RAW_TRIMMED);
        
        $mform->setDefault('config_view_type', $viewtype);
        if ( property_exists($this->block, 'config') &&
            is_object($this->block->config) &&
            property_exists($this->block->config, 'view_type') )
        {
            $this->block->config->view_type = $viewtype;
        }
        
        $mform->addElement('hidden', 'viewtype', $viewtype);
        $mform->setType('viewtype', PARAM_RAW_TRIMMED);
        
        
        
        
        
        $blocktitle = '';
        
        if (!empty($this->block->config->title))
        {
            $blocktitle = $this->block->config->title;
        }
        $blocktitle = optional_param('title', $blocktitle, PARAM_RAW_TRIMMED);
        $mform->setDefault('config_title', $viewtype);
        if (property_exists($this->block, 'config') &&
            is_object($this->block->config) && property_exists($this->block->config, 'title'))
        {
            $this->block->config->title = $blocktitle;
        }
        $mform->addElement('hidden', 'title', $blocktitle);
        $mform->setType('title', PARAM_RAW_TRIMMED);
        
        $vto = $this->block->get_viewtype_object($viewtype);
        $vto->extend_edit_form($mform);
    }
}
