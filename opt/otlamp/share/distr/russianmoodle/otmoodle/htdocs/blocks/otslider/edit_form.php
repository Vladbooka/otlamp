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
 * Слайдер изображений. Библиотека функций блока.
 *
 * @package    block
 * @subpackage otslider
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_otslider\slider;

class block_otslider_edit_form extends block_edit_form
{
    /**
     * Объявление дополнительных полей конфигурации экземпляра блока
     *
     * @return void
     */
    protected function specific_definition($mform)
    {
        global $PAGE, $CFG;
        
        // Остновные настройки блока
        $mform->addElement(
            'header',
            'config_header',
            get_string('config_header_main_label', 'block_otslider')
        );
        
        // Инициализация слайдера
        $slider = new slider($this->block);
        
        if ( ! $slider->count_slides() )
        {// Не добавлено ни одного слайда
            $mform->addElement(
                'html',
                html_writer::div(
                    get_string('config_slidemanagerlink_emptyslides', 'block_otslider'),
                    'alert alert-info block_otslider_notice'
                )
            );
        }
        
        // Ссылка на панель управления слайдами
        $slidemanageurl = new moodle_url(
            '/blocks/otslider/slidemanager.php',
            [
                'blockid' => $this->block->instance->id,
                'backurl' => $PAGE->url->out(true)
            ]
        );
        $mform->addElement(
            'html',
            html_writer::link(
                $slidemanageurl,
                get_string('config_slidemanagerlink_label', 'block_otslider'),
                ['class' => 'btn btn-primary slidemanager']
            )
        );
        
        // Название слайдера
        $mform->addElement('text','config_slidername', get_string('config_slidername', 'block_otslider'));
        $mform->setType('config_slidername', PARAM_ALPHAEXT);
        $mform->addHelpButton('config_slidername', 'config_slidername', 'block_otslider');
        $mform->setAdvanced('config_slidername');

        // Высота слайдера в процентах от ширины
        $mform->addElement('text','config_height', get_string('config_height', 'block_otslider'));
        $mform->setDefault('config_height', 20);
        $mform->setType('config_height', PARAM_INT);
        $mform->addHelpButton('config_height', 'config_height', 'block_otslider');
        
        // Пропорциональное уменьшение высоты слайдера при уменьшении размера экрана
        $mform->addElement('advcheckbox','config_proportionalheight', get_string('config_proportionalheight', 'block_otslider'));
        $mform->setDefault('config_proportionalheight', true);
        $mform->setType('config_proportionalheight', PARAM_BOOL);
        $mform->addHelpButton('config_proportionalheight', 'config_proportionalheight', 'block_otslider');
        $mform->disabledIf('config_proportionalheight', 'config_height', 'eq', '0');
        $mform->disabledIf('config_proportionalheight', 'config_height', 'eq', '');
        
        // Типы анимации
        $slidetypes = [
            'simple' => get_string('slidetype_simple', 'block_otslider'),
            'fadein' => get_string('slidetype_fadein', 'block_otslider'),
            'slide' => get_string('slidetype_slide', 'block_otslider'),
            'slide-overlay' => get_string('slidetype_slideoverlay', 'block_otslider'),
            'triple' => get_string('slidetype_triple', 'block_otslider')
        ];
        $mform->addElement('select','config_slidetype', get_string('config_slidetype', 'block_otslider'), $slidetypes);
        $mform->setDefault('config_slidetype', 'fade');
        $mform->setType('config_slidetype', PARAM_TEXT);

        // Переключать слайды по скроллу
        $mform->addElement('advcheckbox','config_slidescroll', get_string('config_slidescroll', 'block_otslider'));
        $mform->setDefault('config_slidescroll', false);
        $mform->setType('config_slidescroll', PARAM_BOOL);
        $mform->addHelpButton('config_slidescroll', 'config_slidescroll', 'block_otslider');
        
        // Интервал переключения слайда в секундах
        $mform->addElement('text','config_slidespeed', get_string('config_slidespeed', 'block_otslider'));
        $mform->setDefault('config_slidespeed', 3);
        $mform->setType('config_slidespeed', PARAM_FLOAT);
        $mform->disabledIf('config_slidespeed', 'config_slidescroll', 'checked');
        
        // Отображение стрелок
        $mform->addElement('advcheckbox','config_navigation', get_string('config_navigation', 'block_otslider'));
        $mform->setDefault('config_navigation', false);
        $mform->setType('config_navigation', PARAM_BOOL);
        $mform->disabledIf('config_navigation', 'config_slidescroll', 'checked');
        
        // Типы стрелок
        $arrowtypes = [
            'thick' => get_string('arrowtype_thick', 'block_otslider'),
            'thin' => get_string('arrowtype_thin', 'block_otslider')
        ];
        $mform->addElement('select','config_arrowtype', get_string('config_arrowtype', 'block_otslider'), $arrowtypes);
        $mform->setDefault('config_arrowtype', 'thick');
        $mform->setType('config_arrowtype', PARAM_TEXT);

        // Отображение точек
        $mform->addElement('advcheckbox','config_navigationpoints', get_string('config_navigationpoints', 'block_otslider'));
        $mform->setDefault('config_navigationpoints', false);
        $mform->setType('config_navigationpoints', PARAM_BOOL);
        $mform->disabledIf('config_navigationpoints', 'config_slidescroll', 'checked');
        
        if( file_exists($CFG->dirroot . '/theme/opentechnology/classes/profilemanager.php') )
        {
            // Получение менеджера профилей
            $manager = \theme_opentechnology\profilemanager::instance();
            $profiles = $manager->get_profiles();
            foreach($profiles as $profilecode => $profileinstance)
            {
                $profiles[$profilecode] = $profileinstance->get_name();
            }
            $profiles['_all'] = get_string('themeprofile_all', 'block_otslider');
            
            // Отображение блока только при наличии класса в body
            $mform->addElement('select','config_themeprofile', get_string('config_themeprofile', 'block_otslider'), $profiles);
            $mform->setDefault('config_themeprofile', '_all');
            $mform->setType('config_themeprofile', PARAM_TEXT);
            $mform->setAdvanced('config_themeprofile');
        }
        
        $mform->addElement('advcheckbox','config_blockreplace', get_string('config_blockreplace', 'block_otslider'));
        $mform->setDefault('config_blockreplace', false);
        $mform->setType('config_blockreplace', PARAM_BOOL);
        $mform->setAdvanced('config_blockreplace');
    }
}