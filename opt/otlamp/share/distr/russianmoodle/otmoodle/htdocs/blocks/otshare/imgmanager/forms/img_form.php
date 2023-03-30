<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Менеджер изображений. Форма загрузки изображения
 *
 * @package    block
 * @subpackage otshare
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once ($CFG->libdir . '/formslib.php');

class img_form extends moodleform
{
    protected $course;
    protected $course_context;
    
    public function definition_after_data()
    {
        $mform =& $this->_form;
        
        $file = $this->get_image();
        $logo = $this->get_logo();

        if ( ! empty($file) )
        {
            // Поиск пустой пользовательской драфтзоны для подключения к файлпикеру
            $draftitemid = file_get_submitted_draft_itemid('image');
            // Загрузка в пользовательскую зону изображения слайдера
            file_prepare_draft_area(
                    $draftitemid,
                    context_system::instance()->id,
                    'block_otshare',
                    'public',
                    $this->course->id
                    );
            // Привязка файлпикера к пользовательской драфтзоне
            $mform->setDefault('image', $draftitemid);
        }
        
        if ( ! empty($logo) )
        {
            // Поиск пустой пользовательской драфтзоны для подключения к файлпикеру
            $draftitemid = file_get_submitted_draft_itemid('logo');
            // Загрузка в пользовательскую зону изображения слайдера
            file_prepare_draft_area(
                $draftitemid,
                context_system::instance()->id,
                'block_otshare',
                'logo',
                $this->course->id
                );
            // Привязка файлпикера к пользовательской драфтзоне
            $mform->setDefault('logo', $draftitemid);
        }
    }

    public function process()
    {
        if ( $this->is_submitted() AND confirm_sesskey() AND
                $this->is_validated() AND $formdata = $this->get_data()
                )
        {
            // Сохранение изображения
            file_save_draft_area_files(
                $formdata->image, 
                context_system::instance()->id, 
                'block_otshare', 
                'public', 
                $this->course->id,
                ['maxfiles' => 1, 'accepted_types' => ['image']]
            );
            
            // Сохранение изображения
            file_save_draft_area_files(
                $formdata->logo,
                context_system::instance()->id,
                'block_otshare',
                'logo',
                $this->course->id,
                ['maxfiles' => 1, 'accepted_types' => ['image']]
            );
        }
    }
    
    protected function get_image()
    {
        // Получим первое попавшееся изображение из описания курса
        // Подключение менеджера файлов
        $fs = get_file_storage();
        
        // Получение файлов описания курса
        $files = $fs->get_area_files(
                context_system::instance()->id,
                'block_otshare',
                'public',
                $this->course->id
                );

        if ( ! empty($files) )
        {
            return array_pop($files);
        } else
        {
            return false;
        }
    }
    
    protected function get_logo()
    {
        // Получим первое попавшееся изображение из описания курса
        // Подключение менеджера файлов
        $fs = get_file_storage();
    
        // Получение файлов описания курса
        $files = $fs->get_area_files(
            context_system::instance()->id,
            'block_otshare',
            'logo',
            $this->course->id
            );
    
        if ( ! empty($files) )
        {
            return array_pop($files);
        } else
        {
            return false;
        }
    }

    protected function definition ()
    {
        $mform =& $this->_form;

        if ( isset($this->_customdata->courseid) && ! empty($this->_customdata->courseid) )
        {
            $this->course = get_course($this->_customdata->courseid);
            $this->course_context = context_course::instance($this->course->id);
        } else
        {
            throw new moodle_exception('missing_courseid');
        }
        
        $header = [$mform->createElement('header', 'header', get_string('img_form_header', 'block_otshare'))];
        $mform->addGroup($header, 'main_header', '', ' ');
        
        // Изображение
        $mform->addElement(
                'filemanager',
                'image',
                get_string('image_formsave_image_label', 'block_otshare'),
                null,
                ['maxfiles' => 1, 'accepted_types' => ['image']]
                );
        
        // Логотип для альтернативного шаринга
        $mform->addElement(
                'filemanager',
                'logo',
                get_string('logo_formsave_logo_label', 'block_otshare'),
                null,
                ['maxfiles' => 1, 'accepted_types' => ['image']]
                );
        
        $url = new moodle_url('/course/view.php', ['id' => $this->course->id]);
        $buttons = [];
        $buttons[] = $mform->createElement('submit', 'submit', get_string('submit'));
        $buttons[] = $mform->createElement('static', 'backtocourse', '', html_writer::link($url, get_string('back_to_course', 'block_otshare'), ['class' => 'btn']));
        
        $mform->addGroup($buttons);
    }

}