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
 * Слайдер изображений. Класс слайда с изображением.
 *
 * @package    block
 * @subpackage otslider
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_otslider\slides\types;

use MoodleQuickForm;
use stdClass;
use dml_exception;
use html_writer;
use moodle_url;
use context_system;
use block_otslider\slides\base as slidebase;
use block_otslider\slides\formsave as formsave;
use block_otslider\exception\slide as exception_slide;

class image extends slidebase
{
    /**
     * Получить код типа слайда
     *
     * @return string
     */
    public static function get_code()
    {
        return 'image';
    }
    
    /**
     * Получить локализованное название типа слайда
     *
     * @return string
     */
    public static function get_name()
    {
        return get_string('slide_image_name', 'block_otslider');
    }
    
    /**
     * Получить локализованное описание типа слайда
     *
     * @return string
     */
    public static function get_description()
    {
        return get_string('slide_image_descripton', 'block_otslider');
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \block_otslider\slides\base::get_slide_options()
     */
    public function get_slide_options () {
        $slideoptions = new \stdClass();
        $slideoptions->zoomview = $this->get_slide_zoomview();
        $slideoptions->title = $this->get_slide_title();
        $slideoptions->description = $this->get_slide_description();
        $slideoptions->captionalign = $this->get_slide_captionalign();
        $slideoptions->summary = $this->get_slide_summary();
        $slideoptions->captiontop = $this->get_slide_captiontop();
        $slideoptions->captionright = $this->get_slide_captionright();
        $slideoptions->captionbottom = $this->get_slide_captionbottom();
        $slideoptions->captionleft = $this->get_slide_captionleft();
        $slideoptions->parallax = $this->get_slide_parallax();
        $slideoptions->backgroundpositiontop = $this->get_slide_backgroundpositiontop();
			$image = new \stdClass();
			$image->contextid = context_system::instance()->id;
            $image->component = 'block_otslider';
            $image->filearea = 'public';
            $image->itemid = $this->record->id;
		$slideoptions->image = $image;
        return $slideoptions;
    }
    
    /**
     * Получение настройки отображения увеличенного изображения
     *
     * @return int
     */
    public function get_slide_zoomview()
    {
        global $DB;
        
        // 
        $zoomview = $DB->get_field(
            'block_otslider_slide_options',
            'shortdata',
            ['slideid' => $this->record->id, 'name' => 'zoomview']
            );
        return (int)$zoomview;
    }
    
    /**
     * Получение позиции изображения по вертикали в %
     *
     * @return int
     */
    public function get_slide_backgroundpositiontop()
    {
        global $DB;
        
        // Заголовок
        $backgroundpositiontop = $DB->get_field(
            'block_otslider_slide_options',
            'shortdata',
            ['slideid' => $this->record->id, 'name' => 'backgroundpositiontop']
            );
        
        if ( $backgroundpositiontop !== false && ! is_null($backgroundpositiontop) )
        {
            return (int)$backgroundpositiontop;
        }
        
        return 50;
    }
    
    /**
     * Получение отступа текстовой области сверху
     *
     * @return int
     */
    public function get_slide_captiontop()
    {
        global $DB;
        
        // Заголовок
        $captiontop = $DB->get_field(
            'block_otslider_slide_options',
            'shortdata',
            ['slideid' => $this->record->id, 'name' => 'captiontop']
            );
        
        if ( $captiontop !== false && ! is_null($captiontop) )
        {
            return (int)$captiontop;
        }
        
        return 2;
    }
    
    /**
     * Получение отступа текстовой области справа
     *
     * @return int
     */
    public function get_slide_captionright()
    {
        global $DB;
        
        // Заголовок
        $captionright = $DB->get_field(
            'block_otslider_slide_options',
            'shortdata',
            ['slideid' => $this->record->id, 'name' => 'captionright']
            );
        
        if ( $captionright !== false && ! is_null($captionright) )
        {
            return (int)$captionright;
        }
        
        return 20;
    }
    
    /**
     * Получение отступа текстовой области снизу
     *
     * @return int
     */
    public function get_slide_captionbottom()
    {
        global $DB;
        
        // Заголовок
        $captionbottom = $DB->get_field(
            'block_otslider_slide_options',
            'shortdata',
            ['slideid' => $this->record->id, 'name' => 'captionbottom']
        );
        
        if ( $captionbottom !== false && ! is_null($captionbottom) )
        {
            return (int)$captionbottom;
        }
        
        return 2;
    }
    
    /**
     * Получение отступа текстовой области слева
     *
     * @return int
     */
    public function get_slide_captionleft()
    {
        global $DB;
        
        // Заголовок
        $captionleft = $DB->get_field(
            'block_otslider_slide_options',
            'shortdata',
            ['slideid' => $this->record->id, 'name' => 'captionleft']
            );
        
        if ( $captionleft !== false && ! is_null($captionleft) )
        {
            return (int)$captionleft;
        }
        
        return 8;
    }
    
    /**
     * Получение выравнивания текстовой области
     *
     * @return int
     */
    public function get_slide_captionalign()
    {
        global $DB;
        
        // Заголовок
        $captionalign = $DB->get_field(
            'block_otslider_slide_options',
            'shortdata',
            ['slideid' => $this->record->id, 'name' => 'captionalign']
        );
        
        if ( $captionalign !== false && ! is_null($captionalign) )
        {
            return $captionalign;
        }
        
        return 'left';
    }
    
    /**
     * Сохранение настройки отображения увеличенного изображения
     *
     * @return int
     */
    public function set_slide_zoomview($zoomview)
    {
        global $DB;
        
        if( is_null($zoomview) )
        {
            $zoomview = 0;
        }
        
        $data = new stdClass();
        $data->shortdata = (int)$zoomview;
        if ( $record = $DB->get_record('block_otslider_slide_options', ['slideid' => $this->record->id, 'name' => 'zoomview']) )
        {// Обновление заголовка
            $data->id = $record->id;
            $DB->update_record('block_otslider_slide_options', $data);
        } else
        {// Добавление заголовка
            $data->slideid = $this->record->id;
            $data->name = 'zoomview';
            $DB->insert_record('block_otslider_slide_options', $data);
        }
    }
    
    
    
    /**
     * Сохранение позиции изображения по вертикали в %
     *
     * @param string $backgroundpositiontop - позиция изображения по вертикали в %
     *
     * @return void
     */
    public function set_slide_backgroundpositiontop($backgroundpositiontop)
    {
        global $DB;
        
        if( is_null($backgroundpositiontop) )
        {
            $backgroundpositiontop = 50;
        }
        $backgroundpositiontop = (int)$backgroundpositiontop > 100 ? 100 : (int)$backgroundpositiontop;
        $backgroundpositiontop = (int)$backgroundpositiontop < 0 ? 0 : (int)$backgroundpositiontop;
        
        $data = new stdClass();
        $data->shortdata = (string)$backgroundpositiontop;
        if ( $record = $DB->get_record('block_otslider_slide_options', ['slideid' => $this->record->id, 'name' => 'backgroundpositiontop']) )
        {// Обновление заголовка
            $data->id = $record->id;
            $DB->update_record('block_otslider_slide_options', $data);
        } else
        {// Добавление заголовка
            $data->slideid = $this->record->id;
            $data->name = 'backgroundpositiontop';
            $DB->insert_record('block_otslider_slide_options', $data);
        }
    }
    
    
    /**
     * Сохранение отступа текстовой области сверху
     *
     * @param string $captiontop - отступ текстовой области сверху
     *
     * @return void
     */
    public function set_slide_captiontop($captiontop)
    {
        global $DB;
        
        if( is_null($captiontop) )
        {
            $captiontop = 2;
        }
        
        $data = new stdClass();
        $data->shortdata = (string)$captiontop;
        if ( $record = $DB->get_record('block_otslider_slide_options', ['slideid' => $this->record->id, 'name' => 'captiontop']) )
        {// Обновление заголовка
            $data->id = $record->id;
            $DB->update_record('block_otslider_slide_options', $data);
        } else
        {// Добавление заголовка
            $data->slideid = $this->record->id;
            $data->name = 'captiontop';
            $DB->insert_record('block_otslider_slide_options', $data);
        }
    }
    
    
    /**
     * Сохранение отступа текстовой области справа
     *
     * @param string $captionright - отступ текстовой области справа
     *
     * @return void
     */
    public function set_slide_captionright($captionright)
    {
        global $DB;
        
        if( is_null($captionright) )
        {
            $captionright = 20;
        }
        
        $data = new stdClass();
        $data->shortdata = (string)$captionright;
        if ( $record = $DB->get_record('block_otslider_slide_options', ['slideid' => $this->record->id, 'name' => 'captionright']) )
        {// Обновление заголовка
            $data->id = $record->id;
            $DB->update_record('block_otslider_slide_options', $data);
        } else
        {// Добавление заголовка
            $data->slideid = $this->record->id;
            $data->name = 'captionright';
            $DB->insert_record('block_otslider_slide_options', $data);
        }
    }
    
    
    /**
     * Сохранение отступа текстовой области снизу
     *
     * @param string $captionbottom - отступ текстовой области снизу
     *
     * @return void
     */
    public function set_slide_captionbottom($captionbottom)
    {
        global $DB;
        
        if( is_null($captionbottom) )
        {
            $captionbottom = 2;
        }
        
        $data = new stdClass();
        $data->shortdata = (string)$captionbottom;
        if ( $record = $DB->get_record('block_otslider_slide_options', ['slideid' => $this->record->id, 'name' => 'captionbottom']) )
        {// Обновление заголовка
            $data->id = $record->id;
            $DB->update_record('block_otslider_slide_options', $data);
        } else
        {// Добавление заголовка
            $data->slideid = $this->record->id;
            $data->name = 'captionbottom';
            $DB->insert_record('block_otslider_slide_options', $data);
        }
    }
    
    
    /**
     * Сохранение отступа текстовой области слева
     *
     * @param string $captionleft - отступ текстовой области слева
     *
     * @return void
     */
    public function set_slide_captionleft($captionleft)
    {
        global $DB;
        
        if( is_null($captionleft) )
        {
            $captionleft = 8;
        }
        
        $data = new stdClass();
        $data->shortdata = (string)$captionleft;
        if ( $record = $DB->get_record('block_otslider_slide_options', ['slideid' => $this->record->id, 'name' => 'captionleft']) )
        {// Обновление заголовка
            $data->id = $record->id;
            $DB->update_record('block_otslider_slide_options', $data);
        } else
        {// Добавление заголовка
            $data->slideid = $this->record->id;
            $data->name = 'captionleft';
            $DB->insert_record('block_otslider_slide_options', $data);
        }
    }
    
    
    /**
     * Сохранение выравнивания текстовой области
     *
     * @param string $captionalign - выравнивание текстовой области
     *
     * @return void
     */
    public function set_slide_captionalign($captionalign)
    {
        global $DB;
        
        if( is_null($captionalign) )
        {
            $captionalign = 'left';
        }
        
        $data = new stdClass();
        $data->shortdata = (string)$captionalign;
        if ( $record = $DB->get_record('block_otslider_slide_options', ['slideid' => $this->record->id, 'name' => 'captionalign']) )
        {// Обновление заголовка
            $data->id = $record->id;
            $DB->update_record('block_otslider_slide_options', $data);
        } else
        {// Добавление заголовка
            $data->slideid = $this->record->id;
            $data->name = 'captionalign';
            $DB->insert_record('block_otslider_slide_options', $data);
        }
    }
    
    
    
    /**
     * Получение коэффициента смещения изображения при скролле (параллакс-эффект)
     *
     * @return string
     */
    public function get_slide_parallax()
    {
        global $DB;
        
        // Заголовок
        $parallax = $DB->get_field(
            'block_otslider_slide_options',
            'shortdata',
            ['slideid' => $this->record->id, 'name' => 'parallax']
        );
        
        if ( $parallax !== false && ! is_null($parallax) )
        {
            return (int)$parallax;
        }
        
        return 0;
    }
    
    /**
     * Сохранение коэффициента смещения изображения при скролле (параллакс-эффект)
     *
     * @param string $parallax - коэффициент смещения изображения при скролле (параллакс-эффект)
     *
     * @return void
     */
    public function set_slide_parallax($parallax)
    {
        global $DB;

        if( is_null($parallax) )
        {
            $parallax = 0;
        }
        $parallax = (int)$parallax > 100 ? 100 : (int)$parallax;
        $parallax = (int)$parallax < -100 ? -100 : (int)$parallax;
        
        $data = new stdClass();
        $data->shortdata = (string)$parallax;
        if ( $record = $DB->get_record('block_otslider_slide_options', ['slideid' => $this->record->id, 'name' => 'parallax']) )
        {// Обновление заголовка
            $data->id = $record->id;
            $DB->update_record('block_otslider_slide_options', $data);
        } else
        {// Добавление заголовка
            $data->slideid = $this->record->id;
            $data->name = 'parallax';
            $DB->insert_record('block_otslider_slide_options', $data);
        }
    }
    
    /**
     * Получение заголовка слайда
     *
     * @return string
     */
    public function get_slide_title()
    {
        global $DB;
        
        // Заголовок
        $title = (string)$DB->get_field(
            'block_otslider_slide_options',
            'shortdata',
            ['slideid' => $this->record->id, 'name' => 'title']
            );
        return trim($title);
    }
    
    /**
     * Получение резюме слайда
     *
     * @return string
     */
    public function get_slide_summary()
    {
        global $DB;
        
        // Заголовок
        $summary = (string)$DB->get_field(
            'block_otslider_slide_options',
            'shortdata',
            ['slideid' => $this->record->id, 'name' => 'summary']
        );
        return trim($summary);
    }
    
    
    
    /**
     * Сохранение заголовка слайда
     *
     * @param string $title - Заголовок
     *
     * @return void
     */
    public function set_slide_title($title)
    {
        global $DB;
        
        $title = trim($title);
        
        $data = new stdClass();
        $data->shortdata = (string)substr($title, 0, 255);
        if ( $record = $DB->get_record('block_otslider_slide_options', ['slideid' => $this->record->id, 'name' => 'title']) )
        {// Обновление заголовка
            $data->id = $record->id;
            $DB->update_record('block_otslider_slide_options', $data);
        } else
        {// Добавление заголовка
            $data->slideid = $this->record->id;
            $data->name = 'title';
            $DB->insert_record('block_otslider_slide_options', $data);
        }
    }
    
    /**
     * Сохранение резюме слайда
     *
     * @param string $summary - Резюму
     *
     * @return void
     */
    public function set_slide_summary($summary)
    {
        global $DB;
        
        $summary = trim($summary);
        
        $data = new stdClass();
        $data->shortdata = (string)substr($summary, 0, 255);
        if ( $record = $DB->get_record('block_otslider_slide_options', ['slideid' => $this->record->id, 'name' => 'summary']) )
        {// Обновление заголовка
            $data->id = $record->id;
            $DB->update_record('block_otslider_slide_options', $data);
        } else
        {// Добавление заголовка
            $data->slideid = $this->record->id;
            $data->name = 'summary';
            $DB->insert_record('block_otslider_slide_options', $data);
        }
    }
    
    
    
    /**
     * Получение описания слайда
     *
     * @return string
     */
    public function get_slide_description()
    {
        global $DB;
    
        // Описание заголовка
        $description = (string)$DB->get_field(
            'block_otslider_slide_options',
            'data',
            ['slideid' => $this->record->id, 'name' => 'description']
        );
        return trim($description);
    }
    
    /**
     * Сохранение описания слайда
     *
     * @param string $description - Описание
     *
     * @return void
     */
    public function set_slide_description($description)
    {
        global $DB;
    
        $description = trim($description);
    
        $data = new stdClass();
        $data->slideid = $this->record->id;
        $data->name = 'description';
        $data->data = (string)$description;
        if ( $record = $DB->get_record('block_otslider_slide_options', ['slideid' => $this->record->id, 'name' => 'description']) )
        {// Обновление заголовка
            $data->id = $record->id;
            $DB->update_record('block_otslider_slide_options', $data);
        } else
        {// Добавление заголовка
            $DB->insert_record('block_otslider_slide_options', $data);
        }
    }
    
    /**
     * Добавление полей в форму сохранения слайда
     *
     * @param formsave $formsave - Объект формы
     * @param MoodleQuickForm $mform - Объект конструктора формы
     * @param string $prefix - Префикс элементов формы
     *
     * @return void
     */
    public function saveform_definition($formsave, $mform, $prefix)
    {
        // Изображение слайда
        $mform->addElement(
            'filemanager',
            $prefix.'_image',
            get_string('slide_image_formsave_image_label', 'block_otslider'),
            null,
            ['maxfiles' => 1, 'accepted_types' => ['image']]
        );


        // Позиция изображения по вертикали в %
        $backgroundpositiontoprange = function($value) {
            if( $value >=0 && $value <=100)
            {
                return true;
            }
            return false;
        };
        $mform->addElement(
            'text',
            $prefix.'_backgroundpositiontop',
            get_string('slide_image_formsave_backgroundpositiontop_label', 'block_otslider')
            );
        $mform->setType($prefix.'_backgroundpositiontop', PARAM_INT);
        $mform->addRule(
            $prefix.'_backgroundpositiontop',
            get_string('slide_image_formsave_backgroundpositiontop_error_range', 'block_otslider'),
            'callback',
            $backgroundpositiontoprange
        );
        $mform->setDefault($prefix.'_backgroundpositiontop', 50);
        
        // Коэффициент смещения изображения при скролле (параллакс-эффект)
        $parallaxrange = function($value) {
            if( $value >=-100 && $value <=100)
            {
                return true;
            }
            return false;
        };
        $mform->addElement(
            'text',
            $prefix.'_parallax',
            get_string('slide_image_formsave_parallax_label', 'block_otslider')
            );
        $mform->setType($prefix.'_parallax', PARAM_INT);
        $mform->addRule(
            $prefix.'_backgroundpositiontop',
            get_string('slide_image_formsave_parallax_error_range', 'block_otslider'),
            'callback',
            $parallaxrange
            );
        $mform->setDefault($prefix.'_parallax', 0);
        
        // Заголовок
        $mform->addElement(
            'text',
            $prefix.'_title',
            get_string('slide_image_formsave_title_label', 'block_otslider')
        );
        $mform->setType($prefix.'_title', PARAM_TEXT);
        $mform->setDefault($prefix.'_title', '');
        
        // Описание изображения
        $mform->addElement(
            'editor',
            $prefix.'_description',
            get_string('slide_image_formsave_description_label', 'block_otslider')
        );
        $mform->setDefault($prefix.'_description', ['format' => FORMAT_HTML, 'text' => '']);
        
        // Резюме
        $mform->addElement(
            'text',
            $prefix.'_summary',
            get_string('slide_image_formsave_summary_label', 'block_otslider')
            );
        $mform->setType($prefix.'_summary', PARAM_RAW);
        $mform->setDefault($prefix.'_summary', '');
        
        // Отступ текстовой области сверху
        $mform->addElement(
            'text',
            $prefix.'_captiontop',
            get_string('slide_image_formsave_captiontop_label', 'block_otslider')
            );
        $mform->setType($prefix.'_captiontop', PARAM_INT);
        $mform->setDefault($prefix.'_captiontop', 2);
        
        // Отступ текстовой области справа
        $mform->addElement(
            'text',
            $prefix.'_captionright',
            get_string('slide_image_formsave_captionright_label', 'block_otslider')
            );
        $mform->setType($prefix.'_captionright', PARAM_INT);
        $mform->setDefault($prefix.'_captionright', 20);
        
        // Отступ текстовой области снизу
        $mform->addElement(
            'text',
            $prefix.'_captionbottom',
            get_string('slide_image_formsave_captionbottom_label', 'block_otslider')
            );
        $mform->setType($prefix.'_captionbottom', PARAM_INT);
        $mform->setDefault($prefix.'_captionbottom', 2);
        
        // Отступ текстовой области слева
        $mform->addElement(
            'text',
            $prefix.'_captionleft',
            get_string('slide_image_formsave_captionleft_label', 'block_otslider')
            );
        $mform->setType($prefix.'_captionleft', PARAM_INT);
        $mform->setDefault($prefix.'_captionleft', 8);
        
        
        $alignoptions = [
            'left' => get_string('slide_image_formsave_captionalign_left', 'block_otslider'),
            'right' => get_string('slide_image_formsave_captionalign_right', 'block_otslider'),
        ];
        $mform->addElement('select', $prefix.'_captionalign',
            get_string('slide_image_formsave_captionalign_label', 'block_otslider'), $alignoptions);
        $mform->setDefault($prefix.'_captionalign', 'left');
        
        // Просмотр изображений слайдера в модальном окне
        $mform->addElement('advcheckbox', $prefix.'_zoomview', get_string('config_zoomview', 'block_otslider'));
        $mform->setDefault($prefix.'_zoomview', 0);
        $mform->setType($prefix.'_zoomview', PARAM_INT);
    }
    
    /**
     * Предварительная обработка полей формы сохранения слайда
     *
     * Организация заполнения полей данными
     *
     * @param formsave $formsave - Объект формы
     * @param MoodleQuickForm $mform - Объект конструктора формы
     * @param string $prefix - Префикс элементов формы
     *
     * @return void
     */
    public function saveform_set_data($formsave, $mform, $prefix)
    {
        // Поиск пустой пользовательской драфтзоны для подключения к файлпикеру
        $draftitemid = file_get_submitted_draft_itemid($prefix.'_image');
        // Загрузка в пользовательскую зону изображения слайдера
        file_prepare_draft_area(
            $draftitemid,
            context_system::instance()->id,
            'block_otslider',
            'public',
            $this->record->id
        );
        // Привязка файлпикера к пользовательской драфтзоне
        $mform->setDefault($prefix.'_image', $draftitemid);

        // Заполнение данных о позиции изображения по вертикали в %
        $mform->setDefault($prefix.'_backgroundpositiontop', $this->get_slide_backgroundpositiontop());
        
        // Заполнение данных о коэффициенте смещения изображения при скролле (параллакс-эффект)
        $mform->setDefault($prefix.'_parallax', $this->get_slide_parallax());
        
        // Заполнение данных о заголовке
        $mform->setDefault($prefix.'_title', $this->get_slide_title());
        
        // Заполнение данных об описании
        $mform->setDefault($prefix.'_description', ['format' => FORMAT_HTML, 'text' => $this->get_slide_description()]);
        
        // Заполнение данных о заголовке
        $mform->setDefault($prefix.'_summary', $this->get_slide_summary());
        
        // Отступы текстовой области
        $mform->setDefault($prefix.'_captiontop', $this->get_slide_captiontop());
        $mform->setDefault($prefix.'_captionright', $this->get_slide_captionright());
        $mform->setDefault($prefix.'_captionbottom', $this->get_slide_captionbottom());
        $mform->setDefault($prefix.'_captionleft', $this->get_slide_captionleft());
        
        // Выравнивание текстовой области
        $mform->setDefault($prefix.'_captionalign', $this->get_slide_captionalign());
        
        // Сохранение настройки увеличения слайда
        $mform->setDefault($prefix.'_zoomview', $this->get_slide_zoomview());
    }
    
    /**
     * Валидация полей формы сохранения слайда
     *
     * @param array $errors - Массив ошибок валидации
     * @param formsave $saveform - Объект формы
     * @param MoodleQuickForm $mform - Объект конструктора формы
     * @param array $data - Данные формы сохранения
     * @param array $files - Загруженные файлы формы сохранения
     * @param string $prefix - Префикс элементов формы
     *
     * @return void
     */
    public function saveform_validation($errors, $saveform, $mform, $data, $files, $prefix)
    {
        // Получение заголовка слайда
        $title = trim($data[$prefix.'_title']);
        if ( strlen($title) > 255 )
        {// Лимит заголовка
            $errors[$prefix.'_title'] = get_string('slide_image_formsave_title_error_maxlen', 'block_otslider');
        }
        
        // Получение резюме слайда
        $summary = trim($data[$prefix.'_summary']);
        if ( strlen($summary) > 255 )
        {// Лимит резюме
            $errors[$prefix.'_summary'] = get_string('slide_image_formsave_summary_error_maxlen', 'block_otslider');
        }
        
        // Получение резюме слайда
        $captionalign = trim($data[$prefix.'_captionalign']);
        if ( !in_array($captionalign, ['left', 'right']) )
        {// Лимит резюме
            $errors[$prefix.'$captionalign'] = get_string('slide_image_formsave_cpationalign_error_value', 'block_otslider');
        }
        
        // Получение позиции изображения по вертикали
        $backgroundpositiontop = $data[$prefix.'_backgroundpositiontop'];
        if ( $backgroundpositiontop > 100 || $backgroundpositiontop < 0 )
        {// обнаружен выход из диапазона разрешенных значений
            $errors[$prefix.'_backgroundpositiontop'] = get_string('slide_image_formsave_backgroundpositiontop_error_range', 'block_otslider');
        }
        
        // Получение коэффициента смещения изображения (параллакс-эффект)
        $parallax = $data[$prefix.'_parallax'];
        if ( $parallax > 100 || $parallax < -100 )
        {// обнаружен выход из диапазона разрешенных значений
            $errors[$prefix.'_parallax'] = get_string('slide_image_formsave_parallax_error_range', 'block_otslider');
        }
    }
    
    /**
     * Процесс сохранения слайда
     *
     * @param formsave $saveform - Объект формы
     * @param MoodleQuickForm $mform - Объект конструктора формы
     * @param stdClass $formdata - Данные формы сохранения
     * @param string $prefix - Префикс элементов формы
     *
     * @return void
     */
    public function saveform_process($saveform, $mform, $formdata, $prefix)
    {
        // Сохранение изображения
        $fieldname = $prefix.'_image';
        file_save_draft_area_files(
            $formdata->$fieldname,
            context_system::instance()->id,
            'block_otslider',
            'public',
            $this->record->id,
            ['maxfiles' => 1, 'accepted_types' => ['image']]
        );

        // Сохранение позиции изображения по вертикали
        $fieldname = $prefix.'_backgroundpositiontop';
        $this->set_slide_backgroundpositiontop($formdata->$fieldname);
        
        // Сохранение коэффициента смещения изображения (параллакс-эффект)
        $fieldname = $prefix.'_parallax';
        $this->set_slide_parallax($formdata->$fieldname);
        
        // Сохранение заголовка
        $fieldname = $prefix.'_title';
        $this->set_slide_title((string)$formdata->$fieldname);
        
        // Сохранение описания
        $fieldname = $prefix.'_description';
        $descriptiondata = $formdata->$fieldname;
        $this->set_slide_description((string)$descriptiondata['text']);
        
        // Сохранение резюме
        $fieldname = $prefix.'_summary';
        $this->set_slide_summary((string)$formdata->$fieldname);
        
        // Сохранение отступов текстовой области
        $fieldname = $prefix.'_captiontop';
        $this->set_slide_captiontop($formdata->$fieldname);
        $fieldname = $prefix.'_captionright';
        $this->set_slide_captionright($formdata->$fieldname);
        $fieldname = $prefix.'_captionbottom';
        $this->set_slide_captionbottom($formdata->$fieldname);
        $fieldname = $prefix.'_captionleft';
        $this->set_slide_captionleft($formdata->$fieldname);
        
        // Сохранение выравнивания текстовой области
        $fieldname = $prefix.'_captionalign';
        $this->set_slide_captionalign($formdata->$fieldname);
        
        // Сохранение настройки увеличения слайда
        $fieldname = $prefix.'_zoomview';
        $this->set_slide_zoomview($formdata->$fieldname);
    }
    
    /**
     * Процесс удаления данных слайда
     *
     * @return void
     *
     * @throws exception_slide - В случае ошибок при удалении данных слайда
     */
    public function process_delete()
    {
        global $DB;
        
        // Получение менеджера файлов
        $fs = get_file_storage();
        
        // Удаление изображения слайда
        $fs->delete_area_files(
            context_system::instance()->id,
            'block_otslider',
            'public',
            $this->record->id
        );
    
        // Попытка удаления всех опций слайда
        try
        {
            $DB->delete_records('block_otslider_slide_options', ['slideid' => $this->record->id]);
        } catch ( dml_exception $e )
        {// Ошибка удаления слайда
            throw new exception_slide('slide_image_delete_error_options', 'block_otslider', '', null, $e->getMessage());
        }
    }
}