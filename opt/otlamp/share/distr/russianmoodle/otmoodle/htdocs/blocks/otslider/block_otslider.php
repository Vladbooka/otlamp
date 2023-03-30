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
 * Слайдер изображений. Класс блока.
 *
 * @package    block
 * @subpackage otslider
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_otslider\slider;
use core\notification;

class block_otslider extends block_base
{
    /**
     * Текущий профиль темы равен указанному в настройках, или настройка не активна
     *
     * @var boolean
     */
    private $iscurrentthemeprofile = true;
    /**
     * Инициализация блока
     *
     * @return void
     */
    public function init()
    {
        $this->blockname = get_class($this);

        // Имя блока
        $this->title = get_string('title', 'block_otslider');
    }

    /**
     * Поддержка нескольких экземпляров блока на странице
     *
     * @return bool
     */
    public function instance_allow_multiple()
    {
        return true;
    }

    /**
     * Поддержка скрытия блока
     *
     * @return bool
     */
    public function instance_can_be_hidden()
    {
        // Блок разрешено скрывать
        return true;
    }

    /**
     * Поддержка сворачивания блока
     *
     * @return bool
     */
    public function instance_can_be_collapsed()
    {
        return false;
    }

    /**
     * Скрытие заголовка блока
     *
     * @return bool
     */
    public function hide_header()
    {
        global $USER;
        if (property_exists($USER, 'editing') && ($USER->editing == 1)) {
            return false;
        }
        return true;
    }

    /**
     * Сформировать контент блока
     *
     * @return stdClass - Контент блока
     */
    public function get_content()
    {
        global $USER;

        if ( $this->content !== null )
        {
            return $this->content;
        }

        // Объявление контента блока
        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        if (!$this->iscurrentthemeprofile) {
            // Сейчас выбран не тот профиль, в котором разрешено отображение
            return $this->content;
        }

        // Инициализация слайдера
        $slider = new slider($this);
        $slides = $slider->get_slides();
        $sliderecords = $slider->get_slide_records();

        //Инициализация рендера
        if (class_exists('\otcomponent_otslider\slider')) {
            $sliderrender = new \otcomponent_otslider\slider($this->config);
        } else {
            notification::error('Class otcomponent_otslider\slider not found');
            return $this->content;
        }
        foreach ($slides as $key => $slide) {
            $sliderender = $sliderrender->get_slide_class($sliderecords[$key]->type);
            foreach ($slide->get_slide_options() as $optname => $optval) {
                if (isset( $sliderender->{$optname}) ) {
                    $sliderender->{$optname} = $optval;
                }
            }
        }
        $this->content->text = $sliderrender->get_slider_html();
        if (property_exists($USER, 'editing') && $USER->editing == 1) {
            if (!empty($this->config->blockreplace)) {
                $this->content->footer = html_writer::div(
                    get_string('use_placeholder','block_otslider', $this->instance->id),
                    'footer-info'
                );
            } elseif (empty($this->content->text)) {
                $this->content->footer = html_writer::div(
                    get_string('need_config', 'block_otslider'),
                    'footer-info'
                );
            }
        }
        return $this->content;
    }

    /**
     * Поддержка блоком страницы конфигурации
     *
     * @return boolean
     */
    public function has_config()
    {
        // Страница настроек блока доступна
        return true;
    }

    /**
     * Отображение блока на страницах
     *
     * @return array - Перечень типов страниц, но которых возможно добавление блока
     */
    public function applicable_formats()
    {
        return [
            'all' => true,
        ];
    }

    /**
     * Процесс удаления текущего экземпляра блока
     *
     * @return boolean
     */
    public function instance_delete()
    {
        // Инициализация слайдера
        $slider = new slider($this);

        // Передача процесса удаления в слайды
        $slides = $slider->get_slides();
        foreach ($slides as $slideid => $slide)
        {
            $slider->delete_slide($slideid);
        }
        return true;
    }
}