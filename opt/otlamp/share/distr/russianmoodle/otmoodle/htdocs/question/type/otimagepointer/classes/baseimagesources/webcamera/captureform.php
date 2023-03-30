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
 * Тип вопроса Объекты на изображении. Форма сохранения изображения.
 *
 * @package    qtype
 * @subpackage otimagepointer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_otimagepointer\baseimagesources\webcamera;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir.'/formslib.php');

use moodleform;

class captureform extends moodleform 
{   
    /**
     * Обьявление полей формы
     */
    protected function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Поле для сохранения base64 фотографии
        $mform->addElement(
            'textarea',
            'imgs_wbc_capture',
            '',
            ['id' => 'imgs_wbc_capture']
        );
    }
    
    /**
     * Обработка данных формы
     *
     * @return bool
     */
    public function process()
    {
        global $PAGE;
        
        if ( $this->is_submitted() && confirm_sesskey() &&
             $this->is_validated() && $formdata = $this->get_data()
           )
        {// Сохранение данных
            
            if ( ! empty($formdata->imgs_wbc_capture) )
            {// Требуется сохранить пользовательское изображение
                
                // Нормализация данных
                list(, $data) = explode(',', $formdata->imgs_wbc_capture);
                $filecontent = base64_decode($data);

                // Сохранение базового изображения
                $baseimage = webcamera::question_set_image($this->_customdata->qa, $filecontent);
                if ( $baseimage )
                {// Изображение сохранено
                    
                    // Постпроцесс
                    $PAGE->requires->yui_module(
                        'moodle-qtype_otimagepointer-imagesource_webcamera-capture',
                        'Y.Moodle.qtype_otimagepointer.imagesource_webcamera.capture.savepostprocess',
                        [$this->_customdata->qa->get_database_id(), $baseimage->get_contenthash(), $baseimage->get_pathnamehash()]
                    );
                }
            }
        }
    }
}