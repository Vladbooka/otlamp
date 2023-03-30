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
 * Блок "Поделиться ссылкой". Языковые строки
 *
 * @package    block_otshare
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_otshare_edit_form extends block_edit_form
{

    /**
     * Adds configuration options for otshare block
     *
     * @param object $mform
     * @throws coding_exception
     */
    protected function specific_definition($mform)
    {
        GLOBAL $PAGE;
        
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        //ВКонтакте
        $mform->addElement('advcheckbox', 'config_vk', get_string('vk', 'block_otshare'));
        $mform->setDefault('config_vk', false);
        $mform->setType('config_vk', PARAM_BOOL);
        
        // Facebook
        $mform->addElement('advcheckbox', 'config_fb', get_string('fb', 'block_otshare'));
        $mform->setDefault('config_fb', false);
        $mform->setType('config_fb', PARAM_BOOL);
        
        //Twitter
        $mform->addElement('advcheckbox', 'config_tw', get_string('tw', 'block_otshare'));
        $mform->setDefault('config_tw', false);
        $mform->setType('config_tw', PARAM_BOOL);
        
        //Одноклассники
        $mform->addElement('advcheckbox', 'config_ok', get_string('ok', 'block_otshare'));
        $mform->setDefault('config_ok', false);
        $mform->setType('config_ok', PARAM_BOOL);
        
        //Google+
        $mform->addElement('advcheckbox', 'config_gp', get_string('gp', 'block_otshare'));
        $mform->setDefault('config_gp', false);
        $mform->setType('config_gp', PARAM_BOOL);
        
        
        //Пояснение под ссылками
        $mform->addElement('text', 'config_explain', get_string('explain', 'block_otshare'));
        $mform->setDefault('config_explain', get_string('explain_default', 'block_otshare'));
        $mform->setType('config_explain', PARAM_TEXT);
        
        //Использовть аутентичные кнопки
        $mform->addElement('advcheckbox', 'config_authentic', get_string('authentic', 'block_otshare'));
        $mform->setDefault('config_authentic', false);
        $mform->setType('config_authentic', PARAM_BOOL);
        
        // Добавить изображение в метатег для шаринга
        $mform->addElement('advcheckbox', 'config_block_image', get_string('use_custom_metatag_image', 'block_otshare'));
        $mform->setDefault('config_block_image', false);
        $mform->setType('config_block_image', PARAM_BOOL);
        $mform->addHelpButton('config_block_image', 'info_config_block_image', 'block_otshare');
        
        $yesno = [
            0 => get_string('no'),
            1 => get_string('yes')
        ];
        $mform->addElement('select', 'config_altersharing', get_string('altersharing', 'block_otshare'), $yesno);
        $mform->setDefault('config_altersharing', 0);
        $mform->setType('config_altersharing', PARAM_INT);
        $mform->addHelpButton('config_altersharing', 'altersharing', 'block_otshare');
        
        $mform->addElement('text', 'config_altercoursename', get_string('altercoursename', 'block_otshare'));
        $mform->setDefault('config_altercoursename', '');
        $mform->setType('config_altercoursename', PARAM_TEXT);
        $mform->addHelpButton('config_altercoursename', 'altercoursename', 'block_otshare');
        $mform->disabledIf('config_altercoursename', 'config_altersharing', 0);
        
        $mform->addElement('text', 'config_altersitename', get_string('altersitename', 'block_otshare'));
        $mform->setDefault('config_altersitename', '');
        $mform->setType('config_altersitename', PARAM_TEXT);
        $mform->addHelpButton('config_altersitename', 'altersitename', 'block_otshare');
        $mform->disabledIf('config_altersitename', 'config_altersharing', 0);

        // Ссылка на панель управления изображением для шжринга
        $imgmanageurl = new moodle_url(
                '/blocks/otshare/imgmanager/imgmanager.php',
                [
                    'courseid' => $PAGE->course->id,
                    'blockid' => $this->block->instance->id,
                    'backurl' => $PAGE->url->out(true)
                ]
                );
        $mform->addElement(
                'html',
                html_writer::link(
                        $imgmanageurl,
                        get_string('config_imgmanagerlink_label', 'block_otshare'),
                        ['class' => 'block_otshare btn btn-primary imgmanager']
                        )
                );
    }
}