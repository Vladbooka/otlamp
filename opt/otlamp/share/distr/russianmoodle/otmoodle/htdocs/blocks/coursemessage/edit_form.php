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
 * Блок мессенджера курса. Настройки экземпляра блока.
 *
 * @package    block_coursemessage
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_coursemessage_edit_form extends block_edit_form 
{
    protected function specific_definition($mform) 
    {
        global $CFG;
        require_once($CFG->dirroot.'/user/profile/lib.php');
        
        $mform->addElement(
                'header', 
                'config_header', 
                get_string('config_header', 'block_coursemessage')
        );

        $mform->addElement(
                'text', 
                'config_userfields',
                get_string('config_userfields', 'block_coursemessage')
        );
        $mform->setDefault('config_userfields', 'middlename');
        $mform->setType('config_userfields', PARAM_TEXT);
        // Описание настройки идентификаторов для отображения
        $mform->addHelpButton('config_userfields', 'config_userfields_desc', 'block_coursemessage');
        // выбор метода определения получателей сообщения
        $select = $mform->createElement(
            'select',
            'config_recipientselectionmode',
            get_string('config_recipientselectionmode', 'block_coursemessage')
            );
        $select->addOption(
            get_string('config_useglobal', 'block_coursemessage'),
            'useglobal'
            );
        $select->addOption(
            get_string('config_sendtoall', 'block_coursemessage'),
            'sendtoall'
            );
        $select->addOption(
            get_string('config_allowuserselect', 'block_coursemessage'),
            'allowuserselect'
            );
        $select->addOption(
            get_string('config_automaticcontact', 'block_coursemessage'),
            'automaticcontact'
            );
   
        $mform->addElement($select);
        $mform->setDefault('config_recipientselectionmode', 'useglobal');
        // Описание методов определения получателей сообщения
        $mform->addHelpButton('config_recipientselectionmode', 'config_recipientselectionmode_desc', 'block_coursemessage');
        // Дополнять сообщение учащегося сведениями о курсе и группе
        $mform->addElement(
            'advcheckbox',
            'config_senduserinfo',
            get_string('config_senduserinfo', 'block_coursemessage')
            );
        $mform->setDefault('config_senduserinfo', false);
    }
}