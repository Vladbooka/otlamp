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
 * Блок топ-10
 *
 * @package    block
 * @subpackage topten
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use block_topten\report as report;

class block_topten_edit_form extends block_edit_form
{
    /**
     * Тип отчета
     *
     * @var string
     */
    protected $type = '';
    
    /**
     * {@inheritDoc}
     * @see block_edit_form::specific_definition()
     */
    protected function specific_definition($mform)
    {
        global $PAGE;
        $PAGE->requires->js_call_amd('block_topten/mod_form', 'init');

        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('select', 'config_rating_type', get_string('rating_type', 'block_topten'), report::get_rating_types());
        $mform->setType('config_rating_type', PARAM_RAW_TRIMMED);
        
        // Показывать ли заголовок
        $mform->addElement('advcheckbox', 'config_hide_rating_title', get_string('hide_rating_title', 'block_topten'));
        $mform->setDefault('config_hide_rating_title', false);
        
        // время жизни
        $mform->addElement('duration', 'config_timelimit', get_string('slide_object_timelimit', 'block_topten'));
        $mform->setDefault('config_timelimit', 86400);
        
        // Название рейтинга. Если не определить, будет использоваться стандартное
        $mform->addElement('text', 'config_rating_name', get_string('rating_name', 'block_topten'));
        $mform->setType('config_rating_name', PARAM_RAW);
        $mform->disabledIf('config_rating_name', 'config_hide_rating_title', 'checked');

        $mform->addElement('text', 'config_rating_number', get_string('rating_number', 'block_topten'));
        $mform->setDefault('config_rating_number', 10);
        $mform->addRule('config_rating_number', null, 'required', null, 'client');
        $mform->addRule('config_rating_number', null, 'numeric', null, 'client');
        $mform->addRule('config_rating_number', null, 'nonzero', null, 'client');
        $mform->setType('config_rating_number', PARAM_INT);
        
        // Добавление дополнительного класса форме для JS манипуляций
        $mform->updateAttributes(['class' => "{$mform->getAttribute('class')} block_topten-js"]);
        
        // Тип отчета
        $type = optional_param('type', '', PARAM_RAW_TRIMMED);
        
        $ratingobj = null;
        if ( ! empty($this->block->config->rating_type) )
        {
            $ratingobj = report::get_rating_object($this->block->config, $this->block->instance->id);
        }
        if ( ! empty($type) )
        {
            $fakeconfig = new stdClass();
            $fakeconfig->rating_type = $type;
            $ratingobj = report::get_rating_object($fakeconfig, 0);
            $mform->addElement('hidden', 'type', $type);
            $mform->setType('type', PARAM_RAW_TRIMMED);
            $mform->setDefault('config_rating_type', $type);
            if ( property_exists($this->block, 'config') &&
                    is_object($this->block->config) &&
                    property_exists($this->block->config, 'rating_type') )
            {
                $this->block->config->rating_type = $type;
            }
        }
        if ( ! empty($ratingobj) )
        {
            $ratingobj->definition($mform, $this);
        }
    }
}
